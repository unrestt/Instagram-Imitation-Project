<?php
session_start(); // Upewniamy się, że sesja jest aktywna i mamy dostęp do user_id

if (isset($_GET['q'])) {
    $query = $_GET['q'];
    
    // Sprawdzenie, czy użytkownik jest zalogowany
    if (!isset($_SESSION['user_id'])) {
        echo 'error'; // Jeśli brak user_id w sesji, zakończ działanie
        exit();
    }

    $currentUserId = $_SESSION['user_id']; // Pobieramy identyfikator użytkownika z sesji
    
    // Połączenie z bazą danych
    $conn = new mysqli("localhost", "root", "", "kutnik_gallery"); // Dostosuj do swojej bazy danych
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    }

    // Przygotowanie zapytania SQL z operatorem LIKE, aby szukać loginy, które zaczynają się od wpisanego ciągu
    // Dodajemy warunek, aby nie wyświetlać użytkownika z sesji
    $stmt = $conn->prepare("SELECT id, login, profile_img FROM users WHERE login LIKE ? AND id != ? LIMIT 5");
    $searchTerm = $query . '%'; // Używamy '%' tylko po zapytaniu, aby szukało na początku
    $stmt->bind_param('si', $searchTerm, $currentUserId); // 's' dla string, 'i' dla integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Jeśli są wyniki, wyświetlamy je
    while ($row = $result->fetch_assoc()) {
        $userId = $row['id'];
        $login = $row['login'];
        $profileImg = $row['profile_img'] ? $row['profile_img'] : 'assets/uploads/icon-profile-null.png';

        // Sprawdzanie, czy użytkownik jest już obserwowany
        $checkFollowQuery = "SELECT * FROM relacje_obserwacji WHERE id_obserwujacy = ? AND id_obserwowany = ?";
        $checkStmt = $conn->prepare($checkFollowQuery);
        $checkStmt->bind_param('ii', $currentUserId, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        // Zmienna dla klasy i tekstu przycisku
        $followClass = 'follow-btn';
        $followText = 'Obserwuj';

        if ($checkResult->num_rows > 0) {
            $followClass = 'follow-btn followed';
            $followText = 'Obserwujesz';
        }

        // Wyświetlanie wyników w formacie HTML
        echo '<div class="search-result">';
        echo '<div class="img-temp"><img src="' . $profileImg . '" alt="Profilowe"></div>';
        echo '<p class="author-name"><a href="profile.php?user_id=' . $userId . '">' . $login . '</a></p>';
        echo '<p class="follow-text"><a href="#" class="' . $followClass . '" data-userid="' . $userId . '" onclick="followUser(event, '.$userId.')">' . $followText . '</a></p>';
        echo '</div>';

        // Zamykanie zapytania
        $checkStmt->close();
    }

    // Zamykanie zapytania i połączenia
    $stmt->close();
    $conn->close();
}

?>
