<?php
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');



require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
include("dbforms/db_funcoes.php");



$ninst = db_getsession('DB_instit');

function formatNumero($numero, $casas = 2){
   return number_format($numero, $casas, ',', '.');;
}

function testa($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}




$format_rel = "pdf";
if (isset($_GET['format'])){
    $format_rel = $_GET['format'];
}

$nome_responsavel = "";
if (isset($_GET['nome_responsavel'])){
    $nome_responsavel = $_GET['nome_responsavel'];
}

$matricula = "";
if (isset($_GET['matricula'])){
    $matricula = $_GET['matricula'];
}


$exercicio = "";
$exercicio_anterior = "";
$dt_ini = date("Y-m-d H:i:s");
$dt_fin = date("Y-m-d H:i:s");
if (isset($_GET['dDataInicial']) && isset($_GET['dDataFinal'])){
if (($_GET['dDataInicial'] != "") && ($_GET['dDataFinal'] != "")) {
    $dt_ini = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['dDataInicial']." 00:00:00");
    $dt_ini = $dt_ini->format('Y-m-d H:i:s'); 
    
    $dt_fin = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['dDataFinal']." 23:59:59");
    $dt_fin = $dt_fin->format('Y-m-d H:i:s');

    $exercicio = date_format(date_create($_GET['dDataInicial']), "d/m/Y")." - ".date_format(date_create($_GET['dDataFinal']),"d/m/Y");
}}


if (isset($_GET['exercicio']) && ($exercicio == "")){
    $dt_ini = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['exercicio']."-01-01 00:00:00");
    $dt_ini = $dt_ini->format('Y-m-d H:i:s'); 
    
    $dt_fin = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['exercicio']."-12-31 23:59:59");
    $dt_fin = $dt_fin->format('Y-m-d H:i:s');
    $exercicio = $_GET['exercicio'];

    $exercicio_anterior = ((int) $exercicio) - 1;
}

$anorelatorio = $_GET["exercicio"];
$anoanterior = $anorelatorio - 1;

$dtinianterior = "1500-01-01 00:00:00";
$dtinianterior22 = $anoanterior."-01-01 23:59:59";
$dtfinanterior = $anoanterior."-12-31 23:59:59";


$sqldadosinstituicao = pg_query("SELECT nomeinst, nomeinstabrev, munic FROM db_config WHERE codigo = ".db_getsession("DB_instit")." ");
$dadosinstituicao = pg_fetch_all($sqldadosinstituicao);
$instituicao = $dadosinstituicao[0]["nomeinstabrev"] ." - ".$dadosinstituicao[0]["nomeinst"];
$municipio = $dadosinstituicao[0]["munic"];


/*function colunaAM($ano){
    $inst = db_getsession("DB_instit");
    $sql = "SELECT t52_ident, t52_descr, t52_dtaqu, t52_valaqu, t52_codcla, t52_obs, t52_valaquhist, COALESCE((select t44_valoratual from BENSDEPRECIACAO vl where vl.t44_sequencial=(select max(t44_sequencial) from BENSDEPRECIACAO sq where sq.t44_bens=t52_bem)),t52_valaqu) t58_valoratual, t52_bem FROM Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit where t52_instit = {$inst} and t64_bemtipos={$tipo} and t52_dtaqu >= '".$dtini."' and t52_dtaqu <= '".$dtfin."' order by t52_descr";
}*/


//Coluna B
function colunaBmovel($dtini, $dtfin){
    $inst = db_getsession("DB_instit");
    //$sql = pg_query("SELECT sum(t52_valaquhist) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 1 and not exists ( select 1 from bensbaix where bensbaix.t55_codbem = t52_bem )");
    $sql = pg_query("SELECT sum(t52_valaquhist) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 1");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];    
}


function colunaBimovel($dtini, $dtfin){
    $inst = db_getsession("DB_instit");
    //$sql = pg_query("SELECT sum(t52_valaquhist) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 2 and not exists ( select 1 from bensbaix where bensbaix.t55_codbem = t52_bem )");
    $sql = pg_query("SELECT sum(t52_valaquhist) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 2");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];    
}



//Coluna B
function colunaB2021movel($dtini, $dtfin){
    $inst = db_getsession("DB_instit");    
    //$sql = pg_query("SELECT sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 1 and not exists ( select 1 from bensbaix where bensbaix.t55_codbem = t52_bem )");
    $sql = pg_query("SELECT sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 1");
    //$kql = "SELECT sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 1";
    //var_dump($kql); die("Co");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];    
}

function colunaB2021imovel($dtini, $dtfin){
    $inst = db_getsession("DB_instit");
    //$sql = pg_query("SELECT sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 2 and not exists ( select 1 from bensbaix where bensbaix.t55_codbem = t52_bem )");
    $sql = pg_query("SELECT sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 2");
    //$kql = "SELECT sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '{$dtini}' and '{$dtfin}' and t64_bemtipos = 2";
    //var_dump($kql); die("32");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}

//Coluna C -
function colunaCmovel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t63_agregarvalor) FROM benscorr INNER JOIN bens on t63_codbem = t52_bem INNER JOIN clabens ON t52_codcla = t64_codcla WHERE t63_exercicio = {$anorelatorio} AND t63_dataprocessamento IS NOT NULL AND t64_bemtipos = 1 AND t64_instit = {$inst}");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["sum"]; //Retorna Null, tem que ser 0
}

function colunaCimovel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t63_agregarvalor) FROM benscorr INNER JOIN bens on t63_codbem = t52_bem INNER JOIN clabens ON t52_codcla = t64_codcla WHERE t63_exercicio = {$anorelatorio} AND t63_dataprocessamento IS NOT NULL AND t64_bemtipos = 2 AND t64_instit = {$inst}");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["sum"]; //Retorna Null, tem que ser 0
}


function colunaC2021movel($anorelatorio){
    $inst = db_getsession("DB_instit");    
    $sql = pg_query("SELECT (select  sum(t63_agregarvalor) from benscorr inner join bens on t63_codbem = t52_bem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t64_bemtipos = 1 and t63_exercicio = {$anorelatorio}) - (select sum(t63_corrigirvalor) from benscorr inner join bens on t63_codbem = t52_bem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t64_bemtipos = 1 and t63_exercicio = {$anorelatorio}) as total");
    //$kql = "SELECT (select  sum(t63_agregarvalor) from benscorr inner join bens on t63_codbem = t52_bem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t64_bemtipos = 1 and t63_exercicio = {$anorelatorio}) - (select sum(t63_corrigirvalor) from benscorr inner join bens on t63_codbem = t52_bem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t64_bemtipos = 1 and t63_exercicio = {$anorelatorio}) as total";
    //var_dump($kql); die("Olha");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["total"];
}



function colunaC2021imovel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT (select  sum(t63_agregarvalor) from benscorr inner join bens on t63_codbem = t52_bem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t64_bemtipos = 2 and t63_exercicio = {$anorelatorio}) - (select sum(t63_corrigirvalor) from benscorr inner join bens on t63_codbem = t52_bem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t64_bemtipos = 2 and t63_exercicio = {$anorelatorio}) as total");
    //$kql = "SELECT (select  sum(t63_agregarvalor) from benscorr inner join bens on t63_codbem = t52_bem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t64_bemtipos = 2 and t63_exercicio = {$anorelatorio}) - (select sum(t63_corrigirvalor) from benscorr inner join bens on t63_codbem = t52_bem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t64_bemtipos = 2 and t63_exercicio = {$anorelatorio}) as total";
    //var_dump($kql); die("D");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["total"];
}

function colunaC2022movel($anorelatorio){
    $inst = db_getsession("DB_instit");    
    $sql = pg_query("SELECT sum(t63_agregarvalor) as total from benscorr inner join bens on t52_bem = t63_codbem inner join clabens on t52_codcla = t64_codcla WHERE t52_instit = {$inst} and t63_exercicio = {$anorelatorio} and t63_dataprocessamento is not null and t64_bemtipos = 1");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["total"];    
}



function colunaC2022imovel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t63_agregarvalor) as total from benscorr inner join bens on t52_bem = t63_codbem inner join clabens  on t52_codcla = t64_codcla where t52_instit = {$inst} and t63_exercicio = {$anorelatorio} and t63_dataprocessamento is not null and t64_bemtipos = 2");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["total"];
    
}


//ALTERADO EM 2022 COM SQL MANDADA PELO THIAGO
function excecao21moveis(){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT (select sum(t52_valaqu) from bens  inner join clabens on t52_codcla = t64_codcla where t52_instit = 45 and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 1 and t52_valaquhist <> t52_valaqu) - (select sum(t52_valaquhist) from bens  inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 1 and t52_valaquhist <> t52_valaqu) as sum");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
    //return $resultado;
}

function excecao21Imoveis(){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT (select sum(t52_valaqu) from bens  inner join clabens on t52_codcla = t64_codcla where t52_instit = 45 and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 2 and t52_valaquhist <> t52_valaqu) - (select sum(t52_valaquhist) from bens  inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 2 and t52_valaquhist <> t52_valaqu) as sum");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}


function colunaCmovel2($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t63_agregarvalor) FROM benscorr INNER JOIN bens on t63_codbem = t52_bem INNER JOIN clabens ON t52_codcla = t64_codcla WHERE t63_exercicio <= {$anorelatorio} AND t63_dataprocessamento IS NOT NULL AND t64_bemtipos = 1 AND t64_instit = {$inst}");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["sum"];
}

function colunaCimovel2($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t63_agregarvalor) FROM benscorr INNER JOIN bens on t63_codbem = t52_bem INNER JOIN clabens ON t52_codcla = t64_codcla WHERE t63_exercicio <= {$anorelatorio} AND t63_dataprocessamento IS NOT NULL AND t64_bemtipos = 2 AND t64_instit = {$inst}");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["sum"];
}

//Coluna D
function colunaDmovel($dtini, $dtfin){
    $inst = db_getsession("DB_instit");    
    $sql = pg_query("SELECT sum(t52_valaqu) from bensbaix inner join bens on t55_codbem = t52_bem inner join clabens on t64_codcla = t52_codcla where t52_instit = {$inst} and t64_bemtipos = 1 and t55_baixa between '{$dtini}' and '{$dtfin}'");
    //$kql = "SELECT sum(t52_valaqu) from bensbaix inner join bens on t55_codbem = t52_bem inner join clabens on t64_codcla = t52_codcla where t52_instit = {$inst} and t64_bemtipos = 1 and t55_baixa between '{$dtini}' and '{$dtfin}'";
    //var_dump($kql); die("k");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];;
}

function colunaDimovel($dtini, $dtfin){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t52_valaqu) from bensbaix inner join bens on t55_codbem = t52_bem inner join clabens on t64_codcla = t52_codcla where t52_instit = {$inst} and t64_bemtipos = 2 and t55_baixa between '{$dtini}' and '{$dtfin}'");
    //$kql = "SELECT sum(t52_valaqu) from bensbaix inner join bens on t55_codbem = t52_bem inner join clabens on t64_codcla = t52_codcla where t52_instit = {$inst} and t64_bemtipos = 2 and t55_baixa between '{$dtini}' and '{$dtfin}'";
    //var_dump($kql); die("Yx");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}

//Coluna E
function colunaEmovel($dtini, $dtfin){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT t52_ident, t52_descr, t55_baixa,  t52_valaqu, t52_codcla, t51_descr, COALESCE((select t44_valoratual from BENSDEPRECIACAO vl where vl.t44_sequencial=(select max(t44_sequencial) from BENSDEPRECIACAO sq where sq.t44_bens=t52_bem)),t52_valaqu) t58_valoratual from Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit inner join BENSBAIX on t55_codbem = t52_bem inner join BENSMOTBAIXA on t51_motivo = t55_motivo WHERE t52_instit = {$inst} and t64_bemtipos=1 and t55_baixa >= '{$dtini}' and t55_baixa <= '{$dtfin}' order by t52_descr");
    $reav_baixa = 0;
    foreach ($resultado as $linha) {
        if($linha['t58_valoratual'] > $linha['t52_valaqu']){
            $reav_baixa += $linha['t58_valoratual'] - $linha['t52_valaqu'];
        }else{
            $reav_baixa += $linha['t52_valaqu'] - $linha['t58_valoratual'];
        }
    }
    $reav_baixa = 0;
    return $reav_baixa;
}

function colunaEimovel($dtini, $dtfin){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT t52_ident, t52_descr, t55_baixa,  t52_valaqu, t52_codcla, t51_descr, COALESCE((select t44_valoratual from BENSDEPRECIACAO vl where vl.t44_sequencial=(select max(t44_sequencial) from BENSDEPRECIACAO sq where sq.t44_bens=t52_bem)),t52_valaqu) t58_valoratual from Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit inner join BENSBAIX on t55_codbem = t52_bem inner join BENSMOTBAIXA on t51_motivo = t55_motivo WHERE t52_instit = {$inst} and t64_bemtipos=2 and t55_baixa >= '{$dtini}' and t55_baixa <= '{$dtfin}' order by t52_descr");
    $reav_baixa = 0;
    foreach ($resultado as $linha) {
        if($linha['t58_valoratual'] > $linha['t52_valaqu']){
            $reav_baixa += $linha['t58_valoratual'] - $linha['t52_valaqu'];
        }else{
            $reav_baixa += $linha['t52_valaqu'] - $linha['t58_valoratual'];
        }
    }
    $reav_baixa = 0;
    return $reav_baixa;
}



//CONSULTA ALTERADA POR THIAGO
function colunaE21movel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT (select sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 't' and t64_bemtipos = 1) - (select sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 'f' and t64_bemtipos = 1) as sum");
    //$kql = "SELECT (select sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 't' and t64_bemtipos = 1) - (select sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 'f' and t64_bemtipos = 1) as sum";
    //var_dump($kql); die("N");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}


function colunaE21imovel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT (select sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 't' and t64_bemtipos = 2) - (select sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 'f' and t64_bemtipos = 2) as sum");
    //$kql = "SELECT (select sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 't' and t64_bemtipos = 2) - (select sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 'f' and t64_bemtipos = 2) as sum";
    //var_dump($kql); die("Ks");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}



/*
function colunaE21movel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t64_bemtipos = 1");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}


function colunaE21imovel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t58_valorcalculado) from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t64_bemtipos = 2");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}
*/

function novacolunaC2021movel(){
    $inst = db_getsession("DB_instit");    
    $sql = pg_query("SELECT (select sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 1 and t52_valaquhist <> t52_valaqu) - (select sum(t52_valaquhist) from bens  inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 1 and t52_valaquhist <> t52_valaqu) as total");
    //$kql = "SELECT (select sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 1 and t52_valaquhist <> t52_valaqu) - (select sum(t52_valaquhist) from bens  inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 1 and t52_valaquhist <> t52_valaqu) as total";
    //var_dump($kql); die("f");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["total"];
}

function novacolunaC2021Imovel(){
    $inst = db_getsession("DB_instit");    
    $sql = pg_query("SELECT (select sum(t63_agregarvalor) from benscorr inner join bens on t52_bem = t63_codbem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t63_exercicio = 2021 and t64_bemtipos = 2 and t63_dataprocessamento is not null) + (select sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 2 and t52_valaquhist <> t52_valaqu) - (select sum(t52_valaquhist) from bens  inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 2 and t52_valaquhist <> t52_valaqu) as total");
    //$kql = "SELECT (select sum(t63_agregarvalor) from benscorr inner join bens on t52_bem = t63_codbem inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t63_exercicio = 2021 and t64_bemtipos = 2 and t63_dataprocessamento is not null) + (select sum(t52_valaqu) from bens inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 2 and t52_valaquhist <> t52_valaqu) - (select sum(t52_valaquhist) from bens  inner join clabens on t52_codcla = t64_codcla where t52_instit = {$inst} and t52_dtaqu between '1968-01-01' and '2020-12-31' and t64_bemtipos = 2 and t52_valaquhist <> t52_valaqu) as total";
    //var_dump($kql); die("l");
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["total"];
}

function verificaFalsoEMovel($anorelatorio){
    $inst = db_getsession("DB_instit");    
    $sql = pg_query("SELECT t58_valorcalculado, t57_processado from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 'f' and t64_bemtipos = 1");    
    $resultado = pg_fetch_all($sql);    
    return $resultado;
}

function verificaFalsoEImovel($anorelatorio){
    $inst = db_getsession("DB_instit");    
    $sql = pg_query("SELECT t58_valorcalculado, t57_processado from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 'f' and t64_bemtipos = 2");    
    $resultado = pg_fetch_all($sql);    
    return $resultado;
}

function novonovocolunaE21movel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t58_valorcalculado) as sum from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 't' and t64_bemtipos = 1");    
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}


function novonovocolunaE21imovel($anorelatorio){
    $inst = db_getsession("DB_instit");
    $sql = pg_query("SELECT sum(t58_valorcalculado) as sum from bens inner join benshistoricocalculobem on t58_bens = t52_bem  inner join benshistoricocalculo on t58_benshistoricocalculo = t57_sequencial inner join clabens on t52_codcla = t64_codcla where t57_ano = {$anorelatorio} and t57_instituicao = {$inst} and t57_processado = 't' and t64_bemtipos = 2");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["sum"];
}


//2022
//============================================================================================
if($anorelatorio == 2022){
    $dt_ini21 = "2021-01-01 00:00:00";
    $dt_fin21 = "2021-12-31 23:59:59";
    $dtinianterior21 = "1500-01-01 00:00:00";
    $dtfinanterior21 = "2020-12-31 23:59:59";
//COLUNA B 2021
    $xcolunaBMoveis = colunaB2021movel($dt_ini21, $dt_fin21);
    $xcolunaBImoveis = colunaB2021imovel($dt_ini21, $dt_fin21);

//COLUNA C 2021
    $xcolunaCMoveis = novacolunaC2021movel();
    $xcolunaCImoveis = novacolunaC2021Imovel();

//COLUNA D 2021
    $xcolunaDMoveis = colunaDmovel($dt_ini21, $dt_fin21);
    $xcolunaDImoveis = colunaDimovel($dt_ini21, $dt_fin21); 

//COLUNA E 2021
    $xcolunaEMoveis = colunaE21movel(2021);
    $xcolunaEImoveis = colunaE21imovel(2021);


//Coluna A
    $xcolunaBMoveisa = colunaBmovel($dtinianterior21, $dtfinanterior21);
    $xcolunaBImoveisa = colunaBimovel($dtinianterior21, $dtfinanterior21);
    
    $xcolunaCMoveisa = colunaCmovel(2020);
    $xcolunaCImoveisa = colunaCimovel2(2020);
    
    $xcolunaDMoveisa = colunaDmovel($dtinianterior21, $dtfinanterior21);
    $xcolunaDImoveisa = colunaDimovel($dtinianterior21, $dtfinanterior21);
    
    $xcolunaEMoveisa = colunaEmovel($dtinianterior21, $dtfinanterior21);
    $xcolunaEImoveisa = colunaEimovel($dtinianterior21, $dtfinanterior21);
    
    $xcolunaAMoveis = $xcolunaBMoveisa + $xcolunaCMoveisa - $xcolunaDMoveisa - $xcolunaEMoveisa;
    $xcolunaAImoveis = $xcolunaBImoveisa + $xcolunaCImoveisa - $xcolunaDImoveisa - $xcolunaEImoveisa;
 

 
//a + b + c - d - e
$xcolunaFMoveis = $xcolunaAMoveis + $xcolunaBMoveis + $xcolunaCMoveis - $xcolunaDMoveis - $xcolunaEMoveis;
//$colunaFMoveis = number_format($colunaFMoveis, 2, ',', '.');

$xcolunaFImoveis = $xcolunaAImoveis + $xcolunaBImoveis + $xcolunaCImoveis - $xcolunaDImoveis - $xcolunaEImoveis;
//$colunaFImoveis = number_format($colunaFImoveis, 2, ',', '.');

}
//===========================================================================================
//2022




//COLUNA B
if($anorelatorio >= 2021){
    $colunaBMoveis = colunaB2021movel($dt_ini, $dt_fin);
    $colunaBImoveis = colunaB2021imovel($dt_ini, $dt_fin);    
} else {
    $colunaBMoveis = colunaBmovel($dt_ini, $dt_fin);
    $colunaBImoveis = colunaBimovel($dt_ini, $dt_fin);
}


//COLUNA C
if($anorelatorio == 2021){
    $colunaCMoveis = novacolunaC2021movel();
    $colunaCImoveis = novacolunaC2021Imovel();    
} elseif($anorelatorio > 2021){
    $colunaCMoveis = colunaC2022movel($anorelatorio);    
    $colunaCImoveis = colunaC2022imovel($anorelatorio);
} else{
    $colunaCMoveis = colunaCmovel($anorelatorio);
    $colunaCImoveis = colunaCimovel($anorelatorio);
}

//COLUNA D
$colunaDMoveis = colunaDmovel($dt_ini, $dt_fin);
$colunaDImoveis = colunaDimovel($dt_ini, $dt_fin); 

//COLUNA E
/*if($anorelatorio >= 2021){
    $colunaEMoveis = colunaE21movel($anorelatorio);
    $colunaEImoveis = colunaE21imovel($anorelatorio);    
} else {
    $colunaEMoveis = colunaEmovel($dt_ini, $dt_fin);
    $colunaEImoveis = colunaEimovel($dt_ini, $dt_fin);
}*/


if($anorelatorio == 2021){
    $colunaEMoveis = colunaE21movel($anorelatorio);
    $colunaEImoveis = colunaE21imovel($anorelatorio);
} elseif($anorelatorio > 2021){
    $verfem = verificaFalsoEMovel($anorelatorio);
    if($verfem){
        $colunaEMoveis = colunaE21movel($anorelatorio);
    } else {
        $colunaEMoveis = novonovocolunaE21movel($anorelatorio);
    }

    $verfemi = verificaFalsoEImovel($anorelatorio);    
    if($verfemi){        
        $colunaEImoveis = colunaE21imovel($anorelatorio);
    } else {
        $colunaEImoveis = novonovocolunaE21imovel($anorelatorio);        
    }
} else {
    $colunaEMoveis = colunaEmovel($dt_ini, $dt_fin);
    $colunaEImoveis = colunaEimovel($dt_ini, $dt_fin);
}



//Coluna A
if($anorelatorio == 1968){
    $colunaAMoveis = 0;
    $colunaAImoveis = 0;
} else{
    if($anorelatorio == 2022){
        $dt_ini21 = "2021-01-01 00:00:00";
        $dt_fin21 = "2021-12-31 23:59:59";
        $colunaBMoveisa = colunaB2021movel($dt_ini21, $dt_fin21);        
        $colunaBImoveisa = colunaB2021imovel($dt_ini21, $dt_fin21);
    }elseif($anorelatorio > 2022){
        $colunaBMoveisa = colunaB2021movel($dtinianterior, $dtfinanterior);
        $colunaBImoveisa = colunaB2021Imovel($dtinianterior, $dtfinanterior);
    } else {                
        $colunaBMoveisa = colunaBmovel($dtinianterior, $dtfinanterior);
        $colunaBImoveisa = colunaBimovel($dtinianterior, $dtfinanterior);
    }


    if($anorelatorio == 2022){
        $colunaCMoveisa = novacolunaC2021movel();
        $colunaCImoveisa = novacolunaC2021Imovel();
    } elseif($anorelatorio > 2022){
        $colunaCMoveisa = colunaC2021movel($anoanterior);
        $colunaCImoveisa = colunaC2021imovel($anoanterior);    
    } else {
        $colunaCMoveisa = colunaCmovel($anoanterior);
        $colunaCImoveisa = colunaCimovel2($anoanterior);    
    }
    
    if($anorelatorio == 2022){
        $dtinianterior21 = "1500-01-01 00:00:00";
        $dtfinanterior21 = "2020-12-31 23:59:59";
        $colunaDMoveisa = colunaDmovel($dtinianterior21, $dtfinanterior21);
        $colunaDImoveisa = colunaDimovel($dtinianterior21, $dtfinanterior21);
    }else {
        $colunaDMoveisa = colunaDmovel($dtinianterior, $dtfinanterior);
        $colunaDImoveisa = colunaDimovel($dtinianterior, $dtfinanterior);
    }

    if($anorelatorio >= 2022){
        $colunaEMoveisa = colunaE21movel($anoanterior);
        $colunaEImoveisa = colunaE21imovel($anoanterior);
    } else {
        $colunaEMoveisa = colunaEmovel($dtinianterior, $dtfinanterior);
        $colunaEImoveisa = colunaEimovel($dtinianterior, $dtfinanterior);
    }

    
    
if($anorelatorio == 2022){
    $colunaAMoveis = $xcolunaFMoveis;
    $colunaAImoveis = $xcolunaFImoveis;
}else{
    $colunaAMoveis = $colunaBMoveisa + $colunaCMoveisa - $colunaDMoveisa - $colunaEMoveisa;
    $colunaAImoveis = $colunaBImoveisa + $colunaCImoveisa - $colunaDImoveisa - $colunaEImoveisa;
}
    
    
    /*
    echo "Coluna B - {$colunaBMoveisa}";
    echo "<br>";
    echo "Coluna C - {$colunaCMoveisa}";
    echo "<br>";
    echo "Coluna D - {$colunaDMoveisa}";
    echo "<br>";
    echo "Coluna E - {$colunaEMoveisa}";
    echo "<br>";
    die("Anteriores - 2022");
    */

    //var_dump($colunaAMoveis);
    //die("2022");
} //Fim da Coluna A

 
//a + b + c - d - e
$colunaFMoveis = $colunaAMoveis + $colunaBMoveis + $colunaCMoveis - $colunaDMoveis - $colunaEMoveis;
//$colunaFMoveis = number_format($colunaFMoveis, 2, ',', '.');

$colunaFImoveis = $colunaAImoveis + $colunaBImoveis + $colunaCImoveis - $colunaDImoveis - $colunaEImoveis;
//$colunaFImoveis = number_format($colunaFImoveis, 2, ',', '.');



$bens_mov_ini = $colunaAMoveis;
$bens_mov_aqui = $colunaBMoveis;
$bens_mov_reav_aqui = $colunaCMoveis;
$bens_mov_baixa = $colunaDMoveis;
$bens_mov_reav_baixa = $colunaEMoveis;
$bens_mov_fin = $colunaFMoveis;

$bens_imov_ini = $colunaAImoveis;
$bens_imov_aqui = $colunaBImoveis;
$bens_imov_reav_aqui = $colunaCImoveis;
$bens_imov_baixa = $colunaDImoveis;
$bens_imov_reav_baixa = $colunaEImoveis;
$bens_imov_fin = $colunaFImoveis;



/*
echo "Coluna A - {$colunaAMoveis}";
echo "<br>";
echo "Coluna B - {$colunaBMoveis}";
echo "<br>";
echo "Coluna C - {$colunaCMoveis}";
echo "<br>";
echo "Coluna D - {$colunaDMoveis}";
echo "<br>";
echo "Coluna E - {$colunaEMoveis}";
echo "<br>";
echo "Coluna F - {$colunaFMoveis}";
echo "<br>";
echo "<hr>";
die("2021");


echo "Coluna A - {$colunaAImoveis}";
echo "<br>";
echo "Coluna B - {$colunaBImoveis}";
echo "<br>";
echo "Coluna C - {$colunaCImoveis}";
echo "<br>";
echo "Coluna D - {$colunaDImoveis}";
echo "<br>";
echo "Coluna E - {$colunaEImoveis}";
echo "<br>";
echo "Coluna F - {$colunaFImoveis}";
echo "<br>";
*/








if ($format_rel == "pdf"){
    require_once('tcpdf_include.php');

    $PDF_PAGE_ORIENTATION_LOCAL = "L";    
    $pdf = new TCPDF($PDF_PAGE_ORIENTATION_LOCAL, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Volta Redonda');
    $pdf->SetTitle('Relatório Legal XXVI');
    $pdf->SetSubject('Relatório Legal XXVI');
    $pdf->SetKeywords('Patrimônio, Relatório, Legal, XXVI');

    // set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $margin_top = 10;
    $pdf->SetMargins(PDF_MARGIN_LEFT, $margin_top, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

    // remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // ---------------------------------------------------------

    // set font
    $pdf->SetFont('dejavusans', '', 10);

    // add a page
    $pdf->AddPage();

    // writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
    // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)


    $html = '
    <table><tr style="text-align:center;">
        <td>MODELO 26</td>
    </tr></table>
    <br /><br />
    <table border="1" cellpadding="4"><tr style="text-align:center;">
        <td>DEMONSTRATIVO DA MOVIMENTAÇÃO DOS BENS PATRIMONIAIS NO EXERCÍCIO</td>
    </tr></table>
    <br /><br />
    <table border="1" cellpadding="4">
    <tr style="text-align:left;">
        <td>Órgão: '.$instituicao.'</td>
        <td>Município: '.$municipio.'</td>
        <td>Exercício: '.$exercicio.'</td>
    </tr>
    </table>';
    $pdf->writeHTML($html, true, false, true, false, '');


    $pdf->SetFont('dejavusans', '', 8);

    $pdf->Ln(3);

    $html = '
    <br />
    <table border="1" cellpadding="4">
    <tr style="text-align:center;">
        <td rowspan="4">Tipo</td>
        <td rowspan="3">Valor Líquido Inicial no Ano<br />Correspondente ao Registro<br />Contábil (R$)</td>
        <td colspan="4">Movimentação do Período no Ano</td>
        <td rowspan="3">Valor Líquido Final no Ano<br />Correspondente ao<br />Registro Contábil<br />(R$)</td>
    </tr>
    <tr style="text-align:center;">
        <td colspan="2">Entradas (R$)</td>
        <td colspan="2">Saídas (R$)</td>
    </tr>
    <tr style="text-align:center;">
        <td>Aquisições</td>
        <td>Reavaliações</td>
        <td>Baixas</td>
        <td>Reavaliações</td>
    </tr>
    <tr style="text-align:center;">
        <td>(A)</td>
        <td>(B)</td>
        <td>(C)</td>
        <td>(D)</td>
        <td>(E)</td>
        <td>(F = A+B+C-D-E)</td>
    </tr>
    <tr>
        <td>Bens Móveis</td><td align="right">'.formatNumero($bens_mov_ini, 2).'</td><td align="right">'.formatNumero($bens_mov_aqui,2).'</td><td align="right">'.formatNumero($bens_mov_reav_aqui,2).'</td><td align="right">'.formatNumero($bens_mov_baixa,2).'</td><td align="right">'.formatNumero($bens_mov_reav_baixa,2).'</td><td align="right">'.formatNumero($bens_mov_fin,2).'</td>
    </tr>
    <tr>
    <td>Bens Imóveis</td><td align="right">'.formatNumero($bens_imov_ini,2).'</td><td align="right">'.formatNumero($bens_imov_aqui,2).'</td><td align="right">'.formatNumero($bens_imov_reav_aqui,2).'</td><td align="right">'.formatNumero($bens_imov_baixa,2).'</td><td align="right">'.formatNumero($bens_imov_reav_baixa,2).'</td><td align="right">'.formatNumero($bens_imov_fin,2).'</td>
    </tr>
    <tr>
    <td>Total</td><td align="right">'.formatNumero($bens_mov_ini+$bens_imov_ini,2).'</td><td align="right">'.formatNumero($bens_mov_aqui+$bens_imov_aqui,2).'</td><td align="right">'.formatNumero($bens_mov_reav_aqui+$bens_imov_reav_aqui,2).'</td><td align="right">'.formatNumero($bens_mov_baixa+$bens_imov_baixa,2).'</td><td align="right">'.formatNumero($bens_mov_reav_baixa+$bens_imov_reav_baixa,2).'</td><td align="right">'.formatNumero($bens_mov_fin+$bens_imov_fin,2).'</td>
    </tr>
    </table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Ln(6);


    $html = '
    <table border="1" cellpadding="4">
    <tr>
        <td colspan="3">Nome: '.$nome_responsavel.'</td>
        <td colspan="2" align="center">Responsável pelos Bens Patrimoniais</td>
    </tr>
    <tr>
        <td>Matrícula: '.$matricula.'</td>
        <td>Data: '.date('d/m/Y').'</td>
        <td colspan="3">Assinatura:</td>
    </tr>
    <tr>
        <td width="60%" colspan="3">Declaro que os valores acima descritos guardam paridade com o constante nos registros contábeis OU não guardam paridade com o constante nos registros </td>
        <td width="20%" rowspan="2" align="center">Responsável pelo<br />Setor Contábil</td>
        <td width="20%" rowspan="2" align="center">CRC/RJ nº _____</td>
    </tr>
    <tr>
        <td colspan="3">Nome:</td>
    </tr>
    <tr>
        <td>Matrícula:</td>
        <td>Data: __/__/____</td>
        <td colspan="3">Assinatura:</td>
    </tr>
    </table>';
    $pdf->writeHTML($html, true, false, true, false, '');


    // add a page
    //$pdf->AddPage();

    //$html = '
    //<hr />
    //<h3 style="text-align:center">Tutorial Modelo 26</h3>
    //<p><b><u>Valor Líquido Inicial no Ano Correspondente ao Registro Contábil (R$):</b></u>  Neste campo indicar o valor pelo qual o ativo foi contabilizado no exercício anterior após a dedução de qualquer depreciação acumulada e das perdas acumuladas por redução ao valor recuperável.</p>
    //<p><b><u>Entradas/Aquisições:</b></u>  Neste campo indicar o valor total das aquisições ocorridas durante o exercício de referência.</p>
    //<p><b><u>Entradas/Reavaliações:</b></u> Neste campo indicar o valor total das reavaliações positivas ocorridas durante o exercício de referência, tais como custo subsequente adicional e ajustes, dentre outros.</p>
    //<p><b><u>Saídas/Baixas:</b></u> Neste campo indicar o valor total das baixas ocorridas durante o exercício de referência.</p>
    //<p><b><u>Saídas/Reavaliações:</b></u> Neste campo indicar o valor total das reavaliações negativas ocorridas durante o exercício de referência, tais como ajustes, depreciação, amortização e exaustão, dentre outros.</p>
    //<p><b><u>Valor Líquido Final no Ano Correspondente ao Registro Contábil (R$):</b></u>  Neste campo indicar o  resultado do cálculo algébrico das colunas A, B, C, D e E.</p>
    //<hr />';
    //$pdf->writeHTML($html, true, false, true, false, '');


    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output('pat2_relatoriolegalmodeloxxvi.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+

}else{
    

header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=relmodeloxxvi.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);


print "<table>";
print "<tr style='text-align:center'>";
print "<center>";
print "<td colspan='11' rowspan='2' style='text-align:center'>MODELO 26</td>";
print "</center>";
print "</tr>";
print "</table>";

print "<table border='1'>";
print "<tr style='text-align:center'>";
print "<center>";
print "<td colspan='11' rowspan='2'>".utf8_decode("DEMONSTRATIVO DA MOVIMENTAÇÃO DOS BENS PATRIMONIAIS NO EXERCÍCIO")."</td>";
print "</center>";
print "</tr>";
print "</table>";

print "<br>";

print "<table>";
print "<tr>";
print "<td> </td>";
print "</tr>";
print "</table>";

print "<table border='1' cellpadding='4'>";
print "<tr style='text-align:left'>";
print "<td colspan=6>". utf8_decode("Órgão: " .$instituicao)."</td>";
print "<td colspan=3>".utf8_decode("Município: " .$municipio)."</td>";
print "<td colspan=2>". utf8_decode("Exercício: " . $exercicio) ."</td>";
print "</tr>";
print "</table>";


print "<table border='1' cellpadding='4'>";
print "<tr style='text-align:center'>";
print "<td rowspan='4' colspan='2'>Tipo</td>";
print "<center>";
print "<td rowspan='3' colspan='2'>Valor ".utf8_decode("Líquido")." Inicial no Ano<br />Correspondente ao Registro<br />".utf8_decode("Contábil")." (R$)</td>";

print "<td colspan='4'>".utf8_decode("Movimentação do Período")." no Ano</td>";
print "<td rowspan='3' colspan='3'>Valor ".utf8_decode("Líquido")." Final no Ano<br />Correspondente ao<br />Registro ".utf8_decode("Contábil")."<br />(R$)</td>";
print "</center>";
print "</tr>";


print "<tr style='text-align:center'>";
print "<center>";
print "<td colspan='2'>Entradas (R$)</td>";
print "<td colspan='2'>Sa&iacute;das (R$)</td>";
print "</tr>";
print "<tr style='text-align:center'>";
print "<td>Aquisi&ccedil;&otilde;es</td>";
print "<td>Reavalia&ccedil;&otilde;es</td>";
print "<td>Baixas</td>";
print "<td>Reavalia&ccedil;&otilde;es</td>";
print "</tr>";
print "<tr style='text-align:center'>";
print "<td colspan='2'>(A)</td>";
print "<td>(B)</td>";
print "<td>(C)</td>";
print "<td>(D)</td>";
print "<td>(E)</td>";
print "<td colspan='3'>(F = A+B+C-D-E)</td>";
print "</center>";
print "</tr>";

print "<tr>";
print "<td colspan=2>Bens M&oacute;veis</td>";
print "<td colspan='2' align='right'>".formatNumero($bens_mov_ini, 2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_reav_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_baixa,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_reav_baixa,2)."</td>";
print "<td colspan='3' align='right'>".formatNumero($bens_mov_fin,2)."</td>";
print "</tr>";    
    
print "<tr>";
print "<td colspan='2'>Bens Im&oacute;veis</td>";
print "<td colspan='2' align='right'>".formatNumero($bens_imov_ini,2)."</td>";
print "<td align='right'>".formatNumero($bens_imov_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_imov_reav_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_imov_baixa,2)."</td>";
print "<td align='right'>".formatNumero($bens_imov_reav_baixa,2)."</td>";
print "<td colspan='3' align='right'>".formatNumero($bens_imov_fin,2)."</td>";
print "</tr>";
    
print "<tr>";
print "<td colspan='2'>Total</td>";
print "<td colspan='2' align='right'>".formatNumero($bens_mov_ini+$bens_imov_ini,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_aqui+$bens_imov_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_reav_aqui+$bens_imov_reav_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_baixa+$bens_imov_baixa,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_reav_baixa+$bens_imov_reav_baixa,2)."</td>";
print "<td colspan='3' align='right'>".formatNumero($bens_mov_fin+$bens_imov_fin,2)."</td>";

print "</tr>";
print "</table>";


print "<br>";

print "<table>";
print "<tr>";
print "<td> </td>";
print "</tr>";
print "</table>";

print "<table border='1' cellpadding='4'>";
print "<tr>";
print "<td colspan='7'>Nome: ".utf8_decode($nome_responsavel)."</td>";
print "<center>";
print "<td colspan='4' align='center'>Respons&aacute;vel pelos Bens Patrimoniais</td>";
print "</center>";
print "</tr>";

print "<tr>";
print "<td colspan='2'>Matr&iacute;cula: ".$matricula."</td>";
print "<td colspan='2'>Data: ".date('d/m/Y')."</td>";
print "<td colspan='7'>Assinatura:</td>";
print "</tr>";

print "<tr>";
print "<td width='60%'' colspan='7'>Declaro que os valores acima descritos guardam paridade com o constante nos registros cont&aacute;beis OU n&atilde;o <br>guardam paridade com o constante nos registros </td>";
print "<td width='20%' rowspan='2' colspan='2' align='center'>Respons&aacute;vel pelo<br />Setor Cont&aacute;bil</td>";
print "<td width='20%' rowspan='2' colspan='2' align='center'>CRC/RJ n&ordm; _____</td>";
print "</tr>";

print "<tr>";
print "<td colspan='7'>Nome:</td>";
print "</tr>";

print "<tr>";
print "<td colspan='2'>Matr&iacute;cula:</td>";
print "<td colspan='2'>Data: __/__/____</td>";
print "<td colspan='7'>Assinatura:</td>";
print "</tr>";
print "</table>";



/*
$html3 = '
    <table border="1" cellpadding="4">
    <tr>
        <td colspan="3">Nome: '.$nome_responsavel.'</td>
        <td colspan="2" align="center">Responsável pelos Bens Patrimoniais</td>
    </tr>
    <tr>
        <td>Matrícula: '.$matricula.'</td>
        <td>Data: '.date('d/m/Y').'</td>
        <td colspan="3">Assinatura:</td>
    </tr>
    <tr>
        <td width="60%" colspan="3">Declaro que os valores acima descritos guardam paridade com o constante nos registros contábeis OU não guardam paridade com o constante nos registros </td>
        <td width="20%" rowspan="2" align="center">Responsável pelo<br />Setor Contábil</td>
        <td width="20%" rowspan="2" align="center">CRC/RJ nº _____</td>
    </tr>
    <tr>
        <td colspan="3">Nome:</td>
    </tr>
    <tr>
        <td>Matrícula:</td>
        <td>Data: __/__/____</td>
        <td colspan="3">Assinatura:</td>
    </tr>
    </table>';
    
    print $html3;
*/


/*

foreach ($dados as $linha){  
$data = implode("/", array_reverse(explode("-", $linha->t52_dtaqu)));
$valor = number_format($linha->t52_valaqu, 2, '.', '');

$conferebaixa = confereBaixa($pdo, $linha->t52_bem);
if($conferebaixa){
    $baixa = "INATIVO";
} else {
    $baixa = "ATIVO";
}

  print "<tr>";
    print "<td>" . $linha->t52_bem . "</td>";
    print "<td>" . $linha->t52_ident . "</td>";
    print "<td colspan='5'>" . $linha->t52_descr . "</td>";
    print "<td>" . $data . "</td>";
    print "<td>" . $valor . "</td>";
    print "<td>" . $linha->t52_depart . "</td>";
    print "<td colspan='5'>" . $linha->descrdepto . "</td>";
    print "<td>" . $linha->t30_codigo . "</td>";
    print "<td colspan='5'>" . $linha->t30_descr . "</td>";
    print "<td colspan='5'>" . $linha->t52_obs . "</td>";
    print "<td colspan='5'>" . $baixa. "</td>";
  print "</tr>";
    
  }

    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('F1', 'TRIBUNAL DE CONTAS DO ESTADO DO RIO DE JANEIRO');
    $sheet->setCellValue('F3', 'RELAÇÃO DE DOCUMENTOS -ARTIGO 12 DA DELIBERAÇÃO TCE-RJ  N. 277/17');
    $sheet->setCellValue('C4', 'DEMONSTRATIVO DA MOVIMENTAÇÃO DOS BENS PATRIMONIAIS NO EXERCÍCIO');

    $sheet->setCellValue('A7', 'Orgão:');
    $sheet->setCellValue('B7', $instituicao);
    $sheet->setCellValue('J7', 'Município:');
    $sheet->setCellValue('L7', $municipio);
    $sheet->setCellValue('N7', 'Exercício:');
    $sheet->setCellValue('O7', $exercicio);

    $sheet->setCellValue('A10', 'Tipo');
    $sheet->setCellValue('D10', 'Valor Líquido Inicial no Ano');
    $sheet->setCellValue('J10', 'Movimentação do Período no Ano');
    $sheet->setCellValue('Q10', 'Saldo em');

    $sheet->setCellValue('D11', 'Correspondente ao Registro Contábil (R$)');
    $sheet->setCellValue('I11', 'ENTRADAS (R$)');
    $sheet->setCellValue('N11', 'SAÍDAS (R$)');

    $sheet->setCellValue('E14', '(A)');
    $sheet->setCellValue('H14', '(B)');
    $sheet->setCellValue('I14', '(C)');
    $sheet->setCellValue('L14', '(D)');
    $sheet->setCellValue('N14', '(E)');
    $sheet->setCellValue('P14', '(F = A+B+C-D-E)');

    $sheet->setCellValue('A18', 'MOVEIS');
    $sheet->setCellValue('E18', formatNumero($bens_mov_ini,2));
    $sheet->setCellValue('H18', formatNumero($bens_mov_aqui,2));
    $sheet->setCellValue('I18', formatNumero($bens_mov_reav_aqui,2));
    $sheet->setCellValue('L18', formatNumero($bens_mov_baixa,2));
    $sheet->setCellValue('N18', formatNumero($bens_mov_reav_baixa,2));
    $sheet->setCellValue('P18', formatNumero($bens_mov_fin,2));

    $sheet->setCellValue('A19', 'IMOVEIS');
    $sheet->setCellValue('E19', formatNumero($bens_imov_ini,2));
    $sheet->setCellValue('H19', formatNumero($bens_imov_aqui,2));
    $sheet->setCellValue('I19', formatNumero($bens_imov_reav_aqui,2));
    $sheet->setCellValue('L19', formatNumero($bens_imov_baixa,2));
    $sheet->setCellValue('N19', formatNumero($bens_imov_reav_baixa,2));
    $sheet->setCellValue('P19', formatNumero($bens_imov_fin,2));

    $sheet->setCellValue('A20', 'TOTAL');
    $sheet->setCellValue('E20', formatNumero($bens_imov_ini+$bens_mov_ini,2));
    $sheet->setCellValue('H20', formatNumero($bens_imov_aqui+$bens_mov_aqui,2));
    $sheet->setCellValue('I20', formatNumero($bens_imov_reav_aqui+$bens_mov_reav_aqui,2));
    $sheet->setCellValue('L20', formatNumero($bens_imov_baixa+$bens_mov_baixa,2));
    $sheet->setCellValue('N20', formatNumero($bens_imov_reav_baixa+$bens_mov_reav_baixa,2));
    $sheet->setCellValue('P20', formatNumero($bens_imov_fin+$bens_mov_fin,2));

    $sheet->setCellValue('A28', 'Nome:');
    $sheet->setCellValue('B28', $nome_responsavel);
    $sheet->setCellValue('N28', 'Responsável pelos bens patrimoniais');

    $sheet->setCellValue('A30', 'Matricula:');
    $sheet->setCellValue('B30', $matricula);
    $sheet->setCellValue('G30', 'Data:');
    $sheet->setCellValue('H30', date('d/m/Y'));
    $sheet->setCellValue('I30', 'Assinatura:');

    $sheet->setCellValue('A32', 'Declaro que os valores acima descritos guardam paridade com o constante nos registros contábeis OU não guardam paridade ');
    $sheet->setCellValue('A33', 'com o constante  nos registros.');
    $sheet->setCellValue('M33', 'Responsável pelo valor Contábil');
    $sheet->setCellValue('P33', 'CRC-RJ n. _________');

    $sheet->setCellValue('A35', 'Nome:');
    $sheet->setCellValue('A37', 'Matricula:');
    $sheet->setCellValue('B37', '');
    $sheet->setCellValue('G37', 'Data:');
    $sheet->setCellValue('H37', '___/____/______');
    $sheet->setCellValue('I37', 'Assinatura:');



    $writer = new Xlsx($spreadsheet);
    $writer->save('xsl_modeloxxvi.xlsx');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="xsl_modeloxxvi.xlsx"');
    $writer->save("php://output");

    */

}
