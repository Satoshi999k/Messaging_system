<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="chat-container">
        <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
        
        <label for="receiver">Send to:</label>
        <select id="receiver">
        </select>

        <div id="chat-box"></div>

        <input type="text" id="message" placeholder="Type a message">
        <button id="send">Send</button>
    </div>

    <script>
       function loadMessages() {
    let receiver_id = $("#receiver").val();
    if (!receiver_id) {
        console.warn("No recipient selected.");
        return;
    }

    console.log("Requesting messages for receiver:", receiver_id); // Debugging log

    $.get("fetch_message.php", { receiver_id: receiver_id }, function(data) {
        console.log("Server response:", data); // Debugging log

        let chatBox = $("#chat-box");
        chatBox.html(""); // Clear chat box

        try {
            let messages = JSON.parse(data);
            messages.forEach(msg => {
                let messageClass = (msg.sender === "<?php echo $_SESSION['username']; ?>") ? "sent" : "received";
                chatBox.append(`<p class="message ${messageClass}"><strong>${msg.sender}:</strong> ${msg.message}</p>`);
            });

            chatBox.scrollTop(chatBox[0].scrollHeight);
        } catch (error) {
            console.error("Error parsing JSON:", error, data);
        }
    }).fail(function(xhr, status, error) {
        console.error("AJAX error:", error);
    });
}

// Load messages when user selects a recipient
$("#receiver").change(function() {
    let receiver_id = $(this).val();
    loadMessages(receiver_id); // Load messages when a recipient is selected
});

function loadUsers() {
    $.get("fetch_users.php", function(data) {
        let users = JSON.parse(data);
        let receiver = $("#receiver");
        receiver.html("");
        users.forEach(user => {
            receiver.append(`<option value="${user.id}">${user.username}</option>`);
        });

        // Automatically load messages for the first user in the list
        if (users.length > 0) {
            $("#receiver").val(users[0].id).change();
        }
    });
}

$(document).ready(function() {
    loadUsers();

    // Wait for users to load, then load messages for the first available user
    setTimeout(function() {
        let firstReceiver = $("#receiver").val();
        if (firstReceiver) {
            loadMessages(firstReceiver);
        }
    }, 500);

    setInterval(function() {
        let receiver_id = $("#receiver").val();
        if (receiver_id) {
            loadMessages(receiver_id);
        }
    }, 3000);

    $("#send").click(function() {
        let message = $("#message").val();
        let receiver_id = $("#receiver").val();
        if (message.trim() !== "" && receiver_id) {
            $.post("send_message.php", { receiver_id, message }, function() {
                $("#message").val("");
                loadMessages(receiver_id);
            });
        }
    });
});

    </script>
</body>
</html>