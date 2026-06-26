<?php
// Simple PHP server for key-value API using SQLite
// Run with: php -S localhost:8000 server.php
// Assumes database file is 'database.db' with table 'value-string'

$database = 'database.db';
$table = 'value-string';

try {
    $pdo = new PDO("sqlite:$database");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get the request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));
$id = isset($pathParts[1]) ? (int)$pathParts[1] : null;

header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        if ($id) {
            // Get value by id
            $stmt = $pdo->prepare("SELECT value FROM `$table` WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                echo json_encode(['id' => $id, 'value' => $result['value']]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Key not found']);
            }
        } else {
            // Get all key-value pairs
            $stmt = $pdo->query("SELECT id, value FROM `$table`");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
        }
        break;

    case 'POST':
        // Create new key-value (auto-increment id)
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['value'])) {
            $stmt = $pdo->prepare("INSERT INTO `$table` (value) VALUES (?)");
            $stmt->execute([$input['value']]);
            $newId = $pdo->lastInsertId();
            echo json_encode(['id' => $newId, 'value' => $input['value']]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Value required']);
        }
        break;

    case 'PUT':
        if ($id && isset($_GET['value'])) {
            // Update value by id
            $value = $_GET['value'];
            $stmt = $pdo->prepare("UPDATE `$table` SET value = ? WHERE id = ?");
            $stmt->execute([$value, $id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['id' => $id, 'value' => $value]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Key not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID and value required']);
        }
        break;

    case 'DELETE':
        if ($id) {
            // Delete by id
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => 'Deleted']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Key not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>