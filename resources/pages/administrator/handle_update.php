<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();

try {
    require_once('../../../database/database_connection.php');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Debug: log what we received
        error_log("Received update request: " . print_r($input, true));
        
        if (isset($input['id'], $input['name'], $input['data'])) {
            $id = $input['id'];
            $tableName = "tbl" . $input['name'];
            $data = $input['data'];
            
            $setClause = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                $dbColumn = $key;
                
                // Map form fields to database columns for LECTURES
                if ($input['name'] === 'lecture') {
                    if ($key === 'email') $dbColumn = 'emailAddress';
                    if ($key === 'phoneNumber') $dbColumn = 'phoneNo';
                    if ($key === 'faculty') $dbColumn = 'facultyCode';
                }
                
                // Map form fields to database columns for STUDENTS
                if ($input['name'] === 'students') {
                    if ($key === 'course') $dbColumn = 'courseCode';
                }
                
                if ($key !== 'Id' && $key !== 'password') {
                    $setClause[] = "$dbColumn = :$key";
                    $params[":$key"] = $value;
                }
                
                if ($key === 'password' && !empty($value)) {
                    $setClause[] = "password = :$key";
                    $params[":$key"] = password_hash($value, PASSWORD_BCRYPT);
                }
            }
            
            // Debug: log the SQL and params
            error_log("SQL: UPDATE $tableName SET " . implode(', ', $setClause) . " WHERE Id = :id");
            error_log("Params: " . print_r($params, true));
            
            if (!empty($setClause)) {
                $sql = "UPDATE $tableName SET " . implode(', ', $setClause) . " WHERE Id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'No data to update']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Required data missing: ' . print_r($input, true)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
    
} catch (Exception $e) {
    // Get more detailed error information
    $errorInfo = $e->getMessage();
    if ($pdo && $stmt) {
        $errorInfo .= " | SQL Error: " . print_r($stmt->errorInfo(), true);
    }
    error_log("Update error: " . $errorInfo);
    echo json_encode(['success' => false, 'error' => 'Update failed: ' . $e->getMessage()]);
}
?>