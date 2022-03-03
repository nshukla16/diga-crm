<?php

namespace Rkesa\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User as User;
use Illuminate\Http\Request;
use Rkesa\Service\Models\Aru;

use Exception;
use Log;

class AruController extends Controller
{

    private function collectionSample() {
        $collection = (object)array();
        $collection->type = "FeatureCollection";
        $collection->features = array();
        return $collection;
    }

    private function featureSample() {
        $feature = (object)array();
        $feature->type = "Feature";
        $feature->properties = (object)array();
        $feature->geometry = (object)array();
        $feature->geometry->type = "Polygon";
        $feature->geometry->coordinates = array(array());
        return $feature;
    }

    private function build($aru) {
        $collection = $this->collectionSample();
        
        foreach($aru as $region) {
            $sample = $this->featureSample();

            $sample->properties->id = $region->id;
            $sample->properties->name = $region->municipal;
            $sample->properties->description = $region->zone ;
            $sample->properties->discount =  $region->discount;
            $sample->properties->overlap =  $region->overlap;

            foreach($region->coords as $key => $point) {
                array_push($sample->geometry->coordinates[0], array($point->lat, $point->lng));
            }
            // then push first point to end of array (google requires).
            array_push($sample->geometry->coordinates[0], array($region->coords[0]->lat, $region->coords[0]->lng));

            array_push($collection->features, $sample);
        }

        return $collection;
    }


    public function ARUzones(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            // ::remember(60)
            $res->aru = $this->build(Aru::with(['coords'])->get());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

}
