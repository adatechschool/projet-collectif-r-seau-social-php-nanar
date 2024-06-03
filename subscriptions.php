<?php
session_start();

if (!isset($_SESSION['connected_id'])) {
    header("Location: registration.php");
    exit();
}
?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Mes Abonnements / C</title> 
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
                    <?php if($_SESSION['connected_id'] !== null ) : ?>
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
                    <p>Sur cette page vous trouverez la liste des personnes dont
                        l'utilisatrice
                        n° <?php echo intval($_GET['user_id']) ?>
                        suit les messages
                    </p>

                </section>
            </aside>
            <main class='contacts'>
                <?php
                // Etape 1: récupérer l'id de l'utilisateur
                $userId = intval($_GET['user_id']);
                // Etape 2: se connecter à la base de donnée
                $mysqli = new mysqli("localhost", "root", "^f2.?abH;Cp?3ZU", "socialnetwork");
                // Etape 3: récupérer le nom de l'utilisateur
                $laQuestionEnSql = "
                    SELECT users.* 
                    FROM followers 
                    LEFT JOIN users ON users.id=followers.followed_user_id 
                    WHERE followers.following_user_id='$userId'
                    GROUP BY users.id
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                // Etape 4: à vous de jouer
                //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci dessous 
                while ($followers = $lesInformations->fetch_assoc()) {                
                    ?>
                    <article>
                        <img src="account.svg" alt="blason" />
                        <!-- <h3>Alexandra</h3>
                        <p>id:654</p>  -->
                        <a href="wall.php?user_id=<?php echo $followers['id']; ?>">
                            <h3><?php echo $followers['alias'] ?></h3>
                        </a>
                        <p>Id : <?php echo $followers['id'] ?></p>
                    </article>
                <?php } ?>
            </main>
        </div>
    </body></html>