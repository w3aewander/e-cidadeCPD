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

 require_once(modification("fpdf151/pdfwebseller.php"));
 require_once(modification("libs/db_utils.php"));
//  require_once(modification("vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet"));
//  require_once(modification("vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Writer/Xls"));
 use PhpOffice\PhpSpreadsheet\Spreadsheet;
 use PhpOffice\PhpSpreadsheet\Writer\Xls;

  unlink("boletim_estatistico.xls");

 $oDaoEduParametros = new cl_edu_parametros();
 $oDaoCalendario    = new cl_calendario();
 $oDaoTurma         = new cl_turma();
 $oDaoMatricula     = new cl_matricula();

 $spreadsheet = new Spreadsheet();
 $sheet = $spreadsheet->getActiveSheet();


 
 $sCampos          = "ed52_i_ano as ano_calendario, ed52_c_descr as descr_calendario";
 $sSql             = $oDaoCalendario->sql_query_file("", $sCampos, "", " ed52_i_codigo = $iCalendario" );
 $rsAno            = $oDaoCalendario->sql_record( $sSql );
 $oDadosCalendario = db_utils::fieldsmemory($rsAno, 0);
 
 $iDiaLimite = DBDate::getQuantidadeDiasMes($iMes, $oDadosCalendario->ano_calendario);
 
 $iCondicaoEnsino = "";
 if ($sEnsino != "") {
   $iCondicaoEnsino = " AND ed11_i_ensino in ($sEnsino)";
 }
 
 /*
  * Explode a data
  */
 $sCampos = " ed233_c_limitemov ";
 $sWhere  = " ed233_i_escola = $iEscola ";
 $sSql    =  $oDaoEduParametros->sql_query("", $sCampos, "", $sWhere);
 $rs      = $oDaoEduParametros->sql_record($sSql);
 
 if ($oDaoEduParametros->numrows > 0) {
 
   $oDadosParametros = db_utils::fieldsmemory($rs, 0);
   if (!strstr($oDadosParametros->ed233_c_limitemov,"/")) {
 
     ?>
     <table width='100%'>
      <tr>
       <td align='center'>
        <font color='#FF0000' face='arial'>
         <b>Parâmetro Dia/Mês Limite da Movimentação (Procedimentos->Parâmetros)<br>
            deve estar no formato dd/mm ou d/m (Exemplo: 02/02 ou 2/2)<br><br>
            Valor atual do parâmetro Dia/Mês Limite da Movimentação:
            <?= trim($oDadosParametros->ed233_c_limitemov) == "" ? "Não informado"
              :$oDadosParametros->ed233_c_limitemov
            ?><br>
         <input type='button' value='Fechar' onclick='window.close()'>
        </font>
       </td>
      </tr>
     </table>
     <?php
     exit;
   }
 
   $aLimiteMov     = explode("/", $oDadosParametros->ed233_c_limitemov);
   $iDiaLimiteMov  = $aLimiteMov[0];
   $iMesLimiteMov  = $aLimiteMov[1];
 
   if (@!checkdate($iMesLimiteMov, $iDiaLimiteMov, $oDadosCalendario->ano_calendario)) {
     ?>
     <table width='100%'>
      <tr>
       <td align='center'>
        <font color='#FF0000' face='arial'>
         <b>Parâmetro Dia/Mês Limite da Movimentação (Procedimentos->Parâmetros)<br>
            deve estar no formato dd/mm ou d/m (Exemplo: 02/02 ou 2/2) e deve ser uma data válida.<br><br>
            Valor atual do parâmetro Dia/Mês Limite da Movimentação:
            <?= trim($oDadosParametros->ed233_c_limitemov) == "" ? "Não informado"
              :$oDadosParametros->ed233_c_limitemov
            ?><br>
            Data Limite da Movimentação: <?= $iDiaLimiteMov."/".$iMesLimiteMov."/".$oDadosCalendario->ano_calendario
                                         ?> (Data Inválida)<br></b>
         <input type='button' value='Fechar' onclick='window.close()'>
        </font>
       </td>
      </tr>
     </table>
     <?php
     exit;
   }
 
   $dDataLimiteMov  = $oDadosCalendario->ano_calendario."-".(strlen($iMesLimiteMov) == 1 ? "0".
                                                             $iMesLimiteMov:$iMesLimiteMov
                                                            );
   $dDataLimiteMov .= "-".(strlen($iDiaLimiteMov) == 1 ? "0".$iDiaLimiteMov:$iDiaLimiteMov);
 } else {
   $dDataLimiteMov = $oDadosCalendario->ano_calendario."-01-01";
 }
 
 $dDataInicial = $oDadosCalendario->ano_calendario."-".(strlen($iMes) == 1 ? "0".$iMes:$iMes)."-01";
 $dDataLimite  = $oDadosCalendario->ano_calendario."-".(strlen($iMes) == 1 ? "0".$iMes:$iMes)."-".$iDiaLimite;
 
 /*
  * QUANTIDADE DE TURMAS.
  */
 $sCamposQtdTurmas  = " count(ed57_i_codigo) as qtdturmas, ed11_c_descr,  ed11_c_abrev, ed11_i_codigo,";
 $sCamposQtdTurmas .= " ed11_i_ensino, ed10_c_descr, ed15_i_codigo, ed15_c_nome, ed15_i_sequencia ";
 $sWhereQtdTurmas   = " ed57_i_escola = $iEscola AND ed52_i_codigo = $iCalendario $iCondicaoEnsino ";
 $sWhereQtdTurmas  .= " AND exists(select * from matricula where ed60_i_turma = ed57_i_codigo ";
 $sWhereQtdTurmas  .= " AND ed60_d_datamatricula <= '$dDataLimite') ";
 $sGroupQtdTurmas   = " GROUP BY ed11_c_descr, ed11_i_codigo, ed11_i_sequencia, ed11_i_ensino, ed10_c_descr, ed15_i_codigo,";
 $sGroupQtdTurmas  .= " ed15_c_nome, ed15_i_sequencia, ed11_c_abrev ";
 $sOrderQtdTurmas   = " ed15_i_sequencia, ed11_i_ensino, ed11_i_sequencia ";
 $sSqlQtdTurmas     = $oDaoTurma->sql_query_boletimestat("", $sCamposQtdTurmas, $sOrderQtdTurmas,
                                                         $sWhereQtdTurmas.$sGroupQtdTurmas);
 $rsQtdTurmas       = $oDaoTurma->sql_record($sSqlQtdTurmas);
 $iLinhasQtdTurmas  = $oDaoTurma->numrows;
 if ($iLinhasQtdTurmas == 0) {?>
 
   <table width='100%'>
    <tr>
     <td align='center'>
      <font color='#FF0000' face='arial'>
       <b>Nenhum registro encontrado.<br>
       <input type='button' value='Fechar' onclick='window.close()'></b>
      </font>
     </td>
    </tr>
   </table>
   <?php
   exit;
 }
 
 $oDadosTurmas  = db_utils::fieldsmemory($rsQtdTurmas, 0, 'ed10_c_descr');
 $sTituloEnsino = "TODOS";
 if ($sEnsino != "") {
   $sTituloEnsino = $oDadosTurmas->ed10_c_descr;
 }
 
 $lTroca = 1;




 /*
  * Váriáveis Abreviadas:
  * M     = Alunos do Sexo Masculino;
  * F     = Aluno do Sexo Feminino;
  * T     = Total;
  * Tot   = Total;
  * Trans = Alunos Transferidos;
  * Evad  = Alunos Evadidos;
  * Nov   = Alunos Novos;
  * Efe   = Alunos com Matriculas Efetivas;
  */
 $iSomaMTotal = 0;
 $iSomaFTotal = 0;
 $iSomaTot    = 0;
 $iSomaMTrans = 0;
 $iSomaFTrans = 0;
 $iSomaTrans  = 0;
 $iSomaMEvad  = 0;
 $iSomaFEvad  = 0;
 $iSomaTEvad  = 0;
 $iSomaMCanc  = 0;
 $iSomaFCanc  = 0;
 $iSomaTCanc  = 0;
 $iSomaMDesis = 0;
 $iSomaFDesis = 0;
 $iSomaTDesis = 0;
 $iSomaMFalec = 0;
 $iSomaFFalec = 0;
 $iSomaTFalec = 0;
 $iSomaMNov   = 0;
 $iSomaFNov   = 0;
 $iSomaTNov   = 0;
 $iSomaTurma  = 0;
 $iSomaMEfet  = 0;
 $iSomaFEfet  = 0;
 $iSomaTEfet  = 0;
 $iPrimeiro   = "";
 $iPriTurno   = "";
 $iTurnoTot   = 1;
 

$oEscola = EscolaRepository::getEscolaByCodigo( $iEscola );

$sNomeEscola = mb_convert_encoding($oEscola->getNome(), 'UTF-8', 'ISO-8859-1');
 
$sheet->setCellValue('A1', $sNomeEscola);

$linhaAtual = 2;


$cabecalho = [
  'Etapa', 'Turmas', 'Turno', 'Matrícula Anterior M', 'Matrícula Anterior F', 'Matrícula Anterior T',
  'Transferidos M', 'Transferidos F', 'Transferidos T', 'Evadidos M', 'Evadidos F', 'Evadidos T',
  'Cancelados M', 'Cancelados F', 'Cancelados T', 'Desistentes M', 'Desistentes F', 'Desistentes T',
  'Falecidos M', 'Falecidos F', 'Falecidos T', 'Novos M', 'Novos F', 'Novos T',
  'Matrícula Atual M', 'Matrícula Atual F', 'Matrícula Atual T'
];


$cabecalho = array_map(function($v) {
  return mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
}, $cabecalho);

$sheet->fromArray($cabecalho, null, 'A' . $linhaAtual);

$linhaAtual++;

for ($iContTurmas = 0; $iContTurmas < $iLinhasQtdTurmas; $iContTurmas++) {
 
   $oDadosTurmas = db_utils::fieldsmemory($rsQtdTurmas, $iContTurmas);
   $oDadosTurmas->ed15_c_nome;

  //  $oPdf->setfont('arial', 'b', 8);

   // Barra de Turno
   //$oPdf->setfillcolor(215);
 
   if ($iPriTurno != $oDadosTurmas->ed15_i_codigo) {
    
    //$oPdf->cell(275.6, 4, "Turno: ".$oDadosTurmas->ed15_c_nome, 1, 1, "L", 1);
    $iPriTurno = $oDadosTurmas->ed15_i_codigo;
   }
 

   // Tipo de Ensino
   //$oPdf->setfillcolor(240);
   
   if ($iPrimeiro != $oDadosTurmas->ed11_i_ensino) {
    
     //$oPdf->cell(275.6, 4,$oDadosTurmas->ed10_c_descr, 1, 1, "L", 1);
     $iPrimeiro = $oDadosTurmas->ed11_i_ensino;
   }
 
   // Nomes das Turmas
  //  $oPdf->setfont('arial', '', 8);
  //  $oPdf->cell(16.6, 6, $oDadosTurmas->ed11_c_abrev, 1, 0, "C", 0);
  //  $oPdf->cell(16.6, 6, $oDadosTurmas->qtdturmas, 1, 0, "C", 0);
  //  $oPdf->cell(14.4, 6, $oDadosTurmas->ed15_c_nome, 1, 0, "C", 0);
   $iSomaTurma += $oDadosTurmas->qtdturmas;
 
   /*
    * MATRÍCULA
    */
   $dtInicioMes = "{$oDadosCalendario->ano_calendario}-$iMes-01";
 
   $sCamposMatricula  = "ed47_v_sexo,";
   $sCamposMatricula .= "case";
   $sCamposMatricula .= "     when ed60_d_datamatricula between '{$dtInicioMes}' and '{$dDataLimite}'";
   $sCamposMatricula .= "          then 1";
   $sCamposMatricula .= "          else 0";
   $sCamposMatricula .= " end as novo,";
   $sCamposMatricula .= "case";
   $sCamposMatricula .= "     when ed60_c_situacao = 'MATRICULADO' and ed60_d_datasaida is null";
   $sCamposMatricula .= "          then 'M1'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'TRANSFERIDO REDE' or ed60_c_situacao = 'TRANSFERIDO FORA')";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposMatricula .= "          then 'M1'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'TRANSFERIDO REDE' or ed60_c_situacao = 'TRANSFERIDO FORA')";
   $sCamposMatricula .= "      and ed60_d_datasaida between '{$dDataLimiteMov}' and '{$dDataLimite}'";
   $sCamposMatricula .= "          then 'M2'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'EVADIDO')";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposMatricula .= "          then 'M1'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'EVADIDO')";
   $sCamposMatricula .= "     and ed60_d_datasaida between '{$dDataLimiteMov}' and '{$dDataLimite}'";
   $sCamposMatricula .= "         then 'M3'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'CANCELADO')";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposMatricula .= "          then 'M1'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'CANCELADO')";
   $sCamposMatricula .= "     and ed60_d_datasaida between '{$dDataLimiteMov}' and '{$dDataLimite}'";
   $sCamposMatricula .= "         then 'M5'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'DESISTENTE')";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposMatricula .= "          then 'M1'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'DESISTENTE')";
   $sCamposMatricula .= "     and ed60_d_datasaida between '{$dDataLimiteMov}' and '{$dDataLimite}'";
   $sCamposMatricula .= "         then 'M6'";
   $sCamposMatricula .= "     when (ed60_c_situacao ='FALECIDO')";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposMatricula .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposMatricula .= "          then 'M1'";
   $sCamposMatricula .= "     when (ed60_c_situacao ='FALECIDO')";
   $sCamposMatricula .= "     and ed60_d_datasaida between '{$dDataLimiteMov}' and '{$dDataLimite}'";
   $sCamposMatricula .= "         then 'M7'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'AVANÇADO' or ed60_c_situacao = 'CLASSIFICADO')";
   $sCamposMatricula .= "     and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposMatricula .= "     and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposMatricula .= "         then 'M1'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'AVANÇADO' or ed60_c_situacao = 'CLASSIFICADO')";
   $sCamposMatricula .= "     and ed60_d_datasaida between '{$dDataLimiteMov}' and '{$dDataLimite}'";
   $sCamposMatricula .= "         then 'M4'";
   $sCamposMatricula .= "     when (ed60_c_situacao = 'TROCA DE MODALIDADE' and ed60_d_datasaida >= '{$dDataLimite}')";
   $sCamposMatricula .= "        then 'M1'";
   $sCamposMatricula .= " end as situacao";
 
   $sWhereMatricula   = " ed57_i_escola = $iEscola AND ed221_i_serie = $oDadosTurmas->ed11_i_codigo";
   $sWhereMatricula  .= " AND ed52_i_codigo = $iCalendario AND ed221_c_origem = 'S' AND ed15_i_codigo = $iPriTurno";
   $sWhereMatricula  .= " AND ed60_d_datamatricula <= '$dDataLimite' $iCondicaoEnsino ";
   $sOrderMatricula   = " ed11_c_descr,ed15_i_sequencia,ed57_c_descr,ed47_v_nome ";
   $sSqlMatricula     = $oDaoMatricula->sql_query_boletimestat("", $sCamposMatricula, $sOrderMatricula, $sWhereMatricula);
 
   $rsMatricula       = $oDaoMatricula->sql_record($sSqlMatricula);
   $iLinhasMatricula  = $oDaoMatricula->numrows;
 
   $iMTot   = 0;
   $iFTot   = 0;
   $iTot    = 0;
   $iMTrans = 0;
   $iFTrans = 0;
   $iTrans  = 0;
   $iMEvad  = 0;
   $iFEvad  = 0;
   $iTEvad  = 0;
   $iMCanc  = 0;
   $iFCanc  = 0;
   $iTCanc  = 0;
   $iMDesis = 0;
   $iFDesis = 0;
   $iTDesis = 0;
   $iMFalec = 0;
   $iFFalec = 0;
   $iTFalec = 0;
   $iMNov   = 0;
   $iFNov   = 0;
   $iTNov   = 0;
   $iMEfet  = 0;
   $iFEfet  = 0;
   $iTEfet  = 0;
 
   for ($iContMatricula = 0; $iContMatricula < $iLinhasMatricula; $iContMatricula++) {
 
     $oDadosMatricula = db_utils::fieldsmemory($rsMatricula, $iContMatricula);
     if ($oDadosMatricula->ed47_v_sexo == "M" && $oDadosMatricula->situacao != "") {
 
       $iMTot++;
       $iSomaMTotal++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "F" && $oDadosMatricula->situacao != "") {
 
       $iFTot++;
       $iSomaFTotal++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "M" && $oDadosMatricula->situacao == "M2") {
 
       $iMTrans++;
       $iSomaMTrans++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "F" && $oDadosMatricula->situacao == "M2") {
 
       $iFTrans++;
       $iSomaFTrans++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "M" && $oDadosMatricula->situacao == "M3") {
 
       $iMEvad++;
       $iSomaMEvad++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "F" && $oDadosMatricula->situacao == "M3") {
 
       $iFEvad++;
       $iSomaFEvad++;
     }
     if ($oDadosMatricula->ed47_v_sexo == "M" && $oDadosMatricula->situacao == "M5") {
 
       $iMCanc++;
       $iSomaMCanc++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "F" && $oDadosMatricula->situacao == "M5") {
 
       $iFCanc++;
       $iSomaFCanc++;
     }
     if ($oDadosMatricula->ed47_v_sexo == "M" && $oDadosMatricula->situacao == "M6") {
 
       $iMDesis++;
       $iSomaMDesis++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "F" && $oDadosMatricula->situacao == "M6") {
 
       $iFDesis++;
       $iSomaFDesis++;
     }
     if ($oDadosMatricula->ed47_v_sexo == "M" && $oDadosMatricula->situacao == "M7") {
 
       $iMFalec++;
       $iSomaMFalec++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "F" && $oDadosMatricula->situacao == "M7") {
 
       $iFFalec++;
       $iSomaFFalec++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "M" && $oDadosMatricula->novo == 1 && $oDadosMatricula->situacao != "") {
 
       $iMNov++;
       $iSomaMNov++;
     }
 
     if ($oDadosMatricula->ed47_v_sexo == "F" && $oDadosMatricula->novo == 1 && $oDadosMatricula->situacao != "") {
 
       $iFNov++;
       $iSomaFNov++;
     }
   }

  // Contagem individual


   $iTot      = $iMTot+$iFTot;
   $iSomaTot += $iTot;
 
   $iTrans      = $iMTrans+$iFTrans;
   $iSomaTrans += $iTrans;

   $iTEvad      = $iMEvad+$iFEvad;
   $iSomaTEvad += $iTEvad;

   $iTCanc      = $iMCanc+$iFCanc;
   $iSomaTCanc += $iTCanc;

   $iTDesis      = $iMDesis+$iFDesis;
   $iSomaTDesis += $iTDesis;

   $iTFalec      = $iMFalec+$iFFalec;
   $iSomaTFalec += $iTFalec;
 
   $iTNov      = $iMNov+$iFNov;
   $iSomaTNov += $iTNov;

 
   /*
    * Matricula efetiva
    */
   $iMEfet      = $iMTot-($iMTrans+$iMEvad+$iMDesis+$iMCanc+$iMFalec);
   $iFEfet      = $iFTot-($iFTrans+$iFEvad+$iFDesis+$iFCanc+$iFFalec);
   $iSomaMEfet += $iMEfet;
   $iSomaFEfet += $iFEfet;
 

   $iTEfet      = $iMEfet+$iFEfet;
   $iSomaTEfet += $iTEfet;



   if ( $iPriTurno != db_utils::fieldsmemory($rsQtdTurmas, $iContTurmas+1)->ed15_i_codigo)
   {


   }else{
    $iTurnoTot= $iTurnoTot + 1;
   }

   $linha = [
    mb_convert_encoding($oDadosTurmas->ed11_c_abrev,'UTF-8', 'ISO-8859-1'),   // Etapa
    $oDadosTurmas->qtdturmas,                                                 // Turmas
    mb_convert_encoding($oDadosTurmas->ed15_c_nome, 'UTF-8', 'ISO-8859-1'),   // Turno
    $iMTot,                                                                   // Matrícula Anterior M
    $iFTot,                                                                   // Matrícula Anterior F
    $iTot,                                                                    // Matrícula Anterior T
    $iMTrans,                                                                 // Transferidos M
    $iFTrans,                                                                 // Transferidos F
    $iTrans,                                                                  // Transferidos T
    $iMEvad,                                                                  // Evadidos M
    $iFEvad,                                                                  // Evadidos F
    $iTEvad,                                                                  // Evadidos T
    $iMCanc,                                                                  // Cancelados M
    $iFCanc,                                                                  // Cancelados F
    $iTCanc,                                                                  // Cancelados T
    $iMDesis,                                                                 // Desistentes M
    $iFDesis,                                                                 // Desistentes F
    $iTDesis,                                                                 // Desistentes T
    $iMFalec,                                                                 // Falecidos M
    $iFFalec,                                                                 // Falecidos F
    $iTFalec,                                                                 // Falecidos T
    $iMNov,                                                                   // Novos M
    $iFNov,                                                                   // Novos F
    $iTNov,                                                                   // Novos T
    $iMEfet,                                                                  // Matrícula Atual M
    $iFEfet,                                                                  // Matrícula Atual F
    $iTEfet                                                                   // Matrícula Atual T
];

$sheet->fromArray($linha, null, 'A' . $linhaAtual);
$linhaAtual++;

}

 
 $total = [
  'TOTAL',
  $iSomaTurma,
  '',
  $iSomaMTotal,
  $iSomaFTotal,
  $iSomaTot,
  $iSomaMTrans,
  $iSomaFTrans,
  $iSomaTrans,
  $iSomaMEvad,
  $iSomaFEvad,
  $iSomaTEvad,
  $iSomaMCanc,
  $iSomaFCanc,
  $iSomaTCanc,
  $iSomaMDesis,
  $iSomaFDesis,
  $iSomaTDesis,
  $iSomaMFalec,
  $iSomaFFalec,
  $iSomaTFalec,
  $iSomaMNov,
  $iSomaFNov,
  $iSomaTNov,
  $iSomaMEfet,
  $iSomaFEfet,
  $iSomaTEfet
];

$sheet->fromArray($total, null, 'A' . $linhaAtual);
$linhaAtual += 2; // Pula duas linhas para a listagem de alunos

 /*
  * Listagem dos alunos
  */
 if ($sImprimeLista == "yes") {
 
   //$oPdf->setfillcolor(223);
 
   $sCamposLista  = "ed47_i_codigo, ed47_v_nome, ed47_v_sexo, ed11_c_descr, ed57_c_descr, ed15_c_nome, ed60_d_datamatricula,";
   $sCamposLista .= "case";
   $sCamposLista .= "     when ed60_d_datamatricula between '{$dDataInicial}' and '{$dDataLimite}'";
   $sCamposLista .= "          then 1";
   $sCamposLista .= "          else 0";
   $sCamposLista .= " end as novo,";
   $sCamposLista .= "case";
   $sCamposLista .= "     when ed60_c_situacao = 'MATRICULADO'";
   $sCamposLista .= "      and ed60_d_datasaida is null";
   $sCamposLista .= "          then 'M1'";
   $sCamposLista .= "     when (ed60_c_situacao = 'TRANSFERIDO REDE' or ed60_c_situacao = 'TRANSFERIDO FORA')";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposLista .= "          then 'M1'";
   $sCamposLista .= "     when (ed60_c_situacao = 'TRANSFERIDO REDE' or ed60_c_situacao = 'TRANSFERIDO FORA')";
   $sCamposLista .= "      and ed60_d_datasaida between '{$dDataInicial}' and '{$dDataLimite}'";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposLista .= "          then 'M2'";
   $sCamposLista .= "     when (ed60_c_situacao = 'EVADIDO' or ed60_c_situacao = 'CANCELADO' or ed60_c_situacao = 'FALECIDO')";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposLista .= "          then 'M1'";
   $sCamposLista .= "     when (ed60_c_situacao = 'EVADIDO' or ed60_c_situacao = 'CANCELADO' or ed60_c_situacao = 'FALECIDO')";
   $sCamposLista .= "      and ed60_d_datasaida between '{$dDataInicial}' and '{$dDataLimite}'";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposLista .= "          then 'M3'";
   $sCamposLista .= "     when (ed60_c_situacao = 'AVANÇADO' or ed60_c_situacao = 'CLASSIFICADO')";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimite}'";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposLista .= "          then 'M1'";
   $sCamposLista .= "     when (ed60_c_situacao = 'AVANÇADO' or ed60_c_situacao = 'CLASSIFICADO')";
   $sCamposLista .= "      and ed60_d_datasaida between '{$dDataInicial}' and '{$dDataLimite}'";
   $sCamposLista .= "      and ed60_d_datasaida > '{$dDataLimiteMov}'";
   $sCamposLista .= "          then 'M4'";
   $sCamposLista .= " end as situacao";
 
   $sWhereLista   = "     ed57_i_escola  = {$iEscola} ";
   $sWhereLista  .= " AND ed52_i_codigo  = {$iCalendario} ";
   $sWhereLista  .= " AND ed221_c_origem = 'S' ";
   $sWhereLista  .= " AND ed60_d_datamatricula <= '{$dDataLimite}' ";
   $sWhereLista  .= " {$iCondicaoEnsino} ";
   $sOrderLista   = " ed11_c_descr, ed15_i_sequencia, ed57_c_descr, ed47_v_nome ";
   $sSqlLista     = $oDaoMatricula->sql_query_boletimestat("", $sCamposLista, $sOrderLista, $sWhereLista);
   $rsLista       = $oDaoMatricula->sql_record($sSqlLista);
   $iLinhasLista  = $oDaoMatricula->numrows;
   $lTroca        = 1;
   $iConta        = 0;
  

  $cabecalhoAlunos = [
    'Sequência', 'Código Aluno', 'Nome', 'Sexo', 'Turma', 'Etapa', 'Turno', 'Data Matrícula'
  ];

  $cabecalhoAlunos = array_map(function($v) {
    return mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
}, $cabecalhoAlunos);


  $sheet->fromArray($cabecalhoAlunos, null, 'A' . $linhaAtual);
  $linhaAtual++;

  $iConta = 0; // Inicializa contador
  for ($iContLista = 0; $iContLista < $iLinhasLista; $iContLista++) {
      $oDadosMatricula = db_utils::fieldsmemory($rsLista, $iContLista);

      if ($oDadosMatricula->situacao == "M1") {
          $iConta++; // Incrementa contador

          $linhaAluno = [
              $iConta,
              $oDadosMatricula->ed47_i_codigo,
              $oDadosMatricula->ed47_v_nome,
              $oDadosMatricula->ed47_v_sexo,
              substr($oDadosMatricula->ed57_c_descr, 0, 15),
              $oDadosMatricula->ed11_c_descr,
              $oDadosMatricula->ed15_c_nome,
              db_formatar($oDadosMatricula->ed60_d_datamatricula, 'd')
          ];

          $linhaAluno = array_map(function($v) {
            return mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
        }, $linhaAluno);

          $sheet->fromArray($linhaAluno, null, 'A' . $linhaAtual);
          $linhaAtual++;
      }
  }

  // Total de alunos
  $sheet->setCellValue('A' . $linhaAtual, 'Total de alunos:');
  $sheet->setCellValue('B' . $linhaAtual, $iConta);
}


$linhaAtual = $linhaAtual + 2; 


//Relatório Raça/Cor

$oCalendario = CalendarioRepository::getCalendarioByCodigo($iCalendario);
$oEscola     = EscolaRepository::getEscolaByCodigo($iEscola);
$aEtapas     = array(0);

if ($iSerieEscolhida == 0) {

  $aEtapas   = array();
  $sCampos   = "distinct ed11_i_codigo ";
  $sWhere    = "     ed57_i_escola     = $iEscola ";
  $sWhere   .= " and ed57_i_calendario = $iCalendario ";

  $oDaoTurma = new cl_turma();
  $sSql      = $oDaoTurma->sql_query_turma(null, $sCampos, null, $sWhere);
  $rs        = db_query($sSql);

  if ($rs && pg_num_rows($rs) > 0) {

    $iLinhas = pg_num_rows($rs);
    for ($i=0; $i < $iLinhas ; $i++) {
      $aEtapas[] = db_utils::fieldsMemory($rs, $i)->ed11_i_codigo;
    }
  }
}

$oRelatorioXls = new RelatorioAlunosRacaCorXls($oCalendario, $aEtapas, $oEscola, $spreadsheet);
$oRelatorioXls->setLinhaInicial($linhaAtual);
$oRelatorioXls->gerarXls();


$writer = new Xls($spreadsheet);
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="boletim_estatistico.xls"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;