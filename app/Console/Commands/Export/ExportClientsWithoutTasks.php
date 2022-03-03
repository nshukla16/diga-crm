<?php

namespace App\Console\Commands\Export;

use Illuminate\Console\Command;
use Rkesa\Client\Models\ClientContact;
use Log;

class ExportClientsWithoutTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:export_without_tasks';

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
        $clients = ClientContact::with('active_events')->get();

        require_once base_path().'/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
        $fileName = 'clients_without_tasks';

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Me")->setLastModifiedBy("Me")->setTitle("My Excel Sheet")->setSubject("My Excel Sheet")->setDescription("Excel Sheet")->setKeywords("Excel Sheet")->setCategory("Me");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Add column headers
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A1', 'Name')
            ->setCellValue('B1', 'Link')
        ;

        $ii = 1;
        foreach($clients as $key => $client){
            if ($client->active_events->count() == 0) {
                $ii += 1;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $ii, $client->surname . ' ' . $client->name);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $ii, 'https://besterp.rkesa.pt/contacts/' . $client->id);
            }
        }

        // Set worksheet title
        $objPHPExcel->getActiveSheet()->setTitle($fileName);

        //save the file to the server (Excel2007)
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($fileName . '.xlsx');

        $this->info('Done');
    }
}
