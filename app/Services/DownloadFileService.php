<?php

namespace App\Services;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Storage;

class DownloadFileService
{

    /**
     * Download file and return content
     * @param string $url
     * @return string
     */
    public function run(string $url)
    {
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

        // open driver with url
        $driver->get($url);

        // find download button and click
        $btnDownload = $driver->findElement(WebDriverBy::id('direct-download'));
        if ($btnDownload) $btnDownload->click();

        // await download
        while (!Storage::disk('downloads')->exists('textfile.txt')) {
            sleep(1);
        }

        // close browser
        $driver->close();

        // get content by file
        $content = Storage::disk('downloads')->get('textfile.txt');
        
        // delete file
        Storage::disk('downloads')->delete('textfile.txt');
        
        // return content
        return $content;
    }
}
