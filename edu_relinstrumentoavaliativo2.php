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

require_once ("fpdf151educacao/pdfwebseller.php");
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

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaPeriodos($calendario, $escola){
  $sql = pg_query("SELECT distinct ed09_i_codigo as codigo_periodo, ed09_c_descr as descricao_periodo from periodocalendario inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = periodocalendario.ed53_i_periodoavaliacao inner join calendario on calendario.ed52_i_codigo = periodocalendario.ed53_i_calendario inner join calendarioescola on calendarioescola.ed38_i_calendario = calendario.ed52_i_codigo inner join duracaocal on duracaocal.ed55_i_codigo = calendario.ed52_i_duracaocal where ed53_i_calendario in ({$calendario}) and ed38_i_escola in ({$escola}) order by ed09_i_codigo");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function voltaFaltas($matricula){
  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];

  $sql2 = pg_query("select ed95_i_codigo from diario inner join aluno on ed47_i_codigo = ed95_i_aluno inner join matricula on ed60_i_aluno = ed47_i_codigo inner join matriculaserie on ed60_i_codigo = ed221_i_matricula inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie where ed60_i_codigo = {$matricula} and ed95_i_regencia = ed59_i_codigo and ed95_i_serie = {$serie} and ed59_i_turma = {$turma} order by ed95_i_codigo");
  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];

  $sql3 = pg_query("select ed72_i_numfaltas from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo where ed72_i_diario = {$codigo} ORDER BY ed72_i_procavaliacao");
  $resultado = pg_fetch_all($sql3);
  return $resultado;
}

function buscaDisciplinasTurma($turma){
  $sql = pg_query("SELECT ed59_i_codigo, ed59_i_turma, ed59_i_disciplina, ed232_c_descr, ed12_i_codigo, ed59_i_serie FROM regencia INNER JOIN disciplina ON ed59_i_disciplina = ed12_i_codigo INNER JOIN caddisciplina ON ed12_i_caddisciplina = ed232_i_codigo WHERE ed59_i_turma = {$turma} ORDER BY ed59_i_disciplina");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

//ORDENAÇÃO ALTERADA PELA SEGUNDA VEZ
//RAFAEL MATOS 18/12/2024
function buscaAlunosDaTurma($turma){
  $sql = pg_query("SELECT ed47_v_nome, ed60_i_codigo, ed60_i_aluno, ed60_i_turma, ed60_i_numaluno, ed60_matricula FROM matricula INNER JOIN aluno ON ed60_i_aluno = ed47_i_codigo WHERE ed60_i_turma = {$turma} ORDER BY ed60_i_numaluno, ed47_v_nome");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaNotas($calendario, $turma, $periodo){

  $sql = pg_query("SELECT * FROM dclanotas WHERE calendario = {$calendario} AND turma = {$turma} AND periodo = {$periodo}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaNotasPorAlunoDisciplina($codigoaluno, $codigomatricula, $calendario, $turma, $periodo, $disciplina){
  //$sql = pg_query("SELECT ia1, ia1r, ia2, ia2r, ia3, ia3r, ia4, ia4r,ia5, ia5r, rf FROM dclanotas WHERE codigoaluno = {$codigoaluno} AND codigomatricula = {$codigomatricula} AND calendario = {$calendario} AND turma = {$turma} AND periodo = {$periodo} AND disciplina = {$disciplina}");
  $sql = pg_query("SELECT * FROM dclanotas WHERE codigoaluno = {$codigoaluno} AND codigomatricula = {$codigomatricula} AND calendario = {$calendario} AND turma = {$turma} AND periodo = {$periodo} AND disciplina = {$disciplina}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaDescricaoPeriodo($codperiodo){
  $sql = pg_query("SELECT ed09_c_descr FROM periodoavaliacao INNER JOIN procavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo WHERE ed41_i_codigo = {$codperiodo}");
  $resultado = pg_fetch_all($sql);
  return trim($resultado[0]["ed09_c_descr"]);
}

function buscaDescricaoTurma($codturma){
  $sql = pg_query("SELECT ed57_c_descr FROM turma WHERE ed57_i_codigo = {$codturma}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed57_c_descr"];
}

function buscaDescricaoCalendario($codcalendario){
  $sql = pg_query("SELECT ed52_c_descr FROM calendario WHERE ed52_i_codigo = {$codcalendario}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed52_c_descr"];
}

/**
 * Autor: Uemerson Santana
 * Data: 28/11/2025
 * Demanda: 17982
 * Razão: Função utilitária para buscar a matrícula vinculada à escola atual,
 *        garantindo que relatórios exibam a identificação correta quando o profissional
 *        possui múltiplas matrículas em escolas diferentes.
 */
function buscaMatriculaProfissional($cgm, $escola) {
  $cgm = (int) $cgm;
  $escola = (int) $escola;

  if ($cgm <= 0 || $escola <= 0) {
    return '';
  }

  $sqlMatricula = pg_query("
    SELECT ed284_i_rhpessoal as matricula
      FROM rechumanopessoal
      INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
      INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
      INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
     WHERE rh01_numcgm = {$cgm}
       AND rechumanoescola.ed75_i_escola = {$escola}
     LIMIT 1
  ");

  if ($sqlMatricula && pg_num_rows($sqlMatricula) > 0) {
    $dados = db_utils::fieldsMemory($sqlMatricula, 0);
    return !empty($dados->matricula) ? $dados->matricula : '';
  }

  return '';
}

function buscaDescricaoEtapa($turma){
  $sql = pg_query("SELECT ed59_i_serie, ed11_c_descr FROM turma INNER JOIN regencia ON ed59_i_turma = ed57_i_codigo INNER JOIN serie ON ed59_i_serie = ed11_i_codigo WHERE ed57_i_codigo = {$turma}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed11_c_descr"];
}

function buscaCurso($calendario){
  $sql = pg_query("SELECT ed52_c_descr FROM calendario WHERE ed52_i_codigo = {$calendario}");
  $resultado = pg_fetch_all($sql);

  if($resultado[0]["ed52_c_descr"] == "ED. INFANTIL 2024" || $resultado[0]["ed52_c_descr"] == "ED. INFANTIL 2024"){
    return "EDUCAÇÃO INFANTIL";
  }elseif(substr($resultado[0]["ed52_c_descr"], 0, 3) == "EJA"){
    return "EJA";
  }else{
    return "ENSINO FUNDAMENTAL";
  }
}

function buscaDadosDatas($codperiodo, $calendario){
  $sql = pg_query("SELECT ed09_i_codigo FROM periodoavaliacao INNER JOIN procavaliacao ON ed41_i_periodoavaliacao = ed09_i_codigo WHERE ed41_i_codigo = {$codperiodo}");
  $codp = pg_fetch_all($sql);
  $codp = $codp[0]["ed09_i_codigo"];

  $sql2 = pg_query("SELECT ed53_d_inicio, ed53_d_fim, ed53_i_diasletivos FROM periodocalendario WHERE ed53_i_periodoavaliacao = {$codp} AND ed53_i_calendario = {$calendario}");
  $resultado = pg_fetch_all($sql2);
  return $resultado[0];
}

function buscaRegente($codturma){
  $sql = pg_query("select ed235_i_rechumano from regenteconselho WHERE ed235_i_turma = {$codturma}");
  $cod = pg_fetch_all($sql);
  $cod = $cod[0]["ed235_i_rechumano"];

  $sql2 = pg_query("select distinct (case when rh01_numcgm is null then ed285_i_cgm else rh01_numcgm end) as cgm from rechumano left join rechumanopessoal on rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo left join rhpessoal on rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal left join cgm as cgmrh on cgmrh.z01_numcgm = rhpessoal.rh01_numcgm left join db_config on db_config.codigo = rhpessoal.rh01_instit left join rhpessoalmov on rhpessoalmov.rh02_anousu = 0 and rhpessoalmov.rh02_mesusu = 0 and rhpessoalmov.rh02_regist = rhpessoal.rh01_regist and rhpessoalmov.rh02_instit = 96 left join rhregime as regimerh on regimerh.rh30_codreg = rhpessoalmov.rh02_codreg left join rhlota on rhlota.r70_codigo = rhpessoal.rh01_lotac left join rhpesdoc on rhpesdoc.rh16_regist = rhpessoal.rh01_regist left join rhestcivil on rhestcivil.rh08_estciv = rhpessoal.rh01_estciv left join rhraca on rhraca.rh18_raca = rhpessoal.rh01_raca left join rhfuncao on rhfuncao.rh37_funcao = rhpessoal.rh01_funcao and rh37_instit = rh02_instit left join rhinstrucao on rhinstrucao.rh21_instru = rhpessoal.rh01_instru left join rhnacionalidade on rhnacionalidade.rh06_nacionalidade = rhpessoal.rh01_nacion left join rechumanocgm on rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo left join cgm as cgmcgm on cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm left join cgmdoc on cgmdoc.z02_i_cgm = cgmcgm.z01_numcgm left join rhregime as regimecgm on regimecgm.rh30_codreg = rechumano.ed20_i_rhregime left join cgmfisico on cgmfisico.z04_numcgm = rhpessoal.rh01_numcgm and cgmfisico.z04_nomesocial <> '' inner join rechumanoescola on rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo inner join escola on escola.ed18_i_codigo = rechumanoescola.ed75_i_escola left join relacaotrabalho on relacaotrabalho.ed23_i_rechumanoescola = rechumanoescola.ed75_i_codigo left join tipohoratrabalho on tipohoratrabalho.ed128_codigo = ed23_tipohoratrabalho left join rechumanoativ on rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo left join atividaderh on atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade left join disciplina on disciplina.ed12_i_codigo = relacaotrabalho.ed23_i_disciplina left join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina left join ensino on ensino.ed10_i_codigo = disciplina.ed12_i_ensino inner join pais on pais.ed228_i_codigo = rechumano.ed20_i_pais left join censouf as censoufident on censoufident.ed260_i_codigo = rechumano.ed20_i_censoufident left join censouf as censoufnat on censoufnat.ed260_i_codigo = rechumano.ed20_i_censoufnat left join censouf as censoufcert on censoufcert.ed260_i_codigo = rechumano.ed20_i_censoufcert left join censouf as censoufender on censoufender.ed260_i_codigo = rechumano.ed20_i_censoufender left join censomunic as censomunicnat on censomunicnat.ed261_i_codigo = rechumano.ed20_i_censomunicnat left join censomunic as censomunicender on censomunicender.ed261_i_codigo = rechumano.ed20_i_censomunicender left join censoorgemissrg on censoorgemissrg.ed132_i_codigo = rechumano.ed20_i_censoorgemiss left join censocartorio on censocartorio.ed291_i_codigo = rechumano.ed20_i_censocartorio left join regimetrabalho on regimetrabalho.ed24_i_codigo = relacaotrabalho.ed23_i_regimetrabalho where ed20_i_codigo = {$cod}");
  $cgm = pg_fetch_all($sql2);
  $cgm = $cgm[0]["cgm"];

  $sql3 = pg_query("SELECT z01_nome FROM cgm WHERE z01_numcgm = {$cgm}");
  $resultado = pg_fetch_all($sql3);
  return trim($resultado[0]["z01_nome"]);
}

function buscaRegenteD($disciplina, $regente, $escola){
  $doc  ="select
          distinct on (z01_nome)
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
          ed232_c_descr  = '".$disciplina."'
		  and
		  ed58_ativo is true
		  and
		  ed59_i_codigo = ".$regente;

  $sql1 = pg_query($doc);
  $aRegentes = array();

  if ($sql1 && pg_num_rows($sql1) > 0) {
    for ($x = 0; $x < pg_num_rows($sql1); $x++) {
		    $reg  = db_utils::fieldsMemory($sql1,$x);
      $nome = trim($reg->z01_nome);
      $cgmReg = $reg->z01_numcgm;
      $matricula = buscaMatriculaProfissional($cgmReg, $escola);
      $aRegentes[] = array(
        'nome' => $nome,
        'matricula' => $matricula
      );
    }
  } else {
    $instit = db_getsession('DB_instit');
        $sql = "
	        select
			z01_nome,
            z01_numcgm
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
											ed58_i_regencia = {$regente}
											and ed58_ativo is true
											order by ed58_i_rechumano
											)
						  )
	    ";
		$sql2 = pg_query($sql);
		if ($sql2 && pg_num_rows($sql2) > 0) {
			for($x=0;$x<pg_num_rows($sql2);$x++)
			{
				$reg  = db_utils::fieldsMemory($sql2,$x);
				$nome = trim($reg->z01_nome);
        $matricula = buscaMatriculaProfissional($reg->z01_numcgm, $escola);
				$aRegentes[] = array(
          'nome' => $nome,
          'matricula' => $matricula
        );
			}
		}
    }
  return $aRegentes;
}

function buscaRegenteD1($turma,$disciplina){
   $sqla = "
          select
		  ed59_i_codigo
		  from regencia
		  inner join disciplina    on ed59_i_disciplina    = ed12_i_codigo
		  inner join caddisciplina on ed12_i_caddisciplina = ed232_i_codigo
		  where
		  ed59_i_turma = {$turma}
		  and
		  ed232_c_descr = '".$disciplina."'
          ";


  $sql = pg_query($sqla);
  $resultado = pg_fetch_all($sql);
  $regente   = $resultado[0]['ed59_i_codigo'];
  return $regente;
}



function buscaNomeDiretor($escola){
  $iescola = (int) $escola;
  $sql = pg_query("select 'DIRETOR' as funcao,
                          case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as nome,
                          case when ed20_i_tiposervidor = 1 then cgmrh.z01_numcgm else cgmcgm.z01_numcgm end as cgm,
                          ed83_c_descr||' n°: '||ed05_c_numero::varchar as descricao,'D' as tipo
                     FROM escoladiretor
               INNER JOIN turno ON turno.ed15_i_codigo = escoladiretor.ed254_i_turno
                LEFT JOIN atolegal ON atolegal.ed05_i_codigo = escoladiretor.ed254_i_atolegal
                LEFT JOIN tipoato ON tipoato.ed83_i_codigo = atolegal.ed05_i_tipoato
               INNER JOIN rechumano ON rechumano.ed20_i_codigo = escoladiretor.ed254_i_rechumano
                LEFT JOIN rechumanopessoal ON rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo
                LEFT JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                LEFT JOIN cgm AS cgmrh ON cgmrh.z01_numcgm = rhpessoal.rh01_numcgm
                LEFT JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                LEFT JOIN rechumanoativ ON rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo
                LEFT JOIN atividaderh ON atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade
                LEFT JOIN rhpessoalmov ON rh02_anousu = 2018 AND rh02_mesusu = 02 AND rh02_regist = rh01_regist AND rh02_instit = 96
                LEFT JOIN rhfuncao ON rhfuncao.rh37_funcao = rhpessoal.rh01_funcao AND rh37_instit = rh02_instit
                LEFT JOIN rechumanocgm ON rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo
                LEFT JOIN cgm AS cgmcgm ON cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm
                    where ed254_i_escola = {$iescola}
                      AND ed254_c_tipo = 'A'
                      AND ed01_i_funcaoadmin = 2");
    if (!$sql || pg_num_rows($sql) == 0) {
      return array("nome" => "", "matricula" => "");
    }
    $resultado = pg_fetch_all($sql);
    $dados = $resultado[0];
    $matricula = buscaMatriculaProfissional($dados["cgm"], $iescola);
    return array(
      "nome" => $dados["nome"],
      "matricula" => $matricula
    );
}

function buscaAssAdicional($codigo, $escola){
  $iescola = (int) $escola;
  $sql = pg_query("select ed20_i_codigo, z01_nome, z01_numcgm, dl_identificacao, dl_cpf, dl_atividade, dl_regime, ed20_i_tiposervidor, ed75_i_codigo as db_rechumano from (select distinct rechumano.ed20_i_codigo, case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as z01_nome, case when ed20_i_tiposervidor = 1 then cgmrh.z01_numcgm else cgmcgm.z01_numcgm end as z01_numcgm, case when ed20_i_tiposervidor = 1 then rechumanopessoal.ed284_i_rhpessoal else rechumanocgm.ed285_i_cgm end as dl_identificacao, case when ed20_i_tiposervidor = 1 then cgmrh.z01_cgccpf else cgmcgm.z01_cgccpf end as dl_cpf, (select ativrh.ed01_c_descr from rechumanoativ as ativ inner join atividaderh as ativrh on ativrh.ed01_i_codigo = ativ.ed22_i_atividade where ativ.ed22_i_rechumanoescola = ed75_i_codigo order by ed01_c_regencia desc limit 1) as dl_atividade, case when ed20_i_tiposervidor = 1 then regimerh.rh30_descr else regimecgm.rh30_descr end as dl_regime, case when ed20_i_tiposervidor = 1 then 'SIM' else 'NÃO' end as ed20_i_tiposervidor, rechumanoescola.ed75_i_codigo from rechumano left join rechumanopessoal on rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo left join rhpessoal on rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal left join cgm as cgmrh on cgmrh.z01_numcgm = rhpessoal.rh01_numcgm left join db_config on db_config.codigo = rhpessoal.rh01_instit left join rhpessoalmov on rhpessoalmov.rh02_anousu = 0 and rhpessoalmov.rh02_mesusu = 0 and rhpessoalmov.rh02_regist = rhpessoal.rh01_regist and rhpessoalmov.rh02_instit = 96 left join rhregime as regimerh on regimerh.rh30_codreg = rhpessoalmov.rh02_codreg left join rhlota on rhlota.r70_codigo = rhpessoal.rh01_lotac left join rhpesdoc on rhpesdoc.rh16_regist = rhpessoal.rh01_regist left join rhestcivil on rhestcivil.rh08_estciv = rhpessoal.rh01_estciv left join rhraca on rhraca.rh18_raca = rhpessoal.rh01_raca left join rhfuncao on rhfuncao.rh37_funcao = rhpessoal.rh01_funcao and rh37_instit = rh02_instit left join rhinstrucao on rhinstrucao.rh21_instru = rhpessoal.rh01_instru left join rhnacionalidade on rhnacionalidade.rh06_nacionalidade = rhpessoal.rh01_nacion left join rechumanocgm on rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo left join cgm as cgmcgm on cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm left join cgmdoc on cgmdoc.z02_i_cgm = cgmcgm.z01_numcgm left join rhregime as regimecgm on regimecgm.rh30_codreg = rechumano.ed20_i_rhregime left join cgmfisico on cgmfisico.z04_numcgm = rhpessoal.rh01_numcgm and cgmfisico.z04_nomesocial <> '' inner join rechumanoescola on rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo inner join escola on escola.ed18_i_codigo = rechumanoescola.ed75_i_escola left join relacaotrabalho on relacaotrabalho.ed23_i_rechumanoescola = rechumanoescola.ed75_i_codigo left join tipohoratrabalho on tipohoratrabalho.ed128_codigo = ed23_tipohoratrabalho left join rechumanoativ on rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo left join atividaderh on atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade left join disciplina on disciplina.ed12_i_codigo = relacaotrabalho.ed23_i_disciplina left join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina left join ensino on ensino.ed10_i_codigo = disciplina.ed12_i_ensino inner join pais on pais.ed228_i_codigo = rechumano.ed20_i_pais left join censouf as censoufident on censoufident.ed260_i_codigo = rechumano.ed20_i_censoufident left join censouf as censoufnat on censoufnat.ed260_i_codigo = rechumano.ed20_i_censoufnat left join censouf as censoufcert on censoufcert.ed260_i_codigo = rechumano.ed20_i_censoufcert left join censouf as censoufender on censoufender.ed260_i_codigo = rechumano.ed20_i_censoufender left join censomunic as censomunicnat on censomunicnat.ed261_i_codigo = rechumano.ed20_i_censomunicnat left join censomunic as censomunicender on censomunicender.ed261_i_codigo = rechumano.ed20_i_censomunicender left join censoorgemissrg on censoorgemissrg.ed132_i_codigo = rechumano.ed20_i_censoorgemiss left join censocartorio on censocartorio.ed291_i_codigo = rechumano.ed20_i_censocartorio left join regimetrabalho on regimetrabalho.ed24_i_codigo = relacaotrabalho.ed23_i_regimetrabalho where ed75_i_escola = {$iescola} order by z01_nome) as x where ed20_i_codigo not in (select ed321_rechumano from docenteausencia where ed321_inicio is not null and ed321_escola = {$iescola} and ed321_final is null) AND ed20_i_codigo = {$codigo}") ;
  $resultado = pg_fetch_all($sql);
  if (!$resultado) {
    return array(
      "z01_nome" => "",
      "dl_atividade" => "",
      "z01_numcgm" => 0,
      "matricula" => ""
    );
  }
  $dados = $resultado[0];
  $dados["matricula"] = buscaMatriculaProfissional($dados["z01_numcgm"], $escola);
  return $dados;
}

 function buscaobs($calendario, $turma, $periodo){
  $sql = pg_query("SELECT obs FROM regocorr WHERE calendario = {$calendario} AND turma = {$turma} AND periodo = {$periodo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["obs"];
}

function buscaTitulosIA($calendario, $turma, $etapa, $periodo, $disciplina){
    $sql = pg_query("SELECT id, nomeia1, nomeia2, nomeia3, nomeia4, nomeia5 FROM titulosia WHERE calendario = {$calendario} AND turma = {$turma} AND etapa = {$etapa} AND periodo = {$periodo} AND disciplina = {$disciplina}");
    $resultado = pg_fetch_all($sql);


    if($resultado){
        $titulos["id"] = $resultado[0]["id"];
        $titulos["ia1"] = ($resultado[0]["nomeia1"]) ? trim($resultado[0]["nomeia1"]) : "IA 1";
        $titulos["ia2"] = ($resultado[0]["nomeia2"]) ? trim($resultado[0]["nomeia2"]) : "IA 2";
        $titulos["ia3"] = ($resultado[0]["nomeia3"]) ? trim($resultado[0]["nomeia3"]) : "IA 3";
        $titulos["ia4"] = ($resultado[0]["nomeia4"]) ? trim($resultado[0]["nomeia4"]) : "IA 4";
        $titulos["ia5"] = ($resultado[0]["nomeia5"]) ? trim($resultado[0]["nomeia5"]) : "IA 5";
    }else{
        $titulos["id"] = "";
        $titulos["ia1"] = "IA 1";
        $titulos["ia2"] = "IA 2";
        $titulos["ia3"] = "IA 3";
        $titulos["ia4"] = "IA 4";
        $titulos["ia5"] = "IA 5";
    }

    return $titulos;
}

function buscaobsIA($calendario, $turma, $etapa, $periodo, $disciplina){
    $sql = pg_query("SELECT id, observacao FROM titulosiaobs WHERE calendario = {$calendario} AND turma = {$turma} AND etapa = {$etapa} AND periodo = {$periodo} AND disciplina = {$disciplina}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

function cabecalho($dperiodo,$didf)
{
  $oPdf->SetFont("arial", 'b', 8);
  $oPdf->Cell(280, 4, "INSTRUMENTOS AVALIATIVOS - {$dperiodo} - ".$didf." - DISCIPLINA: {$disciplina['ed232_c_descr']}", 0, 1, "C");
  $oPdf->ln();

  $oPdf->SetFont("arial", 'b', 7);

  $iEixoY = $oPdf->GetY();
  $oPdf->Cell(65, 4, '', 0, 0, "C");
  $oPdf->Cell(200, 4, 'Instrumentos Avaliativos - Pontuação', 1, 0, "C");
  $oPdf->ln();

  $oPdf->Cell(5, 4, 'Nº', 1, 0, 'C');
  $oPdf->Cell(60, 4, 'Nome do Aluno', 1);
  $oPdf->Cell(20, 4, 'IA1 - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA1 R - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA2 - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA2 R - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA3 - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA3 R - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA4 - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA4 R - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA5 - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'IA5 R - ', 1, 0, "C");
  $oPdf->Cell(20, 4, 'NOTA PERÍODO', 1, 1, "C");
}

$zcalendario = (int) $_GET["calendario"];
$zturma      = (int) $_GET["turma"];
$zperiodo    = (int) $_GET["periodo"];
$zescola     = (int) $_GET["escola"];
$zadicional  = $_GET["adicional"];



$dadosturmas    = buscaDisciplinasTurma($zturma);


$dadosalunos    = buscaAlunosDaTurma($zturma);
$dadosnotas     = buscaNotas($zcalendario, $zturma, $zperiodo);
$dadosperiodos  = buscaDadosDatas($zperiodo, $zcalendario);
$dinicio        = implode("/", array_reverse(explode("-", $dadosperiodos["ed53_d_inicio"])));
$dfim           = implode("/", array_reverse(explode("-", $dadosperiodos["ed53_d_fim"])));
$didf           = $dinicio . " a " . $dfim;
$aulasdadas     = $dadosperiodos["ed53_i_diasletivos"];
//$regente        = buscaRegente($zturma);
$matriculaDiretor = '';
$dadosDiretor = buscaNomeDiretor($zescola);
$ndiretor = $dadosDiretor["nome"];
$matriculaDiretor = $dadosDiretor["matricula"];
$nomeadi = '';
$cargoadi = '';
$matriculaAdicional = '';
if($zadicional != "nao"){
  $dadosadicionais = buscaAssAdicional($zadicional, $zescola);
  $nomeadi = trim($dadosadicionais["z01_nome"]);
  $cargoadi = trim($dadosadicionais["dl_atividade"]);
  $matriculaAdicional = $dadosadicionais["matricula"];
}
//$textoobs = buscaobs($zcalendario, $zturma, $zperiodo);
$conferecalendario = trim(buscaDescricaoCalendario($zcalendario));


$oPdf = new PDF("L");
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetAutoPageBreak(true);
$oPdf->SetFillColor(215);
$oPdf->SetMargins(8, 10);
$oPdf->SetLineWidth(0);
$oPdf->imprime_rodape = false;


$head1 = "Instrumento Avaliativo";
$head2 = "Curso: " . buscaCurso($zcalendario);
$head3 = "Calendário: " . buscaDescricaoCalendario($zcalendario);
$head4 = "Etapa: " . buscaDescricaoEtapa($zturma);
$head5 = "Turma: " . buscaDescricaoTurma($zturma);
$head6 = "Período: " . buscaDescricaoPeriodo($zperiodo);
$head7 = "Aulas Dadas: " . $aulasdadas;
//$head8 = "Regente: " . $regente;

/*
Curso: Ensino Fundamental
Turma: EF 301 - SELECT ed57_c_descr FROM turma WHERE ed57_i_codigo = 2451;
Calendário: EN FUN ANOS INICIAIS - SELECT ed52_c_descr FROM calendario WHERE ed52_i_codigo = 273;
Etapa: 3º ANO
Aulas Dadas: 52
Regente: Nome do Professor Responsável
*/
//$oPdf->AddPage();

$tamanho = count($dadosturmas);
$contador = 0;
$alunoPorPagina = 0;
$discip = '';
$head8 = "Regente: ";
$head9 = "";
$matriculaRegente  = '';
$matriculaRegente2 = '';
$regentePrincipalNome = '';
$segundoRegenteNome   = '';
foreach ($dadosturmas as $disciplina) {
  $dperiodo = buscaDescricaoPeriodo($zperiodo);
  //$head1 = "Instrumento Avaliativo" ." - ". $disciplina['ed232_c_descr'];
  if( $discip <> $disciplina['ed232_c_descr'])   // verifica se mudou de disciplina
  {
	  $ed59_i_codigo = buscaRegenteD1($zturma,$disciplina['ed232_c_descr']);  // busca o codigo do regente ou regentes
	  $listaRegentes = buscaRegenteD($disciplina['ed232_c_descr'], $ed59_i_codigo, $zescola); // busca regente(s) com matrícula
    $regente = '';
    $head8 = "Regente: ";
    $head9 = "";
    $matriculaRegente  = '';
    $matriculaRegente2 = '';
    $regentePrincipalNome = '';
    $segundoRegenteNome   = '';
    if (!empty($listaRegentes)) {
      $nomesRegentes = array();
      foreach ($listaRegentes as $aRegenteDados) {
        if (!empty($aRegenteDados['nome'])) {
          $nomesRegentes[] = $aRegenteDados['nome'];
        }
      }
      $regente = implode("-", $nomesRegentes);
      $regentePrincipalNome = $listaRegentes[0]['nome'];
      $matriculaRegente     = $listaRegentes[0]['matricula'];

      if (isset($listaRegentes[1]) && !empty($listaRegentes[1]['nome'])) {
        $segundoRegenteNome   = $listaRegentes[1]['nome'];
        $matriculaRegente2    = $listaRegentes[1]['matricula'];
        $head9 = "                " . $segundoRegenteNome;
      }

      $head8 = "Regente: " . $regentePrincipalNome;
    }
	  $oPdf->AddPage();  // muda de pagina
  }
  $discip =  $disciplina['ed232_c_descr'];

  $oPdf->SetFont("arial", 'b', 8);
  $oPdf->Cell(280, 4, "INSTRUMENTOS AVALIATIVOS - {$dperiodo} - ".$didf." - DISCIPLINA: {$disciplina['ed232_c_descr']}", 0, 1, "C");
  $oPdf->ln();
  $nomesia = buscaTitulosIA($zcalendario, $zturma, $disciplina["ed59_i_serie"], $zperiodo, $disciplina["ed12_i_codigo"]);
  $obsia = buscaobsIA($zcalendario, $zturma, $disciplina["ed59_i_serie"], $zperiodo, $disciplina["ed12_i_codigo"]);

  $textoobs = $obsia["observacao"];

  $oPdf->SetFont("arial", 'b', 7);

  $iEixoY = $oPdf->GetY();
  $oPdf->Cell(65, 4, '', 0, 0, "C");
  $oPdf->Cell(200, 4, 'Instrumentos Avaliativos - Pontuação', 1, 0, "C");
  $oPdf->ln();

  if(trim($conferecalendario == "ANOS FINAIS 2025")){
  	$oPdf->Cell(5, 4, 'Nº', 1, 0, 'C');
	  $oPdf->Cell(60, 4, 'Nome do Aluno', 1);
	  $oPdf->Cell(16, 4, $nomesia["ia1"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia1"]." R", 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia2"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia2"]." R", 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia3"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia3"]." R", 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia4"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia4"]." R", 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia5"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia5"]." R", 1, 0, "C");
	  $oPdf->SetFont("arial", 'b', 6);
	  $oPdf->Cell(20, 4, 'MÉDIA PARCIAL', 1, 0, "C");
	  $oPdf->Cell(20, 4, 'REC. DO BIMESTRE', 1, 0, "C");
	  $oPdf->Cell(20, 4, 'MÉDIA FINAL', 1, 1, "C");
  }else{
  	$oPdf->Cell(5, 4, 'Nº', 1, 0, 'C');
	  $oPdf->Cell(60, 4, 'Nome do Aluno', 1);
	  $oPdf->Cell(20, 4, $nomesia["ia1"], 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia1"]." R", 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia2"], 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia2"]." R", 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia3"], 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia3"]." R", 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia4"], 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia4"]." R", 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia5"], 1, 0, "C");
	  $oPdf->Cell(20, 4, $nomesia["ia5"]." R", 1, 0, "C");
	  $oPdf->Cell(20, 4, 'NOTA PERÍODO', 1, 1, "C");
  }
$oPdf->SetFont("arial", 'b', 7);


  $alunoPorPagina = 0;
  foreach ($dadosalunos as $aluno) {
    $notasaluno = buscaNotasPorAlunoDisciplina($aluno['ed60_i_aluno'], $aluno['ed60_i_codigo'], $zcalendario, $zturma, $zperiodo, $disciplina['ed59_i_disciplina']);
    //testa($notasaluno);

    $ia1  = ($notasaluno["ia1"])  ? $notasaluno["ia1"] : "";
    $ia1r = ($notasaluno["ia1r"]) ? $notasaluno["ia1r"] : "";
    $ia2  = ($notasaluno["ia2"])  ? $notasaluno["ia2"] : "";
    $ia2r = ($notasaluno["ia2r"]) ? $notasaluno["ia2r"] : "";
    $ia3  = ($notasaluno["ia3"])  ? $notasaluno["ia3"] : "";
    $ia3r = ($notasaluno["ia3r"]) ? $notasaluno["ia3r"] : "";
    $ia4  = ($notasaluno["ia4"])  ? $notasaluno["ia4"] : "";
    $ia4r = ($notasaluno["ia4r"]) ? $notasaluno["ia4r"] : "";
    $ia5  = ($notasaluno["ia5"])  ? $notasaluno["ia5"] : "";
    $ia5r = ($notasaluno["ia5r"]) ? $notasaluno["ia5r"] : "";

    $recbimestre = ($notasaluno["recbimestre"]) ? $notasaluno["recbimestre"] : "";
    $mediafinal = ($notasaluno["mediafinal"]) ? $notasaluno["mediafinal"] : "";
    $rf   = ($notasaluno["rf"])   ? $notasaluno["rf"] : "";
    //var_dump($ia1); echo "<br>";

    /*Divaldo 11/12/2024
	  formatei os dado abaixo com uma casa decimal não foi necessário usar o procedimento de não arredondamento
	  porque os numeros ja saiam no máximo com uma casa decimal, apenas os numeros inteiros não saiam com casa
	  decimal

	  A pedido de Suellem, as notas de recuperação deveriam ser em branco e não zero, porque dava a impressão que
	  o aluno fez a recuperação e tirou zero, acontece que no arquivo dclanotas os campos que não tem dados digitados
	  aparecem zero, considerei então que notas de recuperação zero, seria porque o aluno não fez recuperação
	*/

    $ia1  = number_format($ia1,1,',','.');
	if( $notasaluno["ia1r"] == '')
	{
	    $ia1r = '';
    }else{
		$ia1r = number_format($ia1r,1,',','.');
	}
    $ia2  = number_format($ia2,1,',','.');
	if($notasaluno["ia2r"] == '')
	{
	    $ia2r = '';
    }else{
		$ia2r = number_format($ia2r,1,',','.');
	}
    $ia3  = number_format($ia3,1,',','.');
	if($notasaluno["ia3r"] == '')
	{
	    $ia3r = '';
    }else{
		$ia3r = number_format($ia3r,1,',','.');
	}
    $ia4  = number_format($ia4,1,',','.');
	if($notasaluno["ia4r"] == '')
	{
	    $ia4r = '';
    }else{
		$ia4r = number_format($ia4r,1,',','.');
	}
    $ia5  = number_format($ia5,1,',','.');
	if($notasaluno["ia5r"] == '')
	{
	    $ia5r = '';
    }else{
		$ia5r = number_format($ia5r,1,',','.');
	}


		$recbimestre   = number_format($recbimestre,1,',','.');
		$mediafinal   = number_format($mediafinal,1,',','.');
    $rf   = number_format($rf,1,',','.');



    $oPdf->Cell(5, 4, $aluno['ed60_i_numaluno'], 1, 0, 'C');
	if( strlen(trim($aluno['ed47_v_nome']))>37)
	{
		$oPdf->SetFont("arial", 'b', 5.5);
	}

	if(trim($conferecalendario == "ANOS FINAIS 2025")){
		$oPdf->Cell(60, 4, $aluno['ed47_v_nome'], 1);
		$oPdf->SetFont("arial", '', 7);
    $oPdf->Cell(16, 4, $ia1, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia1r, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia2, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia2r, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia3, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia3r, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia4, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia4r, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia5, 1, 0, "C");
    $oPdf->Cell(16, 4, $ia5r, 1, 0, "C");
    $oPdf->SetFont("arial", '', 6);
    $oPdf->Cell(20, 4, $rf, 1, 0, "C");
    $oPdf->Cell(20, 4, $recbimestre, 1, 0, "C");
    $oPdf->Cell(20, 4, $mediafinal, 1, 1, "C");
	}else{
		$oPdf->Cell(60, 4, $aluno['ed47_v_nome'], 1);
		$oPdf->SetFont("arial", '', 7);
    $oPdf->Cell(20, 4, $ia1, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia1r, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia2, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia2r, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia3, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia3r, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia4, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia4r, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia5, 1, 0, "C");
    $oPdf->Cell(20, 4, $ia5r, 1, 0, "C");
    $oPdf->Cell(20, 4, $rf, 1, 1, "C");
	}
	$oPdf->SetFont("arial", '', 7);

	$alunoPorPagina++;
	/*
	Divaldo 11/12/2024
	houve um caso de uma turma que tinha muitos alunos e estava gerando uma impressão estranha no final da primeira página
	então coloquei um contador para limitar a quantidade de alunos por página e no caso de acima de 30 alunos impressos
	mudasse para a proxima pagina
	*/
	if( $alunoPorPagina == 30 )
	{
		$oPdf->AddPage();
		$oPdf->SetFont("arial", 'b', 8);
		$oPdf->Cell(280, 4, "INSTRUMENTOS AVALIATIVOS - {$dperiodo} - ".$didf." - DISCIPLINA: {$disciplina['ed232_c_descr']}", 0, 1, "C");
		$oPdf->ln();

		$oPdf->SetFont("arial", 'b', 7);

		$iEixoY = $oPdf->GetY();
		$oPdf->Cell(65, 4, '', 0, 0, "C");
		$oPdf->Cell(200, 4, 'Instrumentos Avaliativos - Pontuação', 1, 0, "C");
		$oPdf->ln();

		if(trim($conferecalendario == "ANOS FINAIS 2025")){
  	$oPdf->Cell(5, 4, 'Nº', 1, 0, 'C');
	  $oPdf->Cell(60, 4, 'Nome do Aluno', 1);
	  $oPdf->Cell(16, 4, $nomesia["ia1"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia1"]." R", 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia2"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia2"]." R", 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia3"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia3"]." R", 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia4"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia4"]." R", 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia5"], 1, 0, "C");
	  $oPdf->Cell(16, 4, $nomesia["ia5"]." R", 1, 0, "C");
	  $oPdf->SetFont("arial", 'b', 6);
	  $oPdf->Cell(20, 4, 'MÉDIA PARCIAL', 1, 0, "C");
	  $oPdf->Cell(20, 4, 'REC. DO BIMESTRE', 1, 0, "C");
	  $oPdf->Cell(20, 4, 'MÉDIA FINAL', 1, 1, "C");
  }else{
  	$oPdf->Cell(5, 4, 'Nº', 1, 0, 'C');
		$oPdf->Cell(60, 4, 'Nome do Aluno', 1);

		$oPdf->Cell(20, 4, $nomesia["ia1"], 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia1"]." R", 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia2"], 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia2"]." R", 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia3"], 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia3"]." R", 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia4"], 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia4"]." R", 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia5"], 1, 0, "C");
  	$oPdf->Cell(20, 4, $nomesia["ia5"]." R", 1, 0, "C");
		$oPdf->Cell(20, 4, 'NOTA PERÍODO', 1, 1, "C");
  }



		$alunoPorPagina = 0;
	}
  }

  $textoMatriculaRegente  = !empty($matriculaRegente)  ? "Matrícula: " . $matriculaRegente  : "";
  $textoMatriculaRegente2 = !empty($matriculaRegente2) ? "Matrícula: " . $matriculaRegente2 : "";
  $textoMatriculaAdicional = !empty($matriculaAdicional) ? "Matrícula: " . $matriculaAdicional : "";
  $textoMatriculaDiretor = !empty($matriculaDiretor) ? "Matrícula: " . $matriculaDiretor : "";

  if($zadicional != "nao"){
      $sDocente = $regentePrincipalNome;


      $oPdf->SetFont("arial", '', 6);

      /**
       * Autor: Uemerson Santana
       * Data: 28/11/2025
       * Demanda: 17982
       * Razão: Quando há dois regentes, o bloco de assinaturas ganha linhas extras para o
       *        segundo regente. Sem ajustar a altura dos retângulos, a borda do relatório
       *        termina antes da última assinatura, dando a sensação de corte. As alturas
       *        abaixo garantem que toda a área (OBS + assinaturas) fique contida no retângulo.
       */
      /**
       * OBS: Com dois regentes passamos a ter linhas extras (traço, nome, "Regente" e "Matrícula")
       *      para o segundo docente. Por isso a altura precisa ser um pouco maior para não
       *      cortar a última linha dentro do retângulo.
       */
      $iAlturaObsEsquerda = !empty($segundoRegenteNome) ? 40 : 16;
      $iAlturaObsDireita  = !empty($segundoRegenteNome) ? 48 : 24;

      $oPdf->Rect($oPdf->GetX(),     $oPdf->GetY(), 135, $iAlturaObsEsquerda);
      $oPdf->Rect($oPdf->GetX()+135, $oPdf->GetY(), 150, $iAlturaObsDireita);


      $oPdf->Cell(135, 4, "OBS.:", 1, 0, 'L');
      $oPdf->Cell(144, 4, "", 0, 1, 'L');
      $oPdf->Cell(135, 4, substr($textoobs, 0, 125), 1, 0, 'L');
      $oPdf->Cell(144, 4, "", 0, 1, 'L');

      $oPdf->Cell(135, 4, substr($textoobs, 125, 250), 1, 0, 'L');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "______________________________________", 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "______________________________________", 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "______________________________________", 0, 1, 'C');

      $oPdf->Cell(135, 4, substr($textoobs, 250, 375), 0, 0, 'L');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, substr($sDocente,0,33), 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $nomeadi, 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $ndiretor, 0, 1, 'C');

      $oPdf->Cell(135, 4, substr($textoobs, 375, 400), 1, 0, 'L');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "Regente", 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $cargoadi, 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "Diretor", 0, 1, 'C');

      // Linha de matrículas do primeiro regente / adicional / diretor
      $oPdf->Cell(135, 4, "", 1, 0, 'L');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $textoMatriculaRegente, 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $textoMatriculaAdicional, 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $textoMatriculaDiretor, 0, 1, 'C');

      // Segunda linha de assinatura opcional para o segundo regente (anos finais com dois regentes)
      if (!empty($segundoRegenteNome)) {

        // Espaço extra antes do bloco do segundo regente para aumentar área de assinatura
        $oPdf->Cell(135, 6, "", 0, 0, 'L');
        $oPdf->Cell(2,   6, "", 0, 0, 'C');
        $oPdf->Cell(45,  6, "", 0, 0, 'C');
        $oPdf->Cell(2,   6, "", 0, 0, 'C');
        $oPdf->Cell(45,  6, "", 0, 0, 'C');
        $oPdf->Cell(2,   6, "", 0, 0, 'C');
        $oPdf->Cell(45,  6, "", 0, 1, 'C');

        // Linha de traços para o segundo regente (somente na coluna de Regente)
        $oPdf->Cell(135, 4, "", 0, 0, 'L');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "______________________________________", 0, 0, 'C'); // Regente 2
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 0, 'C'); // coluna adicional vazia
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 1, 'C'); // coluna diretor vazia

        // Linha com o nome do segundo regente (somente na coluna de Regente)
        $oPdf->Cell(135, 4, "", 0, 0, 'L');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, substr($segundoRegenteNome, 0, 33), 0, 0, 'C'); // nome regente 2
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 0, 'C'); // adicional vazio
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 1, 'C'); // diretor vazio

        // Linha com rótulo do segundo regente (somente na coluna de Regente)
        $oPdf->Cell(135, 4, "", 0, 0, 'L');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "Regente", 0, 0, 'C'); // rótulo na 1ª coluna
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 0, 'C'); // adicional vazio
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 1, 'C'); // diretor vazio

        // Linha com a matrícula do segundo regente (somente na coluna de Regente)
        $oPdf->Cell(135, 4, "", 0, 0, 'L');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, $textoMatriculaRegente2, 0, 0, 'C'); // matrícula na 1ª coluna
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 0, 'C'); // adicional vazio
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 1, 'C'); // diretor vazio
      }
	  //$alunoPorPagina = 0;
  }else{

      $sDocente = $regentePrincipalNome;

      $oPdf->SetFont("arial", '', 6);

      /**
       * Autor: Uemerson Santana
       * Data: 28/11/2025
       * Demanda: 17982
       * Razão: Mesma correção do bloco com assinatura adicional: quando existe segundo regente,
       *        aumentamos a altura dos retângulos para que a borda envolva todas as linhas
       *        de assinatura (primeiro e segundo regente + diretor).
       */
      /**
       * Mesmo ajuste de altura quando não há assinatura adicional:
       * com dois regentes, aumentamos a área vertical para comportar
       * as linhas extras de assinatura do segundo regente.
       */
      $iAlturaObsEsquerda = !empty($segundoRegenteNome) ? 40 : 16;
      $iAlturaObsDireita  = !empty($segundoRegenteNome) ? 48 : 24;

      $oPdf->Rect($oPdf->GetX(),     $oPdf->GetY(), 135, $iAlturaObsEsquerda);
      $oPdf->Rect($oPdf->GetX()+135, $oPdf->GetY(), 150, $iAlturaObsDireita);


      $oPdf->Cell(135, 4, "OBS.:", 1, 0, 'L');
      $oPdf->Cell(144, 4, "", 0, 1, 'L');
      $oPdf->Cell(135, 4, substr($textoobs, 0, 125), 1, 0, 'L');
      $oPdf->Cell(144, 4, "", 0, 1, 'L');

      $oPdf->Cell(135, 4, substr($textoobs, 125, 250), 1, 0, 'L');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "______________________________________", 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "______________________________________", 0, 1, 'C');

      $oPdf->Cell(135, 4, substr($textoobs, 250, 375), 0, 0, 'L');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, substr($sDocente,0,33), 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $ndiretor, 0, 1, 'C');

      $oPdf->Cell(135, 4, substr($textoobs, 375, 400), 1, 0, 'L');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "Regente", 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "Diretor", 0, 1, 'C');

      // Linha de matrículas do primeiro regente / diretor
      $oPdf->Cell(135, 4, "", 1, 0, 'L');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $textoMatriculaRegente, 0, 0, 'C');
      $oPdf->Cell(2, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, "", 0, 0, 'C');
      $oPdf->Cell(45, 4, $textoMatriculaDiretor, 0, 1, 'C');

      // Segunda linha de assinatura opcional para o segundo regente (anos finais com dois regentes)
      if (!empty($segundoRegenteNome)) {

        // Espaço extra antes do bloco do segundo regente para aumentar área de assinatura
        $oPdf->Cell(135, 6, "", 0, 0, 'L');
        $oPdf->Cell(2,   6, "", 0, 0, 'C');
        $oPdf->Cell(45,  6, "", 0, 0, 'C');
        $oPdf->Cell(2,   6, "", 0, 0, 'C');
        $oPdf->Cell(45,  6, "", 0, 0, 'C');
        $oPdf->Cell(2,   6, "", 0, 0, 'C');
        $oPdf->Cell(45,  6, "", 0, 1, 'C');

        // Linha de traços para o segundo regente (somente na coluna de Regente)
        $oPdf->Cell(135, 4, "", 0, 0, 'L');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "______________________________________", 0, 0, 'C'); // Regente 2
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 0, 'C');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 1, 'C');

        // Linha com o nome do segundo regente
        $oPdf->Cell(135, 4, "", 0, 0, 'L');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, substr($segundoRegenteNome, 0, 33), 0, 0, 'C');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 0, 'C');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 1, 'C');

        // Linha com rótulo do segundo regente
        $oPdf->Cell(135, 4, "", 0, 0, 'L');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "Regente", 0, 0, 'C');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 1, 'C');

        // Linha com a matrícula do segundo regente
        $oPdf->Cell(135, 4, "", 0, 0, 'L');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, $textoMatriculaRegente2, 0, 0, 'C');
        $oPdf->Cell(2, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 0, 'C');
        $oPdf->Cell(45, 4, "", 0, 1, 'C');
      }
  }
  $contador++;

  $alunoPorPagina = 0;
// fiz o bloqueio da mudança de pagina abaixo por ja esta mudando na primeira linha de impressão
//  if(($tamanho - $contador) != 0){
//    $oPdf->AddPage();
//  }

}



//die("Maaoe");
$oPdf->Output();

/*



    "turma_i_base_fk" FOREIGN KEY (ed57_i_base) REFERENCES base(ed31_i_codigo) MATCH FULL DEFERRABLE



    "turma_i_calendario_fk" FOREIGN KEY (ed57_i_calendario) REFERENCES calendario(ed52_i_codigo) MATCH FULL DEFERRABLE
    "turma_i_fk" FOREIGN KEY (ed57_i_escola) REFERENCES escola(ed18_i_codigo) MATCH FULL DEFERRABLE
    "turma_i_sala_fk" FOREIGN KEY (ed57_i_sala) REFERENCES sala(ed16_i_codigo) MATCH FULL DEFERRABLE
    "turma_i_turno_fk" FOREIGN KEY (ed57_i_turno) REFERENCES turno(ed15_i_codigo) MATCH FULL DEFERRABLE





SELECT ed31_c_descr FROM base INNER JOIN turma ON ed57_i_base = ed31_i_codigo WHERE ed57_i_codigo = 2451;





Curso: Ensino Fundamental
Turma: EF 301 - SELECT ed57_c_descr FROM turma WHERE ed57_i_codigo = 2451;
Calendário: EN FUN ANOS INICIAIS - SELECT ed52_c_descr FROM calendario WHERE ed52_i_codigo = 273;
Etapa: 3º ANO
Aulas Dadas: 52
Regente: Nome do Professor Responsável






[escola] => 20100
[calendario] => 273
[turma] => 2451
[periodo] => 69
*/

?>
