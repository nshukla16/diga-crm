<?php
namespace App\Http\Traits;

use Exception;
use Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

trait MailTrait {
    public static function format_email($text)
    {
        $text = str_replace('&quot;', "'", $text);
        $text = str_replace('&#10;', "", $text);
        return $text;
    }

    public static function send_mail($data)
    {
        $host = env('MAIL_HOST', '');
        $port = env('MAIL_PORT', '');
        $username = env('MAIL_USERNAME', '');
        $password = env('MAIL_PASSWORD', '');
        $encryption = env('MAIL_ENCRYPTION', '');
        if ($username != null && $password != null) {
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPDebug = false;  // debugging: 1 = errors and messages, 2 = messages only
            $mail->Debugoutput = function($str, $level){
                Log::error($level.": ".$str);
            };
            $mail->SMTPAuth = true;
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPSecure = $encryption;
            // dont verify
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            //
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SetFrom($username);
            $mail->Subject = $data['subject'];
            $mail->Body = $data['body'] == '' ? ' ' : $data['body'];
            if (is_array($data['to'])) {
                foreach ($data['to'] as $address) {
                    $mail->AddAddress($address);
                }
            } else {
                $mail->AddAddress($data['to']);
            }
            $mail->CharSet = 'UTF-8';
            $mail->IsHTML(true);
            if (!$mail->Send()) {
                Log::error($mail->ErrorInfo);
                return false;
            } else {
                return true;
            }
        }else{
            return false;
        }
    }
}
