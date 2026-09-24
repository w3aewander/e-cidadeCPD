<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29095EstruturaVersoes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->estrutura();
        $this->dicionario();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(<<<SQL
drop table if exists contabilidade.emissoeslrf;
SQL
        );
    }

    private function dicionario()
    {
        DB::unprepared(<<<SQL
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

COMMENT ON TABLE contabilidade.emissoeslrf IS
  '{
      "descricao": "Tabela para armazenar emissões dos relatórios da LRF",
      "sigla": "c181",
      "dataincl": "2024-10-10",
      "rotulo": "emissoeslrf",
      "tipotabela": 0,
      "naolibclass": false,
      "naolibfunc": false,
      "naolibprog": false,
      "naolibform": false
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_codigo IS
  '{
      "descricao": "Chave primária da tabela",
      "rotulo": "Código",
      "rotulorel": "Código",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_relatorio IS
  '{
      "descricao": "Código do relatório",
      "rotulo": "Relatório",
      "rotulorel": "Relatório",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_periodo IS
  '{
      "descricao": "Código do período",
      "rotulo": "Período",
      "rotulorel": "Período",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_usuario IS
  '{
      "descricao": "Código do Usuário",
      "rotulo": "Usuário",
      "rotulorel": "Usuário",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_instituicao IS
  '{
      "descricao": "Código da Instituição",
      "rotulo": "Instituição",
      "rotulorel": "Instituição",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_status IS
  '{
      "descricao": "Status do envio",
      "rotulo": "Status",
      "rotulorel": "Status",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 2,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_publicado IS
  '{
      "descricao": "Se foi publicado",
      "rotulo": "Se publicado",
      "rotulorel": "Se publicado",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 5,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_storage IS
  '{
      "descricao": "Ids dos arquivos no storage.",
      "rotulo": "Ids do storage",
      "rotulorel": "Ids do storage",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 2,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.emissoeslrf.c181_filtrosemissao IS
  '{
      "descricao": "Filtros usados na emissão do relatório",
      "rotulo": "Filtros da emissão",
      "rotulorel": "Filtros da emissão",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 2,
      "tamanho": 10,
      "tipoobj": "text"
   }';

SELECT fc_gera_dicionario_apartir_tabela('contabilidade', 'emissoeslrf');
SELECT fc_gera_dicionario_apartir_tabela('contabilidade', 'emissoeslrf');

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
SQL
        );
    }

    private function estrutura()
    {
        DB::unprepared(<<<SQL
create table contabilidade.emissoeslrf (
    c181_codigo serial primary key,
    c181_relatorio integer not null,
    c181_periodo integer not null,
    c181_usuario integer not null,
    c181_instituicao integer not null,
    c181_status varchar(15) default 'PROCESSANDO',
    c181_publicado boolean default false,
    c181_storage jsonb default null,
    c181_filtrosemissao jsonb default null,
    created_at timestamp,
    updated_at timestamp,
    FOREIGN KEY (c181_relatorio) REFERENCES orcamento.orcparamrel (o42_codparrel),
    FOREIGN KEY (c181_periodo) REFERENCES configuracoes.periodo(o114_sequencial),
    FOREIGN KEY (c181_usuario) REFERENCES configuracoes.db_usuarios(id_usuario),
    FOREIGN KEY (c181_instituicao) REFERENCES configuracoes.db_config(codigo)
);


ALTER TABLE contabilidade.emissoeslrf
ADD CONSTRAINT emissoes_lrf_status
CHECK ( (c181_status::text = ANY (ARRAY['PROCESSANDO'::text, 'PROCESSADO'::text, 'ERRO'::text])));

create index emissoes_lrf_relatorio_in ON contabilidade.emissoeslrf(c181_relatorio);
create index emissoes_lrf_instituicao_in ON contabilidade.emissoeslrf(c181_instituicao);
create index emissoes_lrf_periodo_in ON contabilidade.emissoeslrf(c181_periodo);
create index emissoes_lrf_usuario_in ON contabilidade.emissoeslrf(c181_usuario);
SQL
        );
    }
}
