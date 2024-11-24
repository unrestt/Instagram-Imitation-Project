<?php
session_start();
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Przekazanie user_id z sesji
if ($userId === null) {
    echo json_encode(['status' => 'error', 'message' => 'Użytkownik nie jest zalogowany']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kutnik_gallery";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$change = isset($_POST['change']) ? (int)$_POST['change'] : 0;

// Sprawdzenie, czy użytkownik już polubił post
if ($change == 1) { // Polubienie
    $stmt = $conn->prepare("INSERT INTO post_likes (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
} elseif ($change == -1) { // Odpolubienie
    $stmt = $conn->prepare("DELETE FROM post_likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
}

// Zaktualizowanie liczby polubień w tabeli posts
$sql = "UPDATE posts SET licznik_polubień = (SELECT COUNT(*) FROM post_likes WHERE post_id = ?) WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $postId, $postId);
$stmt->execute();

echo json_encode(['status' => 'success']);
$stmt->close();
$conn->close();
?>
