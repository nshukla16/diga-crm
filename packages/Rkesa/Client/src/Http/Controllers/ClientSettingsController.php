<?php

namespace Rkesa\Client\Http\Controllers;

use App\Events\ClientsSettingsChanged;
use App\GlobalSettings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientReferrer;

class ClientSettingsController extends Controller
{
    public function save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            foreach($request['removed'] as $i){
                $r = ClientReferrer::find($i);
                if ($r){
                    $clients = Client::where('client_referrer_id', $r->id)->get();
                    foreach($clients as $client){
                        $client->client_referrer_id = null;
                        $client->save();
                    }
                    $r->delete();
                }
            }
            foreach($request['referrers'] as $i){
                if ($i['id'] == 0){
                    $r = new ClientReferrer;
                    $r->title = $i['title'];
                    $r->save();
                }else {
                    $r = ClientReferrer::find($i['id']);
                    $r->title = $i['title'];
                    $r->save();
                }
            }
            $gs = GlobalSettings::first();
            $gs->contact_attributes = $request['contact_attributes'];
            $gs->client_attributes = $request['client_attributes'];
            $gs->save();
            broadcast(new ClientsSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
