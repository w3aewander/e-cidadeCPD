<?php

class cl_lab_triagemitem
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
    public $la74_sequencial = 0; 
    public $la74_usuario = 0; 
    public $la74_requiitem = 0; 
    public $created_at_dia = null; 
    public $created_at_mes = null; 
    public $created_at_ano = null; 
    public $created_at = null; 
    public $created_at_hora = '00:00'; 
    public $updated_at_dia = null; 
    public $updated_at_mes = null; 
    public $updated_at_ano = null; 
    public $updated_at = null; 
    public $updated_at_hora = '00:00'; 
   // cria propriedade com as variaveis do arquivo 
    public $campos = "
                 la74_sequencial = int4 = Sequencial 
                 la74_usuario = int4 = Usuário 
                 la74_requiitem = int4 = Item Requisição 
                 created_at = timestamp = Data Criação 
                 updated_at = timestamp = Data Atualização 
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("lab_triagemitem"); 
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
       $this->la74_sequencial = ($this->la74_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["la74_sequencial"]:$this->la74_sequencial);
       $this->la74_usuario = ($this->la74_usuario == ""?@$GLOBALS["HTTP_POST_VARS"]["la74_usuario"]:$this->la74_usuario);
       $this->la74_requiitem = ($this->la74_requiitem == ""?@$GLOBALS["HTTP_POST_VARS"]["la74_requiitem"]:$this->la74_requiitem);
       if($this->created_at == ""){
         $this->created_at_dia = ($this->created_at_dia == ""?@$GLOBALS["HTTP_POST_VARS"]["created_at_dia"]:$this->created_at_dia);
         $this->created_at_mes = ($this->created_at_mes == ""?@$GLOBALS["HTTP_POST_VARS"]["created_at_mes"]:$this->created_at_mes);
         $this->created_at_ano = ($this->created_at_ano == ""?@$GLOBALS["HTTP_POST_VARS"]["created_at_ano"]:$this->created_at_ano);
         $this->created_at_hora = ($this->created_at_hora == ""?@$GLOBALS["HTTP_POST_VARS"]["created_at_hora"]:$this->created_at_hora);
         if($this->created_at_dia != ""){
   	   $this->created_at = "date '".$this->created_at_ano."-".$this->created_at_mes."-".$this->created_at_dia."',time '".$this->created_at_hora."'";
         }
       }
       if($this->updated_at == ""){
         $this->updated_at_dia = ($this->updated_at_dia == ""?@$GLOBALS["HTTP_POST_VARS"]["updated_at_dia"]:$this->updated_at_dia);
         $this->updated_at_mes = ($this->updated_at_mes == ""?@$GLOBALS["HTTP_POST_VARS"]["updated_at_mes"]:$this->updated_at_mes);
         $this->updated_at_ano = ($this->updated_at_ano == ""?@$GLOBALS["HTTP_POST_VARS"]["updated_at_ano"]:$this->updated_at_ano);
         $this->updated_at_hora = ($this->updated_at_hora == ""?@$GLOBALS["HTTP_POST_VARS"]["updated_at_hora"]:$this->updated_at_hora);
         if($this->updated_at_dia != ""){
   	   $this->updated_at = "date '".$this->updated_at_ano."-".$this->updated_at_mes."-".$this->updated_at_dia."',time '".$this->updated_at_hora."'";
         }
       }
     }else{
       $this->la74_sequencial = ($this->la74_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["la74_sequencial"]:$this->la74_sequencial);
     }
   }

    public function incluir($la74_sequencial)
    {
      $this->atualizacampos();
     if($this->la74_usuario == null ){ 
       $this->erro_sql = " Campo Usuário não informado.";
       $this->erro_campo = "la74_usuario";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->la74_requiitem == null ){ 
       $this->erro_sql = " Campo Item Requisição não informado.";
       $this->erro_campo = "la74_requiitem";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($la74_sequencial == "" || $la74_sequencial == null ){
       $result = db_query("select nextval('lab_triagemitem_la74_sequencial_seq')"); 
       if($result==false){
         $this->erro_banco = str_replace("\n","",@pg_last_error());
         $this->erro_sql   = "Verifique o cadastro da sequencia: lab_triagemitem_la74_sequencial_seq do campo: la74_sequencial"; 
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false; 
       }
       $this->la74_sequencial = pg_result($result,0,0); 
     }else{
       $result = db_query("select last_value from lab_triagemitem_la74_sequencial_seq");
       if(($result != false) && (pg_result($result,0,0) < $la74_sequencial)){
         $this->erro_sql = " Campo la74_sequencial maior que último número da sequencia.";
         $this->erro_banco = "Sequencia menor que este número.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }else{
         $this->la74_sequencial = $la74_sequencial; 
       }
     }
     if(($this->la74_sequencial == null) || ($this->la74_sequencial == "") ){ 
       $this->erro_sql = " Campo la74_sequencial não declarado.";
       $this->erro_banco = "Chave Primaria zerada.";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     $sql = "insert into lab_triagemitem(
                                       la74_sequencial 
                                      ,la74_usuario 
                                      ,la74_requiitem 
                                      ,created_at 
                                      ,updated_at 
                       )
                values (
                                $this->la74_sequencial 
                               ,$this->la74_usuario 
                               ,$this->la74_requiitem 
                               ,".($this->created_at == "null" || $this->created_at == ""?"null":"timestamp(".$this->created_at.")")." 
                               ,".($this->updated_at == "null" || $this->updated_at == ""?"null":"timestamp(".$this->updated_at.")")." 
                      )";
     $result = db_query($sql); 
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "Item Triagem ($this->la74_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "Item Triagem já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "Item Triagem ($this->la74_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       $this->numrows_incluir= 0;
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->la74_sequencial;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";
     $this->numrows_incluir= pg_affected_rows($result);
     return true;
   } 

    public function alterar($la74_sequencial=null)
    {
      $this->atualizacampos();
     $sql = " update lab_triagemitem set ";
     $virgula = "";
     if(trim($this->la74_sequencial)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la74_sequencial"])){ 
       $sql  .= $virgula." la74_sequencial = $this->la74_sequencial ";
       $virgula = ",";
       if(trim($this->la74_sequencial) == null ){ 
         $this->erro_sql = " Campo Sequencial não informado.";
         $this->erro_campo = "la74_sequencial";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->la74_usuario)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la74_usuario"])){ 
       $sql  .= $virgula." la74_usuario = $this->la74_usuario ";
       $virgula = ",";
       if(trim($this->la74_usuario) == null ){ 
         $this->erro_sql = " Campo Usuário não informado.";
         $this->erro_campo = "la74_usuario";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->la74_requiitem)!="" || isset($GLOBALS["HTTP_POST_VARS"]["la74_requiitem"])){ 
       $sql  .= $virgula." la74_requiitem = $this->la74_requiitem ";
       $virgula = ",";
       if(trim($this->la74_requiitem) == null ){ 
         $this->erro_sql = " Campo Item Requisição não informado.";
         $this->erro_campo = "la74_requiitem";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->created_at)!="" || isset($GLOBALS["HTTP_POST_VARS"]["created_at_dia"]) &&  ($GLOBALS["HTTP_POST_VARS"]["created_at_dia"] !="") ){ 
       $sql  .= $virgula." created_at = '$this->created_at' ";
       $virgula = ",";
     }     else{ 
       if(isset($GLOBALS["HTTP_POST_VARS"]["created_at_dia"])){ 
         $sql  .= $virgula." created_at = null ";
         $virgula = ",";
       }
     }
     if(trim($this->updated_at)!="" || isset($GLOBALS["HTTP_POST_VARS"]["updated_at_dia"]) &&  ($GLOBALS["HTTP_POST_VARS"]["updated_at_dia"] !="") ){ 
       $sql  .= $virgula." updated_at = '$this->updated_at' ";
       $virgula = ",";
     }     else{ 
       if(isset($GLOBALS["HTTP_POST_VARS"]["updated_at_dia"])){ 
         $sql  .= $virgula." updated_at = null ";
         $virgula = ",";
       }
     }
     $sql .= " where ";
     if($la74_sequencial!=null){
       $sql .= " la74_sequencial = $this->la74_sequencial";
     }
     $result = db_query($sql);
     if (!$result) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Item Triagem não Alterado. Alteração Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->la74_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_alterar = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Item Triagem não foi Alterado. Alteração Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->la74_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->la74_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

    public function excluir($la74_sequencial=null, $dbwhere = null)
    {
     $sql = " delete from lab_triagemitem
                    where ";
     $sql2 = "";
     if (empty($dbwhere)) {
        if (!empty($la74_sequencial)){
          if (!empty($sql2)) {
            $sql2 .= " and ";
          }
          $sql2 .= " la74_sequencial = $la74_sequencial ";
        }
     } else {
       $sql2 = $dbwhere;
     }
     $result = db_query($sql.$sql2);
     if ($result == false) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Item Triagem não Excluído. Exclusão Abortada.\\n";
       $this->erro_sql .= "Valores : ".$la74_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_excluir = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Item Triagem não Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql .= "Valores : ".$la74_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_excluir = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$la74_sequencial;
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
        $this->erro_sql   = "Record Vazio na Tabela:lab_triagemitem";
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }
     return $result;
   }

    public function sql_query($la74_sequencial = null,$campos = "*", $ordem = null, $dbwhere = "") { 

     $sql  = "select {$campos}";
     $sql .= "  from lab_triagemitem ";
     $sql .= "      inner join db_usuarios  on  db_usuarios.id_usuario = lab_triagemitem.la74_usuario";
     $sql .= "      inner join lab_requiitem  on  lab_requiitem.la21_i_codigo = lab_triagemitem.la74_requiitem";
     $sql .= "      inner join lab_setorexame  on  lab_setorexame.la09_i_codigo = lab_requiitem.la21_i_setorexame";
     $sql .= "      inner join lab_requisicao  on  lab_requisicao.la22_i_codigo = lab_requiitem.la21_i_requisicao";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($la74_sequencial)) {
         $sql2 .= " where lab_triagemitem.la74_sequencial = $la74_sequencial "; 
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

    public function sql_query_file($la74_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos} ";
     $sql .= "  from lab_triagemitem ";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($la74_sequencial)){
         $sql2 .= " where lab_triagemitem.la74_sequencial = $la74_sequencial "; 
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
