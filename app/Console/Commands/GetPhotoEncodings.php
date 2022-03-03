<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use App\Http\Helpers\PhotoEncoder;
use Rkesa\Hr\Http\Controllers\HrController;

class GetPhotoEncodings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:get_photo_encodings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::all();
        foreach($users as $user){
            $ph_helper = new PhotoEncoder;
            $user->photo_encodings = $ph_helper->get_photo_encodings($user->photo);
            $user->save();
        }
        $this->info('Done');
    }
}
