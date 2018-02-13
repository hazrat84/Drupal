/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($)
{//$("#edit-field-country-value").trigger("chosen:updated");
    Drupal.behaviors.b2bdirectory = {
        attach: function (context, settings) {
            
            var pathname = window.location.pathname;
            
            if(pathname === '/user/register'){
                var path_param = window.location.search.substr(1);
                if(path_param !== ""){
                    path_param_arr = path_param.split("=");
                    $("input[type='radio'][value="+ path_param_arr[1] +"]").attr('checked', 'checked');
                }
            }
            
            $("form#views-exposed-form-country-node-count-page #edit-field-country-value").chosen().change(function(){
               
                var selected_value = $(this).val();
                $.ajax({
                        url: Drupal.settings.SA.AjaxUrlgetTermNodeCountByCountry, // This is the AjAX URL set by the SiteAttention Module to save  instance name
                        method: "GET",
                        data: { country_value : selected_value }, // Pass the data to be saved in CMS DB
                        success: function(responseData) {//alert(responseData);
                           var jsonData = JSON.parse(responseData);
                           for (var key in jsonData) {
                               if (jsonData.hasOwnProperty(key)) {
                                  $("#term_"+jsonData[key].term_id).html(jsonData[key].node_count);
                               }
                            }
                            
                        }
                });
               //alert(params.selected);
            });
            
            // form#user-register-form #edit-profile-main-field-market-und
            $("select[name='profile_main[field_market][und]']").change(function(){
                var voc_value = $(this).val();
                
                $.ajax({
                        url: Drupal.settings.SA.AjaxUrlgetTermsByVocabulary, // This is the AjAX URL set by the SiteAttention Module to save  instance name
                        method: "GET",
                        data: { voc_id : voc_value }, // Pass the data to be saved in CMS DB
                        beforeSend: function(){},
                        success: function(responseData) {//alert(responseData);
                           var jsonData = JSON.parse(responseData);
                           $('select[name="profile_main[field_services][und]"]').empty();
                           
                           $.each(jsonData, function(i, d) {
                                // You will need to alter the below to get the right values from your json object.  Guessing that d.id / d.modelName are columns in your carModels data
                                $('select[name="profile_main[field_services][und]').append('<option value="' + d.tid + '">' + d.name + '</option>');
                                //$('ul.chosen-results').append('<li class="active-result" data-option-array-index="'+ d.tid + '">' + d.name + '</li>');
                            });
                           
                            //console.dir(jsonData);
                        }
                });
                
            });
            
        }
    }
})(jQuery);

