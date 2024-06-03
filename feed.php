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
        <title>Feed / C</title>         
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
            <?php
            /**
             * Cette page est TRES similaire à wall.php. 
             * Vous avez sensiblement à y faire la meme chose.
             * Il y a un seul point qui change c'est la requete sql.
             */
            /**
             * Etape 1: Le mur concerne un utilisateur en particulier
             */
            $userId = intval($_GET['user_id']);
            ?>
            <?php
            /**
             * Etape 2: se connecter à la base de donnée
             */
            $mysqli = new mysqli("localhost", "root", "^f2.?abH;Cp?3ZU", "socialnetwork");
            ?>

            <aside>
                <?php
                /**
                 * Etape 3: récupérer le nom de l'utilisateur
                 */
                $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
                //echo "<pre>" . print_r($user, 1) . "</pre>";
                ?>
                <img src="profil.png" alt="Portrait de l'utilisateur"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les message des utilisatrices
                        auxquel est abonnée l'utilisatrice <?php echo $user['alias'] ?>
                        (n° <?php echo $user['id'] ?>)
                    </p>

                </section>
            </aside>
            <main>
                <?php
                /**
                 * Etape 3: récupérer tous les messages des abonnements
                 */
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    users.id as user_id,
                    tags.id as tag_id,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    JOIN posts ON posts.user_id=users.id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE followers.following_user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                while ($followers = $lesInformations->fetch_assoc())
                {
                    //echo "<pre>" . print_r($followers, 1) . "</pre>";
                /**
                 * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
                 * A vous de retrouver comment faire la boucle while de parcours...
                 */
                ?>                
                    <article>
                        <h3>
                            <time datetime='2020-02-01 11:12:13' ><?php echo $followers['created'] ?></time>
                        </h3>
                        <address><?php echo $followers['author_name'] ?></address>
                        <div>
                            <p><?php echo $followers['content'] ?></p>
                            
                        </div>                                            
                        <footer>
                            <small><?php echo $followers['like_number'] ?> ♥</small>
                            <a href="tags.php?tag_id=<?php echo $post['tag_id']?>"><?php echo $followers['taglist'] ?></a>
                        </footer>
                    </article>
                <?php } ?>


            </main>
        </div>
    </body>
</html>
