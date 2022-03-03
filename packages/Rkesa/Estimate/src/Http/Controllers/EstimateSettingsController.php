<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\Events\EstimateSettingsChanged;
use App\GlobalSettings;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Exception;
use Log;
use Rkesa\Estimate\Models\EstimateDocument;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\Resource;

class EstimateSettingsController extends Controller
{

    public function save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            foreach($request['removed'] as $i){
                $r = EstimateUnit::find($i);
                if ($r){
                    $resources = Resource::where('estimate_unit_id', $r->id)->get();
                    foreach($resources as $resource){
                        $resource->estimate_unit_id = null;
                        $resource->save();
                    }
                    $datas = EstimateLineData::where('estimate_unit_id', $r->id)->get();
                    foreach($datas as $data){
                        $data->estimate_unit_id = null;
                        $data->save();
                    }
                    $fichas = EstimateLineFicha::where('estimate_unit_id', $r->id)->get();
                    foreach($fichas as $ficha){
                        $ficha->estimate_unit_id = null;
                        $ficha->save();
                    }
                    $ficha_resources = EstimateLineFichaResource::where('estimate_unit_id', $r->id)->get();
                    foreach($ficha_resources as $ficha_resource){
                        $ficha_resource->estimate_unit_id = null;
                        $ficha_resource->save();
                    }
                    $r->delete();
                }
            }
            foreach($request['units'] as $i){
                if ($i['id'] == 0){
                    $r = new EstimateUnit;
                    $r->measure = $i['measure'];
                    $r->hours_to_do = $i['hours_to_do'];
                    $r->save();
                }else {
                    $r = EstimateUnit::find($i['id']);
                    $r->measure = $i['measure'];
                    $r->hours_to_do = $i['hours_to_do'];
                    $r->save();
                }
            }
            // Files
            $attachments = EstimateDocument::all();
            foreach($attachments as $attachment){
                $found = false;
                foreach($request['documents'] as $request_attachment){
                    if ($attachment->url == $request_attachment['url']){
                        $found = true;
                        $attachment->default_count = $request_attachment['default_count'];
                        $attachment->default_checked = $request_attachment['default_checked'];
                        $attachment->save();
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
            foreach($request['documents'] as $attachment){
                if (strpos($attachment['url'], '/img/uploads/temp/') !== false) {
                    // if temp
                    $old_file = substr($attachment['url'], 1);
                    $filename = pathinfo($attachment['url'], PATHINFO_BASENAME);
                    $new_file = 'docs/'.$filename;
                    rename($old_file, $new_file);
                    $a = new EstimateDocument();
                    $a->url = '/'.$new_file;
                    $a->name = $attachment['name'];
                    $a->default_count = $attachment['default_count'];
                    $a->default_checked = $attachment['default_checked'];
                    $a->save();
                }
            }
            $gs = GlobalSettings::first();
            $gs->fill($request['global_settings']);
            $gs->save();
            broadcast(new EstimateSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
