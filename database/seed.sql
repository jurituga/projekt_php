-- Seed data: companies, jobs, freelancers, services
-- Run AFTER schema.sql. All seed users have password: password
-- Usage: mysql -u root webjob < database/seed.sql

USE webjob;

-- Shared bcrypt hash for "password"
SET @pwd = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- ========== COMPANIES & JOBS ==========

INSERT INTO users (name, email, password, role, status) VALUES
('Sarah Chen', 'sarah@techcorp.com', @pwd, 'company', 'active');
SET @company1_user = LAST_INSERT_ID();
INSERT INTO companies (user_id, company_name, description, industry, website) VALUES
(@company1_user, 'TechCorp Solutions', 'We build web and mobile applications for startups and enterprises.', 'Technology', 'https://techcorp.example.com');
SET @company1_id = LAST_INSERT_ID();

INSERT INTO jobs (company_id, title, description, location, job_type, salary_min, salary_max, status) VALUES
(@company1_id, 'Senior PHP Developer', 'We are looking for an experienced PHP developer to work on our core platform. You will work with MySQL, REST APIs, and modern PHP. Experience with Laravel or similar is a plus.', 'Remote', 'full_time', 80000, 120000, 'published'),
(@company1_id, 'Frontend Developer', 'Join our team to build responsive, accessible UIs. Strong HTML, CSS, and JavaScript skills required. React experience is a bonus but we also use vanilla JS.', 'New York, NY', 'full_time', 70000, 95000, 'published'),
(@company1_id, 'Part-time WordPress Developer', 'Maintain and extend our client WordPress sites. PHP and theme/plugin development experience needed. 20 hours per week.', 'Remote', 'part_time', 35, 50, 'published');

INSERT INTO users (name, email, password, role, status) VALUES
('Mike Johnson', 'mike@designstudio.com', @pwd, 'company', 'active');
SET @company2_user = LAST_INSERT_ID();
INSERT INTO companies (user_id, company_name, description, industry) VALUES
(@company2_user, 'Design Studio Co', 'Creative agency specializing in branding and digital design. We hire freelancers and full-time designers.', 'Design');
SET @company2_id = LAST_INSERT_ID();

INSERT INTO jobs (company_id, title, description, location, job_type, status) VALUES
(@company2_id, 'UI/UX Designer', 'Create user interfaces and experiences for web and mobile. Proficiency in Figma or Sketch required. Portfolio required.', 'Los Angeles, CA', 'full_time', 'published'),
(@company2_id, 'Graphic Design Intern', '6-month internship for a design student. You will assist on real client projects and learn from senior designers.', 'Los Angeles, CA', 'internship', 'published');

INSERT INTO users (name, email, password, role, status) VALUES
('Emma Wilson', 'emma@startup.io', @pwd, 'company', 'active');
SET @company3_user = LAST_INSERT_ID();
INSERT INTO companies (user_id, company_name, description, industry) VALUES
(@company3_user, 'Startup.io', 'Early-stage SaaS startup. Small team, big impact. We are hiring our first engineers.', 'Technology');
SET @company3_id = LAST_INSERT_ID();

INSERT INTO jobs (company_id, title, description, location, job_type, salary_min, salary_max, status) VALUES
(@company3_id, 'Full Stack Developer', 'Help us build our product from the ground up. PHP or Node backend, modern frontend. You will have real ownership.', 'Remote', 'full_time', 90000, 130000, 'published'),
(@company3_id, 'DevOps / Backend Contract', '3-month contract to set up CI/CD and improve our backend. AWS or GCP experience preferred.', 'Remote', 'contract', 60, 90, 'published');

-- ========== FREELANCERS & SERVICES ==========

INSERT INTO users (name, email, password, role, status) VALUES
('Alex Rivera', 'alex@freelance.dev', @pwd, 'freelancer', 'active');
SET @fl1 = LAST_INSERT_ID();
INSERT INTO freelancer_profiles (user_id, bio, skills, hourly_rate) VALUES
(@fl1, 'Full-stack developer with 8+ years experience. I build secure, scalable web applications in PHP, Node.js, and Python. Clean code and on-time delivery.', 'PHP, MySQL, JavaScript, Laravel, API Development', 85.00);

INSERT INTO services (freelancer_id, title, description, price, price_type, status) VALUES
(@fl1, 'PHP Backend Development', 'Custom PHP backend development: REST APIs, database design, authentication, and integration with frontends. I use PDO/MySQLi and follow PSR standards.', 85.00, 'hourly', 'active'),
(@fl1, 'Laravel Project Setup', 'One-time setup of a new Laravel project: structure, auth, roles, and basic CRUD. Includes 2 hours of support. Fixed price for standard scope.', 450.00, 'fixed', 'active'),
(@fl1, 'API Integration Service', 'Integrate your app with third-party APIs (payment, email, CRM, etc.). Documentation and error handling included.', 75.00, 'hourly', 'active');

INSERT INTO users (name, email, password, role, status) VALUES
('Jordan Lee', 'jordan@designs.io', @pwd, 'freelancer', 'active');
SET @fl2 = LAST_INSERT_ID();
INSERT INTO freelancer_profiles (user_id, bio, skills, hourly_rate) VALUES
(@fl2, 'UI/UX designer and front-end developer. I create interfaces that are both beautiful and usable. Strong in Figma, HTML/CSS, and responsive design.', 'Figma, UI Design, UX Research, HTML, CSS, JavaScript', 65.00);

INSERT INTO services (freelancer_id, title, description, price, price_type, status) VALUES
(@fl2, 'Website Redesign', 'Full redesign of your website: research, wireframes, high-fidelity mockups, and handoff to dev. Up to 10 pages.', 1200.00, 'fixed', 'active'),
(@fl2, 'UI Design (per screen)', 'Professional UI design for web or mobile. One screen, multiple iterations. Style guide optional.', 150.00, 'fixed', 'active'),
(@fl2, 'Frontend Development (HTML/CSS/JS)', 'Convert designs to responsive, accessible HTML/CSS/JS. No frameworks unless requested.', 60.00, 'hourly', 'active');

INSERT INTO users (name, email, password, role, status) VALUES
('Sam Taylor', 'sam@webdev.pro', @pwd, 'freelancer', 'active');
SET @fl3 = LAST_INSERT_ID();
INSERT INTO freelancer_profiles (user_id, bio, skills, hourly_rate) VALUES
(@fl3, 'WordPress and small-business website specialist. Themes, plugins, WooCommerce, and maintenance. Fast turnaround.', 'WordPress, PHP, MySQL, WooCommerce, Elementor', 55.00);

INSERT INTO services (freelancer_id, title, description, price, price_type, status) VALUES
(@fl3, 'WordPress Site Build', 'Complete WordPress site: theme setup, pages, contact form, basic SEO. Up to 15 pages. Content entry included.', 800.00, 'fixed', 'active'),
(@fl3, 'WooCommerce Store Setup', 'Set up your online store: products, shipping, payments (Stripe/PayPal). Training session included.', 650.00, 'fixed', 'active'),
(@fl3, 'WordPress Maintenance (monthly)', 'Monthly updates, backups, security check, and 1 hour of small changes. Peace of mind package.', 99.00, 'fixed', 'active');
