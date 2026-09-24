<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2012  DBselller Servicos de Informatica             
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

/**
 * Carregamos as bibliotecas nescessárias
 */
require_once("libs/db_stdlib.php");
require_once("libs/db_utils.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");
require_once("model/contabilidade/EventoContabil.model.php");
require_once("model/contabilidade/EventoContabilLancamento.model.php");
require_once("model/contabilidade/RegraLancamentoContabil.model.php");

/**
 * Verificamos se o arquivo é de extensão xml e enviamos o mesmo para o tmp.
 * Caso houver um problema em algum desses processos redirecionamos para a página anterior
 * e informamos o ocorrido. 
 */
if ($_FILES['arquivoTransacoes']['type'] != 'text/xml') {
  $sErrorMessage = "O arquivo enviado não é do formato correto. Favor verificar o arquivo.";
  db_redireciona("con1_importartransacao001.php?lErro=true&sErrorMessage=".$sErrorMessage);
}
if (!move_uploaded_file($_FILES['arquivoTransacoes']['tmp_name'], "/tmp/importacaotransacao{$_FILES['arquivoTransacoes']['name']}")) { 
  $sErrorMessage = "Ocorreu um erro na importação do arquivo de implantação. Favor verificar o arquivo.";
  db_redireciona("con1_importartransacao001.php?lErro=true&sErrorMessage=".$sErrorMessage);
}


/**
 * Verifica existência do documento passado na assinatuta na tabela "conhistdoc"
 * @param integer $iCodigoDocumento
 * @return boolean
 */
function existeDocumentoTransacao($iCodigoDocumento) {
  
  $oDaoConhistdoc     = db_utils::getDao('conhistdoc');
  $sSqlBuscaDocumento = $oDaoConhistdoc->sql_query_file($iCodigoDocumento);
  $rsBuscaDocumento   = $oDaoConhistdoc->sql_record($sSqlBuscaDocumento);
  if ($oDaoConhistdoc->numrows > 0) {
    return true;
  }
  return false;
}

/**
 * Verifica existência da transação para para o documento informado e o ano e intituição logadas
 * @param integer $iCodigoDocumento
 * @return boolean
 */
function existeTransacaoParaDocumento($iCodigoDocumento) {
  
  $oDaoContrans          = db_utils::getDao('contrans');
  $iAnousu               = db_getsession("DB_anousu");
  $iInstituicao          = db_getsession("DB_instit");
  $sWhereBuscaTransacao  = "     c45_coddoc = {$iCodigoDocumento} ";
  $sWhereBuscaTransacao .= " and c45_anousu = {$iAnousu} ";
  $sWhereBuscaTransacao .= " and c45_instit = {$iInstituicao} ";
  $sSqlBuscaTransacao    = $oDaoContrans->sql_query_file(null, "*", null, $sWhereBuscaTransacao);
  $rsBuscaTransacao      = $oDaoContrans->sql_record($sSqlBuscaTransacao);
  if ($oDaoContrans->numrows > 0) {
    return true;
  }

  return false;

}

/**
 * Verifica existência de registro na conplano para o estrutural passado na asinatura.
 * Se o mesmo for 0 retorna como se houvesse pois isso indica que a conta não possui conta 
 * de crédito/débito (alguns casos possuem apenas conta de credito ou débito) 
 * @param string $sEstrutural
 * @return boolean
*/
function existeEstrutural($sEstrutural) {
  
  if ($sEstrutural == "0") {
    return true;
  }
  $oDaoConplano          = db_utils::getDao('conplano');
  $sWhereBuscaEstrutural = " c60_estrut = '{$sEstrutural}' and c60_anousu = ".db_getsession("DB_anousu");
  $sSqlBuscaEstrutural   = $oDaoConplano->sql_query_file(null, null, "*", null, $sWhereBuscaEstrutural);
  $rsBuscaEstrutural     = $oDaoConplano->sql_record($sSqlBuscaEstrutural);
  if ($oDaoConplano->numrows > 0) {
    return true;
  }
  return false;
}

/**
 * Verifica existência de reduzido para o estrutural passado na assinatura.
 * Se o mesmo for 0 retorna como se houvesse pois isso indica que a conta não possui conta
 * de crédito/débito (alguns casos possuem apenas conta de crédito ou débito)
 * @param string $sEstrutural
 * @return boolean
 */
function existeReduzido($sEstrutural) {
  
  if ($sEstrutural == "0") {
    return true;
  }
  $oDaoConplanoReduz    = db_utils::getDao('conplanoreduz');
  $sWhereBuscaReduzido  = "     conplano.c60_estrut = '{$sEstrutural}' ";
  $sWhereBuscaReduzido .= " and conplano.c60_anousu = ".db_getsession("DB_anousu") ;
  $sWhereBuscaReduzido .= " and conplanoreduz.c61_reduz is not null and c61_instit = ".db_getsession("DB_instit");
  $sSqlBuscaReduzido    = $oDaoConplanoReduz->sql_query(null, null, "*", null, $sWhereBuscaReduzido);
  $rsSqlBuscaReduzido   = $oDaoConplanoReduz->sql_record($sSqlBuscaReduzido);
  if ($oDaoConplanoReduz->numrows > 0) {
    return true;
  }
  return false;
}

/**
* Buscamos o reduzido através do estrutural da conta
* @param string $sEstrutural
*/
function getReduzidoFromEstrutural($sEstrutural) {

  if ($sEstrutural == "0") {
    return '0';
  }
  $oDaoConplanoReduz   = db_utils::getDao('conplanoreduz');
  $iAnoUsu             = db_getsession("DB_anousu");
  $sWhereBuscaReduzido = " conplano.c60_estrut = '{$sEstrutural}' and conplanoreduz.c61_anousu = {$iAnoUsu} and c61_instit = ".db_getsession("DB_instit");
  $sSqlBuscaReduzido   = $oDaoConplanoReduz->sql_query(null, null, " conplanoreduz.c61_reduz ",
  null, $sWhereBuscaReduzido);
  $rsBuscaReduzido     = $oDaoConplanoReduz->sql_record($sSqlBuscaReduzido);
  if ($oDaoConplanoReduz->numrows > 0) {

    $oBuscaReduzido = db_utils::fieldsMemory($rsBuscaReduzido, 0);
    return $oBuscaReduzido->c61_reduz;
  }
  return null;
}

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

/**
 * Carregamos o arquivo de importação de transações na classe DOMDocument e após isso
 * percorremos o mesmo e montamos uma collection com as informações do mesmo
 */
$oDomXML = new DomDocument();
$oDomXML->load('/tmp/importacaotransacao'.$_FILES["arquivoTransacoes"]['name']);
$aTransacoes = array();
$oTransacoes = $oDomXML->getElementsByTagName('transacao');

//$aCoddoc = array(31,32,33,34,35,36,37,38);
/*$aCoddoc = array(2001,2003);
$aCoddoc = array(1,2,3,4,5,6);
$aCoddoc = array(3,4,5,6,23,24,31,32,33,34,35,36,37,38);
$aCoddoc = array(1,2,3,4,5,6,23,24,100,101,410,411,412,413);

$aCoddoc = array(1,2,3,4,5,6,23,24,100,101,120,121,130,131,140,141,160,161,162,163,200,201,204,205,206,207,208,209,212,213,400,401,402,403,404,410,411,412,413,414,415,416,417,418,419,701);
*/
$aCoddoc = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 23, 24, 25, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 71, 72, 73, 80, 81, 82, 83, 84, 85, 90, 91, 92, 100, 101, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 120, 121, 130, 131, 140, 141, 150, 151, 152, 153, 160, 161, 162, 163, 200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 300, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311, 333, 334, 400, 401, 402, 403, 404, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 510, 511, 512, 513, 514, 600, 601, 602, 603, 604, 605, 700, 701, 702, 703, 704, 800, 801, 802, 803, 804, 805, 806, 807, 808, 900, 901, 903, 904, 1000, 1001, 1002, 1003, 1004, 1005, 1006, 1007, 1008, 1009, 1010, 1999, 2000, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020, 3000, 4000, 4001, 4002, 4003, 4010);



//var_dump($aCoddoc); die("Coddocs");

foreach ($oTransacoes as $oTransacao) {
  
  
  if ( ! ( in_array( $oTransacao->getAttribute('c45_coddoc'), $aCoddoc ) ) ) {
    continue;
  }

  $oTransacaoAtual = new stdClass();
  $oTransacaoAtual->c45_seqtrans = $oTransacao->getAttribute('c45_seqtrans');
  $oTransacaoAtual->c45_anousu   = $oTransacao->getAttribute('c45_anousu');
  $oTransacaoAtual->c45_coddoc   = $oTransacao->getAttribute('c45_coddoc');
  $oTransacaoAtual->c53_descr    = trim(utf8_decode($oTransacao->getAttribute('c53_descr')));
  $oTransacaoAtual->c45_instit   = $oTransacao->getAttribute('c45_instit');
  
  
  $oTransacaoAtual->aLancamentos = array(); 
  foreach ($oTransacao->getElementsByTagName('lancamento') as $oLancamento) {

    $oLancamentoAtual = new stdClass();
    $oLancamentoAtual->c46_seqtranslan = $oLancamento->getAttribute('c46_seqtranslan');                
    $oLancamentoAtual->c46_seqtrans    = $oLancamento->getAttribute('c46_seqtrans');                    
    $oLancamentoAtual->c46_codhist     = $oLancamento->getAttribute('c46_codhist');                     
    $oLancamentoAtual->c46_obs         = trim(utf8_decode($oLancamento->getAttribute('c46_obs')));      
    $oLancamentoAtual->c46_valor       = trim(utf8_decode($oLancamento->getAttribute('c46_valor')));    
    $oLancamentoAtual->c46_obrigatorio = $oLancamento->getAttribute('c46_obrigatorio');                 
    $oLancamentoAtual->c46_evento      = $oLancamento->getAttribute('c46_evento');                      
    $oLancamentoAtual->c46_descricao   = trim(utf8_decode($oLancamento->getAttribute('c46_descricao')));
    $oLancamentoAtual->c46_ordem       = $oLancamento->getAttribute('c46_ordem');
    
    $oLancamentoAtual->aContas = array();
    foreach ($oLancamento->getElementsByTagName('conta') as $oConta) {

//        echo trim(utf8_decode($oConta->getAttribute('c47_debito'))) . "<br>";
//        echo trim(utf8_decode($oConta->getAttribute('c47_credito')));
//        exit;

        $lPassa=1;
        if (!existeEstrutural(trim(utf8_decode($oConta->getAttribute('c47_debito'))))) {
          $lPassa=0;
        } else if (!existeReduzido(trim(utf8_decode($oConta->getAttribute('c47_debito'))))) {
          $lPassa=0;
        }
        if (!existeEstrutural(trim(utf8_decode($oConta->getAttribute('c47_credito'))))) {
          $lPassa=0;
        } else if (!existeReduzido(trim(utf8_decode($oConta->getAttribute('c47_credito'))))) {
          $lPassa=0;
        }
        
        if ($lPassa == 1) {
	  $oContaAtual = new stdClass();
	  $oContaAtual->c47_seqtranslr        = $oConta->getAttribute('c47_seqtranslr');                 
	  $oContaAtual->c47_seqtranslan       = $oConta->getAttribute('c47_seqtranslan');                
	  $oContaAtual->c47_debito            = trim(utf8_decode($oConta->getAttribute('c47_debito')));
	  $oContaAtual->c47_debito_descricao  = trim(utf8_decode($oConta->getAttribute('c47_debito_descricao')));
	  $oContaAtual->c47_credito           = trim(utf8_decode($oConta->getAttribute('c47_credito')));
	  $oContaAtual->c47_credito_descricao = trim(utf8_decode($oConta->getAttribute('c47_credito_descricao')));
	  $oContaAtual->c47_obs               = trim(utf8_decode($oConta->getAttribute('c47_obs')));     
	  $oContaAtual->c47_ref               = trim(utf8_decode($oConta->getAttribute('c47_ref')));     
	  $oContaAtual->c47_anousu            = $oConta->getAttribute('c47_anousu');                    
	  $oContaAtual->c47_instit            = $oConta->getAttribute('c47_instit');                     
	  $oContaAtual->c47_compara           = trim(utf8_decode($oConta->getAttribute('c47_compara'))); 
	  $oContaAtual->c47_tiporesto         = trim(utf8_decode($oConta->getAttribute('c47_tiporesto')));
	  $oLancamentoAtual->aContas[]        = $oContaAtual;
      }
    }
    $oTransacaoAtual->aLancamentos[] = $oLancamentoAtual;
  }
  $aTransacoes[] = $oTransacaoAtual;
  
}



//testa($aTransacoes);
//die("Confere 1");

//exit;

/**
 * Iniciamos o pré-processamento para avaliar possíveis erros na importação de transações.
 * Todos os erros encontrados nesse processo são enviados para o array de erros
 */
$iIstituicaoDestino  = db_getsession("DB_instit");
$iAnoUsuDestino      = db_getsession("DB_anousu");
$aLogErrosTransacoes = array();
$aLogErros           = array();

/**
 * Pré-processamento das transações
 */

try {
  
  db_inicio_transacao();

  $sDeleta = "";
  $sDeleta .= "create temp table w_instit as select " . db_getsession("DB_anousu") .  " as ano, codigo from configuracoes.db_config where codigo = " . db_getsession("DB_instit") . ";";

  $sDeleta .= "create temp table w_transacoes_codigos (codigo integer);";
  for ($i=0; $i < sizeof($aCoddoc); $i++) {
    $sDeleta .= "insert into w_transacoes_codigos values (" . $aCoddoc[$i] . ");";
  }
  $sDeleta .= "	create temp table w_transacoes as 
		select * 
		from contabilidade.contranslr 
		inner join contabilidade.contranslan on c46_seqtranslan = c47_seqtranslan 
		inner join contabilidade.contrans on c45_seqtrans = c46_seqtrans 
		where c45_instit in ( select codigo from w_instit ) and c45_anousu = ( select ano from w_instit );";

   $sDeleta .= "delete from contabilidade.contranslrvinculo using contabilidade.contranslr, contabilidade.contranslan, contabilidade.contrans where c47_seqtranslr = c116_contranslrestorno and c46_seqtranslan = c47_seqtranslan and c45_seqtrans = c46_seqtrans and c45_instit in ( select codigo from w_instit ) and c45_anousu = ( select ano from w_instit ) and c45_coddoc in ( select codigo from w_transacoes_codigos );";

   $sDeleta .= "delete from contabilidade.contranslrvinculo using contabilidade.contranslr, contabilidade.contranslan, contabilidade.contrans where c47_seqtranslr = c116_contranslrinclusao and c46_seqtranslan = c47_seqtranslan and c45_seqtrans = c46_seqtrans and c45_instit in ( select codigo from w_instit ) and c45_anousu = ( select ano from w_instit ) and c45_coddoc in ( select codigo from w_transacoes_codigos );";

   $sDeleta .= "delete from contabilidade.contranslrelemento using contabilidade.contranslr, contabilidade.contranslan, contabilidade.contrans where c47_seqtranslr = c114_contranslr and c46_seqtranslan = c47_seqtranslan and c45_seqtrans = c46_seqtrans and c45_instit in ( select codigo from w_instit ) and c45_anousu = ( select ano from w_instit ) and c45_coddoc in ( select codigo from w_transacoes_codigos );";

   $sDeleta .= "delete from contabilidade.contranslr using contabilidade.contranslan, contabilidade.contrans where c46_seqtranslan = c47_seqtranslan and c45_seqtrans = c46_seqtrans and c45_instit in ( select codigo from w_instit ) and c45_anousu = ( select ano from w_instit ) and c45_coddoc in ( select codigo from w_transacoes_codigos );";

   $sDeleta .= "delete from contabilidade.contranslan using contabilidade.contrans where c45_seqtrans = c46_seqtrans and c45_instit in ( select codigo from w_instit ) and c45_anousu = ( select ano from w_instit ) and c45_coddoc in ( select codigo from w_transacoes_codigos );";

   $sDeleta .= "delete from contabilidade.contrans where c45_instit in ( select codigo from w_instit ) and c45_anousu = ( select ano from w_instit )  and c45_coddoc in ( select codigo from w_transacoes_codigos );";

//  die("x: $sDeleta");

  $rsDeleta = db_query($sDeleta) or die($sDeleta);

foreach ($aTransacoes as $oTransacao) {
  
  if (!existeDocumentoTransacao($oTransacao->c45_coddoc)) {
    
    $oErroTransacao = new stdClass();
    $oErroTransacao->c45_seqtrans = $oTransacao->c45_seqtrans;
    $oErroTransacao->c53_descr    = $oTransacao->c53_descr;
    $oErroTransacao->sDescricao   = "Documento não existe na tabela 'conhistdoc'.";
    $aLogErrosTransacoes[] = $oErroTransacao;
  }
  if (existeTransacaoParaDocumento($oTransacao->c45_coddoc)) {
    
    $oErroTransacao = new stdClass();
    $oErroTransacao->c45_seqtrans = $oTransacao->c45_seqtrans;
    $oErroTransacao->c53_descr    = $oTransacao->c53_descr;
    $oErroTransacao->sDescricao   = "Já existe transação para o documento com o ano e instituiçao atuais.";
    $aLogErrosTransacoes[] = $oErroTransacao;
  }
}

/**
 * Se não houverem problemas no pré-processamento das transações passamos ao pré-processamento
 * dos lançamentos e das contas.
 */
if (count($aLogErrosTransacoes) == 0) {

  foreach ($aTransacoes as $oTransacao) {
    
    foreach ($oTransacao->aLancamentos as $oLancamento) {
      
      foreach ($oLancamento->aContas as $oConta) {
        /**
         * Efetuamos as validações competentes às contas
        */

        if (!existeEstrutural($oConta->c47_debito)) {
          
          $oErro                 = new stdClass();
          $oErro->c45_coddoc     = $oTransacao->c45_coddoc;
          $oErro->c53_descr      = $oTransacao->c53_descr;
          $oErro->c46_descricao  = $oLancamento->c46_descricao;
          $oErro->c46_ordem      = $oLancamento->c46_ordem;
          $oErro->c47_seqtranslr = $oConta->c47_seqtranslr;
          $oErro->c47_debito     = $oConta->c47_debito;
          $oErro->c47_credito    = $oConta->c47_credito;
          $oErro->sDescricao     = "A conta de débito não existe na tabela 'conplano'.";
          $aLogErros[] = $oErro;
        } else if (!existeReduzido($oConta->c47_debito)) {
          
          $oErro                 = new stdClass();
          $oErro->c45_coddoc     = $oTransacao->c45_coddoc;
          $oErro->c53_descr      = $oTransacao->c53_descr;
          $oErro->c46_descricao  = $oLancamento->c46_descricao;
          $oErro->c46_ordem      = $oLancamento->c46_ordem;
          $oErro->c47_seqtranslr = $oConta->c47_seqtranslr;
          $oErro->c47_debito     = $oConta->c47_debito;
          $oErro->c47_credito    = $oConta->c47_credito;
          $oErro->sDescricao     = "A conta de débito não existe não possui reduzido.";
          $aLogErros[] = $oErro;
        }
        if (!existeEstrutural($oConta->c47_credito)) {
          
          $oErro                 = new stdClass();
          $oErro->c45_coddoc     = $oTransacao->c45_coddoc;
          $oErro->c53_descr      = $oTransacao->c53_descr;
          $oErro->c46_descricao  = $oLancamento->c46_descricao;
          $oErro->c46_ordem      = $oLancamento->c46_ordem;
          $oErro->c47_seqtranslr = $oConta->c47_seqtranslr;
          $oErro->c47_debito     = $oConta->c47_debito;
          $oErro->c47_credito    = $oConta->c47_credito;
          $oErro->sDescricao     = "A conta de crétido não existe na tabela 'conplano'.";
          $aLogErros[] = $oErro;
        } else if (!existeReduzido($oConta->c47_credito)) {
          
          $oErro                 = new stdClass();
          $oErro->c45_coddoc     = $oTransacao->c45_coddoc;
          $oErro->c53_descr      = $oTransacao->c53_descr;
          $oErro->c46_descricao  = $oLancamento->c46_descricao;
          $oErro->c46_ordem      = $oLancamento->c46_ordem;
          $oErro->c47_seqtranslr = $oConta->c47_seqtranslr;
          $oErro->c47_debito     = $oConta->c47_debito;
          $oErro->c47_credito    = $oConta->c47_credito;
          $oErro->sDescricao     = "A conta de crédito não existe não possui reduzido.";
          $aLogErros[] = $oErro;
        }

      }
    }
  }
  
  if (count($aLogErros) > 0) {   
    $_SESSION['aConflitoLancamento'] = $aLogErros;
    $_SESSION['sMsgConflitoTransacaoLancamento'] = "Houve conflito entre lançamentos. Confira no relatório que será emitido a seguir.";
    db_redireciona("con1_importartransacao001.php?lRelatorio=true&sUrlRelatorio=con2_lancamentosemconflito002.php");
  }
  //print_r($aLogErros);
} else {
  $_SESSION['aConflitoTransacoes']   = $aLogErrosTransacoes;
  $_SESSION['sMsgConflitoTransacaoLancamento'] = "Houve conflito entre transações. Confira no relatório que será emitido a seguir.";
  db_redireciona("con1_importartransacao001.php?lRelatorio=true&sUrlRelatorio=con2_transacoesemconflito002.php");
}

/**
 * Aqui encerramos o pré-processamento e iniciamos a importação das inclusões
 */

  foreach ($aTransacoes as $oTransacao) {
    
    /**
     * Setamos as informações de transação
     */
    $oEventoContabil = new EventoContabil();
    $oEventoContabil->setCodigoDocumento($oTransacao->c45_coddoc);
    $oEventoContabil->setInstituicao(db_getsession("DB_instit"));
    $oEventoContabil->setAnoUso(db_getsession("DB_anousu"));
    $oEventoContabil->salvar();
    foreach ($oTransacao->aLancamentos as $oLancamento) {
      /**
       * Setamos as informações de lançamento
      */

      $oEventoContabilLancamento = new EventoContabilLancamento();
      $oEventoContabilLancamento->setHistorico($oLancamento->c46_codhist);
      $oEventoContabilLancamento->setObservacao($oLancamento->c46_obs);
      $oEventoContabilLancamento->setValor($oLancamento->c46_valor);
      $oEventoContabilLancamento->setObrigatorio($oLancamento->c46_obrigatorio);
      $oEventoContabilLancamento->setEvento($oLancamento->c46_evento);
      $oEventoContabilLancamento->setDescricao($oLancamento->c46_descricao);
      $oEventoContabilLancamento->setOrdem($oLancamento->c46_ordem);
      $oEventoContabilLancamento->setSequencialTransacao($oEventoContabil->getSequencialTransacao());
      $oEventoContabilLancamento->salvar();
      
      foreach ($oLancamento->aContas as $oConta) {
	$oRegraLancamentoContabil = new RegraLancamentoContabil();
	$oRegraLancamentoContabil->setSequencialLancamento($oEventoContabilLancamento->getSequencialLancamento());
	$oRegraLancamentoContabil->setContaDebito(getReduzidoFromEstrutural($oConta->c47_debito));
	$oRegraLancamentoContabil->setContaCredito(getReduzidoFromEstrutural($oConta->c47_credito));
	$oRegraLancamentoContabil->setObservacao(str_replace("\\", "", $oConta->c47_obs));
	$oRegraLancamentoContabil->setReferencia($oConta->c47_ref);
	$oRegraLancamentoContabil->setAnoUso($oConta->c47_anousu);
	$oRegraLancamentoContabil->setInstituicao(db_getsession("DB_instit"));
	$oRegraLancamentoContabil->setCompara($oConta->c47_compara);
	$oRegraLancamentoContabil->setTipoResto($oConta->c47_tiporesto);
	$oRegraLancamentoContabil->salvar();
      }
    }
    unset($oRegraLancamentoContabil);
    unset($oEventoContabilLancamento);
    unset($oEventoContabil);
  }
  db_fim_transacao(false);
  $sMessage = "Importação das transações realizada com sucesso!";
  $_SESSION['sMsgConflitoTransacaoLancamento'] = $sMessage;
  db_redireciona('con1_importartransacao001.php?lErro=true');
} catch (Exception $eException) {
  
  db_fim_transacao(true);
  $_SESSION['sMsgConflitoTransacaoLancamento'] = $eException->getMessage();
  db_redireciona('con1_importartransacao001.php?lErro=true');
}
?>
