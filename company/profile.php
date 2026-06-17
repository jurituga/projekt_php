<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_COMPANY);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare('SELECT * FROM companies WHERE user_id = ?');
$stmt->execute([$userId]);
$company = $stmt->fetch();

if (!$company) {
    $pdo->prepare('INSERT INTO companies (user_id, company_name) VALUES (?, ?)')->execute([$userId, $_SESSION['user_name'] ?? 'My Company']);
    $stmt = $pdo->prepare('SELECT * FROM companies WHERE user_id = ?');
    $stmt->execute([$userId]);
    $company = $stmt->fetch();
}

$stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $businessReg = trim($_POST['business_registration_number'] ?? '');
    $taxIdVat = trim($_POST['tax_id_vat'] ?? '');
    $govIdRef = trim($_POST['government_id_ref'] ?? '');

    $govIdPath = isset($company['government_id_path']) ? $company['government_id_path'] : null;
    if (!empty($_FILES['government_id_file']['name']) && $_FILES['government_id_file']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['government_id_file']['size'] > UPLOAD_MAX_DOC_SIZE) {
            $error = 'Government ID file too large (max 5MB).';
        } elseif (!in_array($_FILES['government_id_file']['type'], ALLOWED_DOC_TYPES, true)) {
            $error = 'Government ID must be PDF or image (JPEG, PNG, GIF).';
        } else {
            $ext = pathinfo($_FILES['government_id_file']['name'], PATHINFO_EXTENSION) ?: 'pdf';
            $filename = 'gov_' . $userId . '_' . time() . '.' . strtolower($ext);
            $filepath = UPLOAD_PATH_GOV_ID . $filename;
            if (move_uploaded_file($_FILES['government_id_file']['tmp_name'], $filepath)) {
                $govIdPath = $filename;
            } else {
                $error = 'Could not save government ID file.';
            }
        }
    }

    if (!$error && strlen($name) < 2) {
        $error = 'Contact name must be at least 2 characters.';
    }
    if (!$error && strlen($companyName) < 2) {
        $error = 'Company name must be at least 2 characters.';
    }

    if (!$error) {
        $stmt = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->execute([$name, $userId]);

        $hasVerificationCols = isset($company['business_registration_number']);
        if ($hasVerificationCols) {
            $pdo->prepare('UPDATE companies SET company_name = ?, description = ?, industry = ?, website = ?, phone = ?, address = ?, business_registration_number = ?, tax_id_vat = ?, government_id_ref = ?, government_id_path = COALESCE(?, government_id_path) WHERE user_id = ?')
                ->execute([$companyName, $description, $industry, $website, $phone, $address, $businessReg ?: null, $taxIdVat ?: null, $govIdRef ?: null, $govIdPath, $userId]);
        } else {
            $pdo->prepare('UPDATE companies SET company_name = ?, description = ?, industry = ?, website = ?, phone = ?, address = ? WHERE user_id = ?')
                ->execute([$companyName, $description, $industry, $website, $phone, $address, $userId]);
        }
        $_SESSION['user_name'] = $name;
        $success = true;
        $company = array_merge($company, [
            'company_name' => $companyName, 'description' => $description, 'industry' => $industry,
            'website' => $website, 'phone' => $phone, 'address' => $address,
            'business_registration_number' => $businessReg, 'tax_id_vat' => $taxIdVat,
            'government_id_ref' => $govIdRef, 'government_id_path' => $govIdPath ?: ($company['government_id_path'] ?? null)
        ]);
        $user['name'] = $name;
    }
}

$pageTitle = 'Company Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Company Profile</h1>
    <?php if ($success): ?>
        <div class="alert alert-success">Profile updated.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form-card">
        <h2>Contact</h2>
        <div class="form-group">
            <label for="name">Contact Name</label>
            <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <p class="muted"><?= e($user['email']) ?></p>
        </div>
        <h2>Company</h2>
        <div class="form-group">
            <label for="company_name">Company Name</label>
            <input type="text" id="company_name" name="company_name" value="<?= e($company['company_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?= e($company['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="industry">Industry</label>
            <input type="text" id="industry" name="industry" value="<?= e($company['industry'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="website">Website</label>
            <input type="url" id="website" name="website" value="<?= e($company['website'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?= e($company['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="2"><?= e($company['address'] ?? '') ?></textarea>
        </div>

        <h2 class="form-section-title">Verification &amp; trust</h2>
        <div class="form-group">
            <label for="business_registration_number">Business registration number</label>
            <input type="text" id="business_registration_number" name="business_registration_number" value="<?= e($company['business_registration_number'] ?? '') ?>" placeholder="Company registration ID">
        </div>
        <div class="form-group">
            <label for="tax_id_vat">Tax ID / VAT number</label>
            <input type="text" id="tax_id_vat" name="tax_id_vat" value="<?= e($company['tax_id_vat'] ?? '') ?>" placeholder="Region-dependent">
        </div>
        <div class="form-group">
            <label for="government_id_ref">Government ID number or reference</label>
            <input type="text" id="government_id_ref" name="government_id_ref" value="<?= e($company['government_id_ref'] ?? '') ?>" placeholder="ID number">
        </div>
        <div class="form-group">
            <label for="government_id_file">Government ID (upload)</label>
            <input type="file" id="government_id_file" name="government_id_file" accept=".pdf,.jpg,.jpeg,.png,.gif,application/pdf,image/jpeg,image/png,image/gif">
            <span class="form-hint">PDF or image (JPEG, PNG, GIF). Max 5MB.</span>
            <?php if (!empty($company['government_id_path'])): ?>
                <p class="muted">Current file uploaded. Upload a new file to replace.</p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
    <p><a href="<?= BASE_URL ?>/company/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
