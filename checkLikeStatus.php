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

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$sql = "SELECT 1 FROM post_likes WHERE user_id = ? AND post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $postId);
$stmt->execute();
$stmt->store_result();

$isLiked = $stmt->num_rows > 0; // Jeśli znaleziono rekord, oznacza to, że użytkownik polubił post

echo json_encode(['isLiked' => $isLiked]);

$stmt->close();
$conn->close();
?>
