<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBseller Servicos de Informatica
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
require_once(modification("std/db_stdClass.php"));
require_once(modification("libs/db_conecta".".php"));
include_once(modification("libs/db_sessoes.php"));
include_once(modification("libs/db_usuariosonline.php"));
include_once(modification("dbforms/db_funcoes.php"));
try{
    if (db_getsession("DB_instit") != 1 ) {
        throw new Exception("Essa rotina é de uso exclusivo da instituição PREFEITURA MUNICIPAL DE VOLTA REDONDA.");
    }
} catch (Exception $exception) {
    return db_redireciona("db_erros.php?fechar=true&db_erro={$exception->getMessage()}");
}
?>
<html>
    <head>
        <title>Microsist</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
        <meta http-equiv="Expires" CONTENT="0"/>
        <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
        <link href="estilos.css" rel="stylesheet" type="text/css"/>
        <style>
            input[type="file"] {
                height: 22px;
                border: none;
            }

            select {
                width: 157px;
                height: 18px;
            }

            .formulario {
                margin-top: 50px;
                width: 500px;
            }
        </style>
    </head>
    <body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1">
    <center>
        <form name="form1" autocomplete="on" action="" onsubmit="return  validaForm();" method="post"
              class="formulario" enctype="multipart/form-data">
            <fieldset>
                <legend>Processar Arquivo TXT Bancário</legend>
                <table width="100%" border="0" cellspacing="0">
                    <tr>
                        <td width="33%">
                            <label for="arquivoTxt">Arquivo:</label>
                        </td>
                        <td width="67%">
                            <input name="arquivoTxt" id="arquivoTxt" type="file" required="required"/>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <table width="100%" border="0" cellspacing="0" style="margin-top: 10px;">
                <tr align="center">
                    <td colspan="2">
                        <input name="Processar" type="submit" id="Processar" value="Processar"/>
                    </td>
                </tr>
            </table>
        </form>
    </center>
    <script>
        function validaForm() {

            // js_divCarregando('Por favor, aguarde o sistema processar o arquivo', 'msgBox', true);

            var ponto = document.getElementById("tipoDePonto");
            var arquivo = document.getElementById("arquivoTxt");



            if (arquivo.length < 1 || arquivo.value == '') {
                alert("Erro, favor selecionar o arquivo.");
                arquivo.focus();
                return false;
            }

            return true;
        }
    </script>
    <?php db_menu(db_getsession("DB_id_usuario") , db_getsession("DB_modulo") , db_getsession("DB_anousu") , db_getsession("DB_instit"));?>
    </body>
</html>
<?php
//Valida se o formulário foi submetido
if (!isset($_FILES["arquivoTxt"]["name"])) exit;
//-----------------------------------------------------------------------------------------------------------------------------------------//
//----------------------------------[ Constantes do sistema ]------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------//
const INSTITUICAO_FME = 11;                                               // Constante da instituição FME
const MAX_FILE_SIZE = 99000000;                                             // Tamanho máximo permitido por arquivo
const UPLOAD_STATUS_OK = 1;                                               // Status ok do upload
const UPLOAD_STATUS_ERROR = 0;                                            // Status erro do upload
//-----------------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------[ Variáveis do sistema ]------------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------//
$target_dir = "tmp/";                                                     // Direório temporário para uload de arquivos
$target_file = $target_dir . basename($_FILES["arquivoTxt"]["name"]);     // Arquivo selecionado
$txtFileType = pathinfo($target_file , PATHINFO_EXTENSION);                // Extensão do arquivo
$fileSize = $_FILES["arquivoTxt"]["size"];                                // Tamanho do arquivo
// $tipoDePonto = $_POST['tipoDePonto'];                                     // Tipo de ponto inserido
$uploadOk = UPLOAD_STATUS_OK;                                             // Status do Upload
$status = "INFO: ";                                                       // Tipo de mensagem de retorno
$mensagem = null;                                                         // Mensagem de retorno
$sqlInsertParcelas = null;                                                // Variavel para sql insert
$sqlDeleteParcelas = null;                                                // Variável para sql delete
$pre = null;                                                              // Prefixo das tabelas de ponto
$txt = null;                                                              // Array que representa o arquivo
$rs = null;                                                               // Variável que representao Recordset
$busca = null;                                                            // Variável para a consulta de rubricas
$rsbusca = null;                                                          // Variável que representao Recordset da consulta de rubricas
$filha = null;                                                            // Variável que representa a consulta de rubrica filha
$rsfilha = null;                                                          // Variável que representao Recordset da consulta de rubricas filhas
$sqlInsert = null;                                                        // Variável que representa o insert na tabela de ponto
$rsinsert = null;                                                         // Variável que representa o Recordset do insert na tabela de ponto
//-----------------------------------------------------------------------------------------------------------------------------------------//
//-----------------------------------[ Campos da tabela de ponto ]-------------------------------------------------------------------------//
//-----------------------------------------------------------------------------------------------------------------------------------------//
$matr = null;
$vlr = null;
$data = null;
$up = null;
$ap = null;
$ano = db_getsession("DB_anousu");
$mes = date('m' , db_getsession("DB_datausu"));
//------------------------------------------------------------------------------------------------------//
//-------------------[ Validações ]---------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------//

if ($_FILES["arquivoTxt"]["name"] == '') {
    db_msgbox("ERRO: Preencha os campos corretamente.");
    exit;
}

function lValida_Matricula( $iMatricula, $iMes, $iAno, $instit ){
    $sSql  = "select * from rhpessoalmov where rh02_regist = $iMatricula and rh02_anousu = $iAno and rh02_mesusu = $iMes and rh02_instit = $instit";

    $oQuery = db_query($sSql);
    if( pg_num_rows($oQuery) > 0 ){
        return true;
    }else{
        return false;
    }
}
function lValida_Afasta( $iMatricula, $iMes, $iAno, $instit ){
	if ($iMes == 12) {
	  $iMesCalc = 1;
	  $iAnoCalc = $iAno + 1;
	} else {
	  $iMesCalc = $iMes + 1;
	  $iAnoCalc = $iAno;
	}

    $sSql  = "
select 1
from afasta 
where r45_regist = $iMatricula and r45_anousu = $iAno and r45_mesusu = $iMes
and 
	case when r45_situac in ( 5,9,10,11 ) then false
	else
	  case when r45_dtreto is null then true
 	  else 
	    case 
	    when r45_dtreto >= '$iAnoCalc-$iMesCalc-01'::date then true 
	    else false 
	    end  
	  end
	end
";
    $oQuery = db_query($sSql) or die($sSql);
    if( pg_num_rows($oQuery) == 0 ) {
        return true;
    }else{
        return false;
    }
}

function fieldsTable ( $tipoDePonto )
{
    $fields["camposExtras"] = null;
    $fields["pre"] = null;

    switch ($tipoDePonto) {
        case "pontofa"  :
            $fields["pre"] = "r21";
            $fields["camposExtras"] = " null, ";
            break;
        case "pontocom" :
            $fields["pre"] = "r47";
            $fields["camposExtras"] = " null, ";
            break;
        case "pontofx"  :
            $fields["pre"] = "r90";
            $fields["camposExtras"] = " null, ";
            break; // 9 campos
        case "pontofs"  :
            $fields["pre"] = "r10";
            $fields["camposExtras"] = " '0',  ";
            break; // 9 campos
        case "pontofr"  :
            $fields["pre"] = "r19";
            $fields["camposExtras"] = " '0',  ";
            break;
        case "pontof13" :
            $fields["pre"] = "r34";
            $fields["camposExtras"] = " 0, 0, ";
            break; // 10 campos
        case "pontofe"  :
            $fields["pre"] = "r29";
            $fields["camposExtras"] = " 0, 0, '0', ";
            break; // 11 Campos
    }

    return $fields;
}

function getLota( $iMatricula, $iMes, $iAno, $instit ){
    $sSql  = "select rh02_lota from rhpessoalmov where rh02_regist = $iMatricula and rh02_anousu = $iAno and rh02_mesusu = $iMes and rh02_instit = $instit";
    $rsSql = db_query($sSql);
    return pg_result($rsSql,0);
}

function CreateQuery ( $tipoDePonto , $ano , $mes , $matric , $rubrica , $vlr , $up , $ap )
{

    $camposExtras = fieldsTable($tipoDePonto);
    $SqlInsert = "INSERT INTO $tipoDePonto VALUES ( $ano, $mes, $matric, '$rubrica', $vlr, " . $up . "." . $ap;
    $SqlInsert .= ", ( SELECT rh02_lota FROM rhpessoalmov WHERE rhpessoalmov.rh02_regist = $matric AND ";
    $SqlInsert .= "rhpessoalmov.rh02_instit = " . INSTITUICAO_FME . " AND rhpessoalmov.rh02_anousu = $ano AND ";
    $SqlInsert .= "rhpessoalmov.rh02_mesusu = $mes  ), ";
    $SqlInsert .= " {$camposExtras["camposExtras"]} " . INSTITUICAO_FME . " );";

    return $SqlInsert;
}

# ======================================[ Limpa os registros anteriores ]===============================================


function limpaRegistros ( $tipoDePonto , $ano , $mes , $rubrica , $target_file )
{
    $fields = fieldsTable($tipoDePonto);
    $pre = $fields["pre"];
    $rubricaMae = $rubrica;

    $txt = file($target_file);

    //Deleta as parcelas da tabela parcelas_fme
    $sqlParcelas = "DELETE FROM parcelas_fme where rubrica='$rubrica'";
    db_query($sqlParcelas) or die(@pg_last_error());

    for ($i = 0; $i < sizeof($txt); $i++) {
        $matric = substr($txt[$i] , 0 , 9);

        $sql = " select * from $tipoDePonto ";
        $sql .= " where {$pre}_anousu = $ano and {$pre}_mesusu = $mes";
        $sql .= " and {$pre}_regist = $matric and ({$pre}_rubric = '$rubrica' or {$pre}_rubric in ";
        $sql .= " (select pl20_rubrica from plugins.rhrubricasmae where pl20_rubricamae = '$rubrica'))";

        $rsbusca = db_query($sql) or die(@pg_last_error());
        $num_rows = pg_num_rows($rsbusca);

        if ($num_rows > 0) {
            $sql = " select pl20_rubrica from plugins.rhrubricasmae ";
            $sql .= " where pl20_rubricamae = '$rubrica' and pl20_seq = " . $num_rows . " * 2";

            $rsfilha = db_query($sql) or die(@pg_last_error());
            $rubrica = pg_result($rsfilha , 0 , 0);
        }

        $sql = " delete from $tipoDePonto where {$pre}_anousu = $ano and ";
        $sql .= " {$pre}_mesusu = $mes and {$pre}_regist = $matric and ";
        $sql .= " ({$pre}_rubric = '$rubrica' or {$pre}_rubric in ( ";
        $sql .= " select pl20_rubrica from plugins.rhrubricasmae where ";
        $sql .= " pl20_rubricamae = '$rubrica' ) ) ";

        #echo $sql . "<br><br>";
        $result = db_query($sql) or die(@pg_last_error());
        if (!$result) {
            echo $sql;
            exit;
        }
    }

    $sql = "DELETE FROM $tipoDePonto
            WHERE {$pre}_anousu = $ano
            AND   {$pre}_mesusu = $mes
            AND  ({$pre}_rubric = '$rubricaMae' OR {$pre}_rubric IN (
            SELECT pl20_rubrica FROM plugins.rhrubricasmae
            WHERE pl20_rubricamae = '$rubricaMae'))";

    $result = db_query($sql) or die(@pg_last_error());
    if (!$result) {
        echo $sql;
        die;
    }

}

# ======================================================================================================================

//Verificação da tabela para definir qual prefixo utilizar
//$dados = fieldsTable($tipoDePonto);
//$pre = $dados["pre"];

//  pontofa pontocom pontof13 pontofe pontofr

if ($fileSize > MAX_FILE_SIZE) {
    $mensagem = "O arquivo selecionado é muito grande.";
    $uploadOk = UPLOAD_STATUS_ERROR;
}

if ($txtFileType != "txt") {
    $mensagem = "Tipo de arquivo não suportado. \nEsperado: arquivo.txt";
    $uploadOk = UPLOAD_STATUS_ERROR;
}

if ($uploadOk == UPLOAD_STATUS_ERROR) {
    if ($mensagem == null)
        $mensagem = "Houve um erro no upload do arquivo.";
    $status = "ERRO: ";

} else {

    if (move_uploaded_file($_FILES["arquivoTxt"]["tmp_name"] , $target_file)) {

        if (!file_exists($target_file)) {
            db_msgbox("ERRO: arquivo " . $target_file . " não foi copiado para o sistema.");
            exit;
        }

        db_inicio_transacao();
        $txt = file($target_file);
        $deParaOrgaoInstit = [
                12 => 1,
                19 => 25,
                11 => 20,
                2 => 30,
                1 => 35,
                13 => 75,
                5 => 80,
                10 => 80
        ];
        $oTotal = 0;
        $oMatriculas = "";
        $aRubricas = array();
        // Array de matriculas e rubricas
        $aMatriculas = array();
        $erroTransacao = false;
        for ($i = 0; $i < sizeof($txt); $i++) {
            // if ($i == 0)
            //  continue;
            $iAno    = db_anofolha();
            $iMes    = db_mesfolha();
            $orgao     = (int) substr($txt[$i], 74,3);
            $matr      = $orgao . substr($txt[$i] , 4 , 6);
            $rubrica   = "0" . substr($txt[$i] , 77 , 3);
            $iInstit = $deParaOrgaoInstit[$orgao];
            $vlr       = floatval((substr($txt[$i] , 80 , 7) . '.' . substr($txt[$i] , 88 , 2)));
            $parcela   = (int)substr($txt[$i] , 90 , 3);
            $parcTotal = (int)substr($txt[$i] , 93 , 3);
            $iQuant    =  $parcTotal;

            // valida Matriculas
            if (empty($aMatriculas[$matr])) {
                $aMatriculas[$matr] = array();
            }

            // valida Rubricas por matricula
            if (empty($aMatriculas[$matr][$rubrica])) {
                $aMatriculas[$matr][$rubrica] = 0;
            }
            $aMatriculas[$matr][$rubrica] += $vlr;
            $vlr = $aMatriculas[$matr][$rubrica];

            $aRubricas[$rubrica] = $rubrica;
            $iValidaAfasta = lValida_Afasta($matr,$iMes,$iAno,$iInstit);
            $iValidaMatricula = lValida_Matricula($matr,$iMes,$iAno,$iInstit);

	    if ($iValidaMatricula) {

		  $iLota = getLota($matr,$iMes,$iAno,$iInstit);
		  $oDelete = " delete from pontofs where r10_regist = $matr and r10_rubric = '$rubrica' and r10_anousu = $iAno and r10_mesusu = $iMes";
		  $rsDelete = db_query($oDelete);
		  if (!$rsDelete) {
		      $erroTransacao = true;
		      db_msgbox("ERRO: Não foi possível incluir as parcelas da rubrica {$rubrica} da Matrícula: $matr no banco: \n\n" . @pg_last_error());
		      exit;
		  }

	        if ( $iValidaAfasta ) {

		  $sqlInsertParcelas = "insert into pontofs
				      (r10_anousu,r10_mesusu,r10_regist,r10_rubric,r10_valor,r10_quant,r10_lotac,r10_datlim,r10_instit)
				      values ($iAno,$iMes,$matr,'$rubrica',$vlr, $iQuant, $iLota, 0, $iInstit)";

		  $result = db_query($sqlInsertParcelas);


		  if ($result == false) {
		      $erroTransacao = true;
		      db_msgbox("ERRO: Não foi possível incluir as parcelas da rubrica {$rubrica} no banco: \n\n" . @pg_last_error());
		      exit;
		  }

		  $oTotal++;

              }

            }else{
                $oMatriculas .= " Matrícula não encontrada :  ".$matr."\n";
            }
        }
        db_fim_transacao($erroTransacao);
        if ($erroTransacao) {
            throw new Exception("Ocorreu um erro na importação dos dados, cheque o arquivo de importação");
        }
        echo "<script>  js_removeObj(\"msgBox\");</script>";
        $mensagem = "Fim do Processamento. Executado com sucesso! \n Total processados : ".$oTotal." ". $mensagem;

    } else
        $status = "ERRO: ";
}
//Remove o arquivo
unlink($target_file);

db_msgbox($status . " " . $mensagem);
if (!empty($oMatriculas))
    db_msgbox("Matrículas não encontradas : \n".$oMatriculas);
if (!empty($alertMessage))
    db_msgbox("ALERTA: $erro Matrículas não foram inseridas\n\n" . $alertMessage);


