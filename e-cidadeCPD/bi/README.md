# BI com Apache Superset

Esta pasta é a fronteira de analytics do e-Cidade. O Superset não deve usar a
conexão nem as credenciais operacionais da aplicação; ele consulta apenas views
revisadas no schema PostgreSQL `bi`, por um usuário somente leitura.

## Subida local

1. Copie `bi/.env.example` para `bi/.env` e preencha todas as senhas com valores
   exclusivos. Gere `SUPERSET_SECRET_KEY` com `openssl rand -base64 42`.
2. Revise e execute `bi/sql/001_bi_role_and_schema.sql` com um administrador do
   PostgreSQL. Crie em seguida views sem PII no schema `bi`.
3. Inicie a infraestrutura isolada:

   ```bash
   docker compose -f docker-compose.superset.yml --env-file bi/.env up -d --build
   docker compose -f docker-compose.superset.yml --env-file bi/.env run --rm superset-init
   ```

4. Acesse `http://localhost:8088`, autentique-se com o administrador definido no
   `.env` e cadastre a fonte PostgreSQL usando a URL indicada por
   `ECIDADE_ANALYTICS_URI` em `bi/superset/superset_config.py`.

O compose principal do e-Cidade permanece inalterado. Nesta instalação, apenas
o serviço `superset` também entra na rede externa `containers_default`, usando o
alias `ecidade:5433` do PostgreSQL operacional. Os metadados e o Redis continuam
na rede exclusiva `ecidade_superset`. Em outra instalação, informe o DNS/IP e a
porta reais em `ECIDADE_DB_HOST` e `ECIDADE_DB_PORT`.

## Primeiro conjunto de painéis

Comece por um domínio e por dados agregados: execução orçamentária, arrecadação,
atendimentos ou educação. Para cada indicador defina uma view `bi.*` com: período,
unidade/município, dimensões permitidas, métrica agregada e dono do dado. Só então
crie datasets e gráficos no Superset. Evite expor CPF, nomes, prontuários ou dados
sensíveis em datasets analíticos.

O primeiro painel está descrito em
`dashboards/balancete_receita_por_recurso_prefeitura_2025.md`. Publique primeiro
`sql/005_balancete_receita_por_recurso_dinamico.sql`, valide os totais contra o
balancete oficial e só então disponibilize o dashboard. O artefato `003_*`
permanece como referência do recorte inicial homologado de 2025.

## Incorporação no e-Cidade

`EMBEDDED_SUPERSET` está habilitado. A página interna do Balancete solicita um
guest token ao backend e o backend valida a sessão/escopo antes de chamar o
Superset. Configure no `.env` da aplicação, fora do versionamento, a conta de
serviço e o UUID de incorporação do dashboard. Não exponha senha,
`SUPERSET_SECRET_KEY` ou token de serviço no JavaScript. Em homologação, o
dashboard inicial aceita `http://localhost:5000`; revise os domínios permitidos,
TLS, política de frame e permissões antes da publicação.

O Balancete da Receita recebe exercício e período da barra integrada. O backend
limita a instituição à sessão e-Cidade, gera um cache agregado com chave opaca e
inclui no guest token uma cláusula RLS exclusiva para aquele recorte. O Superset
continua usando apenas `ecidade_bi_readonly`, com `SELECT` na view
`bi.balancete_receita_por_recurso`; a tabela de cache não é exposta diretamente.

## Operação

Para uma implantação nova em produção, siga integralmente
[`docs/implantacao-bi-producao.md`](../docs/implantacao-bi-producao.md). O pacote
oficial importável está em `superset/assets/receita_por_fonte_dashboard.zip` e
não contém a senha real da conexão.

- Faça backup periódico do volume `superset_postgres_data`: ele contém usuários,
  dashboards, gráficos e conexões do Superset.
- Exporte dashboards/datasets para versionamento após aprovados.
- Atualize Superset primeiro em homologação; a imagem está fixada em `6.0.0`.
- Não use SQLite para os metadados e não exponha a porta 8088 publicamente sem
  TLS, autenticação e regras de rede adequadas.
