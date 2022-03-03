<?php

namespace Rkesa\Hr\Http\Controllers;

use Log;
use App\Chat;
use App\Role;
use App\User;
use App\Group;
use Exception;
use UrlSigner;
use Carbon\Carbon;
use App\ChatMember;
use App\UserContract;
use App\GlobalSettings;
use App\Events\UsersChanged;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Events\ProfileChanged;
use Rkesa\Calendar\Models\Event;
use Rkesa\Hr\Models\UserDocument;
use Rkesa\Service\Models\Service;
use App\Http\Helpers\PhotoEncoder;
use Illuminate\Support\Facades\DB;
use Rkesa\Hr\Models\UserExperience;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Rkesa\Dashboard\Models\Dashboard;
use Rkesa\Hr\Http\Helpers\HrPDFCreator;
use App\Http\Helpers\TelegramMadelineProto;
use App\Http\Traits\Auth0Trait;
use App\UserSalary;
use Illuminate\Support\Facades\Log as FacadesLog;

class HrController extends Controller
{
    use Auth0Trait;

    public function user_info_by_pin(Request $request, $pin)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = User::select('id', 'email', 'name', 'photo')->where('pin', $pin)->where('active', true)->firstOrFail();
            $res->user = $user;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function change_password(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user_id = $request->input('user_id', 0);
            $user = User::find($user_id);
            if ($user != null) {
                self::ask_change_password($user->email);
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function get_blank_pdf_link(Request $request)
    {
        $user = Auth::user();

        return response()->json(['link' => UrlSigner::sign(env('APP_URL') . '/api/users/pdf', Carbon::now()->addHours(9))]);
    }

    public function blank(Request $request)
    {
        $creator = new HrPDFCreator;
        $format = $request->input('format', 'pdf');
        switch ($format) {
            case 'html':
                $result = $creator->render_html();
                return Response($result);
                break;
            case 'pdf':
                $result =  $creator->render_pdf();
                $headers = [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="blank.pdf"',
                    'Accept-Ranges' => 'bytes',
                    'Content-Length' => strlen($result)
                ];
                return Response($result, 200, $headers);
                break;
        }
    }

    public function team(Request $request)
    {
        $search = $request->input('search', false);
        $users = Group::with('users')->where('name', 'LIKE', '%' . $search . '%')->get();
        return response()->json($users);
    }

    public function search(Request $request)
    {
        $search = $request->input('query', false);
        $users = User::where('name', 'LIKE', '%' . $search . '%')->get();
        return response()->json($users);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $fields = $request->input('fields', '*');
            $fields_array = explode(",", $fields);

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

            $active = $request->input('active', 2);

            $users = User::users()->select($fields_array);

            $query = $request->input('search', '');
            if ($query != '') {
                // parentheses in condition
                $users->where(function ($c) use ($query) {
                    $c->where('name', 'like', '%' . $query . '%')
                        ->orWhere('email', 'like', '%' . $query . '%');
                });
            }

            if ($active != 2) {
                $users->where('active', $active);
            }

            $users->orderBy($sort, $order);

            $res->total = $users->count();
            $res->rows = $users->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            if (User::where('email', $request['email'])->count() > 0) {
                $res->errcode = 2;
            } else {
                $user = new User;
                $fields = [
                    // system
                    'email', 'email_password',
                    // personal
                    'name', 'birthday', 'additional_email', 'cell_phone', 'cell_phone', 'home_phone', 'address', 'postal', 'education', 'nation', 'languages', 'dependencies', 'marital_status_id',
                    // work
                    'salary_type', 'salary', 'active', 'position', 'vacation_days_left',
                    // law
                    'identical_number', 'identical_type_id', 'identical_valid_to', 'tax_number', 'bank_number', 'driver_number', 'insurance_number', 'social_security_number', 'medical_date',
                    // emergency
                    'emergency_name', 'emergency_contact', 'emergency_relation', 'pin'
                ];
                foreach ($fields as $field) {
                    $user[$field] = $request[$field];
                }
                $user->password = bcrypt($request['password']);
                $gs = GlobalSettings::first();
                $user->site_language = $gs->default_language;
                $user->group_id = 1;

                $pins = User::pluck('pin')->toArray();
                while (true) {
                    $pin = rand(1000, 9999);
                    if (in_array($pin, $pins)) {
                        continue;
                    } else {
                        $user->pin = $pin;
                        break;
                    }
                }

                $management = self::get_management_token();
                $token = $management->access_token;

                $auth0_users = self::user_exists($token, $user->email);
                if (count($auth0_users) > 0) {
                    $user->sub = $auth0_users[0]->user_id;
                } else {
                    $auth0 = self::post_user($request['email'], $request['password'], $request['name']);
                    $user->sub = 'auth0|' . $auth0->_id;
                }

                $to_replace = array("http://", "https://", "www.");
                $role = str_replace($to_replace, "", env('APP_URL'));

                $roles = self::get_roles($token, $role);
                if (count($roles) === 0) {
                    throw new Exception("Auth0 role not found");
                }

                $role_add = self::add_role_to_user($user->sub, $roles[0]->id, $token);

                $user->save();

                $user_salary = new UserSalary;
                $user_salary->user_id = $user->id;
                $user_salary->salary_type = $user->salary_type;
                $user_salary->amount = $user->salary;
                $user_salary->start = Carbon::today();
                $user_salary->save();

                //user contracts
                $user_contracts = [];
                foreach ($request['user_contracts'] as $work) {
                    array_push($user_contracts, UserContract::create($work));
                }
                $user->user_contracts()->saveMany($user_contracts);

                //user documents
                $user_documents = [];
                foreach ($request['user_documents'] as $work) {
                    array_push($user_documents, UserDocument::create($work));
                }
                $user->user_documents()->saveMany($user_documents);

                // Work before
                $work_before = [];
                foreach ($request['work_before'] as $work) {
                    array_push($work_before, UserExperience::create($work));
                }
                $user->work_before()->saveMany($work_before);
                // Education before
                $educ_before = [];
                foreach ($request['educ_before'] as $educ) {
                    array_push($educ_before, UserExperience::create($educ));
                }
                $user->educ_before()->saveMany($educ_before);
                // Photo
                if ($request['photo'] == '/img/no_profile_picture.png') {
                    $user->photo = $request['photo'];
                } else {
                    // copy from temp
                    $old_file = substr($request['photo'], 1);
                    $filename = pathinfo($request['photo'], PATHINFO_BASENAME);
                    $new_file = 'img/uploads/user/' . $filename;
                    rename(public_path() . DIRECTORY_SEPARATOR . $old_file, public_path() . DIRECTORY_SEPARATOR . $new_file);
                    $user->photo = '/' . $new_file;
                    $ph_helper = new PhotoEncoder;
                    $user->photo_encodings = $ph_helper->get_photo_encodings($user->photo);
                }
                $user->save();
                $user->create_permissions();

                // Add new user to common company's chat
                $member = new ChatMember;
                $member->user_id = $user->id;
                $member->chat_id = 1;
                $member->save();

                // Add new user to techsupport's chat
                $chat = new Chat;
                $chat->type = 3;
                $chat->name = trans('template.Tech_support_chat');
                $chat->save();

                $member = new ChatMember;
                $member->user_id = $user->id;
                $member->chat_id = $chat->id;
                $member->save();

                $member2 = new ChatMember;
                $member2->user_id = 0;
                $member2->chat_id = $chat->id;
                $member2->save();

                $res->id = $user->id;
                $res->pin = $user->pin;
                broadcast(new UsersChanged());
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $user = User::with(['educ_before', 'work_before', 'user_contracts', 'user_documents', 'estimate_group_workers', 'estimate_group_workers.estimate_group', 'estimate_group_workers.estimate_group.estimate', 'estimate_group_workers.estimate_group.estimate.service', 'estimate_group_workers.estimate', 'estimate_group_workers.estimate.service'])->findOrFail($id);
        $current_user = Auth::user();
        if (!$current_user->can_with_user('read', $user)) {
            return response('', 403);
        }

        return $user;
    }

    public function get_users_pdf_link(Request $request, $id)
    {
        $user = Auth::user();

        return response()->json(['link' => UrlSigner::sign(env('APP_URL') . '/api/users/pdf/' . $id, Carbon::now()->addHours(9))]);
    }

    public function card(Request $request, $id)
    {
        $user = User::find($id);
        $creator = new HrPDFCreator;
        $format = $request->input('format', 'pdf');
        switch ($format) {
            case 'html':
                $result = $creator->render_html($user);
                return Response($result);
                break;
            case 'pdf':
                $result =  $creator->render_pdf($user);
                $headers = [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $user->name . '.pdf"',
                    'Accept-Ranges' => 'bytes',
                    'Content-Length' => strlen($result)
                ];
                return Response($result, 200, $headers);
                break;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $u = User::where('email', $request['email'])->first();
            if ($u != null && $u->id != $id) {
                $res->errcode = 2;
            } else {
                $user = User::find($id);
                $current_user = Auth::user();
                if (!$current_user->can_with_user('update', $user)) {
                    return response('', 403);
                }

                $fields = [
                    // system
                    'email',
                    // personal
                    'name', 'birthday', 'additional_email', 'cell_phone', 'formatted_cell_phone', 'home_phone', 'address', 'postal', 'education', 'nation', 'languages', 'dependencies', 'marital_status_id',
                    // work
                    'salary_type', 'salary', 'active', 'position', 'vacation_days_left',
                    // law
                    'identical_number', 'identical_type_id', 'identical_valid_to', 'tax_number', 'bank_number', 'driver_number', 'insurance_number', 'social_security_number', 'medical_date',
                    // emergency
                    'emergency_name', 'emergency_contact', 'emergency_relation', 'pin'
                ];

                $gs = GlobalSettings::first();
                if ($gs->telegram_enabled == true) {
                    $tg_helper = new TelegramMadelineProto;
                    $first_name = "";
                    $last_name = "";
                    $pieces = explode(" ", $user->name);
                    if (count($pieces) > 1) {
                        $first_name = $pieces[0];
                        $last_name = $pieces[1];
                    } else {
                        $first_name = $user->name;
                        $last_name = $user->name;
                    }
                    $tg_id = $tg_helper->add_to_contacts($request['tg_username'], $first_name, $last_name, $user->formatted_cell_phone);

                    if ($tg_id > 0) {
                        $user->tg_id = $tg_id;
                    }
                }

                if ($user->salary != $request["salary"] || $user->salary_type != $request["salary_type"]) {
                    $old_user_salary = UserSalary::where('user_id', $user->id)->where('end', null)->firstOrFail();
                    $old_user_salary->end = Carbon::today();
                    $old_user_salary->save();

                    $user_salary = new UserSalary;
                    $user_salary->user_id = $user->id;
                    $user_salary->salary_type = $user->salary_type;
                    $user_salary->amount = $user->salary;
                    $user_salary->start = Carbon::today();
                    $user_salary->save();
                }

                foreach ($fields as $field) {
                    $user[$field] = $request[$field];
                }

                if ($request->filled('password')) {
                    $user->password = bcrypt($request['password']);
                }

                if ($request->filled('email_password')) {
                    $user->email_password = $request['email_password'];
                }

                //user contracts
                $user->user_contracts()->delete();
                $user_contracts = [];
                if ($request->exists('user_contracts')) {
                    foreach ($request['user_contracts'] as $work) {
                        array_push($user_contracts, UserContract::create($work));
                    }
                }
                $user->user_contracts()->saveMany($user_contracts);

                //user documents
                $user->user_documents()->delete();
                $user_documents = [];
                if ($request->exists('user_documents')) {
                    foreach ($request['user_documents'] as $work) {
                        array_push($user_documents, UserDocument::create($work));
                    }
                }
                $user->user_documents()->saveMany($user_documents);

                // Work before
                $user->work_before()->delete();
                $work_before = [];
                if ($request->exists('work_before')) {
                    foreach ($request['work_before'] as $work) {
                        array_push($work_before, UserExperience::create($work));
                    }
                }
                $user->work_before()->saveMany($work_before);
                // Education before
                $user->educ_before()->delete();
                $educ_before = [];
                if ($request->exists('educ_before')) {
                    foreach ($request['educ_before'] as $educ) {
                        array_push($educ_before, UserExperience::create($educ));
                    }
                }
                $user->educ_before()->saveMany($educ_before);
                // Photo
                if ($request['photo'] !== $user->photo) {
                    // remove old
                    $old_filepath = public_path() . $user->photo;
                    if ($user->photo !== '/img/no_profile_picture.png' && file_exists($old_filepath)) {
                        unlink($old_filepath);
                    }
                    // copy new from temp
                    $old_file = substr($request['photo'], 1);
                    $filename = pathinfo($request['photo'], PATHINFO_BASENAME);
                    $new_file = 'img/uploads/user/' . $filename;
                    rename(public_path() . DIRECTORY_SEPARATOR . $old_file, public_path() . DIRECTORY_SEPARATOR . $new_file);
                    $user->photo = '/' . $new_file;
                    $ph_helper = new PhotoEncoder;
                    $user->photo_encodings = $ph_helper->get_photo_encodings($user->photo);
                }
                $user->save();
                broadcast(new UsersChanged());
                broadcast(new ProfileChanged($user));
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $removing_user = User::find($id);
        if (!$user->can_with_user('delete', $removing_user) || $user->id == $id) {
            return response('', 403);
        }

        //        $exist_in_services = Service::where('responsible_user_id', $id)->count() > 0;
        //        if ($exist_in_services){
        //            throw new Exception('User is responsible at least in one service');
        //        }
        //
        //        $exist_in_services = Event::where('user_id', $id)->count() > 0;
        //        if ($exist_in_services){
        //            throw new Exception('User is responsible at least in one task');
        //        }

        $removing_user->delete();
        //        Service::where('responsible_user_id', $id)->update();
        //        ClientContactPhone::where('client_contact_id', $id)->delete();
    }
}
