<?php
error_reporting(0);
session_start();

if ($_SESSION['connected_id'] !== null) {
    header("Location: news.php?user_id=" . $_SESSION['connected_id']);
    exit();
}
?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Inscription / C</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="icon" href="./favicon.svg" type="image/x-icon">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
    

        <div id="wrapper" >

            <aside>
                
            </aside>
            <main>
                <article>
                    <h2>Inscription</h2>
                    <?php
                    /**
                     * TRAITEMENT DU FORMULAIRE
                     */
                    // Etape 1 : vérifier si on est en train d'afficher ou de traiter le formulaire
                    // si on recoit un champs email rempli il y a une chance que ce soit un traitement
                    $enCoursDeTraitement = isset($_POST['email']);
                    if ($enCoursDeTraitement)
                    {
                        // on ne fait ce qui suit que si un formulaire a été soumis.
                        // Etape 2: récupérer ce qu'il y a dans le formulaire @todo: c'est là que votre travaille se situe
                        // observez le résultat de cette ligne de débug (vous l'effacerez ensuite)
                       
                        // et complétez le code ci dessous en remplaçant les ???
                        $new_email = $_POST['email'];
                        $new_alias = $_POST['pseudo'];
                        $new_passwd = $_POST['motpasse'];


                        //Etape 3 : Ouvrir une connexion avec la base de donnée.
                        $mysqli = new mysqli("localhost", "root", "^f2.?abH;Cp?3ZU", "socialnetwork");
                        //Etape 4 : Petite sécurité
                        // pour éviter les injection sql : https://www.w3schools.com/sql/sql_injection.asp
                        $new_email = $mysqli->real_escape_string($new_email);
                        $new_alias = $mysqli->real_escape_string($new_alias);
                        $new_passwd = $mysqli->real_escape_string($new_passwd);
                        // on crypte le mot de passe pour éviter d'exposer notre utilisatrice en cas d'intrusion dans nos systèmes
                       
                        // NB: md5 est pédagogique mais n'est pas recommandée pour une vraies sécurité
                        //Etape 5 : construction de la requete
                        $lInstructionSql = "INSERT INTO users (id, email, password, alias) "
                                . "VALUES (NULL, "
                                . "'" . $new_email . "', "
                                . "'" . $new_passwd . "', "
                                . "'" . $new_alias . "'"
                                . ");";
                        // Etape 6: exécution de la requete
                        $ok = $mysqli->query($lInstructionSql);
                        if ( !$ok)
                        {
                            echo "L'inscription a échouée : ";
                        } else
                        {
                            echo "Votre inscription est un succès : " . $new_alias;
                            header("Location: news.php?user_id=" . $_SESSION['connected_id']);
                        }
                    }
                    ?>                     
                    <form action="registration.php" method="post">
                        <input type='hidden'name='???' value='achanger'>
                        <dl>
                            <dt><label for='pseudo'>Pseudo</label></dt>
                            <dd><input type='text'name='pseudo'></dd>
                            <dt><label for='email'>E-Mail</label></dt>
                            <dd><input type='email'name='email'></dd>
                            <dt><label for='motpasse'>Mot de passe</label></dt>
                            <dd><input type='password'name='motpasse'></dd>
                        </dl>
                        <input type='submit'>
                    </form>
                    <p>
                        Vous avez déjà un compte ?
                        <a href='login.php'>Se connecter.</a>
                    </p>
                </article>
            </main>
        </div>
    </body>
</html>