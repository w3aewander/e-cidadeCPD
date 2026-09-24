<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2016  DBselller Servicos de Informatica             
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

class cl_pensaosf{ 
   // cria variaveis de erro 
   var $rotulo          = null; 
   var $query_sql       = null; 
   var $numrows         = 0; 
   var $numrows_incluir = 0; 
   var $numrows_alterar = 0; 
   var $numrows_excluir = 0; 
   var $erro_status     = null; 
   var $erro_sql        = null; 
   var $erro_banco      = null;  
   var $erro_msg        = null;  
   var $erro_campo      = null;  
   var $pagina_retorno  = null; 
   // cria variaveis do arquivo 

  var $pl36_sequencial  = null;
  var $pl36_pensao      = 0;
  var $pl36_valor       = 0;



   //funcao construtor da classe 
   function cl_pensaosf() { 
     //classes dos rotulos dos campos
     $this->rotulo = new rotulo("pensaosf"); 
     $this->pagina_retorno =  basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"]);
   }
   //funcao erro 
   function erro($mostra,$retorna) { 
     if(($this->erro_status == "0") || ($mostra == true && $this->erro_status != null )){
        echo "<script>alert(\"".$this->erro_msg."\");</script>";
        if($retorna==true){
           echo "<script>location.href='".$this->pagina_retorno."'</script>";
        }
     }
   }

   // funcao para inclusao
   function incluir (){ 

     if($this->pl36_pensao == 0 ){ 
       $this->erro_sql    = "Pensão não informada.";
       $this->erro_campo  = "pl36_pensao";
       $this->erro_banco  = "";
       $this->erro_msg    = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }

     if($this->pl36_valor == null || $this->pl36_valor == ""){ 
        $this->pl36_valor = "0";
     }


     $sql = "insert into plugins.pensaosf(
                            pl36_valor
                          , pl36_pensao
                       )
                values (
                            $this->pl36_valor
                          , $this->pl36_pensao
                      )";

     $result = db_query($sql);                                       

     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = $sql."pensaosf - Erro ao incluir no banco (duplicate key). Inclusao Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "Erro!!!!";
         $this->erro_msg  .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = $sql."pensaosf - Erro ao incluir no banco. Inclusao Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg  .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status     = "0";
       $this->numrows_incluir = 0;
       return false;
     }
     $this->erro_banco      = "";
     $this->erro_sql        = "Inclusao efetuada com Sucesso\\n";
     $this->erro_msg        = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg       .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status     = "1";
     $this->numrows_incluir = pg_affected_rows($result);
     return true;
   } 

   // funcao para alteracao
   function alterar () { 

    if($this->pl36_pensao == "" ){ 
       $this->erro_sql    = "Pensão não informada.";
       $this->erro_campo  = "pl36_pensao";
       $this->erro_banco  = "";
       $this->erro_msg    = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
    
    if($this->pl36_valor == null || $this->pl36_valor == ""){ 
        $this->pl36_valor = "0";
     }

     $sql = " update plugins.pensaosf set 
                     pl36_valor     =  $this->pl36_valor
              where  pl36_pensao    =  $this->pl36_pensao ";   

     $result = db_query($sql);

     if($result==false ){ 
       $this->erro_banco      = str_replace("\n","",@pg_last_error());
       $this->erro_sql        = "pensaosf nao Alterado. Alteracao Abortada.\\n";
       $this->erro_sql       .= "Valores : ".$this->pl36_sequencial;
       $this->erro_msg        = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg       .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status     = "0";
       $this->numrows_alterar = 0;
       return false;
     }else{
       if(pg_affected_rows($result)==0){
         $this->erro_banco      = "";
         $this->erro_sql        = "pensaosf nao foi Alterado. Alteracao Abortada.\\n";
         $this->erro_sql       .= "Valores : ".$this->pl36_sequencial;
         $this->erro_msg        = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg       .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status     = "1";
         $this->numrows_alterar = 0;
         return true;
       }else{
         $this->erro_banco      = "";
         $this->erro_sql        = "Alteração efetuada com Sucesso\\n";
         $this->erro_sql       .= "Valores : ".$this->pl36_sequencial;
         $this->erro_msg        = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg       .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status     = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 
   
   // funcao para exclusao 
   function excluir () { 
      if($this->pl36_pensao != null){     
       $sql = " delete from plugins.pensaosf 
                        where pl36_pensao = $this->pl36_pensao";

      } 
      $result = db_query($sql);
     if($result==false){ 
       $this->erro_banco      = str_replace("\n","",@pg_last_error());
       $this->erro_sql        = "pensaosf nao Excluído. Exclusão Abortada.\\n";
       $this->erro_sql       .= "Valores : ".$this->pl36_sequencial;
       $this->erro_msg        = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg       .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status     = "0";
       $this->numrows_excluir = 0;
       return false;
     }else{
       if(pg_affected_rows($result)==0){
         $this->erro_banco      = "";
         $this->erro_sql        = "pensaosf nao Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql       .= "Valores : ".$this->pl36_sequencial;
         $this->erro_msg        = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg       .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status     = "1";
         $this->numrows_excluir = 0;
         return true;
       }else{
         $this->erro_banco      = "";
         $this->erro_sql        = "Exclusão efetuada com Sucesso\\n";
         $this->erro_sql       .= "Valores : ".$this->pl36_sequencial;
         $this->erro_msg        = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg       .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status     = "1";
         $this->numrows_excluir = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

}  


?>