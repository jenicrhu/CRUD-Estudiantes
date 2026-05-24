<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

$errores = [];
$mensaje_exito = "";
$estudiante_editar = null;

// 1. ACCIÓN: REGISTRAR O ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $carrera = trim($_POST['carrera'] ?? '');
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    // [PROPUESTO 2] Validaciones en el backend
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $errores[] = "El nombre debe tener entre 3 y 100 caracteres.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido.";
    }
    if (empty($carrera)) {
        $errores[] = "El campo carrera es obligatorio.";
    }

    if (empty($errores)) {
        if ($_POST['accion'] === 'crear') {
            $stmt = $pdo->prepare("INSERT INTO estudiantes (nombre, email, carrera) VALUES (:n, :e, :c)");
            $stmt->execute([
                ':n' => htmlspecialchars($nombre),
                ':e' => filter_var($email, FILTER_SANITIZE_EMAIL),
                ':c' => htmlspecialchars($carrera),
            ]);
            header('Location: index.php?status=created'); exit;
        } elseif ($_POST['accion'] === 'actualizar' && $id) {
            // [PROPUESTO 1] Función UPDATE con Prepared Statements
            $stmt = $pdo->prepare("UPDATE estudiantes SET nombre = :n, email = :e, carrera = :c WHERE id = :id");
            $stmt->execute([
                ':n' => htmlspecialchars($nombre),
                ':e' => filter_var($email, FILTER_SANITIZE_EMAIL),
                ':c' => htmlspecialchars($carrera),
                ':id' => $id
            ]);
            header('Location: index.php?status=updated'); exit;
        }
    }
}

// 2. ACCIÓN: BORRAR
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM estudiantes WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['delete']]);
    header('Location: index.php?status=deleted'); exit;
}

// 3. ACCIÓN: CARGAR PARA EDITAR
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM estudiantes WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $estudiante_editar = $stmt->fetch();
}

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') $mensaje_exito = "¡Estudiante registrado!";
    if ($_GET['status'] === 'updated') $mensaje_exito = "¡Estudiante modificado!";
    if ($_GET['status'] === 'deleted') $mensaje_exito = "¡Estudiante eliminado!";
}

// [PROPUESTO 4] Búsqueda con ILIKE
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// [PROPUESTO 3] Paginación de 5 en 5
$limit = 5;
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pagina < 1) $pagina = 1;
$offset = ($pagina - 1) * $limit;

if (!empty($search)) {
    $sql_count = "SELECT COUNT(*) FROM estudiantes WHERE nombre ILIKE :s OR carrera ILIKE :s";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([':s' => "%{$search}%"]);
    $total_resultados = $stmt_count->fetchColumn();

    $sql_data  = "SELECT * FROM estudiantes WHERE nombre ILIKE :s OR carrera ILIKE :s ORDER BY creado_en DESC LIMIT :limit OFFSET :offset";
    $stmt_data = $pdo->prepare($sql_data);
    $stmt_data->bindValue(':s', "%{$search}%", PDO::PARAM_STR);
    $stmt_data->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_data->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt_data->execute();
    $estudiantes = $stmt_data->fetchAll();
} else {
    $total_resultados = $pdo->query("SELECT COUNT(*) FROM estudiantes")->fetchColumn();
    $sql_data = "SELECT * FROM estudiantes ORDER BY creado_en DESC LIMIT :limit OFFSET :offset";
    $stmt_data = $pdo->prepare($sql_data);
    $stmt_data->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_data->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt_data->execute();
    $estudiantes = $stmt_data->fetchAll();
}

$total_paginas = ceil($total_resultados / $limit);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estudiantes</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #fafafa; }
        .box { background: white; padding: 20px; border: 1px solid #ddd; max-width: 800px; }
        .error { color: red; background: #fee; padding: 10px; margin-bottom: 10px; }
        .ok { color: green; background: #efe; padding: 10px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .nav { margin-top: 15px; }
        .nav a { padding: 5px 10px; border: 1px solid #ccc; text-decoration: none; margin: 2px; color: #333; }
        .nav .act { background: #007bff; color: white; }
    </style>
</head>
<body>
<div class="box">
    <?php if(!empty($errores)): ?>
        <div class="error"><?= implode('<br>', $errores) ?></div>
    <?php endif; ?>
    <?php if(!empty($mensaje_exito)): ?>
        <div class="ok"><?= $mensaje_exito ?></div>
    <?php endif; ?>

    <h2><?= $estudiante_editar ? 'Modificar Estudiante' : 'Registrar Nuevo Estudiante' ?></h2>
    <form method="POST" action="index.php">
        <input type="hidden" name="id" value="<?= $estudiante_editar ? $estudiante_editar['id'] : '' ?>">
        <input type="hidden" name="accion" value="<?= $estudiante_editar ? 'actualizar' : 'crear' ?>">
        <input name="nombre" placeholder="Nombre" required value="<?= $estudiante_editar ? htmlspecialchars($estudiante_editar['nombre']) : '' ?>">
        <input name="email" type="email" placeholder="Email" required value="<?= $estudiante_editar ? htmlspecialchars($estudiante_editar['email']) : '' ?>">
        <input name="carrera" placeholder="Carrera" required value="<?= $estudiante_editar ? htmlspecialchars($estudiante_editar['carrera']) : '' ?>">
        <button type="submit">Guardar</button>
        <?php if($estudiante_editar): ?> <a href="index.php">Cancelar</a> <?php endif; ?>
    </form>

    <hr style="margin:20px 0;">

    <form method="GET" action="index.php">
        <input name="search" placeholder="Buscar por nombre o carrera..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Buscar</button>
    </form>

    <p>Total resultados: <strong><?= $total_resultados ?></strong></p>

    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Carrera</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($estudiantes as $e): ?>
            <tr>
                <td><?= $e['id'] ?></td>
                <td><?= htmlspecialchars($e['nombre']) ?></td>
                <td><?= htmlspecialchars($e['email']) ?></td>
                <td><?= htmlspecialchars($e['carrera']) ?></td>
                <td>
                    <a href="?edit=<?= $e['id'] ?>">Editar</a> | 
                    <a href="?delete=<?= $e['id'] ?>" onclick="return confirm('¿Seguro?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if($total_paginas > 1): ?>
        <div class="nav">
            <?php if($pagina > 1): ?>
                <a href="?p=<?= $pagina - 1 ?>&search=<?= urlencode($search) ?>">Anterior</a>
            <?php endif; ?>
            <?php for($i=1; $i<=$total_paginas; $i++): ?>
                <a href="?p=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $pagina==$i?'act':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if($pagina < $total_paginas): ?>
                <a href="?p=<?= $pagina + 1 ?>&search=<?= urlencode($search) ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>