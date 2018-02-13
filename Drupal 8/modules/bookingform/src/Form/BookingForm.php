<?php
/**
 * @file
 * Contains \Drupal\bookingform\Form\BookingForm.
 */

namespace Drupal\bookingform\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    $form = parent::buildForm($form, $form_state);

    $form['#prefix'] = "<div id=\"{$this->getFormId()}-wrapper\">";
    $form['#suffix'] = '</div>';

    $form['actions']['submit']['#ajax'] = [
      'wrapper' => $this->getFormId(). '-wrapper',
      'callback' => array($this, 'validateBookFormajaxCallback'),
      'effect' => 'fade',
    ];
  
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
    //echo $amount_session = $field['amount_session'][0]['value'];
    //$start_date = $field['startdate'][0]['value'];

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

      $user_id = \Drupal::currentUser()->id();

      /*$field      = $form_state->getValues();
      $start_date = $field['startdate'][0]['value'];
      $start_time = $field['starttime'][0]['value'];
      $end_time   = $field['endtime'][0]['value'];
      $recurrent  = $field['recurrent'][0]['value'];
      $end_date   = $field['enddate'][0]['value'];

      $entity = $this->getEntity();
      $entity->set('starttime', $start_dateTime_string);
      $entity->set('endtime', $end_dateTime_string);*/
      //$entity->save();

    }
    
  }
