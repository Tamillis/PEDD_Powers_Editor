<?php
// Include the SessionAuth class
require 'SessionAuth.php';
try {
    $auth = new SessionAuth();
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
    exit;
}

//all responses will be JSON so setting that now
header('Content-Type: application/json');

// Handle authentication requests
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'login':
            $input = getInput();
            if ($auth->login($input['username'], $input['password'])) {
                echo json_encode(['message' => 'Login successful']);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid username or password']);
            }
            exit;
        case 'logout':
            $auth->logout();
            echo json_encode(['message' => 'Logout successful']);
            exit;

        case 'check':
            echo json_encode(['authenticated' => $auth->isAuthenticated(), 'username' => $auth->getUsername()]);
            exit;
    }
}

function getInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password are required']);
        exit;
    }
    return $input;
}