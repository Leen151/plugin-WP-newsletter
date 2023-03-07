<?php
/*
Plugin Name: Newsletter
Description: Plugin de newsletter
*/

if (!class_exists("Newsletter"))
{
    class Newsletter
    {
        private $table;
        private $url;

        function __construct() {
            /* objet $wpdb de Wordpress permet de se connecter à la base de données */
            global $wpdb;

            // vaudra 'wp_newsletter' si le préfixe de table configuré à l'installation est 'wp_' (celui par défaut)
            $this->table = $wpdb->prefix.'newsletter';

            // Définit l'url vers le fichier de classe du plugin
            $this->url = get_bloginfo("url")."/wp-admin/options-general.php?page=newsletter-ad/newsletter.php";
        } // -- __construct()


        // Fonction déclenchée à l'activation du plugin
        function newsletter_install() {
            global $wpdb;

            /* fonction get_var() :
            * exécute une requête SQL et retourne une variable
            * ici get_var() retourne NULL car la table n'existe pas
            */

            // On s'assure que la table n'existe pas déjà ('!=')
            if ($wpdb->get_var("SHOW TABLES LIKE '".$this->table."'") != $this->table) {
                $sql = "CREATE TABLE ".$this->table."
                         (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                         `nom` VARCHAR(50) NOT NULL,
                         `prenom` VARCHAR(50) NOT NULL,  
                         `email` VARCHAR(100) NOT NULL UNIQUE                         
                         ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

                /* Inclusion du fichier 'upgrade.php' nécessaire car c'est lui qui contient le code
                * de la fonction dbDelta utilisée à la ligne suivante
                * ABSPATH = chemin absolu vers le répertoire du projet = 'C:\wamp\www\wordpress/'
                */
                if (require_once(ABSPATH."wp-admin/includes/upgrade.php")) {
                    //La fonction dbDelta() applique les changements de structure sur les objets de la base (tables, colonnes...)
                    dbDelta($sql);
                }
            }
        } // -- newsletter_install()


        // Fonction déclenchée lors de la désactivation du plugin --> externalisée dans uninstall.php (la bdd est supprimé à la suppression et plus à la désactivation
//        function newsletter_uninstall() {
//            global $wpdb;
//
//            // On s'assure que la table existe
//            // ici, get_var() retourne le nom de la table, par exemple 'wp_osm'
//            if ($wpdb->get_var("SHOW TABLES LIKE '".$this->table."'") == $this->table) {
//                // On la supprime
//                // ATTENTION : pensez à sauvegarder les données au préalable si nécessaire
//                $wpdb->query("DROP TABLE `".$this->table."`");
//            }
//        } // -- newsletter_uninstall()


        function init() {
            if (function_exists('add_options_page')) {
                /* fonction add_options_page() : ajout d'un lien (sous-menu) dans le menu 'Réglages' de l'administration
                * + fonction qui doit être lancée quand on clique sur ce lien, ici newsletter_admin_page()
                *
                * Ici $sPage vaut 'settings_page_newsletter/newsletter'
                */
                $sPage = add_options_page('Newsletter', 'Newsletter', 'administrator', __FILE__, array($this, 'newsletter_admin_page'));

                // Créer un hook 'load-settings_page_newsletter/newsletter' qui appelle la fonction newsletter_ admin_header()
                add_action("load-".$sPage, array($this, "newsletter_admin_header"));
            }
        }// -- init()

        // Charge le css et js du plugin
        function newsletter_admin_header() {
            wp_register_style('newsletter_css', plugins_url('css/admin-newsletter.css', __FILE__));
            wp_enqueue_style('newsletter_css');
            wp_enqueue_script('newsletter_js', plugins_url('js/admin-newsletter.js', __FILE__), array('jquery'));
        } // -- newsletter_admin_header()


        // Gestion des pages/formulaires dans l'administration
        function newsletter_admin_page() {

            //$page = null si $_GET['p'] n'est pas défini
            $page= $_GET['p'] ?? null;
            if($page =='user') {
                require_once('template-user.php');
            }else {
                require_once('template.php');
            }

            // envoie un message à tous les abonnés
            // déclenché par le bouton du formulaire
            if(isset($_POST['send-nl'])){
                //récupération des mails des abonné(e)s
                $list = $this->getUserMails();

                //si la liste n'est pas vide
                if($list != ''){
                    //pour chaque adresse mail, envoie du message
                    foreach($list as $getUser) {
                        //mail de l'admin
                        //$from = get_bloginfo('admin_email');
                        //destinataire
                        $to = $getUser->email;
                        //sujet
                        $subject =  sanitize_text_field($_POST['sujet-nl']);
                        //message
                        $messageNL = sanitize_textarea_field($_POST['news']);

                        if(($to != '')&&($subject != '')&&($messageNL !='')){
                            wp_mail($to, $subject, $messageNL);
                            // rend visible le message de succès
                            echo "<script> jQuery('#NL-envoi-succes').show(); </script>";
                        }
                        else{
                            // rend visible le message d'erreur
                            echo "<script> jQuery('#NL-envoi-error').show(); </script>";
                        }
                    }
                }
            }

            //création d'abonné(e) par admin
            if(isset($_GET['action'])) {
                if ($_GET['action'] == 'createUser') {
                    if ((trim($_POST['NL-nom']) != '') && (trim($_POST['NL-prenom']) != '') && (trim($_POST['NL-email']) != '')) {
                        // L’appel d’une méthode à l’intérieur d’une autre méthode doit se faire en utilisant $this, qui fait référence à l’instance de classe
                        $insertUser = $this->insertUser($_POST['NL-nom'], $_POST['NL-prenom'], $_POST['NL-email']);

                        //redirection sur la même page en vidant les champs (fct js "window.location")
                        if ($insertUser == true) {
                            echo '<script> window.location = "' . $this->url . '&user=ok' . '"; </script>';
                        } else {
                            echo "<script> jQuery('#NL-global-error').show(); </script>";
                        }
                    } else {
                        echo '<p style="color:red;">Veuillez remplir tous les champs </p>';
                    }
                }
                // modification d'abonné par admin
                else if ($_GET['action'] == 'updateUser') {
                    if ((trim($_POST['NL-nom']) != '') && (trim($_POST['NL-prenom']) != '') && (trim($_POST['NL-email']) != '') && (trim($_POST['NL-id']) != '')) {
                        $updateUser = $this->updateUser($_POST['NL-id'], $_POST['NL-nom'], $_POST['NL-prenom'], $_POST['NL-email']);

                        if ($updateUser == true) {
                            echo '<script> window.location = "' . $this->url . '&user=updateok' . '"; </script>';
                        }else {
                            echo "<script> jQuery('#NL-global-error').show(); </script>";
                        }

                    } else {
                        echo '<p style="color:red;">Veuillez remplir tous les champs</p>';
                    }
                }
                //suppression d'abonné par admin
                else if ($_GET['action'] == 'deleteUser') {
                    if (trim($_POST['NL-id']) != '') {
                        $deleteUser = $this->deleteUser($_POST['NL-id']);

                        if ($deleteUser == true) {
                            echo '<script> window.location = "' . $this->url . '&user=deleteok' . '"; </script>';
                        } else {
                            echo "<script> jQuery('#NL-global-error').show(); </script>";
                        }
                    }
                }
            }

            if(isset($_GET['user'])){
                if($_GET['user']=='ok'){
                    echo "<script> jQuery('#NL-ajout-ok').show(); </script>";
                } else if($_GET['user']=='deleteok'){
                    echo "<script> jQuery('#NL-sup-ok').show(); </script>";
                } else if($_GET['user']=='updateok') {
                    echo "<script> jQuery('#NL-modif-ok').show(); </script>";
                }
            }
        }  // -- newsletter_admin_page()


        //fct d'insertion des données
        function insertUser($nom,$prenom,$email): bool {
            global $wpdb;
            //$table_user= $wpdb->prefix.'newsletter';

            $sql=$wpdb->prepare(
                " INSERT INTO ".$this->table." (nom, prenom, email) VALUES (%s,%s,%s ) ",
                $nom,
                $prenom,
                $email
            );

            if ($wpdb->query($sql))
            {
                return true;
            }

            return false;
        } // -- insertUser()

        //liste des abonné(e)s
        function getUserList(){
            global $wpdb;

            $sql = $wpdb->prepare("SELECT * FROM ".$this->table." ORDER BY email", "");
            return $wpdb->get_results($sql);
        } // -- getUserList()

        //récupère les adresses mail des abonné(e)s
        function getUserMails(){
            global $wpdb;

            $sql = $wpdb->prepare("SELECT (email) FROM ".$this->table, "");
            return $wpdb->get_results($sql);
        } // -- getUserMails()


        // récupère un abonné en fonction de son id
        function getUser($id){
            global $wpdb;

            $sql = $wpdb->prepare("SELECT * FROM ".$this->table." WHERE id=%d LIMIT 1",$id);

            return $wpdb->get_results($sql);
        } // -- getUser()

        // Modification d'un(e) abonné(e)
        function updateUser($id, $nom, $prenom, $email) {
            global $wpdb;

            $sql = $wpdb->prepare("UPDATE ".$this->table." SET
                            nom = %s,
                            prenom = %s,
                            email = %s
                            WHERE id = %d",
                            $nom,
                            $prenom,
                            $email,
                            $id);

            if ($wpdb->query($sql)) {
                return true;
            }

            return false;
        } // -- updateUser()

        // Suppression d'une carte en base
        function deleteUser($id) {
            global $wpdb;

            $sql = $wpdb->prepare("DELETE FROM ".$this->table." WHERE id=%d LIMIT 1", $id);

            if ($wpdb->query($sql)){
                return true;
            }

            return false;
        } // -- deleteUser()

        // nombre d'abonné(e)s
        function nbUser(){
            global $wpdb;

            $sql = $wpdb->prepare("SELECT COUNT(email) as count FROM ".$this->table);
            $nb =$wpdb->get_results($sql);

            return $nb[0]->count;
        } // -- nbUser()

        // recherche d'abonné(e) par le nom, prénom ou email (ignore les maj)
        function getUserByKeyword(){
            global $wpdb;

            if(isset($_GET['action'])) {
                if ($_GET['action'] == 'searchUser') {
                    if ((trim($_POST['NL-keyword']) != '')) {
                        $keyword = $_POST['NL-keyword'];
                        $keyword1 = strtolower($keyword);
                        $keyword2 = "%".$keyword1."%";

                        $sql = $wpdb->prepare("SELECT * FROM ".$this->table." WHERE nom like '%s' or prenom like '%s' or email like '%s'",$keyword2,$keyword2,$keyword2);
                    }
                }
            }

            //retourne une liste
            return $wpdb->get_results($sql);
        } // -- getUserByKeyword()


        // fonction pour le shortcode
        function nl_shortcode(){
//            //récupère le fichier template
//            $file = WP_PLUGIN_DIR.'/newsletter-ad/template-front.php';
//            var_dump($file);
//            $file2 = plugins_url('/template-front.php', __FILE__);
//            var_dump($file2);
//            //lit le fichier pour ensuite le retourner
//            $templateFront = file_get_contents($file);
//            return $templateFront;
            $content = '';

            $content .= '<div id="contentUser">';

            $content .= '<h3 class="title-nl" >S\'inscrire à la newsletter</h3>';
            $content .='<p class="form-info">Les champs marqués d\'un <span class="required">*</span> sont obligatoires</p>';

            $content .= '<form method="post" action=""> ';

            //sert à vérifier que les données viennent bien du formulaire de notre site (sécurité)
            $content .= wp_nonce_field('inscription-subscriber', 'inscription-verif');

            $content .='<div class="user-name">';

            $content .='<div class="user-prenom">';
            $content .='<p><label> Prénom<span class="required">*</span> </label><br /><input type="text" class="NL-prenom" id="NL-prenom-front" name="NL-prenom-front" maxlength="50" required/></p>';
            $content .='<p class="NL-error-front" id="NL-prenom-error-front" style="color:red;display:none;">Ce champ est obligatoire.</p>';
            $content .='</div>';

            $content .='<div class="user-nom">';
            $content .='<p><label> Nom<span class="required">*</span> </label><br /><input type="text" class="NL-nom" id="NL-nom-front" name="NL-nom-front" maxlength="50" required /></p>';
            $content .='<p class="NL-error-front" id="NL-nom-error-front" style="color:red;display:none;">Ce champ est obligatoire.</p>';
            $content .='</div>';

            $content .='</div>';

            $content .='<div class="user-mail">';
            $content .='<p><label> Email<span class="required">*</span> </label><br /><input type="email" class="NL-email" id="NL-email-front" name="NL-email-front" maxlength="100" required/></p>';
            $content .='<p class="NL-error-front" id="NL-email-error-front" style="color:red;display:none;">Entrez une adresse mail valide, svp</p>';
            $content .='<p class="NL-error-front" id="NL-email-error-front2" style="color:red;display:none;">Ce champ est obligatoire.</p>';
            $content .='</div>';

            $content .='<div>';
            $content .='<p><input type="checkbox" id="validation-pdc" name="validation-pdc" required/><label> Accepter la <a href="<?php echo site_url()?>/politique-de-confidentialite/">politique de confidentialité</a> du site<span class="required">*</span></label> <br></p>';
            $content .='<p class="NL-error-front" id="NL-pdc-error-front" style="color:red;display:none;">Ce champ est obligatoire.</p>';
            $content .='</div>';

            $content .='<p><input type="submit" class="button button-primary" id="user_form_submit" name="user_form_submit" value="S\'inscrire" /></p>';


            $content .='</form>';
            $content .='</div>';

            return $content;
            //var_dump($content);
        }


        function user_form_capture() {
            if(isset($_POST['user_form_submit'])){
                if (wp_verify_nonce($_POST['inscription-verif'], 'inscription-subscriber')) {
                    $prenom = sanitize_text_field($_POST['NL-prenom-front']);
                    $nom = sanitize_text_field($_POST['NL-nom-front']);
                    $email = sanitize_text_field($_POST['NL-email-front']);

                    if (($nom != '') && ($prenom != '') && ($email != '')) {
                        $insertUser = $this->insertUser($nom, $prenom, $email);
                        if ($insertUser == true) {
                            echo '<script> window.location = "'.site_url().'/thanks'.'"; </script>';
                        } else {
                            echo "Une erreur s'est produite.";
                        }
                    } else {
                        echo "Veuillez remplir tous les champs";
                    }
                }
            }
        }

        function newsletter_front_header()
        {
            wp_enqueue_style('front_nl_css', plugins_url('css/front-newsletter.css', __FILE__));
            wp_enqueue_script('front_nl_js', plugins_url('js/front-newsletter.js', __FILE__), array('jquery'));
        } // -- newsletter_front_header()


    } // -- classe
} // -- class_exists()

// Instanciation
if (class_exists("Newsletter"))
{
    $news = new Newsletter();
}

// Si instance créée
if (isset($news))
{
    // Sur l'action 'Activer le plugin', exécution de la fonction osm_install()
    // register_activation_hook()
    register_activation_hook(__FILE__, array($news, 'newsletter_install'));

    // Sur l'action 'Désinstaller le plugin', exécution de la fonction osm_uninstall()
    //register_deactivation_hook(__FILE__, array($news, 'newsletter_uninstall'));

    // fct qui s'execute quand on affiche le menu admin (affichage du plugin dans le menu)
    add_action('admin_menu', array($news, 'init'));

    //
    add_action('wp_head', array($news, 'user_form_capture'));

    // Ajout du chargement des scripts définis dans la fonction newsletter_front_header()
    add_action('wp_enqueue_scripts', array($news, 'newsletter_front_header'));

    //ajout du shortcode
    add_shortcode('newsletter_ad', array($news, 'nl_shortcode'));
} // -- fin si objet créé