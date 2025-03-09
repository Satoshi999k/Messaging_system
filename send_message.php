<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['receiver_id']) || !isset($_POST['message'])) {
    exit("Error: Missing data");
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message = trim($_POST['message']);

if ($message === "") exit("Error: Empty message");

// Insert message into database
$query = "INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Message Sent!";
} else {
    echo "Error: Message not sent.";
}
?>

