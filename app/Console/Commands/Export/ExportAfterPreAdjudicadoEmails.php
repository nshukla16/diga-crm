<?php

namespace App\Console\Commands\Export;

use Illuminate\Console\Command;
use Rkesa\Client\Models\ClientContact;
use Log;
use Rkesa\Service\Models\Service;

class ExportAfterPreAdjudicadoEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:export_after_pa';

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
        $services = Service::where('service_state_id', '>=', 10)->get();

        require_once base_path().'/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
        $fileName = 'clients';

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Me")->setLastModifiedBy("Me")->setTitle("My Excel Sheet")->setSubject("My Excel Sheet")->setDescription("Excel Sheet")->setKeywords("Excel Sheet")->setCategory("Me");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Add column headers
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Email');

        $emails = [];
        foreach($services as $service){
            if ($service->client && $service->client->main_contact()['email'] && !in_array($service->client->main_contact()['email'], $emails)) {
                array_push($emails, $service->client->main_contact()['email']);
            }
        }

        foreach($emails as $key => $email){
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($key+2), $email);
        }

        // Set worksheet title
        $objPHPExcel->getActiveSheet()->setTitle($fileName);

        //save the file to the server (Excel2007)
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($fileName . '.xlsx');

        $this->info('Done');
    }
}
