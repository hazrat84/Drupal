(function($, Drupal, drupalSettings)
{
    Drupal.behaviors.timeslot = {
        attach: function (context, settings) {
            
            $('[data-drupal-selector="edit-repeat-slot"]').on('change', function (e) {
                if(this.value == 0){
                    //$('[data-drupal-selector="edit-end-date-wrapper"]').hide();
                }else{
                    //$('[data-drupal-selector="edit-end-date-wrapper"]').show();
                }
            });
            
            /*$('[data-drupal-selector="edit-start-date-0-value"]').datepicker({
                format: 'mm-dd-yyyy',
                startDate: '-0d',
                autoclose: true
            });*/
           
            
        }
    }
})(jQuery, Drupal, drupalSettings);