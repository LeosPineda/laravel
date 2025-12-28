<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.3;
            margin: 0;
            padding: 20px;
            width: 3in;
            margin: 0 auto;
            color: #000;
        }

        .receipt {
            width: 100%;
            max-width: 3in;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .store-info {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .order-info {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .order-info div {
            margin-bottom: 3px;
        }

        .items {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .item {
            margin-bottom: 8px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-details {
            margin-left: 10px;
            font-size: 10px;
        }

        .item-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .addons {
            margin-left: 15px;
            font-size: 10px;
            color: #666;
        }

        .addon-line {
            display: flex;
            justify-content: space-between;
        }

        .subtotal {
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }

        .subtotal-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .total {
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            margin: 10px 0;
            padding: 8px;
            border: 2px solid #000;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }

        .payment-info {
            margin: 10px 0;
            font-size: 10px;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">4RODZ FOOD COURT</div>
            <div class="store-info">Your favorite food, ready when you are</div>
            <div class="store-info">Thank you for your order!</div>
        </div>

        <!-- Order Information -->
        <div class="order-info">
            <div><strong>Receipt #: </strong>{{ $order->order_number }}</div>
            <div><strong>Date: </strong>{{ $order->created_at->format('M d, Y H:i') }}</div>
            <div><strong>Table: </strong>{{ $order->table_number }}</div>
            <div><strong>Vendor: </strong>{{ $order->vendor->brand_name }}</div>
        </div>

        <!-- Items -->
        <div class="items">
            @foreach($order->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->product->name }}</div>
                <div class="item-line">
                    <span>{{ $item->quantity }} x ₱{{ number_format($item->unit_price, 2) }}</span>
                    <span>₱{{ number_format($item->total_price, 2) }}</span>
                </div>

                @if($item->selected_addons && count($item->selected_addons) > 0)
                <div class="addons">
                    @foreach($item->selected_addons as $addon)
                    <div class="addon-line">
                        <span>+ {{ $addon['name'] }}</span>
                        <span>₱{{ number_format($addon['price'], 2) }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Subtotals -->
        <div class="subtotal">
            <div class="subtotal-line">
                <span>Subtotal:</span>
                <span>₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
            @if($order->payment_method === 'qr_code')
            <div class="subtotal-line">
                <span>Payment Method:</span>
                <span>QR Code</span>
            </div>
            @else
            <div class="subtotal-line">
                <span>Payment Method:</span>
                <span>Cashier</span>
            </div>
            @endif
        </div>

        <!-- Total -->
        <div class="total">
            TOTAL: ₱{{ number_format($order->total_amount, 2) }}
        </div>

        <!-- Payment Information -->
        @if($order->payment_proof_url)
        <div class="payment-info">
            <div class="divider"></div>
            <div>Payment confirmed via QR Code</div>
            <div>Transaction ID: {{ $order->order_number }}</div>
        </div>
        @endif

        <!-- Special Instructions -->
        @if($order->special_instructions)
        <div class="payment-info">
            <div class="divider"></div>
            <div><strong>Special Instructions:</strong></div>
            <div>{{ $order->special_instructions }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="divider"></div>
            <div>Thank you for choosing 4Rodz Food Court!</div>
            <div>Come back again soon!</div>
            <div style="margin-top: 10px;">
                <small>Order Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</small>
            </div>
        </div>
    </div>
</body>
</html>
