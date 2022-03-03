<?php

namespace Rkesa\Calendar\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Calendar\Http\Helpers\ChecklistPDFCreator;
use Rkesa\Calendar\Models\Checklist;
use Rkesa\Calendar\Models\ChecklistEntity;
use Rkesa\Calendar\Models\Event;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Log;
use UrlSigner;
use Auth;
use Exception;
use Rkesa\Calendar\Models\ChecklistArea;
use Rkesa\Calendar\Models\ChecklistWork;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\Resource;
use Rkesa\Service\Models\ServiceStateAction;

class ChecklistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'name');
            if ($sort == ''){ $sort = 'created_at'; }
            $order = $request->input('order', 'asc');
            if ($order == ''){ $order = 'asc'; }

            $checklists = Checklist::withCount('checklist_entities');

            $checklists->orderBy($sort, $order);

            $res->total = $checklists->count();
            $res->rows = $checklists->offset($offset)->limit($limit)->get();
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
        try{
            $checklist = new Checklist;
            $checklist->fill($request->all());
            $checklist->save();
            if ($request->filled('checklist_entities')) {
                foreach ($request['checklist_entities'] as $entity) {
                    $checklist_entity = new ChecklistEntity;
                    $checklist_entity->fill($entity);
                    $checklist_entity->checklist_id = $checklist->id;
                    $checklist_entity->save();
                }
            }
            if ($request->filled('checklist_works')) {
                foreach ($request['checklist_works'] as $work) {
                    $checklist_work = new ChecklistWork;
                    $checklist_work->fill($work);
                    $checklist_work->checklist_id = $checklist->id;
                    $checklist_work->save();
                }
            }
            if ($request->filled('checklist_areas')) {
                foreach ($request['checklist_areas'] as $area) {
                    $checklist_area = new ChecklistArea;
                    $checklist_area->fill($area);
                    $checklist_area->checklist_id = $checklist->id;
                    $checklist_area->save();
                }
            }
            $res->id = $checklist->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function get_checklist_pdf_link(Request $request, $id)
    {
        $user = Auth::user();
//        $estimate = Estimate::findOrFail($id);
//        if (!$user->can_with_estimate('read', $estimate)){
//            return response('forbidden', 403);
//        }
        return response()->json(['link' => UrlSigner::sign(env('APP_URL').'/api/checklists/pdf/'.$id, Carbon::now()->addHours(9))]);
    }

    public function show_checklist(Request $request, $id)
    {
        if (UrlSigner::validate(url()->full())){
            $event_id = $request->input('event_id', null);
            $event = Event::with(array('user', 'service', 'client_contact'))->find($event_id);
            $checklist = Checklist::with(['checklist_entities','checklist_works','checklist_areas'])->findOrFail($id);
            $service = $event ? $event->service : null;

            $creator = new ChecklistPDFCreator;
            $format = $request->input('format', 'pdf');
            switch ($format) {
                case 'html':
                    $result = $creator->render_html($event, $service, $checklist);
                    return Response($result);
                    break;
                case 'pdf':
                    $result =  $creator->render_pdf($event, $service, $checklist);
                    $headers = [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="doc.pdf"',
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

    public function show(Request $request, $id)
    {
        $checklist = Checklist::with(['checklist_entities','checklist_works','checklist_areas'])->findOrFail($id);

        return $checklist;
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
            $checklist = Checklist::find($id);
            $checklist->fill($request->all());
            $checklist->save();
            if ($request->filled('removed')) {
                foreach ($request['removed'] as $r_id) {
                    if ($r_id != 0) {
                        ChecklistEntity::find($r_id)->delete();
                    }
                }
            }
            if ($request->filled('checklist_entities')) {
                foreach ($request['checklist_entities'] as $entity) {
                    if ($entity['id'] == 0) {
                        $checklist_entity = new ChecklistEntity;
                    } else {
                        $checklist_entity = ChecklistEntity::find($entity['id']);
                    }
                    $checklist_entity->fill($entity);
                    $checklist_entity->checklist_id = $checklist->id;
                    $checklist_entity->save();
                }
            }

            if ($request->filled('removed_works')) {
                foreach ($request['removed_works'] as $r_id) {
                    if ($r_id != 0) {
                        ChecklistWork::find($r_id)->delete();
                    }
                }
            }
            if ($request->filled('checklist_works')) {
                foreach ($request['checklist_works'] as $work) {
                    if ($work['id'] == 0) {
                        $checklist_work = new ChecklistWork;
                    } else {
                        $checklist_work = ChecklistWork::find($work['id']);
                    }
                    $checklist_work->fill($work);
                    $checklist_work->checklist_id = $checklist->id;
                    $checklist_work->save();
                }
            }

            if ($request->filled('removed_areas')) {
                foreach ($request['removed_areas'] as $r_id) {
                    if ($r_id != 0) {
                        ChecklistArea::find($r_id)->delete();
                    }
                }
            }
            if ($request->filled('checklist_areas')) {
                foreach ($request['checklist_areas'] as $area) {
                    if ($area['id'] == 0) {
                        $checklist_area = new ChecklistArea;
                    } else {
                        $checklist_area = ChecklistArea::find($area['id']);
                    }
                    $checklist_area->fill($area);
                    $checklist_area->checklist_id = $checklist->id;
                    $checklist_area->save();
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        ServiceStateAction::where('email_include_checklist_id', $id)->update(['email_include_checklist_id' => 0]);
        Checklist::destroy($id);
        ChecklistEntity::where('checklist_id', $id)->delete();
    }

}
