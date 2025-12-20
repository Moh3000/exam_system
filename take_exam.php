<?php



$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jerusalem');

$student_id = 1;

if (!isset($_GET['se_id'])) {
    die("Invalid request");
}
$student_exam_id = intval($_GET['se_id']);

$examRes = mysqli_query($link, "
    SELECT 
        se.*, 
        e.ExamID,
        e.title,
        e.description,
        e.time_limit_minutes,
        e.random_order
    FROM StudentExams se
    JOIN Exams e ON se.exam_id = e.ExamID
    WHERE se.id = $student_exam_id
      AND se.student_id = $student_id
      AND se.status = 'in_progress'
    LIMIT 1
");

if (mysqli_num_rows($examRes) == 0) {
    die("Exam not available.");
}

$exam = mysqli_fetch_assoc($examRes);


$now   = time();
$start = strtotime($exam['start_time']);
$end   = strtotime($exam['submit_time']);

if ($now < $start || $now > $end) {
    die("Exam time is invalid.");
}


if (isset($_POST['submit_exam']) || isset($_POST['auto_submit'])) {

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'option_') === 0) {
            $question_id = intval(str_replace('option_', '', $key));
            $option_id   = intval($value);

            $optRes = mysqli_query($link, "
                SELECT is_correct
                FROM QuestionOptions
                WHERE option_id = $option_id
                  AND question_id = $question_id
                LIMIT 1
            ");

            $is_correct = 0;
            if ($opt = mysqli_fetch_assoc($optRes)) {
                $is_correct = (int)$opt['is_correct'];
            }

            mysqli_query($link, "
                INSERT INTO StudentAnswers (student_exam_id, question_id, option_id, is_correct)
                VALUES ($student_exam_id, $question_id, $option_id, $is_correct)
                ON DUPLICATE KEY UPDATE
                    option_id = VALUES(option_id),
                    is_correct = VALUES(is_correct),
                    answered_at = NOW()
            ");
        }
    }

    $scoreRes = mysqli_query($link, "
        SELECT SUM(eq.points) AS total_score
        FROM StudentAnswers sa
        JOIN ExamQuestions eq ON sa.question_id = eq.question_id
        WHERE sa.student_exam_id = $student_exam_id
          AND sa.is_correct = 1
          AND eq.ExamID = {$exam['ExamID']}
    ");

    $row   = mysqli_fetch_assoc($scoreRes);
    $score = $row['total_score'] ?? 0;

    mysqli_query($link, "
        UPDATE StudentExams
        SET status = 'submitted',
            score = $score,
            submit_time = NOW()
        WHERE id = $student_exam_id
    ");

    header("Location: student.php");
    exit;
}


$orderClause = ($exam['random_order'] == 1)
    ? "ORDER BY RAND()"
    : "ORDER BY eq.sort_order";

$questionsRes = mysqli_query($link, "
    SELECT 
        q.question_id,
        q.question_text,
        eq.points
    FROM ExamQuestions eq
    JOIN QuestionBank q ON eq.question_id = q.question_id
    WHERE eq.ExamID = {$exam['ExamID']}
    $orderClause
");


$remaining_seconds = $end - time();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($exam['title']) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style_take_exam.css" rel="stylesheet">
</head>

<body>

    <div class="container">
        <div class="box">

            <h3><?= htmlspecialchars($exam['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($exam['description'])) ?></p>
            <p> Time Remaining: <span id="timer"></span></p>
            <hr>

            <form method="POST" id="examForm">

                <?php while ($q = mysqli_fetch_assoc($questionsRes)): ?>

                    <?php
                    $ansRes = mysqli_query($link, "
    SELECT option_id
    FROM StudentAnswers
    WHERE student_exam_id = $student_exam_id
      AND question_id = {$q['question_id']}
    LIMIT 1
");
                    $selected = mysqli_fetch_assoc($ansRes)['option_id'] ?? null;

                    $optionsRes = mysqli_query($link, "
    SELECT option_id, option_text
    FROM QuestionOptions
    WHERE question_id = {$q['question_id']}
    ORDER BY option_order
");
                    ?>

                    <div class="question">
                        <p>
                            <strong><?= htmlspecialchars($q['question_text']) ?></strong>
                            <span class="badge points-badge"><?= $q['points'] ?> pts</span>
                        </p>

                        <?php while ($opt = mysqli_fetch_assoc($optionsRes)): ?>
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="radio"
                                    name="option_<?= $q['question_id'] ?>"
                                    value="<?= $opt['option_id'] ?>"
                                    <?= $selected == $opt['option_id'] ? 'checked' : '' ?>>
                                <label class="form-check-label">
                                    <?= htmlspecialchars($opt['option_text']) ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>

                <?php endwhile; ?>

                <input type="hidden" name="submit_exam">
                <input type="hidden" name="auto_submit">

                <button type="submit" class="btn btn-danger px-5">
                    Submit Final Answers
                </button>

            </form>

        </div>
    </div>

    <script>
        let remaining = <?= $remaining_seconds ?>;
        const timerEl = document.getElementById('timer');
        const examForm = document.getElementById('examForm');

        function updateTimer() {
            if (remaining <= 0) {
                timerEl.textContent = "00:00";
                examForm.auto_submit.value = 1;
                examForm.submit();
                return;
            }
            let m = Math.floor(remaining / 60);
            let s = remaining % 60;
            timerEl.textContent =
                m.toString().padStart(2, '0') + ':' +
                s.toString().padStart(2, '0');
            remaining--;
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    </script>

</body>

</html>