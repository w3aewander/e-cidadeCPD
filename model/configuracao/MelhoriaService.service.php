<?php

use \ECidade\Core\Config as AppConfig;

class MelhoriaService {

  // tipos de eventos enviados para a API
  const TENTATIVA_ATUALIZACAO = 1;
  const ATUALIZADO = 2;
  const ERRO = 3;


  /** @const DIR_MELHORIAS Diretório Temporário */
  const DIR_MELHORIAS = 'tmp/melhorias/';

  /** @var AppConfig Configurações do Plugin */
  private $sysConfig;

  /** @var Plugin Instancia do Plugins de Melhorias */
  private $plugin;

  /** @var string Versao do sistema */
  private $versao;

  /** @var string Token de acesso à api */
  private $accessToken;

  /**
   * Construtor
   *
   * @param Plugin $plugin Pluing de Atualização de Melhorias
   * @param AppConfig $sysConfig Configurações
   */
  public function __construct(Plugin $plugin, AppConfig $sysConfig) {

    $this->plugin = $plugin;
    $this->sysConfig = $sysConfig;
    $this->prepare();
  }

  /**
   * Obtem Lista de Atualizações
   *
   * @param string|integer $de Código da Atualização
   * @param string|integer $para Código da Atualização
   *
   * @return stdClass
   */
  public function getAtualizacoes($de, $para = '') {

    $url = 'versoes/' . $de . '/atualizacoes';

    if (!empty($para)) {
      $url .= '/' . $para;
    }

    return $this->request($url);
  }

  /**
   * Sincroniza as melhorias atualizadas localmente com as melhorias que existem na API a partir de uma baseline
   *
   * @param  integer $id             Id da melhoria na API
   * @param  UsuarioSistema $usuarioSistema Instancia do usuario logado
   * @param  DBDate $data           Instancia da data atual
   * @return boolean                 Se houve sucesso
   * @throws BusinessException Se houve algum erro ao processar
   */
  public function sincronizar($id, $usuarioSistema, $data) {

    $dadosParaSincronizar = $this->request('versoes/sincronizar/' . $this->versao . '/' . $id);

    foreach ($dadosParaSincronizar as $dadosMelhoria) {

      // HACK
      if ($dadosMelhoria->release === "2358") {
        continue;
      }

      Melhoria::salvar($dadosMelhoria->id, $usuarioSistema, $data);
    }

    return true;
  }

  /**
   * Faz consulta na api de pacotes de atualização
   *
   * @param string $url Recurso da Api
   *
   * @return strClass
   */
  private function request($uri) {

    $request = $this->getHttpRequest();
    $request->addOptions(array(
      'headers' => array(
        'Accept' => 'application/json',
        'X-Access-Token' => $this->accessToken
      )
    ));
    $request->send($uri);
    $resultBody = $request->getBody();

    return json_decode($resultBody);
  }

  /**
   * Execute passos de atualização
   *
   * @param stdClass $melhoria Informações da Atualização
   */
  public function atualizar(stdClass $melhoria) {

    $this->notify($melhoria->id, MelhoriaService::TENTATIVA_ATUALIZACAO, $_SERVER);

    try {

      $filename = $this->downloadPacote($melhoria->id, $melhoria->link);
      $oMelhoria = new Melhoria($filename, $melhoria->id, $melhoria->date);

      // release notes
      foreach ($melhoria->release_notes as $releaseNote) {

        $releaseNoteFilename = $this->downloadReleaseNote($releaseNote->name, $releaseNote->link);
        $releaseNote->fileName = $releaseNoteFilename;

        //tutorial
        if (!empty($releaseNote->tutorial)) {
          // utilizamos o mesmo metodo, porque o arquivo vai ficar no mesmo lugar
          $releaseNote->tutorial = $this->downloadReleaseNote($releaseNote->tutorial->name, $releaseNote->tutorial->link);
        }

        $oMelhoria->addReleaseNote($releaseNote);
      }

      $oMelhoria->instalar();

      $this->notify($melhoria->id, MelhoriaService::ATUALIZADO, array());

    } catch (\Exception $e) {
      $this->notify($melhoria->id, MelhoriaService::ERRO, array('message' => $e->getMessage()));
      throw $e;
    }
  }

  /**
   * Realiza uma requisição e salva o conteudo em um arquivo
   * @param string $url URL da requisicao
   * @param string $destino Arquivo onde será salvo o retorno
   * @return void
   */
  private function downloadTo($url, $destino) {

    $req = $this->getHttpRequest();
    // remove base url
    $req->addOptions(array(
      'baseUrl' => '',
      'headers' => array(
        'X-Access-Token' => $this->accessToken
      )
    ));
    $req->send($url);

    if (file_exists($destino)) {
      unlink($destino);
    }

    file_put_contents($destino, $req->getBody());
  }

  /**
   * Download do Pacote de Atualização
   *
   * @param string $name Nome do Arquivo
   * @param strin $url Caminho para download
   * @return string
   */
  private function downloadPacote($name, $url) {

    $filename = self::DIR_MELHORIAS . 'pacotes/' . $name . '.tar.bz2';
    $this->downloadTo($url, $filename);
    return $filename;
  }

  private function downloadReleaseNote($name, $url) {

    $filename = self::DIR_MELHORIAS . 'release_notes/' . $name;
    $this->downloadTo($url, $filename);
    return $filename;
  }

  /**
   * Retorna a instancia de DBHttpRequest configurada para requisicoes a API de melhorias
   * @return DBHttpRequest  instancia de DBHttpRequest
   */
  public function getHttpRequest() {

    $pluginConfig = PluginService::getPluginConfig($this->plugin);
    $request = new DBHttpRequest($this->sysConfig);

    // add base url based on plugin
    $request->addOptions(array(
      'baseUrl' => $pluginConfig['apiUrl']
    ));

    if (empty($pluginConfig['clientId']) || empty($pluginConfig['clientSecret']) ) {
      throw new Exception("Credenciais de acesso estão mal-configuradas no arquivo de configuração do plugin.");
    }

    $clientId = $pluginConfig['clientId'];
    $clientSecret = $pluginConfig['clientSecret'];

    if (empty($this->accessToken)) {
      $this->accessToken = $this->getAccessToken($request, $clientId, $clientSecret);
    }

    return $request;
  }

  /**
   * Cria estrutura de pastas que armazenam os pacotes
   */
  private function prepare() {

    if (!is_dir(self::DIR_MELHORIAS . 'pacotes')) {
      mkdir(self::DIR_MELHORIAS . 'pacotes', 0775, true);
    }

    if (!is_dir(self::DIR_MELHORIAS . 'release_notes')) {
      mkdir(self::DIR_MELHORIAS . 'release_notes', 0775, true);
    }

    require('libs/db_acessa.php');
    $this->versao = 2 . $db_fonte_codversao . $db_fonte_codrelease;
  }

  /**
   * Cria uma task para execução pelo gerenciador de tarefas do sistema
   * de acordo com os horários informados
   * @param  array $horarios array de horarios do agendamento
   * @return void
   */
  public function agendar($horarios) {

    $oJob = new Job();
    $oJob->setNome('MelhoriaTask');
    $oJob->setCodigoUsuario(1);
    $oJob->setDescricao('Task de atualizacao das melhorias');
    $oJob->setNomeClasse('MelhoriaTask');
    $oJob->setCaminhoPrograma('model/configuracao/MelhoriaTask.model.php');
    $oJob->setTipoPeriodicidade(Agenda::PERIODICIDADE_DIARIA);
    foreach ($horarios as $horario) {
      $oJob->adicionarPeriodicidade(str_replace(":","", $horario));
    }

    $oJob->salvar();

    if (empty($horarios)) {
      $oJob->excluir();
    }

  }

  /**
   * Busca o token de acesso à api
   * @param  DBHttpRequest $request
   * @param  string $clientId
   * @param  string $clientSecret
   * @return string
   */
  private function getAccessToken($request, $clientId, $clientSecret) {

    $data = array(
      'clientId' => $clientId,
      'clientSecret' => $clientSecret
    );

    $request->send('auth/createToken', 'POST', array(
      'body' => http_build_query($data)
    ));
    $result = json_decode($request->getBody());

    if (!empty($result->statusCode)) {
      throw new Exception($result->message);
    }

    return $result->token;
  }

  public function notify($codigoMelhoria, $type, $context) {

    $request = $this->getHttpRequest();
    $request->addOptions(array(
      'headers' => array(
        'Accept' => 'application/json',
        'X-Access-Token' => $this->accessToken
      )
    ));

    $uri = null;

    switch ($type) {
      case MelhoriaService::TENTATIVA_ATUALIZACAO:
        $uri = "eventos/$codigoMelhoria/tentativaAtualizacao";
        break;
      case MelhoriaService::ATUALIZADO:
        $uri = "eventos/$codigoMelhoria/atualizado";
        break;
      case MelhoriaService::ERRO:
        $uri = "eventos/$codigoMelhoria/erro";
        break;
    }

    $data = array(
      'descricao' => json_encode($context)
    );

    $request->send($uri, 'POST', array(
      'body' => http_build_query($data)
    ));

    return;
  }

  /**
   * Retorna um array de horários agendados para execução da atualização.
   * @return array
   */
  public function getHorariosAgendados() {

    try {

      $oJob = new Job('MelhoriaTask');
      return $oJob->getPeriodicidades();
    } catch (Exception $oErro ) {
      return array();
    }

  }

}
