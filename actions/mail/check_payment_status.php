<?php

$authToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ6ekhySFVta1lUU3ZuMERVdGdnVnNMRHF0TnBYaGVrUiJ9.jUpIG6PtuYA7jG59dzw-9r6mwPWvJGyTI-rDI_Sx4EIhakXYt48wy4UHavP9UR5Yy_U3LJDVFN0w7Bimrd3B8aRAvI4yDt1ZneMwfkOiYLato9ZLkKrZqHQjgty8kKEuCKf1-gEKcSYFor9sdFP4XBjRIBenxDKYjjI9Lb6UMFXUr_ET9QsIySvE8Boo6MSIneucWuXRIQnQRcLDwbde7UmfmQpK7HzG4dlNU1Y-6TSxqdbCnM8YQ90Hh-2R0stR4oNGbWxlhvaIIEPDsdmQXyHWJvzYzzktRn5uI51rOHs02j9uIXEcnHyn3Ctp7Du8FagdppCz-KJmflkxXejNON7nHUJaTOpbRjRQAZdw_YV2hZwxZd4NxiuNr4dizH6rbVkevs748SkXZzCPvMvKUnUZPciylsvByjTRJLyets01Ix6PRM695J5bchWCmlzi6uGamIm-sGSFtNjq6JvdNkKgGQbx5f34oeY5Owpb7MhWVh1gfNxRH9UIDCqN-UM_';
$documentId = $_GET['documentId'] ?? null;
$customerCode = $_GET['customerCode'] ?? null;

if (!$documentId || !$customerCode) exit('Missing params');

$url = "https://enter.tochka.com/uapi/invoice/v1.0/bills/$customerCode/$documentId";
$opts = ["http" => ["method" => "GET", "header" => "Authorization: Bearer $authToken\r\n"]];
$ctx = stream_context_create($opts);
$response = file_get_contents($url, false, $ctx);
$data = json_decode($response, true);

if (($data['Data']['Invoice']['status'] ?? '') === 'paid') {
    $comment = $data['Data']['Invoice']['comment'] ?? '';
    $promoData = json_decode($comment, true);

    if ($promoData && isset($promoData['promos'])) {
        include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';

        foreach ($promoData['promos'] as $promo) {
            foreach ($promo['service_ids'] as $service_id) {
                add_promo_placement(
                    $promo['specialist_id'],
                    $promo['promo_id'],
                    $service_id,
                    $promo['start_date'],
                    $promo['end_date'],
                    $promo['price'],
                    $promo['service_name']
                );
            }
        }
        echo 'OK';
    } else {
        echo 'No promos';
    }
} else {
    echo 'Not paid';
}