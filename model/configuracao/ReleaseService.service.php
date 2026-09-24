<?php

class ReleaseService {

  const HOST = "ecidade.dbseller.com.br";

  const PATH = "/releases/";

  private $sDirEcidade;

  private $sDirEcidadeonline;

  private $oPluginConfig;

  public function __construct() {

    $this->oPluginConfig = PluginService::getPluginConfig(new Plugin(null, 'atualiza_release'));;

    if (!empty($this->oPluginConfig['sProxy'])) {

      $aContext = array(
        'http' => array(
          'proxy' => 'tcp://' . $this->oPluginConfig['sProxy'],
          'request_fulluri' => true
        )
      );

      stream_context_set_default($aContext);
    }

    $cxContext = stream_context_create($aContext);

    if (!$this->checkConnection()) {
      throw new BusinessException("Não foi possível conectar ao host: " . self::HOST);
    }

    $this->sDirEcidade = dirname(dirname(__DIR__)) . "/";

  }

  /**
   * Verifica se a conexão de self::HOST url está ok
   */
  private function checkConnection() {

    $ip = gethostbyname(self::HOST);

    return $ip != self::HOST;
  }

  /**
   * Verifica o código de retorno de uma determinada URL
   * @param  string $sUrl URL a ser verificada
   * @return boolean     
   */
  private function checkUrl($sUrl) {

    $aHeaders = get_headers($sUrl);
    preg_match_all("/\d{3}/", $aHeaders[0], $aCodes);
    return $aCodes[0][0] < 400;
  }

  public function verificaAtualizacao(Release $oRelease) {

    if (!$this->checkConnection()) {
      throw new BusinessException("Não foi possível conectar ao host: " . self::HOST);
    }

    $sVersao  = $oRelease->getVersaoFormatada();

    $sUrl = "http://" . self::HOST . self::PATH . "index.php?sVersao=".$sVersao;

    if (!$this->checkUrl($sUrl)) {
      throw new BusinessException("Não foi possível verificar a atualização.\n$sUrl");
    }

    $sRetorno = file_get_contents( $sUrl);

    $oRetorno = json_decode($sRetorno);

    if ( !$oRetorno->atualizado ) {
      $oRelease->setUrlAtualizacao($oRetorno->url);
      $oRelease->setProximaVersao($oRetorno->proximaVersao);
      $oRelease->setMd5Checksum($oRetorno->md5_checksum);
    }

    return $oRetorno->atualizado;
  }

  public function downloadPacote(Release $oRelease) {

    if (!$this->checkConnection()) {
      throw new BusinessException("Não foi possível conectar ao host: " . self::HOST);
    }

    $sUrl = $oRelease->getUrlAtualizacao();

    if ( empty($sUrl) ) {
      throw new BusinessException("URL de download do pacote não informado.");
    }

    if ( !$this->checkUrl($sUrl) ) {
      throw new BusinessException("Erro na requisição do pacote:\n$sUrl");
    }

    file_put_contents("tmp/release_" . $oRelease->getProximaVersao() . ".tar.bz2", file_get_contents($sUrl));

    if (md5_file("tmp/release_" . $oRelease->getProximaVersao() . ".tar.bz2") != $oRelease->getMd5Checksum() ) {
      throw new BusinessException("Arquivo baixado está corrompido.");
    }

  }

  public function atualiza(Release $oReleaseAtual) {

    $sDirRelease = "tmp/release_" . $oReleaseAtual->getProximaVersao();

    $this->__extract($sDirRelease . ".tar.bz2", $sDirRelease);

    /**
     * Atualiza o banco de dados
     */
    require_once(modification($sDirRelease . "/e-cidade-" . $oReleaseAtual->getProximaVersao()) . "-linux/DBMigration.php");


    try {

      DBMigration::begin();
      DBMigration::run("up");

      /**
       * Atualiza as aplicações
       */
      $sReleaseEcidade       = $sDirRelease . "/e-cidade-" . $oReleaseAtual->getProximaVersao() . "-linux/e-cidade/";
      $sReleaseEcidadeOnline = $sDirRelease . "/e-cidade-" . $oReleaseAtual->getProximaVersao() . "-linux/e-cidadeonline/";

      /**
       * Validação antes de executar
       */
      $this->__copiaFontesRelease($sReleaseEcidade, $this->sDirEcidade, true);

      /*if (is_dir($sReleaseEcidadeOnline)) {
        $aScan = scandir($sReleaseEcidadeOnline);
        if (count($aScan) > 2 && empty($this->sDirEcidadeonline) ) {
          throw new BusinessException("Diretório e-cidadeonline mal-configurado no provedor.");
        }

        $this->__copiaFontesRelease($sReleaseEcidadeOnline, $this->sDirEcidadeonline, true);
      }*/

      /**
       * Copia os fontes da release pro e-cidade
       */
      $this->__copiaFontesRelease($sReleaseEcidade, $this->sDirEcidade);

      /**
       * Copia os fontes da release pro e-cidadeonline
       */
      /*if (is_dir($sReleaseEcidadeOnline)) {

        $aScan = scandir($sReleaseEcidadeOnline);
        if (count($aScan) == 2 && empty($this->sDirEcidadeonline) ) {
          throw new BusinessException("Diretório e-cidadeonline mal-configurado no provedor.");
        }

        $this->__copiaFontesRelease($sReleaseEcidadeOnline, $this->sDirEcidadeonline);

      }*/

      DBMenu::limpaCache();

      DBMigration::commit();

    } catch (Exception $e) {
      DBMigration::rollback();
      throw $e;
    }

  }

  private function __extract($sDirOrigem, $sDirDestino) {

    if (!file_exists($sDirOrigem)) {
      throw new BusinessException("Arquivo de atualização não existe.");
    }

    $oPhar = new PharData($sDirOrigem);

    if (is_dir($sDirDestino)) {
      $this->__recursiveRemove($sDirDestino);
    }

    mkdir($sDirDestino, 0775);

    $oPhar->extractTo($sDirDestino);

  }

  private function __recursiveRemove($sDir) {

    $oDirs = new DirectoryIterator($sDir);

    foreach ($oDirs as $oDir) {

      if ( $oDir->isDot() ) {
        continue;
      }

      if ( $oDir->isDir() ) {
        $this->__recursiveRemove($oDir->getPathname());
      }

      if ($oDir->isFile()) {
        //echo "Apagando arquivo {$oDir->getPathname()}\n";
        unlink($oDir->getPathname());
      }

    }

    //echo "Apagando pai {$sDir}\n";
    rmdir($sDir);

  }

  private function __copiaFontesRelease($sDiretorioAtual, $sDiretorioFinal, $lTeste = false) {

    if (!is_dir($sDiretorioAtual)) {
      return false;
    }

    $oIteratorEcidade = new RecursiveDirectoryIterator($sDiretorioAtual);

    foreach (new RecursiveIteratorIterator($oIteratorEcidade) as $oDir) {

      if ( in_array($oDir->getFilename(), array('.', '..')) ) {
        //echo "Passando diretório 'pontos'\n";
        continue;
      }

      if ( $oDir->isDir() && !is_dir($oDir->getPathname()) ) {
        continue;
      }

      $sArquivoOrigem  = $oDir->getPathname();
      $sArquivoDestino = str_replace( $sDiretorioAtual, rtrim($sDiretorioFinal, "/")  . "/", $sArquivoOrigem);

      if ( $lTeste ) {

        if ( is_dir(dirname($sArquivoDestino)) && !is_writable(dirname($sArquivoDestino))) {
          throw new BusinessException("Sem permissão para criar a pasta: " . dirname($sArquivoDestino));
        }

        if (file_exists($sArquivoDestino) && !is_writable($sArquivoDestino)) {
          throw new BusinessException("Sem permissão para escrever o arquivo $sArquivoDestino");
        }

        continue;
      }

      if ( !is_dir(dirname($sArquivoDestino)) ) {
        // echo "Criando diretório " . dirname($sArquivoDestino) . "\n";
        mkdir(dirname($sArquivoDestino), 0775, true);
      }

      // echo "Copiando arquivo $sArquivoOrigem -> $sArquivoDestino\n";
      copy($sArquivoOrigem, $sArquivoDestino);
      //chmod($sArquivoDestino, 0775);
    }

  }

}
