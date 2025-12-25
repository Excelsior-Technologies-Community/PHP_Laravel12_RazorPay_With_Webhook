<?php

namespace App\Http\Controllers;

use Razorpay\Api\Api;
use Illuminate\Http\Request;
use App\Models\Payment;

class RazorpayController extends Controller
{
    public function index()
    {
        return view('pay');
    }

    public function createOrder(Request $request)
    {
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $order = $api->order->create([
            'receipt' => uniqid(),
            'amount' => 500 * 100, // 500 INR
            'currency' => 'INR'
        ]);

        Payment::create([
            'order_id' => $order['id'],
            'amount' => 500
        ]);

        return response()->json($order);
    }

    public function paymentSuccess(Request $request)
    {
        Payment::where('order_id', $request->razorpay_order_id)
            ->update([
                'payment_id' => $request->razorpay_payment_id,
                'status' => 'success'
            ]);

        return redirect('/')->with('success', 'Payment Successful');
    }
}
