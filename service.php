<?php
require_once __DIR__ . '/config/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/services.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT s.*, u.name AS freelancer_name, u.id AS freelancer_id
    FROM services s
    JOIN users u ON u.id = s.freelancer_id
    WHERE s.id = ? AND s.status = 'active'
");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: ' . BASE_URL . '/services.php');
    exit;
}

$stmt = $pdo->prepare('SELECT freelancer_type FROM freelancer_profiles WHERE user_id = ?');
$stmt->execute([$service['freelancer_id']]);
$fpRow = $stmt->fetch();
$freelancerType = $fpRow['freelancer_type'] ?? 'general';
$isScheduled = in_array($freelancerType, ['electrician', 'plumber'], true);

$stmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS rating_count FROM freelancer_ratings WHERE freelancer_id = ?');
$stmt->execute([$service['freelancer_id']]);
$ratingInfo = $stmt->fetch();
$avgRating = $ratingInfo['avg_rating'] ? round($ratingInfo['avg_rating'], 1) : 0;
$ratingCount = (int)($ratingInfo['rating_count'] ?? 0);

$stmt = $pdo->prepare('SELECT fr.id AS rating_id, fr.rating, fr.review, fr.created_at, u.name AS reviewer_name FROM freelancer_ratings fr JOIN users u ON u.id = fr.reviewer_id WHERE fr.freelancer_id = ? ORDER BY fr.created_at DESC LIMIT 10');
$stmt->execute([$service['freelancer_id']]);
$reviews = $stmt->fetchAll();

$reviewImages = [];
if (!empty($reviews)) {
    $ratingIds = array_column($reviews, 'rating_id');
    $placeholders = implode(',', array_fill(0, count($ratingIds), '?'));
    $stmt = $pdo->prepare("SELECT rating_id, file_path FROM rating_images WHERE rating_id IN ($placeholders) ORDER BY id");
    $stmt->execute($ratingIds);
    foreach ($stmt->fetchAll() as $img) {
        $reviewImages[(int)$img['rating_id']][] = $img['file_path'];
    }
}

$availableSlots = [];
if ($isScheduled) {
    $stmt = $pdo->prepare('SELECT id, available_date, slot_time FROM service_availability WHERE service_id = ? AND available_date >= CURDATE() AND is_booked = 0 ORDER BY available_date, slot_time');
    $stmt->execute([$id]);
    $availableSlots = $stmt->fetchAll();
}

$slotsByDate = [];
foreach ($availableSlots as $s) {
    $slotsByDate[$s['available_date']][] = $s;
}

$canRequest = isLoggedIn() && in_array(currentUserRole(), [ROLE_USER, ROLE_COMPANY], true);
$requestError = '';
$requestSuccess = false;
$hasPrice = $service['price'] !== null && (float)$service['price'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_service']) && $canRequest) {
    $message = trim($_POST['message'] ?? '');
    $slotId = (int)($_POST['slot_id'] ?? 0);

    if ($isScheduled) {
        if (!$slotId) {
            $requestError = 'Please select a date and time for the booking.';
        } else {
            $stmt = $pdo->prepare('SELECT id, available_date, slot_time FROM service_availability WHERE id = ? AND service_id = ? AND is_booked = 0');
            $stmt->execute([$slotId, $id]);
            $chosenSlot = $stmt->fetch();
            if (!$chosenSlot) {
                $requestError = 'The selected time slot is no longer available. Please choose another.';
            }
        }
    }

    if (!$requestError) {
        $pdo->beginTransaction();
        try {
            $bookingDate = isset($chosenSlot) ? $chosenSlot['available_date'] : null;
            $bookingTime = isset($chosenSlot) ? $chosenSlot['slot_time'] : null;
            $bookingSlotId = isset($chosenSlot) ? $chosenSlot['id'] : null;
            $paymentAmount = $hasPrice ? (float)$service['price'] : null;

            $stmt = $pdo->prepare('INSERT INTO service_requests (service_id, requester_id, message, booking_date, booking_time, booking_slot_id, payment_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$id, currentUserId(), $message, $bookingDate, $bookingTime, $bookingSlotId, $paymentAmount, SERVICE_REQUEST_PENDING]);

            if ($bookingSlotId) {
                $pdo->prepare('UPDATE service_availability SET is_booked = 1 WHERE id = ?')->execute([$bookingSlotId]);
            }

            $pdo->commit();
            $requestSuccess = true;

            if ($isScheduled) {
                $stmt = $pdo->prepare('SELECT id, available_date, slot_time FROM service_availability WHERE service_id = ? AND available_date >= CURDATE() AND is_booked = 0 ORDER BY available_date, slot_time');
                $stmt->execute([$id]);
                $availableSlots = $stmt->fetchAll();
                $slotsByDate = [];
                foreach ($availableSlots as $s) {
                    $slotsByDate[$s['available_date']][] = $s;
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $requestError = 'Something went wrong, please try again.';
        }
    }
}

$pageTitle = $service['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="detail-header">
    <div class="container">
        <p class="breadcrumb"><a href="<?= BASE_URL ?>/services.php">Services</a> &rarr; <?= e($service['title']) ?></p>
        <h1><?= e($service['title']) ?>
            <?php if ($isScheduled): ?>
                <span class="status status-active" style="font-size:.7rem;vertical-align:middle"><?= e(ucfirst($freelancerType)) ?></span>
            <?php endif; ?>
        </h1>
        <div class="detail-meta">
            <span class="detail-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?= e($service['freelancer_name']) ?>
            </span>
            <?php if ($ratingCount > 0): ?>
                <span class="detail-meta-dot"></span>
                <a href="#reviews" class="detail-meta-item" style="text-decoration:none;color:var(--gray-400)">
                    <?= renderStars($avgRating) ?>
                    <span style="font-weight:600"><?= number_format($avgRating, 1) ?></span>
                    <span>(<?= $ratingCount ?>)</span>
                </a>
            <?php endif; ?>
            <?php if (isLoggedIn() && currentUserId() !== (int)$service['freelancer_id'] && currentUserRole() !== ROLE_ADMIN): ?>
                <span class="detail-meta-dot"></span>
                <a href="<?= BASE_URL ?>/messages/chat.php?with=<?= (int)$service['freelancer_id'] ?>" class="btn btn-sm" style="background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.2);font-size:.78rem">Message</a>
            <?php endif; ?>
        </div>
        <div class="detail-tags">
            <?php if ($hasPrice): ?>
                <span class="detail-tag tag-price">$<?= number_format($service['price'], 2) ?> <?= e($service['price_type']) ?></span>
            <?php else: ?>
                <span class="detail-tag">Contact for price</span>
            <?php endif; ?>
            <?php if ($hasPrice): ?>
                <span class="detail-tag">Payment after completion</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container detail-content">
    <div class="detail-section">
        <h2 class="detail-section-title">About this service</h2>
        <div class="content-block"><?= nl2br(e($service['description'])) ?></div>
    </div>

    <?php if ($requestSuccess): ?>
        <div class="alert alert-success">Service request sent<?= $isScheduled ? ' and time slot booked' : '' ?>. The freelancer will respond shortly.<?= $hasPrice ? ' You will be able to pay once the service is completed.' : '' ?></div>
    <?php endif; ?>

    <?php if ($canRequest): ?>
        <div class="card form-card" style="margin-bottom:1.5rem">
            <h2>Request this service</h2>
            <?php if ($requestError): ?>
                <div class="alert alert-error"><?= e($requestError) ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <input type="hidden" name="request_service" value="1">

                <?php if ($isScheduled): ?>
                    <div class="form-group">
                        <label>Select a date &amp; time <span class="required">*</span></label>
                        <?php if (empty($slotsByDate)): ?>
                            <p class="muted">No available time slots at the moment. Check back later.</p>
                        <?php else: ?>
                            <?php foreach ($slotsByDate as $date => $daySlots): ?>
                                <div class="avail-booking-day">
                                    <strong class="avail-day-heading"><?= date('l, M j, Y', strtotime($date)) ?></strong>
                                    <div class="avail-dates-grid">
                                        <?php foreach ($daySlots as $slot): ?>
                                            <label class="avail-date-option">
                                                <input type="radio" name="slot_id" value="<?= (int)$slot['id'] ?>" required>
                                                <div>
                                                    <span class="date-label"><?= date('g:i A', strtotime($slot['slot_time'])) ?></span>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="4" placeholder="Describe your needs..."></textarea>
                </div>

                <?php if (!$isScheduled || !empty($slotsByDate)): ?>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                <?php endif; ?>
            </form>
        </div>
    <?php elseif (!isLoggedIn()): ?>
        <div class="card" style="text-align:center;padding:2rem;margin-bottom:1.5rem">
            <p><a href="<?= BASE_URL ?>/auth/login.php">Log in</a> or <a href="<?= BASE_URL ?>/auth/register.php">register</a> to request this service.</p>
        </div>
    <?php endif; ?>

    <?php if ($ratingCount > 0): ?>
    <div class="detail-section" id="reviews">
        <h2 class="detail-section-title">Reviews (<?= $ratingCount ?>)</h2>
        <div class="rating-summary" style="margin-bottom:1rem;">
            <?= renderStars($avgRating) ?>
            <span class="rating-number"><?= number_format($avgRating, 1) ?></span>
            <span class="rating-count">(<?= $ratingCount ?> review<?= $ratingCount > 1 ? 's' : '' ?>)</span>
        </div>
        <?php foreach ($reviews as $rev): ?>
            <div class="review-card">
                <div class="review-header">
                    <?= renderStars($rev['rating']) ?>
                    <span class="review-author"><?= e($rev['reviewer_name']) ?></span>
                    <span class="review-date"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
                </div>
                <?php if ($rev['review']): ?>
                    <p class="review-text"><?= nl2br(e($rev['review'])) ?></p>
                <?php endif; ?>
                <?php $imgs = $reviewImages[(int)$rev['rating_id']] ?? []; ?>
                <?php if (!empty($imgs)): ?>
                    <div class="review-images">
                        <?php foreach ($imgs as $imgPath): ?>
                            <a href="<?= BASE_URL ?>/uploads/rating_images/<?= e($imgPath) ?>" target="_blank" class="review-img-thumb">
                                <img src="<?= BASE_URL ?>/uploads/rating_images/<?= e($imgPath) ?>" alt="Review photo" loading="lazy">
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
