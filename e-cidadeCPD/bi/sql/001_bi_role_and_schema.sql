-- Executar uma vez por um administrador PostgreSQL, no banco e-Cidade.
-- Ajuste a senha antes de executar e armazene-a apenas em bi/.env.
-- Este usuário não recebe acesso às tabelas operacionais nem permissão de escrita.

CREATE ROLE ecidade_bi_readonly LOGIN PASSWORD 'SUBSTITUA_POR_UMA_SENHA_FORTE';

CREATE SCHEMA IF NOT EXISTS bi AUTHORIZATION CURRENT_USER;
REVOKE ALL ON SCHEMA bi FROM PUBLIC;
GRANT USAGE ON SCHEMA bi TO ecidade_bi_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA bi TO ecidade_bi_readonly;
ALTER DEFAULT PRIVILEGES IN SCHEMA bi
  GRANT SELECT ON TABLES TO ecidade_bi_readonly;

-- Publique somente views aprovadas neste schema. Exemplo (NÃO executar sem
-- validar tabelas, chaves de município e política de anonimização):
-- CREATE OR REPLACE VIEW bi.indicador_exemplo AS
-- SELECT ano, mes, total
-- FROM schema_origem.fato_aprovada;
