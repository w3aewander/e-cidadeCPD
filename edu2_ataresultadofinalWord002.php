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

require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("std/DBDate.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("classes/db_edu_parametros_classe.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_libparagrafo.php"));
require_once(modification("model/educacao/avaliacao/iFormaObtencao.interface.php"));
require_once(modification("model/educacao/avaliacao/iElementoAvaliacao.interface.php"));
require_once(modification("model/educacao/TurmaRepository.model.php"));
require_once(modification("model/CgmFactory.model.php"));
require_once(modification("std/db_stdClass.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}
//testa($_GET);

function dadosRegencia($codregencia){  

  $sql = pg_query("SELECT * from regencia where regencia.ed59_i_codigo = {$codregencia}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

// ver o erro nesta função
function voltaFaltas($ed95_i_serie, $ed59_i_turma, $matriculaa){  
  $sql1 = pg_query("SELECT 
                    diario.*, 
					ed59_i_codigo 
					from 
					diario 
					inner join aluno on ed47_i_codigo = ed95_i_aluno 
					inner join matricula on ed60_i_aluno = ed47_i_codigo 
					inner join matriculaserie on ed60_i_codigo = ed221_i_matricula 
					inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie 
					where 
					ed60_i_codigo = {$matriculaa} 
					and 
					ed95_i_regencia = ed59_i_codigo 
					and 
					ed95_i_serie = {$ed95_i_serie} 
					and 
					ed59_i_turma = {$ed59_i_turma} 
					order by ed95_i_codigo");
  
  $r1 = pg_fetch_all($sql1);  
  $r1 = $r1[0]["ed95_i_aluno"];    
  
  $sql = pg_query("SELECT 
                   sum(ed72_i_numfaltas) as numero_faltas 
                   from 
				   diarioavaliacao 
				   inner join procavaliacao on ed41_i_codigo       = ed72_i_procavaliacao 
				   inner join diario        on ed95_i_codigo       = ed72_i_diario
				   left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo 
				   left join abonofalta on ed80_i_diarioavaliacao  = ed72_i_codigo 
				   where 
				   ed95_i_aluno = {$r1}");
  
  $resultado = pg_fetch_all($sql);
  
  return $resultado[0]["numero_faltas"];
}

function trataNome($nome){
  $tratanome = explode(" ", mb_strtolower($nome));
    $nometratado = "";
    foreach ($tratanome as $particula) {
      if(strlen($particula) > 2){
        $nometratado .= ucfirst($particula) . " ";
      }else{
        $nometratado .= $particula . " ";
      }
    }
    $nometratado = trim($nometratado);    
    $nometratado = mb_strtoupper(mb_substr($nometratado, 0, 1)).mb_substr($nometratado, 1);
    return $nometratado;
}

function buscaRFeja($matricula, $turma, $serie){
  $sql = pg_query("SELECT ed74_c_resultadofinal,ed74_c_resultadofreq FROM diario inner join aluno on ed47_i_codigo = ed95_i_aluno inner join diariofinal on ed74_i_diario = ed95_i_codigo WHERE ed95_i_aluno = {$matricula} AND ed95_c_encerrado = 'S' AND ed95_i_regencia in (select ed59_i_codigo from regencia where ed59_i_turma = {$turma} and ed59_i_serie = {$serie} and ed59_c_condicao = 'OB')");
  $resultado = pg_fetch_all($sql);
  if($resultado[0]["ed74_c_resultadofinal"] == "A"){
    $resultadofinal = "AP";
  }elseif($resultado[0]["ed74_c_resultadofinal"] == "R"){
    $resultadofinal = "REP";
  }else{
    $resultadofinal = "";
  }
  return $resultadofinal;
}

function buscaFaltasAnosIniciais($codregencia, $codaluno){
  $sql = pg_query("SELECT 
                   sum(ed72_i_numfaltas) as numero_faltas 
				   FROM 
				   diarioavaliacao 
				   INNER JOIN diario ON ed72_i_diario = ed95_i_codigo 
				   INNER JOIN regencia ON ed59_i_codigo = ed95_i_regencia 
				   WHERE 
				   ed59_i_codigo = {$codregencia} 
				   AND 
				   ed95_i_aluno = {$codaluno}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["numero_faltas"];
}



$oJson                                     = new Services_JSON();
$oGet                                      = db_utils::postMemory($_GET);

$aTurmas                                   = $oJson->decode(str_replace("\\","", $oGet->turmas));
$oFiltroRelatorio                          = new stdClass();
$oFiltroRelatorio->iModelo                 = $oGet->modelo;
$oFiltroRelatorio->iOrdenacao              = $oGet->ordenacao;
$oFiltroRelatorio->iFrequencia             = $oGet->frequencia;
$oFiltroRelatorio->iCodigoTipoModelo       = $oGet->tipovar;
$oFiltroRelatorio->iTrocaTurma             = $oGet->trocaTurma;
$oFiltroRelatorio->aDiretor                = array();
$oFiltroRelatorio->aSecretario             = array();
$oFiltroRelatorio->lTemDiretor             = false;
$oFiltroRelatorio->lTemSecretario          = false;
$oFiltroRelatorio->lBrasao                 = false;
$oFiltroRelatorio->lTransferencia          = false;
$oFiltroRelatorio->lAssinatura             = false;
$oFiltroRelatorio->aJustificativas         = array();
$lObservacaoProgressaoParcial              = false;
$oFiltroRelatorio->iTipoModelo             = 1;
$oFiltroRelatorio->mCabecalho              = '';
$oFiltroRelatorio->mRodape                 = '';
$oFiltroRelatorio->mObservacao             = '';
$oFiltroRelatorio->sObservacao             = $oGet->sObservacao;
$oFiltroRelatorio->iImprimirRegente        = $oGet->imprimirNomeRegente;
$oFiltroRelatorio->sCabecalho              = '';
$oFiltroRelatorio->iTamanhoColunaResultado = $oFiltroRelatorio->iCodigoTipoModelo == 4 ? 7 : 6;

if ($oGet->transfer == 'yes') {
  $oFiltroRelatorio->lTransferencia = true;
}

if ($oGet->brasao == 'b1') {
  $oFiltroRelatorio->lBrasao = true;
}

if (!empty($oGet->diretor)) {
  $oGet->diretor = db_stdClass::normalizeStringJsonEscapeString($oGet->diretor);  
  $oFiltroRelatorio->aDiretor    = explode("|", $oGet->diretor);
  $oFiltroRelatorio->lTemDiretor = true;
}

if (!empty($oGet->secretario)) {
  $oGet->secretario = db_stdClass::normalizeStringJsonEscapeString($oGet->secretario);
  $oGet->secretario = "Nome Secretário";
  $oFiltroRelatorio->aSecretario    = explode("|", $oGet->secretario);
  $oFiltroRelatorio->lTemSecretario = true;
}

if (!empty($oGet->iRegente)) {
  $oFiltroRelatorio->iRegente = $oGet->iRegente;
}

if (!empty($oGet->iAtividade)) {
  $oFiltroRelatorio->iAtividade = $oGet->iAtividade;
}

if (in_array($oFiltroRelatorio->iModelo, array(1, 2))) {
  require_once(modification("fpdf151educacao/pdfwebseller.php"));
} else if (in_array($oFiltroRelatorio->iModelo, array(3, 4))) {
  require_once(modification("fpdf151educacao/scpdf.php"));
}

if ($oFiltroRelatorio->iModelo == 2 || $oFiltroRelatorio->iModelo == 4) {
  $oFiltroRelatorio->lAssinatura = true;
}

/**
 * Verificamos se o parametro de decimais esta habilitado
 */
$iEscola              = db_getsession("DB_coddepto");
$oDaoEduParametros    = new cl_edu_parametros();
$sCamposEduParametros = "ed233_c_decimais, ed233_c_limitemov";
$sWhereEduParametros  = "ed233_i_escola = {$iEscola}";
$sSqlEduParametros    = $oDaoEduParametros->sql_query_file(null, $sCamposEduParametros, null, $sWhereEduParametros);
$rsEduParametros      = db_query($sSqlEduParametros);

if( is_resource( $rsEduParametros ) && pg_num_rows( $rsEduParametros ) > 0 ) {

  $oDadosEduParametro                    = db_utils::fieldsMemory($rsEduParametros, 0);
  $oFiltroRelatorio->sDecimais           = $oDadosEduParametro->ed233_c_decimais;
  $oFiltroRelatorio->sLimiteMovimentacao = $oDadosEduParametro->ed233_c_limitemov;
}

/**
 * Buscamos os dados de edu_relatmodel para impressao no cabecalho
 */
if (is_numeric($oFiltroRelatorio->iCodigoTipoModelo)) {

  $oDaoRelatModel   = new cl_edu_relatmodel();
  $sCampoRelatModel = "ed217_t_cabecalho, ed217_t_rodape, ed217_t_obs, ed217_i_tipomodelo";
  $sWhereRelatModel = "ed217_i_codigo = {$oFiltroRelatorio->iCodigoTipoModelo}";
  $sSqlRelatModel   = $oDaoRelatModel->sql_query(null, $sCampoRelatModel, null, $sWhereRelatModel);
  $rsRelatModel     = db_query( $sSqlRelatModel );

  if ( is_resource( $rsRelatModel ) && pg_num_rows( $rsRelatModel ) > 0) {

    $oDadosRelatModel              = db_utils::fieldsMemory($rsRelatModel, 0);
    $oFiltroRelatorio->mCabecalho  = $oDadosRelatModel->ed217_t_cabecalho;
    $oFiltroRelatorio->mRodape     = $oDadosRelatModel->ed217_t_rodape;
    $oFiltroRelatorio->mObservacao = $oDadosRelatModel->ed217_t_obs;
    $oFiltroRelatorio->iTipoModelo = $oDadosRelatModel->ed217_i_tipomodelo;
  }
}



//=======================ÁREA DE TESTES ANTES DO PDF===============================


//testa($oFiltroRelatorio->mCabecalho);
//var_dump($iCodigoEtapa);
//die("Confere cabeçalho");





//=============================================================================



require_once './vendor/autoload.php';
$phpWord = new \PhpOffice\PhpWord\PhpWord();
$section = $phpWord->addSection();

//$tamanho = new \PhpOffice\PHPWord_Shared_Font();

$fontStyle = new \PhpOffice\PhpWord\Style\Font();
$fontStyle->setBold(false);
$fontStyle->setName('Arial');
$fontStyle->setSize(7);

$fontStyle1 = new \PhpOffice\PhpWord\Style\Font();
$fontStyle1->setBold(false);
$fontStyle1->setName('Arial');
$fontStyle1->setSize(6);

$headert = $section->createHeader();
$table   = $headert->addTable();
$table->addRow();
$table->addCell()->addImage('logovoltaeducacaoata.jpg',array('width'  => 480,'height' => 50,'align'  => 'right'));


$combordas = array(''=>'','borderSize'=>6, 'cellMarginTop'=>100);

$bordasexternas = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                        'borderLeftColor'=>''  ,'borderLeftSize'=>6,
                        'borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	    'borderBottomColor'=>'','borderBottomSize'=>6,
				 	    'cellMarginTop'=>100,
					   );

$sembordas = array('cellMarginTop'=>100);

$sembordasabaixo = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                         'borderLeftColor'=>''  ,'borderLeftSize'=>6,
                         'borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	     'cellMarginTop'=>100,
					    );

$sembordasacima = array('borderLeftColor'=>''  ,'borderLeftSize'=>6,
                        'borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	    'borderBottomColor'=>'','borderBottomSize'=>6,
				 	    'cellMarginTop'=>100,
					   );

$esquerdaabaixo = array('borderLeftColor'=>''  ,'borderLeftSize'=>6,
				 	   'borderBottomColor'=>'','borderBottomSize'=>6,
				 	   'cellMarginTop'=>100,
					   );

$esquerdaacima = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                       'borderLeftColor'=>''  ,'borderLeftSize'=>6,
				 	   'cellMarginTop'=>100,
					   );

$abaixo = array('borderBottomColor'=>'','borderBottomSize'=>6,
				'cellMarginTop'=>100,
			   );

$direitaabaixo  = array('borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	    'borderBottomColor'=>'','borderBottomSize'=>6,
				 	    'cellMarginTop'=>100,
					   );





//Case de acordo com o modelo do relatorio 


if($oFiltroRelatorio->iModelo == 1 || $oFiltroRelatorio->iModelo == 2)
{
	
	
    $oFiltroRelatorio->iTotalDisciplinasPorPagina = 7;
    $oFiltroRelatorio->iTotalAlunosPorPagina      = 45;
    $aAlunosComBaixaFrequencia = array();
    $oTurma = TurmaRepository::getTurmaByCodigo($aTurmas[0]->turma); // nessa linha mudei para fazer uma turma de cada vez
	
    //Código para educação infantil = 3
    $pegacodigoinfantil = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();

    $iCodigoEtapa = $aTurmas[0]->etapa;
	
    $oEtapaTurma  = EtapaRepository::getEtapaByCodigo( $iCodigoEtapa );

    $sql = 'select * from escola where ed18_i_codigo = '.$iEscola;
    $result = db_query($sql);
    $escoladados = db_utils::fieldsMemory($result, 0);
	  
      
      //ENSINO E SÉRIE
    $xetapa = $oEtapaTurma->getEnsino()->getNome();
    $xserie = $oEtapaTurma->getNome();
	  
    if($xetapa == "EDUCAÇÃO INFANTIL PRÉ-ESCOLA"){
        $etapaabreviada = "Educação Infantil";
    }

    if($oTurma->getProfessorConselheiro()){
        $xnomeprofessor = trataNome($oTurma->getProfessorConselheiro()->getNome());
    }else{
        $xnomeprofessor = "";
    }
	
    dadosEscola($oTurma, $aTurmas[0]->etapa);
    $oDocumento              = new libdocumento( 5012 );      
    $oDocumento->dia         = $oTurma->oDadosEscola->iDia;
    $oDocumento->mes_extenso = $oTurma->oDadosEscola->iMes;
    $oDocumento->ano         = $oTurma->oDadosEscola->iAno;

    $mesaqui = ucfirst($oTurma->oDadosEscola->iMes);
	$oDadosCabecalho = new stdClass();
    $oDadosCabecalho->aParagrafo = $oDocumento->getDocParagrafos();      

//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq, $xetapa.'---'.$xserie);
//fwrite($arq,"\r\n");
//fclose($arq); 		

    if( $xetapa == "ENSINO FUNDAMENTAL" and $xserie == "1º ANO" ){
		
        $novotexto = "Aos {$oTurma->oDadosEscola->iDia} dias do mês de {$mesaqui} de {$oTurma->oDadosEscola->iAno}, encerrou-se a apuração de resultados do {$xserie} do Ensino Médio, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, deste Estabelecimento de Ensino, com os seguintes resultados:";
		$styleTable = array('cellMarginTop'=>100);
		$styleFirstRow = array('bgColor'=>' #F0F0F0');
		$phpWord->addTableStyle('myTable', $styleTable);
		$table = $section->addTable('myTable');
		$table->addRow(25);
		$table->addCell(17000,$sembordas)->addText($escoladados->ed18_c_nome.' - VOLTA REDONDA - RJ',$fontStyle);
		$table->addCell(7000,$combordas)->addText($novotexto,$fontStyle);
        $myTextElement  = $section->addText(''); //uma linha entre o cabecalho e o resultado
		
    }
	

    if($xetapa == "ENSINO FUNDAMENTAL" && ($xserie == "2º ANO" || $xserie == "3º ANO" || $xserie == "4º ANO" || $xserie == "5º ANO")){
        $novotexto = "Aos {$oTurma->oDadosEscola->iDia} dias do mês de {$mesaqui} de {$oTurma->oDadosEscola->iAno}, encerrou-se a apuração de resultados do {$xserie} do Ensino Fundamental, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, Professor(a) {$xnomeprofessor}, deste Estabelecimento de Ensino, com os seguintes resultados:";
		$styleTable = array('cellMarginTop'=>100);
		$styleFirstRow = array('bgColor'=>' #F0F0F0');
		$phpWord->addTableStyle('myTable', $styleTable);
		$table = $section->addTable('myTable');
		$table->addRow(25);
		$table->addCell(17000,$sembordas)->addText($escoladados->ed18_c_nome.' - VOLTA REDONDA - RJ',$fontStyle);
		$table->addCell(7000,$combordas)->addText($novotexto,$fontStyle);
        $myTextElement  = $section->addText(''); //uma linha entre o cabecalho e o resultado
    }

//        $novotexto = "Aos {$oTurma->oDadosEscola->iDia} dias do mês de {$mesaqui} de {$oTurma->oDadosEscola->iAno}, encerrou-se a apuração de resultados do {$xserie} do Ensino Médio, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, deste Estabelecimento de Ensino, com os seguintes resultados:";
    if($xetapa == "EDUCAÇÃO DE JOVENS E ADULTOS"){
        $xurma = $oTurma->getEtapas();
        $xurma = $xurma[0];
        $xiclo = $xurma->getEtapa()->getNome();
        if($xiclo == "CIC BÁS DE ALFABET"){
          $novotexto = "Aos {$oTurma->oDadosEscola->iDia} dias do mês de {$mesaqui} de {$oTurma->oDadosEscola->iAno}, encerrou-se a apuração de resultados dos alunos do Ciclo Básico e Alfabetização da EJA - Educação de Jovens e Adultos, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, Professor(a) {$xnomeprofessor}, deste Estabelecimento de Ensino, com os seguintes resultados:";
        }else{
          $novotexto = "Aos {$oTurma->oDadosEscola->iDia} dias do mês de {$mesaqui} de {$oTurma->oDadosEscola->iAno}, encerrou-se a apuração de resultados dos alunos do {$xiclo} da EJA - Educação de Jovens e Adultos, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, deste Estabelecimento de Ensino, com os seguintes resultados:";
        }
		$styleTable = array('cellMarginTop'=>100);
		$styleFirstRow = array('bgColor'=>' #F0F0F0');
		$phpWord->addTableStyle('myTable', $styleTable);
		$table = $section->addTable('myTable');
		$table->addRow(25);
		$table->addCell(17000,$sembordas)->addText($escoladados->ed18_c_nome.' - VOLTA REDONDA - RJ',$fontStyle);
		$table->addCell(7000,$combordas)->addText($novotexto,$fontStyle);
        $myTextElement  = $section->addText(''); //uma linha entre o cabecalho e o resultado
		
    }
	
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq, $xetapa.'---'.$xserie);
//fwrite($arq,"\r\n");
//fclose($arq); 		
	
    if($xetapa == "ENSINO FUNDAMENTAL" && ($xserie == "6º ANO" || $xserie == "7º ANO" || $xserie == "8º ANO" || $xserie == "9º ANO")){
        $novotexto = "Aos {$oTurma->oDadosEscola->iDia} dias do mês de {$mesaqui} de {$oTurma->oDadosEscola->iAno}, encerrou-se a apuração de resultados do {$xserie} do Ensino Fundamental, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, deste Estabelecimento de Ensino, com os seguintes resultados:";
		$styleTable = array('cellMarginTop'=>100);
		$styleFirstRow = array('bgColor'=>' #F0F0F0');
		$phpWord->addTableStyle('myTable', $styleTable);
		$table = $section->addTable('myTable');
		$table->addRow(25);
		$table->addCell(17000,$sembordas)->addText($escoladados->ed18_c_nome.' - VOLTA REDONDA - RJ',$fontStyle);
		$table->addCell(7000,$combordas)->addText($novotexto,$fontStyle);
        $myTextElement  = $section->addText(''); //uma linha entre o cabecalho e o resultado
		
    }
	

	
    $head1  = "ATA DE RESULTADOS FINAIS";
    $head2  = $novotexto;

    corpoWord($phpWord, $section, $oTurma, $oFiltroRelatorio, $aAlunosComBaixaFrequencia, $iCodigoEtapa, $xserie);
	
    if ($oFiltroRelatorio->lAssinatura) {
        assinaturaDocente($oTurma, $oFiltroRelatorio, $iCodigoEtapa);
    }
    TurmaRepository::removerTurma($oTurma);
    unset($oTurma);	
}	

$linha_impressa = '';
$myTextElement  = $section->addText($linha_impressa);
$myTextElement->setFontStyle($fontStyle);
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('ata.docx');
session_write_close();
download("ata.docx");
header("Location: https://homologacao.epdvr.com.br/homologacao/extension/desktop");
  
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq, 'estou aqui');
//fwrite($arq,"\r\n");
//fclose($arq); 		

function download($arquivo){
      header("Content-Type: application/force-download");
      header("Content-Type: application/octet-stream;");
      header("Content-Length:".filesize($arquivo));
      header("Content-disposition: attachment; filename=".$arquivo);
      header("Pragma: no-cache");
      header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
      header("Expires: 0");
      readfile($arquivo);
      flush();
}

function dadosEscola(Turma $oTurma, $iEtapa) {

  $oTurma->oDadosEscola              = new stdClass();
  $oTurma->oDadosEscola->iTotalHoras = '';

  /**
   * Retornamos a etapa da turma
   */
  foreach ($oTurma->getEtapas() as $oEtapa) {

    if ($iEtapa == $oEtapa->getEtapa()->getCodigo()) {
      $oTurma->oDadosEscola->sEtapa = $oEtapa->getEtapa()->getNome();
    }
  }

  /**
   * Retornamos o dia, mes e ano da data de resultado final do calendário
   */
  $oTurma->oDadosEscola->iDia = $oTurma->getCalendario()->getDataResultadoFinal()->getDia();
  $oTurma->oDadosEscola->iMes = db_mes($oTurma->getCalendario()->getDataResultadoFinal()->getMes());
  $oTurma->oDadosEscola->iAno = $oTurma->getCalendario()->getDataResultadoFinal()->getAno();

  return $oTurma;
}

function textoAtoCabecalho( $oFiltroRelatorio, $oTurma ) {

  $oDocumento              = new libdocumento( 5012 );
  $oDocumento->dia         = $oTurma->oDadosEscola->iDia;
  $oDocumento->mes_extenso = ucfirst( $oTurma->oDadosEscola->iMes );
  $oDocumento->ano         = $oTurma->oDadosEscola->iAno;

  $oDadosCabecalho              = new stdClass();
  $oDadosCabecalho->aParagrafo  = $oDocumento->getDocParagrafos();
  $oFiltroRelatorio->sCabecalho = $oDadosCabecalho->aParagrafo[1]->oParag->db02_texto;
}


function corpoWord($phpWord, $section, Turma $oTurma, $oFiltroRelatorio, $aAlunosComBaixaFrequencia, $iCodigoEtapa, $xserie2) {
	
  global  $lObservacaoProgressaoParcial;    
  $sNomeAluno = array();      
  $aCargaHoraria  = array();
  $oEtapa         = EtapaRepository::getEtapaByCodigo($iCodigoEtapa);
  $iAnoCalendario = $oTurma->getCalendario()->getAnoExecucao();

//************************************************************************************************************************************************************************
  $aDisciplinas   = $oTurma->getDisciplinasPorEtapa($oEtapa);
//************************************************************************************************************************************************************************  
  $oFiltroRelatorio->lCalculaFrequencia = 1;
  $oFiltroRelatorio->lCalculaFrequencia = $oTurma->getProcedimentoDeAvaliacaoDaEtapa($oEtapa)->getFormaCalculoFrequencia();  
  $oFiltroRelatorio->iTamanhoColunaAbrevDisciplina = 16;
  $oFiltroRelatorio->iAuxiliarTransferido          = 7;
  $oFiltroRelatorio->iTamanhoTotalColunaDisciplina = 65;  
  
  if ($oFiltroRelatorio->iFrequencia == 1 || $oFiltroRelatorio->lCalculaFrequencia == 2) {
    $oFiltroRelatorio->iTamanhoColunaAbrevDisciplina = 11;
    $oFiltroRelatorio->iTotalDisciplinasPorPagina    = 10;
    $oFiltroRelatorio->iAuxiliarTransferido          = 10;
  }  
  $oFiltroRelatorio->iAltura = 4;    
  $aDisciplinasPorPagina = array();
  $iContadorAux          = 0;
  $iPagina               = 0;
  $aListaDeAlunos        = array();
  $lSequencialDiario     = true;

  $xxnomeetapa = $oTurma->getBaseCurricular()->getDescricao();
  $xxturma = $oTurma->getDescricao();
  $turma   = $oTurma->getEtapas();
//  $turma2  = $turma->getEtapa()->getNome();
  
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/turma.txt","w+");
//fwrite($arq, $xxnomeetapa.'--'.$turma2);
//fwrite($arq,"\r\n");
//fclose($arq); 		
  
  $phpWord->setDefaultParagraphStyle(
		array(
			'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
			'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
			'spacing' => 120,
			'lineHeight' => 1
		)
  );  
  $combordas = array(''=>'','borderSize'=>6, 'cellMarginTop'=>100,'valign'=>'center');  
  $fontStyle = new \PhpOffice\PhpWord\Style\Font();
  $fontStyle->setBold(false);
  $fontStyle->setName('Arial');
  $fontStyle->setSize(7);
  
  $styleTable = array('cellMarginTop'=>100);
  $phpWord->addTableStyle('myTable2', $styleTable);
  $table = $section->addTable('myTable2');
  
    if($xxnomeetapa == "EJA ANOS INICIAIS" || $xxnomeetapa == "EJA ANOS FINAIS" ){
		
		$reduzcoluna = 2;    
		foreach ($aDisciplinas as $oDisciplina) {
		  if ( !$oDisciplina->isLancadaNoHistorico() ) {continue;}
		  $aDisciplinasPorPagina[$iPagina][$iContadorAux] = $oDisciplina;
		  $iTotalContadorAux = 6;
		  
		  //Verificamos se foi selecionado algum tipo de frequencia. Caso nao (1), aumentamos a quantidade do contador auxiliar para validar ate 9     
		  if ($oFiltroRelatorio->iFrequencia == 1 || $oFiltroRelatorio->lCalculaFrequencia == 2) {
			$iTotalContadorAux = 9;
		  }
		  if ($iContadorAux >= $iTotalContadorAux) {
			$iPagina ++;
			$iContadorAux = -1;
		  }
		  $iContadorAux++;
		}  
		//Variavel a ser utilizada no laco para impressao das disciplinas  
		$oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinasPorPagina;
		$aListaDeAlunos = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);
	
		switch($oFiltroRelatorio->iOrdenacao) {
		  case 2:
		  case 3:
			usort($aListaDeAlunos, "ordernarAlunosPorNome");
			break;
		}
	  
		if ($oFiltroRelatorio->iOrdenacao == 2) {
		  $lSequencialDiario = false;
		}
		$tamanhoDisciplina = 1000;
		//Armazenamos o total de alunos matriculados na turma   
		$iTotalAlunosMatriculados  = count($aListaDeAlunos);
		$iAlunosImpressos          = 0;
		$lPrimeiroLaco             = true;
        $linha = 31;
	  
		
        foreach ($aDisciplinasPorPagina as $iDisciplina => $aDisciplinasPagina) {	
			$iPreenchimento = 0;
			$lPulouAluno    = false;
			//***************************************************************************************************************************** 
			for ($iContadorAluno = 0; $iContadorAluno < $iTotalAlunosMatriculados; $iContadorAluno++) {//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
			//Valida se nos parâmetros globais, foi configurada uma data limite para movimentação. Caso tenha sido, e a data de saída da matrícula é menor que esta data, não apresenta o aluno no relatório       
				if (isset($oFiltroRelatorio->sLimiteMovimentacao) && !empty($oFiltroRelatorio->sLimiteMovimentacao) && $aListaDeAlunos[$iContadorAluno]->getDataEncerramento() != null){
				  $oFiltroRelatorio->sLimiteMovimentacao = $oFiltroRelatorio->sLimiteMovimentacao . "/" . $oTurma->getCalendario()->getAnoExecucao();
				  $oDataLimiteMovimentacao               = new DBDate( $oFiltroRelatorio->sLimiteMovimentacao );
				  if(DBDate::calculaIntervaloEntreDatas( $oDataLimiteMovimentacao, $aListaDeAlunos[$iContadorAluno]->getDataEncerramento(), 'd' ) > 0 ) {
					$lPulouAluno = true;
					$iAlunosImpressos++;
					continue;
				  }
				}
				if( $linha >= 30 ) // cabecalho - para o arquivo foi gerado aqui, porque a sessão muda para ultima linha impressa
				{
					$table->addRow();
					$table->addCell(1000, $combordas)->addText(' Nº',$fontStyle);
					$table->addCell(10000,$combordas)->addText(' Nome do Aluno',$fontStyle);

					if ($oFiltroRelatorio->iTotalDisciplinas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
					  $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinas;
					}
					$oFiltroRelatorio->iColunasEmBranco = 0;
					if ($oFiltroRelatorio->iContadorDisciplinasImpressas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
					  $oFiltroRelatorio->iColunasEmBranco = $oFiltroRelatorio->iTotalDisciplinasPorPagina - $oFiltroRelatorio->iContadorDisciplinasImpressas;
					}  
					$koluna = 9;
					$kamanho = 100;
					$disciplinasNome = array();

					for ($iContadorDisciplinas = 0; $iContadorDisciplinas <= count($aDisciplinasPagina);  $iContadorDisciplinas++) {
						if (!array_key_exists ($iContadorDisciplinas, $aDisciplinasPagina)) {break;}
						if ($iContadorDisciplinas == $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
							$myTextElement  = $section->addPageBreak();
							$lQuebrouPagina = true;
						}
						$table->addCell(1000,$combordas)->addText(substr($aDisciplinasPagina[$iContadorDisciplinas]->getDisciplina()->getNomeDisciplina(),0,3),$fontStyle);   
					}		
					$table->addCell(1000,$combordas)->addText('Freq%',$fontStyle);
					$table->addCell(1000,$combordas)->addText('Resul',$fontStyle);
					$lQuebrouPagina = false;					  
					$linha = 0;
				}
//************************************************************************				

				$situacaoaluno =  $aListaDeAlunos[$iContadorAluno]->getSituacao();
				$sNome         = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
				if($situacaoaluno != "MATRICULADO"){ // se o aluno evadiu/transferiu ou saiu por qualquer motivo
					$oDtEncerramento = $aListaDeAlunos[$iContadorAluno]->getDataEncerramento();
					
			        $styleTable1 = array('cellMarginTop'=>100);
			        $phpWord->addTableStyle('myTable22', $styleTable1);
			        $table2 = $section->addTable('myTable22'); // tive que mudar a sessão para imprimir 2 colunas, isso faz que essas linha fiquem no final do arquivo
					
					$table2->addRow();
					$table2->addCell(750, $combordas)->addText('  '.$aListaDeAlunos[$iContadorAluno]->getNumeroOrdemAluno(),$fontStyle);
					$sNomeAluno  = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
					$table2->addCell(7200,$combordas)->addText('  '.$sNomeAluno,$fontStyle);
/*					
					if( $sTransferido == 'TROCA DE TURMA')
					{
						$table->addCell(1000)->addText("  ".substr($sTransferido,0,5),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,5,4),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,8,6),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,16,5)." em:",$fontStyle3);
					}else{
						$table->addCell(1000)->addText("  ".substr($sTransferido,0,7),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,7,5),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,11,5),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,16,5)." em:",$fontStyle3);
					}	
*/					
					
					$table2->addCell(15000,$combordas)->addText($aListaDeAlunos[$iContadorAluno]->getSituacao().' em: '.$oDtEncerramento,$fontStyle);
					continue; // passa para o próximo aluno nessa situação
					
				}else{
                    $sNomeAluno                = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());				
					$oFiltroRelatorio->iAltura = $iLinhasAluno * 4;
					$iPreenchimento = $iContadorAluno;
					if($lPulouAluno){$iPreenchimento++;}
					$sBordaAluno = "LR";
					if ( $oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4 ) {
					  if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina - 1) {
						$sBordaAluno = "LRB";
					  }
					}

					$lQuebrouPagina = false;

					//Verificamos se o numero de alunos por pagina foi atingido      
					if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina ) {
					  //footerPadrao($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
//					  $myTextElement  = $section->addPageBreak();
					  $lQuebrouPagina = true;
					}

					if ($iAlunosImpressos >= $iTotalAlunosMatriculados) {
					  $lQuebrouPagina      = true;
					  $iContadorSequencial = 0;
					  $iAlunosImpressos    = 0;
					}

					$iAlunosImpressos++;      
					if( $iAlunosImpressos++ > 55 ) {
						$iAlunosImpressos    = 0;
					    $lQuebrouPagina      = true;						
					    $myTextElement  = $section->addPageBreak();
					}
                    $iAlunosImpressos++;
					
					if ($oFiltroRelatorio->iTrocaTurma == 1 && $aListaDeAlunos[$iContadorAluno]->getSituacao() == "TROCA DE TURMA") {
					  continue;
					}
					
			        $table->addRow();
					if ($lSequencialDiario) {
					    $table->addCell(1000, $combordas)->addText('  '.$aListaDeAlunos[$iContadorAluno]->getNumeroOrdemAluno(),$fontStyle);
					} else {
  					    $table->addCell(1000, $combordas)->addText('  '.++$iContadorSequencial,$fontStyle);
					}

                    $table->addCell(10000,$combordas)->addText('  '.$sNomeAluno,$fontStyle);
					$linha++;
					
					// * Buscamos os dados do resultado final
					
					$sAproveitamento               = '';
					$sPercentualFrequencia         = '';
					$iNumeroFaltas                 = '';
					$sResultadoFinal               = '';
					$iContadorDisciplinasImpressas = 0;
					$sResultadoGeral               = 'A';

					
					 //* Imprimimos a situacao do aluno na linha, caso ele tenha sido transferido
					
					if ($aListaDeAlunos[$iContadorAluno]->getSituacao() != "MATRICULADO") {
			  
					    $oDtEncerramento = $aListaDeAlunos[$iContadorAluno]->getDataEncerramento();
					    $sDtEncerramento = "";
					    if (!empty($oDtEncerramento)) {
						    $sDtEncerramento = " em " .$oDtEncerramento->convertTo(DBDate::DATA_PTBR);
					    }
			  
					    //$sTransferido = $aListaDeAlunos[$iContadorAluno]->getSituacao() . " {$sDtEncerramento}";
					    $sTransferido = $aListaDeAlunos[$iContadorAluno]->getSituacao();
					    if($sTransferido == "TRANSFERIDO FORA"){
						    $sTransferido = explode(" ", $sTransferido);
						    $sTransferido = $sTransferido[0];          
					    }
					    $iLinha       = ($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina * $oFiltroRelatorio->iAuxiliarTransferido) + 12;
					    $table->addCell(7000,$combordas)->addText('  '.$sTransferido.$oDtEncerramento,$fontStyle);
						continue;
					} else {

					    
					    //Verifica se o aluno foi aprovado com progressão parcial
					    
					    $lAprovadoProgressaoAno = false;
					    foreach( $aListaDeAlunos[$iContadorAluno]->getAluno()->getProgressaoParcial() as $oProgressaoParcial ) {
							if(    $oTurma->getCalendario()->getAnoExecucao() == $oProgressaoParcial->getAno()
								&& $oProgressaoParcial->getCodigoDiarioFinal() != null
							  ) {
							  $lAprovadoProgressaoAno = true;
							}
					    }
						
					    $iTotalDeAulasDadas      = 0;
					    $nTotalDeFaltas          = 0;
					    $iTotalDeAulasDadasGeral = 0;
						foreach ($aDisciplinasPagina as $oRegenciaTurma) {
							db_inicio_transacao();
				  
							$oRegencia = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()
																		 ->getDisciplinasPorRegencia($oRegenciaTurma);
							$sAmparado = '';
							if ($oRegencia->getAmparo() != null && $oRegencia->getAmparo()->isTotal() ) {
							
 				                if ($oRegencia->getAmparo()->getCodigoConvencaoAmparo()) {
									$oDaoConvencaoAmparo = new cl_convencaoamp();
									$sSqlConvencaoAmparo = $oDaoConvencaoAmparo->sql_query_file($oRegencia->getAmparo()->getCodigoConvencaoAmparo());
									$rsConvencaoAmparo   = $oDaoConvencaoAmparo->sql_record($sSqlConvencaoAmparo);
									$oConvencaoAmparo    = db_utils::fieldsMemory($rsConvencaoAmparo, 0);
									$sAmparado           = $oConvencaoAmparo->ed250_c_abrev;
					  
									$oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oConvencaoAmparo->ed250_c_abrev.' - '.$oConvencaoAmparo->ed250_c_descr;
									
								}
								
							    if ($oRegencia->getAmparo()->getCodigoJustificativa()) {
				  
								    $sAmparado = 'AMP '.$oRegencia->getAmparo()->getCodigoJustificativa();
				  
								    $oDaoJustificativa = new cl_justificativa();
								    $sSqlJustificativa = $oDaoJustificativa->sql_query_file($oRegencia->getAmparo()->getCodigoJustificativa());
								    $rsJustitificativa = $oDaoJustificativa->sql_record($sSqlJustificativa);
								    $oDadosJustificativa = db_utils::fieldsMemory($rsJustitificativa, 0);
								    $oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oRegencia->getAmparo()->getCodigoJustificativa().' - '.$oDadosJustificativa->ed06_c_descr;
							    }
							}

							$iNumeroFaltas            = $oRegencia->getTotalFaltas();
							$iTotalDeAulasDadas       = $oRegencia->getTotalDeAulasParaCalculo();
							$iTotalDeAulasDadasGeral += $oRegencia->getTotalDeAulasParaCalculo();
							if ($oFiltroRelatorio->lCalculaFrequencia == 2) {
							  $nTotalDeFaltas += $iNumeroFaltas;
							}
							db_fim_transacao();
							$iCodigoEnsino   = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
							$oResultadoFinal = $oRegencia->getResultadoFinal();
							
							// * Valor do resultado de aprovacao
							
							$nValorAproveitamento = $oResultadoFinal->getValorAprovacao();
							
							if(    $oResultadoFinal->getFormaAprovacaoConselho() instanceof AprovacaoConselho
								&& $oResultadoFinal->getFormaAprovacaoConselho()->getFormaAprovacao() == 1
								&& $oResultadoFinal->getFormaAprovacaoConselho()->getAlterarNotaFinal() == 2
							  ) {
							    $nValorAproveitamento = $oResultadoFinal->getFormaAprovacaoConselho()->getAvaliacaoConselho();
							}
							if(    $oResultadoFinal->getFormaAprovacaoConselho() instanceof AprovacaoConselho
								&& $oResultadoFinal->getFormaAprovacaoConselho()->getFormaAprovacao() == 1
								&& $oResultadoFinal->getFormaAprovacaoConselho()->getAlterarNotaFinal() == 2
							  ) {
							    $nValorAproveitamento = $oResultadoFinal->getFormaAprovacaoConselho()->getAvaliacaoConselho();
							}
							
							// * Se for parecer devemos utilizar o resultado da aprovacao do aluno
							
							$oFormaAvaliacao = $oResultadoFinal->getResultadoAvaliacao()->getFormaDeAvaliacao();
							if (!empty($oFormaAvaliacao) && $oFormaAvaliacao->getTipo() == "PARECER") {
				  
							    $nValorAproveitamento = $oResultadoFinal->getResultadoAprovacao();
							    if (!empty($iCodigoEnsino) && ($nValorAproveitamento == 'A' || $nValorAproveitamento == 'R')) {
				  
								    $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $nValorAproveitamento, $iAnoCalendario);
								    if (isset($aDadosTermo[0])) {
								        $nValorAproveitamento = $aDadosTermo[0]->sAbreviatura;
								    }
							    }
							}
							
							// * Se for uma nota o valor do aproveitamento devemos aplicar as regras de arrendondamento
							
							if (is_numeric($nValorAproveitamento)) {
							    $nValorAproveitamento  = ArredondamentoNota::formatar($nValorAproveitamento,
								   													$oTurma->getCalendario()->getAnoExecucao()
																				   );
							}
							$sPercentualFrequencia = $oRegencia->calcularPercentualFrequencia();

							
							// * Antes estava buscando o RF da disciplina.
							// * Devemos buscar o resultado final de todas as avaliações
							
							$sResultadoAprovacao = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->getResultadoFinal();

							
							// * Verificamos se o aluno foi reprovado em alguma disciplina. Caso tenha sido, o Resultado Final é 'R', desde
							// * que o mesmo não tenha sido aprovado com progressão parcial
							
							if ($sResultadoAprovacao == 'R' && !$lAprovadoProgressaoAno) {
							    $sResultadoGeral = 'R';
							}
							
							
							// * Busca o termo do ensino
							
							if (!empty($iCodigoEnsino) && ($sResultadoGeral == 'A' || $sResultadoGeral == 'R')) {
				  
							  $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $sResultadoGeral, $iAnoCalendario);
							  if (isset($aDadosTermo[0])) {
								$sResultadoGeral = $aDadosTermo[0]->sAbreviatura;
							  }
							}

							
							 //Verifica se houve aprovação pelo conselhor
							 //Se sim, identificamos com um número sobrescrito para identificar o tipo na legenda
							 
							$oAprovConselho = $oResultadoFinal->getFormaAprovacaoConselho();
							if ($sAmparado != '') {
							    $nValorAproveitamento = $sAmparado;
							}
							//NOTA COM VÍRGULA
							$nValorAproveitamento = str_replace(".", ",", $nValorAproveitamento);
							//var_dump($nValorAproveitamento); die("Confere");
							if ($nValorAproveitamento == 'Parecer') {
							    $nValorAproveitamento = 'Rel';
							}
							
							 //Preenchemos com o aproveitamento para cada disciplina
							 
							if ($oFiltroRelatorio->lCalculaFrequencia == 1 && $oFiltroRelatorio->iFrequencia != 1) {
							    $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorAproveitamento,$fontStyle);	
								$nValorFalta = !empty($iNumeroFaltas) ? $iNumeroFaltas : "" ;
								if ($oFiltroRelatorio->iFrequencia == 2) {
								    $nValorFalta =  $sPercentualFrequencia;
								}
							    if ($oFiltroRelatorio->iFrequencia == 4) {
								    $nValorFalta  = $iTotalDeAulasDadas - $iNumeroFaltas;
							    }
							    if ($oRegencia->reclassificadoPorBaixaFrequencia()) {
								    //$nValorFalta = '--';
									if( $nValorFalta < '75')
									{ 	   
										$nValorFalta = '75';
									}   
							    }
							    if ($oRegencia->getRegencia()->getFrequenciaGlobal()  == 'A') {
								//$nValorFalta = '-';
								    if( $nValorFalta < '75')
								    { 	   
									    $nValorFalta = '75';
								    }   
                                }
                                $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorFalta,$fontStyle);
                            }else{
							    $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorAproveitamento,$fontStyle);	
							}	// fim do aproveitamento disciplina							
							$iContadorDisciplinasImpressas++;
						}// fim do foreach, não esquecer

						if(($sResultadoGeral == "APR") or ($sResultadoGeral == "Apr")){
							$sResultadoGeral = "AP";
						}
				  
						if( $lAprovadoProgressaoAno ) {  
							$lObservacaoProgressaoParcial  = true;
							$sResultadoGeral              .= '/D';
						}  
                        if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
							$nValorFaltas = $sPercentualFrequencia;
							if ($oFiltroRelatorio->iFrequencia == 3) {
							    $nValorFaltas = !empty($nTotalDeFaltas) ? $nTotalDeFaltas : "" ;
							}  
							if ($oFiltroRelatorio->iFrequencia == 4) {
							    $nValorFaltas  = $iTotalDeAulasDadasGeral - $nTotalDeFaltas;
							}  
							if ($aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia()) {
							  //$nValorFaltas = '--';
							    if( $nValorFaltas < '75')
							    { 	   
								    $nValorFaltas = '75';
							    }   
							}  
							if ($oRegencia->getRegencia()->getFrequenciaGlobal()  == 'A') {
							    //$nValorFalta = '-';
							    if( $nValorFaltas < '75')
							    { 	   
								    $nValorFaltas = '75';
							    }   
							}
							$table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorFalta,$fontStyle);
							$table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$sResultadoGeral,$fontStyle);
						}else{
							$table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$sResultadoGeral,$fontStyle);
						}	// fim do outro if de resultados
						

						
					} // fim do if, não esquecer	

				}// fim do if MATRICULADO	

            }// fim do for 	//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx	
	    }// fim do primeiro foreach
//***********************************************************************************************************************
		if ($oFiltroRelatorio->iTipoModelo != 2) {
			$sObservacoesEscola = $oFiltroRelatorio->sObservacao;
			
			if ($sObservacoesEscola != null) {
			    $combordas = array(''=>'','borderSize'=>6, 'cellMarginTop'=>100,'valign'=>'center');  
			    $fontStyle = new \PhpOffice\PhpWord\Style\Font();
			    $fontStyle->setBold(false);
			    $fontStyle->setName('Arial');
			    $fontStyle->setSize(7);
			  
			    $styleTable = array('cellMarginTop'=>100);
			    $phpWord->addTableStyle('myTable2', $styleTable);
			    $table = $section->addTable('myTable3');
				
				$myTextElement  = $section->addText('');
				$table->addRow(25);
				$table->addCell(20000,$sembordas)->addText("Observações:",$fontStyle);
				if (mb_detect_encoding($sObservacoesEscola . 'x', 'UTF-8', 'ISO-8859-1') == 'UTF-8') {
					$sObservacoesEscola = utf8_decode($sObservacoesEscola);
					$sObservacoesEscola = str_replace(",", ',', $sObservacoesEscola);
				} else {
					$sObservacoesEscola = str_replace(",", ',', $sObservacoesEscola);
				}
				$table->addRow(25);
				$table->addCell(20000,$sembordas)->addText($sObservacoesEscola,$fontStyle);
//				$myTextElement  = $section->addText($sObservacoesEscola);
			}

			//assinatura aqui
			$linha_impressa = "E para constar, eu _______________________________________, lavrei a presente Ata que vai assinada pelas";
			$myTextElement  = $section->addText($linha_impressa);
			$linha_impressa = "autoridades competentes.";
			$myTextElement  = $section->addText($linha_impressa);

			$nomediretor = $_GET["diretor"];
			$nomediretor = explode("|", $nomediretor);
			$nomediretor = $nomediretor[1];
			$nomediretor = trataNome($nomediretor);

			$nomesecretario = $_GET["secretario"];
			$nomesecretario = explode("|", $nomesecretario);
			$nomesecretario = $nomesecretario[1];
			$nomesecretario = trataNome($nomesecretario);

			if(isset($oFiltroRelatorio->iRegente)){
			  $xDocente       = DocenteRepository::getDocenteByCodigoRecursosHumano($oFiltroRelatorio->iRegente);
			  $nomesupervisor   = trataNome($xDocente->getNome());
			}else{
			  $nomesupervisor = "";
			}
			$myTextElement  = $section->addText('');
		    $styleTable = array('cellMarginTop'=>100);
		    $phpWord->addTableStyle('myTable2', $styleTable);
		    $table = $section->addTable('myTable3');
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("Supervisor Escolar",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("Diretor",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText($nomesupervisor,$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText($nomediretor,$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("Secretário",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText($nomesecretario,$fontStyle);
		
		}
	    $turma = $oTurma->getEtapas();
		
		$xurma = $oTurma->getEtapas();
		$xurma = $xurma[0];
		$xiclo = $xurma->getEtapa()->getNome();
		if( $xxnomeetapa == "EF ANOS INICIAIS"){ // aqui aconteceu um erro esquisito $xxnomeetapa == "EF ANOS INICIAIS, não existe abaixo
			$xxnomeetapa = "E F ANOS INICIAIS";
		}
    }
  
    if($xxnomeetapa == "EDUCAÇÃO INFANTIL PRÉ-ESCOLA"){
//*************************************************************************************************************************************************************************************		  
		//cabecalhoPadrao($phpWord, $section, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iCodigoEtapa);
    }	

    if($xxnomeetapa == "EDUCAÇÃO INFANTIL CRECHE"){
//*************************************************************************************************************************************************************************************		  
//        cabecalhoPadrao($phpWord, $section, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iCodigoEtapa);
    }	
	
    if($xxnomeetapa == "E F ANOS INICIAIS" && $xserie2 == "1º ANO"){
		
//        cabecalhoPadrao($phpWord, $section, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iCodigoEtapa);
        $table->addRow(25);
		$table->addCell(1000, $combordas)->addText('  Nº',$fontStyle);
		$table->addCell(17000,$combordas)->addText('     Nome do Aluno',$fontStyle);
		$table->addCell(4000, $combordas)->addText('  Frequência Anual %',$fontStyle);
		$table->addCell(4000, $combordas)->addText('     Resultado',$fontStyle);
        $aListaDeAlunos = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);
		$codregencia =  $aDisciplinas[0]->getCodigo();
		$dadosregencia = dadosRegencia($codregencia);    
		$xserie = $dadosregencia[0]["ed59_i_serie"];
		$xturma = $dadosregencia[0]["ed59_i_turma"];    
//		$diasletivos = $aDisciplinas[0]->getTurma()->getCalendario()->getDiasLetivos();   //*******************   DIAS LETIVOS     
		$diasletivos = retornaaulasdadas($codregencia);	
		
		$contador = 1;
		foreach ($aListaDeAlunos as $aluno){
			$numero = $aluno->getNumeroOrdemAluno();
			$nomealuno = $aluno->getAluno()->getNome();
			$datanascimento = implode("/", array_reverse(explode("-", $aluno->getAluno()->getDataNascimento())));
			$matriculaaluno = $aluno->getMatricula();
			$codaluno = $aluno->getAluno()->getCodigoAluno();      
			$rf = buscaRFeja($codaluno, $xturma, $xserie);      
			$faltasaluno = buscaFaltasAnosIniciais($codregencia, $codaluno);
			$frequencia  = floor(($diasletivos - $faltasaluno) / $diasletivos * 100);
			if($frequencia <75)
			{
				$frequencia =75;
			}
			if($aluno->getSituacao() != "MATRICULADO"){
				$oDtEncerramento = $aluno->getDataEncerramento();
				$table->addRow(25);
				$table->addCell(1000, $combordas)->addText('  '.$numero,$fontStyle);
				$table->addCell(17000,$combordas)->addText('  '.$nomealuno,$fontStyle);
				$table->addCell(4000, $combordas)->addText('  ',$fontStyle);
				$table->addCell(4000, $combordas)->addText('     '.$aluno->getSituacao().' em: '.$oDtEncerramento,$fontStyle);
				
			}else{
				$table->addRow(25);
				$table->addCell(1000, $combordas)->addText('  '.$numero,$fontStyle);
				$table->addCell(17000,$combordas)->addText('  '.$nomealuno,$fontStyle);
				$table->addCell(4000, $combordas)->addText('                '.$frequencia,$fontStyle);
				$table->addCell(4000, $combordas)->addText('                '.$rf,$fontStyle);
			} 	  
			$contador++;
		}
        $myTextElement  = $section->addText('');		
		$myTextElement  = $section->addText('');
		$linha_impressa = "E para constar, eu _______________________________________, lavrei a presente Ata que vai assinada pelas";
		$myTextElement  = $section->addText($linha_impressa,$fontStyle);
		$myTextElement  = $section->addText('');
		$linha_impressa = "autoridades competentes.";
		$myTextElement  = $section->addText($linha_impressa,$fontStyle);
		
    }elseif($xxnomeetapa == "E F ANOS INICIAIS" && ($xserie2 == "2º ANO" || $xserie2 == "3º ANO" || $xserie2 == "4º ANO" || $xserie2 == "5º ANO")){  //CORPO FUNDAMENTAL ANOS INICIAIS
//****************************************************************************	mudar

    $reduzcoluna = 2;    
    foreach ($aDisciplinas as $oDisciplina) {
        if ( !$oDisciplina->isLancadaNoHistorico() ) {continue;}
        $aDisciplinasPorPagina[$iPagina][$iContadorAux] = $oDisciplina;
        $iTotalContadorAux = 6;
      
      //Verificamos se foi selecionado algum tipo de frequencia. Caso nao (1), aumentamos a quantidade do contador auxiliar para validar ate 9     
        if ($oFiltroRelatorio->iFrequencia == 1 || $oFiltroRelatorio->lCalculaFrequencia == 2) {
            $iTotalContadorAux = 9;
        }
        if ($iContadorAux >= $iTotalContadorAux) {
            $iPagina ++;
            $iContadorAux = -1;
        }
        $iContadorAux++;
    }
    //Variavel a ser utilizada no laco para impressao das disciplinas  
    $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinasPorPagina;
    $aListaDeAlunos = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);
    
     
    switch($oFiltroRelatorio->iOrdenacao) {
        case 2:
        case 3:
            usort($aListaDeAlunos, "ordernarAlunosPorNome");
            break;
    }
  
    if ($oFiltroRelatorio->iOrdenacao == 2) {
        $lSequencialDiario = false;
    }
    
    //Armazenamos o total de alunos matriculados na turma   
    $iTotalAlunosMatriculados  = count($aListaDeAlunos);
    $iAlunosImpressos          = 0;
    $lPrimeiroLaco             = true;
    //Variavel para o controle da impressão do nº quando a ordenação for sequencial  
    $iContadorSequencial = 0;
	$combordas = array(''=>'','borderSize'=>6, 'cellMarginTop'=>100,'valign'=>'center');  
	$fontStyle = new \PhpOffice\PhpWord\Style\Font();
	$fontStyle->setBold(false);
	$fontStyle->setName('Arial');
	$fontStyle->setSize(7);
  
	$styleTable = array('cellMarginTop'=>100);
	$phpWord->addTableStyle('myTable2', $styleTable);
	$table = $section->addTable('myTable2');

		foreach ($aDisciplinasPorPagina as $iDisciplina => $aDisciplinasPagina) {
			$iPreenchimento = 0;
			$lPulouAluno    = false;
			for ($iContadorAluno = 0; $iContadorAluno < $iTotalAlunosMatriculados; $iContadorAluno++) {
				if (isset($oFiltroRelatorio->sLimiteMovimentacao) && !empty($oFiltroRelatorio->sLimiteMovimentacao) && $aListaDeAlunos[$iContadorAluno]->getDataEncerramento() != null){
				  $oFiltroRelatorio->sLimiteMovimentacao = $oFiltroRelatorio->sLimiteMovimentacao . "/" . $oTurma->getCalendario()->getAnoExecucao();
				  $oDataLimiteMovimentacao               = new DBDate( $oFiltroRelatorio->sLimiteMovimentacao );
				  if(DBDate::calculaIntervaloEntreDatas( $oDataLimiteMovimentacao, $aListaDeAlunos[$iContadorAluno]->getDataEncerramento(), 'd' ) > 0 ) {
					$lPulouAluno = true;
					$iAlunosImpressos++;
					continue;
				  }
				}
                $sNomeAluno                = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
				
				$iPreenchimento = $iContadorAluno;
				if($lPulouAluno){$iPreenchimento++;}
				$sBordaAluno = "LR";
				if ( $oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4 ) {
				  if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina - 1) {
					$sBordaAluno = "LRB";
				  }
				}
				
				$lQuebrouPagina = false;
				$iCorLinha = 0;
				if ($iPreenchimento % 2 == 0) {
				  $iCorLinha = 1;
				}      

				//Verificamos se o numero de alunos por pagina foi atingido      
				if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina ) {
				  //footerPadrao($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
                  $myTextElement  = $section->addPageBreak();
				  $lQuebrouPagina = true;
				}

				if ($iAlunosImpressos >= $iTotalAlunosMatriculados) {
				  //footerPadrao($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
                  $myTextElement  = $section->addPageBreak();
				  $lQuebrouPagina      = true;
				  $iContadorSequencial = 0;
				  $iAlunosImpressos    = 0;
				}

				if( $iAlunosImpressos == 55 ) {
				  //footerPadrao($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
                  $myTextElement  = $section->addPageBreak();
				  $lQuebrouPagina      = true;
				  $iAlunosImpressos    = 0;
				}
				$iAlunosImpressos++;      
				//Verificamos que houve quebra de pagina ou se entrou no laco pela primeira vez      
				if ($lQuebrouPagina || $lPrimeiroLaco) {
				    if ($oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4) {
					    //cabecalhoScpf($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
				    }
				    $lPrimeiroLaco = false;
					$table->addRow();
					$table->addCell(1000, $combordas)->addText(' Nº',$fontStyle);
					$table->addCell(10000,$combordas)->addText(' Nome do Aluno',$fontStyle);

					if ($oFiltroRelatorio->iTotalDisciplinas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
					  $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinas;
					}
					$oFiltroRelatorio->iColunasEmBranco = 0;
					if ($oFiltroRelatorio->iContadorDisciplinasImpressas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
					  $oFiltroRelatorio->iColunasEmBranco = $oFiltroRelatorio->iTotalDisciplinasPorPagina - $oFiltroRelatorio->iContadorDisciplinasImpressas;
					}  
					$koluna = 9;
					$kamanho = 100;
					$disciplinasNome = array();

					for ($iContadorDisciplinas = 0; $iContadorDisciplinas <= count($aDisciplinasPagina);  $iContadorDisciplinas++) {
						if (!array_key_exists ($iContadorDisciplinas, $aDisciplinasPagina)) {break;}
						if ($iContadorDisciplinas == $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
							$myTextElement  = $section->addPageBreak();
							$lQuebrouPagina = true;
						}
						$table->addCell(1000,$combordas)->addText(substr($aDisciplinasPagina[$iContadorDisciplinas]->getDisciplina()->getNomeDisciplina(),0,5),$fontStyle);   
					}		
					$table->addCell(1000,$combordas)->addText('Freq%',$fontStyle);
					$table->addCell(1000,$combordas)->addText('Resul',$fontStyle);
					$lQuebrouPagina = false;					  
					$linha = 0;
				}
				
				if ($oFiltroRelatorio->iTrocaTurma == 1 && $aListaDeAlunos[$iContadorAluno]->getSituacao() == "TROCA DE TURMA") {
				    continue;
				}
				if ($lSequencialDiario) {
                    $table->addRow(25);
				   	$table->addCell(1000, $combordas)->addText('  '.$aListaDeAlunos[$iContadorAluno]->getNumeroOrdemAluno(),$fontStyle);
				} else {
					$table->addRow(25);
				   	$table->addCell(1000, $combordas)->addText('  ',$fontStyle);
				}
				$table->addCell(10000,$combordas)->addText('  '.$sNomeAluno,$fontStyle);
				
				$sAproveitamento               = '';
				$sPercentualFrequencia         = '';
				$iNumeroFaltas                 = '';
				$sResultadoFinal               = '';
				$iContadorDisciplinasImpressas = 0;
				$sResultadoGeral               = 'A';
				if ($aListaDeAlunos[$iContadorAluno]->getSituacao() != "MATRICULADO") {
					
					
				    $oDtEncerramento = $aListaDeAlunos[$iContadorAluno]->getDataEncerramento();
				    $sDtEncerramento = "";
				    if (!empty($oDtEncerramento)) {
					    $sDtEncerramento = $oDtEncerramento->convertTo(DBDate::DATA_PTBR);
				    }
					$fontStyle3 = new \PhpOffice\PhpWord\Style\Font();
					$fontStyle3->setBold(false);
					$fontStyle3->setName('Arial');
					$fontStyle3->setSize(6);

				    //$sTransferido = $aListaDeAlunos[$iContadorAluno]->getSituacao() . " {$sDtEncerramento}";
				    $sTransferido = $aListaDeAlunos[$iContadorAluno]->getSituacao();
				    if($sTransferido == "TRANSFERIDO FORA"){
					    $sTransferido = explode(" ", $sTransferido);
					    $sTransferido = $sTransferido[0];          
				    }
					$table->addCell(1000)->addText("    ".substr($sTransferido,0,7),$fontStyle3);
					$table->addCell(1000)->addText(substr($sTransferido,7,5),$fontStyle3);
					$table->addCell(1000)->addText(substr($sTransferido,11,5),$fontStyle3);
					$table->addCell(1000)->addText(substr($sTransferido,16,5)." em:",$fontStyle3);
				    $iLinha       = ($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina * $oFiltroRelatorio->iAuxiliarTransferido) + 12;
					$table->addCell(1000)->addText(" ".$sDtEncerramento,$fontStyle3);
					continue;

				} else {
					
				    $lAprovadoProgressaoAno = false;
				    foreach( $aListaDeAlunos[$iContadorAluno]->getAluno()->getProgressaoParcial() as $oProgressaoParcial ) {
		  
					    if(    $oTurma->getCalendario()->getAnoExecucao() == $oProgressaoParcial->getAno()
						&& $oProgressaoParcial->getCodigoDiarioFinal() != null
					    ) 
						{
					        $lAprovadoProgressaoAno = true;
					    }
				    }
				    $iTotalDeAulasDadas      = 0;
				    $nTotalDeFaltas          = 0;
				    $iTotalDeAulasDadasGeral = 0;
				    foreach ($aDisciplinasPagina as $oRegenciaTurma) {
					    db_inicio_transacao();
						$oRegencia = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()
																	 ->getDisciplinasPorRegencia($oRegenciaTurma);
						$sAmparado = '';
						if ($oRegencia->getAmparo() != null && $oRegencia->getAmparo()->isTotal() ) {
						    if ($oRegencia->getAmparo()->getCodigoConvencaoAmparo()) {
								$oDaoConvencaoAmparo = new cl_convencaoamp();
								$sSqlConvencaoAmparo = $oDaoConvencaoAmparo->sql_query_file($oRegencia->getAmparo()->getCodigoConvencaoAmparo());
								$rsConvencaoAmparo   = $oDaoConvencaoAmparo->sql_record($sSqlConvencaoAmparo);
								$oConvencaoAmparo    = db_utils::fieldsMemory($rsConvencaoAmparo, 0);
								$sAmparado           = $oConvencaoAmparo->ed250_c_abrev;
								$oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oConvencaoAmparo->ed250_c_abrev.' - '.$oConvencaoAmparo->ed250_c_descr;						
							}
							if ($oRegencia->getAmparo()->getCodigoJustificativa()) {
								$sAmparado = 'AMP '.$oRegencia->getAmparo()->getCodigoJustificativa();
								$oDaoJustificativa = new cl_justificativa();
								$sSqlJustificativa = $oDaoJustificativa->sql_query_file($oRegencia->getAmparo()->getCodigoJustificativa());
								$rsJustitificativa = $oDaoJustificativa->sql_record($sSqlJustificativa);
								$oDadosJustificativa = db_utils::fieldsMemory($rsJustitificativa, 0);
								$oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oRegencia->getAmparo()->getCodigoJustificativa().' - '.$oDadosJustificativa->ed06_c_descr;
							}
						}
						$iNumeroFaltas            = $oRegencia->getTotalFaltas();
						$iTotalDeAulasDadas       = $oRegencia->getTotalDeAulasParaCalculo();
						$iTotalDeAulasDadasGeral += $oRegencia->getTotalDeAulasParaCalculo();
						if ($oFiltroRelatorio->lCalculaFrequencia == 2) {
						  $nTotalDeFaltas += $iNumeroFaltas;
						}
						db_fim_transacao();
						$iCodigoEnsino   = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
						$oResultadoFinal = $oRegencia->getResultadoFinal();
						/**
						 * Valor do resultado de aprovacao
						 */
						$nValorAproveitamento = $oResultadoFinal->getValorAprovacao();
						if(    $oResultadoFinal->getFormaAprovacaoConselho() instanceof AprovacaoConselho
							&& $oResultadoFinal->getFormaAprovacaoConselho()->getFormaAprovacao() == 1
							&& $oResultadoFinal->getFormaAprovacaoConselho()->getAlterarNotaFinal() == 2
						  ) {
						     $nValorAproveitamento = $oResultadoFinal->getFormaAprovacaoConselho()->getAvaliacaoConselho();
						}
						$oFormaAvaliacao = $oResultadoFinal->getResultadoAvaliacao()->getFormaDeAvaliacao();
						if (!empty($oFormaAvaliacao) && $oFormaAvaliacao->getTipo() == "PARECER") {
						    $nValorAproveitamento = $oResultadoFinal->getResultadoAprovacao();
						    if (!empty($iCodigoEnsino) && ($nValorAproveitamento == 'A' || $nValorAproveitamento == 'R')) {
							    $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $nValorAproveitamento, $iAnoCalendario);
							    if (isset($aDadosTermo[0])) {
							      $nValorAproveitamento = $aDadosTermo[0]->sAbreviatura;
							    }
						    }
						}
						if (is_numeric($nValorAproveitamento)) {
						    $nValorAproveitamento  = ArredondamentoNota::formatar($nValorAproveitamento,
							 													  $oTurma->getCalendario()->getAnoExecucao()
																			     );
						}
						$sPercentualFrequencia = $oRegencia->calcularPercentualFrequencia();
						
						$sResultadoAprovacao = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->getResultadoFinal();
						if ($sResultadoAprovacao == 'R' && !$lAprovadoProgressaoAno) {
							$sResultadoGeral = 'R';
						}
						if (!empty($iCodigoEnsino) && ($sResultadoGeral == 'A' || $sResultadoGeral == 'R')) {
							$aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $sResultadoGeral, $iAnoCalendario);
							if (isset($aDadosTermo[0])) {
								$sResultadoGeral = $aDadosTermo[0]->sAbreviatura;
							}
						}
						$oAprovConselho = $oResultadoFinal->getFormaAprovacaoConselho();
						if (!empty($oAprovConselho)) {
						
						}
						if ($sAmparado != '') {
						  $nValorAproveitamento = $sAmparado;
						}
						//NOTA COM VÍRGULA
						$nValorAproveitamento = str_replace(".", ",", $nValorAproveitamento);
						//var_dump($nValorAproveitamento); die("Confere");
						
						if($nValorAproveitamento=='Parecer'){
							$nValorAproveitamento='Rel';
						}
                        if ($oFiltroRelatorio->lCalculaFrequencia == 1 && $oFiltroRelatorio->iFrequencia != 1) {
							$table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorAproveitamento,$fontStyle);
						    $nValorFalta = !empty($iNumeroFaltas) ? $iNumeroFaltas : "" ;
						    if ($oFiltroRelatorio->iFrequencia == 2) {
							    $nValorFalta =  $sPercentualFrequencia;
						    }
						    if ($oFiltroRelatorio->iFrequencia == 4) {
							    $nValorFalta  = $iTotalDeAulasDadas - $iNumeroFaltas;
						    }
						    if ($oRegencia->reclassificadoPorBaixaFrequencia()) {
							//$nValorFalta = '--';
								if( $nValorFalta < '75')
								{ 	   
									$nValorFalta = '75';
								}   
						    }
			  
						    if ($oRegencia->getRegencia()->getFrequenciaGlobal()  == 'A') {
							//$nValorFalta = '-';
								if( $nValorFalta < '75')
								{ 	   
									$nValorFalta = '75';
								}   
						    }
                            $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorFalta,$fontStyle);						
						}else{
					  	    $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorAproveitamento,$fontStyle);						
						}
						$iContadorDisciplinasImpressas++;
					} // foreach de disciplinas
					
					
				    if(($sResultadoGeral == "APR") or ($sResultadoGeral == "Apr")){
					    $sResultadoGeral = "AP";
				    }
				    if( $lAprovadoProgressaoAno ) {  
					    $lObservacaoProgressaoParcial  = true;
					    $sResultadoGeral              .= '/D';
				    }
           //Verificamos a frequencia para alinhamento do resultado final de cada aluno
          
                    if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
						$nValorFaltas = $sPercentualFrequencia;
						
						if ($oFiltroRelatorio->iFrequencia == 3) {
						  $nValorFaltas = !empty($nTotalDeFaltas) ? $nTotalDeFaltas : "" ;
						}  
						if ($oFiltroRelatorio->iFrequencia == 4) {
						  $nValorFaltas  = $iTotalDeAulasDadasGeral - $nTotalDeFaltas;
						}  
						if ($aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia()) {
						   if( $nValorFaltas < '75')
						   { 	   
							   $nValorFaltas = '75';
						   }   
						}  
						if ($oRegencia->getRegencia()->getFrequenciaGlobal()  == 'A') {
						   if( $nValorFaltas < '75')
						   { 	   
							  $nValorFaltas = '75';
						   }  
						}
					    $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorFaltas,$fontStyle);
					    $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$sResultadoGeral,$fontStyle);
                    } else {
					    $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$sResultadoGeral,$fontStyle);
                    }
					
                    $linha++;
                } // fim do if de impressão
				
				
			}// fim do for
		}// fim do foreach
		
		$myTextElement  = $section->addText('');
		$styleTable = array('cellMarginTop'=>100);
		$phpWord->addTableStyle('myTable3', $styleTable);
		$table = $section->addTable('myTable3');




		if ($oFiltroRelatorio->iTipoModelo != 2) {
			$sObservacoesEscola = $oFiltroRelatorio->sObservacao;
			
			if ($sObservacoesEscola != null) {
				$myTextElement  = $section->addText('');
				$table->addRow(25);
				$table->addCell(20000,$sembordas)->addText("Observações:",$fontStyle);
				if (mb_detect_encoding($sObservacoesEscola . 'x', 'UTF-8', 'ISO-8859-1') == 'UTF-8') {
					$sObservacoesEscola = utf8_decode($sObservacoesEscola);
					$sObservacoesEscola = str_replace(",", ',', $sObservacoesEscola);
				} else {
					$sObservacoesEscola = str_replace(",", ',', $sObservacoesEscola);
				}
				$table->addRow(25);
				$table->addCell(20000,$sembordas)->addText($sObservacoesEscola,$fontStyle);
			}
		
		
			//assinatura aqui
			$myTextElement  = $section->addText('');
			$myTextElement  = $section->addText('');
			$linha_impressa = "E para constar, eu _______________________________________, lavrei a presente Ata que vai assinada pelas";
			$myTextElement  = $section->addText($linha_impressa);
			$linha_impressa = "autoridades competentes.";
			$myTextElement  = $section->addText($linha_impressa);

			$nomediretor = $_GET["diretor"];
			$nomediretor = explode("|", $nomediretor);
			$nomediretor = $nomediretor[1];
			$nomediretor = trataNome($nomediretor);

			$nomesecretario = $_GET["secretario"];
			$nomesecretario = explode("|", $nomesecretario);
			$nomesecretario = $nomesecretario[1];
			$nomesecretario = trataNome($nomesecretario);

			if(isset($oFiltroRelatorio->iRegente)){
			  $xDocente       = DocenteRepository::getDocenteByCodigoRecursosHumano($oFiltroRelatorio->iRegente);
			  $nomesupervisor   = trataNome($xDocente->getNome());
			}else{
			  $nomesupervisor = "";
			}
			$myTextElement  = $section->addText('');
		    $styleTable = array('cellMarginTop'=>100);
		    $phpWord->addTableStyle('myTable2', $styleTable);
		    $table = $section->addTable('myTable3');
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("Supervisor Escolar",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("Diretor",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText($nomesupervisor,$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText($nomediretor,$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("Secretário",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText($nomesecretario,$fontStyle);
		
		}		
//        $myTextElement  = '';
//		$myTextElement  = $section->addText('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        
//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	}elseif($xxnomeetapa == "E.F. ANOS FINAIS"){  //CORPO FUNDAMENTAL ANOS FINAIS //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
	
		$reduzcoluna = 2;    
		foreach ($aDisciplinas as $oDisciplina) {
		  if ( !$oDisciplina->isLancadaNoHistorico() ) {continue;}
		  $aDisciplinasPorPagina[$iPagina][$iContadorAux] = $oDisciplina;
		  $iTotalContadorAux = 6;
		  
		  //Verificamos se foi selecionado algum tipo de frequencia. Caso nao (1), aumentamos a quantidade do contador auxiliar para validar ate 9     
		  if ($oFiltroRelatorio->iFrequencia == 1 || $oFiltroRelatorio->lCalculaFrequencia == 2) {
			$iTotalContadorAux = 9;
		  }
		  if ($iContadorAux >= $iTotalContadorAux) {
			$iPagina ++;
			$iContadorAux = -1;
		  }
		  $iContadorAux++;
		}  
		//Variavel a ser utilizada no laco para impressao das disciplinas  
		$oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinasPorPagina;
		$aListaDeAlunos = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);
	
		switch($oFiltroRelatorio->iOrdenacao) {
		  case 2:
		  case 3:
			usort($aListaDeAlunos, "ordernarAlunosPorNome");
			break;
		}
	  
		if ($oFiltroRelatorio->iOrdenacao == 2) {
		  $lSequencialDiario = false;
		}
		$tamanhoDisciplina = 1000;
		//Armazenamos o total de alunos matriculados na turma   
		$iTotalAlunosMatriculados  = count($aListaDeAlunos);
		$iAlunosImpressos          = 0;
		$lPrimeiroLaco             = true;
        $linha = 31;
	  
		
        foreach ($aDisciplinasPorPagina as $iDisciplina => $aDisciplinasPagina) {	
			$iPreenchimento = 0;
			$lPulouAluno    = false;
			//***************************************************************************************************************************** 
			for ($iContadorAluno = 0; $iContadorAluno < $iTotalAlunosMatriculados; $iContadorAluno++) {//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
			//Valida se nos parâmetros globais, foi configurada uma data limite para movimentação. Caso tenha sido, e a data de saída da matrícula é menor que esta data, não apresenta o aluno no relatório       
				if (isset($oFiltroRelatorio->sLimiteMovimentacao) && !empty($oFiltroRelatorio->sLimiteMovimentacao) && $aListaDeAlunos[$iContadorAluno]->getDataEncerramento() != null){
				  $oFiltroRelatorio->sLimiteMovimentacao = $oFiltroRelatorio->sLimiteMovimentacao . "/" . $oTurma->getCalendario()->getAnoExecucao();
				  $oDataLimiteMovimentacao               = new DBDate( $oFiltroRelatorio->sLimiteMovimentacao );
				  if(DBDate::calculaIntervaloEntreDatas( $oDataLimiteMovimentacao, $aListaDeAlunos[$iContadorAluno]->getDataEncerramento(), 'd' ) > 0 ) {
					$lPulouAluno = true;
					$iAlunosImpressos++;
					continue;
				  }
				}
				if( $linha >= 30 ) // cabecalho - para o arquivo foi gerado aqui, porque a sessão muda para ultima linha impressa
				{
					$table->addRow();
					$table->addCell(1000, $combordas)->addText(' Nº',$fontStyle);
					$table->addCell(10000,$combordas)->addText(' Nome do Aluno',$fontStyle);

					if ($oFiltroRelatorio->iTotalDisciplinas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
					  $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinas;
					}
					$oFiltroRelatorio->iColunasEmBranco = 0;
					if ($oFiltroRelatorio->iContadorDisciplinasImpressas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
					  $oFiltroRelatorio->iColunasEmBranco = $oFiltroRelatorio->iTotalDisciplinasPorPagina - $oFiltroRelatorio->iContadorDisciplinasImpressas;
					}  
					$koluna = 9;
					$kamanho = 100;
					$disciplinasNome = array();

					for ($iContadorDisciplinas = 0; $iContadorDisciplinas <= count($aDisciplinasPagina);  $iContadorDisciplinas++) {
						if (!array_key_exists ($iContadorDisciplinas, $aDisciplinasPagina)) {break;}
						if ($iContadorDisciplinas == $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
							$myTextElement  = $section->addPageBreak();
							$lQuebrouPagina = true;
						}
						$table->addCell(1000,$combordas)->addText(substr($aDisciplinasPagina[$iContadorDisciplinas]->getDisciplina()->getNomeDisciplina(),0,5),$fontStyle);   
					}		
					$table->addCell(1000,$combordas)->addText('Freq%',$fontStyle);
					$table->addCell(1000,$combordas)->addText('Resul',$fontStyle);
					$lQuebrouPagina = false;					  
					$linha = 0;
				}
				
				$situacaoaluno =  $aListaDeAlunos[$iContadorAluno]->getSituacao();
//    echo "<pre>";
//    print_r($aListaDeAlunos);
//    echo "</pre>";
				
				$sNome         = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
				if($situacaoaluno != "MATRICULADO"){ // se o aluno evadiu/transferiu ou saiu por qualquer motivo
					$oDtEncerramento = $aListaDeAlunos[$iContadorAluno]->getDataEncerramento();
					$table->addRow();
					$table->addCell(750, $combordas)->addText('  '.$aListaDeAlunos[$iContadorAluno]->getNumeroOrdemAluno(),$fontStyle);
					
					$sNomeAluno  = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
					
					$codigoaluno = $aListaDeAlunos[$iContadorAluno]->getAluno()->getCodigoAluno();
					
					$table->addCell(7200,$combordas)->addText('  '.$sNomeAluno,$fontStyle);
					$fontStyle3 = new \PhpOffice\PhpWord\Style\Font();
					$fontStyle3->setBold(false);
					$fontStyle3->setName('Arial');
					$fontStyle3->setSize(6);

                    $sTransferido = $aListaDeAlunos[$iContadorAluno]->getSituacao();

					if( $sTransferido == 'TROCA DE TURMA')
					{
						$table->addCell(1000)->addText("  ".substr($sTransferido,0,5),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,5,4),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,8,6),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,16,5)." em:",$fontStyle3);
					}else{
						$table->addCell(1000)->addText("  ".substr($sTransferido,0,7),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,7,5),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,11,5),$fontStyle3);
						$table->addCell(1000)->addText(substr($sTransferido,16,5)." em:",$fontStyle3);
					}	
				    $iLinha       = ($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina * $oFiltroRelatorio->iAuxiliarTransferido) + 12;
					$table->addCell(1000)->addText(" ".$oDtEncerramento,$fontStyle3);

//					$table->addCell(15000,$combordas)->addText($aListaDeAlunos[$iContadorAluno]->getSituacao().' em: '.$oDtEncerramento,$fontStyle);
					continue; // passa para o próximo aluno nessa situação
					
				}else{
                    $sNomeAluno                = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());				
					$oFiltroRelatorio->iAltura = $iLinhasAluno * 4;
					$iPreenchimento = $iContadorAluno;
					if($lPulouAluno){$iPreenchimento++;}
					$sBordaAluno = "LR";
					if ( $oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4 ) {
					  if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina - 1) {
						$sBordaAluno = "LRB";
					  }
					}

					$lQuebrouPagina = false;

					//Verificamos se o numero de alunos por pagina foi atingido      
					if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina ) {
					  //footerPadrao($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
//					  $myTextElement  = $section->addPageBreak();
					  $lQuebrouPagina = true;
					}

					if ($iAlunosImpressos >= $iTotalAlunosMatriculados) {
						
					  $lQuebrouPagina      = true;
					  $iContadorSequencial = 0;
					  $iAlunosImpressos    = 0;
//					  $myTextElement  = $section->addPageBreak();
					  
					}

					$iAlunosImpressos++;      
					if( $iAlunosImpressos++ > 55 ) {
						$iAlunosImpressos    = 0;
					    $lQuebrouPagina      = true;						
					    $myTextElement  = $section->addPageBreak();
					}
                    $iAlunosImpressos++;
					
					//Verificamos que houve quebra de pagina ou se entrou no laco pela primeira vez      
					if ($oFiltroRelatorio->iTrocaTurma == 1 && $aListaDeAlunos[$iContadorAluno]->getSituacao() == "TROCA DE TURMA") {
					  continue;
					}
					
			        $table->addRow();
					if ($lSequencialDiario) {
					    $table->addCell(1000, $combordas)->addText('  '.$aListaDeAlunos[$iContadorAluno]->getNumeroOrdemAluno(),$fontStyle);
					} else {
  					    $table->addCell(1000, $combordas)->addText('  '.++$iContadorSequencial,$fontStyle);
					}

                    $table->addCell(10000,$combordas)->addText('  '.$sNomeAluno,$fontStyle);
					$linha++;
					
					// * Buscamos os dados do resultado final
					
					$sAproveitamento               = '';
					$sPercentualFrequencia         = '';
					$iNumeroFaltas                 = '';
					$NumeroFaltas                  = 0;
					$sResultadoFinal               = '';
					$iContadorDisciplinasImpressas = 0;
					$sResultadoGeral               = 'A';

					
					 //* Imprimimos a situacao do aluno na linha, caso ele tenha sido transferido
					
					if ($aListaDeAlunos[$iContadorAluno]->getSituacao() != "MATRICULADO") {
			  
					    $oDtEncerramento = $aListaDeAlunos[$iContadorAluno]->getDataEncerramento();
					    $sDtEncerramento = "";
					    if (!empty($oDtEncerramento)) {
						    $sDtEncerramento = " em " .$oDtEncerramento->convertTo(DBDate::DATA_PTBR);
					    }
			  
					    //$sTransferido = $aListaDeAlunos[$iContadorAluno]->getSituacao() . " {$sDtEncerramento}";
					    $sTransferido = $aListaDeAlunos[$iContadorAluno]->getSituacao();
					    if($sTransferido == "TRANSFERIDO FORA"){
						    $sTransferido = explode(" ", $sTransferido);
						    $sTransferido = $sTransferido[0];          
					    }
					    $iLinha       = ($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina * $oFiltroRelatorio->iAuxiliarTransferido) + 12;
					    $table->addCell(7000,$combordas)->addText('  '.$sTransferido.$oDtEncerramento,$fontStyle);
						continue;
					} else {

					    
					    //Verifica se o aluno foi aprovado com progressão parcial
					    
					    $lAprovadoProgressaoAno = false;
					    foreach( $aListaDeAlunos[$iContadorAluno]->getAluno()->getProgressaoParcial() as $oProgressaoParcial ) {
							if(    $oTurma->getCalendario()->getAnoExecucao() == $oProgressaoParcial->getAno()
								&& $oProgressaoParcial->getCodigoDiarioFinal() != null
							  ) {
							  $lAprovadoProgressaoAno = true;
							}
					    }
						
					    $iTotalDeAulasDadas      = 0;
					    $nTotalDeFaltas          = 0;
					    $iTotalDeAulasDadasGeral = 0;
						foreach ($aDisciplinasPagina as $oRegenciaTurma) {
							db_inicio_transacao();
							$oRegencia = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()
																		 ->getDisciplinasPorRegencia($oRegenciaTurma);
							$sAmparado = '';
							if ($oRegencia->getAmparo() != null && $oRegencia->getAmparo()->isTotal() ) {
 				                if ($oRegencia->getAmparo()->getCodigoConvencaoAmparo()) {
									$oDaoConvencaoAmparo = new cl_convencaoamp();
									$sSqlConvencaoAmparo = $oDaoConvencaoAmparo->sql_query_file($oRegencia->getAmparo()->getCodigoConvencaoAmparo());
									$rsConvencaoAmparo   = $oDaoConvencaoAmparo->sql_record($sSqlConvencaoAmparo);
									$oConvencaoAmparo    = db_utils::fieldsMemory($rsConvencaoAmparo, 0);
									$sAmparado           = $oConvencaoAmparo->ed250_c_abrev;
									$oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oConvencaoAmparo->ed250_c_abrev.' - '.$oConvencaoAmparo->ed250_c_descr;
								}
							    if ($oRegencia->getAmparo()->getCodigoJustificativa()) {
								    $sAmparado = 'AMP '.$oRegencia->getAmparo()->getCodigoJustificativa();
								    $oDaoJustificativa = new cl_justificativa();
								    $sSqlJustificativa = $oDaoJustificativa->sql_query_file($oRegencia->getAmparo()->getCodigoJustificativa());
								    $rsJustitificativa = $oDaoJustificativa->sql_record($sSqlJustificativa);
								    $oDadosJustificativa = db_utils::fieldsMemory($rsJustitificativa, 0);
								    $oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oRegencia->getAmparo()->getCodigoJustificativa().' - '.$oDadosJustificativa->ed06_c_descr;
							    }
							}

							$iNumeroFaltas             =  $oRegencia->getTotalFaltas();
						    $NumeroFaltas             += $oRegencia->getTotalFaltas();
							$iTotalDeAulasDadas        = $oRegencia->getTotalDeAulasParaCalculo();
							$iTotalDeAulasDadasGeral  += $oRegencia->getTotalDeAulasParaCalculo();
							
							if ($oFiltroRelatorio->lCalculaFrequencia == 2) {
							  $nTotalDeFaltas += $iNumeroFaltas;
							}
							db_fim_transacao();
							$iCodigoEnsino   = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
							$oResultadoFinal = $oRegencia->getResultadoFinal();
							
							// * Valor do resultado de aprovacao
							
							$nValorAproveitamento = $oResultadoFinal->getValorAprovacao();
							
							if(    $oResultadoFinal->getFormaAprovacaoConselho() instanceof AprovacaoConselho
								&& $oResultadoFinal->getFormaAprovacaoConselho()->getFormaAprovacao() == 1
								&& $oResultadoFinal->getFormaAprovacaoConselho()->getAlterarNotaFinal() == 2
							  ) {
							    $nValorAproveitamento = $oResultadoFinal->getFormaAprovacaoConselho()->getAvaliacaoConselho();
							}
							if(    $oResultadoFinal->getFormaAprovacaoConselho() instanceof AprovacaoConselho
								&& $oResultadoFinal->getFormaAprovacaoConselho()->getFormaAprovacao() == 1
								&& $oResultadoFinal->getFormaAprovacaoConselho()->getAlterarNotaFinal() == 2
							  ) {
							    $nValorAproveitamento = $oResultadoFinal->getFormaAprovacaoConselho()->getAvaliacaoConselho();
							}
							
							// * Se for parecer devemos utilizar o resultado da aprovacao do aluno
							
							$oFormaAvaliacao = $oResultadoFinal->getResultadoAvaliacao()->getFormaDeAvaliacao();
							if (!empty($oFormaAvaliacao) && $oFormaAvaliacao->getTipo() == "PARECER") {
				  
							    $nValorAproveitamento = $oResultadoFinal->getResultadoAprovacao();
							    if (!empty($iCodigoEnsino) && ($nValorAproveitamento == 'A' || $nValorAproveitamento == 'R')) {
				  
								    $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $nValorAproveitamento, $iAnoCalendario);
								    if (isset($aDadosTermo[0])) {
								        $nValorAproveitamento = $aDadosTermo[0]->sAbreviatura;
								    }
							    }
							}
							
							// * Se for uma nota o valor do aproveitamento devemos aplicar as regras de arrendondamento
							
							if (is_numeric($nValorAproveitamento)) {
							    $nValorAproveitamento  = ArredondamentoNota::formatar($nValorAproveitamento,
								   													$oTurma->getCalendario()->getAnoExecucao()
																				   );
							}
							$sPercentualFrequencia = $oRegencia->calcularPercentualFrequencia();

							
							// * Antes estava buscando o RF da disciplina.
							// * Devemos buscar o resultado final de todas as avaliações
							
							$sResultadoAprovacao = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->getResultadoFinal();

							
							// * Verificamos se o aluno foi reprovado em alguma disciplina. Caso tenha sido, o Resultado Final é 'R', desde
							// * que o mesmo não tenha sido aprovado com progressão parcial
							
							if ($sResultadoAprovacao == 'R' && !$lAprovadoProgressaoAno) {
							    $sResultadoGeral = 'R';
							}
							
							
							// * Busca o termo do ensino
							
							if (!empty($iCodigoEnsino) && ($sResultadoGeral == 'A' || $sResultadoGeral == 'R')) {
				  
							  $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $sResultadoGeral, $iAnoCalendario);
							  if (isset($aDadosTermo[0])) {
								$sResultadoGeral = $aDadosTermo[0]->sAbreviatura;
							  }
							}

							
							 //Verifica se houve aprovação pelo conselhor
							 //Se sim, identificamos com um número sobrescrito para identificar o tipo na legenda
							 
							$oAprovConselho = $oResultadoFinal->getFormaAprovacaoConselho();
							if ($sAmparado != '') {
							    $nValorAproveitamento = $sAmparado;
							}
							//NOTA COM VÍRGULA
							$nValorAproveitamento = str_replace(".", ",", $nValorAproveitamento);
							//var_dump($nValorAproveitamento); die("Confere");
							if ($nValorAproveitamento == 'Parecer') {
							    $nValorAproveitamento = 'Rel';
							}
							
							 //Preenchemos com o aproveitamento para cada disciplina
							 
							 
							if ($oFiltroRelatorio->lCalculaFrequencia == 1 && $oFiltroRelatorio->iFrequencia != 1) {
							    $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorAproveitamento,$fontStyle);	
								$nValorFalta = !empty($iNumeroFaltas) ? $iNumeroFaltas : "" ;
								if ($oFiltroRelatorio->iFrequencia == 2) {
								    $nValorFalta =  $sPercentualFrequencia;
								}
							    if ($oFiltroRelatorio->iFrequencia == 4) {
								    $nValorFalta  = $iTotalDeAulasDadas - $iNumeroFaltas;
							    }
							    if ($oRegencia->reclassificadoPorBaixaFrequencia()) {
								    //$nValorFalta = '--';
									if( $nValorFalta < '75')
									{ 	   
										$nValorFalta = '75';
									}   
							    }
							    if ($oRegencia->getRegencia()->getFrequenciaGlobal()  == 'A') {
								//$nValorFalta = '-';
								    if( $nValorFalta < '75')
								    { 	   
									    $nValorFalta = '75';
								    }   
                                }
								
                                $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorFalta,$fontStyle);
                            }else{
							    $table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorAproveitamento,$fontStyle);	
							}	// fim do aproveitamento disciplina							
							$iContadorDisciplinasImpressas++;
						}// fim do foreach, não esquecer

						if(($sResultadoGeral == "APR") or ($sResultadoGeral == "Apr")){
							$sResultadoGeral = "AP";
						}
				  
						if( $lAprovadoProgressaoAno ) {  
							$lObservacaoProgressaoParcial  = true;
							$sResultadoGeral              .= '/D';
						}  
						
                        if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
							$nValorFaltas = $sPercentualFrequencia;
							if( $nValorFaltas == 0 or $nValorFaltas = '' or $nValorFaltas == null)
							{	
							    $nValorFaltas =   ($iTotalDeAulasDadas-$iNumeroFaltas)/$iTotalDeAulasDadas*100;
							}	
							if ($oFiltroRelatorio->iFrequencia == 3) {
							    $nValorFaltas = !empty($nTotalDeFaltas) ? $nTotalDeFaltas : "" ;
							}  
							if ($oFiltroRelatorio->iFrequencia == 4) {
							    $nValorFaltas  = $iTotalDeAulasDadasGeral - $nTotalDeFaltas;
							}  
							if ($aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia()) {
							  //$nValorFaltas = '--';
							    if( $nValorFaltas < '75')
							    { 	   
								    $nValorFaltas = '75';
							    }   
							}  
							if ($oRegencia->getRegencia()->getFrequenciaGlobal()  == 'A') {
							    //$nValorFalta = '-';
							    if( $nValorFaltas < '75')
							    { 	   
								    $nValorFaltas = '75';
							    }   
							}
							$nValorFaltas = floor($nValorFaltas);
							$table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$nValorFaltas,$fontStyle);
							$table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$sResultadoGeral,$fontStyle);
						}else{
							$table->addCell($tamanhoDisciplina,$combordas)->addText('   '.$sResultadoGeral,$fontStyle);
						}	// fim do outro if de resultados
						

						
					} // fim do if, não esquecer	

				}// fim do if MATRICULADO	
				
            }// fim do for 	//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx	
	    }// fim do primeiro foreach
//***********************************************************************************************************************
		if ($oFiltroRelatorio->iTipoModelo != 2) {
			$sObservacoesEscola = $oFiltroRelatorio->sObservacao;
			
			if ($sObservacoesEscola != null) {
			    $combordas = array(''=>'','borderSize'=>6, 'cellMarginTop'=>100,'valign'=>'center');  
			    $fontStyle = new \PhpOffice\PhpWord\Style\Font();
			    $fontStyle->setBold(false);
			    $fontStyle->setName('Arial');
			    $fontStyle->setSize(7);
			  
			    $styleTable = array('cellMarginTop'=>100);
			    $phpWord->addTableStyle('myTable2', $styleTable);
			    $table = $section->addTable('myTable3');
				
				$myTextElement  = $section->addText('');
				$table->addRow(25);
				$table->addCell(20000,$sembordas)->addText("Observações:",$fontStyle);
				if (mb_detect_encoding($sObservacoesEscola . 'x', 'UTF-8', 'ISO-8859-1') == 'UTF-8') {
					$sObservacoesEscola = utf8_decode($sObservacoesEscola);
					$sObservacoesEscola = str_replace(",", ',', $sObservacoesEscola);
				} else {
					$sObservacoesEscola = str_replace(",", ',', $sObservacoesEscola);
				}
				$table->addRow(25);
				$table->addCell(20000,$sembordas)->addText($sObservacoesEscola,$fontStyle);
				$myTextElement  = $section->addText($sObservacoesEscola);
			}

			//assinatura aqui
			$myTextElement  = $section->addText('');
			$myTextElement  = $section->addText('');
			$linha_impressa = "E para constar, eu _______________________________________, lavrei a presente Ata que vai assinada pelas";
			$myTextElement  = $section->addText($linha_impressa);
			$linha_impressa = "autoridades competentes.";
			$myTextElement  = $section->addText($linha_impressa);

			$nomediretor = $_GET["diretor"];
			$nomediretor = explode("|", $nomediretor);
			$nomediretor = $nomediretor[1];
			$nomediretor = trataNome($nomediretor);

			$nomesecretario = $_GET["secretario"];
			$nomesecretario = explode("|", $nomesecretario);
			$nomesecretario = $nomesecretario[1];
			$nomesecretario = trataNome($nomesecretario);

			if(isset($oFiltroRelatorio->iRegente)){
			  $xDocente       = DocenteRepository::getDocenteByCodigoRecursosHumano($oFiltroRelatorio->iRegente);
			  $nomesupervisor   = trataNome($xDocente->getNome());
			}else{
			  $nomesupervisor = "";
			}
			$myTextElement  = $section->addText('');
		    $styleTable = array('cellMarginTop'=>100);
		    $phpWord->addTableStyle('myTable2', $styleTable);
		    $table = $section->addTable('myTable3');
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("Supervisor Escolar",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("Diretor",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText($nomesupervisor,$fontStyle);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText($nomediretor,$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("______________________________",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText("Secretário",$fontStyle);
			$table->addRow(25);
			$table->addCell(7000,$sembordas)->addText("",$fontStyle);
			$table->addCell(7000,$sembordas)->addText($nomesecretario,$fontStyle);
		
		}
//***********************************************************************************************************************
	}//FINAL DO CORPO FUNDAMENTAL ANOS FINAIS //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
}	

function cabecalhoPadrao($phpWord, $section,$oFiltroRelatorio, $aDisciplinasPagina, Turma $oTurma, $iEtapa) {
	
	
  $combordas = array(''=>'','borderSize'=>6, 'cellMarginTop'=>100);	
  
  $phpWord->setDefaultParagraphStyle(
		array(
			'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
			'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
			'spacing' => 120,
			'lineHeight' => 1
		)
   );  
   
  $fontStyle = new \PhpOffice\PhpWord\Style\Font();
  $fontStyle->setBold(false);
  $fontStyle->setName('Arial');
  $fontStyle->setSize(6);

  
  $styleTable = array('cellMarginTop'=>100);
  $phpWord->addTableStyle('myTable', $styleTable);
  $table = $section->addTable('myTable');
  $table->addRow(25);
  //Total de disciplinas existentes na turma  
  $oFiltroRelatorio->iTotalDisciplinas = count($aDisciplinasPagina);
  $xxnomeetapa = $oTurma->getBaseCurricular()->getDescricao();  
  $xxturma = $oTurma->getDescricao();
  $reduzcoluna = 2;

  if($xxnomeetapa == "EJA ANOS INICIAIS" || $xxnomeetapa == "EJA ANOS FINAIS" || $xxnomeetapa == "E F ANOS INICIAIS" || $xxnomeetapa == "EF ANOS INICIAIS"){
    $xurma = $oTurma->getEtapas();
    $xurma = $xurma[0];
    $xiclo = $xurma->getEtapa()->getNome();    
	if( $xxnomeetapa == "EF ANOS INICIAIS")
	{
        $xxnomeetapa = "E F ANOS INICIAIS";
	}	
  }

  //XCABECA
  
//*************************************************************************************************************************************************************************************	
  if($xxnomeetapa == "EDUCAÇÃO INFANTIL CRECHE"){
//*************************************************************************************************************************************************************************************		  
//	$table->addCell(100,$sembordas)->addText(' ',$fontStyle);
	$table->addCell(1000,$esquerdaacima)->addText('Nº',$fontStyle);
	$table->addCell(17000,$esquerdaacima)->addText('Nome do Aluno',$fontStyle);
	$table->addCell(4000,$esquerdaacima)->addText('Data de Nascimento',$fontStyle);
	$table->addCell(4000,$sembordasabaixo)->addText('Frequência %',$fontStyle);
  }elseif($xxnomeetapa == "EDUCAÇÃO INFANTIL PRÉ-ESCOLA"){
//*************************************************************************************************************************************************************************************		  
	$table->addCell(1000,$esquerdaacima)->addText('Nº',$fontStyle);
	$table->addCell(17000,$esquerdaacima)->addText('Nome do Aluno',$fontStyle);
	$table->addCell(4000,$esquerdaacima)->addText('Data de Nascimento',$fontStyle);
	$table->addCell(4000,$sembordasabaixo)->addText('Frequência %',$fontStyle);
  }elseif($xxnomeetapa == "E F ANOS INICIAIS" && $xiclo == "1º ANO"){
//*************************************************************************************************************************************************************************************		  
	$table->addCell(1000, $combordas)->addText('  Nº',$fontStyle);
	$table->addCell(17000,$combordas)->addText('     Nome do Aluno',$fontStyle);
	$table->addCell(4000, $combordas)->addText('  Frequência Anual %',$fontStyle);
	$table->addCell(4000, $combordas)->addText('     Resultado',$fontStyle);
//*************************************************************************************************************************************************************************************	
  }elseif($xxnomeetapa == "E F ANOS INICIAIS" && ($xiclo == "2º ANO" || $xiclo == "3º ANO" || $xiclo == "4º ANO" || $xiclo == "5º ANO")){
	  
	  
	$table->addCell(1000, $combordas)->addText(' Nº',$fontStyle);
    $table->addCell(10000,$combordas)->addText(' Nome do Aluno',$fontStyle);
    if ($oFiltroRelatorio->iTotalDisciplinas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
      $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinas;
    }
    $oFiltroRelatorio->iColunasEmBranco = 0;
    if ($oFiltroRelatorio->iContadorDisciplinasImpressas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
      $oFiltroRelatorio->iColunasEmBranco = $oFiltroRelatorio->iTotalDisciplinasPorPagina - $oFiltroRelatorio->iContadorDisciplinasImpressas;
    }  
    $koluna = 9;
    $kamanho = 100;
    $disciplinasNome = array();
    for ($iContadorDisciplinas = 0; $iContadorDisciplinas < $oFiltroRelatorio->iContadorDisciplinasImpressas; $iContadorDisciplinas++) {
        if (!array_key_exists ($iContadorDisciplinas, $aDisciplinasPagina)) {break;}
        if ($iContadorDisciplinas == $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
            $myTextElement  = $section->addPageBreak();
            $lQuebrouPagina = true;
        }
        $table->addCell(1000,$combordas)->addText(substr($aDisciplinasPagina[$iContadorDisciplinas]->getDisciplina()->getNomeDisciplina(),0,3),$fontStyle);   
//	      $disciplinasNome = $aDisciplinasPagina[$iContadorDisciplinas]->getDisciplina()->getNomeDisciplina();
    }		
    $direitaesquerda = array('borderLeftColor'=>''  ,'borderLeftSize'=>6,
                             'borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	         'cellMarginTop'=>100,
					        );
	$table->addCell(1000,$combordas)->addText('   ',$fontStyle);
	$table->addCell(1000,$combordas)->addText('   ',$fontStyle);
	$table->addCell(1000,$combordas)->addText('   ',$fontStyle);
	$table->addCell(1000,$combordas)->addText('   ',$fontStyle);
	$table->addCell(1000,$combordas)->addText('   ',$fontStyle);	
	$table->addCell(1000,$combordas)->addText('Fr%',$fontStyle);
	$table->addCell(1000,$combordas)->addText('Rs',$fontStyle);
  }	  
/*	
	$table->addCell(1000, $direitaesquerda)->addText('  Nº',$fontStyle);
    $table->addCell(10000,$direitaesquerda)->addText('     Nome do Aluno',$fontStyle);
	
	for($x=0;$x<= 21;$x++)
	{   
        for($y = 0;$y<= 12;$y++)
		{	
            $table->addCell(1000,$direitaesquerda)->addText(substr($disciplinasNome[$y],$x,1),$fontStyle);
		}
		$table->addRow(25);
	    $table->addCell(1000, $direitaesquerda)->addText(' ',$fontStyle);
        $table->addCell(10000,$direitaesquerda)->addText(' ',$fontStyle);
	}
*/
//			$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","a+");
//			fwrite($arq,substr($disciplinasNome[$y],$x,1) );
//			fwrite($arq,"\r\n");
//			fclose($arq); 		

}//FIM DA FUNÇÃO


function retornaaulasdadas($regencia)
{
$sql = "
		SELECT 
		sum(ed78_i_aulasdadas) as aulasdadas
		from regenciaperiodo 
		inner join procavaliacao on procavaliacao.ed41_i_codigo = regenciaperiodo.ed78_i_procavaliacao 
		inner join regencia on regencia.ed59_i_codigo = regenciaperiodo.ed78_i_regencia 
		inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao 
		inner join formaavaliacao on formaavaliacao.ed37_i_codigo = procavaliacao.ed41_i_formaavaliacao 
		inner join procedimento on procedimento.ed40_i_codigo = procavaliacao.ed41_i_procedimento 
		inner join disciplina on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina 
		inner join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina 
		inner join turma on turma.ed57_i_codigo = regencia.ed59_i_turma 
		where 
		ed78_i_regencia = 1770 
		and 
		ed09_c_somach = 'S' 
	   ";
$rsAulas = db_query($sql);
$nAulas = db_utils::fieldsMemory($rsAulas, 0);
$numero_aulas = $nAulas->aulasdadas;
return $numero_aulas;
}


function cabecalhoFinais($phpWord, $section, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iCodigoEtapa)
{
	$lPrimeiroLaco = false;
    $combordas = array(''=>'','borderSize'=>6, 'cellMarginTop'=>100);	
  
    $phpWord->setDefaultParagraphStyle(
		array(
			'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
			'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
			'spacing' => 120,
			'lineHeight' => 1
		)
    );  
   
    $fontStyle = new \PhpOffice\PhpWord\Style\Font();
    $fontStyle->setBold(false);
    $fontStyle->setName('Arial');
    $fontStyle->setSize(6);

  
    $styleTable = array('cellMarginTop'=>100);
    $phpWord->addTableStyle('myTable', $styleTable);
    $table = $section->addTable('myTable');
    $table->addRow(25);
	
	$table->addCell(1000, $combordas)->addText(' Nº',$fontStyle);
	$table->addCell(10000,$combordas)->addText(' Nome do Aluno',$fontStyle);

	if ($oFiltroRelatorio->iTotalDisciplinas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
	  $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinas;
	}
	$oFiltroRelatorio->iColunasEmBranco = 0;
	if ($oFiltroRelatorio->iContadorDisciplinasImpressas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
	  $oFiltroRelatorio->iColunasEmBranco = $oFiltroRelatorio->iTotalDisciplinasPorPagina - $oFiltroRelatorio->iContadorDisciplinasImpressas;
	}  
	$koluna = 9;
	$kamanho = 100;
	$disciplinasNome = array();

	for ($iContadorDisciplinas = 0; $iContadorDisciplinas <= count($aDisciplinasPagina);  $iContadorDisciplinas++) {
		if (!array_key_exists ($iContadorDisciplinas, $aDisciplinasPagina)) {break;}
		if ($iContadorDisciplinas == $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
			$myTextElement  = $section->addPageBreak();
			$lQuebrouPagina = true;
		}
		$table->addCell(1000,$combordas)->addText(substr($aDisciplinasPagina[$iContadorDisciplinas]->getDisciplina()->getNomeDisciplina(),0,5),$fontStyle);   
	}		
	$table->addCell(1000,$combordas)->addText('Freq%',$fontStyle);
	$table->addCell(1000,$combordas)->addText('Resul',$fontStyle);
	$lQuebrouPagina = false;
}
?>