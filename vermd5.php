<?php



$arquivos = array();
$arquivos[] = "model/contabilidade/arquivos/sigfis/SigfisArquivoItemReceita.model.php";
$arquivos[] = "model/contabilidade/arquivos/sigfis/SigfisArquivoPrevisaoReceita.model.php";
$arquivos[] = "model/contabilidade/arquivos/sigfis/SigfisArquivoReceitaArrecadada.model.php";
$arquivos[] = "model/contabilidade/arquivos/sigfis/SigfisArquivoAtualizaPrevisaoReceita.model.php";
$arquivos[] = "model/contabilidade/arquivos/sigfis/SigfisArquivoEmpenho.model.php";

foreach ($arquivos as $arquivo) {


    $dados[] = $arquivo."|".md5(file_get_contents($arquivo));
}

file_put_contents('sigfis.csv', implode($dados, "\n"));