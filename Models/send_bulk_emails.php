<?php
session_start();
require_once('../DataBase/DataBase/db_connect.php');

if (!isset($_SESSION['User_ID'])) {
    die("Unauthorized access");
}

$query = "SELECT F_Name, Email FROM user WHERE Role = 'Student'";
$result = mysqli_query($connect, $query);

if ($result) {
    while ($student = mysqli_fetch_assoc($result)) {
        $to = $student['Email'];
        $subject = "Thank you for visiting WorkPortal!";
        $message = "Hi " . $student['F_Name'] . ",\n\n" .
                   "It was a pleasure meeting you at the Spring Career Expo 2026. " .
                   "We have received your profile and are currently reviewing candidates. " .
                   "We will reach out if your background matches our current openings.\n\n" .
                   "Best regards,\n" .
                   "The Recruitment Team";
        
        $headers = "From: recruitment@workportal.com\r\n" .
                   "Reply-To: recruitment@workportal.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // Note: mail() requires a configured SMTP server (like Sendmail or XAMPP Mercury)
        mail($to, $subject, $message, $headers);
    }
    echo "Success";
} else {
    echo "Error fetching students";
}
?>