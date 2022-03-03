<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Estimate\Models\Resource;
use Rkesa\Estimate\Models\EstimateUnit;
use Log;
use Exception;
use Rkesa\Estimate\Models\ResourceAttachment;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'created_at');
            if ($sort == ''){ $sort = 'created_at'; }
            $order = $request->input('order', 'asc');
            if ($order == ''){ $order = 'asc'; }

            $resources = Resource::with('estimate_unit')->orderBy('name');

            $query = $request->input('query', '');
            if ($query != '') {
                $resources->where('name', 'like', '%' . $query . '%');
            }

            $resources->orderBy($sort, $order);

            $res->total = $resources->count();
            $res->rows = $resources->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $resource = new Resource;
            $resource->fill($request->all());
            $resource->save();
            if ($request->exists('resource_attachments')) {
                foreach($request['resource_attachments'] as $attachment){
                    if (strpos($attachment['url'], '/img/uploads/temp/') !== false) {
                        // if temp
                        $old_file = substr($attachment['url'], 1);
                        $filename = pathinfo($attachment['url'], PATHINFO_BASENAME);
                        $new_file = 'img/uploads/resource/'.$filename;
                        rename($old_file, $new_file);
                        $a = new ResourceAttachment();
                        $a->url = '/'.$new_file;
                        $a->name = $attachment['name'];
                        $a->resource_id = $resource->id;
                        $a->save();
                    }
                }
            }
            $res->id = $resource->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        return Resource::with('resource_attachments')->find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $resource = Resource::find($id);
            $resource->fill($request->all());
            $resource->save();
            if ($resource->update_fichas) {
                // Updating ficha patterns
                $ficha_patterns = EstimateLineFicha::where('is_pattern', true)->get();
                foreach ($ficha_patterns as $ficha_pattern) {
                    $estimate_line_ficha_resources = $ficha_pattern->resources;
                    foreach ($estimate_line_ficha_resources as $estimate_line_ficha_resource) {
                        if ($estimate_line_ficha_resource->resource_id == $resource->id) {
                            $estimate_line_ficha_resource->resource_type = $resource->resource_type;
                            $estimate_line_ficha_resource->estimate_unit_id = $resource->estimate_unit_id;
                            $estimate_line_ficha_resource->price = $resource->price;
                            $estimate_line_ficha_resource->quantity = $resource->quantity;
                            switch ($estimate_line_ficha_resource->resource_type) {
                                case 0:
                                case 3:
                                    $estimate_line_ficha_resource->correction = 1;
                                    break;
                                case 1:
                                case 2:
                                    $estimate_line_ficha_resource->correction = 0;
                                    break;
                            }
                            $estimate_line_ficha_resource->save();
                        }
                    }
                }
            }
            $attachments = ResourceAttachment::where('resource_id', $resource->id)->get();
            foreach($attachments as $attachment){
                $found = false;
                foreach($request['resource_attachments'] as $request_attachment){
                    if ($attachment->url == $request_attachment['url']){
                        $found = true;
                    }
                }
                if (!$found){
                    $filepath = substr($attachment->url, 1);
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                    $attachment->delete();
                }
            }
            if ($request->exists('resource_attachments')) {
                foreach ($request['resource_attachments'] as $attachment) {
                    if (strpos($attachment['url'], '/img/uploads/temp/') !== false) {
                        // if temp
                        $old_file = substr($attachment['url'], 1);
                        $filename = pathinfo($attachment['url'], PATHINFO_BASENAME);
                        $new_file = 'img/uploads/resource/' . $filename;
                        rename($old_file, $new_file);
                        $a = new ResourceAttachment();
                        $a->url = '/' . $new_file;
                        $a->name = $attachment['name'];
                        $a->resource_id = $id;
                        $a->save();
                    }
                }
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        EstimateLineFichaResource::where('resource_id', $id)->delete();
        Resource::find($id)->delete();
    }

}
