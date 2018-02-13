<?php
namespace Drupal\timeslots\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 *  Class AddTimeslotForm
 * 
 * @package Drupal\timeslots\Form
 */

class AddTimeslotForm extends FormBase{
	/**
	 *  {@inheritdoc}
	 */

	public function getFormId(){
		return 'timeslots_form';
	}

	/**
	 * {@inheritdoc}
	 */

	public function buildForm(array $form, FormStateInterface $form_state){

		$userSubmittedTimezoneString = drupal_get_user_timezone(); // Get User TimeZone

		$conn = Database::getConnection();
		$record = array();
		$submit_txt = 'save';

		if(isset($_GET['ts_id'])){
			
			$query = $conn->select('timeslots_schedule', 'ts');
			$query->join('timeslots', 't', 't.id = ts.timeslot_id');
			$query->fields('ts', array('id', 'starttime', 'endtime', 'isbooked', 'timeslot_id'));

			$query->fields('t', array('id', 'enddate', 'recurrent'));
			$query->condition('ts.id', $_GET['ts_id']);
			$record = $query->execute()->fetchAssoc();

			$userTz_starttime = $this->converToTz(date('Y-m-d H:i:s', $record['starttime']), $userSubmittedTimezoneString, "UTC");
			$userTz_endtime   = $this->converToTz(date('Y-m-d H:i:s', $record['endtime']), $userSubmittedTimezoneString, "UTC");
			$userTz_enddate   = $this->converToTz(date('Y-m-d H:i:s', $record['endtime']), $userSubmittedTimezoneString, "UTC");
			$period = $record['recurrent'];

			$submit_txt = 'update';
		}

		$form['start_date'] = array(
			'#type' => 'textfield',
			'#placeholder' => $this->t('Start Date like 2017-05-01'),
			'#title' => t('Start Date:'),
			'#required' => TRUE,
			'#default_value' => (isset($userTz_starttime) && $_GET['ts_id']) ? date('Y-m-d', $userTz_starttime):'',
 		);

 		$form['start_time'] = array(
	      '#type' => 'textfield',
	      '#placeholder' => $this->t('Start Time like 15:00'),
	      '#title' => t('Start Time:'),
	      '#default_value' => (isset($userTz_starttime) && $_GET['ts_id']) ? date('H:i', $userTz_starttime):'',
	      );
	    $form['end_time'] = array(
	      '#type' => 'textfield',
	      '#placeholder' => $this->t('End Time like 18:00'),
	      '#title' => t('End Time:'),
	      '#required' => TRUE,
	      '#default_value' => (isset($userTz_endtime) && $_GET['ts_id']) ? date('H:i', $userTz_endtime):'',
	      );
	    $form['recurrent'] = array(
  			'#type' => 'select',
  			'#title' => $this->t('Repeat'),
  			'#default_value' => $period,
			'#options' => [
				'0' => $this->t('No Repeat'),
			    '1' => $this->t('Everyday'),
			    '2' => $this->t('Business Day'),
			    '3' => $this->t('Weekends'),
			],
			'#description' => $this->t('Select Repeat type')
			
  		);
	    $form['end_date'] = array (
	      '#type' => 'textfield',
	      '#placeholder' => $this->t('End Date like 2017-05-01'),
	      '#title' => t('End Repeat'),
	      '#required' => TRUE,
	      '#default_value' => (isset($userTz_enddate) && $_GET['ts_id']) ? date('Y-m-d', $userTz_enddate):'',
	       );
	    $form['submit'] = [
	        '#type' => 'submit',
	        '#value' => $submit_txt,
	    ];

    	return $form;

	}

	/**
	 * {@inheritdoc}
	 */

	public function validateForm(array &$form, FormStateInterface $form_state){
		
	}

	/**
	 * {@inheritdoc}
	 */

	public function submitForm(array &$form, FormStateInterface $form_state){

  		$user_id = \Drupal::currentUser()->id();

		$field      = $form_state->getValues();
		$start_date = $field['start_date'];
		$start_time = $field['start_time'];
	    $end_time   = $field['end_time'];
	    $recurrent  = $field['recurrent'];
	    $end_date   = $field['end_date'];
	    $created_date   = time();
	    $modified_date   = time();
	    $tutor_id   = $user_id;
	    $startDate_var = $start_date;
	    //print_r($field);

	    if(isset($_GET['ts_id'])){

	    	$userSubmittedTimezoneString = drupal_get_user_timezone();

	    	$start_dateTime_string = $this->converToTz($start_date.' '.$start_time, "UTC", $userSubmittedTimezoneString);
	    	$end_dateTime_string   = $this->converToTz($end_date.' '.$end_time, "UTC", $userSubmittedTimezoneString);

	    	$field = array(
	    		'starttime' => $start_dateTime_string,
	    		'endtime' => $end_dateTime_string,
	    		'modified_date' =>  time(),
	    	);

	    	$query = \Drupal::database();
	    	$query->update('timeslots_schedule')
	    		  ->fields($field)
	    		  ->condition('id', $_GET['ts_id'])
	    		  ->condition('id', $_GET['ts_id'])
	    		  ->execute();
	    	drupal_set_message("successfully updated");
	    	$form_state->setRedirect('timeslots.display_timeslots_controller_display');
	    }
	    else
	    { // if no edit ID
	    	
	    	$userSubmittedTimezoneString = drupal_get_user_timezone();
	    	$timeslot_id = 0;
	    	$site_url = \Drupal::request()->getSchemeAndHttpHost();

	    	$start_date_string = $this->converToTz($start_date.' '.$start_time, "UTC", $userSubmittedTimezoneString);
	    	$end_date_string   = $this->converToTz($end_date.' '.$end_time, "UTC", $userSubmittedTimezoneString);

	    	$field  = array(
              'startdate'     => $start_date_string,
              'enddate'       => $end_date_string,
              'recurrent'     => $recurrent,
              'created_date'  => time(),
              'modified_date' => time(),
              'tutor_id'      => $user_id
          	);

	    	$query = \Drupal::database();
	    	$timeslot_id = $query->insert('timeslots')
	    		  				 ->fields($field)
	    		  				 ->execute();

	    	switch ($recurrent) {
			    case 1: // Every Day
			        while ($end_date >= $startDate_var) {
			            $starting_time = $startDate_var.' '.$start_time;
			            $ending_time = $startDate_var.' '.$end_time;
			            $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));
			            $starting_time = $this->converToTz($starting_time, "UTC", $userSubmittedTimezoneString);
			            $ending_time = $this->converToTz($ending_time, "UTC", $userSubmittedTimezoneString);

			            $schedule_field  = array(
			              'starttime'     => $starting_time,
			              'endtime'       => $ending_time,
			              'isbooked'     => 0,
			              'created_date'  => time(),
			              'modified_date' => time(),
			              'timeslot_id'      => $timeslot_id
			          	);
			            $query->insert('timeslots_schedule')
	    		  				 ->fields($schedule_field)
	    		  				 ->execute();
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

			            	$schedule_field  = array(
				              'starttime'     => $starting_time,
				              'endtime'       => $ending_time,
				              'isbooked'     => 0,
				              'created_date'  => time(),
				              'modified_date' => time(),
				              'timeslot_id'      => $timeslot_id
				          	);
				            $query->insert('timeslots_schedule')
		    		  				 ->fields($schedule_field)
		    		  				 ->execute();
			                
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

			            	$schedule_field  = array(
				              'starttime'     => $starting_time,
				              'endtime'       => $ending_time,
				              'isbooked'     => 0,
				              'created_date'  => time(),
				              'modified_date' => time(),
				              'timeslot_id'      => $timeslot_id
				          	);
				            $query->insert('timeslots_schedule')
		    		  				 ->fields($schedule_field)
		    		  				 ->execute();
			                
			            }
			            $startDate_var = date('Y-m-d', strtotime($startDate_var . ' +1 day'));

			        }
			        break;
			}

	    	drupal_set_message("successfully saved");
	    	$response = new RedirectResponse($site_url."/drupal_adv/timeslots");
	    	$response->send();
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