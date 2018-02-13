<?php

namespace Drupal\timeslots\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;

/**
 * Class DeleteForm
 * 
 * @package Drupal\timeslots\Form
 */

class DeleteForm extends ConfirmFormBase {
	/**
	 * {@inheritdoc}
	 */

	public function getFormId(){
		return 'delete_form';
	}

	public $tid;

	public function getQuestion(){
		return t('Do you want to delete %tid?', array('%tid' => $this->tid));
	}

	public function getCancelUrl(){
		return new Url('timeslots.display_timeslots_controller_display');
	}

	public function getDescription(){
		return t('Only do this if you are sure!');
	}

	/**
	 * {@inheritdoc}
	 */

	public function getConfirmText(){
		return t('Delete it!');
	}

	/**
	 * {@inheritdoc}
	 */

	public  function getCancelText(){
		return t('Cancel');
	}

	/**
	 * {@inheritdoc}
	 */

	public function buildForm(array $form, FormStateInterface $form_state, $tid = NULL){
		$this->id = $tid;
		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */

	public function validateForm(array &$form, FormStateInterface $form_state) {
    	$query = \Drupal::database()->select('timeslots_schedule', 'ts');
		$query->join('timeslots', 't', 't.id = ts.timeslot_id');
		$query->fields('ts', array('id', 'starttime', 'endtime', 'isbooked', 'timeslot_id'));
		$query->fields('t', array('id', 'enddate', 'recurrent'));
		$query->condition('ts.id', $_GET['ts_id']);
		$record = $query->execute()->fetchAssoc();

		if($record->isbooked == '1'){
			$form_state->setError($this->t("This slot is booked so you can't delete it"));
			return false;
		}
    	parent::validateForm($form, $form_state);
  	}

  	/**
   	* {@inheritdoc}
   	*/
	  public function submitForm(array &$form, FormStateInterface $form_state) {
	       $query = \Drupal::database();
	       $query->delete('timeslots_schedule')
	                   ->condition('id',$this->id)
	                  ->execute();
	        drupal_set_message("succesfully deleted");
	        $form_state->setRedirect('timeslots.display_timeslots_controller_display');
	  }
}