<?php
 
  require_once("libs/db_stdlib.php");
  require_once("libs/db_utils.php");
  require_once("libs/db_app.utils.php");
  require_once("libs/db_conecta.php");
  require_once("libs/db_sessoes.php");
  require_once("dbforms/db_funcoes.php");

  function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}


  
function verificaEstoque($cod){
  $sql = pg_query("SELECT distinct m70_coddepto,descrdepto,m70_quant, round((m70_quant*3.50000),2)::float as m70_valor, coalesce((select sum(coalesce(case when m81_tipo = 4 then m82_quant end, 0)) as saida from matestoqueinimei inner join matestoqueitem on m71_codlanc = m82_matestoqueitem inner join matestoque trans on m71_codmatestoque = trans.m70_codigo inner join matestoqueini on m80_codigo = m82_matestoqueini left join matestoqueinil on m80_codigo = m86_matestoqueini inner join matestoquetipo on m80_codtipo = m81_codtipo where trans.m70_codigo = matestoque.m70_codigo and m81_codtipo = 7 and m86_matestoqueini IS NULL),0) as dl_transferencias from matestoque inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join db_departorg on db_departorg.db01_coddepto = db_depart.coddepto and db_departorg.db01_anousu = 2020 inner join orcunidade on orcunidade.o41_orgao = db_departorg.db01_orgao and orcunidade.o41_unidade = db_departorg.db01_unidade and orcunidade.o41_anousu = db_departorg.db01_anousu and orcunidade.o41_instit = 45 inner join orcorgao on orcorgao.o40_orgao = orcunidade.o41_orgao and orcorgao.o40_anousu = orcunidade.o41_anousu where m70_codmatmater= {$cod}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaValorUnitario($cod){
  $sql = pg_query("SELECT m80_codigo, case when m81_tipo = 2 then 0 when m81_tipo = 1 then round(m89_valorunitario, 5)::numeric end as m89_valorunitario, m82_quant, round(m89_precomedio, 5)::numeric as fc_calculapm , descrdepto, m80_data, m80_hora, m80_codtipo, (select db_depart.descrdepto from matestoqueinimei inner join matestoqueitem on matestoqueitem.m71_codlanc = matestoqueinimei.m82_matestoqueitem inner join matestoqueinimeiari on matestoqueinimeiari.m49_codmatestoqueinimei = matestoqueinimei.m82_codigo inner join atendrequiitem on atendrequiitem.m43_codigo = matestoqueinimeiari.m49_codatendrequiitem inner join matrequiitem on matrequiitem.m41_codigo = atendrequiitem.m43_codmatrequiitem inner join matrequi on matrequi.m40_codigo = matrequiitem.m41_codmatrequi inner join db_depart on db_depart.coddepto = matrequi.m40_depto where matestoqueinimei.m82_matestoqueini = matestoqueini.m80_codigo limit 1) as coddepto_destino FROM matestoqueini inner join matestoquetipo on m80_codtipo = m81_codtipo inner join matestoqueinimei on m82_matestoqueini = m80_codigo inner join db_usuarios on m80_login = id_usuario inner join db_depart on m80_coddepto = coddepto inner join matestoqueitem on m82_matestoqueitem = m71_codlanc inner join matestoque on m71_codmatestoque = m70_codigo inner join matmater on m60_codmater = m70_codmatmater left join matestoqueitemoc on m71_codlanc = m73_codmatestoqueitem and m73_cancelado is false left join matordemitem on m52_codlanc = m73_codmatordemitem left join matestoqueinill on m87_matestoqueini = m80_codigo left join matestoqueinil on m86_codigo = m87_matestoqueinil left join matestoqueinimeipm on m82_codigo = m89_matestoqueinimei where m81_entrada = 't' and m70_codmatmater = {$cod} and instit = 45 and m71_servico is false order by m80_data desc, m80_hora desc LIMIT 1");  
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
  //return $resultado[0]["m89_valorunitario"];
}

function retornaUltimoCodigo($codmaterial){
  $sql = pg_query("SELECT max(m63_codpcmater)from transmater inner join pcmater on pcmater.pc01_codmater = transmater.m63_codpcmater inner join matmater on matmater.m60_codmater = transmater.m63_codmatmater where m63_codmatmater={$codmaterial}");  
  $resultado = pg_fetch_all($sql);  
  return $resultado[0]["max"];
}

function buscaData($m70codigo){
  $sql = pg_query("SELECT m71_data FROM matestoqueitem WHERE m71_codmatestoque = {$m70codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["m71_data"];
}

function verificaOutraCoisa($codmatestoque){
  $sql = pg_query("SELECT * from matestoqueitem inner join matestoque on matestoque.m70_codigo = matestoqueitem.m71_codmatestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater left join matestoqueitemlote on m71_codlanc = m77_matestoqueitem left join matestoqueitemfabric on m71_codlanc = m78_matestoqueitem left join matfabricante on m78_matfabricante = m76_sequencial left join cgm on m76_numcgm = z01_numcgm where m71_codmatestoque={$codmatestoque} and 1=1 ");
    $resultado = pg_fetch_all($sql);
    return $resultado;

}


$hoje = date("Y-m-d");
$ontem = date('Y-m-d',strtotime("-1 days"));

if($_POST["gerar"]){
  
  $denviada = new DateTime($_POST["datagerar"]);
  $dhoje = new DateTime($hoje);
  $dontem = new DateTime($ontem);
  

  if(($denviada == $dhoje) || ($denviada == $dontem)){    
    //Apenas ativo
    //$sqlTudo = pg_query("SELECT distinct m70_codigo, m70_codmatmater, trim(m60_descr) as m60_descr, m61_usaquant, m61_usadec, m70_coddepto, descrdepto, m70_valor, m70_quant, m60_ativo from matestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid left join transmater on transmater.m63_codmatmater = matmater.m60_codmater left join pcmater on pcmater.pc01_codmater = transmater.m63_codpcmater inner join matmatermaterialestoquegrupo on m68_matmater = m60_codmater inner join materialestoquegrupo on m68_materialestoquegrupo = m65_sequencial inner join db_estruturavalor on m65_db_estruturavalor = db121_sequencial inner join matparam on db121_db_estrutura = m90_db_estrutura where db_depart.instit in (45) and m70_coddepto in (1270) and m60_ativo = 't' and (pc01_servico is false or pc01_servico is null) order by trim(m60_descr)");

    $sqlTudo = pg_query("SELECT distinct m70_codigo, m70_codmatmater, trim(m60_descr) as m60_descr, m61_usaquant, m61_usadec, m70_coddepto, descrdepto, m70_valor, m70_quant, m60_ativo, m61_descr from matestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid left join transmater on transmater.m63_codmatmater = matmater.m60_codmater left join pcmater on pcmater.pc01_codmater = transmater.m63_codpcmater inner join matmatermaterialestoquegrupo on m68_matmater = m60_codmater inner join materialestoquegrupo on m68_materialestoquegrupo = m65_sequencial inner join db_estruturavalor on m65_db_estruturavalor = db121_sequencial inner join matparam on db121_db_estrutura = m90_db_estrutura where db_depart.instit in (45) and m70_coddepto in (1270) and (pc01_servico is false or pc01_servico is null) order by trim(m60_descr)");
      $dados = pg_fetch_all($sqlTudo);


      $ajustado = array();
      

      if($_POST["formato"] == "csv"){

        foreach ($dados as $linha) {
    
          if($linha["m70_quant"] > 0){
            $novosprecos = buscaValorUnitario($linha["m70_codmatmater"]);
            $novovalor = $novosprecos["m89_valorunitario"];
            $novomedio = $novosprecos["fc_calculapm"];
            $novadata = buscaData($linha["m70_codigo"]);

            $linha["m70_valor"] = number_format($novovalor, 2, ',', '');
            $linha["m85_precomedio"] = number_format($novomedio, 2, ',', '');
            $linha["m60_ativo"] = ($linha["m60_ativo"] == "t" ? "S" : "N");
            $linha["m71_data"] = implode("/", array_reverse(explode("-", $novadata)));
            //$linha["m70_quant"] = $verifica[0]["m70_quant"];

            $novocodigo = retornaUltimoCodigo($linha["m70_codmatmater"]);
            //Tratamento dos campos
            $linha["m70_codmatmater"] = str_pad($linha["m70_codmatmater"], 10, " ", STR_PAD_LEFT);
            $linha["m63_codpcmater"] = str_pad($novocodigo, 10, " ", STR_PAD_LEFT);
            //$linha["m63_codpcmater"] = str_pad($linha["m63_codpcmater"], 10, " ", STR_PAD_LEFT);

            $linha["m60_descr"] = str_pad($linha["m60_descr"], 80);
            //$linha["m60_descr"] = str_pad(utf8_encode($linha["m60_descr"]), 80);


            $linha["m70_valor"] = str_pad($linha["m70_valor"], 10, " ", STR_PAD_LEFT);
            $linha["m85_precomedio"] = str_pad($linha["m85_precomedio"], 10, " ", STR_PAD_LEFT);
            $linha["m70_quant"] = str_pad(str_replace(".", ",", $linha["m70_quant"]), 9, " ", STR_PAD_LEFT);

            $linha["m61_descr"] = str_pad($linha["m61_descr"], 10, " ", STR_PAD_RIGHT);

            //array_push($ajustado, $linha["m70_codmatmater"] . "\t" . $linha["m63_codpcmater"] . "\t\t" . $linha["m60_descr"] . "\t" . $linha["m70_valor"] . "\t\t" . $linha["m85_precomedio"] . "\t" . $linha["m70_quant"] . "\t" . $linha["m60_ativo"] . "\t" . $linha["m71_data"]);
            array_push($ajustado, $linha["m70_codmatmater"] . $linha["m63_codpcmater"] . $linha["m60_descr"] . $linha["m70_valor"] . $linha["m85_precomedio"] . $linha["m70_quant"] . $linha["m60_ativo"] . $linha["m71_data"] . $linha["m61_descr"]);
          }
        }//Fim do foreach
        //testa($ajustado); die("Confere linha");
        $arquivo = fopen("tmp/almoxarifado.txt", "w");
        //fwrite($arquivo, "Cod_Material\tCod_Compras\t\tDescrição\t\t\t\t\t\t\t\t\t\t\tValor_U\t\tValor_M\t\tEstoque\t\tAtivo\tData_Cad" . "\r\n");
        foreach ($ajustado as $linhaA) {
          fwrite($arquivo, $linhaA . "\r\n");
        }
        fclose($arquivo);

        $filePath = 'tmp/almoxarifado.txt';
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);

        header("Cache-Control: private");
        header("Content-Type: application/stream");
        header("Content-Length: ".$fileSize);
        header("Content-Disposition: attachment; filename=".$fileName);        
        readfile ($filePath);                   
        exit();
      }//If csv

      if($_POST["formato"] == "xls"){
        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-type:   application/x-msexcel; charset=utf-8");
        header("Content-Disposition: attachment; filename=almoxarifado.xls");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);

        print "<table>";
        print "<thead>";
        print "<tr>";
          print "<th>Cod_Material</th>";
          print "<th>Cod_Compras</th>";
          print "<th colspan='3'>Descrição</th>";
          print "<th>Valor_U</th>";
          print "<th>Valor_M</th>";
          print "<th>Estoque</th>";
          print "<th>Ativo</th>";
          print "<th>Data_Cad</th>";
          print "<th>Unidade</th>";
        print '</tr>';

        foreach ($dados as $linha) {
          if($linha["m70_quant"] > 0){
      
            $novosprecos = $novovalor = buscaValorUnitario($linha["m70_codmatmater"]);
            $novovalor = $novosprecos["m89_valorunitario"];
            $novomedio = $novosprecos["fc_calculapm"];
            $novadata = buscaData($linha["m70_codigo"]);                

            $linha["m70_valor"] = number_format($novovalor, 2, '.', '');
            $linha["m85_precomedio"] = number_format($novomedio, 2, '.', '');
            $linha["m60_ativo"] = ($linha["m60_ativo"] == "t" ? "S" : "N");
            $linha["m71_data"] = implode("/", array_reverse(explode("-", $novadata)));

            $novocodigo = retornaUltimoCodigo($linha["m70_codmatmater"]);
            $linha["m63_codpcmater"] = $novocodigo;

            print "<tr>";
            print "<td>" . $linha["m70_codmatmater"] . "</td>";
            print "<td>" . $linha["m63_codpcmater"] . "</td>";
            print "<td colspan='3'>" . $linha["m60_descr"] . "</td>";
            print "<td>" . $linha["m70_valor"] . "</td>";
            print "<td>" . $linha["m85_precomedio"] . "</td>";
            print "<td>" . $linha["m70_quant"] . "</td>";
            print "<td>" . $linha["m60_ativo"] . "</td>";
            print "<td>" . $linha["m71_data"] . "</td>";
            print "<td>" . $linha["m61_descr"] . "</td>";
            print "</tr>";
          }       
        }//Fim do foreach
        exit();
      }//if xls

  } else {
    echo "<script>alert('Data incorreta. Maior que a atual.');</script>";    
  }
  
}




?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?php
      db_app::load("scripts.js, strings.js, prototype.js, estilos.css");
    ?>
  </head>
  <body style="background-color: #ccc; margin-top: 30px">

    <div id="div_container" style="width: 300px; margin: auto;">
      <form method="post" action="">
      <fieldset>
        <legend style="font-weight: bold;">Geração de Arquivo Diário de Estoque</legend>

        <table>          
          <tr>
            <td style="font-weight: bold;">
              Data da Geração
            </td>
            <td>
              <input type="date" name="datagerar" value="<?=$hoje?>">              
            </td>
            <tr>
              <td>
                <input type="radio" name="formato" value="csv" checked>TXT
                <input type="radio" name="formato" value="xls">XLS
              </td>
            </tr>
          </tr>
        </table>
      </fieldset>

      

      <p align="center">
        <input type="submit" name="gerar" value="Processar">
      </p>
      <p><h5 style="text-align: center">A geração do arquivo pode demorar alguns minutos.</h5></p>
    </form>
    </div>






    <div id="div_container" style="width: 300px; margin: auto;">
      <form method="post" action="mat4_geraarquivo2.php">
      <fieldset>
        <legend style="font-weight: bold;">(NOVO)Geração de Arquivo Diário de Estoque com Estoque Zerado</legend>

        <table>          
          <tr>
            <td style="font-weight: bold;">
              A partir de:
            </td>
            <td>
              <input type="date" name="datagerar" value="<?=$hoje?>">              
            </td>
            <tr>
              <td>
                <input type="radio" name="formato" value="csv" checked>TXT
                <input type="radio" name="formato" value="xls">XLS
              </td>
            </tr>
          </tr>
        </table>
      </fieldset>

      

      <p align="center">
        <input type="submit" name="gerar2" value="Processar">
      </p>
      <p><h5 style="text-align: center">A geração do arquivo pode demorar alguns minutos.</h5></p>
    </form>
    </div>




  <?php
    db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));


  ?>

  </body>
</html>