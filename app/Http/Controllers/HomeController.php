<?php

namespace App\Http\Controllers;

use App\Events\CheckRealTime;
use App\GlobalSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Exception;
use App\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Log;
use Auth;
use Rkesa\Calendar\Models\Event;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Dashboard\Models\Dashboard;
use Rkesa\Project\Models\Project;
use Session;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Rkesa\Service\Models\Service;
use Rkesa\Service\Models\ServiceStateAction;
use App\Http\Traits\SMSTrait;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

class HomeController extends Controller
{
    use SMSTrait;

    const NOT_ALLOWED_EXTENTIONS = ['php', 'html', 'js'];

    public function my_dashboard(Request $request){
        $user = Auth::user();

        $dashboard = Dashboard::with(['entities','entities.state','entities.fields','entities.state', 'widgets', 'widgets.state'])->find($user->dashboard_id);

        $dashboard->entity_default_rows_count = config('dashboard.stage.rows.count');

        return $dashboard;
    }

    public function login_with_photo_test(Request $request){
        return view('settings/auth_photos_test');
    }

    public function start_tour(Request $request){
        $user = Auth::user();
        $user->show_product_tour = true;
        $user->save();
    }

    public function end_tour(Request $request){
        $user = Auth::user();
        $user->show_product_tour = false;
        $user->save();
    }

    public function colors(Request $request) {
        $gs = GlobalSettings::first();
        $contents = view('colors')->with('color1', $gs->color1)
                                        ->with('color1rgb', self::hex2RGB($gs->color1))
                                        ->with('color2', $gs->color2)
                                        ->with('color3', $gs->color3)
                                        ->with('color3rgb', self::hex2RGB($gs->color3))
                                        ->with('color4', $gs->color4)
                                        ->with('color5', $gs->color5);
        return response($contents)->header('Content-Type', 'text/css');
    }

    /*
     * Temprary saves image and return url
     */
    public function photo_upload(Request $request){
        $res = (object)array();
        $res->errcode = 0;
        try {
            $file = $request->file('files');
            $ext = $file->getClientOriginalExtension();
            if (array_search($ext, $this::NOT_ALLOWED_EXTENTIONS) !== false) {
                throw new Exception('Not allowed file extention');
            }
            $path = 'img/uploads/temp/' . uniqid('', true) . '.' . $ext;
            $image = Image::make($file);
            if ($request->toCropImgW) {
                $w = (int)$request->toCropImgW;
                $h = (int)$request->toCropImgH;
                $x = (int)$request->toCropImgX;
                $y = (int)$request->toCropImgY;
                $image = $image->crop($w, $h, $x, $y);
            }
            $image->save($path);
            $res->url = '/'.$path;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage().' Maybe you forgot to resize image?';
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /*
     * Temprary saves file and return url
     */
    public function file_upload(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $file = $request->file('files');
            $ext = $file->getClientOriginalExtension();
            if (array_search($ext, $this::NOT_ALLOWED_EXTENTIONS) !== false) {
                throw new Exception('Not allowed file extention');
            }

            $name_orig = substr($file->getClientOriginalName(), 0, strrpos($file->getClientOriginalName(), "." . $ext)); // we don't use pathinfo because it is locale aware
            $path = 'img/uploads/temp/';
            $name = uniqid('', true) . '.' . $ext;
            $file->move($path, $name);
            $res->url = '/' . $path . $name;
            $res->name = $name_orig.'.'.$ext;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function action_sms(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $action = ServiceStateAction::find($request['action_id']);
            if ($action) {
                $global_data = $request->input('global_data', null);
                $service = Service::find($request['service_id']);
                if ($service) {
                    $cc = $service->client_contact;
                    $replace_from = array('{sent_estimate_numbers}', '{event_start}',
                        '{client_email}', '{estimate_summ}', '{paid_summ}', '{left_summ}');
                    $global_event_start = is_array($global_data['event_start']) ? (new DateTime($global_data['event_start']['date']))->format('Y-m-d H:i') : '';
                    $replace_to = array($global_data['sent_estimate_numbers'], $global_event_start,
                        $cc->email, $service->estimate_summ, $service->paid_summ, $service->estimate_summ - $service->paid_summ);

                    $data = array(
//                        'from' => $action->sms_from,
                        'to' =>'',
                        'text' => str_replace($replace_from, $replace_to, $action->sms_text)
                    );
                    $response = array();
                    if ($action->sms_type == 1) {
                        foreach ($cc->client_contact_phones as $phone) {
                            $data['to'] = $phone->phone_number;
                            array_push($response, self::send_sms($data));
                        }
                    } else {
                        $data['to'] = $action->sms_to;
                        array_push($response, self::send_sms($data));
                    }
                    $res->info = $response;
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

    public function mark_as_read(Request $request)
    {
        // special notif TOGGLE
        $not = DatabaseNotification::find($request['id']);
        if ($not->read()){
            $not->read_at = null;
            $not->save();
        }else{
            $not->markAsRead();
        }
    }

    public function mark_all_as_read(Request $request)
    {
        $user = Auth::user();
        $nots = DatabaseNotification::where('notifiable_id', $user->id)->where('read_at', null)->get();

        foreach($nots as $not)
        {
            $not->markAsRead();
        }
    }

    public function last_notifications(Request $request)
    {
        $user = Auth::user();
        $nots = $user->notifications->take(5);
        foreach($nots as $not){
            $not = $not->type::fromDatabase($not);
        }
        return response()->json(['notifs' => $nots, 'not_count' => $user->unreadNotifications->count()]);
    }

    public function all_notifications(Request $request)
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);

        $res = (object)array();
        $res->errcode = 0;
        try{
            $user = Auth::user();
            $nots = $user->notifications()->offset($offset)->limit($limit)->get();
            foreach($nots as $not){
                $not = $not->type::fromDatabase($not);
                $not->read = $not->read_at != null;
            }

            $res->total = $user->notifications()->count();
            $res->rows = $nots;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function clear_popups(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            ClientContact::where('show_notification', true)->update(['show_notification' => false]);
            ClientContact::where('show_fb_notification', true)->update(['show_fb_notification' => false]);
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function check_real_time()
    {
        $user = Auth::user();
        broadcast(new CheckRealTime($user));
    }

    /**
     * Convert a hexa decimal color code to its RGB equivalent
     * http://php.net/manual/en/function.hexdec.php
     *
     * @param string $hexStr (hexadecimal color value)
     * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
     * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
     * @return array or string (depending on second parameter. Returns False if invalid hex color value)
     */
    private function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
        $rgbArray = array();
        if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false; //Invalid hex color code
        }
        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
    }
}
