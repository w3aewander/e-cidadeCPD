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
class RelatorioGradeAproveitamentoM extends PDFGradeAproveitamento {

  private $oGradeAproveitamento;

  private $aObservacoes = array();

  /**
   *
   * @param FPDF      $oPdf         instancia do fpdf
   * @param Matricula $oMatricula   Instancia de matricula
   * @param integer   $iLimiteLinha Tamanho máximo que vai ter a linha da tabela no arquivo
   */
  public function __construct(FPDF $oPdf, Matricula $oMatricula, $iLimiteLinha) {

    $this->oPdf                 = $oPdf;
    $this->oGradeAproveitamento = new GradeAproveitamentoAluno($oMatricula);
    $this->iLimiteLinha         = $iLimiteLinha;
    $this->lApresentarNotaParcial = $this->oGradeAproveitamento->exibeNotaParcial();
    $this->controleDeFrequencia($oMatricula);
  }

  public function testa($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
  }


  /**
   * Escreve a grade de avaliação no pdf
   */
  public function montarGrade($ed29_c_descr=null, $ed11_c_descr=null, $guardapagina=null) {      
    //var_dump($ed29_c_descr, $ed11_c_descr); die("Confere");
    
    if($ed29_c_descr == "ENSINO FUNDAMENTAL" && $ed11_c_descr == "2º ANO"){
      $this->montarCabecalho($this->oGradeAproveitamento->getProcedimentoAvaliacao(), "ef2a");
      $this->oPdf->SetFont("Arial", "", 7);
      $totalfaltas = 0;
      $rep = 0;
    foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {      
      $iAlturaLinha          = 4;
      $iLinhasNomeDisciplina = $this->oPdf->NbLines($this->iTamanhoDisciplina, $oDisciplina->sNome);
      if ( $iLinhasNomeDisciplina > 1) {
        $iAlturaLinha *= $iLinhasNomeDisciplina;
      }
      $iYAntes = $this->oPdf->GetY();
      $this->oPdf->MultiCell($this->iTamanhoDisciplina, 4, $oDisciplina->sNome, 1, "L");
      $this->oPdf->SetY($iYAntes);
      $this->oPdf->SetX( $this->oPdf->lMargin + $this->iTamanhoDisciplina );

      foreach ($oDisciplina->aAproveitamento as $oAvaliacao){
        $oAvaliacao->oAproveitamento->nAproveitamento = str_replace(".", ",", $oAvaliacao->oAproveitamento->nAproveitamento);
        $oAvaliacao->oAproveitamento->nMinimoAprovacao = str_replace(".", ",", $oAvaliacao->oAproveitamento->nMinimoAprovacao);
        $oDisciplina->oNotaParcial->nNota = str_replace(".", ",", $oDisciplina->oNotaParcial->nNota);
        $oDisciplina->oResultadoFinal->nAproveitamentoFinal = str_replace(".", ",", $oDisciplina->oResultadoFinal->nAproveitamentoFinal);

        if(!$oAvaliacao->lApareceBoletim ){continue;}

        $this->oPdf->SetFont("Arial", "", 7);
        if ($oAvaliacao->oAproveitamento->nAproveitamento == "AMP" || !$oAvaliacao->oAproveitamento->lAtingiuMinimo) {
          //$this->oPdf->SetFont("Arial", "B", 7);
        }

        if ($oAvaliacao->lRecuperacao) {
          $this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }

        if ( $oAvaliacao->lResultado ) {
          //$this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }

        $this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
        $this->oPdf->SetFont("Arial", "", 7);
        $this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, $oAvaliacao->oAproveitamento->iFaltas, 1, 0, "C");
      }

      if ($this->lApresentarNotaParcial ) {
        $this->oPdf->Cell($this->iTamanhoNP, 4, $oDisciplina->oNotaParcial->nNota, 1, 0, "C");
      }

      $sPercentualFrequencia = '';
      if ($oDisciplina->oFrequencia->nPercentualFrequencia !== '') {
        $sPercentualFrequencia = "{$oDisciplina->oFrequencia->nPercentualFrequencia}%";
      }
      $this->oPdf->SetFont("Arial", "", 7);
      $totalfaltas += $oDisciplina->oFrequencia->iTotalFaltas;      

      

      $mAproveitamentoFinal          = '';
      $sTermoResultadoFinalAbreviado = '';

      //if ( $this->oGradeAproveitamento->getMatricula()->isConcluida() ) {
        $mAproveitamentoFinal          = $oDisciplina->oResultadoFinal->nAproveitamentoFinal;
        $sTermoResultadoFinalAbreviado = $oDisciplina->oResultadoFinal->sTermoResultadoFinalAbreviado;
      //}
      
      if($guardapagina == "3º TRIMESTRE"){
        if($sTermoResultadoFinalAbreviado == "AP"){
          $sTermoResultadoFinalAbreviado = "APROVADO";
        }else{
          $sTermoResultadoFinalAbreviado = "REPROVADO";
          $rep++;
        }      
      }else{
        $sTermoResultadoFinalAbreviado = "EM ANDAMENTO";
      }
      $this->oPdf->ln();
    }
    if($rep > 0){
      $sTermoResultadoFinalAbreviado = "REPROVADO";
    }
    $this->oPdf->Cell($this->iColunaRF + 40, $iAlturaLinha, "RESULTADO FINAL: " . $sTermoResultadoFinalAbreviado, 0, 1, "L");
    $guarday = $this->oPdf->GetY();
    $this->oPdf->SetY($iYAntes - 20);
    $this->oPdf->Cell($this->iColunaRF + 124, $iAlturaLinha, "", 0, 0, "L");
    $this->oPdf->Cell($this->iColunaRF + 28, $iAlturaLinha, "Total de Faltas: " . $totalfaltas, 0, 1, "L");

    $this->oPdf->Cell($this->iColunaRF + 124, $iAlturaLinha, "", 0, 0, "L");
    $this->oPdf->Cell($this->iColunaRF + 28, $iAlturaLinha, "Percentual de Frequência: " . $sPercentualFrequencia, 0, 1, "L");
    //var_dump($guardapagina, $this->oPdf->getY()); echo "<br>";
    if($_GET["assresp"] == "yes"){
      if($this->oPdf->getY() == 43){
        $this->oPdf->Rect(10, 35, 192, 71);
      }elseif($this->oPdf->getY() == 46){        
        $this->oPdf->Rect(10, 38, 192, 71);
      }
      //$this->oPdf->Rect(10, 35, 192, 65);
    }else{
      if($this->oPdf->getY() == 43){
        $this->oPdf->Rect(10, 35, 192, 48);
      }elseif($this->oPdf->getY() == 46){        
        $this->oPdf->Rect(10, 38, 192, 48);
      }
      //$this->oPdf->Rect(10, 35, 192, 45);
    }
    
    

    $this->oPdf->SetY($guarday);
    //FIM DO IF 2º ANO
  }elseif($ed29_c_descr == "ENSINO FUNDAMENTAL" && ($ed11_c_descr == "3º ANO" || $ed11_c_descr == "4º ANO" || $ed11_c_descr == "5º ANO")){
    
      $this->montarCabecalho($this->oGradeAproveitamento->getProcedimentoAvaliacao(), "ef345a");
      $this->oPdf->SetFont("Arial", "", 7);
      $totalfaltas = 0;
      $rep = 0;
    foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {      
      $iAlturaLinha          = 4;
      $iLinhasNomeDisciplina = $this->oPdf->NbLines($this->iTamanhoDisciplina, $oDisciplina->sNome);
      if ( $iLinhasNomeDisciplina > 1) {
        $iAlturaLinha *= $iLinhasNomeDisciplina;
      }
      $iYAntes = $this->oPdf->GetY();
      $this->oPdf->MultiCell($this->iTamanhoDisciplina, 4, $oDisciplina->sNome, 1, "L");
      $this->oPdf->SetY($iYAntes);
      $this->oPdf->SetX( $this->oPdf->lMargin + $this->iTamanhoDisciplina );

      foreach ($oDisciplina->aAproveitamento as $oAvaliacao){
        $oAvaliacao->oAproveitamento->nAproveitamento = str_replace(".", ",", $oAvaliacao->oAproveitamento->nAproveitamento);
        $oAvaliacao->oAproveitamento->nMinimoAprovacao = str_replace(".", ",", $oAvaliacao->oAproveitamento->nMinimoAprovacao);
        $oDisciplina->oNotaParcial->nNota = str_replace(".", ",", $oDisciplina->oNotaParcial->nNota);
        $oDisciplina->oResultadoFinal->nAproveitamentoFinal = str_replace(".", ",", $oDisciplina->oResultadoFinal->nAproveitamentoFinal);

        if(!$oAvaliacao->lApareceBoletim ){continue;}

        $this->oPdf->SetFont("Arial", "", 7);
        if ($oAvaliacao->oAproveitamento->nAproveitamento == "AMP" || !$oAvaliacao->oAproveitamento->lAtingiuMinimo) {
          //$this->oPdf->SetFont("Arial", "B", 7);
        }

        if ($oAvaliacao->lRecuperacao) {
          $this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }

        if ( $oAvaliacao->lResultado ) {
          $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }

        $this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
        $this->oPdf->SetFont("Arial", "", 7);
        $this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, $oAvaliacao->oAproveitamento->iFaltas, 1, 0, "C");
      }

      if ($this->lApresentarNotaParcial ) {
        $this->oPdf->Cell($this->iTamanhoNP, 4, $oDisciplina->oNotaParcial->nNota, 1, 0, "C");
      }

      $sPercentualFrequencia = '';
      if ($oDisciplina->oFrequencia->nPercentualFrequencia !== '') {
        $sPercentualFrequencia = "{$oDisciplina->oFrequencia->nPercentualFrequencia}%";
      }
      $this->oPdf->SetFont("Arial", "", 7);
      $totalfaltas += $oDisciplina->oFrequencia->iTotalFaltas;      

      

      $mAproveitamentoFinal          = '';
      $sTermoResultadoFinalAbreviado = '';
      

      //if ( $this->oGradeAproveitamento->getMatricula()->isConcluida() ) {
        $mAproveitamentoFinal          = $oDisciplina->oResultadoFinal->nAproveitamentoFinal;
        $sTermoResultadoFinalAbreviado = $oDisciplina->oResultadoFinal->sTermoResultadoFinalAbreviado;
      //}
        if($guardapagina == "3º TRIMESTRE"){
          if($sTermoResultadoFinalAbreviado == "AP"){
            $sTermoResultadoFinalAbreviado = "APROVADO";
          }else{
            $sTermoResultadoFinalAbreviado = "REPROVADO";
            $rep++;
          }      
        }else{
          $sTermoResultadoFinalAbreviado = "EM ANDAMENTO";
        }

        
      $this->oPdf->ln();
    }
    if($rep > 0){
      $sTermoResultadoFinalAbreviado = "REPROVADO";
    }
    $this->oPdf->Cell($this->iColunaRF + 40, $iAlturaLinha, "RESULTADO FINAL: " . $sTermoResultadoFinalAbreviado, 0, 1, "L");
    $guarday = $this->oPdf->GetY();
    $this->oPdf->SetY($iYAntes - 20);
    $this->oPdf->Cell($this->iColunaRF + 135, $iAlturaLinha, "", 0, 0, "L");
    $this->oPdf->Cell($this->iColunaRF + 28, $iAlturaLinha, "Total de Faltas: " . $totalfaltas, 0, 1, "L");

    $this->oPdf->Cell($this->iColunaRF + 135, $iAlturaLinha, "", 0, 0, "L");
    $this->oPdf->Cell($this->iColunaRF + 28, $iAlturaLinha, "Percentual de Frequência: " . $sPercentualFrequencia, 0, 1, "L");
    
    if($_GET["assresp"] == "yes"){      
      if($this->oPdf->getY() == 43){
        $this->oPdf->Rect(10, 35, 192, 67);
      }elseif($this->oPdf->getY() == 46){        
        $this->oPdf->Rect(10, 38, 192, 70);
      }      
      //$this->oPdf->Rect(10, 35, 192, 67);
    }else{
      if($this->oPdf->getY() == 43){
        $this->oPdf->Rect(10, 38, 192, 45);
      }elseif($this->oPdf->getY() == 46){        
        $this->oPdf->Rect(10, 38, 192, 48);
      }
      //$this->oPdf->Rect(10, 38, 192, 45);
    }

    $this->oPdf->SetY($guarday);
    //FIM DO IF 3º ao 5º ANO
  }elseif($ed29_c_descr == "ENSINO FUNDAMENTAL" && ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO")){
      
      $this->montarCabecalho($this->oGradeAproveitamento->getProcedimentoAvaliacao(), "ef6789a");
      $this->oPdf->SetFont("Arial", "", 7);
      $todasfaltas = 0;
      $rep = 0;
    foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {
      $totalfaltas = 0;
      //var_dump($oDisciplina->sNome);
      $iAlturaLinha          = 4;
      $iLinhasNomeDisciplina = $this->oPdf->NbLines($this->iTamanhoDisciplina, $oDisciplina->sNome);
      if ( $iLinhasNomeDisciplina > 1) {
        $iAlturaLinha *= $iLinhasNomeDisciplina;
      }
      $iYAntes = $this->oPdf->GetY();
      $this->oPdf->MultiCell($this->iTamanhoDisciplina, 4, $oDisciplina->sNome, 1, "L");
      $this->oPdf->SetY($iYAntes);
      $this->oPdf->SetX( $this->oPdf->lMargin + $this->iTamanhoDisciplina );

      
      $n1 = $oDisciplina->aAproveitamento[0]->oAproveitamento->nAproveitamento;
      $n2 = $oDisciplina->aAproveitamento[1]->oAproveitamento->nAproveitamento;
      $n3 = $oDisciplina->aAproveitamento[2]->oAproveitamento->nAproveitamento;
      $volta = 1;
      

      foreach ($oDisciplina->aAproveitamento as $oAvaliacao){
        
        
        //if($oAvaliacao->iCodigo == 29){continue;}
        $oAvaliacao->oAproveitamento->nAproveitamento = str_replace(".", ",", $oAvaliacao->oAproveitamento->nAproveitamento);
        $oAvaliacao->oAproveitamento->nMinimoAprovacao = str_replace(".", ",", $oAvaliacao->oAproveitamento->nMinimoAprovacao);
        $oDisciplina->oNotaParcial->nNota = str_replace(".", ",", $oDisciplina->oNotaParcial->nNota);
        $oDisciplina->oResultadoFinal->nAproveitamentoFinal = str_replace(".", ",", $oDisciplina->oResultadoFinal->nAproveitamentoFinal);

        if(!$oAvaliacao->lApareceBoletim ){continue;}

        $this->oPdf->SetFont("Arial", "", 7);
        if ($oAvaliacao->oAproveitamento->nAproveitamento == "AMP" || !$oAvaliacao->oAproveitamento->lAtingiuMinimo) {
          //$this->oPdf->SetFont("Arial", "B", 7);
        }

        if ($oAvaliacao->lRecuperacao) {
          $this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }

        if ( $oAvaliacao->lResultado ) {
          $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }
        
        if($oAvaliacao->oAproveitamento->nAproveitamento < $oAvaliacao->oAproveitamento->nMinimoAprovacao && ($oAvaliacao->iCodigo == 75 || $oAvaliacao->iCodigo == 76)){          
          
          //if($n1 < $n2 && $volta == 1){
          if($n1 < $n2 && $n1 < $n3 && $volta == 1){
            $this->oPdf->Line($this->oPdf->getX() + 3, $this->oPdf->getY() + 2, $this->oPdf->getX() + 7, $this->oPdf->getY() + 2);
          }

          //if($n2 < $n1 && $volta == 2){
          if($n2 < $n1 && $n2 < $n3 && $volta == 2){
            $this->oPdf->Line($this->oPdf->getX() + 3, $this->oPdf->getY() + 2, $this->oPdf->getX() + 7, $this->oPdf->getY() + 2);
          }
          //$this->oPdf->Line(43, 45, 47, 45);
          //$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, "---", 0, 0, "C");
          $this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
        }else{
          $this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
        }
        //$this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
        //$this->oPdf->SetFont("Arial", "", 7);
        $this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, $oAvaliacao->oAproveitamento->iFaltas, 1, 0, "C");
        $volta++;
      }
      

      if ($this->lApresentarNotaParcial ) {
        $this->oPdf->Cell($this->iTamanhoNP, 4, $oDisciplina->oNotaParcial->nNota, 1, 0, "C");
      }

      $sPercentualFrequencia = '';
      if ($oDisciplina->oFrequencia->nPercentualFrequencia !== '') {
        $sPercentualFrequencia = "{$oDisciplina->oFrequencia->nPercentualFrequencia}%";
      }
      $this->oPdf->SetFont("Arial", "", 7);
      $totalfaltas += $oDisciplina->oFrequencia->iTotalFaltas;
      $todasfaltas += $totalfaltas;

      

      $mAproveitamentoFinal          = '';
      $sTermoResultadoFinalAbreviado = '';

      //if ( $this->oGradeAproveitamento->getMatricula()->isConcluida() ) {
        $mAproveitamentoFinal          = $oDisciplina->oResultadoFinal->nAproveitamentoFinal;
        $sTermoResultadoFinalAbreviado = $oDisciplina->oResultadoFinal->sTermoResultadoFinalAbreviado;
      //}
      
        //if($this->oGradeAproveitamento->getMatricula()->isConcluida()){
          if($guardapagina == "4º BIMESTRE"){
            if($sTermoResultadoFinalAbreviado == "AP"){
              $sTermoResultadoFinalAbreviado = "APROVADO";
            }else{
              $sTermoResultadoFinalAbreviado = "REPROVADO";
              $rep++;
            }      
          }else{
            $sTermoResultadoFinalAbreviado = "EM ANDAMENTO";
          }
        //}else{
          //$sTermoResultadoFinalAbreviado = "EM ANDAMENTO";
        //}

        
        $xfreq = ($oDisciplina->oFrequencia->iTotalAulas - $totalfaltas) / $oDisciplina->oFrequencia->iTotalAulas * 100;
        $this->oPdf->Cell($this->iColunaRF + 4.5, 4, $totalfaltas, "BLR", 0, "C");
        //$this->oPdf->Cell($this->iColunaRF + 12.5, 4, $xfreq, "BLR", 0, "C");
        
      $this->oPdf->ln();
    }
    if($rep > 0){
      $sTermoResultadoFinalAbreviado = "REPROVADO";
    }
    
    $this->oPdf->Cell($this->iColunaRF + 40, $iAlturaLinha, "RESULTADO FINAL: " . $sTermoResultadoFinalAbreviado, 0, 1, "L");
    $guarday = $this->oPdf->GetY();

    $this->oPdf->SetY($iYAntes - 28);
    $this->oPdf->Cell($this->iColunaRF + 148, $iAlturaLinha, "", 0, 0, "L");
    $this->oPdf->Cell($this->iColunaRF + 28, $iAlturaLinha, "Total de Faltas: " . $todasfaltas, 0, 1, "L");

    $this->oPdf->Cell($this->iColunaRF + 148, $iAlturaLinha, "", 0, 0, "L");
    $this->oPdf->SetFont("Arial", "", 6.5);
    $this->oPdf->Cell($this->iColunaRF + 28, $iAlturaLinha, "Percentual de Frequência: " . $sPercentualFrequencia, 0, 1, "L");
    $this->oPdf->SetFont("Arial", "", 7);
    
    //$this->oPdf->SetY($iYAntes - 20);
    //$this->oPdf->Cell($this->iColunaRF + 135, $iAlturaLinha, "", 0, 0, "L");
    //$this->oPdf->Cell($this->iColunaRF + 28, $iAlturaLinha, "Total de Faltas: " . $totalfaltas, 0, 1, "L");
    //$this->oPdf->Cell($this->iColunaRF + 135, $iAlturaLinha, "", 0, 0, "L");
    //$this->oPdf->Cell($this->iColunaRF + 28, $iAlturaLinha, "Percentual de Frequência: " . $sPercentualFrequencia, 0, 1, "L");

    if($_GET["assresp"] == "yes"){
      //var_dump($guardapagina, $this->oPdf->getY()); echo "<br>";
      if($this->oPdf->getY() == 54){
        $this->oPdf->Rect(10, 38, 192, 83);
      }elseif($this->oPdf->getY() == 51){        
        $this->oPdf->Rect(10, 35, 192, 83);
      }      
      //$this->oPdf->Rect(10, 38, 192, 80);
    }else{
      if($this->oPdf->getY() == 54){
        $this->oPdf->Rect(10, 38, 192, 66);
      }elseif($this->oPdf->getY() == 51){        
        $this->oPdf->Rect(10, 35, 192, 60);
      }
      //$this->oPdf->Rect(10, 35, 192, 60);
    }
    $this->oPdf->SetY($guarday);
    //die("Confere");
    //FIM DO IF 6º ao 9º ANO
  }else{ 

    $this->montarCabecalho($this->oGradeAproveitamento->getProcedimentoAvaliacao());
    $this->oPdf->SetFont("Arial", "", 7);
    //$xxx = $this->oGradeAproveitamento->getProcedimentoAvaliacao();
    //$this->testa($xxx);
    //die("Confere");

    foreach ($this->oGradeAproveitamento->getGradeAproveitamento() as $oDisciplina) {
      
      $iAlturaLinha          = 4;
      $iLinhasNomeDisciplina = $this->oPdf->NbLines($this->iTamanhoDisciplina, $oDisciplina->sNome);
      if ( $iLinhasNomeDisciplina > 1) {
        $iAlturaLinha *= $iLinhasNomeDisciplina;
      }

      $iYAntes = $this->oPdf->GetY();
      $this->oPdf->MultiCell($this->iTamanhoDisciplina, 4, $oDisciplina->sNome, 1, "L");

      $this->oPdf->SetY($iYAntes);
      $this->oPdf->SetX( $this->oPdf->lMargin + $this->iTamanhoDisciplina );

      foreach ($oDisciplina->aAproveitamento as $oAvaliacao) {        
        //TROCA NOTA
        $oAvaliacao->oAproveitamento->nAproveitamento = str_replace(".", ",", $oAvaliacao->oAproveitamento->nAproveitamento);
        $oAvaliacao->oAproveitamento->nMinimoAprovacao = str_replace(".", ",", $oAvaliacao->oAproveitamento->nMinimoAprovacao);
        $oDisciplina->oNotaParcial->nNota = str_replace(".", ",", $oDisciplina->oNotaParcial->nNota);
        $oDisciplina->oResultadoFinal->nAproveitamentoFinal = str_replace(".", ",", $oDisciplina->oResultadoFinal->nAproveitamentoFinal);

        if ( !$oAvaliacao->lApareceBoletim ){
          continue;
        }

        $this->oPdf->SetFont("Arial", "", 7);
        if ($oAvaliacao->oAproveitamento->nAproveitamento == "AMP" || !$oAvaliacao->oAproveitamento->lAtingiuMinimo) {
          $this->oPdf->SetFont("Arial", "B", 7);
        }

        if ($oAvaliacao->lRecuperacao) {
          $this->oPdf->Cell($this->iTamanhoElementosRecuperacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }

        if ( $oAvaliacao->lResultado ) {
          $this->oPdf->Cell($this->iTamanhoResultados, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
          continue;
        }

        $this->oPdf->Cell($this->iColunaAvaliacao, $iAlturaLinha, $oAvaliacao->oAproveitamento->nAproveitamento, 1, 0, "C");
        $this->oPdf->SetFont("Arial", "", 7);
        $this->oPdf->Cell($this->iColunaFalta, $iAlturaLinha, $oAvaliacao->oAproveitamento->iFaltas, 1, 0, "C");
        //var_dump($oAvaliacao->oAproveitamento->iFaltas); echo "<br>";
      }

      if ($this->lApresentarNotaParcial ) {
        $this->oPdf->Cell($this->iTamanhoNP, 4, $oDisciplina->oNotaParcial->nNota, 1, 0, "C");
      }

      $sPercentualFrequencia = '';
      if ($oDisciplina->oFrequencia->nPercentualFrequencia !== '') {
        $sPercentualFrequencia = "{$oDisciplina->oFrequencia->nPercentualFrequencia}%";
      }
      $this->oPdf->SetFont("Arial", "", 7);
          //$this->oPdf->Cell($this->iColunaAD,   $iAlturaLinha, $oDisciplina->oFrequencia->iTotalAulas,                1, 0, "C");
          $this->oPdf->Cell($this->iColunaTF + 14,   $iAlturaLinha, $oDisciplina->oFrequencia->iTotalFaltas,               1, 0, "C");
          //$this->oPdf->Cell($this->iColunaFA,   $iAlturaLinha, "{$oDisciplina->oFrequencia->iFaltasAbonadas}",        1, 0, "C");
      //$this->oPdf->Cell($this->iColunaFreq, $iAlturaLinha, "{$sPercentualFrequencia}", 1, 0, "C");
      $this->oPdf->Cell($this->iColunaAD + $this->iColunaTF + $this->iColunaFA + $this->iColunaFreq - 1, $iAlturaLinha, "{$sPercentualFrequencia}", 1, 0, "C");

      if ( $oDisciplina->oResultadoFinal->nAproveitamentoFinal != '' && $oDisciplina->oResultadoFinal->sResultadoAprovacao != "A") {
        $this->oPdf->SetFont("Arial", "B", 7);
      }

      $mAproveitamentoFinal          = '';
      $sTermoResultadoFinalAbreviado = '';

      if ( $this->oGradeAproveitamento->getMatricula()->isConcluida() ) {
        $mAproveitamentoFinal          = $oDisciplina->oResultadoFinal->nAproveitamentoFinal;
        $sTermoResultadoFinalAbreviado = $oDisciplina->oResultadoFinal->sTermoResultadoFinalAbreviado;
      }
      //$this->oPdf->Cell($this->iColunaAprov, $iAlturaLinha, $mAproveitamentoFinal, 1, 0, "C");
      //$this->oPdf->SetFont("Arial", "", 7);
      //$this->oPdf->Cell($this->iColunaRF, $iAlturaLinha, $sTermoResultadoFinalAbreviado, 1, 1, "C");
      $this->oPdf->ln();
    }
  }//fim do else geral
    
  }

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

    $this->oPdf->SetFont("Arial", "B", 8);
    $this->oPdf->Cell($this->iLimiteLinha, 4, $aLegendas[$iTipo], 1, 1, "L");
  }

  /**
   * Escreve o minimo para aprovação no pdf
   */
  public function imprimirMinimoParaAprovacao() {

    $this->oPdf->SetFont("Arial", "B", 8);
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

      $this->oPdf->SetFont('arial', 'b', 8);
      $this->oPdf->Cell($this->iLimiteLinha, 4, "Níveis:", 1, 1, "L", 1);
      $this->oPdf->SetFont('arial', '', 7);     
      $this->oPdf->MultiCell($this->iLimiteLinha, 4, implode(' | ', $listaNiveisDescricao), "RL", "L", 0, 0);
    }
  }

  /**
   * Escreve o Andamento da Matricula
   */
  public function imprimirAndamentoDaMatricula() {

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

    $this->oPdf->SetFont("Arial", "B", 8);
    $mMinino  = "Resultado Final: ";
    $mMinino .= $sAndamento;
    $this->oPdf->Cell($this->iLimiteLinha, 4, $mMinino, 1, 1, "L");
  }

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

      $this->oPdf->SetFont( 'arial', '', 7 );
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
}
