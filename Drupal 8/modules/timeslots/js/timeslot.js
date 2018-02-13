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
            
            $('[data-drupal-selector="edit-start-date-0-value"]').on('change',function(){
                $('[data-drupal-selector="edit-end-date-0-value"]').val($('[data-drupal-selector="edit-start-date-0-value"]').val());
            });
            
            $('[data-drupal-selector="edit-start-date-0-value"]').blur();
            
            $(function(){
                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    startDate: '0d',
                    autoclose: true
                });
            });
            
            
            $(document).ready(function(){
                $(":button.button--danger").attr('disabled',true);
                // /timeslots/138/delete
                $(document).off().on('click', ":button.button.button--danger",  function (e) {
                    var form_action = $('form.timeslots-edit-form').attr('action');
                    form_action = form_action.replace('edit', 'delete');
                    form_action = form_action.replace('/en/', '');
                    console.log(Drupal.url(form_action));
                    var url_arr = form_action.split("/");
                    console.log(url_arr[1]);
                    bootbox.confirm("Are you sure want to delete?", function(result) {
                        if(result == true){
                            var ids = new Array();
                            ids[0] = url_arr[1];
                            jQuery.ajax({
                                url: Drupal.url("timeslots/multipledelete"),
                                type: "POST",
                                data: { id : ids },
                                //dataType: "json"
                                success: function(response) {

                                    if(response.success === 1){
                                        //alert(response);
                                        window.location.reload();
                                    }
                                }
                            });
                            /*jQuery.ajax({
                                url: Drupal.url(form_action),
                                type: "GET",
                                //data: { id : ids },
                                //dataType: "json"
                                success: function(response) {
                                    window.location.reload();
                                    
                                }
                            });*/
                        }
                    });
                });
            });
            
           
        }
    }
})(jQuery, Drupal, drupalSettings);