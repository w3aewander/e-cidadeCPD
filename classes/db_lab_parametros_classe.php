<?php
/**
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

class cl_lab_parametros
{
    // cria variaveis de erro
    public $rotulo = null;
    public $query_sql = null;
    public $numrows = 0;
    public $numrows_incluir = 0;
    public $numrows_alterar = 0;
    public $numrows_excluir = 0;
    public $erro_status = null;
    public $erro_sql = null;
    public $erro_banco = null;
    public $erro_msg = null;
    public $erro_campo = null;
    public $pagina_retorno = null;
    /* Variáveis do Arquivo */
    public $la49_i_codigo = 0;
    public $la49_c_estrutural = null;
    public $la49_i_exameduplo = 0;
    public $la49_modelocoletaamostra = null;
    public $la49_integracao = null;
    public $la49_habilitarabsurdo = null;
    public $la49_modelocomprovanterequisicao = null;
    public $la49_autorizarexamesaoconfirmar = null;
    public $la49_numerocontroleinterno = null;
    public $la49_habilitargrupo = null;
    public $la49_modelolaudo = 0;
    public $la49_exibeliberacaoporexame = 'f';
    // cria propriedade com as variaveis do arquivo
    public $campos = "
                 la49_i_codigo = int4 = Código
                 la49_c_estrutural = char(50) = Estrutural
                 la49_i_exameduplo = int4 = Liberar Exames Duplos
                 la49_modelocoletaamostra = int4 = Modelo de Impressão Coleta de Amostra
                 la49_integracao = int4 = Campo de integracao
                 la49_habilitarabsurdo = int4 = Campo de configuração
                 la49_modelocomprovanterequisicao = int4 = Campo responsavel por armazenar o tipo do modelo do relatorio de comprovante de requisição
                 la49_autorizarexamesaoconfirmar = boolean = Flag que quando True autoriza automaticamente exames ao confirmar o lançamento
                 la49_numerocontroleinterno = boolean = Flag que quando True habilita campo de controle do número interno da requisição
                 la49_habilitargrupo = boolean = Flag que quando True habilita uso de grupos de exames.
                 la49_modelolaudo = int2 = Modelo padrão de laudo de exame.
                 la49_exibeliberacaoporexame = bool = Exibir Liberação por Exame
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("lab_parametros");
        $this->pagina_retorno = basename($_SERVER['PHP_SELF']);
    }

    public function erro($mostra, $retorna)
    {
        if(($this->erro_status == "0") || ($mostra == true && $this->erro_status != null )){
            echo "<script>alert(\"".$this->erro_msg."\")</script>";
            if($retorna==true){
                echo "<script>location.href='".$this->pagina_retorno."'</script>";
            }
        }
    }

    public function atualizacampos($exclusao = false)
    {
        if($exclusao==false){
            $this->la49_i_codigo = ($this->la49_i_codigo == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_i_codigo"]:$this->la49_i_codigo);
            $this->la49_c_estrutural = ($this->la49_c_estrutural == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_c_estrutural"]:$this->la49_c_estrutural);
            $this->la49_i_exameduplo = ($this->la49_i_exameduplo == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_i_exameduplo"]:$this->la49_i_exameduplo);
            $this->la49_modelocoletaamostra = ($this->la49_modelocoletaamostra == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_modelocoletaamostra"]:$this->la49_modelocoletaamostra);
            $this->la49_integracao = ($this->la49_integracao == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_integracao"]:$this->la49_integracao);
            $this->la49_habilitarabsurdo = ($this->la49_habilitarabsurdo == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_habilitarabsurdo"]:$this->la49_habilitarabsurdo);
            $this->la49_modelocomprovanterequisicao = ($this->la49_modelocomprovanterequisicao == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_modelocomprovanterequisicao"]:$this->la49_modelocomprovanterequisicao);
            $this->la49_autorizarexamesaoconfirmar = ($this->la49_autorizarexamesaoconfirmar == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_autorizarexamesaoconfirmar"]:$this->la49_autorizarexamesaoconfirmar);
            $this->la49_numerocontroleinterno = ($this->la49_numerocontroleinterno == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_numerocontroleinterno"]:$this->la49_numerocontroleinterno);
            $this->la49_habilitargrupo = ($this->la49_habilitargrupo == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_habilitargrupo"]:$this->la49_habilitargrupo);
            $this->la49_modelolaudo = ($this->la49_modelolaudo == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_modelolaudo"]:$this->la49_modelolaudo);
            $this->la49_exibeliberacaoporexame = ($this->la49_exibeliberacaoporexame == "f"?@$GLOBALS["HTTP_POST_VARS"]["la49_exibeliberacaoporexame"]:$this->la49_exibeliberacaoporexame);
        }else{
            $this->la49_i_codigo = ($this->la49_i_codigo == ""?@$GLOBALS["HTTP_POST_VARS"]["la49_i_codigo"]:$this->la49_i_codigo);
        }
    }

    public function incluir($la49_i_codigo)
    {
        $this->atualizacampos();
        if($this->la49_c_estrutural == null ){
            $this->erro_sql = " Campo Estrutural nao Informado.";
            $this->erro_campo = "la49_c_estrutural";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if($this->la49_i_exameduplo == null ){
            $this->erro_sql = " Campo Liberar Exames Duplos nao Informado.";
            $this->erro_campo = "la49_i_exameduplo";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if($la49_i_codigo == "" || $la49_i_codigo == null ){
            $result = db_query("select nextval('Lab_parametros_la49_i_codigo_seq')");
            if($result==false){
                $this->erro_banco = str_replace("\n","",@pg_last_error());
                $this->erro_sql   = "Verifique o cadastro da sequencia: Lab_parametros_la49_i_codigo_seq do campo: la49_i_codigo";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
            $this->la49_i_codigo = pg_result($result,0,0);
        }else{
            $result = db_query("select last_value from Lab_parametros_la49_i_codigo_seq");
            if(($result != false) && (pg_result($result,0,0) < $la49_i_codigo)){
                $this->erro_sql = " Campo la49_i_codigo maior que último número da sequencia.";
                $this->erro_banco = "Sequencia menor que este número.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }else{
                $this->la49_i_codigo = $la49_i_codigo;
            }
        }
        if(($this->la49_i_codigo == null) || ($this->la49_i_codigo == "") ){
            $this->erro_sql = " Campo la49_i_codigo não declarado.";
            $this->erro_banco = "Chave Primaria zerada.";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if($this->la49_modelolaudo == null ){
            $this->erro_sql = " Campo Modelo padrão de laudo de exame. não informado.";
            $this->erro_campo = "la49_modelolaudo";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        $sql = "insert into lab_parametros(
                                       la49_i_codigo
                                      ,la49_c_estrutural
                                      ,la49_i_exameduplo
                                      ,la49_modelocoletaamostra
                                      ,la49_integracao
                                      ,la49_habilitarabsurdo
                                      ,la49_modelocomprovanterequisicao
                                      ,la49_autorizarexamesaoconfirmar
                                      ,la49_numerocontroleinterno
                                      ,la49_habilitargrupo
                                      ,la49_modelolaudo
                                      ,la49_exibeliberacaoporexame
                       )
                values (
                                $this->la49_i_codigo
                               ,$this->la49_c_estrutural
                               ,$this->la49_i_exameduplo
                               ,$this->la49_modelocoletaamostra
                               ,$this->la49_integracao
                               ,'$this->la49_habilitarabsurdo'
                               ,$this->la49_modelocomprovanterequisicao
                               ,'$this->la49_autorizarexamesaoconfirmar'
                               ,'$this->la49_numerocontroleinterno'
                               ,'$this->la49_habilitargrupo'
                               ,$this->la49_modelolaudo
                               ,'$this->la49_exibeliberacaoporexame'
                      )";
        $result = db_query($sql);
        if($result==false){
            $this->erro_banco = str_replace("\n","",@pg_last_error());
            if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
                $this->erro_sql   = "Parâmetros ($this->la49_i_codigo) não Incluído. Inclusão Abortada.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_banco = "Parâmetros já Cadastrado";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            }else{
                $this->erro_sql   = "Parâmetros ($this->la49_i_codigo) não Incluído. Inclusão Abortada.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            }
            $this->erro_status = "0";
            $this->numrows_incluir= 0;
            return false;
        }
        $this->erro_banco = "";
        $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
        $this->erro_sql .= "Valores : ".$this->la49_i_codigo;
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "1";
        $this->numrows_incluir= pg_affected_rows($result);
        return true;
    }

    public function alterar($la49_i_codigo=null)
    {
        $this->atualizacampos();
        $sql = " update lab_parametros set ";
        $virgula = "";
        if(trim($this->la49_i_codigo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_i_codigo"])){
            $sql  .= $virgula." la49_i_codigo = $this->la49_i_codigo ";
            $virgula = ",";
            if(trim($this->la49_i_codigo) == null ){
                $this->erro_sql = " Campo Código não informado.";
                $this->erro_campo = "la49_i_codigo";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if(trim($this->la49_c_estrutural)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_c_estrutural"])){
            $sql  .= $virgula." la49_c_estrutural = '$this->la49_c_estrutural' ";
            $virgula = ",";
            if(trim($this->la49_c_estrutural) == null ){
                $this->erro_sql = " Campo Estrutural nao Informado.";
                $this->erro_campo = "la49_c_estrutural";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if(trim($this->la49_i_exameduplo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_i_exameduplo"])){
            $sql  .= $virgula." la49_i_exameduplo = $this->la49_i_exameduplo ";
            $virgula = ",";
            if(trim($this->la49_i_exameduplo) == null ){
                $this->erro_sql = " Campo Liberar Exames Duplos nao Informado.";
                $this->erro_campo = "la49_i_exameduplo";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }

        if(trim($this->la49_modelocoletaamostra)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_modelocoletaamostra"])){
            $sql  .= $virgula." la49_modelocoletaamostra = $this->la49_modelocoletaamostra ";
            $virgula = ",";
        }

        if(trim($this->la49_integracao)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_integracao"])){
            $sql  .= $virgula." la49_integracao = $this->la49_integracao ";
            $virgula = ",";
        }

        if(trim($this->la49_habilitarabsurdo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_habilitarabsurdo"])){
            $sql  .= $virgula." la49_habilitarabsurdo = '$this->la49_habilitarabsurdo' ";
            $virgula = ",";
        }

        if(trim($this->la49_modelocomprovanterequisicao)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_modelocomprovanterequisicao"])){
            $sql  .= $virgula." la49_modelocomprovanterequisicao = '$this->la49_modelocomprovanterequisicao' ";
            $virgula = ",";
        }

        if(trim($this->la49_autorizarexamesaoconfirmar)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_autorizarexamesaoconfirmar"])){
            $sql  .= $virgula." la49_autorizarexamesaoconfirmar = '$this->la49_autorizarexamesaoconfirmar' ";
            $virgula = ",";
        }

        if(trim($this->la49_numerocontroleinterno)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_numerocontroleinterno"])){
            $sql  .= $virgula." la49_numerocontroleinterno = '$this->la49_numerocontroleinterno' ";
            $virgula = ",";
        }

        if(trim($this->la49_habilitargrupo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_habilitargrupo"])){
            $sql  .= $virgula." la49_habilitargrupo = '$this->la49_habilitargrupo' ";
            $virgula = ",";
        }
        if(trim($this->la49_modelolaudo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_modelolaudo"])){
            $sql  .= $virgula." la49_modelolaudo = $this->la49_modelolaudo ";
            $virgula = ",";
        }
        if(trim($this->la49_exibeliberacaoporexame)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la49_exibeliberacaoporexame"])){
            $sql  .= $virgula." la49_exibeliberacaoporexame = '$this->la49_exibeliberacaoporexame' ";
            $virgula = ",";
        }
        $sql .= " where ";
        if($la49_i_codigo!=null){
            $sql .= " la49_i_codigo = $this->la49_i_codigo";
        }
        $result = db_query($sql);
        if (!$result) {
            $this->erro_banco = str_replace("\n","",@pg_last_error());
            $this->erro_sql   = "Parâmetros não Alterado. Alteração Abortada.\\n";
            $this->erro_sql .= "Valores : ".$this->la49_i_codigo;
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            $this->numrows_alterar = 0;
            return false;
        } else {
            if (pg_affected_rows($result) == 0) {
                $this->erro_banco = "";
                $this->erro_sql = "Parâmetros não foi Alterado. Alteração Executada.\\n";
                $this->erro_sql .= "Valores : ".$this->la49_i_codigo;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_alterar = 0;
                return true;
            } else {
                $this->erro_banco = "";
                $this->erro_sql = "Alteração efetuada com sucesso.\\n";
                $this->erro_sql .= "Valores : ".$this->la49_i_codigo;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_alterar = pg_affected_rows($result);
                return true;
            }
        }
    }

    public function excluir($la49_i_codigo=null, $dbwhere = null)
    {
        $sql = " delete from lab_parametros
                    where ";
        $sql2 = "";
        if (empty($dbwhere)) {
            if (!empty($la49_i_codigo)){
                if (!empty($sql2)) {
                    $sql2 .= " and ";
                }
                $sql2 .= " la49_i_codigo = $la49_i_codigo ";
            }
        } else {
            $sql2 = $dbwhere;
        }
        $result = db_query($sql.$sql2);
        if ($result == false) {
            $this->erro_banco = str_replace("\n","",@pg_last_error());
            $this->erro_sql   = "Parâmetros não Excluído. Exclusão Abortada.\\n";
            $this->erro_sql .= "Valores : ".$la49_i_codigo;
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            $this->numrows_excluir = 0;
            return false;
        } else {
            if (pg_affected_rows($result) == 0) {
                $this->erro_banco = "";
                $this->erro_sql = "Parâmetros não Encontrado. Exclusão não Efetuada.\\n";
                $this->erro_sql .= "Valores : ".$la49_i_codigo;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_excluir = 0;
                return true;
            } else {
                $this->erro_banco = "";
                $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
                $this->erro_sql .= "Valores : ".$la49_i_codigo;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_excluir = pg_affected_rows($result);
                return true;
            }
        }
    }

    public function sql_record($sql)
    {
        $result = db_query($sql);
        if (!$result) {
            $this->numrows    = 0;
            $this->erro_banco = str_replace("\n","",@pg_last_error());
            $this->erro_sql   = "Erro ao selecionar os registros.";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        $this->numrows = pg_num_rows($result);
        if ($this->numrows == 0) {
            $this->erro_banco = "";
            $this->erro_sql   = "Record Vazio na Tabela:lab_parametros";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        return $result;
    }

    public function sql_query($la49_i_codigo = null,$campos = "*", $ordem = null, $dbwhere = "") {

        $sql  = "select {$campos}";
        $sql .= "  from lab_parametros ";
        $sql2 = "";
        if (empty($dbwhere)) {
            if (!empty($la49_i_codigo)) {
                $sql2 .= " where lab_parametros.la49_i_codigo = $la49_i_codigo ";
            }
        } else if (!empty($dbwhere)) {
            $sql2 = " where $dbwhere";
        }
        $sql .= $sql2;
        if (!empty($ordem)) {
            $sql .= " order by {$ordem}";
        }
        return $sql;
    }

    public function sql_query_file($la49_i_codigo = null, $campos = "*", $ordem = null, $dbwhere = "") {

        $sql  = "select {$campos} ";
        $sql .= "  from lab_parametros ";
        $sql2 = "";
        if (empty($dbwhere)) {
            if (!empty($la49_i_codigo)){
                $sql2 .= " where lab_parametros.la49_i_codigo = $la49_i_codigo ";
            }
        } else if (!empty($dbwhere)) {
            $sql2 = " where $dbwhere";
        }
        $sql .= $sql2;
        if (!empty($ordem)) {
            $sql .= " order by {$ordem}";
        }
        return $sql;
    }

}
