<?php

namespace App\Http\Controllers;

use App\GlobalSettings;
use App\Http\Traits\SaasAuthTrait;
use App\Jobs\UploadFileToGoogleDrive;
use Illuminate\Http\Request;
use Auth;
use Debugbar;
use Imtigger\LaravelJobStatus\JobStatus;
use Log;
use Storage;
use Exception;

class GoogleDriveController extends Controller
{
    use SaasAuthTrait;

    public function auth()
    {
        $response_decoded = self::get_access_token();

        return ['url' => 'https://saas.diga.pt/integrations/google?access_token='.$response_decoded['access_token'].'&mode=drive'];
    }

    public static function done()
    {
        $gs = GlobalSettings::first();
        $gs->gd_enabled = true;
        $gs->save();
    }

    // Upload the file (with given "src" path) on the server to google drive
    public function upload(Request $request)
    {
        $gs = GlobalSettings::first();
        if ($gs->gd_enabled) {
            $user = Auth::user();

            $response_decoded = self::get_access_token();

            $guzzle = new \GuzzleHttp\Client;

            $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/integrations/google_drive/upload', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $response_decoded['access_token'],
                ],
                'multipart' => [
                    [
                        'name' => 'user_id',
                        'contents' => $user->id
                    ],
                    [
                        'name' => 'doc_file',
                        'contents' => fopen(public_path($request['src']), 'r'),
                        'filename' => $request['filename'] . '.' . pathinfo($request['src'], PATHINFO_EXTENSION)
                    ],
                ]
            ]);

            $response_decoded = json_decode((string)$response->getBody(), true);

//            if everything is ok then remove local file

            return response()->json($response_decoded);
        }else{
            throw new Exception('Google Drive is not connected');
        }
    }

    // Start a job and return job_id
    // Local file is supposed to be removed in job
    // Custom max_post_size is defined in nginx location
    public function upload_with_job(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $file = $request->file('files');
            $ext = $file->getClientOriginalExtension();
            if (array_search($ext, HomeController::NOT_ALLOWED_EXTENTIONS) !== false) {
                throw new Exception('Not allowed file extention');
            }

            $path = $file->store('google_drive');

            $gs = GlobalSettings::first();
            if ($gs->gd_enabled) {
                $user = Auth::user();

                $filename = $file->getClientOriginalName();

                $job = new UploadFileToGoogleDrive($user->id, $path, $filename);
                $this->dispatch($job);
                $res->job_id = $job->getJobStatusId();
            }else{
                throw new Exception('Google Drive is not connected');
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function job_status(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $jobStatus = JobStatus::find($id);

            if ($jobStatus->status == 'failed'){
                throw new Exception('Job failed');
            } else {
                $res->progress = $jobStatus->progress_now;
                if ($jobStatus->progress_now == 100){
                    $res->url = $jobStatus->output['url'];
                    $res->name = $jobStatus->output['name'];
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
}
