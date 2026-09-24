# Pacote de Deploy Seguro: Módulo LGPD (Governança & Conformidade) - e-Cidade CPD

Este pacote contém todos os artefatos necessários para implantar o **Painel de Governança e Conformidade LGPD** no servidor remoto com **100% de segurança**, **zero risco de quebra** e total compatibilidade com PHP 5.6.40 e PostgreSQL.

---

## 📦 Conteúdo do Pacote

| Arquivo | Finalidade |
| :--- | :--- |
| `con4_lgpd_painel001.php` | Script da interface visual (4 abas: ROPA Art. 37, Termos de Sigilo, Trilha de Auditoria, Dossiê do Titular Art. 18 com Privacy by Default). Codificação UTF-8 blindada. |
<<<<<<< HEAD
=======
| `lgpd_schema_migration.sql` | Script SQL 100% idempotente (transacional com `BEGIN...COMMIT`) para registro do módulo, menu e permissões. |
>>>>>>> main
| `lgpd_migration_remote.sql` | Script SQL 100% idempotente (transacional com `BEGIN...COMMIT`) para registro do módulo, menu e permissões. |
| `deploy_remote_lgpd.sh` | Script bash de implantação automatizada no servidor remoto. |

---

## 🛡️ Garantias de Segurança (Zero Breakage)

1. **Sem Modificação de Código Central**: O script não sobrescreve nenhum arquivo nativo do e-Cidade.
2. **Idempotência no PostgreSQL**: A migração verifica `IF NOT EXISTS` antes de qualquer inserção. Se executada várias vezes, não duplica nem trava nada.
3. **Compatibilidade Dual Webroot**: O arquivo PHP é instalado tanto na raiz `/var/www/html/` quanto em `/var/www/html/e-cidadeCPD/`.
4. **Sintaxe PHP 5.6.40**: Sem uso de recursos modernos incompatíveis com o PHP legado.

---

## 🚀 Como Executar o Deploy no Servidor Remoto

### Opção 1: Execução Automatizada via Bash (Recomendado)

1. Transfira a pasta `deploy_lgpd/` para o servidor remoto:
   ```bash
   scp -P 20012 -r deploy_lgpd cpd@177.39.18.200:/tmp/
   ```

2. Acesse o servidor e execute o instalador:
   ```bash
   ssh -p 20012 cpd@177.39.18.200
   cd /tmp/deploy_lgpd
   chmod +x deploy_remote_lgpd.sh
   sudo ./deploy_remote_lgpd.sh
   ```

---

### Opção 2: Implantação Manual Passo a Passo

Se preferir realizar a operação manualmente:

1. **Copiar o arquivo PHP para os webroots**:
   ```bash
<<<<<<< HEAD
=======
   sudo cp con4_lgpd_painel001.php /var/www/html/
   sudo cp con4_lgpd_painel001.php /var/www/html/e-cidadeCPD/
   sudo chown www-data:www-data /var/www/html/con4_lgpd_painel001.php /var/www/html/e-cidadeCPD/con4_lgpd_painel001.php
   sudo chmod 644 /var/www/html/con4_lgpd_painel001.php /var/www/html/e-cidadeCPD/con4_lgpd_painel001.php
>>>>>>> main
   sudo cp con4_lgpd_painel001.php /var/www/html/e-cidade_ontem/
   sudo chown www-data:www-data /var/www/html/e-cidade_ontem/con4_lgpd_painel001.php
   sudo chmod 644 /var/www/html/e-cidade_ontem/con4_lgpd_painel001.php
   ```

2. **Executar a Migração no Banco de Dados**:
   ```bash
<<<<<<< HEAD
=======
   # Diretamente no host ou via container Docker:
   docker exec -i e_cidade_container psql -U ecidade -d ecidade < lgpd_schema_migration.sql
>>>>>>> main
   PGPASSWORD='...' psql -h 127.0.0.1 -U ecidade -d ecidade -f lgpd_migration_remote.sql
   ```

3. **Verificar no e-Cidade Desktop**:
   - Faça login no e-Cidade.
   - Navegue em: `Instituições` $\rightarrow$ `DB:CONFIGURAÇÃO` $\rightarrow$ `LGPD - Governança` $\rightarrow$ `Painel de Governança LGPD`.
