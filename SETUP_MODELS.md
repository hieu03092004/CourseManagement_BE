# Hướng dẫn sử dụng Models (giống Express)

## Cấu trúc

```
app/
├── Models/
│   ├── BaseModel.php       (Base model với các method query chung)
│   ├── LoaiSua.php         (Model cho bảng loai_sua)
│   └── Sua.php             (Model cho bảng sua)
├── Providers/
│   └── AppServiceProvider.php  (Kết nối DB khi start server)
└── Http/Controllers/
    └── Admin/
        └── TestDatabaseController.php  (Chỉ cần gọi Model)
```

## Cách hoạt động

### 1. Khi start server (giống Express)

**Express:**
```javascript
// index.js
const database = require("./config/database.js");
database.connect(); // Kết nối 1 lần

// controllers chỉ cần query
const products = await Product.find();
```

**Laravel:**
```php
// AppServiceProvider->boot()
MySQLConnection::connect(); // Kết nối 1 lần khi app khởi động

// Controllers chỉ cần gọi Model
$loaiSua = LoaiSua::getAll();
```

### 2. Sử dụng trong Controller

**Không cần kết nối database nữa!**

```php
use App\Models\LoaiSua;
use App\Models\Sua;

class TestDatabaseController extends Controller
{
    public function index()
    {
        // Chỉ cần gọi Model
        $loaiSua = LoaiSua::getAll();
        $sua = Sua::getAllWithLoai();
        
        return response()->json([
            'loaiSua' => $loaiSua,
            'sua' => $sua,
        ]);
    }
}
```

## BaseModel - Methods có sẵn

### 1. **all()** - Lấy tất cả records
```php
$data = LoaiSua::all();
```

### 2. **find($id, $primaryKey)** - Tìm theo ID
```php
$loaiSua = LoaiSua::find('SBO', 'Ma_Loai_Sua');
$sua = Sua::find('M001', 'Ma_sua');
```

### 3. **where($conditions)** - Tìm theo điều kiện
```php
$sua = Sua::where(['Ma_loai_sua' => 'SBO']);
$sua = Sua::where(['Ten_sua' => 'Vinamilk', 'status' => 'active']);
```

### 4. **create($data)** - Thêm mới
```php
$id = LoaiSua::create([
    'Ma_Loai_Sua' => 'SNT',
    'Ten_loai' => 'Sữa Nước Trái cây'
]);
```

### 5. **update($id, $data, $primaryKey)** - Cập nhật
```php
LoaiSua::update('SBO', [
    'Ten_loai' => 'Sữa Bột Cao Cấp'
], 'Ma_Loai_Sua');
```

### 6. **delete($id, $primaryKey)** - Xóa
```php
LoaiSua::delete('SBO', 'Ma_Loai_Sua');
```

### 7. **query($sql)** - Query tùy chỉnh
```php
$result = LoaiSua::query("SELECT * FROM loai_sua WHERE Ten_loai LIKE '%Sữa%'");
```

## Tạo Model mới

**Ví dụ: Model HangSua**

```php
<?php

namespace App\Models;

class HangSua extends BaseModel
{
    protected static $table = 'hang_sua';

    public static function getAll()
    {
        return self::all();
    }

    public static function findByMa($maHang)
    {
        return self::find($maHang, 'Ma_hang_sua');
    }

    // Custom methods
    public static function getTopHang()
    {
        $query = "SELECT * FROM " . self::$table . " ORDER BY Ten_hang_sua LIMIT 10";
        return self::query($query);
    }
}
```

## Sử dụng trong Controller

```php
use App\Models\LoaiSua;
use App\Models\Sua;
use App\Models\HangSua;

class ProductController extends Controller
{
    public function index()
    {
        // Lấy tất cả
        $loaiSua = LoaiSua::getAll();
        
        // Tìm theo ID
        $sua = Sua::findByMa('M001');
        
        // Tìm theo điều kiện
        $suaTheoLoai = Sua::getByLoai('SBO');
        
        // JOIN bảng
        $suaWithLoai = Sua::getAllWithLoai();
        
        return response()->json([
            'loaiSua' => $loaiSua,
            'sua' => $sua,
            'suaTheoLoai' => $suaTheoLoai,
            'suaWithLoai' => $suaWithLoai,
        ]);
    }
}
```

## Endpoints Test

- `GET http://127.0.0.1:8000/admin/test-database/connection` - Lấy loại sữa
- `GET http://127.0.0.1:8000/admin/test-database/products` - Lấy sữa (JOIN)

## Lưu ý

- **Kết nối 1 lần** khi start server (AppServiceProvider)
- **Controllers chỉ việc gọi Model** - không cần kết nối lại
- **Models kế thừa BaseModel** - có sẵn các method CRUD
- **Tự động escape SQL injection** qua DatabaseHelper

