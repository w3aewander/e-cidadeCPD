<?php
//Rodar esse arquivo todo dia no horário especificado pela SAAE
//Trocar os dados de conexão na linha 7 para o banco de dados que será utilizado
//Na linha 138 há instruções sobre o local
function conecta(){
    try {
      $pdo = new PDO("pgsql:dbname='voltaredonda_ecidade_prod'; host='10.1.0.135'; user='ecidade'; password='qZqh1fQS3dnk0yBqLe'; port='6432'");
      $pdo->exec( "select fc_startsession();" );
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $pdo;
    } catch (PDOException $e){
      echo "Erro: " . $e->getMessage();
    }
}

$pdo = conecta();
if(!$pdo) die ("Não foi possível conectar ao banco. Tente novamente.");

//$campodata = date("Y-m-d");
$campodata = "2016-01-01";


function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function verificaEstoque($pdo, $cod){
    try {
      $stmt = $pdo->prepare("SELECT distinct m70_coddepto,descrdepto,m70_quant, round((m70_quant*3.50000),2)::float as m70_valor, coalesce((select sum(coalesce(case when m81_tipo = 4 then m82_quant end, 0)) as saida from matestoqueinimei inner join matestoqueitem on m71_codlanc = m82_matestoqueitem inner join matestoque trans on m71_codmatestoque = trans.m70_codigo inner join matestoqueini on m80_codigo = m82_matestoqueini left join matestoqueinil on m80_codigo = m86_matestoqueini inner join matestoquetipo on m80_codtipo = m81_codtipo where trans.m70_codigo = matestoque.m70_codigo and m81_codtipo = 7 and m86_matestoqueini IS NULL),0) as dl_transferencias from matestoque inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join db_departorg on db_departorg.db01_coddepto = db_depart.coddepto and db_departorg.db01_anousu = 2020 inner join orcunidade on orcunidade.o41_orgao = db_departorg.db01_orgao and orcunidade.o41_unidade = db_departorg.db01_unidade and orcunidade.o41_anousu = db_departorg.db01_anousu and orcunidade.o41_instit = 45 inner join orcorgao on orcorgao.o40_orgao = orcunidade.o41_orgao and orcorgao.o40_anousu = orcunidade.o41_anousu where m70_codmatmater= :cod");
      $stmt->execute(array("cod" => $cod));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}

function buscaValorUnitario($pdo, $cod){
  try {
      $stmt = $pdo->prepare("SELECT m80_codigo, case when m81_tipo = 2 then 0 when m81_tipo = 1 then round(m89_valorunitario, 5)::numeric end as m89_valorunitario, m82_quant, round(m89_precomedio, 5)::numeric as fc_calculapm , descrdepto, m80_data, m80_hora, m80_codtipo, (select db_depart.descrdepto from matestoqueinimei inner join matestoqueitem on matestoqueitem.m71_codlanc = matestoqueinimei.m82_matestoqueitem inner join matestoqueinimeiari on matestoqueinimeiari.m49_codmatestoqueinimei = matestoqueinimei.m82_codigo inner join atendrequiitem on atendrequiitem.m43_codigo = matestoqueinimeiari.m49_codatendrequiitem inner join matrequiitem on matrequiitem.m41_codigo = atendrequiitem.m43_codmatrequiitem inner join matrequi on matrequi.m40_codigo = matrequiitem.m41_codmatrequi inner join db_depart on db_depart.coddepto = matrequi.m40_depto where matestoqueinimei.m82_matestoqueini = matestoqueini.m80_codigo limit 1) as coddepto_destino FROM matestoqueini inner join matestoquetipo on m80_codtipo = m81_codtipo inner join matestoqueinimei on m82_matestoqueini = m80_codigo inner join db_usuarios on m80_login = id_usuario inner join db_depart on m80_coddepto = coddepto inner join matestoqueitem on m82_matestoqueitem = m71_codlanc inner join matestoque on m71_codmatestoque = m70_codigo inner join matmater on m60_codmater = m70_codmatmater left join matestoqueitemoc on m71_codlanc = m73_codmatestoqueitem and m73_cancelado is false left join matordemitem on m52_codlanc = m73_codmatordemitem left join matestoqueinill on m87_matestoqueini = m80_codigo left join matestoqueinil on m86_codigo = m87_matestoqueinil left join matestoqueinimeipm on m82_codigo = m89_matestoqueinimei where m81_entrada = 't' and m70_codmatmater = :cod and instit = 45 and m71_servico is false order by m80_data desc, m80_hora desc LIMIT 1");
      $stmt->execute(array("cod" => $cod));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado[0] : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function retornaUltimoCodigo($pdo, $codmaterial){
  try {
      $stmt = $pdo->prepare("SELECT max(m63_codpcmater)from transmater inner join pcmater on pcmater.pc01_codmater = transmater.m63_codpcmater inner join matmater on matmater.m60_codmater = transmater.m63_codmatmater where m63_codmatmater= :codmaterial");
      $stmt->execute(array("codmaterial" => $codmaterial));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado[0]->max : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function buscaData($pdo, $m70codigo){
  try {
      $stmt = $pdo->prepare("SELECT m71_data FROM matestoqueitem WHERE m71_codmatestoque = :m70codigo");
      $stmt->execute(array("m70codigo" => $m70codigo));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado[0]->m71_data : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function verificaOutraCoisa($pdo, $codmatestoque){
  try {
      $stmt = $pdo->prepare("SELECT * from matestoqueitem inner join matestoque on matestoque.m70_codigo = matestoqueitem.m71_codmatestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater left join matestoqueitemlote on m71_codlanc = m77_matestoqueitem left join matestoqueitemfabric on m71_codlanc = m78_matestoqueitem left join matfabricante on m78_matfabricante = m76_sequencial left join cgm on m76_numcgm = z01_numcgm where m71_codmatestoque= :codmatestoque and 1=1 ");
      $stmt->execute(array("codmatestoque" => $codmatestoque));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


function buscaTudo($pdo){
  try {
      $stmt = $pdo->prepare("SELECT distinct m70_codigo, m70_codmatmater, trim(m60_descr) as m60_descr, m61_usaquant, m61_usadec, m70_coddepto, descrdepto, m70_valor, m70_quant, m60_ativo, m61_descr from matestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid left join transmater on transmater.m63_codmatmater = matmater.m60_codmater left join pcmater on pcmater.pc01_codmater = transmater.m63_codpcmater inner join matmatermaterialestoquegrupo on m68_matmater = m60_codmater inner join materialestoquegrupo on m68_materialestoquegrupo = m65_sequencial inner join db_estruturavalor on m65_db_estruturavalor = db121_sequencial inner join matparam on db121_db_estrutura = m90_db_estrutura WHERE db_depart.instit in (45) and m70_coddepto in (1270) and m60_ativo = 't' and (pc01_servico is false or pc01_servico is null) order by trim(m60_descr)");
      $stmt->execute();
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}


$dados = buscaTudo($pdo);
$ajustado = array();

foreach ($dados as $linha) {  
  $novosprecos = buscaValorUnitario($pdo, $linha->m70_codmatmater);  
  $novovalor = $novosprecos->m89_valorunitario;
  $novomedio = $novosprecos->fc_calculapm;  
  $novadata = buscaData($pdo, $linha->m70_codigo);

  if($linha->m70_quant == 0){
    if($novadata < $campodata){
      continue;
    }
  }

  $linha->m70_valor = number_format($novovalor, 2, ',', '');
  $linha->m85_precomedio = number_format($novomedio, 2, ',', '');
  $linha->m60_ativo = ($linha->m60_ativo == "t" ? "S" : "N");
  $linha->m71_data = implode("/", array_reverse(explode("-", $novadata)));

  $novocodigo = retornaUltimoCodigo($pdo, $linha->m70_codmatmater);
  
  $linha->m70_codmatmater = str_pad($linha->m70_codmatmater, 10, " ", STR_PAD_LEFT);
  $linha->m63_codpcmater = str_pad($novocodigo, 10, " ", STR_PAD_LEFT);  

  $linha->m60_descr = str_pad($linha->m60_descr, 80);
  
  $linha->m70_valor = str_pad($linha->m70_valor, 10, " ", STR_PAD_LEFT);
  $linha->m85_precomedio = str_pad($linha->m85_precomedio, 10, " ", STR_PAD_LEFT);
  $linha->m70_quant = str_pad(str_replace(".", ",", $linha->m70_quant), 9, " ", STR_PAD_LEFT);

  $linha->m61_descr = str_pad($linha->m61_descr, 10, " ", STR_PAD_RIGHT);

  array_push($ajustado, $linha->m70_codmatmater . $linha->m63_codpcmater . $linha->m60_descr . $linha->m70_valor . $linha->m85_precomedio . $linha->m70_quant . $linha->m60_ativo . $linha->m71_data . $linha->m61_descr);
          
}


//Colocar o arquivo abaixo na pasta escolhida pela SAAE e dar permissão de escrita.
$arquivo = fopen("/home/saae/txt/almoxarifado-diario.txt", "w");
foreach ($ajustado as $linhaA) {
  fwrite($arquivo, $linhaA . "\r\n");
}
fclose($arquivo);


echo "Finalizado";
