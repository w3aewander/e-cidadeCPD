<?php

class cl_ordemauxiliarempenho extends DAOBasica {

  public function __construct() {

    parent::__construct('plugins.ordemauxiliarempenho');
  }

  public function sql_query_empenho ($iCodigo = null, $sCampos="*", $sOrder, $sWhere) {

    $sSql  = "select {$sCampos}";
    $sSql .= " from plugins.ordemauxiliarempenho";
    $sSql .= "      inner join empempenho on plugins.ordemauxiliarempenho.empempenho = e60_numemp";
    $sSql .= "      inner join cgm        on e60_numcgm = z01_numcgm";

    if (!empty($iCodigo)) {
      $aWhere[] = "sequencial = {$iCodigo}";
    }

    if (!empty($sWhere)) {
      $aWhere[] = $sWhere;
    }

    if (count($aWhere) > 0) {
      $sSql .= " where ".implode(" and ", $aWhere);
    }
    if (!empty($sOrder)) {
      $sSql .= " order by {$sOrder}";
    }
    return $sSql;
  }
}