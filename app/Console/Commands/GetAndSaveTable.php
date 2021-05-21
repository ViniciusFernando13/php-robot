<?php

namespace App\Console\Commands;

use App\Services\GetTableCrawlerService;
use DOMDocument;
use Illuminate\Console\Command;
use Throwable;

class GetAndSaveTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_and_save_table:run {url=https://testpages.herokuapp.com/styled/tag/table.html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the table by the URL passed';

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
        try {
            $url = $this->argument('url');
            $this->info("Acessando url: $url");
            $getTableCrawlerService = new GetTableCrawlerService;
            $this->info('Lendo e salvando dados da tabela');
            $getTableCrawlerService->run($url);
            return;
        } catch (Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
