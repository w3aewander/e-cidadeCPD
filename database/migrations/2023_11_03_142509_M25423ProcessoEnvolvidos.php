<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M25423ProcessoEnvolvidos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getPdo()->exec(<<<SQL

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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection()->getPdo()->exec(<<<SQL
        DROP VIEW IF EXISTS protocolo.view_envolvidos
SQL
        );
    }
}
