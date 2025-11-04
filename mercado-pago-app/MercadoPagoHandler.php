<?php
require_once 'config.php';

class MercadoPagoHandler {
    private $paymentClient;

    public function __construct() {
        // Asegura que el SDK esté configurado con el token correcto
        Config::initMercadoPago();
        $this->paymentClient = new MercadoPago\Client\Payment\PaymentClient();
    }

    /**
     * VERIFICAR PAGO - Se comunica con MercadoPago para obtener info actualizada de un pago
     */
    public function verificarPago($payment_id) {
        try {
            // 🔄 Consulta el estado real del pago en la API de MercadoPago
            $payment = $this->paymentClient->get($payment_id);

            if ($payment && isset($payment->id)) {
                return [
                    'id' => $payment->id,
                    'status' => $payment->status, // 'approved', 'pending', 'rejected'
                    'status_detail' => $payment->status_detail,
                    'transaction_amount' => $payment->transaction_amount,
                    'currency_id' => $payment->currency_id, // 'ARS'
                    'description' => $payment->description ?? '',
                    'external_reference' => $payment->external_reference ?? '',
                    'operation_type' => $payment->operation_type ?? '', // puede ser 'pos_payment'
                    'payment_type_id' => $payment->payment_type_id ?? '', // 'credit_card', 'debit_card', 'account_money', etc.
                    'pos_id' => $payment->pos_id ?? null, // ID del Point Smart
                    'store_id' => $payment->store_id ?? null, // ID del comercio
                    'payer' => [
                        'email' => $payment->payer->email ?? '',
                        'first_name' => $payment->payer->first_name ?? '',
                        'last_name' => $payment->payer->last_name ?? '',
                    ],
                    'point_of_interaction' => [
                        'transaction_data' => [
                            'ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url ?? '',
                            'qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? '',
                            'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? '',
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
     * CREAR PAGO DE PRUEBA - Solo para testing manual (NO se usa en Point Smart)
     */
    public function crearPagoPrueba($monto = 100.00, $descripcion = "Pago de prueba", $external_reference = "") {
        try {
            $request = [
                "transaction_amount" => (float)$monto,
                "description" => $descripcion,
                "external_reference" => $external_reference ?: "TEST_" . date('YmdHis'),
                "payer" => [
                    "email" => "test_user_123@testuser.com",
                ]
            ];

            $payment = $this->paymentClient->create($request);
            return $this->paymentToArray($payment);

        } catch (Exception $e) {
            error_log("❌ Error creando pago de prueba: " . $e->getMessage());
            return null;
        }
    }

    /**
     * CREAR PAGO REAL - ⚠️ NO se usa con Point Smart (se deja para futuras integraciones por API)
     */
    public function crearPagoReal($monto, $descripcion, $external_reference, $email_pagador) {
        try {
            $request = [
                "transaction_amount" => (float)$monto,
                "description" => $descripcion,
                "external_reference" => $external_reference,
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
     * Convertir objeto de pago a array simple
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
            'external_reference' => $payment->external_reference ?? '',
            'payer' => [
                'email' => $payment->payer->email ?? '',
            ],
            'date_created' => $payment->date_created,
        ];
    }
}
?>
