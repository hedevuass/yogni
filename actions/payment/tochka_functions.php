<?php
// actions/payment/tochka_functions.php
// Общие функции для tochka_invoice.php и tochka_invoice_requests.php (перенесены без изменений)

if (!function_exists('isValidInn')) {
    function isValidInn(string $inn, string $type): bool
    {
        $len = strlen($inn);

        if ($type === 'company' && $len === 10) {
            $coeffs = [2, 4, 10, 3, 5, 9, 4, 6, 8];
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += $coeffs[$i] * (int)$inn[$i];
            }
            $control = ($sum % 11) % 10;
            return $control === (int)$inn[9];
        }

        if ($type === 'ip' && $len === 12) {
            $coeffs1 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
            $coeffs2 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];

            $sum1 = 0;
            for ($i = 0; $i < 10; $i++) {
                $sum1 += $coeffs1[$i] * (int)$inn[$i];
            }
            $control1 = ($sum1 % 11) % 10;

            $sum2 = 0;
            for ($i = 0; $i < 11; $i++) {
                $sum2 += $coeffs2[$i] * (int)$inn[$i];
            }
            $control2 = ($sum2 % 11) % 10;

            return $control1 === (int)$inn[10] && $control2 === (int)$inn[11];
        }

        return false;
    }
}

if (!function_exists('apiGetRequest')) {
    function apiGetRequest(string $url, string $authToken): array
    {
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "Authorization: Bearer $authToken\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $respJson = @file_get_contents($url, false, $context);
        if ($respJson === false) {
            return ['error' => 'GET request вернул false'];
        }
        return json_decode($respJson, true) ?: ['error' => 'Невалидный JSON в GET response'];
    }
}

if (!function_exists('apiPostRequest')) {
    function apiPostRequest(string $url, string $authToken, array $postData): array
    {
        $data = json_encode($postData, JSON_UNESCAPED_UNICODE);
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Authorization: Bearer $authToken\r\n" .
                    "Content-Type: application/json\r\n" .
                    "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data,
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($opts);
        $respJson = @file_get_contents($url, false, $context);
        if ($respJson === false) {
            return ['error' => 'POST request вернул false'];
        }
        return json_decode($respJson, true) ?: ['error' => 'Невалидный JSON в POST response'];
    }
}