<?php
/**
 * @file
 * Contains \Drupal\bookingform\Form\BookingForm.
 */

namespace Drupal\bookingform\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use \Drupal\Core\Url;
use \Drupal\Core\Link;

/**
 * Form controller for the content_entity_example entity edit forms.
 *
 * @ingroup content_entity_example
 */
class BookingForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\bookingform\Entity\BookingInfo */
    db_query("TRUNCATE cache_entity");  
    $form = parent::buildForm($form, $form_state);
    $form['#attached']['library'][] = "bookingform/bookingform.calculate.js"; // Calculate Total Price
    
    unset($form['number_of_sessions']['widget']['#options']['_none']);
    
    $form['number_of_sessions']['widget']['#attributes'] = array('class' => array('selectpicker', 'form-control'));
    $form['available_language']['widget']['#attributes'] = array('class' => array('selectpicker', 'form-control'));
    $form['actions']['submit']['#attributes'] = array('class' => array('btn', 'orangeBtn'), 'style' => 'margin: 10px; width:calc(100% - 20px)');
    
    $form['session_amount']['#id'] = 'session_amount';
    $form['session_amount']['#type'] = 'hidden';
    $form['commission_fee']['#type'] = 'hidden';
    
    
    $path = \Drupal::request()->getpathInfo();
    $arg  = explode('/',$path);
    $node_id = '';
    $userLanguages = array();
    
    if($arg[2] == 'node' || $arg[1] == 'node'){
        if($arg[2] == 'node'){
            $node_id = $arg[3];
        }else{
            $node_id = $arg[2];
        }
    }
    
    $session_price = 0;
    if($node_id != ''){
        
        // Get Current User Login id
        $login_user_id = \Drupal::currentUser()->id();
        
        $session_data = Node::load($node_id)->toArray();
        $tutor_id = $session_data['uid'][0]['target_id'];
        $session_length = $session_data['field_session_length'][0]['value'];
        $all_languages = \Drupal::languageManager()->getStandardLanguageList(); // Get All Languages 
        
        if($login_user_id == $tutor_id){ // if Tutor is looking at it's own session so hide booking button
            $form['actions']['submit']['#type'] = 'hidden';
        }
        
        // Populate Available languages with session language
        $session_languages = $session_data['field_language'];
        foreach ($session_languages as $key => $uLang) {
            $lang_code = $uLang['value'];
            $userLanguages[$uLang['value']] = $all_languages[$lang_code][0];
        }
        
        $tempstore = \Drupal::service('user.private_tempstore')->get('booking_data');
        $tempstore->set('tutor_id', $tutor_id);
        $tempstore->set('session_length', $session_length);
        $tempstore->set('session_id', $node_id);
        $session_price = $session_data['field_session_price'][0]['value'];
        
    }
    
    $form['available_language']['widget']['#options'] = $userLanguages;
    
    $form['price'] = array(
     '#type' => 'hidden',
     '#attributes' => array(
        'id' => 'price',
     ),
     '#default_value' => $session_price,
    );
    
    $form['session_length'] = array(
     '#type' => 'hidden',
     '#attributes' => array(
        'id' => 'session_length',
     ),
     '#default_value' => $session_length,
    );
    
    /*$form['#prefix'] = "<div id=\"{$this->getFormId()}-wrapper\">";
    $form['#suffix'] = '</div>';

    $form['actions']['submit']['#ajax'] = [
      'wrapper' => $this->getFormId(). '-wrapper',
      'callback' => array($this, 'validateBookFormajaxCallback'),
      'effect' => 'fade',
    ];*/
    // if the student is not login
    if (\Drupal::currentUser()->isAnonymous()) {
        
        $url = Url::fromRoute('user.login');
        $link_options = array(
          'attributes' => array(
            'class' => array(
              'use-ajax',
              'login-popup-form',
              'btn',
              'orangeLink',
              'button',
              'button--primary'
            ),
            'data-dialog-type' => 'modal',
          ),
        );
        $url->setOptions($link_options);
        $link = Link::fromTextAndUrl(t('Request booking'), $url)->toString();
        
        $form['actions']['#markup'] = ''
                . '<div class="bookingreceipt booking-overview">
                        <h3 class="text-center">Booking overview</h3>
                        <ul>
                            <li><span class="pull-left"><span id="num_of_session">1</span> Session x <span id="session_single_price">18</span> US$</span><span class="pull-right"><span id="session_price">18,00</span> US$</span></li>
                            <li><span class="pull-left">Commission Fee</span><span class="pull-right"><span id="comm_fee">3,60</span> US$</span></li>
                            <li><span class="pull-left"><span id="session_lenth">0</span> min. datacharge</span><span class="pull-right"><span id="transmission_fee">0</span> US$</span></li>
                            <li><span class="pull-left"><strong>Total</strong></span><span class="pull-right"><strong><span id="total_price">0.00</span> US$</strong></span></li>
                        </ul>
                        <span class="text-center padding-top col-xs-12 discriptionBookingreceipt">Additional small Transaction FEE will be added.</span>'
                . '<span class="Login-popup-link">' . $link . '</span>'
                . '<span class="text-center col-xs-12 padding-bottom discriptionBookingreceipt discriptionBookingreceiptlrgfont">Your credit card won’t be charged yet</span></div>';
        
        $form['actions']['submit']['#access'] = false;
        
    }
    else{
        
        $tempstore = \Drupal::service('user.private_tempstore')->get('booking_url');
        $tempstore->set('booking_page_url', NULL);
        $form['actions']['submit']['#value'] = t('Request booking');
        $form['actions']['submit']['#prefix'] = '<div class="bookingreceipt booking-overview">
                        <h3 class="text-center">Booking overview</h3>
                        <ul>
                            <li><span class="pull-left"><span id="num_of_session">1</span> Session x <span id="session_single_price">18</span> US$</span><span class="pull-right"><span id="session_price">18,00</span> US$</span></li>
                            <li><span class="pull-left">Commission Fee</span><span class="pull-right"><span id="comm_fee">3,60</span> US$</span></li>
                            <li><span class="pull-left"><span id="session_lenth">0</span> min. datacharge</span><span class="pull-right"><span id="transmission_fee">0</span> US$</span></li>
                            <li><span class="pull-left"><strong>Total</strong></span><span class="pull-right"><strong><span id="total_price">0.00</span> US$</strong></span></li>
                        </ul>
                        <span class="text-center padding-top col-xs-12 discriptionBookingreceipt">Additional small Transaction FEE will be added.</span>';
        $form['actions']['submit']['#suffix'] = '<span class="text-center col-xs-12 padding-bottom discriptionBookingreceipt discriptionBookingreceiptlrgfont">Your credit card won’t be charged yet</span></div>';
    }
    return $form;
  }

  public function validateBookFormajaxCallback(array $form, FormStateInterface $form_state) {
    //drupal_set_message(t('Entity was successfully created'));

    // @todo Clear form values.
    //$form_state->setRebuild(TRUE);
    //$form_state->setValues([]);
    /*$entity = \Drupal::entityTypeManager()->getStorage('liveblog_post')->create([]);
    $form_object = \Drupal::entityTypeManager()
      ->getFormObject('liveblog_post', 'add')
      ->setEntity($entity);*/
    //$new_form_state = new FormState();
    //$form = \Drupal::formBuilder()->rebuildForm($this->getFormId(), $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */

    public function validateForm(array &$form, FormStateInterface $form_state){
        
        $field      = $form_state->getValues();
        $tempstore = \Drupal::service('user.private_tempstore')->get('booking_data');
        
        if(isset($field['number_of_sessions'][0])){
            $number_of_sessions = $field['number_of_sessions'][0]['value']; 
            $tempstore->set('number_of_sessions', $number_of_sessions);
        }

        $available_language = $field['available_language'][0]['value'];
        $session_amount = $field['session_amount_wrapper']; // session price from the hidden field
        $commission_fee     = $field['commission_fee_wrapper'];  // Commission fee from the hidden field

        $tempstore->set('available_language', $available_language);
        $tempstore->set('session_amount', $session_amount);
        $tempstore->set('commission_fee', $commission_fee);
    }

  /**
   * {@inheritdoc}
   */
    public function save(array $form, FormStateInterface $form_state) {
    
        $user_id = \Drupal::currentUser()->id();
        $url = Url::fromRoute('bookingform.book_date_time_page');
        $form_state->setRedirectUrl($url);
    }
    
  }
