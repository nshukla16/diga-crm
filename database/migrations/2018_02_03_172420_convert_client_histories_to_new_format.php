<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Client\Models\ClientHistory;


class ConvertClientHistoriesToNewFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $history = ClientHistory::whereIn('type_id', [3,4])->get();
        foreach($history as $entity){
            $needle = '<div class="modal-body">';
            $end_needle = '</div>	</div></div>';
            $needle_pos = strpos($entity->message, $needle)+strlen($needle);
            $entity->message = trim(substr($entity->message, $needle_pos, strrpos($entity->message, $end_needle) - $needle_pos));
            $entity->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $history = ClientHistory::whereIn('type_id', [3,4])->get();
        foreach($history as $entity){
            $entity->message = self::wrap_mail_text($entity->message, $entity->user->name);;
            $entity->save();
        }
    }

    private function wrap_mail_text($text, $username)
    {
        $id = uniqid();
        $tpl = '<a class="btn default green-stripe" data-toggle="modal" href="#' . $id . '">' . $username . ' escreveu e-mail</a>';
        $tpl .= '<div style="z-index: 10070 !important;" class="modal fade modal-fullscreen" id="' . $id . '" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">';
        $tpl .= '<div class="modal-content" >';
        $tpl .= '		<div class="modal-header">';
        $tpl .= '			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>';
        $tpl .= '			<h4 class="modal-title">' . $username . ' escreveu e-mail</h4>';
        $tpl .= '		</div>';
        $tpl .= '		<div class="modal-body">';
        $tpl .= $text;
        $tpl .= '		</div>';
        $tpl .= '	</div>';
        $tpl .= '</div>';
        return $tpl;
    }
}
