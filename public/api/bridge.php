<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL); ini_set('display_errors', 0);

/* читаем JSON */
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (!is_array($in) || !isset($in['path'], $in['content'])) {
    http_response_code(400);
    echo json_encode(['error' => 'bad request']); exit;
}

/* нормализуем параметры */
$branch = isset($in['branch']) ? preg_replace('~[^a-z0-9._-]~i', '-', $in['branch']) : 'test';
$msg    = isset($in['message']) ? (string)$in['message'] : 'Bridge auto commit';

$repo   = '/var/www/test.myaibots.ru';
$target = $repo . '/' . ltrim($in['path'], '/');

/* создаём каталог и пишем файл */
$dir = dirname($target);
if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
    http_response_code(500);
    echo json_encode(['error' => 'mkdir failed', 'dir' => $dir]); exit;
}
if (file_put_contents($target, $in['content']) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'write failed', 'file' => $target]); exit;
}

/* вызываем скрипт коммита (PHP/FPM и так работает от www-data) */
$cmd = '/usr/local/bin/bridge_git_commit.sh ' . escapeshellarg($branch) . ' ' . escapeshellarg($msg);
exec($cmd . ' 2>&1', $out, $code);

echo json_encode([
    'ok'        => $code === 0,
    'exit_code' => $code,
    'script'    => $cmd,
    'output'    => implode("\n", $out),
], JSON_UNESCAPED_UNICODE);
