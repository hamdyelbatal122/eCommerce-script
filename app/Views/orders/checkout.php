<?php
ob_start();
$title = 'Checkout';
?>

<div class="py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Secure Checkout</h2>
        <a href="/cart" class="btn btn-outline-primary btn-sm">Back to cart</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Shipping details</h5>
                    <form id="checkoutForm" method="POST" action="/orders">
                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                        <input type="hidden" name="payment_method" value="stripe">
                        <div class="mb-3">
                            <label class="form-label">Shipping address</label>
                            <textarea class="form-control" name="shipping_address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone number</label>
                            <input class="form-control" name="shipping_phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Coupon code</label>
                            <div class="d-flex gap-2">
                                <input class="form-control" id="couponCode" name="coupon_code" value="<?php echo htmlspecialchars($coupon_code ?? ''); ?>">
                                <button class="btn btn-outline-secondary" type="button" id="applyCouponBtn">Apply</button>
                            </div>
                            <small class="text-muted" id="couponMessage"></small>
                        </div>

                        <?php if (!empty($stripe_enabled)): ?>
                            <h6 class="mt-4">Card payment</h6>
                            <div id="card-element" class="form-control p-3 mb-2" style="height:auto;"></div>
                            <small class="text-muted d-block mb-3">Powered by Stripe Elements.</small>
                        <?php else: ?>
                            <div class="alert alert-warning">Stripe is not configured yet. Add keys in `.env` to enable card checkout.</div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary w-100" id="checkoutBtn">
                            Place order
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Order summary</h5>
                    <?php foreach ($cart as $row): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo htmlspecialchars($row['name']); ?> x<?php echo (int) $row['quantity']; ?></span>
                            <strong>$<?php echo number_format((float) $row['price'] * (int) $row['quantity'], 2); ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong>$<?php echo number_format($subtotal, 2); ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Delivery</span><strong>$<?php echo number_format($delivery_fee, 2); ?></strong></div>
                    <div class="d-flex justify-content-between text-success"><span>Discount</span><strong>-$<?php echo number_format($discount, 2); ?></strong></div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5"><span>Total</span><strong id="checkoutTotal">$<?php echo number_format($total, 2); ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($stripe_enabled)): ?>
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stripe = Stripe('<?php echo addslashes($stripe_publishable_key ?? ''); ?>');
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    const form = document.getElementById('checkoutForm');
    const btn = document.getElementById('checkoutBtn');
    const couponBtn = document.getElementById('applyCouponBtn');
    const couponCode = document.getElementById('couponCode');
    const couponMessage = document.getElementById('couponMessage');

    if (couponBtn) {
        couponBtn.addEventListener('click', async function () {
            const code = couponCode.value.trim();
            if (!code) return;
            const subtotal = <?php echo json_encode((float) $subtotal); ?>;
            const res = await fetch('/coupons/validate?code=' + encodeURIComponent(code) + '&subtotal=' + subtotal);
            const data = await res.json();
            couponMessage.textContent = data.valid ? ('Applied: -$' + Number(data.discount_amount).toFixed(2)) : data.message;
            couponMessage.className = data.valid ? 'text-success' : 'text-danger';
        });
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        btn.disabled = true;

        const orderResponse = await fetch('/orders', {
            method: 'POST',
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
            body: new FormData(form)
        });
        const orderData = await orderResponse.json();
        if (!orderData.success) {
            alert(orderData.error || 'Failed to create order');
            btn.disabled = false;
            return;
        }

        const amount = <?php echo json_encode((float) $total); ?>;
        const intentForm = new FormData();
        intentForm.append('amount', amount);
        intentForm.append('order_id', orderData.order_id);
        intentForm.append('_csrf_token', document.querySelector('meta[name="csrf-token"]').content);

        const intentRes = await fetch('/payments/stripe/intent', { method: 'POST', body: intentForm });
        const intentData = await intentRes.json();
        if (!intentData.success || !intentData.clientSecret) {
            alert(intentData.error || 'Payment init failed');
            btn.disabled = false;
            return;
        }

        const result = await stripe.confirmCardPayment(intentData.clientSecret, {
            payment_method: { card: card }
        });
        if (result.error) {
            alert(result.error.message || 'Payment failed');
            btn.disabled = false;
            return;
        }

        window.location.href = '/orders/' + orderData.order_id;
    });
});
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layouts/main.php';
?>
