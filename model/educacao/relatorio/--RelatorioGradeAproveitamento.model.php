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


/**
  * Escreve a grade de aproveitamento do aluno em uma arquivo pdf
  * @author Andrio Costa <andrio.costa@dbseller.com.br>
  * @package educacao
  * @subpackage relatorio
  * @version $Revision: 1.10 $
  */

class RelatorioGradeAproveitamento extends PDFGradeAproveitamento {

  private $oGradeAproveitamento;

  private $aObservacoes = array();

  private $rel;

  public $periodoprova = 0;
  public $buscafaltas2 = 0;
  public $aluno1       = 0;
  public $encerrou     = true;
  public $turma2       = '';
  public $codTurma      = 0;
  public $evadido      = false;
  public $dependenciaF = 0;
  public $quantDepend  = 0;
  public $repconceito  = false;
  public $reprovou     = true;
  public $recFinal     = 0;
  public $quantCol     = 0;
  public $calendarioEja= '';
  /**
   *
   * @param FPDF      $oPdf         instancia do fpdf
   * @param Matricula $oMatricula   Instancia de matricula
   * @param integer   $iLimiteLinha Tamanho máximo que vai ter a linha da tabela no arquivo
   */
  public function __construct(FPDF $oPdf, Matricula $oMatricula, $iLimiteLinha,$rel, $periodo2, $alunoF) {


    $this->oPdf                   = $oPdf;
    $this->oGradeAproveitamento   = new GradeAproveitamentoAluno($oMatricula);
    $this->iLimiteLinha           = $iLimiteLinha;
    /**
     * Autor: Uemerson Santana
     * Demanda: 17328
     * Data: 2025-04-18
     */
    // $this->lApresentarNotaParcial = $this->oGradeAproveitamento->exibeNotaParcial();
    $this->controleDeFrequencia($oMatricula);
	$this->rel                    = $rel;
	$this->periodoprova           = $periodo2;
	$this->aluno1                 = $alunoF;
  }

  public function testa($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
  }


  /**
   * Escreve a grade de avaliação no pdf
   */
  public function montarGrade($ed29_c_descr=null, $ed11_c_descr=null, $guardapagina=null,$calendar=null) {//**********************************************************************
    //var_dump($ed29_c_descr, $ed11_c_descr); die("Confere");
    $this->montarCabecalho($this->oGradeAproveitamento->getProcedimentoAvaliacao()); //**************  MONTA O CABEÇALHO DAS MATERIAS


    $this->oPdf->SetFont("Arial", "", 7);
    $naoimprimefinal   = false;
	$imprimefrequencia = false;
	$a = true;
	$faltastotal       = 0;
	$faltastotal2      = 0;
	$totalfaltas       = 0;
	$periodo           = 0;
	$colunas           = 0;
	$imp               = false;
	$this->reprovou    = true;
	$this->quantDepend = 0;
	$legenda           = true;
	if($_GET["impfrequencia"] == 'yes'){
		$imprimeFreq = true;
	}else{
		$imprimeFreq = false;
	}
  if($this->periodoprova  == '1º TRIMESTRE'){
	 $periodo = 1;
	}
  if($this->periodoprova  == '2º TRIMESTRE'){
	 $periodo = 2;
	}
  if($this->periodoprova  == '3º TRIMESTRE'){
	 $periodo = 3;
	}

  if($this->periodoprova  == '1º BIMESTRE'){
		$periodo = 1;
		$imp = true;
	}
  if($this->periodoprova  == '2º BIMESTRE'){
	 $periodo = 2;
	 $imp = true;
	}
  if($this->periodoprova  == '3º BIMESTRE'){
		$periodo = 3;
		$imp = true;
	}
  if($this->periodoprova  == '4º BIMESTRE'){
		$periodo = 4;
		$imp = true;
	}

    //foreach ($this->oGradeAproveitamento->getGradeAproveitamento($this->periodoprova) as $oFaltas)
    $b                 = 1;
	$quantbolet        = 1;
	$quantlinha        = 1;
	$imprimeFaltas     = true;
	$imprimeperiodo    = 1;
	$RRSvalor          = 0;
	$a=1;
	$b = 1;
//$oDisciplina->aAproveitamento[$b]->sDescricao
    $xx=0;
    $y=1;
	$x=0;
	$d=0;
	$faltastotal2 = 0;
    $nomeTurma = @$GLOBALS["HTTP_POST_VARS"]["nomeTurma"];
    $xxx   = $this->oGradeAproveitamento->getMatricula();

	$this->turma2 = $xxx->getTurma()->getCodigo();
    $inicial = true;

//	foreach ($this->oGradeAproveitamento->getGradeAproveitamento($this->periodoprova) as $oDisciplina1) {
	foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina1) {
		$b=1;
		foreach ($oDisciplina1->aAproveitamento as $oAvaliacao1){
            if($b<=$periodo){
                $faltastotal2 += $oAvaliacao1->oAproveitamento->iFaltas;
			}
			$b++;
		}
		$faltastotalNova = $faltastotal2;
    }

    $b=1;
    $faltastotalNova = $this->faltasPeriodo(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],@$GLOBALS["HTTP_POST_VARS"]["PeriodoFalta"],@$GLOBALS["HTTP_POST_VARS"]["CodEscola"], @$GLOBALS["HTTP_POST_VARS"]["calendarioF"]);
	$discip = '';
    $disciplina1  = '';
	$this->dependenciaF = 0;
	$resFinal = array();
	$notaRS   = array(9);
	$discipRS = array(9);
	$temdependencia = false;
	$y=0;
    foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina){
			$iAlturaLinha = 4;
			$iLinhasNomeDisciplina = $this->oPdf->NbLines($this->iTamanhoDisciplina, $oDisciplina->sNome);
			if ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO"){
              $n1 = $oDisciplina->aAproveitamento[0]->oAproveitamento->nAproveitamento;
			  $n2 = $oDisciplina->aAproveitamento[1]->oAproveitamento->nAproveitamento;
			  $n3 = $oDisciplina->aAproveitamento[2]->oAproveitamento->nAproveitamento;
			  $n4 = $oDisciplina->aAproveitamento[3]->oAproveitamento->nAproveitamento;
			  $volta = 1;
			}      
      if ( $iLinhasNomeDisciplina > 1) {
			  $iAlturaLinha *= $iLinhasNomeDisciplina;
			}
			$iYAntes = $this->oPdf->GetY();
			$this->oPdf->SetFont("Arial", "", 6);
			$this->oPdf->MultiCell($this->iTamanhoDisciplina, 4, $oDisciplina->sNome, 1, "L");
			if ( $oDisciplina->sNome == 'ESTUDO DA SOCIEDADE E DA NATUREZA')
			{
			  $this->oPdf->Cell(190, 4,"", "LR", 1, "");
			  $this->oPdf->Cell(190, 4,"", "LR", 1, "");
			}
			$this->oPdf->SetY($iYAntes);
			$this->oPdf->SetX( $this->oPdf->lMargin + $this->iTamanhoDisciplina );
            $Mnota = 0.0;
			if( substr($this->periodoprova,3,8)  == 'BIMESTRE' )
			{
			    $imp   = true;
			}else{
				$imp   = false;
			}
			$RSS = 1;
			$discImp = 1; //colunas impressas da disciplina
            $Ndiscip = array();

			//busca a media da disciplina
			$oMatricula1 = $this->oGradeAproveitamento->getMatricula();
			$turma       = $oMatricula1->getTurma()->getCodigo();
			$oAno = db_utils::fieldsMemory(pg_query("select ed52_i_ano from calendario where ed52_i_codigo = ".@$GLOBALS["HTTP_POST_VARS"]["calendarioF"]),0);
			$anoF        = @$GLOBALS["HTTP_POST_VARS"]["calendarioF"];
			if( $oAno->ed52_i_ano >= 2025) // se o ano do boletim for igual ou superior a 2025
			{
				$mediaFinal  = $this->mediaC2025(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova);
			}else{
				$mediaFinal  = $this->mediaC(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova);
			}

			if( $mediaFinal >=0 )
			{
				if( $mediaFinal < 5.0 or $mediaFinal <> null or $mediaFinal <> '' ) // verifica se conseguiu ou não media
				{
					if( substr($this->periodoprova,3,8)  == 'BIMESTRE' ) // anos finais é diferente de anos iniciais'
					{
					    $this->dependenciaF = 1;
						$this->quantDepend++;
					}
				}
			}
            $impTec      = true;
			$impEmp      = true;
			$resultadoFinal = true;
			$this->recFinal = 0;
			foreach ($oDisciplina->aAproveitamento as $coluna)
			{
				if( $oDisciplina->sNome == 'LINGUA PORTUGUESA' )
				{
                    $colunas++;
                }
            }
			foreach ($oDisciplina->aAproveitamento as $oAvaliacao) {// ************************** inicio foreach avaliação ***********************************
				$oAvaliacao->oAproveitamento->nAproveitamento       = str_replace(".", ",", $oAvaliacao->oAproveitamento->nAproveitamento);
				$oAvaliacao->oAproveitamento->nMinimoAprovacao      = str_replace(".", ",", $oAvaliacao->oAproveitamento->nMinimoAprovacao);
				$oDisciplina->oNotaParcial->nNota                   = str_replace(".", ",", $oDisciplina->oNotaParcial->nNota);
				$oDisciplina->oResultadoFinal->nAproveitamentoFinal = str_replace(".", ",", $oDisciplina->oResultadoFinal->nAproveitamentoFinal);


				if ( !$oAvaliacao->lApareceBoletim ){
				  continue;
				}

				$this->oPdf->SetFont("Arial", "", 6);
				if ($oAvaliacao->oAproveitamento->nAproveitamento == "AMP" || !$oAvaliacao->oAproveitamento->lAtingiuMinimo) {
				//          $this->oPdf->SetFont("Arial", "B", 7);
				}

/*
Algo estranho estava acontecendo na disciplina TECNOLOGIA E INOVAÇÃO a variavel  $oAvaliacao->lRecuperacao	estava retornando verdadeira quando lançava a nota
do 4º bimestre, e por esse motivo estava sendo impressa na coluna REC S,
fiz um query para verificar o que estava acontecendo, comparando a disciplina LINGUA PORTUGUESA e TECNOLOGIA E INOVAÇÃO
LINGUA PORTUGUESA                         TECNOLOGIA E INOVAÇÃO
            nota    é recuperação           nota    é recuperação
1º BIMESTRE 6,5     =    false               PA     =  false
2º BIMESTRE 9,0     =    false               PA     =  false
REC S       null    =    true                não existe esse campo na disciplinan esse é o problema
3º BIMESTRE 7,9     =    false               PA     =  false
4º BIMESTRE null    =    false               PA     =  true   obs: ao lançar esta nota a variavel $oAvaliacao->lRecuperacao fica verdadeira e ela é impressa
MA          7,8     =    false               null   =  null        na coluna REC S
REC F       null    =    true                null   =  null
NF          7,8     =    false               null   =  null

esta disciplina não tem o campo REC S e REC F
Optei por fazer a impressão conforme abaixo
*/
//				if( $oDisciplina->sNome == 'LINGUA PORTUGUESA' )
//				{
//                   $colunas++;
//              }
                $sqlcalend = pg_query("SELECT substring(ed52_c_descr,1,11) as ed52_c_descr FROM escola.calendario where ed52_i_codigo = ".@$GLOBALS["HTTP_POST_VARS"]["calendarioF"]);
				$rscalend  = db_utils::fieldsmemory($sqlcalend,0);
				$this->calendarioEja = $rscalend;
				$resultado = $this->imprimeConceitoFinais(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova); // busca notas
                $ano_calendario = $this->AnoCalendario($turma);
				if( $oDisciplina->sNome == 'TECNOLOGIA E INOVAÇÃO' and $rscalend->ed52_c_descr == 'ANOS FINAIS' and trim($resultado[2]["ed72_c_valorconceito"]) <> '')
				{
			        if( $impTec)
					{
//						$resultado = $this->imprimeConceitoFinais(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova); // busca notas
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[0]["ed72_c_valorconceito"], 1, 0, "C"); // notas  1º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[0]["ed72_i_numfaltas"], 1, 0, "C");     // faltas 1º periodo
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[1]["ed72_c_valorconceito"], 1, 0, "C"); // notas  2º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[1]["ed72_i_numfaltas"], 1, 0, "C");     // faltas 2º periodo
						//Algumas escolas usam a coluna RSS e outras não
						if( $ano_calendario <= 2024) // a partir de 2025 não tera mais a coluna RSS
						{
							if( $colunas == 9)
							{
								$this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, '', 1, 0, "C"); //  RSS
							}
							$this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, '', 1, 0, "C"); // coluna recuperacao semestral em branco
						}
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[2]["ed72_c_valorconceito"], 1, 0, "C"); // notas  3º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[2]["ed72_i_numfaltas"], 1, 0, "C");     // faltas 3º periodo

						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[3]["ed72_c_valorconceito"], 1, 0, "C"); // notas  4º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[3]["ed72_i_numfaltas"], 1, 0, "C");     // faltas 4º periodo

                        $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, '', 1, 0, "C"); // media anual em branco porque é conceito
						$this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, '', 1, 0, "C"); // coluna recuperacao final em branco
						$this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $resultado[3]["ed72_c_valorconceito"], 1, 0, "C"); // media final em branco porque é conceito
						$impTec = false;
					}
					continue;
				}
				if( $oDisciplina->sNome == 'TECNOLOGIA E INOVAÇÃO' and $rscalend->ed52_c_descr == 'ANOS FINAIS' and trim($resultado[2]["ed72_c_valorconceito"]) == '')
				{
			        if( $impTec)
					{
//						$resultado = $this->imprimeConceitoFinais(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova); // busca notas
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[0]["ed72_c_valorconceito"], 1, 0, "C"); // notas  1º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[0]["ed72_i_numfaltas"], 1, 0, "C");     // faltas 1º periodo
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[1]["ed72_c_valorconceito"], 1, 0, "C"); // notas  2º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[1]["ed72_i_numfaltas"], 1, 0, "C");     // faltas 2º periodo
							//Algumas escolas usam a coluna RSS e outras não
						if( $ano_calendario <= 2024) // a partir de 2025 não tera mais a coluna RSS
						{

							if( $colunas == 9)
							{
								$this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, '', 1, 0, "C"); //  RSS
							}
							$this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, '', 1, 0, "C"); // coluna recuperacao semestral em branco
						}
						//Um aluno tinha nota apenas no 4 bimestre, ele entra nesta condição, porque a coluna $resultado[2]["ed72_c_valorconceito"] esta vazia
						//e imprime o 4 bimestre na coluna do 3, apenas nessa condição isso acontece
						if( trim($resultado[0]["ed72_c_valorconceito"])=='' and trim($resultado[1]["ed72_c_valorconceito"])=='' and trim($resultado[2]["ed72_c_valorconceito"])=='')
						{
							if( trim($resultado[3]["ed72_c_valorconceito"]) <> '' and trim($resultado[4]["ed72_c_valorconceito"]) == '' )
							{
								$resultado[4]["ed72_c_valorconceito"] = $resultado[3]["ed72_c_valorconceito"];
								$resultado[3]["ed72_c_valorconceito"] = '';
							}
						}

						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[3]["ed72_c_valorconceito"], 1, 0, "C"); // notas  3º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[3]["ed72_i_numfaltas"], 1, 0, "C");     // faltas 3º periodo

						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[4]["ed72_c_valorconceito"], 1, 0, "C"); // notas  4º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[4]["ed72_i_numfaltas"], 1, 0, "C");     // faltas 4º periodo

                        $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, '', 1, 0, "C"); // media anual em branco porque é conceito
						$this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, '', 1, 0, "C"); // coluna recuperacao final em branco
						$this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $resultado[4]["ed72_c_valorconceito"], 1, 0, "C"); // media final em branco porque é conceito
						$impTec = false;
					}
					continue;
				}
//***********************************************************************************************************************************************************************
//                if( $oDisciplina->sNome == 'TECNOLOGIA E INOVAÇÃO' or $oDisciplina->sNome == 'EMPREENDEDORISMO')
//				{
					$sqlcalend = pg_query("SELECT substring(ed52_c_descr,1,10) as ed52_c_descr FROM escola.calendario where ed52_i_codigo = ".@$GLOBALS["HTTP_POST_VARS"]["calendarioF"]);
					$rscalend  = db_utils::fieldsmemory($sqlcalend,0);
                    $this->calendarioEja = $rscalend->ed52_c_descr;
					$resultado = $this->imprimeConceitoFinaisEja(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova); // busca notas
					$ano_calendario = $this->AnoCalendario($turma);
//				}
				if( $oDisciplina->sNome == 'TECNOLOGIA E INOVAÇÃO' and $rscalend->ed52_c_descr == 'EJA FINAIS' )
				{
			        if( $impTec)
					{
//						$resultado = $this->imprimeConceitoFinais(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova); // busca notas
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[0]["ed72_c_valorconceito"],  1, 0, "C"); // notas  1º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[0]["ed72_i_numfaltas"],      1, 0, "C"); // faltas 1º periodo
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[1]["ed72_c_valorconceito"],  1, 0, "C"); // notas  2º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[1]["ed72_i_numfaltas"],      1, 0, "C"); // faltas 2º periodo
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[2]["ed72_c_valorconceito"],  1, 0, "C"); // notas  3º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[2]["ed72_i_numfaltas"],      1, 0, "C"); // faltas 3º periodo
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[3]["ed72_c_valorconceito"],  1, 0, "C"); // notas  4º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[3]["ed72_i_numfaltas"],      1, 0, "C"); // faltas 4º periodo
						$this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $resultado[3]["ed72_c_valorconceito"],1, 0, "C"); // media final em branco porque é conceito
						$impTec = false;
					}
					continue;
				}
				if( $oDisciplina->sNome == 'EMPREENDEDORISMO' and $rscalend->ed52_c_descr == 'EJA FINAIS')
				{
			        if( $impEmp)
					{
//						$resultado = $this->imprimeConceitoFinais(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova); // busca notas
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[0]["ed72_c_valorconceito"],  1, 0, "C"); // notas  1º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[0]["ed72_i_numfaltas"],      1, 0, "C"); // faltas 1º periodo
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[1]["ed72_c_valorconceito"],  1, 0, "C"); // notas  2º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[1]["ed72_i_numfaltas"],      1, 0, "C"); // faltas 2º periodo
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[2]["ed72_c_valorconceito"],  1, 0, "C"); // notas  3º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[2]["ed72_i_numfaltas"],      1, 0, "C"); // faltas 3º periodo
						$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $resultado[3]["ed72_c_valorconceito"],  1, 0, "C"); // notas  4º periodo
						$this->oPdf->Cell($this->iColunaFalta,     $iAlturaLinha, $resultado[3]["ed72_i_numfaltas"],      1, 0, "C"); // faltas 4º periodo
						$this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $resultado[3]["ed72_c_valorconceito"],1, 0, "C"); // media final em branco porque é conceito
						$impEmp = false;

					}
					continue;
				}



//***********************************************************************************************************************************************************************

				if ($oAvaliacao->lRecuperacao) {
					if( $oAvaliacao->oAproveitamento->nAproveitamento < 5 )
					{
						$this->oPdf->SetFont("Arial", "B", 6);
					}else{
						$this->oPdf->SetFont("Arial", "", 6);
					}
				    $this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
				    $this->oPdf->SetFont("Arial", "", 6);
				    continue;
				}
//obs: eu estava confundindo aqui, ele só entra aqui par imprimir as colunas RRS, REC S, MA, NF
//o restante ele imprime lá embaiso, aqui devo apenas ver quando é bimestre ou TRIMESTRE

				if ( $oAvaliacao->lResultado )
				{

				    if( $Mnota > 0 ) // imprime dividindo ou não
				    {
						if( substr($this->periodoprova,3,8)  == 'BIMESTRE' and $imp and $nomeTurma <> 'EJA') // é maior que zero e é a primeira vez
						{
							$thi->quantCol = $colunas;
							$mediaSF = $mediaFinal; // media antes da formatacao para 1 casa decimal com virgula
							$nm = explode(".", $mediaFinal);
							if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
							{
								$nm[1] = '0';
							}
							$media = $nm[0] . "," . substr($nm[1], 0, 1);


							$Mnota2 = $media;     // recalcula Mnota, porque estava ficando errado
							$Mnota  = $media;
//imprime MA
							if( $mediaSF < 5 )
							{
								$this->oPdf->SetFont("Arial", "B", 6);
							}else{
								$this->oPdf->SetFont("Arial", "", 6);
							}

							if( $colunas == 8 ) // se não tiver a coluna RSS
							{
                                 $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $Mnota2, 1, 0, "C"); // imprime sem dividir
							}
							if( $colunas == 5 ) // se for imprimir EJA anos finais
							{
                                 $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $Mnota2, 1, 0, "C"); // imprime sem dividir
							}
							if( $colunas == 9 ) // se tiver a coluna RSS
							{
                                 $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $Mnota, 1, 0, "C"); // imprime sem dividir
							}
							if( $colunas == 7 )
							{
                                 $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $Mnota2, 1, 0, "C"); // imprime media anual de 2025
							}

                            /**
                             * Autor: Uemerson Santana
                             * Demanda: 17328
                             * Data: 2025-04-17
                             */
                            if ( null == $colunas and $Mnota == $Mnota2 ) {
                                $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $Mnota, 1, 0, "C");
                            }

							$this->oPdf->SetFont("Arial", "", 6);
							$imp = false; //imprime um vez
///************************************************************************************************************
						}else{
							$mediaF = $this->mediaF(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$mediaFinal,$periodo,$ano_calendario);

							$notaRS[$y]   = $mediaF;
							$discipRS[$y] = $oDisciplina->sNome;
							$y++;
							if( $mediaF < 5 )
							{
								$this->oPdf->SetFont("Arial", "B", 6);
							}else{
								$this->oPdf->SetFont("Arial", "", 6);
							}

							$nm = explode(".", $mediaF);
							if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
							{
								$nm[1] = '0';
							}
							$nota = $nm[0] . "," . substr($nm[1], 0, 1); // não arredonda

							if( substr($this->periodoprova,3,9)  == 'TRIMESTRE') // anos iniciais ou por periodo
							{
                                $nm = $this->mediaAnosIniciais(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova);
								if( $nm < 5.0)  // verifica se o aluno de anos iniciais conseguiu media para ser aprovado
								{
									if( $inicial ) // se não conseguiu  em alguma materia, informa reprovação e não passa mais no if
									{
										$this->dependenciaF = 1;
										$inicial = false;
									}
								}
								$nm = explode(".", $nm);
								if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
								{
									$nm[1] = '0';
								}
								$nota = $nm[0] . "," . substr($nm[1], 0, 1); // não arredonda
							}

							if( $nomeTurma == 'EJA') // Eja
							{
                                $nm = $this->mediaEja(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova);
								if( $nm < 5.0)  // verifica se o aluno de anos iniciais conseguiu media para ser aprovado
								{
									if( $inicial ) // se não conseguiu  em alguma materia, informa reprovação e não passa mais no if
									{
										$this->dependenciaF = 1;
										$inicial = false;
									}
								}
								$nm = explode(".", $nm);
								if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
								{
									$nm[1] = '0';
								}
								$nota = $nm[0] . "," . substr($nm[1], 0, 1); // não arredonda
							}




							$this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $nota, 1, 0, "C");
							$this->oPdf->SetFont("Arial", "", 6);
						}
				    }else{ // nota não é maior que zero, imprime global
						if( $oAvaliacao->oAproveitamento->nAproveitamento < 5 )
						{
							$this->oPdf->SetFont("Arial", "B", 6);
						}else{
							$this->oPdf->SetFont("Arial", "", 6);
						}

					    $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
						$this->oPdf->SetFont("Arial", "", 6);
				    }
				    continue;
				}

				//******************************************* Modificado para sublinar notas substituidas nos anos finais *************************************************************************
				if ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO"){
						if($oAvaliacao->oAproveitamento->nAproveitamento < $oAvaliacao->oAproveitamento->nMinimoAprovacao){
							if( $ano_calendario <= 2024) // a partir de 2025 não tera mais a coluna RSS**************************************************************************************
					        {

								if( count($oDisciplina->aAproveitamento) == 9 ){

									if($n1 < $n2 && $n1 < $n4 && $volta == 1){
										$this->oPdf->Line(43, $this->oPdf->getY() + 2, 47, $this->oPdf->getY() + 2);
										$volta = 2;
									}
									if($n2 < $n1 && $n2 < $n4 && $volta == 1){
										$this->oPdf->Line(59, $this->oPdf->getY() + 2, 63, $this->oPdf->getY() + 2);
										$volta = 2;
									}
									//Recuperação
								  if( $n3 > 0){
										if($n3 < $n1 && $n3 < $n2 && $volta == 1){
											$this->oPdf->Line(79, $this->oPdf->getY() + 2, 83, $this->oPdf->getY() + 2);
											$volta = 2;
										}
									}

								}else{
									if($n1 < $n2 && $n1 < $n3 && $volta == 1){ // 1º bimestre
										//$this->oPdf->Line($this->oPdf->getX() + 3, $this->oPdf->getY() + 2, $this->oPdf->getX() + 7, $this->oPdf->getY() + 2);
										$this->oPdf->Line(43, $this->oPdf->getY() + 2, 47, $this->oPdf->getY() + 2);
										$volta = 2;
									}
									if($n2 < $n1 && $n2 < $n3 && $volta == 1){ // 2º bimestre
										//$this->oPdf->Line($this->oPdf->getX() + 3, $this->oPdf->getY() + 2, $this->oPdf->getX() + 7, $this->oPdf->getY() + 2);
										$this->oPdf->Line(61, $this->oPdf->getY() + 2, 65, $this->oPdf->getY() + 2);
										$volta = 2;
									}
									if( $n3 > 0)                               // recuperacao
									{
										if($n3 < $n1 && $n3 < $n2 && $volta == 1){
											$this->oPdf->Line(79, $this->oPdf->getY() + 2, 83, $this->oPdf->getY() + 2);
											$volta = 2;
										}
									}
								}
                            }
							if( $oAvaliacao->oAproveitamento->nAproveitamento < 5 ){
								$this->oPdf->SetFont("Arial", "B", 6);

								if( $oAvaliacao->oAproveitamento->nAproveitamento == 'PA' or
									$oAvaliacao->oAproveitamento->nAproveitamento == 'PI' or
									$oAvaliacao->oAproveitamento->nAproveitamento == 'TE' or
									$oAvaliacao->oAproveitamento->nAproveitamento == 'PS'
								)
								{
									$this->oPdf->SetFont("Arial", "", 6);
								}

								if($b<=$periodo){
									if( $oAvaliacao->oAproveitamento->nAproveitamento < 5 ){
										$this->oPdf->SetFont("Arial", "B", 6);
									}
									$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");

									$Mnota += floatval(str_replace(",", ".", $oAvaliacao->oAproveitamento->nAproveitamento));
									$this->oPdf->SetFont("Arial", "", 6);
								}else{
									$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, "", 1, 0, "C");
								}

							}else{
								if($b<=$periodo)
								{
									if( $oAvaliacao->oAproveitamento->nAproveitamento < 5 ){
										$this->oPdf->SetFont("Arial", "B", 6);
									}
									$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
									$Mnota += floatval(str_replace(",", ".", $oAvaliacao->oAproveitamento->nAproveitamento));
									$this->oPdf->SetFont("Arial", "",6);
								}else{
									$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, "", 1, 0, "C");
								}
							}
						}else{
							if( $oAvaliacao->oAproveitamento->nAproveitamento < 5 ){
								$this->oPdf->SetFont("Arial", "B", 6);
								if( $oAvaliacao->oAproveitamento->nAproveitamento == 'PA' or
									$oAvaliacao->oAproveitamento->nAproveitamento == 'PI' or
									$oAvaliacao->oAproveitamento->nAproveitamento == 'TE' or
									$oAvaliacao->oAproveitamento->nAproveitamento == 'PS'
								)
								{
									$this->oPdf->SetFont("Arial", "", 6);
								}
								if($b<=$periodo)
								{
									$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
									$Mnota += floatval(str_replace(",", ".", $oAvaliacao->oAproveitamento->nAproveitamento));
								}else{
									$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, "", 1, 0, "C");
								}

							}else{
								if($b<=$periodo)
								{
									if( $oAvaliacao->oAproveitamento->nAproveitamento < 5 ){
										$this->oPdf->SetFont("Arial", "B", 6);
									}
									$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
									$Mnota += floatval(str_replace(",", ".", $oAvaliacao->oAproveitamento->nAproveitamento));
									$this->oPdf->SetFont("Arial", "", 6);
								}else{
									$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, "", 1, 0, "C");
								}
							}
						}

				}else{

					if( $oAvaliacao->oAproveitamento->nAproveitamento < 5 ){
						$this->oPdf->SetFont("Arial", "B", 6);
						if($b<=$periodo){
							$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
							//no caso abaixo a nota já vinha formatada com virgula ou seja um texto, e não somava as casas decimais
							//tive que transformala em numero flutuante, para somar os decimais
							$Mnota += floatval(str_replace(",", ".", $oAvaliacao->oAproveitamento->nAproveitamento));
						}else{
							$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, "", 1, 0, "C");
						}
					}else{
						if($b<=$periodo)
						{
							$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
							$Mnota += floatval(str_replace(",", ".", $oAvaliacao->oAproveitamento->nAproveitamento));
						}else{
							$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, "", 1, 0, "C");
						}
					}
				}
				//**********************************************************************************************************************************************************
// buscar todas as faltas aqui

                if($b<=$periodo)
				{
			        $faltastotal += $oAvaliacao->oAproveitamento->iFaltas;
				    $this->oPdf->SetFont("Arial", "", 6);
				    $this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, $oAvaliacao->oAproveitamento->iFaltas, 1, 0, "C");/****************************************************/

				}else{
					$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', 1, 0, "C");/****************************************************/
				}
				//		$totaltotal += $oAvaliacao->oAproveitamento->iFaltas;




				if($oAvaliacao->oAproveitamento->nAproveitamento == 0)
				{
					 $naoimprimefinal = true;
				}else{
					 $naoimprimefinal = false;
				}

				if(($oAvaliacao->oAproveitamento->nAproveitamento == "A" or $oAvaliacao->oAproveitamento->nAproveitamento == "B" or
					$oAvaliacao->oAproveitamento->nAproveitamento == "C" or $oAvaliacao->oAproveitamento->nAproveitamento == "D"))
				{
					 $naoimprimefinal = false;
				}

					//var_dump($oAvaliacao->oAproveitamento->iFaltas); echo "<br>";

				$x++;
                $b++;
                $d++;
				$discImp++;
			}// ************************** fim foreach avaliação ************************************************************************************
			$discImp = 0;
            $b=1;
			if ($this->lApresentarNotaParcial ) {
                /**
                 * Autor: Uemerson Santana
                 * Demanda: 17328
                 * Data: 2025-04-17
                 */
			   $this->oPdf->Cell($this->iTamanhoNP, $iAlturaLinha, $oDisciplina->oNotaParcial->nNota, 1, 0, "C");
			}
			//      $naoimprimefinal = true;
			$sPercentualFrequencia = '';
			if ($oDisciplina->oFrequencia->nPercentualFrequencia !== '') {
			$sPercentualFrequencia = "{$oDisciplina->oFrequencia->nPercentualFrequencia}%";
			}
			$this->oPdf->SetFont("Arial", "", 6);
			//$this->oPdf->Cell($this->iColunaTF + 12,   $iAlturaLinha, "",               1, 0, "C");


			if( $naoimprimefinal )
			{

//				if ( $faltastotal2 > 0 )
				if ( $faltastotalNova > 0 )
				{
			       if( $imprimeFaltas)
				   {
					   //$this->oPdf->Cell(($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq), $iAlturaLinha,"Total de faltas: "."{$faltastotal2}", "", 0, "");
					   $this->oPdf->Cell(($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq), $iAlturaLinha,"Total de faltas: "."{$faltastotalNova}", "", 0, "");

					   $this->oPdf->Cell(47-($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq), 4, "","R", 0, "C");

					   $faltastotal = 0;
					   $imprimeFaltas = false;
				   }
				}else{
				   if( $imprimeFaltas)
				   {

					  $this->oPdf->Cell(($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq), $iAlturaLinha,"Total de faltas: 0", "", 0, "");
					  $this->oPdf->Cell(47-($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq), 4, "","R", 0, "C");
					  $imprimeFaltas = false;
				   }
			    }
			    $this->oPdf->Cell(47, 4, "","LR", 1, "C");
			}else{

               // $faltastotal = $this->faltasPeriodo(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],@$GLOBALS["HTTP_POST_VARS"]["PeriodoFalta"]);
				$faltastotal = $this->faltasPeriodo(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],@$GLOBALS["HTTP_POST_VARS"]["PeriodoFalta"],@$GLOBALS["HTTP_POST_VARS"]["CodEscola"], @$GLOBALS["HTTP_POST_VARS"]["calendarioF"]);
				if( $imprimeFaltas)
				{
					$linha1 = $this->oPdf->getX();
					$linha2 = $this->oPdf->getY();
					if( $faltastotal == 0 )
					{
						 $faltastotal = $this->buscafalta();
					}

					//$this->oPdf->Cell(($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq), $iAlturaLinha,"Total de faltas: "."{$faltastotal2}", "", 0, "");
					$this->oPdf->Cell(($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq), $iAlturaLinha,"Total de faltas: "."{$faltastotalNova}", "", 0, "");
					$this->oPdf->Cell(47-($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq), 4, "","R", 0, "C");
					$imprimeFaltas = false;
					$faltastotal   = 0;
					$imprimefrequencia = true;
				}else{
					$this->oPdf->Cell(47, 4, "","LR", 0, "C");
				}
			}

			if( $imprimeFreq )
			{
				$linha1 = $this->oPdf->getX();
				$linha2 = $this->oPdf->getY();
				$diasletivos2 = $this->percfrequencia($calendar);
				$sPercentualFrequencia = floor(($diasletivos2 - $faltastotalNova) / $diasletivos2 * 100);
				$sPercentualFrequencia = $sPercentualFrequencia."%";
                if( $colunas == 8 )
				{
                    $this->oPdf->Cell(144,4, "","",1,"");
				}
				$this->oPdf->Cell(144,4, "","",1,"");
				$this->oPdf->Cell(181, $iAlturaLinha," Percentual de frequencia: "."{$sPercentualFrequencia}", "", 0, "R");
				$this->oPdf->Cell(189, 4, "","R", 0, "C");

				$this->oPdf->Cell(180, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->Cell(180, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->Cell(144, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell(36, $iAlturaLinha,"PA - Participação Ativa ", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->Cell(144, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell(36, $iAlturaLinha,"PS - Participação Satisfatória ", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->Cell(144, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell(36, $iAlturaLinha,"PI - Participação Insatisfatória ", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
                $this->oPdf->Cell(144, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell(36, $iAlturaLinha,"PD - Participação Descritiva ", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->setX($linha1);
				$this->oPdf->setY($linha2);
				$imprimeFreq = false;
                $legenda = false;
			}

			if( $legenda ) // imprime a legenda mesmo sem percentual de frequencia
			{
				$linha1 = $this->oPdf->getX();
				$linha2 = $this->oPdf->getY();
				$this->oPdf->Cell(180, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->Cell(180, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->Cell(144, $iAlturaLinha,"", "", 0, "L");
				// legendas para as diciplinas de turma integral
				$this->oPdf->Cell(36, $iAlturaLinha,"PA - Participação Ativa ", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->Cell(144, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell(36, $iAlturaLinha,"PS - Participação Satisfatória ", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->Cell(144, $iAlturaLinha,"", "", 0, "L");
				$this->oPdf->Cell(36, $iAlturaLinha,"PI - Participação Insatisfatória ", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
                $this->oPdf->Cell(144, $iAlturaLinha,"", "", 0, "L");
                $this->oPdf->Cell(36, $iAlturaLinha,"PD - Participação Descritiva ", "", 0, "L");
				$this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, '', '', 1, "C");
				$this->oPdf->setX($linha1);
				$this->oPdf->setY($linha2);
				$legenda = false;
			}


			if ( $oDisciplina->oResultadoFinal->nAproveitamentoFinal != '' && $oDisciplina->oResultadoFinal->sResultadoAprovacao != "A") {
			$this->oPdf->SetFont("Arial", "B", 6);
			}

			$mAproveitamentoFinal          = '';
			$sTermoResultadoFinalAbreviado = '';

			if ( $this->oGradeAproveitamento->getMatricula()->isConcluida() ) {
			$mAproveitamentoFinal          = $oDisciplina->oResultadoFinal->nAproveitamentoFinal;
			$sTermoResultadoFinalAbreviado = $oDisciplina->oResultadoFinal->sTermoResultadoFinalAbreviado;
			}
			if( !$naoimprimefinal){
			 $this->oPdf->ln();
			}
			if ( $oDisciplina->sNome == 'ESTUDO DA SOCIEDADE E DA NATUREZA')
			{
			   //$this->oPdf->Cell(189, 4,"", "LR", 1, "");
			   //$this->oPdf->Cell(189, 4,"", "LR", 1, "");
			}
			if ( $oDisciplina->sNome == 'TECNOLOGIA E INOVAÇÃO')
			{
			   //$this->oPdf->Cell(189, 4,"", "LR", 1, "");
			}
    }//ppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppp

//********************************************************************************************************************
    if( $totalfaltas > 0 and $faltastotal==0 )
	{
		$this->oPdf->SetFont("Arial", "", 7,'','');
		//$this->oPdf->text($linha1+1,$linha2+2,"Total de faltas: "."{$totalfaltas}", "", 0, "");
		$this->oPdf->text($linha1+1,$linha2+2,"Total de faltas: "."{$faltastotalNova}", "", 0, "");
	}
//********************************************************************************************************************


    $oMatricula = $this->oGradeAproveitamento->getMatricula();
    $sAndamento = $oMatricula->retornaAndamentoDaMatricula();

	if( $sAndamento == 'EM ANDAMENTO') // a pedido de suellem, se a turma não foi encerrada ainda, mas ja tem lançamento no 4ºbimestre ou 3ºtrimestre
	{	                               // devera aparecer no boletim se tem nota para ser aprovado ou não
	    $encerramento = $this->final_anoletivo(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova);
		for($x=0;$x<pg_num_rows($encerramento);$x++)
		{
			$oencerramento = db_utils::fieldsmemory($encerramento,$x);
			if( $oencerramento->ed72_i_valornota == null and trim($oencerramento->ed72_c_valorconceito) == '' ) //foi lançado todos os periodos
			{
				$this->encerrou = false;
			}
		}
//****************************************************************************************************************************
        $quantDep = 0; // variavel para quantidade de dependencia
		$nomeDisc = array();
		if( $this->encerrou == true) // ja tem todos os lançamentos de nota
		{
			if( $this->dependenciaF == 0) // teve alguma nota inferior a 5 e ficou com dependencia/reprovacao, verificação feita na linha 558 anos finais e 720 anos iniciais
			{
				$sAndamento = "APROVADO"; // se não teve, passou direto
			}else{ //se a nota for inferior a cinco ou nula
				if( substr($this->periodoprova,3,9) == 'TRIMESTRE' ) // é avaliado por trimestre, anos iniciais
				{
					$sAndamento = 'REPROVADO';   // se os anos iniciais for superior ou igual a 3º ano, existe disciplinas avaliado por nota e não conseguiu media
				}else{ // anos finais
					$alunoEvadido =   $this->evadiu(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],null); // verifica se o aluno fez as dependencias ou não tinha
                    if( !$alunoEvadido ) // se fez ou não tinha dependencia
					{
					    $Naprovado = false;
						$VerAprov  = true;
						$recuper   = false;
                        $nomedisciplina = '';
						for($x=0;$x<=17;$x++)
						{
							if( $notaRS[$x] < 5.0) // procurar as notas menores que < 5 por disciplina
							{
							    if( $nomedisciplina <> $discipRS[$x])
								{
									$this->notasPorDisciplina(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$discipRS[$x],$turma,$periodo); // verificar se foi feita a recuperação
									if($this->recFinal == null ) //se a recuperação for null não foi feito
									{
										$recuper = true;
									}else{ // diferente de null, foi feita a recuperação
										if( $this->recFinal > 5.0 ) // a recuperação é maior que 5??? para que o aluno tenha a possibilidade de ser aprovado sem dependencia
										{
											if( $VerAprov ) // verifica matéria por matéria até que encontre alguma menor que 5 ou não
											{
												if( (($this->recFinal+$notaRS[$x])/2) < 5.0) // a nota da recuperação com a media anual divido por 2 gera RES F, é maior ou menor que 5??
												{
													$Naprovado = true; //se for menor o aluno fica na dependencia
													$VerAprov  = false;// não entra aqui novamente, ja encontrou dependencia
												}
											}
										}else{ // não atingiu media na recuperacao tera que fazer dependencia
											$Naprovado = true; // a recuperação é menor que 5, não conseguiu media e ficou em dependencia
											if( (($this->recFinal+$notaRS[$x])/2) < 5.0) // dependendo da quantidade de dependencia, maior que 2 é reprovado
											{
												$quantDep++; // conta as dependencias
												$nomeDisc[$quantDep] = $discipRS[$x]; // nome disciplina para verificar se pertence a uma dependencia anterior
											}
										}
									}
								}
							    $nomedisciplina = $discipRS[$x];
							}
						}
						// inicia a checagem da situação do aluno
						if( !$Naprovado ) // fez a recuperação e foi aprovado
						{
							$sAndamento = "APROVADO";
						}else{ // fez, mas não conseguiu nota e ficou em dependencia

							$sAndamento = "APROVADO COM PROGRESSÃO PARCIAL / DEPENDÊNCIA";
						}
                        if( $recuper ) // ainda não fez recuperação
						{
							$sAndamento = "EM RECUPERAÇÃO"; // continua a recuperação
						}
						if( $quantDep > 2) // fez todo o processo, inclusive recuperação, mas se tiver mais que duas dependencias não pode fazer e é reprovado
						{
							$sAndamento = "REPROVADO";
						}
						if($quantDep > 0 and $quantDep <= 2 ) //ficou em até 2 disciplina e pode fazer dependencia
						{
						    // verifica se ja estava em dependencia na mesma disciplina no ano anterior e checa se o aluno passou no ano anterior
							$verificaDiscip = $this->checa_dep($nomeDisc,@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"]);
							if( $verificaDiscip == 't' ) // se estava e passou ou a disciplina é outra
							{
								$sAndamento = "APROVADO COM PROGRESSÃO PARCIAL / DEPENDÊNCIA"; // se passou ou a dependencia e outra disciplina pode fazer a dependencia
							}else{
								$sAndamento = "REPROVADO"; // ficou em dependencia na mesma disciplina do ano anterior e nao passou no ano anterior
							}
						}
						$aprovado = $this->depanoant(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"]);
						if( $quantDep == 2 and !$aprovado) // ficou em 2 disciplina e devia uma no ano anterior e foi reprovado, só pode 2 dependencias
						{
							$sAndamento = "REPROVADO";
						}
					}else{
						$sAndamento = "REPROVADO"; // tinha dependencias mas foi evadido, não fez as dependencias
					}
				}
			}
//****************************************************************************************************************************
// Português, Matemática, Histórica, Geografia e Ciências.
// tem a nota do terceiro trimestre mas a turma pode ser segundo ano e avaliado por conceito
			$sqlseg = 'select substring(ed57_c_descr,1,4) as turma from turma where ed57_i_codigo = '.$this->turma2;  // verifica se é o segundo, ou seja so conceito
			$rsseg  = pg_query($sqlseg);
			$oseq   = db_utils::fieldsMemory($rsseg,0);
			if( $oseq->turma == 'EF 2' )  // se for o segundo ano
			{
				if($this->repconceito) // verifica se o conceito do ultimo trimestre é igual a D
				{
				    $sAndamento = 'REPPROVADO';	// D reprova
				}else{
					$sAndamento = 'APROVADO';   // os outros não
				}
			}
		}
	}
	// se a turma ja foi encerrada, tudo no if acima não será usado e passará diretamente para o if abaixo
//******************************************************************************************************************************************************************
    if( $sAndamento == 'APROVADO' or $sAndamento == 'REPROVADO' or $sAndamento == 'EM ANDAMENTO' or 'APROVADO COM PROGRESSAO PARCIAL / DEPENDÊNCIA')
    {
	    $encerramento = $this->final_anoletivo(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova);
		for($x=0;$x<pg_num_rows($encerramento);$x++)
		{
			$oencerramento = db_utils::fieldsmemory($encerramento,$x);
			if( $oencerramento->ed72_i_valornota == null and trim($oencerramento->ed72_c_valorconceito) == '' ) //foi lançado todos os periodos
			{
				$this->encerrou = false;
			}
		}
//****************************************************************************************************************************
        $quantDep = 0; // variavel para quantidade de dependencia
		$nomeDisc = array();
		if( $this->encerrou == true) // ja tem todos os lançamentos de nota
		{

			if( $this->dependenciaF == 0) // teve alguma nota inferior a 5 e ficou com dependencia/reprovacao, verificação feita na linha 558 anos finais e 720 anos iniciais
			{
				$sAndamento = "APROVADO"; // se não teve, passou direto
			}else{ //se a nota for inferior a cinco ou nula
				if( substr($this->periodoprova,3,9) == 'TRIMESTRE' ) // é avaliado por trimestre, anos iniciais
				{
					$sAndamento = 'REPROVADO';   // se os anos iniciais for superior ou igual a 3º ano, existe disciplinas avaliado por nota e não conseguiu media
				}else{ // anos finais

					$alunoEvadido =   $this->evadiu(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],null); // verifica se o aluno fez as dependencias ou não tinha
                    if( !$alunoEvadido ) // se fez ou não tinha dependencia
					{
					    $Naprovado = false;
						$VerAprov  = true;
						$recuper   = false;
						$nomedisciplina = '';
						for($x=0;$x<=17;$x++)
						{

                            if( $notaRS[$x] <> null) // verifica se o indice do array contem algum valor
                            {
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/busca2.txt","a+");
//fwrite($arq, $discipRS[$x].'--'.$notaRS[$x]);
//fwrite($arq,"\r\n");
//fclose($arq);

								if( $notaRS[$x] < 5.0) // procurar as notas menores que < 5 por disciplina
								{
									if( $nomedisciplina <> $discipRS[$x])
									{
										$Naprovado = true;
										$quantDep++; // conta as dependencias
									}
									$nomeDisc[$quantDep] = $discipRS[$x]; // nome disciplina para verificar se pertence a uma dependencia anterior
								}
							}
							$nomedisciplina = $discipRS[$x];
						}

						// inicia a checagem da situação do aluno
						if( !$Naprovado ) // fez a recuperação e foi aprovado
						{
							$sAndamento = "APROVADO";
						}else{ // fez, mas não conseguiu nota e ficou em dependencia

							$sAndamento = "APROVADO COM PROGRESSÃO PARCIAL / DEPENDÊNCIA";
						}
						if( $quantDep > 2) // fez todo o processo, inclusive recuperação, mas se tiver mais que duas dependencias não pode fazer e é reprovado
						{
							$sAndamento = "REPROVADO";
						}

						if($quantDep > 0 and $quantDep <= 2 ) //ficou em até 2 disciplina e pode fazer dependencia
						{
						    // verifica se ja estava em dependencia na mesma disciplina no ano anterior e checa se o aluno passou no ano anterior
							$verificaDiscip = $this->checa_dep($nomeDisc,@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"]);
							if( $verificaDiscip == 't' ) // se estava e passou ou a disciplina é outra
							{
								$sAndamento = "APROVADO COM PROGRESSÃO PARCIAL / DEPENDÊNCIA"; // se passou ou a dependencia e outra disciplina pode fazer a dependencia
							}else{
								$sAndamento = "REPROVADO"; // ficou em dependencia na mesma disciplina do ano anterior e nao passou no ano anterior
							}
						}
						$aprovado = $this->depanoant(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"]);
						if( $quantDep == 2 and !$aprovado) // ficou em 2 disciplina e devia uma no ano anterior e foi reprovado, só pode 2 dependencias
						{
							$sAndamento = "REPROVADO";
						}
					}else{
						$sAndamento = "REPROVADO"; // tinha dependencias mas foi evadido, não fez as dependencias
					}
				}
			}
//****************************************************************************************************************************
// Português, Matemática, Histórica, Geografia e Ciências.
// tem a nota do terceiro trimestre mas a turma pode ser segundo ano e avaliado por conceito
			$sqlseg = 'select substring(ed57_c_descr,1,4) as turma from turma where ed57_i_codigo = '.$this->turma2;  // verifica se é o segundo, ou seja so conceito
			$rsseg  = pg_query($sqlseg);
			$oseq   = db_utils::fieldsMemory($rsseg,0);
			if( $oseq->turma == 'EF 2' )  // se for o segundo ano
			{
				if($this->repconceito) // verifica se o conceito do ultimo trimestre é igual a D
				{
				    $sAndamento = 'REPPROVADO';	// D reprova
				}else{
					$sAndamento = 'APROVADO';   // os outros não
				}
			}
		}
//  O resultado final estava saindo reprovado, porque a rotina aprovadoComProgressaoParcial() na classe DiarioAvaliacaoDisciplina.model.php, linha 1025
//  Só retorna que existe progressão parcial depois que o aluno foi matriculado para o ano seguinte e tambem matriculado na progressão parcial
//  e ainda não tinha sido feito, então usei o mesmo procedimento para colocar o resultado quando todas as notas estiverem lançadas, mas o procedimento de
//  encerramento ainda não foi feito

		// verifica se o aluno foi aprovado pelo conselho

		$sql = "select ed52_i_codigo from calendario inner join turma on ed57_i_calendario = ed52_i_codigo where ed57_i_codigo = {$turma}";
		$result = pg_query($sql);
		$oDados = db_utils::fieldsMemory($result,0);
 		$calendario2 = $oDados->ed52_i_codigo;

		$sql2 = " select ed60_i_aluno from matricula where ed60_i_codigo = {$this->aluno1}";
		$sqlA = pg_query($sql2);
		$oAluno = db_utils::fieldsMemory($sqlA, 0);

//*********************************************************************************************
// verifica se o aluno foi aprovado pelo conselho
		$oDaoAprovConselho = new cl_aprovconselho();
		$sCamposAprovCons  = " distinct ed11_c_descr as serie_conselho, ed52_i_ano, ed253_aprovconselhotipo, ed253_t_obs";
		$sCamposAprovCons .= ", ed232_c_descr, ed253_alterarnotafinal, ed253_avaliacaoconselho";
		$sWhereAprovCons   = "     ed95_i_aluno = {$oAluno->ed60_i_aluno} ";
		$sWhereAprovCons  .= " and ed52_i_codigo = {$calendario2} ";
		$sSqlAprovCons     = $oDaoAprovConselho->sql_query( "", $sCamposAprovCons, "ed11_c_descr, ed52_i_ano", $sWhereAprovCons );
		$rsAprovConselho   = $oDaoAprovConselho->sql_record( $sSqlAprovCons );
		$iLinhasAprovCons  = $oDaoAprovConselho->numrows;
		$aAprovadoBaixaFrequencia   = array();
		$aAprovadoConselhoRegimento = array();
		$formAprov = "";
		if ( $iLinhasAprovCons > 0 ) {
			for ( $iContObs = 0; $iContObs < $iLinhasAprovCons; $iContObs++ ) {
				$oDadosAprovConselho = db_utils::fieldsmemory( $rsAprovConselho, $iContObs );
				switch ($oDadosAprovConselho->ed253_aprovconselhotipo) {
					 //Valida se a aprovação foi por conselho
					case 1:
						$formAprov = "APROVADO"; // APROVADO PELO CONSELHO
					break;

					case 2:
						$formAprov = "APROVADO"; //RECLASSIFICACAO POR BAIXA FRQUENCIA
					break;

					case 3:
						$formAprov = "APROVADO"; //APROVADO CONFORME REGIMENTO ESCOLAR
					break;
				}
			}
		}
		// a pedido de Suellem a variavel $formAprov, bastava estar como APROVADO
        if( $formAprov <> "") //esta varável recebe dados quando existe aprovação pelo conselho na tabela aprovconselho
		{
			$sAndamento = $formAprov;
		}
//*********************************************************************************************
        $this->oPdf->Cell(189, 4,"RESULTADO FINAL:  ".$sAndamento, "LR", 1, "L");
	}else{
		$this->oPdf->Cell(189, 4,"RESULTADO FINAL:  EM ANDAMENTO", "LR", 1, "L");	// houve uma situação que um aluno não tinha situação definida então coloquei essa condição
	}

//$imprimeperiodo++;
//}
/**
 * Autor: Uemerson Santana
 * Demanda: 17328
 * Data: 2025-04-15
 */
    $this->imprimeDependencia(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"], $oAno->ed52_i_ano);
}//*******************************************************************************************************************************************



  /**
   * Escreve as legendas no pdf
   * @param  integer $iTipo
   */
  public function imprimirLegendas($iTipo = 1) {

    $sFrequencia = "AD - Aulas Dadas";
    if ($this->sControleFrequencia == 'DL') {
      $sFrequencia = "DL - Dias Letivos";
    }

    $aLegendas    = array();
    $aLegendas[1] = "TF - Total Faltas | {$sFrequencia} | FA - Faltas Abonadas";
    $aLegendas[2] = "FT - Faltas | TF - Total Faltas | {$sFrequencia} | FA - Faltas Abonadas | Freq. - Percentual de Frequência | Aprov. - Aproveitamento";

    $this->oPdf->SetFont("Arial", "B", 7);
    $this->oPdf->Cell($this->iLimiteLinha, 4, $aLegendas[$iTipo], 1, 1, "L");
  }

  /**
   * Escreve o minimo para aprovação no pdf
   */
  public function imprimirMinimoParaAprovacao() {

    $this->oPdf->SetFont("Arial", "B", 7);
    $mMinino  = "Mínimo para Aprovação Anual: ";
    //$mMinino .= $this->oGradeAproveitamento->getMinimoParaAprovacao();
    $mMinino .= str_replace(".", ",", $this->oGradeAproveitamento->getMinimoParaAprovacao());
    //$notavirgula = str_replace(".", ",", $arr_explode[3]);
    $this->oPdf->Cell($this->iLimiteLinha, 4, $mMinino, 1, 1, "L");
  }

  public function imprimirNiveis() {

    $aResultados          = $this->oGradeAproveitamento->getProcedimentoAvaliacao()->getResultados();
    $listaNiveisDescricao = array();

    foreach ($aResultados as $oResultado) {

      if ($oResultado-> geraResultadoFinal()) {

        $conceitos = $oResultado->getFormaDeAvaliacao()->getConceitos();

        if(!empty($conceitos)) {

          foreach ($conceitos as $conceito) {
            $listaNiveisDescricao[] = $conceito->iOrdem .'-'. $conceito->sConceito .':'. $conceito->sDescricao;
          }
        }
      }
    }

    if(!empty($listaNiveisDescricao)) {

      $this->oPdf->SetFont('arial', 'b', 7);
      $this->oPdf->Cell($this->iLimiteLinha, 4, "Níveis:", 1, 1, "L", 1);
      $this->oPdf->SetFont('arial', '', 7);
      $this->oPdf->MultiCell($this->iLimiteLinha, 4, implode(' | ', $listaNiveisDescricao), "RL", "L", 0, 0);
    }
  }

  /**
   * Escreve o Andamento da Matricula
   */
  public function imprimirAndamentoDaMatricula() {
	$encerramento= $this->final_anoletivo(@$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"],$oDisciplina->sNome,$turma,$periodo,$this->periodoprova);
	for($x=0;$x<count($encerramento->ed72_i_valornota);$x++)
	{
		if( $encerramento->ed72_i_valornota == null)
		{
			$this->encerrou = false;
		}
	}
	if( $this->encerrou )
	{
		$sAndamento = "APROVADO";
	}
    $oMatricula = $this->oGradeAproveitamento->getMatricula();
    $sAndamento = $oMatricula->retornaAndamentoDaMatricula();
    $iEnsino    = $oMatricula->getEtapaDeOrigem()->getEnsino()->getCodigo();
    $iAno       = $oMatricula->getTurma()->getCalendario()->getAnoExecucao();

    if ( $sAndamento == "APROVADO" ) {

      $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iEnsino, "A", $iAno);
      if (isset($aDadosTermo[0])) {
        $sAndamento = $aDadosTermo[0]->sDescricao;
      }
    }

    if ( $sAndamento == "REPROVADO" ) {
      $aDadosTermo = DBEducacaoTermo::getTermoEncerramento( $iEnsino, 'R', $iAno);

      if ( isset($aDadosTermo[0]) ) {
        $sAndamento = $aDadosTermo[0]->sDescricao;
      }
    }

    $this->oPdf->SetFont("Arial", "B", 7);
    $mMinino  = "O aluno foi considerado: ";
    $mMinino .= $sAndamento;
	$this->oPdf->line(201,35,201,90);
    $this->oPdf->Cell($this->iLimiteLinha-23, 4, $mMinino, 1, 1, "");


  }

    /**
     * Verifica o resultado final simplificado do aluno.
     *
     * Esta função verifica de forma simples se o aluno foi reprovado em alguma disciplina,
     * analisando apenas o termo de resultado final abreviado de cada disciplina.
     *
     * @return string "Reprovado" se o aluno tiver alguma disciplina com resultado "REP", ou "Aprovado" caso contrário
     * @see retornaFinal2() Para uma verificação mais completa com todas as regras de aprovação
     * @access public
     */
  public function retornaFinal() {
    $oMatricula = $this->oGradeAproveitamento->getMatricula();

    foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {
      if ( $this->oGradeAproveitamento->getMatricula()->isConcluida() ) {
        $mAproveitamentoFinal          = $oDisciplina->oResultadoFinal->nAproveitamentoFinal;
        $sTermoResultadoFinalAbreviado = $oDisciplina->oResultadoFinal->sTermoResultadoFinalAbreviado;
        if($sTermoResultadoFinalAbreviado == "REP"){
          return "Reprovado";
        }
      }
    }
    return "Aprovado";
  }

  /**
     * Determina o resultado final do aluno seguindo todas as regras de aprovação/reprovação.
     *
     * Esta função implementa uma verificação completa do resultado do aluno, considerando:
     * - Diferentes tipos de ensino (Anos Iniciais, Anos Finais, EJA, Educação Infantil)
     * - Aprovações por conselho escolar
     * - Progressão parcial (dependência)
     * - Avaliação por conceito ou nota, dependendo da série
     * - Frequência mínima
     * - Recuperações (semestral e final)
     *
     * Possíveis resultados:
     * - "Aprovado"
     * - "Reprovado"
     * - "Aprovado com Progressão Parcial / Dependência"
     * - "Reprovado por Frequência"
     * - "Em Andamento"
     *
     * Regras por nível de ensino:
     * - Anos Iniciais (1º e 2º): Avaliação por conceito
     * - Anos Iniciais (3º ao 5º): Média aritmética dos trimestres ? 5,0
     * - Anos Finais (6º ao 9º): Máximo 2 dependências, sem repetir dependência da mesma disciplina
     * - EJA: Regras adaptadas ao sistema de 4 bimestres
     *
     * @return string Situação final do aluno conforme regras da instituição
     * @access public
     */
  public function retornaFinal2() {
    $oMatricula = $this->oGradeAproveitamento->getMatricula();
    $sTurma = $oMatricula->getTurma()->getDescricao();
    $sCalendario = $oMatricula->getTurma()->getCalendario()->getDescricao();
    $sEtapa = $oMatricula->getEtapaDeOrigem()->getNome();

    // Verifica se já existe resultado encerrado no diário final
    $lResultadoFinalJaDefinido = false;
    $sResultadoFinal = "Aprovado"; // Valor padrão

    // Verifica aprovação por conselho
    $sqlConselho = "
      select distinct ed253_aprovconselhotipo
      from aprovconselho
      inner join diario on diario.ed95_i_codigo = aprovconselho.ed253_i_diario
      inner join aluno on aluno.ed47_i_codigo = diario.ed95_i_aluno
      inner join matricula on ed60_i_aluno = ed47_i_codigo
      where ed60_i_codigo = {$oMatricula->getCodigo()}";

    $rsConselho = db_query($sqlConselho);
    if (pg_num_rows($rsConselho) > 0) {
      $oDadosConselho = db_utils::fieldsMemory($rsConselho, 0);
      if ($oDadosConselho->ed253_aprovconselhotipo > 0) {
        return "Aprovado"; // Aprovado por algum tipo de conselho
      }
    }

    // Se for Anos Iniciais (1º ao 5º ano)
    if (strpos($sCalendario, "ANOS INICIAIS") !== false) {
      // 1º e 2º anos são avaliados por conceito
      if ($sEtapa == "1º ANO" || $sEtapa == "2º ANO") {
        return $this->verificaAprovacaoPorConceito($oMatricula->getCodigo(), $oMatricula->getTurma()->getCodigo());
      }
      // 3º ao 5º são avaliados por nota
      else {
        $lReprovadoEmAlguma = false;

        foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {
          // Verifica apenas as disciplinas principais
          $sNomeDisciplina = strtoupper($oDisciplina->sNome);
          if (in_array($sNomeDisciplina, ['LINGUA PORTUGUESA', 'MATEMÁTICA', 'HISTÓRIA', 'GEOGRAFIA', 'CIENCIAS'])) {
            // Verifica se tem todas as notas lançadas (3 trimestres)
            $lTodasNotasLancadas = true;
            $fSomaNotas = 0;
            $iQtdNotas = 0;

            foreach ($oDisciplina->aAproveitamento as $oAvaliacao) {
              if ($oAvaliacao->oAproveitamento->nAproveitamento !== null &&
                  $oAvaliacao->oAproveitamento->nAproveitamento !== '') {
                $fSomaNotas += floatval(str_replace(",", ".", $oAvaliacao->oAproveitamento->nAproveitamento));
                $iQtdNotas++;
              } else {
                $lTodasNotasLancadas = false;
              }
            }

            // Se todas as notas foram lançadas, calcula média
            if ($lTodasNotasLancadas && $iQtdNotas > 0) {
              $fMedia = $fSomaNotas / $iQtdNotas;
              if ($fMedia < 5.0) {
                $lReprovadoEmAlguma = true;
                break;
              }
            } else {
              // Se nem todas as notas foram lançadas
              return "Em Andamento";
            }
          }
        }

        if ($lReprovadoEmAlguma) {
          return "Reprovado";
        }
      }
    }
    // EJA (Educação de Jovens e Adultos)
    else if (strpos($sCalendario, "EJA") !== false) {
      $lReprovadoEmAlguma = false;

      foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {
        // Verifica as disciplinas principais
        $sNomeDisciplina = strtoupper($oDisciplina->sNome);
        if (in_array($sNomeDisciplina, ['LINGUA PORTUGUESA', 'MATEMÁTICA', 'HISTÓRIA', 'GEOGRAFIA', 'CIENCIAS'])) {
          // Verifica se tem todas as notas lançadas (4 bimestres na EJA)
          $lTodasNotasLancadas = true;
          $fSomaNotas = 0;
          $iQtdNotas = 0;

          foreach ($oDisciplina->aAproveitamento as $oAvaliacao) {
            if ($oAvaliacao->oAproveitamento->nAproveitamento !== null &&
                $oAvaliacao->oAproveitamento->nAproveitamento !== '') {
              $fSomaNotas += floatval(str_replace(",", ".", $oAvaliacao->oAproveitamento->nAproveitamento));
              $iQtdNotas++;
            } else {
              $lTodasNotasLancadas = false;
            }
          }

          // Se todas as notas foram lançadas, calcula média
          if ($lTodasNotasLancadas && $iQtdNotas > 0) {
            $fMedia = $fSomaNotas / $iQtdNotas;
            if ($fMedia < 5.0) {
              $lReprovadoEmAlguma = true;
              break;
            }
          } else {
            // Se nem todas as notas foram lançadas
            return "Em Andamento";
          }
        }
      }

      if ($lReprovadoEmAlguma) {
        return "Reprovado";
      }
    }
    // Educação Infantil
    else if (strpos($sCalendario, "ED. INFANTIL") !== false || strpos($sCalendario, "EDUCAÇÃO INFANTIL") !== false) {
      return $this->verificaAprovacaoPorConceitoInfantil($oMatricula->getCodigo(), $oMatricula->getTurma()->getCodigo());
    }
    // Anos Finais (6º ao 9º ano)
    else if (strpos($sCalendario, "ANOS FINAIS") !== false) {
      // Verificar progressão parcial (dependência)
      $iQuantDependencias = 0;
      $lReprovadoEmAlguma = false;
      $lTodasNotasLancadas = true;

      foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {
        // Verifica média final
        if ($oDisciplina->oResultadoFinal->sResultadoAprovacao == "REP" ||
            $oDisciplina->oResultadoFinal->sTermoResultadoFinalAbreviado == "REP") {
          $iQuantDependencias++;
          if ($iQuantDependencias > 2) {
            return "Reprovado";
          }
          $lReprovadoEmAlguma = true;
        }

        // Verifica se todas as notas foram lançadas
        foreach ($oDisciplina->aAproveitamento as $oAvaliacao) {
          if (!$oAvaliacao->lRecuperacao && !$oAvaliacao->lResultado) {
            if ($oAvaliacao->oAproveitamento->nAproveitamento === null ||
                $oAvaliacao->oAproveitamento->nAproveitamento === '') {
              $lTodasNotasLancadas = false;
              break;
            }
          }
        }
      }

      if (!$lTodasNotasLancadas) {
        return "Em Andamento";
      }

      if ($lReprovadoEmAlguma && $iQuantDependencias <= 2) {
        return "Aprovado com Progressão Parcial / Dependência";
      }
    }

    // Verifica frequência mínima (75%)
    $iFrequenciaMinima = 75;
    $iTotalFaltas = 0;
    $iTotalAulas = 0;

    foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {
      foreach ($oDisciplina->aAproveitamento as $oAvaliacao) {
        $iTotalFaltas += $oAvaliacao->oAproveitamento->iFaltas;
      }
    }

    // Calcular aulas dadas conforme o tipo de ensino
    $iTotalAulas = $this->calcularTotalAulasDadas($sCalendario);

    if ($iTotalAulas > 0) {
      $fPercentualFrequencia = (($iTotalAulas - $iTotalFaltas) / $iTotalAulas) * 100;
      if ($fPercentualFrequencia < $iFrequenciaMinima) {
        return "Reprovado por Frequência";
      }
    }

    return $sResultadoFinal;
  }
  private function verificaAprovacaoPorConceito($iCodigoMatricula, $iCodigoTurma) {
    $sSql = "
      select distinct ed74_c_resultadoaprov as aprov
      from diariofinal
      inner join diario on ed95_i_codigo = ed74_i_diario
      inner join regencia on ed59_i_codigo = ed95_i_regencia
      inner join disciplina on ed12_i_codigo = ed59_i_disciplina
      inner join caddisciplina on ed232_i_codigo = ed12_i_caddisciplina
      inner join matricula on ed60_i_aluno = ed95_i_aluno
      where ed60_i_codigo = {$iCodigoMatricula}
      and ed59_i_turma = {$iCodigoTurma}
      and ed232_c_descr in ('LINGUA PORTUGUESA', 'HISTÓRIA', 'GEOGRAFIA', 'CIENCIAS', 'MATEMÁTICA')";

    $rsResultado = db_query($sSql);

    // Se não houver resultados, ainda está em andamento
    if (pg_num_rows($rsResultado) == 0) {
      return "Em Andamento";
    }

    for ($i = 0; $i < pg_num_rows($rsResultado); $i++) {
      $oDados = db_utils::fieldsMemory($rsResultado, $i);
      if ($oDados->aprov != 'A') {
        return "Reprovado";
      }
    }

    return "Aprovado";
  }

  private function verificaAprovacaoPorConceitoInfantil($iCodigoMatricula, $iCodigoTurma) {
    $sSql = "
      select distinct ed74_c_resultadoaprov as aprov
      from diariofinal
      inner join diario on ed95_i_codigo = ed74_i_diario
      inner join regencia on ed59_i_codigo = ed95_i_regencia
      inner join disciplina on ed12_i_codigo = ed59_i_disciplina
      inner join caddisciplina on ed232_i_codigo = ed12_i_caddisciplina
      inner join matricula on ed60_i_aluno = ed95_i_aluno
      where ed60_i_codigo = {$iCodigoMatricula}
      and ed59_i_turma = {$iCodigoTurma}
      and ed232_c_descr = 'CAMPOS DE EXPERIENCIA'";

    $rsResultado = db_query($sSql);

    // Na Educação Infantil, todos são aprovados
    if (pg_num_rows($rsResultado) == 0) {
      return "Em Andamento";
    }

    return "Aprovado";
  }

  private function calcularTotalAulasDadas($sCalendario) {
    $iTotalAulas = 0;

    if (strpos($sCalendario, "ED. INFANTIL") !== false ||
        strpos($sCalendario, "EDUCAÇÃO INFANTIL") !== false) {
      $iTotalAulas = 200;
    }
    else if (strpos($sCalendario, "ANOS INICIAIS") !== false) {
      $iTotalAulas = 200;
    }
    else if (strpos($sCalendario, "ANOS FINAIS") !== false) {
      $iTotalAulas = 1000;
    }
    else if (strpos($sCalendario, "EJA INICIAIS") !== false ||
             strpos($sCalendario, "EJA ANOS INICIAIS") !== false) {
      $iTotalAulas = 170;
    }
    else if (strpos($sCalendario, "EJA FINAIS") !== false ||
             strpos($sCalendario, "EJA ANOS FINAIS") !== false) {
      // Verifica o turno
      $sSqlTurno = "
        select substring(ed15_c_nome,1,5) as turno
        from calendario
        inner join turma on ed57_i_calendario = ed52_i_codigo
        inner join turno on ed15_i_codigo = ed57_i_turno
        where ed52_c_descr like '%{$sCalendario}%'";

      $rsTurno = db_query($sSqlTurno);
      $oTurno = db_utils::fieldsMemory($rsTurno, 0);

      if ($oTurno->turno == 'NOITE') {
        $iTotalAulas = 1000;
      } else {
        $iTotalAulas = 1200;
      }
    }

    return $iTotalAulas;
  }

  public function imprimeResultadoFinal() {

    if ( $this->oGradeAproveitamento->getMatricula()->isConcluida() ) {
      $this->imprimirAndamentoDaMatricula();
    }
  }

  /**
   * Retorna um array com os elementos apresentados
   * @return IElementoAvaliacao[] array com os elementos apresentados
   */
  public function getElementosApresentados() {

    return $this->aElementosApresentados;
  }

  /**
   * Retorna a convenção que o aluno foi amparado, se ouve
   * @return array  array com a descrição das convenções de amparo lançadas para o aluno
   */
  public function getAmparosPorConvencao() {

    $aConvencoes  = array();
    $oDiario      = $this->oGradeAproveitamento->getMatricula()->getDiarioDeClasse();
    $aDisciplinas = $oDiario->getDisciplinas();
    foreach ($aDisciplinas as $oDisciplina) {

      $oAmparo = $oDisciplina->getAmparo();

      if ( $oAmparo->getTipoAmparo() == AmparoDisciplina::AMPARO_CONVENCAO) {

        $oConvencao                            = $oAmparo->getConvencao();
        $aConvencoes[$oConvencao->getCodigo()] = "{$oConvencao->getAbreviatura()} - {$oConvencao->getDescricao()}";
      }
    }

    return $aConvencoes;
  }

  public function getGradeAproveitamento() {
    return $this->oGradeAproveitamento;
  }


  public function imprimeObservacoes($sObservacaoManual, $lMatricula = false, $lDiario = false, $lReclassificacao = false, $lProporcionalidade = false,
                                     $lAprovadoPeloConselho = false, $lAprovadoConformeRegimento = false ) {

    if ( !empty($sObservacaoManual) ) {
      $this->aObservacoes[] = $sObservacaoManual;
    }
    if ( $lMatricula ) {
      $this->adicionarObservacaoMatricula();
    }
    if ( $lDiario ) {
      $this->adicionarObservacaoDoDiario();
    }

    if ( $lAprovadoPeloConselho || $lAprovadoConformeRegimento ) {
      $this->adicionarObservacaoAprovacaoConselho( $lAprovadoPeloConselho, $lAprovadoConformeRegimento );
    }

    if ( $lReclassificacao ) {
      $this->adicionarObservacaoReclassificadoPorBaixaFrequencia();
    }
    if ( $lProporcionalidade ) {
      $this->adicionarObservacaoProporcionalidade();
    }

    if( count( $this->aObservacoes ) > 0 ) {

      $sObservacao = implode( "\n- ", $this->aObservacoes );
      $this->oPdf->Cell( $this->iLimiteLinha, 4, "Observações / Mensagens", 1, 1, 'L' );

      $this->oPdf->SetFont( 'arial', '', 6 );
      $this->oPdf->Multicell( $this->iLimiteLinha, 4, $sObservacao, 1, 'L' );
    }
  }

  private function adicionarObservacaoMatricula() {

    $oMatricula = $this->oGradeAproveitamento->getMatricula();
    if( trim( $oMatricula->getObservacao() ) != '' ) {
      $this->aObservacoes[] = $oMatricula->getObservacao();
    }
  }

  private function adicionarObservacaoDoDiario() {

    $oDiario = $this->oGradeAproveitamento->getMatricula()->getDiarioDeClasse();
    foreach ( $oDiario->getDisciplinas() as $oDiarioAvaliacaoDisciplina ) {

      foreach( $oDiarioAvaliacaoDisciplina->getAvaliacoes() as $oAvaliacaoAproveitamento ) {

        if( trim($oAvaliacaoAproveitamento->getObservacao()) != '' ) {
          $this->aObservacoes[] = $oAvaliacaoAproveitamento->getObservacao();
        }
      }
    }
  }

  /**
   * Monta as observações que devem ser impressas referente a aprovação pelo conselho e aprovação conforme regimento
   * escolar confome parâmetro informado.
   * @param boolean $lAprovadoPeloConselho      Valida se deve ser apresentado a observação da Aprovação pelo Conselho
   * @param boolean $lAprovadoConformeRegimento Valida se deve ser apresentado a observação da Aprovação Conforme Regimento
   */
  private function adicionarObservacaoAprovacaoConselho( $lAprovadoPeloConselho, $lAprovadoConformeRegimento ) {

    $oDiario = $this->oGradeAproveitamento->getMatricula()->getDiarioDeClasse();

    foreach ( $oDiario->getDisciplinas() as $oDiarioDisciplina ) {

      $oFormaAprovacaoConselho = $oDiarioDisciplina->getResultadoFinal()->getFormaAprovacaoConselho();

      if ( empty($oFormaAprovacaoConselho) ) {
        continue;
      }

      $oRegencia = $oDiarioDisciplina->getRegencia();

      if ( $lAprovadoPeloConselho &&
           $oFormaAprovacaoConselho->getFormaAprovacao() == AprovacaoConselho::APROVADO_CONSELHO ) {

        $oDocumento                = new libdocumento( 5013 );
        $oDocumento->disciplina    = $oRegencia->getDisciplina()->getNomeDisciplina();
        $oDocumento->etapa         = $oRegencia->getEtapa()->getNome();
        $oDocumento->justificativa = $oFormaAprovacaoConselho->getJustificativa();
        $oDocumento->nota          = $oFormaAprovacaoConselho->getAvaliacaoConselho();
        $oDocumento->anomatricula  = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();
        $aParagrafos               = $oDocumento->getDocParagrafos();

        if ( isset( $aParagrafos[1] ) ) {
          $this->aObservacoes[] = "- {$aParagrafos[1]->oParag->db02_texto}";
        }
      }

      if ( $lAprovadoConformeRegimento &&
           $oFormaAprovacaoConselho->getFormaAprovacao() == AprovacaoConselho::APROVADO_CONFORME_REGIMENTO_ESCOLAR ) {

        $sObservacao           = "Disciplina {$oRegencia->getDisciplina()->getNomeDisciplina()}: ";
        $sObservacao          .= "Aprovado conforme regimento escolar. Justificativa: ";
        $sObservacao          .= $oFormaAprovacaoConselho->getJustificativa();
        $this->aObservacoes[]  = $sObservacao;
      }
    }
  }

  private function adicionarObservacaoReclassificadoPorBaixaFrequencia() {

    $oMatricula = $this->oGradeAproveitamento->getMatricula();
    $oDiario    = $oMatricula->getDiarioDeClasse();
    if ( $oDiario->reclassificadoPorBaixaFrequencia() ) {

      $oDocumento = new libdocumento( 5006 );

      $oDocumento->nome_aluno = $oMatricula->getAluno()->getNome();
      $oDocumento->ano        = $oMatricula->getTurma()->getCalendario()->getAnoExecucao();
      $oDocumento->nome_etapa = $oMatricula->getEtapaDeOrigem()->getNome();
      $aParagrafos            = $oDocumento->getDocParagrafos();

      if ( isset( $aParagrafos[1] ) ) {
        $this->aObservacoes[] = $aParagrafos[1]->oParag->db02_texto;
      }
    }
  }

  private function adicionarObservacaoProporcionalidade() {

    $oMatricula = $this->oGradeAproveitamento->getMatricula();
    $oTurma     = $oMatricula->getTurma();

    $lPermiteProporcionalidade = false;
    foreach ($oTurma->getDisciplinas() as $oRegencia) {

      foreach ($oRegencia->getProcedimentoAvaliacao()->getElementos() as $oElemento) {

        if ($oElemento instanceof ResultadoAvaliacao && $oElemento->utilizaProporcionalidade()) {
          $lPermiteProporcionalidade = true;
        }
      }
    }

    if ( $lPermiteProporcionalidade ) {

      $iTipoEnsino = $oMatricula->getEtapaDeOrigem()->getEnsino()->getCodigoTipoEnsino();

      if ($iTipoEnsino == 1) {

        $oDocumento           = new libdocumento( 5017 );
        $aParagrafos          = $oDocumento->getDocParagrafos();
        $this->aObservacoes[] = $aParagrafos[1]->oParag->db02_texto;

      } elseif ($iTipoEnsino == 3) {

        $oDocumento           = new libdocumento( 5018 );
        $aParagrafos          = $oDocumento->getDocParagrafos();
        $this->aObservacoes[] = $aParagrafos[1]->oParag->db02_texto;
      }
    }
  }
//*****************************************************************************************************************************************
private function buscafalta(){

    $totaltotal = 0;
	foreach ($this->oGradeAproveitamento->GradeAproveitamentoAluno as $oDisciplina2) {
		foreach ($oDisciplina2->aAproveitamento as $oAvaliacao2) {
		   $totaltotal += $oAvaliacao2->oAproveitamento->iFaltas;
		}
	}
	return $totaltotal;
}

private function percfrequencia($calendar){
    $sqla ="select
		    ed52_c_descr,
		    ed15_c_nome
		    from
		    calendario
		    inner join turma on ed57_i_calendario = ed52_i_codigo
		    inner join turno on ed15_i_codigo     = ed57_i_turno
		    where ed52_i_codigo = ".$calendar;

	$sql   = pg_query($sqla);
    $resultado = pg_fetch_all($sql);
	$nome  = $resultado[0]["ed52_c_descr"];
	$turno = substr($resultado[0]["ed15_c_nome"],0,5);

	if( substr($nome,0,12) == 'ED. INFANTIL' or substr($nome,0,17) == 'EDUCAÇÃO INFANTIL')
	{
		$auladadas = 200;
	}
	elseif( substr($nome,0,13) == 'ANOS INICIAIS' or substr($nome,0,20) == 'EN FUN ANOS INICIAIS' )
	{
		$auladadas = 200;

	}
	elseif( substr($nome,0,11) == 'ANOS FINAIS' or substr($nome,0,18) == 'EN FUN ANOS FINAIS')
	{
		$auladadas = 1000;
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

private function faltasPeriodo($aluno,$periodoaval, $escola, $calendario )
{

	// não esquecer de enviar edu2_boletim003.php
	$sql2 = " select ed60_i_aluno from matricula where ed60_i_codigo = {$aluno}";
	$sqlA = pg_query($sql2);
	$oAluno = db_utils::fieldsMemory($sqlA, 0);

	$sqlP ="select
	        distinct on (ed09_i_codigo)
			ed09_i_codigo
			from
			diarioavaliacao
			inner join procavaliacao    on ed41_i_codigo = ed72_i_procavaliacao
			inner join periodoavaliacao on ed09_i_codigo = ed41_i_periodoavaliacao
			where
			ed41_i_codigo = {$periodoaval}";
	$res = pg_query($sqlP);
	$oPeriodo = db_utils::fieldsMemory($res, 0);

	$sql1=  "
			select
			sum(ed72_i_numfaltas) as numero_faltas
			from
			diarioavaliacao
			inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao
			inner join periodoavaliacao on ed09_i_codigo = ed41_i_periodoavaliacao
			left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo
			left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo
			where
			ed09_i_codigo <= {$oPeriodo->ed09_i_codigo}
			and
			ed72_i_diario IN (SELECT
							  ed95_i_codigo
							  FROM
							  diario
							  WHERE
							  ed95_i_aluno = {$oAluno->ed60_i_aluno}
							  AND
							  ed95_i_escola = {$escola}
							  AND
							  ed95_i_calendario = {$calendario})";

	$sql = pg_query($sql1);
    $resultado   = pg_fetch_all($sql);
	$quantFalta  = $resultado[0]["numero_faltas"];
	return $quantFalta;
}

/**
 * Autor: Uemerson Santana
 * Demanda: 17328
 * Data: 2025-04-15
 */
private function imprimeDependencia($aluno, $ano)  // imprime as dependencias
{
    //Demanda 17897
		//Alterada por Rafael Matos - 06/10/2025
    $sqlanoletivo = pg_query("SELECT ed52_i_ano FROM calendario WHERE ed52_i_codigo = ". $_GET["calendario"]);
		$resultado = pg_fetch_all($sqlanoletivo);
		$anoetapa = $resultado[0]["ed52_i_ano"];

		$sql1= "
			select
			distinct on (ed999_sequencial)
			ed232_i_codigo,
			ed232_c_descr as disciplina,
			ed114_ano     as ano,
			ed09_c_descr  as bimestre,
			ed09_c_descr  as media,
			ed999_nota    as nota,
			ed998_nota    as notareal,
			ed999_faltas  as faltas,
			ed150_ano
			from
			diarioprogressaoavaliacao
			inner join diarioprogressao                        on ed993_sequencial       = ed999_diarioprogressao
			inner join progressaoparcialalunoturmaregencia     on ed115_sequencial       = ed993_progressaoparcialalunoturmaregencia
			inner join progressaoparcialalunomatricula         on ed150_sequencial       = ed115_progressaoparcialalunomatricula
			inner join progressaoparcialaluno                  on ed114_sequencial       = ed150_progressaoparcialaluno
			inner join disciplina                              on ed12_i_codigo          = ed114_disciplina
			inner join caddisciplina                           on ed232_i_codigo         = ed12_i_caddisciplina
			inner join procavaliacao                           on ed41_i_codigo          = ed999_procavaliacao
			inner join periodoavaliacao                        on ed09_i_codigo          = ed41_i_periodoavaliacao
			inner join diarioprogressaoresultado               on ed998_diarioprogressao = ed993_sequencial
			inner join procresultado                           on ed43_i_codigo          = ed998_procresultado
			inner join matricula                               on ed60_i_aluno           = ed114_aluno
			where
			ed60_i_codigo = ".$aluno." and ed150_ano = ".$anoetapa."
			order by ed999_sequencial";

			/**
     * Autor: Uemerson Santana
     * Demanda: 17328
     * Data: 2025-04-15
     */
/*
    $sql1= "
			select
			distinct on (ed999_sequencial)
			ed232_i_codigo,
			ed232_c_descr as disciplina,
			ed114_ano     as ano,
			ed09_c_descr  as bimestre,
			ed09_c_descr  as media,
			ed999_nota    as nota,
			ed998_nota    as notareal,
			ed999_faltas  as faltas
			from
			diarioprogressaoavaliacao
			inner join diarioprogressao                        on ed993_sequencial       = ed999_diarioprogressao
			inner join progressaoparcialalunoturmaregencia     on ed115_sequencial       = ed993_progressaoparcialalunoturmaregencia
			inner join progressaoparcialalunomatricula         on ed150_sequencial       = ed115_progressaoparcialalunomatricula
			inner join progressaoparcialaluno                  on ed114_sequencial       = ed150_progressaoparcialaluno
			inner join disciplina                              on ed12_i_codigo          = ed114_disciplina
			inner join caddisciplina                           on ed232_i_codigo         = ed12_i_caddisciplina
			inner join procavaliacao                           on ed41_i_codigo          = ed999_procavaliacao
			inner join periodoavaliacao                        on ed09_i_codigo          = ed41_i_periodoavaliacao
			inner join diarioprogressaoresultado               on ed998_diarioprogressao = ed993_sequencial
			inner join procresultado                           on ed43_i_codigo          = ed998_procresultado
			inner join matricula                               on ed60_i_aluno           = ed114_aluno
			where
			ed60_i_codigo = ".$aluno." 
            and ed114_ano >= (".$ano." - 1)
			order by ed999_sequencial";
*/

			
			
			

    $sql = pg_query($sql1);
    if( pg_num_rows($sql) > 0){
      $this->oPdf->SetFont("Arial", "B", 6);
      $this->oPdf->Cell(189,4,'', 'LR', 1, "");
			$this->oPdf->Cell(189,4,'', 'LR', 1, "");
			$this->oPdf->Cell(189,4,'DEPENDÊNCIAS', 1, 1, "C");			
			$this->oPdf->Cell(31,4,'', 'LR', 0);
			$this->oPdf->Cell(10,4,'', 'LR', 0);
			$this->oPdf->Cell(10,4,'', 'LR', 0);
			//Demanda 17897
			//Alterada por Rafael Matos - 06/10/2025
			if($anoetapa < 2025){
				$this->oPdf->Cell(10,4,'', 'LR', 0);
			}
			$this->oPdf->Cell(10,4,'', 'LR', 0);
			$this->oPdf->Cell(10,4,'', 'LR', 0);
			$this->oPdf->Cell(10,4,'', 'LR', 0);
			$this->oPdf->Cell(10,4,'', 'LR', 0);
			$this->oPdf->Cell(10,4,'', 'LR', 0);
			$this->oPdf->Cell(10,4,'MÉDIA', 'LR', 0, "C");
			$this->oPdf->Cell(40,4,'FALTAS', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'TOTAL', 'LR', 0, "C");
			//$this->oPdf->Cell(10,4,'FREQ.', 'LR', 0, "C");
			//$this->oPdf->Cell(10,4,'', '', 0, "C");
			$this->oPdf->Cell(10,4,'RES.', 'LR', 1, "C");

			$this->oPdf->Cell(31,4,'DISCIPLINA(S)', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'ANO(S)', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'1º', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'2º', 'LRB', 0, "C");
			//Demanda 17897
			//Alterada por Rafael Matos - 06/10/2025
			if($anoetapa < 2025){
				$this->oPdf->Cell(10,4,'REC', 'LRB', 0, "C");
			}
			$this->oPdf->Cell(10,4,'3º', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'4º', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'MÉDIA', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'REC', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'FINAL', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'1º', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'2º', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'3º', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'4º', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'FALTAS', 'LRB', "LR", "C");
			//$this->oPdf->Cell(10,4,'%', 'LRB', 0, "C");
			//$this->oPdf->Cell(10,4,'', 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,'FINAL', 'LRB', 1, "C");
			$this->oPdf->SetFont("Arial", "", 6);

		for($x=0;$x<pg_num_rows($sql);$x++){
			$oDados     = db_utils::fieldsMemory($sql, $x);
			$disciplina = $oDados->disciplina;
			$coddisciplina = $oDados->ed232_i_codigo;
			$ano        = $oDados->ano;
			$nota1       = null;
			$nota2       = null;
			$nota3       = null;
			$nota4       = null;
			$nota5       = null;
			$nota6       = null;

			$faltas1     = 0;
			$faltas2     = 0;
			$faltas3     = 0;
			$faltas4     = 0;

			$this->oPdf->Cell(31,4,$disciplina, 'LRB', 0, "L");
			$this->oPdf->Cell(10,4,$ano, 'LRB', 0, "C");
      if ( $disciplina == 'ENSINO RELIGIOSO' ) {
      	$this->oPdf->Cell(10,4,'PD', 'LRB', 0, "C"); // 4º bimestre
      } else {
      	$this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 4º bimestre
      }
      //$this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 1º bimestre
      $nota1 = $oDados->nota;
			$faltas1 = $oDados->faltas;

			$x++;
			
			$oDados = db_utils::fieldsMemory($sql, $x);

            if ( $disciplina == 'ENSINO RELIGIOSO' ) {
                $this->oPdf->Cell(10,4,'PD', 'LRB', 0, "C"); // 4º bimestre
            } else {
                $this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 4º bimestre
            }
            //$this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 2º bimestre
			$nota2 = $oDados->nota;
			$faltas2 = $oDados->faltas;


			$x++;
			
			$oDados = db_utils::fieldsMemory($sql, $x);   // recuperacao semestral			
            if ( $disciplina == 'ENSINO RELIGIOSO' ) {
                $this->oPdf->Cell(10,4,'PD', 'LRB', 0, "C"); // 4º bimestre
            } else {
                $this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 4º bimestre
            }
            //$this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C");
       //}
            $nota5 = $oDados->nota; // nota rec sem
			$x++;
			
			$oDados = db_utils::fieldsMemory($sql, $x);
            if ( $disciplina == 'ENSINO RELIGIOSO' ) {
                $this->oPdf->Cell(10,4,'PD', 'LRB', 0, "C"); // 4º bimestre
            } else {
                $this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 4º bimestre
            }
            //$this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 3º bimestre
			$nota3 = $oDados->nota;
			$faltas3 = $oDados->faltas;

			$x++;
			
			$oDados = db_utils::fieldsMemory($sql, $x);
						if($anoetapa < 2025){
            	if ( $disciplina == 'ENSINO RELIGIOSO' ) {
                $this->oPdf->Cell(10,4,'PD', 'LRB', 0, "C"); // 4º bimestre
            	} else {
                $this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 4º bimestre
            	}
            }
            //$this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C"); // 4º bimestre

			$nota4 = $oDados->nota;
			$faltas4 = $oDados->faltas;

      $divisao = 0;
			if( $nota1 <> null ){$divisao++;}
			if( $nota2 <> null ){$divisao++;}
			if( $nota3 <> null ){$divisao++;}
			if( $nota4 <> null ){$divisao++;}
			if( $nota1 < $nota2 and  $nota1 < $nota5){
				$nota1 = $nota5;
			}
			if( $nota2 < $nota1 and  $nota2 < $nota5){
				$nota2 = $nota5;
			}
			//MA
      $media = ($nota1+$nota2+$nota3+$nota4)/$divisao;      

			$nm = explode(".", $media);
			$mediaA = $nm[0] . "," . substr($nm[1], 0, 1);

			if($anoetapa > 2024){
				if( $nota3 <> null ){ // foi lançado todas as notas			
			  	$this->oPdf->Cell(10,4,$mediaA, 'LRB' , 0, "C"); // média
				}else{
					$this->oPdf->Cell(10,4,'', 'LRB' , 0, "C"); // média
				}	
			}else{
				if( $nota4 <> null ){ // foi lançado todas as notas			
			  	$this->oPdf->Cell(10,4,$mediaA, 'LRB' , 0, "C"); // média
				}else{
					$this->oPdf->Cell(10,4,'', 'LRB' , 0, "C"); // média
				}	
			}

			

			$x++;
			$oDados = db_utils::fieldsMemory($sql, $x);   // recuperacao final
			$this->oPdf->Cell(10,4,number_format($oDados->nota, 1, ',', '.'), 'LRB', 0, "C");
			$nota6 = $oDados->nota; // nota rec sem
				//M F
      if( $nota6 <> null and $nota6 <> '' ){ // o aluno fez a recuperação final e teve nota maior que a media anual			
				if( $nota6 > $media ){ // mas ela só ira entrar na media final se for maior que a media final				
				    $mediaf  = ($media+$nota6)/2;        // se for maior, a media anual devera ser somada a recuperação final e ai fazer a media final dividindo por 2
					$mediaAR = $mediaf;
				}else{
					$mediaf  = $media;  // se for menor que o 3º ou menor que o 4º, é descartada e a media anual prevalece como media final
					$mediaAR = $media;
				}
			}else{
			    $mediaf = $media; // o aluno não fez a recuperação final
				$mediaAR = $media;
			}

			if($anoetapa > 2024){
				if( $nota3 <> null ){
					$nmf = explode(".", $mediaf);
					$mediaf = $nmf[0] . "," . substr($nmf[1], 0, 1);
					$this->oPdf->Cell(10,4,$mediaf, 'LRB', 0, "C"); // media final (ver esse calculo)
		    }else{
					$this->oPdf->Cell(10,4,'', 'LRB', 0, "C"); // media final (ver esse calculo)
				}
			}else{
				if( $nota4 <> null ){
					$nmf = explode(".", $mediaf);
					$mediaf = $nmf[0] . "," . substr($nmf[1], 0, 1);
					$this->oPdf->Cell(10,4,$mediaf, 'LRB', 0, "C"); // media final (ver esse calculo)
		    }else{
					$this->oPdf->Cell(10,4,'', 'LRB', 0, "C"); // media final (ver esse calculo)
				}	
			}
			
			/*
			var_dump($nota1);
			var_dump($nota2);
			var_dump($nota3);
			var_dump($nota4);
			var_dump($nota5);
			die("Confere");
			*/

			$this->oPdf->Cell(10,4,$faltas1, 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,$faltas2, 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,$faltas3, 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,$faltas4, 'LRB', 0, "C");
			$this->oPdf->Cell(10,4,($faltas1+$faltas2+$faltas3+$faltas4), 'LRB', 0, "C");

			$diasletivos2 = $this->percfrequencia(@$GLOBALS["HTTP_POST_VARS"]["calendarioF"]);
			$sPercentualFrequencia = floor(($diasletivos2 - ($faltas1+$faltas2+$faltas3+$faltas4)) / $diasletivos2 * 100);  // ver os dias letivos de recuperacao
			//$this->oPdf->Cell(10,4,$sPercentualFrequencia, 'LRB', 0, "C");
      //$this->oPdf->Cell(10,4,'', 'LRB', 0, "C");
      $sqlperiodocabeca = pg_query("SELECT ed41_i_periodoavaliacao, ed09_c_descr FROM procavaliacao INNER JOIN periodoavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo WHERE ed41_i_codigo = ". $_GET["periodo"]);
      $resultado = pg_fetch_all($sqlperiodocabeca);
      $periodocabeca = $resultado[0]["ed41_i_periodoavaliacao"];
			
			//$evadido2 = $this->evadiu($aluno,$coddisciplina);
			
			if($periodocabeca == 4 || $periodocabeca == 8 || $periodocabeca == 10){
				if( $mediaAR >= 5){
		  		$evadido2 = $this->evadiu($aluno,$coddisciplina);
					if( $evadido2 ){
						$this->oPdf->Cell(10,4,'EVA', 'LRB', 1, "C");
					}else{
						if( $nota4 > 0){
							 $this->oPdf->Cell(10,4,'APR', 'LRB', 1, "C");
						}else{
							 //$this->oPdf->Cell(10,4,'', 'LRB', 1, "C");
						}
					}
				}else{
					$evadido2 = $this->evadiu($aluno,$coddisciplina);
					if( $evadido2 ){
						$this->oPdf->Cell(10,4,'EVA', 'LRB', 1, "C");
					}else{
						$this->oPdf->Cell(10,4,'REP', 'LRB', 1, "C");
					}
				}
			}else{
				$this->oPdf->Cell(10,4,'', 'LRB', 1, "C");
			}

			/*
			if( $mediaAR >= 5){
		  	$evadido2 = $this->evadiu($aluno,$coddisciplina);
				if( $evadido2 ){
					$this->oPdf->Cell(10,4,'EVA', 'LRB', 1, "C");
				}else{
					if( $nota4 > 0){
						 $this->oPdf->Cell(10,4,'APR', 'LRB', 1, "C");
					}else{
						 //$this->oPdf->Cell(10,4,'', 'LRB', 1, "C");
					}
				}
			}else{
				$evadido2 = $this->evadiu($aluno,$coddisciplina);
				if( $evadido2 ){
						$this->oPdf->Cell(10,4,'EVA', 'LRB', 1, "C");
				}else{
					$this->oPdf->Cell(10,4,'REP', 'LRB', 1, "C");
				}
			}
			*/

			$this->oPdf->SetFont("Arial", "", 6);
		}
	}
}


function notasPorDisciplina($matricula,$disciplina,$turma,$periodoprova){

  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];
// voltei a disciplina para o nome para fazer anos iniciais
//$disciplina = utf8_decode($disciplina);
$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/busca.txt","w+");
fwrite($arq, $this->calendarioEja);
fwrite($arq,"\r\n");
fclose($arq);


	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  '".$disciplina."'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";
    $result_D = db_query($sqlD);
    $odados = db_utils::fieldsmemory($result_D,0);


  $sqla = " select
			ed95_i_codigo,
			ed47_v_nome
			from
			diario
			inner join aluno on ed47_i_codigo = ed95_i_aluno
			inner join matricula on ed60_i_aluno = ed47_i_codigo
			inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
			inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
			where
			ed60_i_codigo = {$matricula}
			and
			ed95_i_regencia = ed59_i_codigo
			and
			ed59_i_disciplina = {$odados->ed12_i_codigo}";
  if( $serie <> 29 and $serie <> 30 and $serie <> 31 )
  {
       if( $this->calendarioEja <> 'EJA FINAIS' )
	   {
		   $sqla .= "
					and
					ed95_i_serie = {$serie}";
	   }
  }
  $sqla .= "
  		    and
			ed59_i_turma = {$turma}
			order by ed95_i_codigo";
  $sql2 = pg_query($sqla);
  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/busca.sql","w+");
//fwrite($arq, 'estou aqui');
//fwrite($arq,"\r\n");
//fclose($arq);

  $sqlN ="
		select
		distinct on (ed72_i_procavaliacao)
		ed95_i_codigo as diario,
		ed232_c_descr as disciplina,
		ed47_v_nome,
		ed09_c_descr  as bimestre,
		ed72_i_valornota,
		ed72_c_valorconceito,
		ed72_i_numfaltas
		from
		diarioavaliacao
		inner join diario           on ed95_i_codigo          = ed72_i_diario
		inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
		left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
		left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
		inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
		inner join aluno            on ed47_i_codigo          = ed95_i_aluno
		inner join matricula        on ed60_i_aluno           = ed47_i_codigo
		inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
		inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
		inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
		inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
		where
		ed72_i_diario = {$codigo}
        ORDER BY ed72_i_procavaliacao";

//if( trim($r2[0]["ed47_v_nome"]) == 'Isabela de Souza Fortes Estevão')
//{
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/busca2.sql","a+");
//fwrite($arq, $sqlN );
//fwrite($arq,"\r\n");
//fclose($arq);
//}
    $sql3 = pg_query($sqlN);
    $resultado = pg_fetch_all($sql3);

	//verifica nas materias abaixo
    if( trim($disciplina) == 'LINGUA PORTUGUESA' or trim($disciplina) == 'MATEMÁTICA' or trim($disciplina) == 'HISTÓRIA' or trim($disciplina) == 'GEOGRAFIA' or
        trim($disciplina) == 'CIENCIAS' )
    {

	    if(trim($resultado[2]['ed72_c_valorconceito']) == 'D') // se o ultimo conceito do aluno foi 'D'
	    {
            if( $this->reprovou )
		    {
		        $this->repconceito = true; // se foi o aluno será reprovado
				$this->reprovou    = false;
		    }
	    }
    }

    $this->recFinal =  $resultado[5]['ed72_i_valornota'];
    return $resultado;

}

function notasPorDisciplinaEja($matricula,$disciplina,$turma,$periodoprova){

  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];
// voltei a disciplina para o nome para fazer anos iniciais
//$disciplina = utf8_decode($disciplina);


	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  '".$disciplina."'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";
    $result_D = db_query($sqlD);
    $odados = db_utils::fieldsmemory($result_D,0);


  $sqla = " select
			ed95_i_codigo,
			ed47_v_nome
			from
			diario
			inner join aluno on ed47_i_codigo = ed95_i_aluno
			inner join matricula on ed60_i_aluno = ed47_i_codigo
			inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
			inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
			where
			ed60_i_codigo = {$matricula}
			and
			ed95_i_regencia = ed59_i_codigo
			and
			ed59_i_disciplina = {$odados->ed12_i_codigo}";
//  if( $serie <> 29 and $serie <> 30 and $serie <> 31 )
//  {
//       $sqla .= "
//			    and
//			    ed95_i_serie = {$serie}";
//  }
  $sqla .= "
  		    and
			ed59_i_turma = {$turma}
			order by ed95_i_codigo";
  $sql2 = pg_query($sqla);
  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];

  $sqlN ="
		select
		distinct on (ed72_i_procavaliacao)
		ed95_i_codigo as diario,
		ed232_c_descr as disciplina,
		ed47_v_nome,
		ed09_c_descr  as bimestre,
		ed72_i_valornota,
		ed72_c_valorconceito,
		ed72_i_numfaltas
		from
		diarioavaliacao
		inner join diario           on ed95_i_codigo          = ed72_i_diario
		inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
		left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
		left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
		inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
		inner join aluno            on ed47_i_codigo          = ed95_i_aluno
		inner join matricula        on ed60_i_aluno           = ed47_i_codigo
		inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
		inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
		inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
		inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
		where
		ed72_i_diario = {$codigo}
        ORDER BY ed72_i_procavaliacao";

    $sql3 = pg_query($sqlN);
    $resultado = pg_fetch_all($sql3);
    $this->recFinal =  $resultado[5]['ed72_i_valornota'];
    return $resultado;

}

function mediaC( $matricula,$disciplina,$turma,$periodo,$periodoprova)
{
//**********************************************************************************************************************
	$sql    = "select ed09_i_codigo from periodoavaliacao where ed09_c_descr = '".$periodoprova."'";

	$result = pg_query($sql);
	$period = db_utils::fieldsmemory($result,0);
    $result = $this->quantDividir($matricula,$disciplina,$turma,$period->ed09_i_codigo); // busca os dados dos anos iniciais

	$quant = 0;
	$media = 0;
	if( $result[0]["ed72_i_valornota"]<>null ) // vê se o primeiro bimestre teve nota
	{
		$quant++;
	}

	if( $result[1]["ed72_i_valornota"]<>null ) // vê se o segundo bimestre teve nota
	{
		$quant++;
	}
	if( $result[0]["ed72_i_valornota"]==null and $result[1]["ed72_i_valornota"]==null ) // quando os 2 bimestres estiverem nulos, mas tem nota na recuperaçao
	{
		if( $result[2]["ed72_i_valornota"]<>null )
		{
		   $quant++;
		}
	}

	if( $result[3]["ed72_i_valornota"]<>null )
	{
		$quant++;
	}
	if( $result[4]["ed72_i_valornota"]<>null )
	{
		$quant++;
	}

//**********************************************************************************************************************

	  $resultado = $this->notasPorDisciplina($matricula,$disciplina,$turma,$period->ed09_i_codigo);

	  $media = 0;

	  if( $periodo == 1) //se foi escolhido o primeiro bimestre
	  {
		  $media = $resultado[0]["ed72_i_valornota"];
	  }

	  if( $periodo == 2) //se foi escolhido o segundo bimestre
	  {
		  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"])/$quant;
	  }
	  if( $periodo == 3) //se foi escolhido o terceiro bimestre
	  {
		  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant;
	  }
	  // todos os bimestre foram feitos mas não foi feito a recuperação
	  if( $periodo == 4) //se foi escolhido o segundo bimestre
	  {
		  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"]+$resultado[4]["ed72_i_valornota"])/$quant;
	  }


	  // foi feito recuperação
	  if( $periodo == 2) // quando o usuario escolher na tela principal imprimir os dados do segundo bimestre
	  {
		  if( $resultado[2]["ed72_i_valornota"]<>null) // se o aluno fez a recuperação
		  {
			  if( $resultado[0]["ed72_i_valornota"] <> null ) // aconteceu um caso do primeiro bimestre ser nulo e o segundo menor que a recuperação
			  {
				  if( ($resultado[0]["ed72_i_valornota"] > $resultado[1]["ed72_i_valornota"])  ) // se o 1º bimestre for maior que o 2º
				  {
					  if( $resultado[1]["ed72_i_valornota"] < $resultado[2]["ed72_i_valornota"] ) // se o 2º bimestre for menor que a recuperação
					  {
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[2]["ed72_i_valornota"])/$quant; // descarta o 2º bimestre e usa o  (1º+recup)/2
					  }else{
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"])/$quant; // se a recuperação for menor que o 2º bimestre, descarta a recuperação
					  }
				  }else{ // o primeiro bimestre é menor que o segundo
					  if( $resultado[0]["ed72_i_valornota"] < $resultado[2]["ed72_i_valornota"] ) // se o 1º bimestre for menor que a recuperação e menor que o segundo
					  {
						  $media = ($resultado[2]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"])/$quant;
					  }else{ // o primeiro bimestre é menor que o segundo, mas é maior que recuperação, descarta a recuperação
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"])/$quant;
					  }
				  }
				  if( $resultado[1]["ed72_i_valornota"] == null ) // o primeiro bimestre tem nota, mas o segundo é nulo
				  {
					  if( $resultado[2]["ed72_i_valornota"] > $resultado[0]["ed72_i_valornota"]) // o primeiro tem nota, mas é maior que a recuperação???
					  {
						  $media = $resultado[2]["ed72_i_valornota"]; // se não a media sera o valor da recuperação
					  }
				  }
			  }else{
				  if( $resultado[1]["ed72_i_valornota"] <> null ) // o primeiro bimestre é nulo mas o segundo não
				  {
					  if( $resultado[2]["ed72_i_valornota"] > $resultado[1]["ed72_i_valornota"]) // a nota da recuperação é maior que a nota do segundo bimestre
					  {
						  $media = $resultado[2]["ed72_i_valornota"]; //então a nota da media será a nota da recuperação
					  }
				  }
			  }
		  }
	  }


	  if( $periodo == 3) // quando o usuario escolher na tela principal imprimir só os dados terceiro bimestre
	  {
		  if( $resultado[2]["ed72_i_valornota"]<>null)
		  {
			  if( $resultado[0]["ed72_i_valornota"] <> null) // aconteceu um caso do primeiro bimestre ser nulo e o segundo menor que a recuperação
			  {
				  if( ($resultado[0]["ed72_i_valornota"] > $resultado[1]["ed72_i_valornota"])  ) // se o 1º bimestre for maior que o 2º
				  {
					  if( $resultado[1]["ed72_i_valornota"] < $resultado[2]["ed72_i_valornota"] ) // se o 2º bimestre for menor que a recuperação
					  {
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[2]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant; // descarta o 2º bimestre e usa o  (1º+3º+recup)/3
					  }else{
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant; // se a recuperação for menor que o
					  }
				  }else{ // o primeiro bimestre é menor que o segundo
					  if( $resultado[0]["ed72_i_valornota"] < $resultado[2]["ed72_i_valornota"] ) // se o 1º bimestre for menor que a recuperação e menor que o segundo
					  {
						  $media = ($resultado[2]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant;
					  }else{ // o primeiro bimestre é menor que o segundo, mas é maior que recuperação, descarta a recuperação
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant;
					  }
				  }
				  if( $resultado[1]["ed72_i_valornota"] == null )// o primeiro bimestre não é nulo, mas o segundo é
				  {
					  if( $resultado[2]["ed72_i_valornota"] > $resultado[0]["ed72_i_valornota"]) //a recuperação é maior que o primeiro bimestre
					  {
						  $media = ($resultado[2]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant; //se for considera a recuperação para o calculo
					  }
				  }
			  }else{ // o primeiro bimestre é nulo
				  if( $resultado[1]["ed72_i_valornota"] <> null ) // mas o segundo não é
				  {
					  if( $resultado[2]["ed72_i_valornota"] > $resultado[1]["ed72_i_valornota"]) // a recuperação é maior que o segundo bimestre???
					  {
						  $media = ($resultado[2]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant; // se for a media será recuperação+terceiro bimestre
					  }
				  }
			  }
		  }
	  }

	  if( $periodo == 4) // quando o usuario escolher na tela principal imprimir os dados quarto bimestre
	  {
		  if( $resultado[2]["ed72_i_valornota"]<>null)
		  {
			  if( $resultado[0]["ed72_i_valornota"] <> null ) // aconteceu um caso do primeiro bimestre ser nulo e o segundo menor que a recuperação
			  {
				  if( ($resultado[0]["ed72_i_valornota"] > $resultado[1]["ed72_i_valornota"])  ) // se o 1º bimestre for maior que o 2º
				  {
					  if( $resultado[1]["ed72_i_valornota"] < $resultado[2]["ed72_i_valornota"] ) // se o 2º bimestre for menor que a recuperação
					  {
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[2]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"]+$resultado[4]["ed72_i_valornota"])/$quant; // descarta o 2º bimestre
					  }else{
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"]+$resultado[4]["ed72_i_valornota"])/$quant; // se a recuperação for menor que o
																																		//2º bimestre, descarta a recuperação
					  }
				  }else{ // o primeiro bimestre é menor que o segundo
					  if( $resultado[0]["ed72_i_valornota"] < $resultado[2]["ed72_i_valornota"] ) // se o 1º bimestre for menor que a recuperação e menor que o segundo
					  {
						  $media = ($resultado[2]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"]+$resultado[4]["ed72_i_valornota"])/$quant;
					  }else{ // o primeiro bimestre é menor que o segundo, mas é maior que recuperação, descarta a recuperação
						  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"]+$resultado[4]["ed72_i_valornota"])/$quant;
					  }
				  }
				  if( $resultado[1]["ed72_i_valornota"] == null ) // o primeiro bimestre tem nota, mas o segundo não
				  {
					  if( $resultado[2]["ed72_i_valornota"] > $resultado[0]["ed72_i_valornota"])// o primeiro bimestre tem nota, mas é maior que a recuperação???
					  {
						  $media = ($resultado[2]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"]+$resultado[4]["ed72_i_valornota"])/$quant; //se for usa a recuperação par o calculo
					  }
				  }
			  }else{ // o primeiro bimestre é nulo
				  if( $resultado[1]["ed72_i_valornota"] <> null ) // o segundo não é
				  {
					  if( $resultado[2]["ed72_i_valornota"] > $resultado[1]["ed72_i_valornota"])// mas o segundo bimestre é maior que a recuperação???
					  {
						  $media = ($resultado[2]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"]+$resultado[4]["ed72_i_valornota"])/$quant; // se não usa a recuperação no calculo
					  }
				  }
			  }
		  }
	  }



    return $media;
}//*********************************************************************************************************************************************


function mediaC2025( $matricula,$disciplina,$turma,$periodo,$periodoprova)
{
// Função criada para calcular a media a partir de 2025, porque ~a partir desse ano não haverá mais Recuperação Semestral-RSS
// ficando apenas  Recuperação Final
//**********************************************************************************************************************
	$sql    = "select ed09_i_codigo from periodoavaliacao where ed09_c_descr = '".$periodoprova."'";

	$result = pg_query($sql);
	$period = db_utils::fieldsmemory($result,0);
    $result = $this->quantDividir($matricula,$disciplina,$turma,$period->ed09_i_codigo); // busca os dados dos anos iniciais

	$quant = 0;
	$media = 0;
	if( $result[0]["ed72_i_valornota"]<>null ) // vê se o primeiro bimestre teve nota
	{
		$quant++;
	}

	if( $result[1]["ed72_i_valornota"]<>null ) // vê se o segundo bimestre teve nota
	{
		$quant++;
	}

	if( $result[2]["ed72_i_valornota"]<>null )
	{
		$quant++;
	}
	if( $result[3]["ed72_i_valornota"]<>null )
	{
		$quant++;
	}

//**********************************************************************************************************************

	  $resultado = $this->notasPorDisciplina($matricula,$disciplina,$turma,$period->ed09_i_codigo);

	  $media = 0;

	  if( $periodo == 1) //se foi escolhido o primeiro bimestre
	  {
		  $media = $resultado[0]["ed72_i_valornota"];
	  }

	  if( $periodo == 2) //se foi escolhido o segundo bimestre
	  {
		  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"])/$quant;
	  }
	  if( $periodo == 3) //se foi escolhido o terceiro bimestre
	  {
		  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[2]["ed72_i_valornota"])/$quant;
	  }
	  // todos os bimestre foram feitos mas não foi feito a recuperação
	  if( $periodo == 4) //se foi escolhido o segundo bimestre
	  {
		  $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[2]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant;
	  }


    return $media;
}//*********************************************************************************************************************************************

function mediaF($matricula,$disciplina,$turma,$mediaSF,$periodoprova,$ano)
{
	  $sql       = "select ed09_i_codigo from periodoavaliacao where ed09_c_descr = '".$periodoprova."'";
	  $result    = pg_query($sql);
	  $period    = db_utils::fieldsmemory($result,0);
	  $resultado4 = $this->notasPorDisciplina($matricula,$disciplina,$turma,$period->ed09_i_codigo);


	  $media = 0;
//	  if( $resultado4[5]["ed72_i_valornota"] > $mediaSF ) // Se o aluno fez a recuperação Final e ela foi maior que a media anual

    if( $ano <= 2024)
	{
	  if( $resultado4[5]["ed72_i_valornota"] <> null   )
	  {
	       $media = ($mediaSF + $resultado4[5]["ed72_i_valornota"])/2;	   // média final conforme suellem (media+ rec final)/2
	  }else{
		   $media = $mediaSF;  //Não havendo recuperação final ou a recuperação final a media final continua como a media anual
	  }
	}

	if( $ano >= 2025)
	{
	  if( $resultado4[4]["ed72_i_valornota"] <> null   )
	  {
	       $media = ($mediaSF + $resultado4[4]["ed72_i_valornota"])/2;	   // média final conforme suellem (media+ rec final)/2
	  }else{
		   $media = $mediaSF;  //Não havendo recuperação final ou a recuperação final a media final continua como a media anual
	  }
	}
    return $media;
}

function mediaAnosIniciais( $matricula,$disciplina,$turma,$periodo,$periodoprova)
{
	$sql    = "select ed09_i_codigo from periodoavaliacao where ed09_c_descr = '".$periodoprova."'";
	$result = pg_query($sql);
	$period = db_utils::fieldsmemory($result,0);
    $resultado = $this->notasPorDisciplina($matricula,$disciplina,$turma,$period->ed09_i_codigo); // busca os dados dos anos iniciais
	$quant = 0;
	$media = 0;
	for($x=0;$x<=count($resultado);$x++) // percorre o array com os dados
    {
		if( $resultado[$x]["ed72_i_valornota"]<>null ) //pega apenas a quantidade de colunas com notas - demanda 16568
		{
           $quant++;
		}
    }
	// calcula a média apenas das colunas que tem notas e descartando as colunas que estão nulas
	if( $periodo == 1)
	{
		$media = $resultado[0]["ed72_i_valornota"];
	}
	if( $periodo == 2)
	{
		$media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"])/$quant;
	}
	if( $periodo == 3)
	{
	    $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[2]["ed72_i_valornota"])/$quant;
	}
	$media = $media + .001;
	return $media;
}

function mediaEja( $matricula,$disciplina,$turma,$periodo,$periodoprova)
{
	$sql    = "select ed09_i_codigo from periodoavaliacao where ed09_c_descr = '".$periodoprova."'";
	$result = pg_query($sql);
	$period = db_utils::fieldsmemory($result,0);
    $resultado = $this->notasPorDisciplina($matricula,$disciplina,$turma,$period->ed09_i_codigo); // busca os dados dos anos iniciais
	$quant = 0;
	$media = 0;
	for($x=0;$x<=count($resultado);$x++) // percorre o array com os dados
    {
		if( $resultado[$x]["ed72_i_valornota"]<>null ) //pega apenas a quantidade de colunas com notas - demanda 16568
		{
           $quant++;
		}
    }
	// calcula a média apenas das colunas que tem notas e descartando as colunas que estão nulas
	if( $periodo == 1)
	{
		$media = $resultado[0]["ed72_i_valornota"];
	}
	if( $periodo == 2)
	{
		$media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"])/$quant;
	}
	if( $periodo == 3)
	{
	    $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[2]["ed72_i_valornota"])/$quant;
	}
	if( $periodo == 4)
	{
	    $media = ($resultado[0]["ed72_i_valornota"]+$resultado[1]["ed72_i_valornota"]+$resultado[2]["ed72_i_valornota"]+$resultado[3]["ed72_i_valornota"])/$quant;
	}
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/busca.txt","a+");
//fwrite($arq, $media);
//fwrite($arq,"\r\n");
//fclose($arq);
	$media = $media + .001;
	return $media;
}

function quantDividir($matricula,$disciplina,$turma,$periodoprova){

  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];
// voltei a disciplina para o nome para fazer anos iniciais
//$disciplina = utf8_decode($disciplina);


	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  '".$disciplina."'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";

    $result_D = db_query($sqlD);
    $odados = db_utils::fieldsmemory($result_D,0);

  $sqla = " select
			ed95_i_codigo,
			ed47_v_nome
			from
			diario
			inner join aluno on ed47_i_codigo = ed95_i_aluno
			inner join matricula on ed60_i_aluno = ed47_i_codigo
			inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
			inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
			where
			ed60_i_codigo = {$matricula}
			and
			ed95_i_regencia = ed59_i_codigo
			and
			ed59_i_disciplina = {$odados->ed12_i_codigo}";
  if( $serie <> 29 and $serie <> 30 and $serie <> 31 )
  {
       $sqla .= "
			    and
			    ed95_i_serie = {$serie}";
  }
  $sqla .= "
  		    and
			ed59_i_turma = {$turma}
			order by ed95_i_codigo";

  $sql2 = pg_query($sqla);
  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];

  $sqlN ="
		select
		distinct on (ed72_i_procavaliacao)
		ed232_c_descr as disciplina,
		ed09_c_descr  as bimestre,
		ed72_i_valornota,
		ed72_c_valorconceito,
		ed72_i_numfaltas
		from
		diarioavaliacao
		inner join diario           on ed95_i_codigo          = ed72_i_diario
		inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
		left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
		left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
		inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
		inner join aluno            on ed47_i_codigo          = ed95_i_aluno
		inner join matricula        on ed60_i_aluno           = ed47_i_codigo
		inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
		inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
		inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
		inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
		where
		ed72_i_diario = {$codigo} ";
/*
  if( $periodoprova <> null)
  {
      $sqlN .="
	          and
			  ed09_i_codigo <= {$periodoprova}
	          ";
  }
 */
  $sqlN .= "ORDER BY ed72_i_procavaliacao";

  $sql3 = pg_query($sqlN);
  $resultado = pg_fetch_all($sql3);
  return $resultado;

}

function imprimeConceitoFinais( $matricula,$disciplina,$turma,$periodo,$periodoprova)
{
	$resultado = $this->notasPorDisciplina($matricula,$disciplina,$turma,$period->ed09_i_codigo);
	return $resultado;
}

function imprimeConceitoFinaisEja( $matricula,$disciplina,$turma,$periodo,$periodoprova)
{
	$resultado = $this->notasPorDisciplinaEja($matricula,$disciplina,$turma,$period->ed09_i_codigo);
	return $resultado;
}

function final_anoletivo($matricula,$disciplina,$turma,$periodo,$periodoprova){

  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];
// voltei a disciplina para o nome para fazer anos iniciais
//$disciplina = utf8_decode($disciplina);


	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  '".$disciplina."'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";

    $result_D = db_query($sqlD);
    $odados = db_utils::fieldsmemory($result_D,0);

  $sqla = " select
			ed95_i_codigo,
			ed47_v_nome
			from
			diario
			inner join aluno on ed47_i_codigo = ed95_i_aluno
			inner join matricula on ed60_i_aluno = ed47_i_codigo
			inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
			inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
			where
			ed60_i_codigo = {$matricula}
			and
			ed95_i_regencia = ed59_i_codigo
			and
			ed59_i_disciplina = {$odados->ed12_i_codigo}";
  if( $serie <> 29 and $serie <> 30 and $serie <> 31 )
  {
       $sqla .= "
			    and
			    ed95_i_serie = {$serie}";
  }
  $sqla .= "
  		    and
			ed59_i_turma = {$turma}
			order by ed95_i_codigo";


  $sql2 = pg_query($sqla);
  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];
  if( substr($periodoprova,3,8) == 'BIMESTRE' )
  {
	  $periodoprova = '4º BIMESTRE';
  }

  if( substr($periodoprova,3,9) == 'TRIMESTRE' )
  {
	  $periodoprova = '3º TRIMESTRE';
  }
  $calendarioM = @$GLOBALS["HTTP_POST_VARS"]["calendarioF"];
  $sqlN ="
		select
		ed232_c_descr as disciplina,
		ed09_c_descr  as bimestre,
		ed72_i_valornota,
		ed72_c_valorconceito,
		ed72_i_numfaltas
		from
		diarioavaliacao
		inner join diario           on ed95_i_codigo          = ed72_i_diario
		inner join calendario       on ed95_i_calendario      = ed52_i_codigo
		inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
		left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
		left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
		inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
		inner join aluno            on ed47_i_codigo          = ed95_i_aluno
		inner join matricula        on ed60_i_aluno           = ed47_i_codigo
		inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
		inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
		inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
		inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
		where
		trim(ed09_c_descr)  = '{$periodoprova}'
		and
		ed60_i_codigo = {$matricula}
		and
		ed52_i_codigo  = {$calendarioM}
		and
		ed95_c_encerrado = 'N'
		and
		( ed232_c_descr <> 'TECNOLOGIA E INOVAÇÃO' and ed232_c_descr <> 'LÍNGUA INGLESA' and ed232_c_descr <> 'ARTE' and ed232_c_descr <> 'EDUCAÇÃO FISICA' )
        ORDER BY ed72_i_procavaliacao";

  $sql3 = pg_query($sqlN);
  return $sql3;

}



function media_final($matricula,$disciplina,$turma,$periodoprova,$calend){

  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];
// voltei a disciplina para o nome para fazer anos iniciais
//$disciplina = utf8_decode($disciplina);


	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed57_i_codigo    = {$turma}
				and
				ed59_c_freqglob != 'F'
				and
				ed223_i_serie    = ed59_i_serie";


    $result_D = db_query($sqlD);
    $odados = db_utils::fieldsmemory($result_D,0);

  $sqla = " select
			ed95_i_codigo,
			ed47_v_nome
			from
			diario
			inner join aluno on ed47_i_codigo = ed95_i_aluno
			inner join matricula on ed60_i_aluno = ed47_i_codigo
			inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
			inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
			where
			ed60_i_codigo = {$matricula}
			and
			ed95_i_regencia = ed59_i_codigo
			and
			ed59_i_disciplina = {$odados->ed12_i_codigo}";
  if( $serie <> 29 and $serie <> 30 and $serie <> 31 )
  {
       $sqla .= "
			    and
			    ed95_i_serie = {$serie}";
  }
  $sqla .= "
  		    and
			ed59_i_turma = {$turma}
			order by ed95_i_codigo";


  $sql2 = pg_query($sqla);
  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];

  $calendarioM = @$GLOBALS["HTTP_POST_VARS"]["calendarioF"];

  $n1 = 0;
  $n2 = 0;
  $n3 = 0;
  $n4 = 0;

  $sqlN ="
		select
		ed72_i_diario,
		ed72_i_valornota nota
		from
		diarioavaliacao
		inner join diario           on ed95_i_codigo          = ed72_i_diario
		inner join calendario       on ed95_i_calendario      = ed52_i_codigo
		inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
		left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
		left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
		inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
		inner join aluno            on ed47_i_codigo          = ed95_i_aluno
		inner join matricula        on ed60_i_aluno           = ed47_i_codigo
		inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
		inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
		inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
		inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
		where
		ed72_i_valornota is not null
		and
		ed60_i_codigo = {$matricula}
		and
		ed52_i_codigo  = {$calendarioM}
		and
		ed95_c_encerrado = 'N'
		and
		ed232_c_descr <> 'TECNOLOGIA E INOVAÇÃO'
        group by ed72_i_diario";


  $sql3 = pg_query($sqlN);
  $media = pg_fetch_all($sql3);
  if( $media[2]["nota"] > $media[0]["nota"] and $media[2]["nota"] < $media[1]["nota"]) // se a recuperação for maior que o 1º bimestre e menor que o 2º, substitue
  {
      $n1 = $media[2]["nota"];
  }else{  // caso contrario mantém a nota do 1º bimestre
	  $n1 = $media[0]["nota"];
  }

  if( $media[2]["nota"] > $media[1]["nota"] and $media[2]["nota"] < $media[0]["nota"]) // se a recuperação for maior que o 2º bimestre e menor que o 1º, substitue
  {
      $n2 = $media[2]["nota"];
  }else{  // caso contrario mantém a nota do 2º bimestre
	  $n2 = $media[0]["nota"];
  }
  $n3 = $media[3]["nota"];
  $n4 = $media[4]["nota"];
  $mediaFinal2 = ($n1+$n2+$n3+$n4)/4;
  return $mediaFinal2;

}

private function cumpriuDependencia($aluno) // deixei esta funcao para o caso de precisa novamente
{
  $n1 = null;
  $n2 = null;
  $n3 = null;
  $n4 = null;
  $n5 = null;
  $n6 = null;

	$cumpriu = true;
    $sql= "
			select
			distinct on (ed999_sequencial)
			ed232_i_codigo,
			ed232_c_descr as disciplina,
			ed114_ano     as ano,
			ed09_c_descr  as bimestre,
			ed09_c_descr  as media,
			ed999_nota    as nota,
			ed998_nota    as notareal,
			ed999_faltas  as faltas
			from
			diarioprogressaoavaliacao
			inner join diarioprogressao                        on ed993_sequencial       = ed999_diarioprogressao
			inner join progressaoparcialalunoturmaregencia     on ed115_sequencial       = ed993_progressaoparcialalunoturmaregencia
			inner join progressaoparcialalunomatricula         on ed150_sequencial       = ed115_progressaoparcialalunomatricula
			inner join progressaoparcialaluno                  on ed114_sequencial       = ed150_progressaoparcialaluno
			inner join disciplina                              on ed12_i_codigo          = ed114_disciplina
			inner join caddisciplina                           on ed232_i_codigo         = ed12_i_caddisciplina
			inner join procavaliacao                           on ed41_i_codigo          = ed999_procavaliacao
			inner join periodoavaliacao                        on ed09_i_codigo          = ed41_i_periodoavaliacao
			inner join diarioprogressaoresultado               on ed998_diarioprogressao = ed993_sequencial
			inner join procresultado                           on ed43_i_codigo          = ed998_procresultado
			inner join matricula                               on ed60_i_aluno           = ed114_aluno
			where
			ed60_i_codigo = ".$aluno."
			order by ed999_sequencial";



    $result = pg_query($sql);
    if( pg_num_rows($result)  > 0 )
	{
		$media = pg_fetch_all($result);
	    $n1 = $media[0]["nota"];
		$n2 = $media[1]["nota"];
		$n5 = $media[2]["nota"];
		$n3 = $media[3]["nota"];
		$n4 = $media[4]["nota"];
		$n6 = $media[5]["nota"];
		$dividir = 0;

		if( $n1 < $n2 and $n1 < $n5)
		{
			$n1 = $n5;
		}

		if( $n2 < $n1 and  $n2 < $n5)
		{
			$n2 = $n5;

		}

	    if( $n1<>null or $n1<>'' )
		{
			$dividir++;
		}
		if( $n2<>null or $n2<>'' )
		{
			$dividir++;
		}
		if( $n3<>null or $n3<>'' )
		{
			$dividir++;
		}
		if( $n4<>null or $n4<>'' )
		{
			$dividir++;
		}
		$mediaFinal3 = 0;
		$mediaFinal2 = ($n1+$n2+$n3+$n4)/$dividir;
		if( $n6 > $mediaFinal2)
		{
			$mediaFinal3 = ($n6 + $mediaFinal2)/2;
		}


		if( $mediaFinal3 < 5 )
		{
			$cumpriu = false;
		}else{
			$cumpriu = true;
		}
	}
    return $cumpriu;
}

function evadiu($aluno,$disciplina)
{
	$sql = "
			select
			ed993_evadido
			from
			progressaoparcialaluno
			inner join progressaoparcialalunomatricula     on ed150_progressaoparcialaluno              = ed114_sequencial
			inner join progressaoparcialalunoturmaregencia on ed115_progressaoparcialalunomatricula     = ed150_sequencial
			inner join plugins.diarioprogressao            on ed993_progressaoparcialalunoturmaregencia = ed115_sequencial
			inner join matricula                           on ed60_i_aluno                              = ed114_aluno
			where
			ed60_i_codigo = {$aluno}
		   ";
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/evadido.txt","a+");
//fwrite($arq, $disciplina);
//fwrite($arq,"\r\n");
//fclose($arq);

    $result	    = pg_query($sql);
	$evadido1   = false;
	for($x=0;$x<pg_num_rows($result);$x++)
	{
		$oevadiu    = db_utils::fieldsmemory($result,$x);
		if( $oevadiu->ed993_evadido == 't' )
		{
			$evadido1 = true;
			break;
		}
	}
	return $evadido1;
}

function checa_dep($nomeDisc,$matricula) // verifica se a dependencia atual é a mesma anterior e se foi aprovado
{
    // nomeDisc  é um array com as disciplinas que estão na dependencia
    $passou = 't'; // vai verificar se o aluno passou ou ficou na dependencia
	for($x=1;$x<=9;$x++) // vai percorrer o array
	{
		if($nomeDisc[$x] <> '' and $nomeDisc[$x] <> null )
		{
			$passou = $this->mesmaDependencia($nomeDisc[$x],$matricula); // procura se a disciplina enviada no array esta como dependencia no ultimo ano
			if( $passou == 'f' ) // se for e o aluno não passou, retorna falso
			{
				break; // abandona porque ja encontrou uma disciplina que não passou
			}
		}
	}
	return $passou; // retorna a informação
}

private function mesmaDependencia($nomeDisc,$aluno)
{
  $n1 = null;
  $n2 = null;
  $n3 = null;
  $n4 = null;
  $n5 = null;
  $n6 = null;

	$cumpriu = 't';
    $sql= "
			select
			distinct on (ed999_sequencial)
			ed232_i_codigo,
			ed232_c_descr as disciplina,
			ed114_ano     as ano,
			ed09_c_descr  as bimestre,
			ed09_c_descr  as media,
			ed999_nota    as nota,
			ed998_nota    as notareal,
			ed999_faltas  as faltas
			from
			diarioprogressaoavaliacao
			inner join diarioprogressao                        on ed993_sequencial       = ed999_diarioprogressao
			inner join progressaoparcialalunoturmaregencia     on ed115_sequencial       = ed993_progressaoparcialalunoturmaregencia
			inner join progressaoparcialalunomatricula         on ed150_sequencial       = ed115_progressaoparcialalunomatricula
			inner join progressaoparcialaluno                  on ed114_sequencial       = ed150_progressaoparcialaluno
			inner join disciplina                              on ed12_i_codigo          = ed114_disciplina
			inner join caddisciplina                           on ed232_i_codigo         = ed12_i_caddisciplina
			inner join procavaliacao                           on ed41_i_codigo          = ed999_procavaliacao
			inner join periodoavaliacao                        on ed09_i_codigo          = ed41_i_periodoavaliacao
			inner join diarioprogressaoresultado               on ed998_diarioprogressao = ed993_sequencial
			inner join procresultado                           on ed43_i_codigo          = ed998_procresultado
			inner join matricula                               on ed60_i_aluno           = ed114_aluno
			where
			ed60_i_codigo = ".$aluno."
			and
			ed232_c_descr ='".$nomeDisc."'
			order by ed999_sequencial";


    $result = pg_query($sql);
    if( pg_num_rows($result)  > 0 )
	{
		$media = pg_fetch_all($result);
	    $n1 = $media[0]["nota"];
		$n2 = $media[1]["nota"];
		$n5 = $media[2]["nota"];
		$n3 = $media[3]["nota"];
		$n4 = $media[4]["nota"];
		$n6 = $media[5]["nota"];
		$dividir = 0;

		if( $n1 < $n2 and $n1 < $n5)
		{
			$n1 = $n5;
		}

		if( $n2 < $n1 and  $n2 < $n5)
		{
			$n2 = $n5;

		}

	    if( $n1<>null or $n1<>'' )
		{
			$dividir++;
		}
		if( $n2<>null or $n2<>'' )
		{
			$dividir++;
		}
		if( $n3<>null or $n3<>'' )
		{
			$dividir++;
		}
		if( $n4<>null or $n4<>'' )
		{
			$dividir++;
		}
		$mediaFinal3 = 0;
		$mediaFinal2 = ($n1+$n2+$n3+$n4)/$dividir;
		if( $n6 > $mediaFinal2)
		{
			$mediaFinal3 = ($n6 + $mediaFinal2)/2;
		}else{
			$mediaFinal3 = $mediaFinal2;
		}
		if( $mediaFinal3 < 5 )
		{
			$cumpriu = 'f';
		}else{
			$cumpriu = 't';
		}
	}
    return $cumpriu;

}

function depanoant($aluno)
{
    $sqlDisc  = $this->depanoantMat($aluno);
	$result   = pg_query($sqlDisc);
	$aprovado = true;
	for($y=0;$y<pg_num_rows($result);$y++)
	{
        $oDados2 = db_utils::fieldsMemory($result,$y);
		$sql1= "
				select
				distinct on (ed999_sequencial)
				ed232_i_codigo,
				ed232_c_descr as disciplina,
				ed114_ano     as ano,
				ed09_c_descr  as bimestre,
				ed09_c_descr  as media,
				ed999_nota    as nota,
				ed998_nota    as notareal,
				ed999_faltas  as faltas
				from
				diarioprogressaoavaliacao
				inner join diarioprogressao                        on ed993_sequencial       = ed999_diarioprogressao
				inner join progressaoparcialalunoturmaregencia     on ed115_sequencial       = ed993_progressaoparcialalunoturmaregencia
				inner join progressaoparcialalunomatricula         on ed150_sequencial       = ed115_progressaoparcialalunomatricula
				inner join progressaoparcialaluno                  on ed114_sequencial       = ed150_progressaoparcialaluno
				inner join disciplina                              on ed12_i_codigo          = ed114_disciplina
				inner join caddisciplina                           on ed232_i_codigo         = ed12_i_caddisciplina
				inner join procavaliacao                           on ed41_i_codigo          = ed999_procavaliacao
				inner join periodoavaliacao                        on ed09_i_codigo          = ed41_i_periodoavaliacao
				inner join diarioprogressaoresultado               on ed998_diarioprogressao = ed993_sequencial
				inner join procresultado                           on ed43_i_codigo          = ed998_procresultado
				inner join matricula                               on ed60_i_aluno           = ed114_aluno
				where
				ed60_i_codigo = ".$aluno."
				and
				ed232_i_codigo = ".$oDados2->ed232_i_codigo."
				order by ed999_sequencial";

		$sql = pg_query($sql1);
		$aprovado = true;
		if( pg_num_rows($sql) > 0)
		{
			$nota1       = null;
			$nota2       = null;
			$nota3       = null;
			$nota4       = null;
			$nota5       = null;
			$nota6       = null;
			for($x=0;$x<pg_num_rows($sql);$x++)
			{
				$oDados = db_utils::fieldsMemory($sql, $x);   // 1º bimestre
				$nota1  = $oDados->nota;
				$x++;
				$oDados = db_utils::fieldsMemory($sql, $x);   // 2º bimestre
				$nota2  = $oDados->nota;
				$x++;
				$oDados = db_utils::fieldsMemory($sql, $x);   // recuperacao semestral
				$nota5  = $oDados->nota; // nota rec sem
				$x++;
				$oDados = db_utils::fieldsMemory($sql, $x);   // 3º bimestre
				$nota3  = $oDados->nota;
				$x++;
				$oDados = db_utils::fieldsMemory($sql, $x);   // 4º bimestre
				$nota4  = $oDados->nota;
				$divisao = 0;
				if( $nota1 <> null )
				{
					$divisao++;
				}
				if( $nota2 <> null )
				{
					$divisao++;
				}
				if( $nota3 <> null )
				{
					$divisao++;
				}
				if( $nota4 <> null )
				{
					$divisao++;
				}

				if( $nota1 < $nota2 and  $nota1 < $nota5)
				{
					$nota1 = $nota5;
				}
				if( $nota2 < $nota1 and  $nota2 < $nota5)
				{
					$nota2 = $nota5;
				}
	//M A
				$media = ($nota1+$nota2+$nota3+$nota4)/$divisao;
				$x++;
				$oDados = db_utils::fieldsMemory($sql, $x);   // recuperacao final
				$nota6 = $oDados->nota; // nota rec sem
	//M F
				if( $nota6 > 0 ) // o aluno fez a recuperação final
				{
					if( $nota6 > $media ) // mas ela só ira entrar na media final se for maior que a media final
					{
						$mediaf  = ($media+$nota6)/2;        // se for maior, a media anual devera ser somada a recuperação final e ai fazer a media final dividindo por 2
						$mediaAR = $mediaf;
					}else{
						$mediaf  = $media;  // se for menor que o 3º ou menor que o 4º, é descartada e a media anual prevalece como media final'
						$mediaAR = $media;
					}
				}else{
					$mediaf  = $media; // o aluno não fez a recuperação final
					$mediaAR = $media;
				}

				if( $mediaAR >= 5.0  )
				{
					$aprovado = true;
				}else{

					$aprovado = false;
					return $aprovado;
				}
			}
		}
	}
	return $aprovado;
}


function depanoantMat($aluno)
{
    $sql= "
			select
			distinct on (ed232_i_codigo)
			ed232_i_codigo
			from
			diarioprogressaoavaliacao
			inner join diarioprogressao                        on ed993_sequencial       = ed999_diarioprogressao
			inner join progressaoparcialalunoturmaregencia     on ed115_sequencial       = ed993_progressaoparcialalunoturmaregencia
			inner join progressaoparcialalunomatricula         on ed150_sequencial       = ed115_progressaoparcialalunomatricula
			inner join progressaoparcialaluno                  on ed114_sequencial       = ed150_progressaoparcialaluno
			inner join disciplina                              on ed12_i_codigo          = ed114_disciplina
			inner join caddisciplina                           on ed232_i_codigo         = ed12_i_caddisciplina
			inner join procavaliacao                           on ed41_i_codigo          = ed999_procavaliacao
			inner join periodoavaliacao                        on ed09_i_codigo          = ed41_i_periodoavaliacao
			inner join diarioprogressaoresultado               on ed998_diarioprogressao = ed993_sequencial
			inner join procresultado                           on ed43_i_codigo          = ed998_procresultado
			inner join matricula                               on ed60_i_aluno           = ed114_aluno
			where
			ed60_i_codigo = ".$aluno;
	return $sql;
}


function AnoCalendario($turma)
{
	$sql = "
	        select
			ed52_i_ano
			from
			calendario
			inner join turma on ed57_i_calendario = ed52_i_codigo
			where
			ed57_i_codigo = {$turma}
	       ";
	$result = pg_query($sql);
	$oDados = db_utils::fieldsMemory($result,0);
    return  $oDados->ed52_i_ano;
}


function aprovcons()
{

	$sql =  "
			select
			distinct ed11_c_descr as serie_conselho,
			d52_i_ano,
			ed253_aprovconselhotipo,
			ed253_t_obs, ed232_c_descr,
			ed253_alterarnotafinal,
			ed253_avaliacaoconselho
			from
			aprovconselho
			inner join db_usuarios  on  db_usuarios.id_usuario = aprovconselho.ed253_i_usuario
			left join rechumano  on  rechumano.ed20_i_codigo = aprovconselho.ed253_i_rechumano
			left join rechumanopessoal  on  rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo
			left join rhpessoal  on  rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
			left join cgm as cgmrh on  cgmrh.z01_numcgm = rhpessoal.rh01_numcgm
			left join rechumanocgm  on  rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo
			left join cgm as cgmcgm on  cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm
			inner join diario  on  diario.ed95_i_codigo = aprovconselho.ed253_i_diario
			inner join escola  on  escola.ed18_i_codigo = diario.ed95_i_escola
			inner join serie  on  serie.ed11_i_codigo = diario.ed95_i_serie
			inner join aluno  on  aluno.ed47_i_codigo = diario.ed95_i_aluno
			inner join calendario  on  calendario.ed52_i_codigo = diario.ed95_i_calendario
			inner join regencia  on  regencia.ed59_i_codigo = diario.ed95_i_regencia
			inner join turma  on  turma.ed57_i_codigo = regencia.ed59_i_turma
			inner join base  on  base.ed31_i_codigo = turma.ed57_i_base
			inner join disciplina  on  disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
			inner join caddisciplina  on  caddisciplina.ed232_i_codigo = disciplina.ed12_i_caddisciplina
			inner join aprovconselhotipo  on  aprovconselhotipo.ed122_sequencial = aprovconselho.ed253_aprovconselhotipo
			where
			ed95_i_aluno  = {$this->aluno1}
			and
			ed52_i_codigo = {$this->codTurma}
			order by ed11_c_descr, ed52_i_ano
";




//$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/busca.sql","a+");
//fwrite($arq, $sql);
//fwrite($arq,"\r\n");
//fclose($arq);

	$result = pg_query($sql);
    return  $result;
}


}
