<?php

namespace App\Http\Controllers;

use App\Http\Traits\SaasAuthTrait;
use App\User;
use Auth;
use Log;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class GoogleCalendarController extends Controller
{
    use SaasAuthTrait;

    public function auth()
    {
        $response_decoded = self::get_access_token();

        $user = Auth::user();

        return ['url' => 'https://saas.diga.pt/integrations/google?user_id='.$user->id.'&access_token='.$response_decoded['access_token'].'&mode=calendar'];
    }

    public static function synchronize($user_id)
    {
        $user = User::find($user_id);

        $response_decoded = self::get_access_token();

        $events = $user->events();

        $events_formatted = array_map(function($event){
            return self::format_event($event);
        }, $events->with('client_contact', 'event_type')->get()->toArray());

        $guzzle = new \GuzzleHttp\Client;

        $promise = $guzzle->postAsync(env('ERP_SAAS_URL', '').'/api/integrations/google_calendar/synchronize', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => [
                'user_id' => $user->id,
                'events' => $events_formatted
            ],
        ]);
        $promise->then(
            function (ResponseInterface $res) {
                $response_decoded = json_decode((string)$res->getBody(), true);
                Log::info($response_decoded);
            },
            function (RequestException $e) {
                Log::info($e->getMessage() . "\n");
                Log::info($e->getRequest()->getMethod());
            }
        );
        $promise->wait();

        $user->gc_enabled = true;
        $user->save();
    }

    public static function add_event($event)
    {
        $event = $event->toArray();

        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;

        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/integrations/google_calendar/add_event', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => [
                'user_id' => $event['user_id'],
                'event' => self::format_event($event)
            ],
        ]);
    }

    public static function update_event($event)
    {
        $event = $event->toArray();

        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;

        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/integrations/google_calendar/update_event', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => [
                'user_id' => $event['user_id'],
                'event' => self::format_event($event)
            ],
        ]);
    }

    public static function remove_event($event)
    {
        $event = $event->toArray();

        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;

        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/integrations/google_calendar/remove_event', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => [
                'user_id' => $event['user_id'],
                'event_id' => $event['id']
            ],
        ]);
    }

    private static function format_event($event)
    {
        return [
            'id' => $event['id'],
            'date' => $event['start'], // DateTime
            'description' => ($event['description'] ? $event['description'].' ' : '' ).env('APP_URL').'/clients/'.$event['client_contact_id'],
            'caption' => ($event['done'] == 1 ? '['.trans('template.Done').'] ' : '').$event['event_type']['title'].' '.$event['client_contact']['name'].' '.$event['client_contact']['surname'],
            // TODO: set color of event
        ];
    }
}
