<?php
namespace Drupal\bookingform\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the booking form thank you page.
 * https://www.drupal.org/docs/8/theming/twig/create-custom-twig-templates-from-custom-module
 */
class BookingCancellationController extends ControllerBase {
    
    public function CancelBookingPage() {
        
        $booking_id = \Drupal::request()->query->get('booking_id');
        $send_mail = false;
        
        if($booking_id !=''){
            $booking_entity = \Drupal::entityManager()->loadEntityByUuid('bookinginfo', $booking_id); // Load entity by UUID
            
            if($booking_entity->get('is_canceled')->value == NULL){
                $send_mail = true;
                $booking_entity->set('is_canceled', 1); // set the status of is cancel to 1 which means that this booking is cancelled
                $booking_entity->save();
                
                $timeslot_id = $booking_entity->get('timeslot_id')->value;
                $timeslot_info = entity_load('timeslots', $timeslot_id);
                $timeslot_info->set('is_booked', 0);
                $timeslot_info->save();
            }
        }
        
        
        $output = array(
            '#theme' => 'bookingcancellation_template',
            /*'#attached' => [
                'library' => 'bookingform/bookingform.template.css',
            ]*/
        );
        return $output;
        
    }
}
