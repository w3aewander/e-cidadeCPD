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

class cl_movrel
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
    public $r54_sequencial = 0;
    public $r54_anomes = null;
    public $r54_codrel = null;
    public $r54_regist = 0;
    public $r54_codeve = null;
    public $r54_quant1 = 0;
    public $r54_quant2 = 0;
    public $r54_quant3 = 0;
    public $r54_lancad = 'f';
    public $r54_instit = 0;
   // cria propriedade com as variaveis do arquivo
    public $campos = "
                 r54_sequencial = int4 = Sequencial 
                 r54_anomes = varchar(6) = Ano/Mes 
                 r54_codrel = varchar(4) = Código Convênio 
                 r54_regist = int4 = Matricula Servidor 
                 r54_codeve = varchar(4) = Código Relacionamento 
                 r54_quant1 = numeric = Qtd. Rubrica 1 
                 r54_quant2 = numeric = Qtd. Rubrica 2 
                 r54_quant3 = numeric = Qtd. Rubrica 3 
                 r54_lancad = bool = Lançamento efetivado 
                 r54_instit = int4 = Instituição 
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("movrel");
        $this->pagina_retorno = basename($_SERVER['PHP_SELF']);
    }

    public function erro($mostra, $retorna)
    {
        if (($this->erro_status == "0") || ($mostra == true && $this->erro_status != null )) {
            echo "<script>alert(\"".$this->erro_msg."\")</script>";
            if ($retorna==true) {
                echo "<script>location.href='".$this->pagina_retorno."'</script>";
            }
        }
    }

    public function atualizacampos($exclusao = false)
    {
        if ($exclusao==false) {
            $this->r54_sequencial = ($this->r54_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_sequencial"]:$this->r54_sequencial);
            $this->r54_anomes = ($this->r54_anomes == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_anomes"]:$this->r54_anomes);
            $this->r54_codrel = ($this->r54_codrel == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_codrel"]:$this->r54_codrel);
            $this->r54_regist = ($this->r54_regist == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_regist"]:$this->r54_regist);
            $this->r54_codeve = ($this->r54_codeve == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_codeve"]:$this->r54_codeve);
            $this->r54_quant1 = ($this->r54_quant1 == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_quant1"]:$this->r54_quant1);
            $this->r54_quant2 = ($this->r54_quant2 == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_quant2"]:$this->r54_quant2);
            $this->r54_quant3 = ($this->r54_quant3 == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_quant3"]:$this->r54_quant3);
            $this->r54_lancad = ($this->r54_lancad == "f"?@$GLOBALS["HTTP_POST_VARS"]["r54_lancad"]:$this->r54_lancad);
            $this->r54_instit = ($this->r54_instit == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_instit"]:$this->r54_instit);
        } else {
            $this->r54_sequencial = ($this->r54_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["r54_sequencial"]:$this->r54_sequencial);
        }
    }

    public function incluir($r54_sequencial)
    {
        $this->atualizacampos();
        if ($this->r54_anomes == null) {
            $this->erro_sql = " Campo Ano/Mes não informado.";
            $this->erro_campo = "r54_anomes";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r54_codrel == null) {
            $this->erro_sql = " Campo Código Convênio não informado.";
            $this->erro_campo = "r54_codrel";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r54_regist == null) {
            $this->erro_sql = " Campo Matricula Servidor não informado.";
            $this->erro_campo = "r54_regist";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r54_codeve == null) {
            $this->erro_sql = " Campo Código Relacionamento não informado.";
            $this->erro_campo = "r54_codeve";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r54_quant1 == null) {
            $this->erro_sql = " Campo Qtd. Rubrica 1 não informado.";
            $this->erro_campo = "r54_quant1";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r54_quant2 == null) {
            $this->erro_sql = " Campo Qtd. Rubrica 2 não informado.";
            $this->erro_campo = "r54_quant2";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r54_quant3 == null) {
            $this->erro_sql = " Campo Qtd. Rubrica 3 não informado.";
            $this->erro_campo = "r54_quant3";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($this->r54_lancad == null) {
            $this->r54_lancad = "false";
        }
        if ($this->r54_instit == null) {
            $this->erro_sql = " Campo Instituição não informado.";
            $this->erro_campo = "r54_instit";
            $this->erro_banco = "";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        if ($r54_sequencial == "" || $r54_sequencial == null) {
            $result = db_query("select nextval('movrel_2_r54_sequencial_seq')");
            if ($result==false) {
                $this->erro_banco = str_replace("\n", "", @pg_last_error());
                $this->erro_sql   = "Verifique o cadastro da sequencia: movrel_2_r54_sequencial_seq do campo: r54_sequencial";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
            $this->r54_sequencial = pg_fetch_result($result, 0, 0);
        } else {
            $result = db_query("select last_value from movrel_2_r54_sequencial_seq");
            if (($result != false) && (pg_fetch_result($result, 0, 0) < $r54_sequencial)) {
                $this->erro_sql = " Campo r54_sequencial maior que último número da sequencia.";
                $this->erro_banco = "Sequencia menor que este número.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            } else {
                $this->r54_sequencial = $r54_sequencial;
            }
        }
        if (($this->r54_sequencial == null) || ($this->r54_sequencial == "")) {
            $this->erro_sql = " Campo r54_sequencial não declarado.";
            $this->erro_banco = "Chave Primaria zerada.";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        $sql = "insert into movrel(
                                       r54_sequencial 
                                      ,r54_anomes 
                                      ,r54_codrel 
                                      ,r54_regist 
                                      ,r54_codeve 
                                      ,r54_quant1 
                                      ,r54_quant2 
                                      ,r54_quant3 
                                      ,r54_lancad 
                                      ,r54_instit 
                       )
                values (
                                $this->r54_sequencial 
                               ,'$this->r54_anomes' 
                               ,'$this->r54_codrel' 
                               ,$this->r54_regist 
                               ,'$this->r54_codeve' 
                               ,$this->r54_quant1 
                               ,$this->r54_quant2 
                               ,$this->r54_quant3 
                               ,'$this->r54_lancad' 
                               ,$this->r54_instit 
                      )";
        $result = db_query($sql);
        if ($result==false) {
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            if (strpos(strtolower($this->erro_banco), "duplicate key") != 0) {
                $this->erro_sql   = "Importacao arquivo Convenio/Efetividade ($this->r54_sequencial) não Incluído. Inclusão Abortada.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_banco = "Importacao arquivo Convenio/Efetividade já Cadastrado";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            } else {
                $this->erro_sql   = "Importacao arquivo Convenio/Efetividade ($this->r54_sequencial) não Incluído. Inclusão Abortada.";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            }
            $this->erro_status = "0";
            $this->numrows_incluir= 0;
            return false;
        }
        $this->erro_banco = "";
        $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->r54_sequencial;
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "1";
        $this->numrows_incluir= pg_affected_rows($result);

        return true;
    }

    public function alterar($r54_sequencial = null)
    {
        $this->atualizacampos();
        $sql = " update movrel set ";
        $virgula = "";
        if (trim($this->r54_sequencial)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_sequencial"])) {
            $sql  .= $virgula." r54_sequencial = $this->r54_sequencial ";
            $virgula = ",";
            if (trim($this->r54_sequencial) == null) {
                $this->erro_sql = " Campo Sequencial não informado.";
                $this->erro_campo = "r54_sequencial";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->r54_anomes)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_anomes"])) {
            $sql  .= $virgula." r54_anomes = '$this->r54_anomes' ";
            $virgula = ",";
            if (trim($this->r54_anomes) == null) {
                $this->erro_sql = " Campo Ano/Mes não informado.";
                $this->erro_campo = "r54_anomes";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->r54_codrel)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_codrel"])) {
            $sql  .= $virgula." r54_codrel = '$this->r54_codrel' ";
            $virgula = ",";
            if (trim($this->r54_codrel) == null) {
                $this->erro_sql = " Campo Código Convênio não informado.";
                $this->erro_campo = "r54_codrel";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->r54_regist)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_regist"])) {
            $sql  .= $virgula." r54_regist = $this->r54_regist ";
            $virgula = ",";
            if (trim($this->r54_regist) == null) {
                $this->erro_sql = " Campo Matricula Servidor não informado.";
                $this->erro_campo = "r54_regist";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->r54_codeve)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_codeve"])) {
            $sql  .= $virgula." r54_codeve = '$this->r54_codeve' ";
            $virgula = ",";
            if (trim($this->r54_codeve) == null) {
                $this->erro_sql = " Campo Código Relacionamento não informado.";
                $this->erro_campo = "r54_codeve";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->r54_quant1)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_quant1"])) {
            $sql  .= $virgula." r54_quant1 = $this->r54_quant1 ";
            $virgula = ",";
            if (trim($this->r54_quant1) == null) {
                $this->erro_sql = " Campo Qtd. Rubrica 1 não informado.";
                $this->erro_campo = "r54_quant1";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->r54_quant2)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_quant2"])) {
            $sql  .= $virgula." r54_quant2 = $this->r54_quant2 ";
            $virgula = ",";
            if (trim($this->r54_quant2) == null) {
                $this->erro_sql = " Campo Qtd. Rubrica 2 não informado.";
                $this->erro_campo = "r54_quant2";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->r54_quant3)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_quant3"])) {
            $sql  .= $virgula." r54_quant3 = $this->r54_quant3 ";
            $virgula = ",";
            if (trim($this->r54_quant3) == null) {
                $this->erro_sql = " Campo Qtd. Rubrica 3 não informado.";
                $this->erro_campo = "r54_quant3";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        if (trim($this->r54_lancad)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_lancad"])) {
            $sql  .= $virgula." r54_lancad = '$this->r54_lancad' ";
            $virgula = ",";
        }
        if (trim($this->r54_instit)!="" || isset($GLOBALS["HTTP_POST_VARS"]["r54_instit"])) {
            $sql  .= $virgula." r54_instit = $this->r54_instit ";
            $virgula = ",";
            if (trim($this->r54_instit) == null) {
                $this->erro_sql = " Campo Instituição não informado.";
                $this->erro_campo = "r54_instit";
                $this->erro_banco = "";
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "0";
                return false;
            }
        }
        $sql .= " where ";

        if ($r54_sequencial!=null) {
            $sql .= " r54_sequencial = $this->r54_sequencial";
        }

        $result = db_query($sql);
        if (!$result) {
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            $this->erro_sql   = "Importacao arquivo Convenio/Efetividade não Alterado. Alteração Abortada.\\n";
            $this->erro_sql .= "Valores : ".$this->r54_sequencial;
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            $this->numrows_alterar = 0;
            return false;
        } else {
            if (pg_affected_rows($result) == 0) {
                $this->erro_banco = "";
                $this->erro_sql = "Importacao arquivo Convenio/Efetividade não foi Alterado. Alteração Executada.\\n";
                $this->erro_sql .= "Valores : ".$this->r54_sequencial;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_alterar = 0;
                return true;
            } else {
                $this->erro_banco = "";
                $this->erro_sql = "Alteração efetuada com sucesso.\\n";
                $this->erro_sql .= "Valores : ".$this->r54_sequencial;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_alterar = pg_affected_rows($result);
                return true;
            }
        }
    }

    public function excluir($r54_sequencial = null, $dbwhere = null)
    {
        $sql = " delete from movrel
                    where ";
        $sql2 = "";
        if (empty($dbwhere)) {
            if (!empty($r54_sequencial)) {
                if (!empty($sql2)) {
                     $sql2 .= " and ";
                }
                $sql2 .= " r54_sequencial = $r54_sequencial ";
            }
        } else {
            $sql2 = $dbwhere;
        }

        $result = db_query($sql.$sql2);
        if ($result == false) {
            $this->erro_banco = str_replace("\n", "", @pg_last_error());
            $this->erro_sql   = "Importacao arquivo Convenio/Efetividade não Excluído. Exclusão Abortada.\\n";
            $this->erro_sql .= "Valores : ".$r54_sequencial;
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            $this->numrows_excluir = 0;
            return false;
        } else {
            if (pg_affected_rows($result) == 0) {
                $this->erro_banco = "";
                $this->erro_sql = "Importacao arquivo Convenio/Efetividade não Encontrado. Exclusão não Efetuada.\\n";
                $this->erro_sql .= "Valores : ".$r54_sequencial;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
                $this->erro_status = "1";
                $this->numrows_excluir = 0;
                return true;
            } else {
                $this->erro_banco = "";
                $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
                $this->erro_sql .= "Valores : ".$r54_sequencial;
                $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
                $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
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
            $this->erro_sql   = "Record Vazio na Tabela:movrel";
            $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
            $this->erro_msg   .=  str_replace('"', "", str_replace("'", "", "Administrador: \\n\\n ".$this->erro_banco." \\n"));
            $this->erro_status = "0";
            return false;
        }
        return $result;
    }

    public function sql_query($r54_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "")
    {

        $sql  = "select {$campos}";
        $sql .= "  from movrel ";
        $sql .= "       inner join db_config  on  db_config.codigo = movrel.r54_instit";
        $sql .= "       inner join convenio  on  convenio.r56_codrel = movrel.r54_codrel";
        $sql .= "       inner join relac  on  relac.r55_codeve = movrel.r54_codeve";
        $sql .= "       inner join cgm  on  cgm.z01_numcgm = db_config.numcgm";
        
        $sql2 = "";
        if (empty($dbwhere)) {
            if (!empty($r54_sequencial)) {
                $sql2 .= " where movrel.r54_sequencial = $r54_sequencial ";
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

    public function sql_query_file($r54_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "")
    {

        $sql  = "select {$campos} ";
        $sql .= "  from movrel ";
        $sql2 = "";
        if (empty($dbwhere)) {
            if (!empty($r54_sequencial)) {
                $sql2 .= " where movrel.r54_sequencial = $r54_sequencial ";
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

    public function sql_query_dados(
        $sequencial = null,
        $campos = "*",
        $ordem = null,
        $dbwhere = "",
        $ano = "",
        $mes = ""
    ) {
        $sql = "select ";
        $sql .= $campos;
        if ($ano == "") {
            $ano = DBPessoal::getAnoFolha();
        }
        if ($mes == "") {
            $mes = DBPessoal::getMesFolha();
        }
        $instituicao = db_getsession('DB_instit');

        $sql .= " from movrel ";
        $sql .= "      inner join convenio  on convenio.r56_codrel   = movrel.r54_codrel
                                           and convenio.r56_instit   = {$instituicao}";
        $sql .= "      inner join relac     on relac.r55_codeve      = movrel.r54_codeve 
                                           and relac.r55_instit      = {$instituicao}";
        $sql .= "      left join rhpessoalmov on rhpessoalmov.rh02_regist = movrel.r54_regist
                                             and rhpessoalmov.rh02_anousu = {$ano}
                                             and rhpessoalmov.rh02_mesusu = {$mes} 
                                             and rhpessoalmov.rh02_instit = {$instituicao}";
        $sql .= "      left join rhpessoal on rhpessoal.rh01_regist = rh02_regist ";
        $sql .= "      left join cgm       on cgm.z01_numcgm        = rhpessoal.rh01_numcgm";
        $sql .= "      left join rhpesrescisao on rhpesrescisao.rh05_seqpes = rhpessoalmov.rh02_seqpes ";

        $sql2 = "";
        if ($sequencial != "" && $sequencial != null) {
            $sql2 = " where movrel.r54_sequencial = '$sequencial'";
        }

        if (!empty($dbwhere)) {
            if (!empty($sql2)) {
                $sql2 = " and $dbwhere";
            } else {
                $sql2 = " where $dbwhere";
            }
        }
        $sql .= $sql2;

        if ($ordem != null) {
            $sql .= " order by {$ordem}";
        }

        return $sql;
    }

    public function sql_query_gerfsal(
        $sequencial = null,
        $campos = "*",
        $ordem = null,
        $dbwhere = "",
        $ano = "",
        $mes = "",
        $rubric = ""
    ) {
        $sql = "select ";
        $sql .= $campos;
        
        if ($ano == "") {
            $ano = DBPessoal::getAnoFolha();
        }
        if ($mes == "") {
            $mes = DBPessoal::getMesFolha();
        }
        
        $sql .= " from movrel ";
        $sql .= "      left join gerfsal on gerfsal.r14_anousu = {$ano}
                                        and gerfsal.r14_mesusu = {$mes}
                                        and gerfsal.r14_instit = movrel.r54_instit
                                        and gerfsal.r14_rubric = '{$rubric}'
                                        and gerfsal.r14_regist = movrel.r54_regist";

        $sql2 = "";
        if ($sequencial != "" && $sequencial != null) {
            $sql2 = " where movrel.r54_sequencial = '$sequencial'";
        }

        if (!empty($dbwhere)) {
            if (!empty($sql2)) {
                $sql2 = " and $dbwhere";
            } else {
                $sql2 = " where $dbwhere";
            }
        }
        $sql .= $sql2;

        if ($ordem != null) {
            $sql .= " order by {$ordem}";
        }
        return $sql;
    }
}
