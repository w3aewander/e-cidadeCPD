<?
/*
 * E-cidade Software Publico para Gestao Municipal                
 * Copyright (C) 2009  DBSeller Servicos de Informatica             
 * www.dbseller.com.br                     
 * e-cidade@dbseller.com.br                   
 * * Este programa e software livre; voce pode redistribui-lo e/ou     
 * modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 * publicada pela Free Software Foundation; tanto a versao 2 da      
 * Licenca como (a seu criterio) qualquer versao mais nova.          
 * * Este programa e distribuido na expectativa de ser util, mas SEM   
 * QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 * COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 * PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 * detalhes.                                                         
 * * Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 * junto com este programa; se nao, escreva para a Free Software     
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 * 02111-1307, USA.                                                  
 * * Copia da licenca no diretorio licenca/licenca_en.txt 
 * licenca/licenca_pt.txt 
 */

include(modification("fpdf151/pdfwebseller.php"));
include(modification("libs/db_stdlibwebseller.php"));
include(modification("classes/db_matricula_classe.php"));
include(modification("classes/db_regencia_classe.php"));
include(modification("classes/db_escola_classe.php"));
include(modification("classes/db_procavaliacao_classe.php"));
include(modification("classes/db_regenciahorario_classe.php"));
include(modification("classes/db_regenciaperiodo_classe.php"));
include(modification("classes/db_periodocalendario_classe.php"));

$clmatricula         = new cl_matricula;
$clregencia          = new cl_regencia;
$clescola            = new cl_escola;
$clprocavaliacao     = new cl_procavaliacao;
$clregenciahorario   = new cl_regenciahorario;
$clregenciaperiodo   = new cl_regenciaperiodo;
$clperiodocalendario = new cl_periodocalendario;
$escola              = db_getsession("DB_coddepto");
$discglob            = false;
$sSqlRegencia        = $clregencia->sql_query("","*","ed59_i_ordenacao"," ed59_i_codigo in ($disciplinas)");
$sResultRegencia     = $clregencia->sql_record($sSqlRegencia);

if ($clregencia->numrows == 0) {
  echo "<table width='100%'>";
  echo " <tr>";
  echo "  <td align='center'>";
  echo "   <font color='#FF0000' face='arial'>";
  echo "    <b>Nenhuma matrícula para a turma selecionada<br>";
  echo "    <input type='button' value='Fechar' onclick='window.close()'></b>";
  echo "   </font>";
  echo "  </td>";
  echo " </tr>";
  echo "</table>";  
  exit;
}

function Abreviar($nome,$max) {
  if (strlen(trim($nome)) > $max) {
    $strinv   = strrev(trim($nome));
    $ultnome  = substr($strinv,0,strpos($strinv," "));
    $ultnome  = strrev($ultnome);
    $nome     = strrev($strinv);
    $prinome  = substr($nome,0,strpos($nome," "));
    $nomes    = strtok($nome, " ");
    $iniciais = "";
    
    while($nomes):
      if (($nomes == 'E') || ($nomes == 'DE') || ($nomes == 'DOS') ||
         ($nomes == 'DAS') || ($nomes == 'DA') || ($nomes == 'DO')) {
        $iniciais .= " ".$nomes;
        $nomes = strtok(" ");
      } else if (($nomes == $ultnome) || ($nomes == $prinome)) {
        $nome  = "";
        $nomes = strtok(" ");
      } else {
        $iniciais .= " ".$nomes[0].".";
        $nomes     = strtok(" ");
      }
    endwhile;
    $nome  =  $prinome;
    $nome .= $iniciais;
    $nome .= " ".$ultnome;
 }
 return trim($nome);
}

//Função para imprimir o cabeçalho do diário em qualquer página
function imprimirCabecalho($pdf, $dataperiodo, $larguracolunas, $larg, $informadiasletivos, $ed53_d_inicio, $ed53_d_fim, $ed52_c_aulasabado, $ed52_i_codigo, $array_meses, $larguraindiv, $avaliacao, $falta, $colunas, $n_dias, $head1, $head2, $head3, $head4, $head5, $head6, $head7) {
    
  $nomes_meses = [
      1 => 'JANEIRO', 2 => 'FEVEREIRO', 3 => 'MARÇO',
      4 => 'ABRIL',   5 => 'MAIO',      6 => 'JUNHO',
      7 => 'JULHO',   8 => 'AGOSTO',    9 => 'SETEMBRO',
      10 => 'OUTUBRO', 11 => 'NOVEMBRO', 12 => 'DEZEMBRO'
  ];

  $pdf->addpage('L');
  $pdf->setfont('arial','b',8);
  $pdf->cell(60+$larguracolunas+$larg,4,@$dataperiodo,0,1,"C",1);
  $pdf->cell(50,4,"",1,0,"C",0);
  $pdf->cell(10,4,"Mês >",1,0,"R",0);
  
  if ($informadiasletivos == "S") {
      $array_meses = DiasLetivos($ed53_d_inicio, $ed53_d_fim, $ed52_c_aulasabado, $ed52_i_codigo, 3);
      $pdf->setfont('arial','b',7);
      for ($r = 0; $r < count($array_meses); $r++) {
          $qtd_diasmes = explode(",",$array_meses[$r]);
          
          // ALTERADO: Busca o nome do mês no array
          $numero_mes = (int)$qtd_diasmes[0];
          $nome_mes = isset($nomes_meses[$numero_mes]) ? $nomes_meses[$numero_mes] : 'MÊS?';
          
          $iquebra = 0;      
          if ($r == (count($array_meses)-1) && ($avaliacao != "true" && $falta != "true")) {
              $iquebra = 1;	
          }
          // ALTERADO: Usa a variável $nome_mes
          $pdf->cell($larguraindiv*$qtd_diasmes[1],4,$nome_mes,1,$iquebra,"C",0);
      }
  } else {
      $quebras = ($avaliacao != "true" && $falta != "true") ? 1 : 0;
      $pdf->cell($larguracolunas,4,"",1,$quebras,"R",0);
  }

  $pdf->setfont('arial','b',8);
  if ($avaliacao == "true" && $falta == "false") { 
      $pdf->cell(20,4,"Avaliações",1,1,"R",0);
  }
  if ($falta == "true" && $avaliacao == "false") {
      $pdf->cell(25,4,"",1,1,"R",0);
  }
  if ($avaliacao == "true" && $falta == "true") {
      $pdf->cell(20,4,"Avaliações",1,0,"R",0);
      $pdf->cell(25,4,"",1,1,"R",0);
  }

  $pdf->cell(5,4,"N°",1,0,"C",0);
  $pdf->cell(45,4,"Nome do Aluno",1,0,"C",0);
  $pdf->cell(10,4,"Dia >",1,0,"R",0);

  if ($informadiasletivos == "S") {
      $n_dias = DiasLetivos($ed53_d_inicio, $ed53_d_fim, $ed52_c_aulasabado, $ed52_i_codigo, 2);
      $pdf->setfont('arial','b',6);
      for ($r = 0; $r < count($n_dias); $r++) {
          $iQuebra = ($r == (count($n_dias)-1) && ($avaliacao != "true" && $falta != "true")) ? 1 : 0;
          $umdia = explode("-",$n_dias[$r]);
          $pdf->cell($larguraindiv,4,$umdia[0],1,$iQuebra,"C",0);
      }
  } else {
      for ($r = 0; $r < $colunas; $r++) {
          $iQuebra = ($r == ($colunas-1) && ($avaliacao != "true" && $falta != "true")) ? 1 : 0;
          $pdf->cell($larguraindiv,4,"",1,$iQuebra,"C",0);
      }
  }

  $pdf->setfont('arial','b',8);
  if ($avaliacao == "true" && $falta == "false") {
      $pdf->cell(5,4,"",1,0,"C",0);
      $pdf->cell(5,4,"",1,0,"C",0);
      $pdf->cell(5,4,"",1,0,"C",0);
      $pdf->cell(5,4,"",1,1,"C",0);
  } 
  if ($avaliacao == "true" && $falta == "true") {
      $pdf->cell(5,4,"",1,0,"C",0);
      $pdf->cell(5,4,"",1,0,"C",0);
      $pdf->cell(5,4,"",1,0,"C",0);
      $pdf->cell(5,4,"",1,0,"C",0);
      $pdf->cell(5,4,"N°",1,0,"C",0);
      $pdf->cell(10,4,"",1,0,"C",0);
      $pdf->cell(10,4,"Ft",1,1,"C",0);
  }
  if ($falta == "true" && $avaliacao == "false") {
      $pdf->cell(5,4,"N°",1,0,"C",0);
      $pdf->cell(10,4,"",1,0,"C",0);
      $pdf->cell(10,4,"Ft",1,1,"C",0);
  }
}


$pdf = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$iLinhasRegencia = $clregencia->numrows;

for ($x = 0; $x < $iLinhasRegencia; $x++) {
	
  db_fieldsmemory($sResultRegencia,$x);
  $sSqlProcAval    = $clprocavaliacao->sql_query("","ed09_i_codigo,ed09_c_descr",""," ed41_i_codigo = $periodo");
  $sResultProcAval = $clprocavaliacao->sql_record($sSqlProcAval);
  db_fieldsmemory($sResultProcAval,0);
  
  $sCampos           = "ed52_i_codigo,ed52_c_aulasabado,ed53_d_inicio,ed53_d_fim";
  $sWhere            = " ed53_i_calendario = $ed57_i_calendario AND ed53_i_periodoavaliacao = $ed09_i_codigo";
  $sSqlPeriodoCal    = $clperiodocalendario->sql_query("",$sCampos,"",$sWhere);
  $sResultPeriodoCal = $clperiodocalendario->sql_record($sSqlPeriodoCal);
  db_fieldsmemory($sResultPeriodoCal,0);
  
  $dataperiodo            = $ed09_c_descr." - ".db_formatar($ed53_d_inicio,'d')." à ".db_formatar($ed53_d_fim,'d');
  $sCamposRegenciaHorario = "case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as regente";
  $sWhereRegHorario       = " ed58_i_regencia = $ed59_i_codigo and ed58_ativo is true  ";
  $sSqlRegenciaHorario    = $clregenciahorario->sql_query("",$sCamposRegenciaHorario,"",$sWhereRegHorario);
  $sResutlRegenciaHorario = $clregenciahorario->sql_record($sSqlRegenciaHorario);
  
  if ($clregenciahorario->numrows > 0) {
    db_fieldsmemory($sResutlRegenciaHorario,0);
  } else {
    $regente = "";
  }
  
  $sCamposRegPeriodo = " ed78_i_aulasdadas as aulas";
  $sWhereRegPeriodo  = " ed78_i_regencia = $ed59_i_codigo AND ed78_i_procavaliacao = $periodo";
  $sSqlRegPeriodo    = $clregenciaperiodo->sql_query("",$sCamposRegPeriodo,"",$sWhereRegPeriodo);
  $sResultRegPeriodo = $clregenciaperiodo->sql_record($sSqlRegPeriodo);
  
  if ($clregenciaperiodo->numrows > 0) {
    db_fieldsmemory($sResultRegPeriodo,0);
  } else {
    $aulas = "";
  }
  if ($informadiasletivos == "S") {
    $colunas = DiasLetivos($ed53_d_inicio,$ed53_d_fim,$ed52_c_aulasabado,$ed52_i_codigo,1);
  } else {
    $colunas = $qtdecolunas;
  }
  if ($avaliacao == "true" && $falta == "true") {
    $larguraindiv = round(175/$colunas,1);
  } else if ($avaliacao == "false" && $falta == "true") {
    $larguraindiv = round(190/$colunas,1);
  } else if ($avaliacao == "false" && $falta == "false") {
    $larguraindiv = round(219/$colunas,1);	
  } else if ($avaliacao == "true" && $falta == "false")  {
    $larguraindiv = round(195/$colunas,1);		
  }
  $larguracolunas = $colunas*$larguraindiv;
  $pdf->setfillcolor(235);
  $head1 = "DIÁRIO DE CLASSE";
  $head2 = "Curso: $ed29_i_codigo - $ed29_c_descr";
  $head3 = "Calendário: $ed52_c_descr  Etapa: $ed11_c_descr";
  $head4 = "Período: $ed09_c_descr  Turma: $ed57_c_descr";
  $head5 = "Regente: $regente";
  $head6 = "Aulas Dadas: $aulas";
  $head7 = "Disciplina: $ed232_c_descr";

  if ($avaliacao == "true" && $falta == "false") {
 	$larg = 20;
  } else if ($avaliacao == "false" && $falta == "true") {
 	$larg = 25; 	
  } else if ($avaliacao == "false" && $falta == "false") {
   	$larg = 0;
  } else {
  	$larg = 45;
  }
  

  imprimirCabecalho($pdf, $dataperiodo, $larguracolunas, $larg, $informadiasletivos, $ed53_d_inicio, $ed53_d_fim, $ed52_c_aulasabado, $ed52_i_codigo, null, $larguraindiv, $avaliacao, $falta, $colunas, null, $head1, $head2, $head3, $head4, $head5, $head6, $head7);
  
  $max_alunos_por_pagina = 33;
  $contador_alunos = 0;
  $total_alunos = 0;

  $cont                = 1;
  $cor1                = 0;
  $cor2                = 1;
  $cor                 = "";
  $Turma = TurmaRepository::getTurmaByCodigo($ed57_i_codigo);
  $alunosMatriculados = $Turma->getAlunosMatriculados();
  $total_alunos = count($alunosMatriculados);

  foreach ($alunosMatriculados as $oMatricula) {  
    
    $contador_alunos++; 

    if ($cor == $cor1) {
        $cor = $cor2;
    } else {
        $cor = $cor1;
    }
    $pdf->setfont('arial','b',5.8);
    $pdf->cell(5,4,$cont,1,0,"C",0);
    $pdf->cell(55,4,$oMatricula->getAluno()->getNome(),1,0,"L",0);
    $pdf->setfont('arial','b',8);
    
    for ($r = 0; $r < $colunas; $r++) {
        $pdf->setfont('arial','b',12);
        if ($r == ($colunas-1) && ($avaliacao != "true" && $falta != "true")) {
            $pdf->cell($larguraindiv,4,"",1,1,"C",0);
        } else {
            $pdf->cell($larguraindiv,4,"",1,0,"C",0);
        }
    }
    
    $pdf->setfont('arial','b',8);
    for ($r = 0; $r < 4; $r++) {
        if ($r == 3 && ($avaliacao == "true" && $falta != "true")) {
            $pdf->cell(5,4,"",1,1,"C",0);
        } else if ($avaliacao == "true" && $falta == "false") {
            $pdf->cell(5,4,"",1,0,"C",0);
        } else if ($avaliacao == "true" && $falta == "true") { 
            $pdf->cell(5,4,"",1,0,"C",0);
        }      
    }
    
    if ($falta == "true") {
        $pdf->cell(5,4,"",1,0,"C",0);
        $pdf->cell(10,4,"",1,0,"C",0);
        $pdf->cell(10,4,"",1,1,"C",0);
    } else {
        // Garante a quebra de linha se a seção 'falta' não for impressa
        if ($avaliacao != "true" && $falta != "true") {
            // já quebrou a linha no loop de colunas
        } else if ($avaliacao != "true") {
             $pdf->ln();
        }
    }
    
    $cont++;

    // Lógica para quebra de página
    // Se o número de alunos impressos for um múltiplo do limite E não for o último aluno da lista
    if (($contador_alunos % $max_alunos_por_pagina == 0) && ($contador_alunos < $total_alunos)) {
        // Imprime o rodapé da página atual
        $pdf->cell(($larguracolunas+60+$larg)/2,5,"Entregue em _____/_____/_____ POR_______________________",1,0,"L",0);
        $pdf->cell(($larguracolunas+60+$larg)/2,5,"Revisado em _____/_____/_____ POR_______________________",1,1,"L",0);
        $pdf->cell(($larguracolunas+60+$larg)/2,5,"Processado em _____/_____/_____ POR_____________________",1,0,"L",0);
        $pdf->cell(($larguracolunas+60+$larg)/2,5,"Assinatura do professor:_________________________________",1,1,"L",0);

        // Adiciona uma nova página com o cabeçalho
        imprimirCabecalho($pdf, $dataperiodo, $larguracolunas, $larg, $informadiasletivos, $ed53_d_inicio, $ed53_d_fim, $ed52_c_aulasabado, $ed52_i_codigo, null, $larguraindiv, $avaliacao, $falta, $colunas, null, $head1, $head2, $head3, $head4, $head5, $head6, $head7);
    }
  }

  $pdf->setfont('arial','b',8);
  if ($avaliacao == "true" && $falta == "false") {
 	  $larg = 20;
  } else if ($avaliacao == "false" && $falta == "true") {
   	$larg = 25; 	
  } else if ($avaliacao == "false" && $falta == "false") {
   	$larg = 0;
  } else {
   	$larg = 45;
  }
  
  $pdf->cell(($larguracolunas+60+$larg)/2,5,"Entregue em _____/_____/_____ POR_______________________",1,0,"L",0);
  $pdf->cell(($larguracolunas+60+$larg)/2,5,"Revisado em _____/_____/_____ POR_______________________",1,1,"L",0);
  $pdf->cell(($larguracolunas+60+$larg)/2,5,"Processado em _____/_____/_____ POR_____________________",1,0,"L",0);
  $pdf->cell(($larguracolunas+60+$larg)/2,5,"Assinatura do professor:_________________________________",1,1,"L",0);
}

$pdf->Output();
?>