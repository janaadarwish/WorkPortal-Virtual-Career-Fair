<?php
session_start();
require_once('../DataBase/DataBase/db_connect.php');

$recruiter_id = $_SESSION['User_ID'];

// Get company context
$res = mysqli_query($connect, "SELECT c.Company_Name, c.Company_ID FROM recruiter r JOIN company c ON r.Company_ID = c.Company_ID WHERE r.Recruiter_ID = '$recruiter_id'");
$company = mysqli_fetch_assoc($res);
$c_name = $company['Company_Name'];
$c_id = $company['Company_ID'];

// Fetch interviewed students
$sql = "SELECT u.F_Name, u.Email 
        FROM queue q 
        JOIN user u ON q.Student_ID = u.User_ID 
        WHERE q.Company_ID = ? AND q.Status = 'Interviewed'";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $c_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$count = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $to = $row['Email'];
    $name = $row['F_Name'];
    $subject = "Great meeting you! - $c_name";
    $msg = "Hi $name, thank you for stopping by our booth today at the expo!";
    $headers = "From: careers@workportal.com";

    if(mail($to, $subject, $msg, $headers)) {
        $count++;
    }
}

echo json_encode(['success' => true, 'message' => "Successfully sent $count thank-you emails!"]);