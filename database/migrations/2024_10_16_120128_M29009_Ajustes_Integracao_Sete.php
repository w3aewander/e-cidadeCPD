<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29009AjustesIntegracaoSete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upEstrutura();
        $this->upDicionario();
        $this->upDados();
        $this->upItensMenu();
    }

    public function upEstrutura()
    {
        $this->upEstruturaAlunos();
        $this->upEstruturaLinhas();
        $this->upEstruturaVeiculos();
    }


    public function upEstruturaAlunos()
    {
        DB::unprepared(<<<SQL

CREATE TABLE escola.alunotransportedificuldades(
  ed355_sequencial SERIAL PRIMARY KEY,
  ed355_aluno integer not null unique,
  ed355_porteira BOOLEAN NOT NULL DEFAULT FALSE,
  ed355_mataburro  BOOLEAN NOT NULL DEFAULT FALSE ,
  ed355_colchete BOOLEAN NOT NULL DEFAULT FALSE,
  ed355_atoleiro BOOLEAN NOT NULL DEFAULT FALSE,
  ed355_ponterustica BOOLEAN NOT NULL DEFAULT FALSE,
  CONSTRAINT transportedificuldades_aluno_fk FOREIGN KEY (ed355_aluno) references escola.aluno(ed47_i_codigo)
);

SELECT configuracoes.fc_auditoria_cria_funcao('escola.alunotransportedificuldades');

alter table escola.aluno add column ed47_parentescofiliacao1 int default null;
alter table escola.aluno add column ed47_parentescofiliacao2 int default null;
alter table escola.aluno add column ed47_parentescoresponsavel int default null;

SQL
        );
    }

    public function upEstruturaLinhas()
    {
        DB::unprepared(<<<SQL

CREATE TABLE transporteescolar.itinerariokm (
    tre14_sequencial SERIAL PRIMARY KEY,
    tre14_linhatransporteitinerario INTEGER NOT NULL UNIQUE,
    tre14_km FLOAT,
    CONSTRAINT linhatransporteitinerario_km_fk FOREIGN KEY (tre14_linhatransporteitinerario) REFERENCES transporteescolar.linhatransporteitinerario(tre09_sequencial)
);


CREATE TABLE transporteescolar.itinerariomeiotransporte (
    tre15_sequencial SERIAL PRIMARY KEY,
    tre15_linhatransporteitinerario INTEGER NOT NULL UNIQUE,
    tre15_meiotransporte INTEGER NOT NULL,
    CONSTRAINT linhatransporteitinerario_meiotransporte_fk FOREIGN KEY (tre15_linhatransporteitinerario) REFERENCES transporteescolar.linhatransporteitinerario(tre09_sequencial)
);

CREATE TABLE transporteescolar.itinerariodificuldadesacesso (
    tre16_sequencial SERIAL PRIMARY KEY,
    tre16_linhatransporteitinerario INTEGER NOT NULL UNIQUE,
    tre16_porteira BOOLEAN NOT NULL DEFAULT FALSE,
    tre16_mataburro  BOOLEAN NOT NULL DEFAULT FALSE ,
    tre16_colchete BOOLEAN NOT NULL DEFAULT FALSE,
    tre16_atoleiro BOOLEAN NOT NULL DEFAULT FALSE,
    tre16_ponterustica BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT linhatransporteitinerario_dificuldadesacesso_fk FOREIGN KEY (tre16_linhatransporteitinerario) REFERENCES transporteescolar.linhatransporteitinerario(tre09_sequencial)
);

SELECT configuracoes.fc_auditoria_cria_funcao('transporteescolar.itinerariokm');
SELECT configuracoes.fc_auditoria_cria_funcao('transporteescolar.itinerariomeiotransporte');
SELECT configuracoes.fc_auditoria_cria_funcao('transporteescolar.itinerariodificuldadesacesso');

SQL
        );
    }

    public function upEstruturaVeiculos()
    {
        DB::unprepared(<<<SQL

CREATE TABLE transporteescolar.tipoveiculo (
    tre17_sequencial SERIAL PRIMARY KEY,
    tre17_codigo INTEGER UNIQUE,
    tre17_descricao VARCHAR(255)
);

SELECT configuracoes.fc_auditoria_cria_funcao('transporteescolar.tipoveiculo');

ALTER TABLE transporteescolar.veiculotransportemunicipal ALTER COLUMN tre01_tipotransportemunicipal DROP NOT NULL;
ALTER TABLE transporteescolar.veiculotransportemunicipalterceiro ADD COLUMN tre03_placa VARCHAR(8);
ALTER TABLE transporteescolar.veiculotransportemunicipalterceiro ADD COLUMN tre03_renavam VARCHAR(11);
ALTER TABLE transporteescolar.veiculotransportemunicipal ADD COLUMN tre01_anoveiculo INTEGER;
ALTER TABLE transporteescolar.veiculotransportemunicipal ADD COLUMN tre01_modalidadeveiculo INTEGER;
ALTER TABLE transporteescolar.veiculotransportemunicipal ADD COLUMN tre01_tipoveiculo INTEGER;
ALTER TABLE transporteescolar.veiculotransportemunicipal ADD COLUMN tre01_acessibilidade BOOLEAN;
ALTER TABLE transporteescolar.veiculotransportemunicipal ADD CONSTRAINT veiculotransportemunicipal_tipoveiculo_fk FOREIGN KEY (tre01_tipoveiculo) REFERENCES transporteescolar.tipoveiculo(tre17_sequencial);
SQL
        );

    }

    public function upDicionario()
    {
        $this->comentaDicionarioAlunos();
        $this->comentaDicionarioLinhas();
        $this->comentaDicionarioVeiculos();
        $this->geraDicionario();
    }

    public function upItensMenu()
    {
        DB::unprepared(<<<SQL
delete from db_menu where  id_item_filho = 7147;

insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ,api ) values ( 229397 ,'Exportação Aluno' ,'Exportação Aluno' ,'web/educacao/transporte-escolar/procedimentos/sete/exportar/exportacao-aluno' ,'1' ,'1' ,'Exportação Aluno' ,'true' ,'false' );
insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ,api ) values ( 229398 ,'SETE' ,'SETE' ,'' ,'1' ,'1' ,'SETE' ,'true' ,'false' );
insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 32 ,229398 ,593 ,7147 );
insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 229398 ,229397 ,1 ,7147 );

SQL
        );
    }

    public function comentaDicionarioAlunos()
    {
        DB::unprepared(<<<SQL

COMMENT ON COLUMN escola.aluno.ed47_parentescofiliacao1 IS '{
  "descricao": "Grau  de parentesco da filiação 1",
  "rotulo": "Grau Parentesco Filiação 1",
  "rotulorel": "Grau Parentesco Filiação 1",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN escola.aluno.ed47_parentescofiliacao2 IS '{
  "descricao": "Grau  de parentesco da filiação 2",
  "rotulo": "Grau Parentesco Filiação 2",
  "rotulorel": "Grau Parentesco Filiação 2",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN escola.aluno.ed47_parentescoresponsavel IS '{
  "descricao": "Grau  de parentesco do responsável",
  "rotulo": "Grau Parentesco Responsável",
  "rotulorel": "Grau  de parentesco do responsável",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';


COMMENT ON TABLE escola.alunotransportedificuldades IS '{
  "descricao": "Dificuldades no transporte do aluno",
  "sigla": "ed355",
  "dataincl": "2024-10-18",
  "rotulo": "Dificuldades Transporte Aluno",
  "tipotabela": 0,
  "naolibclass": false,
  "naolibfunc": false,
  "naolibprog": false,
  "naolibform": false
}';

COMMENT ON COLUMN escola.alunotransportedificuldades.ed355_sequencial IS '{
  "descricao": "Sequencial",
  "rotulo": "Sequencial",
  "rotulorel": "Sequencial",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN escola.alunotransportedificuldades.ed355_aluno IS '{
  "descricao": "Aluno",
  "rotulo": "Aluno",
  "rotulorel": "Aluno",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN escola.alunotransportedificuldades.ed355_porteira IS '{
  "descricao": "Porteira",
  "rotulo": "Porteira",
  "rotulorel": "Porteira",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 1,
  "tipoobj": "text"
}';

COMMENT ON COLUMN escola.alunotransportedificuldades.ed355_mataburro IS '{
  "descricao": "Mata Burro",
  "rotulo": "Mata Burro",
  "rotulorel": "Mata Burro",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 1,
  "tipoobj": "text"
}';

COMMENT ON COLUMN escola.alunotransportedificuldades.ed355_colchete IS '{
  "descricao": "Colchete",
  "rotulo": "Colchete",
  "rotulorel": "Colchete",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 1,
  "tipoobj": "text"
}';

COMMENT ON COLUMN escola.alunotransportedificuldades.ed355_atoleiro IS '{
  "descricao": "Atoleiro",
  "rotulo": "Atoleiro",
  "rotulorel": "Atoleiro",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 1,
  "tipoobj": "text"
}';

COMMENT ON COLUMN escola.alunotransportedificuldades.ed355_ponterustica IS '{
  "descricao": "Ponte Rustica",
  "rotulo": "Ponte Rustica",
  "rotulorel": "Ponte Rustica",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 1,
  "tipoobj": "text"
}';

SQL
        );

    }

    public function comentaDicionarioVeiculos()
    {
        DB::unprepared(<<<SQL

COMMENT ON TABLE transporteescolar.tipoveiculo IS '{
  "descricao": "Tipo de veículo",
  "sigla": "tre17",
  "dataincl": "2024-10-16",
  "rotulo": "Tipo Veículo",
  "tipotabela": 0,
  "naolibclass": false,
  "naolibfunc": false,
  "naolibprog": false,
  "naolibform": false
}';

COMMENT ON COLUMN transporteescolar.tipoveiculo.tre17_sequencial IS '{
  "descricao": "Sequencial",
  "rotulo": "Sequencial",
  "rotulorel": "Sequencial",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.tipoveiculo.tre17_codigo IS '{
  "descricao": "Código",
  "rotulo": "Código",
  "rotulorel": "Código",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.tipoveiculo.tre17_descricao IS '{
  "descricao": "Descrição",
  "rotulo": "Descrição",
  "rotulorel": "Descrição",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 255,
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.veiculotransportemunicipalterceiro.tre03_placa IS '{
  "descricao": "Placa",
  "rotulo": "Placa",
  "rotulorel": "Placa",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 8,
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.veiculotransportemunicipalterceiro.tre03_renavam IS '{
  "descricao": "Renavam",
  "rotulo": "Renavam",
  "rotulorel": "Renavam",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 11,
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.veiculotransportemunicipal.tre01_anoveiculo IS '{
  "descricao": "Ano Veículo",
  "rotulo": "Ano Veículo",
  "rotulorel": "Ano Veículo",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.veiculotransportemunicipal.tre01_modalidadeveiculo IS '{
  "descricao": "Modalidade Veículo",
  "rotulo": "Modalidade Veículo",
  "rotulorel": "Modalidade Veículo",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.veiculotransportemunicipal.tre01_tipoveiculo IS '{
  "descricao": "Tipo Veículo",
  "rotulo": "Tipo Veículo",
  "rotulorel": "Tipo Veículo",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 10,
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.veiculotransportemunicipal.tre01_acessibilidade IS '{
  "descricao": "Acessibilidade",
  "rotulo": "Acessibilidade",
  "rotulorel": "Acessibilidade",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": 1,
  "tipoobj": "text"
}';

SQL
        );

    }

    public function comentaDicionarioLinhas()
    {
        DB::unprepared(<<<SQL

COMMENT ON TABLE transporteescolar.itinerariokm IS '{
  "descricao": "Km do itinerário",
  "sigla": "tre14",
  "dataincl": "2024-10-16",
  "rotulo": "Itinerario Km",
  "tipotabela": 0,
  "naolibclass": false,
  "naolibfunc": false,
  "naolibprog": false,
  "naolibform": false
}';

COMMENT ON COLUMN transporteescolar.itinerariokm.tre14_sequencial IS '{
  "descricao": "Sequencial Km",
  "rotulo": "Sequencial Km",
  "rotulorel": "Sequencial Km",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariokm.tre14_linhatransporteitinerario IS '{
  "descricao": "Itinerário",
  "rotulo": "Itinerário",
  "rotulorel": "Itinerário",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariokm.tre14_km IS '{
  "descricao": "Km",
  "rotulo": "Km",
  "rotulorel": "Km",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "5",
  "tipoobj": "text"
}';


COMMENT ON TABLE transporteescolar.itinerariomeiotransporte IS '{
  "descricao": "Meio de transporte do itinerário",
  "sigla": "tre15",
  "dataincl": "2024-10-16",
  "rotulo": "Itinerario Meio Transporte",
  "tipotabela": 0,
  "naolibclass": false,
  "naolibfunc": false,
  "naolibprog": false,
  "naolibform": false
}';

COMMENT ON COLUMN transporteescolar.itinerariomeiotransporte.tre15_sequencial IS '{
  "descricao": "Sequencial",
  "rotulo": "Sequencial",
  "rotulorel": "Sequencial",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariomeiotransporte.tre15_linhatransporteitinerario IS '{
  "descricao": "Itinerário",
  "rotulo": "Itinerário",
  "rotulorel": "Itinerário",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariomeiotransporte.tre15_meiotransporte IS '{
  "descricao": "Meio de transporte",
  "rotulo": "Meio de transporte",
  "rotulorel": "Meio de transporte",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "2",
  "tipoobj": "text"
}';

COMMENT ON TABLE transporteescolar.itinerariodificuldadesacesso IS '{
  "descricao": "Dificuldades de acesso do itinerário",
  "sigla": "tre16",
  "dataincl": "2024-10-16",
  "rotulo": "Itinerario Dificuldades Acesso",
  "tipotabela": 0,
  "naolibclass": false,
  "naolibfunc": false,
  "naolibprog": false,
  "naolibform": false
}';

COMMENT ON COLUMN transporteescolar.itinerariodificuldadesacesso.tre16_sequencial IS '{
  "descricao": "Sequencial",
  "rotulo": "Sequencial",
  "rotulorel": "Sequencial",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariodificuldadesacesso.tre16_linhatransporteitinerario IS '{
  "descricao": "Itinerário",
  "rotulo": "Itinerário",
  "rotulorel": "Itinerário",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "10",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariodificuldadesacesso.tre16_porteira IS '{
  "descricao": "Tem Porteira?",
  "rotulo": "Tem Porteira",
  "rotulorel": "Tem Porteira?",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "1",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariodificuldadesacesso.tre16_mataburro IS '{
  "descricao": "Tem Mata-Burro?",
  "rotulo": "Tem Mata-Burro",
  "rotulorel": "Tem Mata-Burro?",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "1",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariodificuldadesacesso.tre16_colchete IS '{
  "descricao": "Tem Colchete?",
  "rotulo": "Tem Colchete",
  "rotulorel": "Tem Colchete?",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "1",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariodificuldadesacesso.tre16_atoleiro IS '{
  "descricao": "Atoleiro?",
  "rotulo": "Atoleiro",
  "rotulorel": "Atoleiro?",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "1",
  "tipoobj": "text"
}';

COMMENT ON COLUMN transporteescolar.itinerariodificuldadesacesso.tre16_ponterustica IS '{
  "descricao": "Ponte Rústica?",
  "rotulo": "Ponte Rústica",
  "rotulorel": "Ponte Rústica?",
  "maiusculo": false,
  "autocompl": false,
  "aceitatipo": 0,
  "tamanho": "1",
  "tipoobj": "text"
}';

SQL
        );

    }

    public function geraDicionario()
    {
        DB::unprepared(<<<SQL

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

select fc_gera_dicionario_apartir_tabela('transporteescolar','itinerariokm');
select fc_gera_dicionario_apartir_tabela('transporteescolar','itinerariomeiotransporte');
select fc_gera_dicionario_apartir_tabela('transporteescolar','itinerariodificuldadesacesso');
select fc_gera_dicionario_apartir_tabela('transporteescolar','tipoveiculo');
select fc_gera_dicionario_apartir_tabela('transporteescolar','linhatransportehorario');
select fc_gera_dicionario_apartir_tabela('transporteescolar','veiculotransportemunicipal');
select fc_gera_dicionario_apartir_tabela('transporteescolar','veiculotransportemunicipalterceiro');
select fc_gera_dicionario_apartir_tabela('escola','alunotransportedificuldades');
select fc_gera_dicionario_apartir_tabela('escola','aluno');

ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;

SQL
        );

    }

    public function upDados()
    {
        DB::unprepared(<<<SQL
INSERT INTO tipoveiculo (tre17_codigo,tre17_descricao) VALUES
   (1,'Ônibus'),
   (2,'Micro-ônibus'),
   (3,'Van'),
   (4,'Kombi'),
   (5,'Caminhão'),
   (6,'Caminhonete'),
   (7,'Motocicleta'),
   (8,'Animal de tração'),
   (9,'Lancha/Voadeira'),
   (10,'Barco de madeira'),
   (11,'Barco de alumínio'),
   (12,'Canoa motorizada'),
   (13,'Canoa a remo');
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
        $this->downEstrutura();
        $this->downItensMenu();
    }

    public function downEstrutura()
    {
        $this->downEstruturaAlunos();
        $this->downEstruturaTransporteEscolar();
    }

    public function downEstruturaAlunos()
    {
        DB::unprepared(<<<SQL
DROP TABLE IF EXISTS escola.alunotransportedificuldades;
alter table escola.aluno drop column ed47_parentescofiliacao1;
alter table escola.aluno drop column ed47_parentescofiliacao2;
alter table escola.aluno drop column ed47_parentescoresponsavel;

SQL
        );
    }

    public function downEstruturaTransporteEscolar()
    {
        DB::unprepared(<<<SQL
ALTER TABLE transporteescolar.veiculotransportemunicipal ALTER COLUMN tre01_tipotransportemunicipal SET NOT NULL;
ALTER TABLE transporteescolar.veiculotransportemunicipalterceiro DROP COLUMN tre03_placa;
ALTER TABLE transporteescolar.veiculotransportemunicipalterceiro DROP COLUMN tre03_renavam;
ALTER TABLE transporteescolar.veiculotransportemunicipal DROP COLUMN tre01_anoveiculo;
ALTER TABLE transporteescolar.veiculotransportemunicipal DROP COLUMN tre01_modalidadeveiculo;
ALTER TABLE transporteescolar.veiculotransportemunicipal DROP COLUMN tre01_tipoveiculo;
ALTER TABLE transporteescolar.veiculotransportemunicipal DROP COLUMN tre01_acessibilidade;
DROP TABLE IF EXISTS transporteescolar.tipoveiculo;
DROP TABLE IF EXISTS transporteescolar.itinerariokm;
DROP TABLE IF EXISTS transporteescolar.itinerariomeiotransporte;
DROP TABLE IF EXISTS transporteescolar.itinerariodificuldadesacesso;
SQL
        );
    }

    public function downItensMenu()
    {
        DB::unprepared(<<<SQL
delete from db_menu where id_item_filho in (229397,229398);
delete from db_itensmenu where id_item in (229397,229398);
SQL
        );
    }

}
