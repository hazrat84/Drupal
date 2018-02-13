<?php
/**	
* 
* @file
* 
* Contains \Drupal\siteattention\sre\Form\siteattentionConfigForm
* 
**/

namespace Drupal\siteattention\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class siteattentionConfigForm extends ConfigFormBase{
	
	/**
	* {@inheritdoc}
	*/

	public function getFormId(){
		return 'siteattention_config_form';
	}

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['siteattention.settings'];
  }

	/**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $config = $this->config('siteattention.settings');
    // Read Setting Values From DB 
    $sa_key   = $config->get('siteattention.SA_KEY');
    $ilocked  = $config->get('siteattention.SA_ILOCKED');
    $sa_iid   = $config->get('siteattention.SA_IID');
    $sa_iname = $config->get('siteattention.SA_INAME');

    $form['sa_customer_info'] = array(
      '#type' => 'details',
      '#title' => t('Customer Info'),
      '#open' => TRUE,
    );

    $form['sa_customer_info']['customer_info'] = array(
      '#markup' => '<dl>          
                      <dt>Name: </dt>
                      <dd id="name">Name</dd>

                      <dt>Email: </dt>
                      <dd id="email">Email</dd> 

                      <dt>Company: </dt>
                      <dd id="companyname">Company</dd>
                                   
                      <dt>License:</dt>
                      <dd id="license">License</dd>

                      <dt>Active:</dt>
                      <dd id="active">Active</dd>

                      <dt>Limit:</dt>
                      <dd id="limit">Limit</dd>

                      <dt>Updated:</dt>
                      <dd id="updated_date">Updated</dd>

                      <dt>Expires:</dt>
                      <dd id="expires_date">Expires</dd>
                    </dl>

                    <dl class="right">
                      <img src="//cdn.shopify.com/s/files/1/1532/4977/t/5/assets/logo_80.png" alt="SiteAttention Logo" /><br /><br />
                      <span><a href="https://siteattention.com">https://siteattention.com</a></span><br />
                      <span><a href="mailto:info@siteattention.com">info@siteattention.com</a></span>
                    </dl>
                    '


    );

    $form['siteattention'] = array(
      '#type' => 'details',
      '#title' => t('SiteAttention Settings'),
      '#open' => TRUE,
    );
    $form['siteattention']['siteattention_iid'] = array(
      '#type' => 'textfield',
      '#title' => t('Instance ID'),
      '#default_value' => $sa_iid,
      '#disabled' => TRUE,
    );
    $form['siteattention']['siteattention_iname'] = array(
      '#type' => 'textfield',
      '#title' => t('Instance Name'),
      '#default_value' => $sa_iname,
      '#disabled' => $ilocked,
    );
    $form['siteattention']['sa_iname_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save Instance Name'),
      '#name' => 'save_iname_btn',
      '#disabled' => $ilocked
    );
    $form['siteattention']['siteattention_key'] = array(
      '#type' => 'textfield',
      '#title' => t('License Key'),
      '#default_value' => $sa_key,
      '#description' => t("Enter the license key provided by the SiteAttention site."),
    );
    $form['siteattention']['sa_key_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save License Key'),
      '#name' => 'save_license_key_btn',
      '#submit' => array('::save_license_key')
    );

    $form['#attached']['drupalSettings']['SA']['KEY'] = $sa_key;
    $form['#attached']['library'][] = "siteattention/siteattention.settings.css";
    $form['#attached']['library'][] = "siteattention/siteattention.settings.js";

    return $form;
  }

  /**
   *  {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $button_clicked = $form_state->getTriggeringElement()['#name']; // get the clicked button name
    
    if($button_clicked == 'save_license_key_btn'){
        $siteattention_key = $form_state->getValue('siteattention_key');
        if($siteattention_key == ''){
          $form_state->setErrorByName("siteattention_key", $this->t("License Key value can't be empty."));
        }
    }    
  }

  public function save_license_key(array &$form, FormStateInterface $form_state){
    $config = $this->config('siteattention.settings');
    $config->set('siteattention.SA_KEY', $form_state->getValue(array('siteattention_key')));
    $config->save();
    drupal_set_message('License Key Saved Successfully!', 'status');
  }

}