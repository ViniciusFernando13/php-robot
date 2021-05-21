<?php

namespace App\Console\Commands;

use App\Services\ExternalFormService;
use Illuminate\Console\Command;
use Throwable;

class SubmitExternalForm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submit_external_form:run {url=https://testpages.herokuapp.com/styled/basic-html-form-test.html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill external form by URL';

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
            $externalFormService = new ExternalFormService;
            $this->info('Carregando o formulário e preenchendo');
            $inputs = [
                'username'          => [
                    'type'  => 'input',
                    'value' => 'Vinicius',
                ],
                'password'      => [
                    'type'  => 'input',
                    'value' => 'vinicius',
                ],
                'comments'          => [
                    'type'  => 'input',
                    'value' => 'comentario',
                ],
                'filename'  => [
                    'type'  => 'input',
                    'value' => '/var/www/html/teste-tk/storage/teste.txt',
                ],
                'checkboxes'        => [
                    'type'  => 'checkbox',
                    'value' => ['cb1', 'cb2'],
                ],
                'radioval'          => [
                    'type'  => 'radio',
                    'value' => 'rd1',
                ],
                'multipleselect[]'  => [
                    'type'  => 'select',
                    'value' => ['ms1', 'ms3'],
                ],
                'dropdown'  => [
                    'type'  => 'select',
                    'value' => 'dd6',
                ],
            ];
            $externalFormService->run($url, $inputs);
            return;
        } catch (Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
