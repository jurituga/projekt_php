# Job & Services Platform

A PHP and MySQL job portal and freelance services marketplace with role-based dashboards.

## Technologies

- **Backend & Frontend:** PHP (no frameworks)
- **Database:** MySQL
- **Styling:** CSS
- **Server:** XAMPP

## User Roles

1. **Job Seeker (user)** – Apply for jobs, upload CVs, request services
2. **Service Provider (freelancer)** – Create services, manage service requests
3. **Company** – Post jobs, manage applications, request services
4. **Admin** – Manage users, jobs, services, view statistics

## Setup (XAMPP)

1. **Start XAMPP**  
   Start Apache and MySQL from the XAMPP Control Panel.

2. **Create the database**  
   - Open phpMyAdmin: http://localhost/phpmyadmin  
   - Import `database/schema.sql` (or run it from the SQL tab).  
   - This creates the database `webjob` and all tables.

2a. **Optional: add verification columns (if DB already exists)**  
   If you created the database before verification was added, import `database/verification_migration.sql` once to add identity/business and certifications columns to `companies` and `freelancer_profiles`. New installs from the current `schema.sql` already include these.

2b. **Optional: add sample jobs and freelancers**  
   - Import `database/seed.sql` (same database in phpMyAdmin, or: `mysql -u root webjob < database/seed.sql`).  
   - This adds 3 companies with 8 published jobs, and 3 freelancers with 9 public services.  
   - All seed accounts use **password:** `password` (e.g. sarah@techcorp.com, alex@freelance.dev, jordan@designs.io, sam@webdev.pro).

3. **Default admin**  
   After importing the schema:
   - **Email:** `admin@platform.com`
   - **Password:** `password` (from schema)  
   To use **admin123** instead, run once: **http://localhost/web/database/setup_admin.php**  
   Then delete or protect that file.

4. **Project URL**  
   Open: **http://localhost/web/**  
   If you see a blank page: (1) Make sure Apache and MySQL are running in XAMPP. (2) Import `database/schema.sql` in phpMyAdmin so the database exists. (3) Use the exact URL including `/web/` (or your folder name). If the database is missing, the home page will show setup instructions instead of staying blank.  
   If your project is in `htdocs/myproject` instead of `htdocs/web`, open http://localhost/myproject/ and in `config/constants.php` set `BASE_URL` to `'/myproject'`.

5. **Upload folders (fix “Permission denied”)**  
   If you see “Permission denied” for `uploads/cvs` or `uploads/images`, create them and allow the web server to write. In Terminal, from the project folder (`htdocs/web`):
   ```bash
   mkdir -p uploads/cvs uploads/images
   chmod -R 777 uploads
   ```
   On macOS with XAMPP, if it still fails, try: `sudo chown -R daemon:daemon uploads` (Apache may run as `daemon`).

6. **Database config**  
   Edit `config/database.php` if your MySQL user/password differ:
   - Default: user `root`, password empty, database `webjob`.

## Project Structure

```
/config       – database.php, constants.php, init.php
/auth         – login.php, register.php, logout.php
/admin        – admin dashboard, users, jobs, services
/company      – company dashboard, jobs, applications
/freelancer   – freelancer dashboard, services, requests
/user         – job seeker dashboard, profile, CVs, applications
/uploads      – CV storage; access via uploads/cv.php
/assets       – css/style.css, js/main.js
/includes     – header.php, footer.php
database/     – schema.sql
index.php     – home
jobs.php      – job listings
job.php       – single job (apply)
services.php  – service listings
service.php   – single service (request)
```

## Security

- Sessions for authentication
- `password_hash()` / `password_verify()` for passwords
- PDO prepared statements for queries
- Role checks on dashboard and action pages
- CV download restricted to owner, admin, or company viewing an applicant’s CV

## Quick Test

1. Register as Job Seeker, Freelancer, or Company.
2. Job Seeker: upload a CV, apply to a job (create one as Company first).
3. Company: create a company profile, post a job, view applications.
4. Freelancer: complete profile, create a service, accept/reject requests.
5. Admin: log in with “Admin login” checked; manage users, jobs, services.
