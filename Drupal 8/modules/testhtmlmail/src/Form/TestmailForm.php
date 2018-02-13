<?php
/**
 * @file
 * Contains \Drupal\testhtmlmail\Form\TestmailForm.
 */
namespace Drupal\testhtmlmail\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class TestmailForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'testmail_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['candidate_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Candidate Name:'),
      '#required' => TRUE,
    );

    $form['candidate_mail'] = array(
      '#type' => 'email',
      '#title' => t('Email ID:'),
      '#required' => TRUE,
    );
    

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {

      /*if (strlen($form_state->getValue('candidate_number')) < 10) {
        $form_state->setErrorByName('candidate_number', $this->t('Mobile number is too short.'));
      }*/

    }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
   // /srv/bindings/bf8e068f7e1c4aa19ed41110e3b9e97d/code/modules
   // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));
    
    //$account = user_load_by_mail($email);
    
    $params = array(
      'subject' => 'Test Subject',//$form_state->getValue('field_value'),
      'body' => $this->htmltext(),
    );
    $module = 'testhtmlmail';
    $key = 'booking_request';
    $to = 'officetest.by.ali@gmail.com';//\Drupal::currentUser()->getEmail();
    $target = 'engr.hazrat@gmail.com';

    $default_langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    /*if ($target_user = user_load_by_mail($target)) {
        $target_langcode = $target_user->getPreferredLangcode();
    }
    else {
        $target_langcode = $default_langcode;
    }*/
    $language = \Drupal::languageManager()->getCurrentLanguage();
  
    //$langcode = \Drupal::currentUser()->getPreferredLangcode();
    $langcode = 'en';
    $send = true;
    // Send the e-mail to the asker. Drupal calls hook_mail() via this.
    $mail_sent = \Drupal::service('plugin.manager.mail')->mail($module, $key, $to, $language, $params, $reply = NULL, $send = TRUE);
 
    // Handle sending result.
    if ($mail_sent) {
      drupal_set_message("Mail Sent successfully.");
    }
    else {
      drupal_set_message("There is error in sending mail.");
    }
    /*foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }*/

   }

   public function htmltext(){
        global $base_url;
        
        return '<table style="max-width: 700px; width: 90%; min-width: 370px; margin: 80px auto 80px; background: #fff; border: 1px solid #ded492; padding: 0;     border-spacing: 0; border-radius:5px;">
  <tbody style="margin: 0; padding: 0; display: block; background:#F3F4F5;">
    <tr style="display: block;">
      <td style="display: block; padding:0 10px; border-radius: 5px 5px 0 0;"><img src="'.$base_url.'/themes/custom/peopleteachme/images/logo-teach-me.png" class="img-responsive" style="padding: 10px 0;" alt="Logo"/>
      <h3 style="color: #000; font-size: 26px;padding-top: 20px;margin: 0;text-align: center;">Please respond to the booking request</h3>
      </td>
    </tr>
    <tr style="display: block; border: 1px solid rgba(0,0,0,0.1); background: #fff; margin:10px 10px 0px 10px; padding:10px 20px 20px;">
      <td style="padding-top:10px;"><strong style="margin: 0;">We’re happy to informe you, that you have a request!</strong>
        <br><br>
        <p style=" margin: 0; color:rgba(28, 40, 60, 0.54); text-align: justify;">Please respond to the member wether or not you are able to accept the request:</p>
        <p style=" margin-left:120px;display: block; color:rgba(28, 40, 60, 0.54); margin-bottom: 0; text-align: justify;">[Session data]</p>
        <p style=" margin-left:120px;display: block; color:rgba(28, 40, 60, 0.54);     margin-top: 5px;margin-bottom: 0;text-align: justify;">[Booking data]</p>
       
       </td>
        <td style="display: inline-block;padding: 30px 0; width:50%; text-align: left; ;">
         <a href="'.$base_url.'/booking/thankyou" style="cursor: pointer; display: inline;color: #fff;font-weight: bold;background-color: #d9534f;border-color: #d43f3a;border: none; margin: 0; padding: 10px;border-radius: 3px; text-decoration:none;">
         <!-- <button style="display: inline;color: #fff;font-weight: bold;background-color: #d9534f;border-color: #d43f3a;border: none; margin: 0; padding: 10px;border-radius: 3px;">I accept the request</button> --> I accept the request
         </a>
        </td>
        <td style="display: inline-block;padding: 30px 0;float:right; width:50%; text-align: left;">
       <a href="'.$base_url.'booking/thankyou" style="display: inline;color: #fff;font-weight: bold;background-color: #d9534f;border-color: #d43f3a; border: none; margin: 0; padding: 10px;border-radius: 3px; text-decoration:none;">
       <!-- <button style="display: inline;color: #fff;font-weight: bold;background-color: #d9534f;border-color: #d43f3a; border: none; margin: 0; padding: 10px;border-radius: 3px;">I decline the request</button> --> I decline the request
        </a>
        </td>
        <td>
      <p style=" padding: 20px 0;display: block; color:rgba(28, 40, 60, 0.54);     margin-top: 5px;margin-bottom: 0;text-align: justify;">Please respond as fast as possible. The request will expire after 48 hours.</p>
       </td>
      
      
    </tr>
    <tr style="display: block; padding-top:10px;">
      <td style="display: block; padding: 10px;"><img src="'.$base_url.'/themes/custom/peopleteachme/images/logo-teach-me.png" class="img-responsive" style="padding: 10px 0;" alt="Logo"/> </td>
    </tr>
    <tr style="display: block; padding: 30px 0;text-align: center;width: 100%; background: #1C283C;">
      <td style="display: block;"><span style="color: #fff; font-size: 30px;" >Footer</span>
      </td>
    </tr>
  </tbody>
</table>';
    }

}