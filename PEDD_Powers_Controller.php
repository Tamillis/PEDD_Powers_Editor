<?php
// Include the SessionAuth class
require 'SessionAuth.php';

//all responses will be JSON so setting that now
header('Content-Type: application/json');


// Initialize the session authentication
try {
    $auth = new SessionAuth();
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
    exit;
}

// Define the path to the JSON file
$jsonFile = 'pedd-powers.json';

// Protect appropraite CRUD operations with authentication
function checkAuthentication() {
    global $auth;
    if (!$auth->isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please log in.']);
        exit;
    }
}

// Function to read the JSON file
function readData() {
    global $jsonFile;
    // Check if the file exists
    if (!file_exists($jsonFile)) {
        // Attempt to create the file if it doesn't exist
        if (!file_put_contents($jsonFile, json_encode([]))) {
            http_response_code(500);
            echo json_encode(['error' => 'Unable to create JSON file. Check permissions.']);
            exit;
        }
    }

    //Check if the file is readable
    if (!is_readable($jsonFile)) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to read JSON file. Check permissions.']);
        exit;
    }

    $data = file_get_contents($jsonFile);
    if ($data === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to read JSON file. Check file content.']);
        exit;
    }
    return json_decode($data, true);
}

// Function to write data to the JSON file
function writeData($data) {
    //always check authentication before writing
    checkAuthentication();

    global $jsonFile;
    // Check if the file is writable
    if (!is_writable($jsonFile)) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to write to JSON file. Check permissions.']);
        exit;
    }

    $result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    if ($result === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to write to JSON file. Check permissions.']);
        exit;
    }
}

function getArrayElementWithName($arr, $name) {
    foreach($arr as $el) {
        if(isset($el['name']) && $el['name'] == $name){
            return $el;
        }
    }
    return false;
}

//adds in element, and makes sure it is ordered by name
function addArrayElement(&$arr, $el) {
    array_push($arr, $el);
    usort($arr, function($a, $b) {
        return strnatcmp($a['name'], $b['name']);
    });
}

// this seems like a long-winded way of doing this
function removeArrayElementWithName(&$arr, $name) {
    $i = 0;
    $n = -1;
    foreach($arr as $el) {
        if(isset($el['name']) && $el['name'] == $name){
            $n = $i;
        }
        $i++;
    }

    if($n == -1) return false;
    array_splice($arr, $n, 1);
    return true;
}

function checkArrayHasName($arr, $name) : void {
    if(!getArrayElementWithName($arr, $name)) {
        http_response_code(409);
        echo json_encode(['error' => 'Record with this name doesn\' exist']);
        exit;
    };
}

function checkArrayDoesntHaveName($arr, $name) : void {
    if(getArrayElementWithName($arr, $name)) {
        http_response_code(409);
        echo json_encode(['error' => 'Record with this name already exists']);
        exit;
    };
}

function getInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['name'])) 
    {
        http_response_code(400);
        echo json_encode(['error' => 'Name is required']);
        exit;
    }
    return $input;
}
// Handle CRUD operations based on the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Read operation
        echo json_encode(readData());
        break;

    case 'POST':
        // Create operation
        $input = getInput();
        $data = readData();
        checkArrayDoesntHaveName($data, $input['name']);
        addArrayElement($data, $input);
        writeData($data);
        echo json_encode(['message' => 'Record ' . $input['name'] . ' created successfully']);
        break;

    case 'PUT':
        // Update operation
        $input = getInput();
        $data = readData();
        checkArrayHasName($data, $input['name']);
        removeArrayElementWithName($data, $input['name']);
        addArrayElement($data, $input);
        writeData($data);
        echo json_encode(['message' => 'Record ' . $input['name'] . ' updated successfully']);
        break;

    case 'DELETE':
        // Delete operation
        $input = getInput();
        $data = readData();
        checkArrayHasName($data, $input['name']);
        removeArrayElementWithName($data, $input['name']);
        writeData($data);
        echo json_encode(['message' => 'Record ' . $input['name'] . ' deleted successfully']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}