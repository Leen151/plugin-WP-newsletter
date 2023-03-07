<div class="wrap">
    <h2>Newsletter</h2>
</div>


<div id="menu-user">
        <p><a href="?page=newsletter-ad/newsletter.php">Retour au menu principal</a></p>
</div><!--fin #menu-user-->


<?php
if (isset($_GET['id'])){
    // On s'assure que l'id est un chiffre
    if (is_numeric($_GET['id'])){
        $user = $this->getUser($_GET['id']);
    } else {
    $bDisplay = FALSE;
    }
} else {
    $bDisplay = FALSE;
}
//var_dump($user);
?>

<!-- formulaire de modification -->
<h3 class="title" >Informations Personnelles de <span id="profil"><?php echo $user[0]->prenom." ". $user[0]->nom?></span></h3>
<form action="?page=newsletter-ad/newsletter.php&action=updateUser" method="post">
    <div class="user-name">
        <div class="user-prenom">
            <p> Prénom* :<br /><input type="text" class="NL-prenom" name="NL-prenom" value="<?php echo $user[0]->prenom; ?>"/></p>
            <p class="NL-prenom-error" style="color:red;display:none;">Entrez votre prénom, svp</p>
        </div>
        <div class="user-nom">
            <p> Nom* :<br /><input type="text" class="NL-nom" name="NL-nom" value="<?php echo $user[0]->nom; ?>"/></p>
            <p class="NL-nom-error" style="color:red;display:none;">Entrez votre nom, svp</p>
        </div>
    </div>
    <div class="user-mail">
        <p> Email* :<br /><input type="email" class="NL-email" name="NL-email" value="<?php echo $user[0]->email; ?>"/></p>
        <p class="NL-email-error" id="NL-email-error" style="color:red;display:none;">Entrez une adresse mail valide, svp</p>
        <p class="NL-email-error" id="NL-email-error2" style="color:red;display:none;">Entrez votre adresse mail, svp</p>
    </div>

    <input type="hidden" name="NL-id" value="<?php echo $user[0]->id ?>" />

    <p><input type="submit" class="button button-primary bt-user" id="bt-user2" value="Modifier" /></p>
    <small>* champs obligatoires</small>
</form>

<!-- formulaire de suppression -->
<form action="?page=newsletter-ad/newsletter.php&action=deleteUser"method="post">
    <p><input type="hidden" name="NL-id" value="<?php echo $user[0]->id ?>" /></p>
    <p><input type="submit" class="button button-primary" id="bt-user" value="Supprimer" /></p>
</form>
<br>