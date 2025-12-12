<?php
session_start();
$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}



$exams = mysqli_query($link, "
    SELECT se.id AS student_exam_id, e.*,
           COUNT(DISTINCT eq.question_id) AS question_count,
           se.start_time, se.submit_time
    FROM StudentExams se
    JOIN Exams e ON se.exam_id = e.ExamID
    JOIN ExamQuestions eq ON e.ExamID = eq.ExamID
    WHERE se.student_id=1
    GROUP BY se.id
    ORDER BY e.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Available Exams</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style_Exams.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="box">
            <h2 class="text-primary mb-4">Available Exams</h2>

            <?php if (mysqli_num_rows($exams) == 0): ?>
                <p>No exams available.</p>
            <?php else: ?>
                <div class="accordion" id="examAccordion">
                    <?php while ($exam = mysqli_fetch_assoc($exams)): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $exam['student_exam_id'] ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $exam['student_exam_id'] ?>" aria-expanded="false">
                                    <?= htmlspecialchars($exam['title']) ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $exam['student_exam_id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $exam['student_exam_id'] ?>" data-bs-parent="#examAccordion">
                                <div class="accordion-body">
                                    <p><b>Description:</b> <?= nl2br(htmlspecialchars($exam['description'])) ?></p>
                                    <div class="exam-info">
                                        <p>Time Limit: <?= $exam['time_limit_minutes'] ?> minutes</p>
                                        <p>Number of Questions: <?= $exam['question_count'] ?></p>
                                        <p>Start Time: <?= date('Y-m-d H:i', strtotime($exam['start_time'])) ?></p>
                                        <p>Submit Time: <?= date('Y-m-d H:i', strtotime($exam['submit_time'])) ?></p>
                                    </div>
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