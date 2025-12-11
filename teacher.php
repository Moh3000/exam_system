<!DOCTYPE html>
<html lang="en" >

<head>
    <meta charset="UTF-8">
    <title>teacher main page</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body class="d-flex justify-content-center align-items-center vh-100 bg-light">

    <div class="container text-center">

        <h2 class="mb-4">
            Exam System <i class="bi bi-book"></i>
        </h2>

        <div class="row justify-content-center">

            <div class="col-md-4 mb-3">
                <a href="create_exam.php" class="text-decoration-none">
                    <div class="card shadow text-center p-4">
                        <h4><i class="bi bi-pencil-square"></i></h4>
                        <p>Make New Exam</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4 mb-3">
                <a href="add_question.php" class="text-decoration-none">
                    <div class="card shadow text-center p-4">
                        <h4><i class="bi bi-plus-circle"></i></h4>
                        <p class="text-muted">Add New Question</p>
                    </div>
                </a>
            </div>

        </div>

    </div>

</body>

</html>