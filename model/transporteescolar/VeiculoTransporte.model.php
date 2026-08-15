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


/**
 * Classe para controle dos veículos que realizam o transporte municipal
 *
 * @author Iuri Guntchnigg iuri@dbseller.com.br
 * @package transporteescolar
 * @version $Revision: 1.8 $
 */
class VeiculoTransporte {

  /**
   * codigo do Transporte
   * @var integer
   */
  private $iCodigo = null;

  /**
   * Identificacao do veiculo
   * @var string
   */
  private $sIdentificacao = null;

  /**
   * Numero de passageiros do veiculo
   * @var integer
   */
  private $iNumeroPassageiros = 0;

  /**
   * Veiculo Vinculado ao transporte
   * @var Veiculo
   */
  private $oVeiculo;

  /**
   * Tipo do veiculo
   * @var TipoVeiculo
   */
  protected $oTipoVeiculo;

  /**
   * Empresa responsavel pelo veiculo
   * @var CgmJuridico
   */
  private $oCgm;

  /**
   * Codigo do vinculo com o veiculos
   * @var integer
   */
  protected $iCodigoVeiculo;

  /**
   * codigo da empresa
   * @var integer
   */
  protected $iCodigoEmpresa;

    /**
   * codigo da empresa
   * @var integer
   */
  protected $ano;

    /**
   * codigo da empresa
   * @var integer
   */
  protected $modalidade;


    /**
   * codigo da empresa
   * @var boolean
   */
  protected $acessibilidade;

    /**
   * codigo da empresa
   * @var varchar(8)
   */
  protected $placa;
  
    /**
   * codigo da empresa
   * @var varchar(11)
   */
  protected $renavam;  
  /**
   * Instancia um veiculo de transporte
   * Caso seja informado o codigo do veiculo, traz os dados do veiculo,
   * @param integer $iCodigo código do veiculo
   * @throws ParameterException veiculo informado nao é encontrado
   */
  public function __construct($iCodigo = null) {

    if (!empty($iCodigo)) {

      $oDaoVeiculoTransporte = new cl_veiculotransportemunicipal();
      $sSqlVeiculo           = $oDaoVeiculoTransporte->sql_query_vinculo_veiculo($iCodigo);
      $rsVeiculo             = $oDaoVeiculoTransporte->sql_record($sSqlVeiculo);
      if ($oDaoVeiculoTransporte->numrows == 0) {

        $sMensagem                  = "educacao.transporteescolar.VeiculoTransporte.veiculo_nao_encontrado";
        $oVariaveis                 = new stdClass();
        $oVariaveis->codigo_veiculo = $iCodigo;

        throw new ParameterException(_M($sMensagem, $oVariaveis));
      }

      $oDadosVeiculo        = db_utils::fieldsMemory($rsVeiculo, 0);
      $this->iCodigo        = $iCodigo;
      $this->iCodigoEmpresa = $oDadosVeiculo->tre03_cgm;
      $this->iCodigoVeiculo = $oDadosVeiculo->tre02_veiculos;
      $this->setIdentificacao($oDadosVeiculo->tre01_identificacao);
      $this->setNumeroPassageiros($oDadosVeiculo->tre01_numeropassageiros);
      $this->setModalidade($oDadosVeiculo->tre01_modalidadeveiculo);
      $this->setAcessibilidade($oDadosVeiculo->tre01_acessibilidade);
      $this->setAno($oDadosVeiculo->tre01_anoveiculo);
      $oTipoVeiculo = new TipoVeiculo($oDadosVeiculo->tre01_tipoveiculo);
      $this->setTipoVeiculo($oTipoVeiculo);
    }
  }

  /**
   * Retorna codigo de cadastro do veiculo
   * @return integer codigo do veiculo
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  /**
   * Retorna a identificacao do veiculo
   * @return string
   */
  public function getIdentificacao() {
    return $this->sIdentificacao;
  }

  /**
   * Define a identificacao do veiculo
   * @param string $iIdentificacao identificacao do veiculo
   */
  public function setIdentificacao($iIdentificacao) {
    $this->sIdentificacao = $iIdentificacao;
  }

  /**
   * Retorna o numero de passageiros do veiculo
   * @return integer numero de passageiros
   */
  public function getNumeroDePassageiros() {
    return $this->iNumeroPassageiros;
  }

  /**
   * Define o numero de passageiros do veiculo
   * @param  integer $iNumeroPassageiros
   */
  public function setNumeroPassageiros($iNumeroPassageiros) {

    if (!DBNumber::isInteger($iNumeroPassageiros)) {
      throw new ParameterException(_M('educacao.transporteescolar.VeiculoTransporte.passageiros_nao_inteiro'));
    }
    $this->iNumeroPassageiros = $iNumeroPassageiros;
  }

  /**
   * Retorna o veiculo que realiza o transporte
   * @return Veiculo
   */
  public function getVeiculo() {

    if ($this->oVeiculo == null && $this->iCodigoVeiculo != null) {
      $this->oVeiculo = new Veiculo($this->iCodigoVeiculo);
    }
    return $this->oVeiculo;
  }

  /**
   * Define o veiculo da prefeitura que ira fazer o transporte
   * @param Veiculo $oVeiculo
   */
  public function setVeiculo(Veiculo $oVeiculo) {

    $this->iCodigoVeiculo = $oVeiculo->getCodigo();
    $this->oVeiculo       = $oVeiculo;
  }

  /**
   * Empresa privada responsavel pelo transporte
   * @param CgmJuridico $oCgm
   */
  public function setEmpresaResponsavel(CgmJuridico $oCgm) {

    $this->oCgm           = $oCgm;
    $this->iCodigoEmpresa = $oCgm->getCodigo();
  }

  /**
   * Retorna a empresa privada responsavel pelo transporte
   * @return CgmJuridico
   */
  public function getEmpresaResponsavel() {

    if (!empty($this->iCodigoEmpresa) && $this->oCgm == null) {
      $this->oCgm = CgmFactory::getInstanceByCgm($this->iCodigoEmpresa);
    }
    return $this->oCgm;
  }

  public function getInfoVeiculoTerceiro()
  {
    $oDaoVeiculoTransporteTerceiro = new cl_veiculotransportemunicipalterceiro;
    $campos = "tre03_cgm,tre03_placa,tre03_renavam";
    $where = "tre03_veiculotransportemunicipal = {$this->getCodigo()}";
    $sqlVeiculoTerceiro = $oDaoVeiculoTransporteTerceiro->sql_query_file(null,$campos,null,$where);
    $rsVeiculoTerceiro = db_query($sqlVeiculoTerceiro);
    $infoVeiculoTerceiro = [
      'cgm' => '',
      'placa' => '',
      'renavam' => ''
    ];
    if(pg_num_rows($rsVeiculoTerceiro) > 0){
      $veiculoTerceiro = db_utils::fieldsMemory($rsVeiculoTerceiro,0);
      $infoVeiculoTerceiro = [
        'cgm' => $veiculoTerceiro->tre03_cgm,
        'placa' => $veiculoTerceiro->tre03_placa,
        'renavam' => $veiculoTerceiro->tre03_renavam
      ];
    }
    return $infoVeiculoTerceiro;
  }
 
  /**
   * Retorna o tipo do transporte do veiculo
   * @return TipoVeiculo
   */
  public function getTipoVeiculo() {
    return $this->oTipoVeiculo;
  }

  /**
   * Define o tipo de transporte do veiculo
   * @param TipoVeiculo $oTipoVeicul
   */
  public function setTipoVeiculo(TipoVeiculo $oTipoVeiculo) {
    $this->oTipoVeiculo = $oTipoVeiculo;
  }

  public function setAno($ano){
    $this->ano = $ano;
  }

  public function getAno(){
    return $this->ano;
  }

  public function setModalidade($modalidade){
    $this->modalidade = $modalidade;
  }

  public function getModalidade(){
    return $this->modalidade;
  }
  
  public function setAcessibilidade($acessibilidade){
    $this->acessibilidade = $acessibilidade;
  }

  public function getAcessibilidade(){
    return $this->acessibilidade;
  }  

  public function setPlaca($placa){
    $this->placa = $placa;
  }

  public function getPlaca(){
    return $this->placa;
  }  

  public function setRenavam($renavam){
    $this->renavam = $renavam;
  }

  public function getRenavam(){
    return $this->renavam;
  }  

  /**
   * Persiste os dados do veiculo na base de dados
   * @throws BusinessException
   */
  public function salvar() {

    if (!db_utils::inTransaction()) {
      throw new DBException('Sem transação com o banco de dados.');
    }

    if ($this->getIdentificacao() == '') {
      throw new BusinessException(_M('educacao.transporteescolar.VeiculoTransporte.identificacao_nao_informada'));
    }

    if ($this->getNumeroDePassageiros() == null || $this->getNumeroDePassageiros() == 0) {
      throw new BusinessException(_M('educacao.transporteescolar.VeiculoTransporte.numero_passageiros_nao_informado'));
    }

    if ($this->getTipoVeiculo() == null || $this->getTipoVeiculo()->getCodigo() == '') {
      throw new BusinessException(_M('educacao.transporteescolar.VeiculoTransporte.tipo_transporte_nao_informado'));
    }

    if (!empty($this->oCgm) && !empty($this->oVeiculo)) {
      throw new BusinessException(_M('educacao.transporteescolar.VeiculoTransporte.tipo_de_vinculo_invalido'));
    }

    /**
     * Caso nao seja informado nenhuma empresa e nenhum veiculo nao podemos persistir os dados
     */
    if (empty($this->oCgm) && empty($this->oVeiculo)) {
      throw new BusinessException(_M('educacao.transporteescolar.VeiculoTransporte.vinculo_do_veiculo_nao_informado'));
    }

    $oDaoVeiculoTransporte = new cl_veiculotransportemunicipal;

    if (!empty($this->oVeiculo) && $this->getCodigo() == null) {

      /**
       * Buscamos se o veiculo ja nao encontra-se cadastrado como transporte municipal
       */
      $sWhereVeiculoTransporte = "tre02_veiculos = {$this->oVeiculo->getCodigo()}";
      $sSqlVeiculoTransporte   = $oDaoVeiculoTransporte->sql_query_vinculo_veiculo(
                                                                                    null,
                                                                                    "tre02_sequencial",
                                                                                    null,
                                                                                    $sWhereVeiculoTransporte
                                                                                  );
      $rsVeiculoTransporte = $oDaoVeiculoTransporte->sql_record($sSqlVeiculoTransporte);

      if ($oDaoVeiculoTransporte->numrows > 0) {
        throw new BusinessException(_M('educacao.transporteescolar.VeiculoTransporte.vinculo_do_veiculo_existente'));
      }
    }

    if (!empty($this->oCgm) && $this->getCodigo() == null) {

      /**
       * Buscamos se um mesmo veiculo de um CGM ja nao encontra-se cadastrado como transporte municipal
       */
      $sWhereVeiculoTransporte  = "     tre03_cgm = {$this->getEmpresaResponsavel()->getCodigo()}";
      $sWhereVeiculoTransporte .= " and tre01_identificacao = trim('{$this->getIdentificacao()}')";
      $sSqlVeiculoTransporte    = $oDaoVeiculoTransporte->sql_query_vinculo_veiculo(
                                                                                      null,
                                                                                      "tre02_sequencial",
                                                                                      null,
                                                                                      $sWhereVeiculoTransporte
                                                                                   );
      $rsVeiculoTransporte = $oDaoVeiculoTransporte->sql_record($sSqlVeiculoTransporte);

      if ($oDaoVeiculoTransporte->numrows > 0) {

        $oVariaveis               = new stdClass();
        $oVariaveis->sIdentificao = $this->getIdentificacao();
        throw new BusinessException(_M('educacao.transporteescolar.VeiculoTransporte.vinculo_do_cgm_existente', $oVariaveis));
      }
    }

    $oDaoVeiculoTransporte->tre01_identificacao           = $this->getIdentificacao();
    $oDaoVeiculoTransporte->tre01_numeropassageiros       = $this->getNumeroDePassageiros();
    $oDaoVeiculoTransporte->tre01_modalidadeveiculo       = $this->getModalidade();
    $oDaoVeiculoTransporte->tre01_acessibilidade       = $this->getAcessibilidade();
    $oDaoVeiculoTransporte->tre01_anoveiculo       = $this->getAno();
    $oDaoVeiculoTransporte->tre01_tipoveiculo = $this->getTipoVeiculo()->getCodigo();
    if (empty($this->iCodigo)) {

      $oDaoVeiculoTransporte->incluir(null);
      $this->iCodigo = $oDaoVeiculoTransporte->tre01_sequencial;
    } else {

      $oDaoVeiculoTransporte->tre01_sequencial = $this->getCodigo();
      $oDaoVeiculoTransporte->alterar($this->getCodigo());
    }
    if ($oDaoVeiculoTransporte->erro_status == 0) {

      $sMensagemErro       = 'educacao.transporteescolar.VeiculoTransporte.erro_persitencia_veiculo';
      $oVariavel           = new stdClass();
      $oVariavel->erro_dao = $oDaoVeiculoTransporte->erro_status;
      throw new BusinessException(_M($sMensagemErro, $oVariavel));
    }

    /**
     * Excluimos o vinculo do transporte com uma empresa e veiculo da prefeitura,
     * para apenas deixar incluso os dados da prefeitura
     */
    $this->removerVinculos();
    if (!empty($this->oCgm)) {

      $oDaoTransporteEmpresa                                   = new cl_veiculotransportemunicipalterceiro();
      $oDaoTransporteEmpresa->tre03_cgm                        = $this->oCgm->getCodigo();
      $oDaoTransporteEmpresa->tre03_veiculotransportemunicipal = $this->getCodigo();
      $oDaoTransporteEmpresa->tre03_placa = $this->getPlaca();
      $oDaoTransporteEmpresa->tre03_renavam = $this->getRenavam();
      $oDaoTransporteEmpresa->incluir(null);
      if ($oDaoTransporteEmpresa->erro_status == 0) {

        $sMensagemErro       = 'educacao.transporteescolar.VeiculoTransporte.erro_inclusao_vinculo_empresa';
        $oVariavel           = new stdClass();
        $oVariavel->erro_dao = $oDaoVeiculoTransporte->erro_status;
        throw new BusinessException(_M($sMensagemErro, $oVariavel));
      }
    }

    if (!empty($this->oVeiculo)) {

      $oDaoTransporteVeiculo                                   = new cl_veiculotransportemunicipalveiculos();
      $oDaoTransporteVeiculo->tre02_veiculos                   = $this->oVeiculo->getCodigo();
      $oDaoTransporteVeiculo->tre02_veiculotransportemunicipal = $this->getCodigo();
      $oDaoTransporteVeiculo->incluir(null);
      if ($oDaoTransporteVeiculo->erro_status == 0) {

        $sMensagemErro       = 'educacao.transporteescolar.VeiculoTransporte.erro_inclusao_vinculo_veiculo';
        $oVariavel           = new stdClass();
        $oVariavel->erro_dao = $oDaoTransporteVeiculo->erro_status;
        throw new BusinessException(_M($sMensagemErro, $oVariavel));
      }
    }
  }

  public function remover() {

    if (!db_utils::inTransaction()) {
      throw new DBException('Sem transação com o banco de dados.');
    }

    $oDaoLinhaTransporteHorarioVeiculo = new cl_linhatransportehorarioveiculo();
    $sSqlLinhaTransporteHorarioVeiculo = $oDaoLinhaTransporteHorarioVeiculo->sql_query(null, 'tre08_sequencial', null, "tre08_veiculotransportemunicipal = {$this->getCodigo()}");
    $oDaoLinhaTransporteHorarioVeiculo->sql_record($sSqlLinhaTransporteHorarioVeiculo);

    if ($oDaoLinhaTransporteHorarioVeiculo->numrows > 0) {
      throw new BusinessException(_M('educacao.transporteescolar.VeiculoTransporte.erro_exclusao_veiculo_vinculos'));
    }

    /**
     * Remove o vinculo
     */
    $this->removerVinculos();
    $oDaoVeiculoTransporte = new cl_veiculotransportemunicipal();
    $oDaoVeiculoTransporte->excluir($this->getCodigo());
    if ($oDaoVeiculoTransporte->erro_status == 0) {

      $sMensagemErro       = 'educacao.transporteescolar.VeiculoTransporte.erro_exclusao_veiculo';
      $oVariavel           = new stdClass();
      $oVariavel->erro_dao = $oDaoVeiculoTransporte->erro_status;
      throw new BusinessException(_M($sMensagemErro, $oVariavel));
    }
  }

  /**
   * Remove o vinculo com empresas e veiculos
   * @throws BusinessException
   */
  protected function removerVinculos() {

    if ($this->iCodigo != null) {

      $oDaoTransporteEmpresa = new cl_veiculotransportemunicipalterceiro();
      $oDaoTransporteEmpresa->excluir(null, "tre03_veiculotransportemunicipal = {$this->getCodigo()}");
      if ($oDaoTransporteEmpresa->erro_status == 0) {

        $sMensagemErro       = 'educacao.transporteescolar.VeiculoTransporte.erro_exclusao_vinculo_empresa';
        $oVariavel           = new stdClass();
        $oVariavel->erro_dao = $oDaoTransporteEmpresa->erro_status;
        throw new BusinessException(_M($sMensagemErro, $oVariavel));
      }

      $oDaoTransporteVeiculo = new cl_veiculotransportemunicipalveiculos();
      $oDaoTransporteVeiculo->excluir(null, "tre02_veiculotransportemunicipal = {$this->getCodigo()}");
      if ($oDaoTransporteVeiculo->erro_status == 0) {

        $sMensagemErro       = 'educacao.transporteescolar.VeiculoTransporte.erro_exclusao_vinculo_veiculo';
        $oVariavel           = new stdClass();
        $oVariavel->erro_dao = $oDaoTransporteVeiculo->erro_status;
        throw new BusinessException(_M($sMensagemErro, $oVariavel));
      }
    }
  }
}