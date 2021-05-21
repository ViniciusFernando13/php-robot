<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GetTableCrawlerService;
use DOMDocument;
use Illuminate\Http\Request;

class TestController extends Controller
{

    /**
     * Constructor method
     *
     */
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $getTableCrawlerService = new GetTableCrawlerService;
        $url = 'https://testpages.herokuapp.com/styled/tag/table.html';
        return dump($getTableCrawlerService->run($url));
    }
}
