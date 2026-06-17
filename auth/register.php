<?php
require_once __DIR__ . '/../config/init.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';
$success = '';

/**
 * Handle a single file upload. Returns filename on success, null if no file, or sets $error.
 */
function handleUpload(string $field, string $destPath, string $prefix, int $maxSize, array $allowedTypes, string &$error): ?string {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed for ' . $field . '. Please try again.';
        return null;
    }
    if ($_FILES[$field]['size'] > $maxSize) {
        $error = 'File too large (max 5MB) for ' . $field . '.';
        return null;
    }
    if (!in_array($_FILES[$field]['type'], $allowedTypes, true)) {
        $error = 'Invalid file type for ' . $field . '. Allowed: PDF, JPEG, PNG, GIF.';
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION) ?: 'pdf');
    $filename = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $destPath . $filename)) {
        $error = 'Could not save file for ' . $field . '.';
        return null;
    }
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? ROLE_USER;

    $allowed_roles = [ROLE_USER, ROLE_FREELANCER, ROLE_COMPANY];
    if (!in_array($role, $allowed_roles, true)) {
        $role = ROLE_USER;
    }

    // Company fields
    $company_name = trim($_POST['company_name'] ?? '');
    $company_industry = trim($_POST['company_industry'] ?? '');
    $company_description = trim($_POST['company_description'] ?? '');
    $company_website = trim($_POST['company_website'] ?? '');
    $company_phone = trim($_POST['company_phone'] ?? '');
    $company_business_reg = trim($_POST['company_business_registration'] ?? '');
    $company_tax_id = trim($_POST['company_tax_id'] ?? '');

    // Freelancer fields
    $freelancer_type = trim($_POST['freelancer_type'] ?? 'general');
    $allowed_freelancer_types = ['general', 'electrician', 'plumber'];
    if (!in_array($freelancer_type, $allowed_freelancer_types, true)) {
        $freelancer_type = 'general';
    }
    $freelancer_bio = trim($_POST['freelancer_bio'] ?? '');
    $freelancer_skills = trim($_POST['freelancer_skills'] ?? '');
    $freelancer_hourly_rate = isset($_POST['freelancer_hourly_rate']) && $_POST['freelancer_hourly_rate'] !== '' ? (float) $_POST['freelancer_hourly_rate'] : null;
    $freelancer_qualifications = trim($_POST['freelancer_qualifications'] ?? '');

    // Validation
    if (strlen($name) < 2) {
        $error = 'Name must be at least 2 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif ($role === ROLE_COMPANY && strlen($company_name) < 2) {
        $error = 'Company name is required (at least 2 characters).';
    } elseif ($role === ROLE_COMPANY && strlen($company_industry) < 2) {
        $error = 'Industry is required for company registration.';
    } elseif ($role === ROLE_FREELANCER && strlen($freelancer_bio) < 20) {
        $error = 'Please add a short bio (at least 20 characters) for your freelancer profile.';
    } elseif ($role === ROLE_FREELANCER && strlen($freelancer_skills) < 2) {
        $error = 'Please list at least one skill (e.g. PHP, Design).';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    }

    // Handle file uploads for Company
    $company_gov_id_path = null;
    $company_business_reg_path = null;
    if (!$error && $role === ROLE_COMPANY) {
        $company_gov_id_path = handleUpload('company_government_id_file', UPLOAD_PATH_GOV_ID, 'company_gov', UPLOAD_MAX_DOC_SIZE, ALLOWED_DOC_TYPES, $error);
        if (!$error) {
            $company_business_reg_path = handleUpload('company_business_reg_file', UPLOAD_PATH_GOV_ID, 'company_reg', UPLOAD_MAX_DOC_SIZE, ALLOWED_DOC_TYPES, $error);
        }
    }

    // Handle file uploads for Freelancer
    $freelancer_gov_id_path = null;
    $freelancer_cert_path = null;
    if (!$error && $role === ROLE_FREELANCER) {
        $freelancer_gov_id_path = handleUpload('freelancer_government_id_file', UPLOAD_PATH_GOV_ID, 'freelancer_gov', UPLOAD_MAX_DOC_SIZE, ALLOWED_DOC_TYPES, $error);
        if (!$error) {
            $freelancer_cert_path = handleUpload('freelancer_certification_file', UPLOAD_PATH_CERTIFICATIONS, 'freelancer_cert', UPLOAD_MAX_DOC_SIZE, ALLOWED_DOC_TYPES, $error);
        }
    }

    if (!$error) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $status = ($role === ROLE_COMPANY || $role === ROLE_FREELANCER) ? USER_STATUS_PENDING : USER_STATUS_ACTIVE;
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, $role, $status]);

            $userId = (int) $pdo->lastInsertId();

            if ($role === ROLE_COMPANY) {
                $stmt = $pdo->prepare('INSERT INTO companies (user_id, company_name, description, industry, website, phone, business_registration_number, tax_id_vat, government_id_ref, government_id_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$userId, $company_name, $company_description ?: null, $company_industry ?: null, $company_website ?: null, $company_phone ?: null, $company_business_reg ?: null, $company_tax_id ?: null, $company_business_reg_path, $company_gov_id_path]);
            } elseif ($role === ROLE_FREELANCER) {
                $stmt = $pdo->prepare('INSERT INTO freelancer_profiles (user_id, freelancer_type, bio, skills, hourly_rate, government_id_ref, government_id_path, qualifications, certification_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$userId, $freelancer_type, $freelancer_bio, $freelancer_skills, $freelancer_hourly_rate, null, $freelancer_gov_id_path, $freelancer_qualifications ?: null, $freelancer_cert_path]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO user_profiles (user_id) VALUES (?)');
                $stmt->execute([$userId]);
            }

            if ($role === ROLE_COMPANY || $role === ROLE_FREELANCER) {
                header('Location: ' . BASE_URL . '/auth/login.php?pending=1');
                exit;
            }

            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = $role;
            header('Location: ' . BASE_URL . '/user/dashboard.php');
            exit;
        }
    }
}

$post_role = $_POST['role'] ?? '';
$is_company = $post_role === ROLE_COMPANY;
$is_freelancer = $post_role === ROLE_FREELANCER;

$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box auth-box-wide">
        <h1>Register</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="" id="register-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Full Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="role">I am a <span class="required">*</span></label>
                <select id="role" name="role">
                    <option value="user" <?= $post_role === 'user' ? 'selected' : '' ?>>Job Seeker</option>
                    <option value="freelancer" <?= $post_role === 'freelancer' ? 'selected' : '' ?>>Service Provider (Freelancer)</option>
                    <option value="company" <?= $post_role === 'company' ? 'selected' : '' ?>>Company</option>
                </select>
            </div>

            <!-- Company fields -->
            <div class="form-section" id="company_fields" style="display:<?= $is_company ? 'block' : 'none' ?>">
                <h2 class="form-section-title">Company details</h2>
                <div class="form-group">
                    <label for="company_name">Company Name <span class="required">*</span></label>
                    <input type="text" id="company_name" name="company_name" value="<?= e($_POST['company_name'] ?? '') ?>" <?= $is_company ? 'required' : '' ?> placeholder="e.g. Acme Inc">
                </div>
                <div class="form-group">
                    <label for="company_industry">Industry <span class="required">*</span></label>
                    <input type="text" id="company_industry" name="company_industry" value="<?= e($_POST['company_industry'] ?? '') ?>" <?= $is_company ? 'required' : '' ?> placeholder="e.g. Technology, Design, Healthcare">
                </div>
                <div class="form-group">
                    <label for="company_description">Description</label>
                    <textarea id="company_description" name="company_description" rows="3" placeholder="What does your company do?"><?= e($_POST['company_description'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="company_website">Website</label>
                        <input type="url" id="company_website" name="company_website" value="<?= e($_POST['company_website'] ?? '') ?>" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label for="company_phone">Phone</label>
                        <input type="text" id="company_phone" name="company_phone" value="<?= e($_POST['company_phone'] ?? '') ?>" placeholder="+1 234 567 8900">
                    </div>
                </div>

                <h2 class="form-section-title">Verification &amp; trust</h2>
                <div class="form-group">
                    <label for="company_business_registration">Business registration number</label>
                    <input type="text" id="company_business_registration" name="company_business_registration" value="<?= e($_POST['company_business_registration'] ?? '') ?>" placeholder="e.g. company registration ID">
                </div>
                <div class="form-group">
                    <label for="company_business_reg_file">Business registration document (upload)</label>
                    <input type="file" id="company_business_reg_file" name="company_business_reg_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    <span class="form-hint">PDF or image (JPEG, PNG, GIF). Max 5MB.</span>
                </div>
                <div class="form-group">
                    <label for="company_tax_id">Tax ID / VAT number</label>
                    <input type="text" id="company_tax_id" name="company_tax_id" value="<?= e($_POST['company_tax_id'] ?? '') ?>" placeholder="Region-dependent">
                </div>
                <div class="form-group">
                    <label for="company_government_id_file">Government ID (upload) <span class="required">*</span></label>
                    <input type="file" id="company_government_id_file" name="company_government_id_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    <span class="form-hint">Upload your government-issued ID (PDF or image). Max 5MB. Required for verification.</span>
                </div>
            </div>

            <!-- Freelancer fields -->
            <div class="form-section" id="freelancer_fields" style="display:<?= $is_freelancer ? 'block' : 'none' ?>">
                <h2 class="form-section-title">Professional profile</h2>
                <div class="form-group">
                    <label for="freelancer_type">Freelancer type <span class="required">*</span></label>
                    <select id="freelancer_type" name="freelancer_type">
                        <option value="general" <?= ($_POST['freelancer_type'] ?? '') === 'general' ? 'selected' : '' ?>>General Freelancer</option>
                        <option value="electrician" <?= ($_POST['freelancer_type'] ?? '') === 'electrician' ? 'selected' : '' ?>>Electrician</option>
                        <option value="plumber" <?= ($_POST['freelancer_type'] ?? '') === 'plumber' ? 'selected' : '' ?>>Plumber</option>
                    </select>
                    <span class="form-hint">Electricians and Plumbers can manage daily availability for bookings.</span>
                </div>
                <div class="form-group">
                    <label for="freelancer_bio">Bio <span class="required">*</span></label>
                    <textarea id="freelancer_bio" name="freelancer_bio" rows="4" <?= $is_freelancer ? 'required' : '' ?> placeholder="Introduce yourself: experience, expertise, what you offer..."><?= e($_POST['freelancer_bio'] ?? '') ?></textarea>
                    <span class="form-hint">At least 20 characters.</span>
                </div>
                <div class="form-group">
                    <label for="freelancer_skills">Skills <span class="required">*</span></label>
                    <input type="text" id="freelancer_skills" name="freelancer_skills" value="<?= e($_POST['freelancer_skills'] ?? '') ?>" <?= $is_freelancer ? 'required' : '' ?> placeholder="e.g. PHP, MySQL, JavaScript, UI Design">
                    <span class="form-hint">Comma-separated list.</span>
                </div>
                <div class="form-group">
                    <label for="freelancer_hourly_rate">Hourly rate ($)</label>
                    <input type="number" id="freelancer_hourly_rate" name="freelancer_hourly_rate" step="0.01" min="0" value="<?= e($_POST['freelancer_hourly_rate'] ?? '') ?>" placeholder="e.g. 50">
                </div>

                <h2 class="form-section-title">Verification &amp; certifications</h2>
                <div class="form-group">
                    <label for="freelancer_government_id_file">Government ID (upload) <span class="required">*</span></label>
                    <input type="file" id="freelancer_government_id_file" name="freelancer_government_id_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    <span class="form-hint">Upload your government-issued ID (PDF or image). Max 5MB. Required for verification.</span>
                </div>
                <div class="form-group">
                    <label for="freelancer_qualifications">Licenses / certifications / qualifications</label>
                    <textarea id="freelancer_qualifications" name="freelancer_qualifications" rows="3" placeholder="List licenses, certifications, or qualifications..."><?= e($_POST['freelancer_qualifications'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="freelancer_certification_file">Certification or license document (upload)</label>
                    <input type="file" id="freelancer_certification_file" name="freelancer_certification_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    <span class="form-hint">PDF or image. Max 5MB. Optional.</span>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm Password <span class="required">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="auth-link">Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Login</a></p>
    </div>
</div>
<script>
(function() {
    var role = document.getElementById('role');
    var companyFields = document.getElementById('company_fields');
    var freelancerFields = document.getElementById('freelancer_fields');
    var companyName = document.getElementById('company_name');
    var companyIndustry = document.getElementById('company_industry');
    var freelancerBio = document.getElementById('freelancer_bio');
    var freelancerSkills = document.getElementById('freelancer_skills');

    function toggleFields() {
        var v = role.value;
        if (v === 'company') {
            companyFields.style.display = 'block';
            freelancerFields.style.display = 'none';
            companyName.required = true;
            companyIndustry.required = true;
            freelancerBio.required = false;
            freelancerSkills.required = false;
        } else if (v === 'freelancer') {
            companyFields.style.display = 'none';
            freelancerFields.style.display = 'block';
            companyName.required = false;
            companyIndustry.required = false;
            freelancerBio.required = true;
            freelancerSkills.required = true;
        } else {
            companyFields.style.display = 'none';
            freelancerFields.style.display = 'none';
            companyName.required = false;
            companyIndustry.required = false;
            freelancerBio.required = false;
            freelancerSkills.required = false;
        }
    }

    role.addEventListener('change', toggleFields);
    toggleFields();
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
