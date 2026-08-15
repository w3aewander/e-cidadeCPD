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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification( "fpdf151educacao/pdf.php" ));

$oGet                = db_utils::postmemory( $_GET );

$oTurma              = TurmaRepository::getTurmaByCodigo( $oGet->iTurma );
$oEtapa              = EtapaRepository::getEtapaByCodigo( $oGet->iEtapa );
$oAvaliacaoPeriodica = AvaliacaoPeriodicaRepository::getAvaliacaoPeriodicaByCodigo( $oGet->iPeriodo );
$oPeriodoAvaliacao   = $oAvaliacaoPeriodica->getPeriodoAvaliacao();
$oPeriodoCalendario  = $oTurma->getCalendario()->getPeriodoCalendarioPorPeriodoAvaliacao($oPeriodoAvaliacao);

$sDataInicio         = $oPeriodoCalendario->getDataInicio()->convertTo(DBDate::DATA_PTBR);
$sDataFim            = $oPeriodoCalendario->getDataTermino()->convertTo(DBDate::DATA_PTBR);

$oCalendario         = $oGet->iCalendario;
$oDisciplina         = $oGet->iDisciplina;
$turma               = $oGet->iTurma;
$periodo             = $oGet->iPeriodo;
$periodon            = $oGet->nPeriodo;
$aAdicion            = $oGet->nAdicion;
$cgmAdicion          = $oGet->iAdicion;

$odadost = db_utils::fieldsMemory(pg_query("select ed57_c_descr  from turma         where ed57_i_codigo  = ".$turma),0) ;
$odadosd = db_utils::fieldsMemory(pg_query("select ed232_c_descr from caddisciplina where ed232_i_codigo = ".$oDisciplina),0) ;
$odadosc = db_utils::fieldsMemory(pg_query("select ed52_c_descr  from calendario    where ed52_i_codigo  = ".$oCalendario),0) ;
$odadose = db_utils::fieldsMemory(pg_query("select descrdepto     from db_depart    where coddepto       = ".db_getsession("DB_coddepto")),0) ;

$sqlper = "
			SELECT
			ed09_c_descr
			FROM
			escola.periodocalendario
			inner join periodoavaliacao on ed09_i_codigo = ed53_i_periodoavaliacao
			where
			ed53_i_codigo = {$periodo}
          ";
$odadosp  =	db_utils::fieldsMemory(pg_query($sqlper),0);

/**
 * Autor: Uemerson Santana
 * Data: 11/04/2025
 * Demanda: 17313
 */
$oDaoRegenteConselho = db_utils::getDao("regenteconselho");
    $sCampos = "cgmrh.z01_nome as nome";
    $sWhere = "ed235_i_turma = {$oGet->iTurma}";
    $sSqlRegenteConselho = $oDaoRegenteConselho->sql_query(null, $sCampos, null, $sWhere);

$oregen = db_utils::fieldsMemory(pg_query($sSqlRegenteConselho),0);

$nTurma = $odadost->ed57_c_descr;

try {
    $sql = "
	        select
			obs,
			obs2
			from
			public.regocorr
			where
			calendario = ".$oCalendario."
			and
			turma      = ".$turma."
			and
			periodo    = ".$periodo."
			and
			disciplina = ".$oDisciplina;

    $result = pg_query($sql);
    $head1 = "RELATÓRIO DE OBSERVAÇÕES";
	$head2 = "Escola    : ".$odadose->descrdepto;
	$head3 = "Turma     : ".$nTurma;
	$head4 = "Disciplina: ".$odadosd->ed232_c_descr;
	$head5 = "Calendário: ".$odadosc->ed52_c_descr;
	$head6 = "Periodo   : ".$oPeriodoAvaliacao->getDescricao();
	$head7 = "Regencia  : ".$oregen->nome;

	$linha = 0;
	$pdf = new PDF();
	$pdf->Open();
	$pdf->AliasNbPages();
	for($x = 0; $x <= pg_num_rows($result); $x++)
	{
        if($linha == 0 )
		{
			$pdf->Addpage('P');
			$pdf->setfillcolor(223);
	        $pdf->Cell(190, 4, "", 0, 1, 'C');
            /**
             * Autor: Uemerson Santana
             * Data: 11/04/2025
             * Demanda: 17313
             */
			$pdf->Cell(190, 4, $periodon.' '.$sDataInicio.' a '.$sDataFim, 0, 1, 'C');
	        $pdf->Cell(190, 4, "", 0, 1, 'C');
		}
		$oDados = db_utils::fieldsMemory($result,$x);
		$pdf->multicell( 190, 4, $oDados->obs, 1, "J", 0, 0 );
		$pdf->multicell( 190, 4, $oDados->obs2, 1, "J", 0, 0 );
		$linha++;
		if( $linha == 30)
		{
			$linha = 0;
		}
    }
	$pdf->Cell(190, 4, "", 0, 1, 'C');
	$pdf->Cell(190, 4, "", 0, 1, 'C');
    $pdf->Cell(2, 4, "", 0, 0, 'C');
    $pdf->Cell(63,4, "______________________________________", 0, 0, 'C');
	if($cgmAdicion > 0)
	{
		$pdf->Cell(2, 4, "", 0, 0, 'C');
		$pdf->Cell(63,4, "______________________________________", 0, 0, 'C');
	}else{
		$pdf->Cell(2, 4, "", 0, 0, 'C');
		$pdf->Cell(63,4, "                                      ", 0, 0, 'C');
	}
    $pdf->Cell(2, 4, "", 0, 0, 'C');
    $pdf->Cell(63,4, "______________________________________", 0, 1, 'C');

    $pdf->Cell(2, 4, "", 0, 0, 'C');
    $pdf->Cell(63,4, $oregen->nome, 0, 0, 'C');
    $pdf->Cell(2, 4, "", 0, 0, 'C');
	if($cgmAdicion > 0)
	{
		$pdf->Cell(63,4, $nAdicion, 0, 0, 'C');
		$pdf->Cell(2, 4, "", 0, 0, 'C');
	}else{
		$pdf->Cell(63,4, "", 0, 0, 'C');
		$pdf->Cell(2, 4, "", 0, 0, 'C');

	}

    /**
     * Autor: Uemerson Santana
     * Data: 11/04/2025
     * Demanda: 17313
     */
    $sqlDiretor = "SELECT
                        'DIRETOR' AS funcao,
                        CASE
                            WHEN ed20_i_tiposervidor = 1 THEN
                                cgmrh.z01_nome
                            ELSE
                                cgmcgm.z01_nome
                        END AS nome,
                        ed83_c_descr || ' n°: ' || ed05_c_numero :: VARCHAR AS descricao,
                        'D' AS tipo
                    FROM
                        escoladiretor
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
                        LEFT JOIN rhpessoalmov ON rh02_anousu = 2018
                        AND rh02_mesusu = 02
                        AND rh02_regist = rh01_regist
                        AND rh02_instit = 96
                        LEFT JOIN rhfuncao ON rhfuncao.rh37_funcao = rhpessoal.rh01_funcao
                        AND rh37_instit = rh02_instit
                        LEFT JOIN rechumanocgm ON rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo
                        LEFT JOIN cgm AS cgmcgm ON cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm
                    WHERE
                        ed254_i_escola = ".db_getsession("DB_coddepto")."
                        AND ed254_c_tipo = 'A'
                        AND ed01_i_funcaoadmin = 2";

    $odadosdir = db_utils::fieldsMemory(pg_query($sqlDiretor),0);

	$pdf->Cell(63,4, $odadosdir->nome, 0, 1, 'C');

    $pdf->Cell(2, 4, "", 0, 0, 'C');
    $pdf->Cell(63,4, "REGENTE", 0, 0, 'C');
    // $odadosdic = db_utils::fieldsMemory(pg_query("select
	//                                               distinct on (rh37_descr)
	// 											  rh37_descr
	// 											  from
	// 											  rechumanopessoal
	// 											  inner join rhpessoal       on rh01_regist = ed284_i_rhpessoal
	// 											  inner join cgm             on z01_numcgm  = rh01_numcgm
	// 											  inner join rhfuncao        on rh37_funcao = rh01_funcao
	// 											  where
	// 											  z01_numcgm = ".$cgmAdicion),0);



    $pdf->Cell(2, 4, "", 0, 0, 'C');
    /**
     * Uemerson Santana
     * Data: 05/06/2025
     * Demanda: 17465
     */
    $pdf->Cell(63,4, "SUPERVISOR EDUCACIONAL", 0, 0, 'C');
    // $pdf->Cell(63,4, $odadosdic->rh37_descr, 0, 0, 'C');
    $pdf->Cell(2, 4, "", 0, 0, 'C');
    $pdf->Cell(63,4, "DIRETOR(A) GERAL", 0, 1, 'C');


	$pdf->Output();


} catch ( Exception $oErro ) {

  $sMsg = urlencode($oErro->getMessage());
  db_redireciona('db_erros.php?fechar=true&db_erro=' . $sMsg);
}
