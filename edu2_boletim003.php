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

use ECidade\Educacao\Escola\Factory\GradeAproveitamentoFactory;


require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("fpdf151educacao/scpdf.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_libparagrafo.php"));


$oGet       = db_utils::postMemory($_GET);
$oGet->obs1 = base64_decode($oGet->obs1);
$oFiltros   = new stdClass();


function buscaParametroArredondamento($escola)
{
    $ano = db_getsession("DB_anousu");
    $sql = pg_query("SELECT ed315_arredondamedia from avaliacaoestruturanota inner join db_estrutura on db_estrutura.db77_codestrut = avaliacaoestruturanota.ed315_db_estrutura inner join escola on escola.ed18_i_codigo = avaliacaoestruturanota.ed315_escola inner join bairro on bairro.j13_codi = escola.ed18_i_bairro inner join ruas on ruas.j14_codigo = escola.ed18_i_rua inner join db_depart on db_depart.coddepto = escola.ed18_i_codigo inner join censouf on censouf.ed260_i_codigo = escola.ed18_i_censouf inner join censomunic on censomunic.ed261_i_codigo = escola.ed18_i_censomunic inner join censodistrito on censodistrito.ed262_i_codigo = escola.ed18_i_censodistrito left join censoorgreg on censoorgreg.ed263_i_codigo = escola.ed18_i_censoorgreg left join censolinguaindig on censolinguaindig.ed264_i_codigo = escola.ed18_i_linguaindigena where ed315_escola = {$escola} AND ed315_ano = {$ano} order by ed315_sequencial");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed315_arredondamedia"];
}

$clturma = new cl_turma;
$clmatricula       = new cl_matricula;
$clregenteconselho = new cl_regenteconselho;
$cldiarioavaliacao = new cl_diarioavaliacao;
$clprocavaliacao   = new cl_procavaliacao;
$clpareceraval     = new cl_pareceraval;
$claprovconselho   = new cl_aprovconselho;
$clDBConfig        = new cl_db_config();
$clEscola          = new cl_escola();
$oDadosGrade       = new stdClass();

$oDadosGrade->nLarguraGrade = 189;

$obs1   = base64_decode($obs1);
$escola = db_getsession("DB_coddepto");
$arredondaMedia = buscaParametroArredondamento($escola);




$clobsboletim   = new cl_obsboletim;
$sSqlObsBoletim = $clobsboletim->sql_query("", "ed252_t_mensagem", "", "ed252_i_escola = {$escola}");
$resultobs      = $clobsboletim->sql_record($sSqlObsBoletim);

if ($clobsboletim->numrows > 0) {
    $obs1 = db_utils::fieldsMemory($resultobs, 0)->ed252_t_mensagem;
}

$global[$notasomada] = '';
//$segboletim = false;
$segboletim = true;


$sSqlTurma = $clturma->sql_query_turmaserie("", "ed57_i_codigo as turma", "", " ed220_i_codigo = {$turma}");
$result00  = $clturma->sql_record($sSqlTurma);
db_fieldsmemory($result00, 0);

$oTurma                    = TurmaRepository::getTurmaByCodigo($turma);
$lPermiteProporcionalidade = false;
foreach ($oTurma->getDisciplinas() as $oRegencia) {

    foreach ($oRegencia->getProcedimentoAvaliacao()->getElementos() as $oElemento) {

        if ($oElemento instanceof ResultadoAvaliacao && $oElemento->utilizaProporcionalidade()) {
            $lPermiteProporcionalidade = true;
        }
    }
}

$sParagrafoProporcionalidade = "";

if ($lPermiteProporcionalidade) {

    $aEtapas     = $oTurma->getEtapas();
    $iTipoEnsino = $aEtapas[0]->getEtapa()->getEnsino()->getCodigoTipoEnsino();

    if ($iTipoEnsino == 1) {

        $oDocumento  = new libdocumento(5017);
        $aParagrafos = $oDocumento->getDocParagrafos();

        $sParagrafoProporcionalidade = $aParagrafos[1]->oParag->db02_texto;
    } elseif ($iTipoEnsino == 3) {

        $oDocumento  = new libdocumento(5018);
        $aParagrafos = $oDocumento->getDocParagrafos();

        $sParagrafoProporcionalidade = $aParagrafos[1]->oParag->db02_texto;
    }
}

$sOrdenacaoMatricula = "ed60_i_numaluno, to_ascii(ed47_v_nome)";
$sWhereMatricula     = " ed60_i_codigo in ({$alunos}) AND ed60_i_turma = {$turma}";
$sSqlMatricula       = $clmatricula->sql_query("", "*", $sOrdenacaoMatricula, $sWhereMatricula);
$result              = $clmatricula->sql_record($sSqlMatricula);


if ($clmatricula->numrows == 0) { ?>

    <table width='100%'>
        <tr>
            <td align='center'>
                <font color='#FF0000' face='arial'>
                    <b>Nenhuma matrícula para a turma selecionada<br>
                        <input type='button' value='Fechar' onclick='window.close()'></b>
                </font>
            </td>
        </tr>
    </table>
<?php
    exit;
}

$sCamposProcAvaliacao = "ed09_i_codigo as periodoParecer, ed09_c_descr as periodoselecionado, ed41_i_sequencia as seqatual";
$sSqlProcAvaliacao    = $clprocavaliacao->sql_query("", $sCamposProcAvaliacao, "", "ed41_i_codigo = {$periodo}");
$periodoaval = $periodo;
@$GLOBALS["HTTP_POST_VARS"]["PeriodoFalta"] = $periodo;
$result_per           = $clprocavaliacao->sql_record($sSqlProcAvaliacao);
db_fieldsmemory($result_per, 0);

$sqlP = "
        select
		ed09_i_codigo
		from
	    procavaliacao
		inner join periodoavaliacao on ed09_i_codigo  = ed41_i_periodoavaliacao
		where
		ed41_i_codigo = " . $periodo;
$resultadoP = db_query($sqlP);

db_fieldsmemory($resultadoP);

/**
 * Dados Instituição
 */
$sCamposInstit   = "nomeinst as nome, ender, munic, uf, telef, email, url, logo";
$sSqlDadosInstit = $clDBConfig->sql_query_file(db_getsession('DB_instit'), $sCamposInstit);
$rsDadosInstit   = db_query($sSqlDadosInstit);
$oDadosInstit    = db_utils::fieldsMemory($rsDadosInstit, 0);
$url             = $oDadosInstit->url;
$nome            = $oDadosInstit->nome;
$sLogoInstit     = $oDadosInstit->logo;
$munic           = $oDadosInstit->munic;



/**
 * Dados Escola
 */
$sCamposEscola     = "ed18_i_codigo, ed18_c_nome, j14_nome, ed18_i_numero, j13_descr, ed261_c_nome, ed260_c_sigla, ";
$sCamposEscola    .= "ed18_c_email, ed18_c_logo, ed18_codigoreferencia";
$sSqlDadosEscola   = $clEscola->sql_query_dados(db_getsession("DB_coddepto"), $sCamposEscola);
$rsDadosEscola     = db_query($sSqlDadosEscola);
$oDadosEscola      = db_utils::fieldsMemory($rsDadosEscola, 0);
$sNomeEscola       = $oDadosEscola->ed18_c_nome;
$sLogoEscola       = $oDadosEscola->ed18_c_logo;
$iCodigoEscola     = $oDadosEscola->ed18_i_codigo;
@$GLOBALS["HTTP_POST_VARS"]["CodEscola"] = $iCodigoEscola;
$ruaescola         = $oDadosEscola->j14_nome;
$numescola         = $oDadosEscola->ed18_i_numero;
$bairroescola      = $oDadosEscola->j13_descr;
$cidadeescola      = $oDadosEscola->ed261_c_nome;
$estadoescola      = $oDadosEscola->ed260_c_sigla;
$emailescola       = $oDadosEscola->ed18_c_email;
$iCodigoReferencia = $oDadosEscola->ed18_codigoreferencia;

if ($iCodigoReferencia != null) {
    $sNomeEscola = "{$iCodigoReferencia} - {$sNomeEscola}";
}

$sSqlTelefoneEscola = $clEscola->sql_query_telefone("", "ed26_i_numero,ed26_i_ddd", "", "ed26_i_escola = {$escola}");
$rsTelefoneEscola   = db_query($sSqlTelefoneEscola);
$oTelefoneEscola    = db_utils::fieldsMemory($rsTelefoneEscola, 0);
$iTelefoneEscola    = $oTelefoneEscola->ed26_i_numero;
$iDTelefone         = $oTelefoneEscola->ed26_i_ddd;

$DadosCabecalho = $sNomeEscola . " (" . $iDTelefone . ")" . $iTelefoneEscola;

$sCamposRegenteConselho = " CASE WHEN cgmcgm.z01_nome <> '' THEN cgmcgm.z01_nome ELSE cgmrh.z01_nome END as conselheiro";
$sSqlRegenteConselho    = $clregenteconselho->sql_query("", $sCamposRegenteConselho, "", "ed235_i_turma = {$turma}");
$result6 = $clregenteconselho->sql_record($sSqlRegenteConselho);

if ($clregenteconselho->numrows > 0) {
    db_fieldsmemory($result6, 0);
} else {
    $conselheiro = "";
}


$pdf = new scpdf();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->ln(5);
$pdf->SetAutoPageBreak(true, 2);

$sLogoInstit         = $oTurma->getEscola()->getLogo();
$sLogoEscola         = $oTurma->getEscola()->getLogoEscola();





if (strlen($nome) > 42 || strlen($sNomeEscola) > 42) {
    $TamFonteNome = 8;
} else {
    $TamFonteNome = 9;
}

$y1         = 9;
$y2         = 14;
$y3         = 18;
$y4         = 22;
$y5         = 26;
$y6         = 30;
$y7         = 6;
$y8         = 6;
$y9         = 35;
$y10        = 33;
$y11        = 63;
$y12        = 3;
$y13        = 12;
$y14        = 35;
$m0         = 9;
$m1         = 14;
$m2         = 18;
$m3         = 22;
$m4         = 26;
$m5         = 30;
$m6         = 33;
$m7         = 3;
$m8         = 5;
$a          = 5;
$f          = 195;
$r          = 33;
$alturahead = $pdf->sety(6);

for ($x = 0; $x < $clmatricula->numrows; $x++) {

    db_fieldsmemory($result, $x);
    $ed47_v_nome = is_null($ed47_v_nomesocial) || empty($ed47_v_nomesocial) ? $ed47_v_nome : $ed47_v_nomesocial;
    $head1    = "BOLETIM DE DESEMPENHO {$periodoselecionado}";
    $head2    = "Nome: {$ed47_v_nome}";
    $head3    = "";
    $head4    = "Matrícula: {$ed60_i_codigo}";

    @$GLOBALS["HTTP_POST_VARS"]["MatriculaFalta"] = $ed60_i_codigo;

    $head5    = "Etapa: {$ed11_c_descr} Ano: {$ed52_i_ano}";
    $head6    = "Turma: {$ed57_c_descr}";

    @$GLOBALS["HTTP_POST_VARS"]["nomeTurma"] = substr($ed57_c_descr, 0, 3);
    @$GLOBALS["HTTP_POST_VARS"]["AnoBol"]    = $ed52_i_ano;



    /**
     *  variaveis utilizada por petropolis
     */
    $ed60_i_numero = $ed60_i_numaluno;
    $ed47_i_dtnasc = $ed47_d_nasc;

    /** fim */

    if (($x % 2) == 0 || $pdf->getAvailHeight() < (($pdf->h / 2) - 20)) {

        $pdf->addpage('P');
        $y1         =  9;     //$y1  9      $y1
        $y2         = 14;     //$y2  14     $y2
        $y3         = 18;     //$y3  18     $y3
        $y4         = 22;     //$y4  22     $y4
        $y5         = 26;     //$y5  26     $y5
        $y6         = 30;     //$y6  30     $y6
        $y7         =  6;     //$y7  6      $y7
        $y8         =  6;     //$y8  5      $y8 -1
        $y9         = 35;     //$y9  35
        $y10        = 33;     //$y10 33
        $y11        = 63;     //$y11 63
        $y12        =  3;     //$y12 3
        $y13        = 12;     //$y13 12
        $y14        = 30;     //$y14 30
        $m0         = 9;      //$m0  160;   $m0 + 150;
        $m1         = 14;     //$m1  163;   $m1 + 150;
        $m2         = 18;     //$m2  167;   $m2 + 150;
        $m3         = 22;     //$m3  170;   $m3 + 150;
        $m4         = 26;     //$m4  173;   $m4 + 150;
        $m5         = 30;     //$m5  176;   $m5 + 150;
        $m6         = 33;     //$m6  180;   $m6 + 150;
        $m7         = 3;      //$m7         $m7;
        $m8         = 5;      //$m8  152;   $m8 + 150;
        $a          = 5;      //$a   5
        $f          = 195;    //$f   195;
        $r          = 33;     //$r   33;
        $alturahead = $pdf->SetY(6);
    }

    $margemesquerda = $pdf->lMargin;

    $pdf->setfillcolor(223);
    $pdf->SetFont('arial', 'b', 6);
    $pdf->SetY($y8);
    $oLibDocumento = new libdocumento(5001, null);

    if ($oLibDocumento->lErro) {
        db_redireciona("db_erros.php?fechar=true&db_erro={$oLibDocumento->sMsgErro}");
    }

    $sSqlTelefoneEscola = "select ed26_i_numero
                           from telefoneescola
                          where ed26_i_escola = " . db_getsession("DB_coddepto") . "
                          limit 1";

    $rsTelefoneEscola   = db_query($sSqlTelefoneEscola);

    if (pg_num_rows($rsTelefoneEscola) > 0) {
        $telefoneescola = db_utils::fieldsMemory($rsTelefoneEscola, 0)->ed26_i_numero;
    } else {
        $telefoneescola = "";
    }

    if ($segboletim) {
        $pdf->Image('imagens/files/' . $sLogoInstit, 7, $pdf->GetY(), 20);
        if (trim($sLogoEscola) != "") {
            $pdf->Image('imagens/' . $sLogoEscola, 107, $pdf->GetY(), 20);
        }

        // Código de impressão do cabeçalho comentado para evitar duplicação (Demanda 17897)
        $pdf->SetFont('Arial', 'BI', 8);
        $pdf->Text(33, $m0, 'ESTADO DO RIO DE JANEIRO');
        $pdf->Text(33, $m0 + 3, 'PREFEITURA MUNICIPAL DE VOLTA REDONDA');
        $pdf->Text(33, $m0 + 6, 'ESTADO DO RIO DE JANEIRO');
        $pdf->Text(33, $m1 + 6, $sNomeEscola);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Text(33, $m2 + 6, $ruaescola . ", " . $numescola . " - " . $bairroescola);
        $pdf->Text(33, $m3 + 6, $cidadeescola . " - " . $estadoescola);
        $pdf->Text(33, $m4 + 6, $telefoneescola);
        // $segboletim = false;
        $segboletim = true;
    }
    $comprim = ($pdf->w - $pdf->rMargin - $pdf->lMargin);
    //    $pdf->Text(33,$m5,($emailescola!=""?$emailescola." - ":"").$url);
    $Espaco = $pdf->w - 80;
    $pdf->SetFont('Arial', '', 7);
    $pdf->setleftmargin($Espaco);
    $alturahead;
    $pdf->setfillcolor(235);
    $pdf->roundedrect($Espaco - 3, $m8, 75, 28, 2, 'DF', '123');
    $pdf->line(10, $m6, $comprim, $m6);
    $pdf->cell(50, 2, "", 0, 1);
    $pdf->setfillcolor(255);
    $pdf->cell(100, 2, @$head1, 0, 1);
    $pdf->multicell(0, $m7, @$head2, 0, 1, "J", 0);
    $pdf->multicell(0, $m7, @$head3, 0, 1, "J", 0);
    $pdf->multicell(0, $m7, @$head4, 0, 1, "J", 0);
    $pdf->multicell(0, $m7, @$head5, 0, 1, "J", 0);
    $pdf->multicell(0, $m7, @$head6, 0, 1, "J", 0);
    $pdf->multicell(0, $m7, @$head7, 0, 1, "J", 0);
    $pdf->multicell(0, $m7, @$head8, 0, 1, "J", 0);
    $pdf->setleftmargin($margemesquerda);

    $pdf->Ln(2);



    // Implementado por Wanderlei Silva do Carmo
    //informações adicionais do boletim


    $iTurma = $_GET['turma'];
    $iEscola = $escola;


    $infoObs = $clobsboletim->lerObsAdicional($iEscola, $iTurma);

    $obsB = mb_convert_encoding($infoObs['obs'], "ISO-8859-1","UTF-8");
    $impObs = $infoObs['imprimirObservacao'];

    $linhaX = $pdf->getX();
    $colY = $pdf->getY();

    if ( $impObs == "s" || $impObs == "S"){
        $pdf->setY($colY + 40);
	    $pdf->setX(-55);
        $pdf->setfont('arial', 'b', 6);
        $pdf->cell(40, 4, 'Observação', 0, 1,"C", 0);
        $pdf->setX(-55);
        $pdf->setfont('arial', '', 6);
        $pdf->multicell(40, 10, $obsB, 1, "C", 0, 0);
    }

    $pdf->setX($linhaX);
    $pdf->setY($colY);





    $pdf->setleftmargin($margemesquerda);
    //**********************************************************************************************************************************
    // Verifica se o aluno tem necessidades especiais e é avaliado por parecer
    /*
	$sql_ne = 'select
			   ed214_i_aluno
			   from
			   escola.alunonecessidade
			   inner join matricula on  ed60_i_aluno = ed214_i_aluno
               inner join diario    on ed95_i_codigo = ed72_i_diario
			   where
			   ed60_i_codigo = '.$ed60_i_codigo;

	$rs_ne  = db_query($sql_ne);
	if( pg_num_rows($rs_ne) > 0)
	{
	   $grade = "no";
	}
*/
    if ($padrao == 'yes') {
        $grade = "no";
    }

    //**********************************************************************************************************************************
    if ($grade == "yes") {
        $matricula = MatriculaRepository::getMatriculaByCodigo($ed60_i_codigo);
        $sqlperiodo = "
			SELECT
			distinct on (ed41_i_codigo)
			ed41_i_codigo,
			ed09_c_descr,
			ed41_i_sequencia,
			case when ed41_i_codigo>0 then 'A' end as tipo,
			ed37_c_tipo
			FROM
			procavaliacao
			inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao
			inner join formaavaliacao   on formaavaliacao.ed37_i_codigo   = procavaliacao.ed41_i_formaavaliacao
			inner join turma            on ed57_i_escola                  = ed37_i_escola
			WHERE
			ed41_i_codigo = " . $periodoaval;

        $rsperiodo = db_query($sqlperiodo);
        $operiodo  = db_utils::fieldsMemory($rsperiodo, 0);


        $oGrade = new RelatorioGradeAproveitamento($pdf, $matricula, 191, "", $operiodo->ed09_c_descr);

        @$GLOBALS["HTTP_POST_VARS"]["calendarioF"]    = $calendario;
        $oGrade->montarGrade(null, $ed11_c_descr, null, $calendario);
    } else {
        //       $padrao = 'yes';
    }


    $sWhere              = " ed95_i_aluno = {$ed60_i_aluno} AND ed59_i_turma = {$turma} AND ed72_i_procavaliacao = {$periodo}";
    $sSqlDiarioAvaliacao = $cldiarioavaliacao->sql_query("", "ed232_c_descr, ed72_t_obs", "", $sWhere);
    $result_obs           = $cldiarioavaliacao->sql_record($sSqlDiarioAvaliacao);

    $sWhereDiarioRegra = " ed95_i_aluno = {$ed60_i_aluno} AND ed59_i_turma = {$turma} ";
    $oDaoDiarioRegra   = new cl_diarioregracalculo();
    $sSqlDiarioRegra   = $oDaoDiarioRegra->sql_query(null, "ed125_diario", null, $sWhereDiarioRegra);
    $rsDiarioRegra     = $oDaoDiarioRegra->sql_record($sSqlDiarioRegra);

    $sObsProporcionalidadeAluno = "";
    if ($oDaoDiarioRegra->numrows > 0) {
        $sObsProporcionalidadeAluno = $sParagrafoProporcionalidade;
    }

    $ed72_t_obs   = "";
    $aObservacoes = array();

    for ($iContador = 0; $iContador < pg_num_rows($result_obs); $iContador++) {

        $oDadosObservacao = db_utils::fieldsMemory($result_obs, $iContador);

        if (!empty($oDadosObservacao->ed72_t_obs)) {
            $aObservacoes[] = "{$oDadosObservacao->ed232_c_descr}: {$oDadosObservacao->ed72_t_obs}";
        }
    }

    $ed72_t_obs = implode(". ", $aObservacoes);


    $impresso = false;
    if ($padrao == "yes") {

        // alunos avaliados por parecer
        $Aval = "
		select
		distinct on (ed232_c_descr)
		ed232_c_descr as discipl,
		ed12_i_codigo
		from
		diarioavaliacao
		inner join diario        on ed95_i_codigo  = ed72_i_diario
		inner join regencia      on ed59_i_codigo  = ed95_i_regencia
		inner join disciplina    on ed12_i_codigo  = ed59_i_disciplina
		inner join caddisciplina on ed232_i_codigo = ed12_i_caddisciplina
		where
		ed59_i_turma = " . $turma . "
		and
		ed95_i_aluno =" . $ed60_i_aluno;


        $rsAval = db_query($Aval);


        if (pg_num_rows($rsAval) > 0) {
            $pdf->setfont('arial', 'b', 7);

            if ($_GET["impfrequencia"] == 'yes') {
                $imprimeFreq = true;
            } else {
                $imprimeFreq = false;
            }

            if ($imprimeFreq) {
                $quantAulas = percfrequencia($calendario);
                $quantFaltas = faltasPeriodo($ed60_i_codigo, $periodo, @$GLOBALS["HTTP_POST_VARS"]["CodEscola"]);
                $percFreq   = floor(($quantAulas - $quantFaltas) / $quantAulas * 100);
                $pdf->cell(191, 4, "Parecer {$periodoselecionado}:" . "                  Quant. faltas: " . $quantFaltas . "           Percentual de Frequencia: " . $percFreq . "%", 1, 1, "L", 1);
                $pdf->setfont('arial', '', 7);
            } else {
                $pdf->cell(191, 4, "Parecer {$periodoselecionado}" . $percFreq, 1, 1, "L", 1);
                $pdf->setfont('arial', '', 7);
            }

            if ($padraotipo == "L") {
                $pdf->cell(195, 4, "Seq - Parecer => Legenda", 1, 1, "L", 0);
            }
        }
        for ($a = 0; $a < pg_num_rows($rsAval); $a++) {
            db_fieldsmemory($rsAval, $a);

            $sSqlParecerAval = "
        select
        ed72_t_parecer
        from
        diarioavaliacao
        inner join diario           on ed95_i_codigo  = ed72_i_diario
        inner join regencia         on ed59_i_codigo  = ed95_i_regencia
        inner join disciplina       on ed12_i_codigo  = ed59_i_disciplina
        inner join caddisciplina    on ed232_i_codigo = ed12_i_caddisciplina
        inner join procavaliacao    on ed41_i_codigo  = ed72_i_procavaliacao
        inner join periodoavaliacao on ed09_i_codigo  = ed41_i_periodoavaliacao
        where
        ed59_i_turma = " . $turma . "
        and
        ed95_i_aluno =" . $ed60_i_aluno . "
        and
        trim(ed72_t_parecer) <> ''
        and
        ed09_i_codigo = " . $ed09_i_codigo . "
        and
        ed232_c_descr = '" . $discipl . "'";

            $result_par      =  $clpareceraval->sql_record($sSqlParecerAval);


            $seq       = "";
            $sep       = "";
            $parpadrao = "";
            $seppadrao = "";
            $pdf->cell(191, 4, "", "", 1, "L", 1);
            $pdf->cell(191, 4, $discipl, "", 1, "L", 1);

            for ($g = 0; $g < $clpareceraval->numrows; $g++) {

                db_fieldsmemory($result_par, $g);
                if (!strstr($seq, "#" . $ed72_t_parecer . "#")) {
                    $parpadrao .= $seppadrao . $ed72_t_parecer;
                    $seq       .= $sep . "#" . $ed72_t_parecer . "#";
                    $sep        = ",";
                    $seppadrao  = "    ";
                }
            }
            if ($padraotipo == "C") {
                $pdf->multicell($oDadosGrade->nLarguraGrade, 4, str_replace("**", "  ", $parpadrao), 1, "J", 0, 0);
            }
            $impresso = true;
        }
        $pdf->cell($oDadosGrade->nLarguraGrade, 4, "", 0, 1, "L", 1);
    }

    if ($descritivo == "yes") {

        $sWhere          = " ed95_i_aluno = {$ed60_i_aluno} AND ed59_i_turma = {$turma} ";
        $sWhere         .= " AND ed72_t_parecer != '' AND ed41_i_sequencia = {$seqatual}";

        $sSqlDiarioAvaliacao = $cldiarioavaliacao->sql_query("", "DISTINCT ed72_t_parecer as pardescr", "", $sWhere);
        $result_pardescr     = $cldiarioavaliacao->sql_record($sSqlDiarioAvaliacao);

        if ($cldiarioavaliacao->numrows > 0) {

            $pardescr = trim(pg_result($result_pardescr, 0, 'pardescr'));

            if ($pardescr != "") {

                $pdf->setfont('arial', 'b', 7);
                $pdf->cell($oDadosGrade->nLarguraGrade, 4, "Parecer {$periodoselecionado}:", 1, 1, "L", 1);
                $pdf->setfont('arial', '', 7);

                for ($g = 0; $g < $cldiarioavaliacao->numrows; $g++) {

                    db_fieldsmemory($result_pardescr, $g);
                    $pdf->multicell($oDadosGrade->nLarguraGrade, 4, $pardescr, 1, "L", 0, 0);
                }

                $impresso = true;
            }
        }
    }

    $obs2 = "";

    if (isset($boletim_convencao)) {

        if (trim($boletim_convencao) != "") {

            $arr_convencao = str_replace("{", "", $boletim_convencao);
            $arr_convencao = str_replace("}", "", $arr_convencao);
            $troca         = chr(34) . "," . chr(34);
            $arr_convencao = str_replace($troca, " - ", $arr_convencao);
            $arr_convencao = str_replace(chr(34), "", $arr_convencao);
            $obs2          = $arr_convencao;
        }
    }

    $obs3         = "";
    $sCampos      = "cgmrh.z01_nome, ed253_i_data, ed232_c_descr as disc_conselho, ed253_t_obs, ed59_i_ordenacao, ed52_i_ano,";
    $sCampos     .= " ed122_sequencial, ed122_descricao, ed11_c_descr as etapa, ed253_alterarnotafinal,  ed253_avaliacaoconselho";
    $sWhere       = " ed95_i_aluno = {$ed60_i_aluno} AND ed59_i_turma = {$turma} AND ed59_i_serie = {$ed221_i_serie}";

    $sSqlAprovConselho = $claprovconselho->sql_query("", $sCampos, "ed59_i_ordenacao", $sWhere);
    $result_cons       = @$claprovconselho->sql_record($sSqlAprovConselho);

    $aAprovadoBaixaFrequencia   = array();
    $aAprovadoConselhoRegimento = array();
    $sObservacaoConselho        = '';
    if ($claprovconselho->numrows > 0) {

        for ($g = 0; $g < $claprovconselho->numrows; $g++) {

            db_fieldsmemory($result_cons, $g);
            $oDadosAprovConselho = db_utils::fieldsMemory($result_cons, $g);

            switch ($oDadosAprovConselho->ed122_sequencial) {

                /**
                 * Valida se a aprovação foi por conselho
                 * Utiliza a justificativa cadastrada em ed253_t_obs
                 */
                case 1:

                    $sJustificativaConselho = trim($oDadosAprovConselho->ed253_t_obs);
                    if (!empty($sJustificativaConselho)) {
                        $aAprovadoConselhoRegimento[] = "- Justificativa ({$oDadosAprovConselho->disc_conselho}): {$sJustificativaConselho}";
                    }

                    break;

                /**
                 * Valida se a aprovação foi por baixa frequência (reclassificação)
                 * Utiliza a justificativa cadastrada em ed253_t_obs
                 */
                case 2:

                    $sJustificativaFrequencia = trim($oDadosAprovConselho->ed253_t_obs);
                    if (!empty($sJustificativaFrequencia)) {
                        $aAprovadoBaixaFrequencia[] = "- Justificativa ({$oDadosAprovConselho->disc_conselho}): {$sJustificativaFrequencia}";
                    }

                    break;

                /**
                 * Valida se a aprovação foi por regimento escolar
                 * Utiliza a justificativa cadastrada em ed253_t_obs
                 */
                case 3:

                    $sJustificativaRegimento = trim($oDadosAprovConselho->ed253_t_obs);
                    if (!empty($sJustificativaRegimento)) {
                        $sObservacao = "- Justificativa ({$oDadosAprovConselho->disc_conselho}): {$sJustificativaRegimento}";
                    } else {
                        $sObservacao = "- Disciplina {$oDadosAprovConselho->disc_conselho}: Aprovado conforme regimento escolar.";
                    }
                    $aAprovadoConselhoRegimento[] = $sObservacao;

                    break;
            }
        }

        if (count($aAprovadoBaixaFrequencia) > 0) {

            foreach ($aAprovadoBaixaFrequencia as $sObsBaixaFrequencia) {
                $sObservacaoConselho .= "{$sObsBaixaFrequencia}\n";
            }
        }

    }

    $sObservacaoConselho .= implode("\n", $aAprovadoConselhoRegimento);



    if (
        trim($ed60_t_obs) != ""
        || trim($ed72_t_obs) != ""
        || trim(str_replace(chr(92), "", $obs1)) != ""
        || trim($obs2) != ""
        || trim($sObservacaoConselho) != ""
        || trim($sObsProporcionalidadeAluno) != ''
    ) {

        $pdf->setfont('arial', 'b', 7);
        $pdf->cell($oDadosGrade->nLarguraGrade, 4, "Observações / Mensagens", 1, 1, "L", 1);
        $pdf->setfont('arial', '', 7);

        $ed60_t_obs = substr($ed60_t_obs,          0, 270);
        $ed72_t_obs = substr($ed72_t_obs,          0, 270);
        $obs2 = substr($obs2, 0, 350);
        $obs3 = $sObservacaoConselho;
        $obs4 = substr($sObsProporcionalidadeAluno, 0, 150);


        $pdf->multicell($oDadosGrade->nLarguraGrade, 4, ($ed60_t_obs != "" ? $ed60_t_obs . "\n" : "") .
            ($ed72_t_obs != "" ? $ed72_t_obs . "\n" : "") .
            (str_replace(chr(92), "", $obs1) != "" ? str_replace(chr(92), "", $obs1) . "\n" : "") .
            ($obs2 != "" ? $obs2 . "\n" : "") .
            ($obs3 != "" ? $obs3 . "\n" : "") .
            ($obs4 != "" ? $obs4 . "\n" : ""), 1, "L", 0, 0);

        $impresso = true;
    }

    if ($assinaturaregente == "S") {
        $pdf->cell($oDadosGrade->nLarguraGrade, 7, "", "LTR",  1, "C", 0);
        $Data = date("d/m/Y");
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "Volta Redonda,   {$Data}",              "LR", 1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "", "LR",  1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "__________________________________________________", "LR",  1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "Professor(a)  {$conselheiro}",              "LR", 1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "", "LR",  1, "C", 0);
        if ($assresp <> "yes") {
            $pdf->cell($oDadosGrade->nLarguraGrade, 5, "", "LBR",  1, "C", 0);
        }
    }
    if ($assresp == "yes") {
        if ($assinaturaregente <> "S") {
            $pdf->cell($oDadosGrade->nLarguraGrade, 5, "", "LTR",  1, "C", 0);
            $Data = date("d/m/Y");
            $pdf->cell($oDadosGrade->nLarguraGrade, 5, "Volta Redonda,   {$Data}",              "LR", 1, "C", 0);
        }
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "__________________________________________________", "LR",  1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "Assinatura do responsável",              "LR", 1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "", "LR",  1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "Eu_________________________________________responsável pelo Aluno " . $ed47_v_nome . " recebi o boletim do ___ trimestre/_______",              "LBR", 1, "C", 0);
    }
    if ($assinaturaregente <> "S" and $assresp <> "yes") {
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "", "LR",  1, "C", 0);
        $Data = date("d/m/Y");
        $pdf->cell($oDadosGrade->nLarguraGrade, 5, "Volta Redonda,   {$Data}",              "LR", 1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 7, "", "LR",  1, "C", 0);
        $pdf->cell($oDadosGrade->nLarguraGrade, 3, "", "LBR", 1, "C", 0);
    }

    $iAdicionalSegundoBoletim = $pdf->GetY();

    if ($pdf->GetY() < $pdf->h / 2) {
        $iAdicionalSegundoBoletim = $pdf->h / 2;
        $segboletim = true;
    }
    $y1         += $iAdicionalSegundoBoletim;
    $y2         += $iAdicionalSegundoBoletim;
    $y3         += $iAdicionalSegundoBoletim;
    $y4         += $iAdicionalSegundoBoletim;
    $y5         += $iAdicionalSegundoBoletim;
    $y6         += $iAdicionalSegundoBoletim;
    $y7         += $iAdicionalSegundoBoletim;
    $y8         += $iAdicionalSegundoBoletim;
    $y9         += $iAdicionalSegundoBoletim;
    $y10        += $iAdicionalSegundoBoletim;
    $y11        += $iAdicionalSegundoBoletim;
    $y12        += $iAdicionalSegundoBoletim;
    $y13        += $iAdicionalSegundoBoletim;
    $y14        += $iAdicionalSegundoBoletim;
    $m0         += $iAdicionalSegundoBoletim;
    $m1         += $iAdicionalSegundoBoletim;
    $m2         += $iAdicionalSegundoBoletim;
    $m3         += $iAdicionalSegundoBoletim;
    $m4         += $iAdicionalSegundoBoletim;
    $m5         += $iAdicionalSegundoBoletim;
    $m6         += $iAdicionalSegundoBoletim;
    $m8         += $iAdicionalSegundoBoletim;
    $a          += $iAdicionalSegundoBoletim;
    $alturahead = $pdf->SetY($pdf->GetY());
}

function percfrequencia($calendar)
{
    $sql   = pg_query("select
	                   ed52_c_descr,
					   ed15_c_nome
					   from
					   calendario
					   inner join turma on ed57_i_calendario = ed52_i_codigo
					   inner join turno on ed15_i_codigo     = ed57_i_turno
					   where ed52_i_codigo = " . $calendar);
    $resultado = pg_fetch_all($sql);
    $nome  = $resultado[0]["ed52_c_descr"];
    $turno = substr($resultado[0]["ed15_c_nome"], 0, 5);
    $turno_completo = trim($resultado[0]["ed15_c_nome"]);
    if (substr($nome, 0, 12) == 'ED. INFANTIL' or substr($nome, 0, 17) == 'EDUCAï¿½ï¿½O INFANTIL') {
        $auladadas = 200;
    } elseif (substr($nome, 0, 13) == 'ANOS INICIAIS' or substr($nome, 0, 20) == 'EN FUN ANOS INICIAIS') {
        $auladadas = 200;
    } elseif (substr($nome, 0, 11) == 'ANOS FINAIS' or substr($nome, 0, 18) == 'EN FUN ANOS FINAIS') {
        // Autor: Uemerson Santana | Data: 19/01/2026 | Demanda: 18059
        // Razão: Para Anos Finais, verificar se o turno é INTEGRAL para aplicar 1522 horas/aula
        //        ao invés de 1000 horas/aula fixas. Turno INTEGRAL tem carga horária maior.
        if (strtoupper($turno_completo) == 'INTEGRAL') {
            $auladadas = 1522;
        } else {
            $auladadas = 1000;
        }
    } elseif (substr($nome, 0, 17) == 'EJA ANOS INICIAIS' or substr($nome, 0, 12) == 'EJA INICIAIS') {
        $auladadas = 170;
    }

    if ($turno <> 'NOITE' and (substr($nome, 0, 15) == 'EJA ANOS FINAIS' or substr($nome, 0, 10) == 'EJA FINAIS')) {
        $auladadas = 1200;
    }

    if ($turno == 'NOITE' and (substr($nome, 0, 15) == 'EJA ANOS FINAIS' or substr($nome, 0, 10) == 'EJA FINAIS')) {
        $auladadas = 1000;
    }
    return $auladadas;
}

function faltasPeriodo($aluno, $periodoaval, $escola)
{
    $sql = pg_query("
					select
					sum(ed72_i_numfaltas) as falta
					from
					diarioavaliacao
					inner join procavaliacao    on ed41_i_codigo = ed72_i_procavaliacao
					inner join periodoavaliacao on ed09_i_codigo = ed41_i_periodoavaliacao
					inner join diario           on ed95_i_codigo = ed72_i_diario
					inner join calendario       on ed52_i_codigo = ed95_i_calendario and ed52_i_ano = " . db_getsession("DB_anousu") . "
					inner join aluno            on ed47_i_codigo = ed95_i_aluno
					inner join matricula        on ed60_i_aluno  = ed47_i_codigo
					where
					ed60_i_codigo = " . $aluno . "
					and
					ed41_i_codigo <= " . $periodoaval . "
					and
			        ed72_i_escola = " . $escola);

    $resultado   = pg_fetch_all($sql);
    $quantFalta  = $resultado[0]["falta"];
    return $quantFalta;
}

$pdf->Output();
