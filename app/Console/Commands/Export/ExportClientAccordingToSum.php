<?php

namespace App\Console\Commands\Export;

use Illuminate\Console\Command;
use Rkesa\Client\Models\ClientContact;
use Log;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Service\Models\Service;

class ExportClientAccordingToSum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:export_sum';

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
        $services = Service::with('client')->where('estimate_summ', '>=', 30000)->whereNotIn('service_state_id', [2,14,15,16,17,18,19,20,12,13])->get();

        require_once base_path().'/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
        $fileName = 'clients_30000';

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Me")->setLastModifiedBy("Me")->setTitle("My Excel Sheet")->setSubject("My Excel Sheet")->setDescription("Excel Sheet")->setKeywords("Excel Sheet")->setCategory("Me");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Add column headers
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A1', 'Name')
            ->setCellValue('B1', 'Email')
            ->setCellValue('C1', 'Phone')
            ->setCellValue('D1', 'Estimate')
            ->setCellValue('E1', 'Sum')
        ;

        foreach($services as $key => $service){
            $ii = $key+2;
            $contact = $service->client->main_contact();
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$ii, $contact->surname . ' ' . $contact->name);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$ii, $contact->email);

            $phones = join(',', array_map(function($e){
                return $e['phone_number'];
            }, $contact->client_contact_phones->toArray()));

            $objPHPExcel->getActiveSheet()->setCellValue('C'.$ii, $phones);
            $estimate = Estimate::with('service')->find($service->master_estimate_id);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$ii, $estimate ? $estimate->get_estimate_number() : $service->get_service_number());
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$ii, $service->estimate_summ);
        }

        // Set worksheet title
        $objPHPExcel->getActiveSheet()->setTitle($fileName);

        //save the file to the server (Excel2007)
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($fileName . '.xlsx');

        $this->info('Done');
    }

}
