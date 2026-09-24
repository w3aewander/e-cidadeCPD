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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

parse_str($_SERVER["QUERY_STRING"]);
db_postmemory($_POST);

$oGet = db_utils::postMemory($_GET);
$oPost = db_utils::postMemory($_POST);

function reiniciaSequencia($oDao, $sQuery, $sCampoSeq, $sCampoCod, $sCondicao = '', $iInicioSeq = 1, $iIncremento = 1)
{

    // Faço a busca dos registros da sequencia
    $sSql = $oDao->{$sQuery}(null, '*', $sCampoSeq . ' asc ', $sCondicao);
    $rs = $oDao->sql_record($sSql);
    if ($oDao->numrows <= 0) {
        return false;
    }

    $iNumRows = $oDao->numrows;
    for ($iCont = 0; $iCont < $iNumRows; $iCont++) {

        $oDados = db_utils::fieldsmemory($rs, $iCont);

        if ($oDados->{$sCampoSeq} != $iInicioSeq) { // Se o campo não estiver na sequência correta, altero

            $oDao->{$sCampoCod} = $oDados->{$sCampoCod};
            $oDao->{$sCampoSeq} = $iInicioSeq;
            $oDao->alterar($oDados->{$sCampoCod});

            if ($oDao->erro_status == '0') {
                return $oDao->erro_msg;
            }
        }

        $iInicioSeq += $iIncremento;
    }

    return '';
}

$resultedu = eduparametros(db_getsession("DB_coddepto"));

$clmatricula = new cl_matricula;
$clmatriculamov = new cl_matriculamov;
$clmatriculaserie = new cl_matriculaserie;
$clturma = new cl_turma;
$clturmaserieregimemat = new cl_turmaserieregimemat;
$clpareceraval = new cl_pareceraval;
$clparecerresult = new cl_parecerresult;
$clabonofalta = new cl_abonofalta;
$clalunocurso = new cl_alunocurso;
$clalunopossib = new cl_alunopossib;
$clalunotransfturma = new cl_alunotransfturma;
$cltransfescolarede = new cl_transfescolarede;
$cltransfescolafora = new cl_transfescolafora;
$cldiario = new cl_diario;
$clamparo = new cl_amparo;
$cltransfaprov = new cl_transfaprov;
$cldiarioavaliacao = new cl_diarioavaliacao;
$cldiarioresultado = new cl_diarioresultado;
$cldiariofinal = new cl_diariofinal;
$cllogmatricula = new cl_logmatricula;
$claprovconselho = new cl_aprovconselho;
$cltrocaserie = new cl_trocaserie;
$clserie = new cl_serie;
$clbaseserie = new cl_baseserie;
$clescolabase = new cl_escolabase;
$oDaoMatriculaTurnoReferente = new cl_matriculaturnoreferente();

$db_botao = false;
$db_opcao = 33;
$db_opcao1 = 3;

if (isset($excluir)) {
    $lErro = false;
    $db_opcao = 3;
    $db_opcao1 = 1;

    $sWhereTrocaSerie = "ed101_i_aluno = {$ed60_i_aluno} AND ed101_i_turmadest = {$ed60_i_turma}";
    $sSqlTrocaSerie = $cltrocaserie->sql_query("", "ed101_i_codigo", "", $sWhereTrocaSerie);
    $result_prog = $cltrocaserie->sql_record($sSqlTrocaSerie);

    $sql = "select ed60_c_situacao from matricula where ed60_i_codigo = {$oPost->ed60_matricula}";
    $situacao = pg_fetch_assoc(db_query($sql));
    if ($cltrocaserie->numrows > 0 && trim($situacao['ed60_c_situacao']) == "AVANÇADO") {
        $lErro = true;
        $clmatricula->erro_status = "0";
        $clmatricula->erro_msg = "Aluno selecionado foi progredido para esta turma.
                          \\nPara excluir sua matrícula, esta progressão deve ser cancelada.
                          \\nAcesse Procedimentos -> Progressão de Aluno -> Cancelar Progressão";
    } else {
        $sql_exc = "
                   select distinct 
				   ed95_i_codigo as coddiario
                   from  
				   diarioavaliacao
                   inner join diario on ed95_i_codigo = ed72_i_diario
                   where 
				   ed95_i_aluno = $ed60_i_aluno
                   and 
				   ed95_i_regencia in (
                                       select 
									   ed59_i_codigo
                                       from 
									   regencia
                                       where 
									   ed59_i_turma in (
                                                        select 
														ed60_i_turma
                                                        from 
														matricula
                                                        where 
														ed60_matricula = {$oPost->ed60_matricula}
                                                       )
                                       )
                   ";
				   
		$result    = pg_query($sql_exc);
		$Dados     = db_utils::fieldsmemory($result,0);
		$coddiario = $Dados->coddiario;
		$matricula = $oPost->ed60_matricula;               
        // se ja tiver movimentação no diario
        if( !empty($coddiario) )
        {			
			pg_query("delete from amparo                     where ed81_i_diario     = ".$coddiario) or die("delete from amparo                     where ed81_i_diario     = ".$coddiario);
			pg_query("delete from diariofinal                where ed74_i_diario     = ".$coddiario) or die("delete from diariofinal                where ed74_i_diario     = ".$coddiario);
			pg_query("delete from diarioresultado            where ed73_i_diario     = ".$coddiario) or die("delete from diarioresultado            where ed73_i_diario     = ".$coddiario);
			pg_query("delete from diarioavaliacao            where ed72_i_diario     = ".$coddiario) or die("delete from diarioavaliacao            where ed72_i_diario     = ".$coddiario);
			pg_query("delete from aprovconselho              where ed253_i_diario    = ".$coddiario) or die("delete from aprovconselho              where ed253_i_diario    = ".$coddiario);
			pg_query("delete from diarioavaliacaoalternativa where ed136_diario      = ".$coddiario) or die("delete from diarioavaliacaoalternativa where ed136_diario      = ".$coddiario);
			pg_query("delete from diario                     where ed95_i_codigo     = ".$coddiario) or die("delete from diario                     where ed95_i_codigo     = ".$coddiario);
        }

        pg_query("delete from alunopossib where ed79_i_alunocurso = ( select
																	  distinct on (ed56_i_codigo)
																	  alunocurso.ed56_i_codigo
																	  from
																	  aluno
																	  inner join matricula  on ed60_i_aluno = ed47_i_codigo
																	  inner join alunocurso on ed56_i_aluno = ed47_i_codigo
																	  where
																	  ed60_i_codigo = {$matricula})") or die("delete from alunopossib where ed79_i_alunocurso = (  select
																											  distinct on (ed56_i_codigo)
																											  alunocurso.ed56_i_codigo
																											  from
																											  aluno
																											  inner join matricula  on ed60_i_aluno = ed47_i_codigo
																											  inner join alunocurso on ed56_i_aluno = ed47_i_codigo
																											  where
																											  ed60_i_codigo = {$matricula})") ;
																	  
        pg_query("delete from alunocurso  where ed56_i_aluno      = (select distinct on (ed60_i_aluno) ed60_i_aluno from matricula where ed60_i_codigo = {$matricula})") or die
		        ("delete from alunocurso  where ed56_i_aluno      = (select distinct on (ed60_i_aluno) ed60_i_aluno from matricula where ed60_i_codigo = {$matricula})");

		pg_query("delete from matriculamov            where ed229_i_matricula = ".$matricula) or die("delete from matriculamov            where ed229_i_matricula = ".$matricula);
		pg_query("delete from alunotransfturma        where ed69_i_matricula  = ".$matricula) or die("delete from alunotransfturma        where ed69_i_matricula  = ".$matricula);
		pg_query("delete from transfescolarede        where ed103_i_matricula = ".$matricula) or die("delete from transfescolarede        where ed103_i_matricula = ".$matricula);
		pg_query("delete from transfescolafora        where ed104_i_matricula = ".$matricula) or die("delete from transfescolafora        where ed104_i_matricula = ".$matricula);
		pg_query("delete from matriculaserie          where ed221_i_matricula = ".$matricula) or die("delete from matriculaserie          where ed221_i_matricula = ".$matricula);
		pg_query("delete from matriculaturnoreferente where ed337_matricula   = ".$matricula) or die("delete from matriculaturnoreferente where ed337_matricula   = ".$matricula);
		pg_query("delete from matricula               where ed60_i_codigo     = ".$matricula) or die("delete from matricula               where ed60_i_codigo     = ".$matricula);
        db_msgbox("Exclusão efetuada com sucesso!!!");
   
	}
} else if (isset($chavepesquisa)) {

    $db_opcao = 3;
    $db_opcao1 = 1;

    $sCampos = "turma.*, calendario.*, base.*, cursoedu.*, turno.*, fc_nomeetapaturma(ed57_i_codigo) as nometapa";
    $sCampos .= ", fc_codetapaturma(ed57_i_codigo) as codetapa";
    $result = $clturma->sql_record($clturma->sql_query("", $sCampos, "", "ed57_i_codigo = {$chavepesquisa}"));
    db_fieldsmemory($result, 0);

    $ed60_i_turma = $ed57_i_codigo;
    $sWhereMatricula = "ed60_i_turma = $ed60_i_turma AND ed60_c_situacao = 'MATRICULADO'";
    $sSqlMatricula = $clmatricula->sql_query_file("", "count(*) ", "", $sWhereMatricula);
    $result1 = $clmatricula->sql_record($sSqlMatricula);
    db_fieldsmemory($result1, 0);
    ?>
    <script>
        parent.document.formaba.a2.disabled = false;
        parent.document.formaba.a2.style.color = "black";
        (window.CurrentWindow || parent.CurrentWindow).corpo.iframe_a2.location.href = 'edu1_alunoturma001.php?ed60_i_turma=<?=$ed57_i_codigo?>'
            + '&ed57_c_descr=<?=$ed57_c_descr?>'
            + '&ed52_c_descr=<?=$ed52_c_descr?>';
    </script>
    <?php
}
?>
<html>
<head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script type="text/javascript" src="scripts/scripts.js"></script>
    <script type="text/javascript" src="scripts/strings.js"></script>
    <script type="text/javascript" src="scripts/prototype.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#CCCCCC">
<?php require_once(modification("forms/db_frmmatricula.php")); ?>
</body>
</html>
<?php
if (isset($excluir)) {
    if ($clmatricula->erro_status == "0") {
        db_msgbox($clmatricula->erro_msg);
    } else {

        ?>
        <script>
            parent.document.formaba.a2.disabled = false;
            parent.document.formaba.a2.style.color = "black";
            (window.CurrentWindow || parent.CurrentWindow).corpo.iframe_a2.location.href = 'edu1_alunoturma001.php?ed60_i_turma=<?=$ed60_i_turma?>'
                + '&ed57_c_descr=<?=$ed57_c_descr?>'
                + '&ed52_c_descr=<?=$ed52_c_descr?>';
        </script>
        <?php
        $clmatricula->erro(true, true);
    }
}

if ($db_opcao == 33) {
    echo "<script>js_pesquisaed60_i_turma();</script>";
}
?>
<script>
    js_tabulacaoforms("form1", "excluir", true, 1, "excluir", true);
</script>
