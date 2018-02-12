<?php
namespace Drupal\bookingform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
/**
 * Provides route responses for the booking form thank you page.
 * https://www.drupal.org/docs/8/theming/twig/create-custom-twig-templates-from-custom-module
 */
class ThankyouController extends ControllerBase {
    
    public function Thankyoupage() {
        
        $tempstore = \Drupal::service('user.private_tempstore')->get('booking_data');
        
        $language_code = $tempstore->get('available_language');
        $session_id    = $tempstore->get('session_id');
        $tutor_id      = $tempstore->get('tutor_id');
        
        if($session_id != NULL){
            $period_start = \Drupal::request()->query->get('period_start');
            $period_end   = \Drupal::request()->query->get('period_end');
            $timeslot_id  = \Drupal::request()->query->get('timeslot_id');
            
            $timeslot_info = entity_load('timeslots', $timeslot_id);
            
            $startdate_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('start_time')->value));
            $enddate_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('end_time')->value));
            $start_date =  $timeslot_info->get('start_date')->value;
            $end_date =  $timeslot_info->get('end_date')->value;
            
            if($startdate_obj->format('i') == 01){
               $startdate_obj->modify("-1 minute");
            }
            
            if($enddate_obj->format('i') == 01){
               $enddate_obj->modify("-1 minute");
            }
            
            $startTime = $startdate_obj->getTimestamp(); 
            $endTime   = $enddate_obj->getTimestamp();
            
            $new_slots_arr = array();
            if($startTime == $period_start){
                // the first tiemslot is chosen
                $new_slots_arr[0]['starttime'] = $period_start;
                $new_slots_arr[0]['endtime']   = $period_end;
                $new_slots_arr[0]['slot_for_booking'] = 1;
                if($period_end != $endTime){
                    $new_slots_arr[1]['starttime'] = $period_end;
                    $new_slots_arr[1]['endtime']   = $endTime;
                    $new_slots_arr[1]['slot_for_booking'] = 0;
                }
                
            }else{ // if not equal means that period_start > starttime
                if($period_end == $endTime){ // if endperiod == endtime means in this case we will have two timeslots
                   
                    $new_slots_arr[0]['starttime'] = $startTime;
                    $new_slots_arr[0]['endtime'] = $period_start;
                    $new_slots_arr[0]['slot_for_booking'] = 0;
                    
                    // the end tiemslot is chosen
                    $new_slots_arr[1]['starttime'] = $period_start;
                    $new_slots_arr[1]['endtime'] = $period_end;
                    $new_slots_arr[1]['slot_for_booking'] = 1;
                    
                }else{
                    $new_slots_arr[0]['starttime'] = $startTime;
                    $new_slots_arr[0]['endtime'] = $period_start;
                    $new_slots_arr[0]['slot_for_booking'] = 0;
                    // the middle timeslot is chosen
                    $new_slots_arr[1]['starttime'] = $period_start;
                    $new_slots_arr[1]['endtime'] = $period_end;
                    $new_slots_arr[1]['slot_for_booking'] = 1;
                    
                    $new_slots_arr[2]['starttime'] = $period_end;
                    $new_slots_arr[2]['endtime'] = $endTime;
                    $new_slots_arr[2]['slot_for_booking'] = 0;
                }
            }
            
            foreach($new_slots_arr as $new_slot){
               
                $starting_time = $new_slot['starttime'];
                $ending_time   = $new_slot['endtime'];
                
                $entity = entity_create('timeslots'); 
                $entity->set('start_date', $start_date);
                $entity->set('start_time', $starting_time);
                $entity->set('end_time', $ending_time);
                $entity->set('repeat_slot', $timeslot_info->get('repeat_slot')->value);
                $entity->set('end_date', $end_date);
                $entity->set('is_booked', $new_slot['slot_for_booking']);
                $entity->set('tutor_id', $tutor_id);
                $entity_obj = $entity->save();
                if($new_slot['slot_for_booking'] == 1){
                    $new_timeslot_id = $entity->get('id')->value;
                }
                
            }
            
            
            $entity = entity_create('bookinginfo'); 
            $entity->set('number_of_sessions', $tempstore->get('number_of_sessions')); // how many sessions
            $entity->set('available_language',  $tempstore->get('available_language'));
            $entity->set('session_amount', $tempstore->get('session_amount')); // Total Price of the session
            $entity->set('commission_fee', $tempstore->get('commission_fee'));
            $entity->set('tutor_id', $tutor_id);
            $entity->set('student_id', \Drupal::currentUser()->id());
            $entity->set('session_id', $session_id);
            $entity->set('timeslot_id', $new_timeslot_id);
            $entity->save();
            $book_uuid = $entity->get('uuid')->value; // uuid of newly created booking
            $book_id = $entity->get('id')->value; // id of newly created booking
            
            $user_data = User::load($tutor_id)->toArray();
            $tutor_mail = $user_data['mail'][0]['value'];
            $tutor_timezone = $user_data['timezone'][0]['value'];
            $tutor_recess_time = $user_data['field_recesstime'][0]['value'];
            $tutor_name = $user_data['field_firstname'][0]['value']." ".$user_data['field_lastname'][0]['value'];

            $session_data = Node::load($session_id);
            $session_title = $session_data->get('title')->value;
            //$session_title = $session_data['title'][0]['value'];
            
            $tempstore->set('session_id', NULL);
            $tempstore->set('tutor_id', NULL);
            $tempstore->set('session_length', NULL);
            $tempstore->set('number_of_sessions', NULL);
            $tempstore->set('available_language', NULL);
            $tempstore->set('session_amount', NULL);
            $tempstore->set('commission_fee', NULL);
            entity_delete_multiple('timeslots', array($timeslot_id)); // Delete the Timeslot which is now divided into new slots 
            
            $service_mail_id = 36; // 36 is on staging
            $all_languages = \Drupal::languageManager()->getStandardLanguageList(); // Get All Languages
            $service_mail_data = Node::load($service_mail_id)->toArray();
            global $base_url;
            
            $accept_link = $base_url.'/booking/response?booking_id='.$book_uuid.'&action=accept';
            $decline_link = $base_url.'/booking/response?booking_id='.$book_uuid.'&action=decline';
            
            $timeslot_info = entity_load('timeslots', $new_timeslot_id);
            $session_date = date('Y-m-d', $timeslot_info->get('start_time')->value);
            
            date_default_timezone_set($tutor_timezone); // timezone is set to tutor so that he gets the time in his own zone
            if($tutor_timezone != ''){
                //$session_start_time_obj = $timeslot_info->converToTz(date('Y-m-d H:i:s', $timeslot_info->get('start_time')->value), $tutor_timezone, "UTC");
                //$session_end_time_obj   = $timeslot_info->converToTz(date('Y-m-d H:i:s', $timeslot_info->get('end_time')->value), $tutor_timezone, "UTC");
                $session_start_time_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('start_time')->value));
                $session_end_time_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('end_time')->value));
            }
            
            if($tutor_recess_time !=''){
                $session_end_time_obj->sub(new \DateInterval('PT'.$tutor_recess_time.'M'));
            }
            
            if($tutor_timezone == 'Europe/London' || \Drupal::service('timezone_service.has_word')->hasWord("America", $tutor_timezone) == 1){
                $session_start_time = \Drupal::service('timezone_service.has_word')->time24to12(date('H', $session_start_time_obj->getTimestamp()), date('i', $session_start_time_obj->getTimestamp()));
                $session_end_time = \Drupal::service('timezone_service.has_word')->time24to12(date('H', $session_end_time_obj->getTimestamp()), date('i', $session_end_time_obj->getTimestamp()));
                        
            }else{
                $session_start_time = date('H:i', $session_start_time_obj->getTimestamp());
                $session_end_time = date('H:i', $session_end_time_obj->getTimestamp());
            }
            
            $session_time = $session_start_time.' - '.$session_end_time;
            date_default_timezone_set(drupal_get_user_timezone()); // set the timezone back to student
            if($language_code == 'dk'){
                $session_language = $all_languages['da'][0];
            }else{
                $session_language = $all_languages[$language_code][0];
            }
            
            $tutor_level = '';
            foreach ($session_data->field_session_level as $item) {
                if ($item->entity) {
                    //$tutor_level[$item->entity->id()] = $item->entity->label();
                    if($tutor_level == ''){
                        $tutor_level = $item->entity->label();
                    }else{
                        $tutor_level .= ', '.$item->entity->label();
                    }
                }
            }

            $service_mail_body = $service_mail_data['body'][0]['value'];
            $service_mail_body = str_replace("[decline_link]", $decline_link, $service_mail_body);
            $service_mail_body = str_replace("[accept_link]", $accept_link, $service_mail_body);
            $service_mail_body = str_replace("[site_url]", $base_url, $service_mail_body);
            $service_mail_body = str_replace("[session_title]", $session_title, $service_mail_body);
            $service_mail_body = str_replace("[Tutor]", $tutor_name, $service_mail_body);
            $service_mail_body = str_replace("[tutor_level]", $tutor_level, $service_mail_body);
            $service_mail_body = str_replace("[Choosen_Language]", $session_language, $service_mail_body);
            $service_mail_body = str_replace("[order_number]", $book_id, $service_mail_body);
            $service_mail_body = str_replace("[session_date]", $session_date, $service_mail_body);
            $service_mail_body = str_replace("[session_time]", $session_time, $service_mail_body);
            
            $from_email = \Drupal::config('system.site')->get('mail');
            $headers = "From: " . strip_tags($from_email) . "\r\n";
            $headers .= "Reply-To: ". strip_tags($from_email) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            
            $subject = $service_mail_data['title'][0]['value'];
            
            $sent = mail($tutor_mail, $subject, $service_mail_body, $headers);
            if($sent){
                \Drupal::logger('timeslots')->notice('Booking Request: Your email has been sent to '.$tutor_mail.'.');
            }else{
                \Drupal::logger('timeslots')->notice('Booking Request: There was a problem sending your email '.$tutor_mail.'.');
            }
            
        } // check for session id
        
        
        $output = array(
            '#theme' => 'thankyou_template',
            /*'#attached' => [
                'library' => 'bookingform/bookingform.template.css',
            ]*/
        );
        return $output;
    }
    
}

