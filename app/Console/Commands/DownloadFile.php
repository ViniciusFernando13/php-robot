<?php

namespace App\Console\Commands;

use App\Services\DownloadFileService;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Console\Command;

class DownloadFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download_file:run {url=https://testpages.herokuapp.com/styled/download/download.html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download file by url';

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
        $url = $this->argument('url');
        $this->info("Acessando url: $url");
        $this->info('Fazendo download');
        $downloadFileService = new DownloadFileService;
        $fileContent = $downloadFileService->run($url);
        $this->info($fileContent);
        return $fileContent;
    }
}
