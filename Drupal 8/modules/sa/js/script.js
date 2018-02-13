(function(Drupal, drupalSettings)
{
    let sa_key = '';
    Drupal.behaviors.siteattention = {
        attach: function (context, settings) {
            sa_key = drupalSettings.SA.KEY;
            if(sa_key == null){
                sa_key = '';
            }
            
            let api_url = 'https://dev.siteattention.com/'+sa_key;
            
            SiteAttentionDemoCMSModule ( { url : api_url } );

        }
    };

})(Drupal, drupalSettings);
