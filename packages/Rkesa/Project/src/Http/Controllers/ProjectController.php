<?php

namespace Rkesa\Project\Http\Controllers;

use App\User;
use Exception;
use App\UserRole;
use Carbon\Carbon;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Calendar\Models\Event;
use Rkesa\Project\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\InnerPayment;
use Rkesa\Project\Models\ProjectFactDeliveryEntity;
use Rkesa\Project\Models\ProjectStatus;
use Rkesa\Project\Models\ProjectCarrier;
use Rkesa\Project\Models\ClientEquipment;
use Rkesa\Project\Models\ContractPayment;
use Rkesa\Project\Models\InnerSpecification;
use Rkesa\Project\Models\InstallationDocument;
use Rkesa\Project\Models\ManufacturerAdditionalContract;
use Rkesa\Project\Models\ManufacturerCommissionRelation;
use Rkesa\Project\Models\ProjectAutotask;
use Rkesa\Project\Models\CommissionPayment;
use Rkesa\Project\Models\ManufacturerOrder;
use Rkesa\Project\Models\TechnicalDocument;
use Rkesa\Project\Models\AdditionalDocument;
use Rkesa\Project\Events\ProjectChangedEvent;
use Rkesa\Project\Models\ManufacturerPayment;
use Rkesa\Project\Models\ProjectManufacturer;
use Rkesa\Project\Models\ProjectSpecification;
use Rkesa\Project\Models\TransportationPayment;
use Rkesa\Project\Models\InstallationPaymentStep;
use Rkesa\Project\Models\ManufacturerSpecification;
use Rkesa\Project\Models\ProjectAdditionalContract;
use Rkesa\Project\Models\CommissionRelationDocument;
use Rkesa\Project\Models\ProjectSpecificationEquipment;
use Rkesa\Project\Models\SpecificationAdditionalContract;
use Rkesa\Project\Notifications\ProjectApplicationsAdded;
use Rkesa\Project\Http\Helpers\NotificationsAndAutotasksHelper;
use Rkesa\Project\Models\AdditionalExpense;
use Rkesa\Project\Models\ProjectManufacturerAdditionalDocument;
use Rkesa\Project\Models\SidePayment;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == ''){ $sort = 'created_at'; }
        $order = $request->input('order', 'asc');
        if ($order == ''){ $order = 'asc'; }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {
            $projects = Project::select($fields_array)->with(['manufacturers.orders', 'client']);

            $responsible_user_id = intval($request->input('responsible_user_id', '0'));
            $project_status_id = intval($request->input('project_status_id', '0'));
            if ($responsible_user_id != 0) {
                $projects->where('responsible_user_id', $responsible_user_id);
            }
            if ($project_status_id != 0) {
                $projects->where('project_status_id', $project_status_id);
            }

            $query = $request->input('search', '');
            if ($query != '') {
                // parentheses in condition
                $projects->where(function ($c) use ($query) {
                    $c
                        ->where('name', 'like', '%' . $query . '%')
                        ->orWhere('contract_number', 'like', '%' . $query . '%')
//                        ->orWhere('finished_at', 'like', '%' . $query . '%')
                        ->orWhereHas('manufacturers', function($m) use ($query){
                            $m->whereHas('orders', function($o) use ($query){
                              $o->where('name', 'like', '%' . $query . '%');
                            });
                        });
                });
            }

            if ($sort == 'responsible_user_id') { // because we need to sort by responsible name, not by responsible id
                $res->total = $projects->count();
                $res->rows = array_values($projects->get()->sortBy(function ($project) {
                    return $project->responsible_user->name;
                }, SORT_REGULAR, $order == 'desc')->splice($offset, $limit)->toArray());
            } else if($sort == 'orders'){
                $res->total = $projects->with('manufacturers.orders')->count();
                $res->rows = array_values($projects->with('manufacturers.orders')->get()->sortBy(function ($project) {
                    return $project->where('manufacturers.orders');
                }, SORT_REGULAR, $order == 'desc')->splice($offset, $limit)->toArray());
            } else {
                $projects->orderBy($sort, $order);

                $res->total = $projects->count();
                $res->rows = $projects->offset($offset)->limit($limit)->get();
            }
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
        try{
            $project = new Project;
            $project->fill($request->all());
            $project->project_status_id = ProjectStatus::first()->id;
            $project->save();
            $gs = GlobalSettings::first();
            foreach($request['contract_payments'] as $p){
                $payment = new ContractPayment;
                $payment->fill($p);
                $payment->project_id = $project->id;
                $payment->save();
            }
            foreach($request['manufacturers'] as $p){
                $man = new ProjectManufacturer;
                $man->manufacturer_id = $p['id'];
                $man->project_id = $project->id;
                $man->limit_forming_type = 0;
                $man->limit_forming_date = 0;
                $man->contract_currency = $gs->currency; // if you change this line, dont forget to change the line in update method
                $man->inner_contract_currency = $gs->currency;
                $man->save();

                foreach ($request['commissioners'] as $r) {
                    $relation = new ManufacturerCommissionRelation;
                    $relation->fill($r);
                    $relation->project_manufacturer_id = $man->id;
                    $relation->currency = $gs->currency; // if you change this line, dont forget to change the line in update method
                    $relation->save();
                }
            }
            $res->id = $project->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();
            $project = Project::find($id);
            $old_project = $project->toArray();
//            if (!$user->can_with_project('update', 1)) {
//                return Response('',403);
//            }

            $project->fill($request->all());
            $project->save();

            if ($request->filled('removed_commissioners')) {
                foreach ($request['removed_commissioners'] as $p) {
                    $m_ids = $project->manufacturers->pluck('id')->toArray();
                    $mcr_ids = ManufacturerCommissionRelation::whereIn('project_manufacturer_id', $m_ids)->where('legal_entity_id', $p)->pluck('id')->toArray();
                    ManufacturerCommissionRelation::whereIn('id', $mcr_ids)->delete();
                    CommissionPayment::whereIn('manufacturer_commission_relation_id', $mcr_ids)->delete();
                }
            }
            if ($request->filled('removed_manufacturers')) {
                foreach ($request['removed_manufacturers'] as $p) {
                    ProjectManufacturer::find($p)->delete();
                    $mcr_ids = ManufacturerCommissionRelation::where('project_manufacturer_id', $p)->pluck('id')->toArray();
                    ManufacturerCommissionRelation::whereIn('id', $mcr_ids)->delete();
                    CommissionPayment::whereIn('manufacturer_commission_relation_id', $mcr_ids)->delete();
                }
            }
            if ($request->filled('removed_contract_steps')) {
                foreach ($request['removed_contract_steps'] as $p) {
                    if ($p['id'] != 0) {
                        ContractPayment::find($p['id'])->delete();
                    }
                }
            }
            if ($request->filled('removed_specifications')) {
                foreach ($request['removed_specifications'] as $s) {
                    if ($s != 0) {
                        ProjectSpecificationEquipment::where('project_specification_id', $s)->delete();
                        ProjectSpecification::find($s)->delete();
                    }
                }
            }
            if ($request->filled('removed_add_docs')) {
                foreach ($request['removed_add_docs'] as $s) {
                    if ($s != 0) {
                        AdditionalDocument::find($s)->delete();
                    }
                }
            }
            if ($request->filled('removed_addit_contracts')) {
                foreach ($request['removed_addit_contracts'] as $s) {
                    if ($s != 0) {
                        ProjectAdditionalContract::find($s)->delete();
                    }
                }
            }
            if ($request->filled('removed_installation_add_docs')) {
                foreach ($request['removed_installation_add_docs'] as $s) {
                    if ($s != 0) {
                        InstallationDocument::find($s)->delete();
                    }
                }
            }
            if ($request->filled('removed_fact_deliveries')) {
                foreach ($request['removed_fact_deliveries'] as $s) {
                    if ($s != 0) {
                        ProjectFactDeliveryEntity::find($s)->delete();
                    }
                }
            }
            foreach ($request['fact_delivery_entities'] as $p) {
                if ($p['id'] != 0) {
                    $d = ProjectFactDeliveryEntity::find($p['id']);
                    $d->fill($p);
                    $d->save();
                } else {
                    $d = new ProjectFactDeliveryEntity;
                    $d->fill($p);
                    $d->project_id = $project->id;
                    $d->save();
                }
            }
            foreach ($request['installation_documents'] as $p) {
                if ($p['id'] != 0) {
                    $payment = InstallationDocument::find($p['id']);
                    $payment->fill($p);
                    $payment->save();
                } else {
                    $payment = new InstallationDocument;
                    $payment->fill($p);
                    $payment->project_id = $project->id;
                    $payment->save();
                }
            }
            foreach ($request['additional_contracts'] as $p) {
                if ($p['id'] != 0) {
                    $payment = ProjectAdditionalContract::find($p['id']);
                    $payment->fill($p);
                    $payment->save();
                } else {
                    $payment = new ProjectAdditionalContract;
                    $payment->fill($p);
                    $payment->project_id = $project->id;
                    $payment->save();
                }
            }
            foreach ($request['additional_documents'] as $p) {
                if ($p['id'] != 0) {
                    $payment = AdditionalDocument::find($p['id']);
                    $payment->fill($p);
                    $payment->save();
                } else {
                    $payment = new AdditionalDocument;
                    $payment->fill($p);
                    $payment->project_id = $project->id;
                    $payment->save();
                }
            }
            foreach ($request['specifications'] as $s) {
                if ($s['id'] != 0) {
                    $spec = ProjectSpecification::find($s['id']);
                    $spec->fill($s);
                    $spec->save();
                    if (array_key_exists('removed_equipment', $s)){
                        foreach ($s['removed_equipment'] as $eq_id){
                            if ($eq_id != 0) {
                                ProjectSpecificationEquipment::find($eq_id)->delete();
                            }
                        }
                    }
                } else {
                    $spec = new ProjectSpecification;
                    $spec->fill($s);
                    $spec->project_id = $project->id;
                    $spec->save();
                }
                foreach($s['equipment'] as $eq){
                    if ($eq['id'] != 0){
                        $equip = ProjectSpecificationEquipment::find($eq['id']);
                        $equip->fill($eq);
                        $equip->save();
                    }else{
                        $equip = new ProjectSpecificationEquipment;
                        $equip->fill($eq);
                        $equip->project_specification_id = $spec->id;
                        $equip->save();
                    }
                }
            }
            foreach ($request['contract_payments'] as $i => $p) {
                if ($p['id'] != 0) {
                    $payment = ContractPayment::find($p['id']);
                    $old_payment = $payment->toArray();
                    $payment->fill($p);
                    $payment->save();
                    if ($payment->accounting_statement_file != null && $old_payment['accounting_statement_file'] != $payment->accounting_statement_file){
                        if ($i == 0){ // prepayment
                            NotificationsAndAutotasksHelper::send_notifications($project->id, 'Prepayment_filled', $user);
                        }else{
                            NotificationsAndAutotasksHelper::send_notifications($project->id, 'Payment_filled', $user);
                        }
                    }
                } else {
                    $payment = new ContractPayment;
                    $payment->fill($p);
                    $payment->project_id = $project->id;
                    $payment->save();
                    if ($payment->accounting_statement_file != null){
                        if ($i == 0){ // prepayment
                            NotificationsAndAutotasksHelper::send_notifications($project->id, 'Prepayment_filled', $user);
                        }else{
                            NotificationsAndAutotasksHelper::send_notifications($project->id, 'Payment_filled', $user);
                        }
                    }
                }
            }
            $gs = GlobalSettings::first();
            foreach ($request['manufacturers'] as $m) {
                if (array_key_exists('is_new', $m)) {
                    $man = new ProjectManufacturer;
                    $man->manufacturer_id = $m['id'];
                    $man->project_id = $project->id;
                    $man->limit_forming_type = 0;
                    $man->limit_forming_date = 0;
                    $man->contract_currency = $gs->currency; // if you change this line, dont forget to change the line in store method
                    $man->inner_contract_currency = $gs->currency;
                    $man->save();

                    foreach ($request['commissioners'] as $r) {
                        $relation = new ManufacturerCommissionRelation;
                        $relation->fill($r);
                        $relation->project_manufacturer_id = $man->id;
                        $relation->currency = $gs->currency; // if you change this line, dont forget to change the line in store method
                        $relation->save();
                    }
                }else{
                    $man = ProjectManufacturer::find($m['id']);
                    $old_man = $man->toArray();
                    $man->fill($m);
                    $man->save();

                    if ($request->filled('commissioners')) {
                        foreach ($request['commissioners'] as $r) {
                            if (array_key_exists('is_new', $r)) { // works only on general fields page (url: projects/id/edit)
                                $relation = new ManufacturerCommissionRelation;
                                $relation->fill($r);
                                $relation->project_manufacturer_id = $man->id;
                                $relation->currency = $gs->currency; // if you change this line, dont forget to change the line in store method
                                $relation->save();
                            }
                        }
                    }

                    // notifications
                    if ($man->order_sent_at != null && $old_man['order_sent_at'] != $man->order_sent_at) {
                        NotificationsAndAutotasksHelper::send_notifications($project->id, 'Order_sent_filled', $user);
                    }
                    if ($man->order_confirmed_at != null && $old_man['order_confirmed_at'] != $man->order_confirmed_at) {
                        NotificationsAndAutotasksHelper::send_notifications($project->id, 'Order_confirmed_filled', $user);
                    }
                    if ($man->designated_shipping_date != null && $old_man['designated_shipping_date'] != $man->designated_shipping_date) {
                        NotificationsAndAutotasksHelper::send_notifications($project->id, 'Designated_shipping_date_filled', $user);
                    }
                    if ($man->fact_shipping_date != null && $old_man['fact_shipping_date'] != $man->fact_shipping_date) {
                        NotificationsAndAutotasksHelper::send_notifications($project->id, 'Fact_shipping_date_filled', $user);
                    }
                    if ($man->equipment_certificate_date != null && $old_man['equipment_certificate_date'] != $man->equipment_certificate_date) {
                        NotificationsAndAutotasksHelper::send_notifications($project->id, 'Equipment_commissioning_certificate_filled', $user);
                    }
                    if ($man->equipment_ex_certificate_date != null && $old_man['equipment_ex_certificate_date'] != $man->equipment_ex_certificate_date) {
                        NotificationsAndAutotasksHelper::send_notifications($project->id, 'Equipment_commissioning_experience_certificate_filled', $user);
                    }

                    // tasks
                    if ($man->limit_forming_days != null && $old_man['limit_forming_days'] != $man->limit_forming_days && $man->limit_forming_type == 0) {
                        if ($man->limit_forming_date == 1 && $man->order_confirmed_at != null){
                            $d = Carbon::parse($man->order_confirmed_at)->addDays($man->limit_forming_days);
                        }
                        if ($man->limit_forming_date == 0 && count($man->payments) > 0){
                            $d = Carbon::parse($man->payments->first()->payment_date)->addDays($man->limit_forming_days);
                        }
                        if ($d){
                            NotificationsAndAutotasksHelper::create_autotask($project, 'Shipping_date_filled', Carbon::parse($d)->subDays(14));
                        }
                    }
                    if ($man->limit_before_date != null && $old_man['limit_before_date'] != $man->limit_before_date && $man->limit_forming_type == 1) {
                        NotificationsAndAutotasksHelper::create_autotask($project, 'Shipping_date_filled', Carbon::parse($man->limit_before_date)->subDays(14));
                    }
                    if ($project)

                    if (array_key_exists('removed_inner_steps', $m)) {
                        foreach ($m['removed_inner_steps'] as $p) {
                            if ($p != 0) {
                                InnerPayment::find($p)->delete();
                            }
                        }
                    }
                    if (array_key_exists('removed_manufacturer_steps', $m)) {
                        foreach ($m['removed_manufacturer_steps'] as $p) {
                            if ($p != 0) {
                                ManufacturerPayment::find($p)->delete();
                            }
                        }
                    }
                    if (array_key_exists('removed_specifications', $m)) {
                        foreach ($m['removed_specifications'] as $p) {
                            if ($p != 0) {
                                ManufacturerSpecification::find($p)->delete();
                            }
                        }
                    }
                    if (array_key_exists('removed_add_docs', $m)) {
                        foreach ($m['removed_add_docs'] as $p) {
                            if ($p != 0) {
                                ProjectManufacturerAdditionalDocument::find($p)->delete();
                            }
                        }
                    }
                    if (array_key_exists('removed_addit_contracts', $m)) {
                        foreach ($m['removed_addit_contracts'] as $p) {
                            if ($p != 0) {
                                ManufacturerAdditionalContract::find($p)->delete();
                            }
                        }
                    }
                    if (array_key_exists('removed_inner_specifications', $m)) {
                        foreach ($m['removed_inner_specifications'] as $p) {
                            if ($p != 0) {
                                InnerSpecification::find($p)->delete();
                            }
                        }
                    }
                    foreach ($m['inner_specifications'] as $p) {
                        if ($p['id'] != 0) {
                            $spec = InnerSpecification::find($p['id']);
                            $spec->fill($p);
                            $spec->save();
                        } else {
                            $spec = new InnerSpecification;
                            $spec->fill($p);
                            $spec->project_manufacturer_id = $man->id;
                            $spec->save();
                        }
                        if (array_key_exists('removed_addit_contracts', $p)) {
                            foreach ($p['removed_addit_contracts'] as $is) {
                                if ($is != 0) {
                                    SpecificationAdditionalContract::find($is)->delete();
                                }
                            }
                        }
                        foreach ($p['additional_contracts'] as $is) {
                            if ($is['id'] != 0) {
                                $doc = SpecificationAdditionalContract::find($is['id']);
                                $doc->fill($is);
                                $doc->save();
                            } else {
                                $doc = new SpecificationAdditionalContract;
                                $doc->fill($is);
                                $doc->inner_specification_id = $spec->id;
                                $doc->save();
                            }
                        }
                    }
                    foreach ($m['additional_contracts'] as $p) {
                        if ($p['id'] != 0) {
                            $doc = ManufacturerAdditionalContract::find($p['id']);
                            $doc->fill($p);
                            $doc->save();
                        } else {
                            $doc = new ManufacturerAdditionalContract;
                            $doc->fill($p);
                            $doc->project_manufacturer_id = $man->id;
                            $doc->save();
                        }
                    }
                    foreach ($m['additional_documents'] as $p) {
                        if ($p['id'] != 0) {
                            $doc = ProjectManufacturerAdditionalDocument::find($p['id']);
                            $doc->fill($p);
                            $doc->save();
                        } else {
                            $doc = new ProjectManufacturerAdditionalDocument;
                            $doc->fill($p);
                            $doc->project_manufacturer_id = $man->id;
                            $doc->save();
                        }
                    }
                    foreach ($m['specifications'] as $p) {
                        if ($p['id'] != 0) {
                            $spec = ManufacturerSpecification::find($p['id']);
                            $spec->fill($p);
                            $spec->save();
                        } else {
                            $spec = new ManufacturerSpecification;
                            $spec->fill($p);
                            $spec->project_manufacturer_id = $man->id;
                            $spec->save();
                        }
                    }
                    foreach ($m['inner_payments'] as $p) {
                        if ($p['id'] != 0) {
                            $payment = InnerPayment::find($p['id']);
                            $old_payment = $payment->toArray();
                            $payment->fill($p);
                            $payment->save();
                            if ($payment->invoice_file != null && $old_payment['invoice_file'] != $payment->invoice_file){
                                NotificationsAndAutotasksHelper::create_autotask($project, 'Inner_bill_filled');
                            }
                            if($payment->confirmed && !$old_payment['confirmed']){
                                NotificationsAndAutotasksHelper::create_autotask($project, 'Inner_confirmed_filled');
                            }
                        } else {
                            $payment = new InnerPayment;
                            $payment->fill($p);
                            $payment->project_manufacturer_id = $man->id;
                            $payment->save();
                            if ($payment->invoice_file != null){
                                NotificationsAndAutotasksHelper::create_autotask($project, 'Inner_bill_filled');
                            }
                            if ($payment->confirmed){
                                NotificationsAndAutotasksHelper::create_autotask($project, 'Inner_confirmed_filled');
                            }
                        }
                    }

                    $notification_pa = new ProjectApplicationsAdded(
                        'Manufacturer_bill_filled', 
                        $project->id, 
                        $project->name, 
                        $man->manufacturer_id, 
                        $man->manufacturer->name
                    );

                    foreach ($m['payments'] as $p) {
                        if ($p['id'] != 0) {
                            $payment = ManufacturerPayment::find($p['id']);
                            $old_payment = $payment->toArray();
                            $payment->fill($p);
                            $payment->save();
                            if ($payment->invoice_file != null && $old_payment['invoice_file'] != $payment->invoice_file){
                                NotificationsAndAutotasksHelper::create_autotask($project, 'Manufacturer_bill_filled', null, $man->manufacturer_id);                             
                                NotificationsAndAutotasksHelper::create_autotask(
                                    $project, 
                                    'Invoice_uploaded', 
                                    null, 
                                    $man->manufacturer_id, 
                                    $payment->invoice_file_name,
                                    sprintf("%s/projects/%u/second", env("APP_URL"), $project->id)
                                );                             
                                NotificationsAndAutotasksHelper::send_notifications($project->id, "Manufacturer_bill_filled", $user, $notification_pa);
                            }
                            if($payment->confirmed && !$old_payment['confirmed']){
                                NotificationsAndAutotasksHelper::create_autotask($project, 'Manufacturer_confirmed_filled', null, $man->manufacturer_id);
                                NotificationsAndAutotasksHelper::create_autotask(
                                    $project, 
                                    'Invoice_confirmed', 
                                    null, 
                                    $man->manufacturer_id, 
                                    $payment->invoice_file_name,
                                    sprintf("%s/projects/%u/second", env("APP_URL"), $project->id)
                                );                             
                                $notification_pa->not_type = 'Manufacturer_confirmed_filled';
                                NotificationsAndAutotasksHelper::send_notifications($project->id, "Manufacturer_confirmed_filled", $user, $notification_pa);
                            }
                        } else {
                            $payment = new ManufacturerPayment;
                            $payment->fill($p);
                            $payment->project_manufacturer_id = $man->id;
                            $payment->save();
                            if ($payment->invoice_file != null){
                                NotificationsAndAutotasksHelper::create_autotask($project, 'Manufacturer_bill_filled', null, $man->manufacturer_id);
                                NotificationsAndAutotasksHelper::create_autotask(
                                    $project, 
                                    'Invoice_uploaded', 
                                    null, 
                                    $man->manufacturer_id, 
                                    $payment->invoice_file_name,
                                    sprintf("%s/projects/%u/second", env("APP_URL"), $project->id)
                                );   
                                NotificationsAndAutotasksHelper::send_notifications($project->id, "Manufacturer_bill_filled", $user, $notification_pa);
                            }
                            if ($payment->confirmed){
                                NotificationsAndAutotasksHelper::create_autotask($project, 'Manufacturer_confirmed_filled', null, $man->manufacturer_id);
                                NotificationsAndAutotasksHelper::create_autotask(
                                    $project, 
                                    'Invoice_confirmed', 
                                    null, 
                                    $man->manufacturer_id, 
                                    $payment->invoice_file_name,
                                    sprintf("%s/projects/%u/second", env("APP_URL"), $project->id)
                                );
                                $notification_pa->not_type = 'Manufacturer_confirmed_filled';
                                NotificationsAndAutotasksHelper::send_notifications($project->id, "Manufacturer_confirmed_filled", $user, $notification_pa);
                            }
                        }
                    }

                    foreach ($m['commission_relations'] as $r) {
                        $relation = ManufacturerCommissionRelation::find($r['id']);
                        $relation->fill($r);
                        $relation->save();

                        if (array_key_exists('removed_documents', $r)) {
                            foreach ($r['removed_documents'] as $s) {
                                if ($s != 0) {
                                    CommissionRelationDocument::find($s)->delete();
                                }
                            }
                        }
                        foreach ($r['documents'] as $p) {
                            if ($p['id'] != 0) {
                                $doc = CommissionRelationDocument::find($p['id']);
                                $doc->fill($p);
                                $doc->save();
                            } else {
                                $doc = new CommissionRelationDocument;
                                $doc->fill($p);
                                $doc->manufacturer_commission_relation_id = $relation->id;
                                $doc->save();
                            }
                        }

                        if (array_key_exists('removed_commission_steps', $r)) {
                            foreach ($r['removed_commission_steps'] as $t) {
                                if ($t != 0) {
                                    CommissionPayment::find($t)->delete();
                                }
                            }
                        }
                        foreach ($r['commission_payments'] as $p) {
                            if ($p['id'] != 0) {
                                $payment = CommissionPayment::find($p['id']);
                                $payment->fill($p);
                                $payment->save();
                            } else {
                                $payment = new CommissionPayment;
                                $payment->fill($p);
                                $payment->manufacturer_commission_relation_id = $relation->id;
                                $payment->save();
                            }
                        }
                    }
                }
            }
            if ($request->filled('removed_tech_docs')) {
                foreach ($request['removed_tech_docs'] as $s) {
                    if ($s != 0) {
                        TechnicalDocument::find($s)->delete();
                    }
                }
            }
            foreach ($request['technical_documents'] as $p) {
                $isTechnicalDocumentsInStock = false;
                if ($p['id'] != 0) {
                    $doc = TechnicalDocument::find($p['id']);
                    //for generating task
                    if ($doc->available == false && $p['available'] == true){
                        $isTechnicalDocumentsInStock = true;
                    }
                    $doc->fill($p);
                    $doc->save();
                } else {
                    $doc = new TechnicalDocument;
                    $doc->fill($p);
                    $doc->project_id = $project->id;
                    //for generating task
                    if ($doc->available == true){
                        $isTechnicalDocumentsInStock = true;
                    }
                    $doc->save();
                }
                if ($isTechnicalDocumentsInStock == true){                    
                    NotificationsAndAutotasksHelper::create_autotask(
                        $project, 
                        'Technical_documentation_available', 
                        null, 
                        $doc->manufacturer_id,
                        null,
                        sprintf("%s/projects/%u/fourth", env("APP_URL"), $project->id)
                    );
                }
            }
            if ($request->filled('removed_expense_payments')) {
                foreach ($request['removed_expense_payments'] as $s) {
                    if ($s != 0) {
                        InstallationPaymentStep::find($s)->delete();
                    }
                }
            }
            foreach ($request['installation_expense_payments'] as $p) {
                if ($p['id'] != 0) {
                    $payment = InstallationPaymentStep::find($p['id']);
                    $old_payment = $payment->toArray();
                    $payment->fill($p);
                    $payment->save();
                    if ($payment->payment_invoice_file_path != null && $old_payment['payment_invoice_file_path'] != $payment->payment_invoice_file_path){
                        NotificationsAndAutotasksHelper::create_autotask($project, 'Installation_bill_filled');
                        NotificationsAndAutotasksHelper::create_autotask(
                            $project, 
                            'Invoice_uploaded', 
                            null, 
                            $man->manufacturer_id, 
                            $payment->payment_invoice_file_name,
                            sprintf("%s/projects/%u/fifth", env("APP_URL"), $project->id)
                        );   
                    }
                    if($payment->payment_confirmed && !$old_payment['payment_confirmed']){
                        NotificationsAndAutotasksHelper::create_autotask(
                            $project, 
                            'Invoice_confirmed', 
                            null, 
                            $man->manufacturer_id, 
                            $payment->payment_invoice_file_name,
                            sprintf("%s/projects/%u/fifth", env("APP_URL"), $project->id)
                        );                             
                    }
                } else {
                    $payment = new InstallationPaymentStep;
                    $payment->fill($p);
                    $payment->project_id = $project->id;
                    $payment->save();
                    if ($payment->payment_invoice_file_path){
                        NotificationsAndAutotasksHelper::create_autotask($project, 'Installation_bill_filled');
                        NotificationsAndAutotasksHelper::create_autotask(
                            $project, 
                            'Invoice_uploaded', 
                            null, 
                            $man->manufacturer_id, 
                            $payment->payment_invoice_file_name,
                            sprintf("%s/projects/%u/fifth", env("APP_URL"), $project->id)
                        );   
                    }
                    if($payment->payment_confirmed){
                        NotificationsAndAutotasksHelper::create_autotask(
                            $project, 
                            'Invoice_confirmed', 
                            null, 
                            $man->manufacturer_id, 
                            $payment->payment_invoice_file_name,
                            sprintf("%s/projects/%u/fifth", env("APP_URL"), $project->id)
                        );                             
                    }
                }
            }

            if ($request->filled('removed_side_payments')) {
                foreach ($request['removed_side_payments'] as $s) {
                    if ($s != 0) {
                        SidePayment::find($s)->delete();
                    }
                }
            }
            foreach ($request['side_payments'] as $p) {
                if ($p['id'] != 0) {
                    $doc = SidePayment::find($p['id']);
                    $doc->fill($p);
                    $doc->save();
                } else {
                    $doc = new SidePayment;
                    $doc->fill($p);
                    $doc->save();
                }
            }

            if ($request->filled('removed_additional_expenses')) {
                foreach ($request['removed_additional_expenses'] as $s) {
                    if ($s != 0) {
                        AdditionalExpense::find($s)->delete();
                    }
                }
            }
            foreach ($request['additional_expenses'] as $p) {
                if ($p['id'] != 0) {
                    $doc = AdditionalExpense::find($p['id']);
                    $doc->fill($p);
                    $doc->save();
                } else {
                    $doc = new AdditionalExpense;
                    $doc->fill($p);
                    $doc->save();
                }
            }

            if ($project->finished){
                foreach($project->specifications as $spec){
                    foreach($spec->equipment as $eq){
                        $c_e = new ClientEquipment;
                        $c_e->client_id = $project->client_id;
                        $c_e->manufacturer_id = $eq->manufacturer_id;
                        $c_e->name = $eq->name;
                        $c_e->size = $eq->size;
                        $c_e->estimate_unit_id = $eq->estimate_unit_id;
                        $c_e->model = $eq->model;
                        $c_e->vendor_code = $eq->vendor_code;
                        $c_e->save();
                    }
                }
            }
            broadcast(new ProjectChangedEvent($project->id, $user->id));
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function show(Request $request, $id)
    {

        $user = Auth::user();

//        $section1_fields = [
//            0 => ['id', 'client_id'],
//            'manufacturers' => ['id', 'manufacturer_id']
//        ];
//        $section2_fields = [];
//        $section3_fields = ['manufacturers.order.order_date', 'manufacturers.order.responsible_user_id'];
//        $section4_fields = [];
//        $section5_fields = [];

//        $final_fields = ['id', 'manufacturers.project_id', 'manufacturers.manufacturer_id', 'client_id', 'lessee_client_id'];
//        if ($user->can_with_project('read', 0)){
//            $final_fields = array_merge($final_fields, $section1_fields);
//        }
//        if ($user->can_with_project('read', 1)){
//            $final_fields = array_merge($final_fields, $section2_fields);
//        }
//        if ($user->can_with_project('read', 2)){
//            $final_fields = array_merge($final_fields, $section3_fields);
//        }
//        if ($user->can_with_project('read', 3)){
//            $final_fields = array_merge($final_fields, $section4_fields);
//        }
//        if ($user->can_with_project('read', 5)){
//            $final_fields = array_merge($final_fields, $section5_fields);
//        }

        $project = Project::with([
            'client.client_contacts.client_contact_phones',
            'specifications.equipment.manufacturer' , 'contract_payments' , 'manufacturers.payments' , 'manufacturers',
            'manufacturers.specifications', 'manufacturers.commission_relations.commission_payments', 'manufacturers.commission_relations.documents', 'manufacturers.inner_payments',
            'manufacturers.orders.managers', 'manufacturers.orders.places', 'manufacturers.orders.customs_documents', 'manufacturers.orders.carriers.carrier' , 'technical_documents',
            'manufacturers.additional_documents', 'manufacturers.additional_contracts',
            'installation_expense_payments', 'manufacturers.manufacturer.contracts', 'additional_documents',
            'manufacturers.orders.transportation_payments',
            'manufacturers.orders.transportation_vat_payments',
            'manufacturers.orders.carriers.carrier.contracts', 'additional_contracts',
            'manufacturers.inner_specifications', 'manufacturers.inner_specifications.additional_contracts',
            'installation_documents',
            'side_payments',
            'additional_expenses',
            'fact_delivery_entities'
        ])->findOrFail($id);

        $project->selected_company_object = $project->client_id ? ['value' => $project->client_id, 'label' => $project->client->name] : null;
        $project->selected_lessee_company_object = $project->lessee_client_id ? ['value' => $project->lessee_client_id, 'label' => $project->lessee_client->name] : null;
        $project->selected_service_object = $project->service_id ? ['value' => $project->service_id, 'label' => $project->service->get_service_number(), 'client_contact_id' => $project->service->client_contact_id] : null;

        foreach($project->manufacturers as $man){
            $man->manufacturer_select_option_object = $man->manufacturer_id ? ['value' => $man->manufacturer_id, 'label' => $man->manufacturer->name ] : null;
        }


        return $project;
    }

    public function destroy(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        ProjectSpecification::where('project_id', $id)->delete();
//        ProjectCarrier::where('project_id', $id)->delete(); // moved to shipping orders
        ProjectManufacturer::where('project_id', $id)->delete();
        // TODO: remove manufacturer deps
        ContractPayment::where('project_id', $id)->delete();
        TechnicalDocument::where('project_id', $id)->delete();
        AdditionalDocument::where('project_id', $id)->delete();
        ManufacturerOrder::where('project_id', $id)->delete();
//        TransportationPayment::where('project_id', $id)->delete(); // moved to shipping orders

        $project->delete();
    }
}
