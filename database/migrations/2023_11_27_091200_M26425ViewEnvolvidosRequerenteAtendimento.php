<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M26425ViewEnvolvidosRequerenteAtendimento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getPdo()->exec(
            <<<SQL
    select configuracoes.fc_auditoria_remove_funcao('protocolo.protprocessodocumento');
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection()->getPdo()->exec(
            <<<SQL
 select configuracoes.fc_auditoria_cria_funcao('protocolo.protprocessodocumento');
CREATE OR REPLACE VIEW protocolo.view_envolvidos AS
select
	distinct
p58_codproc as codigo_processo,
	p58_numcgm as cgm,
	'TITULAR' as envolvimento
from
	protocolo.protprocesso
union
select
	distinct
p58_codproc as codigo_processo,
	configuracoes.db_usuacgm.cgmlogin as cgm,
	'ATENDENTE' as envolvimento
from
	protocolo.protprocesso
inner join configuracoes.db_usuarios on
	configuracoes.db_usuarios.id_usuario = protocolo.protprocesso.p58_id_usuario
inner join configuracoes.db_usuacgm on
	configuracoes.db_usuacgm.id_usuario = configuracoes.db_usuarios.id_usuario
union
select
	distinct
protocolo.proctransferproc.p63_codproc as codigo_processo,
	configuracoes.db_usuacgm.cgmlogin as cgm,
	'TRANSFERENTE REMETENTE' as envolvimento
from
	protocolo.proctransferproc
inner join protocolo.proctransfer on
	protocolo.proctransfer.p62_codtran = protocolo.proctransferproc.p63_codtran
inner join configuracoes.db_usuarios on
	configuracoes.db_usuarios.id_usuario = protocolo.proctransfer.p62_id_usuario
inner join configuracoes.db_usuacgm on
	configuracoes.db_usuacgm.id_usuario = configuracoes.db_usuarios.id_usuario
union
select
	distinct
protocolo.procandam.p61_codproc as codigo_processo,
	configuracoes.db_usuacgm.cgmlogin as cgm,
	'TRANSFERENTE RECEBEDOR' as envolvimento
from
	protocolo.procandam
inner join configuracoes.db_usuarios on
	configuracoes.db_usuarios.id_usuario = protocolo.procandam.p61_id_usuario
inner join configuracoes.db_usuacgm on
	configuracoes.db_usuacgm.id_usuario = configuracoes.db_usuarios.id_usuario
union
select
	distinct
protocolo.procandam.p61_codproc as codigo_processo,
	configuracoes.db_usuacgm.cgmlogin as cgm,
	'DESPACHANTE' as envolvimento
from
	protocolo.procandamint
inner join protocolo.procandam on
	protocolo.procandam.p61_codandam = protocolo.procandamint.p78_codandam
inner join configuracoes.db_usuarios on
	configuracoes.db_usuarios.id_usuario = protocolo.procandamint.p78_usuario
inner join configuracoes.db_usuacgm on
	configuracoes.db_usuacgm.id_usuario = configuracoes.db_usuarios.id_usuario
union
select
	distinct
	protocolo.protprocessodocumento.p01_protprocesso as codigo_processo,
	protocolo.documento_solicitacao_assinaturas.cgm_assinante as cgm,
	'ASSINANTE' as envolvimento
from
	protocolo.documento_solicitacao_assinaturas
inner join protocolo.protprocessodocumento on
	protocolo.protprocessodocumento.p01_sequencial = protocolo.documento_solicitacao_assinaturas.documento_id

union
select
    distinct
	protocolo.protprocessodocumento.p01_protprocesso as codigo_processo,
	protocolo.documento_solicitacao_assinaturas.cgm_solicitante  as cgm,
	'SOLICITANTE ASSINATURA' as envolvimento
from
	protocolo.documento_solicitacao_assinaturas
inner join protocolo.protprocessodocumento on
	protocolo.protprocessodocumento.p01_sequencial = protocolo.documento_solicitacao_assinaturas.documento_id
order by
	codigo_processo,
	cgm,
	envolvimento;
SQL
        );
    }
}
