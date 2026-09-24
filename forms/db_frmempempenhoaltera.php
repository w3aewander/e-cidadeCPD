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
use \ECidade\Financeiro\Orcamento\Repository\RecursoRepository;

//MODULO: empenho
$clempempenho->rotulo->label();
$clrotulo = new rotulocampo;
$clrotulo->label("z01_nome");
$clrotulo->label("nome");
$clrotulo->label("e60_codemp");
$clrotulo->label("pc50_descr");
$clrotulo->label("e60_codcom");
$clrotulo->label("e63_codhist");
$clrotulo->label("e44_tipo");
$clrotulo->label("c58_descr");
$clrotulo->label("e60_tipol");

function dadosAuxFormulario($empenho){
    $sql2 = pg_query("SELECT * FROM empenhoauxsigfis WHERE seqempenho = {$empenho}");
        $resultado = pg_fetch_all($sql2);
        $aux = $resultado[0];

        if(!$aux){
            $sql1 = pg_query("SELECT e61_autori FROM empempaut WHERE e61_numemp = {$empenho}");
            $autorizacao = pg_fetch_all($sql1);
            $autorizacao = $autorizacao[0]["e61_autori"];

            $sql = pg_query("SELECT * FROM empenhoauxsigfis WHERE noautorizacaoempenho = {$autorizacao}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0];    
        }else{
            return $aux;
        }
}

function buscaUGs(){
    $sql = pg_query("SELECT c179_codigo, nomeinst FROM sigfisunidadegestora INNER JOIN db_config ON c179_instit = codigo INNER JOIN cgm ON c179_cgmordenadordespesa = z01_numcgm ORDER BY c179_instit");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}

$auxform = dadosAuxFormulario($e60_numemp);
if($e60_numemp){
    $xaeo = $auxform["aeo"];
    $xjaip = $auxform["jaip"];
    $xjaaj = $auxform["jaaj"];
    $xcgmordenador = $auxform["cgmordenador"];
    $xtipro = $auxform["tipro"];
    $xnoinpr = $auxform["noinpr"];
    $xtajuo = $auxform["tajuo"];
    $xnoatoju = $auxform["noatoju"];
    $xugaj = $auxform["ugaj"];
    $xfolhadiaria = ($auxform["folhadiaria"] == "sim") ? true : false;
    $xugip = $auxform["ugip"];
}


$outrosDados = null;
if (!empty($outros_dados)) {
    $outrosDados = json_decode($outros_dados);
}

$tiposCompraJustificaveis = ['7','8'];
$dispensaElementosAtoJuridico = [];
$e56_codele = "";
if(isRioDeJaneiro()){

    $daoOrcElemento = new cl_orcelemento;
    $anoUsu = db_getsession("DB_anousu");
    $where = "
      o56_anousu = $anoUsu and (
        substr(o56_elemento,3,1) in ('1','2','5','6') OR
        substr(o56_elemento,6,2) in (
          '01','04','03','06','07','08','10','11','12','13','14','15','16','17','18',
          '19','20','21','22','23','24','25','26','27','28','29','41','42','43','45','46','47','48','49','53','54',
          '55','56','57','58','59','65','70','71','72','73','74','75','76','77','81','91','93','94','95','96','97','98'
        )
      )  
    ";
    $sqlElementosDispensados = $daoOrcElemento->sql_query_file(null,null,'o56_codele',null,$where);
    $rsElementosDispensados = db_query($sqlElementosDispensados);
    for($i = 0;$i < pg_num_rows($rsElementosDispensados); $i++){
      $dispensaElementosAtoJuridico[] = db_utils::fieldsMemory($rsElementosDispensados,$i)->o56_codele;
    }
}
?>
<form name="form1" method="post" action="">
    <table border="0">        

        <tr>
            <td nowrap title="<?= @$Te60_codemp ?>">
                <?= @$Le60_codemp ?>
            </td>
            <td>
                <?php
                db_input('e60_numemp', 10, '', true, 'hidden', 3);
                db_input('e60_codemp', 10, $Ie60_codemp, true, 'text', 3);
                ?>
                <input type="checkbox" name="folhadiaria" id="folhadiaria" value="sim" <?=($xfolhadiaria) ? "checked" : ""?>>
                <label for="folhadiaria"><b>Folha de Pagamento/Diária</b></label>
            </td>
            
        </tr>
        <tr>
            <td nowrap title="<?= @$Te60_numcgm ?>">
                <?= $Le60_numcgm ?>
            </td>
            <td>
                <?php
                db_input('e60_numcgm', 10, $Ie60_numcgm, true, 'text', 3);
                db_input('z01_nome', 40, $Iz01_nome, true, 'text', 3, '');
                ?>
            </td>
        </tr>
        <tr>
            <td nowrap title="<?= @$Te60_codcom ?>">
                <?= $Le60_codcom ?>
            </td>
            <td>
                <?php

                $dao = new cl_pctipocompra();
                $campos = "pc50_codcom as e60_codcom, pc50_descr, l44_obrigalicitacao";
                $sql = $dao->sql_query(null, $campos, "pc50_descr", "pc50_ativo is true");
                $result = db_query($sql);
                $tiposCompra = db_utils::getCollectionByRecord($result);
                $aTipoCompra = [];
                foreach ($tiposCompra as $item) {
                    $aTipoCompra[$item->e60_codcom] = "{$item->e60_codcom} - {$item->pc50_descr}";
                }

                db_select('e60_codcom', $aTipoCompra, true, 1, "onchange='pesquisarTipoLicitacao()'");
                ?>
            </td>
        </tr>
        <tr>
            <td nowrap title="<?= @$Te60_tipol ?>">
                <?= @$Le60_tipol ?>
            </td>
            <td>
                <input type='hidden' id='tipoAtribuidoAnterior' value='<?=$e60_tipol?>' >
                <?php

                db_select('e60_tipol', [], true, 1);
                ?>
            </td>
        </tr>
        <tr>
            <td><strong>Número da Licitação:</strong></td>
            <td><?php
                $bloqueia = '';
                if (isset($l44_obrigalicitacao) && $l44_obrigalicitacao == 'f') {
                    $bloqueia = "class='readonly' readonly";
                }

                $numeroLicitacao = '';
                $anoLicitacao = '';
                if (!empty($e60_numerol)) {
                    $dadosLicitacao = explode('/', $e60_numerol);
                    $numeroLicitacao = $dadosLicitacao[0];
                    $anoLicitacao = !empty($dadosLicitacao[1]) ? $dadosLicitacao[1] : '';
                }
                ?>

                <input type="text" id="numeroLicitacao" name="numeroLicitacao" <?= $bloqueia ?> style="width: 150px"
                       oninput="js_ValidaCampos(this, 1, 'Número da Licitação', 'f', 'f', event)" maxlength="20"
                       value="<?=$numeroLicitacao?>">
                &nbsp;&nbsp;/&nbsp;&nbsp;
                <input type="text" id="anoLicitacao" name="anoLicitacao" <?= $bloqueia ?> style="width: 50px"
                       oninput="js_ValidaCampos(this, 1, 'Ano da Licitação', 'f', 'f', event)" maxlength="4"
                       value="<?=$anoLicitacao?>">
            </td>
        </tr>

        <tr>
            <td><label class="bold" for="licitacaoCompartilhada">Licitação Compartilhada:</label></td>
            <td>
                <?php
                $licitacao_compartilhada = 'X';
                if (!empty($outrosDados) && !empty($outrosDados->licitacao_compartilhada)) {
                    $licitacao_compartilhada = $outrosDados->licitacao_compartilhada;
                }

                $tipos = [
                    'X' => 'Não se Aplica',
                    'N' => 'Não',
                    'S' => 'Sim',
                ];

                db_select('licitacao_compartilhada', $tipos, false, 1);
                ?>

            </td>
        </tr>
        <tr style="display: none" id="linhaCNPJ">
            <td><label class="bold" for="cnpjGerenciador">CNPJ do órgão gerenciador:</label></td>
            <td>
                <?php
                $valor = '';
                if (!empty($outrosDados) && !empty($outrosDados->cnpj_gerenciador)) {
                    $valor = $outrosDados->cnpj_gerenciador;
                }

                ?>
                <input type="text" maxlength="14" id="cnpjGerenciador" name="cnpj_gerenciador" value="<?=$valor?>">
            </td>
        </tr>

        <tr>
            <td nowrap title="<?= @$Te60_codtipo ?>">
                <?= $Le60_codtipo ?>
            </td>
            <td>
                <?php
                $result = $clemptipo->sql_record($clemptipo->sql_query_file(null, "e41_codtipo,e41_descr"));
                db_selectrecord("e60_codtipo", $result, true, $db_opcao);

                ?>
            </td>
        </tr>

        <?php if (isParaiba() && substr($o56_elemento, 0, 7) === '3449051'): ?>
        <tr>
            <td nowrap title="Código do Geo Obra">
                <label for="geo_obra" class="bold">GEO Obras:</label>
            </td>
            <td>
                <?php
                $geo_obra = '';
                if (!empty($outros_dados)) {
                    $outrosDados = json_decode($outros_dados);
                    if (!empty($outrosDados->geo_obra)) {
                        $geo_obra = $outrosDados->geo_obra;
                    }
                }
                ?>
                <input type="text" class="field-size5" id="geo_obra" name="geo_obra" value="<?=$geo_obra?>"
                       oninput="js_ValidaCampos(this, 1, 'GEO Obras', 'f', 'f', event);" >
            </td>
        </tr>
        <?php endif; ?>

        <tr>
            <td nowrap title="<?= @$Te63_codhist ?>">
                <?= $Le63_codhist ?>
            </td>
            <td>
                <?php
                $result = $clemphist->sql_record($clemphist->sql_query_file(null, "e40_codhist,e40_descr"));
                db_selectrecord("e63_codhist", $result, true, 1, "", "", "", "Nenhum");
                ?>
            </td>
        </tr>
        <tr>
            <td nowrap title="<?= @$Te44_tipo ?>">
                <?= $Le44_tipo ?>
            </td>
            <td>
                <?php

                $sql = $clempprestatip->sql_query_file(null, "e44_tipo as tipo,e44_descr,e44_obriga", "e44_obriga ");
                $result = $clempprestatip->sql_record($sql);
                $numrows = $clempprestatip->numrows;

                $arr = array();
                for ($i = 0; $i < $numrows; $i++) {
                    db_fieldsmemory($result, $i);
                    if ($e44_obriga == 0 && empty($e44_tipo)) {
                        $e44_tipo = $tipo;
                    }
                    $arr[$tipo] = $e44_descr;
                }

                if ( isset($e60_numemp) && !empty($e60_numemp) ) {

                    $e44_tipo = 1;
                    $oDaoemppresta = new cl_emppresta;
                    $sqlTipo = $oDaoemppresta->sql_query_file(null, "e45_tipo", "e45_data", "e45_numemp = $e60_numemp");
                    $rs = $oDaoemppresta->sql_record($sqlTipo);

                    if ($oDaoemppresta->numrows > 0) {
                      $e44_tipo = db_utils::fieldsMemory($rs, 0)->e45_tipo;
                    }
                }
                db_select("e44_tipo", $arr, true, 3);

                ?>
            </td>
        </tr>

        <?php
        if (isset($e60_numemp)) {

            $sql = "select pagordem.* from pagordem inner join pagordemdesconto on e34_codord = e50_codord
	  													where e50_numemp = $e60_numemp";
            //die($sql);
            $result = $clpagordem->sql_record($sql);
            $ldesconto = false;
            if ($clpagordem->numrows > 0) {
                $ldesconto = true;
            }
        }
        if (isset($e60_vlrliq) && $e60_vlrliq == 0 && !$ldesconto && $e60_anousu >= db_getsession("DB_anousu")) {
            ?>
            <tr>
                <td nowrap title="Desdobramentos">
                    <b><?= "Desdobramento:" ?></b>
                </td>
                <td>
                    <?php
                        $result = $clempempaut->sql_record($clempempaut->sql_query(null, "e61_autori", "", "e61_numemp = $e60_numemp"));
                    if ($clempempaut->numrows > 0) {
                        $oResult = db_utils::fieldsMemory($result, 0);
                        $e54_autori = $oResult->e61_autori;
                        $anoUsu = db_getsession("DB_anousu");
                        $sWhere = "e56_autori = " . $e54_autori . " and e56_anousu = " . $anoUsu;
                        $result = $clempautidot->sql_record($clempautidot->sql_query_dotacao(null, "e56_coddot", null, $sWhere));

                        if ($clempautidot->numrows > 0) {
                            $oResult = db_utils::fieldsMemory($result, 0);
                            $result = $clorcdotacao->sql_record($clorcdotacao->sql_query($anoUsu, $oResult->e56_coddot, "o56_elemento,o56_codele"));
                            if ($clorcdotacao->numrows > 0) {
                                $oResult = db_utils::fieldsMemory($result, 0);
                                $oResult->estrutural = criaContaMae($oResult->o56_elemento . "00");
                                $sWhere = "o56_elemento like '$oResult->estrutural%' and o56_codele <> $oResult->o56_codele and o56_anousu = $anoUsu";
                                $sSql = "select distinct o56_codele,o56_elemento,o56_descr
											  from empempitem
											        inner join pcmater on pcmater.pc01_codmater    = empempitem.e62_item
											        inner join pcmaterele on pcmater.pc01_codmater = pcmaterele.pc07_codmater
											        left join orcelemento on orcelemento.o56_codele = pcmaterele.pc07_codele
											                              and orcelemento.o56_anousu = $anoUsu
											    where o56_elemento like '$oResult->estrutural%'
											    and e62_numemp = $e60_numemp and o56_anousu = $anoUsu";
                                $result = $clorcelemento->sql_record($sSql);

                                $oResult = db_utils::getCollectionByRecord($result);

                                $numrows = $clorcelemento->numrows;
                                $aEle = array();

                                foreach ($oResult as $oRow) {
                                    $aEle[$oRow->o56_codele] = $oRow->o56_descr;
                                }
                                //die($clempautitem->sql_query_autoriza (null,null,"e55_codele",null,"e55_autori = $e54_autori"));
                                $result = $clempelemento->sql_record($clempelemento->sql_query_file($e60_numemp, null, "e64_codele"));
                                if ($clempelemento->numrows > 0) {
                                    $oResult = db_utils::fieldsMemory($result, 0);
                                }
                                
                                $e56_codele = $oResult->e64_codele;
                                
                                $e64_codele = $e56_codele;
                                db_input('e64_codele', 10, 0, true, 'hidden', 3);
                                db_select("e56_codele", $aEle, true, 1);
                            }
                        }
                    } else {                        
                        $aEle = array();
                        $e56_codele = "";
                        db_select("e56_codele", $aEle, true, 1);
                    }
                    ?>
                </td>
            </tr>
            <?php
        } else {
            if (isset($e60_vlrliq) && $e60_vlrliq != 0) {
                $mensagem = "Você não pode alterar o desdobramento deste empenho porque este já possui valor liquidado. Se realmente for necessária a alteração, anule todas as liquidações";
            } else if (isset($ldesconto) && $ldesconto) {
                $mensagem = "Este empenho teve uma operação de desconto e isto inviabiliza a substituição do desdobramento.";
            }

        }
        ?>

        <tr id="trFinalidadeFundeb">
            <td><b>Finalidade:</b></td>
            <td>
                <?php
                $oDaoFinalidadeFundeb = db_utils::getDao('finalidadepagamentofundeb');
                $sSqlFinalidadeFundeb = $oDaoFinalidadeFundeb->sql_query_file(null, "e151_codigo, e151_descricao", "e151_codigo");
                $rsBuscaFinalidadeFundeb = $oDaoFinalidadeFundeb->sql_record($sSqlFinalidadeFundeb);
                db_selectrecord('e151_codigo', $rsBuscaFinalidadeFundeb, true, 1);
                ?>
            </td>
        </tr>

        <tr>
            <td class="bold">Complemento:</td>
            <td>
                <?php
                $complementosDisponiveis = [];
                $dataLimite = date('Y-m-d', db_getsession('DB_datausu'));
                if (!empty($e60_numemp)) {
                    $empenho = EmpenhoFinanceiroRepository::getEmpenhoFinanceiroPorNumero($e60_numemp);
                    $recurso = $empenho->getDotacao()->getDadosRecurso();
                    $fonte = $recurso->getFonteRecurso($empenho->getAno());

                    $complementosEncontrados = RecursoRepository::getComplementosByGestao(
                        $fonte->gestao,
                        $recurso->getRecurso(),
                        $fonte->exercicio,
                        $dataLimite
                    );
                    $complementosDisponiveis = [];
                    foreach ($complementosEncontrados as $complemento) {
                        $complementosDisponiveis[$complemento->codigo] = $complemento->descricao;
                    }
                    $registro = \ECidade\Financeiro\Orcamento\Recurso\Origem::getEmpenho($e60_numemp, db_getsession('DB_anousu'));
                    if (!empty($registro)) {
                        $complemento = $registro->o206_complementorecurso;
                    }
                }
                db_select('complemento', $complementosDisponiveis, true, 1);
                ?>
            </td>
        </tr>

        <?php if(isRioDeJaneiro() && !empty($e56_codele) && !in_array($e56_codele,$dispensaElementosAtoJuridico)) {?>
            <tr style = "display:none">
              <td class = 'bold'>Ato jurídico:</td>
              <td>              
                <?php
                  $daoEmpTipoAtoJuridoco = new cl_tipoatojuridico;
                  $sqlAtoJuridico = $daoEmpTipoAtoJuridoco->sql_query_file();              
                  $rsAtoJuridico = db_query($sqlAtoJuridico);                  
                  $tiposAtoJuridico = [];                    

                  for($i=0;$i<pg_num_rows($rsAtoJuridico);$i++){
                    $tipoAtoJuridico =  db_utils::fieldsMemory($rsAtoJuridico,$i);
                    if($tipoAtoJuridico->e165_ativo){
                      $codigoAtoJuridico = $tipoAtoJuridico->e165_sequencial;
                      $descricaoAtoJuridico = $tipoAtoJuridico->e165_sequencial.' - ';
                      $descricaoAtoJuridico .= $tipoAtoJuridico->e165_descricao;          
                      $tiposAtoJuridico[$codigoAtoJuridico] = $descricaoAtoJuridico;
                    }                  
                  }                  
              
                  db_select('tipoatojuridico', $tiposAtoJuridico, true, 1,"onchange='js_abreFechaBlocoJustificativa()'");
                ?>
              </td>
            </tr>

            <tr id="justificativaatojuridico_bloco" style = "display:none">            
              <td class = 'bold'>Justificativa Ausência de Ato Jurídico: </td>
              <td>
                <?php 
                  $daoJustificativaAtoJuridico = new \cl_justificativaatojuridico;
                  $sqlJustificativaAtoJuridico = $daoJustificativaAtoJuridico->sql_query_file();              
                  $rsJustificativaAtoJuridico =  db_query($sqlJustificativaAtoJuridico);                  
                  $justificativasAtoJuridico = [];                    
                  for($i=0;$i<pg_num_rows($rsJustificativaAtoJuridico);$i++){
                    $justificativaAtoJuridico =  db_utils::fieldsMemory($rsJustificativaAtoJuridico,$i);
                    if($justificativaAtoJuridico->e173_ativo){
                      $codigoJustificativa = $justificativaAtoJuridico->e173_sequencial;
                      $descricaoJustificativa = $justificativaAtoJuridico->e173_sequencial.' - ';
                      $descricaoJustificativa .= $justificativaAtoJuridico->e173_descricao;          
                      $justificativasAtoJuridico[$codigoJustificativa] = $descricaoJustificativa;
                    }                  
                  }
                                  
                  db_select('justificativaatojuridico', $justificativasAtoJuridico, true, 1);
                                   
                ?>
              </td>
            </tr>

            <tr id="justificativainstrumentoprevio_bloco" style = "display:none">
              <td class = 'bold'>Justificativa Ausência de Instrumento Prévio: </td>
              <td>
                <?php
                  $daoJustificativaInstrumentoPrevio = new \cl_justificativainstrumentoprevio;
                  $sqlJustificativaInstrumentoPrevio = $daoJustificativaInstrumentoPrevio->sql_query_file();              
                  $rsJustificativaInstrumentoPrevio =  db_query($sqlJustificativaInstrumentoPrevio);                  
                  $justificativasInstrumentoPrevio = [];                    
                  for($i=0;$i<pg_num_rows($rsJustificativaInstrumentoPrevio);$i++){
                    $justificativaInstrumentoPrevio =  db_utils::fieldsMemory($rsJustificativaInstrumentoPrevio,$i);
                    if($justificativaInstrumentoPrevio->e174_ativo){
                      $codigoJustificativa = $justificativaInstrumentoPrevio->e174_sequencial;
                      $descricaoJustificativa = $justificativaInstrumentoPrevio->e174_sequencial.' - ';
                      $descricaoJustificativa .= $justificativaInstrumentoPrevio->e174_descricao;          
                      $justificativasInstrumentoPrevio[$codigoJustificativa] = $descricaoJustificativa;
                    }                  
                  }                 
                  db_select('justificativainstrumentoprevio', $justificativasInstrumentoPrevio, true, 1);    
                ?>
              </td>
            </tr> 
                
          <?php }?>    

        <!-- indicativo de aquisição de produção rural -->
        <tr id="trAqProd" style="display: none;">
            <td><b>Tipo de Aquisicão: </b></td>
            <td>
                <select name="indAqProd" id="indAqProd"></select>
            </td>
        </tr>

        <tr>
            <td nowrap title="<?= @$Te60_destin ?>">
                <?= @$Le60_destin ?>
            </td>
            <td>
                <?php
                db_input('e60_destin', 40, $Ie60_destin, true, 'text', $db_opcao, "")
                ?>
            </td>
        </tr>

        <tr>
            <td nowrap title="<?= @$Te60_resumo ?>" colspan="2">
                <fieldset>
                    <legend><b><?= @$Le60_resumo ?></b></legend>
                    <?php
                    db_textarea('e60_resumo', 8, 90, $Ie60_resumo, true, 'text', $db_opcao, "")
                    ?>
                </fieldset>
            </td>
        </tr>
        <?php
        $anousu = db_getsession("DB_anousu");

        if ($anousu > 2007) {
            ?>
            <tr>
                <td nowrap title="<?= @$Te60_concarpeculiar ?>"><?php
                    db_ancora(@$Le60_concarpeculiar, "js_pesquisae60_concarpeculiar(true);", $db_opcao);
                    ?></td>
                <td>
                    <?php
                    db_input("e60_concarpeculiar", 10, $Ie60_concarpeculiar, true, "text", $db_opcao, "onChange='js_pesquisae60_concarpeculiar(false);'");
                    db_input("c58_descr", 50, 0, true, "text", 3);
                    ?>
                </td>
            </tr>
            <?php
        } else {
            $e60_concarpeculiar = 0;
            db_input("e60_concarpeculiar", 10, 0, true, "hidden", 3, "");

        }
        if (isset($e60_numemp) && isset($e30_notaliquidacao) && $e30_notaliquidacao != '') {
            $rsNotaLiquidacao = $oDaoEmpenhoNl->sql_record(
                $oDaoEmpenhoNl->sql_query_file(null, "e68_numemp", "", "e68_numemp = {$e60_numemp}"));
            if ($oDaoEmpenhoNl->numrows == 0) {
                ?>
                <tr>
                    <td nowrap title="Nota de liquidação">
                        <b>Nota de liquidação:</b>
                    </td>
                    <td>
                        <?php
                        $aNota = array("s" => "Sim", "n" => "NÃO");
                        db_select("e68_numemp", $aNota, true, 1);
                        ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        <!--[Extensao OrdenadorDespesa] inclusao_ordenador-->

    




















<tr>
          <td><b>Acompanhamento da Execução Orçamentária</b></td>
          
          <td>
            <?php if($codins == 20 || $codins == 25 || $codins == 80 || $codins == 96 || $codins == 50 || $codins == 85) : ?>
            <select id="aeo" name="aeo" required>
            <?php else : ?>
              <select id="aeo" name="aeo">
              <option value="0" <?=($xaeo == 0) ? "selected" : ""?>>0000 - Não se aplica</option>
            <?php endif; ?>  
              <option value="1001" <?=($xaeo == 1001) ? "selected" : ""?>>1001 - Identificação das despesas com manutenção e desenvolvimento do ensino</option>
              <option value="1002" <?=($xaeo == 1002) ? "selected" : ""?>>1002 - Identificação das despesas com ações e serviços públicos de saúde</option>
              <option value="1070" <?=($xaeo == 1070) ? "selected" : ""?>>1070 - Identificação do percentual aplicado no pagamento da remuneração dos profissionais da educação básica em efetivo exercício</option>
              <option value="1111" <?=($xaeo == 1111) ? "selected" : ""?>>1111 - Benefícios previdenciários - Poder Executivo - Fundo em Capitalização (Plano Previdenciário)</option>
              <option value="1121" <?=($xaeo == 1121) ? "selected" : ""?>>1121 - Benefícios previdenciários - Poder Legislativo - Fundo em Capitalização (Plano Previdenciário)</option>
              <option value="2111" <?=($xaeo == 2111) ? "selected" : ""?>>2111 - Benefícios previdenciários - Poder Executivo - Fundo em Repartição (Plano Financeiro)</option>
              <option value="2121" <?=($xaeo == 2121) ? "selected" : ""?>>2121 - Benefícios previdenciários - Poder Legislativo - Fundo em Repartição (Plano Financeiro)</option>
            </select>
          </td>
        </tr>

        <tr>
          <td><b>Justificativa da Ausência de Instrumento Prévio</b></td>
          <td>
            <select id="jaip" name="jaip">
              <option value="0" <?=($xjaip == 0) ? "selected" : ""?>>0 - Nenhum</option>
              <option value="1" <?=($xjaip == 1) ? "selected" : ""?>>1 - Concessionária de serviços públicos</option>
              <option value="2" <?=($xjaip == 2) ? "selected" : ""?>>2 - Tarifas e obrigações bancárias</option>
              <option value="3" <?=($xjaip == 3) ? "selected" : ""?>>3 - Taxas, custas, tributos ou emolumentos</option>
              <option value="4" <?=($xjaip == 4) ? "selected" : ""?>>4 - Adiantamentos</option>
              <option value="5" <?=($xjaip == 5) ? "selected" : ""?>>5 - Aluguel social pago diretamente ao beneficiário</option>
              <option value="6" <?=($xjaip == 6) ? "selected" : ""?>>6 - Pagamento a Estagiários</option>
              <option value="7" <?=($xjaip == 7) ? "selected" : ""?>>7 - Jetons Remuneração por Participação em reuniões ou sessões de conselho</option>
              <option value="8" <?=($xjaip == 8) ? "selected" : ""?>>8 - Honorários advocatícios e ônus de sucumbência</option>
              <option value="9" <?=($xjaip == 9) ? "selected" : ""?>>9 - Convênios celebrados entre órgãos públicos.</option>
              <option value="10" <?=($xjaip == 10) ? "selected" : ""?>>10 - Contratação de pessoal por prazo determinado (CPD) não empenhado em elemento próprio.</option>
              <option value="11" <?=($xjaip == 11) ? "selected" : ""?>>11 - Premiações Culturais, Artísticas, Científicas e Desportivas</option>
              <option value="12" <?=($xjaip == 12) ? "selected" : ""?>>12 - Programas de transferência de rendas ou auxílios pagos diretamente ao beneficiário.</option>
              <option value="13" <?=($xjaip == 13) ? "selected" : ""?>>13 - Despesa com pessoal não empenhada em elemento próprio.</option>
              <option value="14" <?=($xjaip == 14) ? "selected" : ""?>>14 - Desapropiação (casos não fundamentados como inexigibilidade).</option>
              <option value="15" <?=($xjaip == 15) ? "selected" : ""?>>15 - Programas de assistência financeira pagos diretamente às unidades escolares.</option>
              <option value="16" <?=($xjaip == 16) ? "selected" : ""?>>16 - Termo de compromisso com ônus para o ente (Caso não fundamentado como inexigibilidade).</option>
            </select>
          </td>
        </tr>

        <tr>
          <td><b>Justificativa da Ausência de Ato Jurídico</b></td>
          <td>
            <select id="jaaj" name="jaaj">
            <option value="0" <?=($xjaaj == 0) ? "selected" : ""?>>0 - Nenhum</option>
            <option value="1" <?=($xjaaj == 1) ? "selected" : ""?>>1 - Dispensa de Licitação em razão de valor (art. 95, I, da ei nº 14.133/2021).</option>
            <option value="2" <?=($xjaaj == 2) ? "selected" : ""?>>2 - Compra com entegra imediata e integral, não resultando em obrigações futuras</option>
            <option value="3" <?=($xjaaj == 3) ? "selected" : ""?>>3 - Concessionária de serviços públicos (água, energia elétrica, etc.)</option>
            <option value="4" <?=($xjaaj == 4) ? "selected" : ""?>>4 - Tarifas e obrigações bancárias</option>
            <option value="5" <?=($xjaaj == 5) ? "selected" : ""?>>5 - Taxas, custas, tributos ou emolumentos devidos a outros entes da federação</option>
            <option value="6" <?=($xjaaj == 6) ? "selected" : ""?>>6 - Adiantamentos</option>
            <option value="7" <?=($xjaaj == 7) ? "selected" : ""?>> 7 - Contrato não assinado</option>
            <option value="8" <?=($xjaaj == 8) ? "selected" : ""?>> 8 - Pequenas compras ou prestação de serviços de pronto pagamento, assim entendidos aqueles de valor não superior a R$ 10.000,00 (ar t. 95, § 2º Lei 14.133/2021)</option>
            <option value="9" <?=($xjaaj == 9) ? "selected" : ""?>> 9 - Aluguel social pago diretamente ao beneficiário.</option>
            <option value="10" <?=($xjaaj == 10) ? "selected" : ""?>> 10 - Pagamento a Estagiários</option>
            <option value="11" <?=($xjaaj == 11) ? "selected" : ""?>> 11 - Jetons   Remuneração por participação em reuniões ou sessões de conselhos.</option>
            <option value="12" <?=($xjaaj == 12) ? "selected" : ""?>> 12 - Honorários advocatícios e ônus de sucumbência</option>
            <option value="13" <?=($xjaaj == 13 ) ? "selected" : ""?>>13 - Contratação de pessoal por prazo determinado (CPD) não empenhado em elemento próprio.
            <option value="14" <?=($xjaaj == 14 ) ? "selected" : ""?>>14 - Premiações Culturais, Artísticas, Científicas e Desportivas
            <option value="15" <?=($xjaaj == 15 ) ? "selected" : ""?>>15 - Ajuste de contas não amparado por termo contratual.
            <option value="16" <?=($xjaaj == 16 ) ? "selected" : ""?>>16 - Programas de transferência de rendas ou auxílios pagos diretamente ao beneficiário.
            <option value="17" <?=($xjaaj == 17 ) ? "selected" : ""?>>17 - Despesa com pessoal não empenhada em elemento próprio.

            <option value="18" <?=($xjaaj == 18 ) ? "selected" : ""?>>18 - Acordo judicial ou extrajudicial (sem lastro contratual ou congênere).
            <option value="19" <?=($xjaaj == 19 ) ? "selected" : ""?>>19 - Programas de assistência financeira pagos diretamente às unidades escolares.

          </td>
        </tr>

        <tr>
          <td class="bold">
              <label for="CGM">
                  <?php db_ancora('CGM do Ordenador:', 'buscarCGM(true)', 1); ?>
              </label>
          </td>
          <td>
              <?php
              db_input('c139_cgm', 10, 1, true, 'text', 1, 'onChange="buscarCGM(false)"');
              ?>
          
              <?php
              db_input('cgm_descricao', 40, 0, true, 'text', 3);
              ?>

              <?php if($xcgmordenador) : ?>
                <script>document.getElementById("c139_cgm").value = <?=$xcgmordenador?></script>
              <?php endif; ?>
          </td>
      </tr>
      
      <tr>
        
        
        
      </tr>      

      
        <tr id="tipr">
        <td><b>Tipo de Instrumento Prévio:</b></td>
        <td>
          <select id="tipro" name="tipro" onchange="trocanome2(this)">
            <option value="0" <?=($xtipro == 0) ? "selected" : ""?>>Selecione</option>
            <option value="1" <?=($xtipro == 1) ? "selected" : ""?>>1 - Licitação</option>
            <option value="2" <?=($xtipro == 2) ? "selected" : ""?>>2 - Dispensa</option>
            <option value="3" <?=($xtipro == 3) ? "selected" : ""?>>3 - Inexigibilidade</option>
            <option value="4" <?=($xtipro == 4) ? "selected" : ""?>>4 - Ato de Adesão a Registro de Preço</option>            
            <?php /* ?><option value="99" <?=($xtipro == 99) ? "selected" : ""?>>99 - Inexistente / Justificativa</option><?php */ ?>
          </select>
         
         <b><span id="nome2">Nº Instrumento Prévio:</span></b>
          <input type="texto" name="noinpr" id="noinpr" value="<?=($xnoinpr) ? $xnoinpr : ''?>">
        </td>
      </tr>

      <tr>
        <td><b>Unidade Gestora do Instrumento Prévio</b></td>
        <?php 
          $xano = db_getsession("DB_anousu");
          $xinst = db_getsession("DB_instit");          
          $unidades = voltaOrgaos($xinst, $xano);
          $unidades2 = buscaUGs();
         ?>                  
         <td>
           <select id="ugip" name="ugip">
             <?php foreach($unidades2 as $unidade) : ?>
                <option value="<?=$unidade['c179_codigo']?>" <?=($xugip == $unidade['c179_codigo']) ? 'selected' : ''?>><?=$unidade['c179_codigo'] . " - " . $unidade['nomeinst']?></option>
              <?php endforeach; ?>
           </select>
         </td>
      </tr>

      <tr id="taju">
        <td><b>Tipo do Ato Jurídico:</b></td>
        <td>
          <select id="tajuo" name="tajuo" onchange="trocanome(this)">
            <option value="0" <?=($xtajuo == 0) ? "selected" : ""?>>Selecione</option>
            <option value="1" <?=($xtajuo == 1) ? "selected" : ""?>>1 - Contrato</option>
            <option value="2" <?=($xtajuo == 2) ? "selected" : ""?>>2 - Convênio</option>
            <option value="3" <?=($xtajuo == 3) ? "selected" : ""?>>3 - Reconhecimento de Dívida</option>
            <option value="4" <?=($xtajuo == 4) ? "selected" : ""?>>4 - Termo de Parceria</option>
            <option value="5" <?=($xtajuo == 5) ? "selected" : ""?>>5 - Desapropriação</option>
            <option value="6" <?=($xtajuo == 6) ? "selected" : ""?>>6 - Concessões</option>
            <option value="7" <?=($xtajuo == 7) ? "selected" : ""?>>7 - Contrato de Programa</option>
            <option value="8" <?=($xtajuo == 8) ? "selected" : ""?>>8 - Contrato de Gestão</option>
            <option value="9" <?=($xtajuo == 9) ? "selected" : ""?>>9 - Termo de Colaboração / Fomento</option>
            <?php /* ?><option value="99" <?=($xtajuo == 99) ? "selected" : ""?>>99 - Inexistência de Ato jurídico / Justificativa</option><?php */ ?>
          </select>

          <b><span id="nome1">Nº do Ato Jurídico:</span></b>
          <input type="texto" name="noatoju" id="noatoju" value="<?=($xnoatoju) ? $xnoatoju : ''?>">
        </td>
      </tr>



      <tr>
        <td><b>Unidade Gestora do Ato Jurídico</b></td>
        <?php 
          $xano = db_getsession("DB_anousu");
          $xinst = db_getsession("DB_instit");          
          $unidades = voltaOrgaos($xinst, $xano);
          $unidades2 = buscaUGs();
         ?>                  
         <td>
           <select id="ugaj" name="ugaj">
             <?php foreach($unidades2 as $unidade) : ?>
                <option value="<?=$unidade['c179_codigo']?>" <?=($xugaj == $unidade['c179_codigo']) ? 'selected' : ''?>><?=$unidade['c179_codigo'] . " - " . $unidade['nomeinst']?></option>
              <?php endforeach; ?>
           </select>
         </td>         
      </tr>

      



















    </table>

    <input name="alterar" type="submit" id="db_opcao" value="Alterar" <?= ($db_botao == false ? "disabled" : "") ?>
           onclick='return js_valida()';>

    <input type="button" id="btnLancarCotasMensais" value="Manutenção de Cotas Mensais"
           onclick="manutencaoCotasMensais()"/>

    <input name="pesquisar" type="button" id="pesquisar" value="Pesquisar empenhos" onclick="js_pesquisa();">
</form>

<script>
    const UF = '<?=getEstadoInstituicao();?>'
    var isPB = UF === 'PB';
    var isRJ = UF === 'RJ';
    var isJustificavel = '<?php echo !in_array($e56_codele,$dispensaElementosAtoJuridico) ? 'true': 'false';?>';
    
    let linhaCNPJ = document.getElementById('linhaCNPJ');
    let inputLicitacaoCompartilhada = document.getElementById('licitacao_compartilhada');
    let inputCnpjGerenciador = document.getElementById('cnpjGerenciador');
    new DBInputCNPJ(inputCnpjGerenciador);

    inputLicitacaoCompartilhada.addEventListener('change', () => {
        linhaCNPJ.style.display = 'none';
        if (inputLicitacaoCompartilhada.value === 'S') {
            linhaCNPJ.style.display = 'table-row';
        }
    });
    inputLicitacaoCompartilhada.dispatchEvent(new Event('change'));

    function manutencaoCotasMensais() {

        oViewCotasMensais = new ViewCotasMensais('oViewCotasMensais', $F('e60_numemp'));
        oViewCotasMensais.setReadOnly(false);
        oViewCotasMensais.abrirJanela();
    }


    function js_pesquisae60_concarpeculiar(mostra) {
        if (mostra == true) {
            js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_concarpeculiar', 'func_concarpeculiar.php?funcao_js=parent.js_mostraconcarpeculiar1|c58_sequencial|c58_descr', 'Pesquisa', true);
        } else {
            if (document.form1.e60_concarpeculiar.value != '') {
                js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_concarpeculiar', 'func_concarpeculiar.php?pesquisa_chave=' + document.form1.e60_concarpeculiar.value + '&funcao_js=parent.js_mostraconcarpeculiar', 'Pesquisa', false);
            } else {
                document.form1.c58_descr.value = '';
            }
        }
    }

    function js_mostraconcarpeculiar(chave, erro) {
        document.form1.c58_descr.value = chave;
        if (erro == true) {
            document.form1.e60_concarpeculiar.focus();
            document.form1.e60_concarpeculiar.value = '';
        }
    }

    function js_mostraconcarpeculiar1(chave1, chave2) {
        document.form1.e60_concarpeculiar.value = chave1;
        document.form1.c58_descr.value = chave2;
        db_iframe_concarpeculiar.hide();
    }

    function js_pesquisa() {
        js_OpenJanelaIframe('', 'db_iframe_empempenho', 'func_empempenho.php?funcao_js=parent.js_preenchepesquisa|e60_numemp', 'Pesquisa', true);
    }

    function js_preenchepesquisa(chave) {
        db_iframe_empempenho.hide();
        <?php
        echo " location.href = '" . basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"]) . "?chavepesquisa='+chave";
        ?>
    }


    /**
     * Ajustes no layout
     */
    $("e60_codtipo").style.width = "15%";
    $("e63_codhist").style.width = "15%";
    $("e60_codtipodescr").style.width = "84%";
    $("e63_codhistdescr").style.width = "84%";
    $("e44_tipo").style.width = "100%";
    if ($("e56_codele")) {
        $("e56_codele").style.width = "100%";
    }
    $("e60_destin").style.width = "100%";
    $("e60_resumo").style.width = "100%";


    function js_verificaFinalidadeEmpenho() {

        js_divCarregando("Aguarde, verificando recurso da dotação...", "msgBox");
        var oParam = new Object();
        oParam.exec = "getFinalidadePagamentoFundebEmpenho";
        oParam.iSequencialEmpenho = $F('e60_numemp');

        new Ajax.Request('emp4_empenhofinanceiro004.RPC.php',
            {
                method: 'post',
                parameters: 'json=' + Object.toJSON(oParam),
                onComplete: function (oAjax) {

                    js_removeObj("msgBox");
                    var oRetorno = JSON.parse(oAjax.responseText);

                    $('trFinalidadeFundeb').style.display = '';
                    $("e151_codigo").style.width = "15%";
                    $("e151_codigodescr").style.width = "84%";

                    if (oRetorno.oFinalidadePagamentoFundeb) {

                        $('e151_codigo').value = oRetorno.oFinalidadePagamentoFundeb.e151_codigo;
                        js_ProcCod_e151_codigo('e151_codigo', 'e151_codigodescr');
                    }
                }
            });
    }

    js_verificaFinalidadeEmpenho();


    function js_valida() {        
        var tipoinstprevio, noinstprevio;
        var tipoatoj, noatoj;
        var justinstprev = document.getElementById("jaip").value;
        var justatojur = document.getElementById("jaaj").value;
        var folhadiaria = document.getElementById("folhadiaria").checked;
                

        tipoinstprevio = document.getElementById("tipro").value;
        noinstprevio = document.getElementById("noinpr").value;
        tipoatoj = document.getElementById("tajuo").value;
        noatoj = document.getElementById("noatoju").value;

        if(folhadiaria == false){
            if(justinstprev == 0){
                if((!tipoinstprevio || tipoinstprevio == 0)){
                    alert("Tipo de Instumento Prévio é obrigatório.");
                    return false;
                }
                if((!noinstprevio || noinstprevio == 0)){
                    alert("Número do instrumento prévio é obrigatório.");
                    return false;
                }                
            }else{
                if((tipoinstprevio != 0)){
                    alert("Tipo de Instumento não deve ser preenchido quando uma justificativa é escolhida.");
                    return false;
                }
                if((noinstprevio != 0)){
                    alert("Tipo de Instumento não deve ser preenchido quando uma justificativa é escolhida.");
                    return false;
                }
            }

            if(justatojur == 0){
                if((!tipoatoj || tipoatoj == 0)){
                    alert("Tipo de Ato Jurídico é obrigatório.");
                    return false;
                }
                if((!noatoj || noatoj == 0)){
                    alert("Número do Ato Jurídico é obrigatório.");
                    return false;
                }
            }else{
                if((tipoatoj != 0)){
                    alert("Tipo de Ato Jurídico não deve ser preenchido quando uma justificativa é escolhida.");
                    return false;
                }
                if((noatoj != 0)){
                    alert("Número do Ato Jurídico não deve ser preenchido quando uma justificativa é escolhida.");
                    return false;
                }
            }
        }
        

        if (!validaNumeroLicitacao()) {
            return false
        }

        if (!validaTipoServico()) {
            return false;
        }

        let geoObra = document.getElementById('geo_obra');
        if (isPB && geoObra != null && geoObra.value === '') {
            alert(`Para empenhos de Obras, você deve informar o código do GEO Obras.`);
            return false
        }

        let cnpj = inputCnpjGerenciador.getValue().replace(/[^0-9]/gi, "");
        if (inputLicitacaoCompartilhada.value === 'S' && empty(cnpj)) {
            alert(`Quando Licitação Compartilhada for "SIM", você deve informar o CNPJ do órgão gerenciador`);
            return false;
        }
        
        return true;
    }

    function validaNumeroLicitacao() {
        let numeroLicitacao = document.getElementById('numeroLicitacao');
        let anoLicitacao = document.getElementById('anoLicitacao');

        if ((!numeroLicitacao.hasAttribute('readonly') && numeroLicitacao.value == '') ||
            (!anoLicitacao.hasAttribute('readonly') && anoLicitacao.value == '')) {
            alert('Você deve informar o número e ano da licitação.');
            return false;
        }

        if (!anoLicitacao.hasAttribute('readonly') && anoLicitacao.value.length < 4) {
            alert('O ano deve possuir 4 digitos.');
            return false;
        }

        return true;
    }

    /* =============================
    * Validacoes dos indicativo de
    * aquisicão de producao rural
    * (EFD-REINF)
    * =============================
    */
    const indAqProd  = document.querySelector("#indAqProd");
    const trAqProd   = document.querySelector('#trAqProd');

    // entrypoint
    iniciarCamposReinf();

    /**
    * Ajax requests
    */
    async function iniciarCamposReinf() {
        let produtorrural = await checkProdutorrual();

        if (produtorrural && produtorrural == 't') {
            getTipoaAquisicaoProducaoRuralLabels();
        }
    }

    async function checkProdutorrual() {
        const RPC = 'emp4_tipoaquisicaoproducaorural.RPC.php';
        let cgm = $F('e60_numcgm');
        let params = JSON.stringify({exec: 'produtorrural', cgm: cgm});
        let formData = new FormData;
        let response = '';

        try {
            formData.append('json', params);
            response = await fetch(RPC, {method: 'post', body: formData});
            data = await response.json();

            if (!data.erro) {
               return data.produtorrural;
            }
            return false;
        } catch (error) {
            console.error(error);
        }
    }

    async function getTipoaAquisicaoProducaoRuralLabels() {
        const RPC    = 'emp4_tipoaquisicaoproducaorural.RPC.php';
        let cgm      = $F('e60_numcgm');
        let params   = JSON.stringify({exec: 'getLabels', cgm: cgm});
        let formData = new FormData();
        let response = '';
        let labels   = [];
        let option   = '';

        js_divCarregando('Buscando dados do indicativo de aquisição...', 'aqProdLoad');

        try {
            formData.append('json', params);
            response = await fetch(RPC, {method: 'post', body: formData});
            data = await response.json();

            if (!data.erro && data.labels) {
                labels = data.labels;
                trAqProd.style.display = 'table-row';

                // option não se aplica
                option = document.createElement('option');
                option.value = '';
                option.innerHTML = '0 - NÃO SE APLICA';
                indAqProd.appendChild(option);

                labels.forEach(item => {
                    option = document.createElement('option');
                    option.value = item.value;
                    option.innerHTML = item.value + ' - ' + item.name;
                    indAqProd.appendChild(option);
                });
            }
            await getTipoaAquisicaoProducaoRural();
        } catch (error) {
            console.error(error);
        }
        js_removeObj('aqProdLoad');
    }

    async function getTipoaAquisicaoProducaoRural() {
        const RPC = 'emp4_tipoaquisicaoproducaorural.RPC.php';
        let e60_numemp = $F('e60_numemp');
        let params     = JSON.stringify({exec: 'getByEmpenho', numemp: e60_numemp});
        let formData   = new FormData();
        let response   = '';

        try {
            formData.append('json', params);
            response = await fetch(RPC, {method: 'post', body: formData});
            data = await response.json();

            if (!data.erro && data.tipoaquisicaoproducaorural) {
                indAqProd.value = data.tipoaquisicaoproducaorural.e159_tipo;
            }
        } catch (error) {
            console.error(error);
        }
    }

    function pesquisarTipoLicitacao() {

        if(isRJ && isJustificavel == 'true') {
            $('justificativainstrumentoprevio_bloco').style.display = 'none';
            let tiposCompraJustificaveis = <?php echo json_encode($tiposCompraJustificaveis) ?>;
            if(tiposCompraJustificaveis.includes($F('e60_codcom'))){
              $('justificativainstrumentoprevio_bloco').style.display = 'table-row';              
            }
        }

        $('numeroLicitacao').className = ''
        $('anoLicitacao').className = '';
        $('numeroLicitacao').removeAttribute('readonly');
        $('anoLicitacao').removeAttribute('readonly');

        var oTipoLicitacao = $('e60_tipol');

        new AjaxRequest(
            'lic4_geraAutorizacoes.RPC.php',
            { exec : 'getTipoLicitacao', iTipoCompra : $F('e60_codcom') },
            function (oRetorno, lErro) {

                if (!oRetorno.obrigaLicitacao) {
                    $('numeroLicitacao').value = '';
                    $('anoLicitacao').value = '';
                    $('numeroLicitacao').className += 'readonly'
                    $('anoLicitacao').className += 'readonly';
                    $('numeroLicitacao').setAttribute('readonly', 'readonly');
                    $('anoLicitacao').setAttribute('readonly', 'readonly');
                }

                if (oRetorno.aTiposLicitacao.length == 0) {
                    return;
                }

                oTipoLicitacao.options.length = 0;
                oRetorno.aTiposLicitacao.each(
                    function (oTipo) {
                        oTipoLicitacao.add(new Option(`${oTipo.l03_tipo} - ${oTipo.l03_descr}`, oTipo.l03_tipo));
                        oTipoLicitacao.value = $F('tipoAtribuidoAnterior');
                    }
                );
            }
        ).setMessage('Aguarde, carregando tipo de licitação...').execute();
    }
    pesquisarTipoLicitacao();

    function js_abreFechaBlocoJustificativa(){

        $('justificativaatojuridico_bloco').style.display = 'none';        
        if($F('tipoatojuridico') == 99){
          $('justificativaatojuridico_bloco').style.display = 'table-row';        
        } 
    }

    iniciarJustificativasSigfis();
    function iniciarJustificativasSigfis(){
        if(isRJ && isJustificavel == 'true'){
            
            let tipoAtoJuridico = '<?=!empty($tipoatojuridico) ? $tipoatojuridico : ''?>';
            let tipoCompra = '<?=!empty($e60_codcom) ? $e60_codcom : ''?>';
            let tiposCompraJustificaveis = <?php echo json_encode($tiposCompraJustificaveis) ?>;
            
            if(tipoAtoJuridico == '99'){
                $('justificativaatojuridico_bloco').style.display = 'table-row';
            }
            if(tiposCompraJustificaveis.includes(tipoCompra)){
                $('justificativainstrumentoprevio_bloco').style.display = 'table-row';
            }        
        }
    }    


function buscarCGM(lMostrar) {
  $('cgm_descricao').value = '';
  if ( lMostrar ) {
    $('c139_cgm').value = '';
    js_OpenJanelaIframe("",'func_nome','func_cgm2.php?condition="somenteAtivos"&funcao_js=parent.js_preencheCGM|z01_numcgm|z01_nome','Pesquisa',true);
  } else {
    js_divCarregando("Pesquisando ..." , 'msgBox');
    js_OpenJanelaIframe("",'func_nome','func_cgm2.php?condition="somenteAtivos"&pesquisa_chave='+document.form1.c139_cgm.value+'&funcao_js=parent.js_preencheCGM1','Pesquisa',false);
  }
}

function js_preencheCGM( iCodigo, sDescricao ) {          
  document.form1.c139_cgm.value           = iCodigo;
  document.form1.cgm_descricao.value = sDescricao;
  func_nome.hide();
}

function js_preencheCGM1( lErro, sDescricao ) {          
  js_removeObj('msgBox');
  if ( !lErro ) {
    document.form1.cgm_descricao.value = sDescricao;
  } else {
    document.form1.c139_cgm.value           = "";
    document.form1.cgm_descricao.value = sDescricao;
  }
}



function trocanome(self) {
    var valor = self.value;
    var nomenum = document.getElementById("nome1");
    var campojustificativa = document.getElementById("jaaj").value;

    if(valor == 1){
      nomenum.innerHTML = "Nº do Contrato";
    }else if(valor == 2){
      nomenum.innerHTML = "Nº do Convênio";
    }else if(valor == 3){
      nomenum.innerHTML = "Nº do Reconhecimento de Dívida";
    }else if(valor == 4){
      nomenum.innerHTML = "Nº do Termo de Parceria";
    }else if(valor == 5){
      nomenum.innerHTML = "Nº do Desapropriação";
    }else if(valor == 6){
      nomenum.innerHTML = "Nº do Concessões";
    }else if(valor == 7){
      nomenum.innerHTML = "Nº do Contrato de Programa";
    }else if(valor == 8){
      nomenum.innerHTML = "Nº do Contrato de Gestão";
    }else if(valor == 9){
      nomenum.innerHTML = "Nº do Termo de Colaboração / Fomento";
    }else{
      nomenum.innerHTML = "Nº do Ato Jurídico";
    }

    /*if(valor == 99 && campojustificativa == 0){
      alert("Campo da Justificativa da Ausência da Ausência de Ato Jurídico não pode ser 0 quando o Tipo do Ato Jurídico for 99.");
      var cs = document.getElementById("tajuo")[0];
      cs.selected = true;
      return false;
    }*/
}

function trocanome2(self) {
    var valor = self.value;
    var campojustificativa = document.getElementById("jaip").value;
    
    var nome2 = document.getElementById("nome2");

    if(valor == 1){
      nome2.innerHTML = "Nº da Licitação";
    }else if(valor == 2){
      nome2.innerHTML = "Nº da Dispensa";
    }else if(valor == 3){
      nome2.innerHTML = "Nº da Inexigibilidade";
    }else if(valor == 4){
      nome2.innerHTML = "Nº do Ato de Adesão";
    }else{
      nome2.innerHTML = "Nº Instrumento Prévio";
    }

    /*if(valor == 99 && campojustificativa == 0){
      alert("Campo da Justificativa da Ausência de Instrumento Prévio não pode ser 0 quando o Tipo do Instrumento Prévio for 99.");
      var cs = document.getElementById("tipro")[0];
      cs.selected = true;
      return false;
    }*/
}
document.getElementById("e60_codemp").style.marginRight = "130px";



</script>
