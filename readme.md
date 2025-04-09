# Sistem Informasi Apotek

Sistem manajemen apotek berbasis web yang dibangun menggunakan PHP dan PostgreSQL.

## Requirements

- PHP 7.4 atau lebih tinggi
- PostgreSQL 12 atau lebih tinggi
- PHP PostgreSQL Extension (pgsql)
- Web Browser Modern (Chrome, Firefox, Edge)

## Konfigurasi Database

1. Buat database baru di PostgreSQL
2. Import file `database.sql` ke database yang telah dibuat
3. Sesuaikan konfigurasi database di `config/config.php`:

```php
$host = "localhost";
$port = "5432";
$dbname = "nama_database";
$user = "username_database";
$password = "password_database";
```

## Cara Menjalankan Project

### Menggunakan PHP Built-in Server

1. Buka terminal/command prompt
   ```bash
   git clone https://github.com/Rindo88/apotek-management.git
   ```
3. Masuk ke direktori project

```bash
cd apotek-management
```

3. Jalankan PHP development server

```bash
php -S localhost:8000 router.php
```

4. Buka browser dan akses `http://localhost:8000`

### Default Admin Account

```json
{
  username: "admin"
  password: "admin"
}
```
