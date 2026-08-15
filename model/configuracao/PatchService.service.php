<?php

class PatchService {

  private $oPlugin = null;

  private $aConfig = null;

  private $sVersao;

  public function __construct() {

    $this->oPlugin = new Plugin(null, 'atualiza_patch');

    $aConfig = PluginService::getPluginConfig($this->oPlugin);
    if ( !$aConfig )  {
      $aConfig = parse_ini_file('plugins/atualiza_patch/config.ini', true);
    }

    $this->aConfig = $aConfig;

    include(modification('libs/db_acessa.php'));
    $this->sVersao = "2.$db_fonte_codversao.$db_fonte_codrelease";

  }

  public static function getTarefasAtualizadas() {

    $oChangelog = DBCache::read('changelog_patch.json');

    if ( !$oChangelog ) {
      $oChangelog = "{}";
    }

    return $oChangelog;
  }

  /**
   * Retorna a diferencia da utlima atualizacao no sistema com a versao do patch no provedor.
   */
  public function checkUpdates() {

    /**
     * Valida conexão com o servidor
     */
    $sNomeChangelog = "changelog-{$this->sVersao}.json";
    $sUrl = $this->aConfig["sUrlPatches"];

    if (!$this->checkUrl($sUrl . $sNomeChangelog)) {
      throw new Exception("Erro ao buscar o changelog da versão.\nFavor Verifique a URL configurada.");
    }

    /**
     * Baixa o changelog para o tmp do sistema.
     */
    $this->download($sUrl, $sNomeChangelog);

    $sRawNewChangelog = file_get_contents("tmp/{$sNomeChangelog}");
    $oNewChangelog = json_decode($sRawNewChangelog);

    /**
     * Lê o ultimo changelog atualizado no sistema
     */
    $sChangelog = DBCache::read('changelog_patch.json');

    if (!$sChangelog) {
      $sChangelog = '{}';
    }

    $oChangelog = json_decode($sChangelog);

    /**
     * Verifica as diferenças entre os changelogs.
     */
    $oDiffChangelog = new stdClass();
    foreach ($oNewChangelog as $iTarefa => $oDado) {

      if ( !isset($oChangelog->{$iTarefa}) ) {
        $oDiffChangelog->{$iTarefa} = $oDado;
      }

    }

    /**
     * Ordena as tarefas para melhor visualização na tela.
     */
    $oDiffChangelog = (array) $oDiffChangelog;
    ksort($oDiffChangelog);
    $oDiffChangelog = (object) $oDiffChangelog;

    return $oDiffChangelog;
  }

  /**
   * Atualiza de fato o patch no sistema.
   */
  public function atualizar() {

    /**
     * Baixa o pacote do patch dar inicio ao processo de atualização
     */
    $sUrlPatch = $this->aConfig['sUrlPatches'];
    $this->checkUrl($sUrlPatch);
    $sNomeArquivo = "package_dbportal-{$this->sVersao}-patch.tar.bz2";
    $this->download($sUrlPatch, $sNomeArquivo);

    /**
     * Extrai os arquivos do pacote baixado
     */
    try {
      $this->__extract("tmp/$sNomeArquivo", 'tmp/' . basename($sNomeArquivo, '.tar.bz2') );
    } catch (PharException $e) {
      throw new BusinessException("Erro ao atualizar os arquivos do patch.");
    }

    /**
     * Busca os SQLs do funcoes8 e roda na base
     */
    $sDiretorioFuncoes8 = "tmp/" . basename($sNomeArquivo, '.tar.bz2') . "/dbportal-{$this->sVersao}-patch/package/funcoes8";
    $this->__atualizaFuncoes8($sDiretorioFuncoes8);


    /**
     * Verifica acertos em base
     */
    $sDiretorioAcertos = "tmp/" . basename($sNomeArquivo, ".tar.bz2") . "/dbportal-{$this->sVersao}-patch/package/dbportal_prj/db/DML";

    if ( is_dir( $sDiretorioAcertos ) ) {

      global $conn;

      $sSqlAcertos = DBDataBaseMigration::upgradeDatabase($conn, 'dml', $sDiretorioAcertos);

      if ( trim($sSqlAcertos) != '') {

        $rsAcertos = @db_query($conn, $sSqlAcertos);

        if (!$rsAcertos) {
          throw new DBException("Erro ao excutar acerto em base.\n\n" . "Erro:\n" . pg_last_error());
        }

        DBMenu::limpaCache();

      }

    }


    /**
     * Copia os fontes, primeiro testando todos, depois aplica as mudanças de fato
     */
    $sDiretorioEcidade = "tmp/" . basename($sNomeArquivo, '.tar.bz2') . "/dbportal-{$this->sVersao}-patch/package/dbportal_prj";
    $this->__copiaFontes($sDiretorioEcidade, '.', true);
    $this->__copiaFontes($sDiretorioEcidade, '.');


    /**
     * Atualiza a data da utltima atualizacao no arquivo de configurcao
     */
    $this->aConfig['sDataAtualizacao'] = date('d/m/Y H:i:s');
    PluginService::setPluginConfig($this->oPlugin, $this->aConfig);

    /**
     * Atualiza o arquivo de changelog no sistema
     */
    $sNomeChangelog = "changelog-{$this->sVersao}.json";
    DBCache::write("changelog_patch.json", file_get_contents("tmp/{$sNomeChangelog}"));

  }

  /**
   * Baixa um arquivo de uma url e salva no tmp do sistema.
   */
  private function download($sUrl, $sNomeArquivo) {

    if (file_exists("tmp/$sNomeArquivo")) {
      unlink("tmp/$sNomeArquivo");
    }

    $sProxy = str_replace("\n", "", shell_exec("cat /etc/profile | grep -i http_proxy | cut -d \" \" -f2 | cut -d \"=\" -f2"));

    $sCommand = 'cd tmp && wget ' . $sUrl . $sNomeArquivo . "?" . time() . " -O $sNomeArquivo";

    if ( !empty($sProxy) ) {
      $sCommand = "export http_proxy=$sProxy && " . $sCommand;
    }

    $result = system($sCommand, $iRetornoVal);

    if ($iRetornoVal != 0) {
      throw new BusinessException("Falha no download do patch.\n" . "Erro:\n" . $result);
    }

    return true;
  }

  /**
   * Extrai um arquivo em um determinado local.
   */
  private function __extract($sDirOrigem, $sDirDestino) {

    if (!file_exists($sDirOrigem)) {
      throw new BusinessException("Arquivo de atualização não existe.");
    }

    $oPhar = new PharData($sDirOrigem);

    if (is_dir($sDirDestino)) {
      $this->__recursiveRemove($sDirDestino);
    }

    mkdir($sDirDestino, 0775);

    $oPhar->extractTo($sDirDestino, null, true);

  }

  /**
   * Copia todos os arquivos de um diretorio para outro
   */
  private function __copiaFontes($sDiretorioAtual, $sDiretorioFinal, $lTeste = false) {

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
        mkdir(dirname($sArquivoDestino), 0775, true);
      }

      copy($sArquivoOrigem, $sArquivoDestino);
    }

  }

  /**
   * Remove um diretório recursivamente
   */
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
        unlink($oDir->getPathname());
      }

    }

    rmdir($sDir);

  }

  /**
   * Valida a conexão com uma URL
   */
  private function checkUrl($sUrl) {

    if ( !empty($_SERVER['http_proxy']) ) {

      stream_context_set_default(
        array(
          'http' => array(
            'proxy' => str_replace("http", "tcp", $_SERVER['http_proxy']),
            'request_fulluri' => true
          )
        )
      );

    }

    $aHeaders = get_headers($sUrl);

    preg_match_all("/\d{3}/", $aHeaders[0], $aCodes);

    return $aCodes[0][0] < 400;
  }

  /**
   * Busca e roda os SQLs do funcoes8 na base de dados
   */
  private function __atualizaFuncoes8($sDiretorio) {

    $oIteratorFuncoes = new RecursiveDirectoryIterator($sDiretorio);

    foreach (new RecursiveIteratorIterator($oIteratorFuncoes) as $oDir) {

      if ( in_array($oDir->getFilename(), array('.', '..', 'run.psql')) ) {
        continue;
      }

      $sSql = file_get_contents($oDir->getPathname());

      $rsSql = @db_query($sSql);

      if (!$rsSql) {
        throw new DBException("Erro ao executar o SQL do arquivo:\n". $oDir->getFilename() . "\n\nInformação do erro:\n" . pg_last_error());
      }

    }

  }

  /**
   * Agenda os horários que devem ser executado as atualizações dos patches
   * @param  array $aHorarios
   */
  public function agendar( $aHorarios ) {

    $oJob = new Job();
    $oJob->setNome('AtualizaPatchTask');
    $oJob->setCodigoUsuario(1);
    $oJob->setDescricao('Task de atualizacao dos patches');
    $oJob->setNomeClasse('AtualizaPatchTask');
    $oJob->setCaminhoPrograma('model/configuracao/AtualizaPatchTask.model.php');
    $oJob->setTipoPeriodicidade(Agenda::PERIODICIDADE_DIARIA);
    foreach ($aHorarios as $sHorario) {

      $iHorario = str_replace(":","", $sHorario);
      $oJob->adicionarPeriodicidade($iHorario);
    }

    $oJob->salvar();
  }

  /**
   * Retorna as Periodicidades que devem ser executado o patch
   * @return array
   */
  public function getHorarios() {

    try{

      $oJob = new Job('AtualizaPatchTask');
      return $oJob->getPeriodicidades();
    }catch( Exception $oErro ) {

      return array();
    }
  }

}

?>
