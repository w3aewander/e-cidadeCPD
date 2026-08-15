<?php

//MODULO: issqn
//CLASSE DA ENTIDADE issbaseendereco
class cl_issbaseendereco { 
   // cria variaveis de erro 
   public $rotulo     = null; 
   public $query_sql  = null; 
   public $numrows    = 0; 
   public $erro_status= null; 
   public $erro_sql   = null; 
   public $erro_banco = null;  
   public $erro_msg   = null;  
   public $erro_campo = null;  
   public $pagina_retorno = null; 
   // cria variaveis do arquivo 
   public $q205_codigo = 0; 
   public $q205_inscr = 0; 
   public $q205_cep = null; 
   public $q205_bairro = 0; 
   public $q205_rua = 0; 
   public $q205_bairronome = null; 
   public $q205_ruanome = null; 
   public $q205_num = null; 
   public $q205_compl = null; 
   public $q205_dest = null; 
   public $q205_municipal = 'f'; 
   public $q205_atualizado = 0; 
   public $q205_usuario = 0; 
   // cria propriedade com as variaveis do arquivo 
   public $campos = "
                 q205_codigo = int4 = Codigo
                 q205_inscr = int4 = Inscricao 
                 q205_cep = varchar(8) = Cep 
                 q205_bairro = int4 = Bairro 
                 q205_rua = int4 = Rua 
                 q205_bairronome = varchar(30) = Bairro 
                 q205_ruanome = varchar(100) = Rua 
                 q205_num = varchar(10) = Numero 
                 q205_compl = varchar(100) = Complemento 
                 q205_dest = varchar(50) = Destinatario 
                 q205_municipal = bool =  Municipal
                 q205_atualizado = timestamp = Data e Hora 
                 q205_usuario = int4 = Usuario 
                 ";
   //funcao construtor da classe 
   public  function __construct() { 
     //classes dos rotulos dos campos
     $this->rotulo = new rotulo("issbaseendereco"); 
     $this->pagina_retorno =  basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"]);
   }
   //funcao erro 
   public  function erro($mostra,$retorna) { 
     if(($this->erro_status == "0") || ($mostra == true && $this->erro_status != null )){
        echo "<script>alert(\"".$this->erro_msg."\");</script>";
        if($retorna==true){
           echo "<script>location.href='".$this->pagina_retorno."'</script>";
        }
     }
   }
   // funcao para atualizar campos
   public function atualizacampos($exclusao=false) {
     if($exclusao==false){
       $this->q205_codigo = ($this->q205_codigo == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_codigo"]:$this->q205_codigo);
       $this->q205_inscr = ($this->q205_inscr == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_inscr"]:$this->q205_inscr);
       $this->q205_cep = ($this->q205_cep == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_cep"]:$this->q205_cep);
       $this->q205_bairro = ($this->q205_bairro == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_bairro"]:$this->q205_bairro);
       $this->q205_rua = ($this->q205_rua == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_rua"]:$this->q205_rua);
       $this->q205_bairronome = ($this->q205_bairronome == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_bairronome"]:$this->q205_bairronome);
       $this->q205_ruanome = ($this->q205_ruanome == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_ruanome"]:$this->q205_ruanome);
       $this->q205_num = ($this->q205_num == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_num"]:$this->q205_num);
       $this->q205_compl = ($this->q205_compl == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_compl"]:$this->q205_compl);
       $this->q205_dest = ($this->q205_dest == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_dest"]:$this->q205_dest);
       $this->q205_municipal = ($this->q205_municipal == "f"?@$GLOBALS["HTTP_POST_VARS"]["q205_municipal"]:$this->q205_municipal);
       $this->q205_atualizado = ($this->q205_atualizado == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_atualizado"]:$this->q205_atualizado);
       $this->q205_usuario = ($this->q205_usuario == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_usuario"]:$this->q205_usuario);
     }else{
       $this->q205_codigo = ($this->q205_codigo == ""?@$GLOBALS["HTTP_POST_VARS"]["q205_codigo"]:$this->q205_codigo);
     }
   }
   // funcao para inclusao
   public function incluir ($q205_codigo){ 
      $this->atualizacampos();
     if($this->q205_inscr == null ){ 
       $this->erro_sql = " Campo Inscricao nao Informado.";
       $this->erro_campo = "q205_inscr";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->q205_cep == null ){ 
       $this->erro_sql = " Campo Cep nao Informado.";
       $this->erro_campo = "q205_cep";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->q205_num == null ){ 
       $this->erro_sql = " Campo Numero nao Informado.";
       $this->erro_campo = "q205_num";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->q205_municipal == null ){ 
       $this->erro_sql = " Campo  nao Informado.";
       $this->erro_campo = "q205_municipal";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->q205_atualizado == null ){ 
       $this->q205_atualizado = "CURRENT_TIMESTAMP";
     }
     if($this->q205_usuario == null ){ 
      $this->erro_sql = " Campo Usuario nao Informado.";
      $this->erro_campo = "q205_usuario";
      $this->erro_banco = "";
      $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
      $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
      $this->erro_status = "0";
      return false;
    }
     if($q205_codigo == "" || $q205_codigo == null ){
       $result = db_query("select nextval('issbaseendereco_q205_codigo_seq')"); 
       if($result==false){
         $this->erro_banco = str_replace("\n","",@pg_last_error());
         $this->erro_sql   = "Verifique o cadastro da sequencia: issbaseendereco_q205_codigo_seq do campo: q205_codigo"; 
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false; 
       }
       $this->q205_codigo = pg_fetch_result($result,0,0); 
     }else{
       $result = db_query("select last_value from issbaseendereco_q205_codigo_seq");
       if(($result != false) && (pg_fetch_result($result,0,0) < $q205_codigo)){
         $this->erro_sql = " Campo q205_codigo maior que último número da sequencia.";
         $this->erro_banco = "Sequencia menor que este número.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }else{
         $this->q205_codigo = $q205_codigo; 
       }
     }
     if(($this->q205_codigo == null) || ($this->q205_codigo == "") ){ 
       $this->erro_sql = " Campo q205_codigo nao declarado.";
       $this->erro_banco = "Chave Primaria zerada.";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }

     $bairro = $this->q205_municipal == 1? 'q205_bairro' : 'q205_bairronome';
     $rua = $this->q205_municipal == 1? 'q205_rua' : 'q205_ruanome';  
     $bairroValue = $this->q205_municipal == 1? $this->q205_bairro : "'$this->q205_bairronome'";
     $ruaValue = $this->q205_municipal == 1? $this->q205_rua : "'$this->q205_ruanome'";

     $result = db_query("insert into issbaseendereco(
                                       q205_codigo 
                                      ,q205_inscr 
                                      ,q205_cep 
                                      ,$bairro
                                      ,$rua 
                                      ,q205_num 
                                      ,q205_compl 
                                      ,q205_dest 
                                      ,q205_municipal 
                                      ,q205_usuario 
                       )
                values (
                                $this->q205_codigo 
                               ,$this->q205_inscr 
                               ,'$this->q205_cep' 
                               ,$bairroValue
                               ,$ruaValue
                               ,'$this->q205_num' 
                               ,'$this->q205_compl' 
                               ,'$this->q205_dest' 
                               ,'$this->q205_municipal' 
                               ,$this->q205_usuario
                      )");

     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "issbaseendereco ($this->q205_codigo) nao Incluído. Inclusao Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "issbaseendereco já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "issbaseendereco ($this->q205_codigo) nao Incluído. Inclusao Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusao efetuada com Sucesso\\n";
         $this->erro_sql .= "Valores : ".$this->q205_codigo;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";

     return true;
   } 
   // funcao para alteracao
   public function alterar ($q205_codigo=null) { 
      $this->atualizacampos();
     $sql = " update issbaseendereco set ";
     $virgula = "";
     if(trim($this->q205_codigo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_codigo"])){ 
        if(trim($this->q205_codigo)=="" && isset($GLOBALS["HTTP_POST_VARS"]["q205_codigo"])){ 
           $this->q205_codigo = "0" ; 
        } 
       $sql  .= $virgula." q205_codigo = $this->q205_codigo ";
       $virgula = ",";
       if(trim($this->q205_codigo) == null ){ 
         $this->erro_sql = " Campo  nao Informado.";
         $this->erro_campo = "q205_codigo";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->q205_inscr)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_inscr"])){ 
        if(trim($this->q205_inscr)=="" && isset($GLOBALS["HTTP_POST_VARS"]["q205_inscr"])){ 
           $this->q205_inscr = "0" ; 
        } 
       $sql  .= $virgula." q205_inscr = $this->q205_inscr ";
       $virgula = ",";
       if(trim($this->q205_inscr) == null ){ 
         $this->erro_sql = " Campo Inscricao nao Informado.";
         $this->erro_campo = "q205_inscr";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->q205_cep)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_cep"])){ 
       $sql  .= $virgula." q205_cep = '$this->q205_cep' ";
       $virgula = ",";
       if(trim($this->q205_cep) == null ){ 
         $this->erro_sql = " Campo Cep nao Informado.";
         $this->erro_campo = "q205_cep";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->q205_bairro)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_bairro"])){ 
        if(trim($this->q205_bairro)=="" && isset($GLOBALS["HTTP_POST_VARS"]["q205_bairro"])){ 
           $this->q205_bairro = "0" ; 
        } 
       $sql  .= $virgula." q205_bairro = $this->q205_bairro ";
       $virgula = ",";
     } else {
      $sql  .= $virgula." q205_bairro = null ";
      $virgula = ",";
     }
     if(trim($this->q205_rua)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_rua"])){ 
        if(trim($this->q205_rua)=="" && isset($GLOBALS["HTTP_POST_VARS"]["q205_rua"])){ 
           $this->q205_rua = "0" ; 
        } 
       $sql  .= $virgula." q205_rua = $this->q205_rua ";
       $virgula = ",";
     } else {
      $sql  .= $virgula." q205_rua = null ";
      $virgula = ",";
     }
     if(trim($this->q205_bairronome)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_bairronome"])){ 
       $sql  .= $virgula." q205_bairronome = '$this->q205_bairronome' ";
       $virgula = ",";
     } else {
      $sql  .= $virgula." q205_bairronome = '' ";
      $virgula = ",";
     }
     if(trim($this->q205_ruanome)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_ruanome"])){ 
       $sql  .= $virgula." q205_ruanome = '$this->q205_ruanome' ";
       $virgula = ",";
     } else {
      $sql  .= $virgula." q205_ruanome = '' ";
      $virgula = ",";
     }
     if(trim($this->q205_num)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_num"])){ 
       $sql  .= $virgula." q205_num = '$this->q205_num' ";
       $virgula = ",";
       if(trim($this->q205_num) == null ){ 
         $this->erro_sql = " Campo Numero nao Informado.";
         $this->erro_campo = "q205_num";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->q205_compl)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_compl"])){ 
       $sql  .= $virgula." q205_compl = '$this->q205_compl' ";
       $virgula = ",";
     }
     if(trim($this->q205_dest)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_dest"])){ 
       $sql  .= $virgula." q205_dest = '$this->q205_dest' ";
       $virgula = ",";
     }
     if(trim($this->q205_municipal)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_municipal"])){ 
       $sql  .= $virgula." q205_municipal = '$this->q205_municipal' ";
       $virgula = ",";
       if(trim($this->q205_municipal) == null ){ 
         $this->erro_sql = " Campo  nao Informado.";
         $this->erro_campo = "q205_municipal";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->q205_atualizado)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_atualizado"])){ 
       $sql  .= $virgula." q205_atualizado = $this->q205_atualizado ";
       $virgula = ",";
     } else {
        $sql  .= $virgula." q205_atualizado = CURRENT_TIMESTAMP ";
        $virgula = ",";
     }
     if(trim($this->q205_usuario)!="" || isset($GLOBALS["HTTP_POST_VARS"]["q205_usuario"])){ 
        if(trim($this->q205_usuario)=="" && isset($GLOBALS["HTTP_POST_VARS"]["q205_usuario"])){ 
           $this->q205_usuario = "0" ; 
        } 
       $sql  .= $virgula." q205_usuario = $this->q205_usuario ";
       $virgula = ",";
       if(trim($this->q205_usuario) == null ){ 
         $this->erro_sql = " Campo Usuario nao Informado.";
         $this->erro_campo = "q205_usuario";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
      }
    }
     $sql .= " where  q205_codigo = $this->q205_codigo
";
     $result = db_query($sql);
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "issbaseendereco nao Alterado. Alteracao Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->q205_codigo;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }else{
       if(pg_affected_rows($result)==0){
         $this->erro_banco = "";
         $this->erro_sql = "issbaseendereco nao foi Alterado. Alteracao Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->q205_codigo;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         return true;
       }else{
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com Sucesso\\n";
         $this->erro_sql .= "Valores : ".$this->q205_codigo;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         return true;
       } 
     } 
   } 
   // funcao para exclusao 
   public function excluir ($q205_codigo=null) { 
     $this->atualizacampos(true);

     $sql = " delete from issbaseendereco
                    where ";
     $sql2 = "";
      if($this->q205_codigo != ""){
      if($sql2!=""){
        $sql2 .= " and ";
      }
      $sql2 .= " q205_codigo = $this->q205_codigo ";
}
     $result = db_query($sql.$sql2);
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "issbaseendereco nao Excluído. Exclusão Abortada.\\n";
       $this->erro_sql .= "Valores : ".$this->q205_codigo;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }else{
       if(pg_affected_rows($result)==0){
         $this->erro_banco = "";
         $this->erro_sql = "issbaseendereco nao Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql .= "Valores : ".$this->q205_codigo;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         return true;
       }else{
         $this->erro_banco = "";
         $this->erro_sql = "Exclusão efetuada com Sucesso\\n";
         $this->erro_sql .= "Valores : ".$this->q205_codigo;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         return true;
       } 
     } 
   } 
   // funcao do recordset 
   public function sql_record($sql) { 
     $result = db_query($sql);
     if($result==false){
       $this->numrows    = 0;
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Erro ao selecionar os registros.";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     $this->numrows = pg_num_rows($result);
      if($this->numrows==0){
        $this->erro_banco = "";
        $this->erro_sql   = "Dados do Grupo nao Encontrado";
        $this->erro_msg   = "Usuário: \n\n ".$this->erro_sql." \n\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }
     return $result;
   }
   // funcao do sql 
   public function sql_query ( $q205_codigo=null,$campos="*",$ordem=null,$dbwhere=""){ 
     $sql = "select ";
     if($campos != "*" ){
       $campos_sql = explode("#",$campos);
       $virgula = "";
       for($i=0;$i<sizeof($campos_sql);$i++){
         $sql .= $virgula.$campos_sql[$i];
         $virgula = ",";
       }
     }else{
       $sql .= $campos;
     }
     $sql .= " from issbaseendereco ";
     $sql .= "      inner join bairro  on  bairro.j13_codi = issbaseendereco.q205_bairro";
     $sql .= "      inner join ruas  on  ruas.j14_codigo = issbaseendereco.q205_rua";
     $sql .= "      inner join issbase  on  issbase.q02_inscr = issbaseendereco.q205_inscr";
     $sql .= "      inner join db_usuarios  on  db_usuarios.id_usuario = issbaseendereco.q205_usuario";
     $sql .= "      inner join cgm  on  cgm.z01_numcgm = issbase.q02_numcgm";
     $sql .= "      inner join formalocalvara  on  formalocalvara.q167_sequencial = issbase.q02_formalocalvara";
     $sql2 = "";
     if($dbwhere==""){
       if($q205_codigo!=null ){
         $sql2 .= " where issbaseendereco.q205_codigo = $q205_codigo "; 
       } 
     }else if($dbwhere != ""){
       $sql2 = " where $dbwhere";
     }
     $sql .= $sql2;
     if($ordem != null ){
       $sql .= " order by ";
       $campos_sql = explode("#",$ordem);
       $virgula = "";
       for($i=0;$i<sizeof($campos_sql);$i++){
         $sql .= $virgula.$campos_sql[$i];
         $virgula = ",";
       }
     }
     return $sql;
  }
   // funcao do sql 
   public function sql_query_file ( $q205_codigo=null,$campos="*",$ordem=null,$dbwhere=""){ 
     $sql = "select ";
     if($campos != "*" ){
       $campos_sql = explode("#",$campos);
       $virgula = "";
       for($i=0;$i<sizeof($campos_sql);$i++){
         $sql .= $virgula.$campos_sql[$i];
         $virgula = ",";
       }
     }else{
       $sql .= $campos;
     }
     $sql .= " from issbaseendereco ";
     $sql2 = "";
     if($dbwhere==""){
       if($q205_codigo!=null ){
         $sql2 .= " where issbaseendereco.q205_codigo = $q205_codigo "; 
       } 
     }else if($dbwhere != ""){
       $sql2 = " where $dbwhere";
     }
     $sql .= $sql2;
     if($ordem != null ){
       $sql .= " order by ";
       $campos_sql = explode("#",$ordem);
       $virgula = "";
       for($i=0;$i<sizeof($campos_sql);$i++){
         $sql .= $virgula.$campos_sql[$i];
         $virgula = ",";
       }
     }
     return $sql;
  }
}
