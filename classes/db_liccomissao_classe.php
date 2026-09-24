<?php
//MODULO: licitacao
//CLASSE DA ENTIDADE liccomissao
class cl_liccomissao
{
   // cria variaveis de erro
    public $rotulo     = null;
    public $query_sql  = null;
    public $numrows    = 0;
    public $numrows_incluir = 0;
    public $numrows_alterar = 0;
    public $numrows_excluir = 0;
    public $erro_status= null;
    public $erro_sql   = null;
    public $erro_banco = null;
    public $erro_msg   = null;
    public $erro_campo = null;
    public $pagina_retorno = null;
   // cria variaveis do arquivo
    public $l30_codigo = 0;
    public $l30_data_dia = null;
    public $l30_data_mes = null;
    public $l30_data_ano = null;
    public $l30_data = null;
    public $l30_portaria = null;
    public $l30_datavalid_dia = null;
    public $l30_datavalid_mes = null;
    public $l30_datavalid_ano = null;
    public $l30_datavalid = null;
    public $l30_tipo = 0;
    public $l30_nomearquivo = null;
    public $l30_arquivo = null;
   // cria propriedade com as variaveis do arquivo
    public $campos = "
                 l30_codigo = int4 = Código Sequencial
                 l30_data = date = Data
                 l30_portaria = varchar(20) = Portaria
                 l30_datavalid = date = Validade
                 l30_tipo = int4 = Tipo
                 l30_nomearquivo = text = Nome do Arquivo
                 l30_arquivo = oid = Arquivo
                 ";
   //funcao construtor da classe
    public function __construct()
    {
      //classes dos rotulos dos campos
        $this->rotulo = new rotulo("liccomissao");
        $this->pagina_retorno =  basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"]);
    }
   //funcao erro
    public function erro($mostra, $retorna)
    {
        if (($this->erro_status == "0") || ($mostra == true && $this->erro_status != null )) {
            echo "<script>alert(\"".$this->erro_msg."\");</script>";
            if ($retorna) {
                echo "<script>location.href='".$this->pagina_retorno."'</script>";
            }
        }
    }
   // funcao para atualizar campos
    public function atualizacampos($exclusao = false)
    {
        if (!$exclusao) {
            $this->l30_codigo = ($this->l30_codigo == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_codigo"]:$this->l30_codigo);
            if ($this->l30_data == "") {
                $this->l30_data_dia = ($this->l30_data_dia == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_data_dia"]:$this->l30_data_dia);
                $this->l30_data_mes = ($this->l30_data_mes == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_data_mes"]:$this->l30_data_mes);
                $this->l30_data_ano = ($this->l30_data_ano == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_data_ano"]:$this->l30_data_ano);
                if ($this->l30_data_dia != "") {
                     $this->l30_data = $this->l30_data_ano."-".$this->l30_data_mes."-".$this->l30_data_dia;
                }
            }
            $this->l30_portaria = ($this->l30_portaria == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_portaria"]:$this->l30_portaria);
            if ($this->l30_datavalid == "") {
                $this->l30_datavalid_dia = ($this->l30_datavalid_dia == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_datavalid_dia"]:$this->l30_datavalid_dia);
                $this->l30_datavalid_mes = ($this->l30_datavalid_mes == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_datavalid_mes"]:$this->l30_datavalid_mes);
                $this->l30_datavalid_ano = ($this->l30_datavalid_ano == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_datavalid_ano"]:$this->l30_datavalid_ano);
                if ($this->l30_datavalid_dia != "") {
                    $this->l30_datavalid = $this->l30_datavalid_ano."-".$this->l30_datavalid_mes."-".$this->l30_datavalid_dia;
                }
            }
            $this->l30_tipo = ($this->l30_tipo == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_tipo"]:$this->l30_tipo);
            $this->l30_nomearquivo = ($this->l30_nomearquivo == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_nomearquivo"]:$this->l30_nomearquivo);
        } else {
            $this->l30_codigo = ($this->l30_codigo == ""?@$GLOBALS["HTTP_POST_VARS"]["l30_codigo"]:$this->l30_codigo);
        }
    }
   // funcao para Inclusão
    public function incluir($l30_codigo)
    {
        $this->atualizacampos();
        if ($this->l30_data == null) {
            $this->erro_sql = " Campo Data não informado.";
            $this->erro_campo = "l30_data_dia";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->l30_portaria == null) {
            $this->erro_sql = " Campo Portaria não informado.";
            $this->erro_campo = "l30_portaria";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->l30_datavalid == null) {
            $this->erro_sql = " Campo Validade não informado.";
            $this->erro_campo = "l30_datavalid_dia";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->l30_tipo == null) {
            $this->erro_sql = " Campo Tipo não informado.";
            $this->erro_campo = "l30_tipo";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($l30_codigo == "" || $l30_codigo == null) {
            $result = db_query("select nextval('liccomissao_l30_codigo_seq')");
            if ($result==false) {
                $this->erro_banco = str_replace("\n", "", @pg_last_error());
                $this->erro_sql   = "Verifique o cadastro da sequencia: liccomissao_l30_codigo_seq do campo: l30_codigo";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
            $this->l30_codigo = pg_result($result, 0, 0);
        } else {
            $result = db_query("select last_value from liccomissao_l30_codigo_seq");
            if (($result != false) && (pg_result($result, 0, 0) < $l30_codigo)) {
                $this->erro_sql = " Campo l30_codigo maior que último número da sequencia.";
                $this->erro_banco = "Sequencia menor que este número.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            } else {
                $this->l30_codigo = $l30_codigo;
            }
        }
        if (($this->l30_codigo == null) || ($this->l30_codigo == "")) {
            $this->erro_sql = " Campo l30_codigo não declarado.";
            $this->erro_banco = "Chave Primaria zerada.";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        $sql = "insert into liccomissao(
                                       l30_codigo
                                      ,l30_data
                                      ,l30_portaria
                                      ,l30_datavalid
                                      ,l30_tipo
                                      ,l30_nomearquivo
                                      ,l30_arquivo
                       )
                values (
                                $this->l30_codigo
                               ,".($this->l30_data == "null" || $this->l30_data == ""?"null":"'".$this->l30_data."'")."
                               ,'$this->l30_portaria'
                               ,".($this->l30_datavalid == "null" || $this->l30_datavalid == ""?"null":"'".$this->l30_datavalid."'")."
                               ,$this->l30_tipo
                               ,'$this->l30_nomearquivo'
                               ,".($this->l30_arquivo == "null" || $this->l30_arquivo == ""?"null":"'".$this->l30_arquivo."'")."
                      )";
        $result = db_query($sql);
        if (!$result) {
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            if (strpos(strtolower($this->erro_banco), "duplicate key") != 0) {
                $this->erro_sql   = "Comissão da Licitação ($this->l30_codigo) não Incluído. Inclusão Abortada.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_banco = "Comissão da Licitação já Cadastrado";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            } else {
                $this->erro_sql   = "Comissão da Licitação ($this->l30_codigo) não Incluído. Inclusão Abortada.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            }
            $this->erro_status = "0";
            $this->numrows_incluir= 0;
            return false;
        }
        $this->erro_banco = "";
        $this->erro_sql = "Inclusão efetuada com Sucesso\\n";
        $this->erro_sql .= "Valores : ".$this->l30_codigo;
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "1";
        $this->numrows_incluir= pg_affected_rows($result);
        return true;
    }
   // funcao para alteracao
    public function alterar($l30_codigo = null)
    {
        $this->atualizacampos();
        $sql = " update liccomissao set ";
        $virgula = "";
        if (trim($this->l30_data)!="" || isset($GLOBALS["HTTP_POST_VARS"]["l30_data_dia"]) &&  ($GLOBALS["HTTP_POST_VARS"]["l30_data_dia"] !="")) {
            $sql  .= $virgula." l30_data = '$this->l30_data' ";
            $virgula = ",";
            if (trim($this->l30_data) == null) {
                $this->erro_sql = " Campo Data não informado.";
                $this->erro_campo = "l30_data_dia";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        } else {
            if (isset($GLOBALS["HTTP_POST_VARS"]["l30_data_dia"])) {
                $sql  .= $virgula." l30_data = null ";
                $virgula = ",";
                if (trim($this->l30_data) == null) {
                    $this->erro_sql = " Campo Data não informado.";
                    $this->erro_campo = "l30_data_dia";
                    $this->erro_banco = "";
                    $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                    $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                    $this->erro_status = "0";
                    return false;
                }
            }
        }
        if (trim($this->l30_portaria)!="" || isset($GLOBALS["HTTP_POST_VARS"]["l30_portaria"])) {
            $sql  .= $virgula." l30_portaria = '$this->l30_portaria' ";
            $virgula = ",";
            if (trim($this->l30_portaria) == null) {
                $this->erro_sql = " Campo Portaria não informado.";
                $this->erro_campo = "l30_portaria";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->l30_datavalid)!="" || isset($GLOBALS["HTTP_POST_VARS"]["l30_datavalid_dia"]) &&  ($GLOBALS["HTTP_POST_VARS"]["l30_datavalid_dia"] !="")) {
            $sql  .= $virgula." l30_datavalid = '$this->l30_datavalid' ";
            $virgula = ",";
            if (trim($this->l30_datavalid) == null) {
                $this->erro_sql = " Campo Validade não informado.";
                $this->erro_campo = "l30_datavalid_dia";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        } else {
            if (isset($GLOBALS["HTTP_POST_VARS"]["l30_datavalid_dia"])) {
                $sql  .= $virgula." l30_datavalid = null ";
                $virgula = ",";
                if (trim($this->l30_datavalid) == null) {
                    $this->erro_sql = " Campo Validade não informado.";
                    $this->erro_campo = "l30_datavalid_dia";
                    $this->erro_banco = "";
                    $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                    $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                    $this->erro_status = "0";
                    return false;
                }
            }
        }
        if (trim($this->l30_tipo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["l30_tipo"])) {
            $sql  .= $virgula." l30_tipo = $this->l30_tipo ";
            $virgula = ",";
            if (trim($this->l30_tipo) == null) {
                $this->erro_sql = " Campo Tipo não informado.";
                $this->erro_campo = "l30_tipo";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->l30_nomearquivo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["l30_nomearquivo"])) {
            $sql  .= $virgula." l30_nomearquivo = '$this->l30_nomearquivo' ";
            $virgula = ",";
        }
        if (trim($this->l30_arquivo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["l30_arquivo"])) {
            $sql  .= $virgula." l30_arquivo = $this->l30_arquivo ";
            $virgula = ",";
        }
        $sql .= " where ";
        if ($l30_codigo!=null) {
            $sql .= " l30_codigo = $this->l30_codigo";
        }
        $result = db_query($sql);
        if (!$result) {
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            $this->erro_sql   = "Comissão da Licitação não Alterado. Alteração Abortada.\\n";
            $this->erro_sql .= "Valores : ".$this->l30_codigo;
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            $this->numrows_alterar = 0;
            return false;
        } else {
            if (pg_affected_rows($result) == 0) {
                $this->erro_banco = "";
                $this->erro_sql = "Comissão da Licitação não foi Alterado. Alteração Executada.\\n";
                $this->erro_sql .= "Valores : ".$this->l30_codigo;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_alterar = 0;
                return true;
            } else {
                $this->erro_banco = "";
                $this->erro_sql = "Alteração efetuada com Sucesso\\n";
                $this->erro_sql .= "Valores : ".$this->l30_codigo;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_alterar = pg_affected_rows($result);
                return true;
            }
        }
    }
   // funcao para exclusao
    public function excluir($l30_codigo = null, $dbwhere = null)
    {

        $sql = " delete from liccomissao where ";
        $sql2 = "";
        if (empty($dbwhere)) {
            if (!empty($l30_codigo)) {
                if (!empty($sql2)) {
                    $sql2 .= " and ";
                }
                $sql2 .= " l30_codigo = $l30_codigo ";
            }
        } else {
            $sql2 = $dbwhere;
        }
        $result = db_query($sql.$sql2);
        if (!$result) {
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            $this->erro_sql   = "Comissão da Licitação não Excluído. Exclusão Abortada.\\n";
            $this->erro_sql .= "Valores : ".$l30_codigo;
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            $this->numrows_excluir = 0;
            return false;
        } else {
            if (pg_affected_rows($result) == 0) {
                $this->erro_banco = "";
                $this->erro_sql = "Comissão da Licitação não Encontrado. Exclusão não Efetuada.\\n";
                $this->erro_sql .= "Valores : ".$l30_codigo;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_excluir = 0;
                return true;
            } else {
                $this->erro_banco = "";
                $this->erro_sql = "Exclusão efetuada com Sucesso\\n";
                $this->erro_sql .= "Valores : ".$l30_codigo;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_excluir = pg_affected_rows($result);
                return true;
            }
        }
    }
   // funcao do recordset
    public function sql_record($sql)
    {
        $result = db_query($sql);
        if (!$result) {
            $this->numrows    = 0;
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            $this->erro_sql   = "Erro ao selecionar os registros.";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        $this->numrows = pg_num_rows($result);
        if ($this->numrows == 0) {
            $this->erro_banco = "";
            $this->erro_sql   = "Record Vazio na Tabela:liccomissao";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        return $result;
    }
   // funcao do sql
    public function sql_query($l30_codigo = null, $campos = "*", $ordem = null, $dbwhere = "")
    {

        $sql  = "select {$campos}";
        $sql .= "  from liccomissao ";
        $sql2 = "";
        if (empty($dbwhere)) {
            if ($l30_codigo != '' && !is_null($l30_codigo)) {
                $sql2 .= " where liccomissao.l30_codigo = $l30_codigo ";
            }
        } elseif (!empty($dbwhere)) {
            $sql2 = " where $dbwhere";
        }
        $sql .= $sql2;
        if (!empty($ordem)) {
            $sql .= " order by {$ordem}";
        }
        return $sql;
    }
   // funcao do sql
    public function sql_query_file($l30_codigo = null, $campos = "*", $ordem = null, $dbwhere = "")
    {

        $sql  = "select {$campos} ";
        $sql .= "  from liccomissao ";
        $sql2 = "";
        if (empty($dbwhere)) {
            if ($l30_codigo != '' && !is_null($l30_codigo)) {
                $sql2 .= " where liccomissao.l30_codigo = $l30_codigo ";
            }
        } elseif (!empty($dbwhere)) {
            $sql2 = " where $dbwhere";
        }
        $sql .= $sql2;
        if (!empty($ordem)) {
            $sql .= " order by {$ordem}";
        }
        return $sql;
    }
}
