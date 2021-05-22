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
     * @return string|boolean
     */
    public function runDownloadFile(string $url, $driver)
    {

        // open driver with url
        $driver->get($url);

        // find download button and click
        $btnDownload = $driver->findElement(WebDriverBy::id('direct-download'));
        if ($btnDownload) {
            $btnDownload->click();

            // await download
            while (!Storage::disk('downloads')->exists('textfile.txt')) {
                sleep(1);
            }

            return 'textfile.txt';
        }
        return false;
    }

    /**
     * Download file and return content
     * @param string $url
     * @return string
     */
    public function runDownloadAndExcludeFile(string $url, $driver) {
        if($fileName = $this->runDownloadFile($url, $driver)) {

            // get content by file
            $content = Storage::disk('downloads')->get($fileName);

            // delete file
            Storage::disk('downloads')->delete($fileName);

            // return content
            return $content;
            
        }
    }
}
