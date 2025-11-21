
<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sbonline";

try{
    $pdo = new PDO('mysql:dbname=sbonline', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // 返回关联数组，兼容 MySQLi
            PDO::ATTR_EMULATE_PREPARES => false,
 ]);

}catch(PDOException $e){
    die("Connection failed: ".$e->getMessage());
}









    
// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request
function is_get() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

// Is POST request
function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Get GET parameter
function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Get POST parameter
function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Get REQUEST parameter
function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect
function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Temporary session variable (flash message)
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}


// ============================================================================
// Error Handling
// ============================================================================
$_err = [];
function err($key) {
    global $_err;
    if (!empty($_err[$key])) {
        echo "<span class='err'>{$_err[$key]}</span>";
    } else {
        echo "<span></span>";
    }
}
?>
