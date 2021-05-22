<?php

namespace App\Console\Commands;

use App\Services\DownloadFileService;
use App\Services\ExternalFormService;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UploadFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload_file:run {urlDownload=https://testpages.herokuapp.com/styled/download/download.html} {urlUpload=https://testpages.herokuapp.com/styled/file-upload-test.html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload file in url';

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
        $urlDownload = $this->argument('urlDownload');
        $urlUpload = $this->argument('urlUpload');
        $this->info("Acessando url: $urlDownload");
        $this->info('Fazendo download');

        // init chrome driver
        putenv('WEBDRIVER_CHROME_DRIVER=./storage/chromedriver');
        $opt = new ChromeOptions();
        $opt->setExperimentalOption('prefs', [
            'download.default_directory' => '/var/www/html/teste-tk/storage/downloads',
            "download.prompt_for_download" => false,
            "download.directory_upgrade" => true,
            "safebrowsing.enabled" => true,
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $opt);
        $driver = ChromeDriver::start($capabilities);
        $downloadFileService = new DownloadFileService;
        $fileName = $downloadFileService->runDownloadFile($urlDownload, $driver);
        $externalFormService = new ExternalFormService;
        $this->info($fileName);
        $inputs = [
            'filename'  => [
                'type'  => 'input',
                'value' => storage_path('downloads') . "/$fileName",
            ],
            'filetype'  => [
                'type'  => 'radio',
                'value' => 'text',
            ],
        ];
        $this->info("Acessando url: $urlUpload");
        $this->info('Fazendo upload');
        $externalFormService->run($urlUpload, $inputs, $driver);

        // delete file
        Storage::disk('downloads')->delete($fileName);
        return;
    }
}
