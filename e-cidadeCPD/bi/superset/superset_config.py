"""Configuração versionada da instância BI do e-Cidade.

Segredos permanecem em bi/.env; este arquivo não deve conter credenciais.
"""
import os

SECRET_KEY = os.environ["SUPERSET_SECRET_KEY"]
SQLALCHEMY_DATABASE_URI = os.environ["SQLALCHEMY_DATABASE_URI"]

ROW_LIMIT = 50000
SUPERSET_WEBSERVER_TIMEOUT = 120
WTF_CSRF_ENABLED = True
ENABLE_PROXY_FIX = True

# Um dashboard incorporado carrega diversos metadados e chunks em paralelo.
# O padrão de 50 req/s do Superset interrompe essa inicialização com HTTP 429.
# Mantemos o limitador ativo, com estado compartilhado no Redis, e margem apenas
# para o pico legítimo de abertura do painel.
RATELIMIT_ENABLED = True
RATELIMIT_APPLICATION = "200 per second"
RATELIMIT_STORAGE_URI = "redis://{host}:6379/1".format(
    host=os.environ.get("REDIS_HOST", "superset-redis")
)

CACHE_CONFIG = {
    "CACHE_TYPE": "RedisCache",
    "CACHE_DEFAULT_TIMEOUT": 300,
    "CACHE_KEY_PREFIX": "ecidade_superset_",
    "CACHE_REDIS_HOST": os.environ.get("REDIS_HOST", "superset-redis"),
    "CACHE_REDIS_PORT": 6379,
}
DATA_CACHE_CONFIG = CACHE_CONFIG

# Habilitado para a futura incorporação autenticada. Nenhum token é emitido
# nesta camada; o backend e-Cidade deverá fazê-lo depois de validar permissões.
FEATURE_FLAGS = {"EMBEDDED_SUPERSET": True}

# O guest token restringe o recurso ao UUID do dashboard. A role Gamma fornece
# somente as capacidades funcionais de leitura necessárias para o dashboard;
# o acesso efetivo ao recurso continua limitado pelas claims do token.
GUEST_ROLE_NAME = "Gamma"

# O dashboard incorporado é servido por localhost:8088 dentro do Desktop do
# e-Cidade em localhost:5000. Mantemos a CSP restritiva do Superset e liberamos
# somente essa origem como ancestral do iframe. X-Frame-Options não representa
# allowlists entre origens; a restrição equivalente fica em frame-ancestors.
TALISMAN_CONFIG = {
    "content_security_policy": {
        "base-uri": ["'self'"],
        "default-src": ["'self'"],
        "img-src": [
            "'self'",
            "blob:",
            "data:",
            "https://apachesuperset.gateway.scarf.sh",
            "https://static.scarf.sh/",
            "ows.terrestris.de",
            "https://cdn.document360.io",
        ],
        "worker-src": ["'self'", "blob:"],
        "connect-src": [
            "'self'",
            "https://api.mapbox.com",
            "https://events.mapbox.com",
            "https://tile.openstreetmap.org",
            "https://tile.osm.ch",
        ],
        "object-src": ["'none'"],
        "style-src": ["'self'", "'unsafe-inline'"],
        "script-src": ["'self'", "'strict-dynamic'"],
        "frame-ancestors": [
            "'self'",
            os.environ.get("ECIDADE_PUBLIC_ORIGIN", "http://localhost:5000"),
        ],
    },
    "content_security_policy_nonce_in": ["script-src"],
    "frame_options": None,
    "force_https": False,
    "session_cookie_secure": False,
}

# A conexão operacional deve ser cadastrada no Superset com esta URL, usando
# exclusivamente o usuário de leitura provisionado pelo SQL do projeto.
ECIDADE_ANALYTICS_URI = (
    "postgresql+psycopg2://{user}:{password}@{host}:{port}/{database}".format(
        user=os.environ.get("ECIDADE_BI_USER", ""),
        password=os.environ.get("ECIDADE_BI_PASSWORD", ""),
        host=os.environ.get("ECIDADE_DB_HOST", ""),
        port=os.environ.get("ECIDADE_DB_PORT", "5432"),
        database=os.environ.get("ECIDADE_DB_NAME", ""),
    )
)
