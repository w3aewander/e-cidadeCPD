<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29095Estrutura extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->estruturas();
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
alter table orcamento.orcparamrelnota alter column o42_sequencial drop default;
drop table if exists contabilidade.lrfvalormanual;
SQL
        );
    }

    private function estruturas()
    {
        DB::unprepared(<<<SQL
create table contabilidade.lrfvalormanual (
	c180_codigo serial primary key,
	c180_relatorio int not null,
	c180_instituicao int not null,
	c180_linha int not null,
	c180_coluna varchar(30),
	c180_exercicio int not null,
	c180_mes int,
	c180_valor numeric(17,2) ,
	created_at timestamp,
	updated_at timestamp,
 	FOREIGN KEY (c180_relatorio) REFERENCES orcamento.orcparamrel (o42_codparrel),
 	FOREIGN KEY (c180_instituicao) REFERENCES configuracoes.db_config (codigo)
);

create index lrfvalormanual_relatorio_in ON contabilidade.lrfvalormanual(c180_relatorio);
create index lrfvalormanual_instituicao_in ON contabilidade.lrfvalormanual(c180_instituicao);

create index lrfvalormanual_filtro_um_in on contabilidade.lrfvalormanual(c180_relatorio, c180_instituicao, c180_linha, c180_coluna);

alter table orcamento.orcparamrelnota alter column o42_sequencial set default nextval('orcamento.orcparamrelnota_o42_sequencial_seq');
SQL
        );
    }

    private function dicionario()
    {
        DB::unprepared(<<<SQL
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

COMMENT ON TABLE contabilidade.lrfvalormanual IS
  '{
      "descricao": "Tabela para armazenar os valores manuais dos novos relatórios da LRF",
      "sigla": "c180",
      "dataincl": "2024-10-10",
      "rotulo": "lrfvalormanual",
      "tipotabela": 0,
      "naolibclass": false,
      "naolibfunc": false,
      "naolibprog": false,
      "naolibform": false
   }';

COMMENT ON COLUMN contabilidade.lrfvalormanual.c180_codigo IS
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

COMMENT ON COLUMN contabilidade.lrfvalormanual.c180_relatorio IS
  '{
      "descricao": "Código do relatório ",
      "rotulo": "Relatório",
      "rotulorel": "Relatório",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.lrfvalormanual.c180_instituicao IS
  '{
      "descricao": "Código da Institiução",
      "rotulo": "Instituição",
      "rotulorel": "Instituição",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.lrfvalormanual.c180_linha IS
  '{
      "descricao": "Número da linha",
      "rotulo": "Linha",
      "rotulorel": "Linha",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.lrfvalormanual.c180_coluna IS
  '{
      "descricao": "Nome da coluna",
      "rotulo": "Nome da coluna",
      "rotulorel": "Nome da coluna",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 3,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.lrfvalormanual.c180_exercicio IS
  '{
      "descricao": "Exercício",
      "rotulo": "Exercício",
      "rotulorel": "Exercício",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';


COMMENT ON COLUMN contabilidade.lrfvalormanual.c180_mes IS
  '{
      "descricao": "Mês",
      "rotulo": "Mês",
      "rotulorel": "Mês",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 10,
      "tipoobj": "text"
   }';

COMMENT ON COLUMN contabilidade.lrfvalormanual.c180_valor IS
  '{
      "descricao": "Valor",
      "rotulo": "Valor",
      "rotulorel": "Valor",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 4,
      "tamanho": 10,
      "tipoobj": "text"
   }';

SELECT fc_gera_dicionario_apartir_tabela('contabilidade', 'lrfvalormanual');
SELECT fc_gera_dicionario_apartir_tabela('contabilidade', 'lrfvalormanual');

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
SQL
        );
    }
}
