<?php

namespace Rkesa\Hr\Http\Controllers;

use App\AuthPhoto;
use App\EstimateGroupWorkerActivity;
use App\Group;
use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Estimate\Models\EstimateWorker;
use Rkesa\Hr\Models\Timetracker;
use Rkesa\Estimate\Models\Estimate;
use App\User;
use Illuminate\Support\Facades\Auth;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Rkesa\Estimate\Models\EstimateGroupWorker;

class TimeTrackerController extends Controller
{
    public function personal(Request $request)
    {
        return EstimateWorker::where('user_id', Auth::user()->id)->with('estimate.service')->get();
    }

    public function checkin(Request $request)
    {
        $res = (object) array();
        $res->errcode = 0;
        try {
            $user_id = Auth::user()->id;
            $user = User::find($user_id);
            $pin = $request->input('pin', 0);
            if ($pin > 0) {
                $user = User::where('pin', $pin)->where('active', true)->firstOrFail();
                $user_id = $user->id;
            }
            $type = $request['type'];
            $lat = $request->input('lat', 0);
            $lng = $request->input('lng', 0);
            $service_id = intval($request->input('service_id', 0));
            $estimate_id = intval($request->input('estimate_id', 0));
            $client_contact_id = intval($request->input('client_contact_id', 0));
            $branch_id = intval($request->input('branch_id', 0));
            $date_time_str = $request->input('date_time', null);

            $activity_str = $request->input('activities', '');

            $date_time = Carbon::now();
            if ($date_time_str != null) {
                $date_time = Carbon::parse($date_time_str);
            }

            $auth_photo = new AuthPhoto;
            $auth_photo->lat = $lat;
            $auth_photo->lng = $lng;
            $auth_photo->type = $type;

            $path = 'img/uploads/auth/';
            $name = "";
            $file = $request->file('file');
            $encodings = '';

            if ($file) {
                $name_orig = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $name = uniqid('', true) . '.' . $ext;
                $file->move($path, $name);

                $auth_photo->name = $name_orig;
                $auth_photo->url = '/' . $path . $name;
                // $ph_helper = new PhotoEncoder;
                // $encodings = $ph_helper->get_photo_encodings($auth_photo->url);

                if ($user->photo == '/img/no_profile_picture.png') {
                    $user->photo = '/' . $path . $name;
                    // $user->photo_encodings = $encodings;
                    $user->save();
                }
            }

            $egw_id = 0;

            $estimate_group_workers = EstimateGroupWorker::orderBy('id', 'DESC')->where('user_id', $user_id)->where('date', Carbon::now()->toDateString());
            if ($service_id != null && $service_id > 0) {
                $estimate_group_workers->where('service_id', $service_id);
            }
            if ($estimate_id != null && $estimate_id > 0) {
                $estimate_group_workers->where('estimate_id', $estimate_id);
            }
            if ($client_contact_id != null && $client_contact_id > 0) {
                $estimate_group_workers->where('client_contact_id', $client_contact_id);
            }
            if ($branch_id != null && $branch_id > 0) {
                $estimate_group_workers->where('branch_id', $branch_id);
            }

            $egw = $estimate_group_workers->first();

            if ($egw != null) {

                if ($type == "in" && $egw->date_start_after_lunch == null) {
                    if ($egw->date_end_before_lunch == null) {
                        $res->errcode = 3;
                        $res->errmess = "Error. For today no OUT was recorded before";
                        return response()->json($res);
                    }
                    $egw->date_start_after_lunch = $date_time;
                    $egw->save();
                    $egw_id = $egw->id;
                } else  if ($type == "in" && $egw->date_end_after_lunch == null) {
                    $res->errcode = 3;
                    $res->errmess = "Error. Your last action was IN. You should OUT.";
                    return response()->json($res);
                }

                if ($type == "out" && $egw->date_end_before_lunch == null) {
                    $egw->date_end_before_lunch = $date_time;
                    $egw->save();
                    $egw_id = $egw->id;
                } else if ($type == "out" && $egw->date_end_after_lunch == null) {
                    if ($egw->date_start_after_lunch == null) {
                        $res->errcode = 4;
                        $res->errmess = "Error. For today no IN was recorded before.";
                        return response()->json($res);
                    }
                    $egw->date_end_after_lunch = $date_time;
                    $egw->save();
                    $egw_id = $egw->id;
                }
            }
            if ($egw_id == 0 && $type == "out") {
                $res->errcode = 4;
                $res->errmess = "Error. For today no IN was recorded before.";
                return response()->json($res);
            }

            if ($egw_id == 0) {
                $egw = new EstimateGroupWorker;
                $egw->user_id = $user_id;
                $egw->date = Carbon::now()->toDateString();
                $egw->date_start_before_lunch = $date_time;
                $egw->estimate_id = $estimate_id == 0 ? null : $estimate_id;
                $egw->service_id = $service_id == 0 ? null : $service_id;
                $egw->client_contact_id = $client_contact_id == 0 ? null : $client_contact_id;
                $egw->branch_id = $branch_id == 0 ? null : $branch_id;
                $egw->save();
                $egw_id = $egw->id;
            }

            $knowns = [];
            array_push($knowns, $user->photo_encodings);

            $guzzle = new \GuzzleHttp\Client;

            /*$response = $guzzle->request('POST', env('ERP_FACE_RECOGNITION_URL', '127.0.0.1:8888').'/recognize', [
                'multipart' => [
                    [
                        'name' => 'unknown',
                        'contents' => file_get_contents(public_path(substr($auth_photo->url, 1))),
                        'filename' => substr($auth_photo->url, strrpos($auth_photo->url, '/')+1)
                    ],
                    [
                        'name' => 'known',
                        'contents' => file_get_contents(public_path(substr($user->photo, 1))),
                        'filename' => substr($user->photo, strrpos($user->photo, '/')+1)
                    ],
                ]
            ]);
    
            $response_string = (string) $response->getBody();   
            $response_decoded = json_decode($response_string, true);
            */

            $estimate_group_worker = EstimateGroupWorker::find($egw_id);

            if (strlen($activity_str) > 0) {
                $activities = json_decode($activity_str);
                if (count($activities) > 0) {
                    foreach ($activities as $activity) {
                        $egwa = new EstimateGroupWorkerActivity;
                        $egwa->estimate_group_worker_id = $estimate_group_worker->id;
                        $egwa->estimate_line_category_id = $activity->estimate_line_category_id;
                        $egwa->estimate_unit_id = $activity->estimate_unit_id;
                        $egwa->quantity = $activity->quantity;

                        $egwa->save();
                    }
                }
            }

            /*if ($response_decoded['result'] == 'ok'){
                $estimate_group_worker->is_suspicious = false;
            } else {
                $estimate_group_worker->is_suspicious = true;
                $res->errcode = 2;
                $res->errmess = 'Face not recognized';
            }*/
            $estimate_group_worker->is_suspicious = true;
            $estimate_group_worker->save();
            //$auth_photo->result = json_encode($response_decoded);
            $auth_photo->estimate_group_worker_id = $estimate_group_worker->id;
            $auth_photo->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function employee(Request $request)
    {
        $res = (object) array();
        $res->errcode = 0;
        try {
            $workerId = Auth::user()->id;

            $last_record = Timetracker::where('user_id', $workerId)->orderBy('start', 'desc')->first();

            if ($last_record) {
                $res->state = $last_record->finish == null ? 1 : 0;
                $res->total = '0';
            } else {
                $res->state = 0;
                $res->total = '0';
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function checkpoint(Request $request)
    {
        $res = (object) array();
        $res->errcode = 0;
        try {
            $workerId = Auth::user()->id;
            $estimateId = (int) $request['estimate'];
            $lat = $request['lat'];
            $lng = $request['lng'];

            $t = Timetracker::where('user_id', $workerId)->orderBy('start', 'desc')->first();

            if ($t == null || $t->finish != null) {
                $model = new Timetracker();
                $model->user_id = $workerId;
                $model->estimate_id = $estimateId == 0 ? null : $estimateId;
                $model->lat = $lat;
                $model->lng = $lng;
                $model->start = Carbon::now();
                $model->save();
            } else {
                $t->finish = Carbon::now();
                $t->save();
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function search(Request $request)
    {
        $workerId = (int) $request['worker'];
        $group_id = (int) $request['group_id'];
        $from = $request['w_range'][0];
        $to = $request['w_range'][1];
        $res = (object) array();
        $res->errcode = 0;
        $estimateId = (int) $request['estimate_id'];
        $client_contact_id = (int) $request['client_contact_id'];
        $service_id = (int) $request['service_id'];

        try {

            $data = EstimateGroupWorker::with(['estimate_group', 'user', 'auth_photos', 'estimate', 'estimate.service', 'client_contact']);

            if ($workerId != 0) {
                $data = $data->where('user_id', $workerId);
            }

            if ($estimateId != 0) {
                $data = $data->where('estimate_id', $estimateId);
            }

            if ($client_contact_id != 0) {
                $data = $data->where('client_contact_id', $client_contact_id);
            }

            if ($service_id != 0) {
                $data = $data->where('service_id', $service_id);
            }

            if ($group_id > 0) {
                $data = $data->whereHas('user', function ($query) use ($group_id) {
                    $query->where('group_id', $group_id);
                });
            }

            $values = $data->whereBetween('date_start_before_lunch', array($from, $to))->get()->toArray();

            $res->total = null;
            $res->intervals = $values;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function user_estimates(Request $request)
    {
        $res = (object) array();
        $res->errcode = 0;
        $workerId = (int) $request['worker'];
        try {
            $res->estimates = EstimateWorker::where('user_id', $workerId)->with('estimate.service')->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function logs(Request $request)
    {

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);

        $user_id = intval($request->input('user_id', '0'));

        $sort = $request->input('sort', 'created_at');
        if ($sort == '') {
            $sort = 'created_at';
        }
        $order = $request->input('order', 'desc');
        if ($order == '') {
            $order = 'desc';
        }


        $res = (object)array();
        $res->errcode = 0;
        try {
            $estimate_group_workers = EstimateGroupWorker::with('user', 'auth_photos');

            if ($user_id > 0) {
                $estimate_group_workers->where('user_id', $user_id);
            }

            $estimate_group_workers->orderBy($sort, $order);

            $res->total = $estimate_group_workers->count();

            $egws = $estimate_group_workers->offset($offset)->limit($limit)->get();

            $result = [];
            foreach ($egws as $egw) {
                if ($egw->date_start_before_lunch) {
                    $photo = [];
                    if (isset($egw->auth_photos[0])) {
                        $photo = [
                            'url' => $egw->auth_photos[0]->url,
                            'lat' => $egw->auth_photos[0]->lat,
                            'lng' => $egw->auth_photos[0]->lng,
                        ];
                    }
                    array_push($result, [
                        'date' => $egw->date,
                        'date_time' => $egw->date_start_before_lunch,
                        'type' => 'IN',
                        'user_id' => $egw->user->id,
                        'user_name' => $egw->user->name,
                        'photo' => $photo
                    ]);
                }
                if ($egw->date_end_before_lunch) {
                    $photo = [];
                    if (isset($egw->auth_photos[1])) {
                        $photo = [
                            'url' => $egw->auth_photos[1]->url,
                            'lat' => $egw->auth_photos[1]->lat,
                            'lng' => $egw->auth_photos[1]->lng,
                        ];
                    }
                    array_push($result, [
                        'date' => $egw->date,
                        'date_time' => $egw->date_end_before_lunch,
                        'type' => 'OUT',
                        'user_id' => $egw->user->id,
                        'user_name' => $egw->user->name,
                        'photo' => $photo
                    ]);
                }
                if ($egw->date_start_after_lunch) {
                    $photo = [];
                    if (isset($egw->auth_photos[2])) {
                        $photo = [
                            'url' => $egw->auth_photos[2]->url,
                            'lat' => $egw->auth_photos[2]->lat,
                            'lng' => $egw->auth_photos[2]->lng,
                        ];
                    }
                    array_push($result, [
                        'date' => $egw->date,
                        'date_time' => $egw->date_start_after_lunch,
                        'type' => 'IN',
                        'user_id' => $egw->user->id,
                        'user_name' => $egw->user->name,
                        'photo' => $photo
                    ]);
                }
                if ($egw->date_end_after_lunch) {
                    $photo = [];
                    if (isset($egw->auth_photos[3])) {
                        $photo = [
                            'url' => $egw->auth_photos[3]->url,
                            'lat' => $egw->auth_photos[3]->lat,
                            'lng' => $egw->auth_photos[3]->lng,
                        ];
                    }
                    array_push($result, [
                        'date' => $egw->date,
                        'date_time' => $egw->date_end_after_lunch,
                        'type' => 'OUT',
                        'user_id' => $egw->user->id,
                        'user_name' => $egw->user->name,
                        'photo' => $photo
                    ]);
                }
            }

            $res->rows = $result;
        } catch (\Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function update_settings(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            if ($request->filled('data_groups')) {
                foreach ($request['data_groups'] as $g) {
                    $group = Group::find($g['id']);
                    if ($group != null) {
                        $group->day_start_time = $g['day_start_time'];
                        $group->lunch_time = $g['lunch_time'];
                        $group->day_finish_time = $g['day_finish_time'];
                        $group->working_days = json_encode($g['working_days']);
                        $group->save();
                    }
                }
            }
            if ($request->filled('data_users')) {
                foreach ($request['data_users'] as $u) {
                    $user = User::find($u['id']);
                    if ($user != null) {
                        $user->day_start_time = $u['day_start_time'];
                        $user->lunch_time = $u['lunch_time'];
                        $user->day_finish_time = $u['day_finish_time'];
                        $user->working_days = json_encode($u['working_days']);
                        $user->save();
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

    public function report(Request $request)
    {
        $from = Carbon::parse($request['from']);
        $to = Carbon::parse($request['to']);
        $group_id = intval($request['group_id'], 0);

        $res = (object) array();
        $res->errcode = 0;

        try {
            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'created_at');
            if ($sort == '') {
                $sort = 'created_at';
            }
            $order = $request->input('order', 'asc');
            if ($order == '') {
                $order = 'asc';
            }

            $users = User::with(['estimate_group_workers', 'estimate_group_workers.auth_photos', 'user_salaries']);

            $users = $users->whereHas('estimate_group_workers', function ($query) use ($from, $to) {
                $query->whereBetween('date_start_before_lunch', array($from, $to));
            });

            if ($group_id > 0) {
                $users = $users->where('group_id', $group_id);
            }

            $users->orderBy($sort, $order);

            $res->total = $users->count();
            $res->rows = $users->offset($offset)->limit($limit)->get();
            foreach ($res->rows as $row) {
                $row['from'] =
                    $from->toDateString();;
                $row['to'] =
                    $to->toDateString();;
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
