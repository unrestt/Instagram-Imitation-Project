<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kutnik_gallery");

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
    $login = $_POST['login'];
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "SELECT * FROM users WHERE login = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo $error_message = "Istnieje juz konto o takim loginie.";
    } else {
        $sql = "INSERT INTO users (login, imie, nazwisko, email, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $login, $imie, $nazwisko, $email, $password);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            echo "success";
        } else {
            echo "Błąd rejestracji. Spróbuj ponownie.";
        }
        
    }
    $stmt->close();
    $conn->close();
}
?>