<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));

set_time_limit(0);
ini_set('max_execution_time', 0);

try {

if (!empty($_POST['gerar'])) {

  if (empty($_POST['datainicial_ano'])) {
    throw new Exception("Data inicial inválida");
  }

  if (empty($_POST['datafinal_ano'])) {
    throw new Exception("Data final inválida");
  }

  $iAno = $_POST['ano'];
  $iInstit = $_POST['instit'];
  $sDataInicial = $_POST['datainicial_ano'] . '-' . $_POST['datainicial_mes'] . '-' . $_POST['datainicial_dia'];
  $sDataFinal   = $_POST['datafinal_ano'] . '-' . $_POST['datafinal_mes'] . '-' . $_POST['datafinal_dia'];

  $sSqlInicial =  "select o56_elemento,                                                                 \n";
  $sSqlInicial .= "      o56_descr,                                                                     \n";
  $sSqlInicial .= "      sum(case                                                                       \n";
  $sSqlInicial .= "            when c53_tipo = 20 then conlancam.c70_valor                              \n";
  $sSqlInicial .= "            else (conlancam.c70_valor*-1)                                            \n";
  $sSqlInicial .= "          end) as valor                                                              \n";
  $sSqlInicial .= " from conlancamdoc                                                                   \n";
  $sSqlInicial .= "      inner join conhistdoc      on c53_coddoc = c71_coddoc                          \n";
  $sSqlInicial .= "      inner join conlancam       on c70_codlan = c71_codlan                          \n";
  $sSqlInicial .= "      inner join conlancaminstit on c02_codlan = c70_codlan                          \n";
  $sSqlInicial .= "      inner join conlancamele    on conlancamele.c67_codlan = c70_codlan             \n";
  $sSqlInicial .= "      inner join orcelemento     on orcelemento.o56_codele = conlancamele.c67_codele \n";
  $sSqlInicial .= "where c53_tipo in (20, 21)                                                           \n";
  $sSqlInicial .= "  and c71_data between '$sDataInicial' and '$sDataFinal'                             \n";
  $sSqlInicial .= "  and orcelemento.o56_anousu = $iAno                                                 \n";
  $sSqlInicial .= "  and c02_instit = $iInstit and c70_anousu = $iAno                                   \n";
  $sSqlInicial .= "group by o56_elemento,o56_descr                                                      \n";

  $rsElementos = db_query($sSqlInicial);

  if (!$rsElementos) {
    throw new Exception("Não foi possível buscar os elementos.");
  }

  $aElements = db_utils::getCollectionByRecord($rsElementos);

}

} catch (Exception $e) {
  $sErrorMessage = $e->getMessage();
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

    <style type="text/css">

      .container > table {
        border-spacing: 0;
      }

      .container > table table {
        border-spacing: 0;
      }

      .container > table table {
        border-bottom: 2px solid #ccc;
      }

      .container > table > tbody > tr > th {
        background-color: #EFEFEF;
      }

      .container > table > tbody > tr > td {
        background-color: white;
      }

      .container > table > tbody > tr > td > table > tbody > tr > th {
        background-color: #ddd;
      }

      .container > table > tbody > tr > td > table > tbody > tr > td {
        background-color: #eee;
      }

    </style>


  </head>

  <body class="body-default">

    <div class="container">

      <?php

        if (!empty($sErrorMessage)) {
          echo '<p class="error">'. $sErrorMessage .'</p>';
        }

      ?>

      <form method="POST">

        <table class="form-container">

          <tr>
            <td><label for="instit">Instituicao:</label></td>
            <td><?php db_input('instit', 10, 1, true, 'text', 1);?></td>
          </tr>

          <tr>
            <td><label for="ano">Ano:</label></td>
            <td><?php db_input('ano', 10, 1, true, 'text', 1);?></td>
          </tr>

          <tr>
            <td><label for="datainicial">Data Inicial:</label></td>
            <td><?php db_inputdata("datainicial", "","", "", true, 'text', 1); ?></td>
          </tr>

          <tr>
            <td><label for="datafinal">Data Final:</label></td>
            <td><?php db_inputdata("datafinal", "","", "", true, 'text', 1); ?></td>
          </tr>

        </table>

        <br />

        <input name="gerar" type="submit" id="gerar" value="Gerar Relatório" />

      </form>

      <?php if (isset($aElements)) : ?>

        <hr />

        <table>

          <tr>
            <th>Elemento</th>
            <th>Descrição</th>
            <th>Valor</th>
          </tr>

          <?php foreach($aElements as $oElemento): ?>

            <tr>
              <td><?php echo $oElemento->o56_elemento; ?></td>
              <td><?php echo $oElemento->o56_descr; ?></td>
              <td><?php echo $oElemento->valor; ?></td>
            </tr>

            <tr>

            <td></td>
            <td colspan="2">

              <table width="100%">
                <tr>
                  <th>Estrutural</th>
                  <th>Descrição</th>
                  <th>Valor</th>
                  <th>Qtd</th>

                </tr>

                <?php

                  $sSqlFinal  = "select                                                    ";
                  $sSqlFinal .= "       case                                               ";
                  $sSqlFinal .= "         when c53_tipo = 20 then planodeb.c60_estrut      ";
                  $sSqlFinal .= "         else planocred.c60_estrut                        ";
                  $sSqlFinal .= "       end as estrutural,                                 ";
                  $sSqlFinal .= "       case                                               ";
                  $sSqlFinal .= "         when c53_tipo = 20 then planodeb.c60_descr       ";
                  $sSqlFinal .= "         else planocred.c60_descr                         ";
                  $sSqlFinal .= "       end as descricao,                                  ";
                  $sSqlFinal .= "       sum(case                                           ";
                  $sSqlFinal .= "          when c53_tipo = 20 then conlancamval.c69_valor  ";
                  $sSqlFinal .= "          else (conlancamval.c69_valor*-1)                                 ";
                  $sSqlFinal .= "       end) as valor                                 , count(*) as qtd                      ";
                  $sSqlFinal .= "  from conlancamval inner join conlancaminstit on c02_codlan =  c69_codlan ";
                  $sSqlFinal .= "       inner join conlancamdoc on c71_codlan = c69_codlan                  ";
                  $sSqlFinal .= "       inner join conhistdoc   on c53_coddoc = c71_coddoc       inner join conplanoreduz reduzcred on reduzcred.c61_reduz  = c69_credito";
                  $sSqlFinal .= "       inner join conplano      planocred on planocred.c60_codcon = reduzcred.c61_codcon";
                  $sSqlFinal .= "                                         and planocred.c60_anousu = reduzcred.c61_anousu";
                  $sSqlFinal .= "       inner join conplanoreduz reduzdeb  on reduzdeb.c61_reduz  = c69_debito";
                  $sSqlFinal .= "       inner join conplano      planodeb  on planodeb.c60_codcon = reduzdeb.c61_codcon";
                  $sSqlFinal .= "                                         and planodeb.c60_anousu = reduzdeb.c61_anousu";
                  $sSqlFinal .= "       inner join conlancamele on conlancamele.c67_codlan = c69_codlan";
                  $sSqlFinal .= "       inner join orcelemento on orcelemento.o56_codele = conlancamele.c67_codele";
                  $sSqlFinal .= " where o56_elemento = '{$oElemento->o56_elemento}'";
                  $sSqlFinal .= "   and substr(planocred.c60_estrut,1,1) not in ('5','6','7','8')";
                  $sSqlFinal .= "   and substr(planodeb.c60_estrut,1,1) not in ('5','6','7','8')";
                  $sSqlFinal .= "   and planocred.c60_anousu = $iAno ";
                  $sSqlFinal .= "   and planodeb.c60_anousu  = $iAno ";
                  $sSqlFinal .= "   and c69_data between  '$sDataInicial' and '$sDataFinal' ";
                  $sSqlFinal .= "   and c69_anousu = 2014 and c53_tipo in (20, 21) ";
                  $sSqlFinal .= "   and o56_anousu = $iAno ";
                  $sSqlFinal .= "   and c02_instit = $iInstit ";
                  $sSqlFinal .= " group by estrutural, descricao";

                  $rsComlancamval = db_query($sSqlFinal);

                  if (!$rsComlancamval) {
                    echo 'ERRO - ' . pg_last_error();
                    continue;
                  }

                  $aComlancamval = db_utils::getCollectionByRecord($rsComlancamval);

                  foreach ($aComlancamval as $oComlancamval) :

                  ?>
                    <tr>
                      <td><?php echo $oComlancamval->estrutural; ?></td>
                      <td><?php echo $oComlancamval->descricao; ?></td>
                      <td><?php echo $oComlancamval->valor; ?></td>
                      <td><?php echo $oComlancamval->qtd; ?></td>
                    </tr>
                  <?php

                  endforeach;

                ?>

              </table>


            </td>

            </tr>

          <?php endforeach; ?>

        </table>

      <?php endif; ?>

    </div>

  <?php
    db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
  ?>

  </body>

</html>
<?php

?>