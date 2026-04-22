<?php
/**
 * Inserta una entrada en historial_acciones.
 * Se incluye con require_once; no genera salida por sí mismo.
 */
function registrarMovimiento(mysqli $conn, int $incidenciaId, int $usuarioId, string $accion, string $descripcion): bool
{
    $sql  = "INSERT INTO historial_acciones (incidencia_id, usuario_id, accion, descripcion)
             VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('iiss', $incidenciaId, $usuarioId, $accion, $descripcion);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
