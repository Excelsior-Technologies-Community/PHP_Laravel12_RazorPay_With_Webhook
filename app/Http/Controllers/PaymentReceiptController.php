<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentReceiptController extends Controller
{
    public function download($id)
    {

        $payment = Payment::findOrFail($id);


        $pdf = Pdf::loadView(
            'payment.receipt',
            compact('payment')
        );


        return $pdf->download(
            'payment-' . $payment->payment_id . '.pdf'
        );
    }
}
