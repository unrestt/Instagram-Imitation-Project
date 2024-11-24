<?php
// Ustawienia połączenia z bazą danych
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kutnik_gallery";

// Tworzenie połączenia
$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdzanie połączenia
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pobranie ID posta z zapytania
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

// Zapytanie SQL do pobrania danych posta
$sql = "
    SELECT 
        posts.licznik_polubień, 
        posts.data_stworzenia,
        users.id AS user_id, 
        users.login AS user_name,
        users.profile_img,
        posts.image_url
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $postId);
$stmt->execute();
$stmt->bind_result($likes, $date, $userId, $userName, $profile_img, $image_url);
$stmt->fetch();

// Formatowanie daty w formacie "YYYY-MM-DD"
$formattedDate = date("Y-m-d", strtotime($date));

// Zwrócenie danych w formacie JSON
$response = [
    'likes' => $likes,
    'date' => $formattedDate, // Zwracamy sformatowaną datę
    'user_id' => $userId,     // ID użytkownika
    'user_name' => $userName,  // Login użytkownika
    'profile_img' => $profile_img,
    'image_url' => $image_url
];

echo json_encode($response);

// Zamknięcie połączenia
$stmt->close();
$conn->close();
?>
