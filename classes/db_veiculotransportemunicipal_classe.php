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

class cl_veiculotransportemunicipal
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
    public $tre01_sequencial = 0; 
    public $tre01_tipotransportemunicipal = null; 
    public $tre01_identificacao = null; 
    public $tre01_numeropassageiros = 0; 
    public $tre01_modalidadeveiculo = 0; 
    public $tre01_tipoveiculo = 0; 
    public $tre01_acessibilidade = 'f'; 
    public $tre01_anoveiculo = 0; 
   // cria propriedade com as variaveis do arquivo 
    public $campos = "
                 tre01_sequencial = int4 = Sequencial 
                 tre01_tipotransportemunicipal = int4 = Sequencial 
                 tre01_identificacao = varchar(25) = Identificação 
                 tre01_numeropassageiros = int4 = Número de Passageiros 
                 tre01_modalidadeveiculo = int4 = Modalidade Veículo 
                 tre01_tipoveiculo = int4 = Tipo Veículo 
                 tre01_acessibilidade = bool = Acessibilidade 
                 tre01_anoveiculo = int4 = Ano Veículo 
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("veiculotransportemunicipal"); 
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
       $this->tre01_sequencial = ($this->tre01_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["tre01_sequencial"]:$this->tre01_sequencial);
       $this->tre01_tipotransportemunicipal = ($this->tre01_tipotransportemunicipal == ""?@$GLOBALS["HTTP_POST_VARS"]["tre01_tipotransportemunicipal"]:$this->tre01_tipotransportemunicipal);
       $this->tre01_identificacao = ($this->tre01_identificacao == ""?@$GLOBALS["HTTP_POST_VARS"]["tre01_identificacao"]:$this->tre01_identificacao);
       $this->tre01_numeropassageiros = ($this->tre01_numeropassageiros == ""?@$GLOBALS["HTTP_POST_VARS"]["tre01_numeropassageiros"]:$this->tre01_numeropassageiros);
       $this->tre01_modalidadeveiculo = ($this->tre01_modalidadeveiculo == ""?@$GLOBALS["HTTP_POST_VARS"]["tre01_modalidadeveiculo"]:$this->tre01_modalidadeveiculo);
       $this->tre01_tipoveiculo = ($this->tre01_tipoveiculo == ""?@$GLOBALS["HTTP_POST_VARS"]["tre01_tipoveiculo"]:$this->tre01_tipoveiculo);
       $this->tre01_acessibilidade = ($this->tre01_acessibilidade == "f"?@$GLOBALS["HTTP_POST_VARS"]["tre01_acessibilidade"]:$this->tre01_acessibilidade);
       $this->tre01_anoveiculo = ($this->tre01_anoveiculo == ""?@$GLOBALS["HTTP_POST_VARS"]["tre01_anoveiculo"]:$this->tre01_anoveiculo);
     }else{
       $this->tre01_sequencial = ($this->tre01_sequencial == ""?@$GLOBALS["HTTP_POST_VARS"]["tre01_sequencial"]:$this->tre01_sequencial);
     }
   }

    public function incluir($tre01_sequencial)
    {
     $this->atualizacampos();

     if($this->tre01_identificacao == null ){ 
       $this->erro_sql = " Campo Identificação não informado.";
       $this->erro_campo = "tre01_identificacao";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->tre01_tipotransportemunicipal == null ){ 
       $this->tre01_tipotransportemunicipal = "null";
     }
     if($this->tre01_numeropassageiros == null ){ 
       $this->tre01_numeropassageiros = "0";
     }
     if($this->tre01_acessibilidade == null ){ 
      $this->tre01_acessibilidade = "f";
     }     

     if($tre01_sequencial == "" || $tre01_sequencial == null ){
        $result = db_query("select nextval('veiculotransportemunicipal_tre01_sequencial_seq')");
        if($result==false){
          $this->erro_banco = str_replace("\n","",@pg_last_error());
          $this->erro_sql   = "Verifique o cadastro da sequencia: veiculotransportemunicipal_tre01_sequencial_seq do campo: tre01_sequencial";
          $this->erro_msg   = "Usurio: \\n\\n ".$this->erro_sql." \\n\\n";
          $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
          $this->erro_status = "0";
          return false;
        }
        $this->tre01_sequencial = pg_result($result,0,0);
      }else{
        $result = db_query("select last_value from veiculotransportemunicipal_tre01_sequencial_seq");
        if(($result != false) && (pg_result($result,0,0) < $tre01_sequencial)){
          $this->erro_sql = " Campo tre01_sequencial maior que ltimo nmero da sequencia.";
          $this->erro_banco = "Sequencia menor que este nmero.";
          $this->erro_msg   = "Usurio: \\n\\n ".$this->erro_sql." \\n\\n";
          $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
          $this->erro_status = "0";
          return false;
        }else{
          $this->tre01_sequencial = $tre01_sequencial;
        }
     } 
     $sql = "insert into veiculotransportemunicipal(
                                       tre01_sequencial 
                                      ,tre01_tipotransportemunicipal 
                                      ,tre01_identificacao 
                                      ,tre01_numeropassageiros 
                                      ,tre01_modalidadeveiculo 
                                      ,tre01_tipoveiculo 
                                      ,tre01_acessibilidade 
                                      ,tre01_anoveiculo 
                       )
                values (
                                $this->tre01_sequencial 
                               ,$this->tre01_tipotransportemunicipal 
                               ,'$this->tre01_identificacao' 
                               ,$this->tre01_numeropassageiros 
                               ,$this->tre01_modalidadeveiculo 
                               ,$this->tre01_tipoveiculo 
                               ,'$this->tre01_acessibilidade' 
                               ,$this->tre01_anoveiculo 
                      )";                           
     $result = db_query($sql); 
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "Veículo transporte municipal ($this->tre01_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "Veículo transporte municipal já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "Veículo transporte municipal ($this->tre01_sequencial) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       $this->numrows_incluir= 0;
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->tre01_sequencial;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";
     $this->numrows_incluir= pg_affected_rows($result);
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       $resaco = $this->sql_record($this->sql_query_file($this->tre01_sequencial  ));
       if(($resaco!=false)||($this->numrows!=0)){

         $resac = db_query("select nextval('db_acount_id_acount_seq') as acount");
         $acount = pg_result($resac,0,0);
         $resac = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
         $resac = db_query("insert into db_acountkey values($acount,20072,'$this->tre01_sequencial','I')");
         $resac = db_query("insert into db_acount values($acount,3598,20072,'','".AddSlashes(pg_result($resaco,0,'tre01_sequencial'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3598,20073,'','".AddSlashes(pg_result($resaco,0,'tre01_tipotransportemunicipal'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3598,20074,'','".AddSlashes(pg_result($resaco,0,'tre01_identificacao'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3598,20075,'','".AddSlashes(pg_result($resaco,0,'tre01_numeropassageiros'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3598,60517334,'','".AddSlashes(pg_result($resaco,0,'tre01_modalidadeveiculo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3598,219383687,'','".AddSlashes(pg_result($resaco,0,'tre01_tipoveiculo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3598,68502722,'','".AddSlashes(pg_result($resaco,0,'tre01_acessibilidade'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,3598,248389592,'','".AddSlashes(pg_result($resaco,0,'tre01_anoveiculo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
       }
     }
     return true;
   } 

    public function alterar($tre01_sequencial=null)
    {
      $this->atualizacampos();
     $sql = " update veiculotransportemunicipal set ";
     $virgula = "";
     if(trim($this->tre01_sequencial)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre01_sequencial"])){ 
       $sql  .= $virgula." tre01_sequencial = $this->tre01_sequencial ";
       $virgula = ",";
       if(trim($this->tre01_sequencial) == null ){ 
         $this->erro_sql = " Campo Sequencial não informado.";
         $this->erro_campo = "tre01_sequencial";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre01_tipotransportemunicipal)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre01_tipotransportemunicipal"])){ 
        if(trim($this->tre01_tipotransportemunicipal)=="" && isset($GLOBALS["HTTP_POST_VARS"]["tre01_tipotransportemunicipal"])){ 
           $this->tre01_tipotransportemunicipal = "null" ; 
        } 
       $sql  .= $virgula." tre01_tipotransportemunicipal = $this->tre01_tipotransportemunicipal ";
       $virgula = ",";
     }
     if(trim($this->tre01_identificacao)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre01_identificacao"])){ 
       $sql  .= $virgula." tre01_identificacao = '$this->tre01_identificacao' ";
       $virgula = ",";
       if(trim($this->tre01_identificacao) == null ){ 
         $this->erro_sql = " Campo Identificação não informado.";
         $this->erro_campo = "tre01_identificacao";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->tre01_numeropassageiros)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre01_numeropassageiros"])){ 
        if(trim($this->tre01_numeropassageiros)=="" && isset($GLOBALS["HTTP_POST_VARS"]["tre01_numeropassageiros"])){ 
           $this->tre01_numeropassageiros = "0" ; 
        } 
       $sql  .= $virgula." tre01_numeropassageiros = $this->tre01_numeropassageiros ";
       $virgula = ",";
     }
     if(trim($this->tre01_modalidadeveiculo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre01_modalidadeveiculo"])){ 
        if(trim($this->tre01_modalidadeveiculo)=="" && isset($GLOBALS["HTTP_POST_VARS"]["tre01_modalidadeveiculo"])){ 
           $this->tre01_modalidadeveiculo = "0" ; 
        } 
       $sql  .= $virgula." tre01_modalidadeveiculo = $this->tre01_modalidadeveiculo ";
       $virgula = ",";
     }
     if(trim($this->tre01_tipoveiculo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre01_tipoveiculo"])){ 
        if(trim($this->tre01_tipoveiculo)=="" && isset($GLOBALS["HTTP_POST_VARS"]["tre01_tipoveiculo"])){ 
           $this->tre01_tipoveiculo = "0" ; 
        } 
       $sql  .= $virgula." tre01_tipoveiculo = $this->tre01_tipoveiculo ";
       $virgula = ",";
     }
     if(trim($this->tre01_acessibilidade)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre01_acessibilidade"])){ 
       $sql  .= $virgula." tre01_acessibilidade = '$this->tre01_acessibilidade' ";
       $virgula = ",";
     }
     if(trim($this->tre01_anoveiculo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["tre01_anoveiculo"])){ 
        if(trim($this->tre01_anoveiculo)=="" && isset($GLOBALS["HTTP_POST_VARS"]["tre01_anoveiculo"])){ 
           $this->tre01_anoveiculo = "0" ; 
        } 
       $sql  .= $virgula." tre01_anoveiculo = $this->tre01_anoveiculo ";
       $virgula = ",";
     }
     $sql .= " where ";
     if($tre01_sequencial!=null){
       $sql .= " tre01_sequencial = $this->tre01_sequencial";
     }
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       $resaco = $this->sql_record($this->sql_query_file($this->tre01_sequencial));
       if ($this->numrows > 0) {

         for ($conresaco = 0; $conresaco < $this->numrows; $conresaco++) {

           $resac = db_query("select nextval('db_acount_id_acount_seq') as acount");
           $acount = pg_result($resac,0,0);
           $resac = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
           $resac = db_query("insert into db_acountkey values($acount,20072,'$this->tre01_sequencial','A')");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre01_sequencial"]) || $this->tre01_sequencial != "")
             $resac = db_query("insert into db_acount values($acount,3598,20072,'".AddSlashes(pg_result($resaco,$conresaco,'tre01_sequencial'))."','$this->tre01_sequencial',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre01_tipotransportemunicipal"]) || $this->tre01_tipotransportemunicipal != "")
             $resac = db_query("insert into db_acount values($acount,3598,20073,'".AddSlashes(pg_result($resaco,$conresaco,'tre01_tipotransportemunicipal'))."','$this->tre01_tipotransportemunicipal',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre01_identificacao"]) || $this->tre01_identificacao != "")
             $resac = db_query("insert into db_acount values($acount,3598,20074,'".AddSlashes(pg_result($resaco,$conresaco,'tre01_identificacao'))."','$this->tre01_identificacao',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre01_numeropassageiros"]) || $this->tre01_numeropassageiros != "")
             $resac = db_query("insert into db_acount values($acount,3598,20075,'".AddSlashes(pg_result($resaco,$conresaco,'tre01_numeropassageiros'))."','$this->tre01_numeropassageiros',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre01_modalidadeveiculo"]) || $this->tre01_modalidadeveiculo != "")
             $resac = db_query("insert into db_acount values($acount,3598,60517334,'".AddSlashes(pg_result($resaco,$conresaco,'tre01_modalidadeveiculo'))."','$this->tre01_modalidadeveiculo',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre01_tipoveiculo"]) || $this->tre01_tipoveiculo != "")
             $resac = db_query("insert into db_acount values($acount,3598,219383687,'".AddSlashes(pg_result($resaco,$conresaco,'tre01_tipoveiculo'))."','$this->tre01_tipoveiculo',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre01_acessibilidade"]) || $this->tre01_acessibilidade != "")
             $resac = db_query("insert into db_acount values($acount,3598,68502722,'".AddSlashes(pg_result($resaco,$conresaco,'tre01_acessibilidade'))."','$this->tre01_acessibilidade',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["tre01_anoveiculo"]) || $this->tre01_anoveiculo != "")
             $resac = db_query("insert into db_acount values($acount,3598,248389592,'".AddSlashes(pg_result($resaco,$conresaco,'tre01_anoveiculo'))."','$this->tre01_anoveiculo',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         }
       }
     }
     $result = db_query($sql);
     if (!$result) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Veículo transporte municipal não Alterado. Alteração Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->tre01_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_alterar = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Veículo transporte municipal não foi Alterado. Alteração Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->tre01_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->tre01_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

    public function excluir($tre01_sequencial=null, $dbwhere = null)
    {
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       if (empty($dbwhere)) {

         $resaco = $this->sql_record($this->sql_query_file($tre01_sequencial));
       } else { 
         $resaco = $this->sql_record($this->sql_query_file(null,"*",null,$dbwhere));
       }
       if (($resaco != false) || ($this->numrows!=0)) {

         for ($iresaco = 0; $iresaco < $this->numrows; $iresaco++) {

           $resac  = db_query("select nextval('db_acount_id_acount_seq') as acount");
           $acount = pg_result($resac,0,0);
           $resac  = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
           $resac  = db_query("insert into db_acountkey values($acount,20072,'$tre01_sequencial','E')");
           $resac  = db_query("insert into db_acount values($acount,3598,20072,'','".AddSlashes(pg_result($resaco,$iresaco,'tre01_sequencial'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3598,20073,'','".AddSlashes(pg_result($resaco,$iresaco,'tre01_tipotransportemunicipal'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3598,20074,'','".AddSlashes(pg_result($resaco,$iresaco,'tre01_identificacao'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3598,20075,'','".AddSlashes(pg_result($resaco,$iresaco,'tre01_numeropassageiros'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3598,60517334,'','".AddSlashes(pg_result($resaco,$iresaco,'tre01_modalidadeveiculo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3598,219383687,'','".AddSlashes(pg_result($resaco,$iresaco,'tre01_tipoveiculo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3598,68502722,'','".AddSlashes(pg_result($resaco,$iresaco,'tre01_acessibilidade'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,3598,248389592,'','".AddSlashes(pg_result($resaco,$iresaco,'tre01_anoveiculo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         }
       }
     }
     $sql = " delete from veiculotransportemunicipal
                    where ";
     $sql2 = "";
     if (empty($dbwhere)) {
        if (!empty($tre01_sequencial)){
          if (!empty($sql2)) {
            $sql2 .= " and ";
          }
          $sql2 .= " tre01_sequencial = $tre01_sequencial ";
        }
     } else {
       $sql2 = $dbwhere;
     }
     $result = db_query($sql.$sql2);
     if ($result == false) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Veículo transporte municipal não Excluído. Exclusão Abortada.\\n";
       $this->erro_sql .= "Valores : ".$tre01_sequencial;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_excluir = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Veículo transporte municipal não Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql .= "Valores : ".$tre01_sequencial;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_excluir = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$tre01_sequencial;
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
        $this->erro_sql   = "Record Vazio na Tabela:veiculotransportemunicipal";
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }
     return $result;
   }

    public function sql_query($tre01_sequencial = null,$campos = "*", $ordem = null, $dbwhere = "") { 

     $sql  = "select {$campos}";
     $sql .= "  from veiculotransportemunicipal ";
     $sql .= "      left  join tipotransportemunicipal  on  tipotransportemunicipal.tre00_sequencial = veiculotransportemunicipal.tre01_tipotransportemunicipal";
     $sql .= "      left  join tipoveiculo  on  tipoveiculo.tre17_sequencial = veiculotransportemunicipal.tre01_tipoveiculo";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($tre01_sequencial)) {
         $sql2 .= " where veiculotransportemunicipal.tre01_sequencial = $tre01_sequencial "; 
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

    public function sql_query_file($tre01_sequencial = null, $campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos} ";
     $sql .= "  from veiculotransportemunicipal ";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($tre01_sequencial)){
         $sql2 .= " where veiculotransportemunicipal.tre01_sequencial = $tre01_sequencial "; 
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

   function sql_query_veiculostransporte ( $tre01_sequencial=null,$campos="*",$ordem=null,$dbwhere=""){
    
    $sql = "select ";
    if($campos != "*" ){
      $campos_sql = split("#",$campos);
      $virgula = "";
      for($i=0;$i<sizeof($campos_sql);$i++){
        $sql .= $virgula.$campos_sql[$i];
        $virgula = ",";
      }
    }else{
      $sql .= $campos;
    }
    $sql .= " from veiculotransportemunicipal ";
    $sql .= "     left  join veiculotransportemunicipalterceiro on veiculotransportemunicipal.tre01_sequencial              = veiculotransportemunicipalterceiro.tre03_veiculotransportemunicipal";
    $sql .= "     left  join veiculotransportemunicipalveiculos on veiculotransportemunicipal.tre01_sequencial              = veiculotransportemunicipalveiculos.tre02_veiculotransportemunicipal";
    $sql .= "     left  join veiculos                           on veiculos.ve01_codigo                                     = veiculotransportemunicipalveiculos.tre02_veiculos                  ";
    $sql .= "     left  join veiccadmodelo                      on veiculos.ve01_veiccadmodelo                              = veiccadmodelo.ve22_codigo                                          ";
    $sql .= "     left  join tipotransportemunicipal            on veiculotransportemunicipal.tre01_tipotransportemunicipal = tipotransportemunicipal.tre00_sequencial                           ";
    $sql .= "     left  join cgm                                on cgm.z01_numcgm                                           = veiculotransportemunicipalterceiro.tre03_cgm                       ";
    $sql2 = "";
    if($dbwhere==""){
      if($tre01_sequencial!=null ){
        $sql2 .= " where veiculotransportemunicipal.tre01_sequencial = $tre01_sequencial ";
      }
    }else if($dbwhere != ""){
      $sql2 = " where $dbwhere";
    }
    $sql .= $sql2;
    if($ordem != null ){
      $sql .= " order by ";
      $campos_sql = split("#",$ordem);
      $virgula = "";
      for($i=0;$i<sizeof($campos_sql);$i++){
        $sql .= $virgula.$campos_sql[$i];
        $virgula = ",";
      }
    }
    return $sql;
}
   function sql_query_vinculo_veiculo ( $tre01_sequencial=null,$campos="*",$ordem=null,$dbwhere=""){

    $sql = "select ";
    if($campos != "*" ){
      $campos_sql = split("#",$campos);
      $virgula = "";
      for($i=0;$i<sizeof($campos_sql);$i++){
        $sql .= $virgula.$campos_sql[$i];
        $virgula = ",";
      }
    }else{
      $sql .= $campos;
    }
    $sql .= " from veiculotransportemunicipal ";
    $sql .= "     left join veiculotransportemunicipalterceiro on veiculotransportemunicipal.tre01_sequencial = veiculotransportemunicipalterceiro.tre03_veiculotransportemunicipal";
    $sql .= "     left join veiculotransportemunicipalveiculos on veiculotransportemunicipal.tre01_sequencial = veiculotransportemunicipalveiculos.tre02_veiculotransportemunicipal";
    $sql2 = "";
    if ($dbwhere==""){
      if($tre01_sequencial!=null ){
        $sql2 .= " where veiculotransportemunicipal.tre01_sequencial = $tre01_sequencial ";
      }
    } else if ($dbwhere != "") {
      $sql2 = " where $dbwhere";
    }
    $sql .= $sql2;
    if ($ordem != null ) {

      $sql       .= " order by ";
      $campos_sql = split("#",$ordem);
      $virgula    = "";
      for ($i=0;$i<sizeof($campos_sql);$i++) {

        $sql     .= $virgula.$campos_sql[$i];
        $virgula  = ",";
      }
    }
    return $sql;
  }
}
