<?php
// Archivo: views/admin/grupos.php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['id_entrenador'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Consultamos los grupos existentes para la tabla
try {
    $sql = "SELECT g.id_grupo, g.nombre_grupo, g.limite_alumnos, g.cupo_actual, e.nombre_completo as entrenador 
            FROM grupo g 
            INNER JOIN entrenador e ON g.id_entrenador = e.id_entrenador";
    $stmt = $conexion->query($sql);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // También necesitamos la lista de entrenadores para el formulario de crear grupo
    $sql_entrenadores = "SELECT id_entrenador, nombre_completo FROM entrenador WHERE estado = 'Activo'";
    $entrenadores_activos = $conexion->query($sql_entrenadores)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al cargar datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos - ControlSport</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #F4F6F9; margin: 0; }
        .navbar { background-color: #0047AB; color: white; padding: 15px 20px; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; }
        .container { padding: 40px; display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        .card { background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        input, select { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-blue { background-color: #0047AB; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold;}
    </style>
</head>
<body>

    <div class="navbar">
        <h2>ControlSport | Grupos Deportivos</h2>
        <div>
            <a href="gestion.php">Volver a Personal</a>
            <a href="../../controllers/logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h3>Crear Nuevo Grupo</h3>
            <form action="../../controllers/grupoController.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                
                <label>Nombre del Grupo (Ej. Fútbol Infantil)</label>
                <input type="text" name="nombre_grupo" required>
                
                <label>Límite de Alumnos</label>
                <input type="number" name="limite_alumnos" value="15" max="30" min="1" required>
                
                <label>Asignar Entrenador</label>
                <select name="id_entrenador" required>
                    <option value="">Seleccione un entrenador...</option>
                    <?php foreach($entrenadores_activos as $entrenador): ?>
                        <option value="<?php echo $entrenador['id_entrenador']; ?>">
                            <?php echo htmlspecialchars($entrenador['nombre_completo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn-blue" style="margin-top:15px;">Guardar Grupo</button>
            </form>
        </div>

        <div class="card">
            <h3>Grupos Actuales</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Entrenador</th>
                        <th>Cupo Actual</th>
                        <th>Límite</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($grupos as $g): ?>
                    <tr>
                        <td><?php echo $g['id_grupo']; ?></td>
                        <td><?php echo htmlspecialchars($g['nombre_grupo']); ?></td>
                        <td><?php echo htmlspecialchars($g['entrenador']); ?></td>
                        <td style="font-weight:bold; color: <?php echo ($g['cupo_actual'] >= $g['limite_alumnos']) ? '#FF3D71' : '#00E096'; ?>">
                            <?php echo $g['cupo_actual']; ?> alumnos
                        </td>
                        <td><?php echo $g['limite_alumnos']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>