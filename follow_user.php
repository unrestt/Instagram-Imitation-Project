<?php
session_start(); // Załóżmy, że identyfikator aktualnego użytkownika jest przechowywany w sesji

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit();
}

$currentUserId = $_SESSION['user_id'];

if (isset($_GET['follow_user_id'])) {
    $followUserId = (int)$_GET['follow_user_id'];

    // Połączenie z bazą danych
    $conn = new mysqli("localhost", "root", "", "kutnik_gallery");
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    }

    // Sprawdzenie, czy użytkownik już obserwuje
    $checkQuery = "SELECT * FROM relacje_obserwacji WHERE id_obserwujacy = ? AND id_obserwowany = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $currentUserId, $followUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Użytkownik już obserwuje
        echo 'already_followed';
    } else {
        // Dodaj nową obserwację
        $insertQuery = "INSERT INTO relacje_obserwacji (id_obserwujacy, id_obserwowany) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $currentUserId, $followUserId);
        if ($stmt->execute()) {
            echo 'followed';
        } else {
            echo 'error';
        }
    }

    $stmt->close();
    $conn->close();
}
?>
