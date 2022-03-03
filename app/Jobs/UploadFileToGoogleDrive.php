<?php

namespace App\Jobs;

use App\Http\Traits\SaasAuthTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imtigger\LaravelJobStatus\Trackable;

class UploadFileToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;
    use SaasAuthTrait;

    protected $user_id;
    protected $file_path;
    protected $file_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $file_path, $file_name)
    {
        $this->prepareStatus();
        $this->user_id = $user_id;
        $this->file_path = $file_path;
        $this->file_name = $file_name;
        $this->setInput([$user_id, $file_path, $file_name]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->setProgressMax(100);

        $guzzle = new \GuzzleHttp\Client;

        $access = self::get_access_token();

        $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/integrations/google_drive/upload', [
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access['access_token'],
            ],
            'multipart' => [
                [
                    'name' => 'user_id',
                    'contents' => $this->user_id
                ],
                [
                    'name' => 'doc_file',
                    'contents' => file_get_contents(storage_path('app/'.$this->file_path)),
                    'filename' => $this->file_name
                ],
            ],
            'progress' => function($downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes){
                if ($uploadTotal != 0) {
                    $this->setProgressNow((int)($uploadedBytes / $uploadTotal * 100));
                }
            }
        ]);

        $response_decoded = json_decode((string)$response->getBody(), true);

        // if everything is ok then remove local file

        Storage::delete($this->file_path);

        $this->setOutput(['url' => $response_decoded['link'], 'name' => $this->file_name]);
    }

    public function failed($e)
    {
        Log::channel('daily')->error($e);
        app('sentry')->captureException($e);
    }
}
