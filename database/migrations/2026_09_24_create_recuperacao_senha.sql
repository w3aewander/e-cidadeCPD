-- ==============================================================================
-- MIGRAÇÃO: TABELA DE RECUPERAÇÃO SEGURA DE SENHA
-- ==============================================================================
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
