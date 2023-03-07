jQuery(document).ready(function(){

   jQuery('#user_form_submit').click(function(){
        let nom = jQuery('#NL-nom-front').val().trim();
        let prenom = jQuery('#NL-prenom-front').val().trim();
        let email = jQuery('#NL-email-front').val().trim();
        let pdc = jQuery('#validation-pdc');

        let rx2 = /^[\w\.\-\_]+@([\w-]+\.)+[\w-]{2,4}$/;
        let ok = (rx2.exec(email));

        if(nom=='') {
            jQuery('#NL-nom-error-front').show();
        } else {
            jQuery('#NL-nom-error-front').hide();
        }

        if(prenom=='') {
            jQuery('#NL-prenom-error-front').show();
        } else {
            jQuery('#NL-prenom-error-front').hide();
        }

        if(email=='') {
            jQuery('#NL-email-error-front2').show();
        } else if (!ok) {
            jQuery('#NL-email-error-front').show();
        } else {
            jQuery('#NL-email-error-front2').hide();
            jQuery('#NL-email-error-front').hide();
        }

       if(!(pdc.is(':checked'))) {
           jQuery('#NL-pdc-error-front').show();
       } else {
           jQuery('#NL-pdc-error-front').hide();
       }


        if((nom!='')&&(prenom!='')&&(email!='')&&(ok)&&(pdc.is(':checked'))) {
            jQuery('form').submit();
        } else {
            return false;
        }
    });

});
