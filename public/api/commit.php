<?php
header('Content-Type: application/json; charset=utf-8');

// ---- секрет из файла
$secretFile = '/etc/myaibots/gh_webhook_secret';
$secret = @file_get_contents($secretFile);
if ($secret === false) {
  http_response_code(500);
  echo json_encode(['error' => 'secret not available']);
  exit;
}
$secret = trim($secret);

// ---- читаем ключ из заголовков X-API-Key (включая вариант через $_SERVER)
$headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
$hdrKey = '';
if ($headers) {
  $norm = [];
  foreach ($headers as $k => $v) $norm[strtoupper($k)] = $v;
  $hdrKey = $norm['X-API-KEY'] ?? '';
}
if (!$hdrKey && isset($_SERVER['HTTP_X_API_KEY'])) $hdrKey = $_SERVER['HTTP_X_API_KEY'];
$hdrKey = trim($hdrKey);

// ---- проверка секрета (временную метку и лишние пробелы отрезаем)
if (!$hdrKey || !hash_equals($secret, $hdrKey)) {
  http_response_code(403);
  echo json_encode(['error' => 'forbidden']);
  exit;
}

// ---- читаем JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['path'],$data['content'],$data['message'],$data['branch'])) {
  http_response_code(400);
  echo json_encode(['error' => 'bad request', 'raw' => $raw]);
  exit;
}

// ---- путь в репо и запись файла
$repoPath = '/var/www/test.myaibots.ru';
$filePath = $repoPath . '/' . ltrim($data['path'], '/');
@mkdir(dirname($filePath), 0775, true);
file_put_contents($filePath, $data['content']);

// ---- git commit + push
$branch = escapeshellarg($data['branch']);
$path   = escapeshellarg($data['path']);
$msg    = escapeshellarg($data['message']);
$cmds = [
  "cd $repoPath",
  "git checkout $branch",
  "git add $path",
  "git commit -m $msg || echo 'no changes'",
  "git push origin $branch"
];
$out = shell_exec(implode(' && ', $cmds) . ' 2>&1');

echo json_encode(['status' => 'ok', 'output' => $out], JSON_UNESCAPED_UNICODE);
