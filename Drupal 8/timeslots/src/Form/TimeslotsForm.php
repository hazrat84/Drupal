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
    $form['#attribute'][] = 'novalidate'; 
    
    $form['start_date']['widget'][0]['value']['#attributes']['class'][] = 'datepicker'; //array('class' => array('datepicker'));
    $form['end_date']['widget'][0]['value']['#attributes']['class'][] = 'datepicker';
    
    $form['#attached']['library'][] = "timeslots/datepicker_calendar_css";
    $form['#attached']['library'][] = "timeslots/datepicker_calendar"; // datepicker js 
    $form['#attached']['library'][] = "timeslots/timeslots.js"; // change the end date to start date
    
    $form['#prefix'] = "<div id=\"timeslot-ajax-response-wrapper\"></div>";
    
    if($form['end_time']['widget'][0]['value']['#default_value'] != ''){
      $form['end_date']['widget'][0]['value']['#default_value'] = date('Y-m-d', $form['end_time']['widget'][0]['value']['#default_value']);
    }
    
    if($form['#attributes']['class'][0] == 'timeslots-edit-form'){
        $form['start_date']['widget'][0]['#required'] = false;
        $form['start_date']['widget']['#required'] = 0;
        $form['start_date']['widget'][0]['value']['#required'] = 0;
        $form['start_date']['#disabled'] = true; // disable start date in edit
        
        $form['repeat_slot']['widget'][0]['#required'] = false;
        $form['repeat_slot']['widget']['#required'] = 0;
        $form['repeat_slot']['widget'][0]['value']['#required'] = 0;
        $form['repeat_slot']['#access'] = false; // hide repeat in edit
        
        $form['end_date']['widget'][0]['#required'] = false;
        $form['end_date']['widget']['#required'] = 0;
        $form['end_date']['widget'][0]['value']['#required'] = 0;
        $form['end_date']['#access'] = false; // hide end date in edit
        $form['#validate'][] = FALSE;
        $form['actions']['submit']['#value'] = t('Save changes');
        $form['actions']['delete']['#access'] = false;
        $form['actions']['submit']['#attributes']['class'][] = 'edit_submit';
        $form['actions']['my_delete_btn'] = array
        (
          '#type' => 'button',
          '#value' => t('Delete time slot'),
          '#attributes' => array('class' => array('button--danger', 'custom-delete')),
          '#disabled' => true,  
          '#weight' => 10,
        );
        
        
    }else{
        $form['actions']['submit']['#value'] = t('Save time slot');
    }
    $form['actions']['delete']['#disabled'] = true;
    
    $userSubmittedTimezoneString = drupal_get_user_timezone();
    
    if($form['start_time']['widget'][0]['value']['#default_value'] != ''){
      $form['start_date']['widget'][0]['value']['#default_value'] = date('Y-m-d', $form['start_time']['widget'][0]['value']['#default_value']);
    }

    if($form['start_time']['widget'][0]['value']['#default_value'] != ''){
      $starttime = date('H:i', $form['start_time']['widget'][0]['value']['#default_value']);
      //$starttime = $this->converToTz($starttime, $userSubmittedTimezoneString, "UTC");
      if(date('i', $starttime) == 01){
          $starttime = $starttime - 60;
      }
      $form['start_time']['widget'][0]['value']['#default_value'] = $starttime;
    }

    if($form['end_time']['widget'][0]['value']['#default_value'] != ''){
      $endtime = date('H:i', $form['end_time']['widget'][0]['value']['#default_value']);
      
      if(date('i', $endtime) == 01){
          $endtime = $endtime - 60;
      }
      $form['end_time']['widget'][0]['value']['#default_value'] =$endtime;
    }
    
    
    $form['actions']['submit']['#ajax'] = [
      'wrapper' => 'timeslot-ajax-response-wrapper',  //'ajax-wrapper',
      'callback' => array($this, 'editTimeSlotFormAjaxCallback'), 
    ]; 
    
    //$form['actions']['submit']['#validate'] = array();
    return $form;
  }
  
  public function editTimeSlotFormAjaxCallback(&$form, FormStateInterface $form_state) {
        //return $form;
        // Clear the message set by the submit handler.
      
        drupal_get_messages();
        $content = '<div class="alert alert-success alert-dismissible" role="alert">
                        <a href="#" role="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></a>
                        <h4 class="sr-only">Error message</h4>
                        <ul class="item-list item-list--messages">
                            <li class="item item--message">Successfully updated</li>
                        </ul>
                        </div>';
        $response = new AjaxResponse();
        $errorCount = count($form_state->getErrors());
        $require_error_messages = '';
        $require_error = 0;
        $start_date = '';
        $end_date = '';
        
        $field = $form_state->getValues();
        
        //date_default_timezone_set(drupal_get_user_timezone());
        //$user_time = time(); // Returns User(Tutor) Standard Time
        if($form['#attributes']['class'][0] == 'timeslots-add-form'){
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
        }
        
        if($form['#attributes']['class'][0] == 'timeslots-add-form'){
            if($field['start_date'][0]['value'] == NULL || $field['start_date'][0]['value'] == ''){
                $error_messages = '<li class="item item--message">Start Date is required</li>';
                $require_error = 1;
            }
        }
        
        if($field['start_time'][0]['value'] == ''){
            $error_messages .= '<li class="item item--message">Start Time is required</li>';
            $require_error = 1;
        }
        
        if($field['end_time'][0]['value'] == ''){
            $error_messages .= '<li class="item item--message">End Time is required</li>';
            $require_error = 1;
        }
        
        if($form['#attributes']['class'][0] == 'timeslots-add-form'){
            if($field['repeat_slot'][0]['value'] == ''){
                $error_messages .= '<li class="item item--message">Select Repeat is required</li>';
                $require_error = 1;
            }
        }
        
        if($form['#attributes']['class'][0] == 'timeslots-add-form'){
            if($field['end_date'][0]['value'] == ''){
                $error_messages .= '<li class="item item--message">End Date is required</li>';
                $require_error = 1;
            }
        }
        
        if($errorCount > 0) {
            $content = '<div class="alert alert-danger alert-dismissible" role="alert">
                        <a href="#" role="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></a>
                        <h4 class="sr-only">Error message</h4>
                        <ul class="item-list item-list--messages">
                        '.$error_messages.'
                        </ul>
                        </div>';
        }
        
        /*if($form['#attributes']['class'][0] == 'timeslots-add-form'){
            if($start_date_timestamp < $user_time || $end_date_timestamp < $user_time){
                $errorCount = 1;
                if($require_error == 0){
                    $content = '<div class="alert alert-danger alert-dismissible" role="alert">
                            <a href="#" role="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></a>
                            <h4 class="sr-only">Error message</h4>
                            <ul class="item-list item-list--messages">
                                <li class="item item--message">You are adding a previous date</li>
                            </ul>
                            </div>';
                }
            }
        }*/
        
        /*if( $end_date_timestamp < $start_date_timestamp){
            $isFormValid = false;
            $errorCount = 1;
            $content = '<div class="alert alert-danger"> End Date/Time should be greater than Start Date/Time. </div>';
        }*/
        
        $response->addCommand(new HtmlCommand('#timeslot-ajax-response-wrapper', $content));
        
        if( $errorCount === 0 ) {
            $url = Url::fromRoute('view.timeslot_listing.timeslots_listing')->toString();
            $response->addCommand(new RedirectCommand($url));
        }
       
        return $response;
  }

  /**
   * {@inheritdoc}
   */

   /* public function validateForm(array &$form, FormStateInterface $form_state){
      
        $field      = $form_state->getValues();
      
        date_default_timezone_set(drupal_get_user_timezone());
        $user_time = time(); // Returns User(Tutor) Standard Time
        if($form['#attributes']['class'][0] == 'timeslots-add-form'){
            $start_date = $field['start_date'][0]['value'];
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
            
            if($field['start_date'][0]['value'] == NULL || $field['start_date'][0]['value'] == ''){
                $form_state->setErrorByName('start_date', $this->t('Please enter correct date'));
            }
        
            if($field['end_date'][0]['value'] == ''){
                $form_state->setErrorByName('end_date', $this->t('Please enter correct date'));
            }
            
            if($start_date_timestamp < $user_time || $end_date_timestamp < $user_time){
                $form_state->setErrorByName('start_date', $this->t('Please enter correct date'));
            }
            
        }
        
        if($field['start_time'][0]['value'] == ''){
            $form_state->setErrorByName('start_time', $this->t('Start Time is required'));
        }
        
        if($field['end_time'][0]['value'] == ''){
            $form_state->setErrorByName('end_time', $this->t('End Time is required'));
        }
        
        return $form;
    }*/

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Redirect to timeslot list after save.
    db_query("TRUNCATE cache_entity");  
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
        
        $ts_info = entity_load('timeslots', $ts_id);
        //$edit_start_date =  $ts_info->get('start_date')->value;
        //$edit_end_date =  $ts_info->get('end_date')->value;
        
        $ts_info->set('start_time', $start_datetime_string);
        $ts_info->set('end_time', $end_datetime_string);
        $ts_info->save();
        
        //$entity = $this->getEntity();
        //$entity->set('start_time', $start_datetime_string);
        //$entity->set('end_time', $end_datetime_string);
        //$entity->save();

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

          $options = array();
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
                                //$overlap_id_arr[] = $key; // was commented
                            }
                            
                            if($new_starting_time > $timeslot[0] ){
                                $new_starting_time = $timeslot[0];
                                $overlap_id_arr[] = $key;
                            }
                        }
                    }
                    // it will find overlap in case when the db slot lies in between new start and end slot
                    if($start_time <= $timeslot[0] && $end_time >= $timeslot[1]){
                        $overlap_id_arr[] = $key;
                    }
            
                    if($end_time >= $timeslot[0] && $end_time <= $timeslot[1]){
                        $new_ending_time = $timeslot[1];
                        $overlap_id_arr[] = $key;
                    }else{
                        if($new_ending_time == 0 ){
                            $new_ending_time = $end_time;
                            //$overlap_id_arr[] = $key; // was commented
                        }

                        if($new_ending_time < $timeslot[1]){
                            $new_ending_time = $timeslot[1];
                            $overlap_id_arr[] = $key;
                        }
                    }
                }
            }
            /*echo count($overlap_id_arr)."  ";
            echo $overlap_id_arr[0].' ** '.$overlap_id_arr[1];
            echo $new_starting_time."  ".$new_ending_time;exit;*/
            /*if($new_starting_time == 0){
                $new_starting_time = $starting_time;
            }*/
            
          //}
          
          if(count($overlap_id_arr) > 0){
              $overlap_id_arr = array_unique($overlap_id_arr);
          }
          
          $create_new_slots_arr = array(); // From this array I will remove the booked slots if there is any
          $booked_slots = array(); // added to avoid error on 424
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
            
            $entity = entity_create('timeslots'); 
            $entity->set('uuid', $uuid);
            $entity->set('start_date', $start_date_string);
            $entity->set('start_time', $new_starting_time);
            $entity->set('end_time', $new_ending_time);
            $entity->set('repeat_slot', $repeat_slot);
            $entity->set('end_date', $end_date_string);
            $entity->set('is_booked', '0');
            $entity->set('tutor_id', $user_id);
            $entity->save();
            
             if( count($overlap_id_arr) > 0) {
                entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
            }
            
        }else{
            
            foreach($create_new_slots_arr as $create_new_slot){
                
                $uuid = \Drupal::service('uuid');
                $uuid = $uuid->generate();
                
                $entity = entity_create('timeslots'); 
                $entity->set('uuid', $uuid);
                $entity->set('start_date', $start_date_string);
                $entity->set('start_time', $create_new_slot['startslot']);
                $entity->set('end_time', $create_new_slot['endslot']);
                $entity->set('repeat_slot', $repeat_slot);
                $entity->set('end_date', $end_date_string);
                $entity->set('is_booked', '0');
                $entity->set('tutor_id', $user_id);
                $entity->save();
                
            }
            if( count($overlap_id_arr) > 0) {
                entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
            }
            
        }
          /** Code For Merging Ends **/
          
         

      break;
      case 1: // Every Day
          $user_start_time = '';
          $user_end_time = '';
          while ($end_date >= $startDate_var) {
              if($user_start_time == ''){
                  $user_start_time = $start_time;
              }
              
              if($user_end_time == ''){
                  $user_end_time = $end_time;
              }
              
              $starting_time = $startDate_var.' '.$user_start_time;//$start_time;
              $ending_time = $startDate_var.' '.$user_end_time; //$end_time;
              $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));
              
              $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
              $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);
              
                /** Code For Merging Starts  **/
                $connection = Database::getConnection();

                $start_time = $starting_time; 
                $end_time   = $ending_time;

                $startdate = date('Y-m-d', $start_time); // just to find an overlap for that day

                $options = array();
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
                                    //$overlap_id_arr[] = $key; // was commented
                                }

                                if($new_starting_time > $timeslot[0] ){
                                    $new_starting_time = $timeslot[0];
                                    $overlap_id_arr[] = $key;
                                }
                            }
                        }
                        // it will find overlap in case when the db slot lies in between new start and end slot
                        if($start_time <= $timeslot[0] && $end_time >= $timeslot[1]){
                            $overlap_id_arr[] = $key;
                        }

                        if($end_time >= $timeslot[0] && $end_time <= $timeslot[1]){
                            $new_ending_time = $timeslot[1];
                            $overlap_id_arr[] = $key;
                        }else{
                            if($new_ending_time == 0 ){
                                $new_ending_time = $end_time;
                                //$overlap_id_arr[] = $key; // was commented
                            }

                            if($new_ending_time < $timeslot[1]){
                                $new_ending_time = $timeslot[1];
                                $overlap_id_arr[] = $key;
                            }
                        }
                    }
                }
            /*echo count($overlap_id_arr)."  ";
            echo $overlap_id_arr[0].' ** '.$overlap_id_arr[1];
            echo $new_starting_time."  ".$new_ending_time;exit;*/
            /*if($new_starting_time == 0){
                $new_starting_time = $starting_time;
            }*/
            
          //}
          
            if(count($overlap_id_arr) > 0){
                $overlap_id_arr = array_unique($overlap_id_arr);
            }
          
            $create_new_slots_arr = array(); // From this array I will remove the booked slots if there is any
            $booked_slots = array(); // added to avoid error on 424
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

                $entity = entity_create('timeslots'); 
                $entity->set('uuid', $uuid);
                $entity->set('start_date', $start_date_string);
                $entity->set('start_time', $new_starting_time);
                $entity->set('end_time', $new_ending_time);
                $entity->set('repeat_slot', $repeat_slot);
                $entity->set('end_date', $end_date_string);
                $entity->set('is_booked', '0');
                $entity->set('tutor_id', $user_id);
                $entity->save();

                 if( count($overlap_id_arr) > 0) {
                    entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
                }

            }else{

                foreach($create_new_slots_arr as $create_new_slot){

                    $uuid = \Drupal::service('uuid');
                    $uuid = $uuid->generate();

                    $entity = entity_create('timeslots'); 
                    $entity->set('uuid', $uuid);
                    $entity->set('start_date', $start_date_string);
                    $entity->set('start_time', $create_new_slot['startslot']);
                    $entity->set('end_time', $create_new_slot['endslot']);
                    $entity->set('repeat_slot', $repeat_slot);
                    $entity->set('end_date', $end_date_string);
                    $entity->set('is_booked', '0');
                    $entity->set('tutor_id', $user_id);
                    $entity->save();

                }
                if( count($overlap_id_arr) > 0) {
                    entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
                }

            }
            /** Code For Merging Ends **/
            
              /*$uuid = \Drupal::service('uuid');
              $uuid = $uuid->generate();
              
              if($starting_time != '' && $end_time != ''){
                    $entity = entity_create('timeslots'); 
                    $entity->set('uuid', $uuid);
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
            $user_start_time = '';
            $user_end_time = '';
            while ($end_date >= $startDate_var) {
                if($user_start_time == ''){
                    $user_start_time = $start_time;
                }
              
                if($user_end_time == ''){
                    $user_end_time = $end_time;
                }
                if (date('N', strtotime($startDate_var)) <= 5) {
                      $starting_time = $startDate_var.' '.$user_start_time;//$start_time;
                      $ending_time = $startDate_var.' '.$user_end_time;//$end_time;

                      $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
                      $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);
                      
                      
                        /** Code For Merging Starts  **/
                        $connection = Database::getConnection();

                        $start_time = $starting_time; 
                        $end_time   = $ending_time;

                        $startdate = date('Y-m-d', $start_time); // just to find an overlap for that day

                        $options = array();
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
                                            //$overlap_id_arr[] = $key; // was commented
                                        }

                                        if($new_starting_time > $timeslot[0] ){
                                            $new_starting_time = $timeslot[0];
                                            $overlap_id_arr[] = $key;
                                        }
                                    }
                                }
                                // it will find overlap in case when the db slot lies in between new start and end slot
                                if($start_time <= $timeslot[0] && $end_time >= $timeslot[1]){
                                    $overlap_id_arr[] = $key;
                                }

                                if($end_time >= $timeslot[0] && $end_time <= $timeslot[1]){
                                    $new_ending_time = $timeslot[1];
                                    $overlap_id_arr[] = $key;
                                }else{
                                    if($new_ending_time == 0 ){
                                        $new_ending_time = $end_time;
                                        //$overlap_id_arr[] = $key; // was commented
                                    }

                                    if($new_ending_time < $timeslot[1]){
                                        $new_ending_time = $timeslot[1];
                                        $overlap_id_arr[] = $key;
                                    }
                                }
                            }
                        }
            /*echo count($overlap_id_arr)."  ";
            echo $overlap_id_arr[0].' ** '.$overlap_id_arr[1];
            echo $new_starting_time."  ".$new_ending_time;exit;*/
            /*if($new_starting_time == 0){
                $new_starting_time = $starting_time;
            }*/
            
          //}
          
            if(count($overlap_id_arr) > 0){
                $overlap_id_arr = array_unique($overlap_id_arr);
            }
          
            $create_new_slots_arr = array(); // From this array I will remove the booked slots if there is any
            $booked_slots = array(); // added to avoid error on 424
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

                $entity = entity_create('timeslots'); 
                $entity->set('uuid', $uuid);
                $entity->set('start_date', $start_date_string);
                $entity->set('start_time', $new_starting_time);
                $entity->set('end_time', $new_ending_time);
                $entity->set('repeat_slot', $repeat_slot);
                $entity->set('end_date', $end_date_string);
                $entity->set('is_booked', '0');
                $entity->set('tutor_id', $user_id);
                $entity->save();

                 if( count($overlap_id_arr) > 0) {
                    entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
                }

            }else{

                foreach($create_new_slots_arr as $create_new_slot){

                    $uuid = \Drupal::service('uuid');
                    $uuid = $uuid->generate();

                    $entity = entity_create('timeslots'); 
                    $entity->set('uuid', $uuid);
                    $entity->set('start_date', $start_date_string);
                    $entity->set('start_time', $create_new_slot['startslot']);
                    $entity->set('end_time', $create_new_slot['endslot']);
                    $entity->set('repeat_slot', $repeat_slot);
                    $entity->set('end_date', $end_date_string);
                    $entity->set('is_booked', '0');
                    $entity->set('tutor_id', $user_id);
                    $entity->save();

                }
                if( count($overlap_id_arr) > 0) {
                    entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
                }

            }
            /** Code For Merging Ends **/
                      
                      /*$uuid = \Drupal::service('uuid');
                      $uuid = $uuid->generate();
                      
                        $entity = entity_create('timeslots'); 
                        $entity->set('uuid', $uuid);
                        $entity->set('start_date', $start_date_string);
                        $entity->set('start_time', $starting_time);
                        $entity->set('end_time', $ending_time);
                        $entity->set('repeat_slot', $repeat_slot);
                        $entity->set('end_date', $end_date_string);
                        $entity->set('is_booked', '0');
                        $entity->set('tutor_id', $user_id);
                        $entity->save();*/

                }
                  $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));

                }
                break;

        case 3: // Only weekends
            $index = 0;
            $user_start_time = '';
            $user_end_time = '';
            while ($end_date >= $startDate_var) {
                if($user_start_time == ''){
                    $user_start_time = $start_time;
                }
              
                if($user_end_time == ''){
                    $user_end_time = $end_time;
                }
                if (date('N', strtotime($startDate_var)) > 5) {
                    $starting_time = $startDate_var.' '.$user_start_time; //$start_time;
                    $ending_time = $startDate_var.' '.$user_end_time;//$end_time;

                    $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
                    $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);
                    
                    /** Code For Merging Starts  **/
                        $connection = Database::getConnection();

                        $start_time = $starting_time; 
                        $end_time   = $ending_time;

                        $startdate = date('Y-m-d', $start_time); // just to find an overlap for that day

                        $options = array();
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
                                            //$overlap_id_arr[] = $key; // was commented
                                        }

                                        if($new_starting_time > $timeslot[0] ){
                                            $new_starting_time = $timeslot[0];
                                            $overlap_id_arr[] = $key;
                                        }
                                    }
                                }
                                // it will find overlap in case when the db slot lies in between new start and end slot
                                if($start_time <= $timeslot[0] && $end_time >= $timeslot[1]){
                                    $overlap_id_arr[] = $key;
                                }

                                if($end_time >= $timeslot[0] && $end_time <= $timeslot[1]){
                                    $new_ending_time = $timeslot[1];
                                    $overlap_id_arr[] = $key;
                                }else{
                                    if($new_ending_time == 0 ){
                                        $new_ending_time = $end_time;
                                        //$overlap_id_arr[] = $key; // was commented
                                    }

                                    if($new_ending_time < $timeslot[1]){
                                        $new_ending_time = $timeslot[1];
                                        $overlap_id_arr[] = $key;
                                    }
                                }
                            }
                        }
            /*echo count($overlap_id_arr)."  ";
            echo $overlap_id_arr[0].' ** '.$overlap_id_arr[1];
            echo $new_starting_time."  ".$new_ending_time;exit;*/
            /*if($new_starting_time == 0){
                $new_starting_time = $starting_time;
            }*/
            
          //}
          
            if(count($overlap_id_arr) > 0){
                $overlap_id_arr = array_unique($overlap_id_arr);
            }
          
            $create_new_slots_arr = array(); // From this array I will remove the booked slots if there is any
            $booked_slots = array(); // added to avoid error on 424
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

                $entity = entity_create('timeslots'); 
                $entity->set('uuid', $uuid);
                $entity->set('start_date', $start_date_string);
                $entity->set('start_time', $new_starting_time);
                $entity->set('end_time', $new_ending_time);
                $entity->set('repeat_slot', $repeat_slot);
                $entity->set('end_date', $end_date_string);
                $entity->set('is_booked', '0');
                $entity->set('tutor_id', $user_id);
                $entity->save();

                 if( count($overlap_id_arr) > 0) {
                    entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
                }

            }else{

                foreach($create_new_slots_arr as $create_new_slot){

                    $uuid = \Drupal::service('uuid');
                    $uuid = $uuid->generate();

                    $entity = entity_create('timeslots'); 
                    $entity->set('uuid', $uuid);
                    $entity->set('start_date', $start_date_string);
                    $entity->set('start_time', $create_new_slot['startslot']);
                    $entity->set('end_time', $create_new_slot['endslot']);
                    $entity->set('repeat_slot', $repeat_slot);
                    $entity->set('end_date', $end_date_string);
                    $entity->set('is_booked', '0');
                    $entity->set('tutor_id', $user_id);
                    $entity->save();

                }
                if( count($overlap_id_arr) > 0) {
                    entity_delete_multiple('timeslots', $overlap_id_arr); // delete the previous timeslots
                }

            }
            /** Code For Merging Ends **/
                    
                    /*$uuid = \Drupal::service('uuid');
                    $uuid = $uuid->generate();
                    
                    $entity = entity_create('timeslots'); 
                    $entity->set('uuid', $uuid);
                    $entity->set('start_date', $start_date_string);
                    $entity->set('start_time', $starting_time);
                    $entity->set('end_time', $ending_time);
                    $entity->set('repeat_slot', $repeat_slot);
                    $entity->set('end_date', $end_date_string);
                    $entity->set('is_booked', '0');
                    $entity->set('tutor_id', $user_id);
                    $entity->save();*/

                    $form_state->setRedirect('entity.timeslots.collection');

                }
                $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));

            }
            break;
      } // end switch


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
      return $date->getTimestamp();
      //$time = $date->format('Y-m-d H:i:s');
      //return strtotime($time);
  }

}
