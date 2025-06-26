# bacode_system

## 🚀 วิธีรันบน Docker

1. **Clone โปรเจกต์**
    ```bash
    git clone https://github.com/Adapter877/bacode_system
    cd bacode_system
    ```

2. **สร้างไฟล์ `docker-compose.yml`**
    ```yaml
    version: '3.8'
    services:
      web:
        image: php:8.1-apache
        container_name: bacode_system_web
        volumes:
          - ./:/var/www/html
        ports:
          - "8080:80"
        depends_on:
          - db
      db:
        image: mysql:8.0
        container_name: bacode_system_db
        restart: always
        environment:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: bacode_system
          MYSQL_USER: user
          MYSQL_PASSWORD: password
        ports:
          - "3306:3306"
        volumes:
          - db_data:/var/lib/mysql
    volumes:
      db_data:
    ```

3. **ตั้งค่าเชื่อมต่อฐานข้อมูลใน `admin/dbconfig.php`**
    ```php
    $conn = mysqli_connect("db", "user", "password", "bacode_system");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    ```

4. **รัน Docker Compose**
    ```bash
    docker-compose up -d
    ```

5. **Import Database**
    - นำไฟล์ `database.sql` ไป import ใน MySQL Container (ใช้ phpMyAdmin หรือ mysql CLI)

6. **เข้าใช้งานระบบ**
    - เปิดเบราว์เซอร์ไปที่ [http://localhost:8080/admin/login.php](http://localhost:8080/admin/login.php)

---

## 🔑 บัญชีสำหรับทดสอบ

| Role    | Username | Password    |
|---------|----------|-------------|
| Admin   | admin    | 123456789   |
| Editor  | editor   | 123456789   |

---

## Tech Stack

**Client:** Bootstrap, PHP, MySQL  
**Server:** Apache, Docker

---

> ระบบนี้ชื่อ **bacode_system**  
> พัฒนาโดย Adapter877



