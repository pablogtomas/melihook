<?php
require_once 'config.php';

class DatabaseManager {
    
    /**
     * Guardar un pago en la base de datos
     */
    public static function guardarPago($payment_data) {
        $db = Config::getDB();
        
        $sql = "INSERT INTO pagos (payment_id, status, amount, currency, description, qr_code, payer_email, external_reference) 
                VALUES (:payment_id, :status, :amount, :currency, :description, :qr_code, :payer_email, :external_reference)";
        
        $stmt = $db->prepare($sql);
        
        return $stmt->execute([
            ':payment_id' => $payment_data['id'],
            ':status' => $payment_data['status'],
            ':amount' => $payment_data['transaction_amount'],
            ':currency' => $payment_data['currency_id'],
            ':description' => $payment_data['description'] ?? '',
            ':qr_code' => $payment_data['point_of_interaction']['transaction_data']['qr_code'] ?? '',
            ':payer_email' => $payment_data['payer']['email'] ?? '',
            ':external_reference' => $payment_data['external_reference'] ?? '' // ✅ PARA POINTSMART
        ]);
    }
    
    /**
     * Actualizar el estado de un pago
     */
    public static function actualizarEstadoPago($payment_id, $nuevo_estado) {
        $db = Config::getDB();
        
        $sql = "UPDATE pagos SET status = :status WHERE payment_id = :payment_id";
        $stmt = $db->prepare($sql);
        
        return $stmt->execute([
            ':status' => $nuevo_estado,
            ':payment_id' => $payment_id
        ]);
    }
    
    /**
     * Obtener un pago por su ID de MercadoPago
     */
    public static function obtenerPago($payment_id) {
        $db = Config::getDB();
        
        $sql = "SELECT * FROM pagos WHERE payment_id = :payment_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':payment_id' => $payment_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener pagos por external_reference (ID de PointSmart)
     */
    public static function obtenerPagosPorReferencia($external_reference) {
        $db = Config::getDB();
        
        $sql = "SELECT * FROM pagos WHERE external_reference = :external_reference ORDER BY created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':external_reference' => $external_reference]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un pago ya existe en la BD
     */
    public static function pagoExiste($payment_id) {
        $db = Config::getDB();
        
        $sql = "SELECT COUNT(*) FROM pagos WHERE payment_id = :payment_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':payment_id' => $payment_id]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Obtener todos los pagos (para el dashboard)
     */
    public static function obtenerTodosLosPagos($limite = 10) {
        $db = Config::getDB();
        
        $sql = "SELECT * FROM pagos ORDER BY created_at DESC LIMIT :limite";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de pagos
     */
    public static function obtenerEstadisticas() {
        $db = Config::getDB();
        
        $stats = [];
        
        // Total de pagos
        $sql = "SELECT COUNT(*) as total FROM pagos";
        $stmt = $db->query($sql);
        $stats['total_pagos'] = $stmt->fetchColumn();
        
        // Pagos aprobados
        $sql = "SELECT COUNT(*) as aprobados FROM pagos WHERE status = 'approved'";
        $stmt = $db->query($sql);
        $stats['pagos_aprobados'] = $stmt->fetchColumn();
        
        // Total recaudado
        $sql = "SELECT SUM(amount) as total FROM pagos WHERE status = 'approved'";
        $stmt = $db->query($sql);
        $stats['total_recaudado'] = $stmt->fetchColumn() ?: 0;
        
        return $stats;
    }
}
?>