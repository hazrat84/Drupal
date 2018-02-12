(function($, Drupal, drupalSettings)
{
    Drupal.behaviors.bookingform = {
        attach: function (context, settings) {
            $('form#bookinginfo-booking-display-form .selectpicker').wrap("<div class='select-style selectpicker form-control'></div>");
            $('#edit-number-of-sessions').val(1);
            var tutors_price = parseInt($('#price').val()), commission = tutors_price * 20/100, total_price = 0, data_fee = 0.01, session_length = 0, service_fee = 0, session_price = 0, total_price = 0;
            
            $("#price_on_video").html(tutors_price.toFixed(2));           
            $('#session_price').html(tutors_price);
            $('.field--name-session-amount').val(tutors_price); // put session total price in hidden field
            $("#session_single_price").html(tutors_price);
            session_length = $('#session_length').val();
            $('#session_lenth').html(session_length);
            var transmission_fee = session_length * data_fee;
            $('#transmission_fee').html(transmission_fee);
            service_fee = commission * tutors_price;
            session_price = tutors_price + transmission_fee;
            var first_total = commission + tutors_price + transmission_fee; // when user first come on the page without seelct session amount
            $("#total_price").html(first_total);
           
            $('#comm_fee').html(commission);
            $('.field--name-commission-fee').val(commission);
            
            $('#edit-number-of-sessions').on('change', function (e) {
                
                var num_of_Session = this.value;
                num_of_Session = (num_of_Session == '_none')? '0': num_of_Session;
                
                var session_total = num_of_Session * tutors_price;
                total_commission = session_total * 20/100;
                $('.field--name-commission-fee').val(total_commission); // commission on total
                $('#comm_fee').html(total_commission);
                
                $('#num_of_session').html(num_of_Session);
                total_price = (commission + session_price) * num_of_Session;
                total_price = total_price.toFixed(2);
                //total_price = (service_fee * session_price) * num_of_Session;
                $('.field--name-session-amount').val(session_total); // put session total price in hidden field
                $('#session_price').html(session_total);
                
                $('#edit-session-amount-wrapper div label').html(num_of_Session + " Session X " + tutors_price);
                total_transmission_fee = 0;
                total_transmission_fee = transmission_fee * num_of_Session;
                $('#transmission_fee').html(total_transmission_fee);
                total_session_length = 0;
                total_session_length = session_length * num_of_Session;
                $('#session_lenth').html(total_session_length);
                
                $("#total_price").html(total_price);
            });
            
            $('.stripe-button-el span').css('width','200px');
            $('.stripe-button-el span').css('height','40px');
            $('.stripe-button-el span').css('padding-right','40px');
            $('.stripe-button-el span').css('padding-left','40px');
            $('.stripe-button-el span').css('padding-top','5px');
            $('.stripe-button-el span').css('padding-bottom','5px');
            $('.stripe-button-el span').css('font-size','16px');
            $('.stripe-button-el').attr('disabled',true);
            $('#accept_payment_terms').attr('checked', false); // Unchecks it
            $('#accept_payment_terms').on('click', function (e) {
                if($(this).is(':checked')){
                    $('.stripe-button-el').attr('disabled',false);
                    $('.stripe-button-el').css('background','#D96B3B');
                    $('.stripe-button-el span').css('background','#D96B3B');
                }else{
                    $('.stripe-button-el').attr('disabled',true);
                    $('.stripe-button-el').css('background','#F8F9FA');
                    $('.stripe-button-el span').css('background','#F8F9FA');
                    $('.stripe-button-el').css('border','solid 1px #BABBBD');
                }
                
            });
            
            $('#cancel_btn').off().on('click',function(e){
                e.preventDefault();
                bootbox.confirm("Are you sure you want to cancel Booking?", function(result) {
                    if(result == true){
                        var booking_id = $('#booking_id').val();
                        window.location = window.location.origin + '/' + 'cancel/booking?booking_id=' + booking_id ;
                    }
                });
            });
            
            var page_path = $(location).attr('pathname');
            if(page_path === "/booking/gotopayment"){
                
                // Load login popup on gotopayment if user is not logged in
                if($('#login_link').hasClass('login-popup-form')){
                    $('#login_link').click();
                    
                }
                
                window.onload = function(){$("button.ui-dialog-titlebar-close").hide(); $("div.ui-dialog").css("z-index", "10000");};
                $("button.ui-dialog-titlebar-close").hide();
            }
            
                
           
        }, // End Attach
        
        
        // function for getting Information about TimePeriods 
        getTimePeriodsInfo:function(start_timestamp, end_timestamp, timeslot_id, element_id){
            $('ul li').removeClass('active');
            $(element_id).addClass('active');
            $('#period_start').val(start_timestamp);
            $('#period_end').val(end_timestamp);
            $('#timeslot_id').val(timeslot_id);
        },
        
        requestBookingbtn:function(){
            var period_start = '', period_end = '', timeslot_id = '';
            period_start = $('#period_start').val();
            period_end = $('#period_end').val();
            timeslot_id = $('#timeslot_id').val();
            if(period_start == '' || period_end == '' || timeslot_id == ''){
                bootbox.alert('Please Select a Slot');
                event.preventDefault();
            }
        }
        
    };
})(jQuery, Drupal, drupalSettings);