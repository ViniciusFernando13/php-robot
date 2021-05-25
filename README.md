
## Sobre o php-robot

A aplicação tem vários comandos, sendo eles:

- "php artisan get_and_save_table:run" ou acessando o endpoint "/table" acessa [https://testpages.herokuapp.com/styled/tag/table.html] e pega os dados da tabela e salva no banco de dados.
- "php artisan submit_external_form:run" acessa [https://testpages.herokuapp.com/styled/basic-html-form-test.html], preenche e envia o formulário.
- "php artisan download_file:run" acessa [https://testpages.herokuapp.com/styled/download/download.html], faz download do arquivo pelo botão "Direct Link Download", le o arquivo e retorna seu conteúdo.
- "php artisan upload_file:run" acessa [https://testpages.herokuapp.com/styled/download/download.html], faz download do arquivo pelo botão "Direct Link Download" e faz o upload na página [https://testpages.herokuapp.com/styled/file-upload-test.html].
- "php artisan read_pdf:run" ou acessando o endpoint "/pdf" realiza a leitura de todos os pdfs, usando a library [https://github.com/spatie/pdf-to-text], dentro da pasta "storage/downloads/pdfs" e gera um CSV com os dados.