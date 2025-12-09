
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

$stripeSecretKey = getenv('STRIPE_SECRET_KEY') ?: 'sk_test_51SXhfm9YDXhXkkofv44eXmPCDH1LWPDuXuTqrCYUCDkiU0NRUU1gtFaJ3tCvM3GnpOpIpnrjOl1hmnJzIm8OBRNE00OogG9FS0';
?>
