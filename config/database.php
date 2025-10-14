<!-- <?php
define('DB_HOST', 'localhost');         
define('DB_NAME', 'parfumerie_steve_paris');
define('DB_USER', 'root');              
define('DB_PASS', '');                  
define('DB_CHARSET', 'utf8mb4');        


$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;


$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,      
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         
    PDO::ATTR_EMULATE_PREPARES   => false,                     
];


try {
   
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
?> -->
<?php
try{
    $pdo = new PDO('mysql:host=localhost;dbname=parfumerie_steve_paris', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){
        die("Erreur de connexion : " . $e->getMessage());
        throw new PDOException("Erreur de connexion : ".$e->getMessage(), (int)$e->getCode());
    }
?>