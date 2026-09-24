<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M26740OtimizacaoEnvolvidos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getPdo()->exec(<<<SQL
DROP VIEW IF EXISTS  protocolo.view_envolvidos;
CREATE OR REPLACE FUNCTION protocolo.fc_envolvidos(cgms integer[])
 RETURNS TABLE(codigo_processo integer, cgm integer, envolvimento text)
 LANGUAGE plpgsql
AS $$
begin 

RETURN QUERY
select 
   col1, 
   col2::integer,
col3::text as col3
 from (
SELECT DISTINCT protprocesso.p58_codproc AS col1,
    protprocesso.p58_numcgm AS col2,
    'TITULAR'::text as col3
   FROM protprocesso
   where protprocesso.p58_numcgm =  any  (CGMS)
UNION
 SELECT DISTINCT protprocesso.p58_codproc AS col1,
    db_usuacgm.cgmlogin AS col2,
    'ATENDENTE'::text as col3
   FROM protprocesso
     JOIN db_usuarios ON db_usuarios.id_usuario = protprocesso.p58_id_usuario
     JOIN db_usuacgm ON db_usuacgm.id_usuario = db_usuarios.id_usuario
     where 
          db_usuacgm.cgmlogin =  ANY (CGMS)
UNION
 SELECT DISTINCT proctransferproc.p63_codproc AS col1,
    db_usuacgm.cgmlogin AS col2,
    'TRANSFERENTE REMETENTE'::text AS envolvimeouvidoriaatendimentonto
   FROM proctransferproc
     JOIN proctransfer ON proctransfer.p62_codtran = proctransferproc.p63_codtran
     JOIN db_usuarios ON db_usuarios.id_usuario = proctransfer.p62_id_usuario
     JOIN db_usuacgm ON db_usuacgm.id_usuario = db_usuarios.id_usuario
     where db_usuacgm.cgmlogin = ANY (CGMS)
UNION
 SELECT DISTINCT procandam.p61_codproc AS col1,
    db_usuacgm.cgmlogin AS col2,
    'TRANSFERENTE RECEBEDOR'::text as col3
   FROM procandam
     JOIN db_usuarios ON db_usuarios.id_usuario = procandam.p61_id_usuario
     JOIN db_usuacgm ON db_usuacgm.id_usuario = db_usuarios.id_usuario
     where db_usuacgm.cgmlogin = ANY (CGMS)
UNION
 SELECT DISTINCT procandam.p61_codproc AS col1,
    db_usuacgm.cgmlogin AS col2,
    'DESPACHANTE'::text as col3
   FROM procandamint
     JOIN procandam ON procandam.p61_codandam = procandamint.p78_codandam
     JOIN db_usuarios ON db_usuarios.id_usuario = procandamint.p78_usuario
     JOIN db_usuacgm ON db_usuacgm.id_usuario = db_usuarios.id_usuario
     where db_usuacgm.cgmlogin = ANY (CGMS)
UNION
 SELECT DISTINCT protprocessodocumento.p01_protprocesso AS col1,
    documento_solicitacao_assinaturas.cgm_assinante AS col2,
    'ASSINANTE'::text as col3
   FROM documento_solicitacao_assinaturas
     JOIN protprocessodocumento ON protprocessodocumento.p01_sequencial = documento_solicitacao_assinaturas.documento_id
       where documento_solicitacao_assinaturas.cgm_assinante =  ANY (CGMS)
UNION
 SELECT DISTINCT protprocessodocumento.p01_protprocesso AS col1,
    documento_solicitacao_assinaturas.cgm_solicitante AS col2,
    'SOLICITANTE ASSINATURA'::text as col3
   FROM documento_solicitacao_assinaturas
     JOIN protprocessodocumento ON protprocessodocumento.p01_sequencial = documento_solicitacao_assinaturas.documento_id
      where documento_solicitacao_assinaturas.cgm_solicitante = ANY (CGMS)
UNION
 SELECT DISTINCT protprocesso.p58_codproc AS col1,
    cgm.z01_numcgm AS col2,
    'REQUERENTE ATENDIMENTO'::text as col3
   FROM ouvidoriaatendimento
     JOIN ouvidoriaatendimentocidadao ON ouvidoriaatendimentocidadao.ov10_ouvidoriaatendimento = ouvidoriaatendimento.ov01_sequencial
     JOIN cidadao ON cidadao.ov02_sequencial = ouvidoriaatendimentocidadao.ov10_cidadao
     JOIN processoouvidoria ON processoouvidoria.ov09_ouvidoriaatendimento = ouvidoriaatendimento.ov01_sequencial
     JOIN protprocesso ON protprocesso.p58_codproc = processoouvidoria.ov09_protprocesso
     JOIN cgm ON cgm.z01_cgccpf::text = cidadao.ov02_cnpjcpf::text
     where  cgm.z01_numcgm =   ANY (CGMS)
  ORDER BY 1, 2, 3
 ) as  envolvidos_aux;

END;
 $$;

SQL
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection()->getPdo()->exec(<<<SQL
DROP FUNCTION  IF EXISTS protocolo.fc_envolvidos;
       CREATE OR REPLACE VIEW protocolo.view_envolvidos
AS SELECT DISTINCT protprocesso.p58_codproc AS codigo_processo,
    protprocesso.p58_numcgm AS cgm,
    'TITULAR'::text AS envolvimento
   FROM protprocesso
UNION
 SELECT DISTINCT protprocesso.p58_codproc AS codigo_processo,
    db_usuacgm.cgmlogin AS cgm,
    'ATENDENTE'::text AS envolvimento
   FROM protprocesso
     JOIN db_usuarios ON db_usuarios.id_usuario = protprocesso.p58_id_usuario
     JOIN db_usuacgm ON db_usuacgm.id_usuario = db_usuarios.id_usuario
UNION
 SELECT DISTINCT proctransferproc.p63_codproc AS codigo_processo,
    db_usuacgm.cgmlogin AS cgm,
    'TRANSFERENTE REMETENTE'::text AS envolvimento
   FROM proctransferproc
     JOIN proctransfer ON proctransfer.p62_codtran = proctransferproc.p63_codtran
     JOIN db_usuarios ON db_usuarios.id_usuario = proctransfer.p62_id_usuario
     JOIN db_usuacgm ON db_usuacgm.id_usuario = db_usuarios.id_usuario
UNION
 SELECT DISTINCT procandam.p61_codproc AS codigo_processo,
    db_usuacgm.cgmlogin AS cgm,
    'TRANSFERENTE RECEBEDOR'::text AS envolvimento
   FROM procandam
     JOIN db_usuarios ON db_usuarios.id_usuario = procandam.p61_id_usuario
     JOIN db_usuacgm ON db_usuacgm.id_usuario = db_usuarios.id_usuario
UNION
 SELECT DISTINCT procandam.p61_codproc AS codigo_processo,
    db_usuacgm.cgmlogin AS cgm,
    'DESPACHANTE'::text AS envolvimento
   FROM procandamint
     JOIN procandam ON procandam.p61_codandam = procandamint.p78_codandam
     JOIN db_usuarios ON db_usuarios.id_usuario = procandamint.p78_usuario
     JOIN db_usuacgm ON db_usuacgm.id_usuario = db_usuarios.id_usuario
UNION
 SELECT DISTINCT protprocessodocumento.p01_protprocesso AS codigo_processo,
    documento_solicitacao_assinaturas.cgm_assinante AS cgm,
    'ASSINANTE'::text AS envolvimento
   FROM documento_solicitacao_assinaturas
     JOIN protprocessodocumento ON protprocessodocumento.p01_sequencial = documento_solicitacao_assinaturas.documento_id
UNION
 SELECT DISTINCT protprocessodocumento.p01_protprocesso AS codigo_processo,
    documento_solicitacao_assinaturas.cgm_solicitante AS cgm,
    'SOLICITANTE ASSINATURA'::text AS envolvimento
   FROM documento_solicitacao_assinaturas
     JOIN protprocessodocumento ON protprocessodocumento.p01_sequencial = documento_solicitacao_assinaturas.documento_id
union
    select
     DISTINCT 
    protocolo.protprocesso.p58_codproc AS codigo_processo,
    protocolo.cgm.z01_numcgm  AS cgm,
    'REQUERENTE ATENDIMENTO'::text AS envolvimento
from
    ouvidoria.ouvidoriaatendimento
inner join ouvidoria.ouvidoriaatendimentocidadao on
    ouvidoria.ouvidoriaatendimentocidadao.ov10_ouvidoriaatendimento = ouvidoria.ouvidoriaatendimento.ov01_sequencial
inner join ouvidoria.cidadao on
    ouvidoria.cidadao.ov02_sequencial = ouvidoria.ouvidoriaatendimentocidadao.ov10_cidadao
inner join ouvidoria.processoouvidoria on
    ouvidoria.processoouvidoria.ov09_ouvidoriaatendimento  = ouvidoria.ouvidoriaatendimento.ov01_sequencial
inner join protocolo.protprocesso on
    protocolo.protprocesso.p58_codproc = ouvidoria.processoouvidoria.ov09_protprocesso 
inner join protocolo.cgm  on protocolo.cgm.z01_cgccpf = ouvidoria.cidadao.ov02_cnpjcpf 
  ORDER BY 1, 2, 3;
SQL
        );
    }
}
