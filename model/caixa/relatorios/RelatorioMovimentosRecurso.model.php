<?php

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

class RelatorioMovimentosRecurso {

  /**
   * @var PDFDocument
   */
  public $oPdf;

  /**
   * @var DBDate Data inicial do período para emissão do relatório.
   */
  private $oDataInicial;

  /**
   * @var DBDate Data final do período para emissão do relatório
   */
  private $oDataFinal;

  /**
   *
   * @var integer
   */
  private $iOrgao;

  /**
   * @var integer Largura disponível do relatório.
   */
  public $iLargura;

  /**
   * @var integer Altura da linha do relatório.
   */
  public $iAltura;

  /**
   *
   * @var integer
   */
  private $iRecurso;

  /**
   *
   * @var callback
   */
  private $fCabecalho;

  /**
   *
   * @var array
   */
  private $aDados;

  private $nTotalOP;

  private $nTotalRetencao;

  private $nTotalLiquido;

  private $iLinha;

  private $iInstituicao;

  private $nTotalGeralOP;

  private $nTotalGeralRetencao;

  private $nTotalGeralLiquido;

  /**
   * Construtor
   *
   * @param Instituicao    $oInstituicao
   * @param DBDepartamento $oDepartamento
   */
  public function __construct() {

    $this->oPdf     = new PDFDocument(PDFDocument::PRINT_LANDSCAPE);
    $this->iLargura = $this->oPdf->getAvailWidth() - $this->oPdf->GetRightMargin();
    $this->iAltura  = 4;
  }

  /**
   * @param DBDate $oDataInicial
   */
  public function setDataInicial($oDataInicial) {

    $this->oDataInicial = $oDataInicial;
  }

  /**
   * @param DBDate $oDataFinal
   */
  public function setDataFinal($oDataFinal) {

    $this->oDataFinal = $oDataFinal;
  }

  /**
   * @param integer $iOrgao
   */
  public function setOrgao($iOrgao) {

    $this->iOrgao = $iOrgao;
  }

  public function getDados() {


$iOrgao = $this->iOrgao;


    $sSql = "
select * from (
SELECT empagemov.e81_codmov,
  (SELECT e25_empagetipotransmissao FROM empagemovtipotransmissao WHERE e25_empagemov = e81_codmov LIMIT 1) AS e25_empagetipotransmissao,
  e80_codage,
  CASE WHEN a.z01_numcgm IS NOT NULL THEN a.z01_numcgm  ELSE cgm.z01_numcgm END AS z01_numcgm,
  CASE WHEN trim(a.z01_nome) IS NOT NULL THEN a.z01_nome ELSE cgm.z01_nome END AS z01_nome,
  CASE WHEN trim(a.z01_cgccpf) IS NOT NULL THEN a.z01_cgccpf ELSE cgm.z01_cgccpf END AS z01_cgccpf,
  e50_DATA,
  e80_DATA,
  e60_anousu,
  e60_numemp,
  e60_codemp,
  o15_codigo,
  e86_DATA,
  e50_codord,
 e53_valor - fc_valorretencaomov(e81_codmov,FALSE),
  e53_valor,
  o58_codigo,
  o58_orgao,
  o40_descr,
  o58_unidade,
  o58_localizadorgastos,
  e53_vlranu,
  k12_data,
  e91_cheque,
  e91_codmov,
  e71_codnota,
  e79_concarpeculiar,
  e60_concarpeculiar,
   e03_numeroprocesso,
  e69_dtvencimento,
o15_descr,
(CASE WHEN e85_codmov IS NULL THEN (SELECT e28_empagetipo FROM empageformacgm WHERE e28_numcgm = e60_numcgm) ELSE e85_codtipo END) AS e85_codtipo, e97_codmov, e90_cancelado,
 CASE WHEN e90_cancelado IS TRUE THEN NULL ELSE e90_codmov END AS e90_codmov, e98_contabanco,
(CASE WHEN e97_codforma IS NULL THEN
(SELECT e97_codforma FROM empage
INNER JOIN empagemov ON empagemov.e81_codage = empage.e80_codage
INNER JOIN empord ON empord.e82_codmov = empagemov.e81_codmov
INNER JOIN pagordem ON pagordem.e50_codord = empord.e82_codord
INNER JOIN pagordemele ON pagordemele.e53_codord = pagordem.e50_codord
INNER JOIN empempenho ON empempenho.e60_numemp = pagordem.e50_numemp
LEFT JOIN empagemovforma ON empagemovforma.e97_codmov = empagemov.e81_codmov WHERE empempenho.e60_instit =  fc_getsession ('DB_instit')::integer ORDER BY e81_codmov DESC LIMIT 1) ELSE e97_codforma END) AS e97_codforma , e42_dtpagamento, e53_vlrpag, round(e81_valor + (SELECT coalesce(sum(e34_valordesconto), 0)
FROM pagordemdesconto WHERE e34_codord = e50_codord), 2) AS e81_valor, e86_codmov, e43_sequencial, e42_sequencial,fc_validaretencoesmesanterior(e81_codmov,NULL) AS validaretencao,
fc_valorretencaomov(e81_codmov,FALSE) AS valorretencao, coalesce(e43_valor,0) AS e43_valor,
(e42_dtpagamento-current_date) as dias,
CASE WHEN(e42_dtpagamento-current_date) <= 0 THEN 'vencido a '||abs(e42_dtpagamento-current_date)||' dias' ELSE 'a vencer em '||date_part('day',e42_dtpagamento)||'/'||date_part('month',e42_dtpagamento)||'/'||date_part('year',e42_dtpagamento) END as situacao
FROM empage
INNER JOIN empagemov ON empagemov.e81_codage = empage.e80_codage
INNER JOIN empord ON empord.e82_codmov = empagemov.e81_codmov
INNER JOIN pagordem ON pagordem.e50_codord = empord.e82_codord
INNER JOIN pagordemele ON pagordemele.e53_codord = pagordem.e50_codord
INNER JOIN empempenho ON empempenho.e60_numemp = pagordem.e50_numemp
INNER JOIN cgm ON cgm.z01_numcgm = empempenho.e60_numcgm
INNER JOIN db_config ON db_config.codigo = empempenho.e60_instit
INNER JOIN orcdotacao ON orcdotacao.o58_anousu = empempenho.e60_anousu
AND orcdotacao.o58_coddot = empempenho.e60_coddot
inner join orcorgao on o58_anousu = o40_anousu and o58_orgao = o40_orgao
INNER JOIN orctiporec ON orctiporec.o15_codigo = orcdotacao.o58_codigo
INNER JOIN emptipo ON emptipo.e41_codtipo = empempenho.e60_codtipo
LEFT JOIN empageconcarpeculiar ON empageconcarpeculiar.e79_empagemov = empagemov.e81_codmov
LEFT JOIN corempagemov ON corempagemov.k12_codmov = empagemov.e81_codmov
LEFT JOIN empagemovconta ON empagemov.e81_codmov = e98_codmov
LEFT JOIN empageconf ON empageconf.e86_codmov = empord.e82_codmov
LEFT JOIN empageconfche ON empageconf.e86_codmov = e91_codmov
AND e91_ativo IS TRUE
LEFT JOIN pagordemconta ON e49_codord = e82_codord
LEFT JOIN empagemovforma ON e97_codmov = e81_codmov
LEFT JOIN empagepag ON e85_codmov = e81_codmov
LEFT JOIN empagetipo ON e85_codtipo = e83_codtipo
LEFT JOIN pagordemnota ON e71_codord = e50_codord
LEFT JOIN empnota ON e69_codnota = e71_codnota
LEFT JOIN cgm a ON a.z01_numcgm = e49_numcgm
LEFT JOIN empageconfgera ON empageconfgera.e90_codmov = empagemov.e81_codmov
AND empageconfgera.e90_cancelado IS FALSE
LEFT JOIN corgrupocorrente ON k105_data = corempagemov.k12_data
AND k105_autent = corempagemov.k12_autent
AND k105_id = corempagemov.k12_id
LEFT JOIN empagenotasordem ON e81_codmov = e43_empagemov
LEFT JOIN empageordem ON e43_ordempagamento = e42_sequencial
LEFT JOIN pagordemprocesso ON e50_codord = e03_pagordem
WHERE ((round(e53_valor,2)-round(e53_vlranu,2)-round(e53_vlrpag,2)) > 0
AND (round(e60_vlremp,2)-round(e60_vlranu,2)-round(e60_vlrpag,2)) > 0)
AND corempagemov.k12_codmov IS NULL
AND e81_cancelado IS NULL
AND e80_DATA <= current_date AND e60_instit = " . $this->iInstituicao . "
 AND e50_DATA BETWEEN '{$this->oDataInicial->getDate()}' AND '{$this->oDataFinal->getDate()}' ";

    if (!empty($iOrgao)) {
      //$sSql .= " AND o58_orgao = {$iOrgao} " ;
      $sSql .= " AND o58_orgao = 18 " ;
    }

    $sSql .= " ) as x     
      where coalesce(e97_codforma,0) <> 1 and coalesce(e97_codforma,0) <> 2 and coalesce(e97_codforma,0) <> 3 and coalesce(e97_codforma,0) <> 4
";

    $sSqlAlias = "
    select
      o40_descr                                as orgao,
      o15_codigo                               as recurso,
      o15_descr                                as recurso_descricao,
      e03_numeroprocesso                       as processo,
      e60_codemp || '/' || e60_anousu::varchar as empenho,
      z01_numcgm                               as cgm,
      z01_nome                                 as credor,
      e50_codord                               as numero_op,
      e81_valor                                as valor_op,
      valorretencao                            as retencao,
      situacao,
      (e53_valor - fc_valorretencaomov(e81_codmov,FALSE)) as valor_liquido
    from ($sSql) as dados
    order by o15_codigo asc, dias asc
    ";
  // echo "<pre>";
  // print_r($sSqlAlias);
  // echo "<pre>";
  // die();
    $rsDados = db_query($sSqlAlias);
    if (!$rsDados || pg_num_rows($rsDados) === 0) {
      throw new DBException('Não foram encontrados resultados.');
    }

    return db_utils::getCollectionByRecord($rsDados);
  }

  private function escreveRecurso(stdClass $oStdLinha) {

    $this->oPdf->ln($this->iAltura);
    $this->oPdf->SetFont('arial', 'b', 10);
    $this->oPdf->Cell($this->iLargura,
                      $this->iAltura,
                      $oStdLinha->recurso . ' - ' . $oStdLinha->recurso_descricao,
                      0,
                      1);
    $this->oPdf->SetFont('arial', '', 7);
    $this->oPdf->ln($this->iAltura);

    $fCabecalho = $this->fCabecalho;
    $fCabecalho();
  }

  private function escreverLinha(stdClass $oStdLinha, $iChave) {

    $this->iLinha = $iChave;
    if ($oStdLinha->recurso != $this->iRecurso && $this->iRecurso !== null) {
      $this->escreveRecurso($oStdLinha);
    }
    $this->iRecurso = $oStdLinha->recurso;

    $this->oPdf->Cell($this->iLargura * 0.17, $this->iAltura, substr($oStdLinha->orgao, 0, 28), 'TBR', 0, 'L');
    $this->oPdf->Cell($this->iLargura * 0.04, $this->iAltura, $oStdLinha->recurso, 'TBR', 0, 'C');
    $this->oPdf->Cell($this->iLargura * 0.06, $this->iAltura, $oStdLinha->processo, 'TBR', 0, 'R');
    $this->oPdf->Cell($this->iLargura * 0.07, $this->iAltura, $oStdLinha->empenho, 'TBR', 0, 'R');
    $this->oPdf->Cell($this->iLargura * 0.04, $this->iAltura, $oStdLinha->cgm, 'TBR', 0, 'R');
    $this->oPdf->Cell($this->iLargura * 0.20, $this->iAltura, substr($oStdLinha->credor, 0, 30), 'TBR', 0, 'L');
    $this->oPdf->Cell($this->iLargura * 0.06, $this->iAltura, $oStdLinha->numero_op, 'TBR', 0, 'R');
    $this->oPdf->Cell($this->iLargura * 0.08, $this->iAltura, db_formatar($oStdLinha->valor_op, 'f'), 'TBR', 0, 'R');
    $this->oPdf->Cell($this->iLargura * 0.08, $this->iAltura, db_formatar($oStdLinha->retencao, 'f'), 'TBR', 0, 'R');
    $this->oPdf->Cell($this->iLargura * 0.08,
                      $this->iAltura,
                      db_formatar($oStdLinha->valor_liquido, 'f'),
                      'TBR',
                      0,
                      'R');
    $this->oPdf->Cell($this->iLargura * 0.12, $this->iAltura, $oStdLinha->situacao, 'TB', 1, 'L');

    $this->nTotalOP       += $oStdLinha->valor_op;
    $this->nTotalRetencao += $oStdLinha->retencao;
    $this->nTotalLiquido  += $oStdLinha->valor_liquido;

    $this->nTotalGeralOP       += $oStdLinha->valor_op;
    $this->nTotalGeralRetencao += $oStdLinha->retencao;
    $this->nTotalGeralLiquido  += $oStdLinha->valor_liquido;

    /**
     * TOTALIZADORES
     */
    if ((!isset($this->aDados[$iChave + 1])
         || (isset($this->aDados[$iChave + 1]) && $this->aDados[$iChave + 1]->recurso != $oStdLinha->recurso))
        && $this->iRecurso !== null
    ) {

      $this->oPdf->Ln(4);
      $this->oPdf->setBold(true);
      $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Total OP: ', 0, 0);
      $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, db_formatar($this->nTotalOP, 'f'), 0, 1);

      $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Total Retenção: ', 0, 0);
      $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, db_formatar($this->nTotalRetencao, 'f'), 0, 1);

      $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Total Líquido: ', 0, 0);
      $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, db_formatar($this->nTotalLiquido, 'f'), 0, 1);
      $this->oPdf->setBold(false);

      $this->nTotalOP       = 0;
      $this->nTotalRetencao = 0;
      $this->nTotalLiquido  = 0;
    }

  }

  public function emitir() {

    $aDados       = $this->getDados();
    $this->aDados = $aDados;
    $this->configuraPdf();
    $that = $this;

    $this->fCabecalho = function () use ($that) {

      $that->oPdf->setBold(true);
      $that->oPdf->Cell($that->iLargura * 0.17, $that->iAltura, 'Órgão', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.04, $that->iAltura, 'Rec.', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.06, $that->iAltura, 'Nº Processo', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.07, $that->iAltura, 'Nº Empenho', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.04, $that->iAltura, 'CGM', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.20, $that->iAltura, 'Credor', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.06, $that->iAltura, 'Nº OP', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.08, $that->iAltura, 'Valor OP', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.08, $that->iAltura, 'Retenção', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.08, $that->iAltura, 'Val. Líquido', 'TBR', 0, 'C');
      $that->oPdf->Cell($that->iLargura * 0.12, $that->iAltura, 'Situação', 'TB', 1, 'C');
      $that->oPdf->setBold(false);
    };
    // $this->oPdf->setHeader($this->fCabecalho);

    $this->escreveRecurso($aDados[0]);
    foreach ($aDados as $iChave => $oStdLinha) {
      $this->escreverLinha($oStdLinha, $iChave);
    }

    /**
     * Totalizador geral
     */
    $this->oPdf->Ln(8);
    $this->oPdf->setBold(true);
    $this->oPdf->SetFontSize(8);
    $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Total Geral OP: ', 0, 0);
    $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, db_formatar($this->nTotalGeralOP, 'f'), 0, 1);
    $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Total Geral Retenção: ', 0, 0);
    $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, db_formatar($this->nTotalGeralRetencao, 'f'), 0, 1);
    $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Total Geral Líquido: ', 0, 0);
    $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, db_formatar($this->nTotalGeralLiquido, 'f'), 0, 1);
    $this->oPdf->setBold(false);

    $this->oPdf->showPDF("RelatorioMovimentosRecurso_" . time());
  }

  /**
   * Configurações e preparação do objeto oPdf.
   */
  private function configuraPdf() {

    $this->oPdf->addHeaderDescription("Movimentos por Recurso");
    $this->oPdf->addHeaderDescription("");
    $this->oPdf->addHeaderDescription("Data Inicial: " . $this->oDataInicial->getDate(DBDate::DATA_PTBR));
    $this->oPdf->addHeaderDescription("Data Final: " . $this->oDataFinal->getDate(DBDate::DATA_PTBR));
    $this->oPdf->addHeaderDescription("Orgão: " . $this->aDados[0]->orgao);

    $this->oPdf->Open();
    $this->oPdf->SetLeftMargin(10);
    $this->oPdf->AliasNbPages();
    $this->oPdf->SetFillcolor(235);
    $this->oPdf->SetFont('arial', '', 7);
    $this->oPdf->SetAutoPageBreak(true, 20);
    $this->oPdf->AddPage();
  }

  public function setInstituicao($iInstituicao) {

    $this->iInstituicao = $iInstituicao;
  }

}