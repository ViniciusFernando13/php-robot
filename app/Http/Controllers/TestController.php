<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GetTableCrawlerService;
use App\Services\PdfsToCsvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;
use Throwable;

class TestController extends Controller
{

    /**
     * Constructor method
     *
     */
    public function __construct()
    {
    }

    public function table(Request $request)
    {
        try {
            $url = "https://testpages.herokuapp.com/styled/tag/table.html";
            echo "Acessando url: $url<br />";
            $getTableCrawlerService = new GetTableCrawlerService;
            echo 'Lendo e salvando dados da tabela<br />';
            $getTableCrawlerService->run($url);
            echo "Dados salvos no banco de dados";
            return;
        } catch (Throwable $th) {
            dd($th);
            echo $th->getMessage();
        }
    }

    public function pdfs_to_csv()
    {
        $pdfToCsvService = new PdfsToCsvService;
        return response()->download($pdfToCsvService->run());
    }
}
