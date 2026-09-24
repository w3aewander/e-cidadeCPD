<?
class RelGradeAprovDependencia extends PDFGradeAproveitamento {

  private $oGradeAproveitamento;

  private $aObservacoes = array();
  
  private $rel;
 
  public $periodoprova = 0; 
  public $buscafaltas2 = 0;
  public $aluno1 = 0;
  /**
   *
   * @param FPDF      $oPdf         instancia do fpdf
   * @param Matricula $oMatricula   Instancia de matricula
   * @param integer   $iLimiteLinha Tamanho máximo que vai ter a linha da tabela no arquivo
   */
  public function __construct(FPDF $oPdf, Matricula $oMatricula, $iLimiteLinha,$rel, $periodo2) {


    $this->oPdf                   = $oPdf;
    $this->oGradeAproveitamento   = new GradeAproveitamentoAluno($oMatricula);
    $this->iLimiteLinha           = $iLimiteLinha;
    $this->lApresentarNotaParcial = $this->oGradeAproveitamento->exibeNotaParcial();
    $this->controleDeFrequencia($oMatricula);
	$this->rel                    = $rel;
	$this->periodoprova           = $periodo2;
	$this->aluno1                 = $oMatricula;

	
  }

public function montarGrade($ed29_c_descr=null, $ed11_c_descr=null, $guardapagina=null,$calendar=null) {
	

}	


}
?>