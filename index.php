<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Health check endpoint
    echo json_encode([
        'status' => 'HMAC Service running',
        'endpoints' => [
            'POST /hmac' => 'Generate HMAC',
            'GET /' => 'Health check'
        ],
        'timestamp' => time()
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // JSON Input lesen
        $json = file_get_contents('php://input');
        $input = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }
        
        $data = $input['data'] ?? '';
        $secret = $input['secret'] ?? '';
        
        if (empty($data) || empty($secret)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'data and secret required',
                'received' => [
                    'data' => !empty($data),
                    'secret' => !empty($secret)
                ]
            ]);
            exit;
        }
        
        // HMAC-SHA256 generieren (genau wie in onoffice Cloud-Workflow)
        $hmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
        
        echo json_encode([
            'hmac' => $hmac,
            'success' => true,
            'input_data_length' => strlen($data),
            'input_secret_length' => strlen($secret),
            'timestamp' => time()
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage(),
            'success' => false
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
