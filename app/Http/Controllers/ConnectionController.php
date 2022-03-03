<?php

namespace App\Http\Controllers;

use App\Connection;
use App\Events\GlobalSettingsChanged;
use App\GlobalSettings;
use App\Http\Traits\SaasAuthTrait;
use App\Http\Traits\SaasConnectionTrait;
use App\Notifications\NewConnection;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Rkesa\Client\Models\Client;

class ConnectionController extends Controller
{
    use SaasAuthTrait;
    use SaasConnectionTrait;

    public function update(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $connections = $request->input('connections', []);
            foreach ($connections as $connection) {
                $conn = Connection::find($connection['id']);
                $conn->responsible_id = $connection['responsible_id'];
                $conn->client_referrer_id = $connection['client_referrer_id'];
                $conn->save();
            }

            $service_state_id = $request->input('new_service_state_id', 0);
            if ($service_state_id > 0) {
                $gs = GlobalSettings::first();
                $gs->contractor_service_state_id = $service_state_id;
                $gs->save();
                broadcast(new GlobalSettingsChanged());
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public static function receive_confirm(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $url = $request->input('url', null);

            $connection = Connection::where('url', $url)->where('is_my', true)->firstOrFail();
            $connection->is_approved = true;
            $connection->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function confirm(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $connection_id = $request->input('id', 0);
            $connection = Connection::find($connection_id);
            $to_replace = array("http://", "https://", "www.", ".diga.pt");

            $auth = self::get_access_token();
            $token = $auth['access_token'];

            $source_url = str_replace($to_replace, "", env('APP_URL'));

            $confirm = self::confirmation($connection->url, $source_url, $token);

            if ($confirm == false) {
                throw new Exception("Error while sending connection to ERP");
            }

            $connection->is_approved = true;
            $connection->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }


    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $res->rows = Connection::where('is_my', false)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public static function receive(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $url = $request->input('url', null);

            $connection = new Connection;
            $connection->url = $url;
            $connection->is_my = false;
            $connection->is_subcontractor = true;
            $connection->is_approved = false;
            $connection->save();

            $users = User::where('is_admin', true)->get();
            Notification::send($users, new NewConnection($connection));

            $res->connection_id = $connection->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $url = $request->input('url', null);
            $to_replace = array("http://", "https://", "www.", '/', ".diga.pt");
            $url = str_replace($to_replace, "", $url);

            $client_id = $request->input('client_id', 0);

            $auth = self::get_access_token();
            $token = $auth['access_token'];
            $exists = self::check_if_exists($url, $token);

            if ($exists == false) {
                throw new Exception("ERP not found");
            }

            $source_url = str_replace($to_replace, "", env('APP_URL'));

            $store = self::create($url, $source_url, $token);
            if ($store == false) {
                throw new Exception("Error while sending connection to ERP");
            }

            $connection = new Connection;
            $connection->url = $url;
            $connection->is_my = true;
            $connection->is_subcontractor = true;
            $connection->is_approved = false;
            $connection->save();

            $client = Client::find($client_id);
            $client->connection_id = $connection->id;
            $client->save();

            $res->connection_id = $connection->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
