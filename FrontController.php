<?php
$_SERVER["QUERY_STRING"] = escapeSqlKeywords($_SERVER["QUERY_STRING"]);

$requestUri = $_SERVER['REQUEST_URI'];

if (preg_match('/[^?]*api\/v\d\//', $requestUri)) {
    return require_once 'api/api.php';
}

if (preg_match('/.*v4\//', $requestUri)) {
    return require_once 'public/index.php';
}

if (preg_match('/rest\/v1\//', $requestUri)) {
    return require_once 'public/index.php';
}

return require('app.php');

function escapeSqlKeywords($text)
{
    // Lista de palavras-chave SQL comuns
    $sqlKeywords = [
        'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'FROM', 'WHERE', 'AND', 'OR',
        'GROUP BY', 'ORDER BY', 'HAVING', 'JOIN', 'INNER JOIN', 'LEFT JOIN',
        'RIGHT JOIN', 'FULL JOIN', 'UNION', 'UNION ALL', 'LIMIT', 'OFFSET',
        'AS', 'DISTINCT', 'INTO', 'VALUES', 'SET', 'ON', 'IS NULL', 'IS NOT NULL',
        'LIKE', 'IN', 'BETWEEN', 'EXISTS', 'ANY', 'ALL', 'CASE', 'WHEN', 'THEN',
        'ELSE', 'END', 'NOT', 'CREATE', 'ALTER', 'DROP', 'TABLE', 'INDEX',
        'VIEW', 'PROCEDURE', 'FUNCTION', 'TRIGGER', 'GRANT', 'REVOKE', 'COMMIT',
        'ROLLBACK', 'SAVEPOINT', 'TRANSACTION', 'BEGIN', 'DECLARE', 'EXECUTE'
    ];

    // Escape cada palavra-chave no texto
    foreach ($sqlKeywords as $keyword) {
        $text = preg_replace('/\b' . $keyword . '\b/i', '[' . $keyword . ']', $text);
    }

    if (base64_decode($text, true)) {
        $text = base64_encode(escapeSqlKeywords(base64_decode($text)));
    }

    return $text;
}
