<?php
$link = mysqli_connect("localhost", "root", "moh4242000", "exam_system");
if (mysqli_connect_errno()) {
    echo "error: " . mysqli_connect_errno();
} else {
    echo "connect <br/>";
}
$sel = "select * from inser";
$run = mysqli_query($link, $sel);

echo "<table border='1'>";
echo "<tr>";
echo "<th>";
echo "id ";
echo "</th>";
echo "<th>";
echo "name ";
echo "</th>";
echo "</tr>";

while ($data = mysqli_fetch_assoc($run)) {
    echo "<tr>";
    echo "<td>";
    echo $data['id'];
    echo "</td>";
    echo "<td>";
    echo $data['name'];
    echo "</td>";
    echo "</tr>";
    //echo "id : ".$data['id']." name is : ".$data['name']."<br/>";
}
echo "</table>";




if (isset($_POST['btn'])) {
    $insert="insert into inser (id, name) values (".$_POST['id'].",'".$_POST['name']."')";  

    // $insert="update inser set name='".$_POST['name']."' where id=".$_POST['id'].""; 
    //$insert = "delete from inser  where id=" . $_POST['id'] . "";
    if (mysqli_query($link, $insert)) {
        header("location:insert.php");
    } else {
        echo "error " . $insert . "<br>" . mysqli_error($link);
    }
}
?>


<form method="POST" action="">
    <input type="text" name="id" />
    <input type="text" name="name" />
    <input type="submit" name="btn" />
</form>