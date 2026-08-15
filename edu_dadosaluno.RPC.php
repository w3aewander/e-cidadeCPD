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
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("std/DBDate.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_aluno_classe.php"));
require_once(modification("model/educacao/avaliacao/iFormaObtencao.interface.php"));
require_once(modification("model/educacao/avaliacao/iElementoAvaliacao.interface.php"));

$oDaoAlunoTransporte  = new cl_alunocensotipotransporte();
$oJson                = new Services_JSON();
$oParam               = $oJson->decode(str_replace("\\", "", $_POST["json"]));

$oRetorno          = new stdClass();
$oRetorno->status  = 1;
$oRetorno->message = '';
switch ($oParam->exec) {

  case 'getTransportesAluno':

    $sCampos              = "ed312_sequencial as codigo, ed312_descricao as descricao, ";
    $sCampos             .= "case when ed311_sequencial is null then false else true end as possui";
    $sSqlDadosTransporte  = $oDaoAlunoTransporte->sql_query_transporte_aluno($oParam->iCodigoAluno, $sCampos);
    $rsDadosTransporte    = $oDaoAlunoTransporte->sql_record($sSqlDadosTransporte);
    $oRetorno->aTransportes = db_utils::getCollectionByRecord($rsDadosTransporte, false, false, true);

    $daoTransporteDificuldades = new cl_alunotransportedificuldades;
    $campos = "*";
    $where = "ed355_aluno = {$oParam->iCodigoAluno}";
    $sqlTransporteDificuldades = $daoTransporteDificuldades->sql_query_file(null,$campos,null,$where);
    $rsDadosTransporteDificuldades = db_query($sqlTransporteDificuldades);
    
    $oRetorno->aDificuldades = [];

    if(pg_num_rows($rsDadosTransporteDificuldades) > 0){
      $transporteDificuldades = db_utils::fieldsMemory($rsDadosTransporteDificuldades,0);
      $oRetorno->aDificuldades = [
        'porteira' => $transporteDificuldades->ed355_porteira,
        'mataburro' => $transporteDificuldades->ed355_mataburro,
        'colchete' => $transporteDificuldades->ed355_colchete,
        'atoleiro' => $transporteDificuldades->ed355_atoleiro,
        'ponterustica' => $transporteDificuldades->ed355_ponterustica
      ];
    }
      
    $daoAluno = new cl_aluno;
    $campos = "ed47_i_transpublico,ed47_c_transporte";
    $sqlAluno = $daoAluno->sql_query_file($oParam->iCodigoAluno,$campos);
    $rsAluno = db_query($sqlAluno);
    $transporteAlunoInfo = db_utils::fieldsMemory($rsAluno,0);
    $oRetorno->ed47_i_transpublico = $transporteAlunoInfo->ed47_i_transpublico;
    $oRetorno->ed47_c_transporte = $transporteAlunoInfo->ed47_c_transporte;

    break;
  case 'inserirTransporteAluno':

    try {

      db_inicio_transacao(false);
      $sWhereExclusao         = "ed311_aluno = {$oParam->iCodigoAluno}";
      $sSqlExcluirTransportes = $oDaoAlunoTransporte->excluir(null, $sWhereExclusao);
      if ($oDaoAlunoTransporte->erro_status == 0) {
        throw new Exception('Erro ao Salvar dados do transporte publico do aluno');
      }
      foreach ($oParam->aTransporte as $iCodigoTransporte) {

        $oDaoAlunoTransporte->ed311_aluno               = $oParam->iCodigoAluno;
        $oDaoAlunoTransporte->ed311_censotipotransporte = $iCodigoTransporte;
        $oDaoAlunoTransporte->incluir(null);
        if ($oDaoAlunoTransporte->erro_status == 0) {

          $sErroMensagem = "Erro ao Salvar dados do transporte publico do aluno.\n";
          $sErroMensagem .= $oDaoAlunoTransporte->erro_msg;
          throw new Exception($sErroMensagem);
        }
      }

      $daoTransporteDificuldades = new cl_alunotransportedificuldades;
      $where = " ed355_aluno = $oParam->iCodigoAluno ";
      $sqlTransporte = $daoTransporteDificuldades->sql_query_file(null,"ed355_sequencial",null,$where);      
      $rsTransporte = db_query($sqlTransporte);

      $aDificuldades = json_decode($oParam->aDificuldades);

      $daoTransporteDificuldades->ed355_aluno  = $oParam->iCodigoAluno;
      $daoTransporteDificuldades->ed355_porteira = $aDificuldades->ed355_porteira;
      $daoTransporteDificuldades->ed355_mataburro = $aDificuldades->ed355_mataburro;
      $daoTransporteDificuldades->ed355_colchete = $aDificuldades->ed355_colchete;
      $daoTransporteDificuldades->ed355_atoleiro = $aDificuldades->ed355_atoleiro;
      $daoTransporteDificuldades->ed355_ponterustica = $aDificuldades->ed355_ponterustica;
      
      if(pg_num_rows($rsTransporte) > 0 ){        
        $sequencialTransporte = db_utils::fieldsMemory($rsTransporte,0)->ed355_sequencial;
        $daoTransporteDificuldades->ed355_sequencial = $sequencialTransporte;
        $daoTransporteDificuldades->alterar($sequencialTransporte);            
      } else {
        $daoTransporteDificuldades->incluir(null); 
      }
      
      if ($daoTransporteDificuldades->erro_status == 0) {
        $sErroMensagem = "Erro ao Salvar dados das dificuldades do transporte público.\n";
        $sErroMensagem .= $daoTransporteDificuldades->erro_msg;
        throw new Exception($sErroMensagem);
      }

      $daoAluno = new cl_aluno;
      $daoAluno->ed47_i_codigo = $oParam->iCodigoAluno;
      $daoAluno->ed47_i_transpublico = $oParam->ed47_i_transpublico;
      $daoAluno->ed47_c_transporte = $oParam->ed47_c_transporte;
      $resultAlterarAlunoTransporte = $daoAluno->alterar($oParam->iCodigoAluno);
      if (!$resultAlterarAlunoTransporte) {
        $sErroMensagem = "Erro ao atualizar informações sobre o transporte do aluno.\n";
        $sErroMensagem .= $daoAluno->erro_msg;
        throw new Exception($sErroMensagem);
      }            
      db_fim_transacao(false);
    } catch (Exception $eErro) {

      $oRetorno->status = 2;
      $oRetorno->message = urlencode($eErro->getMessage());
      db_fim_transacao(true);

    }

    break;

  case 'gradeAproveitamentoAluno':

    try {

      $oGradeAproveitamento           = new GradeAproveitamentoAluno(new Matricula($oParam->iMatricula));
      $oGradeAproveitamento->setUrlEncode(true);

      $oRetorno->aGradeAproveitamento = $oGradeAproveitamento->getGradeAproveitamento();
    } catch (ParameterException $eErro) {

      $oRetorno->status  = 2;
      $oRetorno->message = urlencode($eErro->getMessage());
    } catch (BusinessException $eErro) {

      $oRetorno->status  = 2;
      $oRetorno->message = urlencode($eErro->getMessage());
    }

    break;

}
echo $oJson->encode($oRetorno);
?>
