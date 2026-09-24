<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_con"."ecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("std/db_stdClass.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("dbforms/db_classesgenericas.php"));

db_postmemory($_POST);

$sItensProcessa = substr(str_replace("_",",",$itensprocessa),1);
$iAnoDestino    = (int)$anodestino;
$iAnoOrigem     = (int)$anodestino - 1;
$oRetorno       = new stdClass();
$oRetorno->erro = false;
  
try {

  db_inicio_transacao();
  $sSQL  =""; 
  $sSQL  ="  select fc_putsession('DB_instit','1');                                                           ";
  $sSQL .="  select fc_putsession('DB_anousu','".$iAnoDestino."');                                            ";
  $sSQL .="  select fc_putsession('DB_datausu',current_date::text);                                           ";
  $sSQL .="  select fc_putsession('DB_id_usuario','1');                                                       ";
  $sSQL .="  select fc_putsession('DB_use_pcasp','true');                                                     ";
  $sSQL .="  drop table if exists w_db_virada;                                                                ";
  $sSQL .="  create temp table w_db_virada as                                                                 ";
  $sSQL .="  select nextval('db_virada_c30_sequencial_seq') as c30_sequencial,                                ";
  $sSQL .="         {$iAnoOrigem}  as c30_anoorigem,                                                          ";
  $sSQL .="         {$iAnoDestino} as c30_anodestino,                                                         ";
  $sSQL .="         1 as c30_usuario,                                                                         ";
  $sSQL .="         (select fc_getsession('DB_datausu'))::date as c30_data,                                   ";
  $sSQL .="         (EXTRACT(HOUR FROM current_time) || ':' || EXTRACT(MINUTE FROM current_time)) as c30_hora,";
  $sSQL .="         1 as c30_situacao;                                                                        ";
  $sSQL .="  insert into db_virada select * from w_db_virada ;                                                ";
  $sSQL .="  insert into db_viradaitem                                                                        ";
  $sSQL .="  select nextval('db_viradaitem_c31_sequencial_seq'),                                              ";
  $sSQL .="          (select c30_sequencial from w_db_virada),                                                ";
  $sSQL .="          c33_sequencial,                                                                          ";
  $sSQL .="          1                                                                                        ";
  $sSQL .="     from db_viradacaditem                                                                         ";
  $sSQL .="    where c33_sequencial in({$sItensProcessa});                                                    ";
  $oRetorno->retornoSQL = db_query($sSQL);
  
  db_fim_transacao(false);

} catch (Exception $e) {
    $oRetorno->erro = true;
    $oRetorno->mensagem = $e->getMessage();
    db_fim_transacao(true);
}

db_redireciona('PluginViradaAnualView.php?anodestino='.$iAnoDestino);

?>