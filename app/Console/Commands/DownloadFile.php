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
        $fileContent = $downloadFileService->runDownloadAndExcludeFile($url, $driver);

        // close browser
        $driver->close();
        $this->info($fileContent);
        return $fileContent;
    }
}
