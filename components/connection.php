<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $host = "localhost";
    $login = "root";
    $pass = "";
    $dbname = "shop_db";
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try{
        $conn = new PDO($dsn, $login, $pass, $options);

        // echo "Connection successfully";
    }catch(\PDOException $e){

        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    
    }
    
?>