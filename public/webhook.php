<?php
http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');

$raw = file_get_contents('php://input');
$p   = json_decode($raw, true);
$ref = $p['ref'] ?? '';

$log = '/var/www/test.myaibots.ru/public/deploy_test.log';
file_put_contents($log, "Webhook: $ref\n", FILE_APPEND);

if ($ref !== 'refs/heads/test') {
    file_put_contents($log, "Skip\n", FILE_APPEND);
    exit('skip');
}

// Запускаем скрипт напрямую (совпадает с sudoers)
$cmd = 'sudo -u myaibots /var/www/test.myaibots.ru/deploy_test.sh >> '.$log.' 2>&1 &';
file_put_contents($log, "Run: $cmd\n", FILE_APPEND);
exec($cmd);

echo "Deploy started\n";
