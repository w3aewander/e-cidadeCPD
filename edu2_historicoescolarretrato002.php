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

require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("fpdf151/FpdfMultiCellBorder.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("libs/db_libparagrafo.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification('model/educacao/ArredondamentoNota.model.php'));

$oGet = db_utils::postMemory($_GET);

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}


if(empty($oGet->sDiretor)){
  $oGet->sDiretor = "DIRETOR - ";
 }

if(empty($oGet->sSecretario)){
  $oGet->sSecretario = "SECRETÁRIO - ";
}


//CÃ³digos das disciplinas
//select ed232_i_codigo,  ed232_c_descr from caddisciplina;



$aAlunosSelecionados  = explode(",", $oGet->alunos);
$lExibirReclassificao = $oGet->sExibirReclassificacao == 't' ? true : false;

$oFpdf = new FpdfMultiCellBorder("P");
$oFpdf->Open();
$oFpdf->AliasNbPages();
$oFpdf->SetMargins(8, 10);

try {

  foreach ( $aAlunosSelecionados as $iCodigoAluno ) {

    $oAluno            = AlunoRepository::getAlunoByCodigo($iCodigoAluno);
    
    $oRelatorioEscolar = new RelatorioHistoricoEscolarRetrato(
                                                               $oFpdf,
                                                               $oAluno,
                                                               EscolaRepository::getEscolaByCodigo($oGet->iEscola),
                                                               $oGet->iTipoRelatorio,
                                                               $lExibirReclassificao
                                                             );
    
    
    $oRelatorioEscolar->setTipoRegistro($oGet->iTipoRegistro);    
    $oRelatorioEscolar->montarEstruturaDeDados();
    $oRelatorioEscolar->setDisposicao($oGet->sDisposicao);
    $oRelatorioEscolar->setExibirTodasEtapasCurso( $oGet->iExibirTodasEtapas == 2 );
    
    $oRelatorioEscolar->escreveCabecalho();
    $oRelatorioEscolar->criarTabelaComponentesCurriculares(); //AQUI
    $oRelatorioEscolar->criarTabelaResumoEtapas();
    $oRelatorioEscolar->montaQuadroObservacao();
    $oRelatorioEscolar->escreverRodape($oGet->sDiretor, $oGet->sSecretario);
    /*if($oGet->iTipoRelatorio == 8){      
      $oRelatorioEscolar->criarTabelaComponentesCurriculares(2);
      $oRelatorioEscolar->criarTabelaResumoEtapas();
      $oRelatorioEscolar->montaQuadroObservacao();
      $oRelatorioEscolar->escreverRodape($oGet->sDiretor, $oGet->sSecretario);
    }*/ 
    
    
    unset($oRelatorioEscolar);
    AlunoRepository::removerAluno($oAluno);
  }
  //die("Confere todos");;
  $oFpdf->Output();
} catch(Exception $oErro) {

  $sMsg = urlencode($oErro->getMessage());
  db_redireciona('db_erros.php?fechar=true&db_erro='.$sMsg);
}