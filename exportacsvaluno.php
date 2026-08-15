<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
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

require_once ("libs/db_stdlibwebseller.php");
require_once ("libs/db_stdlib.php");
require_once ("libs/db_app.utils.php");
require_once ("libs/db_conecta.php");
require_once ("libs/db_sessoes.php");
require_once ("libs/db_usuariosonline.php");
require_once ("libs/db_utils.php");
require_once ("dbforms/db_funcoes.php");
 
function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function retornaMatricula($codigoaluno){
  $sql = pg_query("SELECT ed60_i_codigo from matricula where ed60_i_aluno = {$codigoaluno} and ed60_c_concluida= 'N' and ed60_c_ativa = 'S'");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed60_i_codigo"];
}

function buscaDadosAluno($codigoaluno){
  $sql = pg_query("SELECT * FROM aluno WHERE ed47_i_codigo = {$codigoaluno}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function retornaDataMatricula($codigoaluno){
  $sql = pg_query("SELECT ed60_d_datamatricula from matricula where ed60_i_aluno = {$codigoaluno} and ed60_c_concluida= 'N' and ed60_c_ativa = 'S'");
  $resultado = pg_fetch_all($sql);
  return implode("/", array_reverse(explode("-", $resultado[0]["ed60_d_datamatricula"])));
}

function buscaDadosTurma($matricula){
  $sql = pg_query("SELECT turma.ed57_c_descr, escola.ed18_c_nome FROM matricula INNER JOIN aluno ON aluno.ed47_i_codigo = matricula.ed60_i_aluno LEFT JOIN pais ON pais.ed228_i_codigo = aluno.ed47_i_pais INNER JOIN turma ON turma.ed57_i_codigo = matricula.ed60_i_turma INNER JOIN escola ON escola.ed18_i_codigo = turma.ed57_i_escola LEFT JOIN censouf ON censouf.ed260_i_codigo = escola.ed18_i_censouf LEFT JOIN censomunic ON censomunic.ed261_i_codigo = escola.ed18_i_censomunic INNER JOIN turno ON turno.ed15_i_codigo = turma.ed57_i_turno INNER JOIN sala ON sala.ed16_i_codigo = turma.ed57_i_sala INNER JOIN calendario ON calendario.ed52_i_codigo = turma.ed57_i_calendario INNER JOIN base ON base.ed31_i_codigo = turma.ed57_i_base INNER JOIN cursoedu ON cursoedu.ed29_i_codigo = base.ed31_i_curso INNER JOIN ensino ON ensino.ed10_i_codigo = cursoedu.ed29_i_ensino INNER JOIN matriculaserie ON matriculaserie.ed221_i_matricula = matricula.ed60_i_codigo INNER JOIN serie ON serie.ed11_i_codigo = matriculaserie.ed221_i_serie INNER JOIN serieregimemat ON serieregimemat.ed223_i_serie = serie.ed11_i_codigo INNER JOIN turmaserieregimemat ON turmaserieregimemat.ed220_i_serieregimemat = serieregimemat.ed223_i_codigo AND turmaserieregimemat.ed220_i_turma = matricula.ed60_i_turma INNER JOIN procedimento ON procedimento.ed40_i_codigo = turmaserieregimemat.ed220_i_procedimento LEFT JOIN turma AS turmaant ON turmaant.ed57_i_codigo = matricula.ed60_i_turmaant LEFT JOIN escola AS escolaant ON escolaant.ed18_i_codigo = turmaant.ed57_i_escola LEFT JOIN turno AS turnoant ON turnoant.ed15_i_codigo = turmaant.ed57_i_turno LEFT JOIN sala AS salaant ON salaant.ed16_i_codigo = turmaant.ed57_i_sala LEFT JOIN calendario AS calendarioant ON calendarioant.ed52_i_codigo = turmaant.ed57_i_calendario LEFT JOIN base AS baseant ON baseant.ed31_i_codigo = turmaant.ed57_i_base LEFT JOIN alunoprimat ON alunoprimat.ed76_i_aluno = aluno.ed47_i_codigo LEFT JOIN escola AS escolaprimat ON escolaprimat.ed18_i_codigo = alunoprimat.ed76_i_escola LEFT JOIN escolaproc ON escolaproc.ed82_i_codigo = alunoprimat.ed76_i_escola WHERE matriculaserie.ed221_c_origem = 'S' AND ed60_i_codigo = {$matricula}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function retornaNome($codigo){
  $sql = pg_query("SELECT ed47_v_nome FROM aluno WHERE ed47_i_codigo = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed47_v_nome"];
}

$codigoaluno = $_GET["ed49_i_aluno"];
//$nomealuno = $_GET["ed47_v_nome"];
$nomealuno = retornaNome($codigoaluno);
//die("Teste");




if($_POST){  
  $codaluno = $_POST["ed280_i_aluno"];
  $nome = trim($_POST["ed47_v_nome"]);
  $nome = explode(" ", $nome);

  $dadosaluno = buscaDadosAluno($codaluno);
  $matricula = retornaMatricula($codaluno);  
  $dadosturma = buscaDadosTurma($matricula);
  
  $primeironome = $nome[0];
  $demaisnomes = implode(" ", array_slice($nome, 1));
  $datanascimento = implode("/", array_reverse(explode("-", $dadosaluno["ed47_d_nasc"])));
  $datamatricula = retornaDataMatricula($codaluno);
  $unidadeescolar = trim($dadosturma["ed18_c_nome"]);
  $turma = trim($dadosturma["ed57_c_descr"]);
  $email = $dadosaluno["ed47_v_email"];

  $linha = $matricula .",". $primeironome .",". $demaisnomes .",". $datanascimento .",". $datamatricula .",". $unidadeescolar .",". $turma .",". $email;

  $arquivo = fopen("aluno".$codaluno.".csv", "w");
  fwrite($arquivo, $linha);
  fclose($arquivo);

  $file_url = "aluno".$codaluno.".csv";
  header('Content-Type: application/octet-stream');
  header("Content-Transfer-Encoding: Binary"); 
  header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
  readfile($file_url); 
  unlink($file_url); 
  exit();
  

  
}

?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?
    db_app::load("scripts.js, 
                  prototype.js, 
                  strings.js, 
                  arrays.js,
                  dbcomboBox.widget.js"
                  );
    
    db_app::load("estilos.css");
    ?>
  </head>
  <body>


<center>
<form  name="form1" method="post" action="">
<div class="container">
  <fieldset>
    <legend><b>Exporta Dados</b></legend>
    <table class="form-container">
      
      <tr>        
        <td> 
          <input title="" name="ed280_i_aluno" type="text" id="ed280_i_aluno" value="<?=$codigoaluno?>" size="10" maxlength="" readonly style="background-color:#DEB887;" autocomplete="">
          <input title="" name="ed47_v_nome" type="text" id="ed47_v_nome" value="<?=$nomealuno?>" size="40" maxlength="" readonly style="background-color:#DEB887;" >
          
        </td>
      </tr>
      
    </table>
    
    <input type="submit" name="geracsv" value="Gerar CSV">
    </fieldset>
  </div>
    
  </form>
</center>


   
     
  </body>
</html>


