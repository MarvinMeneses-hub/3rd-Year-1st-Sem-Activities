# EdubridgeAcademy — Localhost Package

PHP 8+ / MySQL / XAMPP.

## Install on XAMPP

1. Copy the entire `EdubridgeAcademy` folder to:
   `C:\xampp\htdocs\EdubridgeAcademy`
2. Start **Apache** and **MySQL** in XAMPP.
3. Open phpMyAdmin:
   `http://localhost/phpmyadmin/`
4. Create/import the database named:
   `edubridgeacademy`
5. Import `database/edubridgeacademy.sql` for a fresh installation.
6. Open:
   `http://localhost/EdubridgeAcademy/`

## Development accounts

- Admin: `admin@edubridgeacademy.test` / `password`
- Teacher: `teacher@edubridgeacademy.test` / `password`
- Student: `student@edubridgeacademy.test` / `password`

## Important

This package is configured specifically for local XAMPP:
- Host: `127.0.0.1`
- Database: `edubridgeacademy`
- User: `root`
- Password: blank

The package includes the current EdubridgeAcademy responsive/pastel-pink skeuomorphic styling, mobile sidebar menu, button animations, media responsiveness, and selection/dropdown styling.

If an existing local `edubridgeacademy` database is already working, do **not** import the full SQL because it recreates the tables. Use the appropriate migration only when needed.
