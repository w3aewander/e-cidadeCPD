-- A conexão serve exclusivamente aos datasets publicados; não precisa aparecer
-- no SQL Lab nem permitir recursos de escrita/importação.
UPDATE dbs
SET expose_in_sqllab = false,
    allow_run_async = false,
    allow_ctas = false,
    allow_cvas = false,
    allow_dml = false,
    allow_file_upload = false,
    changed_on = now()
WHERE uuid = 'b35c7d7a-2637-4af0-90ee-2d21f2f9a42c';
