# Rewire

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-purple)](https://livewire.laravel.com)
[![Flux UI](https://img.shields.io/badge/Flux_UI-2.x-blueviolet)](https://fluxui.dev)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://github.com/zachran-recodex/rewire/actions/workflows/tests.yml/badge.svg)](https://github.com/zachran-recodex/rewire/actions)

A modern open-source **Laravel + Livewire template** built with **Flux UI** and enhanced with **AI/MCP development capabilities**.  
Rewire provides a clean foundation for building interactive web applications with **authentication**, **role-based access**, and **beautiful UI components** out of the box.

---

## ✨ Preview

![Rewire Dashboard Preview](docs/images/dashboard.png)

> *Screenshot of Rewire dashboard (replace with actual screenshot or GIF demo)*

---

## 🚀 Features

### Core Application
- **Authentication System**: Registration, login, password reset, email verification
- **User Management**: Profiles with avatar, bio, and settings
- **Role-Based Access Control**: Powered by Spatie Laravel Permission
- **Modern UI**: Responsive Flux UI components
- **Real-time Interactivity**: Livewire + Volt
- **Settings Management**: Appearance preferences, password & profile customization

### Technical Stack
- **Laravel 12.x**
- **Livewire 3.x**
- **Volt 1.7.x**
- **Flux UI 2.x**
- **Tailwind CSS 4.x**
- **Pest 3.x**
- **SQLite Database** (for development)

### AI & MCP Integration 🤖
- **Laravel Boost MCP Server**
- **Claude Code Integration**
- **Smart Code Generation**
- **Intelligent Testing & Debugging**
- **Context-aware Documentation**

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
├── app/Http/Controllers/
├── app/Livewire/
├── app/Models/
├── database/migrations/
├── database/seeders/
├── resources/views/
│   ├── components/
│   ├── livewire/
│   └── flux/
├── routes/
│   ├── web.php
│   ├── auth.php
│   └── console.php
├── tests/
└── .mcp.json
```

---

## 🎨 UI Components

* Flux UI components (`flux:avatar`, `flux:button`, `flux:modal`, etc.)
* Custom authentication forms
* Profile & settings management
* Dashboard with sidebar navigation

---

## 🔐 Authentication Features

* Registration (`/register`)
* Login (`/login`)
* Password Reset (`/forgot-password`)
* Email Verification (`/verify-email`)
* Logout
* Profile management, avatar upload, account deletion

---

## 🤖 AI & MCP Features

* **MCP server**: `php artisan boost:mcp`
* **AI-powered components**: Livewire scaffolding via AI
* **Intelligent testing & debugging**
* **Documentation generation**

---

## 📚 Development Guidelines

* **Code Standards**: PSR-12 + Pint formatting
* **Laravel Best Practices**: Form Requests, Resource classes, Jobs
* **Livewire Conventions**: Volt for simple components, class-based for complex

---

## 🚀 Deployment

```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan optimize
```

Ensure `.env` contains correct:

* `APP_ENV=production`
* `APP_DEBUG=false`
* Database config
* Mail config

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
