<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;
use PhpOffice\PhpWord\TemplateProcessor;
use Rkesa\Project\Models\ContractPayment;
use Carbon\Carbon;
use Auth;
use App;
use Rkesa\Project\Models\LegalEntity;
use Rkesa\Project\Models\Project;
use LocalizedCarbon;

class TemplateController extends Controller
{

    public function payment_invoice(Request $request)
    {

        $project_id = $request->input('project_id');
        $project = Project::find($project_id);
        $legal_entity = $project->seller_legal_entity;
        if ($project->contract_type == 1){
            $legal_entity = $project->manufacturers->first()->commission_relations->first()->legal_entity;
        }

        $con_num = $project->contract_number;
        $contract_date = $project->date_of_sign_contract;
        $contract_price = floatval($request->input('val'));
        $buyer_name = $project->client->name;
        if ($project->lessee_client) {
            $final_payer = $project->lessee_client->name;
        }else{
            $final_payer = $buyer_name;
        }
        $name = $legal_entity->name;
        $adrs = $legal_entity->address;
        $bic = $legal_entity->bic;
        $tax = $legal_entity->tax_number;
        $kpp = $legal_entity->kpp_number;
        $bank = $legal_entity->bank_name;
        $bank_acc = $legal_entity->bank_account_number;
        $bank_rec = $legal_entity->bank_receiver_number;
        $sign_link = $legal_entity->sign_file_path;
        if (App::getLocale() == 'ru') {
            $today = LocalizedCarbon::now()->formatLocalized('%d %f %Y');
            $contr_date = LocalizedCarbon::parse($contract_date)->formatLocalized('«%d» %f %Y');
        }else{
            $today = Carbon::now()->formatLocalized('%d %B %Y');
            $contr_date = Carbon::parse($contract_date)->formatLocalized('«%d» %B %Y');
        }
        $tax_rate = $legal_entity->tax_rate;
        $tax_part = '';
        if($legal_entity->tax_enabled == true){
            $tax_part = round($contract_price * $tax_rate, 4);
        } else {
            $tax_part = '0,00';
        }

        // Document:
        $languageEnGb = new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::EN_GB);
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->getSettings()->setThemeFontLang($languageEnGb);
        // Styles
        $bold = 'rStyle';
        $phpWord->addFontStyle($bold, array('bold' => true));
        $p = 'pStyle';
        $phpWord->addParagraphStyle($p, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $phpWord->addTitleStyle(1, array('bold' => true, 'underline' => 'single'), array('spaceAfter' => 240));

        // Document beginning
        $section = $phpWord->addSection();
        $section->addTitle($name, 1);
        $section->addText($adrs, $bold);
        $section->addTextBreak(1);
        $section->addText('Образец заполнения платежного поручения', ['bold' => true], $p);
        $section->addTextBreak(1);
        $styleTable = array('borderSize' => 6, 'borderColor' => '999999');
        $phpWord->addTableStyle('Colspan Rowspan', $styleTable);
        $table = $section->addTable('Colspan Rowspan');
        $row = $table->addRow();
        $row->addCell(1000, array('gridSpan' => 2, 'vMerge' => 'restart'))->addText('ИНН ' . $tax);
        $row->addCell(1000, array('gridSpan' => 2, 'vMerge' => 'restart'))->addText('КПП ' . $kpp);
        $row->addCell(1000, array('vMerge' => 'restart', 'valign'=>'bottom'))->addText('Сч. №');
        $row->addCell(1000, array('vMerge' => 'restart', 'valign'=>'bottom'))->addText($bank_rec);
        $row = $table->addRow();
        $row->addCell(1000, array('gridSpan' => 4, 'vMerge' => 'restart'))->addText('Получатель ' . $name);
        $row->addCell(1000, array('vMerge' => 'continue', 'cellMargin' => 'Bottom'));
        $row->addCell(1000, array('vMerge' => 'continue'));
        $row = $table->addRow();
        $row->addCell(1000, array('gridSpan' => 4, 'vMerge' => 'restart'))->addText('Банк получателя ' . $bank);
        $row->addCell(1000)->addText('БИК');
        $row->addCell(1000, array('vMerge' => 'restart'))->addText($bic);
        $row = $table->addRow();
        $row->addCell(1000, array('gridSpan' => 4, 'vMerge' => 'continue'));
        $row->addCell(1000)->addText('Сч. №');
        $row->addCell(1000)->addText($bank_acc);
        $section->addTextBreak(1);
        $section->addText('СЧЕТ № ________ от ' . $today, ['bold' => true, 'size' => 14], $p);
        $section->addTextBreak(1);
        $section->addText('Плательщик: ' . $final_payer);
        $section->addText('Грузополучатель: ' . $buyer_name);
        $section->addTextBreak(1);
        $styleTable = array('borderSize' => 10, 'borderColor' => '000000');
        $phpWord->addTableStyle('Colspan Rowspan', $styleTable);
        $table = $section->addTable('Colspan Rowspan');
        $styleCellB = array('valign'=>'center', 'borderColor'=>'ffffff');
        $mid_row = array('valign'=>'center');
        $bottom_row = array(
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 9,
            'borderBottomColor' =>'ffffff',
            'borderBottomSize' => 9,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 9,);
        $last_left = array(
            'borderTopColor' =>'000000',
            'borderTopSize' => 12,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 12,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 12,
        );
        $last_right = array(
            'borderTopColor' =>'000000',
            'borderTopSize' => 12,
            'borderRightColor' =>'000000',
            'borderRightSize' => 12,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 12,
        );
        $last = array(
            'borderBottomColor' =>'ffffff',
            'borderBottomSize' => 9,
        );
        $row = $table->addRow();
        $row->addCell(500)->addText('№');
        $row->addCell(3000, $styleCellB)->addText('Наименование товара');
        $row->addCell(1500)->addText('Единица измерения');
        $row->addCell(1500)->addText('Количество');
        $row->addCell(1500)->addText('Цена с НДС, у.е.');
        $row->addCell(1500)->addText('Сумма с НДС, у.е.');
        $row = $table->addRow();
        $row->addCell(500)->addText('1');
        $row->addCell(3000, $mid_row)->addText( 'Оплата за оборудование по Договору ' . $con_num . ' от ' . $contr_date);
        $row->addCell(1500, $mid_row)->addText('шт.');
        $row->addCell(1500, $mid_row)->addText('1');
        $row->addCell(1500, $mid_row)->addText($contract_price);
        $row->addCell(1500, $mid_row)->addText($contract_price);
        $row = $table->addRow();
        $row->addCell(null, $bottom_row);
        $row->addCell(null, $bottom_row);
        $row->addCell(null, $bottom_row);
        $row->addCell(null, $bottom_row);
        $row->addCell(1500, $last_left)->addText('Итого:');
        $row->addCell(1500, $last_right)->addText($contract_price);
        $row = $table->addRow();
        $row->addCell(null, $bottom_row);
        $row->addCell(null, $bottom_row);
        $row->addCell(null, $bottom_row);
        $row->addCell(null, $bottom_row);
        $row->addCell(1500, $last)->addText('В т.ч. НДС:');
        $row->addCell(1500)->addText($tax_part);
        $section->addTextBreak(1);
        $section->addText('Всего наименований 1, на сумму                     у.е.', array('bold' => true, 'size' => 8));
        $section->addText('(_______________00/100) условных единиц', array('bold' => true, 'size' => 8));
        $section->addText('ОПЛАТА В РУБЛЯХ ПО КУРСУ Доллара США ЦБ РФ НА ДЕНЬ ОПЛАТЫ.', array('color' => 'FF0000', 'bold' => true, 'size' => 8));
        $section->addTextBreak(1);
        $section->addText('Просьба в платежном поручении указывать дату и номер договора.', array('bold' => true), $p);
        $section->addTextBreak(1);
        $section->addText('(Оплата по Договору № _______ от ' . $con_num . ' ' . $contr_date . ' г.)', array('bold' => true), $p);
        $section->addTextBreak(1.5);
        $section->addText('Оплатить счет следует до ____________', array('bold' => true), $p);
        $section->addTextBreak(1);
        if ($sign_link) {
            $source = public_path($sign_link);
            $fileContent = file_get_contents($source);
            $section->addImage($fileContent, array(
                'width' => 194,
                'height' => 101,
            ));
        }
        // END of the page
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('helloWorld.docx');
        return response()->file('helloWorld.docx')->deleteFileAfterSend(true);
    }

    public function template(Request $request)
    {
        $project_id = $request->input('id');
        $project = Project::with('specifications.equipment', 'client')->find($project_id);
        $legal_entity = $project->seller_legal_entity;
        if ($project->contract_type == 1){
            $legal_entity = $project->manufacturers->first()->commission_relations->first()->legal_entity;
        }

        $contract_date = $project->date_of_sign_contract;

        if (App::getLocale() == 'ru') {
            $current_month_w_p = LocalizedCarbon::now()->formatLocalized('%f');
            $contract_month_w_p = LocalizedCarbon::parse($contract_date)->formatLocalized('%f');
        }else{
            $current_month_w_p = Carbon::now()->formatLocalized('%B');
            $contract_month_w_p = Carbon::parse($contract_date)->formatLocalized('%B');
        }

        $current_day_n = Carbon::now()->formatLocalized('%d');
        $current_month_n = Carbon::now()->formatLocalized('%m');
        $current_year_n = Carbon::now()->formatLocalized('%Y');
        $contract_day_n = Carbon::parse($contract_date)->formatLocalized('%d');
        $contract_year_n = Carbon::parse($contract_date)->formatLocalized('%Y');

        $array_of_arrays = array_map(function($specification){
            return array_map(function($equipment){
                return $equipment['name'];
            }, $specification['equipment']);
        }, $project->specifications->toArray());
        if (count($array_of_arrays) > 0) {
            $all_equipment = join(', ', call_user_func_array('array_merge', $array_of_arrays)); // flatten and join array
        }else{
            $all_equipment = '';
        }

        $type = $request->input('type', 'ready_notification');
        $templateProcessor = new TemplateProcessor(public_path($legal_entity->toArray()[$type.'_template_file_path']));

        $templateProcessor->setValue('contract-number', $project->contract_number);
        $templateProcessor->setValue('current-day-n', $current_day_n);
        $templateProcessor->setValue('current-month-n', $current_month_n);
        $templateProcessor->setValue('current-month-w-p', $current_month_w_p);
        $templateProcessor->setValue('current-year-n', $current_year_n);
        $templateProcessor->setValue('contract-day-n', $contract_day_n);
        $templateProcessor->setValue('contract-month-w-p', $contract_month_w_p);
        $templateProcessor->setValue('contract-year-n', $contract_year_n);
        $templateProcessor->setValue('buyer-company', $project->client->name);
        $templateProcessor->setValue('equipment-name', $all_equipment);

        $tmpFileName = $templateProcessor->save();
        return response()->file($tmpFileName)->deleteFileAfterSend(true);
    }
}
