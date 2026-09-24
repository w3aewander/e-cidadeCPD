<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (c) 2014  DBSeller Servicos de Informatica
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

class cl_rhconsignadomovimentoservidorrubrica
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
    public $rh153_sequencial = 0; 
    public $rh153_consignadomovimentoservidor = 0; 
    public $rh153_rubrica = null; 
    public $rh153_instit = 0; 
    public $rh153_valordescontar = null; 
    public $rh153_valordescontado = null; 
    public $rh153_parcela = null; 
    public $rh153_totalparcelas = null; 
    public $rh153_econsignado = null; 
   // cria propriedade com as variaveis do arquivo 
    public $campos = "
                 rh153_sequencial = int4 =  
                 rh153_consignadomovimentoservidor = int4 = Movimento do Servidor 
                 rh153_rubrica = varchar(4) = Rubrica 
                 rh153_instit = int4 = Instituição 
                 rh153_valordescontar = varchar(10) = Valor da Parcela 
                 rh153_valordescontado = varchar(10) = Valor Descontado 
                 rh153_parcela = varchar(3) = Número da Parcela 
                 rh153_totalparcelas = varchar(3) = Total de Parcelas 
                 rh153_econsignado = varchar(15) = Número eConsignado 
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("rhconsignadomovimentoservidorrubrica"); 
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
       $this->rh153_sequencial = ($this->rh153_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_sequencial"]:$this->rh153_sequencial);
       $this->rh153_consignadomovimentoservidor = ($this->rh153_consignadomovimentoservidor == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_consignadomovimentoservidor"]:$this->rh153_consignadomovimentoservidor);
       $this->rh153_rubrica = ($this->rh153_rubrica == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_rubrica"]:$this->rh153_rubrica);
       $this->rh153_instit = ($this->rh153_instit == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_instit"]:$this->rh153_instit);
       $this->rh153_valordescontar = ($this->rh153_valordescontar == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_valordescontar"]:$this->rh153_valordescontar);
       $this->rh153_valordescontado = ($this->rh153_valordescontado == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_valordescontado"]:$this->rh153_valordescontado);
       $this->rh153_parcela = ($this->rh153_parcela == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_parcela"]:$this->rh153_parcela);
       $this->rh153_totalparcelas = ($this->rh153_totalparcelas == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_totalparcelas"]:$this->rh153_totalparcelas);
       $this->rh153_econsignado = ($this->rh153_econsignado == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_econsignado"]:$this->rh153_econsignado);
     }else{
       $this->rh153_sequencial = ($this->rh153_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["rh153_sequencial"]:$this->rh153_sequencial);
     }
   }

    public function incluir($rh153_sequencial)
    {
      $this->atualizacampos();
     if($this->rh153_consignadomovimentoservidor == null ){ 
       $this->erro_sql = " Campo Movimento do Servidor não informado.";
       $this->erro_campo = "rh153_consignadomovimentoservidor";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->rh153_instit == null ){ 
       $this->erro_sql = " Campo Instituição não informado.";
       $this->erro_campo = "rh153_instit";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->rh153_rubrica == null ){ 
       $this->rh153_rubrica = "null";
     }
     if($this->rh153_valordescontar == null ){ 
       $this->rh153_valordescontar = "null";
     }
     if($this->rh153_valordescontado == null ){ 
       $this->rh153_valordescontado = "null";
     }
     if($this->rh153_parcela == null ){ 
       $this->rh153_parcela = "null";
     }
     if($this->rh153_totalparcelas == null ){ 
       $this->rh153_totalparcelas = "null";
     }
     if($rh153_sequencial == "" || $rh153_sequencial == null ){
       $result = db_query("select nextval('rhconsignadomovimentoservidorrubrica_rh153_sequencial_seq')"); 
       if($result==false){
         $this->erro_banco = str_replace("\n","",@pg_last_error());
         $this->erro_sql   = "Verifique o cadastro da sequencia: rhconsignadomovimentoservidorrubrica_rh153_sequencial_seq do campo: rh153_sequencial"; 
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false; 
       }
       $this->rh153_sequencial = pg_result($result,0,0); 
     }else{
       $result = db_query("select last_value from rhconsignadomovimentoservidorrubrica_rh153_sequencial_seq");
       if(($result != false) && (pg_result($result,0,0) < $rh153_sequencial)){
         $this->erro_sql = " Campo rh153_sequencial maior que último número da sequencia.";
         $this->erro_banco = "Sequencia menor que este número.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }else{
         $this->rh153_sequencial = $rh153_sequencial; 
       }
     }
     if(($this->rh153_sequencial == null) || ($this->rh153_sequencial == "") ){ 
       $this->erro_sql = " Campo rh153_sequencial não declarado.";
       $this->erro_banco = "Chave Primaria zerada.";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }


     $sql = "insert into rhconsignadomovimentoservidorrubrica(
                                       rh153_sequencial 
                                      ,rh153_consignadomovimentoservidor 
                                      ,rh153_rubrica 
                                      ,rh153_instit 
                                      ,rh153_valordescontar 
                                      ,rh153_valordescontado 
                                      ,rh153_parcela 
                                      ,rh153_totalparcelas 
                                      ,rh153_econsignado 
                       )
                values (
                                $this->rh153_sequencial 
                               ,$this->rh153_consignadomovimentoservidor 
                               ,'$this->rh153_rubrica' 
                               ,$this->rh153_instit
                               ,'$this->rh153_valordescontar' 
                               ,'$this->rh153_valordescontado' 
                               ,'$this->rh153_parcela' 
                               ,'$this->rh153_totalparcelas' 
                               ,'$this->rh153_econsignado' 
                      )";
     $result = db_query($sql); 
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "rhconsignadomovimentoservidorrubrica ($this->rh153_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "rhconsignadomovimentoservidorrubrica já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "rhconsignadomovimentoservidorrubrica ($this->rh153_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       $this->numrows_incluir= 0;
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->rh153_sequencial;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";
     $this->numrows_incluir= pg_affected_rows($result);
     return true;
   } 

    public function alterar($rh153_sequencial=null)
    {
      $this->atualizacampos();
     $sql = " update rhconsignadomovimentoservidorrubrica set ";
     $virgula = "";
     if(trim($this->rh153_sequencial)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_sequencial"])){ 
       $sql  .= $virgula." rh153_sequencial = $this->rh153_sequencial ";
       $virgula = ",";
       if(trim($this->rh153_sequencial) == null ){ 
         $this->erro_sql = " Campo  não informado.";
         $this->erro_campo = "rh153_sequencial";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->rh153_consignadomovimentoservidor)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_consignadomovimentoservidor"])){ 
       $sql  .= $virgula." rh153_consignadomovimentoservidor = $this->rh153_consignadomovimentoservidor ";
       $virgula = ",";
       if(trim($this->rh153_consignadomovimentoservidor) == null ){ 
         $this->erro_sql = " Campo Movimento do Servidor não informado.";
         $this->erro_campo = "rh153_consignadomovimentoservidor";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->rh153_instit)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_instit"])){ 
       $sql  .= $virgula." rh153_instit = $this->rh153_instit ";
       $virgula = ",";
       if(trim($this->rh153_instit) == null ){ 
         $this->erro_sql = " Campo Instituição não informado.";
         $this->erro_campo = "rh153_instit";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->rh153_rubrica)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_rubrica"])){ 
        if(trim($this->rh153_rubrica)=="" && isset($GLOBALS["HTTP_POST_VARS"]["rh153_rubrica"])){ 
           $this->rh153_rubrica = "0" ; 
        } 
       $sql  .= $virgula." rh153_rubrica = '$this->rh153_rubrica' ";
       $virgula = ",";
     }
     if(trim($this->rh153_valordescontar)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_valordescontar"])){ 
       $sql  .= $virgula." rh153_valordescontar = '$this->rh153_valordescontar' ";
       $virgula = ",";
     }
     if(trim($this->rh153_valordescontado)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_valordescontado"])){ 
       $sql  .= $virgula." rh153_valordescontado = '$this->rh153_valordescontado' ";
       $virgula = ",";
     }
     if(trim($this->rh153_parcela)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_parcela"])){ 
       $sql  .= $virgula." rh153_parcela = '$this->rh153_parcela' ";
       $virgula = ",";
     }
     if(trim($this->rh153_totalparcelas)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_totalparcelas"])){ 
       $sql  .= $virgula." rh153_totalparcelas = '$this->rh153_totalparcelas' ";
       $virgula = ",";
     }
     if(trim($this->rh153_econsignado)!="" || isset($GLOBALS["HTTP_POST_VARS"]["rh153_econsignado"])){ 
       $sql  .= $virgula." rh153_econsignado = '$this->rh153_econsignado' ";
       $virgula = ",";
     }
     $sql .= " where ";
     if($rh153_sequencial!=null){
       $sql .= " rh153_sequencial = $this->rh153_sequencial";
     }
     $result = db_query($sql);
     if (!$result) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "rhconsignadomovimentoservidorrubrica não Alterado. Alteração Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->rh153_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_alterar = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "rhconsignadomovimentoservidorrubrica não foi Alterado. Alteração Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->rh153_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->rh153_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

    public function excluir($rh153_sequencial=null, $dbwhere = null)
    {
     $sql = " delete from rhconsignadomovimentoservidorrubrica
                    where ";
     $sql2 = "";
     if (empty($dbwhere)) {
        if (!empty($rh153_sequencial)){
          if (!empty($sql2)) {
            $sql2 .= " and ";
          }
          $sql2 .= " rh153_sequencial = $rh153_sequencial ";
        }
     } else {
       $sql2 = $dbwhere;
     }
     $result = db_query($sql.$sql2);
     if ($result == false) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "rhconsignadomovimentoservidorrubrica não Excluído. Exclusão Abortada.\\n";
       $this->erro_sql .= "Valores : ".$rh153_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_excluir = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "rhconsignadomovimentoservidorrubrica não Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql .= "Valores : ".$rh153_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_excluir = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$rh153_sequencial;
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
        $this->erro_sql   = "Record Vazio na Tabela:rhconsignadomovimentoservidorrubrica";
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }
     return $result;
   }

   public function sql_query($rh153_sequencial = null,$campos = "*", $ordem = null, $dbwhere = "") { 

     $sql  = "select {$campos}";
     $sql .= "  from rhconsignadomovimentoservidorrubrica ";
     $sql .= "      inner join rhconsignadomovimentoservidorrubrica  on  rhconsignadomovimentoservidorrubrica.rh153_sequencial = rhconsignadomovimentoservidorrubrica.rh153_consignadomovimentoservidor";
     $sql .= "      inner join rhconsignadomovimentoservidorrubrica  on  rhconsignadomovimentoservidorrubrica.rh153_sequencial = rhconsignadomovimentoservidorrubrica.rh153_consignadomovimentoservidor";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($rh153_sequencial)) {
         $sql2 .= " where rhconsignadomovimentoservidorrubrica.rh153_sequencial = $rh153_sequencial "; 
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

   public function sql_query_file($rh153_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos} ";
     $sql .= "  from rhconsignadomovimentoservidorrubrica ";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($rh153_sequencial)){
         $sql2 .= " where rhconsignadomovimentoservidorrubrica.rh153_sequencial = $rh153_sequencial "; 
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
