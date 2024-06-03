<?php
session_start();
if (!isset($_SESSION['connected_id'])) {
    header("Location: registration.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "socialnetwork");
if ($mysqli->connect_errno) {
    echo "Échec de la connexion à MySQL : " . $mysqli->connect_error;
    exit();
}

$connectedUserId = $_SESSION['connected_id'];
$userId = intval($_GET['user_id']);

$laQuestionEnSql = "SELECT * FROM users WHERE id='$userId'";
$lesInformations = $mysqli->query($laQuestionEnSql);
if ($lesInformations) {
    $user = $lesInformations->fetch_assoc();
} else {
    echo "Échec de la requête : " . $mysqli->error;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['like'])) {
        $postId = $_POST['post_id'];
        $likeQuery = "INSERT INTO likes (user_id, post_id) VALUES ('$connectedUserId', '$postId')";
        if ($mysqli->query($likeQuery)) {
            echo "<p>Like ajouté avec succès !</p>";
        } else {
            echo "<p>Erreur lors de l'ajout du like : " . $mysqli->error . "</p>";
        }
    } elseif (isset($_POST['dislike'])) {
        $postId = $_POST['post_id'];
        $dislikeQuery = "DELETE FROM likes WHERE user_id='$connectedUserId' AND post_id='$postId'";
        if ($mysqli->query($dislikeQuery)) {
            echo "<p>Dislike ajouté avec succès !</p>";
        } else {
            echo "<p>Erreur lors de l'ajout du dislike : " . $mysqli->error . "</p>";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Mur</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
<header>
    <img src="resoc.jpg" alt="Logo de notre réseau social"/>
    <nav id="menu">
        <a href="news.php">Actualités</a>
        <a href="wall.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Mur</a>
        <a href="feed.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Flux</a>
        <a href="tags.php?tag_id=<?php echo $_SESSION['connected_id']; ?>">Mots-clés</a>
    </nav>
    <nav id="user">
        <a href="#">Profil</a>
        <ul>
            <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Paramètres</a></li>
            <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Mes suiveurs</a></li>
            <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Mes abonnements</a></li>
            <li>
                <div class="user-widget">
                    <?php if ($_SESSION['connected_id'] !== null) : ?>
                        <a href="logout.php">Se déconnecter</a>
                    <?php else : ?>
                        <a href="login.php">Se connecter</a>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
    </nav>
</header>
<div id="wrapper">
    <aside>
        <img src="user.jpg" alt="Portrait de l'utilisateur"/>
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez tous les messages de l'utilisateur : <?php echo $user['alias']; ?>
                (n° <?php echo $user['id']; ?>)
            </p>
            <?php if ($userId !== $connectedUserId) : ?>
                <form action="wall.php?user_id=<?php echo $userId; ?>" method="post">
                    <button type="submit" name="subscribe">S'abonner</button>
                </form>
            <?php endif; ?>
        </section>
    </aside>
    <main>
        <?php
        $laQuestionEnSql = "
            SELECT posts.id as post_id, posts.content, posts.created, users.alias as author_name,
            users.id as user_id,
            tags.id as tag_id, 
            COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
            FROM posts
            JOIN users ON users.id=posts.user_id
            LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
            LEFT JOIN tags ON posts_tags.tag_id = tags.id 
            LEFT JOIN likes ON likes.post_id = posts.id 
            WHERE posts.user_id='$userId' 
            GROUP BY posts.id
            ORDER BY posts.created DESC  
        ";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (!$lesInformations) {
            echo "Échec de la requête : " . $mysqli->error;
        }
        while ($post = $lesInformations->fetch_assoc()) {
        ?>
            <article>
                <h3>
                    <time datetime='<?php echo $post['created']; ?>'><?php echo $post['created']; ?></time>
                </h3>
                <address><a href="wall.php?user_id=<?php echo $post['user_id']; ?>"><?php echo $post['author_name']; ?></a></address>
                <div>
                    <p><?php echo $post['content']; ?></p>
                </div>
                <footer>
    <form action="" method="post" style="display: inline;">
        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
        <button type="submit" name="like" style="border: none; background: none; cursor: pointer;">
            <img src="./img/like_icon.png" alt="Like" width="20" height="20">
        </button>
    </form>
    <form action="" method="post" style="display: inline;">
        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
        <button type="submit" name="dislike" style="border: none; background: none; cursor: pointer;">
            <img src="./img/dislike_icon.png" alt="Dislike" width="20" height="20">
        </button>
    </form>
    <small><?php echo $post['like_number']; ?> ♥</small>
    <a href="tags.php?tag_id=<?php echo $post['tag_id']; ?>"><?php echo $post['taglist']; ?></a>
</footer>


            </article>
        <?php
        }
        ?>
    </main>
</div>
</body>
</html>
