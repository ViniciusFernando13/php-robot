<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;

class PdfsToCsvService
{

    /**
     * Reads the PDFs and generate csv
     * @return string
     */
    public function run()
    {
        // get all pdfs
        $files = Storage::disk('downloads')->files('pdfs');

        // set columns
        $columns = array(
            'credenciado',
            'nome_clinica',
            'referencia',
            'numero_pagamento',
            'data_pagamento',
            'nr',
            'conta',
            'numero_associado',
            'nome_associado',
            'nome_operadora',
            'cnpj_operadora',
            'servico',
            'realizacao',
            'quantidade',
            'valor_informado',
            'valor_glosado',
            'valor_aprovado'
        );

        // function to generate pdf
        $callback = function () use ($files, $columns) {

            // init file
            $file = fopen(storage_path('downloads/csv/pdfs_to_csv.csv'), 'w+');
            
            // add columns in first row
            fputcsv($file, $columns);

            // init headers
            $headers = [];

            // init associado
            $associado = [];

            // init conta
            $conta = null;
            
            // loop all pdfs
            foreach ($files as $file_path) {

                // get content by file
                $text = (new Pdf())
                    ->setPdf(storage_path("downloads/$file_path"))
                    ->text();
                $text = explode('Item', $text);
                $operadora = preg_split('/\r\n|\r|\n/', $text[count($text)-1]);
                $operadora = array_values(array_filter($operadora, function ($item) {
                    return $item != '';
                }));
                $operadora = [
                    $operadora[count($operadora)-3],
                    explode(' ', $operadora[count($operadora)-2])[1],
                ];

                foreach ($text as $index => $row) {
                    $row = preg_split('/\r\n|\r|\n/', $row);
                    $row = array_values(array_filter($row, function ($item) {
                        return $item != '';
                    }));
                    //dump($row);
                    if ($index == 0) {
                        $cred =  array_values(array_filter($row, function ($val) {
                            return strpos($val, 'Credenciado:') !== false;
                        }));
                        $nr =  array_values(array_filter($row, function ($val) {
                            return strpos($val, 'NR:') !== false;
                        }));
                        $np =  array_values(array_filter($row, function ($val) {
                            return strpos($val, 'Número Pagamento:') !== false;
                        }));
                        $dpg =  array_values(array_filter($row, function ($val) {
                            return strpos($val, 'Data Pagamento:') !== false;
                        }));
                        $headers = [
                            str_replace(['Credenciado:', ' '], '', $cred[0]),
                            $row[1],
                            '',
                            str_replace(['Número Pagamento:', ' '], '', count($np) > 0 ? $np[0] : ''),
                            str_replace(['Data Pagamento:', ' '], '', count($dpg) > 0 ? $dpg[0] : ''),
                            str_replace(['NR:', ' '], '', $nr[0]),
                        ];
                        $associado =  array_values(array_filter($row, function ($val) {
                            return strpos($val, 'Associados:') !== false;
                        }));
                        $associado = explode(' ', $associado[0], 3);
                        $conta =  array_values(array_filter($row, function ($val) {
                            return strpos($val, 'Conta:') !== false;
                        }));
                        $conta = str_replace(['Conta:', ' '], '', $conta[0]);
                    } else {
                        $associadoAtual = $associado;
                        $associado =  array_values(array_filter($row, function ($val) {
                            return strpos($val, 'Associados:') !== false;
                        }));
                        if (count($associado) > 0) $associado = explode(' ', $associado[0], 3);
                        $contaAtual = $conta;
                        $conta =  array_values(array_filter($row, function ($val) {
                            return strpos($val, 'Conta:') !== false;
                        }));
                        if (count($conta) > 0) $conta = str_replace(['Conta:', ' '], '', $conta[0]);
                        $med = [];
                        $indexInit = array_search('Serviço', $row);
                        $dateIndex = null;
                        $meds = [];
                        foreach ($row as $i => $val) {
                            $valParts = explode(' ', $val, 3);
                            if (
                                strpos(strtoupper($val), strtoupper('Procedimento não autorizado na AP')) === false &&
                                $i > $indexInit &&
                                count($valParts) > 1 &&
                                is_numeric($valParts[0]) &&
                                (strlen($valParts[0]) > 1 || (strlen($valParts[0]) == 1 && is_numeric($valParts[1])))
                            ) {
                                $meds[] = strtoupper(substr($val, strlen($val) - 5)) == 'IRETA' || strtoupper(substr($val, strlen($val) - 4)) == '(POR' || strtoupper(substr($val, strlen($val) - 4)) == 'E/OU' || strlen($val) == 35 ||
                                    (strlen($valParts[0]) == 1 && strlen($val) == 37) ?
                                    [$val . ' ' . $row[$i + 1]] : [$val];
                                continue;
                            }
                            $verifyDate = explode('/', $val);
                            if (count($verifyDate) == 3 && $dateIndex === null) {
                                $dateIndex = $i;
                                continue;
                            }
                        }
                        $dateProblemIndex = null;
                        $qtdProblem = 0;
                        $meds = array_map(function ($val) use (&$dateIndex, &$dateProblemIndex, &$qtdProblem, $row) {
                            if ($row[$dateIndex] != $row[$dateIndex + 1]) {
                                $val[] = $row[$dateIndex];
                                $val[] = $row[$dateIndex + 1];
                                $val[] = $row[$dateIndex + 2];
                                $val[] = $row[$dateIndex + 3];
                                $val[] = $row[$dateIndex + 4];
                                $dateIndex = $dateIndex + 7;
                            } elseif ($dateProblemIndex === null) {
                                $dateProblemIndex = $dateIndex;
                                $qtdProblem++;
                            } elseif ($dateProblemIndex) {
                                $qtdProblem++;
                            }
                            return $val;
                        }, $meds);
                        if ($dateProblemIndex !== null) {
                            $medsProblem = array_filter($meds, function ($med) {
                                return count($med) == 1;
                            });
                            $qtdStep = 1;
                            $qtdStepped = 0;
                            $normalized = false;
                            if ($row[$dateProblemIndex] == $row[$dateProblemIndex + $qtdStep]) {
                                while ($row[$dateProblemIndex] == $row[$dateProblemIndex + $qtdStep]) {
                                    $qtdStep++;
                                }
                                $qtdStep--;
                            }
                            $meds = array_map(function ($med) use ($row, &$dateProblemIndex, &$qtdStep, &$qtdStepped, &$normalized) {
                                if ($qtdStepped - 1 == $qtdStep) {
                                    if (!$normalized) {
                                        if (count(explode('/', $row[$dateProblemIndex + $qtdStepped * 5 + $qtdStep + 1])) == 3) {
                                            $dateProblemIndex = $dateProblemIndex + $qtdStepped * 5 + $qtdStep + 1;
                                            $normalized = true;
                                        }
                                    }
                                    if (count(explode('/', $row[$dateProblemIndex + 1])) == 3) {
                                        $qtdStep = 1;
                                        $qtdStepped = 0;
                                        $normalized = false;
                                        while ($row[$dateProblemIndex] == $row[$dateProblemIndex + $qtdStep]) {
                                            $qtdStep++;
                                        }
                                        $qtdStep--;
                                        $med[] = $row[$dateProblemIndex];
                                        $med[] = $row[$dateProblemIndex + $qtdStep + 1];
                                        $med[] = $row[$dateProblemIndex + ($qtdStep + 1) * 2];
                                        $med[] = $row[$dateProblemIndex + ($qtdStep + 1) * 3];
                                        $med[] = $row[$dateProblemIndex + ($qtdStep + 1) * 4];
                                        $dateProblemIndex = $dateProblemIndex + 1;
                                        $qtdStepped++;
                                    } else {
                                        $med[] = $row[$dateProblemIndex];
                                        $med[] = $row[$dateProblemIndex + 1];
                                        $med[] = $row[$dateProblemIndex + 2];
                                        $med[] = $row[$dateProblemIndex + 3];
                                        $med[] = $row[$dateProblemIndex + 4];
                                        $dateProblemIndex = $dateProblemIndex + 7;
                                    }
                                } else {
                                    $med[] = $row[$dateProblemIndex];
                                    $med[] = $row[$dateProblemIndex + $qtdStep + 1];
                                    $med[] = $row[$dateProblemIndex + ($qtdStep + 1) * 2];
                                    $med[] = $row[$dateProblemIndex + ($qtdStep + 1) * 3];
                                    $med[] = $row[$dateProblemIndex + ($qtdStep + 1) * 4];
                                    $dateProblemIndex = $dateProblemIndex + 1;
                                    $qtdStepped++;
                                }

                                return $med;
                            }, $medsProblem);
                        }
                        foreach ($meds as $med) {
                            $rowInsert = [
                                ...$headers,
                                $contaAtual,
                                $associadoAtual[1],
                                $associadoAtual[2],
                                $operadora[0],
                                $operadora[1],
                                ...$med
                            ];

                            fputcsv($file, $rowInsert);
                        }
                    }
                }
            }
            fclose($file);
        };
        $callback();
        return storage_path('downloads/csv/pdfs_to_csv.csv');
    }
}
