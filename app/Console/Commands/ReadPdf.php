<?php

namespace App\Console\Commands;

use App\Services\PdfsToCsvService;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;

class ReadPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'read_pdf:run';

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
     * @return int
     */
    public function handle()
    {

        $this->info("Lendo os pdfs salvos em: ".storage_path('downloads/pdfs'));
        $pdfToCsvService = new PdfsToCsvService;
        $pdfToCsvService->run();
        $this->info("CSV salvo em: ".$pdfToCsvService->run());
        return;
    }
}
