-- Script para adicionar campos de backup de nota anterior na tabela aprovconselho
-- Data: 2025-09-29
-- Demanda: Restaurar nota do 4º bimestre ao excluir aprovação pelo conselho

BEGIN;

-- Adicionar campo para armazenar a nota anterior (antes da alteração pelo conselho)
ALTER TABLE aprovconselho
ADD COLUMN IF NOT EXISTS ed253_notaanterior DOUBLE PRECISION;

COMMENT ON COLUMN aprovconselho.ed253_notaanterior IS
'Valor da nota antes da alteração pelo conselho (para restauração ao excluir a aprovação)';

-- Adicionar campo para armazenar qual período foi alterado
ALTER TABLE aprovconselho
ADD COLUMN IF NOT EXISTS ed253_periodoalterado INTEGER;

COMMENT ON COLUMN aprovconselho.ed253_periodoalterado IS
'Código do período que foi alterado (ed09_i_codigo: 4=4º bimestre, 8=3º trimestre, 10=Prova Final)';
COMMIT;

BEGIN;
-- continuacao
ALTER TABLE aprovconselho
ADD COLUMN ed253_conceitoanterior CHAR(3),
ADD COLUMN ed253_pareceranterior TEXT,
ADD COLUMN ed253_tipoalteracao CHAR(1);

COMMENT ON COLUMN aprovconselho.ed253_conceitoanterior IS
'Conceito anterior antes da alteração pelo conselho (para restauração ao excluir)';

COMMENT ON COLUMN aprovconselho.ed253_pareceranterior IS
'Parecer anterior antes da alteração pelo conselho (para restauração ao excluir)';

COMMENT ON COLUMN aprovconselho.ed253_tipoalteracao IS
'Tipo de alteração: N=Nota, C=Conceito (NIVEL), P=Parecer';
COMMIT;
