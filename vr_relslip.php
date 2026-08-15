<?PHP

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}


require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_classesgenericas.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_orctiporec_classe.php"));
//$clslip = new cl_slip;
$get = db_utils::postMemory($_GET);
$clrotulo = new rotulocampo;
$clrotulo->label('k17_codigo');
$clrotulo->label('k17_data');
$clrotulo->label('k17_debito');
$clrotulo->label('k17_credito');
$clrotulo->label('k17_valor');
$clrotulo->label('k17_hist');
$clrotulo->label('k17_texto');
$clrotulo->label('k17_dtaut');
$clrotulo->label('k17_autent');
$clrotulo->label('c60_descr');
$clrotulo->label('z01_nome');



parse_str($HTTP_SERVER_VARS['QUERY_STRING']);
//db_postmemory($HTTP_GET_VARS,2);exit;

$where = "";
$where1 = "";
if (($get->data != "--") && ($get->data1 != "--")) {
    $where = " and k17_data  between '$get->data' and '$get->data1' ";
} elseif ($get->data != "--") {
    $where = " and k17_data >= '$get->data' ";
} elseif ($get->data1 != "--") {
    $where = " and  k17_data <= '$get->data1' ";
}
if ($get->situacao == "A") {
    $where1 = "  ";
} else {
    $where1 = " and k17_situacao = " . $get->situacao;
}

if (trim($get->codigos) != "") {
    $where .= " and k17_numcgm ";
    $where .= " in (" . $get->codigos . ") ";
}

$whereslip = "";

if (isset($get->slip1) && trim($get->slip1) != "") {
    $whereslip = " and slip.k17_codigo >= $get->slip1 ";
}

if (isset($get->slip2) && trim($get->slip2) != "") {
    if (trim($whereslip) != "") {
        $whereslip = " and slip.k17_codigo between " . $get->slip1 . " and " . $get->slip2;
    } else {
        $whereslip = " and slip.k17_codigo <= " . $get->slip2;
    }
}

if (isset($get->recurso) && $get->recurso != '0') {
    $ids = \ECidade\Financeiro\Orcamento\Repository\RecursoRepository::getIdsRecursoPorSubrecurso($get->recurso);
    $ids = implode(', ', $ids);
    $whereslip .= " and ( r1.c61_codigo in ($ids) or r2.c61_codigo in ($ids) ) ";
}

if (isset($get->hist) && $get->hist != '') {
    $whereslip .= " and slip.k17_hist = {$get->hist}";
}

if (isset($get->k145_numeroprocesso) && $get->k145_numeroprocesso != '') {
    $whereslip .= " and slipprocesso.k145_numeroprocesso ilike '%{$get->k145_numeroprocesso}%' ";
}

$where .= $whereslip;
$sql = "         select slip.k17_codigo,
                        k17_data,
                        r1.c61_reduz||'-'||c1.c60_descr as dl_debito_descr,
                        r2.c61_reduz||'-'||c2.c60_descr as dl_credito_descr,
                        (case when k17_situacao = 1 then 'Não Autenticado'
                              when k17_situacao = 2 then 'Autenticado'
                              when k17_situacao = 3 then 'Estornado'
                              when k17_situacao = 4 then 'Anulado'
                         end
                        ) as k17_situacao,
                        k17_valor,
                        k17_dtaut,
                        z01_nome,
                        k145_numeroprocesso
                   from slip
                   left join conplanoreduz r1 on r1.c61_reduz  = k17_debito
                                             and r1.c61_instit = k17_instit
                                             and r1.c61_anousu =" . db_getsession("DB_anousu") . "
                   left join conplano      c1 on c1.c60_codcon = r1.c61_codcon
                                             and c1.c60_anousu = r1.c61_anousu

                   left join conplanoreduz r2 on r2.c61_reduz = k17_credito
                                             and r2.c61_instit = k17_instit
                                             and r2.c61_anousu=" . db_getsession("DB_anousu") . "
                   left join conplano c2 on c2.c60_codcon = r2.c61_codcon      and c2.c60_anousu = r2.c61_anousu

                   left join slipnum on slipnum.k17_codigo = slip.k17_codigo
                   left join cgm on cgm.z01_numcgm = slipnum.k17_numcgm
                   left join slipprocesso on slip.k17_codigo = slipprocesso.k145_slip
                  where k17_instit = " . db_getsession('DB_instit') . "
                        $where $where1
                  order by slip.k17_codigo";


$dadossql = pg_query($sql);
$dados = pg_fetch_all($dadossql);

if($_GET["tipo"] == "csv"){

$arquivo = fopen("tmp/relatorio_slips.csv", "w");
fwrite($arquivo, utf8_encode("Código Slip;Data;Débito descr;Crédito descr;Situação;Valor;Data Autenticação;Nome/Razão Social;Nº do Processo")."\n");

$guardacsv = array();
foreach ($dados as $linha){
  if(trim($linha["e69_numero"] == "S/N")){continue;}
  $cod = trim($linha["k17_codigo"]);
  if(empty($linha["k17_data"])){
    $data = "";
  }else{
    $data = implode("/", array_reverse(explode("-", $linha["k17_data"])));
  }
  $debito = trim($linha["dl_debito_descr"]);
  $credito = trim($linha["dl_credito_descr"]);
  $situacao = trim($linha["k17_situacao"]);
  $valor = number_format($linha["k17_valor"],2,',','.');

  if(empty($linha["k17_dtaut"])){
    $dataaut = "";
  }else{
    $dataaut = implode("/", array_reverse(explode("-", $linha["k17_dtaut"])));
  }
  $nome = trim(utf8_encode($linha["z01_nome"]));
  $nprocesso = trim($linha["k145_numeroprocesso"]);
  
      
  $linhacsv = "{$cod};{$data};{$debito};{$credito};{$situacao};{$valor};{$dataaut};{$nome};{$nprocesso}";
  array_push($guardacsv, $linhacsv);  
}



foreach ($guardacsv as $linha) {
  fwrite($arquivo, utf8_encode($linha) . "\n");
}
fclose($arquivo);


$file_url = 'tmp/relatorio_slips.csv';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url); 
exit();
}