<?php
session_start();
$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

$alert_msg = '';
if (isset($_POST['change_state'])) {
    $exam_id = intval($_POST['exam_id']);
    $new_state = $_POST['new_state'] === 'published' ? 'published' : 'draft';
    $start_time_input = $_POST['start_time'] ?? null;

    if ($new_state === 'published') {
        $start_time = $start_time_input ? date('Y-m-d H:i:s', strtotime($start_time_input)) : date('Y-m-d H:i:s');

        $exam_res = mysqli_query($link, "SELECT time_limit_minutes FROM Exams WHERE ExamID=$exam_id");
        $exam_row = mysqli_fetch_assoc($exam_res);
        $time_limit = intval($exam_row['time_limit_minutes']);
        $submit_time = date('Y-m-d H:i:s', strtotime("+$time_limit minutes", strtotime($start_time)));

        $students = mysqli_query($link, "SELECT UserID FROM Users WHERE role='student'");
        $conflict_found = false;


        while ($student = mysqli_fetch_assoc($students)) {
            $sid = $student['UserID'];

            $conflict_check = mysqli_query($link, "
                SELECT * FROM StudentExams 
                WHERE student_id = $sid 
                  AND ((start_time <= '$start_time' AND submit_time > '$start_time')
                       OR (start_time < '$submit_time' AND submit_time >= '$submit_time')
                       OR (start_time >= '$start_time' AND submit_time <= '$submit_time'))
            ");

            if (mysqli_num_rows($conflict_check) > 0) {
                $conflict_found = true;
                break;
            }
        }

        if ($conflict_found) {
            $alert_msg = "Cannot publish exam because some students have overlapping exams.";
            $new_state = 'draft';
        } else {
            $students_to_insert = mysqli_query($link, "SELECT UserID FROM Users WHERE role='student'");
            while ($student = mysqli_fetch_assoc($students_to_insert)) {
                $sid = $student['UserID'];
                mysqli_query($link, "INSERT INTO StudentExams (student_id, exam_id, start_time, submit_time) 
                                     VALUES ($sid, $exam_id, '$start_time', '$submit_time')
                                     ON DUPLICATE KEY UPDATE start_time='$start_time', submit_time='$submit_time'");
            }
        }
    }

    mysqli_query($link, "UPDATE Exams SET status='$new_state' WHERE ExamID=$exam_id");
}

$exams = mysqli_query($link, "SELECT * FROM Exams ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Exams List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php if ($alert_msg): ?>
        <script>
            alert("<?= $alert_msg ?>");
        </script>
    <?php endif; ?>

    <div class="container">
        <div class="box">
            <h2 class="text-primary mb-4">Exams</h2>
            <a href="teacher.php" class="btn btn-secondary mb-3">Return to Main Page</a>

            <?php if (mysqli_num_rows($exams) === 0): ?>
                <p>No exams found.</p>
            <?php else: ?>
                <div class="accordion" id="examAccordion">
                    <?php while ($exam = mysqli_fetch_assoc($exams)): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $exam['ExamID'] ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $exam['ExamID'] ?>" aria-expanded="false">
                                    <?= htmlspecialchars($exam['title']) ?> - <small><?= $exam['status'] ?></small>
                                </button>
                            </h2>
                            <div id="collapse<?= $exam['ExamID'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $exam['ExamID'] ?>" data-bs-parent="#examAccordion">
                                <div class="accordion-body">
                                    <p><b>Description:</b> <?= nl2br(htmlspecialchars($exam['description'])) ?></p>
                                    <p><b>Time Limit:</b> <?= $exam['time_limit_minutes'] ?> minutes</p>

                                    <form method="POST" class="mb-3">
                                        <input type="hidden" name="exam_id" value="<?= $exam['ExamID'] ?>">

                                        <label for="start_time_<?= $exam['ExamID'] ?>" class="form-label">Start Time:</label>
                                        <input type="datetime-local" id="start_time_<?= $exam['ExamID'] ?>" name="start_time" class="form-control mb-2"
                                            value="<?= date('Y-m-d\TH:i', strtotime($exam['created_at'])) ?>">

                                        <select name="new_state" class="form-select w-auto d-inline mb-2">
                                            <option value="draft" <?= $exam['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="published" <?= $exam['status'] == 'published' ? 'selected' : '' ?>>Published</option>
                                        </select>
                                        <button type="submit" name="change_state" class="btn btn-primary btn-sm">Change State</button>
                                    </form>

                                    <hr>
                                    <h5>Questions</h5>
                                    <?php
                                    $exam_id = $exam['ExamID'];
                                    $questions_sql = "
                                    SELECT eq.points, q.question_text, o.option_text, o.is_correct
                                    FROM ExamQuestions eq
                                    JOIN QuestionBank q ON eq.question_id = q.question_id
                                    JOIN QuestionOptions o ON q.question_id = o.question_id
                                    WHERE eq.ExamID=$exam_id
                                    ORDER BY eq.sort_order, o.option_order
                                ";
                                    $res_q = mysqli_query($link, $questions_sql);
                                    $questions = [];
                                    while ($row = mysqli_fetch_assoc($res_q)) {
                                        $q_text = $row['question_text'];
                                        if (!isset($questions[$q_text])) {
                                            $questions[$q_text] = ['points' => $row['points'], 'options' => []];
                                        }
                                        $questions[$q_text]['options'][] = ['text' => $row['option_text'], 'is_correct' => $row['is_correct']];
                                    }
                                    ?>

                                    <?php foreach ($questions as $q_text => $q): ?>
                                        <div class="mb-3">
                                            <b><?= htmlspecialchars($q_text) ?></b> (Points: <?= $q['points'] ?>)
                                            <ul>
                                                <?php foreach ($q['options'] as $opt): ?>
                                                    <li><?= htmlspecialchars($opt['text']) ?> <?php if ($opt['is_correct']): ?><span class="option-correct">(correct)</span><?php endif; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>

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