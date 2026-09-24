<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class M26560CriacaoCampoNovoTipoasse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = <<<SQL
        /*
 * fc_hash_int(text, integer)
 *
 *   . responsavel por gerar um hash em INT4 apartir de um text
 *   . se usar 32, o retorno deve ser bigint, senao ira retornar
 *   . numeros negativos, devera ser alterado a funcao
 *
 * Parametros:
 *  $1 - texto a ser processado.
 *  $2 - quantidade de bits para cálculo (default: 32)
 *
 * Retorno:
 *  INT4 - hash gerado apartir do algoritmo
 *
 * Referencias:
 *   https://stackoverflow.com/questions/9809381/hashing-a-string-to-a-numeric-value-in-postgresql
 *   https://stackoverflow.com/questions/8316164/convert-hex-in-text-representation-to-decimal-number/8316731#8316731
 *
 * para executar com debug: set client_min_messages = debug;
 *
 */
CREATE OR REPLACE FUNCTION fc_hash_int(text, integer DEFAULT 32) RETURNS integer AS
$$
DECLARE
    hash_int INTEGER;
BEGIN
    EXECUTE format(E'SELECT (\'x\'||substr(md5(%L),1,8))::bit(%s)::int;', $1, $2)
        INTO hash_int;

    RETURN hash_int;
END;
$$
    LANGUAGE plpgsql;

/*
 *
 * fc_dicionario_salt()
 *
 *   . retorna o 'salt' para ser usado na geração do hash dos IDs do dicionário
 *
 */
CREATE OR REPLACE FUNCTION fc_dicionario_salt()
    RETURNS INTEGER AS
$$
SELECT 50000000;
$$
    LANGUAGE sql IMMUTABLE;

/*
 *
 * fc_remove_dicionario_tabela(text, text)
 *
 *   . responsavel por limpar dicionario de dados (tabelas db_sys*) apartir de um DROP TABLE
 *
 * Parametros:
 *  $1 - nome do esquema para buscar do PostgreSQL.
 *  $2 - nome da tabela para buscar do PostgreSQL.
 *
 *  Exemplos:
 *     SELECT fc_remove_dicionario_tabela('caixa', 'arrecad'); -- Tabela caixa.arrecad
 *
 */
CREATE OR REPLACE FUNCTION fc_remove_dicionario_tabela(text, text)
    RETURNS void AS
$$
DECLARE
    sysarquivo INTEGER;
    sysmodulo  INTEGER;
    syscampos  INTEGER[];
    relid      REGCLASS;
BEGIN
    -- Salva o "relation id" do catálogo do PostgreSQL
    relid := format('%I.%I', $1, $2)::regclass;

    SELECT a.codarq
    INTO sysarquivo
    FROM configuracoes.db_sysarquivo a
             JOIN configuracoes.db_sysarqmod am ON am.codarq = a.codarq
             JOIN configuracoes.db_sysmodulo m ON am.codmod = m.codmod
    WHERE regexp_replace(lower(to_ascii(nomemod)), '[^A-Za-z]', '', 'g') = $1
      AND a.nomearq = $2;

    IF sysarquivo IS NULL THEN
        RAISE INFO 'Tabela %.% nao encontrada no dicionario de dados!', $1, $2;
    END IF;

    RAISE DEBUG 'sysarquivo: %', sysarquivo;

    SELECT codmod
    INTO sysmodulo
    FROM configuracoes.db_sysmodulo
    WHERE regexp_replace(lower(to_ascii(nomemod)), '[^A-Za-z]', '', 'g') = $1;

    SELECT array_agg(codcam)
    INTO syscampos
    FROM configuracoes.db_sysarqcamp
    WHERE codarq = sysarquivo;

    -- 0) Cria tabelas temporárias para salvar conteúdo anterior
    RAISE DEBUG 'Dropando tabelas temporarias';

    DROP TABLE IF EXISTS tmp_acount;
    DROP TABLE IF EXISTS tmp_db_viradaitemlog;
    DROP TABLE IF EXISTS tmp_cadattdinamicosysarquivo;
    DROP TABLE IF EXISTS tmp_cadtabelacalciptu;
    DROP TABLE IF EXISTS tmp_processa;
    DROP TABLE IF EXISTS tmp_registrosinconsistentesdados;
    DROP TABLE IF EXISTS tmp_registrosinconsistentes;
    DROP TABLE IF EXISTS tmp_relattabelas;
    DROP TABLE IF EXISTS tmp_sysclasses;
    DROP TABLE IF EXISTS tmp_sysforkey;
    DROP TABLE IF EXISTS tmp_syscampo;
    DROP TABLE IF EXISTS tmp_sysarqmod;
    DROP TABLE IF EXISTS tmp_systriggers;
    DROP TABLE IF EXISTS tmp_sysindices;
    DROP TABLE IF EXISTS tmp_syscadind;
    DROP TABLE IF EXISTS tmp_syscampodef;
    DROP TABLE IF EXISTS tmp_syscampodep;
    DROP TABLE IF EXISTS tmp_iptutabelasdepend;
    DROP TABLE IF EXISTS tmp_iptutabelasconfigcampochave;
    DROP TABLE IF EXISTS tmp_iptutabelasconfigcampocorrecao;
    DROP TABLE IF EXISTS tmp_iptutabelasconfigvirada;
    DROP TABLE IF EXISTS tmp_iptutabelasconfig;
    DROP TABLE IF EXISTS tmp_iptutabelas;
    DROP TABLE IF EXISTS tmp_sysarquivo;

    RAISE DEBUG 'Criando tabelas temporarias';

    CREATE TEMP TABLE tmp_acount AS
    SELECT *
    FROM configuracoes.db_acount
    WHERE codarq = sysarquivo;

    CREATE TEMP TABLE tmp_db_viradaitemlog AS
    SELECT *
    FROM configuracoes.db_viradaitemlog
    WHERE c35_codarq = sysarquivo;

    CREATE TEMP TABLE tmp_cadattdinamicosysarquivo AS
    SELECT *
    FROM configuracoes.db_cadattdinamicosysarquivo
    WHERE db17_sysarquivo = sysarquivo;

    CREATE TEMP TABLE tmp_cadtabelacalciptu AS
    SELECT *
    FROM configuracoes.db_cadtabelacalciptu
    WHERE db37_db_sysarquivo = sysarquivo;

    CREATE TEMP TABLE tmp_processa AS
    SELECT *
    FROM configuracoes.db_processa
    WHERE codarq = sysarquivo;

    CREATE TEMP TABLE tmp_registrosinconsistentesdados AS
    SELECT *
    FROM configuracoes.db_registrosinconsistentesdados
    WHERE db137_db_registrosinconsistentes in (SELECT db136_sequencial
                                               FROM configuracoes.db_registrosinconsistentes
                                               WHERE db136_tabela = sysarquivo);

    CREATE TEMP TABLE tmp_registrosinconsistentes AS
    SELECT *
    FROM configuracoes.db_registrosinconsistentes
    WHERE db136_tabela = sysarquivo;

    CREATE TEMP TABLE tmp_relattabelas AS
    SELECT *
    FROM configuracoes.db_relattabelas
    WHERE db92_codarq = sysarquivo;

    CREATE TEMP TABLE tmp_sysclasses AS
    SELECT *
    FROM configuracoes.db_sysclasses
    WHERE codarq = sysarquivo;

    CREATE TEMP TABLE tmp_sysforkey AS
    SELECT db_sysforkey.*
    FROM configuracoes.db_sysforkey
         JOIN configuracoes.db_syscampo ON db_syscampo.codcam = db_sysforkey.codcam
    WHERE db_sysforkey.codcam = ANY (syscampos)
      AND db_sysforkey.codarq <> sysarquivo;

    CREATE TEMP TABLE tmp_syscampo AS
    SELECT *,
           pg_catalog.col_description(relid, (SELECT attnum
                                              FROM pg_attribute
                                              WHERE attrelid = relid AND attname = nomecam)) AS comentario
    FROM configuracoes.db_syscampo
    WHERE codcam = ANY (syscampos)
      AND NOT EXISTS (SELECT 1
                      FROM configuracoes.db_sysarqcamp ac
                      WHERE ac.codcam = db_syscampo.codcam
                        AND ac.codarq IS DISTINCT FROM sysarquivo);

    CREATE TEMP TABLE tmp_systriggers AS
    SELECT *
    FROM configuracoes.db_systriggers
    WHERE codarq = sysarquivo;

    CREATE TEMP TABLE tmp_syscampodef AS
    SELECT db_syscampodef.*
    FROM configuracoes.db_syscampodef
         JOIN configuracoes.db_syscampo ON db_syscampo.codcam = db_syscampodef.codcam
    WHERE db_syscampodef.codcam = ANY (syscampos);

    CREATE TEMP TABLE tmp_syscampodep AS
    SELECT db_syscampodep.*
    FROM configuracoes.db_syscampodep
         JOIN configuracoes.db_syscampo ON db_syscampo.codcam = db_syscampodep.codcam
    WHERE db_syscampodep.codcam = ANY (syscampos);

    CREATE TEMP TABLE tmp_iptutabelasdepend AS
    SELECT *
    FROM cadastro.iptutabelasdepend
    WHERE j128_iptutabelas in (SELECT j121_sequencial FROM cadastro.iptutabelas WHERE j121_codarq = sysarquivo);

    CREATE TEMP TABLE tmp_iptutabelasconfigcampochave AS
    SELECT *
    FROM cadastro.iptutabelasconfigcampochave
    WHERE j124_iptutabelasconfig in (SELECT j122_sequencial
                                     FROM cadastro.iptutabelas
                                          JOIN cadastro.iptutabelasconfig ON j122_iptutabelas = j121_sequencial
                                     WHERE j121_codarq = sysarquivo);

    CREATE TEMP TABLE tmp_iptutabelasconfigcampocorrecao AS
    SELECT *
    FROM cadastro.iptutabelasconfigcampocorrecao
    WHERE j123_iptutabelasconfig in (SELECT j122_sequencial
                                     FROM cadastro.iptutabelas
                                          JOIN iptutabelasconfig ON j122_iptutabelas = j121_sequencial
                                     WHERE j121_codarq = sysarquivo);

    CREATE TEMP TABLE tmp_iptutabelasconfigvirada AS
    SELECT *
    FROM cadastro.iptutabelasconfigvirada
    WHERE j129_iptutabelasconfig in (SELECT j122_sequencial
                                     FROM cadastro.iptutabelas
                                          JOIN cadastro.iptutabelasconfig ON j122_iptutabelas = j121_sequencial
                                     WHERE j121_codarq = sysarquivo);

    CREATE TEMP TABLE tmp_iptutabelasconfig AS
    SELECT *
    FROM cadastro.iptutabelasconfig
    WHERE j122_iptutabelas in (SELECT j121_sequencial FROM cadastro.iptutabelas WHERE j121_codarq = sysarquivo);

    CREATE TEMP TABLE tmp_iptutabelas AS
    SELECT *
    FROM cadastro.iptutabelas
    WHERE j121_codarq = sysarquivo;

    CREATE TEMP TABLE tmp_sysarqmod AS
    SELECT *
    FROM configuracoes.db_sysarqmod
    WHERE codarq = sysarquivo;

    CREATE TEMP TABLE tmp_sysarquivo AS
    SELECT *, pg_catalog.obj_description(relid, 'pg_class') AS comentario
    FROM configuracoes.db_sysarquivo
    WHERE codarq = sysarquivo;

    -- 1) Remove tudo
    RAISE DEBUG 'Deletando registros das tabelas auxiliares';

    DELETE FROM iptutabelasconfigcampochave
    WHERE j124_iptutabelasconfig in (SELECT j122_sequencial
                                     FROM iptutabelas
                                              JOIN iptutabelasconfig ON j122_iptutabelas = j121_sequencial
                                     WHERE j121_codarq = sysarquivo);
    DELETE FROM iptutabelasconfigcampocorrecao
    WHERE j123_iptutabelasconfig in (SELECT j122_sequencial
                                     FROM iptutabelas
                                              JOIN iptutabelasconfig ON j122_iptutabelas = j121_sequencial
                                     WHERE j121_codarq = sysarquivo);
    DELETE FROM iptutabelasconfigvirada
    WHERE j129_iptutabelasconfig in (SELECT j122_sequencial
                                     FROM iptutabelas
                                              JOIN iptutabelasconfig ON j122_iptutabelas = j121_sequencial
                                     WHERE j121_codarq = sysarquivo);
    DELETE FROM cadastro.iptutabelasconfig
    WHERE j122_iptutabelas in
          (SELECT j121_sequencial FROM iptutabelas WHERE j121_codarq = sysarquivo);
    DELETE FROM cadastro.iptutabelasdepend
    WHERE j128_iptutabelasdepend in
          (SELECT j121_sequencial FROM iptutabelas WHERE j121_codarq = sysarquivo);
    DELETE FROM cadastro.iptutabelas WHERE j121_codarq = sysarquivo;
    DELETE FROM configuracoes.db_sysclasses WHERE codarq = sysarquivo;
    DELETE FROM configuracoes.db_acount WHERE codarq = sysarquivo;
    DELETE FROM configuracoes.db_viradaitemlog WHERE c35_codarq = sysarquivo;
    DELETE FROM configuracoes.db_sysarqmod WHERE codarq = sysarquivo AND codmod = sysmodulo;
    DELETE FROM configuracoes.db_sysforkey WHERE codarq = sysarquivo;
    DELETE FROM configuracoes.db_sysprikey WHERE codarq = sysarquivo;
    DELETE FROM configuracoes.db_syscadind WHERE codcam = ANY (syscampos);
    DELETE FROM configuracoes.db_sysindices WHERE codarq = sysarquivo;
    DELETE FROM configuracoes.db_sysarqcamp WHERE codarq = sysarquivo AND codcam = ANY (syscampos);
    DELETE FROM configuracoes.db_syscampodef WHERE codcam = ANY (syscampos);
    DELETE FROM configuracoes.db_syscampodep WHERE codcam = ANY (syscampos);
    DELETE FROM configuracoes.db_processa WHERE codarq = sysarquivo;
    DELETE FROM configuracoes.db_cadattdinamicosysarquivo WHERE db17_sysarquivo = sysarquivo;
    DELETE FROM configuracoes.db_cadtabelacalciptu WHERE db37_db_sysarquivo = sysarquivo;
    DELETE FROM configuracoes.db_registrosinconsistentesdados
    WHERE db137_db_registrosinconsistentes in (SELECT db136_sequencial
                                               FROM configuracoes.db_registrosinconsistentes
                                               WHERE db136_tabela = sysarquivo);

    DELETE FROM configuracoes.db_registrosinconsistentes WHERE db136_tabela = sysarquivo;
    DELETE FROM configuracoes.db_relattabelas WHERE db92_codarq = sysarquivo;
    DELETE FROM configuracoes.db_systriggers WHERE codarq = sysarquivo;
    DELETE FROM configuracoes.db_syscampo
           WHERE codcam = ANY (syscampos)
             AND NOT EXISTS (
                 SELECT 1
                 FROM configuracoes.db_sysarqcamp
                 WHERE codarq <> sysarquivo AND codcam = ANY (syscampos)
           );
    DELETE FROM configuracoes.db_sysarqmod WHERE codarq = sysarquivo;
    DELETE FROM configuracoes.db_sysarquivo WHERE codarq = sysarquivo;

    RETURN;
END;
$$
    LANGUAGE plpgsql;

/*
 *
 * fc_gera_dicionario_apartir_tabela(text, text)
 *
 *   . responsavel por gerar dicionario de dados (tabelas db_sys*) apartir de uma tabela existente no PostgreSQL
 *
 * Parametros:
 *  $1 - nome do esquema para buscar do PostgreSQL.
 *  $2 - nome da tabela para buscar do PostgreSQL.
 *
 *  Exemplos:
 *     SELECT fc_gera_dicionario_apartir_tabela('caixa', 'arrecad'); -- Tabela caixa.arrecad
 *
 */
CREATE OR REPLACE FUNCTION fc_gera_dicionario_apartir_tabela(text, text)
    RETURNS void AS
$$
DECLARE

    sysarquivo   INTEGER;
    sysarquivofk INTEGER;
    syscampo     INTEGER;
    sysmodulo    INTEGER;
    syssequencia INTEGER;
    iAceitatipo  INTEGER;
    iSeqArquivo  INTEGER;
    iUltimoReg   INTEGER;
    iSequencia   INTEGER;
    iCurrentSeq  INTEGER;
    iCodCampo    INTEGER;

    r            RECORD;
    r1           RECORD;

    relid        REGCLASS;

    tDescricao   TEXT;

    bMaiusculo   BOOLEAN;
    bAutocompl   BOOLEAN;

    sRotulo      VARCHAR;
    sTipoobj     VARCHAR;
    sRotulorel   VARCHAR;

BEGIN

    SELECT codmod
    INTO sysmodulo
    FROM configuracoes.db_sysmodulo
    WHERE regexp_replace(lower(to_ascii(nomemod)), '[^A-Za-z]' , '', 'g') = $1;

    -- Salva o "relation id" do catálogo do PostgreSQL
    relid := format('%I.%I', $1, $2)::regclass;

    -- 0) Remove tudo
    RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> 1 - Chamando a funcao fc_remove_dicionario_tabela';
    PERFORM fc_remove_dicionario_tabela($1, $2);
    RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> 1 - Removeu registros, caso existam';

    SELECT codarq
    INTO sysarquivo
    FROM tmp_sysarquivo
    WHERE nomearq = $2;

    IF sysarquivo IS NULL THEN
        RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Gerando novo hash para a tabela do dicionario de dados';
        sysarquivo := fc_hash_int($2, 28) + fc_dicionario_salt();
        -- 1) db_sysarquivo
        INSERT INTO configuracoes.db_sysarquivo(codarq, nomearq) VALUES (sysarquivo, $2);

        TRUNCATE tmp_sysarquivo;
        INSERT INTO tmp_sysarquivo
        SELECT *, pg_catalog.obj_description(relid, 'pg_class') AS comentario
        FROM db_sysarquivo
        WHERE codarq = sysarquivo;
    ELSE
        RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Inserindo registros na tabela db_sysarquivo do dicionario de dados';
        -- 1) db_sysarquivo
        INSERT INTO configuracoes.db_sysarquivo (codarq, nomearq, descricao, sigla, dataincl, rotulo, tipotabela,
                                                 naolibclass, naolibprog, naolibform)
            (select codarq,
                    nomearq,
                    descricao,
                    sigla,
                    dataincl,
                    rotulo,
                    tipotabela,
                    naolibclass,
                    naolibprog,
                    naolibform
             from tmp_sysarquivo);
    END IF;

    /* Verifica a sequence da tabela db_sysindices */
    select last_value into iSeqArquivo from db_sysindices_codind_seq;
    select max(codind) into iUltimoReg from db_sysindices;

    if iUltimoReg > iSeqArquivo then
        RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Seta a sequence da tabela db_sysindices';
        perform setval('db_sysindices_codind_seq', iUltimoReg);
    end if;

    RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Inserindo registros na tabela db_sysarqmod do dicionario de dados';
    -- 2) db_sysarqmod
    INSERT INTO configuracoes.db_sysarqmod(codmod, codarq)
    VALUES (sysmodulo, sysarquivo);

    -- 3) db_syscampo, db_syssequencia, db_sysarqcamp
    FOR r IN
        SELECT columns.table_catalog,
               columns.table_schema,
               columns.table_name,
               columns.column_name,
               columns.column_default,
               columns.udt_name,
               (columns.is_nullable = 'YES')                   AS is_nullable,
               columns.character_maximum_length                AS size,
               columns.ordinal_position                        AS position,
               sequences.sequence_name,
               sequences.increment::integer                    AS increment,
               sequences.start_value::integer                  AS start_value,
               sequences.minimum_value::integer                AS minimum_value,
               sequences.maximum_value::bigint                 AS maximum_value,
               kc.constraint_catalog,
               kc.constraint_schema,
               kc.constraint_name,
               kc.ordinal_position                             AS position_in_constraint,
               kc.position_in_unique_constraint,
               (SELECT array_agg(constraint_type::text)
                FROM information_schema.table_constraints tc
                WHERE tc.constraint_catalog = kc.constraint_catalog
                  AND tc.constraint_schema = kc.constraint_schema
                  AND tc.constraint_name = kc.constraint_name) AS constraint_type

        FROM information_schema.columns
             LEFT JOIN information_schema.sequences
                 ON sequences.sequence_catalog = columns.table_catalog
                AND sequences.sequence_schema = columns.table_schema
                AND format('nextval(%L::regclass)', sequences.sequence_name) = columns.column_default
             LEFT JOIN information_schema.key_column_usage kc ON kc.table_catalog = columns.table_catalog
                AND kc.table_schema = columns.table_schema
                AND kc.table_name = columns.table_name
                AND kc.column_name = columns.column_name
        WHERE columns.table_schema = $1
          AND columns.table_name = $2
    LOOP
            -- 3.1) db_syscampo
            SELECT codcam
            INTO syscampo
            FROM tmp_syscampo
            WHERE nomecam = r.column_name;

            -- Implementado essa verificação, pois pode haver o mesmo campo em mais de 1 tabela
            IF syscampo IS NULL THEN
                RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Verifica se ja existe o campo';

                SELECT codcam
                INTO syscampo
                FROM db_syscampo
                WHERE nomecam = r.column_name;

                INSERT INTO tmp_syscampo
                SELECT *,
                       pg_catalog.col_description(relid, (SELECT attnum
                                                          FROM pg_attribute
                                                          WHERE attrelid = relid AND attname = nomecam)) AS comentario
                FROM db_syscampo
                WHERE nomecam = r.column_name;

            END IF;

            IF syscampo IS NULL THEN
                syscampo := fc_hash_int(r.column_name, 28) + fc_dicionario_salt();

                INSERT INTO tmp_syscampo (codcam, nomecam, comentario)
                   VALUES (syscampo,
                           r.column_name,
                           pg_catalog.col_description(relid, (SELECT attnum
                                                                        FROM pg_attribute
                                                                        WHERE attrelid = relid
                                                                          AND attname = r.column_name))
                          );
            END IF;

            SELECT coalesce(descricao, ''),
                   coalesce(rotulo, ''),
                   coalesce(maiusculo, false),
                   coalesce(autocompl, false),
                   coalesce(aceitatipo, 0),
                   coalesce(tipoobj, ''),
                   coalesce(rotulorel, '')
            INTO tDescricao,
                sRotulo,
                bMaiusculo,
                bAutocompl,
                iAceitatipo,
                sTipoobj,
                sRotulorel
            FROM tmp_syscampo
            WHERE codcam = syscampo
              AND (comentario = '' OR comentario IS NULL);

            IF NOT FOUND THEN
                tDescricao = '';
                sRotulo = '';
                bMaiusculo = false;
                bAutocompl = false;
                iAceitatipo = 0;
                sTipoobj = '';
                sRotulorel = '';
            END IF;

            INSERT INTO configuracoes.db_syscampo(codcam, nomecam, conteudo, nulo, tamanho, valorinicial,
                                                  descricao, rotulo, maiusculo, autocompl, aceitatipo, tipoobj,
                                                  rotulorel)
            VALUES (syscampo, r.column_name, r.udt_name || coalesce('(' || r.size || ')', ''), r.is_nullable, r.size,
                    r.column_default,
                    tDescricao, sRotulo, bMaiusculo, bAutocompl, iAceitatipo, sTipoobj, sRotulorel)
            ON CONFLICT (nomecam)
                DO UPDATE SET conteudo = EXCLUDED.conteudo, nulo = EXCLUDED.nulo, tamanho = EXCLUDED.tamanho;

            -- 3.2) db_syssequencia
            IF r.sequence_name IS NOT NULL THEN
                syssequencia := fc_hash_int(r.sequence_name, 28) + fc_dicionario_salt();

                INSERT INTO configuracoes.db_syssequencia (codsequencia, nomesequencia, incrseq, minvalueseq,
                                                           maxvalueseq, startseq)
                VALUES (syssequencia, r.sequence_name, r.increment, r.minimum_value, r.maximum_value, r.start_value)
                ON CONFLICT (nomesequencia)
                    DO UPDATE SET incrseq     = EXCLUDED.incrseq,
                                  minvalueseq = EXCLUDED.minvalueseq,
                                  maxvalueseq = EXCLUDED.maxvalueseq,
                                  startseq    = EXCLUDED.startseq;
            ELSE
                syssequencia := NULL;
            END IF;

            -- 3.3) db_sysarqcamp
            INSERT INTO configuracoes.db_sysarqcamp (codarq, codcam, seqarq, codsequencia)
            VALUES (sysarquivo, syscampo, r.position, syssequencia)
            ON CONFLICT ON CONSTRAINT db_sysarqcamp_codc_coda_seqa_pk
                DO NOTHING;

            -- 3.4) db_sysprikey
            IF 'PRIMARY KEY' = ANY (r.constraint_type) THEN
                INSERT INTO configuracoes.db_sysprikey (codarq, codcam, sequen, camiden)
                VALUES (sysarquivo, syscampo, r.position_in_constraint, syscampo);
            END IF;

            -- 3.5) db_sysforkey
            IF 'FOREIGN KEY' = ANY (r.constraint_type) THEN
                SELECT a.codarq
                INTO sysarquivofk
                FROM information_schema.referential_constraints rc
                         JOIN information_schema.table_constraints tc
                              ON tc.constraint_catalog = rc.unique_constraint_catalog
                                  AND tc.constraint_schema = rc.unique_constraint_schema
                                  AND tc.constraint_name = rc.unique_constraint_name

                         JOIN configuracoes.db_sysmodulo m
                              ON regexp_replace(lower(to_ascii(m.nomemod)), '[^A-Za-z]', '', 'g') = tc.table_schema
                         JOIN configuracoes.db_sysarquivo a ON a.nomearq = tc.table_name
                         JOIN configuracoes.db_sysarqmod am ON am.codmod = m.codmod AND am.codarq = a.codarq

                WHERE rc.constraint_catalog = r.constraint_catalog
                  AND rc.constraint_schema = r.constraint_schema
                  AND rc.constraint_name = r.constraint_name;

                IF sysarquivofk IS NOT NULL THEN
                    INSERT INTO configuracoes.db_sysforkey(codarq, codcam, sequen, referen)
                    VALUES (sysarquivo, syscampo, r.position_in_constraint, sysarquivofk);
                END IF;
            ELSE
                sysarquivofk := NULL;
            END IF;

            -- 3.6) Atualiza metadados no dicionario da COLUNA apartir do comentario salvo
            PERFORM fc_atualiza_dicionario_apartir_comentario('table column', format('%I.%I.%I', $1, $2, r.column_name),
                                                              comentario)
            FROM tmp_syscampo
            WHERE nomecam = r.column_name
              AND comentario IS NOT NULL;

    END LOOP;

    /* cria as tabelas de cadastro dos indices (db_sysindices, db_syscadind) */

    RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Processando tabela %.%', $1, $2;
    RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Inserindo registros nas tabelas db_sysindices e db_syscadind do dicionario de dados';
    FOR r IN
        SELECT i.relname AS indexname,
               x.indisunique,
               (array(select a.attname
                      from pg_attribute a
                      where a.attrelid = c.oid
                        and a.attnum = ANY (x.indkey)
                      order by a.attname)
                   )     as array_column_name
        FROM pg_index x
                 JOIN pg_class c ON c.oid = x.indrelid
                 JOIN pg_class i ON i.oid = x.indexrelid
                 LEFT JOIN pg_namespace n ON n.oid = c.relnamespace
        WHERE x.indisprimary is false
          AND (c.relkind = ANY (ARRAY ['r'::"char", 'm'::"char"]))
          AND i.relkind = 'i'::"char"
          AND n.nspname = $1
          AND c.relname = $2
    LOOP

            RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Processando indice %', r.indexname;
            iCurrentSeq := nextval('db_sysindices_codind_seq');

            INSERT INTO configuracoes.db_sysindices (codind, nomeind, codarq, campounico)
            VALUES (iCurrentSeq,
                    r.indexname,
                    sysarquivo,
                    case
                        when r.indisunique is true then '1'
                        else '0'
                        end);

            FOR r1 IN SELECT unnest(r.array_column_name) as column_name
            LOOP

                    SELECT codcam
                    INTO iCodCampo
                    FROM configuracoes.db_syscampo
                    WHERE nomecam = r1.column_name;

                    IF NOT FOUND THEN
                        RAISE EXCEPTION 'Campo % não encontrado na tabela db_syscampo.', r1.column_name;
                    END IF;

                    SELECT (sequen + 1)
                    INTO iSequencia
                    FROM configuracoes.db_syscadind
                    WHERE codind = iCurrentSeq
                    ORDER BY sequen DESC
                    LIMIT 1;

                    IF NOT FOUND THEN
                        iSequencia = 1;
                    END IF;

                    INSERT INTO configuracoes.db_syscadind (codind, codcam, sequen)
                    VALUES (iCurrentSeq, iCodCampo, iSequencia);
            END LOOP;
    END LOOP;

    /* fim do cadastro dos indices */
    RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Criou registros nas tabelas db_sysindices e db_syscadind do dicionario de dados';

    RAISE DEBUG '<fc_gera_dicionario_apartir_tabela> Atualiza os comentarios da tabela %', $2;
    -- 4) Atualiza metadados no dicionario da TABELA apartir do comentario salvo
    PERFORM fc_atualiza_dicionario_apartir_comentario('table', format('%I.%I', $1, $2), comentario)
    FROM tmp_sysarquivo
    WHERE nomearq = $2
      AND comentario IS NOT NULL;

    INSERT INTO configuracoes.db_sysarqmod
    SELECT tmp_sysarqmod.*
    FROM tmp_sysarqmod
         LEFT JOIN configuracoes.db_sysarqmod ON db_sysarqmod.codarq = tmp_sysarqmod.codarq
    WHERE db_sysarqmod.codarq IS NULL;

    INSERT INTO configuracoes.db_syscampodep
    SELECT tmp_syscampodep.*
    FROM tmp_syscampodep
         LEFT JOIN configuracoes.db_syscampodep ON db_syscampodep.codcam = tmp_syscampodep.codcam
    WHERE db_syscampodep.codcam IS NULL;

    INSERT INTO configuracoes.db_syscampodef
    SELECT tmp_syscampodef.*
    FROM tmp_syscampodef
         LEFT JOIN configuracoes.db_syscampodef ON db_syscampodef.codcam = tmp_syscampodef.codcam
                                               AND db_syscampodef.defcampo = tmp_syscampodef.defcampo
    WHERE db_syscampodef.codcam IS NULL;

    INSERT INTO configuracoes.db_sysforkey
    SELECT tmp_sysforkey.*
    FROM tmp_sysforkey
         LEFT JOIN configuracoes.db_sysforkey ON db_sysforkey.codcam = tmp_sysforkey.codcam
                                             AND db_sysforkey.codarq = tmp_sysforkey.codarq
    WHERE db_sysforkey.codcam IS NULL;

    INSERT INTO configuracoes.db_acount
    SELECT tmp_acount.*
    FROM tmp_acount;

    INSERT INTO configuracoes.db_viradaitemlog
    SELECT tmp_db_viradaitemlog.*
    FROM tmp_db_viradaitemlog;

    INSERT INTO configuracoes.db_systriggers
    SELECT tmp_systriggers.*
    FROM tmp_systriggers
         LEFT JOIN configuracoes.db_systriggers ON db_systriggers.codarq = tmp_systriggers.codarq
    WHERE db_systriggers.codarq IS NULL;

    INSERT INTO configuracoes.db_sysclasses
    SELECT tmp_sysclasses.*
    FROM tmp_sysclasses
             LEFT JOIN configuracoes.db_sysclasses ON db_sysclasses.codarq = tmp_sysclasses.codarq
    WHERE db_sysclasses.codarq IS NULL;

    INSERT INTO configuracoes.db_processa
    SELECT tmp_processa.*
    FROM tmp_processa
         LEFT JOIN configuracoes.db_processa ON db_processa.codarq = tmp_processa.codarq
                                            AND db_processa.id_item = tmp_processa.id_item
    WHERE db_processa.codarq IS NULL;

    INSERT INTO cadastro.iptutabelas
    SELECT tmp_iptutabelas.*
    FROM tmp_iptutabelas
         LEFT JOIN cadastro.iptutabelas ON iptutabelas.j121_sequencial = tmp_iptutabelas.j121_sequencial
    WHERE iptutabelas.j121_sequencial IS NULL;

    INSERT INTO cadastro.iptutabelasdepend
    SELECT tmp_iptutabelasdepend.*
    FROM tmp_iptutabelasdepend
         LEFT JOIN cadastro.iptutabelasdepend
                ON iptutabelasdepend.j128_sequencial = tmp_iptutabelasdepend.j128_sequencial
    WHERE iptutabelasdepend.j128_sequencial IS NULL;

    INSERT INTO cadastro.iptutabelasconfig
    SELECT tmp_iptutabelasconfig.*
    FROM tmp_iptutabelasconfig
         LEFT JOIN cadastro.iptutabelasconfig
                ON iptutabelasconfig.j122_sequencial = tmp_iptutabelasconfig.j122_sequencial
    WHERE iptutabelasconfig.j122_sequencial IS NULL;

    INSERT INTO cadastro.iptutabelasconfigcampochave
    SELECT tmp_iptutabelasconfigcampochave.*
    FROM tmp_iptutabelasconfigcampochave
         LEFT JOIN cadastro.iptutabelasconfigcampochave
                ON iptutabelasconfigcampochave.j124_sequencial = tmp_iptutabelasconfigcampochave.j124_sequencial
    WHERE iptutabelasconfigcampochave.j124_sequencial IS NULL;

    INSERT INTO cadastro.iptutabelasconfigcampocorrecao
    SELECT tmp_iptutabelasconfigcampocorrecao.*
    FROM tmp_iptutabelasconfigcampocorrecao
         LEFT JOIN cadastro.iptutabelasconfigcampocorrecao
                ON iptutabelasconfigcampocorrecao.j123_sequencial = tmp_iptutabelasconfigcampocorrecao.j123_sequencial
    WHERE iptutabelasconfigcampocorrecao.j123_sequencial IS NULL;

    INSERT INTO cadastro.iptutabelasconfigvirada
    SELECT tmp_iptutabelasconfigvirada.*
    FROM tmp_iptutabelasconfigvirada
         LEFT JOIN cadastro.iptutabelasconfigvirada
                ON iptutabelasconfigvirada.j129_sequencial = tmp_iptutabelasconfigvirada.j129_sequencial
    WHERE iptutabelasconfigvirada.j129_sequencial IS NULL;

    INSERT INTO configuracoes.db_cadattdinamicosysarquivo
    SELECT tmp_cadattdinamicosysarquivo.*
    FROM tmp_cadattdinamicosysarquivo
         LEFT JOIN configuracoes.db_cadattdinamicosysarquivo
                ON db_cadattdinamicosysarquivo.db17_sysarquivo = tmp_cadattdinamicosysarquivo.db17_sysarquivo
    WHERE db_cadattdinamicosysarquivo.db17_sysarquivo IS NULL;

    INSERT INTO configuracoes.db_cadtabelacalciptu
    SELECT tmp_cadtabelacalciptu.*
    FROM tmp_cadtabelacalciptu
         LEFT JOIN configuracoes.db_cadtabelacalciptu
                ON db_cadtabelacalciptu.db37_db_sysarquivo = tmp_cadtabelacalciptu.db37_db_sysarquivo
    WHERE db_cadtabelacalciptu.db37_db_sysarquivo IS NULL;

    INSERT INTO configuracoes.db_registrosinconsistentes
    SELECT tmp_registrosinconsistentes.*
    FROM tmp_registrosinconsistentes
             LEFT JOIN configuracoes.db_registrosinconsistentes
                       ON db_registrosinconsistentes.db136_sequencial = tmp_registrosinconsistentes.db136_sequencial
    WHERE db_registrosinconsistentes.db136_sequencial IS NULL;

    INSERT INTO configuracoes.db_registrosinconsistentesdados
    SELECT tmp_registrosinconsistentesdados.*
    FROM tmp_registrosinconsistentesdados
         LEFT JOIN configuracoes.db_registrosinconsistentesdados
                ON db_registrosinconsistentesdados.db137_sequencial = tmp_registrosinconsistentesdados.db137_sequencial
    WHERE db_registrosinconsistentesdados.db137_sequencial IS NULL;

    INSERT INTO configuracoes.db_relattabelas
    SELECT tmp_relattabelas.*
    FROM tmp_relattabelas
         LEFT JOIN configuracoes.db_relattabelas
                ON db_relattabelas.db92_codarq = tmp_relattabelas.db92_codarq
    WHERE db_relattabelas.db92_codarq IS NULL;

    RETURN;
END;
$$
    LANGUAGE plpgsql;

/*
 *
 * fc_atualiza_dicionario_apartir_comentario(text, text, text)
 *
 *   . responsavel por gerar dicionario de dados (tabelas db_sys*) apartir de uma tabela existente no PostgreSQL
 *
 * Parametros;
 *  $1 - tipo de objeto atualizar (valores: schema, table, table column)
 *  $2 - nome do objeto (schema: foo, table: foo.bar, table column: foo.bar.baz)
 *  $3 - comentário no formato JSON
 *
 *  Exemplos:
 *     SELECT fc_atualiza_dicionario_apartir_comentario(
 * 				'table', 'caixa.arrecad', '{"descricao": "Debitos do Contribuinte"}'); -- Tabela caixa.arrecad
 *
 */
CREATE OR REPLACE FUNCTION fc_atualiza_dicionario_apartir_comentario(tipo_obj text, nome_obj text, comentario_obj text)
    RETURNS void AS
$$
DECLARE
    sysmodulo     TEXT;
    sysarquivo    TEXT;
    syscampo      TEXT;
    systabela     TEXT;
    comentario    JSON;
    sql_update    TEXT;
    sql_where     TEXT;
    lista_from    TEXT;
    lista_join    TEXT;
    campos_update TEXT[];
    linhas        INTEGER;
BEGIN
    comentario := comentario_obj::json;
    sysmodulo := split_part(nome_obj, '.', 1);
    sql_where := format('%s = %L',
                        E'regexp_replace(lower(to_ascii(db_sysmodulo.nomemod)), \'[^A-Za-z]\' , \'\', \'g\')',
                        sysmodulo);

    RAISE DEBUG 'tipo_obj: %  nome_obj: %  comentario: %', tipo_obj, nome_obj, comentario_obj;

    CASE tipo_obj
        WHEN 'schema' THEN systabela := 'configuracoes.db_sysmodulo';
                           campos_update := ARRAY ['descricao', 'dataincl', 'ativo'];
        WHEN 'table' THEN sysarquivo := split_part(nome_obj, '.', 2);
                          systabela := 'configuracoes.db_sysarquivo';

                          lista_from := 'configuracoes.db_sysarqmod, configuracoes.db_sysmodulo';
                          lista_join :=
                                  'db_sysarquivo.codarq = db_sysarqmod.codarq AND db_sysarqmod.codmod = db_sysmodulo.codmod';
                          sql_where := format('%s AND %s = %L', sql_where, 'db_sysarquivo.nomearq', sysarquivo);

                          campos_update := ARRAY ['descricao', 'sigla', 'dataincl', 'rotulo', 'tipotabela',
                              'naolibclass', 'naolibfunc', 'naolibprog', 'naolibform'];
        WHEN 'table column' THEN sysarquivo := split_part(nome_obj, '.', 2);
                                 syscampo := split_part(nome_obj, '.', 3);
                                 systabela := 'configuracoes.db_syscampo';

                                 lista_from :=
                                         'configuracoes.db_sysarqcamp, configuracoes.db_sysarquivo, configuracoes.db_sysarqmod, configuracoes.db_sysmodulo';
                                 lista_join := 'db_syscampo.codcam = db_sysarqcamp.codcam';
                                 lista_join := lista_join || ' AND db_sysarqcamp.codarq = db_sysarquivo.codarq';
                                 lista_join := lista_join || ' AND db_sysarqmod.codarq = db_sysarquivo.codarq';
                                 lista_join := lista_join || ' AND db_sysarqmod.codmod = db_sysmodulo.codmod';

                                 sql_where := format('%s AND %s = %L AND %s = %L', sql_where,
                                                     'db_sysarquivo.nomearq', sysarquivo,
                                                     'db_syscampo.nomecam', syscampo);

                                 campos_update := ARRAY ['conteudo', 'descricao', 'valorinicial', 'rotulo', 'tamanho',
                                     'nulo', 'maiusculo', 'autocompl', 'aceitatipo', 'tipoobj', 'rotulorel'];

        -- Atualizar db_syscampodef
                                 DELETE
                                 FROM db_syscampodef
                                 WHERE codcam = (SELECT codcam FROM db_syscampo WHERE nomecam = syscampo);

                                 INSERT INTO db_syscampodef(codcam, defcampo, defdescr)
                                 SELECT codcam, value ->> 'defcampo', value ->> 'defdescr'
                                 FROM json_array_elements(comentario -> 'syscampodef'), db_syscampo
                                 WHERE nomecam = syscampo;
        ELSE RAISE EXCEPTION 'Tipo de objeto % inválido!', tipo_obj;
        END CASE;

    SELECT format('UPDATE %s SET %s', systabela, string_agg(format('%I = %L', key, value), ', '))
    INTO sql_update
    FROM json_each_text(comentario)
    WHERE lower(key) = ANY (campos_update);

    IF lista_from IS NULL THEN
        sql_update := format('%s WHERE %s;', sql_update, sql_where);
    ELSE
        sql_update := format('%s FROM %s WHERE %s AND %s;', sql_update, lista_from, lista_join, sql_where);
    END IF;

    RAISE DEBUG 'sysmodulo: %, sysarquivo: %, syscampo: %, comentario: %',
        sysmodulo, sysarquivo, syscampo, comentario;

    RAISE DEBUG 'sql_update: %', sql_update;

    EXECUTE sql_update;
    GET DIAGNOSTICS linhas = ROW_COUNT;

    RAISE DEBUG 'linhas: %', linhas;

    RETURN;
END;
$$
    LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION configuracoes.fc_dicionario_gatilho_ddl()
    RETURNS event_trigger AS
$$
DECLARE
    r              RECORD;
    _schema_name   TEXT;
    _current_query TEXT;
BEGIN
    IF fc_getsession('__disable_trigger_dicionario__') IS NOT NULL THEN
        RAISE DEBUG 'Event Trigger do Dicionario de Dados Desabilitada';
        RETURN;
    END IF;

    RAISE DEBUG 'Executando a event trigger ddl';
    FOR r IN
        SELECT objid,
               objsubid,
               schema_name,
               pg_class.relname::text AS table_name,
               command_tag,
               object_type,
               object_identity
        FROM pg_event_trigger_ddl_commands()
                 LEFT JOIN pg_class ON pg_class.oid = objid
        WHERE command_tag IN ('CREATE TABLE', 'ALTER TABLE', 'COMMENT')
        LOOP
            RAISE DEBUG 'pg_event_trigger_ddl_commands: %', r;

            -- Apenas algums subcomandos do ALTER TABLE são permitidos como RENAME, ADD, DROP, SET SCHEMA e ALTER
            IF r.command_tag = 'ALTER TABLE' THEN
                _current_query :=
                        trim(regexp_replace(replace(upper(current_query()), 'ALTER TABLE ', ''), '\s+', ' ', 'g'));
                CONTINUE WHEN _current_query !~ '(RENAME|ADD|DROP|SET SCHEMA|ALTER) ';
            END IF;

            IF r.object_type = 'schema' AND r.command_tag = 'COMMENT' THEN
                _schema_name = r.object_identity;
            ELSE
                _schema_name = r.schema_name;
            END IF;

            -- Processa geracao do dicionario de dados apenas se o "esquema" existir na "db_sysmodulo"
            IF EXISTS (SELECT 1
                       FROM configuracoes.db_sysmodulo
                       WHERE regexp_replace(lower(to_ascii(nomemod)), '[^A-Za-z]', '', 'g') = _schema_name) THEN

                -- Processa comandos de tabela
                IF r.object_type = 'table' AND r.command_tag <> 'COMMENT' THEN
                    -- Particoes de tabelas particionadas (db_logsacessa, db_auditoria e debitos) devem ser ignoradas
                    CONTINUE WHEN r.table_name ~ '^(db_logsacessa|db_auditoria|debitos)_[0-9]' OR
                                  r.table_name ~ '^(db_logsacessa|db_auditoria|debitos)$';
                    RAISE DEBUG '<fc_dicionario_gatilho_ddl> Chamando a funcao fc_gera_dicionario_apartir_tabela';
                    RAISE DEBUG '%', format('fc_gera_dicionario_apartir_tabela(%L, %L)', r.schema_name, r.table_name);
                    PERFORM fc_gera_dicionario_apartir_tabela(r.schema_name, r.table_name);

                    -- Processa comandos de comentarios
                ELSIF r.command_tag = 'COMMENT' THEN
                    IF r.object_type = 'table column' THEN
                        PERFORM fc_atualiza_dicionario_apartir_comentario(
                                r.object_type, r.object_identity, pg_catalog.col_description(r.objid, r.objsubid));
                    ELSIF r.object_type = 'table' THEN
                        PERFORM fc_atualiza_dicionario_apartir_comentario(
                                r.object_type, r.object_identity, pg_catalog.obj_description(r.objid, 'pg_class'));
                    ELSIF r.object_type = 'schema' THEN
                        PERFORM fc_atualiza_dicionario_apartir_comentario(
                                r.object_type, r.object_identity, pg_catalog.obj_description(r.objid, 'pg_namespace'));
                    END IF;
                END IF;

                IF r.object_type <> 'schema' THEN
                    -- Salva na sessao tabelas que foram processadas
                    PERFORM fc_putsession('__evtg_dicionario_gatilho_ddl_tables__', array_agg(distinct i)::text)
                    FROM unnest(array_append(fc_getsession('__evtg_dicionario_gatilho_ddl_tables__')::text[],
                                             format('%s.%s', r.schema_name, r.table_name))) AS i;
                END IF;
            END IF;
        END LOOP;

    RETURN;
END;
$$
    SECURITY DEFINER
    LANGUAGE plpgsql;

DROP EVENT TRIGGER IF EXISTS evtg_dicionario_gatilho_ddl;
CREATE EVENT TRIGGER evtg_dicionario_gatilho_ddl
    ON ddl_command_end
    WHEN tag IN ('CREATE TABLE', 'ALTER TABLE', 'COMMENT')
EXECUTE PROCEDURE configuracoes.fc_dicionario_gatilho_ddl();
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;

CREATE OR REPLACE FUNCTION configuracoes.fc_dicionario_gatilho_ddl_drop()
    RETURNS event_trigger AS
$$
DECLARE
    r RECORD;
BEGIN
    IF fc_getsession('__disable_trigger_dicionario__') IS NOT NULL THEN
        RAISE DEBUG 'Event Trigger do Dicionario de Dados Desabilitada';
        RETURN;
    END IF;

    FOR r IN
        SELECT schema_name, object_name AS table_name
        FROM pg_event_trigger_dropped_objects()
        WHERE object_type = 'table'
        LOOP
            -- Processa geracao do dicionario de dados apenas se o "esquema" existir na "db_sysmodulo"
            IF EXISTS (SELECT 1
                       FROM configuracoes.db_sysmodulo
                       WHERE regexp_replace(lower(to_ascii(nomemod)), '[^A-Za-z]', '', 'g') = r.schema_name) THEN
                RAISE DEBUG 'pg_event_trigger_dropped_objects: %', r;
                RAISE DEBUG '<fc_dicionario_gatilho_ddl_drop> 2 - Chamando a funcao fc_remove_dicionario_tabela';
                PERFORM fc_remove_dicionario_tabela(r.schema_name, r.table_name);

                -- Salva na sessao tabelas que foram processadas
                PERFORM fc_putsession('__evtg_dicionario_gatilho_ddl_tables__', array_agg(distinct i)::text)
                FROM unnest(array_append(fc_getsession('__evtg_dicionario_gatilho_ddl_tables__')::text[],
                                         format('%s.%s', r.schema_name, r.table_name))) AS i;
            END IF;
        END LOOP;

    RETURN;
END;
$$
    SECURITY DEFINER
    LANGUAGE plpgsql;

DROP EVENT TRIGGER IF EXISTS evtg_dicionario_gatilho_ddl_drop;
CREATE EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop
    ON sql_drop
EXECUTE PROCEDURE configuracoes.fc_dicionario_gatilho_ddl_drop();
ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;

SQL;
        DB::connection()->getPdo()->exec($sql);

        $sql = <<<SQL

        SELECT configuracoes.fc_auditoria_remove_funcao('recursoshumanos.tipoasse');

        ALTER TABLE recursoshumanos.tipoasse ADD COLUMN h12_gerafaltasperiodoaquisitivo boolean NOT NULL DEFAULT false;

        SELECT configuracoes.fc_auditoria_cria_funcao('recursoshumanos.tipoasse');

        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

    COMMENT ON TABLE recursoshumanos.tipoasse IS '{                                                                                                                                                                             
     "sigla": "h12",                                                                                                                                                                                                       
     "rotulo": "Cadastro dos Tipos de Assentamento",                                                                                                                                                         
     "dataincl": "20031124",                                                                                                                                                                                               
     "descricao": "Cadastro dos Tipos de Assentamento                                                                                                                                                                      ",
     "naolibfor": false,                                                                                                                                                                                                     
     "naolibfunc": false,                                                                                                                                                                                                    
     "naolibprog": false,                                                                                                                                                                                                    
     "tipotabela": 0,                                                                                                                                                                                                        
     "naolibclass": false                                                                                                                                                                                                    
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_assent IS '{                                                                                                                                                                 
     "rotulo": "Código do tipo de assentamento",                                                                                                                                                                                                     
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 5,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Código do tipo de assentamento",                                                                                                                                                                          
     "maiusculo": true,                                                                                                                                                                                                      
     "rotulorel": "Código do tipo de assentamento",                                                                                                                                                                                                  
     "aceitatipo": 0                                                                                                                                                                                                         
     }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_lancamentomensal IS '{                                                                                                                                                       
     "rotulo": "Lançamento Mensal",                                                                                                                                                                                          
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 10,                                                                                                                                                                                                          
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Quantidades de Lançamento Mensais para o Assentamento",                                                                                                                                                  
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Lançamento Mensal",                                                                                                                                                                                       
     "aceitatipo": 1                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_relass IS '{                                                                                                                                                                 
     "rotulo": "Relaciona como Assentamento",                                                                                                                                                                                
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Relaciona como Assentamento",                                                                                                                                                                             
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Relaciona como Assentamento",                                                                                                                                                                             
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_codigo IS '{                                                                                                                                                                 
     "rotulo": "Sequencial Assentamento",                                                                                                                                                                                    
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 6,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Sequencial Assentamento",                                                                                                                                                                                 
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Sequencial Assentamento",                                                                                                                                                                                 
     "aceitatipo": 1                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_graefe IS '{                                                                                                                                                                 
     "rotulo": "Grade efetividade",                                                                                                                                                                                          
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Relaciona na Grade efetividade",                                                                                                                                                                          
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Grade efetividade",                                                                                                                                                                                       
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_gerafaltas IS '{                                                                                                                                                             
     "rotulo": "Gerar Faltas",                                                                                                                                                                                               
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Campo que define se o tipo de assentamento gera ou não faltas no ponto eletrônico.",                                                                                                                      
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Gerar Faltas",                                                                                                                                                                                            
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_tipefe IS '{                                                                                                                                                                 
     "rotulo": "Tipo de efetividade",                                                                                                                                                                                        
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Identifica o tipo de contagem de tempo do assentamento, visto que o intervalo entre as mesmas datas podem ser contados de maneira diferente.",                                                            
     "maiusculo": true,                                                                                                                                                                                                      
     "rotulorel": "Tipo de efetividade",                                                                                                                                                                                     
     "aceitatipo": 0                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_tipo IS '{                                                                                                                                                                   
     "rotulo": "Tipo",                                                                                                                                                                                                       
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Tipo",                                                                                                                                                                                                    
     "maiusculo": true,                                                                                                                                                                                                      
     "rotulorel": "Tipo",                                                                                                                                                                                                    
     "aceitatipo": 0                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_natureza IS '{                                                                                                                                                               
     "rotulo": "Natureza",                                                                                                                                                                                                   
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 19,                                                                                                                                                                                                          
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Natureza do assentamento.",                                                                                                                                                                               
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Natureza",                                                                                                                                                                                                
     "aceitatipo": 1                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_permiteduplicar IS '{                                                                                                                                                        
     "rotulo": "Permite duplicar",                                                                                                                                                                                           
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Campo que verifica se o tipo de assentamento pode ser duplicado.",                                                                                                                                        
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Permite duplicar",                                                                                                                                                                                        
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_relgra IS '{                                                                                                                                                                 
     "rotulo": "Grade de FG",                                                                                                                                                                                                
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Relaciona na grade de FG",                                                                                                                                                                                
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Grade de FG",                                                                                                                                                                                             
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_dias IS '{                                                                                                                                                                   
     "rotulo": "Dias",                                                                                                                                                                                                       
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 6,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Dias para concessao",                                                                                                                                                                                     
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Dias",                                                                                                                                                                                                    
     "aceitatipo": 1                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_descr IS '{                                                                                                                                                                  
     "rotulo": "Descrição",                                                                                                                                                                                                  
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 40,                                                                                                                                                                                                          
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Descrição do tipo de assentamento",                                                                                                                                                                       
     "maiusculo": true,                                                                                                                                                                                                      
     "rotulorel": "Descrição",                                                                                                                                                                                               
     "aceitatipo": 0                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_bloqueioassentamento IS '{                                                                                                                                                   
     "rotulo": "Bloqueio de Assentamentos",                                                                                                                                                                                  
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Campo que habilitado abre dois novos campos que geram limite de lançamento de assentamentos para o servidor",                                                                                             
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Bloqueio de Assentamentos",                                                                                                                                                                               
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_vinculaperiodoaquisitivo IS '{                                                                                                                                               
     "rotulo": "Vincular Período Aquisitivo de Férias",                                                                                                                                                                      
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Vincular Período Aquisitivo de Férias",                                                                                                                                                                   
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Vincular Período Aquisitivo de Férias",                                                                                                                                                                   
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_efetiv IS '{                                                                                                                                                                 
     "rotulo": "Efetividade",                                                                                                                                                                                                
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Tipo de Efetividade",                                                                                                                                                                                     
     "maiusculo": true,                                                                                                                                                                                                      
     "rotulorel": "Efetividade",                                                                                                                                                                                             
     "aceitatipo": 0                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_lancamentoanual IS '{                                                                                                                                                        
     "rotulo": "Lançamento Anual",                                                                                                                                                                                           
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 10,                                                                                                                                                                                                          
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Quantidade de Lançamentos Anuais para o Assentamento",                                                                                                                                                    
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Lançamento Anual",                                                                                                                                                                                        
     "aceitatipo": 1                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_relvan IS '{                                                                                                                                                                 
     "rotulo": "Relaciona com Vantagem",                                                                                                                                                                                     
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Relaciona com Vantagem",                                                                                                                                                                                  
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Relaciona com Vantagem",                                                                                                                                                                                  
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_tiporeajuste IS '{                                                                                                                                                           
     "rotulo": "Tipo de Reajuste Salarial",                                                                                                                                                                                  
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 10,                                                                                                                                                                                                          
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Tipo de Reajuste Salarial",                                                                                                                                    
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Tipo de Reajuste Salarial",                                                                                                                                                                               
     "aceitatipo": 1,

        "syscampodef": [ 
         {
           "defcampo": "0", 
           "defdescr": "Nenhum"
         },
         {
           "defcampo": "1",
           "defdescr": "Real"
         },
         {
           "defcampo": "2",
           "defdescr": "Paridade"
         }
        ]                                                                                                                                                                              
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_reltot IS '{                                                                                                                                                                 
     "rotulo": "Totais",                                                                                                                                                                                                     
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Relaciona nos totais",                                                                                                                                                                                    
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Totais",                                                                                                                                                                                                  
     "aceitatipo": 0                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_regenc IS '{                                                                                                                                                                 
     "rotulo": "Regência",                                                                                                                                                                                                   
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Tipo de regencia",                                                                                                                                                                                        
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Regência",                                                                                                                                                                                                
     "aceitatipo": 5                                                                                                                                                                                                         
    }';                                                                                                                                                                                                                         
 
    COMMENT ON COLUMN recursoshumanos.tipoasse.h12_gerafaltasperiodoaquisitivo IS '{                                                                                                                                                                 
     "rotulo": "Gera Faltas Periodo Aquisitivo",                                                                                                                                                                                                   
     "tipoob": "text",                                                                                                                                                                                                       
     "tamanho": 1,                                                                                                                                                                                                           
     "autocompl": false,                                                                                                                                                                                                     
     "descricao": "Campo que determina se as faltas serao geradas no periodo aquisitivo",                                                                                                                                                                                        
     "maiusculo": false,                                                                                                                                                                                                     
     "rotulorel": "Gera Faltas Periodo Aquisitivo",                                                                                                                                                                                                
     "aceitatipo": 5                                                                                                                                                                                                         
    }';     

       SELECT fc_gera_dicionario_apartir_tabela('recursoshumanos', 'tipoasse');

       ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
       ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
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
        $sql = <<<SQL

        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

        SELECT configuracoes.fc_auditoria_remove_funcao('recursoshumanos.tipoasse');

        ALTER TABLE recursoshumanos.tipoasse DROP COLUMN h12_gerafaltasperiodoaquisitivo;

        SELECT configuracoes.fc_auditoria_cria_funcao('recursoshumanos.tipoasse');

        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
SQL;
        DB::connection()->getPdo()->exec($sql);
    }
}
