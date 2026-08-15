<?php

use Classes\PostgresMigration;

class Versao51 extends PostgresMigration
{
    public function up() {

        $pre = <<<'SQL_PRE'
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FINANCEIRO ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

select fc_executa_ddl('insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21841 ,\'ac07_datainicio\' ,\'date\' ,\'Data de Início\' ,\'\' ,\'Data de Início\' ,10 ,\'true\' ,\'false\' ,\'false\' ,1 ,\'text\' ,\'Data de Início\')');
select fc_executa_ddl('insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 2831 ,21841 ,5 ,0 );');
select fc_executa_ddl('insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21842 ,\'ac07_datatermino\' ,\'date\' ,\'Data de Término\' ,\'\' ,\'Data de Término\' ,10 ,\'true\' ,\'false\' ,\'false\' ,1 ,\'text\' ,\'Data de Término\');');
select fc_executa_ddl('insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 2831 ,21842 ,6 ,0 );');
select fc_executa_ddl('insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21843 ,\'ac26_tipooperacao\' ,\'int4\' ,\'Tipo de Operação\' ,\'\' ,\'Tipo de Operação\' ,10 ,\'true\' ,\'false\' ,\'false\' ,1 ,\'text\' ,\'Tipo de Operação\' );');
select fc_executa_ddl('insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21843 , \'1\' ,\'Acréscimo de Valor por Aumento de Quantitativo\' );');
select fc_executa_ddl('insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21843 , \'2\' ,\'Acréscimo de valor por inclusão de Itens novos\' );');
select fc_executa_ddl('insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21843 , \'3\' ,\'Reajustamento de Preços\' );');
select fc_executa_ddl('insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21843 , \'4\' ,\'Redução de Valor por Supressão de Itens\' );');
select fc_executa_ddl('insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21843 , \'5\' ,\'Redução de Valor por Supressão de Quantitativo\' );');
select fc_executa_ddl('insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 2930 ,21843 ,10 ,0 );');

select fc_executa_ddl('insert into acordoposicaotipo values (7, \'Alteração de Dotação\');');
select fc_executa_ddl('insert into acordoposicaotipo values (8, \'Supressão de Quantidade/Valor\')');

select fc_executa_ddl('insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10227 ,\'Supressão de Quantidade/Valor\' ,\'Supressão de Quantidade/Valor\' ,\'ac04_aditamentosupressaovalor001.php\' ,\'1\' ,\'1\' ,\'Supressão de Quantidade/Valor\' ,\'true\' );');
select fc_executa_ddl('insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 8568 ,10227 ,6 ,8251 );');

update acordocomissaotipomembro set ac42_descricao = 'Gestor' where ac42_sequencial = 1;

-- Controle de contratos encerrados
select fc_executa_ddl('insert into db_sysarquivo values (3933, \'acordoencerramentolicitacon\', \'Controle do encerramento de contratos para o LicitaCon.\', \'ac58\', \'2016-04-15\', \'Encerramento de Contratos\', 0, \'f\', \'f\', \'f\', \'f\' );');
select fc_executa_ddl('insert into db_sysarqmod values (69,3933);');
select fc_executa_ddl('insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21845 ,\'ac58_sequencial\' ,\'int4\' ,\'Código\' ,\'\' ,\'Código\' ,10 ,\'false\' ,\'false\' ,\'false\' ,1 ,\'text\' ,\'Código\' );');
select fc_executa_ddl('insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3933 ,21845 ,1 ,0 );');
select fc_executa_ddl('insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21846 ,\'ac58_acordo\' ,\'int4\' ,\'Acordo\' ,\'\' ,\'Acordo\' ,10 ,\'false\' ,\'false\' ,\'false\' ,1 ,\'text\' ,\'Acordo\' );');
select fc_executa_ddl('insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3933 ,21846 ,2 ,0 );');
select fc_executa_ddl('insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21847 ,\'ac58_data\' ,\'date\' ,\'Data\' ,\'\' ,\'Data\' ,10 ,\'false\' ,\'false\' ,\'false\' ,0 ,\'text\' ,\'Data\' );');
select fc_executa_ddl('insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3933 ,21847 ,3 ,0 );');
select fc_executa_ddl('insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3933,21845,1,21845);');
select fc_executa_ddl('insert into db_sysforkey values(3933,21846,1,2828,0);');
select fc_executa_ddl('insert into db_syssequencia values(1000565, \'acordoencerramentolicitacon_ac58_sequencial_seq\', 1, 1, 9223372036854775807, 1, 1);');
select fc_executa_ddl('update db_sysarqcamp set codsequencia = 1000565 where codarq = 3933 and codcam = 21845;');
select fc_executa_ddl('update db_layoutcampos set db52_layoutformat = 13 where db52_codigo = 13148;');

-- Campo Ano do Exercício na Tabela acordoempempenho
select fc_executa_ddl('insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21848 ,\'ac54_ano\' ,\'int4\' ,\'Exercício\' ,\'\' ,\'Exercício\' ,4 ,\'true\' ,\'false\' ,\'false\' ,1 ,\'text\' ,\'Exercício\' );');
select fc_executa_ddl('insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3926 ,21848 ,5 ,0);');

-- Campo Unidade da autorização de empenho
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21868 ,'e55_matunid' ,'int4' ,'Unidade' ,'null' ,'Unidade' ,10 ,'true' ,'false' ,'false' ,1 ,'text' ,'Unidade' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 811 ,21868 ,10 ,0 );
insert into db_sysforkey values(811,21868,1,1017,0);

select fc_executa_ddl(
  'insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10236 ,\'Disponibilidade Financeira\' ,\'Relatório de Disponibilidade Financeira\' ,\'con2_disponibilidadefinanceira001.php\' ,\'1\' ,\'1\' ,\'Disponibilidade Financeira\' ,\'true\' );
  delete from db_menu where id_item_filho = 10236 AND modulo = 209;
  insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 9581 ,10236 ,3 ,209 );'
);

-- Relatório Demonstração das Mutações do Patrimônio Líquido
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10237 ,'Dem. das Mutações do Pat. Líquido' ,'Dem. das Mutações do Pat. Líquido' ,'con2_relatoriomutacoespatrimonioliquido001.php' ,'1' ,'1' ,'Dem. das Mutações do Pat. Líquido' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 9882 ,10237 ,9 ,209 );

insert into orcparamrel (o42_codparrel, o42_descrrel, o42_orcparamrelgrupo) values(161, 'DEMONSTRAÇÃO DAS MUTAÇÕES DO PATRIMÔNIO LÍQUIDO', 1);

insert into orcparamseqcoluna values(207, 2016, 'Pat. Social / Capital Social', 1, null, 'capital_social');
insert into orcparamseqcoluna values(208, 2016, 'Adiantamento para Futuro Aumento de Capital (AFAC)', 1, null, 'afac');
insert into orcparamseqcoluna values(209, 2016, 'Reserva  de Capital', 1, null, 'reserva_capital' );
insert into orcparamseqcoluna values(210, 2016, 'Ajustes de Avaliação Patrimonial', 1, null, 'avaliacao_patrimonial');
insert into orcparamseqcoluna values(211, 2016, 'Reservas de Lucros', 1, null, 'reserva_lucros');
insert into orcparamseqcoluna values(212, 2016, 'Demais Reservas', 1, null, 'demais_reservas');
insert into orcparamseqcoluna values(213, 2016, 'Resultados Acumulados', 1, null, 'resultados_acumulados');
insert into orcparamseqcoluna values(214, 2016, 'Acões / Cotas em Tesouraria', 1, null, 'acoes_cotas_tesouraria');

insert into orcparamrelperiodos values(nextval('orcparamrelperiodos_o113_sequencial_seq'), 1, 161);

insert into orcparamseq values (161, 1, 'Saldos Iniciais', 1, 1, 1, false, false, false, false, false, 'Saldos Iniciais', true, false, 1, 1, '', false, 0);
insert into orcparamseq values (161, 2, 'Ajustes de exercícios anteriores', 1, 1, 1, false, false, false, false, false, 'Ajustes de exercícios anteriores', true, false, 2, 1, '', false, 0);
insert into orcparamseq values (161, 3, 'Aumento de Capital', 1, 1, 1, false, false, false, false, false, 'Aumento de Capital', true, false, 3, 1, '', false, 0);
insert into orcparamseq values (161, 4, 'Resgate / Reemissão de Ações e Cotas', 1, 1, 1, false, false, false, false, false, 'Resgate / Reemissão de Ações e Cotas', true, false, 4, 1, '', false, 0);
insert into orcparamseq values (161, 5, 'Juros sobre o capital próprio', 1, 1, 1, false, false, false, false, false, 'Juros sobre o capital próprio', true, false, 5, 1, '', false, 0);
insert into orcparamseq values (161, 6, 'Resultado do exercício', 1, 1, 1, false, false, false, false, false, 'Resultado do exercício', true, false, 6, 1, '', false, 0);
insert into orcparamseq values (161, 7, 'Ajustes de avaliação patrimonial', 1, 1, 1, false, false, false, false, false, 'Ajustes de avaliação patrimonial', true, false, 7, 1, '', false, 0);
insert into orcparamseq values (161, 8, 'Constituição / Reversão de reservas', 1, 1, 1, false, false, false, false, false, 'Constituição / Reversão de reservas', true, false, 8, 1, '', false, 0);
insert into orcparamseq values (161, 9, 'Dividendos a distribuir (R$ ...por ação)', 1, 1, 1, false, false, false, false, false, 'Dividendos a distribuir (R$ ...por ação)', true, false, 9, 1, '', false, 0);
insert into orcparamseq values (161, 10, 'Saldos Finais', 1, 1, 1, false, false, false, false, false, 'Saldos Finais', false, true, 10, 1, '', false, 0);

insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 1, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 1, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 1, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 1, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 1, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 1, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 1, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 1, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 2, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 2, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 2, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 2, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 2, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 2, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 2, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 2, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 3, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 3, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 3, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 3, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 3, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 3, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 3, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 3, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 4, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 4, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 4, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 4, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 4, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 4, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 4, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 4, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 5, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 5, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 5, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 5, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 5, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 5, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 5, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 5, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 6, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 6, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 6, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 6, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 6, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 6, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 6, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 6, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 7, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 7, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 7, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 7, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 7, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 7, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 7, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 7, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 8, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 8, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 8, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 8, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 8, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 8, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 8, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 8, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 9, 161, 207, 1, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 9, 161, 208, 2, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 9, 161, 209, 3, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 9, 161, 210, 4, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 9, 161, 211, 5, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 9, 161, 212, 6, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 9, 161, 213, 7, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 9, 161, 214, 8, 1, '');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 10, 161, 207, 1, 1, 'L[1]->capital_social + L[2]->capital_social + L[3]->capital_social + L[4]->capital_social + L[5]->capital_social + L[6]->capital_social + L[7]->capital_social + L[8]->capital_social + L[9]->capital_social');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 10, 161, 208, 2, 1, 'L[1]->afac + L[2]->afac + L[3]->afac + L[4]->afac + L[5]->afac + L[6]->afac + L[7]->afac + L[8]->afac + L[9]->afac');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 10, 161, 209, 3, 1, 'L[1]->reserva_capital + L[2]->reserva_capital + L[3]->reserva_capital + L[4]->reserva_capital + L[5]->reserva_capital + L[6]->reserva_capital + L[7]->reserva_capital + L[8]->reserva_capital + L[9]->reserva_capital');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 10, 161, 210, 4, 1, 'L[1]->avaliacao_patrimonial + L[2]->avaliacao_patrimonial + L[3]->avaliacao_patrimonial + L[4]->avaliacao_patrimonial + L[5]->avaliacao_patrimonial + L[6]->avaliacao_patrimonial + L[7]->avaliacao_patrimonial + L[8]->avaliacao_patrimonial + L[9]->avaliacao_patrimonial');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 10, 161, 211, 5, 1, 'L[1]->reserva_lucros + L[2]->reserva_lucros + L[3]->reserva_lucros + L[4]->reserva_lucros + L[5]->reserva_lucros + L[6]->reserva_lucros + L[7]->reserva_lucros + L[8]->reserva_lucros + L[9]->reserva_lucros');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 10, 161, 212, 6, 1, 'L[1]->demais_reservas + L[2]->demais_reservas + L[3]->demais_reservas + L[4]->demais_reservas + L[5]->demais_reservas + L[6]->demais_reservas + L[7]->demais_reservas + L[8]->demais_reservas + L[9]->demais_reservas');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 10, 161, 213, 7, 1, 'L[1]->resultados_acumulados + L[2]->resultados_acumulados + L[3]->resultados_acumulados + L[4]->resultados_acumulados + L[5]->resultados_acumulados + L[6]->resultados_acumulados + L[7]->resultados_acumulados + L[8]->resultados_acumulados + L[9]->resultados_acumulados');
insert into orcparamseqorcparamseqcoluna values (nextval('orcparamseqorcparamseqcoluna_o116_sequencial_seq'), 10, 161, 214, 8, 1, 'L[1]->acoes_cotas_tesouraria + L[2]->acoes_cotas_tesouraria + L[3]->acoes_cotas_tesouraria + L[4]->acoes_cotas_tesouraria + L[5]->acoes_cotas_tesouraria + L[6]->acoes_cotas_tesouraria + L[7]->acoes_cotas_tesouraria + L[8]->acoes_cotas_tesouraria + L[9]->acoes_cotas_tesouraria');

---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FOLHA ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

insert into db_sysarquivo values (3936, 'rhconsignacaobancolayout', 'Vinculo dos layouts com banco', 'rh178', '2016-05-02', 'Vinculo dos layouts com banco', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (28,3936);
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21859 ,'rh178_sequencial' ,'int4' ,'Codigo' ,'' ,'Codigo' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Codigo' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3936 ,21859 ,1 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21860 ,'rh178_db_banco' ,'varchar(10)' ,'Banco' ,'' ,'Banco' ,10 ,'false' ,'true' ,'false' ,0 ,'text' ,'Banco' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3936 ,21860 ,2 ,0 );
insert into db_syssequencia values(1000570, 'rhconsignacaobancolayout_rh178_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000570 where codarq = 3936 and codcam = 21859;
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21861 ,'rh178_layout' ,'int4' ,'Layout' ,'' ,'Layout' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Layout' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3936 ,21861 ,3 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21862 ,'rh178_rubrica' ,'char(4)' ,'Rubrica' ,'' ,'Rubrica' ,4 ,'false' ,'true' ,'false' ,0 ,'text' ,'Rubrica' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3936 ,21862 ,4 ,0 );

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21863 ,'rh178_instit' ,'int4' ,'Instituiçao' ,'' ,'Instituiçao' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Instituiçao' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3936 ,21863 ,5 ,0 );

insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3936,21859,1,21859);
insert into db_sysforkey values(3936,21860,1,1185,0);
insert into db_sysforkey values(3936,21861,1,1553,0);
insert into db_sysforkey values(3936,21862,1,1177,0);
insert into db_sysforkey values(3936,21863,2,1177,0);
insert into db_sysindices values(4346,'rhconsignacaobancolayout_banco_in',3936,'0');
insert into db_syscadind values(4346,21860,1);
insert into db_sysindices values(4347,'rhconsignacaobancolayout_layout_in',3936,'0');
insert into db_syscadind values(4347,21861,1);
insert into db_sysindices values(4348,'rhconsignacaobancolayout_rubrica_in',3936,'0');
insert into db_syscadind values(4348,21862,1);

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21869 ,'rh151_arquivo' ,'oid' ,'Conteudo do Arquivo' ,'' ,'Conteudo do Arquivo' ,1 ,'true' ,'false' ,'false' ,1 ,'text' ,'Conteudo do Arquivo' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3785 ,21869 ,8 ,0 );

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21870 ,'rh151_banco' ,'varchar(10)' ,'Banco' ,'' ,'Banco' ,10 ,'true' ,'false' ,'false' ,0 ,'text' ,'Banco' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3785 ,21870 ,9 ,0 );

insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10231 ,'Configuração Consignados' ,'Configuraçao Consignados' ,'pes4_configurararquivoconsignado001.php' ,'1' ,'1' ,'Configuraçao das consignaçoes em folha' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 3516 ,10231 ,14 ,952 );

insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10232 ,'Consignados' ,'Importaçao dos arquivos de consignados' ,'' ,'1' ,'1' ,'Importaçao dos arquivos de consignados' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 5106 ,10232 ,18 ,952 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10234 ,'Importar Arquivo' ,'Importar dados do arquivo ' ,'pes4_importararquivoconsignado001.php' ,'1' ,'1' ,'Importar dados do arquivo ' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10232 ,10234 ,1 ,952 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10235 ,'Exportar Arquivo' ,'Exportar os dados do arquivo para a caixa' ,'pes4_exportararquivoconsignado001.php' ,'1' ,'1' ,'Exportar os dados do arquivo para a caixa' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10232 ,10235 ,3 ,952 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10238 ,'Conferência de Dados' ,'Conferência de Dados' ,'pes4_conferenciaconsignados001.php' ,'1' ,'1' ,'Conferência de Dados' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10232 ,10238 ,2 ,952 );

update db_layouttxt set db50_codigo = 1 , db50_layouttxtgrupo = 1 , db50_descr = 'BANRISUL BLV' , db50_quantlinhas = 0 , db50_obs = '' where db50_codigo = 1;
insert into db_layouttxt( db50_codigo ,db50_layouttxtgrupo ,db50_descr ,db50_quantlinhas ,db50_obs ) values ( 254 ,2 ,'CONSIGNADO CAIXA' ,0 ,'' );
insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 836 ,254 ,'HEADER' ,1 ,150 ,0 ,0 ,'' ,'' ,'0' );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13212 ,836 ,'codigo_do_registro' ,'CODIGO DO REGISTRO' ,2 ,1 ,'1' ,1 ,'t' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13213 ,836 ,'codigo_convenente' ,'CODIGO CONVENENTE' ,2 ,2 ,'' ,5 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13214 ,836 ,'dv_convenente' ,'DV-CONVENENTE' ,2 ,7 ,'' ,1 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13215 ,836 ,'n_extrato_convenente' ,'Nº EXTRATO CONVENENTE' ,2 ,8 ,'' ,3 ,'f' ,'t' ,'e' ,'' ,0 );
update db_layoutcampos set db52_codigo = 13214 , db52_layoutlinha = 836 , db52_nome = 'dv_convenente' , db52_descr = 'DV-CONVENENTE' , db52_layoutformat = 2 , db52_posicao = 7 , db52_default = '' , db52_tamanho = 1 , db52_ident = 't' , db52_imprimir = 't' , db52_alinha = 'e' , db52_obs = '' , db52_quebraapos = 0 where db52_codigo = 13214;
update db_layoutcampos set db52_posicao = db52_posicao+0 where db52_layoutlinha = 836 and db52_posicao >= 7 and db52_codigo <> 13214;
update db_layoutcampos set db52_codigo = 13214 , db52_layoutlinha = 836 , db52_nome = 'dv_convenente' , db52_descr = 'DV-CONVENENTE' , db52_layoutformat = 2 , db52_posicao = 7 , db52_default = '' , db52_tamanho = 1 , db52_ident = 't' , db52_imprimir = 't' , db52_alinha = 'e' , db52_obs = '' , db52_quebraapos = 0 where db52_codigo = 13214;
update db_layoutcampos set db52_posicao = db52_posicao+0 where db52_layoutlinha = 836 and db52_posicao >= 7 and db52_codigo <> 13214;
update db_layoutcampos set db52_codigo = 13215 , db52_layoutlinha = 836 , db52_nome = 'n_extrato_convenente' , db52_descr = 'Nº EXTRATO CONVENENTE' , db52_layoutformat = 2 , db52_posicao = 8 , db52_default = '' , db52_tamanho = 3 , db52_ident = 't' , db52_imprimir = 't' , db52_alinha = 'e' , db52_obs = '' , db52_quebraapos = 0 where db52_codigo = 13215;
update db_layoutcampos set db52_codigo = 13213 , db52_layoutlinha = 836 , db52_nome = 'codigo_convenente' , db52_descr = 'CODIGO CONVENENTE' , db52_layoutformat = 2 , db52_posicao = 2 , db52_default = '' , db52_tamanho = 5 , db52_ident = 't' , db52_imprimir = 't' , db52_alinha = 'e' , db52_obs = '' , db52_quebraapos = 0 where db52_codigo = 13213;
update db_layoutcampos set db52_posicao = db52_posicao+0 where db52_layoutlinha = 836 and db52_posicao >= 2 and db52_codigo <> 13213;
update db_layoutcampos set db52_codigo = 13213 , db52_layoutlinha = 836 , db52_nome = 'codigo_convenente' , db52_descr = 'CODIGO CONVENENTE' , db52_layoutformat = 2 , db52_posicao = 2 , db52_default = '' , db52_tamanho = 5 , db52_ident = 'f' , db52_imprimir = 't' , db52_alinha = 'e' , db52_obs = '' , db52_quebraapos = 0 where db52_codigo = 13213;
update db_layoutcampos set db52_posicao = db52_posicao+0 where db52_layoutlinha = 836 and db52_posicao >= 2 and db52_codigo <> 13213;
update db_layoutcampos set db52_codigo = 13214 , db52_layoutlinha = 836 , db52_nome = 'dv_convenente' , db52_descr = 'DV-CONVENENTE' , db52_layoutformat = 2 , db52_posicao = 7 , db52_default = '' , db52_tamanho = 1 , db52_ident = 'f' , db52_imprimir = 't' , db52_alinha = 'e' , db52_obs = '' , db52_quebraapos = 0 where db52_codigo = 13214;
update db_layoutcampos set db52_posicao = db52_posicao+0 where db52_layoutlinha = 836 and db52_posicao >= 7 and db52_codigo <> 13214;
update db_layoutcampos set db52_codigo = 13215 , db52_layoutlinha = 836 , db52_nome = 'n_extrato_convenente' , db52_descr = 'Nº EXTRATO CONVENENTE' , db52_layoutformat = 2 , db52_posicao = 8 , db52_default = '' , db52_tamanho = 3 , db52_ident = 'f' , db52_imprimir = 't' , db52_alinha = 'e' , db52_obs = '' , db52_quebraapos = 0 where db52_codigo = 13215;
update db_layoutcampos set db52_posicao = db52_posicao+0 where db52_layoutlinha = 589 and db52_posicao >= 1 and db52_codigo <> 1000000;
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13216 ,836 ,'dv_extrato_corrente' ,'DV EXTRATO CONVENENTE' ,2 ,11 ,'' ,1 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13217 ,836 ,'nome_convenente' ,'NOME CONVENENTE' ,2 ,12 ,'' ,35 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13218 ,836 ,'identificacao_do_sistema' ,'IDENTIFICAÇÃO DO SISTEMA' ,1 ,47 ,'SIAPX' ,9 ,'f' ,'t' ,'d' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13219 ,836 ,'agencia_responsavel_centralizadora' ,'AGENCIA_RESPONSAVEL_(CENTRALIZADORA)' ,2 ,56 ,'' ,4 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13220 ,836 ,'identificacao_operacao' ,'IDENTIFICAÇÃO OPERAÇÃO' ,1 ,60 ,'CONSIGNAÇÕES' ,12 ,'f' ,'t' ,'d' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13221 ,836 ,'codigo_remessa_retorno' ,'CÓDIGO REMESSA/RETORNO' ,2 ,72 ,'1' ,1 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13222 ,836 ,'nome_remessa_retorno' ,'NOME REMESSA RETORNO' ,1 ,73 ,'Remessa' ,7 ,'f' ,'t' ,'d' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13223 ,836 ,'data_vencimento_extrato' ,'DATA VENCIMENTO EXTRATO' ,4 ,80 ,'' ,8 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13224 ,836 ,'data_geracao_remessa' ,'DATA GERAÇÃO REMESSA' ,4 ,88 ,'' ,8 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13225 ,836 ,'data_geracao_retorno' ,'DATA GERAÇÃO RETORNO' ,4 ,96 ,'' ,8 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13226 ,836 ,'campo_reservado' ,'CAMPO RESERVADO' ,1 ,104 ,'' ,47 ,'f' ,'t' ,'d' ,'' ,0 );
insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 837 ,254 ,'DETALHE' ,2 ,150 ,0 ,0 ,'' ,'' ,'0' );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13227 ,837 ,'codigo_do_registro' ,'CODIGO DO REGISTRO' ,1 ,1 ,'2' ,1 ,'t' ,'t' ,'d' ,'' ,0 );
update db_layoutlinha set db51_codigo = 837 , db51_layouttxt = 254 , db51_descr = 'DETALHE' , db51_tipolinha = 3 , db51_tamlinha = 150 , db51_linhasantes = 0 , db51_linhasdepois = 0 , db51_obs = '' , db51_separador = '' , db51_compacta = '0' where db51_codigo = 837;
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13228 ,837 ,'codigo_convenente' ,'CODIGO_CONVENENTE' ,2 ,2 ,'' ,5 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13229 ,837 ,'dv_convenente' ,'DV CONVENENTE' ,2 ,7 ,'' ,1 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13230 ,837 ,'n_extrato_convenente' ,'Nº EXTRATO CONVENENTE' ,2 ,8 ,'' ,3 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13231 ,837 ,'dv_extrato_convenente' ,'DV-EXTRATO CONVENENTE' ,2 ,11 ,'' ,1 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13232 ,837 ,'n_sequencial' ,'Nº SEQUENCIAL' ,2 ,12 ,'' ,5 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13233 ,837 ,'prazo_vencimento_contrato' ,'PRAZO VENCIMENTO CONTRATO' ,2 ,17 ,'' ,3 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13234 ,837 ,'prazo_remanescente' ,'PRAZO REMANESCENTE' ,2 ,20 ,'' ,3 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13235 ,837 ,'n_contrato' ,'Nº CONTRATO' ,2 ,23 ,'' ,18 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13236 ,837 ,'n_prestacao' ,'Nº PRESTAÇÃO' ,2 ,41 ,'' ,18 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13237 ,837 ,'n_matricula' ,'Nº MATRÍCULA' ,1 ,59 ,'' ,12 ,'f' ,'t' ,'d' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13238 ,837 ,'nome_cliente' ,'NOME CLIENTE' ,1 ,71 ,'' ,35 ,'f' ,'t' ,'d' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13239 ,837 ,'valor_prestacao' ,'VALOR PRESTAÇÃO' ,2 ,106 ,'' ,17 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13240 ,837 ,'codigo_ocorrencia_processamento' ,'CODIGO OCORRÊNCIA PROCESSAMENTO' ,2 ,123 ,'' ,2 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13241 ,837 ,'n_conta_corrente' ,'Nº CONTA CORRENTE' ,2 ,125 ,'' ,16 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13242 ,837 ,'cpf' ,'CPF' ,1 ,141 ,'' ,14 ,'f' ,'t' ,'d' ,'' ,0 );
update db_layoutcampos set db52_codigo = 13236 , db52_layoutlinha = 837 , db52_nome = 'n_prestacao' , db52_descr = 'Nº PRESTAÇÃO' , db52_layoutformat = 2 , db52_posicao = 41 , db52_default = '' , db52_tamanho = 2 , db52_ident = 'f' , db52_imprimir = 't' , db52_alinha = 'e' , db52_obs = '' , db52_quebraapos = 0 where db52_codigo = 13236;
update db_layoutcampos set db52_posicao = db52_posicao+-16 where db52_layoutlinha = 837 and db52_posicao >= 41 and db52_codigo <> 13236;
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13243 ,837 ,'campo_reservado' ,'CAMPO RESERVADO' ,1 ,139 ,'' ,12 ,'f' ,'t' ,'d' ,'' ,0 );
insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 838 ,254 ,'REGISTRO' ,5 ,150 ,0 ,0 ,'' ,'' ,'1' );
update db_layoutlinha set db51_codigo = 837 , db51_layouttxt = 254 , db51_descr = 'DETALHE' , db51_tipolinha = 3 , db51_tamlinha = 150 , db51_linhasantes = 0 , db51_linhasdepois = 0 , db51_obs = '' , db51_separador = '' , db51_compacta = '0' where db51_codigo = 837;
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13244 ,838 ,'codigo_do_registro' ,'CODIGO_DO_REGISTRO' ,2 ,1 ,'3' ,1 ,'t' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13245 ,838 ,'codigo_convenente' ,'CÓDIGO CONVENENTE' ,2 ,2 ,'' ,5 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13246 ,838 ,'dv_convenente' ,'DV CONVENENTE' ,2 ,7 ,'' ,1 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13247 ,838 ,'n_extrato_convenente' ,'Nº EXTRATO CONVENENTE' ,2 ,8 ,'' ,3 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13248 ,838 ,'dv_extrato_convenente' ,'DV-EXTRATO CONVENENTE' ,2 ,11 ,'' ,1 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13249 ,838 ,'quantidade_registro' ,'QUANTIDADE REGISTRO' ,2 ,12 ,'' ,5 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13250 ,838 ,'valor_total_expectativa' ,'VALOR TOTAL EXPECTATIVA' ,2 ,17 ,'' ,17 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13251 ,838 ,'valor_total_rejeicoes' ,'VALOR TOTAL REJEIÇÕES' ,2 ,34 ,'' ,17 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13252 ,838 ,'valor_total_acatados' ,'VALOR TOTAL ACATADOS' ,2 ,51 ,'' ,17 ,'f' ,'t' ,'e' ,'' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13253 ,838 ,'campo_reservado' ,'CAMPO RESERVADO' ,2 ,68 ,'' ,83 ,'f' ,'t' ,'e' ,'' ,0 );


insert into pessoal.rhconsignadomotivo values (8, 'EXCLUIDO');
insert into pessoal.rhconsignadomotivo values (9, 'SALDO INSUFICIENTE');



select fc_executa_ddl('ALTER TABLE avaliacaopergunta ADD COLUMN db103_tipo int4  default 1 NOT NULL');
select fc_executa_ddl('ALTER TABLE avaliacaopergunta ADD COLUMN db103_mascara  varchar(40)');

select fc_executa_ddl('insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values
  ( 21839 ,''db103_tipo'' ,''int4'' ,''Tipo de dado para criação do formulário.'' ,'''' ,''Tipo'' ,5 ,''false'' ,''false'' ,''false'' ,1 ,''text'' ,''Tipo'' ),
  ( 21840 ,''db103_mascara'' ,''varchar(40)'' ,''Máscara para geração do formulário.'' ,'''' ,''Máscara'' ,40 ,''true'' ,''true'' ,''false'' ,0 ,''text'' ,''Máscara'' );');
                              
select fc_executa_ddl('insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values
  ( 2983 ,21839 , 9 ,0 ),     
  ( 2983 ,21840 ,10 ,0 );');  
                                                                                                                                                                                         
select fc_executa_ddl('insert into db_sysforkey values(3923,21791,1,2983,0);');
select fc_executa_ddl('insert into db_sysforkey values(3923,21790,1,3820,0);');

select fc_executa_ddl('ALTER TABLE avaliacaoperguntadb_formulas
  ADD CONSTRAINT db_formulas_fk
  FOREIGN KEY (eso01_db_formulas) REFERENCES db_formulas;');

select fc_executa_ddl('ALTER TABLE avaliacaoperguntadb_formulas
  ADD CONSTRAINT avaliacaopergunta_fk
  FOREIGN KEY (eso01_avaliacaopergunta) REFERENCES avaliacaopergunta;');

--
-- avaliacaotipo;
--
select fc_executa_ddl('INSERT INTO avaliacaotipo values(5, \'eSocial\');');

--
-- avaliacao;
--
select fc_executa_ddl('INSERT INTO avaliacao VALUES (3000008, 5, \'eSocial S2100\', \'\', true, \'esocial_s2100\')');


--
-- Data for Name: avaliacaogrupopergunta; Type: TABLE DATA; Schema: habitacao; Owner: postgres
--
select fc_executa_ddl('INSERT INTO avaliacaogrupopergunta 
  (db102_sequencial, db102_avaliacao, db102_descricao, db102_identificador) 
  VALUES 
  (3000036, 3000008, \'Dados de Nascimento\', \'dados_nascimento\'),
  (3000037, 3000008, \'Registro de Indentidade Civil (RIC)\', \'registro_identidade\'),
  (3000038, 3000008, \'Carteira de Trabalho (CTPS)\', \'carteira_trabalho\'),
  (3000039, 3000008, \'Registro Geral (RG)\', \'registro_geral\'),
  (3000040, 3000008, \'Registro Nacional Estrangeiro (RNE)\', \'registro_naciona_estrangeiro\'),
  (3000041, 3000008, \'Órgão de Classe (OC)\', \'orgao_classe\'),
  (3000042, 3000008, \'Carteira Nacional de Habilitação (CNH)\', \'carteira_nacional_habilitacao\'),
  (3000043, 3000008, \'Endereço\', \'endereco\'),
  (3000044, 3000008, \'Trabalhador Estrangeiro (caso seja estrangeiro)\', \'trabalhador_estrangeiro\'),
  (3000045, 3000008, \'Trabalhador com deficiência(caso possua)\', \'trabalhador_deficiencia\'),
  (3000046, 3000008, \'Informações de Readaptação(caso possua)\', \'informacoes_readaptacao\'),
  (3000047, 3000008, \'Dependente 1 (Sal. Família e/ou IRRF)\', \'dependente1\'),
  (3000048, 3000008, \'Dependente 2 (Sal. Família e/ou IRRF)\', \'dependente2\'),
  (3000049, 3000008, \'Dependente 3 (Sal. Família e/ou IRRF)\', \'dependente3\'),
  (3000050, 3000008, \'Dependente 4 (Sal. Família e/ou IRRF)\', \'dependente4\'),
  (3000051, 3000008, \'Dependente 5 (Sal. Família e/ou IRRF)\', \'dependente5\'),
  (3000052, 3000008, \'Dependente 6 (Sal. Família e/ou IRRF)\', \'dependente6\'),
  (3000053, 3000008, \'Dependente 7 (Sal. Família e/ou IRRF)\', \'dependente7\'),
  (3000054, 3000008, \'Dependente 8 (Sal. Família e/ou IRRF)\', \'dependente8\'),
  (3000055, 3000008, \'Dependente 9 (Sal. Família e/ou IRRF)\', \'dependente9\'),
  (3000056, 3000008, \'Dependente 10 (Sal. Família e/ou IRRF)\', \'dependente10\'),
  (3000057, 3000008, \'Aposentadoria\', \'aposentadoria\'),
  (3000058, 3000008, \'Informações de Contato\', \'informacoes_contato\'),
  (3000059, 3000008, \'Vínculo de Trabalho\', \'vinculo_trabalho\'),
  (3000060, 3000008, \'Celetista\', \'celetista\'),
  (3000061, 3000008, \'Estatutário\', \'estatutario\'),
  (3000062, 3000008, \'Contrato temporário\', \'contrato_temporario\'),
  (3000063, 3000008, \'Dados do Vínculo\', \'dados_vinculo\'),
  (3000064, 3000008, \'Remuneração\', \'remuneracao\'),
  (3000065, 3000008, \'Local de Trabalho\', \'local_trabalho\'),
  (3000066, 3000008, \'Horário Contratual\', \'horario_contratual\'),
  (3000067, 3000008, \'Filiação Sindical\', \'filiacao_sindical\'),
  (3000035, 3000008, \'Dados Pessoais\', \'dados_pessoais\');');


--
-- Data for Name: avaliacaopergunta; Type: TABLE DATA; Schema: habitacao; Owner: postgres
--
select fc_executa_ddl('INSERT INTO avaliacaopergunta 
  (db103_sequencial, db103_avaliacaotiporesposta, db103_avaliacaogrupopergunta, db103_descricao, db103_obrigatoria, db103_ativo, db103_ordem, db103_identificador, db103_tipo, db103_mascara) 
VALUES 
  (3000232, 1, 3000044, \'Classificação da Condição\', false, true, 2, \'classificacao_condicao\', 6, \'\'),
  (3000231, 1, 3000046, \'Readaptado\', false, true, 2, \'readaptado\', 1, \'\'),
  (3000233, 1, 3000047, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_1\', 6, \'\'),
  (3000235, 1, 3000047, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_1\', 1, \'\'),
  (3000236, 1, 3000044, \'Casado com brasileiro\', false, true, 3, \'casado_brasileiro\', 1, \'\'),
  (3000237, 1, 3000044, \'Tem filhos brasileiros\', false, true, 4, \'filhos_brasileiros\', 1, \'\'),
  (3000238, 1, 3000045, \'Indicar se é portador de Deficiência\', false, true, 1, \'portador_deficiencia\', 1, \'\'),
  (3000239, 3, 3000045, \'Que tipo de Deficiência\', false, true, 2, \'tipo_deficiencia\', 1, \'\'),
  (3000240, 2, 3000047, \'Nome do Dependente:\', true, true, 3, \'nome_dependente_1\', 1, \'\'),
  (3000241, 2, 3000047, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_1\', 5, \'\'),
  (3000243, 2, 3000047, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_1\', 4, \'\'),
  (3000244, 1, 3000046, \'Reabilitado(INSS)\', false, true, 1, \'reabilitado\', 1, \'\'),
  (3000245, 1, 3000057, \'Por qual regime previdenciário?\', false, true, 3, \'por_qual_regime_previdenciario\', 6, \'\'),
  (3000246, 2, 3000044, \'Data da chegada ao Brasil\', false, true, 1, \'data_chegada_brasil\', 5, \'\'),
  (3000247, 2, 3000043, \'País\', true, true, 8, \'pais_endereco\', 1, \'\'),
  (3000248, 1, 3000057, \'Recebe aposentadoria especial (professor ou por exposição a agentes nocivos)?\', true, true, 2, \'aposentadoria_especial\', 6, \'\'),
  (3000249, 2, 3000043, \'Município\', true, true, 5, \'municipio\', 1, \'\'),
  (3000250, 1, 3000057, \'Recebe benefício de aposentadoria por contribuição ou idade?\', true, true, 1, \'aposentadoria_contribuicao_idade\', 6, \'\'),
  (3000251, 2, 3000043, \'CEP\', true, true, 6, \'cep\', 2, \'\'),
  (3000258, 2, 3000043, \'UF\', true, true, 7, \'endereco_uf\', 1, \'\'),
  (3000261, 2, 3000058, \'Email:\', false, true, 4, \'email\', 1, \'\'),
  (3000263, 2, 3000043, \'Complemento\', false, true, 3, \'complemento\', 1, \'\'),
  (3000266, 2, 3000058, \'Email alternativo:\', false, true, 5, \'email_alternativo\', 1, \'\'),
  (3000270, 2, 3000043, \'Bairro\', false, true, 4, \'bairro\', 1, \'\'),
  (3000272, 2, 3000058, \'Telefone Residencial:\', false, true, 1, \'telefone_residencial\', 6, \'\'),
  (3000273, 2, 3000043, \'Nome do Logradouro\', true, true, 1, \'nome_logradouro\', 1, \'\'),
  (3000274, 2, 3000058, \'Telefone Celular:\', false, true, 2, \'telefone_celular\', 6, \'\'),
  (3000275, 2, 3000058, \'Telefone alternativo (caso possua mais de um número)\', false, true, 3, \'telefone_alternativo\', 6, \'\'),
  (3000276, 2, 3000043, \'Número\', true, true, 2, \'numero\', 6, \'\'),
  (3000277, 2, 3000059, \'Matrícula ou CGM\', true, true, 1, \'matricula\', 6, \'\'),
  (3000278, 2, 3000042, \'Data de Expedição\', false, true, 2, \'cnh_data_expedicao\', 5, \'\'),
  (3000279, 1, 3000059, \'Regime de Trabalho\', true, true, 2, \'regime_trabalho\', 6, \'\'),
  (3000280, 2, 3000042, \'UF\', true, true, 1, \'cnh_uf\', 1, \'\'),
  (3000281, 2, 3000042, \'Data Validade\', true, true, 3, \'cnh_data_validade\', 5, \'\'),
  (3000282, 1, 3000059, \'Regime Previdenciário:\', true, true, 3, \'regime_previdenciario\', 6, \'\'),
  (3000288, 2, 3000042, \'Data da primeira habilitação\', false, true, 5, \'cnh_data_primeira_habilitacao\', 5, \'\'),
  (3000289, 2, 3000036, \'Munícipio de Nascimento\', false, true, 2, \'municipio_nascimento\', 1, \'\'),
  (3000291, 2, 3000042, \'Categoria CNH\', true, true, 6, \'cnh_categoria\', 1, \'\'),
  (3000292, 2, 3000036, \'País\', true, true, 4, \'pais\', 1, \'\'),
  (3000293, 1, 3000060, \'Optante pelo FGTS:\', true, true, 8, \'fgts\', 6, \'\'),
  (3000294, 2, 3000036, \'Nome da Mãe\', false, true, 5, \'nome_mae\', 1, \'\'),
  (3000295, 2, 3000041, \'Data de Validade\', false, true, 4, \'oc_data_validade\', 5, \'\'),
  (3000296, 2, 3000036, \'Nome do Pai\', false, true, 6, \'nome_pai\', 1, \'\'),
  (3000297, 1, 3000063, \'Duração do contrato de trabalho:\', true, true, 4, \'duracao_contrato_trabalho\', 1, \'\'),
  (3000298, 2, 3000042, \'Número do Registro da CNH\', true, true, 1, \'cnh_numero_registro_cnh\', 6, \'\'),
  (3000299, 2, 3000038, \'Número da CTPS\', true, true, 1, \'numero_ctps\', 6, \'\'),
  (3000300, 2, 3000038, \'Série\', true, true, 2, \'serie\', 6, \'\'),
  (3000301, 2, 3000040, \'Data Expedição\', false, true, 3, \'rne_data_expedicao\', 5, \'\'),
  (3000302, 2, 3000062, \'Data da contratação:\', true, true, 1, \'data_contratacao\', 5, \'\'),
  (3000303, 2, 3000041, \'Número do Órgão Classe\', true, true, 1, \'numero_orgao_classe\', 1, \'\'),
  (3000304, 2, 3000041, \'Órgão Emissor\', true, true, 2, \'oc_orgao_emissor\', 1, \'\'),
  (3000305, 2, 3000038, \'UF\', true, true, 3, \'ctps_uf\', 1, \'\'),
  (3000306, 2, 3000060, \'CNPJ do Sindicato da Categoria Profissional:\', false, true, 7, \'cnpj_sindicato_profissional\', 3, \'\'),
  (3000330, 1, 3000048, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_2\', 6, \'\'),
  (3000307, 2, 3000062, \'Data prevista para o término da contratação:\', false, true, 2, \'data_prevista_termino_contratacao\', 5, \'\'),
  (3000308, 2, 3000041, \'Data de Expedição\', false, true, 3, \'oc_data_expedicao\', 5, \'\'),
  (3000309, 2, 3000037, \'Número do RIC\', true, true, 1, \'numero_ric\', 6, \'\'),
  (3000310, 2, 3000037, \'Órgão Emissor\', true, true, 2, \'orgao_emissor\', 1, \'\'),
  (3000311, 2, 3000060, \'Data base da Revisão Geral Anual (Preenchimento pelo Município):\', false, true, 6, \'data_base_revisao_geral\', 5, \'\'),
  (3000312, 1, 3000062, \'Motivo da contratação (preenchimento pelo Município):\', false, true, 3, \'motivo_contratacao\', 1, \'\'),
  (3000313, 2, 3000037, \'Data da Expedição\', false, true, 3, \'data_expedicao\', 5, \'\'),
  (3000314, 2, 3000039, \'Número do RG\', true, true, 1, \'numero_rg\', 1, \'\'),
  (3000315, 2, 3000040, \'Número do RNE\', false, true, 1, \'numero_rne\', 1, \'\'),
  (3000316, 2, 3000040, \'Órgão Emissor\', false, true, 2, \'rne_orgao_emissor\', 1, \'\'),
  (3000317, 2, 3000039, \'Órgão Emissor\', true, true, 2, \'rg_orgao_emissor\', 1, \'\'),
  (3000318, 2, 3000060, \'Data da Admissão\', true, true, 1, \'data_admissao\', 5, \'\'),
  (3000319, 2, 3000062, \'Identificação do trabalhador substituído (preenchimento pelo Munícipio):\', false, true, 4, \'identificacao_trabalhador_substituido\', 6, \'\'),
  (3000320, 2, 3000039, \'Data Expedição\', false, true, 3, \'rg_data_expedicao\', 5, \'\'),
  (3000321, 1, 3000061, \'Indicativo de Provimento\', true, true, 1, \'indicativo_provimento\', 6, \'\'),
  (3000322, 1, 3000061, \'Tipo de Provimento\', true, true, 2, \'tipo_provimento\', 1, \'\'),
  (3000323, 2, 3000061, \'Data da Nomeação:\', true, true, 3, \'data_nomeacao\', 5, \'\'),
  (3000328, 2, 3000061, \'Data da Posse:\', true, true, 4, \'data_posse\', 5, \'\'),
  (3000329, 2, 3000061, \'Data do Exercício:\', true, true, 5, \'data_exercicio\', 5, \'\'),
  (3000324, 1, 3000060, \'Tipo de Admissão:\', true, true, 2, \'tipo_admissao\', 6, \'\'),
  (3000325, 1, 3000060, \'Indicativo de Admissão:\', true, true, 3, \'indicativo_admissao\', 6, \'\'),
  (3000326, 1, 3000060, \'Regime de Jornada de Trabalho\', true, true, 4, \'regime_jornada_trabalho\', 6, \'\'),
  (3000327, 1, 3000060, \'Natureza da Atividade:\', true, true, 5, \'natureza_atividade\', 1, \'\'),
  (3000331, 1, 3000048, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_2\', 1, \'\'),
  (3000332, 2, 3000048, \'Nome do Dependente:\', true, true, 3, \'nome_dependente_2\', 1, \'\'),
  (3000333, 2, 3000048, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_2\', 5, \'\'),
  (3000334, 2, 3000048, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_2\', 4, \'\'),
  (3000335, 1, 3000049, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_3\', 6, \'\'),
  (3000336, 1, 3000049, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_3\', 1, \'\'),
  (3000337, 2, 3000049, \'Nome do Dependente:\', true, true, 3, \'nome_dependente_3\', 1, \'\'),
  (3000338, 2, 3000049, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_3\', 5, \'\'),
  (3000339, 2, 3000049, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_3\', 4, \'\'),
  (3000340, 1, 3000050, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_4\', 6, \'\'),
  (3000341, 1, 3000050, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_4\', 1, \'\'),
  (3000342, 2, 3000050, \'Nome do Dependente:\', true, true, 3, \'nome_dependente__4\', 1, \'\'),
  (3000343, 2, 3000050, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_4\', 5, \'\'),
  (3000344, 2, 3000050, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_4\', 4, \'\'),
  (3000345, 1, 3000051, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_5\', 6, \'\'),
  (3000346, 1, 3000051, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_5\', 1, \'\'),
  (3000347, 2, 3000051, \'Nome do Dependente:\', true, true, 3, \'nome_dependente_5\', 1, \'\'),
  (3000226, 2, 3000035, \'Nome\', true, true, 1, \'nome\', 1, \'\'),
  (3000227, 2, 3000035, \'CPF\', true, true, 2, \'cpf\', 4, \'\'),
  (3000228, 2, 3000035, \'PIS/PASEP/NIS\', true, true, 3, \'pis_pasep_nis\', 6, \'\'),
  (3000229, 1, 3000035, \'Sexo\', true, true, 4, \'sexo\', 1, \'\'),
  (3000230, 1, 3000035, \'Raça/Cor\', true, true, 5, \'raca_cor\', 1, \'\'),
  (3000234, 2, 3000067, \'CNPJ do Sindicado\', false, true, 1, \'cnpj_sindicato\', 3, \'\'),
  (3000242, 2, 3000066, \'Quantidade média de horas (preenchimento pelo Município)\', false, true, 1, \'quantidade_media_horas\', 6, \'\'),
  (3000252, 1, 3000066, \'Tipos de jornada:\', true, true, 2, \'tipos_jornada\', 6, \'\'),
  (3000253, 1, 3000035, \'Estado Civil:\', true, true, 6, \'estado_civil\', 1, \'\'),
  (3000254, 2, 3000066, \'Informações diárias do horário contratual:\', true, true, 3, \'informacoes_diarias_horario_contratual\', 6, \'\'),
  (3000255, 2, 3000066, \'Segunda-feira:\', false, true, 4, \'segunda_feira\', 9, \'\'),
  (3000256, 2, 3000066, \'Terça-feira:\', false, true, 5, \'terca_feira\', 9, \'\'),
  (3000257, 2, 3000066, \'Quarta-feira:\', false, true, 6, \'quarta_feira\', 9, \'\'),
  (3000259, 1, 3000035, \'Grau Instrução:\', true, true, 7, \'grau_instrucao\', 1, \'\'),
  (3000260, 2, 3000066, \'Domingo:\', false, true, 10, \'domingo\', 9, \'\'),
  (3000262, 2, 3000066, \'Dia variável:\', false, true, 11, \'dia_variavel\', 9, \'\'),
  (3000264, 2, 3000066, \'Quinta-feira:\', false, true, 7, \'quinta_feira\', 9, \'\'),
  (3000265, 2, 3000066, \'Sexta-feira:\', false, true, 8, \'sexta_feira\', 9, \'\'),
  (3000267, 2, 3000066, \'Sábado:\', false, true, 9, \'sabado\', 9, \'\'),
  (3000268, 2, 3000065, \'Lotação:\', false, true, 1, \'lotacao\', 1, \'\'),
  (3000269, 2, 3000065, \'Endereço (se fora da sede da Prefeitura):\', false, true, 2, \'endereco\', 1, \'\'),
  (3000271, 2, 3000064, \'Remuneração\', true, true, 1, \'remuneracao\', 8, \'\'),
  (3000283, 1, 3000064, \'Unidade de Pagamento:\', true, true, 2, \'unidade_pagamento\', 1, \'\'),
  (3000284, 2, 3000063, \'Nome e código do Cargo:\', false, true, 1, \'nome_codigo_cargo\', 1, \'\'),
  (3000285, 2, 3000063, \'Nome e código da Função\', false, true, 2, \'nome_codigo_funcao\', 1, \'\'),
  (3000286, 1, 3000063, \'Código da Categoria:\', true, true, 3, \'codigo_categoria\', 1, \'\'),
  (3000287, 2, 3000036, \'Data de Nascimento\', true, true, 1, \'data_nascimento\', 5, \'\'),
  (3000290, 2, 3000036, \'UF\', false, true, 3, \'uf\', 1, \'\'),
  (3000348, 2, 3000051, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_5\', 5, \'\'),
  (3000349, 2, 3000051, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_5\', 4, \'\'),
  (3000350, 1, 3000052, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_6\', 6, \'\'),
  (3000351, 1, 3000052, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_6\', 1, \'\'),
  (3000352, 2, 3000052, \'Nome do Dependente:\', true, true, 3, \'nome_dependente_6\', 1, \'\'),
  (3000353, 2, 3000052, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_6\', 5, \'\'),
  (3000354, 2, 3000052, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_6\', 4, \'\'),
  (3000355, 1, 3000053, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_7\', 6, \'\'),
  (3000356, 1, 3000053, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_7\', 1, \'\'),
  (3000357, 2, 3000053, \'Nome do Dependente:\', true, true, 3, \'nome_dependente__7\', 1, \'\'),
  (3000358, 2, 3000053, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_7\', 5, \'\'),
  (3000359, 2, 3000053, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_7\', 4, \'\'),
  (3000360, 1, 3000054, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_8\', 6, \'\'),
  (3000361, 1, 3000054, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_8\', 1, \'\'),
  (3000362, 2, 3000054, \'Nome do Dependente:\', true, true, 3, \'nome_dependente_8\', 1, \'\'),
  (3000363, 2, 3000054, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_8\', 5, \'\'),
  (3000364, 2, 3000054, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_8\', 4, \'\'),
  (3000365, 1, 3000055, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_9\', 6, \'\'),
  (3000366, 1, 3000055, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_9\', 1, \'\'),
  (3000367, 2, 3000055, \'Nome do Dependente:\', true, true, 3, \'nome_dependente_9\', 1, \'\'),
  (3000368, 2, 3000055, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_9\', 5, \'\'),
  (3000369, 2, 3000055, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_9\', 4, \'\'),
  (3000370, 1, 3000056, \'Qualidade do Dependente:\', true, true, 1, \'qualidade_dependente_10\', 6, \'\'),
  (3000371, 1, 3000056, \'Tipo de Dependente:\', true, true, 2, \'tipo_dependente_10\', 1, \'\'),
  (3000372, 2, 3000056, \'Nome do Dependente:\', true, true, 3, \'nome_dependente__10\', 1, \'\'),
  (3000373, 2, 3000056, \'Data de Nascimento do Dependente:\', true, true, 4, \'data_nascimento_dependente_10\', 5, \'\'),
  (3000374, 2, 3000056, \'CPF do dependente (Obrigatório para maiores de 16 anos):\', false, true, 5, \'cpf_dependente_10\', 4, \'\');');

--
-- Data for Name: avaliacaoperguntaopcao; Type: TABLE DATA; Schema: habitacao; Owner: postgres
select fc_executa_ddl('INSERT INTO avaliacaoperguntaopcao 
  (db104_sequencial, db104_avaliacaopergunta, db104_descricao, db104_aceitatexto, db104_identificador, db104_peso) 
VALUES
  (3001097, 3000236, \'Sim\', false, \'filhos_sim\', 0),
  (3000919, 3000226, NULL, true, \'nome_2\', 0),
  (3000920, 3000227, NULL, true, \'cpf_2\', 0),
  (3000921, 3000228, NULL, true, \'pis_pasep_nis_2\', 0),
  (3000922, 3000229, \'Feminino\', false, \'feminino\', 0),
  (3000923, 3000229, \'Masculino\', false, \'masculino\', 0),
  (3000924, 3000230, \'Branca\', false, \'branca\', 0),
  (3000925, 3000230, \'Negra\', false, \'negra\', 0),
  (3000926, 3000230, \'Parda\', false, \'parda\', 0),
  (3000927, 3000230, \'Amarela\', false, \'amarela\', 0),
  (3000928, 3000230, \'Indígena\', false, \'indigena\', 0),
  (3000929, 3000230, \'Não informado\', false, \'nao_informado\', 0),
  (3000930, 3000234, NULL, true, \'cnpj_sindicato_2\', 0),
  (3000931, 3000242, NULL, true, \'quantidade_media_horas_2\', 0),
  (3000932, 3000252, \'Jornada semanal com apenas um horário padrão por dia da semana e folga fixa\', false, \'jornada_semananal_padrao_semana_folga_fixa\', 0),
  (3000933, 3000253, \'Solteiro/União Estável\', false, \'solteiro\', 0),
  (3000934, 3000253, \'Casado\', false, \'casado\', 0),
  (3000935, 3000252, \'Demais tipos de jornada (escala, turno de revezamento, permutas, horários rotativos) Nesse caso, descrever o tipo de jornada. (Exemplo: escala, 12 x 36):\'\'\', true, \'demais_tipos_jornada\', 0),
  (3000936, 3000253, \'Divorciado\', false, \'divorciado\', 0),
  (3000937, 3000253, \'Separado\', false, \'separado\', 0),
  (3000938, 3000253, \'Viúvo\', false, \'viuvo\', 0),
  (3000939, 3000254, NULL, true, \'informacoes_diarias_horario_contratual_2\', 0),
  (3000940, 3000255, NULL, true, \'segunda_feira_2\', 0),
  (3000941, 3000256, NULL, true, \'terca_feira_2\', 0),
  (3000942, 3000257, NULL, true, \'quarta_feira_2\', 0),
  (3000943, 3000264, NULL, true, \'quinta_feira_2\', 0),
  (3000944, 3000265, NULL, true, \'sexta_feira_2\', 0),
  (3000945, 3000267, NULL, true, \'sabado_2\', 0),
  (3000946, 3000260, NULL, true, \'domingo_2\', 0),
  (3000947, 3000262, NULL, true, \'dia_variavel_2\', 0),
  (3000948, 3000259, \'Analfabeto, inclusive o que, embora tenha recebido instrução, não se alfabetizou\', false, \'analfabeto\', 0),
  (3000949, 3000268, NULL, true, \'lotacao_2\', 0),
  (3000950, 3000269, NULL, true, \'endereco_2\', 0),
  (3000951, 3000271, NULL, true, \'remuneracao_2\', 0),
  (3000952, 3000283, \'Por hora\', false, \'por_hora\', 0),
  (3000953, 3000283, \'Por dia\', false, \'por_dia\', 0),
  (3000954, 3000283, \'Por semana\', false, \'por_semana\', 0),
  (3000955, 3000283, \'Por quinzena\', false, \'por_quinzena\', 0),
  (3000956, 3000283, \'Por mês\', false, \'por_mes\', 0),
  (3000957, 3000283, \'Por tarefa\', false, \'por_tarefa\', 0),
  (3000958, 3000283, \'Não aplicável (no caso de salário exclusivamente variável)\', false, \'nao_aplicavel\', 0),
  (3000959, 3000259, \'Até o 5º ano incompleto do Ensino Fundamental (antiga 4º série) ou que se tenha alfabetizado sem ter frequentado escola regular\', false, \'quinto_ano_oncompleto\', 0),
  (3000960, 3000259, \'5º ano completodo Ensino Fundamental\', false, \'ano_completo_ensino_fundamental\', 0),
  (3000961, 3000259, \'Do 6º ao 9º do Ensino Fundamental Incompleto(antiga 5º a 8º série)\', false, \'ensino_fundamental_incompleto\', 0),
  (3000962, 3000284, NULL, true, \'nome_codigo_cargo_2\', 0),
  (3000963, 3000285, NULL, true, \'nome_codigo_funcao_2\', 0),
  (3000964, 3000259, \'Ensino Fundamental Completo\', false, \'ensino_fundamental_completo\', 0),
  (3000965, 3000259, \'Ensino Médio Incompleto\', false, \'ensino_medio_incompleto\', 0),
  (3000966, 3000286, \'101 - Empregado regido pela CLT, inclusive o estabilizado (com FGTS)\', false, \'empregado_clt\', 0),
  (3000967, 3000259, \'Ensino Médio Completo\', false, \'ensino_medio_completo\', 0),
  (3000968, 3000286, \'301 - Servidor público titular de cargo efetivo\', false, \'servidor_efetivo\', 0),
  (3000969, 3000286, \'302 - Servidor público ocupante de cargo exclusivo em comissão\', false, \'servidor_cargo_exclusivo_comissao\', 0),
  (3000970, 3000286, \'303 - Agente público\', false, \'agente_publico\', 0),
  (3000971, 3000259, \'Educação Superior Incompleta\', false, \'educacao_superior_incompleta\', 0),
  (3000972, 3000286, \'305- Servidor público indicado para conselho ou órgão representativo, na condição de representante do governo, órgão ou entidade da administração pública\', false, \'servidor_publico__conselho_representante_governo\', 0),
  (3000973, 3000259, \'Educação Superior Completa\', false, \'educacao_superior_completa\', 0),
  (3000974, 3000259, \'Pós-graduação Completa\', false, \'pos_graduacao_completa\', 0),
  (3000975, 3000286, \'306 - Servidor contratado por prazo determinado, na forma do art. 37, IX, da CR.\', false, \'servidor_contratado\', 0),
  (3000976, 3000259, \'Mestrado Completo\', false, \'mestrado_completo\', 0),
  (3000977, 3000259, \'Doutorado Completo\', false, \'doutorado_completo\', 0),
  (3000978, 3000286, \'309 - Agente público\', false, \'agente_publico_309\', 0),
  (3000979, 3000286, \'701 - Contribuinte individual ( Autônomo)\', false, \'contribuinte_individual_autonomo\', 0),
  (3000980, 3000287, NULL, true, \'data_nascimento_2\', 0),
  (3000981, 3000289, NULL, true, \'municipio_nascimento_2\', 0),
  (3000982, 3000286, \'711 - Contribuinte individual Transportador Autônomo (com base reduzida)\', false, \'contribuinte_individual_transportador_autonomo\', 0),
  (3000983, 3000290, NULL, true, \'uf_2\', 0),
  (3000984, 3000292, NULL, true, \'pais_2\', 0),
  (3000985, 3000286, \'741 - Contribuinte individual - Microempreendedor Individual\', false, \'contribuinte_individual_microempreendedor\', 0),
  (3000986, 3000294, NULL, true, \'nome_mae_2\', 0),
  (3000987, 3000286, \'771 - Membro do Conselho Tutelar\', false, \'membro_conselho_tutelar\', 0),
  (3000988, 3000286, \'901 - Estagiário\', false, \'estagiario\', 0),
  (3000989, 3000296, NULL, true, \'nome_pai_2\', 0),
  (3000990, 3000286, \'410 - Trabalhador Cedido (quando o Município é o cessionário - que recebe)\', false, \'trabalhador_cedido\', 0),
  (3000991, 3000297, \'Prazo indeterminado\', false, \'prazo_indeterminado\', 0),
  (3000992, 3000299, NULL, true, \'numero_ctps_2\', 0),
  (3000993, 3000297, \'Prazo determinado\', false, \'prazo_determinado\', 0),
  (3000994, 3000300, NULL, true, \'serie_2\', 0),
  (3000995, 3000302, NULL, true, \'data_contratacao_2\', 0),
  (3000996, 3000305, NULL, true, \'ctps_uf_2\', 0),
  (3000997, 3000307, NULL, true, \'data_prevista_termino_contratacao_2\', 0),
  (3000998, 3000309, NULL, true, \'numero_ric_2\', 0),
  (3000999, 3000310, NULL, true, \'orgao_emissor_2\', 0),
  (3001000, 3000313, NULL, true, \'data_expedicao_2\', 0),
  (3001001, 3000312, \'Necessidade transitória de substituição de seu pessoal regular\', false, \'necessidade_transitoria_susbstituicao_pessoal\', 0),
  (3001002, 3000314, NULL, true, \'numero_rg_2\', 0),
  (3001003, 3000312, \'Acréscimo extaordinário de serviços\', false, \'acrescimo_extraordinario_servicos\', 0),
  (3001004, 3000317, NULL, true, \'rg_orgao_emissor_2\', 0),
  (3001005, 3000319, NULL, true, \'identificacao_trabalhador_substituido_2\', 0),
  (3001006, 3000320, NULL, true, \'rg_data_expedicao_2\', 0),
  (3001007, 3000321, \'Normal\', false, \'normal\', 0),
  (3001008, 3000321, \'Decorrente de Decisão Judicial\', false, \'decorrente_decisao_judicial\', 0),
  (3001009, 3000321, \'Tomou posse mas não entrou em exercício\', false, \'posse_nao_exercicio\', 0),
  (3001010, 3000322, \'Nomeação em cargo efetivo\', false, \'nomeacao_cargo_efetivo\', 0),
  (3001011, 3000322, \'Nomeação em cargo em comissão\', false, \'nomeacao_cargo_comissao\', 0),
  (3001012, 3000322, \'Incorporação (militar)\', false, \'incorporacao\', 0),
  (3001013, 3000322, \'Matrícula (militar)\', false, \'matricula\', 0),
  (3001014, 3000322, \'Reinclusão (militar)\', false, \'reinclusao\', 0),
  (3001015, 3000322, \'Outros não relacionados acima. Nesse caso, qual:\', true, \'outros_qual\', 0),
  (3001016, 3000323, NULL, true, \'data_nomeacao_2\', 0),
  (3001017, 3000328, NULL, true, \'data_posse_2\', 0),
  (3001018, 3000329, NULL, true, \'data_exercicio_2\', 0),
  (3001019, 3000318, NULL, true, \'data_admissao_2\', 0),
  (3001020, 3000324, \'Admissão\', false, \'admissao\', 0),
  (3001021, 3000324, \'Transferência de empresa do mesmo grupo econômico\', false, \'transferencia_empresa_mesmo_grupo_economico\', 0),
  (3001022, 3000324, \'Transferência de empresa consorciada ou de consórcio\', false, \'transferencia_empresa_consorciada_consorcio\', 0),
  (3001023, 3000324, \'Transferência por motivo de sucessão, incorporação, cisão ou fusão\', false, \'transferencia_sucessao_incorporacao_cisao_fusao\', 0),
  (3001024, 3000325, \'Normal\', false, \'admissao_normal\', 0),
  (3001025, 3000325, \'Decorrente de Ação Fiscal\', false, \'decorrente_acao_fiscal\', 0),
  (3001026, 3000325, \'Decorrente de Ação Judicial\', false, \'decorrente_acao_judicial\', 0),
  (3001027, 3000326, \'Submetido a horário de trabalho\', false, \'submetido_horario_trabalho\', 0),
  (3001028, 3000326, \'Atividade externa especificada no art. 62, I, da CLT\', false, \'atividade_externa\', 0),
  (3001029, 3000326, \'Funções especificadas no inciso II do art. 62, da CLT\', false, \'funcoes_especificadas\', 0),
  (3001030, 3000327, \'Trabalho Urbano\', false, \'trabalho_urbano\', 0),
  (3001031, 3000327, \'Trabalho Rural (Obs: Empregado rural é que exerce atividade agro-econômico, na agricultura ou na pecurária)\', false, \'trabalho_rural\', 0),
  (3001032, 3000311, NULL, true, \'data_base_revisao_geral_2\', 0),
  (3001033, 3000315, NULL, true, \'numero_rne_2\', 0),
  (3001034, 3000316, NULL, true, \'rne_orgao_emissor_2\', 0),
  (3001035, 3000301, NULL, true, \'rne_data_expedicao_2\', 0),
  (3001036, 3000303, NULL, true, \'numero_orgao_classe_2\', 0),
  (3001037, 3000304, NULL, true, \'oc_orgao_emissor_2\', 0),
  (3001038, 3000306, NULL, true, \'cnpj_sindicato_profissional_2\', 0),
  (3001039, 3000308, NULL, true, \'oc_data_expedicao_2\', 0),
  (3001040, 3000295, NULL, true, \'fgts_2\', 0),
  (3001041, 3000293, NULL, true, \'oc_data_validade_2\', 0),
  (3001042, 3000293, \'Sim ( obrigatoriamente se admitido depois de 04/10/88.\', false, \'optante_sim\', 0),
  (3001043, 3000293, \'Não\', false, \'optante_nao\', 0),
  (3001044, 3000298, NULL, true, \'cnh_numero_registro_cnh_2\', 0),
  (3001045, 3000277, NULL, true, \'matricula_2\', 0),
  (3001046, 3000278, NULL, true, \'cnh_data_expedicao_2\', 0),
  (3001047, 3000279, \'CLT - Consolidação das Leis do Trabalho\', false, \'clt\', 0),
  (3001048, 3000280, NULL, true, \'cnh_uf_2\', 0),
  (3001049, 3000279, \'RJP - Regime Jurídico Próprio\', false, \'rjp\', 0),
  (3001050, 3000281, NULL, true, \'cnh_data_validade_2\', 0),
  (3001051, 3000282, \'RGPS - Regime Geral de Previdência Social (INSS)\', false, \'rgps\', 0),
  (3001052, 3000282, \'RPPS - Regime Próprio de Previdência Social no Brasil (Fundo)\', false, \'rpps\', 0),
  (3001053, 3000282, \'RPPE - Regime Próprio de Previd&#7869;ncia Social no Exterior\', false, \'rppe\', 0),
  (3001054, 3000288, NULL, true, \'cnh_data_primeira_habilitacao_2\', 0),
  (3001055, 3000291, NULL, true, \'cnh_categoria_2\', 0),
  (3001056, 3000272, NULL, true, \'telefone_residencial_2\', 0),
  (3001057, 3000273, NULL, true, \'nome_logradouro_2\', 0),
  (3001058, 3000274, NULL, true, \'telefone_celular_2\', 0),
  (3001059, 3000275, NULL, true, \'telefone_alternativo_2\', 0),
  (3001060, 3000276, NULL, true, \'numero_2\', 0),
  (3001061, 3000261, NULL, true, \'email_2\', 0),
  (3001062, 3000263, NULL, true, \'complemento_2\', 0),
  (3001063, 3000266, NULL, true, \'email_alternativo_2\', 0),
  (3001064, 3000270, NULL, true, \'bairro_2\', 0),
  (3001065, 3000249, NULL, true, \'municipio_2\', 0),
  (3001066, 3000250, \'Sim\', false, \'aposentadoria_sim\', 0),
  (3001067, 3000251, NULL, true, \'cep_2\', 0),
  (3001068, 3000250, \'Não\', false, \'aposentadoria_nao\', 0),
  (3001069, 3000258, NULL, true, \'endereco_uf_2\', 0),
  (3001070, 3000247, NULL, true, \'pais_endereco_2\', 0),
  (3001071, 3000248, \'Sim\', false, \'aposentadoria_especial_sim\', 0),
  (3001072, 3000248, \'Não\', false, \'aposentadoria_especial_nao\', 0),
  (3001073, 3000245, \'RGPS (INSS)\', false, \'rgps_inss\', 0),
  (3001074, 3000245, \'RPPS (Fundo de Previdência)\', false, \'rpps_fundo\', 0),
  (3001075, 3000246, NULL, true, \'data_chegada_brasil_2\', 0),
  (3001076, 3000232, \'Visto Permanente\', false, \'visto_permanente\', 0),
  (3001077, 3000232, \'Visto Temporário\', false, \'visto_temporario\', 0),
  (3001078, 3000232, \'Asilado\', false, \'asilado\', 0),
  (3001080, 3000232, \'Refugiado\', false, \'refugiado\', 0),
  (3001082, 3000232, \'Solicitante de Refúgio\', false, \'solicitante_refugio\', 0),
  (3001083, 3000232, \'Residente em país fronteiriço ao Brasil\', false, \'residente_pais_fronteirico_brasil\', 0),
  (3001084, 3000232, \'Deficiente físico e com mais de 51 anos\', false, \'deficiente_fisico_51_anos\', 0),
  (3001087, 3000232, \'Com residência provisória e anistiado, em situação irregular\', false, \'residencia_provisoria_anistiado\', 0),
  (3001089, 3000232, \'Permanência no Brasil em razão de filhos ou cônjuge brasileiros\', false, \'permanencia_brasil_razao_filhos_conjuge_brasileiro\', 0),
  (3001091, 3000232, \'Beneficiado pelo acordo entre países do Mercosul\', false, \'beneficiado_pelo_acordo_paises_mercosul\', 0),
  (3001093, 3000232, \'Dependente de agente diplomático e/ou consular de países que mantém convênio de reciprocidade para o exercício de atividade remnerada no Brasil\', false, \'depentende_agente_diplomatico_paises_convenio\', 0),
  (3001094, 3000232, \'Beneficiado pelo Tratado de Amizade, Cooperação e Consulta entre a República Federativa do Brasil e a República Portuguesa\', false, \'beneficiado_tratado_amizade\', 0),
  (3001095, 3000238, \'Sim\', false, \'sim\', 0),
  (3001096, 3000238, \'Não\', false, \'nao\', 0),
  (3001098, 3000236, \'Não\', false, \'filhos_nao\', 0),
  (3001102, 3000237, \'Sim\', false, \'portador_sim\', 0),
  (3001104, 3000237, \'Não\', false, \'portador_nao\', 0),
  (3001108, 3000239, \'Física\', false, \'fisica\', 0),
  (3001109, 3000239, \'Visual\', false, \'visual\', 0),
  (3001111, 3000239, \'Auditiva\', false, \'auditiva\', 0),
  (3001112, 3000239, \'Mental\', false, \'mental\', 0),
  (3001114, 3000239, \'Intelectual\', false, \'intelectual\', 0),
  (3001116, 3000244, \'Sim\', false, \'reabilitado_sim\', 0),
  (3001117, 3000244, \'Não\', false, \'reabilitado_nao\', 0),
  (3001118, 3000231, \'Sim\', false, \'readaptado_sim\', 0),
  (3001119, 3000231, \'Não\', false, \'readaptado_nao\', 0),
  (3001081, 3000233, \'Imposto de Renda\', false, \'imposto_renda_001\', 0),
  (3001079, 3000233, \'Salário Família\', false, \'salario_familia_001\', 0),
  (3001107, 3000235, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_001\', 0),
  (3001106, 3000235, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_001\', 0),
  (3001105, 3000235, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_001\', 0),
  (3001103, 3000235, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_001\', 0),
  (3001101, 3000235, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_001\', 0),
  (3001100, 3000235, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_001\', 0),
  (3001099, 3000235, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_001\', 0),
  (3001092, 3000235, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_001\', 0),
  (3001090, 3000235, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_001\', 0),
  (3001088, 3000235, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_001\', 0),
  (3001086, 3000235, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_001\', 0),
  (3001085, 3000235, \'Cônjuge\', false, \'conjuge_001\', 0),
  (3001110, 3000240, NULL, true, \'nome_dependente_2_001\', 0),
  (3001113, 3000241, NULL, true, \'data_nascimento_dependente_2_001\', 0),
  (3001115, 3000243, NULL, true, \'cpf_dependente_2_001\', 0),
  (3001120, 3000330, \'Imposto de Renda\', false, \'imposto_renda_002\', 0),
  (3001121, 3000330, \'Salário Família\', false, \'salario_familia_002\', 0),
  (3001122, 3000331, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_002\', 0),
  (3001123, 3000331, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_002\', 0),
  (3001124, 3000331, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_002\', 0),
  (3001125, 3000331, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_002\', 0),
  (3001126, 3000331, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_002\', 0),
  (3001127, 3000331, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_002\', 0),
  (3001128, 3000331, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_002\', 0),
  (3001129, 3000331, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_002\', 0),
  (3001130, 3000331, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_002\', 0),
  (3001131, 3000331, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_002\', 0),
  (3001132, 3000331, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_002\', 0),
  (3001133, 3000331, \'Cônjuge\', false, \'conjuge_002\', 0),
  (3001134, 3000332, NULL, true, \'nome_dependente_2_002\', 0),
  (3001135, 3000333, NULL, true, \'data_nascimento_dependente_2_002\', 0),
  (3001136, 3000334, NULL, true, \'cpf_dependente_2_002\', 0),
  (3001137, 3000335, \'Imposto de Renda\', false, \'imposto_renda_003\', 0),
  (3001138, 3000335, \'Salário Família\', false, \'salario_familia_003\', 0),
  (3001139, 3000336, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_003\', 0),
  (3001140, 3000336, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_003\', 0),
  (3001141, 3000336, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_003\', 0),
  (3001142, 3000336, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_003\', 0),
  (3001143, 3000336, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_003\', 0),
  (3001144, 3000336, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_003\', 0),
  (3001145, 3000336, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_003\', 0),
  (3001146, 3000336, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_003\', 0),
  (3001147, 3000336, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_003\', 0),
  (3001148, 3000336, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_003\', 0),
  (3001149, 3000336, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_003\', 0),
  (3001150, 3000336, \'Cônjuge\', false, \'conjuge_003\', 0),
  (3001151, 3000337, NULL, true, \'nome_dependente_2_003\', 0),
  (3001152, 3000338, NULL, true, \'data_nascimento_dependente_2_003\', 0),
  (3001153, 3000339, NULL, true, \'cpf_dependente_2_003\', 0),
  (3001154, 3000340, \'Imposto de Renda\', false, \'imposto_renda_004\', 0),
  (3001155, 3000340, \'Salário Família\', false, \'salario_familia_004\', 0),
  (3001156, 3000341, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_004\', 0),
  (3001157, 3000341, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_004\', 0),
  (3001158, 3000341, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_004\', 0),
  (3001159, 3000341, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_004\', 0),
  (3001160, 3000341, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_004\', 0),
  (3001161, 3000341, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_004\', 0),
  (3001162, 3000341, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_004\', 0),
  (3001163, 3000341, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_004\', 0),
  (3001164, 3000341, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_004\', 0),
  (3001165, 3000341, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_004\', 0),
  (3001166, 3000341, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_004\', 0),
  (3001167, 3000341, \'Cônjuge\', false, \'conjuge_004\', 0),
  (3001168, 3000342, NULL, true, \'nome_dependente_2_004\', 0),
  (3001169, 3000343, NULL, true, \'data_nascimento_dependente_2_004\', 0),
  (3001170, 3000344, NULL, true, \'cpf_dependente_2_004\', 0),
  (3001171, 3000345, \'Imposto de Renda\', false, \'imposto_renda_005\', 0),
  (3001172, 3000345, \'Salário Família\', false, \'salario_familia_005\', 0),
  (3001173, 3000346, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_005\', 0),
  (3001174, 3000346, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_005\', 0),
  (3001175, 3000346, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_005\', 0),
  (3001176, 3000346, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_005\', 0),
  (3001177, 3000346, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_005\', 0),
  (3001178, 3000346, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_005\', 0),
  (3001179, 3000346, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_005\', 0),
  (3001180, 3000346, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_005\', 0),
  (3001181, 3000346, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_005\', 0),
  (3001182, 3000346, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_005\', 0),
  (3001183, 3000346, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_005\', 0),
  (3001184, 3000346, \'Cônjuge\', false, \'conjuge_005\', 0),
  (3001185, 3000347, NULL, true, \'nome_dependente_2_005\', 0),
  (3001186, 3000348, NULL, true, \'data_nascimento_dependente_2_005\', 0),
  (3001187, 3000349, NULL, true, \'cpf_dependente_2_005\', 0),
  (3001188, 3000350, \'Imposto de Renda\', false, \'imposto_renda_006\', 0),
  (3001189, 3000350, \'Salário Família\', false, \'salario_familia_006\', 0),
  (3001190, 3000351, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_006\', 0),
  (3001191, 3000351, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_006\', 0),
  (3001192, 3000351, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_006\', 0),
  (3001193, 3000351, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_006\', 0),
  (3001194, 3000351, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_006\', 0),
  (3001195, 3000351, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_006\', 0),
  (3001196, 3000351, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_006\', 0),
  (3001197, 3000351, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_006\', 0),
  (3001198, 3000351, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_006\', 0),
  (3001199, 3000351, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_006\', 0),
  (3001200, 3000351, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_006\', 0),
  (3001201, 3000351, \'Cônjuge\', false, \'conjuge_006\', 0),
  (3001202, 3000352, NULL, true, \'nome_dependente_2_006\', 0),
  (3001203, 3000353, NULL, true, \'data_nascimento_dependente_2_006\', 0),
  (3001204, 3000354, NULL, true, \'cpf_dependente_2_006\', 0),
  (3001205, 3000355, \'Imposto de Renda\', false, \'imposto_renda_007\', 0),
  (3001206, 3000355, \'Salário Família\', false, \'salario_familia_007\', 0),
  (3001207, 3000356, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_007\', 0),
  (3001208, 3000356, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_007\', 0),
  (3001209, 3000356, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_007\', 0),
  (3001210, 3000356, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_007\', 0),
  (3001211, 3000356, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_007\', 0),
  (3001212, 3000356, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_007\', 0),
  (3001213, 3000356, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_007\', 0),
  (3001214, 3000356, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_007\', 0),
  (3001215, 3000356, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_007\', 0),
  (3001216, 3000356, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_007\', 0),
  (3001217, 3000356, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_007\', 0),
  (3001218, 3000356, \'Cônjuge\', false, \'conjuge_007\', 0),
  (3001219, 3000357, NULL, true, \'nome_dependente_2_007\', 0),
  (3001220, 3000358, NULL, true, \'data_nascimento_dependente_2_007\', 0),
  (3001221, 3000359, NULL, true, \'cpf_dependente_2_007\', 0),
  (3001222, 3000360, \'Imposto de Renda\', false, \'imposto_renda_008\', 0),
  (3001223, 3000360, \'Salário Família\', false, \'salario_familia_008\', 0),
  (3001224, 3000361, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_008\', 0),
  (3001225, 3000361, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_008\', 0),
  (3001226, 3000361, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_008\', 0),
  (3001227, 3000361, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_008\', 0),
  (3001228, 3000361, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_008\', 0),
  (3001229, 3000361, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_008\', 0),
  (3001230, 3000361, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_008\', 0),
  (3001231, 3000361, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_008\', 0),
  (3001232, 3000361, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_008\', 0),
  (3001233, 3000361, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_008\', 0),
  (3001234, 3000361, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_008\', 0),
  (3001235, 3000361, \'Cônjuge\', false, \'conjuge_008\', 0),
  (3001236, 3000362, NULL, true, \'nome_dependente_2_008\', 0),
  (3001237, 3000363, NULL, true, \'data_nascimento_dependente_2_008\', 0),
  (3001238, 3000364, NULL, true, \'cpf_dependente_2_008\', 0),
  (3001239, 3000365, \'Imposto de Renda\', false, \'imposto_renda_009\', 0),
  (3001240, 3000365, \'Salário Família\', false, \'salario_familia_009\', 0),
  (3001241, 3000366, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_009\', 0),
  (3001242, 3000366, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_009\', 0),
  (3001243, 3000366, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_009\', 0),
  (3001244, 3000366, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_009\', 0),
  (3001245, 3000366, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_009\', 0),
  (3001246, 3000366, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_009\', 0),
  (3001247, 3000366, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_009\', 0),
  (3001248, 3000366, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_009\', 0),
  (3001249, 3000366, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_009\', 0),
  (3001250, 3000366, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_009\', 0),
  (3001251, 3000366, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_009\', 0),
  (3001252, 3000366, \'Cônjuge\', false, \'conjuge_009\', 0),
  (3001253, 3000367, NULL, true, \'nome_dependente_2_009\', 0),
  (3001254, 3000368, NULL, true, \'data_nascimento_dependente_2_009\', 0),
  (3001255, 3000369, NULL, true, \'cpf_dependente_2_009\', 0),
  (3001256, 3000370, \'Imposto de Renda\', false, \'imposto_renda_010\', 0),
  (3001257, 3000370, \'Salário Família\', false, \'salario_familia_010\', 0),
  (3001258, 3000371, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, com idade até 24 anos, se ainda estiver cursando estabelecimento de nível superior ou escola técnica de 2º grau, desde que tenha detido sua guarda judicial até os 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_010\', 0),
  (3001259, 3000371, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, até 21 (vinte e um) anos\', false, \'irmao_neto_bisneto_21_010\', 0),
  (3001260, 3000371, \'Ex-cônjuge que receba pensão de alimentos\', false, \'ex_conjuge_010\', 0),
  (3001261, 3000371, \'A pessoa absolutamente incapaz, da qual seja tutor ou curador\', false, \'pessoa_incapaz_010\', 0),
  (3001262, 3000371, \'Menor pobre, até 21 (vinte e um) anos, que crie e eduque e do qual detenha a guarda judicial\', false, \'menor_pobre_010\', 0),
  (3001263, 3000371, \'Pais, avós e bisavós\', false, \'pais_avos_bisavos_010\', 0),
  (3001264, 3000371, \'Irmão(ã), neto(a) ou bisneto(a) sem arrimo dos pais, do(a) qual detenha a guarda judicial, em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'irmao_neto_bisneto_incapacitado_010\', 0),
  (3001265, 3000371, \'Filho(a) ou enteado(a) em qualquer idade, quando incapacitado física e/ou mentalmente para o trabalho\', false, \'filho_incapacitado_010\', 0),
  (3001266, 3000371, \'Filho(a) ou enteado(a) universitário(a) ou cursando escola técnica de 2º grau, até 24 (vinte e quatro) anos\', false, \'filho_24_010\', 0),
  (3001267, 3000371, \'Filho(a) ou enteado(a) até 21 (vinte e um) anos\', false, \'filho_21_010\', 0),
  (3001268, 3000371, \'Companheiro(a) com o(a) qual tenha filho ou viva há mais de 5 (cinco) anos\', false, \'companheiro_010\', 0),
  (3001269, 3000371, \'Cônjuge\', false, \'conjuge_010\', 0),
  (3001270, 3000372, NULL, true, \'nome_dependente_2_010\', 0),
  (3001271, 3000373, NULL, true, \'data_nascimento_dependente_2_010\', 0),
  (3001272, 3000374, NULL, true, \'cpf_dependente_2_010\', 0);');

UPDATE db_itensmenu set libcliente = true where  id_item = 10218;
UPDATE db_itensmenu set libcliente = false where  id_item = 10219;

-- Altera rótulo do campo de vínculo entre pergunta e grupo
update db_syscampo set descricao = 'Avaliação Grupo Pergunta', rotulo = 'Avaliação Grupo Pergunta', rotulorel = 'Avaliação Grupo Pergunta' where codcam = 16916;

-- Ajusta o nome do módulo eSocial e seus menus corretamente
update db_itensmenu set descricao='eSocial', help='eSocial', desctec='Módulo de integração com o eSocial.' where id_item = 10216;
update db_itensmenu set help='Inclui um formulário do eSocial', desctec='Menu para manutenção dos formulários do eSocial.' where id_item = 10222;
update db_itensmenu set help='Altera um formulário do eSocial', desctec='Menu para manutenção dos formulários do eSocial.' where id_item = 10223;
update db_itensmenu set help='Exclui um formulário do eSocial', desctec='Menu para manutenção dos formulários do eSocial.' where id_item = 10224;
update db_modulos set nome_modulo='eSocial', descr_modulo='eSocial' where id_item = 10216;
update db_sysmodulo set descricao='Módulo para o eSocial' where codmod = 81;
update db_sysarquivo set descricao='Tabela que vincula uma resposta de uma pergunta do eSocial para o servidor.' where codarq = 3924;



---------------------------------------------------------------------------------------------------------------
---------------------------------------- INICIO EDUCACAO ------------------------------------------------------
---------------------------------------------------------------------------------------------------------------

insert into db_syscampo values(21844,'ed10_censocursoprofiss','int4','Representa o curso profissionalizante de acordo com a tabela do censo.','null', 'Curso Profissionalizante',10,'t','f','f',1,'text','Curso Profissionalizante');
insert into db_sysarqcamp values(1010045,21844,8,0);
insert into db_sysforkey values(1010045,21844,1,2349,0);
insert into db_sysindices values(4343,'ensino_censocursoprofiss_in',1010045,'0');
insert into db_syscadind values(4343,21844,1);
update db_syscampo set nomecam = 'ed18_latitude', conteudo = 'varchar(20)', descricao = 'Latitude', valorinicial = '', rotulo = 'Latitude', nulo = 't', tamanho = 20, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Latitude' where codcam = 18917;
update db_syscampo set nomecam = 'ed18_longitude', conteudo = 'varchar(20)', descricao = 'Longitude', valorinicial = '', rotulo = 'Longitude', nulo = 't', tamanho = 20, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Longitude' where codcam = 18918;


select setval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq', (select max(ed313_sequencial) from avaliacaoperguntaopcaolayoutcampo));

-- Vinculos dos campos da avaliacao da Formacao do RecHumano com o registro 50 do censo 2015
insert into avaliacaoperguntaopcaolayoutcampo
        (ed313_sequencial, ed313_ano, ed313_db_layoutcampo, ed313_avaliacaoperguntaopcao, ed313_layoutvalorcampo )
 values ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12127, 3000073, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12129, 3000074, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12131, 3000075, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12133, 3000076, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12134, 3000077, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12135, 3000078, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12136, 3000079, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12141, 3000080, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12143, 3000081, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12145, 3000082, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12147, 3000127, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12148, 3000084, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12150, 3000114, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12151, 3000115, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12152, 3000116, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12153, 3000117, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12155, 3000118, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12156, 3000119, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12158, 3000120, 1),
        ( nextval('avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq'), 2015, 12159, 3000121, 1);

-- Ajusta o valor padrão dos campos de múltipla escolha para o registro 10 do CENSO 2015
update avaliacaoperguntaopcaolayoutcampo
  set ed313_layoutvalorcampo = '1'
  where ed313_db_layoutcampo in (
    12269, 12275, 12281, 12284, 12288, 12289, 12291, 12294, 12297, 12299, 12301, 12303, 12304, 12306, 12310, 12313, 12347, 12277, 12320, 12322, 12325, 12328, 12333, 12336, 12339, 12340, 12341, 12342, 12344, 12361, 12170, 12172, 12174, 12176, 12178, 12181, 12183, 12187, 12247, 12248, 12250, 12377, 12378, 12379, 12350, 12351, 12352, 12353, 12354, 12355, 12356, 12357, 12358, 12359, 12360, 12255, 12257, 12260, 12262, 12264, 12266, 12214, 12218, 12223, 12228, 12231, 12234, 12237, 12241, 12245, 12316, 12185, 12362
  ) and ed313_ano = 2015;

-- Ajusta o valor padrão do campo Água consumida pelos Alunos (NÃO FILTRADA) para o registro 10 do CENSO 2015
update avaliacaoperguntaopcaolayoutcampo set ed313_layoutvalorcampo = '1' where ed313_avaliacaoperguntaopcao = 3000099 and ed313_ano = 2015;

 -- Ajusta o valor padrão do campo Água consumida pelos Alunos (FILTRADA) para o registro 10 do CENSO 2015
update avaliacaoperguntaopcaolayoutcampo set ed313_layoutvalorcampo = '2' where ed313_avaliacaoperguntaopcao = 3000100 and ed313_ano = 2015;

---------------------------------------------------------------------------------------------------------------
------------------------------------------- INICIO SAUDE ------------------------------------------------------
---------------------------------------------------------------------------------------------------------------
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10230 ,'Passagens' ,'Passagens' ,'tfd1_passagensdestino001.php' ,'1' ,'1' ,'Cadastro de passagens do TFD por destino.' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 8323 ,10230 ,12 ,8322 );
insert into db_sysarquivo values (3934, 'passagemdestino', 'Armazena os vínculos de valor de uma passagem para um destino do TFD.', 'tf37', '2016-04-29', 'Passagem / Destino', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (70,3934);
insert into db_syscampo values(21849,'tf37_sequencial','int4','Código sequencial da tabela.','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21850,'tf37_valor','float8','Valor da passagem para o destino.','0', 'Valor da Passagem',10,'f','f','f',4,'text','Valor da Passagem');
insert into db_syscampo values(21851,'tf37_destino','int4','Destino referente ao valor da passagem.','0', 'Destino',10,'f','f','f',1,'text','Destino');
insert into db_sysarqcamp values(3934,21849,1,0);
insert into db_sysarqcamp values(3934,21850,2,0);
insert into db_sysarqcamp values(3934,21851,3,0);
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3934,21849,1,21851);
insert into db_sysforkey values(3934,21851,1,2859,0);
insert into db_sysindices values(4344,'passagemdestino_destino_in',3934,'1');
insert into db_syscadind values(4344,21851,1);
insert into db_syssequencia values(1000568, 'passagemdestino_tf37_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000568 where codarq = 3934 and codcam = 21849;

insert into db_syscampo values(21852,'tf17_tiposaida','int4','Tipo de saída agendada.','0', 'Tipo de Saída',10,'f','f','f',1,'text','Tipo de Saída');
insert into db_syscampodef values(21852,'1','Veículo');
insert into db_syscampodef values(21852,'2','Transporte Coletivo');
insert into db_sysarqcamp values(2873,21852,9,0);

insert into db_sysarquivo values (3935, 'agendasaidapassagemdestino', 'Guarda os valores da passagem de uma agenda de saída para um destino', 'tf38', '2016-05-02', 'Passagem de destino da agenda de saída', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (70,3935);
insert into db_syscampo values(21855,'tf38_sequencial','int4','Código sequencial da tabela.','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21856,'tf38_agendasaida','int4','Agenda de saída com os valores da passagem.','0', 'Agenda de Saída',10,'f','f','f',1,'text','Agenda de Saída');
insert into db_syscampo values(21857,'tf38_valorunitario','float8','Valor unitário da passagem para a agenda e destino.','0', 'Valor Unitário',10,'f','f','f',4,'text','Valor Unitário');
insert into db_syscampo values(21866,'tf38_cgs','int4','CGS do passageiro.','0', 'CGS',10,'f','f','f',1,'text','CGS');
insert into db_syscampo values(21867,'tf38_fica','bool','Controla se o passageiro fica ou não no destino.','f', 'Fica',1,'f','f','f',5,'text','Fica');
insert into db_sysarqcamp values(3935,21855,1,0);
insert into db_sysarqcamp values(3935,21856,2,0);
insert into db_sysarqcamp values(3935,21857,3,0);
insert into db_sysarqcamp values(3935,21866,4,0);
insert into db_sysarqcamp values(3935,21867,5,0);
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3935,21855,1,21856);
insert into db_sysforkey values(3935,21856,1,2873,0);
insert into db_sysforkey values(3935,21866,1,1010144,0);
insert into db_sysindices values(4350,'agendasaidapassagemdestino_agendasaida_cgs_in',3935,'1');
insert into db_syscadind values(4350,21856,1);
insert into db_syscadind values(4350,21866,2);
insert into db_syssequencia values(1000569, 'agendasaidapassagemdestino_tf38_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000569 where codarq = 3935 and codcam = 21855;

insert into db_tipodoc values (5025, 'DECLARAÇÃO DE COMPARECIMENTO');
insert into db_documentopadrao( db60_coddoc ,db60_descr ,db60_tipodoc ,db60_instit ) values ( 252 ,'DECLARAÇÃO DE COMPARECIMENTO' ,5025 ,1 );
insert into db_paragrafopadrao( db61_codparag ,db61_descr ,db61_texto ,db61_alinha ,db61_inicia ,db61_espaco ,db61_alinhamento ,db61_altura ,db61_largura ,db61_tipo ) values ( 549 ,'TÍTULO DA DECLARAÇÃO DE COMPARECIMENTO' ,'DECLARAÇÃO DE COMPARECIMENTO' ,0 ,0 ,1 ,'J' ,0 ,0 ,1 );
insert into db_docparagpadrao( db62_coddoc ,db62_codparag ,db62_ordem ) values ( 252 ,549 ,1 );
insert into db_paragrafopadrao( db61_codparag ,db61_descr ,db61_texto ,db61_alinha ,db61_inicia ,db61_espaco ,db61_alinhamento ,db61_altura ,db61_largura ,db61_tipo ) values ( 550 ,'CORPO DA DECLARAÇÃO DE COMPARECIMENTO' ,'Declaro que Sr.(a) #$nomePaciente# permaneceu na #$nomeUnidade# no dia #$dataAtendimento# #$horario# #$observacao# ' ,0 ,0 ,1 ,'J' ,0 ,0 ,1 );
insert into db_docparagpadrao( db62_coddoc ,db62_codparag ,db62_ordem ) values ( 252 ,550 ,2 );

-- cadastro do layout e seus registros
select fc_executa_ddl('
  insert into db_layouttxt( db50_codigo ,db50_layouttxtgrupo ,db50_descr ,db50_quantlinhas ,db50_obs ) values ( 255 ,2 ,\'CENSO 2016\' ,0 ,\'CENSO 2016\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 840 ,255 ,\'REGISTRO 00\' ,3 ,0 ,0 ,0 ,\'Dados de identificação da escola\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 841 ,255 ,\'REGISTRO 10\' ,3 ,0 ,0 ,0 ,\'Caracterização e infra estrutura\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 842 ,255 ,\'REGISTRO 20\' ,3 ,0 ,0 ,0 ,\'Cadastro de turma\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 843 ,255 ,\'REGISTRO 30\' ,3 ,0 ,0 ,0 ,\'Cadastro de Docente - identificação\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 844 ,255 ,\'REGISTRO 40\' ,3 ,0 ,0 ,0 ,\'Cadastro de Docente - Documentos e Endereço\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 845 ,255 ,\'REGISTRO 50\' ,3 ,0 ,0 ,0 ,\'Cadastro de Docente - Dados Variáveis\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 846 ,255 ,\'REGISTRO 51\' ,3 ,0 ,0 ,0 ,\'Cadastro de docente - dados de docência\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 847 ,255 ,\'REGISTRO 60\' ,3 ,0 ,0 ,0 ,\'Cadastro de Aluno - Identificação\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 848 ,255 ,\'REGISTRO 70\' ,3 ,0 ,0 ,0 ,\'Cadastro de Aluno - Documentos e Endereço\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 849 ,255 ,\'REGISTRO 80\' ,3 ,0 ,0 ,0 ,\'Cadastro de Aluno - Vínculo(matrícula)\' ,\'|\' ,\'0\' );
  insert into db_layoutlinha( db51_codigo ,db51_layouttxt ,db51_descr ,db51_tipolinha ,db51_tamlinha ,db51_linhasantes ,db51_linhasdepois ,db51_obs ,db51_separador ,db51_compacta ) values ( 850 ,255 ,\'REGISTRO 99\' ,3 ,0 ,0 ,0 ,\'fim de arquivo\' ,\'|\' ,\'0\' );
');

-- cadastro registro 00
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13281 ,840 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'00\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13295 ,840 ,\'codigo_escola_inep\' ,\'CÓDIGO DE ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13300 ,840 ,\'numero_cpf_gestor_escolar\' ,\'NÚMERO DO CPF DO GESTOR ESCOLAR\' ,14 ,11 ,\'\' ,11 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13305 ,840 ,\'nome_gestor_escolar\' ,\'NOME DO GESTOR ESCOLAR\' ,14 ,22 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13310 ,840 ,\'cargo_gestor_escolar\' ,\'CARGO DO GESTOR ESCOLAR\' ,14 ,122 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13314 ,840 ,\'endereco_eletronico_gestor_escolar\' ,\'ENDEREÇO ELETRÔNICO DO GESTOR ESCOLAR\' ,14 ,123 ,\'\' ,50 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13316 ,840 ,\'situacao_funcionamento\' ,\'SITUAÇÃO DE FUNCIONAMENTO\' ,14 ,173 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13321 ,840 ,\'data_inicio_ano_letivo\' ,\'DATA DE INÍCIO DO ANO LETIVO\' ,14 ,174 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13325 ,840 ,\'data_termino_ano_letivo\' ,\'DATA DE TÉRMINO DO ANO LETIVO\' ,14 ,184 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13333 ,840 ,\'nome_escola\' ,\'NOME DA ESCOLA\' ,14 ,194 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13338 ,840 ,\'latitude\' ,\'LATITUDE\' ,14 ,294 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13340 ,840 ,\'longitude\' ,\'LONGITUDE\' ,14 ,314 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13344 ,840 ,\'cep\' ,\'CEP\' ,14 ,334 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13348 ,840 ,\'endereco\' ,\'ENDEREÇO\' ,14 ,342 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13351 ,840 ,\'endereco_numero\' ,\'ENDEREÇO NÚMERO\' ,14 ,442 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13354 ,840 ,\'complemento_endereco\' ,\'COMPLEMENTO\' ,14 ,452 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13355 ,840 ,\'bairro\' ,\'BAIRRO\' ,14 ,472 ,\'\' ,50 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13359 ,840 ,\'uf\' ,\'UF\' ,14 ,522 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13361 ,840 ,\'municipio\' ,\'MUNICÍPIO\' ,14 ,524 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13365 ,840 ,\'distrito\' ,\'DISTRITO\' ,14 ,531 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13368 ,840 ,\'ddd\' ,\'DDD\' ,14 ,533 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13371 ,840 ,\'telefone\' ,\'TELEFONE\' ,14 ,535 ,\'\' ,9 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13376 ,840 ,\'telefone_publico_1\' ,\'TELEFONE PÚBLICO\' ,14 ,544 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13379 ,840 ,\'telefone_publico_2\' ,\'OUTRO TELEFONE DE CONTATO\' ,14 ,552 ,\'\' ,9 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13384 ,840 ,\'fax\' ,\'FAX\' ,14 ,561 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13385 ,840 ,\'endereco_eletronico\' ,\'ENDEREÇO ELETRÔNICO\' ,14 ,569 ,\'\' ,50 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13387 ,840 ,\'codigo_orgao_regional_ensino\' ,\'CÓDIGO DO ÓRGÃO REGIONAL DE ENSINO\' ,14 ,619 ,\'\' ,5 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13399 ,840 ,\'dependencia_administrativa\' ,\'DEPENDÊNCIA ADMINISTRATIVA\' ,14 ,624 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13401 ,840 ,\'localizacao_zona_escola\' ,\'LOCALIZAÇÃO/ZONA DA ESCOLA\' ,14 ,625 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13404 ,840 ,\'categoria_escola_privada\' ,\'CATEGORIA DA ESCOLA PRIVADA\' ,14 ,626 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13409 ,840 ,\'conveniada_poder_publico\' ,\'CONVENIADA COM O PODER PÚBLICO\' ,14 ,627 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13412 ,840 ,\'mant_esc_privada_empresa_grupo_empresarial_pes_fis\' ,\'MANTENEDORA - EMPRESA, GRUPOS EMPRESARIA\' ,14 ,628 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13415 ,840 ,\'mant_esc_privada_sidicatos_associacoes_cooperativa\' ,\'MANTENEDORA - SINDICATOS DE TRABALHADORE\' ,14 ,629 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13417 ,840 ,\'mant_esc_privada_ong_internacional_nacional_oscip\' ,\'MANTENEDORA - ORGANIZAÇÃO NÃO GOVERNAMEN\' ,14 ,630 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13418 ,840 ,\'mant_esc_privada_instituicoes_sem_fins_lucrativos\' ,\'MANTENEDORA - INSTITUIÇÕES SEM FINS LUCR\' ,14 ,631 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13419 ,840 ,\'sistema_s_sesi_senai_sesc_outros\' ,\'SISTEMA S\' ,14 ,632 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13420 ,840 ,\'cnpj_mantenedora_principal_escola_privada\' ,\'CNPJ DA MANTENEDORA PRINCIPAL DA ESCOLA\' ,14 ,633 ,\'\' ,14 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13423 ,840 ,\'cnpj_escola_privada\' ,\'CNPJ DA ESCOLA PRIVADA\' ,14 ,647 ,\'\' ,14 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13431 ,840 ,\'regulamentacao_autorizacao_conselho_orgao\' ,\'REGULAMENTAÇÃO/AUTORIZAÇÃO NO CONSELHO\' ,14 ,661 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13432 ,840 ,\'unidade_vinculada_escola_educacao_basica\' ,\'UNIDADE VINCULADA A ESCOLA DE EDUCAÇÃO\' ,14 ,662 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13433 ,840 ,\'codigo_escola_sede\' ,\'CÓDIGO DA ESCOLA SEDE\' ,14 ,663 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13436 ,840 ,\'codigo_ies\' ,\'CÓDIGO DA IES\' ,14 ,671 ,\'\' ,14 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 10
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13258 ,841 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'10\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13259 ,841 ,\'codigo_escola_inep\' ,\'CÓDIGO DA ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13262 ,841 ,\'local_funcionamento_escola_predio_escolar\' ,\'LOCAL DE FUNCIONAMENTO - PRÉDIO ESCOLA\' ,14 ,11 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13263 ,841 ,\'local_funcionamento_escola_templo_igreja\' ,\'LOCAL DE FUNCIONAMENTO - TEMPLO/IGREJA\' ,14 ,12 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13265 ,841 ,\'local_funcionamento_escola_salas_empresas\' ,\'LOCAL DE FUNCIONAMENTO - SALA DE EMPRESA\' ,14 ,13 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13268 ,841 ,\'local_funcionamento_escola_casa_professor\' ,\'LOCAL FUNCIONAMENTO - CASA DO PROFESSOR\' ,14 ,14 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13270 ,841 ,\'local_funcionamento_escola_salas_outras_escolas\' ,\'LOCAL DE FUNCIONAMENTO - SALA OUTRA ESCO\' ,14 ,15 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13275 ,841 ,\'local_funcionamento_escola_galpao_rancho_paiol_bar\' ,\'LOCAL FUNCIONAMENTO - GALPÃO/RANCHO\' ,14 ,16 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13279 ,841 ,\'local_funcionamento_escola_un_internacao_socio\' ,\'LOCAL FUNCIONAMENTO - UNIDADE SOCIOEDUCA\' ,14 ,17 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13280 ,841 ,\'local_funcionamento_escola_unidade_prisional\' ,\'LOCAL FUNCIONAMENTO - UNIDADE PRISIONAL\' ,14 ,18 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13282 ,841 ,\'local_funcionamento_escola_outros\' ,\'LOCAL FUNCIONAMENTO - OUTROS\' ,14 ,19 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13284 ,841 ,\'forma_ocupacao_predio\' ,\'FORMA DE OCUPAÇÃO DO PRÉDIO\' ,14 ,20 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13285 ,841 ,\'predio_compartilhado_outra_escola\' ,\'PRÉDIO COMPARTILHADO COM OUTRA ESCOLA\' ,14 ,21 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13287 ,841 ,\'codigo_escola_compartilha_1\' ,\'CÓDIGO DA ESCOLA A QUAL COMPARTILHA 1\' ,14 ,22 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13288 ,841 ,\'codigo_escola_compartilha_2\' ,\'CÓDIGO DA ESCOLA A QUAL COMPARTILHA 2\' ,14 ,30 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13296 ,841 ,\'codigo_escola_compartilha_3\' ,\'CÓDIGO DA ESCOLA A QUAL COMPARTILHA 3\' ,14 ,38 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13298 ,841 ,\'codigo_escola_compartilha_4\' ,\'CÓDIGO DA ESCOLA A QUAL COMPARTILHA 4\' ,14 ,46 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13299 ,841 ,\'codigo_escola_compartilha_5\' ,\'CÓDIGO DA ESCOLA A QUAL COMPARTILHA 5\' ,14 ,54 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13303 ,841 ,\'codigo_escola_compartilha_6\' ,\'CÓDIGO DA ESCOLA A QUAL COMPARTILHA 6\' ,14 ,62 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13306 ,841 ,\'agua_consumida_alunos\' ,\'ÁGUA CONSUMIDA PELOS ALUNOS\' ,14 ,70 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13309 ,841 ,\'abastecimento_agua_rede_publica\' ,\'ABASTECIMENTO ÁGUA - REDE PÚBLICA\' ,14 ,71 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13311 ,841 ,\'abastecimento_agua_poco_artesiano\' ,\'ABASTECIMENTO ÁGUA - POÇO ARTESIANO\' ,14 ,72 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13312 ,841 ,\'abastecimento_agua_cacimba_cisterna_poco\' ,\'ABASTECIMENTO ÁGUA - CACIMBA/CISTERNA\' ,14 ,73 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13317 ,841 ,\'abastecimento_agua_fonte_rio_igarape_riacho_correg\' ,\'ABASTECIMENTO ÁGUA - FONTE/RIO/IGARAPÉ\' ,14 ,74 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13320 ,841 ,\'abastecimento_agua_inexistente\' ,\'ABASTECIMENTO ÁGUA - INEXISTENTE\' ,14 ,75 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13322 ,841 ,\'abastecimento_energia_eletrica_rede_publica\' ,\'ABASTECIMENTO ENERGIA - REDE PÚBLICA\' ,14 ,76 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13324 ,841 ,\'abastecimento_energia_eletrica_gerador\' ,\'ABASTECIMENTO ENERGIA - GERADOR\' ,14 ,77 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13328 ,841 ,\'abastecimento_energia_eletrica_outros_alternativa\' ,\'ABASTECIMENTO ENERGIA - OUTROS\' ,14 ,78 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13329 ,841 ,\'abastecimento_energia_eletrica_inexistente\' ,\'ABASTECIMENTO ENERGIA - INEXISTENTE\' ,14 ,79 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13331 ,841 ,\'esgoto_sanitario_rede_publica\' ,\'ESGOTO SANITÁRIO - REDE PÚBLICA\' ,14 ,80 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13332 ,841 ,\'esgoto_sanitario_fossa\' ,\'ESGOTO SANITÁRIO - FOSSA\' ,14 ,81 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13335 ,841 ,\'esgoto_sanitario_inexistente\' ,\'ESGOTO SANITÁRIO - INEXISTENTE\' ,14 ,82 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13337 ,841 ,\'destinacao_lixo_coleta_periodica\' ,\'DESTINAÇÃO LIXO - COLETA PERIÓDICA\' ,14 ,83 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13343 ,841 ,\'destinacao_lixo_queima\' ,\'DESTINAÇÃO LIXO - QUEIMA\' ,14 ,84 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13346 ,841 ,\'destinacao_lixo_joga_outra_area\' ,\'DESTINAÇÃO LIXO - JOGA EM OUTRA ÁREA\' ,14 ,85 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13347 ,841 ,\'destinacao_lixo_recicla\' ,\'DESTINAÇÃO LIXO - RECICLA\' ,14 ,86 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13349 ,841 ,\'destinacao_lixo_enterra\' ,\'DESTINAÇÃO LIXO - ENTERRA\' ,14 ,87 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13353 ,841 ,\'destinacao_lixo_outros\' ,\'DESTINAÇÃO LIXO - OUTROS\' ,14 ,88 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13356 ,841 ,\'dependencias_existentes_escola_sala_diretoria\' ,\'DEPENDÊNCIAS - SALA DA DIRETORA\' ,14 ,89 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13357 ,841 ,\'dependencias_existentes_escola_sala_professores\' ,\'DEPENDÊNCIAS - SALA DE PROFESSORES\' ,14 ,90 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13360 ,841 ,\'dependencias_existentes_escola_sala_secretaria\' ,\'DEPENDÊNCIAS - SALA DE SECRETARIA\' ,14 ,91 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13363 ,841 ,\'dependencias_existentes_escola_laboratorio_informa\' ,\'DEPENDÊNCIAS - LABORATÓRIO INFORMÁTICA\' ,14 ,92 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13367 ,841 ,\'dependencias_existentes_escola_laboratorio_ciencia\' ,\'DEPENDÊNCIAS - LABORATÓRIO DE CIÊNCIAS\' ,14 ,93 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13370 ,841 ,\'dependencias_existentes_escola_sala_recursos_multi\' ,\'DEPENDÊNCIAS - SALA RECURSOS MULTIFUNCIO\' ,14 ,94 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13373 ,841 ,\'dependencias_existentes_escola_quadra_esporte_cobe\' ,\'DEPENDÊNCIAS - QUADRA ESPORTE COBERTA\' ,14 ,95 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13375 ,841 ,\'dependencias_existentes_escola_quadra_esporte_desc\' ,\'DEPENDÊNCIAS - QUADRA ESPORTE DESCOBERTA\' ,14 ,96 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13377 ,841 ,\'dependencias_existentes_escola_cozinha\' ,\'DEPENDÊNCIAS - COZINHA\' ,14 ,97 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13381 ,841 ,\'dependencias_existentes_escola_biblioteca\' ,\'DEPENDÊNCIAS - BIBLIOTECA\' ,14 ,98 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13383 ,841 ,\'dependencias_existentes_escola_sala_leitura\' ,\'DEPENDÊNCIAS - SALA DE LEITURA\' ,14 ,99 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13388 ,841 ,\'dependencias_existentes_escola_parque_infantil\' ,\'DEPENDÊNCIAS - PARQUE INFANTIL\' ,14 ,100 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13389 ,841 ,\'dependencias_existentes_escola_bercario\' ,\'DEPENDÊNCIAS - BERÇÁRIO\' ,14 ,101 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13391 ,841 ,\'dependencias_existentes_escola_banheiro_fora_predi\' ,\'DEPENDÊNCIAS - BANHEIRO FORA DO PRÉDIO\' ,14 ,102 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13392 ,841 ,\'dependencias_existentes_escola_banheiro_dentro_pre\' ,\'DEPENDÊNCIAS - BANHEIRO DENTRO DO PRÉDIO\' ,14 ,103 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13394 ,841 ,\'dependencias_existentes_escola_banheiro_educ_infan\' ,\'DEPENDÊNCIAS - BANHEIRO EDUCAÇÃO INFANT\' ,14 ,104 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13395 ,841 ,\'dependencias_existentes_escola_banheiro_alunos_def\' ,\'DEPENDÊNCIAS - BANHEIRO DEFICIÊNCIA\' ,14 ,105 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13396 ,841 ,\'dependencias_existentes_escola_dep_vias_alunos_def\' ,\'DEPENDÊNCIAS - VIAS ADEQUADAS DEFICIÊNC\' ,14 ,106 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13397 ,841 ,\'dependencias_existentes_escola_banheiro_chuveiro\' ,\'DEPENDÊNCIAS - BANHEIRO COM CHUVEIRO\' ,14 ,107 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13398 ,841 ,\'dependencias_existentes_escola_refeitorio\' ,\'DEPENDÊNCIAS - REFEITÓRIO\' ,14 ,108 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13403 ,841 ,\'dependencias_existentes_escola_despensa\' ,\'DEPENDÊNCIAS - DESPENSA\' ,14 ,109 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13405 ,841 ,\'dependencias_existentes_escola_almoxarifado\' ,\'DEPENDÊNCIAS - ALMOXARIFADO\' ,14 ,110 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13407 ,841 ,\'dependencias_existentes_escola_auditorio\' ,\'DEPENDÊNCIAS - AUDITÓRIO\' ,14 ,111 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13410 ,841 ,\'dependencias_existentes_escola_patio_coberto\' ,\'DEPENDÊNCIAS - PÁTIO COBERTO\' ,14 ,112 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13413 ,841 ,\'dependencias_existentes_escola_patio_descoberto\' ,\'DEPENDÊNCIAS - PÁTIO DESCOBERTO\' ,14 ,113 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13414 ,841 ,\'dependencias_existentes_escola_alojamento_aluno\' ,\'DEPENDÊNCIAS - ALOJAMENTO DE ALUNO\' ,14 ,114 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13416 ,841 ,\'dependencias_existentes_escola_alojamento_professo\' ,\'DEPENDÊNCIAS - ALOJAMENTO DE PROFESSOR\' ,14 ,115 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13421 ,841 ,\'dependencias_existentes_escola_area_verde\' ,\'DEPENDÊNCIAS - ÁREA VERDE\' ,14 ,116 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13422 ,841 ,\'dependencias_existentes_escola_lavanderia\' ,\'DEPENDÊNCIAS - LAVANDERIA\' ,14 ,117 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13424 ,841 ,\'dependencias_existentes_escola_nenhuma_relacionada\' ,\'DEPENDÊNCIAS - NENHUMA DAS RELACIONADAS\' ,14 ,118 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13425 ,841 ,\'numero_salas_aula_existentes_escola\' ,\'NÚMERO DE SALAS DE AULA EXISTENTES\' ,14 ,119 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13426 ,841 ,\'numero_salas_usadas_como_salas_aula\' ,\'SALAS UTILIZADAS COMO SALA DE AULA\' ,14 ,123 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13427 ,841 ,\'equipamentos_existentes_escola_televisao\' ,\'EQUIPAMENTOS - APARELHO DE TELEVISÃO\' ,14 ,127 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13428 ,841 ,\'equipamentos_existentes_escola_videocassete\' ,\'EQUIPAMENTOS - VIDEOCASSETE\' ,14 ,131 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13429 ,841 ,\'equipamentos_existentes_escola_dvd\' ,\'EQUIPAMENTOS - APARELHO DE DVD\' ,14 ,135 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13430 ,841 ,\'equipamentos_existentes_escola_antena_parabolica\' ,\'EQUIPAMENTOS - ANTENA PARABÓLICA\' ,14 ,139 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13442 ,841 ,\'equipamentos_existentes_escola_copiadora\' ,\'EQUIPAMENTOS - COPIADORA\' ,14 ,143 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13444 ,841 ,\'equipamentos_existentes_escola_retroprojetor\' ,\'EQUIPAMENTOS - RETROPROJETOR\' ,14 ,147 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13445 ,841 ,\'equipamentos_existentes_escola_impressora\' ,\'EQUIPAMENTOS - IMPRESSORA\' ,14 ,151 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13447 ,841 ,\'equipamentos_existentes_escola_aparelho_som\' ,\'EQUIPAMENTOS - APARELHO DE SOM\' ,14 ,155 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13448 ,841 ,\'equipamentos_existentes_escola_projetor_datashow\' ,\'EQUIPAMENTOS - PROJETOR MULTIMÍDIA\' ,14 ,159 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13449 ,841 ,\'equipamentos_existentes_escola_fax\' ,\'EQUIPAMENTOS - FAX\' ,14 ,163 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13450 ,841 ,\'equipamentos_existentes_escola_maquina_fotografica\' ,\'EQUIPAMENTOS - MÁQUINA FOTOGRÁFICA\' ,14 ,167 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13451 ,841 ,\'equipamentos_existentes_escola_computador\' ,\'EQUIPAMENTOS - COMPUTADORES\' ,14 ,171 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13452 ,841 ,\'equipamentos_impressora_multifuncional\' ,\'EQUIPAMENTOS - IMPRESSORA MULTIFUNCIONAL\' ,14 ,175 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13453 ,841 ,\'quantidade_computadores_uso_administrativo\' ,\'QUANTIDADE DE COMPUTADORES USO ADMINISTR\' ,14 ,179 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13454 ,841 ,\'quantidade_computadores_uso_alunos\' ,\'QUANTIDADE DE COMPUTADORES USO ALUNO\' ,14 ,183 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13455 ,841 ,\'acesso_internet\' ,\'ACESSO À INTERNET\' ,14 ,187 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13456 ,841 ,\'banda_larga\' ,\'BANDA LARGA\' ,14 ,188 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13457 ,841 ,\'total_funcionarios_prof_aux_assistentes_monitores\' ,\'TOTAL DE FUNCIONÁRIOS DA ESCOLA\' ,14 ,189 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13459 ,841 ,\'alimentacao_escolar_aluno\' ,\'ALIMENTAÇÃO ESCOLAR PARA OS ALUNOS\' ,14 ,193 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13460 ,841 ,\'atendimento_educacional_especializado\' ,\'ATENDIMENTO EDUCACIONAL ESPECIALIZADO\' ,14 ,194 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13462 ,841 ,\'atividade_complementar\' ,\'ATIVIDADE COMPLEMENTAR\' ,14 ,195 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13464 ,841 ,\'modalidade_ensino_regular\' ,\'MODALIDADE - ENSINO REGULAR\' ,14 ,196 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13465 ,841 ,\'modalidade_educacao_especial_modalidade_substutiva\' ,\'MODALIDADE - EDUCAÇÃO ESPECIAL\' ,14 ,197 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13467 ,841 ,\'modalidade_educacao_jovens_adultos\' ,\'MODALIDADE - EDUCAÇÃO DE JOVENS E ADULTO\' ,14 ,198 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13468 ,841 ,\'modalidade_educacao_profissional\' ,\'MODALIDADE - EDUCAÇÃO PROFISSIONAL\' ,14 ,199 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13470 ,841 ,\'ensino_fundamental_organizado_ciclos\' ,\'ENSINO FUNDAMENTAL ORGANIZADO EM CICLOS\' ,14 ,200 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13471 ,841 ,\'localizacao_diferenciada_escola\' ,\'LOCALIZAÇÃO DIFERENCIADA DA ESCOLA\' ,14 ,201 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13472 ,841 ,\'materiais_didaticos_especificos_nao_utiliza\' ,\'MATERIAL ESPECÍFICOS - NÃO UTILIZA\' ,14 ,202 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13473 ,841 ,\'materiais_didaticos_especificos_quilombola\' ,\'MATERIAL ESPECÍFICOS - QUILOMBOLA\' ,14 ,203 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13475 ,841 ,\'materiais_didaticos_especificos_indigena\' ,\'MATERIAL ESPECÍFICOS - INDÍGENA\' ,14 ,204 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13477 ,841 ,\'educacao_indigena\' ,\'EDUCAÇÃO INDÍGENA\' ,14 ,205 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13478 ,841 ,\'lingua_ensino_ministrado_lingua_indigena\' ,\'LÍNGUA MINISTRADA - LÍNGUA INDÍGENA\' ,14 ,206 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13479 ,841 ,\'lingua_ensino_ministrada_lingua_portuguesa\' ,\'LÍNGUA MINISTRADA - LÍNGUA PORTUGUESA\' ,14 ,207 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13480 ,841 ,\'codigo_lingua_indigena\' ,\'CÓDIGO DA LÍNGUA INDÍGENA\' ,14 ,208 ,\'\' ,5 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13482 ,841 ,\'escola_cede_espaco_turma_brasil_alfabetizado\' ,\'ESCOLA CEDE ESPAÇO TURMAS BRASIL ALFABET\' ,14 ,213 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13484 ,841 ,\'escola_abre_finais_semanas_comunidade\' ,\'ESCOLA ABRE AOS FINAIS DE SEMANA\' ,14 ,214 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13485 ,841 ,\'escola_formacao_alternancia\' ,\'ESCOLA COM PROPOSTA PEDAGÓGICA DE FORMAÇ\' ,14 ,215 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 20
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13254 ,842 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'20\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13255 ,842 ,\'codigo_escola_inep\' ,\'CÓDIGO INEP DA ESCOLA\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13256 ,842 ,\'codigo_turma_inep\' ,\'CÓDIGO INEP TURMA\' ,14 ,11 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13257 ,842 ,\'codigo_turma_entidade_escola\' ,\'CÓDIGO DA TURMA\' ,14 ,21 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13260 ,842 ,\'nome_turma\' ,\'NOME DA TURMA\' ,14 ,41 ,\'\' ,80 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13261 ,842 ,\'mediacao_didatico_pedagogica\' ,\'TIPO DE MEDIAÇÃO DIDÁTICO PEDAGÓGICA\' ,14 ,121 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13264 ,842 ,\'horario_turma_horario_inicial_hora\' ,\'HORÁRIO DA TURMA - HORA INICIAL\' ,14 ,122 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13266 ,842 ,\'horario_turma_horario_inicial_minuto\' ,\'HORÁRIO DA TURMA - H. INICIAL MINUTO\' ,14 ,124 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13267 ,842 ,\'horario_turma_horario_final_hora\' ,\'HORÁRIO DA TURMA - H. FINAL - HORA\' ,14 ,126 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13269 ,842 ,\'horario_turma_horario_final_minuto\' ,\'HORÁRIO DA TURMA - H. FINAL MINUTO\' ,14 ,128 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13271 ,842 ,\'dia_semana_domingo\' ,\'DIA DA SEMANA - DOMINGO\' ,14 ,130 ,\'\' ,1 ,\'f\' ,\'t\' ,\'e\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13272 ,842 ,\'dia_semana_segunda\' ,\'DIA DA SEMANA - SEGUNDA FEIRA\' ,14 ,131 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13273 ,842 ,\'dia_semana_terca\' ,\'DIA DA SEMANA - TERÇA FEIRA\' ,14 ,132 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13274 ,842 ,\'dia_semana_quarta\' ,\'DIA DA SEMANA - QUARTA FEIRA\' ,14 ,133 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13276 ,842 ,\'dia_semana_quinta\' ,\'DIA DA SEMANA - QUINTA FEIRA\' ,14 ,134 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13277 ,842 ,\'dia_semana_sexta\' ,\'DIA DA SEMANA - SEXTA FEIRA\' ,14 ,135 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13278 ,842 ,\'dia_semana_sabado\' ,\'DIA DA SEMANA - SÁBADO\' ,14 ,136 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13283 ,842 ,\'tipo_atendimento\' ,\'TIPO DE ATENDIMENTO\' ,14 ,137 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13286 ,842 ,\'turma_participante_mais_educacao_ensino_medio_inov\' ,\'TURMA PARTICIPANTE PROG. MAIS EDUCACAO\' ,14 ,138 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'Turma participante do Programa Mais Educação/Ensino Médio Inovador\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13289 ,842 ,\'codigo_tipo_atividade_complementar_1\' ,\'CÓDIGO DO TIPO DE ATIVIDADE 1\' ,14 ,139 ,\'\' ,5 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13290 ,842 ,\'codigo_tipo_atividade_complementar_2\' ,\'CÓDIGO DO TIPO DE ATIVIDADE 2\' ,14 ,144 ,\'\' ,5 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13291 ,842 ,\'codigo_tipo_atividade_complementar_3\' ,\'CÓDIGO DO TIPO DE ATIVIDADE 3\' ,14 ,149 ,\'\' ,5 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13292 ,842 ,\'codigo_tipo_atividade_complementar_4\' ,\'CÓDIGO DO TIPO DE ATIVIDADE 4\' ,14 ,154 ,\'\' ,5 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13293 ,842 ,\'codigo_tipo_atividade_complementar_5\' ,\'CÓDIGO DO TIPO DE ATIVIDADE 5\' ,14 ,159 ,\'\' ,5 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13294 ,842 ,\'codigo_tipo_atividade_complementar_6\' ,\'CÓDIGO DO TIPO DE ATIVIDADE 6\' ,14 ,164 ,\'\' ,5 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13297 ,842 ,\'aee_ensino_sistema_braille\' ,\'ENSINO DO SISTEMA BRAILLE\' ,14 ,169 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13301 ,842 ,\'aee_ensino_uso_recursos_opticos_nao_opticos\' ,\'ENSINO DO USO DE RECURSOS ÓPTICOS\' ,14 ,170 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13302 ,842 ,\'aee_estrategias_desenvolvimento_processos_mentais\' ,\'AEE ESTRAT. P DESENV. DE PROC. MENTAIS\' ,14 ,171 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13304 ,842 ,\'aee_tecnicas_orientacao_mobilidade\' ,\'TÉCNICAS DE ORIENTAÇÃO E MOBILIDADE\' ,14 ,172 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13307 ,842 ,\'aee_ensino_lingua_brasileira_sinais_libras\' ,\'AEE ENS. LINGUA BRAS. DE SINAIS - LIBRAS\' ,14 ,173 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13308 ,842 ,\'aee_ensino_comunicacao_alternativa_aumentativa\' ,\'AEE ENS. DE USO COMUNIC. ALTERNATIVA CAA\' ,14 ,174 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13313 ,842 ,\'aee_estrategia_enriquecimento_curricular\' ,\'AEE ESTRAT. P/ ENRIQUECIMENTO CURRICULAR\' ,14 ,175 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13315 ,842 ,\'aee_ensino_uso_soroban\' ,\'ENSINO DO USO DO SOROBAN\' ,14 ,176 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13318 ,842 ,\'aee_ensino_usabilidade_funcionalidades_informatica\' ,\'AEE USABILIDADE INFORMATICA ACESSIVEL\' ,14 ,177 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13319 ,842 ,\'aee_ensino_lingua_portuguesa_modalidade_escrita\' ,\'AEE ENSINO LINGUA PORTUGUESA MODALIDADE\' ,14 ,178 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13323 ,842 ,\'aee_estrategias_autonomia_ambiente_escolar\' ,\'AEE ESTRATEGIAS PARA AUTONOMIA AMBIENTE\' ,14 ,179 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13326 ,842 ,\'modalidade_turma\' ,\'MODALIDADE\' ,14 ,180 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13327 ,842 ,\'etapa_ensino_turma\' ,\'ETAPA DE ENSINO\' ,14 ,181 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13330 ,842 ,\'codigo_curso_educacao_profissional\' ,\'CÓDIGO CURSO TÉCNICO\' ,14 ,183 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13334 ,842 ,\'disciplinas_turma_quimica\' ,\'DISCIPLINAS - 1 - QUÍMICA\' ,14 ,191 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13336 ,842 ,\'disciplinas_turma_fisica\' ,\'DISCIPLINAS - 2 - FÍSICA\' ,14 ,192 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13339 ,842 ,\'disciplinas_turma_matematica\' ,\'DISCIPLINAS - 3 - MATEMÁTICA\' ,14 ,193 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13341 ,842 ,\'disciplinas_turma_biologia\' ,\'DISCIPLINAS - 4 - BIOLOGIA\' ,14 ,194 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13342 ,842 ,\'disciplinas_turma_ciencias\' ,\'DISCIPLINAS - 5 - CIÊNCIAS\' ,14 ,195 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13345 ,842 ,\'disciplinas_turma_lingua_literatura_portuguesa\' ,\'DISCIPLINAS - 6 - LÍNGUA PORTUGUESA\' ,14 ,196 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13350 ,842 ,\'disciplinas_lingua_literatura_estrangeira_inglesa\' ,\'DISCIPLINAS - 7 - LINGUA INGLESA\' ,14 ,197 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13352 ,842 ,\'disciplinas_lingua_literatura_estrangeira_espanhol\' ,\'DISCIPLINAS - 8 - LÍNGUA ESPANHOLA\' ,14 ,198 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13358 ,842 ,\'disciplinas_lingua_literatura_estrangeira_frances\' ,\'DISCIPLINAS - 30 - LÍNGUA FRANCÊSA\' ,14 ,199 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13362 ,842 ,\'disciplinas_lingua_literatura_estrangeira_outra\' ,\'DISCIPLINAS - 9 - LÍNGUA OUTRA\' ,14 ,200 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13364 ,842 ,\'disciplinas_turma_artes\' ,\'DISCIPLINAS - 10 - ARTES\' ,14 ,201 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13366 ,842 ,\'disciplinas_turma_educacao_fisica\' ,\'DISCIPLINAS - 11 - EDUCAÇÃO FÍSICA\' ,14 ,202 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13369 ,842 ,\'disciplinas_turma_historia\' ,\'DISCIPLINAS - 12 - HISTÓRIA\' ,14 ,203 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13372 ,842 ,\'disciplinas_turma_geografia\' ,\'DISCIPLINAS - 13 - GEOGRAFIA\' ,14 ,204 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13374 ,842 ,\'disciplinas_turma_filosofia\' ,\'DISCIPLINAS - 14 - FILOSOFIA\' ,14 ,205 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13378 ,842 ,\'disciplinas_turma_estudos_sociais\' ,\'DISCIPLINAS - 28 - ESTUDOS SOCIAIS\' ,14 ,206 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13380 ,842 ,\'disciplinas_turma_sociologia\' ,\'DISCIPLINAS - 29 - SOCIOLOGIA\' ,14 ,207 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13382 ,842 ,\'disciplinas_turma_informatica_computacao\' ,\'DISCIPLINAS - 16 - INFORMÁTICA/COMPUTAÇÃ\' ,14 ,208 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13386 ,842 ,\'disciplinas_turma_disciplinas_profissionalizantes\' ,\'DISCIPLINAS 17 - PROFISSIONALIZANTE\' ,14 ,209 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13390 ,842 ,\'disciplinas_turma_voltadas_atendimento_necessidade\' ,\'DISCIPLINA VOLTADAS A ATEND. NECESSIDADE\' ,14 ,210 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13393 ,842 ,\'disciplinas_turma_voltadas_diversidade_sociocultur\' ,\'DISCIPLINA VOLTADAS DIVERSIDADE SOCIOCUL\' ,14 ,211 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13400 ,842 ,\'disciplinas_turma_libras\' ,\'DISCIPLINAS - 23 - LIBRAS\' ,14 ,212 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13402 ,842 ,\'disciplinas_turma_disciplinas_pedagogicas\' ,\'DISCIPLINAS - 25 - PEDAGÓGICAS\' ,14 ,213 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13406 ,842 ,\'disciplinas_turma_ensino_religioso\' ,\'DISCIPLINAS - 26 - ENSINO RELIGIOSO\' ,14 ,214 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13408 ,842 ,\'disciplinas_turma_lingua_indigena\' ,\'DISCIPLINAS - 27 - LÍNGUA INDÍGENA\' ,14 ,215 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13411 ,842 ,\'disciplinas_turma_outras\' ,\'DISCIPLINAS - 99 - OUTRAS\' ,14 ,216 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 30
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13438 ,843 ,\'tipo_registro\' ,\'TIPO DE REGISTRO \' ,14 ,1 ,\'30\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13439 ,843 ,\'codigo_escola_inep\' ,\'CÓDIGO DE ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13440 ,843 ,\'identificacao_unica_docente_inep\' ,\'IDENTIFICAÇÃO ÚNICA DO DOCENTE INEP\' ,14 ,11 ,\'\' ,12 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13441 ,843 ,\'codigo_docente_entidade_escola\' ,\'CÓDIGO DO DOCENTE NA ENTIDADE/ESCOLA\' ,14 ,23 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13443 ,843 ,\'nome_completo\' ,\'NOME COMPLETO\' ,14 ,43 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13446 ,843 ,\'email\' ,\'E-MAIL\' ,14 ,143 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13458 ,843 ,\'numero_identificacao_social_nis\' ,\'NÚMERO DE IDENTIFICAÇÃO SOCIAL (NIS)\' ,14 ,243 ,\'\' ,11 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13461 ,843 ,\'data_nascimento\' ,\'DATA DE NASCIMENTO\' ,14 ,254 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13463 ,843 ,\'sexo\' ,\'SEXO\' ,14 ,264 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13466 ,843 ,\'cor_raca\' ,\'COR/RAÇA\' ,14 ,265 ,\'0\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13469 ,843 ,\'filiacao\' ,\'FILIAÇÃO\' ,14 ,266 ,\'0\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13474 ,843 ,\'filiacao_1\' ,\'FILIAÇÃO 1\' ,14 ,267 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13476 ,843 ,\'filiacao_2\' ,\'FILIAÇÃO 2\' ,14 ,367 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13481 ,843 ,\'nacionalidade_docente\' ,\'NACIONALIDADE DO PROFISSIONAL ESCOLAR\' ,14 ,467 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'Nacionalidade do Profissional escolar em sala de Aula\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13483 ,843 ,\'pais_origem\' ,\'PAÍS DE ORIGEM\' ,14 ,468 ,\'\' ,3 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13486 ,843 ,\'uf_nascimento\' ,\'UF DE NASCIMENTO\' ,14 ,471 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13487 ,843 ,\'municipio_nascimento\' ,\'MUNICÍPIO DE NASCIMENTO\' ,14 ,473 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13488 ,843 ,\'docente_deficiencia\' ,\'PROFISSIONAL ESCOLAR COM DEFICIÊNCIA\' ,14 ,480 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'Profissional escolar com deficiência e Tipos de deficiência\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13490 ,843 ,\'tipos_deficiencia_cegueira\' ,\'TIPO DE DEFICIÊNCIA - CEGUEIRA\' ,14 ,481 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13492 ,843 ,\'tipos_deficiencia_baixa_visao\' ,\'TIPO DE DEFICIÊNCIA - BAIXA VISÃO\' ,14 ,482 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13494 ,843 ,\'tipos_deficiencia_surdez\' ,\'TIPO DE DEFICIÊNCIA - SURDEZ\' ,14 ,483 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13495 ,843 ,\'tipos_deficiencia_auditiva\' ,\'TIPO DE DEFICIÊNCIA - AUDITIVA\' ,14 ,484 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13496 ,843 ,\'tipos_deficiencia_surdocegueira\' ,\'TIPO DE DEFICIÊNCIA - SURDOCEGUEIRA\' ,14 ,485 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13507 ,843 ,\'tipos_deficiencia_fisica\' ,\'TIPO DE DEFICIÊNCIA - FÍSICA\' ,14 ,486 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13508 ,843 ,\'tipos_deficiencia_intelectual\' ,\'TIPO DE DEFICIÊNCIA - INTELECTUAL\' ,14 ,487 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13509 ,843 ,\'tipos_deficiencia_multipla\' ,\'TIPO DE DEFICIÊNCIA - MÚLTIPLA\' ,14 ,488 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 40
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13489 ,844 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'40\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13491 ,844 ,\'codigo_escola_inep\' ,\'CÓDIGO DA ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13493 ,844 ,\'identificacao_unica_docente_inep\' ,\'IDENTIFICAÇÃO ÚNICA DO DOCENTE INEP\' ,14 ,11 ,\'\' ,12 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13497 ,844 ,\'codigo_docente_entidade_escola\' ,\'CÓDIGO DO DOCENTE ENTIDADE/ESCOLA\' ,14 ,23 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13498 ,844 ,\'numero_cpf\' ,\'NÚMERO DO CPF\' ,14 ,43 ,\'\' ,11 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13499 ,844 ,\'localizacao_zona_residencia\' ,\'LOCALIZAÇÃO/ZONA DA RESIDÊNCIA\' ,14 ,54 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13500 ,844 ,\'cep\' ,\'CEP\' ,14 ,55 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13501 ,844 ,\'endereco\' ,\'ENDEREÇO\' ,14 ,63 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13502 ,844 ,\'numero_endereco\' ,\'NÚMERO DO ENDEREÇO\' ,14 ,163 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13503 ,844 ,\'complemento\' ,\'COMPLEMENTO\' ,14 ,173 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13504 ,844 ,\'bairro\' ,\'BAIRRO\' ,14 ,193 ,\'\' ,50 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13505 ,844 ,\'uf\' ,\'UF\' ,14 ,243 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13506 ,844 ,\'municipio\' ,\'MUNICÍPIO\' ,14 ,245 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 50
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13530 ,845 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'50\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13532 ,845 ,\'codigo_escola_inep\' ,\'CÓDIGO DA ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13533 ,845 ,\'identificacao_unica_docente\' ,\'IDENTIFICAÇÃO ÚNICA DO DOCENTE(INEP\' ,14 ,11 ,\'\' ,12 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13534 ,845 ,\'codigo_docente_entidade_escola\' ,\'CÓDIGO DO DOCENTE NA ENTIDADE/ESCOLA\' ,14 ,23 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13535 ,845 ,\'escolaridade\' ,\'ESCOLARIDADE\' ,14 ,43 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13536 ,845 ,\'situacao_curso_superior_1\' ,\'SITUAÇÃO DO CURSO SUPERIOR 1\' ,14 ,44 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13538 ,845 ,\'formacao_complementacao_pedagogica_1\' ,\'FORMAÇÃO/COMPLEMENTAÇÃO PEDAGÓGICA 1\' ,14 ,45 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13540 ,845 ,\'codigo_curso_superior_1\' ,\'CÓDIGO DO CURSO SUPERIOR 1\' ,14 ,46 ,\'\' ,6 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13542 ,845 ,\'ano_inicio_curso_superior_1\' ,\'ANO DE INÍCIO DO CURSO SUPERIOR 1\' ,14 ,52 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13544 ,845 ,\'ano_conclusao_curso_superior_1\' ,\'ANO DE CONCLUSÃO DO CURSO SUPERIOR 1\' ,14 ,56 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13545 ,845 ,\'instituicao_curso_superior_1\' ,\'INSTITUIÇÃO DO CURSO SUPERIOR 1\' ,14 ,60 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13547 ,845 ,\'situacao_curso_superior_2\' ,\'SITUAÇÃO DO CURSO SUPERIOR 2\' ,14 ,67 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13549 ,845 ,\'formacao_complementacao_pedagogica_2\' ,\'FORMAÇÃO/COMPLEMENTAÇÃO PEDAGÓGIGA 2\' ,14 ,68 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13551 ,845 ,\'codigo_curso_superior_2\' ,\'CÓDIGO DO CURSO SUPERIOR 2\' ,14 ,69 ,\'\' ,6 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13555 ,845 ,\'ano_inicio_curso_superior_2\' ,\'ANO DE INÍCIO DO CURSO SUPERIOR 2\' ,14 ,75 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13557 ,845 ,\'ano_conclusao_curso_superior_2\' ,\'ANO DE CONCLUSÃO DO CURSO SUPERIOR 2\' ,14 ,79 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13560 ,845 ,\'instituicao_curso_superior_2\' ,\'INSTITUIÇÃO DO CURSO SUPERIOR 2\' ,14 ,83 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13562 ,845 ,\'situacao_curso_superior_3\' ,\'SITUAÇÃO DO CURSO SUPERIOR 3\' ,14 ,90 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13564 ,845 ,\'formacao_complementacao_pedagogica_3\' ,\'FORMAÇÃO/COMPLEMENTAÇÃO PEDAGÓGICA 3\' ,14 ,91 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13566 ,845 ,\'codigo_curso_superior_3\' ,\'CÓDIGO DO CURSO SUPERIOR 3\' ,14 ,92 ,\'\' ,6 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13567 ,845 ,\'ano_inicio_curso_superior_3\' ,\'ANO DE INÍCIO DO CURSO SUPERIOR 3\' ,14 ,98 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13570 ,845 ,\'ano_conclusao_curso_superior_3\' ,\'ANO DE CONCLUSÃO DO CURSO SUPERIOR 3\' ,14 ,102 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13574 ,845 ,\'instituicao_curso_superior_3\' ,\'INSTITUIÇÃO DO CURSO SUPERIOR 3\' ,14 ,106 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13577 ,845 ,\'pos_graduacao_especializacao\' ,\'PÓS GRADUAÇÃO - ESPECIALIZAÇÃO\' ,14 ,113 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13584 ,845 ,\'pos_graduacao_mestrado\' ,\'PÓS GRADUAÇÃO - MESTRADO\' ,14 ,114 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13585 ,845 ,\'pos_graduacao_doutorado\' ,\'PÓS GRADUAÇÃO DOUTORADO\' ,14 ,115 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13587 ,845 ,\'pos_graduacao\' ,\'PÓS GRADUAÇÃO\' ,14 ,116 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13589 ,845 ,\'especifico_creche_0_3_anos\' ,\'ESPECÍFICO PARA CRECHE DE 0 A 3 ANOS\' ,14 ,117 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13591 ,845 ,\'especifico_pre_escola_4_5_anos\' ,\'ESPECÍFICO PARA PRÉ-ESCOLA DE 4 A 5 ANOS\' ,14 ,118 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13592 ,845 ,\'especifico_anos_iniciais_ensino_fundamental\' ,\'ESPECÍFICO PARA ANOS INICIAIS ENSINO FUN\' ,14 ,119 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13594 ,845 ,\'especifico_anos_finais_ensino_fundamental\' ,\'ESPECÍFICO PARA ANOS FINAIS DO ENS FUNDA\' ,14 ,120 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13595 ,845 ,\'especifico_ensino_medio\' ,\'ESPECÍFICO PARA ENSINO MÉDIO\' ,14 ,121 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13596 ,845 ,\'especifico_eja\' ,\'ESPECÍFICO PARA EDUCAÇÃO JOVENS ADULTOS\' ,14 ,122 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13598 ,845 ,\'especifico_educacao_especial\' ,\'ESPECÍFICO PARA EDUCAÇÃO ESPECIAL\' ,14 ,123 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13600 ,845 ,\'especifico_educacao_indigena\' ,\'ESPECÍFICO PARA EDUCAÇÃO INDÍGENA\' ,14 ,124 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13602 ,845 ,\'especifico_educacao_campo\' ,\'ESPECÍFICO PARA EDUCAÇÃO DO CAMPO\' ,14 ,125 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13605 ,845 ,\'especifico_educacao_ambiental\' ,\'ESPECÍFICO PARA EDUCAÇÃO AMBIENTAL\' ,14 ,126 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13606 ,845 ,\'especifico_educacao_direitos_humanos\' ,\'ESPECÍFICO PARA EDUCAÇÃO EM DIREITOS HUM\' ,14 ,127 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13608 ,845 ,\'genero_diversidade_sexual\' ,\'GÊNERO E DIVERSIDADE SEXUAL\' ,14 ,128 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13610 ,845 ,\'direitos_crianca_adolescente\' ,\'DIREITOS DE CRIANÇA E ADOLESCENTE\' ,14 ,129 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13612 ,845 ,\'educ_relacoes_etnicorraciais_his_cult_afro_brasil\' ,\'EDUC PARA ETNIA HIS CUL AFRO BRASILEIRA\' ,14 ,130 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13615 ,845 ,\'outros\' ,\'OUTROS\' ,14 ,131 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13616 ,845 ,\'nenhum\' ,\'NENHUM\' ,14 ,132 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');


-- cadastro registro 51
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13510 ,846 ,\'tipo_registro\' ,\'TIPO DE REGISTRO \' ,14 ,1 ,\'51\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13511 ,846 ,\'codigo_escola_inep\' ,\'CÓDIGO DA ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13512 ,846 ,\'identificacao_unica_inep\' ,\'IDENTIFICAÇÃO ÚNICA(INEP) PROFISSIONAL\' ,14 ,11 ,\'\' ,12 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13513 ,846 ,\'codigo_docente_entidade_escola\' ,\'CÓDIGO DO PROFISSIONAL ESCOLAR EM SALA\' ,14 ,23 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'Código do Profissional escolar em sala de Aula na Entidade/Escola\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13514 ,846 ,\'codigo_turma_inep\' ,\'CÓDIGO DA TURMA NO INEP\' ,14 ,43 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13515 ,846 ,\'codigo_turma_entidade_escola\' ,\'CÓDIGO DA TURMA NA ENTIDADE/ESCOLA\' ,14 ,53 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13516 ,846 ,\'funcao_exerce_escola_turma\' ,\'FUNÇÃO QUE EXERCE NA ESCOLA/TURMA\' ,14 ,73 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13517 ,846 ,\'situacao_funcional_contratacao_vinculo\' ,\'SITUAÇÃO FUNCIONAL/REGIME DE CONTRATAÇÃO\' ,14 ,74 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'Situação Funcional/ Regime de contratação/Tipo de Vínculo\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13518 ,846 ,\'codigo_disciplina_1\' ,\'CÓDIGO DA DISCIPLINA 1\' ,14 ,75 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13519 ,846 ,\'codigo_disciplina_2\' ,\'CÓDIGO DA DISCIPLINA 2\' ,14 ,77 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13520 ,846 ,\'codigo_disciplina_3\' ,\'CÓDIGO DA DISCIPLINA 3\' ,14 ,79 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13521 ,846 ,\'codigo_disciplina_4\' ,\'CÓDIGO DA DISCIPLINA 4\' ,14 ,81 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13522 ,846 ,\'codigo_disciplina_5\' ,\'CÓDIGO DA DISCIPLINA 5\' ,14 ,83 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13523 ,846 ,\'codigo_disciplina_6\' ,\'CÓDIGO DA DISCIPLINA 6\' ,14 ,85 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13524 ,846 ,\'codigo_disciplina_7\' ,\'CÓDIGO DA DISCIPLINA 7\' ,14 ,87 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13525 ,846 ,\'codigo_disciplina_8\' ,\'CÓDIGO DA DISCIPLINA 8\' ,14 ,89 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13526 ,846 ,\'codigo_disciplina_9\' ,\'CÓDIGO DA DISCIPLINA 9\' ,14 ,91 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13527 ,846 ,\'codigo_disciplina_10\' ,\'CÓDIGO DA DISCIPLINA 10\' ,14 ,93 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13528 ,846 ,\'codigo_disciplina_11\' ,\'CÓDIGO DA DISCIPLINA \' ,14 ,95 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13529 ,846 ,\'codigo_disciplina_12\' ,\'CÓDIGO DA DISCIPLINA 12\' ,14 ,97 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13531 ,846 ,\'codigo_disciplina_13\' ,\'CÓDIGO DA DISCIPLINA 13\' ,14 ,99 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 60
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13553 ,847 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'60\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13559 ,847 ,\'codigo_escola_inep\' ,\'CÓDIGO DA ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13563 ,847 ,\'identificacao_unica_aluno\' ,\'IDENTIFICAÇÃO ÚNICA DO ALUNO (INEP)\' ,14 ,11 ,\'\' ,12 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13568 ,847 ,\'codigo_aluno_entidade_escola\' ,\'CÓDIGO DO ALUNO NA ENTIDADE/ESCOLA\' ,14 ,23 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13572 ,847 ,\'nome_completo\' ,\'NOME COMPLETO\' ,14 ,43 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13575 ,847 ,\'data_nascimento\' ,\'DATA DE NASCIMENTO\' ,14 ,143 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13580 ,847 ,\'sexo\' ,\'SEXO\' ,14 ,153 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13582 ,847 ,\'cor_raca\' ,\'COR/RAÇA\' ,14 ,154 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13586 ,847 ,\'filiacao\' ,\'FILIAÇÃO\' ,14 ,155 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13588 ,847 ,\'filiacao_1\' ,\'FILIAÇÃO 1\' ,14 ,156 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13590 ,847 ,\'filiacao_2\' ,\'FILIAÇÃO 2\' ,14 ,256 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13593 ,847 ,\'nacionalidade_aluno\' ,\'NACIONALIDADE DO ALUNO\' ,14 ,356 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13601 ,847 ,\'pais_origem\' ,\'PAÍS DE ORIGEM\' ,14 ,357 ,\'\' ,3 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13604 ,847 ,\'uf_nascimento\' ,\'UF DE NASCIMENTO\' ,14 ,360 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13613 ,847 ,\'municipio_nascimento\' ,\'MUNICÍPIO DE NASCIMENTO\' ,14 ,362 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13617 ,847 ,\'alunos_deficiencia_transtorno_desenv_superdotacao\' ,\'TRANSTORNO DESENVOLVIMENTO OU SUPERDOTAÇ\' ,14 ,369 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13619 ,847 ,\'tipos_defic_transtorno_cegueira\' ,\'TRANSTORNO DESENV/SUPERDOTAÇÃO CEGUEIRA\' ,14 ,370 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13622 ,847 ,\'tipos_defic_transtorno_baixa_visao\' ,\'TRANSTORNO DESENV/SUPERDOTAÇÃO BAIXA VIS\' ,14 ,371 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13624 ,847 ,\'tipos_defic_transtorno_surdez\' ,\'TRANSTORNO DESENV/SUPERDOTAÇÃO SURDEZ\' ,14 ,372 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13626 ,847 ,\'tipos_defic_transtorno_auditiva\' ,\'TRANSTORNO DESENV/SUPERDOTAÇÃO DEF AUDIT\' ,14 ,373 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13629 ,847 ,\'tipos_defic_transtorno_surdocegueira\' ,\'TRANSTORNO DESENV/SUPERDOTAÇÃO SURDOCEGU\' ,14 ,374 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13630 ,847 ,\'tipos_defic_transtorno_def_fisica\' ,\'TRANSTORNO DESENV/SUPERDOTAÇÃO DEF FISIC\' ,14 ,375 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13631 ,847 ,\'tipos_defic_transtorno_def_intelectual\' ,\'TRANSTORNO DESENV/SUPERDOT DEF INTELECTU\' ,14 ,376 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13632 ,847 ,\'tipos_defic_transtorno_def_multipla\' ,\'TRANSTORNO DESENV/SUPERDOT DEF MULTÍPLA\' ,14 ,377 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13633 ,847 ,\'tipos_defic_transtorno_def_autismo_infantil\' ,\'TRANSTORNO DESENV/SUPERDOT DEF AUTIS INF\' ,14 ,378 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13635 ,847 ,\'tipos_defic_transtorno_def_asperger\' ,\'TRANSTORNO DESENV/SUPERDOT DEF ASPERGER\' ,14 ,379 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13637 ,847 ,\'tipos_defic_transtorno_def_sindrome_rett\' ,\'TRANSTORNO DESENV/SUPERDOT DEF RETT\' ,14 ,380 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13639 ,847 ,\'tipos_defic_transtorno_desintegrativo_infancia\' ,\'TRANSTORNO DESENV/SUPERDOT DESINT INFANC\' ,14 ,381 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13640 ,847 ,\'tipos_defic_transtorno_altas_habilidades\' ,\'TRANSTORNO DESENV/SUPERDOT ALTAS HABILID\' ,14 ,382 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13642 ,847 ,\'recurso_auxilio_ledor\' ,\'RECURSO AUXILIO LEDOR\' ,14 ,383 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13646 ,847 ,\'recurso_auxilio_transcricao\' ,\'RECURSO AUXILIO LEDOR\' ,14 ,384 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13649 ,847 ,\'recurso_auxilio_interprete\' ,\'RECURSO AUXILIO INTERPRETE\' ,14 ,385 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13650 ,847 ,\'recurso_auxilio_interprete_libras\' ,\'RECURSO AUXILIO INTERPRETE LIBRAS\' ,14 ,386 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13651 ,847 ,\'recurso_auxilio_leitura_labial\' ,\'RECURSO AUXILIO LEITURA LABIAL\' ,14 ,387 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13653 ,847 ,\'recurso_auxilio_prova_ampliada_16\' ,\'RECURSO AUXILIO PROVA AMPLIADA 16\' ,14 ,388 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13654 ,847 ,\'recurso_auxilio_prova_ampliada_20\' ,\'RECURSO AUXILIO PROVA AMPLIADA 20\' ,14 ,389 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13656 ,847 ,\'recurso_auxilio_prova_ampliada_24\' ,\'RECURSO AUXILIO PROVA AMPLIADA 24\' ,14 ,390 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13671 ,847 ,\'recurso_auxilio_prova_braille\' ,\'RECURSO AUXILIO PROVA BRAILLE\' ,14 ,391 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13672 ,847 ,\'recurso_auxilio_nenhum\' ,\'RECURSO AUXILIO NENHUM\' ,14 ,392 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 70
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13539 ,848 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'70\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13541 ,848 ,\'codigo_escola_inep\' ,\'CÓDIGO DE ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13543 ,848 ,\'identificacao_unica_aluno\' ,\'IDENTIFICAÇÃO ÚNICA DO ALUNO (INEP)\' ,14 ,11 ,\'\' ,12 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13546 ,848 ,\'codigo_aluno_entidade\' ,\'CÓDIGO DO ALUNO NA ENTIDADE/ESCOLA\' ,14 ,23 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13548 ,848 ,\'numero_identidade\' ,\'NÚMERO DA IDENTIDADE\' ,14 ,43 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13550 ,848 ,\'orgao_emissor_identidade\' ,\'ÓRGÃO EMISSOR DA IDENTIDADE\' ,14 ,63 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13552 ,848 ,\'uf_identidade\' ,\'UF DA IDENTIDADE\' ,14 ,65 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13569 ,848 ,\'data_expedicao_identidade\' ,\'DATA DE EXPEDIÇÃO DA IDENTIDADE\' ,14 ,67 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13571 ,848 ,\'certidao_civil\' ,\'CERTIDÃO CIVIL\' ,14 ,77 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13573 ,848 ,\'tipo_certidao_civil\' ,\'TIPO DE CERTIDÃO CIVIL\' ,14 ,78 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13576 ,848 ,\'numero_termo\' ,\'NÚMERO DO TERMO\' ,14 ,79 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13578 ,848 ,\'folha\' ,\'FOLHA\' ,14 ,87 ,\'\' ,4 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13579 ,848 ,\'livro\' ,\'LIVRO\' ,14 ,91 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13581 ,848 ,\'data_emissao_certidao\' ,\'DATA DE EMISSÃO DA CERTIDÃO\' ,14 ,99 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13583 ,848 ,\'uf_cartorio\' ,\'UF DO CARTÓRIO\' ,14 ,109 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13597 ,848 ,\'municipio_cartorio\' ,\'MUNICÍPIO DO CARTÓRIO\' ,14 ,111 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13599 ,848 ,\'codigo_cartorio\' ,\'CÓDIGO DO CARTÓRIO\' ,14 ,118 ,\'\' ,6 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13603 ,848 ,\'numero_matricula\' ,\'NÚMERO DA MATRÍCULA - CERTIDÃO NOVA\' ,14 ,124 ,\'\' ,32 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13607 ,848 ,\'numero_cpf\' ,\'NÚMERO DO CPF\' ,14 ,156 ,\'\' ,11 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13609 ,848 ,\'documento_estrangeiro_passaporte\' ,\'DOCUMENTO ESTRANGEIRO/PASSAPORTE\' ,14 ,167 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13611 ,848 ,\'numero_identificacao_social\' ,\'NÚMERO DE IDENTIFICAÇÃO SOCIAL (NIS)\' ,14 ,187 ,\'\' ,11 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13614 ,848 ,\'localizacao_zona_residencia\' ,\'LOCALIZAÇÃO / ZONA DE RESIDÊNCIA\' ,14 ,198 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13618 ,848 ,\'cep\' ,\'CEP\' ,14 ,199 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13620 ,848 ,\'endereco\' ,\'ENDEREÇO\' ,14 ,207 ,\'\' ,100 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13621 ,848 ,\'numero\' ,\'NÚMERO\' ,14 ,307 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13623 ,848 ,\'complemento\' ,\'COMPLEMENTO\' ,14 ,317 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13625 ,848 ,\'bairro\' ,\'BAIRRO\' ,14 ,337 ,\'\' ,50 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13627 ,848 ,\'uf\' ,\'UF\' ,14 ,387 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13628 ,848 ,\'municipio\' ,\'MUNICÍPIO\' ,14 ,389 ,\'\' ,7 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 80
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13636 ,849 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'80\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13638 ,849 ,\'codigo_escola_inep\' ,\'CÓDIGO DA ESCOLA - INEP\' ,14 ,3 ,\'\' ,8 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13641 ,849 ,\'identificacao_unica_aluno\' ,\'IDENTIFICAÇÃO ÚNICA DO ALUNO( INEP )\' ,14 ,11 ,\'\' ,12 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13643 ,849 ,\'codigo_aluno_entidade_escola\' ,\'CÓDIGO DO ALUNO NA ENTIDADE/ESCOLA\' ,14 ,23 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13644 ,849 ,\'codigo_turma_inep\' ,\'CÓDIGO DA TURMA NO INEP\' ,14 ,43 ,\'\' ,10 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13645 ,849 ,\'codigo_turma_entidade_escola\' ,\'CÓDIGO DA TURMA NA ENTIDADE/ESCOLA\' ,14 ,53 ,\'\' ,20 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13647 ,849 ,\'codigo_matricula_aluno\' ,\'CÓDIGO DA MATRÍCULA DO ALUNO\' ,14 ,73 ,\'\' ,12 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13648 ,849 ,\'turma_unificada\' ,\'TURMA UNIFICADA\' ,14 ,85 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13652 ,849 ,\'codigo_etapa_multi_etapa\' ,\'ALUNO NA TURMA MULTIETAPA EJA NORMAL/PRO\' ,14 ,86 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13655 ,849 ,\'recebe_escolarizacao_outro_espaco\' ,\'RECEBE ESCOLARIZAÇÃO EM OUTRO ESPAÇO\' ,14 ,88 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13657 ,849 ,\'transporte_escolar_publico\' ,\'TRANSPORTE ESCOLAR PÚBLICO\' ,14 ,89 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13658 ,849 ,\'poder_publico_transporte_escolar\' ,\'PODER PÚBLICO RESPONSÁVEL PELO TRANSPORT\' ,14 ,90 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13659 ,849 ,\'rodoviario_vans_kombi\' ,\'RODOVIÁRIO - VANS/KOMBI\' ,14 ,91 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13660 ,849 ,\'rodoviario_microonibus\' ,\'RODOVIARIO - MICROÔNIBUS\' ,14 ,92 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13661 ,849 ,\'rodoviario_onibus\' ,\'RODOVIARIO ÔNIBUS\' ,14 ,93 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13662 ,849 ,\'rodoviario_bicicleta\' ,\'RODOVIARIO BICICLETA\' ,14 ,94 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13663 ,849 ,\'rodoviario_tracao_animal\' ,\'RODOVIARIO TRAÇÃO ANIMAL\' ,14 ,95 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13664 ,849 ,\'rodoviario_outro\' ,\'RODOVIARIO OUTRO\' ,14 ,96 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13665 ,849 ,\'aquaviario_embarcacao_5_pessoas\' ,\'AQUAVIARIO EMBARCACAO CAPACIDADE PARA 5\' ,14 ,97 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13666 ,849 ,\'aquaviario_embarcacao_5_a_15_pessoas\' ,\'AQUAVIARIO EMBARCACAO CAPACIDADE 5 A 15\' ,14 ,98 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13667 ,849 ,\'aquaviario_embarcacao_15_a_35_pessoas\' ,\'AQUAVIARIO EMBARCACAO CAPACIDADE 15 A 35\' ,14 ,99 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13668 ,849 ,\'aquaviario_embarcacao_mais_de_35_pessoas\' ,\'AQUAVIARIO EMBARCACAO CAPACIDADE + 35 PE\' ,14 ,100 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13669 ,849 ,\'ferroviario_trem_metro\' ,\'FERROVIÁRIO TREM/METRÔ\' ,14 ,101 ,\'\' ,1 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13670 ,849 ,\'forma_ingresso_aluno_escola_federal\' ,\'FORMA INGRESSO ALUNO ESCOLA FEDERAL\' ,14 ,102 ,\'\' ,2 ,\'f\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- cadastro registro 99
select fc_executa_ddl('
insert into db_layoutcampos( db52_codigo ,db52_layoutlinha ,db52_nome ,db52_descr ,db52_layoutformat ,db52_posicao ,db52_default ,db52_tamanho ,db52_ident ,db52_imprimir ,db52_alinha ,db52_obs ,db52_quebraapos ) values ( 13634 ,850 ,\'tipo_registro\' ,\'TIPO DE REGISTRO\' ,14 ,1 ,\'99\' ,2 ,\'t\' ,\'t\' ,\'d\' ,\'\' ,0 );
');

-- adicionado vinculos dos campos do layout do registro 50 com avaliação 3000002 - CENSO RECURSO HUMANO
select fc_executa_ddl('
 select setval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\', (select max(ed313_sequencial) from avaliacaoperguntaopcaolayoutcampo));
 insert into avaliacaoperguntaopcaolayoutcampo
        (ed313_sequencial, ed313_ano, ed313_db_layoutcampo, ed313_avaliacaoperguntaopcao, ed313_layoutvalorcampo )
 values ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13592, 3000079, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13594, 3000080, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13589, 3000077, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13596, 3000082, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13595, 3000081, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13591, 3000078, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13585, 3000075, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13577, 3000073, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13584, 3000074, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13587, 3000076, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13610, 3000118, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13612, 3000119, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13605, 3000115, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13602, 3000114, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13606, 3000116, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13598, 3000127, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13600, 3000084, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13608, 3000117, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13616, 3000121, 1),
        ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13615, 3000120, 1);
');

-- Ajustado vínculos dos campos do layout do registro 10 para 2016
select fc_executa_ddl('
insert into avaliacaoperguntaopcaolayoutcampo
        (ed313_sequencial, ed313_ano, ed313_db_layoutcampo, ed313_avaliacaoperguntaopcao, ed313_layoutvalorcampo )
values ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13262, 3000039, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13263, 3000040, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13265, 3000041, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13268, 3000042, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13270, 3000043, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13275, 3000044, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13279, 3000045, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13280, 3000560, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13282, 3000046, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13284, 3000047, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13284, 3000048, 2),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13284, 3000049, 3),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13285, 3000097, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13285, 3000098, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13287, 3000571, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13288, 3000572, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13296, 3000576, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13298, 3000573, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13299, 3000574, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13303, 3000575, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13306, 3000099, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13306, 3000100, 2),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13309, 3000088, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13311, 3000089, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13312, 3000090, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13317, 3000091, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13320, 3000092, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13322, 3000093, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13324, 3000094, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13328, 3000095, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13329, 3000096, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13331, 3000050, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13332, 3000051, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13335, 3000052, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13337, 3000067, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13343, 3000068, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13346, 3000069, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13347, 3000070, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13349, 3000071, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13353, 3000072, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13356, 3000000, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13357, 3000001, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13360, 3000017, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13363, 3000002, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13367, 3000003, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13370, 3000004, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13373, 3000005, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13375, 3000006, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13377, 3000007, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13381, 3000008, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13383, 3000009, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13388, 3000010, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13389, 3000011, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13391, 3000012, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13392, 3000013, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13394, 3000014, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13395, 3000015, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13396, 3000126, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13397, 3000018, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13398, 3000019, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13403, 3000020, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13405, 3000021, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13407, 3000022, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13410, 3000023, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13413, 3000024, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13414, 3000025, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13416, 3000026, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13421, 3000027, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13422, 3000028, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13424, 3000016, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13425, 3000103, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13426, 3000104, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13427, 3000056, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13428, 3000057, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13429, 3000058, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13430, 3000059, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13442, 3000060, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13444, 3000061, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13445, 3000062, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13447, 3000063, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13448, 3000064, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13449, 3000065, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13450, 3000066, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13451, 3000032, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13452, 3000579, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13453, 3000033, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13454, 3000113, \'\'),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13455, 3000035, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13455, 3000036, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13456, 3000037, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13456, 3000038, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13459, 3000101, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13459, 3000102, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13460, 3000108, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13460, 3000109, 2),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13460, 3000110, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13462, 3000105, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13462, 3000106, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13462, 3000107, 2),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13470, 3000111, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13470, 3000112, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13472, 3000053, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13473, 3000054, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13475, 3000055, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13482, 3000122, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13482, 3000123, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13484, 3000124, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13484, 3000125, 0),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13485, 3000561, 1),
       ( nextval(\'avaliacaoperguntaopcaolayoutcampo_ed313_sequencial_seq\'), 2016, 13485, 3000562, 0);
');

SQL_PRE;

        $ddl = <<<'SQL_DDL'
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FINANCEIRO ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

select fc_executa_ddl('alter table acordocomissaomembro add ac07_datainicio date');
select fc_executa_ddl('alter table acordocomissaomembro add ac07_datatermino date');

select fc_executa_ddl('alter table acordoposicao add ac26_tipooperacao integer');

-- Controle de contratos encerrados
select fc_executa_ddl('create sequence acordos.acordoencerramentolicitacon_ac58_sequencial_seq increment 1 minvalue 1 maxvalue 9223372036854775807 start 1 cache 1;');
select fc_executa_ddl('create table acordos.acordoencerramentolicitacon(
ac58_sequencial   int4 not null default nextval(\'acordoencerramentolicitacon_ac58_sequencial_seq\'),
ac58_acordo       int4 not null,
ac58_data         date,
constraint acordoencerramentolicitacon_sequ_pk primary key (ac58_sequencial));');
select fc_executa_ddl('alter table acordoencerramentolicitacon
add constraint acordoencerramentolicitacon_acordo_fk foreign key (ac58_acordo)
references acordo;');

-- Campo Ano do Exercício na Tabela acordoempempenho
select fc_executa_ddl('alter table acordoempempenho add column ac54_ano int4 default null;');

alter table empautitem add column e55_matunid int4 default null;
alter table empautitem add constraint empautitem_matunid_fk foreign key (e55_matunid) references matunid;

drop table if exists bkp_contacorrenteduplicados;
drop table if exists bkp_de_para_contacorrente;
update contacorrentedetalhe
   set c19_contacorrente = 1
  from conplanoreduz, conplano
 where c61_codcon = c60_codcon
   and c61_anousu = c60_anousu
   and c61_reduz  = c19_reduz
   and c19_contacorrente = 103
   and c60_anousu = 2016
   and c60_estrut ilike '82111%'
   and exists (select 1 from db_config where upper(uf) = 'RS');

update contacorrentedetalhe
   set c19_concarpeculiar = '000'
 where c19_contacorrente = 1
   and c19_concarpeculiar is null;

create table bkp_contacorrenteduplicados
    as select min(c19_sequencial) as primeiro_sequencial,
       c19_orctiporec,
       c19_concarpeculiar,
       c19_reduz,
       count(*)
  from contacorrentedetalhe
       inner join conplanoreduz on conplanoreduz.c61_reduz = contacorrentedetalhe.c19_reduz
                               and conplanoreduz.c61_anousu = contacorrentedetalhe.c19_conplanoreduzanousu
       inner join conplano  on conplano.c60_codcon = conplanoreduz.c61_codcon
                           and conplano.c60_anousu = conplanoreduz.c61_anousu
 where c19_contacorrente = 1
   and c19_conplanoreduzanousu = 2016
   and c60_estrut ilike '82111%'
 group by c19_orctiporec,c19_concarpeculiar, c19_reduz, c19_instit having count(*) > 1;

create table bkp_de_para_contacorrente
    as select c.c19_sequencial as sequencial_errado,
              bkp.primeiro_sequencial
         from contacorrentedetalhe c
              inner join bkp_contacorrenteduplicados bkp  on bkp.c19_orctiporec = c.c19_orctiporec
                                                         and bkp.c19_concarpeculiar::varchar = c.c19_concarpeculiar::varchar
                                                         and bkp.c19_reduz = c.c19_reduz
                                                         and c.c19_sequencial <> bkp.primeiro_sequencial
                                                         and c.c19_conplanoreduzanousu = 2016 ;

update contacorrentedetalheconlancamval
   set c28_contacorrentedetalhe = bkp.primeiro_sequencial
  from bkp_de_para_contacorrente as bkp
 where bkp.sequencial_errado = contacorrentedetalheconlancamval.c28_contacorrentedetalhe;

delete from contacorrentesaldo where c29_contacorrentedetalhe in (select sequencial_errado from bkp_de_para_contacorrente);
delete from contacorrentedetalhe where c19_sequencial in (select sequencial_errado from bkp_de_para_contacorrente);

---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FOLHA ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

CREATE SEQUENCE rhconsignacaobancolayout_rh178_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;


CREATE TABLE rhconsignacaobancolayout(
  rh178_sequencial      int4 NOT NULL  default nextval('rhconsignacaobancolayout_rh178_sequencial_seq'),
  rh178_db_banco        varchar(10) NOT NULL ,
  rh178_layout      int4 NOT NULL ,
  rh178_rubrica     char(4) NOT NULL ,
  rh178_instit      int4 ,
  CONSTRAINT rhconsignacaobancolayout_sequ_pk PRIMARY KEY (rh178_sequencial));



ALTER TABLE rhconsignacaobancolayout
ADD CONSTRAINT rhconsignacaobancolayout_banco_fk FOREIGN KEY (rh178_db_banco)
REFERENCES db_bancos;

ALTER TABLE rhconsignacaobancolayout
ADD CONSTRAINT rhconsignacaobancolayout_layout_fk FOREIGN KEY (rh178_layout)
REFERENCES db_layouttxt;

ALTER TABLE rhconsignacaobancolayout
ADD CONSTRAINT rhconsignacaobancolayout_rubrica_instit_fk FOREIGN KEY (rh178_rubrica,rh178_instit)
REFERENCES rhrubricas;

CREATE  INDEX rhconsignacaobancolayout_rubrica_in ON rhconsignacaobancolayout(rh178_rubrica);
CREATE  INDEX rhconsignacaobancolayout_layout_in ON rhconsignacaobancolayout(rh178_layout);
CREATE  INDEX rhconsignacaobancolayout_banco_in ON rhconsignacaobancolayout(rh178_db_banco);

alter table rhconsignadomovimento add rh151_arquivo oid;
alter table rhconsignadomovimento add rh151_banco varchar(10);

alter table rhconsignadomovimentoservidor alter rh152_regist type varchar;

---------------------------------------------------------------------------------------------------------------
---------------------------------------- INICIO EDUCACAO ------------------------------------------------------
---------------------------------------------------------------------------------------------------------------
insert into funcaoatividade values (5, 'Docente titular - coordenador de tutoria(de módulo ou disciplina) - EAD', true);
insert into funcaoatividade values (6, 'Docente tutor - (de módulo ou disciplina)', true);

alter table ensino add column ed10_censocursoprofiss int4 default null;
alter table ensino add constraint ensino_censocursoprofiss_fk foreign key (ed10_censocursoprofiss) references censocursoprofiss;
create index ensino_censocursoprofiss_in on ensino(ed10_censocursoprofiss);
alter table escola alter COLUMN ed18_latitude type varchar(20), alter COLUMN ed18_longitude type varchar(20);

---------------------------------------------------------------------------------------------------------------
------------------------------------------- INICIO SAUDE ------------------------------------------------------
---------------------------------------------------------------------------------------------------------------
CREATE SEQUENCE agendasaidapassagemdestino_tf38_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE SEQUENCE passagemdestino_tf37_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE agendasaidapassagemdestino(
tf38_sequencial    int4 NOT NULL default 0,
tf38_agendasaida   int4 NOT NULL default 0,
tf38_valorunitario real NOT NULL default 0,
tf38_cgs           int4 NOT NULL default 0,
tf38_fica          bool default 'f',
CONSTRAINT agendasaidapassagemdestino_sequ_pk PRIMARY KEY (tf38_sequencial));

CREATE TABLE passagemdestino(
tf37_sequencial int4 NOT NULL default 0,
tf37_valor      real NOT NULL default 0,
tf37_destino    int4 default 0,
CONSTRAINT passagemdestino_sequ_pk PRIMARY KEY (tf37_sequencial));

ALTER TABLE agendasaidapassagemdestino
ADD CONSTRAINT agendasaidapassagemdestino_cgs_fk FOREIGN KEY (tf38_cgs)
REFERENCES cgs_und;

ALTER TABLE agendasaidapassagemdestino
ADD CONSTRAINT agendasaidapassagemdestino_agendasaida_fk FOREIGN KEY (tf38_agendasaida)
REFERENCES tfd_agendasaida;

ALTER TABLE passagemdestino
ADD CONSTRAINT passagemdestino_destino_fk FOREIGN KEY (tf37_destino)
REFERENCES tfd_destino;

CREATE UNIQUE INDEX agendasaidapassagemdestino_agendasaida_cgs_in ON agendasaidapassagemdestino(tf38_agendasaida, tf38_cgs);
CREATE UNIQUE INDEX passagemdestino_destino_in ON passagemdestino(tf37_destino);

alter table tfd_agendasaida add column tf17_tiposaida int4 default 1 not null;
SQL_DDL;

        $pos = <<<'SQL_POS'
insert into db_versao (db30_codver, db30_codversao, db30_codrelease, db30_data, db30_obs)  values (367, 3, 51, '2016-05-19', 'Tarefas: 99632, 99634, 99640, 99647, 99648, 99649, 99650, 99652, 99654, 99655, 99657, 99658, 99659, 99660, 99661, 99663, 99665, 99666, 99667, 99668, 99669, 99670, 99671, 99672, 99673, 99674, 99675, 99676, 99677, 99678, 99679, 99681, 99682, 99684, 99685, 99686, 99687, 99688, 99689, 99690, 99692, 99693, 99694, 99696, 99697, 99698, 99699, 99701, 99702, 99704, 99705, 99707, 99708, 99710, 99711, 99712, 99713, 99714, 99715, 99716, 99717, 99721, 99723, 99725, 99726, 99727, 99728, 99730, 99731, 99732, 99733, 99735, 99736, 99737, 99738, 99739, 99740, 99741, 99742, 99743, 99744, 99746, 99747, 99748, 99749, 99750, 99751, 99752, 99753, 99755, 99757, 99758, 99759, 99762, 99763, 99766, 99768, 99769, 99771');drop function if exists fc_executa_baixa_banco(integer, date);
create or replace function fc_executa_baixa_banco( cod_ret integer,
                                                   datausu date,
                                                   OUT processado boolean,
                                                   OUT codigo integer,
                                                   OUT descricao text)
returns setof record as
$$
declare

  iInstitSessao                  integer;
  iAnoSessao                     integer;
  iRows                          integer;
  iRegistroAProcessar            integer;
  iOrigensDuplicadas             integer default 0;
  iCodigoRetornoBaixa            integer default 0;
  -- variavel de controle do numpre , se tiver ativado o pgto parcial, e essa variavel for dif. de 0
  -- os numpres a partir dele serao tratados como pgto parcial, abaixo, sem pgto parcial
  iNumprePagamentoParcial        integer default 0;
  iQuantidadeRegistros           integer default 0;

  sRetornoBaixa                  varchar;

  lAtivaPgtoParcial              boolean default false;
  lUltimoProcessamento           boolean default false;
  lRaise                         boolean default false;
  sDebug                         text;
  r_idret                        record;

  -- Utilizado para armazenar as informações de retorno da função que executa a baixa
  rRetornoBaixa                  record;
  sMensagemErro                  text;

begin

  -- Busca Dados Sessão
  iInstitSessao := cast(fc_getsession('DB_instit') as integer);
  if iInstitSessao is null then
     raise exception 'Variavel de sessão [DB_instit] não encontrada.';
  end if;

  iAnoSessao    := cast(fc_getsession('DB_anousu') as integer);
  if iAnoSessao is null then
     raise exception 'Variavel de sessão [DB_anousu] não encontrada.';
  end if;

  --
  -- Validamos os dados da sessão
  --
  if cast( fc_getsession('DB_id_usuario') as integer) is null then
    raise exception 'Variavel de sessão [DB_id_usuario] não encontrada.';
  end if;

  if cast( fc_getsession('DB_datausu') as date) is null then
    raise exception 'Variavel de sessão [DB_datausu] não encontrada.';
  end if;

  if cast( fc_getsession('DB_use_pcasp') as boolean) is null then
    raise exception 'Variavel de sessão [DB_use_pcasp] não encontrada.';
  end if;

  lRaise        := ( case when fc_getsession('DB_debugon') is null then false else true end );

  if lRaise is true then
    perform fc_debug('<PreProcessamento> - ###############################################',lRaise,true,false);
    perform fc_debug('<PreProcessamento> -  INICIO BAIXA DE BANCO    <PREPROCESSAMENTO>   ',lRaise,false,false);
    perform fc_debug('<PreProcessamento> - ###############################################',lRaise,false,false);

    raise info '';
    raise info ' Inicio do Processamento da Baixa de Banco...';
    raise info '';

  end if;

  select k03_pgtoparcial
    into lAtivaPgtoParcial
    from numpref
   where k03_instit = iInstitSessao
     and k03_anousu = iAnoSessao;

  /**
   * Caso NAO exista pagamento parcial ativo, apenas executa a baixa de banco.
   */
  if lAtivaPgtoParcial is false then

    if lRaise is true then
      perform fc_debug('<PreProcessamento> '                                     ,lRaise,false,false);
      perform fc_debug('<PreProcessamento> Pagamento Parcial não está ativado...',lRaise,false,false);
      perform fc_debug('<PreProcessamento> '                                     ,lRaise,false,false);
    end if;
    /**
     * cria estrutura temporaria
     */
    perform fc_baixa_banco_estrutura_temporaria( cod_ret );

    /**
     * Inserimos os IDRETS referente aos recibos originados de um empenho de prestação de contas
     * que tenham o seu VALOR PAGO diferente do VALOR ORIGINAL do recibo
     */
    insert into tmpnaoprocessar
         select distinct disbanco.idret
           from empprestarecibo
                inner join recibo   on recibo.k00_numpre   = empprestarecibo.e170_numpre
                                   and recibo.k00_numpar   = empprestarecibo.e170_numpar
                inner join disbanco on disbanco.k00_numpre = recibo.k00_numpre
                                   and disbanco.k00_numpar = recibo.k00_numpar
          where disbanco.vlrpago <> recibo.k00_valor
            and disbanco.codret = cod_ret;

    begin

      select baixa.codigo_retorno, baixa.descricao
        from fc_baixabanco( cod_ret, datausu ) as baixa
        into rRetornoBaixa;

      processado := (rRetornoBaixa.codigo_retorno = 1)::boolean;
      codigo     := rRetornoBaixa.codigo_retorno;
      descricao  := rRetornoBaixa.descricao;

    exception
      when raise_exception then

        get stacked diagnostics sMensagemErro := MESSAGE_TEXT;

        processado := false;
        codigo     := null;
        descricao  := sMensagemErro;
      end;

    return next;
    return;

  end if;

  /**
   * Quando pagamento parcial ativo
   */
  if lRaise is true then
    perform fc_debug('<PreProcessamento> '                                 ,lRaise,false,false);
    perform fc_debug('<PreProcessamento> Pagamento Parcial está ativado...',lRaise,false,false);
    perform fc_debug('<PreProcessamento> '                                 ,lRaise,false,false);
  end if;

  /**
   * buscamos o valor base setado na numpref campo k03_numprepgtoparcial
   */
  select k03_numprepgtoparcial
    into iNumprePagamentoParcial
    from numpref
   where k03_anousu = iAnoSessao
     and k03_instit = iInstitSessao;

  /**
   * Executa as Baixas de Banco.
   */
  while lUltimoProcessamento is false loop

    if lRaise is true then

      perform fc_debug('<PreProcessamento> - ###############################################',lRaise,false,false);
      perform fc_debug('<PreProcessamento> - lUltimoProcessamento: '||lUltimoProcessamento   ,lRaise,false,false);
      perform fc_debug('<PreProcessamento> - ###############################################',lRaise,false,false);
      perform fc_debug('<PreProcessamento> - ###############################################',lRaise,false,false);
      perform fc_debug('<PreProcessamento> -                  INICIO WHILE                  ',lRaise,false,false);
      perform fc_debug('<PreProcessamento> - ###############################################',lRaise,false,false);

      raise info '';
      raise info ' Dentro do WHILE...';
      raise info ' UltimoProcessamento: %',lUltimoProcessamento;
      raise info '';

    end if;

    /**
     * 0 - Default
     * 1 - Processado Com sucesso
     */
    if lRaise is true then
      perform fc_debug('<PreProcessamento> - ###############################################',lRaise,false,false);
      perform fc_debug('<PreProcessamento> - iCodigoRetornoBaixa: '||iCodigoRetornoBaixa||', iQuantidadeRegistros: '||iQuantidadeRegistros,lRaise,false,false);
    end if;

    if iCodigoRetornoBaixa not in (0, 1) then

      lUltimoProcessamento := true;
      continue;
    end if;

    /**
     * cria estrutura temporaria
     */
    perform fc_baixa_banco_estrutura_temporaria( cod_ret );

    /**
     * Pagamentos em Carnê
     */
    insert into tmp_registrosaprocessar
    select distinct
           disbanco.k00_numpre,
           disbanco.k00_numpar,
           disbanco.idret
      from disbanco
           left join numprebloqpag  on numprebloqpag.ar22_numpre = disbanco.k00_numpre
                                   and numprebloqpag.ar22_numpar = disbanco.k00_numpar
     where disbanco.codret            = cod_ret
       and disbanco.classi            is false
       and disbanco.instit            = iInstitSessao
       and numprebloqpag.ar22_numpre  is null
       and disbanco.k00_numpre        > 0
       and disbanco.k00_numpar        > 0
       and not exists ( select 1
                          from tmpnaoprocessar
                         where idret = disbanco.idret )
     group by disbanco.k00_numpre,
              disbanco.k00_numpar,
              disbanco.idret;

    /***
     * Pagamentos em Recibos da CGF
     */
    insert into tmp_registrosaprocessar
    select distinct
           recibopaga.k00_numpre,
           recibopaga.k00_numpar,
           disbanco.idret
      from disbanco
           inner join recibopaga on disbanco.k00_numpre = recibopaga.k00_numnov
     where disbanco.codret = cod_ret
       and case when iNumprePagamentoParcial = 0
             then true
             else disbanco.k00_numpre > iNumprePagamentoParcial
           end
       and disbanco.classi is false
       and not exists ( select 1
                          from tmpnaoprocessar
                         where idret = disbanco.idret )
       and disbanco.instit = iInstitSessao;


    /***
     * Pagamentos em Recibos Avulsos
     */
    insert into tmp_registrosaprocessar
    select distinct
           recibo.k00_numpre,
           recibo.k00_numpar,
           disbanco.idret
      from disbanco
           inner join recibo on disbanco.k00_numpre = recibo.k00_numpre
     where disbanco.codret = cod_ret
       and case when iNumprePagamentoParcial = 0
             then true
             else disbanco.k00_numpre > iNumprePagamentoParcial
           end
       and not exists ( select 1
                          from tmpnaoprocessar
                         where idret = disbanco.idret )
       and disbanco.classi is false
       and disbanco.instit = iInstitSessao;

    /*
     * Verificamos a quantidade de vezes que a baixa de banco deverá ser processada
     *
     * A lógica a princípio eh ver quantos numpres possuem mais de um idret
     * O maior numero de idret para o mesmo numpre e parcela será a quantidade de vezes que a baixa de banco
     * será processada
     *
     * Por exemplo:
     * Numpre Parcela IdRets
     * 1      1       1,2,3
     * 2      1       4,5
     * 3      1       6
     *
     * A quantidade de vezes que a baixa de banco deverá ser processada é 3 pois é a maior quantidade de idrets
     * para o mesmo numpre e parcela.
     *
     */
    select coalesce( max(qtd), 1)
      into iQuantidadeRegistros
      from ( select array_upper(idrets.array,1)::integer as qtd
              from (select array_accum(distinct idret) as array
                      from tmp_registrosaprocessar
                     group by numpre, numpar) as idrets
             group by qtd) as loops;

    if lRaise is true then
      perform fc_debug('<PreProcessamento> - Quantidade de vezes que a baixa de banco será processada: ' || iQuantidadeRegistros, lRaise);
    end if;

    /*
     * Inserimos os idrets a não serem processados neste loop, estes idrets serão processados no proximo loop
     *
     * A lógica é identificar o idret que possui o numpre e parcela de outro idret, o primeiro idret que será
     * processado é o menor, após serão processados os demais idret de acordo com o processamento da baixa de banco
     */
    insert into tmpnaoprocessar
      select distinct idret
        from tmp_registrosaprocessar
             inner join ( select numpre,
                                 numpar,
                                 min(idret)
                            from tmp_registrosaprocessar
                           group by numpre, numpar
                          having count(distinct idret) > 1) as duplos on duplos.numpre = tmp_registrosaprocessar.numpre
                                                                     and duplos.numpar = tmp_registrosaprocessar.numpar
                                                                     and idret        <> min
       where not exists ( select 1
                            from tmpnaoprocessar
                           where idret = min );

    select array_accum(idret)
      into sDebug
      from tmpnaoprocessar;

    perform fc_debug('<PreProcessamento> - IdRet\'s inseridos na tmpNAOprocessar: ' || sDebug, lRaise );

    if iQuantidadeRegistros = 1  then
      lUltimoProcessamento := true;
    end if;

    /**
     * Inserimos os IDRETS referente aos recibos originados de um empenho de prestação de contas
     * que tenham o seu VALOR PAGO diferente do VALOR ORIGINAL do recibo
     */
    insert into tmpnaoprocessar
         select distinct disbanco.idret
           from empprestarecibo
                inner join recibo   on recibo.k00_numpre   = empprestarecibo.e170_numpre
                                   and recibo.k00_numpar   = empprestarecibo.e170_numpar
                inner join disbanco on disbanco.k00_numpre = recibo.k00_numpre
                                   and disbanco.k00_numpar = recibo.k00_numpar
          where disbanco.vlrpago <> recibo.k00_valor
            and disbanco.codret = cod_ret;


    if lRaise is true then

      raise info '----------------------------------------------------------';
      raise info '  cod_ret: % datausu: % ',cod_ret,datausu;
      raise info '               EXECUTANDO BAIXA DE BANCO                  ';
      raise info '----------------------------------------------------------';

      perform fc_debug('<PreProcessamento> - ##########################################################',lRaise,false,false);
      perform fc_debug('<PreProcessamento> - #               EXECUTANDO BAIXA DE BANCO                #',lRaise,false,false);
      perform fc_debug('<PreProcessamento> - ########################  Debug 1 ########################',lRaise,false,false);
      perform fc_debug('<PreProcessamento> - # cod_ret: '||cod_ret||', datausu: '||datausu||'   #######',lRaise,false,false);
      perform fc_debug('<PreProcessamento> - ##########################################################',lRaise,false,false);

    end if;

    -- Executa a baixa de banco
    begin

      select baixa.codigo_retorno, baixa.descricao
        from fc_baixabanco( cod_ret, datausu ) as baixa
        into rRetornoBaixa;

      processado := (rRetornoBaixa.codigo_retorno = 1)::boolean;
      codigo     := rRetornoBaixa.codigo_retorno;
      descricao  := rRetornoBaixa.descricao;

    exception
      when raise_exception then

        get stacked diagnostics sMensagemErro := MESSAGE_TEXT;

        processado := false;
        codigo     := null;
        descricao  := sMensagemErro;
      end;

    return next;

    iCodigoRetornoBaixa := codigo;
    sRetornoBaixa       := descricao;

    if lRaise is true then

      raise info '';
      raise info 'Resposta Baixa de Banco:     %',sRetornoBaixa;
      raise info '----------------------------------------------------------';

      perform fc_debug('<PreProcessamento> - #############################'||lpad('', length(sRetornoBaixa), '#') ,lRaise,false,false);
      perform fc_debug('<PreProcessamento> - Resposta Baixa de Banco:     '||sRetornoBaixa||'.'                   ,lRaise,false,false);
      perform fc_debug('<PreProcessamento> - #############################'||lpad('', length(sRetornoBaixa), '#') ,lRaise,false,false);

    end if;
  end loop;--Fim do While

  if lRaise is true then

    raise info '';
    raise info ' Fim do Processamento ';
    raise info '';
    raise info ' O log do processamento esta na variavel db_debug gravado na sessao.';
    raise info ' Para ver o log execute: select fc_getsession(''db_debug'');';
    perform fc_debug('<PreProcessamento> - ################  FIM DO PREPROCESSAMENTO ################################' ,lRaise,false,true);

  end if;

end;

$$ language 'plpgsql';drop function if exists fc_baixabanco(integer, date);
create or replace function fc_baixabanco( cod_ret integer,
                                          datausu date,
                                          OUT codigo_retorno integer,
                                          OUT descricao text)
returns record as
$$
declare

  retorno                          boolean default false;

  r_codret                         record;
  r_idret                          record;
  r_divold                         record;
  r_receitas                       record;
  r_idunica                        record;
  q_disrec                         record;
  r_testa                          record;

  x_totreg                         float8;
  valortotal                       float8;
  valorjuros                       float8;
  valormulta                       float8;
  fracao                           float8;
  nVlrRec                          float8;
  nVlrTfr                          float8;
  nVlrRecm                         float8;
  nVlrRecj                         float8;

  _testeidret                      integer;
  vcodcla                          integer;
  gravaidret                       integer;
  v_nextidret                      integer;
  conta                            integer;

  v_contador                       integer;
  v_somador                        numeric(15,2) default 0;
  v_valor                          numeric(15,2) default 0;

  v_valor_sem_round                float8;
  v_diferenca_round                float8;

  dDataCalculoRecibo               date;
  dDataReciboUnica                 date;

  v_contagem                       integer;
  primeirarec                      integer default 0;
  primeirarecj                     integer default 0;
  primeirarecm                     integer default 0;
  primeiranumpre                   integer;
  primeiranumpar                   integer;

  nBloqueado                       integer;

  valorlanc                        float8;
  valorlancj                       float8;
  valorlancm                       float8;

  oidrec                           int8;

  autentsn                         boolean;

  valorrecibo                      float8;

  v_total1                         float8 default 0;
  v_total2                         float8 default 0;

  v_estaemrecibopaga               boolean;
  v_estaemrecibo                   boolean;
  v_estaemarrecadnormal            boolean;
  v_estaemarrecadunica             boolean;
  lVerificaReceita                 boolean;
  lClassi                          boolean;
  lReciboInvalidoPorTrocaDeReceita boolean default false;
  lReciboPossuiPgtoParcial         boolean default false;

  nSimDivold                       integer;
  nNaoDivold                       integer;
  iQtdeParcelasAberto              integer;
  iQtdeParcelasRecibo              integer;

  nValorSimDivold                  numeric(15,2) default 0;
  nValorNaoDivold                  numeric(15,2) default 0;
  nValorTotDivold                  numeric(15,2) default 0;

  nValorPagoDivold                 numeric(15,2) default 0;
  nTotValorPagoDivold              numeric(15,2) default 0;

  nTotalRecibo                     numeric(15,2) default 0;
  nTotalNovosRecibos               numeric(15,2) default 0;

  nTotalDisbancoOriginal           numeric(15,2) default 0;
  nTotalDisbancoDepois             numeric(15,2) default 0;

  iNumnovDivold                    integer;
  iIdret                           integer;
  v_diferenca                      float8 default 0;

  cCliente                         varchar(100);
  iIdRetProcessar                  integer;

  -- Abatimentos
  lAtivaPgtoParcial                boolean default false;
  lInsereJurMulCorr                boolean default true;

  iAbatimento                      integer;
  iAbatimentoArreckey              integer;
  iArreckey                        integer;
  iArrecadCompos                   integer;
  iNumpreIssVar                    integer;
  iNumpreRecibo                    integer;
  iNumpreReciboAvulso              integer;
  iTipoDebitoPgtoParcial           integer;
  iTipoAbatimento                  integer;
  iTipoReciboAvulso                integer;
  iReceitaCredito                  integer;
  iReceitaPadraoCredito            integer;
  iRows                            integer;
  iSeqIdRet                        integer;
  iNumpreAnterior                  integer default 0;

  nVlrCalculado                    numeric(15,2) default 0;
  nVlrPgto                         numeric(15,2) default 0;
  nVlrJuros                        numeric(15,2) default 0;
  nVlrMulta                        numeric(15,2) default 0;
  nVlrCorrecao                     numeric(15,2) default 0;
  nVlrHistCompos                   numeric(15,2) default 0;
  nVlrJurosCompos                  numeric(15,2) default 0;
  nVlrMultaCompos                  numeric(15,2) default 0;
  nVlrCorreCompos                  numeric(15,2) default 0;
  nVlrPgtoParcela                  numeric(15,2) default 0;
  nVlrDiferencaPgto                numeric(15,2) default 0;
  nVlrTotalRecibopaga              numeric(15,2) default 0;
  nVlrTotalHistorico               numeric(15,2) default 0;
  nVlrTotalJuroMultaCorr           numeric(15,2) default 0;
  nVlrReceita                      numeric(15,2) default 0;
  nVlrAbatido                      numeric(15,2) default 0;
  nVlrDiferencaDisrec              numeric(15,2) default 0;
  nVlrInformado                    numeric(15,2) default 0;
  nVlrTotalInformado               numeric(15,2) default 0;

  nVlrToleranciaPgtoParcial        numeric(15,2) default 0;
  nVlrToleranciaCredito            numeric(15,2) default 0;

  nPercPgto                        numeric;
  nPercReceita                     numeric;
  nPercDesconto                    numeric;

  iCountDebitoOrigem               integer default 0;
  iCountRecibopaga                 integer default 0;

  iAnoSessao                       integer;
  iInstitSessao                    integer;

  rReciboPaga                      record;
  rContador                        record;
  rRecordDisbanco                  record;
  rRecordBanco                     record;
  rRecord                          record;
  rRecibo                          record;
  rAcertoDiferenca                 record;

  /**
   * variavel de controle do numpre , se tiver ativado o pgto parcial, e essa variavel for dif. de 0
   * os numpres a partir dele serão tratados como pgto parcial, abaixo, sem pgto parcial
   */
  iNumprePagamentoParcial          integer default 0;

  lRaise                           boolean default false;
  sDebug                           text;

begin

  -- Busca Dados Sessão
  iInstitSessao := cast(fc_getsession('DB_instit') as integer);
  iAnoSessao    := cast(fc_getsession('DB_anousu') as integer);
  lRaise        := ( case when fc_getsession('DB_debugon') is null then false else true end );

  if lRaise is true then
    if trim(fc_getsession('db_debug')) <> '' then
      perform fc_debug('  <BaixaBanco>  - INICIANDO PROCESSAMENTO... ',lRaise,false,false);
    else
      perform fc_debug('  <BaixaBanco>  - INICIANDO PROCESSAMENTO... ',lRaise,true,false);
    end if;
  end if;

  /**
   * Verifica se o numpre pertence a outra instituição e insere na tmpnaoprocessar
   * para gerar inconsistencia
   */
  insert into tmpnaoprocessar
       select idret
         from disbanco
              inner join arreinstit on arreinstit.k00_numpre = disbanco.k00_numpre
        where arreinstit.k00_instit <> iInstitSessao
          and disbanco.codret       = cod_ret;

  /**
   * Verifica se esta configurado Pagamento Parcial
   * Buscamos o valor base setado na numpref campo k03_numprepgtoparcial
   * Consulta o tipo de debito configurado para Recibo Avulso
   * Consulta o parametro de tolerancia para pagamento parcial
   */
  select k03_pgtoparcial,
         k03_numprepgtoparcial,
         k03_reciboprot,
         coalesce(numpref.k03_toleranciapgtoparc,0)::numeric(15, 2),
         coalesce(numpref.k03_toleranciacredito,0)::numeric(15, 2),
         k03_receitapadraocredito
    into lAtivaPgtoParcial,
         iNumprePagamentoParcial,
         iTipoReciboAvulso,
         nVlrToleranciaPgtoParcial,
         nVlrToleranciaCredito,
         iReceitaPadraoCredito
    from numpref
   where numpref.k03_anousu = iAnoSessao
     and numpref.k03_instit = iInstitSessao;

   if lRaise is true then
     perform fc_debug('  <BaixaBanco>  - PARAMETROS DO NUMPREF '                                  ,lRaise,false,false);
     perform fc_debug('  <BaixaBanco>  - lAtivaPgtoParcial:  '||lAtivaPgtoParcial                 ,lRaise,false,false);
     perform fc_debug('  <BaixaBanco>  - iNumprePagamentoParcial:  '||iNumprePagamentoParcial     ,lRaise,false,false);
     perform fc_debug('  <BaixaBanco>  - iTipoReciboAvulso:  '||iTipoReciboAvulso                 ,lRaise,false,false);
     perform fc_debug('  <BaixaBanco>  - nVlrToleranciaPgtoParcial:  '||nVlrToleranciaPgtoParcial ,lRaise,false,false);
     perform fc_debug('  <BaixaBanco>  - nVlrToleranciaCredito:  '||nVlrToleranciaCredito         ,lRaise,false,false);
     perform fc_debug('  <BaixaBanco>  - iReceitaPadraoCredito:  '||iReceitaPadraoCredito         ,lRaise,false,false);
   end if;

   if iTipoReciboAvulso is null then

     codigo_retorno := 2;
     descricao := 'Operacao Cancelada. Tipo de Debito nao configurado para Recibo Avulso.';
     return;
   end if;

    select k00_conta,
           autent,
           count(*)
      into conta,
           autentsn,
           vcodcla
      from disbanco
           inner join disarq on disarq.codret = disbanco.codret
     where disbanco.codret = cod_ret
       and disbanco.classi is false
       and disbanco.instit = iInstitSessao
  group by disarq.k00_conta,
           disarq.autent;

  if vcodcla is null or vcodcla = 0 then
    codigo_retorno := 3;
    descricao := 'ARQUIVO DE RETORNO DO BANCO JA CLASSIFICADO.';
    return;
  end if;

  if conta is null or conta = 0 then
    codigo_retorno := 4;
    descricao := 'SEM CONTA CADASTRADA PARA ARRECADACAO. OPERACAO CANCELADA.';
    return;
  end if;

  if lRaise is true then
    perform fc_debug('  <BaixaBanco>  - autentsn:  '||autentsn,lRaise,false,false);
  end if;

  select upper(munic)
  into cCliente
  from db_config
  where codigo = iInstitSessao;

  if autentsn is false then

    select nextval('discla_codcla_seq')
      into vcodcla;

    insert into discla ( codcla,  codret,  dtcla,   instit )
                values ( vcodcla, cod_ret, datausu, iInstitSessao );

   /**
    * Insere dados da baixa de Banco nesta tabela pois na pl que a chama o arquivo e divido em mais de uma classificacao
    */
   if lRaise is true then
     perform fc_debug('  <BaixaBanco> - 276 - '||cod_ret||','||vcodcla,lRaise,false,false);
   end if;

   insert into   tmp_classificaoesexecutadas("codigo_retorno", "codigo_classificacao")
          values (cod_ret, vcodcla);

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  - vcodcla: '||vcodcla,lRaise,false,false);
    end if;

  else
    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  - nao '||autentsn,lRaise,false,false);
    end if;
  end if;

/**
 * Aqui inicia pre-processamento do Pagamento Parcial
 */
  if lAtivaPgtoParcial is true then

    if lRaise then
      perform fc_debug('  <PgtoParcial>  - Parametro pagamento parcial ativado !',lRaise,false,false);
    end if;

    /*******************************************************************************************************************
     *  VERIFICA RECIBO AVULSO
     ******************************************************************************************************************/
    -- Caso exista algum recibo avulso que jah esteja pago, o sistema gera um novo recibo avulso
    if lRaise then
      perform fc_debug('  <PgtoParcial> Regra 1 - VERIFICA RECIBO AVULSO',lRaise,false,false);
    end if;

    for rRecordDisbanco in

      select disbanco.*
        from disbanco
             inner join recibo   on recibo.k00_numpre   = disbanco.k00_numpre
             inner join arrepaga on arrepaga.k00_numpre = disbanco.k00_numpre
       where disbanco.codret = cod_ret
         and disbanco.classi is false
         and case when iNumprePagamentoParcial = 0
                  then true
                  else disbanco.k00_numpre > iNumprePagamentoParcial
              end
         and disbanco.instit = iInstitSessao

    loop

      select nextval('numpref_k03_numpre_seq')
        into iNumpreRecibo;

      if lRaise is true then
        perform fc_debug('  <PgtoParcial>  - lança recibo avulso já pago ',lRaise,false,false);
      end if;

      insert into recibo ( k00_numcgm,
                           k00_dtoper,
                           k00_receit,
                           k00_hist,
                           k00_valor,
                           k00_dtvenc,
                           k00_numpre,
                           k00_numpar,
                           k00_numtot,
                           k00_numdig,
                           k00_tipo,
                           k00_tipojm,
                           k00_codsubrec,
                           k00_numnov
                         ) select k00_numcgm,
                                  k00_dtoper,
                                  k00_receit,
                                  k00_hist,
                                  k00_valor,
                                  k00_dtvenc,
                                  iNumpreRecibo,
                                  k00_numpar,
                                  k00_numtot,
                                  k00_numdig,
                                  k00_tipo,
                                  k00_tipojm,
                                  k00_codsubrec,
                                  k00_numnov
                             from recibo
                            where recibo.k00_numpre = rRecordDisbanco.k00_numpre;

      insert into reciborecurso ( k00_sequen, k00_numpre, k00_recurso )
           select nextval('reciborecurso_k00_sequen_seq'),
                  iNumpreRecibo,
                  k00_recurso
             from reciborecurso
            where reciborecurso.k00_numpre = rRecordDisbanco.k00_numpre;

      insert into arrehist ( k00_numpre,
                             k00_numpar,
                             k00_hist,
                             k00_dtoper,
                             k00_hora,
                             k00_id_usuario,
                             k00_histtxt,
                             k00_limithist,
                             k00_idhist
                           ) values (
                             iNumpreRecibo,
                             1,
                             502,
                             datausu,
                             '00:00',
                             1,
                             'Recibo avulso referente a baixa do recibo avulso ja pago - Numpre : '||rRecordDisbanco.k00_numpre,
                             null,
                             nextval('arrehist_k00_idhist_seq')
                          );

      insert into arreproc ( k80_numpre,
                             k80_codproc )  select iNumpreRecibo,
                                                   arreproc.k80_codproc
                                              from arreproc
                                             where arreproc.k80_numpre = rRecordDisbanco.k00_numpre;

      insert into arrenumcgm ( k00_numpre,
                               k00_numcgm ) select iNumpreRecibo,
                                                   arrenumcgm.k00_numcgm
                                              from arrenumcgm
                                             where arrenumcgm.k00_numpre = rRecordDisbanco.k00_numpre;

      insert into arrematric ( k00_numpre,
                               k00_matric ) select iNumpreRecibo,
                                                   arrematric.k00_matric
                                              from arrematric
                                             where arrematric.k00_numpre = rRecordDisbanco.k00_numpre;

      insert into arreinscr ( k00_numpre,
                              k00_inscr )   select iNumpreRecibo,
                                                   arreinscr.k00_inscr
                                              from arreinscr
                                             where arreinscr.k00_numpre = rRecordDisbanco.k00_numpre;

      if lRaise then
        perform fc_debug('  <PgtoParcial>  - 1 - Alterando numpre disbanco ! novo numpre : '||iNumpreRecibo,lRaise,false,false);
      end if;

      update disbanco
         set k00_numpre = iNumpreRecibo
       where idret      = rRecordDisbanco.idret;

    end loop; --Fim do loop de validação da regra 1 para recibo avulso


    /*********************************************************************************
     *  GERA RECIBO PARA CARNE
     ********************************************************************************/
    -- Verifica se o pagamento eh referente a um Carne
    -- Caso seja entao eh gerado um recibopaga para os debitos
    -- do arrecad e acertado o numpre na tabela disbanco
    if lRaise then
      perform fc_debug('  <PgtoParcial> Regra 2 - GERA RECIBO PARA CARNE!',lRaise,false,false);
    end if;

    for rRecordDisbanco in select disbanco.idret,
                                  disbanco.dtpago,
                                  disbanco.k00_numpre,
                                  disbanco.k00_numpar,
                                  ( select k00_dtvenc
                                      from (select k00_dtvenc
                                              from arrecad
                                             where arrecad.k00_numpre = disbanco.k00_numpre
                                              and case
                                                    when disbanco.k00_numpar = 0 then true
                                                    else arrecad.k00_numpar = disbanco.k00_numpar
                                                  end
                                           union
                                            select k00_dtvenc
                                              from arrecant
                                             where arrecant.k00_numpre = disbanco.k00_numpre
                                               and case
                                                     when disbanco.k00_numpar = 0 then true
                                                     else arrecant.k00_numpar = disbanco.k00_numpar
                                                   end
                                           union
                                            select k00_dtvenc
                                              from arreold
                                             where arreold.k00_numpre = disbanco.k00_numpre
                                               and case
                                                     when disbanco.k00_numpar = 0 then true
                                                     else arreold.k00_numpar = disbanco.k00_numpar
                                                   end
                                            ) as x limit 1
                                  ) as data_vencimento_debito
                            from disbanco
                            where disbanco.codret = cod_ret
                              and disbanco.classi is false
                              and disbanco.instit = iInstitSessao
                              and case when iNumprePagamentoParcial = 0
                                       then true
                                       else disbanco.k00_numpre > iNumprePagamentoParcial
                                   end
                              and exists ( select 1
                                             from arrecad
                                            where arrecad.k00_numpre = disbanco.k00_numpre
                                              and case
                                                    when disbanco.k00_numpar = 0 then true
                                                    else arrecad.k00_numpar  = disbanco.k00_numpar
                                                  end
                                            union all
                                           select 1
                                             from arrecant
                                            where arrecant.k00_numpre = disbanco.k00_numpre
                                              and case
                                                    when disbanco.k00_numpar = 0 then true
                                                    else arrecant.k00_numpar = disbanco.k00_numpar
                                                  end
                                           union all
                                           select 1
                                             from arreold
                                            where arreold.k00_numpre = disbanco.k00_numpre
                                              and case
                                                    when disbanco.k00_numpar = 0 then true
                                                    else arreold.k00_numpar = disbanco.k00_numpar
                                                  end
                                            limit 1 )
                              and not exists ( select 1
                                                 from issvar
                                                where issvar.q05_numpre = disbanco.k00_numpre
                                                  and issvar.q05_numpar = disbanco.k00_numpar
                                                limit 1 )
                              and not exists ( select 1
                                                 from tmpnaoprocessar
                                                where tmpnaoprocessar.idret = disbanco.idret )
                         order by disbanco.idret

    loop

      select nextval('numpref_k03_numpre_seq')
        into iNumpreRecibo;

      if lRaise is true then
        perform fc_debug('  <PgtoParcial>  - Processando geracao de recibo para - Numpre: '||rRecordDisbanco.k00_numpre||'  Numpar: '||rRecordDisbanco.k00_numpar,lRaise,false,false);
      end if;

      select distinct
             arrecad.k00_tipo
        into rRecord
        from arrecad
       where arrecad.k00_numpre = rRecordDisbanco.k00_numpre
         and case
               when rRecordDisbanco.k00_numpar = 0
                 then true
               else arrecad.k00_numpar = rRecordDisbanco.k00_numpar
             end
       limit 1;

      if found then

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - Encontrou no arrecad - Gerando Recibo para o debito - Numpre: '||rRecordDisbanco.k00_numpre||'  Numpar: '||rRecordDisbanco.k00_numpar,lRaise,false,false);
        end if;

        select k00_codbco, k00_codage, fc_numbco(k00_codbco,k00_codage) as fc_numbco
          into rRecordBanco
          from arretipo
         where k00_tipo = rRecord.k00_tipo;

        insert into db_reciboweb ( k99_numpre,
                                   k99_numpar,
                                   k99_numpre_n,
                                   k99_codbco,
                                   k99_codage,
                                   k99_numbco,
                                   k99_desconto,
                                   k99_tipo,
                                   k99_origem
                                 ) values (
                                   rRecordDisbanco.k00_numpre,
                                   rRecordDisbanco.k00_numpar,
                                   iNumpreRecibo,
                                   coalesce(rRecordBanco.k00_codbco,0),
                                   coalesce(rRecordBanco.k00_codage,'0'),
                                   rRecordBanco.fc_numbco,
                                   0,
                                   2,
                                   1 );

        dDataCalculoRecibo := rRecordDisbanco.data_vencimento_debito;

        /**
         * Trabalhar este if
         */
        select ru.k00_dtvenc
          into dDataReciboUnica
          from recibounica ru
         where ru.k00_numpre = rRecordDisbanco.k00_numpre
           and rRecordDisbanco.k00_numpar = 0
           and ru.k00_dtvenc >= rRecordDisbanco.dtpago
         order by k00_dtvenc
         limit 1;

        perform fc_debug('  <PgtoParcial>  - dDataReciboUnica:' || dDataReciboUnica, lRaise);

        if found then
          dDataCalculoRecibo := dDataReciboUnica;
        end if;

        perform fc_debug('  <PgtoParcial>  - dDataCalculoRecibo:' || dDataCalculoRecibo, lRaise);

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - ');
          perform fc_debug('  <PgtoParcial>  ---------------- Validando datas de vencimento ----------------');
          perform fc_debug('  <PgtoParcial>  - Opções:');
          perform fc_debug('  <PgtoParcial>  - 1 - Próximo dia util do vencimento do arrecad : ' || fc_proximo_dia_util(dDataCalculoRecibo));
          perform fc_debug('  <PgtoParcial>  - 2 - Data do Pagamento Bancário                : ' || rRecordDisbanco.dtpago);
          perform fc_debug('  <PgtoParcial>  ---------------------------------------------------------------');
          perform fc_debug('  <PgtoParcial>  - ');
          perform fc_debug('  <PgtoParcial>  - Opção Default : "1" ');
        end if;

        if rRecordDisbanco.dtpago > fc_proximo_dia_util(dDataCalculoRecibo)  then -- Paguei Depois do Vencimento

          dDataCalculoRecibo := rRecordDisbanco.dtpago;

          if lRaise is true then
            perform fc_debug('  <PgtoParcial>  - Alterando para Opção de Vencimento "2" ');
          end if;
        end if;

        if lRaise is true then

          perform fc_debug('  <PgtoParcial>');
          perform fc_debug('  <PgtoParcial>  - Rodando FC_RECIBO'    );
          perform fc_debug('  <PgtoParcial>  --- iNumpreRecibo      : ' || iNumpreRecibo      );
          perform fc_debug('  <PgtoParcial>  --- dDataCalculoRecibo : ' || dDataCalculoRecibo );
          perform fc_debug('  <PgtoParcial>  --- iAnoSessao         : ' || iAnoSessao         );
          perform fc_debug('  <PgtoParcial>');
        end if;

        select * from fc_recibo(iNumpreRecibo,dDataCalculoRecibo,dDataCalculoRecibo,iAnoSessao)
          into rRecibo;

        if rRecibo.rlerro is true then

          codigo_retorno := 5;
          descricao := rRecibo.rvmensagem||' Erro ao processar idret '||rRecordDisbanco.idret||'.';
          return;
        end if;

      else

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - Nao encontrou no arrecad - Gerando Recibo para o debito - Numpre: '||rRecordDisbanco.k00_numpre||'  Numpar: '||rRecordDisbanco.k00_numpar,lRaise,false,false);
        end if;

        select distinct
               arrecant.k00_tipo
          into rRecord
          from arrecant
         where arrecant.k00_numpre = rRecordDisbanco.k00_numpre
         union
        select distinct
               arreold.k00_tipo
          from arreold
         where arreold.k00_numpre = rRecordDisbanco.k00_numpre
         limit 1;

        select k00_codbco,
               k00_codage,
               fc_numbco(k00_codbco,k00_codage) as fc_numbco
          into rRecordBanco
          from arretipo
         where k00_tipo = rRecord.k00_tipo;

        insert into db_reciboweb ( k99_numpre,
                                   k99_numpar,
                                   k99_numpre_n,
                                   k99_codbco,
                                   k99_codage,
                                   k99_numbco,
                                   k99_desconto,
                                   k99_tipo,
                                   k99_origem
                                 ) values (
                                   rRecordDisbanco.k00_numpre,
                                   rRecordDisbanco.k00_numpar,
                                   iNumpreRecibo,
                                   coalesce(rRecordBanco.k00_codbco,0),
                                   coalesce(rRecordBanco.k00_codage,'0'),
                                   rRecordBanco.fc_numbco,
                                   0,
                                   2,
                                   1 );

        if lRaise is true then
          perform fc_debug('<PgtoParcial>  - Lançou recibo caso sejá carne ',lRaise,false,false);
        end if;

        insert into recibopaga ( k00_numcgm,
                                 k00_dtoper,
                                 k00_receit,
                                 k00_hist,
                                 k00_valor,
                                 k00_dtvenc,
                                 k00_numpre,
                                 k00_numpar,
                                 k00_numtot,
                                 k00_numdig,
                                 k00_conta,
                                 k00_dtpaga,
                                 k00_numnov )
                          select k00_numcgm,
                                 k00_dtoper,
                                 k00_receit,
                                 k00_hist,
                                 k00_valor,
                                 k00_dtvenc,
                                 k00_numpre,
                                 k00_numpar,
                                 k00_numtot,
                                 k00_numdig,
                                 0,
                                 datausu,
                                 iNumpreRecibo
                            from arrecant
                           where arrecant.k00_numpre = rRecordDisbanco.k00_numpre
                             and case
                                   when rRecordDisbanco.k00_numpar = 0 then true
                                     else rRecordDisbanco.k00_numpar = arrecant.k00_numpar
                                 end
                           union
                          select k00_numcgm,
                                 k00_dtoper,
                                 k00_receit,
                                 k00_hist,
                                 k00_valor,
                                 k00_dtvenc,
                                 k00_numpre,
                                 k00_numpar,
                                 k00_numtot,
                                 k00_numdig,
                                 0,
                                 datausu,
                                 iNumpreRecibo
                            from arreold
                           where arreold.k00_numpre = rRecordDisbanco.k00_numpre
                             and case
                                   when rRecordDisbanco.k00_numpar = 0 then true
                                     else rRecordDisbanco.k00_numpar = arreold.k00_numpar
                                 end;

      end if;

      if rRecordDisbanco.k00_numpar = 0 then
        insert into tmplista_unica values (rRecordDisbanco.idret);
      end if;

      -- Acerta o conteudo da disbanco, alterando o numpre do carne pelo da recibopaga
      if lRaise then
        perform fc_debug('  <PgtoParcial>  - Acertando numpre do recibo gerado para o carne (arreold ou arrecant) numnov : '||iNumpreRecibo,lRaise,false,false);
      end if;

      if lRaise then
        perform fc_debug('  <PgtoParcial>  - 2 - Alterando numpre disbanco ! novo numpre : '||iNumpreRecibo,lRaise,false,false);
      end if;

      update disbanco
         set k00_numpre = iNumpreRecibo,
             k00_numpar = 0
       where idret = rRecordDisbanco.idret;

      update tmpdisbanco_inicio_original
         set k00_numpre = iNumpreRecibo
       where idret = rRecordDisbanco.idret;

    end loop;

    if lRaise then
      perform fc_debug('  <PgtoParcial>  - Final processamento para geracao recibo para carne, '||clock_timestamp(),lRaise,false,false);
    end if;

    /*******************************************************************************************************************
     *  NÃO PROCESSA PAGAMENTOS DUPLICADOS EM RECIBOS DIFERENTES
     ******************************************************************************************************************/
    if lRaise then
      perform fc_debug('  <PgtoParcial> Regra 4 - NAO PROCESSA PAGAMENTOS DUPLICADOS EM RECIBOS DIFERENTES!',lRaise,false,false);
    end if;
    for r_idret in

        select x.k00_numpre,
               x.k00_numpar,
               count(x.idret) as ocorrencias
          from ( select distinct
                        recibopaga.k00_numpre,
                        recibopaga.k00_numpar,
                        disbanco.idret
                   from disbanco
                        inner join recibopaga on recibopaga.k00_numnov = disbanco.k00_numpre
                  where disbanco.codret = cod_ret
                    and disbanco.classi is false
                    and case when iNumprePagamentoParcial = 0
                             then true
                             else disbanco.k00_numpre > iNumprePagamentoParcial
                         end
                    and disbanco.instit = iInstitSessao ) as x
                left  join numprebloqpag  on numprebloqpag.ar22_numpre = x.k00_numpre
                                         and numprebloqpag.ar22_numpar = x.k00_numpar
         where numprebloqpag.ar22_numpre is null
             and not exists ( select 1
                                from tmpnaoprocessar
                               where tmpnaoprocessar.idret = x.idret )
         group by x.k00_numpre,
                  x.k00_numpar
           having count(x.idret) > 1

    loop

      if lRaise is true then
        perform fc_debug('  <PgtoParcial>  - ######## Incluido na tmpnaoprocessar',lRaise,false,false);
      end if;

      for iRows in 1..( r_idret.ocorrencias - 1 ) loop

          perform fc_debug('  <PgtoParcial>  - Inserindo na tmpnaoprocessar - Pagamento duplicado em recibos diferentes', lRaise);
          perform fc_debug('  <PgtoParcial>  - ######## Incluido na tmpnaoprocessar numpre: ' || r_idret.k00_numpre,      lRaise);
          perform fc_debug('  <PgtoParcial>                                         numpar: ' || r_idret.k00_numpar,      lRaise);

          -- @todo - verificar esta logica, a principio parece estar inserindo aqui o mesmo recibo
          -- em arquivos (codret) diferentes
          insert into tmpnaoprocessar select coalesce(max(disbanco.idret),0)
                                        from disbanco
                                       where disbanco.codret = cod_ret
                                         and case when iNumprePagamentoParcial = 0
                                                  then true
                                                  else disbanco.k00_numpre > iNumprePagamentoParcial
                                              end
                                         and disbanco.classi is false
                                         and disbanco.instit = iInstitSessao
                                         and disbanco.k00_numpre in ( select recibopaga.k00_numnov
                                                                        from recibopaga
                                                                       where recibopaga.k00_numpre = r_idret.k00_numpre
                                                                         and recibopaga.k00_numpar = r_idret.k00_numpar )
                                         and not exists ( select 1
                                                            from tmpnaoprocessar
                                                           where tmpnaoprocessar.idret = disbanco.idret );

      end loop;

    end loop;


    /*********************************************************************************************************************
     *  EFETUA AJUSTE NOS RECIBOS QUE TENHAM ALGUMA PARCELA DE SUA ORIGEM, PAGA/CANCELADA/IMPORTADA PARA DIVIDA/PARCELADA
     *********************************************************************************************************************/
    --
    -- Processa somente os recibos que tenham todos debitos em aberto ou todos pagos
    if lRaise then
      perform fc_debug('  <PgtoParcial> Regra 5 - EFETUA AJUSTE NOS RECIBOS QUE TENHAM ALGUMA PARCELA DE SUA ORIGEM',lRaise,false,false);
      perform fc_debug('  <PgtoParcial>           PAGA/CANCELADA/IMPORTADA PARA DIVIDA/PARCELADA!',lRaise,false,false);
    end if;

    for r_idret in
        select disbanco.idret,
               disbanco.k00_numpre as numpre,
               r.k00_numpre,
               r.k00_numpar,
               (select count(*)
                  from (select distinct
                               recibopaga.k00_numpre,
                               recibopaga.k00_numpar
                          from recibopaga
                               inner join arrecad on arrecad.k00_numpre = recibopaga.k00_numpre
                                                 and arrecad.k00_numpar = recibopaga.k00_numpar
                         where recibopaga.k00_numnov = disbanco.k00_numpre ) as x
               ) as qtd_aberto,
               (select count(*)
                  from (select distinct
                               k00_numpre,
                               k00_numpar
                          from recibopaga
                          where recibopaga.k00_numnov = disbanco.k00_numpre ) as x
               ) as qtd_recibo,
               exists ( select 1
                          from arrecad a
                         where a.k00_numpre = r.k00_numpre
                           and a.k00_numpar = r.k00_numpar ) as arrecad,
               exists ( select 1
                          from arrecant a
                         where a.k00_numpre = r.k00_numpre
                           and a.k00_numpar = r.k00_numpar ) as arrecant,
               exists ( select 1
                          from arreold a
                         where a.k00_numpre = r.k00_numpre
                           and a.k00_numpar = r.k00_numpar ) as arreold
          from disbanco
               inner join recibopaga r   on r.k00_numnov              = disbanco.k00_numpre
               left  join numprebloqpag  on numprebloqpag.ar22_numpre = disbanco.k00_numpre
                                        and numprebloqpag.ar22_numpar = disbanco.k00_numpar
         where disbanco.codret = cod_ret
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
           and numprebloqpag.ar22_numpre is null
           and case when iNumprePagamentoParcial = 0
                    then true
                    else disbanco.k00_numpre > iNumprePagamentoParcial
                end
           and not exists ( select 1
                              from tmpnaoprocessar
                             where tmpnaoprocessar.idret = disbanco.idret )
           order by disbanco.codret,
                  disbanco.idret,
                  disbanco.k00_numpre,
                  r.k00_numpre,
                  r.k00_numpar
    loop

      if lRaise is true then
        perform fc_debug('<PgtoParcial> Processando idret '||r_idret.idret||' Numpre: '||r_idret.numpre||'...',lRaise,false,false);
      end if;

      -- @todo - verificar esta logica com muita calma, acredito nao ser aqui o melhor lugar...
      if ( r_idret.qtd_aberto = r_idret.qtd_recibo ) or r_idret.qtd_aberto = 0 then
        if lRaise is true then
          perform fc_debug('<PgtoParcial> Continuando 1 ( qtd_aberto = qtd_recibo OU qtd_aberto = 0 )...',lRaise,false,false);
        end if;
        continue;
      end if;

      if r_idret.arrecad then
        perform 1 from arrecad where k00_numpre = r_idret.k00_numpre and k00_tipo = 3;
        if found then
          if lRaise is true then
        perform fc_debug('<PgtoParcial> Continuando 2 ( nao encontrou numpre na arrecad )...',lRaise,false,false);
      end if;
          continue;
        end if;
      elsif r_idret.arrecant then
        perform 1 from arrecant where k00_numpre = r_idret.k00_numpre and k00_tipo = 3;
        if found then
          if lRaise is true then
        perform fc_debug('<PgtoParcial> Continuando 3 ( nao encontrou numpre na arrecant )...',lRaise,false,false);
      end if;
          continue;
        end if;
      elsif r_idret.arreold then
        perform 1 from arreold where k00_numpre = r_idret.k00_numpre and k00_tipo = 3;
        if found then
          if lRaise is true then
             perform fc_debug('<PgtoParcial> Continuando 4 ( nao encontrou numpre na arreold )...',lRaise,false,false);
          end if;
          continue;
        end if;
      end if;

      --
      -- Se nao encontrar o numpre e numpar em nenhuma das tabelas : arrecad,arrecant,arreold
      --   insere em tmpnaoprocessar para nao processar do loop principal do processamento
      --
      if r_idret.arrecad is false and r_idret.arrecant is false and r_idret.arreold is false then
        perform 1 from tmpnaoprocessar where idret = r_idret.idret;
        if not found then

          if lRaise is true then
             perform fc_debug('<PgtoParcial> Inserindo idret '||r_idret.idret||' em tmpnaoprocessar...',lRaise,false,false);
          end if;
          insert into tmpnaoprocessar values (r_idret.idret);
        end if;
      elsif r_idret.arrecad is false then
        --
        --  Caso nao encontrar no arrecad deleta o numpre e numpar
        --    da recibopaga para ajustar o recibo, pressupondo que tenha sido pago ou cancelado
        --    uma parcela do recibo. Este ajuste no recibo Ã© necessario para que o sistema encontre
        --    a diferenca entre o valor pago e o valor do recibo, gerando assim um credito com o valor
        --    da diferenca
        --

        if lRaise then
          perform fc_debug('  <PgtoParcial>  - Quantidade em aberto : '||r_idret.qtd_aberto||' Quantidade no recibo : '||r_idret.qtd_recibo                             ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Deletando da recibopaga -- numnov : '||r_idret.numpre||' numpre : '||r_idret.k00_numpre||' numpar : '||r_idret.k00_numpar,lRaise,false,false);
        end if;

        --
        -- Verificamos se o numnov que esta prestes a ser deletado poussui vinculo com alguma partilha
        -- Caso encontrado vinculo, o recibo nao e exclui­do e sera retornado erro no processamento
        --
        perform v77_processoforopartilha
           from processoforopartilhacusta
          where v77_numnov in (select k00_numnov
                                from recibopaga
                               where k00_numnov = r_idret.numpre
                                 and k00_numpre = r_idret.k00_numpre
                                 and k00_numpar = r_idret.k00_numpar);
        if found then
          raise exception   'Erro ao realizar exclusao de recibo da CGF (recibopaga) Numnov: % Numpre: % Numpar: % possuem vinculo com geracao de partilha de custas para processo do foro', r_idret.numpre, r_idret.k00_numpre, r_idret.k00_numpar;
        end if;

        delete from recibopaga
         where k00_numnov = r_idret.numpre
           and k00_numpre = r_idret.k00_numpre
           and k00_numpar = r_idret.k00_numpar;

      end if;

    end loop;

    /*******************************************************************************************************************
     *  GERA RECIBO PARA ISSQN VARIAVEL
     ******************************************************************************************************************/
    if lRaise then
      perform fc_debug('  <PgtoParcial> Regra 6 - GERA RECIBO PARA ISSQN VARIAVEL',lRaise,false,false);
    end if;

    -- Verifica se existe algum  referente a ISSQN Variavel que ja esteja quitado e o valor seja 0 (zero)
    -- Nesse caso sera gerado ARRECAD / ISSVAR / RECIBO para o  encontrado e acertado o numpre na tabela disbanco
    --
    -- Alterado o sql para buscar dados da disbanco de issqn variável que estão na recibopaga, jah realizava antes da alteracao,
    -- e buscar dados da disbanco de issqn variavel que nao tiveram seu pagamento por recibo, lógica nova.
    --
    for rRecordDisbanco in select distinct
                                  disbanco.*,
                                  issvar_carne.q05_numpre as issvar_carne_numpre,
                                  issvar_carne.q05_numpar as issvar_carne_numpar
                             from disbanco
                                  left join recibopaga                        on recibopaga.k00_numnov            = disbanco.k00_numpre
                                  left join arrecant                          on arrecant.k00_numpre              = recibopaga.k00_numpre
                                                                             and arrecant.k00_numpar              = recibopaga.k00_numpar
                                                                             and arrecant.k00_receit              = recibopaga.k00_receit
                                  left join arreold                           on arreold.k00_numpre               = recibopaga.k00_numpre
                                                                             and arreold.k00_numpar               = recibopaga.k00_numpar
                                                                             and arreold.k00_receit               = recibopaga.k00_receit
                                  left join issvar as issvar_recibo           on issvar_recibo.q05_numpre         = arrecant.k00_numpre
                                                                             and issvar_recibo.q05_numpar         = arrecant.k00_numpar
                                  left join issvar as issvar_recibo_old       on issvar_recibo_old.q05_numpre     = arreold.k00_numpre
                                                                             and issvar_recibo_old.q05_numpar     = arreold.k00_numpar
                                  left join issvar as issvar_carne            on issvar_carne.q05_numpre          = disbanco.k00_numpre
                                                                             and issvar_carne.q05_numpar          = disbanco.k00_numpar
                                  left join arrecant as arrecant_issvar_carne on arrecant_issvar_carne.k00_numpre = disbanco.k00_numpre
                                                                             and arrecant_issvar_carne.k00_numpar = disbanco.k00_numpar
                                  left join arreold as arreold_issvar_carne   on arreold_issvar_carne.k00_numpre  = disbanco.k00_numpre
                                                                             and arreold_issvar_carne.k00_numpar  = disbanco.k00_numpar
                            where disbanco.classi is false
                              and disbanco.codret = cod_ret
                              and disbanco.instit = iInstitSessao
                              and (
                                    --deve Estar na arrecant              OU Estar na arreold
                                    (issvar_recibo.q05_numpre is not null or issvar_recibo_old.q05_numpre is not null )
                                    -- OU
                                    or (
                                          -- Estar na disbanco com o numpre da issvar
                                          issvar_carne.q05_numpre is not null
                                          --E  Estar na arrecant                            OU Estar na arreold
                                          and (arrecant_issvar_carne.k00_numpre is not null or arreold_issvar_carne.k00_numpre is not null)
                                        )
                                  )
                              and case when iNumprePagamentoParcial = 0
                                       then true
                                       else disbanco.k00_numpre > iNumprePagamentoParcial
                                   end
                              and not exists ( select 1
                                                 from tmpnaoprocessar
                                                where tmpnaoprocessar.idret = disbanco.idret )
                         order by disbanco.idret
    loop

      if lRaise is true then
          perform fc_debug('  <PgtoParcial> ',lRaise,false,false);
          perform fc_debug('  <PgtoParcial> ',lRaise,false,false);
          perform fc_debug('  <PgtoParcial> PROCESSANDO IDRET '||rRecordDisbanco.idret||'...',lRaise,false,false);
          perform fc_debug('  <PgtoParcial>                                                 ',lRaise,false,false);
          perform fc_debug('  <PgtoParcial> Gerando recibos                                 ',lRaise,false,false);
      end if;

      --
      -- Alterado o sql para atender aos casos em que foi pago um issqn variavel por carnê ao invés de recibo
      --
      select distinct
             case
               when recibopaga.k00_numnov is not null and round(sum(recibopaga.k00_valor),2) > 0.00 then
                 round(sum(recibopaga.k00_valor),2)
               else
                 vlrpago
             end
        into nVlrTotalRecibopaga
        from disbanco
             left join recibopaga on recibopaga.k00_numnov = disbanco.k00_numpre
       where disbanco.idret  = rRecordDisbanco.idret
         and disbanco.instit = iInstitSessao
       group by recibopaga.k00_numnov, disbanco.vlrpago ;

      if lRaise is true then

        perform fc_debug('  <PgtoParcial> Numpre Disbanco .........: '||rRecordDisbanco.k00_numpre                                                            ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial> Numpre IssVar ...........: '||rRecordDisbanco.issvar_carne_numpre||' Parcela: '||rRecordDisbanco.issvar_carne_numpar,lRaise,false,false);
        perform fc_debug('  <PgtoParcial> Valor Pago na Disbanco (recibopaga) ..: '||nVlrTotalRecibopaga                                                                   ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial> ',lRaise,false,false);
      end if;

      nVlrTotalInformado := 0;

      for rRecord in select distinct tipo,
                                        k00_numpre,
                                        k00_numpar,
                                        case
                                          when k00_valor = 0 then rRecordDisbanco.vlrpago
                                          else k00_valor
                                        end as k00_valor
                       from ( select distinct
                                     1 as tipo,
                                     recibopaga.k00_numpre,
                                     recibopaga.k00_numpar,
                                     round(sum(recibopaga.k00_valor),2) as k00_valor
                                from recibopaga
                                     left join arrecant  c on c.k00_numpre = recibopaga.k00_numpre
                                                          and c.k00_numpar  = recibopaga.k00_numpar
                               where recibopaga.k00_numnov = rRecordDisbanco.k00_numpre
                               group by recibopaga.k00_numpre,
                                        recibopaga.k00_numpar
                               union
                              select 2 as tipo,
                                     rRecordDisbanco.issvar_carne_numpre as k00_numpre,
                                     rRecordDisbanco.issvar_carne_numpar as k00_numpar,
                                     rRecordDisbanco.vlrpago             as k00_valor
                               where rRecordDisbanco.issvar_carne_numpre is not null
                             ) as dados
                  order by k00_numpre, k00_numpar

      loop

        if lRaise is true then

          perform fc_debug('  <PgtoParcial> '                                                                                                          ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial> Calculando valor informado...'                                                                             ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial> Valor pago na Disbanco ...:'||rRecordDisbanco.vlrpago                                                      ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial> Valor do debito ..........:'||rRecord.k00_valor                                                            ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial> Valor do debito encontrado na tabela '||(case when rRecord.tipo = 1 then 'Recibopaga' else 'Disbanco' end ),lRaise,false,false);
          perform fc_debug('  <PgtoParcial> Valor pago na disbanco ...:'||nVlrTotalRecibopaga                                                          ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial> Calculo ..................: ( Valor pago na Disbanco * ((( Valor do debito * 100 ) / Valor pago na disbanco ) / 100 ))',lRaise,false,false);
          perform fc_debug('  <PgtoParcial> Valor Informado ..........: ( '||coalesce(rRecordDisbanco.vlrpago,0)||' * ((( '||coalesce(rRecord.k00_valor,0)||' * 100 ) / '||coalesce(nVlrTotalRecibopaga,0)||' ) / 100 )) = '||( coalesce(rRecordDisbanco.vlrpago,0) * ((( coalesce(rRecord.k00_valor,0) * 100 ) / coalesce(nVlrTotalRecibopaga,0) ) / 100 )) ,lRaise,false,false);
        end if;

        nVlrInformado := ( rRecordDisbanco.vlrpago * ((( rRecord.k00_valor * 100 ) / nVlrTotalRecibopaga ) / 100 ));

        if rRecord.k00_numpre != iNumpreAnterior then

          -- Gera Numpre do ISSQN Variavel
          select nextval('numpref_k03_numpre_seq')
            into iNumpreIssVar;

          -- Gera Numpre do Recibo
          select nextval('numpref_k03_numpre_seq')
            into iNumpreRecibo;

          iNumpreAnterior    := rRecord.k00_numpre;

          insert into arreinscr select distinct
                                       iNumpreIssVar,
                                       arreinscr.k00_inscr,
                                       arreinscr.k00_perc
                                  from arreinscr
                                 where arreinscr.k00_numpre = rRecord.k00_numpre;

        end if;
        --
        -- Apenas excluimos o recibo quando o pagamento for por recibo (tipo = 1)
        --
        if rRecord.tipo = 1 then

          delete
            from recibopaga
           where k00_numnov = rRecordDisbanco.k00_numpre
             and k00_numpre = rRecord.k00_numpre
             and k00_numpar = rRecord.k00_numpar;
        end if;

        if lRaise is true then
          perform fc_debug('  <PgtoParcial> Incluindo registros do Numpre '||rRecord.k00_numpre||' Parcela '||rRecord.k00_numpar||' na tabela arrecad como iss complementar com o novo numpre '||iNumpreIssVar,lRaise,false,false);
        end if;

        /*
         * Alterada a lógica para inclusão no arrecad.
         *
         * Ao invés de utilizar a data de operação e vencimento original do débito, esta sendo utilizada a data de processamento da baixa de banco
         * Isto devido a geração de correção, juro e multa indevidos para o débito pois esses valores ja estão embutidos no valor total pago na disbanco.
         *
         * Verifica se deve buscar os dados da arrecant (Caso o débito ja tenha sido pago)
         *                                  ou arreold (Caso o débito tenha sido importado para divida)
         */
        perform 1
           from arrecant
          where k00_numpre = rRecord.k00_numpre
            and k00_numpar = rRecord.k00_numpar;

        if not found then

          perform fc_debug('  <PgtoParcial> Gerando arrecad utilizando arreold para iss complementar', lRaise);
          insert into arrecad ( k00_numpre,
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
                              ) select iNumpreIssVar,
                                       arreold.k00_numpar,
                                       arreold.k00_numcgm,
                                       datausu,
                                       arreold.k00_receit,
                                       arreold.k00_hist,
                                       ( case
                                           when rRecord.tipo = 1
                                             then 0
                                           else rRecordDisbanco.vlrpago
                                         end ),
                                       datausu,
                                       1,
                                       arreold.k00_numdig,
                                       arreold.k00_tipo,
                                       arreold.k00_tipojm
                                  from arreold
                                 where arreold.k00_numpre = rRecord.k00_numpre
                                   and arreold.k00_numpar = rRecord.k00_numpar;
        else

          perform fc_debug('  <PgtoParcial> Gerando arrecad utilizando arrecant para iss complementar', lRaise);
          insert into arrecad ( k00_numpre,
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
                              ) select iNumpreIssVar,
                                       arrecant.k00_numpar,
                                       arrecant.k00_numcgm,
                                       datausu,
                                       arrecant.k00_receit,
                                       arrecant.k00_hist,
                                       ( case
                                           when rRecord.tipo = 1
                                             then 0
                                           else rRecordDisbanco.vlrpago
                                         end ),
                                       datausu,
                                       1,
                                       arrecant.k00_numdig,
                                       arrecant.k00_tipo,
                                       arrecant.k00_tipojm
                                  from arrecant
                                 where arrecant.k00_numpre = rRecord.k00_numpre
                                   and arrecant.k00_numpar = rRecord.k00_numpar;
        end if;

        insert into issvar ( q05_codigo,
                             q05_numpre,
                             q05_numpar,
                             q05_valor,
                             q05_ano,
                             q05_mes,
                             q05_histor,
                             q05_aliq,
                             q05_bruto,
                             q05_vlrinf
                           ) select nextval('issvar_q05_codigo_seq'),
                                    iNumpreIssVar,
                                    issvar.q05_numpar,
                                    issvar.q05_valor,
                                    issvar.q05_ano,
                                    issvar.q05_mes,
                                    'ISSQN Complementar gerado automaticamente atraves da baixa de banco devido a quitacao ',
                                    issvar.q05_aliq,
                                    issvar.q05_bruto,
                                    nVlrInformado
                               from issvar
                              where q05_numpre = rRecord.k00_numpre
                                and q05_numpar = rRecord.k00_numpar;

        select k00_codbco,
               k00_codage,
               fc_numbco(k00_codbco,k00_codage) as fc_numbco
          into rRecordBanco
          from arretipo
         where k00_tipo = ( select k00_tipo
                              from arrecant
                             where arrecant.k00_numpre = rRecord.k00_numpre
                               and arrecant.k00_numpar = rRecord.k00_numpar
                             limit 1 );

        insert into db_reciboweb ( k99_numpre,
                                   k99_numpar,
                                   k99_numpre_n,
                                   k99_codbco,
                                   k99_codage,
                                   k99_numbco,
                                   k99_desconto,
                                   k99_tipo,
                                   k99_origem
                                 ) values (
                                   iNumpreIssVar,
                                   rRecord.k00_numpar,
                                   iNumpreRecibo,
                                   coalesce(rRecordBanco.k00_codbco,0),
                                   coalesce(rRecordBanco.k00_codage,'0'),
                                   rRecordBanco.fc_numbco,
                                   0,
                                   2,
                                   1
                                 );

         if lRaise is true then
           perform fc_debug('  <PgtoParcial>  - xxx - valor informado : '||nVlrInformado||' total : '||nVlrTotalInformado,lRaise,false,false);
         end if;

         nVlrTotalInformado := ( nVlrTotalInformado + nVlrInformado );

      end loop;

      if lRaise is true then
        perform fc_debug('  <PgtoParcial>  - 1 - valor antes disbanco : '||nVlrTotalInformado,lRaise,false,false);
      end if;

      if rRecordDisbanco.vlrpago != round(nVlrTotalInformado,2) then

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - Valor Pago na Disbanco diferente do Valor Total Informado... ',lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Valor Pago na Disbanco ....: '||rRecordDisbanco.vlrpago,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Valor Total Informado......: '||round(nVlrTotalInformado,2),lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - ',lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Alterando o valor informado da issvar ajustando com a diferenca encontrada ('||(rRecordDisbanco.vlrpago - round(nVlrTotalInformado,2))||')',lRaise,false,false);
        end if;

        update issvar
           set q05_vlrinf = q05_vlrinf + (rRecordDisbanco.vlrpago - round(nVlrTotalInformado,2))
         where q05_codigo = ( select max(q05_codigo)
                                from issvar
                               where q05_numpre = iNumpreIssVar );
      end if;

      if lRaise is true then
      perform fc_debug('  <PgtoParcial>  - 2 - valor antes disbanco : '||nVlrTotalInformado,lRaise,false,false);
      end if;

      -- Gera Recibopaga
      if lRaise is true then
      perform fc_debug('  <PgtoParcial>  - Gerando ReciboPaga',lRaise,false,false);
      end if;

      select * from fc_recibo(iNumpreRecibo,rRecordDisbanco.dtpago,rRecordDisbanco.dtpago,iAnoSessao)
        into rRecibo;

      if lRaise is true then
        perform fc_debug('  <PgtoParcial>  - Fim do Processamento da ReciboPaga',lRaise,false,false);
      end if;

      if rRecibo.rlerro is true then

        codigo_retorno := 6;
        descricao := rRecibo.rvmensagem||'.';
        return;
      end if;

      -- Acerta o conteudo da disbanco, alterando o numpre do ISSQN quitado pelo da recibopaga
      if lRaise then
        perform fc_debug('  <PgtoParcial>  - 3 - Alterando numpre disbanco ! novo numpre : '||iNumpreRecibo,lRaise,false,false);
      end if;

      update disbanco
         set vlrpago = round((vlrpago - nVlrTotalInformado),2),
             vlrtot  = round((vlrtot  - nVlrTotalInformado),2)
       where idret   = rRecordDisbanco.idret;

       /**
        * Comentando update da tmpantesprocessar pois gerava inconsistencia quando o debito
        * foi pago em duplicidade
        *
       update tmpantesprocessar
         set vlrpago = round((vlrpago - nVlrTotalInformado),2)
       where idret   = rRecordDisbanco.idret;*/

       perform * from recibopaga
         where k00_numnov = rRecordDisbanco.k00_numpre;

       if not found then

         update disbanco
            set k00_numpre = iNumpreRecibo,
                k00_numpar = 0,
                vlrpago    = round(nVlrTotalInformado,2),
                vlrtot     = round(nVlrTotalInformado,2)
          where idret      = rRecordDisbanco.idret;

          /*update tmpantesprocessar
             set vlrpago    = round(nVlrTotalInformado,2)
           where idret      = rRecordDisbanco.idret;*/

       else

         iSeqIdRet := nextval('disbanco_idret_seq');

         if lRaise is true then
           perform fc_debug('  <PgtoParcial>  - idret update : '||rRecordDisbanco.idret||' novo idret : '||iSeqIdRet||' valor antes disbanco : '||nVlrTotalInformado,lRaise,false,false);
         end if;

          insert into disbanco ( k00_numbco,
                                k15_codbco,
                                k15_codage,
                                codret,
                                dtarq,
                                dtpago,
                                vlrpago,
                                vlrjuros,
                                vlrmulta,
                                vlracres,
                                vlrdesco,
                                vlrtot,
                                cedente,
                                vlrcalc,
                                idret,
                                classi,
                                k00_numpre,
                                k00_numpar,
                                convenio,
                                instit )
                        select k00_numbco,
                               k15_codbco,
                               k15_codage,
                               codret,
                               dtarq,
                               dtpago,
                               round(nVlrTotalInformado,2),
                               0,
                               0,
                               0,
                               0,
                               round(nVlrTotalInformado,2),
                               cedente,
                               round(nVlrTotalInformado,2),
                               iSeqIdRet,
                               classi,
                               iNumpreRecibo,
                               0,
                               convenio,
                              instit
                         from disbanco
                        where disbanco.idret = rRecordDisbanco.idret;
           end if;

         if lRaise is true then
           perform fc_debug('  <PgtoParcial>  ',lRaise,false,false);
           perform fc_debug('  <PgtoParcial>  FIM DO PROCESSAMENTO DO IDRET '||rRecordDisbanco.idret,lRaise,false,false);
           perform fc_debug('  <PgtoParcial>  ',lRaise,false,false);
         end if;

    end loop;

    perform fc_debug('  <PgtoParcial>  - Inicio verificacao de recibo oriundo de integracao externa', lRaise);
    for rRecord in select disbanco.k00_numpre, disbanco.k00_numpar, disbanco.idret,
                          vlrpago::numeric(15,2)   as valorpagodisbanco,
                          q05_valor::numeric(15,2) as valorlancadoissvar
                     from disbanco
                          inner join db_reciboweb on k99_numpre = disbanco.k00_numpre
                                                 and k99_numpar = disbanco.k00_numpar
                                                 and k99_tipo   = 9
                                                 and k99_origem = 3
                          inner join issvar       on k99_numpre = q05_numpre
                                                 and k99_numpar = q05_numpar
                          inner join arrecad      on arrecad.k00_numpre = disbanco.k00_numpre
                                                 and arrecad.k00_numpar = disbanco.k00_numpar
                    where codret = cod_ret
                      and classi is false
                      and instit = iInstitSessao
    loop

      perform fc_debug('  <PgtoParcial>  - Numpre Iss:                   ' || rRecord.k00_numpre,         lRaise);
      perform fc_debug('  <PgtoParcial>  - Valor encontrado em aberto:   ' || rRecord.valorlancadoissvar, lRaise);
      perform fc_debug('  <PgtoParcial>  - Valor pago:                   ' || rRecord.valorpagodisbanco,  lRaise);
      perform fc_debug('  <PgtoParcial>  - Tolerancia Pagamento Parcial: ' || nVlrToleranciaPgtoParcial,  lRaise);

      nVlrDiferencaPgto := ( rRecord.valorlancadoissvar - rRecord.valorpagodisbanco )::numeric(15,2);

      /**
       * Pagamento parcial para recibos oriundos de integracao externa deve ser gerado inconsistencia
       */
      if nVlrDiferencaPgto > 0 and nVlrDiferencaPgto > nVlrToleranciaPgtoParcial then

        perform fc_debug('  <PgtoParcial>  - Gerando inconsistencia para o idret: ' || rRecord.idret ||'. Pagamento Parcial oriundo de integracao externa.', lRaise);
        insert into tmpnaoprocessar values (rRecord.idret);
      end if;
    end loop;

    perform fc_debug('  <PgtoParcial>  - Fim verificacao de recibo oriundo de integracao externa', lRaise);

    /*******************************************************************************************************************
     *  GERA ABATIMENTOS
     ******************************************************************************************************************/
    --
    -- Verifica se existe abatimentos sendo eles ( PAGAMENTO PARCIAL, CREDITO E DESCONTO )
    --

    if lRaise is true then
      perform fc_debug('  <PgtoParcial> Regra 7 - GERA ABATIMENTO ', lRaise,false,false);
    end if;

    for r_idret in

        select distinct
               disbanco.k00_numpre as numpre,
               disbanco.k00_numpar as numpar,
               disbanco.idret,
               disbanco.k15_codbco,
               disbanco.k15_codage,
               disbanco.k00_numbco,
               disbanco.vlrpago,
               disbanco.vlracres,
               disbanco.vlrdesco,
               disbanco.vlrjuros,
               disbanco.vlrmulta,
               disbanco.dtpago,
               round(sum(recibopaga.k00_valor),2) as k00_valor,
               recibopaga.k00_dtpaga,
               disbanco.instit
          from disbanco
               inner join recibopaga     on disbanco.k00_numpre       = recibopaga.k00_numnov
               left  join numprebloqpag  on numprebloqpag.ar22_numpre = disbanco.k00_numpre
                                        and numprebloqpag.ar22_numpar = disbanco.k00_numpar
         where disbanco.codret = cod_ret
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
           and numprebloqpag.ar22_numpre is null
           and case when iNumprePagamentoParcial = 0
                    then true
                    else disbanco.k00_numpre > iNumprePagamentoParcial
                end
           and not exists ( select 1
                              from tmpnaoprocessar
                             where tmpnaoprocessar.idret = disbanco.idret )
           and exists ( select 1
                          from arrecad
                         where arrecad.k00_numpre = recibopaga.k00_numpre
                           and arrecad.k00_numpar = recibopaga.k00_numpar
                         union all
                        select 1
                          from arrecant
                         where arrecant.k00_numpre = recibopaga.k00_numpre
                           and arrecant.k00_numpar = recibopaga.k00_numpar
                         union all
                        select 1
                          from arreold
                         where arreold.k00_numpre = recibopaga.k00_numpre
                           and arreold.k00_numpar = recibopaga.k00_numpar
                         union all
                        select 1
                          from arreprescr
                         where arreprescr.k30_numpre = recibopaga.k00_numpre
                           and arreprescr.k30_numpar = recibopaga.k00_numpar
                          limit 1 )
      group by disbanco.k00_numpre,
               disbanco.k00_numpar,
               disbanco.idret,
               disbanco.k15_codbco,
               disbanco.k15_codage,
               disbanco.k00_numbco,
               disbanco.vlrpago,
               disbanco.vlracres,
               disbanco.vlrdesco,
               disbanco.vlrjuros,
               disbanco.vlrmulta,
               disbanco.dtpago,
               disbanco.instit,
               recibopaga.k00_dtpaga
      order by disbanco.idret

    loop

      lReciboInvalidoPorTrocaDeReceita = false;

      if lRaise is true then

        perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'=')                                  ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - IDRET : '||r_idret.idret                             ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'=')                                  ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - '                                                    ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - Numpre RECIBOPAGA : '||r_idret.numpre                ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - Valor Pago        : '||r_idret.vlrpago::numeric(15,2),lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - '                                                    ,lRaise,false,false);

      end if;

      --
      -- se o recibo estiver valido buscamos o valor calculado do recibo
      --
      if lRaise is true then
        perform fc_debug('  <PgtoParcial>  - Data recibopaga : '||r_idret.k00_dtpaga||' data pago banco : '||r_idret.dtpago,lRaise,false,false);
      end if;

      --
      -- Verificamos se o recibo que esta sendo pago tem algum pagamento parcial
      --   caso tenha pgto parcial recalcula a origem do debito
      --
      perform *
         from recibopaga r
              inner join arreckey           k    on k.k00_numpre       = r.k00_numpre
                                                and k.k00_numpar       = r.k00_numpar
                                                and k.k00_receit       = r.k00_receit
              inner join abatimentoarreckey ak   on ak.k128_arreckey   = k.k00_sequencial
              inner join abatimentodisbanco ab   on ab.k132_abatimento = ak.k128_abatimento
        where k00_numnov    = r_idret.numpre;

      if found then
        lReciboPossuiPgtoParcial := true;
      else

        lReciboPossuiPgtoParcial := false;
        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  ------------------------------------------'            ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Nao Encontrou Pagamento Parcial Anterior'            ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Numpre: '||r_idret.numpre||', IDRet: '||r_idret.idret,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  ------------------------------------------'            ,lRaise,false,false);
        end if;
      end if;

      /**
       * Validamos se o recibo foi gerado por regra, pois caso tenha
       * sido não deve recalcular a origem do débito
       * --Se for diferente de 0 não pode recalcular
       **/
      if lReciboPossuiPgtoParcial is true then

        perform *
         from recibopaga r
              inner join arreckey           k    on k.k00_numpre       = r.k00_numpre
                                                 and k.k00_numpar       = r.k00_numpar
                                                 and k.k00_receit       = r.k00_receit
              inner join abatimentoarreckey ak   on ak.k128_arreckey   = k.k00_sequencial
              inner join abatimentodisbanco ab   on ab.k132_abatimento = ak.k128_abatimento
              inner join db_reciboweb       dw   on r.k00_numnov       = dw.k99_numpre_n
        where k00_numnov   = r_idret.numpre
          and k99_desconto <> 0;

        if found then
          lReciboPossuiPgtoParcial := false;
        end if;

      end if;

       perform 1
          from recibopaga
         where recibopaga.k00_numnov = r_idret.numpre
           and recibopaga.k00_hist   not in (918, 970, 400, 401)
           and not exists (select 1
                             from arrecad
                            where arrecad.k00_numpre = recibopaga.k00_numpre
                              and arrecad.k00_numpar = recibopaga.k00_numpar
                              and arrecad.k00_receit = recibopaga.k00_receit );
      if found then
        lReciboInvalidoPorTrocaDeReceita := true;
      end if;

      perform fc_debug('  <PgtoParcial>  - Numpre : '||r_idret.numpre, lRaise);
      perform fc_debug('  <PgtoParcial>  - Data para pagamento : '||fc_proximo_dia_util(r_idret.k00_dtpaga), lRaise);
      perform fc_debug('  <PgtoParcial>  - Encontrou outro abatimento : '||lReciboPossuiPgtoParcial, lRaise);
      perform fc_debug('  <PgtoParcial>  - Trocou de receita : '||lReciboInvalidoPorTrocaDeReceita, lRaise);

      if     fc_proximo_dia_util(r_idret.k00_dtpaga) >= r_idret.dtpago
         and lReciboPossuiPgtoParcial is false
         and lReciboInvalidoPorTrocaDeReceita is false
      then

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - Calculado 1 ',lRaise,false,false);
        end if;

        select round(sum(k00_valor),2) as valor_total_recibo
          into nVlrCalculado
          from recibopaga
               inner join disbanco on disbanco.k00_numpre = recibopaga.k00_numnov
         where recibopaga.k00_numnov = r_idret.numpre
           and disbanco.idret        = r_idret.idret
           and exists ( select 1
                          from arrecad
                         where arrecad.k00_numpre = recibopaga.k00_numpre
                           and arrecad.k00_numpar = recibopaga.k00_numpar
                         limit 1 );

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - Valor calculado para recibo pago dentro do vencimento (recibopaga) : '||nVlrCalculado,lRaise,false,false);
        end if;

      else

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - Calculado 2 ',lRaise,false,false);
        end if;

        select coalesce(round(sum(utotal),2),0)::numeric(15,2)
          into nVlrCalculado
          from ( select ( substr(fc_calcula,15,13)::float8 +
                          substr(fc_calcula,28,13)::float8 +
                          substr(fc_calcula,41,13)::float8 -
                          substr(fc_calcula,54,13)::float8 ) as utotal
                   from ( select fc_calcula( x.k00_numpre,
                                             x.k00_numpar,
                                             0,
                                             x.dtpago,
                                             x.dtpago,
                                             extract(year from x.dtpago)::integer)
                                        from ( select distinct
                                                      recibopaga.k00_numpre,
                                                      recibopaga.k00_numpar,
                                                      dtpago
                                                 from recibopaga
                                                      inner join disbanco    on disbanco.k00_numpre     = recibopaga.k00_numnov
                                                      inner join arrecad     on arrecad.k00_numpre      = recibopaga.k00_numpre
                                                                            and arrecad.k00_numpar      = recibopaga.k00_numpar
                                                where recibopaga.k00_numnov = r_idret.numpre
                                                  and disbanco.idret        = r_idret.idret ) as x
                        ) as y
                ) as z;

      end if;

      if nVlrCalculado is null then
        nVlrCalculado := 0;
      end if;

      perform 1
         from recibopaga
              inner join disbanco on disbanco.k00_numpre = recibopaga.k00_numnov
              inner join arrecad  on arrecad.k00_numpre  = recibopaga.k00_numpre
                                 and arrecad.k00_numpar  = recibopaga.k00_numpar
              inner join issvar   on issvar.q05_numpre   = recibopaga.k00_numpre
                                 and issvar.q05_numpar   = recibopaga.k00_numpar
        where recibopaga.k00_numnov = r_idret.numpre
          and arrecad.k00_valor     = 0;

      if found then

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - **** ISSQN Variavel **** ',lRaise,false,false);
        end if;

        nVlrCalculado := r_idret.vlrpago;

      end if;


      if nVlrCalculado < 0 then

        codigo_retorno := 7;
        descricao := 'Debito com valor negativo - Numpre : '||r_idret.numpre||'.';
        return;
      end if;


      nVlrPgto          := ( r_idret.vlrpago )::numeric(15,2);
      nVlrDiferencaPgto := ( nVlrCalculado - nVlrPgto )::numeric(15,2);

      if lRaise is true then

        perform fc_debug('  <PgtoParcial>  - Calculado ................: '||nVlrCalculado            ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - Diferenca ................: '||nVlrDiferencaPgto        ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - Tolerancia Pgto Parcial ..: '||nVlrToleranciaPgtoParcial,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - Tolerancia Credito .......: '||nVlrToleranciaCredito    ,lRaise,false,false);
        perform fc_debug('  <PgtoParcial>  - '                                                       ,lRaise,false,false);

      end if;

      -- Caso o Pagamento Parcial esteja ativado entao a verificado se o valor pago e igual ao total do
      -- e caso nao seja, tambem e verificado se a diferenca do pagamento e menor que a tolenrancia para pagamento
      if lRaise is true then
        perform fc_debug('  <PgtoParcial>  - nVlrDiferencaPgto: '||nVlrDiferencaPgto||', nVlrDiferencaPgto: '||nVlrDiferencaPgto||',  nVlrToleranciaPgtoParcial: '||nVlrToleranciaPgtoParcial,lRaise,false,false);
      end if;

      /**
       * Inicio do Pagamento Parcial
       */
      if nVlrDiferencaPgto > 0 and nVlrDiferencaPgto > nVlrToleranciaPgtoParcial then

        -- Percentual pago do debito
        nPercPgto          := (( nVlrPgto * 100 ) / nVlrCalculado )::numeric;

        -- Insere Abatimento
        select nextval('abatimento_k125_sequencial_seq')
          into iAbatimento;

        if lRaise is true then

          perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'-'),lRaise,false,false);
          perform fc_debug('  PAGAMENTO PARCIAL : '||iAbatimento,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'-'),lRaise,false,false);

        end if;

        insert into abatimento ( k125_sequencial,
                                 k125_tipoabatimento,
                                 k125_datalanc,
                                 k125_hora,
                                 k125_usuario,
                                 k125_instit,
                                 k125_valor,
                                 k125_perc
                               ) values (
                                 iAbatimento,
                                 1,
                                 datausu,
                                 to_char(current_timestamp,'HH24:MI'),
                                 cast(fc_getsession('DB_id_usuario') as integer),
                                 iInstitSessao,
                                 nVlrPgto,
                                 nPercPgto
                               );

        insert into abatimentodisbanco ( k132_sequencial,
                     k132_abatimento,
                     k132_idret
                     ) values (
                      nextval('abatimentodisbanco_k132_sequencial_seq'),
                      iAbatimento,
                      r_idret.idret
                    );


        -- Gera um Recibo Avulso
        select nextval('numpref_k03_numpre_seq')
          into iNumpreReciboAvulso;

        insert into abatimentorecibo ( k127_sequencial,
                                       k127_abatimento,
                                       k127_numprerecibo,
                                       k127_numpreoriginal
                                     ) values (
                                       nextval('abatimentorecibo_k127_sequencial_seq'),
                                       iAbatimento,
                                       iNumpreReciboAvulso,
                                       coalesce( (select k00_numpre
                                                    from tmpdisbanco_inicio_original
                                                   where idret = r_idret.idret), iNumpreReciboAvulso)
                                     );


        -- Geracao de Recibo Avulso por Receita e Pagamento;

        select distinct round(sum(recibopaga.k00_valor),2)
          into nVlrTotalRecibopaga
          from disbanco
               inner join recibopaga on recibopaga.k00_numnov = disbanco.k00_numpre
         where disbanco.idret  = r_idret.idret
           and disbanco.instit = iInstitSessao;


        for rRecord in select distinct
                              recibopaga.k00_numcgm     as k00_numcgm,
                              recibopaga.k00_receit     as k00_receit,
                              round(sum(recibopaga.k00_valor),2) as k00_valor
                         from disbanco
                              inner join recibopaga on recibopaga.k00_numnov = disbanco.k00_numpre
                        where disbanco.idret  = r_idret.idret
                          and disbanco.instit = iInstitSessao
                     group by recibopaga.k00_receit,
                              recibopaga.k00_numcgm
        loop

          select k00_tipo
            into iTipoDebitoPgtoParcial
            from ( select ( select arrecad.k00_tipo
                              from arrecad
                             where arrecad.k00_numpre = recibopaga.k00_numpre
                               and arrecad.k00_numpar = recibopaga.k00_numpar

                             union

                            select arrecant.k00_tipo
                              from arrecant
                             where arrecant.k00_numpre = recibopaga.k00_numpre
                               and arrecant.k00_numpar = recibopaga.k00_numpar
                                limit 1
                          ) as k00_tipo
                     from disbanco
                          inner join recibopaga on recibopaga.k00_numnov = disbanco.k00_numpre
                    where disbanco.idret  = r_idret.idret
                      and disbanco.instit = iInstitSessao
                 ) as x;


          nPercReceita := ( (rRecord.k00_valor * 100) / nVlrTotalRecibopaga )::numeric(20,10);
          nVlrReceita  := trunc(( nVlrPgto * ( nPercReceita / 100 ))::numeric(15,2),2);

          if lRaise is true then
            perform fc_debug('  <PgtoParcial>  - <PgtoParcial> - Gerando recibo por receita e pagamento ',lRaise,false,false);
          end if;

          insert into recibo ( k00_numcgm,
                               k00_dtoper,
                               k00_receit,
                               k00_hist,
                               k00_valor,
                               k00_dtvenc,
                               k00_numpre,
                               k00_numpar,
                               k00_numtot,
                               k00_numdig,
                               k00_tipo,
                               k00_tipojm,
                               k00_codsubrec,
                               k00_numnov
                             ) values (
                               rRecord.k00_numcgm,
                               datausu,
                               rRecord.k00_receit,
                               504,
                               nVlrReceita,
                               datausu,
                               iNumpreReciboAvulso,
                               1,
                               1,
                               0,
                               iTipoDebitoPgtoParcial,
                               0,
                               0,
                               0
                             );


          insert into arrehist ( k00_numpre,
                                 k00_numpar,
                                 k00_hist,
                                 k00_dtoper,
                                 k00_hora,
                                 k00_id_usuario,
                                 k00_histtxt,
                                 k00_limithist,
                                 k00_idhist
                               ) values (
                                 iNumpreReciboAvulso,
                                 1,
                                 504,
                                 datausu,
                                 '00:00',
                                 1,
                                 'Recibo avulso referente pagamento parcial do recibo da CGF - numnov: ' || r_idret.numpre || ' idret: ' || r_idret.idret,
                                 null,
                                 nextval('arrehist_k00_idhist_seq')
                               );

          perform *
             from arrenumcgm
            where k00_numpre = iNumpreReciboAvulso
              and k00_numcgm = rRecord.k00_numcgm;

          if not found then

            insert into arrenumcgm ( k00_numcgm, k00_numpre ) values ( rRecord.k00_numcgm, iNumpreReciboAvulso );

          end if;
        end loop;

        /**
         * @todo remover o 100% pois quanto eh gerado um recibo de um parcelamento ha sim mais de uma matricula vinculada ao mesmo e assim o sera inserido 100 para cada origem do credito ou pagamento parcial
         */
        -- Acerta as origens do Recibo Avulso de acordo os Numpres da recibopaga informado
        insert into arrematric select distinct
                                      iNumpreReciboAvulso,
                                      arrematric.k00_matric,
                                      -- colocado 100 % fixo porque o numpre do recibo avulso gerado se trata de pagamento parcial
                                      -- e nao vai ter divisao de percentual entre mais de um numpre da mesma matricula
                                      100 as k00_perc
                                 from recibopaga
                                      inner join arrematric on arrematric.k00_numpre = recibopaga.k00_numpre
                                where recibopaga.k00_numnov = r_idret.numpre;


        insert into arreinscr  select distinct
                                      iNumpreReciboAvulso,
                                      arreinscr.k00_inscr,
                                      -- colocado 100 % fixo porque o numpre do recibo avulso gerado se trata de pagamento parcial
                                      -- e nao vai ter divisao de percentual entre mais de um numpre da mesma inscricao
                                      100 as k00_perc
                                 from recibopaga
                                      inner join arreinscr on arreinscr.k00_numpre = recibopaga.k00_numpre
                                where recibopaga.k00_numnov = r_idret.numpre;

        -- Percorre todos os debitos a serem abatidos
        for rRecord in select distinct
                              arrecad.k00_numpre,
                              arrecad.k00_numpar,
                              arrecad.k00_hist,
                              arrecad.k00_receit,
                              arrecad.k00_tipo
                         from recibopaga
                              inner join arrecad on arrecad.k00_numpre = recibopaga.k00_numpre
                                                and arrecad.k00_numpar = recibopaga.k00_numpar
                                                and arrecad.k00_receit = recibopaga.k00_receit
                        where recibopaga.k00_numnov = r_idret.numpre
                     order by arrecad.k00_numpre,
                              arrecad.k00_numpar,
                              arrecad.k00_receit
        loop

          select arreckey.k00_sequencial,
                 arrecadcompos.k00_sequencial
            into iArreckey,
                 iArrecadCompos
            from arreckey
                 left join arrecadcompos on arrecadcompos.k00_arreckey = arreckey.k00_sequencial
           where k00_numpre = rRecord.k00_numpre
             and k00_numpar = rRecord.k00_numpar
             and k00_receit = rRecord.k00_receit
             and k00_hist   = rRecord.k00_hist;

          --
          --   Quando houver um recibo com desconto manual e for realizado um pagamento parcial o sistema
          --   utiliza como valor calculado o valor liquido (valor com o desconto manual 918)
          --   e deixa o desconto perdido no arrecad, abatimentoarreckey, arreckey sendo que o mesmo ja foi utilizado
          --   para resolver, deletamos o registro de historico 918 do arrecad.
          --

          delete
            from arrecad
           where k00_numpre = rRecord.k00_numpre
             and k00_numpar = rRecord.k00_numpar
             and k00_receit = rRecord.k00_receit
             and k00_hist   in (918, 970);

          delete
            from abatimentoarreckey
           using arreckey
           where k00_sequencial = k128_arreckey
             and k00_numpre = rRecord.k00_numpre
             and k00_numpar = rRecord.k00_numpar
             and k00_receit = rRecord.k00_receit
             and k00_hist   in (918, 970);

          delete
            from arreckey
           where k00_numpre = rRecord.k00_numpre
             and k00_numpar = rRecord.k00_numpar
             and k00_receit = rRecord.k00_receit
             and k00_hist   in (918, 970);

          if iArreckey is null then

            select nextval('arreckey_k00_sequencial_seq')
              into iArreckey;

            insert into arreckey ( k00_sequencial,
                                   k00_numpre,
                                   k00_numpar,
                                   k00_receit,
                                   k00_hist,
                                   k00_tipo
                                 ) values (
                                   iArreckey,
                                   rRecord.k00_numpre,
                                   rRecord.k00_numpar,
                                   rRecord.k00_receit,
                                   rRecord.k00_hist,
                                   rRecord.k00_tipo
                                 );

          end if;

          -- Insere ligacao do abatimento com o debito
          select nextval('abatimentoarreckey_k128_sequencial_seq')
            into iAbatimentoArreckey;

          insert into abatimentoarreckey ( k128_sequencial,
                                           k128_arreckey,
                                           k128_abatimento,
                                           k128_valorabatido,
                                           k128_correcao,
                                           k128_juros,
                                           k128_multa
                                         ) values (
                                           iAbatimentoArreckey,
                                           iArreckey,
                                           iAbatimento,
                                           0,
                                           0,
                                           0,
                                           0
                                         );

          if iArrecadCompos is not null then

            insert into abatimentoarreckeyarrecadcompos ( k129_sequencial,
                                                          k129_abatimentoarreckey,
                                                          k129_arrecadcompos,
                                                          k129_vlrhist,
                                                          k129_correcao,
                                                          k129_juros,
                                                          k129_multa
                                                        ) values (
                                                          nextval('abatimentoarreckeyarrecadcompos_k129_sequencial_seq'),
                                                          iAbatimentoArreckey,
                                                          iArrecadCompos,
                                                          0,
                                                          0,
                                                          0,
                                                          0
                                                        );
          end if;

        end loop;

        -- Consulta valor total historico do debito
        select round(sum(x.k00_valor),2) as k00_valor
          into nVlrTotalHistorico
          from ( select distinct arrecad.*
                   from recibopaga
                      inner join arrecad  on arrecad.k00_numpre = recibopaga.k00_numpre
                                       and arrecad.k00_numpar = recibopaga.k00_numpar
                                       and arrecad.k00_receit = recibopaga.k00_receit
                where recibopaga.k00_numnov = r_idret.numpre ) as x;

        if lRaise is true then

          perform fc_debug('  <PgtoParcial>  - ',lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Total Historico   : '||nVlrTotalHistorico,lRaise,false,false);

        end if;

        -- Valor a ser abatido do arrecad e igual ao percentual do pagamento sobre o total historico
        nVlrAbatido := trunc(( nVlrTotalHistorico * ( nPercPgto / 100 ))::numeric(15,2),2);

        nVlrTotalJuroMultaCorr := nVlrPgto - nVlrAbatido;

        -- Dilui o valor abatido do arrecad ate zerar o nVlrAbatido encontrado
        while round(nVlrAbatido,2) > 0 loop

          nPercPgto := (( nVlrAbatido * 100 ) / nVlrTotalHistorico )::numeric;

          if lRaise is true then

            perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'.')              ,lRaise,false,false);
            perform fc_debug('  <PgtoParcial>  - Valor Abatido   : '||nVlrAbatido ,lRaise,false,false);
            perform fc_debug('  <PgtoParcial>  - Perc  Pagamento : '||nPercPgto   ,lRaise,false,false);

          end if;

          for rRecord in select *,
                                case
                                  when k00_hist in (918, 970) then 0
                                  else ( substr(fc_calcula,15,13)::float8 - substr(fc_calcula, 2,13)::float8 )::float8
                                end as vlrcorrecao,
                                case when k00_hist in (918, 970) then 0::float8 else substr(fc_calcula,28,13)::float8 end as vlrjuros,
                                case when k00_hist in (918, 970) then 0::float8 else substr(fc_calcula,41,13)::float8 end as vlrmulta
                           from ( select abatimentoarreckey.k128_sequencial,
                                         abatimentoarreckeyarrecadcompos.k129_sequencial,
                                         arrecad.*,
                                         arrecadcompos.*,
                                         fc_calcula( arrecad.k00_numpre,
                                                     arrecad.k00_numpar,
                                                     arrecad.k00_receit,
                                                     r_idret.dtpago,
                                                     r_idret.dtpago,
                                                     extract( year from r_idret.dtpago )::integer )
                                    from abatimentoarreckey
                                         inner join arreckey      on arreckey.k00_sequencial    = abatimentoarreckey.k128_arreckey
                                         left  join arrecadcompos on arrecadcompos.k00_arreckey = arreckey.k00_sequencial
                                         left  join abatimentoarreckeyarrecadcompos on k129_abatimentoarreckey = abatimentoarreckey.k128_sequencial
                                         inner join arrecad       on arrecad.k00_numpre         = arreckey.k00_numpre
                                                                 and arrecad.k00_numpar         = arreckey.k00_numpar
                                                                 and arrecad.k00_receit         = arreckey.k00_receit
                                                                 and arrecad.k00_hist           = arreckey.k00_hist
                                   where abatimentoarreckey.k128_abatimento = iAbatimento
                                order by arrecad.k00_numpre asc,
                                         arrecad.k00_numpar asc,
                                         arrecad.k00_valor  desc
                                ) as x


          loop

            -- Caso tenha sido zerado a variavel nVlrAbatido entao sai do loop

            if nVlrAbatido <= 0 then

              exit;

            end if;

            nVlrPgtoParcela := trunc((rRecord.k00_valor * ( nPercPgto / 100 ))::numeric(20,10),2);

            if lRaise is true then
              perform fc_debug('  <PgtoParcial>  - Valor Pagamento da Parcela: '||nVlrPgtoParcela,lRaise,false,false);
              perform fc_debug('  <PgtoParcial>  - lInsereJurMulCorr: '||lInsereJurMulCorr,lRaise,false,false);
            end if;

            if lInsereJurMulCorr then

              nVlrJuros         := trunc((rRecord.vlrjuros     * ( nPercPgto / 100 ))::numeric(20,10),2);
              nVlrMulta         := trunc((rRecord.vlrmulta     * ( nPercPgto / 100 ))::numeric(20,10),2);
              nVlrCorrecao      := trunc((rRecord.vlrcorrecao  * ( nPercPgto / 100 ))::numeric(20,10),2);

              nVlrHistCompos    := trunc((rRecord.k00_vlrhist  * ( nPercPgto / 100 ))::numeric(20,10),2);
              nVlrJurosCompos   := trunc((rRecord.k00_juros    * ( nPercPgto / 100 ))::numeric(20,10),2);
              nVlrMultaCompos   := trunc((rRecord.k00_multa    * ( nPercPgto / 100 ))::numeric(20,10),2);
              nVlrCorreCompos   := trunc((rRecord.k00_correcao * ( nPercPgto / 100 ))::numeric(20,10),2);

              if lRaise is true then
                perform fc_debug('  <PgtoParcial>  - nPercPgto:          : '||nPercPgto           ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - rRecord.vlrjuros    : '||rRecord.vlrjuros    ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - rRecord.vlrmulta    : '||rRecord.vlrmulta    ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - rRecord.vlrcorrecao : '||rRecord.vlrcorrecao ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>'                                                ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - rRecord.k00_vlrhist : '||rRecord.k00_vlrhist ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - rRecord.k00_juros   : '||rRecord.k00_juros   ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - rRecord.k00_multa   : '||rRecord.k00_multa   ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - rRecord.k00_correcao: '||rRecord.k00_correcao,lRaise,false,false);

                perform fc_debug('  <PgtoParcial>  -'                                             ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  -'                                             ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - nVlrJuros      : '||nVlrJuros                ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - nVlrMulta      : '||nVlrMulta                ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - nVlrCorrecao   : '||nVlrCorrecao             ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - '                                            ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - nVlrHistCompos : '||nVlrHistCompos           ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - nVlrJurosCompos: '||nVlrJurosCompos          ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - nVlrMultaCompos: '||nVlrMultaCompos          ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - nVlrCorreCompos: '||nVlrCorreCompos          ,lRaise,false,false);
              end if;

            else

              nVlrJuros         := 0;
              nVlrMulta         := 0;
              nVlrCorrecao      := 0;

              nVlrHistCompos    := 0;
              nVlrJurosCompos   := 0;
              nVlrMultaCompos   := 0;
              nVlrCorreCompos   := 0;

            end if;
            if lRaise is true then

              perform fc_debug('  <PgtoParcial>  -    Numpre: '||lpad(rRecord.k00_numpre,10,'0')||' Numpar: '||lpad(rRecord.k00_numpar, 3,'0')||' Receita: '||rRecord.k00_receit||' Valor Parcela: '||rRecord.k00_valor::numeric(15,2)||' Corr: '||nVlrCorrecao::numeric(15,2)||' Juros: '||nVlrJuros::numeric(15,2)||' Multa: '||nVlrMulta::numeric(15,2)||' Valor Pago: '||nVlrPgtoParcela::numeric(15,2)||' Resto: '||nVlrAbatido::numeric(15,2),lRaise,false,false);

            end if;

            -- Nao deixa retornar o valor zerado

            if lRaise is true then
              perform fc_debug('  <PgtoParcial>  - nVlrPgtoParcela: '||nVlrPgtoParcela||' rRecord.k00_hist: '||rRecord.k00_hist,lRaise,false,false);
            end if;

            if round(nVlrPgtoParcela,2) <= 0 and rRecord.k00_hist not in (918, 970) then

              if lRaise is true then

                perform fc_debug('  <PgtoParcial>  -    * Valor Parcela Menor que 0,01 - Corrige para 0,01 ',lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - ',lRaise,false,false);

              end if;

              nVlrPgtoParcela := 0.01;

            end if;


            update abatimentoarreckey
               set k128_valorabatido = ( k128_valorabatido + nVlrPgtoParcela )::numeric(15,2),
                   k128_correcao     = ( k128_correcao     + nVlrCorrecao    )::numeric(15,2),
                   k128_juros        = ( k128_juros        + nVlrJuros       )::numeric(15,2),
                   k128_multa        = ( k128_multa        + nVlrMulta       )::numeric(15,2)
             where k128_sequencial   = rRecord.k128_sequencial;


            if rRecord.k129_sequencial is not null then

              update abatimentoarreckeyarrecadcompos
                 set k129_vlrhist      = ( k129_vlrhist  + nVlrHistCompos  )::numeric(15,2),
                     k129_correcao     = ( k129_correcao + nVlrCorreCompos )::numeric(15,2),
                     k129_juros        = ( k129_juros    + nVlrJurosCompos )::numeric(15,2),
                     k129_multa        = ( k129_multa    + nVlrMultaCompos )::numeric(15,2)
               where k129_sequencial   = rRecord.k129_sequencial;

            end if;


            nVlrAbatido := trunc(( nVlrAbatido - nVlrPgtoParcela )::numeric(20,10),2)::numeric(15,2);

            if lRaise is true then
              perform fc_debug('  <PgtoParcial>  - nVlrAbatido: '||nVlrAbatido,lRaise,false,false);
            end if;

          end loop;

          if lRaise is true then
            perform fc_debug('  <PgtoParcial>  - lInsereJurMulCorr = False',lRaise,false,false);
          end if;

          lInsereJurMulCorr := false;

        end loop;

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - iAbatimento: '||iAbatimento,lRaise,false,false);
        end if;

        select round(sum(abatimentoarreckey.k128_correcao) +
                     sum(abatimentoarreckey.k128_juros)    +
                     sum(abatimentoarreckey.k128_multa),2) as totaljuromultacorr
          into rRecord
          from abatimentoarreckey
         where abatimentoarreckey.k128_abatimento = iAbatimento;


        if lRaise is true then

          perform fc_debug('  <PgtoParcial>  - '                                                          ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Total - Juros/ Multa / Corr : '||rRecord.totaljuromultacorr,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - Total - Geral               : '||nVlrTotalJuroMultaCorr    ,lRaise,false,false);
          perform fc_debug('  <PgtoParcial>  - '                                                          ,lRaise,false,false);

        end if;


        if rRecord.totaljuromultacorr <> round(nVlrTotalJuroMultaCorr,2) then

          update abatimentoarreckey
             set k128_correcao = ( k128_correcao + ( nVlrTotalJuroMultaCorr - round(rRecord.totaljuromultacorr,2) ))::numeric(15,2)
           where k128_sequencial in ( select max(k128_sequencial)
                                         from abatimentoarreckey
                                        where k128_abatimento = iAbatimento );
        end if;

        for rRecord in select distinct
                              arrecad.*,
                              abatimentoarreckey.k128_valorabatido,
                              arrecadcompos.k00_sequencial                              as arrecadcompos,
                              coalesce(abatimentoarreckeyarrecadcompos.k129_vlrhist ,0) as histcompos,
                              coalesce(abatimentoarreckeyarrecadcompos.k129_correcao,0) as correcompos,
                              coalesce(abatimentoarreckeyarrecadcompos.k129_juros   ,0) as juroscompos,
                              coalesce(abatimentoarreckeyarrecadcompos.k129_multa   ,0) as multacompos
                         from abatimentoarreckey
                              inner join arreckey                        on arreckey.k00_sequencial    = abatimentoarreckey.k128_arreckey
                              inner join arrecad                         on arrecad.k00_numpre         = arreckey.k00_numpre
                                                                        and arrecad.k00_numpar         = arreckey.k00_numpar
                                                                        and arrecad.k00_receit         = arreckey.k00_receit
                                                                        and arrecad.k00_hist           = arreckey.k00_hist
                              left  join arrecadcompos                   on arrecadcompos.k00_arreckey = arreckey.k00_sequencial
                              left  join abatimentoarreckeyarrecadcompos on abatimentoarreckeyarrecadcompos.k129_abatimentoarreckey = abatimentoarreckey.k128_sequencial
                        where abatimentoarreckey.k128_abatimento = iAbatimento
                     order by arrecad.k00_numpre,
                              arrecad.k00_numpar,
                              arrecad.k00_receit

        loop


          -- Caso o valor abata todo valor devido entao e quitado a tabela

          if round((rRecord.k00_valor - rRecord.k128_valorabatido),2) = 0 then

            insert into arrecantpgtoparcial ( k00_numpre,
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
                                              k00_tipojm,
                                              k00_abatimento
                                            ) values (
                                              rRecord.k00_numpre,
                                              rRecord.k00_numpar,
                                              rRecord.k00_numcgm,
                                              rRecord.k00_dtoper,
                                              rRecord.k00_receit,
                                              rRecord.k00_hist,
                                              rRecord.k00_valor,
                                              rRecord.k00_dtvenc,
                                              rRecord.k00_numtot,
                                              rRecord.k00_numdig,
                                              rRecord.k00_tipo,
                                              rRecord.k00_tipojm,
                                              iAbatimento
                                            );
            delete
              from arrecad
             where k00_numpre = rRecord.k00_numpre
               and k00_numpar = rRecord.k00_numpar
               and k00_receit = rRecord.k00_receit
               and k00_hist   = rRecord.k00_hist;

          else

            update arrecad
             set k00_valor  = ( k00_valor - rRecord.k128_valorabatido )
           where k00_numpre = rRecord.k00_numpre
             and k00_numpar = rRecord.k00_numpar
             and k00_receit = rRecord.k00_receit
             and k00_hist   = rRecord.k00_hist;

          end if;


          if rRecord.arrecadcompos is not null then

            update arrecadcompos
               set k00_vlrhist    = ( k00_vlrhist  - rRecord.histcompos  ),
                   k00_correcao   = ( k00_correcao - rRecord.correcompos ),
                   k00_juros      = ( k00_juros    - rRecord.juroscompos ),
                   k00_multa      = ( k00_multa    - rRecord.multacompos )
             where k00_sequencial = rRecord.arrecadcompos;

          end if;

        end loop;

        -- Acerta NUMPRE da disbanco
        if lRaise then
          perform fc_debug('  <PgtoParcial>  - 4 - Alterando numpre disbanco ! novo numpre : '||iNumpreReciboAvulso,lRaise,false,false);
        end if;

        update disbanco
           set k00_numpre = iNumpreReciboAvulso,
               k00_numpar = 0
         where idret      = r_idret.idret;


      --
      -- FIM PGTO PARCIAL
      --
      -- INICIO CREDITO/DESCONTO
      -- validacao da tolerancia do credito
      -- se o valor da diferenca for menor que 0 (significa que é um credito)
      -- e se o valor absoluto da diferenca for maior que o valor da tolerancia para credito sera gerado o credito
      --
      --
      elsif nVlrDiferencaPgto != 0 and ( nVlrDiferencaPgto > 0 or ( nVlrDiferencaPgto < 0 and abs(nVlrDiferencaPgto) > nVlrToleranciaCredito) ) then


        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - nVlrDiferencaPgto: '||nVlrDiferencaPgto||' - nVlrToleranciaCredito: '||nVlrToleranciaCredito, lRaise, false, false);
        end if;

        select nextval('abatimento_k125_sequencial_seq')
          into iAbatimento;


        if nVlrDiferencaPgto > 0 then

          iTipoAbatimento   = 2;

          if lRaise is true then

            perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'-')      ,lRaise,false,false);
            perform fc_debug('  <PgtoParcial>  - DESCONTO : '||iAbatimento,lRaise,false,false);
            perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'-')      ,lRaise,false,false);

          end if;

        else

          iTipoAbatimento   = 3;
          nVlrDiferencaPgto := ( nVlrDiferencaPgto * -1 );

          if lRaise is true then

            perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'-')      ,lRaise,false,false);
            perform fc_debug('  <PgtoParcial>  - CREDITO : '||iAbatimento ,lRaise,false,false);
            perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'-')      ,lRaise,false,false);

          end if;

        end if;


        nPercPgto := (( nVlrDiferencaPgto * 100 ) / r_idret.k00_valor )::numeric;

        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - Lancando Abatimento. nPercPgto: '||nPercPgto,lRaise,false,false);
        end if;

        insert into abatimento ( k125_sequencial,
                                 k125_tipoabatimento,
                                 k125_datalanc,
                                 k125_hora,
                                 k125_usuario,
                                 k125_instit,
                                 k125_valor,
                                 k125_perc,
                                 k125_valordisponivel
                               ) values (
                                 iAbatimento,
                                 iTipoAbatimento,
                                 datausu,
                                 to_char(current_timestamp,'HH24:MI'),
                                 cast(fc_getsession('DB_id_usuario') as integer),
                                 iInstitSessao,
                                 nVlrDiferencaPgto,
                                 nPercPgto,
                                 nVlrDiferencaPgto
                               );

        insert into abatimentodisbanco ( k132_sequencial,
                                         k132_abatimento,
                                         k132_idret
                                       ) values (
                                         nextval('abatimentodisbanco_k132_sequencial_seq'),
                                         iAbatimento,
                                         r_idret.idret
                                       );
        if lRaise is true then
          perform fc_debug('  <PgtoParcial>  - TipoAbatimento: '||iTipoAbatimento,lRaise,false,false);
        end if;

          if iTipoAbatimento = 3 then


          -- Gera um Recibo Avulso

          select nextval('numpref_k03_numpre_seq')
            into iNumpreReciboAvulso;

          if lRaise is true then
            perform fc_debug('  <PgtoParcial> -  ## Gerando recibo avulso. NumpreReciboAvulso: '||iNumpreReciboAvulso,lRaise,false,false);
          end if;

          insert into abatimentorecibo ( k127_sequencial,
                                         k127_abatimento,
                                         k127_numprerecibo,
                                         k127_numpreoriginal
                                       ) values (
                                         nextval('abatimentorecibo_k127_sequencial_seq'),
                                         iAbatimento,
                                         iNumpreReciboAvulso,
                                         coalesce( (select k00_numpre
                                                      from tmpdisbanco_inicio_original
                                                     where idret = r_idret.idret ), iNumpreReciboAvulso)
                                       );

          for rRecord in select k00_numcgm,
                                k00_tipo,
                                round(sum(k00_valor),2) as k00_valor
                           from ( select recibopaga.k00_numcgm,
                                         ( select arrecad.k00_tipo
                                             from arrecad
                                            where arrecad.k00_numpre = recibopaga.k00_numpre
                                              and arrecad.k00_numpar = recibopaga.k00_numpar
                                            union all
                                           select arrecant.k00_tipo
                                             from arrecant
                                            where arrecant.k00_numpre = recibopaga.k00_numpre
                                              and arrecant.k00_numpar = recibopaga.k00_numpar
                                            union all
                                           select arreold.k00_tipo
                                             from arreold
                                            where arreold.k00_numpre = recibopaga.k00_numpre
                                              and arreold.k00_numpar = recibopaga.k00_numpar
                                    union all
                                 select 1
                                   from arreprescr
                                  where arreprescr.k30_numpre = recibopaga.k00_numpre
                                    and arreprescr.k30_numpar = recibopaga.k00_numpar
                                         limit 1 ) as k00_tipo,
                                         recibopaga.k00_valor
                                    from disbanco
                                         inner join recibopaga on recibopaga.k00_numnov = disbanco.k00_numpre
                                   where disbanco.idret  = r_idret.idret
                                     and disbanco.instit = iInstitSessao
                                ) as x
                       group by k00_numcgm,
                                k00_tipo
           loop

             nVlrReceita := ( rRecord.k00_valor * ( nPercPgto / 100 ) )::numeric(15,2);

             select k00_receitacredito
               into iReceitaCredito
               from arretipo
              where k00_tipo = rRecord.k00_tipo;

              --Caso não tenha receita de crédito configurada para o tipo de débito, usamos a receita padrão dos parâmetros
              if iReceitaCredito is null then
                iReceitaCredito := iReceitaPadraoCredito;
              end if;

              if lRaise is true then
                perform fc_debug('  <PgtoParcial>  - iReceitaCredito: '||iReceitaCredito,lRaise,false,false);
              end if;

             if iReceitaCredito is null then

               codigo_retorno := 8;
               descricao := 'Receita de Crédito nao configurado nos parâmeotros e nem para o tipo de débito : '||rRecord.k00_tipo||'.';
               return;
             end if;

             if lRaise is true then
               perform fc_debug('  <PgtoParcial>  - ## lancando o recibo ref ao credito. ReceitaCredito: '||rRecord.k00_tipo||' ValorReceita: '||nVlrReceita,lRaise,false,false);
             end if;

             insert into recibo ( k00_numcgm,
                                  k00_dtoper,
                                  k00_receit,
                                  k00_hist,
                                  k00_valor,
                                  k00_dtvenc,
                                  k00_numpre,
                                  k00_numpar,
                                  k00_numtot,
                                  k00_numdig,
                                  k00_tipo,
                                  k00_tipojm,
                                  k00_codsubrec,
                                  k00_numnov
                                ) values (
                                  rRecord.k00_numcgm,
                                  datausu,
                                  iReceitaCredito,
                                  505,
                                  nVlrReceita,
                                  datausu,
                                  iNumpreReciboAvulso,
                                  1,
                                  1,
                                  0,
                                  iTipoReciboAvulso,
                                  0,
                                  0,
                                  0
                                );

             insert into arrehist ( k00_numpre,
                                    k00_numpar,
                                    k00_hist,
                                    k00_dtoper,
                                    k00_hora,
                                    k00_id_usuario,
                                    k00_histtxt,
                                    k00_limithist,
                                    k00_idhist
                                  ) values (
                                    iNumpreReciboAvulso,
                                    1,
                                    505,
                                    datausu,
                                    '00:00',
                                    1,
                                    'Recibo avulso referente ao credito do recibo da CGF - numnov: ' || r_idret.numpre || 'idret: ' || r_idret.idret,
                                    null,
                                    nextval('arrehist_k00_idhist_seq')
                                  );

             perform *
                from arrenumcgm
               where k00_numpre = iNumpreReciboAvulso
                 and k00_numcgm = rRecord.k00_numcgm;

             if not found then

               perform fc_debug('  <PgtoParcial>  - inserindo registro do recibo na arrenumcgm',lRaise,false,false);
               insert into arrenumcgm ( k00_numcgm, k00_numpre ) values ( rRecord.k00_numcgm, iNumpreReciboAvulso );
             end if;

           end loop;

           if lRaise is true then
             perform fc_debug('  <PgtoParcial>  - Inserindo na Arrematric [3]:'||iNumpreReciboAvulso,lRaise,false,false);
           end if;

           if lRaise is true then
             perform fc_debug('  <PgtoParcial>  - '||sDebug,lRaise,false,false);
           end if;

           insert into arrematric select distinct
                                         iNumpreReciboAvulso,
                                         arrematric.k00_matric,
                                         -- colocado 100 % fixo porque o numpre do recibo avulso gerado se trata de credito
                                         -- e nao vai ter divisao de percentual entre mais de um numpre da mesma matricula
                                         100 as k00_perc
                                    from recibopaga
                                         inner join arrematric on arrematric.k00_numpre = recibopaga.k00_numpre
                                   where recibopaga.k00_numnov = r_idret.numpre;

           insert into arreinscr  select distinct
                                         iNumpreReciboAvulso,
                                         arreinscr.k00_inscr,
                                         -- colocado 100 % fixo porque o numpre do recibo avulso gerado se trata de credito
                                         -- e nao vai ter divisao de percentual entre mais de um numpre da mesma matricula
                                         100 as k00_perc
                                    from recibopaga
                                         inner join arreinscr on arreinscr.k00_numpre = recibopaga.k00_numpre
                                   where recibopaga.k00_numnov = r_idret.numpre;

          if nVlrCalculado = 0 then

            if lRaise then
              perform fc_debug('  <PgtoParcial>  - 5 - Alterando numpre disbanco ! novo numpre : '||iNumpreReciboAvulso,lRaise,false,false);
            end if;

            update disbanco
               set k00_numpre = iNumpreReciboAvulso,
                   k00_numpar = 0
             where idret      = r_idret.idret;

          else

            if lRaise is true or true then
              perform fc_debug('  <PgtoParcial>  - Insere Disbanco',lRaise,false,false);
            end if;

            select nextval('disbanco_idret_seq')
              into iSeqIdRet;

            insert into disbanco (k00_numbco,
                                  k15_codbco,
                                  k15_codage,
                                  codret,
                                  dtarq,
                                  dtpago,
                                  vlrpago,
                                  vlrjuros,
                                  vlrmulta,
                                  vlracres,
                                  vlrdesco,
                                  vlrtot,
                                  cedente,
                                  vlrcalc,
                                  idret,
                                  classi,
                                  k00_numpre,
                                  k00_numpar,
                                  convenio,
                                  instit )
                           select k00_numbco,
                                  k15_codbco,
                                  k15_codage,
                                  codret,
                                  dtarq,
                                  dtpago,
                                  round(nVlrDiferencaPgto,2),
                                  0,
                                  0,
                                  0,
                                  0,
                                  round(nVlrDiferencaPgto,2),
                                  cedente,
                                  round(vlrcalc,2),
                                  iSeqIdRet,
                                  classi,
                                  iNumpreReciboAvulso,
                                  0,
                                  convenio,
                                 instit
                            from disbanco
                           where disbanco.idret = r_idret.idret;

            insert into tmpantesprocessar ( idret,
                                         vlrpago,
                                         v01_seq
                                       ) values (
                                         iSeqIdRet,
                                         nVlrDiferencaPgto,
                                         ( select nextval('w_divold_seq') )
                                       );

            update disbanco
               set vlrpago  = round(( vlrpago - nVlrDiferencaPgto ),2),
                   vlrtot   = round(( vlrtot  - nVlrDiferencaPgto ),2)
             where idret    = r_idret.idret;

            update tmpantesprocessar
               set vlrpago = round( vlrpago - nVlrDiferencaPgto,2 )
             where idret   = r_idret.idret;

          end if;

        end if;

        while nVlrDiferencaPgto > 0 loop

          nPercDesconto := (( nVlrDiferencaPgto * 100 ) / r_idret.k00_valor )::numeric;

          if lRaise is true then

            perform fc_debug('  <PgtoParcial>  - '||lpad('',100,'.')               ,lRaise,false,false);
            perform fc_debug('  <PgtoParcial>  - Percentual : '||nPercDesconto     ,lRaise,false,false);
            perform fc_debug('  <PgtoParcial>  - Diferenca  : '||nVlrDiferencaPgto ,lRaise,false,false);

          end if;

          perform 1
             from recibopaga
            where recibopaga.k00_numnov = r_idret.numpre
              and recibopaga.k00_hist   not in (918, 970)
              and exists ( select 1
                             from arrecad
                            where arrecad.k00_numpre = recibopaga.k00_numpre
                              and arrecad.k00_numpar = recibopaga.k00_numpar
                            union all
                           select 1
                             from arrecant
                            where arrecant.k00_numpre = recibopaga.k00_numpre
                              and arrecant.k00_numpar = recibopaga.k00_numpar
                            union all
                           select 1
                             from arreold
                            where arreold.k00_numpre = recibopaga.k00_numpre
                              and arreold.k00_numpar = recibopaga.k00_numpar
                            union all
                           select 1
                             from arreprescr
                            where arreprescr.k30_numpre = recibopaga.k00_numpre
                              and arreprescr.k30_numpar = recibopaga.k00_numpar
                            limit 1 );

          if not found then

            codigo_retorno := 9;
            descricao := 'Recibo '||r_idret.numpre||' inconsistente. IDRET : '||r_idret.idret||'.';
            return;
          end if;

          for rRecord in select distinct
                                recibopaga.k00_numpre,
                                recibopaga.k00_numpar,
                                recibopaga.k00_receit,
                                recibopaga.k00_hist,
                                recibopaga.k00_numcgm,
                                recibopaga.k00_numtot,
                                recibopaga.k00_numdig,
                                ( select arrecad.k00_tipo
                                    from arrecad
                                   where arrecad.k00_numpre  = recibopaga.k00_numpre
                                     and arrecad.k00_numpar  = recibopaga.k00_numpar
                                   union all
                                  select arrecant.k00_tipo
                                    from arrecant
                                   where arrecant.k00_numpre = recibopaga.k00_numpre
                                     and arrecant.k00_numpar = recibopaga.k00_numpar
                                   union all
                                  select arreold.k00_tipo
                                    from arreold
                                   where arreold.k00_numpre = recibopaga.k00_numpre
                                     and arreold.k00_numpar = recibopaga.k00_numpar
                                   union all
                                  select 1
                                    from arreprescr
                                   where arreprescr.k30_numpre = recibopaga.k00_numpre
                                     and arreprescr.k30_numpar = recibopaga.k00_numpar
                                   limit 1 ) as k00_tipo,
                                round(sum(recibopaga.k00_valor),2) as k00_valor
                           from recibopaga
                          where recibopaga.k00_numnov = r_idret.numpre
                            and recibopaga.k00_hist   not in (918, 970)
                            and exists ( select 1
                                           from arrecad
                                          where arrecad.k00_numpre = recibopaga.k00_numpre
                                            and arrecad.k00_numpar = recibopaga.k00_numpar
                                         --   and arrecad.k00_receit = recibopaga.k00_receit
                                          union all
                                         select 1
                                           from arrecant
                                          where arrecant.k00_numpre = recibopaga.k00_numpre
                                            and arrecant.k00_numpar = recibopaga.k00_numpar
                                         --   and arrecant.k00_receit = recibopaga.k00_receit
                                          union all
                                         select 1
                                           from arreold
                                          where arreold.k00_numpre = recibopaga.k00_numpre
                                            and arreold.k00_numpar = recibopaga.k00_numpar
                                         --   and arreold.k00_receit = recibopaga.k00_receit
                                          union all
                                         select 1
                                           from arreprescr
                                          where arreprescr.k30_numpre = recibopaga.k00_numpre
                                            and arreprescr.k30_numpar = recibopaga.k00_numpar
                                         --   and arreprescr.k30_receit = recibopaga.k00_receit
                                          limit 1 )
                       group by recibopaga.k00_numpre,
                                recibopaga.k00_numpar,
                                recibopaga.k00_receit,
                                recibopaga.k00_hist,
                                recibopaga.k00_numcgm,
                                recibopaga.k00_numtot,
                                recibopaga.k00_numdig
          loop

            if nVlrDiferencaPgto <= 0 then

              if lRaise is true then

                perform fc_debug('  <PgtoParcial>  - '     ,lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - SAIDA',lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - '     ,lRaise,false,false);

              end if;

              exit;

            end if;

            nVlrPgtoParcela := trunc((rRecord.k00_valor * ( nPercDesconto / 100 ))::numeric,2);


            if lRaise is true then
              perform fc_debug('  <PgtoParcial>  -   Numpre: '||lpad(rRecord.k00_numpre,10,'0')||' Numpar: '||lpad(rRecord.k00_numpar, 3,'0')||' Receita: '||lpad(rRecord.k00_receit,10,'0')||' Valor Parcela: '||rRecord.k00_valor::numeric(15,2)||' Valor Pago: '||nVlrPgtoParcela::numeric(15,2)||' Resto: '||nVlrDiferencaPgto::numeric(15,2),lRaise,false,false);
            end if;


            if nVlrPgtoParcela <= 0 then

              if lRaise is true then
                perform fc_debug('  <PgtoParcial>  -   * Valor Parcela Menor que 0,01 - Corrige para 0,01 ',lRaise,false,false);
                perform fc_debug('  <PgtoParcial>  - ',lRaise,false,false);
              end if;

              nVlrPgtoParcela := 0.01;

            end if;


            select k00_sequencial
              into iArreckey
              from arrecadacao.arreckey
             where k00_numpre = rRecord.k00_numpre
               and k00_numpar = rRecord.k00_numpar
               and k00_receit = rRecord.k00_receit
               and k00_hist   = rRecord.k00_hist;


            if not found then

              select nextval('arreckey_k00_sequencial_seq')
                into iArreckey;

              insert into arreckey ( k00_sequencial,
                                     k00_numpre,
                                     k00_numpar,
                                     k00_receit,
                                     k00_hist,
                                     k00_tipo
                                   ) values (
                                     iArreckey,
                                     rRecord.k00_numpre,
                                     rRecord.k00_numpar,
                                     rRecord.k00_receit,
                                     rRecord.k00_hist,
                                     rRecord.k00_tipo
                                   );
            end if;


            select k128_sequencial
              into iAbatimentoArreckey
              from abatimentoarreckey
                   inner join arreckey on arreckey.k00_sequencial = abatimentoarreckey.k128_arreckey
             where abatimentoarreckey.k128_abatimento = iAbatimento
               and arreckey.k00_numpre = rRecord.k00_numpre
               and arreckey.k00_numpar = rRecord.k00_numpar
               and arreckey.k00_receit = rRecord.k00_receit
               and arreckey.k00_hist   = rRecord.k00_hist;

            if found then

              update abatimentoarreckey
                 set k128_valorabatido = ( k128_valorabatido + nVlrPgtoParcela )::numeric(15,2)
               where k128_sequencial   = iAbatimentoArreckey;

            else

              -- Insere ligacao do abatimento com o

              insert into abatimentoarreckey ( k128_sequencial,
                                               k128_arreckey,
                                               k128_abatimento,
                                               k128_valorabatido,
                                             k128_correcao,
                                             k128_juros,
                                             k128_multa
                                             ) values (
                                               nextval('abatimentoarreckey_k128_sequencial_seq'),
                                               iArreckey,
                                               iAbatimento,
                                               nVlrPgtoParcela,
                                               0,
                                               0,
                                               0
                                             );
            end if;

            nVlrDiferencaPgto := round(nVlrDiferencaPgto - nVlrPgtoParcela,2);

          end loop;

        end loop;

      end if; -- fim credito/desconto

    end loop;

    if lRaise is true then
      perform fc_debug('  <PgtoParcial>  -  FIM ABATIMENTO ',lRaise,false,false);
    end if;

  end if;

  /**
   * Fim do Pagamento Parcial
   */

  if lRaise is true then
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  - processando numpres duplos com pagamento em cota unica e parcelado no mesmo arquivo...',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
  end if;

  for r_codret in
      select disbanco.codret,
             disbanco.idret,
             disbanco.instit,
             disbanco.k00_numpre,
             disbanco.k00_numpar,
             coalesce((select count(*)
                         from recibopaga
                        where recibopaga.k00_numnov = disbanco.k00_numpre
                          and disbanco.k00_numpar = 0),0) as quant_recibopaga,
             coalesce((select count(*)
                         from arrecad
                        where arrecad.k00_numpre = disbanco.k00_numpre
                          and disbanco.k00_numpar = 0),0) as quant_arrecad_unica,
             coalesce((select max(k00_numtot)
                         from arrecad
                        where arrecad.k00_numpre = disbanco.k00_numpre
                          and disbanco.k00_numpar = 0),0) as arrecad_unica_numtot,
             coalesce((select count(distinct k00_numpar)
                         from arrecad
                        where arrecad.k00_numpre = disbanco.k00_numpre
                          and disbanco.k00_numpar = 0),0) as arrecad_unica_quant_numpar,
             coalesce((select max(d2.idret)
                         from disbanco d2
                        where d2.k00_numpre = disbanco.k00_numpre
                          and d2.codret = disbanco.codret
                          and d2.idret <> disbanco.idret
                          and classi is false),0) as idret_mesmo_numpre
        from disbanco
       where disbanco.codret = cod_ret
         and disbanco.classi is false
         and disbanco.instit = iInstitSessao
    order by idret
  loop

    -- idret_mesmo_numpre
    -- busca se tem algum numpre duplo no mesmo arquivo (significa que o contribuinte pagou no mesmo dia e banco e consequentemente no mesmo arquivo
    -- o numpre numpre 2 ou mais vezes

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  - idret: '||r_codret.idret||' - numpre: '||r_codret.k00_numpre||' - parcela: '||r_codret.k00_numpar||' - quant_recibopaga: '||r_codret.quant_recibopaga||' - quant_arrecad_unica: '||r_codret.quant_arrecad_unica||' - arrecad_unica_numtot: '||r_codret.arrecad_unica_numtot||' - arrecad_unica_quant_numpar: '||r_codret.arrecad_unica_quant_numpar,lRaise,false,false);
    end if;

    -- alteracao 1
    -- o sistema tem que descobrir nos casos de pagamento da unica e parcelado, qual o idret na unica de maior percentual (pois pode ter pago 2 unicas)
    -- e nao inserir na tabela "tmpnaoprocessar" o idret desse registro

    if r_codret.k00_numpar = 0 and r_codret.quant_arrecad_unica > 0 then

      if r_codret.arrecad_unica_quant_numpar <> r_codret.arrecad_unica_numtot then
        -- se for unica e a quantidade de parcelas em aberto for diferente da quantidade total de parcelas, significa que o contribuinte pagou como unica
        -- mas ja tem parcelas em aberto, e dessa forma o sistema nao vai processar esse registro para alguem verificar o que realmente vai ser feito,
        -- pois o contribuinte pagou o valor da unica mas nao tem mais todas as parcelas que formaram a unica em aberto

        if cCliente != 'ALEGRETE' then
          insert into tmpnaoprocessar values (r_codret.idret);

          if lRaise is true then
           perform fc_debug('  <BaixaBanco>  - inserindo em tmpnaoprocessar (1): '||r_codret.idret,lRaise,false,false);
          end if;
        end if;

      else

        for r_testa in
          select idret,
                 k00_numpre,
                 k00_numpar
            from disbanco
           where disbanco.k00_numpre =  r_codret.k00_numpre
             and disbanco.codret     =  r_codret.codret
             and disbanco.idret      <> r_codret.idret
             and classi              is false
        loop

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  - idret: '||r_testa.idret||' - numpar: '||r_testa.k00_numpar,lRaise,false,false);
          end if;

          -- busca a parcela unica de menor valor (maior percentual de desconto) paga por esse numpre
          select idret
          into iIdRetProcessar
          from disbanco
          where disbanco.k00_numpre =  r_codret.k00_numpre
                and disbanco.k00_numpar = 0
                and disbanco.codret     =  r_codret.codret
                and classi is false
          order by vlrpago limit 1;

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  - idret: '||r_testa.idret||' - iIdRetProcessar: '||iIdRetProcessar,lRaise,false,false);
          end if;

          -- senao for o registro da unica de maior percentual nao processa
          if iIdRetProcessar != r_testa.idret then

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - inserindo em tmpnaoprocessar (2): '||r_testa.idret,lRaise,false,false);
            end if;

            insert into tmpnaoprocessar values (r_testa.idret);

          else

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - NAO inserindo em tmpnaoprocessar (2): '||r_testa.idret,lRaise,false,false);
            end if;

          end if;

      end loop;

        select count(distinct disbanco2.idret)
          into v_contador
          from disbanco
               inner join recibopaga          on disbanco.k00_numpre  =  recibopaga.k00_numpre
                                             and disbanco.k00_numpar  =  0
               inner join disbanco disbanco2  on disbanco2.k00_numpre =  recibopaga.k00_numnov
                                             and disbanco2.k00_numpar =  0
                                             and disbanco2.codret     =  cod_ret
                                             and disbanco2.classi     is false
                                             and disbanco2.instit     =  iInstitSessao
                                             and disbanco2.idret      <> r_codret.idret
         where disbanco.codret = cod_ret
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
           and disbanco.idret  = r_codret.idret;

        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - idret: '||r_codret.idret||' - v_contador: '||v_contador,lRaise,false,false);
        end if;

        if v_contador = 1 then
          select distinct
                 disbanco2.idret
            into iIdret
            from disbanco
                 inner join recibopaga         on disbanco.k00_numpre  = recibopaga.k00_numpre
                                              and disbanco.k00_numpar  = 0
                 inner join disbanco disbanco2 on disbanco2.k00_numpre = recibopaga.k00_numnov
                                              and disbanco2.k00_numpar = 0
                                              and disbanco2.codret     = cod_ret
                                              and disbanco2.classi     is false
                                              and disbanco2.instit     = iInstitSessao
                                              and disbanco2.idret      <> r_codret.idret
           where disbanco.codret = cod_ret
             and disbanco.classi is false
             and disbanco.instit = iInstitSessao
             and disbanco.idret  = r_codret.idret;

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  - inserindo em nao processar (3) - idret1: '||iIdret||' - idret2: '||r_codret.idret,lRaise,false,false);
          end if;

          insert into tmpnaoprocessar values (iIdret);

        elsif v_contador >= 1 then

          codigo_retorno := 21;
          descricao := 'IDRET ' || r_codret.idret || ' COM MAIS DE UM PAGAMENTO NO MESMO ARQUIVO. CONTATE SUPORTE PARA VERIFICAÇÕES!';
          return;
        end if;

      end if;

    end if;

    -- Validamos o numpre para ver se nao esta duplicado em algum lugar
    -- arrecad(k00_numpre) = recibopaga(k00_numnov)
    -- arrecad(k00_numpre) = recibo(k00_numnov)
    -- caso esteja nao processa o numpre caindo em inconsistencia
    if exists ( select 1 from arrecad where arrecad.k00_numpre   = r_codret.k00_numpre limit 1)
          and ( exists ( select 1 from recibopaga where recibopaga.k00_numnov = r_codret.k00_numpre limit 1) or
                exists ( select 1 from recibo     where recibo.k00_numnov     = r_codret.k00_numpre limit 1) ) then
       if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - inserindo em tmpnaoprocessar (5): '||r_codret.idret,lRaise,false,false);
       end if;
       insert into tmpnaoprocessar values (r_codret.idret);
    end if;

    -- Validacao numpre na ISSVAR com numpar = 0 na DISBANCO para nao processar
    -- porem se o numpre estiver na db_reciboweb (k99_numpre_n) e na issvar (q05_numpre)
    -- significa que esse debito eh oriundo de uma integracao externa. Ex: Gissonline
    if r_codret.k00_numpar = 0
      and exists (select 1 from issvar where q05_numpre = r_codret.k00_numpre)
      and not exists (select 1 from db_reciboweb where k99_numpre_n = r_codret.k00_numpre) then
      if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - inserindo em tmpnaoprocessar (6): '||r_codret.idret,lRaise,false,false);
      end if;
      insert into tmpnaoprocessar values (r_codret.idret);
    end if;

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    end if;

  end loop;

  /**
   * Adicionada validação para separar recibos e carnes que possuiam suas origens importadas para divida
   * apenas quando pagamento parcial estiver DESATIVADO
   */
  if lAtivaPgtoParcial = false then

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  - inicio separando recibopaga...',lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    end if;

    -- acertando recibos (recibopaga) com registros que foram importados divida e outros que nao foram importados, e estava gerando erro, entao a logica abaixo
    -- separa em dois recibos novos os casos
    for r_codret in
        select disbanco.codret,
               disbanco.idret,
               disbanco.instit,
               disbanco.k00_numpre,
               disbanco.k00_numpar,
               disbanco.vlrpago::numeric(15,2),
               (select round(sum(k00_valor),2)
                  from recibopaga
                 where k00_numnov = disbanco.k00_numpre) as recibopaga_sum_valor
         from disbanco
         where disbanco.codret = cod_ret
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
           and k00_numpar = 0
           and exists (select 1 from recibopaga inner join divold on k00_numpre = k10_numpre and k00_numpar = k10_numpar where k00_numnov = disbanco.k00_numpre)
           and (select count(*) from recibopaga where k00_numnov = disbanco.k00_numpre) > 0
           and disbanco.idret not in (select idret from tmpnaoprocessar)
      order by idret
    loop

        perform *
           from abatimentodisbanco
          where k132_idret = r_codret.idret;

        if found then
          continue;
        end if;

        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
          perform fc_debug('  <BaixaBanco>  - idret: '||r_codret.idret||' - vlrpago: '||r_codret.vlrpago||' - numpre: '||r_codret.k00_numpre||' - numpar: '||r_codret.k00_numpar,lRaise,false,false);
        end if;

        nSimDivold := 0;
        nNaoDivold := 0;

        nValorSimDivold := 0;
        nValorNaoDivold := 0;

        nTotValorPagoDivold := 0;

        nTotalRecibo       := 0;
        nTotalNovosRecibos := 0;

        perform * from (
        select  recibopaga.k00_numpre as recibopaga_numpre,
                recibopaga.k00_numpar as recibopaga_numpar,
                recibopaga.k00_receit as recibopaga_receit,
                recibopaga.k00_numnov,
                coalesce( (select count(*)
                             from divold
                                  inner join divida  on divold.k10_coddiv  = divida.v01_coddiv
                                  inner join arrecad on arrecad.k00_numpre = divida.v01_numpre
                                                    and arrecad.k00_numpar = divida.v01_numpar
                                                    and arrecad.k00_valor  > 0
                            where divold.k10_numpre = recibopaga.k00_numpre
                              and divold.k10_numpar = recibopaga.k00_numpar
                          ), 0 ) as divold,
                round(sum(k00_valor),2) as k00_valor
           from disbanco
                inner join recibopaga on disbanco.k00_numpre = recibopaga.k00_numnov
                                     and disbanco.k00_numpar = 0
          where disbanco.idret = r_codret.idret
          group by recibopaga.k00_numpre,
                   recibopaga.k00_numpar,
                   recibopaga.k00_receit,
                   recibopaga.k00_numnov,
                   divold
        ) as x where k00_valor < 0;

        if found then
          insert into tmpnaoprocessar values (r_codret.idret);
          perform fc_debug('  <BaixaBanco>  - idret '||r_codret.idret || ' - insert tmpnaoprocessar',lRaise,false,false);
        else

          for r_testa in
          select  recibopaga.k00_numpre as recibopaga_numpre,
                  recibopaga.k00_numpar as recibopaga_numpar,
                  recibopaga.k00_receit as recibopaga_receit,
                  recibopaga.k00_numnov,
                  coalesce( (select count(*)
                               from divold
                                    inner join divida  on divold.k10_coddiv = divida.v01_coddiv
                                    inner join arrecad on arrecad.k00_numpre = divida.v01_numpre
                                                     and arrecad.k00_numpar = divida.v01_numpar
                 and arrecad.k00_valor > 0
                             where divold.k10_numpre = recibopaga.k00_numpre
                               and divold.k10_numpar = recibopaga.k00_numpar
                             --and divold.k10_receita = recibopaga.k00_receit
                            ),0) as divold,
                  round(sum(k00_valor),2) as k00_valor
             from disbanco
                  inner join recibopaga on disbanco.k00_numpre = recibopaga.k00_numnov
                                       and disbanco.k00_numpar = 0
            where disbanco.idret = r_codret.idret
            group by recibopaga.k00_numpre,
                     recibopaga.k00_numpar,
                     recibopaga.k00_receit,
                     recibopaga.k00_numnov,
                     divold
          loop

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - verificando recibopaga - numpre: '||r_testa.recibopaga_numpre||' - numpar: '||r_testa.recibopaga_numpar||' - divold: '||r_testa.divold||' - k00_valor: '||r_testa.k00_valor,lRaise,false,false);
            end if;

            if r_testa.divold > 0 then
              nSimDivold := nSimDivold + 1;
              nValorSimDivold := nValorSimDivold + r_testa.k00_valor;
            else
              nNaoDivold := nNaoDivold + 1;
              nValorNaoDivold := nValorNaoDivold + r_testa.k00_valor;
            end if;
            insert into tmpacerta_recibopaga_unif values (r_testa.recibopaga_numpre, r_testa.recibopaga_numpar, r_testa.recibopaga_receit, r_testa.k00_numnov, case when r_testa.divold > 0 then 1 else 2 end);

          end loop;

        end if;

        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - nSimDivold: '||nSimDivold||' - nNaoDivold: '||nNaoDivold||' - idret: '||r_codret.idret,lRaise,false,false);
        end if;

        if nSimDivold > 0 and nNaoDivold > 0 then

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
            perform fc_debug('  <BaixaBanco>  - vai ser dividido...',lRaise,false,false);
            perform fc_debug('  <BaixaBanco>  - nSimDivold: '||nSimDivold||' - nNaoDivold: '||nNaoDivold||' - idret: '||r_codret.idret,lRaise,false,false);
            perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
          end if;

          nValorTotDivold := nValorSimDivold + nValorNaoDivold;

          for rContador in select 1 as tipo union select 2 as tipo
            loop

            select nextval('numpref_k03_numpre_seq') into iNumnovDivold;

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - inserindo em recibopaga - numnov: '||iNumnovDivold,lRaise,false,false);
              perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
            end if;

            insert into recibopaga
            (
            k00_numcgm,
            k00_dtoper,
            k00_receit,
            k00_hist,
            k00_valor,
            k00_dtvenc,
            k00_numpre,
            k00_numpar,
            k00_numtot,
            k00_numdig,
            k00_conta,
            k00_dtpaga,
            k00_numnov
            )
            select
            k00_numcgm,
            k00_dtoper,
            k00_receit,
            k00_hist,
            k00_valor,
            k00_dtvenc,
            k00_numpre,
            k00_numpar,
            k00_numtot,
            k00_numdig,
            k00_conta,
            k00_dtpaga,
            iNumnovDivold
            from recibopaga
            inner join tmpacerta_recibopaga_unif on
            recibopaga.k00_numpre = tmpacerta_recibopaga_unif.numpre and
            recibopaga.k00_numpar = tmpacerta_recibopaga_unif.numpar and
            recibopaga.k00_receit = tmpacerta_recibopaga_unif.receit and
            recibopaga.k00_numnov = tmpacerta_recibopaga_unif.numpreoriginal
            where tmpacerta_recibopaga_unif.tipo = rContador.tipo;

            insert into db_reciboweb
            (
            k99_numpre,
            k99_numpar,
            k99_numpre_n,
            k99_codbco,
            k99_codage,
            k99_numbco,
            k99_desconto,
            k99_tipo,
            k99_origem
            )
            select
            distinct
            k99_numpre,
            k99_numpar,
            iNumnovDivold,
            k99_codbco,
            k99_codage,
            k99_numbco,
            k99_desconto,
            k99_tipo,
            k99_origem
            from db_reciboweb
            inner join tmpacerta_recibopaga_unif on
            k99_numpre = tmpacerta_recibopaga_unif.numpre and
            k99_numpar = tmpacerta_recibopaga_unif.numpar and
            k99_numpre_n = tmpacerta_recibopaga_unif.numpreoriginal
            where tmpacerta_recibopaga_unif.tipo = rContador.tipo;

            insert into arrehist
            (
            k00_numpre,
            k00_numpar,
            k00_hist,
            k00_dtoper,
            k00_hora,
            k00_id_usuario,
            k00_histtxt,
            k00_limithist,
            k00_idhist
            )
            values
            (
            iNumnovDivold,
            0,
            930,
            current_date,
            to_char(now(), 'HH24:MI'),
            1,
            'criado automaticamente pela divisao automatica dos recibos durante a consistencia da baixa de banco - numpre original: ' || r_testa.k00_numnov,
            null,
            nextval('arrehist_k00_idhist_seq'));

            select nextval('disbanco_idret_seq') into v_nextidret;

            nValorPagoDivold := case when rContador.tipo = 1 then nValorSimDivold else nValorNaoDivold end / nValorTotDivold * r_codret.vlrpago;
            nTotValorPagoDivold := nTotValorPagoDivold + nValorPagoDivold;

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - tipo: '||rContador.tipo||' - nValorSimDivold: '||nValorSimDivold||' - nValorNaoDivold: '||nValorNaoDivold||' - nValorTotDivold: '||nValorTotDivold||' - vlrpago: '||r_codret.vlrpago||' - nTotValorPagoDivold: '||nTotValorPagoDivold,lRaise,false,false);
            end if;

            if rContador.tipo = 2 then
              if nTotValorPagoDivold <> r_codret.vlrpago then
                if lRaise is true then
                  perform fc_debug('  <BaixaBanco>  - acertando nValorPagoDivold',lRaise,false,false);
                end if;
                nValorPagoDivold := r_codret.vlrpago - nTotValorPagoDivold;
              end if;
            end if;

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - inserindo disbanco - idret: '||v_nextidret||' - vlrpago: '||nValorPagoDivold||' - numnov: '||iNumnovDivold||' - novo idret: '||v_nextidret,lRaise,false,false);
            end if;

            insert into disbanco (k00_numbco,
                                  k15_codbco,
                                  k15_codage,
                                  codret,
                                  dtarq,
                                  dtpago,
                                  vlrpago,
                                  vlrjuros,
                                  vlrmulta,
                                  vlracres,
                                  vlrdesco,
                                  vlrtot,
                                  cedente,
                                  vlrcalc,
                                  idret,
                                  classi,
                                  k00_numpre,
                                  k00_numpar,
                                  convenio,
                                  instit )
                           select k00_numbco,
                                  k15_codbco,
                                  k15_codage,
                                  codret,
                                  dtarq,
                                  dtpago,
                                  round(nValorPagoDivold,2),
                                  0,
                                  0,
                                  0,
                                  0,
                                  round(nValorPagoDivold,2),
                                  cedente,
                                  round(vlrcalc,2),
                                  v_nextidret,
                                  false,
                                  iNumnovDivold,
                                  0,
                                  convenio,
                                  instit
                             from disbanco
                            where idret = r_codret.idret;

            insert into tmpantesprocessar (idret, vlrpago, v01_seq) values (v_nextidret, nValorPagoDivold, (select nextval('w_divold_seq')) );

            select round(sum(k00_valor),2)
              into nTotalRecibo
              from recibopaga where k00_numnov = iNumnovDivold;

            nTotalNovosRecibos := nTotalNovosRecibos + nTotalRecibo;

          end loop;

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  - nTotalNovosRecibos: '||nTotalNovosRecibos||' - recibopaga_k00_valor: '||r_codret.recibopaga_sum_valor,lRaise,false,false);
            if round(nTotalNovosRecibos,2) <> round(r_codret.recibopaga_sum_valor,2) then

              codigo_retorno := 22;
              descricao := 'INCONSISTENCIA AO GERAR NOVOS RECIBOS NA DIVISAO. IDRET: '||r_codret.idret||' - NUMPRE RECIBO ORIGINAL: '||r_codret.k00_numpre||'.';
              return;
            end if;
          end if;

          update disbancotxtreg
             set k35_idret = v_nextidret
           where k35_idret = r_codret.idret;

          delete
            from issarqsimplesregdisbanco
           where q44_disbanco = r_codret.idret;

          delete
            from disbancoprocesso
           where k142_idret = r_codret.idret;

          delete
            from disbanco
           where disbanco.idret = r_codret.idret;

        else

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
            perform fc_debug('  <BaixaBanco>  - NAO vai ser dividido...',lRaise,false,false);
            perform fc_debug('  <BaixaBanco>  - nSimDivold: '||nSimDivold||' - nNaoDivold: '||nNaoDivold||' - idret: '||r_codret.idret,lRaise,false,false);
            perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
          end if;

        end if;

        delete from tmpacerta_recibopaga_unif;

    end loop;

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  - fim separando recibopaga...',lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    end if;

  end if;
  /**
   * Fim da separação de recibos e carnes que possuiam suas origens importadas para divida
   */

  select round(sum(vlrpago),2)
    into nTotalDisbancoOriginal
    from tmpdisbanco_inicio_original;

  select round(sum(vlrpago),2)
    into nTotalDisbancoDepois
    from disbanco
   where disbanco.codret = cod_ret
     and disbanco.classi is false
     and disbanco.instit = iInstitSessao;

  if lRaise is true then
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  - nTotalDisbancoOriginal: '||nTotalDisbancoOriginal||' - nTotalDisbancoDepois: '||nTotalDisbancoDepois,lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  - inicio verificando se foi importado para divida',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
  end if;

  -- verifica se foi importado para divida, porem somente nos casos de pagamento por carne, ou seja, registros que estejam no arrecad pelo numpre e parcela
  for r_codret in
      select disbanco.codret,
             disbanco.idret,
             disbanco.instit
        from disbanco
       where disbanco.codret = cod_ret
         and disbanco.classi is false
         and disbanco.instit = iInstitSessao
         and disbanco.idret not in (select idret from tmpnaoprocessar)
    order by idret
  loop

    -- inicio numpre/numpar (carne)
    for r_idret in
      select distinct
             1 as tipo,
             disbanco.dtarq,
             disbanco.dtpago,
             disbanco.k00_numpre as numpre,
             disbanco.k00_numpar as numpar,
             disbanco.idret,
             disbanco.k15_codbco,
             disbanco.k15_codage,
             disbanco.k00_numbco,
             disbanco.vlrpago,
             disbanco.vlracres,
             disbanco.vlrdesco,
             disbanco.vlrjuros,
             disbanco.vlrmulta,
             disbanco.instit
        from disbanco
             inner join divold   on divold.k10_numpre = disbanco.k00_numpre
                                and divold.k10_numpar = disbanco.k00_numpar
             inner join divida   on divida.v01_coddiv = divold.k10_coddiv
                                and divida.v01_instit = iInstitSessao
             inner join arrecad  on arrecad.k00_numpre = divida.v01_numpre
                                and arrecad.k00_numpar = divida.v01_numpar
        and arrecad.k00_valor > 0
       where disbanco.idret  = r_codret.idret
         and disbanco.classi is false
         and disbanco.instit = iInstitSessao
         and disbanco.k00_numpar > 0
      union
      select distinct
             2 as tipo,
             disbanco.dtarq,
             disbanco.dtpago,
             disbanco.k00_numpre as numpre,
             disbanco.k00_numpar as numpar,
             disbanco.idret,
             disbanco.k15_codbco,
             disbanco.k15_codage,
             disbanco.k00_numbco,
             disbanco.vlrpago,
             disbanco.vlracres,
             disbanco.vlrdesco,
             disbanco.vlrjuros,
             disbanco.vlrmulta,
             disbanco.instit
       from disbanco
             inner join db_reciboweb on db_reciboweb.k99_numpre_n = disbanco.k00_numpre
             inner join divold       on divold.k10_numpre = db_reciboweb.k99_numpre
                                    and divold.k10_numpar = db_reciboweb.k99_numpar
             inner join divida       on divida.v01_coddiv = divold.k10_coddiv
                                    and divida.v01_instit = iInstitSessao
             inner join arrecad      on arrecad.k00_numpre = divida.v01_numpre
                                    and arrecad.k00_numpar = divida.v01_numpar
            and arrecad.k00_valor > 0
       where disbanco.idret  = r_codret.idret
         and disbanco.classi is false
         and disbanco.instit = iInstitSessao
         and disbanco.k00_numpar = 0
       union
      select distinct
             3 as tipo,
             disbanco.dtarq,
             disbanco.dtpago,
             disbanco.k00_numpre as numpre,
             disbanco.k00_numpar as numpar,
             disbanco.idret,
             disbanco.k15_codbco,
             disbanco.k15_codage,
             disbanco.k00_numbco,
             disbanco.vlrpago,
             disbanco.vlracres,
             disbanco.vlrdesco,
             disbanco.vlrjuros,
             disbanco.vlrmulta,
             disbanco.instit
        from disbanco
             inner join divold   on divold.k10_numpre = disbanco.k00_numpre and disbanco.k00_numpar = 0
             inner join divida   on divida.v01_coddiv = divold.k10_coddiv
                                and divida.v01_instit = iInstitSessao
             inner join arrecad  on arrecad.k00_numpre = divida.v01_numpre
                                and arrecad.k00_numpar = divida.v01_numpar
                                and arrecad.k00_valor > 0
       where disbanco.idret  = r_codret.idret
         and disbanco.classi is false
         and disbanco.instit = iInstitSessao
         and disbanco.k00_numpar = 0

    loop

      --
      -- Verificamos se o idret ja nao teve um abatimento lancado.
      --   Quando temos um recibo que teve uma de suas origens(numpre, numpar) importadas para divida / parcelada
      --   antes do processamento do pagamento a baixa os retira do recibopaga para gerar uma diferenca e processa
      --   o pagamento parcial / credito normalmente
      -- Por isso no caso de existir regitros na abatimentodisbanco passamos para a proxima volta do for
      --
      perform *
         from abatimentodisbanco
        where k132_idret = r_codret.idret;

      if found then
        continue;
      end if;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
        perform fc_debug('  <BaixaBanco>  - divold idret: '||R_IDRET.idret||' - tipo: '||R_IDRET.tipo||' - vlrpago: '||R_IDRET.vlrpago,lRaise,false,false);
      end if;

      v_total1 := 0;
      v_total2 := 0;
      v_diferenca_round := 0;

      for r_divold in

        select distinct
               1 as tipo,
               v01_coddiv,
               divida.v01_numpre,
               divida.v01_numpar,
               divida.v01_valor
          from disbanco
               inner join divold   on divold.k10_numpre = disbanco.k00_numpre
                                  and divold.k10_numpar = disbanco.k00_numpar
               inner join divida   on divida.v01_coddiv = divold.k10_coddiv
                                  and divida.v01_instit = iInstitSessao
               inner join arrecad  on arrecad.k00_numpre = divida.v01_numpre
                                  and arrecad.k00_numpar = divida.v01_numpar
          and arrecad.k00_valor > 0
         where disbanco.idret  = r_codret.idret and r_idret.tipo = 1
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
           and disbanco.k00_numpar > 0
        union
        select distinct
               2 as tipo,
               v01_coddiv,
               divida.v01_numpre,
               divida.v01_numpar,
               divida.v01_valor
          from disbanco
               inner join db_reciboweb on db_reciboweb.k99_numpre_n = disbanco.k00_numpre and disbanco.k00_numpar = 0
               inner join divold       on divold.k10_numpre = db_reciboweb.k99_numpre
                                      and divold.k10_numpar = db_reciboweb.k99_numpar
               inner join divida       on divida.v01_coddiv = divold.k10_coddiv
                                      and divida.v01_instit = iInstitSessao
               inner join arrecad      on arrecad.k00_numpre = divida.v01_numpre
                                      and arrecad.k00_numpar = divida.v01_numpar
              and arrecad.k00_valor > 0
         where disbanco.idret  = r_codret.idret and r_idret.tipo = 2
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
         union
        select distinct
               3 as tipo,
               v01_coddiv,
               divida.v01_numpre,
               divida.v01_numpar,
               divida.v01_valor
          from disbanco
               inner join divold   on divold.k10_numpre = disbanco.k00_numpre and disbanco.k00_numpar = 0
               inner join divida   on divida.v01_coddiv = divold.k10_coddiv
                                  and divida.v01_instit = iInstitSessao
               inner join arrecad  on arrecad.k00_numpre = divida.v01_numpre
                                  and arrecad.k00_numpar = divida.v01_numpar
          and arrecad.k00_valor > 0
         where disbanco.idret  = r_codret.idret and r_idret.tipo = 3
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
           and disbanco.k00_numpar = 0

      loop

        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - somando v_total1 - v01_coddiv: '||r_divold.v01_coddiv||' - valor: '||r_divold.v01_valor,lRaise,false,false);
        end if;

        v_total1 := v_total1 + r_divold.v01_valor;

      end loop;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - idret: '||r_codret.idret||' - v_total1: '||v_total1,lRaise,false,false);
      end if;

      for r_divold in
        select * from
        (
        select distinct
               1 as tipo,
               v01_coddiv,
               divida.v01_numpre,
               divida.v01_numpar,
               divida.v01_valor,
               nextval('w_divold_seq') as v01_seq
          from disbanco
               inner join divold   on divold.k10_numpre = disbanco.k00_numpre
                                  and divold.k10_numpar = disbanco.k00_numpar
               inner join divida   on divida.v01_coddiv = divold.k10_coddiv
                                  and divida.v01_instit = iInstitSessao
               inner join arrecad  on arrecad.k00_numpre = divida.v01_numpre
                                  and arrecad.k00_numpar = divida.v01_numpar
          and arrecad.k00_valor > 0
         where disbanco.idret  = r_codret.idret
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
           and r_idret.tipo = 1
           and disbanco.k00_numpar > 0
         union
         select distinct
               2 as tipo,
               v01_coddiv,
               v01_numpre,
               v01_numpar,
               v01_valor,
               nextval('w_divold_seq') as v01_seq
          from (
                 select distinct
                       v01_coddiv,
                       divida.v01_numpre,
                       divida.v01_numpar,
                       divida.v01_valor
                  from disbanco
                       inner join db_reciboweb on db_reciboweb.k99_numpre_n = disbanco.k00_numpre and disbanco.k00_numpar = 0
                       inner join divold   on divold.k10_numpre = db_reciboweb.k99_numpre
                                          and divold.k10_numpar = db_reciboweb.k99_numpar
                       inner join divida   on divida.v01_coddiv = divold.k10_coddiv
                                          and divida.v01_instit = iInstitSessao
                       inner join arrecad  on arrecad.k00_numpre = divida.v01_numpre
                                          and arrecad.k00_numpar = divida.v01_numpar
            and arrecad.k00_valor > 0
                 where disbanco.idret  = r_codret.idret
                   and disbanco.classi is false
                   and disbanco.instit = iInstitSessao
                   and r_idret.tipo = 2
              ) as x
        union
         select distinct
               3 as tipo,
               v01_coddiv,
               v01_numpre,
               v01_numpar,
               v01_valor,
               nextval('w_divold_seq') as v01_seq
          from (
                select distinct
                       v01_coddiv,
                       divida.v01_numpre,
                       divida.v01_numpar,
                       divida.v01_valor
                from disbanco
                     inner join divold   on divold.k10_numpre = disbanco.k00_numpre and disbanco.k00_numpar = 0
                     inner join divida   on divida.v01_coddiv = divold.k10_coddiv
                                        and divida.v01_instit = iInstitSessao
                     inner join arrecad  on arrecad.k00_numpre = divida.v01_numpre
                                        and arrecad.k00_numpar = divida.v01_numpar
          and arrecad.k00_valor > 0
               where disbanco.idret  = r_codret.idret
                 and disbanco.classi is false
                 and disbanco.instit = iInstitSessao
                 and r_idret.tipo = 3
                 and disbanco.k00_numpar = 0
              ) as x
           ) as x
           order by v01_seq

      loop

        select nextval('disbanco_idret_seq')
          into v_nextidret;

        v_valor           := round(round(r_divold.v01_valor, 2) / v_total1 * round(r_idret.vlrpago, 2), 2);
        v_valor_sem_round := round(r_divold.v01_valor, 2) / v_total1 * round(r_idret.vlrpago, 2);

        v_diferenca_round := v_diferenca_round + (v_valor - v_valor_sem_round);

        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - inserindo disbanco - processando idret: '||r_codret.idret||' - v01_coddiv: '||r_divold.v01_coddiv||' - valor: '||v_valor,lRaise,false,false);
        end if;

        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - v_valor: '||v_valor||' - v_valor_sem_round: '||v_valor_sem_round||' - v_diferenca_round: '||v_diferenca_round||' - seq: '||r_divold.v01_seq||' - tipo: '||r_divold.tipo,lRaise,false,false);
        end if;

        insert into disbanco (
          k00_numbco,
          k15_codbco,
          k15_codage,
          codret,
          dtarq,
          dtpago,
          vlrpago,
          vlrjuros,
          vlrmulta,
          vlracres,
          vlrdesco,
          vlrtot,
          cedente,
          vlrcalc,
          idret,
          classi,
          k00_numpre,
          k00_numpar,
          convenio,
          instit
        ) values (
          r_idret.k00_numbco,
          r_idret.k15_codbco,
          r_idret.k15_codage,
          cod_ret,
          r_idret.dtarq,
          r_idret.dtpago,
          v_valor,
          0,
          0,
          0,
          0,
          v_valor,
          '',
          0,
          v_nextidret,
          false,
          r_divold.v01_numpre,
          r_divold.v01_numpar,
          '',
          r_idret.instit
        );

        insert into tmpantesprocessar (idret, vlrpago, v01_seq) values (v_nextidret, v_valor, r_divold.v01_seq);

        v_total2 := v_total2 + v_valor;

      end loop;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - v_total2 antes da diferenca do round: '||v_total2,lRaise,false,false);
      end if;

      if v_diferenca_round <> 0 then
        update tmpantesprocessar set vlrpago = round(vlrpago - v_diferenca_round,2) where v01_seq = (select max(v01_seq) from tmpantesprocessar);
        update disbanco          set vlrpago = round(vlrpago - v_diferenca_round,2) where idret   = (select idret from tmpantesprocessar where v01_seq = (select max(v01_seq) from tmpantesprocessar));
        v_total2 := v_total2 - v_diferenca_round;
        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - v_total2 depois da diferenca do round: '||v_total2,lRaise,false,false);
        end if;

      end if;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - v_total2: '||v_total2||' - vlrpago: '||r_idret.vlrpago,lRaise,false,false);
      end if;

      if round(v_total2, 2) <> round(r_idret.vlrpago, 2) then

        codigo_retorno := 23;
        descricao := 'IDRET ' || r_codret.idret || ' INCONSISTENTE AO VINCULAR A DIVIDA ATIVA! CONTATE SUPORTE - VALOR SOMADO: ' || v_total2 || ' - VALOR PAGO: ' || r_idret.vlrpago || '!';
        return;
      end if;

      update disbancotxtreg
         set k35_idret = v_nextidret
       where k35_idret = r_codret.idret;

      --
      -- Deletando da issarqsimplesregdisbanco pois pode o debito
      -- do simples ter sido importado para divida
      --
      delete
        from issarqsimplesregdisbanco
       where q44_disbanco = r_codret.idret;

      delete
        from disbancoprocesso
       where k142_idret = r_codret.idret;

      delete
        from disbanco
       where disbanco.codret = cod_ret
         and disbanco.classi = false
         and disbanco.idret  = r_codret.idret;

      delete
        from tmpantesprocessar
       where idret = r_codret.idret;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - DELETANDO DISBANCO E ANTESPROCESSAR...',lRaise,false,false);
      end if;

    end loop;

  end loop;

  if lRaise is true then
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  - fim verificando se foi importado para divida',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  - inicio PROCESSANDO REGISTROS...',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
  end if;

  --------
  -------- PROCESSANDO REGISTROS
  --------
  for r_codret in
      select disbanco.codret,
             disbanco.idret,
             disbanco.k00_numpre,
             disbanco.k00_numpar,
             disbanco.vlrpago
        from disbanco
       where disbanco.codret = cod_ret
         and disbanco.classi is false
         and disbanco.instit = iInstitSessao
         and disbanco.idret not in (select idret from tmpnaoprocessar)
    order by disbanco.idret
  loop
    gravaidret := 0;

    -- pelo NUMPRE
    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  - iniciando registro disbanco - idret '||r_codret.idret,lRaise,false,false);
    end if;

    -- Verifica se eh recibo da emissao geral do issqn e na recibopaga esta com valor zerado
    -- caso positivo iremos atualizar o valor da recibopaga com o vlrpago da disbanco
    -- e gerar um arrehist para o caso
      select k00_numpre,
             k00_numpar,
             k00_receit,
             k00_hist,
             round(sum(k00_valor),2) as k00_valor
        into rReciboPaga
        from db_reciboweb
             inner join recibopaga  on k00_numnov = k99_numpre_n
       where k99_numpre_n = r_codret.k00_numpre
         and k99_tipo     = 6 -- Emissao Geral de ISSQN
    group by k00_numpre,
             k00_numpar,
             k00_receit,
             k00_hist
      having cast(round(sum(k00_valor),2) as numeric) = cast(0.00 as numeric);

    if found then
      update recibopaga
         set k00_valor  = r_codret.vlrpago
       where k00_numnov = r_codret.k00_numpre
         and k00_numpre = rReciboPaga.k00_numpre
         and k00_numpar = rReciboPaga.k00_numpar
         and k00_receit = rReciboPaga.k00_receit
         and k00_hist   = rReciboPaga.k00_hist;

      -- T24879: gerar arrehist para essa alteracao
      insert
        into arrehist(k00_idhist, k00_numpre, k00_numpar, k00_hist, k00_dtoper, k00_hora, k00_id_usuario, k00_histtxt, k00_limithist)
      values (nextval('arrehist_k00_idhist_seq'),
              rReciboPaga.k00_numpre,
              rReciboPaga.k00_numpar,
              rReciboPaga.k00_hist,
              cast(fc_getsession('DB_datausu') as date),
              to_char(current_timestamp, 'HH24:MI'),
              cast(fc_getsession('DB_id_usuario') as integer),
              'ALTERADO PELO ARQUIVO BANCARIO CODRET='||cast(r_codret.codret as text)||' IDRET='||cast(r_codret.idret as text),
              null);

    end if;

    v_estaemrecibopaga    := false;
    v_estaemrecibo        := false;
    v_estaemarrecadnormal := false;
    v_estaemarrecadunica  := false;

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  - verificando recibopaga...',lRaise,false,false);
      -- TESTE 1 - RECIBOPAGA
      -- alteracao 2 - sistema deve testar como ja faz na autentica se todos os registros da recibopaga estao na arrecad, e senao tem que dar inconsistencia
    end if;

    for r_idret in

    /**
     * @todo verificar numprebloqpag / alterar disbanco por recibopaga
     */
        select disbanco.k00_numpre as numpre,
               disbanco.k00_numpar as numpar,
               disbanco.idret,
               disbanco.k15_codbco,
               disbanco.k15_codage,
               disbanco.k00_numbco,
               disbanco.vlrpago,
               disbanco.vlracres,
               disbanco.vlrdesco,
               disbanco.vlrjuros,
               disbanco.vlrmulta,
               round(sum(recibopaga.k00_valor),2) as k00_valor,
               disbanco.instit
          from disbanco
               inner join recibopaga     on disbanco.k00_numpre       = recibopaga.k00_numnov
               left  join numprebloqpag  on numprebloqpag.ar22_numpre = disbanco.k00_numpre
                                        and numprebloqpag.ar22_numpar = disbanco.k00_numpar
         where disbanco.idret  = r_codret.idret
           and disbanco.classi is false
           and disbanco.instit = iInstitSessao
           and recibopaga.k00_conta = 0
           and numprebloqpag.ar22_numpre is null
      group by disbanco.k00_numpre,
               disbanco.k00_numpar,
               disbanco.idret,
               disbanco.k15_codbco,
               disbanco.k15_codage,
               disbanco.k00_numbco,
               disbanco.vlrpago,
               disbanco.vlracres,
               disbanco.vlrdesco,
               disbanco.vlrjuros,
               disbanco.vlrmulta,
               disbanco.instit
    loop

      v_estaemrecibopaga := true;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - recibopaga - numpre '||r_idret.numpre||' numpar '||r_idret.numpar,lRaise,false,false);
      end if;

      -- Verifica se algum numpre do recibo nao esta no arrecad
      -- caso nao esteja passa para o proximo e deixa inconsistente
      select coalesce(count(*),0)
        into iQtdeParcelasAberto
        from  (select distinct
                      arrecad.k00_numpre,
                      arrecad.k00_numpar
                 from recibopaga
                      inner join arrecad on arrecad.k00_numpre = recibopaga.k00_numpre
                                        and arrecad.k00_numpar = recibopaga.k00_numpar
                where k00_numnov = r_codret.k00_numpre ) as x;

      select coalesce(count(*),0)
        into iQtdeParcelasRecibo
        from (select distinct
                     recibopaga.k00_numpre,
                     recibopaga.k00_numpar
                from recibopaga
               where k00_numnov = r_codret.k00_numpre ) as x;

      if iQtdeParcelasAberto <> iQtdeParcelasRecibo then
        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  -   nao encontrou arrecad... gravaidret: '||gravaidret,lRaise,false,false);
        end if;
        continue;
      else
        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  -   encontrou em arrecad... gravaidret: '||gravaidret,lRaise,false,false);
        end if;
      end if;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - entrou no update vlrcalc (1)...',lRaise,false,false);
      end if;

      -- Acerta vlrcalc
      update disbanco
         set vlrcalc = round((select (substr(fc_calcula,15,13)::float8+
                                substr(fc_calcula,28,13)::float8+
                                substr(fc_calcula,41,13)::float8-
                                substr(fc_calcula,54,13)::float8) as utotal
                          from (select fc_calcula(k00_numpre,k00_numpar,0,dtpago,dtpago,extract(year from dtpago)::integer)
                                  from disbanco
                                 where idret = r_codret.idret
                                   and codret = r_codret.codret
                                   and instit = iInstitSessao
                          ) as x
                       ),2)
       where idret  = r_codret.idret
         and codret = r_codret.codret
         and instit = r_idret.instit;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - saiu no update vlrcalc (1)...',lRaise,false,false);
      end if;

      gravaidret := r_codret.idret;
      retorno    := true;

      -- INSERE NO ARREPAGA OS PAGAMENTOS
      insert into arrepaga ( k00_numcgm,
                             k00_dtoper,
                             k00_receit,
                             k00_hist,
                             k00_valor,
                             k00_dtvenc,
                             k00_numpre,
                             k00_numpar,
                             k00_numtot,
                             k00_numdig,
                             k00_conta,
                             k00_dtpaga
                           ) select k00_numcgm,
                                    datausu,
                                    k00_receit,
                                    k00_hist,
                                    round(sum(k00_valor),2) as k00_valor,
                                    datausu,
                                    k00_numpre,
                                    k00_numpar,
                                    k00_numtot,
                                    k00_numdig,
                                    conta,
                                    datausu
                               from ( select k00_numcgm,
                                             k00_receit,
                                             case
                                               when exists ( select 1
                                                               from tmplista_unica
                                                              where idret = r_idret.idret ) then 990
                                               else k00_hist
                                             end as k00_hist,
                                             round((k00_valor / r_idret.k00_valor) * r_idret.vlrpago, 2) as k00_valor,
                                             k00_numpre,
                                             k00_numpar,
                                             k00_numtot,
                                             k00_numdig
                                        from recibopaga
                                       where k00_numnov = r_idret.numpre
                                    ) as x
                           group by k00_numcgm,
                                    k00_receit,
                                    k00_hist,
                                    k00_numpre,
                                    k00_numpar,
                                    k00_numtot,
                                    k00_numdig
                           order by k00_numpre,
                                    k00_numpar,
                                    k00_receit;

-- ALTERA SITUACAO DO ARREPAGA
      update recibopaga
         set k00_conta = conta,
             k00_dtpaga = datausu
       where k00_numnov = r_idret.numpre;

      v_contador := 0;
      v_somador  := 0;
      v_contagem := 0;

      for q_disrec in
          select k00_numpre,
                 k00_numpar,
                 k00_receit,
                 sum(round((k00_valor / r_idret.k00_valor) * r_idret.vlrpago, 2))
            from recibopaga
           where k00_numnov = r_idret.numpre
        group by k00_numpre,
                 k00_numpar,
                 k00_receit
          having sum(round(k00_valor,2)) <> 0.00::float8
      loop
        v_contagem := v_contagem + 1;
      end loop;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - v_contagem: '||v_contagem,lRaise,false,false);
      end if;

      for q_disrec in
          select k00_numpre,
                 k00_numpar,
                 k00_receit,
                 sum( round((k00_valor / r_idret.k00_valor) * r_idret.vlrpago, 2) )::numeric(15,2)
            from recibopaga
           where k00_numnov = r_idret.numpre
        group by k00_numpre,
                 k00_numpar,
                 k00_receit
          having sum(round(k00_valor,2)) <> 0.00::float8
      loop

        v_contador := v_contador + 1;

        -- INSERE NO ARRECANT
        insert into arrecant  (
          k00_numpre,
          k00_numpar,
          k00_numcgm,
          k00_dtoper,
          k00_receit,
          k00_hist  ,
          k00_valor ,
          k00_dtvenc,
          k00_numtot,
          k00_numdig,
          k00_tipo  ,
          k00_tipojm
        ) select arrecad.k00_numpre,
                 arrecad.k00_numpar,
                 arrecad.k00_numcgm,
                 arrecad.k00_dtoper,
                 arrecad.k00_receit,
                 arrecad.k00_hist  ,
                 arrecad.k00_valor ,
                 arrecad.k00_dtvenc,
                 arrecad.k00_numtot,
                 arrecad.k00_numdig,
                 arrecad.k00_tipo  ,
                 arrecad.k00_tipojm
            from arrecad
                 inner join arreinstit  on arreinstit.k00_numpre = arrecad.k00_numpre
                                       and arreinstit.k00_instit = iInstitSessao
           where arrecad.k00_numpre = q_disrec.k00_numpre
             and arrecad.k00_numpar = q_disrec.k00_numpar;

        -- DELETE DO ARRECAD
        delete
          from arrecad
         using arreinstit
         where arreinstit.k00_numpre = arrecad.k00_numpre
           and arreinstit.k00_instit = iInstitSessao
           and arrecad.k00_numpre = q_disrec.k00_numpre
           and arrecad.k00_numpar = q_disrec.k00_numpar;

       -- TESTA SE EXISTE NUMPRE E NUMPAR NO ARREIDRET, NAO EXISTINDO INSERE O IDRET DO PAGAMENTO
        select arreidret.k00_numpre
          into _testeidret
          from arreidret
         where arreidret.k00_numpre = q_disrec.k00_numpre
           and arreidret.k00_numpar = q_disrec.k00_numpar
           and arreidret.idret      = r_idret.idret
           and arreidret.k00_instit = iInstitSessao;

        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - inserindo arreidret - numpre: '||q_disrec.k00_numpre||' - numpar: '||q_disrec.k00_numpar||' - idret: '||r_idret.idret,lRaise,false,false);
        end if;

        if _testeidret is null then
          insert into arreidret (
            k00_numpre,
            k00_numpar,
            idret,
            k00_instit
          ) values (
            q_disrec.k00_numpre,
            q_disrec.k00_numpar,
            r_idret.idret,
            r_idret.instit
          );
        end if;

        if q_disrec.sum != 0 then
          if autentsn is false then
-- GRAVA DISREC DAS RECEITAS PARA A CLASSIFICACAO
            v_somador := v_somador + q_disrec.sum;

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - inserindo disrec - receita: '||q_disrec.k00_receit||' - valor: '||q_disrec.sum||' - contador: '||v_contador||' - somador: '||v_somador||' - contagem: '||v_contagem,lRaise,false,false);
            end if;

            v_valor := q_disrec.sum;

            if v_contador = v_contagem then
              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  - vlrpago: '||r_idret.vlrpago||' - v_somador: '||v_somador,lRaise,false,false);
              end if;
              v_valor := v_valor + round(r_idret.vlrpago - v_somador,2);
            end if;

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - into disrec 1',lRaise,false,false);
              perform fc_debug('  <BaixaBanco>  - Verifica Receita',lRaise,false,false);
            end if;

            lVerificaReceita := fc_verificareceita(q_disrec.k00_receit);

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - Retorno verifica Receita: '||lVerificaReceita,lRaise,false,false);
            end if;

            if lVerificaReceita is false then

              codigo_retorno := 24;
              descricao := 'Receita: '||q_disrec.k00_receit||' nao encontrada verifique o cadastro (1).';
              return;
            end if;

            perform * from disrec where disrec.codcla = vcodcla and disrec.k00_receit = q_disrec.k00_receit and disrec.idret = r_idret.idret and disrec.instit = r_idret.instit;

            if not found then

              v_valor := round(v_valor,2);

              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  -    inserindo disrec 1 - valor: '||v_valor||' - receita: '||q_disrec.k00_receit,lRaise,false,false);
              end if;

              if v_valor > 0 then

                if lRaise is true then
                  perform fc_debug('  <BaixaBanco>  - Inserindo na DISREC. valor: '||v_valor,lRaise,false,false);
                end if;

                insert into disrec (
                  codcla,
                  k00_receit,
                  vlrrec,
                  idret,
                  instit
                ) values (
                  vcodcla,
                  q_disrec.k00_receit,
                  v_valor,
                  r_idret.idret,
                  r_idret.instit
                );
              end if;

            else

              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  -    update disrec 1 - receita: '||q_disrec.k00_receit,lRaise,false,false);
              end if;

              update disrec set vlrrec = vlrrec + round(v_valor,2)
              where disrec.codcla = vcodcla and disrec.k00_receit = q_disrec.k00_receit and disrec.idret = r_idret.idret and disrec.instit = r_idret.instit;
            end if;

          end if;
        end if;

      end loop;

    end loop;

    if v_estaemrecibopaga is false then
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - nao esta em recibopaga...',lRaise,false,false);
      end if;
    else
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - esta em recibopaga...',lRaise,false,false);
      end if;
    end if;

-- arquivo recibo

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  - verificando recibo...',lRaise,false,false);
      -- TESTE 2 -RECIBO AVULSO
    end if;

    for r_idret in
      select distinct
             disbanco.k00_numpre as numpre,
             disbanco.k00_numpar as numpar,
             disbanco.idret,
             disbanco.k15_codbco,
             disbanco.k15_codage,
             disbanco.k00_numbco,
             disbanco.vlrpago,
             disbanco.vlracres,
             disbanco.vlrdesco,
             disbanco.vlrjuros,
             disbanco.vlrmulta,
             disbanco.instit
        from disbanco
             inner join recibo       on disbanco.k00_numpre       = recibo.k00_numpre
             left join numprebloqpag on numprebloqpag.ar22_numpre = disbanco.k00_numpre
                                    and numprebloqpag.ar22_numpar = disbanco.k00_numpar
       where disbanco.idret  = r_codret.idret
         and disbanco.classi = false
         and disbanco.instit = iInstitSessao
         and numprebloqpag.ar22_sequencial is null
    loop

      v_estaemrecibo := true;

-- Verifica se algum numpre do recibo jÃ¡ esta pago
-- caso positivo passa para o proximo e deixa inconsistente
      perform recibo.k00_numpre
         from recibo
              inner join arrepaga  on arrepaga.k00_numpre = recibo.k00_numpre
                                  and arrepaga.k00_numpar = recibo.k00_numpar
        where recibo.k00_numpre = r_idret.numpre;

      if found then
        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - recibo ja esta pago... gravaidret: '||gravaidret,lRaise,false,false);
        end if;
        continue;
      end if;

      -- Acerta vlrcalc
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - entrou no update vlrcalc (1)...',lRaise,false,false);
      end if;

      -- Acerta vlrcalc
      update disbanco
         set vlrcalc = round((select (substr(fc_calcula,15,13)::float8+
                                substr(fc_calcula,28,13)::float8+
                                substr(fc_calcula,41,13)::float8-
                                substr(fc_calcula,54,13)::float8) as utotal
                          from (select fc_calcula(k00_numpre,k00_numpar,0,dtpago,dtpago,extract(year from dtpago)::integer)
                                  from disbanco
                                 where idret = r_codret.idret
                                   and codret = r_codret.codret
                                   and instit = iInstitSessao
                          ) as x
                       ),2)
       where idret  = r_codret.idret
         and codret = r_codret.codret
         and instit = r_idret.instit;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - saiu no update vlrcalc (1)...',lRaise,false,false);
      end if;

      gravaidret := r_codret.idret;
      retorno    := true;

      -- INSERE NO ARREPAGA OS PAGAMENTOS
      select round(sum(k00_valor),2)
        into valorrecibo
        from recibo
       where k00_numpre = r_idret.numpre;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - xxx - numpre: '||r_idret.numpre||' - valorrecibo: '||valorrecibo||' - vlrpago: '||r_idret.vlrpago,lRaise,false,false);
      end if;

      if valorrecibo = 0 then
        valorrecibo := r_idret.vlrpago;
      end if;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - recibo... vlrpago: '||r_idret.vlrpago||' - valor recibo: '||valorrecibo,lRaise,false,false);
      end if;

     /**
      * Alterado para agrupar por receita quando for recibo avulso para não gerar registros duplicados
      * na arrepaga (k00_numpre k00_numpar k00_receit k00_hist)
      */
      insert into arrepaga (
        k00_numcgm,
        k00_dtoper,
        k00_receit,
        k00_hist,
        k00_valor,
        k00_dtvenc,
        k00_numpre,
        k00_numpar,
        k00_numtot,
        k00_numdig,
        k00_conta,
        k00_dtpaga
      ) select recibo.k00_numcgm,
               min(datausu),
                recibo.k00_receit,
                recibo.k00_hist,
               sum(round((recibo.k00_valor / valorrecibo) * r_idret.vlrpago, 2)),
               min(datausu),
                recibo.k00_numpre,
                recibo.k00_numpar,
               min(recibo.k00_numtot),
               min(recibo.k00_numdig),
               min(conta),
               min(datausu)
           from recibo
                inner join arreinstit on arreinstit.k00_numpre = recibo.k00_numpre
                                     and arreinstit.k00_instit = iInstitSessao
         where recibo.k00_numpre = r_idret.numpre
      group by recibo.k00_numcgm,
               recibo.k00_numpre,
               recibo.k00_numpar,
               recibo.k00_receit,
               recibo.k00_hist;

      -- Verifica se o Total Pago é diferente do que foi Classificado (inserido na Arrepaga)
      v_diferenca := round(r_idret.vlrpago - (select round(sum(k00_valor),2) from arrepaga where k00_numpre = r_idret.numpre), 2);
      if v_diferenca > 0 then

        -- Altera maior receita com a diferenca encontrada
        if lRaise is true then
          perform fc_debug('  <BaixaBanco>  - recibo com diferenca de '||v_diferenca||' no classificado do idret '||r_idret.idret||' (numpre '||r_idret.numpre||' numpar '||r_idret.numpar||')',lRaise,false,false);
        end if;

        update arrepaga
           set k00_valor = k00_valor + v_diferenca
         where k00_numpre = r_idret.numpre
           and k00_receit = (select max(k00_receit) from arrepaga where k00_numpre = r_idret.numpre);
      end if;

      v_diferenca := 0; -- Seta valor anterior para garantir

     -- ALTERA SITUACAO DO ARREPAGA
      for q_disrec in

          select arrepaga.k00_numpre,
                 arrepaga.k00_numpar,
                 arrepaga.k00_receit,
                 sum(round(arrepaga.k00_valor, 2))
            from arrepaga
                 inner join disbanco on disbanco.k00_numpre = arrepaga.k00_numpre
           where arrepaga.k00_numpre = r_idret.numpre
             and disbanco.idret      = r_codret.idret
             and disbanco.instit     = iInstitSessao
        group by arrepaga.k00_numpre,
                 arrepaga.k00_numpar,
                 arrepaga.k00_receit
      loop
        -- INSERE NO ARRECANT
        -- DELETE DO ARRECAD
        -- TESTA SE EXISTE NUMPRE E NUMPAR NO ARREIDRET, NAO EXISTINDO INSERE O IDRET DO PAGAMENTO
        select arreidret.k00_numpre
          into _testeidret
          from arreidret
         where arreidret.k00_numpre = q_disrec.k00_numpre
           and arreidret.k00_numpar = q_disrec.k00_numpar
           and arreidret.idret      = r_idret.idret
           and arreidret.k00_instit = iInstitSessao;

        if _testeidret is null then
          insert into arreidret (
            k00_numpre,
            k00_numpar,
            idret,
            k00_instit
          ) values (
            q_disrec.k00_numpre,
            q_disrec.k00_numpar,
            r_idret.idret,
            r_idret.instit
          );
        end if;

        if q_disrec.sum != 0 then
          if autentsn is false then
-- GRAVA DISREC DAS RECEITAS PARA A CLASSIFICACAO
            lVerificaReceita := fc_verificareceita(q_disrec.k00_receit);
            if lVerificaReceita is false then

              codigo_retorno := 25;
              descricao := 'Receita: '||q_disrec.k00_receit||' não encontrada verifique o cadastro (2).';
              return;
            end if;

            perform *
               from disrec
              where disrec.codcla     = vcodcla
                and disrec.k00_receit = q_disrec.k00_receit
                and disrec.idret      = r_idret.idret
                and disrec.instit     = r_idret.instit;
            if not found then
              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  - into disrec 2 - valor: '||q_disrec.sum||' - receita: '||q_disrec.k00_receit,lRaise,false,false);
              end if;


              if round(q_disrec.sum,2) > 0 then

                insert into disrec (
                  codcla,
                  k00_receit,
                  vlrrec,
                  idret,
                  instit
                ) values (
                  vcodcla,
                  q_disrec.k00_receit,
                  round(q_disrec.sum,2),
                  r_idret.idret,
                  r_idret.instit
                );

             end if;

            else
              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  -    update disrec 2 - receita: '||q_disrec.k00_receit,lRaise,false,false);
              end if;
              update disrec set vlrrec = vlrrec + round(v_valor,2)
              where disrec.codcla = vcodcla and disrec.k00_receit = q_disrec.k00_receit and disrec.idret = r_idret.idret and disrec.instit = r_idret.instit;
            end if;
            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - into disrec 3',lRaise,false,false);
            end if;
          end if;
        end if;

      end loop;

    end loop;

    if v_estaemrecibo is false then
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - nao esta em recibo...',lRaise,false,false);
      end if;
    else
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - esta em recibo...',lRaise,false,false);
      end if;
    end if;

    ----
    ---- PROCURANDO ARRECAD
    ----

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  - verificando arrecad...',lRaise,false,false);
      -- TESTE 3 - ARRECAD
    end if;

    for r_idret in
      select distinct
             1 as tipo,
             disbanco.k00_numpre as numpre,
             disbanco.k00_numpar as numpar,
             disbanco.idret,
             disbanco.k15_codbco,
             disbanco.k15_codage,
             disbanco.k00_numbco,
             disbanco.vlrpago,
             disbanco.vlracres,
             disbanco.vlrdesco,
             disbanco.vlrjuros,
             disbanco.vlrmulta,
             disbanco.instit
        from disbanco
             inner join arrecad      on arrecad.k00_numpre = disbanco.k00_numpre
                                    and arrecad.k00_numpar = disbanco.k00_numpar
             inner join arreinstit   on arreinstit.k00_numpre = arrecad.k00_numpre
                                    and arreinstit.k00_instit = iInstitSessao
             left join arrepaga      on arrepaga.k00_numpre = arrecad.k00_numpre
                                    and arrepaga.k00_numpar = arrecad.k00_numpar
                                    and arrepaga.k00_receit = arrecad.k00_receit
             left join numprebloqpag on numprebloqpag.ar22_numpre = arrecad.k00_numpre
                                    and numprebloqpag.ar22_numpar = arrecad.k00_numpar
       where disbanco.idret  = r_codret.idret
         and disbanco.classi is false
         and disbanco.instit = iInstitSessao
         and arrepaga.k00_numpre is null
         and numprebloqpag.ar22_sequencial is null
      union
      select distinct
             2 as tipo,
             disbanco.k00_numpre as numpre,
             disbanco.k00_numpar as numpar,
             disbanco.idret,
             disbanco.k15_codbco,
             disbanco.k15_codage,
             disbanco.k00_numbco,
             disbanco.vlrpago,
             disbanco.vlracres,
             disbanco.vlrdesco,
             disbanco.vlrjuros,
             disbanco.vlrmulta,
             disbanco.instit
        from disbanco
             inner join arrecad      on arrecad.k00_numpre = disbanco.k00_numpre
                                    and disbanco.k00_numpar = 0
             inner join arreinstit   on arreinstit.k00_numpre = arrecad.k00_numpre
                                    and arreinstit.k00_instit = iInstitSessao
             left join arrepaga      on arrepaga.k00_numpre = arrecad.k00_numpre
                                    and arrepaga.k00_numpar = arrecad.k00_numpar
                                    and arrepaga.k00_receit = arrecad.k00_receit
             left join numprebloqpag on numprebloqpag.ar22_numpre = arrecad.k00_numpre
                                    and numprebloqpag.ar22_numpar = arrecad.k00_numpar
       where disbanco.idret = r_codret.idret
         and disbanco.classi is false
         and disbanco.instit = iInstitSessao
         and arrepaga.k00_numpre is null
         and numprebloqpag.ar22_sequencial is null
    loop

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - ###### - tipo: '||r_idret.tipo,lRaise,false,false);
      end if;

      retorno := true;

      if r_idret.numpar = 0 then
        v_estaemarrecadunica  := true;
      else
        v_estaemarrecadnormal := true;
      end if;

      -- INSERE NO DISBANCO O VALOR CORRETO DO PAGAMENTO
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - codret : '||r_codret.codret||'-idret : '||r_codret.idret,lRaise,false,false);
      end if;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - arrecad - numpre: '||r_idret.numpre||' - numpar: '||r_idret.numpar||' - tot: '||x_totreg||' - pago: '||r_idret.vlrpago,lRaise,false,false);
      end if;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - entrou no update vlrcalc...',lRaise,false,false);
      end if;

        -- Acerta vlrcalc
        update disbanco
           set vlrcalc = round((select (substr(fc_calcula,15,13)::float8+
                                  substr(fc_calcula,28,13)::float8+
                                  substr(fc_calcula,41,13)::float8-
                                  substr(fc_calcula,54,13)::float8) as utotal
                            from (select fc_calcula(k00_numpre,k00_numpar,0,dtpago,dtpago,extract(year from dtpago)::integer)
                                    from disbanco
                                   where idret = r_codret.idret
                                     and codret = r_codret.codret
                                     and instit = iInstitSessao
                            ) as x
                         ),2)
         where idret  = r_codret.idret
           and codret = r_codret.codret
           and instit = r_idret.instit;

      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - saiu no update vlrcalc...',lRaise,false,false);
      end if;

      if not r_idret.numpre is null then

        if r_idret.numpar != 0 then

          -- TESTE 3.1 - ARRECAD COM PARCELA PREENCHIDA

          valortotal := r_idret.vlrpago+r_idret.vlracres-r_idret.vlrdesco;
          valorjuros := r_idret.vlrjuros;
          valormulta := r_idret.vlrmulta;

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  - valortotal: '||valortotal,lRaise,false,false);
          end if;

          select round(sum(arrecad.k00_valor),2) as k00_vlrtot
            into nVlrTfr
            from arrecad
                 inner join arreinstit on arreinstit.k00_numpre = arrecad.k00_numpre
           where arrecad.k00_numpre    = r_idret.numpre
             and arrecad.k00_numpar    = r_idret.numpar
             and arreinstit.k00_instit = r_idret.instit;

          primeirarec := 0;
          valorlanc   := 0;
          valorlancj  := 0;
          valorlancm  := 0;
          for r_receitas in
              select k00_numcgm,
                     k00_numtot,
                     k00_numdig,
                     k00_receit,
                     round(sum(k00_valor),2)::float8 as k00_valor,
                     k02_recjur,
                     k02_recmul
                from arrecad
                     inner join arreinstit on arreinstit.k00_numpre = arrecad.k00_numpre
                     inner join tabrec     on tabrec.k02_codigo     = arrecad.k00_receit
                     inner join tabrecjm   on tabrec.k02_codjm      = tabrecjm.k02_codjm
               where arrecad.k00_numpre    = r_idret.numpre
                 and arrecad.k00_numpar    = r_idret.numpar
                 and arreinstit.k00_instit = r_idret.instit
            group by k00_numcgm,
                     k00_numtot,
                     k00_numdig,
                     k00_receit,
                     k02_recjur,
                     k02_recmul
          loop

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - inicio do for...',lRaise,false,false);
              perform fc_debug('  <BaixaBanco>  - ==========',lRaise,false,false);
            end if;

            if r_receitas.k00_valor = 0 then
              fracao := 1::float8;
            else
              fracao := round((r_receitas.k00_valor*100)::float8/nVlrTfr,8)::float8/100::float8;
            end if;

            nVlrRec := to_char(round(valortotal * fracao,2),'9999999999999.99')::float8;

            -- juros
            nVlrRecj := to_char(round(valorjuros * fracao,2),'9999999999999.99')::float8;

            -- multa
            nVlrRecm := to_char(round(valormulta * fracao,2),'9999999999999.99')::float8;

            if lRaise then
              perform fc_debug('  <BaixaBanco>  - JUROS : '||nVlrRecj||' RECEITA : '||r_receitas.k02_recjur,lRaise,false,false);
              perform fc_debug('  <BaixaBanco>  - MULTA : '||nVlrRecm||' RECEITA : '||r_receitas.k02_recmul,lRaise,false,false);
              perform fc_debug('  <BaixaBanco>  - VALOR : '||nVlrRec ||' RECEITA : '||r_receitas.k00_receit,lRaise,false,false);
            end if;

            if r_receitas.k02_recjur = r_receitas.k02_recmul then
              nVlrRecj := nVlrRecj + nVlrRecm;
              nVlrRecm := 0;
            end if;

            if r_receitas.k02_recjur is null then
              nVlrRec  := nVlrRecm + nVlrRecj;
              nVlrRecj := 0;
              nVlrRecm := 0;
            end if;

            gravaidret := r_codret.idret;

            --
            -- Inserindo o valor da receita
            --
            if nVlrRec != 0 then
              if primeirarec = 0 then
                primeirarec := r_receitas.k00_receit;
              end if;
              valorlanc := round(valorlanc + nVlrRec,2)::float8;
              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  - valorlanc: '||valorlanc,lRaise,false,false);
              end if;

              insert into arrepaga  (
                k00_numcgm,
                k00_dtoper,
                k00_receit,
                k00_hist  ,
                k00_valor ,
                k00_dtvenc,
                k00_numpre,
                k00_numpar,
                k00_numtot,
                k00_numdig,
                k00_conta ,
                k00_dtpaga
              ) values (
                r_receitas.k00_numcgm,
                datausu,
                r_receitas.k00_receit  ,
                991,
                nVlrRec,
                datausu ,
                r_idret.numpre,
                r_idret.numpar ,
                r_receitas.k00_numtot ,
                r_receitas.k00_numdig ,
                conta,
                datausu
              );
            end if;

            --
            -- Inserindo o valor do juros
            --
            if round(nVlrRecj,2)::float8 != 0 then
              if primeirarecj = 0 then
                primeirarecj := r_receitas.k02_recjur;
              end if;
              valorlancj := round(valorlancj + nVlrRecj,2)::float8;

              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  - Valor do juros '||nVlrRecj,lRaise,false,false);
                perform fc_debug('  <BaixaBanco>  - valorlancj: '||valorlancj,lRaise,false,false);
              end if;

              insert into arrepaga (
                k00_numcgm,
                k00_dtoper,
                k00_receit,
                k00_hist  ,
                k00_valor ,
                k00_dtvenc,
                k00_numpre,
                k00_numpar,
                k00_numtot,
                k00_numdig,
                k00_conta ,
                k00_dtpaga
              ) values (
                r_receitas.k00_numcgm,
                datausu,
                r_receitas.k02_recjur ,
                991,
                round(nVlrRecj,2)::float8,
                datausu,
                r_idret.numpre,
                r_idret.numpar ,
                r_receitas.k00_numtot ,
                r_receitas.k00_numdig  ,
                conta,
                datausu
              );
            end if;

            --
            -- Inserindo o valor da multa
            --
            if round(nVlrRecm,2)::float8 != 0 then

              if lRaise then
                perform fc_debug('  <BaixaBanco>  - Valor da multa : '||round(nVlrRecm,2),lRaise,false,false);
              end if;

              if primeirarecm = 0 then
                primeirarecm := r_receitas.k02_recmul;
              end if;
              valorlancm := round(valorlancm + nVlrRecm,2)::float8;

              insert into arrepaga (
                k00_numcgm,
                k00_dtoper,
                k00_receit,
                k00_hist  ,
                k00_valor ,
                k00_dtvenc,
                k00_numpre,
                k00_numpar,
                k00_numtot,
                k00_numdig,
                k00_conta ,
                k00_dtpaga
              ) values (
                r_receitas.k00_numcgm,
                datausu,
                r_receitas.k02_recmul ,
                991 ,
                round(nVlrRecm,2)::float8,
                datausu  ,
                r_idret.numpre,
                r_idret.numpar ,
                r_receitas.k00_numtot ,
                r_receitas.k00_numdig  ,
                conta,
                datausu
              );
            else
              if lRaise then
                perform fc_debug('  <BaixaBanco>  - nao processou multa - valor da multa : '||round(nVlrRecm,2),lRaise,false,false);
              end if;
            end if;

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - final do for...',lRaise,false,false);
              perform fc_debug('  <BaixaBanco>  - ==========',lRaise,false,false);
            end if;

          end loop;

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  - ==========',lRaise,false,false);
            perform fc_debug('  <BaixaBanco>  - fora do for...',lRaise,false,false);
            perform fc_debug('  <BaixaBanco>  - ==========',lRaise,false,false);
          end if;

          valorlanc := round(valortotal - (valorlanc),2)::float8;

          if valorlanc != 0 then
            select oid
              into oidrec
              from arrepaga
             where k00_numpre = r_idret.numpre
               and k00_numpar = r_idret.numpar
               and k00_receit = primeirarec;

            update arrepaga
               set k00_valor = round(k00_valor + valorlanc,2)::float8
             where oid = oidrec ;
          end if;

          valorlancj := round(valorjuros - (valorlancj),2)::float8;
          if valorlancj != 0 then

            if lRaise then
              perform fc_debug('  <BaixaBanco>  - Somando juros na receira principal : '||valorlancj,lRaise,false,false);
            end if;

            select oid
              into oidrec
              from arrepaga
             where k00_numpre = r_idret.numpre
               and k00_numpar = r_idret.numpar
               and k00_receit = primeirarecj;

            -- comentei para teste

            update arrepaga
               set k00_valor = round(k00_valor + valorlancj,2)::float8
             where oid = oidrec;

          end if;

          valorlancm := round(valormulta - (valorlancm),2)::float8;
          if valorlancm != 0 then
            select oid
              into oidrec
              from arrepaga
             where k00_numpre = r_idret.numpre
               and k00_numpar = r_idret.numpar
               and k00_receit = primeirarecm;

            update arrepaga
               set k00_valor = round(k00_valor + valorlancm,2)::float8
             where oid = oidrec;

          end if;

          for q_disrec in
              select k00_receit,
                     round(sum(k00_valor),2) as sum
                from arrepaga
               where k00_numpre = r_idret.numpre
                 and k00_numpar = r_idret.numpar
                 and k00_dtoper = datausu
            group by k00_receit
          loop
            if q_disrec.sum != 0 then
              if autentsn is false then

                lVerificaReceita := fc_verificareceita(q_disrec.k00_receit);
                if lVerificaReceita is false then

                  codigo_retorno := 26;
                  descricao := 'Receita: '||q_disrec.k00_receit||' nao encontrada verifique o cadastro (3).';
                  return;
                end if;

                perform *
                   from disrec
                  where disrec.codcla = vcodcla
                    and disrec.k00_receit = q_disrec.k00_receit
                    and disrec.idret      = r_idret.idret
                    and disrec.instit     = r_idret.instit;
                if not found then
                  if lRaise is true then
                    perform fc_debug('  <BaixaBanco>  - into disrec 4 - valor: '||q_disrec.sum||' - receita: '||q_disrec.k00_receit,lRaise,false,false);
                  end if;

                  if round(q_disrec.sum,2) > 0 then

                    insert into disrec (
                      codcla,
                      k00_receit,
                      vlrrec,
                      idret,
                      instit
                    ) values (
                      vcodcla,
                      q_disrec.k00_receit,
                      round(q_disrec.sum,2),
                      r_idret.idret,
                      r_idret.instit
                    );

                  end if;

                else

                  if lRaise is true then
                    perform fc_debug('  <BaixaBanco>  -    update disrec 4 - receita: '||q_disrec.k00_receit,lRaise,false,false);
                  end if;

                  update disrec
                     set vlrrec = vlrrec + round(v_valor,2)
                   where disrec.codcla     = vcodcla
                     and disrec.k00_receit = q_disrec.k00_receit
                     and disrec.idret      = r_idret.idret
                     and disrec.instit     = r_idret.instit;

                end if;
                if lRaise is true then
                  perform fc_debug('  <BaixaBanco>  - into disrec 5',lRaise,false,false);
                end if;
              end if;
            end if;
          end loop;

          insert into arrecant (
            k00_numcgm,
            k00_dtoper,
            k00_receit,
            k00_hist  ,
            k00_valor ,
            k00_dtvenc,
            k00_numpre,
            k00_numpar,
            k00_numtot,
            k00_numdig,
            k00_tipo  ,
            k00_tipojm
          ) select arrecad.k00_numcgm,
                   arrecad.k00_dtoper,
                   arrecad.k00_receit,
                   arrecad.k00_hist  ,
                   arrecad.k00_valor ,
                   arrecad.k00_dtvenc,
                   arrecad.k00_numpre,
                   arrecad.k00_numpar,
                   arrecad.k00_numtot,
                   arrecad.k00_numdig,
                   arrecad.k00_tipo  ,
                   arrecad.k00_tipojm
              from arrecad
                   inner join arreinstit on arreinstit.k00_numpre = arrecad.k00_numpre
             where arrecad.k00_numpre = r_idret.numpre
               and arrecad.k00_numpar = r_idret.numpar
               and arreinstit.k00_instit = r_idret.instit;

          delete
            from arrecad
           using arreinstit
           where arrecad.k00_numpre    = arreinstit.k00_numpre
             and arrecad.k00_numpre    = r_idret.numpre
             and arrecad.k00_numpar    = r_idret.numpar
             and arreinstit.k00_instit = r_idret.instit;

-- TESTA SE EXISTE NUMPRE E NUMPAR NO ARREIDRET, NAO EXISTINDO INSERE O IDRET DO PAGAMENTO
          select arreidret.k00_numpre
            into _testeidret
            from arreidret
           where arreidret.k00_numpre = r_idret.numpre
             and arreidret.k00_numpar = r_idret.numpar
             and arreidret.idret      = r_idret.idret
             and arreidret.k00_instit = r_idret.instit;

          if _testeidret is null then
            insert into arreidret (
              k00_numpre,
              k00_numpar,
              idret,
              k00_instit
            ) values (
              r_idret.numpre,
              r_idret.numpar,
              r_idret.idret,
              r_idret.instit
            );
          end if;

        else
          -- PARCELA UNICA
          -- TESTE 3.2 - ARRECAD COM PARCELA UNICA

          valortotal := r_idret.vlrpago+r_idret.vlracres-r_idret.vlrdesco;
          valorjuros := r_idret.vlrjuros;
          valormulta := r_idret.vlrmulta;

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  -  unica - vlrtot '||valortotal||' - numpre: '||r_idret.numpre,lRaise,false,false);
          end if;

          select round(sum(arrecad.k00_valor),2) as k00_vlrtot
            into nVlrTfr
            from arrecad
                 inner join arreinstit on arreinstit.k00_numpre = arrecad.k00_numpre
           where arrecad.k00_numpre    = r_idret.numpre
             and arreinstit.k00_instit = r_idret.instit;

          primeirarec := 0;
          valorlanc   := 0;
          valorlancj  := 0;
          valorlancm  := 0;

          for r_idunica in
            select distinct
                   arrecad.k00_numpre as numpre,
                   arrecad.k00_numpar as numpar
              from arrecad
                   inner join arreinstit on arreinstit.k00_numpre = arrecad.k00_numpre
             where arrecad.k00_numpre    = r_idret.numpre
               and arreinstit.k00_instit = r_idret.instit
          loop

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - dentro do for do arrecad - parcela: '||r_idunica.numpar,lRaise,false,false);
            end if;

            for r_receitas in
                select k00_numcgm,
                       k00_numtot,
                       k00_numdig,
                       k00_receit,
                       k00_tipo,
                       round(sum(k00_valor),2)::float8 as k00_valor,
                       k02_recjur,
                       k02_recmul
                  from arrecad
                       inner join arreinstit on arreinstit.k00_numpre = arrecad.k00_numpre
                       inner join tabrec     on tabrec.k02_codigo     = arrecad.k00_receit
                       inner join tabrecjm   on tabrec.k02_codjm      = tabrecjm.k02_codjm
                 where arrecad.k00_numpre    = r_idunica.numpre
                   and arrecad.k00_numpar    = r_idunica.numpar
                   and arreinstit.k00_instit = r_idret.instit
              group by k00_numcgm,
                       k00_numtot,
                       k00_numdig,
                       k00_receit,
                       k00_tipo,
                       k02_recjur,
                       k02_recmul
            loop

              --
              -- ModificaÃ§ao realizada devido ao erro gerado na tarefa 32607
              -- Motivo do erro:
              -- Foi pego o valor de 72.83 para um numpre de ISSQN Var, quando o arquivo do banco retornou, o  estava com valor zero no arrecad
              -- O que ocasionava erro nas linhas abaixo pois a variavel nVlrTfr que e resultado do somatorio do valor do  na tabela arrecad e
              -- utilizado para a divisao do valor da receita abaixo, estava igual a zero.
              --
              if r_receitas.k00_tipo = 3 and nVlrTfr = 0 then
                fracao := 100;
              else
                fracao := round((r_receitas.k00_valor*100)::float8/nVlrTfr,8)::float8/100::float8;
              end if;
              --
              -- fim da modificacao
              --


              nVlrRec := round(to_char(round(valortotal * fracao,2),'9999999999999.99')::float8,2)::float8;

              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  -  rec '||r_receitas.k00_receit||' nVlrRec '||nVlrRec,lRaise,false,false);
              end if;

              -- juros
              nVlrRecj := round(to_char(round(valorjuros * fracao,2),'9999999999999.99')::float8,2)::float8;

              -- multa
              nVlrRecm := round(to_char(round(valormulta * fracao,2),'9999999999999.99')::float8,2)::float8;

              if r_receitas.k02_recjur = r_receitas.k02_recmul then
                nVlrRecj := nVlrRecj + nVlrRecm;
                nVlrRecm := 0;
              end if;

              if r_receitas.k02_recjur is null then
                nVlrRec  := nVlrRecm + nVlrRecj;
                nVlrRecj := 0;
                nVlrRecm := 0;
              end if;

              gravaidret := r_codret.idret;

              if lRaise is true then
                perform fc_debug('  <BaixaBanco>  - nVlrRec: '||nVlrRec,lRaise,false,false);
              end if;

              if nVlrRec != 0 then
                if primeirarec = 0 then
                  primeirarec := r_receitas.k00_receit;
                end if;
                primeiranumpre := r_idunica.numpre;
                primeiranumpar := r_idunica.numpar;
                valorlanc      := round(valorlanc + nVlrRec,2)::float8;

                insert into arrepaga (
                  k00_numcgm,
                  k00_dtoper,
                  k00_receit,
                  k00_hist,
                  k00_valor,
                  k00_dtvenc,
                  k00_numpre,
                  k00_numpar,
                  k00_numtot,
                  k00_numdig,
                  k00_conta,
                  k00_dtpaga
                ) values (
                  r_receitas.k00_numcgm,
                  datausu,
                  r_receitas.k00_receit  ,
                  990 ,
                  round(nVlrRec,2)::float8,
                  datausu  ,
                  r_idunica.numpre,
                  r_idunica.numpar ,
                  r_receitas.k00_numtot,
                  r_receitas.k00_numdig  ,
                  conta,
                  datausu
                );
              end if;

              if round(nVlrRecj,2)::float8 != 0 then
                if primeirarecj = 0 then
                  primeirarecj := r_receitas.k02_recjur;
                end if;
                primeiranumpre := r_idunica.numpre;
                primeiranumpar := r_idunica.numpar;
                valorlancj     := round(valorlancj + nVlrRecj,2)::float8;
                if lRaise is true then
                  perform fc_debug('  <BaixaBanco>  - juros '||nVlrRecj,lRaise,false,false);
                end if;

                insert into arrepaga (
                  k00_numcgm,
                  k00_dtoper,
                  k00_receit,
                  k00_hist  ,
                  k00_valor ,
                  k00_dtvenc,
                  k00_numpre,
                  k00_numpar,
                  k00_numtot,
                  k00_numdig,
                  k00_conta ,
                  k00_dtpaga
                ) values (
                  r_receitas.k00_numcgm,
                  datausu,
                  r_receitas.k02_recjur ,
                  990,
                  round(nVlrRecj,2)::float8,
                  datausu,
                  r_idunica.numpre,
                  r_idunica.numpar ,
                  r_receitas.k00_numtot ,
                  r_receitas.k00_numdig  ,
                  conta,
                  datausu
                );
              end if;

              if round(nVlrRecm,2)::float8 != 0 then
                if primeirarecm = 0 then
                  primeirarecm := r_receitas.k02_recmul;
                end if;
                primeiranumpre := r_idunica.numpre;
                primeiranumpar := r_idunica.numpar;
                valorlancm     := round(valorlancm + nVlrRecm,2)::float8;

                insert into arrepaga (
                  k00_numcgm,
                  k00_dtoper,
                  k00_receit,
                  k00_hist  ,
                  k00_valor ,
                  k00_dtvenc,
                  k00_numpre,
                  k00_numpar,
                  k00_numtot,
                  k00_numdig,
                  k00_conta ,
                  k00_dtpaga
                ) values (
                  r_receitas.k00_numcgm,
                  datausu,
                  r_receitas.k02_recmul ,
                  990 ,
                  round(nVlrRecm,2)::float8,
                  datausu ,
                  r_idunica.numpre,
                  r_idunica.numpar ,
                  r_receitas.k00_numtot ,
                  r_receitas.k00_numdig  ,
                  conta,
                  datausu
                );
              end if;

            end loop;

            insert into arrecant (
              k00_numcgm,
              k00_dtoper,
              k00_receit,
              k00_hist  ,
              k00_valor ,
              k00_dtvenc,
              k00_numpre,
              k00_numpar,
              k00_numtot,
              k00_numdig,
              k00_tipo  ,
              k00_tipojm
            ) select arrecad.k00_numcgm,
                     arrecad.k00_dtoper,
                     arrecad.k00_receit,
                     arrecad.k00_hist  ,
                     arrecad.k00_valor ,
                     arrecad.k00_dtvenc,
                     arrecad.k00_numpre,
                     arrecad.k00_numpar,
                     arrecad.k00_numtot,
                     arrecad.k00_numdig,
                     arrecad.k00_tipo  ,
                     arrecad.k00_tipojm
                from arrecad
                     inner join arreinstit on arreinstit.k00_numpre = arrecad.k00_numpre
               where arrecad.k00_numpre    = r_idunica.numpre
                 and arrecad.k00_numpar    = r_idunica.numpar
                 and arreinstit.k00_instit = r_idret.instit;

            delete
              from arrecad
             using arreinstit
             where arrecad.k00_numpre    = arreinstit.k00_numpre
               and arrecad.k00_numpre    = r_idunica.numpre
               and arrecad.k00_numpar    = r_idunica.numpar
               and arreinstit.k00_instit = r_idret.instit;
-- TESTA SE EXISTE NUMPRE E NUMPAR NO ARREIDRET, NAO EXISTINDO INSERE O IDRET DO PAGAMENTO
            select arreidret.k00_numpre
              into _testeidret
              from arreidret
             where arreidret.k00_numpre = r_idunica.numpre
               and arreidret.k00_numpar = r_idunica.numpar
               and arreidret.idret      = r_idret.idret
               and arreidret.k00_instit = r_idret.instit;

            if _testeidret is null then
              insert into arreidret (
                k00_numpre,
                k00_numpar,
                idret,
                k00_instit
              ) values (
                r_idunica.numpre,
                r_idunica.numpar,
                r_idret.idret,
                r_idret.instit
              );
            end if;

          end loop;

          valorlanc  := round(valortotal - (valorlanc),2)::float8;
          valorlancj := round(valorjuros - (valorlancj),2)::float8;
          valorlancm := round(valormulta - (valorlancm),2)::float8;

          IF VALORLANC != 0  THEN

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  -  acerta 1 -- '||valorlanc,lRaise,false,false);
            end if;

            select oid
              into oidrec
              from arrepaga
             where k00_numpre = primeiranumpre
               and k00_numpar = primeiranumpar
               and k00_receit = primeirarec;

            update arrepaga
               set k00_valor = round(k00_valor + valorlanc,2)::float8
             where oid = oidrec ;
          end if;

          if valorlancj != 0 then

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  -  acerta 2 -- '||valorlancj,lRaise,false,false);
            end if;

            select oid
              into oidrec
              from arrepaga
             where k00_numpre = primeiranumpre
               and k00_numpar = primeiranumpar
               and k00_receit = primeirarecj;

            update arrepaga
               set k00_valor = round(k00_valor + valorlancj,2)::float8
             where oid = oidrec;

          end if;

          if valorlancm != 0 then

            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  -  acerta 3  -- '||valorlancm,lRaise,false,false);
            end if;

            select oid
              into oidrec
              from arrepaga
             where k00_numpre = primeiranumpre
               and k00_numpar = primeiranumpar
               and k00_receit = primeirarecm;

            update arrepaga
               set k00_valor = round(k00_valor + valorlancm,2)::float8
             where oid = oidrec;

          end if;

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  - antes for disrec - datausu: '||datausu,lRaise,false,false);
          end if;

          for q_disrec in
              select k00_receit,
                     round(sum(k00_valor),2) as sum
                from arrepaga
               where k00_numpre = r_idret.numpre
--                and k00_numpar = r_idret.numpar
                 and k00_dtoper = datausu
            group by k00_receit
          loop
            if q_disrec.sum != 0 then
              if autentsn is false then
                if lRaise is true then
                  perform fc_debug('  <BaixaBanco>  - into disrec 6',lRaise,false,false);
                end if;

                lVerificaReceita := fc_verificareceita(q_disrec.k00_receit);
                if lVerificaReceita is false then

                  codigo_retorno := 27;
                  descricao := 'Receita: '||q_disrec.k00_receit||' nao encontrada verifique o cadastro (4).';
                  return;
                end if;

                perform * from disrec where disrec.codcla = vcodcla and disrec.k00_receit = q_disrec.k00_receit and disrec.idret = r_idret.idret and disrec.instit = r_idret.instit;
                if not found then
                  if lRaise is true then
                    perform fc_debug('  <BaixaBanco>  -    inserindo disrec 6 - valor: '||q_disrec.sum||' - receita: '||q_disrec.k00_receit,lRaise,false,false);
                  end if;

                  if round(q_disrec.sum,2) > 0 then

                    insert into disrec (
                      codcla,
                      k00_receit,
                      vlrrec,
                      idret,
                      instit
                    ) values (
                      vcodcla,
                      q_disrec.k00_receit,
                      round(q_disrec.sum,2),
                      r_idret.idret,
                      r_idret.instit
                    );

                  end if;

                else
                  if lRaise is true then
                    perform fc_debug('  <BaixaBanco>  -    update disrec 6 - receita: '||q_disrec.k00_receit,lRaise,false,false);
                  end if;
                  update disrec set vlrrec = vlrrec + round(v_valor,2)
                  where disrec.codcla = vcodcla and disrec.k00_receit = q_disrec.k00_receit and disrec.idret = r_idret.idret and disrec.instit = r_idret.instit;
                end if;
              end if;
            end if;
            if lRaise is true then
              perform fc_debug('  <BaixaBanco>  - durante for disrec',lRaise,false,false);
            end if;
          end loop;

          if lRaise is true then
            perform fc_debug('  <BaixaBanco>  - depois for disrec',lRaise,false,false);
          end if;

        end if;

      end if;

    end loop;

    if v_estaemarrecadnormal is false then
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - nao esta em arrecad normal...',lRaise,false,false);
      end if;
    else
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - esta em arrecad normal...',lRaise,false,false);
      end if;
    end if;

    if v_estaemarrecadunica is false then
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - nao esta em arrecad unica...',lRaise,false,false);
      end if;
    else
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - esta em arrecad unica...',lRaise,false,false);
      end if;
    end if;

-- pelo numpre do arrecad
    if gravaidret != 0 then
      if autentsn is false then
        insert into disclaret (
          codcla,
          codret
        ) values (
          vcodcla,
          r_codret.idret
        );
      end if;

      select ar22_sequencial
        into nBloqueado
        from numprebloqpag
             inner join disbanco on disbanco.k00_numpre = numprebloqpag.ar22_numpre
                                and disbanco.k00_numpar = numprebloqpag.ar22_numpar
        where disbanco.idret = r_codret.idret;

      if nBloqueado is not null and nBloqueado > 0 then
        lClassi = false;
      else
        lClassi = true;
      end if;

      if lRaise is true then
        if lClassi is true then
          perform fc_debug('  <BaixaBanco>  -  3 - Debito nao Bloqueado ',lRaise,false,false);
        else
          perform fc_debug('  <BaixaBanco>  -  4 - Debito Bloqueado '||r_codret.idret,lRaise,false,false);
        end if;
      end if;

      update disbanco
         set classi = lClassi
       where idret = r_codret.idret;
    else
      if lRaise is true then
        perform fc_debug('  <BaixaBanco>  - classi is false',lRaise,false,false);
      end if;
    end if;

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  - finalizando registro disbanco - idret '||R_CODRET.IDRET,lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    end if;

  end loop;

  if lRaise is true then
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  - fim PROCESSANDO REGISTROS...',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  -  ',lRaise,false,false);
  end if;

  select sum(round(tmpantesprocessar.vlrpago,2))
    into v_total1
    from tmpantesprocessar
         inner join disbanco on tmpantesprocessar.idret = disbanco.idret
   where disbanco.classi is true;

  if lRaise is true then
    perform fc_debug('  <BaixaBanco>  - ===============',lRaise,false,false);
    perform fc_debug('  <BaixaBanco>  - VCODCLA: '||VCODCLA,lRaise,false,false);
  end if;

  if autentsn is false then

    select sum(round(disrec.vlrrec,2))
      into v_total2
      from disrec
     where disrec.codcla = VCODCLA;

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  ',lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  |1| v_total1 (soma disbanco.vlrpago): '||v_total1||' - v_total2 (soma disrec.vlrrec): '||v_total2,lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  ',lRaise,false,false);
    end if;

    perform distinct
            disbanco.idret,
            disrec.idret
       from tmpantesprocessar
            inner join disbanco  on disbanco.idret = tmpantesprocessar.idret
                                and disbanco.classi is true
            left  join disrec    on disrec.idret = disbanco.idret
      where disrec.idret is null;

    if found and autentsn is false then

      codigo_retorno := 28;
      descricao := 'REGISTROS CLASSIFICADOS SEM DISREC';
      return;
    end if;

    v_diferenca = ( v_total1 - v_total2 );

    if cast(round(v_diferenca,2) as numeric) <> cast(round(0,2) as numeric) then

      if lRaise is true then
        perform fc_debug('============================',lRaise,false,false);
        perform fc_debug('<BaixaBanco> - Executar Acerto',lRaise,false,false);
        perform fc_debug('<BaixaBanco> - CodRet: '||cod_ret,lRaise,false,false);
        perform fc_debug('============================',lRaise,false,false);
      end if;

      for rAcertoDiferenca in  select idret,
                                      vlrpago as valor_disbanco,
                                      ( select sum(vlrrec)
                                          from disrec
                                         where disrec.idret = disbanco.idret) as valor_disrec
                                 from disbanco
                                where codret = cod_ret
                                  and cast(round(vlrpago,2) as numeric) <> cast(round((select sum(vlrrec)
                                                                                        from disrec
                                                                                       where disrec.idret = disbanco.idret),2) as numeric)
      loop

        nVlrDiferencaDisrec := ( rAcertoDiferenca.valor_disbanco - rAcertoDiferenca.valor_disrec );

        if lRaise is true then
          perform fc_debug('  <BaixaBanco> - Acerto de diferenca disrec | idret : '||rAcertoDiferenca.idret,lRaise,false,false);
          perform fc_debug('  <BaixaBanco> - valor disbanco : '||rAcertoDiferenca.valor_disbanco           ,lRaise,false,false);
          perform fc_debug('  <BaixaBanco> - valor disrec : '||rAcertoDiferenca.valor_disrec               ,lRaise,false,false);
        end if;

        update disrec
           set vlrrec = ( vlrrec + nVlrDiferencaDisrec )
         where idret  = rAcertoDiferenca.idret
           and codcla = VCODCLA
           and k00_receit = (select k00_receit
                               from disrec
                              where idret = rAcertoDiferenca.idret
                              order by vlrrec
                               desc limit 1);

      end loop;

      select sum(round(disrec.vlrrec,2))
        into v_total2
        from disrec
       where disrec.codcla
       in (select codigo_classificacao
               from tmp_classificaoesexecutadas);

       perform fc_debug('  <BaixaBanco> - v_total2 ( soma disrec ) : ' ||v_total2, lRaise, false, false);
    end if;

    if lRaise is true then
      perform fc_debug('  <BaixaBanco>  ',lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  |2| v_total1 (soma disbanco.vlrpago): '||v_total1||' - v_total2 (soma disrec.vlrrec): '||v_total2,lRaise,false,false);
      perform fc_debug('  <BaixaBanco>  ',lRaise,false,false);
    end if;

    if v_total1 <> v_total2 then

      codigo_retorno := 29;
      descricao := 'INCONSISTENCIA NOS VALORES PROCESSADOS DIFERENCA TOTAL DE '||(v_total1-v_total2);
      return;
    end if;

  end if;

  if lRaise is true then
    perform fc_debug('  <BaixaBanco>  - FIM DO PROCESSAMENTO... ',lRaise,false,true);
  end if;

  if retorno = false then

    codigo_retorno := 30;
    descricao := 'NAO EXISTEM DEBITOS PENDENTES PARA ESTE ARQUIVO';
    return;
  else

    codigo_retorno := 1;
    descricao := 'PROCESSO CONCLUIDO COM SUCESSO';
    return;
  end if;

end;

$$ language 'plpgsql';-- quando caracteristica 441 e tem mais de um logradouro, o sistema calcula 30% a mais
-- V_TEM_CARACT_ESQUINA = 441 quando encontrada no carlot
-- no iptucalc a area gravada e a area corrigidae nao a area real

set check_function_bodies to on;
create or replace function fc_calculoiptu_bag_2008(integer,integer,boolean,boolean,boolean,boolean,boolean,integer,integer)
RETURNS VARCHAR(100)
as $$
DECLARE
  MATRICULA      ALIAS FOR $1;
  ANOUSU         ALIAS FOR $2;
  GERAFINANC     ALIAS FOR $3;
  ATUALIZAP      ALIAS FOR $4;
  NOVONUMPRE     ALIAS FOR $5;
  CALCULO_GERAL  ALIAS FOR $6;
  DEMO           ALIAS FOR $7;
  PARCELAINI     ALIAS FOR $8;
  PARCELAFIM     ALIAS FOR $9;

  V_SETOR       CHAR(4);
  V_QUADRA      CHAR(4);
  V_LOTE        CHAR(4);
  V_IPTUFRAC        INTEGER;
  V_TOTCONSTR       FLOAT8;
  V_TOTMAT      INTEGER;
  V_AREALO      FLOAT8;
  V_TAXALIMP        FLOAT8 DEFAULT 0;
  V_TAXABOMB        FLOAT8;
    nValIsenTaxa1   FLOAT8;
  V_TOTALZAO           FLOAT8 DEFAULT 0;
  V_MANUAL          TEXT DEFAULT ' \n';
  V_IDBQL       INTEGER DEFAULT 0;
  V_QFACE       INTEGER ;
    iHistIptuIsen   INTEGER ;
  V_VALINFLATOR FLOAT8;
  V_VM2T            FLOAT8 DEFAULT 0;
  V_AREAL           FLOAT8 DEFAULT 0;
  V_PROFUND_PADRAO  FLOAT8 DEFAULT 0;
  V_PROFUND_MEDIA   FLOAT8 DEFAULT 0;
  V_INDICE_CORRECAO FLOAT8 DEFAULT 0;
  V_INDICE_MULTI    FLOAT8 DEFAULT 0;
  V_TESTADA         FLOAT8 DEFAULT 0;
  V_QUANT_TESTADA   INTEGER DEFAULT 0;
  V_NUMERO_ESQUINA  INTEGER DEFAULT 0;
  V_AREA_CORRIG     FLOAT8 DEFAULT 0;
  V_AREA_TRIBUT     FLOAT8 DEFAULT 0;
  V_VVT     FLOAT8 DEFAULT 0;
  V_TIPOI       INTEGER DEFAULT 0;
  V_FRACAO      FLOAT8 DEFAULT 0;
  V_J01_FRACAO      FLOAT8 DEFAULT 0;
  V_CARAC       VARCHAR(250);
  V_BAIXA       DATE;
  V_AREATC             FLOAT8 DEFAULT 0;
  V_AREAC       FLOAT8 DEFAULT 0;
  V_VVC     FLOAT8 DEFAULT 0;
  V_VVCP        FLOAT8 DEFAULT 0;
  V_VM2C        FLOAT8 DEFAULT 0;
  V_VM2C2       FLOAT8 DEFAULT 0;
  V_VV          FLOAT8 DEFAULT 0;
  V_AREACALC        FLOAT8 DEFAULT 0;
  V_ALIPRE      FLOAT8 DEFAULT 0;
  V_ALITER      FLOAT8 DEFAULT 0;
  V_ALIQ        FLOAT8 DEFAULT 0;
  V_ALIQANT     FLOAT8 DEFAULT 0;
  V_RECIPTU     INTEGER;
  V_IPTU        FLOAT8 DEFAULT 0;
  V_DIGITO      INTEGER;
  V_MESMONUMPRE BOOLEAN DEFAULT FALSE;
  V_NUMPRE      INTEGER;
  V_NUMPREEXISTE INTEGER;
  V_VENCIM      INTEGER;
  V_PARCELAS        INTEGER;
  V_VALORPAR        FLOAT8 DEFAULT 0;
  V_PARINI      FLOAT8 DEFAULT 1;
  V_QUANT_IMOVEIS   INTEGER DEFAULT 0;
  V_NUMCGM      INTEGER;
  V_CGMPRI      INTEGER;
  V_CGMPRITESTE INTEGER;
  V_DTOPER      DATE;
  V_TEMFINANC       BOOLEAN DEFAULT FALSE;
  V_SOMA        FLOAT8;
  V_DIFIPTU     FLOAT8 default 0;
  V_ISENALIQ        FLOAT8;
  V_VLRISEN     FLOAT8 DEFAULT 0;
  V_NUMBCO      VARCHAR(15);
  V_TEMPAGAMENTO    BOOLEAN DEFAULT FALSE;
  V_TIPOIS      INTEGER;
  V_ISENTAXAS       BOOLEAN DEFAULT FALSE;
  V_CODISEN         INTEGER;
  V_CODTIPOISEN INTEGER;
  V_TESTAISEN       INTEGER;
  V_PROJETOCURA FLOAT8 DEFAULT 0;
  V_CONDOMINIO      INTEGER DEFAULT 0;
  V_ONERACAO        FLOAT8  DEFAULT 0;
  V_MURO        BOOLEAN DEFAULT FALSE;
  V_CALCADA     BOOLEAN DEFAULT FALSE;
  V_PAVIMENTACAO    INTEGER DEFAULT 0;
  V_BEIRAL      BOOLEAN DEFAULT FALSE;
  V_SANITARIA       BOOLEAN DEFAULT TRUE;
  V_IRREGULAR       BOOLEAN DEFAULT FALSE;
  V_BOMBEIRO        FLOAT8 DEFAULT 0;
  V_LIMPEZA     FLOAT8 DEFAULT 0;
  V_QLIMPEZA        FLOAT8 DEFAULT 0;
  V_ZONA        INTEGER DEFAULT 0;
  V_COMERCIO        BOOLEAN DEFAULT FALSE;
  V_DESCONTOT       FLOAT8  DEFAULT 0;
  V_DESCONTOP       FLOAT8  DEFAULT 0;
  V_ILUMINACAO      INTEGER DEFAULT 0;
  V_ALAGADO     BOOLEAN DEFAULT FALSE;
  V_ENCRAVADO       BOOLEAN DEFAULT FALSE;
  V_GLEBA       BOOLEAN DEFAULT FALSE;
  V_ESPECIE     INTEGER DEFAULT 0;
  V_USO     INTEGER DEFAULT 0;
  V_ANOCONSTRUCAO   INTEGER DEFAULT 0;
  V_TIPOCONSTRUCAO  INTEGER DEFAULT 0;
  V_UTILIDADEPUBLICA    BOOLEAN DEFAULT FALSE;
  V_1           INTEGER DEFAULT 1;
  V_2           INTEGER DEFAULT 2;
  V_QONERACAO       FLOAT8 DEFAULT 0;
  V_QDESCONTOT      FLOAT8 DEFAULT 0;
  V_QDESCONTOP      FLOAT8 DEFAULT 0;
  V_QPROJETOCURA    FLOAT8 DEFAULT 0;
  V_LOTEAM      FLOAT8 DEFAULT 0;
  V_TIPOIMP            CHAR(1);
  V_DIAINI      DATE;
  V_DIAFIM      DATE;

  V_TAXA1       FLOAT8 DEFAULT 0;
  V_VALORTX1        FLOAT8 DEFAULT 0;
  V_SOMATX1         FLOAT8 DEFAULT 0;
  V_DIFTX1      FLOAT8 DEFAULT 0;
  V_RECTX1      INTEGER;

  V_TAXA2       FLOAT8 DEFAULT 0;
  V_VALORTX2        FLOAT8 DEFAULT 0;
  V_SOMATX2         FLOAT8 DEFAULT 0;
  V_DIFTX2      FLOAT8 DEFAULT 0;
  V_RECTX2      INTEGER;
  V_TAXA3       FLOAT8 DEFAULT 0;
  V_VALORTX3        FLOAT8 DEFAULT 0;
  V_SOMATX3         FLOAT8 DEFAULT 0;
  V_DIFTX3      FLOAT8 DEFAULT 0;
  V_RECTX3      INTEGER;
  V_TAXA4       FLOAT8 DEFAULT 0;
  V_VALORTX4        FLOAT8 DEFAULT 0;
  V_SOMATX4         FLOAT8 DEFAULT 0;
  V_DIFTX4      FLOAT8 DEFAULT 0;
  V_RECTX4      INTEGER;
  V_TAXA5       FLOAT8 DEFAULT 0;
  V_VALORTX5        FLOAT8 DEFAULT 0;
  V_SOMATX5         FLOAT8 DEFAULT 0;
  V_DIFTX5      FLOAT8 DEFAULT 0;
  V_RECTX5      INTEGER;
  V_TAXA6       FLOAT8 DEFAULT 0;
  V_VALORTX6        FLOAT8 DEFAULT 0;
  V_SOMATX6         FLOAT8 DEFAULT 0;
  V_DIFTX6      FLOAT8 DEFAULT 0;
  V_RECTX6      INTEGER;
  V_TAXA7       FLOAT8 DEFAULT 0;
  V_VALORTX7        FLOAT8 DEFAULT 0;
  V_SOMATX7         FLOAT8 DEFAULT 0;
  V_DIFTX7      FLOAT8 DEFAULT 0;
  V_RECTX7      INTEGER;
  V_TAXA8       FLOAT8 DEFAULT 0;
  V_VALORTX8        FLOAT8 DEFAULT 0;
  V_SOMATX8         FLOAT8 DEFAULT 0;
  V_DIFTX8      FLOAT8 DEFAULT 0;
  V_RECTX8      INTEGER;
  V_TAXA9       FLOAT8 DEFAULT 0;
  V_VALORTX9        FLOAT8 DEFAULT 0;
  V_SOMATX9         FLOAT8 DEFAULT 0;
  V_DIFTX9      FLOAT8 DEFAULT 0;
  V_RECTX9      INTEGER;
  V_QUATX       INTEGER DEFAULT 0;
  V_J71_VALOR       FLOAT8 DEFAULT 0;
  V_J72_VALOR       FLOAT8 DEFAULT 0;
  V_CARTAXA     INTEGER;

  R_CONSTR             RECORD;
  R_IDCONSTR        RECORD;
  R_VENCIM      RECORD;
  R_TAXA        RECORD;
  R_CARLOTE     RECORD;
  R_CARFACE     RECORD;
  R_IPTUTAXA        RECORD;
  R_FRACAO      RECORD;
  R_PROPRIS     RECORD;

  V_RECORD_ARRECAD  RECORD;


  V_CAR_PASSEIO INTEGER;
  V_CAR_CERCA_MURO  INTEGER;
  V_SIT_CONSTR      INTEGER;

  CAR_ESQ       INTEGER DEFAULT 0;
  CAR_AMBAS         INTEGER DEFAULT 0;
  V_PONTOS      FLOAT8 DEFAULT 0;
  V_CARACT_CALCULO  INTEGER;
  V_FORMA_TERRENO   INTEGER;
  V_FATOR       FLOAT8;
  V_CORRIG      CHAR(1);
  V_PONTUACAO       INTEGER;
  V_TAXALIXOCAR INTEGER;
  V_TAXALIXOVAL FLOAT8 DEFAULT 0;
  V_BASE        FLOAT8 DEFAULT 0;
  V_ACRESCIMO       BOOLEAN DEFAULT FALSE;
  V_MOSTRAR     BOOLEAN DEFAULT TRUE;


  V_TEM_CARACT_ESQUINA INTEGER DEFAULT 0;
  V_EMPAGAMENTO BOOLEAN DEFAULT FALSE;
  v_raise           boolean default false;


  -- mudancas parcelas
  V_TEXT        TEXT DEFAULT '';
  V_PERC        FLOAT8;
  V_HIST        INTEGER;
  V_TIPO        INTEGER;
  V_PARCELAINI      INTEGER DEFAULT 0;
  V_PARCELACALC FLOAT8;

  V_PASSA           BOOLEAN DEFAULT TRUE;

  lProcessarArrecad BOOLEAN DEFAULT TRUE;
  lAbatimento       BOOLEAN DEFAULT TRUE;

  iNumpreVerifica   INTEGER;

  rRecibosGerados record;

  BEGIN

    v_raise := ( case when fc_getsession('DB_debugon') is null then false else true end );

    IF PARCELAFIM < PARCELAINI THEN
      RETURN '21 PARCELAS INCONSISTENTES';
    END IF;

    SELECT J01_IDBQL,CASE WHEN J34_AREAL = 0 THEN J34_AREA ELSE J34_AREAL END AS J34_AREAL, J34_TOTCON, J01_NUMCGM, J01_BAIXA
    INTO V_IDBQL, V_AREAL, V_FRACAO, V_NUMCGM, V_BAIXA
    FROM IPTUBASE
    INNER JOIN LOTE ON J34_IDBQL = J01_IDBQL
    LEFT OUTER JOIN IPTUFRAC ON J25_ANOUSU = ANOUSU AND J25_MATRIC = J01_MATRIC
    WHERE J01_MATRIC = MATRICULA;
    IF V_IDBQL IS NULL THEN
      RETURN '16 MATRICULA NAO CADASTRADA';
    END IF;
    IF NOT V_BAIXA IS NULL THEN
      RETURN '02 MATRICULA BAIXADA';
    END IF;
    IF V_AREAL = 0 OR V_AREAL IS NULL THEN
      RETURN '03 AREA DO LOTE ZERADA';
    END IF;

    SELECT COUNT(J01_IDBQL)
    INTO V_TOTMAT
    FROM IPTUBASE
    WHERE J01_BAIXA IS NULL AND J01_IDBQL = V_IDBQL;

    if v_raise is true then
      raise notice 'matricula: %',MATRICULA;
      raise notice 'fracao: %',V_FRACAO;
      raise notice 'total de matriculas: %',V_TOTMAT;
    end if;

    SELECT J34_SETOR, J34_QUADRA, J34_LOTE
    INTO V_SETOR, V_QUADRA, V_LOTE
    FROM LOTE
    WHERE J34_IDBQL = V_IDBQL;

    if v_raise is true then
      raise notice 'V_SETOR: % - V_QUADRA: %', V_SETOR, V_QUADRA;
    end if;

    select j20_numpre
      into iNumpreVerifica
      from iptunump
     where j20_matric = MATRICULA
       and j20_anousu = ANOUSU
     limit 1;

    if found then

      -- Verifica se existe Pagamento Parcial para o débito informado
      select fc_verifica_abatimento(1, iNumpreVerifica)::boolean into lAbatimento;

      if lAbatimento then
        return '04 DEBITO COM PAGAMENTO PARCIAL!';
      end if;
    end if;


    IF V_TOTMAT = 1 THEN

      IF V_FRACAO IS NULL OR V_FRACAO = 0 THEN
        V_FRACAO = 100::float8;
      ELSE
        -- CALCULA FRACAO DO LOTE
        if v_raise is true then
          raise notice 'calculando area construida da matricula...  ';
        end if;
        SELECT SUM(J39_AREA)
        INTO V_AREACALC
        FROM IPTUCONSTR
        WHERE J39_MATRIC = MATRICULA
        AND J39_DTDEMO IS NULL
        GROUP BY J39_MATRIC;

        if v_raise is true then
          raise notice 'fracao de novo: %',V_FRACAO;
        end if;

        if v_raise is true then
          raise notice 'fracaocalc: %',V_AREACALC;
        end if;
        IF V_AREACALC IS NULL OR V_AREACALC = 0 THEN
          V_FRACAO = 100;
        ELSE
          if v_raise is true then
            raise notice 'V_AREACALC: % - V_FRACAO: %',V_AREACALC,V_FRACAO;
          end if;
          V_FRACAO = ((V_AREACALC/V_FRACAO)*100);
        END IF;
      END IF;

    ELSE

      SELECT    SUM(J39_AREA)
      INTO V_TOTCONSTR
      FROM IPTUBASE
      INNER JOIN IPTUCONSTR ON J39_MATRIC = J01_MATRIC
      INNER JOIN LOTE ON J34_IDBQL = J01_IDBQL
      WHERE     J01_BAIXA IS NULL AND
      J34_SETOR = V_SETOR AND
      J34_QUADRA = V_QUADRA AND
      J34_LOTE = V_LOTE AND
      J39_DTDEMO IS NULL;


      if v_raise is true then
        raise notice 'total construido no lote: %',V_TOTCONSTR;
      end if;
      V_MANUAL := V_MANUAL || 'TOTAL CONSTRUIDO NO LOTE: ' || V_TOTCONSTR || ' - ';

      IF V_TOTCONSTR = 0 THEN
        UPDATE IPTUBASE SET J01_FRACAO = 0 WHERE J01_IDBQL = V_IDBQL;
      ELSE

        if v_raise is true then
          raise notice 'entrando no fracao';
        end if;

        FOR R_FRACAO IN
          SELECT J01_MATRIC, SUM(J39_AREA) FROM IPTUBASE
          LEFT JOIN IPTUCONSTR ON J39_MATRIC = J01_MATRIC
          WHERE J01_BAIXA IS NULL AND J39_DTDEMO IS NULL AND J01_IDBQL = V_IDBQL
          GROUP BY J01_MATRIC LOOP

          if v_raise is true then
            raise notice 'processando fracao matricula: % - contruido desta: %',R_FRACAO.J01_MATRIC,R_FRACAO.SUM;
          end if;

          SELECT J25_MATRIC
          INTO V_IPTUFRAC
          FROM IPTUFRAC
          WHERE   J25_MATRIC = R_FRACAO.J01_MATRIC AND
          J25_ANOUSU = ANOUSU;
          if v_raise is true then
            raise notice '      iptufrac: %', V_IPTUFRAC;
          end if;
          IF V_IPTUFRAC IS NULL OR V_IPTUFRAC = 0 THEN
            if v_raise is true then
              raise notice '   insert no iptufrac';
            end if;
            INSERT INTO IPTUFRAC values (ANOUSU,R_FRACAO.J01_MATRIC,V_IDBQL,R_FRACAO.SUM / V_TOTCONSTR * 100);
          ELSE
            if v_raise is true then
              raise notice '   update no iptufrac';
            end if;
            UPDATE IPTUFRAC SET J25_FRACAO = R_FRACAO.SUM / V_TOTCONSTR * 100, J25_IDBQL = V_IDBQL WHERE
            J25_MATRIC = R_FRACAO.J01_MATRIC AND J25_ANOUSU = ANOUSU;
          END IF;

        END LOOP;

        SELECT J25_FRACAO
        INTO V_FRACAO
        FROM IPTUFRAC
        WHERE J25_MATRIC = MATRICULA AND J25_ANOUSU = ANOUSU;

        IF V_FRACAO IS NULL OR V_FRACAO = 0 THEN
          V_FRACAO = 100::float8;
        END IF;

      END IF;

    END IF;

    SELECT J01_FRACAO
    INTO V_J01_FRACAO
    FROM IPTUBASE
    WHERE J01_MATRIC = MATRICULA;
    IF V_J01_FRACAO IS NOT NULL AND V_J01_FRACAO > 0 THEN
      V_FRACAO = V_J01_FRACAO;
    END IF;

    if v_raise is true then
      raise notice 'fracao: %',V_FRACAO;
      raise notice 'verificando pagamentos';
      raise notice 'select iptunump inner join arrecant';
    end if;

    -- VERIFICA PAGAMENTOS
    ----raise NOTICE 'VERIFICA PAGAMENTO';
    SELECT J20_NUMPRE,MAX(K00_NUMPAR) AS K00_NUMPAR
      INTO V_NUMPRE,V_PARINI
      FROM IPTUNUMP
           INNER JOIN ARRECANT ON J20_NUMPRE = K00_NUMPRE
     WHERE J20_ANOUSU = ANOUSU
         AND J20_MATRIC = MATRICULA
    GROUP BY J20_NUMPRE;

    IF NOT V_NUMPRE IS NULL AND DEMO = FALSE THEN
      IF ATUALIZAP = FALSE THEN
        V_EMPAGAMENTO = TRUE;
        --RETURN '04 CARNE EM PROCESSO DE PAGAMENTO';
      END IF;
      V_TEMPAGAMENTO = TRUE;
    ELSE
      V_PARINI := 1;
    END IF;

    if v_raise is true then
      raise notice 'V_NUMPRE: % - V_PARINI: %', V_NUMPRE, V_PARINI;
    end if;

    -- REMOVE CALCULO EXISTENTE
    if v_raise is true then
      raise notice 'deletando iptucalv... iptucale... iptucalc...';
    end if;

    IF CALCULO_GERAL = FALSE AND DEMO IS FALSE THEN
      DELETE FROM IPTUCALV WHERE J21_ANOUSU = ANOUSU AND J21_MATRIC = MATRICULA;
      DELETE FROM IPTUCALE WHERE J22_ANOUSU = ANOUSU AND J22_MATRIC = MATRICULA;
      DELETE FROM IPTUCALC WHERE J23_ANOUSU = ANOUSU AND J23_MATRIC = MATRICULA;
    END IF;
    -- CALCULA VALOR DO TERRENO





    SELECT J30_ALITER, J30_ALIPRE, J34_ZONA
    INTO V_ALITER, V_ALIPRE, V_ZONA
    FROM LOTE
    INNER JOIN SETOR ON J34_SETOR = J30_CODI
    WHERE J34_IDBQL = V_IDBQL;

    if v_raise is true then
      raise NOTICE 'IDBQL %', V_IDBQL;
    end if;

    select j35_caract
        into V_CARACT_CALCULO
    FROM CARLOTE
    inner join caracter on j31_codigo = j35_caract
    WHERE   j35_idbql = V_IDBQL AND
    j31_grupo = 30;

    if V_CARACT_CALCULO is null then
      V_CARACT_CALCULO = 0;
    end if;

    V_MANUAL := V_MANUAL || 'CARACTERISTICA DA AREA DO LOTE: ' || V_CARACT_CALCULO || ' - ';

    select j35_caract
        into V_FORMA_TERRENO
    FROM CARLOTE
    inner join caracter on j31_codigo = j35_caract
    WHERE   j35_idbql = V_IDBQL AND
    j31_grupo = 31;

    if V_FORMA_TERRENO is null then
      V_FORMA_TERRENO = 0;
    end if;

    V_MANUAL := V_MANUAL || 'CARACTERISTICA DO FORMATO DO IMOVEL: ' || V_FORMA_TERRENO || ' \n ';

    select j74_fator, case when j74_corrig is true then '1' else '0' end from carfator into V_FATOR, V_CORRIG where j74_anousu = ANOUSU and j74_caract = V_FORMA_TERRENO;

    V_MANUAL := V_MANUAL || 'FATOR DE ACORDO COM FORMA DO TERRENO: ' || V_FATOR || ' - CORRECAO: ' || V_CORRIG || ' \n ';

    --
    -- TESTADA PRINCIPAL DO LOTE
    --

    SELECT J37_FACE, CASE WHEN J36_TESTLE = 0 THEN J36_TESTAD ELSE J36_TESTLE END AS J36_TESTLE
    INTO V_QFACE, V_TESTADA
    FROM IPTUCONSTR
    INNER JOIN TESTADA ON J36_FACE = J39_CODIGO AND J36_IDBQL = V_IDBQL
    INNER JOIN FACE ON J37_FACE = J36_FACE
--      INNER JOIN FACEVALOR ON J81_FACE = J37_FACE AND J81_ANOUSU = ANOUSU
    INNER JOIN IPTUBASE ON J01_MATRIC = J39_MATRIC
    WHERE J39_MATRIC = MATRICULA AND J39_DTDEMO IS NULL AND J01_BAIXA IS NULL LIMIT 1;

    if v_raise is true then
      raise NOTICE 'V_QFACE % ',V_QFACE;
    end if;

    select count(*) from testada into V_QUANT_TESTADA WHERE j36_idbql = V_IDBQL;

    select j35_caract
    INTO  V_TEM_CARACT_ESQUINA
    from carlote where j35_idbql = V_IDBQL and j35_caract = 441;

    IF V_TEM_CARACT_ESQUINA IS NULL THEN
      V_TEM_CARACT_ESQUINA = 0;
    END IF;

    V_MANUAL := V_MANUAL || ' TEM ESQUINA(441) : ' || V_TEM_CARACT_ESQUINA || ' - ';

    --
    -- PEGAR ZONA
    --

    if v_raise is true then
      raise notice ' ZONA %',V_ZONA;
    end if;

    V_MANUAL := V_MANUAL || 'FRACAO: ' || V_FRACAO || ' - ';
    V_MANUAL := V_MANUAL || 'ZONA FISCAL: ' || V_ZONA || ' - ';
    V_MANUAL := V_MANUAL || 'QUANTIDADE DE TESTADA: ' || V_QUANT_TESTADA || ' \n ';

    IF V_QFACE IS NULL THEN

      SELECT J49_FACE, CASE WHEN J36_TESTLE = 0 THEN J36_TESTAD ELSE J36_TESTLE END AS J36_TESTLE
        INTO V_QFACE, V_TESTADA
        FROM TESTPRI
             INNER JOIN FACE ON J49_FACE = J37_FACE
             INNER JOIN TESTADA ON J49_FACE = J36_FACE AND J49_IDBQL = J36_IDBQL
       WHERE J49_IDBQL = V_IDBQL;

    END IF;

    select J81_VALORCONSTR
      into V_VM2C
      from FACEVALOR
     where J81_FACE   = V_QFACE
       and J81_ANOUSU = ANOUSU;
--    if not found or V_VM2C is null or V_VM2C = 0 then
--      RETURN '06 VALOR DO M2 DA CONSTRUÇÃO NÃO ENCONTRADO PARA A FACE '||V_QFACE||' !';
--    end if;


    SELECT J82_VALORTERRENO
        INTO V_VM2T
        FROM LOTESETORFISCAL
    INNER JOIN SETORFISCAL ON J90_CODIGO = J91_CODIGO
    INNER JOIN SETORFISCALVALOR ON J82_SETORFISCAL = J90_CODIGO AND J82_ANOUSU = ANOUSU
    WHERE J91_IDBQL = V_IDBQL;

    IF NOT FOUND THEN
      RETURN '05 SEM VALOR DO M2 CADASTRADO PARA O LOTE CONFIGURADO PELO SETOR FISCAL!';
    END IF;


    V_MANUAL := V_MANUAL || 'VALOR DO M2 DO TERRENO: ' || V_VM2T || ' - ';

    if v_raise is true then
      raise notice 'V_VM2T: % - V_FATOR: %', V_VM2T, V_FATOR;
    end if;

    IF V_QFACE IS NULL THEN
      RETURN '06 TESTADA PRINCIPAL DO LOTE NAO CADASTRADA';
    END IF;

    IF V_VM2T IS NULL OR V_VM2T = 0 THEN
      RETURN '07 VALOR DO M2 DO TERRENO NAO CADASTRADO PARA A ZONA!';
    END IF;

    V_VM2T = V_VM2T * V_FATOR;

    V_MANUAL := V_MANUAL || 'VALOR DO M2 DO TERRENO * FATOR: ' || V_VM2T || ' - ';

--    IF V_VM2C IS NULL THEN
--      RETURN '08 VALOR DO M2 DA CONSTRUCAO NAO CADASTRADO PARA A ZONA!';
--    END IF;

    V_MANUAL := V_MANUAL || 'VALOR DO M2 DO TERRENO: ' || V_VM2T || '\n';

    if v_raise is true then
      raise NOTICE 'V_QFACE % V_VM2T % AREA % TESTADA %',V_QFACE,V_VM2T,V_AREAL,V_TESTADA;
    end if;

    --VERIFICA ISENCOES
    if v_raise is true then
      raise NOTICE 'verificando isencoes';
    end if;

    SELECT J46_CODIGO, J45_TIPIS , J46_PERC , CASE WHEN J45_TAXAS = FALSE THEN TRUE ELSE FALSE END, CASE WHEN J46_AREALO IS NULL THEN 0 ELSE J46_AREALO END AS J46_AREALO
    INTO V_CODISEN, V_TIPOIS, V_ISENALIQ, V_ISENTAXAS, V_AREALO
    FROM IPTUISEN
    INNER JOIN ISENEXE ON J46_CODIGO = J47_CODIGO
    INNER JOIN TIPOISEN ON J46_TIPO = J45_TIPO
    WHERE J46_MATRIC = MATRICULA AND J47_ANOUSU = ANOUSU;

    IF V_ISENALIQ IS NULL THEN
      V_ISENALIQ = 0;
    END IF;
    IF V_ISENTAXAS IS NULL THEN
      V_ISENTAXAS = TRUE;
    END IF;

    if v_raise is true then
      raise notice 'V_CODISEN: % - V_ISENTAXAS: % - V_ISENALIQ: %', V_CODISEN, V_ISENTAXAS, V_ISENALIQ;
    end if;

    -- CALCULA A PROFUNDIDADE MEDIA

    IF V_ZONA = 1 OR V_ZONA = 2 THEN
      V_PROFUND_PADRAO := 40;
    ELSE
      V_PROFUND_PADRAO := 30;
    END IF;

    V_MANUAL := V_MANUAL || 'PROFUNDIDADE PADRAO: ' || V_PROFUND_PADRAO || ' \n ';

    if v_raise is true then
      raise notice 'V_CARACT_CALCULO: %', V_CARACT_CALCULO;
    end if;

    if V_CARACT_CALCULO = 300 THEN
      -- calcula com a area tributada e profundidade
      -- informada. os campos:
      -- diverso->v14_vlrter = area tributada
      -- diverso->v14_vlredi = profundidade

      V_MANUAL := V_MANUAL || 'V_CARACT_CALCULO: ' || V_CARACT_CALCULO || ' - CALCULANDO PELA AREA TRIBUTADA E PROFUNDIDADE INFORMADA ' || ' - ';

      select j80_areatrib, j80_profund into V_AREA_TRIBUT, V_PROFUND_MEDIA from iptudiversos where j80_matric = MATRICULA;

      V_MANUAL := V_MANUAL || 'AREA TRIBUTADA INFORMADA: ' || V_AREA_TRIBUT || ' - PROFUNDIDADE MEDIA INFORMADA: ' || V_PROFUND_MEDIA || ' \n ';

      IF V_AREA_TRIBUT IS NULL OR V_PROFUND_MEDIA IS NULL THEN
        RETURN '09 - sem dados diversos lancados...';
      END IF;


      if v_raise is true then
        raise notice 'V_AREA_TRIBUT: % - V_PROFUND_MEDIA: %', V_AREA_TRIBUT, V_PROFUND_MEDIA;
      end if;

    ELSE

      V_MANUAL := V_MANUAL || ' CALCULANDO PELA FORMULA ' || ' - ';

      if V_AREAL <= 10000 then

        V_MANUAL := V_MANUAL || ' AREA DO LOTE <= 10000 ' || V_AREAL || ' - ';

        V_PROFUND_MEDIA   = ROUND(V_AREAL / V_TESTADA,2);
        V_MANUAL := V_MANUAL || ' PROFUNDIDADE MEDIA = AREA DO LOTE / TESTADA: ' || V_PROFUND_MEDIA || ' - ';

        V_INDICE_CORRECAO = ROUND(SQRT(V_PROFUND_PADRAO / V_PROFUND_MEDIA),2);
        V_MANUAL := V_MANUAL || ' INDICE DE CORRECAO = RAIZ QUADRADA DA (PROFUNDIDADE PADRAO / PROFUNDIDADE MEDIA): ' || V_INDICE_CORRECAO || ' - ';

        V_AREA_CORRIG     = ROUND(V_AREAL * V_INDICE_CORRECAO);
        V_MANUAL := V_MANUAL || ' AREA CORRIGIDA = AREA DO LOTE * INDICE DE CORRECAO: ' || V_AREA_CORRIG || ' - ';

        V_AREA_TRIBUT     = V_AREA_CORRIG;
        V_MANUAL := V_MANUAL || ' AREA TRIBUTADA = AREA CORRIGIDA: ' || V_AREA_TRIBUT || ' - ';

        V_MANUAL := V_MANUAL || ' QUANTIDADE DE TESTADA: ' || V_QUANT_TESTADA || ' - ';

        if v_raise is true then
          raise notice 'V_QUANT_TESTADA: %', V_QUANT_TESTADA;
        end if;

        if V_QUANT_TESTADA > 1 and V_TEM_CARACT_ESQUINA > 0 then

          if V_QUANT_TESTADA = 2 then
            V_NUMERO_ESQUINA = 1;
            V_MANUAL := V_MANUAL || ' SE QUANTIDADE DE TESTADA = 2, NUMERO DE ESQUINAS = 1 - ';
          ELSIF V_QUANT_TESTADA = 3 THEN
            V_NUMERO_ESQUINA = 2;
            V_MANUAL := V_MANUAL || ' SE QUANTIDADE DE TESTADA = 3, NUMERO DE ESQUINAS = 2 - ';
          ELSE
            V_NUMERO_ESQUINA = V_QUANT_TESTADA;
            V_MANUAL := V_MANUAL || ' SE QUANTIDADE DE TESTADA DIFERENTE DE 2 E DE 3, NUMERO DE ESQUINAS = QUANTIDADE DE TESTADAS: ' || V_QUANT_TESTADA || ' - ';
          end if;

          IF V_AREAL >= 300 THEN
            V_AREA_TRIBUT = (V_AREA_TRIBUT + ((300 * V_FATOR::float8)-300)::float8) + ( 300::float8 * (((V_NUMERO_ESQUINA::float8*30::float8)/100::float8)::float8)::float8);
            V_MANUAL := V_MANUAL || ' SE AREA TRIBUTADA MAIOR OU IGUAL A 300, AREA TRIBUTADA = AREA TRIBUTADA + ((300 * ' || V_FATOR ||')-300) * NUMERO DE ESQUINAS ('||(1::float8+((V_NUMERO_ESQUINA::float8*30::float8)/100::float8))::float8||'): ' || V_AREA_TRIBUT || ' - ';
          ELSE
            V_AREA_TRIBUT = (V_AREA_TRIBUT + ((V_AREAL * V_FATOR::float8)-V_AREAL)::float8) + ( V_AREAL::float8 * (((V_NUMERO_ESQUINA::float8*30::float8)/100::float8)::float8)::float8);
            --     V_AREA_TRIBUT = V_AREA_TRIBUT + (V_AREA_CORRIG * V_FATOR) * V_NUMERO_ESQUINA;
            V_MANUAL := V_MANUAL || ' SE AREA TRIBUTADA MENOR QUE 300, AREA TRIBUTADA = AREA TRIBUTADA + (AREA CORRIGIDA * FATOR) * NUMERO DE ESQUINAS: ' || V_AREA_TRIBUT || ' - ';
          END IF;

        end if;

        if V_CORRIG = '0' THEN
          V_MANUAL := V_MANUAL || ' CORRIGIDO = 1 - ';

          if V_FORMA_TERRENO in (311, 312, 313) then
            V_AREA_TRIBUT = V_AREA_TRIBUT + V_AREAL;
            V_MANUAL := V_MANUAL || ' SE FORMA DO TERRENO = 311 OU 312 OU 313 - AREA TRIBUTADA: ' || V_AREA_TRIBUT || ' - ';
          elsif V_FORMA_TERRENO in (314) AND V_QUANT_TESTADA >= 3 then
            V_MANUAL := V_MANUAL || ' SE FORMA DO TERRENO = 314 - AREA TRIBUTADA: ' || V_AREA_TRIBUT || ' - ';
            V_AREA_TRIBUT = V_AREA_TRIBUT + V_AREAL;
          elsif V_FORMA_TERRENO in (315) AND V_PROFUND_MEDIA < 8 then
            V_AREA_TRIBUT = V_AREA_TRIBUT + V_AREAL;
            V_MANUAL := V_MANUAL || ' SE FORMA DO TERRENO = 315 - AREA TRIBUTADA: ' || V_AREA_TRIBUT || ' - ';
          else
            V_AREA_TRIBUT = V_AREA_TRIBUT + V_AREAL;
            V_MANUAL := V_MANUAL || ' SE FORMA DO TERRENO DIFERENTE DE 311, 312, 313, 314 E 315 - AREA TRIBUTADA = AREA TRIBUTADA + AREA DO LOTE: ' || V_AREA_TRIBUT || ' - ';
          end if;

        end if;

      else

        -- verifica se eh gleba, calcula cfe. area do lote
        V_MANUAL := V_MANUAL || ' AREA DO LOTE > 10000 ' || V_AREAL || ' - ';
        V_AREA_TRIBUT = V_AREAL;
        V_MANUAL := V_MANUAL || ' AREA TRIBUTADA = AREA DO LOTE: ' || V_AREA_TRIBUT || ' - ';
        V_VM2T = V_VM2T * V_FATOR;
        V_MANUAL := V_MANUAL || ' VALOR DO METRO QUADRADO DO TERRENO = VALOR DO METRO QUADRADO DO TERRENO * FATOR: ' || V_VM2T || ' - ';

      end if;

    end if;

    V_AREA_TRIBUT = ROUND(V_AREA_TRIBUT * (V_FRACAO / 100),2);
    V_MANUAL := V_MANUAL || 'AREA TRIBUTADA = AREA TRIBUTADA * FRACAO: ' || V_AREA_TRIBUT || '\n';

    V_VVT = V_AREA_TRIBUT * V_VM2T;

    V_MANUAL := V_MANUAL || 'AREA DO LOTE UTILIZADA PARA CALCULO: ' || V_AREA_TRIBUT || ' - ';
    V_MANUAL := V_MANUAL || 'TESTADA TRIBUTADA: ' || V_TESTADA || ' METROS\n';
    V_MANUAL := V_MANUAL || 'VALOR VENAL DO TERRENO: AREA TRIBUTADA * VALOR DO METRO QUADRADO DO TERRENO ' || V_VVT || '\n';


    SELECT DISTINCT I02_VALOR
    FROM CFIPTU
    INTO V_VALINFLATOR
    INNER JOIN INFLA ON J18_INFLA = I02_CODIGO
    WHERE CFIPTU.J18_ANOUSU = ANOUSU
    AND DATE_PART('y',I02_DATA) = ANOUSU;
    IF V_VALINFLATOR IS NULL THEN
      V_VALINFLATOR = 1;
    END IF;


    if v_raise is true then
      raise NOTICE 'V_VALINFLATOR: % ', V_VALINFLATOR;
    end if;


    if v_raise is true then
      raise NOTICE 'VALOR VENAL TERRENO % - f % ',V_VVT,v_fracao;
    end if;



    -- CALCULA EDIFICACOES
    V_TIPOIMP = 'T';

    FOR R_CONSTR IN SELECT *
      FROM IPTUCONSTR
      WHERE J39_MATRIC = MATRICULA
      AND J39_DTDEMO IS NULL
      LOOP
      V_TIPOI := 1;
      V_CARAC := '';
      V_PONTOS:= 0;
      V_ANOCONSTRUCAO = ANOUSU - R_CONSTR.J39_ANO;
      --       comentado para depois acertar ANOUSU
      if v_raise is true then
        raise notice ' ';
        raise notice 'construcao %', R_CONSTR.J39_IDCONS;
      end if;
      V_TIPOIMP = 'P';

      V_MANUAL := V_MANUAL || 'EDIFICACAO ' || LPAD(R_CONSTR.j39_idcons,3,'0') || ' - LISTA DE CARACTERISTICAS PONTOS\n';

      FOR R_IDCONSTR IN SELECT J48_CARACT,J31_PONTOS::FLOAT8, J31_GRUPO, J31_DESCR, J32_DESCR
        FROM CARCONSTR
        INNER JOIN CARACTER ON J48_CARACT = J31_CODIGO
        INNER JOIN CARGRUP  ON J31_GRUPO = J32_GRUPO
        WHERE J48_MATRIC = R_CONSTR.J39_MATRIC AND J48_IDCONS = R_CONSTR.J39_IDCONS LOOP

        V_DESCONTOP = 0;
        V_CARAC := V_CARAC || TO_CHAR(R_IDCONSTR.J48_CARACT,'9999');
        V_PONTOS := V_PONTOS + R_IDCONSTR.J31_PONTOS;

        V_MANUAL := V_MANUAL || RPAD(UPPER(R_IDCONSTR.j32_descr),30,'_') || '\t' || RPAD(R_IDCONSTR.j48_caract,3) || ' ' || RPAD(RTRIM(R_IDCONSTR.j31_descr),25,'_') || '\t' || LPAD(R_IDCONSTR.j31_pontos,3) || '\n';

        IF R_IDCONSTR.J31_GRUPO = 20 THEN
          V_PONTUACAO = R_IDCONSTR.J48_CARACT;
        END IF;

        IF R_IDCONSTR.J31_GRUPO = 6 THEN
          V_TAXALIXOCAR = R_IDCONSTR.J48_CARACT;
        END IF;

        IF R_IDCONSTR.J31_GRUPO = 38 THEN
          V_SIT_CONSTR = R_IDCONSTR.J48_CARACT;
        END IF;

      END LOOP;

      V_AREAC = ROUND(R_CONSTR.J39_AREA);

      select j71_valor into V_VM2C2 from carvalor where j71_anousu = anousu and j71_caract = V_PONTUACAO;

      select j72_valor into V_TAXALIXOVAL from carzonavalor where j72_anousu = anousu and j72_caract = V_TAXALIXOCAR and j72_zona = v_zona and j72_tipo = 'V';

      if v_raise is true then
        raise notice 'V_TAXALIXOCAR: % - V_TAXALIXOVAL: %', V_TAXALIXOCAR, V_TAXALIXOVAL;
      end if;

      V_AREATC := V_AREATC + V_AREAC;

      V_VVCP   := ROUND( V_VM2C2 * V_AREAC,2);

      V_VVC    := ROUND(V_VVC + V_VVCP,2)::FLOAT8;

      if v_raise is true then
        raise notice 'V_VM2C2 % - AREA: % - V_VVCP: %, V_VVC: %',V_VM2C2, R_CONSTR.J39_AREA, V_VVCP, V_VVC;
      end if;

      IF V_VM2C2 IS NULL OR V_VM2C2 = 0 THEN
        RETURN '10 VALOR M2 CONSTRUCAO ZERADA. CONSTRUCAO: ' || R_CONSTR.J39_IDCONS || ' PONTOS : ' || TO_CHAR(V_PONTOS,'999');
      END IF;

      IF DEMO IS FALSE THEN

        if v_raise is true then
          raise notice 'inserindo em IPTUCALE';
        end if;

        INSERT INTO IPTUCALE VALUES ( ANOUSU,
        MATRICULA,
        R_CONSTR.J39_IDCONS,
        R_CONSTR.J39_AREA,
        V_VM2C2,
        V_PONTOS,
        V_VVCP);
      END IF;

    END LOOP;

    if v_raise is true then
      raise notice 'area total construcao %',V_AREATC;
    end if;

    V_DESCONTOT := 0;

    IF V_TIPOI = 0 THEN
      if v_raise is true then
        raise NOTICE 'TIPO TERRITORIAL ';
      end if;
    ELSE
      if v_raise is true then
        raise NOTICE 'TIPO PREDIAL ';
      end if;
    END IF;


    SELECT CASE WHEN V_TIPOI = 0 THEN J18_RTERRI ELSE J18_RPREDI END, J18_VENCIM, J18_DTOPER, j18_vlrref
    INTO V_RECIPTU, V_VENCIM, V_DTOPER, V_BASE
    FROM CFIPTU
    WHERE J18_ANOUSU = ANOUSU;

    if v_raise is true then
      raise notice 'V_RECIPTU: %', V_RECIPTU;
    end if;

    IF V_RECIPTU IS NULL THEN
      RETURN '11 RECEITA IPTU NAO CADASTRADA';
    END IF;
    IF V_VENCIM IS NULL THEN
      RETURN '12 TABELA DE VENCIMENTO NAO CADASTRADA';
    END IF;






    if v_raise is true then
      raise notice ' TIPO %',V_TIPOI;
      raise notice ' V_ALITER: %, V_ALIPRE: %', V_ALITER, V_ALIPRE;
    end if;

    -- VERIFICA CARACTERISTICAS DO LOTE

    V_MANUAL := V_MANUAL || '\nCARACTERISTICAS DO LOTE                GRUPO\n';

    FOR R_CARLOTE IN SELECT J35_CARACT, J31_GRUPO, j31_descr, j32_descr
      FROM CARLOTE
      INNER JOIN CARACTER ON J31_CODIGO = J35_CARACT
      INNER JOIN CARGRUP  ON J32_GRUPO = J31_GRUPO
      WHERE J35_IDBQL = V_IDBQL LOOP

      IF R_CARLOTE.J31_GRUPO = 36 THEN
        V_CAR_PASSEIO = R_CARLOTE.J35_CARACT;
      end if;


      IF R_CARLOTE.J31_GRUPO = 37 THEN
        V_CAR_CERCA_MURO = R_CARLOTE.J35_CARACT;
      end if;

    END LOOP;


    V_VV := V_VVT + V_VVC;
    V_MANUAL := V_MANUAL || 'VALOR VENAL TOTAL: ' || V_VV || '\n';

    if v_raise is true then
      raise notice 'V_VV: % - V_BASE: %', V_VV, V_BASE;
    end if;


    IF V_TIPOI = 0 THEN
      SELECT    j70_aliter
      into V_ALITER
      from zonasaliq
      where j70_zona = V_ZONA;
      V_ALIQ := V_ALITER;
      V_MANUAL := V_MANUAL || 'TERRITORIAL - ALIQUOTA: ' || V_ALIQ || '\n';
    ELSE

      IF V_VV BETWEEN (V_BASE * 0.0001) AND (V_BASE * 183.3399) THEN
        if v_raise is true then
          raise notice 'entre 0 e 183.3399';
        end if;
        V_ALIQ := 0.8;
        V_MANUAL := V_MANUAL || 'PREDIAL - VALOR VENAL TOTAL ENTRE VALOR BASE (' || V_BASE || ') MULTIPLICADO POR 0.01 E VALOR BASE MULTIPLICADO POR 183.339: ' || V_ALIQ || '\n';
      ELSIF V_VV BETWEEN (V_BASE * 183.34) AND (V_BASE * 366.6699) THEN
        if v_raise is true then
          raise notice 'entre 183.34 e 366.6699';
        end if;
        V_MANUAL := V_MANUAL || 'PREDIAL - VALOR VENAL TOTAL ENTRE VALOR BASE (' || V_BASE || ') MULTIPLICADO POR 183.34 E VALOR BASE MULTIPLICADO POR 366.6699: ' || V_ALIQ || '\n';
        V_ALIQ := 0.9;
      ELSIF V_VV BETWEEN (V_BASE * 366.67) AND (V_BASE * 9999999999.9999) THEN
        if v_raise is true then
          raise notice 'entre 366.67 e 9999999999.9999';
        end if;
        V_ALIQ := 1;
        V_MANUAL := V_MANUAL || 'PREDIAL - VALOR VENAL TOTAL ENTRE VALOR BASE (' || V_BASE || ') MULTIPLICADO POR 366.6699 E VALOR BASE MULTIPLICADO POR 9999999999.9999: ' || V_ALIQ || '\n';
      END IF;

    END IF;

    IF V_ALIQ = 0 THEN
      RETURN '13 ALIQUOTA ZERADA';
    END IF;

    if v_raise is true then
      raise notice 'aliquota: %', V_ALIQ;
    end if;

    if v_raise is true then
      raise notice 'procurando CARZONAVALOR - V_CAR_PASSEIO: %', V_CAR_PASSEIO;
    end if;
    select j72_valor into V_FATOR
        from CARZONAVALOR
    where j72_anousu = ANOUSU AND
    j72_caract = V_CAR_PASSEIO AND
    j72_zona = V_ZONA;
    if V_FATOR is not null then
      V_ALIQ = V_ALIQ * V_FATOR;
      V_ACRESCIMO = TRUE;

      if v_raise is true then
        raise notice '   achou CARZONAVALOR - V_CAR_PASSEIO: % - FATOR: %', V_CAR_PASSEIO, V_FATOR;
      end if;

    end if;


    if V_ACRESCIMO is false then
      if v_raise is true then
        raise notice 'procurando CARZONAVALOR - V_CAR_CERCA_MURO: %', V_CAR_CERCA_MURO;
      end if;
      select j72_valor into V_FATOR from CARZONAVALOR
      where     j72_anousu = ANOUSU AND
      j72_caract = V_CAR_CERCA_MURO AND
      j72_zona = V_ZONA;
      if V_FATOR is not null then
        V_ALIQ = V_ALIQ * V_FATOR;
        V_ACRESCIMO = TRUE;
        if v_raise is true then
          raise notice '   achou CARZONAVALOR - V_CAR_CERCA_MURO: % - V_FATOR: %', V_CAR_CERCA_MURO, V_FATOR;
        end if;
      end if;
    end if;

    if v_raise is true then
      raise notice 'procurando CARZONAVALOR - V_SIT_CONSTR: %', V_SIT_CONSTR;
    end if;
    if V_ACRESCIMO is false then
      select j72_valor into V_FATOR from CARZONAVALOR
      where   j72_anousu = ANOUSU AND
      j72_caract = V_SIT_CONSTR AND
      j72_zona = V_ZONA;
      if V_FATOR is not null then
        V_ACRESCIMO = TRUE;
        V_ALIQ = V_ALIQ * V_FATOR;
        if v_raise is true then
          raise notice '   achou CARZONAVALOR - V_SIT_CONSTR: % - V_FATOR: %', V_SIT_CONSTR, V_FATOR;
        end if;
      end if;
    end if;





    V_QLIMPEZA := 1;



    --    VERIFICA CARACTERISTICAS DA FACE DE QUADRA
    --    FOR R_CARFACE IN SELECT J38_CARACT, J31_GRUPO
    --  FROM CARFACE
    --     INNER JOIN CARACTER ON J31_CODIGO = J38_CARACT
    --  WHERE J38_FACE = V_QFACE LOOP

    --    END LOOP;

    V_MANUAL := V_MANUAL || '\n';

    V_MANUAL := V_MANUAL || '\n';

    if v_raise is true then
      raise notice 'ONERACAO %, DESCONTOT %, DESCONTOP %, LIMPEZA %, BOMBEIRO %',V_ONERACAO, V_DESCONTOT, V_DESCONTOP,V_LIMPEZA, V_BOMBEIRO;
      raise notice 'IPTU BRUTO %',V_IPTU;
    end if;

    V_IPTU := V_IPTU::float8 - V_VLRISEN::float8;

    V_QDESCONTOT := V_DESCONTOT;
    V_QDESCONTOP := V_DESCONTOP;

    V_QONERACAO := V_ONERACAO;

    V_DESCONTOT := 0; --ROUND((V_IPTU * V_DESCONTO / 100),2)::FLOAT8;
    V_DESCONTOP := 0; --ROUND((V_IPTU * V_DESCONTO / 100),2)::FLOAT8;

    if v_raise is true then
      raise notice 'IPTU LIQUIDO %',V_IPTU;
      raise notice 'V_QDESCONTOT %',V_QDESCONTOT;
      raise notice 'V_QDESCONTOP %',V_QDESCONTOP;
    end if;

    IF V_QDESCONTOT != 0 THEN
      V_VVT := ROUND(V_VVT * ((100::float8-V_QDESCONTOT)/100::FLOAT8),2)::FLOAT8;
      V_MANUAL := V_MANUAL || 'VALOR VENAL TERRENO COM DESCONTO: ' || V_VVT || '\n';
    END IF;

    IF V_QDESCONTOP != 0 THEN
      --      V_VVC := ROUND(V_VVC * ((100::float8-V_QDESCONTOP)/100::FLOAT8),2)::FLOAT8;
    END IF;

    V_MANUAL := V_MANUAL || 'VALOR VENAL TOTAL: ' || V_VV || '\n';

    if v_raise is true then
      raise notice 'VVT %,VVC % ',v_vvt, v_vvc;
    end if;

    V_MANUAL := V_MANUAL || 'VALOR VENAL EDIFICACOES: ' || V_VVC || ' - ';

    --raise notice 'VVT %,VVC % ',v_vvt, v_vvc;
    if v_raise is true then
      raise notice 'ALIQUOTA: %', V_ALIQ;
    end if;

    V_IPTU := ROUND((( V_VVT::float8 + V_VVC::float8 ) * ( V_ALIQ::float8 / 100::float8))::numeric,2)::float8;

    V_MANUAL := V_MANUAL || 'VALOR DO IPTU: ' || V_IPTU || ' - ';
    V_MANUAL := V_MANUAL || 'VALOR DA LIMPEZA: ' || V_LIMPEZA || ' - ';

    V_VLRISEN := ROUND((V_IPTU::float8 * ( V_ISENALIQ::float8 / 100::float8))::numeric,2);



    -- VERIFICA ENQUADRAMENTO NA LEI
    -- PARA ISENCAO DE IPTU

    if v_raise is true then
      raise notice 'deletando registros existentes em ISENEXE';
    end if;

    DELETE FROM ISENEXE
        USING IPTUISEN
        WHERE ISENEXE.j47_codigo = IPTUISEN.j46_codigo and
    IPTUISEN.j46_matric = MATRICULA and
    ISENEXE.j47_anousu = ANOUSU and
    IPTUISEN.j46_tipo in (261, 262);

    SELECT j46_codigo into V_TESTAISEN
    FROM IPTUISEN
    INNER JOIN ISENEXE on ISENEXE.j47_codigo = IPTUISEN.j46_codigo
    WHERE   IPTUISEN.j46_matric = MATRICULA and
    IPTUISEN.j46_tipo in (261, 262) and ISENEXE.j47_anousu = ANOUSU;

    if V_TESTAISEN is null then
      delete from IPTUISEN using ISENEXE where IPTUISEN.j46_codigo = ISENEXE.j47_codigo and
      IPTUISEN.j46_matric = MATRICULA and
      IPTUISEN.j46_tipo in (261, 262)  and ISENEXE.j47_anousu = ANOUSU;
      if v_raise is true then
        raise notice 'deletando rgistro do IPTUISEN, pois nao tem mais anos nessa isencao';
                raise notice 'xxxxx';
                raise notice 'xxxxx';
                raise notice 'xxxxx';
      end if;
    end if;

    IF V_TIPOI = 0 THEN
      V_INDICE_MULTI = 6.666;
    ELSE
      V_INDICE_MULTI = 21.666;
    END IF;

    select j41_numcgm into V_CGMPRI from promitente where j41_matric = MATRICULA and j41_tipopro is true limit 1;

    if V_CGMPRI is null then -- nao tem promitente nessa matricula
      V_CGMPRI =  V_NUMCGM;
      select count(*) into V_QUANT_IMOVEIS from iptubase where j01_numcgm = V_CGMPRI group by j01_numcgm;
    else
      select count(*) into V_QUANT_IMOVEIS from promitente where j41_numcgm = V_CGMPRI group by j41_numcgm;
    end if;

    V_QUANT_IMOVEIS = 0;

    FOR R_PROPRIS IN

      select * from (
      select distinct
            j01_matric,
            j01_numcgm,
            1 as j01_tipo
            from iptubase
            left join promitente on j41_matric = j01_matric
            where j01_numcgm = V_CGMPRI and
                        j41_matric is null
      union
      select distinct
            j42_matric,
            j42_numcgm,
            2 as j01_tipo
            from propri
            left join promitente on j41_matric = j42_matric
            where j42_numcgm = V_CGMPRI and
                        j41_matric is null
      union
      select
            distinct
            j41_matric,
            j41_numcgm,
            3 as j01_tipo
            from promitente
            where j41_numcgm = V_CGMPRI) as x
      order by j01_tipo desc
      LOOP

            if v_raise is true then
                raise notice 'tipo %', R_PROPRIS.j01_tipo;
            end if;

      if R_PROPRIS.j01_tipo = 3 then
        if V_CGMPRI = R_PROPRIS.j01_numcgm then
          V_QUANT_IMOVEIS = V_QUANT_IMOVEIS + 1;
        end if;
      else
        if V_CGMPRI = R_PROPRIS.j01_numcgm then
          V_QUANT_IMOVEIS = V_QUANT_IMOVEIS + 1;
        end if;
      end if;

    END LOOP;

    if v_raise is true then
      raise notice 'V_QUANT_IMOVEIS: % - V_CGMPRI: %', V_QUANT_IMOVEIS, V_CGMPRI;
            raise notice 'xxxxx';
            raise notice 'xxxxx';
            raise notice 'xxxxx';
    end if;

    IF V_QUANT_IMOVEIS IS NULL THEN
      V_QUANT_IMOVEIS = 0;
    END IF;

    if V_QUANT_IMOVEIS <= 1 THEN

      if V_ZONA = 4 THEN

        if V_VV < (V_BASE * V_INDICE_MULTI) then
          V_VLRISEN = V_IPTU;
--          V_IPTU = 0;
          V_ISENALIQ = 100;

          SELECT J46_CODIGO into V_TESTAISEN
          FROM IPTUISEN
          INNER JOIN ISENEXE on ISENEXE.j47_codigo = IPTUISEN.j46_codigo
          WHERE IPTUISEN.j46_matric = MATRICULA and
          IPTUISEN.j46_tipo in (261, 262) and ISENEXE.j47_anousu = anousu;

          IF V_TESTAISEN IS NULL THEN

            select nextval('iptuisen_j46_codigo_seq') INTO V_CODISEN;

            V_DIAINI = TO_CHAR(ANOUSU, '9999') || '-01-01';
            V_DIAFIM = TO_CHAR(ANOUSU, '9999') || '-12-31';

            if v_raise is true then
              raise notice 'V_DIAINI: % - V_DIAFIM: %', V_DIAINI, V_DIAFIM;
            end if;

            if V_TIPOI = 0 THEN
              V_CODTIPOISEN = 262;
            else
              V_CODTIPOISEN = 261;
            end if;

            if v_raise is true then
              raise notice 'inserindo no iptuisen j46_codigo: %', V_CODISEN;
            end if;

            insert into IPTUISEN (j46_codigo, j46_matric, j46_tipo, j46_dtini, j46_dtfim, j46_perc, j46_dtinc, j46_idusu, j46_hist, j46_arealo)
            values
            (V_CODISEN, MATRICULA, V_CODTIPOISEN, V_DIAINI::date, V_DIAFIM::date, 100, now(),1,'ISENTO CFE. LEI 3965 DE 26.12.2002 (CALCULO)',0);

            INSERT INTO ISENEXE (j47_codigo, j47_anousu) values (V_CODISEN, ANOUSU);

          END IF;


        end if;

      END IF;

    END IF;

    V_MANUAL := V_MANUAL || 'VALOR DA ISENCAO: ' || V_VLRISEN || ' \n ';

    if v_raise is true then
      raise notice 'IPTU: % - ISENCAO: %',V_IPTU,V_VLRISEN;
    end if;




    if v_raise is true then
      raise NOTICE 'VALOR VT % VC % VALOR TOTAL % ',V_VVT,V_VVC,V_VV ;
    end if;

    IF DEMO IS FALSE THEN

      if v_raise is true then
        raise notice 'inserindo em IPTUCALC';
      end if;

      INSERT INTO IPTUCALC VALUES ( ANOUSU,
      MATRICULA,
      V_TESTADA,
      V_AREA_TRIBUT,
      V_FRACAO,
      V_AREATC,
      V_VM2T,
      V_VVT,
      V_ALIQ,
      ROUND(V_VLRISEN,2),
      V_TIPOIMP );

        select j18_iptuhistisen
          into iHistIptuIsen
          from cfiptu
         where j18_anousu = anousu;
        if iHistIptuIsen is null then
            return '18 PARAMETRO DE CONFIGURACAO DO HISTORICO DE ISENCAO DO IPTU NAO CONFIGURADO';
        end if;

      if V_IPTU > 0 then
        -- registra valor iptu
        INSERT INTO IPTUCALV    (
        j21_anousu,
        j21_matric,
        j21_receit,
        j21_valor,
        j21_quant,
        j21_codhis
        )
        VALUES (ANOUSU,
        MATRICULA,
        V_RECIPTU,
        ROUND(V_IPTU,2),
        V_ALIQ,
        1);
      end if;

      IF V_VLRISEN > 0 THEN
                -- registra valor isencoes
                INSERT INTO IPTUCALV    (
                j21_anousu,
                j21_matric,
                j21_receit,
                j21_valor,
                j21_quant,
                j21_codhis
                )
                VALUES (
                ANOUSU,
                MATRICULA,
                V_RECIPTU,
                ROUND(V_VLRISEN*-1,2),
                0,
                iHistIptuIsen);
            END IF;

    END IF;

    V_IPTU := V_IPTU + V_ONERACAO + V_PROJETOCURA - V_DESCONTOT - V_VLRISEN;

    -- VERIFICA PARCELAS
    SELECT COUNT(*)
    INTO V_PARCELAS
    FROM CADVENCDESC
    INNER JOIN CADVENC ON Q92_CODIGO = Q82_CODIGO
    WHERE Q92_CODIGO = V_VENCIM ;

    IF V_PARCELAS IS NULL OR V_PARCELAS = 0 THEN
      RETURN '14 PARCELAS NAO CADASTRADAS ';
    END IF;

    if v_raise is true then
      raise notice 'isentaxa: %', V_ISENTAXAS;
    end if;

    IF V_ISENTAXAS = TRUE THEN

      if v_raise is true then
        raise notice 'acessou isentaxas... ';
      end if;

      FOR R_TAXA IN SELECT *
        FROM IPTUTAXA
                     inner join iptucadtaxaexe on j08_tabrec = J19_RECEIT
                                              and j08_anousu = ANOUSU
             LEFT OUTER JOIN ISENTAXA ON J56_CODIGO = V_CODISEN AND J56_RECEIT = J19_RECEIT
        WHERE J19_ANOUSU = ANOUSU
          LOOP

        if v_raise is true then
          raise notice 'inserindo em IPTUCALC... taxas...';
        end if;

        IF R_TAXA.J19_RECEIT = 13 THEN
          -- registra valor lixo
          V_QUATX  := V_QUATX + 1;
          V_TAXA1  := V_TAXALIXOVAL * V_PARCELAS;
          V_RECTX1 := R_TAXA.J19_RECEIT;
          if v_raise is true then
            raise notice 'rec lixo %',v_taxa1;
          end if;
          IF R_TAXA.J56_PERC != 0 THEN
            if v_raise is true then
              raise notice 'rec taxa1%',v_taxa1;
            end if;
                    nValIsenTaxa1 := V_TAXA1 - (V_TAXA1 * ( ( 100::FLOAT8-(R_TAXA.J56_PERC) )/100::FLOAT8 ));
--            V_TAXA1 := V_TAXA1 * ((100::FLOAT8-(R_TAXA.J56_PERC))/100::FLOAT8);
            IF V_TAXA1 = 0 OR V_TAXA1 < 0 THEN
              V_TAXA1 = 0;
            END IF;
            if v_raise is true then
              raise notice 'rec taxa1%',v_taxa1;
            end if;
          END IF;

          IF DEMO IS FALSE THEN

            if V_TAXA1 > 0 then
              INSERT INTO IPTUCALV  (
              j21_anousu,
              j21_matric,
              j21_receit,
              j21_valor,
              j21_quant,
              j21_codhis
              )
              VALUES (ANOUSU,
              MATRICULA,
              V_RECTX1,
              ROUND(V_TAXA1,2),
              V_QUATX,
              R_TAXA.j08_iptucalh);
            end if;

                        if nValIsenTaxa1 > 0 then
                            INSERT INTO IPTUCALV    (   j21_anousu,
                                                                            j21_matric,
                                                                            j21_receit,
                                                                            j21_valor,
                                                                            j21_quant,
                                                                            j21_codhis )
                                         VALUES ( ANOUSU,
                                                                            MATRICULA,
                                                                            V_RECTX1,
                                                                            ROUND((nValIsenTaxa1*-1),2),
                                                                            V_QLIMPEZA,
                                                                            R_TAXA.j08_histisen);
                        end if;

          END IF;

        END IF;

      END LOOP;

    END IF;

    -- GERA FINANCEIRO
    IF GERAFINANC = TRUE THEN
      --daa =  current_time;
      if v_raise is true then
        raise notice 'gerando financeiro...';
      end if;

      -- VERIFICA CODIGO DE ARRECADACAO
      SELECT J20_NUMPRE
      INTO V_NUMPRE
      FROM IPTUNUMP
      WHERE J20_ANOUSU = ANOUSU AND J20_MATRIC = MATRICULA;

      IF NOT V_NUMPRE IS NULL THEN
        IF CALCULO_GERAL = FALSE AND DEMO IS FALSE THEN
          if v_raise is true then
            raise notice 'deletando arrebanco, arrematric e arrecad...';
          end if;
          --               DELETE FROM ARREBANCO WHERE K00_NUMPRE = V_NUMPRE
          --         DELETE FROM ARREMATRIC WHERE K00_NUMPRE = V_NUMPRE;

          if v_raise is true then
            raise notice ' ';
            raise notice ' ';
            raise notice ' ';
          end if;

          FOR V_RECORD_ARRECAD IN SELECT distinct K00_NUMPAR FROM ARRECAD WHERE K00_NUMPRE = V_NUMPRE ORDER BY k00_numpar LOOP

            if v_raise is true then
              raise notice 'processando parcela: %', V_RECORD_ARRECAD.K00_NUMPAR;
            end if;
                        IF PARCELAFIM > 0 THEN
                            IF V_RECORD_ARRECAD.K00_NUMPAR >= PARCELAINI AND V_RECORD_ARRECAD.K00_NUMPAR <= PARCELAFIM THEN
                                if v_raise is true then
                                    raise notice '   deletando do arrecad parcela: %', V_RECORD_ARRECAD.K00_NUMPAR;
                                end if;
                                DELETE FROM ARRECAD WHERE K00_NUMPRE = V_NUMPRE AND K00_NUMPAR = V_RECORD_ARRECAD.K00_NUMPAR;
                            END IF;
                        ELSE
                            IF V_RECORD_ARRECAD.K00_NUMPAR >= PARCELAINI THEN
                                if v_raise is true then
                                    raise notice '   deletando do arrecad parcela: %', V_RECORD_ARRECAD.K00_NUMPAR;
                                end if;
                                DELETE FROM ARRECAD WHERE K00_NUMPRE = V_NUMPRE AND K00_NUMPAR = V_RECORD_ARRECAD.K00_NUMPAR;
                            END IF;
            END IF;

          END LOOP;

          if v_raise is true then
            raise notice ' ';
            raise notice ' ';
            raise notice ' ';
          end if;

        END IF;
        IF NOVONUMPRE = FALSE THEN
          V_MESMONUMPRE = TRUE;
        ELSE
          IF V_TEMPAGAMENTO = FALSE THEN
            IF CALCULO_GERAL = FALSE AND DEMO IS FALSE THEN
              if v_raise is true then
                raise notice 'deletando iptunump...';
              end if;
              DELETE FROM IPTUNUMP WHERE J20_ANOUSU = ANOUSU AND J20_MATRIC = MATRICULA;
            END IF;
            IF DEMO IS FALSE THEN
              SELECT NEXTVAL('numpref_k03_numpre_seq')::INTEGER
              INTO V_NUMPRE;
            END IF;
          END IF;
        END IF;
      ELSE
        IF DEMO IS FALSE THEN
          SELECT NEXTVAL('numpref_k03_numpre_seq')::INTEGER
          INTO V_NUMPRE;
        END IF;
      END IF;
      -- se imune sai
      IF NOT V_TIPOIS IS NULL THEN
        IF V_TIPOIS = 1 THEN
          RETURN '15 MATRICULA IMUNE';
        END IF;
      END IF;
      -- verifica taxas
      V_SOMA := 0;

      if v_raise is true then
        raise notice 'antes dos vencimentos';
      end if;

      V_MANUAL := V_MANUAL || 'PARCELA INICIAL BASEADA NOS PAGAMENTOS: ' || V_PARINI || ' \n ';

      V_TEXT = 'SELECT *
      FROM CADVENCDESC
      INNER JOIN CADVENC            ON Q92_CODIGO = Q82_CODIGO
      LEFT  JOIN CADVENCDESCBAN     ON Q93_CODIGO = Q92_CODIGO
      LEFT  JOIN CADBAN     ON K15_CODIGO = Q93_CADBAN
      WHERE Q92_CODIGO = ' || V_VENCIM || ' ORDER BY Q82_PARC';

      if v_raise is true then
        raise notice 'V_TEXT: %', V_TEXT;
      end if;

      V_PASSA = TRUE;

      FOR R_VENCIM IN EXECUTE V_TEXT LOOP
        --daa = current_time;
        if v_raise is true then
          raise notice 'gerando parcelas % pelo cadvencdesc - venc: %',R_VENCIM.Q82_PARC, R_VENCIM.Q82_VENC;
        end if;
        -- VERIFICA SE PARCELA INICIAL ESTA CERTA
        if v_raise is true then
          raise NOTICE 'INICIA % PARCELA % ',V_PARINI,R_VENCIM.Q82_PARC;
        end if;

        lProcessarArrecad := true;

        --
        -- Se parcela ja esta paga ou cancelada passa para a proxima
        --

        raise notice 'antes status debitos';

        if V_NUMPRE is not null then
          perform * from fc_statusdebitos(V_NUMPRE,R_VENCIM.Q82_PARC) where rtstatus = 'PAGO' or rtstatus = 'CANLELADO' limit 1;
          raise notice 'depois status debitos';
          if found then
            lProcessarArrecad := false;
          end if;
        end if;

        IF PARCELAINI = 0 THEN
          V_PASSA = TRUE;
        ELSE
          IF R_VENCIM.Q82_PARC >= PARCELAINI AND R_VENCIM.Q82_PARC <= PARCELAFIM THEN
            V_PASSA = TRUE;
          ELSE
            V_PASSA = FALSE;
          END IF;
        END IF;

        if v_raise is true then
          raise notice 'processando parcela: % - V_PASSA: %', R_VENCIM.Q82_PARC, V_PASSA;
        end if;

  --    IF V_PARINI <= R_VENCIM.Q82_PARC THEN

        IF V_PASSA IS TRUE THEN


          if v_raise is true then
            raise notice 'V_IPTU: % - V_TAXA1: % - V_TAXA2: %', V_IPTU, V_TAXA1, V_TAXA2;
          end if;
          IF V_IPTU > 0 THEN
            V_VALORPAR := ROUND(V_IPTU * ( R_VENCIM.Q82_PERC / 100),2)::FLOAT8;
            if V_MOSTRAR IS TRUE THEN
              if v_raise is true then
                raise notice 'soma % vparc % parcela % v_parcelas %',v_soma,v_valorpar,r_vencim.q82_parc, V_PARCELAS;
              end if;
            end if;
            IF V_PARCELAS = R_VENCIM.Q82_PARC THEN
              V_VALORPAR := ROUND(V_IPTU - V_DIFIPTU - V_SOMA,2)::FLOAT8;
            END IF;
            V_SOMA := V_SOMA + V_VALORPAR;
            V_TEMFINANC = TRUE;
            V_DIGITO := FC_DIGITO(V_NUMPRE,R_VENCIM.Q82_PARC,V_PARCELAS);
            IF DEMO IS FALSE THEN
              if V_MOSTRAR IS TRUE THEN
                if v_raise is true then
                  raise notice 'CGM: % - DTOPER: % - REC: % - HIST: % - VALOR: % - VENC: % - NUMPRE: % - PARC: % - PARC: % - DIGITO: % - TIPO: %',V_NUMCGM,V_DTOPER,V_RECIPTU,R_VENCIM.Q82_HIST,V_VALORPAR,R_VENCIM.Q82_VENC,V_NUMPRE,R_VENCIM.Q82_PARC,V_PARCELAS,V_DIGITO,R_VENCIM.Q92_TIPO;
                end if;
              end if;

              if lProcessarArrecad then
                 INSERT INTO ARRECAD (K00_NUMCGM,K00_DTOPER,K00_RECEIT,K00_HIST,K00_VALOR,K00_DTVENC,K00_NUMPRE,K00_NUMPAR,K00_NUMTOT,K00_NUMDIG,K00_TIPO)
                 VALUES( V_NUMCGM,
                 V_DTOPER,
                 V_RECIPTU,
                 R_VENCIM.Q82_HIST,
                 V_VALORPAR,
                 R_VENCIM.Q82_VENC,
                 V_NUMPRE,
                 R_VENCIM.Q82_PARC,
                 V_PARCELAS,
                 V_DIGITO,
                 R_VENCIM.Q92_TIPO);
              end if;
            END IF;
            V_TOTALZAO = V_TOTALZAO + V_VALORPAR;
          END IF;
          IF V_TAXA1 > 0 THEN
            V_VALORTX1 := ROUND( (V_TAXA1-coalesce(nValIsenTaxa1,0) ) *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            IF V_PARCELAS = R_VENCIM.Q82_PARC THEN
              V_VALORTX1 := ROUND(V_TAXA1 - V_DIFTX1 - V_SOMATX1 - coalesce(nValIsenTaxa1,0),2)::FLOAT8;
            END IF;
            V_SOMATX1 := V_SOMATX1 + V_VALORTX1;

            V_TEMFINANC = TRUE;
            IF DEMO IS FALSE THEN
              if V_MOSTRAR IS TRUE THEN
                if v_raise is true then
                  raise notice 'CGM: % - DTOPER: % - REC: % - HIST: % - VALOR: % - VENC: % - NUMPRE: % - PARC: % - PARC: % - DIGITO: % - TIPO: %',V_NUMCGM,V_DTOPER,V_RECTX1,R_VENCIM.Q82_HIST,V_VALORTX1,R_VENCIM.Q82_VENC,V_NUMPRE,R_VENCIM.Q82_PARC,V_PARCELAS,V_DIGITO,R_VENCIM.Q92_TIPO;
                end if;
              end if;
              if lProcessarArrecad then
                INSERT INTO ARRECAD (K00_NUMCGM,K00_DTOPER,K00_RECEIT,K00_HIST,K00_VALOR,K00_DTVENC,K00_NUMPRE,K00_NUMPAR,K00_NUMTOT,K00_NUMDIG,K00_TIPO)
                VALUES(V_NUMCGM,
                 V_DTOPER,
                 V_RECTX1,
                 R_VENCIM.Q82_HIST,
                 V_VALORTX1,
                 R_VENCIM.Q82_VENC,
                 V_NUMPRE,
                 R_VENCIM.Q82_PARC,
                 V_PARCELAS,
                 V_DIGITO,
                 R_VENCIM.Q92_TIPO);
              end if;
            END IF;
            V_TOTALZAO = V_TOTALZAO + V_VALORTX1;
          END IF;
          IF V_TAXA2 > 0 THEN
            V_VALORTX2 := ROUND(V_TAXA2 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            IF V_PARCELAS = R_VENCIM.Q82_PARC THEN
              V_VALORTX2 := ROUND(V_TAXA2 - V_DIFTX2 - V_SOMATX2,2)::FLOAT8;
            END IF;
            V_SOMATX2 := V_SOMATX2 + V_VALORTX2;

            V_TEMFINANC = TRUE;
            IF DEMO IS FALSE THEN
              if V_MOSTRAR IS TRUE THEN
                if v_raise is true then
                  raise notice 'CGM: % - DTOPER: % - REC: % - HIST: % - VALOR: % - VENC: % - NUMPRE: % - PARC: % - PARC: % - DIGITO: % - TIPO: %',V_NUMCGM,V_DTOPER,V_RECTX2,R_VENCIM.Q82_HIST,V_VALORTX2,R_VENCIM.Q82_VENC,V_NUMPRE,R_VENCIM.Q82_PARC,V_PARCELAS,V_DIGITO,R_VENCIM.Q92_TIPO;
                end if;
              end if;
              if lProcessarArrecad then
                INSERT INTO ARRECAD (K00_NUMCGM,K00_DTOPER,K00_RECEIT,K00_HIST,K00_VALOR,K00_DTVENC,K00_NUMPRE,K00_NUMPAR,K00_NUMTOT,K00_NUMDIG,K00_TIPO)
                VALUES(V_NUMCGM,
                V_DTOPER,
                V_RECTX2,
                R_VENCIM.Q82_HIST,
                V_VALORTX2,
                R_VENCIM.Q82_VENC,
                V_NUMPRE,
                R_VENCIM.Q82_PARC,
                V_PARCELAS,
                V_DIGITO,
                R_VENCIM.Q92_TIPO);
              end if;
            END IF;
            V_TOTALZAO = V_TOTALZAO + V_VALORTX2;
          END IF;
          IF V_TAXA3 > 0 THEN
            V_VALORTX3 := ROUND(V_TAXA3 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            IF V_PARCELAS = R_VENCIM.Q82_PARC THEN
              V_VALORTX3 := ROUND(V_TAXA3 - V_DIFTX3 - V_SOMATX3,2)::FLOAT8;
            END IF;
            V_SOMATX3 := V_SOMATX3 + V_VALORTX3;

            V_TEMFINANC = TRUE;
            IF DEMO IS FALSE THEN
              if V_MOSTRAR IS TRUE THEN
                if v_raise is true then
                  raise notice 'CGM: % - DTOPER: % - REC: % - HIST: % - VALOR: % - VENC: % - NUMPRE: % - PARC: % - PARC: % - DIGITO: % - TIPO: %',V_NUMCGM,V_DTOPER,V_RECTX3,R_VENCIM.Q82_HIST,V_VALORTX3,R_VENCIM.Q82_VENC,V_NUMPRE,R_VENCIM.Q82_PARC,V_PARCELAS,V_DIGITO,R_VENCIM.Q92_TIPO;
                end if;
              end if;
              if lProcessarArrecad then
                 INSERT INTO ARRECAD (K00_NUMCGM,K00_DTOPER,K00_RECEIT,K00_HIST,K00_VALOR,K00_DTVENC,K00_NUMPRE,K00_NUMPAR,K00_NUMTOT,K00_NUMDIG,K00_TIPO)
                 VALUES (V_NUMCGM,
                 V_DTOPER,
                 V_RECTX3,
                 R_VENCIM.Q82_HIST,
                 V_VALORTX3,
                 R_VENCIM.Q82_VENC,
                 V_NUMPRE,
                 R_VENCIM.Q82_PARC,
                 V_PARCELAS,
                 V_DIGITO,
                 R_VENCIM.Q92_TIPO);
              end if;
            END IF;
            V_TOTALZAO = V_TOTALZAO + V_VALORTX3;
          END IF;
          IF V_TAXA4 > 0 THEN
            V_VALORTX4 := ROUND(V_TAXA4 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            IF V_PARCELAS = R_VENCIM.Q82_PARC THEN
              V_VALORTX4 := ROUND(V_TAXA4 - V_DIFTX4 - V_SOMATX4,2)::FLOAT8;
            END IF;
            V_SOMATX4 := V_SOMATX4 + V_VALORTX4;

            V_TEMFINANC = TRUE;
            IF DEMO IS FALSE THEN
              if V_MOSTRAR IS TRUE THEN
                if v_raise is true then
                  raise notice 'CGM: % - DTOPER: % - REC: % - HIST: % - VALOR: % - VENC: % - NUMPRE: % - PARC: % - PARC: % - DIGITO: % - TIPO: %',V_NUMCGM,V_DTOPER,V_RECTX4,R_VENCIM.Q82_HIST,V_VALORTX4,R_VENCIM.Q82_VENC,V_NUMPRE,R_VENCIM.Q82_PARC,V_PARCELAS,V_DIGITO,R_VENCIM.Q92_TIPO;
                end if;
              end if;
              if lProcessarArrecad then
                 INSERT INTO ARRECAD (K00_NUMCGM,K00_DTOPER,K00_RECEIT,K00_HIST,K00_VALOR,K00_DTVENC,K00_NUMPRE,K00_NUMPAR,K00_NUMTOT,K00_NUMDIG,K00_TIPO)
                 VALUES (V_NUMCGM,
                 V_DTOPER,
                 V_RECTX4,
                 R_VENCIM.Q82_HIST,
                 V_VALORTX4,
                 R_VENCIM.Q82_VENC,
                 V_NUMPRE,
                 R_VENCIM.Q82_PARC,
                 V_PARCELAS,
                 V_DIGITO,
                 R_VENCIM.Q92_TIPO);
              end if;
            END IF;
            V_TOTALZAO = V_TOTALZAO + V_VALORTX4;
          END IF;
          IF V_TAXA5 > 0 THEN
            V_VALORTX5 := ROUND(V_TAXA5 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            IF V_PARCELAS = R_VENCIM.Q82_PARC THEN
              V_VALORTX5 := ROUND(V_TAXA5 - V_DIFTX5 - V_SOMATX5,2)::FLOAT8;
            END IF;
            V_SOMATX5 := V_SOMATX5 + V_VALORTX5;
            V_TEMFINANC = TRUE;
            IF DEMO IS FALSE THEN
              if V_MOSTRAR IS TRUE THEN
                if v_raise is true then
                  raise notice 'CGM: % - DTOPER: % - REC: % - HIST: % - VALOR: % - VENC: % - NUMPRE: % - PARC: % - PARC: % - DIGITO: % - TIPO: %',V_NUMCGM,V_DTOPER,V_RECTX5,R_VENCIM.Q82_HIST,V_VALORTX5,R_VENCIM.Q82_VENC,V_NUMPRE,R_VENCIM.Q82_PARC,V_PARCELAS,V_DIGITO,R_VENCIM.Q92_TIPO;
                end if;
              end if;
              if lProcessarArrecad then
                INSERT INTO ARRECAD (K00_NUMCGM,K00_DTOPER,K00_RECEIT,K00_HIST,K00_VALOR,K00_DTVENC,K00_NUMPRE,K00_NUMPAR,K00_NUMTOT,K00_NUMDIG,K00_TIPO)
                VALUES (V_NUMCGM,
                V_DTOPER,
                V_RECTX5,
                R_VENCIM.Q82_HIST,
                V_VALORTX5,
                R_VENCIM.Q82_VENC,
                V_NUMPRE,
                R_VENCIM.Q82_PARC,
                V_PARCELAS,
                V_DIGITO,
                R_VENCIM.Q92_TIPO);
              end if;
            END IF;
            V_TOTALZAO = V_TOTALZAO + V_VALORTX5;
          END IF;



        ELSE





          IF V_IPTU > 0 THEN
            V_VALORPAR := ROUND(V_IPTU * ( R_VENCIM.Q82_PERC / 100),2)::FLOAT8;
            V_DIFIPTU := V_DIFIPTU + V_VALORPAR;
          END IF;

          IF V_TAXA1 > 0 THEN
            V_VALORTX1 := ROUND(V_TAXA1 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            V_DIFTX1 := V_DIFTX1 + V_VALORTX1;
          END IF;

          IF V_TAXA2 > 0 THEN
            V_VALORTX2 := ROUND(V_TAXA2 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            V_DIFTX2 := V_DIFTX2 + V_VALORTX2;
          END IF;

          IF V_TAXA3 > 0 THEN
            V_VALORTX3 := ROUND(V_TAXA3 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            V_DIFTX3 := V_DIFTX3 + V_VALORTX3;
          END IF;

          IF V_TAXA4 > 0 THEN
            V_VALORTX4 := ROUND(V_TAXA4 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            V_DIFTX4 := V_DIFTX4 + V_VALORTX4;
          END IF;

          IF V_TAXA5 > 0 THEN
            V_VALORTX5 := ROUND(V_TAXA5 *( R_VENCIM.Q82_PERC / 100 ),2)::FLOAT8;
            V_DIFTX5 := V_DIFTX5 + V_VALORTX5;
          END IF;

        END IF;

      END LOOP;

      if v_raise is true then
        raise notice 'depois dos vencimentos';
      end if;

      IF V_TEMFINANC = TRUE THEN
        IF DEMO IS FALSE THEN
          SELECT K00_NUMPRE
          INTO V_NUMPREEXISTE
          FROM ARREMATRIC
          WHERE K00_NUMPRE = V_NUMPRE AND
          K00_MATRIC = MATRICULA;
          IF V_NUMPREEXISTE IS NULL THEN
            INSERT INTO ARREMATRIC VALUES ( V_NUMPRE,
            MATRICULA );
          END IF;
        END IF;
        IF V_MESMONUMPRE = FALSE AND DEMO IS FALSE THEN
          INSERT INTO IPTUNUMP VALUES ( ANOUSU,
          MATRICULA,
          V_NUMPRE );
        END IF;

        if DEMO is false then

          for rRecibosGerados in select distinct recibopaga.k00_numnov,
                                        recibopaga.k00_dtoper,
                                        recibopaga.k00_dtpaga
                                   from arrecad
                                        inner join recibopaga on recibopaga.k00_numpre = arrecad.k00_numpre
                                                             and recibopaga.k00_numpar = arrecad.k00_numpar
                                  where arrecad.k00_numpre = V_NUMPRE
          loop

            delete from recibopaga where k00_numnov = rRecibosGerados.k00_numnov;
            perform fc_recibo(rRecibosGerados.k00_numnov, rRecibosGerados.k00_dtoper, rRecibosGerados.k00_dtpaga, extract(year from rRecibosGerados.k00_dtpaga)::integer);
          end loop;

        end if;

      END IF;

    END IF;

    V_MANUAL := V_MANUAL || 'VALOR TOTAL A PAGAR: ' || V_TOTALZAO || '\n';

    IF DEMO IS FALSE THEN

      perform * from IPTUCALC where J23_MATRIC = MATRICULA AND J23_ANOUSU = ANOUSU;
      if found then
        UPDATE IPTUCALC SET J23_MANUAL = V_MANUAL WHERE J23_MATRIC = MATRICULA AND J23_ANOUSU = ANOUSU;
      else
        RETURN V_MANUAL;
      end if;

    END IF;

    IF DEMO IS TRUE THEN
      ----      V_MANUAL := '9 CALCULO CONCLUIDO';
      RETURN V_MANUAL;
    ELSE

      if v_raise is true then
        raise notice 'matricula: %',MATRICULA;
        raise notice 'demo: %',DEMO;
      end if;

      raise notice 'Demonstrativo : %', V_MANUAL;

      RETURN '01 CALCULO CONCLUIDO - MATRICULA '||MATRICULA;
    END IF;

    END;

$$ language 'plpgsql';create or replace function fc_calculoiptu(integer,integer,boolean,boolean,boolean,boolean,boolean,text[]) returns varchar(100) as
$$

declare

   iMatricula             alias for $1;
   iAnousu                alias for $2;
   lGeraFinanceiro        alias for $3;
   lAtualizaParcela       alias for $4;
   lNovoNumpre            alias for $5;
   lIsCalculoGeral        alias for $6;
   bDemo                      alias for $7;
   aParametrosOpcionais   alias for $8;

   iCodigoFuncao          integer default 0;
   iCodigoCliente         integer default 0;
   iTotalPosicoes         integer default 0;
   iIndice                integer default 0;
   iTotalParametros       integer default 0;

   aParametros            text[3];
   tChamadaCalculo        text;
   tRetornoCalculo        text default '';

   lRaise                 boolean default false;

begin

    lRaise  := ( case when fc_getsession('DB_debugon') is null then false else true end );

  select j18_db_sysfuncoes
    into iCodigoFuncao
    from cfiptu
   where j18_anousu = iAnousu;

  if not found then
    return 'CALCULO INDISPONIVEL PARA O ANO DE '||iAnousu;
  end if;

  select db21_codcli
    into iCodigoCliente
    from db_config
   where prefeitura is true;

  if not found then
    return 'CONFIGURACAO DO CLIENTE INDISPONIVEL';
  end if;

  aParametros[1] := iMatricula::text;
  aParametros[2] := iAnousu::text;
  aParametros[3] := case when lGeraFinanceiro  is true then 'true' else 'false' end;
  aParametros[4] := case when lAtualizaParcela is true then 'true' else 'false' end;
  aParametros[5] := case when lNovoNumpre      is true then 'true' else 'false' end;
  aParametros[6] := case when lIsCalculoGeral  is true then 'true' else 'false' end;
  aParametros[7] := case when bDemo            is true then 'true' else 'false' end;

  iTotalPosicoes   := ( array_upper(aParametrosOpcionais,1) + array_upper(aParametros,1) )::integer;
  iTotalParametros := array_upper(aParametros,1)::integer;

  for iIndice in 8..iTotalPosicoes loop
    aParametros[iIndice] := aParametrosOpcionais[ ( iIndice - iTotalParametros ) ];
  end loop;

  tChamadaCalculo := fc_montachamadafuncao(iCodigoFuncao, iCodigoCliente, aParametros, lRaise);

  perform fc_debug(' <calculoiptu> Funcao a ser executada: ' || tChamadaCalculo, lRaise);

  if tChamadaCalculo is not null then
    execute 'select '||tChamadaCalculo into tRetornoCalculo;
  end if;

  return tRetornoCalculo;

end;
$$  language 'plpgsql';drop function if exists fc_iptu_gerafinanceiro(integer,integer,integer,integer,boolean,boolean,boolean,boolean,boolean);
drop function if exists fc_iptu_gerafinanceiro(integer,integer,integer,integer,boolean,boolean,boolean,boolean,boolean,integer);

create or replace function fc_iptu_gerafinanceiro(integer,integer,integer,integer,boolean,boolean,boolean,boolean,boolean) returns boolean as
$$
declare

  iMatricula        alias for $1;
  iAnousu           alias for $2;
  iParcelaini       alias for $3;
  iParcelafim       alias for $4;
  lCalculogeral     alias for $5;
  lPossuiPagamento  alias for $6;
  lNovoNumpre       alias for $7;
  lDemonstrativo    alias for $8;
  lRaise            alias for $9;

  lExisteAbatimento boolean default false;
  lRetorno          boolean default false;

begin

  return ( select fc_iptu_gerafinanceiro(iMatricula, iAnousu, iParcelaini, iParcelafim, lCalculogeral, lPossuiPagamento, lNovoNumpre, lDemonstrativo, lRaise, 0) );

end;
$$  language 'plpgsql';


create or replace function fc_iptu_gerafinanceiro(integer,integer,integer,integer,boolean,boolean,boolean,boolean,boolean,integer) returns boolean as
$$
declare

  iMatricula                      alias for $1;
  iAnousu                         alias for $2;
  iParcelaini                     alias for $3;
  iParcelafim                     alias for $4;
  lCalculogeral                   alias for $5;
  lPossuiPagamento                alias for $6;
  lNovoNumpre                     alias for $7;
  lDemonstrativo                  alias for $8;
  lRaise                          alias for $9;
  iDiasVcto                       alias for $10;

  nValorParcela                   numeric(15,2) default 0;
  nValorIncrementalReceitaParcela numeric(15,2) default 0;
  nValorTotalCalculo                    numeric(15,2) default 0;
  nValorParcelaMinima                 numeric(15,2) default 0;
  nPercentualParcela                  numeric(15,2) default 0;
  nValorTotalAberto               numeric(15,2) default 0;
  nTotalGeradoReceita             numeric(15,2) default 0;

  iDigito                         integer default 0;
  iNumpre                         integer default 0;
  iParcelas                       integer default 0;
  iCgm                            integer default 0;
  iNumpreArrematric               integer default 0;
  iNumeroParcelasPagasCanceladas  integer default 0;
  iUltimaParcelaGerada            integer default 0;
  iDiaPadraoVencimento            integer default 0;
  iMesInicial                     integer default 0;
  iParcelasPadrao                 integer default 0;
  iParcelasProcessadas            integer default 0;
  iNumpreIptunump                 integer default 0;

  lExisteNumpre                   boolean default false;
  lUtilizandoMinima               boolean default false;
  lExisteFinanceiroGerado         boolean;
  lProcessaParcela                boolean;
  lProcessaVencimentoForcado      boolean default false;
  lExisteAbatimento               boolean default false;

  dDataOperacao                   date;

  tSqlVencimentos                 text default '';
  tManual                         text default '';

  rVencimentos                    record;
  rArrecad                        record;
  rValoresPorReceita              record;
  rDadosIptu                      record;
  rRecibosGerados                 record;

  begin

    -- Verifica se existe Pagamento Parcial para o débito informado
    select j20_numpre
      into iNumpreIptunump
      from iptunump
     where j20_matric = iMatricula
       and j20_anousu = iAnousu
     limit 1;

    if found then

      select fc_verifica_abatimento(1, iNumpreIptunump )::boolean into lExisteAbatimento;
      if lExisteAbatimento then
        raise exception '<erro>Operação Cancelada, Débito com Pagamento Parcial!</erro>';
      end if;
    end if;

    if lRaise is true then

      perform fc_debug('', lRaise, false, false);
      perform fc_debug(' <iptu_gerafinanceiro> Gerando financeiro', lRaise, false, false);
    end if;

    select coalesce( (select sum(k00_valor)
                        from arrecad
                       where k00_numpre = j20_numpre) ,0 ) as valor_total
      into nValorTotalAberto
      from iptunump
     where j20_matric = iMatricula
       and j20_anousu = iAnousu;

    iMesInicial          := iDiasVcto;
    iParcelasPadrao      := iParcelaini;
    iDiaPadraoVencimento := iParcelafim;

    if iMesInicial <> 0 and iParcelasPadrao <> 0 and iDiaPadraoVencimento <> 0 then
      lProcessaVencimentoForcado := true;
    end if;

    select * from tmpdadosiptu into rDadosIptu;

    select nparc
      into iParcelas
      from tmpdadostaxa;

    /**
     * Verifica codigo de arrecadacao
     */
    select j20_numpre
      into iNumpre
      from iptunump
     where j20_anousu = iAnousu
       and j20_matric = iMatricula;

    perform fc_debug(' <iptu_gerafinanceiro> Calculo geral : '||(case when lCalculogeral is true then 'Sim' else 'Nao' end), lRaise, false, false);
    perform fc_debug(' <iptu_gerafinanceiro> Numpre atual  : '||iNumpre                                   , lRaise, false, false);
    perform fc_debug(' <iptu_gerafinanceiro> parcelaini    : '||iParcelaini||' Parcelafim : '||iParcelafim, lRaise, false, false);

    if iNumpre is not null then

        /**
       * Se for calculo parcial e nao for demonstrativo
       */
      if lCalculogeral = false and lDemonstrativo is false then

        for rArrecad in select distinct k00_numpar
                          from arrecad
                         where k00_numpre = iNumpre
                      order by k00_numpar
        loop

                    if iParcelafim = 0 then

                        if rArrecad.k00_numpar >= iParcelaini then
                            delete from arrecad where k00_numpre = iNumpre and k00_numpar = rArrecad.k00_numpar;
                        end if;

                    else

                        if rArrecad.k00_numpar >= iParcelaini and rArrecad.k00_numpar <= iParcelafim then
                            delete from arrecad where k00_numpre = iNumpre and k00_numpar = rArrecad.k00_numpar;
                        end if;

                    end if;

        end loop;

      end if;

      if lNovoNumpre = false then

        lExisteNumpre = true;

      else

        if lPossuiPagamento = false then

          if lCalculogeral = false and lDemonstrativo is false then

            if lRaise is true then
              perform fc_debug(' <iptu_gerafinanceiro> Deletando iptunump', lRaise, false, false);
            end if;

            delete from iptunump
                  where j20_anousu = iAnousu
                    and j20_matric = iMatricula;
          end if;

          if lDemonstrativo is false then

            select nextval('numpref_k03_numpre_seq')::integer
              into iNumpre;
          end if;

        end if;

      end if;

    else

      if lDemonstrativo is false then
        select nextval('numpref_k03_numpre_seq')::integer into iNumpre;
      end if;

    end if;

    /**
     * Verifica imune
     */
    if not rDadosIptu.tipoisen is null then

      if rDadosIptu.tipoisen = 1 then
        return true;
      end if;
    end if;

    perform fc_debug(' <iptu_gerafinanceiro> Numpre: '||iNumpre, lRaise, false, false);

    /**
     * Verifica taxas
     */
    if lRaise is true then
      perform fc_debug(' <iptu_gerafinanceiro> Processando vencimentos', lRaise, false, false);
    end if;

    /**
     * Esta funcao retorna um select com a consulta para gerar os vencimentos
     * lendo os parametros iMesInicial,iParcelasPadrao,iDiaPadraoVencimento... se os parametros forem diferente de 0 a funcao
     * ira criar uma tabela temporaria com a estrutura do select do cadastro de vencimentos e retornara a string do select
     */
    tSqlVencimentos := ( select fc_iptu_getselectvencimentos(iMatricula,iAnousu,rDadosIptu.codvenc,iMesInicial,iParcelasPadrao,iDiaPadraoVencimento,nValorTotalAberto,lRaise) );

    execute 'select count(*) from ('|| tSqlVencimentos ||') as x'
       into iParcelas;

    lProcessaParcela = true;

    perform fc_debug(' <iptu_gerafinanceiro> Sql retornado dos vencimentos: ' || tSqlVencimentos, lRaise, false, false);

    /**
     * Cgm que sera gravado no arrecad e arrenumcgm
     */
    select fc_iptu_getcgmiptu(iMatricula) into iCgm;

    /**
     * Data de operacao do cfiptu
     */
    select j18_dtoper
      into dDataOperacao
      from cfiptu
     where j18_anousu = iAnousu;

    /**
     * Quantidade de receitas e valores gerados pelo calculo
     */
    select sum(valor)
      into nValorTotalCalculo
      from tmprecval;

    perform fc_debug(' <iptu_gerafinanceiro> Total retornado da tmporecval: '||nValorTotalCalculo, lRaise, false, false);

    /**
     * Valor de minimo da parcela
     */
    select q92_vlrminimo
        into nValorParcelaMinima
        from cadvencdesc
     where q92_codigo = rDadosIptu.codvenc;

    /**
     * Quantidade de parcelas que já foram
     * pagas e ou canceladas do iptu sendo gerado
     */
    select coalesce(count(distinct k00_numpar),0)
      into iNumeroParcelasPagasCanceladas
      from ( select distinct k00_numpar
               from arrecant
              where arrecant.k00_numpre = iNumpre
           ) as x;

      perform fc_debug(' <iptu_gerafinanceiro> TOTAL: '||nValorTotalCalculo||' - nValorParcelaMinima: '||nValorParcelaMinima||' - iParcelas: '||iParcelas||' - Divisao (nValorTotalCalculo / iParcelas): '||(nValorTotalCalculo / iParcelas), lRaise, false, false);

    if nValorTotalCalculo > 0 then

      perform fc_debug(' <iptu_gerafinanceiro> Inicia rateio de valor por parcela', lRaise, false, false);
      perform fc_debug(' <iptu_gerafinanceiro> Parcelas: '||iParcelas||' nValorTotalCalculo: '||nValorTotalCalculo, lRaise, false, false);
      perform fc_debug(' <iptu_gerafinanceiro> Verifica se ('||nValorTotalCalculo||' / '||iParcelas||') eh menor que o valor de parcela minimo '||nValorParcelaMinima, lRaise, false, false);
      if (nValorTotalCalculo / iParcelas) < nValorParcelaMinima then

                if floor((nValorTotalCalculo / nValorParcelaMinima)::numeric)::integer = 0 then
                  iParcelas := 1;
                else
          iParcelas := floor((nValorTotalCalculo / nValorParcelaMinima)::numeric)::integer;
                end if;

        lUtilizandoMinima := true;
        perform fc_debug(' <iptu_gerafinanceiro> Entrou em parcela minima... '       , lRaise, false, false);
        perform fc_debug(' <iptu_gerafinanceiro> Quantidade de Parcelas: '||iParcelas, lRaise, false, false);
      end if;

      perform fc_debug('', lRaise, false, false);
      perform fc_debug(' <iptu_gerafinanceiro> NUMPRE DO CALCULO: '||iNumpre, lRaise, false, false);
      perform fc_debug('', lRaise, false, false);

      perform fc_debug(' <iptu_gerafinanceiro> Percorrendo valores a serem gerados agrupado por receita '||iNumpre, lRaise, false, false);

      /**
       * Agrupa por receita
       */
      for rValoresPorReceita in select receita,
                                       (select count( distinct receita) from tmprecval) as qtdreceitas,
                                       sum(valor) as valor
                                  from tmprecval
                              group by receita
                              order by receita
      loop

        nValorIncrementalReceitaParcela := 0;
        iParcelasProcessadas            := 1;

        perform fc_debug(' <iptu_gerafinanceiro> iParcelasProcessadas: '||iParcelasProcessadas||' iParcelas: '||iParcelas, lRaise, false, false);

        /**
         * Percorre o record de vencimentos rateando o valor que fora agrupado por receita
         */
        for rVencimentos in execute tSqlVencimentos
        loop

          if lUtilizandoMinima is false then
            nPercentualParcela := cast(rVencimentos.q82_perc as numeric(15,2));
          else
            nPercentualParcela := 100::numeric / iParcelas;
          end if;

          perform fc_debug(' <iptu_gerafinanceiro> Percentual da parcela ' || nPercentualParcela, lRaise, false, false);

          if iParcelas < iParcelasProcessadas and lProcessaVencimentoForcado is false then

            perform fc_debug(' <iptu_gerafinanceiro> PARCELA '||rVencimentos.q82_parc||' NAO SERA CALCULADA', lRaise, false, false);
            perform fc_debug('', lRaise, false, false);
            continue;
          end if;

          if iParcelaini = 0 then

            perform fc_debug(' <iptu_gerafinanceiro> lProcessaParcela = true | iParcelaini = 0', lRaise, false, false);
            lProcessaParcela = true;
          else

            if rVencimentos.q82_parc >= iParcelaini and rVencimentos.q82_parc <= iParcelafim then
              lProcessaParcela = true;
            else
              lProcessaParcela = false;
            end if;

          end if;

          if lProcessaVencimentoForcado then
            lProcessaParcela = true;
          end if;

          perform fc_debug(' <iptu_gerafinanceiro> Processando parcela = '||( case when lProcessaParcela is true then 'Sim' else 'Nao' end ), lRaise, false, false);

          if lProcessaParcela is true then

            perform *
               from fc_statusdebitos(iNumpre, rVencimentos.q82_parc)
              where rtstatus = 'PAGO' or rtstatus = 'CANCELADO'
              limit 1;

            if found then

              perform fc_debug(' <iptu_gerafinanceiro> Ignorando parcela '||rVencimentos.q82_parc||' por estar paga ou cancelada', lRaise, false, false);
              perform fc_debug('', lRaise, false, false);
              continue;
            end if;

            if rValoresPorReceita.valor > 0 then

              if iParcelas = iParcelasProcessadas and iNumeroParcelasPagasCanceladas = 0 then
                nValorParcela := rValoresPorReceita.valor - nValorIncrementalReceitaParcela;
              else

                nValorParcela                   := trunc (rValoresPorReceita.valor * ( nPercentualParcela / 100::numeric )::numeric, 2 );
                nValorIncrementalReceitaParcela := nValorIncrementalReceitaParcela + nValorParcela;
              end if;

              lExisteFinanceiroGerado := true;
              iDigito                 := fc_digito(iNumpre, rVencimentos.q82_parc, iParcelas);

              perform fc_debug('', lRaise, false, false);
              perform fc_debug(' <iptu_gerafinanceiro> Parcela: '||rVencimentos.q82_parc||' Receita: '||rValoresPorReceita.receita||' Valor: '||nValorParcela, lRaise, false, false);

              if lDemonstrativo is false then

              iParcelasProcessadas = ( iParcelasProcessadas + 1 );

               if round(nValorParcela, 2) = 0 then

                 perform fc_debug(' <iptu_gerafinanceiro> Valor de parcela zerado, continue...', lRaise);
                 continue;
               end if;

                perform fc_debug(' <iptu_gerafinanceiro> GERANDO ARRECAD '                             , lRaise, false, false);
                perform fc_debug(' <iptu_gerafinanceiro> '                                             , lRaise, false, false);
                perform fc_debug(' <iptu_gerafinanceiro> Numpre .......: '||iNumpre                    , lRaise, false, false);
                perform fc_debug(' <iptu_gerafinanceiro> Numpar .......: '||rVencimentos.q82_parc      , lRaise, false, false);
                perform fc_debug(' <iptu_gerafinanceiro> Receita ......: '||rValoresPorReceita.receita , lRaise, false, false);
                perform fc_debug(' <iptu_gerafinanceiro> Valor ........: '||nValorParcela              , lRaise, false, false);
                perform fc_debug(' <iptu_gerafinanceiro> Vencimento ...: '||rVencimentos.q82_parc      , lRaise, false, false);

                delete from arrecad
                 where k00_numpre = iNumpre
                   and k00_numpar = rVencimentos.q82_parc
                   and k00_receit = rValoresPorReceita.receita;

                insert into arrecad (k00_numcgm,
                                     k00_dtoper,
                                     k00_receit,
                                     k00_hist,
                                     k00_valor,
                                     k00_dtvenc,
                                     k00_numpre,
                                     k00_numpar,
                                     k00_numtot,
                                     k00_numdig,
                                     k00_tipo)
                             values (iCgm,
                                     dDataOperacao,
                                     rValoresPorReceita.receita,
                                     rVencimentos.q82_hist,
                                     nValorParcela,
                                     rVencimentos.q82_venc,
                                     iNumpre,
                                     rVencimentos.q82_parc,
                                     iParcelas,
                                     iDigito,
                                     rVencimentos.q92_tipo);
              end if;

            end if;

          end if;

          perform fc_debug(' <iptu_gerafinanceiro> nValorParcela.: '||nValorParcela, lRaise, false, false);
          perform fc_debug(' <iptu_gerafinanceiro> nValorIncrementalReceitaParcela: ' || nValorIncrementalReceitaParcela, lRaise);

        end loop;

        /*
         * Lancando a diferenca na ultima parcela
         */
        select max(k00_numpar)
          into iUltimaParcelaGerada
          from arrecad
         where k00_numpre = iNumpre;

        select sum(k00_valor)
          into nTotalGeradoReceita
          from arrecad
         where k00_numpre = iNumpre
           and k00_receit = rValoresPorReceita.receita;

        update arrecad
           set k00_valor = ( k00_valor + ( rValoresPorReceita.valor - nTotalGeradoReceita ) )
         where k00_numpre = iNumpre
           and k00_numpar = iUltimaParcelaGerada
           and k00_receit = rValoresPorReceita.receita;

      end loop;

      if lRaise is true then

        perform fc_debug('', lRaise, false, false);
        perform fc_debug(' <iptu_gerafinanceiro> Verificando e gerando arrematric, iptunump e iptucalc' , lRaise, false, false);
      end if;

      if lExisteFinanceiroGerado = true then

        if lDemonstrativo is false then

          select k00_numpre
            into iNumpreArrematric
            from arrematric
           where k00_numpre = iNumpre
             and k00_matric = iMatricula;

          if iNumpreArrematric is null then
            insert into arrematric (k00_numpre, k00_matric) values (iNumpre, iMatricula);
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

        end if;

        if lExisteNumpre = false and lDemonstrativo is false then
          insert into iptunump (j20_anousu, j20_matric, j20_numpre) values (iAnousu, iMatricula, iNumpre);
        end if;

      end if;

    end if;

    if lDemonstrativo is false then
      update iptucalc set j23_manual = tManual where j23_matric = iMatricula and j23_anousu = iAnousu;
    end if;

    if lRaise is true then
      perform fc_debug(' <iptu_gerafinanceiro> Fim do processamento da funcao iptu_gerafinanceiro', lRaise, false, true);
    end if;

    return true;

  end;
$$  language 'plpgsql';create or replace function fc_issqn(integer,date,integer,date,boolean,boolean,integer,varchar)
 returns varchar(200)
as $$
  begin
    return fc_issqn($1, $2, $3, $4, $5, $6, $7, $8, 0);
  end;
$$ language 'plpgsql';


create or replace function fc_issqn(integer,date,integer,date,boolean,boolean,integer,varchar,integer)
 returns varchar(200)
as $$
  begin
    return fc_issqn($1, $2, $3, $4, $5, $6, $7, $8, $9, 1);
  end;
$$ language 'plpgsql';

create or replace function fc_issqn(integer,date,integer,date,boolean,boolean,integer,varchar,integer,integer)
returns varchar(200)
as $$
declare

  iInscr                    alias   for $1; -- inscricao que esta sendo calculada
  dDatahj                   alias   for $2; -- data do sistema
  iAnousu                   alias   for $3; -- ano do sistema
  dDtbaixa                  alias   for $4; -- data da baixa se estiver calculando baixa
  bRecalc                   alias   for $5; -- se e um recalculo
  bGeral                    alias   for $6; -- se e um calculo geral
  iInstit                   alias   for $7; -- instituicao
  sAtivs                    alias   for $8; -- relacao das atividades separadas por virgula. Exemplo: 1,2,3,4,5
  iTipoCalculo              alias   for $9; -- Tipo de calculo: 0 - Todos,
                                            --                  1 - ISSQN,
                                            --                  2 - Alvara
  iNumeroParcelasAlvara     alias   for $10;-- Quantidade de Parcelas em que o Alvare deve ser dividido

  iCalcfixvar               integer default 0;
  iDiasvctoCissqn           integer default 0;
  iConsiderarMesInicio      integer default 0;
  iMesFinal                 integer default 0;
  iDiaInicio                integer default 0;

  v_vencproc                integer default 0;
  v_quantexcluido           integer default 0;
  v_diasjasomados           integer default 0;
  v_anoatualservidor        integer;
  v_tipo_quant              integer;
  iTotalVencimentosCad      integer;
  v_anoiniciocalc           integer;
  v_mesinicio               integer;
  v_numprejapago            integer;
  v_uqtab                   integer;
  v_uqcad                   float8;
  v_ativprinc               integer;
  v_codvencadcalc           integer;
  v_codven                  integer;
  v_sequencia               integer;
  v_tabativ                 integer;
  v_ativtipo                integer;
  v_tipcalc                 integer;
  iInscrexiste              integer;
  v_quantativ               integer;
  v_numpre                  integer;
  v_numpar                  integer;
  v_numtot                  integer;
  v_numdig                  integer;
  v_numcgm                  integer;
  v_receita                 integer;
  v_codbco                  integer;
  iAux                      integer;
  v_diasdesdeinicio         integer;
  v_diasdestevcto           integer;
  iTotalDiasAno             integer;
  v_q81_rec                 integer;
  iQtdeVencProcessar        integer;
  v_totvenc                 integer default 0;
  iQtdParcelas              integer;
  iQtdParcelasPagas         integer;
  v_forcal                  integer;
  iDiasParaVencimento       integer default 0;
  iAnoCadastroEmpresa       integer default 0;
  iNumTot                   integer default 1;

  v_qprovisorio             float8 default 1;
  nValorParcela             float8;
  v_valor                   float8;
  v_valorgrav               float8 default 0;
  v_quant                   float8;
  v_base                    float8;
  v_valinflator             float8;
  v_q81_qini                float8;
  v_q81_qfim                float8;
  v_q81_val                 float8;
  nPercentualParcela        float8;

  nValorTotalPago           float8 default 0; -- total pago usado no caso de um recalculo
  nDescontoPagamentoParcela float8 default 0; -- desconto por parcela, e o valor a ser descontado por parcela no caso de um recalculo com pagamentos
  nPercPagoNovo             float8 default 0; -- percentual pago referente ao total do calculo, usado para calcular o desconto por parcela no caso de um recalculo com pagamento

  dDataCadastro             date;
  v_venc                    date;
  v_vencvar                 date;
  v_venccalc                date;
  iPrimeiroDiaAno           date;
  iUltimoDiaAno             date;
  v_dtvencano               date;
  dMaiorVencimentoCadvenc   date;
  dInicioAtividade          date;
  dInicioAtividadecalc      date;
  v_dtbase                  date;
  v_databaixa               date;
  dDtProporcionalidade      date;
  dVencimentoAtual          date;

  v_cep                     char(8);
  v_cepinstit               char(8);
  v_integr                  char(1);
  v_var                     char(1);
  v_codage                  char(5);
  v_numbco                  char(15);

  v_descrvariavel           varchar(50);
  v_descrtipcalc            varchar(40);
  v_descrcadcalc            varchar(40);
  sDescrProporcionalidade   varchar;

  v_text                    text;
  v_textexclui              text;
  v_textexclui2             text;
  v_manual                  text default '\n';
  tManual                   text;
  sSqlInsert                text;
  sManualCorrecao           text;
  sManualText               text;

  v_provisorio              boolean default false;
  v_cadcalc_var             boolean default false;
  v_cadcalc_fix             boolean default false;
  v_comcalculo              boolean default false;
  v_continuar               boolean default true;
  v_prim                    boolean;
  v_jagravou                boolean;
  lJaPassouUltVenc          boolean;
  bTemFixado                boolean default false; -- se tem valor fixado(varfixval)
  lProcessaParcVencidas     boolean default false;
  lProcessaParcela          boolean;
  lTabelasCriadas           boolean;
  lInscricaoMei             boolean default false;

  v_record_ativ             record;
  v_record_tipcalc          record;
  v_record_cadvenc          record;
  v_record_cadcalc          record;
  v_record_ativprinc        record;
  v_record_maiorvalor       record;
  v_record_somatodos        record;
  v_record_variavel         record;
  v_numprejacalculado       record;
  v_record_excluir          record;
  rDebitos                  record;
  rParcelasAlvara           record;

  dtOperacao                varchar;
  iNumpreArquivoSimples     integer;

  lRaise                    boolean default false; -- variavel para debug
  lAbatimento               boolean default false;

  begin

    select to_char(fc_getsession('DB_datausu')::date, 'DD/MM/YYYY') into dtOperacao;

      if  dtOperacao is null then
         RAISE EXCEPTION 'Erro: variavel de sessao DB_datausu nao declarado!';
      end if;


    if     iTipoCalculo = 1 then
       v_manual :=  '  ======================= Calculo de ISSQN          | Data de Calculo: ' || dtOperacao ||'  =========== \n';
    elsif  iTipoCalculo = 2 then
       v_manual :=  '  ======================= Calculo de Alvara         | Data de Calculo: ' || dtOperacao ||'  =========== \n';
    else
       v_manual :=  '  ======================= Calculo de ISSQN e Alvara | Data de Calculo: ' || dtOperacao ||'  =========== \n';
    end if;

    lRaise  := ( case when fc_getsession('DB_debugon') is null then false else true end );

    perform fc_debug('INICIANDO CALCULO PARA INSCRICAO : '||iInscr||' EXERCICIO : '||iAnousu,lRaise,true,false);

    perform fc_debug('DATA DE BAIXA - '||dDtbaixa,lRaise,false,false);

    perform * from ativtipo
      where q80_ativ in ( select distinct q07_ativ from tabativ where q07_inscr = iInscr );
    if not found then
      return '24-Empresa sem tipo de calculo configurado!';
    end if;

    --
    -- Realizamos a conferÃªncia dos dados para sabermos se a inscriÃ§Ã£o Ã© optante pelo SIMPLES
    --
    -- Caso seja optante nÃ£o serÃ¡ calculado a taxa de alvarÃ¡ da empresa.
    --
    -- Deve possui cadastro na tabela meicgm...
    -- OU
    -- A empresa deve ser optante com:
    --  - A categoria 3 - MEI;
    --  - Data de inÃ­cio do cadastro no simples menor ou igual a data de calculo;
    --  - NÃ£o pode estar com o cadastro do simples baixado (isscadsimplesbaixa)
    --

    perform *
       from meicgm
            inner join issbase on issbase.q02_numcgm = meicgm.q115_numcgm
      where issbase.q02_inscr = iInscr;

    if found then
      lInscricaoMei = true;
    end if;

    perform *
       from isscadsimples
      where isscadsimples.q38_inscr     = iInscr
        and isscadsimples.q38_categoria = 3
        and isscadsimples.q38_dtinicial <= dDatahj
        and not exists ( select 1
                           from isscadsimplesbaixa
                          where isscadsimplesbaixa.q39_isscadsimples = isscadsimples.q38_sequencial
                            and q39_dtbaixa <= dDatahj );
    if found then
      lInscricaoMei = true;
    end if;

    --
    -- FIM DA CONFERÃŠNCIA DE CALCULO DO MEI
    --

    select extract(year from q02_dtinic)
      into iAnoCadastroEmpresa
      from issbase
     where q02_inscr = iInscr;

    if iAnousu < iAnoCadastroEmpresa then
      return '24-NÃ£o pode ser feito calculo para exercicio menor que o ano de cadastramento da empresa.';
    end if;

    select fc_issqn_criatemptable(lRaise)
      into lTabelasCriadas;
    if lTabelasCriadas is false then
      return '24-Problema ao criar as tabelas temporarias. ';
    end if;

    for rDebitos in
      select q01_numpre
        from isscalc
       where q01_inscr  = iInscr
         and q01_anousu = iAnousu
    loop

      -- Verifica se existe Pagamento Parcial para o dÃ©bito informado
      select fc_verifica_abatimento(1,rDebitos.q01_numpre)::boolean into lAbatimento;

      if lAbatimento then
        return '24-OperaÃ§Ã£o Cancelada, DÃ©bito com Pagamento Parcial!';
      end if;

    end loop;


    sSqlInsert := '
    insert into ativs (q07_inscr,q07_perman,q07_seq,q07_calcula,q07_ativ,q03_descr,q07_datain,q07_datafi,q07_databx,q07_quant,q11_tipcalc)
           select distinct
                  q07_inscr,
                  q07_perman,
                  q07_seq, \'*\'::char(1) as q07_calcula,
                  q07_ativ,
                  q03_descr,
                  q07_datain,
                  q07_datafi,
                  q07_databx,
                  q07_quant,
                  q11_tipcalc
             from tabativ
                  left outer join tabativtipcalc on q11_inscr   = q07_inscr
                                                and q11_seq     = q07_seq
                  inner join ativtipo            on q07_ativ    = q80_ativ
                  inner join tipcalc             on q80_tipcal  = q81_codigo
                  inner join ativid              on q07_ativ    = q03_ativ
                  inner join cadcalc             on q81_cadcalc = q85_codigo
            where q07_inscr ='||  iInscr ||'
              and q07_seq in ('||sAtivs||')
     union
           select distinct
                  q07_inscr,
                  q07_perman,
                  q07_seq, \'*\'::char(1) as q07_calcula,
                  q07_ativ,
                  q03_descr,
                  q07_datain,
                  q07_datafi,
                  q07_databx,
                  q07_quant,
                  q11_tipcalc
             from tabativ
                  left outer join tabativtipcalc on q11_inscr     = q07_inscr
                                                and q11_seq       = q07_seq
                  inner join clasativ            on q82_ativ      = q07_ativ
                  inner join issbaseporte        on q45_inscr     = q07_inscr
                  inner join issportetipo        on q41_codclasse = q82_classe
                                                and q41_codporte  = q45_codporte
                  inner join tipcalc             on q81_codigo    = q41_codtipcalc
                  inner join ativid              on q07_ativ      = q03_ativ
                  inner join cadcalc             on q81_cadcalc   = q85_codigo
            where q07_inscr =  '|| iInscr ||'
              and q07_seq in ('||sAtivs||')' ;

    execute sSqlInsert;

    select count(*) from ativs into v_sequencia;

    v_sequencia = 1;

    --
    -- Primeiro dia do ano para calculo
    --
    iPrimeiroDiaAno = to_char(iAnousu, '9999') || '-01-01';

    --
    -- Ultimo dia do ano para calculo
    --
    iUltimoDiaAno = to_char(iAnousu, '9999') || '-12-31';

    --
    -- Total de dias do ano
    --
    iTotalDiasAno  = iUltimoDiaAno::date - iPrimeiroDiaAno::date + 1;

    select q02_inscr
      from issbase
      into iInscrexiste
     where q02_inscr = iInscr;
    if iInscrexiste is null then
      return '11-INSCRICAO NAO CADASTRADA ';
    end if;

    select q02_dtinic,
           q02_dtinic,
           extract (month from q02_dtinic)
      from issbase
      into dInicioAtividade,
           dDataCadastro,
           v_mesinicio
     where q02_inscr = iInscr;

    if dInicioAtividade is null then

      select q02_dtinic,
             extract (month from q02_dtinic)
        from issbase
        into dInicioAtividade,
             v_mesinicio
       where q02_inscr = iInscr;

      if dInicioAtividade is null then
        select min(q07_datain),
               extract (month from min(q07_datain))
          into dInicioAtividade,
               v_mesinicio
          from tabativ
         where (q07_datafi is null or q07_datafi >= dDatahj)
           and (q07_databx is null or q07_databx >= dDatahj);

        if dInicioAtividade is null then
          return '12-INSCRICAO SEM DATA DE INICIO CONFIGURADA! ';
        else
          v_manual = v_manual || 'data de inicio da inscricao (baseada na menor data de inicio das atividades): ' || dInicioAtividade || '\n';
        end if;
      else

        v_manual = v_manual || 'data de inicio da inscricao (baseada na data de cadastramento): ' || dInicioAtividade || '\n';

      end if;

    else
      v_manual = v_manual || 'data de inicio da inscricao: ' || dInicioAtividade || '\n';
    end if;

    select extract (year from dDatahj) into v_anoatualservidor;

    perform fc_debug('inscr: '||iInscr||' - inicio: '||dInicioAtividade,lRaise,false,false);

--*********************************** se calcula por area ou por quantidade de funcionarios ********************************************************
--
-- grande bloco que verifica a variavel v_quant, que serÃ¡ utilizada para encontrar posteriormente o tipcalc a ser utilizado no calculo
--
    select q60_campoutilcalc
      into v_tipo_quant
      from parissqn;

--  se o campo q60_campoutilcalc for igual a 1 calcula pelo campo q30_area

    if v_tipo_quant is null then
      return '13-PARAMETRO DE QUANTIDADE PARA O CALCULO NAO CONFIGURADO NA TABELA PARISSQN';
    end if;

--**************************************************************************************************************************************************

    select q04_vbase,
           q04_calfixvar,
           q04_diasvcto
      from cissqn
      into v_base,
           iCalcfixvar,
           iDiasvctoCissqn
     where cissqn.q04_anousu = iAnousu;

    if v_base = 0 or v_base is null then
      return '14-SEM VALOR BASE CADASTRADO NOS PARAMETROS ';
    end if;

    v_manual = v_manual || 'valor base configurado nos parametros: ' || v_base || '\n';

    select q04_dtbase
      from cissqn
      into v_dtbase
    where cissqn.q04_anousu = iAnousu;
    if v_dtbase is null then
      return '15-SEM DATA BASE CADASTRADA NOS PARAMETROS ';
    end if;
    v_manual = v_manual || 'data base configurada nos parametros: ' || v_dtbase || '\n';

    select distinct i02_valor
      from cissqn
      into v_valinflator
           inner join infla on q04_inflat = i02_codigo
     where cissqn.q04_anousu = iAnousu
       and date_part('y',i02_data) = iAnousu;
    if v_valinflator is null then
      v_valinflator = 1;
--      return 'valor do inflator nao configurado corretamente';
    end if;
    v_manual = v_manual || 'valor do inflator configurada nos parametros: ' || v_valinflator || '\n';

    perform fc_debug('valor do inflator : '||v_valinflator,lRaise,false,false);

    select q02_dtbaix
      from issbase
      into v_databaixa
     where q02_inscr = iInscr;
    if v_databaixa is not null then
      return '16-INSCRICAO JA BAIXADA ';
    end if;

    -- Q81_TIPO:
    -- 1 issqn
    -- 4 alvara
    -- 5 taxa de expediente
    select count(*) into v_quantativ from (
    select distinct q07_seq
      from tabativ
           inner join ativtipo on ativtipo.q80_ativ = tabativ.q07_ativ
           inner join tipcalc on q81_codigo = q80_tipcal
     where q81_tipo in (1,4,5)
       and q07_inscr = iInscr
       and (q07_datafi is null or q07_datafi >= dDatahj)
       and (q07_databx is null or q07_databx >= dDatahj)) as x;

    perform fc_debug('Total de atividades : '||v_quantativ,lRaise,false,false);

    select q07_inscr
      from tabativ
      into v_tabativ
           inner join ativtipo on ativtipo.q80_ativ = tabativ.q07_ativ
     where q07_inscr = iInscr
       and q07_datain <= dDatahj;

    select q07_inscr
      from tabativ
      into v_ativtipo
           inner join ativtipo on ativtipo.q80_ativ = tabativ.q07_ativ
     where q07_inscr = iInscr
       and (q07_datafi is null or q07_datafi >= dDatahj)
       and (q07_databx is null or q07_databx >= dDatahj);

    select q07_inscr
      from tabativ
      into v_tipcalc
           inner join ativtipo on ativtipo.q80_ativ = tabativ.q07_ativ
           inner join tipcalc  on q81_codigo = q80_tipcal
     where q81_tipo in (1,4,5)
       and q07_inscr = iInscr
       and q07_datain <= dDatahj;

    perform fc_debug('Data da baixa : '||dDtbaixa,lRaise,false,false);

-- verifica quais os tipos de calculo para as atividades cadastradas para a inscricao

    select cep
      from db_config
      into v_cepinstit
     where codigo = iInstit;

    if v_cepinstit is null then
      return '17-PROBLEMAS COM A TABELA DB_CONFIG';
    end if;
    v_manual = v_manual || 'cep da instituicao: ' || v_cepinstit || '\n';

    select q02_cep
     from issbase
     into v_cep
    where q02_inscr = iInscr;

    v_manual = v_manual || 'cep da inscricao: ' || v_cep || '\n';

    v_manual = v_manual || '\n--- p r i m e i r a   e t a p a  -----\n';

    select distinct
           q07_inscr
      into iAux
      from ativs;

    perform fc_debug('Inscricao tabela temporaria (ativs) : '||iAux,lRaise,false,false);

    -- ativs Ã© uma tabela temporÃ¡ria criada antes de chamar a funcao

    for v_record_ativ in execute 'select * from ativs where q07_inscr = ' || iInscr loop


      v_manual = v_manual || '\nprocessando atividade: ' || v_record_ativ.q07_ativ || ' - ' || v_record_ativ.q03_descr || ' - sequencia: ' || v_record_ativ.q07_seq || ' - inicio: ' || v_record_ativ.q07_datain || '\n';
      --
      -- sempre entra aqui porque ninguem utiliza o esquema da tabela
      -- tabativtipcalc (tipo de calculo especifico para aquela atividade daquela inscricao)
      --
      if v_record_ativ.q11_tipcalc is null then
        v_text = 'select distinct
                         tipcalc.*,
                         cadcalc.q85_outromun,
                         cadcalc.q85_var,
                         case
                           when q81_tipo = 4 then
                             ( select q83_codven
                                 from ativtipo ativtipo2
                                      inner join tipcalc    on ativtipo2.q80_tipcal = q81_codigo
                                      left  join tipcalcexe on q83_tipcalc = q81_codigo
                                                           and q83_anousu  = (select extract(year from q02_dtinic)
                                                                                from issbase
                                                                               where q02_inscr = '||iInscr||')
                                where ativtipo2.q80_ativ = ativtipo.q80_ativ and ativtipo2.q80_tipcal = ativtipo.q80_tipcal )
                           else
                             q83_codven
                         end as q83_codven
                    from ativtipo
                         inner join tipcalc    on q80_tipcal = q81_codigo
                         left join tipcalcexe  on q83_tipcalc = q81_codigo
                                              and q83_anousu = ' || iAnousu || '
                         inner join cadcalc    on cadcalc.q85_codigo = tipcalc.q81_cadcalc
                   where q81_tipo in (1,4,5)
                     and q80_ativ = ' || v_record_ativ.q07_ativ || '
                 union
                  select tipcalc.*,
                         cadcalc.q85_outromun,
                         cadcalc.q85_var,
                         case
                           when q81_tipo = 4 then
                             ( select q83_codven
                                 from tipcalc tipcalc2
                                      left  join tipcalcexe tipcalcexe2   on tipcalcexe2.q83_tipcalc = tipcalc2.q81_codigo
                                                             and tipcalcexe2.q83_anousu  = (select extract(year from issbase2.q02_dtinic)
                                                                                  from issbase issbase2
                                                                                 where issbase2.q02_inscr = '||iInscr||')
                                      inner join cadcalc cadcalc2  on cadcalc2.q85_codigo = tipcalc2.q81_cadcalc
                                      inner join clasativ clasativ2  on clasativ2.q82_classe = issportetipo.q41_codclasse and clasativ2.q82_ativ = ' || v_record_ativ.q07_ativ || '
                                where tipcalc2.q81_codigo = issportetipo.q41_codtipcalc )
                           else
                             q83_codven
                         end as q83_codven
                    from issportetipo
                         inner join issbaseporte on q45_inscr = ' || iInscr || '
                         inner join tipcalc      on q41_codtipcalc = q81_codigo
                         left  join tipcalcexe   on q83_tipcalc = q81_codigo
                                                and q83_anousu = ' || iAnousu || '
                         inner join cadcalc      on cadcalc.q85_codigo = tipcalc.q81_cadcalc
                         inner join clasativ     on q82_classe = q41_codclasse
                   where q45_codporte = q41_codporte
                     and q81_tipo in (1,4,5)
                     and q82_ativ = ' || v_record_ativ.q07_ativ;

        v_textexclui = 'select distinct
                               tipcalc.q81_cadcalc
                          from ativtipo
                               inner join tipcalc on q80_tipcal = q81_codigo
                               inner join cadcalc on cadcalc.q85_codigo = tipcalc.q81_cadcalc
                         where q81_tipo in (1,4,5)
                           and q80_ativ = ' || v_record_ativ.q07_ativ || '
                       union
                        select tipcalc.q81_cadcalc
                          from issportetipo
                               inner join issbaseporte on q45_inscr = ' || iInscr || '
                               inner join tipcalc      on q41_codtipcalc = q81_codigo
                               inner join cadcalc      on cadcalc.q85_codigo = tipcalc.q81_cadcalc
                               inner join clasativ     on q82_classe = q41_codclasse
                         where q45_codporte = q41_codporte
                           and q81_tipo in (1,4,5)
                           and q82_ativ = ' || v_record_ativ.q07_ativ;
      else
        v_text = 'select tipcalc.*,
                         q85_outromun,
                         cadcalc.q85_var
                    from tipcalc
                         left outer join cadcalc on cadcalc.q85_codigo = tipcalc.q81_cadcalc
                   where q81_tipo in (1,4,5)
                     and q81_codigo = ' || v_record_ativ.q11_tipcalc;
      end if;

-- deletando calculo atual da inscricao do ano
      v_manual = v_manual || 'deletando os calculos antigos da inscricao no ano - apenas os que nao serao recalculados neste calculo\n';

      v_textexclui2 = 'select q01_numpre,
                              q01_cadcal
                         from isscalc
                        where q01_cadcal not in ( ' || v_textexclui || ')
                          and q01_inscr  = ' || iInscr || '
                          and q01_anousu = ' || iAnousu;

      -- limpa o financeiro do que foi selecionado pelo usuario antes de calcular

      for v_record_excluir in execute v_textexclui2 loop

        perform fc_debug('Numpre : '||v_record_excluir.q01_numpre,lRaise,false,false);
        -- verifica se nÃ£o esta no arrecant
        select k00_numpre
          from arrecant
          into v_numprejapago
         where k00_numpre = v_record_excluir.q01_numpre;
        if v_numprejapago is null then

          v_manual = v_manual || '     deletando numpre ' || v_record_excluir.q01_numpre || ' - calculo: ' || v_record_excluir.q01_cadcal || '\n';
          delete from arrecad where k00_numpre = v_record_excluir.q01_numpre;
          delete from isscalc where q01_numpre = v_record_excluir.q01_numpre;
          v_quantexcluido = v_quantexcluido + 1;

        else
          perform fc_debug('Numpre ja pago : '||v_record_excluir.q01_numpre,lRaise,false,false);
        end if;

      end loop;

      v_manual = v_manual || 'quantidade de calculos excluidos: ' || v_quantexcluido || ' \n';

      perform fc_debug('Atividade : '||v_record_ativ.q07_ativ,lRaise,false,false);

      perform fc_debug('',lRaise,false,false);
      perform fc_debug('Sql buscando os tipos de calculo : '||v_text,lRaise,false,false);
      perform fc_debug('',lRaise,false,false);

      -- para cada atividade retornada com seu tipo de calculo...


      for v_record_tipcalc in execute v_text loop


         select *
           into v_quant, tManual
           from fc_buscaquantidadeempresa( cast(iInscr                 as integer),
                                           cast(iAnousu                as integer),
                                           cast(v_tipo_quant           as integer),
                                           cast(v_record_ativ.q07_ativ as integer)
                                         );
         perform fc_debug('========== Excluindo registros da porcalculo e "do": '||tManual,lRaise,false,false);


         perform fc_debug('========== QUANTIDADES : '||tManual,lRaise,false,false);

         v_manual = v_manual||tManual;

         if v_quant = 0 and v_tipo_quant = 3 then
           return '30 - Erro buscando as quantidades para o calculo por pontuaÃ§Ã£o. Verifique o cadastro de pontuaÃ§Ã£o das Classes, Areas, Zonas e Empregados';
         end if;


        perform fc_debug('========== Tipo de calculo : '||v_record_tipcalc.q81_codigo||'-'||v_record_tipcalc.q81_descr||' - Quantidade : '||v_quant,lRaise,false,false);

        v_continuar = true;

        perform fc_debug('p r o c e s s a n d o  tipo de calculo: ' || v_record_tipcalc.q81_codigo || ' - ' || v_record_tipcalc.q81_descr || ' - gera: ' || v_record_tipcalc.q81_gera,lRaise,false,false);

        if  extract(year from dInicioAtividade )::integer = iAnousu then
          v_q81_rec  = v_record_tipcalc.q81_recexe;
          v_q81_qini = v_record_tipcalc.q81_qiexe;
          v_q81_qfim = v_record_tipcalc.q81_qfexe;
          v_q81_val  = v_record_tipcalc.q81_valexe;
        else
          v_q81_rec  = v_record_tipcalc.q81_recpro;
          v_q81_qini = v_record_tipcalc.q81_qipro;
          v_q81_qfim = v_record_tipcalc.q81_qfpro;
          v_q81_val  = v_record_tipcalc.q81_valpro;
        end if;

        perform fc_debug('Quantidade : '||coalesce(v_quant,0)||' Entre : '||coalesce(v_q81_qini,0)||' e '||coalesce(v_q81_qfim,0),lRaise,false,false);

        if coalesce(v_quant,0) >= coalesce(v_q81_qini,0) and coalesce(v_quant,0) <= coalesce(v_q81_qfim,999999) then

          perform fc_debug('Dentro do if da quantidade gera : '||v_record_tipcalc.q81_gera,lRaise,false,false);
          perform fc_debug('Ano de inicio de atividades : '||to_number(substr(dInicioAtividade,1,4),'99999'),lRaise,false,false);

          if v_record_tipcalc.q81_gera = 1 and to_number(substr(dInicioAtividade,1,4),'99999') < iAnousu then
            perform fc_debug('nao vai processar...',lRaise,false,false);
            v_manual = v_manual || '\n';
          else

            perform fc_debug('entrou no gera 2 do if - verificando pagamentos ',lRaise,false,false);

            v_manual = v_manual || 'verificando pagamentos\n';

            for v_numprejacalculado in
              select q01_numpre
                from isscalc
               where q01_anousu = iAnousu
                 and q01_inscr  = iInscr
                 and q01_cadcal = v_record_tipcalc.q81_cadcalc
                 and q01_recei  = v_q81_rec
            loop

              perform fc_debug('procurando numpre : '||v_numprejacalculado.q01_numpre,lRaise,false,false);
              v_manual = v_manual || 'processando numpre: ' || v_numprejacalculado.q01_numpre || '\n';

              -- trocado ordem de verificaÃ§Ã£o de arrecad, arrecant para arrecant, arrecad
              select k00_numpre
                from arrecant
                into v_numprejapago
               where k00_numpre = v_numprejacalculado.q01_numpre;

              perform fc_debug('q01_numpre: '|| v_numprejacalculado.q01_numpre,lRaise,false,false);

              if v_numprejacalculado.q01_numpre is not null then

                perform fc_debug('v_numprejacalculado.q01_numpre is not null - v_numprejapago: '||v_numprejapago,lRaise,false,false);

                if v_numprejapago is null then

                  v_manual = v_manual || 'numpre em aberto\n';

                  if dDtbaixa is not null and v_record_tipcalc.q85_var is true then
                    v_manual = v_manual || 'se data da baixa preenchido e variavel, deleta do arrecad e issvar\n';

                    perform fc_debug('Deletando do arrecad e issvar',lRaise,false,false);

                    delete from arrecad where k00_numpre = v_numprejacalculado.q01_numpre and k00_numpar >= to_number(substr(dDtbaixa,6,2),'999') + 1;

                    perform *
                       from issvarlev
                            inner join issvar on issvar.q05_codigo = issvarlev.q18_codigo
                      where q05_numpre = v_numprejacalculado.q01_numpre
                        and q05_numpar >= to_number(substr(dDtbaixa,6,2),'999') + 1;
                    if found then
                      return '28 - EMPRESA JA POSSUI LEVANTAMENTO FISCAL PARA COMPETENCIA : '||(to_number(substr(dDtbaixa,6,2),'999') + 1)||'/'||iAnousu ;
                    end if;

                    delete from issvarnotas
                     using issvar
                     where issvar.q05_codigo  = issvarnotas.q06_codigo
                       and issvar.q05_numpre  = v_numprejacalculado.q01_numpre
                       and issvar.q05_numpar >= to_number(substr(dDtbaixa,6,2),'999') + 1;

                    delete from issvar
                     where q05_numpre = v_numprejacalculado.q01_numpre
                       and q05_numpar >= to_number(substr(dDtbaixa,6,2),'999') + 1;

                  elsif dDtbaixa is not null then

                    v_manual = v_manual || 'se data da baixa preenchido e nao variavel, insere no isscalcant e deleta do isscalc e arrecad\n';

                    perform fc_debug('Deletando do arrecad e isscalv',lRaise,false,false);

                    insert into isscalcant select * from isscalc where q01_numpre = v_numprejacalculado.q01_numpre;
                    delete from isscalc where q01_numpre = v_numprejacalculado.q01_numpre;
                    delete from arrecad where k00_numpre = v_numprejacalculado.q01_numpre;
                  end if;

                else

                  perform fc_debug('Numpre pago.',lRaise,false,false);

                  v_manual = v_manual || 'numpre pago\n';
                  v_comcalculo = true;

                end if;

                if v_numprejapago is null then

                  select k00_numpre
                  from arrecad
                  into v_numprejapago
                  where k00_numpre = v_numprejacalculado.q01_numpre;

                  if v_numprejapago is not null then

                    if bRecalc is false then
                      return '18-INSCRICAO JA CALCULADA E RECALCULO NAO PASSADO COMO PARAMETRO';
                    end if;

                    perform fc_debug('recalculo',lRaise,false,false);

                    v_manual = v_manual || 'recalculo\n';
                    v_comcalculo = true;
                  end if;

                end if;

              end if;

            end loop;

            perform fc_debug('saiu do procura pagamentos - continuar : '||( case when v_continuar is true then 'true' else 'false' end ),lRaise,false,false);

            if v_continuar then

                -- se true, busca quantidade do tabativ, senao default 1
              if v_record_tipcalc.q81_uqtab is false then

                perform fc_debug('uqtab false',lRaise,false,false);

                v_uqtab = 1;
                v_manual = v_manual || 'quantidade utilizada da tabela para calculo (utilizada default do sistema):' || v_uqtab || '\n';
              else

                perform fc_debug('uqtab true',lRaise,false,false);

                if v_record_ativ.q07_quant = 0 then
                  v_uqtab = 1;
                else
                  v_uqtab = v_record_ativ.q07_quant;
                end if;
                v_manual = v_manual || 'quantidade utilizada para calculo (baseada na quantidade da atividade lancada): ' || v_uqtab || '\n';
              end if;

              -- se true, busca quantidade do issquant, senao default 1
              if v_record_tipcalc.q81_uqcad is false then
                v_uqcad = 1;
              else
                select q30_mult
                  from issquant
                  into v_uqcad
                 where issquant.q30_inscr = iInscr
                   and issquant.q30_anousu = iAnousu;
                if v_uqcad is null then
                  return '19-MULTIPLICADOR NAO LANCADO PARA ESTA INSCRICAO';
                end if;

              end if;

              if v_record_tipcalc.q81_integr is true then
                v_integr = '1';
              else
                v_integr = '0';
              end if;
              v_manual = v_manual || 'integral: (1 = sim - 0 = nao): ' || v_integr || '\n';

              select case when q85_var is true then '1' else '0' end as q85_var
              from cadcalc
              into v_var
              where cadcalc.q85_codigo = v_record_tipcalc.q81_cadcalc;
              if v_var is null then
                return '20-NAO DEFINIDO NO CADASTRO DE CALCULO SE VARIAVEL OU NAO';
              end if;

              select q85_forcal
              from cadcalc
              into v_forcal
              where cadcalc.q85_codigo = v_record_tipcalc.q81_cadcalc;
              if v_forcal is null then
                return '21-nAO DEFINIDO NO CADASTRO DE CALCULO A FORMA DE CALCULO';
              end if;
              v_manual = v_manual || 'forma de calculo (1 = atividade principal - 2 = atividade com maior valor - 3 = soma do valor das atividades):' || v_forcal || '\n';

              select q85_perman
                from cadcalc
                into v_provisorio
               where cadcalc.q85_codigo = v_record_tipcalc.q81_cadcalc;

              if v_provisorio is true and v_record_ativ.q07_perman is false then

                v_qprovisorio = v_record_tipcalc.q81_percprovis;
                v_manual = v_manual || 'provisorio: vai acrescer ' || v_qprovisorio || ' por centro no valor calculado\n';

                perform fc_debug('Provisorio -- '||v_qprovisorio,lRaise,false,false);

              else

                v_qprovisorio = 1;

                perform fc_debug('Nao provisorio',lRaise,false,false);

              end if;

              perform fc_debug('inserindo na tabela tudo... sequencia:'||v_sequencia,lRaise,false,false);
              perform fc_debug('q81_val: '||v_q81_val||' - v_base: '|| v_base||' - uqtab: '||v_uqtab||' - uqcad: '||v_uqcad||' - qprovisorio: '||v_qprovisorio||' - valinflator: '||v_valinflator,lRaise,false,false);

              if v_record_tipcalc.q83_codven is null then
                return '25-VENCIMENTO NAO ENCONTRADO NO CADASTRO DE TIPO DE CALCULO';
              else
                v_codven = v_record_tipcalc.q83_codven;
              end if;
              perform fc_debug('q81_codigo: '||v_record_tipcalc.q81_codigo||' - q81_cadcalc: '||v_record_tipcalc.q81_cadcalc||' - vencimento: '||v_codven,lRaise,false,false);

              --
              -- Se empresa for optante pelo simples nao deve ser efetuado
              -- calculo de alvara
              --
              if lInscricaoMei and v_record_tipcalc.q81_cadcalc = 1 then
                perform fc_debug('Inscricao optante pelo MEI, nao calculando alvara.',lRaise,false,false);
                continue;
              end if;


              insert into
                tudo values (iAnousu,
                             iInscr,
                             v_record_ativ.q07_ativ,
                             v_record_tipcalc.q81_codigo,
                             v_record_tipcalc.q81_cadcalc,
                             v_base,
                             v_record_tipcalc.q81_recexe,
                             v_record_tipcalc.q81_recpro,
                             coalesce(v_quant,0),
                             v_record_tipcalc.q81_qiexe,
                             v_record_tipcalc.q81_qfexe,
                             v_uqtab,
                             v_uqcad,
                             v_forcal,
                             v_codven,
                             v_integr,
                             v_record_tipcalc.q81_tippro,
                             v_q81_val * v_base * v_uqtab * v_uqcad * v_qprovisorio * v_valinflator,
                             v_q81_val * v_qprovisorio * v_valinflator,
                             v_record_ativ.q07_datain,
                             coalesce( v_record_ativ.q07_datafi,(iAnousu||'-12-31')::date ),
                             v_var,
                             v_record_tipcalc.q81_gera,
                             v_sequencia);

                  v_sequencia = v_sequencia + 1;

            end if;

          end if;

        end if;

      end loop;
      perform fc_debug('Terminou atividade',lRaise,false,false);

    end loop;

    --
    -- SEGUNDA FASE DO CALCULO
    -- na fase anterior a rotina insere registros na tabela tudo e nela Ã© que se baseia daqui para frente para saber o que calcular
    --

    perform fc_debug('--------------------------------------------------------------------------------------------------------',lRaise,false,false);
    perform fc_debug('',lRaise,false,false);
    perform fc_debug('SEGUNDA FASE DO CALCULO',lRaise,false,false);
    perform fc_debug('',lRaise,false,false);
    perform fc_debug('Na fase anterior a rotina insere registros na tabela tudo e nela Ã© que se baseia daqui para frente para saber o que calcular',lRaise,false,false);


    if    iTipoCalculo = 1  then
      perform fc_debug('Excluindo registros de AlvarÃ¡', lRaise, false, false);
      DELETE FROM tudo where cadcalc not in (2,3);
    elsif iTipoCalculo = 2  then

      perform fc_debug('Excluindo registros de ISSQN', lRaise, false, false);
      DELETE FROM tudo where cadcalc not in (1,4,9);
    end if;


    select count(*) into iAux from tudo;

    v_manual = v_manual || '\nregistros processados na etapa de tipos de calculo: ' || iAux || '\n';
    v_manual = v_manual || '\n---- s e g u n d a  e t a p a  -----\n';
    v_manual = v_manual || 'agrupando por cadastro de calculo' || '\n';

    perform fc_debug('Quantidade de registros tabela tudo : '||iAux,lRaise,false,false);


    --
    -- for na tabela tudo com os tipos de calculo a utilizar
    -- veja o detalhe do group by, que faz com que apenas um cadcalc (ALVARA/ISSQN FIXO/ISSQN VARIAVEL) seja utilizado por calculo
    -- nessa fase o sistema cria registros na tabela porcalculo que serÃ¡ utilizada nessa fase
    --
    for v_record_cadcalc in select cadcalc, forcal, var from tudo group by cadcalc, forcal, var loop

      select q85_descr
        into v_descrcadcalc
        from cadcalc
       where q85_codigo = v_record_cadcalc.cadcalc;

      v_manual = v_manual || '   processando calculo ' || v_record_cadcalc.cadcalc || ' - ' || v_descrcadcalc || '\n';

      perform fc_debug('Var : '||v_record_cadcalc.var||' cadcalc : '||v_record_cadcalc.cadcalc,lRaise,false,false);

      -- se for variavel
      if v_record_cadcalc.var = '1' then

        v_manual = v_manual || '      variavel\n';

        -- pode ser fixado por inscricao
        for v_record_variavel in select * from tudo where tudo.cadcalc = v_record_cadcalc.cadcalc limit 1 loop

          perform fc_debug('Inserindo na tabela tudo fixado por inscricao',lRaise,false,false);

          execute 'insert into porcalculo values ('
          || iAnousu || ','
          || iInscr  || ','
          || v_base || ','
          || v_record_variavel.tipcalc || ','
          || v_record_variavel.cadcalc || ','
          || v_record_variavel.forcal  || ','
          || v_record_variavel.codven  || ','
          || v_record_variavel.integr  || ','
          || '''' || v_record_variavel.tipopro || '''' || ','
          || '''' || v_record_variavel.inicio  || '''' || ','
          || '''' || coalesce( v_record_variavel.final,(iAnousu||'-12-31')::date )   || '''' || ','
          || 0 || ','
          || v_record_variavel.valori || ','
          || 0 || ','
          || '''' || v_record_variavel.var     || '''' || ','
          || v_record_variavel.gera || ','
          || v_record_variavel.seq
          || ');';

        end loop;

      -- se NAO for variavel
      else

        perform fc_debug('Inserindo na tabela tudo pela atividade principal',lRaise,false,false);

        v_manual = v_manual || '      nao variavel\n';

        -- se for pela atividade principal
        if v_record_cadcalc.forcal = 1 then
          v_manual = v_manual || '         calculando pela atividade principal\n';

          select q07_ativ
            into v_ativprinc
            from ativprinc
                 inner join tabativ on q07_inscr = q88_inscr and q07_seq = q88_seq
           where q88_inscr = iInscr;
          if v_ativprinc is null then
            return '22-SEM ATIVIDADE PRINCIPAL CADASTRADA PARA ESTA INSCRICAO ';
          end if;

--        for v_record_ativprinc in select * from tudo where tudo.cadcalc = v_record_cadcalc.cadcalc and tudo.ativ = v_ativprinc loop
--        DESCOBRIR QUEM E PORQUE COMENTARAM A LINHA ACIMA
--        PORQUE PELA LOGICA DEVERIA UTILIZAR A LINHA COMENTADA

          for v_record_ativprinc in select * from tudo where tudo.cadcalc = v_record_cadcalc.cadcalc limit 1 loop

            v_manual = v_manual || '            inserindo na tabela de tipos de calculo a processar - tipcalc: ' || v_record_ativprinc.tipcalc || ' - cadcalc: ' || v_record_ativprinc.cadcalc || '\n';
            execute 'insert into porcalculo values ('
            || iAnousu || ','
            || iInscr  || ','
            || v_base || ','
            || v_record_ativprinc.tipcalc || ','
            || v_record_ativprinc.cadcalc || ','
            || v_record_ativprinc.forcal  || ','
            || v_record_ativprinc.codven  || ','
            || v_record_ativprinc.integr  || ','
            || '''' || v_record_ativprinc.tipopro || '''' || ','
            || '''' || v_record_ativprinc.inicio  || '''' || ','
            || '''' || coalesce( v_record_ativprinc.final, (iAnousu||'-12-31')::date )   || '''' || ','
            || v_record_ativprinc.valor   || ','
            || v_record_ativprinc.valori  || ','
            || 0 || ','
            || '''' || v_record_ativprinc.var     || '''' || ','
            || v_record_ativprinc.gera    || ','
            || v_record_ativprinc.seq
            || ');';

          end loop;

        end if;

        -- pela atividade que gerou o maior valor
        if v_record_cadcalc.forcal = 2 then
          v_manual = v_manual || 'calculando pela atividade que gerou o maior valor\n';

          for v_record_maiorvalor in select * from tudo where tudo.cadcalc = v_record_cadcalc.cadcalc order by valor desc limit 1 loop

            perform fc_debug('Inserindo na tabela porcalculo pela atividade de maior valor valor : '||v_record_maiorvalor.valor||' - tipo de calculo: '||v_record_maiorvalor.tipcalc||' - vencimento: '||v_record_maiorvalor.codven,lRaise,false,false);

            execute 'insert into porcalculo values ('
            || iAnousu || ','
            || iInscr  || ','
            || v_base || ','
            || v_record_maiorvalor.tipcalc || ','
            || v_record_maiorvalor.cadcalc || ','
            || v_record_maiorvalor.forcal  || ','
            || v_record_maiorvalor.codven  || ','
            || v_record_maiorvalor.integr  || ','
            || '''' || v_record_maiorvalor.tipopro || '''' || ','
            || '''' || v_record_maiorvalor.inicio  || '''' || ','
            || '''' || coalesce( v_record_maiorvalor.final, (iAnousu||'-12-31')::date )   || '''' || ','
            || v_record_maiorvalor.valor   || ','
            || v_record_maiorvalor.valori  || ','
            || 0 || ','
            || '''' || v_record_maiorvalor.var     || '''' || ','
            || v_record_maiorvalor.gera    || ','
            || v_record_maiorvalor.seq
            || ');';

          end loop;

        end if;

        -- pela soma de todos os valores calculados
        -- ainda nao implementado totalmente
        -- ou seja, nao funciona ainda...
        -- TESTADORES DEVEM UTILIZAR ESSA FORMULA DE CALCULO NOS TESTES
        if v_record_cadcalc.forcal = 3 then
          v_manual = v_manual || 'calculando pela soma de todos os valores\n';

          for v_record_somatodos in select * ,
                                           (select sum(valor) as somatotal
                                              from tudo
                                             where tudo.cadcalc = v_record_cadcalc.cadcalc) as somatotal
                                      from tudo
                                     where tudo.cadcalc = v_record_cadcalc.cadcalc loop

            select q85_codven
            from cadcalc
            into v_codvencadcalc
            where q85_codigo = v_record_cadcalc.cadcalc;
            if v_codvencadcalc is null then
              return '23-SEM VENCIMENTO PADRAO NO CADASTRO DE VENCIMENTOS ';
            end if;

            execute 'insert into porcalculo values ('
            || iAnousu || ','
            || iInscr  || ','
            || v_base || ','
            || v_record_somatodos.tipcalc || ','
            || v_record_cadcalc.cadcalc || ','
            || v_record_cadcalc.forcal  || ','
            || v_codvencadcalc            || ','
            || v_record_somatodos.integr  || ','
            || '''' || v_record_somatodos.tipopro || '''' || ','
            || '''' || v_record_somatodos.inicio  || '''' || ','
            || '''' || coalesce( v_record_somatodos.final, (iAnousu||'-12-31')::date)   || '''' || ','
            || v_record_somatodos.somatotal || ','
            || v_record_somatodos.valori  || ','
            || 0 || ','
            || '''' || v_record_somatodos.var     || '''' || ','
            || v_record_somatodos.gera    || ','
            || v_record_somatodos.seq
            || ');';

          end loop;

        end if;

      end if;

    end loop;
    -- fim do for do select na tabela tudo, que gera os registros na porcalculo

    v_manual = v_manual || '\n---- t e r c e i r a  e t a p a  -----\n';
    v_manual = v_manual || 'agrupando por tipo de vencimento e preparando para calcular\n';

    perform fc_debug('--------------------------------------------------------------------------------------------------------',lRaise,false,false);
    perform fc_debug('',lRaise,false,false);
    perform fc_debug('TERCEIRA ETAPA DO CALCULO ',lRaise,false,false);
    perform fc_debug('',lRaise,false,false);
    perform fc_debug('agrupando por tipo de vencimento e preparando para calcular',lRaise,false,false);

    for v_record_cadcalc in select porcalculo.*,
                                   tipcalc.q81_excedenteativ
                              from porcalculo
                                   inner join tipcalc on q81_codigo = porcalculo.tipcalc
    loop

      if v_record_cadcalc.q81_excedenteativ > 0 then
        v_valor = v_record_cadcalc.valor;

        perform fc_debug('Seq : '||v_record_cadcalc.seq||' Valor : '||v_valor,lRaise,false,false);

        v_valor = v_valor + (v_valor * v_record_cadcalc.q81_excedenteativ * (v_quantativ - 1));
        update porcalculo set valor = v_valor where seq = v_record_cadcalc.seq ;

        perform fc_debug('Apos calcular 30% por atividade excedente - Seq : '||v_record_cadcalc.seq||' Valor : '||v_valor,lRaise,false,false);

      end if;
    end loop;

    perform fc_debug('Descobrindo de calcula fixo ou variavel ',lRaise,false,false);

    --
    -- Verifica se calcula fixo ou variavel
    --
    for v_record_cadvenc in select distinct cadcalc from porcalculo
    loop

      if v_record_cadvenc.cadcalc = 2 then

        v_cadcalc_fix = true;

      elsif v_record_cadvenc.cadcalc = 3 then

        v_cadcalc_var = true;

      end if;

    end loop;

    if v_cadcalc_fix = true and v_cadcalc_var = true and iCalcfixvar = 1 then

      perform fc_debug('Inscricao com dois (2) calculos fixo/var, excluido calculo fixo',lRaise,false,false);
      delete from porcalculo where cadcalc = 2;
      v_manual = v_manual || '\n inscricao com dois (2) calculos fixo/variavel, calculado somente variavel \n';

    end if;

    if v_cadcalc_fix = true and v_cadcalc_var = true and iCalcfixvar = 2 then

      perform fc_debug('Inscricao com dois (2) calculos fixo/var, excluido calculo variavel',lRaise,false,false);

      delete from porcalculo where cadcalc = 3;
      v_manual = v_manual || '\n inscricao com dois (2) calculos fixo/variavel, calculado somente fixo \n';

    end if;

    perform fc_debug('select trazendo os vencimentos para gerar as proporcionalidades',lRaise,false,false);

    for v_record_cadvenc in select codven from porcalculo group by codven
    loop

      v_manual = v_manual || 'processando vencimento ' || v_record_cadvenc.codven || '\n';

      perform fc_debug('Codigo do cadastro de vencimentos : '||v_record_cadvenc.codven,lRaise,false,false);

      for v_record_cadcalc in select distinct tipcalc, cadcalc, valor, integr, inicio, final, tipopro, seq from porcalculo where codven = v_record_cadvenc.codven loop

        perform fc_debug('Tipo de calulo : '||v_record_cadcalc.tipcalc||' Cadcalc : '||v_record_cadcalc.cadcalc||'seq :'||v_record_cadcalc.seq,lRaise,false,false);

        select q81_abrev
        into v_descrtipcalc
          from tipcalc
         where q81_codigo = v_record_cadcalc.tipcalc;

        select q85_descr
          into v_descrcadcalc
          from cadcalc
         where q85_codigo = v_record_cadcalc.cadcalc;

        v_manual = v_manual || 'processando tipcalc: ' || v_record_cadcalc.tipcalc || ' - ' || v_descrtipcalc || ' - cadcalc: ' || v_record_cadcalc.cadcalc || ' - ' || v_descrcadcalc || '\n';

        v_valor = v_record_cadcalc.valor;
        v_manual = v_manual || 'valor: ' || v_valor || '\n';

        -- se Ã© para calcular com proporcionalidade
        if v_record_cadcalc.integr = '0' then

          perform fc_debug('Valor sem proporcionalidade : '||v_valor,lRaise,false,false);

          v_manual = v_manual || '   nao integral = proporcional ' || ' inicio: ' || v_record_cadcalc.inicio || ' - ano atual ' || iAnousu || '\n';

          -- soh calcula integralidade se ano do inicio da atividade for igual ao atual ou for calculo de baixa
          if extract(year from v_record_cadcalc.inicio)::integer = iAnousu or dDtbaixa is not null then
            --
            -- Calculo da proporcionalidade
            --
            if dDtbaixa is null then
              dDtProporcionalidade := v_record_cadcalc.final;
            else
              dDtProporcionalidade := dDtbaixa;
            end if;

            perform fc_debug('Tipo   - '||v_record_cadcalc.tipopro,lRaise,false,false);
            perform fc_debug('Inicio - '||v_record_cadcalc.inicio,lRaise,false,false);
            perform fc_debug('Final  - '||dDtProporcionalidade,lRaise,false,false);

            --
            -- Funcao fc_issqn_proporcionalidade
            --   retorna o valor proporcional ao periodo de atividade do exercio e a descricao do tipo de proporcionalidade
            --
            select rnValorProporcional,rsTipoProporcionalidade
              into v_valor,sDescrProporcionalidade
              from fc_issqn_proporcionalidade(v_record_cadcalc.valor::numeric,v_record_cadcalc.tipopro::varchar,v_record_cadcalc.inicio::date,dDtProporcionalidade::date,iAnousu::integer,dDtbaixa::date);

            v_manual = v_manual ||sDescrProporcionalidade||' \n';

          end if;

          perform fc_debug('Valor com a proporcionalide : '||v_valor,lRaise,false,false);

          v_manual = v_manual || 'valor ja calculado a proporcionalidade: ' || v_valor || '\n';

        else

          v_manual = v_manual || 'integral\n';

        end if;

        update porcalculo set valorintegr = v_valor  where seq = v_record_cadcalc.seq ;

      end loop;

    end loop;

    select count(*)
      into iAux
      from porcalculo;

    v_manual = v_manual || '\n---- q u a r t a  e t a p a  -----\n';
    v_manual = v_manual || 'total de calculos que o sistema vai processar: ' || iAux || '\n';

    if iAux = 0 then
      return '24-NENHUM CALCULO EFETUADO!';
    end if;

    --
    -- QUARTA FASE - GERANDO FINANCEIRO
    --
    perform fc_debug('--------------------------------------------------------------------------------------------------------',lRaise,false,false);
    perform fc_debug('',lRaise,false,false);
    perform fc_debug('QUARTA FASE DO CALCULO (GERANDO FINANCEIRO) ',lRaise,false,false);
    perform fc_debug('Quantidade de registros tabela porcalculo : '||iAux,lRaise,false,false);
    perform fc_debug('',lRaise,false,false);
    perform fc_debug('--------------------------------------------------------------------------------------------------------',lRaise,false,false);

    /* for nos calculo da inscricao que esta sendo calculada */

    perform fc_debug('Percorrendo os calculo da inscricao que esta sendo calculada',lRaise,false,false);

    dInicioAtividade := dDataCadastro;

    for v_record_cadcalc in select * from porcalculo loop

      select q81_abrev
        from tipcalc
        into v_descrtipcalc
      where q81_codigo = v_record_cadcalc.tipcalc;

      select q85_descr
      from cadcalc
      into v_descrcadcalc
      where q85_codigo = v_record_cadcalc.cadcalc;

      v_manual = v_manual || '\nprocessando calculo ' || v_record_cadcalc.cadcalc || ' - ' || v_descrcadcalc ||  ' - tipo de calculo: ' || v_record_cadcalc.tipcalc || ' - ' || v_descrtipcalc || '\n';

      -- data de baixa preenchida e Ã© variavel
      -- resumindo, Ã© para nao fazer nada se for variÃ¡vel e calculo para baixa

      if dDtbaixa is not null and v_record_cadcalc.var = '1' then

        perform fc_debug('Com data de baixa passada como parametro: ' || dDtbaixa || ' e variavel: nao calcula',lRaise,false,false);
        v_manual = v_manual || 'com data de baixa passada como parametro: ' || dDtbaixa || ' e variavel: nao calcula\n';
      -- senao
      else

        select q01_numpre
          from isscalc
          into v_numpre
         where q01_anousu = iAnousu
           and q01_inscr = iInscr
           and q01_cadcal = v_record_cadcalc.cadcalc;

        if v_numpre is null then

          perform fc_debug('Sem calculo para o exercicio ',lRaise,false,false);
          v_comcalculo = false;

        else

          perform fc_debug('Com calculo para o exercicio numpre : '||v_numpre,lRaise,false,false);
          v_comcalculo = true;

        end if;

        if v_comcalculo is false then
          v_manual = v_manual || 'calculo novo\n';

          select nextval('numpref_k03_numpre_seq')
            into v_numpre;

          perform fc_debug('Calculo Novo ',lRaise,false,false);
          perform fc_debug('Novo numpre : '||v_numpre,lRaise,false,false);

          if v_numpre is null then
            return '25-ERRO DO PROCESSAR SEQUENCIA DO NUMPRE';
          end if;

        else

          v_manual = v_manual || 'calculo ja existe... utilizar o mesmo numpre\n';
          select q01_numpre
            from isscalc
            into v_numpre
            where q01_anousu = iAnousu
              and q01_inscr = iInscr
              and q01_cadcal = v_record_cadcalc.cadcalc;

        end if;

        v_numpar = 1;

        select max(q82_parc)
          into v_numtot
          from cadvenc
               inner join cadvencdesc on q82_codigo = q92_codigo
         where cadvenc.q82_codigo = v_record_cadcalc.codven;

        if v_record_cadcalc.var = '1' then
          v_numtot = 12;
        else

          if v_numtot is null then
            v_numtot = 0;
          end if;

          if bGeral is false then
            v_numtot = 1;
          end if;
        end if;

        v_manual = v_manual || 'total de parcelas baseado no vencimento: ' || v_numtot || '\n';

        perform fc_debug('Codigo do Vencimento encontrado : '||v_record_cadcalc.codven,lRaise,false,false);
        perform fc_debug('Numpre : '||v_numpre||' Parcela : '||v_numpar||' Numtot : '||v_numtot||' Cadcalc : '||v_record_cadcalc.cadcalc,lRaise,false,false);

        select fc_digito(v_numpre,v_numpar,v_numtot)
          into v_numdig;

        if v_numdig is null then
          return '26-ERRO DO PROCESSAR FUNCAO DE CALCULO DO DIGITO VERIFICADOR';
        end if;

        select k15_codbco,
               k15_codage
          into v_codbco,
               v_codage
          from cadvencdescban
               inner join cadban on cadvencdescban.q93_cadban = cadban.k15_codigo
        where q93_codigo = v_record_cadcalc.codven;

        if v_codbco is null then
          v_codbco = 0;
        end if;

        if v_codage is null then
          v_codage = 0;
        end if;

        select q02_numcgm
          into v_numcgm
          from issbase
         where q02_inscr = iInscr;
        if v_numcgm is null then
          return '27-CGM DA INSCRICAO : '||iInscr||' NAO ENCONTRADO DO CADASTRO DO CGM';
        end if;

        perform fc_debug('Inicio : '||v_record_cadcalc.inicio||' Anousu : '||iAnousu,lRaise,false,false);

        if iAnousu = to_number(substr(v_record_cadcalc.inicio,1,4),'99999') then

          v_manual = v_manual || 'Ano atual igual ao ano de inicio da atividade: ' || iAnousu || '\n';

          perform fc_debug('Ano atual igual ao ano de inicio',lRaise,false,false);

          select recexe
            into v_receita
            from tudo
           where tudo.seq = v_record_cadcalc.seq;
        else

          v_manual = v_manual||'Ano atual diferente do ano de inicio da atividade: ' || iAnousu || '\n';
          perform fc_debug('Ano atual diferente ao ano de inicio',lRaise,false,false);
          select recpro
            into v_receita
            from tudo
           where tudo.seq = v_record_cadcalc.seq;

        end if;

        v_prim = false;

        /**
         * Se for variavel
         */
        if v_record_cadcalc.var = '1' then
          v_manual = v_manual || 'variavel\n';

          perform fc_debug('Calculando variavel -- vencimento : '||v_record_cadcalc.codven,lRaise,false,false);

         -- se for baixa
          perform fc_debug('',lRaise,false,false);
          perform fc_debug('COMECANDO A PROCESSAR OS VENCIMENTOS (PELO CADASTRO DE VENCIMENTOS CADVENC) ',lRaise,false,false);
          perform fc_debug('',lRaise,false,false);

          /* este for calcula pelo cadvenc todas as parcelas pelo select do porcalculo(todos os calculos da inscricao) */
          for v_record_cadvenc in select * from cadvenc
                                           inner join cadvencdesc on q82_codigo = q92_codigo
                                     where cadvenc.q82_codigo = v_record_cadcalc.codven
                                     order by q82_parc
          loop

            perform fc_debug('Processando vencimentos ',lRaise,false,false);
            perform fc_debug('PARCELA : '||v_record_cadvenc.q82_parc||' VENCIMENTO : '||v_record_cadvenc.q82_venc,lRaise,false,false);
            perform fc_debug('1 -- Deletando do arrecad numpre : '||v_numpre||' numpar : '||v_record_cadvenc.q82_parc,lRaise,false,false);

            delete from arrecad
             where k00_numpre = v_numpre
               and k00_numpar = v_record_cadvenc.q82_parc;

            if to_number(substr(v_record_cadvenc.q82_venc,1,4)||substr(v_record_cadvenc.q82_venc,6,2),'999999') >
               to_number(substr(v_record_cadcalc.inicio,1,4)||substr(v_record_cadcalc.inicio,6,2),'999999') then

              v_manual = v_manual || 'vencimento do cadastro de vencimentos: ' || v_record_cadvenc.q82_venc || ' maior ou igual a data de inicio da atividade mais 1 mes\n';

              select count(*)
                into iAux
                from arreinscr
               where k00_numpre = v_numpre
                 and k00_inscr = iInscr;

              if iAux = 0 then

                v_manual = v_manual || 'inserindo numpre no arreinscr: ' || v_numpre || '\n';
                insert into arreinscr values (v_numpre,iInscr);
              end if;

              /* se nao for primeiro calculo do exercicio */
              if v_prim is false then

                if v_comcalculo is true then

                  perform fc_debug('Deletando do isscalc numpre : '||v_numpre,lRaise,false,false);
                  v_manual = v_manual || 'deletando numpre do isscalc e arrecad: ' || v_numpre || '\n';
                  delete from isscalc where q01_numpre = v_numpre;

                end if;

                insert into isscalc values (iAnousu,iInscr,v_record_cadcalc.cadcalc,v_receita,v_numpre,v_record_cadcalc.valori / v_valinflator);
                insert into numpres values (v_numpre);
                v_prim = true;

              end if;

              v_valor         := v_record_cadcalc.valori / v_valinflator;
              v_valorgrav     := 0;
              v_descrvariavel := 'arrecadacao de issqn variavel nao fixado';
              v_manual        := v_manual || 'valor: ' || v_valor || '\n';

              /* verifica se tem valor fixado lancado */
              select q34_valor
                into v_valorgrav
                from varfix
                     inner join varfixval on varfix.q33_codigo = varfixval.q34_codigo
               where varfix.q33_inscr    = iInscr
                 and varfixval.q34_numpar = v_record_cadvenc.q82_parc;

              if v_valorgrav is not null then

                v_descrvariavel = 'arrecadacao de issqn variavel fixado';
                bTemFixado = true;
              else

                v_valorgrav     = 0;
                v_descrvariavel = 'arrecadacao de issqn variavel nao fixado';
              end if;

              perform fc_debug('Tipo de valor : '||v_descrvariavel ,lRaise,false,false);

              v_numpar = v_record_cadvenc.q82_parc;
              v_manual = v_manual || 'parcela: ' || v_numpar || '\n';

              select q05_codigo
                into iAux
                from issvar
                     inner join arreinscr on q05_numpre = arreinscr.k00_numpre
               where q05_ano   = iAnousu
                 and q05_mes   = v_numpar
                 and k00_inscr = iInscr
                 and q05_valor > 0;

              v_vencvar = v_record_cadvenc.q82_venc;

              perform fc_debug('Vencimento variavel : '||v_vencvar,lRaise,false,false);

              select q05_numpre
                from issvar
                into v_numprejapago
                     inner join arrecant on q05_numpre = arrecant.k00_numpre and q05_numpar = arrecant.k00_numpar
                     inner join arreinscr on arrecant.k00_numpre = arreinscr.k00_numpre
              where q05_ano   = iAnousu
                and q05_mes   = v_numpar
                and k00_inscr = iInscr;

              perform fc_debug('Numpre ja pago '||v_numprejapago||' Numpar : '||v_numpar||' Anousu : '||iAnousu,lRaise,false,false);

              select q05_numpre
                into iNumpreArquivoSimples
                from issvar
                     inner join issarqsimplesregissvar on q68_issvar = q05_codigo
                     inner join arreinscr on q05_numpre = arreinscr.k00_numpre
              where q05_ano   = iAnousu
                and q05_mes   = v_numpar
                and k00_inscr = iInscr;

              perform fc_debug('Numpre no arquivo do simples '||iNumpreArquivoSimples||' Numpar : '||v_numpar||' Anousu : '||iAnousu,lRaise,false,false);

              if v_numprejapago is null and iNumpreArquivoSimples is null  then

                perform fc_debug('2 -- deletando do arrecad numpre : '||v_numpre||' numpar : '||v_numpar,lRaise,false,false);

                delete from arrecad where k00_numpre = v_numpre and k00_numpar = v_numpar ;

                perform * from fc_statusdebitos(v_numpre,v_numpar) where rtstatus = 'PAGO' or rtstatus = 'CANCELADO'  limit 1;
                if found then
                  continue;
                end if;

                perform * from issvardiv
                          inner join issvar    on q05_codigo = q19_issvar
                          inner join arreinscr on k00_numpre = q05_numpre
                    where issvar.q05_ano       = iAnousu
                      and issvar.q05_mes       = v_numpar
                      and arreinscr.k00_inscr  = iInscr ;

                if found then
                  return '28 - INSCRICAO COM CALCULO IMPORTADO PARA DIVIDA';
                end if;

                perform fc_debug('deletando do issvar Ano : '||iAnousu||' Mes : '||v_numpar||' Inscricao : '||iInscr,lRaise,false,false);
                perform *
                   from arreinscr
                        inner join issvar    on issvar.q05_numpre    = arreinscr.k00_numpre
                        inner join issvarlev on issvarlev.q18_codigo = issvar.q05_codigo
                  where issvar.q05_ano       = iAnousu
                    and issvar.q05_mes       = v_numpar
                    and arreinscr.k00_inscr  = iInscr ;
                if found then
                  return '28 - EMPRESA JA POSSUI LEVANTAMENTO FISCAL PARA COMPETENCIA : '||v_numpar||'/'||iAnousu ;
                end if;

               delete from issvarnotas
                using issvar
                where issvar.q05_codigo = issvarnotas.q06_codigo
                  and issvar.q05_ano    = iAnousu
                  and issvar.q05_mes    = v_numpar ;

                delete from issvar
                 using arreinscr
                 where issvar.q05_ano       = iAnousu
                   and issvar.q05_mes       = v_numpar
                   and arreinscr.k00_inscr  = iInscr
                   and arreinscr.k00_numpre = issvar.q05_numpre ;

                delete from informacaodebito
                 where informacaodebito.k163_numpre = v_numpre
                   and informacaodebito.k163_numpar = v_numpar;

                insert into issvar (q05_codigo, q05_numpre, q05_numpar, q05_valor, q05_ano, q05_mes, q05_histor, q05_aliq, q05_bruto)
                            values (nextval('issvar_q05_codigo_seq'), v_numpre, v_numpar, v_valorgrav, iAnousu, v_numpar, v_descrvariavel, v_valor, 0);

                perform fc_debug('INSERT NUMERO 1 NO ARRECAD (issvar)',lRaise,false,false);
                perform fc_debug('Numpre : '||v_numpre||' Numpar : '||v_numpar||' Valor : '||round(v_valorgrav,2)||' Vencimento : '||v_vencvar||' Tipo : '||v_record_cadvenc.q92_tipo,lRaise,false,false);

                insert into arrecad (k00_numcgm, k00_dtoper, k00_receit, k00_hist, k00_valor, k00_dtvenc, k00_numpre, k00_numpar, k00_numtot, k00_numdig, k00_tipo)
                     values (v_numcgm,v_vencvar,v_receita,v_record_cadvenc.q82_hist,round(v_valorgrav,2),v_vencvar,v_numpre,v_numpar,v_numtot,v_numdig,v_record_cadvenc.q92_tipo);

                if v_codbco != 0 and v_comcalculo is false then

                  select fc_numbco(v_codbco,v_codage) into v_numbco;
                  /*
                      Comentado insert na arrebanco conforme solicitado pelo Paulo e Dalpozzo, pois nao faz
                      o menor sentido gerar arrebanco no calculo de issqn / alvara
                   */
                  --insert into arrebanco values (v_numpre,v_numpar,v_codbco,v_codage,v_numbco,'');

                end if;

              else
                perform fc_debug('Ja pago',lRaise,false,false);
              end if;

              v_numpar = v_numpar + 1;

            end if;

          end loop;

          perform fc_debug('FIM DO PROCESSAMENTO DO VARIAVEL',lRaise,false,false);

        else  -- SE NAO FOR VARIAVEL

          perform fc_debug('',lRaise,false,false);
          perform fc_debug('--------------------- P R O C E S S A N D O   (  N A O  )  V A R I A V E L  ---------------------',lRaise,false,false);
          perform fc_debug('vencimento: '||v_record_cadcalc.codven,lRaise,false,false);
          perform fc_debug('',lRaise,false,false);

          v_manual = v_manual || 'processando com base no cadastro de vencimentos\n';

          lJaPassouUltVenc   = false;
          v_jagravou         = false;
          iQtdeVencProcessar = 0;

          -- busca quantas parcelas vai calcular e registrar no arrecad (variavel iQtdeVencProcessar)
          select count(*)
            into v_totvenc
            from cadvencdesc
                 inner join cadvenc on q82_codigo = q92_codigo
           where cadvencdesc.q92_codigo = v_record_cadcalc.codven ;

          select count(*)
            into iQtdeVencProcessar
            from cadvencdesc
                 inner join cadvenc on q82_codigo = q92_codigo
           where cadvencdesc.q92_codigo = v_record_cadcalc.codven
             and ( case
                     when cadvencdesc.q92_formacalcparcvenc = 1
                       then case when q82_venc >= dInicioAtividade then true else false end
                     when cadvencdesc.q92_formacalcparcvenc = 3
                       then q82_venc >= dDatahj and q82_venc >= dInicioAtividade
                     else
                       case
                         when cadvenc.q82_calculaparcvenc is true
                           then true
                         else
                           q82_venc >= dDatahj and q82_venc >= dInicioAtividade
                       end
                   end );
             -- esse case tem a finalidade de processar ou nao parcelas
             -- vencidas de acordo com os parametros do cadastro de
             -- vencimentos(cadvencdescm,cadvenc)
          perform fc_debug('Pesquisando quantidade de vencimentos no periodo de atividade',lRaise,false,false);
          perform fc_debug('Data inicial : '||coalesce(dInicioAtividade,'2999-01-01'::date)    ,lRaise,false,false);
          perform fc_debug('Data final   : '||iUltimoDiaAno ,lRaise,false,false);
          perform fc_debug('Quantidade de vencimentos encontrada : '||iQtdeVencProcessar||''    ,lRaise,false,false);

          -- utiliza a variavel iTotalVencimentosCad, que e o total de vencimentos para mais abaixo saber em que parcela jogar os centavos de diferenca de arredondamento
          -- utiliza a variavel dMaiorVencimentoCadvenc para no caso da empresa iniciar depois do ultimo vencimento, calcular tudo em uma parcela e jogar no vencimento 31-12
          select max(cadvenc.q82_venc), coalesce(max(cadvencdesc.q92_diasvcto),0), count(*)
            into dMaiorVencimentoCadvenc, iDiasParaVencimento, iTotalVencimentosCad
            from cadvencdesc
                 inner join cadvenc on q82_codigo = q92_codigo
           where cadvencdesc.q92_codigo = v_record_cadcalc.codven;

          -- passando conteudo da variavel do cissqn para cadvencdesc
          iDiasvctoCissqn := iDiasParaVencimento;

          perform fc_debug('Quantidade de vencimentos a processar : '||iQtdeVencProcessar,  lRaise,false,false);
          perform fc_debug('Dias para o vencimento(q92_diasvcto)  : '||iDiasParaVencimento, lRaise,false,false);
          perform fc_debug('Maior vencimento do cadastro          : '||dMaiorVencimentoCadvenc||' Data atual : '||dDatahj,lRaise,false,false);

          select count(*), sum(k00_valor)
            into iQtdParcelasPagas, nValorTotalPago
            from ( select k00_numpre, k00_numpar, sum(k00_valor) as k00_valor
                     from arrecant
                    where k00_numpre = v_numpre
                 group by k00_numpre, k00_numpar ) as x;

          perform fc_debug('Quantidade de parcelas pagas : '||coalesce(iQtdParcelasPagas,0)||' Valor total pago : '||coalesce(nValorTotalPago,0),lRaise,false,false);

          begin

            select case when count(*) = 1 then
                     1
                   else
                     ( count(*) - iQtdParcelasPagas )
                   end as qtdparcelas,

                   case when count(*) = 1 then
                     100
                   else
                     ( 100 / ( count(*) - iQtdParcelasPagas ) )
                   end as percpago

              into iQtdParcelas, nPercPagoNovo
              from cadvencdesc
                   left outer join cadvenc on q82_codigo = q92_codigo
             where cadvencdesc.q92_codigo = v_record_cadcalc.codven
               and ( case
                     when cadvencdesc.q92_formacalcparcvenc = 1
                       then case when q82_venc >= dInicioAtividade then true else false end
                     when cadvencdesc.q92_formacalcparcvenc = 3

                       then q82_venc >= dDatahj and q82_venc >= dInicioAtividade
                     else
                       case
                         when cadvenc.q82_calculaparcvenc is true
                           then true
                         else
                           q82_venc >= dDatahj and q82_venc >= dInicioAtividade
                       end
                   end ) ;

           exception

             when division_by_zero then
               nPercPagoNovo := 0;

           end;

          if iQtdParcelasPagas = 0 then
            nPercPagoNovo := 0;
          end if;

          /*
           * Quando a quantidade total de parcelas for negativo, significa que  o calculo nÃ£o possui mais
           * parcelas em aberto, todas estÃ£o abertas. logo, o total de parcelas devera ser 1
           */
          if iQtdParcelas < 0 then
            iQtdParcelas = 1;
          end if;

          if nPercPagoNovo < 0 then
            nPercPagoNovo = 100;
          end if;

          nDescontoPagamentoParcela := coalesce( ( nValorTotalPago / iQtdParcelas ) ,0);

          perform fc_debug(' PROCURANDO PAGAMENTOS : ',lRaise,false,false);
          perform fc_debug('--------------------------------------------------------------------------------------------',lRaise,false,false);
          perform fc_debug(' TOTAL PAGO           : '||coalesce( nValorTotalPago, 0)           ,lRaise,false,false);
          perform fc_debug(' PARCELAS PAGAS       : '||coalesce( iQtdParcelasPagas, 0)         ,lRaise,false,false);
          perform fc_debug(' PARCELAS A CALCULAR  : '||coalesce( iQtdParcelas, 0)              ,lRaise,false,false);
          perform fc_debug(' DESCONTO POR PARCELA : '||coalesce( nDescontoPagamentoParcela, 0) ,lRaise,false,false);
          perform fc_debug(' PERCENTUAL NOVO      : '||coalesce( nPercPagoNovo, 0)             ,lRaise,false,false);
          perform fc_debug('--------------------------------------------------------------------------------------------',lRaise,false,false);

          -- sempre deve deletar o calculo anterior no caso de encontrar um
          perform fc_debug('DELETANDO DO ARRECAD NUMPRE : '||v_numpre,lRaise,false,false);
          delete from arrecad where k00_numpre = v_numpre;

          -- Loop que gera/processa financeiro
          for v_record_cadvenc in select *
                                    from cadvencdesc
                                         left join cadvenc on q82_codigo = q92_codigo
                                   where cadvencdesc.q92_codigo = v_record_cadcalc.codven
                                order by q82_parc
          loop

            perform fc_debug('------------------------------------------------------------------------------------------',lRaise,false,false);
            perform fc_debug('Processando Vencimento : '||v_record_cadvenc.q82_venc||' Parcela : '||v_record_cadvenc.q82_parc||' Inicio : '||v_record_cadcalc.inicio,lRaise,false,false);
            perform fc_debug('------------------------------------------------------------------------------------------',lRaise,false,false);

            /**
             * Guardar o vencimento atual do cadvenc
             */
            dVencimentoAtual := v_record_cadvenc.q82_venc;

            if dVencimentoAtual is null then
              dVencimentoAtual := dDatahj;
            end if;

            /**
             * Se a quantidade de vencimentos a processar for igual a zero
             */
            if iQtdeVencProcessar = 0 then

              -- soma dias para o vencimento na data do ultimo vencimento
              if iDiasvctoCissqn > 0 then

                dVencimentoAtual := ( dDatahj + iDiasvctoCissqn )::date;
                perform fc_debug('Trocou o vencimento para : '||dVencimentoAtual,lRaise,false,false);
              else

                -- se mesmo somando os dias para vencimento o debito continuar vencido joga para 31/12
                dVencimentoAtual := cast(to_char(iAnousu,'9999')||'-12-31' as date);
                perform fc_debug('Trocou o vencimento para ultimo dia do ano : '||dVencimentoAtual,lRaise,false,false);
              end if;

            end if;

            perform fc_debug('Vencimento apos processamento das regras : '||dVencimentoAtual,lRaise,false,false);

            /**
             * Variavel para controlar a forma para calculo de parcelas vencidas
             */
            lProcessaParcVencidas := ( case
                                         when v_record_cadvenc.q92_formacalcparcvenc = 1
                                           then true
                                         when v_record_cadvenc.q92_formacalcparcvenc = 3
                                           then v_record_cadvenc.q82_venc between dDatahj and iUltimoDiaAno
                                         else
                                           case
                                             when v_record_cadvenc.q82_calculaparcvenc is true
                                               then true
                                             else
                                               v_record_cadvenc.q82_venc between dDatahj and iUltimoDiaAno
                                           end
                                       end );

            perform fc_debug('Processando parcelas vencidas(lProcessaParcVencidas) ? '||(case when lProcessaParcVencidas is true then 'SIM' else 'NAO' end),lRaise,false,false);

            v_vencproc       = v_vencproc + 1;
            lProcessaParcela = false;

            perform fc_debug('Vencimento do cadvenc : '||dVencimentoAtual||' Maior Vencimento : '||dMaiorVencimentoCadvenc,lRaise,false,false);

            -- se vencimento do cadvenc do registro atual do for maior que o maximo vencimento do cadvenc
            if v_record_cadvenc.q82_venc > dMaiorVencimentoCadvenc then

              perform fc_debug('',lRaise,false,false);
              perform fc_debug('Vencimento do cadastro de vencimentos maior que o maximo vencimento, pasando lJaPassouUltVenc para true',lRaise,false,false);
              perform fc_debug('',lRaise,false,false);
              lJaPassouUltVenc = true;
            end if;

            perform fc_debug('Proximo vencimento : '||v_vencproc||'  Total de vencimentos : '||iTotalVencimentosCad||' Passa: '||( case when lProcessaParcela is true then 'true' else 'false' end ),lRaise,false,false);

            perform fc_debug('Data da baixa em branco',lRaise,false,false);
            lProcessaParcela = true;

            if extract( year from v_record_cadcalc.inicio) <> iAnousu then

              perform fc_debug('Ano de inicio diferente do atual ',lRaise,false,false);
              lProcessaParcela = true;
              v_numtot         = v_totvenc;

            else

              perform fc_debug('Ano de inicio igual do atual ',lRaise,false,false);
              if v_record_cadvenc.q82_venc >= dInicioAtividade or dVencimentoAtual is null or iQtdeVencProcessar = 0 or lProcessaParcVencidas is true then

                perform fc_debug('Vencimento: '||dVencimentoAtual||' maior ou igual a inicio: '||dInicioAtividade||' ou vencimento is null ',lRaise,false,false);
                lProcessaParcela = true;

              else

                perform fc_debug('Passando lProcessaParcela para FALSE -- Vencimento: '||dVencimentoAtual||' menor que inicio: '||dInicioAtividade,lRaise,false,false);
                lProcessaParcela = false;

              end if;
              v_numtot = iQtdeVencProcessar;

            end if;

            if dDtbaixa is not null and dVencimentoAtual > dDtbaixa then

              perform fc_debug('Data de baixa : '||dDtbaixa,lRaise,false,false);
              perform fc_debug('1 - Passando lProcessaParcela para false',lRaise,false,false);
            end if;

            if dDatahj > dVencimentoAtual and lProcessaParcVencidas is false then

              perform fc_debug('Inicio maior que data de vencimento e processar parcelas vencidas NAO',lRaise,false,false);
              lProcessaParcela = false;
            end if;

            perform fc_debug('----------------------------------------------------------------------------------------------',lRaise,false,false);
            perform fc_debug('Total de vencimentos     : '||iTotalVencimentosCad ,lRaise,false,false);
            perform fc_debug('Vecimentos do cadastro   : '||v_vencproc,lRaise,false,false);
            perform fc_debug('Qtd parcelas a calcular  : '||v_totvenc,lRaise,false,false);
            perform fc_debug('Ja passou ult vencimento : '||(case when lJaPassouUltVenc is true then 'SIM' else 'NAO' end),lRaise,false,false);
            perform fc_debug('Inicio                   : '||v_record_cadcalc.inicio,lRaise,false,false);
            perform fc_debug('Vencimento do Cadastro   : '||dVencimentoAtual,lRaise,false,false);
            perform fc_debug('----------------------------------------------------------------------------------------------',lRaise,false,false);

            if    v_record_cadcalc.inicio > dVencimentoAtual
              and iTotalVencimentosCad <> v_vencproc
              and lJaPassouUltVenc is false                 then

              perform fc_debug('Passando lProcessaParcela para false',lRaise,false,false);
              lProcessaParcela = false;
            end if;

            perform fc_debug('Passando para o proximo vencimento(lProcessaParcela) ? '||(case when lProcessaParcela is true then 'True' else 'False' end),lRaise,false,false);

            if lProcessaParcela is true then

              -- DESCOBRIR EM QUE CASO E UTILIZADO
              --
              -- Desabilitado o if abaixo pois Ã© justamente ele que impede o calculo para anos posteriores,
              v_venc = dVencimentoAtual;

              perform fc_debug('Quantidade de vencimentos(iQtdeVencProcessar) a processar : '||iQtdeVencProcessar,lRaise,false,false);
              if iQtdeVencProcessar = 0 then
                nPercentualParcela = 100;
              else

                -- Verificacao do valor total proporcional
                if v_record_cadcalc.tipopro = 'D' then

                  v_venc     = v_record_cadvenc.q82_venc;
                  v_venccalc = v_venc;
                  if lJaPassouUltVenc is true or v_record_cadvenc.q82_venc = dMaiorVencimentoCadvenc then

                    v_venccalc = to_char(iAnousu,'9999') || '-12-31';
                    dMaiorVencimentoCadvenc  = v_venccalc;

                  end if;
                  v_anoiniciocalc := extract(year from v_record_cadcalc.inicio);
                  if v_anoiniciocalc < iAnousu then
                    dInicioAtividadecalc = to_char(iAnousu,'9999') || '-01-01';
                  else
                    dInicioAtividadecalc = v_record_cadcalc.inicio;
                  end if;

                  perform fc_debug('Total de vencimentos : '||dMaiorVencimentoCadvenc||' Inicio: '||dInicioAtividadecalc,lRaise,false,false);
                  v_diasdesdeinicio = (iUltimoDiaAno - dInicioAtividadecalc)::integer + 1;
                  perform fc_debug('Vencimento calculo : '||v_venccalc||' Inicio: '||dInicioAtividadecalc,lRaise,false,false);
                  v_diasdestevcto = ((v_venccalc - dInicioAtividadecalc)::integer + 1) - v_diasjasomados;

                  if bGeral is true or dDtbaixa is not null then
                    nPercentualParcela = 100::float8 / iQtdeVencProcessar::float8;
                  else
                    nPercentualParcela = (100::float8 / v_diasdesdeinicio::float8)::float8 * v_diasdestevcto::float8;
                    v_diasjasomados = v_diasjasomados + v_diasdestevcto;
                  end if;

                else

                  nPercentualParcela = 100::float8 / iQtdParcelas::float8;

                end if;
              end if;

              perform fc_debug('Vencimento : '||v_venc||' Dias de vencimento : '||iDiasvctoCissqn,lRaise,false,false);

              if v_venc is null and iAnousu < v_anoatualservidor then

                v_venc = to_char(iAnousu,'9999')||'-12-31';

                --- verificado o vcto do alvara quando calculado, data calc. + 30 dias
                if iDiasvctoCissqn > 0 and iAnousu = v_anoatualservidor then

                  select dDatahj + iDiasvctoCissqn
                    into v_venc;

                  v_manual = v_manual || 'alterado vencimento para: '||v_venc||'\n';

                end if;
              end if;

              perform fc_debug('Percentual : '||nPercentualParcela,lRaise,false,false);
              perform fc_debug('Vencimento : '||v_venc,lRaise,false,false);

              if v_prim is false then
                if v_comcalculo is false then
                  insert into arreinscr values (v_numpre,iInscr);
                else
                  perform fc_debug('deletando do isscalc o numpre : '||v_numpre,lRaise,false,false);
                  v_manual = v_manual || 'deletando numpre do isscalc e arrecad: ' || v_numpre || '\n';
                  delete from isscalc where q01_numpre = v_numpre;
                end if;

                insert into isscalc values (iAnousu, iInscr, v_record_cadcalc.cadcalc, v_receita, v_numpre, v_record_cadcalc.valor);
                insert into numpres values ( v_numpre );
                v_prim = true;

              end if;

              /**
               * Valida quando executa calculo geral
               */
              if bGeral is true then

                /**
                 * Subtraido valor que ja foi pago, caso contrario subtraira 0
                 */
                nValorParcela = round( ( (v_record_cadcalc.valorintegr) * nPercentualParcela / 100) - coalesce(nDescontoPagamentoParcela, 0), 2);

                perform fc_debug('(1) Valor Parcela : '||nValorParcela,lRaise,false,false);
                perform fc_debug('Valor Total       : '||v_record_cadcalc.valorintegr||' Valor Parcial(parcela) : '||nValorParcela,lRaise,false,false);

                /**
                 * Ultima Parcela
                 */
                if v_numpar = v_numtot then

                  /**
                   * Arredonda os centavos e joga na ultima
                   */
                  perform fc_debug('',lRaise,false,false);
                  perform fc_debug('(4) Valor Parcela : '||nValorParcela,lRaise,false,false);
                  nValorParcela = nValorParcela + ((v_record_cadcalc.valorintegr) - (nValorParcela * v_numtot));
                  perform fc_debug('(5) Valor Parcela : '||nValorParcela,lRaise,false,false);
                end if;

                perform fc_debug('100 -- Delete from arrecad numpre : '||v_numpre||' numpar : '||v_record_cadvenc.q82_parc,lRaise,false,false);

                -- se a parcela esta paga ou cancelada passa para a proxima
                perform * from fc_statusdebitos(v_numpre, v_record_cadvenc.q82_parc)
                  where rtstatus = 'PAGO'
                     or rtstatus = 'CANCELADO' limit 1;
                if found then

                  perform fc_debug('1 -- PARCELA '||v_record_cadvenc.q82_parc||' ESTA PAGA OU CANCELADA ',lRaise,false,false);
                  continue;
                end if;

                nValorParcela := round(nValorParcela,2);
                perform fc_debug('INSERT NUMERO 2 NO ARRECAD',lRaise,false,false);
                perform fc_debug('Numpre : '||v_numpre||' Numpar : '||v_numpar||' Valor : '||round(nValorParcela,2)||' Vencimento : '||v_venc||' Tipo : '||v_record_cadvenc.q92_tipo,lRaise,false,false);

                if nValorParcela > 0 then

                  /**
                   * Alterado para inserir o q82_parc ao inves do numpar pois a pl nao levava em consideracao parcelas
                   * pagas e ou canceladas e assim gerando inconsistencia com numpar pago/cancelado e em aberto ao mesmo tempo
                   */
                  insert into arrecad (k00_numcgm, k00_dtoper, k00_receit, k00_hist, k00_valor, k00_dtvenc, k00_numpre, k00_numpar, k00_numtot, k00_numdig, k00_tipo)
                       values (v_numcgm, v_dtbase, v_receita, v_record_cadvenc.q92_hist, round(nValorParcela,2),
                               v_venc, v_numpre, v_record_cadvenc.q82_parc, v_numtot, v_numdig, v_record_cadvenc.q92_tipo);

                end if;

              else

                -- se a parcela esta paga ou cancelada passa para a proxima
                perform * from fc_statusdebitos(v_numpre, v_record_cadvenc.q82_parc)
                  where rtstatus = 'PAGO'
                     or rtstatus = 'CANCELADO' limit 1;

                if found then

                  perform fc_debug('2 -- PARCELA '||v_record_cadvenc.q82_parc||' ESTA PAGA OU CANCELADA ',lRaise,false,false);
                  v_numpar = v_numpar + 1;
                  continue;

                  /**
                   * Replicada mesma logica descrita acima quando o calculo eh geral
                   */
                else
                  v_numpar = v_record_cadvenc.q82_parc;
                end if;

                if v_numpar is null then
                  v_numpar := 1;
                end if;

                v_dtvencano   = v_venc;
                nValorParcela = round( ( ( v_record_cadcalc.valorintegr * nPercentualParcela ) / 100 ) - nDescontoPagamentoParcela,2 );

                v_manual = v_manual || 'valor da parcela: ' || nValorParcela || '\n';

                perform fc_debug('Valor integral        : '||v_record_cadcalc.valorintegr||' Percentual : '||nPercentualParcela,lRaise,false,false);
                perform fc_debug('Valor da parcela      : '||nValorParcela,lRaise,false,false);
                perform fc_debug('Percentual da parcela : '||nPercentualParcela,lRaise,false,false);
                perform fc_debug('Desconto da parcela   : '||nDescontoPagamentoParcela,lRaise,false,false);

                perform fc_debug('300 -- Delete from arrecad numpre : '||v_numpre||' numpar : '||v_numpar,lRaise,false,false);

                if nValorParcela > 0 then

                  perform fc_debug('INSERT NUMERO 3 NO ARRECAD',lRaise,false,false);
                  perform fc_debug('Numpre : '||v_numpre||' Numpar : '||v_numpar||' Valor : '||round(nValorParcela,2)||' - Vencimento : '||v_dtvencano||' Tipo : '||v_record_cadvenc.q92_tipo,lRaise,false,false);

                  /**
                   * Caso Seja Calculo de AlvarÃ¡, valida quantaas parcelas foram informadas para o cÃ¡lculo de Alvara
                   *
                   * Seguindo pelo principio:
                   * -- Dividir o Valor Pela Quantidade
                   * -- Criar parcelas do alvara sempre com base de vencimento
                   * -- Gravar nova estrutura de dÃ©bitos no arrecad
                   **/
                   if v_record_cadcalc.cadcalc = 1  and iNumeroParcelasAlvara > 1 then

                    perform fc_debug('+----------------------------------------------',lRaise,false,false);
                    perform fc_debug('| Divisao da Parcela do Alvara em - '|| iNumeroParcelasAlvara ||' - partes ',lRaise,false,false);
                    perform fc_debug('+--------------------------------------------',lRaise,false,false);

                     for rParcelasAlvara
                      in select numero_parcela,
                                valor_parcela,
                                data_vencimento
                           from fc_issqn_divide_valores(round(nValorParcela,2), iNumeroParcelasAlvara,v_dtvencano )
                     loop
                       -- ---------------------------- --
                       --  Inserindo Dados no Arrecad  --
                       -- ---------------------------- --
                       perform fc_debug('| Parcela           : '|| rParcelasAlvara.numero_parcela ,lRaise,false,false);
                       perform fc_debug('| ValorParcela      : '|| rParcelasAlvara.valor_parcela  ,lRaise,false,false);
                       perform fc_debug('| VencimentoParcela : '|| rParcelasAlvara.data_vencimento,lRaise,false,false);


                        insert
                          into
                       arrecad (k00_numcgm,
                                k00_dtoper,
                                k00_receit,
                                k00_hist,
                                k00_valor,
                                k00_dtvenc,
                                k00_numpre,
                                k00_numpar,
                                k00_numtot,
                                k00_numdig,
                                k00_tipo)
                        values (v_numcgm,
                                v_dtbase,
                                v_receita,
                                v_record_cadvenc.
                                q92_hist,
                                rParcelasAlvara.valor_parcela,   -- VALOR ANTERIOR -> round(nValorParcela,2)
                                rParcelasAlvara.data_vencimento, -- VALOR ANTERIOR -> v_dtvencano,
                                v_numpre,
                                rParcelasAlvara.numero_parcela,  -- VALOR ANTERIOR -> v_numpar
                                iNumeroParcelasAlvara,           -- VALOR ANTERIOR -> v_numtot
                                v_numdig,
                                v_record_cadvenc.q92_tipo);

                     perform fc_debug('+--------------------------------------------',lRaise,false,false);
                     end loop;

                   else -- Fim da Validacao de parcelas e Tipo de Calculo, seguindo como era antes

                    iNumTot = v_numtot;
                    if v_numtot = 0 then
                      iNumTot = 1;
                    end if;

                    insert into arrecad (k00_numcgm, k00_dtoper, k00_receit, k00_hist, k00_valor, k00_dtvenc, k00_numpre, k00_numpar, k00_numtot, k00_numdig, k00_tipo)
                         values (v_numcgm, v_dtbase, v_receita, v_record_cadvenc.q92_hist, round(nValorParcela,2), v_dtvencano, v_numpre, v_numpar, iNumTot, v_numdig, v_record_cadvenc.q92_tipo);
                  end if;
                end if;

              end if; -- final do bloco para bgeral is false
              perform fc_debug('Valor parcela : '||nValorParcela,lRaise,false,false);

              if v_comcalculo is false then

                select fc_numbco(v_codbco,v_codage) into v_numbco;
                /*
                  Comentado insert na arrebanco conforme solicitado pelo Paulo e Dalpozzo, pois nao faz
                  o menor sentido gerar arrebanco no calculo de issqn / alvara
                */
                --insert into arrebanco values (v_numpre,v_numpar,v_codbco,v_codage,v_numbco);
              end if;

              v_numpar = v_numpar + 1;
              v_jagravou = true;

              if nPercentualParcela = 100 then

                perform fc_debug('Saindo do for percentual = 100',lRaise,false,false);
                exit;
              end if;

            -- se lProcessaParcela is false
            else

              v_manual = v_manual || 'nao utilizando vencimento: ' || v_record_cadvenc.q82_venc || '\n';
              perform fc_debug('Nao processando financeiro lProcessaParcela is false',lRaise,false,false);

            end if;

          end loop;

        end if;

      end if;

    end loop;


    for v_record_cadcalc in select * from numpres loop

      select q01_manual
        into sManualText
        from isscalc
       where q01_inscr  = iInscr
         and q01_anousu = iAnousu
         and q01_manual is not null
       limit 1;

      v_manual := v_manual || 'atualizando log do calculo do numpre: ' || v_record_cadcalc.numpre || '\n';
      update isscalc
         set q01_manual = coalesce(sManualText,' ') || v_manual
       where q01_inscr = iInscr and q01_anousu = iAnousu;

    end loop;

    return '01-OK';

  end;

$$ language 'plpgsql';insert into db_versaoant (db31_codver,db31_data) values (367, current_date);
select setval ('db_versaousu_db32_codusu_seq',(select max (db32_codusu) from db_versaousu));
select setval ('db_versaousutarefa_db28_sequencial_seq',(select max (db28_sequencial) from db_versaousutarefa));
select setval ('db_versaocpd_db33_codcpd_seq',(select max (db33_codcpd) from db_versaocpd));
select setval ('db_versaocpdarq_db34_codarq_seq',(select max (db34_codarq) from db_versaocpdarq));create table bkp_db_permissao_20160519_141155 as select * from db_permissao;
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
--------------------------------------------------- INICIO FOLHA ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
ALTER TABLE avaliacaopergunta DROP COLUMN IF EXISTS db103_tipo;
ALTER TABLE avaliacaopergunta DROP COLUMN IF EXISTS db103_mascara ;
DROP TABLE IF EXISTS rhconsignacaobancolayout CASCADE;
DROP SEQUENCE IF EXISTS rhconsignacaobancolayout_rh178_sequencial_seq;

alter table rhconsignadomovimento drop column if exists rh151_arquivo;
alter table rhconsignadomovimento drop column if exists rh151_banco;


---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FINANCEIRO ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
select fc_executa_ddl('alter table acordocomissaomembro drop ac07_datainicio');
select fc_executa_ddl('alter table acordocomissaomembro drop ac07_datatermino');
select fc_executa_ddl('alter table acordoposicao drop ac26_tipooperacao');

drop table if exists acordoencerramentolicitacon;
drop sequence if exists acordoencerramentolicitacon_ac58_sequencial_seq;

-- Campo Ano do Exercício na Tabela acordoempempenho
select fc_executa_ddl('alter table acordoempempenho drop column if exists ac54_ano;');

-- Campo Unidade da autorização de empenho
alter table empautitem drop constraint if exists empautitem_matunid_fk;
alter table empautitem drop column if exists e55_matunid;

---------------------------------------------------------------------------------------------------------------
---------------------------------------- INICIO EDUCACAO ------------------------------------------------------
---------------------------------------------------------------------------------------------------------------
delete from funcaoatividade where ed119_sequencial in (5,6);
drop index if exists ensino_censocursoprofiss_in;
alter table ensino drop column if exists ed10_censocursoprofiss;
alter table escola alter COLUMN ed18_latitude type varchar(10), alter COLUMN ed18_longitude type varchar(10);

---------------------------------------------------------------------------------------------------------------
------------------------------------------- INICIO SAUDE ------------------------------------------------------
---------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS agendasaidapassagemdestino CASCADE;
DROP TABLE IF EXISTS passagemdestino CASCADE;

DROP SEQUENCE IF EXISTS agendasaidapassagemdestino_tf38_sequencial_seq;
DROP SEQUENCE IF EXISTS passagemdestino_tf37_sequencial_seq;

alter table tfd_agendasaida drop column tf17_tiposaida;
SQL_DDL;

        $pre = <<<'SQL_PRE'
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FOLHA ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------


delete from db_sysforkey    where codarq = 3936;
delete from db_sysprikey    where codarq = 3936;
delete from db_syssequencia where codsequencia = 1000570;
delete from db_sysarqmod    where codarq = 3936;
delete from db_sysindices   where codarq = 3936;
delete from db_syscadind    where codcam in (21839, 21840, 21859, 21860, 21861, 21862, 21863, 21869, 21870);
delete from db_syscampodep  where codcam in (21839, 21840, 21859, 21860, 21861, 21862, 21863, 21869, 21870);
delete from db_sysarqcamp   where codcam in (21839, 21840, 21859, 21860, 21861, 21862, 21863, 21869, 21870);
delete from db_syscampo     where codcam in (21839, 21840, 21859, 21860, 21861, 21862, 21863, 21869, 21870);
delete from db_sysarquivo where codarq in (3936);
delete from db_menu where id_item_filho in(10231, 10232, 10233, 10234, 10235, 10238);
delete from db_itensmenu where id_item in(10231,10232, 10233,10234, 10235,10238);

delete from db_layoutcampos where db52_layoutlinha in(836,837, 838);
delete from db_layoutlinha  where db51_layouttxt in(254);
delete from db_layouttxt  where db50_codigo in(254);
delete from pessoal.rhconsignadomotivo where rh154_sequencial in (8, 9);
---------------------------------------------------------------------------------------------------------------------------
--------------------------------------------------- INICIO FINANCEIRO ----------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

select fc_executa_ddl('delete from db_syscampodef where codcam = 21843;');
select fc_executa_ddl('delete from db_sysarqcamp where codcam in(21841, 21842, 21843)');
select fc_executa_ddl('delete from db_syscampo where codcam in(21841, 21842, 21843)');
DELETE from acordoposicaotipo where ac27_sequencial in(7,8);
delete from db_menu where id_item_filho = 10226 AND modulo = 8251;
delete from db_menu where id_item_filho = 10227 AND modulo = 8251;
delete from db_itensmenu where id_item in(10226, 10227);

delete from db_syssequencia where nomesequencia = 'acordoencerramentolicitacon_ac58_sequencial_seq';
delete from db_sysarqcamp   where codarq = 3933;
delete from db_sysforkey    where codarq = 3933;
delete from db_sysprikey    where codarq = 3933;
delete from db_syscampo     where codcam in(21845, 21846, 21847);
delete from db_sysarqmod    where codarq = 3933;
delete from db_acount       where codarq = 3933;
delete from db_sysarquivo   where codarq = 3933;

-- Campo Ano do Exercício na Tabela acordoempempenho
select fc_executa_ddl('delete from db_sysarqcamp where codarq = 3926 and codcam = 21848;');
select fc_executa_ddl('delete from db_syscampo   where codcam = 21848;');

-- Campo Unidade da autorização de empenho
delete from db_sysforkey where codcam = 21868 and codarq = 811;
delete from db_sysarqcamp where codcam = 21868;
delete from db_syscampo where codcam = 21868;

delete from db_menu where id_item_filho = 10236 AND modulo = 209;
delete from db_itensmenu where id_item = 10236;

-- Configuração do relatório Demonstração das Mutações do Patrimônio Líquido
delete from db_menu      where id_item_filho = 10237;
delete from db_itensmenu where id_item       = 10237;

delete from orcparamseqorcparamseqcoluna where o116_codparamrel = 161;
delete from orcparamseq                  where o69_codparamrel  = 161;
delete from orcparamrelperiodos          where o113_orcparamrel = 161;
delete from orcparamseqcoluna            where o115_sequencial in(207, 208, 209, 210, 211, 212, 213, 214);
delete from orcparamrel                  where o42_codparrel    = 161;

---------------------------------------------------------------------------------------------------------------
---------------------------------------- INICIO EDUCACAO ------------------------------------------------------
---------------------------------------------------------------------------------------------------------------

delete from db_syscadind  where codind = 4343;
delete from db_sysindices where codind = 4343;
delete from db_sysforkey  where codcam = 21844;
delete from db_sysarqcamp where codcam = 21844;
delete from db_syscampo   where codcam = 21844;
update db_syscampo set nomecam = 'ed18_latitude', conteudo = 'varchar(10)', descricao = 'Latitude', valorinicial = '', rotulo = 'Latitude', nulo = 't', tamanho = 20, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Latitude' where codcam = 18917;
update db_syscampo set nomecam = 'ed18_longitude', conteudo = 'varchar(10)', descricao = 'Longitude', valorinicial = '', rotulo = 'Longitude', nulo = 't', tamanho = 20, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Longitude' where codcam = 18918;

delete from avaliacaoperguntaopcaolayoutcampo where ed313_ano = 2015 and ed313_db_layoutcampo in (12127, 12129, 12131, 12133, 12134, 12135, 12136, 12141, 12143, 12145, 12147, 12148, 12150, 12151, 12152, 12153, 12155, 12156, 12158, 12159);

update avaliacaoperguntaopcaolayoutcampo
  set ed313_layoutvalorcampo = '0'
  where ed313_db_layoutcampo in (
    12269, 12275, 12281, 12284, 12288, 12289, 12291, 12294, 12297, 12299, 12301, 12303, 12304, 12306, 12310, 12313, 12347, 12277, 12320, 12322, 12325, 12328, 12333, 12336, 12339, 12340, 12341, 12342, 12344, 12361, 12170, 12172, 12174, 12176, 12178, 12181, 12183, 12187, 12247, 12248, 12250, 12377, 12378, 12379, 12350, 12351, 12352, 12353, 12354, 12355, 12356, 12357, 12358, 12359, 12360, 12255, 12257, 12260, 12262, 12264, 12266, 12214, 12218, 12223, 12228, 12231, 12234, 12237, 12241, 12245, 12316, 12185, 12362
  ) and ed313_ano = 2015;

update avaliacaoperguntaopcaolayoutcampo set ed313_layoutvalorcampo = '0' where ed313_avaliacaoperguntaopcao = 3000099 and ed313_ano = 2015;
update avaliacaoperguntaopcaolayoutcampo set ed313_layoutvalorcampo = '1' where ed313_avaliacaoperguntaopcao = 3000100 and ed313_ano = 2015;

---------------------------------------------------------------------------------------------------------------
------------------------------------------- INICIO SAUDE ------------------------------------------------------
---------------------------------------------------------------------------------------------------------------
delete from db_menu         where id_item = 8323 and id_item_filho = 10230;
delete from db_itensmenu    where id_item = 10230;

delete from db_syssequencia where codsequencia = 1000568;
delete from db_syscadind    where codind = 4344 and codcam = 21851 and sequen = 1;
delete from db_sysindices   where codind = 4344;
delete from db_sysforkey    where codarq = 3934;
delete from db_sysprikey    where codarq = 3934;
delete from db_sysarqcamp   where codarq = 3934;
delete from db_syscampo     where codcam in( 21849, 21850, 21851 );
delete from db_sysarqmod    where codmod = 70 and codarq = 3934;
delete from db_sysarquivo   where codarq = 3934;

delete from db_sysarqcamp  where codarq = 2873 and seqarq = 9;
delete from db_syscampodef where codcam = 21852;
delete from db_syscampo    where codcam = 21852;

delete from db_syssequencia where codsequencia = 1000569;
delete from db_syscadind    where codind = 4350 and codcam = 21856 and sequen = 1;
delete from db_syscadind    where codind = 4350 and codcam = 21866 and sequen = 2;
delete from db_sysindices   where codind = 4350;
delete from db_sysforkey    where codarq = 3935;
delete from db_sysprikey    where codarq = 3935;
delete from db_sysarqcamp   where codarq = 3935;
delete from db_syscampo     where codcam in( 21855, 21856, 21857, 21866, 21867 );
delete from db_sysarqmod    where codmod = 70 and codarq = 3935;
delete from db_sysarquivo   where codarq = 3935;

delete from db_docparagpadrao where db62_coddoc = 252;
delete from db_paragrafopadrao where db61_codparag in (549, 550);
delete from db_documentopadrao where db60_coddoc = 252;
delete from db_tipodoc where db08_codigo = 5025;
SQL_PRE;

        $this->execute($ddl);
        $this->execute($pre);

    }
}
