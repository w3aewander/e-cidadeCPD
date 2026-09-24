<?php

class Release {

  private $iCodigo;

  private $iVersao;

  private $iRelease;

  private $iSubrelease;

  private $dData;

  private $sObservacao;

  private $sUrlAtualizacao;

  private $sProximaVersao;

  private $sMd5Checksum;

  public function __construct($iCodigo = null, $lAtual = false) {


    if ( !empty( $iCodigo) || $lAtual ) {

      $sOrdem = null;

      if ($lAtual) {
        $sOrdem = "db30_codver DESC";
        $iCodigo = null;
      }

      $oDaoVersao = db_utils::getDao('db_versao');
      $sSql       = $oDaoVersao->sql_query_file($iCodigo, "*", $sOrdem);
      $rsResult   = $oDaoVersao->sql_record($sSql);

      if(!$rsResult) {
        throw new DBException("Não foi possível buscar os dados.\n" . pg_last_error());
      }

      if($oDaoVersao->numrows == 0) {
        throw new BusinessException("Nenhum registro encontrado");
      }

      $oDadoVersao = db_utils::fieldsMemory($rsResult, 0);

      $this->iCodigo = $oDadoVersao->db30_codver;
      $this->iVersao = 2;
      $this->iRelease = $oDadoVersao->db30_codversao;
      $this->iSubrelease = $oDadoVersao->db30_codrelease;
      $this->dData = new DBDate($oDadoVersao->db30_data);
      $this->sObservacao = $oDadoVersao->db30_obs;

    }

  }

  public function getCodigo() {
    return $this->iCodigo;
  }

  public function setCodigo($iCodigo) {
    $this->iCodigo = $iCodigo;
  }

  public function getVersaoFormatada() {
    return $this->iVersao . "." . $this->iRelease . "." . $this->iSubrelease;
  }

  public function setUrlAtualizacao($sUrl) {
    $this->sUrlAtualizacao = $sUrl;
  }

  public function geturlAtualizacao() {
    return $this->sUrlAtualizacao;
  }

  public function setProximaVersao($sVersao) {
    $this->sProximaVersao = $sVersao;
  }

  public function getProximaVersao() {
    return $this->sProximaVersao;
  }

  public function getMd5Checksum() {
    return $this->sMd5Checksum;
  }

  public function setMd5Checksum($sMd5Checksum) {
    $this->sMd5Checksum = $sMd5Checksum;
  }

  //@TODO gerar outros getters e setters

}