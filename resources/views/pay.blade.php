<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Razorpay Payment</title>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .payment-card {
            background: #fff;
            width: 360px;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .payment-card h2 {
            margin-bottom: 10px;
            color: #333;
        }

        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
        }

        .pay-btn {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            background: #667eea;
            border: none;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            transition: 0.3s;
        }

        .pay-btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .secure-text {
            margin-top: 15px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>
<body>

<div class="payment-card">
    <h2>Complete Payment</h2>
    <div class="amount">₹500</div>

    <button id="payBtn" class="pay-btn">
        Pay Now
    </button>

    <div class="secure-text">
        🔒 Secure payment powered by Razorpay
    </div>
</div>

<script>
document.getElementById('payBtn').onclick = function () {

    fetch('/create-order', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(order => {

        var options = {
            key: "{{ config('services.razorpay.key') }}",
            amount: order.amount,
            currency: "INR",
            order_id: order.id,
            name: "Laravel Razorpay",
            description: "Test Payment",
            handler: function (response) {

                fetch('/payment-success', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(response)
                }).then(() => window.location.reload());
            }
        };

        var rzp = new Razorpay(options);
        rzp.open();
    });
};
</script>

</body>
</html>
