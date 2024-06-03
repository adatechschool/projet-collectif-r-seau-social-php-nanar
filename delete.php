<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id']);

    $mysqli = new mysqli("localhost", "root", "^f2.?abH;Cp?3ZU", "socialnetwork");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

 
    $sql = "DELETE FROM user WHERE id = $user_id";

    if ($conn->query($sql) === TRUE) {
        echo "Utilisateur supprimé avec succès";
    } else {
        echo "Erreur lors de la suppression: " . $conn->error;
    }

    $conn->close();
    header("Location: registration.php");
    exit();
}
?>
