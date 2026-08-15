<?php
 

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_classesgenericas.php"));


$iAnoSessao     = db_getsession("DB_anousu");


?>
<html>
<head>
<title>Microsist</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta http-equiv="Expires" CONTENT="0">
	<script type="text/javascript" src="scripts/scripts.js"></script>
	<script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
	<script type="text/javascript" src="scripts/widgets/DBLancador.widget.js"></script>
  <script type="text/javascript" src="scripts/EmissaoRelatorio.js"></script>
	<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
  <div class="container">
    <form name="form1">
    	<fieldset>
    		<legend>Relatório de Agregações Patrimoniais</legend>
        <table>
          <?php /* ?>
          <tr>
            <td>
              <label class="bold" for="periodoInicial">Período Inicial</label>
            </td>
            <td> </td>
            <td>
              <input type="text" name="inicio" id="inicio" size="5" style="margin-right: 25px" onkeyup="valida(this)" required>
              <label class="bold" for="periodoFinal"> Período Final </label>
              <input type="text" name="fim" id="fim" size="5" onkeyup="valida(this)" required>
            </td>
       	  </tr>          
          <?php */ ?>
          <tr>
            <td>
              <label class="bold" for="periodoInicial">Período Inicial</label>
              <input type="date" name="inicio" id="inicio" size="5" style="margin-right: 25px" required>
            </td>
            
            <td>              
              <label class="bold" for="periodoFinal"> Período Final </label>
              <input type="date" name="fim" id="fim" size="5" required>
            </td>
          </tr>

          <tr>
            <td colspan="2">              
              <b>Placa:</b>
              <input type="text" name="placa" id="placa">
            </td>
            
          </tr>


          <tr>
            <td colspan="2"><b>Tipo:</b><br>
              <input type="radio" id="todos" name="tipo" checked value="t">Todos<br>
              <input type="radio" id="baixados" name="tipo" value="b">Baixados<br>
              <input type="radio" id="naobaixados" name="tipo" value="nb">Não Baixados
          </td>
          
          </tr>
        </table>
    	</fieldset>

      <input type="button" id="btnEmitir" name="btnEmitir" value="Emitir" onclick="js_emitir()">
    </form>
  </div>
  <?php db_menu(); ?>
  <script>
    function valida(obj){
      var expr = new RegExp("[^0-9]+");
      if(obj.value.match(expr)) {
        alert("Este campo deve ser preenchido somente com números!");
        //obj.select();
        obj.value = "";
      }
    }

    function js_emitir() {
        var inicio = document.getElementById("inicio").value;
        var fim = document.getElementById("fim").value;
        var tipo = document.querySelector('input[name="tipo"]:checked').value;
        var placa = document.getElementById("placa").value;        

        if(inicio == ""){
          alert("Campo 'Período Inicial' obrigatório.");
          return false;
        }
        if(fim == ""){
          alert("Campo 'Período Final' obrigatório.");
          return false;
        }       
        
          

          var iHeight = (screen.availHeight - 40);
          var iWidth  = (screen.availWidth - 5);
          var sOpcoes = 'width=' + iWidth + ',height=' + iHeight + ',scrollbars=1,location=0';
          //var oJanela = window.open('gerarelatorioagregacao.php?inicio='+inicio+'&fim='+fim+'&tipo='+tipo, '', sOpcoes);
          var oJanela = window.open('gerarelatorioagregacao.php?inicio='+inicio+'&fim='+fim+'&tipo='+tipo+'&placa='+placa, '', sOpcoes);
          oJanela.moveTo(0, 0);          
        }
    
  </script>
</body>
</html>