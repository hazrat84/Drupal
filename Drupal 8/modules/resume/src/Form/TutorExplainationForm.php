<?php
/**
 * @file
 * Contains \Drupal\bookingform\Form\TutorExplainationForm.
 */
namespace Drupal\bookingform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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

    $form['tutor_explaination'] = array(
      '#type' => 'textarea',
      '#title' => t('Tutor Explaination:'),
      '#required' => TRUE,
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send message'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {

      if (strlen($form_state->getValue('tutor_explaination')) < 10) {
        //$form_state->setErrorByName('candidate_number', $this->t('Mobile number is too short.'));
      }

    }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

   // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));

    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

   }
}