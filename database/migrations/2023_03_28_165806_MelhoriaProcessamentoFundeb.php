<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MelhoriaProcessamentoFundeb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql  = <<<SQL
            insert into db_sysarquivo values (1011055, 'rhcargosfundeb', 'Tabela referente ao Tipo de Cargos do Fundeb', 'rh283', '2023-03-28', 'rh283', 0, 'f', 'f', 'f', 'f' );
            insert into db_sysarqmod values (28,1011055);
            insert into db_sysarqarq values(0,1011055);
            insert into db_syscampo values(1014930,'rh283_codigo','int4','Código do Cargo nos Parâmetros do Fundeb','0', 'Código do Cargo',10,'f','f','f',1,'text','Código do Cargo');
            insert into db_syscampo values(1014931,'rh283_descricao','varchar(20)','Descrição do Cargo nos Parâmetros do Fundeb','', 'Descrição do Cargo',20,'f','t','f',0,'text','Descrição do Cargo');
            insert into db_sysarqcamp values(1011055,1014930,1,0);
            insert into db_sysarqcamp values(1011055,1014931,2,0);
            insert into db_sysprikey (codarq,codcam,sequen,camiden) values(1011055,1014930,1,1014930);

            insert into db_sysarquivo values (1011056, 'rhparametrosfundeb', 'Tabela referente a Parametrização dos Cargos para o Cálculo do Fundeb', 'rh284', '2023-03-28', 'rh284', 0, 'f', 'f', 'f', 'f' );
            insert into db_sysarqmod values (28,1011056);
            insert into db_sysarqarq values(0,1011056);
            insert into db_syscampo values(1014932,'rh284_sequencial','int4','Sequencial da tabela','0', 'Sequencial Parâmetros Fundeb',10,'f','f','f',1,'text','Sequencial Parâmetros Fundeb');
            insert into db_syscampo values(1014933,'rh284_tipo','int4','Código do Cargo, o mesmo código da tabela rhcargosfundeb','0', 'Código do Cargo',10,'f','f','f',1,'text','Código do Cargo');
            insert into db_syscampo values(1014934,'rh284_cargo','int4','Código do Cargo o mesno da tabela rhfuncao','0', 'Código do Cargo',10,'f','f','f',1,'text','Código do Cargo');
            insert into db_syscampo values(1014935,'rh284_departamento','int4','Código do Departamento do Cargo','0', 'Código do Departamento',10,'f','f','f',1,'text','Código do Departamento');
            insert into db_syscampo values(1014936,'rh284_instituicao','int4','Código da Instituição','0', 'Código da Instituição',10,'f','f','f',1,'text','Código da Instituição');
            insert into db_sysarqcamp values(1011056,1014932,1,0);
            insert into db_sysarqcamp values(1011056,1014933,2,0);
            insert into db_sysarqcamp values(1011056,1014934,3,0);
            insert into db_sysarqcamp values(1011056,1014935,4,0);
            insert into db_sysarqcamp values(1011056,1014936,5,0);
            insert into db_sysprikey (codarq,codcam,sequen,camiden) values(1011056,1014932,1,1014932);
            insert into db_sysforkey values(1011056,1014933,1,1011055,0);
            insert into db_sysforkey values(1011056,1014934,1,1174,0);
            insert into db_sysforkey values(1011056,1014936,2,1174,0);

            insert into db_sysarquivo values (1011057, 'rhcalculofundeb', 'Cálculo do Fundeb', 'rh285', '2023-03-28', 'rh285', 0, 'f', 'f', 'f', 'f' );
            insert into db_sysarqmod values (69,1011057);
            insert into db_sysarqarq values(0,1011057);
            insert into db_syscampo values(1014937,'rh285_mes','int4','Mês da Competência do Cálculo do Fundeb','0', 'Mês da Competência do Cálculo',10,'f','f','f',1,'text','Mês da Competência do Cálculo');
            insert into db_syscampo values(1014938,'rh285_ano','int4','Ano da Competência do Cálculo do Fundeb','0', 'Ano da Competência do Cálculo',10,'f','f','f',1,'text','Ano da Competência do Cálculo');
            insert into db_syscampo values(1014939,'rh285_valor','float4','Valor Total que será calculado para os cargos do Fundeb','0', 'Valor do Fundeb',10,'f','f','f',4,'text','Valor do Fundeb');
            insert into db_sysarqcamp values(1011057,1014937,1,0);
            insert into db_sysarqcamp values(1011057,1014938,2,0);
            insert into db_sysarqcamp values(1011057,1014939,3,0);

            insert into db_sysarquivo values (1011058, 'rhprocessamentofundeb', 'Tabela de Processamento do Cálculo do Fundeb', 'rh286', '2023-03-28', 'rh286', 0, 'f', 'f', 'f', 'f' );
            insert into db_sysarqmod values (28,1011058);
            insert into db_syscampo values(1014940,'rh286_sequencial','int4','Sequencial do Cálculo do Processamento do Fundeb','0', 'Sequencial Processamento Fundeb',10,'f','f','f',1,'text','Sequencial Processamento Fundeb');
            insert into db_syscampo values(1014943,'rh286_matricula','int4','Código da Matrícula do Servidor','0', 'Código da Matrícula do Servidor',10,'t','f','f',1,'text','Código da Matrícula do Servidor');
            insert into db_syscampo values(1014946,'rh286_ano','int4','Ano da Competência do Cálculo do Servidor','0', 'Ano da Competência do Cálculo',10,'f','f','f',1,'text','Ano da Competência do Cálculo');
            insert into db_syscampo values(1014947,'rh286_mes','int4','Mês da Competência do Cálculo do Servidor','0', 'Mês da Competência do Cálculo',10,'f','f','f',1,'text','Mês da Competência do Cálculo');
            insert into db_syscampo values(1014941,'rh286_cargo','int4','Código do Cargo da Parametrização do Fundeb','0', 'Código do Cargo ',10,'t','f','f',1,'text','Código do Cargo ');
            insert into db_syscampo values(1014942,'rh286_rubrica','varchar(4)','Código da Rubrica para cálculo','', 'Código da Rubrica',4,'t','t','f',0,'text','Código da Rubrica');
            insert into db_syscampo values(1014944,'rh286_valor','float4','Valor Calculado do Servidor','0', 'Valor Calculado do Servidor',10,'t','f','f',4,'text','Valor Calculado do Servidor');
            insert into db_syscampo values(1014945,'rh286_instituicao','int4','Código da Instituição do Servidor','0', 'Código da Instituição',10,'t','f','f',1,'text','Código da Instituição');

            insert into db_sysarqcamp values(1011058,1014940,1,0);
            insert into db_sysarqcamp values(1011058,1014943,2,0);
            insert into db_sysarqcamp values(1011058,1014946,3,0);
            insert into db_sysarqcamp values(1011058,1014947,4,0);
            insert into db_sysarqcamp values(1011058,1014941,5,0);
            insert into db_sysarqcamp values(1011058,1014942,6,0);
            insert into db_sysarqcamp values(1011058,1014944,7,0);
            insert into db_sysarqcamp values(1011058,1014945,8,0);

            insert into db_sysprikey (codarq,codcam,sequen,camiden) values(1011058,1014940,1,1014940);


            update db_syscampo set nomecam = 'rh283_descricao', conteudo = 'varchar(20)', descricao = 'Descrição do Cargo nos Parâmetros do Fundeb', valorinicial = '', rotulo = 'Descrição do Cargo', nulo = 't', tamanho = 20, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Descrição do Cargo' where codcam = 1014931;
            update db_syscampo set nomecam = 'rh284_tipo', conteudo = 'int4', descricao = 'Tipo do Cargo, o mesmo tipo configurado na tabela rhcargosfundeb', valorinicial = '0', rotulo = 'Tipo do Cargo', nulo = 'f', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Tipo do Cargo' where codcam = 1014933;
            update db_sysarqmod SET codmod = 28 WHERE codarq = 1011057;
            update db_syscampo set nomecam = 'rh285_valor', conteudo = 'float4', descricao = 'Valor Total que será calculado para os cargos do Fundeb', valorinicial = '0', rotulo = 'Valor do Fundeb', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 4, tipoobj = 'text', rotulorel = 'Valor do Fundeb' where codcam = 1014939;
            update db_syscampo set nomecam = 'rh285_ano', conteudo = 'int4', descricao = 'Ano da Competência do Cálculo do Fundeb', valorinicial = '0', rotulo = 'Ano da Competência do Cálculo', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Ano da Competência do Cálculo' where codcam = 1014938;
            update db_syscampo set nomecam = 'rh285_mes', conteudo = 'int4', descricao = 'Mês da Competência do Cálculo do Fundeb', valorinicial = '0', rotulo = 'Mês da Competência do Cálculo', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Mês da Competência do Cálculo' where codcam = 1014937;
            update db_syscampo set nomecam = 'rh284_instituicao', conteudo = 'int4', descricao = 'Código da Instituição', valorinicial = '0', rotulo = 'Código da Instituição', nulo = 'f', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Código da Instituição' where codcam = 1014936;
            update db_syscampo set nomecam = 'rh284_instituicao', conteudo = 'int4', descricao = 'Código da Instituição', valorinicial = '0', rotulo = 'Código da Instituição', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Código da Instituição' where codcam = 1014936;
            update db_syscampo set nomecam = 'rh284_departamento', conteudo = 'int4', descricao = 'Código do Departamento do Cargo', valorinicial = '0', rotulo = 'Código do Departamento', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Código do Departamento' where codcam = 1014935;
            update db_syscampo set nomecam = 'rh284_cargo', conteudo = 'int4', descricao = 'Código do Cargo o mesno da tabela rhfuncao', valorinicial = '0', rotulo = 'Código do Cargo', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Código do Cargo' where codcam = 1014934;
            update db_syscampo set nomecam = 'rh284_tipo', conteudo = 'int4', descricao = 'Tipo do Cargo, o mesmo tipo configurado na tabela rhcargosfundeb', valorinicial = '0', rotulo = 'Tipo do Cargo', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Tipo do Cargo' where codcam = 1014933;
            update db_syscampo set nomecam = 'rh283_codigo', conteudo = 'int4', descricao = 'Código do Cargo nos Parâmetros do Fundeb', valorinicial = '0', rotulo = 'Código do Cargo', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Código do Cargo' where codcam = 1014930;
            update db_syscampo set nomecam = 'rh286_instituicao', conteudo = 'int4', descricao = 'Código da Instituição do Servidor', valorinicial = '0', rotulo = 'Código da Instituição', nulo = 'f', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Código da Instituição' where codcam = 1014945;
            update db_syscampo set nomecam = 'rh286_matricula', conteudo = 'int4', descricao = 'Código da Matrícula do Servidor', valorinicial = '0', rotulo = 'Código da Matrícula do Servidor', nulo = 'f', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Código da Matrícula do Servidor' where codcam = 1014943;
            update db_syscampo set nomecam = 'rh286_rubrica', conteudo = 'varchar(4)', descricao = 'Código da Rubrica para cálculo', valorinicial = '', rotulo = 'Código da Rubrica', nulo = 'f', tamanho = 4, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Código da Rubrica' where codcam = 1014942;
SQL;
            DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql  = <<<SQL
            delete from db_sysarqarq where codarq = 1011055;
            delete from db_sysarqcamp where codarq = 1011055;
            delete from db_sysprikey where codarq = 1011055;
            delete from db_sysarqarq where codarq = 1011056;
            delete from db_sysarqcamp where codarq = 1011056;
            delete from db_sysprikey where codarq = 1011056;
            delete from db_sysforkey where codarq = 1011056 and referen = 0;
            delete from db_syscampodep where codcam = 1014931;
            delete from db_syscampodef where codcam = 1014931;
            delete from db_syscampodep where codcam = 1014933;
            delete from db_syscampodef where codcam = 1014933;
            delete from db_sysarqarq where codarq = 1011057;
            delete from db_sysarqcamp where codarq = 1011057;
            delete from db_syscampodep where codcam = 1014939;
            delete from db_syscampodef where codcam = 1014939;
            delete from db_syscampodep where codcam = 1014938;
            delete from db_syscampodef where codcam = 1014938;
            delete from db_syscampodep where codcam = 1014937;
            delete from db_syscampodef where codcam = 1014937;
            delete from db_syscampodep where codcam = 1014936;
            delete from db_syscampodef where codcam = 1014936;
            delete from db_syscampodep where codcam = 1014935;
            delete from db_syscampodef where codcam = 1014935;
            delete from db_syscampodep where codcam = 1014934;
            delete from db_syscampodef where codcam = 1014934;
            delete from db_syscampodep where codcam = 1014930;
            delete from db_syscampodef where codcam = 1014930;
            delete from db_sysarqcamp where codarq = 1011058;
            delete from db_sysprikey where codarq = 1011058;
            delete from db_syscampodep where codcam = 1014945;
            delete from db_syscampodef where codcam = 1014945;
            delete from db_syscampodep where codcam = 1014943;
            delete from db_syscampodef where codcam = 1014943;
            delete from db_syscampodep where codcam = 1014942;
            delete from db_syscampodef where codcam = 1014942;
SQL;
            DB::connection()->getPdo()->exec($sql);
    }
}
