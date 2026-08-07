# ExamFlow Frontend

Modern web frontend for **ExamFlow**, a scalable examination management platform designed to support educational institutions, examination centers, universities, training organizations, and large-scale examination authorities.

The frontend provides the web-based interface for managing the examination ecosystem and communicates with the ExamFlow backend through versioned REST APIs.

> **Project Status:** Active Development 🚧

---

## ✨ Overview

ExamFlow is designed as a centralized platform for managing academic and examination operations through a modern, responsive, and scalable web application.

The frontend is responsible for providing the user interface for administrators, academic staff, examination coordinators, instructors, and other authorized users.

The application is built with a component-driven architecture and is designed to grow alongside the backend business modules.

---

## 🎯 Goals

The frontend aims to provide:

* A modern and responsive administration interface
* Clear and consistent user experience
* Role-aware navigation and authorization
* Academic structure management
* Examination management
* Question and assessment workflows
* Student and academic data management
* Results and reporting interfaces
* Dashboard and analytics
* Responsive layouts for different screen sizes
* Reusable UI components
* Clean separation between presentation and business/API concerns

---

## 🧱 Architecture

The frontend is organized around application-level components, styles, and feature-oriented modules.

```text
src/
├── app/
│   ├── components/
│   ├── pages/
│   ├── routes/
│   └── ...
│
├── imports/
│   └── pasted_text/
│
├── styles/
│
└── main.tsx
```

The project structure is intentionally kept extensible so that additional examination and academic modules can be introduced without restructuring the entire application.

---

## 🛠️ Technology Stack

| Technology      | Purpose                           |
| --------------- | --------------------------------- |
| React           | UI framework                      |
| TypeScript      | Type-safe development             |
| Vite            | Development server and build tool |
| React Router    | Client-side routing               |
| MUI             | Material Design components        |
| Radix UI        | Accessible UI primitives          |
| Tailwind CSS    | Utility-first styling             |
| Lucide React    | Icon system                       |
| Motion          | UI animations                     |
| React Hook Form | Form management                   |
| Recharts        | Charts and analytics              |
| date-fns        | Date manipulation                 |
| React DnD       | Drag-and-drop interactions        |
| Sonner          | Notifications                     |
| pnpm            | Workspace/package management      |

The current project dependencies and Vite configuration are defined in `package.json` and `vite.config.ts`.

---

## 📋 Planned & Current Modules

The frontend architecture is prepared to support modules such as:

### Academic Management

* Academic years
* Institutions
* Educational structures
* Programs
* Grades / levels
* Departments
* Subjects

### Examination Management

* Examination creation
* Examination schedules
* Examination sessions
* Question banks
* Exam papers
* Question selection
* Exam configuration

### Student Management

* Student records
* Academic enrollment
* Student examination registration
* Student examination history

### Results

* Mark entry
* Correction workflows
* Result calculation
* Result publishing
* Result reports
* Performance analytics

### Administration

* Dashboard
* Users
* Roles
* Permissions
* Tenant-aware navigation
* System settings
* Audit-related interfaces

> Modules are being introduced incrementally according to the backend development phases.

---

## 🔌 Backend Integration

The frontend communicates with the ExamFlow backend through a versioned REST API.

The backend API follows:

```text
/api/v1
```

The frontend should not directly access the database.

```text
┌──────────────────────┐
│   ExamFlow Frontend  │
│      React / Vite    │
└──────────┬───────────┘
           │
           │ REST API
           │ /api/v1
           ▼
┌──────────────────────┐
│   ExamFlow Backend   │
│   Laravel 12 / PHP   │
└──────────┬───────────┘
           │
     ┌─────┴─────┐
     ▼           ▼
 PostgreSQL     Redis
```

The backend repository provides the API, authentication, authorization, tenant isolation, queue infrastructure, and data layer.

---

## 🔐 Authentication & Authorization

Authentication and authorization are handled by the backend.

The frontend is responsible for:

* Authentication screens
* Session/token handling
* Protected routes
* Role-aware navigation
* Permission-aware UI
* Authentication state
* Handling expired sessions
* API authentication errors

Authorization must ultimately be enforced by the backend. Frontend permission checks are intended for user experience and navigation control, not as a security boundary.

---

## 🌐 Environment Configuration

Create a local environment configuration when API integration is enabled.

Example:

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

Never commit private credentials, API secrets, or environment-specific secrets to the repository.

---

## 🚀 Getting Started

### Requirements

Make sure the following are installed:

* Node.js
* npm or pnpm
* Git

### Clone

```bash
git clone https://github.com/Ayhamkassar/ExamsManagement.git
cd ExamsManagement
```

Switch to the frontend branch:

```bash
git checkout main
```

### Install dependencies

Using npm:

```bash
npm install
```

Or using pnpm:

```bash
pnpm install
```

### Start development server

```bash
npm run dev
```

Or:

```bash
pnpm dev
```

Vite will start the development server.

---

## 🏗️ Production Build

Build the application:

```bash
npm run build
```

Or:

```bash
pnpm build
```

The production build is generated by Vite.

---

## 🧪 Development Guidelines

When adding new functionality:

1. Keep features modular.
2. Reuse existing UI components.
3. Avoid duplicating business logic.
4. Keep API communication separated from presentation components.
5. Use TypeScript types for API and application data.
6. Keep forms validated.
7. Make interfaces responsive.
8. Respect role and permission boundaries.
9. Avoid hardcoded production URLs.
10. Never commit secrets.

---

## 📁 Important Files

| File / Directory | Purpose                       |
| ---------------- | ----------------------------- |
| `src/`           | Application source code       |
| `src/app/`       | Main application structure    |
| `src/styles/`    | Global and application styles |
| `src/main.tsx`   | Application entry point       |
| `package.json`   | Dependencies and scripts      |
| `vite.config.ts` | Vite configuration            |
| `index.html`     | Application HTML entry        |
| `.gitignore`     | Ignored files                 |

---

## 🔗 Related Project

### ExamFlow Backend

The backend is maintained in the same GitHub repository under the `master` branch.

```text
Repository
│
├── main
│   └── Frontend
│
└── master
    └── Backend
```

Backend documentation and architecture are maintained separately in the backend branch.

---

## 🗺️ Development Roadmap

The frontend will evolve alongside the backend implementation.

```text
Foundation
    │
    ▼
Authentication
    │
    ▼
Academic Structure
    │
    ▼
Students & Staff
    │
    ▼
Question Bank
    │
    ▼
Examinations
    │
    ▼
Correction & Grading
    │
    ▼
Results
    │
    ▼
Reports & Analytics
    │
    ▼
Advanced Administration
```

---

## 📌 Project Status

This project is under active development.

Features and interfaces may change as the backend domain model and examination workflows evolve.

---

## 👨‍💻 Author

**Ayham Kassar**

GitHub: [Ayhamkassar](https://github.com/Ayhamkassar)

---

## 📄 License

This project is currently maintained as a private development project.

License information will be added when the project is officially released.
