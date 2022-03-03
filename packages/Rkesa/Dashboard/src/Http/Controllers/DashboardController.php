<?php

namespace Rkesa\Dashboard\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Rkesa\Dashboard\Models\Dashboard;
use Rkesa\Dashboard\Http\Helpers\DashboardDataBuilder;
use Exception;
use Log;
use Rkesa\Dashboard\Models\DashboardEntity;
use Rkesa\Dashboard\Models\DashboardEntityField;
use Rkesa\Dashboard\Models\DashboardWidget;
use Validator;
use Auth;
use App\User;
use Rkesa\Service\Models\ServiceState;

class DashboardController extends Controller
{
    public function config(){
        $res = (object)array();
        $res->entity_field_types = config('dashboard.entity.fields.columns');
        $res->max_widgets = 10; //config('dashboard.max_widgets');
        return json_encode($res);
    }

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

            $dashboards = Dashboard::select($fields_array)->withCount([
                'entities' => function($query) {
                    $query->where('hide', '!=', true)
                        ->whereHas('state', function($query2){
                            $query2->where('type', 0);
                        });
                },
                'widgets']);

            $dashboards->orderBy($sort, $order);

            $res->total = $dashboards->count();
            $res->rows = $dashboards->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function store(Request $request) {

        if(!$request->ajax())
            return response()->json(null, 403);

        if(empty($request->all()))
            return response()->json(['OK' => false, 'message' => 'invalid JSON'], 422);

        $validation = Dashboard::validation();
        $validator = Validator::make($request->all(), $validation->rules, $validation->messages);
        if ($validator->fails())
            return response()->json(['OK' => false, 'message' => 'invalid data', 'errors' => $validator->errors()], 422);

        $post = $request->all();
        $widgets = $post['widgets'];
        $entities = $post['entities'];
        $dashboard = new Dashboard();
        $dashboard->name = $post['name'];
        $dashboard->save();
        foreach($entities as $entity) {
            $entityModel = new DashboardEntity();
            $entityModel->hide =  $entity['hide'];
            $entityModel->service_state_id = $entity['service_state_id'];
            $dashboard_entity = $dashboard->entities()->save($entityModel);
            foreach($entity['fields'] as $field) {
                $fieldModel = new DashboardEntityField();
                $fieldModel->type = $field['type'];
                $fieldModel->dashboard_entity_id = $entityModel->id;
                $fieldModel->event_type_id = array_key_exists('event_type_id', $field) ? $field['event_type_id'] : null;
                $fieldModel->save();
                $dashboard_entity->fields()->save($fieldModel);
            }
        }
        foreach($widgets as $widget) {
            $widgetModel = new DashboardWidget();
            $widgetModel->widget_type = $widget['widget_type'];
            if(isset($widget['size']))
                $widgetModel->size = $widget['size'];
            if(isset($widget['service_state_id']))
                $widgetModel->service_state_id = $widget['service_state_id'];
            if(isset($widget['event_type_id']))
                $widgetModel->event_type_id = $widget['event_type_id'];
            if(isset($widget['data_type']))
                $widgetModel->data_type = $widget['data_type'];
            if(isset($widget['color1']))
                $widgetModel->color1 = $widget['color1'];
            if(isset($widget['color2']))
                $widgetModel->color2 = $widget['color2'];
            if(isset($widget['color3']))
                $widgetModel->color3 = $widget['color3'];
            if(isset($widget['color4']))
                $widgetModel->color4 = $widget['color4'];
            if(isset($widget['data']))
                $widgetModel->data = $widget['data'];

            $w = $dashboard->widgets()->save($widgetModel);
        }
        $dashboard->save();
        return response()->json([
            'OK' => true,
            'message' => 'Dashboard successfully saved.',
            'dashboard' => [
                'id' => $dashboard->id
            ]
        ], 200);
    }

    public function show(Request $request, $id) {
//        if (Auth::user()->dashboard_id == null || Auth::user()->dashboard_id == 0){
//            return redirect('/home');
//        }
//
//
//
//        $statuses = ServiceState::where('type', '=', 0)
//            ->select('id', 'name', 'order', 'icon')
//            ->get();
//
//        $rangeStart = Carbon::now('Europe/Lisbon')->subMonth();
//        $rangeEnd =  Carbon::now('Europe/Lisbon');
//
//        $filters = [
//            'range' => [
//                'start' => $rangeStart,
//                'end' => $rangeEnd
//            ],
//            'responsible' => Auth::user()->id,
//            'use_range' => null
//        ];
//
//        return view('dashboard::dashboard/show', [
//            'filters' =>  json_encode($filters),
//        ]);
        $res = (object)array();
        $res->errcode = 0;
        try{
            $dashboard = Dashboard::with(['entities','entities.state','entities.fields','entities.state', 'widgets', 'widgets.state'])->find($id);

            $dashboard->entity_default_rows_count = config('dashboard.stage.rows.count');

            return $dashboard;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function update(Request $request, $id) {
        if(!$request->ajax())
            return response()->json(null, 403);

        if(empty($request->all()))
            return response()->json(['OK' => false, 'message' => 'invalid JSON'], 422);

        $validation = Dashboard::validation();
        $validator = Validator::make($request->all(), $validation->rules, $validation->messages);
        if ($validator->fails())
            return response()->json(['OK' => false, 'message' => 'invalid data', 'errors' => $validator->errors()], 422);

        $post = $request->all();
        $entities = $post['entities'];
        $widgets = $post['widgets'];
        $dashboard = Dashboard::with(['entities','entities.fields','widgets'])->where('id', '=', $id)->first();
        $dashboard->name = $post['name'];
        $dashboard->entities()->delete();
        $dashboard->widgets()->delete();

        foreach($entities as $entity) {
            $entityModel = new DashboardEntity();
            $entityModel->hide =  $entity['hide'];
            $entityModel->service_state_id = $entity['service_state_id'];
            $dashboard_entity = $dashboard->entities()->save($entityModel);
            foreach($entity['fields'] as $field) {
                $fieldModel = new DashboardEntityField();
                $fieldModel->type = $field['type'];
                $fieldModel->dashboard_entity_id = $entityModel->id;
                $fieldModel->event_type_id = array_key_exists('event_type_id', $field) ? $field['event_type_id'] : null;
                $fieldModel->save();
                $dashboard_entity->fields()->save($fieldModel);
            }
        }
        foreach($widgets as $widget) {
            $widgetModel = new DashboardWidget();
            $widgetModel->widget_type = $widget['widget_type'];
            if(isset($widget['size']))
                $widgetModel->size = $widget['size'];
            if(isset($widget['service_state_id']))
                $widgetModel->service_state_id = $widget['service_state_id'];
            if(isset($widget['event_type_id']))
                $widgetModel->event_type_id = $widget['event_type_id'];
            if(isset($widget['data_type']))
                $widgetModel->data_type = $widget['data_type'];
            if(isset($widget['color1']))
                $widgetModel->color1 = $widget['color1'];
            if(isset($widget['color2']))
                $widgetModel->color2 = $widget['color2'];
            if(isset($widget['color3']))
                $widgetModel->color3 = $widget['color3'];
            if(isset($widget['color4']))
                $widgetModel->color4 = $widget['color4'];
            if(isset($widget['data']))
                $widgetModel->data = $widget['data'];

            $w = $dashboard->widgets()->save($widgetModel);
        }

        $dashboard->save();

        return response()->json([
            'OK' => true,
            'message' => 'Dashobard succesfully saved.',
            'dashboard' => [
                'id' => $dashboard->id
            ]
        ], 200);
    }

    public function destroy(Request $request, $id) {
        Dashboard::find($id)->delete();
    }

    public function short_entity(Request $request) {

        $builder = new DashboardDataBuilder($request);

        $rows = $builder->build_entity('short');

        return response()->json([
                'total_rows_count' => count($builder->build_entity('full')),
                'rows' => $rows,
                'total_master_sum' => self::get_total_master_sum($builder),
                'master_sum_index' => $builder->get_master_sum_index(),
            ], 200);
    }

    public function full_entity(Request $request) {

        $builder = new DashboardDataBuilder($request);

        $rows = $builder->build_entity('full');

        return response()->json([
                'total_rows_count' => count($rows),
                'rows' => $rows,
                'total_master_sum' => self::get_total_master_sum($builder),
                'master_sum_index' => $builder->get_master_sum_index(),
            ], 200);
    }

    public function widget(Request $request) {
        $builder = new DashboardDataBuilder($request);

        $res = $builder->build_widget($request);

        return response()->json([
                'widget' => $res
            ], 200);
    }

    public function widget_more(Request $request){
        $builder = new DashboardDataBuilder($request);

        $res = $builder->build_widget_more($request);

        return response()->json([
            'widget' => $res
        ], 200);
    }

    private function get_total_master_sum($builder){
        $total_master_sum = DashboardDataBuilder::UNDEFINED;
        if ($builder->get_master_sum_index() != null) {
            $total_master_sum = 0;
            foreach ($builder->build_entity('full') as $row) {
                $master_sum = $row[$builder->get_master_sum_index() + 1];
                if ($master_sum != DashboardDataBuilder::UNDEFINED) {
                    $total_master_sum += $master_sum;
                }
            }
        }
        return $total_master_sum;
    }
}
