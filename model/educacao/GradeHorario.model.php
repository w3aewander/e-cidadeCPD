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
 * Grade de horário da turma
 * @package educacao
 * @author Andrio Costa <andrio.costa@dbseller.com.br>
 * @version $Revision: 1.8 $
 */
class GradeHorario
{
    /**
     * Instancia da Turma
     * @var Turma
     */
    private $oTurma;

    /**
     * Instância da Etapa
     * @var Etapa
     */
    private $oEtapa;

    /**
     * Instância do período de aula
     * @var PeriodoAula[]
     */
    private $aPeriodosAula = array();

    private $aLogConflito = array();

    /**
     * Tipo da grade horario
     * PeriodoAula::VINCULAR_PROFESSOR_DISCISPLINA
     * PeriodoAula::GRADE_HORARIO
     * @var integer
     */
    private $tipoGrade;

    /**
     * GradeHorario constructor.
     * @param Turma $oTurma
     * @param Etapa $oEtapa
     * @param bool $lApenasPeriodosAtivos
     * @throws Exception
     */
    public function __construct(Turma $oTurma, Etapa $oEtapa, $lApenasPeriodosAtivos = true)
    {
        $iTurma = $oTurma->getCodigo();
        $iEtapa = $oEtapa->getCodigo();

        if (empty($iTurma) || empty($iEtapa)) {
            throw new ParameterException("Etapa e turma deve ser informada para montar a grade de horário.");
        }

        $this->oEtapa = $oEtapa;
        $this->oTurma = $oTurma;
        $this->aPeriodosAula = $this->buscarPeriodos($lApenasPeriodosAtivos);
		
    }

    /**
     * Retorna a Turma
     * @return Turma
     */
    public function getTurma()
    {
        return $this->oTurma;
    }

    /**
     * Retorna os Períodos de aula da turma e etapa informada
     * @return PeriodoAula[]
     */
    public function getPeriodosAula()
    {
        return $this->aPeriodosAula;
    }

    /**
     * Retorna a Etapa
     * @return Etapa
     */
    public function getOEtapa()
    {
        return $this->oEtapa;
    }

    /**
     * Retorna uma estrutura os dias que uma disciplina tem aula de acordo com o Período de avaliação do calendário
     * da turma.
     *
     * @exemple [ aDatas : [ oData : DBDate,
     *                       aPeriodoAula : [PeriodoAula1, PeriodoAula2 ]
     *                    ]
     *          ]
     *
     * @param Disciplina $oDisciplina
     * @param PeriodoAvaliacao $oPeriodoAvaliacao
     * @return array $aDiasAula[]
     * @throws DBException
     * @throws ParameterException
     */
                
    public function getDiasDeAulaDaDisciplinaNoPeriodoDeAvaliacao(Disciplina $oDisciplina, PeriodoAvaliacao $oPeriodoAvaliacao)
    {              

        $oPeriodoCalendario = $this->oTurma->getCalendario()->getPeriodoCalendarioPorPeriodoAvaliacao($oPeriodoAvaliacao);
		
        $aDiasSemenaComAula = array();

// $this->aPeriodosAula = $this->buscarPeriodos(false);  // aqui busca o periodo duplicaco
// o erro da demanda 16291, acontecia porque a função estava buscando todos periodos, inclusive os inativos
// a funcao funciona private function buscarPeriodos($lSomenteAtivos = true)
// por causa da troca de professores, se o parametro for falso os periodos agora inativos, aparecem tambem e duplica

        // <<< INÍCIO DA CORREÇÃO: Lógica da Disciplina e Sábado >>>
        
        // Pega o código da disciplina atual que está sendo processada
        $iCodigoDisciplinaAtual = $oDisciplina->getCodigoDisciplina();
        
        // Lista das disciplinas que PERMITEM aulas aos sábados
        $aDisciplinasPermitemSabado = array(13, 19);

        $this->aPeriodosAula = $this->buscarPeriodos(false);
        
        foreach ($this->aPeriodosAula as $oPeriodoAula) {
            
            // 1. Filtro original: Ignora horários de outras disciplinas
            if ($oPeriodoAula->getDisciplina()->getCodigoDisciplina() != $iCodigoDisciplinaAtual) {
                continue;
            }

            $iDiaDaSemana = $oPeriodoAula->getDiaSemana();

            // 2. Novo Filtro: A regra de negócio do Sábado
            //    (Padrão PHP: 0=Dom, 1=Seg, 2=Ter, 3=Qua, 4=Qui, 5=Sex, 6=Sab)
            if ($iDiaDaSemana == 6) { // Se for Sábado
                
                // Verificamos se a disciplina NÃO ESTÁ na lista de permissão
                if (!in_array($iCodigoDisciplinaAtual, $aDisciplinasPermitemSabado)) {
                    
                    // Se for sábado E a disciplina NÃO for 13 ou 19,
                    // pulamos este horário e não o adicionamos à lista.
                    continue; 
                }
            }

            // 3. Se passou pelos filtros, adiciona o dia da semana à lista
            $aDiasSemenaComAula[$iDiaDaSemana] = $iDiaDaSemana;
        }
        
        // <<< FIM DA CORREÇÃO >>>
        $aDatasNoIntervalo = DBDate::getDatasNoIntervalo(
            $oPeriodoCalendario->getDataInicio(),
            $oPeriodoCalendario->getDataTermino(),
            $aDiasSemenaComAula
        );
		
/*		
A classe DBDate fica no arquivo std/DBDate.php
A função chamada getDatasNoIntervalo, retorna o dia da semana entre um dataInicio e uma datafim
por ex:
em uma disciplina lecionada em uma terça-feira=2   (0-domingo 1-segunda 2-terça ....)
mostrar quantas terças feiras tera entre o inicio(05/02/2024) e o fim(30/04/2024), ou melhor quanto dias letivos da materia tera nesse periodo
06/02/2024
07/02/2024
20/02/2024
21/02/2024
27/02/2024
28/02/2024
05/03/2024
06/03/2024
12/03/2024
13/03/2024
19/03/2024
20/03/2024
26/03/2024
27/03/2024
02/04/2024
03/04/2024
09/04/2024
10/04/2024
16/04/2024
17/04/2024
24/04/2024
30/04/2024
*/
        $datasFeriados = $this->oTurma->getCalendario()->getDataFeriados();

        foreach ($aDatasNoIntervalo as $index => $data) {
			
            foreach ($datasFeriados as $dataFeriado) {
                if ($data->getTimeStamp() == $dataFeriado->getTimeStamp()) {
                    unset($aDatasNoIntervalo[$index]);
                }
            }
        }
		//  INÍCIO DA CORREÇÃO
        
        // 1. Carrega os dias letivos da escola (1=Dom, 2=Seg, 3=Ter, ..., 7=Sab)
        $iEscola = $this->oTurma->getEscola()->getCodigo();
        
        // Carrega a classe 'cl_dialetivo' (se já não estiver carregada)
        if (!class_exists('cl_dialetivo')) {
             require_once(modification("classes/db_dialetivo_classe.php"));
        }
        
        $clDialetivo = new cl_dialetivo();
        $sSqlDiasLetivos = $clDialetivo->sql_query_file(null, "ed04_i_diasemana", null, "ed04_i_escola = {$iEscola} AND ed04_c_letivo = 'S'");
        $rsDialetivo = db_query($sSqlDiasLetivos);

        // Cria um array simples com os dias letivos. Ex: [2, 3, 4, 5, 6]
        $aDiasLetivosEscola = array();
        for ($i = 0; $i < pg_num_rows($rsDialetivo); $i++) {
            $oDiaLetivo = db_utils::fieldsMemory($rsDialetivo, $i);
            $aDiasLetivosEscola[] = $oDiaLetivo->ed04_i_diasemana;
        }

        // 2. Filtra a lista de datas, removendo dias que não são letivos
        foreach ($aDatasNoIntervalo as $index => $data) {
        
            // A classe DBDate usa o padrão do PHP (0=Dom, 1=Seg, ..., 6=Sab)
            $iDiaSemanaPHP = $data->getDiaSemana(); 
            
            // A tabela 'dialetivo' usa o padrão do banco (1=Dom, 2=Seg, ..., 7=Sab)
            $iDiaSemanaBanco = $iDiaSemanaPHP + 1;
            
            // Se o dia da semana (Ex: 7 para Sábado) não estiver no array de dias letivos da escola...
            if (!in_array($iDiaSemanaBanco, $aDiasLetivosEscola)) {
                
                // ... remove ele da lista de datas.
                unset($aDatasNoIntervalo[$index]);
            }
        }
        //  FIM DA CORREÇÃO 

        foreach ($aDatasNoIntervalo as $key => $oData) {
            $lDataEstaPresente = false;

            foreach ($this->aPeriodosAula as $oPeriodoAula) {
                if (DBDate::dataEstaNoIntervalo($oData, $oPeriodoAula->getDataInicio(), $oPeriodoAula->getDataFim())) {
                    $lDataEstaPresente = true;
					
                }
            }

            if (!$lDataEstaPresente) {
                unset($aDatasNoIntervalo[$key]);
            }
			
        }

        $aDiasAula = array();
		$i = 1;
        foreach ($aDatasNoIntervalo as $oDiaAula) {

            $oDia = new stdClass();
            $oDia->oData = $oDiaAula;

            $oDia->aPeriodoAula = array();
            foreach ($this->aPeriodosAula as $oPeriodoAula) {
				
                if ($oPeriodoAula->getDisciplina()->getCodigoDisciplina() != $oDisciplina->getCodigoDisciplina()) {
                    continue;
                }

                if ($oPeriodoAula->getDiaSemana() == $oDiaAula->getDiaSemana()
                    && DBDate::dataEstaNoIntervalo($oDiaAula, $oPeriodoAula->getDataInicio(), $oPeriodoAula->getDataFim())) {
                    $oDia->aPeriodoAula[] = $oPeriodoAula;
                }
            }
                $aDiasAula[] = $oDia;
				
        }
        return $aDiasAula;
    }

    /**
     * @param PeriodoAula $oPeriodoAula
     */
    public function adicionarPeriodo(PeriodoAula $oPeriodoAula)
    {
        $this->aPeriodosAula[] = $oPeriodoAula;
    }

    /**
     * @param bool $lSomenteAtivos
     * @return array
     * @throws DBException
     * @throws ParameterException
     */
    private function buscarPeriodos($lSomenteAtivos = true)
    {
		$lSomenteAtivos = false;
		$sqlTurma  = pg_query("SELECT calendario.* FROM escola.calendario inner join turma on ed57_i_calendario = ed52_i_codigo where ed57_i_codigo = {$this->oTurma->getCodigo()} and substr(ed52_c_descr,1,12) = 'EJA INICIAIS' ");		
		$sqlTurma2 = pg_query("SELECT calendario.* FROM escola.calendario inner join turma on ed57_i_calendario = ed52_i_codigo where ed57_i_codigo = {$this->oTurma->getCodigo()} and substr(ed52_c_descr,1,13) = 'ANOS INICIAIS' ");
		$sqlEI     = pg_query("SELECT calendario.* FROM escola.calendario inner join turma on ed57_i_calendario = ed52_i_codigo where ed57_i_codigo = {$this->oTurma->getCodigo()} and substr(ed52_c_descr,1,12) = 'ED. INFANTIL'");
		if( pg_num_rows($sqlEI) > 0)
		{
			$lSomenteAtivos = false;
		}
		$sqlEI     = pg_query("SELECT calendario.* FROM escola.calendario inner join turma on ed57_i_calendario = ed52_i_codigo where ed57_i_codigo = {$this->oTurma->getCodigo()} and substr(ed52_c_descr,1,12) = 'ED. INFANTIL'");
		if( pg_num_rows($sqlEI) > 0)
		{
			$lSomenteAtivos = false;
		}
		$sqlAI     = pg_query("SELECT calendario.* FROM escola.calendario inner join turma on ed57_i_calendario = ed52_i_codigo where ed57_i_codigo = {$this->oTurma->getCodigo()} and substr(ed52_c_descr,1,13) = 'ANOS INICIAIS'");
		if( pg_num_rows($sqlAI) > 0)
		{
			$lSomenteAtivos = false;
		}
		if($this->oTurma->getCodigo() == 2463)
		{	
		    $lSomenteAtivos = false;
	    }
/*
será mais facil corrigir as turmas que houve troca de professores
*/		
        $sWhere = "     ed59_i_turma = {$this->oTurma->getCodigo()} ";
        $sWhere .= " and ed59_i_serie = {$this->oEtapa->getCodigo()} ";
        $sWhere .= " and ed58_datainicio is not null                 ";
        $sWhere .= " and ed58_datafim is not null                    ";
        if ($lSomenteAtivos) {
            $sWhere .= " and ed58_ativo is TRUE ";
        }
        $sWhereUnion = "     ed59_i_turma = {$this->oTurma->getCodigo()} ";
        $sWhereUnion .= " and ed59_i_serie = {$this->oEtapa->getCodigo()} ";
        $sWhereUnion .= " and ed175_datainicio is not null                 ";
        $sWhereUnion .= " and ed175_datafim is not null                    ";
        if ($lSomenteAtivos) {
            $sWhereUnion .= " and ed175_ativo is TRUE ";
        }
        $sOrdemUnion = ' ed58_i_diasemana ';
        $oDaoRegencia = new cl_regenciahorario();
        $sSqlRegenciaHorario = $oDaoRegencia->sql_query_regencia_dia_semana_union_semreg(null, "regenciahorario.*", '', $sWhere, $sOrdemUnion, $sWhereUnion);
		if( pg_num_rows($sqlTurma) > 0 or pg_num_rows($sqlTurma2 ) > 0)
		{
			// um unico professor da as aulas que devem ser computadas como 1 só no dia
             $sSqlRegenciaHorario = $oDaoRegencia->sql_query_regencia_dia_semana_union_semregEJA(null, "regenciahorario.*", '', $sWhere, $sOrdemUnion, $sWhereUnion);			
		}else{	
             $sSqlRegenciaHorario = $oDaoRegencia->sql_query_regencia_dia_semana_union_semreg(null, "regenciahorario.*", '', $sWhere, $sOrdemUnion, $sWhereUnion, $this->disp);
		}
		
        $rsRegenciaHorario = db_query($sSqlRegenciaHorario);
        if (!$rsRegenciaHorario) {
            throw new DBException ("Erro ao buscar grade horario. \n" . pg_last_error());
        }

        $aPeriodosAula = array();
        $iLinhas = pg_num_rows($rsRegenciaHorario);

        for ($i = 0; $i < $iLinhas; $i++) {
            $oDados = db_utils::fieldsMemory($rsRegenciaHorario, $i);
            $oPeriodoAula = new PeriodoAula();
            $oPeriodoAula->setDiaSemana($oDados->ed58_i_diasemana - 1);
            $oPeriodoAula->setRegencia(RegenciaRepository::getRegenciaByCodigo($oDados->ed58_i_regencia));
            $oPeriodoAula->setPeriodoEscola(PeriodoEscolaRepository::getByCodigo($oDados->ed58_i_periodo));
            $oPeriodoAula->setCodigo($oDados->ed58_i_codigo);
            $oPeriodoAula->setRegente($oDados->ed58_i_rechumano);
            $oPeriodoAula->setDataInicio(new DBDate($oDados->ed58_datainicio));
            $oPeriodoAula->setDataFim(new DBDate($oDados->ed58_datafim));
            $oPeriodoAula->setAtivo($oDados->ed58_ativo == 't');
            $oPeriodoAula->setTipoVinculo($oDados->ed58_tipovinculo);
            $aPeriodosAula[] = $oPeriodoAula;
        }

        return $aPeriodosAula;
    }


    /**
     * @return bool
     * @throws DBException
     * @throws ParameterException
     */
    private function validarPeriodos()
    {
		
        $aPeriodosValidar = array();
        $aTodosPeriodos = $this->buscarPeriodos(false);
        

        /**
         * Identifica o período mais atual
         */
        foreach ($aTodosPeriodos as $oPeriodo) {
            $sHash = "{$oPeriodo->getDiaSemana()}#{$oPeriodo->getPeriodoEscola()->getCodigo()}";
            if (!array_key_exists($sHash, $aPeriodosValidar)) {
                $aPeriodosValidar[$sHash] = $oPeriodo;
            } else {
                if ($aPeriodosValidar[$sHash]->getDataFim()->getTimeStamp() < $oPeriodo->getDataFim()->getTimeStamp()) {
                    $aPeriodosValidar[$sHash] = $oPeriodo;
                }
            }
        }

        $this->aLogConflito = array();

        foreach ($this->aPeriodosAula as $oPeriodoSalvar) {
            // os periodos novos não tem código
            
            if ($oPeriodoSalvar->getCodigo() != '') {
                continue;
            }
            //echo "<pre>";
            //print_r($oPeriodoSalvar);
            //echo "</pre>";
            //die("Confere");

            foreach ($aPeriodosValidar as $oOutrosPeriodos) {
                if ($oOutrosPeriodos->getDiaSemana() == $oPeriodoSalvar->getDiaSemana()
                    && $oOutrosPeriodos->getPeriodoEscola()->getCodigo() == $oPeriodoSalvar->getPeriodoEscola()->getCodigo()) {

                    if ($oPeriodoSalvar->getDataInicio()->getTimeStamp() <= $oOutrosPeriodos->getDataFim()->getTimeStamp()) {
                        $this->aLogConflito[] = array(
                            'periodo' => $oPeriodoSalvar->getPeriodoEscola()->getDescricao(),
                            'diasemana' => DBDate::getLabelDiaSemana($oPeriodoSalvar->getDiaSemana()),
                            'data_fim' => $oOutrosPeriodos->getDataFim()->adiantarPeriodo(1, 'd')->convertTo(DBDate::DATA_PTBR)
                        );
                    }
                }
            }
            //die("Foreach");
        }

        return count($this->aLogConflito) == 0;
    }

    /**
     * @throws Exception
     */
    public function salvar()
    {
        $sMsg = "Não é possível salvar a grade de horários, pois existem conflitos na Vigência do Período:\n";
        /*if ($this->tipoGrade === PeriodoAula::GRADE_HORARIO && !$this->validarPeriodos()) {

            foreach ($this->aLogConflito as $aVariaveis) {
                $sMsg .= sprintf(
                    "Data disponível para incluir o %s período de %s: a partir de %s.\n",
                    $aVariaveis['periodo'],
                    $aVariaveis['diasemana'],
                    $aVariaveis['data_fim']
                );
            }
            $sMsg .= "Altere a data de Vigência do Período.(1)";
            throw new Exception($sMsg);
        }

        if ($this->tipoGrade === PeriodoAula::VINCULAR_PROFESSOR_DISCISPLINA && !$this->validarPeriodosDisciplina()) {
            foreach ($this->aLogConflito as $aVariaveis) {
                $sMsg .= sprintf(
                    "Disciplina: %s - Data disponível para incluir o %s período de %s: a partir de %s.\n",
                    $aVariaveis['disciplina'],
                    $aVariaveis['periodo'],
                    $aVariaveis['diasemana'],
                    $aVariaveis['data_fim']
                );
            }
            $sMsg .= "Altere a data de Vigência do Período.(2)";
            throw new Exception($sMsg);
        }*/

        foreach ($this->aPeriodosAula as $oPeriodo) {
            // como não altera... só inclui... realiza manutenção só nos registros novos
            if ($oPeriodo->getCodigo() == null) {
                if ($oPeriodo->getRegente() == null) {
                    $oPeriodo->salvarDisciplinaSemRegente();
                } else {
                    $oPeriodo->salvar();
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getTipoGrade()
    {
        return $this->tipoGrade;
    }

    /**
     * @param int $tipoGrade
     */
    public function setTipoGrade($tipoGrade)
    {
        $this->tipoGrade = $tipoGrade;
    }

    /**
     * Valida a grade dos períodos de aula do tipo 2
     * @return bool
     * @throws Exception
     */
    private function validarPeriodosDisciplina()
    {
	
        $this->aLogConflito = [];
        foreach ($this->aPeriodosAula as $perido) {
            $dataInicio = $perido->getDataInicio()->getDate();
            $dataFinal = $perido->getDataFim()->getDate();

            $where = "
                ed58_i_regencia = {$perido->getRegencia()->getCodigo()}
                and ed58_i_diasemana = {$perido->getDiaSemana()}";
            $filtroPerido = "(datainicio, datafim) overlaps ('{$dataInicio}'::date, '{$dataFinal}'::date)";

            $sql = "
                select * from (
                    select max(ed58_datainicio) as datainicio, max(ed58_datafim) as datafim
                    from regenciahorario
                    where {$where}
                ) as x
                where {$filtroPerido}
            ";

            $rs = db_query($sql);
            if ($rs && pg_num_rows($rs) > 0) {
                $dados = pg_fetch_array($rs, 0);
                $proximoDiaDisponivel = new DBDate($dados['datafim']);

                $this->aLogConflito[] = array(
                    'disciplina' => $perido->getRegencia()->getDisciplina()->getNomeDisciplina(),
                    'periodo' => $perido->getPeriodoEscola()->getDescricao(),
                    'diasemana' => DBDate::getLabelDiaSemana($perido->getDiaSemana()),
                    'data_fim' => $proximoDiaDisponivel->adiantarPeriodo(1, 'd')->convertTo(DBDate::DATA_PTBR),
                    'sql' => $sql,
                );
            }
        }

        return count($this->aLogConflito) === 0;
    }


    /**
     * @param PeriodoAula[] $periodos
     */
    public function setPeriodosAula(array $periodos)
    {
        $this->aPeriodosAula = $periodos;
    }
}
