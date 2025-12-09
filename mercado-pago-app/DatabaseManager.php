<?php
require_once 'config.php';

class DatabaseManager
{
    private static function utf($v) {
        return is_string($v) ? mb_convert_encoding($v, 'UTF-8', 'UTF-8') : $v;
    }

    public static function guardarPago($payment_data)
    {
        $db = Config::getDB();

        // Si existe → actualizar
        if (self::pagoExiste($payment_data['id'])) {
            return self::actualizarEstadoPago($payment_data['id'], $payment_data['status']);
        }

        $sql = "INSERT INTO pagos 
                (pos_id, payment_id, status, amount, description, payer_email, external_reference, created_at, updated_at)
                VALUES 
                (:pos_id :payment_id, :status, :amount, :description, :payer_email, :external_reference, NOW(), NOW())";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
             ':pos_id' => self::utf($payment_data['external_reference'] ?? 'SIN_POS'),
            ':payment_id' => $payment_data['id'],
            ':status' => self::utf($payment_data['status']),
            ':amount' => $payment_data['transaction_amount'] ?? 0,
            ':description' => self::utf($payment_data['description'] ?? ''),
            ':payer_email' => self::utf($payment_data['payer']['email'] ?? ''),
            ':external_reference' => self::utf($payment_data['external_reference'] ?? '')
        ]);
    }

    public static function actualizarEstadoPago($payment_id, $nuevo_estado)
    {
        $db = Config::getDB();

        $sql = "UPDATE pagos 
                SET status = :status, updated_at = NOW() 
                WHERE payment_id = :payment_id";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':status' => self::utf($nuevo_estado),
            ':payment_id' => $payment_id
        ]);
    }

    public static function pagoExiste($payment_id)
    {
        $db = Config::getDB();

        $sql = "SELECT COUNT(*) FROM pagos WHERE payment_id = :payment_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':payment_id' => $payment_id]);

        return $stmt->fetchColumn() > 0;
    }

    public static function obtenerPago($payment_id)
    {
        $db = Config::getDB();

        $sql = "SELECT * FROM pagos WHERE payment_id = :payment_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':payment_id' => $payment_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerPagosPorReferencia($external_reference)
    {
        $db = Config::getDB();

        $sql = "SELECT * FROM pagos 
                WHERE external_reference = :external_reference 
                ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':external_reference' => self::utf($external_reference)]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

