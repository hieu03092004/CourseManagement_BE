# Setup MySQL Database cho Laravel (giống Express)

## 1. Cấu hình .env

Thêm/cập nhật các dòng sau trong file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ql_ban_sua
DB_USERNAME=root
DB_PASSWORD=
```

## 2. Khởi động XAMPP

- Mở XAMPP Control Panel
- Start Apache và MySQL
- Vào phpMyAdmin: `http://localhost/phpmyadmin`
- Tạo database: `ql_ban_sua`

## 3. Cấu trúc Database (giống Express)

### Express (index.js):
```javascript
const database = require("./config/database.js");
database.connect();
```

### Laravel (bootstrap/app.php):
```php
\App\Database\MySQLConnection::connect();
```

## 4. Sử dụng trong Controller

### Cách 1: Dùng DatabaseHelper (khuyến nghị)
```php
use App\Helpers\DatabaseHelper;

// SELECT
$products = DatabaseHelper::select("SELECT * FROM products WHERE status = 'active'");

// INSERT
DatabaseHelper::execute("INSERT INTO products (title, price) VALUES ('Product 1', 100000)");
$lastId = DatabaseHelper::lastInsertId();

// UPDATE
DatabaseHelper::execute("UPDATE products SET title = 'New Title' WHERE id = 1");

// DELETE
DatabaseHelper::execute("DELETE FROM products WHERE id = 1");
```

### Cách 2: Dùng trực tiếp MySQLConnection
```php
use App\Database\MySQLConnection;

$conn = MySQLConnection::getConnection();
$result = $conn->query("SELECT * FROM products");

while ($row = $result->fetch_assoc()) {
    echo $row['title'];
}
```

## 5. Files đã tạo

```
app/
├── Database/
│   └── MySQLConnection.php      (Kết nối MySQL - giống database.js)
├── Helpers/
│   ├── DatabaseHelper.php       (Helper query database)
│   └── StatisticHelper.php
└── Providers/
    └── DatabaseServiceProvider.php

bootstrap/
└── app.php                      (Gọi MySQLConnection::connect())
```

## 6. So sánh Express vs Laravel

| Express | Laravel |
|---------|---------|
| `const database = require("./config/database.js")` | `use App\Database\MySQLConnection` |
| `database.connect()` | `MySQLConnection::connect()` |
| `mongoose.connect()` | `MySQLConnection::connect()` |

## 7. Test Connection

Chạy server:
```bash
php artisan serve
```

Nếu kết nối thành công, sẽ thấy message: `MySQL Connected Successfully`

