<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014 DBSeller Servicos de Informatica
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
require_once(modification("libs/db_conecta" . ".php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification('libs/db_sql.php'));
require_once(modification("dbforms/db_funcoes.php"));
include(modification("dbforms/db_classesgenericas.php"));


$oPost = db_utils::postMemory($_POST);
$oGet = db_utils::postMemory($_GET);

if (isset($oPost->incluir)) {

    db_inicio_transacao();
    $sqlerro = false;
    $processado = true;

    $mes = $_POST['mes'];
    $ano = $_POST['ano'];

    if ($oPost->modelo == 'of_tce_servidores') {
        $nomearq = "./tmp/ME" . $_POST['ano'] . $_POST['mes'] . "000{$oPost->unidade}Servidor.txt";
        $sql = oficio_tce_servidores($mes, $ano, $oPost->unidade);
    } elseif ($oPost->modelo == 'of_tce_folha') {
        $nomearq = "./tmp/ME" . $_POST['ano'] . $_POST['mes'] . "000{$oPost->unidade}FOLHA.txt";
        $sql = oficio_tce_folha($mes, $ano, $oPost->unidade);
    } else {
        $nomearq = "./tmp/ME" . $_POST['ano'] . $_POST['mes'] . "000{$oPost->unidade}LANCAMENTO.txt";
        $sql = oficio_tce_lancamento($mes, $ano, $oPost->unidade);
    }

    $result = db_query($sql);

    $linhas = pg_num_rows($result);

    $arquivo = fopen($nomearq, "w");

    if ($linhas > 0) {
        while ($linha = pg_fetch_array($result)) {

            if (trim($linha[0]) == "") {
                continue;
            }
            fwrite($arquivo, $linha[0] . $linha[1] . "\n");
        }
    } else {
        fwrite($arquivo, "Não foram encontrados resultados para esse período. \n Certifique-se de que os campos foram preenchidos corretamente.");
    }
    fclose($arquivo);

    if ($result == false) {
        die($sql);
        //var_dump(pg_last_error());
        $sqlerro = true;
        $processado = false;
        echo "<script> alert('Algum erro aconteceu, favor entrar em contato com o suporte!') </script>";
    } else {
        echo "<script> alert('RelatÃ³rio gerado com sucesso.') </script>";

    }
    db_fim_transacao($sqlerro);
}

$iInstituicao = db_getsession('DB_instit');
// 000700- VOLTA REDONDA - PREFEITURA MUNICIPAL DE VOLTA REDONDA
// 000707- VOLTA REDONDA - FUNDAÃ?Â?Ã?Â?O EDUCACIONAL DE VOLTA REDONDA
// 000706- VOLTA REDONDA - FUNDAÃ?Â?Ã?Â?O BEATRIZ GAMA
// 000704- VOLTA REDONDA - SERVIÃ?Â?O AUTÃ?Â?NOMO HOSPITALAR
// 000702- VOLTA REDONDA - INSTITUTO DE PESQUISA E PLANEJAMENTO URBANO
// 000703- VOLTA REDONDA - SERVIÃ?Â?O AUTÃ?Â?NOMO DE Ã?Â?GUA E ESGOTO
// 000725- VOLTA REDONDA - FUNDO COMUNITÃ?Â?RIO DE VOLTA REDONDA
// 000727- VOLTA REDONDA - FUNDO MUNICIPAL DE ASSISTÃ?Â?NCIA SOCIAL
// 000709- VOLTA REDONDA - EMPRESA DE PROCESSAMENTO DE DADOS DE VOLTA REDONDA
// 001301- VOLTA REDONDA - COMPANHIA DE HABITAÃ?Â?Ã?Â?O DE VOLTA REDONDA

switch ($iInstituicao) {

  case 1:
    $sUnidade = '700';
    break;
  case 20:  
    $sUnidade = '707';
    break;
  case 25:
    $sUnidade = '706';
    break;
  case 30:
    $sUnidade = '704';
    break;
  case 35:
    $sUnidade = '702';
    break;
  case 45:
    $sUnidade = '703';
    break;
  case 55:
    $sUnidade = '725';
    break;
  case 65:
    $sUnidade = '727';
    break;
  case 75:
    $sUnidade = '709';
    break;
  default:
    $sUnidade = '1301';
    break;

}

?>
<html>
<head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <?php
    db_app::load("scripts.js, strings.js, prototype.js, datagrid.widget.js");
    db_app::load("widgets/messageboard.widget.js, widgets/windowAux.widget.js");
    db_app::load("estilos.css, grid.style.css");
    ?>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1">
<table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
    <tr>
        <td width="360" height="21">&nbsp;</td>
        <td width="263">&nbsp;</td>
        <td width="25">&nbsp;</td>
        <td width="140">&nbsp;</td>
    </tr>
</table>
<center>
    <style type="text/css">
        fieldset {
            border-radius: 7px;
            padding: 8px;
        }

        table.form-container th, table.form-container td {
            padding: 0px 4px;
            text-align: left;
        }

    </style>
    <br/>
    <br/>
    <form name="form1" method="post" action="" class="container">

        <fieldset>
            <legend>Modelo TCE</legend>
            <table border="0" width="100%" class="form-container">
                <tr>
                    <td>
                        <b>Ano:</b>
                    </td>
                    <td>
                        <?php

                        $sqlAnos = "select distinct r11_anousu from cfpess where r11_instit = " . db_getsession('DB_instit') . " order by 1 desc";
                        $rsAnos = db_query($sqlAnos);

                        $aAnos = pg_fetch_all($rsAnos);
                        ?>
                        <select name="ano">
                          <?php
                          foreach ($aAnos as $ano) {
                            $selected = ($ano['r11_anousu'] == $anoAtual) ? 'selected' : '';
                            ?>
                            <option value="<?php echo $ano['r11_anousu'] ?>" <?php echo $selected; ?>>
                              <?php echo $ano['r11_anousu'] ?>
                            </option>
                          <?php } ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>
                        <b>Mês:</b>
                    </td>
                    <td>
                        <?php $mesAtual = date('m');  ?>
                        <select name="mes">
                          <option value="01" <?php echo ($mesAtual == '01') ? 'selected' : ''; ?>>Janeiro</option>
                          <option value="02" <?php echo ($mesAtual == '02') ? 'selected' : ''; ?>>Fevereiro</option>
                          <option value="03" <?php echo ($mesAtual == '03') ? 'selected' : ''; ?>>Março</option>
                          <option value="04" <?php echo ($mesAtual == '04') ? 'selected' : ''; ?>>Abril</option>
                          <option value="05" <?php echo ($mesAtual == '05') ? 'selected' : ''; ?>>Maio</option>
                          <option value="06" <?php echo ($mesAtual == '06') ? 'selected' : ''; ?>>Junho</option>
                          <option value="07" <?php echo ($mesAtual == '07') ? 'selected' : ''; ?>>Julho</option>
                          <option value="08" <?php echo ($mesAtual == '08') ? 'selected' : ''; ?>>Agosto</option>
                          <option value="09" <?php echo ($mesAtual == '09') ? 'selected' : ''; ?>>Setembro</option>
                          <option value="10" <?php echo ($mesAtual == '10') ? 'selected' : ''; ?>>Outubro</option>
                          <option value="11" <?php echo ($mesAtual == '11') ? 'selected' : ''; ?>>Novembro</option>
                          <option value="12" <?php echo ($mesAtual == '12') ? 'selected' : ''; ?>>Dezembro</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Modelo:</b>
                    </td>
                    <td>
                        <select name="modelo">
                            <option value="of_tce_servidores">Ofício TCE Servidores</option>
                            <option value="of_tce_folha">Ofício TCE Folha</option>
                            <option value="of_tce_lancamento">Ofício TCE Lançamento</option>
                        </select>
                    </td>
                </tr>
                <tr>
                </tr>
                <td><b>Unidade :</b></td>
                <td><input type="text" name="unidade" name="id" value="<?php echo $sUnidade; ?>" size="10"></td>
                <tr>
                    <td></td>
                    <td>
                        <input id="incluir" name="incluir" type="submit" value="Processar" onclick="js_insere_matri();">
                    </td>
                </tr>
        </fieldset>
        </table>
    </form>
</center>
<?php
db_menu(db_getsession("DB_id_usuario"), db_getsession("DB_modulo"), db_getsession("DB_anousu"), db_getsession("DB_instit"));
?>
</body>
</html>
<script>

    function js_detectaarquivo(sArquivo) {
        var sListagem = sArquivo + "#Download arquivo ";
        js_montarlista(sListagem, "form1");
    }

</script>
<?php
if ($processado == true) {
    echo "
      <script>
        js_detectaarquivo('$nomearq');
      </script>
      ";
}

function oficio_tce_servidores($mes, $ano, $unidade)
{
    $sql = "

select
        'HH' ||
        '001' ||
        repeat(' ',412) ||
        '1' as conteudo

union all

select * from (
select
        'DD' ||
        rpad($unidade, 6) ||
        lpad(z01_cgccpf,11,0) ||
        rpad(trim(to_char(rh01_regist,'9999999999')),'20') ||
        rpad(case when coalesce(length(trim(to_ascii(z01_nome,'latin1'))),0) = 0 then ' ' else z01_nome end,80) ||
        rpad(case when coalesce(length(trim(rh01_sexo)),0) = 0 then ' ' else rh01_sexo end,1) ||
        rpad(case when coalesce(length(trim(z01_mae)),0) = 0 then ' ' else z01_mae end,80) ||
        rpad(case when coalesce(length(trim(z01_pai)),0) = 0 then ' ' else z01_pai end,80) ||

        lpad(
        case when rh02_instit = 1 then
          case
            when rh02_codreg in ( 11 ) then 1
            when rh02_codreg in ( 14 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 17 ) then 5
            when rh02_codreg in ( 12,13 ) then 6
            when rh02_codreg in ( 16 ) then 9
            when rh02_codreg in ( 811,836 ) then 12
            when rh02_codreg in ( 18,19,843 ) then 13
            when rh02_codreg in ( 15 ) then 15
            when rh02_codreg in ( 810 ) then 16
          else 0
          end

          when rh02_instit = 20 then
          case
            when rh02_codreg in ( 201 ) then 1
            when rh02_codreg in ( 204 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 202,203 ) then 4
            when rh02_codreg in ( 252,253 ) then 6
            when rh02_codreg in ( 813 ) then 12
            when rh02_codreg in ( 207 ) then 05
            when rh02_codreg in ( 208,848 ) then 13
            when rh02_codreg in ( 812 ) then 16
          else 0
          end

          when rh02_instit = 25 then
          case
            when rh02_codreg in ( 251 ) then 1
            when rh02_codreg in ( 254 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 257 ) then 5
            when rh02_codreg in ( 252,253 ) then 6
            when rh02_codreg in ( 815 ) then 12
            when rh02_codreg in ( 841 ) then 13
            when rh02_codreg in ( 814 ) then 16
          else 0
          end

          when rh02_instit = 30 then
          case
            when rh02_codreg in ( 301 ) then 1
            when rh02_codreg in ( 304 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 307,859 ) then 5
            when rh02_codreg in ( 302,303 ) then 6
            when rh02_codreg in ( 817,848 ) then 12
            when rh02_codreg in ( 308,309,845,849,850 ) then 13
            when rh02_codreg in ( 816 ) then 16
          else 0
          end

          when rh02_instit = 35 then
          case
            when rh02_codreg in ( 351 ) then 1
            when rh02_codreg in ( 354 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 357 ) then 5
            when rh02_codreg in ( 352,353 ) then 6
            when rh02_codreg in ( 819 ) then 12
            when rh02_codreg in ( 818 ) then 16
          else 0
          end

          when rh02_instit = 45 then
          case
            when rh02_codreg in ( 451 ) then 1
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 452,453 ) then 6
            when rh02_codreg in ( 838 ) then 12
            when rh02_codreg in ( 837 ) then 13
            when rh02_codreg in ( 839 ) then 14
            when rh02_codreg in ( 457 ) then 16
          else 0
          end

          when rh02_instit = 55 then
          case
            when rh02_codreg in ( 551 ) then 1
            when rh02_codreg in ( 554 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 828 ) then 12
            when rh02_codreg in ( 847 ) then 13
            when rh02_codreg in ( 827 ) then 16
          else 0
          end

          when rh02_instit = 65 then
          case
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 830 ) then 12
            when rh02_codreg in ( 658,659 ) then 13
            when rh02_codreg in ( 829 ) then 16
          else 0
          end

          when rh02_instit = 75 then
          case
            when rh02_codreg in ( 751 ) then 1
            when rh02_codreg in ( 754 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 752,753 ) then 6
            when rh02_codreg in ( 832 ) then 12
            when rh02_codreg in ( 759,842 ) then 13
            when rh02_codreg in ( 831 ) then 16
          else 0
          end

          when rh02_instit = 80 then
            case
              when rh02_codreg in ( 801 ) then 1
              when rh02_codreg in ( 804 ) then 3
              when rh30_regime = 2 and rh20_cargo is not null then 4
              when rh02_codreg in ( 807 ) then 5
              when rh02_codreg in ( 802,803 ) then 6
              when rh02_codreg in ( 834 ) then 12
              when rh02_codreg in ( 840 ) then 13
              when rh02_codreg in ( 833 ) then 16
              else 0
            end
        end
        ,2,0) ||

        rpad(case when (SELECT rh261_datamovimentacao FROM rhcedencia WHERE rh261_regist = rhpessoalmov.rh02_regist LIMIT 1) IS NULL THEN '00000000' ELSE (SELECT to_char(rh261_datamovimentacao, 'YYYYMMDD') FROM rhcedencia WHERE rh261_regist = rhpessoalmov.rh02_regist LIMIT 1) END, 8) ||

        rpad(case when coalesce(length(trim(z01_ident)),0) = 0 then '00000000' else z01_ident end,15) ||
        rpad( case when coalesce(length(trim(z01_identorgao)),0) = 0 then 'NInformado' else trim(z01_identorgao) end ,10) ||
        rpad(case when coalesce(length(trim( to_char(rh01_nasc,'YYYYMMDD') )),0) = 0 then ' ' else to_char(rh01_nasc,'YYYYMMDD') end,8) ||

        lpad(case when rh01_instru in (1) then 1
                  when rh01_instru in (2,3,4,5) then 2
                  when rh01_instru in (6,7) then 3
                  when rh01_instru in (8,9) then 5
                  when rh01_instru in (12) then 6
                  when rh01_instru in (10) then 8
                  when rh01_instru in (11) then 9
                  else 0
              end
              ,1,0) ||

        repeat('0',10) ||
        repeat('0',10) ||
        rpad(case when coalesce(length(trim( to_char(rh01_admiss,'YYYYMMDD') )),0) = 0 then ' ' else to_char(rh01_admiss,'YYYYMMDD') end,8) ||
        rpad(case when rh02_funcao = 31 then to_char(rh01_admiss,'YYYYMMDD') else '00000000' end,8  ) ||
        rpad(case when coalesce(length(trim(z01_cep)),0) < 8 then '00000000' else z01_cep end,8) ||
        rpad(case when coalesce(length(trim(z01_munic)),0) = 0 then ' ' else z01_munic end,40) ||
        rpad(case when coalesce(length(trim(z01_uf)),0) = 0 then ' ' else z01_uf end,2) ||
        repeat(' ',7) ||
        '1'
FROM cgm
INNER JOIN rhpessoal ON rh01_numcgm = z01_numcgm
INNER JOIN rhpessoalmov ON rh02_regist = rh01_regist AND rh02_instit = rh01_instit
LEFT JOIN rhfuncao ON rh37_instit = rh02_instit AND rh37_funcao = rh02_funcao
LEFT JOIN rhpescargo ON rh20_seqpes = rh02_seqpes
LEFT JOIN rhcargo ON rh04_codigo = rh20_cargo AND rh04_instit = rh20_instit
INNER JOIN rhregime ON rh30_codreg = rh02_codreg AND rh30_instit = rh02_instit
WHERE rh01_instit = " . db_getsession("DB_instit") . "
  AND rh02_anousu = " . $_POST['ano'] . "
  AND rh02_mesusu = " . $_POST['mes'] . "
  AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
  AND ((SELECT count(*)
          FROM gerfsal
         WHERE r14_regist = rh02_regist
           AND r14_anousu = rh02_anousu
           AND r14_mesusu = rh02_mesusu
           AND r14_instit = rh02_instit
       ) > 0
       OR
       (SELECT count(*)
          FROM gerfres
         WHERE r20_regist = rh02_regist
           AND r20_anousu = rh02_anousu
           AND r20_mesusu = rh02_mesusu
           AND r20_instit = rh02_instit
       ) > 0
       OR
       (SELECT count(*)
          FROM gerfs13
         WHERE r35_regist = rh02_regist
           AND r35_anousu = rh02_anousu
           AND r35_mesusu = rh02_mesusu
           AND r35_instit = rh02_instit
       ) > 0
       OR
       (SELECT count(*)
          FROM gerfcom
         WHERE r48_regist = rh02_regist
           AND r48_anousu = rh02_anousu
           AND r48_mesusu = rh02_mesusu
           AND r48_instit = rh02_instit
       ) > 0
      )
  AND ((SELECT count(*)
          FROM rhpesrescisao
         WHERE rh05_seqpes = rh02_seqpes
           AND ( rh05_recis >= ($ano||'-'||$mes||'-01')::date  or extract(year from rh05_recis ) = $ano )
       ) > 0
       OR
       (SELECT count(*)
          FROM rhpesrescisao
         WHERE rh05_seqpes = rh02_seqpes

       ) = 0
      )
order by  z01_cgccpf
) as x

union all

select
        'TT' ||
        lpad( ( select count(*)
FROM cgm
INNER JOIN rhpessoal ON rh01_numcgm = z01_numcgm
INNER JOIN rhpessoalmov ON rh02_regist = rh01_regist AND rh02_instit = rh01_instit
LEFT JOIN rhfuncao ON rh37_instit = rh02_instit AND rh37_funcao = rh02_funcao
LEFT JOIN rhpescargo ON rh20_seqpes = rh02_seqpes
LEFT JOIN rhcargo ON rh04_codigo = rh20_cargo AND rh04_instit = rh20_instit
INNER JOIN rhregime ON rh30_codreg = rh02_codreg AND rh30_instit = rh02_instit
WHERE rh01_instit = " . db_getsession("DB_instit") . "
  AND rh02_anousu = " . $_POST['ano'] . "
  AND rh02_mesusu = " . $_POST['mes'] . "
  AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
  AND (
      (SELECT count(*)
          FROM gerfsal
         WHERE r14_regist = rh02_regist
           AND r14_anousu = rh02_anousu
           AND r14_mesusu = rh02_mesusu
           AND r14_instit = rh02_instit
       ) > 0
       OR
       (SELECT count(*)
          FROM gerfres
         WHERE r20_regist = rh02_regist
           AND r20_anousu = rh02_anousu
           AND r20_mesusu = rh02_mesusu
           AND r20_instit = rh02_instit
       ) > 0
       OR
       (SELECT count(*)
          FROM gerfs13
         WHERE r35_regist = rh02_regist
           AND r35_anousu = rh02_anousu
           AND r35_mesusu = rh02_mesusu
           AND r35_instit = rh02_instit
       ) > 0
       OR
       (SELECT count(*)
          FROM gerfcom
         WHERE r48_regist = rh02_regist
           AND r48_anousu = rh02_anousu
           AND r48_mesusu = rh02_mesusu
           AND r48_instit = rh02_instit
       ) > 0
      )
  AND ((SELECT count(*)
          FROM rhpesrescisao
         WHERE rh05_seqpes = rh02_seqpes
           AND ( rh05_recis >= ($ano||'-'||$mes||'-01')::date  or extract(year from rh05_recis ) = $ano )
       ) > 0
       OR
       (SELECT count(*)
          FROM rhpesrescisao
         WHERE rh05_seqpes = rh02_seqpes
       ) = 0
      ))::integer,15,0) ||
        repeat(' ',400) ||
        '1'
";
    return $sql;
}

function oficio_tce_folha($mes, $ano, $unidade)
{
    $sql = "

select
        'HH' ||
        '001' ||
        repeat(' ',174) ||
        '2' as conteudo

union all

select * from (
select
      'DD' ||
        rpad( $unidade ,6) ||
        lpad(z01_cgccpf,11,0) ||
        rpad(trim(to_char(rh01_regist,'9999999999')),'20') ||
        rpad( " . $_POST['ano'] . " ,4) ||
        lpad( " . $_POST['mes'] . " ,2,0) ||
        rpad(1,1) ||
        lpad(1,2,0) ||

       lpad(
        case when rh02_instit = 1 then
          case
            when rh02_codreg in ( 11 ) then 1
            when rh02_codreg in ( 14 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 17 ) then 5
            when rh02_codreg in ( 12,13 ) then 6
            when rh02_codreg in ( 16 ) then 9
            when rh02_codreg in ( 811,836 ) then 12
            when rh02_codreg in ( 18,19,843 ) then 13
            when rh02_codreg in ( 15 ) then 15
            when rh02_codreg in ( 810 ) then 16
          else 0
          end

          when rh02_instit = 20 then
          case
            when rh02_codreg in ( 201 ) then 1
            when rh02_codreg in ( 204 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 202,203 ) then 4
            when rh02_codreg in ( 207 ) then 5
            when rh02_codreg in ( 252,253 ) then 6
            when rh02_codreg in ( 813 ) then 12
            when rh02_codreg in ( 208,484 ) then 13
            when rh02_codreg in ( 812 ) then 16
          else 0
          end

          when rh02_instit = 25 then
          case
            when rh02_codreg in ( 251 ) then 1
            when rh02_codreg in ( 254 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 04
            when rh02_codreg in ( 257 ) then 5
            when rh02_codreg in ( 252,253 ) then 6
            when rh02_codreg in ( 815 ) then 12
            when rh02_codreg in ( 841 ) then 13
            when rh02_codreg in ( 814 ) then 16
          else 0
          end

          when rh02_instit = 30 then
          case
            when rh02_codreg in ( 301 ) then 1
            when rh02_codreg in ( 304 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 307,859 ) then 5
            when rh02_codreg in ( 302,303 ) then 6
            when rh02_codreg in ( 817,848 ) then 12
            when rh02_codreg in ( 308,309,845,849,850 ) then 13
            when rh02_codreg in ( 816 ) then 16
          else 0
          end

          when rh02_instit = 35 then
          case
            when rh02_codreg in ( 351 ) then 1
            when rh02_codreg in ( 354 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 357 ) then 5
            when rh02_codreg in ( 352,353 ) then 6
            when rh02_codreg in ( 819 ) then 12
            when rh02_codreg in ( 846,359 ) then 13
            when rh02_codreg in ( 818 ) then 16
          else 0
          end

          when rh02_instit = 45 then
          case
            when rh02_codreg in ( 451 ) then 1
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 856,860 ) then 03
            when rh02_codreg in ( 457 ) then 05
            when rh02_codreg in ( 452,453 ) then 6
            when rh02_codreg in ( 838 ) then 12
            when rh02_codreg in ( 837,857,858,842 ) then 13
            when rh02_codreg in ( 839 ) then 14
            when rh02_codreg in ( 855 ) then 16
          else 0
          end

          when rh02_instit = 55 then
          case
            when rh02_codreg in ( 551 ) then 1
            when rh02_codreg in ( 554 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 828 ) then 12
            when rh02_codreg in ( 847 ) then 13
            when rh02_codreg in ( 827 ) then 16
          else 0
          end

          when rh02_instit = 65 then
          case
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 830 ) then 12
            when rh02_codreg in ( 658,659 ) then 13
            when rh02_codreg in ( 829 ) then 16
          else 0
          end

          when rh02_instit = 75 then
          case
            when rh02_codreg in ( 751 ) then 1
            when rh02_codreg in ( 754 ) then 3
            when rh30_regime = 2 and rh20_cargo is not null then 4
            when rh02_codreg in ( 757 ) then 05
            when rh02_codreg in ( 752,753 ) then 6
            when rh02_codreg in ( 832 ) then 12
            when rh02_codreg in ( 759,842 ) then 13
            when rh02_codreg in ( 831 ) then 16
          else 0
          end

          when rh02_instit = 80 then
            case
              when rh02_codreg in ( 801 ) then 1
              when rh02_codreg in ( 804 ) then 3
              when rh30_regime = 2 and rh20_cargo is not null then 4
              when rh02_codreg in ( 807 ) then 5
              when rh02_codreg in ( 802,803 ) then 6
              when rh02_codreg in ( 834 ) then 12
              when rh02_codreg in ( 840 ) then 13
              when rh02_codreg in ( 833 ) then 16
              else 0
            end
        end
        ,2,0) ||

        lpad( coalesce ( ( 
          select
            legenda 
          from 
            plugins.depara_cargos_oficio_tce 
          where 
            instit = rh02_instit 
            and cargo = ( select rh20_cargo from rhpescargo where rh20_seqpes = rh02_seqpes ) limit 1),
            case           
              when rh02_instit = 1 and rh02_funcao in (23941001,23121001,33111003,51530500,23121002,33120501,23121002,33120501,23212000,23211503,23211506,239410000,23913000,23941000,23943000,23943001) then 1
              when rh02_instit = 1 and rh02_funcao in (25210502,24100500,21240501,21240500,21242000,21410500,41510500,41100503,35111501,25221000,31850500,25120500,
                21420500,95110501,26180500,26112500,23941500,31711001,32110500,31321500,31231000,35220500,41100506) then 2 
              when rh02_instit = 1 and rh02_funcao in (51510502,51510500,51510501,25160500,22110500,22120500,22350500,22340500,22360500,22381000,2251500,2251503,
                22515100,22510500,22510600,22511000,22511501,22513500,22514000,22512000,22516500,22525000,32420500,75223500,32411502,32220500,32240500,31321503,
                35160500,22390500,22356000,25151000,23942500,2534000,22519500,22515001,22525500,22514200,22525001,22526500,22527000,22527500,22512400,22512700,
                22513300,22515400,22532000, 22528500,22330500,223710000,22320800,2251502,41100500,41100533) then 3
              when rh02_instit = 1 and rh02_funcao in (2412500) then 5
              when rh02_instit = 1 and rh02_funcao in (41100509,41100512,11141501,41410500,411030000,25210501,52510509,41101001,41100506,41100503,41410501,33111001,32223000,
              52113000,32411000,41100518,41100519,31331501,32241500,51432001,33111003,71661000,2140500,2140500,41100528,9999,42220500,62302000,11125500,51742000,515120000, 
              11125000,51741000,51712500,72121503,72510500,37310500,41211001,51511003,31210501,51421500,517215800,51721501,23321005,622010000,51511000,91440501,25141003,35221004,
              235221002,35221001,51120501,51120500,35221005,72111000,41220500,71520500,2148400,2148400,71020500,22110500,78230500,26261502,72121503,82510500,22410500,48230500,
              2145100,71550500,51532000,41010500,41010501,51342500,51661000,2140100,21449900,41010503,31312500,0,71702001,2145100,2141800,2141801,2141802,2141803,2141804,21418005,
              21418006,21418007,21418008,21418009,2141810,2141811,2141812,2141813,2141814,2141815,2141816,2141817,2141818,2141819,2141820) then 7

           
              when rh02_instit = 20 and rh02_funcao in (23211501,23211502,23211504,23943000) then 1 
              when rh02_instit = 20 and rh02_funcao in (35110500,23941000,41100514,41100515,41100516,41100504) then 2  
              when rh02_instit = 20 and rh02_funcao in (51742000,42220500,23943000,51432001,42210500,2149900,2149400,26261506,23321000,2145100,78230500,11141500) then 7 


              when rh02_instit = 25 and rh02_funcao in (23941001,23121001,33111003) then 1 
              when rh02_instit = 25 and rh02_funcao in (24100500,35110500,41100500,41100503) then 2  
              when rh02_instit = 25 and rh02_funcao in (22371000,25151000,23941001,23121001,23212000,25160500,322233000,52113000) then 3  
              when rh02_instit = 25 and rh02_funcao in (41400500,37141000,51511001,31411000,33111002,76631500,2145100,35420500,76300500,51320500,2149900,95110501,72411000,76625000,
              78230500,86212000,84830500,51741000,51432006,51432005,42220500,51742000,41101000) then 7


              when rh02_instit = 30 and rh02_funcao in (24100500,21240501,21240500,21410500,22110500,25221000,25120500,95110500,2140500,41100525,35221002,35110500,31321500,32420500,
              31321503) then 2 
              when rh02_instit = 30 and rh02_funcao in (25160500,32223000,52113000,32411000,32241500,22350500,22340500,22360500,22381000,71640500,51511000,22511500,22511503,22515100,
              22510500,22510600,22511000,22511501,22512000,22516500,22518000,22525000,22534000,22514200,22525001,225265000,22512400,22515400,22511502,22528500,22371000,22320800,21151000,
              32420500,32411500,32220500,22511502,32411501) then 3  
              when rh02_instit = 30 and rh02_funcao in (41100512,4140500,25210501,41101001,51631000,51322000,41100503,32111501,41100500,41100518,2145100,41010500,76300500,51320500,2140100,
              41410529,2149900,41220501,78230500,42210500,51432001,41100525) then 7

              when rh02_instit = 35 and rh02_funcao in (25210502,21410500,25221000,31850500,21420500,41100503) then 2 
              when rh02_instit = 35 and rh02_funcao in (25210501,2145100,2149900) then 7
              
              when rh02_instit = 45 and rh02_funcao in (24101000,31151501,31151500,31812000,21426000,31110501,32420500,34110500,31210500,31711000,35230500,35160500,41101000,41101001,
              41101002,41101003,31221000,31221003,35110500,95412500,2149800) then 2
              when rh02_instit = 45  and rh02_funcao in (72410503,72410501,86220502,81811000,71702000,86220503,11141500,51342500,2149900,2140100,41211000,72411000,2149800,71020500,
              91511000,51994000,72410504,91440500,71020503,78230500,86230500,862305503,71511500,41023000,71521000,71661000,71702001) then 7  

              when rh02_instit = 55 and rh02_funcao in (2145100) then 7
              
              when rh02_instit = 65 and rh02_funcao in (24100500,51320500,41211000,51530500,23321002,23941500,25112000) then 2  
              when rh02_instit = 65 and rh02_funcao in (25160500,22360500,22371000,23212000,25151000) then 3
              when rh02_instit = 65 and rh02_funcao in (76521500,51621001,41211000,42413000,23321005,23322500,23321002,782530500,51432001) then 7

              when rh02_instit = 75 and rh02_funcao in (31711002,41100508,21240501,2124200) then 2  
              when rh02_instit = 75 and rh02_funcao in (2141819,2141818,41101000) then 7

              when rh02_instit = 80 and rh02_funcao in (21420500,21410500,21410501,23321002,24100500,25120500,25151000,25160500,25210502,25221000,26112500,31210503,41100503,41100500,
              41100518,41100519) then 2
              when rh02_instit = 80 and rh02_funcao in (32223000) then 3  
              when rh02_instit = 80 and rh02_funcao in (32241501,33411000,37110501,41100502,41100507,41100512,41100517,41101000,41101001,41101003,41211000,41211001,41220501,41321000,
              42210500,42220500,51320501,51342500,51412000,51432001,51741000,51742000,71020501,71521000,71550500,78230500,71661000,71702000,72121500) then 7
              else 7        
            end),2,0) ||
        rpad(case when coalesce(length(trim(rh37_descr)),0) = 0 then ' ' else rh37_descr end,30) ||
      (SELECT lpad(replace(coalesce((select coalesce(sum(r14_valor),0)
                   from gerfsal
                   where r14_regist = rh02_regist
                   AND r14_anousu = rh02_anousu
                   AND r14_mesusu = rh02_mesusu
                   AND r14_instit = rh02_instit
                   AND r14_pd = 1)
 +
 (select coalesce(sum(r35_valor),0)
    from gerfs13
    where r35_regist = rh02_regist
    AND r35_anousu = rh02_anousu
    AND r35_mesusu = rh02_mesusu
    AND r35_instit = rh02_instit
    AND r35_pd = 1)
 +
 (select coalesce(sum(r48_valor),0)
    from gerfcom
    where r48_regist = rh02_regist
    AND r48_anousu = rh02_anousu
    AND r48_mesusu = rh02_mesusu
    AND r48_instit = rh02_instit
    AND r48_pd = 1)
 +
 (select coalesce(sum(r20_valor),0)
    from gerfres
    where r20_regist = rh02_regist
    AND r20_anousu = rh02_anousu
    AND r20_mesusu = rh02_mesusu
    AND r20_instit = rh02_instit
    AND r20_pd = 1),0)::numeric(15,2)::varchar,'.',''),15,'0') AS bruto)
||
  (SELECT lpad(replace(coalesce((select coalesce(sum(CASE WHEN r14_pd = 1 THEN r14_valor WHEN r14_pd = 2 THEN r14_valor*-1 END),0)
    from gerfsal
    where r14_regist = rh02_regist
    AND r14_anousu = rh02_anousu
    AND r14_mesusu = rh02_mesusu
    AND r14_instit = rh02_instit)
 +
 (select coalesce(sum(CASE WHEN r35_pd = 1 THEN r35_valor WHEN r35_pd = 2 THEN r35_valor*-1 END),0)
    from gerfs13
    where r35_regist = rh02_regist
    AND r35_anousu = rh02_anousu
    AND r35_mesusu = rh02_mesusu
    AND r35_instit = rh02_instit)
 +
 (select coalesce(sum(CASE WHEN r48_pd = 1 THEN r48_valor WHEN r48_pd = 2 THEN r48_valor*-1 END),0)
    from gerfcom
    where r48_regist = rh02_regist
    AND r48_anousu = rh02_anousu
    AND r48_mesusu = rh02_mesusu
    AND r48_instit = rh02_instit)
 +
 (select coalesce(sum(CASE WHEN r20_pd = 1 THEN r20_valor WHEN r20_pd = 2 THEN r20_valor*-1 END),0)
    from gerfres
    where r20_regist = rh02_regist
    AND r20_anousu = rh02_anousu
    AND r20_mesusu = rh02_mesusu
    AND r20_instit = rh02_instit),0)::numeric(15,2)::varchar,'.',''),15,'0') AS liquido)
||
    (SELECT lpad(replace(coalesce((select coalesce(sum(r14_valor),0)
          from gerfsal
            inner join basesr on r09_anousu = r14_anousu
            AND r09_mesusu = r14_mesusu
            AND r09_instit = r14_instit
            where r14_regist = rh02_regist
            AND r14_anousu = rh02_anousu
            AND r14_mesusu = rh02_mesusu
            AND r14_instit = rh02_instit
            AND r09_base in ('B914')
            AND r09_rubric = r14_rubric
            AND r14_pd = 1)
    +
    (select coalesce(sum(r35_valor),0)
          from gerfs13
            inner join basesr on r09_anousu = r35_anousu
            AND r09_mesusu = r35_mesusu
            AND r09_instit = r35_instit
            where r35_regist = rh02_regist
            AND r35_anousu = rh02_anousu
            AND r35_mesusu = rh02_mesusu
            AND r35_instit = rh02_instit
            AND r09_base in ('B914')
            AND r09_rubric = r35_rubric
            AND r35_pd = 1)
    +        
    (select coalesce(sum(r48_valor),0)
          from gerfcom
            inner join basesr on r09_anousu = r48_anousu
            AND r09_mesusu = r48_mesusu
            AND r09_instit = r48_instit
            where r48_regist = rh02_regist
            AND r48_anousu = rh02_anousu
            AND r48_mesusu = rh02_mesusu
            AND r48_instit = rh02_instit
            AND r09_base in ('B914')
            AND r09_rubric = r48_rubric
            AND r48_pd = 1)
    +        
    (select coalesce(sum(r20_valor),0)
          from gerfres
            inner join basesr on r09_anousu = r20_anousu
            AND r09_mesusu = r20_mesusu
            AND r09_instit = r20_instit
            where r20_regist = rh02_regist
            AND r20_anousu = rh02_anousu
            AND r20_mesusu = rh02_mesusu
            AND r20_instit = rh02_instit
            AND r09_base in ('B914')
            AND r09_rubric = r20_rubric
            AND r20_pd = 1),0)::numeric(15,2)::varchar,'.',''),15,'0') AS parcela_indenizatoria)
    ||
 
        lpad(translate( lpad ( coalesce ( ( select r14_valor
              from gerfsal as salario_base
              where (salario_base.r14_regist = rhpessoal.rh01_regist and salario_base.r14_instit = rhpessoal.rh01_instit) and salario_base.r14_anousu = " . $_POST['ano'] . " and salario_base.r14_mesusu = " . $_POST['mes'] . " and r14_rubric = '0245' ),0),10,'0'),'.',''),10,'0') ||
        rpad(0,6) ||

        rpad(case when coalesce(length(trim(rh04_descr)),0) = 0 then ' ' else rh04_descr end,30) ||

        repeat(' ',6) ||
        '2'
FROM cgm
INNER JOIN rhpessoal ON rh01_numcgm = z01_numcgm
INNER JOIN rhpessoalmov ON rh02_regist = rh01_regist AND rh02_instit = rh01_instit 
LEFT JOIN rhfuncao ON rh37_instit = rh02_instit AND rh37_funcao = rh02_funcao
LEFT JOIN rhpescargo ON rh20_seqpes = rh02_seqpes
LEFT JOIN rhcargo ON rh04_codigo = rh20_cargo AND rh04_instit = rh20_instit
INNER JOIN rhregime ON rh30_codreg = rh02_codreg AND rh30_instit = rh02_instit
WHERE rh01_instit = " . db_getsession("DB_instit") . "
  AND rh02_anousu = " . $_POST['ano'] . "
  AND rh02_mesusu = " . $_POST['mes'] . "
  AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
  AND ((SELECT count(*)
          FROM gerfsal
         WHERE r14_regist = rh02_regist
           AND r14_anousu = rh02_anousu
           AND r14_mesusu = rh02_mesusu
           AND r14_instit = rh02_instit
       ) > 0
       OR
        (SELECT count(*)
          FROM gerfs13
         WHERE r35_regist = rh02_regist
           AND r35_anousu = rh02_anousu
           AND r35_mesusu = rh02_mesusu
           AND r35_instit = rh02_instit
       ) > 0
       OR
        (SELECT count(*)
          FROM gerfcom
         WHERE r48_regist = rh02_regist
           AND r48_anousu = rh02_anousu
           AND r48_mesusu = rh02_mesusu
           AND r48_instit = rh02_instit
       ) > 0
       OR
       (SELECT count(*)
          FROM gerfres
         WHERE r20_regist = rh02_regist
           AND r20_anousu = rh02_anousu
           AND r20_mesusu = rh02_mesusu
           AND r20_instit = rh02_instit
       ) > 0
      )
  AND ((SELECT count(*)
          FROM rhpesrescisao
         WHERE rh05_seqpes = rh02_seqpes
          AND ( rh05_recis >= ($ano||'-'||$mes||'-01')::date  or extract(year from rh05_recis ) = $ano )
       ) > 0
       OR
       (SELECT count(*)
          FROM rhpesrescisao
         WHERE rh05_seqpes = rh02_seqpes
       ) = 0
     )

order by z01_cgccpf
) as x

union all

select
        'TT' ||
        lpad(( select count(*)
                        FROM cgm
INNER JOIN rhpessoal ON rh01_numcgm = z01_numcgm
INNER JOIN rhpessoalmov ON rh02_regist = rh01_regist AND rh02_instit = rh01_instit 
LEFT JOIN rhfuncao ON rh37_instit = rh02_instit AND rh37_funcao = rh02_funcao
LEFT JOIN rhpescargo ON rh20_seqpes = rh02_seqpes
LEFT JOIN rhcargo ON rh04_codigo = rh20_cargo AND rh04_instit = rh20_instit
INNER JOIN rhregime ON rh30_codreg = rh02_codreg AND rh30_instit = rh02_instit
WHERE rh01_instit = " . db_getsession("DB_instit") . "
  AND rh02_anousu = " . $_POST['ano'] . "
  AND rh02_mesusu = " . $_POST['mes'] . "
  AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
  AND ((SELECT count(*)
          FROM gerfsal
         WHERE r14_regist = rh02_regist
           AND r14_anousu = rh02_anousu
           AND r14_mesusu = rh02_mesusu
           AND r14_instit = rh02_instit
       ) > 0
       OR
        (SELECT count(*)
          FROM gerfs13
         WHERE r35_regist = rh02_regist
           AND r35_anousu = rh02_anousu
           AND r35_mesusu = rh02_mesusu
           AND r35_instit = rh02_instit
       ) > 0
       OR
        (SELECT count(*)
          FROM gerfcom
         WHERE r48_regist = rh02_regist
           AND r48_anousu = rh02_anousu
           AND r48_mesusu = rh02_mesusu
           AND r48_instit = rh02_instit
       ) > 0
       OR
       (SELECT count(*)
          FROM gerfres
         WHERE r20_regist = rh02_regist
           AND r20_anousu = rh02_anousu
           AND r20_mesusu = rh02_mesusu
           AND r20_instit = rh02_instit
       ) > 0
      )
  AND ((SELECT count(*)
          FROM rhpesrescisao
         WHERE rh05_seqpes = rh02_seqpes
           AND ( rh05_recis >= ($ano||'-'||$mes||'-01')::date  or extract(year from rh05_recis ) = $ano )
       ) > 0
       OR
       (SELECT count(*)
          FROM rhpesrescisao
         WHERE rh05_seqpes = rh02_seqpes
       ) = 0
      )
                     )::integer,15,0
                    ) || (SELECT lpad(replace(coalesce(sum((select coalesce(sum(r14_valor),0)
                                                           from gerfsal
                                                           where r14_regist = rh02_regist
                                                           AND r14_anousu = rh02_anousu
                                                           AND r14_mesusu = rh02_mesusu
                                                           AND r14_instit = rh02_instit
                                                           AND r14_pd = 1)
                                         +
                                         (select coalesce(sum(r35_valor),0)
                                            from gerfs13
                                            where r35_regist = rh02_regist
                                            AND r35_anousu = rh02_anousu
                                            AND r35_mesusu = rh02_mesusu
                                            AND r35_instit = rh02_instit
                                            AND r35_pd = 1)
                                         +
                                         (select coalesce(sum(r48_valor),0)
                                            from gerfcom
                                            where r48_regist = rh02_regist
                                            AND r48_anousu = rh02_anousu
                                            AND r48_mesusu = rh02_mesusu
                                            AND r48_instit = rh02_instit
                                            AND r48_pd = 1)
                                         +
                                         (select coalesce(sum(r20_valor),0)
                                            from gerfres
                                            where r20_regist = rh02_regist
                                            AND r20_anousu = rh02_anousu
                                            AND r20_mesusu = rh02_mesusu
                                            AND r20_instit = rh02_instit
                                            AND r20_pd = 1)),0)::numeric(15,2)::varchar,'.',''),15,'0') AS bruto
                    FROM cgm
                    INNER JOIN rhpessoal ON rh01_numcgm = z01_numcgm
                    INNER JOIN rhpessoalmov ON rh02_regist = rh01_regist AND rh02_instit = rh01_instit 
                    LEFT JOIN rhfuncao ON rh37_instit = rh02_instit AND rh37_funcao = rh02_funcao
                    LEFT JOIN rhpescargo ON rh20_seqpes = rh02_seqpes
                    LEFT JOIN rhcargo ON rh04_codigo = rh20_cargo AND rh04_instit = rh20_instit
                    WHERE rh01_instit = " . db_getsession("DB_instit") . "
                      
                      AND rh02_anousu = " . $_POST['ano'] . "
                      AND rh02_mesusu = " . $_POST['mes'] . "
                      AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date

                      AND ((SELECT count(*)
                              FROM gerfsal
                             WHERE r14_regist = rh02_regist
                               AND r14_anousu = rh02_anousu
                               AND r14_mesusu = rh02_mesusu
                               AND r14_instit = rh02_instit
                           ) > 0
                          OR
                          (SELECT count(*)
                              FROM gerfs13
                             WHERE r35_regist = rh02_regist
                               AND r35_anousu = rh02_anousu
                               AND r35_mesusu = rh02_mesusu
                               AND r35_instit = rh02_instit
                           ) > 0
                           OR
                            (SELECT count(*)
                              FROM gerfcom
                             WHERE r48_regist = rh02_regist
                               AND r48_anousu = rh02_anousu
                               AND r48_mesusu = rh02_mesusu
                               AND r48_instit = rh02_instit
                           ) > 0
                           OR
                           (SELECT count(*)
                              FROM gerfres
                             WHERE r20_regist = rh02_regist
                               AND r20_anousu = rh02_anousu
                               AND r20_mesusu = rh02_mesusu
                               AND r20_instit = rh02_instit
                           ) > 0
                          )
                          AND ((SELECT count(*)
                              FROM rhpesrescisao
                             WHERE rh05_seqpes = rh02_seqpes
                              AND ( rh05_recis >= ($ano||'-'||$mes||'-01')::date  or extract(year from rh05_recis ) = $ano )
                           ) > 0
                           OR
                           (SELECT count(*)
                              FROM rhpesrescisao
                             WHERE rh05_seqpes = rh02_seqpes
                           ) = 0
                       )) || repeat(' ',147) || '2'
";
    //die($sql);
    return $sql;
}

function oficio_tce_lancamento($mes, $ano, $unidade)
{
    $sql = "

select
        'HH' ||
        '001' ||
        repeat(' ',96) ||
        '3' as conteudo

union all

select * from (
select
        'DD' ||
        rpad( $unidade ,6) ||
        lpad(z01_cgccpf,11,0) ||
        rpad(trim(to_char(rh01_regist,'9999999999')),'20') ||
        rpad( " . $_POST['ano'] . " ,4) ||
        lpad( " . $_POST['mes'] . " ,2,0) ||
        rpad(1,1) ||
        lpad(1,2,'0') ||
        case when r14_pd = 1 then 'C' when r14_pd = 2 then 'D' else ' ' end ||
        lpad(translate( lpad ( coalesce ( ( select r14_quant::numeric(15,2) ),0),10,'0'),'.',''),10,'0') ||
        rpad(rh27_descr,20) ||
        (SELECT replace(lpad(r14_valor::numeric(15,2)::varchar,16,'0'),'.','')) ||

        case when ( select count(*) from basesr where r09_anousu = r14_anousu and r09_mesusu = r14_mesusu and r09_instit = r14_instit and r09_base in ('B001') and r09_rubric = r14_rubric ) > 0 then 'S' else 'N' end ||
        case when ( select count(*) from basesr where r09_anousu = r14_anousu and r09_mesusu = r14_mesusu and r09_instit = r14_instit and r09_base in ('B004') and r09_rubric = r14_rubric ) > 0 then 'S' else 'N' end ||
        case when ( select count(*) from basesr where r09_anousu = r14_anousu and r09_mesusu = r14_mesusu and r09_instit = r14_instit and r09_base = 'B031' and r09_rubric = r14_rubric ) > 0 then 'S' else 'N' end ||

        repeat(' ',4) ||
        '3' as conteudo
from gerfsal
inner join rhpessoalmov on rh02_anousu = r14_anousu and rh02_mesusu = r14_mesusu and rh02_regist = r14_regist and rh02_instit = r14_instit 
inner join rhrubricas  on  rhrubricas.rh27_rubric = gerfsal.r14_rubric and rhrubricas.rh27_instit = gerfsal.r14_instit
inner join rhpessoal on rh01_regist = r14_regist
inner join cgm on z01_numcgm = rh01_numcgm
where r14_anousu = " . $_POST['ano'] . " and r14_mesusu = " . $_POST['mes'] . " and r14_instit = " . db_getsession("DB_instit") . "
AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
and r14_pd in (1,2)

and(
        (select count(*) from gerfsal where r14_regist = rh02_regist and r14_anousu = rh02_anousu and r14_mesusu = rh02_mesusu and r14_instit = rh02_instit) > 0
                or
        (select count(*) from gerfres where r20_regist = rh02_regist and r20_anousu = rh02_anousu and r20_mesusu = rh02_mesusu and r20_instit = rh02_instit) > 0
      )
and (

      ( select count(*) from rhpesrescisao where rh05_seqpes = rh02_seqpes and rh05_recis >= '2017-07-01' ) > 0
      or
      ( select count(*) from rhpesrescisao where rh05_seqpes = rh02_seqpes ) = 0
    )

order by z01_cgccpf
) as x

union all

select * from (
select
        'DD' ||
        rpad( $unidade ,6) ||
        lpad(z01_cgccpf,11,0) ||
        rpad(trim(to_char(rh01_regist,'9999999999')),'20') ||
        rpad( " . $_POST['ano'] . " ,4) ||
        lpad( " . $_POST['mes'] . " ,2,0) ||
        rpad(1,1) ||
        lpad(1,2,'0') ||
        case when r35_pd = 1 then 'C' when r35_pd = 2 then 'D' else ' ' end ||
        lpad(translate( lpad ( coalesce ( ( select r35_quant::numeric(15,2) ),0),10,'0'),'.',''),10,'0') ||
        rpad(rh27_descr,20) ||
        (SELECT replace(lpad(r35_valor::numeric(15,2)::varchar,16,'0'),'.','')) ||

        case when ( select count(*) from basesr where r09_anousu = r35_anousu and r09_mesusu = r35_mesusu and r09_instit = r35_instit and r09_base in ('B001') and r09_rubric = r35_rubric ) > 0 then 'S' else 'N' end ||
        case when ( select count(*) from basesr where r09_anousu = r35_anousu and r09_mesusu = r35_mesusu and r09_instit = r35_instit and r09_base in ('B004') and r09_rubric = r35_rubric ) > 0 then 'S' else 'N' end ||
        case when ( select count(*) from basesr where r09_anousu = r35_anousu and r09_mesusu = r35_mesusu and r09_instit = r35_instit and r09_base = 'B031' and r09_rubric = r35_rubric ) > 0 then 'S' else 'N' end ||

        repeat(' ',4) ||
        '3' as conteudo
from gerfs13
inner join rhpessoalmov on rh02_anousu = r35_anousu and rh02_mesusu = r35_mesusu and rh02_regist = r35_regist and rh02_instit = r35_instit
inner join rhrubricas  on  rhrubricas.rh27_rubric = gerfs13.r35_rubric and rhrubricas.rh27_instit = gerfs13.r35_instit
inner join rhpessoal on rh01_regist = r35_regist
inner join cgm on z01_numcgm = rh01_numcgm
where r35_anousu = " . $_POST['ano'] . " and r35_mesusu = " . $_POST['mes'] . " and r35_instit = " . db_getsession("DB_instit") . "
AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
and r35_pd in (1,2)

and(
        (select count(*) from gerfs13 where r35_regist = rh02_regist and r35_anousu = rh02_anousu and r35_mesusu = rh02_mesusu and r35_instit = rh02_instit) > 0
                or
        (select count(*) from gerfres where r20_regist = rh02_regist and r20_anousu = rh02_anousu and r20_mesusu = rh02_mesusu and r20_instit = rh02_instit) > 0
      )
and (

      ( select count(*) from rhpesrescisao where rh05_seqpes = rh02_seqpes and rh05_recis >= '2017-07-01' ) > 0
      or
      ( select count(*) from rhpesrescisao where rh05_seqpes = rh02_seqpes ) = 0
    )

order by z01_cgccpf
) as x

union all

select * from (
select
        'DD' ||
        rpad( $unidade ,6) ||
        lpad(z01_cgccpf,11,0) ||
        rpad(trim(to_char(rh01_regist,'9999999999')),'20') ||
        rpad( " . $_POST['ano'] . " ,4) ||
        lpad( " . $_POST['mes'] . " ,2,0) ||
        rpad(1,1) ||
        lpad(1,2,'0') ||
        case when r48_pd = 1 then 'C' when r48_pd = 2 then 'D' else ' ' end ||
        lpad(translate( lpad ( coalesce ( ( select r48_quant::numeric(15,2) ),0),10,'0'),'.',''),10,'0') ||
        rpad(rh27_descr,20) ||
        (SELECT replace(lpad(r48_valor::numeric(15,2)::varchar,16,'0'),'.','')) ||

        case when ( select count(*) from basesr where r09_anousu = r48_anousu and r09_mesusu = r48_mesusu and r09_instit = r48_instit and r09_base in ('B001') and r09_rubric = r48_rubric ) > 0 then 'S' else 'N' end ||
        case when ( select count(*) from basesr where r09_anousu = r48_anousu and r09_mesusu = r48_mesusu and r09_instit = r48_instit and r09_base in ('B004') and r09_rubric = r48_rubric ) > 0 then 'S' else 'N' end ||
        case when ( select count(*) from basesr where r09_anousu = r48_anousu and r09_mesusu = r48_mesusu and r09_instit = r48_instit and r09_base = 'B031' and r09_rubric = r48_rubric ) > 0 then 'S' else 'N' end ||

        repeat(' ',4) ||
        '3' as conteudo
from gerfcom
inner join rhpessoalmov on rh02_anousu = r48_anousu and rh02_mesusu = r48_mesusu and rh02_regist = r48_regist and rh02_instit = r48_instit
inner join rhrubricas  on  rhrubricas.rh27_rubric = gerfcom.r48_rubric and rhrubricas.rh27_instit = gerfcom.r48_instit
inner join rhpessoal on rh01_regist = r48_regist
inner join cgm on z01_numcgm = rh01_numcgm
where r48_anousu = " . $_POST['ano'] . " and r48_mesusu = " . $_POST['mes'] . " and r48_instit = " . db_getsession("DB_instit") . "
AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
and r48_pd in (1,2)

and(
        (select count(*) from gerfcom where r48_regist = rh02_regist and r48_anousu = rh02_anousu and r48_mesusu = rh02_mesusu and r48_instit = rh02_instit) > 0
                or
        (select count(*) from gerfres where r20_regist = rh02_regist and r20_anousu = rh02_anousu and r20_mesusu = rh02_mesusu and r20_instit = rh02_instit) > 0
      )
and (

      ( select count(*) from rhpesrescisao where rh05_seqpes = rh02_seqpes and rh05_recis >= '2017-07-01' ) > 0
      or
      ( select count(*) from rhpesrescisao where rh05_seqpes = rh02_seqpes ) = 0
    )

order by z01_cgccpf
) as x

union all

select * from (
select
        'DD' ||
        rpad( $unidade ,6) ||
        lpad(z01_cgccpf,11,0) ||
        rpad(trim(to_char(rh01_regist,'9999999999')),'20') ||
        rpad( " . $_POST['ano'] . " ,4) ||
        lpad( " . $_POST['mes'] . " ,2,0) ||
        rpad(1,1) ||
        lpad(1,2,'0') ||
        case when r20_pd = 1 then 'C' when r20_pd = 2 then 'D' else ' ' end ||
        lpad(translate( lpad ( coalesce ( ( select r20_quant::numeric(15,2) ),0),10,'0'),'.',''),10,'0') ||
        rpad(rh27_descr,20) ||
        (SELECT replace(lpad(r20_valor::numeric(15,2)::varchar,16,'0'),'.','')) ||

        case when ( select count(*) from basesr where r09_anousu = r20_anousu and r09_mesusu = r20_mesusu and r09_instit = r20_instit and r09_base in ('B001') and r09_rubric = r20_rubric ) > 0 then 'S' else 'N' end ||
        case when ( select count(*) from basesr where r09_anousu = r20_anousu and r09_mesusu = r20_mesusu and r09_instit = r20_instit and r09_base in ('B004') and r09_rubric = r20_rubric ) > 0 then 'S' else 'N' end ||
        case when ( select count(*) from basesr where r09_anousu = r20_anousu and r09_mesusu = r20_mesusu and r09_instit = r20_instit and r09_base = 'B031' and r09_rubric = r20_rubric ) > 0 then 'S' else 'N' end ||

        repeat(' ',4) ||
        '3' as conteudo
from gerfres
inner join rhpessoalmov on rh02_anousu = r20_anousu and rh02_mesusu = r20_mesusu and rh02_regist = r20_regist and rh02_instit = r20_instit
inner join rhrubricas  on  rhrubricas.rh27_rubric = gerfres.r20_rubric and rhrubricas.rh27_instit = gerfres.r20_instit
inner join rhpessoal on rh01_regist = r20_regist
inner join cgm on z01_numcgm = rh01_numcgm
where r20_anousu = " . $_POST['ano'] . " and r20_mesusu = " . $_POST['mes'] . " and r20_instit = " . db_getsession("DB_instit") . "
AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
and r20_pd in (1,2)

and(
        (select count(*) from gerfsal where r14_regist = rh02_regist and r14_anousu = rh02_anousu and r14_mesusu = rh02_mesusu and r14_instit = rh02_instit) > 0
                or
        (select count(*) from gerfs13 where r35_regist = rh02_regist and r35_anousu = rh02_anousu and r35_mesusu = rh02_mesusu and r35_instit = rh02_instit) > 0
                or
        (select count(*) from gerfcom where r48_regist = rh02_regist and r48_anousu = rh02_anousu and r48_mesusu = rh02_mesusu and r48_instit = rh02_instit) > 0
                or
        (select count(*) from gerfres where r20_regist = rh02_regist and r20_anousu = rh02_anousu and r20_mesusu = rh02_mesusu and r20_instit = rh02_instit) > 0
      )
and (

      ( select count(*) from rhpesrescisao where rh05_seqpes = rh02_seqpes and rh05_recis >= '2017-07-01' ) > 0
      or
      ( select count(*) from rhpesrescisao where rh05_seqpes = rh02_seqpes ) = 0
    )

order by z01_cgccpf
) as x

union all

SELECT
      'TT' ||
       lpad(((select count(*)
          FROM gerfsal
    INNER JOIN rhpessoalmov ON rh02_anousu = r14_anousu AND rh02_mesusu = r14_mesusu AND rh02_regist = r14_regist AND rh02_instit = r14_instit
    INNER JOIN rhrubricas ON rhrubricas.rh27_rubric = gerfsal.r14_rubric AND rhrubricas.rh27_instit = gerfsal.r14_instit
    INNER JOIN rhpessoal ON rh01_regist = r14_regist
    INNER JOIN cgm ON z01_numcgm = rh01_numcgm
    WHERE r14_anousu = " . $_POST['ano'] . "
           AND r14_mesusu = " . $_POST['mes'] . "
           AND r14_instit = " . db_getsession("DB_instit") . "
           AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
           AND r14_pd IN (1,2)
           
           AND ((SELECT count(*)
                FROM gerfsal
                  WHERE r14_regist = rh02_regist
                      AND r14_anousu = rh02_anousu
                      AND r14_mesusu = rh02_mesusu
                      AND r14_instit = rh02_instit
            ) > 0
            OR
                (SELECT count(*)
                FROM gerfs13
                  WHERE r35_regist = rh02_regist
                    AND r35_anousu = rh02_anousu
                    AND r35_mesusu = rh02_mesusu
                    AND r35_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfcom
                  WHERE r48_regist = rh02_regist
                    AND r48_anousu = rh02_anousu
                    AND r48_mesusu = rh02_mesusu
                    AND r48_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfres
                  WHERE r20_regist = rh02_regist
                    AND r20_anousu = rh02_anousu
                    AND r20_mesusu = rh02_mesusu
                    AND r20_instit = rh02_instit
                ) > 0
               )
          AND ((SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
                   AND rh05_recis >= '2017-07-01'
               ) > 0
               OR
               (SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
               ) = 0
              )
       )
    +

    (SELECT count(*)
          FROM gerfs13
    INNER JOIN rhpessoalmov ON rh02_anousu = r35_anousu AND rh02_mesusu = r35_mesusu AND rh02_regist = r35_regist AND rh02_instit = r35_instit
    INNER JOIN rhrubricas ON rhrubricas.rh27_rubric = gerfs13.r35_rubric AND rhrubricas.rh27_instit = gerfs13.r35_instit
    INNER JOIN rhpessoal ON rh01_regist = r35_regist
    INNER JOIN cgm ON z01_numcgm = rh01_numcgm
         WHERE r35_anousu = " . $_POST['ano'] . "
           AND r35_mesusu = " . $_POST['mes'] . "
           AND r35_instit = " . db_getsession("DB_instit") . "
           AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
           AND r35_pd IN (1,2)

           AND ((SELECT count(*)
                   FROM gerfsal
                  WHERE r14_regist = rh02_regist
                    AND r14_anousu = rh02_anousu
                    AND r14_mesusu = rh02_mesusu
                    AND r14_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfs13
                  WHERE r35_regist = rh02_regist
                    AND r35_anousu = rh02_anousu
                    AND r35_mesusu = rh02_mesusu
                    AND r35_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfcom
                  WHERE r48_regist = rh02_regist
                    AND r48_anousu = rh02_anousu
                    AND r48_mesusu = rh02_mesusu
                    AND r48_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfres
                  WHERE r20_regist = rh02_regist
                    AND r20_anousu = rh02_anousu
                    AND r20_mesusu = rh02_mesusu
                    AND r20_instit = rh02_instit
                ) > 0
               )
          AND ((SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
                   AND rh05_recis >= '2017-07-01'
               ) > 0
               OR
               (SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
               ) = 0
    ))

    +

    (SELECT count(*)
          FROM gerfcom
    INNER JOIN rhpessoalmov ON rh02_anousu = r48_anousu AND rh02_mesusu = r48_mesusu AND rh02_regist = r48_regist AND rh02_instit = r48_instit
    INNER JOIN rhrubricas ON rhrubricas.rh27_rubric = gerfcom.r48_rubric AND rhrubricas.rh27_instit = gerfcom.r48_instit
    INNER JOIN rhpessoal ON rh01_regist = r48_regist
    INNER JOIN cgm ON z01_numcgm = rh01_numcgm
         WHERE r48_anousu = " . $_POST['ano'] . "
           AND r48_mesusu = " . $_POST['mes'] . "
           AND r48_instit = " . db_getsession("DB_instit") . "
           AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
           AND r48_pd IN (1,2)

           AND ((SELECT count(*)
                   FROM gerfsal
                  WHERE r14_regist = rh02_regist
                    AND r14_anousu = rh02_anousu
                    AND r14_mesusu = rh02_mesusu
                    AND r14_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfs13
                  WHERE r35_regist = rh02_regist
                    AND r35_anousu = rh02_anousu
                    AND r35_mesusu = rh02_mesusu
                    AND r35_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfcom
                  WHERE r48_regist = rh02_regist
                    AND r48_anousu = rh02_anousu
                    AND r48_mesusu = rh02_mesusu
                    AND r48_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfres
                  WHERE r20_regist = rh02_regist
                    AND r20_anousu = rh02_anousu
                    AND r20_mesusu = rh02_mesusu
                    AND r20_instit = rh02_instit
                ) > 0
               )
          AND ((SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
                   AND rh05_recis >= '2017-07-01'
               ) > 0
               OR
               (SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
               ) = 0
    ))

    +

    (SELECT count(*)
          FROM gerfres
    INNER JOIN rhpessoalmov ON rh02_anousu = r20_anousu AND rh02_mesusu = r20_mesusu AND rh02_regist = r20_regist AND rh02_instit = r20_instit
    INNER JOIN rhrubricas ON rhrubricas.rh27_rubric = gerfres.r20_rubric AND rhrubricas.rh27_instit = gerfres.r20_instit
    INNER JOIN rhpessoal ON rh01_regist = r20_regist
    INNER JOIN cgm ON z01_numcgm = rh01_numcgm
         WHERE r20_anousu = " . $_POST['ano'] . "
           AND r20_mesusu = " . $_POST['mes'] . "
           AND r20_instit = " . db_getsession("DB_instit") . "
           AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
           AND r20_pd IN (1,2)

           AND ((SELECT count(*)
                   FROM gerfsal
                  WHERE r14_regist = rh02_regist
                    AND r14_anousu = rh02_anousu
                    AND r14_mesusu = rh02_mesusu
                    AND r14_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfs13
                  WHERE r35_regist = rh02_regist
                    AND r35_anousu = rh02_anousu
                    AND r35_mesusu = rh02_mesusu
                    AND r35_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfcom
                  WHERE r48_regist = rh02_regist
                    AND r48_anousu = rh02_anousu
                    AND r48_mesusu = rh02_mesusu
                    AND r48_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfres
                  WHERE r20_regist = rh02_regist
                    AND r20_anousu = rh02_anousu
                    AND r20_mesusu = rh02_mesusu
                    AND r20_instit = rh02_instit
                ) > 0
               )
          AND ((SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
                   AND rh05_recis >= '2017-07-01'
               ) > 0
               OR
               (SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
               ) = 0
          )
       ))
  ,15,'0'
  )

||

lpad(translate(lpad(coalesce(((SELECT sum(r14_valor)::numeric(15,2)
          FROM gerfsal
    INNER JOIN rhpessoalmov ON rh02_anousu = r14_anousu AND rh02_mesusu = r14_mesusu AND rh02_regist = r14_regist AND rh02_instit = r14_instit
    INNER JOIN rhrubricas ON rhrubricas.rh27_rubric = gerfsal.r14_rubric AND rhrubricas.rh27_instit = gerfsal.r14_instit
    INNER JOIN rhpessoal ON rh01_regist = r14_regist
    INNER JOIN cgm ON z01_numcgm = rh01_numcgm
         WHERE r14_anousu = " . $_POST['ano'] . "
           AND r14_mesusu = " . $_POST['mes'] . "
           AND r14_instit = " . db_getsession("DB_instit") . "
           AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
           AND r14_pd IN (1,2)

           AND ((SELECT count(*)
                   FROM gerfsal
                  WHERE r14_regist = rh02_regist
                    AND r14_anousu = rh02_anousu
                    AND r14_mesusu = rh02_mesusu
                    AND r14_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfs13
                  WHERE r35_regist = rh02_regist
                    AND r35_anousu = rh02_anousu
                    AND r35_mesusu = rh02_mesusu
                    AND r35_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfcom
                  WHERE r48_regist = rh02_regist
                    AND r48_anousu = rh02_anousu
                    AND r48_mesusu = rh02_mesusu
                    AND r48_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfres
                  WHERE r20_regist = rh02_regist
                    AND r20_anousu = rh02_anousu
                    AND r20_mesusu = rh02_mesusu
                    AND r20_instit = rh02_instit
                ) > 0
               )
          AND ((SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
                   AND rh05_recis >= '2017-07-01'
               ) > 0
               OR
               (SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
               ) = 0
              )
       )

    +

   coalesce((SELECT sum(r35_valor)::numeric(15,2)
          FROM gerfs13
    INNER JOIN rhpessoalmov ON rh02_anousu = r35_anousu AND rh02_mesusu = r35_mesusu AND rh02_regist = r35_regist AND rh02_instit = r35_instit
    INNER JOIN rhrubricas ON rhrubricas.rh27_rubric = gerfs13.r35_rubric AND rhrubricas.rh27_instit = gerfs13.r35_instit
    INNER JOIN rhpessoal ON rh01_regist = r35_regist
    INNER JOIN cgm ON z01_numcgm = rh01_numcgm
         WHERE r35_anousu = " . $_POST['ano'] . "
           AND r35_mesusu = " . $_POST['mes'] . "
           AND r35_instit = " . db_getsession("DB_instit") . "
           AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
           AND r35_pd IN (1,2)

           AND ((SELECT count(*)
                   FROM gerfsal
                  WHERE r14_regist = rh02_regist
                    AND r14_anousu = rh02_anousu
                    AND r14_mesusu = rh02_mesusu
                    AND r14_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfs13
                  WHERE r35_regist = rh02_regist
                    AND r35_anousu = rh02_anousu
                    AND r35_mesusu = rh02_mesusu
                    AND r35_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfcom
                  WHERE r48_regist = rh02_regist
                    AND r48_anousu = rh02_anousu
                    AND r48_mesusu = rh02_mesusu
                    AND r48_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfres
                  WHERE r20_regist = rh02_regist
                    AND r20_anousu = rh02_anousu
                    AND r20_mesusu = rh02_mesusu
                    AND r20_instit = rh02_instit
                ) > 0
               )
          AND ((SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
                   AND rh05_recis >= '2017-07-01'
               ) > 0
               OR
               (SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
               ) = 0
              )
       ),0)
    +

    coalesce((SELECT sum(r48_valor)::numeric(15,2)
          FROM gerfcom
    INNER JOIN rhpessoalmov ON rh02_anousu = r48_anousu AND rh02_mesusu = r48_mesusu AND rh02_regist = r48_regist AND rh02_instit = r48_instit
    INNER JOIN rhrubricas ON rhrubricas.rh27_rubric = gerfcom.r48_rubric AND rhrubricas.rh27_instit = gerfcom.r48_instit
    INNER JOIN rhpessoal ON rh01_regist = r48_regist
    INNER JOIN cgm ON z01_numcgm = rh01_numcgm
         WHERE r48_anousu = " . $_POST['ano'] . "
           AND r48_mesusu = " . $_POST['mes'] . "
           AND r48_instit = " . db_getsession("DB_instit") . "
           AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
           AND r48_pd IN (1,2)

           AND ((SELECT count(*)
                   FROM gerfsal
                  WHERE r14_regist = rh02_regist
                    AND r14_anousu = rh02_anousu
                    AND r14_mesusu = rh02_mesusu
                    AND r14_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfs13
                  WHERE r35_regist = rh02_regist
                    AND r35_anousu = rh02_anousu
                    AND r35_mesusu = rh02_mesusu
                    AND r35_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfcom
                  WHERE r48_regist = rh02_regist
                    AND r48_anousu = rh02_anousu
                    AND r48_mesusu = rh02_mesusu
                    AND r48_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfres
                  WHERE r20_regist = rh02_regist
                    AND r20_anousu = rh02_anousu
                    AND r20_mesusu = rh02_mesusu
                    AND r20_instit = rh02_instit
                ) > 0
               )
          AND ((SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
                   AND rh05_recis >= '2017-07-01'
               ) > 0
               OR
               (SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
               ) = 0
              )
       ),0)

    +

    coalesce((SELECT sum(r20_valor)::numeric(15,2)
          FROM gerfres
    INNER JOIN rhpessoalmov ON rh02_anousu = r20_anousu AND rh02_mesusu = r20_mesusu AND rh02_regist = r20_regist AND rh02_instit = r20_instit
    INNER JOIN rhrubricas ON rhrubricas.rh27_rubric = gerfres.r20_rubric AND rhrubricas.rh27_instit = gerfres.r20_instit
    INNER JOIN rhpessoal ON rh01_regist = r20_regist
    INNER JOIN cgm ON z01_numcgm = rh01_numcgm
         WHERE r20_anousu = " . $_POST['ano'] . "
           AND r20_mesusu = " . $_POST['mes'] . "
           AND r20_instit = " . db_getsession("DB_instit") . "
           AND rh01_admiss <= (" . $_POST['ano'] . "||'-12-31')::date
           AND r20_pd IN (1,2)

           AND ((SELECT count(*)
                   FROM gerfsal
                  WHERE r14_regist = rh02_regist
                    AND r14_anousu = rh02_anousu
                    AND r14_mesusu = rh02_mesusu
                    AND r14_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfs13
                  WHERE r35_regist = rh02_regist
                    AND r35_anousu = rh02_anousu
                    AND r35_mesusu = rh02_mesusu
                    AND r35_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfcom
                  WHERE r48_regist = rh02_regist
                    AND r48_anousu = rh02_anousu
                    AND r48_mesusu = rh02_mesusu
                    AND r48_instit = rh02_instit
                ) > 0
                OR
                (SELECT count(*)
                   FROM gerfres
                  WHERE r20_regist = rh02_regist
                    AND r20_anousu = rh02_anousu
                    AND r20_mesusu = rh02_mesusu
                    AND r20_instit = rh02_instit
                ) > 0
               )
                AND ((SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
                   AND rh05_recis >= '2017-07-01'
               ) > 0
               OR
               (SELECT count(*)
                  FROM rhpesrescisao
                 WHERE rh05_seqpes = rh02_seqpes
               ) = 0
              )
       ),0)),0
                            )::varchar,15,'0'
                   ),'.',''
              ),15,'0'
    )
||
repeat(' ',69)
||
'3'
  ";
    return $sql;
}

?>

