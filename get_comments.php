<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "kutnik_gallery");

if ($mysqli->connect_error) {
    die("Błąd połączenia: " . $mysqli->connect_error);
}

// Odbieranie post_id z GET
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

$query = "SELECT c.tresc_komentarza, u.login, u.profile_img FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode($comments);

$stmt->close();
$mysqli->close();
?>