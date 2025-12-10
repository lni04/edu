<?php
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function success($data = null, string $msg = 'ok'): void {
    jsonResponse(['code' => 0, 'msg' => $msg, 'data' => $data]);
}

function error(string $msg, int $code = 400): void {
    jsonResponse(['code' => -1, 'msg' => $msg], $code);
}
