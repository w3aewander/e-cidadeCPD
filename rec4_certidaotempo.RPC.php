<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2016  DBselller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */
require_once (modification("dbforms/db_funcoes.php"));
require_once (modification("libs/JSON.php"));
require_once (modification("libs/db_stdlib.php"));
require_once (modification("libs/db_utils.php"));
require_once (modification("libs/db_app.utils.php"));
require_once (modification("libs/db_conecta."."php"));
require_once (modification("libs/db_sessoes.php"));
require_once (modification("std/db_stdClass.php"));
require_once (modification("classes/db_certidaotemposervico_classe.php"));

$oDaoTempoServico = new cl_certidaotemposervico;

$oJson             = new services_json();
$oParam            = $oJson->decode(db_stdClass::db_stripTagsJson(str_replace("\\","",$_POST["json"])));

$oRetorno          = new stdClass();
$oRetorno->status  = 1;
$oRetorno->mensagem = '';
$oRetorno->erro    = false;
$sMensagem         = "";

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case 'lSalvar' :
    $oDaoTempoServico->regist    = $oParam->matricula;
    $oDaoTempoServico->orgao     = utf8_decode($oParam->orgao);
    $oDaoTempoServico->processo  = utf8_decode($oParam->processo);
    $oDaoTempoServico->texto     = utf8_decode($oParam->texto);
    $oDaoTempoServico->localdata = utf8_decode($oParam->local);
    $oDaoTempoServico->certidao  = utf8_decode($oParam->certidao);
    $oDaoTempoServico->usuario   = db_getsession('DB_id_usuario');
    $oDaoTempoServico->incluir();
    if(  $oDaoTempoServico->erro_status == '0' ){
       throw new Exception($oDaoTempoServico->erro_msg);
    }
    $oRetorno->codigo =  $oDaoTempoServico->sequencial;

    break;
    case 'lBusca':
    $lSql    = $oDaoTempoServico->sql_query_file(null,"*","sequencial desc ", " regist = $oParam->matricula");
    $lResult = $oDaoTempoServico->sql_record( $lSql );

    $oRetorno->codigo = db_utils::fieldsMemory($lResult,0)->sequencial;

      break;
  }

  db_fim_transacao(false);

} catch (Exception $e) {
  db_fim_transacao(true);
  $oRetorno->mensagem = $e->getMessage();
  $oRetorno->erro    = true;
}

$oRetorno->mensagem = urlencode($oRetorno->mensagem);
echo $oJson->encode($oRetorno);