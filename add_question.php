<?php

$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

$show_success = false;
$error_message = "";

if (isset($_POST['go_teacher'])) {
    header("Location: teacher.php");
    exit();
}

if (isset($_POST['add_question'])) {

    $question = trim($_POST['question']);
    $options = $_POST['options'] ?? [];
    $correct = $_POST['correct_option'] ?? [];

    if ($question === "") {
        $error_message = "Question text is required";
    } elseif (count($options) < 2) {
        $error_message = "At least two options are required";
    } elseif (count($correct) < 1) {
        $error_message = "Please select at least one correct option";
    } else {
        $question_safe = mysqli_real_escape_string($link, $question);
        $insert_question = "INSERT INTO QuestionBank (teacher_id, question_text) VALUES (2, '$question_safe')";
        if (mysqli_query($link, $insert_question)) {
            $question_id = mysqli_insert_id($link);
            foreach ($options as $i => $opt) {
                $opt_safe = mysqli_real_escape_string($link, $opt);
                $is_correct = in_array($i, $correct) ? 1 : 0;
                $insert_option = "INSERT INTO QuestionOptions (question_id, option_text, option_order, is_correct)
                                  VALUES ($question_id, '$opt_safe', $i+1, $is_correct)";
                mysqli_query($link, $insert_option);
            }
            $show_success = true;
        } else {
            $error_message = "Error saving question: " . mysqli_error($link);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Add Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style_add_question.css" rel="stylesheet">

</head>

<body>
    <div class="container">
        <div class="box">

            <h3 class="text-primary mb-4">Add New Question</h3>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <?php if (!$show_success): ?>
                <form method="POST" id="questionForm">
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea name="question" class="form-control" required><?= $_POST['question'] ?? "" ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Options</label>
                        <div id="optionsContainer">
                            <?php
                            $prev_options = $_POST['options'] ?? ["", ""];
                            foreach ($prev_options as $i => $opt):
                                $checked = (isset($_POST['correct_option']) && in_array($i, $_POST['correct_option'])) ? "checked" : "";
                            ?>
                                <div class="input-group mb-2">
                                    <div class="input-group-text">
                                        <input type="checkbox" name="correct_option[]" value="<?= $i ?>" <?= $checked ?>>
                                    </div>
                                    <input type="text" name="options[]" class="form-control" placeholder="Option <?= $i + 1 ?>" value="<?= htmlspecialchars($opt) ?>" required>
                                    <?php if ($i >= 2): ?>
                                        <button type="button" class="btn btn-danger removeOption">Remove</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" id="addOptionBtn">Add Option</button>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="submit" name="add_question" class="btn btn-success flex-fill">Save Question</button>
                        <a href="teacher.php" class="btn btn-secondary flex-fill">Go to Main Page</a>
                    </div>
                </form>

            <?php else: ?>
                <div class="alert alert-success">Question saved successfully</div>
                <form method="POST" class="d-flex gap-2">
                    <button name="add_new" class="btn btn-primary w-50">Add Another Question</button>
                    <button name="go_teacher" class="btn btn-secondary w-50">Go to Main Page</button>
                </form>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="JS/script_add_question.js">
       
    </script>
</body>

</html>