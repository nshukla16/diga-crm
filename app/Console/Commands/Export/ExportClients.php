<?php

namespace App\Console\Commands\Export;

use Illuminate\Console\Command;
use Rkesa\Client\Models\ClientContact;
use Log;

class ExportClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:export';

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
        $clients = ClientContact::with('client_contact_phones', 'client_contact_emails')->get();

        require_once base_path().'/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
        $fileName = 'clients';

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
        ;

        foreach($clients as $key => $client){
            $ii = $key+2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$ii, $client->surname . ' ' . $client->name);

            $emails = join(',', array_map(function($e){
                return $e['email'];
            }, $client->client_contact_emails->toArray()));

            $objPHPExcel->getActiveSheet()->setCellValue('B'.$ii, $emails);

            $phones = join(',', array_map(function($e){
                return $e['phone_number'];
            }, $client->client_contact_phones->toArray()));

            $objPHPExcel->getActiveSheet()->setCellValue('C'.$ii, $phones);
        }

        // Set worksheet title
        $objPHPExcel->getActiveSheet()->setTitle($fileName);

        //save the file to the server (Excel2007)
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($fileName . '.xlsx');

        $this->info('Done');
    }
}
