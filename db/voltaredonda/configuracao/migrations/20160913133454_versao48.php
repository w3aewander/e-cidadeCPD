<?php

use Classes\PostgresMigration;

class Versao48 extends PostgresMigration
{
    
    public function up() {

        $pre = <<<'SQL_PRE'
DISCARD TEMP;
---------------------------------------------------------------------------------------------
---------------------------------- INICIO FINANCEIRO -----------------------------------------
---------------------------------------------------------------------------------------------

insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10204 ,'Exportação de Dados' ,'Exportação de Dados' ,'' ,'1' ,'1' ,'Exportação de Dados do Módulo Licitação' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 1818 ,10204 ,115 ,381 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10212 ,'LicitaCon TCE/RS' ,'LicitaCon TCE/RS' ,'' ,'1' ,'1' ,'Exportação de Dados para o LicitaCon TCE/RS' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10204 ,10212 ,2 ,381 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10213 ,'Geração de Arquivos' ,'Geração de Arquivos' ,'lic4_licitacon001.php' ,'1' ,'1' ,'Geração de Arquivos para o LicitaCon TCE/RS' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10212 ,10213 ,1 ,381 );
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10214 ,'Confirmação de Envio' ,'Confirmação de Envio' ,'lic4_licitaconencerramento001.php' ,'1' ,'1' ,'Confirmação de envio dos arquivos do LicitaCon TCE/RS' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 10212 ,10214 ,2 ,381 );

insert into db_layouttxtgrupo (db56_sequencial, db56_layouttxtgrupotipo, db56_descr) values(6, 1, 'ARQUIVOS LICITACON TCE/RS');

-- PESSOAS
insert into db_layouttxt( db50_codigo ,db50_layouttxtgrupo ,db50_descr ,db50_quantlinhas ,db50_obs ) values ( 231 ,6 ,'TCE/RS - LICITACON - PESSOAS' ,0 ,'' );
insert into db_layoutlinha (db51_codigo, db51_layouttxt, db51_descr, db51_tipolinha, db51_tamlinha, db51_linhasantes, db51_linhasdepois, db51_obs, db51_separador, db51_compacta) values (791, 231, 'CABEÇALHO', 1, 0, 0, 0, '', '|', true);
insert into db_layoutlinha (db51_codigo, db51_layouttxt, db51_descr, db51_tipolinha, db51_tamlinha, db51_linhasantes, db51_linhasdepois, db51_obs, db51_separador, db51_compacta) values (792, 231, 'REGISTRO', 3, 0, 0, 0, '', '|', true);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12753, 791, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12754, 791, 'DATA_INICIAL', 'DATA INICIAL', 1, 15, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12755, 791, 'DATA_FINAL', 'DATA FINAL', 1, 25, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12756, 791, 'DATA_GERACAO', 'DATA DE GERAÇÃO', 1, 35, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12757, 791, 'NOME_SETOR', 'NOME DO SETOR DE GOVERNO', 1, 45, '', 150, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12758, 791, 'TOTAL_REGISTROS', 'TOTAL DE REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12759, 792, 'TP_DOCUMENTO', 'TIPO DE DOCUMENTO', 1, 1, '', 1, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12760, 792, 'NR_DOCUMENTO', 'NÚMERO DO DOCUMENTO', 1, 2, '', 14, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12761, 792, 'TP_PESSOA', 'TIPO DE PESSOA', 1, 16, '', 1, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12762, 792, 'NM_PESSOA', 'NOME DA PESSOA', 1, 17, '', 60, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12763, 792, 'DS_OBJETO_SOCIAL', 'DESCRIÇÃO DO OBJETO SOCIAL', 1, 77, '', 60, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12764, 792, 'NR_INSCRICAO_ESTADUAL', 'NÚMERO DE INSCRIÇÃO  ESTADUAL', 1, 137, '', 30, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12765, 792, 'NR_INSCRICAO_MUNICIPAL', 'NÚMERO  DE INSCRIÇÃO  MUNICIPAL', 1, 167, '', 30, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12766, 792, 'CD_TIPO_CONSELHO_PROFISSIONAL', 'CÓDIGO  DO  CONSELHO  REGIONAL', 1, 197, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12767, 792, 'NR_CONSELHO_PROFISSIONAL', 'NÚMERO DO  CONSELHO  REGIONAL', 1, 207, '', 20, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12768, 792, 'SG_UF_CONSELHO_PROFISSIONAL', 'SIGLA DA UF DO CONSELHO REGIONAL', 1, 227, '', 2, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12769, 792, 'DS_EMAIL', 'E-MAIL', 1, 229, '', 60, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12770, 792, 'DS_PAGINA_INTERNET', 'ENDEREÇO DO SITE DA PESSOA', 1, 289, '', 100, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12771, 792, 'SG_UF', 'SIGLA DA UF DO ENDEREÇO DA PESSOA', 1, 389, '', 2, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12772, 792, 'CD_MUNICIPIO_IBGE', 'CÓDIGO IBGE DO MUNICÍPIO', 1, 391, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12773, 792, 'LOGRADOURO', 'NOME DO LOGRADOURO', 1, 401, '', 100, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12774, 792, 'NR_ENDERECO', 'NÚMERO DO ENDEREÇO DO LOGRADOURO', 1, 501, '', 5, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12775, 792, 'COMPLEMENTO', 'COMPLEMENTO DO ENDEREÇO', 1, 506, '', 40, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12776, 792, 'BAIRRO', 'NOME DO BAIRRO', 1, 546, '', 40, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12777, 792, 'CEP', 'NÚMERO DO CEP', 1, 586, '', 8, false, true, 'd', '', 0);
insert into db_layoutcampos (db52_codigo, db52_layoutlinha, db52_nome, db52_descr, db52_layoutformat, db52_posicao, db52_default, db52_tamanho, db52_ident, db52_imprimir, db52_alinha, db52_obs, db52_quebraapos) values (12778, 792, 'TELEFONE', 'TELEFONE', 1, 594, '', 40, false, true, 'd', '', 0);

-- MEMBROCONS
insert into db_layouttxt( db50_codigo ,db50_layouttxtgrupo ,db50_descr ,db50_quantlinhas ,db50_obs ) values ( 232 ,6 ,'TCE/RS - LICITACON - MEMBROCONS' ,0 ,'' );
insert into db_layoutlinha  values (793, 232, 'CABEÇALHO', 1, 0, 0, 0, '', '|', true);
insert into db_layoutcampos values (12779, 793, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0);
insert into db_layoutcampos values (12780, 793, 'DATA_INICIAL', 'DATA INICIAL', 1, 15, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12781, 793, 'DATA_FINAL', 'DATA FINAL', 1, 25, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12782, 793, 'DATA_GERACAO', 'DATA DE GERAÇÃO', 1, 35, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12783, 793, 'NOME_SETOR', 'NOME DO SETOR DE GOVERNO', 1, 45, '', 150, false, true, 'd', '', 0);
insert into db_layoutcampos values (12784, 793, 'TOTAL_REGISTROS', 'TOTAL DE REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0);

-- COMISSAO
insert into db_layouttxt( db50_codigo ,db50_layouttxtgrupo ,db50_descr ,db50_quantlinhas ,db50_obs ) values ( 233 , 6 ,'TCE/RS - LICITACON - COMISSAO' ,0 ,'' );
insert into db_layoutlinha  values (794, 233, 'CABEÇALHO', 1, 0, 0, 0, '', '|', true);
insert into db_layoutcampos values (12785, 794, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0);
insert into db_layoutcampos values (12786, 794, 'DATA_INICIAL', 'DATA INICIAL', 1, 15, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12787, 794, 'DATA_FINAL', 'DATA FINAL', 1, 25, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12788, 794, 'DATA_GERACAO', 'DATA DE GERAÇÃO', 1, 35, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12789, 794, 'NOME_SETOR', 'NOME DO SETOR DE GOVERNO', 1, 45, '', 150, false, true, 'd', '', 0);
insert into db_layoutcampos values (12790, 794, 'TOTAL_REGISTROS', 'TOTAL DE REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0);

insert into db_layoutlinha  values (795, 233, 'REGISTRO', 3, 0, 0, 0, '', '|', true);
insert into db_layoutcampos values (12791, 795, 'NR_COMISSAO', 'NR COMISSAO', 1, 1, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12792, 795, 'ANO_COMISSAO', 'ANO COMISSAO', 1, 15, '', 4, false, true, 'd', '', 0);
insert into db_layoutcampos values (12793, 795, 'TP_COMISSAO', 'TP COMISSAO', 1, 25, '', 1, false, true, 'd', '', 0);
insert into db_layoutcampos values (12794, 795, 'DT_DESIGNACAO', 'DT DESIGNACAO', 1, 35, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12795, 795, 'DT_INICIO_VIGENCIA', 'DT INICIO VIGENCIA', 1, 45, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12796, 795, 'DT_FINAL_VIGENCIA', 'DT FINAL VIGENCIA', 1, 195, '', 10, false, true, 'd', '', 0);
insert into db_layoutcampos values (12797, 795, 'NOME_ARQUIVO_DOCUMENTO', 'NOME ARQUIVO DOCUMENTO', 13, 195, '', 100, false, true, 'd', '', 0);

insert into db_syscampo values(21704,'l30_arquivo','oid','OID do Arquivo','null', 'Arquivo',1,'t','f','f',1,'text','Arquivo');
insert into db_syscampo values(21705,'l30_nomearquivo','varchar(100)','Nome do Arquivo','', 'Nome do Arquivo',100,'t','t','f',0,'text','Nome do Arquivo');
insert into db_sysarqcamp values(1324,21705,6,0);
insert into db_sysarqcamp values(1324,21704,7,0);

delete from db_syscampodef where codcam = 7915;
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 7915 ,'1' ,'Permanente' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 7915 ,'2' ,'Especial' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 7915 ,'3' ,'Pregão' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 7915 ,'4' ,'Servidor Designado' );

insert into db_sysarquivo values
  (3900, 'db_cadattdinamicosysarquivo', 'Vinculo entre os atributos dinâmicos e a tabela do sistema.', 'db17', '2016-02-18', 'Atributos Dinâmicos Sysarquivo', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (7,3900);

insert into db_syscampo values
  ( 21706 ,'db17_sequencial' ,'int4' ,'Código Sequencial' ,'' ,'Código' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código' ),
  ( 21707 ,'db17_sysarquivo' ,'int4' ,'Código da Tabela do Sistema' ,'' ,'Código Tabela' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código Tabela' ),
  ( 21708 ,'db17_cadattdinamico' ,'int4' ,'Código dos Atributos Dinâmicos' ,'' ,'Código Atributos' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código Atributos' );

insert into db_sysarqcamp values
  ( 3900 ,21706 ,1 ,0 ),
  ( 3900 ,21707 ,2 ,0 ),
  ( 3900 ,21708 ,3 ,0 );

insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3900,21706,1,21706);

insert into db_sysforkey values
  (3900,21707,1,140,0),
  (3900,21708,1,3162,0);

insert into db_syssequencia values(1000545, 'db_cadattdinamicosysarquivo_db17_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000545 where codarq = 3900 and codcam = 21706;

insert into db_sysindices values(4321,'db_cadattdinamicosysarquivo_sysarquivo_un',3900,'1');
insert into db_syscadind values(4321,21707,1);

insert into db_syscampo values ( 21709 ,'db109_nome' ,'varchar(100)' ,'Nome do Campo' ,'' ,'Nome do Campo' ,100 ,'true' ,'false' ,'false' ,0 ,'text' ,'Nome do Campo' );
insert into db_sysarqcamp values ( 3163 ,21709 ,7 ,0 );

update db_syscampo set conteudo = 'text', tamanho = 1  where codcam = 17882;

-- tabela de vinculo
insert into db_sysarquivo values
  (3901, 'liccomissaocgmcadattdinamicovalorgrupo', 'Valores dos Atributos Dinâmicos', 'l15', '2016-02-19', 'Valores dos Atributos Dinâmicos', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (19,3901);
insert into db_syscampo values
  ( 21710 ,'l15_sequencial' ,'int4' ,'Código' ,'' ,'Código' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código' ),
  ( 21711 ,'l15_liccomissaocgm' ,'int4' ,'Menbro da Comissão' ,'' ,'Menbro da Comissão' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Menbro da Comissão' ),
  ( 21712 ,'l15_cadattdinamicovalorgrupo' ,'int4' ,'Grupo dos Valores' ,'' ,'Grupo dos Valores' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Grupo dos Valores' );
insert into db_sysarqcamp values
  ( 3901 ,21710 ,1 ,0 ),
  ( 3901 ,21711 ,2 ,0 ),
  ( 3901 ,21712 ,3 ,0 );

insert into db_syssequencia values(1000546, 'liccomissaocgmcadattdinamicovalorgrupo_l15_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000546 where codarq = 3901 and codcam = 21710;

insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3901,21710,1,21710);
insert into db_sysforkey values(3901,21711,1,1325,0);
insert into db_sysforkey values(3901,21712,1,3165,0);

insert into db_sysarquivo values
  (3902, 'db_cadattdinamicoatributosopcoes', 'Opções dos Atributos Dinâmicos', 'db18', '2016-02-22', 'Opções dos Atributos Dinâmicos', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (7,3902);

insert into db_syscampo values
  ( 21713 ,'db18_sequencial' ,'int4' ,'Código' ,'' ,'Código' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código' ),
  ( 21714 ,'db18_cadattdinamicoatributos' ,'int4' ,'Atributo' ,'' ,'Atributo' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Atributo' ),
  ( 21715 ,'db18_opcao' ,'varchar(50)' ,'Opção padrão do campo' ,'' ,'Opção' ,50 ,'false' ,'false' ,'false' ,0 ,'text' ,'Opção' ),
  ( 21716 ,'db18_valor' ,'varchar(200)' ,'Valor padrão do campo' ,'' ,'Valor' ,200 ,'false' ,'false' ,'false' ,0 ,'text' ,'Valor' );

insert into db_sysarqcamp values
  ( 3902 ,21713 ,1 ,0 ),
  ( 3902 ,21714 ,2 ,0 ),
  ( 3902 ,21715 ,3 ,0 ),
  ( 3902 ,21716 ,4 ,0 );

insert into db_syssequencia values(1000547, 'db_cadattdinamicoatributosopcoes_db18_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000547 where codarq = 3902 and codcam = 21713;

insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3902,21713,1,21713);
insert into db_sysforkey values(3902,21714,1,3163,0);

-- MEMCOMISSAO
insert into db_layouttxt values (234, 'TCE/RS - LICITACON - MEMCOMISSAO', 0, '', 6 );
insert into db_layoutlinha values (796, 234, 'CABEÇALHO', 1, 0, 0, 0, '', '|', true );
insert into db_layoutlinha values (797, 234, 'REGISTRO', 3, 0, 0, 0, '', '|', true );
insert into db_layoutcampos values (12798, 796, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12799, 796, 'DATA_INICIAL', 'DATA_INICIAL', 1, 15, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12800, 796, 'DATA_FINAL', 'DATA_FINAL', 1, 25, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12801, 796, 'DATA_GERACAO', 'DATA_GERACAO', 1, 35, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12802, 796, 'NOME_SETOR', 'NOME_SETOR', 1, 45, '', 150, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12803, 796, 'TOTAL_REGISTROS', 'TOTAL_REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12804, 797, 'NR_COMISSAO', 'NR_COMISSAO', 1, 1, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12805, 797, 'ANO_COMISSAO', 'ANO_COMISSAO', 1, 11, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12806, 797, 'TP_COMISSAO', 'TP_COMISSAO', 1, 15, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12807, 797, 'TP_DOCUMENTO_MEMBRO', 'TP_DOCUMENTO_MEMBRO', 1, 16, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12808, 797, 'NR_DOCUMENTO_MEMBRO', 'NR_DOCUMENTO_MEMBRO', 1, 17, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12809, 797, 'DS_CARGO', 'DS_CARGO', 13, 31, '', 60, false, true, 'e', '', 0 );
insert into db_layoutcampos values (12810, 797, 'TP_CARGO', 'TP_CARGO', 1, 91, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12811, 797, 'TP_ATRIBUICAO', 'TP_ATRIBUICAO', 1, 92, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12812, 797, 'DT_DESIGNACAO', 'DT_DESIGNACAO', 1, 93, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12813, 797, 'NR_ATO_DESIGNACAO', 'NR_ATO_DESIGNACAO', 1, 103, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12814, 797, 'ANO_ATO_DESIGNACAO', 'ANO_ATO_DESIGNACAO', 1, 113, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12815, 797, 'DT_DESTITUICAO', 'DT_DESTITUICAO', 1, 117, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12816, 797, 'NR_ATO_DESTITUICAO', 'NR_ATO_DESTITUICAO', 1, 127, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12817, 797, 'ANO_ATO_DESTITUICAO', 'ANO_ATO_DESTITUICAO', 1, 137, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12818, 797, 'NOME_ARQUIVO_DOCUMENTO', 'NOME_ARQUIVO_DOCUMENTO', 13, 141, '', 100, false, true, 'e', '', 0 );

-- tipo compra tribunal
insert into db_syscampo values
  ( 21717 ,'l44_sigla' ,'varchar(3)' ,'Sigla' ,'' ,'Sigla' ,3 ,'true' ,'false' ,'false' ,0 ,'text' ,'Sigla' );
insert into db_sysarqcamp values
  ( 3145 ,21717 ,5 ,0 );

-- LICITACAO
insert into db_layouttxt values (235, 'TCE/RS - LICITACON - LICITACAO', 0, '', 6 );
insert into db_layoutlinha values (798, 235, 'CABEÇALHO', 1, 0, 0, 0, '', '|', true );
insert into db_layoutcampos values (12819, 798, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12820, 798, 'DATA_INICIAL', 'DATA_INICIAL', 1, 15, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12821, 798, 'DATA_FINAL', 'DATA_FINAL', 1, 25, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12822, 798, 'DATA_GERACAO', 'DATA_GERACAO', 1, 35, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12823, 798, 'NOME_SETOR', 'NOME_SETOR', 1, 45, '', 150, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12824, 798, 'TOTAL_REGISTROS', 'TOTAL_REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0 );
insert into db_layoutlinha values (799, 235, 'REGISTRO', 3, 0, 0, 0, '', '|', true );
insert into db_layoutcampos values (12825, 799, 'NR_LICITACAO', 'NR_LICITACAO', 1, 1, '', 20, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12826, 799, 'ANO_LICITACAO', 'ANO_LICITACAO', 1, 21, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12827, 799, 'CD_TIPO_MODALIDADE', 'CD_TIPO_MODALIDADE', 1, 25, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12828, 799, 'NR_COMISSAO', 'NR_COMISSAO', 1, 28, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12829, 799, 'ANO_COMISSAO', 'ANO_COMISSAO', 1, 38, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12830, 799, 'TP_COMISSAO', 'TP_COMISSAO', 1, 42, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12831, 799, 'NR_PROCESSO', 'NR_PROCESSO', 1, 43, '', 20, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12832, 799, 'ANO_PROCESSO', 'ANO_PROCESSO', 1, 63, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12833, 799, 'TP_OBJETO', 'TP_OBJETO', 1, 67, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12834, 799, 'CD_TIPO_FASE_ATUAL', 'CD_TIPO_FASE_ATUAL', 1, 70, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12835, 799, 'TP_LICITACAO', 'TP_LICITACAO', 1, 73, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12836, 799, 'TP_NIVEL_JULGAMENTO', 'TP_NIVEL_JULGAMENTO', 1, 76, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12837, 799, 'DT_AUTORIZACAO_ADESAO', 'DT_AUTORIZACAO_ADESAO', 1, 77, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12838, 799, 'TP_CARACTERISTICA_OBJETO', 'TP_CARACTERISTICA_OBJETO', 1, 87, '', 2, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12839, 799, 'TP_NATUREZA', 'TP_NATUREZA', 1, 89, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12840, 799, 'TP_REGIME_EXECUCAO', 'TP_REGIME_EXECUCAO', 1, 90, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12841, 799, 'BL_PERMITE_SUBCONTRATACAO', 'BL_PERMITE_SUBCONTRATACAO', 1, 91, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12842, 799, 'TP_BENEFICIO_MICRO_EPP', 'TP_BENEFICIO_MICRO_EPP', 1, 92, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12843, 799, 'TP_FORNECIMENTO', 'TP_FORNECIMENTO', 1, 93, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12844, 799, 'TP_ATUACAO_REGISTRO', 'TP_ATUACAO_REGISTRO', 1, 94, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12845, 799, 'NR_LICITACAO_ORIGINAL', 'NR_LICITACAO_ORIGINAL', 1, 95, '', 20, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12846, 799, 'ANO_LICITACAO_ORIGINAL', 'ANO_LICITACAO_ORIGINAL', 1, 115, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12847, 799, 'NR_ATA_REGISTRO_PRECO', 'NR_ATA_REGISTRO_PRECO', 1, 119, '', 20, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12848, 799, 'DT_ATA_REGISTRO_PRECO', 'DT_ATA_REGISTRO_PRECO', 1, 139, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12849, 799, 'PC_TAXA_RISCO', 'PC_TAXA_RISCO', 1, 149, '', 6, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12850, 799, 'TP_EXECUCAO', 'TP_EXECUCAO', 1, 155, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12851, 799, 'TP_DISPUTA', 'TP_DISPUTA', 1, 156, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12852, 799, 'TP_PREQUALIFICACAO', 'TP_PREQUALIFICACAO', 1, 157, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12853, 799, 'BL_INVERSAO_FASES', 'BL_INVERSAO_FASES', 1, 158, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12854, 799, 'TP_RESULTADO_GLOBAL', 'TP_RESULTADO_GLOBAL', 1, 159, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12856, 799, 'CNPJ_ORGAO_GERENCIADOR', 'CNPJ_ORGAO_GERENCIADOR', 1, 161, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12857, 799, 'NM_ORGAO_GERENCIADOR', 'NM_ORGAO_GERENCIADOR', 1, 175, '', 60, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12858, 799, 'DS_OBJETO', 'DS_OBJETO', 1, 235, '', 500, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12859, 799, 'CD_TIPO_FUNDAMENTACAO', 'CD_TIPO_FUNDAMENTACAO', 1, 735, '', 8, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12860, 799, 'NR_ARTIGO', 'NR_ARTIGO', 1, 743, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12861, 799, 'DS_INCISO', 'DS_INCISO', 1, 753, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12862, 799, 'DS_LEI', 'DS_LEI', 1, 763, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12863, 799, 'DT_INICIO_INSCR_CRED', 'DT_INICIO_INSCR_CRED', 1, 773, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12864, 799, 'DT_FIM_INSCR_CRED', 'DT_FIM_INSCR_CRED', 1, 783, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12865, 799, 'DT_INICIO_VIGEN_CRED', 'DT_INICIO_VIGEN_CRED', 1, 793, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12866, 799, 'DT_FIM_VIGEN_CRED', 'DT_FIM_VIGEN_CRED', 1, 803, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12867, 799, 'VL_LICITACAO', 'VL_LICITACAO', 1, 813, '', 16, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12868, 799, 'BL_ORCAMENTO_SIGILOSO', 'BL_ORCAMENTO_SIGILOSO', 1, 829, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12869, 799, 'BL_RECEBE_INSCRICAO_PER_VIG', 'BL_RECEBE_INSCRICAO_PER_VIG', 1, 830, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12870, 799, 'BL_PERMITE_CONSORCIO', 'BL_PERMITE_CONSORCIO', 1, 831, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12871, 799, 'DT_ABERTURA', 'DT_ABERTURA', 1, 832, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12872, 799, 'DT_HOMOLOGACAO', 'DT_HOMOLOGACAO', 1, 842, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12873, 799, 'DT_ADJUDICACAO', 'DT_ADJUDICACAO', 1, 852, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12874, 799, 'BL_LICIT_PROPRIA_ORGAO', 'BL_LICIT_PROPRIA_ORGAO', 1, 862, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12875, 799, 'TP_DOCUMENTO_FORNECEDOR', 'TP_DOCUMENTO_FORNECEDOR', 1, 863, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12876, 799, 'NR_DOCUMENTO_FORNECEDOR', 'NR_DOCUMENTO_FORNECEDOR', 1, 864, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12877, 799, 'TP_DOCUMENTO_VENCEDOR', 'TP_DOCUMENTO_VENCEDOR', 1, 878, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12878, 799, 'NR_DOCUMENTO_VENCEDOR', 'NR_DOCUMENTO_VENCEDOR', 1, 879, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12879, 799, 'VL_HOMOLOGADO', 'VL_HOMOLOGADO', 1, 893, '', 16, false, true, 'd', '', 0 );


insert into db_sysarquivo values
  (3903, 'liclicitacadattdinamicovalorgrupo', 'Vinculo da licitação com os atributos dinamicos', 'l16', '2016-02-23', 'Vinculo da licitação com os atributos dinamicos', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (19,3903);

insert into db_syscampo values
  ( 21718 ,'l16_sequencial' ,'int4' ,'Código' ,'' ,'Código' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código' ),
  ( 21719 ,'l16_cadattdinamicovalorgrupo' ,'int4' ,'Grupo de Valores' ,'' ,'Grupo de Valores' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Grupo de Valores' ),
  ( 21720 ,'l16_liclicita' ,'int4' ,'Licitação' ,'' ,'Licitação' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Licitação' );

insert into db_sysarqcamp values
  ( 3903 ,21718 ,1 ,0 ),
  ( 3903 ,21719 ,2 ,0 ),
  ( 3903 ,21720 ,3 ,0 );

insert into db_syssequencia values(1000548, 'liclicitacadattdinamicovalorgrupo_l16_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000548 where codarq = 3903 and codcam = 21718;

insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3903,21718,1,21718);
insert into db_sysforkey values(3903,21719,1,3165,0);
insert into db_sysforkey values(3903,21720,1,1260,0);

-- Situacao Licitacao
insert into licsituacao values (6, 'Adjudicada', false);
insert into licsituacao values (7, 'Homologada', false);

-- Menu Lancar Valores -> Lancar Propostas
update db_itensmenu set id_item = 4686 , descricao = 'Lançar Propostas' , help = 'Lançar Propostas para Licitação' , funcao = 'lic1_lancavallic001.php' , itemativo = '1' , manutencao = '1' , desctec = 'Lançar Propostas para Licitação' , libcliente = 'true' where id_item = 4686;

  -- Menu Habilitação de Fornecedores
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10206 ,'Habilitação de Fornecedores' ,'Habilitação de Fornecedores' ,'lic4_habilitacaofornecedores001.php' ,'1' ,'1' ,'Habilitação de Fornecedores' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 1818 ,10206 ,116 ,381 );

update db_menu set menusequencia = 1 where id_item = 1818 and modulo = 381 and id_item_filho = 4689;
update db_menu set menusequencia = 2 where id_item = 1818 and modulo = 381 and id_item_filho = 5478;
update db_menu set menusequencia = 3 where id_item = 1818 and modulo = 381 and id_item_filho = 4680;
update db_menu set menusequencia = 4 where id_item = 1818 and modulo = 381 and id_item_filho = 4685;
update db_menu set menusequencia = 5 where id_item = 1818 and modulo = 381 and id_item_filho = 4686;
update db_menu set menusequencia = 6 where id_item = 1818 and modulo = 381 and id_item_filho = 10206;
update db_menu set menusequencia = 7 where id_item = 1818 and modulo = 381 and id_item_filho = 147886;
update db_menu set menusequencia = 8 where id_item = 1818 and modulo = 381 and id_item_filho = 4718;
update db_menu set menusequencia = 9 where id_item = 1818 and modulo = 381 and id_item_filho = 4719;
update db_menu set menusequencia = 10 where id_item = 1818 and modulo = 381 and id_item_filho = 4750;
update db_menu set menusequencia = 11 where id_item = 1818 and modulo = 381 and id_item_filho = 5624;
update db_menu set menusequencia = 12 where id_item = 1818 and modulo = 381 and id_item_filho = 6813;
update db_menu set menusequencia = 13 where id_item = 1818 and modulo = 381 and id_item_filho = 7985;
update db_menu set menusequencia = 14 where id_item = 1818 and modulo = 381 and id_item_filho = 8056;
update db_menu set menusequencia = 15 where id_item = 1818 and modulo = 381 and id_item_filho = 8131;
update db_menu set menusequencia = 16 where id_item = 1818 and modulo = 381 and id_item_filho = 8602;
update db_menu set menusequencia = 17 where id_item = 1818 and modulo = 381 and id_item_filho = 8983;
update db_menu set menusequencia = 18 where id_item = 1818 and modulo = 381 and id_item_filho = 9401;
update db_menu set menusequencia = 19 where id_item = 1818 and modulo = 381 and id_item_filho = 10204;

-- Menu Licitacoes - Procedimentos - Licitacao - Adjudicar
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10207 ,'Adjudicar' ,'Adjudicar Licitação' ,'lic4_adjudicacaohomologacao001.php?situacao=6' ,'1' ,'1' ,'Adjudicar licitação' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 4680 ,10207 ,10 ,381 );

-- Menu Licitacoes - Procedimentos - Licitacao - Homologar
insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10208 ,'Homologar' ,'Homologar Licitação' ,'lic4_adjudicacaohomologacao001.php?situacao=7' ,'1' ,'1' ,'Homologar Licitação' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 4680 ,10208 ,11 ,381 );

-- Tabela Habilitação de Fornecedores
insert into db_sysarquivo values (3904, 'pcorcamfornelichabilitacao', 'Habilitação dos Fornecedores da Licitação', 'l17', '2016-02-25', 'Habilitação dos Fornecedores da Licitação', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (19,3904);
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21721 ,'l17_sequencial' ,'int4' ,'Código' ,'' ,'Código' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código' );

insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3904 ,21721 ,1 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21722 ,'l17_pcorcamfornelic' ,'int4' ,'Fornecedor' ,'' ,'Fornecedor' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Fornecedor' );

insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3904 ,21722 ,2 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21723 ,'l17_situacao' ,'int4' ,'Situação' ,'' ,'Situação' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Situação' );

insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21723 ,'1' ,'Habilitado' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21723 ,'2' ,'Inabilitado' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21723 ,'3' ,'Não Compareceu' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3904 ,21723 ,3 ,0 );

insert into db_syssequencia values(1000549, 'pcorcamfornelichabilitacao_l17_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000549 where codarq = 3904 and codcam = 21721;

insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3904,21721,1,21721);
insert into db_sysforkey values(3904,21722,1,1291,0);

-- Campo Tipo de Condição - Tabela de Fornecedores da Liquidação
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21728 ,'pc31_tipocondicao' ,'int4' ,'Tipo de Condição' ,'' ,'Tipo de Condição' ,10 ,'true' ,'false' ,'false' ,1 ,'text' ,'Tipo de Condição' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21728 ,'1' ,'Convidado e Participante' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21728 ,'2' ,'Convidado e Não Participante' );
insert into db_syscampodef ( codcam ,defcampo ,defdescr ) values ( 21728 ,'3' ,'Não Convidado e Participante' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 1291 ,21728 ,6 ,0 );

-- Arquivo Licitante
insert into db_layouttxt values (236, 'TCE/RS - LICITACON - LICITANTE', 0, '', 6 );
insert into db_layoutlinha values (800, 236, 'CABEÇALHO', 1, 0, 0, 0, '', '|', true );
insert into db_layoutlinha values (801, 236, 'REGISTRO', 3, 0, 0, 0, '', '|', true );
insert into db_layoutcampos values (12880, 800, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12881, 800, 'DATA_INICIAL', 'DATA_INICIAL', 1, 15, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12882, 800, 'DATA_FINAL', 'DATA_FINAL', 1, 25, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12883, 800, 'DATA_GERACAO', 'DATA_GERACAO', 1, 35, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12884, 800, 'NOME_SETOR', 'NOME_SETOR', 1, 45, '', 150, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12885, 800, 'TOTAL_REGISTROS', 'TOTAL_REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12887, 801, 'ANO_LICITACAO', 'ANO_LICITACAO', 1, 21, '', 4, false, true, 'e', '', 0 );
insert into db_layoutcampos values (12888, 801, 'CD_TIPO_MODALIDADE', 'CD_TIPO_MODALIDADE', 1, 25, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12889, 801, 'TP_DOCUMENTO_LICITANTE', 'TP_DOCUMENTO_LICITANTE', 1, 28, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12890, 801, 'NR_DOCUMENTO_LICITANTE', 'NR_DOCUMENTO_LICITANTE', 1, 29, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12891, 801, 'TP_DOCUMENTO_REPRES', 'TP_DOCUMENTO_REPRES', 1, 43, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12892, 801, 'NR_DOCUMENTO_REPRES', 'NR_DOCUMENTO_REPRES', 1, 44, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12893, 801, 'TP_CONDICAO', 'TP_CONDICAO', 1, 58, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12894, 801, 'TP_RESULTADO_HABILITACAO', 'TP_RESULTADO_HABILITACAO', 1, 61, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12895, 801, 'BL_BENEFICIO_MICRO_EPP', 'BL_BENEFICIO_MICRO_EPP', 1, 62, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12886, 801, 'NR_LICITACAO', 'NR_LICITACAO', 1, 1, '', 20, false, true, 'e', '', 0 );

-- DOTACAO_LIC
insert into db_layouttxt values (237, 'TCE/RS - LICITACON - DOTACAO_LIC', 0, '', 6 );
insert into db_layoutlinha values (802, 237, 'CABEÇALHO  ', 1, 0, 0, 0, '', '|', true );
insert into db_layoutlinha values (803, 237, 'REGISTRO', 3, 0, 0, 0, '', '|', true );
insert into db_layoutcampos values (12896, 802, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12897, 802, 'DATA_INICIAL', 'DATA_INICIAL', 1, 15, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12898, 802, 'DATA_FINAL', 'DATA_FINAL', 1, 25, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12899, 802, 'DATA_GERACAO', 'DATA_GERACAO', 1, 35, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12900, 802, 'NOME_SETOR', 'NOME_SETOR', 1, 45, '', 150, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12901, 802, 'TOTAL_REGISTROS', 'TOTAL_REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12902, 803, 'NR_LICITACAO', 'NR_LICITACAO', 1, 1, '', 20, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12903, 803, 'ANO_LICITACAO', 'ANO_LICITACAO', 1, 21, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12904, 803, 'CD_TIPO_MODALIDADE', 'CD_TIPO_MODALIDADE', 1, 25, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12905, 803, 'CD_PROJETO_ATIVIDADE', 'CD_PROJETO_ATIVIDADE', 2, 28, '', 5, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12906, 803, 'CD_RECURSO_ORCAMENTARIO', 'CD_RECURSO_ORCAMENTARIO', 1, 33, '', 4, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12907, 803, 'CD_NATUREZA_DESPESA', 'CD_NATUREZA_DESPESA', 1, 37, '', 6, false, true, 'd', '', 0 );

insert into db_itensmenu values( 10209, 'Eventos', 'Eventos da Licitação', 'lic4_licitacaoeventos001.php', '1', '1', '', '1');
insert into db_itensfilho (id_item, codfilho) values(10209,1);
delete from db_menu where id_item_filho = 10209 AND modulo = 381;
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 1818 ,10209 ,115 ,381 );
update db_menu set menusequencia = 1 where id_item = 1818 and modulo = 381 and id_item_filho = 4689;
update db_menu set menusequencia = 2 where id_item = 1818 and modulo = 381 and id_item_filho = 5478;
update db_menu set menusequencia = 3 where id_item = 1818 and modulo = 381 and id_item_filho = 4680;
update db_menu set menusequencia = 4 where id_item = 1818 and modulo = 381 and id_item_filho = 4685;
update db_menu set menusequencia = 5 where id_item = 1818 and modulo = 381 and id_item_filho = 4686;
update db_menu set menusequencia = 6 where id_item = 1818 and modulo = 381 and id_item_filho = 10206;
update db_menu set menusequencia = 7 where id_item = 1818 and modulo = 381 and id_item_filho = 10209;
update db_menu set menusequencia = 8 where id_item = 1818 and modulo = 381 and id_item_filho = 147886;
update db_menu set menusequencia = 9 where id_item = 1818 and modulo = 381 and id_item_filho = 4718;
update db_menu set menusequencia = 10 where id_item = 1818 and modulo = 381 and id_item_filho = 4719;
update db_menu set menusequencia = 11 where id_item = 1818 and modulo = 381 and id_item_filho = 4750;
update db_menu set menusequencia = 12 where id_item = 1818 and modulo = 381 and id_item_filho = 5624;
update db_menu set menusequencia = 13 where id_item = 1818 and modulo = 381 and id_item_filho = 6813;
update db_menu set menusequencia = 14 where id_item = 1818 and modulo = 381 and id_item_filho = 7985;
update db_menu set menusequencia = 15 where id_item = 1818 and modulo = 381 and id_item_filho = 8056;
update db_menu set menusequencia = 16 where id_item = 1818 and modulo = 381 and id_item_filho = 8131;
update db_menu set menusequencia = 17 where id_item = 1818 and modulo = 381 and id_item_filho = 8602;
update db_menu set menusequencia = 18 where id_item = 1818 and modulo = 381 and id_item_filho = 8983;
update db_menu set menusequencia = 19 where id_item = 1818 and modulo = 381 and id_item_filho = 9401;
update db_menu set menusequencia = 20 where id_item = 1818 and modulo = 381 and id_item_filho = 10204;

insert into db_sysarquivo values (3916, 'liclicitatipoevento', 'Eventos da licitação', 'l45', '2016-03-01', 'Eventos da Licitação', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (19,3916);
insert into db_syscampo values(21737,'l45_sequencial','int4','Código sequencial','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21738,'l45_descricao','varchar(200)','Descrição do tipo de evento','', 'Descrição',200,'f','t','f',0,'text','Descrição');
delete from db_sysarqcamp where codarq = 3916;
insert into db_sysarqcamp values(3916,21737,1,0);
insert into db_sysarqcamp values(3916,21738,2,0);
delete from db_sysprikey where codarq = 3916;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3916,21737,1,21738);
insert into db_sysindices values(4328,'liclicitatipoevento_sequencial_in',3916,'0');
insert into db_syscadind values(4328,21737,1);
insert into db_syssequencia values(1000552, 'liclicitatipoevento_l45_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000552 where codarq = 3916 and codcam = 21737;

insert into db_sysarquivo values (3917, 'liclicitaevento', 'Eventos da Licitação', 'l46', '2016-03-01', 'Eventos da Licitação', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (19,3917);
insert into db_syscampo values(21739,'l46_sequencial','int4','Código sequencial','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21740,'l46_liclicita','int4','Código sequencial da Licitação','0', 'Código da Licitação',10,'f','f','f',1,'text','Código da Licitação');
insert into db_syscampo values(21741,'l46_fase','int4','Fase em em que se encontra a licitação.','0', 'Fase',10,'f','f','f',1,'text','Fase');
insert into db_syscampo values(21742,'l46_liclicitatipoevento','int4','Tipo de evento da licitação.','0', 'Tipo de Evento',10,'f','f','f',1,'text','Tipo de Evento');
insert into db_syscampo values(21743,'l46_dataevento','date','Data do Evento','null', 'Data do Evento',10,'f','f','f',1,'text','Data do Evento');
insert into db_syscampo values(21744,'l46_datajulgamento','date','Data do Julgamento','null', 'Data do Julgamento',10,'t','f','f',1,'text','Data do Julgamento');
insert into db_syscampo values(21745,'l46_cgm','int4','Autor','null', 'Autor',10,'t','f','f',1,'text','Autor');
insert into db_syscampo values(21746,'l46_tipopublicacao','int4','Tipo de Publicação','null', 'Tipo de Publicação',10,'t','f','f',1,'text','Tipo de Publicação');
insert into db_syscampo values(21747,'l46_descricaopublicacao','text','Descrição da Publicação','', 'Descrição da Publicação',1,'t','t','f',0,'text','Descrição da Publicação');
insert into db_syscampo values(21748,'l46_tiporesultado','int4','Tipo de Resultado','null', 'Tipo de Resultado',10,'f','f','f',1,'text','Tipo de Resultado');
delete from db_sysarqcamp where codarq = 3917;
insert into db_sysarqcamp values(3917,21739,1,0);
insert into db_sysarqcamp values(3917,21740,2,0);
insert into db_sysarqcamp values(3917,21741,3,0);
insert into db_sysarqcamp values(3917,21742,4,0);
insert into db_sysarqcamp values(3917,21743,5,0);
insert into db_sysarqcamp values(3917,21744,6,0);
insert into db_sysarqcamp values(3917,21745,7,0);
insert into db_sysarqcamp values(3917,21746,8,0);
insert into db_sysarqcamp values(3917,21747,9,0);
insert into db_sysarqcamp values(3917,21748,10,0);
delete from db_sysforkey where codarq = 3917 and referen = 0;
insert into db_sysforkey values(3917,21740,1,1260,0);
delete from db_sysforkey where codarq = 3917 and referen = 0;
insert into db_sysforkey values(3917,21742,1,3916,0);
delete from db_sysforkey where codarq = 3917 and referen = 0;
insert into db_sysforkey values(3917,21745,1,42,0);
delete from db_sysprikey where codarq = 3917;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3917,21740,1,21739);
insert into db_sysindices values(4329,'liclicitaevento_liclicita_in',3917,'0');
insert into db_syscadind values(4329,21740,1);
insert into db_syssequencia values(1000553, 'liclicitaevento_l46_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000553 where codarq = 3917 and codcam = 21739;

update db_syscampo set nomecam = 'l46_fase', conteudo = 'int4', descricao = 'Fase em em que se encontra a licitação.', valorinicial = '0', rotulo = 'Fase', nulo = 'f', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Fase' where codcam = 21741;
delete from db_syscampodep where codcam = 21741;
delete from db_syscampodef where codcam = 21741;
insert into db_syscampodef values(21741,'1','Fase Interna');
insert into db_syscampodef values(21741,'2','Edital Publicado');
insert into db_syscampodef values(21741,'3','Publicação');
insert into db_syscampodef values(21741,'4','Habilitação / Propostas');
insert into db_syscampodef values(21741,'5','Adjudicação / Homologação');

update db_syscampo set nomecam = 'l46_tiporesultado', conteudo = 'int4', descricao = 'Tipo de Resultado', valorinicial = '0', rotulo = 'Tipo de Resultado', nulo = 'f', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Tipo de Resultado' where codcam = 21748;
delete from db_syscampodep where codcam = 21748;
delete from db_syscampodef where codcam = 21748;
insert into db_syscampodef values(21748,'1','Deferido');
insert into db_syscampodef values(21748,'2','Indeferido');
insert into db_syscampodef values(21748,'3','Deferido Parcialmente');

update db_syscampo set nomecam = 'l46_tipopublicacao', conteudo = 'int4', descricao = 'Tipo de Publicação', valorinicial = '0', rotulo = 'Tipo de Publicação', nulo = 't', tamanho = 10, maiusculo = 'f', autocompl = 'f', aceitatipo = 1, tipoobj = 'text', rotulorel = 'Tipo de Publicação' where codcam = 21746;
delete from db_syscampodep where codcam = 21746;
delete from db_syscampodef where codcam = 21746;
insert into db_syscampodef values(21746,'1','Diário Oficial do Estado');
insert into db_syscampodef values(21746,'2','Internet');
insert into db_syscampodef values(21746,'3','Jornal');
insert into db_syscampodef values(21746,'4','Mural da Entidade');
insert into db_syscampodef values(21746,'5','Diário Oficial do Município');
insert into db_syscampodef values(21746,'6','Diário Oficial dos Municípios / RS');
insert into db_syscampodef values(21746,'7','Diário');


insert into db_syscampo values(21749,'l47_sequencial','int4','Código sequencial','0', 'Código',10,'f','f','f',1,'text','Código');
insert into db_syscampo values(21750,'l47_liclicitaevento','int4','Eventos da Licitação','0', 'Eventos da Licitação',10,'f','f','f',1,'text','Eventos da Licitação');
insert into db_syscampo values(21751,'l47_arquivo','oid','Identificador do Arquivo','', 'Identificador do Arquivo',1,'f','f','f',1,'text','Identificador do Arquivo');
insert into db_syscampo values(21752,'l47_nomearquivo','varchar(200)','Nome do Arquivo','', 'Nome do Arquivo',200,'f','t','f',0,'text','Nome do Arquivo');
insert into db_syscampo values(21753,'l47_tipodocumento','varchar(3)','Tipo de Documento','', 'Tipo de Documento',3,'f','t','f',0,'text','Tipo de Documento');
insert into db_sysarquivo values (3918, 'liclicitaeventodocumento', 'Documentos Vinculados ao Evento da Licitação', 'l47', '2016-03-01', 'Documentos Vinculados ao Evento da Licitação', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (19,3918);
delete from db_sysarqcamp where codarq = 3918;
insert into db_sysarqcamp values(3918,21749,1,0);
insert into db_sysarqcamp values(3918,21750,2,0);
insert into db_sysarqcamp values(3918,21752,3,0);
insert into db_sysarqcamp values(3918,21751,4,0);
insert into db_sysarqcamp values(3918,21753,5,0);
delete from db_sysprikey where codarq = 3918;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3918,21749,1,21752);
delete from db_sysforkey where codarq = 3918 and referen = 0;
insert into db_sysforkey values(3918,21750,1,3917,0);
insert into db_sysindices values(4330,'liclicitaeventodocumento_liclicitaevento_in',3918,'0');
insert into db_syscadind values(4330,21750,1);
insert into db_syssequencia values(1000554, 'liclicitaeventodocumento_l47_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000554 where codarq = 3918 and codcam = 21749;



-- EVENTOS_LIC
insert into db_layouttxt values (238, 'TCE/RS - LICITACON - EVENTOS_LIC', 0, '', 6 );
insert into db_layoutlinha values (804, 238, 'CABEÇALHO', 1, 0, 0, 0, '', '|', true );
insert into db_layoutlinha values (805, 238, 'REGISTRO', 3, 0, 0, 0, '', '|', true );
insert into db_layoutcampos values (12908, 804, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12909, 804, 'DATA_INICIAL', 'DATA_INICIAL', 1, 15, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12910, 804, 'DATA_FINAL', 'DATA_FINAL', 1, 25, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12911, 804, 'DATA_GERACAO', 'DATA_GERACAO', 1, 35, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12912, 804, 'NOME_SETOR', 'NOME_SETOR', 1, 45, '', 150, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12913, 804, 'TOTAL_REGISTROS', 'TOTAL_REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12914, 805, 'NR_LICITACAO', 'NR_LICITACAO', 1, 1, '', 20, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12915, 805, 'ANO_LICITACAO', 'ANO_LICITACAO', 1, 21, '', 4, false, true, 'e', '', 0 );
insert into db_layoutcampos values (12916, 805, 'CD_TIPO_MODALIDADE', 'CD_TIPO_MODALIDADE', 1, 25, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12917, 805, 'SQ_EVENTO', 'SQ_EVENTO', 1, 28, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12918, 805, 'CD_TIPO_FASE', 'CD_TIPO_FASE', 1, 38, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12919, 805, 'CD_TIPO_EVENTO', 'CD_TIPO_EVENTO', 1, 41, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12920, 805, 'DT_EVENTO', 'DT_EVENTO', 1, 44, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12921, 805, 'TP_VEICULO_PUBLICACAO', 'TP_VEICULO_PUBLICACAO', 1, 54, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12922, 805, 'DS_PUBLICACAO', 'DS_PUBLICACAO', 13, 55, '', 100, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12923, 805, 'TP_DOCUMENTO_AUTOR', 'TP_DOCUMENTO_AUTOR', 1, 155, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12924, 805, 'NR_DOCUMENTO_AUTOR', 'NR_DOCUMENTO_AUTOR', 1, 156, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12925, 805, 'DT_JULGAMENTO', 'DT_JULGAMENTO', 1, 170, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12926, 805, 'TP_RESULTADO', 'TP_RESULTADO', 1, 180, '', 1, false, true, 'd', '', 0 );

-- DOCUMENTO_LIC
insert into db_layouttxt values (239, 'TCE/RS - LICITACON - DOCUMENTO_LIC', 0, '', 6 );
insert into db_layoutlinha values (806, 239, 'CABEÇALHO', 1, 0, 0, 0, '', '|', true );
insert into db_layoutlinha values (807, 239, 'REGISTRO', 3, 0, 0, 0, '', '|', true );
insert into db_layoutcampos values (12927, 806, 'CNPJ', 'CNPJ', 1, 1, '', 14, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12928, 806, 'DATA_INICIAL', 'DATA_INICIAL', 1, 15, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12929, 806, 'DATA_FINAL', 'DATA_FINAL', 1, 25, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12930, 806, 'DATA_GERACAO', 'DATA_GERACAO', 1, 35, '', 10, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12931, 806, 'NOME_SETOR', 'NOME_SETOR', 1, 45, '', 150, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12932, 806, 'TOTAL_REGISTROS', 'TOTAL_REGISTROS', 1, 195, '', 15, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12933, 807, 'NR_LICITACAO', 'NR_LICITACAO', 1, 1, '', 20, false, true, 'e', '', 0 );
insert into db_layoutcampos values (12934, 807, 'ANO_LICITACAO', 'ANO_LICITACAO', 1, 21, '', 4, false, true, 'e', '', 0 );
insert into db_layoutcampos values (12935, 807, 'CD_TIPO_MODALIDADE', 'CD_TIPO_MODALIDADE', 1, 25, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12936, 807, 'CD_TIPO_DOCUMENTO', 'CD_TIPO_DOCUMENTO', 1, 28, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12937, 807, 'NOME_ARQUIVO_DOCUMENTO', 'NOME_ARQUIVO_DOCUMENTO', 13, 31, '', 100, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12938, 807, 'CD_TIPO_FASE', 'CD_TIPO_FASE', 1, 131, '', 3, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12939, 807, 'SQ_EVENTO', 'SQ_EVENTO', 1, 134, '', 10, false, true, 'e', '', 0 );
insert into db_layoutcampos values (12940, 807, 'TP_DOCUMENTO_LICITANTE', 'TP_DOCUMENTO_LICITANTE', 1, 144, '', 1, false, true, 'd', '', 0 );
insert into db_layoutcampos values (12941, 807, 'NR_DOCUMENTO_LICITANTE', 'NR_DOCUMENTO_LICITANTE', 1, 145, '', 14, false, true, 'd', '', 0 );

insert into db_sysarquivo values (3920, 'liclicitaencerramentolicitacon', 'Armazena as licitações que já foram encerradas e enviadas ao LicitaCon.', 'l18', '2016-03-07', 'Encerramento LicitaCon', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (19,3920);
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21758 ,'l18_sequencial' ,'int4' ,'Código sequencial' ,'' ,'Código' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Código' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3920 ,21758 ,1 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21759 ,'l18_liclicita' ,'int4' ,'Código da licitação' ,'' ,'Licitação' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Licitação' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3920 ,21759 ,2 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21760 ,'l18_data' ,'date' ,'Data da geração do arquivo' ,'' ,'Data de Geração' ,10 ,'false' ,'false' ,'false' ,0 ,'text' ,'Data de Geração' );
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3920 ,21760 ,3 ,0 );
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3920,21758,1,21758);
insert into db_sysforkey values(3920,21759,1,1260,0);
insert into db_sysindices values(4331,'liclicitaencerramentolicitacon_sequencial_in',3920,'0');
insert into db_syscadind values(4331,21758,1);
insert into db_syssequencia values(1000555, 'liclicitaencerramentolicitacon_l18_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000555 where codarq = 3920 and codcam = 21758;

---------------------------------------------------------------------------------------------
---------------------------------- FIM FINANCEIRO -----------------------------------------
---------------------------------------------------------------------------------------------


---------------------------------------------------------------------------------------------
------------------------------- INICIO EDUCAÇÃO/SAÚDE ---------------------------------------
---------------------------------------------------------------------------------------------

update db_itensmenu set descricao = 'Atendimentos', help = 'Atendimentos', funcao = 'sau2_atendimentomedico001.php', desctec = 'Relatório de atendimentos' where id_item = 7146;
delete from atendcadareamod where at26_id_item = 10191;
delete from db_modulos where id_item = 10191;
delete from db_itensmenu where id_item = 10191;



insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10210 ,'Cadastro Geral da Saúde' ,'Cadastro Geral da Saúde' ,'sau3_consultacgs001.php' ,'1' ,'1' ,'Consulta os dados do CGS' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 31 ,10210 ,182 ,1000004 );


---------------------------------------------------------------------------------------------
---------------------------------- FIM EDUCAÇÃO/SAÚDE ---------------------------------------
---------------------------------------------------------------------------------------------

---------------------------------------------------------------------------------------------
---------------------------------- INICIO FOLHA ---------------------------------------------
---------------------------------------------------------------------------------------------

insert into db_sysarquivo values (3914, 'cargorhrubricas', 'Rubricas por cargo', 'rh176', '2016-02-25', 'Rubricas por cargo', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (28,3914);
insert into db_sysarquivo values (3915, 'funcaorhrubricas', 'Rubricas por função', 'rh177', '2016-02-25', 'Rubricas por função', 0, 'f', 'f', 'f', 'f' );
insert into db_sysarqmod values (28,3915);
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21729 ,'rh176_sequencial' ,'int4' ,'Sequencial da tabela.' ,'' ,'Sequencial' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Sequencial' );
delete from db_syscampodef where codcam = 21729;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3914 ,21729 ,1 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21730 ,'rh176_cargo' ,'int4' ,'Código do cargo.' ,'' ,'Cargo' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Cargo' );
delete from db_syscampodef where codcam = 21730;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3914 ,21730 ,2 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21731 ,'rh176_rubrica' ,'int4' ,'Código da rubrica.' ,'' ,'Rubrica' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Rubrica' );
delete from db_syscampodef where codcam = 21731;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3914 ,21731 ,3 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21732 ,'rh176_instit' ,'int4' ,'Código da instituição.' ,'' ,'Instituição' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Instituição' );
delete from db_syscampodef where codcam = 21732;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3914 ,21732 ,4 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21733 ,'rh177_sequencial' ,'int4' ,'Sequencial da tabela.' ,'' ,'Sequencial' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Sequencial' );
delete from db_syscampodef where codcam = 21733;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3915 ,21733 ,1 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21734 ,'rh177_funcao' ,'int4' ,'Código da função.' ,'' ,'Função' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Função' );
delete from db_syscampodef where codcam = 21734;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3915 ,21734 ,2 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21735 ,'rh177_rubrica' ,'int4' ,'Código da rubrica.' ,'' ,'Rubrica' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Rubrica' );
delete from db_syscampodef where codcam = 21735;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3915 ,21735 ,3 ,0 );
insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21736 ,'rh177_instit' ,'int4' ,'Código da instituição.' ,'' ,'Instituição' ,10 ,'false' ,'false' ,'false' ,1 ,'text' ,'Instituição' );
delete from db_syscampodef where codcam = 21736;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3915 ,21736 ,4 ,0 );
delete from db_sysprikey where codarq = 3914;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3914,21729,1,21729);
delete from db_sysforkey where codarq = 3914 and referen = 0;
insert into db_sysforkey values(3914,21730,1,1496,0);
insert into db_sysforkey values(3914,21732,2,1496,0);
delete from db_sysforkey where codarq = 3914 and referen = 0;
insert into db_sysforkey values(3914,21731,1,1177,0);
insert into db_sysforkey values(3914,21732,2,1177,0);
delete from db_sysforkey where codarq = 3914 and referen = 0;
insert into db_sysforkey values(3914,21732,1,83,0);
insert into db_syssequencia values(1000550, 'cargorhrubricas_rh176_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000550 where codarq = 3914 and codcam = 21729;
insert into db_sysindices values(4322,'cargorhrubricas_cargo_instit',3914,'0');
insert into db_syscadind values(4322,21730,1);
insert into db_syscadind values(4322,21732,2);
insert into db_sysindices values(4323,'cargorhrubricas_rubrica_instit_in',3914,'0');
insert into db_syscadind values(4323,21731,1);
insert into db_syscadind values(4323,21732,2);
update db_sysindices set nomeind = 'cargorhrubricas_cargo_instit_in',campounico = '0' where codind = 4322;
delete from db_syscadind where codind = 4322;
insert into db_syscadind values(4322,21730,1);
insert into db_syscadind values(4322,21732,2);
insert into db_sysindices values(4324,'cargorhrubricas_instit_in',3914,'0');
insert into db_syscadind values(4324,21732,1);
delete from db_sysprikey where codarq = 3915;
insert into db_sysprikey (codarq,codcam,sequen,camiden) values(3915,21733,1,21733);
delete from db_sysforkey where codarq = 3915 and referen = 0;
insert into db_sysforkey values(3915,21734,1,1174,0);
insert into db_sysforkey values(3915,21736,2,1174,0);
delete from db_sysforkey where codarq = 3915 and referen = 0;
insert into db_sysforkey values(3915,21735,1,1177,0);
insert into db_sysforkey values(3915,21736,2,1177,0);
delete from db_sysforkey where codarq = 3915 and referen = 0;
insert into db_sysforkey values(3915,21736,1,83,0);
insert into db_sysindices values(4325,'funcaorhrubricas_funcao_instit_in',3915,'0');
insert into db_syscadind values(4325,21734,1);
insert into db_syscadind values(4325,21736,2);
insert into db_sysindices values(4326,'funcaorhrubricas_rubrica_instit_in',3915,'0');
insert into db_syscadind values(4326,21735,1);
insert into db_syscadind values(4326,21736,2);
insert into db_sysindices values(4327,'funcaorhrubricas_instit_in',3915,'0');
insert into db_syscadind values(4327,21736,1);
insert into db_syssequencia values(1000551, 'funcaorhrubricas_rh177_sequencial_seq', 1, 1, 9223372036854775807, 1, 1);
update db_sysarqcamp set codsequencia = 1000551 where codarq = 3915 and codcam = 21733;

update db_sysarqcamp set codsequencia = 1000551 where codarq = 3915 and codcam = 21733;
update db_syscampo set nomecam = 'rh176_rubrica', conteudo = 'char(4)', descricao = 'Código da rubrica.', valorinicial = '', rotulo = 'Rubrica', nulo = 'f', tamanho = 4, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Rubrica' where codcam = 21731;
delete from db_syscampodep where codcam = 21731;
delete from db_syscampodef where codcam = 21731;
update db_syscampo set nomecam = 'rh177_rubrica', conteudo = 'char(4)', descricao = 'Código da rubrica.', valorinicial = '', rotulo = 'Rubrica', nulo = 'f', tamanho = 4, maiusculo = 't', autocompl = 'f', aceitatipo = 0, tipoobj = 'text', rotulorel = 'Rubrica' where codcam = 21735;
delete from db_syscampodep where codcam = 21735;
delete from db_syscampodef where codcam = 21735;

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21754 ,'rh176_quantidade' ,'float8' ,'Quantidade para inicializar.' ,'' ,'Quantidade' ,15 ,'false' ,'false' ,'false' ,4 ,'text' ,'Quantidade' );
delete from db_syscampodef where codcam = 21754;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3914 ,21754 ,5 ,0 );

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21755 ,'rh176_valor' ,'float8' ,'Valor para inicializar.' ,'' ,'Valor' ,15 ,'false' ,'false' ,'false' ,4 ,'text' ,'Valor' );
delete from db_syscampodef where codcam = 21755;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3914 ,21755 ,6 ,0 );

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21756 ,'rh177_quantidade' ,'float8' ,'Quantidade para inicializar.' ,'' ,'Quantidade' ,15 ,'false' ,'false' ,'false' ,4 ,'text' ,'Quantidade' );
delete from db_syscampodef where codcam = 21756;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3915 ,21756 ,5 ,0 );

insert into db_syscampo ( codcam ,nomecam ,conteudo ,descricao ,valorinicial ,rotulo ,tamanho ,nulo ,maiusculo ,autocompl ,aceitatipo ,tipoobj ,rotulorel ) values ( 21757 ,'rh177_valor' ,'float8' ,'Valor para inicializar.' ,'' ,'Valor' ,15 ,'false' ,'false' ,'false' ,4 ,'text' ,'Valor' );
delete from db_syscampodef where codcam = 21757;
insert into db_sysarqcamp ( codarq ,codcam ,seqarq ,codsequencia ) values ( 3915 ,21757 ,6 ,0 );

update db_syscampo set nomecam = 'rh176_quantidade', conteudo = 'float8', descricao = 'Quantidade para inicializar.', valorinicial = '0', rotulo = 'Quantidade', nulo = 'f', tamanho = 15, maiusculo = 'f', autocompl = 'f', aceitatipo = 4, tipoobj = 'text', rotulorel = 'Quantidade' where codcam = 21754;
update db_syscampo set nomecam = 'rh176_valor', conteudo = 'float8', descricao = 'Valor para inicializar.', valorinicial = '0', rotulo = 'Valor', nulo = 'f', tamanho = 15, maiusculo = 'f', autocompl = 'f', aceitatipo = 4, tipoobj = 'text', rotulorel = 'Valor' where codcam = 21755;
update db_syscampo set nomecam = 'rh177_quantidade', conteudo = 'float8', descricao = 'Quantidade para inicializar.', valorinicial = '0', rotulo = 'Quantidade', nulo = 'f', tamanho = 15, maiusculo = 'f', autocompl = 'f', aceitatipo = 4, tipoobj = 'text', rotulorel = 'Quantidade' where codcam = 21756;
update db_syscampo set nomecam = 'rh177_valor', conteudo = 'float8', descricao = 'Valor para inicializar.', valorinicial = '0', rotulo = 'Valor', nulo = 'f', tamanho = 15, maiusculo = 'f', autocompl = 'f', aceitatipo = 4, tipoobj = 'text', rotulorel = 'Valor' where codcam = 21757;

insert into db_itensmenu ( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 10211 ,'Rubricas por Função / Cargo' ,'Rubricas por Função / Cargo' ,'pes1_rubricasfuncaocargo001.php' ,'1' ,'1' ,'Rubricas por Função / Cargo' ,'true' );
insert into db_menu ( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 4374 ,10211 ,24 ,952 );

---------------------------------------------------------------------------------------------
---------------------------------- FIM FOLHA ------------------------------------------------
---------------------------------------------------------------------------------------------

SQL_PRE;

        $ddl = <<<'SQL_DDL'

---------------------------------------------------------------------------------------------
---------------------------------- INICIO FINANCEIRO -----------------------------------------
---------------------------------------------------------------------------------------------

alter table liccomissao add column l30_nomearquivo varchar(100) default null;
alter table liccomissao add column l30_arquivo oid default null;

CREATE SEQUENCE db_cadattdinamicosysarquivo_db17_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

create table db_cadattdinamicosysarquivo (
  db17_sequencial int4 not null primary key default nextval('db_cadattdinamicosysarquivo_db17_sequencial_seq'),
  db17_sysarquivo integer not null,
  db17_cadattdinamico integer not null,
  constraint db_cadattdinamicosysarquivo_cadattdinamico_fk foreign key (db17_cadattdinamico) references db_cadattdinamico,
  constraint db_cadattdinamicosysarquivo_sysarquivo_fk foreign key (db17_sysarquivo) references db_sysarquivo
);

create unique index db_cadattdinamicosysarquivo_sysarquivo_un on db_cadattdinamicosysarquivo(db17_sysarquivo);

CREATE SEQUENCE liccomissaocgmcadattdinamicovalorgrupo_l15_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

create table liccomissaocgmcadattdinamicovalorgrupo(
  l15_sequencial int4 not null primary key default nextval('liccomissaocgmcadattdinamicovalorgrupo_l15_sequencial_seq'),
  l15_liccomissaocgm integer not null,
  l15_cadattdinamicovalorgrupo integer not null,
  constraint liccomissaocgmcadattdinamicovalorgrupo_liccomissaocgm_fk foreign key (l15_liccomissaocgm) references liccomissaocgm,
  constraint liccomissaocgmcadattdinamicovalorgrupo_cadattdinamicovalorgrupo_fk foreign key (l15_cadattdinamicovalorgrupo) references db_cadattdinamicovalorgrupo
);

alter table db_cadattdinamicoatributos add column db109_nome character varying(100);
alter table db_cadattdinamicoatributosvalor alter column db110_valor type text;

CREATE SEQUENCE db_cadattdinamicoatributosopcoes_db18_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

create table db_cadattdinamicoatributosopcoes (
  db18_sequencial int4 not null primary key default nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'),
  db18_cadattdinamicoatributos integer not null,
  db18_opcao character varying(50),
  db18_valor character varying(200),
  constraint db_cadattdinamicoatributosopcoes_cadattdinamicoatributos_fk foreign key (db18_cadattdinamicoatributos) references db_cadattdinamicoatributos
);

insert into db_cadattdinamico values (nextval('db_cadattdinamico_db118_sequencial_seq'), 'Atributos dos membros da comissão');
insert into db_cadattdinamicosysarquivo values (nextval('db_cadattdinamicosysarquivo_db17_sequencial_seq'), 1325, currval('db_cadattdinamico_db118_sequencial_seq'));
insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Cargo', '', 1, 'cargo');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Tipo de Cargo', '', 1, 'tipocargo');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A', 'Agente Político'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'C', 'Comissionado'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'E', 'Efetivo'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'O', 'Outros'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'P', 'Empregado Público'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'T', 'Empregado Temporário');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Número do Ato de Designação', '', 2, 'numeroatodesignacao'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Ano do Ato de Designação', '', 2, 'anoatodesignacao'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Data de Designação', '', 3, 'datadesignacao');


-- tipo compra tribunal
alter table pctipocompratribunal add column l44_sigla character varying(3);

select fc_executa_ddl('
insert into pctipocompratribunal (l44_sequencial, l44_codigotribunal, l44_descricao, l44_uf) values
  (48, \'09\', \'Concurso\', \'RS\'),
  (49, \'10\', \'RDC\', \'RS\'),
  (50, \'11\', \'Registro de Preço de Outro Orgão\', \'RS\'),
  (51, \'12\', \'Processo de Dispensa por Pequeno Valor\', \'RS\'),
  (52, \'13\', \'Processo de Dispensa (exceto pequeno valor)\', \'RS\'),
  (53, \'14\', \'Chamamento Público\', \'RS\');
');

select fc_executa_ddl('
insert into pctipocompratribunal values
  (54, \'14\', \'Chamamento Público/Credenciamento\', \'RS\', \'CPC\'),
  (55, \'99\', \'Leilão\', \'RS\', \'LEI\'),
  (56, \'99\', \'Manifestação de Interesse\', \'RS\', \'MAI\');
');

update pctipocompratribunal
   set l44_sigla = case l44_codigotribunal
                        when '14' then 'CHP'
                        when '05' then 'CNC'
                        when '09' then 'CNS'
                        when '03' then 'CNV'
                        when '01' then 'PRD'
                        when '12' then 'PRD'
                        when '13' then 'PRD'
                        when '07' then 'PRE'
                        when '02' then 'PRI'
                        when '06' then 'PRP'
                        when '10' then 'RDC'
                        when '08' then 'RIN'
                        when '11' then 'RPO'
                        when '04' then 'TMP'
                        else l44_sigla
                   end
 where l44_uf = 'RS';

insert into db_cadattdinamico values (nextval('db_cadattdinamico_db118_sequencial_seq'), 'Atributos da licitação');

insert into db_cadattdinamicosysarquivo values (nextval('db_cadattdinamicosysarquivo_db17_sequencial_seq'), 1260, currval('db_cadattdinamico_db118_sequencial_seq'));

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Tipo de Objeto', '', 1, 'tipoobjeto');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'ALB', 'Alienação de Bens'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'COM', 'Compras'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'CON', 'Concessão'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'CSE', 'Compras e Outros Serviços'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'LOC', 'Locações'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'NSA', 'Não se Aplica'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'OSE', 'Obras e Serviçoes de Engenharia'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'OUS', 'Outros Serviços'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'PER', 'Permissão');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Tipo de Licitação', '', 1, 'tipolicitacao');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MCA', 'Melhor Conteúdo Artístico'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MDE', 'Maior Desconto'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MLO', 'Maior Lance ou Oferta'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MOO', 'Maior Oferta de Outorga'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MOP', 'Maior Oferta de Preço'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MOQ', 'Maior Oferta de Outorga após Qualificação das Propostas Técnicas'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MOT', 'Maior Oferta de Outorga e Melhor Técnica'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MPP', 'Melhor Proposta Técnica com Preço fixado no Edital'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MPR', 'Menor Preço'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MRE', 'Maior Retorno Econômico'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MTC', 'Melhor Técnica'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MTO', 'Menor Valor da Tarifa e Maior Oferta de Outorga'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MTT', 'Menor Valor da Tarifa e Melhor Técnica'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'MVT', 'Menor Valor da Tarifa'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'NSA', 'Não se aplica'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'TPR', 'Técnica e Preço');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Característica do Objeto', '', 1, 'caracteristicaobjeto');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'IT', 'Itens'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'IU', 'Item Único'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'LT', 'Lotes'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'LU', 'Lote Único');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Regime de Execução', '', 1, 'regimeexecucao');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'C', 'Contratação Integrada'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'G', 'Empreitada por Preço Global'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'I', 'Empreitada Integral'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'T', 'Tarefa'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'U', 'Empreitada por Preço Unitário');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Permite Subcontratação', '', 5, 'permitesubcontratacao');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Tipo de Beneficio à Microempresa e Empresa de Pequeno Porte', '', 1, 'tipobeneficiomicroepp');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'L', 'Licitação exclusiva'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'N', 'Não se aplica'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'T', 'Tratamento diferenciado/simplificado');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Tipo de Fornecimento', '', 1, 'tipofornecimento');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'I', 'Integral'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'P', 'Parcelado');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Percentual de Taxa de Risco', '', 4, 'pctaxarisco');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Tipo de Execução', '', 1, 'tipoexecucao');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'E', 'Eletrônica'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'P', 'Presencial');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Tipo de Disputa', '', 1, 'tipodisputa');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A', 'Aberto'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'C', 'Combinado'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'F', 'Fechado');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Pré-Qualificação', '', 1, 'prequalificacao');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'E', 'Específica'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'G', 'Geral'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'N', 'Não Realizada');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Inversão de fases', '', 5, 'inversaofases');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Fundamentação', '', 1, 'codigofundamentacao');

insert into db_cadattdinamicoatributosopcoes values
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24IV', 'Art. 24, inc. IV, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24V', 'Art. 24, inc. V, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24VII', 'Art. 24, inc. VII, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24VIII', 'Art. 24, inc. VIII, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24X', 'Art. 24, inc. X, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24XI', 'Art. 24, inc. XI, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24XII', 'Art. 24, inc. XII, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24XIII', 'Art. 24, inc. XIII, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24XVI', 'Art. 24, inc. XVI, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24XX', 'Art. 24, inc. XX, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A24XXII', 'Art. 24, inc. XXII, da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A25CAPT', 'Art. 25, "caput", da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A25I', 'Art. 25, "inc. I", da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A25II', 'Art. 25, "inc. II", da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'A25III', 'Art. 25, "inc. III", da Lei no 8.666/93'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'OUTD', 'Outra(Processo de Dispensa)'),
  (nextval('db_cadattdinamicoatributosopcoes_db18_sequencial_seq'), currval('db_cadattdinamicoatributos_db109_sequencial_seq'), 'OUTI', 'Outra(Processo de Inexigibilidade)');

insert into db_cadattdinamicoatributos values
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Número do Artigo', '', 2, 'numeroartigo'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Inciso', '', 1, 'inciso'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Lei', '', 1, 'lei'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Data de Início de Inscrição', '', 3, 'datainicioinscricaocredenciamento'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Data de Fim de Inscrição', '', 3, 'datafiminscricaocredenciamento'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Data de Início da Vigência', '', 3, 'datainiciovigenciacredenciamento'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Data de Fim da Vigência', '', 3, 'datafimvigenciacredenciamento'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Recebe Inscrição no Período de Vigência', '', 5, 'recebeinscricaoperiodovigencia'),
  (nextval('db_cadattdinamicoatributos_db109_sequencial_seq'), currval('db_cadattdinamico_db118_sequencial_seq'), null, 'Permite a Participação de Consórcio', '', 5, 'permiteconsorcio');

CREATE SEQUENCE liclicitacadattdinamicovalorgrupo_l16_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

create table liclicitacadattdinamicovalorgrupo(
  l16_sequencial int4 primary key default nextval('liclicitacadattdinamicovalorgrupo_l16_sequencial_seq'),
  l16_cadattdinamicovalorgrupo integer not null,
  l16_liclicita integer not null,
  constraint liclicitacadattdinamicovalorgrupo_cadattdinamicovalorgrupo_fk foreign key (l16_cadattdinamicovalorgrupo) references db_cadattdinamicovalorgrupo,
  constraint liclicitacadattdinamicovalorgrupo_liclicita_fk foreign key (l16_liclicita) references liclicita
);

-- Estrutura Tabela Habilitação de Fornecedores
CREATE SEQUENCE pcorcamfornelichabilitacao_l17_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE pcorcamfornelichabilitacao(
  l17_sequencial    int4 NOT NULL  default nextval('pcorcamfornelichabilitacao_l17_sequencial_seq'),
  l17_pcorcamfornelic    int4 NOT NULL ,
  l17_situacao    int4 ,
CONSTRAINT pcorcamfornelichabilitacao_sequ_pk PRIMARY KEY (l17_sequencial));

ALTER TABLE pcorcamfornelichabilitacao
ADD CONSTRAINT pcorcamfornelichabilitacao_pcorcamfornelic_fk FOREIGN KEY (l17_pcorcamfornelic)
REFERENCES pcorcamfornelic;

-- Campo Tipo de Condição - Tabela de Fornecedores da Liquidação
alter table pcorcamfornelic add column pc31_tipocondicao integer default null;


CREATE SEQUENCE liclicitatipoevento_l45_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE liclicitatipoevento(
l45_sequencial  int4 NOT NULL default nextval('liclicitatipoevento_l45_sequencial_seq'),
l45_descricao   varchar(200) ,
CONSTRAINT liclicitatipoevento_sequ_pk PRIMARY KEY (l45_sequencial));
CREATE  INDEX liclicitatipoevento_sequencial_in ON liclicitatipoevento(l45_sequencial);

insert into liclicitatipoevento
     values (1, upper('Alteração do edital')),
            (2, upper('Anulação por determinação judicial')),
            (3, upper('Anulação de ofício')),
            (4, upper('Encerramento por falta de propostas classificadas')),
            (5, upper('Encerramento por falta de licitantes habilitados')),
            (6, upper('Encerramento por falta de interessados')),
            (7, upper('Encerramento')),
            (8, upper('Esclarecimento')),
            (9, upper('Impugnação do edital')),
            (10, upper('Publicação')),
            (11, upper('Publicação do edital')),
            (12, upper('Recurso de credenciamento/lances')),
            (13, upper('Republicação do edital')),
            (14, upper('Reinício')),
            (15, upper('Revogação de ofício')),
            (16, upper('Recurso da habilitação')),
            (17, upper('Recurso de habilitação/proposta')),
            (18, upper('Recurso da proposta/projeto')),
            (19, upper('Recurso da proposta')),
            (20, upper('Suspensão por determinação judicial')),
            (21, upper('Suspensão por medida cautelar')),
            (22, upper('Suspensão de ofício')),
            (23, upper('Não informado'));
select setval('liclicitatipoevento_l45_sequencial_seq', 23);


CREATE SEQUENCE liclicitaevento_l46_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE liclicitaevento(
l46_sequencial     int4 NOT NULL default nextval('liclicitaevento_l46_sequencial_seq'),
l46_liclicita             int4 NOT NULL default 0,
l46_fase                  int4 NOT NULL default 0,
l46_liclicitatipoevento   int4 NOT NULL default 0,
l46_dataevento            date default null,
l46_datajulgamento        date  default null,
l46_cgm             int4  default null,
l46_tipopublicacao  int4  default null,
l46_descricaopublicacao text  ,
l46_tiporesultado  int4 default 0,
CONSTRAINT liclicitaevento_sequencial_pk PRIMARY KEY (l46_sequencial));

ALTER TABLE liclicitaevento
ADD CONSTRAINT liclicitaevento_liclicita_fk FOREIGN KEY (l46_liclicita)
REFERENCES liclicita;

ALTER TABLE liclicitaevento
ADD CONSTRAINT liclicitaevento_cgm_fk FOREIGN KEY (l46_cgm)
REFERENCES cgm;

ALTER TABLE liclicitaevento
ADD CONSTRAINT liclicitaevento_liclicitatipoevento_fk FOREIGN KEY (l46_liclicitatipoevento)
REFERENCES liclicitatipoevento;

CREATE  INDEX liclicitaevento_liclicita_in ON liclicitaevento(l46_liclicita);

insert into liclicitaevento select nextval('liclicitaevento_l46_sequencial_seq'), l20_codigo, 1, 7, '2016-01-01', null, null, null, '', 1 from liclicita;


CREATE SEQUENCE liclicitaeventodocumento_l47_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE liclicitaeventodocumento(
l47_sequencial       int4 NOT NULL default nextval('liclicitaeventodocumento_l47_sequencial_seq'),
l47_liclicitaevento  int4 NOT NULL default 0,
l47_nomearquivo      varchar(200) NOT NULL ,
l47_arquivo          oid NOT NULL ,
l47_tipodocumento    varchar(3) NOT NULL,
CONSTRAINT liclicitaeventodocumento_sequ_pk PRIMARY KEY (l47_sequencial));

ALTER TABLE liclicitaeventodocumento ADD CONSTRAINT liclicitaeventodocumento_liclicitaevento_fk FOREIGN KEY (l47_liclicitaevento) REFERENCES liclicitaevento;
CREATE  INDEX liclicitaeventodocumento_liclicitaevento_in ON liclicitaeventodocumento(l47_liclicitaevento);

create sequence liclicitaencerramentolicitacon_l18_sequencial_seq
increment 1
minvalue 1
maxvalue 9223372036854775807
start 1
cache 1;

create table liclicitaencerramentolicitacon(
l18_sequencial    int4 not null  default nextval('liclicitaencerramentolicitacon_l18_sequencial_seq'),
l18_liclicita   int4 not null ,
l18_data    date ,
constraint liclicitaencerramentolicitacon_sequ_pk primary key (l18_sequencial));

alter table liclicitaencerramentolicitacon
add constraint liclicitaencerramentolicitacon_liclicita_fk foreign key (l18_liclicita)
references liclicita;

create  index liclicitaencerramentolicitacon_sequencial_in on liclicitaencerramentolicitacon(l18_sequencial);

---------------------------------------------------------------------------------------------
---------------------------------- FIM FINANCEIRO -----------------------------------------
---------------------------------------------------------------------------------------------




---------------------------------------------------------------------------------------------
---------------------------------- INICIO CONFIGURACAO --------------------------------------
---------------------------------------------------------------------------------------------

select fc_executa_ddl('DROP INDEX IF EXISTS '||oid::regclass::text||';')
  from pg_class
 where relkind = 'i'
   and relname ~ '^db_logsacessa_'
   and (relname ~ 'data_in$' or relname ~ 'instit_in$');

select fc_executa_ddl('CREATE INDEX '||relname::text||'_instit_data_in ON '||oid::regclass::text||' USING btree (instit, data);')
  from pg_class
 where relkind = 'r'
   and (relname ~ '^db_logsacessa_[0-9]' or relname = 'db_logsacessa')
 order by relname desc;

---------------------------------------------------------------------------------------------
---------------------------------- FIM CONFIGURACAO -----------------------------------------
---------------------------------------------------------------------------------------------



---------------------------------------------------------------------------------------------
---------------------------------- INICIO FOLHA ---------------------------------------------
---------------------------------------------------------------------------------------------

CREATE SEQUENCE cargorhrubricas_rh176_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;


CREATE SEQUENCE funcaorhrubricas_rh177_sequencial_seq
INCREMENT 1
MINVALUE 1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

CREATE TABLE cargorhrubricas(
  rh176_sequencial      int4 NOT NULL  default nextval('cargorhrubricas_rh176_sequencial_seq'),
  rh176_cargo       int4 NOT NULL ,
  rh176_rubrica     char(4) NOT NULL ,
  rh176_instit      int4 ,
  rh176_quantidade      float8 NOT NULL default 0,
  rh176_valor       float8 default 0,
  CONSTRAINT cargorhrubricas_sequ_pk PRIMARY KEY (rh176_sequencial)
);


CREATE TABLE funcaorhrubricas(
  rh177_sequencial      int4 NOT NULL  default nextval('funcaorhrubricas_rh177_sequencial_seq'),
  rh177_funcao      int4 NOT NULL ,
  rh177_rubrica     char(4) NOT NULL ,
  rh177_instit      int4 ,
  rh177_quantidade      float8 NOT NULL default 0,
  rh177_valor       float8 default 0,
  CONSTRAINT funcaorhrubricas_sequ_pk PRIMARY KEY (rh177_sequencial)
);

ALTER TABLE cargorhrubricas
ADD CONSTRAINT cargorhrubricas_instit_fk FOREIGN KEY (rh176_instit)
REFERENCES db_config;

ALTER TABLE cargorhrubricas
ADD CONSTRAINT cargorhrubricas_rubrica_instit_fk FOREIGN KEY (rh176_rubrica,rh176_instit)
REFERENCES rhrubricas;

ALTER TABLE cargorhrubricas
ADD CONSTRAINT cargorhrubricas_cargo_instit_fk FOREIGN KEY (rh176_cargo,rh176_instit)
REFERENCES rhcargo;

ALTER TABLE funcaorhrubricas
ADD CONSTRAINT funcaorhrubricas_instit_fk FOREIGN KEY (rh177_instit)
REFERENCES db_config;

ALTER TABLE funcaorhrubricas
ADD CONSTRAINT funcaorhrubricas_rubrica_instit_fk FOREIGN KEY (rh177_rubrica,rh177_instit)
REFERENCES rhrubricas;

ALTER TABLE funcaorhrubricas
ADD CONSTRAINT funcaorhrubricas_funcao_instit_fk FOREIGN KEY (rh177_funcao,rh177_instit)
REFERENCES rhfuncao;

CREATE  INDEX cargorhrubricas_instit_in ON cargorhrubricas(rh176_instit);
CREATE  INDEX cargorhrubricas_cargo_instit_in ON cargorhrubricas(rh176_cargo,rh176_instit);
CREATE  INDEX cargorhrubricas_rubrica_instit_in ON cargorhrubricas(rh176_rubrica,rh176_instit);

CREATE  INDEX funcaorhrubricas_instit_in ON funcaorhrubricas(rh177_instit);
CREATE  INDEX funcaorhrubricas_rubrica_instit_in ON funcaorhrubricas(rh177_rubrica,rh177_instit);
CREATE  INDEX funcaorhrubricas_funcao_instit_in ON funcaorhrubricas(rh177_funcao,rh177_instit);

---------------------------------------------------------------------------------------------
---------------------------------- FIM FOLHA ------------------------------------------------
---------------------------------------------------------------------------------------------


SQL_DDL;

        $pos = <<<'SQL_POS'
insert into db_versao (db30_codver, db30_codversao, db30_codrelease, db30_data, db30_obs)  values (364, 3, 48, '2016-03-10', 'Tarefas: 99418, 99419, 99422, 99423, 99424, 99425, 99428, 99429, 99430, 99431, 99432, 99433, 99434, 99435, 99436, 99437, 99438, 99439, 99441, 99442, 99443, 99445, 99446, 99447, 99449, 99450, 99453, 99454, 99455, 99456, 99457, 99458, 99459, 99460, 99465, 99466, 99467, 99468, 99469, 99470, 99471, 99472, 99473, 99475, 99476, 99477, 99478, 99479, 99480, 99481, 99483, 99484, 99485, 99486, 99487, 99488, 99489, 99490, 99491, 99492, 99493, 99494, 99495, 99496, 99497, 99500, 99501, 99502, 99503, 99505, 99506, 99507, 99508, 99509, 99510, 99511, 99512, 99513, 99514, 99517, 99518, 99519, 99520, 99521, 99523, 99526, 99527, 99528, 99529, 99530, 99531, 99532, 99534, 99535, 99537, 99539');create or replace function fc_iptu_taxacoletalixo_taquari_2016(integer,numeric,integer,numeric,numeric,boolean) returns boolean as
$$
declare

  iReceita             alias for $1;
  iAliquota            alias for $2;
  iHistoricoCalculo    alias for $3;
  iPercentualIsencao   alias for $4;
  nValorParcela        alias for $5;
  lRaise               alias for $6;

  bPredial             boolean default false;

  iAnousu              integer default 0;
  iIdbql               integer default 0;
  iMatricula           integer default 0;
  iCaracteristica      integer default 0;
  iAnoCorrecao         integer default 0;
  iAnoInicioCorrecao   integer default 2016; --Exercício de início da aplicação da regra de correção

  nValorCorrecao       numeric(15,4) default 0;
  nValorTaxa           numeric(15,2) default 0;
  nValorTaxaComIsencao numeric(15,2) default 0;

  rConstrucoes         record;

  begin

    lRaise := true;
    perform fc_debug('CALCULANDO TAXA DE COLETA DE LIXO ...',lRaise,false,false);
    perform fc_debug('receita - '||iReceita||' aliq - '||iAliquota||' historico - '||iHistoricoCalculo,lRaise,false,false);

    select anousu,
           idbql,
           matric
      into iAnousu,
           iIdbql,
           iMatricula
      from tmpdadostaxa;

    if not found then
      return false;
    end if;

    select predial
      into bPredial
      from tmpdadosiptu;

    if bPredial is true then

      for rConstrucoes in select iptuconstr.j39_idcons,
                                 iptuconstr.j39_area,
                                 iptuconstr.j39_areap
                            from iptuconstr
                           where iptuconstr.j39_matric = iMatricula
                             and j39_dtdemo is null
                             and j39_idprinc = true loop

        perform fc_debug('receita - '||iReceita,lRaise);

        select j48_caract
          into iCaracteristica
          from carconstr
               inner join caracter on j48_caract = j31_codigo
               inner join cargrup  on j31_grupo = j32_grupo
         where j48_matric = iMatricula
           and j48_idcons = rConstrucoes.j39_idcons
           and j32_grupo  = 19;

        if not found then
          --@todo retornar erro para quando nao encontrar caracteristica
          return false;
        end if;

        nValorTaxa = 71.15;

        --residencial
        if iCaracteristica = 30274 then
          nValorTaxa = 59.31;
        end if;

        --industrial
        if iCaracteristica = 30278 then
          nValorTaxa = 83.01;
        end if;

        select j18_perccorrepadrao
          into nValorCorrecao
          from cfiptu
         where j18_anousu = iAnousu;
        -- Aplicamos a correção ao valor conforme o passar do anos, partindo do exercício de 2015, cujo quando foi iniciada a regra de correção

        for iAnoCorrecao in iAnoInicioCorrecao..iAnousu loop
          nValorTaxa := nValorTaxa + (nValorTaxa * nValorCorrecao / 100);
        end loop;

        perform fc_debug('Caracteristica: '||iCaracteristica,lRaise);
        perform fc_debug('nValorTaxa: '||nValorTaxa,lRaise);

        insert into tmptaxapercisen values (iReceita,iPercentualIsencao,0,nValorTaxa);

        nValorTaxaComIsencao := nValorTaxa;

        if iPercentualIsencao > 0 then
          nValorTaxaComIsencao := nValorTaxa * (100 - iPercentualIsencao) / 100;
        end if;

        perform fc_debug('nValorTaxaComIsencao: '||nValorTaxaComIsencao,lRaise);
        perform fc_debug('iPercentualIsencao: '||iPercentualIsencao,lRaise);

        insert into tmprecval values (iReceita,nValorTaxaComIsencao,iHistoricoCalculo,true);

      end loop;

    end if;

    return true;

  end;
$$ language 'plpgsql';create or replace function fc_vistorias(integer) returns varchar(200)
as $$
DECLARE

  iCodigoVistoria             alias for $1;

  V_ATIVTIPO                  integer;
  iNumpreGerado               integer;
  iNumpreArreinscr            integer;
  iNumpreArrecant             integer;
  iNumpreArrecad              integer;
  V_DATA                      date;

  V_DIASGERAL                 integer;
  V_MESGERAL                  integer;
  V_PARCIAL                   boolean;

  V_ACHOU                     boolean default false;
  lCalculou                   boolean default false;
  V_DATAVENC                  date;
  V_Y74_CODSANI               integer;
  V_Y74_INSCRSANI             integer;
  V_Y80_NUMCGM                integer;
  V_Y69_NUMPRE                integer;
  iCodigoReceitaExercicio     integer;
  iCodigoHistoricoCalculo     integer;
  nValorExercicio             float8;
  iCodigoArretipo             integer;
  V_Y71_INSCR                 integer;
  V_Q02_NUMCGM                integer;
  V_ATIV                      integer;
  iAnousu                     integer;
  iCodigoAtividade            integer;
  V_DIASVENC                  integer;
  iFormaCalculo               integer;
  iCodVencimento              integer default 0;

  lIsSanitario                boolean;
  V_INSCR                     boolean;
  V_AREA                      float8;
  nValorInflator              float8;
  nValorBase                  float8;
  nValorVistoria              float8;
  sSql                        text default '';
  nValorExcedente             float8;
  iQuantidadeAtividades       integer default 0;

  iFormulaCalculoVistoria     integer;

  nPontuacaoClasse            integer default NULL;
  iPontuacaoPorZonaFiscal     integer;
  iPontuacaoPorEmpregadosArea integer;
  iPontuacaoPorEmpregados     integer;
  iPontuacaoPorArea           integer;
  iPontuacaoGeral             integer;

  v_tipo_quant                integer;

  iInstit                     integer;
  iCodCli                     integer;

  dDataVistoria               date;
  dDataAtual                  date;

  rAtivtipo                   record;
  rSaniAtividade              record;
  rFinanceiro                 record;

  lRaise                      boolean default true;

  lCalculaVistoriaMei         boolean default true;
  lContribuinteMei            boolean default false;

  nQuantidadeAtividade        float8 default 1; -- Quantidade informada no campo q07_quant da tabativ
  nMultiplicadorIssQuantidade float8 default 1; -- Quantidade informada no campo q30_multi da issquant

  BEGIN

    /**
     * Verifica pl por Cliente Especifico
     *
     *  14    (CHARQUEADAS)
     *  19985 (MARICA)
     *  50    (CANELA)
     *  74    (ARARUAMA)
     */
    select db21_codcli
      into iCodCli
      from db_config
     where prefeitura is true;

    if iCodCli = 14 or iCodCli = 19985 or iCodCli = 50 or iCodCli = 74 then
      return fc_vistorias_charqueadas(iCodigoVistoria);
    end if;

    lRaise     := ( case when fc_getsession('DB_debugon') is null then false else true end );
    iInstit    := fc_getsession('DB_instit');
    dDataAtual := fc_getsession('DB_datausu');


    /**
     * 3 = VISTORIA LOCALIZACAO
     * 5 = TAXA
     * 6 = VISTORIA SANITARIO
     */
    begin

      if iCodCli = 4 then

        create temp table w_tipos_localizacao as select 3 as codigo;
        create temp table w_tipos_sanitario   as select 6 as codigo;
      else

        create temp table w_tipos_localizacao as select 3 as codigo union select 5;
        create temp table w_tipos_sanitario   as select 5 as codigo union select 6;
      end if;
    exception

         when duplicate_table then

           truncate w_tipos_localizacao;
           truncate w_tipos_sanitario;
           if iCodCli = 4 then

             insert into w_tipos_localizacao values (3);
             insert into w_tipos_sanitario   values (6);
           else

             insert into w_tipos_localizacao values (3),(5);
             insert into w_tipos_sanitario   values (5),(6);
           end if;
    end;

    select extract(year from y70_data), y70_data
      into iAnousu, dDataVistoria
      from vistorias
     where y70_codvist = iCodigoVistoria;

    /**
     *  Verifica se a vistoria eh parcial ou geral, para montar a data de vencimento a ser gravada no arrecad
     */
    select y70_parcial
      into V_PARCIAL
      from vistorias
     where y70_codvist = iCodigoVistoria;

    if V_PARCIAL is not null AND V_PARCIAL = false then

      perform fc_debug('<fc_vistorias> GERAL', lRaise);

      select y77_diasgeral, y77_mesgeral, y70_data
        into  V_DIASGERAL,V_MESGERAL,V_DATA
        from tipovistorias
             inner join vistorias on y77_codtipo = y70_tipovist
       where y70_codvist = iCodigoVistoria;


      if V_DIASGERAL is null OR
         V_DIASGERAL = 0     OR
         V_MESGERAL is null  OR
         V_MESGERAL = 0      then

        return '01- TIPO DE VISTORIA SEM DIA OU MES PARA VENCIMENTO CONFIGURADO!';
      end if;

      V_DATAVENC = iAnousu||'-'||V_MESGERAL||'-'||V_DIASGERAL;

    else

      perform fc_debug('<fc_vistorias> PARCIAL', lRaise);

      select y77_dias, y70_data, y70_data, y77_diasgeral, y77_mesgeral
        into V_DIASVENC, V_DATA, V_DATAVENC, V_DIASGERAL, V_MESGERAL
        from tipovistorias
             inner join vistorias on y77_codtipo = y70_tipovist
       where y70_codvist = iCodigoVistoria;

      if V_DIASVENC is null OR V_DIASVENC = 0 then

        if V_DIASGERAL is null then
          return '02- TIPO DE VISTORIA SEM DIAS PARA VENCIMENTO CONFIGURADO!';
        else
          V_DATAVENC = iAnousu||'-'||V_MESGERAL||'-'||V_DIASGERAL;
        end if;
      end if;

      perform fc_debug('<fc_vistorias> V_DIASVENC: ' || V_DIASVENC || ' V_DATAVENC: ' || V_DATAVENC, lRaise);

      /**
       * V_DATA = V_DATAVENC;
       */
      if V_DIASVENC is null then
        V_DIASVENC = 0;
      end if;

      select V_DATAVENC + V_DIASVENC
        into V_DATAVENC;

    end if;

    perform fc_debug('<fc_vistorias> V_DATAVENC: ' || V_DATAVENC, lRaise);

    select y32_formvist,
           y32_calculavistoriamei
      into iFormulaCalculoVistoria,
           lCalculaVistoriaMei
      from parfiscal
     where y32_instit = iInstit ;

    select q04_vbase
      into nValorBase
      from cissqn
     where cissqn.q04_anousu = iAnousu;

    if nValorBase = 0 OR nValorBase is null then
      return '03- SEM VALOR BASE CADASTRADO NOS PARAMETROS ';
    end if;

    select distinct i02_valor
      into nValorInflator
      from cissqn
           inner join infla on q04_inflat = i02_codigo
      where cissqn.q04_anousu       = iAnousu
        and date_part('y',i02_data) = iAnousu;

    perform fc_debug('<fc_vistorias> Inflator: ' || nValorInflator, lRaise);

    if nValorInflator is null then
      nValorInflator = 1;
    end if;

    select y74_codsani,y80_numcgm,y69_numpre
      into V_Y74_CODSANI, V_Y80_NUMCGM, V_Y69_NUMPRE
      from vistsanitario
           inner join sanitario      on y74_codsani = y80_codsani
           left  join vistorianumpre on y69_codvist = iCodigoVistoria
     where Y74_CODVIST = iCodigoVistoria;

    perform fc_debug('<fc_vistorias> V_Y74_CODSANI: ' || V_Y74_CODSANI || ' iCodigoVistoria: ' || iCodigoVistoria, lRaise);

    if V_Y74_CODSANI = 0 OR V_Y74_CODSANI is null then

      lIsSanitario = false;
      select y71_inscr,q02_numcgm,y69_numpre
        into V_Y71_INSCR,V_Q02_NUMCGM,V_Y69_NUMPRE
        from vistinscr
             inner join issbase        on q02_inscr   = y71_inscr
             left  join vistorianumpre on y69_codvist = iCodigoVistoria
      where Y71_CODVIST = iCodigoVistoria;

      if V_Y71_INSCR is null then
        V_INSCR = false;
      else
        V_INSCR = true;
      end if;

    else
      lIsSanitario = true;
    end if;

    perform fc_debug('<fc_vistorias> lIsSanitario: ' || lIsSanitario || ' iFormulaCalculoVistoria: ' || iFormulaCalculoVistoria, lRaise);

    if iFormulaCalculoVistoria = 1 then

      if lIsSanitario = true OR V_INSCR = true then

        if lIsSanitario is true then

          V_ACHOU = false;
          perform fc_debug('<fc_vistorias> V_Y74_CODSANI: ' || V_Y74_CODSANI, lRaise);

          select min(q85_forcal)
            into iFormaCalculo
            from tipcalc
                 inner join cadcalc       on tipcalc.q81_cadcalc    = cadcalc.q85_codigo
                 inner join ativtipo      on ativtipo.q80_tipcal    = tipcalc.q81_codigo
                 inner join saniatividade on saniatividade.y83_ativ = ativtipo.q80_ativ
           where saniatividade.y83_codsani = V_Y74_CODSANI
             and saniatividade.y83_dtfim is null
             and tipcalc.q81_tipo in ( select codigo from w_tipos_sanitario );

          if iFormaCalculo is null then
            return '11-SEM FORMA DE CALCULO ENCONTRADA (SANI)!';
          end if;

          perform fc_debug('<fc_vistorias> iFormaCalculo: ' || iFormaCalculo, lRaise);

          if iFormaCalculo = 1 then

            select q80_ativ
              into iCodigoAtividade
              from saniatividade
                   inner join ativtipo on saniatividade.y83_ativ = ativtipo.q80_ativ
                   inner join tipcalc  on tipcalc.q81_codigo     = ativtipo.q80_tipcal
             where y83_codsani = V_Y74_CODSANI
               and y83_dtfim is null
               and q81_tipo in ( select codigo from w_tipos_sanitario )
               and y83_ativprinc is true;

            if iCodigoAtividade is not null then
              V_ACHOU = true;
            end if;

          elsif iFormaCalculo = 2 then

              select  Q80_ATIV
                into iCodigoAtividade
                from saniatividade
                     inner join ativtipo on saniatividade.y83_ativ = ativtipo.q80_ativ
                     inner join tipcalc  on tipcalc.q81_codigo     = ativtipo.q80_tipcal
               where y83_codsani = V_Y74_CODSANI
                 and y83_dtfim is null
                 and q81_tipo in ( select codigo from w_tipos_sanitario )
            order by q81_valexe desc
               limit 1;

            if iCodigoAtividade is not null then
              V_ACHOU = true;
            end if;

          end if;

          perform fc_debug('<fc_vistorias> iCodigoAtividade: ' || iCodigoAtividade, lRaise);

          if V_ACHOU is false then
            return '04- NENHUMA ATIVIDADE COM TIPO 6 CADASTRADA';
          end if;
        end if;

        if V_INSCR = true then

          select MIN(Q85_FORCAL)
          into iFormaCalculo
          from TIPCALC
          INNER JOIN CADCALC                ON TIPCALC.Q81_CADCALC = CADCALC.Q85_CODIGO
          INNER JOIN ATIVTIPO               ON ATIVTIPO.Q80_TIPCAL = TIPCALC.Q81_CODIGO
          INNER JOIN TABATIV          ON TABATIV.Q07_ATIV = ATIVTIPO.Q80_ATIV
          where Q07_INSCR = V_Y71_INSCR AND
          TABATIV.Q07_DATAFI is null AND
          TIPCALC.Q81_TIPO IN ( select codigo from w_tipos_localizacao );

          if iFormaCalculo is null then

            select MIN(Q85_FORCAL)
            into iFormaCalculo
            from ISSPORTETIPO
            INNER JOIN ISSBASEPORTE ON Q45_INSCR = V_Y71_INSCR
                                   and q45_codporte = q41_codporte
            INNER JOIN TIPCALC ON Q41_CODTIPCALC = Q81_CODIGO
            INNER JOIN CADCALC ON CADCALC.Q85_CODIGO = TIPCALC.Q81_CADCALC
            INNER JOIN CLASATIV ON Q82_CLASSE = Q41_CODCLASSE
            INNER JOIN TABATIV ON Q82_ATIV = Q07_ATIV AND Q07_INSCR = V_Y71_INSCR
            INNER JOIN ATIVPRINC ON ATIVPRINC.q88_inscr = TABATIV.q07_inscr and ATIVPRINC.q88_seq = TABATIV.q07_seq
            where Q45_CODPORTE = Q41_CODPORTE AND Q81_TIPO IN ( select codigo from w_tipos_localizacao ) and
            case when q07_datafi is null then true else q07_datafi >= V_DATA end AND
            q07_databx is null;

            if iFormaCalculo is null then
              return '17-SEM FORMA DE CALCULO ENCONTRADA (INSCR)!';
            end if;

          end if;

          perform fc_debug('Forma de Calculo encontrada: ' || iFormaCalculo, lRaise);
          perform fc_debug('v_data: ' ||v_data ||' V_Y71_INSCR: ' || V_Y71_INSCR, lRaise);

          /**
           * Pontuacao das classes
           */
            select Q82_ATIV, MAX(Q25_PONTUACAO)
              into iCodigoAtividade, nPontuacaoClasse
              from TABATIV
                   INNER JOIN CLASATIV   ON Q82_ATIV   = Q07_ATIV
                   INNER JOIN CLASSEPONT ON Q25_CLASSE = Q82_CLASSE
             where Q07_INSCR = V_Y71_INSCR
               AND case when q07_datafi is null
                        then true
                        else q07_datafi >= V_DATA end
               AND q07_databx is null
          GROUP BY Q82_ATIV
          ORDER BY MAX(Q25_PONTUACAO) DESC
             limit 1;

          if nPontuacaoClasse is not null then

            /**
             * Pontuacao zona fiscal
             */
            select Q26_PONTUACAO
              into iPontuacaoPorZonaFiscal
              from ZONAPONT
                   INNER JOIN ISSZONA ON Q26_ZONA = Q35_ZONA
             where Q35_INSCR = V_Y71_INSCR;

            if iPontuacaoPorZonaFiscal is null then
              return '12-PONTUACAO DA ZONA NAO ENCONTRADA';
            end if;

            /**
             * Pontuacao empregados/area
             */
            select Q30_QUANT, Q30_AREA
              into iPontuacaoPorEmpregadosArea, V_AREA
              from ISSQUANT
             where ISSQUANT.Q30_INSCR  = V_Y71_INSCR
               AND ISSQUANT.Q30_ANOUSU = iAnousu;

            if iPontuacaoPorEmpregadosArea is null then
              select Q30_QUANT, Q30_AREA
              from ISSQUANT
              into iPontuacaoPorEmpregadosArea, V_AREA
              where ISSQUANT.Q30_INSCR = V_Y71_INSCR AND ISSQUANT.Q30_ANOUSU = (iAnousu - 1);
              if iPontuacaoPorEmpregadosArea is null then
                select Q30_QUANT, Q30_AREA
                from ISSQUANT
                into iPontuacaoPorEmpregadosArea, V_AREA
                where ISSQUANT.Q30_INSCR = V_Y71_INSCR AND ISSQUANT.Q30_ANOUSU = (iAnousu + 1);
                if iPontuacaoPorEmpregadosArea is null then
                  INSERT into ISSQUANT select * from ISSQUANT where ISSQUANT.Q30_INSCR = V_Y71_INSCR AND ISSQUANT.Q30_ANOUSU = (iAnousu - 1);
                end if;
              end if;
            end if;

            /**
             * Pontuacao pelos empregados
             */
            select Q27_PONTUACAO
              into iPontuacaoPorEmpregados
              from EMPREGPONT
             where iPontuacaoPorEmpregadosArea >= Q27_QUANTINI AND
                   iPontuacaoPorEmpregadosArea <= Q27_QUANTFIM;

            if iPontuacaoPorEmpregados is null then
              return '13-PONTUACAO DO NUMERO DE EMPREGADOS NAO ENCONTRADA';
            end if;

            if lRaise is true then
              raise notice 'V_AREA: %', V_AREA;
            end if;

            /**
             * Pontuacao pela area
             */
            select Q28_PONTUACAO
              into iPontuacaoPorArea
              from AREAPONT
             where V_AREA >= Q28_QUANTINI
               AND V_AREA <= Q28_QUANTFIM;

            if iPontuacaoPorArea is null then
              return '14-PONTUACAO DA AREA NAO ENCONTRADA';
            end if;

            if lRaise is true then
              raise notice 'nPontuacaoClasse: % - iPontuacaoPorZonaFiscal: %  - iPontuacaoPorEmpregados: %  - iPontuacaoPorArea: %', nPontuacaoClasse, iPontuacaoPorZonaFiscal, iPontuacaoPorEmpregados, iPontuacaoPorArea;
            end if;

            iPontuacaoGeral = nPontuacaoClasse + iPontuacaoPorZonaFiscal + iPontuacaoPorEmpregados + iPontuacaoPorArea;

            perform fc_debug('Pontuacaogeral: ' || iPontuacaoGeral ,lRaise);

            select Q81_CODIGO, Q81_RECEXE, Q92_HIST, Q81_VALEXE, Q92_TIPO
              into V_ATIVTIPO, iCodigoReceitaExercicio, iCodigoHistoricoCalculo, nValorExercicio, iCodigoArretipo
              from TIPCALC
                   inner join tipcalcexe on tipcalcexe.q83_anousu = iAnousu
                                        and tipcalcexe.q83_tipcalc = tipcalc.q81_codigo
                   INNER JOIN CADVENCDESC ON Q92_CODIGO = tipcalcexe.Q83_CODVEN
             where iPontuacaoGeral >= Q81_QIEXE
               AND iPontuacaoGeral <= Q81_QFEXE
               AND Q81_TIPO IN ( select codigo from w_tipos_localizacao );

         /**
          * Por ativtipo
          */
          else

            if iFormaCalculo = 1 then

              select Q80_ATIV
                into iCodigoAtividade
                from TABATIV
                     INNER JOIN ATIVPRINC ON ATIVPRINC.q88_inscr = TABATIV.q07_inscr
                                         and ATIVPRINC.q88_seq = TABATIV.q07_seq
                     INNER JOIN ATIVTIPO  ON TABATIV.Q07_ativ = ATIVTIPO.q80_ativ
                     INNER JOIN TIPCALC   ON TIPCALC.Q81_CODIGO = ATIVTIPO.Q80_TIPCAL
              where Q07_INSCR = V_Y71_INSCR
                AND TABATIV.Q07_DATAFI is null
                AND Q81_TIPO IN ( select codigo from w_tipos_localizacao );

              if iCodigoAtividade is not null then
                V_ACHOU = true;
              else

                select Q07_ATIV
                  into iCodigoAtividade
                  from ISSPORTETIPO
                       INNER JOIN ISSBASEPORTE ON Q45_INSCR = V_Y71_INSCR and q45_codporte = q41_codporte
                       INNER JOIN TIPCALC ON Q41_CODTIPCALC = Q81_CODIGO
                       INNER JOIN CADCALC ON CADCALC.Q85_CODIGO = TIPCALC.Q81_CADCALC
                       INNER JOIN CLASATIV ON Q82_CLASSE = Q41_CODCLASSE
                       INNER JOIN TABATIV ON Q82_ATIV = Q07_ATIV AND Q07_INSCR = V_Y71_INSCR
                       INNER JOIN ATIVPRINC ON ATIVPRINC.q88_inscr = TABATIV.q07_inscr
                                           and ATIVPRINC.q88_seq = TABATIV.q07_seq
                where Q45_CODPORTE = Q41_CODPORTE
                  AND Q81_TIPO IN ( select codigo from w_tipos_localizacao )
                  and case when q07_datafi is null then true else q07_datafi >= V_DATA end
                  AND q07_databx is null;

                if iCodigoAtividade is not null then
                  V_ACHOU = true;
                end if;

              end if;

            elsif iFormaCalculo = 2 then

                select Q80_ATIV
                  into iCodigoAtividade
                  from TABATIV
                       INNER JOIN ATIVTIPO ON TABATIV.Q07_ativ = ATIVTIPO.q80_ativ
                       INNER JOIN TIPCALC ON TIPCALC.Q81_CODIGO = ATIVTIPO.Q80_TIPCAL
                 where Q07_INSCR = V_Y71_INSCR
                   AND TABATIV.Q07_DATAFI is null
                   AND Q81_TIPO IN ( select codigo from w_tipos_localizacao )
              ORDER BY Q81_VALEXE DESC
                 LIMIT 1;

              if iCodigoAtividade is not null then
                V_ACHOU = true;
              end if;

            end if;

            if V_ACHOU is false then
              return '16 - SEM ATIVIDADE PRINCIPAL';
            end if;

            select TIPCALC.Q81_CODIGO
              into V_ATIVTIPO
              from ATIVTIPO
                   INNER JOIN TABATIV ON Q07_ATIV = q80_ativ
                   INNER JOIN TIPCALC ON Q80_TIPCAL = Q81_CODIGO
                   INNER JOIN CADCALC ON CADCALC.Q85_CODIGO = TIPCALC.Q81_CADCALC
             where Q81_TIPO IN ( select codigo from w_tipos_localizacao )
               AND Q07_INSCR = V_Y71_INSCR
               AND case when q07_datafi is null then true else q07_datafi >= V_DATA end
               AND q07_databx is null
               AND Q07_ATIV = iCodigoAtividade;

            if V_ATIVTIPO is null then

              select TIPCALC.Q81_CODIGO
                into V_ATIVTIPO
                from ISSPORTETIPO
                     INNER JOIN ISSBASEPORTE ON Q45_INSCR = V_Y71_INSCR and q45_codporte = q41_codporte
                     INNER JOIN TIPCALC ON Q41_CODTIPCALC = Q81_CODIGO
                     INNER JOIN CADCALC ON CADCALC.Q85_CODIGO = TIPCALC.Q81_CADCALC
                     INNER JOIN CLASATIV ON Q82_CLASSE = Q41_CODCLASSE
                     INNER JOIN TABATIV ON Q82_ATIV = Q07_ATIV AND Q07_INSCR = V_Y71_INSCR
               where Q45_CODPORTE = Q41_CODPORTE
                 AND Q81_TIPO IN ( select codigo from w_tipos_localizacao )
                 and case when q07_datafi is null then true else q07_datafi >= V_DATA end
                 AND q07_databx is null
                 AND Q82_ATIV = iCodigoAtividade;

              if V_ATIVTIPO is null then
                return '06-SEM TIPO DE CALCULO CONFIGURADO!';
              end if;
            end if;

          end if;

        end if;

        if V_Y69_NUMPRE = 0 OR V_Y69_NUMPRE is null then

          select NEXTVAL('numpref_k03_numpre_seq')
            into iNumpreGerado;

          INSERT into VISTORIANUMPRE VALUES(iCodigoVistoria, iNumpreGerado);
        else

          iNumpreGerado = V_Y69_NUMPRE;
          select k00_numpre
            into iNumpreArrecant
            from arrecant
           where k00_numpre = iNumpreGerado;

          if iNumpreArrecant != 0 OR iNumpreArrecant is not null then
            return '07- VISTORIA JA PAGA OU CANCELADA ';
          end if;

          select k00_numpre
            into iNumpreArrecad
            from arrecad
           where k00_numpre = iNumpreGerado;

          if iNumpreArrecad != 0 OR iNumpreArrecad is not null then
            delete from arrecad where k00_numpre = iNumpreGerado;
          end if;

        end if;
      end if;

      perform fc_debug('Verifica se sanitario lIsSanitario: ' || lIsSanitario,  lRaise);
      perform fc_debug('                           V_INSCR: ' || V_INSCR, lRaise);

      /**
       * SE FOR POR SANITARIO SEGUE AQUI
       */
      if lIsSanitario = true then

        FOR rSaniAtividade IN
          select Y83_ATIV
            from SANIATIVIDADE
           where Y83_CODSANI = V_Y74_CODSANI
             AND Y83_ATIV    = iCodigoAtividade

          LOOP

          if lRaise is true then
            raise notice 'Y83_ATIV (2): % - anousu: %', rSaniAtividade.Y83_ATIV, iAnousu;
          end if;

          select Y18_INSCR
            into V_Y74_INSCRSANI
            from sanitarioinscr
           where y18_codsani = V_Y74_CODSANI;

          if lCalculaVistoriaMei is false then

            lContribuinteMei := fc_verifica_contribuinte_mei(V_Y74_INSCRSANI, dDataAtual);

            if lContribuinteMei then

              delete from vistorianumpre where y69_codvist = iCodigoVistoria and y69_numpre = iNumpreGerado;
              return '25 - CONTRIBUINTE OPTANTE PELO SIMPLES NACIONAL NA CATEGORIA MEI';
            end if;
          end if;

          select q81_recexe, q92_hist, q81_valexe, q92_tipo,
                 (select distinct q83_codven
                    from tipcalcexe
                   where q83_tipcalc = q81_codigo
                     and q83_anousu  = iAnousu)
            into iCodigoReceitaExercicio,
                 iCodigoHistoricoCalculo,
                 nValorExercicio,
                 iCodigoArretipo,
                 iCodVencimento
            from ATIVTIPO
                 inner join tipcalc     on q80_tipcal             = q81_codigo
                 inner join tipcalcexe  on tipcalcexe.q83_anousu  = iAnousu
                                       and tipcalcexe.q83_tipcalc = tipcalc.q81_codigo
                 inner join cadvencdesc on q92_codigo             = tipcalcexe.q83_codven
           where q80_ativ = rsaniatividade.y83_ativ
             and ( select y80_area from sanitario where y80_codsani = V_Y74_CODSANI) >= q81_qiexe
             and ( select y80_area from sanitario where y80_codsani = V_Y74_CODSANI) <= q81_qfexe
             and q81_tipo in ( select codigo from w_tipos_sanitario );

          if iCodigoReceitaExercicio is not null then

            nValorVistoria = round(nValorExercicio * nValorInflator * nValorBase,2);

            if lRaise is true then
              raise notice 'inserindo no arrecad... nValorVistoria (2): % - iCodVencimento: %', nValorVistoria, iCodVencimento;
            end if;

            lCalculou = true;
            --
            -- Inserindo por sanitario
            --

            --
            -- Funcao para gerar o financeiro
            --
            if iCodVencimento is null then
              return '18-SEM VENCIMENTO CONFIGURADO PARA O EXERCICIO!';
            end if;

            if lRaise is true then
              raise notice 'executando fc_gerafinanceiro(%,%,%,%,%,%,%)', iNumpreGerado,nValorVistoria,iCodVencimento,V_Y80_NUMCGM,V_DATA,iCodigoReceitaExercicio,dDataVistoria;
            end if;

            select *
              into rFinanceiro
              from fc_gerafinanceiro(iNumpreGerado,nValorVistoria,iCodVencimento,V_Y80_NUMCGM,V_DATA,iCodigoReceitaExercicio,dDataVistoria);

            if V_Y74_INSCRSANI is not null then

              select k00_numpre
                into iNumpreArreinscr
                from arreinscr
               where k00_numpre = iNumpreGerado;

              if iNumpreArreinscr != 0 OR iNumpreArreinscr is not null then
                DELETE from ARREINSCR where K00_NUMPRE = iNumpreArreinscr;
              end if;

              INSERT into ARREINSCR (k00_numpre, k00_inscr)
                             VALUES (iNumpreGerado, V_Y74_INSCRSANI);

            end if;
          end if;
        end loop;

        if lCalculou IS true then
          return '08 - OK ';
        else
          return '15 - ERRO DURANTE O CALCULO';
        end if;
      /**
       * FIM DO if DO SANITARIO
       */

      /**
       * SE FOR POR INSCRICAO SEGUE AQUI
       */
      elsif V_INSCR = true then

        perform fc_debug('<fc_vistorias> Inscricao - nPontuacaoClasse: ' || nPontuacaoClasse || ' iCodigoAtividade: ' || iCodigoAtividade, lRaise);

        if lCalculaVistoriaMei is false then

          lContribuinteMei := fc_verifica_contribuinte_mei(V_Y71_INSCR, dDataAtual);

          if lContribuinteMei then

            delete from vistorianumpre where y69_codvist = iCodigoVistoria and y69_numpre = iNumpreGerado;
            return '25 - CONTRIBUINTE OPTANTE PELO SIMPLES NACIONAL NA CATEGORIA MEI';
          end if;
        end if;

        if nPontuacaoClasse is null then

          sSql = sSql || ' select q81_qiexe, q81_qfexe, q81_codigo, q81_uqcad, q81_uqtab                ';
          sSql = sSql || '   from ativtipo                                                              ';
          sSql = sSql || '          inner join tipcalc on q81_codigo = q80_tipcal                       ';
          sSql = sSql || '  where q80_ativ = ' || iCodigoAtividade;
          sSql = sSql || '    and q81_tipo in ( select codigo from w_tipos_localizacao )                ';
          sSql = sSql || ' UNION                                                                        ';
          sSql = sSql || ' select q81_qiexe, q81_qfexe, q81_codigo, q81_uqcad, q81_uqtab                ';
          sSql = sSql || '   from issportetipo                                                          ';
          sSql = sSql || '        inner join issbaseporte on q45_inscr          = ' || V_Y71_INSCR;
          sSql = sSql || '        inner join tipcalc      on q41_codtipcalc     = q81_codigo            ';
          sSql = sSql || '        inner join cadcalc      on cadcalc.q85_codigo = tipcalc.q81_cadcalc   ';
          sSql = sSql || '        inner join clasativ     on q82_classe         = q41_codclasse         ';
          sSql = sSql || '  where q45_codporte = q41_codporte                                           ';
          sSql = sSql || '    and q81_tipo in (select codigo from w_tipos_localizacao )                 ';
          sSql = sSql || '    and q82_ativ = ' || iCodigoAtividade;

          select q60_campoutilcalc
            into v_tipo_quant
            from parissqn;

          if v_tipo_quant = 2 then

            select q30_quant
              into V_AREA
              from issquant
             where q30_inscr  = V_Y71_INSCR
               and q30_anousu = iAnousu;
          else

            select q30_area
              into V_AREA
              from issquant
             where q30_inscr  = V_Y71_INSCR
               and q30_anousu = iAnousu;
          end if;

          perform fc_debug('<fc_vistorias> V_AREA: ' || V_AREA, lRaise);
          perform fc_debug('<fc_vistorias> inscr : ' || V_Y71_INSCR, lRaise);
          perform fc_debug('<fc_vistorias> anousu: ' || iAnousu, lRaise);

          if V_AREA is null then
            V_AREA = 0;
          end if;

        else

          sSql = sSql || ' select q81_codigo,                                                              ';
          sSql = sSql || '        q81_recexe,                                                              ';
          sSql = sSql || '        q92_hist,                                                                ';
          sSql = sSql || '        q81_valexe,                                                              ';
          sSql = sSql || '        q92_tipo,                                                                ';
          sSql = sSql || '        q81_qiexe,                                                               ';
          sSql = sSql || '        q81_qfexe,                                                               ';
          sSql = sSql || '        q81_uqcad,                                                               ';
          sSql = sSql || '        q81_uqtab                                                                ';
          sSql = sSql || '   from ativtipo                                                                 ';
          sSql = sSql || '        inner join tipcalc     on tipcalc.q81_codigo     = ativtipo.q80_tipcal   ';
          sSql = sSql || '        inner join tipcalcexe  on tipcalcexe.q83_anousu  = ' || iAnousu;
          sSql = sSql || '                              and tipcalcexe.q83_tipcalc = tipcalc.q81_codigo    ';
          sSql = sSql || '        inner join cadcalc     on q81_cadcalc            = q85_codigo            ';
          ssql = ssql || '        inner join cadvencdesc on q92_codigo             = tipcalcexe.q83_codven ';
          sSql = sSql || '      where ' || iPontuacaoGeral || ' >= q81_qiexe and                          ';
          sSql = sSql ||                iPontuacaoGeral || '    <= q81_qfexe and                          ';
          sSql = sSql || '        Q81_TIPO IN ( select codigo from w_tipos_localizacao ) AND ATIVTIPO.Q80_ATIV = ' || iCodigoAtividade;

          V_AREA = iPontuacaoGeral;

          perform fc_debug('<fc_vistorias> V_AREA: ' || V_AREA, lRaise);
        end if;

        select count(*)
          into iQuantidadeAtividades
          from ( select distinct
                        q07_seq
                   from tabativ
                        inner join ativtipo on ativtipo.q80_ativ = tabativ.q07_ativ
                        inner join tipcalc on q81_codigo = q80_tipcal
                  where q81_tipo in (3,5,6)
                    and q07_inscr = V_Y71_INSCR
                    and (q07_datafi is null or q07_datafi >= current_date)
                    and (q07_databx is null or q07_databx >= current_date) ) as x;

        perform fc_debug('<fc_vistorias> iQuantidadeAtividades: ' || iQuantidadeAtividades, lRaise);

        FOR rAtivtipo IN EXECUTE sSql LOOP

          if lRaise is true then
            raise notice 'dentro do for... vcalculou : % - tipcalc: % - area: % - qiexe: % - qfexe: %',lCalculou, rAtivtipo.Q81_CODIGO, V_AREA, rAtivtipo.Q81_QIEXE, rAtivtipo.Q81_QFEXE;
          end if;

          if lRaise is true then
            raise notice 'antes do if... area - % q81_qiexe - % q81_qfexe - %',V_AREA,rAtivtipo.Q81_QIEXE,rAtivtipo.Q81_QFEXE;
          end if;

          if V_AREA >= rAtivtipo.Q81_QIEXE and V_AREA <= rAtivtipo.Q81_QFEXE then

            select q81_recexe, q92_hist, q81_valexe, q92_tipo, q81_excedenteativ,
                   (select distinct q83_codven from tipcalcexe where q83_tipcalc = q81_codigo and q83_anousu = iAnousu)
              into iCodigoReceitaExercicio,
                   iCodigoHistoricoCalculo,
                   nValorExercicio,
                   iCodigoArretipo,
                   nValorExcedente, -- Valor excedente configurado na atividade
                   iCodVencimento
              from tipcalc
                   inner join tipcalcexe  on tipcalcexe.q83_anousu  = ianousu
                                         and tipcalcexe.q83_tipcalc = tipcalc.q81_codigo
                   inner join cadcalc     on q81_cadcalc            = q85_codigo
                   inner join cadvencdesc on q92_codigo             = tipcalcexe.q83_codven
            where Q81_CODIGO = rAtivtipo.Q81_CODIGO;


            if iCodVencimento is null then
              return '18-SEM VENCIMENTO CONFIGURADO PARA O EXERCICIO!';
            end if;

            perform fc_debug('Calculando valor do debito:',        lRaise);
            perform fc_debug('nValorExercicio  : ' || nValorExercicio,   lRaise);
            perform fc_debug('nValorInflator: ' || nValorInflator, lRaise);
            perform fc_debug('nValorBase    : ' || nValorBase,     lRaise);

            /**
             * Verifica se o parametro Utiliza quantidade do cadastro configurado no tipo de calculo esta setado
             */
            if rAtivtipo.q81_uqcad is true then

              select q30_mult
                into nMultiplicadorIssQuantidade
                from issquant
               where q30_inscr  = v_y71_inscr
                 and q30_anousu = iAnousu;
              if not found then
                  return '22 - inscricao sem multiplicador cadastrado na issquant';
              end if;

              if nMultiplicadorIssQuantidade is null or nMultiplicadorIssQuantidade = 0 then
                  nMultiplicadorIssQuantidade := 1;
              end if;
            end if;

            /**
             * Verifica se o parametro Utiliza quantidade da tabela de atividades configurado no
             * tipo de calculo esta setado
             */
            if rAtivtipo.q81_uqtab is true then

               select q07_quant
                 into nQuantidadeAtividade
                 from tabativ
                where q07_inscr = v_y71_inscr
                  and q07_ativ = iCodigoAtividade
                  and q07_databx is null;
               if not found then
                  return '23 - inscricao sem atividade cadastrada na tabativ ou atividade baixada';
               end if;

               if nQuantidadeAtividade is null or nQuantidadeAtividade = 0 then
                  nQuantidadeAtividade := 1;
               end if;
            end if;

            nValorVistoria = round(nValorExercicio * nValorInflator * nValorBase * nMultiplicadorIssQuantidade * nQuantidadeAtividade, 2);
            perform fc_debug('Resultado -> nValorVistoria: ' || nValorVistoria,  lRaise);

            lCalculou = true;

            if lRaise is true then
              raise notice 'nValorExcedente-: % nValorVistoria (1): % - k00_numpre: % - nValorInflator: % - nValorBase: %', nValorExcedente, nValorVistoria, iNumpreGerado, nValorInflator, nValorBase;
            end if;

            if nValorExcedente > 0 then

              raise notice 'valor antes: %', nValorVistoria;
              nValorVistoria = nValorVistoria + (nValorVistoria * 0.3 * (iQuantidadeAtividades - 1));
              raise notice 'valor depois: %', nValorVistoria;
            end if;

            --
            -- Funcao para gerar o financeiro
            --
            select *
              into rFinanceiro
              from fc_gerafinanceiro(iNumpreGerado, nValorVistoria, iCodVencimento, V_q02_NUMCGM, V_DATA, iCodigoReceitaExercicio, dDataVistoria);

            select k00_numpre
              into iNumpreArreinscr
              from arreinscr
             where k00_numpre = iNumpreGerado;

            if iNumpreArreinscr != 0 OR iNumpreArreinscr is not null then
              DELETE from ARREINSCR where K00_NUMPRE = iNumpreArreinscr;
            end if;
            insert into arreinscr (k00_numpre, k00_inscr)
                           values (iNumpreGerado, V_Y71_INSCR);
          end if;

        end loop;

        if lRaise is true then
          raise notice 'fora do for... lCalculou: %', lCalculou;
        end if;

        if lCalculou IS true then
          return '09-OK INSCRICAO NUMERO ' || V_Y71_INSCR;
        else
          return '19-OCORREU ALGUM ERRO DURANTE O CALCULO (2)!!!';
        end if;

      end if;

      if V_INSCR = false AND lIsSanitario = false then
          return '10- CALCULO NAO CONFIGURADO PARA A VISTORIA NUMERO ' || iCodigoVistoria;
      end if;

    else
          return '20-PROCEDIMENTO N? PREPARADO PARA CALCULO POR FORMA DIFERENTE DE 1 (NORMAL)';
    end if;

  end;

$$ language 'plpgsql';insert into db_versaoant (db31_codver,db31_data) values (364, current_date);
select setval ('db_versaousu_db32_codusu_seq',(select max (db32_codusu) from db_versaousu));
select setval ('db_versaousutarefa_db28_sequencial_seq',(select max (db28_sequencial) from db_versaousutarefa));
select setval ('db_versaocpd_db33_codcpd_seq',(select max (db33_codcpd) from db_versaocpd));
select setval ('db_versaocpdarq_db34_codarq_seq',(select max (db34_codarq) from db_versaocpdarq));create table bkp_db_permissao_20160310_153647 as select * from db_permissao;
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

---------------------------------------------------------------------------------------------
---------------------------------- INICIO FINANCEIRO -----------------------------------------
---------------------------------------------------------------------------------------------
alter table liccomissao drop column if exists l30_nomearquivo;
alter table liccomissao drop column if exists l30_arquivo;

-- remove os atributos dinamicos cadastrados
drop table if exists w_codigoatributosdinamicos;
create temp table w_codigoatributosdinamicos as select db17_cadattdinamico as codigo from db_cadattdinamicosysarquivo where db17_sysarquivo = 1260;

delete from db_cadattdinamicoatributosvalor
 where db110_db_cadattdinamicoatributos in ( select db109_sequencial
                                               from db_cadattdinamicoatributos
                                              where db109_db_cadattdinamico in (select codigo from w_codigoatributosdinamicos) );
delete from db_cadattdinamicoatributosopcoes
  where db18_cadattdinamicoatributos in ( select db109_sequencial
                                            from db_cadattdinamicoatributos
                                           where db109_db_cadattdinamico in (select codigo from w_codigoatributosdinamicos) );
delete from db_cadattdinamicoatributos where db109_db_cadattdinamico in (select codigo from w_codigoatributosdinamicos);
delete from db_cadattdinamicosysarquivo where db17_sysarquivo = 1260;
delete from db_cadattdinamico where db118_sequencial in (select codigo from w_codigoatributosdinamicos);

drop table if exists w_codigoatributosdinamicos;
create temp table w_codigoatributosdinamicos as select db17_cadattdinamico as codigo from db_cadattdinamicosysarquivo where db17_sysarquivo = 1325;

delete from db_cadattdinamicoatributosvalor
 where db110_db_cadattdinamicoatributos in ( select db109_sequencial
                                               from db_cadattdinamicoatributos
                                              where db109_db_cadattdinamico in (select codigo from w_codigoatributosdinamicos) );
delete from db_cadattdinamicoatributosopcoes
  where db18_cadattdinamicoatributos in ( select db109_sequencial
                                            from db_cadattdinamicoatributos
                                           where db109_db_cadattdinamico in (select codigo from w_codigoatributosdinamicos) );
delete from db_cadattdinamicoatributos where db109_db_cadattdinamico in (select codigo from w_codigoatributosdinamicos);
delete from db_cadattdinamicosysarquivo where db17_sysarquivo = 1325;
delete from db_cadattdinamico where db118_sequencial in (select codigo from w_codigoatributosdinamicos);

---
drop table if exists db_cadattdinamicosysarquivo;
drop sequence if exists db_cadattdinamicosysarquivo_db17_sequencial_seq;

drop table if exists liccomissaocgmcadattdinamicovalorgrupo;
drop sequence if exists liccomissaocgmcadattdinamicovalorgrupo_l15_sequencial_seq;

drop table if exists liclicitacadattdinamicovalorgrupo;
drop sequence if exists liclicitacadattdinamicovalorgrupo_l16_sequencial_seq;

alter table db_cadattdinamicoatributos drop column if exists db109_nome;

drop table if exists db_cadattdinamicoatributosopcoes;
drop sequence if exists db_cadattdinamicoatributosopcoes_db18_sequencial_seq;

alter table pctipocompratribunal drop column if exists l44_sigla;

-- Estrutura Tabela Habilitação de Fornecedores
DROP TABLE IF EXISTS pcorcamfornelichabilitacao CASCADE;
DROP SEQUENCE IF EXISTS pcorcamfornelichabilitacao_l17_sequencial_seq;

-- Campo Tipo de Condição - Tabela de Fornecedores da Liquidação
alter table pcorcamfornelic drop column if exists pc31_tipocondicao;

drop table if exists liclicitatipoevento cascade;
drop sequence if exists liclicitatipoevento_l45_sequencial_seq;

drop table if exists liclicitaeventodocumento cascade;
drop sequence if exists liclicitaeventodocumento_l47_sequencial_seq;

drop table if exists liclicitaevento;
drop sequence if exists liclicitaevento_l46_sequencial_seq;

drop table if exists liclicitatipoevento cascade;
drop sequence if exists liclicitatipoevento_l45_sequencial_seq;

drop index if exists liclicitaencerramentolicitacon_sequencial_in;
drop table if exists liclicitaencerramentolicitacon;
drop sequence if exists liclicitaencerramentolicitacon_l18_sequencial_seq;
---------------------------------------------------------------------------------------------
---------------------------------- FIM FINANCEIRO -----------------------------------------
---------------------------------------------------------------------------------------------


---------------------------------------------------------------------------------------------
---------------------------------- INICIO FOLHA ---------------------------------------------
---------------------------------------------------------------------------------------------

DROP INDEX IF EXISTS cargorhrubricas_instit_in;
DROP INDEX IF EXISTS cargorhrubricas_cargo_instit_in;
DROP INDEX IF EXISTS cargorhrubricas_rubrica_instit_in;
DROP INDEX IF EXISTS funcaorhrubricas_instit_in;
DROP INDEX IF EXISTS funcaorhrubricas_rubrica_instit_in;
DROP INDEX IF EXISTS funcaorhrubricas_funcao_instit_in;

DROP TABLE IF EXISTS cargorhrubricas;
DROP TABLE IF EXISTS funcaorhrubricas;

DROP SEQUENCE IF EXISTS cargorhrubricas_rh176_sequencial_seq;
DROP SEQUENCE IF EXISTS funcaorhrubricas_rh177_sequencial_seq;

---------------------------------------------------------------------------------------------
---------------------------------- FIM FOLHA ------------------------------------------------
---------------------------------------------------------------------------------------------


SQL_DDL;

      $pre = <<<'SQL_PRE'


---------------------------------------------------------------------------------------------
---------------------------------- INICIO FINANCEIRO -----------------------------------------
---------------------------------------------------------------------------------------------

delete from db_menu where id_item_filho in (10204, 10212, 10213, 10214, 10205) AND modulo = 381;
delete from db_itensmenu where id_item in (10204, 10212, 10213, 10214, 10205);

delete from db_layoutcampos   where db52_layoutlinha in(791, 792, 793, 794, 795);
delete from db_layoutlinha    where db51_layouttxt in (231, 232, 233);
delete from db_layouttxt      where db50_codigo in (231, 232, 233);

delete from db_sysarqcamp where codarq = 1324 and codcam in (21705, 21704);
delete from db_syscampodef where codcam = 7915;
delete from db_syscampo where codcam in (21704, 21705);

delete from db_syscadind where codind = 4321;
delete from db_sysindices where codind = 4321;
delete from db_sysforkey where codarq = 3900;
delete from db_sysprikey where codarq = 3900;
delete from db_sysarqcamp where codarq = 3900;
delete from db_syssequencia where codsequencia = 1000545;
delete from db_syscampo where codcam in (21706, 21707, 21708);
delete from db_sysarqmod where codarq = 3900;
delete from db_sysarquivo where codarq = 3900;

delete from db_sysarqcamp where codarq = 3163 and codcam = 21709;
delete from db_syscampodef where codcam = 21709;
delete from db_syscampo where codcam = 21709;

delete from db_syscampodef where codcam in (21710, 21711, 21712);
delete from db_sysprikey where codarq = 3901;
delete from db_sysforkey where codarq = 3901;
delete from db_sysarqcamp where codarq = 3901;
delete from db_syssequencia where codsequencia = 1000546;
delete from db_syscampo where codcam in (21710, 21711, 21712);
delete from db_sysarqmod where codarq = 3901;
delete from db_sysarquivo where codarq = 3901;

delete from db_sysforkey where codarq = 3902;
delete from db_sysprikey where codarq = 3902;
delete from db_sysarqcamp where codarq = 3902;
delete from db_syssequencia where codsequencia = 1000547;
delete from db_syscampodef where codcam in (21716, 21713, 21714, 21715);
delete from db_syscampo where codcam in (21713, 21714, 21715, 21716);
delete from db_sysarqmod where codarq = 3902;
delete from db_sysarquivo where codarq = 3902;

delete from db_layoutcampos where db52_layoutlinha in (796,797);
delete from db_layoutlinha where db51_layouttxt in (234);
delete from db_layouttxt where db50_codigo in (234);

delete from db_syscampodef where codcam = 21717;
delete from db_sysarqcamp where codarq = 3145 and codcam = 21717;
delete from db_syscampo where codcam = 21717;

-- LICITACAO
delete from db_layoutcampos where db52_layoutlinha in (798,799);
delete from db_layoutlinha where db51_layouttxt in (235);
delete from db_layouttxt where db50_codigo in (235);

delete from db_syscampodef where codcam in(21718, 21719, 21720);
delete from db_sysprikey where codarq = 3903;
delete from db_sysforkey where codarq = 3903;
delete from db_sysarqcamp where codarq = 3903;
delete from db_syssequencia where codsequencia = 1000548;
delete from db_syscampo where codcam in (21718, 21719, 21720);
delete from db_sysarqmod where codarq = 3903;
delete from db_sysarquivo where codarq = 3903;

-- Situacao Licitacao
update liclicita set l20_licsituacao = 1 where l20_licsituacao in (6,7);
update liclicitasituacao set l11_licsituacao = 1 where l11_licsituacao in (6,7);

delete from licsituacao where l08_sequencial = 6;
delete from licsituacao where l08_sequencial = 7;

-- Menu Habilitação de Fornecedores
delete from db_menu where id_item = 1818 and id_item_filho = 10206;
delete from db_itensmenu where id_item = 10206;

-- Menu Licitacoes - Procedimentos - Licitacao - Adjudicar
delete from db_menu where id_item_filho = 10207 AND modulo = 381;
delete from db_itensmenu where id_item = 10207;

-- Menu Licitacoes - Procedimentos - Licitacao - Homologar
delete from db_menu where id_item_filho = 10208 AND modulo = 381;
delete from db_itensmenu where id_item = 10208;

-- Tabela Habilitação de Fornecedores
delete from db_sysforkey where codarq = 3904 and codcam = 21722;
delete from db_sysprikey where codarq = 3904 and codcam = 21721;

delete from db_syssequencia where codsequencia = 1000549;
delete from db_sysarqcamp where codarq = 3904 and codcam = 21723;
delete from db_syscampodef where codcam = 21723;

delete from db_syscampo where codcam = 21723;
delete from db_sysarqcamp where codarq = 3904 and codcam = 21722;
delete from db_syscampodef where codcam = 21722;

delete from db_syscampo where codcam = 21722;
delete from db_sysarqcamp where codarq = 3904 and codcam = 21721;
delete from db_syscampodef where codcam = 21721;

delete from db_syscampo where codcam = 21721;
delete from db_sysarqmod where codmod = 19 and codarq = 3904;
delete from db_sysarquivo where codarq = 3904;

-- Arquivo Licitante
delete from db_layoutcampos where db52_layoutlinha in (800,801);
delete from db_layoutlinha where db51_layouttxt in (236);
delete from db_layouttxt where db50_codigo in (236);

-- Campo Tipo de Condição - Tabela de Fornecedores da Liquidação
delete from db_sysarqcamp where codarq = 1291 and codcam = 21728;
delete from db_syscampodef where codcam = 21728;
delete from db_syscampo where codcam = 21728;

-- DOTACAO_LIC
delete from db_layoutcampos where db52_layoutlinha in (802,803);
delete from db_layoutlinha where db51_layouttxt in (237);
delete from db_layouttxt where db50_codigo in (237);

-- EVENTOS_LIC
delete from db_layoutcampos where db52_layoutlinha in (804,805);
delete from db_layoutlinha where db51_layouttxt in (238);
delete from db_layouttxt where db50_codigo in (238);

-- DOCUMENTO_LIC
delete from db_layoutcampos where db52_layoutlinha in (806,807);
delete from db_layoutlinha where db51_layouttxt in (239);
delete from db_layouttxt where db50_codigo in (239);

-- deixar para o final
delete from db_layouttxtgrupo where db56_sequencial = 6;


delete from db_menu where id_item_filho = 10209 AND modulo = 381;
delete from db_itensfilho where id_item = 10209;
delete from db_itensmenu where id_item = 10209;

delete from db_sysarqcamp where codsequencia = 1000552 and codarq = 3916 and codcam = 21737;
delete from db_syssequencia where codsequencia = 1000552;
delete from db_syscadind where codind = 4328 and codcam = 21737;
delete from db_sysindices where codind = 4328 and codarq = 3916;
delete from db_sysprikey where codarq = 3916;
delete from db_sysarqcamp where codarq = 3916;
delete from db_syscampo where codcam in (21737,21738);
delete from db_sysarqmod where codarq = 3916;
delete from db_sysarquivo where codarq = 3916;

delete from db_sysarqcamp where  codsequencia = 1000553 and codarq = 3917 and codcam = 21739;
delete from db_syssequencia where codsequencia = 1000553;
delete from db_syscadind where codind = 4329 and codcam = 21740;
delete from db_sysindices where codind = 4329;
delete from db_sysforkey where codarq = 3917;

delete from db_syscampodep where codcam = 21741;
delete from db_syscampodef where codcam = 21741;

delete from db_syscampodep where codcam = 21748;
delete from db_syscampodef where codcam = 21748;

delete from db_syscampodep where codcam = 21746;
delete from db_syscampodef where codcam = 21746;

delete from db_sysprikey where codarq = 3917;
delete from db_sysarqcamp where codarq = 3917;
delete from db_syscampo where codcam in (21739,21740,21741,21742,21743,21744,21745,21746,21747,21748);
delete from db_sysarqmod where codarq = 3917;
delete from db_sysarquivo where codarq = 3917;

delete from db_sysarqcamp where codsequencia = 1000554 and codarq = 3918 and codcam = 21749;
delete from db_syssequencia where codsequencia = 1000554;
delete from db_syscadind where codcam = 21750;
delete from db_sysindices where codarq = 3918;
delete from db_sysprikey where codarq = 3918;
delete from db_sysarqcamp where codarq = 3918;
delete from db_sysforkey where codarq = 3918;
delete from db_syscampo where codcam in (21749,21750,21751,21752,21753);
delete from db_sysarqmod where codarq = 3918;
delete from db_sysarquivo where codarq = 3918;

delete from db_syscadind    where (codind, codcam, sequen) = (4331, 21758, 1);
delete from db_syssequencia where codsequencia = 1000555;
delete from db_sysindices   where nomeind = 'liclicitaencerramentolicitacon_sequencial_in';
delete from db_sysforkey    where codarq = 3920;
delete from db_sysprikey    where codarq = 3920;
delete from db_sysarqcamp   where codarq = 3920;
delete from db_syscampo     where codcam in (21760, 21759, 21758);
delete from db_sysarqmod    where codarq = 3920;
delete from db_sysarquivo   where codarq = 3920;

---------------------------------------------------------------------------------------------
---------------------------------- FIM FINANCEIRO -----------------------------------------
---------------------------------------------------------------------------------------------

---------------------------------------------------------------------------------------------
------------------------------- INICIO EDUCAÇÃO/SAÚDE ---------------------------------------
---------------------------------------------------------------------------------------------

update db_itensmenu set descricao = 'Atendimento Médico', help = 'Atendimento Médico', funcao = 'sau2_atendimentomedico000.php', desctec = 'Relatório de Atendimento Médico' where id_item = 7146;

delete from db_menu      where id_item_filho = 10210 AND modulo = 1000004;
delete from db_itensmenu where id_item = 10210;
---------------------------------------------------------------------------------------------
---------------------------------- FIM EDUCAÇÃO/SAÚDE ---------------------------------------
---------------------------------------------------------------------------------------------

---------------------------------------------------------------------------------------------
---------------------------------- INICIO FOLHA ---------------------------------------------
---------------------------------------------------------------------------------------------

delete from db_sysarqmod where codarq in(3914, 3915);

delete from db_syscampodef where codcam in(21729, 21730, 21731, 21732, 21733, 21734, 21735, 21736, 21754, 21755, 21756, 21757);
delete from db_sysarqcamp where codcam in(21729, 21730, 21731, 21732, 21733, 21734, 21735, 21736, 21754, 21755, 21756, 21757);

delete from db_sysprikey where codarq in(3914, 3915);
delete from db_sysforkey where codarq in(3914, 3915);

delete from db_sysindices where codarq in(3914, 3915);
delete from db_syscadind where codcam in(21730, 21731, 21732, 21734, 21735, 21736, 21754, 21755, 21756, 21757);

delete from db_syssequencia where codsequencia in (1000551, 1000550);

delete from db_syscampo where codcam in(21729, 21730, 21731, 21732, 21733, 21734, 21735, 21736, 21754, 21755, 21756, 21757);

delete from db_sysarquivo where codarq in(3914, 3915);

delete from db_menu where id_item_filho = 10211;
delete from db_itensmenu where id_item = 10211;
---------------------------------------------------------------------------------------------
---------------------------------- FIM FOLHA ------------------------------------------------
---------------------------------------------------------------------------------------------



delete from db_menu where id_item_filho = 10210 AND modulo = 1000004;

SQL_PRE;

      $this->execute($ddl);    
      $this->execute($pre);

    }

}
