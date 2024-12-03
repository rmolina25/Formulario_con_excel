<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $fecha_solicitud = $_POST['fecha_solicitud'];
    $numero_registro = $_POST['numero_registro'];
    $persona_solicitante = $_POST['persona_solicitante'];
    $persona_error = $_POST['persona_error'];
    $descripcion = $_POST['descripcion'];
    $impacto = $_POST['impacto'];
    $estado_correccion = $_POST['estado_correccion'];
    $tiempo_respuesta = $_POST['tiempo_respuesta'];
    $comentarios = $_POST['comentarios'];
    $hora_solicitud = $_POST['hora_solicitud'];
    $hora_respuesta = $_POST['hora_respuesta'];

    // Formatear fechas al formato Y-m-d
    $fecha_solicitud = DateTime::createFromFormat('d-m-Y', $fecha_solicitud)->format('Y-m-d');
    $tiempo_respuesta = $tiempo_respuesta ? DateTime::createFromFormat('d-m-Y', $tiempo_respuesta)->format('Y-m-d') : null;

    // Preparar la consulta SQL para insertar los datos necesarios
    $sql = "INSERT INTO tabla_errores (fecha_solicitud, numero_registro, persona_solicitante, persona_error, descripcion, impacto, estado_correccion, tiempo_respuesta, comentarios, hora_solicitud, hora_respuesta)
            VALUES (:fecha_solicitud, :numero_registro, :persona_solicitante, :persona_error, :descripcion, :impacto, :estado_correccion, :tiempo_respuesta, :comentarios, :hora_solicitud, :hora_respuesta)";
    $stmt = $conn->prepare($sql);

    // Ejecutar la consulta con los valores proporcionados
    $stmt->execute([
        ':fecha_solicitud' => $fecha_solicitud,
        ':numero_registro' => $numero_registro,
        ':persona_solicitante' => $persona_solicitante,
        ':persona_error' => $persona_error,
        ':descripcion' => $descripcion,
        ':impacto' => $impacto,
        ':estado_correccion' => $estado_correccion,
        ':tiempo_respuesta' => $tiempo_respuesta,
        ':comentarios' => $comentarios,
        ':hora_solicitud' => $hora_solicitud,
        ':hora_respuesta' => $hora_respuesta,
    ]);

    // Redirigir a la página deseada
    header("Location:/correos/");
    exit; // Asegurarse de que el script no continúe ejecutándose
}
?>
