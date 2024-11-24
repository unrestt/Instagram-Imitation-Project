<?php
session_start(); // Upewniamy się, że sesja jest aktywna

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit();
}

$currentUserId = $_SESSION['user_id'];

if (isset($_GET['unfollow_user_id'])) {
    $unfollowUserId = (int)$_GET['unfollow_user_id'];

    // Połączenie z bazą danych
    $conn = new mysqli("localhost", "root", "", "kutnik_gallery");
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    }

    // Usunięcie obserwacji
    $deleteQuery = "DELETE FROM relacje_obserwacji WHERE id_obserwujacy = ? AND id_obserwowany = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("ii", $currentUserId, $unfollowUserId);
    
    if ($stmt->execute()) {
        echo 'unfollowed';
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
}
?>