<?php
session_start(); // Rozpocznij sesję

$conn = new mysqli("localhost", "root", "", "kutnik_gallery");

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];


    $sql = "SELECT * FROM users WHERE login = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Nieprawidłowy login lub hasło";

        }
    } else {
        $error_message = "Nieprawidłowy login lub hasło";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kutnigram</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/edit-profile.css">
    <link rel="stylesheet" href="assets/css/login-register.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
</head>
<body>
    <main>
        <div class="container-login">
            <div class="text-top">
                <div class="nav-logo-login-reg">
                    <img src="assets/img/logo/logo.png" alt="logo">
                    <h1>GRAM</h1>
                </div>
                <b><p id="text-top">LOGIN</p></b>
            </div>
            <div class="login-container">
                <form method="post" id="login_form">
                    <input type="text" name="login" placeholder="Login" required><br>
                    <div class="password-things">
                    <input type="password" name="password" placeholder="Hasło" id="password_input" required><br>
                    <div class="eyes">
                    <i class="fa-solid fa-eye-slash" id="password_view_change"></i>
                    </div>
                    </div>
                    
                    <button type="submit" id="button_login">Zaloguj się</button>
                    </form>
                
                <div class="login-link">
                    <a href="register.php">Nie masz konta? Zarejestruj się</a><br><br>
                    <b><p id="register-message"><?php echo htmlspecialchars($error_message); ?></p></b>
                    </div>
            </div>

        </div>
    </main>


<script src="assets/js/register-login.js"></script>
<script src="https://kit.fontawesome.com/70f2470b08.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>
</html>