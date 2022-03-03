<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Estimate\Http\Helpers\EstimatePDFCreator;
use Rkesa\Estimate\Http\Helpers\PlanningPDFCreator;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimateChange;
use Rkesa\Estimate\Models\EstimateDocument;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateLineCategory;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Estimate\Models\EstimateLineSeparator;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineWorker;
use Rkesa\Estimate\Models\EstimateMaterial;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\EstimateWorker;
use Rkesa\Estimate\Models\Resource;
use Rkesa\EstimateFork\Models\EstimateFork;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Service\Models\Service;
use App\GlobalSettings;
use Illuminate\Support\Facades\Auth;
use Log;
use DB;
use Exception;
use FPDI;
use UrlSigner;
use Carbon\Carbon;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

class EstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search', false);
        $user = Auth::user();
        if ($search !== false) {
            $by_service = $request->input('by_service', false);
            if ($by_service !== false) {
                $estimates = Estimate::where('service_id', $search);

                switch($user->cando('estimates', 'read')){
                    case 0:
                        $estimates->where('id', null);
                        break;
                    case 1:
                        $estimates->where('user_id', $user->id);
                        break;
                    case 2:
                        $estimates->whereIn('user_id', $user->groupmates_ids());
                        break;
                    case 3:
                        break;
                }

                $estimate_forks = EstimateFork::all();
                return response()->json(array('estimates' => $estimates->get(), 'forks' => $estimate_forks));
            }else{
                $estimates = Estimate::search($search);
                return response()->json($estimates);
            }
        }else{ // for index page
            $res = (object)array();
            $res->errcode = 0;
            try{
                $limit = $request->input('limit', 10);
                $offset = $request->input('offset', 0);
                $sort = $request->input('sort', 'created_at');
                if ($sort == ''){ $sort = 'created_at'; }
                $order = $request->input('order', 'asc');
                if ($order == ''){ $order = 'asc'; }

                $fields = $request->input('fields', '*');
                $fields_array = explode(",", $fields);

                $estimates = Estimate::select($fields_array)->with('service', 'service.client_contact');
                switch($user->cando('estimates', 'read')){
                    case 0:
                        $estimates->where('id', null);
                        break;
                    case 1:
                        $estimates->where('user_id', $user->id);
                        break;
                    case 2:
                        $estimates->whereIn('user_id', $user->groupmates_ids());
                        break;
                    case 3:
                        break;
                }

                $query = $request->input('query', '');
                if ($query != ''){
                    // TODO: add name+surname search
                    $estimates = $estimates->whereHas('service', function($q) use($query) {
                        $q->where('estimate_number', 'like', '%'.$query.'%')
                          ->orWhere('address', 'like', '%'.$query.'%');
                    });
                }

                $estimates->orderBy($sort, $order);

                $res->total = $estimates->count();
                $res->rows = $estimates->offset($offset)->limit($limit)->get();
            } catch (\Exception $e) {
                $res->errcode = 1;
                $res->errmess = $e->getMessage();
                Log::channel('daily')->error($e);
                app('sentry')->captureException($e);
            }
            return response()->json($res);
        }
    }

//    /**
//     * Show the form for creating a new resource.
//     *
//     * @return Response
//     */
//    public function create(Request $request)
//    {
//
//        $estimate = (object) array();
//
//        $base_estimate_id = $request->input('estimate_id', null);
//        if (!is_null($base_estimate_id)){
//            $base_estimate = Estimate::find($base_estimate_id);
//            if (!is_null($base_estimate)) {
//                $service = array('estimate_number' => $base_estimate->service->estimate_number);
//                $estimate->service_id = $base_estimate->service_id;
//                $estimate->service = $service;
//                $estimate->revision = $base_estimate->revision;
//                $estimate->option = $base_estimate->option;
//
//                $action = $request->input('action', null);
//
//                if (!is_null($action)){
//                    switch ($action) {
//                        case 'rev':
//                            $existed_estimates = Estimate::where(['service_id' => $base_estimate->service->id, 'option' => $base_estimate->option])->orderBy('revision', 'desc')->get();
//                            $estimate->revision = $existed_estimates[0]->revision + 1;
//                            break;
//                        case 'opt':
//                            $existed_estimates = Estimate::where(['service_id' => $base_estimate->service->id, 'revision' => $base_estimate->revision])->orderBy('option', 'desc')->get();
//                            $estimate->option = $existed_estimates[0]->option + 1;
//                            break;
//                    }
//                }
//            }
//        }
//
//        return view('estimate::estimate/create', ['estimate' => $estimate]);
//    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $estimate = new Estimate;
            $estimate->fill($request->all());
            $estimate->additional_price = intval($request['additional_price']);
            $estimate->save();
            if ($request->exists('estimate_pay_stages')) {
                foreach ($request->estimate_pay_stages as $ps) {
                    $pay_stage = new EstimatePayStage();
                    $pay_stage->percent = $ps['percent'];
                    $pay_stage->text = $ps['text'];
                    $pay_stage->vat_type = $ps['vat_type'];
                    $pay_stage->invoice_file = $ps['invoice_file'];
                    $pay_stage->invoice_file_name = $ps['invoice_file_name'];
                    $pay_stage->recibo_file = $ps['recibo_file'];
                    $pay_stage->recibo_file_name = $ps['recibo_file_name'];
                    $pay_stage->paid = $ps['paid'];
                    $pay_stage->invoice_number = $ps['invoice_number'];
                    $pay_stage->fact_paid = $ps['fact_paid'];
                    $pay_stage->email_template_id = $ps['email_template_id'];
                    $pay_stage->proof_file = $ps['proof_file'];
                    $pay_stage->proof_file_name = $ps['proof_file_name'];
                    $pay_stage->estimate_id = $estimate->id;
                    $pay_stage->save();
                }
            }
            $assosiation_array = array();
            if ($request->exists('lines')) {
                foreach ($request->lines as $ps) {
                    $line = new EstimateLine();
                    $line->lineable_type = $ps['lineable_type'];
                    $line->order = $ps['order'];
                    $line->estimate_id = $estimate->id;
                    switch ($ps['lineable_type']) {
                        case '\App\EstimateLineCategory':
                            $category = new EstimateLineCategory();
                            $category->name = $ps['category_name'];
                            $category->is_pattern = false;
                            $category->save();
                            $line->lineable_id = $category->id;
                            break;
                        case '\App\EstimateLineSeparator':
                            $separator = new EstimateLineSeparator();
                            $separator->name = $ps['separator_name'];
                            $separator->is_pattern = false;
                            $separator->save();
                            $line->lineable_id = $separator->id;
                            break;
                        case '\App\EstimateLineData':
                            $data = new EstimateLineData();
                            $data->description = $ps['data_description'];
                            $data->note = $ps['data_note'];
                            $data->ppu = $ps['data_ppu'];
                            $data->quantity = $ps['data_quantity'];
                            $data->price = $ps['data_price'];
                            $data->estimate_unit_id = $ps['data_measure'];
                            $data->save();
                            $line->lineable_id = $data->id;
                            break;
                        case '\App\EstimateLineFicha':
                            $ficha = new EstimateLineFicha();
                            $ficha->description = $ps['ficha_description'];
                            $ficha->note = $ps['ficha_note'];
                            $ficha->ppu = $ps['ficha_ppu'];
                            $ficha->quantity = $ps['ficha_quantity'];
                            $ficha->price = $ps['ficha_price'];
                            $ficha->estimate_unit_id = $ps['ficha_measure'];
                            $ficha->save();
                            foreach (['maodeobra', 'materials', 'equipment', 'subs'] as $resname) {
                                foreach ($ps[$resname]['list'] as $ress) {
                                    $m = new EstimateLineFichaResource();
                                    $r = Resource::where('name', $ress['name'])->first();
                                    $m->price = $ress['price'];
                                    $m->resource_type = $ress['resource_type'];
                                    $m->estimate_unit_id = $ress['estimate_unit_id'];
                                    $m->estimate_line_ficha_id = $ficha->id;
                                    $m->correction = $ress['correction'];
                                    $m->quantity = $ress['quantity'];
                                    $m->resource_id = $r->id;
                                    $m->save();
                                }
                            }
                            $line->lineable_id = $ficha->id;
                            break;
                    }
                    if (isset($ps['parent_id'])) {
                        $line->parent_id = $assosiation_array[$ps['parent_id']];
                    }
                    // correct lineable part start
                    $line->correct_lineable_id = $line->lineable_id;
                    switch($line->lineable_type){
                        case '\App\EstimateLineCategory':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineCategory';
                            break;
                        case '\App\EstimateLineData':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineData';
                            break;
                        case '\App\EstimateLineFicha':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineFicha';
                            break;
                        case '\App\EstimateLineSeparator':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineSeparator';
                            break;
                    }
                    // end
                    $line->save();
                    $assosiation_array[$ps['id']] = $line->id;
                }
            }

            if (!isset($request->service_id)){
                $service = new Service();
                $service->generate_estimate_number();
                $service->save();
                $estimate->service_id = $service->id;
            }
            $service = $estimate->service;
            $service->estimate_summ = self::global_price($estimate);
            // set master
            if (count($service->estimates) == 1){
                $service->master_estimate_id = $estimate->id;
            }
            $service->save();
            //
            $estimate->user_id = Auth::user()->id;
            $estimate->save();
            // save to changes
            $e_c = new EstimateChange;
            $e_c->estimate_id = $estimate->id;
            $e_c->user_id = Auth::user()->id;
            $e_c->save();
            //
            $res->id = $estimate->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function get_estimate_pdf_link(Request $request, $id)
    {
        $user = Auth::user();
        $estimate = Estimate::findOrFail($id);
        if (!$user->can_with_estimate('read', $estimate)){
            return response('forbidden', 403);
        }
        return response()->json(['link' => UrlSigner::sign(env('APP_URL').'/api/estimates/pdf/'.$id, Carbon::now()->addHours(9))]);
    }

    public function show_estimate(Request $request, $id)
    {
        if (UrlSigner::validate(url()->full())){
            $estimate = Estimate::findOrFail($id);
            $creator = new EstimatePDFCreator;
            $format = $request->input('format', 'pdf');
            $type = $request->input('type', 'normal');
            $system = boolval($request->input('system', '0'));
            switch ($format) {
                case 'html':
                    $result = $creator->render_html($estimate);
                    return Response($result);
                    break;
                case 'pdf':
                    $result =  $creator->render_pdf($estimate, $system, $type);
                    $headers = [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="Orc.No '.$estimate->get_estimate_number().($estimate->service->address ? ' - '.$estimate->service->address : '').'.pdf"',
                        'Accept-Ranges' => 'bytes',
                        'Content-Length' => strlen($result)
                    ];
                    return Response($result, 200, $headers);
                    break;
            }
        }else{
            return response('forbidden', 403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $estimate = Estimate::with(['service', 'estimate_fork', 'service.region', 'changes', 'workers', 'estimate_materials', 'estimate_materials.resource', 'estimate_details', 'groups', 'estimate_pay_stages.invoices'])->findOrFail($id);
        if (!$user->can_with_estimate('read', $estimate)){
            return response('forbidden', 403);
        }
//        $format = $request->input('format', 'html');
//        if ($estimate->blocked && $estimate->user_id != Auth::user()->id && $format == 'html'){
//            return response('forbidden', 403);
//        }
        $unformatted_lines = $estimate->lines_with_join();
        // Help vars
        $formatted_lines = array();

        foreach($unformatted_lines as $line){
            $line->line_number = EstimateLine::gen_number($line->id, $unformatted_lines);
            $line->active = false;
            $line->show_insert_after = false;
            $line->children_total_price = 0.0;
            $line = $line->toArray();

            $line['workers'] = EstimateLineWorker::with('user')->where('estimate_line_id', $line['id'])->get()->toArray();

            if ($line['lineable_type']=='\App\EstimateLineFicha'){
                $line['maodeobra'] = array();
                $line['materials'] = array();
                $line['equipment'] = array();
                $line['subs'] = array();
                $line['maodeobra']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->where('resource_type', 0)->with('resource')->get()->toArray();
                for ($i = 0; $i < count($line['maodeobra']['list']); $i++){
                    $line['maodeobra']['list'][$i]['name'] = $line['maodeobra']['list'][$i]['resource']['name'];
                    $line['maodeobra']['list'][$i]['selected'] = true;
                    $line['maodeobra']['list'][$i]['total_price'] = 0;
                }
                $line['materials']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->where('resource_type', 1)->with('resource')->get()->toArray();
                for ($i = 0; $i < count($line['materials']['list']); $i++){
                    $line['materials']['list'][$i]['name'] = $line['materials']['list'][$i]['resource']['name'];
                    $line['materials']['list'][$i]['selected'] = true;
                    $line['materials']['list'][$i]['total_price'] = 0;
                }
                $line['equipment']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->where('resource_type', 2)->with('resource')->get()->toArray();
                for ($i = 0; $i < count($line['equipment']['list']); $i++){
                    $line['equipment']['list'][$i]['name'] = $line['equipment']['list'][$i]['resource']['name'];
                    $line['equipment']['list'][$i]['selected'] = true;
                    $line['equipment']['list'][$i]['total_price'] = 0;
                }
                $line['subs']['list']      = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->where('resource_type', 3)->with('resource')->get()->toArray();
                for ($i = 0; $i < count($line['subs']['list']); $i++){
                    $line['subs']['list'][$i]['name'] = $line['subs']['list'][$i]['resource']['name'];
                    $line['subs']['list'][$i]['selected'] = true;
                    $line['subs']['list'][$i]['total_price'] = 0;
                }
                $line['maodeobra']['total_price'] = 0;
                $line['materials']['total_price'] = 0;
                $line['equipment']['total_price'] = 0;
                $line['subs']     ['total_price'] = 0;
            }
            array_push($formatted_lines, $line);
        }
        $estimate->lines = $formatted_lines;

        return response()->json($estimate);
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
        try{
            $user = Auth::user();
            $estimate = Estimate::findOrFail($id);
            if (!$user->can_with_estimate('update', $estimate)){
                return response('', 403);
            }
            $estimate->fill($request->all());
            $estimate->additional_price = intval($request['additional_price']);
            $estimate->user_id = Auth::user()->id;
            $estimate->save();
            //
            // $estimate->estimate_pay_stages()->delete();
            if ($request->exists('estimate_pay_stages')) {
                foreach ($request->estimate_pay_stages as $ps) {
                    $pay_stage = new EstimatePayStage;
                    if (array_key_exists('id', $ps) && $ps['id'] != null){
                        $pay_stage = EstimatePayStage::find($ps['id']);
                    }                    
                    $pay_stage->percent = $ps['percent'];
                    $pay_stage->text = $ps['text'];
                    $pay_stage->vat_type = $ps['vat_type'];
                    $pay_stage->invoice_file = $ps['invoice_file'];
                    $pay_stage->invoice_file_name = $ps['invoice_file_name'];
                    $pay_stage->recibo_file = $ps['recibo_file'];
                    $pay_stage->recibo_file_name = $ps['recibo_file_name'];
                    $pay_stage->paid = $ps['paid'];
                    $pay_stage->invoice_number = $ps['invoice_number'];
                    $pay_stage->fact_paid = $ps['fact_paid'];
                    $pay_stage->email_template_id = $ps['email_template_id'];
                    $pay_stage->proof_file = $ps['proof_file'];
                    $pay_stage->proof_file_name = $ps['proof_file_name'];
                    $pay_stage->estimate_id = $estimate->id;
                    $pay_stage->save();
                }
            }
            if ($request->exists('deleted_pay_stages')){
                foreach ($request->deleted_pay_stages as $dps) {
                    EstimatePayStage::where('id', $dps)->delete();
                }
            }
            $assosiation_array = array();
            if ($request->exists('lines')) {
                foreach ($request->lines as $ps) {
                    if (strlen($ps['id']) == 13) {
                        $line = new EstimateLine;
                    } else {
                        $line = EstimateLine::find($ps['id']);
                    }
                    if ($line == null){
                        continue;
                    }
                    $line->lineable_type = $ps['lineable_type'];
                    $line->order = $ps['order'];
                    $line->estimate_id = $estimate->id;
                    $line->save();
                    switch ($ps['lineable_type']) {
                        case '\App\EstimateLineCategory':
                            $category = EstimateLineCategory::where('name', $ps['category_name'])->first();
                            if ($category) {
                                $line->lineable_id = $category->id;
                            } else {
                                $category = new EstimateLineCategory;
                                $category->name = $ps['category_name'];
                                $category->is_pattern = false;
                                $category->save();
                                $line->lineable_id = $category->id;
                            }
                            break;
                        case '\App\EstimateLineSeparator':
                            $separator = EstimateLineSeparator::where('name', $ps['separator_name'])->first();
                            if ($separator) {
                                $line->lineable_id = $separator->id;
                            } else {
                                $separator = new EstimateLineSeparator;
                                $separator->name = $ps['separator_name'];
                                $separator->is_pattern = false;
                                $separator->save();
                                $line->lineable_id = $separator->id;
                            }
                            break;
                        case '\App\EstimateLineData':
                            if (strlen($ps['id']) == 13) {
                                $data = new EstimateLineData;
                            } else {
                                $data = EstimateLineData::find($ps['lineable_id']);
                            }
                            $data->description = $ps['data_description'];
                            $data->note = $ps['data_note'];
                            $data->ppu = $ps['data_ppu'];
                            $data->quantity = $ps['data_quantity'];
                            $data->price = $ps['data_price'];
                            $data->estimate_unit_id = $ps['data_measure'];
                            $data->save();
                            $line->lineable_id = $data->id;
                            break;
                        case '\App\EstimateLineFicha':
                            if (strlen($ps['id']) == 13) {
                                $ficha = new EstimateLineFicha;
                            } else {
                                $ficha = EstimateLineFicha::find($ps['lineable_id']);
                            }
                            if ($ficha == null){
                                break;
                            }
                            $ficha->description = $ps['ficha_description'];
                            $ficha->note = $ps['ficha_note'];
                            $ficha->ppu = $ps['ficha_ppu'];
                            $ficha->quantity = $ps['ficha_quantity'];
                            $ficha->price = $ps['ficha_price'];
                            $ficha->estimate_unit_id = $ps['ficha_measure'];
                            $ficha->save();
                            $ficha->resources()->delete();
                            foreach (['maodeobra', 'materials', 'equipment', 'subs'] as $resname) {
                                foreach ($ps[$resname]['list'] as $ress) {
                                    $m = new EstimateLineFichaResource;
                                    $r = Resource::where('name', $ress['name'])->first();
                                    $m->price = $ress['price'];
                                    $m->resource_type = $ress['resource_type'];
                                    $m->estimate_unit_id = $ress['estimate_unit_id'];
                                    $m->estimate_line_ficha_id = $ficha->id;
                                    $m->correction = $ress['correction'];
                                    $m->quantity = $ress['quantity'];
                                    $m->resource_id = $r->id;
                                    $m->save();
                                }
                            }
                            $line->lineable_id = $ficha->id;
                            break;
                    }
                    if (isset($ps['parent_id'])) {
                        $line->parent_id = $assosiation_array[$ps['parent_id']];
                    }
                    // correct lineable part start
                    $line->correct_lineable_id = $line->lineable_id;
                    switch($line->lineable_type){
                        case '\App\EstimateLineCategory':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineCategory';
                            break;
                        case '\App\EstimateLineData':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineData';
                            break;
                        case '\App\EstimateLineFicha':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineFicha';
                            break;
                        case '\App\EstimateLineSeparator':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineSeparator';
                            break;
                    }
                    // end
                    $line->save();
                    $assosiation_array[$ps['id']] = $line->id;
                }
            }
            // remove rows
            if ($request->exists('deleted_ids')) {
                foreach ($request['deleted_ids'] as $d) {
                    $el = EstimateLine::find($d);
                    if ($el != null) {
                        switch ($el->lineable_type) {
                            case '\App\EstimateLineData':
                                EstimateLineData::find($el->lineable_id)->delete();
                                break;
                            case '\App\EstimateLineFicha':
                                EstimateLineFicha::find($el->lineable_id)->delete();
                                EstimateLineFichaResource::where('estimate_line_ficha_id', $el->lineable_id)->delete();
                                break;
                        }
                        EstimateLineWorker::where('estimate_line_id', $el->id)->delete();
                        $el->delete();
                    }
                }
            }
            // save to changes
            $e_c = new EstimateChange;
            $e_c->estimate_id = $estimate->id;
            $e_c->user_id = Auth::user()->id;
            $e_c->save();
            //
            $service = $estimate->service;
            $service->estimate_summ = self::global_price($estimate);
            $service->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $estimate = Estimate::find($id);
        if (!$user->can_with_estimate('delete', $estimate)){
            return response('', 403);
        }
        Estimate::delete_with_relations($id);
    }

    public function documents(Request $request, $id)
    {
        $estimate = Estimate::find($id);
        $docs = EstimateDocument::select('url', 'name', 'default_count as count', 'default_checked as checked')->get()->toArray();
        // Add planning
        $planning_pdf = array('count' => 1, 'checked' => false, 'url' => $request->getSchemeAndHttpHost().'/plannings/'.$id, 'name' => 'Planning');
        array_unshift($docs, $planning_pdf);
        foreach($estimate->estimate_materials()->get() as $material){
            $res = $material->resource()->first();
            foreach($res->resource_attachments()->get() as $attachment){
                $new_at = array('count' => 1, 'checked' => false, 'url' => $attachment->url, 'name' => $attachment->name);
                array_push($docs, $new_at);
            }
        }
        return $docs;
    }

    public function concat_documents(Request $request, $id)
    {
        $pdf = new FPDI();
        foreach(json_decode($request['docs']) as $d){
            if ($d->checked){
                for($i = 0; $i < $d->count; $i++){
                    $gen_file = strpos($d->url, '/plannings/') !== false;
                    if ($gen_file){
                        $estimate = Estimate::find($id);
                        $creator = new PlanningPDFCreator;
                        $result =  $creator->render_pdf($estimate);
                        $tmp_file = public_path().'/img/uploads/temp/'.uniqid('', true). '.pdf';
                        $fp = fopen($tmp_file, 'w');
                        fwrite($fp, $result);
                        fclose($fp);
                        $pageCount = $pdf->setSourceFile($tmp_file);
                    }else{
                        $pageCount = $pdf->setSourceFile(public_path().$d->url);
                    }
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($templateId);
                        if ($size['w'] > $size['h']) {
                            $pdf->AddPage('L', array($size['w'], $size['h']));
                        } else {
                            $pdf->AddPage('P', array($size['w'], $size['h']));
                        }
                        $pdf->useTemplate($templateId);
                    }
                    if ($gen_file){
                        unlink($tmp_file);
                    }
                }
            }
        }

        return Response($pdf->Output(), '200');
    }

    public function set_master_estimate(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $estimate = Estimate::find($request->id);
            $service = $estimate->service;
            $service->master_estimate_id = $estimate->id;
            $service->estimate_summ = self::global_price($estimate);
            $service->save();
            $res->estimate_summ = $service->estimate_summ;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function block(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $estimate = Estimate::find($id);
            if ($estimate->user_id == Auth::user()->id || Auth::user()->id == GlobalSettings::first()->unlocker_user_id || Auth::user()->is_admin) {
                $estimate->blocked = $request['blocked'];
                $estimate->save();
            }else{
                $res->errcode = 1;
                $res->errmess = trans('estimate::estimate.Block_state_can_change_only_owner');
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    private function global_price($estimate){
        $price_with_addit = $estimate->price;
        if($estimate->discount){
            $price_with_addit -= $price_with_addit * ($estimate->discount / 100);
        }
        return round($price_with_addit, 2);
    }

    public function export(Request $request)
    {
        $id = $request['id'];
        $estimate = Estimate::findOrFail($id);
        $fileName = trans('estimate.estimate_number') . $estimate->get_estimate_number() . '.xlsx';

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($fileName);
        $writer->addRow([
            '№',
            trans('estimate.Description'),
            trans('estimate.Un'),
            trans('estimate.Quant'),
            trans('estimate.PU') 
        ]);

        $lines = $estimate->lines_with_join();
        $lines = EstimateLine::array_to_tree_and_to_array($lines);
        $begin_of_estimate = true;
        $current_total_price = 0.0;
        $full_price = 0.0;

        foreach ($lines as $line) {
            switch ($line['lineable_type']) {
                case '\App\EstimateLineSeparator':
                    $writer->addRow([
                        EstimateLine::gen_number($line['id'], $lines),
                        $line['separator_name']
                    ]);
                    break;
                case '\App\EstimateLineCategory':
                    if (!$begin_of_estimate && $line['parent_id']==null){
                        $full_price += $current_total_price;
                        $current_total_price = 0.0;
                    } else {
                        $begin_of_estimate = false;
                    }
                    $writer->addRow([
                        EstimateLine::gen_number($line['id'], $lines),
                        $line['category_name']
                    ]);
                    break;
                case '\App\EstimateLineData':
                    $un = EstimateUnit::convertUnit($line['data_measure']);
                    $data_line_price = self::data_with_additional($line['data_ppu'], $estimate->additional_price);
                    $data_total_price = round($data_line_price*$line['data_quantity'], 2);
                    $writer->addRow([
                        EstimateLine::gen_number($line['id'], $lines),
                        $line['data_description'],
                        $un,
                        ($un=="" ? "" :  number_format($line['data_quantity'], 2, $dec_point = ".", $thousands_sep = ",") ),
                        $un=="" ? "" :  number_format($data_line_price, 2, $dec_point = ".", $thousands_sep = ",")
                    ]);                        
                    $current_total_price += $data_total_price;
                    break;
                case '\App\EstimateLineFicha':
                    $un = EstimateUnit::convertUnit($line['ficha_measure']);
                    $ficha_line_price =  self::data_with_additional($line['ficha_ppu'], $estimate->additional_price);
                    $ficha_total_price = round($ficha_line_price*$line['ficha_quantity'], 2);
                    $writer->addRow([
                        EstimateLine::gen_number($line['id'], $lines),
                        $line['ficha_description'],
                        $un,
                        ($un=="" ? "" :  number_format($line['ficha_quantity'], 2, $dec_point = ".", $thousands_sep = ",") ),
                        $un=="" ? "" :  number_format($ficha_line_price, 2, $dec_point = ".", $thousands_sep = ",")
                    ]);                            
                    $current_total_price += $ficha_total_price;
                    break;
            }                
        }

        $writer->close();

        return response()->file($fileName)->deleteFileAfterSend(true);
    }

    private function data_with_additional($data, $ad){
        return round($data*(1+$ad/100.0), 2);
    }

}
