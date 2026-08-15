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
 
define( 'MENSAGENS_EDU2_FICHAALUNO001', 'educacao.escola.edu2_fichaaluno002.' );

require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("model/educacao/ArredondamentoNota.model.php"));
require_once(modification("model/educacao/DBEducacaoTermo.model.php"));
require_once(modification("fpdf151/pdfwebseller.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("libs/db_libparagrafo.php"));

//db_getsession("DB_instit") == 96

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function dadosDoAluno($codigo){
  $sql = pg_query("SELECT * from aluno inner join pais on pais.ed228_i_codigo = aluno.ed47_i_pais left join censouf as censoufident on censoufident.ed260_i_codigo = aluno.ed47_i_censoufident left join censouf as censoufnat on censoufnat.ed260_i_codigo = aluno.ed47_i_censoufnat left join censouf as censoufcert on censoufcert.ed260_i_codigo = aluno.ed47_i_censoufcert left join censouf as censoufend on censoufend.ed260_i_codigo = aluno.ed47_i_censoufend left join censomunic as censomunicnat on censomunicnat.ed261_i_codigo = aluno.ed47_i_censomunicnat left join censomunic as censomuniccert on censomuniccert.ed261_i_codigo = aluno.ed47_i_censomuniccert left join censomunic as censomunicend on censomunicend.ed261_i_codigo = aluno.ed47_i_censomunicend left join censoorgemissrg on censoorgemissrg.ed132_i_codigo = aluno.ed47_i_censoorgemissrg left join censocartorio on censocartorio.ed291_i_codigo = aluno.ed47_i_censocartorio left join tiposanguineo as d on d.sd100_sequencial = aluno.ed47_tiposanguineo where aluno.ed47_i_codigo = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaCodigoCidadaoAluno($codigo){
  $sql = pg_query("SELECT ed330_cidadao from alunocidadao where ed330_aluno = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed330_cidadao"];
}

function buscaVinculosFamiliares($codigocidadao){  
  $sql1 = pg_query("SELECT ov29_cidadaovinculo from cidadaofiliacao where ov29_cidadao = {$codigocidadao} and ov29_tipofamiliar in(4,5)");
  $resultado1 = pg_fetch_all($sql1);
  
  $pai = (int)$resultado1[0]["ov29_cidadaovinculo"];
  $mae = (int)$resultado1[1]["ov29_cidadaovinculo"];

  $sql2 = pg_query("SELECT ov02_nome, ov02_ident, ov02_cnpjcpf FROM cidadao WHERE ov02_sequencial in ({$pai}, {$mae});");
  $resultado = pg_fetch_all($sql2);
  return $resultado;
}

function buscaNomeContato($codigo){
  $sql = pg_query("SELECT ed332_cidadao from alunocidadaocontato where ed332_aluno = {$codigo}");
  $resultado1 = pg_fetch_all($sql);
  $codresp = $resultado1[0]["ed332_cidadao"];

  $sql2 = pg_query("SELECT ov02_nome FROM cidadao WHERE ov02_sequencial = {$codresp}");
  $resultado = pg_fetch_all($sql2);
  return $resultado[0]["ov02_nome"];
}

function buscaDadosPaiMae($codigocidadao){
  $sql1 = pg_query("SELECT ov29_cidadaovinculo from cidadaofiliacao where ov29_cidadao = {$codigocidadao} and ov29_tipofamiliar in(4,5)");
  $resultado1 = pg_fetch_all($sql1);
  
  $pai = (int)$resultado1[0]["ov29_cidadaovinculo"];
  $mae = (int)$resultado1[1]["ov29_cidadaovinculo"];

  $codfiliacao = array();
  $codfiliacao["pai"] = $pai;
  $codfiliacao["mae"] = $mae;

  return $codfiliacao;
}

function buscaTelefoneCidadao($codcid){
  $sql = pg_query("SELECT ov07_ddd, ov07_numero FROM cidadaotelefone WHERE ov07_principal = 'true' AND ov07_cidadao = {$codcid}");
  $resultado = pg_fetch_all($sql);
  return "(" . $resultado[0]["ov07_ddd"] . ") " . $resultado[0]["ov07_numero"];
}

$oGet                 = db_utils::postMemory($_GET);
$oDaoMatricula        = new cl_matricula();
$oDaoMatriculamov     = new cl_matriculamov();
$oDaoAluno            = new cl_aluno();
$oDaoAlunoprimat      = new cl_alunoprimat();
$oDaoAlunonecessidade = new cl_alunonecessidade();
$oDaoProcResultado    = new cl_procresultado();
$oDaoHistmpsdisc      = new cl_histmpsdisc();
$oDaoHistmpsdiscfora  = new cl_histmpsdiscfora();
$oDaoTipoSanguineo    = new cl_tiposanguineo();
$oErro                = new stdClass();

$dadosaluno = dadosDoAluno($alunos);
$codcidadao = buscaCodigoCidadaoAluno($alunos);
$responsaveis = buscaVinculosFamiliares($codcidadao);

if(empty($responsaveis[0]["ov02_ident"])){
  $responsaveis[0]["ov02_nome"] = $dadosaluno["ed47_v_mae"];
  $responsaveis[0]["ov02_ident"] = "NÃO INFORMADO";
}
if(empty($responsaveis[1]["ov02_ident"])){
  $responsaveis[1]["ov02_nome"] = $dadosaluno["ed47_v_pai"];
  $responsaveis[1]["ov02_ident"] = "NÃO INFORMADO";
}

if(empty($responsaveis[0]["ov02_cnpjcpf"])){
  $responsaveis[0]["ov02_nome"] = $dadosaluno["ed47_v_mae"];
  $responsaveis[0]["ov02_cnpjcpf"] = "NÃO INFORMADO";
}
if(empty($responsaveis[1]["ov02_cnpjcpf"])){
  $responsaveis[1]["ov02_nome"] = $dadosaluno["ed47_v_pai"];
  $responsaveis[1]["ov02_cnpjcpf"] = "NÃO INFORMADO";
}


$paimae = buscaDadosPaiMae($codcidadao);
$codccidpai = $paimae["pai"];
$codcidmae = $paimae["mae"];
$telpai = buscaTelefoneCidadao($codccidpai);
$telmae = buscaTelefoneCidadao($codcidmae);

$registropelopai = $dadosaluno["registropai"];
$aimagem = $dadosaluno["aimagem"];
/*
// Exibir o valor detalhado
echo "<pre>"; // Usar <pre> para manter a formatação
print_r($registropelopai);
echo "</pre>";
*/

$nomecontato = buscaNomeContato($alunos);
$localcontato = $dadosaluno["contatolocal"];
$emailcontato = $dadosaluno["contatoemail"];
$telcontato = $dadosaluno["contatocelular"];
$telcontato = "(".substr($telcontato, 0, 2).") " . substr($telcontato, 2);

$telefonea = "(".substr($dadosaluno["ed47_v_telef"], 0, 2).") " . substr($dadosaluno["ed47_v_telef"], 2);
$telefonec = "(".substr($dadosaluno["ed47_v_telcel"], 0, 2).") " . substr($dadosaluno["ed47_v_telcel"], 2);

$fotoaluno = trim($dadosaluno["ed47_c_foto"]);





$endereco = trim($dadosaluno["ed47_v_ender"]) . ", " . trim($dadosaluno["ed47_c_numero"]);
if($dadosaluno["ed47_v_compl"]){
  $endereco .= ", " . trim($dadosaluno["ed47_v_compl"]);
}

$registronascimento = $dadosaluno["ed47_c_certidaonum"];
$rn = "não";
if(strlen($registronascimento > 1)){
  $rn = "sim";
  $ntermo = $dadosaluno["ed47_c_certidaonum"];
  $nlivro = $dadosaluno["ed47_c_certidaolivro"];
  $nfolha = $dadosaluno["ed47_c_certidaofolha"];
}




try {

  $resultedu            = eduparametros(db_getsession("DB_coddepto"));
  $permitenotaembranco  = VerParametroNota(db_getsession("DB_coddepto"));

  $oDaoAluno->rotulo->label();
  $clrotulo = new rotulocampo;
  $clrotulo->label("ed76_i_escola");
  $clrotulo->label("ed76_d_data");

  $escola = db_getsession("DB_coddepto");// variável $escola não está sendo usada na classe @matheus;

  $sCampos  = " aluno.*,   ";
  $sCampos .= " censoufident.ed260_c_nome as ufident,  ";
  $sCampos .= " censoufnat.ed260_c_nome as ufnat,   ";
  $sCampos .= " censoufcert.ed260_c_nome as ufcert,   ";
  $sCampos .= " censoufend.ed260_c_nome as ufend,   ";
  $sCampos .= " censomunicnat.ed261_c_nome as municnat,   ";
  $sCampos .= " censomuniccert.ed261_c_nome as municcert,   ";
  $sCampos .= " censomunicend.ed261_c_nome as municend,   ";
  $sCampos .= " censoorgemissrg.ed132_c_descr as orgemissrg,   ";
  $sCampos .= " pais.ed228_c_descr ";
  $sSql     = $oDaoAluno->sql_query("",  $sCampos, "ed47_v_nome", " ed47_i_codigo IN ($alunos) ");
  $rsResult = db_query($sSql);

  if( !is_resource( $rsResult ) ) {

    $oErro->sErro = pg_last_error();
    throw new DBException( _M( MENSAGENS_EDU2_FICHAALUNO001 . 'erro_buscar_dados_aluno', $oErro ) );
  }

  $iLinhasAluno = pg_num_rows( $rsResult );

  if( $iLinhasAluno == 0 ) {
    throw new BusinessException(_M(MENSAGENS_EDU2_FICHAALUNO001 . 'nenhum_registro_encontrado'));
  }

  $sSqlTipoSanguineo = $oDaoTipoSanguineo->sql_query_file("", "*", "sd100_sequencial", "");
  $rsTipoSanguineo   = db_query($sSqlTipoSanguineo);

  if( !is_resource( $rsTipoSanguineo ) ) {

    $oErro->sErro = pg_last_error();
    throw new DBException( _M( MENSAGENS_EDU2_FICHAALUNO001 . 'erro_buscar_tipo_sanguineo', $oErro ) );
  }

  $iLinhas           = pg_num_rows( $rsTipoSanguineo );
  $aTiposSanguineos  = array();

  if ( $iLinhas > 0) {

    for ( $iContador = 0; $iContador < $iLinhas; $iContador++ ) {

      $oDados = db_utils::fieldsMemory($rsTipoSanguineo, $iContador);
      $aTiposSanguineos[$oDados->sd100_sequencial] = $oDados->sd100_tipo;
    }
  }  

  $oPdf = new PDF();
  $oPdf->Open();
  $oPdf->AliasNbPages();
  $oPdf->setfillcolor(223);
  $oPdf->SetAutoPageBreak(false, 10);
  $zaltura = 2;
  $zaltura2 = 0;
  for ($iCont = 0; $iCont < $iLinhasAluno; $iCont++) {

    db_fieldsmemory($rsResult,  $iCont);

    $head1 = "FICHA DE MATRÍCULA";
    $head2 = "$ed47_i_codigo - $ed47_v_nome";
    $oPdf->addpage('P');
    

    
    if($fotoaluno){
      $oPdf->Image('tmp/'.$fotoaluno, 175, 58, 25, 25);  
    }/*else{
      $oPdf->cell(194,  4 + $zaltura,  "DADOS SOBRE O ALUNO",  1,  1,  "C",  0);      
    }*/

    

    
    //DADOS PESSOAIS
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(194,  4 + $zaltura,  "DADOS SOBRE O ALUNO",  1,  1,  "C",  0);    

    
    

    
    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(10,  4 + $zaltura,  "NOME: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(100,  4 + $zaltura, $ed47_v_nome, 0, 0, "L", 0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(7,  4 + $zaltura,  "CPF: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(71,  4 + $zaltura, $dadosaluno["ed47_v_cpf"], 0, 0, "L", 0);
    $oPdf->cell(3,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);

    

    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(31,  4 + $zaltura,  "DATA DE NASCIMENTO: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(23,  4 + $zaltura, db_formatar($ed47_d_nasc, 'd'), 0, 0, "L", 0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(10,  4 + $zaltura,  "SEXO: ",  0,  0,  "L",  0);    
    $oPdf->cell(4, 4, $ed47_v_sexo=="M"?"X":"", 1, 0, "L", 0);
    $oPdf->cell(10,  4 + $zaltura,  "MASC. ",  0,  0,  "L",  0);    
    $oPdf->cell(4, 4, $ed47_v_sexo=="F"?"X":"", 1, 0, "L", 0);
    $oPdf->cell(28,  4 + $zaltura,  "FEM. ",  0,  0,  "L",  0);  
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(14,  4 + $zaltura,  "RAÇA/COR: ",  0,  0,  "L",  0); 
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(35,  4 + $zaltura, $dadosaluno["ed47_c_raca"], 0, 0, "L", 0);  
    $oPdf->cell(32,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);


    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);   
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(64,  4 + $zaltura,  "REGISTRADO EM NOME DO PAI: ",  0,  0,  "L",  0);    
    $oPdf->cell(4, 4, $registropelopai=="1"?"X":"", 1, 0, "L", 0);
    $oPdf->cell(10,  4 + $zaltura,  "SIM ",  0,  0,  "L",  0);    
    $oPdf->cell(4, 4, $registropelopai=="2"?"X":"", 1, 0, "L", 0);
    $oPdf->cell(28,  4 + $zaltura,  "NÃO ",  0,  0,  "L",  0);  
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(40,  4 + $zaltura,  "AUTORIZAÇÃO DE IMAGEM: ",  0,  0,  "L",  0);    
    $oPdf->cell(4, 4, $aimagem=="1"?"X":"", 1, 0, "L", 0);
    $oPdf->cell(10,  4 + $zaltura,  "SIM ",  0,  0,  "L",  0);    
    $oPdf->cell(4, 4, $aimagem=="2"?"X":"", 1, 0, "L", 0);
    $oPdf->cell(19,  4 + $zaltura,  "NÃO ",  0,  0,  "L",  0);  
    $oPdf->cell(4,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);



    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(18,  4 + $zaltura,  "NATURAL DE: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(36,  4 + $zaltura, $municnat, 0, 0, "L", 0); 
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(22,  4 + $zaltura,  "UF/NASCIMENTO: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(34,  4 + $zaltura, $ufnat, 0, 0, "L", 0);   
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(22,  4 + $zaltura,  "CARTÃO DO SUS: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(46,  4 + $zaltura, $ed47_cartaosus, 0, 0, "L", 0);
    $oPdf->cell(13,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);



    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(15,  4 + $zaltura,  "ENDEREÇO: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);    
    $oPdf->cell(95,  4 + $zaltura, $endereco, 0, 0, "L", 0);    
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(7,  4 + $zaltura,  "CEP: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(10,  4 + $zaltura, $ed47_v_cep, 0, 0, "L", 0);
    $oPdf->cell(64,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);

    



    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(12,  4 + $zaltura,  "BAIRRO: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(98,  4 + $zaltura, $ed47_v_bairro, 0, 0, "L", 0);    
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(15,  4 + $zaltura,  "MUNICÍPIO: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7 + $zaltura2);
    $oPdf->cell(55,  4 + $zaltura, $municend, 0, 0, "L", 0); 
    $oPdf->cell(11,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);


    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(28,  4 + $zaltura,  "POSSUI DEFICIÊNCIA: ",  0,  0,  "L",  0);
    $sSql22     = $oDaoAlunonecessidade->sql_query("", "*", "ed48_c_descr LIMIT 2", " ed214_i_aluno = $ed47_i_codigo");
    $rsResult22 = db_query($sSql22);
    
    if (!is_resource( $rsResult22)){
    	$oErro->sErro = pg_last_error();
    	throw new DBException( _M( MENSAGENS_EDU2_FICHAALUNO001 . 'erro_buscar_necessidade_aluno', $oErro ) );
    }
    
    $iCont = 0;
    $iLinhasNecessidades = pg_num_rows($rsResult22);
    
    if( $iLinhasNecessidades > 0 ) {
      $oPdf->cell(4, 4, "X", 1, 0, "L", 0);
      $oPdf->cell(8,  4 + $zaltura,  "SIM",  0,  0,  "L",  0);
      $oPdf->cell(4, 4, "", 1, 0, "L", 0);
      $oPdf->cell(8,  4 + $zaltura,  "NÃO",  0,  0,  "L",  0);      
    }else{      
      $oPdf->cell(4, 4, "", 1, 0, "L", 0);
      $oPdf->cell(8,  4 + $zaltura,  "SIM",  0,  0,  "L",  0);
      $oPdf->cell(4, 4, "X", 1, 0, "L", 0);
      $oPdf->cell(10,  4 + $zaltura,  "NÃO",  0,  0,  "L",  0);
    }    
    
    $oPdf->cell(37,  4 + $zaltura,  "ESPECIFIQUE:",  0,  0,  "L",  0);
    if( $iLinhasNecessidades > 0 ) {
      db_fieldsmemory($rsResult22, 0);      
      $oPdf->cell( 80, 4 + $zaltura, $ed48_c_descr,0, 0, "L", 0);      
    }else{
      $oPdf->cell(80, 4 + $zaltura, "",0, 0, "L", 0);
    }
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(20,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    

    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->cell(58,  4 + $zaltura,  "BENEFICIADO POR PROGRAMA DO GOVERNO: ",  0,  0,  "L",  0);
    if($ed47_c_bolsafamilia == "N"){
      $oPdf->cell(4, 4, "", 1, 0, "L", 0);
      $oPdf->cell(10,  4 + $zaltura,  "SIM",  0,  0,  "L",  0);
      $oPdf->cell(8,  4 + $zaltura,  "Qual?: ",  0,  0,  "L",  0);
      $oPdf->cell(60,  4 + $zaltura,  "",  0,  0,  "L",  0);
    }else{
      $oPdf->cell(4, 4, "X", 1, 0, "L", 0);
      $oPdf->cell(10,  4 + $zaltura,  "SIM",  0,  0,  "L",  0);
      $oPdf->cell(8,  4 + $zaltura,  "Qual?: ",  0,  0,  "L",  0);
      $oPdf->cell(60,  4 + $zaltura,  "Bolsa Família",  0,  0,  "L",  0);
    }    
    $oPdf->cell(10,  4 + $zaltura,  "NIS:",  0,  0,  "L",  0);
    $oPdf->cell(30,  4 + $zaltura,  $ed47_c_nis,  0,  0,  "L",  0);        
    $oPdf->cell(11,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->cell(194,  2 + $zaltura,  "",  "LBR",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7 + $zaltura2);
    $oPdf->Ln(5);
    
    $oPdf->cell(194,  1,  "",  "LTR",  1,  "C",  0);
    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->cell(20,  4 + $zaltura,  "RESPONSÁVEL: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(75,  4 + $zaltura, $responsaveis[0]["ov02_nome"], 0, 0, "L", 0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->cell(15,  4 + $zaltura,  "TELEFONE: ",  0,  0,  "L",  0);    
    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(20,  4 + $zaltura, $telpai, 0, 0, "L", 0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->cell(5,  4 + $zaltura,  "RG: ",  0,  0,  "L",  0);    
    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(24,  4 + $zaltura, $responsaveis[0]["ov02_ident"], 0, 0, "L", 0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->cell(6,  4 + $zaltura,  "CPF: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(25,  4 + $zaltura, $responsaveis[0]["ov02_cnpjcpf"], 0, 0, "L", 0);
    $oPdf->cell(1,  4 + $zaltura,  "",  "R",  1,  "C",  0);

    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->cell(20,  4 + $zaltura,  "RESPONSÁVEL: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(75,  4 + $zaltura, $responsaveis[1]["ov02_nome"], 0, 0, "L", 0);
    $oPdf->setfont('arial',  '',  7);    
    $oPdf->cell(15,  4 + $zaltura,  "TELEFONE: ",  0,  0,  "L",  0);    
    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(20,  4 + $zaltura, $telmae, 0, 0, "L", 0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->cell(5,  4 + $zaltura,  "RG: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(24,  4 + $zaltura, $responsaveis[1]["ov02_ident"], 0, 0, "L", 0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->cell(6,  4 + $zaltura,  "CPF: ",  0,  0,  "L",  0);
    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(25,  4 + $zaltura, $responsaveis[1]["ov02_cnpjcpf"], 0, 0, "L", 0);
    $oPdf->cell(1,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->cell(194,  2,  "",  "LBR",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->Ln(5);

    $oPdf->cell(194,  1,  "",  "LTR",  1,  "C",  0);
    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    if($rn == "sim"){
      $oPdf->cell(180,  4 + $zaltura,  "A CRIANÇA/ADOLESCENTE É REGISTRADA?  SIM ( X )   NÃO ( ) ",  0,  0,  "L",  0);  
    }else{
      $oPdf->cell(180,  4 + $zaltura,  "A CRIANÇA/ADOLESCENTE É REGISTRADA?  SIM ( )   NÃO ( X ) ",  0,  0,  "L",  0);
    }
    
    $oPdf->cell(11,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);    
    $oPdf->cell(180,  4 + $zaltura,  "EM CASO POSITIVO, REGISTRAR: ",  0,  0,  "L",  0);
    $oPdf->cell(11,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    if($rn == "sim"){
      $oPdf->cell(180,  4 + $zaltura,  "N° DO TERMO: {$ntermo}    LIVRO: {$nlivro}   FOLHAS: {$nfolha} ",  0,  0,  "L",  0);   
    }else{
      $oPdf->cell(180,  4 + $zaltura,  "N° DO TERMO: ________________________________________________________    LIVRO: _______________________   FOLHAS: ________________ ",  0,  0,  "L",  0);     
    }
    
    /*
    $oPdf->cell(11,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    if($registropelopai == 1){
      $oPdf->cell(180,  4 + $zaltura,  "A CRIANÇA/ADOLESCENTE É REGISTRADA EM NOME DO PAI?  SIM (X)   NÃO ( ) ",  0,  0,  "L",  0);    
    }else{
      $oPdf->cell(180,  4 + $zaltura,  "A CRIANÇA/ADOLESCENTE É REGISTRADA EM NOME DO PAI?  SIM ( )   NÃO (X) ",  0,  0,  "L",  0);    
    }
    */
    
    
    $oPdf->cell(11,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->cell(194,  2,  "",  "LBR",  1,  "C",  0);    
    $oPdf->setfont('arial',  '',  7);
    $oPdf->Ln(5);


    $oPdf->cell(194,  1,  "",  "LTR",  1,  "C",  0);
    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->cell(150,  4 + $zaltura,  "NOME DO RESPONSÁVEL: {$nomecontato}",  0,  0,  "L",  0);
    $oPdf->cell(41,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    $oPdf->cell(3,  4 + $zaltura,  "",  "L",  0,  "C",  0);
    $oPdf->cell(130,  4 + $zaltura,  "EMPRESA OU LOCAL DE TRABALHO: {$localcontato}",  0,  0,  "L",  0);
    $oPdf->cell(20,  4 + $zaltura,  "TEL: {$telcontato}",  0,  0,  "L",  0);
    $oPdf->cell(41,  4 + $zaltura,  "",  "R",  1,  "C",  0);
    
    
    $oPdf->cell(194,  2,  "",  "LBR",  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7);
    $oPdf->Ln(5);

    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(194,  4,  "MATRÍCULA",  1,  1,  "C",  0);    
    $oPdf->cell(97,  4,  "EDUCAÇÃO INFANTIL",  1,  0,  "C",  0);
    $oPdf->cell(97,  4,  "ENSINO FUNDAMENTAL",  1,  1,  "C",  0);
    $oPdf->cell(48.5,  4,  "Creche",  1,  0,  "C",  0);
    $oPdf->cell(48.5,  4,  "Pré-Escolar",  1,  0,  "C",  0);
    $oPdf->cell(48.5,  4,  "Anos Inicias",  1,  0,  "C",  0);
    $oPdf->cell(48.5,  4,  "Anos Finais",  1,  1,  "C",  0);
    $oPdf->setfont('arial',  '',  7);

    $oPdf->cell(48.5,  2,  "",  "LR",  0,  "C",  0);
    $oPdf->cell(48.5,  2,  "",  "LR",  0,  "C",  0);
    $oPdf->cell(48.5,  2,  "",  "LR",  0,  "C",  0);
    $oPdf->cell(48.5,  2,  "",  "LR",  1,  "C",  0);
    $oPdf->cell(15,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(8,  6,  "",  1,  0,  "L",  0);
    $oPdf->cell(25.5,  6,  "Berçário",  0,  0,  "L",  0);
    
    $oPdf->cell(15,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(8,  6,  "",  1,  0,  "L",  0);
    $oPdf->cell(25.5,  6,  "Período",  0,  0,  "L",  0);
    
    $oPdf->cell(15,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(8,  6,  "",  1,  0,  "L",  0);
    $oPdf->cell(25.5,  6,  "Ano",  0,  0,  "L",  0);
    
    $oPdf->cell(15,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(8,  6,  "",  1,  0,  "L",  0);
    $oPdf->cell(25,  6,  "Ano",  0,  0,  "L",  0);
    $oPdf->cell(0.5,  6,  "",  "R",  1,  "L",  0);
    
    $oPdf->cell(48.5,  3,  "",  "LR",  0,  "C",  0);
    $oPdf->cell(48.5,  3,  "",  "LR",  0,  "C",  0);
    $oPdf->cell(48.5,  3,  "",  "LR",  0,  "C",  0);
    $oPdf->cell(48.5,  3,  "",  "LR",  1,  "C",  0);
    
    $oPdf->cell(15,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(8,  6,  "",  1,  0,  "L",  0);
    $oPdf->cell(25.5,  6,  "Maternal",  "R",  0,  "L",  0);

    $oPdf->cell(48.5,  6,  "",  "LR",  0,  "C",  0);
    $oPdf->cell(48.5,  6,  "",  "LR",  0,  "C",  0);
    $oPdf->cell(48.5,  6,  "",  "LR",  1,  "C",  0);

    $oPdf->cell(48.5,  3,  "",  "BLR",  0,  "C",  0);
    $oPdf->cell(48.5,  3,  "",  "BLR",  0,  "C",  0);
    $oPdf->cell(48.5,  3,  "",  "BLR",  0,  "C",  0);
    $oPdf->cell(48.5,  3,  "",  "BLR",  1,  "C",  0);       

    $oPdf->Ln(5);

    $oPdf->cell(194,  1,  "",  0,  1,  "C",  0);
    $oPdf->cell(3,  4,  "",  0,  0,  "C",  0);
    $oPdf->cell(150,  4,  "MATRÍCULA REALIZADA EM ____/____/______                        POR: ____________________________________________",  0,  1,  "L",  0);
    $oPdf->cell(95,  4,  "",  0,  0,  "L",  0);
    $oPdf->cell(50,  4,  "Assinatura do Funcionário",  0,  1,  "L",  0);
    
    $oPdf->Ln(5);

    $oPdf->setfont('arial',  '',  10);
    $oPdf->cell(194,  4,  "",  "LTR",  1,  "C",  0);
    $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(150,  6,  "Comprometo-me cumprir fielmente o Regimento Escolar único da Rede Municipal de Ensino.",  0,  0,  "L",  0);
    $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);

    $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(150,  6,  "Declaro estar ciente de que o não comparecimento às aulas até 31 de março, sem justificativa por escrito,",  0,  0,  "L",  0);
    $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);

    $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(150,  6,  "implicará no cancelamento da matrícula.",  0,  0,  "L",  0);
    $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);

    //$oPdf->Ln(7);
    $oPdf->cell(60,  12,  "",  "L",  0,  "C",  0);
    $oPdf->cell(70,  12,  "",  "B",  0,  "C",  0);
    $oPdf->cell(64,  12,  "",  "R",  1,  "C",  0);

    $oPdf->cell(60,  12,  "",  "L",  0,  "C",  0);
    $oPdf->cell(70,  12,  "Assinatura do Responsável",  0,  0,  "C",  0);
    $oPdf->cell(64,  12,  "",  "R",  1,  "C",  0);

    $oPdf->cell(194,  10,  "",  "LBR",  1,  "C",  0);

    
    $oPdf->addpage('P');    

    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(194,  4,  "RENOVAÇÃO",  1,  1,  "C",  0);    
    $oPdf->cell(20,  4,  "ANO LETIVO",  1,  0,  "C",  0);
    $oPdf->cell(42,  4,  "ANO DE ESCOLARIDADE / CICLO",  1,  0,  "C",  0);
    $oPdf->cell(40,  4,  "MUDANÇA DE ENDEREÇO",  1,  0,  "C",  0);
    $oPdf->cell(30,  4,  "DATA DA RENOVAÇÃO",  1,  0,  "C",  0);
    $oPdf->cell(62,  4,  "ASSINATURA DO RESPONSÁVEL",  1,  1,  "C",  0);
    $oPdf->setfont('arial',  '',  8);

    $oPdf->cell(20,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(42,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(40,  4 + $zaltura,  "(   ) Sim    (   ) Não",  1,  0,  "C",  0);
    $oPdf->cell(30,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(62,  4 + $zaltura,  "",  1,  1,  "C",  0);

    $oPdf->cell(20,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(42,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(40,  4 + $zaltura,  "(   ) Sim    (   ) Não",  1,  0,  "C",  0);
    $oPdf->cell(30,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(62,  4 + $zaltura,  "",  1,  1,  "C",  0);

    $oPdf->cell(20,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(42,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(40,  4 + $zaltura,  "(   ) Sim    (   ) Não",  1,  0,  "C",  0);
    $oPdf->cell(30,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(62,  4 + $zaltura,  "",  1,  1,  "C",  0);

    $oPdf->cell(20,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(42,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(40,  4 + $zaltura,  "(   ) Sim    (   ) Não",  1,  0,  "C",  0);
    $oPdf->cell(30,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(62,  4 + $zaltura,  "",  1,  1,  "C",  0);

    $oPdf->cell(20,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(42,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(40,  4 + $zaltura,  "(   ) Sim    (   ) Não",  1,  0,  "C",  0);
    $oPdf->cell(30,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(62,  4 + $zaltura,  "",  1,  1,  "C",  0);

    $oPdf->cell(20,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(42,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(40,  4 + $zaltura,  "(   ) Sim    (   ) Não",  1,  0,  "C",  0);
    $oPdf->cell(30,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(62,  4 + $zaltura,  "",  1,  1,  "C",  0);

    $oPdf->cell(20,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(42,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(40,  4 + $zaltura,  "(   ) Sim    (   ) Não",  1,  0,  "C",  0);
    $oPdf->cell(30,  4 + $zaltura,  "",  1,  0,  "C",  0);
    $oPdf->cell(62,  4 + $zaltura,  "",  1,  1,  "C",  0);

    $oPdf->setfont('arial',  '',  7);

    
    $oPdf->Ln(5);

    $oPdf->setfont('arial',  'b',  7);
    $oPdf->cell(194,  4,  "AUTORIZAÇÃO PARA A EXIBIÇÃO DE IMAGENS",  1,  1,  "C",  0);
    $oPdf->cell(194,  4,  "",  "RL",  1,  "C",  0);
    $oPdf->cell(15,  6,  "",  "L",  0,  "C",  0);
    $oPdf->setfont('arial',  '',  10);    
    $oPdf->cell(150,  6,  "Autorizo o uso de imagem de meu filho(a), ". $ed47_v_nome .", em atividades escolares ",  0,  0,  "L",  0);
    $oPdf->cell(29,  6,  "",  "R",  1,  "C",  0);
    $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(150,  6,  "realizadas neste estabelecimento de ensino tais como: murais, jornais, vídeos, projetos, cartazes, eventos da SME, ",  0,  0,  "L",  0);
    $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);
    $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(150,  6,  "outdoor da PMVR, redes sociais e afins.",  0,  0,  "L",  0);
    $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);

    $oPdf->cell(194,  4,  "",  "RL",  1,  "C",  0);

    $oPdf->cell(15,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(150,  6,  "(   ) SIM                                      (   ) NÃO",  0,  0,  "C",  0);
    $oPdf->cell(29,  6,  "",  "R",  1,  "C",  0);
    
    $oPdf->cell(194,  4,  "",  "RL",  1,  "C",  0);

    $oPdf->cell(15,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(150,  6,  "Volta Redonda, ____de____________________de________",  0,  0,  "C",  0);
    $oPdf->cell(29,  6,  "",  "R",  1,  "C",  0);
    
    $oPdf->cell(194,  4,  "",  "RL",  1,  "C",  0);

    $oPdf->cell(9,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(105,  6,  "___________________________________",  0,  0,  "L",  0);
    $oPdf->cell(45,  6,  "___________________________________",  0,  0,  "L",  0);
    $oPdf->cell(35,  6,  "",  "R",  1,  "C",  0);

    $oPdf->cell(9,  6,  "",  "L",  0,  "C",  0);
    $oPdf->cell(68,  6,  "Assinatura do Responsável",  0,  0,  "C",  0);
    $oPdf->cell(79,  6,  "Direção",  0,  0,  "R",  0);
    $oPdf->cell(38,  6,  "",  "R",  1,  "C",  0);

    $oPdf->cell(194,  2,  "",  "RLB",  1,  "C",  0);

    // $oPdf->setfont('arial',  'b',  9);
    // $oPdf->cell(194,  2,  "",  "RL",  1,  "C",  0);
    
    // $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);    
    // $oPdf->cell(150,  6,  "AUTORIZO A EXIBIÇÃO DA IMAGEM E DA VOZ DO MEU(MINHA) FILHO(A)",  0,  0,  "L",  0);
    // $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);

    // $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);
    // $oPdf->cell(150,  6,  $ed47_v_nome . " NAS ATIVIDADES DIVULGADAS PELA ESCOLA, considerando os direitos ",  0,  0,  "L",  0);
    // $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);

    // $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);
    // $oPdf->cell(150,  6,  "assegurados nos incisos X do art. 5° da Constituição Federal e o art. 20 do Código Civil Brasileiro e demais leis aplicadas",  0,  0,  "L",  0);
    // $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);

    // $oPdf->cell(3,  6,  "",  "L",  0,  "C",  0);
    // $oPdf->cell(150,  6,  "à espécie.",  0,  0,  "L",  0);
    // $oPdf->cell(41,  6,  "",  "R",  1,  "C",  0);

    // $oPdf->cell(194,  10,  "",  "RL",  1,  "C",  0);

    // $oPdf->cell(20,  6,  "",  "L",  0,  "C",  0);
    // $oPdf->cell(100,  6,  "Data: ____/____/________",  0,  0,  "L",  0);
    // $oPdf->cell(30,  6,  "___________________________________",  0,  0,  "L",  0);
    // $oPdf->cell(44,  6,  "",  "R",  1,  "C",  0);

    
    // $oPdf->setfont('arial',  '',  9);
    // $oPdf->cell(20,  6,  "",  "L",  0,  "C",  0);
    // $oPdf->cell(113,  6,  "",  0,  0,  "L",  0);
    // $oPdf->cell(30,  6,  "Assinatura do Responsável",  0,  0,  "L",  0);
    // $oPdf->cell(31,  6,  "",  "R",  1,  "C",  0);
    
    // $oPdf->cell(194,  2,  "",  "RLB",  1,  "C",  0);
   
  }

  $oPdf->Output();
} catch ( Exception $oErro ) {
//  db_redireciona('db_erros.php?fechar=true&db_erro=' . $oErro->getMessage());
}






