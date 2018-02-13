<?php
/**
 * @file
 * Contains \Drupal\timeslots\Form\TimeslotsForm.
 */

namespace Drupal\timeslots\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use \Drupal\Core\Url;
use Drupal\Core\Database\Database;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Form controller for the content_entity_example entity edit forms.
 *
 * @ingroup content_entity_example
 */
class TimeslotsForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\timeslots\Entity\Timeslots */
    $form = parent::buildForm($form, $form_state);
    $form['#attached']['library'][] = "timeslots/timeslots.js"; // Calculate Total Price
    $form['#prefix'] = "<div id=\"timeslot-ajax-response-wrapper\"></div>";
    
    $form['start_date']['widget']['#attributes'] = array('class' => array('start_date', 'form-text'));
    
    if($form['#attributes']['class'][0] == 'timeslots-edit-form'){
      $form['repeat_slot']['#access'] = false; // hide repeat in edit
    }
    
    $userSubmittedTimezoneString = drupal_get_user_timezone();

    /*if($form['start_date']['widget'][0]['value']['#default_value'] != ''){
      $form['start_date']['widget'][0]['value']['#default_value'] = date('Y-m-d', $form['start_date']['widget'][0]['value']['#default_value']);
    }*/
    
    if($form['start_time']['widget'][0]['value']['#default_value'] != ''){
      $form['start_date']['widget'][0]['value']['#default_value'] = date('Y-m-d', $form['start_time']['widget'][0]['value']['#default_value']);
    }

    if($form['start_time']['widget'][0]['value']['#default_value'] != ''){
      $starttime = date('Y-m-d H:I:s', $form['start_time']['widget'][0]['value']['#default_value']);
      $starttime = $this->converToTz($starttime, $userSubmittedTimezoneString, "UTC");
      if(date('i', $starttime) == 01){
          $starttime = $starttime - 60;
      }
      $form['start_time']['widget'][0]['value']['#default_value'] = date('H:i', $starttime);
    }
    
    if($form['end_time']['widget'][0]['value']['#default_value'] != ''){
      $form['end_date']['widget'][0]['value']['#default_value'] = date('Y-m-d', $form['end_time']['widget'][0]['value']['#default_value']);
    }

    if($form['end_time']['widget'][0]['value']['#default_value'] != ''){
      $endtime = date('Y-m-d H:I:s', $form['end_time']['widget'][0]['value']['#default_value']);
      $endtime = $this->converToTz($endtime, $userSubmittedTimezoneString, "UTC");
      if(date('i', $endtime) == 01){
          $endtime = $endtime - 60;
      }
      $form['end_time']['widget'][0]['value']['#default_value'] = date('H:i', $endtime);
    }
    
    /*$form['actions']['submit']['#attributes'] = [
        'class' => [
          'use-ajax',
        ],
      ];*/
    $form['actions']['submit']['#ajax'] = [
      'wrapper' => 'timeslot-ajax-response-wrapper',//'ajax-wrapper',
      'callback' => array($this, 'editTimeSlotFormAjaxCallback'),
        'event' => 'click',
        //'validate_first' => TRUE,
    ]; 
    
    //$form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }
  
    /*public function validateForm(array &$form, FormStateInterface $form_state)
    {
        //if($form_state->getValue('some_text') != 'Awesome')
        //{
        $form->setValue('start_date', $this->t('Please enter "Awesome"'));
        //}
        parent::validateForm($form, $form_state);
    }*/
  
  public function editTimeSlotFormAjaxCallback(&$form, FormStateInterface $form_state) {
        
        $content = '<div class="alert alert-success"> Successfully updated</div>';
        $response = new AjaxResponse();
        $errorCount = count($form_state->getErrors());
        $isFormValid = true;
        
        $field      = $form_state->getValues();
        
        date_default_timezone_set(drupal_get_user_timezone());
        $user_time = time(); // Returns User(Tutor) Standard Time
        
        if(isset($field['start_date'][0])){
            $start_date = $field['start_date'][0]['value'];
            if(isset($field['start_time'][0])){
                $start_time = $field['start_time'][0]['value'];
                $start_date = $start_date .' '.$start_time;
            }
            $start_date_timestamp = strtotime($start_date);
        }
        
        if(isset($field['end_date'][0])){
            $end_date = $field['end_date'][0]['value'];
            if(isset($field['end_time'][0])){
                $end_time = $field['end_time'][0]['value'];
                $end_date = $end_date .' '.$end_time;
            }
            $end_date_timestamp = strtotime($end_date);
            
        }
        
        if($errorCount > 0) {
            $isFormValid = false;
            $content = '<div class="alert alert-danger"> Error Occured </div>';
        }
        
        if($start_date_timestamp < $user_time || $end_date_timestamp < $user_time){
            $isFormValid = false;
            $errorCount = 1;
            $content = '<div class="alert alert-danger"> You are adding a previous date. </div>';
           
        }
        
        if( $end_date_timestamp < $start_date_timestamp){
            $isFormValid = false;
            $errorCount = 1;
            $content = '<div class="alert alert-danger"> End Date/Time should be greater than Start Date/Time. </div>';
        }
        
        $response->addCommand(new HtmlCommand('#timeslot-ajax-response-wrapper', $content));
        
        if( $errorCount === 0 ) {
            $url = Url::fromRoute('view.timeslot_listing.timeslots_listing')->toString();
            $response->addCommand(new RedirectCommand($url));
        }
        
        return $response;
        //return ['response' => $response, 'isFormValid' => $isFormValid];
  }

  /**
   * {@inheritdoc}
   */

  public function validateForm(array &$form, FormStateInterface $form_state){}

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Redirect to timeslot list after save.
   
    
    $user_id = \Drupal::currentUser()->id();

    $field      = $form_state->getValues();
    $start_date = $field['start_date'][0]['value'];
    $start_time = $field['start_time'][0]['value'];
    $end_time   = $field['end_time'][0]['value'];
    $repeat_slot  = $field['repeat_slot'][0]['value'];
    $end_date   = $field['end_date'][0]['value'];
    
    $userSubmittedTimezoneString = drupal_get_user_timezone(); // get current user timezone
    
    $current_path =  \Drupal::service('path.current')->getPath();
    $arg = explode('/',$current_path);
    $ts_id = $arg[2];

    if(is_numeric($ts_id)){
        
        $start_datetime_string = $this->converToTz($start_date.' '.$start_time, "UTC", $userSubmittedTimezoneString);
        $end_datetime_string   = $this->converToTz($end_date.' '.$end_time, "UTC", $userSubmittedTimezoneString);
        
        $entity = $this->getEntity();
        $entity->set('start_time', $start_datetime_string);
        $entity->set('end_time', $end_datetime_string);
        $entity->save();

        //drupal_set_message("successfully updated");
        $form_state->setRedirect('entity.timeslots.collection');
    }
    else{
    
    $startDate_var = $start_date;

    $start_date_string = $this->converToTz($start_date.' '.$start_time, "UTC", $userSubmittedTimezoneString);
    $end_date_string   = $this->converToTz($end_date.' '.$end_time, "UTC", $userSubmittedTimezoneString);
    
    //echo $start_date_string,__LINE__;exit;

    switch ($repeat_slot) {
      case 0: // No Repeat
          $starting_time = $startDate_var.' '.$start_time;
          $ending_time = $startDate_var.' '.$end_time;
          $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));
          
          $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
          $ending_time   = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);
            
          /** Code For Merging Starts  **/
          $connection = Database::getConnection();
          
          $start_time = $starting_time; 
          $end_time   = $ending_time;
          
          $startdate = date('Y-m-d', $start_time); // just to find an overlap for that day

          // (start_time <= :start_time or end_time >= :end_time) and  ':start_time' => $StartTime, ':end_time' => $EndTime, 
          $options = array();
          //$result = $connection->query('SELECT * FROM timeslots WHERE  tutor_id = :tutor_id and is_booked = :is_booked and DATE_FORMAT(FROM_UNIXTIME(start_date), \'%Y-%m-%d\') = :start_date', array(':tutor_id' => $user_id, ':is_booked' => '0', ':start_date' => $startdate), $options);
          $result = $connection->query('SELECT * FROM timeslots WHERE tutor_id = :tutor_id and is_booked = :isbooked and FROM_UNIXTIME(start_time, \'%Y-%m-%d\') = :start_time', 
                                        array(':tutor_id' => $user_id, ':isbooked' => '0', ':start_time' => $startdate), $options);
          foreach($result as $item) {
            $timeslot_arr[$item->id] = array($item->start_time, $item->end_time);
          }
          
          if(!empty($timeslot_arr)){
              asort($timeslot_arr);
          }
          
          $overlap_id_arr = array();
          
          //if(count($timeslot_arr) > 0 ) {
            $new_starting_time = 0;
            $new_ending_time = 0;
            
            if(!empty($timeslot_arr)){
                foreach($timeslot_arr as $key => $timeslot){
            
                    if($start_time >= $timeslot[0] && $start_time <= $timeslot[1]){
                        $new_starting_time = $timeslot[0];
                        $overlap_id_arr[] = $key;
                    }else{
                        if($start_time < $timeslot[0]){
                            if($new_starting_time == 0 ){
                                $new_starting_time = $start_time;
                                //$overlap_id_arr[] = $key;
                            }
                            
                            if($new_starting_time > $timeslot[0] ){
                                $new_starting_time = $timeslot[0];
                                $overlap_id_arr[] = $key;
                            }
                        }
                    }
            
                    if($end_time >= $timeslot[0] && $end_time <= $timeslot[1]){
                        $new_ending_time = $timeslot[1];
                        $overlap_id_arr[] = $key;
                    }else{
                        if($new_ending_time == 0 ){
                            $new_ending_time = $end_time;
                            //$overlap_id_arr[] = $key;
                        }

                        if($new_ending_time < $timeslot[1]){
                            $new_ending_time = $timeslot[1];
                            $overlap_id_arr[] = $key;
                        }
                    }
                }
            }
            
            /*if($new_starting_time == 0){
                $new_starting_time = $starting_time;
            }*/
            
          //}
          
          if(count($overlap_id_arr) > 0){
              $overlap_id_arr = array_unique($overlap_id_arr);
          }
          
          $create_new_slots_arr = array(); // From this array I will remove the booked slots if there is any
          
          if(count($overlap_id_arr) > 0 || empty($timeslot_arr)){
              if(empty($timeslot_arr)){
                  $new_starting_time = $starting_time;
                  $new_ending_time = $ending_time;
              }
              $starting_time = $new_starting_time;
              $ending_time = $new_ending_time;
             
              $booked_slots = $connection->query('SELECT * FROM timeslots WHERE tutor_id = :tutor_id and is_booked = :is_booked and FROM_UNIXTIME(start_time, \'%Y-%m-%d\') = :start_time', 
                              array(':tutor_id' => $user_id, ':is_booked' => '1', ':start_time' => $startdate), $options);
              
              if(!empty($booked_slots)){
                  asort($booked_slots);
              }
              
              foreach($booked_slots as $booked_slot){
                  if($booked_slot->start_time < $new_starting_time && $new_starting_time < $booked_slot->end_time && $booked_slot->end_time < $new_ending_time){ // booked slot end time lies in the middle
                        $create_new_slots_arr[] = array('startslot' => $booked_slot->end_time, 'endslot' => $ending_time);
                  }
                  
                  if($new_starting_time < $booked_slot->start_time && $booked_slot->end_time > $new_ending_time){ // booked slot only start time lies in the middle
                      if($booked_slot->start_time != $new_ending_time){ 
                          $create_new_slots_arr[] = array('startslot' => $new_starting_time, 'endslot' => $booked_slot->start_time);
                      }
                  }
                  
                  if($new_starting_time < $booked_slot->start_time && $booked_slot->end_time < $new_ending_time){ // booked slot lies in the middle of the merge slot
                      if(empty($create_new_slots_arr)){
                          $create_new_slots_arr[] = array('startslot' => $new_starting_time, 'endslot' => $booked_slot->start_time);
                          $create_new_slots_arr[] = array('startslot' => $booked_slot->end_time, 'endslot' => $new_ending_time);
                      }
                  }
              }
        }
        
        if(count($create_new_slots_arr) == 0){
            if($new_starting_time == 0){
                $new_starting_time = $starting_time;
            }
            
            if($new_ending_time == 0){
                $new_ending_time = $ending_time;
            }
           
            // when booked slots not found inside the merged slot
            $uuid = \Drupal::service('uuid');
            $uuid = $uuid->generate();
            
            $field  = array(
                'uuid'          => $uuid,
                'start_date'     => $start_date_string,
                'start_time'     => $new_starting_time,
                'end_time'       => $new_ending_time,
                'end_date'       => $end_date_string,
                'repeat_slot'     => $repeat_slot,
                'is_booked'      => '0',
                'created'       => time(),
                'changed'       => time(),
                'tutor_id'      => $user_id
            );
            $query_no_repeat = \Drupal::database();
            $timeslot_id = $query_no_repeat->insert('timeslots')
                                 ->fields($field)
                                 ->execute();
             if( count($overlap_id_arr) > 0) {
                entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
            }
            
        }else{
            
            foreach($create_new_slots_arr as $create_new_slot){
                $uuid = \Drupal::service('uuid');
                $uuid = $uuid->generate();
                $field  = array(
                    'uuid'          => $uuid,
                    'start_date'     => $start_date_string,
                    'start_time'     => $create_new_slot['startslot'],
                    'end_time'       => $create_new_slot['endslot'],
                    'end_date'       => $end_date_string,
                    'repeat_slot'     => $repeat_slot,
                    'is_booked'      => '0',
                    'created'       => time(),
                    'changed'       => time(),
                    'tutor_id'      => $user_id
                );

                $query = \Drupal::database();
                $timeslot_id = $query->insert('timeslots')
                                 ->fields($field)
                                 ->execute();
               
                
            }
            if( count($overlap_id_arr) > 0) {
                entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
            }
            
        }
          /** Code For Merging Ends **/
          
         

      break;
      case 1: // Every Day
          while ($end_date >= $startDate_var) {
              $starting_time = $startDate_var.' '.$start_time;
              $ending_time = $startDate_var.' '.$end_time;
              $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));
              $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
              $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);
              
              $uuid = \Drupal::service('uuid');
              $uuid = $uuid->generate();
              $field  = array(
                        'uuid'          => $uuid,
                        'start_date'     => $start_date_string,
                        'start_time'     => $starting_time,
                        'end_time'       => $ending_time,
                        'end_date'       => $end_date_string,
                        'repeat_slot'     => $repeat_slot,
                        'is_booked'      => '0',
                        'created'       => time(),
                        'changed'       => time(),
                        'tutor_id'      => $user_id
                      );

                      $query = \Drupal::database();
                      $timeslot_id = $query->insert('timeslots')
                                           ->fields($field)
                                           ->execute();
              /*if($starting_time != '' && $end_time != ''){
                    $entity = entity_create('timeslots'); 
                    $entity->set('start_date', $start_date_string);
                    $entity->set('start_time', $starting_time);
                    $entity->set('end_time', $ending_time);
                    $entity->set('repeat_slot', $repeat_slot);
                    $entity->set('end_date', $end_date_string);
                    $entity->set('is_booked', '0');
                    $entity->set('tutor_id', $user_id);
                    $entity->save();
              }*/
          }
          break;
        case 2: // Business days
            $index = 0;
            while ($end_date >= $startDate_var) {

                  if (date('N', strtotime($startDate_var)) <= 5) {
                      $starting_time = $startDate_var.' '.$start_time;
                      $ending_time = $startDate_var.' '.$end_time;

                      $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
                      $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);

                      $uuid = \Drupal::service('uuid');
                      $uuid = $uuid->generate();

                      $field  = array(
                        'uuid'          => $uuid,
                        'start_date'     => $start_date_string,
                        'start_time'     => $starting_time,
                        'end_time'       => $ending_time,
                        'end_date'       => $end_date_string,
                        'repeat_slot'     => $repeat_slot,
                        'is_booked'      => '0',
                        'created'       => time(),
                        'changed'       => time(),
                        'tutor_id'      => $user_id
                      );

                      $query = \Drupal::database();
                      $timeslot_id = $query->insert('timeslots')
                                           ->fields($field)
                                           ->execute();

                  }
                  $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));

                }
                break;

        case 3: // Only weekends
            $index = 0;
            while ($end_date >= $startDate_var) {

                if (date('N', strtotime($startDate_var)) > 5) {
                    $starting_time = $startDate_var.' '.$start_time;
                    $ending_time = $startDate_var.' '.$end_time;

                    $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
                    $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);

                    $uuid = \Drupal::service('uuid');
                    $uuid = $uuid->generate();

                    $field  = array(
                      'uuid'          => $uuid,
                      'start_date'     => $start_date_string,
                      'start_time'     => $starting_time,
                      'end_time'       => $ending_time,
                      'end_date'       => $end_date_string,
                      'repeat_slot'     => $repeat_slot,
                      'is_booked'      => '0',
                      'created'       => time(),
                      'changed'       => time(),
                      'tutor_id'      => $user_id
                    );

                    $query = \Drupal::database();
                    $timeslot_id = $query->insert('timeslots')
                                         ->fields($field)
                                         ->execute();

                    $form_state->setRedirect('entity.timeslots.collection');

                }
                $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));

            }
            break;
      } // end switch
//        $content = '<div class="alert alert-success"> Successfully updated</div>';
//        $response = new AjaxResponse();
//        $errorCount = count($form_state->getErrors());
//
//        if($errorCount > 0) {
//            $content = '<div class="alert alert-danger"> Error occurred</div>';
//        }
//        $response->addCommand(new HtmlCommand('#timeslot-ajax-response-wrapper', $content));
//
//        if( $errorCount === 0 ) {
//            $url = Url::fromRoute('view.timeslot_listing.timeslots_listing')->toString();
//            $response->addCommand(new RedirectCommand($url));
//        }
//
//        return $response;
//      $site_url = \Drupal::request()->getSchemeAndHttpHost();
//      //drupal_set_message("successfully saved");
//      $url = Url::fromRoute('view.timeslot_listing.timeslots_listing')->toString();
//      $response = new RedirectResponse($url);
//      $response->send();
      //$this->editTimeSlotFormAjaxCallback($form, $form_state);
      //$form_state->setRedirect('entity.timeslots.collection');
    }
    
    
   
  }

  function converToTz($time="",$toTz='',$fromTz='')
  {   
      $date = new \DateTime($time, new \DateTimeZone($fromTz));
      $date->setTimezone(new \DateTimeZone($toTz));
      $time = $date->format('Y-m-d H:i:s');
      return strtotime($time);
  }

}
