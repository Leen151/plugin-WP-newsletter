jQuery(document).ready(function(){

        jQuery("#code-user").click(function(){
            this.select();
        });

        jQuery("#list-mail").click(function(){
            this.select();
        });

        jQuery('#bt-user').click(function(){
            let nom = jQuery('#NL-nom').val().trim();
            let prenom = jQuery('#NL-prenom').val().trim();
            let email = jQuery('#NL-email').val().trim();

            let rx = /^[\w\.\-\_]+@([\w-]+\.)+[\w-]{2,4}$/;
            let ok = (rx.exec(email));

            if(nom=='') {
                jQuery('#NL-nom-error').show();
            } else {
                jQuery('#NL-nom-error').hide();
            }

            if(prenom=='') {
                jQuery('#NL-prenom-error').show();
            } else {
                jQuery('#NL-prenom-error').hide();
            }

            if(email=='') {
                jQuery('#NL-email-error2').show();
            } else if (!ok) {
                jQuery('#NL-email-error').show();
            } else {
                jQuery('#NL-email-error2').hide();
                jQuery('#NL-email-error').hide();
            }

            if((nom!='')&&(prenom!='')&&(email!='')&&(ok)) {
                jQuery('form').submit();
            } else {
                return false;
            }
        });
});
