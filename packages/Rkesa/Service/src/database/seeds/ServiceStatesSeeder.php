<?php

namespace Rkesa\Service\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceStatesSeeder extends Seeder {

	public function run($lang = 'ru')
    {
        // IF YOU CHANGE SERVICE STATES, YOU NEED TO CHANGE DASHBOARD ENTITIES ALSO
        // AND SERVICE SCOPES SEEDER
        switch ($lang){
            case 'pt':
                DB::table('service_states')->insert(array(
                    array('id' => '1',  'name' => 'Inicial',			'order' => 1,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-dot-circle-o',     'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '2',  'name' => 'Spam',				'order' => 2,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-ban',             'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '3',  'name' => 'RH',					'order' => 3,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-users',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '4',  'name' => 'Outro', 				'order' => 4, 	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-question-circle', 'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '5',  'name' => 'Pedido Recebido',	'order' => 5,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-check',            'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '21', 'name' => 'Marcar visita',		'order' => 6,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-check',            'type' => 1, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 6),
                    array('id' => '6',  'name' => 'Visita marcada',	    'order' => 7,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-car',              'type' => 0, 'can_click' => 0, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '7',  'name' => 'Orçamentação',	    'order' => 8,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-file-o',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '22', 'name' => 'Enviar Orçamento',	'order' => 9,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-file-o',           'type' => 1, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 8),
                    array('id' => '8',  'name' => 'Orçamento enviado',	'order' => 10,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-paper-plane',      'type' => 0, 'can_click' => 0, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '9',  'name' => 'Em negociação', 		'order' => 11, 	'horizontal' => true, 'color' => '#f1c40f', 'icon' => 'fa-comments-o',       'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '10', 'name' => 'Pre Adjudicado',		'order' => 12,	'horizontal' => true, 'color' => '#f1c40f', 'icon' => 'fa-refresh',          'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '11', 'name' => 'Falta de verbas',	'order' => 13,	'horizontal' => false, 'color' => '#7d7d7d', 'icon' => 'fa-money',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '12', 'name' => 'Recusado',			'order' => 14,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-remove',          'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '13', 'name' => 'Despejado',			'order' => 15,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-trash-o',         'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '14', 'name' => 'Adjudicado',			'order' => 16,	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-handshake-o',      'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '15', 'name' => 'Por marcar inicio',	'order' => 17,	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-calendar-o',       'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '16', 'name' => 'Inicio marcado', 	'order' => 18, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-calendar-check-o', 'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '17', 'name' => 'Em processo', 		'order' => 19, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-wrench',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '18', 'name' => 'Finalizado', 		'order' => 20, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-thumbs-o-up',      'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '19', 'name' => 'Em crédito', 		'order' => 21, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-euro',             'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '20', 'name' => 'Concluido', 			'order' => 22, 	'horizontal' => true, 'color' => '#000000', 'icon' => 'fa-trophy',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                ));
                DB::table('service_state_actions')->insert(array(
                    array('id' => '1',  'order' => 1, 'service_state_id' => 21, 'type' => 3, 'event_type_id' => 2,
                        'email_type' => 1,
                        'email_subject' => null,
                        'email_text' => null,
                        'email_include_estimate_type' => 0,
                        'email_show' => 0,
                        ),
                    array('id' => '2',  'order' => 1, 'service_state_id' => 22, 'type' => 1, 'event_type_id' => 0,
                        'email_type' => 1,
                        'email_subject' => 'Orçamento(s) {selected_estimate_numbers}, {service_address}',
                        'email_text' => 'Exmo(s) Senhor(es),<br/><br/><br/>
                                        Junto em anexo envio todos os documentos necessários e orçamento para os trabalhos a realizar na(o)/em {service_address}<br/><br/>
                                        Qualquer dúvida ou esclarecimento adicional não hesite em contactar-nos.<br/><br/>
                                        Esperamos que seja do vosso agrado, aguardando a vossa resposta.<br/><br/>
                                        <span style=\'color: rgb(230,145,56);\'>Agradecemos a confirmação da recepção do respectivo orçamento.</span><br/><br/><br/><br/>',
                        'email_include_estimate_type' => 2,
                        'email_show' => 1
                        ),
                ));
                break;
            case 'ru':
                DB::table('service_states')->insert(array(
                    array('id' => '1',  'name' => 'Начало',	    		'order' => 1,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-dot-circle-o',     'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '2',  'name' => 'Спам',				'order' => 2,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-ban',             'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '3',  'name' => 'HR',					'order' => 3,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-users',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '4',  'name' => 'Другой', 			'order' => 4, 	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-question-circle', 'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '5',  'name' => 'Заказ получен',      'order' => 5,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-check',            'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '21', 'name' => 'Запланировать визит','order' => 6,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-check',            'type' => 1, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 6),
                    array('id' => '6',  'name' => 'Визит запланирован', 'order' => 7,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-car',              'type' => 0, 'can_click' => 0, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '7',  'name' => 'Составление сметы',	'order' => 8,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-file-o',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '22', 'name' => 'Отправить смету',	'order' => 9,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-file-o',           'type' => 1, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 8),
                    array('id' => '8',  'name' => 'Смета отправлена',	'order' => 10,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-paper-plane',      'type' => 0, 'can_click' => 0, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '9',  'name' => 'Переговоры', 		'order' => 11, 	'horizontal' => true, 'color' => '#f1c40f', 'icon' => 'fa-comments-o',       'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '10', 'name' => 'Предварительно утвержден','order' => 12,	'horizontal' => true, 'color' => '#f1c40f', 'icon' => 'fa-refresh',          'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '11', 'name' => 'Недостаточно средств','order' => 13,	'horizontal' => false, 'color' => '#7d7d7d', 'icon' => 'fa-money',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '12', 'name' => 'Отказ',			'order' => 14,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-remove',          'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '13', 'name' => 'Заказ сброшен',		'order' => 15,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-trash-o',         'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '14', 'name' => 'Одобрен',			'order' => 16,	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-handshake-o',      'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '15', 'name' => 'Запланировать начало работ',	'order' => 17,	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-calendar-o',       'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '16', 'name' => 'Начало запланировано','order' => 18, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-calendar-check-o', 'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '17', 'name' => 'В процессе', 		'order' => 19, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-wrench',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '18', 'name' => 'Завершение', 		'order' => 20, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-thumbs-o-up',      'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '19', 'name' => 'Оплата работы', 		'order' => 21, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-euro',             'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '20', 'name' => 'Завершен', 			'order' => 22, 	'horizontal' => true, 'color' => '#000000', 'icon' => 'fa-trophy',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                ));
                DB::table('service_state_actions')->insert(array(
                    array('id' => '1',  'order' => 1, 'service_state_id' => 21, 'type' => 3, 'event_type_id' => 2,
                        'email_type' => 1,
                        'email_subject' => null,
                        'email_text' => null,
                        'email_include_estimate_type' => 0,
                        'email_show' => 0,
                    ),
                    array('id' => '2',  'order' => 1, 'service_state_id' => 22, 'type' => 1, 'event_type_id' => 0,
                        'email_type' => 1,
                        'email_subject' => 'Смета(ы) {selected_estimate_numbers}, {service_address}',
                        'email_text' => 'Уважаемый клиент,<br/><br/><br/>
                                        Отправляю все необходимые документы и бюджет работы, которая должна быть выполнена по адресу {service_address}<br/><br/>
                                        Если у вас есть какие-либо дополнительные вопросы или проблемы, пожалуйста, обратитесь к нам.<br/><br/>
                                        Надеемся, вам понравится. Ожидаем вашего ответа.<br/><br/>
                                        <span style=\'color: rgb(230,145,56);\'>Спасибо, что подтвердили получение вашего бюджета.</span><br/><br/><br/><br/>',
                        'email_include_estimate_type' => 2,
                        'email_show' => 1
                    ),
                ));
                break;
            case 'en':
                DB::table('service_states')->insert(array(
                    array('id' => '1',  'name' => 'Initial',	    	'order' => 1,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-dot-circle-o',     'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '2',  'name' => 'Spam',				'order' => 2,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-ban',             'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '3',  'name' => 'HR',					'order' => 3,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-users',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '4',  'name' => 'Other', 			    'order' => 4, 	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-question-circle', 'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '5',  'name' => 'Order received',     'order' => 5,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-check',            'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '21', 'name' => 'Schedule a visit',   'order' => 6,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-check',            'type' => 1, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 6),
                    array('id' => '6',  'name' => 'The visit is planned','order' => 7,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-car',              'type' => 0, 'can_click' => 0, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '7',  'name' => 'Budgeting',	        'order' => 8,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-file-o',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '22', 'name' => 'Send a budget',	    'order' => 9,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-file-o',           'type' => 1, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 8),
                    array('id' => '8',  'name' => 'Budget sent',	    'order' => 10,	'horizontal' => true, 'color' => '#659be0', 'icon' => 'fa-paper-plane',      'type' => 0, 'can_click' => 0, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '9',  'name' => 'Conversation', 		'order' => 11, 	'horizontal' => true, 'color' => '#f1c40f', 'icon' => 'fa-comments-o',       'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '10', 'name' => 'Preapproved',        'order' => 12,	'horizontal' => true, 'color' => '#f1c40f', 'icon' => 'fa-refresh',          'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '11', 'name' => 'Insufficient funds', 'order' => 13,	'horizontal' => false, 'color' => '#7d7d7d', 'icon' => 'fa-money',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '12', 'name' => 'Refused',			'order' => 14,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-remove',          'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '13', 'name' => 'Order cancelled',	'order' => 15,	'horizontal' => false, 'color' => '#bf0000', 'icon' => 'fa-trash-o',         'type' => 0, 'can_click' => 1, 'with_reason' => 1, 'destination_state_id' => 0),
                    array('id' => '14', 'name' => 'Approved',			'order' => 16,	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-handshake-o',      'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '15', 'name' => 'Schedule start of work',	'order' => 17,	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-calendar-o',       'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '16', 'name' => 'Start scheduled','order' => 18, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-calendar-check-o', 'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '17', 'name' => 'In progress', 		'order' => 19, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-wrench',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '18', 'name' => 'Completion', 		'order' => 20, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-thumbs-o-up',      'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '19', 'name' => 'Payment of work', 	'order' => 21, 	'horizontal' => true, 'color' => '#338e07', 'icon' => 'fa-euro',             'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                    array('id' => '20', 'name' => 'Finished', 			'order' => 22, 	'horizontal' => true, 'color' => '#000000', 'icon' => 'fa-trophy',           'type' => 0, 'can_click' => 1, 'with_reason' => 0, 'destination_state_id' => 0),
                ));
                DB::table('service_state_actions')->insert(array(
                    array('id' => '1',  'order' => 1, 'service_state_id' => 21, 'type' => 3, 'event_type_id' => 2,
                        'email_type' => 1,
                        'email_subject' => null,
                        'email_text' => null,
                        'email_include_estimate_type' => 0,
                        'email_show' => 0,
                    ),
                    array('id' => '2',  'order' => 1, 'service_state_id' => 22, 'type' => 1, 'event_type_id' => 0,
                        'email_type' => 1,
                        'email_subject' => 'Budget(s) {selected_estimate_numbers}, {service_address}',
                        'email_text' => 'Dear Customer,<br/><br/><br/>
                                        I send all necessary documents and work budget, which should be done at {service_address}<br/><br/>
                                        If you have any further questions or problems, please feel free to contact us.<br/><br/>
                                        Hope you enjoy. We await your reply.<br/><br/>
                                        <span style=\'color: rgb(230,145,56);\'>Thank you for confirming your budget.</span><br/><br/><br/><br/>',
                        'email_include_estimate_type' => 2,
                        'email_show' => 1
                    ),
                ));
                break;
        }
	}
}