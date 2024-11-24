<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "kutnik_gallery");

if ($mysqli->connect_error) {
    die("Błąd połączenia: " . $mysqli->connect_error);
}

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nie jesteś zalogowany']);
    exit();
}

// Odbieranie danych z POST
$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$postId = $data['post_id'];
$comment = $data['comment'];

// Wstawianie komentarza do bazy danych
$query = "INSERT INTO comments (post_id, user_id, tresc_komentarza) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iis", $postId, $userId, $comment);

if ($stmt->execute()) {
    // Pobieranie loginu użytkownika i zdjęcia profilowego
    $queryUser   = "SELECT login, profile_img FROM users WHERE id = ?";
    $stmtUser   = $mysqli->prepare($queryUser );
    $stmtUser ->bind_param("i", $userId);
    $stmtUser ->execute();
    $resultUser   = $stmtUser ->get_result();

    if ($resultUser ->num_rows > 0) {
        $user = $resultUser ->fetch_assoc();
        $username = $user['login'];
        $profileImg = $user['profile_img']; // Pobieramy zdjęcie profilowe
    } else {
        $username = 'Nieznany użytkownik';
        $profileImg = 'assets/uploads/icon-profile-null.png'; // Domyślne zdjęcie profilowe
    }

    echo json_encode(['success' => true, 'username' => $username, 'profile_img' => $profileImg]);
} else {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas dodawania komentarza']);
}

$stmt->close();
$stmtUser ->close();
$mysqli->close();
?>