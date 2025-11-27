<?php
// Test if PDO MySQL extension is available

echo "PHP Version: " . PHP_VERSION . "\n";
echo "PDO Extension: " . (extension_loaded('pdo') ? 'Loaded' : 'Not Loaded') . "\n";
echo "PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'Not Loaded') . "\n";

if (extension_loaded('pdo_mysql')) {
    echo "PDO MySQL is available\n";
    
    // Try to create a PDO connection
    try {
        $dsn = 'mysql:host=82.146.42.213;port=3306;dbname=tg_bot;charset=utf8mb4';
        $username = 'WizardJIOCb';
        $password = '7qQ#28051961';
        
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "Database connection successful\n";
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "PDO MySQL is not available\n";
    
    // Try to load it dynamically
    if (function_exists('dl')) {
        $extension_dir = ini_get('extension_dir');
        $pdo_mysql_path = $extension_dir . DIRECTORY_SEPARATOR . 'php_pdo_mysql.dll';
        echo "Extension directory: " . $extension_dir . "\n";
        echo "PDO MySQL DLL path: " . $pdo_mysql_path . "\n";
        echo "PDO MySQL DLL exists: " . (file_exists($pdo_mysql_path) ? 'Yes' : 'No') . "\n";
        
        if (file_exists($pdo_mysql_path)) {
            if (dl('php_pdo_mysql.dll')) {
                echo "PDO MySQL extension loaded dynamically\n";
                echo "PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'Not Loaded') . "\n";
            } else {
                echo "Failed to load PDO MySQL extension dynamically\n";
            }
        }
    } else {
        echo "dl() function is not available\n";
    }
}