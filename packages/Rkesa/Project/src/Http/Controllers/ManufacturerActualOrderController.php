<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;
use Auth;
use Exception;
use Rkesa\Project\Http\Helpers\NotificationsAndAutotasksHelper;
use Rkesa\Project\Models\CustomsDocument;
use Rkesa\Project\Models\ManufacturerActualOrder;
use Rkesa\Project\Models\ManufacturerOrder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Rkesa\Project\Models\ManufacturerOrderManager;
use Rkesa\Project\Models\ManufacturerOrderPlace;
use Rkesa\Project\Models\ProjectCarrier;

class ManufacturerActualOrderController extends Controller
{

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $m_o = new ManufacturerActualOrder;
            $m_o->fill($request->all());
            $m_o->save();

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
            $m_o = ManufacturerActualOrder::find($id);
            $m_o->fill($request->all());
            $m_o->save();
        }
        catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public function destroy(Request $request, $id)
    {
        ManufacturerActualOrder::find($id)->delete();
    }

}
