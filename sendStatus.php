<?php
header("Content-Type: application/json");

$baseDir = __DIR__;
$estadoDir = $baseDir . "/statuses";
if (!is_dir($estadoDir)) {
    mkdir($estadoDir, 0777, true);
}

function getStatusFile($txid) {
    global $estadoDir;
    $safeTxid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $txid);
    return "$estadoDir/estado_botones_$safeTxid.json";
}

// GET → Consultar estado
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["txid"])) {
    $txid = $_GET["txid"];
    $file = getStatusFile($txid);

    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo json_encode(["status" => "esperando"]);
    }
    exit;
}

// POST → Actualizar estado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    $status = $input["status"] ?? "sin_status";
    $txid = $_GET["txid"] ?? uniqid("manual_");
    $file = getStatusFile($txid);

    file_put_contents($file, json_encode(["status" => $status]));
    echo json_encode(["ok" => true]);
    exit;
}

// Método no permitido
http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
exit;
?>
