<?php
class cl_alunonecessidade
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
    public $ed214_i_codigo = 0; 
    public $ed214_i_aluno = 0; 
    public $ed214_i_necessidade = 0; 
    public $ed214_c_principal = null; 
    public $ed214_i_apoio = 0; 
    public $ed214_d_data_dia = null; 
    public $ed214_d_data_mes = null; 
    public $ed214_d_data_ano = null; 
    public $ed214_d_data = null; 
    public $ed214_i_tipo = 0; 
    public $ed214_i_escola = 0; 
    public $ed214_i_anexo_estorage = 0; 
    public $ed214_v_cid = null; 
    public $ed214_b_amdfono = 'f'; 
    public $ed214_b_amdto = 'f'; 
    public $ed214_b_amdpsi = 'f'; 
    public $ed214_b_amdpsiped = 'f'; 
    public $ed214_b_amdpsiq = 'f'; 
    public $ed214_b_amdneu = 'f'; 
    public $ed214_b_amdequ = 'f'; 
    public $ed214_b_amdmus = 'f'; 
    public $ed214_b_amdfis = 'f'; 
    public $ed214_v_amdoutqual = null; 
    public $ed214_b_medcon = 'f'; 
    public $ed214_v_medcon = null; 
    public $ed214_i_curriculo = 0; 
    public $ed214_i_amputado = 0; 
    public $ed214_v_ampuqual = null; 
    public $ed214_b_usocad = 'f'; 
    public $ed214_b_usoort = 'f'; 
    public $ed214_b_usopro = 'f'; 
    public $ed214_b_usoben = 'f'; 
    public $ed214_b_usomul = 'f'; 
    public $ed214_b_usoand = 'f'; 
   	public $ed214_b_avaliado = 'f'; 
	public $ed214_v_avaliado = null;
   // cria propriedade com as variaveis do arquivo 
    public $campos = "
                 ed214_i_codigo = int8 = Código 
                 ed214_i_aluno = int8 = Aluno 
                 ed214_i_necessidade = int8 = Necessidade 
                 ed214_c_principal = char(3) = Necessidade Maior 
                 ed214_i_apoio = int4 = Apoio Pedagógico 
                 ed214_d_data = date = Data 
                 ed214_i_tipo = int4 = Situacao 
                 ed214_i_escola = int8 = Última Alteração 
                 ed214_i_anexo_estorage = int8 = id anexo diagnóstico 
                 ed214_v_cid = varchar(12) = CID 
                 ed214_b_amdfono = bool = Fonoaudiologo 
                 ed214_b_amdto = bool = Terapia ocupacional 
                 ed214_b_amdpsi = bool = Psicólogo 
                 ed214_b_amdpsiped = bool = Psicopedagogo 
                 ed214_b_amdpsiq = bool = Pisiquiatra 
                 ed214_b_amdneu = bool = Neurologista 
                 ed214_b_amdequ = bool = Equoterapia 
                 ed214_b_amdmus = bool = Musicoterapia 
                 ed214_b_amdfis = bool = Fisioterapia 
                 ed214_v_amdoutqual = varchar(30) = Outros 
                 ed214_b_medcon = bool = Medicação controlada 
                 ed214_v_medcon = varchar(30) = Medicação qual 
                 ed214_i_curriculo = int4 = Tipo de currículo 
                 ed214_i_amputado = int4 = Possui membros amputado 
                 ed214_v_ampuqual = varchar(30) = Amputado qual 
                 ed214_b_usocad = bool = Cadeira de rodas 
                 ed214_b_usoort = bool = Órtese 
                 ed214_b_usopro = bool = Prótese 
                 ed214_b_usoben = bool = Bengala 
                 ed214_b_usomul = bool = Muleta 
                 ed214_b_usoand = bool = Andador 
				 ed214_b_avaliado = bool = Avaliado pela Seção de Educação Especial 
				 ed214_b_avaliado = varchar(60) = Avaliado pela Seção de Educação Especial 
                 ";

    public function __construct()
    {
        $this->rotulo = new rotulo("alunonecessidade"); 
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
       $this->ed214_i_codigo = ($this->ed214_i_codigo == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_codigo"]:$this->ed214_i_codigo);
       $this->ed214_i_aluno = ($this->ed214_i_aluno == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_aluno"]:$this->ed214_i_aluno);
       $this->ed214_i_necessidade = ($this->ed214_i_necessidade == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_necessidade"]:$this->ed214_i_necessidade);
       $this->ed214_c_principal = ($this->ed214_c_principal == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_c_principal"]:$this->ed214_c_principal);
       $this->ed214_i_apoio = ($this->ed214_i_apoio == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_apoio"]:$this->ed214_i_apoio);
       if($this->ed214_d_data == ""){
         $this->ed214_d_data_dia = ($this->ed214_d_data_dia == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_d_data_dia"]:$this->ed214_d_data_dia);
         $this->ed214_d_data_mes = ($this->ed214_d_data_mes == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_d_data_mes"]:$this->ed214_d_data_mes);
         $this->ed214_d_data_ano = ($this->ed214_d_data_ano == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_d_data_ano"]:$this->ed214_d_data_ano);
         if($this->ed214_d_data_dia != ""){
            $this->ed214_d_data = $this->ed214_d_data_ano."-".$this->ed214_d_data_mes."-".$this->ed214_d_data_dia;
         }
       }
       $this->ed214_i_tipo = ($this->ed214_i_tipo == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_tipo"]:$this->ed214_i_tipo);
       $this->ed214_i_escola = ($this->ed214_i_escola == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_escola"]:$this->ed214_i_escola);
       $this->ed214_i_anexo_estorage = ($this->ed214_i_anexo_estorage == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_anexo_estorage"]:$this->ed214_i_anexo_estorage);
       $this->ed214_v_cid = ($this->ed214_v_cid == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_v_cid"]:$this->ed214_v_cid);
       $this->ed214_b_amdfono = ($this->ed214_b_amdfono == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdfono"]:$this->ed214_b_amdfono);
       $this->ed214_b_amdto = ($this->ed214_b_amdto == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdto"]:$this->ed214_b_amdto);
       $this->ed214_b_amdpsi = ($this->ed214_b_amdpsi == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdpsi"]:$this->ed214_b_amdpsi);
       $this->ed214_b_amdpsiped = ($this->ed214_b_amdpsiped == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdpsiped"]:$this->ed214_b_amdpsiped);
       $this->ed214_b_amdpsiq = ($this->ed214_b_amdpsiq == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdpsiq"]:$this->ed214_b_amdpsiq);
       $this->ed214_b_amdneu = ($this->ed214_b_amdneu == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdneu"]:$this->ed214_b_amdneu);
       $this->ed214_b_amdequ = ($this->ed214_b_amdequ == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdequ"]:$this->ed214_b_amdequ);
       $this->ed214_b_amdmus = ($this->ed214_b_amdmus == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdmus"]:$this->ed214_b_amdmus);
       $this->ed214_b_amdfis = ($this->ed214_b_amdfis == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_amdfis"]:$this->ed214_b_amdfis);
       $this->ed214_v_amdoutqual = ($this->ed214_v_amdoutqual == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_v_amdoutqual"]:$this->ed214_v_amdoutqual);
       $this->ed214_b_medcon = ($this->ed214_b_medcon == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_medcon"]:$this->ed214_b_medcon);
       $this->ed214_v_medcon = ($this->ed214_v_medcon == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_v_medcon"]:$this->ed214_v_medcon);
       $this->ed214_i_curriculo = ($this->ed214_i_curriculo == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_curriculo"]:$this->ed214_i_curriculo);
       $this->ed214_i_amputado = ($this->ed214_i_amputado == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_amputado"]:$this->ed214_i_amputado);
       $this->ed214_v_ampuqual = ($this->ed214_v_ampuqual == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_v_ampuqual"]:$this->ed214_v_ampuqual);
       $this->ed214_b_usocad = ($this->ed214_b_usocad == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_usocad"]:$this->ed214_b_usocad);
       $this->ed214_b_usoort = ($this->ed214_b_usoort == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_usoort"]:$this->ed214_b_usoort);
       $this->ed214_b_usopro = ($this->ed214_b_usopro == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_usopro"]:$this->ed214_b_usopro);
       $this->ed214_b_usoben = ($this->ed214_b_usoben == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_usoben"]:$this->ed214_b_usoben);
       $this->ed214_b_usomul = ($this->ed214_b_usomul == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_usomul"]:$this->ed214_b_usomul);
       $this->ed214_b_usoand = ($this->ed214_b_usoand == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_usoand"]:$this->ed214_b_usoand);
       $this->ed214_b_avaliado = ($this->ed214_b_avaliado == "f"?@$GLOBALS["HTTP_POST_VARS"]["ed214_b_avaliado"]:$this->ed214_b_avaliado);
       $this->ed214_v_avaliado = ($this->ed214_v_avaliado == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_v_avaliado"]:$this->ed214_v_avaliado);	   
     }else{
       $this->ed214_i_codigo = ($this->ed214_i_codigo == ""?@$GLOBALS["HTTP_POST_VARS"]["ed214_i_codigo"]:$this->ed214_i_codigo);
     }
	 
   }

    public function incluir($ed214_i_codigo)
    {
      $this->atualizacampos();
     if($this->ed214_i_aluno == null ){ 
       $this->erro_sql = " Campo Aluno não informado.";
       $this->erro_campo = "ed214_i_aluno";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->ed214_i_necessidade == null ){ 
       $this->erro_sql = " Campo Necessidade não informado.";
       $this->erro_campo = "ed214_i_necessidade";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->ed214_c_principal == null ){ 
       $this->erro_sql = " Campo Necessidade Maior não informado.";
       $this->erro_campo = "ed214_c_principal";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->ed214_i_apoio == null ){ 
       $this->erro_sql = " Campo Apoio Pedagógico não informado.";
       $this->erro_campo = "ed214_i_apoio";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     if($this->ed214_d_data == null ){ 
       $this->erro_sql = " Campo Data não informado.";
       $this->erro_campo = "ed214_d_data_dia";
       $this->erro_banco = "";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
	 
     if($this->ed214_i_tipo  == null ){ 
	    $this->ed214_i_tipo = 1;
     }
     if($this->ed214_i_escola == null ){ 
       $this->ed214_i_escola = "0";
     }
     if($this->ed214_i_anexo_estorage == null ){ 
       $this->ed214_i_anexo_estorage = "0";
     }
//****************************
     if($this->ed214_v_cid == null ){ 
	   $this->ed214_v_cid = ""; 
     }
     if($this->ed214_b_amdfono == null ){ 
	   $this->ed214_b_amdfono = false;
     }

     if($this->ed214_b_amdto == null ){ 
	   $this->ed214_b_amdto = false;
     }
     if($this->ed214_b_amdpsi == null ){ 
	   $this->ed214_b_amdpsi = false;
     }
     if($this->ed214_b_amdpsiped == null ){ 
	   $this->ed214_b_amdpsiped = false;
     }
     if($this->ed214_b_amdpsiq == null ){ 
       $this->ed214_b_amdpsiq = false;
     }
     if($this->ed214_b_amdneu == null ){ 
	   $this->ed214_b_amdneu = false;
     }
     if($this->ed214_b_amdequ == null ){ 
       $this->ed214_b_amdequ = false;
     }
     if($this->ed214_b_amdmus == null ){ 
       $this->ed214_b_amdmus = false;
     }
     if($this->ed214_b_amdfis == null ){ 
       $this->ed214_b_amdfis = false;
     }
	 if($this->ed214_v_amdoutqual == null ){
	   $this->ed214_v_amdoutqual = "";
	 }
     if($this->ed214_b_medcon == null ){ 
       $this->ed214_b_medcon = false;
     }
     if($this->ed214_v_medcon == null ){ 
       $this->ed214_v_medcon = "";
     }
     if($this->ed214_i_curriculo == null ){ 
       $this->ed214_i_curriculo = false; 
     }
     if($this->ed214_i_amputado == null ){ 
       $this->ed214_i_amputado = false;
     }
     if($this->ed214_v_ampuqual == null ){ 
       $this->ed214_v_ampuqual = "";
     }
     if($this->ed214_b_usocad == null ){ 
       $this->ed214_b_usocad = false;
     }
     if($this->ed214_b_usoort == null ){ 
       $this->ed214_b_usoort = false;
     }
     if($this->ed214_b_usopro == null ){ 
       $this->ed214_b_usopro = false;
     }
     if($this->ed214_b_usoben == null ){ 
       $this->ed214_b_usoben = false;
     }
     if($this->ed214_b_usomul == null ){ 
       $this->ed214_b_usomul = false;
     }
     if($this->ed214_b_usoand == null ){ 
       $this->ed214_b_usoand = false;
     }
//     if($this->ed214_b_avaliado == null ){ 
//       $this->ed214_b_avaliado = false;
//     }
     if($this->ed214_v_avaliado == null ){ 
       $this->ed214_v_avaliado = "";
     }
	 
//****************************	 
	 
     if($ed214_i_codigo == "" || $ed214_i_codigo == null ){
       $result = db_query("select nextval('alunonecessidade_ed214_i_codigo_seq')"); 
       if($result==false){
         $this->erro_banco = str_replace("\n","",@pg_last_error());
         $this->erro_sql   = "Verifique o cadastro da sequencia: alunonecessidade_ed214_i_codigo_seq do campo: ed214_i_codigo"; 
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false; 
       }
       $this->ed214_i_codigo = pg_result($result,0,0); 
     }else{
       $result = db_query("select last_value from alunonecessidade_ed214_i_codigo_seq");
       if(($result != false) && (pg_result($result,0,0) < $ed214_i_codigo)){
         $this->erro_sql = " Campo ed214_i_codigo maior que último número da sequencia.";
         $this->erro_banco = "Sequencia menor que este número.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }else{
         $this->ed214_i_codigo = $ed214_i_codigo; 
       }
     }
     if(($this->ed214_i_codigo == null) || ($this->ed214_i_codigo == "") ){ 
       $this->erro_sql = " Campo ed214_i_codigo não declarado.";
       $this->erro_banco = "Chave Primaria zerada.";
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       return false;
     }
     $sql = "insert into alunonecessidade(
                                       ed214_i_codigo 
                                      ,ed214_i_aluno 
                                      ,ed214_i_necessidade 
                                      ,ed214_c_principal 
                                      ,ed214_i_apoio 
                                      ,ed214_d_data 
                                      ,ed214_i_tipo 
                                      ,ed214_i_escola 
                                      ,ed214_i_anexo_estorage 
                                      ,ed214_v_cid 
                                      ,ed214_b_amdfono 
                                      ,ed214_b_amdto 
                                      ,ed214_b_amdpsi 
                                      ,ed214_b_amdpsiped 
                                      ,ed214_b_amdpsiq 
                                      ,ed214_b_amdneu 
                                      ,ed214_b_amdequ 
                                      ,ed214_b_amdmus 
                                      ,ed214_b_amdfis 
                                      ,ed214_v_amdoutqual 
                                      ,ed214_b_medcon 
                                      ,ed214_v_medcon 
                                      ,ed214_i_curriculo 
                                      ,ed214_i_amputado 
                                      ,ed214_v_ampuqual 
                                      ,ed214_b_usocad 
                                      ,ed214_b_usoort 
                                      ,ed214_b_usopro 
                                      ,ed214_b_usoben 
                                      ,ed214_b_usomul 
                                      ,ed214_b_usoand 
									  ,ed214_b_avaliado
									  
                       )
                values (
                                $this->ed214_i_codigo 
                               ,$this->ed214_i_aluno 
                               ,$this->ed214_i_necessidade 
                               ,'$this->ed214_c_principal' 
                               ,$this->ed214_i_apoio 
                               ,".($this->ed214_d_data == "null" || $this->ed214_d_data == ""?"null":"'".$this->ed214_d_data."'")." 
                               ,$this->ed214_i_tipo 
                               ,$this->ed214_i_escola 
                               ,$this->ed214_i_anexo_estorage 
                               ,'$this->ed214_v_cid' 
                               ,'$this->ed214_b_amdfono' 
                               ,'$this->ed214_b_amdto' 
                               ,'$this->ed214_b_amdpsi' 
                               ,'$this->ed214_b_amdpsiped' 
                               ,'$this->ed214_b_amdpsiq' 
                               ,'$this->ed214_b_amdneu' 
                               ,'$this->ed214_b_amdequ' 
                               ,'$this->ed214_b_amdmus' 
                               ,'$this->ed214_b_amdfis' 
                               ,'$this->ed214_v_amdoutqual' 
                               ,'$this->ed214_b_medcon' 
                               ,'$this->ed214_v_medcon' 
                               ,$this->ed214_i_curriculo 
                               ,$this->ed214_i_amputado 
                               ,'$this->ed214_v_ampuqual' 
                               ,'$this->ed214_b_usocad' 
                               ,'$this->ed214_b_usoort' 
                               ,'$this->ed214_b_usopro' 
                               ,'$this->ed214_b_usoben' 
                               ,'$this->ed214_b_usomul' 
                               ,'$this->ed214_b_usoand'
							   ,'$this->ed214_b_avaliado'
		      )";
			  
$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.sql","w+");
fwrite($arq, $sql);
fwrite($arq,"\r\n");
fclose($arq); 		

     $result = db_query($sql); 
     if($result==false){ 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       if( strpos(strtolower($this->erro_banco),"duplicate key") != 0 ){
         $this->erro_sql   = "Cadastro das Necessidades dos Alunos ($this->ed214_i_codigo) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_banco = "Cadastro das Necessidades dos Alunos já Cadastrado";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }else{
         $this->erro_sql   = "Cadastro das Necessidades dos Alunos ($this->ed214_i_codigo) não Incluído. Inclusão Abortada.";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       }
       $this->erro_status = "0";
       $this->numrows_incluir= 0;
       return false;
     }
     $this->erro_banco = "";
     $this->erro_sql = "Inclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->ed214_i_codigo;
     $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
     $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
     $this->erro_status = "1";
     $this->numrows_incluir= pg_affected_rows($result);
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       $resaco = $this->sql_record($this->sql_query_file($this->ed214_i_codigo  ));
       if(($resaco!=false)||($this->numrows!=0)){

         $resac = db_query("select nextval('db_acount_id_acount_seq') as acount");
         $acount = pg_result($resac,0,0);
         $resac = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
         $resac = db_query("insert into db_acountkey values($acount,11072,'$this->ed214_i_codigo','I')");
         $resac = db_query("insert into db_acount values($acount,1907,11072,'','".AddSlashes(pg_result($resaco,0,'ed214_i_codigo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,11073,'','".AddSlashes(pg_result($resaco,0,'ed214_i_aluno'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,11074,'','".AddSlashes(pg_result($resaco,0,'ed214_i_necessidade'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,11075,'','".AddSlashes(pg_result($resaco,0,'ed214_c_principal'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,11299,'','".AddSlashes(pg_result($resaco,0,'ed214_i_apoio'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,11300,'','".AddSlashes(pg_result($resaco,0,'ed214_d_data'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,11301,'','".AddSlashes(pg_result($resaco,0,'ed214_i_tipo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,11302,'','".AddSlashes(pg_result($resaco,0,'ed214_i_escola'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1014692,'','".AddSlashes(pg_result($resaco,0,'ed214_i_anexo_estorage'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645559,'','".AddSlashes(pg_result($resaco,0,'ed214_v_cid'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645560,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdfono'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645561,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdto'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645562,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdpsi'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645563,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdpsiped'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645564,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdpsiq'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645565,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdneu'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645566,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdequ'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645567,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdmus'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645568,'','".AddSlashes(pg_result($resaco,0,'ed214_b_amdfis'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645569,'','".AddSlashes(pg_result($resaco,0,'ed214_v_amdoutqual'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645570,'','".AddSlashes(pg_result($resaco,0,'ed214_b_medcon'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645571,'','".AddSlashes(pg_result($resaco,0,'ed214_v_medcon'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645572,'','".AddSlashes(pg_result($resaco,0,'ed214_i_curriculo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645573,'','".AddSlashes(pg_result($resaco,0,'ed214_i_amputado'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645574,'','".AddSlashes(pg_result($resaco,0,'ed214_v_ampuqual'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645575,'','".AddSlashes(pg_result($resaco,0,'ed214_b_usocad'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645576,'','".AddSlashes(pg_result($resaco,0,'ed214_b_usoort'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645577,'','".AddSlashes(pg_result($resaco,0,'ed214_b_usopro'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645578,'','".AddSlashes(pg_result($resaco,0,'ed214_b_usoben'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645579,'','".AddSlashes(pg_result($resaco,0,'ed214_b_usomul'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         $resac = db_query("insert into db_acount values($acount,1907,1009645580,'','".AddSlashes(pg_result($resaco,0,'ed214_b_usoand'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");		 
         $resac = db_query("insert into db_acount values($acount,1907,1009645581,'','".AddSlashes(pg_result($resaco,0,'ed214_b_avaliado'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");		 
         $resac = db_query("insert into db_acount values($acount,1907,1009645582,'','".AddSlashes(pg_result($resaco,0,'ed214_v_avaliado'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");		 
       }
     }
     return true;
   } 

    public function alterar($ed214_i_codigo=null)
    {
      $this->atualizacampos();
     $sql = " update alunonecessidade set ";
     $virgula = "";
     if(trim($this->ed214_i_codigo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_codigo"])){ 
       $sql  .= $virgula." ed214_i_codigo = $this->ed214_i_codigo ";
       $virgula = ",";
       if(trim($this->ed214_i_codigo) == null ){ 
         $this->erro_sql = " Campo Código não informado.";
         $this->erro_campo = "ed214_i_codigo";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_i_aluno)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_aluno"])){ 
       $sql  .= $virgula." ed214_i_aluno = $this->ed214_i_aluno ";
       $virgula = ",";
       if(trim($this->ed214_i_aluno) == null ){ 
         $this->erro_sql = " Campo Aluno não informado.";
         $this->erro_campo = "ed214_i_aluno";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_i_necessidade)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_necessidade"])){ 
       $sql  .= $virgula." ed214_i_necessidade = $this->ed214_i_necessidade ";
       $virgula = ",";
       if(trim($this->ed214_i_necessidade) == null ){ 
         $this->erro_sql = " Campo Necessidade não informado.";
         $this->erro_campo = "ed214_i_necessidade";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_c_principal)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_c_principal"])){ 
       $sql  .= $virgula." ed214_c_principal = '$this->ed214_c_principal' ";
       $virgula = ",";
       if(trim($this->ed214_c_principal) == null ){ 
         $this->erro_sql = " Campo Necessidade Maior não informado.";
         $this->erro_campo = "ed214_c_principal";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_i_apoio)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_apoio"])){ 
       $sql  .= $virgula." ed214_i_apoio = $this->ed214_i_apoio ";
       $virgula = ",";
       if(trim($this->ed214_i_apoio) == null ){ 
         $this->erro_sql = " Campo Apoio Pedagógico não informado.";
         $this->erro_campo = "ed214_i_apoio";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_d_data)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_d_data_dia"]) &&  ($GLOBALS["HTTP_POST_VARS"]["ed214_d_data_dia"] !="") ){ 
       $sql  .= $virgula." ed214_d_data = '$this->ed214_d_data' ";
       $virgula = ",";
       if(trim($this->ed214_d_data) == null ){ 
         $this->erro_sql = " Campo Data não informado.";
         $this->erro_campo = "ed214_d_data_dia";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }     else{ 
       if(isset($GLOBALS["HTTP_POST_VARS"]["ed214_d_data_dia"])){ 
         $sql  .= $virgula." ed214_d_data = null ";
         $virgula = ",";
         if(trim($this->ed214_d_data) == null ){ 
           $this->erro_sql = " Campo Data não informado.";
           $this->erro_campo = "ed214_d_data_dia";
           $this->erro_banco = "";
           $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
           $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
           $this->erro_status = "0";
           return false;
         }
       }
     }
     $sql  .= $virgula." ed214_i_tipo = 1 ";
     $virgula = ",";

     if(trim($this->ed214_i_escola)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_escola"])){ 
        if(trim($this->ed214_i_escola)=="" && isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_escola"])){ 
           $this->ed214_i_escola = "0" ; 
        } 
       $sql  .= $virgula." ed214_i_escola = $this->ed214_i_escola ";
       $virgula = ",";
     }
     if(trim($this->ed214_i_anexo_estorage)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_anexo_estorage"])){ 
        if(trim($this->ed214_i_anexo_estorage)=="" && isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_anexo_estorage"])){ 
           $this->ed214_i_anexo_estorage = "0" ; 
        } 
       $sql  .= $virgula." ed214_i_anexo_estorage = $this->ed214_i_anexo_estorage ";
       $virgula = ",";
     }
	 
     if(trim($this->ed214_v_cid)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_v_cid"])){ 
       $sql  .= $virgula." ed214_v_cid = '$this->ed214_v_cid' ";
       $virgula = ",";
     }
     if(trim($this->ed214_b_amdfono)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdfono"])){ 
       $sql  .= $virgula." ed214_b_amdfono = '$this->ed214_b_amdfono' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdfono) == null ){ 
         $this->erro_sql = " Campo Fonoaudiologo não informado.";
         $this->erro_campo = "ed214_b_amdfono";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_amdto)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdto"])){ 
       $sql  .= $virgula." ed214_b_amdto = '$this->ed214_b_amdto' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdto) == null ){ 
         $this->erro_sql = " Campo Terapia ocupacional não informado.";
         $this->erro_campo = "ed214_b_amdto";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_amdpsi)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdpsi"])){ 
       $sql  .= $virgula." ed214_b_amdpsi = '$this->ed214_b_amdpsi' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdpsi) == null ){ 
         $this->erro_sql = " Campo Psicólogo não informado.";
         $this->erro_campo = "ed214_b_amdpsi";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_amdpsiped)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdpsiped"])){ 
       $sql  .= $virgula." ed214_b_amdpsiped = '$this->ed214_b_amdpsiped' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdpsiped) == null ){ 
         $this->erro_sql = " Campo Psicopedagogo não informado.";
         $this->erro_campo = "ed214_b_amdpsiped";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_amdpsiq)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdpsiq"])){ 
       $sql  .= $virgula." ed214_b_amdpsiq = '$this->ed214_b_amdpsiq' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdpsiq) == null ){ 
         $this->erro_sql = " Campo Pisiquiatra não informado.";
         $this->erro_campo = "ed214_b_amdpsiq";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_amdneu)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdneu"])){ 
       $sql  .= $virgula." ed214_b_amdneu = '$this->ed214_b_amdneu' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdneu) == null ){ 
         $this->erro_sql = " Campo Neurologista não informado.";
         $this->erro_campo = "ed214_b_amdneu";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_amdequ)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdequ"])){ 
       $sql  .= $virgula." ed214_b_amdequ = '$this->ed214_b_amdequ' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdequ) == null ){ 
         $this->erro_sql = " Campo Equoterapia não informado.";
         $this->erro_campo = "ed214_b_amdequ";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_amdmus)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdmus"])){ 
       $sql  .= $virgula." ed214_b_amdmus = '$this->ed214_b_amdmus' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdmus) == null ){ 
         $this->erro_sql = " Campo Musicoterapia não informado.";
         $this->erro_campo = "ed214_b_amdmus";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_amdfis)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_amdfis"])){ 
       $sql  .= $virgula." ed214_b_amdfis = '$this->ed214_b_amdfis' ";
       $virgula = ",";
       if(trim($this->ed214_b_amdfis) == null ){ 
         $this->erro_sql = " Campo Fisioterapia não informado.";
         $this->erro_campo = "ed214_b_amdfis";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_v_amdoutqual)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_v_amdoutqual"])){ 
       $sql  .= $virgula." ed214_v_amdoutqual = '$this->ed214_v_amdoutqual' ";
       $virgula = ",";
     }
     if(trim($this->ed214_b_medcon)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_medcon"])){ 
       $sql  .= $virgula." ed214_b_medcon = '$this->ed214_b_medcon' ";
       $virgula = ",";
       if(trim($this->ed214_b_medcon) == null ){ 
         $this->erro_sql = " Campo Medicação controlada não informado.";
         $this->erro_campo = "ed214_b_medcon";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
	 
     if(trim($this->ed214_v_medcon)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_v_medcon"])){ 
       $sql  .= $virgula." ed214_v_medcon = '$this->ed214_v_medcon' ";
       $virgula = ",";
     }
	 
     if(trim($this->ed214_b_avaliado)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_avaliado"])){ 
       $sql  .= $virgula." ed214_b_avaliado = '$this->ed214_b_avaliado' ";
       $virgula = ",";
       if(trim($this->ed214_b_avaliado) == null ){ 
         $this->erro_sql = " Campo Avaliado pela secao de educacao especial não informado.";
         $this->erro_campo = "ed214_b_avaliado";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
	 
     if(trim($this->ed214_i_curriculo)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_curriculo"])){ 
       $sql  .= $virgula." ed214_i_curriculo = $this->ed214_i_curriculo ";
       $virgula = ",";
       if(trim($this->ed214_i_curriculo) == null ){ 
         $this->erro_sql = " Campo Tipo de currículo não informado.";
         $this->erro_campo = "ed214_i_curriculo";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_i_amputado)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_amputado"])){ 
       $sql  .= $virgula." ed214_i_amputado = $this->ed214_i_amputado ";
       $virgula = ",";
       if(trim($this->ed214_i_amputado) == null ){ 
         $this->erro_sql = " Campo Amputado não informado.";
         $this->erro_campo = "ed214_i_amputado";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
	 
     if(trim($this->ed214_v_ampuqual)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_v_ampuqual"])){ 
       $sql  .= $virgula." ed214_v_ampuqual = '$this->ed214_v_ampuqual' ";
       $virgula = ",";
       if(trim($this->ed214_v_ampuqual) == null ){ 
	       $this->ed214_v_ampuqual = "";
       }
     }

     if(trim($this->ed214_b_usocad)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_usocad"])){ 
       $sql  .= $virgula." ed214_b_usocad = '$this->ed214_b_usocad' ";
       $virgula = ",";
       if(trim($this->ed214_b_usocad) == null ){ 
         $this->erro_sql = " Campo Cadeira de rodas não informado.";
         $this->erro_campo = "ed214_b_usocad";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_usoort)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_usoort"])){ 
       $sql  .= $virgula." ed214_b_usoort = '$this->ed214_b_usoort' ";
       $virgula = ",";
       if(trim($this->ed214_b_usoort) == null ){ 
         $this->erro_sql = " Campo Órtese não informado.";
         $this->erro_campo = "ed214_b_usoort";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_usopro)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_usopro"])){ 
       $sql  .= $virgula." ed214_b_usopro = '$this->ed214_b_usopro' ";
       $virgula = ",";
       if(trim($this->ed214_b_usopro) == null ){ 
         $this->erro_sql = " Campo Prótese não informado.";
         $this->erro_campo = "ed214_b_usopro";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_usoben)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_usoben"])){ 
       $sql  .= $virgula." ed214_b_usoben = '$this->ed214_b_usoben' ";
       $virgula = ",";
       if(trim($this->ed214_b_usoben) == null ){ 
         $this->erro_sql = " Campo Bengala não informado.";
         $this->erro_campo = "ed214_b_usoben";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_usomul)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_usomul"])){ 
       $sql  .= $virgula." ed214_b_usomul = '$this->ed214_b_usomul' ";
       $virgula = ",";
       if(trim($this->ed214_b_usomul) == null ){ 
         $this->erro_sql = " Campo Muleta não informado.";
         $this->erro_campo = "ed214_b_usomul";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
     if(trim($this->ed214_b_usoand)!="" || isset($GLOBALS["HTTP_POST_VARS"]["ed214_b_usoand"])){ 
       $sql  .= $virgula." ed214_b_usoand = '$this->ed214_b_usoand' ";
       $virgula = ",";
       if(trim($this->ed214_b_usoand) == null ){ 
         $this->erro_sql = " Campo Andador não informado.";
         $this->erro_campo = "ed214_b_usoand";
         $this->erro_banco = "";
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "0";
         return false;
       }
     }
	 
     $sql .= " where ";
     if($ed214_i_codigo!=null){
       $sql .= " ed214_i_codigo = $this->ed214_i_codigo";
     }
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       $resaco = $this->sql_record($this->sql_query_file($this->ed214_i_codigo));
       if ($this->numrows > 0) {

         for ($conresaco = 0; $conresaco < $this->numrows; $conresaco++) {

           $resac = db_query("select nextval('db_acount_id_acount_seq') as acount");
           $acount = pg_result($resac,0,0);
           $resac = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
           $resac = db_query("insert into db_acountkey values($acount,11072,'$this->ed214_i_codigo','A')");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_codigo"]) || $this->ed214_i_codigo != "")
             $resac = db_query("insert into db_acount values($acount,1907,11072,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_i_codigo'))."','$this->ed214_i_codigo',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_aluno"]) || $this->ed214_i_aluno != "")
             $resac = db_query("insert into db_acount values($acount,1907,11073,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_i_aluno'))."','$this->ed214_i_aluno',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_necessidade"]) || $this->ed214_i_necessidade != "")
             $resac = db_query("insert into db_acount values($acount,1907,11074,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_i_necessidade'))."','$this->ed214_i_necessidade',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_c_principal"]) || $this->ed214_c_principal != "")
             $resac = db_query("insert into db_acount values($acount,1907,11075,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_c_principal'))."','$this->ed214_c_principal',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_apoio"]) || $this->ed214_i_apoio != "")
             $resac = db_query("insert into db_acount values($acount,1907,11299,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_i_apoio'))."','$this->ed214_i_apoio',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_d_data"]) || $this->ed214_d_data != "")
             $resac = db_query("insert into db_acount values($acount,1907,11300,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_d_data'))."','$this->ed214_d_data',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_tipo"]) || $this->ed214_i_tipo != "")
             $resac = db_query("insert into db_acount values($acount,1907,11301,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_i_tipo'))."','$this->ed214_i_tipo',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_escola"]) || $this->ed214_i_escola != "")
             $resac = db_query("insert into db_acount values($acount,1907,11302,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_i_escola'))."','$this->ed214_i_escola',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           if (isset($GLOBALS["HTTP_POST_VARS"]["ed214_i_anexo_estorage"]) || $this->ed214_i_anexo_estorage != "")
             $resac = db_query("insert into db_acount values($acount,1907,1014692,'".AddSlashes(pg_result($resaco,$conresaco,'ed214_i_anexo_estorage'))."','$this->ed214_i_anexo_estorage',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         }
       }
     }
     $result = db_query($sql);
     if (!$result) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Cadastro das Necessidades dos Alunos não Alterado. Alteração Abortada.\\n";
         $this->erro_sql .= "Valores : ".$this->ed214_i_codigo;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_alterar = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Cadastro das Necessidades dos Alunos não foi Alterado. Alteração Executada.\\n";
         $this->erro_sql .= "Valores : ".$this->ed214_i_codigo;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Alteração efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$this->ed214_i_codigo;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_alterar = pg_affected_rows($result);
         return true;
       } 
     } 
   } 

    public function excluir($ed214_i_codigo=null, $dbwhere = null)
    {
     $lSessaoDesativarAccount = db_getsession("DB_desativar_account", false);
     if (!isset($lSessaoDesativarAccount) || (isset($lSessaoDesativarAccount)
       && ($lSessaoDesativarAccount === false))) {

       if (empty($dbwhere)) {

         $resaco = $this->sql_record($this->sql_query_file($ed214_i_codigo));
       } else { 
         $resaco = $this->sql_record($this->sql_query_file(null,"*",null,$dbwhere));
       }
       if (($resaco != false) || ($this->numrows!=0)) {

         for ($iresaco = 0; $iresaco < $this->numrows; $iresaco++) {

           $resac  = db_query("select nextval('db_acount_id_acount_seq') as acount");
           $acount = pg_result($resac,0,0);
           $resac  = db_query("insert into db_acountacesso values($acount,".db_getsession("DB_acessado").")");
           $resac  = db_query("insert into db_acountkey values($acount,11072,'$ed214_i_codigo','E')");
           $resac  = db_query("insert into db_acount values($acount,1907,11072,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_i_codigo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,1907,11073,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_i_aluno'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,1907,11074,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_i_necessidade'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,1907,11075,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_c_principal'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,1907,11299,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_i_apoio'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,1907,11300,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_d_data'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,1907,11301,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_i_tipo'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,1907,11302,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_i_escola'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
           $resac  = db_query("insert into db_acount values($acount,1907,1014692,'','".AddSlashes(pg_result($resaco,$iresaco,'ed214_i_anexo_estorage'))."',".db_getsession('DB_datausu').",".db_getsession('DB_id_usuario').")");
         }
       }
     }
     $sql = " delete from alunonecessidade
                    where ";
     $sql2 = "";
     if (empty($dbwhere)) {
        if (!empty($ed214_i_codigo)){
          if (!empty($sql2)) {
            $sql2 .= " and ";
          }
          $sql2 .= " ed214_i_codigo = $ed214_i_codigo ";
        }
     } else {
       $sql2 = $dbwhere;
     }
     $result = db_query($sql.$sql2);
     if ($result == false) { 
       $this->erro_banco = str_replace("\n","",@pg_last_error());
       $this->erro_sql   = "Cadastro das Necessidades dos Alunos não Excluído. Exclusão Abortada.\\n";
       $this->erro_sql .= "Valores : ".$ed214_i_codigo;
       $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
       $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
       $this->erro_status = "0";
       $this->numrows_excluir = 0;
       return false;
     } else {
       if (pg_affected_rows($result) == 0) {
         $this->erro_banco = "";
         $this->erro_sql = "Cadastro das Necessidades dos Alunos não Encontrado. Exclusão não Efetuada.\\n";
         $this->erro_sql .= "Valores : ".$ed214_i_codigo;
         $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
         $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
         $this->erro_status = "1";
         $this->numrows_excluir = 0;
         return true;
       } else {
         $this->erro_banco = "";
         $this->erro_sql = "Exclusão efetuada com sucesso.\\n";
         $this->erro_sql .= "Valores : ".$ed214_i_codigo;
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
        $this->erro_sql   = "Record Vazio na Tabela:alunonecessidade";
        $this->erro_msg   = "Usuário: \\n\\n ".$this->erro_sql." \\n\\n";
        $this->erro_msg   .=  str_replace('"',"",str_replace("'","",  "Administrador: \\n\\n ".$this->erro_banco." \\n"));
        $this->erro_status = "0";
        return false;
      }
     return $result;
   }

    public function sql_query($ed214_i_codigo = null,$campos = "*", $ordem = null, $dbwhere = "") { 

     $sql  = "select {$campos}";
     $sql .= "  from alunonecessidade ";
     $sql .= "      left  join escola  on  escola.ed18_i_codigo = alunonecessidade.ed214_i_escola";
     $sql .= "      inner join necessidade  on  necessidade.ed48_i_codigo = alunonecessidade.ed214_i_necessidade";
     $sql .= "      inner join aluno  on  aluno.ed47_i_codigo = alunonecessidade.ed214_i_aluno";
     $sql .= "      left join bairro  on  bairro.j13_codi = escola.ed18_i_bairro";
     $sql .= "      left join ruas  on  ruas.j14_codigo = escola.ed18_i_rua";
     $sql .= "      left join db_depart  on  db_depart.coddepto = escola.ed18_i_codigo";
     $sql .= "      left join pais  on  pais.ed228_i_codigo = aluno.ed47_i_pais";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($ed214_i_codigo)) {
         $sql2 .= " where alunonecessidade.ed214_i_codigo = $ed214_i_codigo "; 
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

    public function sql_query_file($ed214_i_codigo = null, $campos = "*", $ordem = null, $dbwhere = "") {

     $sql  = "select {$campos} ";
     $sql .= "  from alunonecessidade ";
     $sql2 = "";
     if (empty($dbwhere)) {
       if (!empty($ed214_i_codigo)){
         $sql2 .= " where alunonecessidade.ed214_i_codigo = $ed214_i_codigo "; 
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
