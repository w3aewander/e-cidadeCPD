<?
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
require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("classes/db_liclicitem_classe.php");
include("dbforms/db_funcoes.php");
db_postmemory($HTTP_POST_VARS, 2);
$clliclicitem = new cl_liclicitem;
$clrotulo = new rotulocampo;
$clrotulo->label("l20_codigo");

// Funcao Locao para formatar um campo
function lic2_relitenhtml002_formatar($valor, $separador, $delimitador) {
  $del="";
  $valor = str_replace("\"","",$valor);
  if($delimitador=="1") {
    $del = "\"";
  }else if($delimitador == "2") {
    $del = "'";
  }
  $valor = str_replace("\n"," ",$valor);
  $valor = str_replace("\r"," ",$valor);
  return "{$del}{$valor}{$del}{$separador}";
}

$sql = "select l20_codigo, l20_codtipocom, l20_numero, l20_id_usucria, l20_datacria, l20_horacria, l20_dataaber, l20_dtpublic, l20_horaaber, l20_local, l20_objeto, l20_tipojulg, l20_liccomissao, l20_liclocal, l20_procadmin, l20_correto, l20_instit, l20_licsituacao, l20_edital, l20_anousu, l20_usaregistropreco, l20_localentrega, l20_prazoentrega, l20_condicoespag, l20_validadeproposta from liclicita where l20_codigo = $l20_codigo";
$result_licita = $clliclicitem->sql_record($sql);

$clabre_arquivo = new cl_abre_arquivo("/tmp/licitacao_$l20_codigo.csv");

if ($clabre_arquivo->arquivo == false) {
  db_redireciona('db_erros.php?fechar=true&db_erro=Erro ao criar arquivo TXT.');
  exit;
}

$vir = $separador;
$del = $delimitador;

for($i=0;$i<count($result_licita);$i++){
  
  if ( $i == 0 ){
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar("00", $vir, $del));
    for($ii=0;$ii<pg_num_fields($result_licita);$ii++){

      $campo =  pg_field_name($result_licita,$ii);
      fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($campo, $vir, $del));

    }
    fputs($clabre_arquivo->arquivo, "\n");
  
  }

  fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar("01", $vir, $del));
  for($ii=0;$ii<pg_num_fields($result_licita);$ii++){

    $campo =  pg_result($result_licita,$i,$ii);
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($campo, $vir, $del));

  }

  fputs($clabre_arquivo->arquivo, "\n");

}

//$campos = "l21_codigo,l21_ordem,pc11_codigo,pc01_descrmater,pc11_resum,m61_descr,pc11_quant,pc11_vlrun,pcprocitem.pc81_codprocitem";
//$campos = "distinct ".$campos;

//$result_itens = $clliclicitem->sql_record($clliclicitem->sql_query_inf(null, "$campos", "l21_ordem", "l21_codliclicita = $l20_codigo and l20_instit = ".db_getsession("DB_instit")));

 

$sql = "select l21_codigo, l21_codliclicita, l21_codpcprocitem, l21_situacao, l21_ordem, l20_codigo, l20_codtipocom, l20_numero, l20_id_usucria, l20_datacria, l20_horacria, l20_dataaber, l20_dtpublic, l20_horaaber, l20_local, l20_objeto, l20_tipojulg, l20_liccomissao, l20_liclocal, l20_procadmin, l20_correto, l20_instit, l20_licsituacao, l20_edital, l20_anousu, l20_usaregistropreco, l20_localentrega, l20_prazoentrega, l20_condicoespag, l20_validadeproposta, l03_codigo, l03_descr, l03_tipo, l03_codcom, l03_instit, l03_usaregistropreco, l03_pctipocompratribunal, pc50_codcom, pc50_descr, pc50_pctipocompratribunal, pc81_codprocitem, pc81_codproc, pc81_solicitem, pc80_codproc, pc80_data, pc80_usuario, pc80_depto, pc80_resumo, pc80_situacao, pc11_codigo, pc11_numero, pc11_seq, pc11_quant, pc11_vlrun, pc11_prazo, pc11_pgto, pc11_resum, pc11_just, pc11_liberado, pc11_servicoquantidade, pc10_numero, pc10_data, pc10_resumo, pc10_depto, pc10_log, pc10_instit, pc10_correto, pc10_login, pc10_solicitacaotipo, coddepto, descrdepto, nomeresponsavel, emailresponsavel, limite, fonedepto, emaildepto, faxdepto, ramaldepto, instit, id_usuario, nome, login, senha, usuarioativo, email, usuext, administrador, pc17_unid, pc17_quant, pc17_codigo, m61_codmatunid, m61_descr, m61_usaquant, m61_abrev, m61_usadec, pc16_codmater, pc16_solicitem, pc01_codmater, pc01_descrmater, pc01_complmater, pc01_codsubgrupo, pc01_ativo, pc01_conversao, pc01_id_usuario, pc01_libaut, pc01_servico, pc01_veiculo, pc01_validademinima, pc01_obrigatorio, pc01_fraciona, pc01_liberaresumo, pc18_solicitem, pc18_codele, o56_codele, o56_anousu, o56_elemento, o56_descr, o56_finali, o56_orcado, l07_codigo, l07_usuario, l07_data, l07_hora, l07_motivo, l07_liclicitem, l08_sequencial, l08_descr, l08_altera, l04_codigo, l04_liclicitem, l04_descricao, ";
$sql .= "(  select round(avg(pc23_vlrun),2) 
            from pcorcamval 
            inner join pcorcamforne     on pcorcamforne.pc21_orcamforne = pcorcamval.pc23_orcamforne 
            inner join pcorcamitem      on pcorcamitem.pc22_orcamitem = pcorcamval.pc23_orcamitem
            inner join pcorcamitemproc  on pcorcamitem.pc22_orcamitem = pcorcamitemproc.pc31_orcamitem
            inner join cgm              on cgm.z01_numcgm = pcorcamforne.pc21_numcgm 
            inner join pcorcam          on pc20_codorc = pcorcamitem.pc22_codorc 
            left  join pcorcamjulg      on pcorcamjulg.pc24_orcamitem = pcorcamval.pc23_orcamitem and pcorcamjulg.pc24_orcamforne=pcorcamval.pc23_orcamforne 
            where pcorcamitemproc.pc31_pcprocitem = pcprocitem.pc81_codprocitem ) as valor_unit_medio_orcamento_pc ";
$sql .= " from liclicitem
      inner join liclicita        on liclicita.l20_codigo            = liclicitem.l21_codliclicita
      inner join cflicita         on cflicita.l03_codigo             = liclicita.l20_codtipocom
      inner join pctipocompra     on pctipocompra.pc50_codcom        = cflicita.l03_codcom
      inner join pcprocitem       on liclicitem.l21_codpcprocitem    = pcprocitem.pc81_codprocitem
      inner join pcproc           on pcproc.pc80_codproc             = pcprocitem.pc81_codproc
      inner join solicitem        on solicitem.pc11_codigo           = pcprocitem.pc81_solicitem
      inner join solicita         on solicita.pc10_numero            = solicitem.pc11_numero
      inner join db_depart        on db_depart.coddepto              = solicita.pc10_depto
      inner join db_usuarios      on solicita.pc10_login             = db_usuarios.id_usuario
      left  join solicitemunid    on solicitemunid.pc17_codigo       = solicitem.pc11_codigo
      left  join matunid          on matunid.m61_codmatunid          = solicitemunid.pc17_unid
      left  join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo
      left  join pcmater          on pcmater.pc01_codmater           = solicitempcmater.pc16_codmater
      left  join solicitemele     on solicitemele.pc18_solicitem     = solicitem.pc11_codigo
      left  join orcelemento      on solicitemele.pc18_codele        = orcelemento.o56_codele and orcelemento.o56_anousu = ".db_getsession("DB_anousu")."
      left  join liclicitemanu    on liclicitemanu.l07_liclicitem    = liclicitem.l21_codigo
      left  join licsituacao      on licsituacao.l08_sequencial    = liclicita.l20_licsituacao
      left  join liclicitemlote   on liclicitemlote.l04_liclicitem = liclicitem.l21_codigo ";

$result_itens = $clliclicitem->sql_record($sql."where l21_codliclicita = $l20_codigo and l20_instit = ".db_getsession("DB_instit"));

if ($clliclicitem->numrows == 0) {
  db_redireciona('db_erros.php?fechar=true&db_erro=Não existem Itens cadastrados para Licitação.');
  exit;
}

for($i=0;$i<pg_num_rows($result_itens);$i++){
  
  if ( $i == 0 ){
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar("02", $vir, $del));
    for($ii=0;$ii<pg_num_fields($result_itens);$ii++){

      $campo =  pg_field_name($result_itens,$ii);
      fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($campo, $vir, $del));

    }
    fputs($clabre_arquivo->arquivo, "\n");
  
  }

  fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar("03", $vir, $del));
  for($ii=0;$ii<pg_num_fields($result_itens);$ii++){

    $campo =  pg_result($result_itens,$i,$ii);
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($campo, $vir, $del));

  }

  fputs($clabre_arquivo->arquivo, "\n");

}



$sql = "select distinct pc20_codorc,cgm.*,liclicitatipoempresa.*
from liclicita 
inner join db_usuarios  on  db_usuarios.id_usuario = liclicita.l20_id_usucria
inner join cflicita  on  cflicita.l03_codigo = liclicita.l20_codtipocom
inner join db_config  on  db_config.codigo = cflicita.l03_instit
inner join pctipocompra  on  pctipocompra.pc50_codcom = cflicita.l03_codcom
inner join liclicitem on liclicitem.l21_codliclicita = liclicita.l20_codigo
inner join pcorcamitemlic on pcorcamitemlic.pc26_liclicitem = liclicitem.l21_codigo
inner join pcorcamitem on pcorcamitemlic.pc26_orcamitem = pcorcamitem.pc22_orcamitem
inner join pcorcam on pcorcam.pc20_codorc = pcorcamitem.pc22_codorc
inner join pcorcamforne on pc21_codorc = pc20_codorc 
inner join cgm on z01_numcgm = pc21_numcgm
left join pcorcamfornelic on pc21_orcamforne = pc31_orcamforne
left join liclicitatipoempresa on pc31_liclicitatipoempresa = l32_sequencial
where l20_codigo = $l20_codigo";
$result_forne = $clliclicitem->sql_record($sql);

//die("x: " . count($result_forne));

for($i=0;$i<pg_num_rows($result_forne);$i++){
  
  if ( $i == 0 ){
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar("04", $vir, $del));
    for($ii=0;$ii<pg_num_fields($result_forne);$ii++){

      $campo =  pg_field_name($result_forne,$ii);
      fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($campo, $vir, $del));

    }
    fputs($clabre_arquivo->arquivo, "\n");
  
  }

  fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar("05", $vir, $del));
  for($ii=0;$ii<pg_num_fields($result_forne);$ii++){

    $campo =  pg_result($result_forne,$i,$ii);
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($campo, $vir, $del));

  }

  fputs($clabre_arquivo->arquivo, "\n");

}

/*

$clabre_arquivo = new cl_abre_arquivo("/tmp/licitacao_$l20_codigo.csv");


if ($clabre_arquivo->arquivo != false) {
  $vir = $separador;
  $del = $delimitador;

  fputs($clabre_arquivo->arquivo, '01');

  

  fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar("QUANT.",   $vir, $del));

  

  
  for ($w = 0; $w < $clliclicitem->numrows; $w ++) {
    db_fieldsmemory($result_itens, $w);


    fputs($clabre_arquivo->arquivo, '01');
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($l21_ordem                        , $vir, $del));
    if ( $layout != 2 ) {
      fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($pc81_codprocitem                 , $vir, $del));
    }
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar("{$pc01_descrmater} {$pc11_resum}", $vir, $del));
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($pc11_quant                       , $vir, $del));
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($m61_descr                        , $vir, $del));
    fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar(db_formatar($pc11_vlrun,"v")      , $vir, $del));

    // ve se tem lote
    $sql = " select * from liclicitemlote where l04_liclicitem = $l21_codigo";
    $res = pg_query($sql);
    if( pg_num_rows($res) > 0 ){
      db_fieldsmemory($res,0);
      fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($l04_descricao , $vir, $del));

    }

     // ve se tem lote
    $sql = " select * from liclicitemlote where l04_liclicitem = $l21_codigo";
    $res = pg_query($sql);
    if( pg_num_rows($res) > 0 ){
      db_fieldsmemory($res,0);
      fputs($clabre_arquivo->arquivo, lic2_relitenhtml002_formatar($l04_descricao , $vir, $del));

    }

      




    fputs($clabre_arquivo->arquivo, "\n");

  }
*/ 
  fclose($clabre_arquivo->arquivo);

  echo "<script>";
  echo "  jan = window.open('db_download.php?arquivo=".$clabre_arquivo->nomearq."','','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');";
  echo "  jan.moveTo(0,0);";
  echo "</script>";

//}

?>
