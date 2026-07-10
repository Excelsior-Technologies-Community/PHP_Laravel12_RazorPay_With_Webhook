<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Payment;

class RazorpayController extends Controller
{
    /**
     * Dashboard + Payment List
     */
    public function index(Request $request)
    {
        // Dashboard Statistics
        $totalPayments = Payment::count();
        $successPayments = Payment::where('status', Payment::STATUS_SUCCESS)->count();
        $failedPayments = Payment::where('status', Payment::STATUS_FAILED)->count();
        $pendingPayments = Payment::where('status', Payment::STATUS_PENDING)->count();
        $totalRevenue = Payment::where('status', Payment::STATUS_SUCCESS)->sum('amount');

        // Payment Listing
        $payments = Payment::query();

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);

            $payments->where(function ($query) use ($search) {
                $query->where('order_id', 'like', "%{$search}%")
                    ->orWhere('payment_id', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $payments->where('status', $request->status);
        }

        // Sorting
        switch ($request->sort) {

            case 'amount_asc':
                $payments->orderBy('amount', 'asc');
                break;

            case 'amount_desc':
                $payments->orderBy('amount', 'desc');
                break;

            case 'oldest':
                $payments->oldest();
                break;

            default:
                $payments->latest();
                break;
        }

        $payments = $payments->paginate(4)->withQueryString();

        return view('pay', compact(
            'payments',
            'totalPayments',
            'successPayments',
            'failedPayments',
            'pendingPayments',
            'totalRevenue'
        ));
    }

    /**
     * Create Razorpay Order
     */
    public function createOrder(Request $request)
    {
        $api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        $amount = 500;

        $order = $api->order->create([
            'receipt' => uniqid('receipt_'),
            'amount' => $amount * 100,
            'currency' => 'INR'
        ]);

        Payment::create([
            'order_id' => $order['id'],
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
        ]);

        return response()->json($order);
    }

    /**
     * Payment Success
     */
    public function paymentSuccess(Request $request)
    {
        Payment::where('order_id', $request->razorpay_order_id)
            ->update([
                'payment_id' => $request->razorpay_payment_id,
                'status' => Payment::STATUS_SUCCESS
            ]);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Delete Payment
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        $payment->delete();

        return redirect('/')
            ->with('success', 'Payment deleted successfully.');
    }
}