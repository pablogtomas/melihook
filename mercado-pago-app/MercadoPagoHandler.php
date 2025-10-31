<?php
require_once 'config.php';

class MercadoPagoHandler {
    private $paymentClient;
    
    public function __construct() {
        $this->paymentClient = new MercadoPago\Client\Payment\PaymentClient();
    }
    
    /**
     * VERIFICAR PAGO - Esta es la función MÁS IMPORTANTE
     * Se comunica con MercadoPago para obtener info actualizada de un pago
     */
    public function verificarPago($payment_id) {
        try {
            // 🔄 Esta línea se conecta a la API de MercadoPago
            $payment = $this->paymentClient->get($payment_id);
            
            if ($payment && isset($payment->id)) {
                return [
                    'id' => $payment->id,
                    'status' => $payment->status, // 'approved', 'pending', 'rejected'
                    'status_detail' => $payment->status_detail,
                    'transaction_amount' => $payment->transaction_amount, // Monto
                    'currency_id' => $payment->currency_id, // 'ARS'
                    'description' => $payment->description ?? '',
                    'external_reference' => $payment->external_reference ?? '', // ✅ PARA POINTSMART
                    'payer' => [
                        'email' => $payment->payer->email ?? '',
                        'first_name' => $payment->payer->first_name ?? '',
                        'last_name' => $payment->payer->last_name ?? '',
                    ],
                    'point_of_interaction' => [
                        'transaction_data' => [
                            'qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? '',
                            'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? '',
                            'ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url ?? '',
                        ]
                    ],
                    'date_created' => $payment->date_created,
                    'date_approved' => $payment->date_approved ?? null,
                    'date_last_updated' => $payment->date_last_updated,
                ];
            }
        } catch (Exception $e) {
            error_log("❌ Error verificando pago {$payment_id}: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * CREAR PAGO DE PRUEBA - Solo para testing
     */
    public function crearPagoPrueba($monto = 100.00, $descripcion = "Pago de prueba", $external_reference = "") {
        try {
            $request = [
                "transaction_amount" => (float)$monto,
                "description" => $descripcion,
                "payment_method_id" => "pix",  // ✅ PIX PARA QR
                "external_reference" => $external_reference ?: "TEST_" . date('YmdHis'), // ✅ REFERENCIA
                "payer" => [
                    "email" => "test_user_123@testuser.com",
                ]
            ];
            
            $payment = $this->paymentClient->create($request);
            return $this->paymentToArray($payment);
            
        } catch (Exception $e) {
            error_log("❌ Error creando pago prueba: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * CREAR PAGO REAL - Para PointSmart
     */
    public function crearPagoReal($monto, $descripcion, $external_reference, $email_pagador) {
        try {
            $request = [
                "transaction_amount" => (float)$monto,
                "description" => $descripcion,
                "payment_method_id" => "pix",
                "external_reference" => $external_reference, // ✅ ID DE POINTSMART
                "payer" => [
                    "email" => $email_pagador,
                ]
            ];
            
            $payment = $this->paymentClient->create($request);
            return $this->paymentToArray($payment);
            
        } catch (Exception $e) {
            error_log("❌ Error creando pago real: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Convertir objeto de pago a array
     */
    private function paymentToArray($payment) {
        if (!$payment || !isset($payment->id)) {
            return null;
        }
        
        return [
            'id' => $payment->id,
            'status' => $payment->status,
            'status_detail' => $payment->status_detail,
            'transaction_amount' => $payment->transaction_amount,
            'currency_id' => $payment->currency_id,
            'description' => $payment->description ?? '',
            'external_reference' => $payment->external_reference ?? '', // ✅ PARA POINTSMART
            'payer' => [
                'email' => $payment->payer->email ?? '',
            ],
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? '',
                ]
            ],
            'date_created' => $payment->date_created,
        ];
    }
}
?>