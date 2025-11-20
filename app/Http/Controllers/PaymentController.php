<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Step 1: Generate the secure Hash and Config for the Frontend
     */
    public function initiate(Request $request, $bookingId)
    {
        $user = $request->user();
        $booking = Booking::where('id', $bookingId)
            ->where('student_id', $user->id)
            ->firstOrFail();

        // Prevent paying for confirmed or cancelled bookings
        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Booking is not eligible for payment.'], 400);
        }

        $merchantId = env('PAYHERE_MERCHANT_ID');
        $merchantSecret = env('PAYHERE_MERCHANT_SECRET');
        $amount = number_format($booking->price, 2, '.', ''); // Format: 1000.00
        $currency = 'LKR';

        // PayHere Hash Calculation:
        // strtoupper(md5(merchant_id + order_id + amount + currency + strtoupper(md5(merchant_secret))))
        $hash = strtoupper(md5(
            $merchantId .
            $booking->id .
            $amount .
            $currency .
            strtoupper(md5($merchantSecret))
        ));

        return response()->json([
            'sandbox' => env('PAYHERE_MODE') === 'sandbox',
            'merchant_id' => $merchantId,
            'return_url' => url('/payment/success'), // Frontend URL
            'cancel_url' => url('/payment/cancel'),  // Frontend URL
            'notify_url' => url('/api/payment/notify'), // Backend Webhook URL
            'order_id' => $booking->id,
            'items' => 'Tuition Session #' . $booking->id,
            'currency' => $currency,
            'amount' => $amount,
            'first_name' => $user->name,
            'last_name' => '', // Optional
            'email' => $user->email,
            'phone' => '0771234567', // You should collect this from user profile
            'address' => 'Colombo',
            'city' => 'Colombo',
            'country' => 'Sri Lanka',
            'hash' => $hash
        ]);
    }

    /**
     * Step 2: Handle the Webhook (Server-to-Server Notification)
     * This is where we actually mark the payment as "Paid".
     */
    public function notify(Request $request)
    {
        // Log the request for debugging
        Log::info('PayHere Notify:', $request->all());

        $merchantId = $request->merchant_id;
        $orderId = $request->order_id;
        $payhereAmount = $request->payhere_amount;
        $payhereCurrency = $request->payhere_currency;
        $statusCode = $request->status_code;
        $md5sig = $request->md5sig;

        $merchantSecret = env('PAYHERE_MERCHANT_SECRET');

        // Verify the signature to ensure request is genuinely from PayHere
        $localMd5sig = strtoupper(md5(
            $merchantId .
            $orderId .
            $payhereAmount .
            $payhereCurrency .
            $statusCode .
            strtoupper(md5($merchantSecret))
        ));

        if ($localMd5sig !== $md5sig) {
            Log::error('PayHere Signature Mismatch');
            return response()->json(['error' => 'Signature mismatch'], 400);
        }

        // Status Code 2 = Success
        if ($statusCode == 2) {
            $booking = Booking::find($orderId);

            if ($booking) {
                // 1. Update Booking Status
                $booking->update(['status' => 'confirmed']);

                // 2. Create Payment Record
                Payment::create([
                    'booking_id' => $booking->id,
                    'student_id' => $booking->student_id,
                    'amount' => $payhereAmount,
                    'status' => 'paid',
                    'gateway' => 'payhere',
                    'gateway_ref' => $request->payment_id, // PayHere Transaction ID
                    'paid_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}