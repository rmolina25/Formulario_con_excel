<?php
// Incluir la conexión a la base de datos
include('db.php');

// Definir cuántos registros por página
$registros_por_pagina = 10;

// Obtener el número de página actual desde la URL (por defecto será 1)
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

// Calcular el OFFSET
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Consultar los registros de la tabla tabla_errores con LIMIT y OFFSET
$query = "SELECT fecha_solicitud, hora_solicitud, persona_solicitante, impacto, estado_correccion, tiempo_respuesta, hora_respuesta 
          FROM tabla_errores 
          LIMIT $registros_por_pagina OFFSET $offset";
$result = $conn->query($query);

// Consultar el total de registros para calcular la cantidad de páginas
$total_count_query = "SELECT COUNT(*) AS total_registros FROM tabla_errores";
$total_count_result = $conn->query($total_count_query);
$total_count = $total_count_result->fetch(PDO::FETCH_ASSOC)['total_registros'];
$total_paginas = ceil($total_count / $registros_por_pagina);

// Consultar los totales de tiempo de respuesta
$total_query = "SELECT SUM(tiempo_respuesta) AS total_tiempo FROM tabla_errores";
$total_result = $conn->query($total_query);
$total_tiempo = 0;
if ($total_result->rowCount() > 0) {
    $total_row = $total_result->fetch(PDO::FETCH_ASSOC);
    $total_tiempo = $total_row['total_tiempo'];
}

// Convertir el total de segundos en horas, minutos y segundos
$total_hours = floor($total_tiempo / 3600);
$total_minutes = floor(($total_tiempo % 3600) / 60);
$total_seconds = $total_tiempo % 60;

// Formatear el total de tiempo en un formato legible
$total_time_formatted = sprintf("%02d horas, %02d minutos, %02d segundos", $total_hours, $total_minutes, $total_seconds);

// Consultar las personas con más solicitudes
$personas_query = "SELECT persona_solicitante, impacto, COUNT(*) AS cantidad_solicitudes
                    FROM tabla_errores
                    GROUP BY persona_solicitante, impacto
                    ORDER BY cantidad_solicitudes DESC";
$personas_result = $conn->query($personas_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Resultados de Solicitudes y Respuestas</h1>
        <div class="text-center mt-4">
            <a href="/correos" class="btn btn-primary">Volver al Registro</a>
            <a href="export_excel.php" class="btn btn-success">Descargar Excel</a>
        </div>

        <!-- Total de registros -->
        <h3>Total de Registros: <?php echo $total_count; ?> Registros</h3>

        <!-- Tabla de registros con tiempo de respuesta -->
        <h3>Registros de Solicitudes</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha Solicitud</th>
                    <th>Hora Solicitud</th>
                    <th>Persona Solicitante</th>
                    <th>Impacto</th>
                    <th>Estado Corrección</th>
                    <th>Hora Respuesta</th>
                    <th>Tiempo Respuesta (minutos)</th> <!-- Columna añadida -->
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->rowCount() > 0) {
                    $counter = 1 + $offset;
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        // Evitar mostrar valores nulos en las fechas y horas
                        $tiempo_respuesta_segundos = $row['tiempo_respuesta'] ?? 'N/A';

                        // Calcular el tiempo de respuesta en minutos
                        if (!empty($row['hora_solicitud']) && !empty($row['hora_respuesta'])) {
                            $hora_solicitud = strtotime($row['hora_solicitud']);
                            $hora_respuesta = strtotime($row['hora_respuesta']);
                            $tiempo_respuesta_minutos = round(($hora_respuesta - $hora_solicitud) / 60);
                        } else {
                            $tiempo_respuesta_minutos = 'N/A';
                        }

                        echo "<tr>
                                <td>{$counter}</td>
                                <td>{$row['fecha_solicitud']}</td>
                                <td>{$row['hora_solicitud']}</td>
                                <td>{$row['persona_solicitante']}</td>
                                <td>{$row['impacto']}</td>
                                <td>{$row['estado_correccion']}</td>
                                <td>{$row['hora_respuesta']}</td>
                                <td>{$tiempo_respuesta_minutos} minutos</td> <!-- Mostrar tiempo en minutos -->
                              </tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='8'>No hay registros disponibles.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Total de los tiempos de respuesta -->
        <h3>Total de Tiempos de Respuesta</h3>
        <p>El total de los tiempos de respuesta es: <strong><?php echo $total_time_formatted; ?></strong></p>

        <!-- Personas con más solicitudes y su impacto -->
        <h3>Personas con Más Solicitudes y Tipo de Impacto</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Persona Solicitante</th>
                    <th>Impacto</th>
                    <th>Cantidad de Solicitudes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($personas_result->rowCount() > 0) {
                    while ($persona_row = $personas_result->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>{$persona_row['persona_solicitante']}</td>
                                <td>{$persona_row['impacto']}</td>
                                <td>{$persona_row['cantidad_solicitudes']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No hay datos disponibles.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>" tabindex="-1">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Cerrar la conexión
$conn = null;
?>
