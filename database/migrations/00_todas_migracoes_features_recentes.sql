-- ==============================================================================
-- CONSOLIDAÇÃO DE TODAS AS MIGRAÇÕES EXECUTADAS RECENTEMENTE NO CPD-MUNICIPAL
-- 1. BI Apache Superset (Schema & Procedure)
-- 2. Manuais da Educação
-- 3. Governança LGPD (Módulo, Menus e Permissões)
-- 4. Subsistema de Autenticação Segura (3 tentativas & Recuperação)
-- ==============================================================================

-- 1. BI SUPERSET
CREATE SCHEMA IF NOT EXISTS bi;

-- 2. MANUAIS DA EDUCAÇÃO
-- Ver arquivo: 2026_09_22_create_manuais_educacao.sql

-- 3. GOVERNANÇA LGPD
-- Ver arquivo: 2026_09_24_create_lgpd_schema.sql

-- 4. NOVO SISTEMA DE LOGIN E RECUPERAÇÃO DE SENHA
CREATE TABLE IF NOT EXISTS configuracoes.db_usuariorecuperasenha (
    id SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    login VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_db_usuariorecuperasenha_token 
    ON configuracoes.db_usuariorecuperasenha(token);
