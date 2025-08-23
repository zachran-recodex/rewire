# Rewire

[![Laravel](https://img.shields.io/badge/Laravel-12.25-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.6-purple)](https://livewire.laravel.com)
[![Flux UI](https://img.shields.io/badge/Flux_UI-2.2-blueviolet)](https://fluxui.dev)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.0-blue)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://github.com/zachran-recodex/rewire/actions/workflows/tests.yml/badge.svg)](https://github.com/zachran-recodex/rewire/actions)

A modern open-source **Laravel + Livewire template** built with **Flux UI** and enhanced with **AI/MCP development capabilities**.  
Rewire provides a production-ready foundation for building interactive web applications with **complete authentication system**, **advanced role-based access control**, **user management**, and **beautiful UI components** out of the box.

---

## ✨ Preview

![Rewire Dashboard Preview](docs/images/website.png)

---

## 🚀 Features

### Core Application
- **Complete Authentication System**: Registration, login, password reset, email verification
- **Advanced User Management**: Full profiles with avatar uploads, bio, location, website, birth date
- **Role-Based Access Control**: Super-admin, admin, and user roles with comprehensive policy system
- **Administrator Dashboard**: Full user management with create, edit, delete, and role assignment
- **Modern UI**: Responsive Flux UI components with dark mode support
- **Real-time Interactivity**: Livewire 3.x + Volt for single-file components
- **Settings Management**: Separate modules for profile, password, appearance, and account deletion
- **User Status Management**: Active/inactive user system with middleware protection

### Technical Stack
- **Laravel 12.25** - Latest framework with streamlined structure
- **Livewire 3.6** - Full-stack reactive components
- **Volt 1.7** - Single-file component architecture
- **Flux UI 2.2** - Modern component library (Free edition)
- **Tailwind CSS 4.0** - Modern utility-first CSS framework
- **Pest 3.8** - Modern testing framework with comprehensive test coverage
- **Spatie Laravel Permission 6.21** - Role and permission management
- **SQLite/MySQL** - Flexible database support

### AI & MCP Integration 🤖
- **Laravel Boost MCP Server 1.0** - Full integration with development workflow
- **Claude Code Integration** - AI-assisted development and debugging
- **Smart Code Generation** - Automated component scaffolding
- **Intelligent Testing & Debugging** - AI-powered test generation and error analysis
- **Context-aware Documentation** - Dynamic documentation search and generation
- **Browser Log Integration** - Frontend debugging through MCP tools

---

## 📦 Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- Git

### Setup Instructions

```bash
# 1. Clone the repository
git clone https://github.com/zachran-recodex/rewire.git
cd rewire

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database setup
touch database/database.sqlite
php artisan migrate --seed

# 5. Build assets
npm run dev
````

---

## 🛠️ Development

### Quick Start

Run everything in one command:

```bash
composer run dev
```

This launches:

* Laravel development server
* Queue worker
* Vite dev server

### Testing

```bash
php artisan test
composer test
```

### Code Quality

```bash
vendor/bin/pint
```

---

## 📁 Project Structure

```
rewire/
├── app/
│   ├── Http/
│   │   ├── Controllers/Auth/
│   │   └── Middleware/EnsureUserIsActive.php
│   ├── Livewire/
│   │   ├── Actions/Logout.php
│   │   ├── Administrator/ManageUsers.php
│   │   └── Forms/UserForm.php
│   ├── Models/User.php
│   ├── Policies/UserPolicy.php
│   └── Providers/
├── database/
│   ├── factories/UserFactory.php
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_permission_tables.php
│   │   ├── create_jobs_table.php
│   │   └── create_cache_table.php
│   └── seeders/
│       ├── RolesSeeder.php
│       └── UserSeeder.php
├── resources/views/
│   ├── components/layouts/
│   ├── livewire/
│   │   ├── auth/ (login, register, etc.)
│   │   ├── settings/ (profile, password, appearance)
│   │   └── administrator/
│   └── flux/ (custom Flux components)
├── routes/
│   ├── web.php (main routes + admin)
│   ├── auth.php
│   └── console.php
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Settings/
│   │   ├── ManageUsersTest.php
│   │   └── Seeders/
│   └── Unit/
└── .mcp.json
```

---

## 🎨 UI Components & Features

### Flux UI Components
- Complete component library: `avatar`, `badge`, `button`, `callout`, `checkbox`, `dropdown`, `field`, `heading`, `input`, `modal`, `navbar`, `profile`, `radio`, `select`, `separator`, `switch`, `text`, `textarea`, `tooltip`
- Custom icons: `layout-grid`, `folder-git-2`, `chevrons-up-down`, `book-open-text`, `panel-left`
- Dark mode support throughout

### Application Features
- **Dashboard**: Clean interface with sidebar navigation
- **Authentication Forms**: Complete auth flow with modern styling
- **Settings Pages**: Modular Volt components for profile, password, appearance
- **User Management**: Full CRUD interface for administrators
- **Responsive Design**: Mobile-first approach with Tailwind CSS
- **Loading States**: Wire loading indicators and transitions
- **Toast Notifications**: Success/error messaging system

---

## 🔐 Authentication & Authorization Features

### Authentication Routes
- **Registration** (`/register`) - User signup with validation
- **Login** (`/login`) - Secure authentication
- **Password Reset** (`/forgot-password`, `/reset-password/{token}`) - Complete reset flow
- **Email Verification** (`/verify-email`, `/verify-email/{id}/{hash}`) - Email confirmation
- **Password Confirmation** (`/confirm-password`) - Sensitive action protection
- **Logout** - Secure session termination

### User Management
- **Profile Management** (`/dashboard/settings/profile`) - Name, username, email, bio, location, website, birth date, avatar upload
- **Password Management** (`/dashboard/settings/password`) - Secure password updates
- **Appearance Settings** (`/dashboard/settings/appearance`) - UI preferences
- **Account Deletion** - Self-service account removal with confirmation

### Role-Based Access Control
- **Three-tier Role System**: super-admin, admin, user
- **Policy-based Authorization**: Comprehensive UserPolicy with granular permissions
- **Middleware Protection**: Custom `active` middleware for user status
- **Administrator Panel** (`/dashboard/administrator/manage-users`) - Full user management for admins
- **Role Assignment**: Dynamic role management through admin interface

---

## 🤖 AI & MCP Features

### Laravel Boost MCP Server
- **Server Command**: `php artisan boost:mcp`
- **Database Integration**: Schema inspection, query execution, connection management
- **Documentation Search**: Version-specific package documentation with semantic search
- **Debug Tools**: Error logs, browser logs, tinker execution
- **Application Info**: Package versions, model discovery, route inspection
- **Configuration Access**: Real-time config value retrieval

### Development Workflow
- **AI-powered Components**: Automated Livewire/Volt component generation
- **Intelligent Testing**: AI-assisted test creation and debugging
- **Code Quality**: Integration with Pint formatter and Pest testing
- **Documentation Generation**: Context-aware documentation updates
- **Browser Debugging**: Frontend error tracking and analysis

---

## 📚 Development Guidelines

### Code Standards
- **PSR-12** compliance with Laravel Pint formatting
- **Type Declarations**: Explicit return types and parameter typing
- **Constructor Promotion**: PHP 8.2+ property promotion patterns
- **Modern PHP**: Leveraging PHP 8.2+ features throughout

### Laravel Best Practices
- **Form Requests**: Dedicated validation classes (UserForm)
- **Policies**: Comprehensive authorization logic (UserPolicy)
- **Eloquent Relationships**: Proper ORM usage with eager loading
- **Database Seeders**: Environment-aware seeding with RolesSeeder/UserSeeder
- **Middleware**: Custom middleware for business logic (EnsureUserIsActive)

### Component Architecture
- **Volt Components**: Single-file components for settings pages
- **Class-based Livewire**: Complex components like ManageUsers
- **Form Objects**: Dedicated form classes for data handling
- **Layout Components**: Reusable layout patterns
- **Testing**: Comprehensive test coverage with Pest framework

---

## 🚀 Deployment

### Production Build
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Configuration
Ensure `.env` contains:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://rewire.web.id

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=rewire
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Admin Credentials (for seeding)
ADMIN_NAME="Administrator"
ADMIN_USERNAME="admin"
ADMIN_EMAIL="admin@rewire.web.id"
ADMIN_PASSWORD="secure_password_here"

# Mail Configuration
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS="hello@rewire.web.id"
MAIL_FROM_NAME="Rewire"

# Session & Security
SESSION_DOMAIN=rewire.web.id
SESSION_SECURE_COOKIE=true
BCRYPT_ROUNDS=12
```

### Database Setup
```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 🤝 Contributing

1. Fork the repo
2. Create a feature branch
3. Write tests
4. Run Pint formatter
5. Submit PR

We welcome **AI-assisted contributions** — just document where AI was used.

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🔗 Links

* **Repository**: [Rewire GitHub](https://github.com/zachran-recodex/rewire)
* **Laravel Docs**: [laravel.com/docs](https://laravel.com/docs)
* **Livewire Docs**: [livewire.laravel.com](https://livewire.laravel.com)
* **Flux UI Docs**: [fluxui.dev](https://fluxui.dev)
* **Claude Code Docs**: [claude docs](https://docs.anthropic.com/claude/docs/claude-code)

---

**Built with ❤️ using Laravel, Livewire, Flux UI, and AI tools.**
*Rewire is a modern Laravel starter template for developers who want speed, elegance, and AI-enhanced workflows.*
