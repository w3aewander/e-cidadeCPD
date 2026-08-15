<?php
ini_set('max_execution_time', 0); // 0 = Unlimited
ini_set("memory_limit","-1");


require_once "libs/db_stdlib.php";
require_once "libs/db_conecta.php";
require_once "libs/db_sessoes.php";
require_once "libs/db_usuariosonline.php";
require_once (modification("fpdf151/assinatura.php"));
require_once (modification("fpdf151/pdf.php"));
require_once (modification("libs/db_sql.php"));
require_once "mpdf60/mpdf.php";
require_once(modification("libs/JSON.php"));

//$oJson                  = new services_json();
//$oParam                 = $oJson->decode(str_replace("\\","",$_POST["json"]));
$ops = $_POST["ops"];//$oParam->ops;
$strempenhos  = $_POST["strempenhos"];//$oParam->strempenhos;

if(isset($strempenhos) && strlen($strempenhos) > 0) {
    $sql = "";
    if ($ops == "e")
        $sql = "update empenho.empempenho set e60_relatorio = false where e60_numemp in ({$strempenhos})";
    else
        $sql = "update empenho.empempenho set e60_relatorio = true where e60_numemp in ({$strempenhos})";

    $result = db_query($sql);
}
exit();
