# 🎓 C-Familia Tutorial Services

A vanilla PHP web application for managing student enrollment, payments, and administration for a review/tutorial center (e.g. board exam review programs such as Criminology Review).

Built with plain **PHP + MySQL** — no framework — using a simple file-based routing structure and role-based dashboards for **Students** and **Admins**.

---

## ✨ Features

### 👨‍🎓 Student Side
- Registration & login
- Program enrollment (e.g. Criminology Review)
- Payment submission (GCash / manual upload with reference number & receipt)
- Personal dashboard — enrollment status & payment history
- Student profile management
- Access to learning resources

### 👨‍💼 Admin Side
- Admin login
- Student management — view all registered users & enrollment status
- Enrollment management — approve/monitor pending enrollments, assign batch
- Payment verification — approve or reject submitted payments
- Reports & monitoring — total students, paid vs. pending payments, enrolled student lists
- Announcements & posts
- Gallery management (e.g. top passers, testimonials, batch photos)
- Activity logs
- Database backup handling

---

## 🔄 Core Workflow

**Student flow:**
```
Register → Login → Enroll → Submit Payment → Admin Verifies → Enrolled
```

1. **Registration** — student submits name, email, and password; saved to the `users` table. Status: *Registered (not yet enrolled)*.
2. **Login** — student authenticates with email & password.
3. **Enrollment** — student clicks "Enroll Now"; a record is created in the `enrollments` table (`status = pending`, `program_type = ...`).
4. **Payment submission** — student selects a payment method, enters the amount and reference number, and optionally uploads a receipt. Saved to the `payments` table (`status = pending`).
5. **Dashboard** — student can track enrollment status and payment history at any time.

**Payment verification flow:**
1. Student submits a payment (`status = pending`).
2. Admin reviews the reference number and uploaded receipt.
3. Admin approves (`status = paid`) or rejects (`status = failed`) the payment.
4. If approved, the related enrollment is automatically updated to `enrolled`.

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Language | PHP (vanilla, no framework) |
| Database | MySQL |
| Web server | Apache or Nginx |
| Frontend | HTML / CSS / JS (server-rendered PHP views) |

---

## 📁 Project Structure

```
migrations/                 Database migration scripts
student/                    Student-facing pages/assets

index.php                   Entry point
login.php / register.php    Authentication
logout.php                  Session termination
db.php                      Database connection

student_dashboard.php       Student dashboard
student_profile.php         Student profile management
student_resources.php       Learning resources
enroll.php                  Enrollment handling
payment.php                 Payment submission
upload_payment.php          Receipt upload handler
get_payments.php            Payment data endpoint
get_student_details.php     Student data endpoint

admin_dashboard.php         Admin dashboard
admin_enrollments.php       Enrollment management
admin_payments.php          Payment verification
admin_passers.php           Passers / results management
admin_announcements.php     Announcements
admin_posts.php             Posts management
admin_gallery.php           Gallery management
admin_activity_log.php      Admin activity log
admin_backup.php            Database backup
backup_handler.php          Backup logic
activity_log.php            General activity logging

aside.php                   Shared sidebar layout

config.sample.php           Database configuration template (copy to config.php)
cfts.sql                    Canonical database schema (structure only)
```

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- MySQL (or MariaDB)
- Apache or Nginx with PHP-FPM

### Local Setup

```bash
# 1. Clone the repository
git clone https://github.com/rhondelp/C-FAMILIATUTORIALSERVICES.git
cd C-FAMILIATUTORIALSERVICES
```

**2. Create the database and import the schema:**

```sql
CREATE DATABASE cfam;
```

```bash
mysql -u root -p cfam < cfts.sql
```

**3. Configure the database connection**

Update the credentials in `db.php` to match your local MySQL setup.

**4. Serve the app**

Using PHP's built-in server for quick local testing:

```bash
php -S localhost:8000
```

Or point your Apache/Nginx virtual host document root at the project directory.

---

## ☁️ Deployment

The app is designed to run behind Apache or Nginx with PHP-FPM. General outline:

1. Provision a Linux server and create a dedicated system user for the app.
2. Set up the document root (e.g. `/home/<user>/public_html/<app>` or `/var/www/<app>`) with correct ownership and permissions.
3. Configure a virtual host:
   - **Apache** — enable `mod_rewrite`, set `AllowOverride All`, and point `DocumentRoot` at the project folder.
   - **Nginx** — proxy `.php` requests to `php-fpm` and use `try_files` for clean URL fallback to `index.php`.
4. Create the MySQL database and a dedicated database user with privileges scoped to that database only.
5. Set up HTTPS (e.g. via Let's Encrypt/Certbot) for the production domain.
6. Push to the server via a Git remote pointed at a bare repo on the host, or deploy via your preferred CI/CD method.

> ⚠️ **Security note:** Never commit real server credentials, database passwords, or SSH details to the repository or its README. Store secrets in environment variables or a `.env`/config file that's excluded via `.gitignore`.

---

## 🧩 Roadmap / Suggested Enhancements

- **Installment payments** — allow students to pay in multiple installments, tracking total paid vs. remaining balance.
- **Auto-enrollment check** — automatically set status to `enrolled` once total payments meet the required fee.
- **Notifications** — email/alert on payment approval and enrollment confirmation.
- **Passers filtering** — filter passers by city, branch, and batch/year; track passing rate by batch & year.
- **Diagnostic/pre-board/comprehensive exam tracking** per student.
- **Testimonials & top passers gallery** with photos by batch.
- **Insurance add-on** tracking (if applicable to the program).
- **Social-style posts** — announcements formatted similarly to Facebook posts, with per-branch targeting.

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome — feel free to open a pull request or start a discussion.

## 📄 License

No license specified yet. Add a `LICENSE` file to clarify usage terms for this project.