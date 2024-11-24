<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Połączenie z bazą danych
$mysqli = new mysqli("localhost", "root", "", "kutnik_gallery");
if ($mysqli->connect_error) {
    die("Błąd połączenia: " . $mysqli->connect_error);
}
$message_password = "";
$message_user_login = "";

// Odbieranie user_id z sesji
$userId = $_SESSION['user_id'];

// Pobieranie danych użytkownika z tabeli 'users'
$query = "SELECT login, profile_img, biogram, plec FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Pobieramy dane użytkownika
    $user = $result->fetch_assoc();
    $login = $user['login'];
    $profileImg = $user['profile_img'];
    $bio = $user['biogram'];
    $gender = $user['plec'];
} else {
    // Użytkownik nie istnieje
    echo "Użytkownik nie znaleziony!";
    exit();
}
$stmt->close();

// Sprawdzamy, czy formularz został wysłany w celu edytowania danych
// Sprawdzamy, czy formularz został wysłany w celu edytowania danych
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aktualizacja loginu (jeśli został podany)
    if (!empty($_POST['login-change'])) {
        $newLogin = trim($_POST['login-change']);

        // Sprawdzanie, czy nowy login jest inny od aktualnego
        if ($newLogin !== $login) {
            // Sprawdzanie, czy nowy login jest już w bazie
            $checkLoginQuery = "SELECT COUNT(*) FROM users WHERE login = ?";
            $stmtCheckLogin = $mysqli->prepare($checkLoginQuery);
            $stmtCheckLogin->bind_param("s", $newLogin);
            $stmtCheckLogin->execute();
            $stmtCheckLogin->bind_result($loginCount);
            $stmtCheckLogin->fetch();
            $stmtCheckLogin->close();

            // Jeśli login już istnieje
            if ($loginCount > 0) {
                $_SESSION['message_login'] = "Podany login jest już zajęty!";
            } else {
                // Jeśli login jest dostępny, zaktualizuj go
                $updateLoginSql = "UPDATE users SET login = ? WHERE id = ?";
                $stmtLogin = $mysqli->prepare($updateLoginSql);
                $stmtLogin->bind_param("si", $newLogin, $userId);
                $stmtLogin->execute();
                $stmtLogin->close();
            }
        }
    }

    // Aktualizacja biogramu i płci
    $newBio = $_POST['bio'];
    $newGender = $_POST['gender'];
    $updateQuery = "UPDATE users SET biogram = ?, plec = ? WHERE id = ?";
    $updateStmt = $mysqli->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $newBio, $newGender, $userId);
    $updateStmt->execute();
    $updateStmt->close();

    // Aktualizacja zdjęcia profilowego (jeśli przesłane)
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $target_dir = "assets/uploads/";
        $profile_img_path = $target_dir . basename($_FILES['profile_img']['name']);
        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $profile_img_path)) {
            $updateImgSql = "UPDATE users SET profile_img = ? WHERE id = ?";
            $stmtImg = $mysqli->prepare($updateImgSql);
            $stmtImg->bind_param("si", $profile_img_path, $userId);
            $stmtImg->execute();
            $stmtImg->close();
        }
    }

    header("Location: edit-profile.php");
    exit();
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
    <link rel="stylesheet" href="assets/css/popup.css">
    <link rel="stylesheet" href="assets/css/edit-profile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
</head>
<body>
    <div class="navigation-bar">
        <div class="navigation">
            <div class="nav-logo">
                <img src="assets/img/logo/logo.png" alt="logo">
                <h1>GRAM</h1>
            </div>
            <nav>
                <ul>
                <li>
                    <a href="index.php"><i class="fa-solid fa-house"></i><p class="nav-text">Home</p></a>
                </li>
                <li id="search-button">
                    <a href="#"><i class="fa-solid fa-magnifying-glass"></i><p class="nav-text">Szukaj</p></a>
                </li>
                <li id="create-post-button">
                    <a href="#"><i class="fa-regular fa-square-plus"></i><p class="nav-text">Utwórz</p></a>
                </li>
                <li>
                    <a href="profile.php?user_id=<?php echo $_SESSION['user_id']; ?>">
                        <div class="img-temp">
                            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Zdjęcie profilowe">
                        </div>
                        <p class="nav-text">Profil</p>
                    </a>
                </li>
                </ul>
            </nav>
            <div class="navigation-bottom">
            <a href="edit-profile.php"><i class="fa-solid fa-gear"></i></a>
                <a href="logut.php"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>  
        </div>
    </div>

    <div class="search-side-panel">
    <div class="side-panel-top">
        <p>Szukaj</p>
        <input type="text" id="search-input" placeholder="Szukaj" onkeyup="searchUsers()">
    </div>
    <div class="side-panel-search-results" id="search-results">
        <!-- Wyniki wyszukiwania pojawią się tutaj -->
    </div>
</div>
   <main>
        <div class="edit-profile-containers">
            <div class="edit-profile-container">
                <p class="edit-profile-text">Edytuj profil</p>
                <div class="change-avatar-nickname-container">
                    <div class="img-temp"><img src="<?php echo htmlspecialchars($profileImg); ?>"></div>
                    <p><?php echo htmlspecialchars($login); ?></p>
                    <button id="show-change-box-nick-img">Zmień</button>
                </div>
                <p class="edit-profile-text-heading">Biogram</p>
                <form method="POST" action="edit-profile.php">
                    <textarea name="bio" maxlength="150"><?php echo htmlspecialchars($bio); ?></textarea>
                    <p class="edit-profile-text-heading">Płeć</p>
                    <select name="gender">
                        <option value="Kobieta" <?php echo ($gender === "kobieta" ? "selected" : ""); ?>>Kobieta</option>
                        <option value="Mężczyzna" <?php echo ($gender === "mężczyzna" ? "selected" : ""); ?>>Mężczyzna</option>
                    </select>
                    <button type="submit" id="save-button">Zapisz</button>
                </form>
            </div>

        
            <?php
if (isset($_SESSION['message_login'])) {
    $message_user_login = $_SESSION['message_login'];
    unset($_SESSION['message_login']);
} else {
  $message_user_login = '';
}
?>
        <div id="popup_login" class="popup <?= !empty($message_user_login) ? '' : 'hidden'; ?>" data-message="<?= htmlspecialchars($message_user_login); ?>">
            <p></p>
        </div>

        </div>

        <div class="background-change" id="background-change" style="display: none;">
        <div class="edit-profile-login-img-change" id="edit-profile-login-img-change" style="display: flex">
            <form method="post" enctype="multipart/form-data">
              <div class="reg-columns">
                  <div class="reg-column-left">
                    <p>Wybierz nowe zdjęcie profilowe</p>
                    <div class="img-temp"><img src="<?php echo htmlspecialchars($profileImg); ?>" id="img-profile-change"></div>
                    <label for="profile_img"><div class="choose-img" id="choose-img">
                      <i class="fa-regular fa-image"></i>
                      <p>Upuść zdjęcie tutaj lub <span id="choose-file">wybierz z urządzenia</span></p>
                      <p>.jpg, .jpeg, .png</p>
                      <input type="file" id="profile_img" name="profile_img" accept=".jpg,.jpeg,.png" hidden />
                    </div></label>

                    </div>
                    <div class="reg-column-right">
                    <div class="text-reg">
                    <p class="reg-column-right-text">Zmień login</p>
                    </div>
                    <input type="text" id="login-change" name="login-change" value="<?php echo htmlspecialchars($login); ?>" />
                    </div>
              </div>

                  <button id="button_edit_profile_change">Zaaktualizuj</button>
                  </form>
            </div>
            <i class="fa-solid fa-xmark" id="popupclose"></i>
        </div>
       
       <!-----------popup dla wyswietlania konkretnego posta --------------->
       <div class="popup-background" id="popup-single-post-view">
            <div class="popup-post-box">
                <img src="assets/img/test-post-img.png" alt="img" class="popup-post-left">
                <div class="popup-post-right">
                    <div class="popup-author-informations">
                        <div class="img-temp"><img src="<?php echo htmlspecialchars($profileImg); ?>" alt="dsad"></div>
                        <p class="author-name"><?php echo htmlspecialchars($login); ?></p>
                        <i class="fa-solid fa-ellipsis"></i>
                    </div>
                    <div class="comments-section">

                    </div>
                    <div class="post-below">
                        <div class="post-buttons">
                            <i class="fa-regular fa-heart" id="post-like-button"></i>
                        </div>
                        <div class="post-below-text">
                            <div class="likes-amount">
                                <p>Liczba polubień:</p>
                                <p class="likes-amount-text"></p>

                            </div>
                            <div class="date-time-post">
                            <p class="date-post"></p>

                            </div>
                            
                        </div>
                    </div>
                    <div class="add-comment">
                        <input type="text" id="comment-input" placeholder="Dodaj komentarz...">
                        <button id="publish-comment">Opublikuj</button>
                    </div>

                   
                </div>
            </div>
            <i class="fa-solid fa-xmark" id="popupclose"></i>
            
       </div>
    <!-----------popup dla wyswietlania konkretnego posta --------------->
       

       <!-- pierwszy popup dla tworzenia postu (wybieranie zdjecia) -->
       <div class="popup-background" id="popup-create-post-box-first">
    <!-----------box pierwszego popupa: --------------->
    <div class="popup-post-box" id="create-post-drop-area">
        <div class="create-post-top">
            <p>Utwórz nowy post</p>
        </div>
        <div id="create-post-drop-image-area">
            <div class="create-post-below">
                <i class="fa-regular fa-images"></i>
                <p>Przeciągnij zdjęcia tutaj</p>
                <button id="button-choose-image-pc">Wybierz z komputera</button>
                <input type="file" id="create-post-img-input" name="create-post-img-input" accept=".jpg,.jpeg,.png" hidden />
            </div>
        </div>
    </div>
    <div id="popup-close">
        <i class="fa-solid fa-xmark" id="popupclose"></i>
    </div>
</div>
        <!-- pierwszy popup dla tworzenia postu (wybieranie zdjecia) -->
                        

        <!-------------- drugi popup dla tworzenia postu (final) (wybieranie opis i finalnie udostepnainie)------------->
        <div class="popup-background" id="popup-create-post-box-final">
            <div class="popup-post-box">
                <div class="create-post-top">
                    <div id="go-back-button">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </div> <!--jesli klikne na ten button to odpali sie div #popup-reject-post -->
                    <p>Utwórz nowy post</p>
                    <button id="share-post">Udostępnij</button> <!--po kliknieciu post i z jego danymi wysyla sie do tabeli posts -->
                </div>
                <div class="final-post-container">
                   
                    <img src="assets/img/test-post-img.png" alt="img" class="create-post-image" id="final-box-image-view">
                    <div class="box-final-right">
                        <div class="author-items">
                             <div class="img-temp"><img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Zdjęcie profilowe"></div> <!-- src zdjecia uzupelnia sie w zaleznosci od wstawiajacego post (czyli od aktualnego uzytkownika w sesji) -->
                            <p><?php echo htmlspecialchars($login); ?></p> <!-- tekst uzupelnia sie w zaleznosci od wstawiajacego post (czyli od aktualnego uzytkownika w sesji) -->
                        </div>
                       
                         <textarea placeholder="Opis zdjęcia..." maxlength="350" name="opis-postu" id="post-description"></textarea> <!--opis zdjecia ktory bedzie wysylany do tabeli posts w kolumnie "opis" -->
                        <p id="textarea-amount">0/350</p>
                    </div>
                </div>
            </div>
            <div id="popup-close-final">
                <i class="fa-solid fa-xmark" id="popupclose"></i>
            </div><!--jesli klikne na ten button to odpali sie div #popup-reject-post -->
        </div>
        <!-------------- drugi popup dla tworzenia postu (final) (wybieranie opis i finalnie udostepnainie)------------->






        

        <!-------- na dole popup dla odrzucenia posta --------->
        <div class="popup-background" id="popup-reject-post">
            <div class="popup-reject-post-box" id="popup-reject-post-box">
                <div class="reject-row-1">
                    <p>Odrzucić post?</p>
                    <p>Jeżeli wyjdziesz, zmiany nie zostaną zapisane.</p>
                </div>
                <div class="reject-row-2">
                     <p id="reject-post-button">Odrzuć</p> <!--jesli klikne na ten button to jesli jestem w divie final to zniknie ten div oraz reject-post----->
                </div>
                <div class="reject-row-3">
                    <p id="cancel-post-button">Anuluj</p> <!--jesli klikne na ten button to znike div reject-post----->
                </div>
            </div>
        </div>
        <div class="popup-background" id="popup-reject-post-2">
            <div class="popup-reject-post-box" id="popup-reject-post-box">
                <div class="reject-row-1">
                    <p>Odrzucić post?</p>
                    <p>Jeżeli wyjdziesz, zmiany nie zostaną zapisane.</p>
                </div>
                <div class="reject-row-2-2">
                     <p id="reject-post-button">Odrzuć</p> <!--jesli klikne na ten button to jesli jestem w divie final to zniknie ten div oraz reject-post----->
                </div>
                <div class="reject-row-3-3">
                    <p id="cancel-post-button">Anuluj</p> <!--jesli klikne na ten button to znike div reject-post----->
                </div>
            </div>
        </div>
        <!-------- na dole popup dla odrzucenia posta --------->
    </main>
    <script>
 document.addEventListener("DOMContentLoaded", function() {
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;; // Pass PHP variable to JavaScript
        setUserId(userId); // Set the userId in the JavaScript
    });
</script>
<script src="assets/js/post.js"></script>
<script src="assets/js/search-follow.js"></script>
<script>
const chooseImgDiv = document.getElementById('choose-img');
const profileImgInput = document.getElementById('profile_img');
const chooseFileText = document.getElementById('choose-file');
const imgprofile = document.getElementById("img-profile-change");

// Funkcjonalność przeciągania i upuszczania
chooseImgDiv.addEventListener('dragover', (event) => {
    event.preventDefault();
    chooseImgDiv.classList.add('drag-over');
});

chooseImgDiv.addEventListener('dragleave', () => {
    chooseImgDiv.classList.remove('drag-over');
});

chooseImgDiv.addEventListener('drop', (event) => {
    event.preventDefault();
    chooseImgDiv.classList.remove('drag-over');
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        profileImgInput.files = files;
        chooseFileText.innerText = shortenFileName(files[0].name);

        // Wyświetlanie obrazu w podglądzie
        const file = files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgprofile.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});

// Obsługa wyboru pliku
profileImgInput.addEventListener('change', (event) => {
    const files = event.target.files;
    if (files.length > 0) {
        chooseFileText.innerText = shortenFileName(files[0].name);
    }
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        imgprofile.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
});

function shortenFileName(fileName, maxLength = 15) {
    if (fileName.length > maxLength) {
        return fileName.substring(0, maxLength) + '...';
    }
    return fileName;
}


</script>
<script>
        const editProfileChangeImgPass = document.getElementById("background-change");
        const xbutton = document.getElementById("popupclose");
        const changeButton = document.getElementById("show-change-box-nick-img");

        xbutton.addEventListener("click", ()=>{
            editProfileChangeImgPass.style.display = "none";
        })
        changeButton.addEventListener("click", ()=>{
            editProfileChangeImgPass.style.display = "flex";
        })

</script>
<script src="assets/js/popup.js"></script>
<script src="assets/js/popup_login.js"></script>
<script src="assets/js/nav.js"></script>
<script src="https://kit.fontawesome.com/70f2470b08.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>
</html>