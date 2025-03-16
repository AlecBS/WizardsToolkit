# Wizard's Toolkit (WTK)

[![Apache License 2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-8.3-blue.svg)](https://www.mysql.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Latest-blue.svg)](https://www.postgresql.org)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://github.com/AlecBS/WizardsToolkit/graphs/commit-activity)

> 🚀 A powerful, production-ready full-stack development framework for building modern web applications. Enterprise-grade, Docker-ready, and continuously maintained since 2009.

<p align="center">
  <a href="https://wizardstoolkit.com/docs/">📚 Documentation</a> •
  <a href="https://wizardstoolkit.com/tutorials.php">🎓 Tutorials</a> •
  <a href="https://wizardstoolkit.com/wtk.php">🔥 Live Demo</a> •
  <a href="#quick-start-with-docker">⚡ Quick Start</a>
</p>

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Quick Start with Docker](#quick-start-with-docker)
  - [Prerequisites](#prerequisites)
  - [Basic Setup](#basic-setup)
  - [Access Points](#access-points)
- [Project Structure](#project-structure)
- [Configuration](#configuration)
  - [Local Development Setup](#local-development-setup)
  - [Database Configuration](#database-configuration)
- [Development Tools](#development-tools)
  - [Utility Scripts](#utility-scripts)
  - [Database Management](#database-management)
- [Documentation & Resources](#documentation--resources)
- [License](#license)
- [Troubleshooting](#troubleshooting)
  - [Common Issues](#common-issues)
- [Security](#security)

## Overview

Wizard's Toolkit (WTK) is a powerful full-stack development framework combining PHP, SQL, JavaScript, and MaterializeCSS. Originally created in 2009 and continuously maintained, WTK streamlines the development of data-driven websites and mobile applications. The framework has evolved through multiple PHP versions and is currently optimized for PHP 8.1 while maintaining compatibility with earlier versions.

It provides a comprehensive solution for building and maintaining full-featured data-driven websites and mobile apps quickly and efficiently. The repository includes SQL table definitions, initial data, PHP components, HTML templates, CSS styling, and JavaScript functionality, along with extensive documentation created via phpDocs and numerous demo files.

🌐 **Website**: [https://wizardstoolkit.com](https://wizardstoolkit.com)

## Key Features

- **Full-Stack Solution**: Integrated PHP, SQL, JavaScript, and MaterializeCSS
- **Database Flexibility**: Seamless support for both MySQL 8.3 and PostgreSQL
- **Docker-Ready**: Complete development environment setup in under 2 minutes
- **Modern Stack**:
  - Nginx with Alpine for lightweight performance
  - PHP 8.1 with PDO support
  - MySQL 8.3 or PostgreSQL latest
  - Development tools (phpMyAdmin for MySQL)
- **Enterprise-Grade**: Production-tested since 2009 with continuous updates
- **Extensive Documentation**: Includes API docs, demos, and tutorials
- **Version Compatibility**: Supports PHP 5.4 through 8.1

## Quick Start with Docker

### Prerequisites

1. **For Windows Users**:

   - WSL2 with Ubuntu 20.04 LTS
   - Docker Desktop
   - Docker Hub account (free)

2. **For Mac Users**:
   - Docker Desktop
   - Docker Hub account (free)

### Basic Setup

1. **Clone the Repository**:

   ```bash
   git clone https://github.com/AlecBS/WizardsToolkit.git
   cd WizardsToolkit
   ```

2. **Initialize Environment**:

   ```bash
   ./WTK.sh
   ```

3. **Choose and Setup Database**:

   - For MySQL:
     ```bash
     ./SETUP_MYSQL.sh
     ```
   - For PostgreSQL: Automatic setup on first launch

4. **Verify Installation**:
   Visit http://127.0.0.1/devUtils/testWTK.php to confirm:
   - Database connectivity
   - Environment variables
   - Email functionality
   - Set admin password

### Access Points

- **Main Application**: http://127.0.0.1/ or http://dev.wtk.com/
- **Database Admin**:
  - MySQL: http://127.0.0.1:8080/ (phpMyAdmin)
  - PostgreSQL: Use [DBeaver](https://dbeaver.io/) (recommended)
- **Default Admin**:
  - Email: admin@email.com
  - Set password: /wtk/passwordReset.php?u=needToSet

## Project Structure

```
WizardsToolkit/
├── app/                # Application source files
├── config/             # Configuration settings
├── SQL/                # Database scripts and migrations
│   ├── mySQL/          # MySQL specific scripts
│   └── postgresql/     # PostgreSQL specific scripts
├── Mounts/             # Docker volume mount points
└── *.sh                # Utility scripts
```

## Configuration

### Local Development Setup

1. **Host Configuration** (Optional but recommended):

   ```
   # Add to /etc/hosts
   127.0.0.1   dev.wtk.com
   ```

2. **Environment Settings**:
   Edit your database-specific config:
   ```env
   # In phpMySQL.env or phpPG.env
   URL = "http://dev.wtk.com"  # or http://127.0.0.1
   ```

### Database Configuration

Choose your preferred database:

**MySQL (Default)**:

- Use existing configuration
- Data location: `/Mounts/mydata`
- Access via phpMyAdmin or CLI

**PostgreSQL**:

- Rename `docker-composePG.yml` to `docker-compose.yml`
- Data location: `/Mounts/pgdata`
- Connect using DBeaver or preferred client
- Connection details:
  ```
  Database: pgwiztools
  Username: wizdba
  Password: See docker-compose.yml
  ```

## Development Tools

### Utility Scripts

- `WTK.sh`: Initialize environment
- `START_CONTAINERS.sh`: Launch services
- `STOP_CONTAINERS.sh`: Stop all containers
- `REBUILD_CONTAINERS.sh`: Rebuild environment
- `RESTART_WEBSITE.sh`: Quick service restart
- `SETUP_MYSQL.sh`: Initialize MySQL database
- `MYSQL.sh`: MySQL CLI access

### Database Management

#### MySQL (via phpMyAdmin)

Access phpMyAdmin at http://127.0.0.1:8080/ or http://dev.wtk.com:8080/

1. **Login**:

   - Username: root
   - Password: (see docker-compose.yml)

2. **Basic Operations**:

   - Select `wiztools` database from the left sidebar
   - Browse tables and data using the GUI interface
   - Execute SQL queries using the "SQL" tab
   - Import/Export data using the corresponding tabs

3. **Common Tasks**:
   - View table structure: Click table name → "Structure"
   - Browse data: Click table name → "Browse"
   - Add records: Click table name → "Insert"
   - Backup database: Export → "Quick" or "Custom"

#### PostgreSQL

Connection Details:

- Database: pgwiztools
- Username: wizdba
- Password: (see docker-compose.yml)

Recommended PostgreSQL GUI Client: [DBeaver](https://dbeaver.io/) (free)

Note: Ensure no local PostgreSQL server is running before starting the Docker containers.

## Documentation & Resources

- [Official Documentation](https://wizardstoolkit.com/docs/)
- [Setup Guide](https://wizardstoolkit.com/docs/setup.html)
- [Wiki](https://wizardstoolkit.com/wiki/)
- [Tutorials](https://wizardstoolkit.com/tutorials.php)
- [Demo Files](/app/public/demo/)
- [Contact Form](https://wizardstoolkit.com/contact.php)

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE) file for details.

## Troubleshooting

### Common Issues

1. **Docker Build Failure**:

   ```bash
   export DOCKER_BUILDKIT=0
   ./WTK.sh  # Retry build
   ```

2. **Database Reset**:

   ```bash
   ./STOP_CONTAINERS.sh
   rm -rf /Mounts/mydata  # For MySQL
   # or
   rm -rf /Mounts/pgdata  # For PostgreSQL
   ./START_CONTAINERS.sh
   ./SETUP_MYSQL.sh  # MySQL only
   ```

3. **Port Conflicts**:

   - Ensure ports 80 and 443 are available
   - Stop any running web servers (Apache, Nginx)
   - Choose between MAMP or Docker, not both

4. **First-Time Setup**:

   - Database initialization takes ~1-2 minutes
   - Monitor progress in DockerDesktop
   - Wait for database container to be fully ready

5. **Connection Issues**:
   - Verify Docker containers are running
   - Check ports are not blocked by firewall
   - Ensure correct URL configuration in env files

## Security

For security-related issues, please use our
 [contact form](https://wizardstoolkit.com/contact.php).

---

Built and maintained with ❤️ since 2009
