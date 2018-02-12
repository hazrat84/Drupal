(function($, Drupal, drupalSettings)
{
    let sa_key = '';
    Drupal.behaviors.siteattention = {
        attach: function (context, settings) {
            sa_key = drupalSettings.SA.KEY;

            if(sa_key == null){
                sa_key = '';
            }

            let api_url = 'https://dev.siteattention.com/'+sa_key;

            if(sa_key != ''){
                /*$.ajax({
                    type : "POST",
                    url : api_url,
                    data : {
                        func: 'info'
                    },
                    beforeSend: function(xhr){xhr.setRequestHeader('X-SiteAttention', sa_key);},
                    success : function(data) {
                        console.log('Response ' + JSON.stringify(data));
                        $('#name').html(data.name);
                        $('#email').html('<a href="mailto:'+data.email+'">'+data.email+'</a>');
                        $('#companyname').html(data.company);
                        $('#license').html(data.license);
                        $('#active').html(data.active);
                        $('#limit').html(data.limit);
                        $('#updated_date').html(data.updated);
                        $('#expires_date').html(data.expires);
                    }
                });*/
            }

            // Save Instance Name on Live and Local DB
            $("#edit-sa-iname-submit").click(function(e) {
                e.preventDefault();
                updateIname();
                let sa_iname = $('#edit-siteattention-iname').val();
                let sa_iid   = $('#edit-siteattention-iid').val();
                let sa_key   = drupalSettings.SA.KEY;
return;
                if(sa_key == null || sa_key == ''){
                    alert(Drupal.t("Without Key Instance Name can't be saved."));
                    return;
                }
                if(sa_iname != ''){ // New Instance Name should not be empty
                    console.log('Ajax Request');
                    /*$.ajax({
                        type : "POST",
                        url : api_url,
                        data : {
                            func: 'iname',
                            iid:  sa_iid,
                            name: sa_iname
                        },
                        beforeSend: function(xhr){xhr.setRequestHeader('X-SiteAttention', sa_key);},
                        success : function(responseData) {
                            console.log('Response ' + JSON.stringify(responseData));
                            if(responseData.success == true){
                                updateIname(); // callback to save instance name in cms db
                            }
                        }
                    });*/

                }else{
                    alert(Drupal.t('Please Enter a value for Instance Name'));
                }
            });

            function updateIname(){
                let sa_iname = $('#edit-siteattention-iname').val();

                    $.ajax({
                        url: Drupal.url('siteattention/save/afterinstance/'), // This is the AjAX URL set by the SiteAttention Module to save  instance name
                        type: "GET",
                        data: { instance_name : sa_iname }, // Pass the data to be saved in CMS DB
                        success: function(response) {
                            if(response.success == 1){
                                
                            }
                        }
                    });
            }

        } // End attach
    };

})(jQuery, Drupal, drupalSettings);
