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
require_once(modification( "fpdf151educacao/pdf.php" ));


/**
 * Classe base para os relatórios de Diário de classe
 *
 * @package   educacao
 * @subpackge relatorio
 * @author    Andrio Costa <andrio.costa@dbseller.com.br>
 * @version   $Revision: 1.26 $
 */
class RelatorioDiarioClasseBase extends PDF {

  /**
   * Instância de Turma
   * @var Turma
   */
  protected $oTurma;

  /**
   * Instância de Etapa
   * @var Etapa
   */
  protected $oEtapa;

  /**
   * Instância de uma AvaliacaoPeriodica
   * @var AvaliacaoPeriodica
   */
  protected $oAvaliacaoPeriodica;

  /**
   * Coleção das regências que serão impressas
   * @var Regencia[]
   */
  protected $aRegencias;

  /**
   * Controla se devemos exibir a coluna de faltas
   * @var bool
   */
  protected $lExibirFaltas = false;

  /**
   * Controla se devemos exibir alunos que trocaram de turma no relatório
   * @var bool
   */
  protected $lExibirTrocaTurma     = false;

  /**
   * Controla se devemos exibir a coluna de avaliação
   * @var bool
   */
  protected $lExibirAvaliacao      = false;

  /**
   * Se false não exibe a descrição do período de avaliação no subcabeçalho
   * @var bool
   */
  protected $lExibirLinhaDataPeriodo = true;

  /**
   * Controla se devemos exibir a data do período de avaliação informado.
   * Se false exibe o nome do período de avaliação seguido de __/__/____
   * @var bool
   */
  protected $lExibirDataPeriodo    = true;

  /**
   * Controla se devemos exibir os pontos na grade
   * @var bool
   */
  protected $lExibirPontos         = true;

  /**
   * Controla a exibição da idade do aluno ao lado da coluna nome
   * @var bool
   */
  protected $lExibirIdade = false;

  /**
   * Controla a exibição da etapa em que o aluno esta matriculado aluno ao lado da coluna nome
   * @var bool
   */
  protected $lExibirEtapa = false;

  /**
   * Controla se devemos exibir somente alunos matriculados.
   * Se false traz todas outras situações, exceto Troca de Turma, que é controlada pelo parâmetro $lExibirTrocaTurma
   * @var bool
   */
  protected $lSomenteMatriculados  = true;

  /**
   * Controla como devemos buscar as informações da grade.
   * @exemple true  - imprime a grade em branco, sem se preocupar com as faltas do aluno
   *          false - valida se o aluno teve falta em cada dia do periodo de avaliação, imprimindo um "f" na grade.
   *                  imprimir somente os dias de aula da disciplina selecionada para cada período de aula
   * @var bool
   */
  protected $lRegistroManual  = true;

  /**
   * Sempre quando $lRegistroManual for true, devemos considerar como foi informado o parâmetro $lInformarDiasLetivos
   * @exemple true  - calcula o número de colunas de acordo com os dias de aula do período de avaliação
   *          false - calcula o número de colunas de acordo com o parâmetro $iDiasLetivos
   * @var bool
   */
  protected $lInformarDiasLetivos  = true;

  /**
   * Define se devemos tratar o dados como turma globalizada
   * @var bool
   */
  protected $lTurmaGlobalizada = false;

  /**
   * Usada no cabeçalho do relatório, se true irá apresentar ambas etapas
   * @var bool
   */
  protected $lTurmaMultEtapa = false;

  /**
   * Quando selecionado Turma globalizada, temos que imprimir na grade as disciplinas que controlam avaliação.
   * @var Regencia[]
   */
  private $aRegenciasGlobalizadasQueControlamAvaliacao = array();

  /**
   * Regencia Atual na que a turma se encontra
   * @var Regencia
   */
  protected $oRegenciaAtual;

  /**
   * Usado para calcular o número de colunas da grade sempre que:
   *   $lRegistroManual = true e $lInformarDiasLetivos = false
   * @var int
   */
  protected $iDiasLetivos;

  /**
   * Lista dos alunos organizados pela disciplina e página
   * aAlunosOrganizados[codigoRegencia][pagina][] alunos{}
   *
   * @var array
   */
  protected $aAlunosOrganizados = array();

  /**
   * Identifica se o período selecionado é uma Recuperação
   * @var boolean
   */
  protected $lPeriodoDeRecuperacao = false;

  /** **************************************** *****************************
   * As variáveis abaixo são pré configurações para impressão do relatório *
   ** *************************************** **************************** */

  protected $sTituloColunaNome        = "Nome  do Aluno";
  protected $iLarguraColunaNumero     = 5;
  protected $iLarguraColunaPadrao     = 5;
  protected $iLarguraColunaNome       = 60;
  protected $iNumeroColunasAvaliacao  = 4;
  protected $iLarguraPagina           = 281;
  protected $iNumeroAlunosPagina      = 35;
  protected $iTamanhoFonteGrade       = 6;
  protected $iNumeroMinimoColunaFalta = 30;

  protected $iLarguraColunaGrade;

  /**
   * Situações de Transferencia
   * @var array
   */
  protected $aSituacaoTransferido = array('TRANSFERIDO FORA', 'TRANSFERIDO REDE');
  /**
   * Matriculas de Alunos na turma
   * @var Matricula[]
   */
  protected $aMatriculas = array();


  /**
   * Identifica, em uma turma de turno INTEGRAL, a possíbilidade de matricular alunos no turno de referencia
   * @var boolean
   */
  protected $lPossuiMatriculaPorTurnoReferencia = false;

  /**
   * Contém uma estrutura organizada dos cabeçalho
   * @var array
   */
  protected $aEstruturaCabecalho =array();

  protected $nomecalendario;

  protected $transferencia2;

  protected $colunasImpressas = 0;
  protected $colunasImpressasCab = 0;
  protected $NumeColl = 0;
  protected $NaoExibePonto = false;
  protected $codDisciplina;
  protected $mesext;
  protected $impMeses = 0 ;
  //protected $pagina = 1;
  public function __construct( Turma $oTurma, Etapa $oEtapa, AvaliacaoPeriodica $oAvaliacaoPeriodica ) {
    parent::fpdf('L');


    $this->oTurma = $oTurma;

    $lEnsinoInfantil = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->isInfantil();
    if ( $lEnsinoInfantil && $oTurma->getTurno()->isIntegral() ) {
      $this->lPossuiMatriculaPorTurnoReferencia = true;
    }

    $this->oEtapa              = $oEtapa;
    $this->oAvaliacaoPeriodica = $oAvaliacaoPeriodica;
    $this->aMatriculas         = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);
    $this->SetMargins(8, 10);
    //$this->SetAutoPageBreak(true, 10);
    $this->SetAutoPageBreak(true, 2);
    $this->imprime_rodape = false;


    if ( $this->oAvaliacaoPeriodica->isRecuperacao() ){
      $this->lPeriodoDeRecuperacao = true;
    }
    $this->nomecalendario = $this->oTurma->getCalendario()->getDescricao();

  }

  public function testa($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
  }

  public function trataNome($nome){
    $tratanome = explode(" ", mb_strtolower($nome));
      $nometratado = "";
      foreach ($tratanome as $particula) {
        if(strlen($particula) > 2){
          $nometratado .= ucfirst($particula) . " ";
        }else{
          $nometratado .= $particula . " ";
        }
      }
      $nometratado = trim($nometratado);
      $nometratado = mb_strtoupper(mb_substr($nometratado, 0, 1)).mb_substr($nometratado, 1);
      return $nometratado;
  }

  public function retornaMesExtenso($mes){
    if($mes == "01"){$mesextenso = "JANEIRO";}
    if($mes == "02"){$mesextenso = "FEVEREIRO";}
    if($mes == "03"){
      $mesextenso = "MARÇO";
    }
    if($mes == "04"){
      $mesextenso = "ABRIL";
    }

    if($mes == "05"){
      $mesextenso = "MAIO";
    }

    if($mes == "06"){
      $mesextenso = "JUNHO";
    }

    if($mes == "07"){
      $mesextenso = "JULHO";
    }

    if($mes == "08"){
      $mesextenso = "AGOSTO";
    }

    if($mes == "09"){
      $mesextenso = "SETEMBRO";
    }

    if($mes == "10"){
      $mesextenso = "OUTUBRO";
    }

    if($mes == "11"){
      $mesextenso = "NOVEMBRO";
    }

    if($mes == "12"){
      $mesextenso = "DEZEMBRO";
    }

    if($mes == "0"){
      $mesextenso = "DEZEMBRO";
    }

    return $mesextenso;
  }

  public function retornaDataTransferencia($codmatricula){
    $sql = pg_query("select ed103_d_data as data, ed102_d_data as data2, 'TRANSFERÊNCIA REDE' as tipotransf FROM transfescolarede inner join escola on escola.ed18_i_codigo = transfescolarede.ed103_i_escolaorigem inner join censouf on censouf.ed260_i_codigo = escola.ed18_i_censouf inner join censomunic on censomunic.ed261_i_codigo = escola.ed18_i_censomunic inner join matricula on matricula.ed60_i_codigo = transfescolarede.ed103_i_matricula inner join atestvaga on atestvaga.ed102_i_codigo = transfescolarede.ed103_i_atestvaga inner join escola as escoladestino on escoladestino.ed18_i_codigo = atestvaga.ed102_i_escola inner join censouf as censoufdestino on censoufdestino.ed260_i_codigo = escoladestino.ed18_i_censouf inner join censomunic as censomunicdestino on censomunicdestino.ed261_i_codigo = escoladestino.ed18_i_censomunic inner join aluno on aluno.ed47_i_codigo = atestvaga.ed102_i_aluno WHERE ed47_i_codigo = {$codmatricula} UNION select ed104_d_data as data, 'TRANSFERÊNCIA FORA' as tipotransf FROM transfescolafora inner join escola on escola.ed18_i_codigo = transfescolafora.ed104_i_escolaorigem inner join censouf on censouf.ed260_i_codigo = escola.ed18_i_censouf inner join censomunic on censomunic.ed261_i_codigo = escola.ed18_i_censomunic inner join aluno on aluno.ed47_i_codigo = transfescolafora.ed104_i_aluno inner join escolaproc on escolaproc.ed82_i_codigo = transfescolafora.ed104_i_escoladestino left join censouf as dd on dd.ed260_i_codigo = escolaproc.ed82_i_censouf left join censomunic as ss on ss.ed261_i_codigo = escolaproc.ed82_i_censomunic where ed47_i_codigo = {$codmatricula}");
    $resultado = pg_fetch_all($sql);
    if($resultado[0]["data2"]){
      return implode("/", array_reverse(explode("-", $resultado[0]["data2"])));
    }

    if($resultado[0]["data"]){
      return implode("/", array_reverse(explode("-", $resultado[0]["data"])));
    }

    return "";
  }

/**
 * Autor: Uemerson Santana
 * Demanda: 17795
 * Data: 15/09/2025
 */
  public function retornaDataTransferencia2($codmatricula){
    $sqla = "SELECT
	         COALESCE(to_char(alunotransfturma.ed69_d_datatransf,'DD/MM/YYYY'), to_char(ed60_d_datasaida,'DD/MM/YYYY')) as datasaida,
			 ed60_i_codigo,
			 ed60_i_turma,
			 ed60_matricula
			 FROM matricula
			 LEFT JOIN alunotransfturma ON alunotransfturma.ed69_i_matricula = matricula.ed60_i_codigo
			 WHERE matricula.ed60_i_codigo = {$codmatricula}";

	$sql = pg_query($sqla);
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["datasaida"];
  }  public function retornaDataDesistencia($codmatricula){
// Divaldo 23/1/2024 - demanda 16792
// Função criada para não colocar * ou F em alunos desistentes
// chamada na linha 1576,
// verifica a situação do aluno e caso seja DESISTENTE, verifica a data da desistencia
// e para de marcar presença ou falta

    $sqla = "SELECT
	         COALESCE(to_char(alunotransfturma.ed69_d_datatransf,'DD/MM/YYYY'), to_char(ed60_d_datasaida,'DD/MM/YYYY')) as datasaida,
			 ed60_i_codigo,
			 ed60_i_turma,
			 ed60_matricula
			 FROM matricula
			 LEFT JOIN alunotransfturma ON alunotransfturma.ed69_i_matricula = matricula.ed60_i_codigo
			 WHERE matricula.ed60_i_codigo = {$codmatricula}";


	$sql = pg_query($sqla);
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["datasaida"];
  }

  public function buscaobs($calendario, $turma, $periodo, $disciplina2){
  $sql = pg_query("SELECT obs FROM regocorr WHERE calendario = {$calendario} AND turma = {$turma} AND periodo = {$periodo} AND disciplina = {$disciplina2}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["obs"];
}
  public function buscaobs2($calendario, $turma, $periodo, $disciplina2){
  $sql = pg_query("SELECT obs2 FROM regocorr WHERE calendario = {$calendario} AND turma = {$turma} AND periodo = {$periodo} AND disciplina = {$disciplina2}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["obs2"];
}

public function faltaPorPeriodo($aluno, $regencia, $inicio, $fim){
  $sql = pg_query("SELECT ed58_i_periodo, ed300_datalancamento, ed301_sequencial from diarioclassealunofalta inner join diarioclasseregenciahorario on diarioclasseregenciahorario.ed302_sequencial = diarioclassealunofalta.ed301_diarioclasseregenciahorario inner join aluno on aluno.ed47_i_codigo = diarioclassealunofalta.ed301_aluno inner join diarioclasse on diarioclasse.ed300_sequencial = diarioclasseregenciahorario.ed302_diarioclasse inner join regenciahorario on regenciahorario.ed58_i_codigo = diarioclasseregenciahorario.ed302_regenciahorario where ed58_i_regencia = {$regencia} and ed301_aluno = {$aluno} and ed300_datalancamento between '{$inicio}' and '{$fim}' order by ed300_datalancamento, ed58_i_periodo");
  $resultado = pg_fetch_all($sql);
  return ($resultado) ? count($resultado) : "0";
}

  /**
   * Adiciona uma regência para impressão
   * @param Regencia $oRegencia
   */
  public function adicionarRegencias( Regencia $oRegencia ) {
    $this->aRegencias[] = $oRegencia;
  }

  /**
   * Define número de colunas da grade
   * @param int $iDiasLetivos
   */
  public function setDiasLetivos($iDiasLetivos) {
    $this->iDiasLetivos = $iDiasLetivos;
  }

  /**
   * Define se devemos exibir a data do período de avaliação informado.
   * @param boolean $lExibirDataPeriodo
   */
  public function setExibirDataPeriodo($lExibirDataPeriodo) {
    $this->lExibirDataPeriodo = $lExibirDataPeriodo;
  }

  /**
   * Define se devemos exibir os pontos na grade
   * @param boolean $lExibirPontos
   */
  public function setExibirPontos($lExibirPontos) {
    $this->lExibirPontos = $lExibirPontos;
  }

  /**
   * Define se devemos exibir alunos que trocaram de turma no relatório
   * @param boolean $lExibirTrocaTurma
   */
  public function setExibirTrocaTurma($lExibirTrocaTurma) {
    $this->lExibirTrocaTurma = $lExibirTrocaTurma;
  }

  /**
   * Define como será calculado as colunas da grade.
   * Só é considerada se $lRegistroManual for TRUE
   * @param boolean $lInformarDiasLetivos
   */
  public function setInformarDiasLetivos($lInformarDiasLetivos) {
    $this->lInformarDiasLetivos = $lInformarDiasLetivos;
  }

  /**
   * Define se devemos buscar as informações do Lançamento da Frequência/Conteúdo ou se será manual
   * @param boolean $lRegistroManual
   */
  public function setRegistroManual($lRegistroManual) {
    $this->lRegistroManual = $lRegistroManual;
  }

  /**
   * Define se será listado apenas alunos com situação MATRICULADO
   * @param boolean $lSomenteMatriculados
   */
  public function setSomenteMatriculados($lSomenteMatriculados) {
    $this->lSomenteMatriculados = $lSomenteMatriculados;
  }

  /**
   * Define a AvaliacaoPeriodica
   * @param AvaliacaoPeriodica $oAvaliacaoPeriodica
   */
  public function setAvaliacaoPeriodica(AvaliacaoPeriodica $oAvaliacaoPeriodica) {
    $this->oAvaliacaoPeriodica = $oAvaliacaoPeriodica;
  }

  /**
   * Define a etapa
   * @param Etapa $oEtapa
   */
  public function setEtapa(Etapa $oEtapa) {
    $this->oEtapa = $oEtapa;
  }

  /**
   * Define a turma
   * @param Turma $oTurma
   */
  public function setTurma(Turma $oTurma) {
    $this->oTurma = $oTurma;
  }

  /**
   * Atribui a Regencia atual da turma a propriedade oRegenciaAtual
   * @param Regencia $oRegencia
   */
  public function setRegenciaAtual( Regencia $oRegencia ) {
    $this->oRegenciaAtual = $oRegencia;
  }

  /**
   * Diz para o Diario de Classe que devemos calcular o modelo como disciplina globalizada
   * @param $lTurmaGlobalizada
   */
  public function setTurmaGlobalizada( $lTurmaGlobalizada ) {
    $this->lTurmaGlobalizada= $lTurmaGlobalizada;
  }

  /**
   * Define se devemos exibir a descrição do período de avaliação no subcabeçalho
   * @param $lExibirLinhaDataPeriodo
   */
  public function setExibirLinhaDataPeriodo( $lExibirLinhaDataPeriodo ) {
    $this->lExibirLinhaDataPeriodo = $lExibirLinhaDataPeriodo;
  }

  /**
   * Define se devemos exibir a idade do aluno ao lado do nome do aluno
   * @param bool $lExibirIdade
   */
  protected function setExibirIdade( $lExibirIdade ) {
    $this->lExibirIdade = $lExibirIdade;
  }


  /**
   * Sobreescreve o método Header do model PDF para que monte um cabeçalho para os novos diarios de classe
   */
  public function Header() {

    if($_GET["mcapa"] == "sim" && $this->PageNo() == 1){
      $xnomecalendario = $this->nomecalendario;
      //var_dump($xnomecalendario); die("Confere");

      if($xnomecalendario == "EDUCAÇÃO INFANTIL"){
        $this->setY(10);
        $this->SetFont('Arial', 'B', '16');
        $this->Cell(282, 8, "DIÁRIO DE CLASSE", 0, 1, 'C');
        $this->Ln();
        $this->SetFont('Arial', 'B', '28');
        $this->Cell(282, 8, "EDUCAÇÃO INFANTIL", 0, 1, 'C');
      }elseif($xnomecalendario == "EN FUN ANOS INICIAIS"){
        $this->setY(10);
        $this->SetFont('Arial', 'B', '16');
        $this->Cell(282, 6, "DIÁRIO DE CLASSE", 0, 1, 'C');
        $this->Ln();
        $this->SetFont('Arial', 'B', '28');
        $this->Cell(282, 12, "ENSINO FUNDAMENTAL", 0, 1, 'C');
        $this->Cell(282, 8, "- 1º ao 5º Ano - ", 0, 1, 'C');
      }elseif($xnomecalendario == "EN FUN ANOS FINAIS"){
        $this->setY(10);
        $this->SetFont('Arial', 'B', '16');
        $this->Cell(282, 6, "DIÁRIO DE CLASSE", 0, 1, 'C');
        $this->Ln();
        $this->SetFont('Arial', 'B', '28');
        $this->Cell(282, 12, "ENSINO FUNDAMENTAL", 0, 1, 'C');
        $this->Cell(282, 8, "- 6º ao 9º Ano - ", 0, 1, 'C');
      }elseif(substr($xnomecalendario,0,11) == "ANOS FINAIS"){
        $this->setY(10);
        $this->SetFont('Arial', 'B', '16');
        $this->Cell(282, 6, "DIÁRIO DE CLASSE", 0, 1, 'C');
        $this->Ln();
        $this->SetFont('Arial', 'B', '28');
        $this->Cell(282, 12, "ENSINO FUNDAMENTAL", 0, 1, 'C');
        $this->Cell(282, 8, "- 6º ao 9º Ano - ", 0, 1, 'C');
      }

      if($xnomecalendario == "EN FUN ANOS FINAIS"){
          $this->Image("imagens/files/bvr2.png",120,45, 60, 60);
          $this->setY(105);
          $this->SetFont('Arial', 'B', '14');
          $this->Cell(282, 8, "Prefeitura Municipal de Volta Redonda", 0, 1, 'C');
          $this->SetFont('Arial', '', '14');
          $this->Cell(282, 8, "Secretaria Municipal de Educação", 0, 1, 'C');
          $this->Ln(0.5);

          $this->Cell(282, 8, $this->oTurma->getEscola()->getNome(), 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Estabelecimento", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();

          $xDocente = "";
          $contap   = 0;
          foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
            if($contap>0){
              $xDocente .= "-";
            }
            $xDocente .= $oDocente->getNome();
            $contap++;
          }
          $docentes = explode("-",$xDocente);

          $this->Cell(282, 8, $docentes[0], 0, 1, 'C');
          $this->Cell(282, 8, $docentes[1], 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Nome do(s) Professor(es)", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();

          $componentecurricular = "";
          $this->Cell(282, 8, $componentecurricular, 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Componente Curricular", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();

          $this->Cell(282, 8, "Turma: ".trim($this->oTurma->getDescricao())."     Turno: ".trim($this->oTurma->getTurno()->getDescricao())."     Ano Letivo: " . $this->oTurma->getCalendario()->getAnoExecucao(), 0, 1, 'C');

          $this->roundedrect( 8, 6, 280, 200, 2, '', '1234' );
          $this->Ln(10);
        }elseif(substr($xnomecalendario,0,11) == "ANOS FINAIS"){
          //zaqui

          $this->Image("imagens/files/bvr2.png",120,30, 60, 60);
          $this->setY(100);
          $this->SetFont('Arial', 'B', '14');
          $this->Cell(282, 8, "Prefeitura Municipal de Volta Redonda", 0, 1, 'C');
          $this->SetFont('Arial', '', '14');
          $this->Cell(282, 8, "Secretaria Municipal de Educação", 0, 1, 'C');
          $this->Ln();


          $this->Cell(282, 8, $this->oTurma->getEscola()->getNome(), 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Estabelecimento", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();


          $xDocente = "";
          $contap   = 0;
          foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
            if($contap>0){
              $xDocente .= "-";
            }
            $xDocente .= $oDocente->getNome();
            $contap++;
          }
          $docentes = explode("-",$xDocente);

          if(count($docentes > 1)){
            $this->SetFont('Arial', '', '14');
            $this->Cell(282, 6, $docentes[0], 0, 1, 'C');
            $this->Cell(282, 6, $docentes[1], 0, 1, 'C');
            $this->Cell(71, 1, "", 0, 0, 'C');
            $this->SetFont('Arial', '', '14');
            $this->Cell(140, 6, "Nome do(s) Professor(es)", "T", 0, 'C');
            $this->Cell(71, 7, "", 0, 1, 'C');
            $this->Ln();
          }else{
            $this->Cell(282, 8, $xDocente, 0, 1, 'C');
            $this->Cell(71, 8, "", 0, 0, 'C');
            $this->Cell(140, 8, "Nome do Professor", "T", 0, 'C');
            $this->Cell(71, 8, "", 0, 1, 'C');
            $this->Ln();
          }







          $this->Cell(282, 8, $this->oRegenciaAtual->getDisciplina()->getNomeDisciplina(), 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Disciplina", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();


          $this->Cell(282, 8, "Turma: ".trim($this->oTurma->getDescricao())."     Turno: ".trim($this->oTurma->getTurno()->getDescricao())."     Ano Letivo: " . $this->oTurma->getCalendario()->getAnoExecucao(), 0, 1, 'C');

          $this->roundedrect( 8, 6, 280, 200, 2, '', '1234' );
          $this->Ln(10);


        }elseif(substr($xnomecalendario,0,10) == "EJA FINAIS"){
          //zaqui

          $this->Image("imagens/files/bvr2.png",120,30, 60, 60);
          $this->setY(100);
          $this->SetFont('Arial', 'B', '14');
          $this->Cell(282, 8, "Prefeitura Municipal de Volta Redonda", 0, 1, 'C');
          $this->SetFont('Arial', '', '14');
          $this->Cell(282, 8, "Secretaria Municipal de Educação", 0, 1, 'C');
          $this->Ln();


          $this->Cell(282, 8, $this->oTurma->getEscola()->getNome(), 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Estabelecimento", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();


          $xDocente = "";
          foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
            $xDocente = $oDocente->getNome();
            break;
          }

          $this->Cell(282, 8, $xDocente, 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Nome do Professor", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();


          $this->Cell(282, 8, $this->oRegenciaAtual->getDisciplina()->getNomeDisciplina(), 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Disciplina", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();


          $this->Cell(282, 8, "Turma: ".trim($this->oTurma->getDescricao())."     Turno: ".trim($this->oTurma->getTurno()->getDescricao())."     Ano Letivo: " . $this->oTurma->getCalendario()->getAnoExecucao(), 0, 1, 'C');

          $this->roundedrect( 8, 6, 280, 200, 2, '', '1234' );
          $this->Ln(10);
        }else{
          $this->Image("imagens/files/bvr2.png",120,45, 60, 60);
          $this->setY(115);
          $this->SetFont('Arial', 'B', '14');
          $this->Cell(282, 8, "Prefeitura Municipal de Volta Redonda", 0, 1, 'C');
          $this->SetFont('Arial', '', '14');
          $this->Cell(282, 8, "Secretaria Municipal de Educação", 0, 1, 'C');
          $this->Ln();

          $this->Cell(282, 8, $this->oTurma->getEscola()->getNome(), 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Estabelecimento", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();

          $xDocente = "";
          foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
            $xDocente = $oDocente->getNome();
            break;
          }

          $this->Cell(282, 8, $xDocente, 0, 1, 'C');
          $this->Cell(71, 8, "", 0, 0, 'C');
          $this->Cell(140, 8, "Nome do Professor", "T", 0, 'C');
          $this->Cell(71, 8, "", 0, 1, 'C');
          $this->Ln();

          $this->Cell(282, 8, "Turma: ".trim($this->oTurma->getDescricao())."     Turno: ".trim($this->oTurma->getTurno()->getDescricao())."     Ano Letivo: " . $this->oTurma->getCalendario()->getAnoExecucao(), 0, 1, 'C');

          $this->roundedrect( 8, 6, 280, 200, 2, '', '1234' );
          $this->Ln(10);

        }

        return;
    }//if da capa

    $sDocente = "";

    $xDocente2 = "";
    $contap2   = 0;
    foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
      if($contap2>0){
        $xDocente2 .= "-";
      }
      $xDocente2 .= $oDocente->getNome();
      $contap2++;
    }
    $docentes2 = explode("-",$xDocente2);


    $oInstituicao = InstituicaoRepository::getInstituicaoByCodigo(db_getsession("DB_instit"));
    $sImagem      = $oInstituicao->getImagemLogo();

    $this->Image("imagens/files/{$sImagem}",11,9,13);
    foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
      $sDocente = $oDocente->getNome();
      break;
    }

    $iPosicaoX     = 25;
    $iAlturaLinha  = 4;
    $iTamanhoLinha = 120;

    $this->SetFont('Arial', 'B', '7');
    $this->SetXY($iPosicaoX,8);

    $sNomeEscola       = $this->oTurma->getEscola()->getNome();
    $iCodigoReferencia = $this->oTurma->getEscola()->getCodigoReferencia();

    if ( $iCodigoReferencia != null ) {
      $sNomeEscola = "{$iCodigoReferencia} - {$sNomeEscola}";
    }

    $sDepartamento = $this->oTurma->getEscola()->getDepartamento()->getInstituicao()->getDescricao();
    $this->Cell($iTamanhoLinha, $iAlturaLinha, $sDepartamento, 0, 1, 'L');
    $this->SetX($iPosicaoX);
    $this->Cell($iTamanhoLinha, $iAlturaLinha, $sNomeEscola, 0, 1, 'L');

    $this->SetFont('Arial', '', '7');
    $this->SetXY($iPosicaoX, 24);
    $this->Cell($iTamanhoLinha, $iAlturaLinha, "Cidade: {$this->oTurma->getEscola()->getMunicipio()}", 0, 0, 'L');

    $iPosicaoX     = 140;
    $iTamanhoLinha = 90;

    $this->SetXY($iPosicaoX,8);

    $sCurso = $this->oTurma->getBaseCurricular()->getCurso()->getNome();
    $this->Cell($iTamanhoLinha, $iAlturaLinha, "Curso: {$sCurso}", 0, 0, "L");
    $this->Cell($iTamanhoLinha, $iAlturaLinha, "Calendário: {$this->oTurma->getCalendario()->getDescricao()}", 0, 1, "L");

    $this->SetX($iPosicaoX);
    $this->Cell($iTamanhoLinha, $iAlturaLinha, "Turma: {$this->oTurma->getDescricao()}", 0, 0, "L");

    $sEtapa = $this->oEtapa->getNome();
    if ( $this->lTurmaMultEtapa ) {

      $aEtapaTurma = array();
      foreach ($this->oTurma->getEtapas() as $oEtapaTurma ) {
        $aEtapaTurma[] = $oEtapaTurma->getEtapa()->getNome();
      }

      $sEtapa = implode(" / ", $aEtapaTurma);
    }

    $this->Cell($iTamanhoLinha, $iAlturaLinha, "Etapa: {$sEtapa}", 0, 1, "L");

    $this->SetX($iPosicaoX);

    $sPeriodo = $this->oAvaliacaoPeriodica->getPeriodoAvaliacao()->getDescricao();
    $this->Cell($iTamanhoLinha, $iAlturaLinha, "Período: {$sPeriodo}", 0, 0, "L");

    $iTotalAulas = $this->oRegenciaAtual->getTotalDeAulasNoPeriodo( $this->oAvaliacaoPeriodica->getPeriodoAvaliacao() );
    $this->Cell($iTamanhoLinha, $iAlturaLinha, "Aulas Dadas: {$iTotalAulas}", 0, 1, "L");

    $this->SetX($iPosicaoX);

    $sDisciplina   = $this->oRegenciaAtual->getDisciplina()->getNomeDisciplina();
    $this->codDisciplina = $this->oRegenciaAtual->getDisciplina()->getCodigoDisciplinaGeral(); //busca o codigo da disciplina para a impressão da observação por disciplina

    $this->Cell($iTamanhoLinha, $iAlturaLinha, "Disciplina: {$sDisciplina}", 0, 1, "L");
    $this->SetX($iPosicaoX);
    if(count($docentes2) > 1){
      $this->Cell($iTamanhoLinha, $iAlturaLinha, "Regentes: {$docentes2[0]}, {$docentes2[1]}", 0, 1, "L");
    }else{
      $this->Cell($iTamanhoLinha, $iAlturaLinha, "Regente: {$sDocente}", 0, 1, "L");
    }

    $this->roundedrect( 8, 8, 280, 20, 2, '', '1234' );
  }// fim do header *******************************************************************************************************************************************


  /**
   * Retorna uma estrutura com os dados padrão do cabeçalho:
   * -> Colunas :
   *    Nº | Nome Aluno | Dias (separado pelos meses)
   *
   * @return array
   */
  protected function estruturaSubCabecalho() {

    if ( count($this->aEstruturaCabecalho) > 0) {
      return $this->aEstruturaCabecalho;
    }

    $aTipoFrquenciaTurmaGlobalizadas = array('F', 'FA');

    $this->aSubCabecalho = array();
    $aEstrutura          = array();


    foreach ( $this->aRegencias as $oRegencia ) {

      if ( $this->lTurmaGlobalizada  && !in_array($oRegencia->getFrequenciaGlobal(), $aTipoFrquenciaTurmaGlobalizadas) ) {
        continue;
      }

      $oDadosEstrutura = $this->getDadosPadraoSubCabecalho();  // getDadosPadraoSubCabecalho() na linha 504
/*
tdClass Object
(
    [iTamanhoGrade] => 171
    [iLarguraColunaNumero] => 5
    [iLarguraColunaNome] => 60
    [iNumeroColunasVazias] => 0
    [aMeses] => Array
        (
        )

)
*/
      if ( $this->lRegistroManual ) {
	    // recebe os dados (data, etc)

        $aEstrutura[$oRegencia->getCodigo()] = $this->calcularColunaGradeRegistroManual($oDadosEstrutura); // esta funca é chamada na linha 976, $oDadosEstrutura linha 866
      } else {

        $oEstruturaCalculada = $this->calcularColunaGradeRegistroControleFrequencia($oDadosEstrutura, $oRegencia );
        //ERRO RECUPERAçÃO ACIMA
        if ( empty($oEstruturaCalculada) ) {
          continue;
        }
        $aEstrutura[$oRegencia->getCodigo()] = $oEstruturaCalculada;
      }
    }

    $this->aEstruturaCabecalho = $aEstrutura; // $aEstrutura um array que recebe os dados na linha  883
    return $this->aEstruturaCabecalho ;
  }

  /**
   * StdClass com os dados padrão de todos modelos de relatório
   * Definimos a largura das colunas por página
   * @return stdClass
   */
  private function getDadosPadraoSubCabecalho() {

    $oDadosBasicos                       = new stdClass();
    $oDadosBasicos->iTamanhoGrade        = $this->getTamanhoGrade();      // linha 1066
    $oDadosBasicos->iLarguraColunaNumero = $this->iLarguraColunaNumero;   // linha 187 = 5
    $oDadosBasicos->iLarguraColunaNome   = $this->iLarguraColunaNome;     // linha 189 = 60
    $oDadosBasicos->iNumeroColunasVazias = 0;
    $oDadosBasicos->aMeses               = array();

    return $oDadosBasicos;
  }

  /**
   * Calcula a estrutura do SubCabçalho quando informado para controlar por frequência
   * @param          $oDadosEstrutura
   * @param Regencia $oRegencia
   * @return stdClass
   */

  private function calcularColunaGradeRegistroControleFrequencia( $oDadosEstrutura, Regencia $oRegencia ) {
    $oPeriodoAvaliacao = $this->oAvaliacaoPeriodica->getPeriodoAvaliacao();
    //$this->testa($oPeriodoAvaliacao);
    $oGradeHorario     = new GradeHorario($this->oTurma, $this->oEtapa);
    $aDatasLetiva      = $oGradeHorario->getDiasDeAulaDaDisciplinaNoPeriodoDeAvaliacao($oRegencia->getDisciplina(), $oPeriodoAvaliacao);

    //$this->testa($aDatasLetiva);
    //ERRO RECUPERAÇÃO ACIMA
    $aDiasOrganizados = array();

    foreach ($aDatasLetiva as $oDataLetiva) {
        // Para cada periodo temos que repetir o dia letivo
        foreach ($oDataLetiva->aPeriodoAula as $oPeriodoAula) {
			$oDataPeriodo           = new stdClass();
			$oDataPeriodo->oData    = $oDataLetiva->oData;
			$oDataPeriodo->iPeriodo = $oPeriodoAula->getPeriodoEscola()->getCodigo();
			$aDiasOrganizados[]     = $oDataPeriodo;
        }
    }

    /**
     * Se disciplina não tem Grade de horário configurada, retorna null;
     */

    if (count($aDiasOrganizados) == 0) {
      return null;
    }

    $oDadosEstrutura->iNumeroColunas      = count($aDiasOrganizados);
    $oDadosEstrutura->iLarguraCelulaGrade = $oDadosEstrutura->iTamanhoGrade / $oDadosEstrutura->iNumeroColunas;

    if ($oDadosEstrutura->iNumeroColunas < $this->iNumeroMinimoColunaFalta ) {
      $oDadosEstrutura->iLarguraCelulaGrade  = $oDadosEstrutura->iTamanhoGrade / $this->iNumeroMinimoColunaFalta;
      $oDadosEstrutura->iNumeroColunasVazias = $this->iNumeroMinimoColunaFalta - $oDadosEstrutura->iNumeroColunas;
	  $this->NumeColl = $oDadosEstrutura->iNumeroColunas;
    }

    if ($oDadosEstrutura->iLarguraCelulaGrade > 5) {
      $oDadosEstrutura = $this->recalculaLarguraCelulaGrade( $oDadosEstrutura );
    }

    $oDadosEstrutura = $this->organizaDatasSubCabecalho( $aDiasOrganizados, $oDadosEstrutura );

    return $oDadosEstrutura;
  }


  /**
   * Calcula a estrutura do SubCabçalho quando informado para controlar manualmente
   * @param $oDadosEstrutura
   * @return stdClass
   */
  private function calcularColunaGradeRegistroManual( $oDadosEstrutura ) {  // é chamada na linha 883
    $iTamanhoGrade = $oDadosEstrutura->iTamanhoGrade;
    $oDadosEstrutura->iLarguraCelulaGrade = 5;
    $oDadosEstrutura->aMeses = array();
    if ( $this->lInformarDiasLetivos ) {

      $oPeriodoAvaliacao = $this->oAvaliacaoPeriodica->getPeriodoAvaliacao();
      $aDatasCalendario  = $this->oTurma->getCalendario()->getDatasLetivoNoPeriodo( $oPeriodoAvaliacao );

      $aDatasLetivas = array();
      foreach ($aDatasCalendario as $oDataCalendario) {

        $oDataPeriodo           = new stdClass();
        $oDataPeriodo->oData    = $oDataCalendario;
        $oDataPeriodo->iPeriodo = null;
        $aDatasLetivas[]        = $oDataPeriodo;
      }

      $oDadosEstrutura->iNumeroColunas      = count($aDatasLetivas);
      $oDadosEstrutura->iLarguraCelulaGrade = $iTamanhoGrade / $oDadosEstrutura->iNumeroColunas;

      if ($oDadosEstrutura->iLarguraCelulaGrade > 5) {
        $oDadosEstrutura = $this->recalculaLarguraCelulaGrade( $oDadosEstrutura, count($aDatasLetivas) );
      }

      $oDadosEstrutura = $this->organizaDatasSubCabecalho( $aDatasLetivas, $oDadosEstrutura );

    } else {

      $oDadosEstrutura->iNumeroColunas      = $this->iDiasLetivos;
      $oDadosEstrutura->iLarguraCelulaGrade = $iTamanhoGrade / $this->iDiasLetivos;
      $oDadosEstrutura->aMeses              = array();

      if ($oDadosEstrutura->iLarguraCelulaGrade > 5) {
        $oDadosEstrutura = $this->recalculaLarguraCelulaGrade( $oDadosEstrutura );
      }
    }

    return $oDadosEstrutura;
  }

  /**
   * Organiza as Datas do período de avaliação, separando os meses e os dias
   * @param $aDatas           datas do período de avaliacao
   * @param $oDadosEstrutura  estrutura das colunas da página atual
   * @return stdClass
   */
  private function organizaDatasSubCabecalho( $aDatas, $oDadosEstrutura ) {

    foreach ( $aDatas as $oDataPeriodo ) {

      $oDia           = new stdClass();
      $oDia->iDia     = $oDataPeriodo->oData->getDia();
      $oDia->iPeriodo = $oDataPeriodo->iPeriodo;
      if ( !array_key_exists( $oDataPeriodo->oData->getMes(), $oDadosEstrutura->aMeses ) ) {

        $oDias          = new stdClass();
        $oDias->aDias   = array();
        $oDias->sMes    = $oDataPeriodo->oData->getMesExtenso($oDataPeriodo->oData->getMes());
        $oDias->aDias[] = $oDia;
        $oDadosEstrutura->aMeses[$oDataPeriodo->oData->getMes()] = $oDias;
        continue;
      }
      $oDadosEstrutura->aMeses[$oDataPeriodo->oData->getMes()]->aDias[] = $oDia;
    }

    return $oDadosEstrutura;
  }

  /**
   * Recalcula o tamanho da largura das celulas da grade para um tamanho máximo de 5 pt.
   * O excedente é colocado na coluna nome
   * @param $oDadosEstrutura
   * @return stdClass
   */
  protected function recalculaLarguraCelulaGrade( $oDadosEstrutura ) {

    $iSobra = $oDadosEstrutura->iLarguraCelulaGrade - 5;

    $oDadosEstrutura->iLarguraCelulaGrade = 5;
    $oDadosEstrutura->iLarguraColunaNome += $iSobra * ($oDadosEstrutura->iNumeroColunas + $oDadosEstrutura->iNumeroColunasVazias);
    $oDadosEstrutura->iTamanhoGrade      -= $iSobra * ($oDadosEstrutura->iNumeroColunas + $oDadosEstrutura->iNumeroColunasVazias);

    return $oDadosEstrutura;
  }

  /**
   * Calcula o disponivel para o calculo da grade
   * @return int
   */
  private function getTamanhoGrade() {  // chamada na linha 907

    $iTamanhoDisponivelGrade = $this->iLarguraPagina - $this->iLarguraColunaNome - $this->iLarguraColunaNumero;

    if ( $this->lExibirAvaliacao ) {
      $iTamanhoDisponivelGrade -= ($this->iNumeroColunasAvaliacao * $this->iLarguraColunaPadrao);
    }

    if ( $this->lExibirFaltas ) {
      $iTamanhoDisponivelGrade -= $this->iLarguraColunaPadrao;
    }

    if ( $this->lExibirAvaliacao || $this->lExibirFaltas ) {
      $iTamanhoDisponivelGrade -= $this->iLarguraColunaNumero;
    }

    if ($this->lTurmaGlobalizada ) {
      $iTamanhoDisponivelGrade -= (count( $this->getRegenciasQueControlamAvaliacao() ) * $this->iLarguraColunaPadrao);
    }

    return $iTamanhoDisponivelGrade;
  }

  /**
   * Retorna as disciplinas da turma na etapa selecionada que controlam avaliação
   * @return Regencia[]
   */
  private function getRegenciasQueControlamAvaliacao() {

    if ( count( $this->aRegenciasGlobalizadasQueControlamAvaliacao) == 0) {

      foreach ( $this->oTurma->getDisciplinasPorEtapa($this->oEtapa) as $oRegencia ) {

        if ( $oRegencia->getFrequenciaGlobal() == 'F' ) {
          continue;
        }
        $this->aRegenciasGlobalizadasQueControlamAvaliacao[] = $oRegencia;
      }
    }
    return $this->aRegenciasGlobalizadasQueControlamAvaliacao;
  }

  /**
   * Escreve o subCabeçalho
   * @param $oEstrutura
   */
  protected function escreverSubCabecalho( $oEstrutura, $xcalendario="" ) {
    //AQUI
    $calendario = $xcalendario;
	$conta = 0;
    $this->colunasImpressas = 0;
    $this->AddPage();
    $this->SetFont("arial", 'B', 8);
    $this->AliasNbPages();
    $this->ln(0.3);
    if ( $this->lExibirLinhaDataPeriodo ) {
      $this->imprimeLinhaDescricaoPeriodo();
    }

    $this->SetFont("arial", 'B', 7);
    $this->imprimeLinhaMeses($oEstrutura, $calendario);
    $this->colunasImpressas = 0;

    if($oEstrutura->aMeses["07"]->sMes == "Julho"){
      $this->mesext = 'Julho';
	  $conta = 1;
      $cdias = count($oEstrutura->aMeses["07"]->aDias);
      $this->colunasImpressas = $cdias;
      if($cdias < 5){
        array_push($oEstrutura->aMeses["07"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["07"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["07"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["07"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["07"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["07"]->aDias, array("iDia" => "", "iPeriodo" => ""));
      }
    }

    if($oEstrutura->aMeses["09"]->sMes == "Setembro"){
	  $this->mesext = 'Setembro';
	  $conta = 1;
      $cdias = count($oEstrutura->aMeses["09"]->aDias);
	  $this->colunasImpressas = $cdias;
      if($cdias < 5){
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses["09"]->aDias, array("iDia" => "", "iPeriodo" => ""));
      }
    }
/*
    Nesta situação as colunas em branco dos dias impressos não estavam descontando a quantidade de dias que foram impressos nos meses
	e imprimia alem do limite, busquei aqui a qantidade de dias para fazer o desconto nas colunas em branco a ser impressas
	isso acontecia apenas nos meses abaixo que fecham o total de faltas setembro, novembro e dezembro
*/

    if($oEstrutura->aMeses["12"]->sMes == "Dezembro"){
	  $this->mesext = 'Dezembro';
	  $cdias = count($oEstrutura->aMeses["12"]->aDias)+count($oEstrutura->aMeses["11"]->aDias);
	  $this->colunasImpressas = $cdias;
	  $conta = 1;
    }

    if($oEstrutura->aMeses[0]->sMes == "Dezembro"){
	  $this->mesext = 'Dezembro';
      if($cdias < 5){
        array_push($oEstrutura->aMeses[0]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses[0]->aDias, array("iDia" => "", "iPeriodo" => ""));
        array_push($oEstrutura->aMeses[0]->aDias, array("iDia" => "", "iPeriodo" => ""));
      }

    }

    $this->Cell($oEstrutura->iLarguraColunaNumero, 4, "Nº", 1, 0, "C");
    $this->Cell($oEstrutura->iLarguraColunaNome - 10, 4, $this->sTituloColunaNome, 1, 0, "C");
    $this->Cell($oEstrutura->iLarguraColunaNumero + 10, 4, "Nascimento", 1, 0, "C");
    $this->Cell($oEstrutura->iLarguraColunaNumero + 2, 4, "Sexo", 1, 0, "C");
    $this->Cell($oEstrutura->iLarguraColunaNumero + 8, 4, "Matrícula", 1, 0, "C");
    $this->Cell($oEstrutura->iLarguraColunaNumero + 12, 4, "Transferência", 1, 0, "C");
    //Data de Nascimento
    //Sexo
    //Data da Matrícula
    //Data de Transferência
    //$this->Cell(10, 4, "Dia >", 1, 0, "C");

    if ( count( $oEstrutura->aMeses ) == 0 ) {
      for ($i = 0; $i < $oEstrutura->iNumeroColunas; $i++) {
        $this->Cell($oEstrutura->iLarguraCelulaGrade, 4, "", 1);
      }
      $this->Cell(6, 4, "", 1, 0, "C");
    } else {
      $this->SetFont("arial", '', 6);
	  $conta = 0;
      foreach ($oEstrutura->aMeses as $oMes ) {
        //$this->testa($oMes); die("confere");
        foreach ($oMes->aDias as $oDia) {
 			if(!empty($oDia->iDia))
			{
                 $this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, $oDia->iDia, 1, 0, "C");
			}
        }
        $this->Cell(6, 4, "", 1, 0, "C");
		$conta = 1;
      }
      if(substr($calendario,0,10) !== "EJA FINAIS" && $calendario !== "EJA ANOS FINAIS"){
            $this->escreverColunasFaltasEmBranco($oEstrutura);
      }
      //$this->escreverColunasFaltasEmBranco($oEstrutura);
    }

    //$this->escreverColunaNumeroAluno();
    //$this->escreverColunasAvaliacao(false);
    //$this->escreverColunasDisciplinasGlobalizada(false);
    if($oEstrutura->mostrafalta){
      $this->escreverColunaFalta();
    }
    //$this->escreverColunaFalta();
    $this->ln();
  }

  /**
   * Imprime a descrição do período de avaliação do subCabeçalho
   */
  private function imprimeLinhaDescricaoPeriodo() {

    $sDataInicio = "___/___/_____";
    $sDataFim    = "___/___/_____";

    $oPeriodoAvaliacao = $this->oAvaliacaoPeriodica->getPeriodoAvaliacao();

    if ( $this->lExibirDataPeriodo ) {

      $oPeriodoCalendario = $this->oTurma->getCalendario()->getPeriodoCalendarioPorPeriodoAvaliacao($oPeriodoAvaliacao);
      $sDataInicio        = $oPeriodoCalendario->getDataInicio()->convertTo(DBDate::DATA_PTBR);
      $sDataFim           = $oPeriodoCalendario->getDataTermino()->convertTo(DBDate::DATA_PTBR);
    }

    $sPeriodo  = "FREQUÊNCIA - " .$oPeriodoAvaliacao->getDescricao();
    $sPeriodo .= " {$sDataInicio} a {$sDataFim}";

    $this->SetFont("arial", 'B', 8);
    $this->SetFillColor(245);
    $this->ln(0.5);
    $this->Cell($this->iLarguraPagina + 2, 4, $sPeriodo, '', 1, "C", 1 );
  }

  /**
   * Imprime a linha dos meses do subCabeçalho
   * @param $oEstrutura
   */
  private function imprimeLinhaMeses( $oEstrutura, $calendario="" ) {

    $iPrimeiraColuna = $oEstrutura->iLarguraColunaNumero + $oEstrutura->iLarguraColunaNome - 10;

    $this->Cell($iPrimeiraColuna + 10 + 42, 4, "", 1);
    $konta = 1;
    //AQUI

/*
    Nesta situação as colunas em branco do cabeçalho não estavam descontando a quantidade de dias a ser impressos nos meses
	e imprimia alem do limite, busquei aqui a qantidade de dias para fazer o desconto nas colunas em branco a ser impressas
	isso acontecia apenas nos meses abaixo que fecham o total de faltas
*/
	$this->colunasImpressasCab = 0;
    if($oEstrutura->aMeses["12"]->sMes == "Dezembro"){
	  $this->mesext = 'Dezembro';
	  $cdias = count($oEstrutura->aMeses["12"]->aDias)+count($oEstrutura->aMeses["11"]->aDias);
	  $this->colunasImpressasCab = $cdias;
	  $conta = 1;
    }
    if($oEstrutura->aMeses["09"]->sMes == "Setembro"){
	  $this->mesext = 'Setembro';
	  $cdias = count($oEstrutura->aMeses["09"]->aDias);
	  $this->colunasImpressasCab = $cdias;
	  $conta = 1;
    }
    if($oEstrutura->aMeses["07"]->sMes == "Julho"){
	  $this->mesext = 'Julho';
	  $cdias = count($oEstrutura->aMeses["07"]->aDias);
	  $this->colunasImpressasCab = $cdias;
	  $conta = 1;
    }
    if ( count( $oEstrutura->aMeses ) == 0 ) {
        $this->Cell($oEstrutura->iLarguraCelulaGrade * $oEstrutura->iNumeroColunas, 4, "", 1);
    } else {
      foreach ($oEstrutura->aMeses as $iMes => $oMes ) {
        $iLarguraColunaMes = count($oMes->aDias) * ($oEstrutura->iLarguraCelulaGrade+0.7);

        if($iLarguraColunaMes < 4 || $oMes->sMes == "Julho"){
          $contadiasj = count($oMes->aDias);
          if($contadiasj < 15){
			  if( substr($calendario,0,11) == "ANOS FINAIS" and intval(substr($calendario,12,4) >= 2025 ) )
			  {
				 //$iLarguraColunaMes = 5.2;
				 // é necessario ver porque a largura celula grade nesta situação teve que ficar tão baixo
				 $iLarguraColunaMes = count($oMes->aDias) * ($oEstrutura->iLarguraCelulaGrade-3.26);
			  }else{
                 $iLarguraColunaMes = count($oMes->aDias) * ($oEstrutura->iLarguraCelulaGrade+0.7);
			  }
          }else{
		      $iLarguraColunaMes = count($oMes->aDias) * ($oEstrutura->iLarguraCelulaGrade);
		  }
        }
        if($iLarguraColunaMes < 4 || $oMes->sMes == "Setembro"){
          $contadias = count($oMes->aDias);
          if($contadiasj < 15){
			  if( substr($calendario,0,11) == "ANOS FINAIS" and intval(substr($calendario,12,4) >= 2025 ) )
			  {
				 $iLarguraColunaMes = count($oMes->aDias) * ($oEstrutura->iLarguraCelulaGrade+0.7);
			  }else{
				 $iLarguraColunaMes = count($oMes->aDias) * ($oEstrutura->iLarguraCelulaGrade+0.7);
			  }
          }
        }
        $mesextenso = $oMes->sMes;
        if($mesextenso == "Julho" && ($calendario == "EN FUN ANOS FINAIS" || substr($calendario,0,11) == "ANOS FINAIS")){
          if(substr($calendario,0,11) == "ANOS FINAIS"){
            $this->Cell($iLarguraColunaMes+12, 4, $mesextenso, 1, 0, "C");
          }else{
            $this->Cell($iLarguraColunaMes+0.6, 4, $mesextenso, 1, 0, "C");
          }
        }elseif($mesextenso == "Julho" && substr($calendario,0,12) == "EJA INICIAIS"){
          $contadias = count($oMes->aDias);
          if($contadias == 3){
            $this->Cell($iLarguraColunaMes+11.4, 4, $mesextenso, 1, 0, "C");
          }else{
            $this->Cell($iLarguraColunaMes+12.4, 4, $mesextenso, 1, 0, "C");
          }
        }elseif($mesextenso == "Julho" && (substr($calendario,0,10) == "EJA FINAIS" || $calendario == "EJA ANOS FINAIS")){
          $contadias = count($oMes->aDias);
          if($contadias == 2){
            $this->Cell($iLarguraColunaMes+14.1, 4, $mesextenso, 1, 0, "C");
          }else{
            $this->Cell($iLarguraColunaMes+25.5, 4, $mesextenso, 1, 0, "C");
          }
        }elseif($mesextenso == "Setembro" && (substr($calendario,0,10) == "EJA FINAIS" || $calendario == "EJA ANOS FINAIS")){
          $contadias = count($oMes->aDias);
          if($contadias > 5){
			  if($contadias == 8){
				$this->Cell($iLarguraColunaMes+14.4, 4, $mesextenso, 1, 0, "C");
			  } else{
				$this->Cell($iLarguraColunaMes+25.8, 4, $mesextenso, 1, 0, "C");
			  }
          }else{
            $this->Cell($iLarguraColunaMes+25.5, 4, $mesextenso, 1, 0, "C");
          }
        }else{
          $this->Cell($iLarguraColunaMes, 4, $mesextenso, 1, 0, "C");
        }
        $this->Cell(6, 4, "TF", 1, 0, "C");
      }
      if(substr($calendario,0,10) !== "EJA FINAIS" && $calendario !== "EJA ANOS FINAIS"){
        $this->escreverColunasFaltasEmBranco($oEstrutura);
      }
    }
    if($oEstrutura->mostrafalta){
      $this->escreverColunaFalta("F");
    }
    $this->Ln();
  }

  private function escreverColunasDisciplinasGlobalizada($lImpimeDescricao) {

    if ( $this->lTurmaGlobalizada ) {

      foreach ($this->getRegenciasQueControlamAvaliacao() as $oRegencia ) {

        $sDescricao = "";
        if ( $lImpimeDescricao ) {
          $sDescricao = $oRegencia->getDisciplina()->getAbreviatura();
        }
        $this->SetFont("arial", '', 6);
        $this->Cell ($this->iLarguraColunaPadrao, 4, $sDescricao, 1, 0, 'C');
        $this->SetFont("arial", 'B', 7);
      }
    }
  }

  /**
   * Imprime a coluna de Avaliações
   * @param bool $lTitulo true  escreve o Label título
   *                      false escreve as colunas para lançamento da avaliacao
   */
  private function escreverColunasAvaliacao ( $lTitulo = true ) {

    if ( $this->lExibirAvaliacao ) {

      if ( $lTitulo ) {

        $iLarguraColunaAvaliacao = $this->iNumeroColunasAvaliacao * $this->iLarguraColunaPadrao;
        $this->Cell($iLarguraColunaAvaliacao, 4, "Avaliações", 1, 0, "C");
      } else {

        for ( $i = 0; $i < $this->iNumeroColunasAvaliacao; $i++ ) {
          $this->Cell($this->iLarguraColunaPadrao, 4, "", 1, 0, "C");
        }
      }
    }
  }

  /**
   * Escreve a coluna com o numero do aluno quando necessario
   * @param string $sValor pode ser tando o titulo da coluna numero do aluno como o próprio nº do aluno
   */
  private function escreverColunaNumeroAluno ( $sValor = '') {

    if ( $this->lExibirAvaliacao || $this->lExibirFaltas ) {
      $this->Cell($this->iLarguraColunaPadrao, 4, $sValor, 1, 0, "C");
    }
  }

  /**
   * Escreve a coluna para informar o total de faltas
   * @param string $sTitulo titulo da Coluna
   */
  private function escreverColunaFalta($sTitulo = '', $ftotal = '') {
    $oPeriodoAvaliacao = $this->oAvaliacaoPeriodica->getPeriodoAvaliacao();
    $pfalta = $oPeriodoAvaliacao->getDescricao();
    $pfalta = explode(" ", $pfalta);
    $pfalta = ucfirst(strtolower($pfalta[1]));


    if ( $this->lExibirFaltas ) {
      if($sTitulo == "F"){
        $this->Cell($this->iLarguraColunaPadrao + 30, 4, "Total de Faltas por {$pfalta}", 1, 0, "C");
      }else{
        if($ftotal != ""){
          $this->Cell($this->iLarguraColunaPadrao + 30, 4, $ftotal, 1, 0, "C");
        }else{
          $this->Cell($this->iLarguraColunaPadrao + 30, 4, "", 1, 0, "C");
        }
        //$this->Cell($this->iLarguraColunaPadrao + 30, 4, $sTitulo, 1, 0, "C");

      }
      //$this->Cell($this->iLarguraColunaPadrao, 4, $sTitulo, 1, 0, "C");

    }
  }

  /**
   * Organiza os alunos de acordo com a por disciplina e quebrando as páginas
   * Retorna uma estrutura no seguinte modelo:
   *
   * -> aAlunosOrganizados[codigoRegencia][iPagina][0] = {oAluno, oFaltas}
   *
   * Onde cada página pode ter até 35 alunos
   *
   * @return array
   */
  private function organizarListaAlunos() {

    if ( count($this->aAlunosOrganizados) > 0 ) {
      return $this->aAlunosOrganizados;
    }

    $aMatriculas = $this->aMatriculas;


    foreach ( $this->estruturaSubCabecalho() as $iRegencia => $oEstrutura ) {

      $oRegencia = RegenciaRepository::getRegenciaByCodigo($iRegencia);
      $iPagina   = 0;

      db_inicio_transacao();

      foreach ($aMatriculas as $iIndice => $oMatricula ) {

        // Usado metodo getDisciplinasPorDisciplina por causa das turmas com mais de uma etapa.
        $oDiarioDiscplina   = $oMatricula->getDiarioDeClasse()->getDisciplinasPorDisciplina($oRegencia->getDisciplina());

        /**
         * Se o período for uma recuperação, só deve imprimir os alunos em Recuperação e que estejam matriculados
         */
        if ( $this->lPeriodoDeRecuperacao ) {
            //$this->testa($oDiarioDiscplina); die("Confere");
          if ( !$oDiarioDiscplina->emRecuperacao() ) {
            continue;
          }

          if ( $oMatricula->getSituacao() != 'MATRICULADO' ) {
            continue;
          }
        }

        $oPeriodoAvaliacao  = $this->oAvaliacaoPeriodica->getPeriodoAvaliacao();
        $oAluno             = new stdClass();
        $oAluno->oMatricula = $oMatricula;
        $oAluno->aFaltas    = $oDiarioDiscplina->getFaltasPorPeriodoDeAvaliacao($oPeriodoAvaliacao);

        if ($iIndice % ($this->iNumeroAlunosPagina + 1) == 0) {
          $iPagina++;
        }
        $this->aAlunosOrganizados[$iRegencia][$iPagina][] = $oAluno;
      }
      db_fim_transacao();
    }
    return $this->aAlunosOrganizados;

  }

  /**
   * Retorna uma coleção de alunos de acordo com o codigo da regencia informada
   * @param $iRegencia código da regência
   * @return array
   */
  protected function getAlunos($iRegencia) {

    $oRegencia = RegenciaRepository::getRegenciaByCodigo($iRegencia);
    $aAlunos   = $this->organizarListaAlunos();



    if ( empty($aAlunos[$iRegencia]) ) {

      $sDisciplina = $oRegencia->getDisciplina()->getNomeDisciplina();

      $sMsgErro  = "Não existem alunos para serem impressos na disciplina: <b>{$sDisciplina}</b>";
      $sMsgErro .= " no período: <b>" . $this->oAvaliacaoPeriodica->getDescricao();
      $sMsgErro .= "</b>.<br>Remova a disciplina {$sDisciplina} da lista de impressão.";
      throw new Exception( $sMsgErro );
    }

    return $aAlunos[$iRegencia];
  }

  /**
   * Validamos se o aluno atende a configuração dos parâmetros:
   * $lSomenteMatriculados
   * $lExibirTrocaTurma
   *
   * @param $oDadosAluno stclas com os dados do aluno avaliado
   * @return bool
   */
  protected function validaSituacaoAluno($oDadosAluno) {

    if ( ($oDadosAluno->oMatricula->getSituacao() == 'TROCA DE TURMA')
         && !$this->lExibirTrocaTurma) {
      return false;
    } elseif ( $this->lSomenteMatriculados &&
               !in_array($oDadosAluno->oMatricula->getSituacao(), array('TROCA DE TURMA', 'MATRICULADO'))) {
      return false;
    }

    return true;
  }


  /**
   * Escreve os dados dos alunos no diário de classe
   * @param $aAlunos    Lista de alunos de uma regência
   * @param $oEstrutura Dados da estrutuda de uma regência
   */
  protected function escreverCorpo( $aAlunos, $oEstrutura, $coreg="", $nomcalen="") {
    $nomecalendario = $nomcalen;
//    echo "<pre>";
//    print_r($aAlunos);
//    echo "</pre>";

    foreach ( $aAlunos as $aAlunosPagina ) {
      //$this->testa($aAlunosPagina); die("Confere");
      $this->escreverSubCabecalho( $oEstrutura, $nomecalendario );

      $iAlunosImpressos = 0;
      foreach ( $aAlunosPagina as $oDadosAluno ) {

        if ( !$this->validaSituacaoAluno($oDadosAluno) ) {continue; }
//if($oDadosAluno->oMatricula->getNumeroOrdemAluno() != 25){continue;}

        $this->SetFont("arial", '', $this->iTamanhoFonteGrade);
        $iClassificacao = $oDadosAluno->oMatricula->getNumeroOrdemAluno();
        $this->Cell($oEstrutura->iLarguraColunaNumero, 4, $iClassificacao, 1, 0, "C");
        $sNomeAluno = $this->getNomeAluno( $oDadosAluno->oMatricula );
        $this->Cell($oEstrutura->iLarguraColunaNome - 10, 4, $sNomeAluno, 1, 0, "L");
        $dtnascimento = implode("/", array_reverse(explode("-", $oDadosAluno->oMatricula->getAluno()->getDataNascimento())));
        $this->Cell($oEstrutura->iLarguraColunaNumero + 10, 4, $dtnascimento, 1, 0, "C");
        $sexo = $oDadosAluno->oMatricula->getAluno()->getSexo();
        $this->Cell($oEstrutura->iLarguraColunaNumero + 2, 4, $sexo, 1, 0, "C");
        $dtmatricula = date("d/m/Y", $oDadosAluno->oMatricula->getDataMatricula()->getTimeStamp());
        $this->Cell($oEstrutura->iLarguraColunaNumero + 8, 4, $dtmatricula, 1, 0, "C");
//******************************************************************************************************************
		$transferencia2 = '';
        $dttransferencia = $this->retornaDataTransferencia2($oDadosAluno->oMatricula->getCodigo());
        $xsituacao = $oDadosAluno->oMatricula->getSituacao();
		if( $xsituacao == 'DESISTENTE')
		{
// Divaldo 23/1/2024 - demanda 16792 verifica a data da desistencia e deixa em branco a presença
			 $dttransferencia = $this->retornaDataDesistencia($oDadosAluno->oMatricula->getCodigo());
		}
		$this->transferencia2 = $dttransferencia;
		if( $xsituacao <> 'DESISTENTE')
		{
            $this->Cell($oEstrutura->iLarguraColunaNumero + 12, 4, $dttransferencia, 1, 0, "C");
        }else{
			$this->Cell($oEstrutura->iLarguraColunaNumero + 12, 4, "", 1, 0, "C");
		}
        if ( !$this->validaAlunoAmparado($oDadosAluno->oMatricula, $oEstrutura) ) {
          $this->imprimirGradeFaltasAluno($oDadosAluno->aFaltas, $oEstrutura, $xsituacao, $nomecalendario, $oDadosAluno->oMatricula->getAluno()->getCodigoAluno());
        }
//******************************************************************************************************************


        if($oEstrutura->mostrafalta){
          $xaluno = $oDadosAluno->oMatricula->getAluno()->getCodigoAluno();
          $xPeriodoAvaliacao = $this->oAvaliacaoPeriodica->getPeriodoAvaliacao();
          $xPeriodoCalendario = $this->oTurma->getCalendario()->getPeriodoCalendarioPorPeriodoAvaliacao($xPeriodoAvaliacao);
          $xDataInicio        = date("Y-m-d", $xPeriodoCalendario->getDataInicio()->getTimeStamp());
          $xDataFim           = date("Y-m-d", $xPeriodoCalendario->getDataTermino()->getTimeStamp());

          $ttf = $this->faltaPorPeriodo($xaluno, $coreg, $xDataInicio, $xDataFim);
          $this->escreverColunaFalta("", $ttf);

        }
        //$this->escreverColunaFalta();

        $this->ln();
        $iAlunosImpressos ++;
      }


      if ( $iAlunosImpressos < $this->iNumeroAlunosPagina ) {

        for ( $i = $iAlunosImpressos; $i < $this->iNumeroAlunosPagina; $i++) {

          $this->Cell($oEstrutura->iLarguraColunaNumero, 4, "", 1, 0, "C");
          //$this->Cell($oEstrutura->iLarguraColunaNome,   4, "", 1, 0, "L");
          //$this->Cell($oEstrutura->iLarguraColunaNome + 52,   4, "", 1, 0, "L");
          $this->Cell($oEstrutura->iLarguraColunaNome + 42,   4, "", 1, 0, "L");


          $this->imprimirCelulasGradeFaltaSemAlunos( $oEstrutura, $nomecalendario );

          //$this->escreverColunaNumeroAluno();
          //$this->escreverColunasAvaliacao(false);
          //$this->escreverColunasDisciplinasGlobalizada(false);
          if($oEstrutura->mostrafalta){
            $this->escreverColunaFalta();
          }
          //$this->escreverColunaFalta();

          $this->ln();
        }
      }

      $this->escreverAssinatura();
    }

  }

  /**
   * Imprime a Grade de Falta do aluno
   * @param $aFaltas    array com as faltas do aluno
   * @param $oEstrutura estrutura base do subCabeçalho
   */
  private function imprimirGradeFaltasAluno($aFaltas, $oEstrutura, $xsituacao, $nomecalendario="", $codaluno) {

	$NaoExibePonto = false;
    if ( count($oEstrutura->aMeses) == 0) {
      for ($i = 0; $i < $oEstrutura->iNumeroColunas; $i++) {
        $this->exibePontos($oEstrutura->iLarguraCelulaGrade);
        $this->Cell($oEstrutura->iLarguraCelulaGrade, 4, "", 1);
      }
    } else {
      $datatransferencia = "";
      $contmes = 1;

      foreach ($oEstrutura->aMeses as $iMes =>  $oMes ) {
        $tf = 0;
        foreach ($oMes->aDias as $oDia){
          $sFalta = "";
          if ( !$this->lRegistroManual ) {
            foreach ($aFaltas as $oFaltaAluno){

              if (
                   ($oFaltaAluno->getData()->getMes() == $iMes)
                && ($oFaltaAluno->getData()->getDia() == $oDia->iDia)
                && ($oFaltaAluno->getPeriodo() == $oDia->iPeriodo )
               ) {
                $sFalta = "F";
                $tf++;

                break;
              }
            }
          }
          //AQUI99
          //if($xsituacao == "MATRICULADO"){
        /**
         * Uemerson Santana
         * Data: 22/07/2025
         * Demanda: 17578
         */
        $ano_calendario = $this->oTurma->getCalendario()->getAnoExecucao();
		$data = $ano_calendario . '-'.$iMes.'-'.$oDia->iDia;
/*
        $sqlRegencia = "select
		                *
						from
						diarioclasse
						where

						"
*/
        $data1 = strtotime($data);
        $data2 = strtotime(substr($this->transferencia2,6,4).'-'.substr($this->transferencia2,3,2).'-'.substr($this->transferencia2,0,2));
// alteração para não imprimir o ponto de presença quando o aluno for transferido
        if($data2 >0 )
		{
          // imprime
          if($data2 > $data1)
		  {
            if(empty($sFalta)){
              if(!empty($oDia->iDia)){
                if($data2 > $data1 ){
                  $this->exibePontos($oEstrutura->iLarguraCelulaGrade);
			    }
              }
			}
		  }
		}else if(!in_array($xsituacao, $this->aSituacaoTransferido)){
            if ( empty ($sFalta) ) {
              if(!empty($oDia->iDia)){
                $this->exibePontos($oEstrutura->iLarguraCelulaGrade);
              }
              //$this->exibePontos($oEstrutura->iLarguraCelulaGrade);
            }
            //$this->Cell($oEstrutura->iLarguraCelulaGrade, 4, $sFalta, 1, 0, "C");
        }

        if($data2 >0){

            /**
             * Uemerson Santana
             * Data: 15/07/2025
             * Demanda: 17578
             */
		    if($data2 > $data1){
                 $this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, $sFalta, 1, 0, "C");
			}else{
                 $this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, "", 1, 0, "C");
			}
        } else{
			if(!empty($oDia->iDia) && !in_array($xsituacao, $this->aSituacaoTransferido))
			{
		        $this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, $sFalta, 1, 0, "C");
			}else{
				$this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, "", 1, 0, "C");
			}
		}

          /*}else{
            //$this->Cell($oEstrutura->iLarguraCelulaGrade, 4, "", 0, 0, "C");
            $this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, "", 0, 0, "C");
          }*/
        }//foreach ***********************************************************************************************

        $this->Cell(6, 4, ($tf == 0) ? "0" : $tf, 1, 0, "C");
//        $this->Cell(6, 4, "", 1, 0, "C");
        $contmes++;
      }
      /*if($xsituacao != "MATRICULADO"){
        if($oEstrutura->mostrafalta){
          $gx = $this->getX();
          $this->setX(125);
          $this->Cell($oEstrutura->iLarguraCelulaGrade, 4, $xsituacao, 0, 0, "C");
          $this->setX($gx);
        }else{
          $this->Cell($oEstrutura->iLarguraCelulaGrade, 4, "", "L", 0, "C");
          $this->setX(125);
          $this->Cell($oEstrutura->iLarguraCelulaGrade, 4, $xsituacao, 0, 0, "C");
        }
      }*/
      if(substr($nomecalendario,0,10) !== "EJA FINAIS" && $calendario !== "EJA ANOS FINAIS" && $nomecalendario !== "EJA ANOS FINAIS"){
        $this->escreverColunasFaltasEmBranco($oEstrutura);
      }
      //$this->escreverColunasFaltasEmBranco($oEstrutura);
      //AQUI
    }


  }

  /**
   * Imprime as linhas no Diário
   * @param $oEstrutura
   */
  private function imprimirCelulasGradeFaltaSemAlunos ($oEstrutura, $calendario="") {
    $numeroCol = 0;
    if ( count($oEstrutura->aMeses) == 0) {

      for ($i = 0; $i < $oEstrutura->iNumeroColunas; $i++) {
        $this->exibePontos($oEstrutura->iLarguraCelulaGrade);
        $this->Cell($oEstrutura->iLarguraCelulaGrade, 4, "", 1);
      }
    } else {

      foreach ($oEstrutura->aMeses as $iMes =>  $oMes ) {
        foreach ($oMes->aDias as $oDia) {
            //$this->exibePontos($oEstrutura->iLarguraCelulaGrade);  tirei, verificar se precisa
			if( substr($calendario,0,11) == 'ANOS FINAIS')
			{
				if( $this->mesext == 'Julho' or $this->mesext == 'Setembro' or $this->mesext == 'Dezembro')
				{
		 	        if(!empty($oDia->iDia))
					{
						$this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, '', 1, 0, "C");
					}
				}else{
					$this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, '', 1, 0, "C");
				}
            }else{
				$this->Cell($oEstrutura->iLarguraCelulaGrade+0.7, 4, '', 1, 0, "C");
			}
        }
        $this->Cell(6, 4, '', 1, 0, "C");
      }
      if(substr($calendario,0,10) !== "EJA FINAIS" && $calendario !== "EJA ANOS FINAIS"){
        $this->escreverColunasFaltasEmBranco($oEstrutura);
      }
      //$this->escreverColunasFaltasEmBranco($oEstrutura);
    }
  }

  /**
   * Imprime os pontos na grade de faltas
   * @param $iLarguraCelula
   */
  private function exibePontos( $iLarguraCelula) {


    if ($this->lExibirPontos) {

      $iY = $this->getY();
      $iX = $this->getX();

      $this->Setfont('arial','B',12);
      $this->Text($iX + ($iLarguraCelula * 30 / 95), $iY + 2.5 , ".");
      $this->SetFont("arial", '', 6);
    }

  }

  /**
   * Escreve a assinatura padrão dos modelos, com exceção do modelo 3, que sobrescreve o método
   */
  protected function escreverAssinatura() {

    if( $this->pagina == 1)
	{
        $textoobs = $this->buscaobs2($_GET["iCalendario"], $_GET["iTurma"], $_GET["iPeriodo"], $this->codDisciplina);
		$this->pagina++;
	}else{
		$textoobs = $this->buscaobs($_GET["iCalendario"], $_GET["iTurma"], $_GET["iPeriodo"], $this->codDisciplina);
		$this->pagina = 1;
	}


    /**
     * Autor: Uemerson Santana
     * Data: 07/10/2025
     * Demanda: 17874
     */
    // Busca matrículas dos responsáveis (Regente, Assinatura Adicional, Diretor)
    $matriculaRegente = '';
    $matriculaAssAdic = '';
    $matriculaDiretor = '';

    // CGM do Regente a partir da regência atual
    $iCodigoRegenciaAtual = $this->oRegenciaAtual ? $this->oRegenciaAtual->getCodigo() : null;
    if ($iCodigoRegenciaAtual) {
      $sqlRegenteCGM = "SELECT DISTINCT ON (z01_numcgm) z01_numcgm
                         FROM regenciahorario
                         INNER JOIN regencia ON ed58_i_regencia = ed59_i_codigo
                         INNER JOIN rechumano ON ed20_i_codigo = ed58_i_rechumano
                         INNER JOIN rechumanopessoal ON ed284_i_rechumano = ed20_i_codigo
                         INNER JOIN rhpessoal ON rh01_regist = ed284_i_rhpessoal
                         INNER JOIN cgm ON z01_numcgm = rh01_numcgm
                         WHERE ed58_ativo IS TRUE
                           AND ed59_i_codigo = {$iCodigoRegenciaAtual}
                         LIMIT 1";
      $rsRegenteCGM = pg_query($sqlRegenteCGM);
      if ($rsRegenteCGM && pg_num_rows($rsRegenteCGM) > 0) {
        $oCGMRegente = db_utils::fieldsMemory($rsRegenteCGM, 0);
        /**
         * Autor: Uemerson Santana
         * Data: 27/11/2025
         * Demanda: 17982
         * Razão: Corrigida busca da matrícula do regente para filtrar pela escola vinculada através de rechumanoescola,
         *        garantindo que quando o profissional possui múltiplas matrículas, seja retornada a correta vinculada à escola atual.
         *        Removido substr que removia os 2 primeiros dígitos, exibindo agora a matrícula completa.
         */
        $iEscola = db_getsession("DB_coddepto");
        $sqlMatReg = "SELECT ed284_i_rhpessoal as matricula
                      FROM rechumanopessoal
                      INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                      INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                      INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                      WHERE rh01_numcgm = {$oCGMRegente->z01_numcgm}
                        AND rechumanoescola.ed75_i_escola = {$iEscola}
                      LIMIT 1";
        $rsMatReg = pg_query($sqlMatReg);
        if ($rsMatReg && pg_num_rows($rsMatReg) > 0) {
          $oMatReg = db_utils::fieldsMemory($rsMatReg, 0);
          // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
          $matriculaRegente = !empty($oMatReg->matricula) ? $oMatReg->matricula : '';
        }
      }
    }

    /**
     * Autor: Uemerson Santana
     * Data: 27/11/2025
     * Demanda: 17982
     * Razão: Corrigida busca da matrícula da assinatura adicional (supervisor) para filtrar pela escola vinculada através de rechumanoescola,
     *        garantindo que quando o profissional possui múltiplas matrículas, seja retornada a correta vinculada à escola atual.
     *        Removido substr que removia os 2 primeiros dígitos, exibindo agora a matrícula completa.
     */
    // Matrícula da assinatura adicional, quando informada via GET (cgmaa)
    if (isset($_GET["cgmaa"]) && !empty($_GET["cgmaa"])) {
      $cgmAssAdic = $_GET["cgmaa"];
      $iEscola = db_getsession("DB_coddepto");
      $sqlMatAssAdic = "SELECT ed284_i_rhpessoal as matricula
                        FROM rechumanopessoal
                        INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                        INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                        INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                        WHERE rh01_numcgm = {$cgmAssAdic}
                          AND rechumanoescola.ed75_i_escola = {$iEscola}
                        LIMIT 1";
      $rsMatAssAdic = pg_query($sqlMatAssAdic);
      if ($rsMatAssAdic && pg_num_rows($rsMatAssAdic) > 0) {
        $oMatAssAdic = db_utils::fieldsMemory($rsMatAssAdic, 0);
        // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
        $matriculaAssAdic = !empty($oMatAssAdic->matricula) ? $oMatAssAdic->matricula : '';
      }
    }

    /**
     * Autor: Uemerson Santana
     * Data: 27/11/2025
     * Demanda: 17982
     * Razão: Corrigida busca da matrícula do diretor para filtrar pela escola vinculada através de rechumanoescola,
     *        garantindo que quando o profissional possui múltiplas matrículas, seja retornada a correta vinculada à escola atual.
     *        Removido substr que removia os 2 primeiros dígitos, exibindo agora a matrícula completa.
     */
    // Matrícula do Diretor (CGM via escoladiretor)
    $sqlDiretorCGM = "SELECT z01_numcgm
                       FROM rechumano
                       INNER JOIN rechumanopessoal ON ed284_i_rechumano = ed20_i_codigo
                       INNER JOIN rhpessoal ON rh01_regist = ed284_i_rhpessoal
                       INNER JOIN cgm ON z01_numcgm = rh01_numcgm
                       INNER JOIN escoladiretor ON ed254_i_rechumano = ed20_i_codigo
                       WHERE ed254_i_escola = " . db_getsession("DB_coddepto") . "
                       LIMIT 1";
    $rsDiretorCGM = pg_query($sqlDiretorCGM);
    if ($rsDiretorCGM && pg_num_rows($rsDiretorCGM) > 0) {
      $oCGMDiretor = db_utils::fieldsMemory($rsDiretorCGM, 0);
      $iEscola = db_getsession("DB_coddepto");
      $sqlMatDir = "SELECT ed284_i_rhpessoal as matricula
                    FROM rechumanopessoal
                    INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                    INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                    INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                    WHERE rh01_numcgm = {$oCGMDiretor->z01_numcgm}
                      AND rechumanoescola.ed75_i_escola = {$iEscola}
                    LIMIT 1";
      $rsMatDir = pg_query($sqlMatDir);
      if ($rsMatDir && pg_num_rows($rsMatDir) > 0) {
        $oMatDir = db_utils::fieldsMemory($rsMatDir, 0);
        // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
        $matriculaDiretor = !empty($oMatDir->matricula) ? $oMatDir->matricula : '';
      }
    }

    if($_GET["aa"] == "nao"){
		/*
		  $iTamanhoLinha = 140.5;

		  $sTexto = "OBS.:";
		  $this->Cell( $iTamanhoLinha, $iAlturaLinha, $sTexto, 1, 0, "L" );
		  $sTexto = "Encerrado em ____/____/____                    Aulas Previstas: _____                Aulas Dadas: _____";
		  $this->Cell( $iTamanhoLinha, $iAlturaLinha, $sTexto, 1, 1, "L" );
		  $sTexto = "Processado em ____/____/____ POR " . str_repeat("_", 29);
		  $this->Cell( $iTamanhoLinha, $iAlturaLinha, $sTexto, 1, 0, "L" );
		  $sTexto = "Assinatura do professor ____/____/____ POR " . str_repeat("_", 29);
		  $this->Cell( $iTamanhoLinha, $iAlturaLinha, $sTexto, 1, 1, "L" );
		  if ( $this->lPossuiMatriculaPorTurnoReferencia ) {
			$this->SetFont("arial", '', 7);
			$this->Cell( 281, $iAlturaLinha, "Legenda: Alunos matriculados somente em um turno ¹ - Manhã | ² - Tarde | ³ - Noite ", 1, 0, "L" );
		  }
		  $this->SetFont("arial", '', $this->iTamanhoFonteGrade);
		*/
		$sql = pg_query("select 'DIRETOR' as funcao, case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as nome, ed83_c_descr||' n°: '||ed05_c_numero::varchar as descricao,'D' as tipo FROM escoladiretor INNER JOIN turno ON turno.ed15_i_codigo = escoladiretor.ed254_i_turno LEFT JOIN atolegal ON atolegal.ed05_i_codigo = escoladiretor.ed254_i_atolegal LEFT JOIN tipoato ON tipoato.ed83_i_codigo = atolegal.ed05_i_tipoato INNER JOIN rechumano ON rechumano.ed20_i_codigo = escoladiretor.ed254_i_rechumano LEFT JOIN rechumanopessoal ON rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal LEFT JOIN cgm AS cgmrh ON cgmrh.z01_numcgm = rhpessoal.rh01_numcgm LEFT JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN rechumanoativ ON rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo LEFT JOIN atividaderh ON atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade LEFT JOIN rhpessoalmov ON rh02_anousu = 2018 AND rh02_mesusu = 02 AND rh02_regist = rh01_regist AND rh02_instit = 96 LEFT JOIN rhfuncao ON rhfuncao.rh37_funcao = rhpessoal.rh01_funcao AND rh37_instit = rh02_instit LEFT JOIN rechumanocgm ON rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN cgm AS cgmcgm ON cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm where ed254_i_escola = ".db_getsession("DB_coddepto")." AND ed254_c_tipo = 'A' AND ed01_i_funcaoadmin = 2");
		$resultado = pg_fetch_all($sql);
		$ndiretor = $resultado[0]["nome"];

		$xDocente2 = "";
		$contap2   = 0;
		foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
		  if($contap2>0){
			$xDocente2 .= "-";
		  }
		  $xDocente2 .= $oDocente->getNome();
		  $contap2++;
		}
		$docentes2 = explode("-",$xDocente2);


		foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
		  $sDocente = $oDocente->getNome();
		  break;
		}

		if(count($docentes2) > 1){
		  $this->Rect($this->GetX(), $this->GetY(), 282, 24);
	//      $this->Rect($this->GetX()+135, $this->GetY(), 144, 24);
		  $iAlturaLinha  = 4;

		  //$this->Cell(135, $iAlturaLinha, "OBS.:", 1, 0, 'L');
		  $this->Cell(282, $iAlturaLinha, "Encerrado em: ____/____/______", "B", 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 0, 115), 1, 0, 'L');
		  $this->Cell(282, $iAlturaLinha, "", 0, 0, 'L');

		  //$this->Cell(135, $iAlturaLinha, "", 1, 0, 'L');
		  //$this->Cell(144, $iAlturaLinha, "", 0, 1, 'L');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 115, 115), 1, 0, 'L');
		  $this->Cell(70, $iAlturaLinha, "_______________________________________________", 0, 0, 'C');
		  $this->Cell(70, $iAlturaLinha, "_______________________________________________", 0, 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 230, 70), 0, 0, 'L');
		  $this->Cell(141, $iAlturaLinha, $docentes2[0], 0, 0, 'C');
		  $this->Cell(141, $iAlturaLinha, $ndiretor, 0, 1, 'C');
		  //$this->Cell(70, $iAlturaLinha, $docentes2[1], 0, 1, 'C');

		  $this->Cell(282, $iAlturaLinha, '', 1, 0, 'L');
		  $this->Cell(141, $iAlturaLinha, $docentes2[1], 0, 0, 'C');
		  //$this->Cell(70, $iAlturaLinha, "Regentes", 0, 0, 'C');
		  $this->Cell(141, $iAlturaLinha, "Diretor Geral", 0, 1, 'C');

		  //$this->Cell(282, $iAlturaLinha, "", 1, 0, 'L');
          $this->Cell(141, $iAlturaLinha, "Regentes", 0, 0, 'C');
          // Matrículas
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaRegente != '' ? "Matrícula: ".$matriculaRegente : ""), 0, 0, 'C');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaAssAdic != '' ? "Matrícula: ".$matriculaAssAdic : ""), 0, 0, 'C');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaDiretor != '' ? "Matrícula: ".$matriculaDiretor : ""), 0, 1, 'C');

		}else{

		  $this->Rect($this->GetX(), $this->GetY(), 282, 24);
	//      $this->Rect($this->GetX()+135, $this->GetY(), 144, 24);
		  $iAlturaLinha  = 4;

	//      $this->Cell(135, $iAlturaLinha, "OBS.:", 1, 0, 'L');
		  $this->Cell(282, $iAlturaLinha, "Encerrado em: ____/____/______", "B", 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 0, 115), 1, 0, 'L');
		  $this->Cell(282, $iAlturaLinha, "", 0, 1, 'L');

		  //$this->Cell(135, $iAlturaLinha, "", 1, 0, 'L');
		  //$this->Cell(144, $iAlturaLinha, "", 0, 1, 'L');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 115, 115), 1, 0, 'L');
		  $this->Cell(141, $iAlturaLinha, "_______________________________________________", 0, 0, 'C');
		  $this->Cell(141, $iAlturaLinha, "_______________________________________________", 0, 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 230, 70), 0, 0, 'L');
		  $this->Cell(141, $iAlturaLinha, $sDocente, 0, 0, 'C');
		  $this->Cell(141, $iAlturaLinha, $ndiretor, 0, 1, 'C');

//		  $this->Cell(135, $iAlturaLinha, '', 1, 0, 'L');
          $this->Cell(141, $iAlturaLinha, "Regente", 0, 0, 'C');
          $this->Cell(141, $iAlturaLinha, "Diretor Geral", 0, 1, 'C');
          // Matrículas
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(137, $iAlturaLinha, ($matriculaRegente != '' ? "Matrícula: ".$matriculaRegente : ""), 0, 0, 'C');
          $this->Cell(144, $iAlturaLinha, ($matriculaDiretor != '' ? "Matrícula: ".$matriculaDiretor : ""), 0, 1, 'C');

//		  $this->Cell(282, $iAlturaLinha, "", 1, 0, 'L');

		}



    }else{

        $sql = pg_query("select 'DIRETOR' as funcao, case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as nome, ed83_c_descr||' n°: '||ed05_c_numero::varchar as descricao,'D' as tipo FROM escoladiretor INNER JOIN turno ON turno.ed15_i_codigo = escoladiretor.ed254_i_turno LEFT JOIN atolegal ON atolegal.ed05_i_codigo = escoladiretor.ed254_i_atolegal LEFT JOIN tipoato ON tipoato.ed83_i_codigo = atolegal.ed05_i_tipoato INNER JOIN rechumano ON rechumano.ed20_i_codigo = escoladiretor.ed254_i_rechumano LEFT JOIN rechumanopessoal ON rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal LEFT JOIN cgm AS cgmrh ON cgmrh.z01_numcgm = rhpessoal.rh01_numcgm LEFT JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN rechumanoativ ON rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo LEFT JOIN atividaderh ON atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade LEFT JOIN rhpessoalmov ON rh02_anousu = 2018 AND rh02_mesusu = 02 AND rh02_regist = rh01_regist AND rh02_instit = 96 LEFT JOIN rhfuncao ON rhfuncao.rh37_funcao = rhpessoal.rh01_funcao AND rh37_instit = rh02_instit LEFT JOIN rechumanocgm ON rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN cgm AS cgmcgm ON cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm where ed254_i_escola = ".db_getsession("DB_coddepto")." AND ed254_c_tipo = 'A' AND ed01_i_funcaoadmin = 2");
		$resultado = pg_fetch_all($sql);
		$ndiretor = $resultado[0]["nome"];

		foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
		  $sDocente = $oDocente->getNome();
		  break;
		}


		$xDocente2 = "";
		$contap2   = 0;
		foreach ( $this->oRegenciaAtual->getDocentes() as $oDocente) {
		  if($contap2>0){
			$xDocente2 .= "-";
		  }
		  $xDocente2 .= $oDocente->getNome();
		  $contap2++;
		}
		$docentes2 = explode("-",$xDocente2);

		if(count($docentes2) > 1){
		  $this->SetFont("arial", '', 6);
		  $this->Rect($this->GetX(), $this->GetY(), 282, 24);
	//      $this->Rect($this->GetX()+135, $this->GetY(), 144, 24);
		  $iAlturaLinha  = 4;

	//      $this->Cell(135, $iAlturaLinha, "OBS.:", 1, 0, 'L');
		  $this->Cell(282, $iAlturaLinha, "Encerrado em: ____/____/______", "B", 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 0, 125), 1, 0, 'L');
		  $this->Cell(282, $iAlturaLinha, "", 0, 1, 'L');

		  //$this->Cell(135, $iAlturaLinha, "", 1, 0, 'L');
		  //$this->Cell(144, $iAlturaLinha, "", 0, 1, 'L');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 125, 125), 1, 0, 'L');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, "______________________________________", 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, "______________________________________", 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, "______________________________________", 0, 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 250, 125), 0, 0, 'L');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, $docentes2[0], 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, $_GET["aa"], 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, $ndiretor, 0, 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 375, 70), 1, 0, 'L');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  //$this->Cell(45, $iAlturaLinha, "Regente", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, $docentes2[1], 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, $this->trataNome($_GET["at"]), 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, "Diretor Geral", 0, 1, 'C');

	//      $this->Cell(282, $iAlturaLinha, "", 1, 0, 'L');
          $this->Cell(94, $iAlturaLinha, "Regentes", 0, 0, 'C');
          // Matrículas
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaRegente != '' ? "Matrícula: ".$matriculaRegente : ""), 0, 0, 'C');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaAssAdic != '' ? "Matrícula: ".$matriculaAssAdic : ""), 0, 0, 'C');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaDiretor != '' ? "Matrícula: ".$matriculaDiretor : ""), 0, 1, 'C');
		}else{
		  $this->SetFont("arial", '', 6);
		  $this->Rect($this->GetX(), $this->GetY(), 282, 24);
		  //$this->Rect($this->GetX()+135, $this->GetY(), 144, 24);
		  $iAlturaLinha  = 4;

	//      $this->Cell(135, $iAlturaLinha, "OBS.:", 1, 0, 'L');
		  $this->Cell(282, $iAlturaLinha, "Encerrado em: ____/____/______", "B", 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 0, 125), 1, 0, 'L');
		  $this->Cell(282, $iAlturaLinha, "", 0, 1, 'L');

		  //$this->Cell(135, $iAlturaLinha, "", 1, 0, 'L');
		  //$this->Cell(144, $iAlturaLinha, "", 0, 1, 'L');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 125, 125), 1, 0, 'L');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, "______________________________________", 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, "______________________________________", 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, "______________________________________", 0, 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 250, 125), 0, 0, 'L');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, $sDocente, 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, $_GET["aa"], 0, 0, 'C');
		  $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
		  $this->Cell(94, $iAlturaLinha, $ndiretor, 0, 1, 'C');

	//      $this->Cell(135, $iAlturaLinha, substr($textoobs, 375, 70), 1, 0, 'L');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, "Regente", 0, 0, 'C');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, $this->trataNome($_GET["at"]), 0, 0, 'C');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, "Diretor Geral", 0, 1, 'C');
          // Matrículas
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaRegente != '' ? "Matrícula: ".$matriculaRegente : ""), 0, 0, 'C');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaAssAdic != '' ? "Matrícula: ".$matriculaAssAdic : ""), 0, 0, 'C');
          $this->Cell(2, $iAlturaLinha, "", 0, 0, 'C');
          $this->Cell(94, $iAlturaLinha, ($matriculaDiretor != '' ? "Matrícula: ".$matriculaDiretor : ""), 0, 1, 'C');

//		  $this->Cell(282, $iAlturaLinha, "", 1, 0, 'L');
		}


    }


    /*
    $iTamanhoLinha = 140.5;
    $iAlturaLinha  = 5;

    $sTexto = "Entregue em ____/____/____ POR " . str_repeat("_", 31);
    $this->Cell( $iTamanhoLinha, $iAlturaLinha, $sTexto, 1, 0, "L" );
    $sTexto = "Revisado em ____/____/____ POR " . str_repeat("_", 39);
    $this->Cell( $iTamanhoLinha, $iAlturaLinha, $sTexto, 1, 1, "L" );
    $sTexto = "Processado em ____/____/____ POR " . str_repeat("_", 29);
    $this->Cell( $iTamanhoLinha, $iAlturaLinha, $sTexto, 1, 0, "L" );
    $sTexto = "Assinatura do professor ____/____/____ POR " . str_repeat("_", 29);
    $this->Cell( $iTamanhoLinha, $iAlturaLinha, $sTexto, 1, 1, "L" );
    if ( $this->lPossuiMatriculaPorTurnoReferencia ) {
      $this->SetFont("arial", '', 7);
      $this->Cell( 281, $iAlturaLinha, "Legenda: Alunos matriculados somente em um turno ¹ - Manhã | ² - Tarde | ³ - Noite ", 1, 0, "L" );
    }
    $this->SetFont("arial", '', $this->iTamanhoFonteGrade);
    */
  }

  /**
   * Renderiza o documento
   */
  public function escrever() {

    $xnomecalendario = $this->nomecalendario;



    $this->estruturaSubCabecalho();


    if ( count($this->aEstruturaCabecalho) == 0) {
      throw new Exception ("Nenhuma regência(s) selecionada(s) possuem grade de horário.");
    }

    $this->Open();
    if($xnomecalendario == "EN FUN ANOS FINAIS" || substr($xnomecalendario,0,11) == "ANOS FINAIS"){
      foreach ( $this->aEstruturaCabecalho as $iRegencia => $oEstrutura ) {     // e populada na linha 895
        $this->oRegenciaAtual = RegenciaRepository::getRegenciaByCodigo($iRegencia);
        //$this->testa($oEstrutura); die("Caça Novembro");
        $oEstruturaX1 = clone $oEstrutura;
        $oEstruturaX2 = clone $oEstrutura;

        $konta2 = count($oEstruturaX2->aMeses);
		if($konta2 == 2){
		  array_pop($oEstruturaX1->aMeses);
		  array_shift($oEstruturaX2->aMeses);
		}elseif($konta2 == 3 && $oEstruturaX1->aMeses[10]){
		  array_pop($oEstruturaX1->aMeses);
		  array_pop($oEstruturaX1->aMeses);
		  array_shift($oEstruturaX2->aMeses);
		}else{
		  array_pop($oEstruturaX1->aMeses);
		  array_shift($oEstruturaX2->aMeses);
		  array_shift($oEstruturaX2->aMeses);
		}


        //$this->testa($oEstruturaX2->aMeses); die("Confere2");

        $oEstruturaX1->mostrafalta = false;
        $oEstruturaX2->mostrafalta = true;

        if($oEstruturaX2->aMeses[0]){
          if($oEstruturaX2->aMeses[0]->sMes == "Novembro"){
            $oEstruturaX2->aMeses[11] = $oEstruturaX2->aMeses[0];
            unset($oEstruturaX2->aMeses[0]);
          }
        }
        if($oEstruturaX2->aMeses[1]){
          if($oEstruturaX2->aMeses[1]->sMes == "Dezembro"){
            $oEstruturaX2->aMeses[12] = $oEstruturaX2->aMeses[1];
            unset($oEstruturaX2->aMeses[1]);
          }
        }



        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX1, $iRegencia, $xnomecalendario );
        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX2, $iRegencia, $xnomecalendario );

      }//fim do foreach

    }elseif(substr($xnomecalendario,0,12) == "EJA INICIAIS"){
      foreach ( $this->aEstruturaCabecalho as $iRegencia => $oEstrutura ) {
        $this->oRegenciaAtual = RegenciaRepository::getRegenciaByCodigo($iRegencia);
        //$this->testa($oEstrutura); die("Caça Julho");
        $oEstruturaX1 = clone $oEstrutura;
        $oEstruturaX2 = clone $oEstrutura;

        $konta2 = count($oEstruturaX2->aMeses);


        if($konta2 == 2){
          array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }elseif($konta2 == 3 && $oEstruturaX1->aMeses[10]){
          array_pop($oEstruturaX1->aMeses);
          array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }elseif($konta2 == 4 && $oEstruturaX1->aMeses["09"]){
          array_pop($oEstruturaX1->aMeses);
          array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }else{
          array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }

        //$this->testa($oEstruturaX2->aMeses); die("Confere2");

        $oEstruturaX1->mostrafalta = false;
        $oEstruturaX2->mostrafalta = true;



        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX1, $iRegencia, $xnomecalendario );
        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX2, $iRegencia, $xnomecalendario );

      }//fim do foreach

    }elseif(substr($xnomecalendario,0,10) == "EJA FINAIS" || $xnomecalendario == "EJA ANOS FINAIS"){
      foreach ( $this->aEstruturaCabecalho as $iRegencia => $oEstrutura ) {
        $this->oRegenciaAtual = RegenciaRepository::getRegenciaByCodigo($iRegencia);
        //$this->testa($oEstrutura); die("Caça Julho");
        $oEstruturaX1 = clone $oEstrutura;
        $oEstruturaX2 = clone $oEstrutura;

        $konta2 = count($oEstruturaX2->aMeses);


        if($konta2 == 2){
          array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }elseif($konta2 == 3 && $oEstruturaX1->aMeses[10]){
          array_pop($oEstruturaX1->aMeses);
          array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }elseif($konta2 == 4 && $oEstruturaX1->aMeses["09"]){
          array_pop($oEstruturaX1->aMeses);
          array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }else{
          array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }

        //$this->testa($oEstruturaX2->aMeses); die("Confere2");

        $oEstruturaX1->mostrafalta = false;
        $oEstruturaX2->mostrafalta = true;

        if($oEstruturaX2->aMeses[0]){
          if($oEstruturaX2->aMeses[0]->sMes == "Novembro"){
            $oEstruturaX2->aMeses[11] = $oEstruturaX2->aMeses[0];
            unset($oEstruturaX2->aMeses[0]);
          }
        }
        if($oEstruturaX2->aMeses[1]){
          if($oEstruturaX2->aMeses[1]->sMes == "Dezembro"){
            $oEstruturaX2->aMeses[12] = $oEstruturaX2->aMeses[1];
            unset($oEstruturaX2->aMeses[1]);
          }
        }



        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX1, $iRegencia, $xnomecalendario );
        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX2, $iRegencia, $xnomecalendario );

      }//fim do foreach

    }elseif($xnomecalendario == "EDUCAÇÃO INFANTIL" || substr($xnomecalendario,0,12) == "ED. INFANTIL"){

      foreach ( $this->aEstruturaCabecalho as $iRegencia => $oEstrutura ) {
        $this->oRegenciaAtual = RegenciaRepository::getRegenciaByCodigo($iRegencia);

        $oEstruturaX1 = clone $oEstrutura;
        $oEstruturaX2 = clone $oEstrutura;
        $konta = count($oEstruturaX1->aMeses);

        if($konta == 4){
            array_pop($oEstruturaX1->aMeses);
            array_pop($oEstruturaX1->aMeses);
            array_shift($oEstruturaX2->aMeses);
            array_shift($oEstruturaX2->aMeses);
        }else{
          array_pop($oEstruturaX1->aMeses);
          array_pop($oEstruturaX1->aMeses);
          //array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
          array_shift($oEstruturaX2->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }

        $oEstruturaX1->mostrafalta = false;
        $oEstruturaX2->mostrafalta = true;

        if($_GET["mcapa"] == "sim"){
          $this->AddPage();
        }

        if($oEstruturaX2->aMeses[0]){
          if($oEstruturaX2->aMeses[0]->sMes == "Novembro"){
            $oEstruturaX2->aMeses[11] = $oEstruturaX2->aMeses[0];
            unset($oEstruturaX2->aMeses[0]);
          }
        }
        if($oEstruturaX2->aMeses[1]){
          if($oEstruturaX2->aMeses[1]->sMes == "Dezembro"){
            $oEstruturaX2->aMeses[12] = $oEstruturaX2->aMeses[1];
            unset($oEstruturaX2->aMeses[1]);
          }
        }

        //$this->testa($oEstruturaX2->aMeses); die("Confere");

        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX1, $iRegencia );
        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX2, $iRegencia );





      }//fim do foreach
    }elseif($xnomecalendario == "EN FUN ANOS INICIAIS" || substr($xnomecalendario,0,13) == "ANOS INICIAIS"){

      foreach ( $this->aEstruturaCabecalho as $iRegencia => $oEstrutura ) {
        $this->oRegenciaAtual = RegenciaRepository::getRegenciaByCodigo($iRegencia);

        $oEstruturaX1 = clone $oEstrutura;
        $oEstruturaX2 = clone $oEstrutura;
        $konta = count($oEstruturaX1->aMeses);

        if($konta == 4){
            array_pop($oEstruturaX1->aMeses);
            array_pop($oEstruturaX1->aMeses);
            array_shift($oEstruturaX2->aMeses);
            array_shift($oEstruturaX2->aMeses);
        }else{
          array_pop($oEstruturaX1->aMeses);
          array_pop($oEstruturaX1->aMeses);
          //array_pop($oEstruturaX1->aMeses);
          array_shift($oEstruturaX2->aMeses);
          array_shift($oEstruturaX2->aMeses);
          array_shift($oEstruturaX2->aMeses);
        }

        $oEstruturaX1->mostrafalta = false;
        $oEstruturaX2->mostrafalta = true;

        if($_GET["mcapa"] == "sim"){
          //$this->AddPage();
        }

        if($oEstruturaX2->aMeses[0]){
          if($oEstruturaX2->aMeses[0]->sMes == "Novembro"){
            $oEstruturaX2->aMeses[11] = $oEstruturaX2->aMeses[0];
            unset($oEstruturaX2->aMeses[0]);
          }
        }
        if($oEstruturaX2->aMeses[1]){
          if($oEstruturaX2->aMeses[1]->sMes == "Dezembro"){
            $oEstruturaX2->aMeses[12] = $oEstruturaX2->aMeses[1];
            unset($oEstruturaX2->aMeses[1]);
          }
        }

        //$this->testa($oEstruturaX2->aMeses); die("Confere");

        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX1, $iRegencia );
        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstruturaX2, $iRegencia );

      }//fim do foreach
    }else{
      foreach ( $this->aEstruturaCabecalho as $iRegencia => $oEstrutura ) {
        $this->oRegenciaAtual = RegenciaRepository::getRegenciaByCodigo($iRegencia);
        $this->escreverCorpo( $this->getAlunos($iRegencia), $oEstrutura);
      }
    }

    //die("Saída");
    $this->Output();
  }

  /**
   * Imprime a situação do aluno
   * @param $sSituacao     Situação do aluno que esta sendo impresso
   * @param $iTamanhoLinha Tamanho da linha
   */
  protected function imprimeSituacaoAluno( $sSituacao, $iTamanhoLinha ) {

    $this->SetFont("arial", "B", 7);
    $sSituacaoImprimir = $sSituacao;

    if ( !$this->lSomenteMatriculados && in_array($sSituacao, $this->aSituacaoTransferido) ) {
      $sSituacaoImprimir = "TRANSFERIDO";
    }

    $this->Cell($iTamanhoLinha , 4, $sSituacaoImprimir, 1, 0, 'C');
    $this->SetFont("arial", "", $this->iTamanhoFonteGrade);
  }


  /**
   * Quando selecionado modelo de impressão com "Regitro = Frequência/Conteúdo" pode haver um número inferior
   * a 30 dias de avaliação ( Exemplo uma disciplina com apenas um período de avaliação)
   * Para a coluna nome não ocupar metade folha, foi decido que teria um minimo de 30 quadros para lançar faltas
   *
   * Essa função escreve as colunas em branco para lançamento de faltas
   *
   * @param $oEstrutura
   */
  protected function escreverColunasFaltasEmBranco($oEstrutura) {
	$colunas = 0;
/*
   nos meses abaixo não estavam descontando as colunas impressas das colunas vazias
   e imprimiam alem do limite
*/

	if( $this->mesext == 'Setembro' or $this->mesext == 'Dezembro')
	{
		if( $this->colunasImpressas > 0 and $oEstrutura->iNumeroColunasVazias > 0)
		{

			if( substr($this->nomecalendario,0,11) == 'ANOS FINAIS'  )
			{
				if( intval(substr($this->nomecalendario,12,4)) >= 2025  )
				{
					$colunas = ($oEstrutura->iNumeroColunasVazias-$this->colunasImpressas);
				}
			}
		}
		if( $this->colunasImpressasCab > 0 and $oEstrutura->iNumeroColunasVazias > 0)
		{

			if( substr($this->nomecalendario,0,11) == 'ANOS FINAIS'  )
			{
				if( intval(substr($this->nomecalendario,12,4)) >= 2025  )
				{
					$colunas = ($oEstrutura->iNumeroColunasVazias-$this->colunasImpressasCab);
				}
			}
		}
	}

	if( $this->mesext == 'Julho')
	{
		if( $this->colunasImpressasCab > 0 and $oEstrutura->iNumeroColunasVazias > 0)
		{

			if( substr($xnomecalendario,0,13) == "ANOS INICIAIS" )
			{
				if( intval(substr($this->nomecalendario,14,4)) >= 2025  )
				{
					$colunas = ($oEstrutura->iNumeroColunasVazias-$this->colunasImpressasCab);
				}
			}
			if( substr($xnomecalendario,0,12) == "ED. INFANTIL" )
			{
				if( intval(substr($this->nomecalendario,13,4)) >= 2025  )
				{
					$colunas = ($oEstrutura->iNumeroColunasVazias-$this->colunasImpressasCab);
				}
			}
		}
    }
/*
	if( $this->mesext == 'Setembro')
	{

	$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/busca.txt","a+");
	fwrite($arq, $this->colunasImpressasCab.'--'.$oEstrutura->iNumeroColunasVazias);
	fwrite($arq,"\r\n");
	fclose($arq);

//		if( substr($xnomecalendario,0,13) == "ANOS INICIAIS" )
//		{
//			if( intval(substr($this->nomecalendario,14,4)) >= 2025  )
//			{
//				$colunas = ($oEstrutura->iNumeroColunasVazias-$this->colunasImpressasCab);
//			}
//		}
    }
*/
    if( $this->colunasImpressasCab > 0 and $colunas > 0)
	{
	    for ($i = 0; $i <= $colunas; $i++) {
			$this->Cell($oEstrutura->iLarguraCelulaGrade, 4, '', 1, 0, "C");
		}
	    $this->colunasImpressasCab = 0;
	}else{
		if( $colunas >0 )
		{
		  for ($i = 0; $i <= $colunas; $i++) {
			$this->Cell($oEstrutura->iLarguraCelulaGrade, 4, '', 1, 0, "C");
		  }
		}else{

			if ($oEstrutura->iNumeroColunasVazias > 0) {
			  for ($i = 0; $i < $oEstrutura->iNumeroColunasVazias; $i++) {
				$this->Cell($oEstrutura->iLarguraCelulaGrade, 4, '', 1, 0, "C");
			  }
			}
		}
	}
  }


  /**
   * Escreve se o aluno esta amaparado
   * @param Matricula $oMatricula
   * @param $oEstrutura
   * @return bool
   */
  protected function validaAlunoAmparado(Matricula $oMatricula, $oEstrutura) {

    $oAvaliacaoDisciplina = $this->getDiarioAvaliacaoDisciplinaAluno($oMatricula);
    $oAvaliacaoPeriodo    = $oAvaliacaoDisciplina->getAvaliacoesPorOrdem( $this->oAvaliacaoPeriodica->getOrdemSequencia() );

    if ( $oAvaliacaoPeriodo->isAmparado() ) {

      $this->SetFont("arial", "B", 8);
      $this->Cell($oEstrutura->iTamanhoGrade, 4, "AMPARADO", 1, 0, 'C');
      $this->SetFont("arial", "", 7);
      return true;
    }
    return false;
  }

  /**
   * Retorna os dados da avaliação do aluno para a disciplina
   * @param Matricula $oMatricula
   * @return DiarioAvaliacaoDisciplina
   */
  protected function getDiarioAvaliacaoDisciplinaAluno( Matricula $oMatricula) {

    return $oMatricula->getDiarioDeClasse()->getDisciplinasPorDisciplina($this->oRegenciaAtual->getDisciplina());
  }

  /**
   * Retorna o nome do aluno com uma observação se houver
   * @param  Matricula $oMatricula
   * @return string
   */
  protected function getNomeAluno(Matricula $oMatricula) {

    $sNomeAluno = '';

    /*

      Autor: Uemerson Santana
      Demanda: 17338
      Data: 02/06/2025
      Descrição:
      - Se o nome social do aluno for diferente de vazio, utiliza o nome social
      - Se o nome social do aluno for vazio, utiliza o nome
    */
    if ($oMatricula->getAluno()->getNomeSocial() != '') {
      $sNomeAluno = $oMatricula->getAluno()->getNomeSocial();
    } else {
      $sNomeAluno = $oMatricula->getAluno()->getNome();
    }

    if ( $this->lPossuiMatriculaPorTurnoReferencia ) {

      $aTurnosVinculo = $oMatricula->getTurnosVinculados();
      if ( count($aTurnosVinculo) == 1) {

        switch ($aTurnosVinculo[0]->ed336_turnoreferente) {
          case Turno::TURNO_REFERENTE_MANHA:

            $sNomeAluno .= " ¹ ";
            break;
          case Turno::TURNO_REFERENTE_TARDE:

            $sNomeAluno .= " ² ";
            break;
          case Turno::TURNO_REFERENTE_NOITE:

            $sNomeAluno .= " ³ ";
            break;
        }
      }
    }

   return $sNomeAluno;
  }

public function observacao() {
    if( $this->pagina == 1)
	{
        $textoobs = $this->buscaobs2($_GET["iCalendario"], $_GET["iTurma"], $_GET["iPeriodo"], $this->codDisciplina);
		$this->pagina++;
	}else{
		$textoobs = $this->buscaobs($_GET["iCalendario"], $_GET["iTurma"], $_GET["iPeriodo"], $this->codDisciplina);
		$this->pagina = 1;
	}
      $this->Cell(135, $iAlturaLinha, substr($textoobs, 0, 115), 1, 0, 'L');
      $this->Cell(144, $iAlturaLinha, "", 0, 1, 'L');
}

}
