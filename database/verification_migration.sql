-- Verification & Trust: add identity/business and certifications fields
-- Run once: Import this in phpMyAdmin (select database "webjob") or: mysql -u root webjob < database/verification_migration.sql

USE webjob;

-- Company: business verification
ALTER TABLE companies
    ADD COLUMN business_registration_number VARCHAR(100) NULL AFTER address,
    ADD COLUMN tax_id_vat VARCHAR(100) NULL AFTER business_registration_number,
    ADD COLUMN government_id_ref VARCHAR(100) NULL COMMENT 'Government ID number or reference' AFTER tax_id_vat,
    ADD COLUMN government_id_path VARCHAR(255) NULL COMMENT 'Uploaded gov ID file path' AFTER government_id_ref;

-- Freelancer: identity + certifications
ALTER TABLE freelancer_profiles
    ADD COLUMN government_id_ref VARCHAR(100) NULL COMMENT 'Government ID number or reference' AFTER hourly_rate,
    ADD COLUMN government_id_path VARCHAR(255) NULL COMMENT 'Uploaded gov ID file path' AFTER government_id_ref,
    ADD COLUMN qualifications TEXT NULL COMMENT 'Licenses, certifications, qualifications list' AFTER government_id_path,
    ADD COLUMN certification_path VARCHAR(255) NULL COMMENT 'Uploaded certification document path' AFTER qualifications;
