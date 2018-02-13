<?php
/**
 * @file
 * Contains \Drupal\bookingform\Form\TutorExplainationForm.
 */
namespace Drupal\bookingform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

class TutorExplainationForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tutorexplaination_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));
    
    $form['tutor_explaination'] = array(
      '#type' => 'textarea',
      '#title' => t('Tutor Explaination:'),
      '#attributes' => array('class' => array('tutor_explaination_textarea')),
      '#required' => TRUE,
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#attributes' => array('class' => array('tutor_Send_btn')),
      '#value' => $this->t('Send message'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {

        if ($form_state->getValue('tutor_explaination') == '') {
            $form_state->setErrorByName('tutor_explaination', $this->t('Enter some explaintion for declining.'));
        }
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));
        $booking_id = \Drupal::request()->query->get('booking_id');
        $entity = \Drupal::entityManager()->loadEntityByUuid('bookinginfo', $booking_id);
        $service_mail_id = 38; // 38 for staging
        $all_languages = \Drupal::languageManager()->getStandardLanguageList(); // Get All Languages

        $service_mail_data = Node::load($service_mail_id)->toArray();
        global $base_url;
        
        if($entity->get('available_language')->value == 'dk'){
            $session_language = $all_languages['da'][0];
        }else{
            $session_language = $all_languages[$entity->get('available_language')->value][0];
        }
        
        $order_id = $entity->get('id')->value;
        $session_id = $entity->get('session_id')->value;
        $timeslot_id = $entity->get('timeslot_id')->value;

        $tutor_id = $entity->tutor_id->target_id;
        $student_id = $entity->student_id->target_id;

        $user_data = User::load($tutor_id)->toArray();
       $tutor_recess_time = $user_data['field_recesstime'][0]['value'];
        $tutor_name = $user_data['field_firstname'][0]['value']." ".$user_data['field_lastname'][0]['value'];

        $student_data = User::load($student_id)->toArray();
        $student_mail = $student_data['mail'][0]['value'];
        $student_timezone = $student_data['timezone'][0]['value'];

        $timeslot_info = entity_load('timeslots', $timeslot_id);
        $session_date = date('Y-m-d', $timeslot_info->get('start_time')->value);
        
        date_default_timezone_set($student_timezone); // set the timezone to that of student
        
        $start_date_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('start_time')->value));
        $end_date_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('end_time')->value));
 
        //$session_start_time = date('H:i', $timeslot_info->get('start_time')->value);
        if($tutor_recess_time !=''){
            $end_date_obj->sub(new \DateInterval('PT'.$tutor_recess_time.'M'));
        }
        
        //$session_end_time = date('H:i', $end_date_obj->getTimestamp());
        
        if($student_timezone == 'Europe/London' || \Drupal::service('timezone_service.has_word')->hasWord("America", $student_timezone) == 1){
            $session_start_time = \Drupal::service('timezone_service.has_word')->time24to12(date('H', $timeslot_info->get('start_time')->value), date('i', $timeslot_info->get('start_time')->value));
            $session_end_time = \Drupal::service('timezone_service.has_word')->time24to12(date('H', $end_date_obj->getTimestamp()), date('i', $end_date_obj->getTimestamp()));
                        
        }else{
            $session_start_time = date('H:i', $timeslot_info->get('start_time')->value);
            $session_end_time = date('H:i', $end_date_obj->getTimestamp());
        }

        $session_time = $session_start_time.' - '.$session_end_time;
        
        date_default_timezone_set(drupal_get_user_timezone()); // set the timezone back to tutor
        
        $session_data = Node::load($session_id);
        $session_title = $session_data->get('title')->value;
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
        $new_booking_link = $base_url.'/en/node/'.$session_id;
        
        $tutor_explaination = $form_state->getValue('tutor_explaination');

        $service_mail_body = $service_mail_data['body'][0]['value'];
        $service_mail_body = str_replace("[new_booking_link]", $new_booking_link, $service_mail_body);
        $service_mail_body = str_replace("[site_url]", $base_url, $service_mail_body);
        $service_mail_body = str_replace("[session_title]", $session_title, $service_mail_body);
        $service_mail_body = str_replace("[tutor_level]", $tutor_level, $service_mail_body);
        $service_mail_body = str_replace("[Choosen_Language]", $session_language, $service_mail_body);
        $service_mail_body = str_replace("[order_number]", $order_id, $service_mail_body);
        $service_mail_body = str_replace("[session_date]", $session_date, $service_mail_body);
        $service_mail_body = str_replace("[session_time]", $session_time, $service_mail_body);
        $service_mail_body = str_replace("[Tutor]", $tutor_name, $service_mail_body);
        $service_mail_body = str_replace("[Tutor_explanation]", $tutor_explaination, $service_mail_body);

        $from_email = \Drupal::config('system.site')->get('mail');
        $headers = "From: " . strip_tags($from_email) . "\r\n";
        $headers .= "Reply-To: ". strip_tags($from_email) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        
        $subject = $service_mail_data['title'][0]['value'];
        //echo $service_mail_body;exit;
        $sent = mail($student_mail, $subject, $service_mail_body, $headers);
        if($sent){
                        \Drupal::logger('timeslots')->notice('Booking Request Decline: Your email has been sent '.$student_mail.'.');
            }else{
                        \Drupal::logger('timeslots')->notice('Booking Request Decline: There was a problem sending your email '.$student_mail.'.');
                    }
        drupal_set_message();

   }
}