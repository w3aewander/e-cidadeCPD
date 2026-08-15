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

require_once ("fpdf151/pdfwebseller.php");
require_once ("std/DBDate.php");
require_once ("std/db_stdClass.php");
require_once ("dbforms/db_funcoes.php");
require_once ("libs/db_sql.php");
require_once ("libs/db_stdlib.php");
require_once ("libs/db_conecta.php");
require_once ("libs/db_sessoes.php");
require_once ("libs/db_utils.php");
require_once ("libs/JSON.php");
require_once ("libs/db_usuariosonline.php");
require_once ("libs/db_app.utils.php");
require_once ("libs/exceptions/BusinessException.php");
require_once ("libs/exceptions/ParameterException.php");
require_once ("libs/exceptions/DBException.php");
require_once ("model/educacao/DocenteRepository.model.php");
require_once ("model/educacao/Docente.model.php");

/**
 * Converte texto para WinAnsi (Windows-1252/ISO-8859-1) para impressão no FPDF (core fonts),
 * sem corromper strings que já estejam nesse encoding.
 *
 * - Se a string for UTF-8 válida -> converte para Windows-1252 (fallback: utf8_decode).
 * - Se NÃO for UTF-8 válida -> assume que já está em ISO/Win-1252 e retorna como está.
 */
function pdf_text($s) {

  if ($s === null) {
    return '';
  }

  $s = (string) $s;

  // Se for UTF-8 válido, converte; caso contrário, mantém (evita "3º" virar "3?")
  if (preg_match('//u', $s)) {
    if (function_exists('iconv')) {
      $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
      if ($converted !== false) {
        return $converted;
      }
    }
    return utf8_decode($s);
  }

  return $s;
}

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

/**
 * Calcula o resultado final do encerramento para uma matrícula,
 * reutilizando a mesma regra da Ficha Individual / ATA.
 *
 * Autor: Uemerson Santana
 * Data: 18/12/2025
 * Demanda: 18034
 * Razão: Garantir que o Resumo Anual reflita exatamente o resultado
 *        do encerramento oficial (incluindo reclassificações, baixa
 *        frequência, progressão parcial, EM RECUPERA??O, etc.).
 *
 * @param int $iEtapa         Código da série/etapa
 * @param int $iTurma         Código da turma
 * @param int $iMatricula     Código da matrícula (ed60_i_codigo)
 * @param int $iAnoCalendario Ano do calendário
 * @return string             Descrição final do encerramento ou vazio se não houver
 */
function resultado_final_rpc($iEtapa, $iTurma, $iMatricula, $iAnoCalendario) {

  /**
   * Autor: Uemerson Santana
   * Data: 18/12/2025
   * Demanda: 18034
   * Razao: Buscar matrícula diretamente pelo código, sem depender de getAlunosMatriculadosNaTurmaPorSerie.
   *        Isso garante que TODOS os alunos sejam encontrados, incluindo os com necessidades especiais
   *        que podem não estar na lista retornada por aquele método.
   */
  $oTurma = new Turma($iTurma);
  $oEtapa = EtapaRepository::getEtapaByCodigo($iEtapa);

  // Buscar matrícula diretamente pelo código
  $mat = new Matricula($iMatricula);

  // Verificar se a matrícula foi encontrada
  if (!$mat->getCodigo()) {
    return '';
  }

  $iCodigoEnsino            = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
  $aTermosAprovado          = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, 'A', $iAnoCalendario);
  $sLabelAprovado           = count($aTermosAprovado) > 0 ? $aTermosAprovado[0]->sDescricao : '';
  $lPermiteAprovacaoParcial = EncerramentoAvaliacao::permiteAprovacaoParcial($oTurma, $oEtapa);

    db_inicio_transacao();

  // Verificar situação do aluno
  if (trim($mat->getSituacao()) !== 'MATRICULADO') {
      db_fim_transacao(false);
    return trim($mat->getSituacao());
    }

    $diarioService    = $mat->getDiarioDeClasse()->getDiarioAlunoService();
    $areaProcedimento = $mat->getDiarioDeClasse()->getAreaProcedimento();
    $resultadoFinal   = $mat->getDiarioDeClasse()->getResultadoFinal();

    if (!is_null($areaProcedimento)) {
      $resultadoFinal = $diarioService->getDiarioAluno()->getResultadoFinal()->getResultadoFinal();
    }

    if (is_null($areaProcedimento) &&
        $lPermiteAprovacaoParcial &&
        $resultadoFinal === 'A' &&
        EncerramentoAvaliacao::validaDiarioAlunoEja($mat, $oEtapa) === 'P') {
      $resultadoFinal = 'P';
    }

    if (!empty($resultadoFinal)) {
      $aTermos = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $resultadoFinal, $iAnoCalendario);
      if (count($aTermos) > 0) {
        $resultadoFinal = $aTermos[0]->sDescricao;
      }
    }

    $lAprovadoComProgressaoParcial = false;
    if (is_null($areaProcedimento)) {
    $oDiarioClasse                 = $mat->getDiarioDeClasse();
      $lAprovadoComProgressaoParcial = $oDiarioClasse->aprovadoComProgressaoParcial();
    }

    if ($lAprovadoComProgressaoParcial) {
      $resultadoFinal = " {$sLabelAprovado} (Progressão Parcial / Dependência)";
    }

    $lTemRecuperacao = $mat->getDiarioDeClasse()->temRecuperacao();
    if ($lTemRecuperacao) {
      $resultadoFinal = 'EM RECUPERAÇÃO';
    }

    db_fim_transacao(false);
    return $resultadoFinal;
}

function buscaPeriodos($calendario, $escola){
  $sql = pg_query("SELECT distinct ed09_i_codigo as codigo_periodo, ed09_c_descr as descricao_periodo from periodocalendario inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = periodocalendario.ed53_i_periodoavaliacao inner join calendario on calendario.ed52_i_codigo = periodocalendario.ed53_i_calendario inner join calendarioescola on calendarioescola.ed38_i_calendario = calendario.ed52_i_codigo inner join duracaocal on duracaocal.ed55_i_codigo = calendario.ed52_i_duracaocal where ed53_i_calendario in ({$calendario}) and ed38_i_escola in ({$escola}) order by ed09_i_codigo");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

/**
 * Autor: Uemerson Santana
 * Data: 24/11/2025
 * Demanda: 17994
 * Razao: Identificar séries (ex.: 2º ano) avaliadas exclusivamente por conceito para que relatórios ignorem notas num?ricas lançadas inadvertidamente.
 */
function serieAvaliadaSomentePorConceito($iCodigoSerie) {

  static $aCacheSerieConceito = array();

  if (array_key_exists($iCodigoSerie, $aCacheSerieConceito)) {
    return $aCacheSerieConceito[$iCodigoSerie];
  }

  $sSqlSerie = "SELECT ed11_c_descr FROM serie WHERE ed11_i_codigo = {$iCodigoSerie} LIMIT 1";
  $rsSerie   = db_query($sSqlSerie);

  if (pg_num_rows($rsSerie) == 0) {
    $aCacheSerieConceito[$iCodigoSerie] = false;
    return false;
  }

  $sDescricao = trim(db_utils::fieldsMemory($rsSerie, 0)->ed11_c_descr);
  $sNormalizado = strtoupper(str_replace(array('º', 'ª', '°', '´'), '', $sDescricao));
  $aCacheSerieConceito[$iCodigoSerie] = (strpos($sNormalizado, '2 ANO') !== false);

  return $aCacheSerieConceito[$iCodigoSerie];
}

function voltaFaltas($matricula,$disciplina,$turma){
  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];
  $lSomenteConceito = serieAvaliadaSomentePorConceito($serie);
// voltei a disciplina para o nome para fazer anos iniciais
//$disciplina = utf8_decode($disciplina);


  /**
   * IMPORTANTE: em produção há mistura de encodings (UTF-8 e ISO/Win-1252).
   * Para não "sumir" disciplina com acento (ex.: TECNOLOGIA E INOVAÇÃO),
   * tentamos localizar a regência primeiro com o texto original e, se não achar,
   * tentamos novamente com utf8_decode().
   */
  $disciplinaBusca = $disciplina;

	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  '".$disciplinaBusca."'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";

    $result_D = db_query($sqlD);
    if (pg_num_rows($result_D) == 0 && preg_match('//u', (string)$disciplina)) {
      $disciplinaBusca = utf8_decode($disciplina);
      // Recria a query com o novo valor (mais seguro do que depender do str_replace acima em casos futuros)
      $sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  '".$disciplinaBusca."'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";
      $result_D = db_query($sqlD);
    }

    /**
     * Autor: Uemerson Santana
     * Data: 02/02/2026
     * Demanda: 18059
     * Razão: No banco a disciplina está cadastrada como "EDUCAÇÃO FISICA" (sem acento no I).
     *        A conexão PHP/PostgreSQL pode estar em Latin1; ao enviar a string em UTF-8 a comparação
     *        falha e os conceitos de Educação Física não aparecem no Resumo Anual. Usar
     *        utf8_decode('EDUCAÇÃO FISICA') garante que a busca encontre a regência e exiba os conceitos.
     */
    if (pg_num_rows($result_D) == 0 && trim($disciplina) === 'EDUCAÇÃO FÍSICA') {
      $disciplinaBusca = utf8_decode('EDUCAÇÃO FISICA');
      $sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  '".$disciplinaBusca."'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";
      $result_D = db_query($sqlD);
    }

    // Se não localizou a disciplina/regência, retorna estrutura vazia (evita erro fatal em fieldsmemory)
    if (pg_num_rows($result_D) == 0) {
      return array(
        array('ed72_i_valornota' => null, 'ed72_c_valorconceito' => null, 'ed72_i_numfaltas' => null),
        array('ed72_i_valornota' => null, 'ed72_c_valorconceito' => null, 'ed72_i_numfaltas' => null),
        array('ed72_i_valornota' => null, 'ed72_c_valorconceito' => null, 'ed72_i_numfaltas' => null),
      );
    }
    $odados = db_utils::fieldsmemory($result_D,0);

  $sqla = " select
			ed95_i_codigo,
			ed47_v_nome
			from
			diario
			inner join aluno on ed47_i_codigo = ed95_i_aluno
			inner join matricula on ed60_i_aluno = ed47_i_codigo
			inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
			inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
			where
			ed60_i_codigo = {$matricula}
			and
			ed95_i_regencia = ed59_i_codigo
			and
			ed59_i_disciplina = {$odados->ed12_i_codigo}";
  if( $serie <> 29 and $serie <> 30 and $serie <> 31 )
  {
       $sqla .= "
			    and
			    ed95_i_serie = {$serie}";
  }
  $sqla .= "
  		    and
			ed59_i_turma = {$turma}
			order by ed95_i_codigo";


  $sql2 = pg_query($sqla);
  $r2 = pg_fetch_all($sql2);
  if (!is_array($r2) || count($r2) == 0) {
    return array(
      array('ed72_i_valornota' => null, 'ed72_c_valorconceito' => null, 'ed72_i_numfaltas' => null),
      array('ed72_i_valornota' => null, 'ed72_c_valorconceito' => null, 'ed72_i_numfaltas' => null),
      array('ed72_i_valornota' => null, 'ed72_c_valorconceito' => null, 'ed72_i_numfaltas' => null),
    );
  }
  $codigo = $r2[0]["ed95_i_codigo"];

  $sqlN ="
		select
		distinct on (ed72_i_procavaliacao)
		ed232_c_descr as disciplina,
		ed09_c_descr  as bimestre,
		ed72_i_valornota,
		ed72_c_valorconceito,
		ed72_i_numfaltas
		from
		diarioavaliacao
		inner join diario           on ed95_i_codigo          = ed72_i_diario
		inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
		left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
		left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
		inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
		inner join aluno            on ed47_i_codigo          = ed95_i_aluno
		inner join matricula        on ed60_i_aluno           = ed47_i_codigo
		inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
		inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
		inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
		inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
		where ed72_i_diario = {$codigo}
		ORDER BY ed72_i_procavaliacao";

  $sql3 = pg_query($sqlN);
  $resultado = pg_fetch_all($sql3);

  /**
   * Autor: Uemerson Santana
   * Data: 24/11/2025
   * Demanda: 17994
   * Razao: Para séries de conceito, força o relatório a exibir apenas o conceito mesmo que haja valor numérico armazenado.
   */
  if ($lSomenteConceito && is_array($resultado)) {
    foreach ($resultado as $iIdx => $aDadosAvaliacao) {
      if (isset($aDadosAvaliacao['ed72_c_valorconceito']) && trim($aDadosAvaliacao['ed72_c_valorconceito']) !== '') {
        $resultado[$iIdx]['ed72_i_valornota'] = null;
      }
    }
  }

  return $resultado;
}

function voltaFaltas2($matricula,$turma){ // para anos iniciais
  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];
  $lSomenteConceito = serieAvaliadaSomentePorConceito($serie);


// as faltas de anos iniciais só são lançadas na linha PORTUGUESA
	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  'LINGUA PORTUGUESA'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";

    $result_D = db_query($sqlD);
    $odados = db_utils::fieldsmemory($result_D,0);

  $sql2 = pg_query("select
                    ed95_i_codigo
					from
					diario
					inner join aluno on ed47_i_codigo = ed95_i_aluno
					inner join matricula on ed60_i_aluno = ed47_i_codigo
					inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
					inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
					where
					ed60_i_codigo = {$matricula}
					and
					ed95_i_regencia = ed59_i_codigo
					and
					ed59_i_disciplina = {$odados->ed12_i_codigo}
					and
					ed95_i_serie = {$serie}
					and
					ed59_i_turma = {$turma}
					order by ed95_i_codigo");

  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];

  $sql3 = pg_query("
                    select
					distinct on (ed72_i_procavaliacao)
					ed232_c_descr as disciplina,
                    ed09_c_descr  as bimestre,
					ed72_i_valornota,
					ed72_c_valorconceito,
                    ed72_i_numfaltas
					from
					diarioavaliacao
					inner join diario           on ed95_i_codigo          = ed72_i_diario
					inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
					left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
					left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
	                inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
	                inner join aluno            on ed47_i_codigo          = ed95_i_aluno
	                inner join matricula        on ed60_i_aluno           = ed47_i_codigo
	                inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
	                inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
	                inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
	                inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
					where ed72_i_diario = {$codigo}
					ORDER BY ed72_i_procavaliacao");




  $resultado = pg_fetch_all($sql3);

  /**
   * Autor: Uemerson Santana
   * Data: 24/11/2025
   * Demanda: 17994
   * Razao: Garante que, no 2º ano, os trimestres e médias reflitam exclusivamente os conceitos lançados.
   */
  if ($lSomenteConceito && is_array($resultado)) {
    foreach ($resultado as $iIdx => $aDadosAvaliacao) {
      if (isset($aDadosAvaliacao['ed72_c_valorconceito']) && trim($aDadosAvaliacao['ed72_c_valorconceito']) !== '') {
        $resultado[$iIdx]['ed72_i_valornota'] = null;
      }
    }
  }

  return $resultado;
}


function buscaCodDisciplinas($calendario,$turma){

	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
				ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed52_i_codigo        =  ".$calendario."
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";

    $result_D = db_query($sqlD);
	return $result_D;
}











$escola = db_getsession("DB_coddepto");
$calendario = $_GET["calendario"];
$disciplina = $_GET["disciplina"];
$assAdicion = $_GET["aa"];
$assAtivid  = $_GET["at"];
/**
 * Autor: Uemerson Santana
 * Data: 07/10/2025
 * Demanda: 17874
 */
$cgmAssAdic = isset($_GET["cgmaa"]) ? $_GET["cgmaa"] : '';

$periodos = buscaPeriodos($calendario, $escola);
//testa($periodos);
//[periodo] => 6
//die("Confere");
//string(596) "select distinct ed09_i_codigo as codigo_periodo, ed09_c_descr as descricao_periodo from periodocalendario inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = periodocalendario.ed53_i_periodoavaliacao inner join calendario on calendario.ed52_i_codigo = periodocalendario.ed53_i_calendario inner join calendarioescola on calendarioescola.ed38_i_calendario = calendario.ed52_i_codigo inner join duracaocal on duracaocal.ed55_i_codigo = calendario.ed52_i_duracaocal where ed53_i_calendario in (17) and ed38_i_escola in (20100) order by ed09_i_codigo" Confere
/**
 * Relatório de Conselho de Classe
 * filtros passados na url
 *  - periodo
 *  - trocaTurma
 *  - classificacaoAlunoTurma
 *  - turmas = pode ser informado mais de um código que será separado por virgula.
 *             OBS.: não é o código da turma e sim o código da: turmaserieregimemat
 * relatório
 * - quebra página por turma
 * - possui as seguintes colunas:
 * -- N? = classificação do aluno (opcional, ver filtro $oConfigRelatorio->lClassificacaoAlunoTurma)
 * -- Aluno = nome do aluno
 * -- S = situacao do aluno
 * -- Parecere
 * -- [Disciplinas] = todas disciplinas da turma (Config Padrão: $oConfigRelatorio->iMaximoDisciplinaPagina = 10)
 * -- TF            = Total de faltas
 */

$oGet  = db_utils::postMemory($_GET);
$oJson = new Services_JSON();
//testa($oGet); // testar as variaveis que estão vindo aqui

$aTurmasSelecionadas = $oJson->decode(str_replace("\\", "", $oGet->oTurmas));
//$aTurmasSelecionadas
$oGet->periodo = $periodos[0]["codigo_periodo"];
$aFiltroParametro = array();
$aFiltroParametro[] = null;
$aFiltroParametro[] = "ed233_c_notabranca";
$aFiltroParametro[] = null;
$aFiltroParametro[] = " ed233_i_escola = " . db_getsession("DB_coddepto");
$aParametroGlobal   = db_stdClass::getParametro("edu_parametros", $aFiltroParametro, "ed233_c_notabranca");

/**
 * Objeto com a configuração do relatório
 */
$oConfigRelatorio = new stdClass();
$oConfigRelatorio->lTrocaTurma              = $oGet->trocaTurma == 'Sim' ? true : false;
$oConfigRelatorio->lClassificacaoAlunoTurma = $oGet->classificacaoAlunoTurma == 'Sim' ? true : false;
$oConfigRelatorio->comLegenda               = $oGet->comLegenda == 'Sim' ? true : false;
$oConfigRelatorio->iFonteAvaliacao          = $oGet->tamanhoFonte;
$oConfigRelatorio->iMaximoDisciplinaPagina  = 10; // Nº disciplina por página  (Máximo 10)
$oConfigRelatorio->iAlunosPorPagina         = 27; // Nº de alunos por página
$oConfigRelatorio->iAlturaLinha             = 4;  // Altura da linha
$oConfigRelatorio->iColunaNome              = 34; // Largura da coluna Aluno
$oConfigRelatorio->iColunaNumero            = 5;  // Largura da coluna Nº, S (Situação) e TF (Total de Faltas)
$oConfigRelatorio->iColunaCodigo            = 9;  // CódigoAluno
$oConfigRelatorio->iColunaPareceres         = 18; // Coluna Pareceres
$oConfigRelatorio->iAlturaLine              = 183; //


$iSomaColunasDadosAluno  = ($oConfigRelatorio->iColunaNome + $oConfigRelatorio->iColunaCodigo);
$iSomaColunasDadosAluno += $oConfigRelatorio->iColunaPareceres;

/**
 * Por que o cálculo abaixo?
 * Como visto no comentário da variável ($oConfigRelatorio->iColunaNumero) ela é utilizada para definir o tamanho
 * de três colunas.
 * -- N?, S (Situação) e TF (Total de Faltas)
 * Sendo assim calculamos quantas vezes ela será descontada da $oConfigRelatorio->iLarguraTotalDisciplinas
 */
if ($oConfigRelatorio->lTrocaTurma) {
  $iSomaColunasDadosAluno += ($oConfigRelatorio->iColunaNumero * 3);
} else {
  $iSomaColunasDadosAluno += ($oConfigRelatorio->iColunaNumero * 2);
}


$oConfigRelatorio->iLarguraTotalDisciplinas  = 282 - $iSomaColunasDadosAluno;
$oConfigRelatorio->iLarguraTotalDisciplinas -= (0.3 * $oConfigRelatorio->iMaximoDisciplinaPagina);
$oConfigRelatorio->lCalculaMediaParcial      = $aParametroGlobal[0]->ed233_c_notabranca == 'S' ? true : false;

$aTurmas = array();
/**
 * Cria a instancia de todas turmas selecionas no filtro
 * Organizamos os dados a serem impressos no relatório
 */
//testa($aTurmasSelecionadas); die("confere");
foreach ($aTurmasSelecionadas as $oTurmaSelecionada) { //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa

  $oTurma = TurmaRepository::getTurmaByCodigo($oTurmaSelecionada->iTurma);
  $oEtapa = EtapaRepository::getEtapaByCodigo($oTurmaSelecionada->iEtapa);

  if (empty($oTurma)) {
    continue;
  }

  $oTurmaEtapa           = new stdClass();
  $oTurmaEtapa->aAlunos  = array();
  $oTurmaEtapa->aPaginas = array();
  $iContDisciplinas      = 0;

  /**
   * Verificamos quantas páginas terá o relatório
   */
  foreach ($oTurma->getDisciplinasPorEtapa($oEtapa) as $oRegencia) {

//testa($oRegencia);
    $iContDisciplinas ++;

    if ($iContDisciplinas <= $oConfigRelatorio->iMaximoDisciplinaPagina) {
      $oTurmaEtapa->aPaginas[0][$oRegencia->getCodigo()] = $oRegencia;
    } else if ($iContDisciplinas > $oConfigRelatorio->iMaximoDisciplinaPagina &&
               $iContDisciplinas <= ($oConfigRelatorio->iMaximoDisciplinaPagina * 2)) {
      $oTurmaEtapa->aPaginas[1][$oRegencia->getCodigo()] = $oRegencia;
    } else {
      $oTurmaEtapa->aPaginas[2][$oRegencia->getCodigo()] = $oRegencia;
    }
  }


  /**
   * Informações referente a turma
   */
  $codTurma                     = $oTurma->getCodigo();
  $oTurmaEtapa->sTurma          = $oTurma->getDescricao();
  $oTurmaEtapa->sEtapa          = $oEtapa->getNome();
  $oTurmaEtapa->sTurno          = $oTurma->getTurno()->getDescricao();
  $oTurmaEtapa->sCalendario     = $oTurma->getCalendario()->getDescricao();
  $oTurmaEtapa->iAnoCalendario  = $oTurma->getCalendario()->getAnoExecucao();
  $oTurmaEtapa->sCurso          = $oTurma->getBaseCurricular()->getCurso()->getNome();
  $oTurmaEtapa->sFormaAvaliacao = null;
  $oTurmaEtapa->sPeriodo        = null;
  $oTurmaEtapa->lUltimoPeriodo  = false;
  $oTurmaEtapa->lJaCalculado    = false; // Controle utilizado quando $oTurmaEtapa->lUltimoPeriodo = true
  $oTurmaEtapa->iColunaNome     = $oConfigRelatorio->iColunaNome;
  $oTurmaEtapa->diasLetivos = $oTurma->getCalendario()->getDiasLetivos();

  /**
   * Localizamos qual forma de avaliacao para o período selecionado
   */
  $oAvaliacaoPeriodica = null;

  $iOrdemPeriodoSelecionado = 0;
  $iOrdemUltimoPeriodoTurma = 0;
  //testa($oTurma->getProcedimentoDeAvaliacaoDaEtapa($oEtapa)->getElementos()); die("confere");
  foreach ($oTurma->getProcedimentoDeAvaliacaoDaEtapa($oEtapa)->getElementos() as $oAvaliacaoPeriodicaTurma) {


    if ($oAvaliacaoPeriodicaTurma->isResultado()) {
      continue;
    }
    $iOrdemUltimoPeriodoTurma = $oAvaliacaoPeriodicaTurma->getPeriodoAvaliacao()->getOrdemPeriodo();

    if ($oAvaliacaoPeriodicaTurma->getPeriodoAvaliacao()->getCodigo() == $oGet->periodo) {
      $iOrdemPeriodoSelecionado         = $oAvaliacaoPeriodicaTurma->getPeriodoAvaliacao()->getOrdemPeriodo();
      $oTurmaEtapa->sNomeFormaAvaliacao = $oAvaliacaoPeriodicaTurma->getFormaDeAvaliacao()->getDescricao();
      $oTurmaEtapa->sFormaAvaliacao     = $oAvaliacaoPeriodicaTurma->getFormaDeAvaliacao()->getTipo();
      $oTurmaEtapa->sPeriodo            = $oAvaliacaoPeriodicaTurma->getPeriodoAvaliacao()->getDescricao();
      $oAvaliacaoPeriodica              = $oAvaliacaoPeriodicaTurma;
    }
  }

  //testa($oAvaliacaoPeriodica);

  if ($iOrdemUltimoPeriodoTurma == $iOrdemPeriodoSelecionado) {
    $oTurmaEtapa->lUltimoPeriodo = true;
  }

  /**
   * Buscamos os dados do aluno e suas avali??es para o periodo selecionado.
   * Organizamos a estrutura das avaliações dos alunos pelo código da regencia
   */
  foreach ($oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa) as $oMatricula) {


    $oDadosAluno                      = new stdClass();
    $oDadosAluno->iMatricula          = $oMatricula->getCodigo();
    //$oDadosAluno->sNome               = abreviar($oMatricula->getAluno()->getNome(), 32, true);
    $oDadosAluno->sNome               = $oMatricula->getAluno()->getNome(); // a pedido de suellem foi tirada a abreviação
    $oDadosAluno->iCodigoAluno        = $oMatricula->getAluno()->getCodigoAluno();
    $oDadosAluno->sSituacao           = $oMatricula->getSituacao();
    $oDadosAluno->oDtMatricula        = $oMatricula->getDataMatricula();
    $oDadosAluno->iClassificacao      = $oMatricula->getNumeroOrdemAluno();
    $oDadosAluno->aAvaliacao          = array();
    $oDadosAluno->iTotalFaltas        = 0;
    $oDadosAluno->lAvaliadoPorParecer = $oMatricula->isAvaliadoPorParecer();

    db_inicio_transacao();

    $oDiarioDeClasse = $oMatricula->getDiarioDeClasse();

    $iContDisciplinas = 0;

    foreach ($oTurma->getDisciplinasPorEtapa($oEtapa) as $oRegencia) {

      $iContDisciplinas ++;
      $oDisciplinaDiario        = $oDiarioDeClasse->getDisciplinasPorRegencia($oRegencia, $oAvaliacaoPeriodica);
      $oAvaliacaoAproveitamento = $oDisciplinaDiario->getAvaliacoesPorOrdem($oAvaliacaoPeriodica->getOrdemSequencia());

      $oAvaliacao = new stdClass();
      $oAvaliacao->iRegencia       = $oRegencia->getCodigo();
      $oAvaliacao->sRegencia       = $oRegencia->getDisciplina()->getNomeDisciplina();


	  if( $oAvaliacao->sRegencia == 'LINGUA PORTUGUESA' or $oAvaliacao->sRegencia == 'CAMPOS DE EXPERIENCIA')
	  {
		  $regenc = $oRegencia->getCodigo();
	  }

      $oAvaliacao->sRegenciaAbrev  = $oRegencia->getDisciplina()->getAbreviatura();
      $oAvaliacao->iFaltas         = $oAvaliacaoAproveitamento->getTotalFaltas() + $oAvaliacaoAproveitamento->getFaltasAbonadas();
      $oAvaliacao->mAproveitamento = $oAvaliacaoAproveitamento->getValorAproveitamento();
      $oAvaliacao->lAtingiuMinimo  = $oAvaliacaoAproveitamento->temAproveitamentoMinimo();
      $oAvaliacao->lNotaExterna    = $oAvaliacaoAproveitamento->isAvaliacaoExterna();
      $oAvaliacao->lAmparado       = $oAvaliacaoAproveitamento->isAmparado();
      $oAvaliacao->sTipoAmparo     = 'AMP';

      if ($oAvaliacao->lAmparado && $oDisciplinaDiario->getAmparo()->getCodigoConvencaoAmparo() != '') {
        $oAvaliacao->sTipoAmparo = $oDisciplinaDiario->getAmparo()->getConvencao()->getAbreviatura();
      }


      $oAvaliacao->mNotaParcial    = $oDisciplinaDiario->getNotaParcial($oAvaliacaoPeriodica);
      $oAvaliacao->sTipoAvaliacao  = $oAvaliacaoAproveitamento->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo();

      unset($oAvaliacaoAproveitamento);
      /**
       * Como uma turma pode ter mais de 10 disciplinas, devemos quebrar página e continuar imprimindo as disciplinas
       * restantes. Neste bloco definimos at? três páginas de para uma turma com at? 30 disciplinas (Não sendo alterada
       * a configuração padrão de 10 disciplinas por página)
       * - 1ª página : de 1 à 10 disciplinas
       * - 2ª página : de 11 à 20 disciplinas
       * - 3ª página : de 21 à 30 disciplinas
       */
      if ($iContDisciplinas <= $oConfigRelatorio->iMaximoDisciplinaPagina) {
        $oDadosAluno->aAvaliacao[0][$oRegencia->getCodigo()] = $oAvaliacao;
      } else if ($iContDisciplinas > $oConfigRelatorio->iMaximoDisciplinaPagina &&
                 $iContDisciplinas <= ($oConfigRelatorio->iMaximoDisciplinaPagina * 2)) {
        $oDadosAluno->aAvaliacao[1][$oRegencia->getCodigo()] = $oAvaliacao;
      } else {
        $oDadosAluno->aAvaliacao[2][$oRegencia->getCodigo()] = $oAvaliacao;
      }

      $oDadosAluno->iTotalFaltas  += $oAvaliacao->iFaltas;
    }

    $oTurmaEtapa->aAlunos[] = $oDadosAluno;
    MatriculaRepository::removerMatricula($oMatricula);
    db_fim_transacao();

  }

  $aTurmas[] = $oTurmaEtapa;
  TurmaRepository::removerTurma($oTurma);
  EtapaRepository::removerEtapa($oEtapa);
}

$oPdf = new PDF("L");
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetAutoPageBreak(true);
$oPdf->SetFillColor(215);
$oPdf->SetMargins(8, 10);
$oPdf->SetLineWidth(0);
$oPdf->imprime_rodape = false;

foreach ($aTurmas as $oTurmaEtapa) {  //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa

  $head1 = "Resumo Anual";
  $head2 = pdf_text("Curso: {$oTurmaEtapa->sCurso}");
  $head3 = pdf_text("Turma: {$oTurmaEtapa->sTurma}");
  $head4 = pdf_text("Calendário: {$oTurmaEtapa->sCalendario}");
  $head5 = pdf_text("Etapa: {$oTurmaEtapa->sEtapa}");
  $head6 = pdf_text("Turno: {$oTurmaEtapa->sTurno}");
  $discip= utf8_decode($disciplina);



  $lPrimeiraPagina = true;
  $iPaginas        = count($oTurmaEtapa->aPaginas);
  $diasletivos     = $oTurmaEtapa->diasLetivos;

  $disciplinas           = buscaCodDisciplinas($calendario,$codTurma);

    for ($iPagina = 0; $iPagina < $iPaginas; $iPagina ++) {

    /**
     * A cada página, calcula em tempo de execução o tamanho de variáveis necessárias para o cálculo
     * de algumas colunas do relatório. Essas variáveis serão recalculadas a cada turma e página emitida
     */
         calculaTamanhoDeCelulasDinamicas($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);

         $iAlunosImpressoPagina = 0;
         $iAlunosTurma          = count($oTurmaEtapa->aAlunos);

         $iTotalDisciplina      = $oTurmaEtapa->iTotalDisciplina;
         $iLarguraCelulaParecer = $oTurmaEtapa->iLarguraCelulaParecer;
         $iLarguraDisciplina    = $oTurmaEtapa->iLarguraDisciplina;
         $iLarguraAvaliacao     = $iLarguraDisciplina - 5;
		foreach ($oTurmaEtapa->aAlunos as $oAluno) {//bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb

//    echo "<pre>";
//    print_r($oAluno);
//    echo "</pre>";

		  $sql = "
				 select
				 ed52_c_descr as anos
				 from
				 calendario
				 where
				 ed52_i_codigo = ".$_GET["calendario"];
		  $resultado = db_query($sql);
		  $calDados  = db_utils::fieldsMemory($resultado,0);

		  if ( $lPrimeiraPagina ) {
			$lPrimeiraPagina = false;
			adicionaHeader($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);
		  }

		  if (!$oConfigRelatorio->lTrocaTurma && $oAluno->sSituacao == 'TROCA DE TURMA') {
			continue;
		  }

		  $iAlunosImpressoPagina ++;
		  if ($iAlunosImpressoPagina > $oConfigRelatorio->iAlunosPorPagina) {
			$iAlunosImpressoPagina = 1;
			//montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa);
			montaQuadroLegendaAssinaturaC($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa, $oTurmaSelecionada->iTurma, $oTurmaSelecionada->iEtapa, $regenc, $assAdicion, $assAtivid, $discipl->ed12_i_codigo);
			if ($iAlunosImpressoPagina < $iAlunosTurma) {
			  adicionaHeader($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);
			}
		  }

		  if( substr($calDados->anos,0,13) == 'ANOS INICIAIS') //******************************** Anos iniciais ******************************************
		  {


            $sqlTransf = "
                        select
                        ed60_c_situacao
                        from
                        matricula
                        where
                        ed60_i_codigo = {$oAluno->iMatricula}
                        ";
            $result    = db_query($sqlTransf);
            $sitDados  = db_utils::fieldsMemory($result,0);

			  $oPdf->SetFont("arial", '', 6);
			  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');
			  if( strlen(trim( $oAluno->sNome )) >37)
			  {
				  $oPdf->SetFont("arial", '', 5);
			  }
			  $oPdf->Cell(57, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
			  $oPdf->SetFont("arial", '', 7);
			  // já existia o array $xfaltas, então eu utilizei para buscar as notas - Divaldo 11/10/2024
			  $port = false;
			  $hist = false;
			  $geog = false;
			  $cien = false;
			  $mate = false;

			  //LINGUA PORTUGUESA
			  $xfaltas  = voltaFaltas($oAluno->iMatricula,'LINGUA PORTUGUESA',$oTurmaEtapa->sTurma);
			  $media    = 0;
			  $quantdiv = 0; // quantidade de divisão para as médias
			  if( $xfaltas[0]["ed72_i_valornota"] <> null) //verifica se o primeiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null) //verifica se o segundo trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null) //verifica se o terceiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }

			  if( $xfaltas[0]["ed72_i_valornota"] <> null )
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  if(trim($xfaltas[0]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
  			  if( $xfaltas[1]["ed72_i_valornota"] <> null )
			  {

		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  if(trim($xfaltas[1]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }

  			  if( $xfaltas[2]["ed72_i_valornota"] <> null )
			  {
		          $port = true; //ultima nota portugues lançada
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
              if($xfaltas[0]["ed72_i_valornota"] <>null or $xfaltas[1]["ed72_i_valornota"] <>null or $xfaltas[2]["ed72_i_valornota"] <>null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] <> null)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] <> null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/$quantdiv;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] <> null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/$quantdiv;
				  }
				  $mediaP = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
                  }else{
				     $mediaA = '0';
                  }
		          if( $mediaP < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }

                  /**
                     * Autor: Uemerson Santana
                     * Data: 12/06/2025
                     * Demanda: 17356
                     */
                  if ( stripos(trim($sitDados->ed60_c_situacao), 'TRANSFERIDO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'TROCA DE TURMA') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'CANCELADO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'EVADIDO') !== false
                    ) {
                        $mediaA = '';
                  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");

			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");
                  }else{
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
				  }
			  }


			  // HISTORIA
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'HISTÓRIA',$oTurmaEtapa->sTurma);
			  $media = 0;
			  // repeti a geração das variáveis abaixo, porque teve um aluno que tinha nota em um bimestre não tinha no outro
			  $quantdiv = 0; // quantidade de divisão para as médias
			  if( $xfaltas[0]["ed72_i_valornota"] <> null) //verifica se o primeiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null) //verifica se o segundo trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null) //verifica se o terceiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }

			  if( $xfaltas[0]["ed72_i_valornota"] <> null )
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  if(trim($xfaltas[0]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }

  			  if( $xfaltas[1]["ed72_i_valornota"] <> null )
			  {

		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  if(trim($xfaltas[1]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }

  			  if( $xfaltas[2]["ed72_i_valornota"] <> null )
			  {
		          $hist = true; // ultima nota historia lançada
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3?
			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }

              if($xfaltas[0]["ed72_i_valornota"] <>null or $xfaltas[1]["ed72_i_valornota"] <>null or $xfaltas[2]["ed72_i_valornota"] <>null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] <>null)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] <>null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/$quantdiv;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] <>null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/$quantdiv;
				  }
				  $mediaH = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
				  }else{
					  $mediaA = '0';
				  }
		          if( $mediaH < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }

                  /**
                     * Autor: Uemerson Santana
                     * Data: 12/06/2025
                     * Demanda: 17356
                     */
                  if ( stripos(trim($sitDados->ed60_c_situacao), 'TRANSFERIDO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'TROCA DE TURMA') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'CANCELADO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'EVADIDO') !== false
                    ) {
                        $mediaA = '';
                  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }
			  else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");
                  }else{
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
				  }
			  }

			  // GEOGRAFIA
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'GEOGRAFIA',$oTurmaEtapa->sTurma);
			  $media = 0;
			  // repeti a geração das variáveis abaixo, porque teve um aluno que tinha nota em um bimestre não tinha no outro
			  $quantdiv = 0; // quantidade de divisão para as médias
			  if( $xfaltas[0]["ed72_i_valornota"] <> null) //verifica se o primeiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null) //verifica se o segundo trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null) //verifica se o terceiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }

			  if( $xfaltas[0]["ed72_i_valornota"] <> null )
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  if(trim($xfaltas[0]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null )
			  {
		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  if(trim($xfaltas[1]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null )
			  {
                  $geog = true; // ultima nota geografia lançada
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //REC S
			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
              if($xfaltas[0]["ed72_i_valornota"] <>null or $xfaltas[1]["ed72_i_valornota"] <>null or $xfaltas[2]["ed72_i_valornota"] <>null)
			  {

				  if( $xfaltas[0]["ed72_i_valornota"] <>null)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] <>null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/$quantdiv;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] <>null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/$quantdiv;
				  }
				  $mediaG = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media >0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
				  }else{
					  $mediaA = '0';
				  }
		          if( $mediaG < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }

                  /**
                     * Autor: Uemerson Santana
                     * Data: 12/06/2025
                     * Demanda: 17356
                     */
                  if ( stripos(trim($sitDados->ed60_c_situacao), 'TRANSFERIDO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'TROCA DE TURMA') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'CANCELADO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'EVADIDO') !== false
                    ) {
                        $mediaA = '';
                  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");
                  }else{
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
				  }

			  }

			  // CIENCIAS
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'CIENCIAS',$oTurmaEtapa->sTurma);
			  $media = 0;
			  // repeti a geração das variáveis abaixo, porque teve um aluno que tinha nota em um bimestre não tinha no outro
			  $quantdiv = 0; // quantidade de divisão para as médias
			  if( $xfaltas[0]["ed72_i_valornota"] <> null) //verifica se o primeiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null) //verifica se o segundo trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null) //verifica se o terceiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }

			  if( $xfaltas[0]["ed72_i_valornota"] <> null )
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  if(trim($xfaltas[0]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null )
			  {
		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  if(trim($xfaltas[1]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null )
			  {
		          $cien = true; // ultima nota ciencias lançada
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //REC S
			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
              if($xfaltas[0]["ed72_i_valornota"] <>null or $xfaltas[1]["ed72_i_valornota"] <>null or $xfaltas[2]["ed72_i_valornota"] <>null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] <>null)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] <>null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/$quantdiv;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] <>null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/$quantdiv;
				  }
				  $mediaC = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if($media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
				  }else{
					  $mediaA = '0';
				  }
		          if( $mediaC < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }

                  /**
                     * Autor: Uemerson Santana
                     * Data: 12/06/2025
                     * Demanda: 17356
                     */
                  if ( stripos(trim($sitDados->ed60_c_situacao), 'TRANSFERIDO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'TROCA DE TURMA') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'CANCELADO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'EVADIDO') !== false
                    ) {
                        $mediaA = '';
                  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");
                  }else{
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
				  }
			  }

			  // MATEM?TICA
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'MATEMÁTICA',$oTurmaEtapa->sTurma);
			  $media = 0;
			  // repeti a geração das variáveis abaixo, porque teve um aluno que tinha nota em um bimestre não tinha no outro
			  $quantdiv = 0; // quantidade de divisão para as médias
			  if( $xfaltas[0]["ed72_i_valornota"] <> null) //verifica se o primeiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null) //verifica se o segundo trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null) //verifica se o terceiro trimestre é nulo ou não
			  {
				  $quantdiv++;
			  }

			  if( $xfaltas[0]["ed72_i_valornota"] <> null )
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  if(trim($xfaltas[0]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null )
			  {
		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  if(trim($xfaltas[1]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null )
			  {
		          $mate = true; //ultima nota matemática lançada
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //REC S
			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
					  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  }else{
				      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
				  }
			  }
              if($xfaltas[0]["ed72_i_valornota"] <>null or $xfaltas[1]["ed72_i_valornota"] <>null or $xfaltas[2]["ed72_i_valornota"] <>null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] <>null)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] <>null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/$quantdiv;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] <>null )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/$quantdiv;
				  }
				  $mediaM = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
				  }else{
					  $mediaA = '0';
				  }
		          if( $mediaM < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }

                  /**
                     * Autor: Uemerson Santana
                     * Data: 12/06/2025
                     * Demanda: 17356
                     */
                  if ( stripos(trim($sitDados->ed60_c_situacao), 'TRANSFERIDO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'TROCA DE TURMA') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'CANCELADO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'EVADIDO') !== false
                    ) {
                        $mediaA = '';
                  }
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }else{
				  if(trim($xfaltas[2]["ed72_c_valorconceito"])<> '')
                  {
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");
                  }else{
                      $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
				  }
			  }


			  // LINGUA PORTUGUESA   usado para pegar o total de faltas que nos anos iniciais é lançada apenas na lingua portuguesa
			  $faltas = voltaFaltas($oAluno->iMatricula,'LINGUA PORTUGUESA',$oTurmaEtapa->sTurma);
			  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $faltas[0]["ed72_i_numfaltas"], 1, 0, "C");        //1?
			  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $faltas[1]["ed72_i_numfaltas"], 1, 0, "C");        //2?
			  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $faltas[2]["ed72_i_numfaltas"], 1, 0, "C");        //REC S
         	  $totalfaltas = $faltas[0]["ed72_i_numfaltas"]+$faltas[1]["ed72_i_numfaltas"]+$faltas[2]["ed72_i_numfaltas"];


              /**
                 * Autor: Uemerson Santana
                 * Data: 05/06/2025
                 * Demanda: 17356
                 */
              // Verificar situação do aluno antes de exibir total e percentual
                if ( stripos(trim($sitDados->ed60_c_situacao), 'TRANSFERIDO') !== false ||
                stripos(trim($sitDados->ed60_c_situacao), 'TROCA DE TURMA') !== false ) {
                $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C"); // total vazio
                $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C'); // percentual vazio
                } else {
                $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $totalfaltas , 1, 0, "C");

                $aulasdadas =  percfrequencia($_GET["calendario"]);
                $cfreq = (($aulasdadas - ($faltas[0]["ed72_i_numfaltas"] + $faltas[1]["ed72_i_numfaltas"] + $faltas[2]["ed72_i_numfaltas"])) / $aulasdadas) * 100;
                $nm = explode(".", $cfreq);
                $cfreq = $nm[0];

                $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $cfreq, 1, 0, 'C'); // percentual de frequencia
                }

              /**
               * Autor: Uemerson Santana
               * Data: 18/12/2025
               * Demanda: 18034
               * Razao: Para ANOS INICIAIS, verificar frequência para definir resultado final.
               *        Alunos com frequência < 75% sem reclassificação = REPROVADO.
               *        Mesma lógica usada em edu2_fichaindividualaluno002_old.php (função resultado_final2).
               */
              if( trim($sitDados->ed60_c_situacao) == 'MATRICULADO')
			  {
                  // Buscar resultado do encerramento oficial
                  $sResultadoEncerramento = resultado_final_rpc(
                    $oTurmaSelecionada->iEtapa,
                    $oTurmaSelecionada->iTurma,
                    $oAluno->iMatricula,
                    $oTurmaEtapa->iAnoCalendario
                  );

                  if (trim($sResultadoEncerramento) !== '') {
                      // Já existe resultado de encerramento: usa o texto
                      $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, utf8_decode($sResultadoEncerramento), 1, 1, 'C');
                  } else {
                      // Sem encerramento ainda: mostrar EM ANDAMENTO para TODOS
							  $oPdf->SetFont("arial", 'B', 5);
                      $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'EM ANDAMENTO', 1, 1, 'C');
							  $oPdf->SetFont("arial", '', 7);
				  }
			  }else{
		          $oPdf->SetFont("arial", 'B', 5);
		          $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, trim($sitDados->ed60_c_situacao), 1, 1, 'C');
				  $oPdf->SetFont("arial", '', 7);
			  }
		  }
//***********************************************************************************************************************************************************
		  if( substr($calDados->anos,0,12) == 'EJA INICIAIS') //******************************** Eja Anos iniciais ******************************************
		  {
			  $oPdf->SetFont("arial", '', 6);
			  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');
			  if( strlen(trim( $oAluno->sNome )) >37)
			  {
				  $oPdf->SetFont("arial", '', 5);
			  }
			  $oPdf->Cell(57, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
			  $oPdf->SetFont("arial", '', 7);
			  // já existia o array $xfaltas, então eu utilizei para buscar as notas - Divaldo 11/10/2024
			  $ejaport = false;
			  $ejahist = false;
			  $ejageog = false;
			  $ejacien = false;
			  $ejamate = false;

			  //LINGUA PORTUGUESA
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'LINGUA PORTUGUESA',$oTurmaEtapa->sTurma);
			  $media = 0;
			  if( $xfaltas[0]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
              }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2?
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null)
			  {

		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
			  }
			  if( $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
		          $ejaport = true;
		          if( $xfaltas[3]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[3]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
			  }
			  if( $xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null  or $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] >0)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/2;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/3;
				  }
				  if( $xfaltas[3]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"]+$xfaltas[3]["ed72_i_valornota"])/4;
				  }

				  $mediaP = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
                  }else{
				     $mediaA = '0';
                  }
		          if( $mediaP < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }

				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
			  }

			  //HISTÓRIA  **********************************************
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'HISTÓRIA',$oTurmaEtapa->sTurma);
			  $media = 0;
			  if( $xfaltas[0]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2?
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
			  }
			  if( $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
		          $ejahist = true;
		          if( $xfaltas[3]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[3]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
			  }
			  if( $xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null or $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] >0)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/2;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/3;
				  }
				  if( $xfaltas[3]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"]+$xfaltas[3]["ed72_i_valornota"])/4;
				  }

				  $mediaH = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
                  }else{
				     $mediaA = '0';
                  }
		          if( $mediaH < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }

				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
			  }

			  //GEOGRAFIA  ************************************************************************
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'GEOGRAFIA',$oTurmaEtapa->sTurma);
			  $media = 0;
			  if( $xfaltas[0]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
              }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
  			      $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2?
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
			  }
			  if( $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
		          $ejageog = true;
		          if( $xfaltas[3]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[3]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
			  }
			  if( $xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null or $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] >0)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/2;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/3;
				  }
				  if( $xfaltas[3]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"]+$xfaltas[3]["ed72_i_valornota"])/4;
				  }

				  $mediaG = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
                  }else{
				     $mediaA = '0';
                  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
			  }

			  //CIENCIAS
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'CIENCIAS',$oTurmaEtapa->sTurma);
			  $media = 0;
			  if( $xfaltas[0]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null)
			  {

		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
			  }
			  if( $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
		          $ejacien = true;
		          if( $xfaltas[3]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[3]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
			  }
			  if( $xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null or $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] >0)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/2;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/3;
				  }
				  if( $xfaltas[3]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"]+$xfaltas[3]["ed72_i_valornota"])/4;
				  }

				  $mediaC = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
                  }else{
				     $mediaA = '0';
                  }
		          if( $mediaC < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
			  }



			  //MATEMATICA
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'MATEMÁTICA',$oTurmaEtapa->sTurma);
			  $media = 0;
			  if( $xfaltas[0]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[0]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1?
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
			  }
			  if( $xfaltas[1]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[1]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2?
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2?
			  }
			  if( $xfaltas[2]["ed72_i_valornota"] <> null)
			  {
		          if( $xfaltas[2]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
			  }
			  if( $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
		          $ejamate = true;
		          if( $xfaltas[3]["ed72_i_valornota"] < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[3]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
			  }
			  if( $xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null or $xfaltas[3]["ed72_i_valornota"] <> null)
			  {
				  if( $xfaltas[0]["ed72_i_valornota"] >0)
				  {
					  $media = $xfaltas[0]["ed72_i_valornota"];
				  }
				  if( $xfaltas[1]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"])/2;
				  }
				  if( $xfaltas[2]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"])/3;
				  }
				  if( $xfaltas[3]["ed72_i_valornota"] >0 )
				  {
					  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[2]["ed72_i_valornota"]+$xfaltas[3]["ed72_i_valornota"])/4;
				  }

				  $mediaM = $media; // media antes da formatacao para 1 casa decimal com virgula
				  if( $media > 0)
				  {
					  $nm = explode(".", $media);
					  if( substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ')
					  {
						  $nm[1] = '0';
					  }
					  $media = $nm[0] . "," . substr($nm[1], 0, 1);
					  $mediaA = $media;     // media anual igual a média
                  }else{
				     $mediaA = '0';
                  }
		          if( $mediaM < 5 )
				  {
					  $oPdf->SetFont("arial", 'B', 7);
				  }else{
					  $oPdf->SetFont("arial", '', 7);
				  }
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $mediaA , 1, 0, "C");
			  }else{
				  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, "", 1, 0, "C");
			  }


			  // LINGUA PORTUGUESA   usado para pegar o total de faltas que nos anos iniciais é lançada apenas na lingua portuguesa
			  $faltas = voltaFaltas($oAluno->iMatricula,'LINGUA PORTUGUESA',$oTurmaEtapa->sTurma);
			  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $faltas[0]["ed72_i_numfaltas"], 1, 0, "C");        //1?
			  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $faltas[1]["ed72_i_numfaltas"], 1, 0, "C");        //2?
			  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $faltas[2]["ed72_i_numfaltas"], 1, 0, "C");        //3?
			  $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $faltas[3]["ed72_i_numfaltas"], 1, 0, "C");        //4?
         	  $totalfaltas = $faltas[0]["ed72_i_numfaltas"]+$faltas[1]["ed72_i_numfaltas"]+$faltas[2]["ed72_i_numfaltas"]+$faltas[3]["ed72_i_numfaltas"];


                /*
                            if( $mediaP >= 5 and $mediaH >= 5 and $mediaG >= 5 and $mediaC >= 5 and $mediaM >= 5)
                            {
                                $oPdf->Cell(12, $oConfigRelatorio->iAlturaLinha, 'APR', 1, 1, 'C'); // resultado final
                            }else{
                                $oPdf->Cell(12, $oConfigRelatorio->iAlturaLinha, 'REP', 1, 1, 'C'); // resultado final
                            }
                */
                //*******************************************************************************************************
			  $sqlTransf = "
			               select
						   ed60_c_situacao
						   from
						   matricula
						   where
						   ed60_i_codigo = {$oAluno->iMatricula}
			               ";
		      $result    = db_query($sqlTransf);
		      $sitDados  = db_utils::fieldsMemory($result,0);

              /*
              * Autor: Uemerson Santana
              * Data: 05/06/2025
              * Demanda: 17356
              */
              // Verificar situação do aluno antes de exibir total e percentual
                if ( stripos(trim($sitDados->ed60_c_situacao), 'TRANSFERIDO') !== false ||
                stripos(trim($sitDados->ed60_c_situacao), 'TROCA DE TURMA') !== false ) {
                    $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C"); // total vazio
                    $oPdf->Cell(7, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C'); // percentual vazio
                } else {
                    $oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, $totalfaltas , 1, 0, "C");

                    $aulasdadas =  percfrequencia($_GET["calendario"]);
                    $cfreq = (($aulasdadas - ($faltas[0]["ed72_i_numfaltas"] + $faltas[1]["ed72_i_numfaltas"] + $faltas[2]["ed72_i_numfaltas"] + $faltas[3]["ed72_i_numfaltas"])) / $aulasdadas) * 100;
                    $nm = explode(".", $cfreq);
                    $cfreq = $nm[0];

                    $oPdf->Cell(7, $oConfigRelatorio->iAlturaLinha, $cfreq, 1, 0, 'C'); // percentual de frequencia
                }

              if( trim($sitDados->ed60_c_situacao) == 'MATRICULADO')
			  {
                  // Buscar resultado do encerramento oficial
          $sResultadoEncerramento = resultado_final_rpc(
            $oTurmaSelecionada->iEtapa,
            $oTurmaSelecionada->iTurma,
            $oAluno->iMatricula,
            $oTurmaEtapa->iAnoCalendario
          );

          if (trim($sResultadoEncerramento) !== '') {
            // Já existe resultado de encerramento: usa o texto
            $oPdf->Cell(12, $oConfigRelatorio->iAlturaLinha, utf8_decode($sResultadoEncerramento), 1, 1, 'C');
          } else {
                      // Sem encerramento ainda: mostrar EM AND para TODOS
              $oPdf->SetFont("arial", 'B', 5);
                      $oPdf->Cell(12, $oConfigRelatorio->iAlturaLinha, 'EM AND', 1, 1, 'C');
              $oPdf->SetFont("arial", '', 7);
            }
			  }else{
				  $situcao = $sitDados->ed60_c_situacao;
				  if( trim($sitDados->ed60_c_situacao) == "TRANSFERIDO FORA")
				  {
					  $situcao = 'TR. FORA';
				  }elseif(trim($sitDados->ed60_c_situacao) == "TRANSFERIDO REDE")
				  {
					  $situcao = 'TR. REDE';
				  }elseif(trim($sitDados->ed60_c_situacao) == "TROCA DE MODALIDADE")
				  {
					  $situcao = 'TRO. MOD.';
				  }elseif(trim($sitDados->ed60_c_situacao) == "TROCA DE TURMA")
				  {
					  $situcao = 'TRO. TURM.';
				  }

		          $oPdf->SetFont("arial", 'B', 5);
		          $oPdf->Cell(12, $oConfigRelatorio->iAlturaLinha, $situcao, 1, 1, 'C');
				  $oPdf->SetFont("arial", '', 7);
			  }

//*******************************************************************************************************


		  }

//***********************************************************************************************************************************************************
		  if( substr($calDados->anos,0,12) == 'ED. INFANTIL') //******************************** EDUCACAO INFANTIL ******************************************
		  {
			  $oPdf->SetFont("arial", '', 6);
			  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');
			  $oPdf->Cell(115, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
			  $oPdf->SetFont("arial", '', 7);
			  // já existia o array $xfaltas, então eu utilizei para buscar as notas - Divaldo 11/10/2024

			  // LINGUA PORTUGUESA   usado para pegar o total de faltas que nos anos iniciais é lançada apenas na lingua portuguesa
			  $faltas = voltaFaltas($oAluno->iMatricula,'CAMPOS DE EXPERIENCIA',$oTurmaEtapa->sTurma);
			  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $faltas[0]["ed72_i_numfaltas"], 1, 0, "C");        //1?
			  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $faltas[1]["ed72_i_numfaltas"], 1, 0, "C");        //2?
			  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $faltas[2]["ed72_i_numfaltas"], 1, 0, "C");        //REC S
         	  $totalfaltas = $faltas[0]["ed72_i_numfaltas"]+$faltas[1]["ed72_i_numfaltas"]+$faltas[2]["ed72_i_numfaltas"];
			  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $totalfaltas , 1, 0, "C");

              $aulasdadas =  percfrequencia($_GET["calendario"]);

			  $cfreq = (($aulasdadas - ($faltas[0]["ed72_i_numfaltas"] + $faltas[1]["ed72_i_numfaltas"] + $faltas[2]["ed72_i_numfaltas"])) / $aulasdadas) * 100;
			  $nm = explode(".", $cfreq);
			  $cfreq = $nm[0];



                $sqlTransf = "
                select
                ed60_c_situacao
                from
                matricula
                where
                ed60_i_codigo = {$oAluno->iMatricula}
                ";
                $result    = db_query($sqlTransf);
                $sitDados  = db_utils::fieldsMemory($result,0);

                 /**
                 * Autor: Uemerson Santana
                 * Data: 18/04/2025
                 * Demanda: 17301
                 */
                if ( stripos(trim($sitDados->ed60_c_situacao), 'TRANSFERIDO') !== false ||
                    stripos(trim($sitDados->ed60_c_situacao), 'TROCA DE TURMA') !== false ) {
                    $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 1, 'C');
                } else {
                    $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $cfreq, 1, 1, 'C'); // percentual de frequencia
                }
            /**
             * Autor: Uemerson Santana
             * Data: 18/04/2025
             * Demanda: 17301
             */
            //   if( trim($sitDados->ed60_c_situacao) == 'MATRICULADO')
			//   {
		    //       if( $oTurmaEtapa->sEtapa <> '1º ANO' and $oTurmaEtapa->sEtapa <> '2º ANO' )
			// 	  {
			// 		  //alunos normais, avaliados por nota
			// 		  if( $port == true and $hist == true and $geog == true and $cien == true and $mate == true )// todas as notas foram lançadas, e pode verificar se o aluno foi aprovado
			// 		  {
			// 			  if( $mediaP < 5 or $mediaH < 5 or $mediaG < 5 or $mediaC < 5 or $mediaM < 5 ) // se alguma nota for menor que 5 reprova
			// 			  {
			// 				  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'REP', 1, 1, 'C'); // resultado final
			// 			  }else{
			// 				  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'APR', 1, 1, 'C'); // resultado final
			// 			  }
			// 		  }else{
			// 			  // alunos especiais, avaliados por parecer
			// 			  $conceitos = checarConceitoInfantil($oAluno->iMatricula,$oTurmaEtapa->sTurma); // verifica se na tabela diariofinal o aluno esta aprovado
			// 			  if( $conceitos )
			// 			  {
			// 				  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'APR', 1, 1, 'C'); // resultado final
			// 		      }else{
			// 				  $oPdf->SetFont("arial", 'B', 5);
			// 				  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'EM ANDAMENTO', 1, 1, 'C'); // todas as notas não foram lançadas
			// 				  $oPdf->SetFont("arial", '', 7);
			// 			  }
			// 		  }
			// 	  }else{ // alunos avaiiados por conceito
			// 	      if( $oTurmaEtapa->sEtapa == '1º ANO' )
			// 		  {
			// 			  $conceitos = checarConceito($oAluno->iMatricula,$oTurmaEtapa->sTurma); //na diariofinal final o aluno foi aprovado
			// 			  if( $conceitos )
			// 			  {
			// 				  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'APR', 1, 1, 'C'); // resultado final
			// 			  }else{
			// 				  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'REP', 1, 1, 'C'); // resultado final
			// 			  }
			// 		  }else{
			// 			  if( trim($xfaltas[2]["ed72_c_valorconceito"])<> '' ) // ultimo conceito foi lançado
			// 			  {
			// 				  $conceitos = checarConceito($oAluno->iMatricula,$oTurmaEtapa->sTurma); //na diariofinal final o aluno foi aprovado
			// 				  if( $conceitos )
			// 				  {
			// 					  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'APR', 1, 1, 'C'); // resultado final
			// 				  }else{
			// 					  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'REP', 1, 1, 'C'); // resultado final
			// 				  }
			// 			  }else{
			// 				  $oPdf->SetFont("arial", 'B', 5);
			// 				  $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'EM ANDAMENTO', 1, 1, 'C'); // resultado final
			// 				  $oPdf->SetFont("arial", '', 7);
			// 			  }

			// 		  }
			// 	  }
			//   }else{
		    //       $oPdf->SetFont("arial", 'B', 5);
		    //       $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, trim($sitDados->ed60_c_situacao), 1, 1, 'C');
			// 	  $oPdf->SetFont("arial", '', 7);
			//   }
	     }
//*******************************************************************************************************

		  /**
		   * Imprime colunas de avaliação vazia
		   */
		  if ($iTotalDisciplina < $oConfigRelatorio->iMaximoDisciplinaPagina) {
			//imprimeQuadroAvaliacaoVazio ($oPdf, $oTurmaEtapa, $oTurmaEtapa->iTotalDisciplina, $oConfigRelatorio, false);
		  }

		}//foreach do aluno //bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb
        montaQuadroLegendaAssinaturaC($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa, $oTurmaSelecionada->iTurma, $oTurmaSelecionada->iEtapa, $regenc, $assAdicion, $assAtivid, $discipl->ed12_i_codigo);
        $lPrimeiraPagina = true;

}
  unset($oTurmaEtapa);
} //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa

//******************************************************** IMPRIME DISCIPLINAS POR CONCEITO *******************************************************************
foreach ($aTurmas as $oTurmaEtapa) {  //bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb

  $head1 = "Resumo Anual";
  $head2 = pdf_text("Curso: {$oTurmaEtapa->sCurso}");
  $head3 = pdf_text("Turma: {$oTurmaEtapa->sTurma}");
  $head4 = pdf_text("Calendário: {$oTurmaEtapa->sCalendario}");
  $head5 = pdf_text("Etapa: {$oTurmaEtapa->sEtapa}");
  $head6 = pdf_text("Turno: {$oTurmaEtapa->sTurno}");
  $discip= utf8_decode($disciplina);

  $lPrimeiraPagina = true;
  $iPaginas        = count($oTurmaEtapa->aPaginas);
  $diasletivos     = $oTurmaEtapa->diasLetivos;
  $disciplinas     = buscaCodDisciplinas($calendario,$codTurma);
/*
Pedido de Paloma em 18/12/2024 - Divaldo
A condição abaixo, não imprimia as disciplinas ARTE, TECNOLOGIA E INOVAÇÃO, LINGUA INGLESA, EDUCAÇÃO FÍSICA
para o primeiro ano.
Paloma pediu para acrescentar
*/

//  if( $oTurmaEtapa->sEtapa <> '1º ANO')
//  {

	for ($iPagina = 0; $iPagina < $iPaginas; $iPagina ++) {

	/**
	 * A cada página, calcula em tempo de execução o tamanho de variáveis necessárias para o cálculo
	 * de algumas colunas do relatório. Essas variáveis serão recalculadas a cada turma e página emitida
	 */
		 calculaTamanhoDeCelulasDinamicas($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);

		 $iAlunosImpressoPagina = 0;
		 $iAlunosTurma          = count($oTurmaEtapa->aAlunos);

		 $iTotalDisciplina      = $oTurmaEtapa->iTotalDisciplina;
		 $iLarguraCelulaParecer = $oTurmaEtapa->iLarguraCelulaParecer;
		 $iLarguraDisciplina    = $oTurmaEtapa->iLarguraDisciplina;
		 $iLarguraAvaliacao     = $iLarguraDisciplina - 5;
		foreach ($oTurmaEtapa->aAlunos as $oAluno) {//bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb

		  $sql = "
				 select
				 ed52_c_descr as anos
				 from
				 calendario
				 where
				 ed52_i_codigo = ".$_GET["calendario"];
		  $resultado = db_query($sql);
		  $calDados  = db_utils::fieldsMemory($resultado,0);

		  if ( $lPrimeiraPagina ) {
			  $lPrimeiraPagina = false;
			  if( substr($calDados->anos,0,13) == 'ANOS INICIAIS')
			  {
				  adicionaHeaderC($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);
			  }
		  }

		  if (!$oConfigRelatorio->lTrocaTurma && $oAluno->sSituacao == 'TROCA DE TURMA') {
			continue;
		  }

		  $iAlunosImpressoPagina ++;
		  if ($iAlunosImpressoPagina > $oConfigRelatorio->iAlunosPorPagina) {
			$iAlunosImpressoPagina = 1;
			//montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa);
			if( substr($calDados->anos,0,13) == 'ANOS INICIAIS')
			{
				montaQuadroLegendaAssinaturaC($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa, $oTurmaSelecionada->iTurma, $oTurmaSelecionada->iEtapa, $regenc, $assAdicion, $assAtivid, $discipl->ed12_i_codigo);
			}
			if ($iAlunosImpressoPagina < $iAlunosTurma) {
				if( substr($calDados->anos,0,13) == 'ANOS INICIAIS')
				{
					 adicionaHeaderC($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);
				}
			}
		  }

		  if( substr($calDados->anos,0,13) == 'ANOS INICIAIS') //******************************** Anos iniciais ******************************************
		  {
			  $oPdf->SetFont("arial", '', 6);
			  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');
			  if( strlen(trim( $oAluno->sNome )) >37)
			  {
				  $oPdf->SetFont("arial", '', 5);
			  }
			  $oPdf->Cell(57, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
			  $oPdf->SetFont("arial", '', 7);
			  // já existia o array $xfaltas, então eu utilizei para buscar as notas - Divaldo 11/10/2024

			  //ARTE
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'ARTE',$oTurmaEtapa->sTurma);
			  if( $xfaltas[0]["ed72_c_valorconceito"] <> null)
			  {
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //2?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //3
                  /*
                    @autor Uemerson Santana
                    @data 22/05/2025
                    @demanda: 17356
                  */
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"] , 1, 0, "C"); // conceito final
			  }else{
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '' , 1, 0, "C");
			  }

			  // TECNOLOGIA E INOVAÇÃO
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'TECNOLOGIA E INOVAÇÃO',$oTurmaEtapa->sTurma);
			  if( $xfaltas[0]["ed72_c_valorconceito"] <> null)
			  {
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //2?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //REC S
                  /*
                    @autor Uemerson Santana
                    @data 22/05/2025
                    @demanda: 17356
                  */
				  $oPdf->Cell(13, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"] , 1, 0, "C"); // conceito final
			  }else{
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '' , 1, 0, "C");
			  }

			  // L?NGUA INGLESA
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'LÍNGUA INGLESA',$oTurmaEtapa->sTurma);
			  if( $xfaltas[0]["ed72_c_valorconceito"] <> null)
			  {
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //2?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //REC S
                  /*
                    @autor Uemerson Santana
                    @data 22/05/2025
                    @demanda: 17356
                  */
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"] , 1, 0, "C"); // conceito final
			  }else{
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '' , 1, 0, "C");
			  }

			  // EDUCA??O FISICA
			  $xfaltas = voltaFaltas($oAluno->iMatricula,'EDUCAÇÃO FÍSICA',$oTurmaEtapa->sTurma);
			  if( $xfaltas[0]["ed72_c_valorconceito"] <> null)
			  {
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //2?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //REC S
                  /*
                    @autor Uemerson Santana
                    @data 22/05/2025
                    @demanda: 17356
                  */
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"] , 1, 1, "C"); // conceito final
			  }else{
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2?
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3
				  $oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '' , 1, 1, "C");
			  }
		  }
		}//foreach do aluno //bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb
		if( substr($calDados->anos,0,13) == 'ANOS INICIAIS')
		{
			 montaQuadroLegendaAssinaturaC($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa, $oTurmaSelecionada->iTurma, $oTurmaSelecionada->iEtapa, $regenc, $assAdicion, $assAtivid, $discipl->ed12_i_codigo);
		}
		$lPrimeiraPagina = true;
    }
//  }
  unset($oTurmaEtapa);
} //bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb





/**
 * Renderiza quadro das legendas
 * @param FPDF $oPdf
 * @param stdClass $oConfigRelatorio
 */
function montaQuadroLegendaAssinatura(FPDF $oPdf, $oConfigRelatorio, $lUltimoPeriodo, $oTurmaEtapa, $turma, $etapa, $regencia, $assAdic, $Ativ, $disciplina) {
  global $cgmAssAdic;

//testa($oTurmaEtapa);
  /**
   * Soma com base na configuração do layout a largura do quadro
   */
;
  $iLarguraQuadro  = $oConfigRelatorio->iLarguraTotalDisciplinas;
  $iLarguraQuadro += $oConfigRelatorio->iColunaCodigo ;
  $iLarguraQuadro += $oTurmaEtapa->iColunaNome;
  $iLarguraQuadro += ($oConfigRelatorio->iColunaNumero*3);
  if (!$lUltimoPeriodo) {
    $iLarguraQuadro += $oConfigRelatorio->iColunaPareceres;
  }

  $iXInicial = $oPdf->GetX();
  $iYInicial = $oPdf->GetY();
  $oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $iLarguraQuadro, 21);

  if ($oConfigRelatorio->comLegenda) {

    $sLegendasCabecalho  = "Nº: Número da classificação do aluno;  S: Saída;  Ft.: Nº de faltas na disciplina;";
    $sLegendasCabecalho .= "  NT: Nota;  NP: Nota parcial;  TF: Total de faltas no período.";

    $sLegendasSituacao  = montaLegendaSituacoes();

    $oPdf->SetY($iYInicial+1);
    $oPdf->SetFont("arial", 'b', 5);

    $iAlturaLinhaLegenda = $oConfigRelatorio->iAlturaLinha - 1.5;

    $oPdf->Cell(20, $iAlturaLinhaLegenda, utf8_decode("Legendas Cabeçalho: "));
    $oPdf->SetFont("arial", '', 5);
    $oPdf->MultiCell(175, $iAlturaLinhaLegenda, utf8_decode($sLegendasCabecalho));

    $oPdf->SetFont("arial", 'b', 5);
    $oPdf->Cell(20, $iAlturaLinhaLegenda, utf8_decode("Legendas Situação: "));
    $oPdf->SetFont("arial", '', 5);
    $oPdf->MultiCell(170, $iAlturaLinhaLegenda, utf8_decode($sLegendasSituacao));
  }

  /**
   * Assinatura
  */
  $oPdf->SetY($iYInicial+2);
//  $oPdf->Cell($iXInicial+100, 4, "","LR",0,"L");
//  $oPdf->Cell(100, 4, "","R",1,"L");

  $oPdf->Cell($iXInicial+97, 3, "Obs: ","RB",0,"L");
  $oPdf->Cell(40, 3, "","B",0);
  $oPdf->Cell(40, 3, "Encerrado em: ___/___/_____","B",0,"L");
  $oPdf->Cell(40, 3, "Aulas previstas: _____________","B",0,"L");
  $oPdf->Cell(54, 3, "Aulas dadas: _____________","B",1,"L");


  $doc  ="select
	  	  z01_numcgm,
		  z01_nome
		  from
		  regenciahorario
		  inner join regencia         on ed58_i_regencia   = ed59_i_codigo
		  inner join rechumano        on ed20_i_codigo     = ed58_i_rechumano
		  inner join rechumanopessoal on ed284_i_rechumano = ed20_i_codigo
		  inner join rhpessoal        on rh01_regist       = ed284_i_rhpessoal
		  inner join cgm              on z01_numcgm        = rh01_numcgm
		  inner join disciplina       on ed12_i_codigo     = ed59_i_disciplina
		  where
          ed12_i_codigo  = ".$disciplina."
		  and
		  ed58_ativo is true
		  and
		  ed59_i_codigo = ".$regencia;



// verificar porque o regente esta retornando vazio

  $sql1 = pg_query($doc);
  $reg = db_utils::fieldsMemory($sql1,0);
    if( !pg_num_rows($sql1) )
    {
        $sql = "
	        select
			z01_nome
			from
			cgm
			where
			z01_numcgm = (
							select
							distinct (case when rh01_numcgm is null then  ed285_i_cgm else rh01_numcgm end) as cgm
							from rechumano
							left join rechumanopessoal  on  rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo
							left join rhpessoal  on  rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
							left join cgm as cgmrh on  cgmrh.z01_numcgm = rhpessoal.rh01_numcgm
							left join db_config  on  db_config.codigo = rhpessoal.rh01_instit
							left join rhpessoalmov on rhpessoalmov.rh02_anousu  = 0 and rhpessoalmov.rh02_mesusu  = 0  and rhpessoalmov.rh02_regist  = rhpessoal.rh01_regist and rhpessoalmov.rh02_instit  = {$instit}
							left join rhregime as regimerh on  regimerh.rh30_codreg = rhpessoalmov.rh02_codreg
							left join rhlota  on  rhlota.r70_codigo = rhpessoal.rh01_lotac
							left join rhpesdoc  on  rhpesdoc.rh16_regist = rhpessoal.rh01_regist
							left join rhestcivil  on  rhestcivil.rh08_estciv = rhpessoal.rh01_estciv
							left join rhraca  on  rhraca.rh18_raca = rhpessoal.rh01_raca
							left join rhfuncao  on  rhfuncao.rh37_funcao = rhpessoal.rh01_funcao and rh37_instit  = rh02_instit
							left join rhinstrucao  on  rhinstrucao.rh21_instru = rhpessoal.rh01_instru
							left join rhnacionalidade  on  rhnacionalidade.rh06_nacionalidade = rhpessoal.rh01_nacion
							left join rechumanocgm  on  rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo
							left join cgm as cgmcgm on  cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm
							left join cgmdoc on  cgmdoc.z02_i_cgm = cgmcgm.z01_numcgm
							left join rhregime as regimecgm on  regimecgm.rh30_codreg = rechumano.ed20_i_rhregime
							left join cgmfisico on cgmfisico.z04_numcgm = rhpessoal.rh01_numcgm and cgmfisico.z04_nomesocial <> ''
							inner join rechumanoescola  on  rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
							inner join escola  on  escola.ed18_i_codigo = rechumanoescola.ed75_i_escola
							left join relacaotrabalho  on  relacaotrabalho.ed23_i_rechumanoescola = rechumanoescola.ed75_i_codigo
							left join tipohoratrabalho on  tipohoratrabalho.ed128_codigo = ed23_tipohoratrabalho
							left join rechumanoativ  on  rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo
							left join atividaderh  on  atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade
							left join disciplina  on  disciplina.ed12_i_codigo = relacaotrabalho.ed23_i_disciplina
							left join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina
							left join ensino  on  ensino.ed10_i_codigo = disciplina.ed12_i_ensino
							inner join pais  on  pais.ed228_i_codigo = rechumano.ed20_i_pais
							left  join censouf as censoufident on  censoufident.ed260_i_codigo = rechumano.ed20_i_censoufident
							left  join censouf as censoufnat on  censoufnat.ed260_i_codigo = rechumano.ed20_i_censoufnat
							left  join censouf as censoufcert on  censoufcert.ed260_i_codigo = rechumano.ed20_i_censoufcert
							left  join censouf as censoufender on  censoufender.ed260_i_codigo = rechumano.ed20_i_censoufender
							left  join censomunic as censomunicnat on  censomunicnat.ed261_i_codigo = rechumano.ed20_i_censomunicnat
							left  join censomunic as censomunicender on  censomunicender.ed261_i_codigo = rechumano.ed20_i_censomunicender
							left  join censoorgemissrg  on  censoorgemissrg.ed132_i_codigo = rechumano.ed20_i_censoorgemiss
							left  join censocartorio  on  censocartorio.ed291_i_codigo = rechumano.ed20_i_censocartorio
							left  join regimetrabalho on  regimetrabalho.ed24_i_codigo = relacaotrabalho.ed23_i_regimetrabalho
							where
							ed20_i_codigo = (
											select
											distinct ed58_i_rechumano
											from
											regenciahorario
											where
											ed58_i_regencia = {$regencia}
											and ed58_ativo is true
											order by ed58_i_rechumano
											)
						  )
	    ";
		$sql2 = pg_query($sql);
		$reg = '';
		if( pg_num_rows($sql2) > 1 )
		{
			for($x=0;$x<pg_num_rows($sql2);$x++)
			{
				if( $x > 0)
				{
					$prof .= "-";
				}
				$reg  = db_utils::fieldsMemory($sql2,$x);
				$prof .=  $reg->z01_nome;
			}
		}else{

			$reg = db_utils::fieldsMemory($sql2,0);
			$prof =  $reg->z01_nome;
		}
    }



  $esc  ="select
	  	  z01_numcgm,
		  z01_nome
		  from
		  rechumano
		  inner join rechumanopessoal on ed284_i_rechumano = ed20_i_codigo
		  inner join rhpessoal        on rh01_regist       = ed284_i_rhpessoal
		  inner join cgm              on z01_numcgm        = rh01_numcgm
		  inner join escoladiretor    on ed254_i_rechumano = ed20_i_codigo
		  where
          ed254_i_escola = ".db_getsession("DB_coddepto");

  $sql2 = pg_query($esc);
  $dir  = db_utils::fieldsMemory($sql2,0);

  // Busca matrículas dos assinantes
  $matriculaReg = '';
  $matriculaDir = '';
  $matriculaAssAdic = '';

  /**
   * Autor: Uemerson Santana
   * Data: 27/11/2025
   * Demanda: 17982
   * Razão: Corrigida busca da matrícula do regente para filtrar pela escola vinculada através de rechumanoescola,
   *        garantindo que quando o profissional possui múltiplas matrículas, seja retornada a correta vinculada à escola atual.
   *        Removido substr que removia os 2 primeiros dígitos, exibindo agora a matrícula completa.
   */
  // Matrícula do Regente
  if (isset($reg->z01_numcgm)) {
    $iEscola = db_getsession("DB_coddepto");
    $sqlMatReg = "SELECT ed284_i_rhpessoal as matricula
                  FROM rechumanopessoal
                  INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                  INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                  INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                  WHERE rh01_numcgm = {$reg->z01_numcgm}
                    AND rechumanoescola.ed75_i_escola = {$iEscola}
                  LIMIT 1";
    $rsMatReg = pg_query($sqlMatReg);
    if (pg_num_rows($rsMatReg) > 0) {
      $oMatReg = db_utils::fieldsMemory($rsMatReg, 0);
      // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
      $matriculaReg = !empty($oMatReg->matricula) ? $oMatReg->matricula : '';
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
  // Matrícula do Diretor
  if (isset($dir->z01_numcgm)) {
    $iEscola = db_getsession("DB_coddepto");
    $sqlMatDir = "SELECT ed284_i_rhpessoal as matricula
                  FROM rechumanopessoal
                  INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                  INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                  INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                  WHERE rh01_numcgm = {$dir->z01_numcgm}
                    AND rechumanoescola.ed75_i_escola = {$iEscola}
                  LIMIT 1";
    $rsMatDir = pg_query($sqlMatDir);
    if (pg_num_rows($rsMatDir) > 0) {
      $oMatDir = db_utils::fieldsMemory($rsMatDir, 0);
      // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
      $matriculaDir = !empty($oMatDir->matricula) ? $oMatDir->matricula : '';
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
  // Matrícula da Assinatura Adicional (Supervisor)
  if (!empty($cgmAssAdic)) {
    $iEscola = db_getsession("DB_coddepto");
    $sqlMatAdic = "SELECT ed284_i_rhpessoal as matricula
                   FROM rechumanopessoal
                   INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                   INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                   INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                   WHERE rh01_numcgm = {$cgmAssAdic}
                     AND rechumanoescola.ed75_i_escola = {$iEscola}
                   LIMIT 1";
    $rsMatAdic = pg_query($sqlMatAdic);
    if (pg_num_rows($rsMatAdic) > 0) {
      $oMatAdic = db_utils::fieldsMemory($rsMatAdic, 0);
      // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
      $matriculaAssAdic = !empty($oMatAdic->matricula) ? $oMatAdic->matricula : '';
    }
  }

  $oPdf->SetFont("arial", 'b', 5);
  $oPdf->Cell($iXInicial+97, 4, "","RB",1,"L");
//  $oPdf->Cell(30, 4, "","",1);
  $oPdf->Cell($iXInicial+97, 4, "","RB",1,"L");
//  $oPdf->Cell(30, 4, "","",1);

  $oPdf->Line($iXInicial+110, $iYInicial+12, $iXInicial+160, $iYInicial+12);
  $oPdf->Line($iXInicial+165, $iYInicial+12, $iXInicial+215, $iYInicial+12);
  $oPdf->Line($iXInicial+220, $iYInicial+12, $iXInicial+270, $iYInicial+12);


  $oPdf->Cell($iXInicial+97, 4, "","RB",0,"L");



  $oPdf->Cell(60, 4,$reg->z01_nome,"",0,"C");
  $oPdf->Cell(50, 4,      $assAdic,"",0,"C");
  $oPdf->Cell(50, 4,$dir->z01_nome,"",1,"C");

  $oPdf->Cell($iXInicial+97, 4, "","RB",0,"L");

  $oPdf->Cell(60, 4, "             Regente               ","",0,"C");
  $oPdf->Cell(50, 4, $Ativ,"",0,"C");
  $oPdf->Cell(50, 4, "         Diretor(a) Geral       	 ","",1,"C");

  $oPdf->Cell($iXInicial+97, 4, "","RB",0,"L");

  // Exibir matrículas
  $textoMatriculaReg = !empty($matriculaReg) ? "Matrícula: " . $matriculaReg : "";
  $textoMatriculaAssAdic = !empty($matriculaAssAdic) ? "Matrícula: " . $matriculaAssAdic : "";
  $textoMatriculaDir = !empty($matriculaDir) ? "Matrícula: " . $matriculaDir : "";

  $oPdf->Cell(60, 4, utf8_decode($textoMatriculaReg),"",0,"C");
  $oPdf->Cell(50, 4, utf8_decode($textoMatriculaAssAdic),"",0,"C");
  $oPdf->Cell(50, 4, utf8_decode($textoMatriculaDir),"",1,"C");

}

function montaQuadroLegendaAssinaturaC(FPDF $oPdf, $oConfigRelatorio, $lUltimoPeriodo, $oTurmaEtapa, $turma, $etapa, $regencia, $assAdic, $Ativ, $disciplina) {
  global $cgmAssAdic;

//testa($oTurmaEtapa);
  /**
   * Soma com base na configuração do layout a largura do quadro
   */
;
  $iLarguraQuadro  = $oConfigRelatorio->iLarguraTotalDisciplinas;
  $iLarguraQuadro += $oConfigRelatorio->iColunaCodigo ;
  $iLarguraQuadro += $oTurmaEtapa->iColunaNome;
  $iLarguraQuadro += ($oConfigRelatorio->iColunaNumero*3);
  if (!$lUltimoPeriodo) {
    $iLarguraQuadro += $oConfigRelatorio->iColunaPareceres;
  }

  $iXInicial = $oPdf->GetX();
  $iYInicial = $oPdf->GetY();
  $oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $iLarguraQuadro, 25);


  /**
   * Assinatura
  */
  $oPdf->SetY($iYInicial+2);
//  $oPdf->Cell($iXInicial+100, 4, "","LR",0,"L");
//  $oPdf->Cell(100, 4, "","R",1,"L");

  $oPdf->Cell($iXInicial+10, 3, "Encerrado em: ___/___/_____","",1,"L");
  $oPdf->Cell($iXInicial+10, 3, "","",1,"L");
  $oPdf->Cell($iXInicial+10, 3, "","",1,"L");

  $doc  ="select
	  	  z01_numcgm,
		  z01_nome
		  from
		  regenciahorario
		  inner join regencia         on ed58_i_regencia   = ed59_i_codigo
		  inner join rechumano        on ed20_i_codigo     = ed58_i_rechumano
		  inner join rechumanopessoal on ed284_i_rechumano = ed20_i_codigo
		  inner join rhpessoal        on rh01_regist       = ed284_i_rhpessoal
		  inner join cgm              on z01_numcgm        = rh01_numcgm
		  inner join disciplina       on ed12_i_codigo     = ed59_i_disciplina
		  inner join caddisciplina    on ed232_i_codigo    = ed12_i_caddisciplina
		  where
          ed232_c_descr  = 'LINGUA PORTUGUESA'
		  and
		  ed58_ativo is true
		  and
		  ed59_i_codigo = ".$regencia;


// verificar porque o regente esta retornando vazio

  $sql1 = pg_query($doc);
  $reg = db_utils::fieldsMemory($sql1,0);

  if( !pg_num_rows($sql1) )
  {
	$doc  ="select
			  z01_numcgm,
			  z01_nome
			  from
			  regenciahorario
			  inner join regencia         on ed58_i_regencia   = ed59_i_codigo
			  inner join rechumano        on ed20_i_codigo     = ed58_i_rechumano
			  inner join rechumanopessoal on ed284_i_rechumano = ed20_i_codigo
			  inner join rhpessoal        on rh01_regist       = ed284_i_rhpessoal
			  inner join cgm              on z01_numcgm        = rh01_numcgm
			  inner join disciplina       on ed12_i_codigo     = ed59_i_disciplina
			  inner join caddisciplina    on ed232_i_codigo    = ed12_i_caddisciplina
			  where
			  ed232_c_descr  = 'CAMPOS DE EXPERIENCIA'
			  and
			  ed58_ativo is true
			  and
			  ed59_i_codigo = ".$regencia;


	// verificar porque o regente esta retornando vazio

	  $sql1 = pg_query($doc);
	  $reg = db_utils::fieldsMemory($sql1,0);
  }
    if( !pg_num_rows($sql1) )
    {
        $sql = "
	        select
			z01_nome
			from
			cgm
			where
			z01_numcgm = (
							select
							distinct (case when rh01_numcgm is null then  ed285_i_cgm else rh01_numcgm end) as cgm
							from rechumano
							left join rechumanopessoal  on  rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo
							left join rhpessoal  on  rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
							left join cgm as cgmrh on  cgmrh.z01_numcgm = rhpessoal.rh01_numcgm
							left join db_config  on  db_config.codigo = rhpessoal.rh01_instit
							left join rhpessoalmov on rhpessoalmov.rh02_anousu  = 0 and rhpessoalmov.rh02_mesusu  = 0  and rhpessoalmov.rh02_regist  = rhpessoal.rh01_regist and rhpessoalmov.rh02_instit  = {$instit}
							left join rhregime as regimerh on  regimerh.rh30_codreg = rhpessoalmov.rh02_codreg
							left join rhlota  on  rhlota.r70_codigo = rhpessoal.rh01_lotac
							left join rhpesdoc  on  rhpesdoc.rh16_regist = rhpessoal.rh01_regist
							left join rhestcivil  on  rhestcivil.rh08_estciv = rhpessoal.rh01_estciv
							left join rhraca  on  rhraca.rh18_raca = rhpessoal.rh01_raca
							left join rhfuncao  on  rhfuncao.rh37_funcao = rhpessoal.rh01_funcao and rh37_instit  = rh02_instit
							left join rhinstrucao  on  rhinstrucao.rh21_instru = rhpessoal.rh01_instru
							left join rhnacionalidade  on  rhnacionalidade.rh06_nacionalidade = rhpessoal.rh01_nacion
							left join rechumanocgm  on  rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo
							left join cgm as cgmcgm on  cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm
							left join cgmdoc on  cgmdoc.z02_i_cgm = cgmcgm.z01_numcgm
							left join rhregime as regimecgm on  regimecgm.rh30_codreg = rechumano.ed20_i_rhregime
							left join cgmfisico on cgmfisico.z04_numcgm = rhpessoal.rh01_numcgm and cgmfisico.z04_nomesocial <> ''
							inner join rechumanoescola  on  rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
							inner join escola  on  escola.ed18_i_codigo = rechumanoescola.ed75_i_escola
							left join relacaotrabalho  on  relacaotrabalho.ed23_i_rechumanoescola = rechumanoescola.ed75_i_codigo
							left join tipohoratrabalho on  tipohoratrabalho.ed128_codigo = ed23_tipohoratrabalho
							left join rechumanoativ  on  rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo
							left join atividaderh  on  atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade
							left join disciplina  on  disciplina.ed12_i_codigo = relacaotrabalho.ed23_i_disciplina
							left join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina
							left join ensino  on  ensino.ed10_i_codigo = disciplina.ed12_i_ensino
							inner join pais  on  pais.ed228_i_codigo = rechumano.ed20_i_pais
							left  join censouf as censoufident on  censoufident.ed260_i_codigo = rechumano.ed20_i_censoufident
							left  join censouf as censoufnat on  censoufnat.ed260_i_codigo = rechumano.ed20_i_censoufnat
							left  join censouf as censoufcert on  censoufcert.ed260_i_codigo = rechumano.ed20_i_censoufcert
							left  join censouf as censoufender on  censoufender.ed260_i_codigo = rechumano.ed20_i_censoufender
							left  join censomunic as censomunicnat on  censomunicnat.ed261_i_codigo = rechumano.ed20_i_censomunicnat
							left  join censomunic as censomunicender on  censomunicender.ed261_i_codigo = rechumano.ed20_i_censomunicender
							left  join censoorgemissrg  on  censoorgemissrg.ed132_i_codigo = rechumano.ed20_i_censoorgemiss
							left  join censocartorio  on  censocartorio.ed291_i_codigo = rechumano.ed20_i_censocartorio
							left  join regimetrabalho on  regimetrabalho.ed24_i_codigo = relacaotrabalho.ed23_i_regimetrabalho
							where
							ed20_i_codigo = (
											select
											distinct ed58_i_rechumano
											from
											regenciahorario
											where
											ed58_i_regencia = {$regencia}
											and ed58_ativo is true
											order by ed58_i_rechumano
											)
						  )
	    ";
		$sql2 = pg_query($sql);
		$reg = '';
		if( pg_num_rows($sql2) > 1 )
		{
			for($x=0;$x<pg_num_rows($sql2);$x++)
			{
				if( $x > 0)
				{
					$prof .= "-";
				}
				$reg  = db_utils::fieldsMemory($sql2,$x);
				$prof .=  $reg->z01_nome;
			}
		}else{

			$reg = db_utils::fieldsMemory($sql2,0);
			$prof =  $reg->z01_nome;
		}
    }


  $esc  ="select
	  	  z01_numcgm,
		  z01_nome
		  from
		  rechumano
		  inner join rechumanopessoal on ed284_i_rechumano = ed20_i_codigo
		  inner join rhpessoal        on rh01_regist       = ed284_i_rhpessoal
		  inner join cgm              on z01_numcgm        = rh01_numcgm
		  inner join escoladiretor    on ed254_i_rechumano = ed20_i_codigo
		  where
          ed254_i_escola = ".db_getsession("DB_coddepto");

  $sql2 = pg_query($esc);
  $dir  = db_utils::fieldsMemory($sql2,0);

  // Busca matrículas dos assinantes
  $matriculaReg = '';
  $matriculaDir = '';
  $matriculaAssAdic = '';

  /**
   * Autor: Uemerson Santana
   * Data: 27/11/2025
   * Demanda: 17982
   * Razão: Corrigida busca da matrícula do regente para filtrar pela escola vinculada através de rechumanoescola,
   *        garantindo que quando o profissional possui múltiplas matrículas, seja retornada a correta vinculada à escola atual.
   *        Removido substr que removia os 2 primeiros dígitos, exibindo agora a matrícula completa.
   */
  // Matrícula do Regente
  if (isset($reg->z01_numcgm)) {
    $iEscola = db_getsession("DB_coddepto");
    $sqlMatReg = "SELECT ed284_i_rhpessoal as matricula
                  FROM rechumanopessoal
                  INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                  INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                  INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                  WHERE rh01_numcgm = {$reg->z01_numcgm}
                    AND rechumanoescola.ed75_i_escola = {$iEscola}
                  LIMIT 1";
    $rsMatReg = pg_query($sqlMatReg);
    if (pg_num_rows($rsMatReg) > 0) {
      $oMatReg = db_utils::fieldsMemory($rsMatReg, 0);
      // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
      $matriculaReg = !empty($oMatReg->matricula) ? $oMatReg->matricula : '';
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
  // Matrícula do Diretor
  if (isset($dir->z01_numcgm)) {
    $iEscola = db_getsession("DB_coddepto");
    $sqlMatDir = "SELECT ed284_i_rhpessoal as matricula
                  FROM rechumanopessoal
                  INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                  INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                  INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                  WHERE rh01_numcgm = {$dir->z01_numcgm}
                    AND rechumanoescola.ed75_i_escola = {$iEscola}
                  LIMIT 1";
    $rsMatDir = pg_query($sqlMatDir);
    if (pg_num_rows($rsMatDir) > 0) {
      $oMatDir = db_utils::fieldsMemory($rsMatDir, 0);
      // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
      $matriculaDir = !empty($oMatDir->matricula) ? $oMatDir->matricula : '';
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
  // Matrícula da Assinatura Adicional (Supervisor)
  if (!empty($cgmAssAdic)) {
    $iEscola = db_getsession("DB_coddepto");
    $sqlMatAdic = "SELECT ed284_i_rhpessoal as matricula
                   FROM rechumanopessoal
                   INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                   INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                   INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                   WHERE rh01_numcgm = {$cgmAssAdic}
                     AND rechumanoescola.ed75_i_escola = {$iEscola}
                   LIMIT 1";
    $rsMatAdic = pg_query($sqlMatAdic);
    if (pg_num_rows($rsMatAdic) > 0) {
      $oMatAdic = db_utils::fieldsMemory($rsMatAdic, 0);
      // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
      $matriculaAssAdic = !empty($oMatAdic->matricula) ? $oMatAdic->matricula : '';
    }
  }

  $oPdf->SetFont("arial", 'b', 5);

//  $oPdf->Line($iXInicial+10, $iYInicial+12, $iXInicial+160, $iYInicial+12);
//  $oPdf->Line($iXInicial+65, $iYInicial+12, $iXInicial+215, $iYInicial+12);
//  $oPdf->Line($iXInicial+120, $iYInicial+12, $iXInicial+270, $iYInicial+12);
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4,"","B",0,"C");
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4,"","B",0,"C");
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4,"","B",1,"C");

  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4,$reg->z01_nome,"",0,"C");
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4,      $assAdic,"",0,"C");
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4,$dir->z01_nome,"",1,"C");

  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4, "             Regente               ","",0,"C");
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4, $Ativ,"",0,"C");
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 4, "         Diretor(a) Geral       	 ","",1,"C");

  // Exibir matrículas
  $textoMatriculaReg = !empty($matriculaReg) ? "Matrícula: " . $matriculaReg : "";
  $textoMatriculaAssAdic = !empty($matriculaAssAdic) ? "Matrícula: " . $matriculaAssAdic : "";
  $textoMatriculaDir = !empty($matriculaDir) ? "Matrícula: " . $matriculaDir : "";

  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 2, utf8_decode($textoMatriculaReg),"",0,"C");
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 2, utf8_decode($textoMatriculaAssAdic),"",0,"C");
  $oPdf->Cell(5, 4,"","",0,"C");
  $oPdf->Cell(50, 2, utf8_decode($textoMatriculaDir),"",1,"C");

}

/**
 * Realiza o cálculo, em tempo de execução, de variáveis de controle
 * @param FPDF     $oPdf
 * @param stdClass $oTurmaEtapa
 * @param stdClass $oConfigRelatorio
 * @param integer  $iPagina
 */
function calculaTamanhoDeCelulasDinamicas(FPDF $oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina) {

  $oTurmaEtapa->iTotalDisciplina   = count($oTurmaEtapa->aPaginas[$iPagina]);
  $oTurmaEtapa->iLarguraDisciplina = $oConfigRelatorio->iLarguraTotalDisciplinas / $oConfigRelatorio->iMaximoDisciplinaPagina;

  $oTurmaEtapa->iLarguraCelulaParecer = $oConfigRelatorio->iColunaPareceres / 3;

  if ($oTurmaEtapa->lUltimoPeriodo && !$oTurmaEtapa->lJaCalculado) {

    $oTurmaEtapa->lJaCalculado          = true;
    $oTurmaEtapa->iColunaNome     += $oConfigRelatorio->iColunaPareceres;
    $oTurmaEtapa->iLarguraCelulaParecer = 0;
  }

}

/**
 * Adiciona o cabeçalho
 * @param FPDF     $oPdf
 * @param stdClass $oTurmaEtapa
 * @param stdClass $oConfigRelatorio
 * @param integer  $iPagina
 */
function adicionaHeader(FPDF $oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina) {
  $sql = "
         select
		 ed52_c_descr as anos
		 from
		 calendario
		 where
		 ed52_i_codigo = ".$_GET["calendario"];
    $resultado = db_query($sql);
    $calDados  = db_utils::fieldsMemory($resultado,0);

    $oPdf->AddPage();
	$oPdf->SetFont("arial", 'b', 12);
	$oPdf->Cell($oPdf->w, $oConfigRelatorio->iAlturaLinha, "RESUMO ANUAL", 0, 1, "C");
	$oPdf->ln();
	$oPdf->SetFont("arial", 'b', 7);
	$iEixoY = $oPdf->GetY();
  if( substr($calDados->anos,0,13) == 'ANOS INICIAIS' )
  {
		$oPdf->Cell(62, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'LINGUA PORTUGUESA', 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, utf8_decode('HISTÓRIA'), 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'GEOGRAFIA', 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, utf8_decode('CIÊNCIAS'), 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'MATEMATICA', 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TOTAL DE FALTAS', 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 'LTR', 0, "C");
		$oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, '', 'LTR', 1, "C");  // muda de linha

		$iEixoY = $oPdf->GetY();
		$oPdf->Cell(62, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, 'FREQ.', 'LR', 0, "C");
		$oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'Resultado','LR', 1, "C");  // muda de linha
		$oPdf->SetFont("arial", 'b', 7);

		$oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, utf8_decode('Nº'), 1, 0, "C");
		$oPdf->Cell(57, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1, 0, "C");

		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);

		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);


		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);


		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);


		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);


		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, 'Total', 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);


		$oPdf->Cell(8,  $oConfigRelatorio->iAlturaLinha, '%', 'LRB', 0, "C");
		$oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'Final', 'LRB', 0, "C");  // muda de linha
  }

  if( substr($calDados->anos,0,12) == 'EJA INICIAIS' )
  {
		$oPdf->Cell(62, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'LINGUA PORTUGUESA', 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, utf8_decode('HISTÓRIA'), 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'GEOGRAFIA', 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, utf8_decode('CIÊNCIAS'), 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'MATEMATICA', 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'TOTAL DE FALTAS', 1, 0, "C");
		$oPdf->Cell(7, $oConfigRelatorio->iAlturaLinha, '', 'LTR', 0, "C");
		$oPdf->Cell(12, $oConfigRelatorio->iAlturaLinha, '', 'LTR', 1, "C");  // muda de linha


		$iEixoY = $oPdf->GetY();
		$oPdf->Cell(62, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'BIMESTRE', 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'BIMESTRE', 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'BIMESTRE', 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'BIMESTRE', 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'BIMESTRE', 1, 0, "C");
		$oPdf->Cell(33, $oConfigRelatorio->iAlturaLinha, 'BIMESTRE', 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(7, $oConfigRelatorio->iAlturaLinha, 'FREQ.', 'LR', 0, "C");
		$oPdf->Cell(12, $oConfigRelatorio->iAlturaLinha, 'Resultado','LR', 1, "C");  // muda de linha
		$oPdf->SetFont("arial", 'b', 7);

		$oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, utf8_decode('Nº'), 1, 0, "C");
		$oPdf->Cell(57, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1, 0, "C");

		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('4º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);

		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('4º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);


		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('4º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);

		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('4º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);

		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('4º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('MÉDIA'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);

		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, utf8_decode('4º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(6.6, $oConfigRelatorio->iAlturaLinha, 'Total', 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);


		$oPdf->Cell(7, $oConfigRelatorio->iAlturaLinha, '%', 'LRB', 0, "C");
		$oPdf->Cell(12, $oConfigRelatorio->iAlturaLinha, 'Final', 'LRB', 0, "C");  // muda de linha
  }

  if( substr($calDados->anos,0,12) == 'ED. INFANTIL' )
  {
		$oPdf->Cell(120, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TOTAL DE FALTAS', 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, '', 'LTR', 1, "C");
        /**
         * Autor: Uemerson Santana
         * Data: 18/04/2025
         * Demanda: 17301
         */
		// $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, '', 'LTR', 1, "C");  // muda de linha

		$iEixoY = $oPdf->GetY();
		$oPdf->Cell(120, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
		$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, 'FREQ.', 'LR', 1, "C");
        /**
         * Autor: Uemerson Santana
         * Data: 18/04/2025
         * Demanda: 17301
         */
		// $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'Resultado','LR', 1, "C");  // muda de linha
		$oPdf->SetFont("arial", 'b', 7);

		$oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, utf8_decode('Nº'), 1, 0, "C");
		$oPdf->Cell(115, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1, 0, "C");

		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 6);
		$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, 'Total', 1, 0, "C");
		$oPdf->SetFont("arial", 'b', 7);


		$oPdf->Cell(8,  $oConfigRelatorio->iAlturaLinha, '%', 'LRB', 0, "C");
        /**
         * Autor: Uemerson Santana
         * Data: 18/04/2025
         * Demanda: 17301
         */
		// $oPdf->Cell(17, $oConfigRelatorio->iAlturaLinha, 'Final', 'LRB', 0, "C");  // muda de linha
  }

    $oPdf->ln();
}

function adicionaHeaderC(FPDF $oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina) {
  $sql = "
         select
		 ed52_c_descr as anos
		 from
		 calendario
		 where
		 ed52_i_codigo = ".$_GET["calendario"];
    $resultado = db_query($sql);
    $calDados  = db_utils::fieldsMemory($resultado,0);

    $oPdf->AddPage();
	$oPdf->SetFont("arial", 'b', 12);
	$oPdf->Cell($oPdf->w, $oConfigRelatorio->iAlturaLinha, "RESUMO ANUAL", 0, 1, "C");
	$oPdf->ln();

	$oPdf->SetFont("arial", 'b', 7);

	$iEixoY = $oPdf->GetY();
	$oPdf->Cell(62, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
	$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'ARTE', 1, 0, "C");
	$oPdf->Cell(37, $oConfigRelatorio->iAlturaLinha, utf8_decode('TECNOLOGIA E INOVAÇÃO'), 1, 0, "C");
	$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, utf8_decode('LINGUA INGLESA'), 1, 0, "C");
	$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, utf8_decode('EDUCAÇÃO FÍSICA'), 1, 1, "C");

	$iEixoY = $oPdf->GetY();
	$oPdf->Cell(62, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
	$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
	$oPdf->Cell(37, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
	$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 0, "C");
	$oPdf->Cell(32, $oConfigRelatorio->iAlturaLinha, 'TRIMESTRE', 1, 1, "C");


	$oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, utf8_decode('Nº'), 1, 0, "C");
	$oPdf->Cell(57, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1, 0, "C");

	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
	$oPdf->SetFont("arial", 'b', 6);
    /*
        @autor Uemerson Santana
        @data 22/05/2025
        @demanda: 17356
    */
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, 'CF', 1, 0, "C"); // conceito final header
	$oPdf->SetFont("arial", 'b', 7);

	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
	$oPdf->SetFont("arial", 'b', 6);
    /*
        @autor Uemerson Santana
        @data 22/05/2025
        @demanda: 17356
    */
	$oPdf->Cell(13, $oConfigRelatorio->iAlturaLinha, 'CF', 1, 0, "C"); // conceito final header
	$oPdf->SetFont("arial", 'b', 7);


	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
	$oPdf->SetFont("arial", 'b', 6);
    /*
        @autor Uemerson Santana
        @data 22/05/2025
        @demanda: 17356
    */
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, 'CF', 1, 0, "C"); // conceito final header
	$oPdf->SetFont("arial", 'b', 7);


	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('1º'), 1, 0, "C");
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('2º'), 1, 0, "C");
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, utf8_decode('3º'), 1, 0, "C");
	$oPdf->SetFont("arial", 'b', 6);
    /*
        @autor Uemerson Santana
        @data 22/05/2025
        @demanda: 17356
    */
	$oPdf->Cell(8, $oConfigRelatorio->iAlturaLinha, 'CF', 1, 0, "C"); // conceito final header
	$oPdf->SetFont("arial", 'b', 7);
    $oPdf->ln();
}

/**
 * Imprime vazio os quadros das avaliações
 * @param FPDF     $oPdf
 * @param stdClass $oTurmaEtapa      -> Dados da turma
 * @param integer  $iTotalDisciplina -> Número de disciplina da página atual
 * @param stdClass $oConfigRelatorio -> Objeto de configuração
 * @param boolean  $lPrimeiraLinha   -> Se é a primeira linha do cabeçalho
 */
function imprimeQuadroAvaliacaoVazio (FPDF $oPdf, $oTurmaEtapa, $iTotalDisciplina, $oConfigRelatorio,
                                      $lPrimeiraLinha = true) {

  for ($i = $iTotalDisciplina; $i < $oConfigRelatorio->iMaximoDisciplinaPagina; $i++) {

    if ($lPrimeiraLinha) {

      $oPdf->Cell($oTurmaEtapa->iLarguraDisciplina, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
      imprimeLinhaSeparadora ($oPdf, $oConfigRelatorio);
    } else {

      $iLarguraAvaliacao = $oTurmaEtapa->iLarguraDisciplina - 5;

      if ($oTurmaEtapa->sFormaAvaliacao == 'NOTA' && $oConfigRelatorio->lCalculaMediaParcial) {

        $oPdf->Cell($iLarguraAvaliacao/2, $oConfigRelatorio->iAlturaLinha, '', 1);
        $oPdf->Cell($iLarguraAvaliacao/2, $oConfigRelatorio->iAlturaLinha, '', 1);
      } else {
        $oPdf->Cell($iLarguraAvaliacao, $oConfigRelatorio->iAlturaLinha, '', 1);
      }
      $oPdf->Cell(5,  $oConfigRelatorio->iAlturaLinha, '', 1);
    }
  }
}

/**
 * Imprime a linha vertical que separa as disciplinas
 * @param FPDF     $oPdf
 * @param stdClass $oConfigRelatorio
 */
function imprimeLinhaSeparadora (FPDF $oPdf, $oConfigRelatorio) {

  $oPdf->SetLineWidth(0.3);
  $oPdf->Line($oPdf->GetX(), $oPdf->GetY(), $oPdf->GetX(), $oConfigRelatorio->iAlturaLine);
  $oPdf->SetLineWidth(0);
}

/**
 * Imprime celulas para lançar o parecer
 * @param FPDF     $oPdf
 * @param integer  $iLarguraCelulaParecer
 * @param stdClass $oConfigRelatorio
 */
function imprimeCelulasParecer(FPDF $oPdf, $iLarguraCelulaParecer, $oConfigRelatorio) {

  $oPdf->Cell($iLarguraCelulaParecer, $oConfigRelatorio->iAlturaLinha, '', 1);
  $oPdf->Cell($iLarguraCelulaParecer, $oConfigRelatorio->iAlturaLinha, '', 1);
  $oPdf->Cell($iLarguraCelulaParecer, $oConfigRelatorio->iAlturaLinha, '', 1);
}

/**
 * Imprime linhas em branco para fechar o numero maximo de alunos
 * @param FPDF     $oPdf
 * @param stdClass $oConfigRelatorio
 * @param stdClass  $oTurmaEtapa
 */
function imprimeLinhaEmBranco(FPDF $oPdf, $oConfigRelatorio, $oTurmaEtapa) {
  $sql = "
         select
		 ed52_c_descr as anos
		 from
		 calendario
		 where
		 ed52_i_codigo = ".$_GET["calendario"];
  $resultado = db_query($sql);
  $calDados  = db_utils::fieldsMemory($resultado,0);


  $oPdf->AddPage();
  if( substr($calDados->anos,0,11) == 'ANOS FINAIS' )
  {
	  $oPdf->SetFont("arial", 'b', 7);
	  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
	  $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);

	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");



	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

  }

  if( substr($calDados->anos,0,10) == 'EJA FINAIS' )
  {
	  $oPdf->SetFont("arial", 'b', 7);
	  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
	  $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);

	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

  }

  if( substr($calDados->anos,0,12) == 'EJA INICIAIS' )
  {
	  $oPdf->SetFont("arial", 'b', 7);
	  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
	  $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);

	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

  }

  if( substr($calDados->anos,0,13) == 'ANOS INICIAIS' )
  {
	  $oPdf->SetFont("arial", 'b', 7);
	  $iEixoY = $oPdf->GetY();

	  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
	  $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);

	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 1, "C");
  }



  $iLarguraCelulaNome = $oConfigRelatorio->iColunaNumero + $oTurmaEtapa->iColunaNome;
  /*
  if ($oConfigRelatorio->lClassificacaoAlunoTurma) {
    $iLarguraCelulaNome = $oTurmaEtapa->iColunaNome;
    $oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '', 1);
  }
  $oPdf->Cell($iLarguraCelulaNome, $oConfigRelatorio->iAlturaLinha, '', 1);
  $oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '',   1);
  $oPdf->Cell($oConfigRelatorio->iColunaCodigo, $oConfigRelatorio->iAlturaLinha, '', 1);
  */

  /*if (!$oTurmaEtapa->lUltimoPeriodo) {
    imprimeCelulasParecer($oPdf, $oTurmaEtapa->iLarguraCelulaParecer, $oConfigRelatorio);
  }*/
  //imprimeQuadroAvaliacaoVazio ($oPdf, $oTurmaEtapa, 0, $oConfigRelatorio, false);
  //$oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '', 1, 1);

}


/**
 * Retorna uma abreviatura para a situacao do aluno
 * @param string $sSituacaoBusca
 * @return string $sSituacaoRetorno
 */
function buscaAbreviaturaSituacao($sSituacaoBusca) {

  $sSituacaoRetorno = '';

  foreach (getSituacoes() as $sAbrev => $sSituacao) {

    if ($sSituacaoBusca == $sSituacao) {

      $sSituacaoRetorno = $sAbrev;
      break;
    }
  }
  return $sSituacaoRetorno;
}

/**
 * Retorna a legenda das Situa??es tratadas no relatório
 * @return string
 */
function montaLegendaSituacoes() {

  $sLegenda = '';
  foreach (getSituacoes() as $sAbrev => $sSituacao) {

    $sLegenda .= "{$sAbrev}: $sSituacao;  ";
  }
  return $sLegenda;
}

/**
 * Array com as situações tratadas no relatório
 * @return multitype:string
 */
function getSituacoes() {

  /**
   * Array com as situação da matricula do aluno indexado pela abreviatura
   */
  $aSituacoes       = array();
  $aSituacoes['MT'] = 'MATRICULA TRANCADA';
  $aSituacoes['IN'] = 'MATRICULA INDEFERIDA';
  $aSituacoes['MI'] = 'MATRICULA INDEVIDA';
  $aSituacoes['TR'] = 'TRANSFERIDO REDE';
  $aSituacoes['TF'] = 'TRANSFERIDO FORA';
  $aSituacoes['TT'] = 'TROCA DE TURMA';
  $aSituacoes['TM'] = 'TROCA DE MODALIDADE';
  $aSituacoes['C']  = 'CANCELADO';
  $aSituacoes['E']  = 'EVADIDO';
  $aSituacoes['F']  = 'FALECIDO';

  return $aSituacoes;
}

/**
 * Abrevia o nome do aluno
 * @todo Mover para aluno
 * @param string $nome
 * @param integer $max
 * @param string $substr
 */
function abreviar($nome, $max, $substr=false) {

  if(strlen(trim($nome))>$max){

    $strinv = strrev(trim($nome));
    $ultnome = substr($strinv,0,strpos($strinv," "));
    $ultnome = strrev($ultnome);
    $nome = strrev($strinv);
    $prinome = substr($nome,0,strpos($nome," "));
    $nomes = strtok($nome, " ");
    $iniciais = "";

    while($nomes):
    if(($nomes == 'E') || ($nomes == 'DE') || ($nomes == 'DOS') ||
    ($nomes == 'DAS') || ($nomes == 'DA') || ($nomes == 'DO')){
      $iniciais .= " ".$nomes;
      $nomes = strtok(" ");
    }elseif (($nomes == $ultnome) || ($nomes == $prinome)){
      $nome = "";
      $nomes = strtok(" ");
    }else{
      $iniciais .= " ".$nomes[0].".";
      $nomes = strtok(" ");
    }
    endwhile;

    $nome =  $prinome;
    $nome .= $iniciais;
    $nome .= " ".$ultnome;
  }

  if (!$substr){
    return trim($nome);
  }else{
    return substr(trim($nome),0,20);
  }
}

function percfrequencia($calendar){
    $sql   = pg_query("select
	                   ed52_c_descr,
					   ed15_c_nome
					   from
					   calendario
					   inner join turma on ed57_i_calendario = ed52_i_codigo
					   inner join turno on ed15_i_codigo     = ed57_i_turno
					   where ed52_i_codigo = ".$calendar);
    $resultado = pg_fetch_all($sql);
	$nome  = $resultado[0]["ed52_c_descr"];
	$turno = substr($resultado[0]["ed15_c_nome"],0,5);
	$turno_completo = trim($resultado[0]["ed15_c_nome"]);
	if( substr($nome,0,12) == 'ED. INFANTIL' or substr($nome,0,17) == 'EDUCAÇÃO INFANTIL')
	{
		$auladadas = 200;
	}
	elseif( substr($nome,0,13) == 'ANOS INICIAIS' or substr($nome,0,20) == 'EN FUN ANOS INICIAIS' )
	{
		$auladadas = 200;

	}
	elseif( substr($nome,0,11) == 'ANOS FINAIS' or substr($nome,0,18) == 'EN FUN ANOS FINAIS')
	{
		// Autor: Uemerson Santana | Data: 19/01/2026 | Demanda: 18059
		// Razão: Para Anos Finais, verificar se o turno é INTEGRAL para aplicar 1522 horas/aula
		//        ao invés de 1000 horas/aula fixas. Turno INTEGRAL tem carga horária maior.
		if (strtoupper($turno_completo) == 'INTEGRAL') {
			$auladadas = 1522;
		} else {
			$auladadas = 1000;
		}
	}
	elseif( substr($nome,0,17) == 'EJA ANOS INICIAIS' or substr($nome,0,12) == 'EJA INICIAIS')
	{
		$auladadas = 170;
	}

	if( $turno <> 'NOITE' and (substr($nome,0,15) == 'EJA ANOS FINAIS' or substr($nome,0,10) == 'EJA FINAIS'))
	{
		$auladadas = 1200;
	}

	if( $turno == 'NOITE' and (substr($nome,0,15) == 'EJA ANOS FINAIS' or substr($nome,0,10) == 'EJA FINAIS'))
	{
		$auladadas = 1000;
	}
	return $auladadas;
}

function buscarSituacao($matricula,$disciplina,$turma)
{
  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];
// voltei a disciplina para o nome para fazer anos iniciais
//$disciplina = utf8_decode($disciplina);


	$sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
                ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  '".$disciplina."'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";

    $result_D = db_query($sqlD);
    $odados = db_utils::fieldsmemory($result_D,0);

  $sqla = " select
			ed95_i_codigo,
			ed47_v_nome
			from
			diario
			inner join aluno on ed47_i_codigo = ed95_i_aluno
			inner join matricula on ed60_i_aluno = ed47_i_codigo
			inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
			inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
			where
			ed60_i_codigo = {$matricula}
			and
			ed95_i_regencia = ed59_i_codigo
			and
			ed59_i_disciplina = {$odados->ed12_i_codigo}";
  if( $serie <> 29 and $serie <> 30 and $serie <> 31 )
  {
       $sqla .= "
			    and
			    ed95_i_serie = {$serie}";
  }
  $sqla .= "
  		    and
			ed59_i_turma = {$turma}
			order by ed95_i_codigo";


  $sql2 = pg_query($sqla);
  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];

  /**
   * @author Uemerson Santana
   * @date 24/11/2025
   * @demanda 17994
   * @razão: Modificada query para buscar também ed74_c_resultadofreq e ed74_c_resultadofinal,
   *         permitindo calcular o resultado final quando resultadofinal está vazio ou com espaço.
   *         Isso garante que alunos com necessidades especiais tenham seu resultado final exibido
   *         corretamente, mesmo quando ed74_c_resultadofinal está vazio ou com espaço.
   */
  $sqlN ="
		select
		diariofinal.ed74_c_resultadoaprov as aprov,
		diariofinal.ed74_c_resultadofreq as freq,
		diariofinal.ed74_c_resultadofinal as final
		from
		diariofinal
		inner join diario           on ed95_i_codigo          = ed74_i_diario
		where
		ed74_i_diario = {$codigo}
	    ";

  $sql3 = pg_query($sqlN);
  $resultado = pg_fetch_all($sql3);

  if (empty($resultado) || !isset($resultado[0])) {
      return '';
  }

  $dados = $resultado[0];
  $sResultadoAprovacao = isset($dados['aprov']) ? trim($dados['aprov']) : '';
  $sResultadoFrequencia = isset($dados['freq']) ? trim($dados['freq']) : '';
  $sResultadoFinal = isset($dados['final']) ? trim($dados['final']) : '';

  // Se resultadofinal está vazio ou com espaço, calcula baseado em resultadoaprov e resultadofreq
  // (mesma lógica usada em DiarioAvaliacaoDisciplina.model.php linha 829)
  if (empty($sResultadoFinal)) {
      if (!empty($sResultadoAprovacao) && !empty($sResultadoFrequencia)) {
          // Se ambos são 'A', resultado final é 'A'
          if ($sResultadoAprovacao === 'A' && $sResultadoFrequencia === 'A') {
              return 'A';
          } elseif ($sResultadoAprovacao === 'R' || $sResultadoFrequencia === 'R') {
              // Se algum é 'R', resultado final é 'R'
              return 'R';
          }
      }
  }

  // Se resultadofinal tem valor, retorna resultadoaprov (compatibilidade com código original)
  return $sResultadoAprovacao;

}

/**
 * Verifica se aluno tem necessidades especiais
 *
 * @author Uemerson Santana
 * @date 24/11/2025
 * @demanda 17994
 * @razão: Fun??o criada para verificar se aluno possui necessidades especiais,
 *         permitindo exibir resultado final corretamente no resumo anual dos Anos Iniciais
 *         quando o aluno tem necessidades especiais e parecer descritivo ativo.
 *
 * @param int $iCodigoAluno Código do aluno
 * @return bool
 */
function isAlunoComNecessidadesEspeciais($iCodigoAluno) {
    $sSql = "SELECT ed214_i_aluno FROM alunonecessidade WHERE ed214_i_aluno = {$iCodigoAluno}";
    $rsResult = db_query($sSql);
    return (pg_num_rows($rsResult) > 0);
}

function checarConceito($matricula,$turma)
{
	$port = buscarSituacao($matricula,'LINGUA PORTUGUESA',$turma);
	$hist = buscarSituacao($matricula,'HISTÓRIA',$turma);
	$geog = buscarSituacao($matricula,'GEOGRAFIA',$turma);
	$cien = buscarSituacao($matricula,'CIENCIAS',$turma);
	$mate = buscarSituacao($matricula,'MATEMÁTICA',$turma);
    if( $port == 'A' and $hist == 'A' and $geog == 'A' and $cien == 'A' and $mate == 'A')
	{
		$passou = true;
	}else{
		$passou = false;
	}
	return $passou;
}

function checarConceitoInfantil($matricula,$turma)
{
	$camp = buscarSituacao($matricula,'CAMPOS DE EXPERIENCIA',$turma);
    if( $camp == 'A')
	{
		$passou = true;
	}else{
		$passou = false;
	}
	return $passou;
}

$oPdf->Output();
