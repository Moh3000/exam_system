<?php
session_start();


$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

$student_id = 1;

if (!isset($_GET['attempt_id'])) {
    die("Invalid request");
}

$attempt_id = intval($_GET['attempt_id']);


$examRes = mysqli_query($link, "
    SELECT 
        se.id,
        se.score AS final_score,
        e.ExamID,
        e.title,
        e.description
    FROM StudentExams se
    JOIN Exams e ON se.exam_id = e.ExamID
    WHERE se.id = $attempt_id
      AND se.student_id = $student_id
      AND se.status = 'submitted'
    LIMIT 1
");

if (mysqli_num_rows($examRes) == 0) {
    die("Exam not found.");
}

$exam = mysqli_fetch_assoc($examRes);


$questionsRes = mysqli_query($link, "
    SELECT 
        q.question_id,
        q.question_text,
        eq.points
    FROM ExamQuestions eq
    JOIN QuestionBank q ON eq.question_id = q.question_id
    WHERE eq.ExamID = {$exam['ExamID']}
    ORDER BY eq.sort_order
");


$total_max = 0;
$total_earned = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($exam['title']) ?> - Review</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="css/style_exam_review.css" rel="stylesheet">

</head>

<body>

    <div class="container">
        <div class="box">

            <h3><?= htmlspecialchars($exam['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($exam['description'])) ?></p>

            <a href="results.php" class="btn btn-secondary mb-3">⬅ Back to Results</a>
            <hr>

            <?php while ($q = mysqli_fetch_assoc($questionsRes)): ?>

                <?php
                $total_max += $q['points'];


                $ansRes = mysqli_query($link, "
    SELECT option_id, is_correct
    FROM StudentAnswers
    WHERE student_exam_id = $attempt_id
      AND question_id = {$q['question_id']}
    LIMIT 1
");

                $ansRow = mysqli_fetch_assoc($ansRes);
                $student_option = $ansRow['option_id'] ?? null;
                $is_correct = $ansRow['is_correct'] ?? 0;

                $earned_points = $is_correct ? $q['points'] : 0;
                $total_earned += $earned_points;


                $optionsRes = mysqli_query($link, "
    SELECT option_id, option_text, is_correct
    FROM QuestionOptions
    WHERE question_id = {$q['question_id']}
    ORDER BY option_order
");
                ?>

                <div class="mb-4">

                    <h5>
                        <?= htmlspecialchars($q['question_text']) ?>
                        <span class="points">(<?= $q['points'] ?> pts)</span>

                        <span class="badge bg-<?= $earned_points > 0 ? 'success' : 'danger' ?> score-badge ms-2">
                            <?= $earned_points ?> / <?= $q['points'] ?>
                        </span>
                    </h5>

                    <?php while ($opt = mysqli_fetch_assoc($optionsRes)): ?>

                        <?php
                        $class = '';
                        if ($opt['is_correct']) {
                            $class = 'correct';
                        } elseif ($student_option == $opt['option_id']) {
                            $class = 'wrong';
                        }
                        ?>

                        <div class="option <?= $class ?>">
                            <?= htmlspecialchars($opt['option_text']) ?>
                        </div>

                    <?php endwhile; ?>

                </div>

            <?php endwhile; ?>

            <hr>

            <h4 class="text-end">
                Final Score:
                <span class="badge bg-primary">
                    <?= number_format($total_earned, 2) ?> / <?= number_format($total_max, 2) ?>
                </span>
            </h4>

        </div>
    </div>

</body>

</html>