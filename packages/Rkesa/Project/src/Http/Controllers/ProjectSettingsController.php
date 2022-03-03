<?php

namespace Rkesa\Project\Http\Controllers;

use App\Events\ProjectSettingsChanged;
use App\GlobalSettings;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Exception;
use Log;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientReferrer;
use Rkesa\Estimate\Models\EstimateDocument;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\Resource;
use Rkesa\Project\Models\LegalEntity;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\ProjectAutotask;
use Rkesa\Project\Models\ProjectAutotaskRecipient;
use Rkesa\Project\Models\ProjectManufacturer;
use Rkesa\Project\Models\ProjectNotification;
use Rkesa\Project\Models\ProjectNotificationRecipient;
use Rkesa\Project\Models\ProjectStatus;
use Rkesa\Project\Models\ProjectType;

class ProjectSettingsController extends Controller
{
    public function save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            // Validations
            foreach($request['removed_types'] as $i){
                if (Project::where('project_type_id', $i)->count() > 0){
                    throw new Exception('There are projects with this type. Cannot remove.');
                }
            }
            foreach($request['removed_statuses'] as $i){
                if (Project::where('project_status_id', $i)->count() > 0){
                    throw new Exception('There are projects with this status. Cannot remove.');
                }
            }
            if (count($request['statuses']) == 0){
                throw new Exception('There must be at least one project status');
            }
            if (count($request['types']) == 0){
                throw new Exception('There must be at least one project type');
            }
            // Actions
            foreach($request['removed_types'] as $i){
                if ($i != 0){
                    ProjectType::find($i)->delete();
                }
            }
            foreach($request['types'] as $i){
                if ($i['id'] == 0){
                    $r = new ProjectType;
                    $r->name = $i['name'];
                    $r->save();
                }else {
                    $r = ProjectType::find($i['id']);
                    $r->name = $i['name'];
                    $r->save();
                }
            }
            //
            foreach($request['removed_statuses'] as $i){
                if ($i != 0){
                    ProjectStatus::find($i)->delete();
                }
            }
            foreach($request['statuses'] as $i){
                if ($i['id'] == 0){
                    $r = new ProjectStatus;
                    $r->fill($i);
                    $r->save();
                }else {
                    $r = ProjectStatus::find($i['id']);
                    $r->fill($i);
                    $r->save();
                }
            }
            foreach($request['notifications'] as $n){
                $not = ProjectNotification::find($n['id']);
                if (array_key_exists('removed_recipients', $n)) {
                    foreach ($n['removed_recipients'] as $s) {
                        if ($s != 0) {
                            ProjectNotificationRecipient::find($s)->delete();
                        }
                    }
                }
                foreach($n['recipients'] as $rec){
                    if ($rec['id'] == 0){
                        $not_rec = new ProjectNotificationRecipient;
                        $not_rec->fill($rec);
                        $not_rec->project_notification_id = $not->id;
                        $not_rec->save();
                    }else{
                        $not_rec = ProjectNotificationRecipient::find($rec['id']);
                        $not_rec->fill($rec);
                        $not_rec->project_notification_id = $not->id;
                        $not_rec->save();
                    }
                }
            }
            foreach($request['autotasks'] as $n){
                $not = ProjectAutotask::find($n['id']);
                if (array_key_exists('removed_recipients', $n)) {
                    foreach ($n['removed_recipients'] as $s) {
                        if ($s != 0) {
                            ProjectAutotaskRecipient::find($s)->delete();
                        }
                    }
                }
                foreach($n['recipients'] as $rec){
                    if ($rec['id'] == 0){
                        $not_rec = new ProjectAutotaskRecipient;
                        $not_rec->fill($rec);
                        $not_rec->project_autotask_id = $not->id;
                        $not_rec->save();
                    }else{
                        $not_rec = ProjectAutotaskRecipient::find($rec['id']);
                        $not_rec->fill($rec);
                        $not_rec->project_autotask_id = $not->id;
                        $not_rec->save();
                    }
                }
            }
            broadcast(new ProjectSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
