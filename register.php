<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kutnik_gallery");


if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
          echo "<script>window.location.href = 'register_step2.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
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
                <b><p id="text-top">REJESTRACJA</p></b>
            </div>
            <div class="register-container" id="register-container-1">
              <form method="post" id="register_form" action="register_step1.php">
                <input type="text" name="login" placeholder="Login" required><br>
                <input type="text" name="imie" placeholder="Imię" required><br>
                <input type="text" name="nazwisko" placeholder="Nazwisko" required><br>
                <input type="email" name="email" placeholder="E-mail" required><br>
                <div class="password-things">
                  <input type="password" name="password" placeholder="Hasło" required id="password_input"><br>
                  <div class="eyes">
                    <i class="fa-solid fa-eye-slash" id="password_view_change"></i>
                  </div>
                </div>
                <button type="submit" id="button_register">Zarejestruj się</button>
              </form>
              <div class="register-link">
                <a href="login.php">Masz już konto? Zaloguj się</a><br><br>
                <b><p id="register-message"><?php echo htmlspecialchars($error_message); ?></p></b>
            </div>
          
            </div>

            <div class="register-container-after-reg" id="register-container-2" style="display: none;">
            <form action="register_step2_handler.php" method="post" enctype="multipart/form-data">
              <div class="reg-columns">
                  <div class="reg-column-left">
                    <p>Wybierz zdjęcie profilowe</p>
                    <p class="optional-text">(opcjonalnie)</p>
                    <div class="img-temp"><img src="assets/img/icon-profile-null.png" id="img-profile-change"></div>
                    <label for="profile_img"><div class="choose-img" id="choose-img">
                      <i class="fa-regular fa-image"></i>
                      <p>Upuść zdjęcie tutaj lub <span id="choose-file">wybierz z urządzenia</span></p>
                      <p>.jpg, .jpeg, .png</p>
                      <input type="file" id="profile_img" name="profile_img" accept=".jpg,.jpeg,.png" hidden />
                    </div></label>

                    </div>
                    <div class="reg-column-right">
                    <div class="text-reg">
                    <p class="reg-column-right-text">Ustaw biogram</p><p class="optional-text">(opcjonalnie)</p>
                    </div>
                    <textarea name="biogram" maxlength="150" placeholder="Moje zainteresowania to..."></textarea>
                    <div class="text-reg">
                      <p class="reg-column-right-text">Wybierz Płeć</p><p class="optional-text">(opcjonalnie)</p>
                    </div>
                    <select name="plec">
                      <option value=""></option>
                      <option value="Kobieta">Kobieta</option>
                      <option value="Mężczyzna">Mężczyzna</option>
                    </select>
                    </div>
              </div>

                  <button id="button_register_2">Dalej</button>
                  </form>
            </div>







            

        </div>
       
       
    </main>


<script src="assets/js/register-login.js"></script>
<script src="assets/js/register.js"></script>
<script src="https://kit.fontawesome.com/70f2470b08.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>
</html>