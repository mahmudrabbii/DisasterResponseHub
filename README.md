# Disaster Response Hub (DRH)

The **Disaster Response Hub (DRH)** is a disaster management system designed to coordinate disaster response activities including incident reporting, volunteer coordination, fundraising, aid distribution, and emergency requests.

The platform helps administrators, officials, and volunteers collaborate efficiently during disasters such as floods, cyclones, or earthquakes.

---

# Project Purpose

Disasters require quick coordination between responders, volunteers, and affected communities. The **DRH system** centralizes disaster information and response activities to improve decision-making and resource allocation.

The system provides a structured workflow for reporting disasters, managing relief resources, assigning volunteers, and distributing aid to beneficiaries.

---

# User Roles

## Admin
- Manage users and system settings
- Monitor disasters and incidents
- Create alerts and policies
- Manage resources and aid distribution

## Officials
- Report disasters
- Create and update incidents
- Coordinate volunteers
- Manage beneficiaries and aid distribution

## Volunteers
- Participate in disaster response
- Accept assignments
- Contribute to relief operations

---

# System Modules

## 1. Location Management
Stores geographic information about affected areas.

Table:
- locations

Fields:
- city
- district
- country

---

## 2. People and User Management

The system separates personal information and login accounts.

Tables:
- people
- users

People table stores:
- name
- email
- phone

Users table stores:
- login credentials
- user roles (admin, official, volunteer)

---

## 3. Disaster Management

Tracks disasters and their impact.

Table:
- disasters

Fields:
- disaster type
- location
- disaster date
- affected population
- disaster status

Statuses:
- pending
- in_progress
- resolved

---

## 4. Incident Reporting

Each disaster may contain multiple incidents.

Table:
- incidents

Examples:
- water overflow
- road damage
- infrastructure destruction

Severity levels:
- low
- medium
- high

---

## 5. Volunteer Management

Tracks volunteers and their assignments.

Tables:
- volunteers
- volunteer_assignments

Volunteer data:
- skills
- availability

Assignment data:
- disaster involved
- hours worked
- assignment date

---

## 6. Fundraising and Donations

Manages disaster fundraising activities.

Table:
- fundraising

Roles:
- donor
- organizer

Each campaign is linked to a disaster.

---

## 7. Resource Management

Tracks available disaster relief resources.

Table:
- resources

Examples:
- food supplies
- medicine
- water supplies

Fields:
- resource name
- category
- quantity
- expiry date

---

## 8. Aid Distribution

Tracks beneficiaries and aid distribution.

Tables:
- beneficiaries
- aid_types
- beneficiary_aid

Aid types include:
- food package
- medical kit
- water supply

The system records:
- aid received
- quantity
- distribution date

---

## 9. Aid Request System

Allows affected individuals to request assistance.

Table:
- aid_requests

Statuses:
- pending
- approved
- rejected
- completed

---

## 10. SOS Emergency Requests

Allows people to send urgent rescue requests.

Table:
- sos_requests

Statuses:
- pending
- responded
- resolved

---

## 11. Alert System

Allows administrators to broadcast emergency alerts.

Table:
- alerts

Examples:
- flood warnings
- cyclone alerts

---

## 12. Policy Management

Stores disaster response policies and guidelines.

Table:
- policies

Example:
- flood response policy

---

# Database Tables

The system uses the following main tables:

```
locations
people
users
disasters
incidents
volunteers
volunteer_assignments
fundraising
resources
beneficiaries
aid_types
beneficiary_aid
aid_requests
sos_requests
alerts
policies
```

---

# Technologies Used

Backend
- PHP
- Laravel Framework

Frontend
- HTML
- CSS
- Bootstrap

Database
- MySQL

Tools
- VS Code
- Git
- phpMyAdmin

---

# How to Run the Project

## 1 Clone the repository

```
git clone https://github.com/yourusername/drh.git
```

## 2 Navigate to project folder

```
cd drh
```

## 3 Install dependencies

```
composer install
```

## 4 Setup environment file

```
cp .env.example .env
```

Update database credentials inside `.env`.

---

## 5 Import Database

Import the SQL file into MySQL:

```
disaster_response_hub.sql
```

---

## 6 Start the Laravel server

```
php artisan serve
```

Open in browser:

```
http://127.0.0.1:8000
```

---

# Future Improvements

Possible system improvements include:

- Real-time disaster map
- SMS alert system
- Mobile application
- AI-based disaster prediction
- Volunteer GPS tracking
- Weather API integration

---

# Author

Md. Fazle Rabbi Mahmud