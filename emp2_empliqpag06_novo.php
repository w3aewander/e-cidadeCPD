<?php
ini_set('max_execution_time', 0); // 0 = Unlimited
ini_set("memory_limit","-1");
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

require_once "libs/db_stdlib.php";
require_once "libs/db_conecta.php";
require_once "libs/db_sessoes.php";
require_once "libs/db_usuariosonline.php";
require_once (modification("fpdf151/assinatura.php"));
require_once (modification("fpdf151/pdf.php"));
require_once (modification("libs/db_sql.php"));
require_once "mpdf60/mpdf.php";

$oGet = db_utils::postMemory($_GET);


$instits = str_replace('-',', ',$instits);
$orgaos = str_replace('-',', ',$orgaos);



function contaData($dataagendamento){
  $data1 = date_create($dataagendamento);
  $data2 = date_create(date("Y-m-d"));
  $diff=date_diff($data1,$data2);
  //echo $diff->format("%a dias");
  return $diff->format("%a");
}


//echo("teste");exit();
//echo($instits);exit();

// monta sql
$txt_where = "e60_instit in ( $instits )";

if ($orgaos != '')
    $txt_where .= " and o40_orgao in ( $orgaos )";

if (($datacredor != "--") && ($datacredor1 != "--")) {
    $txt_where = $txt_where." and e60_emiss  between '$datacredor' and '$datacredor1'  ";
    //        $datacredor=db_formatar($datacredor,"d");
    //        $datacredor1=db_formatar($datacredor1,"d");
    $info = "De ".db_formatar($datacredor, "d")." até ".db_formatar($datacredor1, "d").".";
} else
    if ($datacredor != "--") {
        $txt_where = $txt_where." and e60_emiss >= '$datacredor'  ";
        //          $datacredor=db_formatar($datacredor,"d");
        $info = "Apartir de ".db_formatar($datacredor, "d").".";
    } else
        if ($datacredor1 != "--") {
            $txt_where = $txt_where."    e60_emiss <= '$datacredor1'   ";
            //         $datacredor1=db_formatar($datacredor1,"d");
            $info = "Até ".db_formatar($datacredor1, "d").".";
}


$oDaoPcorcamitem  = new cl_pcorcamitem();
$oDaoPcorcamforne = new cl_pcorcamforne();
$oDaoPcorcamval   = new cl_pcorcamval();
$oDaoPcorcamtroca = new cl_pcorcamtroca();

//Adicionado
$oDaoPcprocessocompralote= new cl_processocompralote();



$txtnaopago = "";
$datasit = "";

if($ops == "p") {
    $txt_where .= " and c53_coddoc in (5,35,37) ";
    $datasit = "c75_data";
}
else if($ops == "n") {
    $txt_where .= " and c53_coddoc in (3,33,23) ";
    /*$txtnaopago = "and e60_numemp not in (select e60_numemp
from conlancamemp
inner join conlancam on c70_codlan = c75_codlan 
left join empelemento on empelemento.e64_numemp = conlancamemp.c75_numemp 
left join empempenho on empempenho.e60_numemp = empelemento.e64_numemp 
left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan 
left OUTER join conlancampag on c82_codlan = c70_codlan 
inner join conlancamdoc on c71_codlan = c70_codlan 
inner join conhistdoc on c53_coddoc = c71_coddoc 
where e60_numemp = emp1.e60_numemp and c53_coddoc in (5,35,37))";*/
    $datasit = "CURRENT_DATE";
}
else {
    $txt_where .= "and c53_coddoc in (3,33,23,5,35,37)";
    //$datasit = "CURRENT_DATE";
    $datasit = "c75_data";
}



if(isset($nValorEmpenhoInicial) && $nValorEmpenhoInicial > 0)
    $txt_where .= " and c70_valor >= ".str_replace(',','.',$nValorEmpenhoInicial);

if(isset($nValorEmpenhoFinal) && $nValorEmpenhoFinal > 0)
    $txt_where .= " and c70_valor <= ".str_replace(',','.',$nValorEmpenhoFinal);

if(isset($o58_codigo) && $o58_codigo > 0)
    $txt_where .= " and orctiporec.o15_codigo = ".$o58_codigo;



/*Empenhos*/ 
$sSqlEmpenhos = "
    select e50_codord as ordem, e60_codemp ||'/'|| e60_anousu as numero_da_nota_de_empenho, z01_cgccpf, z01_nome, 
e69_numero as numero_nota_nf, e50_codord as nota_liquidacao, c75_data as data_pagamento, e50_data as data_liquidacao, c80_data as dataliqnova, c70_valor as valor,
e50_dataquebraordem AS data_pub,  CASE WHEN EXISTS (SELECT 1 FROM conlancampag where c82_codlan = c03_codlan) THEN 1 ELSE 2 END as status_pagamento, 
o56_descr,
e60_instit as unidadegestora,
case when o15_codigo is null then '-' else text(o15_codigo) end as fonterecurso,
({$datasit} - e50_data) as situacao,
case when pagordemprocesso.e03_numeroprocesso is null then '' else pagordemprocesso.e03_numeroprocesso end as processo,
sum(retencaoreceitas.e23_valorretencao) as retencoes,e50_justificativaquebraordem, e50_numedi
from conlancamemp
 inner join conlancam on c70_codlan = c75_codlan left join conlancamcgm on c70_codlan = c76_codlan 
inner join cgm on c76_numcgm = z01_numcgm 
left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan 
left OUTER join conlancampag on c82_codlan = c70_codlan 
inner join conlancamdoc on c71_codlan = c70_codlan 
inner join conhistdoc on c53_coddoc = c71_coddoc 
left join conlancamcompl on c72_codlan =c70_codlan
 left join conlancamnota on c66_codlan =c70_codlan
 left join conlancamord on c80_codlan =c70_codlan
 left join empnota on c66_codnota = e69_codnota 
left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu 
left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu 
left join pagordem on e50_codord = c80_codord 
left join empelemento on empelemento.e64_numemp = conlancamemp.c75_numemp 
left join empempenho emp1 on emp1.e60_numemp = empelemento.e64_numemp 
left join orcelemento on orcelemento.o56_codele = empelemento.e64_codele and orcelemento.o56_anousu = emp1.e60_anousu
left join orcdotacao on emp1.e60_coddot = orcdotacao.o58_coddot and emp1.e60_anousu = orcdotacao.o58_anousu
left join orctiporec on orcdotacao.o58_codigo = orctiporec.o15_codigo
inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao
left join pagordemprocesso on pagordem.e50_codord = pagordemprocesso.e03_pagordem
left join retencaopagordem on pagordem.e50_codord = retencaopagordem.e20_pagordem
left join retencaoreceitas on retencaopagordem.e20_sequencial = retencaoreceitas.e23_retencaopagordem
where {$txt_where} and emp1.e60_relatorio = true {$txtnaopago}

and e60_numemp not in (select e60_numemp
from conlancamemp
inner join conlancam on c70_codlan = c75_codlan 
left join empelemento on empelemento.e64_numemp = conlancamemp.c75_numemp 
left join empempenho on empempenho.e60_numemp = empelemento.e64_numemp 
left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan 
left OUTER join conlancampag on c82_codlan = c70_codlan 
inner join conlancamdoc on c71_codlan = c70_codlan 
inner join conhistdoc on c53_coddoc = c71_coddoc 
where e60_numemp = emp1.e60_numemp and c53_coddoc in (4,34,24,6,36,38))

and (
o56_elemento not like '3319003%'
 and o56_elemento not like '3319004%'
 and o56_elemento not like '3319008%'
 and o56_elemento not like '3319009%'
 and o56_elemento not like '3319011%'
 and o56_elemento not like '3319013%'
 and o56_elemento not like '3319091%'
 and o56_elemento not like '3319094%'
 and o56_elemento not like '3319096%'
 and o56_elemento not like '3329021%'
 and o56_elemento not like '3469071%' 
 and o56_elemento not like '3469073%' 
 and o56_elemento not like '3319003000000'
 and o56_elemento not like '3319004000000'
 and o56_elemento not like '3319008000000'
 and o56_elemento not like '3319009000000'
 and o56_elemento not like '3319011000000'
 and o56_elemento not like '3319013000000'
 and o56_elemento not like '3319091000000'
 and o56_elemento not like '3319094000000'
 and o56_elemento not like '3319096000000'
 and o56_elemento not like '3329021000000'
 and o56_elemento not like '3335041000000'
 and o56_elemento not like '3335043000000'
 and o56_elemento not like '3339004000000'
 and o56_elemento not like '3339008000000'
 and o56_elemento not like '3339014000000'
 and o56_elemento not like '3339018000000'
 and o56_elemento not like '3339031000000'
 and o56_elemento not like '3339032000000'
 and o56_elemento not like '3339033000000'
 and o56_elemento not like '3339046000000'
 and o56_elemento not like '3339047000000'
 and o56_elemento not like '3339091000000'
 and o56_elemento not like '3469071000000'
 and o56_elemento not like '3469073000000'
 and o56_elemento not like '3999999000000'
 and o56_elemento not like '33904100'
and o56_elemento not like '33904500'
and o56_elemento not like '33904600'
and o56_elemento not like '33904700'
and o56_elemento not like '33904800'
and o56_elemento not like '33904900'
and o56_elemento not like '33906700'
and o56_elemento not like '33909100'
and o56_elemento not like '46200000'
and o56_elemento not like '46300000'
and o56_elemento not like '46710000'
and o56_elemento not like '46720000'
and o56_elemento not like '46730000'
and o56_elemento not like '46740000'
and o56_elemento not like '331%'
 ) 
 and(z01_numcgm != '6000'
and z01_numcgm != '6517'
and z01_numcgm != '117555'
and z01_numcgm != '116919'
and z01_numcgm != '117554'
and z01_numcgm != '7652'
and z01_numcgm != '10234'
and z01_numcgm != '6585'
and z01_numcgm != '14906'
and z01_numcgm != '11365'
and z01_numcgm != '14189'
and z01_numcgm != '13281'
and z01_numcgm != '5362'
and z01_numcgm != '5412'
and z01_numcgm != '58927'
and z01_numcgm != '5388'
and z01_numcgm != '118023'
and z01_numcgm != '55636'
and z01_numcgm != '7766')
group by e60_codemp,e60_anousu, z01_cgccpf, z01_nome, e69_numero, 
e50_codord, c75_data, e50_data, c80_data, c70_valor, e50_dataquebraordem, 
e50_justificativaquebraordem, c03_codlan,o40_anousu,e60_emiss,
o56_descr, pagordemprocesso.e03_numeroprocesso,
e60_instit,o15_codigo,e50_justificativaquebraordem, e50_numedi
order by o40_anousu, e50_data 
";

//var_dump($sSqlEmpenhos); die("Verifica SQL");
//echo $sSqlEmpenhos; exit;
$rsEmpenhos = pg_query($sSqlEmpenhos );


//echo "<pre>";
//$x = pg_fetch_all($rsEmpenhos);
//print_r($x);
//echo "</pre>";
//die("Testa");

if (pg_num_rows($rsEmpenhos) == 0) {
  db_redireciona('db_erros.php?fechar=true&db_erro=Nenhum registro retornado.');
}


//Até aqui ok


$todasLinhas = array();
$todasLinhas[] = "Unidade Gestora;Fonte de Recurso;Nº Empenho;Razão Social;Nº Nota de Liq.;Data Adimplemento;Data Liquidação;Data Pagamento;Valor;Data Publicação;Link;Nº do Processo;Retenção;Situação";

//db_criatabela($rsEmpenhos); exit;
 
    $valorTotal = 0; 
    $array = array();
    $novoarray = array();

function verificapago($ordem){
$sql = pg_query("select c61_reduz from pagordem inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo left join conlancamord on c80_codord = pagordem.e50_codord left join conlancampag on c82_codlan = conlancamord.c80_codlan left join conlancam on c70_codlan = conlancamord.c80_codlan left join conlancamdoc on c71_codlan = conlancam.c70_codlan left join conhistdoc on c53_coddoc = conlancamdoc.c71_coddoc left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c82_anousu = c61_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordemconta on pagordemconta.e49_codord = pagordem.e50_codord where e50_codord = {$ordem} AND c61_reduz is not null order by e50_codord;");
$resultado = pg_fetch_all($sql);
//Campo c53_tipo
return $resultado[0]["c61_reduz"];
}

function buscaDataliqnova($ordem){
  $sql = pg_query("SELECT c80_data from pagordem inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo left join conlancamord on c80_codord = pagordem.e50_codord left join conlancampag on c82_codlan = conlancamord.c80_codlan left join conlancam on c70_codlan = conlancamord.c80_codlan left join conlancamdoc on c71_codlan = conlancam.c70_codlan left join conhistdoc on c53_coddoc = conlancamdoc.c71_coddoc left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c82_anousu = c61_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordemconta on pagordemconta.e49_codord = pagordem.e50_codord where e50_codord={$ordem} AND c53_tipo = 20");
  $resultado = pg_fetch_all($sql);
  
  return $resultado[0]["c80_data"];
}


function buscaDataAgendamento($ordem){
  $sql = pg_query("SELECT e42_dtpagamento from empage inner join empagemov on empagemov.e81_codage = empage.e80_codage inner join empord on empord.e82_codmov = empagemov.e81_codmov inner join pagordem on pagordem.e50_codord = empord.e82_codord inner join pagordemele on pagordemele.e53_codord = pagordem.e50_codord inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo left join empageconcarpeculiar on empageconcarpeculiar.e79_empagemov = empagemov.e81_codmov left join corempagemov on corempagemov.k12_codmov = empagemov.e81_codmov left join empagemovconta on empagemov.e81_codmov = e98_codmov left join empageconf on empageconf.e86_codmov = empord.e82_codmov left join empageconfche on empageconf.e86_codmov = e91_codmov and e91_ativo is true left join pagordemconta on e49_codord = e82_codord left join empagemovforma on e97_codmov = e81_codmov left join empagepag on e85_codmov = e81_codmov left join empagetipo on e85_codtipo = e83_codtipo left join pagordemnota on e71_codord = e50_codord left join empnota on e69_codnota = e71_codnota left join cgm a on a.z01_numcgm = e49_numcgm left join empageconfgera on empageconfgera.e90_codmov = empagemov.e81_codmov and empageconfgera.e90_cancelado is false left join corgrupocorrente on k105_data = corempagemov.k12_data and k105_autent = corempagemov.k12_autent and k105_id = corempagemov.k12_id left join empagenotasordem on e81_codmov = e43_empagemov left join empageordem on e43_ordempagamento = e42_sequencial left join saltes on e83_conta = k13_conta left join empageforma on e97_codforma = e96_codigo left join pcfornecon on e98_contabanco = pc63_contabanco left join empempaut on e60_numemp = e61_numemp left join empautorizaprocesso on e150_empautoriza = e61_autori left join pagordemprocesso on e03_pagordem = e50_codord where e50_codord = {$ordem} order by e82_codord, e81_codmov");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["e42_dtpagamento"];
  
}





//var_dump($rsEmpenhos); exit;

//print(pg_num_rows($rsEmpenhos));exit;

for ($iRow = 0; $iRow < pg_num_rows($rsEmpenhos); $iRow++) {

    $oEmpenhos = db_utils::fieldsMemory($rsEmpenhos, $iRow);

    $data_pagamento = "";
    $sit = "";
    $justificativa = "";

    if($ops == "t") {
    $txt_wherepg = " m51_codordem = " . $oEmpenhos->ordem;

    $sSqlEmpenhospagos = "
select c75_data as data_pagamento, e50_data as data_liquidacao, 
CASE WHEN (c75_data - e50_data) < 0 THEN 0 ELSE (c75_data - e50_data) END as situacao
from conlancamemp
 inner join conlancam on c70_codlan = c75_codlan left join conlancamcgm on c70_codlan = c76_codlan 
inner join cgm on c76_numcgm = z01_numcgm 
left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan 
left OUTER join conlancampag on c82_codlan = c70_codlan 
inner join conlancamdoc on c71_codlan = c70_codlan 
inner join conhistdoc on c53_coddoc = c71_coddoc 
left join conlancamcompl on c72_codlan =c70_codlan
 left join conlancamnota on c66_codlan =c70_codlan
 left join conlancamord on c80_codlan =c70_codlan
 left join empnota on c66_codnota = e69_codnota 
left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu 
left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu 
left join pagordem on e50_codord = c80_codord 
left join empelemento on empelemento.e64_numemp = conlancamemp.c75_numemp 
left join empempenho on empempenho.e60_numemp = empelemento.e64_numemp 
left join orcelemento on orcelemento.o56_codele = empelemento.e64_codele and orcelemento.o56_anousu = empempenho.e60_anousu
left join orcdotacao on empempenho.e60_coddot = orcdotacao.o58_coddot and empempenho.e60_anousu = orcdotacao.o58_anousu
left join orctiporec on orcdotacao.o58_codigo = orctiporec.o15_codigo
inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao
left join pagordemprocesso on pagordem.e50_codord = pagordemprocesso.e03_pagordem
left join retencaopagordem on pagordem.e50_codord = retencaopagordem.e20_pagordem
left join retencaoreceitas on retencaopagordem.e20_sequencial = retencaoreceitas.e23_retencaopagordem
where {$txt_wherepg} and c53_coddoc in (5) and empempenho.e60_relatorio = true

and (
o56_elemento not like '3319003%'
 and o56_elemento not like '3319004%'
 and o56_elemento not like '3319008%'
 and o56_elemento not like '3319009%'
 and o56_elemento not like '3319011%'
 and o56_elemento not like '3319013%'
 and o56_elemento not like '3319091%'
 and o56_elemento not like '3319094%'
 and o56_elemento not like '3319096%'
 and o56_elemento not like '3329021%'
 and o56_elemento not like '3469071%' 
 and o56_elemento not like '3469073%' 
 and o56_elemento not like '3319003000000'
 and o56_elemento not like '3319004000000'
 and o56_elemento not like '3319008000000'
 and o56_elemento not like '3319009000000'
 and o56_elemento not like '3319011000000'
 and o56_elemento not like '3319013000000'
 and o56_elemento not like '3319091000000'
 and o56_elemento not like '3319094000000'
 and o56_elemento not like '3319096000000'
 and o56_elemento not like '3329021000000'
 and o56_elemento not like '3335041000000'
 and o56_elemento not like '3335043000000'
 and o56_elemento not like '3339004000000'
 and o56_elemento not like '3339008000000'
 and o56_elemento not like '3339014000000'
 and o56_elemento not like '3339018000000'
 and o56_elemento not like '3339031000000'
 and o56_elemento not like '3339032000000'
 and o56_elemento not like '3339033000000'
 and o56_elemento not like '3339046000000'
 and o56_elemento not like '3339047000000'
 and o56_elemento not like '3339091000000'
 and o56_elemento not like '3469071000000'
 and o56_elemento not like '3469073000000'
 and o56_elemento not like '3999999000000'
 and o56_elemento not like '33904100'
and o56_elemento not like '33904500'
and o56_elemento not like '33904600'
and o56_elemento not like '33904700'
and o56_elemento not like '33904800'
and o56_elemento not like '33904900'
and o56_elemento not like '33906700'
and o56_elemento not like '33909100'
and o56_elemento not like '46200000'
and o56_elemento not like '46300000'
and o56_elemento not like '46710000'
and o56_elemento not like '46720000'
and o56_elemento not like '46730000'
and o56_elemento not like '46740000'
and o56_elemento not like '331%'
 ) 
 and(z01_numcgm != '6000'
and z01_numcgm != '6517'
and z01_numcgm != '117555'
and z01_numcgm != '116919'
and z01_numcgm != '117554'
and z01_numcgm != '7652'
and z01_numcgm != '10234'
and z01_numcgm != '6585'
and z01_numcgm != '14906'
and z01_numcgm != '11365'
and z01_numcgm != '14189'
and z01_numcgm != '13281'
and z01_numcgm != '5362'
and z01_numcgm != '5412'
and z01_numcgm != '58927'
and z01_numcgm != '5388'
and z01_numcgm != '118023'
and z01_numcgm != '55636'
and z01_numcgm != '7766')
group by c75_data, e50_data
order by c75_data,e50_data 
";
//echo $sSqlEmpenhospagos; exit;

//echo $sSqlEmpenhos; exit;
        $rsEmpenhospagos = pg_query($sSqlEmpenhospagos);
        $pag = pg_num_rows($rsEmpenhospagos);

        if($pag > 0){
            $oEmppag = db_utils::fieldsMemory($rsEmpenhospagos, 0);
            $data_pagamento =$oEmppag->data_pagamento;
            $sit = $oEmppag->situacao;
        } else {
            if(verificapago($oEmpenhos->ordem)){
              $data_pagamento =$oEmpenhos->data_pagamento;
              $oEmpenhos->dataliqnova = buscaDataliqnova($oEmpenhos->ordem);
            } else {
              $data_pagamento = "";
            }

            $sit = $oEmpenhos->situacao;
            $dataquebra = $oEmpenhos->data_pub;
            if(strlen($dataquebra) > 0) {
                $dataquebra = substr($dataquebra, 0, 10);
                $numedi = $oEmpenhos->e50_numedi;
                $ano = substr($dataquebra, 0, 4);
                if(strlen($numedi) > 0) {
                    $justificativa = $oEmpenhos->e50_justificativaquebraordem;
                }
            }
        }
    }
    else
    {
        if($ops == "p"){
            $data_pagamento =$oEmpenhos->data_pagamento;
            $oEmpenhos->dataliqnova = buscaDataliqnova($oEmpenhos->ordem);

        } else {
            $veri2 = verificapago($oEmpenhos->ordem);
            if($veri2 != NULL){continue;}
            $data_pagamento = "";
            $dataquebra = $oEmpenhos->data_pub;
            if(strlen($dataquebra) > 0) {
                $dataquebra = substr($dataquebra, 0, 10);
                $numedi = $oEmpenhos->e50_numedi;
                $ano = substr($dataquebra, 0, 4);
                if(strlen($numedi) > 0) {
                    $justificativa = $oEmpenhos->e50_justificativaquebraordem;
                    //$justificativa = "http://new.voltaredonda.rj.org.br/images/Documentos/VRDestaques/" . $ano . "/" . $dataquebra . "_" . $numedi . ".pdf";
                }
            }
        }
        $sit = $oEmpenhos->situacao;
    }

        $sit2 = contaData($oEmpenhos->dataliqnova);
        $novaData = buscaDataAgendamento($oEmpenhos->ordem);
        //Até aqui certo

        $novoarray[$oEmpenhos->ordem] = array(
            $oEmpenhos->ordem, //0
            $oEmpenhos->numero_da_nota_de_empenho, //1
            $oEmpenhos->z01_cgccpf, //2
            $oEmpenhos->z01_nome, //3
            $oEmpenhos->numero_nota_nf, //4
            $oEmpenhos->nota_liquidacao, //5
            $novaData, //6            
            $data_pagamento, //7
            $oEmpenhos->valor, //8
            $oEmpenhos->data_pub, //9
            $justificativa, //10
            $oEmpenhos->status_pagamento, //11
            $justificativa, //12
            $oEmpenhos->unidadegestora, //13
            $oEmpenhos->fonterecurso, //14
            $sit2, //15
            $oEmpenhos->processo, //16
            $oEmpenho->retencoes, //17
            $oEmpenhos->dataliqnova //18 //Errada
        );
        //$todasLinhas[] = implode(';', $linha);
        //$todasLinhas[] = implode(';', $novoarray[$oEmpenhos->ordem]);
        
        $valorTotal += $oEmpenhos->valor;        
}

        foreach ($novoarray as $key => $part) {
            $sort[$key] = strtotime($part[18]);
        }
        array_multisort($sort, SORT_ASC, $novoarray);

//echo "<pre>";
//print_r($novoarray);
//print_r($todasLinhas);
//echo "</pre>";
//die("CSV");

foreach ($novoarray as $linha) {
    $linha2 = array();
    $linha2[0] = $linha[13];
    $linha2[1] = $linha[14];
    $linha2[2] = $linha[1];
    $linha2[3] = $linha[3];
    $linha2[4] = $linha[5];    
    $linha2[5] = db_formatar($linha[6], "d");
    $linha2[6] = db_formatar($linha[18], "d");
    $linha2[7] = db_formatar($linha[7], "d");
    $linha2[8] = number_format($linha[8], 2, ",", ".");
    $linha2[9] = db_formatar($linha[9], "d");
    $linha2[10] = $linha[12];
    $linha2[11] = $linha[16];
    $linha2[12] = number_format($linha[17], 2, ",", ".");
    $linha2[13] = $linha[15];
    //echo "<pre>";
    //print_r($linha);
    //echo "</pre>";
    $todasLinhas[] = implode(';', $linha2);
}
//echo "<pre>";
//print_r($novoarray);
//print_r($todasLinhas);
//echo "</pre>";
//die("Verifica formatação");

$linhatot = array(utf8_encode("TOTAIS"), number_format($valorTotal, 2, ",", "."));
$todasLinhas[] = implode(';', $linhatot);



$resultado = implode("\n", $todasLinhas);
file_put_contents('tmp/empenho.csv', $resultado);
echo "<html><body bgcolor='#cccccc'><center><a href='tmp/empenho.csv'>Clique com botão direito para Salvar o arquivo <b>empenho.csv</b></a></body></html>";

exit();
