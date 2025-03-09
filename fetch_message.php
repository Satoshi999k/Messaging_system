<?php
session_start();
include "db.php"; // Make sure this correctly connects to your database

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

if ($receiver_id == 0) {
    echo json_encode(["error" => "Receiver ID is missing"]);
    exit();
}

$query = "SELECT m.*, u.username AS sender_name 
          FROM messages m
          JOIN users u ON m.sender_id = u.id
          WHERE (m.sender_id = ? AND m.receiver_id = ?) 
             OR (m.sender_id = ? AND m.receiver_id = ?)
          ORDER BY m.timestamp ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "sender" => $row['sender_name'],
        "message" => $row['message']
    ];
}

echo json_encode($messages);
?>
