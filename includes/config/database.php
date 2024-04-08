<?php 

function conectarDB() : mysqli {
    $db = new mysqli('localhost', 'root', 'Mokamax1890*', 'realstate');

    if(!$db) {
        echo "Error no se pudo conectar";
        exit;
    } 

    return $db;
    
}