<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kutnik_gallery");

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if (isset($_SESSION['user_id']) && $_SERVER["REQUEST_METHOD"] == "POST" ) {
    $user_id = $_SESSION['user_id'];
    $biogram = $_POST['biogram'];
    $plec = $_POST['plec'];
    
    // Domyślna ścieżka do zdjęcia
    $profile_img = 'assets/uploads/icon-profile-null.png';

    // Sprawdzamy, czy użytkownik wybrał zdjęcie
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        // Ustalamy ścieżkę zapisu pliku
        $target_dir = "assets/uploads/"; // Nowy folder docelowy
        $profile_img_path = $target_dir . basename($_FILES['profile_img']['name']);

        // Przenosimy plik do folderu 'assets/uploads'
        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $profile_img_path)) {
            $profile_img = $profile_img_path;
        }
    }

    $sql = "UPDATE users SET biogram = ?, plec = ?, profile_img = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $biogram, $plec, $profile_img, $user_id);

    if ($stmt->execute()) {
        echo "<script> window.location.href = 'login.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $stmt->close();
    $conn->close();
}

?>
