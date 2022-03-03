<?php

namespace App\Http\Controllers\Zadarma;

use Zadarma_API\Client;
use stdClass;
use Log;
use Exception;

class Api {

    protected $client;
    protected $settings;
    protected $record_lifetime = 5184000; // max

    public function __construct() {
        $this->init();
    }

    private function init() {

        $this->settings = Settings::getSettings();
        if($this->settings->zadarma_enabled == true)
            $this->client = new Client($this->settings->zadarma_key, $this->settings->zadarma_secret);
        //else throw new Exception(trans('Zadarma is not enabled'));
    }

    public function getSettings() {
        return $this->settings;
    }

    public function getBalance() {
        return $this->client->call('/v1/info/balance/');
    }

    public function getDirectNumbers() {
        $output = array();
        $res = json_decode($this->client->call('/v1/direct_numbers/'));

        if($res->status == 'success')
            foreach($res->info as $number)
                $output[] = $number->number;

        return $output;
    }

    public function getCallRecordLink(string $call_id_with_rec) {
        
        $params = array(
            'call_id' => $call_id_with_rec,
            'lifetime' => $this->record_lifetime
        );

        $res = json_decode($this->client->call('/v1/pbx/record/request/', $params));

        $output = false;

        if($res->status == 'success')
            $output = $res;

        return $output;
    }

    public function getCredentials() {
        $c = new stdClass;
        // get sip
        $rq = json_decode($this->client->call('/v1/sip/'));

        $sip = $rq->sips[0];
        $sip->id = (int) $sip->id;
        $c->sip = $sip;
        // Internal numbers
        $in = json_decode($this->client->call('/v1/pbx/internal/'));
        $c->pbx_id = $in->pbx_id;
        $c->numbers = $in->numbers;
        return $c;
    }

    public function make_call($from, $to) {
        $params = array('from' => $from, 'to'   => $to);
        return $this->client->call('/v1/request/callback/', $params);
    }

    public function isAuthorized() {
        try {
            $test = json_decode($this->getBalance());
            $r = $test->status == 'success';
        } catch (Exception $e) {
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return $r;
    }

    public function checkRecord(string $call_id) {
        $params = array(
            'call_id' => $call_id,
            'lifetime' => $this->record_lifetime
        );
        $result = json_decode($this->client->call('/v1/pbx/record/request/', $params));
        return $result;
    }

}
