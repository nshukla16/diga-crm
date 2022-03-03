<?php

namespace App\Http\Controllers;

use App\GlobalSettings;
use Illuminate\Http\Request;
use Auth;
use Debugbar;
use Imtigger\LaravelJobStatus\JobStatus;
use Log;
use Storage;
use Exception;
use Rkesa\Service\Models\Service;

class MailChimpController extends Controller
{
    protected $base_url = '.api.mailchimp.com/3.0/';

    public function audiences()
    {
        $gs = GlobalSettings::first();
        if ($gs->mailchimp_integration_enabled && $gs->mailchimp_api_key !== '') {

            $guzzle = new \GuzzleHttp\Client;
            $data_center = substr($gs->mailchimp_api_key, strpos($gs->mailchimp_api_key, "-") + 1);    

            $response_decoded = '';            

            $response = $guzzle->get('https://'.$data_center.$this->base_url.'lists?count=1000', [
                'auth' => [
                    'username', $gs->mailchimp_api_key
                ]
            ]);

            $response_decoded = json_decode((string)$response->getBody(), true);

            return response()->json($response_decoded);
        }else{
            throw new Exception('Mailchimp is not connected');
        }
    }

    public function campaigns()
    {
        $gs = GlobalSettings::first();
        if ($gs->mailchimp_integration_enabled && $gs->mailchimp_api_key !== '') {

            $guzzle = new \GuzzleHttp\Client;
            $data_center = substr($gs->mailchimp_api_key, strpos($gs->mailchimp_api_key, "-") + 1);

            $response_decoded = '';            

            $response = $guzzle->get('https://'.$data_center.$this->base_url.'campaigns?count=1000', [
                'auth' => [
                    'username', $gs->mailchimp_api_key
                ]
            ]);

            $response_decoded = json_decode((string)$response->getBody(), true);

            return response()->json($response_decoded);
        }else{
            throw new Exception('Mailchimp is not connected');
        }
    }

    public function create_new_audience(Request $request)
    {
        $selected_service_state_id = $request['selected_service_state_id'];
        $new_list_name = $request['new_list_name'];
        $company = $request['company'];
        $address1 = $request['address1'];
        $address2 = $request['address2'];
        $city = $request['city'];
        $state = $request['state'];
        $zip = $request['zip'];
        $country = $request['country'];
        $phone = $request['phone'];
        $permission_reminder = $request['permission_reminder'];
        $from_name = $request['from_name'];
        $from_email = $request['from_email'];
        $language = $request['language'];

        $res = (object)array();
        $res->errcode = 0;
        try{
            $gs = GlobalSettings::first();
            if ($gs->mailchimp_integration_enabled && $gs->mailchimp_api_key !== '') {
    
                $guzzle = new \GuzzleHttp\Client(['http_errors' => false]);
                $data_center = substr($gs->mailchimp_api_key, strpos($gs->mailchimp_api_key, "-") + 1);
    
                $response = $guzzle->post('https://'.$data_center.$this->base_url.'lists', [
                    'auth' => [
                        'username', $gs->mailchimp_api_key
                    ],
                    'body' => json_encode(
                        [
                            'name' => $new_list_name,
                            'contact' => [
                                'company' => $company,
                                'address1' => $address1,
                                'address2' => $address1,
                                'city' => $city,
                                'state' => $state,
                                'zip' => $zip,
                                'country' => $country,
                                'phone' => $phone
                            ],
                            'permission_reminder' => $permission_reminder,
                            'campaign_defaults' => [
                                'from_name' => $from_name,
                                'from_email' => $from_email,
                                'subject' => $new_list_name,
                                'language' => $language
                            ],
                            'email_type_option' => false,                            
                        ]
                    )
                ]);

                $result = json_decode((string)$response->getBody(), true);

                if ($response->getStatusCode() !== 200){
                    Log::info($result);
                    throw new Exception('Integration failed .. '.$result['detail']);
                }
                else{
                    $request['selected_audience_id'] = $result['id'];
                    $this->add_to_audience($request);
                }

            }else{
                throw new Exception('Mailchimp is not connected');
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function add_to_audience(Request $request)
    {
        $selected_service_state_id = $request['selected_service_state_id'];
        $selected_audience_id = $request['selected_audience_id'];

        $res = (object)array();
        $res->errcode = 0;
        try{
            $gs = GlobalSettings::first();
            if ($gs->mailchimp_integration_enabled && $gs->mailchimp_api_key !== '') {
    
                $guzzle = new \GuzzleHttp\Client(['http_errors' => false]);
                $data_center = substr($gs->mailchimp_api_key, strpos($gs->mailchimp_api_key, "-") + 1);

                $services = Service::with(['client_contact', 'client_contact.client_contact_emails'])->where('service_state_id', $selected_service_state_id)->get();
                $emails = [];

                foreach($services as $s)
                {
                    foreach($s->client_contact->client_contact_emails as $e){
                        if (!in_array($e->email, $emails)){
                            $regex = '/[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})/'; 
                            if (preg_match($regex, $e->email, $email_is)) {
                                $emails[] = $e->email;
                            }                            
                        }
                    }
                }

                foreach ($emails as $email){

                    Log::info($email);

                    $response = $guzzle->get('https://'.$data_center.$this->base_url.'search-members?list_id='.$selected_audience_id.'&query='.$email, [
                        'auth' => [
                            'username', $gs->mailchimp_api_key
                        ]
                    ]);
                    $existing_members = json_decode((string)$response->getBody(), true);
                    if (count($existing_members['exact_matches']['members']) === 0)
                    {
                        $response = $guzzle->post('https://'.$data_center.$this->base_url.'lists/'.$selected_audience_id.'/members', [
                            'auth' => [
                                'username', $gs->mailchimp_api_key
                            ],
                            'body' => json_encode(
                                [
                                    'email_address' => $email,
                                    'status' => 'subscribed'
                                ]
                            )
                        ]);

                        $result = json_decode((string)$response->getBody(), true);

                        if ($response->getStatusCode() === 400){
                            if ($result['title'] === 'Invalid Resource')
                            {
                                continue;
                            }
                            else {
                                throw new Exception('Integration failed .. '.$result['detail']);
                            }
                        }
                    }
                }  

            }else{
                throw new Exception('Mailchimp is not connected');
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
