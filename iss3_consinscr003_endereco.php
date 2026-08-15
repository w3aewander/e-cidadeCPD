<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

if ($inscricao != "") {
  $sql = "select q205_dest as destinatario, q205_codigo as codigo, q205_cep as cep, ";
  $sql .= " q205_bairro as bairro, q205_rua as rua, q205_bairronome as bairronome, ";
  $sql .= " q205_ruanome as ruanome, q205_num as numero, q205_compl as complemento, ";
  $sql .= " q205_dest as destinatario, q205_municipal as municipal, q205_atualizado as atualizado, ";
  $sql .= " db_usuarios.nome ";
  $sql .= " from issbaseendereco ";
  $sql .= " left join issqn.issbase on issbase.q02_inscr = issbaseendereco.q205_inscr ";
  $sql .= " inner join db_usuarios on id_usuario = issbaseendereco.q205_usuario ";
  $sql .= " inner join cgm on cgm.z01_numcgm = issbase.q02_numcgm ";
  $sql .= " where issbaseendereco.q205_inscr = " . (int) $inscricao;

  $result = db_query($sql);
  $num_rows = pg_num_rows($result);

  if ($num_rows != 0) {
    db_fieldsmemory($result, 0);

    if ($municipal == 't') {
      $sql = "select ruastipo.j88_sigla || ' ' || ruas.j14_nome as ruanome from ruas left join ruastipo on j88_codigo = j14_tipo where ruas.j14_codigo = $rua";
      db_fieldsmemory(db_query($sql), 0);
      $sql = "select j13_descr as bairronome from bairro where bairro.j13_codi = $bairro";
      db_fieldsmemory(db_query($sql), 0);
    }
  }
}

$endereco = "$ruanome, $numero";
if ($municipal == 't') {
  $endereco = "$ruanome, $numero";
}

$data = new DateTime($atualizado);
$dataAtualizacao = $data->format('d/m/Y \À\S H:i');
?>

<html>
<head>
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script type="text/javascript" src="scripts/widgets/windowAux.widget.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

  <?php if ($num_rows == 0): ?>
    <p style="text-align:center; margin-top:40px;">Nenhum endereço de entrega cadastrado</p>

  <?php else: ?>
  <table width="450" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr bgcolor="#CCCCCC">
      <td colspan="4" align="center">
        <font color="#333333">
          <strong style="margin-bottom: 5px; display: block;">
            ENDEREÇO DE ENTREGA
          </strong>
        </font>
      </td>
    </tr>
    <tr>
      <td class="secondcolumn" align="right" nowrap bgcolor="#CCCCCC">Destinatário: </td>
      <td width="300" align="left" nowrap bgcolor="#FFFFFF">
        <font color="#666666"><strong class="data"> <?= $destinatario ?> </strong></font>
      </td>
      <td class="secondcolumn" align="right" nowrap bgcolor="#CCCCCC">Municipal: </td>
        <td width="100" nowrap bgcolor="#FFFFFF">
          <font color="#666666"><strong class="data"> <?= $municipal == 't'? 'Sim': 'Não' ?> </strong></font>
      </td>
    </tr>
    <tr>
      <td align="right" nowrap bgcolor="#CCCCCC">Endereço de Entrega: </td>
      <td width="300" nowrap bgcolor="#FFFFFF">
        <font color="#666666"><strong class="data"> <?= $endereco ?> </strong></font>
      </td>
      <td align="right" nowrap bgcolor="#CCCCCC">CEP: </td>
        <td width="100" nowrap bgcolor="#FFFFFF">
          <font color="#666666"><strong class="data"> <?= $cep ?> </strong></font>
      </td>
    </tr>
    <tr>
        <td align="right" nowrap bgcolor="#CCCCCC">Bairro: </td>
        <td width="300" nowrap bgcolor="#FFFFFF">
          <font color="#666666"><strong class="data"> <?= $bairronome ?> </strong></font>
        </td>
    </tr>
    <tr>
      <td align="right" nowrap bgcolor="#CCCCCC">Complemento: </td>
      <td width="300" align="left" nowrap bgcolor="#FFFFFF">
        <font color="#666666"><strong class="data"> <?= $complemento ?> </strong></font>
      </td>
    </tr>
    <tr style="margin-top: 34px;">
      <td align="right" nowrap bgcolor="#CCCCCC">Atualizado por: </td>
      <td width="300" nowrap bgcolor="#FFFFFF">
        <font color="#666666"><strong class="data"><?= $nome ?> - <?= $dataAtualizacao ?></strong></font>
      </td>
    </tr>
  </table>
  <?php endif; ?>
</body>
</html>

<style>
  .data {
    margin-left: 5px;
  }

  .secondcolumn {
    margin-left: 50px;
    display: inline;
  }
</style>