# Rewire

A modern Laravel application built with Livewire, Flux UI, and AI-enhanced development capabilities. This project demonstrates best practices for building interactive web applications with cutting-edge technologies and AI/MCP integration.

## 🚀 Features

### Core Application
- **Authentication System**: Complete user authentication with registration, login, password reset, and email verification
- **User Management**: Comprehensive user profiles with avatars, bio, contact information, and account settings
- **Role-Based Access Control**: Powered by Spatie Laravel Permission package
- **Modern UI**: Beautiful, responsive interface built with Flux UI components
- **Real-time Interactivity**: Livewire and Volt for seamless user experiences
- **Settings Management**: User appearance preferences, password management, and profile customization

### Technical Stack
- **Laravel 12.25.0**: Latest Laravel framework with modern PHP 8.2+ features
- **Livewire 3.6.4**: Full-stack framework for building dynamic interfaces
- **Livewire Volt 1.7.2**: Single-file Livewire components for rapid development
- **Flux UI 2.2.5**: Professional UI component library
- **Tailwind CSS 4.0.7**: Utility-first CSS framework
- **Pest 3.8.4**: Modern PHP testing framework
- **SQLite Database**: Lightweight, file-based database for development

### AI & MCP Integration 🤖
This project is enhanced with **Model Context Protocol (MCP)** and AI development capabilities:

- **Laravel Boost MCP Server**: Advanced Laravel-specific AI assistance
- **Claude Code Integration**: Optimized for AI-assisted development
- **Smart Code Generation**: AI-powered component and feature creation
- **Intelligent Testing**: Automated test generation and debugging
- **Documentation Assistance**: Context-aware documentation generation

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & npm
- Git

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/zachran-recodex/rewire.git
   cd rewire
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

6. **Build assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

## 🛠️ Development

### Quick Start
Run the development server with automatic asset compilation and queue processing:
```bash
composer run dev
```

This command runs:
- PHP development server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Vite development server (`npm run dev`)

### Testing
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/AuthenticationTest.php

# Run with coverage
composer test
```

### Code Quality
```bash
# Fix code formatting
vendor/bin/pint

# Run with dry-run to see what would be changed
vendor/bin/pint --test
```

## 🤖 AI & MCP Features

### MCP Server Configuration
The project includes Laravel Boost MCP server for enhanced AI development:

```json
{
    "mcpServers": {
        "laravel-boost": {
            "command": "php",
            "args": ["./artisan", "boost:mcp"]
        }
    }
}
```

### AI-Enhanced Development
- **Smart Component Generation**: AI can create Livewire components following project conventions
- **Test Automation**: Intelligent test generation and debugging
- **Database Query Assistance**: AI-powered database operations and optimization
- **Real-time Error Analysis**: Automatic error detection and resolution suggestions
- **Documentation Generation**: Context-aware documentation creation

### Claude Code Integration
This project is optimized for Claude Code with:
- Pre-configured permissions for common operations
- Laravel-specific tool access
- Automated code formatting and testing workflows
- Smart Git operations

## 📁 Project Structure

```
rewire/
├── app/
│   ├── Http/Controllers/     # HTTP controllers
│   ├── Livewire/            # Livewire components
│   ├── Models/              # Eloquent models
│   └── Providers/           # Service providers
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   └── views/              # Blade templates
│       ├── components/     # Reusable components
│       ├── livewire/       # Livewire views
│       └── flux/           # Custom Flux components
├── routes/
│   ├── web.php             # Web routes
│   ├── auth.php            # Authentication routes
│   └── console.php         # Console routes
├── tests/
│   ├── Feature/            # Feature tests
│   └── Unit/               # Unit tests
├── .mcp.json               # MCP server configuration
├── .claude/                # Claude Code settings
└── CLAUDE.md               # AI development guidelines
```

## 🎨 UI Components

### Available Flux UI Components
- `flux:avatar` - User avatars with fallback initials
- `flux:button` - Various button styles and states
- `flux:input` - Form inputs with validation support
- `flux:textarea` - Multi-line text inputs
- `flux:select` - Dropdown selections
- `flux:checkbox` - Checkboxes and toggles
- `flux:modal` - Modal dialogs
- `flux:dropdown` - Dropdown menus
- `flux:navbar` - Navigation components
- `flux:icon` - Icon system

### Custom Components
- Authentication forms with Volt integration
- Settings management interfaces
- Profile management with avatar upload
- Dashboard with sidebar navigation

## 🔐 Authentication Features

### Available Authentication Routes
- **Registration**: `/register` - User registration with email verification
- **Login**: `/login` - Username/password authentication
- **Password Reset**: `/forgot-password` - Email-based password reset
- **Email Verification**: `/verify-email` - Email address verification
- **Logout**: Secure session termination

### User Profile Features
- **Profile Management**: Complete profile editing with avatar upload
- **Password Management**: Secure password updates
- **Account Settings**: User preferences and appearance settings
- **Account Deletion**: Secure account deletion with confirmation

## 🧪 Testing

The project includes comprehensive test coverage:

### Feature Tests
- Authentication flow testing
- User registration and login
- Password reset functionality
- Profile management
- Settings updates

### Test Database
Tests use a separate SQLite database for isolation and speed.

## 📚 Development Guidelines

### Code Standards
- PSR-12 coding standards enforced by Laravel Pint
- Comprehensive PHPDoc documentation
- Type declarations for all methods
- Constructor property promotion for PHP 8+

### Laravel Best Practices
- Eloquent relationships over raw queries
- Form Request validation classes
- Resource classes for API responses
- Queue jobs for time-consuming operations
- Environment-based configuration

### Livewire Conventions
- Single-file Volt components for simple interactions
- Class-based components for complex logic
- Proper use of lifecycle hooks
- Wire directives for reactive updates

## 🚀 Deployment

### Production Build
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build production assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Configuration
Ensure these environment variables are set in production:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` (generated key)
- Database connection settings
- Mail configuration for notifications

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Run code formatting with Pint
6. Submit a pull request

### AI-Assisted Development
This project encourages AI-assisted development:
- Use Claude Code for component generation
- Leverage MCP tools for database operations
- AI-generated tests are welcome with human review
- Document AI-assisted changes appropriately

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🔗 Links

- **Repository**: [https://github.com/zachran-recodex/rewire](https://github.com/zachran-recodex/rewire)
- **Laravel Documentation**: [https://laravel.com/docs](https://laravel.com/docs)
- **Livewire Documentation**: [https://livewire.laravel.com](https://livewire.laravel.com)
- **Flux UI Documentation**: [https://fluxui.dev](https://fluxui.dev)
- **Claude Code Documentation**: [https://docs.anthropic.com/claude/docs/claude-code](https://docs.anthropic.com/claude/docs/claude-code)

---

**Built with ❤️ using Laravel, Livewire, and AI-enhanced development tools.**

*This project demonstrates modern Laravel development practices with AI integration, showcasing how traditional web development can be enhanced with intelligent tooling and automation.*