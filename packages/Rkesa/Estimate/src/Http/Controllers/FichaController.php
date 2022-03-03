<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Log;
use Exception;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\Resource;

class FichaController extends Controller
{

    public function search(Request $request)
    {
        $search = $request->input('query', false);
        $fichas = EstimateLineFicha::search($search);
        foreach($fichas as $ficha){
            $ficha->maodeobra['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $ficha->id)->where('resource_type', 0)->get();
            foreach ($ficha->maodeobra['list'] as $ficha_resource){
                $ficha_resource['name'] = Resource::find($ficha_resource['resource_id'])->name;
                $ficha_resource['selected'] = true;
            }
            $ficha->maodeobra['total_price'] = 0;
            $ficha->materials['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $ficha->id)->where('resource_type', 1)->get();
            foreach ($ficha->materials['list'] as $ficha_resource){
                $ficha_resource['name'] = Resource::find($ficha_resource['resource_id'])->name;
                $ficha_resource['selected'] = true;
            }
            $ficha->materials['total_price'] = 0;
            $ficha->equipment['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $ficha->id)->where('resource_type', 2)->get();
            foreach ($ficha->equipment['list'] as $ficha_resource){
                $ficha_resource['name'] = Resource::find($ficha_resource['resource_id'])->name;
                $ficha_resource['selected'] = true;
            }
            $ficha->equipment['total_price'] = 0;
            $ficha->subs['list']      = EstimateLineFichaResource::where('estimate_line_ficha_id', $ficha->id)->where('resource_type', 3)->get();
            foreach ($ficha->subs['list'] as $ficha_resource){
                $ficha_resource['name'] = Resource::find($ficha_resource['resource_id'])->name;
                $ficha_resource['selected'] = true;
            }
            $ficha->subs['total_price']      = 0;
        }
        return response()->json($fichas);
    }

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

            $fichas = EstimateLineFicha::where('is_pattern', true)->orderBy('name');

            $query = $request->input('query', '');
            if ($query != '') {
                $fichas->where('name', 'like', '%' . $query . '%');
            }

            $fichas->orderBy($sort, $order);

            $res->total = $fichas->count();
            $res->rows = $fichas->offset($offset)->limit($limit)->get();
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
            $ficha = new EstimateLineFicha;
            $ficha->fill($request->all());
            $ficha->is_pattern = true;
            $ficha->save();
            foreach(['maodeobra', 'materials', 'equipment', 'subs'] as $resname) {
                if ($request->exists($resname)) {
                    foreach ($request[$resname]['list'] as $ress) {
                        $m = new EstimateLineFichaResource;
                        $r = Resource::where('name', $ress['name'])->first();
                        $m->price = $ress['price'];
                        $m->resource_type = $ress['resource_type'];
                        $m->estimate_unit_id = $ress['estimate_unit_id'];
                        $m->estimate_line_ficha_id = $ficha->id;
                        $m->correction = $ress['correction'];
                        $m->quantity = $ress['quantity'];
                        $m->resource_id = $r->id;
                        $m->save();
                    }
                }
            }
            $res->id = $ficha->id;
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
        $ficha = EstimateLineFicha::find($id)->toArray();
        $ficha['maodeobra'] = array();
        $ficha['materials'] = array();
        $ficha['equipment'] = array();
        $ficha['subs'] = array();
        $ficha['maodeobra']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $ficha['id'])->where('resource_type', 0)->with('resource')->get()->toArray();
        $ficha['materials']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $ficha['id'])->where('resource_type', 1)->with('resource')->get()->toArray();
        $ficha['equipment']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $ficha['id'])->where('resource_type', 2)->with('resource')->get()->toArray();
        $ficha['subs']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $ficha['id'])->where('resource_type', 3)->with('resource')->get()->toArray();
        $ficha['maodeobra']['total_price'] = 0;
        $ficha['materials']['total_price'] = 0;
        $ficha['equipment']['total_price'] = 0;
        $ficha['subs']['total_price'] = 0;
        for ($i = 0; $i < count($ficha['maodeobra']['list']); $i++){
            $ficha['maodeobra']['list'][$i]['name'] = $ficha['maodeobra']['list'][$i]['resource']['name'];
            $ficha['maodeobra']['list'][$i]['selected'] = true;
            $ficha['maodeobra']['list'][$i]['total_price'] = 0;
        }
        for ($i = 0; $i < count($ficha['materials']['list']); $i++){
            $ficha['materials']['list'][$i]['name'] = $ficha['materials']['list'][$i]['resource']['name'];
            $ficha['materials']['list'][$i]['selected'] = true;
            $ficha['materials']['list'][$i]['total_price'] = 0;
        }
        for ($i = 0; $i < count($ficha['equipment']['list']); $i++){
            $ficha['equipment']['list'][$i]['name'] = $ficha['equipment']['list'][$i]['resource']['name'];
            $ficha['equipment']['list'][$i]['selected'] = true;
            $ficha['equipment']['list'][$i]['total_price'] = 0;
        }
        for ($i = 0; $i < count($ficha['subs']['list']); $i++){
            $ficha['subs']['list'][$i]['name'] = $ficha['subs']['list'][$i]['resource']['name'];
            $ficha['subs']['list'][$i]['selected'] = true;
            $ficha['subs']['list'][$i]['total_price'] = 0;
        }
        return $ficha;
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
            $ficha = EstimateLineFicha::find($id);
            $ficha->fill($request->all());
            $ficha->save();
            $ficha->resources()->delete();
            foreach(['maodeobra', 'materials', 'equipment', 'subs'] as $resname) {
                if ($request->exists($resname)) {
                    foreach ($request[$resname]['list'] as $ress) {
                        $m = new EstimateLineFichaResource;
                        $r = Resource::where('name', $ress['name'])->first();
                        $m->price = $ress['price'];
                        $m->resource_type = $ress['resource_type'];
                        $m->estimate_unit_id = $ress['estimate_unit_id'];
                        $m->estimate_line_ficha_id = $ficha->id;
                        $m->correction = $ress['correction'];
                        $m->quantity = $ress['quantity'];
                        $m->resource_id = $r->id;
                        $m->save();
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
        EstimateLineFicha::find($id)->delete();
    }


}
