# FleetForge Pro — Vehicle Rental Management System

A DBMS lab project: PHP/MySQL-backed vehicle rental management app with role-based admin/staff authentication.

## Setup (WAMP / XAMPP)

1. Place the `fleetforge` folder inside your server's web root (e.g. `C:/wamp64/www/`).
2. Import `fleetforge/database.sql` into MySQL (via phpMyAdmin or CLI). This creates the `fleetforge` database, all tables, and two seed users.
3. Check `fleetforge/includes/db.php` matches your MySQL credentials (default WAMP: user `root`, empty password).
4. Visit `http://localhost/fleetforge/` in your browser — you'll be redirected to the login page.

## Login credentials (seeded)

| Username | Password  | Role  | Access                              |
|----------|-----------|-------|--------------------------------------|
| admin    | admin123  | admin | Full access, including delete        |
| staff    | staff123  | staff | View / create / update, no delete    |

**Change these passwords before any real deployment.** Passwords are stored as bcrypt hashes in the `users` table.

## Authentication design

- `includes/auth.php` — session handling, login/logout, and two guard functions:
  - `requireApiAuth()` — blocks any API call (401) if not logged in.
  - `requireAdmin()` — blocks delete-type actions (403) if the logged-in user isn't an admin.
- `login.php` / `logout.php` — login form and session teardown.
- `index.php` requires login (`requireLogin()`) and hides delete buttons in the UI for staff accounts, while the API layer enforces the same rule server-side.

## Project structure
```
fleetforge/
├── index.php              Main dashboard (requires login)
├── login.php              Login page
├── logout.php             Session destroy
├── database.sql           Full schema + seed data (incl. users table)
├── includes/
│   ├── db.php              PDO connection
│   ├── helpers.php         JSON response helpers
│   └── auth.php            Session auth + role guards
└── api/
    ├── vehicles.php
    ├── customers.php
    ├── rentals.php
    ├── insurance.php
    ├── maintenance.php
    └── dashboard.php
```
