<?php
// config/database.php – Conexión PDO a Supabase (PostgreSQL cloud)
function getDBConnection(): PDO {
    // Estas funciones leen las variables que pondrás en Render
    $host = getenv('DB_HOST') ?: 'aws-1-us-west-2.pooler.supabase.com';  
    $port = getenv('DB_PORT') ?: '6543';  
    $name = getenv('DB_NAME') ?: 'postgres';  
    $user = getenv('DB_USER') ?: 'postgres.ldtlmfqtuqwiqbhvjqmh';  
    $pass = getenv('DB_PASS') ?: 'Sociedadquimica123_'; // <-- Cambia esto por tu contraseña real temporalmente si pruebas en tu PC

    $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode=require";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}
