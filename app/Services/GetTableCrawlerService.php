<?php

namespace App\Services;

use App\Models\Register;
use DOMDocument;
use Error;
use Throwable;

class GetTableCrawlerService
{
    /**
     * Get html table row by url and insert to database
     * @param string $url
     * @return $inserts
     */
    public function run(String $url)
    {
        $rows = $this->getTable($url);
        $inserts = $this->saveRows($rows);
        return $inserts;
    }

    /**
     * Get html table row by url
     * @param string $url
     * @return Array
     */
    private function getTable(String $url)
    {
        $dom = new DOMDocument('1.0');

        // load html file by url
        $dom->loadHTMLFile($url);

        // get all tr tags
        $rowsDom = $dom->getElementsByTagName('tr');

        // get headers by th tags
        $cellsHeader = [];
        foreach ($rowsDom as $row) {
            $cells = $row->getElementsByTagName('th');
            foreach ($cells as $cell) {
                $cellsHeader[] = strtolower($cell->nodeValue);
            }
        }

        // get and format contents by td tags
        $rowsContent = [];
        foreach ($rowsDom as $row) {
            $cells = $row->getElementsByTagName('td');

            // verify if row contains td tag
            if (count($cells) === 0) continue;

            // get row data and format
            $dataCell = [];
            foreach ($cells as $index => $cell) {
                if (!isset($cellsHeader[$index])) continue;
                $dataCell[$cellsHeader[$index]] = $cell->nodeValue;
            }
            $rowsContent[] = $dataCell;
        }
        return $rowsContent;
    }


    /**
     * Save rows in database
     * @param Array $rows
     * @return Array
     */
    private function saveRows(Array $rows = [])
    {
        try {
            if (Register::insert($rows)) {
                return Register::get();
            }
            throw new Error('Não foi possível salvar os dados');
        } catch (Throwable $th) {
            throw $th;
        }
    }
}
