<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Running notification system migrations...\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/database/notifications.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $conn->exec($statement);
        }
    }
    
    echo "Migrations completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
}
?>
