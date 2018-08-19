// javascript code for deleting subscribers
jQuery(document).ready(function () {
  jQuery('.trash').click(function () {
    var sub_id = jQuery(this).attr('data-id');
    jQuery('#subscriber_id').val(sub_id);
    jQuery('#sub_id_holder').html(sub_id);
  });
});
// javascript for filter buttons
jQuery(document).ready(function () {
  jQuery('#all').click(function (event) {
    event.preventDefault();
    jQuery('.subscribed').show();
    jQuery('.unsubscribed').show();
    jQuery('.unverified').show();
  });
  jQuery('#subscribed').click(function (event) {
    event.preventDefault();
    jQuery('.subscribed').show();
    jQuery('.unsubscribed').hide();
    jQuery('.unverified').hide();
  });
  jQuery('#unsubscribed').click(function (event) {
    event.preventDefault();
    jQuery('.unsubscribed').show();
    jQuery('.subscribed').hide();
    jQuery('.unverified').hide();
  });
  jQuery('#unverified').click(function (event) {
    event.preventDefault();
    jQuery('.unverified').show();
    jQuery('.subscribed').hide();
    jQuery('.unsubscribed').hide();
  });
});
// javascript email check and subscriber save
jQuery(document).ready(function () {
  jQuery('#successMessage').hide();
  jQuery('.fa-spinner').hide();
  jQuery("#ruaemail").focus(function (event) {
    jQuery("#ruaValidationEmailMesage").html("");
    jQuery('#ruaSubmit').attr('disabled', false);
    jQuery("#ruaemail").val('');
  });
  jQuery("#ruaSubmit").click(function () {
    jQuery('.fa-spinner').show();
    jQuery("#subscribeform").validate({
      rules: {
        ruaname: "required",
        ruaemail: {
          required: true,
          email: true
        },
      },
      messages: {
        ruaname: "Please enter your name",
        ruaemail: "Please enter a valid email address",
      },
      submitHandler: function (form) {
        var email = jQuery("#ruaemail").val();
        jQuery.ajax({
          type: 'POST',
          url: MyAjax.ajaxurl,
          data: {
            "action": "rua_email_validation",
            "ruaemail": email,
          },
          success: function (data) {
            if (data == 0) {
              if (grecaptcha === undefined) {
                alert('Recaptcha not defined');
                return;
              }

              var response = grecaptcha.getResponse();

              if (!response) {
                alert('Coud not get recaptcha response');
                return;
              }
              var reg_nonce = jQuery('#rua_blog_subscriber_nonce').val();
              var name = jQuery("#ruaname").val();
              var email = jQuery("#ruaemail").val();
              var status = jQuery("#ruasubstatus").val();
              var siteid = jQuery("#ruasiteid").val();
              var subdate = jQuery("#ruasubdate").val();
              jQuery.ajax({
                type: 'POST',
                url: MyAjax.ajaxurl,
                data: {
                  "action": "rua_save_subscriber",
                  "nonce": reg_nonce,
                  "ruaname": name,
                  "ruaemail": email,
                  "ruasubstatus": status,
                  "ruasiteid": siteid,
                  "ruasubdate": subdate,
                  "recaptcha": response,
                },
                success: function (data) {
                  jQuery('#ruaname').val('');
                  jQuery('#ruaemail').val('');
                  jQuery('#successMessage').show();
                  jQuery('#subscribe_form').hide();
                  setTimeout(function () {
                    jQuery("#subscribeform")[0].reset();
                    jQuery('#subscribe_form').show();
                    jQuery('#successMessage').hide();
                    jQuery('.fa-spinner').hide();
                    jQuery("#ruaValidationEmailMesage").html("");
                  }, 5000);
                }
              });
            }
            else {
              jQuery("#ruaValidationEmailMesage").html("<span class='val_message'>You are already subscribed to this blog. Please check your email and click confirm to activate your subscription.</span>");
              jQuery('#ruaSubmit').attr('disabled', true);
            }
          }
        });
      }
    });
  });
});
