<?php
header("Content-Type: application/json; charset=utf-8");

// Загружаем ключ из файла
$env = parse_ini_file("/etc/myaibots/openai.env");
$apiKey = $env["OPENAI_API_KEY"] ?? null;

if (!$apiKey) {
    echo json_encode(["error" => "API key not found"]);
    exit;
}

// Пример запроса к OpenAI (Chat Completions)
$data = [
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "system", "content" => "You are a helpful assistant."],
        ["role" => "user", "content" => "Скажи привет Сергею!"]
    ],
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiKey,
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
if ($response === false) {
    echo json_encode(["error" => curl_error($ch)]);
} else {
    echo $response;
}
curl_close($ch);
