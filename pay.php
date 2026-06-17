<?php
require_once __DIR__ . '/config/init.php';

if (!isLoggedIn() || !in_array(currentUserRole(), [ROLE_USER, ROLE_COMPANY], true)) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$requestId = (int)($_GET['request_id'] ?? 0);
if (!$requestId) {
    header('Location: ' . BASE_URL . '/services.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT sr.*, s.title AS service_title, s.price AS service_price, s.price_type,
           u.name AS freelancer_name
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    JOIN users u ON u.id = s.freelancer_id
    WHERE sr.id = ? AND sr.requester_id = ?
");
$stmt->execute([$requestId, currentUserId()]);
$request = $stmt->fetch();

if (!$request) {
    $_SESSION['pay_error'] = 'Service request not found.';
    header('Location: ' . BASE_URL . '/user/service_requests.php');
    exit;
}

if ($request['status'] !== SERVICE_REQUEST_COMPLETED) {
    $_SESSION['pay_error'] = 'You can only pay for completed services.';
    header('Location: ' . BASE_URL . '/user/service_requests.php');
    exit;
}

if (($request['payment_status'] ?? 'unpaid') === 'paid') {
    $_SESSION['pay_error'] = 'This service has already been paid.';
    header('Location: ' . BASE_URL . '/user/service_requests.php');
    exit;
}

$amount = (float)($request['payment_amount'] ?: $request['service_price']);
if ($amount <= 0) {
    $_SESSION['pay_error'] = 'No payment required for this service.';
    header('Location: ' . BASE_URL . '/user/service_requests.php');
    exit;
}

$amountCents = (int)round($amount * 100);
$paymentError = '';
$paymentSuccess = false;

// AJAX: create PaymentIntent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_intent'])) {
    header('Content-Type: application/json');
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    try {
        // Reuse existing intent if one was already created for this request
        if ($request['stripe_payment_intent']) {
            $intent = \Stripe\PaymentIntent::retrieve($request['stripe_payment_intent']);
            if ($intent->status === 'succeeded') {
                echo json_encode(['error' => 'Already paid.']);
                exit;
            }
        } else {
            $intent = \Stripe\PaymentIntent::create([
                'amount'   => $amountCents,
                'currency' => STRIPE_CURRENCY,
                'metadata' => [
                    'request_id'   => $requestId,
                    'requester_id' => currentUserId(),
                    'service'      => $request['service_title'],
                ],
            ]);
            $pdo->prepare('UPDATE service_requests SET stripe_payment_intent = ? WHERE id = ?')
                ->execute([$intent->id, $requestId]);
        }
        echo json_encode(['clientSecret' => $intent->client_secret]);
    } catch (\Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// AJAX: confirm payment succeeded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    header('Content-Type: application/json');
    $piId = $request['stripe_payment_intent'];
    if (!$piId) {
        echo json_encode(['error' => 'No payment intent found.']);
        exit;
    }
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    try {
        $intent = \Stripe\PaymentIntent::retrieve($piId);
        if ($intent->status === 'succeeded') {
            $pdo->prepare('UPDATE service_requests SET payment_status = ?, paid_at = NOW() WHERE id = ?')
                ->execute(['paid', $requestId]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Payment not confirmed. Status: ' . $intent->status]);
        }
    } catch (\Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

$pageTitle = 'Pay for Service';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:560px;">
    <p class="breadcrumb"><a href="<?= BASE_URL ?>/user/service_requests.php">My Requests</a> &rarr; Payment</p>

    <div class="card form-card">
        <h1>Pay for Service</h1>

        <div class="pay-summary">
            <table class="pay-details-table">
                <tr><td class="pay-label">Service</td><td><?= e($request['service_title']) ?></td></tr>
                <tr><td class="pay-label">Freelancer</td><td><?= e($request['freelancer_name']) ?></td></tr>
                <?php if ($request['booking_date']): ?>
                <tr><td class="pay-label">Date</td><td><?= date('M j, Y', strtotime($request['booking_date'])) ?><?= $request['booking_time'] ? ' at ' . date('g:i A', strtotime($request['booking_time'])) : '' ?></td></tr>
                <?php endif; ?>
                <tr><td class="pay-label">Amount</td><td class="pay-amount">$<?= number_format($amount, 2) ?></td></tr>
            </table>
        </div>

        <div id="pay-error" class="alert alert-error" style="display:none;"></div>
        <div id="pay-success" class="alert alert-success" style="display:none;">
            Payment successful! <a href="<?= BASE_URL ?>/user/service_requests.php">Back to my requests</a>
        </div>

        <form id="payment-form" style="margin-top:1.5rem;">
            <div class="form-group">
                <label for="card-element">Card details</label>
                <div id="card-element" class="stripe-card-element"></div>
            </div>
            <button id="pay-btn" type="submit" class="btn btn-primary btn-pay" style="width:100%;margin-top:1rem;">
                Pay $<?= number_format($amount, 2) ?>
            </button>
        </form>

        <p class="form-hint" style="margin-top:1rem;text-align:center;">
            <small>Payments are processed securely by Stripe. Your card details never touch our server.</small>
        </p>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
(function() {
    const stripe = Stripe('<?= e(STRIPE_PUBLISHABLE_KEY) ?>');
    const elements = stripe.elements();
    const card = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#1a1a2e',
                fontFamily: '"Inter", "Segoe UI", sans-serif',
                '::placeholder': { color: '#9ca3af' }
            },
            invalid: { color: '#ef4444' }
        }
    });
    card.mount('#card-element');

    const form = document.getElementById('payment-form');
    const btn  = document.getElementById('pay-btn');
    const errEl = document.getElementById('pay-error');
    const okEl  = document.getElementById('pay-success');

    function showError(msg) {
        errEl.textContent = msg;
        errEl.style.display = 'block';
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        btn.disabled = true;
        btn.textContent = 'Processing...';
        errEl.style.display = 'none';

        // 1. Ask server to create PaymentIntent
        let res = await fetch('<?= BASE_URL ?>/pay.php?request_id=<?= $requestId ?>', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'create_intent=1'
        });
        let data = await res.json();

        if (data.error) {
            showError(data.error);
            btn.disabled = false;
            btn.textContent = 'Pay $<?= number_format($amount, 2) ?>';
            return;
        }

        // 2. Confirm payment with card
        const {error, paymentIntent} = await stripe.confirmCardPayment(data.clientSecret, {
            payment_method: { card: card }
        });

        if (error) {
            showError(error.message);
            btn.disabled = false;
            btn.textContent = 'Pay $<?= number_format($amount, 2) ?>';
            return;
        }

        if (paymentIntent.status === 'succeeded') {
            // 3. Tell server payment succeeded
            await fetch('<?= BASE_URL ?>/pay.php?request_id=<?= $requestId ?>', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'confirm_payment=1'
            });
            form.style.display = 'none';
            okEl.style.display = 'block';
        } else {
            showError('Unexpected payment status: ' + paymentIntent.status);
            btn.disabled = false;
            btn.textContent = 'Pay $<?= number_format($amount, 2) ?>';
        }
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
