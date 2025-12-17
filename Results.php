<?php
session_start();

$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

$student_id = 1;

$query = "
    SELECT 
        se.id AS attempt_id,
        e.ExamID,
        e.title AS exam_name,
        se.score AS earned_score,
        se.submit_time,
        (SELECT SUM(points) FROM ExamQuestions WHERE ExamID = e.ExamID) AS total_max_points
    FROM StudentExams se
    JOIN Exams e ON se.exam_id = e.ExamID
    WHERE se.student_id = ? 
      AND se.status = 'submitted'
    ORDER BY se.submit_time DESC
";

$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Exam Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style_Results.css" rel="stylesheet">

</head>

<body>

    <div class="container">
        <div class="card card-results p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="m-0">Exam Results</h2>
                <a href="student.php" class="btn btn-primary">Back to Main Page</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Exam Title</th>
                            <th>Submission Date</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($results)):
                            $earned = (float)$row['earned_score'];
                            $total  = (float)$row['total_max_points'];

                    
                            $percentage = ($total > 0) ? ($earned / $total) * 100 : 0;
                            $status = ($percentage >= 50) ? 'Passed' : 'Failed';
                            $class = ($percentage >= 50) ? 'pass' : 'fail';
                            $bg_class = ($percentage >= 50) ? 'bg-success' : 'bg-danger';
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['exam_name']) ?></strong></td>
                                <td><?= date('M d, Y - h:i A', strtotime($row['submit_time'])) ?></td>
                                <td class="score-box <?= $class ?>">
                                    <?= number_format($earned, 2) ?> / <?= number_format($total, 2) ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar <?= $bg_class ?>" style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        <span><?= round($percentage) ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $bg_class ?>"><?= $status ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>