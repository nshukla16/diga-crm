<?php

namespace App\Http\Controllers\Zadarma;

use App\User;
use Carbon\Carbon;
use App\Call;
use Log;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientHistory;
use Exception;

class History {
    
    protected $call_types = array(21,22);

    private function isValidType(int $type_id) {
        return array_search($type_id, $this->call_types) !== false;
    }

    public static function updateCallRecord($call_id, $link, $lifetime) {
        return Call::where('id', '=', $call_id)->update(array(
            'record_link' => $link,
            'record_link_lifetime_till' => $lifetime
        ));
    }

    public static function isNeedToUpdateCallRecord($lifetime_till) {
        if($lifetime_till == null)
            return true;

        $the_date = Carbon::parse($lifetime_till);
        return !$the_date->gt(Carbon::now());
    }

    public function write_history(int $user_id, int $contact_id, int $type_id, int $call_id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            if(!$this->isValidType($type_id))
                throw new Exception(trans('zadarma.wrong_call_type'));
            
            //$isValidContact = $cm::where('id', '=' , $contact_id)->count() > 0;
            //if(!$isValidContact)
            //  throw new Exception(trans('zadarma.contact_not_exists'));

            //$isValidUser    = $u::where('id', '=', $user_id)->count() > 0;
            //if(!$isValidUser)
            //  throw new Exception(trans('template.User_not_exists'));

            //$record = $this->checkRecord($call_id);
            //if($record->status != 'success')
            //  throw new Exception(trans('zadarma.Api_call_record_request_failed'));

            //if(!property_exists($record, 'link'))
            //  throw new Exception(trans('zadarma.Api_link_property_not_found'));

            //$link = $record->link;
            //$lifetime_till = $record->lifetime_till;
            $h = new ClientHistory;
            $h->type_id = $type_id;
            $h->user_id = $user_id;
            $h->call_id = $call_id;
            //$h->link    = $link;
            //$h->lifetime= $lifetime_till;
            $h->client_contact_id = $contact_id;
            $h->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

}
