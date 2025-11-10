# 🏨 Hotel Reservation Transaction Processing System

> A robust hotel reservation system demonstrating practical implementation of data structures and algorithms in a real-world transaction processing environment. This school project features ACID-compliant booking operations, concurrent request handling, and secure payment processing.

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com/)
[![React](https://img.shields.io/badge/React-18.3-61DAFB?style=flat&logo=react&logoColor=black)](https://react.dev/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.5-3178C6?style=flat&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)
[![Java](https://img.shields.io/badge/Java-17+-ED8B00?style=flat&logo=java&logoColor=white)](https://www.java.com/)
[![JavaFX](https://img.shields.io/badge/JavaFX-21-ED8B00?style=flat&logo=java&logoColor=white)](https://openjfx.io/)

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Data Structures & Algorithms](#data-structures--algorithms)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Testing](#testing)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [License](#license)

## 🎯 Overview

This project is a comprehensive **Transaction Processing System (TPS)** for hotel reservations that demonstrates the practical application of fundamental data structures and algorithms. The system ensures ACID compliance, handles concurrent requests efficiently, and provides real-time booking capabilities through a hybrid web-desktop architecture.

**Key Highlights:**
- ✅ ACID-compliant transaction management with automatic rollback
- ✅ Custom data structure implementations (Queue, HashTable, AVL Tree, Stack, Graph)
- ✅ Performance optimizations achieving 10x faster queries
- ✅ Hybrid architecture: Web app + Desktop admin panel
- ✅ Secure payment processing via Xendit gateway

## ✨ Features

### Core Functionality
- **User Authentication & Registration** - Secure user accounts with session management
- **Real-Time Room Availability** - Instant availability checking with O(1) hash table lookups
- **Reservation Management** - Create, modify, and cancel bookings with transaction safety
- **Payment Processing** - Secure payment integration via Xendit (credit/debit cards, digital wallets)
- **Promo Code System** - Discount code validation and application
- **Review & Rating System** - Guest feedback and ratings
- **Admin Dashboard** - Comprehensive management interface (JavaFX desktop application)

### Transaction Processing
- **ACID Compliance** - All operations maintain atomicity, consistency, isolation, and durability
- **Concurrent Request Handling** - Queue-based system prevents race conditions
- **Transaction Rollback** - Automatic rollback on failures using Stack data structure
- **Audit Trail** - Complete transaction logging for all operations

### Performance Optimizations
- **Hash Table** - O(1) average-time lookups for room status and user sessions
- **AVL Tree** - O(log n) price range queries and sorted room listings
- **Queue** - FIFO processing for fair request handling during peak loads
- **Graph Algorithms** - BFS/DFS for multi-room booking logic
- **Database Indexing** - Strategic indexes for 10x query performance improvement

## 📊 Data Structures & Algorithms

### Implemented Data Structures

| Structure | Implementation | Complexity | Use Case |
|-----------|---------------|------------|----------|
| **Queue (FIFO)** | `BookingQueue.php` | O(1) enqueue/dequeue | Concurrent booking request management |
| **Hash Table** | `HashTable.php` | O(1) average lookup | Room status, user sessions, active reservations |
| **AVL Tree** | `BST.php` | O(log n) operations | Price range queries, sorted room inventory |
| **Stack (LIFO)** | `TransactionStack.php` | O(1) push/pop | Transaction rollback, undo/redo operations |
| **Graph** | `RoomGraph.php` | O(V + E) traversal | Room dependencies, multi-room bookings |

### Implemented Algorithms

- **Sorting Algorithms** (Java Admin Panel):
  - Quick Sort - O(n log n) average case
  - Merge Sort - O(n log n) worst case, stable
  - Heap Sort - O(n log n) guaranteed performance

- **Graph Traversal**:
  - Breadth-First Search (BFS) - Shortest path finding
  - Depth-First Search (DFS) - Room connection exploration

- **Hash Functions**:
  - djb2 algorithm - Efficient key distribution

- **Tree Operations**:
  - AVL rotations (left, right, left-right, right-left)
  - Range queries with pruning

## 🛠️ Tech Stack

### Backend
- **PHP 8.2+** - Server-side programming
- **Laravel 12.0** - Web framework
- **SQLite/MySQL** - Database (SQLite default, MySQL supported)

### Frontend
- **React 18.3** - UI library
- **TypeScript 5.5** - Type-safe JavaScript
- **Tailwind CSS 3.1** - Utility-first CSS framework
- **Alpine.js** - Lightweight JavaScript framework
- **Vite** - Build tool and dev server

### Admin Panel
- **Java 17+** - Programming language
- **JavaFX 21** - Desktop application framework
- **Maven** - Build automation tool
- **JDBC** - Database connectivity

### Payment & Services
- **Xendit API** - Payment gateway integration
- **Laravel Mail** - Email notifications

## 🚀 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- Java 17+ (for admin panel)
- Maven 3.6+ (for admin panel)
- XAMPP (Windows) or LAMP (Linux) - Apache, MySQL, PHP stack

### Web Application Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd hotel-reservation-system
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** (Edit `.env` file)
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   php artisan db:seed  # Optional: seed sample data
   ```

6. **Install frontend dependencies**
   ```bash
   npm install
   npm run build
   ```

7. **Start development server**
   ```bash
   # Terminal 1: Laravel server
   php artisan serve

   # Terminal 2: Vite dev server (for hot-reload)
   npm run dev
   ```

8. **Access the application**
   - Web Application: http://localhost:8000
   - Admin Login: http://localhost:8000/login

### Admin Panel Setup

1. **Navigate to admin panel directory**
   ```bash
   cd admin-panel
   ```

2. **Configure database connection**
   - Edit `src/main/resources/config.properties`
   - Update database credentials

3. **Build the project**
   ```bash
   mvn clean compile
   ```

4. **Run the application**
   ```bash
   mvn javafx:run
   ```

5. **Create admin account** (before first login)
   ```bash
   # In Laravel project root
   php artisan tinker
   ```
   ```php
   App\Models\Admin::create([
       'name' => 'Admin User',
       'email' => 'admin@belmonthotel.com',
       'password' => bcrypt('password'),
       'is_active' => true
   ]);
   ```

For detailed installation instructions, see [INSTALLATION.md](docs/INSTALLATION.md) and [ADMIN_PANEL_SETUP.md](ADMIN_PANEL_SETUP.md).

## 💻 Usage

### Web Application

1. **Register/Login** - Create an account or login
2. **Search Rooms** - Browse available rooms with filters
3. **Create Booking** - Select dates and room, complete reservation
4. **Make Payment** - Process payment via Xendit gateway
5. **Manage Bookings** - View, modify, or cancel reservations

### Admin Panel

1. **Login** - Use admin credentials
2. **Dashboard** - View system statistics and metrics
3. **Manage Rooms** - Add, edit, or delete rooms
4. **View Bookings** - Monitor all reservations
5. **Process Payments** - Track payment transactions
6. **Generate Reports** - Export booking and revenue reports

## 📁 Project Structure

```
hotel-reservation-system/
├── app/
│   ├── DataStructures/          # Custom data structure implementations
│   │   ├── BookingQueue.php     # FIFO Queue
│   │   ├── HashTable.php        # Hash Table with chaining
│   │   ├── BST.php              # AVL Tree (self-balancing BST)
│   │   ├── TransactionStack.php # LIFO Stack
│   │   └── RoomGraph.php        # Graph with BFS/DFS
│   ├── Http/Controllers/        # Request handlers
│   ├── Models/                  # Eloquent models
│   ├── Services/                # Business logic
│   └── Mail/                    # Email templates
├── admin-panel/                 # JavaFX desktop application
│   ├── src/main/java/          # Java source code
│   └── pom.xml                 # Maven configuration
├── database/
│   ├── migrations/             # Database schema
│   └── seeders/                # Sample data
├── resources/
│   ├── js/                     # React/TypeScript components
│   ├── views/                  # Blade templates
│   └── css/                    # Stylesheets
├── routes/                     # Application routes
├── tests/                      # PHPUnit tests
└── docs/                       # Documentation
```

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ReservationTest.php

# Run with coverage
php artisan test --coverage
```

**Test Coverage:**
- ✅ Transaction Processing: 15+ test cases
- ✅ User Authentication: 8+ test cases
- ✅ Payment Processing: 6+ test cases
- ✅ Database Interactions: 5+ test cases
- ✅ Promo Code Validation: 7+ test cases

## 📚 Documentation

- [Installation Guide](docs/INSTALLATION.md) - Detailed setup instructions
- [API Documentation](docs/API.md) - API endpoint reference
- [Endpoints Reference](docs/ENDPOINTS.md) - Quick endpoint guide
- [Admin Panel Setup](ADMIN_PANEL_SETUP.md) - JavaFX admin panel guide
- [React Setup](REACT_SETUP.md) - Frontend component setup

## 🎓 Academic Context

This project was developed as a **school/academic project** to demonstrate:
- Practical application of data structures and algorithms
- ACID transaction processing principles
- System design and architecture patterns
- Full-stack development with multiple technologies
- Performance optimization techniques

## 🤝 Contributing

This is an academic project. Contributions, suggestions, and improvements are welcome!

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Laravel Framework** - https://laravel.com
- **React** - https://react.dev
- **JavaFX** - https://openjfx.io
- **Xendit** - Payment gateway integration
- **Tailwind CSS** - Utility-first CSS framework

## 📧 Contact

For questions or inquiries about this project, please open an issue in the repository.

---

**Note:** This is an academic/school project demonstrating practical implementation of computer science concepts in a real-world application context.
