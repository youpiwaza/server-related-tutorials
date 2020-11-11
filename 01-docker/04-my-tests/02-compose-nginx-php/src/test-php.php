Un peu de texte, n'importe quoi

<p>Un paragraphe</p>

<p>
<?php
    // ^ Balise d'ouverture de php

    // Les commentaires sur une ligne avec //

    // Afficher  quelque chose dans le html
    echo 'Heya world !';

    /* Un commentaire
    sur
    plusieurs
    lignes
    */

    /* Un commentaire
     * sur
     * plusieurs
     * lignes en JOLI !
    */

    echo 'hey'; // Jusqu'à la fin de la ligne avec //

    echo '2'; /* Ce n'est pas forcément jusqu'a la fin de la ligne */ echo '3';

    // La fermeture v
?>
</p>

<p>Il existe également d'autres balises PHP</p>

<p>
<?
    // Ne pas utiliser ! Vecteur d'erreur
    // Il y a certaines anciennes version de php qui ne connaissent pas
    echo "Hey ! Il n'y a pas 'php' derrière <?"
?>
</p>

<p><?= 'Pour afficher directement !'; ?></p>

<p>C'est la même chose que <?php echo 'Pour afficher'; ?></p>

<style>
    /* PAS BIEN ! Faire une feuille de style */
    td {
        border: 1px solid black;
        padding: 1em;
    }
</style>
<table>
    <tbody>
        <tr>
            <td><?= 'Je recommande'; ?></td>
            <td><?= 'Pour'; ?></td>
        </tr>
        <tr>
            <td><?= 'des structures html '; ?></td>
            <td><?= 'complexes'; ?></td>
        </tr>
    </tbody>
</table>

<p>Attention a en pas coller les balises php : Mettre des espaces</p>

<!-- CA NE MARCHE PAS -->
<?php
/* 
<?phpecho 'Bonjour !';?>
*/
?>
<!-- CA MARCHE ! Bien mettre un espace, ou mieux un passage a la ligne -->
<?php echo 'Bonjour !';?>

<?php
    // LES include
    // 2 types principaux d'include, et chacun en a un secondaire
    
    //      https://www.php.net/manual/fr/function.include.php
    // On essaie d'inclure, si ca ne marche pas (fichier non trouvé), le reste du site s'affiche
    // include 'a-inclure.php';
    
    // L'include est REQUIS/Obligatoire, sinon, le site ne s'affiche pas
    // require 'a-inclure.php';
    // require 'a-inclure.php';
    // require 'a-inclure.php';
    // require 'a-inclure.php';
    // require 'a-inclure.php';
    // require 'a-inclure.php';

    // Les autres, ce sont des variantes
    // une seule fois
    include_once 'a-inclure.php';
    include_once 'a-inclure.php';
    include_once 'a-inclure.php';
    include_once 'a-inclure.php';
    include_once 'a-inclure.php';
    include_once 'a-inclure.php';
    require_once 'a-inclure.php';
    require_once 'a-inclure.php';
    require_once 'a-inclure.php';
    require_once 'a-inclure.php';
    require_once 'a-inclure.php';

    // Plus simplement : toujours utiliser require_once pour du code !
    
    echo 'La suite fonctionne quand même';
?>

<hr>

<h2>Les types de variables</h2>

On va avoir plusieurs types de variables :

<table>
    <tbody>
        <tr>
            <th>Types</th>
            <th>Syntaxe</th>
        </tr>
        <tr>
            <td>Chaîne de caractère</td>
            <td>$chaine = 'Je suis une chaine'; // avec les guillemets simples</td>
        </tr>
        <tr>
            <td>Chaîne de caractère qui accepte les variables php</td>
            <td>$chaine = "Je suis une chaine avec des variables $hey"; // avec les guillemets doubles</td>
        </tr>
        <tr>
            <td>Chiffres/nombres</td>
            <td>$age = 42; // sans guillement</td>
        </tr>
        <tr>
            <td>Booléen (vrai/faux)</td>
            <td>$permis = true; // mots clés true/false</td>
        </tr>
        <tr>
            <td>Tableaux (une variable qui contient plein de variables)</td>
            <td>$fruits = array('pommes', 'poires', 'pêches'); // mot clé array + des  variables, séparées par des virgules</td>
        </tr>
        <tr>
            <td>Tableaux (notation raccourcie)</td>
            <td>$fruits = ['pommes', 'poires', 'pêches']; // crochets</td>
        </tr>
    </tbody>
</table>

<h3>Concrètement</h3>
<?php
    $prenom = "Tristan";
    echo "Chaine de caractères : Je m'apelle $prenom <br>";

    $age = 28;
    echo "Chiffre : J'ai $age ans <br>";

    // $ageAnneeProchaine = $age + 1;
    echo "Chiffre avec maths : L'année prochaine j'aurai " . ($age + 1) . " ans <br>";
    // Maths : + - * / etc.

    $permisConduire = true;
    // $permisConduire = false;
    // echo "J'ai mon permis : $permisConduire <br>";

    // Utlisation avec le conditionnel : if ... else > Si ... sinon
    if($permisConduire === true) {
        echo "J'ai mon permis<br>";
    }
    // Sinon ($permisConduire === false)
    else {
        echo "Je n'ai pas mon permis<br>";
    }
    
    // Tableaux
    // $fruits = array('pommes', 'poires', 'pêches');

    //             0        1         2
    $fruits = ['pommes', 'poires', 'pêches'];
    
    echo $fruits . '<br>'; // Affiche le type : Array

    echo $fruits[0] . '<br>';
    echo $fruits[1] . '<br>';
    echo $fruits[2] . '<br>';

    // Affichage dynamique du tableau, il faut un boucle
    // En deux fois : une boucle simple, PUIS une boucle qui affiche le tableau
    
    echo '<h3>Boucle simple : afficher 1 2 3 4 5 6 7 8 9</h3>';
    // Le mot clé "for"
    //  initialisation              condition de sortie            le "pas" > à chaque tour
    //  on comemnce avec i = 0      on arrête quand i vaut 10      A chaque tour, on rajoute 1 a i
    for($i = 0 ;                    $i < 10 ;                      $i++){
        // A chaque tour, j'affiche i suivie d'une virgule
        echo $i . ", ";
    }
    echo '<br>';

    echo '<h3>Boucle moins simple : afficher le contenu d\'un tableau</h3>';

    // Du premier au dernier fruit
    for($i = 0 ; $i < count($fruits) ; $i++){
        // A chaque tour, j'affiche i suivie d'une virgule
        echo $fruits[$i] . ", ";
    }
    echo '<br>';
?>

<br><br><br><br><br><br><br><br><br><br><br>

<?php
    // A VOIR
    // ✅ Les balises ouverture php
    //      ✅ Attention, ne pas coller les balises
    // ✅ Les 4 includes :)
    // ✅ Gestion index.php par le serveur (affichage direct)
    // ✅ Afficher du php dans html, les 2 manières
    // ✅     et concaténation 'hey ' . $prenom
    // BASES PHP
    //     Types des variables
    //     Boucles
    //     Fonctions
?>