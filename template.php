<p class="NL-error" id="NL-global-error" style="color:red;font-size: 18px;font-weight: bold;display:none;"><br>une erreur s'est produite<br></p>
<p class="NL-succes" id="NL-ajout-ok" style="color:green;font-size: 18px;font-weight: bold;display:none;"><br>L'abonné(e) a bien été ajouté(e)<br></p>
<p class="NL-succes" id="NL-modif-ok" style="color:green;font-size: 18px;font-weight: bold;display:none;"><br>L'abonné(e) a bien été modifié(e)<br></p>
<p class="NL-succes" id="NL-sup-ok" style="color:green;font-size: 18px;font-weight: bold;display:none;"><br>L'abonné(e) a bien été supprimé(e)<br></p>

<div class="wrap">
    <h2>Newsletter</h2>
</div>


<div id="placecode">
    Copiez (ctrl+c) le code et collez-le (ctrl+v) dans la page ou l'article pour insérer le formulaire d'inscription :
    <input id="code-user" type="text" value="[newsletter_ad]" readonly="readonly" />
</div>
<br>
<p>Vous pouvez envoyer un message à tout vos abonné(e)s avec le formulaire ci-dessous,</p>
<p>ou récupérer la liste de leurs emails un peu plus bas sur cette page, afin envoyer le message depuis votre boite mail.</p>
<br>
<p>Vous pouvez également ajouter, modifier ou supprimer un(e) abonné(e).</p>
<br>
<p>La désinstallation du plugin entrainera la suppression de toutes les données.</p>
<br>
<hr>

<div>
    <H3>Envoyer un message à tous vos abonné(e)s</H3>
    <form method="post">
        <p class="NL-error" id="NL-envoi-error" style="color:red;font-weight: bold;font-size: 18px;display:none;">une erreur s'est produite<br></p>
        <p class="NL-succes" id="NL-envoi-succes" style="color:green;font-weight: bold;font-size: 18px;display:none;">message envoyé<br></p>
        <p>Écrivez le message à envoyer à vos abonné(e)s</p>
        <br>
        <p><input id="sujet-nl" name="sujet-nl" type="text" size="60" placeholder="sujet" required></p>
        <p><textarea id="news" name="news" rows="4" cols="100" placeholder="message" required></textarea></p>
        <p><input type="submit" class="button button-primary" name="send-nl" id="send-nl" value="Envoyer" /></p>
    </form>
</div>

<br>
<hr>

<div id="contentUser">
     <h3 class="title" >Ajouter un(e) abonné(e) :</h3>
     <form action="?page=newsletter-ad/newsletter.php&action=createUser" method="post">

         <div class="user-name">
             <div class="user-prenom">
                 <p> Prénom* :<br /><input type="text" class="NL-prenom" id="NL-prenom" name="NL-prenom" required/></p>
                 <p class="NL-prenom-error" id="NL-prenom-error" style="color:red;display:none;">Entrez votre prénom, svp</p>
             </div>
             <div class="user-nom">
                 <p> Nom* :<br /><input type="text" class="NL-nom" id="NL-nom" name="NL-nom" required /></p>
                 <p class="NL-nom-error" id="NL-nom-error" style="color:red;display:none;">Entrez votre nom, svp</p>
             </div>
         </div>

         <div class="user-mail">
             <p> Email* :<br /><input type="email" class="NL-email" id="NL-email" name="NL-email" required/></p>
             <p class="NL-email-error" id="NL-email-error" style="color:red;display:none;">Entrez une adresse mail valide, svp</p>
             <p class="NL-email-error" id="NL-email-error2" style="color:red;display:none;">Entrez votre adresse mail, svp</p>
         </div>

         <p><input type="submit" class="button button-primary"  id="bt-user" value="Enregistrer" /></p>
         <small><span class="required">*</span> champs obligatoires</small>
     </form>
</div>

<br>
<hr>

<div id="menu-user">
    <h3>Vous avez <?php $nbUser = $this->nbUser(); echo $nbUser;?> abonné(e)s</h3>

    <div class="list-mail-user">
        <h3>Liste des mails des abonné(e)</h3>
        <p>Copiez (ctrl+c) la liste et collez-la (ctrl+v) dans le champ destinataire de votre boite mail pour envoyer un message à tous vos abonné(e)s</p>
        <?php
        $list = $this->getUserMails();
        //var_dump($list);
        $chaine = '';
        foreach($list as $getUser){
            $chaine .= $getUser->email.", ";
        }
        if (trim($chaine) != '') {
            $chaine = substr($chaine, 0, -2);
        }
        ?>
        <textarea id="list-mail" rows="4" cols="50" readonly="readonly"><?php echo $chaine; ?> </textarea>
        <br>
        <br>
    </div>

    <div class="recherche-mot-cle">
        <h3>Recherche par mot-clé</h3>
        <form action="?page=newsletter-ad/newsletter.php&action=searchUser" method="post">
            <p>Entrez un nom, prénom ou email :<br><input id="NL-keyword" name="NL-keyword"/></p>
            <p><input type="submit" class="button button-primary" id="bt-user" value="Rechercher"/></p>
        </form>
        <br>
    </div>

    <div class="list-user">
        <h3 class="title" >Liste des abonné(e)s :</h3>

        <ul id="list-user">
            <?php
            //var_dump($keyword);
            //selon si recherche ou non:
            //$userlist=$this->getUserByKeyword($keyword);
            //ou liste complète
            $resultSearch = $this->getUserByKeyword();

            if (isset($resultSearch)) {
                $userList= $resultSearch;
                echo '<p><a href="?page=newsletter-ad/newsletter.php">Retour à la liste complète</a></p><br>';
            } else {
                $userList=$this->getUserList();
            }
            //var_dump($userList);
            foreach($userList as $getUser){
                //var_dump($getUser);
                echo "<li><a href=\"?page=newsletter-ad/newsletter.php&p=user&id=".$getUser->id."\">".$getUser->email."</a> (".$getUser->prenom." ".$getUser->nom.")</li>";
            }
            ?>
        </ul>
    </div>
</div><!--fin #menumap-->