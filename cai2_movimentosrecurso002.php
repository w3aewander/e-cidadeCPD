<?php
/*
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
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("std/db_stdClass.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("model/caixa/relatorios/RelatorioMovimentosRecurso.model.php"));

$oGet = db_utils::postMemory($_GET);

try {

  if (empty($oGet->sDataInicial)) {
    throw new Exception("A Data Inicial do período não foi informada.");
  }

  if (empty($oGet->sDataFinal)) {
    throw new Exception("A Data Final do período não foi informada.");
  }

  $oDataInicial = new DBDate($oGet->sDataInicial);
  $oDataFinal   = new DBDate($oGet->sDataFinal);
  $iOrgao       = (int) $oGet->iOrgao;

  $oRelatorio = new RelatorioMovimentosRecurso();
  $oRelatorio->setDataInicial($oDataInicial);
  $oRelatorio->setDataFinal($oDataFinal);
  $oRelatorio->setInstituicao(db_getsession('DB_instit'));
  if (!empty($iOrgao)) {
    $oRelatorio->setOrgao($iOrgao);
  }

  $oRelatorio->emitir();
} catch (Exception $oException) {
  db_redireciona('db_erros.php?fechar=true&db_erro=' . urlencode($oException->getMessage()));
}
