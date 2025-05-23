# PHP CRUD and Dashboard Generator

A modern, secure, and feature-rich CRUD (Create, Read, Update, Delete) and dashboard generator built with PHP, MySQL, and Tailwind CSS. This system provides dynamic CRUD operations for database tables and includes user management with role-based access control.

## Features

- 🔐 User Authentication & Authorization
- 👥 Role-based Access Control (Admin, Editor, User)
- 📊 Dynamic CRUD Operations
- 🎨 Modern UI with Tailwind CSS
- 🛡️ Secure Database Operations (PDO)
- 🔒 Protection against XSS and SQL Injection
- 📝 Audit Logging
- ⚙️ System Settings Management
- 📱 Responsive Design
- 🚀 Easy to Extend

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite enabled
- PDO PHP Extension
- JSON PHP Extension

## Installation

1. Clone or download this repository to your web server directory:
```bash
git clone https://github.com/yourusername/crud-dashboard.git
```

2. Import the database structure:
```bash
mysql -u your_username -p < database.sql
```

3. Configure your database connection in `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'crud_dashboard');
```

4. Ensure proper permissions:
```bash
chmod 755 -R php/
chmod 777 -R php/logs/
```

5. Configure your web server to point to the `php/public` directory

## Default Login Credentials

```
Username: admin
Password: admin123
```

## Directory Structure

```
php/
├── classes/
│   ├── Database.php
│   ├── User.php
│   ├── UserManager.php
│   ├── CrudGenerator.php
│   └── DashboardGenerator.php
├── public/
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── crud.php
│   ├── logout.php
│   └── .htaccess
├── logs/
│   └── app.log
├── config.php
└── database.sql
```

## Security Features

- Password hashing using PHP's password_hash()
- PDO prepared statements for SQL injection prevention
- XSS protection
- CSRF protection
- Session security
- Input validation and sanitization
- Secure headers configuration
- Error logging

## User Levels

1. **Admin**
   - Full access to all features
   - User management
   - System settings

2. **Editor**
   - CRUD operations on content
   - Limited access to settings

3. **User**
   - View content
   - Basic operations

## Customization

### Adding New Tables

1. Create your table in MySQL
2. Access it through the dashboard
3. CRUD operations will be automatically generated

### Modifying the Theme

The system uses Tailwind CSS. You can modify the design by:

1. Editing the HTML templates in the public files
2. Adjusting Tailwind classes
3. Adding custom CSS if needed

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the repository or contact the maintainers.

## Acknowledgments

- Tailwind CSS for the UI framework
- PHP community for best practices and security guidelines
- MySQL team for the robust database system

## Roadmap

- [ ] Add API endpoints
- [ ] Implement file upload handling
- [ ] Add export functionality
- [ ] Implement caching
- [ ] Add more chart types
- [ ] Create installation wizard
