<?php
session_start();

$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jerusalem');


$student_id = 1;

$error_message = "";


if (isset($_POST['start_exam'])) {

    $student_exam_id = intval($_POST['student_exam_id']);

    $res = mysqli_query($link, "
        SELECT start_time, submit_time, status 
        FROM StudentExams
        WHERE id = $student_exam_id
          AND student_id = $student_id
        LIMIT 1
    ");

    if ($row = mysqli_fetch_assoc($res)) {

        $now   = time();
        $start = strtotime($row['start_time']);
        $end   = strtotime($row['submit_time']);

        if (
            ($row['status'] === 'not_started') &&
            $now >= $start &&
            $now <= $end
        ) {
            mysqli_query($link, "
                UPDATE StudentExams
                SET status = 'in_progress'
                WHERE id = $student_exam_id
            ");

            header("Location: take_exam.php?se_id=$student_exam_id");
            exit;
        } else {
            $error_message = "Exam is not available at this time.";
        }
    }
}


$exams = mysqli_query($link, "
    SELECT 
        se.id AS student_exam_id,
        se.status,
        se.start_time,
        se.submit_time,
        e.title,
        e.description,
        e.time_limit_minutes,
        COUNT(DISTINCT eq.question_id) AS question_count
    FROM StudentExams se
    JOIN Exams e ON se.exam_id = e.ExamID
    JOIN ExamQuestions eq ON e.ExamID = eq.ExamID
    WHERE se.student_id = $student_id
    GROUP BY se.id
    ORDER BY e.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Available Exams</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/Exams.css" rel="stylesheet">

</head>

<body>

    <div class="container">
        <div class="box">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="m-0">Available Exams</h2>
                <a href="student.php" class="btn btn-primary">Back to Main Page</a>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if (mysqli_num_rows($exams) == 0): ?>
                <p>No exams available.</p>
            <?php else: ?>

                <div class="accordion" id="examAccordion">

                    <?php while ($exam = mysqli_fetch_assoc($exams)): ?>

                        <?php
                        $now   = time();
                        $start = strtotime($exam['start_time']);
                        $end   = strtotime($exam['submit_time']);
                        ?>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $exam['student_exam_id'] ?>">
                                <button class="accordion-button collapsed"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse<?= $exam['student_exam_id'] ?>">
                                    <?= htmlspecialchars($exam['title']) ?>
                                </button>
                            </h2>

                            <div id="collapse<?= $exam['student_exam_id'] ?>"
                                class="accordion-collapse collapse"
                                data-bs-parent="#examAccordion">

                                <div class="accordion-body">

                                    <p><b>Description:</b><br>
                                        <?= nl2br(htmlspecialchars($exam['description'])) ?>
                                    </p>

                                    <p><b>Time Limit:</b> <?= $exam['time_limit_minutes'] ?> minutes</p>
                                    <p><b>Questions:</b> <?= $exam['question_count'] ?></p>
                                    <p><b>Start Time:</b> <?= date('Y-m-d H:i', $start) ?></p>
                                    <p><b>Submit Time:</b> <?= date('Y-m-d H:i', $end) ?></p>
                                    <p><b>Status:</b> <?= $exam['status'] ?></p>

                                    <hr>

                                    <?php if ($now < $start): ?>

                                        <div class="alert alert-info">
                                            Exam has not started yet.
                                        </div>

                                    <?php elseif ($now > $end): ?>

                                        <div class="alert alert-danger">
                                            Exam time has ended.
                                        </div>

                                    <?php elseif ($exam['status'] === 'not_started'): ?>

                                        <form method="POST">
                                            <input type="hidden" name="student_exam_id"
                                                value="<?= $exam['student_exam_id'] ?>">
                                            <button type="submit"
                                                name="start_exam"
                                                class="btn btn-success">
                                                Start Exam
                                            </button>
                                        </form>

                                    <?php elseif ($exam['status'] === 'in_progress'): ?>

                                        <a href="take_exam.php?se_id=<?= $exam['student_exam_id'] ?>"
                                            class="btn btn-warning">
                                            Continue Exam
                                        </a>

                                    <?php elseif ($exam['status'] === 'submitted'): ?>

                                        <div class="alert alert-success">
                                            Exam submitted.
                                        </div>

                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>

                </div>

            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>