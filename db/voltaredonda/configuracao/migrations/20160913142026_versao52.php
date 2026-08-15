<?php

use Classes\PostgresMigration;

class Versao52 extends PostgresMigration
{
    public function up() {

        $pre = <<<'SQL_PRE'
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FOLHA ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

--
-- Ajustes da TAG 99760 adicionada ao path da release 50
update db_sysarquivo set nomearq = 'avaliacaogruporespostarhpessoal', descricao = 'Tabela que vincula uma resposta de uma pergunta do e-Social para o servidor.', sigla = 'eso02', dataincl = '2016-05-18', rotulo = 'Vincula uma resposta de avaliação a um servidor', tipotabela = 0, naolibclass = 'f', naolibfunc = 'f', naolibprog = 'f', naolibform = 'f' where codarq = 3924;
update db_syscampo set nomecam = 'eso02_rhpessoal', conteudo = 'int4', descricao = 'Ví­nculo com o cadastro de servidores', valorinicial = '0', rotulo = 'Matrícula', nulo = 'f', tamanho = 19, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Matrí­cula' where codcam = 21794;
delete from db_syscampodep where codcam = 21794;
delete from db_syscampodef where codcam = 21794;
delete from db_sysforkey where codarq = 3924 and codcam = 21794;
select fc_executa_ddl('insert into db_sysforkey values(3924,21794,1,1153,0);');
delete from db_syssequencia where codsequencia = 1000559;
select fc_executa_ddl('insert into db_syssequencia values(1000573, ''avaliacaogruporespostarhpessoal_eso02_sequencial_seq'', 1, 1, 9223372036854775807, 1, 1);');
select fc_executa_ddl('insert into db_sysindices values(4352,''avaliacaogruporespostarhpessoal_un_in'',3924,''1'');');
select fc_executa_ddl('insert into db_syscadind values(4352,21793,1);');
select fc_executa_ddl('insert into db_syscadind values(4352,21794,2);');
select fc_executa_ddl('insert into db_syscadind values(4351,21792,1);');
update db_sysarqcamp set codsequencia = 1000573 where codarq = 3924 and codcam = 21792;

update db_syscampo set nomecam = 'eso02_avaliacaogruporesposta', conteudo = 'int4', descricao = 'Vínculo com a resposta', valorinicial = '0', rotulo = 'Resposta', nulo = 'f', tamanho = 19, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Resposta' where codcam = 21793;
delete from db_syscampodep where codcam = 21793;
delete from db_syscampodef where codcam = 21793;
delete from db_sysforkey where codarq = 3924 and referen = 2986;
select fc_executa_ddl('insert into db_sysforkey values(3924,21793,1,2987,0);');


-------------------------------
-- Melhorias para release 52 --
-------------------------------
update db_itensmenu set descricao = 'Exportar' where id_item = 10235;
alter table db_formulas alter db148_nome type varchar(100);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
         VALUES (nextval('db_formulas_db148_sequencial_seq'), 'CODIGO_CGM', 'Retorna o código do CGM', '', true);
INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
                 VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_INSTITUICAO', 'Retorna a instituição atual', '', true);
INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
                 VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_MATRICULA_SERVIDOR', 'Retorna a matrícula do servidor que está respondendo ao questionário do eSocial', '', true);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
                 VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_PORTADOR_DEFICIENCIA', 'Retorna a opção se o servidor tem ou não deficiencia', 'select case rh02_deficientefisico::int when 1 then 3001095 else 3001096 end as portador_deficiencia from rhpessoalmov where rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO]) and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO]) and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000238);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEFICIENCIA', 'Retorna a opção para o campo de tipo de deficiência que o servidor possui', 'select case rh02_tipodeficiencia when 1 then 3001108 when 3 then 3001109 when 2 then 3001111 when 4 then 3001114 else null end as tipo_deficiencia from rhpessoalmov where rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO]) and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO]) and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000239);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_REGIME_PREVIDENCIARIO', 'Retorna a opção para o regime previdenciário, se inss ou próprio', 'select case rh02_tbprev when 0 then null when 1 then 3001073 else 3001074 end as regime_previdenciario from rhpessoalmov  where  rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO])  and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO])  and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000245);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_CODIGO_REGIME_PREVIDENCIARIO', 'Retorna a opção para o regime previdenciário, se inss ou próprio', 'select case rh02_tbprev when 0 then null when 1 then 3001051 else 3001052 end as regime_previdenciario from rhpessoalmov  where  rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO])  and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO])  and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000282);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_CARGO_SERVIDOR', 'Retorna o cargo do servidor', 'select rh37_descr as cargo from rhpessoalmov inner join rhfuncao on rh37_funcao = rh02_funcao and rh37_instit = rh02_instit where rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO]) and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO]) and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000284);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_FUNCAO_SERVIDOR', 'Retorna a funcao do servidor', 'select rh04_descr as funcao from rhpessoalmov inner join rhpescargo on rh20_seqpes = rh02_seqpes and rh20_instit = rh02_instit inner join rhcargo on rh04_codigo = rh20_cargo and rh04_instit = rh20_instit where rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO]) and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO]) and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000285);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_UNIDADE_PAGAMENTO', 'Retorna a opção da unidade de pagamento do servidor', 'select case rh02_tipsal when ''M'' then 3000956 when ''Q'' then 3000955 when ''D'' then 3000953 when ''H'' then 3000952 else null end as unidade_pagamento from rhpessoalmov where rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO]) and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO]) and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000283);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_REMUNERACAO', 'Retorna a remuneração do servidor', 'select f010 as remuneracao from fc_variaveis_matricula([ESOCIAL_MATRICULA_SERVIDOR], fc_anofolha([ESOCIAL_INSTITUICAO]), fc_mesfolha([ESOCIAL_INSTITUICAO]), [ESOCIAL_INSTITUICAO])', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000271);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_QTDE_HORAS_SEMANA', 'Retorna a quantidade de horas semanais do servidor', 'select rh02_hrssem as qtde_horas_semana from rhpessoalmov where rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO]) and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO]) and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000242);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_LOCAL_TRABALHO', 'Retorna onde o servidor está lotado (local de trabalho)', 'select rh55_descr as lotacao from rhpessoalmov inner join rhpeslocaltrab on rh56_seqpes = rh02_seqpes inner join rhlocaltrab on rh55_codigo = rh56_localtrab and rh55_instit = rh02_instit where rh02_anousu = fc_anofolha([ESOCIAL_INSTITUICAO]) and rh02_mesusu = fc_mesfolha([ESOCIAL_INSTITUICAO]) and rh02_regist = [ESOCIAL_MATRICULA_SERVIDOR] and rh56_princ is true', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000268);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_1', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 0 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000240);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_2', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 1 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000332);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_3', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 2 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000337);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_4', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 3 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000342);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_5', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 4 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000347);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_6', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 5 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000352);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_7', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 6 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000357);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_8', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 7 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000362);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_9', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 8 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000367);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_DEPENDENTE_10', 'Retorna o nome dos dependentes do servidor', 'select rh31_nome as nome from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 9 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000372);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_1', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 0 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000241);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_2', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 1 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000333);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_3', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 2 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000338);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_4', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 3 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000343);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_5', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 4 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000348);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_6', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 5 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000353);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_7', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 6 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000358);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_8', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 7 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000363);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_9', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 8 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000368);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_DEPENDENTE_10', 'Retorna a data de nascimento dos dependentes do servidor', 'select rh31_dtnasc as data_nascimento from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 9 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000373);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_1', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001079 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001081 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 0 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000233);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_2', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001121 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001120 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 1 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000330);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_3', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001138 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001137 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 2 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000335);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_4', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001155 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001154 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 3 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000340);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_5', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001172 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001171 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 4 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000345);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_6', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001189 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001188 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 5 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000350);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_7', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001206 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001205 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 6 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000355);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_8', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001223 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001222 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 7 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000360);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_9', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001240 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001239 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 8 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000365);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SALARIO_FAMILIA_IRRF_DEPENDENTE_10', 'Retorna se o dependente do servidor é considerado para o cálculo de IRRF e salário família', 'select * from (select unnest(regexp_split_to_array(rh31_depend_rh31_irf, '','')) as salario_familia_irrf_dependente from (select case when rh31_depend <> ''N'' then 3001257 else 0 end||'',''||case when rh31_irf <> ''0'' then 3001256 else 0 end as rh31_depend_rh31_irf from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 9 limit 1 ) as dados ) as salario_familia_irrf where salario_familia_irrf_dependente::int > 0', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000370);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_1', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001088'' when ''3'' then ''3001090'' when ''4'' then ''3001106'' when ''5'' then ''3001107'' when ''6'' then ''3001100'' when ''7'' then ''3001101'' when ''8'' then ''3001103'' else case when rh31_gparen = ''C'' then ''3001085'' when rh31_irf = ''1'' then ''3001086'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 0 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000235);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_2', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001131'' when ''3'' then ''3001130'' when ''4'' then ''3001123'' when ''5'' then ''3001122'' when ''6'' then ''3001127'' when ''7'' then ''3001126'' when ''8'' then ''3001125'' else case when rh31_gparen = ''C'' then ''3001133'' when rh31_irf = ''1'' then ''3001132'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 1 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000331);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_3', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001148'' when ''3'' then ''3001147'' when ''4'' then ''3001140'' when ''5'' then ''3001139'' when ''6'' then ''3001144'' when ''7'' then ''3001143'' when ''8'' then ''3001142'' else case when rh31_gparen = ''C'' then ''3001150'' when rh31_irf = ''1'' then ''3001149'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 2 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000336);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_4', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001165'' when ''3'' then ''3001164'' when ''4'' then ''3001157'' when ''5'' then ''3001156'' when ''6'' then ''3001161'' when ''7'' then ''3001160'' when ''8'' then ''3001159'' else case when rh31_gparen = ''C'' then ''3001167'' when rh31_irf = ''1'' then ''3001166'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 3 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000341);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_5', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001182'' when ''3'' then ''3001181'' when ''4'' then ''3001174'' when ''5'' then ''3001173'' when ''6'' then ''3001178'' when ''7'' then ''3001177'' when ''8'' then ''3001176'' else case when rh31_gparen = ''C'' then ''3001184'' when rh31_irf = ''1'' then ''3001183'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 4 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000346);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_6', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001199'' when ''3'' then ''3001198'' when ''4'' then ''3001191'' when ''5'' then ''3001190'' when ''6'' then ''3001195'' when ''7'' then ''3001194'' when ''8'' then ''3001193'' else case when rh31_gparen = ''C'' then ''3001201'' when rh31_irf = ''1'' then ''3001200'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 5 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000351);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_7', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001216'' when ''3'' then ''3001215'' when ''4'' then ''3001208'' when ''5'' then ''3001207'' when ''6'' then ''3001212'' when ''7'' then ''3001211'' when ''8'' then ''3001210'' else case when rh31_gparen = ''C'' then ''3001218'' when rh31_irf = ''1'' then ''3001217'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 6 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000356);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_8', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001233'' when ''3'' then ''3001232'' when ''4'' then ''3001225'' when ''5'' then ''3001224'' when ''6'' then ''3001229'' when ''7'' then ''3001228'' when ''8'' then ''3001227'' else case when rh31_gparen = ''C'' then ''3001235'' when rh31_irf = ''1'' then ''3001234'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 7 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000361);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_9', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001250'' when ''3'' then ''3001249'' when ''4'' then ''3001242'' when ''5'' then ''3001241'' when ''6'' then ''3001246'' when ''7'' then ''3001245'' when ''8'' then ''3001244'' else case when rh31_gparen = ''C'' then ''3001252'' when rh31_irf = ''1'' then ''3001251'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 8 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000366);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TIPO_DEPENDENTE_10', 'Retorna o tipo de dependente do servidor', 'select case rh31_irf when ''2'' then ''3001267'' when ''3'' then ''3001266'' when ''4'' then ''3001259'' when ''5'' then ''3001258'' when ''6'' then ''3001263'' when ''7'' then ''3001262'' when ''8'' then ''3001261'' else case when rh31_gparen = ''C'' then ''3001269'' when rh31_irf = ''1'' then ''3001268'' else null end end as tipo_dependente from rhdepend where (rh31_depend <> ''N'' or rh31_irf <> ''0'') and rh31_regist in (select rh01_regist from rhpessoal where rh01_numcgm = [CODIGO_CGM]) order by rh31_codigo offset 9 limit 1', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000371);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NOMEACAO', 'Data da nomeaçãao do servidor', 'select case (select fc_executa_ddl(''select fc_putsession(\\''plugins_dataassentamento_nomeacao\\'', (select nomeacao from plugins.dataassentamento where matricula = [ESOCIAL_MATRICULA_SERVIDOR])::varchar)'')) when true then (select fc_getsession(''plugins_dataassentamento_nomeacao'')) else null end datas_nomeacao_posse_exercicio', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000323);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_POSSE', 'Data da posse do servidor', 'select case (select fc_executa_ddl(''select fc_putsession(\\''plugins_dataassentamento_posse\\'', (select posse from plugins.dataassentamento where matricula = [ESOCIAL_MATRICULA_SERVIDOR])::varchar)'')) when true then (select fc_getsession(''plugins_dataassentamento_posse'')) else null end datas_nomeacao_posse_exercicio', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000328);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_EXERCICIO', 'Data da exercício do servidor', 'select case (select fc_executa_ddl(''select fc_putsession(\\''plugins_dataassentamento_exercicio\\'', (select exercicio from plugins.dataassentamento where matricula = [ESOCIAL_MATRICULA_SERVIDOR])::varchar)'')) when true then (select fc_getsession(''plugins_dataassentamento_exercicio'')) else null end datas_nomeacao_posse_exercicio', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000329);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_SERVIDOR', 'Retorna o nome do servidor', 'select z01_nome from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_NOME_SERVIDOR'), 3000226);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_CPF_SERVIDOR', 'Retorna o CPF do servidor', 'select z01_cgccpf from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_CPF_SERVIDOR'), 3000227);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_PIS_PASEP_SERVIDOR', 'Retorna o PIS/PASEP do servidor', 'select z01_pis from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_PIS_PASEP_SERVIDOR'), 3000228);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SEXO_SERVIDOR', 'Retorna o SEXO do servidor', 'select case when rh01_sexo=\'M\' then  3000923 else 3000922 end  as sexo from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_SEXO_SERVIDOR'), 3000229);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_RACA_COR_SERVIDOR', 'Retorna a raça/cor do servidor', 'select case rh01_raca when  1 then 3000928 when 2 then  3000924 when 4 then 3000925 when 6 then 3000927 when 8 then 3000926 else 3000929 end from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_RACA_COR_SERVIDOR'), 3000230);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ESTADO_CIVIL_SERVIDOR', 'Retorna o estado civil do servidor', 'select case rh01_estciv when  1 then 3000933 when 2 then 3000934 when 3 then 3000938 when 4 then 3000937 when 5 then 3000936 end from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ESTADO_CIVIL_SERVIDOR'), 3000253);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_GRAU_INSTRUCAO_SERVIDOR', 'Retorna o grau de instrucao do servidor',
        'select case rh01_instru when  1 then 3000948 when 2 then 3000959 when 3 then 3000960 when 4 then 3000961 when 5 then  3000962 when 6 then 3000965 when 7 then 3000967 when 8 then 3000971 when 9 then 3000973 when 10 then 3000976 when 11 then 3000977 end from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_GRAU_INSTRUCAO_SERVIDOR'), 3000259);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_NASCIMENTO_SERVIDOR', 'Retorna a data de nascimento do servidor',
        'select z01_nasc as data_nascimento from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_DATA_NASCIMENTO_SERVIDOR'), 3000287);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NATURALIDADE_SERVIDOR', 'Retorna a d do servidor',
        'select z01_naturalidade from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_NATURALIDADE_SERVIDOR'), 3000289);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_PAI_SERVIDOR', 'Retorna o nome do pai do servidor',
        'select z01_pai from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_NOME_PAI_SERVIDOR'), 3000296);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NOME_MAE_SERVIDOR', 'Retorna o nome do pai do servidor',
        'select z01_mae from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_NOME_MAE_SERVIDOR'), 3000294);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_IDENTIDADE_SERVIDOR', 'Retorna  a identidade do servidor',
        'select z01_ident from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_IDENTIDADE_SERVIDOR'), 3000314);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ORGAO_EMISSOR_IDENTIDADE_SERVIDOR', 'Retorna o orgao emissor da identidade do servidor',
        'select z01_identorgao from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ORGAO_EMISSOR_IDENTIDADE_SERVIDOR'), 3000317);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_DATA_EXPEDICAO_IDENTIDADE_SERVIDOR', 'Retorna a data de nascimento do servidor',
        'select z01_identdtexp from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_DATA_EXPEDICAO_IDENTIDADE_SERVIDOR'), 3000320);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NUMERO_CTPS_SERVIDOR', 'Retorna o número da CTPS do servidor',
        'select rh16_ctps_n from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm inner join rhpesdoc on rh16_regist = rh01_regist where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_NUMERO_CTPS_SERVIDOR'), 3000299);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_SERIE_CTPS_SERVIDOR', 'Retorna a serie da CTPS do servidor',
        'select rh16_ctps_s from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm inner join rhpesdoc on rh16_regist = rh01_regist where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_SERIE_CTPS_SERVIDOR'), 3000300);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_UF_CTPS_SERVIDOR', 'Retorna a serie da CTPS do servidor',
        'select rh16_ctps_uf from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm inner join rhpesdoc on rh16_regist = rh01_regist where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_UF_CTPS_SERVIDOR'), 3000305);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_NUMERO_CNH_SERVIDOR', 'Retorna o numero da CNH do servidor',
        'select rh16_carth_n from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm inner join rhpesdoc on rh16_regist = rh01_regist where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_NUMERO_CNH_SERVIDOR'), 3000298);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_VALIDADE_CNH_SERVIDOR', 'Retorna a validade da CNH do servidor',
        'select rh16_carth_val as rh16_carth_val from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm inner join rhpesdoc on rh16_regist = rh01_regist where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_VALIDADE_CNH_SERVIDOR'), 3000281);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_CATEGORIA_CNH_SERVIDOR', 'Retorna a categoria da CNH do servidor',
        'select r16_carth_cat from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm inner join rhpesdoc on rh16_regist = rh01_regist where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_CATEGORIA_CNH_SERVIDOR'), 3000291);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ENDERECO_LOGRADOURO_SERVIDOR', 'Retorna o logradouro do servidor',
        'select z01_ender from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ENDERECO_LOGRADOURO_SERVIDOR'), 3000273);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ENDERECO_NUMERO_SERVIDOR', 'Retorna o logradouro do servidor',
        'select z01_numero from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ENDERECO_NUMERO_SERVIDOR'), 3000276);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ENDERECO_COMPLEMENTO_SERVIDOR', 'Retorna o logradouro do servidor',
        'select z01_compl from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ENDERECO_COMPLEMENTO_SERVIDOR'), 3000263);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ENDERECO_BAIRRO_SERVIDOR', 'Retorna o bairro do servidor',
        'select z01_bairro from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ENDERECO_BAIRRO_SERVIDOR'), 3000270);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ENDERECO_MUNICIPIO_SERVIDOR', 'Retorna o municipio do servidor',
        'select z01_munic from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ENDERECO_MUNICIPIO_SERVIDOR'), 3000249);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ENDERECO_CEP_SERVIDOR', 'Retorna o CEP do endereco do servidor',
        'select z01_cep from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ENDERECO_CEP_SERVIDOR'), 3000251);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ENDERECO_UF_SERVIDOR', 'Retorna a UF do endereco do servidor',
        'select z01_uf from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ENDERECO_UF_SERVIDOR'), 3000258);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_ENDERECO_PAIS_SERVIDOR', 'Retorna a UF do endereco do servidor',
        'select db70_descricao
           from cgm
                inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm
                inner join cgmendereco on z07_numcgm = z01_numcgm
                inner join endereco on z07_endereco = db76_sequencial
                inner join cadenderlocal on db76_cadenderlocal = db75_sequencial
                inner join cadenderbairrocadenderrua on db75_cadenderbairrocadenderrua = db87_sequencial
                inner join cadenderbairro            on db87_cadenderbairro = db73_sequencial
                inner join cadendermunicipio         on db73_cadendermunicipio = db72_sequencial
                inner join cadenderestado            on db72_cadenderestado    = db71_sequencial
                inner join cadenderpais              on db71_cadenderpais      = db70_sequencial
          where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_ENDERECO_PAIS_SERVIDOR'), 3000247);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_FILIACAO_SINDICAL_SERVIDOR', 'Retorna a cpnj do sindicato do servidor',
        'select rh116_cnpj from rhpessoal inner join rhsindicato on rh01_rhsindicato = rh116_sequencial where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_FILIACAO_SINDICAL_SERVIDOR'), 3000234);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TELEFONE_RESIDENCIAL_SERVIDOR', 'Retorna o telefone residencial do servidor',
        'select z01_telef from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_TELEFONE_RESIDENCIAL_SERVIDOR'), 3000272);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_TELEFONE_CELULAR_SERVIDOR', 'Retorna o telefone celular do servidor',
        'select z01_telcel from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_TELEFONE_CELULAR_SERVIDOR'), 3000274);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_EMAIL_SERVIDOR', 'Retorna o telefone celular do servidor',
        'select z01_email from cgm inner join rhpessoal on rhpessoal.rh01_numcgm = cgm.z01_numcgm where rh01_regist = [ESOCIAL_MATRICULA_SERVIDOR]', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_EMAIL_SERVIDOR'), 3000261);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_MATRICULA_CGM_SERVIDOR', 'Retorna a matricula do sindicato do servidor',
        'select [ESOCIAL_MATRICULA_SERVIDOR] as matricula', false);

INSERT INTO avaliacaoperguntadb_formulas (eso01_sequencial, eso01_db_formulas, eso01_avaliacaopergunta)
VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), (select db148_sequencial from db_formulas where db148_nome = 'ESOCIAL_MATRICULA_CGM_SERVIDOR'), 3000277);

--Ajuste no tipo de dados da pergunta Qualidade do Depentende no grupo de dependentes
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000047, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_1', db103_tipo=6, db103_mascara='' where db103_sequencial=3000233;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000048, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_2', db103_tipo=6, db103_mascara='' where db103_sequencial=3000330;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000049, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_3', db103_tipo=6, db103_mascara='' where db103_sequencial=3000335;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000050, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_4',  db103_tipo=6, db103_mascara='' where db103_sequencial=3000340;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000051, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_5',  db103_tipo=6, db103_mascara='' where db103_sequencial=3000345;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000052, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_6',  db103_tipo=6, db103_mascara='' where db103_sequencial=3000350;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000053, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_7',  db103_tipo=6, db103_mascara='' where db103_sequencial=3000355;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000054, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_8',  db103_tipo=6, db103_mascara='' where db103_sequencial=3000360;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000055, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_9',  db103_tipo=6, db103_mascara='' where db103_sequencial=3000365;
UPDATE avaliacaopergunta SET db103_avaliacaotiporesposta=3, db103_avaliacaogrupopergunta=3000056, db103_descricao='Qualidade do Dependente:', db103_obrigatoria=true, db103_ativo=true, db103_ordem=1, db103_identificador='qualidade_dependente_10', db103_tipo=6, db103_mascara='' where db103_sequencial=3000370;

--Menus para formulário de preenchimento das informações do empregador
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10244 ,'Informações do Empregador' ,'Informações do Empregador para o eSocial' ,'eso01_preenchimentoempregador.php' ,'1' ,'1' ,'Formulário para preenchimento das informações que serão enviadas ao eSocial referente ao estabelecimento empregador.' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 29 ,10244 ,270 ,10216 );

--Tabela de vínculo entre formulário de preenchimento das informações do empregador e cgm
insert into db_sysarquivo values (3943, 'avaliacaogruporespostacgm', 'Vinculo entre uma resposta de um conjunto de respostas do eSocial com um cgm para informações do estabelecimento do empregador.', '', '2016-05-24', 'Vinculo entre eSocial e cgm', 0, 'f', 'f', 't', 't' );
insert into db_sysarqmod values (81,3943);
update db_sysarquivo set nomearq = 'avaliacaogruporespostacgm', descricao = 'Vinculo entre uma resposta de um conjunto de respostas do eSocial com um cgm para informações do estabelecimento do empregador.', sigla = 'eso03', dataincl = '2016-05-24', rotulo = 'Vinculo entre eSocial e cgm', tipotabela = 0, naolibclass = 'f', naolibfunc = 'f', naolibprog = 't', naolibform = 't' where codarq = 3943;
insert into db_sysarqarq values(0,3943);

insert into db_syscampo values(21904,'eso03_sequencial','int4','Código do vínculo entre cgm e um conjunto de respostas do formulário do eSocial.','0', 'Código',19,'f','f','f',1,'text','Código');
insert into db_syscampo values(21905,'eso03_avaliacaogruporesposta','int4','Grupo de resposta que será vinculado.','0', 'Grupo Resposta',19,'f','f','f',1,'text','Grupo Resposta');
insert into db_syscampo values(21906,'eso03_cgm','int4','Cgm que respondeu o formulário.','0', 'CGM',19,'f','f','f',1,'text','CGM');

insert into db_sysarqcamp values(3943,21904,1,0);
insert into db_sysarqcamp values(3943,21905,2,0);
insert into db_sysarqcamp values(3943,21906,3,0);

insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3943,21904,1,21904);
insert into db_sysforkey values(3943,21905,1,2987,0);
insert into db_sysforkey values(3943,21906,1,42,0);

insert into db_syssequencia values(1000578, 'avaliacaogruporespostacgm_eso03_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000578 where codarq = 3943 and codcam = 21904;

ALTER TABLE avaliacaogrupopergunta ALTER COLUMN db102_descricao TYPE varchar(100);

--INSERE perguntas do empregador para o eSocial
INSERT INTO avaliacao VALUES (3000009, 5, 'e-Social S1000', '', true, 'e-Social S1000');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000075, 3000009, 'Informações do Empregador', 'infoCadastro');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000076, 3000009, 'Informações Fator Acidentário de Prevenção - FAP', 'infoFap');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000077, 3000009, 'Processo Adm/judicial FAP', 'procAdmJudFap');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000078, 3000009, 'Informações Complementares - Empresas Isentas', 'dadosIsencao');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000079, 3000009, 'Informações de Contato', 'contato');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000080, 3000009, 'Informações Organismos Internacionais e Instituições Extraterritoriais', 'infoOrgInternacional');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000081, 3000009, 'Informações da Empresa de Software', 'softwareHouse');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000083, 3000009, 'Informações Complementares', 'situacaoPJ');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000085, 3000009, 'Informações Relativa a Órgãos de Regime Próprio de Previdência Social - RPPS', 'infoRPPS');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000086, 3000009, 'Informações das Alíquotas do Ente Federativo', 'aliqEnteFed');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000088, 3000009, 'Informações dos Limites Remuneratórios do Ente Federativo', 'limitesRem');
insert into avaliacaogrupopergunta (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) values  (3000090, 3000009, 'Informações dos Entes Federativos com Regime Próprio de Previdência Social - RPPS', 'infEnteFed');

insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000376, 1, 3000075, 'Natureza Jurídica', false, true, 2, 'nat_juridica_empregador', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000380, 1, 3000075, 'Indicativo de Opção de Registro Eletrônico de Empregados', true, true, 3, 'ind_reg_eletronico_empregador', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000381, 1, 3000075, 'Utiliza mais de uma Tabela de Rubricas', false, true, 4, 'multiplas_tab_rubricasempregador', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000382, 2, 3000075, 'Número do SIAFI', false, true, 6, 'nro_siafi_empregador', 6, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000383, 2, 3000076, 'Fator Acidentário de Prevenção', true, true, 1, 'fap', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000384, 1, 3000077, 'Tipo do Processo Administrativo', true, true, 2, 'tipo_processo_administrativo_fap', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000385, 2, 3000077, 'Número do Processo Administrativo', true, true, 3, 'numero_processo_administrativo_fap', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000386, 1, 3000078, 'Identificação do Ministério/Lei que Concedeu o Certificado', true, true, 1, 'dados_isensao_sigla_ministerio', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000387, 2, 3000078, 'Número do Certificado/Portaria/Lei', false, true, 2, 'numero_certificado_lei_isencao', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000388, 2, 3000078, 'Data de Emissão do Certificado', true, true, 3, 'data_emissao_certificado_isencao', 5, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000389, 2, 3000078, 'Data de Vencimento do Certificado', true, true, 4, 'data_vencimento_certificado_isencao', 5, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000390, 2, 3000078, 'Número do Protocolo do Pedido de Renovação', false, true, 5, 'numero_protocolo_pedido_renovacao', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000391, 2, 3000078, 'Data do Protocolo de Renovação', false, true, 6, 'data_pedido_protocolo_renovacao', 5, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000392, 2, 3000078, 'Data da Publicação no Diário Oficial', false, true, 7, 'data_publicacao_isencao_dou', 5, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000393, 2, 3000078, 'Número da Página no Diário Oficial da União', false, true, 8, 'paginas_dou', 6, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000394, 2, 3000079, 'Nome do Contato', true, true, 1, 'nome_contato_empresa', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000395, 2, 3000079, 'CPF do Contato', true, true, 2, 'cpf_contato_empresa', 4, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000396, 2, 3000079, 'Telefone', false, true, 3, 'esocial_empregador_telefone_fixo', 7, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000397, 2, 3000079, 'Celular', false, true, 4, 'esocial_empregador_telefone_celular', 7, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000398, 2, 3000079, 'Email', false, true, 5, 'esocial_empregador_email', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000399, 1, 3000080, 'Acordo Internacional', true, true, 1, 'esocial_empregador_acordo_internacional', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000400, 2, 3000081, 'CNPJ Empresa', true, true, 1, 'cnpj_software_house', 3, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000401, 1, 3000085, 'Possui RPPS', true, true, 1, 'possui_rpps', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000402, 2, 3000090, 'UF', true, true, 1, 'uf_unidade_federativa', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000403, 2, 3000090, 'Código Município', false, true, 2, 'codigo_municipio', 6, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000404, 1, 3000086, 'Público Alvo da Alíquota', true, true, 1, 'publico_aliquota_aplicada', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000405, 2, 3000086, 'Informações Referente a Lei', false, true, 2, 'informacoes_lei_rpps', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000406, 2, 3000086, 'Percentual Segurado ou Beneficiário', false, true, 3, 'percentual_segurado_beneficiario_rpps', 8, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000407, 2, 3000086, 'Percentual Normal do Ente Federativo do RPPS', false, true, 4, 'percentual_normal_ente_federativo', 8, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000408, 2, 3000086, 'Percentual Alíquota da Contribuição Suplementar', true, true, 5, 'percentual_suplementar_ente_federativo_rpps', 8, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000409, 1, 3000088, 'Poder Referente do Subteto', true, true, 1, 'poder_limite_remuneratorio_ente', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000410, 2, 3000088, 'Valor do Subteto do Ente', true, true, 2, 'valor_limite_remunetaratorio_subteto_ente', 8, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000411, 2, 3000088, 'Idade Correspondente a Maioridade', false, true, 5, 'maioridade_anos_dependente_ente', 6, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000412, 1, 3000083, 'Indicação da Situação da Pessoa Jurídica', false, true, 1, 'situacaoPJ_pergunta', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000413, 1, 3000083, 'Indicação da Situação da Pessoa Física', false, true, 2, 'situacaoPF_pergunta', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000414, 2, 3000081, 'Nome/Razão Social', true, true, 1, 'nome_software_house', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000415, 2, 3000081, 'Contato', true, true, 2, 'contato_software_house', 1, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000416, 2, 3000081, 'Telefone', true, true, 3, 'telefone_software_house', 7, '');
insert into avaliacaopergunta (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao,db103_obrigatoria, db103_ativo,  db103_ordem, db103_identificador, db103_tipo, db103_mascara) values  (3000417, 2, 3000081, 'E-mail', true, true, 4, 'email_software_house', 1, '');

insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001275, 3000376, '101-5 - Órgão Público do Poder Executivo Federal', false, 'nat_juridica_empregador_101_5', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001276, 3000376, '102-3 - Órgão Público do Poder Executivo Estadual ou do Distrito Federal', false, 'nat_juridica_empregador_102_3', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001277, 3000376, '103-1 - Órgão Público do Poder Executivo Municipal', false, 'nat_juridica_empregador_103_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001278, 3000376, '104-0 - Órgão Público do Poder Legislativo Federal', false, 'nat_juridica_empregador_104_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001279, 3000376, '105-8 - Órgão Público do Poder Legislativo Estadual ou do Distrito Federal', false, 'nat_juridica_empregador_105_8', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001280, 3000376, '106-6 - Órgão Público do Poder Legislativo Municipal', false, 'nat_juridica_empregador_106_6', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001281, 3000376, '107-4 - Órgão Público do Poder Judiciário Federal', false, 'nat_juridica_empregador_107_4', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001282, 3000376, '108-2 - Órgão Público do Poder Judiciário Estadual', false, 'nat_juridica_empregador_108_2', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001283, 3000376, '110-4 - Autarquia Federal', false, 'nat_juridica_empregador_110_4', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001284, 3000376, '111-2 - Autarquia Estadual ou do Distrito Federal', false, 'nat_juridica_empregador_111_2', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001285, 3000376, '112-0 - Autarquia Municipal', false, 'nat_juridica_empregador_112_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001286, 3000376, '113-9 - Fundação Pública de Direito Público Federal', false, 'nat_juridica_empregador_113_9', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001287, 3000376, '114-7 - Fundação Pública de Direito Público Estadual ou do Distrito Federal', false, 'nat_juridica_empregador_114_7', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001288, 3000376, '115-5 - Fundação Pública de Direito Público Municipal', false, 'nat_juridica_empregador_115_5', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001289, 3000376, '116-3 - Órgão Público Autônomo Federal', false, 'nat_juridica_empregador_116_3', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001290, 3000376, '117-1 - Órgão Público Autônomo Estadual ou do Distrito Federal', false, 'nat_juridica_empregador_117_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001291, 3000376, '118-0 - Órgão Público Autônomo Municipal', false, 'nat_juridica_empregador_118_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001292, 3000376, '119-8 - Comissão Polinacional', false, 'nat_juridica_empregador_119_8', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001293, 3000376, '120-1 - Fundo Público', false, 'nat_juridica_empregador_120_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001294, 3000376, '121-0 - Consórcio Público de Direito Público (Associação Pública)', false, 'nat_juridica_empregador_121_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001295, 3000376, '122-8 - Consórcio Público de Direito Privado', false, 'nat_juridica_empregador_122_8', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001296, 3000376, '123-6 - Estado ou Distrito Federal', false, 'nat_juridica_empregador_123_6', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001297, 3000376, '124-4 - Município', false, 'nat_juridica_empregador_124_4', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001298, 3000376, '125-2 - Fundação Pública de Direito Privado Federal', false, 'nat_juridica_empregador_125_2', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001299, 3000376, '126-0 - Fundação Pública de Direito Privado Estadual ou do Distrito Federal', false, 'nat_juridica_empregador_126_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001300, 3000376, '127-9 - Fundação Pública de Direito Privado Municipal', false, 'nat_juridica_empregador_127_9', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001309, 3000380, 'Não optou pelo registro eletrônico de empregados.', false, 'ind_reg_eletronico_empregador_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001310, 3000380, 'Optou pelo registro eletrônico de empregados.', false, 'ind_reg_eletronico_empregador_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001311, 3000381, 'Sim', false, 'multiplas_tab_rubricasempregador_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001312, 3000381, 'Não', false, 'multiplas_tab_rubricasempregador_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001313, 3000382, 'null', false, 'nro_siafi_empregador_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001314, 3000383, 'null', false, 'fap_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001315, 3000384, 'Administrativo', false, 'tipo_pocesso_fap_administrativo', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001316, 3000384, 'Judicial', false, 'tipo_pocesso_fap_judicial', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001317, 3000385, null, true, 'numero_processo_administrativo_fap_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001318, 3000387, null, true, 'numero_certificado_lei_isencao_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001319, 3000388, null, true, 'data_emissao_certificado_isencao_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001320, 3000389, null, true, 'data_vencimento_certificado_isencao_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001321, 3000390, null, true, 'numero_protocolo_pedido_renovacao_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001322, 3000391, null, true, 'data_pedido_protocolo_renovacao_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001323, 3000392, null, true, 'data_publicacao_isencao_dou_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001324, 3000386, 'CNAS - Conselho Nacional de Assistência Social', false, 'identificacao_ministerio_certificado_cnas', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001325, 3000386, 'MEC - Ministério da Educação', false, 'identificacao_ministerio_certificado_mec', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001326, 3000386, 'MS - Ministério da Saúde', false, 'identificacao_ministerio_certificado_ms', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001327, 3000386, 'MDS - Ministério do Desenvolvimento Social e Combate à Fome', false, 'identificacao_ministerio_certificado_mds', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001328, 3000386, 'LEI - Lei Específica.', false, 'identificacao_ministerio_certificado_lei', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001329, 3000393, null, true, 'paginas_dou', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001330, 3000394, null, true, 'nome_contato_empresa', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001331, 3000395, null, true, 'cpf_contato_empresa', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001332, 3000396, null, true, 'telefone_fixo', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001333, 3000397, null, true, 'telefone_celular', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001334, 3000398, null, true, 'email', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001335, 3000399, 'Sem Acordo', false, 'acordo_internacional_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001336, 3000399, 'Com Acordo', false, 'acordo_internacional_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001337, 3000400, null, true, 'cnpj_software_house', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001338, 3000408, null, true, 'percentual_suplementar_ente_rpps_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001339, 3000409, 'Executivo', false, 'poder_limite_remuneratorio_executivo', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001340, 3000409, 'Legislativo', false, 'poder_limite_remuneratorio_legislativo', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001341, 3000409, 'Judiciário', false, 'poder_limite_remuneratorio_judiciario', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001342, 3000409, 'Todos os poderes', false, 'poder_limite_remuneratorio_todos', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001343, 3000410, null, true, 'valor_limite_remuneratorio_subteto_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001344, 3000411, null, true, 'maioridade_anos_dependente_ente_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001345, 3000402, null, true, 'uf_unidade_federativa_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001346, 3000403, null, true, 'codigo_municipio_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001347, 3000401, 'Sim', false, 'possui_rpps_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001348, 3000401, 'Não', false, 'possui_rpps_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001349, 3000404, 'Servidor ativo', false, 'publico_aliquota_aplicada_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001350, 3000404, 'Aposentado', false, 'publico_aliquota_aplicada_2', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001351, 3000404, 'Aposentado por invalidez', false, 'publico_aliquota_aplicada_3', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001352, 3000404, 'Pensionista', false, 'publico_aliquota_aplicada_4', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001353, 3000404, 'Militar ativo (e o reformado)', false, 'publico_aliquota_aplicada_5', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001354, 3000404, 'Segurado diferenciado', false, 'publico_aliquota_aplicada_6', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001355, 3000405, null, true, 'informacoes_lei_rpps_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001356, 3000406, null, true, 'percentual_segurado_beneficiario_rpps_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001357, 3000407, null, true, 'percentual_normal_ente_federativo_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001358, 3000413, 'Normal', false, 'indSitPF_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001359, 3000413, 'Encerramento de espólio', false, 'indSitPF_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001360, 3000414, null, false, 'nome_software_house_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001361, 3000415, null, false, 'contato_software_house_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001362, 3000416, null, false, 'telefone_software_house_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001363, 3000417, null, false, 'email_software_house_resposta', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001364, 3000413, 'Saída do país em caráter permanente', false, 'indSitPF_2', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001365, 3000412, 'Normal', false, 'indSitPJ_0', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001366, 3000412, 'Extinção', false, 'indSitPJ_1', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001367, 3000412, 'Fusão', false, 'indSitPJ_2', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001368, 3000412, 'Cisão', false, 'indSitPJ_3', 0);
insert into avaliacaoperguntaopcao (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto,db104_identificador, db104_peso) values  (3001369, 3000412, 'Incorporação', false, 'indSitPJ_4', 0);

INSERT INTO db_formulas (db148_sequencial, db148_nome, db148_descricao, db148_formula, db148_ambiente)
     VALUES (nextval('db_formulas_db148_sequencial_seq'), 'ESOCIAL_EMPREGADOR_FAP', 'Fator Acidentário de Prevenção', 'select r11_peactr from cfpess where r11_anousu = (select max(r11_anousu) from cfpess where r11_instit = [CODIGO_CGM]) and r11_mesusu = (select max(r11_mesusu) from cfpess where r11_instit = [CODIGO_CGM] and r11_anousu = (select max(r11_anousu) from cfpess where r11_instit = [CODIGO_CGM]))', false);
INSERT INTO avaliacaoperguntadb_formulas VALUES (nextval('avaliacaoperguntadb_formulas_eso01_sequencial_seq'), currval('db_formulas_db148_sequencial_seq'), 3000383);
----------------------------------------------------- FIM FOLHA -----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------


---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO SAÚDE ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
insert into db_sysarquivo values (3937, 'cgs_unddocumento', 'Documentos do Cadastro de CGS', 'sd108', '2016-05-17', 'Documentos', 0, 'f', 't', 't', 't' );
insert into db_sysarquivo values (3938, 'cgs_undendereco', 'Dados dos endereços vinculados ao CGS', 'sd109', '2016-05-17', 'Endereços do CGS', 0, 'f', 't', 't', 't' );

insert into db_sysarqmod values (1000004,3937);
insert into db_sysarqmod values (1000004,3938);

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel )
  values
  ( 21871 ,'sd109_sequencial' ,'int4' ,'Identificador da ligação' ,'' ,'Código' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código' ),
  ( 21872 ,'sd109_endereco' ,'int4' ,'Código do Endereço' ,'' ,'Código do Endereço' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código do Endereço' ),
  ( 21873 ,'sd109_cgs_und' ,'int4' ,'Cadastro Geral Saúde.' ,'' ,'CGS' ,7 ,'false' ,'false' ,'false' ,1 ,'text' ,'CGS' ),
  ( 21874 ,'sd108_sequencial' ,'int4' ,'Identificador da Relação do DOcumento com o CGS' ,'' ,'Sequencial' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Sequencial' ),
  ( 21875 ,'sd108_cgs_und' ,'int4' ,'Cadastro Geral Saúde.' ,'' ,'CGS' ,7 ,'false' ,'false' ,'false' ,1 ,'text' ,'CGS' ),
  ( 21876 ,'sd108_documento'  ,'int4' ,'Sequencial.' ,'' ,'Código Documento' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código Documento' );

insert into db_sysprikey (codarq,codcam,sequen,camiden)
  values
  (3938,21871,1,21872),
  (3937,21874,1,21874);

insert into db_sysindices
  values
  (4354,'cgs_undendereco_endereco_in',3938,'0'),
  (4355,'cgs_undendereco_cgs_und_in',3938,'0'),
  (4356,'cgs_unddocumento_documento_in',3937,'0'),
  (4357,'cgs_unddocumento_cgs_und_in',3937,'0');

insert into db_sysforkey
  values
  (3938,21872,1,2786,0),
  (3938,21873,1,1010144,0),
  (3937,21876,1,2920,0),
  (3937,21875,1,1010144,0);

insert into db_syscadind
  values
  (4354,21872,1),
  (4355,21873,1),
  (4356,21876,1),
  (4357,21875,1);


insert into db_syscampodep ( codcam ,codcampai )
  values
  ( 21872 ,15869 ),
  ( 21873 ,1008844 ),
  ( 21875 ,1008844 ),
  ( 21876 ,15676 );

insert into db_syssequencia
  values
  (1000571, 'cgs_unddocumento_sd108_sequencial_seq', 1, 1, 9223372036854775807, 1, 1),
  (1000572, 'cgs_undendereco_sd109_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);

insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia )
  values
  ( 3938 ,21871 ,1 ,1000572),
  ( 3938 ,21872 ,2 ,0 ),
  ( 3938 ,21873 ,3 ,0 ),
  ( 3937 ,21874 ,1 ,1000571),
  ( 3937 ,21875 ,2 ,0 ),
  ( 3937 ,21876 ,3 ,0 );

insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente )
  values
  ( 10239 ,'Manutenção do CGS' ,'Manutenção do CGS' ,'sau1_manutencaocgs001.php' ,'1' ,'1' ,'Efetua manutenção do Cadastro geral da Saude' ,true);


delete from db_menu where id_item_filho = 1045399;

insert into db_menu(id_item ,id_item_filho ,menusequencia ,modulo )
  values
  (3470 ,10239 ,36,    6952),
  (3470 ,10239 ,37, 1000004),
  (3470 ,10239 ,38,    6877),
  (9049 ,10239 , 3,    9053),
  (8170 ,10239 ,17,    8167),
  (8323 ,10239 ,13,    8322),
  (8482 ,10239 , 4,    8481);

insert into cadtipodocumento values( 3, 'CGS' );
insert into caddocumento( db44_sequencial ,db44_descricao ,db44_cadtipodocumento ) values ( 3000000 ,'GERAIS' ,3 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 4 ,3000000 ,NULL ,'PIS/PASEP' ,'' ,1 ,11 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 5 ,3000000 ,NULL ,'DATA ENTRADA' ,'' ,3 ,10 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 29 ,3000000 ,NULL ,'CPF' ,'' ,1 ,14 );
insert into caddocumento( db44_sequencial ,db44_descricao ,db44_cadtipodocumento ) values ( 3000001 ,'IDENTIDADE' ,3 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 7 ,3000001 ,NULL ,'NÚMERO' ,'' ,1 ,20 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 8 ,3000001 ,NULL ,'DATA DE EMISSÃO' ,'' ,3 ,10 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 9 ,3000001 ,NULL ,'ÓRGÃO EMISSOR' ,'' ,1 ,100 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 10 ,3000001 ,NULL ,'UF' ,'' ,1 ,10 );
insert into caddocumento( db44_sequencial ,db44_descricao ,db44_cadtipodocumento ) values ( 3000002 ,'CTPS' ,3 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 11 ,3000002 ,NULL ,'NÚMERO' ,'' ,1 ,5 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 12 ,3000002 ,NULL ,'SÉRIE' ,'' ,1 ,20 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 13 ,3000002 ,NULL ,'DATA DE EMISSÃO' ,'' ,3 ,10 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 14 ,3000002 ,NULL ,'UF' ,'' ,1 ,5 );
insert into caddocumento( db44_sequencial ,db44_descricao ,db44_cadtipodocumento ) values ( 3000003 ,'CERTIDÃO' ,3 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 15 ,3000003 ,NULL ,'TIPO DE CERTIDÃO' ,'' ,1 ,1 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 16 ,3000003 ,NULL ,'LIVRO' ,'' ,1 ,20 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 17 ,3000003 ,NULL ,'TERMO' ,'' ,1 ,40 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 18 ,3000003 ,NULL ,'CARTÓRIO' ,'' ,1 ,30 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 19 ,3000003 ,NULL ,'FOLHA' ,'' ,1 ,20 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 20 ,3000003 ,NULL ,'DATA DA EMISSÃO' ,'' ,3 ,10 );
insert into caddocumento( db44_sequencial ,db44_descricao ,db44_cadtipodocumento ) values ( 3000004 ,'CNH' ,3 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 21 ,3000004 ,NULL ,'NÚMERO' ,'' ,1 ,20 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 22 ,3000004 ,NULL ,'CATEGORIA' ,'' ,1 ,2 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 23 ,3000004 ,NULL ,'DATA DE EMISSÃO' ,'' ,3 ,10 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 24 ,3000004 ,NULL ,'DATA HABILITAÇÃO' ,'' ,3 ,10 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 25 ,3000004 ,NULL ,'DATA DE VENCIMENTO' ,'' ,3 ,10 );
insert into caddocumento( db44_sequencial ,db44_descricao ,db44_cadtipodocumento ) values ( 3000005 ,'DADOS BANCÁRIOS' ,3 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 26 ,3000005 ,NULL ,'BANCO' ,'' ,1 ,40 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 27 ,3000005 ,NULL ,'AGÊNCIA' ,'' ,1 ,10 );
insert into caddocumentoatributo( db45_sequencial ,db45_caddocumento ,db45_codcam ,db45_descricao ,db45_valordefault ,db45_tipo ,db45_tamanho ) values ( 28 ,3000005 ,NULL ,'CONTA' ,'' ,1 ,10 );

insert into db_syscampo values(21901,'z01_orgaoemissoridentidade','varchar(100)','Órgão Emissor da Identidade','', 'Órgão Emissor da Identidade',100,'t','t','f',0,'text','Órgão Emissor da Identidade');
insert into db_sysarqcamp values(1010144,21901,79,0);

update db_syscampo set nomecam = 'z01_v_cxpostal', conteudo = 'varchar(50)', descricao = 'Caixa Postal', valorinicial = '', rotulo = 'Caixa Postal', nulo = 't', tamanho = 50, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Caixa Postal' where codcam = 11209;
update db_syscampo set nomecam = 'z33_v_nome', conteudo = 'varchar(255)', descricao = 'Nome', valorinicial = '', rotulo = 'Nome', nulo = 'f', tamanho = 255, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Nome' where codcam = 1008966;
----------------------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------------- INICIO FINANCEIRO ------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------------------------
insert into db_sysarquivo values (3939, 'classificacaocredoreselemento', 'Tabela de vínculo com o plano orçamentário.', 'cc32', '2016-05-18', 'classificacaocredoreselemento', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (38,3939);
insert into db_syscampo values(21877,'cc32_sequencial','int4','Código','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21878,'cc32_classificacaocredores','int4','Classificação de Credores','0', 'Classificação de Credores',10,'f','f','f',1,'text','Classificação de Credores');
insert into db_syscampo values(21879,'cc32_codcon','int4','Conta','0', 'Conta',10,'f','f','f',1,'text','Conta');
insert into db_syscampo values(21883,'cc32_anousu','int4','Conta','0', 'Conta',10,'f','f','f',1,'text','Conta');
insert into db_syscampo values(21885,'cc32_exclusao','bool','Exclusão','','Exclusão',1,'false','false','false',5,'text','Exclusão');
insert into db_sysarqcamp values(3939,21877,1,0);
insert into db_sysarqcamp values(3939,21878,2,0);
insert into db_sysarqcamp values(3939,21879,3,0);
insert into db_sysarqcamp values(3939,21883,4,0);
insert into db_sysarqcamp values(3939,21885,5,0);
insert into db_syssequencia values(1000574, 'classificacaocredoreselemento_cc32_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000574 where codarq = 3939 and codcam = 21877;
delete from db_sysprikey where codarq = 3939;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3939,21877,1,21878);
delete from db_sysforkey where codarq = 3939 and referen = 0;
insert into db_sysforkey values(3939,21878,1,3878,0);
insert into db_sysforkey values(3939,21879,1,3268,0);
insert into db_sysforkey values(3939,21883,2,3268,0);


insert into db_sysarquivo values (3940, 'classificacaocredoresrecurso', 'Tabela de vínculo com o recurso.', 'cc33', '2016-05-18', 'classificacaocredoresrecurso', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (38,3940);
insert into db_syscampo values(21886,'cc33_sequencial','int4','Código','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21887,'cc33_classificacaocredores','int4','Classificação de Credores','0', 'Classificação de Credores',10,'f','f','f',1,'text','Classificação de Credores');
insert into db_syscampo values(21888,'cc33_orctiporec','int4','Recurso','0', 'Recurso',10,'f','f','f',1,'text','Recurso');
insert into db_sysarqcamp values(3940,21886,1,0);
insert into db_sysarqcamp values(3940,21887,2,0);
insert into db_sysarqcamp values(3940,21888,3,0);
insert into db_syssequencia values(1000575, 'classificacaocredoresrecurso_cc33_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000575 where codarq = 3940 and codcam = 21886;
delete from db_sysprikey where codarq = 3940;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3940,21886,1,21888);
delete from db_sysforkey where codarq = 3940 and referen = 0;
insert into db_sysforkey values(3940,21887,1,3878,0);
insert into db_sysforkey values(3940,21888,1,749,0);

insert into db_sysarquivo values (3941, 'classificacaocredorestipocompra', 'Tabela de vínculo com o Tipo de Compra.', 'cc34', '2016-05-19', 'classificacaocredorestipocompra', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (38,3941);
insert into db_syscampo values(21889,'cc34_sequencial','int4','Código','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21890,'cc34_classificacaocredores','int4','Classificação de Credores','0', 'Classificação de Credores',10,'f','f','f',1,'text','Classificação de Credores');
insert into db_syscampo values(21891,'cc34_pctipocompra','int4','Tipo de Compra','0', 'Tipo de Compra',10,'f','f','f',1,'text','Tipo de Compra');
insert into db_sysarqcamp values(3941,21889,1,0);
insert into db_sysarqcamp values(3941,21890,2,0);
insert into db_sysarqcamp values(3941,21891,3,0);
insert into db_syssequencia values(1000576, 'classificacaocredorestipocompra_cc34_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000576 where codarq = 3941 and codcam = 21889;
delete from db_sysprikey where codarq = 3941;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3941,21889,1,21891);
delete from db_sysforkey where codarq = 3941 and referen = 0;
insert into db_sysforkey values(3941,21890,1,3878,0);
insert into db_sysforkey values(3941,21891,1,866,0);

insert into db_sysarquivo values (3942, 'classificacaocredoresevento', 'Vinculo da classificação de credor com o tipo de evento do empenho.', 'cc35', '2016-05-19', 'classificacaocredoresevento', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (38,3942);
insert into db_syscampo values(21892,'cc35_sequencial','int4','Código','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21893,'cc35_classificacaocredores','int4','Código da Classificação de Credores','0', 'Classificação de Credores',10,'f','f','f',1,'text','Classificação de Credores');
insert into db_syscampo values(21894,'cc35_empprestatip','int4','Tipo de Evento','0', 'Tipo de Evento',10,'f','f','f',1,'text','Tipo de Evento');
delete from db_sysarqcamp where codarq = 3942;
insert into db_sysarqcamp values(3942,21892,1,0);
insert into db_sysarqcamp values(3942,21893,2,0);
insert into db_sysarqcamp values(3942,21894,3,0);
delete from db_sysprikey where codarq = 3942;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3942,21892,1,21893);
delete from db_sysforkey where codarq = 3942 and referen = 0;
insert into db_sysforkey values(3942,21893,1,3878,0);
delete from db_sysforkey where codarq = 3942 and referen = 0;
insert into db_sysforkey values(3942,21894,1,1038,0);
insert into db_syssequencia values(1000577, 'classificacaocredoresevento_cc35_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000577 where codarq = 3942 and codcam = 21892;

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21895 ,'cc30_contagemdias' ,'int4' ,'Vencimento em Dias' ,'' ,'Vencimento em Dias' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Vencimento em Dias' );
delete from db_syscampodef where codcam = 21895;
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21895 ,'1' ,'Úteis' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21895 ,'2' ,'Corridos' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3878 ,21895 ,3 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21896 ,'cc30_diasvencimento' ,'int4' ,'Quantidade de Dias para o Vencimento' ,'' ,'Quantidade de Dias para o Vencimento' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Quantidade de Dias para o Vencimento' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3878 ,21896 ,4 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21897 ,'cc30_valorinicial' ,'float4' ,'Valor Inicial' ,'' ,'Valor Inicial' ,50 ,'true' ,'false' ,'false' ,4 ,'text' ,'Valor Inicial' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3878 ,21897 ,5 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21898 ,'cc30_valorfinal' ,'float4' ,'Valor Final' ,'' ,'Valor Final' ,50 ,'true' ,'false' ,'false' ,4 ,'text' ,'Valor Final' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3878 ,21898 ,6 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21899 ,'cc30_dispensa' ,'bool' ,'Lista do Tipo Dispensa' ,'false' ,'Lista do Tipo Dispensa' ,1 ,'false' ,'false' ,'false' ,5 ,'text' ,'Lista do Tipo Dispensa' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3878 ,21899 ,7 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21900 ,'cc30_ordem' ,'int4' ,'Ordem' ,'' ,'Ordem' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Ordem' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3878 ,21900 ,8 ,0 );

insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10240 ,'Lista de Classificação de Credores' ,'Cadastro de Lista de Classificação de Credores' ,'' ,'1' ,'1' ,'Cadastro de Lista de Classificação de Credores' ,'true' );
delete from db_menu where id_item_filho = 10240 AND modulo = 398;
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 29 ,10240 ,269 ,398 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10241 ,'Inclusão' ,'Inclusão de Lista de Classificação de Credores' ,'emp1_classificacaocredores001.php?opcao=1' ,'1' ,'1' ,'Inclusão de Lista de Classificação de Credores' ,'true' );
delete from db_menu where id_item_filho = 10241 AND modulo = 398;
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10240 ,10241 ,1 ,398 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10242 ,'Alteração' ,'Alteração de Lista de Classificação de Credores' ,'emp1_classificacaocredores001.php?opcao=2' ,'1' ,'1' ,'Alteração de Lista de Classificação de Credores' ,'true' );
delete from db_menu where id_item_filho = 10242 AND modulo = 398;
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10240 ,10242 ,2 ,398 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10243 ,'Exclusão' ,'Exclusão de Lista de Classificação de Credores' ,'emp1_classificacaocredores001.php?opcao=3' ,'1' ,'1' ,'Exclusão de Lista de Classificação de Credores' ,'true' );
delete from db_menu where id_item_filho = 10243 AND modulo = 398;
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10240 ,10243 ,3 ,398 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10245 ,'Ordenar' ,'Ordenar' ,'emp1_classificacaocredoresordenacao001.php' ,'1' ,'1' ,'Ordenar Classificação de Credores' ,'true' );
delete from db_menu where id_item_filho = 10245 AND modulo = 398;
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10240 ,10245 ,4 ,398 );

update db_syscampo set codcam = 3347 , nomecam = 'o15_codigo' , conteudo = 'int4' , descricao = 'Codigo do Tipo de Recurso' , valorinicial = '0' , rotulo = 'Recurso' , tamanho = 10 , nulo = 'false' , maiusculo = 'false' , autocompl = 'false' , aceitatipo = 1 , tipoobj = 'text' , rotulorel = 'Recurso' where codcam = 3347;
update db_syscampo set codcam = 3348 , nomecam = 'o15_descr' , conteudo = 'varchar(60)' , descricao = 'Descrição do Recurso' , rotulo = 'Descrição do Recurso' , tamanho = 60 , nulo = 'false' , maiusculo = 'true' , autocompl = 'false' , aceitatipo = 0 , tipoobj = 'text' , rotulorel = 'Tipo de Recurso' where codcam = 3348;
update db_syscampo set codcam = 5526 , nomecam = 'pc50_codcom' , conteudo = 'int4' , descricao = 'Código do Tipo de Compra.' , valorinicial = '0' , rotulo = 'Tipo de Compra' , tamanho = 4 , nulo = 'false' , maiusculo = 'false' , autocompl = 'false' , aceitatipo = 1 , tipoobj = 'text' , rotulorel = 'Código' where codcam = 5526;
update db_syscampo set codcam = 5527 , nomecam = 'pc50_descr' , conteudo = 'varchar(50)' , descricao = 'Descrição do Tipo de Compra.' , rotulo = 'Descrição do Tipo de Compra' , tamanho = 50 , nulo = 'false' , maiusculo = 'true' , autocompl = 'false' , aceitatipo = 0 , tipoobj = 'text' , rotulorel = 'Descrição' where codcam = 5527;
update db_syscampo set codcam = 6356 , nomecam = 'e44_descr' , conteudo = 'varchar(40)' , descricao = 'Descrição do Evento' , rotulo = 'Descrição do Evento' , tamanho = 40 , nulo = 'false' , maiusculo = 'true' , autocompl = 'false' , aceitatipo = 0 , tipoobj = 'text' , rotulorel = 'Descrição do Evento' where codcam = 6356;


select fc_executa_ddl('
  insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10246 ,\'Manutenção de Licitações Enviadas\' ,\'Manutenção de Licitações Enviadas\' ,\'lic4_manutencaolicitacoesenviadas001.php\',1 ,1 ,\'Manutenção de Licitações Enviadas\' ,\'true\' );
  delete from db_menu where id_item_filho = 10246 AND modulo = 381;
  insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10212 ,10246 ,3 ,381 );
');

select fc_executa_ddl('
  insert into db_sysarquivo values (3944, \'cgmestrangeiro\', \'Informações para um CGM estrangeiro\', \'z09\', \'2016-06-10\', \'CGM Estrangeiro\', 0, \'f\', \'f\', \'f\', \'f\' );
  insert into db_sysarqmod values (4,3944);
  insert into db_syscampo values(21907,\'z09_sequencial\',\'int4\',\'Código\',\'0\', \'Código\',10,\'f\',\'f\',\'f\',1,\'text\',\'Código\');
  insert into db_syscampo values(21908,\'z09_numcgm\',\'int4\',\'Código do CGM\',\'0\', \'Código do CGM\',10,\'f\',\'f\',\'f\',1,\'text\',\'Código do CGM\');
  insert into db_syscampo values(21909,\'z09_documento\',\'varchar(30)\',\'Documento\',\'\', \'Documento\',30,\'f\',\'t\',\'f\',0,\'text\',\'Documento\');
  delete from db_sysarqcamp where codarq = 3944;
  insert into db_sysarqcamp values(3944,21907,1,0);
  insert into db_sysarqcamp values(3944,21908,2,0);
  insert into db_sysarqcamp values(3944,21909,3,0);
  delete from db_sysforkey where codarq = 3944 and referen = 0;
  insert into db_sysforkey values(3944,21908,1,42,0);
  delete from db_sysprikey where codarq = 3944;
  insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3944,21907,1,21908);
  insert into db_sysindices values(4358,\'cgmestrangeiro_numcgm_in\',3944,\'0\');
  insert into db_syscadind values(4358,21908,1);
  insert into db_syssequencia values(1000579, \'cgmestrangeiro_z09_sequencial_seq\', 1, 1, 9223372036854775807, 1, 1);
  update db_sysarqcamp set codsequencia = 1000579 where codarq = 3944 and codcam = 21907;
');



SQL_PRE;

        $ddl = <<<'SQL_DDL'
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO SAÚDE ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
CREATE SEQUENCE cgs_unddocumento_sd108_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;


CREATE SEQUENCE cgs_undendereco_sd109_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE cgs_unddocumento(
sd108_sequencial    int4 NOT NULL,
sd108_cgs_und       int4 NOT NULL,
sd108_documento  int4 NOT NULL );

CREATE TABLE cgs_undendereco(
sd109_sequencial int4 NOT NULL,
sd109_endereco int4 NOT NULL ,
sd109_cgs_und int4 NOT NULL );

alter table cgs_und add column z01_orgaoemissoridentidade varchar(100);


-- CHAVE ESTRANGEIRA


ALTER TABLE cgs_unddocumento
ADD CONSTRAINT cgs_unddocumento_und_fk FOREIGN KEY (sd108_cgs_und)
REFERENCES cgs_und;

ALTER TABLE cgs_unddocumento
ADD CONSTRAINT cgs_unddocumento_documento_fk FOREIGN KEY (sd108_documento)
REFERENCES documento;

ALTER TABLE cgs_undendereco
ADD CONSTRAINT cgs_undendereco_und_fk FOREIGN KEY (sd109_cgs_und)
REFERENCES cgs_und;

ALTER TABLE cgs_undendereco
ADD CONSTRAINT cgs_undendereco_endereco_fk FOREIGN KEY (sd109_endereco)
REFERENCES endereco;




-- INDICES


CREATE  INDEX cgs_unddocumento_cgs_und_in ON cgs_unddocumento(sd108_cgs_und);

CREATE  INDEX cgs_unddocumento_documento_in ON cgs_unddocumento(sd108_documento);

CREATE  INDEX cgs_undendereco_cgs_und_in ON cgs_undendereco(sd109_cgs_und);

CREATE  INDEX cgs_undendereco_endereco_in ON cgs_undendereco(sd109_endereco);

alter table cgs_und alter COLUMN z01_v_cxpostal type varchar(50);
alter table cgs_undalt alter COLUMN z33_v_nome type varchar(255);

---------------------------------------------------------------------------------------------------------------------------
---------------------------------------------- INICIO FINANCEIRO ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

DROP TABLE IF EXISTS classificacaocredoreselemento CASCADE;
DROP SEQUENCE IF EXISTS classificacaocredoreselemento_cc32_sequencial_seq;

CREATE SEQUENCE classificacaocredoreselemento_cc32_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE classificacaocredoreselemento(
cc32_sequencial int4 NOT NULL default 0,
cc32_classificacaocredores int4 NOT NULL default 0,
cc32_codcon int4 NOT NULL default 0,
cc32_anousu int4 NOT NULL default 0,
cc32_exclusao bool default false,
CONSTRAINT classificacaocredoreselemento_sequ_pk PRIMARY KEY (cc32_sequencial));

ALTER TABLE classificacaocredoreselemento
ADD CONSTRAINT classificacaocredoreselemento_classificacaocredores_fk FOREIGN KEY (cc32_classificacaocredores)
REFERENCES classificacaocredores;

ALTER TABLE classificacaocredoreselemento
ADD CONSTRAINT classificacaocredoreselemento_conplanoorcamento_fk FOREIGN KEY (cc32_codcon, cc32_anousu)
REFERENCES conplanoorcamento;


DROP TABLE IF EXISTS classificacaocredoresrecurso CASCADE;
DROP SEQUENCE IF EXISTS classificacaocredoresrecurso_cc33_sequencial_seq;

CREATE SEQUENCE classificacaocredoresrecurso_cc33_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE classificacaocredoresrecurso(
cc33_sequencial int4 NOT NULL default 0,
cc33_classificacaocredores int4 NOT NULL default 0,
cc33_orctiporec int4 default 0,
CONSTRAINT classificacaocredoresrecurso_sequ_pk PRIMARY KEY (cc33_sequencial));

ALTER TABLE classificacaocredoresrecurso
ADD CONSTRAINT classificacaocredoresrecurso_orctiporec_fk FOREIGN KEY (cc33_orctiporec)
REFERENCES orctiporec;

ALTER TABLE classificacaocredoresrecurso
ADD CONSTRAINT classificacaocredoresrecurso_classificacaocredores_fk FOREIGN KEY (cc33_classificacaocredores)
REFERENCES classificacaocredores;


DROP TABLE IF EXISTS classificacaocredorestipocompra CASCADE;
DROP SEQUENCE IF EXISTS classificacaocredorestipocompra_cc34_sequencial_seq;

CREATE SEQUENCE classificacaocredorestipocompra_cc34_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE classificacaocredorestipocompra(
cc34_sequencial int4 NOT NULL default 0,
cc34_classificacaocredores int4 NOT NULL default 0,
cc34_pctipocompra   int4 default 0,
CONSTRAINT classificacaocredorestipocompra_sequ_pk PRIMARY KEY (cc34_sequencial));

ALTER TABLE classificacaocredorestipocompra
ADD CONSTRAINT classificacaocredorestipocompra_classificacaocredores_fk FOREIGN KEY (cc34_classificacaocredores)
REFERENCES classificacaocredores;

ALTER TABLE classificacaocredorestipocompra
ADD CONSTRAINT classificacaocredorestipocompra_pctipocompra_fk FOREIGN KEY (cc34_pctipocompra)
REFERENCES pctipocompra;

DROP TABLE IF EXISTS classificacaocredoresevento CASCADE;
DROP SEQUENCE IF EXISTS classificacaocredoresevento_cc35_sequencial_seq;

CREATE SEQUENCE classificacaocredoresevento_cc35_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE classificacaocredoresevento(
cc35_sequencial              int4 NOT NULL default 0,
cc35_classificacaocredores   int4 NOT NULL default 0,
cc35_empprestatip            int4 not null default 0,
CONSTRAINT classificacaocredoresevento_sequ_pk PRIMARY KEY (cc35_sequencial));

ALTER TABLE classificacaocredoresevento ADD CONSTRAINT classificacaocredoresevento_empprestatip_fk FOREIGN KEY (cc35_empprestatip) REFERENCES empprestatip;
ALTER TABLE classificacaocredoresevento ADD CONSTRAINT classificacaocredoresevento_classificacaocredores_fk FOREIGN KEY (cc35_classificacaocredores) REFERENCES classificacaocredores;

ALTER TABLE classificacaocredores ADD COLUMN cc30_diasvencimento int4 default null;
ALTER TABLE classificacaocredores ADD COLUMN cc30_contagemdias int4 default null;
ALTER TABLE classificacaocredores ADD COLUMN cc30_valorinicial numeric default null;
ALTER TABLE classificacaocredores ADD COLUMN cc30_valorfinal numeric default null;
ALTER TABLE classificacaocredores ADD COLUMN cc30_dispensa boolean default false;
ALTER TABLE classificacaocredores ADD COLUMN cc30_ordem int4 not null default 0;


update classificacaocredores set cc30_dispensa = true where cc30_codigo = 4;

/**
 * DML - 99810
 */
insert into acordoempempenho
     select nextval('acordoempempenho_ac54_sequencial_seq'),
            e100_acordo,
            e100_numemp,
            null,
            null
       from empempenhocontrato
            inner join acordo on ac16_sequencial = e100_acordo
      where ac16_origem = 6;
update acordo set ac16_origem = 3 where ac16_origem = 6;

/**
 * Acertos para cadastro de lista de classificação de credores padrão
 */
update classificacaocredores set cc30_descricao = upper(cc30_descricao);

update classificacaocredores
set cc30_diasvencimento = 30,
  cc30_contagemdias = 1,
  cc30_ordem = 2
where cc30_codigo = 1;

update classificacaocredores
set cc30_diasvencimento = 5,
  cc30_contagemdias = 1,
  cc30_valorinicial = 0,
  cc30_valorfinal = 8000.00,
  cc30_ordem = 3
where cc30_codigo = 2;

update classificacaocredores
set cc30_diasvencimento = 30,
  cc30_contagemdias = 1,
  cc30_ordem = 4
where cc30_codigo = 3;

update classificacaocredores
set cc30_diasvencimento = null,
  cc30_contagemdias = null,
  cc30_ordem = 1
where cc30_codigo = 4;

insert into classificacaocredoresevento
  select
    nextval('classificacaocredoresevento_cc35_sequencial_seq'),
    4,
    e44_tipo
  from empprestatip
  where e44_obriga <> 0;

insert into classificacaocredoreselemento
  select
    nextval('classificacaocredoreselemento_cc32_sequencial_seq'),
    4,
    c60_codcon,
    c60_anousu,
    false
  from conplanoorcamento
  where c60_anousu = 2016
        and substring(c60_estrut from 1 for 1) = '3'
        and (substring(c60_estrut from 6 for 2) in ('47', '16', '93') or substring(c60_estrut from 1 for 3) = '331');



insert into classificacaocredoresrecurso
  select
    nextval('classificacaocredoresrecurso_cc33_sequencial_seq'),
    1,
    o15_codigo
  from orctiporec
  where o15_tipo = 2;

select setval('classificacaocredores_cc30_codigo_seq', 100);


delete from empautpresta where not exists (select 1 from empautoriza where e58_autori = e54_autori);
delete from empautpresta where not exists (select 1 from empprestatip where e44_tipo = e58_tipo);
alter table empautpresta add constraint empautoriza_empautpresta_fk foreign key (e58_autori) references empautoriza;
alter table empautpresta add constraint empprestatip_empautpresta_fk foreign key (e58_tipo) references empprestatip;
create index empautpresta_autori_in on empautpresta (e58_autori);


---------------------------------------------------------------------------------------------------------------------------
------------------------------------------------ INICIO FOLHA -------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

--
-- Ajustes da TAG 99760 adicionada ao path da release 50
select fc_executa_ddl('CREATE SEQUENCE esocial.avaliacaogruporespostarhpessoal_eso02_sequencial_seq
                         INCREMENT 1
                         MINVALUE 1
                         MAXVALUE 9223372036854775807
                         START 1
                         CACHE 1;');

select fc_executa_ddl('CREATE TABLE esocial.avaliacaogruporespostarhpessoal(
                         eso02_sequencial int4 NOT NULL default nextval(''avaliacaogruporespostarhpessoal_eso02_sequencial_seq''),
                         eso02_avaliacaogruporesposta int4 NOT NULL,
                         eso02_rhpessoal int4 NOT NULL);');

select fc_executa_ddl('CREATE UNIQUE INDEX avaliacaogruporespostarhpessoal_un_in on avaliacaogruporespostarhpessoal(eso02_avaliacaogruporesposta, eso02_rhpessoal);');

select fc_executa_ddl('ALTER TABLE avaliacaogruporespostarhpessoal
                                                 ADD CONSTRAINT eso02_sequencial_pk PRIMARY KEY (eso02_sequencial);');
select fc_executa_ddl('ALTER TABLE avaliacaogruporespostarhpessoal
                                                 ADD CONSTRAINT eso02_avaliacaogruporesposta_fk FOREIGN KEY (eso02_avaliacaogruporesposta) REFERENCES avaliacaogruporesposta;');
select fc_executa_ddl('ALTER TABLE avaliacaogruporespostarhpessoal
                                                 ADD CONSTRAINT eso02_rhpessoal_fk FOREIGN KEY (eso02_rhpessoal) REFERENCES rhpessoal;');

--Guarda dados ja prenchidos do eSocial
select fc_executa_ddl('CREATE TEMP TABLE w_avaliacaogruporespostacgm AS (    SELECT avaliacaogruporespostacgm.*, rh01_regist as matricula
                                                                               FROM avaliacaogruporespostacgm
                                                                         INNER JOIN rhpessoal ON rh01_numcgm = eso02_cgm);');

DROP TABLE IF EXISTS avaliacaogruporespostacgm;
DROP SEQUENCE IF EXISTS avaliacaogruporespostacgm_eso02_sequencial_seq;

--Retorna os valores já respondidos do eSocial
select fc_executa_ddl('INSERT INTO avaliacaogruporespostarhpessoal (SELECT eso02_sequencial, eso02_avaliacaogruporesposta, matricula FROM w_avaliacaogruporespostacgm);');

--Ajuste o valor da sequence
select fc_executa_ddl('select setval(''avaliacaogruporespostarhpessoal_eso02_sequencial_seq'', (select max(eso02_sequencial) from w_avaliacaogruporespostacgm));');

-------------------------------
-- Melhorias para release 52 --
-------------------------------
CREATE SEQUENCE avaliacaogruporespostacgm_eso03_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE avaliacaogruporespostacgm(
eso03_sequencial              int4 NOT NULL default nextval('avaliacaogruporespostacgm_eso03_sequencial_seq'),
eso03_avaliacaogruporesposta  int4 NOT NULL,
eso03_cgm                     int4 NOT NULL,
CONSTRAINT avaliacaogruporespostacgm_pk PRIMARY KEY (eso03_sequencial));

ALTER TABLE avaliacaogruporespostacgm ADD CONSTRAINT avaliacaogruporespostacgm_avaliacaogruporesposta_fk FOREIGN KEY (eso03_avaliacaogruporesposta) REFERENCES avaliacaogruporesposta;
ALTER TABLE avaliacaogruporespostacgm ADD CONSTRAINT avaliacaogruporespostacgm_cgm_fk FOREIGN KEY (eso03_cgm) REFERENCES cgm;


select fc_executa_ddl('
  CREATE SEQUENCE cgmestrangeiro_z09_sequencial_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;
  CREATE TABLE protocolo.cgmestrangeiro(
  z09_sequencial  int4 NOT NULL default 0,
  z09_numcgm      int4 NOT NULL default 0,
  z09_documento   varchar(30) not null,
  CONSTRAINT cgmestrangeiro_sequ_pk PRIMARY KEY (z09_sequencial));
  ALTER TABLE cgmestrangeiro ADD CONSTRAINT cgmestrangeiro_numcgm_fk FOREIGN KEY (z09_numcgm) REFERENCES cgm;
  CREATE INDEX cgmestrangeiro_numcgm_in ON cgmestrangeiro(z09_numcgm);
');









---------------------------------------------------------------------------------------
--------------------------------INICIO CONFIGURACAO------------------------------------
---------------------------------------------------------------------------------------

SELECT fc_executa_ddl('
ALTER TABLE db_auditoria_migracao ADD datahora_ini TIMESTAMP WITH TIME ZONE, ADD datahora_fim TIMESTAMP WITH TIME ZONE, ADD instit INTEGER[];

');


CREATE OR REPLACE FUNCTION fc_auditoria_busca_datahora_e_instit(data_inicial DATE, id_acount_ini INTEGER, id_acount_fim INTEGER, OUT datahora_ini TIMESTAMPTZ, OUT datahora_fim TIMESTAMPTZ, OUT instit INTEGER[]) AS $$
SELECT min(datahora_ini) AS datahora_ini,
       max(datahora_fim) AS datahora_fim,
       array_agg(instit) AS instit
FROM (
        (SELECT to_timestamp(min(datahr)) AS datahora_ini,
                to_timestamp(max(datahr)) AS datahora_fim,
                instit
         FROM
           (SELECT datahr,
                   coalesce(
                              (SELECT min(i.id_instit)
                               FROM db_userinst i
                               WHERE i.id_usuario=a.id_usuario),
                              (SELECT codigo
                               FROM db_config
                               WHERE prefeitura IS TRUE LIMIT 1)) AS instit
            FROM db_acount a
            WHERE NOT EXISTS
                (SELECT 1
                 FROM db_acountacesso ac
                 WHERE ac.id_acount = a.id_acount)
              AND a.id_acount BETWEEN $2 AND $3
) AS y
         GROUP BY instit)
      UNION ALL
        (SELECT (min(DATA)||' '||min(hora))::timestamptz AS datahora_ini,
                (max(DATA)||' '||max(hora))::timestamptz AS datahora_fim,
                la.instit
         FROM db_acountacesso ac
         JOIN db_logsacessa la ON la.codsequen=ac.codsequen
         AND la.DATA >= $1

         AND la.instit IN
           (SELECT codigo
            FROM db_config)
         WHERE ac.id_acount BETWEEN $2 AND $3

         GROUP BY la.instit)) AS x $$ LANGUAGE SQL;

 -- Ajusta registros ainda nao migrados

UPDATE db_auditoria_migracao
SET datahora_ini = (dhi).datahora_ini,
                         datahora_fim = (dhi).datahora_fim,
                                              instit = (dhi).instit
FROM
  (SELECT sequencial,
          fc_auditoria_busca_datahora_e_instit((CURRENT_DATE - interval '1 year')::date, id_acount_ini, id_acount_fim) AS dhi
   FROM db_auditoria_migracao
   WHERE status <> 'FINALIZADO'
     AND datahora_ini IS NULL
     AND datahora_fim IS NULL
     AND instit IS NULL) AS x
WHERE db_auditoria_migracao.sequencial = x.sequencial;


CREATE OR REPLACE FUNCTION fc_auditoria_adiciona_acount_fila() RETURNS void LANGUAGE SQL AS $function$
SELECT NEXTVAL('configuracoes.db_auditoria_migracao_sequencial_seq');
INSERT INTO configuracoes.db_auditoria_migracao (sequencial, id_acount_ini, id_acount_fim, status)
SELECT CURRVAL('configuracoes.db_auditoria_migracao_sequencial_seq'),
       id_acount_ini,
       id_acount_fim,
       status
FROM
  (SELECT COALESCE(MIN(id_acount), 0) AS id_acount_ini,
          COALESCE(MAX(id_acount), 0) AS id_acount_fim,
          'NAO INICIADO'::text AS status
   FROM ONLY configuracoes.db_acount
   WHERE id_acount > COALESCE(
                                (SELECT id_acount_fim
                                 FROM configuracoes.db_auditoria_migracao
                                 ORDER BY id_acount_fim DESC LIMIT 1), 0)) AS lote
WHERE (id_acount_ini + id_acount_fim) > 0;
  UPDATE db_auditoria_migracao
  SET datahora_ini = (dhi).datahora_ini,
                           datahora_fim = (dhi).datahora_fim,
                                                instit = (dhi).instit
  FROM
    (SELECT sequencial,
            fc_auditoria_busca_datahora_e_instit((CURRENT_DATE - interval '6 months')::date, id_acount_ini, id_acount_fim) AS dhi
     FROM db_auditoria_migracao
     WHERE sequencial = CURRVAL('configuracoes.db_auditoria_migracao_sequencial_seq')) AS x WHERE db_auditoria_migracao.sequencial = x.sequencial; $function$;

---------------------------------------------------------------------------------------
---------------------------------- FIM CONFIGURACAO------------------------------------
---------------------------------------------------------------------------------------



---------------------------------------------------------------------------------------
---------------------------------- INICIO EDUCAÇÃO ------------------------------------
---------------------------------------------------------------------------------------

insert into censoetapa
    (select ed266_i_codigo, ed266_c_descr, ed266_c_regular, ed266_c_especial, ed266_c_eja, 2016
       from censoetapa
      where ed266_ano = 2015
        and not exists (select 1 from censoetapa where ed266_ano = 2016)
    );

insert into censoetapamediacaodidaticopedagogica
    (select nextval('censoetapamediacaodidaticopedagogica_ed131_codigo_seq'), ed131_mediacaodidaticopedagogica, ed131_censoetapa, 2016, ed131_regular, ed131_especial, ed131_eja, ed131_profissional
       from censoetapamediacaodidaticopedagogica
      where ed131_ano = 2015
        and not exists (select 1 from censoetapamediacaodidaticopedagogica where ed131_ano = 2016 )
    );

insert into censoregradisc
    (select nextval('censoregradisc_ed272_i_codigo_seq'), ed272_i_censoetapa, ed272_i_censodisciplina, 2016
       from censoregradisc
      where ed272_ano = 2015
        and not exists (select 1 from censoregradisc where ed272_ano = 2016 )
    );

update censoinstsuperior set ed257_c_nome = 'UNIVERSIDADE DO CONTESTADO'                            , ed257_i_censomunic = '4210100', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 441;
update censoinstsuperior set ed257_c_nome = 'UNIVERSIDADE DO SUL DE SANTA CATARINA'                 , ed257_i_censomunic = '4218707', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 494;
update censoinstsuperior set ed257_c_nome = 'CENTRO UNIVERSITARIO DE MANDAGUARI UNIMAN'             , ed257_i_censomunic = '4114203', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 535;
update censoinstsuperior set ed257_c_nome = 'FACULDADE DE FORMACAO DE PROFESSORES DE SERRA TALHADA' , ed257_i_censomunic = '2613909', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 657;
update censoinstsuperior set ed257_c_nome = 'UNIVERSIDADE DE TAUBATE'                               , ed257_i_censomunic = '3554102', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 665;
update censoinstsuperior set ed257_c_nome = 'UNIVERSIDADE DO PLANALTO CATARINENSE'                  , ed257_i_censomunic = '4209300', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 1189;
update censoinstsuperior set ed257_c_nome = 'FACULDADES ADAMANTINENSES INTEGRADAS'                  , ed257_i_censomunic = '3500105', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 1292;
update censoinstsuperior set ed257_c_nome = 'FACULDADES INTEGRADAS DE SANTA FE DO SUL'              , ed257_i_censomunic = '3546603', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 1356;
update censoinstsuperior set ed257_c_nome = 'CENTRO UNIVERSITARIO FUNDACAO SANTO ANDRE'             , ed257_i_censomunic = '3547809', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 2183;
update censoinstsuperior set ed257_c_nome = 'UNIVERSIDADE COMUNITARIA DA REGIAO DE CHAPECO'         , ed257_i_censomunic = '4204202', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 3151;
update censoinstsuperior set ed257_c_nome = 'UNIVERSIDADE DE RIO VERDE'                             , ed257_i_censomunic = '5218805', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 3974;
update censoinstsuperior set ed257_c_nome = 'CENTRO UNIVERSITARIO MUNICIPAL DE SAO JOSE'            , ed257_i_censomunic = '4216602', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 4756;
update censoinstsuperior set ed257_c_nome = 'UNIVERSIDADE ALTO VALE DO RIO DO PEIXE'                , ed257_i_censomunic = '4203006', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 15032;
update censoinstsuperior set ed257_c_nome = 'FACULDADE DE CIENCIAS DA SAUDE DE SERRA TALHADA'       , ed257_i_censomunic = '2613909', ed257_i_dependencia = 3, ed257_i_tipo = 1, ed257_c_situacao = 'ATIVA' where ed257_i_codigo = 17775;

select fc_executa_ddl('
insert into censoinstsuperior
values (12899, \'FACULDADE METROPOLITANA DO VALE DO AÇO\'                       , 4, 2, \'3131307\', \'INATIVA\'),
       (10251, \'FACULDADE ORTODOXA\'                                           , 4, 2, \'5104104\', \'ATIVA\'),
       (13728, \'FACULDADE DOS CARAJÁS\'                                        , 4, 2, \'1504208\', \'ATIVA\'),
       (13764, \'FACULDADE DE TECNOLOGIA DE AMPÉRE\'                            , 4, 2, \'4101002\', \'INATIVA\'),
       (14158, \'FACULDADE DE TECNOLOGIA DE NOVO CABRAIS\'                      , 4, 2, \'4313391\', \'ATIVA\'),
       (14718, \'FACULDADE PARANÁ\'                                             , 4, 2, \'4103701\', \'INATIVA\'),
       (15500, \'FACULDADE LUSOCAPIXABA\'                                       , 4, 2, \'3201308\', \'INATIVA\'),
       (15562, \'FACULDADE BATISTA DO CARIRI\'                                  , 4, 2, \'2304202\', \'ATIVA\'),
       (16602, \'FACULDADE DE EDUCAÇÃO ELIÂ\'                                   , 4, 2, \'1507953\', \'INATIVA\'),
       (16782, \'FACULDADE MÁRIO QUINTANA\'                                     , 4, 2, \'4314902\', \'ATIVA\'),
       (16849, \'FACULDADE MODAL\'                                              , 4, 2, \'3106200\', \'INATIVA\'),
       (16918, \'FACULDADE CATÓLICA DE FEIRA DE SANTANA\'                       , 4, 2, \'2910800\', \'INATIVA\'),
       (16948, \'FACULDADE 28 DE AGOSTO DE ENSINO E PESQUISA\'                  , 4, 2, \'3550308\', \'INATIVA\'),
       (17025, \'FACULDADE DE EDUCAÇÃO SUPERIOR DE PARAGOMINAS\'                , 4, 2, \'1505502\', \'INATIVA\'),
       (17091, \'FACULDADE DE NEGÓCIOS DO RECIFE\'                              , 4, 2, \'2611606\', \'INATIVA\'),
       (17115, \'FACULDADE DA UNIÃO DE ENSINO E PESQUISA INTEGRADA\'            , 4, 2, \'2507507\', \'INATIVA\'),
       (17118, \'FACULDADE DO NORTE DE MATO GROSSO\'                            , 4, 2, \'5104104\', \'ATIVA\'),
       (17289, \'FACULDADE DE TEOLOGIA DE CARATINGA URIEL DE ALMEIDA LEITÃO\'   , 4, 2, \'3113404\', \'INATIVA\'),
       (17348, \'FACULDADE DE TECNOLOGIA DOS INCONFIDENTES\'                    , 4, 2, \'3131901\', \'ATIVA\'),
       (17355, \'FACULDADE DE EDUCAÇÃO EM CIÊNCIAS DA SAÚDE\'                   , 4, 2, \'3550308\', \'ATIVA\'),
       (17382, \'FACULDADE IETEC\'                                              , 4, 2, \'3106200\', \'INATIVA\'),
       (17394, \'FACULDADE ALENCARINA DE SOBRAL\'                               , 4, 2, \'2312908\', \'INATIVA\'),
       (17400, \'FACULDADE MENINO DEUS\'                                        , 4, 2, \'4314902\', \'INATIVA\'),
       (17403, \'FACULDADE ARI DE SÁ\'                                          , 4, 2, \'2304400\', \'INATIVA\'),
       (17420, \'FACULDADE CESUMAR DE PONTA GROSSA\'                            , 4, 2, \'4119905\', \'INATIVA\'),
       (17460, \'FACULDADE PROFISSIONAL\'                                       , 4, 2, \'4106902\', \'ATIVA\'),
       (17558, \'FACULDADE SANTO ANDRÉ\'                                        , 4, 2, \'1100304\', \'INATIVA\'),
       (17563, \'FACULDADE COESP\'                                              , 4, 2, \'2507507\', \'INATIVA\'),
       (17565, \'FACULDADE DE CIÊNCIAS HUMANAS,EXATAS E DA SAÚDE DO PIAUÍ\'     , 4, 2, \'2207702\', \'INATIVA\'),
       (17590, \'FACULDADE ISAE BRASIL\'                                        , 4, 2, \'4106902\', \'INATIVA\'),
       (17593, \'FACULDADE DE BOTUCATU\'                                        , 4, 2, \'3507506\', \'INATIVA\'),
       (17598, \'FACULDADE PROF. WLADEMIR DOS SANTOS\'                          , 4, 2, \'3552205\', \'INATIVA\'),
       (17608, \'FACULDADE DE EDUCAÇÃO PAULISTANA\'                             , 4, 2, \'3550308\', \'INATIVA\'),
       (17622, \'FACULDADE TALLES DE MILETO - SEDE DRAGÃO DO MAR\'              , 4, 2, \'2304400\', \'INATIVA\'),
       (17628, \'FACULDADE DO MACIÇO DO BATURITÉ\'                              , 4, 2, \'2302107\', \'ATIVA\'),
       (17662, \'FACULDADE GALILEU\'                                            , 4, 2, \'3507506\', \'ATIVA\'),
       (17670, \'FACULDADE DE QUIXERAMOBIM\'                                    , 4, 2, \'2311405\', \'INATIVA\'),
       (17672, \'INSTITUTO DE DIREITO PÚBLICO DE SÃO PAULO\'                    , 4, 2, \'3550308\', \'INATIVA\'),
       (17674, \'FACULDADE DE EDUCAÇÃO DE SÃO MATEUS\'                          , 4, 2, \'2111508\', \'INATIVA\'),
       (17701, \'FAP-FACULDADE DE PINHEIROS\'                                   , 4, 2, \'3204104\', \'INATIVA\'),
       (17731, \'FACULDADE SESI-SP DE EDUCAÇÃO\'                                , 4, 2, \'3550308\', \'INATIVA\'),
       (17749, \'FACULDADE AMÉRICA\'                                            , 4, 2, \'3201209\', \'INATIVA\'),
       (17763, \'FACULDADE SENAI DE JOÃO PESSOA\'                               , 4, 2, \'2507507\', \'INATIVA\'),
       (17816, \'FACULDADE MAURÍCIO DE NASSAU DE FEIRA DE SANTANA\'             , 4, 2, \'2910800\', \'INATIVA\'),
       (17828, \'FACULDADE DO CENTRO LESTE - CARIACICA\'                        , 4, 2, \'3201308\', \'INATIVA\'),
       (17831, \'FACULDADE DE TECNOLOGIA E NEGÓCIOS DE CATALÃO\'                , 4, 2, \'5205109\', \'INATIVA\'),
       (17850, \'FACULDADE TECNOLÓGICA SANTANNA\'                               , 4, 2, \'3556701\', \'ATIVA\'),
       (17854, \'FACULDADE CAPITAL FEDERAL\'                                    , 4, 2, \'3552809\', \'ATIVA\'),
       (18010, \'FACULDADE ESTÁCIO DE CUIABÁ\'                                  , 4, 2, \'5103403\', \'INATIVA\'),
       (18019, \'FACULDADE DO EDUCADOR\'                                        , 4, 2, \'3550308\', \'INATIVA\'),
       (18023, \'FACULDADE MAURÍCIO DE NASSAU DE PETROLINA\'                    , 4, 2, \'2611101\', \'INATIVA\'),
       (18067, \'CISNE - FACULDADE TECNOLÓGICA DE QUIXADÁ\'                     , 4, 2, \'2311306\', \'INATIVA\'),
       (18075, \'FACULDADE MAURÍCIO DE NASSAU DE JABOATÃO DOS GUARARAPES\'      , 4, 2, \'2607901\', \'INATIVA\'),
       (18114, \'FACULDADE FASIPE MATO GROSSO\'                                 , 4, 2, \'5103403\', \'INATIVA\'),
       (18133, \'FACULDADE UNIDA DE CAMPINAS GOIÂNIA - FACUNICAMPS GOIÂNIA\'    , 4, 2, \'5208707\', \'INATIVA\'),
       (18165, \'FUNDAÇÃO UNIVERSIDADE VIRTUAL DO ESTADO DE SÃO PAULO\'         , 2, 1, \'3550308\', \'INATIVA\'),
       (18257, \'FACULDADE SÄO JOSÉ\'                                           , 4, 2, \'4217204\', \'INATIVA\'),
       (18288, \'FACULDADE LATINO-AMERICANA\'                                   , 4, 2, \'3503901\', \'ATIVA\'),
       (19500, \'FACULDADE DE TECNOLOGIA DE SÃO CARLOS\'                        , 2, 1, \'3548906\', \'ATIVA\'),
       (19501, \'FACULDADE DE TECNOLOGIA SEBRAE\'                               , 2, 1, \'3550308\', \'ATIVA\'),
       (19512, \'INSTITUTO MASTER DE ENSINO PRESIDENTE ANTÔNIO CARLOS\'         , 4, 2, \'3103504\', \'ATIVA\'),
       (19578, \'FACULDADE DE TECNOLOGIA DE COTIA\'                             , 2, 1, \'3513009\', \'ATIVA\'),
       (19588, \'FACULDADE DE EDUCAÇÃO TECNOLÓGICA DO ESTADO DO RIO DE JANEIRO\', 2, 1, \'3301702\', \'ATIVA\'),
       (19739, \'FACULDADE DE TECNOLOGIA DE CAMPINAS\'                          , 2, 1, \'3509502\', \'ATIVA\'),
       (19862, \'FACULDADE DE TECNOLOGIA DE BEBEDOURO\'                         , 2, 1, \'3506102\', \'ATIVA\'),
       (20478, \'FACULDADE DE TECNOLOGIA DE SANTANA DE PARNAÍBA\'               , 2, 1, \'3547304\', \'ATIVA\'),
       (21095, \'ACADEMIA MILITAR DAS AGULHAS NEGRAS\'                          , 3, 1, \'3304201\', \'ATIVA\'),
       (21206, \'ESCOLA DE EDUCAÇÃO FÍSICA DO EXÉRCITO\'                        , 3, 1, \'3304557\', \'ATIVA\');
');
---------------------------------------------------------------------------------------
------------------------------------ FIM EDUCAÇÃO -------------------------------------
---------------------------------------------------------------------------------------

SQL_DDL;

        $pos = <<<'SQL_POS'
insert into db_versao (db30_codver, db30_codversao, db30_codrelease, db30_data, db30_obs)  values (368, 3, 52, '2016-06-22', 'Tarefas: 99558, 99745, 99756, 99760, 99772, 99774, 99777, 99778, 99782, 99783, 99784, 99785, 99787, 99788, 99791, 99792, 99793, 99794, 99795, 99796, 99797, 99798, 99799, 99800, 99801, 99802, 99803, 99804, 99805, 99806, 99807, 99808, 99809, 99811, 99812, 99813, 99814, 99815, 99816, 99817, 99819, 99820, 99821, 99822, 99823, 99824, 99825, 99826, 99827, 99828, 99830, 99831, 99832, 99833, 99834, 99835, 99836, 99837, 99838, 99839, 99840, 99842, 99843, 99844, 99845, 99846, 99847, 99848, 99849, 99850, 99851, 99852, 99853, 99856, 99857, 99859, 99862, 99864, 99865, 99866, 99868, 99870, 99871, 99872, 99873, 99874, 99875, 99876, 99879, 99880, 99881, 99882, 99884, 99885, 99886, 99887, 99888, 99889, 99890, 99891, 99893, 99894, 99895, 99896, 99897, 99899, 99900, 99901, 99902, 99905, 99906, 99907, 99910, 99911, 99912, 99914, 99915, 99916, 99918');-- ID do Commit: $Id: cgs_endereco.sql,v 1.2 2016/05/31 20:50:41 dbrafael.nery Exp $

CREATE OR REPLACE FUNCTION fc_cgs_endereco_inc_alt() returns trigger AS
$$   
declare 
    
  iCodigoEstado       integer default 0;
  iCodigoMunicipio    integer default 0;
  iCodigoBairro       integer default 0;
  iCodigoRua          integer default 0;
  iCodigoBairroRua    integer default 0;
  iCodigoLocal        integer default 0;
  iCodigoEndereco     integer default 0;
  iCodigoRuasTipo     integer default 0;
  iCodigoCGS          integer default 0;
  iCodigoCGSEndereco  integer default 0;
  iNumCGSEndereco     integer default 0;

  lRaise              boolean default false;
  sOperacao           text    default '';    

  recordCGS           record;
  recordParametrosEndereco      record;
  rEndereco           record;
begin
      
  lRaise  := case when fc_getsession('DB_debugon') is null then false else true end;
  perform fc_debug('Inicio do Debug da Trigger de Atualizacao de Endereco do CGS', lRaise, true, false);

  if (fc_getsession('DB_desativa_trigger_endereco') is null) then
    return NEW;
  end if;
    
  sOperacao := upper(TG_OP);


  if (sOperacao = 'INSERT') then
    iCodigoCGS := NEW.z01_i_cgsund;
  else 
    iCodigoCGS := OLD.z01_i_cgsund;
  end if;


  /**
   * Verificar se o CGS alterado esta incluído na cgs_undendereco 
   * se estiver tem que verificar campo a campo se houve alteração
   * se não estiver tem que gerar um endereco novo e fazer a ligação 
   * da cgs_undendereco
   */
  select z01_i_cgsund          as codigo_cgs,
         z01_v_ender           as endereco,
         z01_i_numero::varchar as numero,
         z01_v_compl           as complemento,
         z01_v_bairro          as bairro,
         z01_v_munic           as municipio,
         z01_v_uf              as estado_uf,
         z01_v_cep             as cep,
         z01_v_endcon          as endereco_comercial,
         z01_i_numcon          as numero_comercial,
         z01_v_comcon          as complemento_comercial,
         ''                    as bairro_comercial,
         z01_v_muncon          as municipio_comercial,
         z01_v_ufcon           as estado_uf_comercial,
         z01_v_cepcon          as cepcon
    into recordCGS
    from cgs_und 
   where z01_i_cgsund = iCodigoCGS;

      
  if not found then
    perform fc_debug('Nenhum registro retornado para o CGS: ' || iCodigoCGS);
    return null;
  end if;
  
  perform fc_debug('CGS pesquisado: ' || iCodigoCGS);


  if (recordCGS.endereco = '') then
    perform fc_debug('Endereco vazio');
    return null;
  end if;
  
----------------------------------------------- INICIO VERIFICAÇÃO DOS DADOS DO ENDEREÇO -------------------------------------------

  select db99_cadenderpais       as codigoPadraoPais,
         db70_descricao          as descricaoPadraoPais,

         db99_cadenderestado     as codigoPadraoEstado,
         db71_descricao          as descricaoPadraoEstado,
         db71_sigla              as siglaPadraoEstado,

         db99_cadendermunicipio  as codigoPadraoMunicipio,
         db72_descricao          as descricaoPadraoMunicipio
    into recordParametrosEndereco 
    from cadenderparam
         inner join cadenderpais      on cadenderpais.db70_sequencial      = cadenderparam.db99_cadenderpais
         inner join cadenderestado    on cadenderestado.db71_sequencial    = cadenderparam.db99_cadenderestado
         inner join cadendermunicipio on cadendermunicipio.db72_sequencial = cadenderparam.db99_cadendermunicipio;

  if not found then
    perform fc_debug('PARAMETROS DO ENDERECO NAO CONFIGURADOS.');
    return null;
  end if;
    
  perform fc_debug('- Dados Padrao para o Pais:      ' || recordParametrosEndereco.codigoPadraoPais      || ' - ' 
                                                       || recordParametrosEndereco.descricaoPadraoPais
  );
  perform fc_debug('- Dados Padrao para o Estado:    ' || recordParametrosEndereco.codigoPadraoEstado    || ' - ' 
                                                       || recordParametrosEndereco.descricaoPadraoEstado || ' - ' 
                                                       || recordParametrosEndereco.siglaPadraoEstado 
  );
  perform fc_debug('- Dados Padrao para o Municipio: ' || recordParametrosEndereco.codigoPadraoMunicipio || ' - ' 
                                                       || recordParametrosEndereco.descricaoPadraoMunicipio
  );

  /**
   * Pesquisando relação do CGS com o endereco
   */
  select sd109_sequencial as codigoLigacaoEndereco, 
         sd109_endereco   as codigoEndereco
    into iCodigoCGSEndereco, 
         iCodigoEndereco 
    from cgs_undendereco
   where sd109_cgs_und = iCodigoCGS;
  
  if not found then
  ------------------ Se o CGS não existir na tabela de ligação Cria novo endereço para vincular. ID.000001---------------------------

    perform fc_debug('CGS sem endereco cadastrado, sera incluido novo registro.');

    ----------------------------------------------- INICIO do tratameno do estado do ESTADO -------------------------------------------
      -- 
      -- Código padrão do Estado
      -- 
      iCodigoEstado := recordParametrosEndereco.codigoPadraoEstado;
            
      --
      -- Verificar se estado_uf e municipio são diferentes de vazio
      -- se não for atribuir o estado default para o municipio não informado RS, 0-Não Informado 
      --
      if ( (recordCGS.municipio, recordCGS.estado_uf, recordCGS.bairro) != ('','','') ) then
        
        select db71_sequencial
          into iCodigoEstado
          from cadenderestado
         where db71_sigla = trim(recordCGS.estado_uf);

        --   
        -- Se não localizar o estado atribuir o estado dos parametros do endereço
        -- 
        if not found then
          perform fc_debug('Estado não encontrado pela sigla fornecida atribuido dos Parametros do endereco');
        end if;
        iCodigoEstado := recordParametrosEndereco.codigoPadraoEstado;
      end if; -- Fechamento do if (recordCGS.municipio, recordCGS.estado_uf, recordCGS.bairro) != ('','','')
    ----------------------------------------------- FINAL do tratameno do estado do ESTADO --------------------------------------------


    ----------------------------------------------- Inicio do tratameno do estado do Municipio ----------------------------------------
      perform fc_debug('Caso Municipio não seja informado, 0 - Nao informado será utilizado');
      iCodigoMunicipio := 0;

      if (recordCGS.municipio != '') then
        
        select cadendermunicipio.db72_sequencial
          into iCodigoMunicipio
          from cadendermunicipio
         where trim(cadendermunicipio.db72_descricao) = trim(recordCGS.municipio)
           and cadendermunicipio.db72_cadenderestado  = iCodigoEstado;

        --  
        -- Se não encontrou o municipio entao tem que incluir
        -- 
        if not found then

          perform fc_debug('Municipio informado não foi encontrado, será incluido um novo');
          
          iCodigoMunicipio := nextval('cadendermunicipio_db72_sequencial_seq');
          
          insert into cadendermunicipio (
            db72_sequencial,
            db72_descricao,
            db72_cadenderestado
          ) values (
            iCodigoMunicipio,
            recordCGS.municipio,
            iCodigoEstado
          );
        end if;

      end if;
    ---------------------------------------------------- Fim do tratamento do Municipio -----------------------------------------------


    ----------------------------------------------- INICIO TRATAMENTO DOS DADOS DO BAIRRO ---------------------------------------------
      iCodigoBairro := 0; -- Código default

      --  
      -- Se o bairro diferente de vazio pesquisar se existe senão incluir
      -- 
      if (recordCGS.bairro != '') then

        select cadenderbairro.db73_sequencial
          into iCodigoBairro
          from cadenderbairro
         where cadenderbairro.db73_descricao         = recordCGS.bairro 
           and cadenderbairro.db73_cadendermunicipio = iCodigoMunicipio;
       
        if not found then

          perform fc_debug('Bairro não encontrado, será incluído um novo.');

          iCodigoBairro := nextval('cadenderbairro_db73_sequencial_seq');

          insert into cadenderbairro (
            db73_sequencial,
            db73_descricao,
            db73_cadendermunicipio
          ) values (
            iCodigoBairro,
            recordCGS.bairro,
            iCodigoMunicipio
          );
        end if;

      end if;
    ----------------------------------------------- FINAL TRATAMENTO DOS DADOS DO BAIRRO ----------------------------------------------


    ----------------------------------------------- FINAL TRATAMENTO DOS DADOS DA RUA -------------------------------------------------
      if (recordCGS.endereco = '') then
        
        perform fc_debug('Cadastro não pode continuar sem endereço.');
        return null;
      end if;
          
      select cadenderrua.db74_sequencial
        into iCodigoRua
        from cadenderrua
       where cadenderrua.db74_descricao         = recordCGS.endereco
         and cadenderrua.db74_cadendermunicipio = iCodigoMunicipio;
      
      if not found then

        perform fc_debug('Rua não encontrada, será incluído uma nova.');
        
        iCodigoRua := nextval('cadenderrua_db74_sequencial_seq');
        
        insert into cadenderrua (
          db74_sequencial,
          db74_descricao,
          db74_cadendermunicipio
        ) values (
          iCodigoRua,
          recordCGS.endereco,
          iCodigoMunicipio
        );
      end if;
                
      --
      -- Verfica se a rua informada já está vinculada a uma 
      --
      perform cadenderruaruastipo.db85_sequencial from cadenderruaruastipo where cadenderruaruastipo.db85_cadenderrua = iCodigoRua;
          
      if not found then
        perform fc_debug('Vinculando RUA ao TIPO DE RUA');

        insert into cadenderruaruastipo (
          db85_sequencial,
          db85_cadenderrua,
          db85_ruastipo
        ) values (
          nextval('cadenderruaruastipo_db85_sequencial_seq'),
          iCodigoRua,
          3
        );
      end if;
             

      --
      -- Vinculação do BAIRRO com a RUA
      --
      select cadenderbairrocadenderrua.db87_sequencial
        into iCodigoBairroRua
        from cadenderbairrocadenderrua
       where cadenderbairrocadenderrua.db87_cadenderrua    = iCodigoRua
         and cadenderbairrocadenderrua.db87_cadenderbairro = iCodigoBairro;

      if not found then

        perform fc_debug('Não existe vinculação entre BAIRRO e RUA, criando nova...');

        iCodigoBairroRua := nextval('cadenderbairrocadenderrua_db87_sequencial_seq');

        insert into cadenderbairrocadenderrua (
          db87_sequencial,
          db87_cadenderrua,
          db87_cadenderbairro
        ) values (
          iCodigoBairroRua,
          iCodigoRua,
          iCodigoBairro
        );

      end if;

      --
      -- Vinculação da RUA com o LOCAL
      -- 
      select cadenderlocal.db75_sequencial
        into iCodigoLocal
        from cadenderlocal
       where cadenderlocal.db75_cadenderbairrocadenderrua = iCodigoBairroRua;

      if not found then
        
        perform fc_debug('Não existe vinculação do LOCAL com a RUA, incluindo...');

        iCodigoLocal := nextval('cadenderlocal_db75_sequencial_seq');

        insert into cadenderlocal (  
          db75_sequencial,
          db75_cadenderbairrocadenderrua,
          db75_numero
        ) values (
          iCodigoLocal,
          iCodigoBairroRua,
          recordCGS.numero     
        );
      end if;
    ----------------------------------------------- FINAL TRATAMENTO DOS DADOS DA RUA -------------------------------------------------
   

    ------------------------------------------------- INICIO DO TRATAMENTO DO ENDERECO ------------------------------------------------
      --
      -- Inserindo novo endereço
      --
      iCodigoEndereco := nextval('endereco_db76_sequencial_seq');
      
      perform fc_debug('Novo endereco:  ' || iCodigoEndereco);       
      perform fc_debug(' - Complemento: ' || recordCGS.complemento);       
      perform fc_debug(' - CEP........: ' || recordCGS.cep);       

      insert into endereco (
        db76_sequencial,
        db76_cadenderlocal,
        db76_complemento,
        db76_cep
      ) values (
        iCodigoEndereco,
        iCodigoLocal,
        recordCGS.complemento,
        recordCGS.cep
      );

      --
      -- Vinculando endereço com o CGS
      -- 
      perform fc_debug('Vinculando Código do CGS com o Endereço.');

      insert into cgs_undendereco (
        sd109_endereco,
        sd109_cgs_und
      ) values (
        iCodigoEndereco,
        iCodigoCGS
      );
    -------------------------------------------------- FINAL DO TRATAMENTO DO ENDERECO ------------------------------------------------
  else  
  --------------------------------- Se ja exisitir na cgs_undendereco verifica mudanças ID.000001------------------------------------
    
    select cadenderrua.db74_sequencial        as codigoRua,
           cadenderrua.db74_descricao         as descricaoRua,
           
           cadenderlocal.db75_numero          as codigoLocal,
           
           cadenderbairro.db73_sequencial     as codigoBairro,
           cadenderbairro.db73_descricao      as descricaoBairro,
           
           cadendermunicipio.db72_sequencial  as codigoMunicipio,
           cadendermunicipio.db72_descricao   as descricaoMunicipio,

           cadenderestado.db71_sequencial     as codigoEstado,
           cadenderestado.db71_descricao      as descricaoEstado,
           cadenderestado.db71_sigla          as siglaEstado,
           
           endereco.db76_sequencial           as codigoEndereco,
           endereco.db76_cep                  as cep,
           endereco.db76_pontoref             as pontoReferencia,
           endereco.db76_condominio           as numeroCondominio,
           endereco.db76_loteamento           as numeroLoteamento,
           endereco.db76_caixapostal          as numeroCaixaPostal,
           endereco.db76_complemento          as complemento
      into rEndereco
      from endereco 
           inner join cadenderlocal             on cadenderlocal.db75_sequencial             = endereco.db76_cadenderlocal
           inner join cadenderbairrocadenderrua on cadenderbairrocadenderrua.db87_sequencial = cadenderlocal.db75_cadenderbairrocadenderrua
           inner join cadenderrua               on cadenderrua.db74_sequencial               = cadenderbairrocadenderrua.db87_cadenderrua
           inner join cadenderbairro            on cadenderbairro.db73_sequencial            = cadenderbairrocadenderrua.db87_cadenderbairro
           inner join cadendermunicipio         on cadendermunicipio.db72_sequencial         = cadenderrua.db74_cadendermunicipio
           inner join cadenderestado            on cadenderestado.db71_sequencial            = cadendermunicipio.db72_cadenderestado 
     where db76_sequencial = iCodigoEndereco;
             
    if not found then
      perform fc_debug('Não foi possivel recuperar todos os dados para o Endereço: ' || iCodigoEndereco || ' do CGS ' || iCodigoCGS);
      return null;
    end if;
                        
    ------------------------------------------------- INICIO Verificação de Mudança de ESTADO -----------------------------------------------
      select cadenderestado.db71_sequencial
        into iCodigoEstado
        from cadenderestado 
       where cadenderestado.db71_sigla = case when rEndereco.siglaEstado != recordCGS.estado_uf -- Caso estado informado seja diferente
                                              then recordCGS.estado_uf                          -- Utiliza o estado da Operação do Banco
                                              else rEndereco.siglaEstado end;                       -- Caso contrário o do endereço cadastrado
      
      if not found then 

        perform fc_debug(
          'Nenhum estado cadastrado com a sigla: ' || 
          case when rEndereco.siglaEstado != recordCGS.estado_uf
               then recordCGS.estado_uf                         
               else rEndereco.siglaEstado end
        ); 

        return null;
      end if;
    -------------------------------------------------- FINAL Verificação de Mudança de ESTADO -----------------------------------------------
           
    ------------------------------------------------ INICIO Verificação de Mudança de MUNICIPIO ---------------------------------------------
      -- 
      -- Se o municipio vazio atribui 0-Não Informaado e estado Default
      -- 
      iCodigoMunicipio := 0;
      iCodigoEstado    := recordParametrosEndereco.codigoPadraoEstado;

      -- 
      -- Verifica se houve mudança no municipio cadastrado 
      -- procurar pelo municipio se existe se não cadastrar
      -- 
      if (recordCGS.municipio != '') then

        select cadendermunicipio.db72_sequencial
          into iCodigoMunicipio
          from cadendermunicipio 
         where trim(cadendermunicipio.db72_descricao) = trim(recordCGS.municipio);
        
        if not found then 
          
          perform fc_debug('Vinculação do Municipio com o estado não exista, será criado uma nova...');

          iCodigoMunicipio := nextval('cadendermunicipio_db72_sequencial_seq');

          insert into cadendermunicipio (
            db72_sequencial,
            descricaoPadraoMunicipio,
            db72_cadenderestado
          ) values (
            iCodigoMunicipio,
            recordCGS.municipio,
            iCodigoEstado
          );
          
        end if;
           
      end if;/*Fim do if do municipio*/
    ------------------------------------------------- FINAL Verificação de Mudança de MUNICIPIO ---------------------------------------------
    
    ------------------------------------------------- INICIO Verificação de Mudança de BAIRRO -----------------------------------------------
            iCodigoBairro := 0; -- Padrão: Não informado
              
            --
            -- Verifica se houve mudança no municipio cadastrado 
            -- procura pelo municipio 
            -- se existe se não cadastra
            -- 
            if (recordCGS.bairro != '') then

              select cadenderbairro.db73_sequencial
                into iCodigoBairro
                from cadenderbairro 
               where trim(cadenderbairro.db73_descricao)   = trim(recordCGS.bairro)
                 and cadenderbairro.db73_cadendermunicipio = iCodigoMunicipio;
              
              if not found then 
                
                perform fc_debug('Vinculação com bairro e municipio não existe, uma nova será criada.');
                
                iCodigoBairro := nextval('cadenderbairro_db73_sequencial_seq');

                insert into cadenderbairro (
                  db73_sequencial,                                            
                  db73_descricao,                                            
                  db73_cadendermunicipio
                ) values (
                  iCodigoBairro,
                  recordCGS.bairro,
                  iCodigoMunicipio  
                );
              end if;
                 
            end if;
    -------------------------------------------------- FINAL Verificação de Mudança de BAIRRO -----------------------------------------------

    --------------------------------------------------- INICIO Verificação de Mudança de RUA ------------------------------------------------
        if (recordCGS.endereco ='') then

          perform fc_debug('Campo endereço não foi informado.');
          return null;
        end if;

        select cadenderrua.db74_sequencial
          into iCodigoRua
          from cadenderrua 
         where cadenderrua.db74_descricao         = recordCGS.endereco
           and cadenderrua.db74_cadendermunicipio = iCodigoMunicipio;
        
        if not found then 
          
          perform fc_debug('Vinculação entre Rua e Cidade não encontrada, uma nova será criada.');
          iCodigoRua := nextval('cadenderrua_db74_sequencial_seq');

          insert into cadenderrua (
            db74_sequencial,
            db74_descricao,
            db74_cadendermunicipio
          ) values (
            iCodigoRua,
            recordCGS.endereco,
            iCodigoMunicipio
          );
        end if;
    ---------------------------------------------------- FINAL Verificação de Mudança de RUA ------------------------------------------------

    ---------------------------------------------- INICIO Verificação de Mudança de TIPO DE RUA ---------------------------------------------

        perform db85_sequencial from cadenderruaruastipo where db85_cadenderrua = iCodigoRua;
        
        if not found then

          perform fc_debug('TIPO DE RUA não informado, criando um novo...');

          iCodigoRuasTipo := nextval('cadenderruaruastipo_db85_sequencial_seq');

          insert into cadenderruaruastipo (
            db85_sequencial,
            db85_cadenderrua,
            db85_ruastipo
          ) values (
            iCodigoRuasTipo,
            iCodigoRua,
            3
          );
        end if;
    ----------------------------------------------- FINAL Verificação de Mudança de TIPO DE RUA ---------------------------------------------

    ---------------------------------------------- INICIO Verificação de Mudança de BAIRO na RUA --------------------------------------------
        select cadenderbairrocadenderrua.db87_sequencial
          into iCodigoBairroRua
          from cadenderbairrocadenderrua
         where cadenderbairrocadenderrua.db87_cadenderrua    = iCodigoRua
           and cadenderbairrocadenderrua.db87_cadenderbairro = iCodigoBairro;

        if not found then
          
          if lRaise then
            raise notice 'Incluindo na BairroRua';
          end if;

          iCodigoBairroRua := nextval('cadenderbairrocadenderrua_db87_sequencial_seq'); 

          insert into cadenderbairrocadenderrua (
            db87_sequencial,
            db87_cadenderrua,
            db87_cadenderbairro
          ) values (
            iCodigoBairroRua,
            iCodigoRua,
            iCodigoBairro
          );
        end if;
    ----------------------------------------------- FINAL Verificação de Mudança de BAIRO na RUA --------------------------------------------

    ------------------------------------------------- INICIO Verificação de Mudança de LOCAL ------------------------------------------------

        select cadenderlocal.db75_sequencial
          into iCodigoLocal
          from cadenderlocal 
         where cadenderlocal.db75_cadenderbairrocadenderrua = iCodigoBairroRua
           and cadenderlocal.db75_numero                    = cast(recordCGS.numero as text);
        
        if not found then
          
          iCodigoLocal := nextval('cadenderlocal_db75_sequencial_seq');

          insert into cadenderlocal (
            db75_sequencial,
            db75_cadenderbairrocadenderrua,
            db75_numero
          ) values (
            iCodigoLocal,
            iCodigoBairroRua,
            recordCGS.numero
          );
        end if;
    -------------------------------------------------  FINAL Verificação de Mudança de LOCAL ------------------------------------------------

    ------------------------------------------------ INICIO Verificação de Mudança de Endereco ----------------------------------------------

        select count(*) 
          into iNumCGSEndereco
          from cgs_undendereco 
         where sd109_endereco = iCodigoEndereco 
        having count(*) > 1;

        delete from cgs_undendereco where sd109_cgs_und = iCodigoCGS;
              

        if (iNumCGSEndereco > 0 and (recordCGS.complemento != rEndereco.complemento)) then -- ID.000002

          perform fc_debug('Existe mais de um CGS no mesmo endereco inserindo endereco novo');

          iCodigoEndereco := nextval('endereco_db76_sequencial_seq');

          insert into endereco (
            db76_sequencial,
            db76_cadenderlocal,
            db76_complemento,
            db76_caixapostal,
            db76_loteamento,
            db76_condominio,
            db76_pontoref,
            db76_cep
          ) values (
            iCodigoEndereco,
            iCodigoLocal,
            recordCGS.complemento,
            rEndereco.numeroCaixaPostal,
            rEndereco.numeroLoteamento,
            rEndereco.numeroCondominio,
            rEndereco.pontoReferencia,
            recordCGS.cep
          );
        else -- ID.000002
          
          perform db76_sequencial
             from endereco
            where db76_sequencial    = iCodigoEndereco
              and db76_cadenderlocal = iCodigoLocal;

          if not found then

            iCodigoEndereco := nextval('endereco_db76_sequencial_seq');

            insert into endereco (
              db76_sequencial,
              db76_cadenderlocal,
              db76_complemento,
              db76_caixapostal,
              db76_loteamento,
              db76_condominio,
              db76_pontoref,
              db76_cep
            ) values (
              iCodigoEndereco,
              iCodigoLocal,
              recordCGS.complemento,
              rEndereco.numeroCaixaPostal,
              rEndereco.numeroLoteamento,
              rEndereco.numeroCondominio,
              rEndereco.pontoReferencia,
              recordCGS.cep
            );


          else 

            update endereco 
               set db76_cadenderlocal = iCodigoLocal,
                   db76_complemento   = recordCGS.complemento,
                   db76_cep           = recordCGS.cep
             where db76_sequencial    = rEndereco.codigoEndereco;
          end if;

        end if; -- ID.000002

        insert into cgs_undendereco(
          sd109_endereco,
          sd109_cgs_und
        ) values (
          iCodigoEndereco,
          iCodigoCGS
        );
    ------------------------------------------------  FINAL Verificação de Mudança de Endereco ----------------------------------------------

  end if;
  -------------------------------------------------------------- FIM ID.000001---------------------------------------------------------------
  return null;  

end;

$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS tg_cgs_endereco_inc_alt on cgs_und;
CREATE TRIGGER tg_cgs_endereco_inc_alt AFTER UPDATE OR INSERT ON cgs_und FOR EACH ROW EXECUTE PROCEDURE fc_cgs_endereco_inc_alt();--
-- Funcao que gera registros no Financeiro de acordo com calculo
--
-- Parametros: 1 - Ano de Referencia
--             2 - Mes de Referencia
--             3 - Matricula
--             4 - Numpre
--             5 - NumCgm
--             6 - Receita
--             7 - Historico
--             8 - Valor


--drop function fc_agua_calculogerafinanceiro(integer, integer, integer, integer, integer, integer, integer, float8);
create or replace function fc_agua_calculogerafinanceiro(integer, integer, integer, integer, integer, integer, integer, integer, float8) returns bool as 
$$
declare
  -- Parametros
  iAno     alias for $1;
  iMes     alias for $2;
  iMatric   alias for $3;
  iNumpreOld alias for $4;
  iNumpre   alias for $5;
  iNumCgm   alias for $6;
  iReceit   alias for $7;
  iCodHist   alias for $8;
  nValor     alias for $9;
  -- Variaveis
  rArrecad          record;
  rArrecant         record;
  rArreMatric       record;
  rArreNumCgm       record;
  rRecibosGerados   record;
  rsResulDescArreca record;
  rAguaDescHist     record;
  dDataVenc         date;
  dDataOper         date;
  iNumTot            integer := 12; -- Total de Parcelas
  iNumDig           integer := 0;
  iTipo             integer := 0;
  iTipojm           integer := 0;
  iIdOcor           integer := 0;
  iIdOcorMatric     integer := 0;
  fTotalDescDebito  float;
  lRaise            boolean default false;
begin

  lRaise := ( case when fc_getsession('DB_debugon') is null then false else true end );   

  -- Busca Data de Vencimento
  dDataVenc := fc_agua_datavencimento(iAno, iMes, iMatric);

  if dDataVenc is null then
    return false;
  end if;

  --dDataOper := to_date(''01-''||to_char(iMes,''00'')||''-''||iAno, ''DD-MM-YYYY'');
  if fc_getsession('DB_datausu') = '' or fc_getsession('DB_datausu') is null then
    raise exception 'Variavel de sessao[DB_datausu] nao encontrada';
  end if;
  
  dDataOper := cast(fc_getsession('DB_datausu') as date);
  
  iTipo := fc_agua_confarretipo(iAno);
  
  -- Verifica se NumpreOld nao foi Pago/Cancelado/Importado pra Divida
  -- Verifica tambem se numpre não possui pagamento parcial
  perform * from ( 
    select k00_numpre
      from arrecant
     where k00_numpre = iNumpreOld
       and k00_numpar = iMes
       and k00_tipo   = iTipo
       and k00_receit = iReceit
    union all
    select k10_numpre
      from divold
     where k10_numpre  = iNumpreOld
       and k10_numpar  = iMes
       and k10_receita = iReceit
  ) as x;
  
  if not found and nValor > 0 then
    
    --deleta da arreold os registros antigos
    delete from arreold 
     where k00_numpre = iNumpreOld
       and k00_numpar = iMes
       and k00_tipo   = iTipo
       and k00_receit = iReceit;
  
    -- Insere Registro no Arrecad
    insert into arrecad(
      k00_numpre , k00_numpar ,
      k00_numcgm , k00_dtoper ,
      k00_receit , k00_hist   ,
      k00_valor  , k00_dtvenc ,
      k00_numtot , k00_numdig ,
      k00_tipo   , k00_tipojm 
    ) values (
      iNumpre , 
      iMes ,
      iNumCgm , 
      dDataOper ,
      iReceit , 
      3999 + iMes ,
      nValor  , 
      dDataVenc ,
      iNumTot , 
      iNumDig ,
      iTipo , 
      iTipojm
    );
    
    /**
     * retorna para arrecad e arrehist os descontos lançados 
    */
    fTotalDescDebito := 0;
    
    for rsResulDescArreca IN ( SELECT *
                                 FROM aguadescarrecad
                                WHERE x35_numpre = iNumpre
                                  AND x35_numpar = iMes
                                  AND x35_numcgm = iNumCgm
                                  AND x35_receit = iReceit)
    loop
                                  
      /**
       * cancela desconto caso valor do desconto sejá maior do que o débito
       */
    
      fTotalDescDebito := fTotalDescDebito + (rsResulDescArreca.x35_valor * -1);
  
      IF (fTotalDescDebito < nValor) THEN
  
        INSERT INTO arrecad(k00_numpre, k00_numpar, k00_numcgm,
                            k00_dtoper, k00_receit, k00_hist  ,
                            k00_valor , k00_dtvenc, k00_numtot,
                            k00_numdig, k00_tipo  , k00_tipojm)
                    VALUES (rsResulDescArreca.x35_numpre, rsResulDescArreca.x35_numpar, rsResulDescArreca.x35_numcgm,
                            rsResulDescArreca.x35_dtoper, rsResulDescArreca.x35_receit, rsResulDescArreca.x35_hist  ,
                            rsResulDescArreca.x35_valor , rsResulDescArreca.x35_dtvenc, rsResulDescArreca.x35_numtot,
                            rsResulDescArreca.x35_numdig, rsResulDescArreca.x35_tipo  , rsResulDescArreca.x35_tipojm);
        
        SELECT INTO rAguaDescHist *
          FROM aguadescarrehist
               INNER JOIN arrehist ON arrehist.k00_numpre = aguadescarrehist.x36_numpre
                                  AND arrehist.k00_numpar = aguadescarrehist.x36_numpar
                                  AND arrehist.k00_hist   = aguadescarrehist.x36_hist
                                  AND arrehist.k00_dtoper = aguadescarrehist.x36_dtoper
                                  AND arrehist.k00_hora   = aguadescarrehist.x36_hora
         WHERE aguadescarrehist.x36_numpre = iNumpre
           AND aguadescarrehist.x36_numpar = iMes;      
           
        IF NOT found THEN
  
          INSERT INTO arrehist (k00_numpre , k00_numpar   , k00_hist      ,
                                k00_dtoper , k00_hora     , k00_id_usuario,
                                k00_histtxt, k00_limithist, k00_idhist)
          SELECT x36_numpre , x36_numpar   , x36_hist      ,
                 x36_dtoper , x36_hora     , x36_id_usuario,
                 x36_histtxt, x36_limithist, nextval('arrehist_k00_idhist_seq')
            FROM aguadescarrehist
           WHERE aguadescarrehist.x36_numpre = iNumpre
             AND aguadescarrehist.x36_numpar = iMes;
        
        END IF;
        
      ELSE 
      
        iIdOcor       := nextval('histocorrencia_ar23_sequencial_seq');
        iIdOcorMatric := nextval('histocorrenciamatric_ar25_sequencial_seq');
        
        INSERT INTO histocorrencia  
          (ar23_sequencial  , ar23_id_usuario, ar23_instit, ar23_modulo,
           ar23_id_itensmenu, ar23_data      , ar23_hora  , ar23_tipo  ,
           ar23_descricao   , ar23_ocorrencia)
         VALUES
          (iIdOcor, cast(fc_getsession('DB_id_usuario') as integer),
           cast(fc_getsession('DB_instit') as integer), cast(fc_getsession('DB_modulo') as integer),
           cast(fc_getsession('DB_itemmenu_acessado') as integer), TO_DATE(fc_getsession('DB_datausu'), 'YYYY-MM-DD'),
           TO_CHAR(CURRENT_TIMESTAMP, 'HH24:MI')                 , 2,           
           'CANCELAMENTO DE DESCONTO', 'Desconto lançado anteriormente sobre o Numpre '||iNumpre||
           ' é maior que o valor da Parcela Total: R$ '||nValor);
        
        INSERT INTO histocorrenciamatric (ar25_sequencial, ar25_matric, ar25_histocorrencia)
                                  VALUES (iIdOcorMatric, iMatric, iIdOcor);
      
      END IF;
    
    END loop;
    
    -- Gera ArreMatric
    select  into rArreMatric
        *
    from   arrematric
    where  k00_numpre = iNumpre;
  
    if not found then
      insert into arrematric (
        k00_numpre,
        k00_matric
      ) values (
        iNumpre,
        iMatric
      );
    end if;
  end if;

  for rRecibosGerados in select distinct recibopaga.k00_numnov,
            recibopaga.k00_dtoper,
            recibopaga.k00_dtpaga
           from arrecad
            inner join recibopaga on recibopaga.k00_numpre = arrecad.k00_numpre
                         and recibopaga.k00_numpar = arrecad.k00_numpar
          where arrecad.k00_numpre = iNumpre
  loop

    delete from recibopaga where k00_numnov = rRecibosGerados.k00_numnov;
    perform fc_recibo(rRecibosGerados.k00_numnov, rRecibosGerados.k00_dtoper, rRecibosGerados.k00_dtpaga, extract(year from rRecibosGerados.k00_dtpaga)::integer);
  end loop;
 
  return true;
end;
$$ language 'plpgsql';--
-- Funcao que efetua calculo/re-calculo de uma matricula especifica
--
-- Parametros: 1 - Ano de Referencia
--             2 - Mes de Referencia
--             3 - Matricula
--             4 - Tipo de movimentacao aguacalc(1 - Parcial, 2 - Geral, 3 Coletor)
--             5 - Troca Numpre? (Qdo houver recalculo)
--             6 - Gera Financeiro? (arrecad, arrematric, arrenumcgm, arreold)
--
SET check_function_bodies TO off; 

drop function if exists fc_agua_calculoparcial(integer, integer, integer, integer, bool, bool);

create or replace function fc_agua_calculoparcial(integer, integer, integer, integer, bool, bool) returns varchar as 
$$
declare
  ---------------------- Parametros
  iAno             alias for $1;
  iMes             alias for $2;
  iMatric          alias for $3;
  iTipo            alias for $4;
  lTrocaNumpre     alias for $5;
  lGeraFinanceiro  alias for $6;
  ----------------------- Variaveis
  sMemoria         text   := ''; -- Campo que contera a memoria de Calculo da Matricula
  sHora            text   := '';
  dData            date   := null;
  nConsumo         float8 := 0;
  nExcesso         float8 := 0;
  nSaldoComp       float8 := 0;
  lTaxaBasica      bool   := false;
  rCalculo         record;  
  rAguaBase        record;
  rCondominio      record;
  iUsuario         integer;
  iInstit          integer;
  iEconomias       integer;
  iConsumo         integer;
  iNumpre          integer;
  iNumpreOld       integer;
  iCodCalc         integer;
  iCondominio      integer;
  iApto            integer;
  iTipoImovel      integer;
  nValorTemp       float8 := 0;
  nValorExcesso    float8 := 0;
  nPercentualIsencao   float4 := 0;
  lAguaLigada      bool := false;
  lSemAgua         bool := false;
  lEsgotoLigado    bool := false;
  lSemEsgoto       bool := false;
  lRecalculo       bool := false;
  lGeraArrecad     bool := true;
  lRaise           bool := true;
  nAreaConstr      float4 := 0;
  lGeraDesconto    bool := false;
  dDataVenc         date;
  lAtivaPgtoParcial boolean default false;
  lPossuiPagamentoParcialDebito boolean default false;
  
  rsAreaAlterada record;
  
begin
  
  dDataVenc := fc_agua_datavencimento(iAno, iMes, iMatric);
  -- 1) Verifica se Existe Hidrometro Instalado
  if fc_agua_hidrometroinstalado(iMatric) = false then
    lTaxaBasica := true;
  else
    -- 1.1) Verifica Consumo e Excesso
    nConsumo   := fc_agua_consumo(iAno, iMes, iMatric);
    nExcesso   := fc_agua_excesso(iAno, iMes, iMatric);
    nSaldoComp := fc_agua_saldocompensado(iAno, iMes, iMatric);
    
    if (nConsumo + nExcesso) = 0 then 
      -- E R R O !!!!
      lTaxaBasica := true;
    end if;
    
  end if;
  
  -- Busca dados da Matricula
  select * 
    into rAguaBase
    from aguabase
   where x01_matric = iMatric;

  -- 2) Verifica dados para Calculo
  lGeraArrecad  := lGeraFinanceiro;
  iEconomias    := rAguaBase.x01_qtdeconomia;
  iConsumo      := fc_agua_consumocodigo(iAno, iMatric, rAguaBase.x01_zona);
  lAguaLigada   := fc_agua_agualigada(iAno, iMatric);
  lEsgotoLigado := fc_agua_esgotoligado(iAno, iMatric);
  lSemAgua      := fc_agua_semagua(iAno, iMatric);
  lSemEsgoto    := fc_agua_semesgoto(iAno, iMatric);
  nAreaConstr   := fc_agua_areaconstr(iMatric);  
  --iNumpre       := fc_agua_numpre();
  iCodCalc      := fc_agua_calculocodigo(iAno, iMes, iMatric);

  if iConsumo is null then
    return '2 - NÃO FOI POSSÍVEL ENCONTRAR CONFIGURAÇÃO DE CONSUMO PARA MATRÍCULA '||iMatric||' EXERCÍCIO '||iAno||' ZONA '||rAguaBase.x01_zona;
  end if;
  
  
  if iCodCalc is not null then
    lRecalculo := true;
  end if;
  
  -- 3) Verifica se eh matricula de condominio e apto
  iCondominio := fc_agua_condominiocodigo(iMatric);
  iApto       := fc_agua_condominioapto(iMatric);
  
    raise notice 'lGeraArrecad (%) lAguaLigada (%) iApto (%) nConsumo (%) nExcesso (%)', lGeraArrecad, lAguaLigada, iApto, nConsumo, nExcesso;

  -- 4) Gera AguaCalc
  --    . Verifica se eh um Novo Calculo 
  dData      := TO_DATE(fc_getsession('DB_datausu'), 'YYYY-MM-DD');
  sHora      := TO_CHAR(CURRENT_TIMESTAMP, 'HH24:MI');
  iUsuario   := CAST(fc_getsession('DB_id_usuario') as integer);
  iInstit    := CAST(fc_getsession('DB_instit') as integer);

  if lRecalculo = false then
    iNumpre    := fc_agua_numpre();
    iNumpreOld := iNumpre;
    iCodCalc   := nextval('aguacalc_x22_codcalc_seq');
  
    insert into aguacalc(
      x22_codcalc,
      x22_codconsumo,
      x22_exerc,
      x22_mes,
      x22_matric,
      x22_area,
      x22_numpre,
      x22_tipo,
      x22_data,
      x22_hora,
      x22_usuario
    ) values (
      iCodCalc,
      iConsumo,
      iAno,
      iMes,
      iMatric,
      nAreaConstr,
      iNumpre,
      iTipo,
      dData,
      sHora,
      iUsuario
    );
  else
    --if lTrocaNumpre = false then
      iNumpreOld := fc_agua_calculonumpre(iAno, iMes, iMatric);
      iNumpre    := iNumpreOld;
    --end if;
    
    update aguacalc 
       set x22_codconsumo = iConsumo,
           x22_area       = nAreaConstr,
           x22_numpre     = iNumpre,
           x22_tipo       = iTipo,
           x22_data       = dData,
           x22_hora       = sHora,
           x22_usuario    = iUsuario
     where x22_codcalc    = iCodCalc;

    delete 
      from aguacalcval 
     where x23_codcalc = iCodCalc;

  end if;
  
  select k03_pgtoparcial
    into lAtivaPgtoParcial
    from numpref
   where k03_instit = iInstit
     and k03_anousu = iAno;
  
  if lAtivaPgtoParcial is true then      
          
    select true
      into lPossuiPagamentoParcialDebito
      from arreckey
           inner join abatimentoarreckey on k128_arreckey   = k00_sequencial
           inner join abatimento         on k125_sequencial = k128_abatimento 
     where k00_numpre = iNumpre
       and k00_numpar = iMes;
       
    if lPossuiPagamentoParcialDebito is true then
        return '1 - Calculo não efetuado, matricula possui débito com pagamento parcial.';  
    end if;
       
      
  end if;
  
  -- Se for um Apto de um Condominio e tiver agua desligada,
  -- força nao gerar financeiro
  if (lAguaLigada = false) and (iApto is not null) then
    lGeraArrecad := false;

    perform fc_agua_calculogeraarreold(
      iAno,
      iMes,
      iNumpreOld);
  end if;

  if lGeraArrecad = true then
  
    raise notice 'Ano(%), Mes(%), Numpre(%)', iAno, iMes, iNumpreOld;
      
    perform fc_agua_calculogeraarreold(
      iAno,
      iMes,
      iNumpreOld);
  end if;

  -- Busca Registros para Cálculo
  for rCalculo in 
    select aguaconsumo.*,
           aguaconsumorec.*,
           aguaconsumotipo.*,
           0::float8 as x99_valor,
           0::float8 as x99_valor_desconto
      from aguaconsumo
           inner join aguaconsumorec  on x19_codconsumo = x20_codconsumo
           inner join aguaconsumotipo on x20_codconsumotipo = x25_codconsumotipo
     where x19_codconsumo = iConsumo
  loop
    --
    -- Verifica se Esta Calculando Consumo de Agua
    --
    if  (rCalculo.x25_codconsumotipo = fc_agua_confconsumoagua()) and
      (lSemAgua = false) then
      --
      -- ATENCAO!! Multiplica pelo retorno da funcao que verifica
      --           a qtd de economias levando em conta o multiplicador
      --
      if (iCondominio is not null) then
      
        nValorTemp := fc_agua_calculatxapto(iAno, iCondominio, rCalculo.x25_codconsumotipo);
      
      else 
      
        nValorTemp := rCalculo.x20_valor * fc_agua_qtdeconomias(iMatric);
        
      end if;
    --
    -- .. ou Esgoto
    --
    elsif (rCalculo.x25_codconsumotipo = fc_agua_confconsumoesgoto()) and
          (lSemEsgoto = false) then
          
      if (iCondominio is not null) then
      
        nValorTemp := fc_agua_calculatxapto(iAno, iCondominio, rCalculo.x25_codconsumotipo);
      
      else
      
        nValorTemp := rCalculo.x20_valor * fc_agua_qtdeconomias(iMatric);
        
      end if;
      
    --
    -- .. ou Excesso
    --
    elsif (rCalculo.x25_codconsumotipo = fc_agua_confconsumoexcesso()) then
      if   ((iCondominio is not null) and (lAguaLigada = false)) or (lSemAgua = false) then
        if nExcesso > 0 then
          select coalesce(x50_valor_m3_excesso,0)
              into nValorExcesso
              from aguacalc
              inner join agualeitura                    on x21_mes = iMes  and x21_exerc = iAno and x21_status = 1
              inner join aguahidromatric                on x04_codhidrometro = x21_codhidrometro and x04_matric = iMatric
              inner join aguacoletorexportadadosleitura on x51_agualeitura   = x21_codleitura
              inner join aguacoletorexportadados        on x50_aguacoletorexportadados    = x51_aguacoletorexportadados
            where x22_codconsumo = iConsumo and x22_codcalc = iCodCalc and x50_contaimpressa = 1;
          if nValorExcesso <> 0 then
            nValorTemp   := nValorExcesso;
          else
            nValorTemp   := rCalculo.x20_valor * nExcesso;
          end if;
          nValorExcesso := 0;
          lGeraDesconto = true;
        else
          nValorTemp := 0;
        end if;
      else
        nValorTemp := 0;
      end if;
    else
      nValorTemp := 0;
    end if;
    
    -- Verifica Isencao
    nPercentualIsencao := fc_agua_percentualisencao(iAno, iMes, iMatric, rCalculo.x25_codconsumotipo);
    
    if nPercentualIsencao = 0 then
      rCalculo.x99_valor := nValorTemp;
    else
      rCalculo.x99_valor := round(nValorTemp - (nValorTemp * (nPercentualIsencao / 100)), 2);
    end if;

      raise notice '2 - Matricula=(%) Isencao=(%) Padrao=(%) Taxa=(%) Valor Calculado=(%) Tipo Consumo=(%)', 
        iMatric,
        nPercentualIsencao,
        rCalculo.x20_valor,
        rCalculo.x25_descr,
        rCalculo.x99_valor,
        rCalculo.x25_codconsumotipo;

    if rCalculo.x99_valor > 0 then
      insert into aguacalcval (
        x23_codcalc,
        x23_codconsumotipo,
        x23_valor
      ) values (
        iCodCalc,
        rCalculo.x25_codconsumotipo,
        rCalculo.x99_valor
      );
    end if;

    if lGeraArrecad = true then
      perform fc_agua_calculogerafinanceiro(
        iAno,
        iMes,
        iMatric,
        iNumpreOld,
        iNumpre,
        coalesce(rAguaBase.x01_numcgm, 0),
        rCalculo.x25_receit,
        rCalculo.x25_codhist,
        rCalculo.x99_valor);
    end if;
    
    if lGeraDesconto = true and nSaldoComp > 0 then
      --caso haja saldos compensados repete operação para gerar o desconto
      lGeraDesconto := false;
      
      nValorTemp := -(rCalculo.x20_valor * nSaldoComp);
      
      if nPercentualIsencao = 0 then
        rCalculo.x99_valor_desconto := nValorTemp;
      else
        rCalculo.x99_valor_desconto := round(nValorTemp - (nValorTemp * (nPercentualIsencao / 100)), 2);
      end if;
  
        raise notice '1 - Matricula=(%) Isencao=(%) Padrao=(%) Taxa=(%) Valor Calculado=(%) Tipo Consumo=(%)', 
          iMatric,
          nPercentualIsencao,
          rCalculo.x20_valor,
          rCalculo.x25_descr,
          rCalculo.x99_valor_desconto,
          rCalculo.x25_codconsumotipo;
  
      if lGeraArrecad = true then
      
        perform * from (
                        select k00_numpre
                          from arrecant
                         where k00_numpre = iNumpre
                           and k00_numpar = iMes
                           and k00_tipo   = 137
                           and k00_receit = rCalculo.x25_receit
                        union all
                        select k10_numpre
                          from divold
                         where k10_numpre  = iNumpre
                           and k10_numpar  = iMes
                           and k10_receita = rCalculo.x25_receit
                      ) as x;
                  
        if not found then

          delete from arrehist where k00_numpre = iNumpre and k00_numpar = iMes and k00_hist in (970, 918) and k00_histtxt LIKE 'VOLUME DE %GUA COMPENSADO%';

          insert into arrehist
          ( k00_numpre, k00_numpar, k00_hist, k00_dtoper, k00_hora, k00_id_usuario, k00_histtxt, k00_limithist, k00_idhist )
          values 
          ( iNumpre, iMes, 970, dData, sHora, iUsuario, 'VOLUME DE ÁGUA COMPENSADO - VALOR DESCONTO = '||abs(rCalculo.x99_valor_desconto), null, nextval('arrehist_k00_idhist_seq') );

          insert into arrecad
          ( k00_numpre, k00_numpar, k00_numcgm, k00_dtoper, k00_receit, k00_hist, k00_valor, k00_dtvenc, k00_numtot, k00_numdig, k00_tipo, k00_tipojm )
          values
          ( iNumpre, iMes, coalesce(rAguaBase.x01_numcgm, 0), dData, rCalculo.x25_receit, 970, rCalculo.x99_valor_desconto,dDataVenc, 12, 0, 137, 0 );
          
          if rCalculo.x99_valor - abs(rCalculo.x99_valor_desconto) = 0 then
          
            insert into cancdebitos ( k20_codigo, k20_cancdebitostipo, k20_instit, k20_descr, k20_hora, k20_data, k20_usuario )
            values ( ( select nextval('cancdebitos_k20_codigo_seq') ), 2, iInstit, 'CANCELAMENTO POR COMPENSAÇÃO DE CRÉDITO', sHora, dData, iUsuario );
            
            insert into cancdebitosconcarpeculiar ( k72_sequencial, k72_cancdebitos, k72_concarpeculiar ) 
            values ( ( select nextval('cancdebitosconcarpeculiar_k72_sequencial_seq') ), ( select last_value from cancdebitos_k20_codigo_seq ),'000' );
            
            insert into cancdebitosreg ( k21_sequencia, k21_codigo, k21_numpre, k21_numpar, k21_receit, k21_data, k21_hora, k21_obs ) 
            values ( ( select nextval('cancdebitosreg_k21_sequencia_seq') ), 
                     ( select last_value from cancdebitos_k20_codigo_seq ), 
                     iNumpre,
                     iMes,
                     rCalculo.x25_receit,
                     dData, 
                     sHora,
                     'TAXA DE EXCESSO INTEGRALMENTE COMPENSADA, SALDO COMPENSADO: '||nSaldoComp||'m³, VALOR DESCONTO R$'||abs(rCalculo.x99_valor_desconto) );
                     
            insert into cancdebitosproc ( k23_codigo, k23_data, k23_hora, k23_usuario, k23_obs, k23_cancdebitostipo ) 
            values ( ( select nextval('cancdebitosproc_k23_codigo_seq') ), 
                     dData, 
                     sHora, 
                     iUsuario, 
                     'TAXA DE EXCESSO INTEGRALMENTE COMPENSADA, SALDO COMPENSADO: '||nSaldoComp||'m³, VALOR DESCONTO R$'||abs(rCalculo.x99_valor_desconto), 
                     2 );
                     
            insert into cancdebitosprocconcarpeculiar ( k74_sequencial, k74_cancdebitosproc, k74_concarpeculiar ) 
            values ( ( select nextval('cancdebitosprocconcarpeculiar_k74_sequencial_seq') ), ( select last_value from cancdebitosproc_k23_codigo_seq ), '000' );
            
            insert into arrecant ( k00_numpre, k00_numpar, k00_numcgm, k00_dtoper, k00_receit, k00_hist, k00_valor, k00_dtvenc, k00_numtot, k00_numdig, k00_tipo, k00_tipojm )
            select k00_numpre,
                   k00_numpar,
                   k00_numcgm,
                   k00_dtoper,
                   k00_receit,
                   k00_hist,
                   k00_valor,
                   k00_dtvenc,
                   k00_numtot,
                   k00_numdig,
                   k00_tipo,
                   k00_tipojm
              from arrecad
             where k00_numpre = iNumpre
               and k00_numpar = iMes
               and k00_receit = rCalculo.x25_receit;
               
            delete
              from arrecad
             where k00_numpre = iNumpre
               and k00_numpar = iMes
               and k00_receit = rCalculo.x25_receit;
               
            insert into cancdebitosprocreg ( k24_sequencia, k24_codigo, k24_cancdebitosreg, k24_vlrhis, k24_vlrcor, k24_juros, k24_multa, k24_desconto ) 
            values ( ( select nextval('cancdebitosprocreg_k24_sequencia_seq') ), 
                     ( select last_value from cancdebitosproc_k23_codigo_seq ), 
                     ( select last_value from cancdebitosreg_k21_sequencia_seq ), 
                     0, 0, 0, 0, 0 );

          end if;
          
        end if;
        
      end if;
      
    end if;
    
  end loop;
  
  raise notice 'Economias=(%) Agua Ligada=(%) Esgoto Ligado (%) Recalculo=(%) CodCalc=(%) Numpre=(%)',
      iEconomias,
      lAguaLigada,
      lEsgotoLigado,
      lRecalculo,
      iCodCalc,
      iNumpre;
  
  return '1 - CALCULO CONCLUIDO COM SUCESSO ';
end;
$$ language 'plpgsql';--
-- Funcao que retorna o consumo total do Ano/Mes de referencia 
--
-- Parametros: 1 - Ano de Referencia
--             2 - Mes de Referencia
--             3 - Matricula
--
SET check_function_bodies TO off;
create or replace function fc_agua_consumo(integer, integer, integer) returns float8 as
$$
  select coalesce(sum(x21_consumo),0)
      from agualeitura
         inner join aguahidromatric on x21_codhidrometro = x04_codhidrometro
     where x21_exerc  = $1
     and x21_mes    = $2
     and x04_matric = $3
     and x21_status = 1 -- Leitura Ativa;
$$
language 'sql';

--
-- Funcao que retorna o consumo total do Ano/Mes de referencia 
--
-- Parametros: 1 - Ano de Referencia
--             2 - Mes de Referencia
--             3 - Matricula
--             4 - Codigo da Leitura a Desconsiderar
--
create or replace function fc_agua_consumo(integer, integer, integer, integer) returns float8 as '
    select      coalesce(sum(x21_consumo),0)
    from        agualeitura
    inner join  aguahidromatric
    on          x21_codhidrometro = x04_codhidrometro
    where       x21_exerc  = $1
    and         x21_mes    = $2
    and     x21_codleitura <> $4
    and     x21_status = 1
    and         x04_matric = $3;
' language 'sql';insert into db_versaoant (db31_codver,db31_data) values (368, current_date);
select setval ('db_versaousu_db32_codusu_seq',(select max (db32_codusu) from db_versaousu));
select setval ('db_versaousutarefa_db28_sequencial_seq',(select max (db28_sequencial) from db_versaousutarefa));
select setval ('db_versaocpd_db33_codcpd_seq',(select max (db33_codcpd) from db_versaocpd));
select setval ('db_versaocpdarq_db34_codarq_seq',(select max (db34_codarq) from db_versaocpdarq));create table bkp_db_permissao_20160622_171732 as select * from db_permissao;
create temp table w_perm_filhos as 
select distinct 
       i.id_item        as filho, 
       p.id_usuario     as id_usuario, 
       p.permissaoativa as permissaoativa, 
       p.anousu         as anousu, 
       p.id_instit      as id_instit, 
       m.modulo         as id_modulo  
  from db_itensmenu i  
       inner join db_menu      m  on m.id_item_filho = i.id_item 
       inner join db_permissao p  on p.id_item       = m.id_item_filho 
                                 and p.id_modulo     = m.modulo 
 where coalesce(i.libcliente, false) is true;

create index w_perm_filhos_in on w_perm_filhos(filho);

create temp table w_semperm_pai as 
select distinct m.id_item       as pai, m.id_item_filho as filho 
  from db_itensmenu i 
       inner join db_menu            m  on m.id_item   = i.id_item 
       left  outer join db_permissao p  on p.id_item   = m.id_item 
                                       and p.id_modulo = m.modulo 
 where p.id_item is null 
   and coalesce(i.libcliente, false) is true;
create index w_semperm_pai_in on w_semperm_pai(filho);
insert into db_permissao (id_usuario,id_item,permissaoativa,anousu,id_instit,id_modulo) 
select distinct wf.id_usuario, wp.pai, wf.permissaoativa, wf.anousu, wf.id_instit, wf.id_modulo 
  from w_semperm_pai wp 
       inner join w_perm_filhos wf on wf.filho = wp.filho 
       where not exists (select 1 from db_permissao p 
                    where p.id_usuario = wf.id_usuario 
                      and p.id_item    = wp.pai 
                      and p.anousu     = wf.anousu 
                      and p.id_instit  = wf.id_instit 
                      and p.id_modulo  = wf.id_modulo); 
delete from db_permissao
 where not exists (select a.id_item 
                     from db_menu a 
                    where a.modulo = db_permissao.id_modulo 
                      and (a.id_item       = db_permissao.id_item or 
                           a.id_item_filho = db_permissao.id_item) );
delete from db_itensfilho    
 where not exists (select 1 from db_arquivos where db_arquivos.codfilho = db_itensfilho.codfilho);

CREATE FUNCTION acerta_permissao_hierarquia() RETURNS varchar AS $$ 

 declare  

   i integer default 1; 

   BEGIN 

  while i < 5 loop   

    insert into db_permissao select distinct 
                                 db_permissao.id_usuario, 
                                 db_menu.id_item, 
                                 db_permissao.permissaoativa, 
                                 db_permissao.anousu, 
                                 db_permissao.id_instit, 
                                 db_permissao.id_modulo 
                            from db_permissao 
                                 inner join db_menu on db_menu.id_item_filho = db_permissao.id_item 
                                                   and db_menu.modulo        = db_permissao.id_modulo 
                           where not exists ( select 1 
                                                from db_permissao as p 
                                               where p.id_item    = db_menu.id_item 
                                                 and p.id_usuario = db_permissao.id_usuario 
                                                 and p.anousu     = db_permissao.anousu 
                                                 and p.id_instit  = db_permissao.id_instit 
                                                 and p.id_modulo  = db_permissao.id_modulo );

  i := i+1; 

 end loop;

return 'Processo concluido com sucesso!';
END; 
$$ LANGUAGE 'plpgsql' ;

select acerta_permissao_hierarquia();
drop function acerta_permissao_hierarquia();create or replace function fc_executa_ddl(text) returns boolean as $$ 
  declare  
    sDDL     alias for $1;
    lRetorno boolean default true;
  begin   
    begin 
      EXECUTE sDDL;
    exception 
      when others then 
        raise info 'Error Code: % - %', SQLSTATE, SQLERRM;
        lRetorno := false;
    end;  
    return lRetorno;
  end; 
  $$ language plpgsql ;

  select fc_executa_ddl('ALTER TABLE '||quote_ident(table_schema)||'.'||quote_ident(table_name)||' ENABLE TRIGGER ALL;') 
  from information_schema.tables 
   where table_schema not in ('pg_catalog', 'pg_toast', 'information_schema')
     and table_schema !~ '^pg_temp'
     and table_type = 'BASE TABLE'
   order by table_schema, table_name;

                                                                                                       
SELECT CASE WHEN EXISTS (SELECT 1 FROM pg_authid WHERE rolname = 'dbseller')                           
  THEN fc_grant('dbseller', 'select', '%', '%') ELSE -1 END;                                           
SELECT CASE WHEN EXISTS (SELECT 1 FROM pg_authid WHERE rolname = 'plugin')                             
  THEN fc_grant('plugin', 'select', '%', '%') ELSE -1 END;                                             
SELECT fc_executa_ddl('GRANT CREATE ON TABLESPACE '||spcname||' TO dbseller;')                         
  FROM pg_tablespace                                                                                   
 WHERE spcname !~ '^pg_' AND EXISTS (SELECT 1 FROM pg_authid WHERE rolname = 'dbseller');              
                                                                                                       
  delete from db_versaoant where not exists (select 1 from db_versao where db30_codver = db31_codver); 
  delete from db_versaousu where not exists (select 1 from db_versao where db30_codver = db32_codver); 
  delete from db_versaocpd where not exists (select 1 from db_versao where db30_codver = db33_codver); 
                                                                                                       
select fc_schemas_dbportal();
        
DISCARD TEMP;
SQL_POS;

        $this->execute($pre);
        $this->execute($ddl);
        $this->execute($pos);
    
    }

    public function down() {

        $ddl = <<<'SQL_DDL'
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO SAÚDE ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
DROP TABLE    IF EXISTS cgs_unddocumento CASCADE;
DROP TABLE    IF EXISTS cgs_undendereco     CASCADE;

DROP SEQUENCE IF EXISTS cgs_unddocumento_sd108_sequencial_seq;
DROP SEQUENCE IF EXISTS cgs_undendereco_sd109_sequencial_seq;

alter table cgs_und drop column if exists z01_orgaoemissoridentidade;
alter table cgs_und alter COLUMN z01_v_cxpostal type varchar(20);

---------------------------------------------------------------------------------------------------------------------------
---------------------------------------------- INICIO FINANCEIRO ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS classificacaocredoreselemento CASCADE;
DROP SEQUENCE IF EXISTS classificacaocredoreselemento_cc32_sequencial_seq;
DROP TABLE IF EXISTS classificacaocredoresrecurso CASCADE;
DROP SEQUENCE IF EXISTS classificacaocredoresrecurso_cc33_sequencial_seq;
DROP TABLE IF EXISTS classificacaocredorestipocompra CASCADE;
DROP SEQUENCE IF EXISTS classificacaocredorestipocompra_cc34_sequencial_seq;
DROP TABLE IF EXISTS classificacaocredoresevento CASCADE;
DROP SEQUENCE IF EXISTS classificacaocredoresevento_cc35_sequencial_seq;

ALTER TABLE classificacaocredores DROP COLUMN if exists cc30_diasvencimento;
ALTER TABLE classificacaocredores DROP COLUMN if exists cc30_contagemdias;
ALTER TABLE classificacaocredores DROP COLUMN if exists cc30_valorinicial;
ALTER TABLE classificacaocredores DROP COLUMN if exists cc30_valorfinal;
ALTER TABLE classificacaocredores DROP COLUMN if exists cc30_dispensa;
ALTER TABLE classificacaocredores DROP COLUMN if exists cc30_ordem;

alter table empautpresta DROP CONSTRAINT empautoriza_empautpresta_fk;
alter table empautpresta DROP CONSTRAINT empprestatip_empautpresta_fk;
drop index  empautpresta_autori_in;

DROP TABLE IF EXISTS cgmestrangeiro CASCADE;
DROP SEQUENCE IF EXISTS cgmestrangeiro_z09_sequencial_seq;

---------------------------------------------------------------------------------------------------------------------------
------------------------------------------------ INICIO FOLHA -------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS avaliacaogruporespostacgm;
DROP SEQUENCE IF EXISTS avaliacaogruporespostacgm_eso03_sequencial_seq;


---------------------------------------------------------------------------------------
---------------------------------- INICIO EDUCAÇÃO ------------------------------------
---------------------------------------------------------------------------------------
delete from censoregradisc  where ed272_ano = 2016;
delete from censoetapamediacaodidaticopedagogica where ed131_ano = 2016;
delete from censoinstsuperior where ed257_i_codigo in (12899, 10251, 13728, 13764, 14158, 14718, 15500, 15562, 16602, 16782, 16849, 16918, 16948, 17025, 17091, 17115, 17118, 17289, 17348, 17355, 17382, 17394, 17400, 17403, 17420, 17460, 17558, 17563, 17565, 17590, 17593, 17598, 17608, 17622, 17628, 17662, 17670, 17672, 17674, 17701, 17731, 17749, 17763, 17816, 17828, 17831, 17850, 17854, 18010, 18019, 18023, 18067, 18075, 18114, 18133, 18165, 18257, 18288, 19500, 19501, 19512, 19578, 19588, 19739, 19862, 20478, 21095, 21206);

SQL_DDL;

        $pre = <<<'SQL_PRE'
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FOLHA ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
TRUNCATE avaliacaoperguntadb_formulas;
DELETE FROM db_formulas WHERE db148_nome = 'CODIGO_CGM' OR db148_nome LIKE 'ESOCIAL_%';

--Menus para formulári de preenchimento das informações do empregador
delete from db_menu where id_item_filho = 10244 AND modulo = 10216;
delete from db_itensmenu where id_item = 10244;

delete from db_sysforkey where codarq = 3943;
delete from db_sysprikey where codarq = 3943;
delete from db_sysarqcamp where codarq = 3943;
delete from db_syssequencia where codsequencia = 1000578;

delete from db_syscampo where codcam in (21904, 21905, 21906);

delete from db_sysarqarq where codarq = 3943;
delete from db_sysarqmod where codarq = 3943;
delete from db_sysarquivo where codarq = 3943;

delete from avaliacaoperguntaopcao where db104_sequencial between 3001274 and 3001369;
delete from avaliacaopergunta      where db103_sequencial between 3000375 and 3000417;
delete from avaliacaogrupopergunta where db102_avaliacao = 3000009;
delete from avaliacao              where db101_sequencial= 3000009;
delete from db_menu where id_item_filho =  437509 and id_item = 2458 and modulo = 952;
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO SAÚDE ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
delete from db_itensmenu    where id_item = 10239;
delete from db_sysarqcamp   where codarq in (3937,3938);

delete from db_sysforkey    where codcam in (21871, 21872, 21873, 21874, 21875, 21876);
delete from db_sysprikey    where codcam in (21871, 21872, 21873, 21874, 21875, 21876);

delete from db_sysprikey    where codcam in (21871, 21872, 21873, 21874, 21875, 21876);
delete from db_sysindices   where codarq in (3937,3938);
delete from db_sysforkey    where codcam in (21871, 21872, 21873, 21874, 21875, 21876);
delete from db_syscadind    where codcam in (21871, 21872, 21873, 21874, 21875, 21876);

delete from db_syssequencia where codsequencia in (1000571,1000572);
delete from db_syscampodep  where codcam in (21871, 21872, 21873, 21874, 21875, 21876);
delete from db_syscampo     where codcam in (21871, 21872, 21873, 21874, 21875, 21876);
delete from db_sysarqmod    where codarq in (3937,3938);
delete from db_sysarquivo   where codarq in (3937,3938);

delete from db_menu where id_item_filho in (10239, 1045399);

insert into db_menu(id_item, id_item_filho, menusequencia, modulo )
  values
  (8170, 1045399, 14, 8167   ),
  (8323, 1045399, 10, 8322   ),
  (8482, 1045399, 3,  8481   ),
  (3470, 1045399, 1,  6952   ),
  (3470, 1045399, 5,  1000004);

delete from caddocumentoatributo where db45_caddocumento in( 3000000, 3000001, 3000002, 3000003, 3000004, 3000005 );
delete from caddocumento         where db44_sequencial   in( 3000000, 3000001, 3000002, 3000003, 3000004, 3000005 );
delete from cadtipodocumento     where db123_sequencial = 3;

delete from db_sysarqcamp where codarq = 1010144 and codcam = 21901;
delete from db_syscampo   where codcam = 21901;

update db_syscampo set nomecam = 'z01_v_cxpostal', conteudo = 'varchar(20)', descricao = 'Caixa Postal', valorinicial = '', rotulo = 'Caixa Postal', nulo = 't', tamanho = 20, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Caixa Postal' where codcam = 11209;

---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FINANCEIRO -----------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
delete from db_sysforkey where codarq in (3939, 3940, 3941, 3942);
delete from db_sysprikey where codarq in (3939, 3940, 3941, 3942);
delete from db_syssequencia where codsequencia in (1000574, 1000575, 1000576, 1000577);
delete from db_sysarqcamp where codarq in (3939, 3940, 3941, 3942);
delete from db_sysarqcamp where codcam in (21895, 21896, 21897, 21898, 21899, 21900);
delete from db_syscampodef where codcam = 21895;
delete from db_syscampo where codcam in (21877, 21878, 21879, 21883, 21885, 21886, 21887, 21888, 21889, 21890, 21891,21892, 21893, 21894,
                                         21895, 21896, 21897, 21898, 21899, 21900);
delete from db_sysarqmod where codmod = 38 and codarq in (3939, 3940, 3941, 3942);
delete from db_sysarquivo where codarq in (3939, 3940, 3941, 3942);
delete from db_menu where id_item_filho = 10240 AND modulo = 398;
delete from db_menu where id_item_filho = 10241 AND modulo = 398;
delete from db_menu where id_item_filho = 10242 AND modulo = 398;
delete from db_menu where id_item_filho = 10243 AND modulo = 398;
delete from db_itensmenu where id_item = 10240;
delete from db_itensmenu where id_item = 10241;
delete from db_itensmenu where id_item = 10242;
delete from db_itensmenu where id_item = 10243;
delete from db_itensmenu where id_item = 10245;

delete from db_menu where id_item_filho = 10246 AND modulo = 381;
delete from db_itensmenu where id_item = 10246;


select fc_executa_ddl('
  delete from db_sysforkey where codarq = 3944;
  delete from db_sysprikey where codarq = 3944;
  delete from db_sysindices where codarq = 3944;
  delete from db_syscadind where codcam in (21907, 21908, 21909);
  delete from db_syssequencia where codsequencia in (1000579);
  delete from db_sysarqcamp where codarq in (3944);
  delete from db_sysarqcamp where codcam in (21907, 21908, 21909);
  delete from db_syscampodef where codcam in (21907, 21908, 21909);
  delete from db_syscampo where codcam in (21907, 21908, 21909);
  delete from db_sysarqmod where codmod = 4 and codarq in (3944);
  delete from db_sysarquivo where codarq in (3944);
');
SQL_PRE;

        $this->execute($ddl);
        $this->execute($pre);

    }
}
