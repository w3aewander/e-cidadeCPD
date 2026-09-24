<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M31292NovosCamposConvenioFolha extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = <<<SQL
alter table pessoal.convenio add column r56_posrubrica varchar(6) default null;

 COMMENT ON COLUMN pessoal.convenio.r56_posrubrica IS '{
  "descricao": "Posição da Rubrica1",
  "rotulo": "r56_posrubrica",
  "rotulorel": "Posição da Rubrica1",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 1,
  "tamanho": 6,
  "tipoobj": "text"
}';

create table pessoal.movrel_2 (r54_sequencial serial primary key, 
r54_anomes varchar(6) not null, 
r54_codrel varchar(4) not null, 
r54_regist int not null, 
r54_codeve varchar(4) not null, 
r54_quant1 numeric(10,2) not null, 
r54_quant2 numeric(10,2) not null, 
r54_quant3 numeric(10,2) not null, 
r54_lancad boolean default false, 
r54_instit int not null);

create index movrel_in on movrel(r54_anomes, r54_codrel, r54_regist);
create index movrel_anomes_regist_in on movrel(r54_anomes, r54_regist);
create index movrel_regist_in on movrel(r54_regist);


insert into pessoal.movrel_2 (r54_anomes,
                              r54_codrel,
                              r54_regist,
                              r54_codeve,
                              r54_quant1,
                              r54_quant2,
                              r54_quant3,
                              r54_lancad,
                              r54_instit)
                       select r54_anomes,
                              r54_codrel,
                              r54_regist,
                              r54_codeve,
                              r54_quant1,
                              r54_quant2,
                              r54_quant3,
                              r54_lancad,
                              r54_instit
                         from pessoal.movrel;

drop table pessoal.movrel;
alter table pessoal.movrel_2 rename to movrel;

COMMENT ON TABLE pessoal.movrel IS '{
  "descricao": "Importacao arquivo Convenio/Efetividade",
  "sigla": "r54",
  "dataincl": "2025-06-24",
  "rotulo": "Importacao arquivo Convenio/Efetividade",
  "tipotabela": 0,
  "naolibclass": false,
  "naolibfunc": false,
  "naolibprog": false,
  "naolibform": false
}';


COMMENT ON COLUMN pessoal.movrel.r54_sequencial IS '{
  "descricao": "Sequencial",
  "rotulo": "Sequencial",
  "rotulorel": "Sequencial",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 1,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_anomes IS '{
  "descricao": "Ano/Mes",
  "rotulo": "Ano/Mes",
  "rotulorel": "Ano/Mes",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 1,
  "tamanho": "6",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_codrel IS '{
  "descricao": "Código Convênio",
  "rotulo": "Código Convênio",
  "rotulorel": "Código Convênio",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 1,
  "tamanho": "4",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_regist IS '{
  "descricao": "Matricula Servidor",
  "rotulo": "Matricula Servidor",
  "rotulorel": "Matricula Servidor",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 1,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_codeve IS '{
  "descricao": "Código Relacionamento",
  "rotulo": "Código Relacionamento",
  "rotulorel": "Código Relacionamento",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 1,
  "tamanho": "3",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_quant1 IS '{
  "descricao": "Qtd. Rubrica 1",
  "rotulo": "Qtd. Rubrica 1",
  "rotulorel": "Qtd. Rubrica 1",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 4,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_quant2 IS '{
  "descricao": "Qtd. Rubrica 2",
  "rotulo": "Qtd. Rubrica 2",
  "rotulorel": "Qtd. Rubrica 2",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 4,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_quant3 IS '{
  "descricao": "Qtd. Rubrica 3",
  "rotulo": "Qtd. Rubrica 3",
  "rotulorel": "Qtd. Rubrica 3",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 4,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_lancad IS '{
  "descricao": "Lançamento efetivado",
  "rotulo": "Lançamento efetivado",
  "rotulorel": "Lançamento efetivado",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 5,
  "tamanho": "1",
  "tipoobj": "text"
}';

COMMENT ON COLUMN pessoal.movrel.r54_instit IS '{
  "descricao": "Instituição",
  "rotulo": "Instituição",
  "rotulorel": "Instituição",
  "maiusculo": false,
  "autocompl": true,
  "aceitatipo": 1,
  "tamanho": "2",
  "tipoobj": "text"
}';

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

SELECT configuracoes.fc_auditoria_cria_funcao('pessoal.convenio');
SELECT configuracoes.fc_auditoria_cria_funcao('pessoal.movrel');

SELECT fc_gera_dicionario_apartir_tabela('pessoal', 'convenio');
SELECT fc_gera_dicionario_apartir_tabela('pessoal', 'movrel');

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;        


SQL;
        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = <<<SQL

select configuracoes.fc_auditoria_remove_funcao('pessoal.convenio');
select configuracoes.fc_auditoria_remove_funcao('pessoal.movrel');

alter table pessoal.convenio drop column r56_posrubrica;
alter table pessoal.movrel drop column r54_sequencial;

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

SELECT fc_gera_dicionario_apartir_tabela('pessoal', 'convenio');
SELECT fc_gera_dicionario_apartir_tabela('pessoal', 'movrel');

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE; 

SQL;
        DB::unprepared($sql);
    }
}
