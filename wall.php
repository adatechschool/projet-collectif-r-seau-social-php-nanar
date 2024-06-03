<?php
error_reporting(0);
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['connected_id'])) {
    header("Location: registration.php");
    exit();
}

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "^f2.?abH;Cp?3ZU", "socialnetwork");
if ($mysqli->connect_errno) {
    echo "Échec de la connexion à MySQL : " . $mysqli->connect_error;
    exit();
}

// Récupération de l'ID de l'utilisateur connecté et de l'ID de l'utilisateur dont le profil est consulté
$connectedUserId = $_SESSION['connected_id'];
$userId = intval($_GET['user_id']);

// Récupération des informations sur l'utilisateur dont le profil est consulté
$laQuestionEnSql = "SELECT * FROM users WHERE id='$userId'";
$lesInformations = $mysqli->query($laQuestionEnSql);
if ($lesInformations) {
    $user = $lesInformations->fetch_assoc();
} else {
    echo "Échec de la requête : " . $mysqli->error;
    exit();
}

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mon Profil / C</title> 
    <meta name="author" content="Julien Falconnet">
    <link rel="icon" href="./favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
<header>
    <img src="logo.svg" class="logo"/>
    <nav id="menu">
        <a href="news.php"><img src="news.png">Actualités</a>
        <a href="wall.php?user_id=<?php echo $_SESSION['connected_id']; ?>"><img src="wall.png">Mon Profil</a>
        <a href="feed.php?user_id=<?php echo $_SESSION['connected_id']; ?>"><img src="flux.png">Flux</a>
        <a href="tags.php?tag_id=<?php echo $_SESSION['connected_id']; ?>"><img src="tag.svg">Mots-clés</a>
    </nav>
    <nav id="user">
        <a href="#"><img src="account.svg" class="account">Mon Compte</a>
        <ul>
            <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Paramêtres</a></li>
            <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Mes Followers</a></li>
            <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Mes Abonnements</a></li>
            <li>
                <div class="user-widget">
                    <?php if ($_SESSION['connected_id'] !== null) : ?>
                        <a href="logout.php">Me Déconnecter</a>
                    <?php else : ?>
                        <a href="login.php">Me connecter</a>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
    </nav>
</header>
<div id="wrapper">
    <aside>
        <img src="profil.png" alt="Portrait de l'utilisateur"/>
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez tous les messages de l'utilisateur : <?php echo $user['alias']; ?>
                (n° <?php echo $user['id']; ?>)
            </p>
        </section>
    </aside>
    <main>
        <?php
        // Gestion de l'action de "like" ou "dislike" sur un message
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['like'])) {
                $postId = $_POST['post_id'];
                $checkLikeQuery = "SELECT * FROM likes WHERE user_id='$connectedUserId' AND post_id='$postId'";
                $result = $mysqli->query($checkLikeQuery);
                if ($result && $result->num_rows == 0) {
                    $likeQuery = "INSERT INTO likes (user_id, post_id) VALUES ('$connectedUserId', '$postId')";
                    if (!$mysqli->query($likeQuery)) {
                    }
                }
            } elseif (isset($_POST['dislike'])) {
                $postId = $_POST['post_id'];
                $dislikeQuery = "DELETE FROM likes WHERE user_id='$connectedUserId' AND post_id='$postId'";
                if (!$mysqli->query($dislikeQuery)) {}
            }
        }
        
        // Affichage du formulaire pour poster un message si l'utilisateur consulte son propre profil
        if ($userId == $_SESSION['connected_id']) {
        ?>
            <article>
                <h2>Poster un message</h2>
                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
                    $authorId = $_SESSION['connected_id'];
                    $postContent = $_POST['message'];
                    $authorId = intval($mysqli->real_escape_string($authorId));
                    $postContent = $mysqli->real_escape_string($postContent);
                    $lInstructionSql = "INSERT INTO posts (id, user_id, content, created, parent_id) VALUES (NULL, '$authorId', '$postContent', NOW(), NULL)";
                    $ok = $mysqli->query($lInstructionSql);
                    if (!$ok) {
                    }
                }
                ?>
                <form action="wall.php?user_id=<?php echo $_SESSION['connected_id']; ?>" method="post">
                    <dl>
                        <dt><label for='message'>Message</label></dt>
                        <dd><textarea name='message'></textarea></dd>
                    </dl>
                    <input type='submit' value='Poster' class="orange-button" >
                </form>               
            </article>
        <?php
        }

        // Récupération des messages de l'utilisateur consulté
        $laQuestionEnSql = "
            SELECT posts.id as post_id, posts.content, posts.created, users.alias as author_name,
            users.id as user_id,
            tags.id as tag_id, 
            COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist,
            IF(likes.user_id IS NOT NULL, 1, 0) AS liked_by_user
            FROM posts
            JOIN users ON users.id=posts.user_id
            LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
            LEFT JOIN tags ON posts_tags.tag_id = tags.id 
            LEFT JOIN likes ON likes.post_id = posts.id AND likes.user_id = '$connectedUserId'
            WHERE posts.user_id='$userId'
            GROUP BY posts.id
            ORDER BY posts.created DESC  
        ";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (!$lesInformations) {
            echo "Échec de la requête : " . $mysqli->error;
        }

        // Suppression d'un message
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['delete'])) {
    
            $postIdToDelete = $_POST['post_id'];
        
            // Préparation de la requête SQL de suppression
            //$deleteQuery = "UPDATE posts SET deleted = 1 WHERE id='$postIdToDelete'";
            
            // Exécution de la requête de suppression
            if ($mysqli->query($deleteQuery)) {
                // Redirection de l'utilisateur vers la page actuelle pour actualiser les messages
                header("Location: {$_SERVER['REQUEST_URI']}");
                exit();
            } else {
            }
        }

        // Affichage des messages de l'utilisateur consulté
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
                        <?php if ($post['liked_by_user'] == 0) : ?>
                            <button type="submit" name="like" style="border: none; background: none; cursor: pointer;">
                                <img src="like_icon.png" alt="Like" width="30" height="30">
                            </button>
                        <?php else : ?>
                            <button type="submit" name="dislike" style="border: none; background: none; cursor: pointer;">
                                <img src="dislike_icon.png" alt="Dislike" width="30" height="30">
                            </button>
                        <?php endif; ?>
                    </form>
                    <small><?php echo $post['like_number']; ?> ♥</small>
                    <a href="tags.php?tag_id=<?php echo $post['tag_id']; ?>"><?php echo $post['taglist']; ?></a>
                    
                    <!-- Bouton de suppression du message -->
                    <?php if ($post['user_id'] == $_SESSION['connected_id']) : ?>
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <button type="submit" name="delete" style="border: none; background: none; cursor: pointer;">
                                Supprimer
                            </button>
                        </form>
                    <?php endif; ?>
                </footer>
            </article>

        <?php
        }
        ?>
    </main>
</div>
</body>
</html>