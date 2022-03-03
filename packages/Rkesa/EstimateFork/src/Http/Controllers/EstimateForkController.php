<?php

namespace Rkesa\EstimateFork\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Rkesa\Estimate\Http\Controllers\EstimateController;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateLineCategory;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Estimate\Models\EstimateLineSeparator;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\Resource;
use Rkesa\EstimateFork\Models\EntityChange;
use Rkesa\EstimateFork\Models\EntityChangeResource;
use Rkesa\EstimateFork\Models\EntityRule;
use Rkesa\EstimateFork\Models\EntityRuleResource;
use Rkesa\EstimateFork\Models\EstimateFork;
use Rkesa\EstimateFork\Models\ForkEntity;

class EstimateForkController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $fields = $request->input('fields', '*');
            $fields_array = explode(",", $fields);

            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'created_at');
            if ($sort == ''){ $sort = 'created_at'; }
            $order = $request->input('order', 'asc');
            if ($order == ''){ $order = 'asc'; }

            $forks = EstimateFork::select($fields_array);

            $forks->orderBy($sort, $order);

            $res->total = $forks->count();
            $res->rows = $forks->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

//    public function create(Request $request)
//    {
//        $fork = (object) array('name' => 'New fork', 'fork_entities' => []);
//        return view('estimate_fork::estimate_fork/create', [
//            'estimate_fork' => $fork,
//            'resources' => Resource::orderBy('name')->get(),
//            'fichas' => EstimateLineFicha::where('is_pattern', true)->orderBy('name')->get(),
//            'units' => EstimateUnit::all()
//        ]);
//    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $fork = new EstimateFork;
            $fork->name = $request['name'];
            $fork->save();
            foreach($request['fork_entities'] as $entity){
                $fork_entity = new ForkEntity;
                $fork_entity->fill($entity);
                $fork_entity->estimate_fork_id = $fork->id;
                $fork_entity->save();
                foreach($entity['entity_rules'] as $rule){
                    $entity_rule = new EntityRule;
                    $entity_rule->fill($rule);
                    $entity_rule->fork_entity_id = $fork_entity->id;
                    $entity_rule->save();
                    foreach($rule['resources'] as $req_resource){
                        $resource = new EntityRuleResource;
                        $resource->fill($req_resource);
                        $resource->entity_rule_id = $entity_rule->id;
                        $resource->save();
                    }
                }
                foreach($entity['entity_changes'] as $change){
                    $entity_change = new EntityChange;
                    $entity_change->fill($change);
                    $entity_change->fork_entity_id = $fork_entity->id;
                    $entity_change->save();
                    foreach($change['resources'] as $req_resource){
                        $resource = new EntityChangeResource;
                        $resource->fill($req_resource);
                        $resource->entity_change_id = $entity_change->id;
                        $resource->save();
                    }
                }
            }
            $res->id = $fork->id;
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
//        return view('estimate_fork::estimate_fork/edit', [
//            'estimate_fork' => ,
//            'resources' => Resource::orderBy('name')->get(),
//            'fichas' => EstimateLineFicha::where('is_pattern', true)->orderBy('name')->get(),
//            'units' => EstimateUnit::all()
//        ]);

        return EstimateFork::with(['fork_entities.entity_rules.resources', 'fork_entities.entity_changes.resources'])->find($id);
    }

    public function update(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $fork = EstimateFork::find($request['id']);
            $fork->name = $request['name'];
            $fork->save();
            // remove first
            foreach($fork->fork_entities as $entity){
                foreach($entity->entity_rules as $rule){
                    EntityRuleResource::where('entity_rule_id', $rule->id)->delete();
                }
                EntityRule::where('fork_entity_id', $entity->id)->delete();
                EntityChange::where('fork_entity_id', $entity->id)->delete();
            }
            ForkEntity::where('estimate_fork_id', $fork->id)->delete();
            //
            foreach($request['fork_entities'] as $entity){
                $fork_entity = new ForkEntity;
                $fork_entity->fill($entity);
                $fork_entity->estimate_fork_id = $fork->id;
                $fork_entity->save();
                foreach($entity['entity_rules'] as $rule){
                    $entity_rule = new EntityRule;
                    $entity_rule->fill($rule);
                    $entity_rule->fork_entity_id = $fork_entity->id;
                    $entity_rule->save();
                    foreach($rule['resources'] as $req_resource){
                        $resource = new EntityRuleResource;
                        $resource->fill($req_resource);
                        $resource->entity_rule_id = $entity_rule->id;
                        $resource->save();
                    }
                }
                foreach($entity['entity_changes'] as $change){
                    $entity_change = new EntityChange;
                    $entity_change->fill($change);
                    $entity_change->fork_entity_id = $fork_entity->id;
                    $entity_change->save();
                    foreach($change['resources'] as $req_resource){
                        $resource = new EntityChangeResource;
                        $resource->fill($req_resource);
                        $resource->entity_change_id = $entity_change->id;
                        $resource->save();
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

    public function generate_fork($estimate, $fork)
    {
        // DONT FORGET TO FILL correct_lineable!!!!!!!!!!! (see EstimateController@update:530)
        $changed = [];
        // Remove existed fork estimates
        $exist_estimate = Estimate::where(['fork_id' => $fork->id, 'fork_origin_estimate_id' => $estimate->id])->first();
        if ($exist_estimate != null){
            Estimate::delete_with_relations($exist_estimate->id);
        }
        //
        $new_estimate = $estimate->replicate();
        if ($exist_estimate) {
            $new_estimate->id = $exist_estimate->id;
        }
        $new_estimate->fork_origin_estimate_id = $estimate->id;
        $new_estimate->fork_id = $fork->id;
        $new_estimate->save();
        $pay_stages = EstimatePayStage::where('estimate_id', $estimate->id)->get();
        foreach($pay_stages as $pay_stage) {
            $new_pay_stage = $pay_stage->replicate();
            $new_pay_stage->estimate_id = $new_estimate->id;
            $new_pay_stage->save();
        }
        foreach ($estimate->lines as $lineable) {
            $new_lineable = self::clone_line($lineable);
            $new_lineable->estimate_id = $new_estimate->id;
            if ($lineable->parent_id != null) {
                $new_lineable->parent_id = $changed[$lineable->parent_id];
            }
            $new_lineable->save();
            $changed[$lineable->id] = $new_lineable->id;
        }
        $new_estimate->load('lines');
//        Log::info('---------------------');
        foreach ($new_estimate->lines as $new_lineable) {
            foreach ($fork->fork_entities as $entity) {
//                $debug = false;
//                $old_id = array_search($new_lineable->id, $changed);
//                if ($fork->id == 3 && $entity->order == 20) {
//                    $debug = true;
//                    Log::info('ORIGINAL ID');
//                    Log::info($old_id);
//                }
                $match = true;
                if ($entity->object == 1 && $new_lineable->lineable_type == '\App\EstimateLineData'){
                    $data = EstimateLineData::find($new_lineable->lineable_id);
                    foreach($entity->entity_rules as $rule) {
                        switch ($rule->field) {
                            case 1: // Description
                                switch ($rule->rule_type) {
                                    case 1: // Include
//                                        if ($debug){
//                                            Log::info('Data description Include - ');
//                                        }
                                        if (strpos(str_replace(array("\r", "\n"), ' ', $data->description), $rule->subject) === false) {
                                            $match = false;
//                                            if ($debug){
//                                                Log::info('false');
//                                            }
                                            break 3;
                                        }
//                                        if ($debug){
//                                            Log::info('true');
//                                        }
                                        break;
                                    case 2: // Equals
                                        if ($rule->subject != $data->description) {
                                            $match = false;
                                            break 3;
                                        }
                                        break;
                                }
                                break;
                            case 2: // Price
                                switch ($rule->rule_type) {
                                    case 1: // Equals
                                        if (floatval($rule->subject) != $data->price) {
                                            $match = false;
                                            break 3;
                                        }
                                        break;
                                    case 2: // Include
                                        // Not possible
                                        break;
                                }
                                break;
                        }
                    }
                }elseif ($entity->object == 2 && $new_lineable->lineable_type == '\App\EstimateLineFicha'){
                    $ficha = EstimateLineFicha::find($new_lineable->lineable_id);
                    foreach($entity->entity_rules as $rule){
                        switch($rule->field){
                            case 1: // Description
                                switch($rule->rule_type){
                                    case 1: // Include
//                                            if ($debug){
//                                                if ($old_id == 157837){
//                                                    Log::info(str_replace(array("\r", "\n"), ' ', $ficha->description));
//                                                    Log::info('------------');
//                                                    Log::info($rule->subject);
//                                                }
//                                                Log::info('Ficha description Include - ');
//                                            }
                                        if (strpos(str_replace(array("\r", "\n"), ' ', $ficha->description), $rule->subject) === false){
                                            $match = false;
//                                                if ($debug){
//                                                    Log::info('false');
//                                                }
                                            break 3;
                                        }
//                                            if ($debug){
//                                                Log::info('true');
//                                            }
                                        break;
                                    case 2: // Equals
                                        if ($rule->subject != $ficha->description){
                                            $match = false;
                                            break 3;
                                        }
                                        break;
                                }
                                break;
                            case 2: // Price
                                switch($rule->rule_type){
                                    case 1: // Equals
                                        if (floatval($rule->subject) != $ficha->price){
                                            $match = false;
                                            break 3;
                                        }
                                        break;
                                    case 2: // Include
                                        // Not possible
                                        break;
                                }
                                break;
                            case 3: // Resource
                                foreach($rule->resources as $rule_resource){
                                    $resource_match = false;
                                    foreach($ficha->resources as $ficha_resource){
                                        switch($rule_resource->field){
                                            case 1: // Name
                                                switch($rule_resource->rule_type){
                                                    case 1: // Equals
                                                        if ($ficha_resource->resource_id == $rule_resource->resource_id){
                                                            $resource_match = true;
                                                        }
                                                        break;
                                                    case 2: // Include
                                                        if (strpos($ficha_resource->resource->name, $rule_resource->subject) !== false){
                                                            $resource_match = true;
                                                        }
                                                        break;
                                                }
                                                break;
                                            case 2: // Price
                                                switch($rule_resource->rule_type){
                                                    case 1: // Equals
                                                        if (floatval($rule_resource->subject) == $ficha_resource->price){
                                                            $resource_match = true;
                                                        }
                                                        break;
                                                    case 2: // Include
                                                        // Not possible
                                                        break;
                                                }
                                                break;
                                        }
                                    }
                                    if (!$resource_match){
                                        $match = false;
                                        break 3;
                                    }
                                }
                                break;
                        }
                    }
                } elseif ($entity->object == 3 && $new_lineable->lineable_type == '\App\EstimateLineCategory'){
                    $subcategory = EstimateLineCategory::find($new_lineable->lineable_id);
                    $category_line = EstimateLine::find($new_lineable->parent_id);
                    $category = null;
                    if ($category_line && $category_line->lineable_type == '\App\EstimateLineCategory'){
                        $category = EstimateLineCategory::find($category_line->lineable_id);
                    }
                    if ($category != null){
                        if (!preg_match ('/'.$entity->category.'/', $category->name) || !preg_match ('/'.$entity->subcategory.'/', $subcategory->name)){
                            $match = false;
                        }
                    }else{
                        $match = false;
                    }
                } elseif ($entity->object == 4 && $new_lineable->lineable_type == '\App\EstimateLineCategory') {
                    $category = EstimateLineCategory::find($new_lineable->lineable_id);
                    if ($category != null){
                        if (!preg_match ('/'.$entity->category.'/', $category->name)){
                            $match = false;
                        }
                    }else{
                        $match = false;
                    }
                } else {
                    $match = false;
                }
                if ($match){
                    $new_lineable->attention = true;
                    $new_lineable->save();
                    $data = null;
                    $ficha = null;
                    if ($entity->object == 1 && $new_lineable->lineable_type == '\App\EstimateLineData'){
                        $data = EstimateLineData::find($new_lineable->lineable_id);
                    }
                    if ($entity->object == 2 && $new_lineable->lineable_type == '\App\EstimateLineFicha'){
                        $ficha = EstimateLineFicha::find($new_lineable->lineable_id);
                    }
                    foreach($entity->entity_changes as $change){
//                        Log::info(strval($new_lineable->id).' '.strval($entity->order).' '.strval($change->change_type));
                        switch($change->change_type){
                            case 1: // Change description
                                if ($entity->object == 1){
                                    $data->description = $change->description;
                                    $data->save();
                                }else{
                                    $ficha->description = $change->description;
                                    $ficha->save();
                                }
                                break;
                            case 2: // Change quantity
                                if ($change->quantity_type == 1){ // fixed
                                    $destination_quantity = $change->quantity;
                                }else{ // from another ficha
                                    $destination_quantity = null;
                                    foreach ($new_estimate->lines as $find_lineable) {
                                        if ($find_lineable->lineable_type == '\App\EstimateLineFicha'){
                                            $find_ficha = EstimateLineFicha::find($find_lineable->lineable_id);
                                            if ($find_ficha && strpos(str_replace(array("\r", "\n"), ' ', $find_ficha->description), $change->description) !== false){
                                                $destination_quantity = $find_ficha->quantity;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if ($destination_quantity) {
                                    if ($entity->object == 1) {
                                        $data->quantity = $destination_quantity;
                                        $data->price = $data->quantity*$data->ppu;
                                        $data->save();
                                    } else {
                                        $ficha->quantity = $destination_quantity;
                                        $ficha->price = $ficha->quantity*$ficha->ppu;
                                        $ficha->save();
                                    }
                                }
                                break;
                            case 3: // Add resource
                                $f_res = Resource::find($change->resource_id);
                                $r = new EstimateLineFichaResource;
                                $r->quantity = $change->quantity;
                                $r->price = $change->price;
                                $r->resource_id = $f_res->id;
                                $r->resource_type = $f_res->resource_type;
                                $r->estimate_unit_id = $f_res->estimate_unit_id;
                                $r->estimate_line_ficha_id = $ficha->id;
                                $r->correction = $change->correction;
                                //in_array($f_res->resource_type, [0,3]) ? 1 : 0;
                                $r->save();
                                self::update_ficha_price($ficha);
                                break;
                            case 4: // Change resource
                                $resources = EstimateLineFichaResource::where(['resource_id' => $change->resource_id, 'estimate_line_ficha_id' => $ficha->id])->get();
                                foreach($resources as $r){
                                    foreach($change->resources as $res){
                                        switch($res->field){
                                            case 1: // Resource type
                                                $rrr = Resource::find($res->resource_id);
                                                $r->resource_id = $rrr->id;
                                                $r->resource_type = $rrr->resource_type;
                                                $r->estimate_unit_id = $rrr->estimate_unit_id;
                                                $r->save();
                                                break;
                                            case 2: // Price
                                                $r->price = floatval($res->subject);
                                                $r->save();
                                                break;
                                            case 3: // Quantity
                                                $r->quantity = floatval($res->subject);
                                                $r->save();
                                                break;
                                            case 4: // Correction
                                                $r->correction = floatval($res->subject);
                                                $r->save();
                                                break;
                                        }
                                    }
                                }
                                self::update_ficha_price($ficha);
                                break;
                            case 5: // Remove resource
                                EstimateLineFichaResource::where(['resource_id' => $change->resource_id, 'estimate_line_ficha_id' => $ficha->id])->delete();
                                self::update_ficha_price($ficha);
                                break;
                            case 6: // Add ficha
                                $template_ficha = EstimateLineFicha::find($change->ficha_id);
                                $new_ficha = $template_ficha->replicate();
                                $new_ficha->is_pattern = false;
                                $new_ficha->save();

                                $template_resources = EstimateLineFichaResource::where('estimate_line_ficha_id', $template_ficha->id)->get();
                                foreach($template_resources as $template_resource){
                                    $resource = $template_resource->replicate();
                                    $resource->estimate_line_ficha_id = $new_ficha->id;
                                    $resource->save();
                                }

                                self::update_ficha_price($new_ficha);

                                $new_estimate_line = new EstimateLine;
                                $new_estimate_line->lineable_id = $new_ficha->id;
                                $new_estimate_line->lineable_type = '\App\EstimateLineFicha';
                                $new_estimate_line->attention = true;
                                $new_estimate_line->estimate_id = $new_lineable->estimate_id;
                                switch($entity->object) {
                                    case 1: // to data's parent
                                    case 2: // to ficha's parent
                                        $new_estimate_line->parent_id = $new_lineable->parent_id;
                                        $lines_after_ficha = EstimateLine::where('parent_id', $new_lineable->parent_id)->where('order', '>', $new_lineable->order)->get();
                                        foreach($lines_after_ficha as $line_after_ficha){
                                            $line_after_ficha->order++;
                                            $line_after_ficha->save();
                                        }
                                        $new_estimate_line->order = $new_lineable->order + 1;
                                        break;
                                    case 3: // to subcategory
                                        $new_estimate_line->parent_id = $new_lineable->id;
                                        if ($change->position == 1) { // top
                                            $lines_after = EstimateLine::where('parent_id', $new_lineable->id)->get();
                                            foreach($lines_after as $line_after_ficha){
                                                $line_after_ficha->order++;
                                                $line_after_ficha->save();
                                            }
                                            $new_estimate_line->order = 1;
                                        }else{ // bottom
                                            $last_estimate_line = EstimateLine::where('parent_id', $new_lineable->id)->orderBy('order', 'desc')->first();
                                            if ($last_estimate_line != null) {
                                                $new_estimate_line->order = $last_estimate_line->order + 1;
                                            }else{
                                                $new_estimate_line->order = 1;
                                            }
                                        }
                                        break;
                                }
                                $new_estimate_line->save();
                                break;
                            case 7: // Add artigo
                                $data = new EstimateLineData;
                                $data->price = $change->price;
                                $data->quantity = $change->quantity;
                                $data->estimate_unit_id = $change->estimate_unit_id;
                                $data->description = $change->description;
                                $data->note = $change->note;
                                $data->save();

                                $new_estimate_line = new EstimateLine;
                                $new_estimate_line->lineable_id = $data->id;
                                $new_estimate_line->lineable_type = '\App\EstimateLineData';
                                $new_estimate_line->attention = true;
                                $new_estimate_line->estimate_id = $new_lineable->estimate_id;
                                switch($entity->object) {
                                    case 1: // to data's parent
                                    case 2: // to ficha's parent
                                        $new_estimate_line->parent_id = $new_lineable->parent_id;
                                        $lines_after_ficha = EstimateLine::where('parent_id', $new_lineable->parent_id)->where('order', '>', $new_lineable->order)->get();
                                        foreach($lines_after_ficha as $line_after_ficha){
                                            $line_after_ficha->order++;
                                            $line_after_ficha->save();
                                        }
                                        $new_estimate_line->order = $new_lineable->order + 1;
                                        break;
                                    case 3: // to subcategory
                                        $new_estimate_line->parent_id = $new_lineable->id;
                                        if ($change->position == 1) { // top
                                            $lines_after = EstimateLine::where('parent_id', $new_lineable->id)->get();
                                            foreach($lines_after as $line_after_ficha){
                                                $line_after_ficha->order++;
                                                $line_after_ficha->save();
                                            }
                                            $new_estimate_line->order = 1;
                                        }else { // bottom
                                            $last_estimate_line = EstimateLine::where('parent_id', $new_lineable->id)->orderBy('order', 'desc')->first();
                                            if ($last_estimate_line != null) {
                                                $new_estimate_line->order = $last_estimate_line->order + 1;
                                            } else {
                                                $new_estimate_line->order = 1;
                                            }
                                        }
                                        break;
                                }
                                $new_estimate_line->save();
                                break;
                            case 8: // Remove object
                                if ($entity->object == 1){ // data
                                    $data->delete();
                                }
                                if ($entity->object == 2){ // ficha
                                    $ficha->delete();
                                    EstimateLineFichaResource::where('estimate_line_ficha_id', $new_lineable->id)->delete();
                                }
                                $new_lineable->delete();
                                break 3;
                            case 9: // Change note
                                if ($entity->object == 1){
                                    $data->note = $change->note;
                                    $data->save();
                                }else{
                                    $ficha->note = $change->note;
                                    $ficha->save();
                                }
                                break;
                            case 10: // Add subcategory
                                $subcategory = new EstimateLineCategory;
                                $subcategory->name = $change->subcategory;
                                $subcategory->is_pattern = false;
                                $subcategory->category_id = $new_lineable->lineable_id;
                                $subcategory->save();

                                $subcategory_line = new EstimateLine;
                                $subcategory_line->lineable_type = '\App\EstimateLineCategory';
                                $subcategory_line->lineable_id = $subcategory->id;
                                $subcategory_line->attention = true;
                                $subcategory_line->parent_id = $new_lineable->id;
                                $subcategory_line->estimate_id = $new_lineable->estimate_id;
                                $last_estimate_line = EstimateLine::where('parent_id', $new_lineable->id)->orderBy('order', 'desc')->first();
                                if ($last_estimate_line != null) {
                                    $subcategory_line->order = $last_estimate_line->order + 1;
                                }else{
                                    $subcategory_line->order = 1;
                                }
                                $subcategory_line->save();
                                break;
                            case 11: // Change unit
                                if ($entity->object == 1){
                                    $data->estimate_unit_id = $change->estimate_unit_id;
                                    $data->save();
                                }else{
                                    $ficha->estimate_unit_id = $change->estimate_unit_id;
                                    $ficha->save();
                                }
                                break;
                        }
                    }
                }
            }
        }
        $new_estimate->price = self::calculate_price_for_estimate($new_estimate);
        $new_estimate->save();
    }

    public function generate_forks(Request $request, $id)
    {
        $estimate = Estimate::find($id);
        if ($estimate){
            $forks = EstimateFork::all();
            foreach($forks as $fork){
                self::generate_fork($estimate, $fork);
            }
        }
//        Log::info('----------------------');
    }

    private function clone_line($lineable){
        $new_line = null;
        $line = null;
        switch($lineable->lineable_type){
            case '\App\EstimateLineSeparator':
                $line = EstimateLineSeparator::find($lineable->lineable_id);
                break;
            case '\App\EstimateLineCategory':
                $line = EstimateLineCategory::find($lineable->lineable_id);
                break;
            case '\App\EstimateLineData':
                $line = EstimateLineData::find($lineable->lineable_id);
                break;
            case '\App\EstimateLineFicha':
                $line = EstimateLineFicha::find($lineable->lineable_id);
                break;
        }
        $new_line = $line->replicate();
        $new_line->save();
        if ($lineable->lineable_type == '\App\EstimateLineFicha'){
            $resources = EstimateLineFichaResource::where('estimate_line_ficha_id', $line->id)->get();
            foreach($resources as $resource){
                $new_resource = $resource->replicate();
                $new_resource->estimate_line_ficha_id = $new_line->id;
                $new_resource->save();
            }
        }
        $new_lineable = $lineable->replicate();
        $new_lineable->lineable_id = $new_line->id;
        return $new_lineable;
    }

    private function calculate_price_for_estimate($estimate){
        $lines = $estimate->lines_with_join();
        $current_total_price = 0;
        foreach ($lines as $line) {
            switch ($line['lineable_type']) {
                case '\App\EstimateLineData':
                    $current_total_price += $line['data_price'];
                    break;
                case '\App\EstimateLineFicha':
                    $current_total_price += $line['ficha_price'];
                    break;
            }
        }
        return $current_total_price;
    }

    private function update_ficha_price($ficha){
        $total_price = 0;
        $ficha->load('resources');
        foreach($ficha->resources as $resource){
            $resource_total_price = $resource->price*$resource->quantity*$resource->correction;
            if (in_array($resource->resource_type, [1,2])){
                $resource_total_price /= 100;
                $resource_total_price += $resource->price*$resource->quantity;
            }
            $total_price += $resource_total_price;
        }
        $ficha->ppu = $total_price;
        $ficha->price = $total_price*$ficha->quantity;
        $ficha->save();
    }
}
