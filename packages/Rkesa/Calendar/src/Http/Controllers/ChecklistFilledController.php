<?php

namespace Rkesa\Calendar\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use UrlSigner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Calendar\Http\Helpers\ChecklistPDFCreator;
use Rkesa\Calendar\Models\Checklist;
use Rkesa\Calendar\Models\ChecklistEntity;
use Log;
use Auth;
use Exception;
use Rkesa\Calendar\Models\ChecklistFilled;
use Rkesa\Calendar\Models\ChecklistFilledEntity;
use Rkesa\Service\Models\ServiceStateAction;


class ChecklistFilledController extends Controller
{

    public function index(Request $request)
    {
        //
    }

    public function create(Request $request)
    {
//        Checklist::find($request);
//        $checklist = array('id' => 0, 'name' => '', 'checklist_entities' => []);
//        return view('calendar::checklist_filled/create', ['checklist' => $checklist]);
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
            $checklist = ChecklistFilled::create(['checklist_id' => $request['id'], 'service_id' => $request['service_id']]);
            $checklist->note = $request->input('filled_description', '');
            $checklist->save();
            if ($request->filled('checklist_entities')) {
                foreach ($request['checklist_entities'] as $entity) {
                    $checklist_entity = new ChecklistFilledEntity;
                    $checklist_entity->checklist_filled_id = $checklist->id;
                    $checklist_entity->checklist_entity_id = $entity['id'];
                    if (array_key_exists('answer', $entity)){
                        $checklist_entity->text = $entity['answer'];
                    }
                    $checklist_entity->save();
                }
            }
            $checklist->load('checklist');
            $res->checklist = $checklist;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function get_fill_pdf_link(Request $request, $id)
    {
        $user = Auth::user();
//        $estimate = Estimate::findOrFail($id);
//        if (!$user->can_with_estimate('read', $estimate)){
//            return response('forbidden', 403);
//        }
        return response()->json(['link' => UrlSigner::sign(env('APP_URL').'/api/fills/pdf/'.$id, Carbon::now()->addHours(9))]);
    }

    public function show_fill(Request $request, $id)
    {
        if (UrlSigner::validate(url()->full())){
            $filled = ChecklistFilled::with('checklist', 'service')->findOrFail($id);
            $checklist = $filled->checklist;
            $event = null;
            $creator = new ChecklistPDFCreator;
            $format = $request->input('format', 'pdf');
            switch ($format) {
                case 'html':
                    $result = $creator->render_html($event, $filled->service, $checklist, $filled);
                    return Response($result);
                    break;
                case 'pdf':
                    $result =  $creator->render_pdf($event, $filled->service, $checklist, $filled);
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
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $checklist = Checklist::with('checklist_entities')->find($id);
        return view('calendar::checklist/edit', ['checklist' => $checklist]);
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
                    ChecklistEntity::find($r_id)->delete();
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
