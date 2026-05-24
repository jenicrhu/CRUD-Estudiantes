<?php
// config/database.php – Conexión PDO a Supabase (PostgreSQL cloud)
function getDBConnection(): PDO {
    // Estas funciones leen las variables que pondrás en Render
    $host = getenv('DB_HOST') ?: 'db.ldtlmfqtuqwiqbhvjqmh.supabase.co';  
    $port = getenv('DB_PORT') ?: '5432';  
    $name = getenv('DB_NAME') ?: 'postgres';  
    $user = getenv('DB_USER') ?: 'postgres';  
    $pass = getenv('DB_PASS') ?: 'TU_CONTRASEÑA_SECRETA'; // <-- Cambia esto por tu contraseña real temporalmente si pruebas en tu PC

    $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode=require";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}
