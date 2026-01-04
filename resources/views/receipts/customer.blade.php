<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
            max-width: 380px;
            margin: 0 auto;
            color: #333;
            background: #fff;
            position: relative;
        }

        /* Watermark Background */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.03);
            font-weight: bold;
            pointer-events: none;
            z-index: -1;
            white-space: nowrap;
        }

        .receipt {
            width: 100%;
            position: relative;
            border: 2px solid #333;
            padding: 15px;
            background: #fff;
        }

        /* Security Border Pattern */
        .security-border {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: repeating-linear-gradient(
                90deg,
                #FF6B35 0px,
                #FF6B35 10px,
                #333 10px,
                #333 20px
            );
        }

        .security-border-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: repeating-linear-gradient(
                90deg,
                #333 0px,
                #333 10px,
                #FF6B35 10px,
                #FF6B35 20px
            );
        }

        /* Header with Logo */
        .header {
            text-align: center;
            padding: 15px 0;
            border-bottom: 2px solid #333;
            margin-bottom: 15px;
        }

        .logo-container {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
            background: linear-gradient(135deg, #FF6B35, #FF8C5A);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo-icon {
            font-size: 32px;
        }

        .store-name {
            font-size: 22px;
            font-weight: bold;
            color: #FF6B35;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }

        .vendor-name {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
        }

        .tagline {
            font-size: 10px;
            color: #888;
        }

        /* Official Receipt Badge */
        .official-badge {
            display: inline-block;
            background: #333;
            color: #fff;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 8px;
        }

        /* Order Number Box */
        .order-number {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            background: linear-gradient(135deg, #f5f5f5, #e8e8e8);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
        }

        /* Order Info */
        .order-info {
            padding: 12px 0;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 12px;
        }

        .order-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .order-row .label {
            color: #666;
            font-size: 11px;
        }

        .order-row .value {
            font-weight: 600;
            color: #333;
        }

        /* Items Section */
        .items-header {
            font-weight: bold;
            font-size: 13px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
            color: #333;
            background: #f9f9f9;
            padding: 8px;
            margin: 0 -5px 10px;
        }

        .item {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px dotted #eee;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-main {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .item-name {
            font-weight: 600;
            font-size: 12px;
            flex: 1;
        }

        .item-price {
            font-weight: 600;
            text-align: right;
            min-width: 70px;
        }

        .item-qty {
            color: #666;
            font-size: 11px;
            margin-top: 2px;
        }

        .addons {
            margin-top: 5px;
            padding-left: 15px;
        }

        .addon {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #666;
            margin-bottom: 2px;
        }

        .addon-name::before {
            content: "+ ";
            color: #999;
        }

        /* Totals */
        .totals {
            border-top: 2px solid #333;
            padding-top: 12px;
            margin-top: 15px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .total-row.grand-total {
            font-size: 18px;
            font-weight: bold;
            padding: 12px;
            border: 2px solid #333;
            margin-top: 10px;
            background: #f9f9f9;
        }

        .total-row.grand-total .amount {
            color: #FF6B35;
        }

        /* Payment Info */
        .payment-section {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
            border: 1px solid #eee;
        }

        .payment-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            color: #666;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .payment-badge.qr {
            background: #e3f2fd;
            color: #1976d2;
        }

        .payment-badge.cash {
            background: #e8f5e9;
            color: #388e3c;
        }

        /* Special Instructions */
        .instructions {
            background: #fff8e1;
            padding: 10px;
            border-radius: 4px;
            margin-top: 12px;
            border-left: 3px solid #ffc107;
        }

        .instructions-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
            color: #f57c00;
        }

        .instructions-text {
            font-size: 11px;
            color: #666;
        }

        /* Status */
        .status-section {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }

        .status-ready {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .status-completed {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }

        /* Security Section */
        .security-section {
            margin-top: 20px;
            padding: 15px;
            background: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }

        .security-title {
            font-size: 10px;
            font-weight: bold;
            color: #666;
            margin-bottom: 10px;
            text-align: center;
            letter-spacing: 1px;
        }

        .verification-code {
            text-align: center;
            margin-bottom: 10px;
        }

        .verification-label {
            font-size: 9px;
            color: #888;
            margin-bottom: 3px;
        }

        .verification-hash {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            color: #333;
            background: #fff;
            padding: 5px 10px;
            border: 1px dashed #ccc;
            border-radius: 3px;
            word-break: break-all;
        }

        .qr-placeholder {
            width: 80px;
            height: 80px;
            margin: 10px auto;
            background: #fff;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-size: 8px;
            color: #666;
        }

        .qr-icon {
            font-size: 24px;
            margin-bottom: 3px;
        }

        .digital-signature {
            text-align: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ddd;
        }

        .signature-label {
            font-size: 8px;
            color: #999;
        }

        .signature-line {
            font-family: 'Georgia', serif;
            font-style: italic;
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #333;
        }

        .thank-you {
            font-size: 16px;
            font-weight: bold;
            color: #FF6B35;
            margin-bottom: 5px;
        }

        .footer-text {
            font-size: 10px;
            color: #888;
            margin-bottom: 3px;
        }

        .timestamp {
            font-size: 9px;
            color: #aaa;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #eee;
        }

        .legal-notice {
            font-size: 8px;
            color: #bbb;
            margin-top: 10px;
            text-align: center;
            font-style: italic;
        }

        /* Print styles */
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: 1px solid #000;
                page-break-inside: avoid;
            }
            .watermark {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">4RODZ FOOD COURT</div>

    <div class="receipt">
        <!-- Security Border Top -->
        <div class="security-border"></div>

        <!-- Header with Logo -->
        <div class="header">
            <div class="logo-container">
                <span class="logo-icon">🍴</span>
            </div>
            <div class="store-name">4RODZ FOOD COURT</div>
            <div class="vendor-name">{{ $order->vendor->brand_name ?? 'Vendor' }}</div>
            <div class="tagline">Your favorite food, ready when you are</div>
            <div class="official-badge">OFFICIAL RECEIPT</div>
        </div>

        <!-- Order Number -->
        <div class="order-number">
            {{ $order->order_number }}
        </div>

        <!-- Order Info -->
        <div class="order-info">
            <div class="order-row">
                <span class="label">Date & Time</span>
                <span class="value">{{ $order->created_at->format('M d, Y - h:i A') }}</span>
            </div>
            <div class="order-row">
                <span class="label">Table Number</span>
                <span class="value">{{ $order->table_number ?? 'N/A' }}</span>
            </div>
            @if($order->customer)
            <div class="order-row">
                <span class="label">Customer</span>
                <span class="value">{{ $order->customer->name ?? 'Guest' }}</span>
            </div>
            @endif
            <div class="order-row">
                <span class="label">Vendor ID</span>
                <span class="value">#{{ $order->vendor_id }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="items-section">
            <div class="items-header">📋 ORDER ITEMS</div>

            @foreach($order->items as $item)
            <div class="item">
                <div class="item-main">
                    <div>
                        <div class="item-name">{{ $item->product->name ?? 'Product' }}</div>
                        <div class="item-qty">{{ $item->quantity }} × ₱{{ number_format($item->unit_price, 2) }}</div>
                    </div>
                    <div class="item-price">₱{{ number_format($item->quantity * $item->unit_price, 2) }}</div>
                </div>

                @if($item->selected_addons && count($item->selected_addons) > 0)
                <div class="addons">
                    @foreach($item->selected_addons as $addon)
                    <div class="addon">
                        <span class="addon-name">{{ $addon['name'] ?? 'Add-on' }}</span>
                        <span>₱{{ number_format($addon['price'] ?? 0, 2) }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal ({{ $order->items->count() }} items)</span>
                <span>₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL AMOUNT</span>
                <span class="amount">₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-section">
            <div class="payment-title">💳 PAYMENT METHOD</div>
            <div class="payment-method">
                @if($order->payment_method === 'qr_code')
                <span class="payment-badge qr">📱 QR Code Payment</span>
                @if($order->payment_proof_url)
                <span style="font-size: 10px; color: #4caf50;">✓ Payment Verified</span>
                @endif
                @else
                <span class="payment-badge cash">💵 Pay at Cashier</span>
                @endif
            </div>
        </div>

        <!-- Special Instructions -->
        @if($order->special_instructions)
        <div class="instructions">
            <div class="instructions-title">📝 Special Instructions</div>
            <div class="instructions-text">{{ $order->special_instructions }}</div>
        </div>
        @endif

        <!-- Status -->
        <div class="status-section">
            @if($order->status === 'ready_for_pickup')
            <span class="status-badge status-ready">✓ Ready for Pickup</span>
            @elseif($order->status === 'completed')
            <span class="status-badge status-completed">✓ Completed</span>
            @endif
        </div>

        <!-- Security Section -->
        <div class="security-section">
            <div class="security-title">🔒 SECURITY VERIFICATION</div>

            <div class="verification-code">
                <div class="verification-label">Verification Code</div>
                <div class="verification-hash">{{ strtoupper(substr(hash('sha256', $order->order_number . $order->created_at . $order->total_amount . env('APP_KEY', 'secret')), 0, 24)) }}</div>
            </div>

            <div class="qr-placeholder">
                <span class="qr-icon">📱</span>
                <span>SCAN TO</span>
                <span>VERIFY</span>
            </div>

            <div class="digital-signature">
                <div class="signature-label">Digitally Signed By</div>
                <div class="signature-line">4Rodz Food Court System</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">Thank You!</div>
            <div class="footer-text">We appreciate your order</div>
            <div class="footer-text">See you again soon! 🙏</div>
            <div class="timestamp">
                Receipt Generated: {{ now()->format('M d, Y h:i:s A') }}<br>
                Transaction ID: {{ $order->id }}-{{ $order->vendor_id }}-{{ $order->created_at->timestamp }}
            </div>
            <div class="legal-notice">
                This is a computer-generated receipt. Any unauthorized alteration or reproduction is prohibited.
            </div>
        </div>

        <!-- Security Border Bottom -->
        <div class="security-border-bottom"></div>
    </div>
</body>
</html>
