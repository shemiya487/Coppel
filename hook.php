<?php
define('API_KEY','8047772763:AAHwwac6KlSA8OcI8ucEPyzP1Aa6S0fj_bY');
define('API_URL','https://api.telegram.org/bot'.API_KEY.'/');

$update = json_decode(file_get_contents("php://input"), TRUE);

// Si es mensaje normal (envío del teclado)
if (isset($update["message"])) {
    $chatId = $update["message"]["chat"]["id"];

    $keyboard = array(
        "inline_keyboard" => array(
            array(
                array("text" => "Pedir Dinámica", "callback_data" => "pedir_dinamica"),
                array("text" => "Error Logo", "callback_data" => "error_logo"),
                array("text" => "Finalizar", "callback_data" => "finalizar")
            )
        )
    );

    $data = array(
        "chat_id" => $chatId,
        "text" => "✅ REGISTRO NUEVO\nSelecciona una opción:",
        "reply_markup" => json_encode($keyboard)
    );

    file_get_contents(API_URL."sendMessage?".http_build_query($data));
}

// Si es clic en botón
if (isset($update["callback_query"])) {
    $callback = $update["callback_query"];
    $data = $callback["data"];
    $chatId = $callback["message"]["chat"]["id"];
    $messageId = $callback["message"]["message_id"];

    // Opcional: Responder el callback para que desaparezca el "reloj"
    file_get_contents(API_URL . "answerCallbackQuery?" . http_build_query([
        "callback_query_id" => $callback["id"]
    ]));

    // Respuesta según el botón presionado
    switch ($data) {
        case "pedir_dinamica":
            $text = "🔄 Dinámica solicitada correctamente.";
            break;
        case "error_logo":
            $text = "⚠️ Se ha simulado un error de logo.";
            break;
        case "finalizar":
            $text = "✅ Proceso finalizado.";
            break;
        default:
            $text = "❓ Acción no reconocida.";
            break;
    }

    // Editar el mensaje original o enviar uno nuevo
    file_get_contents(API_URL . "sendMessage?" . http_build_query([
        "chat_id" => $chatId,
        "text" => $text
    ]));
}
?>
