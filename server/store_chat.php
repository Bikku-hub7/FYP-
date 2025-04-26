<?php

include('connection.php');
session_start();

// Retrieve chat message from POST
$chat_message = isset($_POST['chat_message']) ? $_POST['chat_message'] : '';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Fetch user name and email from profile in table "users"
    $query = "SELECT user_name, user_email FROM users WHERE user_id = ?";
    $stmtUser = $conn->prepare($query);
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $result = $stmtUser->get_result();
    if($row = $result->fetch_assoc()){
        $user = $row['user_name'];
        $user_email = $row['user_email'];
    } else {
        $user = 'unknown';
        $user_email = '';
    }
    $stmtUser->close();
} else {
    $user = 'guest';
    $user_email = '';
}

// Prepare insert statement with user_email
$stmt = $conn->prepare("INSERT INTO chats (user, user_email, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $user, $user_email, $chat_message);

if ($stmt->execute()) {
    echo "Chat stored successfully.";
} else {
    echo "Error storing chat: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>