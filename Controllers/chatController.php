<?php

session_start();

include("../DataBase/db_connect.php");
include("../Models/messages.php");

$messageModel = new messages($connect);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $sender =
    $_SESSION['User_ID'];

    $receiver =
    $_POST['receiver_id'];

    $content =
    trim($_POST['message']);

    $file_path = null;

    // =========================
    // PDF Upload
    // =========================

    if(
        isset($_FILES['pdf_file']) &&
        $_FILES['pdf_file']['error'] == 0
    ){

        $file_name =
        time() . "_" .
        $_FILES['pdf_file']['name'];

        move_uploaded_file(

            $_FILES['pdf_file']['tmp_name'],

            "../uploads/" . $file_name
        );

        $file_path =
        "uploads/" . $file_name;
    }

    // =========================
    // Send Message
    // =========================

    if (
        !empty($content)
        || $file_path != null
    ) {

        $messageModel->sendMessage(

            $sender,
            $receiver,
            $content,
            $file_path

        );

    }

    header("Location: ../Views/student/S_liveSessions.php");

    exit();
}