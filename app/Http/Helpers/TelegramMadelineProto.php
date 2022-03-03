<?php

namespace App\Http\Helpers;

use Exception;
use Carbon\Carbon;
use App\GlobalSettings;
use App\ObjectStore;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;

class TelegramMadelineProto
{
    public function __construct()
    {
        require_once 'madeline.php';
    }

    public function send_code($tg_api_id, $tg_api_hash, $tg_phone)
    {
        $MadelineProto = new \danog\MadelineProto\API('session.madeline',
            [
                'app_info' => [
                    'api_id' => $tg_api_id,
                    'api_hash' => $tg_api_hash,
                ]
            ]
        );
        $MadelineProto->async(true);        
        $MadelineProto->loop(function () use ($MadelineProto, $tg_phone) {
            yield $MadelineProto->phoneLogin($tg_phone);
        });

        return 'ok';
    }

    public function enter_code($tg_api_id, $tg_api_hash, $tg_phone, $tg_code)
    {
        $MadelineProto = new \danog\MadelineProto\API('session.madeline');
        $MadelineProto->async(true);        
        $MadelineProto->loop(function () use ($MadelineProto, $tg_api_id, $tg_api_hash, $tg_phone, $tg_code) {            
            $authorization = yield $MadelineProto->completePhoneLogin($tg_code);
            return 'ok';
            
            if ($authorization['_'] === 'account.password') {
                return 'need_password';
            }
            if ($authorization['_'] === 'account.needSignup') {
                return 'need_registration';
            }
        });

        return 'ok';
    }

    public function create_chat($chat_name, $user_names)
    {
        try{
            $MadelineProto = new \danog\MadelineProto\API('session.madeline');
            $MadelineProto->async(true);        
            $MadelineProto->loop(function () use ($MadelineProto, $chat_name, $user_names) {
    
                $exists = false;
                $peer = null;
    
                $dialogs = yield $MadelineProto->getFullDialogs();
                foreach ($dialogs as $dialog) {
                    $full_info = yield $MadelineProto->getFullInfo($dialog['peer']);
    
                    if (array_key_exists ('Chat', $full_info)){
                        if (strpos($chat_name, $full_info['Chat']['title']) !== false){
                            $exists = true;
                            $peer = $dialog['peer'];
                        }
                    }
                }

                if ($peer != null){
                    $pwr_chat = yield $MadelineProto->getPwrChat($peer);
                    foreach($user_names as $user_name){
                        $user_exists = false;
                        foreach ($pwr_chat['participants'] as $participant) {
                            if ($participant['user']['id'] == $user_name){
                                $user_exists = true;
                            }
                        }
                        if ($user_exists == false){
                            yield $MadelineProto->messages->addChatUser(['chat_id' => $peer, 'user_id' => $user_name, 'fwd_limit' => 10, ]);
                        }
                    }
                }
                else{                    
                    $me = yield $MadelineProto->getSelf();
                    array_push($user_names, $me);  
                    yield $MadelineProto->messages->createChat(['users' => $user_names, 'title' => $chat_name, ]);
                }
            });
        } catch (\danog\MadelineProto\RPCErrorException $e) {
            Log::info($e);
            return $e;
        }

        return 'ok';
    }

    public function add_to_contacts($user_name, $first_name, $last_name, $phone){
        $MadelineProto = new \danog\MadelineProto\API('session.madeline');
        try {
            $MadelineProto->async(true);
            $tg_id = $MadelineProto->loop(function () use ($MadelineProto, $user_name, $first_name, $last_name, $phone) {  
                $contacts = yield $MadelineProto->contacts->getContacts();
                foreach($contacts['users'] as $user){
                    if (array_key_exists ('phone', $user) && (strpos($user['phone'], $phone) !== false || strpos($phone, $user['phone']) !== false)){
                        return $user['id'];
                    }
                    if (array_key_exists ('first_name', $user) && array_key_exists ('last_name', $user) && $user['first_name'] == $first_name && $user['last_name'] == $last_name){
                        return $user['id'];
                    }
                    if (array_key_exists ('username', $user)){
                        if ($user['username'] == $user_name){
                            return $user['id'];
                        }
                    }
                }

                if ($user_name == null){
                    yield $MadelineProto->contacts->addContact(['first_name' => $first_name, 'last_name' => $last_name, 'phone' => $phone, ]);
                }
                else{
                    $user_name = "@".$user_name;
                    yield $MadelineProto->contacts->addContact(['id' => $user_name, 'first_name' => $first_name, 'last_name' => $last_name, 'phone' => $phone, ]);
                }

                $contacts = yield $MadelineProto->contacts->getContacts();
                foreach($contacts['users'] as $user){
                    if (array_key_exists ('phone', $user) && strpos($user['phone'], $phone) !== false){
                        return $user['id'];
                    }
                    if (array_key_exists ('first_name', $user) && array_key_exists ('last_name', $user) && $user['first_name'] == $first_name && $user['last_name'] == $last_name){
                        return $user['id'];
                    }
                    if (array_key_exists ('username', $user)){
                        if ($user['username'] == $user_name){
                            return $user['id'];
                        }
                    }
                }
            });

            return $tg_id;
        } catch (\danog\MadelineProto\RPCErrorException $e) 
        {
            Log::info($e);
            return 0;
        }
    }
}
