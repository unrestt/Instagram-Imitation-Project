<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Połączenie z bazą danych
$mysqli = new mysqli("localhost", "root", "", "kutnik_gallery");
if ($mysqli->connect_error) {
    die("Błąd połączenia: " . $mysqli->connect_error);
}

// Odbieranie user_id z URL (jeśli jest)
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];

if ($userId) {
    // Pobieranie danych użytkownika z tabeli 'users'
    $query = "SELECT login, profile_img, biogram FROM users WHERE id = ?";
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
    } else {
        // Użytkownik nie istnieje
        echo "Użytkownik nie znaleziony!";
        exit();
    }

    // Zliczanie liczby postów użytkownika
    $queryPosts = "SELECT COUNT(*) AS posts_count FROM posts WHERE user_id = ?";
    $stmtPosts = $mysqli->prepare($queryPosts);
    $stmtPosts->bind_param("i", $userId);
    $stmtPosts->execute();
    $resultPosts = $stmtPosts->get_result();
    $postsCount = $resultPosts->fetch_assoc()['posts_count'];
    $stmtPosts->close();

    // Zliczanie liczby obserwujących użytkownika
    $queryFollowers = "SELECT COUNT(*) AS followers_count FROM relacje_obserwacji WHERE id_obserwowany = ?";
    $stmtFollowers = $mysqli->prepare($queryFollowers);
    $stmtFollowers->bind_param("i", $userId);
    $stmtFollowers->execute();
    $resultFollowers = $stmtFollowers->get_result();
    $followersCount = $resultFollowers->fetch_assoc()['followers_count'];
    $stmtFollowers->close();

    // Zliczanie liczby obserwowanych przez użytkownika
    $queryFollowing = "SELECT COUNT(*) AS following_count FROM relacje_obserwacji WHERE id_obserwujacy = ?";
    $stmtFollowing = $mysqli->prepare($queryFollowing);
    $stmtFollowing->bind_param("i", $userId);
    $stmtFollowing->execute();
    $resultFollowing = $stmtFollowing->get_result();
    $followingCount = $resultFollowing->fetch_assoc()['following_count'];
    $stmtFollowing->close();

    // Sprawdzanie, czy użytkownik już obserwuje daną osobę
    $queryFollowed = "SELECT COUNT(*) AS is_following FROM relacje_obserwacji WHERE id_obserwujacy = ? AND id_obserwowany = ?";
    $stmtFollowed = $mysqli->prepare($queryFollowed);
    $stmtFollowed->bind_param("ii", $_SESSION['user_id'], $userId);
    $stmtFollowed->execute();
    $resultFollowed = $stmtFollowed->get_result();
    $isFollowing = $resultFollowed->fetch_assoc()['is_following'] > 0;
    $stmtFollowed->close();

    $isOwnProfile = ($_SESSION['user_id'] == $userId);
    // Obsługa akcji "Follow" i "Unfollow" za pomocą POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['follow'])) {
            // Użytkownik chce obserwować
            $insertQuery = "INSERT INTO relacje_obserwacji (id_obserwujacy, id_obserwowany) VALUES (?, ?)";
            $insertStmt = $mysqli->prepare($insertQuery);
            $insertStmt->bind_param("ii", $_SESSION['user_id'], $userId);
            $insertStmt->execute();
            $insertStmt->close();
            echo 'followed'; // Odpowiedź AJAX
            exit();
        } elseif (isset($_POST['unfollow'])) {
            // Użytkownik chce odobserwować
            $deleteQuery = "DELETE FROM relacje_obserwacji WHERE id_obserwujacy = ? AND id_obserwowany = ?";
            $deleteStmt = $mysqli->prepare($deleteQuery);
            $deleteStmt->bind_param("ii", $_SESSION['user_id'], $userId);
            $deleteStmt->execute();
            $deleteStmt->close();
            echo 'unfollowed'; // Odpowiedź AJAX
            exit();
        }
    }
} else {
    echo "Nieprawidłowe ID użytkownika.";
    exit();
}

$stmt->close();
?>






<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kutnigram</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/profile.css">
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
                            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Zdjęcie profilowe">
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
       <div class="profile-main-container">
        <div class="profile-informations">
                <div class="profile-image-container">
                    <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Zdjęcie profilowe">
                </div>
                <div class="profile-right-information-container">
                <div class="row-profile-name">
                        <p id="name-account"><?php echo htmlspecialchars($login); ?></p>
                        <?php if ($userId == $_SESSION['user_id']): ?>
                            <button><a href="edit-profile.php">Edytuj profil</a></button>
                        <?php endif; ?>
                        <div class="follow-container">
                            <?php if (!$isOwnProfile): ?>
                                <button class="follow" style="display: <?= $isFollowing ? 'none' : 'inline-block' ?>">Follow</button>
                                <button class="unfollow" style="display: <?= $isFollowing ? 'inline-block' : 'none' ?>">Unfollow</button>
                            <?php endif; ?>
                        </div>

                    </div>

                    <div class="row-profile-text">
                        <p id="posts-amount">Posty: <?php echo $postsCount; ?></p>
                        <p id="followers-amount">Obserwujących: <?php echo $followersCount; ?></p>
                        <p id="following-amount">Obserwujący: <?php echo $followingCount; ?></p>
                    </div>
                    <div class="profile-bio">
                        <p id="bio-text"><?php echo nl2br(htmlspecialchars($bio)); ?></p>
                    </div>
                </div>
            </div>
            <div class="profile-images-container">
            <?php
// Połączenie z bazą danych przy użyciu mysqli
$mysqli = new mysqli("localhost", "root", "", "kutnik_gallery");
if ($mysqli->connect_error) {
    die("Błąd połączenia: " . $mysqli->connect_error);
}

// Zapytanie SQL do pobrania postów
$user_id = isset($_GET['user_id']) ? $mysqli->real_escape_string($_GET['user_id']) : $_SESSION['user_id'];

// Zapytanie SQL do pobrania postów dla konkretnego użytkownika w odwrotnej kolejności
$query = "SELECT id, image_url, opis, licznik_polubień, data_stworzenia FROM posts WHERE user_id = '$user_id' ORDER BY data_stworzenia DESC";
$result = $mysqli->query($query);
?>
<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($post = $result->fetch_assoc()): ?>
        <div class="image-container">
            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post Image" data-post-id="<?php echo $post['id']; ?>">
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>Brak dostępnych postów.</p>
<?php endif; ?>

<?php
// Zamknięcie wyniku i połączenia z bazą
$result->free();
$mysqli->close();
?>
</div>
        </div>



       <!-----------popup dla wyswietlania konkretnego posta --------------->
       <div class="popup-background" id="popup-single-post-view">
            <div class="popup-post-box">
                <img src="assets/img/test-post-img.png" alt="img" class="popup-post-left">
                <div class="popup-post-right">
                    <div class="popup-author-informations">
                        <div class="img-temp"><img src="<?php echo htmlspecialchars($profileImg); ?>" alt="dsad"></div>
                        <p class="author-name"><?php echo htmlspecialchars($login); ?></p>
                        <?php if ($userId == $_SESSION['user_id']): ?>
                            <i class="fa-solid fa-ellipsis" id="delete-post-popup-button"></i>
                        <?php endif; ?>
                    </div>
                    <div class="delete-post-popup" id="delete-post-popup" style="display: none;">
                        <p>Usunąć post?</p>
                        <div class="delete-buttons">
                            <button>Tak</button>
                            <button>Nie</button>
                        </div>
  
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







        <div class="popup-background" id="popup-delete-post">
            <div class="popup-reject-post-box" id="popup-delete-post-box">
                <div class="reject-row-1">
                    <p>Usunąć post?</p>
                    <p>Nie bedziesz mógł tego cofnąć.</p>
                </div>
                <div class="reject-row-2" id="delete-post-button">
                     <p>Usuń</p>
                </div>
                <div class="reject-row-3" id="anuluj-post-button">
                    <p>Anuluj</p>
                </div>
            </div>
        </div>
    </main>

<script>
 document.addEventListener("DOMContentLoaded", function() {
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>; // Pass PHP variable to JavaScript
        setUserId(userId); // Set the userId in the JavaScript
    });
</script>
<script src="assets/js/post.js"></script>
<script src="assets/js/search-follow.js"></script>



<script src="https://kit.fontawesome.com/70f2470b08.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="assets/js/nav.js"></script>
<script src="assets/js/follow.js"></script>
</body>
</html>