<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include 'db.php';

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Registros de Errores');
    $sheet->fromArray(
        ['ID', 'Fecha Solicitud','Hora de Solicitud', 'Número Registro', 'Solicitante', 'Responsable', 'Descripción', 'Impacto', 'Estado Corrección', 'Tiempo Respuesta','hora_respuesta', 'Comentarios'], 
        NULL, 
        'A1'
    );

    $stmt = $conn->query("SELECT * FROM tabla_errores");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $sheet->fromArray($data, NULL, 'A2');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Registros_Errores.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    echo 'Error al generar el Excel: ', $e->getMessage();
}
?>
