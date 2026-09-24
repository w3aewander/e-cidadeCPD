<?
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

require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("fpdf151educacao/pdfwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("model/educacao/Turma.model.php"));
require_once(modification("model/educacao/AlunoRepository.model.php"));
require_once(modification("model/educacao/relatorio/RelatorioGradeAproveitamento.model.php"));

$resfinal = 0;
function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function retornaNacionalidade($ed47_i_nacion){
  if($ed47_i_nacion == 1){
    return "Brasileira";
  }elseif($ed47_i_nacion == 2){
    return "Brasileira no exterior ou naturalizado";
  }else{
    return "Estrangeira";
  }
}

function retornaMunicipio($ed47_i_censomunicend){
  $sql = pg_query("SELECT ed261_c_nome,ed261_i_censouf FROM censomunic WHERE ed261_i_codigo = {$ed47_i_censomunicend}"); 
  $resultado = pg_fetch_all($sql);
  $nome    = strtolower($resultado[0]["ed261_c_nome"]);
  return ucwords($nome);
}
function retornaNaturalidade($ed47_i_censomunicnat){
  $sql = pg_query("SELECT ed261_c_nome,ed261_i_censouf FROM censomunic WHERE ed261_i_codigo = {$ed47_i_censomunicnat}");
  $resultado = pg_fetch_all($sql);
  $nome    = strtolower($resultado[0]["ed261_c_nome"]);
  return ucwords($nome);
}

function buscaCodigoRegencia($ed57_i_codigo){
  $sql = pg_query("SELECT ed59_i_codigo, ed59_i_turma, ed59_i_disciplina FROM regencia WHERE ed59_i_turma = {$ed57_i_codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed59_i_codigo"];
}

function buscaCodigoRegencia2($ed57_i_codigo){
  $sql = pg_query("SELECT ed59_i_codigo, ed59_i_turma, ed59_i_disciplina FROM regencia WHERE ed59_i_turma = {$ed57_i_codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma){  
  $sql = "SELECT 
                   diario.*, 
				   ed59_i_codigo,
				   ed59_i_disciplina 
				   from 
				   diario 
				   inner join aluno on ed47_i_codigo = ed95_i_aluno 
				   inner join matricula on ed60_i_aluno = ed47_i_codigo 
				   inner join matriculaserie on ed60_i_codigo = ed221_i_matricula 
				   inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie 
                   inner join disciplina         on ed12_i_codigo  = ed59_i_disciplina
                   inner join censodisciplina    on ed265_i_codigo = ed12_i_caddisciplina
				   where 
				   ed60_i_codigo = {$ed60_i_codigo} 
				   and 
				   ed95_i_aluno = {$ed60_i_aluno} 
				   and 
				   ed95_i_regencia = ed59_i_codigo 
				   and 
				   ed95_i_serie = {$ed11_i_codigo} 
				   and 
				   ed59_i_turma = {$ed60_i_turma}
				   order by ed59_i_ordenacao";
				   
  $sql1 = pg_query($sql	);
  $resultado = pg_fetch_all($sql1);
  return $resultado[0]["ed95_i_codigo"];
}

function retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma){  
    $sql = "SELECT 
                   diario.*, 
				   ed59_i_codigo,
				   ed59_i_disciplina
				   from 
				   diario 
				   inner join aluno on ed47_i_codigo = ed95_i_aluno 
				   inner join matricula on ed60_i_aluno = ed47_i_codigo 
				   inner join matriculaserie on ed60_i_codigo = ed221_i_matricula 
				   inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie 
				   where 
				   ed60_i_codigo = {$ed60_i_codigo} 
				   and 
				   ed95_i_aluno = {$ed60_i_aluno} 
				   and 
				   ed95_i_regencia = ed59_i_codigo 
				   and 
				   ed95_i_serie = {$ed11_i_codigo} 
				   and 
				   ed59_i_turma = {$ed60_i_turma}
				   order by ed59_i_ordenacao";

  $sql1 = pg_query($sql);
	

  $resultado = pg_fetch_all($sql1);
  return $resultado;
}

function dadosDiario($codiario){
  $sql = pg_query("SELECT 
                   ed72_i_codigo as codigo, 
				   ed72_i_procavaliacao as codigo_elemento, 
				   ed72_i_numfaltas as numero_faltas, 
				   ed80_i_codigo as codigo_faltas_abonadas, 
				   ed72_i_valornota as valor_nota, 
				   ed72_i_valornota as valor_nota_real, 
				   ed72_c_valorconceito as valor_conceito, 
				   ed72_t_parecer as parecer, 
				   ed72_c_aprovmin as minimo, 
				   ed72_c_amparo as amparo, 
				   ed41_i_sequencia as sequencia, 
				   trim(ed93_t_parecer) as parecerpadronizado, 
				   ed72_i_escola as escola, 
				   ed72_c_tipo as origem, 
				   ed72_c_convertido as convertido, 
				   (select 
				   ed39_i_sequencia 
				   from conceito 
				   where 
				   conceito.ed39_i_formaavaliacao = ed41_i_formaavaliacao 
				   and 
				   conceito.ed39_c_conceito = ed72_c_valorconceito) as ordem_conceito, 
				   'A' as tipo_elemento, 
				   ed72_t_obs as observacao, 
				   false as em_recuperacao 
				   from 
				   diarioavaliacao 
				   inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao 
				   left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo 
				   left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo 
				   where ed72_i_diario = {$codiario} 
				   order by sequencia");
				   
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function dadosDiario2($codiario){
  $sql = pg_query("SELECT 
                   ed72_i_codigo as codigo, 
				   ed72_i_procavaliacao as codigo_elemento, 
				   ed72_i_numfaltas as numero_faltas, 
				   ed80_i_codigo as codigo_faltas_abonadas, 
				   ed72_i_valornota as valor_nota, 
				   ed72_i_valornota as valor_nota_real, 
				   ed72_c_valorconceito as valor_conceito, 
				   ed72_t_parecer as parecer, 
				   ed72_c_aprovmin as minimo, 
				   ed72_c_amparo as amparo, 
				   ed41_i_sequencia as sequencia, 
				   trim(ed93_t_parecer) as parecerpadronizado, 
				   ed72_i_escola as escola, ed72_c_tipo as origem, 
				   ed72_c_convertido as convertido, 
				   (select 
				    ed39_i_sequencia 
					from 
					conceito 
					where 
					conceito.ed39_i_formaavaliacao = ed41_i_formaavaliacao 
					and 
					conceito.ed39_c_conceito = ed72_c_valorconceito) as ordem_conceito, 
					'A' as tipo_elemento, 
					ed72_t_obs as observacao, 
					false as em_recuperacao 
					from 
					diarioavaliacao 
					inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao 
					left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo 
					left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo 
					where 
					ed72_i_diario = {$codiario} 
					order by sequencia");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function dadosAulas($codregencia){
  $sql = "SELECT 
                   ed78_i_codigo, 
				   ed78_i_regencia, 
				   ed78_i_procavaliacao, 
				   ed78_i_aulasdadas 
				   from 
				   regenciaperiodo 
				   inner join procavaliacao on procavaliacao.ed41_i_codigo = regenciaperiodo.ed78_i_procavaliacao 
				   inner join regencia on regencia.ed59_i_codigo = regenciaperiodo.ed78_i_regencia 
				   inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao 
				   inner join formaavaliacao on formaavaliacao.ed37_i_codigo = procavaliacao.ed41_i_formaavaliacao 
				   inner join procedimento on procedimento.ed40_i_codigo = procavaliacao.ed41_i_procedimento 
				   inner join disciplina on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina 
				   inner join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina 
				   inner join turma on turma.ed57_i_codigo = regencia.ed59_i_turma 
				   where 
				   ed78_i_regencia = {$codregencia} and ed09_c_somach = 'S' 
				   ORDER BY ed78_i_procavaliacao";

		   
  $sql1 = pg_query($sql);
  $resultado = pg_fetch_all($sql1);
  return $resultado;
}

function retornaNomeDisciplina($ed59_i_disciplina){
  $sql = pg_query("SELECT ed232_c_descr FROM caddisciplina INNER JOIN disciplina ON ed232_i_codigo = ed12_i_caddisciplina WHERE ed12_i_codigo = {$ed59_i_disciplina}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed232_c_descr"];
}

function ajustaNota($nota){
  if(strlen($nota) == 1){
    $nota = $nota . ".0";
  }elseif($nota == 10){
    $nota = $nota . ".0";
  }
  return $nota;
}

function buscaParametroFrequencia($escola, $ano){	
  $sql = pg_query("SELECT ed328_arredondafrequencia FROM avaliacaoestruturafrequencia WHERE ed328_escola = {$escola} AND ed328_ano = {$ano}");
  $resultado = pg_fetch_all($sql);
  $resultado = db_fieldsmemory($result,$x);
  return $resultado[0]["ed328_arredondafrequencia"];
}

function resultado_final($escola,$etapa,$turma,$aluno){
	
$sql = "
		select 
		ed47_i_codigo         as aluno,
		ed47_v_nome           as alunonome,
		ed18_i_codigo         as codigo_escola,   
		ed18_c_nome           as nome_escola,     
		ed10_i_codigo         as ensino,          
		ed11_i_codigo         as codigo_etapa,    
		ed11_c_descr          as descricao_etapa, 
		ed57_i_codigo         as codigo_turma,    
		ed60_i_codigo         as matricula_aluno, 
		ed60_c_situacao       as situacao, 
		ed95_i_codigo         as diario,          
		ed74_c_resultadofinal as resultado_final,
        1                     as resfinal		
		from 
		turma
		inner join escola              on escola.ed18_i_codigo              = turma.ed57_i_escola    
		inner join calendario          on calendario.ed52_i_codigo          = turma.ed57_i_calendario
		inner join base                on base.ed31_i_codigo                = turma.ed57_i_base      
		inner join cursoedu            on cursoedu.ed29_i_codigo            = base.ed31_i_curso      
		inner join ensino              on ensino.ed10_i_codigo              = cursoedu.ed29_i_ensino 
		inner join turmaserieregimemat on turmaserieregimemat.ed220_i_turma = turma.ed57_i_codigo    
		inner join serieregimemat      on serieregimemat.ed223_i_codigo     = turmaserieregimemat.ed220_i_serieregimemat
		inner join serie               on serie.ed11_i_codigo               = serieregimemat.ed223_i_serie
		inner join matricula           on matricula.ed60_i_turma            = turma.ed57_i_codigo    
		inner join aluno               on aluno.ed47_i_codigo               = matricula.ed60_i_aluno 
		inner join regencia            on  regencia.ed59_i_turma            = turma.ed57_i_codigo    
		inner join diario              on  diario.ed95_i_escola             = escola.ed18_i_codigo    
									   and diario.ed95_i_calendario         = calendario.ed52_i_codigo   
									   and diario.ed95_i_aluno              = aluno.ed47_i_codigo    
									   and diario.ed95_i_serie              = serie.ed11_i_codigo    
									   and diario.ed95_i_regencia           = regencia.ed59_i_codigo 
		inner join diariofinal         on  diariofinal.ed74_i_diario        = diario.ed95_i_codigo   
		Where
		escola.ed18_i_codigo = ".$escola."
		and
		ed11_i_codigo        = ".$etapa."
		and
		ed57_i_codigo        = ".$turma."
		and
		ed47_i_codigo        = ".$aluno."
        and
        (ed74_c_resultadofinal <>'' or ed74_c_resultadofinal <> ' ')
		and
		ed52_d_resultfinal  <='".date('Y-m-d')."'
		
       ";                 
	   
        $rsfinal = db_query($sql);
        if(pg_num_rows($rsfinal)>0)
		{
            db_fieldsmemory($rsfinal,0);			
		}else{
			$resfinal =0; 
		}	
		return $resfinal;
}


function transferencia($aluno){
$sql = "
		select
		ed60_i_aluno,
		ed60_d_datasaida,
		ed60_c_situacao
		from 
		matricula
		where
		ed60_d_datasaida between '".db_getsession("DB_anousu")."-01-01' AND '".db_getsession("DB_anousu")."-12-31'  
		and
		ed60_i_aluno = ".$aluno;
		
$rstransf = db_query($sql);
db_fieldsmemory($rstransf);
return $ed60_c_situacao;

}	

function fimanoletivo($escola,$etapa,$turma,$aluno){
$sql = "
		select 
		distinct on (ed52_d_resultfinal)
		ed52_d_resultfinal
		from 
		turma
		inner join escola              on escola.ed18_i_codigo              = turma.ed57_i_escola    
		inner join calendario          on calendario.ed52_i_codigo          = turma.ed57_i_calendario
		inner join base                on base.ed31_i_codigo                = turma.ed57_i_base      
		inner join cursoedu            on cursoedu.ed29_i_codigo            = base.ed31_i_curso      
		inner join ensino              on ensino.ed10_i_codigo              = cursoedu.ed29_i_ensino 
		inner join turmaserieregimemat on turmaserieregimemat.ed220_i_turma = turma.ed57_i_codigo    
		inner join serieregimemat      on serieregimemat.ed223_i_codigo     = turmaserieregimemat.ed220_i_serieregimemat
		inner join serie               on serie.ed11_i_codigo               = serieregimemat.ed223_i_serie
		inner join matricula           on matricula.ed60_i_turma            = turma.ed57_i_codigo    
		inner join aluno               on aluno.ed47_i_codigo               = matricula.ed60_i_aluno 
		inner join regencia            on  regencia.ed59_i_turma            = turma.ed57_i_codigo    
		inner join diario              on  diario.ed95_i_escola             = escola.ed18_i_codigo   
									   and diario.ed95_i_calendario         = calendario.ed52_i_codigo   
									   and diario.ed95_i_aluno              = aluno.ed47_i_codigo    
									   and diario.ed95_i_serie              = serie.ed11_i_codigo    
									   and diario.ed95_i_regencia           = regencia.ed59_i_codigo 
		inner join diariofinal         on  diariofinal.ed74_i_diario        = diario.ed95_i_codigo   
		Where
		escola.ed18_i_codigo = ".$escola."
		and
		ed11_i_codigo        = ".$etapa."
		and
		ed57_i_codigo        = ".$turma."
		and
		ed47_i_codigo        = ".$aluno."
        and
        (ed74_c_resultadofinal <>'' or ed74_c_resultadofinal <> ' ')
		";
        $rsfimletivo = db_query($sql);
        db_fieldsmemory($rsfimletivo,0);			
		return $ed52_d_resultfinal;
}

$resultedu           = eduparametros(db_getsession("DB_coddepto"));
$permitenotaembranco = VerParametroNota(db_getsession("DB_coddepto"));
$escola              = db_getsession("DB_coddepto");
$oGet                = db_utils::postMemory( $_GET );
$sObs                = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $sObs);

$clmatricula       = new cl_matricula;
$claluno           = new cl_aluno;
$clturma           = new cl_turma;
$clEscola          = new cl_escola();
$cldiarioavaliacao = new cl_diarioavaliacao;
$cldiarioresultado = new cl_diarioresultado;
$clregenteconselho = new cl_regenteconselho;
$clregencia        = new cl_regencia;
$clrotulo          = new rotulocampo;
$oDaoEscolaDiretor = new cl_escoladiretor();
$oDaoTipoSanguineo = new cl_tiposanguineo();

$claluno->rotulo->label();
$clrotulo->label("ed76_i_escola");
$clrotulo->label("ed76_d_data");

/**
 * Busca o município da escola
 */
$sSqlDadosEscola = $clEscola->sql_query( "", "ed261_c_nome as mun_escola", "", "ed18_i_codigo = {$escola}" );
$rsDadosEscola   = db_query( $sSqlDadosEscola );
$oDadosEscola    = db_utils::fieldsMemory( $rsDadosEscola, 0 );
$mun_escola      = $oDadosEscola->mun_escola;

/**
 * Campos a serem retornados em relação ao diretor da escola
 */
$sCamposDiretor    = " 'DIRETOR' as funcao, ";
$sCamposDiretor   .= "          case when ed20_i_tiposervidor = 1 then ";
$sCamposDiretor   .= "                  cgmrh.z01_nome ";
$sCamposDiretor   .= "               else cgmcgm.z01_nome ";
$sCamposDiretor   .= "            end as nome,";
$sCamposDiretor   .= " ed83_c_descr||' n°: '||ed05_c_numero::varchar as descricao,'D' as tipo";
$sWhereDiretor     = " ed254_i_escola = ".$escola." AND ed254_c_tipo = 'A' AND ed01_i_funcaoadmin = 2 limit 1 ";
$sSqlDiretor       = $oDaoEscolaDiretor->sql_query_resultadofinal("", $sCamposDiretor, "", $sWhereDiretor);
$rsDiretor         = $oDaoEscolaDiretor->sql_record($sSqlDiretor);
$iLinhasDiretor    = $oDaoEscolaDiretor->numrows;

if ( $iLinhasDiretor > 0 ) {

  db_fieldsmemory( $result, 0 );
  $nome = trim( db_utils::fieldsmemory( $rsDiretor, 0 )->nome );
} else {
  $nome= "";
}

/**
 * Campos para impressão dos dados do aluno
 */
$camp  = " ed60_d_datasaida as datasaida, ed10_i_codigo as ensino, ";
$camp .= " case ";
$camp .= "   when ed60_c_situacao = 'TRANSFERIDO REDE' then ";
$camp .= "    (select escoladestino.ed18_c_nome from transfescolarede ";
$camp .= "      inner join atestvaga  on  atestvaga.ed102_i_codigo = transfescolarede.ed103_i_atestvaga ";
$camp .= "      inner join escola  as escoladestino on  escoladestino.ed18_i_codigo = atestvaga.ed102_i_escola ";
$camp .= "     where ed103_i_matricula = ed60_i_codigo order by ed103_d_data desc limit 1) ";
$camp .= "   when ed60_c_situacao = 'TRANSFERIDO FORA' then ";
$camp .= "    (select escolaproc1.ed82_c_nome from transfescolafora ";
$camp .= "     inner join escolaproc as escolaproc1 on  escolaproc1.ed82_i_codigo = transfescolafora.ed104_i_escoladestino ";
$camp .= "     where ed104_i_matricula = ed60_i_codigo order by ed104_d_data desc limit 1) ";
$camp .= "    else null ";
$camp .= "  end as destinosaida, ";
$camp .= "  matricula.*, ";
$camp .= "  turma.ed57_c_descr, ";
$camp .= "  turma.ed57_i_codigo, ";
$camp .= "  turmaserieregimemat.ed220_i_procedimento, ";
$camp .= "  turma.ed57_c_medfreq, ";
$camp .= "  calendario.ed52_c_descr, ";
$camp .= "  calendario.ed52_i_ano, ";
$camp .= "  case when turma.ed57_i_tipoturma = 2 then ";
$camp .= "   fc_nomeetapaturma(ed60_i_turma) else ";
$camp .= "   serie.ed11_c_descr ";
$camp .= "  end as ed11_c_descr, ";
$camp .= "  serie.ed11_c_descr as ejaturma, ";
$camp .= "  serie.ed11_i_codigo, ";
$camp .= "  escola.ed18_c_nome, ";
$camp .= "  turno.ed15_c_nome, ";
$camp .= "  aluno.ed47_v_nome, ";
$camp .= "  alunoprimat.ed76_i_codigo, ";
$camp .= "  alunoprimat.ed76_i_escola, ";
$camp .= "  alunoprimat.ed76_d_data, ";
$camp .= "  alunoprimat.ed76_c_tipo, ";
$camp .= "  case when ed76_c_tipo = 'M' ";
$camp .= "   then escolaprimat.ed18_c_nome else escolaproc.ed82_c_nome end as nomeescola, ";
$camp .= "   aluno.*  ";

$sSqlMatricula = $clmatricula->sql_query( "", $camp, "ed60_d_datamatricula desc", " ed60_i_codigo in ($alunos)" );
$result1       = $clmatricula->sql_record( $sSqlMatricula );

$sSqlTipoSanguineo = $oDaoTipoSanguineo->sql_query_file("", "*", "sd100_sequencial", "");
$rsTipoSanguineo   = $oDaoTipoSanguineo->sql_record($sSqlTipoSanguineo);
$iLinhas           = $oDaoTipoSanguineo->numrows;

$aTiposSanguineos = array();

if ( isset( $rsTipoSanguineo ) && $iLinhas > 0) {
  for ( $iContador = 0; $iContador < $iLinhas; $iContador++ ) {
    $oDados = db_utils::fieldsMemory($rsTipoSanguineo, $iContador);
    $aTiposSanguineos[$oDados->sd100_sequencial] = $oDados->sd100_tipo;
  }
}

/**
 * Caso não encontre registros da matrícula do aluno, apresenta a mensagem de nenhum registro encontrado
 */
if ( $clmatricula->numrows == 0 ) {
  db_redireciona( "db_erros.php?fechar=true&db_erro=Nenhum registro encontrado." );
}

$arrfreq = buscaParametroFrequencia($escola, db_getsession("DB_anousu"));


//$tudi = pg_fetch_all($result1);
//testa($tudi);
//die("Confere");

/**
 * Início da impressão do PDF
 */
$pdf = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->ln(5);
$pdf->imprime_rodape = false;
db_fieldsmemory( $result1, 0 );






/**
 * Cabeçalho da ficha do aluno
 */
$head1 = "RENOVAÇÃO DE MATRÍCULA";
$head2 = "{$ed47_i_codigo} - {$ed47_v_nome}";

$pdf->Addpage('P');
$pdf->setfillcolor(223);

$u       = 0;
$iCodigo = 0;

/**
 * Percorre as matrículas encontradas
 */
$anos_iniciais = $ed52_c_descr;
$ejaturmaC     = $ejaturma; //substr($ed11_c_descr,0,18);
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq, $ejaturma);
//fwrite($arq,"\r\n");
//fclose($arq); 		

for ( $ww = 0; $ww < $clmatricula->numrows; $ww ++ ) {

  db_fieldsmemory( $result1, $ww );
  
  $xmunicipio = retornaMunicipio($ed47_i_censomunicend);
  $xnacionalidade = retornaNacionalidade($ed47_i_nacion);
  $xnaturalidade  = retornaNaturalidade($ed47_i_censomunicnat);
  $ufcenso        = retornaNaturalidade($ed47_i_censoufnat);
  $xfotoaluno = trim($ed47_c_foto);

  if(($ed52_c_descr == "EDUCAÇÃO INFANTIL" or $ed52_c_descr == "ED. INFANTIL 2024") ){
    $xcodiario = retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);
    $xdiario = dadosDiario($xcodiario);

    $codreg = buscaCodigoRegencia($ed57_i_codigo);
    $xaulas = dadosAulas($codreg);
	
  }//FIM EDUCAÇÃO INFANTIL
//***********************************************************************************************************************************************************************************	
  if(($ed52_c_descr == "EN FUN ANOS INICIAIS" or $ed52_c_descr == "ANOS INICIAIS 2024") && $ed11_c_descr == "1º ANO"){
//***********************************************************************************************************************************************************************************	
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq, 'estou aqui');
//fwrite($arq,"\r\n");
//fclose($arq); 		
	  
// aqui imprime correto na não	  
    $indice = 0;  
    $dadosgrade = array();
    $xcodiario = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  
    $codreg = buscaCodigoRegencia2($ed57_i_codigo); 
  
    foreach ($xcodiario as $linha) {    
      $xcodiario = $linha["ed95_i_codigo"];
      $xdiario = dadosDiario2($xcodiario);    
      $xaulas = dadosAulas($linha["ed95_i_regencia"]);
    
      if($xaulas){
        $dadosgrade[$indice]["xdiario"] = $xdiario;
        $dadosgrade[$indice]["xaulas"]  = $xaulas;    
        $indice++;      
      }    
    }  
  }//FIM 1º ANO DO FUNDAMENTAL
  
 

  if(($ed52_c_descr == "EJA ANOS INICIAIS" or $ed52_c_descr = "EJA INICIAIS 2024") && $ejaturmaC == "CIC BÁS DE ALFABET"){
	  
    $xcodiario = retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);
    $xdiario = dadosDiario($xcodiario);

    $codreg = buscaCodigoRegencia($ed57_i_codigo);
    $xaulas = dadosAulas($codreg);

    $indice2 = 0;
    $dadosgrade2 = array();
    $xcodiario2 = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);    
    $codreg2    = buscaCodigoRegencia2($ed57_i_codigo);    
	

    $ceja = 0;
    foreach ($xcodiario2 as $linha) {


      $xcodiario2 = $linha["ed95_i_codigo"];
      $xdiario2   = dadosDiario2($xcodiario2);
//      $xdiario2["disciplina"] = retornaNomeDisciplina($linha["ed59_i_disciplina"]);
      
      $xdiario2["disciplina"]   = retornaNomeDisciplina($codreg2[$ceja]["ed59_i_disciplina"]);
	  
	  
      $dadosgrade2[$indice2]    = $xdiario2;      
	  $dadosgrade2[$indice]["xaulas"]  = $xaulas;
      $indice2++;
      $ceja++;
    }
	
  }//FIM EJA

  if(($ed52_c_descr == "EJA ANOS INICIAIS" or $ed52_c_descr = "EJA INICIAIS 2024") && ($ejaturma == "1º CICLO" || $ejaturma == "2º CICLO")){
    $xcodiario = retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);
    $xdiario = dadosDiario($xcodiario);

    $codreg = buscaCodigoRegencia($ed57_i_codigo);
    $xaulas = dadosAulas($codreg);  
    $indice2 = 0;
    $dadosgrade2 = array();
    $xcodiario2 = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);    
    $codreg2 = buscaCodigoRegencia2($ed57_i_codigo);    

    $ceja = 0;
    foreach ($xcodiario2 as $linha) {
      $xcodiario2 = $linha["ed95_i_codigo"];
      $xdiario2   = dadosDiario2($xcodiario2);
      $xdiario2["disciplina"] = retornaNomeDisciplina($linha["ed59_i_disciplina"]);
      //$xdiario2["disciplina"] = retornaNomeDisciplina($codreg2[$ceja]["ed59_i_disciplina"]);
      $dadosgrade2[$indice2] = $xdiario2;      
      $indice2++;
      $ceja++;
    }
	
    //testa($dadosgrade2); die("Confere");
  }//FIM EJA


  $data         = date( "Y-m-d",DB_getsession("DB_datausu") );
  $dia          = date( "d" );
  $mes          = date( "m" );
  $ano          = date( "Y" );
  $mes_extenso  = array(
                         "01" => "janeiro",
                         "02" => "fevereiro",
                         "03" => "março",
                         "04" => "abril",
                         "05" => "maio",
                         "06" => "junho",
                         "07" => "julho",
                         "08" => "agosto",
                         "09" => "setembro",
                         "10" => "outubro",
                         "11" => "novembro",
                         "12" => "dezembro"
                       );
  $data_extenso = $mun_escola.", ".$dia." de ".$mes_extenso[$mes]." de ".$ano.".";

  /**
   * Cabeçalho da ficha do aluno
   */
  $head1        = "FICHA DO ALUNO";
  $head2        = "{$ed47_i_codigo} - {$ed47_v_nome}";

  if ( $iCodigo != $ed60_i_codigo ) {

    if ( $ww != 0 ) {

   	  $pdf->ln(5);
      $pdf->Addpage('P');
    }

    $iCodigo = $ed60_i_codigo;
  }

    // Verifica se ambos os telefones existem
  if (!empty($ed47_v_telef) && !empty($ed47_v_telcel)) {
    $telefones = "{$ed47_v_telef} / {$ed47_v_telcel}";
  } else {
    // Se apenas um dos telefones existir, use apenas esse
    $telefones = !empty($ed47_v_telef) ? $ed47_v_telef : $ed47_v_telcel;
  }



  /**
   * ****************************
   * Impressão dos DADOS PESSOAIS
   * ****************************
   */
  $pdf->Ln(13);
  $pdf->setfont( 'arial', 'b', 15);
  $pdf->cell( 190, 4, "RENOVAÇÃO DE MATRÍCULA", "", 1, "C", 0 );
  //$pdf->cell( 3,   4, "",               "L",   0, "C", 0 );

  $pdf->Ln(10);

  //$pdf->cell( 35, 4, , 0, 0, "L", 0 );
  if (!empty($ed47_d_nasc)) {
    $dataFormatada = date('d/m/Y', strtotime($ed47_d_nasc));
}
  $pdf->setfont( 'arial', '', 10 );
  $pdf->cell(70, 4, "{$ed47_v_nome}, nascido(a) em {$dataFormatada}, devidamente matriculado(a), na turma", 0, 1, "L", 0);
  $pdf->Ln(1);
  $pdf->setfont( 'arial', '', 10 );
  $pdf->cell(40, 4, "{$ed57_c_descr} do(a) {$ed11_c_descr}, turno(s) {$ed15_c_nome}, MATRÍCULA {$ed60_i_aluno}", 0, 0, "L", 0 );
  $pdf->Ln(); // Mover para a próxima linha
  $pdf->Ln(6);
  
  $pdf->setfont( 'arial', 'b', 10 );  
  $pdf->cell(20, 4, "Endereço:", 0, 0, "L", 0 );
  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(60, 4, "{$ed47_v_ender}", 0, 0, "L", 0 );

  $pdf->setfont( 'arial', 'b', 10 );  
  $pdf->cell(15, 4, "Bairro:", 0, 0, "L", 0 );
  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(3, 4, "{$ed47_v_bairro}", 0, 1, "L", 0 );

  $pdf->setfont( 'arial', 'b', 10 );  
  $pdf->cell(20, 4, "CEP:", 0, 0, "L", 0 );
  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(60, 4, "{$ed47_v_cep}", 0, 0, "L", 0 );

  $pdf->setfont( 'arial', 'b', 10 );  
  $pdf->cell(20, 4, "Município:", 0, 0, "L", 0 );
  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(40, 4, "{$xmunicipio}", 0, 1, "L", 0 );

  $pdf->setfont( 'arial', 'b', 10 );  
  $pdf->cell(20, 4, "Telefone:", 0, 0, "L", 0 );
  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(40, 4, "{$telefones}", 0, 1, "L", 0 );

  $pdf->Ln(10);

  $pdf->cell(95, 4, "Eu, _____________________________________________________________________, Responsável pelo aluno(a)", 0, 1, "L", 0 );
  $pdf->Ln(1);
  $pdf->cell(100, 4, "acima, confirmo a matrícula para o ano de _______, comprometendo-me de comparecer a esta Unidade Escola sempre", 0, 1, "L", 0 );
  $pdf->Ln(1);
  $pdf->cell(40, 4, "que se fizer necessário e acompanhá-lo a fim de que cumpra o Regimento Escolar Único do Município de Volta Redonda.", 0, 0, "L", 0 );

  $pdf->Ln(10);

  $pdf->setfont( 'arial', 'b', 10 );  
  $pdf->cell(30, 4, "Bolsa Família:", 0, 0, "L", 0 );

  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(15, 4, "[  ] Sim", 0, 0, "L", 0 );
  $pdf->cell(10, 4, "[  ] Não", 0, 0, "L", 0 );

  // $pdf->Ln(6);

  // $pdf->setfont( 'arial', 'b', 10 );  
  // $pdf->cell(30, 4, "Cartão de Passe:", 0, 0, "L", 0 );

  // $pdf->setfont( 'arial', '', 10 );  
  // $pdf->cell(15, 4, "[  ] Sim", 0, 0, "L", 0 );
  // $pdf->cell(15, 4, "[  ] Não", 0, 0, "L", 0 );
  // $pdf->cell(25, 4, "[  ] Renovação", 0, 0, "L", 0 );
  // $pdf->cell(20, 4, "[  ] Recarga", 0, 0, "L", 0 );
  // $pdf->cell(10, 4, "[  ] Livre", 0, 0, "L", 0 );

  $pdf->Ln(6);

  $pdf->setfont( 'arial', 'b', 10 );  
  $pdf->cell(65, 4, "Mudança de Endereço e/ou Telefone:", 0, 0, "L", 0 );

  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(20, 4, "[  ] Sim", 0, 0, "L", 0 );
  $pdf->cell(40, 4, "[  ] Não", 0, 1, "L", 0 );


  $pdf->Ln(10);

  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(173, 4, "Endereço: _____________________________________________________________________________,", 0, 0, "L", 0 );
  $pdf->cell(17, 4, "N° _____,", 0, 1, "L", 0 );
  $pdf->Ln(3);
  $pdf->cell(65, 4, "Bairro __________________________,", 0, 0, "L", 0 );
  $pdf->cell(38, 4, "CEP _____________,", 0, 0, "L", 0 );
  $pdf->cell(25, 4, "Município __________________________.", 0, 0, "L", 0 );

  $pdf->Ln(15);
  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(100, 4, "Data: ____/_____/_____", 0, 0, "L", 0 );

  $pdf->setfont( 'arial', '', 10 );  
  $pdf->cell(100, 4, "Assinatura do Responsável: _____________________", 0, 0, "L", 0 );

  $pdf->Ln(15);
  $pdf->multicell(0, 4, "Obs.: {$sObs}", 0, "L", 0 );

  


  
 

  //$pdf->cell( 3,  4, "",          "L", 0, "C", 0 );
//
  
  //var_dump($ed52_c_descr, $ed11_c_descr); die("Test");

    

//***************************************************************************************************************************************************	
 
//---------------------------------------------------------	
  
   
 
    //Retorna o resultado final do aluno
   
  //$sResultadoFinal = ResultadoFinal( $ed60_i_codigo, $ed60_i_aluno, $ed60_i_turma, trim( $ed60_c_situacao ), trim( $ed60_c_concluida), $ensino );
  //$pdf->setfont( 'arial', 'b', 7 );  
  //$pdf->cell( 190, 4, "Resultado Final : ". $sResultadoFinal, 1, 1, "L", 1 );
  //$pdf->cell( 190, 4, "",                                     0, 1, "C", 0 );


  //$final = $pdf->getY();
  //$pdf->setY( $final + 5 );
  //$pdf->cell( 25, 4, $data_extenso, 0, 1, "L", 0 );

  $sCampos             = "case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as regente";
  $sSqlRegenteConselho = $clregenteconselho->sql_query( "", $sCampos, "", " ed235_i_turma = {$ed57_i_codigo}" );
  $result5             = $clregenteconselho->sql_record( $sSqlRegenteConselho );

  if ($clregenteconselho->numrows > 0) {
    db_fieldsmemory( $result5, 0 );
  } else {
    $regente = "";
  }

  if ($pdf->getY() >= $pdf->h - 30 ) {
    $pdf->Addpage('P');
  }

  $pdf->ln(15);  
  /**
   * Variáveis para controle da posição X das opções possíveis de serem impressas
   */
  $iPosicaoXProfessor = 10;
  $iPosicaoXAdicional = 10;
  $iPosicaoXDiretor   = 10;

  /**
   * Variáveis para controle da posição Y das opções possíveis de serem impressas
   */
  $iPosicaoYDiretor   = $pdf->GetY();
  $iPosicaoYProfessor = $pdf->GetY();

  /**
   * Variáveis para controle de exibição da assinatura adicional e do professor
   */
  $lExibirAdicional = false;
  $lExibirAdicional2 = false;
  $lExibirProfessor = false;
  $pdf->ln(2);
 $pdf->cell(2, 3,'' , 0, 0, "L", 0 ); 
  if( $iAssinaturaAdicional3 <> null )
  {	  
     $pdf->cell( 70, 3,'________________________________________' , 0, 0, "L", 0 );
  }else{
	 $pdf->cell( 58, 3,'' , 0, 0, "L", 0 ); 
  }
  $pdf->cell( 1, 3,'' , 0, 0, "L", 0 ); 
  if( $iAssinaturaAdicional2 <> null )
  {	  
     $pdf->cell( 80, 3,'______________________________________' , 0, 0, "L", 0 );
  }else{
	 $pdf->cell( 56, 3,'' , 0, 0, "L", 0 ); 
  }
  $pdf->cell( 10, 3,'' , 0, 0, "L", 0 ); 
  if($iAssinaturaAdicional <> null)
  {	  
     $pdf->cell( 10, 3,'__________________________________' , 0, 1, "L", 0 );
  }else{
	 $pdf->cell( 57, 3,''    , 0, 1, "L", 0 ); 
  }


    $pdf->cell( 5, 3,'' , 0, 0, '', 0 );
  if( $iAssinaturaAdicional3 <> null )
  {	  
     $pdf->cell( 65, 3,utf8_decode($iAssinaturaAdicional3) , 0, 0, "L", 0 );
  }
  else{
	 $pdf->cell( 58, 3,'' , 0, 0, "L", 0 ); 
  }
  if( $iAssinaturaAdicional2 <> null )
  {	  
     $pdf->cell( 57, 3,utf8_decode($iAssinaturaAdicional2) , 0, 0, "L", 0 );
  }else{
	 $pdf->cell( 57, 3,'' , 0, 0, "L", 0 ); 
  }
  $pdf->cell( 3, 3,'' , 0, 0, '', 0 );
  if($iAssinaturaAdicional <> null)
  {	  
     $pdf->cell( 64, 3,utf8_decode($iAssinaturaAdicional)    , 0, 1, "L", 0 );
  }else{
	 $pdf->cell( 64, 3,''    , 0, 1, "L", 0 ); 
  }
  
  $pdf->cell( 15, 3,'' , 0, 0, '', 0 );
  if( $iAssinaturaAdicional3 <> null )
  {	  
     $pdf->cell( 58, 3,'SECRETÁRIO DE ESCOLA', 0, 0, "L", 0 );
  }else{
	 $pdf->cell( 58, 3,'', 0, 0, "L", 0 ); 
  }	 
  $pdf->cell( 5, 3,'' , 0, 0, '', 0 );
  if( $iAssinaturaAdicional2 <> null )
  {	  
     $pdf->cell( 57, 3,'SUPERVISOR ESCOLAR', 0, 0, "L", 0 );
  }else{
	 $pdf->cell( 57, 3,'', 0, 0, "L", 0 ); 
  }
  $pdf->cell( 20, 3,'' , 0, 0, '', 0 );
  if( $iAssinaturaAdicional <> null )
  {	  
     $pdf->cell( 80, 3,'DIRETOR', 0, 1, "L", 0 );
  }else{
	 $pdf->cell( 64, 3,'', 0, 1, "L", 0 ); 
  }	 

  
  
  db_fieldsmemory(
  db_query("
			select
			ed284_i_rhpessoal as matriculasec
			from
			rechumanopessoal
			left join rhpessoal on rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal 
			where
			rh01_numcgm = ".$matrisec
		  ),
			   0);

  
  db_fieldsmemory(
  db_query("
			select
			ed284_i_rhpessoal as matriculasup 
			from
			rechumanopessoal
			left join rhpessoal on rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal 
			where
			rh01_numcgm = ".$matrisup
		  ),
			  0);
  db_fieldsmemory(
  db_query("
			select
			ed284_i_rhpessoal as matriculadir
			from
			rechumanopessoal
			left join rhpessoal on rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal 
			where
			rh01_numcgm = ".$matridir
		  ),
		      0);
  
    $pdf->cell( 25, 3,'' , 0, 0, '', 0 );
  if( $iAssinaturaAdicional3 <> null )
  {	  
     $pdf->cell( 58, 3,'Matrícula: '.substr($matriculasec,2,15) , 0, 0, "L", 0 );
  }else{
	 $pdf->cell( 58, 3,'', 0, 0, "L", 0 ); 
  }	 
  if( $iAssinaturaAdicional2 <> null )
  {	  
     $pdf->cell( 57, 3,'Matrícula: '.substr($matriculasup,2,15) , 0, 0, "L", 0 );
  }else{
	 $pdf->cell( 57, 3,'' , 0, 0, "L", 0 ); 
  }	 
  $pdf->cell( 5, 3,'' , 0, 0, '', 0 );
  if( $iAssinaturaAdicional <> null )
  {	  
     $pdf->cell( 64, 3,'Matrícula: '.substr($matriculadir,2,15) , 0, 1, "L", 0 );
  }else{
	 $pdf->cell( 64, 3,'' , 0, 1, "L", 0 ); 
  }


}

//die("Conta");
$pdf->Output();


function resultadoprimeiro($escola,$etapa,$turma,$aluno){
$sql = "
		select 
		ed74_c_resultadofinal as resultfinal
		from 
		turma
		inner join escola              on escola.ed18_i_codigo              = turma.ed57_i_escola    
		inner join calendario          on calendario.ed52_i_codigo          = turma.ed57_i_calendario
		inner join base                on base.ed31_i_codigo                = turma.ed57_i_base      
		inner join cursoedu            on cursoedu.ed29_i_codigo            = base.ed31_i_curso      
		inner join ensino              on ensino.ed10_i_codigo              = cursoedu.ed29_i_ensino 
		inner join turmaserieregimemat on turmaserieregimemat.ed220_i_turma = turma.ed57_i_codigo    
		inner join serieregimemat      on serieregimemat.ed223_i_codigo     = turmaserieregimemat.ed220_i_serieregimemat
		inner join serie               on serie.ed11_i_codigo               = serieregimemat.ed223_i_serie
		inner join matricula           on matricula.ed60_i_turma            = turma.ed57_i_codigo    
		inner join aluno               on aluno.ed47_i_codigo               = matricula.ed60_i_aluno 
		inner join regencia            on  regencia.ed59_i_turma            = turma.ed57_i_codigo    
		inner join diario              on  diario.ed95_i_escola             = escola.ed18_i_codigo   
									   and diario.ed95_i_calendario         = calendario.ed52_i_codigo   
									   and diario.ed95_i_aluno              = aluno.ed47_i_codigo    
									   and diario.ed95_i_serie              = serie.ed11_i_codigo    
									   and diario.ed95_i_regencia           = regencia.ed59_i_codigo 
		inner join diariofinal         on  diariofinal.ed74_i_diario        = diario.ed95_i_codigo   
		Where
		escola.ed18_i_codigo = ".$escola."
		and
		ed11_i_codigo        = ".$etapa."
		and
		ed57_i_codigo        = ".$turma."
		and
		ed47_i_codigo        = ".$aluno."
       ";                 
		$rsfinal = db_query($sql);
        db_fieldsmemory($rsfinal,0);
		return $resultfinal;
}

?>