<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_FREELANCER);

$userId = currentUserId();
$pdo = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

// Get freelancer type
$stmt = $pdo->prepare('SELECT freelancer_type FROM freelancer_profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$fpRow = $stmt->fetch();
$freelancerType = $fpRow['freelancer_type'] ?? 'general';
$isScheduled = in_array($freelancerType, ['electrician', 'plumber'], true);

$service = null;
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ? AND freelancer_id = ?');
    $stmt->execute([$id, $userId]);
    $service = $stmt->fetch();
    if (!$service) {
        header('Location: ' . BASE_URL . '/freelancer/services.php');
        exit;
    }
}

$error = '';
$success = false;

// ── Handle service create / update ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_service'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : null;
    $priceType = $_POST['price_type'] ?? 'fixed';
    $status = $_POST['status'] ?? 'active';
    if (!in_array($priceType, ['fixed', 'hourly'], true)) $priceType = 'fixed';
    if (!in_array($status, ['active', 'inactive'], true)) $status = 'active';

    if (strlen($title) < 2) {
        $error = 'Title must be at least 2 characters.';
    } else {
        if ($isEdit) {
            $pdo->prepare('UPDATE services SET title = ?, description = ?, price = ?, price_type = ?, status = ? WHERE id = ? AND freelancer_id = ?')
                ->execute([$title, $description, $price, $priceType, $status, $id, $userId]);
            $success = true;
            $service = array_merge($service, ['title' => $title, 'description' => $description, 'price' => $price, 'price_type' => $priceType, 'status' => $status]);
        } else {
            $pdo->prepare('INSERT INTO services (freelancer_id, title, description, price, price_type, status) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$userId, $title, $description, $price, $priceType, $status]);
            $id = (int) $pdo->lastInsertId();
            $isEdit = true;
            $service = ['id' => $id, 'title' => $title, 'description' => $description, 'price' => $price, 'price_type' => $priceType, 'status' => $status];
            $success = true;
            // Don't redirect — stay on page so they can add availability
        }
    }
}

// ── Handle add availability slots ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slots']) && $isEdit) {
    $slotDate = trim($_POST['slot_date'] ?? '');
    $slotTimes = $_POST['slot_times'] ?? [];

    if (empty($slotDate) || strtotime($slotDate) < strtotime('today')) {
        $error = 'Please select a valid future date.';
    } elseif (empty($slotTimes)) {
        $error = 'Please select at least one time slot.';
    } else {
        $added = 0;
        $stmt = $pdo->prepare('INSERT IGNORE INTO service_availability (service_id, available_date, slot_time) VALUES (?, ?, ?)');
        foreach ($slotTimes as $t) {
            // Validate time format
            if (preg_match('/^\d{2}:\d{2}$/', $t)) {
                $stmt->execute([$id, $slotDate, $t . ':00']);
                $added += $stmt->rowCount();
            }
        }
        if ($added > 0) {
            $success = true;
        }
    }
}

// ── Handle remove a slot ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_slot']) && $isEdit) {
    $slotId = (int)($_POST['slot_id'] ?? 0);
    if ($slotId) {
        $pdo->prepare('DELETE FROM service_availability WHERE id = ? AND service_id = ? AND is_booked = 0')
            ->execute([$slotId, $id]);
    }
}

// ── Fetch existing slots for this service ──
$slots = [];
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT id, available_date, slot_time, is_booked FROM service_availability WHERE service_id = ? AND available_date >= CURDATE() ORDER BY available_date, slot_time');
    $stmt->execute([$id]);
    $slots = $stmt->fetchAll();
}

// Group slots by date for display
$slotsByDate = [];
foreach ($slots as $s) {
    $slotsByDate[$s['available_date']][] = $s;
}

// Time options for the picker
$timeOptions = [];
for ($h = 6; $h <= 21; $h++) {
    $timeOptions[] = sprintf('%02d:00', $h);
    if ($h < 21) {
        $timeOptions[] = sprintf('%02d:30', $h);
    }
}

$pageTitle = $isEdit ? 'Edit Service' : 'Add Service';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1><?= $isEdit ? 'Edit Service' : 'Add Service' ?>
        <?php if ($isScheduled): ?>
            <span class="status status-active"><?= e(ucfirst($freelancerType)) ?></span>
        <?php endif; ?>
    </h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $isEdit ? 'Service updated.' : 'Service created.' ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- ── Service Details Form ── -->
    <form method="post" class="form-card">
        <input type="hidden" name="save_service" value="1">
        <h2 class="form-section-title">Service details</h2>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?= e($service['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" required><?= e($service['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= isset($service['price']) && $service['price'] !== null && $service['price'] !== '' ? e((string)$service['price']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="price_type">Price type</label>
                <select id="price_type" name="price_type">
                    <option value="fixed" <?= ($service['price_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                    <option value="hourly" <?= ($service['price_type'] ?? '') === 'hourly' ? 'selected' : '' ?>>Hourly</option>
                </select>
            </div>
        </div>
        <?php if ($isEdit): ?>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= ($service['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($service['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> Service</button>
    </form>

    <?php if ($isEdit): ?>
    <!-- ── Availability Section ── -->
    <div class="card form-card" style="margin-top:2rem;">
        <h2 class="form-section-title">Availability &mdash; add dates &amp; time slots</h2>
        <p class="muted">Add the dates and specific times when you are available for this service. Clients will pick from these when booking.</p>

        <form method="post">
            <input type="hidden" name="add_slots" value="1">
            <div class="form-row">
                <div class="form-group">
                    <label for="slot_date">Date</label>
                    <input type="date" id="slot_date" name="slot_date" min="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Select time slots</label>
                <div class="time-slots-grid">
                    <?php foreach ($timeOptions as $t): ?>
                        <label class="time-slot-option">
                            <input type="checkbox" name="slot_times[]" value="<?= $t ?>">
                            <span><?= date('g:i A', strtotime($t)) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <span class="form-hint">Pick one or more times you're available on this date.</span>
            </div>
            <button type="submit" class="btn btn-primary">Add Slots</button>
        </form>
    </div>

    <!-- ── Current Availability ── -->
    <?php if (!empty($slotsByDate)): ?>
    <div class="card" style="margin-top:1.5rem;">
        <h2>Current availability</h2>
        <?php foreach ($slotsByDate as $date => $daySlots): ?>
            <div class="avail-day-group">
                <h3 class="avail-day-heading"><?= date('l, M j, Y', strtotime($date)) ?></h3>
                <div class="avail-slots-row">
                    <?php foreach ($daySlots as $slot): ?>
                        <div class="avail-slot-chip <?= $slot['is_booked'] ? 'booked' : '' ?>">
                            <span><?= date('g:i A', strtotime($slot['slot_time'])) ?></span>
                            <?php if ($slot['is_booked']): ?>
                                <span class="chip-badge">Booked</span>
                            <?php else: ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="remove_slot" value="1">
                                    <input type="hidden" name="slot_id" value="<?= (int)$slot['id'] ?>">
                                    <button type="submit" class="chip-remove" title="Remove">&times;</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <p style="margin-top:1.5rem;"><a href="<?= BASE_URL ?>/freelancer/services.php">&larr; Back to Services</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
