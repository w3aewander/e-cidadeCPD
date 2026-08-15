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
require_once(modification("fpdf151/scpdf.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_libparagrafo.php"));

$clmatricula       = new cl_matricula;
$clturma           = new cl_turma;
$clregenteconselho = new cl_regenteconselho;
$cldiarioavaliacao = new cl_diarioavaliacao;
$clprocavaliacao   = new cl_procavaliacao;
$clpareceraval     = new cl_pareceraval;
$claprovconselho   = new cl_aprovconselho;
$clDBConfig        = new cl_db_config();
$clEscola          = new cl_escola();
$oDadosGrade       = new stdClass();

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaParada($periodo){
  $sql = pg_query("select ed09_c_descr as periodo, ed41_i_sequencia as seqatual from procavaliacao inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao inner join formaavaliacao on formaavaliacao.ed37_i_codigo = procavaliacao.ed41_i_formaavaliacao inner join procedimento on procedimento.ed40_i_codigo = procavaliacao.ed41_i_procedimento where ed41_i_codigo = {$periodo}");
  $resultado = pg_fetch_all($sql);
  return trim($resultado[0]["periodo"]);
}



function ajustaNome($nome){  
  $nomemin = strtolower($nome);
  $novonome = explode(" ", $nomemin);
  $nome = "";
  foreach ($novonome as $palavra){
    if(strlen($palavra) > 2){
      $nome .= ucfirst($palavra) . " ";
    }else{
      $nome .= $palavra . " ";
    }
  }
  return trim($nome);
}

$oDadosGrade->nLarguraGrade = 191;
$sSqlTurma = $clturma->sql_query_turmaserie( "", "ed57_i_codigo as turma", "", " ed220_i_codigo = {$turma}" );
$result00  = $clturma->sql_record( $sSqlTurma );
db_fieldsmemory( $result00, 0 );
$oTurma                    = TurmaRepository::getTurmaByCodigo($turma);

$sOrdenacaoMatricula = "ed60_i_numaluno, to_ascii(ed47_v_nome)";
$sWhereMatricula     = " ed60_i_codigo in ({$alunos}) AND ed60_i_turma = {$turma}";
$sSqlMatricula       = $clmatricula->sql_query( "", "*", $sOrdenacaoMatricula, $sWhereMatricula );
$result              = $clmatricula->sql_record( $sSqlMatricula );

if ($clmatricula->numrows == 0) {?>
  <table width='100%'>
   <tr>
    <td align='center'>
     <font color='#FF0000' face='arial'>
      <b>Nenhuma matrícula para a turma selecionada<br>
      <input type='button' value='Fechar' onclick='window.close()'></b>
     </font>
    </td>
   </tr>
  </table>
 <?php
  exit;
}

$sCamposProcAvaliacao = "ed09_c_descr as periodoselecionado, ed41_i_sequencia as seqatual";
$sSqlProcAvaliacao    = $clprocavaliacao->sql_query( "", $sCamposProcAvaliacao, "", "ed41_i_codigo = {$periodo}" );
$result_per           = $clprocavaliacao->sql_record( $sSqlProcAvaliacao );
db_fieldsmemory($result_per,0);

//Dados Instituição
$sCamposInstit   = "nomeinst as nome, ender, munic, uf, telef, email, url, logo";
$sSqlDadosInstit = $clDBConfig->sql_query_file( db_getsession('DB_instit'), $sCamposInstit );
$rsDadosInstit   = db_query($sSqlDadosInstit);
$oDadosInstit    = db_utils::fieldsMemory( $rsDadosInstit, 0 );



//Dados Escola 
$ruaescola =  str_repeat(" ", 500);
$cidadeescola =  str_repeat(" ", 500);
$url =  str_repeat(" ", 500);

$sSqlTelefoneEscola = $clEscola->sql_query_telefone( "", "ed26_i_numero,ed26_i_ddd", "", "ed26_i_escola = {$escola}" );
$rsTelefoneEscola   = db_query( $sSqlTelefoneEscola );
$oTelefoneEscola    = db_utils::fieldsMemory( $rsTelefoneEscola, 0 );
$iTelefoneEscola    = $oTelefoneEscola->ed26_i_numero;
$iDTelefone         = $oTelefoneEscola->ed26_i_ddd;
$DadosCabecalho = $sNomeEscola. " (".$iDTelefone.")" .$iTelefoneEscola;
$sCamposRegenteConselho = "cgmrh.z01_nome as conselheiro";
$sSqlRegenteConselho    = $clregenteconselho->sql_query( "", $sCamposRegenteConselho, "", "ed235_i_turma = {$turma}" );
$result6                = $clregenteconselho->sql_record( $sSqlRegenteConselho );

if ( $clregenteconselho->numrows > 0 ) {
  db_fieldsmemory( $result6, 0 );
} else {
  $conselheiro = "";
}

$periodoparada = buscaParada($periodo);



$pdf = new scpdf();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->ln(5);

if ( strlen($nome) > 42 || strlen($sNomeEscola) > 42 ) {
  $TamFonteNome = 8;
} else {
  $TamFonteNome = 9;
}

$y1         = 9;
$y2         = 14;
$y3         = 18;
$y4         = 22;
$y5         = 26;
$y6         = 30;
$y7         = 6;
$y8         = 6;
$y9         = 35;
$y10        = 33;
$y11        = 63;
$y12        = 3;
$y13        = 12;
$y14        = 35;
$m0         = 9;
$m1         = 14;
$m2         = 18;
$m3         = 22;
$m4         = 26;
$m5         = 30;
$m6         = 33;
$m7         = 3;
$m8         = 5;
$a          = 5;
$f          = 195;
$r          = 33;
$alturahead = $pdf->sety(6);
$contapagina = 0;
//$pdf->addpage('P');
$guarday = 0;
$guardapagina = 1;
$nomealunos = array();
$matriculaalunos = array();
$xxx = pg_fetch_all($result); 
//testa($xxx); die("Busca matrícula");
foreach ($xxx as $naluno) {
  array_push($nomealunos, $naluno["ed47_v_nome"]);
  array_push($matriculaalunos, $naluno["ed47_i_codigo"]);
}


for ($x = 0; $x < $clmatricula->numrows; $x++) {  
  
  
  if($contapagina % 2 == 0){
    $pdf->addpage('P');    
  }
  
  db_fieldsmemory( $result, $x );
  
  $head1    = "BOLETIM ESCOLAR";
  if($x % 2 == 0){
    $head2    = "Nome: {$nomealunos[$x]}";
    $head3    = "Matrícula: {$matriculaalunos[$x]}";
  }  
  $head5    = "Etapa: {$ed11_c_descr} Ano: {$ed52_i_ano}";
  $head6    = "Turma: {$ed57_c_descr}";
  $situacao = trim($ed60_c_concluida) == "S" &&
              trim($ed60_c_situacao) != 'AVANÇADO' &&
              trim($ed60_c_situacao) != 'CLASSIFICADO' ? "CONCLUÍDO" : Situacao( $ed60_c_situacao, $ed60_i_codigo );
  
  $margemesquerda = $pdf->lMargin;

  $pdf->setfillcolor(223);
  $pdf->SetFont( 'arial', 'b', 6 );
  $pdf->SetY($y8);
  
  
  $oLibDocumento = new libdocumento(5001,null);
  

  if ( $oLibDocumento->lErro ){
    db_redireciona("db_erros.php?fechar=true&db_erro={$oLibDocumento->sMsgErro}");
  }

  $aParagrafo = $oLibDocumento->getDocParagrafos();
  foreach ($aParagrafo as $oParagrafo ) {
    eval($oParagrafo->oParag->db02_texto);
  }
  $pdf->setleftmargin($margemesquerda);
  
  if ($grade == "yes") {    

    //$oGrade = new RelatorioGradeAproveitamento($pdf, MatriculaRepository::getMatriculaByCodigo($ed60_i_codigo), 191 );    
    $oGrade = new RelatorioGradeAproveitamentoM($pdf, MatriculaRepository::getMatriculaByCodigo($ed60_i_codigo), 191 );    

    if($contapagina  > 0 && $contapagina % 2 != 0 && $guardapagina == $pdf->PageNo()){
      if($ed29_c_descr == "ENSINO FUNDAMENTAL" && $ed11_c_descr == "2º ANO"){
        $head2    = "Nome: {$ed47_v_nome}";
        $head3    = "Matrícula: {$ed47_i_codigo}";
        $pdf->Hextra($pdf->getX(), $pdf->getY() + 101);
        $pdf->setY(174);
        if($assresp == "yes"){
          $pdf->Rect(10, 174, 192, 71);
        }else{
          $pdf->Rect(10, 174, 192, 48);
        } 
        //$pdf->Rect(10, 175, 192, 68);
      }elseif($ed29_c_descr == "ENSINO FUNDAMENTAL" && ($ed11_c_descr == "3º ANO" || $ed11_c_descr == "4º ANO" || $ed11_c_descr == "5º ANO")){
        $head2    = "Nome: {$ed47_v_nome}";
        $head3    = "Matrícula: {$ed47_i_codigo}";        
        $pdf->Hextra($pdf->getX(), $pdf->getY() + 99);        
        $pdf->setY(175);
        if($assresp == "yes"){
          $pdf->Rect(10, 175, 192, 70);
        }else{
          $pdf->Rect(10, 175, 192, 48);
        }
        //$pdf->Rect(10, 175, 192, 70);
      }elseif($ed29_c_descr == "ENSINO FUNDAMENTAL" && ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO")){                
        $head2    = "Nome: {$ed47_v_nome}";
        $head3    = "Matrícula: {$ed47_i_codigo}";
        $pdf->Hextra($pdf->getX(), $pdf->getY() + 97);        
        $pdf->setY(170);        
        if($assresp == "yes"){
          $pdf->Rect(10, 170, 192, 83);
        }else{
          $pdf->Rect(10, 170, 192, 63);
        }
        //$pdf->Rect(10, 170, 192, 83);
      }elseif($ed29_c_descr == "EDUCAÇÃO DE JOVENS E ADULTOS" && $ed11_c_descr == "3º CICLO"){
        $pdf->setY(120);
        $pdf->Rect(10, 120, 192, 80);
      }
    }/*elseif($contapagina  > 0 && $contapagina % 2 == 0 && $guardapagina == $pdf->PageNo()){
      if($ed29_c_descr == "ENSINO FUNDAMENTAL" && $ed11_c_descr == "2º ANO"){
        $pdf->setY(120);        
        $pdf->Rect(10, 120, 192, 65);
      }elseif($ed29_c_descr == "ENSINO FUNDAMENTAL" && ($ed11_c_descr == "3º ANO" || $ed11_c_descr == "4º ANO" || $ed11_c_descr == "5º ANO")){
        $pdf->setY(205);        
        $pdf->Rect(10, 205, 192, 65);
      }elseif($ed29_c_descr == "ENSINO FUNDAMENTAL" && ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO")){
        $pdf->setY(205);
        $pdf->Rect(10, 205, 192, 80);
        die("Entra aqui?");
      }elseif($ed29_c_descr == "EDUCAÇÃO DE JOVENS E ADULTOS" && $ed11_c_descr == "3º CICLO"){
        $pdf->setY(120);
        //$pdf->Rect(10, 120, 192, 80);        
      }
    }*/
    //var_dump($pdf->getY()); echo "<br>";

    $oGrade->montarGrade($ed29_c_descr, $ed11_c_descr, $periodoparada);
    $guardapagina = $pdf->PageNo();
    //$oGrade->montarGrade();
    //$oGrade->imprimirMinimoParaAprovacao();
    //$oGrade->imprimeResultadoFinal();
  }


  $sWhere              = " ed95_i_aluno = {$ed60_i_aluno} AND ed59_i_turma = {$turma} AND ed72_i_procavaliacao = {$periodo}";
  $sSqlDiarioAvaliacao = $cldiarioavaliacao->sql_query( "", "ed232_c_descr, ed72_t_obs", "", $sWhere );
  $result_obs           = $cldiarioavaliacao->sql_record( $sSqlDiarioAvaliacao );

  $sWhereDiarioRegra = " ed95_i_aluno = {$ed60_i_aluno} AND ed59_i_turma = {$turma} ";
  $oDaoDiarioRegra   = new cl_diarioregracalculo();
  $sSqlDiarioRegra   = $oDaoDiarioRegra->sql_query(null, "ed125_diario", null, $sWhereDiarioRegra);
  $rsDiarioRegra     = $oDaoDiarioRegra->sql_record($sSqlDiarioRegra);

  $sObsProporcionalidadeAluno = "";
  if ( $oDaoDiarioRegra->numrows > 0 ) {
    $sObsProporcionalidadeAluno = $sParagrafoProporcionalidade;
  }

  $ed72_t_obs   = "";
  $aObservacoes = array();

  for( $iContador = 0; $iContador < pg_num_rows( $result_obs ); $iContador++ ) {

    $oDadosObservacao = db_utils::fieldsMemory( $result_obs, $iContador );

    if( !empty( $oDadosObservacao->ed72_t_obs ) ) {
      $aObservacoes[] = "{$oDadosObservacao->ed232_c_descr}: {$oDadosObservacao->ed72_t_obs}";
    }
  }

  $ed72_t_obs = implode( ". ", $aObservacoes );

  $impresso = false;
  if ($padrao == "yes") {

    $sWhere          = " ed95_i_aluno = {$ed60_i_aluno} AND ed72_i_procavaliacao = {$periodo} AND ed59_i_turma = {$turma}";
    $sSqlParecerAval = $clpareceraval->sql_query( "", "ed93_t_parecer", "ed93_i_codigo", $sWhere );
    $result_par      =  $clpareceraval->sql_record( $sSqlParecerAval );

    if ($clpareceraval->numrows > 0) {

      $pdf->setfont( 'arial', 'b', 7 );
      $pdf->cell( 195, 4, "Parecer {$periodoselecionado}:", 1, 1, "L", 1 );
      $pdf->setfont( 'arial', '', 7 );

      if ($padraotipo == "L") {
        $pdf->cell( 195, 4, "Seq - Parecer => Legenda", 1, 1, "L", 0 );
      }

      $seq       = "";
      $sep       = "";
      $parpadrao = "";
      $seppadrao = "";

      for ($g = 0; $g < $clpareceraval->numrows; $g++) {

        db_fieldsmemory( $result_par,$g );
        if( !strstr( $seq, "#" . $ed93_t_parecer . "#" ) ) {

          $parpadrao .= $seppadrao . $ed93_t_parecer;
          $seq       .= $sep . "#" . $ed93_t_parecer . "#";
          $sep        = ",";
          $seppadrao  = "    ";

          if ($padraotipo == "L") {

            $explode_parecer = explode( "**", $ed93_t_parecer );

            for ($b = 0; $b < count($explode_parecer); $b++) {
              $pdf->cell( $oDadosGrade->nLarguraGrade, 4, trim( $explode_parecer[$b] ), 1, 1, "L", 0 );
            }
          }
        }
      }

      if ($padraotipo == "C") {
        $pdf->multicell( $oDadosGrade->nLarguraGrade, 4, str_replace( "**", "  ", $parpadrao ), 1, "L", 0, 0 );
      }

      $impresso = true;
    }
  }

  if ($descritivo == "yes") {

    $sWhere          = " ed95_i_aluno = {$ed60_i_aluno} AND ed59_i_turma = {$turma} ";
    $sWhere         .= "AND ed72_i_procavaliacao = {$periodo} AND ed72_t_parecer != ''";

    $sSqlDiarioAvaliacao = $cldiarioavaliacao->sql_query( "", "DISTINCT ed72_t_parecer as pardescr", "", $sWhere );
    $result_pardescr     = $cldiarioavaliacao->sql_record( $sSqlDiarioAvaliacao );

    if ($cldiarioavaliacao->numrows > 0) {

      $pardescr = trim( pg_result( $result_pardescr, 0, 'pardescr' ) );

      if ($pardescr != "") {

        $pdf->setfont( 'arial', 'b', 7 );
        $pdf->cell( $oDadosGrade->nLarguraGrade, 4, "Parecer {$periodoselecionado}:", 1, 1, "L", 1 );
        $pdf->setfont( 'arial', '', 7 );

        for($g = 0; $g < $cldiarioavaliacao->numrows; $g++) {

          db_fieldsmemory( $result_pardescr, $g );
          $pdf->multicell( $oDadosGrade->nLarguraGrade, 4, $pardescr, 1, "L", 0, 0 );
        }

        $impresso = true;
      }
    }
  }

  $obs2 = "";

  if( isset( $boletim_convencao ) ) {

 	  if( trim( $boletim_convencao ) != "" ) {

 	    $arr_convencao = str_replace( "{", "", $boletim_convencao );
 	    $arr_convencao = str_replace( "}", "", $arr_convencao );
 	    $troca         = chr(34) . "," . chr(34);
 	    $arr_convencao = str_replace( $troca, " - ", $arr_convencao );
 	    $arr_convencao = str_replace( chr(34), "", $arr_convencao );
 	    $obs2          = $arr_convencao;
 	  }
  }

  $obs3         = "";
  $sCampos      = "cgmrh.z01_nome, ed253_i_data, ed232_c_descr as disc_conselho, ed253_t_obs, ed59_i_ordenacao, ed52_i_ano,";
  $sCampos     .= " ed122_sequencial, ed122_descricao, ed11_c_descr as etapa, ed253_alterarnotafinal,  ed253_avaliacaoconselho";
  $sWhere       = " ed95_i_aluno = {$ed60_i_aluno} AND ed59_i_turma = {$turma} AND ed59_i_serie = {$ed221_i_serie}";

  $sSqlAprovConselho = $claprovconselho->sql_query( "", $sCampos, "ed59_i_ordenacao", $sWhere );
  $result_cons       = @$claprovconselho->sql_record( $sSqlAprovConselho );

  $aAprovadoBaixaFrequencia   = array();
  $aAprovadoConselhoRegimento = array();
  $sObservacaoConselho        = '';

  if ($claprovconselho->numrows > 0) {

    for ($g = 0; $g < $claprovconselho->numrows; $g++) {

      db_fieldsmemory( $result_cons, $g );
      $oDadosAprovConselho = db_utils::fieldsMemory( $result_cons, $g );

      switch ($oDadosAprovConselho->ed122_sequencial) {

        /**
        * Valida se a aprovação foi por conselho
        */
        case 1:          
          $oDocumento                = new libdocumento( 5013 );
          $oDocumento->disciplina    = $oDadosAprovConselho->disc_conselho;
          $oDocumento->etapa         = $oDadosAprovConselho->etapa;
          $oDocumento->justificativa = $oDadosAprovConselho->ed253_t_obs;
          $oDocumento->nota          = $oDadosAprovConselho->ed253_avaliacaoconselho;
          $oDocumento->anomatricula  = $oDadosAprovConselho->ed52_i_ano;

          $oDadosObservacao              = new stdClass();
          $oDadosObservacao->aParagrafos = $oDocumento->getDocParagrafos();

          if( trim( $oDadosObservacao->aParagrafos[1]->oParag->db02_texto ) ) {
            $aAprovadoConselhoRegimento[]  = "- {$oDadosObservacao->aParagrafos[1]->oParag->db02_texto}";
          }

          break;

        /**
        * Valida se a aprovação não foi por baixa frequência
        */
        case 2:
        
          $sHashSerieAno = $oDadosAprovConselho->etapa.$oDadosAprovConselho->ed52_i_ano;
          if ( !isset( $aAprovadoBaixaFrequencia[$sHashSerieAno] ) ) {
            $aAprovadoBaixaFrequencia[$sHashSerieAno] = $oDadosAprovConselho;
          }
          
          break;

        /**
        * Valida se a aprovação foi por regimento escolar
        */
        case 3;        
          $sObservacao = "- Disciplina $disc_conselho: Aprovado conforme regimento escolar. Justificativa: $ed253_t_obs";
          $aAprovadoConselhoRegimento[] = $sObservacao;

          break;
      }
    }

    if ( count( $aAprovadoBaixaFrequencia ) > 0 ) {

      $oDocumento = new libdocumento( 5006 );
      foreach ( $aAprovadoBaixaFrequencia as $oBaixaFrequencia ) {

        $oDocumento->nome_aluno = $ed47_v_nome;
        $oDocumento->ano        = $oBaixaFrequencia->ed52_i_ano;
        $oDocumento->nome_etapa = $oBaixaFrequencia->etapa;
        $aParagrafos            = $oDocumento->getDocParagrafos();

        if ( isset( $aParagrafos[1] ) ) {
          $sObservacaoConselho .= "- {$aParagrafos[1]->oParag->db02_texto}\n";
        }
      }
    }
  }

  $sObservacaoConselho .= implode( "\n", $aAprovadoConselhoRegimento );



  if (   trim( $ed60_t_obs ) != ""
      || trim( $ed72_t_obs ) != ""
      || trim( str_replace( chr(92), "", $obs1 ) ) != ""
      || trim( $obs2 ) != ""
      || trim( $sObservacaoConselho ) != ""
      || trim ($sObsProporcionalidadeAluno) != '') {

    $pdf->setfont( 'arial', 'b', 7 );
    $pdf->cell( $oDadosGrade->nLarguraGrade, 4, "Observações / Mensagens", 1, 1, "L", 1 );
    $pdf->setfont( 'arial', '', 7 );

    $ed60_t_obs = substr( $ed60_t_obs,          0, 270 );
    $ed72_t_obs = substr( $ed72_t_obs,          0, 270 );
    $obs2       = substr( $obs2,                0, 350 );
    $obs3       = substr( $sObservacaoConselho, 0, 610 );
    $obs4       = substr( $sObsProporcionalidadeAluno, 0, 150 );


    $pdf->multicell( $oDadosGrade->nLarguraGrade, 4, ( $ed60_t_obs != "" ? $ed60_t_obs . "\n" : "" ).
                          ( $ed72_t_obs != "" ? $ed72_t_obs . "\n" : "" ).
                          ( str_replace( chr(92) , "", $obs1 ) != "" ? str_replace( chr(92), "", $obs1 ) . "\n" : "" ).
                          ( $obs2 != "" ? $obs2 . "\n" : "" ) .
                          ( $obs3 != "" ? $obs3 . "\n" : "" ) .
                          ( $obs4 != "" ? $obs4 . "\n" : "" ) , 1, "L", 0, 0 );
    $impresso = true;
  }

  $pdf->cell( $oDadosGrade->nLarguraGrade, 5, "Volta Redonda " . date("d/m/Y"), 0,  1, "C", 0 );

  /*if ($impresso == false) {
    $sTexto  = "TF - Total Faltas | " . ( trim( $ed57_c_medfreq ) == "PERÍODOS" ? "AD - Aulas Dadas" : "DL - Dias Letivos");
    $sTexto .= " | FA - Faltas Abonadas";
    $pdf->cell( $oDadosGrade->nLarguraGrade, 4, $sTexto, 1, 1, "L", 0 );
  } else {
    $sTexto  = "TF - Total Faltas | " . ( trim( $ed57_c_medfreq ) == "PERÍODOS" ? "AD - Aulas Dadas" : "DL - Dias Letivos");
    $sTexto .= " | FA - Faltas Abonadas";
    $pdf->cell( $oDadosGrade->nLarguraGrade, 4, $sTexto, "LRB", 1, "L", 0 );
  }*/

  if ($assinaturaregente == "S") {
    $pdf->cell( $oDadosGrade->nLarguraGrade, 7, "__________________________________________________", 0,  1, "C", 0 );    
    $pdf->cell( $oDadosGrade->nLarguraGrade, 3, "Professor(a) " . ajustaNome($conselheiro), 0, 1, "C", 0 );

  } else {
    //$pdf->cell( $oDadosGrade->nLarguraGrade, 7, "", "LR",  1, "C", 0 );
    //$pdf->cell( $oDadosGrade->nLarguraGrade, 3, "", "LBR", 1, "C", 0 );
  }

  //$pdf->ln(3);
  if($assresp == "yes"){
    $pdf->ln(3);
    $pdf->cell( $oDadosGrade->nLarguraGrade, 7, "__________________________________________________", 0,  1, "C", 0 );  
    $pdf->cell( $oDadosGrade->nLarguraGrade, 3, "Assinatura do Responsável",              0, 1, "C", 0 );
    //$ed47_v_nome = "Nome Grande do Nome Junior Neto da Silva Filho";
    $pdf->ln(2);
    $pdf->cell( $oDadosGrade->nLarguraGrade, 7, "     Eu, ________________________________________ responsável pelo aluno {$ed47_v_nome} recebi o boletim do ___ trimestre/_____. ", 0,  1, "C", 0 );  
    //$pdf->cell( $oDadosGrade->nLarguraGrade, 7, "", 0,  1, "L", 0 );  
  }

   

  
  
  $contapagina++;
  
}//fim do for
//$pdf->addpage('P');

//die("Testa");
$pdf->Output();
