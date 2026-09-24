<?php

$conn = mysqli_connect("localhost","root","","test");

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM users WHERE id=$id");

header("Location: index.php");

?>