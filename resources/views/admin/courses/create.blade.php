<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tạo Khoá Học Mới</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        label { display: block; margin-top: 10px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; }
        .message { margin-top: 20px; color: green; }
        .error { margin-top: 20px; color: red; }
    </style>
</head>
<body>
    <h1>Tạo Khoá Học Mới</h1>
    <form id="createCourseForm">
        <label for="USER_ID">User ID</label>
        <input type="number" id="USER_ID" name="USER_ID" required>

        <label for="TITLE">Tiêu đề</label>
        <input type="text" id="TITLE" name="TITLE" required>

        <label for="DESCRIPTION">Mô tả</label>
        <textarea id="DESCRIPTION" name="DESCRIPTION" required></textarea>

        <label for="TARGET">Mục tiêu</label>
        <textarea id="TARGET" name="TARGET" required></textarea>

        <label for="RESULT">Kết quả mong đợi</label>
        <textarea id="RESULT" name="RESULT" required></textarea>

        <label for="IMAGE">URL Ảnh</label>
        <input type="text" id="IMAGE" name="IMAGE">

        <label for="DURATION">Thời lượng (giờ)</label>
        <input type="number" id="DURATION" name="DURATION" required>

        <label for="PRICE">Giá (VND)</label>
        <input type="number" step="0.01" id="PRICE" name="PRICE" required>

        <label for="TYPE">Loại</label>
        <input type="text" id="TYPE" name="TYPE" required>

        <label for="DISCOUNT_PERCENT">Phần trăm giảm giá</label>
        <input type="number" step="0.01" id="DISCOUNT_PERCENT" name="DISCOUNT_PERCENT">

        <button type="submit">Tạo Khoá Học</button>
    </form>

    <div class="message" id="message"></div>
    <div class="error" id="error"></div>

    <script>
        const form = document.getElementById('createCourseForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch("{{ route('courses.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if(result.success) {
                    document.getElementById('message').innerText = result.message + " ID khoá học: " + result.course;
                    document.getElementById('error').innerText = '';
                } else {
                    document.getElementById('error').innerText = result.message || 'Có lỗi xảy ra';
                    document.getElementById('message').innerText = '';
                }
            } catch (err) {
                document.getElementById('error').innerText = 'Lỗi khi gửi request';
                document.getElementById('message').innerText = '';
            }
        });
    </script>
</body>
</html>
