# pipofo

A Laravel application for managing predefined forms with participatory, asynchronous completion between requesters and employees.

## Overview

pipofo allows authenticated users (Requesters) to fill out and submit predefined forms. Employees can review, comment on, request corrections, accept, or decline submitted forms. Every change is tracked as a revision with field-level diffs.

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+
- SQLite (default) or MySQL/PostgreSQL

## Setup

```bash
# Install PHP dependencies
composer install

# Install JS dependencies and build assets
npm install && npm run build

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed sample data (optional)
php artisan db:seed

# Start development server
php artisan serve
```

## Seed Accounts

After running `php artisan db:seed`:

| Role | Email | Password |
|---|---|---|
| Administrator | admin@pipofo.test | password |
| Manager | manager@pipofo.test | password |
| Employee (Base) | employee@pipofo.test | password |
| Requester | requester@pipofo.test | password |

## Architecture

### Roles

- **Requester** — Can create forms from templates, fill them out, submit them, and respond to correction requests. Only sees their own forms.
- **Employee Base** — Can review and act on forms assigned to them.
- **Employee Manager** — Access to all forms, can assign Base employees to forms, can create/edit form templates.
- **Employee Admin** — Full administrator access.

### Form Lifecycle

```
Draft → Submitted → Accepted
                  → Declined
                  → Corrections → (Requester edits) → Submitted
```

| Status | Requester Can Edit | Employee Can Edit | Description |
|---|---|---|---|
| Draft | ✅ | ❌ | Form created but not submitted |
| Submitted | ❌ | ✅ | Awaiting employee review |
| Accepted | ❌ | ❌ | Form accepted, archived |
| Declined | ❌ | ❌ | Form declined, archived |
| Corrections | ✅ | ❌ | Employee requested changes |

### Data Model

- **FormTemplate** — Defines the structure of a form (name, description, active state)
- **InputFieldTemplate** — Individual field definitions (type, label, validation, options)
- **Form** — A filled instance of a template, owned by a requester
- **FormField** — The value of each field in a form
- **Revision** — Tracks every change as a JSON diff (`{field_id: {old, new}}`)
- **Comment** — Comments on forms (general or field-specific), from requesters or employees
- **FormEmployee** — Pivot table assigning Base employees to specific forms

### Key Directories

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php    # Role-based dashboard
│   │   ├── FormController.php         # Form CRUD + status transitions
│   │   ├── FormTemplateController.php # Template CRUD (Manager/Admin)
│   │   ├── CommentController.php      # Add comments to forms
│   │   ├── RequesterController.php    # Requester-specific views
│   │   └── EmployeeController.php     # Employee-specific views
│   └── Middleware/
│       └── EnsureRole.php             # Role-based route protection
├── Models/
│   ├── User.php                       # With role helpers
│   ├── Form.php                       # Status helpers + scopes
│   ├── FormTemplate.php
│   ├── InputFieldTemplate.php
│   ├── FormField.php
│   ├── Revision.php
│   └── Comment.php
database/
├── migrations/                        # 8 migration files
└── seeders/
    └── DatabaseSeeder.php             # Sample data
resources/views/
├── requester/                         # Requester views
├── employee/                          # Employee views
├── forms/                             # Form CRUD views
├── form-templates/                    # Template editor views
└── components/                        # Reusable Blade components
```

### Routes Overview

| Route | Method | Description |
|---|---|---|
| `/dashboard` | GET | Role-based dashboard |
| `/requester/available-forms` | GET | List active templates |
| `/requester/my-forms` | GET | List requester's forms |
| `/requester/pending-corrections` | GET | Forms needing corrections |
| `/requester/forms/create/{template}` | GET/POST | Create form from template |
| `/forms/{form}` | GET | View form details |
| `/forms/{form}/edit` | GET | Edit form fields |
| `/forms/{form}` | PUT | Save form field changes |
| `/forms/{form}/submit` | POST | Submit form for review |
| `/forms/{form}/revisions` | GET | View revision history |
| `/forms/{form}/comments` | POST | Add comment |
| `/employee/submitted` | GET | List submitted forms |
| `/employee/corrections` | GET | List forms with corrections |
| `/employee/completed` | GET | List completed forms |
| `/employee/forms/{form}/accept` | POST | Accept form |
| `/employee/forms/{form}/decline` | POST | Decline form |
| `/employee/forms/{form}/request-corrections` | POST | Request corrections |
| `/form-templates` | CRUD | Manage templates (Manager/Admin) |
| `/forms/{form}/assign-employee` | POST | Assign employee (Manager/Admin) |

## UI Features

- **Dark mode** — Tailwind CSS class-based dark mode support
- **Responsive** — Mobile-friendly layout
- **Accessible** — Semantic HTML, ARIA labels, keyboard navigation
- **Status badges** — Color-coded form status indicators
- **Visual template editor** — Alpine.js-powered field builder with live preview
- **Filter & search** — Employee views support text search and status filtering

## Testing

```bash
php artisan test
```

## Tech Stack

- Laravel 13 (PHP 8.3)
- Laravel Breeze (Blade + Tailwind)
- Alpine.js
- SQLite (development)
- Tailwind CSS with dark mode
