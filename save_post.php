<?php
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $user_id = $data->user_id;
    $image_url = $data->image_url;
    $opis = $data->opis;

    // Połączenie z bazą danych
    $mysqli = new mysqli("localhost", "root", "", "kutnik_gallery");
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Wstawienie danych do tabeli posts
    $stmt = $mysqli->prepare("INSERT INTO posts (user_id, image_url, opis) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $image_url, $opis);

    if ($stmt->execute()) {

        echo json_encode(["success" => true]);
    } else {
        // Zwrócenie odpowiedzi w formacie JSON
        echo json_encode(["success" => false]);
    }

    $stmt->close();
    $mysqli->close();
}
?>
