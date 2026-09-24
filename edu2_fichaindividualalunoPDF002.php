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

require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("fpdf151educacao/pdfwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("libs/db_libparagrafo.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("model/educacao/ArredondamentoNota.model.php"));
require_once(modification("model/educacao/Turma.model.php"));
require_once './vendor/autoload.php';
$phpWord = new \PhpOffice\PhpWord\PhpWord();
$section = $phpWord->addSection();

$fontStyle = new \PhpOffice\PhpWord\Style\Font();
$fontStyle->setBold(false);
$fontStyle->setName('Arial');
$fontStyle->setSize(7);

$fontStyle1 = new \PhpOffice\PhpWord\Style\Font();
$fontStyle1->setBold(false);
$fontStyle1->setName('Arial');
$fontStyle1->setSize(6);

$paraStyleEsquerda = array ('align' => 'left');
$paraStyleDireta   = array ('align' => 'left');
$paraStyleCentro   = array ('align' => 'center');

$headert = $section->createHeader();
$table   = $headert->addTable();
$table->addRow();
$table->addCell()->addImage('logovoltaeducacao.jpg',array('width'  => 480,'height' => 50,'align'  => 'right'));

$combordas = array(''=>'','borderSize'=>6, 'cellMarginTop'=>100);

$bordasexternas = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                        'borderLeftColor'=>''  ,'borderLeftSize'=>6,
                        'borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	    'borderBottomColor'=>'','borderBottomSize'=>6,
				 	    'cellMarginTop'=>100,
					   );

$sembordas = array('cellMarginTop'=>100);

$sembordasabaixo = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                         'borderLeftColor'=>''  ,'borderLeftSize'=>6,
                         'borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	     'cellMarginTop'=>100,
					    );

$sembordasacima = array('borderLeftColor'=>''  ,'borderLeftSize'=>6,
                        'borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	    'borderBottomColor'=>'','borderBottomSize'=>6,
				 	    'cellMarginTop'=>100,
					   );

$esquerdaabaixo = array('borderLeftColor'=>''  ,'borderLeftSize'=>6,
				 	   'borderBottomColor'=>'','borderBottomSize'=>6,
				 	   'cellMarginTop'=>100,
					   );

$abaixo = array('borderBottomColor'=>'','borderBottomSize'=>6,
				'cellMarginTop'=>100,
			   );

$direitaabaixo  = array('borderRightColor'=>'' ,'borderRightSize'=>6, 
				 	    'borderBottomColor'=>'','borderBottomSize'=>6,
				 	    'cellMarginTop'=>100,
					   );


$resfinal = 0;
$aprovacao = true;
$imp2024 = false;

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

function retornaNaturalidade($ed47_i_censomunicnat){
  $sql = pg_query("SELECT ed261_c_nome FROM censomunic WHERE ed261_i_codigo = {$ed47_i_censomunicnat}");
  $resultado = pg_fetch_all($sql);
  $nome = strtolower($resultado[0]["ed261_c_nome"]);
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

function retornaFaltas($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma){  
  $sql =  "SELECT 
		   ed59_i_disciplina 
		   from 
		   diario 
		   inner join aluno              on ed47_i_codigo  = ed95_i_aluno 
		   inner join matricula          on ed60_i_aluno   = ed47_i_codigo 
		   inner join matriculaserie     on ed60_i_codigo  = ed221_i_matricula 
		   inner join regencia           on ed59_i_codigo  = ed95_i_regencia and ed59_i_serie = ed221_i_serie 
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
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/faltas.sql","w+");
//fwrite($arq, $sql);
//fwrite($arq,"\r\n");
//fclose($arq); 		

//  $resultado = db_query($sql);
//  return $result2;
return $sql;
}

function buscafalta($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma,$ed59_i_disciplina){
$sql = "	
		SELECT 
		distinct on (ed95_i_codigo,ed09_c_abrev)
		ed232_c_descr,
		ed95_i_aluno,
		ed09_c_abrev,	
		case when ed72_i_numfaltas is null then 0 else ed72_i_numfaltas end as ed72_i_numfaltas
		FROM 
		procavaliacao           
		inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao           
		inner join formaavaliacao   on formaavaliacao.ed37_i_codigo   = procavaliacao.ed41_i_formaavaliacao          
		inner join turma            on ed57_i_escola                  = ed37_i_escola and ed57_i_codigo = ".$ed60_i_turma."
		inner join diarioavaliacao  on ed72_i_procavaliacao           = ed41_i_codigo 
		inner join diario           on ed95_i_codigo                  = ed72_i_diario
		inner join regencia         on ed59_i_codigo                  = ed95_i_regencia
        inner join disciplina       on ed12_i_codigo                  = ed59_i_disciplina
        inner join caddisciplina    on ed232_i_codigo                 = ed12_i_caddisciplina		
		where 
		ed95_i_aluno = {$ed60_i_aluno}
		and
        ed57_i_codigo = {$ed60_i_turma}
		and
		ed59_i_disciplina = {$ed59_i_disciplina}
        and 
		ed59_d_dataatualiz >= '2024-01-01'
        and 
		ed37_c_descr = 'NOTA'
		
		";

//$arq = fopen("/dados/www/homologacao.epdvr.com.br/faltas.sql","w+");
//fwrite($arq, $sql);
//fwrite($arq,"\r\n");
//fclose($arq); 		
		
//  $sql1 = pg_query($sql);
//  $rsfaltas = pg_fetch_all($sql1);
  //$rsfaltas = db_query($sql);
  //return $rsfaltas;
  return $sql;	
}

function retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma){  
  $sql =  "SELECT 
		   diario.*, 
		   ed59_i_codigo 
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
		   and ed59_i_turma = {$ed60_i_turma} 
		   order by ed59_i_ordenacao";
  $result = pg_query($sql);				   
  $resultado = pg_fetch_all($result);
  return $resultado[0]["ed95_i_codigo"];
}

function retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma){  
  $sql =  "SELECT 
		   diario.*, 
		   ed59_i_codigo, 
		   ed59_i_disciplina 
		   from 
		   diario 
		   inner join aluno              on ed47_i_codigo  = ed95_i_aluno 
		   inner join matricula          on ed60_i_aluno   = ed47_i_codigo 
		   inner join matriculaserie     on ed60_i_codigo  = ed221_i_matricula 
		   inner join regencia           on ed59_i_codigo  = ed95_i_regencia and ed59_i_serie = ed221_i_serie 
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
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/retornaCodDiario2.sql","w+");
//fwrite($arq, $sql);
//fwrite($arq,"\r\n");
//fclose($arq); 		

  $result = pg_query($sql);
  $resultado = pg_fetch_all($result);
  return $resultado;
}



function dadosDiario($codiario){
  $sql = pg_query("SELECT ed72_i_codigo as codigo, ed72_i_procavaliacao as codigo_elemento, ed72_i_numfaltas as numero_faltas, ed80_i_codigo as codigo_faltas_abonadas, ed72_i_valornota as valor_nota, ed72_i_valornota as valor_nota_real, ed72_c_valorconceito as valor_conceito, ed72_t_parecer as parecer, ed72_c_aprovmin as minimo, ed72_c_amparo as amparo, ed41_i_sequencia as sequencia, trim(ed93_t_parecer) as parecerpadronizado, ed72_i_escola as escola, ed72_c_tipo as origem, ed72_c_convertido as convertido, (select ed39_i_sequencia from conceito where conceito.ed39_i_formaavaliacao = ed41_i_formaavaliacao and conceito.ed39_c_conceito = ed72_c_valorconceito) as ordem_conceito, 'A' as tipo_elemento, ed72_t_obs as observacao, false as em_recuperacao from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo where ed72_i_diario = {$codiario} order by sequencia");

//db_criatabela($sql);
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function dadosDiario2($codiario){
  $sql = pg_query("SELECT 
                   ed72_i_codigo as codigo, 
				   ed72_i_procavaliacao as codigo_elemento, 
				   case when ed72_i_numfaltas is null then 0 else ed72_i_numfaltas end as numero_faltas,
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
				   (select ed39_i_sequencia from conceito where conceito.ed39_i_formaavaliacao = ed41_i_formaavaliacao and conceito.ed39_c_conceito = ed72_c_valorconceito) as ordem_conceito, 
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
				   
//db_criatabela($sql);
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function dadosDiario3($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma,$ed59_i_disciplina){
  $sql = "SELECT 
                   ed72_i_codigo as codigo, 
				   ed72_i_procavaliacao as codigo_elemento, 
				   case when ed72_i_numfaltas is null then 0 else ed72_i_numfaltas end as numero_faltas,
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
				   (select ed39_i_sequencia from conceito where conceito.ed39_i_formaavaliacao = ed41_i_formaavaliacao and conceito.ed39_c_conceito = ed72_c_valorconceito) as ordem_conceito, 
				   'A' as tipo_elemento, 
				   ed72_t_obs as observacao, 
				   false as em_recuperacao 
				   from 
				   diarioavaliacao 
				   inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao 
				   inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao           
				   inner join formaavaliacao   on formaavaliacao.ed37_i_codigo   = procavaliacao.ed41_i_formaavaliacao  
				   inner join turma            on ed57_i_escola                  = ed37_i_escola
				   inner join diario           on ed95_i_codigo                  = ed72_i_diario        and ed95_i_escola = ed72_i_escola
				   inner join regencia         on ed59_i_codigo                  = ed95_i_regencia      
				   inner join disciplina       on ed12_i_codigo                  = ed59_i_disciplina
				   inner join caddisciplina    on ed232_i_codigo                 = ed12_i_caddisciplina		
				   left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo 
				   left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo 
				   where 
				   ed95_i_aluno = {$ed60_i_aluno}
				   and
				   ed57_i_codigo = {$ed60_i_turma}
				   and
				   ed59_i_disciplina = {$ed59_i_disciplina}
				   and 
				   ed37_c_descr = 'NOTA'
         ";
  $sql1 = pg_query($sql);
  $resultado = pg_fetch_all($sql1);
//  echo "<pre>";
//  print_r($resultado);
//  echo "</pre>";
  
  return $resultado;
}
function dadosAulas($codregencia){
  $sql = pg_query("SELECT 
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
				   ed78_i_regencia = {$codregencia} 
				   and 
				   ed09_c_somach = 'S' 
				   ORDER BY ed78_i_procavaliacao");
//if($codregencia==12088)
//{	  
//   db_criatabela($sql);
//}

  $resultado = pg_fetch_all($sql);

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
		ed95_i_codigo         as diario,          
		ed52_d_resultfinal    as datafinal,
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

//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq, 'estou aqui');
//fwrite($arq,"\r\n");
//fclose($arq); 		
	   
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
//db_criatabela($rsfimletivo);
        db_fieldsmemory($rsfimletivo,0);	
		
		if( !$ed52_d_resultfinal)
		{
			$ed52_d_resultfinal = date('Y').'-12-15';
		}	
		
		return $ed52_d_resultfinal;
}

function aluno()
{
	return $ed60_i_aluno;
}

$resultedu           = eduparametros(db_getsession("DB_coddepto"));
$permitenotaembranco = VerParametroNota(db_getsession("DB_coddepto"));
$escola              = db_getsession("DB_coddepto");
$oGet                = db_utils::postMemory( $_GET );
$sObs                = base64_decode($sObs);

$clmatricula       = new cl_matricula;
$claluno           = new cl_aluno;
$clturma           = new cl_turma;
$clEscola          = new cl_escola();
$cldiarioavaliacao = new cl_diarioavaliacao;
$clregenteconselho = new cl_regenteconselho;
$clrotulo          = new rotulocampo;
$oDaoEscolaDiretor = new cl_escoladiretor();
$oDaoTipoSanguineo = new cl_tiposanguineo();

$claluno->rotulo->label();
$clrotulo->label("ed76_i_escola");
$clrotulo->label("ed76_d_data");

$sSqlDadosEscola = $clEscola->sql_query( "","ed261_c_nome as mun_escola","","ed18_i_codigo = {$escola}" );
$rsDadosEscola   = db_query($sSqlDadosEscola);
$oDadosEscola    = db_utils::fieldsMemory($rsDadosEscola,0);
$mun_escola      = $oDadosEscola->mun_escola;

$sCamposDiretor    = " 'DIRETOR' as funcao, ";
$sCamposDiretor   .= "          case when ed20_i_tiposervidor = 1 then ";
$sCamposDiretor   .= "                  cgmrh.z01_nome ";
$sCamposDiretor   .= "               else cgmcgm.z01_nome ";
$sCamposDiretor   .= "            end as nome,";
$sCamposDiretor   .= " ed83_c_descr||' n°: '||ed05_c_numero::varchar as descricao,'D' as tipo";
$sWhereDiretor     = " ed254_i_escola = ".$escola." AND ed254_c_tipo = 'A' AND ed01_i_funcaoadmin = 2 limit 1 ";
$sSqlDiretor       = $oDaoEscolaDiretor->sql_query_resultadofinal( "", $sCamposDiretor, "", $sWhereDiretor );
$rsDiretor         = $oDaoEscolaDiretor->sql_record( $sSqlDiretor );
$iLinhasDiretor    = $oDaoEscolaDiretor->numrows;

if ($iLinhasDiretor > 0) {
  db_fieldsmemory( $result, 0 );
  $nome = trim( db_utils::fieldsmemory( $rsDiretor, 0 )->nome );
} else {
  $nome= "";
}

$camp  = " ed60_d_datasaida as datasaida, ";
$camp .= "  case ";
$camp .= "   when ed60_c_situacao = 'TRANSFERIDO REDE' then ";
$camp .= "    (select escoladestino.ed18_c_nome from transfescolarede ";
$camp .= "      inner join atestvaga  on  atestvaga.ed102_i_codigo = transfescolarede.ed103_i_atestvaga ";
$camp .= "      inner join escola  as escoladestino on  escoladestino.ed18_i_codigo = atestvaga.ed102_i_escola ";
$camp .= "     where ed103_i_matricula = ed60_i_codigo order by ed103_d_data desc limit 1) ";
$camp .= "   when ed60_c_situacao = 'TRANSFERIDO FORA' then ";
$camp .= "    (select escolaproc1.ed82_c_nome from transfescolafora ";
$camp .= "     inner join escolaproc as escolaproc1 on  escolaproc1.ed82_i_codigo = transfescolafora.ed104_i_escoladestino ";
$camp .= "     where ed104_i_matricula = ed60_i_codigo order by ed104_d_data desc limit 1) ";
$camp .= "   else null ";
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
$camp .= "   aluno.*     ";

$sSqlMatricula = $clmatricula->sql_query( "", $camp, "ed60_d_datamatricula desc", " ed60_i_codigo in ({$alunos})" );

$result1       = $clmatricula->sql_record( $sSqlMatricula );

if ( $clmatricula->numrows == 0 ) {
  db_redireciona( "db_erros.php?fechar=true&db_erro=Nenhum registro encontrado." );
}

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

//$tudi = pg_fetch_all($result1);
//testa($tudi);
//die("Confere");


for ( $ww = 0; $ww < $clmatricula->numrows; $ww++ ) {// inicio da impressão da ficha **************************************************************

  db_fieldsmemory($result1,$ww);
  $xnacionalidade = retornaNacionalidade($ed47_i_nacion);
  $xnaturalidade = retornaNaturalidade($ed47_i_censomunicnat);
  $xfotoaluno = trim($ed47_c_foto);


  if(substr($ed52_c_descr,0,13) == "ANOS INICIAIS" && $ed11_c_descr == "2º ANO"){
    $indice = 0;
    $dadosgrade = array();
    $xcodiario  = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  
    $codreg     = buscaCodigoRegencia2($ed57_i_codigo);   
    $alunoFaltas = $ed60_i_aluno;
    foreach ($xcodiario as $linha) {    
      $xcodiario = $linha["ed95_i_codigo"];
      $xdiario = dadosDiario2($xcodiario);    
      $xaulas = dadosAulas($linha["ed95_i_regencia"]);    
      
      if($xaulas){
        $dadosgrade[$indice]["xdiario"] = $xdiario;
        $dadosgrade[$indice]["xaulas"] = $xaulas;    
        $indice++;      
      }    
    }


    $indice2 = 0;
    $dadosgrade2 = array();
    $xcodiario2 = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  
    $codreg2 = buscaCodigoRegencia2($ed57_i_codigo);       
    
    foreach ($xcodiario2 as $linha) {
      $xcodiario2 = $linha["ed95_i_codigo"];
      $xdiario2 = dadosDiario2($xcodiario2);
      $xdiario2["disciplina"] = retornaNomeDisciplina($linha["ed59_i_disciplina"]);      
      $dadosgrade2[$indice2] = $xdiario2;      
      $indice2++;
    }
  }elseif(substr($ed52_c_descr,0,13) == "ANOS INICIAIS" && ($ed11_c_descr == "3º ANO" || $ed11_c_descr == "4º ANO" || $ed11_c_descr == "5º ANO")){
//********************************************************************************************************************************************************	  
    $indice = 0;
    $dadosgrade = array();
    $xcodiario = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  
	
    $codreg = buscaCodigoRegencia2($ed57_i_codigo);   
    $alunoFaltas = $ed60_i_aluno;
    foreach ($xcodiario as $linha) {    
      $xcodiario = $linha["ed95_i_codigo"];
      $xdiario = dadosDiario2($xcodiario);    // busca dados do diario
      $xaulas  = dadosAulas($linha["ed95_i_regencia"]);    
      
      if($xaulas){
        $dadosgrade[$indice]["xdiario"] = $xdiario;
        $dadosgrade[$indice]["xaulas"]  = $xaulas;    
        $indice++;      
      }    
    }
	
    $indice2 = 0;
    $dadosgrade2 = array();
    $xcodiario2 = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  
    $codreg2 = buscaCodigoRegencia2($ed57_i_codigo);       
    
    foreach ($xcodiario2 as $linha) {
      $xcodiario2 = $linha["ed95_i_codigo"];
      $xdiario2 = dadosDiario2($xcodiario2);
      $xdiario2["disciplina"] = retornaNomeDisciplina($linha["ed59_i_disciplina"]);      
      $dadosgrade2[$indice2] = $xdiario2;      
      $indice2++;
    }    //------------------------------------------
  }elseif($ed52_c_descr == "EN FUN ANOS FINAIS" && ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO")){
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
        $dadosgrade[$indice]["xaulas"] = $xaulas;    
		
		
        $indice++;      
      }    
    }


    $indice2 = 0;
    $dadosgrade2 = array();
    $xcodiario2 = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  
    $codreg2 = buscaCodigoRegencia2($ed57_i_codigo);       
    
    foreach ($xcodiario2 as $linha) {
      $xcodiario2 = $linha["ed95_i_codigo"];
      $xdiario2 = dadosDiario2($xcodiario2);
      $xdiario2["disciplina"] = retornaNomeDisciplina($linha["ed59_i_disciplina"]);      
      $dadosgrade2[$indice2] = $xdiario2;      
      $indice2++;
    }

    //testa($dadosgrade); die("Notas");      
    
  }elseif(substr($ed52_c_descr,0,11) == "ANOS FINAIS" && ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO")){
    $indice = 0;
    $dadosgrade = array();
    $xcodiario = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  // aqui também, porque não esta pegando faltas das 2 ultimas materias
    $alunoFaltas = $ed60_i_aluno;
    $codreg = buscaCodigoRegencia2($ed57_i_codigo);   
    foreach ($xcodiario as $linha) {    
      $xcodiario1 = $linha["ed95_i_codigo"];
      $xdiario   = dadosDiario2($xcodiario1);                 
	  
      $xaulas    = dadosAulas($linha["ed95_i_regencia"]);     
      if($xaulas){
        $dadosgrade[$indice]["xdiario"] = $xdiario;
        $dadosgrade[$indice]["xaulas"] = $xaulas;    
        $indice++;      
      }    
    }






    $indice2 = 0;
    $dadosgrade2 = array();
    $xcodiario2  = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  
    $codreg2     = buscaCodigoRegencia2($ed57_i_codigo);       
    
    foreach ($xcodiario2 as $linha) {
      $xcodiario2 = $linha["ed95_i_codigo"];
      $xdiario2 = dadosDiario2($xcodiario2);
      $xdiario2["disciplina"] = retornaNomeDisciplina($linha["ed59_i_disciplina"]);      
      $dadosgrade2[$indice2] = $xdiario2;      
      $indice2++;
    }
  
  }elseif(substr($ed52_c_descr,0,10) == "EJA FINAIS"){ //******************************************************************************************************************
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
        $dadosgrade[$indice]["xaulas"] = $xaulas;    
        $indice++;      
      }    
    }


    $indice2 = 0;
    $dadosgrade2 = array();
    $xcodiario2 = retornaCodDiario2($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);  
    $codreg2 = buscaCodigoRegencia2($ed57_i_codigo);       
    
    foreach ($xcodiario2 as $linha) {
      $xcodiario2 = $linha["ed95_i_codigo"];
      $xdiario2 = dadosDiario2($xcodiario2);
      $xdiario2["disciplina"] = retornaNomeDisciplina($linha["ed59_i_disciplina"]);      
      $dadosgrade2[$indice2] = $xdiario2;      
      $indice2++;
    }

  } //********************************************************************************************************************************************************
  
  
  
  

  $data = date( "Y-m-d",DB_getsession("DB_datausu") );
  $dia  = date( "d" );
  $mes  = date( "m" );
  $ano  = date( "Y" );
  $linha_impressa = "";
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
  $head1        = "FICHA INDIVIDUAL";
  $head2        = "{$ed47_i_codigo} - {$ed47_v_nome}";
  //*******************************************************************************************************************************************************
  $sql = 'select 
          ed18_c_nome,
		  ed18_c_email,
		  db74_descricao as rua
		  from
		  escola
		  inner join cadenderrua on db74_sequemcial = d18_i_rua
		  where
		  ed18_i_codigo = '.$escola;
  $resultado = db_query($sql);
  db_fieldsmemory($resultado,0);
  $phpWord->addParagraphStyle('tStyle', array('align' => 'center', 'spaceAfter' =>0));
  $styleTable = array('borderTopColor'=>'','borderTopSize'=>6,
                      'borderLeftColor'=>'','borderLeftSize'=>6,
                      'borderRightColor'=>'','borderRightSize'=>6, 'cellMarginTop'=>100);
  $phpWord->addTableStyle('myTable', $styleTable);
  $table = $section->addTable('myTable');
  $table->addRow(25);
// até que enfim consegui definir o espaçamento do parágrafo na linha abaixo
  $phpWord->setDefaultParagraphStyle(
		array(
			'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
			'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
			'spacing' => 120,
			'lineHeight' => 1
		)
   );  
// spacing, acima define a distancia entre a borda superior da tabela e o texto
//  $phpWord->addParagraphStyle('pStyle', array('align' => 'center', 'spaceAfter' => 100));	
//  $table->addCell(13000)->addText('ESTADO DO RIO DE JANEIRO',$fontStyle);
//  $table->addCell(7000)->addText(' FICHA INDIVIDUAL',$fontStyle);
//  $table->addRow(25);
//  $table->addCell(13000)->addText('PREFEITURA MUNICIPAL DE VOLTA REDONDA',$fontStyle);
  $table->addCell(15000)->addText('VOLTA REDONDA - RJ',$fontStyle);
  $table->addCell(7000)->addText($ed47_i_codigo.'-'.$ed47_v_nome,$fontStyle);

  $styleTable1 = array('borderLeftColor'=>'','borderLeftSize'=>6,
                       'borderRightColor'=>'','borderRightSize'=>6, 
					   'borderBottomColor'=>'','borderBottomSize'=>6,
					   'cellMarginTop'=>100);
  $phpWord->addTableStyle('myTable2', $styleTable1);
  $table = $section->addTable('myTable2');
  $table->addRow(25);

//  $table->addRow(25);
  if(substr($ed52_c_descr,0,13) == "ANOS INICIAIS")
  {	  
       $table->addCell(10000)->addText($ed18_c_nome,$fontStyle);
       $table->addCell(10000)->addText($email,$fontStyle);
       $myTextElement  = $section->addText('');
  }else{
       $table->addCell(20000)->addText($ed18_c_nome,$fontStyle);	  
       $table->addRow(25);
       $table->addCell(20000)->addText($email,$fontStyle);
       $myTextElement  = $section->addText('');
  } 
  $styleTable2 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);
  $styleFirstRow = array('bgColor'=>' #F0F0F0');
  $phpWord->addTableStyle('myTable3', $styleTable2, $styleFirstRow);
  $table = $section->addTable('myTable3');
  $table->addRow(25);
  $fontStyle->setBold(true);
  $table->addCell(20000)->addText('DADOS PESSOAIS',$fontStyle);
  $fontStyle->setBold(false);
  
 

  switch ($ed47_i_estciv) {
    case 1:
      $ed47_i_estciv = 'SOLTEIRO';
      break;

    case 2:
      $ed47_i_estciv = 'CASADO';
      break;

    case 3:
      $ed47_i_estciv = 'VIÚVO';
      break;

    case 4:
      $ed47_i_estciv = 'DIVORCIADO';
      break;

    default:
      $ed47_i_estciv = 'NÃO INFORMADO';
  }  
  $sexo= $ed47_v_sexo == "M" ? "MASCULINO" : "FEMININO";
  
// Inicia a impressão dos dados pessoais
  $styleTable2 = array('borderLeftColor'=>'','borderLeftSize'=>6,
                       'borderRightColor'=>'','borderRightSize'=>6, 
					   'cellMarginTop'=>100);

  $phpWord->addTableStyle('myTable33', $styleTable2);
  $table = $section->addTable('myTable33');
  $table->addRow(25);
  $fontStyle->setBold(true);
  $table->addCell(3300)->addText('Nome:',$fontStyle);
  $table->addCell(16700)->addText($ed47_v_nome,$fontStyle);
  $fontStyle->setBold(false);

  $styleTable4 = array('borderLeftColor'=>'','borderLeftSize'=>6,
                       'borderRightColor'=>'','borderRightSize'=>6, 
					   'borderBottomColor'=>'','borderBottomSize'=>6,
					   'cellMarginTop'=>100);
  
  $phpWord->addTableStyle('myTable4', $styleTable4);
  
  $table = $section->addTable('myTable4');
  $table->addRow(25);
  
  $table->addCell(3000)->addText('Codigo:',$fontStyle);
  $table->addCell(12000)->addText($ed47_i_codigo.'                           Código INEP / ID Aluno:'.$ed47_c_codigoinep,$fontStyle);
  $table->addCell(5000)->addText('N° NIS: '.$ed47_c_nis,$fontStyle);
  $table->addRow(25);
  $table->addCell(3000)->addText('Nascimento:',$fontStyle);
  $table->addCell(12000)->addText(db_formatar( $ed47_d_nasc, 'd' ).'                    Sexo: '.$sexo.$esp1,$fontStyle);
  $table->addCell(5000)->addText('Estado civil: '.$ed47_i_estciv,$fontStyle);
  $table->addRow(25); // não pode abrir a linha sem adicionar celulas
  $table->addCell(3000)->addText('Tipo Sanguíneo:',$fontStyle);
  $tipo_sangue = $ed47_tiposanguineo == "" ? "NÃO INFORMADO" : $aTiposSanguineos[$ed47_tiposanguineo];
  $table->addCell(12000)->addText($tipo_sangue,$fontStyle);
  $table->addCell(5000)->addText('Raça/Cor: '.$ed47_c_raca,$fontStyle);
  
  $res = db_query("SELECT ed261_i_censouf FROM censomunic WHERE ed261_i_codigo = {$ed47_i_censomunicnat}");
  db_fieldsmemory($res,0);
  $ufcenso= 'select 
	         ed260_c_sigla 
	         from 
	         censouf  
	         where
	         ed260_i_codigo = '.$ed261_i_censouf;
  $rsuf = db_query($ufcenso);
  db_fieldsmemory($rsuf,0);
  
  $table->addRow(25);
  $table->addCell(3000)->addText('Filiação:',$fontStyle);
  $table->addCell(12000)->addText('PAI E/OU MÃE',$fontStyle);
  $table->addCell(5000)->addText('Nacionalidade:'.$xnacionalidade,$fontStyle);

  $table->addRow(25);
  $table->addCell(3000)->addText(' ',$fontStyle);
  $table->addCell(12000)->addText($ed47_v_pai,$fontStyle);
  $table->addCell(5000)->addText('Naturalidade:'.$ed260_c_sigla,$fontStyle);

  $table->addRow(25);
  $table->addCell(3000)->addText(' ',$fontStyle);
  $table->addCell(12000)->addText($ed47_v_mae,$fontStyle);
  $table->addCell(5000)->addText('UF/Nascimento:'.$ed260_c_sigla,$fontStyle);

  $table->addRow(25);
  $table->addCell(3000)->addText('Responsavel:',$fontStyle);
  $table->addCell(12000)->addText($ed47_c_nomeresp,$fontStyle);
  $table->addCell(5000)->addText(' ',$fontStyle);

  $table->addRow(25);
  $table->addCell(3000)->addText('email:',$fontStyle);
  $table->addCell(12000)->addText($$ed47_c_emailresp,$fontStyle);
  $table->addCell(5000)->addText(' ',$fontStyle);
  
 // Fim dos dados pessoais
//***********************************************************************************************************************************************************/
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq, substr(ed52_c_descr,0,13).'--'.$ed11_c_descr);
//fwrite($arq,"\r\n");
//fclose($arq); 		

if(substr($ed52_c_descr,0,13) == "ANOS INICIAIS" && $ed11_c_descr == "2º ANO"){	// acrescentei 2024-----------------------------------------------------------
  $ed60_situacao = $ed60_c_situacao;
  
  $phpWord->addParagraphStyle('pStyle', array('align' => 'center', 'spaceAfter' => 100));	
  $styleTable5 = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                       'borderLeftColor'=>''  ,'borderLeftSize'=>6,
                       'borderRightColor'=>'' ,'borderRightSize'=>6, 
					   'borderBottomColor'=>'','borderBottomSize'=>6,
					   'cellMarginTop'=>100,
					   );
  
  $phpWord->addTableStyle('myTable5', $styleTable5);
  $myTextElement  = $section->addText(''); //insere uma linha em branco para separar as tabelas
  $table = $section->addTable('myTable5');
  $table->addRow(25);
  $table->addCell(20000)->addText('                                                                                  Ensino Fundamental - '.$ed11_c_descr,$fontStyle);
  $table->addRow(25);
  $table->addCell(20000)->addText('                                                     Etapa: '.$ed11_c_descr.'       Ano Letivo: '.$ed52_i_ano.'       Nome da Turma: '.$ed57_c_descr,$fontStyle);
  $myTextElement  = $section->addText('');
  
  $styleTable6 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);					   
  $phpWord->addTableStyle('myTable6', $styleTable6);
  $table = $section->addTable('myTable6');
  $table->addRow(25);
  $table->addCell(10750)->addText('         Apuração dos rendimentos          ',$fontStyle);
  $table->addCell(10)->addText(' ',$fontStyle);
  $table->addCell(5280)->addText('                Dias letivos                 ',$fontStyle);
  $table->addCell(1800)->addText('Faltas do aluno',$fontStyle);

  $styleTable7 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);					   
  //$bordas = array('borderColor'=>'','cellMarginTop'=>100);					   
  $bordas = array('cellMarginTop'=>100);					     
  $phpWord->addTableStyle('myTable7', $bordas);
  $table = $section->addTable('myTable7');
  $table->addRow(25);
  $table->addCell(3900,$combordas)->addText(' Componentes curriculares',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 1º TRI ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 2º TRI ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 3º TRI ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' MÉDIA  ',$fontStyle);
  resultado_final($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
  $x = 0;
  $faltas1=0;
  $indice = 0;
  $faltas1 = [];
  $linha2=[];
  $xindice1 = 0;
  $xconta1  = 1;
  $xconta   = 0;
  transferencia($ed47_i_codigo);
  fimanoletivo($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
  foreach ($dadosgrade as $linhona) {
     foreach ($linhona["xdiario"] as $linha){
        $linha2[$xconta] = $xconta1 . "ª Trimestre";
		if($ed60_situacao == 'TRANSFERIDO FORA' or $ed60_situacao == 'TRANSFERIDO REDE')
		{		
			if( $linhona["xdiario"][$xindice1]["numero_faltas"]==0 or $linhona["xdiario"][$xindice1]["numero_faltas"]==null or $linhona["xdiario"][$xindice1]["numero_faltas"]=="")
			{
				$faltas1[$xindice1] = '';
			}else{
				$faltas1[$xindice1] = $linhona["xdiario"][$xindice]["numero_faltas"];
			}
		}else{
			if( $linhona["xdiario"][$xindice1]["numero_faltas"]==0 or $linhona["xdiario"][$xindice1]["numero_faltas"]==null or $linhona["xdiario"][$xindice1]["numero_faltas"]=="")
			{
				$faltas1[$xindice1] = '';
			}else{
				$faltas1[$xindice1] = $linhona["xdiario"][$xindice1]["numero_faltas"];
			}
            
		}
		
        $xdl += $linhona["xaulas"][$xindice1]["ed78_i_aulasdadas"];
        $xfa += $linhona["xdiario"][$xindice1]["numero_faltas"];
		$impfre += $xfa+$xdl;
		$xconta++;
        $xconta1++;
        $xindice1++;
      }
	  if($ed60_situacao == 'TRANSFERIDO FORA' or $ed60_situacao == 'TRANSFERIDO REDE')
	  {			
  		 $xfreq = ""; 
      }else{
         $xfreq = ($xdl - $xfa) / $xdl * 100;
	     $xfreq = floor($xfreq);
	  }
	  
  }  
  $table->addCell(10,$combordas)->addText('',$fontStyle);
  $table->addCell(2846,$combordas)->addText(' 1º Trimestre  ',$fontStyle);
  $table->addCell(1438,$combordas)->addText('   ',$fontStyle);
  $table->addCell(1758,$combordas)->addText('        '.$faltas1[0],$fontStyle,$paraStyleDireta);
//***************************************************************************
  $indfalt = 1;
  foreach ($dadosgrade2 as $linha) {
      //$table = $section->addTable('myTable6');
      $table->addRow(25);
      $table->addCell(3900,$combordas)->addText($linha["disciplina"],$fontStyle);
	  
      $n1 = $linha[0]["valor_conceito"];
      $n2 = $linha[1]["valor_conceito"];
      $n3 = $linha[2]["valor_conceito"];
	  
      $table->addCell(1000,$combordas)->addText('  '.$n1,$fontStyle);
      $table->addCell(1000,$combordas)->addText('  '.$n2,$fontStyle);
      $table->addCell(1000,$combordas)->addText('  '.$n3,$fontStyle);
	  $dividir = 3;
	  
//      $notamedia = ($linha[0]["valor_nota"] + $linha[1]["valor_nota"] + $linha[2]["valor_nota"]) / $dividir;
	  $notamedia = '';

      if($resfinal>0){
		  if($ed60_situacao == 'TRANSFERIDO FORA' or $ed60_situacao == 'TRANSFERIDO REDE')
		  {			
			 
             $table->addCell(1000,$combordas)->addText(' ',$fontStyle);			 
		  }else{
	         $table->addCell(1000,$combordas)->addText('  '.$notamedia,$fontStyle); 
		  }
		  
	  }else{
		  $table->addCell(1000,$combordas)->addText('  '.$notamedia,$fontStyle); 
	  }	 
	  if( $indfalt == 1)
	  {	  
		  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(2846,$combordas)->addText(' 2º Trimestre  ',$fontStyle);
		  $table->addCell(1438,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(1758,$combordas)->addText('        '.$faltas1[1],$fontStyle,$paraStyleDireta);
	  }
	  elseif( $indfalt == 2 )
	  {
		  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(2846,$combordas)->addText(' 3º Trimestre  ',$fontStyle);
		  $table->addCell(1438,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(1758,$combordas)->addText('        '.$faltas1[2],$fontStyle,$paraStyleDireta);
	  }
	  elseif( $indfalt == 3 )
	  {
		  $totalFaltas2 = $faltas1[0]+$faltas1[1]+$faltas1[2];
		  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(2846,$combordas)->addText(' TOTAL  ',$fontStyle);
		  $table->addCell(1438,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(1758,$combordas)->addText('        '.$totalFaltas2,$fontStyle,$paraStyleDireta);
	  }
	  elseif( $indfalt == 4 )
	  {
		  if(date('Y-m-d') >= $ed52_d_resultfinal and substr($ed60_situacao,0,11) <> 'TRANSFERIDO' )
		  {
			  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
			  $table->addCell(2846,$esquerdaabaixo)->addText(' Frequencia % ',$fontStyle);
			  $table->addCell(1438,$abaixo)->addText(' ',$fontStyle);
			  $table->addCell(1758,$direitaabaixo)->addText('                '.$xfreq,$fontStyle);
		  }else{
			  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
			  $table->addCell(2846,$esquerdaabaixo)->addText(' Frequencia % ',$fontStyle);
			  $table->addCell(1438,$abaixo)->addText(' ',$fontStyle);
			  $table->addCell(1758,$direitaabaixo)->addText('   ',$fontStyle);
		  }
	  }	  
      $indfalt++;
  }
  $myTextElement->setFontStyle($fontStyle);
  fimanoletivo($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
  $datasaidaaluno = substr($ed60_d_datasaida,8,2).'/'.substr($ed60_d_datasaida,5,2).'/'.substr($ed60_d_datasaida,0,4);
  if(substr($ed60_situacao,0,11) <> 'TRANSFERIDO')
  {			
      if( $ed60_situacao <> 'MATRICULADO')
	  {
	       $myTextElement  = $section->addText(" ");
	       $myTextElement  = $section->addText($ed60_c_situacao." em: ".$datasaidaaluno);  
	  }else{
		   if(date('Y-m-d')<$ed52_d_resultfinal )
		   {
	            $myTextElement  = $section->addText(" ");
	            $myTextElement  = $section->addText("À vistas dos Resultados Obtidos, o aluno foi considerado : EM ANDAMENTO");  
		   }else{
	            $myTextElement  = $section->addText(" ");
	            $myTextElement  = $section->addText("À vistas dos Resultados Obtidos, o aluno foi considerado : ".$ed60_c_situacao);
		   }
	  }	   
  }else{
	  $myTextElement  = $section->addText(" ");
	  $myTextElement  = $section->addText("Aluno transferido em : " .$datasaidaaluno);  
  }
//***********************************************************************************************************************************************************/  
}elseif(substr($ed52_c_descr,0,13) == "ANOS INICIAIS" && ($ed11_c_descr == "3º ANO" || $ed11_c_descr == "4º ANO" || $ed11_c_descr == "5º ANO")){
  $ed60_situacao = $ed60_c_situacao;
  $phpWord->addParagraphStyle('pStyle', array('align' => 'center', 'spaceAfter' => 100));	
  $styleTable5 = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                       'borderLeftColor'=>''  ,'borderLeftSize'=>6,
                       'borderRightColor'=>'' ,'borderRightSize'=>6, 
					   'borderBottomColor'=>'','borderBottomSize'=>6,
					   'cellMarginTop'=>100,
					   );
  
  $phpWord->addTableStyle('myTable5', $styleTable5);
  $myTextElement  = $section->addText(''); //insere uma linha em branco para separar as tabelas
  $table = $section->addTable('myTable5');
  $table->addRow(25);
  $table->addCell(20000)->addText('                                                                                  Ensino Fundamental - '.$ed11_c_descr,$fontStyle);
  $table->addRow(25);
  $table->addCell(20000)->addText('                                                     Etapa: '.$ed11_c_descr.'       Ano Letivo: '.$ed52_i_ano.'       Nome da Turma: '.$ed57_c_descr,$fontStyle);
  $myTextElement  = $section->addText('');
  
  $styleTable6 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);					   
  $phpWord->addTableStyle('myTable6', $styleTable6);
  $table = $section->addTable('myTable6');
  $table->addRow(25);
  $table->addCell(10750)->addText('         Apuração dos rendimentos          ',$fontStyle);
  $table->addCell(10)->addText(' ',$fontStyle);
  $table->addCell(5280)->addText('                Dias letivos                 ',$fontStyle);
  $table->addCell(1800)->addText('Faltas do aluno',$fontStyle);

  $styleTable7 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);			
  //$bordas = array('borderColor'=>'','cellMarginTop'=>100);					   
  $bordas = array('cellMarginTop'=>100);					     
  $phpWord->addTableStyle('myTable7', $bordas);
  $table = $section->addTable('myTable7');
  $table->addRow(25);
  $table->addCell(3900,$combordas)->addText(' Componentes curriculares',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 1º TRI ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 2º TRI ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 3º TRI ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' MÉDIA  ',$fontStyle);
  resultado_final($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
  $x = 0;
  $faltas1=0;
  $indice = 0;
  $faltas1 = [];
  $linha2=[];
  $xindice1 = 0;
  $xconta1  = 1;
  $xconta   = 0;
  transferencia($ed47_i_codigo);
  fimanoletivo($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
  foreach ($dadosgrade as $linhona) {
     foreach ($linhona["xdiario"] as $linha){
        $linha2[$xconta] = $xconta1 . "ª Trimestre";
		if($ed60_situacao == 'TRANSFERIDO FORA' or $ed60_situacao == 'TRANSFERIDO REDE')
		{		
			if( $linhona["xdiario"][$xindice1]["numero_faltas"]==0 or $linhona["xdiario"][$xindice1]["numero_faltas"]==null or $linhona["xdiario"][$xindice1]["numero_faltas"]=="")
			{
				$faltas1[$xindice1] = '';
			}else{
				$faltas1[$xindice1] = $linhona["xdiario"][$xindice]["numero_faltas"];
			}
		}else{
			if( $linhona["xdiario"][$xindice1]["numero_faltas"]==0 or $linhona["xdiario"][$xindice1]["numero_faltas"]==null or $linhona["xdiario"][$xindice1]["numero_faltas"]=="")
			{
				$faltas1[$xindice1] = '';
			}else{
				$faltas1[$xindice1] = $linhona["xdiario"][$xindice1]["numero_faltas"];
			}
            
		}
		
        $xdl += $linhona["xaulas"][$xindice1]["ed78_i_aulasdadas"];
        $xfa += $linhona["xdiario"][$xindice1]["numero_faltas"];
		$impfre += $xfa+$xdl;
		$xconta++;
        $xconta1++;
        $xindice1++;
      }
	  if($ed60_situacao == 'TRANSFERIDO FORA' or $ed60_situacao == 'TRANSFERIDO REDE')
	  {			
  		 $xfreq = ""; 
      }else{
         $xfreq = ($xdl - $xfa) / $xdl * 100;
	     $xfreq = floor($xfreq);
	  }
  }
  $table->addCell(10,$combordas)->addText('',$fontStyle);
  $table->addCell(2846,$combordas)->addText(' 1º Trimestre  ',$fontStyle);
  $table->addCell(1438,$combordas)->addText('   ',$fontStyle);
  $table->addCell(1758,$combordas)->addText('        '.$faltas1[0],$fontStyle);
  $indfalt = 1;
  foreach ($dadosgrade2 as $linha) {
      //$table = $section->addTable('myTable6');
      $table->addRow(25);
      $table->addCell(3900,$combordas)->addText($linha["disciplina"],$fontStyle);
	  
      if($linha["disciplina"] == "ARTE" || $linha["disciplina"] == "TECNOLOGIA E INOVAÇÃO" || $linha["disciplina"] == "LÍNGUA INGLESA" || $linha["disciplina"] == "EDUCAÇÃO FISICA"){
        $n1 = $linha[0]["valor_conceito"];
        $n2 = $linha[1]["valor_conceito"];
        $n3 = $linha[2]["valor_conceito"];
      }else{
        $n1 = ajustaNota($linha[0]["valor_nota"]);
        $n2 = ajustaNota($linha[1]["valor_nota"]);
        $n3 = ajustaNota($linha[2]["valor_nota"]);
      }
      $table->addCell(1000,$combordas)->addText('  '.$n1,$fontStyle);
      $table->addCell(1000,$combordas)->addText('  '.$n2,$fontStyle);
      $table->addCell(1000,$combordas)->addText('  '.$n3,$fontStyle);
	  $dividir = 3;
	  $notamedia = '';
	  if( $linha[0]["valor_nota"] == null or trim($linha[0]["valor_nota"]) == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  if( $linha[1]["valor_nota"] == null or trim($linha[1]["valor_nota"]) == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  if( $linha[2]["valor_nota"] == null or trim($linha[2]["valor_nota"]) == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  
      $notamedia = ($linha[0]["valor_nota"] + $linha[1]["valor_nota"] + $linha[2]["valor_nota"]) / $dividir;
	  
      if($linha["disciplina"] == "ARTE" || $linha["disciplina"] == "TECNOLOGIA E INOVAÇÃO" || $linha["disciplina"] == "LÍNGUA INGLESA" || $linha["disciplina"] == "EDUCAÇÃO FISICA")
	  {
		  $notamedia = '';	  
	  }	  
		 	  
      $nm = explode(".", $notamedia);
      if(count($nm) == 2){
        if(strlen($nm[1]) > 1){
          $notamedia = $nm[0] . "," . substr($nm[1], 0, 1);
        }else{
		  $notamedia = str_replace(".", ",",$notamedia);
	    }
      }else{
		  $notamedia = number_format($notamedia, 1, ",", "");  
	  }
	  
      if($resfinal>0){
		  if($ed60_situacao == 'TRANSFERIDO FORA' or $ed60_situacao == 'TRANSFERIDO REDE')
		  {			
			 
             $table->addCell(1000,$combordas)->addText(' ',$fontStyle);			 
		  }else{
	         $table->addCell(1000,$combordas)->addText('  '.$notamedia,$fontStyle); 
		  }
		  
	  }else{
		  $table->addCell(1000,$combordas)->addText('  '.$notamedia,$fontStyle); 
	  }	 
	  if( $indfalt == 1)
	  {	  
		  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(2846,$combordas)->addText(' 2º Trimestre  ',$fontStyle);
		  $table->addCell(1438,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(1758,$combordas)->addText('        '.$faltas1[1],$fontStyle);
	  }
	  elseif( $indfalt == 2 )
	  {
		  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(2846,$combordas)->addText(' 3º Trimestre  ',$fontStyle);
		  $table->addCell(1438,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(1758,$combordas)->addText('        '.$faltas1[2],$fontStyle);
	  }
	  elseif( $indfalt == 3 )
	  {
		  $totalFaltas2 = $faltas1[0]+$faltas1[1]+$faltas1[2];
		  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(2846,$combordas)->addText(' TOTAL  ',$fontStyle);
		  $table->addCell(1438,$combordas)->addText(' ',$fontStyle);
		  $table->addCell(1758,$combordas)->addText('        '.$totalFaltas2,$fontStyle);
	  }
	  elseif( $indfalt == 4 )
	  {
		  if(date('Y-m-d') >= $ed52_d_resultfinal and substr($ed60_situacao,0,11) <> 'TRANSFERIDO' )
		  {
			  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
			  $table->addCell(2846,$esquerdaabaixo)->addText(' Frequencia % ',$fontStyle);
			  $table->addCell(1438,$abaixo)->addText(' ',$fontStyle);
			  $table->addCell(1758,$direitaabaixo)->addText('                '.$xfreq,$fontStyle);
		  }else{
			  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
			  $table->addCell(2846,$esquerdaabaixo)->addText(' Frequencia % ',$fontStyle);
			  $table->addCell(1438,$abaixo)->addText(' ',$fontStyle);
			  $table->addCell(1758,$direitaabaixo)->addText('   ',$fontStyle);
		  }
	  }	  
      $indfalt++;
  }
  $myTextElement->setFontStyle($fontStyle);
  fimanoletivo($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
  $datasaidaaluno = substr($ed60_d_datasaida,8,2).'/'.substr($ed60_d_datasaida,5,2).'/'.substr($ed60_d_datasaida,0,4);
  if(substr($ed60_situacao,0,11) <> 'TRANSFERIDO')
  {			
      if( $ed60_situacao <> 'MATRICULADO')
	  {
	       $myTextElement  = $section->addText(" ");
	       $myTextElement  = $section->addText($ed60_c_situacao." em: ".$datasaidaaluno);  
	  }else{
		   if(date('Y-m-d')<$ed52_d_resultfinal )
		   {
	            $myTextElement  = $section->addText(" ");
	            $myTextElement  = $section->addText("À vistas dos Resultados Obtidos, o aluno foi considerado : EM ANDAMENTO");  
		   }else{
	            $myTextElement  = $section->addText(" ");
	            $myTextElement  = $section->addText("À vistas dos Resultados Obtidos, o aluno foi considerado : ".$ed60_c_situacao);
		   }
	  }	   
  }else{
	  $myTextElement  = $section->addText(" ");
	  $myTextElement  = $section->addText("Aluno transferido em : " .$datasaidaaluno);  
  }
//***********************************************************************************************************************************************************/  
}elseif(substr($ed52_c_descr,0,11) == "ANOS FINAIS" && ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO")){
  $ed60_situacao = $ed60_c_situacao;
  $phpWord->addParagraphStyle('pStyle', array('align' => 'center', 'spaceAfter' => 100));	
  $styleTable5 = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                       'borderLeftColor'=>''  ,'borderLeftSize'=>6,
                       'borderRightColor'=>'' ,'borderRightSize'=>6, 
					   'borderBottomColor'=>'','borderBottomSize'=>6,
					   'cellMarginTop'=>100,
					   );
  
  $phpWord->addTableStyle('myTable5', $styleTable5);
  $myTextElement  = $section->addText(''); //insere uma linha em branco para separar as tabelas
  $table = $section->addTable('myTable5');
  $table->addRow(25);
  $table->addCell(20000)->addText('                                                                                  Ensino Fundamental - '.$ed11_c_descr,$fontStyle);
  $table->addRow(25);
  $table->addCell(20000)->addText('                                                     Etapa: '.$ed11_c_descr.'       Ano Letivo: '.$ed52_i_ano.'       Nome da Turma: '.$ed57_c_descr,$fontStyle);
  $myTextElement  = $section->addText('');
  
  $styleTable6 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);					   
  $phpWord->addTableStyle('myTable6', $styleTable6);
  $table = $section->addTable('myTable6');
  $table->addRow(25);
  $table->addCell(12000)->addText('         Apuração dos rendimentos          ',$fontStyle);
  $table->addCell(20)->addText(' ',$fontStyle);
  $table->addCell(5050)->addText('          FALTAS    ',$fontStyle);
  

//  $styleTable7 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);					   
  $bordas = array('cellMarginTop'=>100);					     
  $phpWord->addTableStyle('myTable7', $bordas);  
  $table = $section->addTable('myTable7');
  $table->addRow(25);
  $table->addCell(4000,$combordas)->addText(' Componentes curriculares',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 1º BIM ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 2º BIM ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' REC ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 3º BIM ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 4º BIM ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' MP  ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' REC ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' MF  ',$fontStyle);
  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
  $table->addCell(1000,$combordas)->addText('1º BIM',$fontStyle);
  $table->addCell(1000,$combordas)->addText('2º BIM',$fontStyle);
  $table->addCell(1000,$combordas)->addText('3º BIM',$fontStyle);
  $table->addCell(1000,$combordas)->addText('4º BIM',$fontStyle);
  $table->addCell(1000,$combordas)->addText('TOTAL',$fontStyle);
  
//  Faltas
//***********************************************************************************************************************************************************/

  transferencia($ed47_i_codigo);
  fimanoletivo($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
    $xconta = 1;
    $xindice = 0;
    $xdl = 0;
    $xfa = 0;

    $f1 = 0;
    $f2 = 0;
    $f3 = 0;
    $f4 = 0;
    $gt = 0;
	$x  = 1;
	$impfre = 0;
    $totalaulas = 0;
	$faltas2 = array(
	                 array()
					);
	
	$faltasTotal = [];
	$a = 0;
	$b = 0;
    foreach ($dadosgrade as $linha) {
        $faltas2[$a][0] = ($linha["xdiario"][0]["numero_faltas"]) ? $linha["xdiario"][0]["numero_faltas"] : "0";
		$faltas2[$a][1] = ($linha["xdiario"][1]["numero_faltas"]) ? $linha["xdiario"][1]["numero_faltas"] : "0";
		$faltas2[$a][2] = ($linha["xdiario"][3]["numero_faltas"]) ? $linha["xdiario"][3]["numero_faltas"] : "0";
		$faltas2[$a][3] = ($linha["xdiario"][4]["numero_faltas"]) ? $linha["xdiario"][4]["numero_faltas"] : "0";
		$faltasTotal[$a]= $faltas2[$a][0]+$faltas2[$a][1]+$faltas2[$a][2]+$faltas2[$a][3]; 
	    $a++;	
    }
	
//***********************************************************************************************************************************************************/
    $a=0;
    foreach ($dadosgrade2 as $linha) {
      if($linha["disciplina"] == "TECNOLOGIA E INOVAÇÃO"){
          $n1 = $linha[0]["valor_conceito"];
          $n2 = $linha[1]["valor_conceito"];
          $n3 = $linha[3]["valor_conceito"];
          $n4 = $linha[4]["valor_conceito"];
          $nr = '';		
		  
      }else{
		  $n1 = ajustaNota($linha[0]["valor_nota"]);
		  $n2 = ajustaNota($linha[1]["valor_nota"]);
		  $n3 = ajustaNota($linha[3]["valor_nota"]);
		  $n4 = ajustaNota($linha[4]["valor_nota"]);
		  $nr = ajustaNota($linha[2]["valor_nota"]);
      }		
      $table->addRow(25);	  
      $table->addCell(4000,$combordas)->addText($linha["disciplina"],$fontStyle);

      $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $n1),$fontStyle);	  
	  $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $n2),$fontStyle);
      //$table->addCell(1000)->addText(($linha[2]["valor_nota"]) ? str_replace(".", ",", $nr) : "-",$fontStyle);
	  $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $nr),$fontStyle);
      $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $n3),$fontStyle);
      $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $n4),$fontStyle);
	  $dividir = 4;
	  if( $linha[0]["valor_nota"] == null or $linha[0]["valor_nota"] == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  if( $linha[1]["valor_nota"] == null or $linha[1]["valor_nota"] == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  if( $linha[3]["valor_nota"] == null or $linha[3]["valor_nota"] == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  if( $linha[4]["valor_nota"] == null or $linha[4]["valor_nota"] == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  
	  if( $linha[2]["valor_nota"] ){
         $notamedia = ($linha[0]["valor_nota"] + $linha[1]["valor_nota"] + $linha[3]["valor_nota"] + $linha[4]["valor_nota"] + $linha[2]["valor_nota"]) / ($dividir+1);
	  }else{
		 $notamedia = ($linha[0]["valor_nota"] + $linha[1]["valor_nota"] + $linha[3]["valor_nota"] + $linha[4]["valor_nota"]) / $dividir; 
	  }	 
	  
	  $notafinal2 = $notamedia ;
      $nm = explode(".", $notamedia);      
      if(count($nm) == 2){
        if(strlen($nm[1]) > 1){
          $notamedia = $nm[0] . "," . substr($nm[1], 0, 1);
        }else{
		  $notamedia = str_replace(".", ",", $notamedia);
	    }
      }else{
		  $notamedia = number_format($notamedia, 1, ",", "");  
	  }
	  
	  if( $linha[5]["valor_nota"] > 0)
	  {
		  $notafinal = ($notafinal2+$linha[5]["valor_nota"])/2 ;
		  $nf = explode(".", $notafinal);      
		  if(count($nf) == 2){
			if(strlen($nf[1]) > 1){
			  $notafinal = $nf[0] . "," . substr($nf[1], 0, 1);
			}else{
			  $notafinal = str_replace(".", ",", $notafinal);
			}
		  }else{
			  $notafinal = number_format($notafinal, 1, ",", "");  
		  }
	  }
	  else{
		  $notafinal = $notamedia;
	  }

      if( $linha[5]["valor_nota"] == null or $linha[5]["valor_nota"] == ""){
		  $notarec = '-';
	  }else{
		  $nrec = explode(".", $linha[5]["valor_nota"]);      
		  if(count($nrec) == 2){
			if(strlen($nrec[1]) > 1){
			  $notarec = $nrec[0] . "," . substr($nrec[1], 0, 1);
			}else{
			  $notarec = str_replace(".", ",", $linha[5]["valor_nota"]);
			}
		  }else{
			  $notarec = number_format($linha[5]["valor_nota"], 1, ",", "");  
		  }
      }	  
	  $ed60_situacao = $ed60_c_situacao;
      if($linha["disciplina"] == "TECNOLOGIA E INOVAÇÃO")
	  {
		  $notamedia = '';	  
	  }	  
      if($linha["disciplina"] == "TECNOLOGIA E INOVAÇÃO")
	  {
		  $notafinal = '';	  
	  }	  
	  
 	  if($ed60_c_situacao <> 'TRANSFERIDO FORA' or $ed60_c_situacao <> 'TRANSFERIDO REDE')
	  {			
          $table->addCell(1000,$combordas)->addText('   '.$notamedia,$fontStyle);
		  $table->addCell(1000,$combordas)->addText('   '.$notarec,$fontStyle);
		  $table->addCell(1000,$combordas)->addText('   '.$notafinal,$fontStyle);
	  }  
	  if( $faltas2[$a][0] == 0)
	  {
		  $faltas2[$a][0] = "";
	  }	  
  	  if( $faltas2[$a][1] == 0)
	  {
		  $faltas2[$a][1] = "";
	  }	  
  	  if( $faltas2[$a][2] == 0)
	  {
		  $faltas2[$a][2] = "";
	  }	  
  	  if( $faltas2[$a][3] == 0)
	  {
		  $faltas2[$a][3] = "";
	  }	  
  	  if( $faltasTotal[$a] == 0)
	  {
		  $faltasTotal[$a] = "";
	  }	  
	  
	  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
	  $table->addCell(1000,$combordas)->addText('     '.$faltas2[$a][0],$fontStyle);
      $table->addCell(1000,$combordas)->addText('     '.$faltas2[$a][1],$fontStyle);
      $table->addCell(1000,$combordas)->addText('     '.$faltas2[$a][2],$fontStyle);
      $table->addCell(1000,$combordas)->addText('     '.$faltas2[$a][3],$fontStyle);
      $table->addCell(1000,$combordas)->addText('       '.$faltasTotal[$a],$fontStyle);
	  $a++;
    }
	$totalfaltas = 0;
	for($x=0;$x<10;$x++)
	{
		$totalfaltas += $faltasTotal[$x];
	}	
	// para colocar bordas apenas em uma celula usando o borderstyle($bordasexternas), não poderá ser usado bordas no addTableStyle
	// conforme abaixo, criei $bordas, sem definir bordas, então usei $bordasexternas no addCell, caso contrário não funciona
    $bordas = array('cellMarginTop'=>100);					   
    $phpWord->addTableStyle('myTable11', $bordas);
    $table = $section->addTable('myTable11');
    $table->addRow(25);
    $table->addCell(12100)->addText('',$fontStyle);
    $table->addCell(20)->addText(' ',$fontStyle);
    $table->addCell(4550,$bordasexternas)->addText('          Total faltas :'.$totalfaltas,$fontStyle);
    $table->addRow(25);
    if(date('Y-m-d') >= $ed52_d_resultfinal and substr($ed60_situacao,0,11) <> 'TRANSFERIDO' )
	{
		$table->addCell(12100)->addText('',$fontStyle);
		$table->addCell(20)->addText(' ',$fontStyle);
		$table->addCell(4550,$bordasexternas)->addText('          Frequencia % :'.$xfreq,$fontStyle);
    }else{
		$table->addCell(12100)->addText('',$fontStyle);
		$table->addCell(20)->addText(' ',$fontStyle);
		$table->addCell(4550,$bordasexternas)->addText('          Frequencia % :',$fontStyle);
	}	
    fimanoletivo($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
    $datasaidaaluno = substr($ed60_d_datasaida,8,2).'/'.substr($ed60_d_datasaida,5,2).'/'.substr($ed60_d_datasaida,0,4);
    if(substr($ed60_situacao,0,11) <> 'TRANSFERIDO')
    {			
        if( $ed60_situacao <> 'MATRICULADO' and $ed60_situacao <> '' )
	    {
//	        $myTextElement  = $section->addText(" ");
	        $myTextElement  = $section->addText($ed60_c_situacao." em: ".$datasaidaaluno);  
	    }else{
		    if(date('Y-m-d')<$ed52_d_resultfinal )
		    {
//	            $myTextElement  = $section->addText(" ");
	            $myTextElement  = $section->addText("À vistas dos Resultados Obtidos, o aluno foi considerado : EM ANDAMENTO");  
		    }else{
//	            $myTextElement  = $section->addText(" ");
	            $myTextElement  = $section->addText("À vistas dos Resultados Obtidos, o aluno foi considerado : ".$ed60_c_situacao);
		    }
	    }	   
    }else{
//	    $myTextElement  = $section->addText(" ");
	   $myTextElement  = $section->addText("Aluno transferido em : " .$datasaidaaluno);  
    }
}elseif(substr($ed52_c_descr,0,10) == "EJA FINAIS"){
    $ed60_situacao = $ed60_c_situacao;
    $phpWord->addParagraphStyle('pStyle', array('align' => 'center', 'spaceAfter' => 100));	
    $styleTable5 = array('borderTopColor'=>''   ,'borderTopSize'=>6,
                         'borderLeftColor'=>''  ,'borderLeftSize'=>6,
                         'borderRightColor'=>'' ,'borderRightSize'=>6, 
                         'borderBottomColor'=>'','borderBottomSize'=>6,
                         'cellMarginTop'=>100,
					    );
  
  $phpWord->addTableStyle('myTable5', $styleTable5);
  $myTextElement  = $section->addText(''); //insere uma linha em branco para separar as tabelas
  $table = $section->addTable('myTable5');
  $table->addRow(25);
  $table->addCell(20000)->addText('                                                                                  Ensino Fundamental - '.$ed11_c_descr,$fontStyle);
  $table->addRow(25);
  $table->addCell(20000)->addText('                                                     Etapa: '.$ed11_c_descr.'       Ano Letivo: '.$ed52_i_ano.'       Nome da Turma: '.$ed57_c_descr,$fontStyle);
  $myTextElement  = $section->addText('');
  
  $styleTable6 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);					   
  $phpWord->addTableStyle('myTable6', $styleTable6);
  $table = $section->addTable('myTable6');

  $bordas = array('cellMarginTop'=>100);					   
  $phpWord->addTableStyle('myTable11', $bordas);
  $table = $section->addTable('myTable11');
  $table->addRow(25);
  $table->addCell(12000)->addText('',$fontStyle);
  $table->addCell(20)->addText(' ',$fontStyle);
  $table->addCell(5950,$bordasexternas)->addText('                                    FALTAS',$fontStyle);
  
  
//  $styleTable6 = array('borderColor'=>'','borderSize'=>6, 'cellMarginTop'=>100);					   
  $phpWord->addTableStyle('myTable6', $bordas);
  $table = $section->addTable('myTable6');
  $table->addRow(25);
  $table->addCell(4000,$combordas)->addText(' Componentes curriculares',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 1º BIM ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 2º BIM ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 3º BIM ',$fontStyle);
  $table->addCell(1000,$combordas)->addText(' 4º BIM ',$fontStyle);
  $table->addCell(1000,$combordas)->addText('Média final',$fontStyle);
  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
  $table->addCell(1000,$combordas)->addText('1º BIM',$fontStyle);
  $table->addCell(1000,$combordas)->addText('2º BIM',$fontStyle);
  $table->addCell(1000,$combordas)->addText('3º BIM',$fontStyle);
  $table->addCell(1000,$combordas)->addText('4º BIM',$fontStyle);
  $table->addCell(1000,$combordas)->addText('TOTAL',$fontStyle);
//*************************************************************************************************************************************  
  transferencia($ed47_i_codigo);
  fimanoletivo($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
    $xconta = 1;
    $xindice = 0;
    $xdl = 0;
    $xfa = 0;

    $f1 = 0;
    $f2 = 0;
    $f3 = 0;
    $f4 = 0;
    $gt = 0;
	$x  = 1;
	$impfre = 0;
    $totalaulas = 0;
	$faltas2 = array(
	                 array()
					);
	
	$faltasTotal = [];
	$a = 0;
	$b = 0;
    foreach ($dadosgrade as $linha) {
        $faltas2[$a][0] = ($linha["xdiario"][0]["numero_faltas"]) ? $linha["xdiario"][0]["numero_faltas"] : "0";
		$faltas2[$a][1] = ($linha["xdiario"][1]["numero_faltas"]) ? $linha["xdiario"][1]["numero_faltas"] : "0";
		$faltas2[$a][2] = ($linha["xdiario"][3]["numero_faltas"]) ? $linha["xdiario"][3]["numero_faltas"] : "0";
		$faltas2[$a][3] = ($linha["xdiario"][4]["numero_faltas"]) ? $linha["xdiario"][4]["numero_faltas"] : "0";
		$faltasTotal[$a]= $faltas2[$a][0]+$faltas2[$a][1]+$faltas2[$a][2]+$faltas2[$a][3]; 
	    $a++;	
    }
	
//***********************************************************************************************************************************************************/
    $a=0;
    foreach ($dadosgrade2 as $linha) {
      if($linha["disciplina"] == "TECNOLOGIA E INOVAÇÃO"){
          $n1 = $linha[0]["valor_conceito"];
          $n2 = $linha[1]["valor_conceito"];
          $n3 = $linha[3]["valor_conceito"];
          $n4 = $linha[4]["valor_conceito"];
          $nr = '';		
		  
      }else{
		  $n1 = ajustaNota($linha[0]["valor_nota"]);
		  $n2 = ajustaNota($linha[1]["valor_nota"]);
		  $n3 = ajustaNota($linha[3]["valor_nota"]);
		  $n4 = ajustaNota($linha[4]["valor_nota"]);
		  $nr = ajustaNota($linha[2]["valor_nota"]);
      }		
      $table->addRow(25);	  
      $table->addCell(4000,$combordas)->addText($linha["disciplina"],$fontStyle);

      $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $n1),$fontStyle);	  
	  $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $n2),$fontStyle);
      $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $n3),$fontStyle);
      $table->addCell(1000,$combordas)->addText('   '.str_replace(".", ",", $n4),$fontStyle);
	  $dividir = 4;
	  if( $linha[0]["valor_nota"] == null or $linha[0]["valor_nota"] == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  if( $linha[1]["valor_nota"] == null or $linha[1]["valor_nota"] == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  if( $linha[3]["valor_nota"] == null or $linha[3]["valor_nota"] == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  if( $linha[4]["valor_nota"] == null or $linha[4]["valor_nota"] == "")
	  {
		  $dividir = $dividir - 1;
	  }
	  
	  if( $linha[2]["valor_nota"] ){
         $notamedia = ($linha[0]["valor_nota"] + $linha[1]["valor_nota"] + $linha[3]["valor_nota"] + $linha[4]["valor_nota"] + $linha[2]["valor_nota"]) / ($dividir+1);
	  }else{
		 $notamedia = ($linha[0]["valor_nota"] + $linha[1]["valor_nota"] + $linha[3]["valor_nota"] + $linha[4]["valor_nota"]) / $dividir; 
	  }	 
	  
	  $notafinal2 = $notamedia ;
      $nm = explode(".", $notamedia);      
      if(count($nm) == 2){
        if(strlen($nm[1]) > 1){
          $notamedia = $nm[0] . "," . substr($nm[1], 0, 1);
        }else{
		  $notamedia = str_replace(".", ",", $notamedia);
	    }
      }else{
		  $notamedia = number_format($notamedia, 1, ",", "");  
	  }
	  
	  if( $linha[5]["valor_nota"] > 0)
	  {
		  $notafinal = ($notafinal2+$linha[5]["valor_nota"])/2 ;
		  $nf = explode(".", $notafinal);      
		  if(count($nf) == 2){
			if(strlen($nf[1]) > 1){
			  $notafinal = $nf[0] . "," . substr($nf[1], 0, 1);
			}else{
			  $notafinal = str_replace(".", ",", $notafinal);
			}
		  }else{
			  $notafinal = number_format($notafinal, 1, ",", "");  
		  }
	  }
	  else{
		  $notafinal = $notamedia;
	  }

      if( $linha[5]["valor_nota"] == null or $linha[5]["valor_nota"] == ""){
		  $notarec = '-';
	  }else{
		  $nrec = explode(".", $linha[5]["valor_nota"]);      
		  if(count($nrec) == 2){
			if(strlen($nrec[1]) > 1){
			  $notarec = $nrec[0] . "," . substr($nrec[1], 0, 1);
			}else{
			  $notarec = str_replace(".", ",", $linha[5]["valor_nota"]);
			}
		  }else{
			  $notarec = number_format($linha[5]["valor_nota"], 1, ",", "");  
		  }
      }	  
	  $ed60_situacao = $ed60_c_situacao;
      if($linha["disciplina"] == "TECNOLOGIA E INOVAÇÃO")
	  {
		  $notamedia = '';	  
	  }	  
      if($linha["disciplina"] == "TECNOLOGIA E INOVAÇÃO")
	  {
		  $notafinal = '';	  
	  }	  
	  
 	  if($ed60_c_situacao <> 'TRANSFERIDO FORA' or $ed60_c_situacao <> 'TRANSFERIDO REDE')
	  {			
          $table->addCell(1000,$combordas)->addText('   '.$notamedia,$fontStyle);
	  }  
	  if( $faltas2[$a][0] == 0)
	  {
		  $faltas2[$a][0] = "";
	  }	  
  	  if( $faltas2[$a][1] == 0)
	  {
		  $faltas2[$a][1] = "";
	  }	  
  	  if( $faltas2[$a][2] == 0)
	  {
		  $faltas2[$a][2] = "";
	  }	  
  	  if( $faltas2[$a][3] == 0)
	  {
		  $faltas2[$a][3] = "";
	  }	  
  	  if( $faltasTotal[$a] == 0)
	  {
		  $faltasTotal[$a] = "";
	  }	  
	  
	  $table->addCell(10,$combordas)->addText(' ',$fontStyle);
	  $table->addCell(1000,$combordas)->addText('     '.$faltas2[$a][0],$fontStyle);
      $table->addCell(1000)->addText('     '.$faltas2[$a][1],$fontStyle);
      $table->addCell(1000,$combordas)->addText('     '.$faltas2[$a][2],$fontStyle);
      $table->addCell(1000,$combordas)->addText('     '.$faltas2[$a][3],$fontStyle);
      $table->addCell(1000,$combordas)->addText('       '.$faltasTotal[$a],$fontStyle);
	  $a++;
    }
	for($x=0;$x<10;$x++)
	{
		$totalfaltas += $faltasTotal[$x];
	}	
	// para colocar bordas apenas em uma celula usando o borderstyle($bordasexternas), não poderá ser usado bordas no addTableStyle
	// conforme abaixo, criei $bordas, sem definir bordas, então usei $bordasexternas no addCell, caso contrário não funciona
    $bordas = array('cellMarginTop'=>100);					   
    $phpWord->addTableStyle('myTable11', $bordas);
    $table = $section->addTable('myTable11');
    $table->addRow(25);
    $table->addCell(12100)->addText('',$fontStyle);
    $table->addCell(20)->addText(' ',$fontStyle);
    $table->addCell(5850,$bordasexternas)->addText('                                                 Total faltas :'.$totalfaltas,$fontStyle);
    if(date('Y-m-d') >= $ed52_d_resultfinal and substr($ed60_situacao,0,11) <> 'TRANSFERIDO' )
	{
		$table->addRow(25);
		$table->addCell(12100)->addText('',$fontStyle);
		$table->addCell(20)->addText(' ',$fontStyle);
		$table->addCell(5800,$bordasexternas)->addText('                                                 Frequencia % :'.$xfreq,$fontStyle);
    }else{
		$table->addRow(25);
		$table->addCell(12100)->addText('',$fontStyle);
		$table->addCell(20)->addText(' ',$fontStyle);
		$table->addCell(5800,$bordasexternas)->addText('                                                 Frequencia % :',$fontStyle);
	}
    fimanoletivo($escola,$ed11_i_codigo,$ed60_i_turma,$ed47_i_codigo);
    $datasaidaaluno = substr($ed60_d_datasaida,8,2).'/'.substr($ed60_d_datasaida,5,2).'/'.substr($ed60_d_datasaida,0,4);
    if(substr($ed60_situacao,0,11) <> 'TRANSFERIDO')
    {			
        if( $ed60_situacao <> 'MATRICULADO' and $ed60_situacao <> '' )
	    {
//	        $myTextElement  = $section->addText(" ");
	        $myTextElement  = $section->addText($ed60_c_situacao." em: ".$datasaidaaluno);  
	    }else{
		    if(date('Y-m-d')<$ed52_d_resultfinal )
		    {
//	            $myTextElement  = $section->addText(" ");
	            $myTextElement  = $section->addText("À vistas dos Resultados Obtidos, o aluno foi considerado : EM ANDAMENTO");  
		    }else{
//	            $myTextElement  = $section->addText(" ");
	            $myTextElement  = $section->addText("À vistas dos Resultados Obtidos, o aluno foi considerado : ".$ed60_c_situacao);
		    }
	    }	   
    }else{
//	    $myTextElement  = $section->addText(" ");
	   $myTextElement  = $section->addText("Aluno transferido em : " .$datasaidaaluno);  
    }
//*************************************************************************************************************************************	
}	// fim do else

  $data = date( "Y-m-d",DB_getsession("DB_datausu") );
  $dia  = date( "d" );
  $mes  = date( "m" );
  $ano  = date( "Y" );

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
  
  if(substr($ed52_c_descr,0,11) <> "ANOS FINAIS" and substr($ed52_c_descr,0,10) <> "EJA FINAIS"){
      $myTextElement  = $section->addText('');  
  }	  
  $myTextElement  = $section->addText($data_extenso,$fontStyle);  
  $myTextElement  = $section->addText('');  

  $lExibirAdicional = false;
  $lExibirAdicional2 = false;
  $lExibirProfessor = false;

  if ( isset( $oGet->iAssinaturaAdicional ) && !empty( $oGet->iAssinaturaAdicional ) ) {
    $lExibirAdicional = true;
  }

  if ( isset( $oGet->iAssinaturaAdicional2 ) && !empty( $oGet->iAssinaturaAdicional2 ) ) {
    $lExibirAdicional2 = true;
  }

  if ( isset( $oGet->lExibeAssinaturaProfessor ) && $oGet->lExibeAssinaturaProfessor == "S" ) {
    $lExibirProfessor = true;
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
  

  if( $iAssinaturaAdicional3 <> null and $iAssinaturaAdicional2 == null )
  {	  
      $myTextElement  = $section->addText('');  
      $myTextElement  = $section->addText('____________________________________');  
	  $myTextElement  = $section->addText(utf8_decode($iAssinaturaAdicional3));
	  $myTextElement  = $section->addText('SECRETÁRIO DE ESCOLA');
	  $myTextElement  = $section->addText('Matrícula: '.substr($matriculasec,2,15));
	  
  }
      
  if( $iAssinaturaAdicional2 <> null and $iAssinaturaAdicional3 == null )
  {	  
     $myTextElement  = $section->addText('');  
     $myTextElement  = $section->addText('                                                                             ____________________________________' );
	 $myTextElement  = $section->addText('                                                                             '.utf8_decode($iAssinaturaAdicional2) );
	 $myTextElement  = $section->addText('                                                                             SUPERVISOR DE ESCOLA');
	 $myTextElement  = $section->addText('                                                                             Matrícula: '.substr($matriculasup,2,15));
  }
  
  if( $iAssinaturaAdicional3 <> null and $iAssinaturaAdicional2 <> null )
  {	  
	  $phpWord->addParagraphStyle('pStyle', array('align' => 'center', 'spaceAfter' => 100));
      $styleTable10 = array('cellMarginTop'=>100);					   
      $phpWord->addTableStyle('myTable10', $styleTable10);
	  
      $table = $section->addTable('myTable10');
	  $table->addRow(25);
      $table->addCell(5000)->addText('____________________________________',$fontStyle1);
	  $table->addCell(10000)->addText('',$fontStyle1);
	  $table->addCell(5000)->addText('____________________________________',$fontStyle1);

      $table->addRow(25);
      $table->addCell(5000)->addText(utf8_decode($iAssinaturaAdicional3),$fontStyle1);
	  $table->addCell(10000)->addText('',$fontStyle1);
	  $table->addCell(5000)->addText(utf8_decode($iAssinaturaAdicional2),$fontStyle1);
      $table->addRow(25);
      $table->addCell(5000)->addText('SECRETÁRIO DE ESCOLA',$fontStyle1);
	  $table->addCell(10000)->addText('',$fontStyle1);
	  $table->addCell(5000)->addText('SUPERVISOR ESCOLAR',$fontStyle1);
      $table->addRow(25);
      $table->addCell(5000)->addText('Matrícula: '.substr($matriculasec,2,15),$fontStyle1);
	  $table->addCell(10000)->addText('',$fontStyle);
	  $table->addCell(5000)->addText('Matrícula: '.substr($matriculasup,2,15),$fontStyle1);
	  
  }

  if($iAssinaturaAdicional <> null)
  {	  
	  $myTextElement  = $section->addText('',$fontStyle1);  
      $myTextElement  = $section->addText('                                                                                              ______________________________',$fontStyle1);  
  	  $myTextElement  = $section->addText('',$fontStyle1);  
	  $myTextElement  = $section->addText('                                                                                              '.$iAssinaturaAdicional,$fontStyle1);
	  $myTextElement  = $section->addText('',$fontStyle1);  
	  $myTextElement  = $section->addText('                                                                                               DIRETOR DE ESCOLA',$fontStyle1);
	  $myTextElement  = $section->addText('',$fontStyle1);  
	  $myTextElement  = $section->addText('                                                                                               Matrícula: '.substr($matriculadir,2,15),$fontStyle1);
  }
  $myTextElement  = $section->addPageBreak();

//***********************************************************************************************************************************************************/
}
$linha_impressa = '';
$myTextElement  = $section->addText($linha_impressa);
$myTextElement->setFontStyle($fontStyle);
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('tmp/ficha_individual.docx');
session_write_close();
download("tmp/ficha_individual.docx");
header("Location: https://ecidade.epdvr.com.br/e-cidade/extension/desktop");
//header("Location: https://homologacao.epdvr.com.br/homologacao/extension/desktop");
  
  
//$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.txt","w+");
//fwrite($arq, 'estou aqui');
//fwrite($arq,"\r\n");
//fclose($arq); 		

function download($arquivo){
      header("Content-Type: application/force-download");
      header("Content-Type: application/octet-stream;");
      header("Content-Length:".filesize($arquivo));
      header("Content-disposition: attachment; filename=".$arquivo);
      header("Pragma: no-cache");
      header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
      header("Expires: 0");
      readfile($arquivo);
      flush();
}
?>