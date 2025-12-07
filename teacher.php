<?php
// يمكنك إضافة أي سيشن أو حماية هنا لاحقاً
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #f5f5f5;
        }

        .card {
            transition: 0.3s;
            cursor: pointer;
        }

        .card:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body>

    <div class="container mt-5">

        <h2 class="text-center mb-4">📘 نظام الامتحانات</h2>

        <div class="row justify-content-center">

            <!-- إنشاء امتحان -->
            <div class="col-md-4 mb-3">
                <a href="create_exam.php" style="text-decoration: none;">
                    <div class="card shadow text-center p-4">
                        <h4>✏️ إنشاء امتحان</h4>
                        <p class="text-muted">ابدأ بإنشاء امتحان جديد للطلاب</p>
                    </div>
                </a>
            </div>

            <!-- إضافة سؤال -->
            <div class="col-md-4 mb-3">
                <a href="add_question.php" style="text-decoration: none;">
                    <div class="card shadow text-center p-4">
                        <h4>➕ إضافة سؤال</h4>
                        <p class="text-muted">أضف سؤالًا جديدًا إلى بنك الأسئلة</p>
                    </div>
                </a>
            </div>

            <!-- مشاهدة الأسئلة -->
            <div class="col-md-4 mb-3">
                <a href="questions_list.php" style="text-decoration: none;">
                    <div class="card shadow text-center p-4">
                        <h4>📚 بنك الأسئلة</h4>
                        <p class="text-muted">استعرض جميع الأسئلة</p>
                    </div>
                </a>
            </div>

        </div>

    </div>

</body>

</html>