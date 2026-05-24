<?php
// Datos exactos de tu Supabase
$host = 'aws-1-us-west-2.pooler.supabase.com';
$port = '6543';
$dbname = 'postgres';
$user = 'postgres.ldtlmfqtuqwiqbhvjqmh';
$password = 'Sciedadquimica1234_';

try {
    // Forzamos al DSN a incluir explícitamente el usuario largo dentro de la cadena de conexión
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Conexión exitosa
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>