<?php
header('Content-Type: application/json');

session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sprawdzanie czy plik jest przesyłany
    if (isset($_FILES['file'])) {
        $targetDir = "assets/uploads_posts/";
        $targetFile = $targetDir . basename($_FILES["file"]["name"]);
        
        // Sprawdzanie typu pliku
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
            // Zwrócenie ścieżki do pliku w formacie JSON
            echo json_encode(["success" => true, "filePath" => $targetFile]);
        } else {
            echo json_encode(["success" => false]);
        }
    }
}
?>
