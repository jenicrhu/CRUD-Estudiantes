<?php
// Credenciales directas de Supabase
$host = 'aws-1-us-west-2.pooler.supabase.com';
$port = '6543';
$dbname = 'postgres';
$user = 'postgres.ldtlmfqtuqwiqbhvjqmh';
$password = 'Sociedadquimica123';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // Conexión exitosa
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>