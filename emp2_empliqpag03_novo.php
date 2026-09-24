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

require_once "mpdf60/mpdf.php";


  

$oGet = db_utils::postMemory($_GET);

$instits = str_replace('-',', ',$db_selinstit);
$orgaos = str_replace('-',', ',$orgaos);

function contaData($dataagendamento){
  $data1 = date_create($dataagendamento);
  $data2 = date_create(date("Y-m-d"));
  $diff=date_diff($data1,$data2);
  //echo $diff->format("%a dias");
  return $diff->format("%a");
}

//$xdt = "2020-06-14";
//$xteste = contaData($xdt);
//var_dump($xteste);

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

/*
if (empty($oGet->iOrcamento)) {
  db_redireciona('db_erros.php?fechar=true&db_erro=Código do orçamento inválido.');
}

*/

/*
if (empty($oGet->sJustificativa)) {
  db_redireciona('db_erros.php?fechar=true&db_erro=Erro ao passar o parametro da Justificativa.');
}
*/

$oDaoPcorcamitem  = new cl_pcorcamitem();
$oDaoPcorcamforne = new cl_pcorcamforne();
$oDaoPcorcamval   = new cl_pcorcamval();
$oDaoPcorcamtroca = new cl_pcorcamtroca();

//Adicionado
$oDaoPcprocessocompralote= new cl_processocompralote();
      

//Dados da instituição
$dados = db_query($conn,"select nomeinst,
                               db21_compl,
                               trim(ender)||',
                               '||trim(cast(numero as text)) as ender,
                               trim(ender) as rua,
                               munic,
                               numero,
                               uf,
                               cgc,
                               telef,
                               email,
                               url,
                               logo 
                        from db_config where codigo = ".db_getsession("DB_instit"));

$logo = 'imagens/files/'.pg_result($dados,0,'logo'); 
$nomeinst = pg_result($dados,0,"nomeinst");
$sComplento = substr(trim(pg_result($dados,0,"db21_compl") ),0,20 );

//o58_codigo
//nValorEmpenhoInicial
//nValorEmpenhoFinal



if ($sComplento != '' || $sComplento != null ) {
  $sComplento = ", ".substr(trim(pg_result($dados,0,"db21_compl") ),0,20 );
}

$ender = trim(pg_result($dados,0,"rua")).", ".trim(pg_result($dados,0,"numero")).$sComplento;
$munic = trim(pg_result($dados,0,"munic"))." - ".pg_result($dados,0,"uf");  
$telef = trim(pg_result($dados,0,"telef"))."   -    CNPJ : ".db_formatar(pg_result($dados,0,"cgc"),"cnpj");
$email = trim(pg_result($dados,0,"email"));
$url = @pg_result($dados,0,"url");


$sSqlFornecedores = $oDaoPcorcamforne->sql_query( null,
                                                  "*",
                                                  null,
                                                  "pc21_codorc = {$oGet->iOrcamento}" );

$rsFornecedores = $oDaoPcorcamforne->sql_record( $sSqlFornecedores );

if ($oDaoPcorcamforne->numrows == 0) {
  //db_redireciona('db_erros.php?fechar=true&db_erro=Nenhum Fornecedor cadastrado para o Orçamento.');
}

/*Numero de Processo*/
$sSqlNumeroProcesso = "select distinct * from pcorcamitem inner join pcorcam on pcorcam.pc20_codorc = pcorcamitem.pc22_codorc inner join pcorcamitemproc on pcorcamitemproc.pc31_orcamitem = pcorcamitem.pc22_orcamitem inner join pcprocitem on pcprocitem.pc81_codprocitem = pcorcamitemproc.pc31_pcprocitem inner join pcproc on pcprocitem.pc81_codproc = pcproc.pc80_codproc inner join solicitem on solicitem.pc11_codigo = pcprocitem.pc81_solicitem left join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo left join pcmater on pcmater.pc01_codmater = solicitempcmater.pc16_codmater left join processocompraloteitem on pc69_pcprocitem = pcprocitem.pc81_codprocitem left join processocompralote on pc68_sequencial = pc69_processocompralote left join solicitemunid on solicitemunid.pc17_codigo = solicitem.pc11_codigo left join matunid on matunid.m61_codmatunid = solicitemunid.pc17_unid left join pcorcamval on pc23_orcamitem = pc22_orcamitem left join pcorcamforne on pcorcamforne.pc21_orcamforne = pcorcamval.pc23_orcamforne left join cgm on cgm.z01_numcgm = pcorcamforne.pc21_numcgm left join pcorcamjulg on pcorcamjulg.pc24_orcamitem = pcorcamval.pc23_orcamitem and pcorcamjulg.pc24_orcamforne=pcorcamval.pc23_orcamforne
where pc22_codorc = {$oGet->iOrcamento} limit 1";  

$rsNumeroProcesso = db_query($sSqlNumeroProcesso );

$iCodigoProcesso = db_utils::fieldsMemory($rsNumeroProcesso, 0)->pc80_codproc;

/*Numero de Processo Administrativo */
$sSqlNumeroProcessoAdm = "select pc90_numeroprocesso from pcprocitem inner join pcproc on pcproc.pc80_codproc = pcprocitem.pc81_codproc  inner join solicitem on solicitem.pc11_codigo = pcprocitem.pc81_solicitem inner join solicita on solicita.pc10_numero = solicitem.pc11_numero inner join db_depart on db_depart.coddepto = solicita.pc10_depto inner join solicitaprotprocesso on pc11_numero = pc90_solicita
where pc81_codproc = {$iCodigoProcesso} limit 1";  

$rsNumeroProcessoAdm = db_query($sSqlNumeroProcessoAdm );

$iCodigoProcessoAdm = db_utils::fieldsMemory($rsNumeroProcessoAdm, 0)->pc90_numeroprocesso;

$mpdf=new mPDF('c','A4-L','','',8,8,45,16,9,2); 

$mpdf->tablethead = 0;
$mpdf->mirrorMargins = 0; // Use different Odd/Even headers and footers and mirror margins
$mpdf->useSubstitutions = false;
$mpdf->simpleTables = true;

//$logo = "imagens/files/e-cidade_logo_o.jpg";

//var_dump($_POST); exit;

$info = "De ".db_formatar($datacredor, "d")." até ".db_formatar($datacredor1, "d");

$info2 = "";
$info3 = "";
$info4 = "";

if($ops == "p")
    $info2 = "Somente OP's pagas";
else if($ops == "n")
    $info2 = "Somente OP's não pagas";
else if($ops == "gnp")
    $info2 = "Geral não pagos.";
else $info2 = "Todas OP's";

if(isset($o58_codigo) && $o58_codigo > 0) {

    $strquerytiporecurso = db_query("select o15_descr from orcamento.orctiporec 
                                        where o15_codigo = ".$o58_codigo);
    $nomerec = pg_result($strquerytiporecurso,0,"o15_descr");
    $info3 = "Tipo de Recurso: " . $o58_codigo . " - " . $nomerec;
}

$blninfo4 = true;
if(isset($nValorEmpenhoInicial) && $nValorEmpenhoInicial > 0)
{
    if(strlen($info3) == 0)
    {
        $blninfo4 = false;
        $info3 = "Faixa de Valor: acima de ".number_format($nValorEmpenhoInicial, 2, ",", ".");
    }
    else
    {
        $blninfo4 = true;
        $info4 = "Faixa de Valor: acima de ".number_format($nValorEmpenhoInicial, 2, ",", ".");
    }
}

if(isset($nValorEmpenhoFinal) && $nValorEmpenhoFinal > 0)
{
    if(strlen($info3) == 0)
    {
        //quer dizer que o filtro de tipo de recurso e o filtro de valoinicial não foram setados
        $info3 = "Faixa de Valor: 0 até ".number_format($nValorEmpenhoFinal, 2, ",", ".");
    }
    else
    {
        if($blninfo4)
        {
            if(isset($nValorEmpenhoInicial) && $nValorEmpenhoInicial > 0)
                $info4 = "Faixa de Valor: ".number_format($nValorEmpenhoInicial, 2, ",", ".")." até ".$nValorEmpenhoFinal;
            else
                $info4 = "Faixa de Valor: 0 até ".number_format($nValorEmpenhoFinal, 2, ",", ".")." ";
        }
        else
        {
            if(isset($nValorEmpenhoInicial) && $nValorEmpenhoInicial > 0)
                $info3 = "Faixa de Valor: ".number_format($nValorEmpenhoInicial, 2, ",", ".")." até ".$nValorEmpenhoFinal;
            else
                $info3 = "Faixa de Valor: 0 até ".number_format($nValorEmpenhoFinal, 2, ",", ".")." ";
        }
    }
}

$dadosinstit = "";

if(strpos($instits, ","))
    $dadosinstit = "Instituições: ".$instits;
else {
    $dadosinstitfiltro = db_query($conn,"select nomeinst
                        from db_config where codigo = ".$instits);


    $nomeinstfiltro = pg_result($dadosinstitfiltro,0,"nomeinst");
    $dadosinstit = "Instituição: ".$instits." - ".$nomeinstfiltro;
}

$header = '<table width="100%" border="0" style="border-bottom:1px solid black; vertical-align: bottom; font-family: arial; font-size: 9pt; color: #000000;">
    <tr>
        <td width="5%" height="5%" rowspan="6" align="left" valign="top" style="padding-top:0px"><img src="'.$logo.'" width="100px" height="100px" alt=""/></td>
        <td width="55%" align="left" valign="top" style="font-weight: bold; padding-top:10px; font-style: italic;">'.utf8_encode($nomeinst).'&nbsp;</td>
        <td width="40%" align="left" valign="top" style="font-size:8pt; padding-top:10px;">'.utf8_encode("MUNICÍPIO DE VOLTA REDONDA").'&nbsp;</td>
    </tr>

    <tr>
        <td width="55%" align="left" valign="top" style="font-style: italic;">'.utf8_encode($ender).'&nbsp;</td>
        <td width="40%" align="left" valign="top" style="font-size:8pt;">'.utf8_encode("Relatórios de Empenhos").'&nbsp;</td>
    </tr>   
       <tr>
        <td width="55%" align="left" valign="top" style="font-style: italic;">'.utf8_encode($munic).'&nbsp;</td>
        <td width="40%" align="left" valign="top" style="font-size:8pt;">'.utf8_encode($info).'&nbsp;</td>
    </tr>
       <tr>
        <td width="55%" align="left" valign="top" style="font-style: italic;">'.utf8_encode($telef).'&nbsp;</td>
        <td width="40%" align="left" valign="top" style="font-size:8pt;">'.utf8_encode($info2).'&nbsp;</td>
    </tr>
       <tr>
        <td width="55%" align="left" valign="top" style="font-style: italic;">'.utf8_encode($email).'&nbsp;</td>
        <td width="40%" align="left" valign="top" style="font-size:8pt;">'.utf8_encode($dadosinstit).'&nbsp;</td>
    </tr>
       <tr>
        <td width="55%" align="left" valign="top" style="font-style: italic;">'.utf8_encode($url).'&nbsp;</td>
        <td width="40%" align="left" valign="top" style="font-size:8pt;">'.utf8_encode($info3).'&nbsp;</td>
    </tr>

    </tr>
       <tr>
        <td width="5%" align="left" valign="top" style="font-style: italic;"></td>
        <td width="55%" align="left" valign="top" style="font-style: italic;"></td>
        <td width="40%" align="left" valign="top" style="font-size:8pt;">'.utf8_encode($info4).'&nbsp;</td>
    </tr>
</table>

';


/*
 * Modificação para exibir o caminho do menu 
 * na base do relatório
 */         
  //$sSqlMenuAcess = "SELECT fc_montamenu(funcao) as menu from db_itensmenu where id_item =".db_getsession("DB_itemmenu_acessado");
  $sSqlMenuAcess = " select trim(modulo.descricao)||'>'||trim(menu.descricao)||'>'||trim(item.descricao) as menu
                       from db_menu 
                      inner join db_itensmenu as modulo on modulo.id_item = db_menu.modulo 
                      inner join db_itensmenu as menu on menu.id_item = db_menu.id_item 
                      inner join db_itensmenu as item on item.id_item = db_menu.id_item_filho 
                      where id_item_filho = ".db_getsession("DB_itemmenu_acessado")." 
                        and modulo = ".db_getsession("DB_modulo");

  $rsMenuAcess   = db_query($conn,$sSqlMenuAcess);
  $sMenuAcess    = substr(pg_result($rsMenuAcess, 0, "menu"), 0, 50);

  //Position at 1.5 cm from bottom
  $nome = @$GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"];
  $nome = substr($nome,strrpos($nome,"/")+1);
  $result_nomeusu = db_query($conn, "select nome as nomeusu from db_usuarios where id_usuario =".db_getsession("DB_id_usuario"));
  if (pg_numrows($result_nomeusu)>0){
    $nomeusu = pg_result($result_nomeusu,0,0);
  }
  if (isset($nomeusu)&&$nomeusu!=""){
    $emissor = $nomeusu;
  }else{
    $emissor = @$GLOBALS["DB_login"];
  }


$footer = "<span style='font-size:5pt; font-family: arial; font-style: italic;'>".utf8_encode($sMenuAcess). "  ". utf8_encode($nome)."   Emissor: ".utf8_encode(substr(ucwords(mb_strtolower($emissor)),0,30))."  Exerc: ".db_getsession('DB_anousu').
                                            "   Data: ".date('d-m-Y',db_getsession('DB_datausu'))." - ".date('H:i:s');"</span> <div align='center'><b>{PAGENO} / {nbpg}</b></div>";
//$mpdf->setFooter('{PAGENO}');

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);

$preco_unit_ref_total_lote = 0;
$preco_quant_ref_total_lote = 0;
$preco_unit_ref_total_lote_array = array();
$preco_quant_ref_total_lote_array = array();

$txtnaopago = "";
$datasit = "";

if($ops == "p") {
    $txt_where .= " and c53_coddoc in (5,35,37) ";
    $datasit = "c75_data";
}
//else if($ops == "n") {
else if($ops == "n" || $ops == "gnp") {
    $txt_where .= " and c53_coddoc in (3,33,23,39) ";

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
    $txt_where .= "and c53_coddoc in (3,33,23,39,5,35,37)";
    //$datasit = "CURRENT_DATE";
    $datasit = "c75_data";
}



if(isset($nValorEmpenhoInicial) && $nValorEmpenhoInicial > 0)
    $txt_where .= " and c70_valor >= ".str_replace(',','.',$nValorEmpenhoInicial);

if(isset($nValorEmpenhoFinal) && $nValorEmpenhoFinal > 0)
    $txt_where .= " and c70_valor <= ".str_replace(',','.',$nValorEmpenhoFinal);

if(isset($o58_codigo) && $o58_codigo > 0)
    $txt_where .= " and orctiporec.o15_codigo = ".$o58_codigo;

//echo $txt_where;exit;
$html = "";


 
//Retirado da linha 427 
/*and e60_numemp not in (select e60_numemp
from conlancamemp
inner join conlancam on c70_codlan = c75_codlan 
left join empelemento on empelemento.e64_numemp = conlancamemp.c75_numemp 
left join empempenho on empempenho.e60_numemp = empelemento.e64_numemp 
left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan 
left OUTER join conlancampag on c82_codlan = c70_codlan 
inner join conlancamdoc on c71_codlan = c70_codlan 
inner join conhistdoc on c53_coddoc = c71_coddoc 
where e60_numemp = emp1.e60_numemp and c53_coddoc in (4,34,24,40,6,36,38))
*/

/*Empenhos*/ 
$sSqlEmpenhos = "
    select c75_codlan, e50_codord as ordem, e60_codemp ||'/'|| e60_anousu as numero_da_nota_de_empenho, z01_cgccpf, z01_nome, 
e69_numero as numero_nota_nf, e50_codord as nota_liquidacao, c75_data as data_pagamento, e50_data as data_liquidacao, c80_data as dataliqnova, c70_valor as valor,
e50_dataquebraordem AS data_pub,  CASE WHEN EXISTS (SELECT 1 FROM conlancampag where c82_codlan = c03_codlan) THEN 1 ELSE 2 END as status_pagamento, 
o56_descr,
e60_instit as unidadegestora,
case when o15_codigo is null then '-' else text(o15_codigo) end as fonterecurso,
({$datasit} - e50_data) as situacao,
case when pagordemprocesso.e03_numeroprocesso is null then '' else pagordemprocesso.e03_numeroprocesso end as processo,
sum(retencaoreceitas.e23_valorretencao) as retencoes,e50_justificativaquebraordem, e50_numedi, o40_orgao, o40_descr 
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
where e60_numemp = emp1.e60_numemp and c53_coddoc in (31))

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
group by c75_codlan, e60_codemp,e60_anousu, z01_cgccpf, z01_nome, e69_numero, 
e50_codord, c75_data, e50_data, c80_data, c70_valor, e50_dataquebraordem, 
e50_justificativaquebraordem, c03_codlan,o40_anousu,e60_emiss,
o56_descr, pagordemprocesso.e03_numeroprocesso,
e60_instit,o15_codigo,e50_justificativaquebraordem, e50_numedi, o40_orgao, o40_descr 
order by o40_anousu, e60_instit, o40_orgao, o15_codigo, e50_data
";

//ORIGINAL
//order by o40_anousu, e50_data 
//NOVO
//order by o40_anousu, e60_instit, o40_orgao, o15_codigo, e50_data

//ORIGINAL
//sum(retencaoreceitas.e23_valorretencao) as retencoes,e50_justificativaquebraordem, e50_numedi 
//NOVO
//sum(retencaoreceitas.e23_valorretencao) as retencoes,e50_justificativaquebraordem, e50_numedi, o40_orgao, o40_descr 

//ORIGINAL
//e60_instit,o15_codigo,e50_justificativaquebraordem, e50_numedi
//NOVO
//e60_instit,o15_codigo,e50_justificativaquebraordem, e50_numedi, o40_orgao, o40_descr 



//var_dump($sSqlEmpenhos); die("SQL");


$mpdf->WriteHTML($html);
//var_dump($sSqlEmpenhos); die("Verifica SQL");
//echo $sSqlEmpenhos; exit;
//SQL do Relatório
$rsEmpenhos = pg_query($sSqlEmpenhos);
/*
var_dump($sSqlEmpenhos);
echo "<pre>";
$x = pg_fetch_all($rsEmpenhos);
print_r($x);
echo "</pre>";
die("Verifica");
*/
//$lanlan = array();

if (pg_num_rows($rsEmpenhos) == 0) {
  db_redireciona('db_erros.php?fechar=true&db_erro=Nenhum registro retornado.');
}

$palavra = ($ops == "n") ? "Ordem" : "Link";

$html1 = ' <table style="width: 100%; border: 1px solid black; border-collapse: collapse">   
             <thead>
                <tr bgcolor="#EBEBEB">
                    <td align="center" style="width:8;border: 1px solid black; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Unidade Gestora").'</td>
                    <td align="center" style="border: 1px solid black; width: 10; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Fonte de Recurso").'</td>
                    <td align="center" style="border: 1px solid black; width: 6; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Nº do Empenho").'</td>
                    <td align="center" style="border: 1px solid black; width: 15; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Razão Social.").'</td>
                    <td align="center" style="border: 1px solid black; width: 10; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Nº Nota de Liq.").'</td>
                    <td align="center" style="border: 1px solid black; width: 10; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Data Adimplemento").'</td>
                    <td align="center" style="border: 1px solid black; width: 10; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Data Liquidação").'</td>
                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Data Pagamento.").'</td> 
                    <td align="center" style="border: 1px solid black; width: 4; font-weight: bold;  font-size: 8pt;">Valor</td> 
                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Data Publicação").'</td>
                    <td align="center" style="border: 1px solid black; width: 7; font-weight: bold;  font-size: 8pt;">'.utf8_encode($palavra).'</td>
                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold; font-size: 8pt;">'.utf8_encode("Nº do Processo").'</td>
                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold; font-size: 8pt;">'.utf8_encode("Retenção").'</td>
                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold; font-size: 8pt;">'.utf8_encode("Situação").'</td>
                    
                </tr>
                </thead>';
 
if($ops == "gnp"){
  $html1 = ' <table style="width: 100%; border: 1px solid black; border-collapse: collapse">   
             <thead>
                <tr bgcolor="#EBEBEB">
                    <td align="center" style="width:8;border: 1px solid black; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Órgão").'</td>
                    <td align="center" style="border: 1px solid black; width: 10; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Fonte de Recurso").'</td>
                    <td align="center" style="border: 1px solid black; width: 6; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Nº do Empenho").'</td>
                    <td align="center" style="border: 1px solid black; width: 15; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Razão Social.").'</td>
                    <td align="center" style="border: 1px solid black; width: 10; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Nº OP").'</td>
                    <td align="center" style="border: 1px solid black; width: 10; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Data Adimplemento").'</td>
                    <td align="center" style="border: 1px solid black; width: 10; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Data Liquidação").'</td>
                    
                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Data Pagamento.").'</td> 
                    <td align="center" style="border: 1px solid black; width: 4; font-weight: bold;  font-size: 8pt;">Valor</td> 
                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Data Publicação").'</td>
                    <td align="center" style="border: 1px solid black; width: 7; font-weight: bold;  font-size: 8pt;">'.utf8_encode("Ordem").'</td>

                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold; font-size: 8pt;">'.utf8_encode("Nº do Processo").'</td>
                    
                    <td align="center" style="border: 1px solid black; width: 9; font-weight: bold; font-size: 8pt;">'.utf8_encode("Situação").'</td>
                    
                </tr>
                </thead>';
}
 $mpdf->WriteHTML($html1);

//db_criatabela($rsEmpenhos); exit;
 
 $valorTotal = 0;
 
$array = array();
$novoarray = array();

//var_dump($rsEmpenhos); exit;

//print(pg_num_rows($rsEmpenhos));exit;
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

/*function verificaLancamento($ordem){
  $sql = pg_query("SELECT c70_codlan, c70_data, c53_coddoc as dl_coddoc, c53_descr, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord , e50_data from conlancamemp inner join conlancam on c70_codlan = c75_codlan left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord where e50_codord = {$ordem} AND c53_coddoc in (4, 34, 24, 40, 6, 36, 38, 2, 31, 82, 83) order by c75_data, c03_ordem, c75_codlan;");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}*/

function verificaLancamento($ordem){
  $sql = pg_query("SELECT * FROM conlancamord INNER JOIN conlancamdoc ON c71_codlan = c80_codlan AND c71_coddoc in (4, 34, 24, 40, 6, 36, 38, 2, 31, 82, 83) WHERE c80_codord = {$ordem}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}






for ($iRow = 0; $iRow < pg_num_rows($rsEmpenhos); $iRow++) {
  $oEmpenhos = db_utils::fieldsMemory($rsEmpenhos, $iRow);
  //data_pagamento - c80_data - where  conhistdoc.c53_coddoc = 5

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
      where {$txt_wherepg} and c53_coddoc in (5,35,37) and empempenho.e60_relatorio = true

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
      //var_dump($sSqlEmpenhospagos); die("SQL");

      if($pag > 0){
        //die("Tem que entrar aqui");
        $oEmppag = db_utils::fieldsMemory($rsEmpenhospagos, 0);
        $data_pagamento =$oEmppag->data_pagamento;
        $sit = $oEmppag->situacao;
      } else {
        //die("Se entrou aqui é porque o sql inexiste");
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
            //$justificativa = "http://new.voltaredonda.rj.org.br/images/Documentos/VRDestaques/" . $ano . "/" . $dataquebra . "_" . $numedi . ".pdf";
          }
        }
      }
    }//if $ops == "t"
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
    }//else do ops != de t 

    
        
    $sit2 = contaData($oEmpenhos->dataliqnova);
    $novaData = buscaDataAgendamento($oEmpenhos->ordem);        
        
    $verificador = verificaLancamento($oEmpenhos->ordem);        
    if(!$verificador){
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
            $oEmpenhos->dataliqnova, //18 //Errada
            $oEmpenhos->o40_orgao, //19
            $oEmpenhos->o40_descr //20
          );  
    }//Verificador        
}//Fim do for

//$novoarray2 = array();
if($ops == "gnp"){  
  foreach ($novoarray as $key => $part) {
       $sort[$key] = strtotime($part[18]);
  }
  array_multisort($sort, SORT_ASC, $novoarray);
}


if($ops !="gnp"){
  $sort = array();
  foreach ($novoarray as $key => $part) {       
    $sort["instituicao"][$key] = $part[13];
    $sort["orgao"][$key] = $part[19];
    $sort["fonte"][$key] = $part[14];
  }
  array_multisort($sort["instituicao"], SORT_ASC, $sort["orgao"], SORT_ASC, $sort["fonte"], SORT_ASC, $novoarray);
}

if($ops =="n"){    
  $cl = 1;
  $guardaorgao = "";
  $guardafonte = "";
  $totorgao = 0;
  foreach ($novoarray as $result) {       
    if($result[19] != $guardaorgao){
      if($guardaorgao != ""){        

        $mpdf->WriteHTML('<tr><td colspan="14">TOTAL:'.number_format($totorgao, 2, ",", ".").'</td></tr>');
        $guardafonte = "";        
        $mpdf->WriteHTML('</table>');
        $mpdf->AddPage();
        $mpdf->WriteHTML($html1);
      }
      
      $totorgao = 0;      
      $html1x = '<tr><td align="center" colspan="14">'.utf8_encode($result[19]).' - '.utf8_encode($result[20]).'</td></tr>';  

      $kql = db_query("select o15_descr from orcamento.orctiporec where o15_codigo = ".$result[14]);
      $descrec = pg_result($kql,0,"o15_descr");
      $linharec = $result[14] ." - ". utf8_encode($descrec);
      if($result[14] != $guardafonte){
        $html1x .= '<tr><td align="left" colspan="14">'.$linharec.'</td></tr>';
        $guardafonte = $result[14];
      }
    }

    if($result[14] != $guardafonte){
      $kql2 = db_query("select o15_descr from orcamento.orctiporec where o15_codigo = ".$result[14]);
      $descrec2 = pg_result($kql2,0,"o15_descr");
      $linharec2 = $result[14] ." - ". utf8_encode($descrec2);
      $htmlw = '<tr><td align="left" colspan="14">'.$linharec2.'</td></tr>';
      $mpdf->WriteHTML($htmlw);
      $guardafonte = $result[14];
  }

    $html2 = '
    <tr >
            <td align="center" style="width:8;border: 1px solid black;  font-size: 8pt;">'.$result[13].'</td>
            <td align="center" style="border: 1px solid black; width: 10;  font-size: 8pt;">'.$result[14].'</td>
            <td align="center" style="border: 1px solid black; width: 6;  font-size: 8pt;">'.$result[1].'</td>
            <td align="center" style="border: 1px solid black; width: 15;  font-size: 8pt;">'.utf8_encode($result[3]).'</td>
            <td align="center" style="border: 1px solid black; width: 10;  font-size: 8pt;">'.$result[5].'</td>
            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.db_formatar($result[6], "d").'</td>

            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.db_formatar($result[18], "d").'</td>

            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.db_formatar($result[7], "d").'</td> 
            <td align="center" style="border: 1px solid black; width: 4;font-size: 8pt;">'. number_format($result[8], 2, ",", ".").'</td>
            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.db_formatar($result[9], "d").'</td>            
            <td align="center" style="border: 1px solid black; width: 7; font-size: 8pt;">'.$cl.'</td>
            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.utf8_encode($result[16]).'</td>
            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.number_format($result[17], 2, ",", ".").'</td>
            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.utf8_encode($result[15]).'</td>
            </tr>';
           
          $valorTotal += $result[8];
          $cl++;
          
          if($result[19] != $guardaorgao){
            $mpdf->WriteHTML($html1x);
          }
          $totorgao += $result[8];
          $guardaorgao = $result[19];
          $mpdf->WriteHTML($html2);          
          
        }//end foreach
        $html1f = '<tr><td colspan="14">TOTAL:'.number_format($totorgao, 2, ",", ".").'</td></tr>';
        $mpdf->WriteHTML($html1f);
} elseif($ops =="gnp"){  
  $cl = 1;
  $guardaorgao = "";
  $guardafonte = "";
  $totorgao = 0;
  foreach ($novoarray as $result) {       
    
    

    $html2 = '
    <tr >
            <td align="center" style="width:8;border: 1px solid black;  font-size: 8pt;">'.$result[19].'</td>
            <td align="center" style="border: 1px solid black; width: 10;  font-size: 8pt;">'.$result[14].'</td>
            <td align="center" style="border: 1px solid black; width: 6;  font-size: 8pt;">'.$result[1].'</td>
            <td align="center" style="border: 1px solid black; width: 15;  font-size: 8pt;">'.utf8_encode($result[3]).'</td>
            <td align="center" style="border: 1px solid black; width: 10;  font-size: 8pt;">'.$result[5].'</td>
            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.db_formatar($result[6], "d").'</td>

            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.db_formatar($result[18], "d").'</td>
            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.db_formatar($result[7], "d").'</td> 

            
            <td align="center" style="border: 1px solid black; width: 4;font-size: 8pt;">'. number_format($result[8], 2, ",", ".").'</td>
            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.db_formatar($result[9], "d").'</td>            
            <td align="center" style="border: 1px solid black; width: 7; font-size: 8pt;">'.$cl.'</td>

            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.utf8_encode($result[16]).'</td>
            
            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.utf8_encode($result[15]).'</td>
            </tr>';
           
          $valorTotal += $result[8];
          $cl++;
          
          
          
          $mpdf->WriteHTML($html2);          
          
        }//end foreach
        
}else {    
  $guardaorgao2 = "";
  $guardafonte2 = "";
  $totorgao = 0;
  foreach ($novoarray as $result) {

    if($result[19] != $guardaorgao2){
      if($guardaorgao2 != ""){        
        $mpdf->WriteHTML('<tr><td colspan="14">TOTAL:'.number_format($totorgao2, 2, ",", ".").'</td></tr>');
        $guardafonte2 = "";        
        $mpdf->WriteHTML('</table>');
        $mpdf->AddPage();
        $mpdf->WriteHTML($html1);
      }
      
      $totorgao2 = 0;
      $html1x = '<tr><td align="center" colspan="14">'.utf8_encode($result[19]).' - '.utf8_encode($result[20]).'</td></tr>';  
      
      
      $kql = db_query("select o15_descr from orcamento.orctiporec where o15_codigo = ".$result[14]);
      $descrec = pg_result($kql,0,"o15_descr");
      $linharec = $result[14] ." - ". utf8_encode($descrec);
      if($result[14] != $guardafonte2){
        $html1x .= '<tr><td align="left" colspan="14">'.$linharec.'</td></tr>';
        $guardafonte2 = $result[14];
      }
    }
  
  if($result[14] != $guardafonte2){
      $kql2 = db_query("select o15_descr from orcamento.orctiporec where o15_codigo = ".$result[14]);
      $descrec2 = pg_result($kql2,0,"o15_descr");
      $linharec2 = $result[14] ." - ". utf8_encode($descrec2);
      $htmlw = '<tr><td align="left" colspan="14">'.$linharec2.'</td></tr>';
      $mpdf->WriteHTML($htmlw);
      $guardafonte2 = $result[14];
  }
    
    
    $html2 = '<tr >
            <td align="center" style="width:8;border: 1px solid black;  font-size: 8pt;">'.$result[13].'</td>
            <td align="center" style="border: 1px solid black; width: 10;  font-size: 8pt;">'.$result[14].'</td>
            <td align="center" style="border: 1px solid black; width: 6;  font-size: 8pt;">'.$result[1].'</td>
            <td align="center" style="border: 1px solid black; width: 15;  font-size: 8pt;">'.utf8_encode($result[3]).'</td>
            <td align="center" style="border: 1px solid black; width: 10;  font-size: 8pt;">'.$result[5].'</td>
            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.db_formatar($result[6], "d").'</td>

            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.db_formatar($result[18], "d").'</td>

            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.db_formatar($result[7], "d").'</td> 
            <td align="center" style="border: 1px solid black; width: 4;font-size: 8pt;">'. number_format($result[8], 2, ",", ".").'</td>
            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.db_formatar($result[9], "d").'</td>            
            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.utf8_encode($result[12]).'</td>
            <td align="center" style="border: 1px solid black; width: 9; font-size: 8pt;">'.utf8_encode($result[16]).'</td>
            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.number_format($result[17], 2, ",", ".").'</td>
            <td align="center" style="border: 1px solid black; width: 9;  font-size: 8pt;">'.utf8_encode($result[15]).'</td>
            </tr>';
           
          $valorTotal += $result[8];
          $cl++;
          if($result[19] != $guardaorgao2){
            $mpdf->WriteHTML($html1x);              
          }
          $totorgao2 += $result[8];
          $guardaorgao2 = $result[19];
          $mpdf->WriteHTML($html2);
        }//end foreach
        $html1f = '<tr><td colspan="14">TOTAL:'.number_format($totorgao2, 2, ",", ".").'</td></tr>';
        $mpdf->WriteHTML($html1f);
}//Fim do else ops != n 

        
        $html3='
        <tr>
        <td colspan="7" style="border-left:1px;
	border-right:1px solid black;
	border-bottom:1px solid black;
	border-top:1px solid black;"></td>
        <td align="" bgcolor="#EBEBEB">TOTAIS:</td>
        <td bgcolor="#EBEBEB" style="border-right:1px solid black;">'.number_format($valorTotal, 2, ",", ".").' </td>
        </tr>';
        
        $html3.='</table>';
        $html3 .= '<br>';


$mpdf->WriteHTML($html3);
$mpdf->Output();
ob_clean();


exit();
