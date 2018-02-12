<?php
/**
 * @file
 * Contains \Drupal\timeslots\Form\TimeslotsForm.
 */

namespace Drupal\timeslots\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    
    if($form['#attributes']['class'][0] == 'timeslots-edit-form'){
      $form['recurrent']['#access'] = false; // hide repeat in edit
    }
    
    $userSubmittedTimezoneString = drupal_get_user_timezone();

    if($form['startdate']['widget'][0]['value']['#default_value'] != ''){
      $form['startdate']['widget'][0]['value']['#default_value'] = date('Y-m-d', $form['startdate']['widget'][0]['value']['#default_value']);
    }

    if($form['starttime']['widget'][0]['value']['#default_value'] != ''){
      $starttime = date('Y-m-d H:I:s', $form['starttime']['widget'][0]['value']['#default_value']);
      $starttime = $this->converToTz($starttime, $userSubmittedTimezoneString, "UTC");
      $form['starttime']['widget'][0]['value']['#default_value'] = date('H:i', $starttime);
    }

    if($form['endtime']['widget'][0]['value']['#default_value'] != ''){
      $endtime = date('Y-m-d H:I:s', $form['endtime']['widget'][0]['value']['#default_value']);
      $endtime = $this->converToTz($endtime, $userSubmittedTimezoneString, "UTC");
      $form['endtime']['widget'][0]['value']['#default_value'] = date('H:i', $endtime);
    }

    if($form['enddate']['widget'][0]['value']['#default_value'] != ''){
      $form['enddate']['widget'][0]['value']['#default_value'] = date('Y-m-d', $form['enddate']['widget'][0]['value']['#default_value']);
    }

    /*$form['actions']['submit']['#ajax'] = [
      'wrapper' => $this->getFormId(),
      'callback' => 'Drupal\timeslots\Plugin\Validation\Constraint',//array($this, 'ajaxRebuildCallback'),
      'effect' => 'fade',
    ];*/
  
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */

  /*public function validateForm(array &$form, FormStateInterface $form_state){
    $field      = $form_state->getValues();
    $start_date = $field['startdate'][0]['value'];

  }*/

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Redirect to timeslot list after save.
    //$form_state->setRedirect('entity.timeslots.collection');

    $user_id = \Drupal::currentUser()->id();

    $field      = $form_state->getValues();
    $start_date = $field['start_date'][0]['value'];
    $start_time = $field['start_time'][0]['value'];
    $end_time   = $field['end_time'][0]['value'];
    $recurrent  = $field['recurrent'][0]['value'];
    $end_date   = $field['end_date'][0]['value'];
    
    $userSubmittedTimezoneString = drupal_get_user_timezone(); // get current user timezone

    $path = \Drupal::request()->getpathInfo();
    $arg = explode('/',$path);
    $ts_id = $arg[2];

    if(is_numeric($ts_id)){

        $start_dateTime_string = $this->converToTz($start_date.' '.$start_time, "UTC", $userSubmittedTimezoneString);
        $end_dateTime_string   = $this->converToTz($end_date.' '.$end_time, "UTC", $userSubmittedTimezoneString);

        $entity = $this->getEntity();
        $entity->set('start_time', $start_dateTime_string);
        $entity->set('end_time', $end_dateTime_string);
        $entity->save();

        drupal_set_message("successfully updated");
        $form_state->setRedirect('entity.timeslots.collection');
    }
    else{
    
    $startDate_var = $start_date;

    $start_date_string = $this->converToTz($start_date.' '.$start_time, "UTC", $userSubmittedTimezoneString);
    $end_date_string   = $this->converToTz($end_date.' '.$end_time, "UTC", $userSubmittedTimezoneString);

    switch ($recurrent) {
      case 0: // No Repeat
          $starting_time = $startDate_var.' '.$start_time;
          $ending_time = $startDate_var.' '.$end_time;
          $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));
          $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
          $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);
          if(!empty($start_date_string)){
          $entity = $this->getEntity();
          $entity->set('start_date', $start_date_string);
          $entity->set('start_time', $starting_time);
          $entity->set('end_time', $ending_time);
          $entity->set('recurrent', $recurrent);
          $entity->set('end_date', $end_date_string);
          $entity->set('isbooked', '0');
          $entity->save();
        }

      break;
      case 1: // Every Day
          while ($end_date >= $startDate_var) {
              $starting_time = $startDate_var.' '.$start_time;
              $ending_time = $startDate_var.' '.$end_time;
              $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));
              $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
              $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);
              if(!empty($start_date_string)){
                $entity = entity_create('timeslots'); 
                $entity->set('start_date', $start_date_string);
                $entity->set('start_time', $starting_time);
                $entity->set('end_time', $ending_time);
                $entity->set('recurrent', $recurrent);
                $entity->set('end_date', $end_date_string);
                $entity->set('isbooked', '0');
                $entity->set('tutor_id', $user_id);
                $entity->save();
              }
              
          
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
                        'recurrent'     => $recurrent,
                        'isbooked'      => '0',
                        'created'       => time(),
                        'changed'       => time(),
                        'tutor_id'      => $user_id
                      );
                      if(!empty($start_date_string)){
                        $query = \Drupal::database();
                        $timeslot_id = $query->insert('timeslots')
                                             ->fields($field)
                                             ->execute();
                      }

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
                      'recurrent'     => $recurrent,
                      'isbooked'      => '0',
                      'created'       => time(),
                      'changed'       => time(),
                      'tutor_id'      => $user_id
                    );

                    if(!empty($start_date_string)){
                    $query = \Drupal::database();
                    $timeslot_id = $query->insert('timeslots')
                                         ->fields($field)
                                         ->execute();
                          }

                    $form_state->setRedirect('entity.timeslots.collection');

                }
                $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));

            }
            break;
      } // end switch

      $site_url = \Drupal::request()->getSchemeAndHttpHost();
      drupal_set_message("successfully saved");
      $response = new RedirectResponse($site_url."/timeslots/list");
      $response->send();
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
