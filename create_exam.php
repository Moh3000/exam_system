<?php
session_start();
$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

$success_message = "";
$error_message = "";

if (isset($_POST['create_exam'])) {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $time_limit = intval($_POST['time_limit']);
    $random_order = isset($_POST['random_order']) ? 1 : 0;

    $selected_q = $_POST['question_id'] ?? [];
    $points = $_POST['points'] ?? [];
    $sort_order = $_POST['sort_order'] ?? [];
    $option_sort = $_POST['option_sort'] ?? [];

    if ($title === "") {
        $error_message = "Exam title is required.";
    } elseif (count($selected_q) < 1) {
        $error_message = "You must select at least one question.";
    } else {
        $title_safe = mysqli_real_escape_string($link, $title);
        $desc_safe  = mysqli_real_escape_string($link, $description);

        $insert_exam = "INSERT INTO Exams (teacher_id, title, description, time_limit_minutes, random_order)
                        VALUES (2, '$title_safe', '$desc_safe', $time_limit, $random_order)";

        if (mysqli_query($link, $insert_exam)) {
            $exam_id = mysqli_insert_id($link);

            foreach ($selected_q as $qid) {
                $p = floatval($points[$qid]);
                $q_sort = intval($sort_order[$qid] ?? 0);

                $insert_q = "INSERT INTO ExamQuestions (ExamID, question_id, points, sort_order)
                             VALUES ($exam_id, $qid, $p, $q_sort)";
                mysqli_query($link, $insert_q);

                if (isset($option_sort[$qid])) {
                    foreach ($option_sort[$qid] as $optid => $opt_order) {
                        $opt_order_int = intval($opt_order);
                        $upd_sql = "UPDATE QuestionOptions
                                    SET option_order = $opt_order_int
                                    WHERE option_id = $optid";
                        mysqli_query($link, $upd_sql);
                    }
                }
            }

            $success_message = "Exam created successfully.";
        } else {
            $error_message = "Error creating exam: " . mysqli_error($link);
        }
    }
}

$sql = "SELECT q.question_id, q.question_text,
               o.option_id, o.option_text, o.is_correct
        FROM QuestionBank q
        LEFT JOIN QuestionOptions o ON q.question_id = o.question_id
        ORDER BY q.question_id, o.option_order";

$res = mysqli_query($link, $sql);

$questions = [];
while ($row = mysqli_fetch_assoc($res)) {
    $qid = $row['question_id'];

    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'text' => $row['question_text'],
            'options' => []
        ];
    }

    if ($row['option_id']) {
        $questions[$qid]['options'][] = [
            'id' => $row['option_id'],
            'text' => $row['option_text'],
            'is_correct' => $row['is_correct']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Create Exam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js" rel="stylesheet" />
    <link href="css/style_create_exam.css" rel="stylesheet">

</head>

<body>
    <div class="container">
        <div class="box">
            <h2 class="text-primary mb-4">Create New Exam</h2>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
                <a href="teacher.php" class="btn btn-secondary mt-3 w-100">Return to Main Page</a>
            <?php else: ?>

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Exam Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Time Limit (minutes)</label>
                        <input type="number" name="time_limit" class="form-control" value="60" min="1">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="random_order" id="random_order">
                        <label class="form-check-label" for="random_order">Randomize Question Order</label>
                    </div>

                    <hr>
                    <h4 class="mt-4 mb-3">Select Questions</h4>

                    <div id="questionsContainer">
                        <?php foreach ($questions as $qid => $q): ?>
                            <div class="question-row" data-qid="<?= $qid ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="drag-handle">☰</span>
                                        <input type="checkbox" name="question_id[]" value="<?= $qid ?>">
                                        <a data-bs-toggle="collapse" href="#q<?= $qid ?>" style="text-decoration:none;">
                                            <b><?= htmlspecialchars($q['text']) ?></b></a>
                                    </div>
                                    <div style="width: 120px;">
                                        <input type="number" name="points[<?= $qid ?>]" class="form-control" value="1" min="0.25" step="0.25">
                                    </div>
                                </div>

                                <div class="collapse mt-2" id="q<?= $qid ?>">
                                    <ul class="options-list" data-qid="<?= $qid ?>">
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <li class="option-item" data-optid="<?= $opt['id'] ?>">
                                                <span class="drag-handle">↕</span>
                                                <?= htmlspecialchars($opt['text']) ?>
                                                <?php if ($opt['is_correct']): ?>
                                                    <span class="option-correct">(correct)</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" name="create_exam" class="btn btn-success w-100 mt-4">Save Exam</button>
                    <a href="teacher.php" class="btn btn-secondary w-100 mt-2">Cancel and Return</a>

                </form>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="JS/script_create_exam.JS">

    </script>
</body>

</html>