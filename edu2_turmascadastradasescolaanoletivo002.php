<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
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

require_once(modification("fpdf151/pdf.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_app.utils.php"));

define('ALTURA_LINHA', 5);
define('TAMANHO_FONTE_CABECALHO', 5.5);
define('TAMANHO_FONTE', 6);
define('MARGEM_NOVA_PAGINA', 30);

db_postmemory($_POST);
parse_str($_SERVER['QUERY_STRING']);

$clEscola = new cl_escola;

$aEscolas =  array();
if (empty($escola)){
    $sSqlEscola = $clEscola->sql_query(null,"ed18_i_codigo,ed18_c_nome","ed18_c_nome");
    $rsEscolas = $clEscola->sql_record($sSqlEscola );
    $aEscolas = db_utils::getCollectionByRecord($rsEscolas);
    $escola = "TODOS";
}
else{

    $sSqlEscola = $clEscola->sql_query($escola,"ed18_i_codigo,ed18_c_nome");
    $rsEscolas = $clEscola->sql_record($sSqlEscola );
    $aEscolas = db_utils::getCollectionByRecord($rsEscolas);    
}

$head3 = "TURMAS CADASTRADAS POR ESCOLA E ANO LETIVO";
$head5 = "Ano = ".$anousu;
$head7 = "Escola = ".$escola;

$oPDF = new PDF('P','mm','A4'); 
$oPDF->Open(); 
$oPDF->AliasNbPages(); 
$oPDF->setfillcolor(235);
$oPDF->setfont('arial','b',8);
$oPDF->addPage();

foreach($aEscolas as $oEscola){
    $oPDF->ln();
    $aTurmasEspeciaisEscola = RetornaTurmasEspeciaisEscola($oEscola->ed18_i_codigo,$anousu);
    $aTurmasEscola = RetornaTurmasEscola($oEscola->ed18_i_codigo,$anousu);
    ImprimeEscolaPDF($oPDF,$oEscola->ed18_c_nome);

    

    $aTurmasEscolarizacaoNormal = RetornaDadosTurmaEscolarizacao($aTurmasEscola);
    $aTurmasEducacionalEspecial = RetornaDadosTurmaEducacionalEspecial($aTurmasEspeciaisEscola);
    $aTurmasAtividadeComplementar = RetornaDadosTurmaAtividadeComplementar($aTurmasEspeciaisEscola);

    $nTotalCH = 0;
    $nTotalNA = 0;
    $nTotalUI = 0;
    $nTotalUP = 0;

    $nTotalVagas = 0;
    $nTotalMatriculados = 0;
    $nTotalVagasAbertas = 0;

    
    $nTotalTarde = 0;
    $nTotalManha = 0;
    // $nTotalIntegral = 0;

    $nTotalEscolarizacao = 0;
    
    if (count($aTurmasEscolarizacaoNormal) >  0 ){

        ImprimeLinhaTurmasEscolaricacaoPDF($oPDF);
        ImprimeCabecalhoEscolaPDF($oPDF);
        foreach($aTurmasEscolarizacaoNormal as $oTurmaEscolarizacaoNormal){

            $nTotalVagas += (int)$oTurmaEscolarizacaoNormal->numero_vagas;
            $nTotalMatriculados += (int)$oTurmaEscolarizacaoNormal->numero_matriculas;
            $nTotalVagasAbertas += (int)$oTurmaEscolarizacaoNormal->numero_vagas - (int)$oTurmaEscolarizacaoNormal->numero_matriculas;

            switch($oTurmaEscolarizacaoNormal->ed57_i_tipoatend){
                case "0":
                    $nTotalNA++;
                    break;
                case "1":
                    $nTotalCH++;
                    break;
                case "2":
                    $nTotalUI++;
                    break;
                case "3":
                    $nTotalUP++;
                    break;
            }
            switch($oTurmaEscolarizacaoNormal->descricao_turno) {
                case "MANHÃ":
                    $nTotalManha++;
                    break;
                case "TARDE":
                    $nTotalTarde++;
                    break;
                // case "INTEGRAL":
                //     $nTotalIntegral++;
                //     break;
            }
            
            ImprimeDadosTurmaPDF($oPDF,$oTurmaEscolarizacaoNormal);
            $nTotalEscolarizacao++;
        }

        ImprimeTotalizadorEscolarizacaoNormalPDF($oPDF,$nTotalEscolarizacao);
        ImprimeTurmasTardePDF($oPDF, $nTotalTarde);
        ImprimeTurmasManhaPDF($oPDF, $nTotalManha);
        // ImprimeTurmasIntegralPDF($oPDF, $nTotalIntegral);
        // ImprimeTotalizadorNA_CH_UI_UPPDF($oPDF,$nTotalNA,$nTotalCH,$nTotalUI,$nTotalUP);

    }

    $nTotalEducacionalEspecial = 0;
    if(count($aTurmasEducacionalEspecial) >  0 ){

        ImprimeLinhaAtendimentoEspecialPDF($oPDF);
        ImprimeCabecalhoEscolaPDF($oPDF);
        foreach($aTurmasEducacionalEspecial as $oTurmaEducacionalEspecial){
            ImprimeDadosTurmaPDF($oPDF,$oTurmaEducacionalEspecial);
            $nTotalEducacionalEspecial++;
        }
        ImprimeTotalizadorAEEPDF($oPDF,$nTotalEducacionalEspecial);

    }

    $nTotalAtividadeComplementar = 0;
    if(count($aTurmasAtividadeComplementar) >  0 ){

        ImprimeLinhaAtividadesComplementaresPDF($oPDF);
        ImprimeCabecalhoEscolaPDF($oPDF);
        foreach($aTurmasAtividadeComplementar as $oTurmaAtividadeComplementar){
            ImprimeDadosTurmaPDF($oPDF,$oTurmaAtividadeComplementar);
            $nTotalAtividadeComplementar++;
        }
        ImprimeTotalizadorAtividadeComplementarPDF($oPDF,$nTotalAtividadeComplementar);
    }

    ImprimeTotalVagasPDF($oPDF, $nTotalVagas);
    ImprimeTotalMatriculadosPDF($oPDF, $nTotalMatriculados);
    ImprimeTotalVagasAbertasPDF($oPDF, $nTotalVagasAbertas);

}

$oPDF->Output();


function ImprimeEscolaPDF($oPDF,$sEscola){

   
   $oPDF->setfont('arial', 'b', '6');
   $oPDF->cell(168,ALTURA_LINHA, $sEscola , "LTRB", "L");
   $oPDF->ln();
   $oPDF->setfont('arial', '', TAMANHO_FONTE);

 }

 function ImprimeLinhaTurmasEscolaricacaoPDF($oPDF){

   
    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(168,ALTURA_LINHA, "Turmas de Escolarização" , "LTRB", "L");
    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
 
  }


 function ImprimeLinhaAtendimentoEspecialPDF($oPDF){

   
    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28,ALTURA_LINHA, "Atendimento Educacional Especial" , "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
 
  }
 

  function ImprimeLinhaAtividadesComplementaresPDF($oPDF){

    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28,ALTURA_LINHA, "Atividades complementares" , "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
 
  }


  function ImprimeTotalizadorAEEPDF($oPDF,$nTotal){

    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28,ALTURA_LINHA, "Total AEE = ".strval($nTotal) , "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
 
  }


  function ImprimeTotalizadorAtividadeComplementarPDF($oPDF,$nTotal){

    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28,ALTURA_LINHA, "Total AC = ".strval($nTotal), "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
 
  }


  function ImprimeTotalizadorEscolarizacaoNormalPDF($oPDF,$nTotal){

    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28,ALTURA_LINHA, "Total de Turmas = ".strval($nTotal), "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
 
  }

  function ImprimeTurmasTardePDF($oPDF, $nTotalTarde) {
    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28, ALTURA_LINHA, "Turmas da Tarde = ".strval($nTotalTarde), "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
}

function ImprimeTurmasManhaPDF($oPDF, $nTotalManha) {
    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28, ALTURA_LINHA, "Turmas da Manhã = ".strval($nTotalManha), "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
}

function ImprimeTotalVagasPDF($oPDF, $nTotalVagas) {
    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28, ALTURA_LINHA, "Total de Vagas = ".strval($nTotalVagas), "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
}

function ImprimeTotalMatriculadosPDF($oPDF, $nTotalMatriculados) {
    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28, ALTURA_LINHA, "Matriculados = ".strval($nTotalMatriculados), "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
}

function ImprimeTotalVagasAbertasPDF($oPDF, $nTotalVagasAbertas) {
    $oPDF->setfont('arial', 'b', '6');
    $oPDF->cell(28, ALTURA_LINHA, "Vagas Abertas = ".strval($nTotalVagasAbertas), "LTRB", "L");
    //    $oPDF->ln();
    $oPDF->setfont('arial', '', TAMANHO_FONTE);
}

// function ImprimeTurmasIntegralPDF($oPDF, $nTotalIntegral) {
//     $oPDF->setfont('arial', 'b', '6');
//     $oPDF->cell(28, ALTURA_LINHA, "Turmas Integrais = ".strval($nTotalIntegral), "LTRB", "L");
//     $oPDF->ln();
//     $oPDF->setfont('arial', '', TAMANHO_FONTE);
// }


//   function ImprimeTotalizadorNA_CH_UI_UPPDF($oPDF,$nTotalNA,$nTotalCH,$nTotalUI,$nTotalUP){

//     $sNAColumn = "NA = ".strval($nTotalNA);
//     $sCHColumn = "CH = ".strval($nTotalCH);
//     $sUIColumn = "UI = ".strval($nTotalUI);
//     $sUPColumn = "UP = ".strval($nTotalUP);

//     if($nTotalNA == 0){
//        $sNAColumn = ""; 
//     }

//     if($nTotalCH == 0){
//        $sCHColumn = ""; 
//     }

//     if($nTotalUI == 0){
//        $sUIColumn = ""; 
//     }

//     if($nTotalUP == 0){
//        $sUPColumn = "";
//     }

//     $oPDF->setfont('arial', 'b', '5');
//     $oPDF->cell(20,ALTURA_LINHA, $sNAColumn, "TB",0, "L");
//     $oPDF->cell(20,ALTURA_LINHA, $sCHColumn, "TB",0, "L");
//     $oPDF->cell(20,ALTURA_LINHA, $sUIColumn, "TB",0, "L");
//     $oPDF->cell(132,ALTURA_LINHA, $sUPColumn, "TB",1, "L");
//     $oPDF->setfont('arial', '', TAMANHO_FONTE);
 
//   }


function ImprimeCabecalhoEscolaPDF($oPDF){

   $oPDF->setfont('arial', 'b', TAMANHO_FONTE_CABECALHO);

   $oPDF->cell(52, ALTURA_LINHA, "TURMA", "LTRB", 0, "C",1);
   $oPDF->cell(20, ALTURA_LINHA, "TURNO", "LTRB", 0, "C",1);
   $oPDF->cell(20, ALTURA_LINHA, "TURNO ADICIONAL", "LTRB", 0, "C",1); 
   $oPDF->cell(27, ALTURA_LINHA, "SÉRIE", "LTRB", 0, "C",1);
   //$oPDF->cell(45, ALTURA_LINHA, "TIPO ATENDIMENTO", "LTRB", 0, "C",1);
   $oPDF->cell(10, ALTURA_LINHA, "VAGAS", "LTRB", 0,"C",1);
   $oPDF->cell(20, ALTURA_LINHA, "MATRICULADOS", "LTRB",0,"C",1);
   $oPDF->cell(19, ALTURA_LINHA, "VAGAS ABERTAS", "LTRB",0,"C",1);
 
   $oPDF->ln();
   $oPDF->setfont('arial', '', TAMANHO_FONTE);
 
 }

 function ImprimeDadosTurmaPDF($oPDF,$oDados){


    $oPDF->cell(52, ALTURA_LINHA, $oDados->descricao_turma, "LTRB", "C");
    $oPDF->cell(20, ALTURA_LINHA, $oDados->descricao_turno, "LTRB", "C");
    $oPDF->cell(20, ALTURA_LINHA, $oDados->descricao_turnoadicional, "LTRB", "C");
     //$oPDF->cell(20, ALTURA_LINHA, $oDados->descricao_turmas, "LTRB", "C");
     
    //$oPDF->cell(20, ALTURA_LINHA, $oDados->descricao_calendario, "LTRB", "C");
    $oPDF->cell(27, ALTURA_LINHA, $oDados->descricao_serie, "LTRB", "C");
    //$oPDF->cell(45,ALTURA_LINHA,  $oDados->tipoatendimento, "LTRB", "C");
    $oPDF->cell(10, ALTURA_LINHA, $oDados->numero_vagas, "LTRB", "C");
    $oPDF->cell(20, ALTURA_LINHA, $oDados->numero_matriculas, "LTRB", "C");
    $oPDF->cell(19, ALTURA_LINHA, strval((int)$oDados->numero_vagas - (int)$oDados->numero_matriculas), "LTRB", "C");

   $oPDF->ln();
}

function RetornaTurmasEscola($iEscola,$iAno){
    
    $sCampos  = "distinct ed57_i_codigo, ";
    $sCampos .= "ed57_i_tipoatend, ";
    $sCampos .= "ed57_c_descr as descricao_turma, ";
    $sCampos .= "ed52_c_descr as descricao_calendario, ";
    $sCampos .= "ed15_c_nome as descricao_turno,";
    // $sCampos .= "ed246_i_turno as descricao_turnoadicional,";
    $sCampos .= "fc_nomeetapaturma(ed57_i_codigo) as descricao_serie";

    
    $clturma = new cl_turma;
    $sSqlTurmasEscola = $clturma->sql_query("","$sCampos","ed57_c_descr"," ed52_c_passivo = 'N' AND ed57_i_escola = {$iEscola} and ed52_i_ano = {$iAno} ");

    $result = db_query($sSqlTurmasEscola);

    $aTurmasEscola = array();
    if ($result == true){
        $aTurmasEscola = db_utils::getCollectionByRecord($result);
    }
    return $aTurmasEscola;

}

function RetornaTurmasEspeciaisEscola($iEscola,$iAno){
    
    $clturmaac = new cl_turmaac;
    
    $sCampos  = "ed268_c_descr as descricao_turma, ";
    $sCampos .= "ed268_i_numvagas as numero_vagas, ";
    $sCampos .= "ed268_i_nummatr as numero_matriculas, ";
    $sCampos .= "ed268_i_codigo, ";
    $sCampos .= "ed268_c_descr, ";
    $sCampos .= "ed268_i_tipoatend, ";
    $sCampos .= "ed52_c_descr as descricao_calendario";

    $sWhere = "ed268_i_escola = {$iEscola} and ed52_i_ano = {$iAno}";
    $sSqlTurmasEspeciaisEscola = $clturmaac->sql_query("",$sCampos,"",$sWhere);
    
    $result = $clturmaac->sql_record($sSqlTurmasEspeciaisEscola);


    $aTurmasEspeciaisEscola = array();
    if ($result == true){
        $aTurmasEspeciaisEscola = db_utils::getCollectionByRecord($result);

    }
    return $aTurmasEspeciaisEscola;
}


function RetornaDadosTurmaEscolarizacao($aTurmasEscola){

    $aTurmasEscolarizacaoNormal = array();
    foreach($aTurmasEscola as $oTurmaEscola){
        
        switch($oTurmaEscola->ed57_i_tipoatend){
            case "0":
                $oTurmaEscola->tipoatendimento = "NÃO SE APLICA";
                break;
            case "1":
                $oTurmaEscola->tipoatendimento = "CLASSE HOSPITALAR";
                break;
            case "2":
                $oTurmaEscola->tipoatendimento = "UNIDADE DE INTERNAÇÃO";
                break;
            case "3":
                $oTurmaEscola->tipoatendimento = "UNIDADE PRISIONAL";
                break;
        }

        $oTurma = new Turma($oTurmaEscola->ed57_i_codigo);
        $aVagasOcupadas = $oTurma->getVagasOcupadas();
        $aVagas = $oTurma->getVagas();
        
        $ed336_vagas = 0;
        foreach ($aVagas as $iTurnoReferente => $iNumeroVagas) {
            $ed336_vagas = $iNumeroVagas;
        }

        $iAlunosMatriculados = 0;
        foreach ($aVagasOcupadas as $iTurnoReferente => $iVagasOcupadas) {
            $iAlunosMatriculados = $iVagasOcupadas;
        }

        $oTurmaEscola->numero_vagas = $ed336_vagas;
        $oTurmaEscola->numero_matriculas = $iAlunosMatriculados;

        $aTurmasEscolarizacaoNormal[] = $oTurmaEscola;
  

    }

    return $aTurmasEscolarizacaoNormal;

}

function RetornaDadosTurmaAtividadeComplementar($aTurmasEscola){

    $aTurmasAtividadeComplementar = array();
    foreach($aTurmasEscola as $oTurmaEscola){

        if ($oTurmaEscola->ed268_i_tipoatend  == "4"){
            $oTurmaEscola->tipoatendimento = "ATIVIDADE COMPLEMENTAR";
            $aTurmasAtividadeComplementar[] = $oTurmaEscola; 
        }
    }
    return $aTurmasAtividadeComplementar;
}

function RetornaDadosTurmaEducacionalEspecial($aTurmasEscola){

    $aTurmasEducacionalEspecial = array();
    foreach($aTurmasEscola as $oTurmaEscola){

        if ($oTurmaEscola->ed268_i_tipoatend  == "5"){
            $oTurmaEscola->tipoatendimento = "ATENDIMENTO EDUCACIONAL ESPECIAL - AEE";
            $aTurmasEducacionalEspecial[] = $oTurmaEscola; 
        }
    }
   
    return $aTurmasEducacionalEspecial ;

}

?>

