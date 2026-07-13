<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

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

        // Monthly Revenue

        $monthlyRevenue = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as revenue')
        )
            ->where('status', Payment::STATUS_SUCCESS)
            ->groupBy('month')
            ->orderBy('month')
            ->get();


        // Payment Status

        $statusData = [
            'Success' => $successPayments,
            'Failed' => $failedPayments,
            'Pending' => $pendingPayments,
        ];


        // Monthly Payment Count

        $monthlyPayments = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('pay', compact(
            'payments',
            'totalPayments',
            'successPayments',
            'failedPayments',
            'pendingPayments',
            'totalRevenue',
            'monthlyRevenue',
            'statusData',
            'monthlyPayments'
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

        return response()->json([
            'id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency']
        ]);
    }

    /**
     * Payment Success
     */
    public function paymentSuccess(Request $request)
    {
        \Log::info('Payment Success Response', $request->all());

        $payment = Payment::where(
            'order_id',
            $request->razorpay_order_id
        )->first();

        if (!$payment) {

            \Log::error('Payment order not found: ' . $request->razorpay_order_id);

            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }


        $payment->update([
            'payment_id' => $request->razorpay_payment_id,
            'status' => Payment::STATUS_SUCCESS
        ]);


        \Log::info('Payment Updated Successfully', [
            'id' => $payment->id,
            'status' => $payment->status
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully'
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
