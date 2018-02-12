<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\bookingform\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\User;
use \Drupal\Core\Url;
use \Drupal\Core\Link;
use Stripe\Stripe;

/**
 * Description of GotoPayment
 *
 * @author hakh
 */
class GotoPayment extends ControllerBase {
    /*protected $stripeApi;
    public function __construct(){
        $this->stripeApi = new StripeApiService();
        parent::__construct();
    }*/
   /**
   * Returns a page with period of a slot.
   *
   * @return array
   *   A simple renderable array.
   */
  public function gotoPaymentPage() {
        //echo $this->stripeApi->getApiKey();
        
        //$config = $this->config('stripe_api.settings');
        $link = '';
        if (\Drupal::currentUser()->isAnonymous()) {
        
            $url = Url::fromRoute('user.login');
            $link_options = array(
              'attributes' => array(
                'class' => array(
                  'use-ajax',
                  'login-popup-form',
                  'btn',
                  'button',
                  'button--primary'
                ),
                'id' => 'login_link',
                'data-dialog-type' => 'modal',
              ),
            );
            $url->setOptions($link_options);
            $link = Link::fromTextAndUrl(t(''), $url)->toString();
        }else{
            $url = Url::fromRoute('user.login');
            $link_options = array(
              'attributes' => array(
                'class' => array(
                  'use-ajax',
                  'logout-popup-form',
                  'btn',
                  'button',
                  'button--primary'
                ),
                'id' => 'login_link',
                'data-dialog-type' => 'modal',
              ),
            );
            $url->setOptions($link_options);
            $link = Link::fromTextAndUrl(t(''), $url)->toString();
        }
        
        //echo $config->get('stripe_api.test_secret_key');
        $bookingid = \Drupal::request()->query->get('bookingid');
        $session_info = array();
        $data_fee = 0.01;
        
        if($bookingid != ''){
            
            $booking_info = \Drupal::entityManager()->loadEntityByUuid('bookinginfo', $bookingid);
            $timeslot_id = $booking_info->get('timeslot_id')->value;
            
            $timeslot_info = entity_load('timeslots', $timeslot_id);
            
            $start_date_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('start_time')->value));
            $end_date_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('end_time')->value));
            
            // Get Tutor Info
            $session_data = Node::load($booking_info->get('session_id')->value);
            
            //$tutor_level = [];
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
            
            $owner = $session_data->getOwner();
            
            $fid = $owner->get('user_picture')->target_id;
            
            if($fid != ''){
                $file = File::load($fid);
                $image_uri = ImageStyle::load('medium')->buildUrl($file->getFileUri());
            }
            
            $fulname = $owner->get('field_firstname')->value." ".$owner->get('field_lastname')->value;
            $tutor_recess_time = $owner->get('field_recesstime')->value;
            if($tutor_recess_time != ''){
                $end_date_obj->sub(new \DateInterval('PT'.$tutor_recess_time.'M'));
            }
            $all_languages = \Drupal::languageManager()->getStandardLanguageList(); // Get All Languages
            
            if(drupal_get_user_timezone() == 'Europe/London' || \Drupal::service('timezone_service.has_word')->hasWord("America", drupal_get_user_timezone()) == 1){
                $session_start_time = \Drupal::service('timezone_service.has_word')->time24to12(date('H', $timeslot_info->get('start_time')->value), date('i', $timeslot_info->get('start_time')->value));
                $session_end_time = \Drupal::service('timezone_service.has_word')->time24to12(date('H', $end_date_obj->getTimestamp()), date('i', $end_date_obj->getTimestamp()));        
            }else{
                $session_start_time = date('H:i', $timeslot_info->get('start_time')->value);
                $session_end_time = date('H:i', $end_date_obj->getTimestamp()); // $timeslot_info->get('end_time')->value
            }
            
            $session_info['booking_uuid'] = $bookingid;
            $session_info['booking_id'] = $booking_info->get('id')->value;
            $session_info['booking_date'] = $booking_info->get('created')->value;
            $session_info['number_of_sessions'] = $booking_info->get('number_of_sessions')->value;
            $session_info['session_lang'] = $booking_info->get('available_language')->value;
            $session_info['student_id'] = $booking_info->student_id->target_id;
            $session_info['session_id'] = $booking_info->get('session_id')->value;
            $session_info['session_date'] = $start_date_obj->format('l d. F, Y');
            $session_info['start_time'] = $session_start_time; //date('H:i', $timeslot_info->get('start_time')->value);
            $session_info['end_time'] = $session_end_time;//date('H:i', $timeslot_info->get('end_time')->value);
            $session_info['session_time'] = (($end_date_obj->getTimestamp() - $start_date_obj->getTimestamp())/60);
            $session_info['session_title'] = $session_data->get('title')->value;
            $session_info['language'] = $all_languages[$booking_info->get('available_language')->value][0];
            $session_info['tutor_name'] = $fulname;
            
            $session_info['tutor_image'] = $image_uri;
            $session_info['session_price'] = $session_data->get('field_session_price')->value;
            $session_info['tutor_level'] = $tutor_level;
            
            $student_data = User::load($session_info['student_id'])->toArray();
            $student_mail = $student_data['mail'][0]['value'];
            $session_info['student_mail'] = $student_mail;
            
            if (\Drupal::currentUser()->isAnonymous()) {
                // Anonymous user...
                //$user = User::load($uid);
                //user_login_finalize($user);
            }
            
            $tempstore = \Drupal::service('user.private_tempstore')->get('booking_data');
            $tempstore->set('student_id', $session_info['student_id']);
            $tempstore->set('booking_id', $session_info['booking_id']);
            
            $transmission_fee = ($session_info['session_time']) * $data_fee;
            
            $session_info['session_commission'] = ($session_info['session_price'] * 20/100) * $session_info['number_of_sessions'];
            $session_info['total_price'] = $session_info['session_commission'] + $transmission_fee + ($session_info['session_price'] * $session_info['number_of_sessions']);
            $transaction_fee = round(($session_info['total_price'] * 2.9/100) + 0.3, 2);
            $tempstore->set('total_price', $session_info['total_price'] + $transaction_fee);
        }
        
        $element = array(
            '#theme' => 'gotopayment_template',
            '#session_info' => $session_info,
            '#login_link' => $link,
        );
        
        return $element;
    }
    
        public function gotoPaymentCheckoutPage() {
            //echo Stripe::setApiKey($this->getApiKey());
            $stripeToken = \Drupal::request()->request->get('stripeToken'); // to get post param
            $stripeEmail = \Drupal::request()->request->get('stripeEmail'); // to get post param

            $tempstore = \Drupal::service('user.private_tempstore')->get('booking_data');
            $student_id   = $tempstore->get('student_id');
            $booking_id   = $tempstore->get('booking_id');
            $total_price  = $tempstore->get('total_price') * 100; // Convert to Cents
            
            if($student_id != NULL){
              
                \Stripe\Stripe::setApiKey('sk_test_fPf9axbaxi8lzu50hL71NH6u');
              
                $bookorder_info = \Drupal::entityQuery('bookorder')
                ->condition('student_id', $student_id, '=')
                ->execute();
                
                if(count($bookorder_info) > 0){
                  
                }else{
                    
                    $customer = \Stripe\Customer::create(array(
                        "email" => $stripeEmail,
                        "description" => "Customer for ".$stripeEmail,
                        "source" => $stripeToken // obtained with Stripe.js
                    ));
                    
                    $charge = \Stripe\Charge::create(array(
                        'customer' => $customer->id,
                        'amount' => $total_price,
                        'currency' => 'usd',
                       'capture' => false,
                       
                    ));
                 
                }
              
                // Send Mail to Student  
              
                $booking_info = entity_load('bookinginfo', $booking_id);

                $tutor_id = $booking_info->tutor_id->target_id;
                $timeslot_id = $booking_info->get('timeslot_id')->value;
                $number_of_sessions = $booking_info->get('number_of_sessions')->value;
                $session_id = $booking_info->get('session_id')->value;
                $session_language_code =  $booking_info->get('available_language')->value;


                $student_data  = User::load($student_id)->toArray();
                $student_fname = $student_data['field_firstname'][0]['value'];
                $student_mail  = $student_data['mail'][0]['value'];

                $tutor_data = User::load($tutor_id)->toArray();
                $tutor_name = $tutor_data['field_firstname'][0]['value']." ".$tutor_data['field_lastname'][0]['value'];
                $tutor_recess_time = $tutor_data['field_recesstime'][0]['value'];

                $timeslot_info = entity_load('timeslots', $timeslot_id);
                
                $start_date_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('start_time')->value));
                $end_date_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot_info->get('end_time')->value));
                
                if($tutor_recess_time != ''){
                    $end_date_obj->sub(new \DateInterval('PT'.$tutor_recess_time.'M'));
                }
                
                $session_date = date('Y-m-d', $timeslot_info->get('start_time')->value);
                $session_start_time = date('H:i', $timeslot_info->get('start_time')->value);
                $session_end_time = date('H:i', $end_date_obj->getTimestamp());
                
                if(drupal_get_user_timezone() == 'Europe/London' || \Drupal::service('timezone_service.has_word')->hasWord("America", drupal_get_user_timezone()) == 1){
                    $session_start_time = \Drupal::service('timezone_service.has_word')->time24to12(date('H', $timeslot_info->get('start_time')->value), date('i', $timeslot_info->get('start_time')->value));
                    $session_end_time = \Drupal::service('timezone_service.has_word')->time24to12(date('H', $end_date_obj->getTimestamp()), date('i', $end_date_obj->getTimestamp()));
                }else{
                    $session_start_time = date('H:i', $timeslot_info->get('start_time')->value);
                    $session_end_time = date('H:i', $end_date_obj->getTimestamp());
                }
                
                $session_time = $session_start_time.' - '.$session_end_time;
          
                $session_data = Node::load($session_id);
                $session_title = $session_data->get('title')->value; 
                $session_price = $session_data->get('field_session_price')->value;
                $session_length = $session_data->get('field_session_length')->value;
                $session_total_price = $session_price * $number_of_sessions;
                $commission = $session_total_price * 20/100;
                $session_length = $session_length * $number_of_sessions;
                $transmission_fee = $session_length * 0.01;
                $total_price = $session_total_price + $commission + $transmission_fee;
            
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

                $service_mail_id = 39; // 39 on staging
                $all_languages = \Drupal::languageManager()->getStandardLanguageList(); // Get All Languages
                if($session_language_code == 'dk'){
                    $session_language = $all_languages['da'][0];
                }else{
                    $session_language = $all_languages[$session_language_code][0];
                }

                $service_mail_data = Node::load($service_mail_id)->toArray();
                global $base_url;

                $service_mail_body = $service_mail_data['body'][0]['value'];
                $service_mail_body = str_replace("[site_url]", $base_url, $service_mail_body);
                $service_mail_body = str_replace("[Tutor]", $tutor_name, $service_mail_body);
                $service_mail_body = str_replace("[first_name]", $student_fname, $service_mail_body);
                $service_mail_body = str_replace("[session_date]", $session_date, $service_mail_body);
                $service_mail_body = str_replace("[session_time]", $session_time, $service_mail_body);
                $service_mail_body = str_replace("[order_number]", $booking_id, $service_mail_body);
                $service_mail_body = str_replace("[session_title]", $session_title, $service_mail_body);
                $service_mail_body = str_replace("[tutor_level]", $tutor_level, $service_mail_body);
                $service_mail_body = str_replace("[Choosen_Language]", $session_language, $service_mail_body);
                $service_mail_body = str_replace("[num_of_session]", $number_of_sessions, $service_mail_body);
                $service_mail_body = str_replace("[session_price]", $session_price, $service_mail_body);
                $service_mail_body = str_replace("[session_total_price]", $session_total_price, $service_mail_body);
                $service_mail_body = str_replace("[commission]", $commission, $service_mail_body);
                $service_mail_body = str_replace("[session_length]", $session_length, $service_mail_body);
                $service_mail_body = str_replace("[transmission_fee]", $transmission_fee, $service_mail_body);
                $service_mail_body = str_replace("[total_price]", $total_price, $service_mail_body);

                $from_email = \Drupal::config('system.site')->get('mail');
                $headers = "From: " . strip_tags($from_email) . "\r\n";
                $headers .= "Reply-To: ". strip_tags($from_email) . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

                $subject = $service_mail_data['title'][0]['value'];
                
                $sent = mail($student_mail, $subject, $service_mail_body, $headers);
                
                if($sent){
                    \Drupal::logger('timeslots')->notice('Tokbox Link Mail: Your email has been sent '.$student_mail.'.');
                }else{
                    \Drupal::logger('timeslots')->notice('Tokbox Link Mail: There was a problem sending your email '.$student_mail.'.');
                }
                // Send Mail to Student

                $tempstore->set('booking_id', NULL);
                $tempstore->set('total_price', NULL);
                $tempstore->set('student_id', NULL);
            }
          
            $element = array(
                '#theme' => 'gotopayment_complete_template',
                
            );
        
            return $element;
      }
}
