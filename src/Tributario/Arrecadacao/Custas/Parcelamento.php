<?php
/*
*     E-cidade Software Publico para Gestao Municipal
*  Copyright (C) 2017  DBselller Servicos de Informatica
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

namespace ECidade\Tributario\Arrecadacao\Custas;

use \Recibo;
use \cl_termo;
use \cl_termotaxaparc;

class Parcelamento
{
	public static function validaUsoDeCustas($iTipo)
	{
		  $lCustas = false;

      $iInstit = db_getsession("DB_instit");

      $oDaoTermoTaxaParc = new cl_termotaxaparc();
      $sSqlTermoTaxaParc = $oDaoTermoTaxaParc->sql_query(null, "*", null, "ar29_instit = {$iInstit}");
      $rsResult = $oDaoTermoTaxaParc->sql_record($sSqlTermoTaxaParc);

      if ($iTipo == 13 && $oDaoTermoTaxaParc->numrows > 0){
         $lCustas = true;
      }

      return $lCustas;
	}

	public static function processaRecibo(Recibo $oRecibo)
	{
        $iNumnov = $oRecibo->getNumpreRecibo();
		$iInstit = db_getsession("DB_instit");

		$oDaoTermoTaxaParc = new cl_termotaxaparc();
		$sSqlTermoTaxaParc = $oDaoTermoTaxaParc->sql_query(null, "*", null, "ar29_instit = {$iInstit}");
		$rsResult = $oDaoTermoTaxaParc->sql_record($sSqlTermoTaxaParc);

		if ($oDaoTermoTaxaParc->numrows > 0) {

			$aTermoTaxaParc = \db_utils::getCollectionByRecord($rsResult);

			$aDebitosRecibo = $oRecibo->getDebitosRecibo();

			foreach ($aDebitosRecibo as $oDebitosRecibo) {

				$oDaoArretipo = new \cl_arretipo();
				$sSqlArretipo = $oDaoArretipo->sql_query_file($oDebitosRecibo->k00_tipo);
				$rsResult = $oDaoArretipo->sql_record($sSqlArretipo);

			    if (!$rsResult) {
			      throw new \Exception("Erro ao buscar informações de tipo de debito.");
			    }

				$aArretipo = \db_utils::fieldsMemory($rsResult, 0);

				if ($aArretipo->k03_tipo == 13) {

					foreach ($aTermoTaxaParc as $oTermoTaxaParc) {

						if ($oTermoTaxaParc->ar29_numpar == $oDebitosRecibo->k00_numpar) {
					
					        $sSqlProcessoForo  = " select true                                                                                          ";
					        $sSqlProcessoForo .= "   from termoini                                                                                      ";
					        $sSqlProcessoForo .= "        inner join inicial on inicial.v50_inicial = termoini.inicial                                  ";
					        $sSqlProcessoForo .= "        inner join processoforoinicial on processoforoinicial.v71_inicial = inicial.v50_inicial       ";
					        $sSqlProcessoForo .= "        inner join processoforo on processoforo.v70_sequencial = processoforoinicial.v71_processoforo ";
					        $sSqlProcessoForo .= "  where processoforoinicial.v71_anulado = false                                                       ";
					        $sSqlProcessoForo .= "    and processoforo.v70_anulado = false                                                              ";
					        $sSqlProcessoForo .= "    and termoini.parcel = termo.v07_parcel limit 1                                                    ";

							$oDaoTermo = new cl_termo();
							$sSqlTermo = $oDaoTermo->sql_query_file(null, "({$sSqlProcessoForo}) as com_processo", null, " termo.v07_numpre = {$oDebitosRecibo->k00_numpre}");

							$rsResult = $oDaoTermo->sql_record($sSqlTermo);

						    if (!$rsResult) {
						      throw new \Exception("Erro ao buscar os dados de origem do parcelamento.");
						    }

							$aTermo = \db_utils::fieldsMemory($rsResult, 0);

							if (($aTermo->com_processo != 't' && $oTermoTaxaParc->ar36_debitoscomprocesso == 't') || ($aTermo->com_processo == 't' && $oTermoTaxaParc->ar36_debitossemprocesso == 't')) {
								continue;
							}

							$fValorDebito = 0;

							if ($oTermoTaxaParc->ar36_aplicajurosmulta == 't') {

						        $sSql  = " select sum(case when k00_hist <> 401 and k00_hist <> 400 and k00_hist <> 918 then k00_valor else 0 end) as valor_hist, ";
						        $sSql .= "        sum(case when k00_hist = 400 then k00_valor else 0 end) as valor_juros,                                         ";
						        $sSql .= "        sum(case when k00_hist = 401 then k00_valor else 0 end) as valor_multa,                                         ";
						        $sSql .= "        sum(case when k00_hist = 918 then k00_valor else 0 end) as valor_desconto                                       ";
						        $sSql .= "   from recibopaga                                                                                                      ";
						        $sSql .= "  where k00_numnov = {$iNumnov}                                                                                         ";
						        $sSql .= "    and k00_numpre = {$oDebitosRecibo->k00_numpre}                                                                      ";
						        $sSql .= "    and k00_numpar = {$oDebitosRecibo->k00_numpar}                                                                      ";

						        $rsValor = db_query($sSql); 

						        if (!$rsValor) {
						            throw new DBException("Não foi possivel obter o valor base de calculo das custas " . $sMensagem);
						        }

						        $oValor = pg_fetch_object($rsValor, 0);

						        $fValorDebito = $oValor->valor_juros + $oValor->valor_multa - $oValor->valor_desconto;
							}

							$oDaoTermo = new cl_termo();
							$sSqlTermo = $oDaoTermo->sql_query_file(null, "*", null, "v07_numpre = {$oDebitosRecibo->k00_numpre}");
							$rsTermo = $oDaoTermo->sql_record($sSqlTermo);

							$oTermo = pg_fetch_object($rsTermo, 0);

							$sSql  = " select sum(termoini.total) as valor                                                                       ";
							$sSql .= "   from termoini                                                                                           ";
							$sSql .= "  where termoini.parcel = (select v07_parcel from termo where v07_numpre = {$oDebitosRecibo->k00_numpre})  ";
							$sSql .= "    and termoini.inicial not in (select ti.inicial                                                         ";
							$sSql .= "                                   from termoini ti                                                        ";
							$sSql .= "                                        inner join termo on termo.v07_parcel = ti.parcel                   ";
							$sSql .= "                                        inner join recibopaga on recibopaga.k00_numpre = termo.v07_numpre  ";
							$sSql .= "                                        inner join disbanco on disbanco.k00_numpre = recibopaga.k00_numnov ";
							$sSql .= "                                  where ti.inicial = termoini.inicial                                      ";
							$sSql .= "                                    and termo.v07_situacao = 2                                             ";
							$sSql .= "                                    and disbanco.classi = true                                             ";
							$sSql .= "                                    and recibopaga.k00_receit = {$oTermoTaxaParc->ar36_receita})           ";

					        $rsResult = db_query($sSql);
					        
					        if (!$rsResult) {
					            throw new DBException("Ocorreu um erro ao verificar se a custa já foi paga.");
					        }

					        if (pg_num_rows($rsResult) > 0) {

								$oDebito = pg_fetch_object($rsResult, 0);

								$fValorDebito += $oDebito->valor;
					        } else {
					        	$fValorDebito += $oTermo->v07_valor;
					        }
							
					        $fPorcentagemTaxa = $oTermoTaxaParc->ar36_perc;
					        $fValorCustas = $oTermoTaxaParc->ar36_valor;

					        if (!empty($fPorcentagemTaxa) && $fPorcentagemTaxa > 0) {

					            $fValorCustas = ($fValorDebito * ($fPorcentagemTaxa / 100));

					            if ($fValorCustas < $oTermoTaxaParc->ar36_valormin) {
					                $fValorCustas = $oTermoTaxaParc->ar36_valormin;
					            } elseif ($fValorCustas > $oTermoTaxaParc->ar36_valormax) {
					                $fValorCustas = $oTermoTaxaParc->ar36_valormax;
					            }
					        }

					        /**PLUGINTAXAJURIDICAADICIONALPORNOME2**/

					        $fValorCustas = round($fValorCustas, 2);

					        $oRecibo->adicionarReceitaCustaParcelamento(
					        	$oDebitosRecibo->k00_numpre, 
					        	$oDebitosRecibo->k00_numpar,
					        	$oTermoTaxaParc->ar36_receita, 
					        	$fValorCustas, 
					        	11403);
						}
					}
				}
			}

          	$iNumnov = $oRecibo->getNumpreRecibo();

            $sSql  = " select distinct                                                         ";
            $sSql .= "        recibopaga.k00_numpre,                                           ";
            $sSql .= "        recibopaga.k00_numpar                                            ";
            $sSql .= "   from recibopaga                                                       ";
            $sSql .= "        inner join arrecad on arrecad.k00_numpre = recibopaga.k00_numpre ";
            $sSql .= "                          and arrecad.k00_tipo = 30                      ";
            $sSql .= "  where recibopaga.k00_numpar = recibopaga.k00_numtot                    ";
            $sSql .= "    and recibopaga.k00_numnov = {$iNumnov}                               ";
            $sSql .= "    and recibopaga.k00_numtot < (select max(ar29_numpar)                 ";
            $sSql .= "    	                             from termotaxaparc                    ";
            $sSql .= "  	                            where ar29_instit = {$iInstit})        ";
			
            $rsResult = db_query($sSql);

			$aNumpre = \db_utils::getCollectionByRecord($rsResult);

			foreach ($aNumpre as $oNumpre) {

				$sSqlTermoTaxaParc = $oDaoTermoTaxaParc->sql_query(null, "*", null, "ar29_instit = {$iInstit} and ar29_numpar > {$oNumpre->k00_numpar}");
				$rsResult = $oDaoTermoTaxaParc->sql_record($sSqlTermoTaxaParc);
				$aTermoTaxaParc = \db_utils::getCollectionByRecord($rsResult);

				foreach ($aTermoTaxaParc as $oTermoTaxaParc) {

			        $sSqlProcessoForo  = " select true                                                                                          ";
			        $sSqlProcessoForo .= "   from termoini                                                                                      ";
			        $sSqlProcessoForo .= "        inner join inicial on inicial.v50_inicial = termoini.inicial                                  ";
			        $sSqlProcessoForo .= "        inner join processoforoinicial on processoforoinicial.v71_inicial = inicial.v50_inicial       ";
			        $sSqlProcessoForo .= "        inner join processoforo on processoforo.v70_sequencial = processoforoinicial.v71_processoforo ";
			        $sSqlProcessoForo .= "  where processoforoinicial.v71_anulado = false                                                       ";
			        $sSqlProcessoForo .= "    and processoforo.v70_anulado = false                                                              ";
			        $sSqlProcessoForo .= "    and termoini.parcel = termo.v07_parcel limit 1                                                    ";

					$oDaoTermo = new cl_termo();
					$sSqlTermo = $oDaoTermo->sql_query_file(null, "({$sSqlProcessoForo}) as com_processo", null, " termo.v07_numpre = {$oDebitosRecibo->k00_numpre}");

					$rsResult = $oDaoTermo->sql_record($sSqlTermo);

				    if (!$rsResult) {
				      throw new \Exception("Erro ao buscar os dados de origem do parcelamento.");
				    }

					$aTermo = \db_utils::fieldsMemory($rsResult, 0);

					if (($aTermo->com_processo != 't' && $oTermoTaxaParc->ar36_debitoscomprocesso == 't') || ($aTermo->com_processo == 't' && $oTermoTaxaParc->ar36_debitossemprocesso == 't')) {
						continue;
					}

					$fValorDebito = 0;

					if ($oTermoTaxaParc->ar36_aplicajurosmulta == 't') {

				        $sSql  = " select sum(case when k00_hist <> 401 and k00_hist <> 400 and k00_hist <> 918 then k00_valor else 0 end) as valor_hist, ";
				        $sSql .= "        sum(case when k00_hist = 400 then k00_valor else 0 end) as valor_juros,                                         ";
				        $sSql .= "        sum(case when k00_hist = 401 then k00_valor else 0 end) as valor_multa,                                         ";
				        $sSql .= "        sum(case when k00_hist = 918 then k00_valor else 0 end) as valor_desconto                                       ";
				        $sSql .= "   from recibopaga                                                                                                      ";
				        $sSql .= "  where k00_numnov = {$iNumnov}                                                                                         ";
				        $sSql .= "    and k00_numpre = {$oDebitosRecibo->k00_numpre}                                                                                         ";
				        $sSql .= "    and k00_numpar = {$oDebitosRecibo->k00_numpar}                                                                                         ";

				        $rsValor = db_query($sSql);

				        if (!$rsValor) {
				            throw new DBException("Não foi possivel obter o valor base de calculo das custas " . $sMensagem);
				        }

				        $oValor = pg_fetch_object($rsValor, 0);

				        $fValorDebito = $oValor->valor_juros + $oValor->valor_multa - $oValor->valor_desconto;
					} 

					$oDaoTermo = new cl_termo();
					$sSqlTermo = $oDaoTermo->sql_query_file(null, "*", null, "v07_numpre = {$oDebitosRecibo->k00_numpre}");
					$rsTermo = $oDaoTermo->sql_record($sSqlTermo);

					$oTermo = pg_fetch_object($rsTermo, 0);

					$sSql  = " select sum(termoini.total) as valor                                                                       ";
					$sSql .= "   from termoini                                                                                           ";
					$sSql .= "  where termoini.parcel = (select v07_parcel from termo where v07_numpre = {$oDebitosRecibo->k00_numpre})  ";
					$sSql .= "    and termoini.inicial not in (select ti.inicial                                                         ";
					$sSql .= "                                   from termoini ti                                                        ";
					$sSql .= "                                        inner join termo on termo.v07_parcel = ti.parcel                   ";
					$sSql .= "                                        inner join recibopaga on recibopaga.k00_numpre = termo.v07_numpre  ";
					$sSql .= "                                        inner join disbanco on disbanco.k00_numpre = recibopaga.k00_numnov ";
					$sSql .= "                                  where ti.inicial = termoini.inicial                                      ";
					$sSql .= "                                    and termo.v07_situacao = 2                                             ";
					$sSql .= "                                    and disbanco.classi = true                                             ";
					$sSql .= "                                    and recibopaga.k00_receit = {$oTermoTaxaParc->ar36_receita})           ";

			        $rsResult = db_query($sSql);
			        
			        if (!$rsResult) {
			            throw new DBException("Ocorreu um erro ao verificar se a custa já foi paga.");
			        }

			        if (pg_num_rows($rsResult) > 0) {

						$oDebito = pg_fetch_object($rsResult, 0);

						$fValorDebito += $oDebito->valor;
			        } else {
			        	$fValorDebito += $oTermo->v07_valor;
			        }

			        $fPorcentagemTaxa = $oTermoTaxaParc->ar36_perc;
			        $fValorCustas = $oTermoTaxaParc->ar36_valor;

			        if (!empty($fPorcentagemTaxa) && $fPorcentagemTaxa > 0) {

			            $fValorCustas = ($fValorDebito * ($fPorcentagemTaxa / 100));

			            if ($fValorCustas < $oTermoTaxaParc->ar36_valormin) {
			                $fValorCustas = $oTermoTaxaParc->ar36_valormin;
			            } elseif ($fValorCustas > $oTermoTaxaParc->ar36_valormax) {
			                $fValorCustas = $oTermoTaxaParc->ar36_valormax;
			            }
			        }

			        /**PLUGINTAXAJURIDICAADICIONALPORNOME3**/

			        $fValorCustas = round($fValorCustas, 2);

			        $oRecibo->adicionarReceitaCustaParcelamento(
			        	$oNumpre->k00_numpre, 
			        	$oNumpre->k00_numpar,
			        	$oTermoTaxaParc->ar36_receita, 
			        	$fValorCustas, 
			        	11403);
				}
			}
		}

		$oRecibo->processaReceitaCustaParcelamento();

		return $oRecibo;
	}
}
