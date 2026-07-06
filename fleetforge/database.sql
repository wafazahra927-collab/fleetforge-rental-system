-- =============================================================
--  FleetForge Pro — Normalized Database Schema
--  WAMP / MySQL 5.7+
--  Normalization: 3NF throughout
-- =============================================================

CREATE DATABASE IF NOT EXISTS fleetforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fleetforge;

-- =============================================================
-- TABLE 0: users  (authentication + role-based access)
--   role: 'admin' → full access (incl. delete)
--         'staff' → view/create/update only, no delete
-- =============================================================
CREATE TABLE users (
    user_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,   -- bcrypt hash (password_hash())
    full_name    VARCHAR(100) NOT NULL,
    role         ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================
-- TABLE 1: vehicle_types  (extracted from vehicles — 2NF → 3NF)
--   Eliminates repeating type strings in vehicles table
-- =============================================================
CREATE TABLE vehicle_types (
    type_id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(30) NOT NULL UNIQUE
);

-- =============================================================
-- TABLE 2: vehicles
--   All attributes depend solely on vehicle_id (3NF)
-- =============================================================
CREATE TABLE vehicles (
    vehicle_id   VARCHAR(10)  NOT NULL PRIMARY KEY,   -- e.g. V001
    name         VARCHAR(80)  NOT NULL,
    plate        VARCHAR(20)  NOT NULL UNIQUE,
    type_id      TINYINT UNSIGNED NOT NULL,
    year         YEAR         NOT NULL,
    rate_per_day DECIMAL(8,2) NOT NULL,
    mileage      INT UNSIGNED NOT NULL DEFAULT 0,
    icon         VARCHAR(10)  NOT NULL DEFAULT '🚗',
    status       ENUM('available','rented','maintenance') NOT NULL DEFAULT 'available',
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES vehicle_types(type_id)
);

-- =============================================================
-- TABLE 3: customers
--   All attributes depend solely on customer_id (3NF)
-- =============================================================
CREATE TABLE customers (
    customer_id  VARCHAR(10)  NOT NULL PRIMARY KEY,   -- e.g. C001
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(120) NOT NULL UNIQUE,
    phone        VARCHAR(25)  NOT NULL,
    license_no   VARCHAR(30)  NOT NULL UNIQUE,
    color_index  TINYINT UNSIGNED NOT NULL DEFAULT 0, -- UI avatar colour
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================
-- TABLE 4: insurance_plans  (lookup — normalises plan details
--          that were hardcoded in JS)
-- =============================================================
CREATE TABLE insurance_plans (
    plan_id      VARCHAR(10)  NOT NULL PRIMARY KEY,   -- basic / standard / premium
    plan_name    VARCHAR(30)  NOT NULL,
    daily_rate   DECIMAL(6,2) NOT NULL,
    description  VARCHAR(80)  NOT NULL DEFAULT ''
);

-- =============================================================
-- TABLE 5: rentals
--   FK → vehicles, customers, insurance_plans
--   amount is a derived/stored value (days × rate + insurance)
-- =============================================================
CREATE TABLE rentals (
    rental_id    VARCHAR(10)  NOT NULL PRIMARY KEY,   -- e.g. R001
    vehicle_id   VARCHAR(10)  NOT NULL,
    customer_id  VARCHAR(10)  NOT NULL,
    start_date   DATE         NOT NULL,
    end_date     DATE         NOT NULL,
    days         SMALLINT UNSIGNED NOT NULL,
    amount       DECIMAL(10,2) NOT NULL,
    plan_id      VARCHAR(10)  NOT NULL DEFAULT 'none', -- 'none' = no insurance
    status       ENUM('active','returned','overdue') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id)  REFERENCES vehicles(vehicle_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    -- plan_id has FK-like meaning; 'none' is special so no hard FK
);

-- =============================================================
-- TABLE 6: maintenance_types  (lookup — normalises service names)
-- =============================================================
CREATE TABLE maintenance_types (
    mtype_id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mtype_name VARCHAR(50) NOT NULL UNIQUE
);

-- =============================================================
-- TABLE 7: maintenance_records
--   FK → vehicles, maintenance_types
-- =============================================================
CREATE TABLE maintenance_records (
    maint_id     VARCHAR(10)  NOT NULL PRIMARY KEY,   -- e.g. M001
    vehicle_id   VARCHAR(10)  NOT NULL,
    mtype_id     TINYINT UNSIGNED NOT NULL,
    description  TEXT,
    scheduled    DATE         NOT NULL,
    cost_est     DECIMAL(8,2) NOT NULL DEFAULT 0,
    progress     TINYINT UNSIGNED NOT NULL DEFAULT 0 CHECK (progress BETWEEN 0 AND 100),
    status       ENUM('scheduled','inprogress','done','overdue') NOT NULL DEFAULT 'scheduled',
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id),
    FOREIGN KEY (mtype_id)   REFERENCES maintenance_types(mtype_id)
);

-- =============================================================
-- TABLE 8: insurance_policies
--   FK → vehicles, customers, insurance_plans
--   Separated from rentals — a policy can exist independently
-- =============================================================
CREATE TABLE insurance_policies (
    policy_id   VARCHAR(10)  NOT NULL PRIMARY KEY,    -- e.g. I001
    vehicle_id  VARCHAR(10)  NOT NULL,
    customer_id VARCHAR(10)  NOT NULL,
    plan_id     VARCHAR(10)  NOT NULL,
    start_date  DATE         NOT NULL,
    expiry_date DATE         NOT NULL,
    status      ENUM('active','expired') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id)  REFERENCES vehicles(vehicle_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (plan_id)     REFERENCES insurance_plans(plan_id)
);

-- =============================================================
-- INDEXES for common queries
-- =============================================================
CREATE INDEX idx_vehicles_status      ON vehicles(status);
CREATE INDEX idx_rentals_vehicle      ON rentals(vehicle_id);
CREATE INDEX idx_rentals_customer     ON rentals(customer_id);
CREATE INDEX idx_rentals_status       ON rentals(status);
CREATE INDEX idx_maint_vehicle        ON maintenance_records(vehicle_id);
CREATE INDEX idx_maint_status         ON maintenance_records(status);
CREATE INDEX idx_policies_vehicle     ON insurance_policies(vehicle_id);
CREATE INDEX idx_policies_customer    ON insurance_policies(customer_id);
CREATE INDEX idx_policies_status      ON insurance_policies(status);

-- =============================================================
-- SEED DATA
-- =============================================================

-- users
-- Default logins (CHANGE THESE before any real deployment):
--   admin / admin123   (role: admin — full access incl. delete)
--   staff / staff123   (role: staff — no delete access)
INSERT INTO users (username, password, full_name, role) VALUES
  ('admin', '$2b$10$uT5T4o7NpVH0wfHNSY.qLuOrQPPu3y/dv8pVqXsBdYeKoGsM64.jC', 'Administrator', 'admin'),
  ('staff', '$2b$10$MKOyGqoR///nuED7n3FvH.VlBqY6gUfe8caLxRhc1CxU3wSQgH05u', 'Staff Member',   'staff');

-- vehicle_types
INSERT INTO vehicle_types (type_name) VALUES
  ('Sedan'), ('SUV'), ('Pickup'), ('Van'), ('Electric');

-- insurance_plans
INSERT INTO insurance_plans VALUES
  ('none',     'No Insurance', 0.00, 'No coverage'),
  ('basic',    'Basic',        8.00, 'Essential coverage'),
  ('standard', 'Standard',    18.00, 'Most popular plan'),
  ('premium',  'Premium',     32.00, 'Full protection');

-- maintenance_types
INSERT INTO maintenance_types (mtype_name) VALUES
  ('Oil Change'),('Tyre Rotation'),('Brake Pads'),
  ('Engine Service'),('AC Service'),('Battery Check'),
  ('Body Work'),('Full Inspection'),('Other');

-- vehicles, customers, rentals, maintenance_records, insurance_policies
-- are intentionally left empty — add all data from the front end.
