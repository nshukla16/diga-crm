<?php

namespace App\Http\Controllers;

use App\Events\NewFBMessage;
use App\FacebookPage;
use App\GlobalSettings;
use App\Http\Traits\MailTrait;
use App\Http\Traits\SaasAuthTrait;
use Exception;
use Log;
use Illuminate\Http\Request;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Client\Models\ClientReferrer;

class FacebookController extends Controller
{
    use MailTrait, SaasAuthTrait;

    public static function fb_send($user_id, $page_id, $message) {
        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/integrations/facebook/send_message', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => [
                'page_id' => $page_id,
                'user_id' => $user_id,
                'text' => $message
            ],
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['result'] == 'OK';
    }

    public function auth()
    {
        $response_decoded = self::get_access_token();

        return ['url' => 'https://saas.diga.pt/integrations/facebook?access_token='.$response_decoded['access_token']];
    }

    public static function subscribe_page($page_id)
    {
        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/integrations/facebook/subscribe', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => ['page_id' => $page_id],
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded;
    }

    public static function unsubscribe_page($page_id)
    {
        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/integrations/facebook/unsubscribe', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => ['page_id' => $page_id],
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded;
    }

    public static function get_user_from_id($user_id, $page_id)
    {
        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/integrations/facebook/get_user', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => [
                'user_id' => $user_id,
                'page_id' => $page_id,
            ],
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded;
    }

    public static function get_pages_list()
    {
        $gs = GlobalSettings::first();
        $gs->fb_enabled = true;
        $gs->save();

        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->get(env('ERP_SAAS_URL', '').'/api/integrations/facebook/pages', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
        ]);

        $pages = json_decode((string) $response->getBody(), true);
        FacebookPage::truncate();
        foreach($pages as $page){
            FacebookPage::create($page);
        }
    }

    public static function fb_incoming_message($page_id, $sender_psid, $text)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $client_contact = ClientContact::where('fb_psid', $sender_psid)->first();

            if (!$client_contact){
                $client_referrer = ClientReferrer::firstOrCreate(['title' => 'Facebook']);

                $sender = FacebookController::get_user_from_id($sender_psid, $page_id);

                $user_sex = null;
                if (array_key_exists('gender', $sender)) {
                    $user_sex = $sender['gender'] == 'male' ? 1 : 0;
                }

                ApiController::new_client($client_referrer, null, 19, $sender['first_name'], $sender['last_name'], $user_sex, '', null, '', '', false, '', '', $text, array(), null, $sender_psid, $page_id);
            }else{
                $client_contact->client_referrer;

                $hist = new ClientHistory;
                $hist->message = MailTrait::format_email($text);
                $hist->client_contact_id = $client_contact->id;
                $hist->type_id = 19;
                $hist->save();

                $client_contact->show_fb_notification = true;
                $client_contact->save();

                broadcast(new NewFBMessage(array_merge(['message' => $hist->message], $client_contact->toArray())));
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
