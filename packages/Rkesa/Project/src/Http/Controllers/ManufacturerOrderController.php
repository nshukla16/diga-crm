<?php

namespace Rkesa\Project\Http\Controllers;

use Log;
use Auth;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Rkesa\Project\Models\ProjectCarrier;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Rkesa\Project\Models\CustomsDocument;
use Rkesa\Project\Models\ManufacturerOrder;
use Rkesa\Project\Models\ManufacturerOrderPlace;
use Rkesa\Project\Models\ManufacturerOrderManager;
use Rkesa\Project\Models\TransportationPayment;
use Rkesa\Project\Notifications\ProjectApplicationsAdded;
use Rkesa\Project\Http\Helpers\NotificationsAndAutotasksHelper;
use Rkesa\Project\Models\TransportationVatPayment;

class ManufacturerOrderController extends Controller
{

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();

            $m_o = new ManufacturerOrder;
            $m_o->fill($request->all());
            $m_o->generate_order_number();
            $m_o->save();

            foreach ($request['managers'] as $m) {
                $spec = new ManufacturerOrderManager;
                $spec->fill($m);
                $spec->manufacturer_order_id = $m_o->id;
                $spec->save();
            }

            foreach ($request['places'] as $m) {
                $spec = new ManufacturerOrderPlace;
                $spec->fill($m);
                $spec->manufacturer_order_id = $m_o->id;
                $spec->save();
            }

            foreach ($request['carriers'] as $c) {
                $carrier = new ProjectCarrier;
                $carrier->fill($c);
                $carrier->manufacturer_order_id = $m_o->id;
                $carrier->save();
            }

            foreach ($request['customs_documents'] as $p) {
                $doc = new CustomsDocument;
                $doc->fill($p);
                $doc->manufacturer_order_id = $m_o->id;
                $doc->save();
            }

//            // notifications
//            if ($m_o->customs_application_date != null) {
//                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Date_of_application_filled', $user);
//            }
//            if ($m_o->customs_issue_date != null) {
//                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Date_of_issue_filled', $user);
//            }
//            if ($m_o->approximate_date_of_arrival_to_temporary != null) {
//                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Approximate_arrival_date_to_temporary_filled', $user);
//            }
//            if ($m_o->approximate_date_of_arrival != null) {
//                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Approximate_arrival_date_filled', $user);
//            }
//            // tasks
//            if ($m_o->dt != null){
//                NotificationsAndAutotasksHelper::create_autotask($m_o->project, 'Dt_filled');
//            }

            $m_o->load("project_manufacturer");
            NotificationsAndAutotasksHelper::create_autotask(
                $m_o->project, 
                'Manufacturer_order_created', 
                null, 
                $m_o->project_manufacturer->manufacturer_id,
                null,
                sprintf("%s/projects/%u/second", env("APP_URL"), $m_o->project->id)
            );
            $notification = new ProjectApplicationsAdded(
                'Manufacturer_order_created', 
                $m_o->project->id, $m_o->project->name, 
                $m_o->project_manufacturer->manufacturer_id, 
                $m_o->project_manufacturer->manufacturer->name
            );
            
            NotificationsAndAutotasksHelper::send_notifications($m_o->project->id, "Manufacturer_order_created", $user, $notification);

            $res->id=$m_o->id;
        }
        catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public function update(Request $request, $id){
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();

            $m_o = ManufacturerOrder::find($id);
            $old_m_o = $m_o->toArray();
            $m_o->fill($request->all());
            $m_o->save();
            if ($request->filled('removed_managers')) {
                foreach ($request['removed_managers'] as $m) {
                    if ($m != 0) {
                        ManufacturerOrderManager::find($m)->delete();
                    }
                }
            }
            foreach ($request['managers'] as $m) {
                if ($m['id'] != 0) {
                    $spec = ManufacturerOrderManager::find($m['id']);
                    $spec->fill($m);
                    $spec->save();
                } else {
                    $spec = new ManufacturerOrderManager;
                    $spec->fill($m);
                    $spec->manufacturer_order_id = $m_o->id;
                    $spec->save();
                }
            }

            if ($request->filled('removed_places')) {
                foreach ($request['removed_places'] as $m) {
                    if ($m != 0) {
                        ManufacturerOrderPlace::find($m)->delete();
                    }
                }
            }
            foreach ($request['places'] as $m) {
                if ($m['id'] != 0) {
                    $spec = ManufacturerOrderPlace::find($m['id']);
                    $spec->fill($m);
                    $spec->save();
                } else {
                    $spec = new ManufacturerOrderPlace;
                    $spec->fill($m);
                    $spec->manufacturer_order_id = $m_o->id;
                    $spec->save();
                }
            }
            if ($request->filled('removed_carriers')) {
                foreach ($request['removed_carriers'] as $s) {
                    if ($s != 0) {
                        ProjectCarrier::find($s)->delete();
                    }
                }
            }
            foreach ($request['carriers'] as $c) {
                if ($c['id'] != 0) {
                    $carrier = ProjectCarrier::find($c['id']);
                    $carrier->fill($c);
                    $carrier->save();
                } else {
                    $carrier = new ProjectCarrier;
                    $carrier->fill($c);
                    $carrier->manufacturer_order_id = $m_o->id;
                    $carrier->save();
                }
            }
            if ($request->filled('removed_customs_documents')) {
                foreach ($request['removed_customs_documents'] as $s) {
                    if ($s != 0) {
                        CustomsDocument::find($s)->delete();
                    }
                }
            }
            foreach ($request['customs_documents'] as $p) {
                if ($p['id'] != 0) {
                    $doc = CustomsDocument::find($p['id']);
                    $doc->fill($p);
                    $doc->save();
                } else {
                    $doc = new CustomsDocument;
                    $doc->fill($p);
                    $doc->manufacturer_order_id = $m_o->id;
                    $doc->save();
                }
            }
            if ($request->filled('removed_transportation_payments')) {
                foreach ($request['removed_transportation_payments'] as $s) {
                    if ($s != 0) {
                        TransportationPayment::find($s)->delete();
                    }
                }
            }
            foreach ($request['transportation_payments'] as $p) {
                if ($p['id'] != 0) {
                    $doc = TransportationPayment::find($p['id']);
                    $old_doc = $doc->toArray();
                    $doc->fill($p);
                    $doc->save();
                    if ($doc->invoice_file != null && $old_doc['invoice_file'] != $doc->invoice_file){
                        NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 'Transportation_bill_filled');
                        NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 
                            'Invoice_uploaded', null, $doc->manufacturer_order->project_manufacturer->manufacturer_id, 
                            $doc->document_file_name,
                            sprintf("%s/projects/%u/third", env("APP_URL"), $doc->manufacturer_order->project_manufacturer->project->id)
                        );
                    }
                    if ($doc->confirmed && !$old_doc['confirmed']){
                        NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 'Transportation_confirmed_filled');
                        NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 
                        'Invoice_confirmed', null, $doc->manufacturer_order->project_manufacturer->manufacturer_id, 
                        $doc->document_file_name,
                        sprintf("%s/projects/%u/third", env("APP_URL"), $doc->manufacturer_order->project_manufacturer->project->id)
                    );
                    }
                } else {
                    $doc = new TransportationPayment;
                    $doc->fill($p);
                    $doc->manufacturer_order_id = $m_o->id;
                    $doc->save();
                    if ($doc->invoice_file != null){
                        NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 'Transportation_bill_filled');
                        NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 
                        'Invoice_uploaded', null, $doc->manufacturer_order->project_manufacturer->manufacturer_id, 
                        $doc->document_file_name,
                        sprintf("%s/projects/%u/third", env("APP_URL"), $doc->manufacturer_order->project_manufacturer->project->id)
                    );
                    }
                    if ($doc->confirmed){
                        NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 'Transportation_confirmed_filled');
                        NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 
                        'Invoice_confirmed', null, $doc->manufacturer_order->project_manufacturer->manufacturer_id, 
                        $doc->document_file_name,
                        sprintf("%s/projects/%u/third", env("APP_URL"), $doc->manufacturer_order->project_manufacturer->project->id)
                    );
                    }
                }
            }

            //vat transportation payments
            if ($request->filled('removed_transportation_vat_payments')) {
                foreach ($request['removed_transportation_vat_payments'] as $s) {
                    if ($s != 0) {
                        TransportationVatPayment::find($s)->delete();
                    }
                }
            }
            foreach ($request['transportation_vat_payments'] as $p) {
                if ($p['id'] != 0) {
                    $doc = TransportationVatPayment::find($p['id']);
                    $old_doc = $doc->toArray();
                    $doc->fill($p);
                    $doc->save();
                    // if ($doc->invoice_file != null && $old_doc['invoice_file'] != $doc->invoice_file){
                    //     NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 'Transportation_bill_filled');
                    //     NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 
                    //         'Invoice_uploaded', null, $doc->manufacturer_order->project_manufacturer->manufacturer_id, 
                    //         $doc->document_file_name,
                    //         sprintf("%s/projects/%u/third", env("APP_URL"), $doc->manufacturer_order->project_manufacturer->project->id)
                    //     );
                    // }
                    // if ($doc->confirmed && !$old_doc['confirmed']){
                    //     NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 'Transportation_confirmed_filled');
                    //     NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 
                    //     'Invoice_confirmed', null, $doc->manufacturer_order->project_manufacturer->manufacturer_id, 
                    //     $doc->document_file_name,
                    //     sprintf("%s/projects/%u/third", env("APP_URL"), $doc->manufacturer_order->project_manufacturer->project->id)
                    // );
                    // }
                } else {
                    $doc = new TransportationVatPayment;
                    $doc->fill($p);
                    $doc->manufacturer_order_id = $m_o->id;
                    $doc->save();
                    // if ($doc->invoice_file != null){
                    //     NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 'Transportation_bill_filled');
                    //     NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 
                    //     'Invoice_uploaded', null, $doc->manufacturer_order->project_manufacturer->manufacturer_id, 
                    //     $doc->document_file_name,
                    //     sprintf("%s/projects/%u/third", env("APP_URL"), $doc->manufacturer_order->project_manufacturer->project->id)
                    // );
                    // }
                    // if ($doc->confirmed){
                    //     NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 'Transportation_confirmed_filled');
                    //     NotificationsAndAutotasksHelper::create_autotask($doc->manufacturer_order->project_manufacturer->project, 
                    //     'Invoice_confirmed', null, $doc->manufacturer_order->project_manufacturer->manufacturer_id, 
                    //     $doc->document_file_name,
                    //     sprintf("%s/projects/%u/third", env("APP_URL"), $doc->manufacturer_order->project_manufacturer->project->id)
                    // );
                    // }
                }
            }

            // notifications
            if ($m_o->customs_application_date != null && $old_m_o['customs_application_date'] != $m_o->customs_application_date) {
                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Date_of_application_filled', $user);
            }
            if ($m_o->customs_issue_date != null && $old_m_o['customs_issue_date'] != $m_o->customs_issue_date) {
                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Date_of_issue_filled', $user);
            }
            if ($m_o->approximate_date_of_arrival_to_temporary != null && $old_m_o['approximate_date_of_arrival_to_temporary'] != $m_o->approximate_date_of_arrival_to_temporary) {
                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Approximate_arrival_date_to_temporary_filled', $user);
            }
            if ($m_o->approximate_date_of_arrival != null && $old_m_o['approximate_date_of_arrival'] != $m_o->approximate_date_of_arrival) {
                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Approximate_arrival_date_filled', $user);
            }
            if ($m_o->fact_delivery_date != null && $old_m_o['fact_delivery_date'] != $m_o->fact_delivery_date) {
                NotificationsAndAutotasksHelper::send_notifications($m_o->project_id, 'Order_fact_delivery_filled', $user);
            }
            // tasks
            if ($m_o->dt != null && $old_m_o['dt'] != $m_o->dt){
                NotificationsAndAutotasksHelper::create_autotask($m_o->project, 'Dt_filled');
            }
        }
        catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public function show(Request $request, $id){
        $order = ManufacturerOrder::with([
            'managers', 'places', 'customs_documents', 'carriers.carrier', 'carriers.carrier.contracts', 'transportation_payments',
            'transportation_vat_payments'
        ])->findOrFail($id);

        return $order;
    }

    // if you change shipping order conditions, be sure you changed them also in frontend
    // in helper.js (shipping_order_conditions_of_delivery)
    private function get_condition_name_from_id($id){
        switch ($id) {
            case 0:
                return "DDP";
                break;
            case 1:
                return "DAP";
                break;
            case 2:
                return "FCA";
                break;
            case 3:
                return "FOB";
                break;
            case 4:
                return "EXW";
                break;
            case 5:
                return "CIF";
                break;
            case 6:
                return "CPT";
                break;
        }
    }

    public function destroy(Request $request, $id)
    {
        ManufacturerOrder::find($id)->delete();
    }

    public function export(Request $request)
    {
        $order_id = $request->input('id');
        $fileName = 'orders-'.date("Y-m-d-H-i-s"). '.xlsx';
        $order = ManufacturerOrder::with('managers.user')->find($order_id);
        ;
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getStyle('A:B')->getAlignment()->setHorizontal('left');

        // Cells with format
        $delivery = $this->get_condition_name_from_id($order->conditions_of_delivery);
        $order_date = date('Y-m-d', strtotime($order->order_date));
        $loading_order_date = date('Y-m-d', strtotime($order->loading_ready_date));

        $arrayData = [
            [trans('project.Order_name'), $order->name],
            [trans('project.Request_number'), $order->number],
            [trans('project.Order_date'),$order_date],
            [trans('project.Manager'), join(', ', array_map(function($man){ return $man['user']['name']; }, $order->managers->toArray()))], //->name
            [trans('project.Sender_company_name'), $order->sender_manufacturer->name],//->name
            [trans('project.Sender_legal_address'), $order->sender_legal_address],
            [trans('project.Uploading_address'), $order->uploading_address],
            [trans('project.Manufacturer_contact_name'), $order->manufacturer_contact_name],
            [trans('project.Manufacturer_contact_phone'), $order->manufacturer_contact_phone],
            [trans('project.Manufacturer_contact_email'), $order->manufacturer_contact_email],
            [trans('project.Loading_ready_date'), $loading_order_date ],
            [trans('project.Sender_contract_number'), $order->order_contract_and_specifications],
            [trans('project.Order_conditions_of_delivery'), $delivery],
            [trans('project.Additional_loading'), $order->additional_loading],
            [trans('project.Shipment_place'), $order->shipment_place],
            [trans('project.Destination_place'), $order->destination_place],
            [trans('project.Shipment_type_and_counts'), $order->shipment_type_and_counts],
            [trans('project.Consignment_receiver_company_name'), $order->consignment_receiver_company_name],
            [trans('project.Consignment_receiver_address'), $order->consignment_receiver_address],
            [trans('project.Consignment_receiver_phone'), $order->consignment_receiver_phone],
            [trans('project.Final_buyer'), $order->final_buyer],
            [trans('project.Client_contract_number'), $order->client_contract_number],
            [trans('project.Downloading_address'), $order->downloading_address],
            [trans('project.Downloading_contact'), $order->downloading_contact->name.', тел: '.join(', ', array_map(function($man){ return $man['phone_number']; }, $order->downloading_contact->client_contact_phones->toArray()))],
            [trans('project.Order_manufacturer_name'), $order->manufacturer_name],
            [trans('project.Manufacturer_legal_address'), $order->manufacturer_legal_address],
            [trans('project.Loading_name'), $order->loading_name],
            [trans('project.Loading_size'), join(', ', array_map(function($man){ return $man['text']; }, $order->places->toArray()))],
            [trans('project.Loading_selling_price'), $order->loading_selling_price],
            [trans('project.Loading_cost_price'), $order->loading_cost_price],
            [trans('project.Inner_order_name'), $order->inner_specification_number],
        ];

        $spreadsheet->getActiveSheet()
            ->fromArray(
                $arrayData,  // The data to set
                null    // Array values with this value will not be set
        );

        $writer = new Xlsx($spreadsheet);
        $writer->save($fileName);

        return response()->file($fileName)->deleteFileAfterSend(true);
    }

}
