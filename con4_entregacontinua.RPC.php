<?php
/**
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
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
use \ECidade\V3\Extension\Registry;

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_con" . "ecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_conn.php"));

$oParam             = JSON::create()->parse(str_replace("\\", "", $_POST["json"]));
$oRetorno           = new stdClass();
$oRetorno->erro     = false;
$oRetorno->mensagem = '';

$iInstituicaoSessao = db_getsession('DB_instit');
$iAnoSessao         = db_getsession('DB_anousu');

$bancoDBconn        = $_SESSION['DB_base'];
$bancoConexao       = $DB_BASE;

try {
    $sysConfig = Registry::get('app.config');

    switch ($oParam->exec) {

    case 'sincronizar':

      db_inicio_transacao();

      $oRetorno->sincronizado = false;

      $iCodigoUltimaMelhoria  = Melhoria::getUltimaMelhoriaAtualizada();
      $oUsuario = UsuarioSistemaRepository::getPorCodigo(db_getsession('DB_id_usuario'));

      $oMelhoriaService       = new MelhoriaService(new Plugin(null, 'EntregaContinua'), $sysConfig);

      $oRetorno->sincronizado = $oMelhoriaService->sincronizar($iCodigoUltimaMelhoria, $oUsuario, new DBDate(date('Y-m-d')));

      db_fim_transacao(false);

    break;

    case "carregarAtualizacoes":

      $iCodigoUltimaMelhoria  = Melhoria::getUltimaMelhoriaAtualizada();
      $oMelhoriaService       = new MelhoriaService(new Plugin(null, 'EntregaContinua'), $sysConfig);
      $oRetorno->atualizacoes = $oMelhoriaService->getAtualizacoes($iCodigoUltimaMelhoria);
      break;


    case 'atualizar':

      if ($bancoDBconn != $bancoConexao) {
          throw new Exception("O banco de dados selecionado não é o mesmo do ambiente em que você está autenticado.");
      }

      if (count($oParam->melhorias) == 0) {
          throw new Exception("Nenhuma melhoria informada para atualização.");
      }

      $oData = new DBDate(date('Y-m-d'));
      $oUsuario = UsuarioSistemaRepository::getPorCodigo(db_getsession('DB_id_usuario'));
      foreach ($oParam->melhorias as $oStdMelhoria) {
          db_inicio_transacao();

          Melhoria::salvar($oStdMelhoria->id, $oUsuario, $oData);
          $oMelhoriaService = new MelhoriaService(new Plugin(null, 'EntregaContinua'), $sysConfig);
          $oMelhoriaService->atualizar($oStdMelhoria);
          db_fim_transacao(false);
      }
      $oRetorno->mensagem = "Melhorias atualizadas com sucesso.";

      break;

    case 'agendar':

      $oMelhoriaService = new MelhoriaService(new Plugin(null, 'EntregaContinua'), $sysConfig);
      $oMelhoriaService->agendar($oParam->aHorarios);

      $oRetorno->mensagem = 'Agendamento realizado com sucesso.';

      break;

    case 'getHorariosAgendados':

      $oMelhoriaService = new MelhoriaService(new Plugin(null, 'EntregaContinua'), $sysConfig);
      $oRetorno->aHorarios = $oMelhoriaService->getHorariosAgendados();

      break;
  }
} catch (Exception $e) {
    db_fim_transacao(true);
    $oRetorno->erro     = true;
    $oRetorno->mensagem = $e->getMessage();
}
echo JSON::create()->stringify($oRetorno);
