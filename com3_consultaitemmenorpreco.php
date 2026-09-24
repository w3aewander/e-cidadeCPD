<?
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

require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("classes/db_solicitem_classe.php");
include("classes/db_empautitem_classe.php");
include("classes/db_empempitem_classe.php");
include("dbforms/db_funcoes.php");
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
db_postmemory($HTTP_POST_VARS);
$clsolicitem = new cl_solicitem;
$clempautitem = new cl_empautitem;
$clempempitem = new cl_empempitem;
$clrotulo = new rotulocampo;
$clrotulo->label("");
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
<script>
</script>
<style>
<?//$cor="#999999"?>
.bordas{
    border: 2px solid #cccccc;
    border-top-color: #999999;
    border-right-color: #999999;
    border-left-color: #999999;
    border-bottom-color: #999999;
    background-color: #999999;
}
.bordas_corp{
    border: 1px solid #cccccc;
    border-top-color: #999999;
    border-right-color: #999999;
    border-left-color: #999999;
    border-bottom-color: #999999;
    background-color: #cccccc;
}
</style>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0"> 
<table  border="0" cellspacing="0" cellpadding="0" width='100%'>
<tr> 
<td  align="center" valign="top" > 

<table border='0'>  
  <tr>
    <td colspan=6 align=center>
    <br>
    <br>
    <br>
    </td>
  </tr>
<?
db_input("mostra",10,"",true,"hidden",3);
db_input("pc01_codmater",10,"",true,"hidden",3);

$sql = "
select pc20_codorc, /*pc11_codigo,*/ trim(pc11_resum) as pc11_resum, pc20_dtate as dl_data_orcamento, z01_nome, z01_telef, z01_email, pc23_vlrun, pc10_instit, nomeinstabrev
from pcorcamitemsol 
inner join pcorcamitem on pc22_orcamitem = pc29_orcamitem 
inner join pcorcam on pc20_codorc = pc22_codorc 
inner join pcorcamjulg on pc29_orcamitem = pc24_orcamitem 
inner join pcorcamforne on pc21_orcamforne = pc24_orcamforne and pc21_codorc = pc22_codorc 
inner join cgm on z01_numcgm = pc21_numcgm 
inner join solicitem on pc11_codigo = pc29_solicitem 
inner join solicitempcmater on pc11_codigo = pc16_solicitem 
inner join pcmater on pc01_codmater = pc16_codmater 
inner join pcorcamval on pc21_orcamforne = pc23_orcamforne and pc22_orcamitem = pc23_orcamitem 
inner join solicita on pc10_numero = pc11_numero 
inner join db_config on codigo = pc10_instit 
where pc16_codmater = $pc01_codmater
and pc20_dtate >= current_date - '90 days'::interval 
and pc24_pontuacao = 1 
and pc23_orcamforne = ( select i.pc23_orcamforne from pcorcamitemsol a
inner join pcorcamitem b on b.pc22_orcamitem = a.pc29_orcamitem 
inner join pcorcam c on c.pc20_codorc = b.pc22_codorc 
inner join pcorcamjulg d on a.pc29_orcamitem = d.pc24_orcamitem 
inner join pcorcamforne e on e.pc21_orcamforne = d.pc24_orcamforne and e.pc21_codorc = b.pc22_codorc 
inner join solicitem f on f.pc11_codigo = a.pc29_solicitem 
inner join solicitempcmater g on f.pc11_codigo = g.pc16_solicitem 
inner join pcmater h on h.pc01_codmater = g.pc16_codmater 
inner join pcorcamval i on e.pc21_orcamforne = i.pc23_orcamforne and b.pc22_orcamitem = i.pc23_orcamitem 
inner join solicita j on j.pc10_numero = f.pc11_numero 
where g.pc16_codmater = solicitempcmater.pc16_codmater and d.pc24_pontuacao = 1 
and trim(f.pc11_resum) = trim(solicitem.pc11_resum)
and c.pc20_dtate >= current_date - '90 days'::interval
order by i.pc23_vlrun, c.pc20_dtate desc limit 1 )
and pc23_orcamitem = ( select i.pc23_orcamitem from pcorcamitemsol a
inner join pcorcamitem b on b.pc22_orcamitem = a.pc29_orcamitem 
inner join pcorcam c on c.pc20_codorc = b.pc22_codorc 
inner join pcorcamjulg d on a.pc29_orcamitem = d.pc24_orcamitem 
inner join pcorcamforne e on e.pc21_orcamforne = d.pc24_orcamforne and e.pc21_codorc = b.pc22_codorc 
inner join solicitem f on f.pc11_codigo = a.pc29_solicitem 
inner join solicitempcmater g on f.pc11_codigo = g.pc16_solicitem 
inner join pcmater h on h.pc01_codmater = g.pc16_codmater 
inner join pcorcamval i on e.pc21_orcamforne = i.pc23_orcamforne and b.pc22_orcamitem = i.pc23_orcamitem 
inner join solicita j on j.pc10_numero = f.pc11_numero 
where g.pc16_codmater = solicitempcmater.pc16_codmater and d.pc24_pontuacao = 1
and c.pc20_dtate >= current_date - '90 days'::interval
and trim(f.pc11_resum) = trim(solicitem.pc11_resum)
order by i.pc23_vlrun, c.pc20_dtate desc limit 1 )

order by 3,4";

db_lovrot(@$sql,15,"()","","");
?>     
</table>

</td>
</tr>
</table>
<script>
</script>
</body>
</html>
