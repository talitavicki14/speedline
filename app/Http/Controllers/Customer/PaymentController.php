<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private function baseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    private function serverKey(): string
    {
        return config('services.midtrans.server_key');
    }

    public function show(Payment $payment)
    {
        $transaction = $payment->transaction;
        abort_if($transaction->booking->user_id !== Auth::id(), 403);
        $payment->load([
            'transaction.booking.user',
            'transaction.booking.vehicle',
            'transaction.booking.services',
            'transaction.booking.transactionSpareparts.sparepart',
        ]);
        return view('customer.payments.show', compact('payment'));
    }

    public function pay(Request $request, Payment $payment)
    {
        $transaction = $payment->transaction;
        abort_if($transaction->booking->user_id !== Auth::id(), 403);
        
        $payment->load([
            'transaction.booking.user',
            'transaction.booking.vehicle',
            'transaction.booking.services',
            'transaction.booking.transactionSpareparts.sparepart',
        ]);
        
        $type = $request->query('type');
        $bank = $request->query('bank');
        
        if (!$type && $payment->payment_status !== 'paid') {
            return redirect()->route('customer.payments.show', $payment);
        }
        
        return view('customer.payments.pay', compact('payment', 'type', 'bank'));
    }

    public function initiateDigital(Request $request, Payment $payment)
    {
        $request->validate([
            'payment_type' => 'required|in:bank_transfer,gopay,shopeepay,qris',
            'bank'         => 'nullable|in:bca,bni,bri,mandiri,permata',
        ]);

        $transaction = $payment->transaction;
        abort_if($transaction->booking->user_id !== Auth::id(), 403);

        if ($payment->payment_status === 'paid') {
            return response()->json(['error' => 'Sudah dibayar'], 400);
        }

        if ($payment->midtrans_order_id) {
            $statusResponse = Http::withoutVerifying()
                ->withBasicAuth($this->serverKey(), '')
                ->get($this->baseUrl() . '/v2/' . $payment->midtrans_order_id . '/status');

            if ($statusResponse->successful()) {
                $statusData = $statusResponse->json();
                $txStat = $statusData['transaction_status'] ?? '';

                if (in_array($txStat, ['pending', 'settlement', 'capture'])) {
                    return $this->formatMidtransResponse($statusData, $payment->midtrans_order_id, $request->bank, $request->payment_type);
                }
            }
        }

        $user      = Auth::user();
        $orderId   = 'SL-' . $payment->id . '-' . time();
        $payType   = $request->payment_type;

        $itemDetails = $this->buildItemDetails($transaction);
        $totalAmount = 0;
        foreach ($itemDetails as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        $body = [
            'payment_type' => $payType,
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '',
            ],
            'item_details' => $itemDetails,
        ];

        if ($payType === 'bank_transfer') {
            $bank = $request->bank ?? 'bca';
            if ($bank === 'mandiri') {
                $body['payment_type'] = 'echannel';
                $body['echannel'] = ['bill_info1' => 'SpeedLine', 'bill_info2' => 'Payment'];
            } else {
                $body['bank_transfer'] = ['bank' => $bank];
            }
        }

        if ($payType === 'gopay') {
            $body['gopay'] = ['enable_callback' => false];
        }

        $response = Http::withoutVerifying()
            ->withBasicAuth($this->serverKey(), '')
            ->post($this->baseUrl() . '/v2/charge', $body);

        if ($response->failed()) {
            return response()->json(['error' => 'Kesalahan Koneksi Midtrans: ' . $response->reason()], 500);
        }

        $data = $response->json();

        $statusCode = $data['status_code'] ?? '500';
        if (substr($statusCode, 0, 1) !== '2') {
            return response()->json([
                'error' => 'Kesalahan Midtrans (' . $statusCode . '): ' . ($data['status_message'] ?? 'Kesalahan tidak diketahui')
            ], 400);
        }

        $payment->update([
            'midtrans_order_id' => $orderId,
            'payment_method'    => $payType === 'bank_transfer' ? 'transfer' : $payType,
        ]);

        return $this->formatMidtransResponse($data, $orderId, $request->bank, $payType);
    }

    private function formatMidtransResponse(array $data, string $orderId, ?string $bank, string $paymentType)
    {
        $vaNumber   = null;
        $billerCode = null;
        $qrCodeUrl  = null;
        $deeplinkUrl = null;

        if (isset($data['va_numbers'][0]['va_number'])) {
            $vaNumber = $data['va_numbers'][0]['va_number'];
        } elseif (isset($data['permata_va_number'])) {
            $vaNumber = $data['permata_va_number'];
        } elseif (isset($data['bill_key'])) {
            $vaNumber   = $data['bill_key'];
            $billerCode = $data['biller_code'];
        }

        if (isset($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if ($action['name'] === 'generate-qr-code') $qrCodeUrl = $action['url'];
                if ($action['name'] === 'deeplink-redirect') $deeplinkUrl = $action['url'];
            }
        }

        return response()->json([
            'order_id'    => $orderId,
            'va_number'   => $vaNumber,
            'biller_code' => $billerCode,
            'qr_code_url' => $qrCodeUrl,
            'deeplink_url'=> $deeplinkUrl,
            'bank'        => $bank,
            'payment_type'=> $paymentType,
            'raw'         => $data,
        ]);
    }

    public function checkStatus(Payment $payment)
    {
        $transaction = $payment->transaction;
        abort_if($transaction->booking->user_id !== Auth::id(), 403);

        if (!$payment->midtrans_order_id) {
            return response()->json(['status' => $payment->payment_status]);
        }

        $response = Http::withoutVerifying()
            ->withBasicAuth($this->serverKey(), '')
            ->get($this->baseUrl() . '/v2/' . $payment->midtrans_order_id . '/status');

        if ($response->failed()) {
            return response()->json(['status' => $payment->payment_status]);
        }

        $data              = $response->json();
        $txStatus          = $data['transaction_status'] ?? '';
        $fraudStatus       = $data['fraud_status'] ?? 'accept';

        $isPaid = ($txStatus === 'settlement') ||
                  ($txStatus === 'capture' && $fraudStatus === 'accept');

        $isExpired = in_array($txStatus, ['expire', 'cancel']);
        $isFailed  = in_array($txStatus, ['deny', 'failure']);

        if ($isPaid && $payment->payment_status !== 'paid') {
            $payment->update([
                'payment_status'           => 'paid',
                'payment_date'             => now()->toDateString(),
                'amount_paid'              => $transaction->grand_total,
                'midtrans_transaction_id'  => $data['transaction_id'] ?? null,
            ]);

            $booking = $transaction->booking;
            if ($booking && $booking->status === 'ready') {
                $booking->update(['status' => 'completed']);
            }
        } elseif ($isExpired && $payment->payment_status !== 'expired') {
            $payment->update([
                'payment_status' => 'expired',
            ]);
        } elseif ($isFailed && $payment->payment_status !== 'failed') {
            $payment->update([
                'payment_status' => 'failed',
            ]);
        }

        return response()->json([
            'status'           => $payment->fresh()->payment_status,
            'midtrans_status'  => $txStatus,
        ]);
    }

    private function buildItemDetails(Transaction $transaction): array
    {
        $items = [];
        $booking = $transaction->booking;

        if ($booking) {
            if ($booking->services) {
                foreach ($booking->services as $svc) {
                    $items[] = [
                        'id'       => 'SVC-' . $svc->id,
                        'price'    => (int) ($svc->pivot->price ?? 0),
                        'quantity' => 1,
                        'name'     => mb_substr($svc->service_name, 0, 50),
                    ];
                }
            }

            if ($booking->bookingSpareparts) {
                foreach ($booking->bookingSpareparts as $sp) {
                    $items[] = [
                        'id'       => 'SP-' . $sp->sparepart_id,
                        'price'    => (int) ($sp->price ?? 0),
                        'quantity' => $sp->qty ?? 1,
                        'name'     => mb_substr($sp->sparepart->name ?? 'Sparepart', 0, 50),
                    ];
                }
            }
        }

        if (empty($items)) {
            $items[] = [
                'id'       => 'SERVICE',
                'price'    => (int) $transaction->grand_total,
                'quantity' => 1,
                'name'     => 'Automotive Service',
            ];
        }
        return $items;
    }

    public function cancelDigital(Request $request, Payment $payment)
    {
        $transaction = $payment->transaction;
        abort_if($transaction->booking->user_id !== Auth::id(), 403);

        if ($payment->payment_status === 'paid') {
            return response()->json(['error' => 'Sudah dibayar'], 400);
        }

        $orderId = $request->input('order_id') ?? $payment->midtrans_order_id;

        if ($orderId) {
            try {
                Http::withoutVerifying()
                    ->withBasicAuth($this->serverKey(), '')
                    ->post($this->baseUrl() . '/v2/' . $orderId . '/cancel');
            } catch (\Throwable $e) {
                Log::warning('Midtrans cancel failed: ' . $e->getMessage());
            }

            $payment->update(['midtrans_order_id' => null]);
        }

        return response()->json(['cancelled' => true]);
    }
}
