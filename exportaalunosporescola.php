<?
/*
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_aluno_classe.php"));
require_once(modification("classes/db_serie_classe.php"));
require_once(modification("classes/db_alunocurso_classe.php"));
require_once(modification("classes/db_alunopossib_classe.php"));
require_once(modification("classes/db_cursoescola_classe.php"));
require_once(modification("libs/db_jsplibwebseller.php"));
db_postmemory($_POST);
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
  //$sql = pg_query("SELECT ed60_i_codigo from matricula where ed60_i_aluno = {$codigoaluno} and ed60_c_concluida= 'N' and ed60_c_ativa = 'S'");
  $sql = pg_query("SELECT ed60_i_codigo from matricula where ed60_i_aluno = {$codigoaluno} and ed60_c_ativa = 'S'");
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


function listaAlunosPorEscola($codescola){
  $sql = pg_query("SELECT DISTINCT ed47_i_codigo,to_ascii(ed47_v_nome) as ed47_v_nome, ed56_i_escola as escola from historico inner join aluno on ed47_i_codigo = ed61_i_aluno left join alunocurso on ed56_i_aluno = ed47_i_codigo left join historicomps on ed62_i_historico = ed61_i_codigo left join historicompsfora on ed99_i_historico = ed61_i_codigo where ( ed62_i_escola = {$codescola} OR ed56_i_escola = {$codescola} ) order by to_ascii(ed47_v_nome)");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}





$claluno       = new cl_aluno;
$clserie       = new cl_serie;
$clalunocurso  = new cl_alunocurso;
$clalunopossib = new cl_alunopossib;
$clcursoescola = new cl_cursoescola;
$clrotulo      = new rotulocampo;
$clrotulo->label("ed47_i_codigo");
$clrotulo->label("ed60_i_codigo");
$clrotulo->label("ed47_v_nome");
$clrotulo->label("ed47_v_pai");
$clrotulo->label("ed47_v_mae");
$clrotulo->label("ed56_c_situacao");
$clrotulo->label("ed223_i_serie");
$clrotulo->label("ed31_i_curso");
$clrotulo->label("ed56_i_escola");

$codescola    = empty($codescola) ? 0 : $codescola;
$codcurso     = empty($codcurso)  ? 0 : $codcurso;



if($_POST){  
  $codigo_escola = $_POST["ed56_i_escola"];
  $alunos = listaAlunosPorEscola($codigo_escola);

  $guardadados = array();
  foreach ($alunos as $aluno){    
    $codaluno = $aluno["ed47_i_codigo"];
    $nome = trim($aluno["ed47_v_nome"]);
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
    array_push($guardadados, $linha);
    //$arquivo = fopen("alunosescola".$codigo_escola.".csv", "w");
    //fwrite($arquivo, $linha);
    //fclose($arquivo);
  }
  //testa($guardadados); die("Confere");
  ob_clean();
  $arquivo = fopen("alunosescola".$codigo_escola.".csv", "w");
  foreach ($guardadados as $linha) {
    fwrite($arquivo, $linha . "\n");
  }
  fclose($arquivo); 

  $file_url = "alunosescola".$codigo_escola.".csv";
  header('Content-Type: application/octet-stream');
  header("Content-Transfer-Encoding: Binary"); 
  header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
  readfile($file_url); 
  unlink($file_url);
  exit();
  


/*
  $arquivo = fopen("alunosescola".$codigo_escola.".csv", "w");
  foreach ($guardadados as $linha) {
    fwrite($arquivo, $linha . "\n");
  }
  fclose($arquivo); 

  
  $file_url = "alunosescola".$codigo_escola.".csv";
  
  header('Content-Type: application/octet-stream');
  header("Content-Transfer-Encoding: Binary"); 
  header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
  readfile($file_url); 
  exit();
*/
    

}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

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

<body bgcolor="#CCCCCC" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form name="form1" action="" method="post">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
 <tr>
  <td width="360" height="18">&nbsp;</td>
  <td width="263">&nbsp;</td>
  <td width="25">&nbsp;</td>
  <td width="140">&nbsp;</td>
 </tr> 
</table>
<?//MsgAviso(db_getsession("DB_coddepto"),"escola");?>
<br>
<center>
<fieldset style="width:50%"><legend><b>Exportar Dados de Alunos</b></legend>
<table width="50%" border="0" cellspacing="0" cellpadding="0" bgcolor="#CCCCCC">
 <tr>
  
  <td valign="top">
   <table border="0" cellspacing="0">
    <tr>
     <td nowrap title="<?=$Ted56_i_escola?>">
      <?=$Led56_i_escola?>
     </td>
     <td>
      <?
      $result_escola = $clalunocurso->sql_record($clalunocurso->sql_query("",
                                                                          "DISTINCT ed18_i_codigo,ed18_c_nome",
                                                                          " ed18_c_nome",
                                                                          ""
                                                                         )
                                                );
      if ($clalunocurso->numrows==0) {
      	
        $x = array(''=>'NENHUM REGISTRO');
        db_select('ed56_i_escola',$x,true,1,"style='width:300px;'");
        
      } else {
      	
        ?>
        <select name="ed56_i_escola" id="ed56_i_escola" style="width:300px;">
         <option value=""></option>
         <?
         for ($x=0;$x<$clalunocurso->numrows;$x++) {
           db_fieldsmemory($result_escola,$x);
         ?>
           <option value="<?=$ed18_i_codigo?>" <?=$codescola==$ed18_i_codigo?"selected":""?>><?=$ed18_c_nome?></option>
          <?
         }
        ?>
       </select>
       <?
      }
      ?>
     </td>
    </tr>
    
   </table>
  </td>
 </tr>

 <tr>
  <td colspan="2" align="center">
   <br>
   <input type="submit" name="geracsv" value="Gerar CSV">
  </td>
 </tr>
</table>
<p><span><tr><td><h5>(A geração pode demorar alguns segundos)</h5></span></p>
</fieldset>
</form>

</center>
</body>
</html>

<?db_menu(db_getsession("DB_id_usuario"),
          db_getsession("DB_modulo"),
          db_getsession("DB_anousu"),
          db_getsession("DB_instit")
         );
?>
