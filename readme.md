
👨‍🎓 1. STUDENT WORKFLOW
📝 Step 1: Registration

Student fills up:
Name

Email

Password

Data saved in users table
➡️ Status: Registered (not yet enrolled)
🔐 Step 2: Login

Student logs in using email & password

System authenticates user
📋 Step 3: Enrollment

Student clicks “Enroll Now”

System creates record in enrollments table
Example:

status = pending
program_type = Criminology Review
➡️ Status: Pending Enrollment
💳 Step 4: Payment Submission

Student selects payment method:
GCash / Manual Upload

Inputs:
Amount

Reference Number

Upload receipt (optional)
➡️ Saved in payments table

➡️ status = pending
📊 Step 5: View Dashboard
Student can see:


Enrollment status

Payment history

Payment status (pending/paid)
💸 2. PAYMENT WORKFLOW
🧾 Step 1: Student Submits Payment

Creates a payment record:status = pending
🔍 Step 2: Admin Reviews Payment
Admin checks:


Reference number

Uploaded receipt
✅ Step 3: Approval / Rejection
Admin updates:

status = paid   → if valid
status = failed → if invalid

🔄 Step 4: System Updates Enrollment
If payment is paid:


Enrollment status becomes:
enrolled

👨‍💼 3. ADMIN WORKFLOW
🔐 Step 1: Admin Login
👥 Step 2: Manage Students

View all registered users

View enrollment status
📋 Step 3: Manage Enrollments

Approve or monitor pending enrollments

Assign batch (optional)
💳 Step 4: Verify Payments

View all payments

Approve / Reject payments
📊 Step 5: Reports & Monitoring
Admin can see:


Total students

Paid vs pending payments

List of enrolled students
🔁 SYSTEM FLOW SUMMARY (Simple View)
Register → Login → Enroll → Pay → Admin Verify → Enrolled

🧩 OPTIONAL ADVANCED FLOW (Highly Recommended)
📌 Installment Payments

Student pays multiple times

System tracks total paid vs balance
📌 Enrollment Auto-Check

If total payments ≥ required fee:

→ Auto set enrolled
📌 Notifications

Email or alert:
“Payment Approved”

“Enrollment Confirmed”
🖥️ SAMPLE PAGE STRUCTURE
👨‍🎓 Student Pages

Register

Login

Dashboard

Enrollment Page

Payment Page

Payment History
👨‍💼 Admin Pages

Dashboard

Students List

Enrollments List