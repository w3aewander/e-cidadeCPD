<?php 
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBselller Servicos de Informatica
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("std/db_stdClass.php"));
require_once(modification("std/DBDate.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("model/webservices/ControleAcessoAluno.model.php"));
require_once(modification("model/educacao/avaliacao/iFormaObtencao.interface.php"));
require_once(modification("model/educacao/avaliacao/iElementoAvaliacao.interface.php"));
db_app::import("educacao.*");
db_app::import("educacao.censo.DisciplinaCenso");
db_app::import("exceptions.*");
db_app::import("educacao.avaliacao.*");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function verificaNota($matricula, $regencia, $dataaula){
  $sql = pg_query("SELECT * FROM dcnotas WHERE matricula = {$matricula} AND regencia = {$regencia} AND data = '{$dataaula}' ");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function verificaNota2($matricula, $horario){
  $sql = pg_query("SELECT * FROM dcnotas WHERE matricula = {$matricula} AND ed301_diarioclasseregenciahorario = {$horario}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function verificaNota3($matricula, $data, $regencia){
  $kql = "SELECT * FROM dcnotas WHERE matricula = {$matricula} AND data = '{$data}' AND regencia = {$regencia} ";
  //var_dump($kql); die("Confere primeiro");
  $sql = pg_query("SELECT * FROM dcnotas WHERE matricula = {$matricula} AND data = '{$data}' AND regencia = {$regencia} ");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function verificaNota3Rec($matricula, $data, $regencia){
  $sql = pg_query("SELECT * FROM dcnotasrec WHERE matricula = {$matricula} AND data = '{$data}' AND regencia = {$regencia} ");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaCodigoDaAvaliacao($matricula, $regencia, $proc){  
  $sql = pg_query("SELECT ed72_i_codigo FROM diario INNER JOIN diarioavaliacao ON ed95_i_codigo = ed72_i_diario WHERE ed95_i_aluno = {$matricula} AND ed95_i_regencia = {$regencia} AND ed72_i_procavaliacao = {$proc}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed72_i_codigo"];
}

function buscaNotaAtual($cod){
            $sql = pg_query("SELECT ed72_i_valornota FROM diarioavaliacao WHERE ed72_i_codigo = {$cod}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0]["ed72_i_valornota"];
          }

function validaProc($matricula, $regencia, $proc){
  //$oParam->dtAula
  $sql = pg_query("SELECT ed72_i_codigo, ed54_i_evento FROM diario INNER JOIN diarioavaliacao ON ed95_i_codigo = ed72_i_diario INNER JOIN feriado ON ed54_i_calendario = ed95_i_calendario WHERE ed95_i_aluno = {$matricula} AND ed95_i_regencia = {$regencia} AND ed72_i_procavaliacao = {$proc} AND ed54_i_evento IN(13,14)");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed72_i_codigo"];
}

function buscaSeparacaoPeriodos($codigoturma, $data){
  $sql1 = pg_query("SELECT ed57_i_calendario FROM turma WHERE ed57_i_codigo = {$codigoturma}");
  $r1 = pg_fetch_all($sql1);
  $codcalendario = $r1[0]["ed57_i_calendario"];

  $sql2 = pg_query("SELECT ed53_d_inicio, ed53_d_fim FROM periodocalendario WHERE ed53_d_inicio <= '{$data}' AND ed53_d_fim >= '{$data}' AND ed53_i_calendario = {$codcalendario}");
  $r2 = pg_fetch_all($sql2);

  $periodo = $r2[0];

  return $periodo;
}

function buscaSomatorio($dcmatricula2, $dcdisciplina2, $dcturma2, $periodoi, $periodof){
  $sql = pg_query("SELECT sum(nota) as somatorio FROM dcnotas WHERE matricula = {$dcmatricula2} AND disciplina = {$dcdisciplina2} AND turma = '{$dcturma2}' AND data BETWEEN '{$periodoi}' AND '{$periodof}'");
  $resultado = pg_fetch_all($sql);
  return ($resultado[0]["somatorio"]) ? $resultado[0]["somatorio"] : 0;          
}

$oRetorno          = new stdClass();
$oRetorno->status  = 1;
$oRetorno->message = "";

$iEscola        = db_getsession("DB_coddepto");
$oJson          = new services_json();
$oParam         = $oJson->decode(str_replace("\\", "", $_POST["json"]));

define("ARQUIVO_MENSAGEM", "educacao.escola.edu4_diarioclasse_RPC.");

try {

  db_inicio_transacao();
  switch ($oParam->exec) {

    case 'getDisciplinasRegenteEscola':

      //$dbusca = substr($_SESSION["DIAS_LETIVOS_ESCOLA"][0]->data, 0, 4);
      $dbusca = (int)db_getsession("DB_anousu");
      //var_dump($dbusca);
      //die("Confere");

      $sDtLogin = date("Y-m-d", db_getsession('DB_datausu'));
      //$sDtLogin = "2024-01-01";

      $oRetorno->codigo_regente = 0;
      $oRetorno->nome_regente   = '';
      $oDaoDBUsusario   = db_utils::getDao("db_usuacgm");
      $iCodigoUsuario   = db_getsession("DB_id_usuario");
      $sSqlDadosRegente = $oDaoDBUsusario->sql_query($iCodigoUsuario, "z01_numcgm, z01_nome");
      $rsDadosRegente   = $oDaoDBUsusario->sql_record($sSqlDadosRegente);
      

      if ($oDaoDBUsusario->numrows > 0) {
        $oDadosRegente            = db_utils::fieldsMemory($rsDadosRegente, 0);
        $oRetorno->codigo_regente =  $oDadosRegente->z01_numcgm;
        $oRetorno->nome_regente   =  $oDadosRegente->z01_nome;
      }
      if (isset($_SESSION["DIAS_LETIVOS_ESCOLA"])) {
         unset($_SESSION["DIAS_LETIVOS_ESCOLA"]);
      }
      //var_dump($iEscola);
      //var_dump($oRetorno->codigo_regente); die("Confere");

      $sWhereFuncionario  = "     rhpessoal.rh01_numcgm         = {$oDadosRegente->z01_numcgm} ";
      $sWhereFuncionario .= " and rechumanoescola.ed75_i_escola = {$iEscola}                   ";

      $sWhereNaoFuncionario  = "     rechumanocgm.ed285_i_cgm      = {$oDadosRegente->z01_numcgm} ";
      $sWhereNaoFuncionario .= " and rechumanoescola.ed75_i_escola = {$iEscola}                   ";

      $sWhereRegenciaHorario  = "    ed58_ativo is true      ";
      //$sWhereRegenciaHorario .= " and ed59_c_freqglob <> 'A' ";

      $sWhereSubstituto  = " {$sWhereRegenciaHorario}";
      $sWhereSubstituto .= " and '{$sDtLogin}' >= ed322_periodoinicial ";
      $sWhereSubstituto .= " and ((ed322_periodofinal is null and  '{$sDtLogin}' <= ed52_d_fim) ";
      $sWhereSubstituto .= "       or  '{$sDtLogin}' <= ed322_periodofinal) ";


      $sCampos  = " distinct ed232_i_codigo as codigo_disciplina, ";
      $sCampos .= " ed232_c_descr as descricao_disciplina";

      $oDaoRegenciaHorario    = db_utils::getDao("regenciahorario");
      //$sWhereTurmaEscola      = " ed57_i_escola = {$iEscola} ";
      $sWhereTurmaEscola      = " ed57_i_escola = {$iEscola} AND calendario.ed52_i_ano = {$dbusca} ";
      $sSqlDisciplinasRegente = $oDaoRegenciaHorario->sql_query_disciplinas_cgm($sCampos,
                                                                                "descricao_disciplina",
                                                                                $sWhereFuncionario,
                                                                                $sWhereNaoFuncionario,
                                                                                $sWhereRegenciaHorario,
                                                                                $sWhereSubstituto,
                                                                                $sWhereTurmaEscola);

       $rsDisciplinasRegente = $oDaoRegenciaHorario->sql_record($sSqlDisciplinasRegente);
       //var_dump($sSqlDisciplinasRegente); die("Confere");
       $oRetorno->itens      = db_utils::getCollectionByRecord($rsDisciplinasRegente, false, false, true);
    break;

    case 'getDatasProfessorDisciplina':

      $oDaoRecHumano       = new cl_rechumano();
      $oDaoRegenciaHorario = new cl_regenciahorario();

      if (isset($_SESSION["DIAS_LETIVOS_ESCOLA"])) {
        unset($_SESSION["DIAS_LETIVOS_ESCOLA"]);
      }

      $aDiasLetivos = array();

      $sCampoRegente  = " distinct ed20_i_codigo ";
      $sWhereRegente  = "     (rh01_numcgm  = {$oParam->iRegente} or ed285_i_cgm = {$oParam->iRegente})";
      $sWhereRegente .= " and ed75_i_escola = {$iEscola}";
      $sWhereRegente .= " and ed75_i_saidaescola is null ";


      $sSqlRegente   = $oDaoRecHumano->sql_query_rechumano_cgm(null, $sCampoRegente, null, $sWhereRegente);
      $rsRegente     = $oDaoRecHumano->sql_record($sSqlRegente);
      //1312
      

      if ($oDaoRecHumano->numrows == 0) {
        throw new Exception('Não foi possível localizar o regente.');
      }

      $aCodigoRecHumano = db_utils::getCollectionByRecord( $rsRegente );

      $aCodigos         = array();
      foreach ($aCodigoRecHumano as $oCodigoRecHumano ) {
        $aCodigos[] = $oCodigoRecHumano->ed20_i_codigo;
      }

      $sCodigoRecHumano = implode( ", ", $aCodigos );

      $sWhere  = " ed57_i_escola    = {$iEscola} ";
      $sWhere .= " and ed52_i_ano   = " . db_getsession("DB_anousu");
      $sWhere .= " and ed232_i_codigo = {$oParam->iCodigoDisciplina}";
      $sWhere .= " and ed58_datainicio is not null ";
      $sWhere .= " and ed58_datafim    is not null ";

      $sWhereRegencia      = " {$sWhere} ";
      $sWhereRegencia     .= " and ed58_i_rechumano in ({$sCodigoRecHumano}) ";

      $sWhereSubstituicao  = " {$sWhere} ";
      $sWhereSubstituicao .= " and ed322_rechumano in ({$sCodigoRecHumano})";
      $sWhereSubstituicao .= " and ed322_tipovinculo = 1";

      $sSqlDiasDaSemanaComAula = $oDaoRegenciaHorario->sql_query_periodos_de_regencia($sWhereRegencia, $sWhereSubstituicao);
      $rsDiasDaSemanaComAula   = db_query($sSqlDiasDaSemanaComAula);
      //var_dump($sSqlDiasDaSemanaComAula); die("Dias");
      if (!$rsDiasDaSemanaComAula) {
        throw new DBException( _M( ARQUIVO_MENSAGEM . "erro_buscar_dias_docente") );
      }

      $aDiasDeAula               = array();
      $iTotalDiasDaSemanaComAula = pg_num_rows($rsDiasDaSemanaComAula);

      for ($iAula = 0; $iAula < $iTotalDiasDaSemanaComAula; $iAula++) {

        /**
         * Diminuimos 1 do dia da semana, pois o dia do semana para o php, inicia em 0 para domingo,
         * e termina em 6 (sábado.), na tabela diasemana, o inicio é 1 para domingo, e 7 para Sábado;
         */
        $oDadosDiaSemana = db_utils::fieldsMemory($rsDiasDaSemanaComAula, $iAula);
        $iDiaNaSemana    = $oDadosDiaSemana->ed58_i_diasemana-1;
        if (!isset($aDiasDeAula[$iDiaNaSemana])) {
          $aDiasDeAula[$iDiaNaSemana] = new stdClass();
        }

        $oTurma = new stdClass();
        $oTurma->codigo_turma     = $oDadosDiaSemana->codigo_turma."_".$oDadosDiaSemana->serie;
        $oTurma->codigo_serie     = $oDadosDiaSemana->serie;
        $oTurma->descricao_turma  = urlencode("{$oDadosDiaSemana->descricao_turma} ({$oDadosDiaSemana->nome_etapa})");

        /**
         * para não ter que refatorar a busca das turmas
         */
        if ( $oDadosDiaSemana->substituto == 't' && empty($oDadosDiaSemana->data_final) ) {
          $oDadosDiaSemana->data_final = date("Y-m-d", db_getsession("DB_datausu"));
        }
        $oTurma->data_inicio = $oDadosDiaSemana->data_inicio;
        $oTurma->data_final  = $oDadosDiaSemana->data_final;

        $aDiasDeAula[$iDiaNaSemana]->turmas[] = $oTurma;
      }

      /**
       * Carregamos as datas do calendario.
       */
      $sCampos             = "min(ed53_d_inicio) as inicio, max(ed53_d_fim) as maximo, ";
      $sCampos            .= "array_to_string(array_accum(distinct ed52_i_codigo), ', ') as calendarios,";
      $sCampos            .= "max(ed53_d_fim) - min(ed53_d_inicio) as numero_dias_aula";
      $sSqlDatasCalendario = $oDaoRegenciaHorario->sql_query_diario_classe_periodo_avaliacao(null, $sCampos,  null, $sWhere);
      $rsDatasCalendario   = $oDaoRegenciaHorario->sql_record($sSqlDatasCalendario);
      //var_dump($sSqlDatasCalendario); die("Datas");
      

      if ($oDaoRegenciaHorario->numrows == 0) {
        throw new Exception("Periodo sem calendario informado.\n{$oDaoRegenciaHorario->erro_msg}");
      }
      $oDadosAnoLetivo = db_utils::fieldsMemory($rsDatasCalendario, 0);


      /**
       * Calcula as datas em que o docente leciona com base na regenciahorario
       */
      $aDatasDeAula = array();
      for( $i = 0; $i < $iTotalDiasDaSemanaComAula; $i++ ) {

        $oDadosDiaSemana = db_utils::fieldsMemory($rsDiasDaSemanaComAula, $i);
        $oDataInicio     = new DBDate( $oDadosDiaSemana->data_inicio );


        if ( $oDadosDiaSemana->substituto == 't' && empty($oDadosDiaSemana->data_final) ) {

          $oDadosDiaSemana->data_final = date("Y-m-d", db_getsession("DB_datausu"));
        }

        $oDataFimPeriodo = new DBDate( $oDadosDiaSemana->data_final );
        $oDataAtual      = new DBDate( date("Y-m-d", db_getsession("DB_datausu") ) );

        if ( $oDataFimPeriodo->getTimeStamp() > $oDataAtual->getTimeStamp() ) {
          $oDataFimPeriodo = $oDataAtual;
        }

        if ($oDataInicio->getTimeStamp() > $oDataAtual->getTimeStamp()) {
          continue;
        }

        $aAuxiliar    = DBDate::getDatasNoIntervalo($oDataInicio, $oDataFimPeriodo, array($oDadosDiaSemana->ed58_i_diasemana - 1));

        $aAuxiliar    = array_diff($aAuxiliar, $aDatasDeAula);
        $aDatasDeAula = array_merge($aAuxiliar, $aDatasDeAula);
      }

      asort($aDatasDeAula);

        //echo "<pre>";
        //print_r($aDatasDeAula);
        //echo "</pre>";
        //die("Confere");

      
       //Remove os eventos / feriados dos dias de aula do professor.
      //var_dump($oDadosAnoLetivo->calendarios); die("Confere ca"); //222,223
       
      $aEventos = buscaFeriados($oDadosAnoLetivo->calendarios);
        //echo "<pre>";
        //print_r($aEventos);
        //echo "</pre>";
        //die("Confere");
      foreach ($aEventos as $oEvento) {

        $lDiaLetivo  = $oEvento->dia_letivo == 'S';
        $oDataEvento = new DBDate($oEvento->data);
        foreach ($aDatasDeAula as $key => $oDiaAula) {

          if ($oDiaAula->getTimeStamp() == $oDataEvento->getTimeStamp() && !$lDiaLetivo) {
            unset($aDatasDeAula[$key]);
          }
        }
      }

      /**
       * Criamos a Estrutura com os Dados do dia Letivo
       */
      foreach ($aDatasDeAula as $oData) {

        $oDiaLetivo            = new stdClass();
        $oDiaLetivo->data      = $oData->getDate();
        $oDiaLetivo->diasemana = urlencode( mb_strtoupper( DBDate::getLabelDiaSemana($oData->getDiaSemana()) ) );
        $oDiaLetivo->turmas    = array_values( (array) $aDiasDeAula[$oData->getDiaSemana()]->turmas );
        $aDiasLetivos[]        = $oDiaLetivo;
      }

      $_SESSION["DIAS_LETIVOS_ESCOLA"] = $aDiasLetivos;
      $oRetorno->aDiasLetivos          = $aDiasLetivos;
      $oRetorno->dataatual             = date("Y-m-d", db_getsession("DB_datausu"));

      break;

      case 'getDatasProfessorDisciplinaRec':

      $oDaoRecHumano       = new cl_rechumano();
      $oDaoRegenciaHorario = new cl_regenciahorario();

      if (isset($_SESSION["DIAS_LETIVOS_ESCOLA"])) {
        unset($_SESSION["DIAS_LETIVOS_ESCOLA"]);
      }

      $aDiasLetivos = array();

      $sCampoRegente  = " distinct ed20_i_codigo ";
      $sWhereRegente  = "     (rh01_numcgm  = {$oParam->iRegente} or ed285_i_cgm = {$oParam->iRegente})";
      $sWhereRegente .= " and ed75_i_escola = {$iEscola}";
      $sWhereRegente .= " and ed75_i_saidaescola is null ";


      $sSqlRegente   = $oDaoRecHumano->sql_query_rechumano_cgm(null, $sCampoRegente, null, $sWhereRegente);
      $rsRegente     = $oDaoRecHumano->sql_record($sSqlRegente);

      if ($oDaoRecHumano->numrows == 0) {
        throw new Exception('Não foi possível localizar o regente.');
      }

      $aCodigoRecHumano = db_utils::getCollectionByRecord( $rsRegente );
      $aCodigos         = array();
      foreach ($aCodigoRecHumano as $oCodigoRecHumano ) {
        $aCodigos[] = $oCodigoRecHumano->ed20_i_codigo;
      }

      $sCodigoRecHumano = implode( ", ", $aCodigos );

      $sWhere  = " ed57_i_escola    = {$iEscola} ";
      $sWhere .= " and ed52_i_ano   = " . db_getsession("DB_anousu");
      $sWhere .= " and ed232_i_codigo = {$oParam->iCodigoDisciplina}";
      $sWhere .= " and ed58_datainicio is not null ";
      $sWhere .= " and ed58_datafim    is not null ";

      $sWhereRegencia      = " {$sWhere} ";
      $sWhereRegencia     .= " and ed58_i_rechumano in ({$sCodigoRecHumano}) ";

      $sWhereSubstituicao  = " {$sWhere} ";
      $sWhereSubstituicao .= " and ed322_rechumano in ({$sCodigoRecHumano})";
      $sWhereSubstituicao .= " and ed322_tipovinculo = 1";

      $sSqlDiasDaSemanaComAula = $oDaoRegenciaHorario->sql_query_periodos_de_regencia($sWhereRegencia, $sWhereSubstituicao);
      $rsDiasDaSemanaComAula   = db_query($sSqlDiasDaSemanaComAula);
      if (!$rsDiasDaSemanaComAula) {
        throw new DBException( _M( ARQUIVO_MENSAGEM . "erro_buscar_dias_docente") );
      }

      $aDiasDeAula               = array();
      $iTotalDiasDaSemanaComAula = pg_num_rows($rsDiasDaSemanaComAula);

      for ($iAula = 0; $iAula < $iTotalDiasDaSemanaComAula; $iAula++) {

        /**
         * Diminuimos 1 do dia da semana, pois o dia do semana para o php, inicia em 0 para domingo,
         * e termina em 6 (sábado.), na tabela diasemana, o inicio é 1 para domingo, e 7 para Sábado;
         */
        $oDadosDiaSemana = db_utils::fieldsMemory($rsDiasDaSemanaComAula, $iAula);
        $iDiaNaSemana    = $oDadosDiaSemana->ed58_i_diasemana -1;
        if (!isset($aDiasDeAula[$iDiaNaSemana])) {
          $aDiasDeAula[$iDiaNaSemana] = new stdClass();
        }

        $oTurma = new stdClass();
        $oTurma->codigo_turma     = $oDadosDiaSemana->codigo_turma."_".$oDadosDiaSemana->serie;
        $oTurma->codigo_serie     = $oDadosDiaSemana->serie;
        $oTurma->descricao_turma  = urlencode("{$oDadosDiaSemana->descricao_turma} ({$oDadosDiaSemana->nome_etapa})");

        /**
         * para não ter que refatorar a busca das turmas
         */
        if ( $oDadosDiaSemana->substituto == 't' && empty($oDadosDiaSemana->data_final) ) {
          $oDadosDiaSemana->data_final = date("Y-m-d", db_getsession("DB_datausu"));
        }
        $oTurma->data_inicio = $oDadosDiaSemana->data_inicio;
        $oTurma->data_final  = $oDadosDiaSemana->data_final;

        $aDiasDeAula[$iDiaNaSemana]->turmas[] = $oTurma;
      }

      /**
       * Carregamos as datas do calendario.
       */
      $sCampos             = "min(ed53_d_inicio) as inicio, max(ed53_d_fim) as maximo, ";
      $sCampos            .= "array_to_string(array_accum(distinct ed52_i_codigo), ', ') as calendarios,";
      $sCampos            .= "max(ed53_d_fim) - min(ed53_d_inicio) as numero_dias_aula";
      $sSqlDatasCalendario = $oDaoRegenciaHorario->sql_query_diario_classe_periodo_avaliacao(null, $sCampos,  null, $sWhere);
      $rsDatasCalendario   = $oDaoRegenciaHorario->sql_record($sSqlDatasCalendario);

      if ($oDaoRegenciaHorario->numrows == 0) {
        throw new Exception("Periodo sem calendario informado.\n{$oDaoRegenciaHorario->erro_msg}");
      }
      $oDadosAnoLetivo = db_utils::fieldsMemory($rsDatasCalendario, 0);

      /**
       * Calcula as datas em que o docente leciona com base na regenciahorario
       */
      $aDatasDeAula = array();
      for( $i = 0; $i < $iTotalDiasDaSemanaComAula; $i++ ) {

        $oDadosDiaSemana = db_utils::fieldsMemory($rsDiasDaSemanaComAula, $i);
        $oDataInicio     = new DBDate( $oDadosDiaSemana->data_inicio );

        if ( $oDadosDiaSemana->substituto == 't' && empty($oDadosDiaSemana->data_final) ) {

          $oDadosDiaSemana->data_final = date("Y-m-d", db_getsession("DB_datausu"));
        }

        $oDataFimPeriodo = new DBDate( $oDadosDiaSemana->data_final );
        $oDataAtual      = new DBDate( date("Y-m-d", db_getsession("DB_datausu") ) );

        if ( $oDataFimPeriodo->getTimeStamp() > $oDataAtual->getTimeStamp() ) {
          $oDataFimPeriodo = $oDataAtual;
        }

        if ($oDataInicio->getTimeStamp() > $oDataAtual->getTimeStamp()) {
          continue;
        }

        $aAuxiliar    = DBDate::getDatasNoIntervalo($oDataInicio, $oDataFimPeriodo, array($oDadosDiaSemana->ed58_i_diasemana - 1));

        $aAuxiliar    = array_diff($aAuxiliar, $aDatasDeAula);
        $aDatasDeAula = array_merge($aAuxiliar, $aDatasDeAula);
      }

      asort($aDatasDeAula);

      /**
       * Remove os eventos / feriados dos dias de aula do professor.
       */
      $aEventos = buscaFeriadosRec($oDadosAnoLetivo->calendarios);
      $datasrec = array();
      foreach ($aEventos as $linha){
        $datarecu = new DBDate($linha->data);
        $datarecu = $datarecu->getTimeStamp();
        array_push($datasrec, $datarecu);         
      }

      foreach ($aEventos as $oEvento) {
        $lDiaLetivo  = $oEvento->dia_letivo == 'S';        
        $oDataEvento = new DBDate($oEvento->data);
        foreach ($aDatasDeAula as $key => $oDiaAula) {
          if(!in_array($oDiaAula->getTimeStamp(), $datasrec)){
              unset($aDatasDeAula[$key]);
          }
          /*if ($oDiaAula->getTimeStamp() == $oDataEvento->getTimeStamp() && !$lDiaLetivo) {
            unset($aDatasDeAula[$key]);
          }*/
        }
      }

      /**
       * Criamos a Estrutura com os Dados do dia Letivo
       */
      foreach ($aDatasDeAula as $oData) {

        $oDiaLetivo            = new stdClass();
        $oDiaLetivo->data      = $oData->getDate();
        $oDiaLetivo->diasemana = urlencode( mb_strtoupper( DBDate::getLabelDiaSemana($oData->getDiaSemana()) ) );
        $oDiaLetivo->turmas    = array_values( (array) $aDiasDeAula[$oData->getDiaSemana()]->turmas );
        $aDiasLetivos[]        = $oDiaLetivo;
      }

      $_SESSION["DIAS_LETIVOS_ESCOLA"] = $aDiasLetivos;
      $oRetorno->aDiasLetivos          = $aDiasLetivos;
      $oRetorno->dataatual             = date("Y-m-d", db_getsession("DB_datausu"));

      break;


    case 'getTurmasNoDia' :

      $aTurmas = array();

      if (isset($oParam->dtAula) && isset($oParam->dtAula)) {

        $oDataInformada = new DBDate($oParam->dtAula);

        if (isset($_SESSION["DIAS_LETIVOS_ESCOLA"])) {
          foreach ($_SESSION["DIAS_LETIVOS_ESCOLA"] as $oData) {
            if ($oData->data == $oParam->dtAula) {
              foreach ($oData->turmas as $oTurma ) {
                if ( DBDate::dataEstaNoIntervalo($oDataInformada, new DBDate($oTurma->data_inicio), new DBDate($oTurma->data_final) ) ) {
                  $aTurmas[$oTurma->codigo_turma] = $oTurma;
                }
              }

              break;
            }
          }
        }
      }
      $oRetorno->aTurmas = array_values((array)$aTurmas);
      break;

      case 'getTurmasNoDiaRec' :      
      $aTurmas = array();

      if (isset($oParam->dtAula) && isset($oParam->dtAula)) {

        $oDataInformada = new DBDate($oParam->dtAula);
        if (isset($_SESSION["DIAS_LETIVOS_ESCOLA"])) {

          foreach ($_SESSION["DIAS_LETIVOS_ESCOLA"] as $oData) {

            if ($oData->data == $oParam->dtAula) {

              foreach ($oData->turmas as $oTurma ) {

                if ( DBDate::dataEstaNoIntervalo($oDataInformada, new DBDate($oTurma->data_inicio), new DBDate($oTurma->data_final) ) ) {
                  $aTurmas[$oTurma->codigo_turma] = $oTurma;
                }
              }

              break;
            }
          }
        }
      }
      $oRetorno->aTurmas = array_values((array)$aTurmas);
      break;

    case 'getAlunosTurma' :

      $oDaoMatricula                   = new cl_matricula();
      $oDaoRecHumano                   = new cl_rechumano();
      $oDaoRegenciaHorario             = new cl_regenciahorario();
      $oDaoDiarioClasseAlunoFalta      = new cl_diarioclassealunofalta();
      $oDaoDiarioClasseRegenciaHorario = new cl_diarioclasseregenciahorario();
      $oDaoControleAcessoAluno         = new cl_controleacessoalunoregistro();
      $oDaoControleAcesso              = new cl_controleacessoaluno();
      $oDaoPeriodoEscolaTurnoReferente = new cl_periodoescolaturnoreferente();

      $aPartesTurma  = explode("_", $oParam->iCodigoTurma);
      $iCodigoSerie  = '';
      if (isset($aPartesTurma[1])) {
       $iCodigoSerie  = $aPartesTurma[1];
      }
      $oParam->iCodigoTurma  = $aPartesTurma[0];
      $sListaCampos  = " aluno.ed47_v_nome as  nome, ";
      $sListaCampos .= " aluno.ed47_i_codigo as codigo, ";
      $sListaCampos .= " matricula.ed60_i_numaluno as ordem_turma, ";
      $sListaCampos .= " matricula.ed60_c_situacao as situacao, ";
      $sListaCampos .= " matricula.ed60_d_datamatricula as data_matricula, ";
      $sListaCampos .= " matricula.ed60_d_datasaida data_saida, ";
      $sListaCampos .= " matricula.ed60_i_codigo as codigo_matricula ";
      $sSqlAlunosMatriculadosNaTurma  = $oDaoMatricula->sql_query("",
                                                               $sListaCampos,
                                                               "matricula.ed60_i_numaluno, to_ascii(ed47_v_nome)",
                                                               "ed60_i_turma = {$oParam->iCodigoTurma}
                                                               and ed221_i_serie = {$iCodigoSerie}"
                                                              );
      
      $aPeriodosDeAulaDoDia        = array();
      $iDiaDaSemana                = date('w', db_strtotime($oParam->dtAula)) + 1;
      

      /**
       * Busca codigo do rechumano
       */
      $sCampoRegente  = " distinct ed20_i_codigo ";
      $sWhereRegente  = "     (rh01_numcgm  = {$oParam->iRegente} or ed285_i_cgm = {$oParam->iRegente})";
      $sWhereRegente .= " and ed75_i_escola = {$iEscola}";
      $sWhereRegente .= " and ed75_i_saidaescola is null ";

      $sSqlRegente    = $oDaoRecHumano->sql_query_rechumano_cgm(null, $sCampoRegente, null, $sWhereRegente);
      $rsRegente      = $oDaoRecHumano->sql_record($sSqlRegente);

      if ($oDaoRecHumano->numrows == 0) {
        throw new Exception( _M( ARQUIVO_MENSAGEM . "erro_localizar_regente") );
      }
      $aRecursosHumanos = db_utils::getCollectionByRecord($rsRegente);
      $aCodigoRecursosHumanos = array();
      foreach ($aRecursosHumanos as $oRecursoHumano) {
        $aCodigoRecursosHumanos[] =  $oRecursoHumano->ed20_i_codigo;
      }
      $iCodigoRecHumano = implode(", ", $aCodigoRecursosHumanos);

      /**
       * Carregamos os periodos do dia.
       *
       */
      $sWhereHorarioAula  = " ed57_i_escola  = ".db_getsession("DB_coddepto");
      $sWhereHorarioAula .= " and ed57_i_codigo  = {$oParam->iCodigoTurma}";

      $sSqlHorarioAula = $oDaoControleAcesso->sql_query_horario_aula_turma(null,
                                                                           "min(ed17_h_inicio) as horainicio,
                                                                            max(ed17_h_fim) as horatermino",
                                                                           null,
                                                                           $sWhereHorarioAula
                                                                          );
      $rsHorarioAula    = $oDaoControleAcesso->sql_record($sSqlHorarioAula);
      if ($oDaoControleAcesso->numrows == 0) {
        throw new Exception( _M( ARQUIVO_MENSAGEM . "turma_sem_periodos") );
      }
      $sHoraInicioAula   = db_utils::fieldsMemory($rsHorarioAula, 0)->horainicio;
      $sHoraTerminoAula  = db_utils::fieldsMemory($rsHorarioAula ,0)->horatermino;


      /**
       * Busca os periodos de alua
       */
      $sWhere  = " ed57_i_escola    = {$iEscola} ";
      $sWhere .= " and ed52_i_ano   = ".db_getsession("DB_anousu");
      $sWhere .= " and ed232_i_codigo = {$oParam->iCodigoDisciplina}";
      $sWhere .= " and ed58_i_diasemana = {$iDiaDaSemana}";
      $sWhere .= " and ed59_i_turma     = {$oParam->iCodigoTurma}";
      $sWhere .= " and '{$oParam->dtAula}' between ed58_datainicio and ed58_datafim ";

      if ($iCodigoSerie != "") {
        $sWhere .= " and ed59_i_serie     = {$iCodigoSerie}";
      }

      $sWhereRegencia      = " {$sWhere} ";
      $sWhereRegencia     .= " and ed58_i_rechumano in ({$iCodigoRecHumano}) ";

      $sWhereSubstituicao  = " {$sWhere} ";
      $sWhereSubstituicao .= " and ed322_rechumano in({$iCodigoRecHumano})";

      $sCampos           = " ed58_i_codigo    as sequencial, ";
      $sCampos          .= " ed08_c_descr     as descricao_periodo, ";
      $sCampos          .= " ed58_i_codigo    as codigo_regencia_periodo, ";
      $sCampos          .= " ed17_i_codigo AS codigo_periodo_escola ";

      $sSqlPeriodosAula = $oDaoRegenciaHorario->sql_query_diario_classe_periodo_substituicao($sCampos, null,
                                                                                             $sWhereRegencia,
                                                                                             $sWhereSubstituicao
                                                                                            );
      $rsPeriodosAula  = $oDaoRegenciaHorario->sql_record($sSqlPeriodosAula);


      //var_dump($sSqlPeriodosAula); die("Confere");
      $oRetorno->aPeriodosAulaDia  = db_utils::getCollectionByRecord($rsPeriodosAula, false, false, true);
      $aPeriodosAula               = array();
      //echo "<pre>";
      //print_r($oRetorno->aPeriodosAulaDia);
      //echo "</pre>";
      //die("Confere ");
      foreach ($oRetorno->aPeriodosAulaDia as $oPeriodo) {

        $sWherePeriodoTurno = "ed143_periodoescola = {$oPeriodo->codigo_periodo_escola}";
        $sSqlPeriodoTurno   = $oDaoPeriodoEscolaTurnoReferente->sql_query(null, "ed231_i_referencia", null, $sWherePeriodoTurno);
        $rsPeriodoTurno     = db_query( $sSqlPeriodoTurno );


        if ( !$rsPeriodoTurno || pg_num_rows($rsPeriodoTurno) == 0 ) {
          throw new Exception( _M( ARQUIVO_MENSAGEM . "configure_turnos_referentes") );
        }

        $oPeriodo->aTurnosReferentes = array();

        for ( $iEquivalencias = 0; $iEquivalencias < pg_num_rows($rsPeriodoTurno); $iEquivalencias++ ) {
          $oPeriodo->aTurnosReferentes[] = db_utils::fieldsMemory($rsPeriodoTurno, $iEquivalencias)->ed231_i_referencia;
        }

        $aPeriodosAula[] = $oPeriodo->codigo_regencia_periodo;
      }
      /**
       * Pegamos os dados de aula do aluno
       */
      $rsAlunosMatriculadosNaTurma = $oDaoMatricula->sql_record($sSqlAlunosMatriculadosNaTurma);
      $aAlunosMatriculados         = array();
      for ($iAluno = 0; $iAluno < $oDaoMatricula->numrows; $iAluno++) {

        $oAluno                  = db_utils::fieldsMemory($rsAlunosMatriculadosNaTurma, $iAluno, false, false, true);
        $oInstanciaAluno         = AlunoRepository::getAlunoByCodigo( $oAluno->codigo );
        $oAluno->lTemNecessidade = count( $oInstanciaAluno->getNecessidadesEspeciais() ) > 0 ? true : false;

        $sWhereAcesso  = "     ed101_dataleitura = '{$oParam->dtAula}' ";
        $sWhereAcesso .= " and ed101_entrada is true ";
        $sWhereAcesso .= " and ed303_aluno = {$oAluno->codigo}";
        $sWhereAcesso .= " and not exists(select 1 ";
        $sWhereAcesso .= "                  From controleacessoalunoregistro as x ";
        $sWhereAcesso .= "                 where (x.ed101_dataleitura = '{$oParam->dtAula}' ";
        $sWhereAcesso .= "                   and x.ed101_horaleitura > controleacessoalunoregistro.ed101_horaleitura)";
        $sWhereAcesso .= "                   and ed101_entrada is false ";
        $sWhereAcesso .= "                   and ed303_aluno = {$oAluno->codigo})";

        $oAluno->acessoescola = ControleAcessoAluno::alunoEstaNaEscola($oAluno->codigo,
                                                                       $oParam->dtAula,
                                                                       $sHoraInicioAula,
                                                                       $sHoraTerminoAula
                                                                      );

        /**
         * Verificamos se existe falta para o Aluno em algum periodo no dia
         */

        $sWhereFaltas  = "     ed302_regenciahorario in (".implode(",", $aPeriodosAula).") ";
        $sWhereFaltas .= " and ed300_datalancamento = '{$oParam->dtAula}' ";
        $sWhereFaltas .= " and ed301_aluno          = {$oAluno->codigo} ";
        $sSqlAlunoFaltas = $oDaoDiarioClasseAlunoFalta->sql_query_aluno_falta(null,
                                                                               "ed302_regenciahorario as periodo",
                                                                                null,
                                                                                $sWhereFaltas
                                                                               );
        

        
        $aFaltas               = array();
        $rsAlunoFaltas         = $oDaoDiarioClasseAlunoFalta->sql_record($sSqlAlunoFaltas);
        $aAlunoFaltas          = db_utils::getCollectionByRecord($rsAlunoFaltas);

        

        //NOTA AQUI
        $dcmatricula2 = $oAluno->codigo;
        $dcdata2 = $oParam->dtAula;
        $dcregengia2 = $aPeriodosAula[0];
        $dcdisciplina2 = $oParam->iCodigoDisciplina;
        $dcturma2 = $oParam->iCodigoTurma;
        $dcverificanota = verificaNota3($dcmatricula2, $dcdata2, $dcregengia2);
        $pesquisaperiodo = buscaSeparacaoPeriodos($dcturma2, $dcdata2);
        $periodoi = $pesquisaperiodo["ed53_d_inicio"];
        $periodof = $pesquisaperiodo["ed53_d_fim"];
        $somatorio = buscaSomatorio($dcmatricula2, $dcdisciplina2, $dcturma2, $periodoi, $periodof);
        

        if($dcverificanota){          
          $oAluno->dcnota =  str_replace(".", ",", $dcverificanota["nota"]);          
          //$oAluno->somatorio = $somatorio;
        }else{
          $oAluno->dcnota = "";
          //$oAluno->somatorio = "";
        }

        $oAluno->somatorio = $somatorio;
        
        

        foreach ($aAlunoFaltas as $oFalta) {
          $aFaltas[] = $oFalta->periodo;
        }

        $oAluno->lBloqueioFalta = false;
        $oDataDia               = new DBDate($oParam->dtAula);
        $oDataMatricula         = new DBDate($oAluno->data_matricula);

        if ($oDataMatricula->getTimeStamp() > $oDataDia->getTimeStamp()) {
          $oAluno->lBloqueioFalta = true;
        }
        if (!empty($oAluno->data_saida)) {

          $oDataSaida = new DBDate($oAluno->data_saida);
          if ($oDataSaida->getTimeStamp() <  $oDataDia->getTimeStamp()) {
            $oAluno->lBloqueioFalta = true;
          }
        }
        unset($aAlunoFaltas);

        $oDaoMatriculaTurnoReferente   = new cl_matriculaturnoreferente();
        $sWhereMatriculaTurnoReferente = " ed336_turma = {$oParam->iCodigoTurma} AND ed337_matricula = {$oAluno->codigo_matricula}";
        $sSqlMatriculaTurnoReferente   = $oDaoMatriculaTurnoReferente->sql_query(null, "ed336_turnoreferente", null, $sWhereMatriculaTurnoReferente);
        $rsMatriculaTurnoReferente     = db_query($sSqlMatriculaTurnoReferente);

        if ( !$rsMatriculaTurnoReferente || pg_num_rows($rsMatriculaTurnoReferente) == 0) {          
          throw new DBException(_M(ARQUIVO_MENSAGEM . "erro_turnos_matricula_aluno"));
        }

        $oAluno->aTurnosReferentes = array();
        for ( $iTurnos = 0; $iTurnos < pg_num_rows($rsMatriculaTurnoReferente); $iTurnos++ ) {
          $oAluno->aTurnosReferentes[] =  db_utils::fieldsMemory($rsMatriculaTurnoReferente, $iTurnos)->ed336_turnoreferente;
        }

        $oAluno->faltas        = $aFaltas;
        $aAlunosMatriculados[] = $oAluno;
      }
      $oRetorno->aAlunos   = $aAlunosMatriculados;
      $oRetorno->sAulaData = '';
      /**
       * Retornamos os dados do diario de classe
       */
      $sWhereDiarioClasse  = "ed302_regenciahorario in (".implode(",", $aPeriodosAula).") ";
      $sWhereDiarioClasse .= " and ed300_datalancamento = '{$oParam->dtAula}' ";
      $sSqlDiarioClasse    = $oDaoDiarioClasseRegenciaHorario->sql_query(null,
                                                                         "distinct ed300_auladesenvolvida",
                                                                          null,
                                                                          $sWhereDiarioClasse
                                                                        );
      $rsDiarioClasse      = $oDaoDiarioClasseRegenciaHorario->sql_record($sSqlDiarioClasse);
      //var_dump($sSqlDiarioClasse);
      if ($oDaoDiarioClasseRegenciaHorario->numrows > 0) {
        $oRetorno->sAulaData = urlencode(db_utils::fieldsMemory($rsDiarioClasse, 0)->ed300_auladesenvolvida);
      }


    break;

    case 'getAlunosTurma2' :

      $oDaoMatricula                   = new cl_matricula();
      $oDaoRecHumano                   = new cl_rechumano();
      $oDaoRegenciaHorario             = new cl_regenciahorario();
      $oDaoDiarioClasseAlunoFalta      = new cl_diarioclassealunofalta();
      $oDaoDiarioClasseRegenciaHorario = new cl_diarioclasseregenciahorario();
      $oDaoControleAcessoAluno         = new cl_controleacessoalunoregistro();
      $oDaoControleAcesso              = new cl_controleacessoaluno();
      $oDaoPeriodoEscolaTurnoReferente = new cl_periodoescolaturnoreferente();

      $aPartesTurma  = explode("_", $oParam->iCodigoTurma);
      $iCodigoSerie  = '';
      if (isset($aPartesTurma[1])) {
       $iCodigoSerie  = $aPartesTurma[1];
      }
      $oParam->iCodigoTurma  = $aPartesTurma[0];
      $sListaCampos  = " aluno.ed47_v_nome as  nome, ";
      $sListaCampos .= " aluno.ed47_i_codigo as codigo, ";
      $sListaCampos .= " matricula.ed60_i_numaluno as ordem_turma, ";
      $sListaCampos .= " matricula.ed60_c_situacao as situacao, ";
      $sListaCampos .= " matricula.ed60_d_datamatricula as data_matricula, ";
      $sListaCampos .= " matricula.ed60_d_datasaida data_saida, ";
      $sListaCampos .= " matricula.ed60_i_codigo as codigo_matricula ";
      $sSqlAlunosMatriculadosNaTurma  = $oDaoMatricula->sql_query("",
                                                               $sListaCampos,
                                                               "matricula.ed60_i_numaluno, to_ascii(ed47_v_nome)",
                                                               "ed60_i_turma = {$oParam->iCodigoTurma}
                                                               and ed221_i_serie = {$iCodigoSerie}"
                                                              );
      
      $aPeriodosDeAulaDoDia        = array();
      $iDiaDaSemana                = date('w', db_strtotime($oParam->dtAula)) + 1;
      

      /**
       * Busca codigo do rechumano
       */
      $sCampoRegente  = " distinct ed20_i_codigo ";
      $sWhereRegente  = "     (rh01_numcgm  = {$oParam->iRegente} or ed285_i_cgm = {$oParam->iRegente})";
      $sWhereRegente .= " and ed75_i_escola = {$iEscola}";
      $sWhereRegente .= " and ed75_i_saidaescola is null ";

      $sSqlRegente    = $oDaoRecHumano->sql_query_rechumano_cgm(null, $sCampoRegente, null, $sWhereRegente);
      $rsRegente      = $oDaoRecHumano->sql_record($sSqlRegente);

      if ($oDaoRecHumano->numrows == 0) {
        throw new Exception( _M( ARQUIVO_MENSAGEM . "erro_localizar_regente") );
      }
      $aRecursosHumanos = db_utils::getCollectionByRecord($rsRegente);
      $aCodigoRecursosHumanos = array();
      foreach ($aRecursosHumanos as $oRecursoHumano) {
        $aCodigoRecursosHumanos[] =  $oRecursoHumano->ed20_i_codigo;
      }
      $iCodigoRecHumano = implode(", ", $aCodigoRecursosHumanos);

      /**
       * Carregamos os periodos do dia.
       *
       */
      $sWhereHorarioAula  = " ed57_i_escola  = ".db_getsession("DB_coddepto");
      $sWhereHorarioAula .= " and ed57_i_codigo  = {$oParam->iCodigoTurma}";

      $sSqlHorarioAula = $oDaoControleAcesso->sql_query_horario_aula_turma(null,
                                                                           "min(ed17_h_inicio) as horainicio,
                                                                            max(ed17_h_fim) as horatermino",
                                                                           null,
                                                                           $sWhereHorarioAula
                                                                          );
      $rsHorarioAula    = $oDaoControleAcesso->sql_record($sSqlHorarioAula);
      if ($oDaoControleAcesso->numrows == 0) {
        throw new Exception( _M( ARQUIVO_MENSAGEM . "turma_sem_periodos") );
      }
      $sHoraInicioAula   = db_utils::fieldsMemory($rsHorarioAula, 0)->horainicio;
      $sHoraTerminoAula  = db_utils::fieldsMemory($rsHorarioAula ,0)->horatermino;


      /**
       * Busca os periodos de alua
       */
      $sWhere  = " ed57_i_escola    = {$iEscola} ";
      $sWhere .= " and ed52_i_ano   = ".db_getsession("DB_anousu");
      $sWhere .= " and ed232_i_codigo = {$oParam->iCodigoDisciplina}";
      //$sWhere .= " and ed58_i_diasemana = {$iDiaDaSemana}";
      $sWhere .= " and ed59_i_turma     = {$oParam->iCodigoTurma}";
      $sWhere .= " and '{$oParam->dtAula}' between ed58_datainicio and ed58_datafim ";

      if ($iCodigoSerie != "") {
        $sWhere .= " and ed59_i_serie     = {$iCodigoSerie}";
      }

      $sWhereRegencia      = " {$sWhere} ";
      $sWhereRegencia     .= " and ed58_i_rechumano in ({$iCodigoRecHumano}) ";

      $sWhereSubstituicao  = " {$sWhere} ";
      $sWhereSubstituicao .= " and ed322_rechumano in({$iCodigoRecHumano})";

      $sCampos           = " ed58_i_codigo    as sequencial, ";
      $sCampos          .= " ed08_c_descr     as descricao_periodo, ";
      $sCampos          .= " ed58_i_codigo    as codigo_regencia_periodo, ";
      $sCampos          .= " ed17_i_codigo AS codigo_periodo_escola ";

      $sSqlPeriodosAula = $oDaoRegenciaHorario->sql_query_diario_classe_periodo_substituicao($sCampos, null,
                                                                                             $sWhereRegencia,
                                                                                             $sWhereSubstituicao
                                                                                            );
      $rsPeriodosAula  = $oDaoRegenciaHorario->sql_record($sSqlPeriodosAula);


      //var_dump($sSqlPeriodosAula); die("Confere");
      $oRetorno->aPeriodosAulaDia  = db_utils::getCollectionByRecord($rsPeriodosAula, false, false, true);
      $aPeriodosAula               = array();
      //echo "<pre>";
      //print_r($oRetorno->aPeriodosAulaDia);
      //echo "</pre>";
      //die("Confere ");
      foreach ($oRetorno->aPeriodosAulaDia as $oPeriodo) {

        $sWherePeriodoTurno = "ed143_periodoescola = {$oPeriodo->codigo_periodo_escola}";
        $sSqlPeriodoTurno   = $oDaoPeriodoEscolaTurnoReferente->sql_query(null, "ed231_i_referencia", null, $sWherePeriodoTurno);
        $rsPeriodoTurno     = db_query( $sSqlPeriodoTurno );


        if ( !$rsPeriodoTurno || pg_num_rows($rsPeriodoTurno) == 0 ) {
          throw new Exception( _M( ARQUIVO_MENSAGEM . "configure_turnos_referentes") );
        }

        $oPeriodo->aTurnosReferentes = array();

        for ( $iEquivalencias = 0; $iEquivalencias < pg_num_rows($rsPeriodoTurno); $iEquivalencias++ ) {
          $oPeriodo->aTurnosReferentes[] = db_utils::fieldsMemory($rsPeriodoTurno, $iEquivalencias)->ed231_i_referencia;
        }

        $aPeriodosAula[] = $oPeriodo->codigo_regencia_periodo;
      }
      /**
       * Pegamos os dados de aula do aluno
       */
      $rsAlunosMatriculadosNaTurma = $oDaoMatricula->sql_record($sSqlAlunosMatriculadosNaTurma);
      $aAlunosMatriculados         = array();
      for ($iAluno = 0; $iAluno < $oDaoMatricula->numrows; $iAluno++) {

        $oAluno                  = db_utils::fieldsMemory($rsAlunosMatriculadosNaTurma, $iAluno, false, false, true);
        $oInstanciaAluno         = AlunoRepository::getAlunoByCodigo( $oAluno->codigo );
        $oAluno->lTemNecessidade = count( $oInstanciaAluno->getNecessidadesEspeciais() ) > 0 ? true : false;

        $sWhereAcesso  = "     ed101_dataleitura = '{$oParam->dtAula}' ";
        $sWhereAcesso .= " and ed101_entrada is true ";
        $sWhereAcesso .= " and ed303_aluno = {$oAluno->codigo}";
        $sWhereAcesso .= " and not exists(select 1 ";
        $sWhereAcesso .= "                  From controleacessoalunoregistro as x ";
        $sWhereAcesso .= "                 where (x.ed101_dataleitura = '{$oParam->dtAula}' ";
        $sWhereAcesso .= "                   and x.ed101_horaleitura > controleacessoalunoregistro.ed101_horaleitura)";
        $sWhereAcesso .= "                   and ed101_entrada is false ";
        $sWhereAcesso .= "                   and ed303_aluno = {$oAluno->codigo})";

        $oAluno->acessoescola = ControleAcessoAluno::alunoEstaNaEscola($oAluno->codigo,
                                                                       $oParam->dtAula,
                                                                       $sHoraInicioAula,
                                                                       $sHoraTerminoAula
                                                                      );

        /**
         * Verificamos se existe falta para o Aluno em algum periodo no dia
         */

        $sWhereFaltas  = "     ed302_regenciahorario in (".implode(",", $aPeriodosAula).") ";
        $sWhereFaltas .= " and ed300_datalancamento = '{$oParam->dtAula}' ";
        $sWhereFaltas .= " and ed301_aluno          = {$oAluno->codigo} ";
        $sSqlAlunoFaltas = $oDaoDiarioClasseAlunoFalta->sql_query_aluno_falta(null,
                                                                               "ed302_regenciahorario as periodo",
                                                                                null,
                                                                                $sWhereFaltas
                                                                               );
        

        
        $aFaltas               = array();
        $rsAlunoFaltas         = $oDaoDiarioClasseAlunoFalta->sql_record($sSqlAlunoFaltas);
        $aAlunoFaltas          = db_utils::getCollectionByRecord($rsAlunoFaltas);

        

        //NOTA AQUI
        $dcmatricula2 = $oAluno->codigo;
        $dcdata2 = $oParam->dtAula;
        $dcregengia2 = $aPeriodosAula[0];
        $dcdisciplina2 = $oParam->iCodigoDisciplina;
        $dcturma2 = $oParam->iCodigoTurma;
        $dcverificanota = verificaNota3($dcmatricula2, $dcdata2, $dcregengia2);
        $pesquisaperiodo = buscaSeparacaoPeriodos($dcturma2, $dcdata2);
        $periodoi = $pesquisaperiodo["ed53_d_inicio"];
        $periodof = $pesquisaperiodo["ed53_d_fim"];
        $somatorio = buscaSomatorio($dcmatricula2, $dcdisciplina2, $dcturma2, $periodoi, $periodof);
        

        if($dcverificanota){          
          $oAluno->dcnota =  str_replace(".", ",", $dcverificanota["nota"]);          
          //$oAluno->somatorio = $somatorio;
        }else{
          $oAluno->dcnota = "";
          //$oAluno->somatorio = "";
        }

        $oAluno->somatorio = $somatorio;
        
        

        foreach ($aAlunoFaltas as $oFalta) {
          $aFaltas[] = $oFalta->periodo;
        }

        $oAluno->lBloqueioFalta = false;
        $oDataDia               = new DBDate($oParam->dtAula);
        $oDataMatricula         = new DBDate($oAluno->data_matricula);

        if ($oDataMatricula->getTimeStamp() > $oDataDia->getTimeStamp()) {
          $oAluno->lBloqueioFalta = true;
        }
        if (!empty($oAluno->data_saida)) {

          $oDataSaida = new DBDate($oAluno->data_saida);
          if ($oDataSaida->getTimeStamp() <  $oDataDia->getTimeStamp()) {
            $oAluno->lBloqueioFalta = true;
          }
        }
        unset($aAlunoFaltas);

        $oDaoMatriculaTurnoReferente   = new cl_matriculaturnoreferente();
        $sWhereMatriculaTurnoReferente = " ed336_turma = {$oParam->iCodigoTurma} AND ed337_matricula = {$oAluno->codigo_matricula}";
        $sSqlMatriculaTurnoReferente   = $oDaoMatriculaTurnoReferente->sql_query(null, "ed336_turnoreferente", null, $sWhereMatriculaTurnoReferente);
        $rsMatriculaTurnoReferente     = db_query($sSqlMatriculaTurnoReferente);

        if ( !$rsMatriculaTurnoReferente || pg_num_rows($rsMatriculaTurnoReferente) == 0) {
          throw new DBException(_M(ARQUIVO_MENSAGEM . "erro_turnos_matricula_aluno"));
        }

        $oAluno->aTurnosReferentes = array();
        for ( $iTurnos = 0; $iTurnos < pg_num_rows($rsMatriculaTurnoReferente); $iTurnos++ ) {
          $oAluno->aTurnosReferentes[] =  db_utils::fieldsMemory($rsMatriculaTurnoReferente, $iTurnos)->ed336_turnoreferente;
        }

        $oAluno->faltas        = $aFaltas;
        $aAlunosMatriculados[] = $oAluno;
      }
      $oRetorno->aAlunos   = $aAlunosMatriculados;
      $oRetorno->sAulaData = '';
      /**
       * Retornamos os dados do diario de classe
       */
      $sWhereDiarioClasse  = "ed302_regenciahorario in (".implode(",", $aPeriodosAula).") ";
      $sWhereDiarioClasse .= " and ed300_datalancamento = '{$oParam->dtAula}' ";
      $sSqlDiarioClasse    = $oDaoDiarioClasseRegenciaHorario->sql_query(null,
                                                                         "distinct ed300_auladesenvolvida",
                                                                          null,
                                                                          $sWhereDiarioClasse
                                                                        );
      $rsDiarioClasse      = $oDaoDiarioClasseRegenciaHorario->sql_record($sSqlDiarioClasse);
      //var_dump($sSqlDiarioClasse);
      if ($oDaoDiarioClasseRegenciaHorario->numrows > 0) {
        $oRetorno->sAulaData = urlencode(db_utils::fieldsMemory($rsDiarioClasse, 0)->ed300_auladesenvolvida);
      }


    break;

    case 'getAlunosTurmaRec' :

      $oDaoMatricula                   = new cl_matricula();
      $oDaoRecHumano                   = new cl_rechumano();
      $oDaoRegenciaHorario             = new cl_regenciahorario();
      $oDaoDiarioClasseAlunoFalta      = new cl_diarioclassealunofalta();
      $oDaoDiarioClasseRegenciaHorario = new cl_diarioclasseregenciahorario();
      $oDaoControleAcessoAluno         = new cl_controleacessoalunoregistro();
      $oDaoControleAcesso              = new cl_controleacessoaluno();
      $oDaoPeriodoEscolaTurnoReferente = new cl_periodoescolaturnoreferente();

      $aPartesTurma  = explode("_", $oParam->iCodigoTurma);
      $iCodigoSerie  = '';
      if (isset($aPartesTurma[1])) {
       $iCodigoSerie  = $aPartesTurma[1];
      }
      $oParam->iCodigoTurma  = $aPartesTurma[0];
      $sListaCampos  = " aluno.ed47_v_nome as  nome, ";
      $sListaCampos .= " aluno.ed47_i_codigo as codigo, ";
      $sListaCampos .= " matricula.ed60_i_numaluno as ordem_turma, ";
      $sListaCampos .= " matricula.ed60_c_situacao as situacao, ";
      $sListaCampos .= " matricula.ed60_d_datamatricula as data_matricula, ";
      $sListaCampos .= " matricula.ed60_d_datasaida data_saida, ";
      $sListaCampos .= " matricula.ed60_i_codigo as codigo_matricula ";
      $sSqlAlunosMatriculadosNaTurma  = $oDaoMatricula->sql_query("",
                                                               $sListaCampos,
                                                               "matricula.ed60_i_numaluno, to_ascii(ed47_v_nome)",
                                                               "ed60_i_turma = {$oParam->iCodigoTurma}
                                                               and ed221_i_serie = {$iCodigoSerie}"
                                                              );
      $aPeriodosDeAulaDoDia        = array();
      $iDiaDaSemana                = date('w', db_strtotime($oParam->dtAula)) + 1;


      /**
       * Busca codigo do rechumano
       */
      $sCampoRegente  = " distinct ed20_i_codigo ";
      $sWhereRegente  = "     (rh01_numcgm  = {$oParam->iRegente} or ed285_i_cgm = {$oParam->iRegente})";
      $sWhereRegente .= " and ed75_i_escola = {$iEscola}";
      $sWhereRegente .= " and ed75_i_saidaescola is null ";

      $sSqlRegente    = $oDaoRecHumano->sql_query_rechumano_cgm(null, $sCampoRegente, null, $sWhereRegente);
      $rsRegente      = $oDaoRecHumano->sql_record($sSqlRegente);

      if ($oDaoRecHumano->numrows == 0) {
        throw new Exception( _M( ARQUIVO_MENSAGEM . "erro_localizar_regente") );
      }
      $aRecursosHumanos = db_utils::getCollectionByRecord($rsRegente);
      $aCodigoRecursosHumanos = array();
      foreach ($aRecursosHumanos as $oRecursoHumano) {
        $aCodigoRecursosHumanos[] =  $oRecursoHumano->ed20_i_codigo;
      }
      $iCodigoRecHumano = implode(", ", $aCodigoRecursosHumanos);

      /**
       * Carregamos os periodos do dia.
       *
       */
      $sWhereHorarioAula  = " ed57_i_escola  = ".db_getsession("DB_coddepto");
      $sWhereHorarioAula .= " and ed57_i_codigo  = {$oParam->iCodigoTurma}";

      $sSqlHorarioAula = $oDaoControleAcesso->sql_query_horario_aula_turma(null,
                                                                           "min(ed17_h_inicio) as horainicio,
                                                                            max(ed17_h_fim) as horatermino",
                                                                           null,
                                                                           $sWhereHorarioAula
                                                                          );
      $rsHorarioAula    = $oDaoControleAcesso->sql_record($sSqlHorarioAula);
      if ($oDaoControleAcesso->numrows == 0) {
        throw new Exception( _M( ARQUIVO_MENSAGEM . "turma_sem_periodos") );
      }
      $sHoraInicioAula   = db_utils::fieldsMemory($rsHorarioAula, 0)->horainicio;
      $sHoraTerminoAula  = db_utils::fieldsMemory($rsHorarioAula ,0)->horatermino;


      /**
       * Busca os periodos de alua
       */
      $sWhere  = " ed57_i_escola    = {$iEscola} ";
      $sWhere .= " and ed52_i_ano   = ".db_getsession("DB_anousu");
      $sWhere .= " and ed232_i_codigo = {$oParam->iCodigoDisciplina}";
      $sWhere .= " and ed58_i_diasemana = {$iDiaDaSemana}";
      $sWhere .= " and ed59_i_turma     = {$oParam->iCodigoTurma}";
      $sWhere .= " and '{$oParam->dtAula}' between ed58_datainicio and ed58_datafim ";

      if ($iCodigoSerie != "") {
        $sWhere .= " and ed59_i_serie     = {$iCodigoSerie}";
      }

      $sWhereRegencia      = " {$sWhere} ";
      $sWhereRegencia     .= " and ed58_i_rechumano in ({$iCodigoRecHumano}) ";

      $sWhereSubstituicao  = " {$sWhere} ";
      $sWhereSubstituicao .= " and ed322_rechumano in({$iCodigoRecHumano})";

      $sCampos           = " ed58_i_codigo    as sequencial, ";
      $sCampos          .= " ed08_c_descr     as descricao_periodo, ";
      $sCampos          .= " ed58_i_codigo    as codigo_regencia_periodo, ";
      $sCampos          .= " ed17_i_codigo AS codigo_periodo_escola ";

      $sSqlPeriodosAula = $oDaoRegenciaHorario->sql_query_diario_classe_periodo_substituicao($sCampos, null,
                                                                                             $sWhereRegencia,
                                                                                             $sWhereSubstituicao
                                                                                            );
      $rsPeriodosAula  = $oDaoRegenciaHorario->sql_record($sSqlPeriodosAula);


      $oRetorno->aPeriodosAulaDia  = db_utils::getCollectionByRecord($rsPeriodosAula, false, false, true);
      $aPeriodosAula               = array();
      foreach ($oRetorno->aPeriodosAulaDia as $oPeriodo) {

        $sWherePeriodoTurno = "ed143_periodoescola = {$oPeriodo->codigo_periodo_escola}";
        $sSqlPeriodoTurno   = $oDaoPeriodoEscolaTurnoReferente->sql_query(null, "ed231_i_referencia", null, $sWherePeriodoTurno);
        $rsPeriodoTurno     = db_query( $sSqlPeriodoTurno );

        if ( !$rsPeriodoTurno || pg_num_rows($rsPeriodoTurno) == 0 ) {
          throw new Exception( _M( ARQUIVO_MENSAGEM . "configure_turnos_referentes") );
        }

        $oPeriodo->aTurnosReferentes = array();

        for ( $iEquivalencias = 0; $iEquivalencias < pg_num_rows($rsPeriodoTurno); $iEquivalencias++ ) {
          $oPeriodo->aTurnosReferentes[] = db_utils::fieldsMemory($rsPeriodoTurno, $iEquivalencias)->ed231_i_referencia;
        }

        $aPeriodosAula[] = $oPeriodo->codigo_regencia_periodo;
      }
      /**
       * Pegamos os dados de aula do aluno
       */
      $rsAlunosMatriculadosNaTurma = $oDaoMatricula->sql_record($sSqlAlunosMatriculadosNaTurma);
      $aAlunosMatriculados         = array();
      for ($iAluno = 0; $iAluno < $oDaoMatricula->numrows; $iAluno++) {

        $oAluno                  = db_utils::fieldsMemory($rsAlunosMatriculadosNaTurma, $iAluno, false, false, true);
        $oInstanciaAluno         = AlunoRepository::getAlunoByCodigo( $oAluno->codigo );
        $oAluno->lTemNecessidade = count( $oInstanciaAluno->getNecessidadesEspeciais() ) > 0 ? true : false;

        $sWhereAcesso  = "     ed101_dataleitura = '{$oParam->dtAula}' ";
        $sWhereAcesso .= " and ed101_entrada is true ";
        $sWhereAcesso .= " and ed303_aluno = {$oAluno->codigo}";
        $sWhereAcesso .= " and not exists(select 1 ";
        $sWhereAcesso .= "                  From controleacessoalunoregistro as x ";
        $sWhereAcesso .= "                 where (x.ed101_dataleitura = '{$oParam->dtAula}' ";
        $sWhereAcesso .= "                   and x.ed101_horaleitura > controleacessoalunoregistro.ed101_horaleitura)";
        $sWhereAcesso .= "                   and ed101_entrada is false ";
        $sWhereAcesso .= "                   and ed303_aluno = {$oAluno->codigo})";

        $oAluno->acessoescola = ControleAcessoAluno::alunoEstaNaEscola($oAluno->codigo,
                                                                       $oParam->dtAula,
                                                                       $sHoraInicioAula,
                                                                       $sHoraTerminoAula
                                                                      );

        /**
         * Verificamos se existe falta para o Aluno em algum periodo no dia
         */

        $sWhereFaltas  = "     ed302_regenciahorario in (".implode(",", $aPeriodosAula).") ";
        $sWhereFaltas .= " and ed300_datalancamento = '{$oParam->dtAula}' ";
        $sWhereFaltas .= " and ed301_aluno          = {$oAluno->codigo} ";
        $sSqlAlunoFaltas = $oDaoDiarioClasseAlunoFalta->sql_query_aluno_falta(null,
                                                                               "ed302_regenciahorario as periodo",
                                                                                null,
                                                                                $sWhereFaltas
                                                                               );
        

        
        $aFaltas               = array();
        $rsAlunoFaltas         = $oDaoDiarioClasseAlunoFalta->sql_record($sSqlAlunoFaltas);
        $aAlunoFaltas          = db_utils::getCollectionByRecord($rsAlunoFaltas);

        //NOTA AQUI
        $dcmatricula2 = $oAluno->codigo;
        $dcdata2 = $oParam->dtAula;
        $dcregengia2 = $aPeriodosAula[0];
        $dcverificanota = verificaNota3Rec($dcmatricula2, $dcdata2, $dcregengia2);
        if($dcverificanota){
          $oAluno->dcnota =  str_replace(".", ",", $dcverificanota["nota"]);
        }else{
          $oAluno->dcnota = "";  
        }

        

        foreach ($aAlunoFaltas as $oFalta) {
          $aFaltas[] = $oFalta->periodo;
        }

        $oAluno->lBloqueioFalta = false;
        $oDataDia               = new DBDate($oParam->dtAula);
        $oDataMatricula         = new DBDate($oAluno->data_matricula);

        if ($oDataMatricula->getTimeStamp() > $oDataDia->getTimeStamp()) {
          $oAluno->lBloqueioFalta = true;
        }
        if (!empty($oAluno->data_saida)) {

          $oDataSaida = new DBDate($oAluno->data_saida);
          if ($oDataSaida->getTimeStamp() <  $oDataDia->getTimeStamp()) {
            $oAluno->lBloqueioFalta = true;
          }
        }
        unset($aAlunoFaltas);

        $oDaoMatriculaTurnoReferente   = new cl_matriculaturnoreferente();
        $sWhereMatriculaTurnoReferente = " ed336_turma = {$oParam->iCodigoTurma} AND ed337_matricula = {$oAluno->codigo_matricula}";
        $sSqlMatriculaTurnoReferente   = $oDaoMatriculaTurnoReferente->sql_query(null, "ed336_turnoreferente", null, $sWhereMatriculaTurnoReferente);
        $rsMatriculaTurnoReferente     = db_query($sSqlMatriculaTurnoReferente);

        if ( !$rsMatriculaTurnoReferente || pg_num_rows($rsMatriculaTurnoReferente) == 0) {
          throw new DBException(_M(ARQUIVO_MENSAGEM . "erro_turnos_matricula_aluno"));
        }

        $oAluno->aTurnosReferentes = array();
        for ( $iTurnos = 0; $iTurnos < pg_num_rows($rsMatriculaTurnoReferente); $iTurnos++ ) {
          $oAluno->aTurnosReferentes[] =  db_utils::fieldsMemory($rsMatriculaTurnoReferente, $iTurnos)->ed336_turnoreferente;
        }

        $oAluno->faltas        = $aFaltas;
        $aAlunosMatriculados[] = $oAluno;
      }
      $oRetorno->aAlunos   = $aAlunosMatriculados;
      $oRetorno->sAulaData = '';
      /**
       * Retornamos os dados do diario de classe
       */
      $sWhereDiarioClasse  = "ed302_regenciahorario in (".implode(",", $aPeriodosAula).") ";
      $sWhereDiarioClasse .= " and ed300_datalancamento = '{$oParam->dtAula}' ";
      $sSqlDiarioClasse    = $oDaoDiarioClasseRegenciaHorario->sql_query(null,
                                                                         "distinct ed300_auladesenvolvida",
                                                                          null,
                                                                          $sWhereDiarioClasse
                                                                        );
      $rsDiarioClasse      = $oDaoDiarioClasseRegenciaHorario->sql_record($sSqlDiarioClasse);
      if ($oDaoDiarioClasseRegenciaHorario->numrows > 0) {
        $oRetorno->sAulaData = urlencode(db_utils::fieldsMemory($rsDiarioClasse, 0)->ed300_auladesenvolvida);
      }


    break;

    case 'salvarDiarioClasse' : 
      //echo "<pre>";
      //print_r($oParam);
      //echo "</pre>";
      //die("Confere");
      
      /**
       * Pesquisamos quais os registros devemos excluir e lançar novamente.
       */

      db_inicio_transacao();
      $oDaoDiarioClasseAlunoFalta      = db_utils::getDao("diarioclassealunofalta");
      $oDaoDiarioClasse                = db_utils::getDao("diarioclasse");
      $oDaoDiarioClasseRegenciaHorario = db_utils::getDao("diarioclasseregenciahorario");
      $oDaoRegenciaHorario             = db_utils::getDao("regenciahorario");
      $sCodigoRegencias                = implode(",", $oParam->aRegencias);

      /**
       * Buscamos a turma pelo codigo das Regencias;
       */
      $sWhereTurma = "ed58_i_codigo in({$sCodigoRegencias})";
      $sSqlTurma   = $oDaoRegenciaHorario->sql_query(null, "distinct ed57_i_codigo, ed58_i_regencia, ed59_i_serie",
                                                     null,
                                                     $sWhereTurma);
      $rsTurma     = $oDaoRegenciaHorario->sql_record($sSqlTurma);
      if ($oDaoRegenciaHorario->numrows != 1) {

        throw new Exception("Turma com regencias configuradas incorretamente!");
      }
      $oDadosRegencia    = db_utils::fieldsMemory($rsTurma, 0);
      $iCodigoDaTurma    = $oDadosRegencia->ed57_i_codigo;

      $oRegencia         = new Regencia($oDadosRegencia->ed58_i_regencia);
      $oTurma            = new Turma($iCodigoDaTurma);
      $oEtapaOrigem      = new Etapa($oDadosRegencia->ed59_i_serie);
      $aPeriodoAvalicao  = $oTurma->getCalendario()->getPeriodoPorData(new DBDate($oParam->dtAula));

      
      //8 aqui
      //var_dump($oDadosRegencia->ed58_i_regencia);

      //ed95_i_regencia = 8 AND ed72_i_procavaliacao = 39;

      if (count($aPeriodoAvalicao) > 1 || count($aPeriodoAvalicao) == 0) {
        throw new Exception("Existem periodos com datas inconsistentes para o Calendario {$oTurma->getCalendario()->getDescricao()}!");
      }
      $sWhere            = "ed300_datalancamento = '{$oParam->dtAula}' ";
      $sWhere           .= "and ed302_regenciahorario in({$sCodigoRegencias})";
      $sSqlDiarioClasse  = $oDaoDiarioClasse->sql_query_faltas(null,
                                                               "distinct ed300_sequencial,
                                                                ed302_sequencial,
                                                                ed302_regenciahorario
                                                                ",
                                                                null,
                                                                $sWhere
                                                              );
      /**
       * @todo Deletar apenas as faltas dos alunos
       */
       $iCodigoDiario  = '';
       $rsDiarioClasse = $oDaoDiarioClasse->sql_record($sSqlDiarioClasse);
       $aDiarios       = db_utils::getCollectionByRecord($rsDiarioClasse);

       foreach ($aDiarios as $oDiario) {

         $iCodigoDiario                                     = $oDiario->ed300_sequencial;
         $aCodigosRegencia[$oDiario->ed302_regenciahorario] = $oDiario->ed302_sequencial;
       }

      /**
       * Incluimos os dados da Falta
       */

      $oDaoDiarioClasse->ed300_datalancamento   = $oParam->dtAula;
      $oDaoDiarioClasse->ed300_auladesenvolvida = db_stdClass::normalizeStringJson($oParam->sAulaDesenvolvida);
      $oDaoDiarioClasse->ed300_hora             = db_hora();
      $oDaoDiarioClasse->ed300_id_usuario       = db_getsession("DB_id_usuario");
      if ($iCodigoDiario == "") {

        $oDaoDiarioClasse->incluir(null);
      } else {

        $oDaoDiarioClasse->ed300_sequencial = $iCodigoDiario;
        $oDaoDiarioClasse->alterar($iCodigoDiario);
      }
      if ($oDaoDiarioClasse->erro_status == 0) {

        $sMsg  = "Erro ao salvar dados do Diário de classe.\n";
        $sMsg .= "Erro Sistema: {$oDaoDiarioClasse->erro_msg}";
        throw new Exception($sMsg);
      }

      /**
       * Persistimos os dados da periodo da aula
       */
      if ($iCodigoDiario == "" ) {

        $aCodigosRegencia = array();
        foreach ($oParam->aRegencias as $iRegencia) {

          $oDaoDiarioClasseRegenciaHorario->ed302_diarioclasse    = $oDaoDiarioClasse->ed300_sequencial;
          $oDaoDiarioClasseRegenciaHorario->ed302_regenciahorario = $iRegencia;
          $oDaoDiarioClasseRegenciaHorario->incluir(null);
          if ($oDaoDiarioClasseRegenciaHorario->erro_status == 0) {

            $sMsg  = "Erro ao salvar dados do Diário de classe.\n";
            $sMsg .= "Erro Sistema: {$oDaoDiarioClasseRegenciaHorario->erro_msg}";
            throw new Exception($sMsg);
          }
          $aCodigosRegencia[$iRegencia] = $oDaoDiarioClasseRegenciaHorario->ed302_sequencial;
        }
      }

      /*
       * Persistimos os dados da Falta;
       */
      foreach ($oParam->aAlunos as $oAlunoFalta) {


        /**
         * Verificamos se existe alguma falta lançada no dia
         */
        $sWhere          = "ed301_aluno = {$oAlunoFalta->iCodigo}";
        $sWhere         .= " and ed300_datalancamento = '{$oParam->dtAula}'";
        $sWhere         .= " and ed302_regenciahorario in(".implode(",", $oParam->aRegencias).")";
        $sSqlFaltasNoDia = $oDaoDiarioClasseAlunoFalta->sql_query_aluno_falta(null,
                                                                              "ed58_i_codigo,
                                                                              ed301_sequencial",
                                                                              null,
                                                                              $sWhere
                                                                              );

        $rsFaltasNoDia  = $oDaoDiarioClasseAlunoFalta->sql_record($sSqlFaltasNoDia);
        $aFaltasNoDia   = db_utils::getCollectionByRecord($rsFaltasNoDia);


        


        //var_dump($aFaltasNoDia);
        foreach ($aFaltasNoDia as $oFalta) {

          /**
           * Verificamos se a falta existe no dia.
           */
          if (in_array($oFalta->ed58_i_codigo, $oAlunoFalta->faltas)) {
            
            /**
             * falta já lançada, não incluimos novamente
             */
            array_splice($oAlunoFalta->faltas, array_search($oFalta->ed58_i_codigo, $oAlunoFalta->faltas), 1);
          } else {

            /**
             * a falta foi dada como presenca.devemos excluir a mesma
             */
            $oDaoOcorrenciaFalta = db_utils::getDao("ocorrenciafalta");
            $oDaoOcorrenciaFalta->excluir(null, "ed104_diarioclassealunofalta = {$oFalta->ed301_sequencial}");
            if ($oDaoOcorrenciaFalta->erro_status == 0) {

              $sMensagemErro   = "Falta não excluída.\\n ";
              $sMensagemErro  .= "Erro Técnico : {$oDaoOcorrenciaFalta->erro_msg}";
              throw new BusinessException($sMensagemErro);
            }

            $oDaoDiarioClasseAlunoFalta->excluir($oFalta->ed301_sequencial);
            if ($oDaoDiarioClasse->erro_status == 0) {

              $sMensagemErro   = "Erro ao excluir faltas do aluno.\\n ";
              $sMensagemErro  .= "Erro Técnico : {$oDaoDiarioClasse->erro_msg}";
              throw new BusinessException($sMensagemErro);
            }
          }
        }

        foreach ($oAlunoFalta->faltas as $iPeriodoFalta) {

          $oDaoDiarioClasseAlunoFalta->ed301_aluno                       = $oAlunoFalta->iCodigo;
          $oDaoDiarioClasseAlunoFalta->ed301_diarioclasseregenciahorario = $aCodigosRegencia[$iPeriodoFalta];
          $oDaoDiarioClasseAlunoFalta->incluir(null);
          if ($oDaoDiarioClasseAlunoFalta->erro_status == 0) {

            $sMsg  = "Erro ao salvar dados do aluno.\n";
            $sMsg .= "Erro Sistema: {$oDaoDiarioClasseAlunoFalta->erro_msg}";
            throw new Exception($sMsg);
          }
        }//fim do foreach
      }



    //===========================================================================
    //NOTAS AQUI
      //testa($oRegencia);
      //testa($oTurma);
      //testa($oParam); die("Confere 1"); 
      /*
      $alunonota = array();
      $jaexiste = false;
      foreach ($oParam->aAlunos as $linha){        
        //var_dump($oDadosRegencia->ed58_i_regencia); 8
        $dcmatricula = $linha->iCodigo;        
        $dcnota = str_replace(",", ".", $linha->dcnotas);
        if($dcnota == ""){
          $dcnota = 0;
        }
        $dcdata = $oParam->dtAula;
        $dcregencia = $oParam->aRegencias[0];
        $dcdiscplina = $oParam->iDisciplina;
        $dcturma = explode("_", $oParam->iTurma);
        $dcturma = $dcturma[0];
        
        $verifica = verificaNota3($dcmatricula, $dcdata, $dcregencia);
        $jaexiste = false;
        if($verifica){
          $iddc = $verifica["id"];            
          $alunonota[$dcmatricula]["notaanterior"] = $verifica["nota"];
          pg_query("UPDATE dcnotas SET nota = {$dcnota} WHERE id = {$iddc}");
          $jaexiste = true;          
        }else{
          //echo "INSERT INTO dcnotas(matricula, data, nota, regencia) VALUES({$dcmatricula}, '{$dcdata}', {$dcnota}, {$dcregencia})";
          //pg_query("INSERT INTO dcnotas(matricula, data, nota, regencia) VALUES({$dcmatricula}, '{$dcdata}', {$dcnota}, {$dcregencia})");
          pg_query("INSERT INTO dcnotas(matricula, data, nota, regencia, disciplina, turma) VALUES({$dcmatricula}, '{$dcdata}', {$dcnota}, {$dcregencia}, {$dcdiscplina}, '{$dcturma}')");
        }
        $alunonota[$dcmatricula]["matricula"] = $dcmatricula;
        $alunonota[$dcmatricula]["nota"] = $dcnota;
        $alunonota[$dcmatricula]["existe"] = $jaexiste;
        
      }
      */
    //===========================================================================



//$codigo39 = $oAvaliacao->getElementoAvaliacao()->getCodigo();
//var_dump($codigo39);
      
      $comandos = array();

      /**
       * Atualizamos o total de faltas no periodo
       */
      $aMatriculas            = $oTurma->getAlunosMatriculadosNaturmaPorSerie($oEtapaOrigem);
      //testa($aMatriculas); die("E aqui?");
      $oPeriodoAvaliacaoNoDia = $aPeriodoAvalicao[0];
      foreach ($aMatriculas as $oMatricula) {
        $oDiarioClasse  = $oMatricula->getDiarioDeClasse();
        $oDisciplina    = $oDiarioClasse->getDisciplinasPorRegencia($oRegencia);
        $iTotalDeFaltas = $oDisciplina->getTotalDeFaltasPorPeriodoDeAula($oPeriodoAvaliacaoNoDia->getPeriodoAvaliacao());
        foreach ($oDisciplina->getAvaliacoes() as $oAvaliacao) {
          

          /*
          $xcodigoproc = $oAvaliacao->getElementoAvaliacao()->getCodigo();
          $xcodigoaluno = $oMatricula->getAluno()->getCodigoAluno();
          $xcodigoregencia = $oDadosRegencia->ed58_i_regencia;
          $xdata = $oParam->dtAula;
          //SELECT ed72_i_codigo, ed72_i_procavaliacao, ed54_i_evento, ed54_d_data FROM diario INNER JOIN diarioavaliacao ON ed95_i_codigo = ed72_i_diario INNER JOIN feriado ON ed54_i_calendario = ed95_i_calendario WHERE ed95_i_aluno = 120366 AND ed95_i_regencia = 84 AND ed54_d_data = '2023-07-04';


          $xnota = $alunonota[$xcodigoaluno]["nota"];
          $xcodigo = buscaCodigoDaAvaliacao($xcodigoaluno, $xcodigoregencia, $xcodigoproc);
          
          $notaAtual = buscaNotaAtual($xcodigo);
          if($alunonota[$xcodigoaluno]["existe"]){
            $novaNota = (float)$notaAtual + ( (float)$xnota - (float)$alunonota[$xcodigoaluno]["notaanterior"] );  
          }else{
            $novaNota = (float)$notaAtual + (float)$xnota;
          }
          $xsql = "UPDATE diarioavaliacao SET ed72_i_valornota = {$novaNota} WHERE ed72_i_codigo = {$xcodigo}";
          array_push($comandos, $xsql);
          */


          
          
          
          if (!$oAvaliacao->getElementoAvaliacao()->isResultado()) {
            $oPeriodoAvaliacao = $oAvaliacao->getElementoAvaliacao()->getPeriodoAvaliacao();
            if ($oPeriodoAvaliacao->getCodigo() == $oPeriodoAvaliacaoNoDia->getPeriodoAvaliacao()->getCodigo()) {
              $oAvaliacao->setNumeroFaltas($iTotalDeFaltas);
              $oDisciplina->salvar();
              unset($oAvaliacao);
              unset($oPeriodoAvaliacao);
              break;
            }
          }
        }

        unset($oDisciplina);
        unset($oDiarioClasse);
        unset($oMatricula);
      }
      
      foreach ($comandos as $executasql) {
        pg_query($executasql);
      }

      db_fim_transacao(false);

    break;

    case 'salvarDiarioClasseRec' : 
      
      //testa($oParam);      
      //testa($oParam->aAlunos);
      //testa($oParam->aRegencias);
      //die("Confere");
      
      /**
       * Pesquisamos quais os registros devemos excluir e lançar novamente.
       */

      db_inicio_transacao();
      $oDaoDiarioClasseAlunoFalta      = db_utils::getDao("diarioclassealunofalta");
      $oDaoDiarioClasse                = db_utils::getDao("diarioclasse");
      $oDaoDiarioClasseRegenciaHorario = db_utils::getDao("diarioclasseregenciahorario");
      $oDaoRegenciaHorario             = db_utils::getDao("regenciahorario");
      $sCodigoRegencias                = implode(",", $oParam->aRegencias);

      /**
       * Buscamos a turma pelo codigo das Regencias;
       */
      $sWhereTurma = "ed58_i_codigo in({$sCodigoRegencias})";
      $sSqlTurma   = $oDaoRegenciaHorario->sql_query(null, "distinct ed57_i_codigo, ed58_i_regencia, ed59_i_serie",
                                                     null,
                                                     $sWhereTurma);
      $rsTurma     = $oDaoRegenciaHorario->sql_record($sSqlTurma);
      if ($oDaoRegenciaHorario->numrows != 1) {

        throw new Exception("Turma com regencias configuradas incorretamente!");
      }
      $oDadosRegencia    = db_utils::fieldsMemory($rsTurma, 0);
      $iCodigoDaTurma    = $oDadosRegencia->ed57_i_codigo;

      $oRegencia         = new Regencia($oDadosRegencia->ed58_i_regencia);
      $oTurma            = new Turma($iCodigoDaTurma);
      $oEtapaOrigem      = new Etapa($oDadosRegencia->ed59_i_serie);
      $aPeriodoAvalicao  = $oTurma->getCalendario()->getPeriodoPorData(new DBDate($oParam->dtAula));

      
      //8 aqui
      //var_dump($oDadosRegencia->ed58_i_regencia);

      //ed95_i_regencia = 8 AND ed72_i_procavaliacao = 39;

      if (count($aPeriodoAvalicao) > 1 || count($aPeriodoAvalicao) == 0) {
        throw new Exception("Existem periodos com datas inconsistentes para o Calendario {$oTurma->getCalendario()->getDescricao()}!");
      }
      $sWhere            = "ed300_datalancamento = '{$oParam->dtAula}' ";
      $sWhere           .= "and ed302_regenciahorario in({$sCodigoRegencias})";
      $sSqlDiarioClasse  = $oDaoDiarioClasse->sql_query_faltas(null,
                                                               "distinct ed300_sequencial,
                                                                ed302_sequencial,
                                                                ed302_regenciahorario
                                                                ",
                                                                null,
                                                                $sWhere
                                                              );
      /**
       * @todo Deletar apenas as faltas dos alunos
       */
       $iCodigoDiario  = '';
       $rsDiarioClasse = $oDaoDiarioClasse->sql_record($sSqlDiarioClasse);
       $aDiarios       = db_utils::getCollectionByRecord($rsDiarioClasse);

       foreach ($aDiarios as $oDiario) {

         $iCodigoDiario                                     = $oDiario->ed300_sequencial;
         $aCodigosRegencia[$oDiario->ed302_regenciahorario] = $oDiario->ed302_sequencial;
       }

      /**
       * Incluimos os dados da Falta
       */

      $oDaoDiarioClasse->ed300_datalancamento   = $oParam->dtAula;
      $oDaoDiarioClasse->ed300_auladesenvolvida = db_stdClass::normalizeStringJson($oParam->sAulaDesenvolvida);
      $oDaoDiarioClasse->ed300_hora             = db_hora();
      $oDaoDiarioClasse->ed300_id_usuario       = db_getsession("DB_id_usuario");
      if ($iCodigoDiario == "") {

        $oDaoDiarioClasse->incluir(null);
      } else {

        $oDaoDiarioClasse->ed300_sequencial = $iCodigoDiario;
        $oDaoDiarioClasse->alterar($iCodigoDiario);
      }
      if ($oDaoDiarioClasse->erro_status == 0) {

        $sMsg  = "Erro ao salvar dados do Diário de classe.\n";
        $sMsg .= "Erro Sistema: {$oDaoDiarioClasse->erro_msg}";
        throw new Exception($sMsg);
      }

      /**
       * Persistimos os dados da periodo da aula
       */
      if ($iCodigoDiario == "" ) {

        $aCodigosRegencia = array();
        foreach ($oParam->aRegencias as $iRegencia) {

          $oDaoDiarioClasseRegenciaHorario->ed302_diarioclasse    = $oDaoDiarioClasse->ed300_sequencial;
          $oDaoDiarioClasseRegenciaHorario->ed302_regenciahorario = $iRegencia;
          $oDaoDiarioClasseRegenciaHorario->incluir(null);
          if ($oDaoDiarioClasseRegenciaHorario->erro_status == 0) {

            $sMsg  = "Erro ao salvar dados do Diário de classe.\n";
            $sMsg .= "Erro Sistema: {$oDaoDiarioClasseRegenciaHorario->erro_msg}";
            throw new Exception($sMsg);
          }
          $aCodigosRegencia[$iRegencia] = $oDaoDiarioClasseRegenciaHorario->ed302_sequencial;
        }
      }

      /*
       * Persistimos os dados da Falta;
       */
      foreach ($oParam->aAlunos as $oAlunoFalta) {


        /**
         * Verificamos se existe alguma falta lançada no dia
         */
        $sWhere          = "ed301_aluno = {$oAlunoFalta->iCodigo}";
        $sWhere         .= " and ed300_datalancamento = '{$oParam->dtAula}'";
        $sWhere         .= " and ed302_regenciahorario in(".implode(",", $oParam->aRegencias).")";
        $sSqlFaltasNoDia = $oDaoDiarioClasseAlunoFalta->sql_query_aluno_falta(null,
                                                                              "ed58_i_codigo,
                                                                              ed301_sequencial",
                                                                              null,
                                                                              $sWhere
                                                                              );

        $rsFaltasNoDia  = $oDaoDiarioClasseAlunoFalta->sql_record($sSqlFaltasNoDia);
        $aFaltasNoDia   = db_utils::getCollectionByRecord($rsFaltasNoDia);


        


        //var_dump($aFaltasNoDia);
        foreach ($aFaltasNoDia as $oFalta) {

          /**
           * Verificamos se a falta existe no dia.
           */
          if (in_array($oFalta->ed58_i_codigo, $oAlunoFalta->faltas)) {
            
            /**
             * falta já lançada, não incluimos novamente
             */
            array_splice($oAlunoFalta->faltas, array_search($oFalta->ed58_i_codigo, $oAlunoFalta->faltas), 1);
          } else {

            /**
             * a falta foi dada como presenca.devemos excluir a mesma
             */
            $oDaoOcorrenciaFalta = db_utils::getDao("ocorrenciafalta");
            $oDaoOcorrenciaFalta->excluir(null, "ed104_diarioclassealunofalta = {$oFalta->ed301_sequencial}");
            if ($oDaoOcorrenciaFalta->erro_status == 0) {

              $sMensagemErro   = "Falta não excluída.\\n ";
              $sMensagemErro  .= "Erro Técnico : {$oDaoOcorrenciaFalta->erro_msg}";
              throw new BusinessException($sMensagemErro);
            }

            $oDaoDiarioClasseAlunoFalta->excluir($oFalta->ed301_sequencial);
            if ($oDaoDiarioClasse->erro_status == 0) {

              $sMensagemErro   = "Erro ao excluir faltas do aluno.\\n ";
              $sMensagemErro  .= "Erro Técnico : {$oDaoDiarioClasse->erro_msg}";
              throw new BusinessException($sMensagemErro);
            }
          }
        }

        foreach ($oAlunoFalta->faltas as $iPeriodoFalta) {

          $oDaoDiarioClasseAlunoFalta->ed301_aluno                       = $oAlunoFalta->iCodigo;
          $oDaoDiarioClasseAlunoFalta->ed301_diarioclasseregenciahorario = $aCodigosRegencia[$iPeriodoFalta];
          $oDaoDiarioClasseAlunoFalta->incluir(null);
          if ($oDaoDiarioClasseAlunoFalta->erro_status == 0) {

            $sMsg  = "Erro ao salvar dados do aluno.\n";
            $sMsg .= "Erro Sistema: {$oDaoDiarioClasseAlunoFalta->erro_msg}";
            throw new Exception($sMsg);
          }
        }//fim do foreach
      }



    //===========================================================================
    //NOTAS AQUI
      $alunonota = array();
      //testa($oParam->aAlunos);
      foreach ($oParam->aAlunos as $linha){
        //var_dump($oDadosRegencia->ed58_i_regencia); 8
        $dcmatricula = $linha->iCodigo;        
        $dcnota = str_replace(",", ".", $linha->dcnotas);
        if($dcnota == ""){
          $dcnota = 0;
        }
        $dcdata = $oParam->dtAula;
        $dcregencia = $oParam->aRegencias[0];        
        $verifica = verificaNota3Rec($dcmatricula, $dcdata, $dcregencia);
        //var_dump($verifica);
        
        if($verifica){
          $iddc = $verifica["id"];          
          pg_query("UPDATE dcnotasrec SET nota = {$dcnota} WHERE id = {$iddc}");
        }else{
          //echo "INSERT INTO dcnotas(matricula, data, nota, regencia) VALUES({$dcmatricula}, '{$dcdata}', {$dcnota}, {$dcregencia})";
          pg_query("INSERT INTO dcnotasrec(matricula, data, nota, regencia) VALUES({$dcmatricula}, '{$dcdata}', {$dcnota}, {$dcregencia})");
        }
        $alunonota[$dcmatricula]["matricula"] = $dcmatricula;
        $alunonota[$dcmatricula]["nota"] = $dcnota;
        
      }
    //===========================================================================



//$codigo39 = $oAvaliacao->getElementoAvaliacao()->getCodigo();
//var_dump($codigo39);
      
      $comandos = array();

      /**
       * Atualizamos o total de faltas no periodo
       */
      $aMatriculas            = $oTurma->getAlunosMatriculadosNaturmaPorSerie($oEtapaOrigem);
      //testa($aMatriculas); die("E aqui?");
      $oPeriodoAvaliacaoNoDia = $aPeriodoAvalicao[0];
      foreach ($aMatriculas as $oMatricula) {
        $oDiarioClasse  = $oMatricula->getDiarioDeClasse();
        $oDisciplina    = $oDiarioClasse->getDisciplinasPorRegencia($oRegencia);
        $iTotalDeFaltas = $oDisciplina->getTotalDeFaltasPorPeriodoDeAula($oPeriodoAvaliacaoNoDia->getPeriodoAvaliacao());
        
        
        
        
        
        $contarec = 1;
        foreach ($oDisciplina->getAvaliacoes() as $oAvaliacao) {
          //testa($oAvaliacao);
          $xcodigoaluno = $oMatricula->getAluno()->getCodigoAluno();
          $xcodigoregencia = $oDadosRegencia->ed58_i_regencia;
          $xcodigoproc = $oAvaliacao->getElementoAvaliacao()->getCodigo(); //59, 60, 61
          $xdata = $oParam->dtAula;
          
          $validaRec = validaProc($xcodigoaluno, $xcodigoregencia, $xcodigoproc);
          if(!$validaRec){continue;}
          //if($contarec != 3 || $contarec != 6){$contarec++;continue;}
          $xnota = $alunonota[$xcodigoaluno]["nota"];          
          $xcodigo = buscaCodigoDaAvaliacao($xcodigoaluno, $xcodigoregencia, $xcodigoproc);
          $notaAtual = buscaNotaAtual($xcodigo);
          $novaNota = (float)$notaAtual + (float)$xnota;

          $xsql = "UPDATE diarioavaliacao SET ed72_i_valornota = {$novaNota} WHERE ed72_i_codigo = {$xcodigo}";
          //var_dump($contarec);
          //var_dump($xcodigoaluno, $xcodigoregencia, $xcodigoproc);
          //var_dump($xsql); echo "<br>";

          if($contarec == 3 || $contarec == 6){
            array_push($comandos, $xsql);
          }
          //var_dump("UPDATE diarioavaliacao SET ed72_i_valornota = {$novaNota} WHERE ed72_i_codigo = {$xcodigo}");
          //pg_query("UPDATE diarioavaliacao SET ed72_i_valornota = {$novaNota} WHERE ed72_i_codigo = {$xcodigo}");

          /*$sql = pg_query($conn, "UPDATE diarioavaliacao SET ed72_i_valornota = {$novaNota} WHERE ed72_i_codigo = {$xcodigo}");
          if(!$sql){
            $error = pg_last_error($conn);
            var_dump($error);
          }else{
            var_dump("UPDATE diarioavaliacao SET ed72_i_valornota = {$novaNota} WHERE ed72_i_codigo = {$xcodigo}");
          }*/
          
          if (!$oAvaliacao->getElementoAvaliacao()->isResultado()) {
            $oPeriodoAvaliacao = $oAvaliacao->getElementoAvaliacao()->getPeriodoAvaliacao();

            if ($oPeriodoAvaliacao->getCodigo() == $oPeriodoAvaliacaoNoDia->getPeriodoAvaliacao()->getCodigo()) {
              $oAvaliacao->setNumeroFaltas($iTotalDeFaltas);
              $oDisciplina->salvar(); //Erro aqui
              //$oDisciplina->salvarrec();
              unset($oAvaliacao);
              unset($oPeriodoAvaliacao);
              break;
            }
          }
          $contarec++;
        }//foreach interno

        unset($oDisciplina);
        unset($oDiarioClasse);
        unset($oMatricula);
      }//foreach final
      
      foreach ($comandos as $executasql) {
        pg_query($executasql);
      }

      db_fim_transacao(false);

    break;
  }
} catch (Exception $eErro ) {

  db_fim_transacao(true);
  $oRetorno->status  = 2;
  $oRetorno->message = urlencode($eErro->getMessage());
}
$oRetorno->erro = $oRetorno->status == 2;
//testa($oRetorno);
echo $oJson->encode($oRetorno);


function buscaFeriados($sCalendario) {
  
  $calendarios = explode(",", $sCalendario);
  
  $kql = "";
  foreach($calendarios as $cx){
    $kql .= "SELECT ed54_d_data as data, ed54_c_dialetivo as dia_letivo from feriado where ed54_i_calendario  = ".trim($cx)." INTERSECT ";
  }
  $kql = rtrim($kql,"INTERSECT ");
  $kql .= " ORDER BY data";
  $rsFeriado = pg_query($kql);

  //$oDaoFeriado = new cl_feriado();
  //$sCampos     = "ed54_d_data as data, ed54_c_dialetivo as dia_letivo";
  //$sSqlFeriado = $oDaoFeriado->sql_query_file(null, $sCampos, "ed54_d_data", "ed54_i_calendario in({$sCalendario})" );
  //$rsFeriado   = db_query($sSqlFeriado);
  

  if ( !$rsFeriado ) {
    throw new Exception( _M(ARQUIVO_MENSAGEM . "erro_buscar_feriados") );
  }
  return db_utils::getCollectionByRecord($rsFeriado);
}

function buscaFeriadosRec($sCalendario) {

  $oDaoFeriado = new cl_feriado();
  $sCampos     = "ed54_d_data as data, ed54_c_dialetivo as dia_letivo";
  $sSqlFeriado = $oDaoFeriado->sql_query_file(null, $sCampos, "ed54_d_data", "ed54_i_calendario in({$sCalendario}) AND ed54_i_evento in(13,14)" );  
  $rsFeriado   = db_query($sSqlFeriado);

  if ( !$rsFeriado ) {
    throw new Exception( _M(ARQUIVO_MENSAGEM . "erro_buscar_feriados") );
  }
  return db_utils::getCollectionByRecord($rsFeriado);
}
