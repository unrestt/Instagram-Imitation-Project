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

// ID aktualnie zalogowanego użytkownika
$currentUserId = $_SESSION['user_id'] ?? null;

// Pobranie ścieżki do zdjęcia profilowego użytkownika
$profileImage = 'assets/uploads/icon-profile-null.png'; // Domyślne zdjęcie
$loginPost = '';

if ($currentUserId) {
    $query = "SELECT profile_img, login FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $currentUserId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $profileImage = $user['profile_img'] ?? $profileImage;
            $loginPost = $user['login'] ?? '';
        }
        $stmt->close();
    }
}

// Obsługa "obserwowania" użytkownika
if ($currentUserId && isset($_GET['follow_user_id'])) {
    $followUserId = (int)$_GET['follow_user_id'];
    $checkQuery = "SELECT * FROM relacje_obserwacji WHERE id_obserwujacy = ? AND id_obserwowany = ?";
    $stmt = $mysqli->prepare($checkQuery);
    if ($stmt) {
        $stmt->bind_param("ii", $currentUserId, $followUserId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            echo 'already_followed';
        } else {
            $insertQuery = "INSERT INTO relacje_obserwacji (id_obserwujacy, id_obserwowany) VALUES (?, ?)";
            $stmt = $mysqli->prepare($insertQuery);
            if ($stmt) {
                $stmt->bind_param("ii", $currentUserId, $followUserId);
                echo $stmt->execute() ? 'followed' : 'error';
            }
        }
        $stmt->close();
    }
    exit();
}

// Pobranie 5 losowych użytkowników do wyświetlenia jako sugestie
$suggestions = [];
$query = "
    SELECT id, login, profile_img
    FROM users
    WHERE id NOT IN (SELECT id_obserwowany FROM relacje_obserwacji WHERE id_obserwujacy = ?)
    AND id != ?
    ORDER BY RAND() LIMIT 5
";
$stmt = $mysqli->prepare($query);
if ($stmt) {
    $stmt->bind_param("ii", $currentUserId, $currentUserId); // Przekazujemy dwa razy $currentUser Id
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row;
        }
    }
    $stmt->close();
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
                            <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Zdjęcie profilowe">
                        </div>
                        <p class="nav-text">Profil</p>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="navigation-bottom">
            <a href="edit-profile.php"><i class="fa-solid fa-gear"></i></a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
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
        <div class="main-containers">
            <div class="main-left-container">
                <div class="top-tab">
                    <a href="#" id="for-u">Dla ciebie</a>
                </div> 
                <div class="posts-container">
                <?php

$sql = "
    SELECT 
        p.id AS post_id,
        p.user_id,
        p.image_url,
        p.opis,
        p.data_stworzenia,
        p.licznik_polubień,
        u.id AS user_id,
        u.login AS author_name,
        u.profile_img AS author_profile_img,
        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS liczba_komentarzy
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.data_stworzenia DESC
";

$result = $mysqli->query($sql);

// Wyświetlanie postów
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Formatowanie daty na "czas relatywny"
        $post_time_released = date('Y-m-d', strtotime($row['data_stworzenia']));
        $userId = htmlspecialchars($row['user_id']);
        $currentUserId = $_SESSION['user_id'] ?? null; // Pobieramy identyfikator użytkownika z sesji

        // Sprawdzanie, czy użytkownik jest już obserwowany
        $followClass = 'follow-btn';
        $followText = 'Obserwuj';

        if ($currentUserId && $currentUserId != $userId) { // Sprawdzamy, czy nie jesteśmy właścicielem posta
            $checkFollowQuery = "SELECT * FROM relacje_obserwacji WHERE id_obserwujacy = ? AND id_obserwowany = ?";
            $checkStmt = $mysqli->prepare($checkFollowQuery);
            $checkStmt->bind_param('ii', $currentUserId, $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $followClass = 'follow-btn followed';
                $followText = 'Obserwujesz';
            }

            // Zamykanie zapytania
            $checkStmt->close();
        }
        
        echo '
        <div class="post-container">         
            <div class="top-post">
                <div class="img-temp"><img src="' . htmlspecialchars($row['author_profile_img']) . '" alt="profile-img"></div> 
                <p class="author-name"><a href="profile.php?user_id=' . $userId . '">' . htmlspecialchars($row['author_name']) . '</a></p>
                <p>•</p>
                <p class="post-time-relased">' . htmlspecialchars($post_time_released) . '</p>
                <p>•</p>';

            if ($currentUserId && $currentUserId != $userId) {
                echo '<p class="follow-text"><a href="#" class="' . $followClass . '" data-userid="' . $userId . '" onclick="followUser(event, ' . $userId . ')">' . $followText . '</a></p>';
            }

        echo '
            </div>
            <div class="image-post">
                <img src="' . htmlspecialchars($row['image_url']) . '" alt="post-image">
            </div>
            <div class="bottom-post">
                <div class="post-buttons">
                    <i class="fa-regular fa-heart" id="post-like-button" data-post-id="' . $row['post_id'] . '"></i>
                    <i class="fa-regular fa-comment" data-post-id="' . $row['post_id'] . '"></i>
                </div>
                <div class="likes-amount">
                    <p>Liczba polubień: </p>
                    <p class="likes-amount-text">' . htmlspecialchars($row['licznik_polubień']) . '</p>
                </div>
                <div class="post-description">
                    <p class="author">' . htmlspecialchars($row['author_name']) . '</p>
                    <p class="post-description-text">' . htmlspecialchars($row['opis']) . '</p> 
                </div>
                <div class="post-comments-text">
                    <p class="comments-text-link" data-post-id="' . $row['post_id'] . '">Zobacz wszystkie komentarze: </p>
                    <p class="amount-of-comments">' . htmlspecialchars($row['liczba_komentarzy']) . '</p>
                </div>
            </div>
        </div>';
    }
 } else {
    echo '<p>Brak postów do wyświetlenia.</p>';
}

// Zamknięcie połączenia z bazą danych
$mysqli->close();
?>


                </div>
    
            </div>
            <div class="main-right-container">
                <div class="top-text-right">
                    <p>Propozycje dla Ciebie</p>
                </div>
                <div class="suggests-box">
                <?php
                if (!empty($suggestions)) {
                    foreach ($suggestions as $suggestion) {
                        $userId = htmlspecialchars($suggestion['id']);
                        $login = htmlspecialchars($suggestion['login']);
                        $profileImg = htmlspecialchars($suggestion['profile_img']);

                        echo '
                        <div class="suggest-box">
                            <div class="img-temp"><img src="' . $profileImg . '" alt="Profilowe"></div>
                            <div class="suggest-text-box">
                                <p class="suggest-name"><a href="profile.php?user_id=' . $userId . '">' . $login . '</a></p>
                                <p class="follow-text"><a href="#" class="' . $followClass . '" data-userid="' . $userId . '" onclick="followUser(event, '.$userId.')">' . $followText . '</a></p>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<p>Brak użytkowników do wyświetlenia.</p>';
                }
                ?>
                   


                </div>
                <footer>
                    <p id="footer-text">
                        Copyright © Kutnigram, Wszelkie prawa zastrzeżone 2024
                    </p>

                </footer>
            </div>
        </div>
              <!-----------popup dla wyswietlania konkretnego posta --------------->
              <?php
$mysqli = new mysqli("localhost", "root", "", "kutnik_gallery");

// Sprawdź połączenie
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = "
    SELECT 
        p.id AS post_id
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.data_stworzenia DESC
";

$result = $mysqli->query($sql);

// Wyświetlanie postów
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '
            <div class="popup-background" id="popup-single-post-view">
                <div class="popup-post-box">
                    <img src="assets/img/test-post-img.png" alt="img" class="popup-post-left" id="post-image-url">
                    <div class="popup-post-right">
                        <div class="popup-author-informations">
                            <div class="img-temp"><img src="" alt="dsad" id="profile-autor"></div>
                            <p class="author-name" id="popup-author-name"></p>
                        </div>
                        <div class="comments-section"></div>
                        <div class="post-below">
                            <div class="post-buttons">
                                <i class="fa-regular fa-heart" id="post-like-button" data-post-id="' . $row['post_id'].'"></i>
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
            </div>';
    }
}

// Zamknij połączenie na końcu skryptu
$mysqli->close();
?>
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
                             <div class="img-temp"><img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Zdjęcie profilowe"></div> <!-- src zdjecia uzupelnia sie w zaleznosci od wstawiajacego post (czyli od aktualnego uzytkownika w sesji) -->
                            <p><?php echo htmlspecialchars($loginPost); ?></p> <!-- tekst uzupelnia sie w zaleznosci od wstawiajacego post (czyli od aktualnego uzytkownika w sesji) -->
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
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
        setUserId(userId);
    });
</script>
<script src="assets/js/post.js"></script>
<script src="assets/js/search-follow.js"></script>




<script src="assets/js/nav.js"></script>
<script src="https://kit.fontawesome.com/70f2470b08.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>
</html>