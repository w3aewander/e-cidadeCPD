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

/**
 * Avaliação de um elemento do procedimento de avaliação de um aluno em progressão parcial
 *
 * @todo Como pretende-se usar os calculos de avaliações já prontos, foi mantido metodos de retorno com dados padrão
 *       Ao ver que um metodo não for necessário, remova-o.
 *
 * @package    educacao
 * @subpackage progressaoparcial
 * @author     Andrio Costa <andrio.costa@dbseller.com.br>
 * @version    $Revision: 1.3 $
 */
final class AvaliacaoAproveitamentoProgressao {

  /**
   * Código do Aproveitamento
   * diarioprogressaoavaliacao / diarioprogressaoresultado
   * @var integer
   */
  private $iCodigo;

  /**
   * Guarda a instância das avaliações do aluno
   * @var DiarioAvaliacaoDisciplina
   */
  private $oDiarioProgressaoParcial = null;

  /**
   * Elemento da Avaliacao
   * @var iElementoAvaliacao
   */
  private $oElementoAvaliacao;

  /**
   * Valor do aproveitamento do aluno para um periodo
   * @var ValorAproveitamentoNota|ValorAproveitamentoParecer|ValorAproveitamentoNivel
   */
  private $oValorAproveitamento;

  /**
   * Define se o Aproveitamento foi superior ao aproveitamento minino
   * configurado para o aproveitamento;
   * @var boolean
   */
  private $lAproveitamentoMinimo = true;

  /**
   * Total de faltas no periodo
   * @var integer
   */
  private $iNumeroFaltas;


  /**
   * Avaliacao está amparada
   * @var boolean
   */
  private $lAmparado = false;

  /**
   * observação lançada para avaliação
   * @var string
   */
  private $sObservacao = '';

  /**
   * string do parecer
   * @var string
   */
  private $sParecer = '';

  /**
   * parecer padronizado do periodo
   */
  private $sParecerPadronizado = '';

  /**
   * Aluno em recuperacao
   * @var bool
   */
  private $lRecuperacao = false;


  /**
   * Variaveis para manter compatibilidade com os calculos
   */
  private $sTipo = 'M';
  private $oEscola;


  public function __construct($iCodigo = null) {

    $this->iCodigo = $iCodigo;
  }

  /**
   * Define o codigo do aproveitamento
   */
  public function setCodigo($iCodigo) {

    $this->iCodigo = $iCodigo;
  }

  /**
   * Retorna o codigo do aproveitamento
   */
  public function getCodigo() {

    return $this->iCodigo;
  }

  /**
   * Define a qual o Diário de Avaliação Disciplina pertence a avaliação
   * @param DiarioProgressaoParcial $oDiarioProgressaoParcial
   */
  public function setDiarioProgressaoParcial( DiarioProgressaoParcial $oDiario ) {
    $this->oDiarioProgressaoParcial = $oDiario;
  }

  /**
   * Retorna o Diário da progressão,
   *
   *  @todo verificar necessidade de manter este nome
   *
   *  -->  nome do metodo mantido igual ao da classe AvaliacaoAproveitamento para manter compatibilidade
   *
   * @return DiarioProgressaoParcial $oDiarioProgressaoParcial
   */
  public function getDiarioAvaliacaoDisciplina () {
    return $this->oDiarioProgressaoParcial;
  }

  /**
   * Define o periodo de alaviacao
   *
   * @param IElementoAvaliacao $oElementoAvaliacao
   */
  public function setElementoAvaliacao(IElementoAvaliacao $oElementoAvaliacao) {

    $this->oElementoAvaliacao = $oElementoAvaliacao;
  }

  /**
   * Retorna o periodo de alaviacao
   * @return AvaliacaoPeriodica|ResultadoAvaliacao
   */
  public function getElementoAvaliacao() {

    return $this->oElementoAvaliacao;
  }

  /**
   * Define numero de faltas do Aluno
   * @param integer $iNumeroFaltas
   */
  public function setNumeroFaltas($iNumeroFaltas) {

    $this->iNumeroFaltas = $iNumeroFaltas;
  }

  /**
   * Retorna o numero de faltas do Aluno
   */
  public function getNumeroFaltas() {

    return $this->iNumeroFaltas;
  }

  /**
   * Define o parecer padronizado do aluno
   * @param string $sParecer texto do parecer
   */
  public function setParecerPadronizado($sParecer = '') {
    $this->sParecerPadronizado = $sParecer;
  }

  /**
   * Retorna o texto do paracer padronizado
   * @return string
   */
  public function getParecerPadronizado() {
    return $this->sParecerPadronizado;
  }

  /**
   * Define se o aluno está amparado no período
   * @param boolean $lAmparado true para amparado
   */
  public function setAmparado($lAmparado) {
    $this->lAmparado = $lAmparado;
  }

  /**
   * Verifica se o aluno está amparado no período
   * @return bool
   */
  public function isAmparado() {
    return $this->lAmparado;
  }

   /**
   * seta a observação
   * @param string $sObservacao
   */
  public function setObservacao($sObservacao) {
    $this->sObservacao = $sObservacao;
  }

  /**
   * retorna a observação
   * @return string
   */
  public function getObservacao() {
    return $this->sObservacao;
  }

  /**
   * Retorna a string do parecer
   * @return string
   */
  public function getParecer() {

    return $this->sParecer;
  }

  /**
   * Seta a string do parecer
   * @param string $sParecer
   */
  public function setParecer($sParecer = '') {

    $this->sParecer = $sParecer;
  }

  /**
   * Define valor do aproveitamento do aluno para o elemento
   * @param ValorAproveitamento $oValorAproveitamento
   */
  public function setValorAproveitamento(ValorAproveitamento $oValorAproveitamento) {

    $this->oValorAproveitamento = $oValorAproveitamento;
  }

  /**
   * Return valor do aproveitamento do aluno para o elemento
   * @return ValorAproveitamentoNota|ValorAproveitamentoParecer|ValorAproveitamentoNivel
   */
  public function getValorAproveitamento() {
    return $this->oValorAproveitamento;
  }

  /**
   * Define se o aproveitamento tem um valor acima do aproveitamento Minimo
   * @param boolean $lTemAproveitamentoMinimo True ou false
   */
  public function setAproveitamentoMinimo($lTemAproveitamentoMinimo) {

    $this->lAproveitamentoMinimo = $lTemAproveitamentoMinimo;
  }

  /**
   * Retorna se o aluno atingio o aproveitamento minimo para o elemento
   * @return boolean
   */
  public function temAproveitamentoMinimo() {

    return $this->lAproveitamentoMinimo;
  }

  /**
   * Define o aluno como em recuperacao na disciplina
   * @param boolean $lEmRecuperacao Define o aluno como em recuperacao na disciplina
   */
  public function setEmRecuperacao($lEmRecuperacao) {
    $this->lRecuperacao = $lEmRecuperacao;
  }

  /**
   * Verifica se o aluno está em recuperacao no período
   * @return bool aluno em recuperacao
   */
  public function emRecuperacao() {
    return $this->lRecuperacao;
  }

  /**
   * Verdadeiro se tiver um parecer informado
   * @return boolean
   */
  public function hasParecer() {

    if ($this->getParecer() != '' || $this->getParecerPadronizado() != '') {
      return true;
    }
    return false;
  }

  /**
   * Retorna se a avaliação é um resultado
   * @return boolean
   */
  public function isResultado() {

    return $this->getElementoAvaliacao()->isResultado();
  }

  /**
   * Retorna o total número real de faltas
   * @return number
   */
  public function getTotalFaltas() {

    return (int) $this->getNumeroFaltas() - (int) $this->getFaltasAbonadas();
  }


  /**
   * Metodo para manter compatibilidade
   * @return integer
   */
  public function getFaltasAbonadas() {
    return 0;
  }

  /**
   * Metodo para manter compatibilidade
   * @return boolean
   */
  public function isConvertido() {
    return false;
  }

  /**
   * Metodo para manter compatibilidade
   * @return string
   */
  public function isAvaliacaoExterna() {

    return false;
  }

  /**
   * Metodo para manter compatibilidade
   * @param string $sTipo M para escolas da rede.
   */
  public function setTipo($sTipo = 'M') {

    $this->sTipo = $sTipo;
  }

  /**
   * Metodo para manter compatibilidade
   * @return string M Para notas da Escola da Rede
   */
  public function getTipo() {

    return $this->sTipo;
  }

  /**
   * Metodo para manter compatibilidade
   * Define a escola que  codigo da escola que lancou a avaliacao
   * @param IEscola $oEscola
   */
  public function setEscola(IEscola $oEscola) {

    $this->oEscola = $oEscola;
  }

  /**
   * Retorna o codigo da escola que lancou a avaliacao
   * @return iEscola
   */
  public function getEscola() {

    return $this->oEscola;
  }

   /**
   * Retorna a ordem do lancamento da nota
   * @return integer
   */
  public function getOrdemSequencia() {
    return $this->oElementoAvaliacao->getOrdemSequencia();
  }
}