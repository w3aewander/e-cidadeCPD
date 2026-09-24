<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
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

require_once(modification("fpdf151educacao/FpdfMultiCellBorder.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("std/DBDate.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/exceptions/DBException.php"));

$oGet       = db_utils::postMemory($_GET);

$oGet->obs1 = base64_decode($oGet->obs1);
$oFiltros   = new stdClass();

$assinatura1  = base64_decode($iAssinaturaAdicional);
$assinatura12 = base64_decode($iAssinaturaAdicional2);



/**
 * Forma de apresentacao dos pareceres padronizados
 * 'C' => Concatenar pareceres lado a lado
 * 'L' => Listar pareceres um abaixo do outro
 * @var string
 */
$oFiltros->sPadrao = $oGet->padraotipo;

/**
 * Informa se eh um parecer unico 'PU'
 * 'yes' | 'no'
 * @var boolean
 */
$oFiltros->lParecerUnico = $oGet->punico == 'yes' ? true : false;

/**
 * Tipo de avaliacao concatenado com o codigo do periodo
 * Ex.: A|219
 *      A => Avaliacao
 *      R => Resultado
 * @var string
 */
$sPeriodo             = explode("|", $oGet->periodo);
$oFiltros->sAvaliacao = $sPeriodo[0];
$oFiltros->iPeriodo   = $sPeriodo[1];


/**
 * Array com o codigo da regencia da disciplina
 * @var array
 */
$aDisciplinas = explode(",", $oGet->disciplinas);

/**
 * Informa se deve ser impresso o nome do professor conselheiro
 * 'S' | 'N'
 * @var string
 */
$oFiltros->lAssinaturaConselheiro = $oGet->assinaturaregente == 'S' ? true : false;

/**
 * Codigo dos alunos selecionados
 * Ex.: 49740,49741,49742,49743
 * @var string
 */
$sAlunos = $oGet->alunos;

/**
 * Observacao a ser impressa no boletim
 * @var text
 */
$oFiltros->sObservacao = $oGet->obs1;

/**
 * Altura padrao das linhas
 */
$oFiltros->iAlturaLinhaPadrao = 4;

/**
 * Largura padrao das linhas
 */
$oFiltros->iLarguraLinha = 190;

/**
 * Array com os alunos selecionados para impressao
 */
$aAlunos = explode(",", $sAlunos);

/**
 * Instancia de turma, pelo codigo passado como parametro (turmaserieregimemat)
 */
$oFiltros->oTurma = TurmaRepository::getTurmaByCodigoTurmaSerieRegimeMat($oGet->turma);

/**
 * Instancia de etapa, pelo codigo passado como parametro (turmaserieregimemat)
 */
$oFiltros->oEtapa = EtapaRepository::getEtapaByCodigoTurmaSerieRegimeMat($oGet->turma);

/**
 * Variavel que recebe o elemento de uma avaliacao
 */
$oFiltros->oElementoAvaliacao = null;

$oFiltros->iLarguraRetangulo = 200;

/**
 * Professor conselheiro da Turma
 */
$oFiltros->sProfessorConselheiro = "";
if ($oFiltros->oTurma->getProfessorConselheiro() && $oFiltros->oTurma->getProfessorConselheiro()->getNome() != '') {
  $oFiltros->sProfessorConselheiro = "Professor Conselheiro  ".$oFiltros->oTurma->getProfessorConselheiro()->getNome();
}

/**
 * Contém os elementos de avaliação
 */
$aAvaliacoes = array();

if ($oFiltros->sAvaliacao == 'A') {

  $oFiltros->oElementoAvaliacao = AvaliacaoPeriodicaRepository::getAvaliacaoPeriodicaByCodigo($oFiltros->iPeriodo);
  $aAvaliacoes[] = $oFiltros->oElementoAvaliacao;
} else {

  $oProcedimentoAvalicao        = $oFiltros->oTurma->getProcedimentoDeAvaliacaoDaEtapa($oFiltros->oEtapa);
  $oFiltros->oElementoAvaliacao = ResultadoAvaliacaoRepository::getResultadoAvaliacaoByCodigo($oFiltros->iPeriodo);

  /**
   * Procurar todas as avaliações em que a ordem da avaliação é menor que o resultado
   */
  $aAvaliacoesProcedimento = $oProcedimentoAvalicao->getAvaliacoes();
  $iOrdemSequencia         = $oFiltros->oElementoAvaliacao->getOrdemSequencia();

  foreach ($aAvaliacoesProcedimento as $oAvaliacao) {

    if ($oAvaliacao->getOrdemSequencia() < $iOrdemSequencia) {
      $aAvaliacoes[] = $oAvaliacao;
    }
  }
}


/**
 * Controla o total de faltas dentro de um periodo (por aluno)
 */
$oFiltros->iFaltasPeriodo = 0;

$pdf = new FpdfMultiCellBorder();
$pdf->Open();
$pdf->AliasNbPages();

$oFiltros->iAlturaDeQuebraPagina = $pdf->h - 15;

$aAlunosImpressao = array();

$clobsboletim    = new cl_obsboletim;
$iEscola = $oFiltros->oTurma->getEscola()->getCodigo();
$sSqlObsBoletim = $clobsboletim->sql_query( "", "ed252_t_mensagem", "", "ed252_i_escola = {$iEscola}" );
$resultobs      = $clobsboletim->sql_record( $sSqlObsBoletim );

if( $clobsboletim->numrows > 0 ) {

	$oDadosObs = db_utils::fieldsMemory($resultobs, 0);
	$oFiltros->sObservacao = $oDadosObs->ed252_t_mensagem;
}

foreach ($aAlunos as $iMatricula) {

  $oMatricula = MatriculaRepository::getMatriculaByCodigo($iMatricula);
  
  $oDadosAluno                  = new stdClass();
  
  
  $oDadosAluno->iCodigo         = $oMatricula->getAluno()->getCodigoAluno();
  $oDadosAluno->sNome           = $oMatricula->getAluno()->getNome();
  $oDadosAluno->sNomeSocial     = $oMatricula->getAluno()->getNomeSocial();
  $oDadosAluno->iMatricula      = $oMatricula->getCodigo();
  $oDadosAluno->iOrdem          = $oMatricula->getNumeroOrdemAluno();
  $oDadosAluno->sDataNasc       = $oMatricula->getAluno()->getDataNascimento();
  $oDadosAluno->sSituacao       = $oMatricula->getSituacao();
  $oDadosAluno->sResultadoFinal = "EM ANDAMENTO";


  $oDadosAluno->aDisciplinas = array();


  foreach ($aDisciplinas as $iDisciplina) {

    $oRegencia        = RegenciaRepository::getRegenciaByCodigo($iDisciplina);
    $oDadosDisciplina = new stdClass();

    $oDadosDisciplina->iRegencia    = $oRegencia->getCodigo();
    $oDadosDisciplina->sAbreviatura = $oRegencia->getDisciplina()->getAbreviatura();
    $oDadosDisciplina->sDisciplina  = $oRegencia->getDisciplina()->getNomeDisciplina();
    $oDadosDisciplina->iTotalFaltas = 0;
    $oDadosDisciplina->iTotalAulas  = 0;

    db_inicio_transacao();
    $oDiarioDeClasse   = $oMatricula->getDiarioDeClasse();
    $oDiarioDisciplina = $oMatricula->getDiarioDeClasse()->getDisciplinasPorRegencia($oRegencia);
    db_fim_transacao();

    /**
     * Percorremos a(s) avaliação(ões) de acordo com o período selecionado
     */
	 
    foreach ($aAvaliacoes as $oAvaliacao) {

      $oPeriodoAvaliacao = $oAvaliacao->getPeriodoAvaliacao();

      /**
       * Verificamos se trata-se de parecer unico, calculando o total de dias letivos e faltas. Caso contrario, o calculo
       * sera de acordo com a regencia
       */
      if ($oFiltros->lParecerUnico) {

        foreach ($oDiarioDeClasse->getDisciplinas() as $oDisciplinaDiario) {

          $oDadosDisciplina->iTotalFaltas += $oDisciplinaDiario->getTotalFaltasPorPeriodo($oPeriodoAvaliacao);
          $oDadosDisciplina->iTotalAulas  += $oDisciplinaDiario->getRegencia()->getTotalDeAulasNoPeriodo($oPeriodoAvaliacao);
        }
      } else {
        $oDadosDisciplina->iTotalFaltas = $oDiarioDisciplina->getTotalFaltasPorPeriodo($oPeriodoAvaliacao);
        $oDadosDisciplina->iTotalAulas  = $oRegencia->getTotalDeAulasNoPeriodo($oPeriodoAvaliacao);
      }
    }


    /**
     * Caso a matricula do aluno esteja concluida, buscamos o resultado final de acordo com o termo configurado para o
     * ensino no ano de execucao do calendario, imprimindo a descricao do Termo
     * Ex.: Aprovado / Reprovado
     *
     * Caso nao esteja concluida, imprime como EM ANDAMENTO
     * @todo Testar
     */
    if ($oMatricula->isConcluida()) {

      $sTermoResultadoFinal = $oDiarioDeClasse->getResultadoFinal();
      $oTermoEnsino         = DBEducacaoTermo::getTermoEncerramento($oFiltros->oTurma->getBaseCurricular()->getCurso()->getEnsino(),
                                                                    $sTermoResultadoFinal,
                                                                    $oFiltros->oTurma->getCalendario()->getAnoExecucao()
                                                                    );
      $oDadosAluno->sResultadoFinal = $oTermoEnsino[0]->sDescricao;
    }


    /**
     * Buscamos os pareceres vinculados ao aluno para reg?ncia
     */
    $oDadosDisciplina->oParecer = LancamentoAvaliacaoAluno::getParecer($oMatricula, $oRegencia,
                                                                       $oFiltros->oElementoAvaliacao->getOrdemSequencia()
                                                                      );

    $oDadosAluno->aDisciplinas[] = $oDadosDisciplina;

  }
  $aAlunosImpressao[] = $oDadosAluno;
}


/** ***************************************************************************************************************** *
 ** ************************************** IMPRESSÃO DOS DADOS ****************************************************** *
 ** ***************************************************************************************************************** */
$lAdicionaPagina    = true;
$lImprimeMeioPagina = false;

foreach ($aAlunosImpressao as $oAluno) {

  $lImprimeCabecalho = true;
  $pdf->exibeHeader(false);
  if ($lAdicionaPagina || $pdf->GetY() > 150) {
    $pdf->AddPage();
    $lAdicionaPagina = false;
  }
  $iPaginaInicial = $pdf->PageNo();
  cabecalhoRelatorio($pdf, $oFiltros, $oAluno, $lImprimeMeioPagina);
  $iYInicialImpressaoAluno = $pdf->GetY();
  
  $pdf->Line($pdf->GetX(), $iYInicialImpressaoAluno, $oFiltros->iLarguraRetangulo, $pdf->GetY());
  $pdf->Line($pdf->GetX(), $iYInicialImpressaoAluno, $pdf->GetX(), $pdf->GetY() + $oFiltros->iAlturaLinhaPadrao);
  $pdf->Line($oFiltros->iLarguraRetangulo, $iYInicialImpressaoAluno, $oFiltros->iLarguraRetangulo, $pdf->GetY() + $oFiltros->iAlturaLinhaPadrao);
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq,$oFiltros->iLarguraRetangulo);
//fwrite($arq,"\r\n");
//fclose($arq); 		
  
  /**
   * Imprime o valor do Resultado Final de acordo com a situacao da matricula
   */
  $pdf->SetX(20);
  $sMensagemResultadoFinal = "Resultado Final em {$oFiltros->oEtapa->getNome()}: {$oAluno->sResultadoFinal} ";
  $pdf->cell($oFiltros->iLarguraLinha, $oFiltros->iAlturaLinhaPadrao, $sMensagemResultadoFinal, 0, 1, "L");

  $iLarguraLinha = $oFiltros->iLarguraLinha - 8;
  
  foreach ($oAluno->aDisciplinas as $oDisciplina) {

    /**
     * Cálculo para saber se devemos quebrar página
     */
    $iLinhasParecerPadronizado = 0;
    if (!empty($oDisciplina->oParecer->sParecerPadronizado)) {

      if ($oFiltros->sPadrao == 'C') {
        $iLinhasParecerPadronizado = $pdf->NbLines($iLarguraLinha, $oDisciplina->oParecer->sParecerPadronizado);
      } else {
        $iLinhasParecerPadronizado = count(explode("**", $oDadosDisciplina->oParecer->sParecerPadronizado));
      }
      $iLinhasParecerPadronizado += 2;  //linhas de header do parecer padronizado
    }
    $iLinhasParecer = 0;
    if (!empty($oDisciplina->oParecer->sParecer)) {
      $iLinhasParecer = $pdf->NbLines($iLarguraLinha, $oDisciplina->oParecer->sParecer);
    } else {
      $iLinhasParecer = 5;
    }

    $iLinhasAproximado       = $iLinhasParecer + $iLinhasParecerPadronizado;
    $iAlturaAproximadaQuadro = $pdf->GetY() + 25 + ($iLinhasAproximado * $oFiltros->iAlturaLinhaPadrao);

    $lPaginaNova = false;
    if (($iAlturaAproximadaQuadro > $oFiltros->iAlturaDeQuebraPagina) || ($pdf->GetY() > 250)) {

      $pdf->Line($pdf->GetX(), $oFiltros->iAlturaDeQuebraPagina ,  $oFiltros->iLarguraRetangulo, $oFiltros->iAlturaDeQuebraPagina);
      $pdf->Line($pdf->GetX(), $pdf->GetY() , $pdf->GetX(), $oFiltros->iAlturaDeQuebraPagina);
      $pdf->Line($oFiltros->iLarguraRetangulo, $pdf->GetY() , $oFiltros->iLarguraRetangulo, $oFiltros->iAlturaDeQuebraPagina);
    }
    /**
     * Imprime Informações Disciplina e Parecer
     */
	$calendario = $oFiltros->oTurma->getCalendario()->getCodigo(); 
	
    informacoesAlunoDisciplina($pdf, $oFiltros, $oDisciplina, $lPaginaNova, $calendario);
    imprimePareceres($pdf, $oFiltros, $oDisciplina);
  }
//***************************************************************************************************
  /**
   * Cálculo para saber se as observações caberão na página atual
   */
  $iLinhasObservacao     = $pdf->NbLines($iLarguraLinha, $oFiltros->sObservacao);
  $iAlturaAproximadaObs  = $pdf->GetY() + ($iLinhasObservacao * $oFiltros->iAlturaLinhaPadrao);
  $iAlturaAproximadaObs += 20; //Altura da assinatura se houver

  $lPaginaNova = false;
  if ($iAlturaAproximadaObs > $oFiltros->iAlturaDeQuebraPagina) {

    $lPaginaNova = true;
    $pdf->Line($pdf->GetX(), $pdf->GetY() + 2 , $oFiltros->iLarguraRetangulo, $pdf->GetY() + 2);
    $pdf->AddPage();
  }
  imprimeObservacoes($pdf, $oFiltros, $lPaginaNova, $oDadosAluno->iCodigo);
  assinaturaAdicional($pdf, $iAssinaturaAdicional,$iAssinaturaAdicional2,$iAtividade,$iAtividade2);
  $iYAposImprimirDados = $pdf->GetY();
  $iPaginaFinal = $pdf->PageNo();
  if ($iPaginaFinal - $iPaginaInicial == 0) {
    $pdf->Line($pdf->GetX(), $iYAposImprimirDados + 2 , $oFiltros->iLarguraRetangulo, $iYAposImprimirDados + 2);
  }


  $lAdicionaPagina    = true;
  $lImprimeMeioPagina = false;
  if ($iYAposImprimirDados < 150) {

    $lAdicionaPagina    = false;
    $lImprimeMeioPagina = true;
  }
  if ($iPaginaFinal - $iPaginaInicial > 0) {

    $lAdicionaPagina    = true;
    $lImprimeMeioPagina = false;
    $pdf->Rect(10, 8, 190, $oFiltros->iAlturaDeQuebraPagina);
  }
}



/**
 * Imprime as informacoes do cabecalho
 * @param PDF $pdf
 * @param stdClass $oFiltros
 */
function cabecalhoRelatorio(FpdfMultiCellBorder $pdf, $oFiltros, $oAluno, $lImprimeMeioPagina) {


  /**
   * Variáveis de controle para impressão do cabeçalho padrão, utilizado em libdocumento
   */
  $d   = 0;
  $y1  = 9;
  $y2  = 14;
  $y3  = 18;
  $y4  = 22;
  $y5  = 26;
  $y6  = 30;
  $y7  = 6;
  $y8  = 5;
  $y9  = 35;
  $y10 = 33;
  $y11 = 63;
  $y12 = 3;
  $y13 = 12;
  $y14 = 43;
  $m0  = 10;  // altura que será escrito o nome do departamento
  $m1  = 14; // altura que será escrito o nome da escola
  $m2  = 18; // altura que será escrito o endereço
  $m3  = 22; // altura que será escrito a cidade
  $m4  = 26; // altura que será escrito o telefone da escola
  $m5  = 30; // altura que será escrito o email
  $m6  = 34; // altura da linha do cabeçalho
  $m7  = 3;  // altura da linha dentro do quadro das legendas
  $m8  = 7;  // altura que começa a desenhar o quadro das legendas
  $m9 = 35; // altura que será escrito o site da escola
  $a   = 5;
  $f   = 190;
  $r   = 32;
  $margemesquerda  = $pdf->lMargin;

  /**
   * Verificamos se estamos imprimindo o 2º aluno da página e se neste caso
   */
  if ($lImprimeMeioPagina) {

  	$m0  = 10  + 150; // altura que será escrito o nome do departamento
    $m1  = 14 + 150; // altura que será escrito o nome da escola
    $m2  = 18 + 150; // altura que será escrito o endereço
    $m3  = 22 + 150; // altura que ser? escrito a cidade
    $m4  = 26 + 150; // altura que ser? escrito o telefone da escola
    $m5  = 30 + 150; // altura que ser? escrito o email
    $m6  = 34 + 150; // altura que ser? escrito o site da escola
    $m7  = 3;  // altura da linha dentro do quadro das legendas
    $m8  = 7 + 150 ;  // altura que come?a a desenhar o quadro  das legendas
    $m9 = 35 + 150; // altura da linha do cabe?alho

    $pdf->SetY(158);
  }



  /**
   * *************************************
   * DADOS A SEREM IMPRESSOS NO CABECALHO
   * *************************************
   */
  $oCabecalho          = new stdClass();
  $oCabecalho->sDDD    = "";
  $oCabecalho->iNumero = "";
  $sLogoInstit         = $oFiltros->oTurma->getEscola()->getLogo();
  $sLogoEscola         = $oFiltros->oTurma->getEscola()->getLogoEscola();
  $nome                = $oFiltros->oTurma->getEscola()->getDepartamento()->getNomeDepartamento()." - ".$oFiltros->oTurma->getEscola()->getUf();
  $ed52_i_ano          = $oFiltros->oTurma->getCalendario()->getAnoExecucao();
  $ed57_c_descr        = $oFiltros->oTurma->getDescricao();
  $ed11_c_descr        = $oFiltros->oEtapa->getNome();
  $ed15_c_nome         = $oFiltros->oTurma->getTurno()->getDescricao();
  $ed10_c_abrev        = $oFiltros->oTurma->getBaseCurricular()->getCurso()->getEnsino()->getAbreviatura();
  $ed29_i_codigo       = $oFiltros->oTurma->getBaseCurricular()->getCurso()->getCodigo();
  $ed29_c_descr        = $oFiltros->oTurma->getBaseCurricular()->getCurso()->getNome();
  $ed52_c_descr        = $oFiltros->oTurma->getCalendario()->getDescricao();
  $ruaescola           = $oFiltros->oTurma->getEscola()->getEndereco();
  $numescola           = $oFiltros->oTurma->getEscola()->getNumeroEndereco();
  $bairroescola        = $oFiltros->oTurma->getEscola()->getBairro();
  $cidadeescola        = $oFiltros->oTurma->getEscola()->getMunicipio();
  $estadoescola        = $oFiltros->oTurma->getEscola()->getUf();
  $emailescola         = $oFiltros->oTurma->getEscola()->getEmail();
  $sNomeEscola         = $oFiltros->oTurma->getEscola()->getNome();
  $url                 = $oFiltros->oTurma->getEscola()->getUrl();

  /**
   * Buscamos o primeiro registro de telefone cadastrado para a escola, caso exista
  */
  $aTelefones = $oFiltros->oTurma->getEscola()->getTelefones();
  if (count($aTelefones) > 0) {

    $oCabecalho->sDDD    = !empty($aTelefones[0]->iDDD) ? " ({$aTelefones[0]->iDDD}) " : "";
    $oCabecalho->iNumero = !empty($aTelefones[0]->iNumero) ? "{$aTelefones[0]->iNumero}" : "";
  }
  $DadosCabecalho  = $oFiltros->oTurma->getEscola()->getNome().$oCabecalho->sDDD.$oCabecalho->iNumero;
  $iTelefoneEscola = 1;

  /**
   * Setamos o periodo a ser apresentado no cabecalho, nome do aluno e condigo do mesmo
  */
  $periodoselecionado = $oFiltros->oElementoAvaliacao->getDescricao();

  $ed47_v_nome = is_null($oAluno->sNomeSocial) || empty($oAluno->sNomeSocial) ? $oAluno->sNome :$oAluno->sNomeSocial;
  $ed47_i_codigo      = $oAluno->iCodigo;
  $ed47_i_dtnasc      = $oAluno->sDataNasc;
  $ed60_i_numero      = $oAluno->iOrdem;
  $ed60_c_situacao    = $oAluno->sSituacao;


  $nasci = substr($ed47_i_dtnasc,8,2).'/'.substr($ed47_i_dtnasc,5,2).'/'.substr($ed47_i_dtnasc,0,4);
//9999-99-99
  
  /**
   * Dados do cabe?alho padr?o, quando n?o houver cabe?alho configurado
   */
  $head1 = "BOLETIM POR PARECER DESCRITIVO $periodoselecionado";
//  $head2 = "Aluno: $ed47_v_nome - $oAluno->sResultadoFinal";
  $head2 = "Aluno: $ed47_v_nome - Nasc.: $nasci";
  $head3 = "Curso: $ed29_i_codigo - $ed29_c_descr";
  $head4 = "Calend?rio: $ed52_c_descr";
  $head5 = "Etapa: $ed11_c_descr          " . "Turma: $ed57_c_descr"   ;
  $head6 = "Matr?cula: {$oAluno->iMatricula}";

  $pdf->setfillcolor(225);
  $pdf->SetFont('arial','b',7);

  /**
   * Cria a instancia do documento que imprimira os dados do cabecalho de acordo com as variaveis setadas
   */
  $oLibDocumento = new libdocumento(5001,null);

  if ( $oLibDocumento->lErro ) {
    db_redireciona("db_erros.php?fechar=true&db_erro={$oLibDocumento->sMsgErro}");
  }

    $sSqlTelefoneEscola = "select ed26_i_numero
                           from telefoneescola
                          where ed26_i_escola = ".db_getsession("DB_coddepto")."
                          limit 1";

    $rsTelefoneEscola   = db_query($sSqlTelefoneEscola);

    if( pg_num_rows($rsTelefoneEscola) > 0 ){
        $telefoneescola = db_utils::fieldsMemory($rsTelefoneEscola,0)->ed26_i_numero;
    } else {
        $telefoneescola = "";
    }


    $pdf->Image('imagens/files/'.$sLogoInstit,7,$pdf->GetY(),20);

    if(trim($sLogoEscola)!=""){
        $pdf->Image('imagens/'.$sLogoEscola,108,$pdf->GetY() - 2,20);
    }
$m1 += 2;
$m2 += 2;
$m3 += 2;
$m4 += 2;
$m5 += 2;


//    $pdf->Text(33,$m0,$nome);
    $pdf->Text(33,7,"ESTADO DO RIO DE JANEIRO");
    $pdf->Text(33,10,"PREFEITURA MUNICIPAL DE VOLTA REDONDA");
    $pdf->Text(33,13,"SECRETARIA MUNICIPAL DE EDUCA??O");
    
    $pdf->Text(33,$m1,$sNomeEscola);
	
    $pdf->SetFont('Arial','I',8);
    $pdf->Text(33,$m2,$ruaescola.", ".$numescola." - ".$bairroescola);
    $pdf->Text(33,$m3,$cidadeescola." - ".$estadoescola);
    $pdf->Text(33,$m4,$telefoneescola);
    $comprim = ($pdf->w - $pdf->rMargin);
    $pdf->Text(33,$m5,($emailescola!=""?$emailescola:""));
    $pdf->Text(33,$m6,($url!=""?$url:""));
	

    $Espaco = $pdf->w - 80 ;
    $pdf->SetFont('Arial','',7);
    $pdf->setleftmargin($Espaco);
    $pdf->setfillcolor(235);
    $pdf->roundedrect($Espaco - 3,$m8,75,28,2,'DF','123');
    $pdf->line(10,$m9,$comprim,$m9);
    $pdf->setfillcolor(255);
    $pdf->multicell(0,$m7,@$head1,0,1,"J",0);
    $pdf->multicell(0,$m7,@$head2,0,1,"J",0);
    $pdf->multicell(0,$m7,@$head3,0,1,"J",0);
    $pdf->multicell(0,$m7,@$head4,0,1,"J",0);
    $pdf->multicell(0,$m7,@$head5,0,1,"J",0);
    $pdf->multicell(0,$m7,@$head6,0,1,"J",0);
    $pdf->multicell(0,$m7,@$head7,0,1,"J",0);
    $pdf->multicell(0,$m7,@$head8,0,1,"J",0);
    $pdf->setleftmargin($margemesquerda);
    $pdf->Ln(5);
	
}

/**
 * Imprime as informacoes de dia letivo, faltas e disciplina
 * @param FpdfMultiCellBorder $pdf
 * @param stdClass $oFiltros
 */
function informacoesAlunoDisciplina(FpdfMultiCellBorder $pdf, $oFiltros, $oDadosDisciplina, $lPaginaNova, $calendario) {

  $iYInicial = $pdf->GetY();

   if ($lPaginaNova) {
     $pdf->Line($pdf->GetX(), $iYInicial, $oFiltros->iLarguraRetangulo, $iYInicial);
   }
  /**
   * Imprime uma linha em branco com fundo cinza
   */
  $pdf->setfillcolor(225);
  $pdf->SetFont('times','',8);

  /**
   * Imprime o valor das aulas dadas. Valida a forma de calculo da carga horaria da Turma, podendo ser impresso:
   * Quando 1: Aulas Dadas
   * Quando 2: Dias Letivos
  */
  $pdf->SetXY(20, $pdf->GetY() + 2);
  $pdf->SetFont('arial', 'b', 10);
  $diasletivos = percfrequencia($calendario);
  //$sMensagemAulas = "Aulas Dadas: {$oDadosDisciplina->iTotalAulas}";
  $sMensagemAulas = "Aulas Dadas: {$diasletivos}";
  if ($oFiltros->oTurma->getFormaCalculoCargaHoraria() == 2) {
    //$sMensagemAulas = "Dias Letivos: {$oDadosDisciplina->iTotalAulas}";
	$sMensagemAulas = "Dias Letivos: {$diasletivos}";
  }
  $pdf->cell($oFiltros->iLarguraLinha / 2, $oFiltros->iAlturaLinhaPadrao, $sMensagemAulas, 0, 0, "L");

  /**
   * Imprime a linha com o numero de faltas total
  */
//   $pdf->SetX(20);
  $sNumeroFaltas = "N? de Faltas: {$oDadosDisciplina->iTotalFaltas}";
  $pdf->cell($oFiltros->iLarguraLinha / 2, $oFiltros->iAlturaLinhaPadrao, $sNumeroFaltas, 0, 1, "L");

  if( $_GET["impfrequencia"] == 'yes' )
  {
		$imprimeFreq = true;
  }else{
		$imprimeFreq = false;
  }
  if( $imprimeFreq )
  {
	  $Faltas = $oDadosDisciplina->iTotalFaltas;
	  $percFreq = floor(($diasletivos - $Faltas) / $diasletivos * 100);
      $sMensagemAulas = 'Percentual de Frequencia: '.$percFreq.'%';
      $pdf->cell(10, $oFiltros->iAlturaLinhaPadrao, '', 0, 0, "L");
      $pdf->cell($oFiltros->iLarguraLinha / 2, $oFiltros->iAlturaLinhaPadrao, $sMensagemAulas, 0, 1, "L");
  }	  

  /**
   * Imprime a linha com o nome da disciplina selecionada. Caso seja parecer unico ($oFiltros->lParecerUnico is true),
   * mostra PARECER UNICO
  */
  $pdf->SetXY(14, $pdf->GetY() + 2);
  $sDisciplina = $oFiltros->lParecerUnico ? 'PARECER ?NICO' : $oDadosDisciplina->sDisciplina;
  $pdf->cell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao, $sDisciplina, 1, 1, "L", 1);

  $iYFinal = $pdf->GetY();
  $pdf->Line($pdf->GetX(), $iYInicial, $pdf->GetX(), $iYFinal);
  $pdf->Line($oFiltros->iLarguraRetangulo, $iYInicial, $oFiltros->iLarguraRetangulo, $iYFinal);
}

/**
 * Imprime os pareceres
 * @param FpdfMultiCellBorder $pdf
 * @param stdClass $oFiltros
 */
function imprimePareceres(FpdfMultiCellBorder $pdf, $oFiltros, $oDadosDisciplina) {

  $iYInicial = $pdf->GetY();

  /**
   * Caso exista parecer padronizado setado, imprimimos cada parecer de acordo com a forma desejada ($sPadrao)
   * Senao existir, nao apresenta as linhas nem titulo de parecer padronizado
   */
  if (!empty($oDadosDisciplina->oParecer->sParecerPadronizado)) {
    $pdf->SetX(14);
    $pdf->setfillcolor(245);
    $pdf->cell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao, "Parecer Padronizado:", 1, 1, "L", 1);
    $pdf->SetX(14);
    $pdf->SetFont('arial', '', 10);

    if ($oFiltros->sPadrao == 'C') {
      $pdf->MultiCell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao, $oDadosDisciplina->oParecer->sParecerPadronizado, 1, "J");
    } else {
      $pdf->SetFont('arial', 'b', 10);
      $pdf->cell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao, "Seq - Parecer => Legenda", 1, 1, "J");
      $aPareceres = explode("**", $oDadosDisciplina->oParecer->sParecerPadronizado);

      $pdf->SetFont('arial', '', 10);
        foreach ($aPareceres as $sParecer) {
            $pdf->SetX(14);
            $pdf->MultiCell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao + 1, trim($sParecer), 1, 'J');
        }
    }
  }

  /**
   * Fecha os lados do parecer padronizado
   */
  $iYFinal = $pdf->GetY();
  $pdf->Line($pdf->GetX(), $iYInicial, $pdf->GetX(), $iYFinal + 2);
  $pdf->Line($oFiltros->iLarguraRetangulo, $iYInicial, $oFiltros->iLarguraRetangulo, $iYFinal + 2);

  $pdf->SetX(14);
  $pdf->SetFont('arial', 'b', 10);
  $pdf->cell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao, "Parecer Descritivo:", 1, 1, "L", 1);

  /**
   * Calculo para fechar os lados do parecer descritivos
   */
  $iLinhasParecer      = $pdf->NbLines($oFiltros->iLarguraLinha - 8, $oDadosDisciplina->oParecer->sParecer);
  $iAlturaTotal        = $oFiltros->iAlturaLinhaPadrao * $iLinhasParecer;
  $iAlturaQuebraPagina = $oFiltros->iAlturaDeQuebraPagina;
  $lQuebrouPagina      = false;
  if ($iAlturaTotal > $iAlturaQuebraPagina) {

    $lQuebrouPagina = true;
    $pdf->Line($pdf->GetX(), $iYInicial, $pdf->GetX(), $iAlturaQuebraPagina);
    $pdf->Line($oFiltros->iLarguraRetangulo, $iYInicial, $oFiltros->iLarguraRetangulo, $iAlturaQuebraPagina);
  }

  /**
   * Caso exista parecer descritivo setado, imprimimos o que foi informado. Caso contrario, imprimimos apenas linhas sem
   * valores
   */
  if (!empty($oDadosDisciplina->oParecer->sParecer)) {
//******************************************************* impress?o parecer **************************************************************************
    $pdf->SetX(14);
    $pdf->SetFont('arial', '', 10);
    $pdf->MultiCell($oFiltros->iLarguraLinha - 8,
                    $oFiltros->iAlturaLinhaPadrao,
                    "  ".$oDadosDisciplina->oParecer->sParecer,
                    1,
                    "J");
//****************************************************************************************************************************************************
  } else {

    /**
     * Quando nao houver um parecer descritivo, pegamos a posicao do Y para calcular a area do retangulo a ser impresso
     */
    $iYParecerVazio = $pdf->GetY();
    for ($iContador = 0; $iContador < 4; $iContador++) {

      $pdf->SetX(14);
      $pdf->SetFont('arial', '', 10);
      $sLinhas = "";
      $pdf->cell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao, str_pad($sLinhas, 90, "_"), 0, 1, "C");
    }
    $pdf->Rect(14, $iYParecerVazio, 182, 18);
    $pdf->setY($pdf->GetY() + 2);
  }

  $iYFinal = $pdf->GetY();
  if ($lQuebrouPagina) {
    $iYInicial = 10;
  }
  $pdf->Line($pdf->GetX(), $iYInicial, $pdf->GetX(), $iYFinal + 2);
  $pdf->Line($oFiltros->iLarguraRetangulo, $iYInicial, $oFiltros->iLarguraRetangulo, $iYFinal + 2);
}

 /**
  * Imprime as observacoes
  * @param FpdfMultiCellBorder $pdf
  * @param stdClass $oFiltros
  */
  function imprimeObservacoes(FpdfMultiCellBorder $pdf, $oFiltros, $lPaginaNova, $iAluno) {
    $cldiarioavaliacao = new cl_diarioavaliacao();

    $sWhere              = " ed95_i_aluno = {$iAluno} AND ed59_i_turma = {$oFiltros->oTurma->getCodigo()} AND ed72_i_procavaliacao = {$oFiltros->iPeriodo}";
    $sSqlDiarioAvaliacao = $cldiarioavaliacao->sql_query( "", "ed232_c_descr, ed72_t_obs", "", $sWhere );
    $result_obs          = $cldiarioavaliacao->sql_record( $sSqlDiarioAvaliacao );

    $aObservacoes = array();

    for( $iContador = 0; $iContador < pg_num_rows( $result_obs ); $iContador++ ) {
      $oDadosObservacao = db_utils::fieldsMemory( $result_obs, $iContador );

      if( !empty( $oDadosObservacao->ed72_t_obs ) ) {
        $aObservacoes[] = "{$oDadosObservacao->ed72_t_obs}";
      }
    }
    $ed72_t_obs = implode( "\n", $aObservacoes );

    $iYInicial = $pdf->GetY();

    if ($lPaginaNova) {
      $pdf->Line($pdf->GetX(), $iYInicial, $oFiltros->iLarguraRetangulo, $iYInicial);
    }

    /**
     * Imprimimos as observacoes de acordo com o que foi informado na tela de impressao
     */
    $pdf->setfillcolor(225);
    $pdf->SetFont('arial', 'b', 10);
    $pdf->SetXY(14, $pdf->GetY() + 4);
    $pdf->cell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao, "Observa??es:", 1, 1, "L", 1);

    $pdf->SetX(14);
    $pdf->SetFont('arial', '', 7);
    $pdf->MultiCell($oFiltros->iLarguraLinha - 8, $oFiltros->iAlturaLinhaPadrao, $oFiltros->sObservacao."\n".$ed72_t_obs, 1, "L");

  /**
   * Caso tenha sido marcada a opcao de imprimir a assinatura do conselheiro, acrescentamos essa linha ao final do
     * relatorio
  */
  if ($oFiltros->lAssinaturaConselheiro) {

    $pdf->SetFont('arial', '', 7);
    $pdf->SetY($pdf->GetY() + 4);
    $sProfessorConselheiro = '';

    if ($oFiltros->oTurma->getProfessorConselheiro() && $oFiltros->oTurma->getProfessorConselheiro()->getNome() != '') {
      $sProfessorConselheiro = $oFiltros->oTurma->getProfessorConselheiro()->getNome();
    }
    $sLinhas = "";
    $pdf->cell($oFiltros->iLarguraLinha, $oFiltros->iAlturaLinhaPadrao, str_pad($sLinhas, 50, "_"), 0, 1, "C");
    $pdf->cell($oFiltros->iLarguraLinha, $oFiltros->iAlturaLinhaPadrao, $sProfessorConselheiro, 0, 1, "C");
    $pdf->cell($oFiltros->iLarguraLinha, $oFiltros->iAlturaLinhaPadrao, "Professor(a)", 0, 1, "C");
  }

  $iYFinal = $pdf->GetY();
  $pdf->Line($pdf->GetX(), $iYInicial, $pdf->GetX(), $iYFinal + 2);
  $pdf->Line($oFiltros->iLarguraRetangulo, $iYInicial, $oFiltros->iLarguraRetangulo, $iYFinal + 2);
}

function assinaturaAdicional(FpdfMultiCellBorder $pdf,$assinatura1,$assinatura2,$cargo1,$cargo2)
{

  if (!empty($assinatura1) ) {
	$pdf->SetFont('arial', '', 7);
	$pdf->SetY($pdf->GetY() + 2);
	$sLinhas = "";
	$pdf->cell(20, 4, "", "L", 0, "C");
	$pdf->cell(30, 4, str_pad($sLinhas, 50, "_"), 0, 0, "L");
	$pdf->cell(50, 4, "", 0, 0, "C");
	$pdf->cell(30, 4, str_pad($sLinhas, 50, "_"), 0, 0, "L");
	$pdf->cell(60, 4, "", "R", 1, "C");
	$pdf->cell(20, 4, "", "L", 0, "C");
	$pdf->cell(30, 4, $assinatura1, 0, 0, "L");
	$pdf->cell(50, 4, "", 0, 0, "C");
	$pdf->cell(30, 4, $assinatura2, 0, 0, "L");
	$pdf->cell(60, 4, "", "R", 1, "C");
	$pdf->cell(20, 4, "", "L", 0, "C");
	$pdf->cell(30, 4, $cargo1, 0, 0, "L");
	$pdf->cell(50, 4, "", 0, 0, "C");
	$pdf->cell(30, 4, $cargo2, 0, 0, "L");
	$pdf->cell(60, 2, "", "R", 1, "C");
  }
  
}

function percfrequencia($calendar){
    $sql   = pg_query("select 
	                   ed52_c_descr,
					   ed15_c_nome
					   from 
					   calendario 
					   inner join turma on ed57_i_calendario = ed52_i_codigo
					   inner join turno on ed15_i_codigo     = ed57_i_turno
					   where ed52_i_codigo = ".$calendar);
    $resultado = pg_fetch_all($sql);
	$nome  = $resultado[0]["ed52_c_descr"];
	$turno = substr($resultado[0]["ed15_c_nome"],0,5);
	$turno_completo = trim($resultado[0]["ed15_c_nome"]);
	if( substr($nome,0,12) == 'ED. INFANTIL' or substr($nome,0,17) == 'EDUCA??O INFANTIL')
	{
		$auladadas = 200;
	}
	elseif( substr($nome,0,13) == 'ANOS INICIAIS' or substr($nome,0,20) == 'EN FUN ANOS INICIAIS' )
	{
		$auladadas = 200;
		
	}
	elseif( substr($nome,0,11) == 'ANOS FINAIS' or substr($nome,0,18) == 'EN FUN ANOS FINAIS')
	{
		// Autor: Uemerson Santana | Data: 19/01/2026 | Demanda: 18059
		// Raz?o: Para Anos Finais, verificar se o turno ? INTEGRAL para aplicar 1522 horas/aula
		//        ao inv?s de 1000 horas/aula fixas. Turno INTEGRAL tem carga hor?ria maior.
		if (strtoupper($turno_completo) == 'INTEGRAL') {
			$auladadas = 1522;
		} else {
			$auladadas = 1000;
		}
	}
	elseif( substr($nome,0,17) == 'EJA ANOS INICIAIS' or substr($nome,0,12) == 'EJA INICIAIS')
	{
		$auladadas = 170;
	}
	
	if( $turno <> 'NOITE' and (substr($nome,0,15) == 'EJA ANOS FINAIS' or substr($nome,0,10) == 'EJA FINAIS'))
	{
		$auladadas = 1200;
	}
	
	if( $turno == 'NOITE' and (substr($nome,0,15) == 'EJA ANOS FINAIS' or substr($nome,0,10) == 'EJA FINAIS'))
	{
		$auladadas = 1000;
	}	
	return $auladadas;
} 

$pdf->Output();

?>
