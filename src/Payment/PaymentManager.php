<?php
/**
 * Simulated payment gateway.
 *
 * In a production system this class would integrate with a provider like
 * Stripe, PayFast, or PayPal. For demonstration purposes, payments are
 * approved automatically and a transaction record is written to the database.
 */
class PaymentManager {
    private $pdo;

    public function __construct(PDO $db_connection) {
        $this->pdo = $db_connection;
    }

    /**
     * Processes a payment for the given order.
     *
     * Generates a simulated gateway reference and records the transaction.
     * In production, the $paymentToken parameter would carry tokenized
     * card or wallet data from the client-side SDK.
     *
     * @param int    $orderId      The order being paid for.
     * @param float  $amount       The charge amount in ZAR.
     * @param string $paymentToken Client-side payment token (unused in simulation).
     * @return bool True if the payment succeeded.
     */
    public function processPayment($orderId, $amount, $paymentToken = '') {
        $isSuccessful = $amount > 0;
        $gatewayRef   = 'SIM_' . strtoupper(uniqid());

        if ($isSuccessful) {
            return $this->recordTransaction($orderId, $gatewayRef, $amount, 'Completed');
        }

        $this->recordTransaction($orderId, $gatewayRef, $amount, 'Failed');
        return false;
    }

    /**
     * Writes a transaction record to the database.
     *
     * @param int    $orderId    Associated order.
     * @param string $gatewayRef Unique reference from the payment gateway.
     * @param float  $amount     Transaction amount.
     * @param string $status     "Completed" or "Failed".
     * @return bool True on successful insert.
     */
    private function recordTransaction($orderId, $gatewayRef, $amount, $status) {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO Transactions (OrderID, GatewayReferenceID, Amount, Status)
                 VALUES (:orderId, :refId, :amount, :status)'
            );
            $stmt->execute([
                ':orderId' => $orderId,
                ':refId'   => $gatewayRef,
                ':amount'  => $amount,
                ':status'  => $status,
            ]);
            return true;

        } catch (PDOException $e) {
            error_log("Transaction record failed for Order #$orderId: " . $e->getMessage());
            return false;
        }
    }
}