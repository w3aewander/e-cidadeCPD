<?


require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("dbforms/db_funcoes.php");


function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}


function buscaDados($codigo){
  $sql = pg_query("SELECT pc31_orcamforne as codigo, z01_nome as nome, l17_situacao as situacao, l17_tipo as tipo, l17_motivo as motivo, z01_cgccpf as cpf from pcorcamfornelic inner join pcorcamforne on pcorcamforne.pc21_orcamforne = pcorcamfornelic.pc31_orcamforne inner join liclicitatipoempresa on liclicitatipoempresa.l32_sequencial = pcorcamfornelic.pc31_liclicitatipoempresa inner join cgm on cgm.z01_numcgm = pcorcamforne.pc21_numcgm inner join pcorcam on pcorcam.pc20_codorc = pcorcamforne.pc21_codorc left join pcorcamfornelichabilitacao on l17_pcorcamfornelic = pc31_orcamforne where pc21_codorc in (select pc20_codorc from pcorcamitemlic inner join pcorcamitem on pcorcamitem.pc22_orcamitem = pcorcamitemlic.pc26_orcamitem inner join liclicitem on liclicitem.l21_codigo = pcorcamitemlic.pc26_liclicitem inner join pcorcam on pcorcam.pc20_codorc = pcorcamitem.pc22_codorc inner join pcprocitem on pcprocitem.pc81_codprocitem = liclicitem.l21_codpcprocitem inner join liclicita on liclicita.l20_codigo = liclicitem.l21_codliclicita where l20_codigo = {$codigo})");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

$dados = buscaDados($_GET["codigo"]);

//testa($dados); die("Confere");

  
  header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
  header("Content-type:   application/x-msexcel; charset=utf-8");
  header("Content-Disposition: attachment; filename=ImportacaoParticipantes.xls");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private",false);

  print "<table>";  
  print "<tr>";
  print "<td>CPF / CNPJ</td>";
  print "<td>Nome / Razão Social</td>";
  print "<td>Nome Represetante Empresa</td>";
  print "<td>CPF Representante Empresa</td>";  
  print "<td>Tipo Participante</td>";
  print "<td>Habilitado</td>";
  print "<td>Motivo Inabilitação</td>";  
  print '</tr>';

  foreach ($dados as $linha){      
    $situacao = ($linha["situacao"] == 1) ? "Sim" : "Não";    
    $tipo = ($linha["tipo"] == 1) ? "Participante Comum" : "Consórcio";
    
    print "<tr>";
      //print "<td>" . $linha["cpf"] . "</td>";
    if(substr($linha["cpf"], 0, 1) == "0"){
      print "<td>'" . $linha["cpf"] . "</td>"; 
    } else {
      print "<td>" . $linha["cpf"] . "</td>";
    }
      print "<td>" . $linha["nome"] . "</td>";
      print "<td> </td>";
      print "<td> </td>";      
      print "<td>" . $tipo . "</td>";
      print "<td>" . $situacao . "</td>";
      print "<td>" . $linha["motivo"] . "</td>";
    print "</tr>";
    
  }
  
