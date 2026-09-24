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

require_once(modification('libs/db_stdlib.php'));
require_once(modification('libs/db_conecta.php'));
require_once(modification('libs/db_sessoes.php'));
require_once(modification('libs/db_usuariosonline.php'));
require_once(modification('libs/JSON.php'));
require_once(modification('libs/db_utils.php'));

$oJson = new services_json();
$rsSql = null;
$aResultados = [];
$iLinhas = 0;

/**
 * Este RPC devera ser apenas para consultas em virtude dos autocompletes
 * que o chamam e tem o seu consumo desta RPC de maneira bem limitada .
 * Por isso, nao tem o status de certo/errado e respostas de tratamento
 * para erros. 
*/

$oParam = isset($_POST["json"]) ? $oJson->decode(str_replace("\\", "", $_POST["json"])) : null;

/**
 * Adaptando RPC para chamadas via autocomplete
 */
$filtroAutoComplete = null;
if(isset($_POST['string'])){
    $filtroAutoComplete = html_entity_decode(urldecode($_POST['string']));
    $oParam = new stdClass();
    $oParam->exec = '';

    if(isset($_GET['central'])){
        $oParam->exec = 'getInfoCentral';
    }
    
    if(isset($_GET['prestadora']) && isset($_GET['codigoCentral'])
      && !empty($_GET['codigoCentral'])
    ){
        $oParam->exec = 'getInfoPrestadora';
        $oParam->codigoCentral = $_GET['codigoCentral'];
    }
    
    if(isset($_GET['veiculos'])){
        $oParam->exec = 'getInfoVeiculos';
    }
    
    if(isset($_GET['motoristas'])){
        $oParam->exec = 'getInfoMotoristas';
    }
} 

switch($oParam->exec) {

    case 'getInfoCentral':
        $daoCentralAgendamento = new cl_tfd_centralagendamento;
        $campos = "tf09_i_codigo as cod,z01_nome as label,tf09_i_numcgm as numCgm";
        $where = "z01_nome ilike '$filtroAutoComplete%'";
        $sqlCentralAgendamento = $daoCentralAgendamento->sql_query("",$campos,"tf09_i_codigo",$where);            
        $rsSql = db_query($sqlCentralAgendamento); 
        $iLinhas = pg_num_rows($rsSql);
        break;

    case 'getInfoPrestadora':           
        $oDaoPrestadora = new cl_tfd_prestadoracentralagend;
        $campos = "tf10_i_codigo as cod,a.z01_nome as label";    
        $dData     = date('Y-m-d', db_getsession('DB_datausu'));
        $codigoCentral = $oParam->codigoCentral;

        $where = " 
            a.z01_nome ilike '$filtroAutoComplete%'
            and tf10_i_centralagend = $codigoCentral
            and tf10_d_validadeini <= '$dData' 
            and (tf10_d_validadefim is null or tf10_d_validadefim >= '$dData')
        ";

        $sqlPrestadora = $oDaoPrestadora->sql_query("", $campos, "tf10_i_codigo", $where);        
        $rsSql = db_query($sqlPrestadora);
        $iLinhas = pg_num_rows($rsSql);
        break;

    case 'getInfoVeiculos':                   
        $daoVeiculos = new cl_veiculos;
        $campos = "ve01_codigo as cod,ve01_placa as label";        
        $where = " ve01_ativo = '1' ";
        $where .= " and ve01_placa ilike '$filtroAutoComplete%' ";
        $where .= " and ve36_coddepto = ".db_getsession("DB_coddepto");            
        $sSqlVeiculos = $daoVeiculos->sql_query_central(null,$campos, "ve01_codigo",$where);        
        $rsSql = db_query($sSqlVeiculos);
        $iLinhas = pg_num_rows($rsSql);

        break;

    case 'getInfoMotoristas': 
        $daoMotoristas = new cl_veicmotoristas;
        $campos = "distinct ve05_codigo as cod,cgm.z01_nome as label";        
        $where = " cgm.z01_nome ilike '$filtroAutoComplete%' ";
        $where .= " and ve41_veicmotoristas is not null and '";
        $where .= date("Y-m-d",db_getsession("DB_datausu"));
        $where .= "' between ve41_dtini and coalesce(ve41_dtfim,cast('9999-12-31' as date))";
        $where .= " and (ve36_coddepto = ".db_getsession("DB_coddepto")." or 
                       ve37_coddepto = ".db_getsession("DB_coddepto").")";

        $sqlMotoristas = $daoMotoristas->sql_query_veic(null,$campos,"ve05_codigo",$where);        
        $rsSql = db_query($sqlMotoristas);
        $iLinhas = pg_num_rows($rsSql);
        break;           
}

if ($iLinhas > 0) {
  $aResultados = db_utils::getCollectionByRecord($rsSql, false, false, true);
}

echo $oJson->encode($aResultados);

function crossUrlDecode($sSource) {

 // Troco os caracteres especiais por pelo coringa
 $aOrig   = array('á', 'é', 'í', 'ó', 'ú', 'â', 'ê', 'ô', 'ã', 'õ', 'à', 'è', 'ì', 'ò', 'ù', 'ç',
                  'Á', 'É', 'Í', 'Ó', 'Ú', 'Â', 'Ê', 'Ô', 'Ã', 'Õ', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ç'
                 );

 return str_replace($aOrig, '_', mb_convert_encoding($sSource, "ISO-8859-1", "UTF-8"));

}
?>
