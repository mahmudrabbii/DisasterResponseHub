
CREATE DATABASE disaster_response_hub;
USE disaster_response_hub;

CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100),
    district VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Bangladesh'
);

CREATE TABLE people (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT,
    password VARCHAR(255),
    role ENUM('admin','official','volunteer') DEFAULT 'volunteer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id)
);

CREATE TABLE disasters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100),
    location_id INT,
    disaster_date DATE,
    affected_population INT,
    status ENUM('pending','in_progress','resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disaster_id INT,
    title VARCHAR(150),
    description TEXT,
    severity ENUM('low','medium','high'),
    status ENUM('reported','in_progress','resolved') DEFAULT 'reported',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id)
);

CREATE TABLE volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT UNIQUE,
    skills VARCHAR(150),
    availability VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id)
);

CREATE TABLE volunteer_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disaster_id INT,
    person_id INT,
    hours_worked INT DEFAULT 0,
    assigned_date DATE,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE CASCADE,
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
);

CREATE TABLE fundraising (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT,
    disaster_id INT,
    title VARCHAR(150),
    amount DECIMAL(10,2),
    role ENUM('donor','organizer') DEFAULT 'donor',
    status ENUM('active','completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id),
    FOREIGN KEY (disaster_id) REFERENCES disasters(id)
);

CREATE TABLE aid_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    category VARCHAR(100),
    quantity INT DEFAULT 0,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE beneficiaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT,
    family_size INT,
    location_id INT,
    disaster_id INT,
    aid_received VARCHAR(255),
    support_status ENUM('pending','approved','rejected') DEFAULT 'pending',
    support_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id),
    FOREIGN KEY (location_id) REFERENCES locations(id),
    FOREIGN KEY (disaster_id) REFERENCES disasters(id)
);

CREATE TABLE beneficiary_aid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    beneficiary_id INT,
    aid_type_id INT,
    quantity INT,
    distributed_date DATE,
    FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries(id),
    FOREIGN KEY (aid_type_id) REFERENCES aid_types(id)
);

CREATE TABLE aid_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT,
    location_id INT,
    aid_type_id VARCHAR(255),
    description TEXT,
    status ENUM('pending','approved','rejected','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id),
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

CREATE TABLE sos_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT,
    location_id INT,
    message TEXT,
    status ENUM('pending','responded','resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES people(id),
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

CREATE TABLE resource_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disaster_id INT NOT NULL,
    requested_by_person_id INT NOT NULL,
    resource_name VARCHAR(100) NOT NULL,
    quantity_requested INT UNSIGNED NOT NULL,
    notes TEXT,
    status ENUM('pending','approved','fulfilled','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by_person_id) REFERENCES people(id) ON DELETE CASCADE
);

CREATE TABLE resource_usage_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disaster_id INT NOT NULL,
    resource_id INT NULL,
    resource_name VARCHAR(100) NOT NULL,
    quantity_used INT UNSIGNED NOT NULL,
    notes TEXT,
    recorded_by_person_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE SET NULL,
    FOREIGN KEY (recorded_by_person_id) REFERENCES people(id) ON DELETE CASCADE
);

CREATE TABLE policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE volunteer_disaster_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    disaster_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    submission_type ENUM('incident_report','damage_assessment','resource_need','population_data','other') NOT NULL DEFAULT 'incident_report',
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_notes LONGTEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE CASCADE,
    
    INDEX idx_person_id (person_id),
    INDEX idx_disaster_id (disaster_id),
    INDEX idx_status (status)
);

CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE `volunteer_disaster_submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `person_id` INT NOT NULL,
    `disaster_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `submission_type` ENUM('incident_report','damage_assessment','resource_need','population_data','other') NOT NULL DEFAULT 'incident_report',
    `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `admin_notes` LONGTEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`disaster_id`) REFERENCES `disasters` (`id`) ON DELETE CASCADE,
    
    INDEX `idx_person_id` (`person_id`),
    INDEX `idx_disaster_id` (`disaster_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



INSERT INTO locations (city, district, country) VALUES
('Dhaka', 'Dhaka', 'Bangladesh'),
('Chattogram', 'Chattogram', 'Bangladesh'),
('Khulna', 'Khulna', 'Bangladesh');

INSERT INTO people (name, email, phone) VALUES
('Rahim Uddin', 'rahim@gmail.com', '01710000001'),
('Karim Ahmed', 'karim@gmail.com', '01710000002'),
('Nusrat Jahan', 'nusrat@gmail.com', '01710000003'),
('Abdul Karim', 'abdul@gmail.com', '01710000004');

INSERT INTO users (person_id, password, role) VALUES
(1, 'pass123', 'admin'),
(2, 'pass123', 'volunteer'),
(3, 'pass123', 'official');

INSERT INTO disasters (type, location_id, disaster_date, affected_population, status) VALUES
('Flood', 1, '2026-04-01', 50000, 'in_progress'),
('Cyclone', 2, '2026-03-20', 120000, 'resolved');

INSERT INTO incidents (disaster_id, title, description, severity, status) VALUES
(1, 'Water Overflow', 'Severe flooding in low areas', 'high', 'in_progress'),
(1, 'Road Damage', 'Roads broken due to flood water', 'medium', 'reported'),
(2, 'Wind Damage', 'Houses destroyed due to cyclone', 'high', 'resolved');

INSERT INTO volunteers (person_id, skills, availability) VALUES
(2, 'First Aid, Rescue', 'available'),
(4, 'Logistics, Transport', 'busy');

INSERT INTO volunteer_assignments (disaster_id, person_id, hours_worked, assigned_date) VALUES
(1, 2, 10, '2026-04-02'),
(2, 4, 8, '2026-03-21');

INSERT INTO fundraising (person_id, disaster_id, title, amount, role, status) VALUES
(1, 1, 'Flood Relief Fund', 5000.00, 'organizer', 'active'),
(3, 1, 'Donation Support', 1500.00, 'donor', 'active');

INSERT INTO aid_types (name) VALUES
('Food Package'),
('Medical Kit'),
('Rescue Kit'),
('Water Supply');

INSERT INTO resources (name, category, quantity, expiry_date) VALUES
('Rice Bags', 'Food', 200, '2026-12-01'),
('Medicine Box', 'Medical', 50, '2026-08-01');

INSERT INTO beneficiaries (person_id, family_size, location_id, disaster_id) VALUES
(1, 5, 1, 1);

INSERT INTO beneficiary_aid (beneficiary_id, aid_type_id, quantity, distributed_date) VALUES
(1, 1, 2, '2026-04-03');

INSERT INTO aid_requests (person_id, location_id, aid_type_id, description, status) VALUES
(3, 1, 1, 'Need urgent food support', 'pending');

INSERT INTO sos_requests (person_id, location_id, message, status) VALUES
(2, 2, 'Need rescue immediately', 'pending');

INSERT INTO resource_requests (disaster_id, requested_by_person_id, resource_name, quantity_requested, notes, status) VALUES
(1, 3, 'Food Packages', 250, 'Urgent request for flood relief', 'pending');

INSERT INTO resource_usage_logs (disaster_id, resource_id, resource_name, quantity_used, notes, recorded_by_person_id) VALUES
(1, 1, 'Rice Bags', 25, 'Delivered to flood shelter', 3);

INSERT INTO policies (title, description) VALUES
('Flood Response Policy', 'Guidelines for handling flood emergencies');

INSERT INTO alerts (title, message) VALUES
('Flood Warning', 'Heavy rainfall expected in Dhaka');