# PHP_Laravel12_RazorPay_With_Webhook

A complete, beginner‑friendly implementation of **Razorpay Payment Gateway** using **Laravel 12**, covering **One‑Time Payments**, **Webhook Verification**, and **Subscription Payments**.

This project is ideal for **learning**, **interviews**, and **real‑world Laravel payment integrations**.

---

## Features

* Laravel 12 based secure payment system
* Razorpay Checkout integration (Test & Live mode ready)
* Order creation & payment processing
* Payment success & failure handling
* Razorpay Webhook integration
* Webhook signature verification (HMAC SHA256)
* Payment status tracking in database
* Subscription (Recurring Payment) support
* Clean MVC architecture
* Simple & modern UI

---

## Tech Stack

* **Backend:** Laravel 12 (PHP 8.2+)
* **Database:** MySQL
* **Payment Gateway:** Razorpay
* **Frontend:** Blade + JavaScript
* **SDK:** razorpay/razorpay (Official SDK)

---

## Project Structure

```
razorpay-webhook/
├── app/
│   ├── Http/Controllers/
│   │   ├── RazorpayController.php
│   │   ├── RazorpayWebhookController.php
│   │   └── RazorpaySubscriptionController.php
│   └── Models/
│       ├── Payment.php
│       └── Subscription.php
├── resources/views/
│   ├── pay.blade.php
│   └── subscribe.blade.php
├── routes/web.php
├── database/migrations/
│   ├── create_payments_table.php
│   └── create_subscriptions_table.php
└── .env
```

---

## Requirements

* PHP 8.2 or higher
* Composer
* MySQL
* Laravel 12
* Razorpay Account (Test Mode recommended)

---

## Installation Steps

### Clone Repository

```bash
git clone https://github.com/your-username/PHP_Laravel12_RazorPay_With_Webhook.git
cd PHP_Laravel12_RazorPay_With_Webhook
```

### Install Dependencies

```bash
composer install
```

### Create `.env` File

```bash
cp .env.example .env
php artisan key:generate
```

### Configure Database & Razorpay Keys

Update `.env`:

```env
DB_DATABASE=razorpay_db
DB_USERNAME=root
DB_PASSWORD=

RAZORPAY_KEY=rzp_test_xxxxx
RAZORPAY_SECRET=xxxxxxxx
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret
```

### Run Migrations

```bash
php artisan migrate
```

### Start Server

```bash
php artisan serve
```

Open browser:

```
http://127.0.0.1:8000
```

---

## One‑Time Payment Flow

1. User clicks **Pay Now**
2. Laravel creates Razorpay Order
3. Razorpay Checkout popup opens
4. User completes payment
5. Payment success handled on frontend
6. Razorpay Webhook updates payment status
7. Payment saved in database

---

## Subscription Payment Flow

1. Admin creates Razorpay Plan (Dashboard)
2. User clicks **Subscribe Now**
3. Laravel creates Razorpay Subscription
4. Razorpay Checkout opens for recurring payment
5. Subscription activated on success
6. Webhook tracks future renewals
7. Subscription status stored in database

---

## Webhook Setup (Razorpay Dashboard)

1. Login to Razorpay Dashboard
2. Go to **Settings → Webhooks**
3. Add Webhook URL:

```
https://yourdomain.com/razorpay-webhook
```

4. Select Events:

* payment.captured
* payment.failed
* subscription.activated
* subscription.charged
* subscription.cancelled

5. Copy Webhook Secret and add to `.env`

---

## Database Tables

### payments

| Column     | Type      | Description                |
| ---------- | --------- | -------------------------- |
| id         | bigint    | Primary Key                |
| order_id   | string    | Razorpay Order ID          |
| payment_id | string    | Razorpay Payment ID        |
| status     | string    | success / failed / pending |
| amount     | integer   | Payment amount             |
| created_at | timestamp | Created time               |

### subscriptions

| Column          | Type      | Description              |
| --------------- | --------- | ------------------------ |
| id              | bigint    | Primary Key              |
| subscription_id | string    | Razorpay Subscription ID |
| plan_id         | string    | Razorpay Plan ID         |
| status          | string    | active / cancelled       |
| total_count     | integer   | Billing cycles           |
| created_at      | timestamp | Created time             |

---

## Testing

Use **Razorpay Test Mode**

**Test Card Details:**

* Card Number: 4111 1111 1111 1111
* Expiry: Any future date
* CVV: Any 3 digits
* OTP: 123456

---

## UI Preview

* Centered modern payment card
* Gradient background
* Secure Razorpay checkout popup
* Mobile responsive

---

## Security Notes

* Webhook signature verification enabled
* Payments validated from Razorpay server
* CSRF protection enabled
* Secret keys never exposed in frontend

---

## Future Enhancements

* Payment success & failure pages
* Email invoice after payment
* Refund API integration
* Admin dashboard for payments & subscriptions
* API‑only implementation

---

## License

This project is open‑source and free to use for learning and development purposes.

---

## Author

Developed for Laravel learners and professionals to understand **Razorpay Payment & Subscription integration** clearly and practically.
