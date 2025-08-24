<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require_once('../../../database/database_connection.php');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['id'], $input['name'])) {
            $id = $input['id'];
            $tableName = "tbl" . $input['name'];
            
            $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE Id = :id");
            $stmt->execute([':id' => $id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                // Field mapping
                if ($input['name'] === 'lecture') {
                    if (isset($record['emailAddress'])) $record['email'] = $record['emailAddress'];
                    if (isset($record['phoneNo'])) $record['phoneNumber'] = $record['phoneNo'];
                    if (isset($record['facultyCode'])) $record['faculty'] = $record['facultyCode'];
                }
                
                if ($input['name'] === 'students') {
                    if (isset($record['courseCode'])) $record['course'] = $record['courseCode'];
                }
                
                echo json_encode(['success' => true, 'data' => $record]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Record not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'ID or Name not provided']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>