# ExamFlow Backend

Production-oriented backend for **ExamFlow**, a scalable examination management platform designed to support schools, universities, training centers, examination authorities, and large-scale educational organizations.

The backend provides a secure, API-first foundation for the ExamFlow ecosystem and exposes versioned REST APIs consumed by web applications, mobile applications, and future third-party integrations.

> **Project Status:** Active Development 🚧
> **Current Focus:** Backend Foundation + Academic Domain

---

## 🎓 About ExamFlow

ExamFlow is designed to provide a unified platform for managing academic and examination operations.

The long-term platform is intended to support:

* Schools
* Universities
* Training centers
* Examination centers
* Educational organizations
* National and large-scale examination authorities

The backend is designed from the beginning around:

* Multi-tenancy
* Security
* Auditability
* API versioning
* Role-based access control
* Horizontal scalability
* Background processing
* Reliable data isolation

---

# 🏗️ Architecture

ExamFlow follows an **API-first architecture**.

```text
                     ┌─────────────────────┐
                     │   React Web App     │
                     └──────────┬──────────┘
                                │
                                │ REST API
                                ▼
┌───────────────────────────────────────────────────┐
│                 ExamFlow Backend                  │
│                                                   │
│  Laravel 12                                      │
│  API v1                                           │
│  Authentication                                   │
│  Authorization                                    │
│  Multi-tenancy                                    │
│  Audit Logging                                    │
│  Business Services                                │
│  Queue / Scheduler                                │
└───────────────┬───────────────────┬───────────────┘
                │                   │
                ▼                   ▼
        ┌──────────────┐     ┌──────────────┐
        │ PostgreSQL   │     │    Redis     │
        │     16       │     │      7       │
        └──────────────┘     └──────────────┘
```

All external clients communicate with the backend through versioned APIs under:

```text
/api/v1
```

---

# 🛠️ Technology Stack

| Area            | Technology                   |
| --------------- | ---------------------------- |
| Framework       | Laravel 12                   |
| Language        | PHP 8.3+ recommended         |
| Database        | PostgreSQL 16                |
| Cache           | Redis 7                      |
| Queue           | Laravel Queue + Redis        |
| Authentication  | Laravel Sanctum              |
| API             | REST API                     |
| API Version     | `/api/v1`                    |
| Storage         | S3-compatible object storage |
| Testing         | Pest / PHPUnit               |
| Static Analysis | PHPStan + Larastan           |
| Code Style      | Laravel Pint                 |
| Containers      | Docker / Docker Compose      |

---

# 🚀 Current Foundation

The backend foundation provides the infrastructure required for future examination business modules.

## API Versioning

All public API endpoints are versioned:

```text
/api/v1
```

This allows future API versions to evolve without breaking existing clients.

---

## 📦 Consistent API Responses

API responses follow a consistent structure:

```json
{
    "success": true,
    "message": "Operation completed successfully.",
    "data": {},
    "errors": {}
}
```

This keeps API consumption predictable across web, mobile, and third-party clients.

---

# 🏢 Multi-Tenancy

ExamFlow is designed as a multi-tenant platform.

Each organization operates inside its own tenant context.

Example:

```text
Tenant A
├── Users
├── Academic Data
├── Exams
└── Results

Tenant B
├── Users
├── Academic Data
├── Exams
└── Results
```

Tenant isolation is enforced through the backend rather than relying on frontend filtering.

The foundation includes:

* `TenantContext`
* Tenant resolution
* `X-Tenant-ID`
* `TenantScope`
* `BelongsToTenant`
* Tenant-aware jobs
* Tenant-aware cache keys
* Tenant isolation tests

---

# 🔐 Authentication

Authentication is implemented using **Laravel Sanctum**.

The API is designed to support authenticated clients including:

* Web applications
* Mobile applications
* Internal services
* Future third-party integrations

Authentication logic is kept within the backend so that security boundaries remain server-side.

---

# 🛡️ Authorization & RBAC

ExamFlow provides a role-based authorization foundation.

The system includes:

* Roles
* Permissions
* Policies
* Gates
* Authorization services

Authorization is enforced on the backend.

Frontend permission checks are treated only as a user-experience feature and never as the primary security boundary.

---

# 🧾 Audit Logging

ExamFlow includes an audit logging foundation designed for traceability and accountability.

The audit system records important system actions and is designed around an append-only model.

Audit information can be used to answer:

```text
Who performed the action?
What happened?
When did it happen?
Which tenant was affected?
Which resource was changed?
```

This is particularly important for examination systems where administrative actions and result-related operations may require traceability.

---

# ❤️ Health Checks

The backend exposes health endpoints:

```text
GET /api/v1/health/live
GET /api/v1/health/ready
```

### Liveness

Checks whether the application is alive.

### Readiness

Checks whether required infrastructure such as:

* Database
* Redis
* Cache

is available.

These endpoints are suitable for container orchestration and deployment monitoring.

---

# ⚡ Queue & Background Processing

Redis is used for queue and cache infrastructure.

The backend supports background processing through Laravel Queue.

The architecture includes:

* Queue workers
* Scheduler
* Tenant-aware jobs
* Redis-backed queues
* Cache infrastructure

This allows expensive operations to be moved outside the request lifecycle.

Examples of future background workloads include:

* Large report generation
* Result processing
* Notifications
* File processing
* Import/export operations
* Scheduled academic operations

---

# 💾 Storage

The backend is prepared for S3-compatible object storage.

Storage paths are designed to support tenant-aware organization.

Example:

```text
tenants/
    {tenant-id}/
        documents/
        exams/
        results/
        attachments/
```

Sensitive files should not be exposed through public storage URLs unless explicitly intended.

---

# 🗄️ Database

The primary database is:

```text
PostgreSQL 16
```

The database layer is designed around:

* Tenant isolation
* Referential integrity
* Migrations
* Seeders
* Indexing
* Future academic relationships

---

# 🎓 Academic Domain

The academic domain is being developed as a flexible foundation rather than a model restricted to a single educational structure.

The architecture is intended to support:

```text
Institution
    │
    └── Academic Year
            │
            ├── Programs / Levels
            │
            ├── Departments
            │
            └── Subjects
```

This allows the platform to serve different educational organizations without requiring a fundamental database redesign.

---

# 📝 Examination Domain

The examination domain will progressively cover:

* Examination definitions
* Examination sessions
* Question banks
* Questions
* Exam papers
* Student registrations
* Attempts
* Answers
* Corrections
* Grading
* Results
* Result publishing
* Appeals
* Reports

These modules are being introduced incrementally according to the project roadmap.

---

# 📊 Future Platform Modules

Planned business capabilities include:

### Academic Management

* Institutions
* Academic years
* Programs
* Departments
* Subjects
* Educational levels

### People

* Students
* Teachers
* Examiners
* Staff
* Administrators

### Examination

* Exams
* Exam schedules
* Question banks
* Exam papers
* Exam sessions
* Exam rooms
* Student registration

### Assessment

* Answers
* Corrections
* Marking
* Grading rules
* Result calculation

### Results

* Student results
* Result publishing
* Result certificates
* Performance analytics
* Result reports

### Administration

* Users
* Roles
* Permissions
* Tenants
* Audit logs
* System configuration

### Platform

* Notifications
* Billing
* File storage
* Background jobs
* Reporting
* Integrations

---

# 🧪 Testing

The backend uses **Pest** for automated testing.

Testing covers areas including:

* Feature tests
* Unit tests
* Architecture tests
* Authentication
* Authorization
* Tenant isolation
* API behavior
* Infrastructure behavior

Run the test suite:

```bash
composer test
```

Or:

```bash
php artisan test
```

---

# 🔍 Static Analysis

PHPStan / Larastan is used for static analysis.

Run:

```bash
composer analyse
```

---

# 🧹 Code Style

Laravel Pint is used to maintain consistent PHP formatting.

Check code style:

```bash
composer lint
```

Automatically fix formatting:

```bash
composer lint:fix
```

---

# ✅ Full Quality Check

Run the complete project quality pipeline:

```bash
composer check
```

This combines:

```text
Lint
  ↓
Static Analysis
  ↓
Tests
```

---

# 🐳 Docker

Docker Compose provides the development infrastructure.

The environment includes services for:

```text
Application
PostgreSQL
Redis
Queue Worker
Scheduler
```

Start the environment:

```bash
docker compose up -d --build
```

Run migrations:

```bash
docker compose exec app php artisan migrate --seed
```

Run tests:

```bash
docker compose exec app php artisan test
```

---

# 💻 Local Development

## Requirements

For local development without Docker:

* PHP 8.2+
* Composer
* PostgreSQL 16
* Redis 7
* Node.js / npm for frontend asset tooling

---

## Installation

Clone the repository:

```bash
git clone https://github.com/Ayhamkassar/ExamsManagement.git
cd ExamsManagement
```

Switch to the backend branch:

```bash
git checkout master
```

Install PHP dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure:

```text
Database
Redis
Application URL
Mail
Storage
```

Then run:

```bash
php artisan migrate --seed
```

Start the application:

```bash
php artisan serve --port=8000
```

The API will be available at:

```text
http://localhost:8000
```

---

# ❤️ Health Verification

After starting the application:

```bash
curl http://localhost:8000/api/v1/health/live
```

Then:

```bash
curl http://localhost:8000/api/v1/health/ready
```

A successful readiness response confirms that the application can communicate with the required infrastructure.

---

# 📚 Documentation

Detailed technical documentation is available under:

```text
docs/
├── api/
├── architecture/
├── deployment/
├── development/
└── security/
```

Important documentation includes:

```text
docs/architecture/overview.md
docs/architecture/multi-tenancy.md
docs/architecture/security.md
docs/architecture/storage.md
docs/architecture/queues.md
docs/architecture/scaling.md

docs/api/README.md
docs/api/openapi.yaml

docs/development/setup.md
docs/development/testing.md

docs/deployment/README.md
```

---

# 🔒 Security

Security is a core architectural requirement.

The backend includes foundations for:

* CORS
* Rate limiting
* Security headers
* Request validation
* Authentication
* Authorization
* Tenant isolation
* Audit logging
* Secret-safe exception responses
* Request correlation IDs

### Never commit:

```text
.env
APP_KEY
Database passwords
Redis credentials
Storage credentials
API secrets
Private keys
```

Use environment variables or a dedicated secret manager in production.

---

# 🆔 Request Correlation

Each API request can be associated with an `X-Request-ID`.

This identifier is useful for:

* Debugging
* Logging
* Error tracking
* Audit trails
* Distributed request tracing

Example:

```http
X-Request-ID: 8c1c2f7a-....
```

---

# 📈 Scalability

The backend is designed with future horizontal scaling in mind.

The architecture separates:

```text
HTTP Requests
     │
     ├── Application Servers
     │
     ├── PostgreSQL
     │
     ├── Redis
     │
     └── Queue Workers
```

This allows application servers and workers to scale independently as workload increases.

---

# 🗺️ Development Roadmap

ExamFlow is developed incrementally.

```text
Phase 0
Backend Foundation
      │
      ▼
Phase 1
Core Academic & Organization Domain
      │
      ▼
Phase 2
Examination Domain
      │
      ▼
Phase 3
Academic Structure & Educational Model
      │
      ▼
Phase 4
Students, Teachers & Enrollment
      │
      ▼
Phase 5
Questions & Examination Sessions
      │
      ▼
Phase 6
Correction, Grading & Results
      │
      ▼
Phase 7
Reports, Notifications & Advanced Features
```

> The roadmap is evolutionary. Each phase should preserve the security, tenant isolation, API stability, and architectural boundaries established by the foundation.

---

# 📌 Current Status

**Active Development 🚧**

The backend foundation is established and the platform is progressively moving into its academic and examination business domains.

The API, multi-tenancy, security, authorization, auditability, infrastructure, testing, and deployment foundations are designed to support the future business modules.

---

# 🔗 Frontend

The ExamFlow web frontend is maintained in the same repository under the `main` branch.

```text
ExamsManagement
│
├── main
│   └── React Frontend
│
└── master
    └── Laravel Backend
```

The frontend communicates with this backend through the versioned REST API.

---

# 👨‍💻 Author

**Ayham Kassar**

GitHub: [Ayhamkassar](https://github.com/Ayhamkassar)

---

# 📄 License

This project is currently maintained as a private development project.

License information will be added when the project is officially released.
