<!DOCTYPE html>
<html>

<head>
    <title>Payment Receipt</title>

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .receipt-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .success {
            color: green;
            font-weight: bold;
        }
    </style>

</head>

<body>

    <div class="header">
        <h2>Payment Receipt</h2>
        <p>Laravel Razorpay Payment</p>
    </div>


    <div class="receipt-box">

        <table>

            <tr>
                <td><strong>Order ID</strong></td>
                <td>{{ $payment->order_id }}</td>
            </tr>


            <tr>
                <td><strong>Payment ID</strong></td>
                <td>{{ $payment->payment_id }}</td>
            </tr>


            <tr>
                <td><strong>Amount</strong></td>
                <td>₹{{ number_format($payment->amount,2) }}</td>
            </tr>


            <tr>
                <td><strong>Status</strong></td>
                <td class="success">
                    {{ ucfirst($payment->status) }}
                </td>
            </tr>


            <tr>
                <td><strong>Date</strong></td>
                <td>
                    {{ $payment->created_at->format('d M Y h:i A') }}
                </td>
            </tr>


        </table>

    </div>


</body>

</html>