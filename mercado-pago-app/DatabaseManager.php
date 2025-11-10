<?php
require_once 'config.php';

class DatabaseManager
{
    /**
     * Guarda un pago en la base de datos.
     * Si el pago ya existe, actualiza su estado.
     */
    public static function guardarPago($payment_data)
    {
        $db = Config::getDB();

        // Si ya existe, actualiza
        if (self::pagoExiste($payment_data['id'])) {
            return self::actualizarEstadoPago($payment_data['id'], $payment_data['status']);
        }

        // Insertar nuevo pago
        $sql = "INSERT INTO pagos 
                (payment_id, status, amount, description, payer_email, external_reference, created_at, updated_at)
                VALUES 
                (:payment_id, :status, :amount, :description, :payer_email, :external_reference, NOW(), NOW())";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':payment_id' => $payment_data['id'],
            ':status' => $payment_data['status'],
            ':amount' => $payment_data['transaction_amount'] ?? 0,
            ':description' => $payment_data['description'] ?? '',
            ':payer_email' => $payment_data['payer']['email'] ?? '',
            ':external_reference' => $payment_data['external_reference'] ?? ''
        ]);
    }

    /**
     * Actualiza el estado de un pago existente.
     */
    public static function actualizarEstadoPago($payment_id, $nuevo_estado)
    {
        $db = Config::getDB();

        $sql = "UPDATE pagos 
                SET status = :status, updated_at = NOW() 
                WHERE payment_id = :payment_id";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':status' => $nuevo_estado,
            ':payment_id' => $payment_id
        ]);
    }

    /**
     * Verifica si un pago ya existe en la base de datos.
     */
    public static function pagoExiste($payment_id)
    {
        $db = Config::getDB();

        $sql = "SELECT COUNT(*) FROM pagos WHERE payment_id = :payment_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':payment_id' => $payment_id]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un pago por su ID de Mercado Pago.
     */
    public static function obtenerPago($payment_id)
    {
        $db = Config::getDB();

        $sql = "SELECT * FROM pagos WHERE payment_id = :payment_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':payment_id' => $payment_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene pagos filtrando por referencia externa.
     */
    public static function obtenerPagosPorReferencia($external_reference)
    {
        $db = Config::getDB();

        $sql = "SELECT * FROM pagos 
                WHERE external_reference = :external_reference 
                ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':external_reference' => $external_reference]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los pagos, limitado por cantidad.
     */
    public static function obtenerTodosLosPagos($limite = 10)
    {
        $db = Config::getDB();

        $sql = "SELECT * FROM pagos ORDER BY created_at DESC LIMIT :limite";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
