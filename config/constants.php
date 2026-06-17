<?php
/**
 * Application constants and role definitions
 */

// Base URL path (e.g. '/web' when app is at http://localhost/web). Use '' if document root is this project.
define('BASE_URL', '/web');

define('ROLE_USER', 'user');        // Job Seeker
define('ROLE_FREELANCER', 'freelancer');
define('ROLE_COMPANY', 'company');
define('ROLE_ADMIN', 'admin');

define('USER_STATUS_PENDING', 'pending');
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_BLOCKED', 'blocked');

define('APPLICATION_STATUS_PENDING', 'pending');
define('APPLICATION_STATUS_VIEWED', 'viewed');
define('APPLICATION_STATUS_ACCEPTED', 'accepted');
define('APPLICATION_STATUS_REJECTED', 'rejected');

define('SERVICE_REQUEST_PENDING', 'pending');
define('SERVICE_REQUEST_ACCEPTED', 'accepted');
define('SERVICE_REQUEST_REJECTED', 'rejected');
define('SERVICE_REQUEST_COMPLETED', 'completed');

// Stripe (test mode)
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51RyrGKPKDuGiHLLadVfaG7dgjemHlaxcAAtrNUiE0aPorS3L9oMjAoQ7Y1hf7Uuj4jr9ouakfcQOnYjFxx6S3EDr00KsjD0eS5');
define('STRIPE_SECRET_KEY', 'sk_test_51RyrGKPKDuGiHLLaeLCJq98h8BWW0l7TUoJX5hoQvzOcigQwKKbzz6gsz4uzc0HEyosfTFY27zGWIsv4xvNALyzj00g6OZEp6f');
define('STRIPE_CURRENCY', 'usd');

define('UPLOAD_PATH_CV', __DIR__ . '/../uploads/cvs/');
define('UPLOAD_PATH_IMAGES', __DIR__ . '/../uploads/images/');
define('UPLOAD_PATH_GOV_ID', __DIR__ . '/../uploads/government_ids/');
define('UPLOAD_PATH_CERTIFICATIONS', __DIR__ . '/../uploads/certifications/');
define('UPLOAD_PATH_RATING_IMAGES', __DIR__ . '/../uploads/rating_images/');
define('UPLOAD_MAX_CV_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_MAX_DOC_SIZE', 5 * 1024 * 1024); // 5MB for gov ID / certs
define('UPLOAD_MAX_RATING_IMG_SIZE', 5 * 1024 * 1024); // 5MB per image
define('UPLOAD_MAX_RATING_IMAGES', 5); // max 5 images per review
define('ALLOWED_CV_TYPES', ['application/pdf']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_RATING_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
