<?php
session_start();
setcookie(session_name(), '', time() - 3600, '/');  // Usuwa ciasteczko PHPSESSID
session_start();  // Ponowne uruchomienie sesji przed jej zniszczeniem
session_destroy();  // Niszczenie sesji
header("Location: login.php");
exit();
?>
