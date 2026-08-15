<?php

use Classes\PostgresMigration;

class M16509CriandoTabelasDePara extends PostgresMigration
{
    public function up()
    {
        $this->upDDL();
        $this->upDicionario();
    }

    public function down()
    {
        $this->downDDL();
        $this->downDicionario();
    }

    private function upDicionario()
    {
        $this->execute(<<<SQL

            insert into db_sysarquivo values (1010622, 'receitaexterna', 'Tabela criada para guardar as receitas externas', 'k191', '2020-10-05', 'Receita Externa', 0, 'f', 'f', 'f', 'f' );
            insert into db_sysarqmod values (5,1010622);
            insert into db_sysarquivo values (1010623, 'receitaexternatabrec', 'Tabela de ligação entre receita externa e receita do ecidade', 'k192', '2020-10-05', 'Receita Externa x Tabrec', 0, 'f', 'f', 'f', 'f' );
            insert into db_sysarqmod values (5,1010623);
            insert into db_syscampo values(1011841,'k191_sequencial','int8','Sequencial da tabela','0', 'Sequencial',10,'f','f','f',1,'text','Sequencial');
            insert into db_syscampo values(1011842,'k191_descricao','varchar(255)','Descrição da receita externa','', 'Descrição',255,'f','t','f',0,'text','Descrição');
            insert into db_syscampo values(1011843,'k191_receitaexterna','int8','Código da receita externa','0', 'Receita Externa',10,'f','f','f',1,'text','Receita Externa');
            insert into db_syscampo values(1011844,'k192_sequencial','int8','Sequencial da tabela','0', 'Sequencial',10,'f','f','f',1,'text','Sequencial');
            insert into db_syscampo values(1011845,'k192_receitaexterna','int8','Sequencial da tabela Receita externa','0', 'Receita Externa',10,'f','f','f',1,'text','Receita Externa');
            insert into db_syscampo values(1011846,'k192_receit','int8','Código ta receita do ecidade (k02_codigo)','0', 'Receita',10,'f','f','f',1,'text','Receita');
            delete from db_sysarqcamp where codarq = 1010622;
            insert into db_sysarqcamp values(1010622,1011841,1,0);
            insert into db_sysarqcamp values(1010622,1011842,2,0);
            insert into db_sysarqcamp values(1010622,1011843,3,0);
            delete from db_sysprikey where codarq = 1010622;
            insert into db_sysprikey (codarq,codcam,sequen,camiden) values(1010622,1011841,1,1011841);
            delete from db_sysarqcamp where codarq = 1010623;
            insert into db_sysarqcamp values(1010623,1011844,1,0);
            insert into db_sysarqcamp values(1010623,1011845,2,0);
            insert into db_sysarqcamp values(1010623,1011846,3,0);
            delete from db_sysprikey where codarq = 1010623;
            insert into db_sysprikey (codarq,codcam,sequen,camiden) values(1010623,1011844,1,1011844);
            delete from db_sysprikey where codarq = 1010623;
            insert into db_sysprikey (codarq,codcam,sequen,camiden) values(1010623,1011844,1,1011844);
            delete from db_sysforkey where codarq = 1010623 and referen = 0;
            insert into db_sysforkey values(1010623,1011845,1,1010622,0);
            delete from db_sysforkey where codarq = 1010623 and referen = 0;
            insert into db_sysforkey values(1010623,1011846,1,75,0);

SQL
        );
    }

    private function downDicionario()
    {
        $this->execute(<<<SQL

            delete from db_sysforkey where codarq in (1010622, 1010623);
            delete from db_sysprikey where codarq in (1010622, 1010623);
            delete from db_sysarqcamp where codarq in (1010622, 1010623);
            delete from db_syscampo where codcam in (1011841, 1011842, 1011843, 1011844, 1011845, 1011846);
            delete from db_sysarqmod where codarq in (1010622, 1010623);
            delete from db_sysarquivo where codarq in (1010622, 1010623);

SQL
        );
    }

    private function upDDL()
    {
        $this->execute(<<<SQL

            CREATE SEQUENCE caixa.receitaexterna_k191_sequencial_seq
                INCREMENT 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                START 1
                CACHE 1;

            CREATE TABLE caixa.receitaexterna(
                k191_sequencial integer NOT NULL default nextval('caixa.receitaexterna_k191_sequencial_seq'),
                k191_descricao  varchar(255) NOT NULL,
                k191_receitaexterna  integer NOT NULL,
                CONSTRAINT receitaexterna_sequ_pk PRIMARY KEY (k191_sequencial)
            );

            CREATE SEQUENCE caixa.receitaexternatabrec_k192_codigo_seq
                INCREMENT 1
                MINVALUE 1
                MAXVALUE 9223372036854775807
                START 1
                CACHE 1;

            CREATE TABLE caixa.receitaexternatabrec(
                k192_sequencial integer NOT NULL default nextval('caixa.receitaexternatabrec_k192_codigo_seq'),
                k192_receitaexterna integer NOT NULL,
                k192_receit integer NOT NULL,
                CONSTRAINT receitaexternatabrec_sequ_pk PRIMARY KEY (k192_sequencial)
            );

            ALTER TABLE caixa.receitaexternatabrec
                ADD CONSTRAINT receitaexternatabrec_receitaexterna_fk FOREIGN KEY (k192_receitaexterna)
                REFERENCES caixa.receitaexterna(k191_sequencial),
                ADD CONSTRAINT receitaexternatabrec_tabrec_fk FOREIGN KEY (k192_receit)
                REFERENCES caixa.tabrec(k02_codigo);

SQL
        );
    }

    private function downDDL()
    {
        $this->execute(<<<SQL

            DROP TABLE caixa.receitaexternatabrec;
            DROP TABLE caixa.receitaexterna;

            DROP SEQUENCE caixa.receitaexternatabrec_k192_codigo_seq;
            DROP SEQUENCE caixa.receitaexterna_k191_sequencial_seq;

SQL
        );
    }
}
