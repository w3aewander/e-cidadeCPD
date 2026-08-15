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
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_utils.php"));

if (isset($_GET['z01_numcgm']) && !empty($_GET['z01_numcgm'])) {
  $cgm = $_GET['z01_numcgm'];
}else{
  $cgm = 0;
}
?>

<html>

<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <script type="text/javascript" src="scripts/classes/http/http.js"></script>
  <script language="javascript" type="text/javascript" src="scripts/prototype.js"></script>
  <script language="javascript" type="text/javascript" src="scripts/widgets/DBDownload.widget.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1">
  <table align="center" style="padding-top:25px;" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td>
        <center>
          <script type="text/javascript">
            function verificaCampos() {
              let btnProcessar = document.getElementById('processar');
              let arquivo = document.getElementById('fileImportcgm').value;
              let descricao = document.getElementById('descrArquivoImport').value;
              let processarButton = document.getElementById('processar');
              if (btnProcessar.value == 'Processar') {
                if (arquivo.length > 0 && descricao.length > 0) {
                  processarButton.disabled = false;
                } else {
                  processarButton.disabled = true;
                }

              } else if (btnProcessar.value == 'Alterar') {
                if (descricao.length > 0) {
                  processarButton.disabled = false;
                } else {
                  processarButton.disabled = true;
                }
              }

            }

            async function processarDados() {

              let btnProcessar = document.getElementById('processar');
              const sApiUrl = "<?= ECIDADE_REQUEST_PATH ?>v4/api/";

              if (btnProcessar.value == 'Processar') {
                js_divCarregando('Aguarde!', 'msgbox');
                let verificaArquivo = document.getElementById('fileImportcgm').value;
                let descricao = document.getElementById('descrArquivoImport').value;
                let obs = document.getElementById('obsArquivoImport').value;
                let user = <?= db_getsession("DB_id_usuario") ?>;
                let cgm = <?= $cgm ?>;
                if (verificaArquivo.length > 0 && descricao.length > 0) {
                  let file = document.getElementById('fileImportcgm').files[0];
                  const data = new FormData();
                  data.append('cgm', cgm);
                  data.append('file', file);
                  data.append('desc', descricao);
                  data.append('obs', obs);
                  data.append('user', user);

                  await HttpClient.post(`${sApiUrl}patrimonial/protocolo/anexocgm/processar-arquivo`, {
                    body: data,
                    reportProgress: false
                  }).then(response => {
                    if (response.file && response.file[0] == 'arqInvalido') {
                      alert('Erro ao processar o arquivo. \n\n Erro: Arquivo Invalido.');
                      js_removeObj('msgbox');
                    }

                    if (response.data) {

                      if (response.data.saveInfo.sucesso && response.data.upload.sucesso) {
                        js_removeObj('msgbox');
                        alert('Arquivo processado com sucesso!');
                        getArquivoscgm(cgm);
                        formImportacaocgm.reset();
                        verificaCampos();

                      } else {
                        js_removeObj('msgbox');
                        alert('Erro ao processar o arquivo. \n\n Erro: ' + response.data.error);
                      }
                    }
                    js_removeObj('msgbox');

                  }).catch(error => {
                    js_removeObj('msgbox');
                    alert('Erro ao processar o arquivo. \n\n Erro: ' + error);
                  });
                  js_removeObj('msgbox');
                  return false;
                }

              } else if (btnProcessar.value == 'Alterar') {
                js_divCarregando('Aguarde!', 'msgbox');
                let descricao = document.getElementById('descrArquivoImport').value;
                let obs = document.getElementById('obsArquivoImport').value;
                let dadosAlteracao = document.getElementById('dadosAlteracao').value;
                let sequencial = dadosAlteracao.split('|')[0];
                if (descricao.length > 0) {
                  let cgm = <?= $cgm ?>;
                  const data = new FormData();
                  data.append('sequencial', sequencial);
                  data.append('desc', descricao);
                  data.append('obs', obs);
                  data.append('cgm', cgm);
                  HttpClient.post(`${sApiUrl}patrimonial/protocolo/anexocgm/update-dados-arquivo`, {
                    body: data,
                    reportProgress: false
                  }).then(response => {
                    if (response.data.sucesso == true) {
                      js_removeObj('msgbox');
                      alert('Dados alterados com sucesso!');
                      getArquivoscgm(cgm);
                      formImportacaocgm.reset();
                      verificaCampos();
                    } else {
                      js_removeObj('msgbox');
                      alert('Erro ao alterar o arquivo. \n\n Erro: ' + response.data.erro);
                    }
                  });
                  js_removeObj('msgbox');
                  return false;
                }
              }

            }


            function cancelarAlteracao() {
              let btnProcessar = document.getElementById('processar');
              btnProcessar.value = 'Processar';
              let inptArquivo = document.getElementById('fileImportcgm');
              inptArquivo.disabled = false;
              let btnCancelar = document.getElementById('cancelarAlteracao');
              formImportacaocgm.reset();
              if (btnCancelar) {
                btnCancelar.remove();
              }
            }

            function getArquivoscgm(cgm) {
              if (cgm) {
                const sApiUrl = "<?= ECIDADE_REQUEST_PATH ?>v4/api/";
                const data = new FormData();
                data.append('cgm', cgm);

                HttpClient.post(`${sApiUrl}patrimonial/protocolo/anexocgm/get-arquivos`, {
                  body: data,
                  reportProgress: false
                }).then(response => {
                  limpaTable();
                  if (response.data.length > 0) {
                    const colunas = [{
                        key: 'z34_sequencial',
                        label: 'Cód.'
                      },
                      {
                        key: 'nome',
                        label: 'Usuário'
                      },
                      {
                        key: 'z34_arquivo',
                        label: 'Arquivo'
                      },
                      {
                        key: 'z34_descricao',
                        label: 'Descrição'
                      },
                      {
                        key: 'z34_observacao',
                        label: 'Observação'
                      },
                      {
                        key: 'z34_data',
                        label: 'Data'
                      },
                      {
                        label: 'Baixar'
                      },
                      {
                        label: 'Alterar'
                      },
                      {
                        label: 'Excluir'
                      }
                    ];

                    let tableHtml = '<table border="1" cellpadding="5" cellspacing="0">';

                    tableHtml += '<thead><tr>';
                    colunas.forEach(coluna => {
                      tableHtml += `<th>${coluna.label}</th>`;
                    });
                    tableHtml += '</tr></thead>';

                    tableHtml += '<tbody>';
                    response.data.forEach(item => {
                      tableHtml += '<tr>';

                      colunas.slice(0, 6).forEach(coluna => {
                        tableHtml += `<td>${item[coluna.key] || ''}</td>`;
                      });


                      tableHtml += `<td><button class="btn-baixar" onclick="baixarArquivo(${item.z34_sequencial} + '|' + '${item.z34_idstorage}', ${cgm})">Baixar</button></td>`;
                      tableHtml += `<td><button class="btn-alterar" data-item='${JSON.stringify(item)}' data-cgm='${cgm}' onclick="alterarArquivo(this)">Alterar</button></td>`;
                      tableHtml += `<td><button class="btn-excluir" onclick="excluirArquivo(${item.z34_sequencial} + '|' + '${item.z34_idstorage}', ${cgm})">Excluir</button></td>`;
                      

                      tableHtml += '</tr>';
                    });
                    tableHtml += '</tbody>';

                    tableHtml += '</table>';

                    document.getElementById('painelarquivos').innerHTML = tableHtml;
                  }
                }).catch(error => {
                  console.error('Erro ao buscar os arquivos:', error);
                  document.getElementById('painelarquivos').innerHTML = 'Erro ao carregar arquivos.';
                });
              }
            }

            function alterarArquivo(button) {
              const item = JSON.parse(button.getAttribute('data-item'));
              const cgm = button.getAttribute('data-cgm');
              let inptArquivo = document.getElementById('fileImportcgm');
              let inptDescricao = document.getElementById('descrArquivoImport');
              let inptObservacao = document.getElementById('obsArquivoImport');
              let btnProcessar = document.getElementById('processar');
              let dadosAlteracao = document.getElementById('dadosAlteracao');

              if (item && cgm) {
                inptArquivo.disabled = true;
                inptDescricao.value = item.z34_descricao;
                inptObservacao.value = item.z34_observacao;
                dadosAlteracao.value = item.z34_sequencial + '|' + item.z34_idstorage;
                btnProcessar.value = 'Alterar';
                let btnCancelar = document.getElementById('cancelarAlteracao');
                if (!btnCancelar) {
                  let divButtons = document.getElementById('buttons');
                  btnCancelar = document.createElement('input');
                  btnCancelar.type = 'button';
                  btnCancelar.className = 'btn-cancelar';
                  btnCancelar.id = 'cancelarAlteracao';
                  btnCancelar.onclick = cancelarAlteracao;
                  btnCancelar.value = 'Cancelar';
                  divButtons.appendChild(btnCancelar);
                }
                verificaCampos();
              }
            }

            function baixarArquivo(dados) {
              if (dados) {
                js_divCarregando('Aguarde!', 'msgbox');
                let idArq = dados.split('|')[1];
                const sApiUrl = "<?= ECIDADE_REQUEST_PATH ?>v4/api/";
                const data = new FormData();
                data.append('idArq', idArq);
                HttpClient.post(`${sApiUrl}patrimonial/protocolo/anexocgm/download-arquivo`, {
                  body: data,
                  reportProgress: false
                }).then(response => {
                  js_removeObj('msgbox');
                  if (response.data.path !== null) {
                    var oDownload = new DBDownload();
                    oDownload.addFile(response.data.path, response.data.nomeArquivo);
                    oDownload.show();
                  } else {
                    alert('Erro ao baixar o arquivo.');
                  }
                });
              }
            }

            function excluirArquivo(dados, cgm) {
              let msg = 'Deseja realmente excluir o arquivo?';
              if (confirm(msg) !== true) {
                return;
              }
              if (dados) {
                const sApiUrl = "<?= ECIDADE_REQUEST_PATH ?>v4/api/";
                const data = new FormData();
                let idUser = <?= db_getsession("DB_id_usuario") ?>;
                let arquivo = dados.split('|');
                let sequencial = arquivo[0];
                let idArq = arquivo[1];
                data.append('sequencial', sequencial);
                data.append('idUser', idUser);
                data.append('idArq', idArq);
                data.append('cgm', cgm);
                HttpClient.post(`${sApiUrl}patrimonial/protocolo/anexocgm/delete-arquivo`, {
                  body: data,
                  reportProgress: false
                }).then(response => {
                  if (response.data.status == true) {
                    getArquivoscgm(cgm);
                    alert('Arquivo excluido com sucesso!');
                  } else {
                    alert('Erro ao excluir o arquivo.');
                    getArquivoscgm(cgm);
                  }
                });
              }
            }

            function limpaTable() {
              document.getElementById('painelarquivos').innerHTML = '';
            }

            <?php
            if ($cgm !== 0) {
              echo "getArquivoscgm($cgm);";
            }
            ?>
          </script>

          <fieldset>
            <legend>Anexos</legend>
            <form action="" method="post" name="formcgm" id="formImportacaocgm" enctype="multipart/form-data">
              <fieldset style="width: 500px">
                <legend>Upload</legend>
                <table class="form-container">
                  <tr>
                    <td><label for="numcgm">Numcgm:</label></td>
                    <td><input style="width: 100px;" type="text" name="cgmGuia" id="numcgm" disabled value="<?= $cgm ?>" /></td>
                  </tr>
                  <tr>
                    <td><label for="fileImportcgm">Arquivo:</label></td>
                    <td><input style="height: 25px; width: 422px;" type="file" name="filecgm" id="fileImportcgm" onchange="verificaCampos();" /></td>
                  </tr>
                  <tr>
                    <td><label for="descrArquivoImport">Descrição*:</label></td>
                    <td><input style="width: 100%;" type="text" name="descrArquivo" id="descrArquivoImport" onchange="verificaCampos();" /></td>
                  </tr>
                  <tr>
                    <td><label for="obsArquivoImport">Observação:</label></td>
                    <td><textarea name="obsArquivo" id="obsArquivoImport"></textarea></td>
                  </tr>
                  <input type="text" name="dadosalteracao" id="dadosAlteracao" value="" hidden>
                </table>
              </fieldset>
              <div id="buttons">
                <input type="button" id="processar" value="Processar" disabled onclick="processarDados();" />
                <input type="button" id="limpar" value="Limpar" onclick="formImportacaocgm.reset(); verificaCampos();" />
              </div>
            </form>
            <fieldset style="margin-top:25px; max-width: 1000px">
              <legend>Gerenciar Uploads</legend>
              <div id="painelarquivos">

              </div>
            </fieldset>
          </fieldset>

        </center>
      </td>
    </tr>
  </table>
</body>

</html>