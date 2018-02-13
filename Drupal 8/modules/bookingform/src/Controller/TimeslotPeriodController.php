<?php
namespace Drupal\bookingform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\timezone_service\TZServices;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route responses for the bookingform module.
 */
class TimeslotPeriodController extends ControllerBase {

  private $entityQuery, $timezoneService, $userPrivateTempStore;

  public function __construct(QueryFactory $entityQuery, TZServices $timezoneService, PrivateTempStoreFactory $userPrivateTempStore){
    $this->entityQuery = $entityQuery;
    $this->timezoneService = $timezoneService;
    $this->userPrivateTempStore = $userPrivateTempStore;
  }
  /**
   * Returns a page with period of a slot.
   *
   * @return array
   *   A simple renderable array.
   */
  public function createTimeslotPeriod(Request $Request) {
   // Get Tutor Slots for dividing in ession length
    $output = '';
    
    $tempstore = $this->userPrivateTempStore->get('booking_data');
    $tutor_id = $tempstore->get('tutor_id');
    
    $session_length = $tempstore->get('session_length');
    $no_of_session = $tempstore->get('number_of_sessions');
    $session_id = $tempstore->get('session_id');
    
    // get Tutor Image Url
    if($tutor_id){
        $tutor = User::load($tutor_id);
        $image_id = $tutor->get('user_picture')->target_id;
        // check that the field exists and that it has a value.
        if($image_id){
            $file = File::load($image_id);
            $image_uri = ImageStyle::load('medium')->buildUrl($file->getFileUri());
        }
        
        $recess_time = $tutor->get('field_recesstime')->value;
        
        if($recess_time == ''){
            $recess_time = 0;
        }
    }else{
        $recess_time = 0;
    }
    
    // Recess time set by Tutor from his account
    $session_duration = ($session_length * $no_of_session); // Like 45 min X 1 = 45 min or 45 min X 2 = 90 min 
    $session_duration_plus_recess = $session_duration + $recess_time;
    
    // Find current tutor time according to his own timezone
    date_default_timezone_set(drupal_get_user_timezone());
    $user_time = time(); // Returns User Standard Time

    $query = $this->entityQuery->get('timeslots');
    $query->condition('tutor_id', $tutor_id, '=');
    $query->condition('start_time', $user_time, '>');
    $query->condition('is_booked', 0, '=');
    $query->sort('start_time', 'ASC');
    $tutor_slots = $query->execute(); // it will return all entity ids for which there is a match
    
    /*$tutor_slots = \Drupal::entityQuery('timeslots')
    ->condition('tutor_id', $tutor_id, '=')
    ->condition('start_time', $user_time, '>')
    ->condition('is_booked', 0, '=')
    ->sort('start_time', 'ASC')
    ->execute();*/
   
    $tutor_slots_arr = entity_load_multiple('timeslots', $tutor_slots);
    $timeslot_count = count($tutor_slots_arr);
    
    $time_periods = array();
    
    if(count($tutor_slots_arr) > 0){
        foreach($tutor_slots_arr as $timeslot){
            
           $timeslot_id = $timeslot->get('id')->value;
           
           $startdate_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot->get('start_time')->value));
           $enddate_obj = new \DateTime(date('Y-m-d H:i:s', $timeslot->get('end_time')->value));
           
           if($startdate_obj->format('i') == 01){
               $startdate_obj->modify("-1 minute");
           }
           
          $startDate = strtoupper(date('D d. M. Y', $timeslot->get('start_time')->value));
           
           $min_diff = ($timeslot->get('end_time')->value - $timeslot->get('start_time')->value)/60;
           
           $slots = (int)($min_diff/$session_duration_plus_recess);
           
           if($slots > 0){
               $output .= "<div>".$startDate."</div>";
           }
           
           for($i = 0; $i < $slots; $i++){
               
                if(drupal_get_user_timezone() == 'Europe/London' || $this->timezoneService->hasWord("America", drupal_get_user_timezone()) == 1){
                    $period_start = $this->timezoneService->time24to12($startdate_obj->format('H'), $startdate_obj->format('i'));
                }else{
                    $period_start = $startdate_obj->format('H:i');
                }
               
               $start_period_tmestamp = $startdate_obj->getTimestamp(); 
               $startdate_obj->add(new \DateInterval('PT'.$session_duration_plus_recess.'M'));
                if(drupal_get_user_timezone() == 'Europe/London' || $this->timezoneService->hasWord("America", drupal_get_user_timezone()) == 1){
                    $period_end = $this->timezoneService->time24to12($startdate_obj->format('H'), $startdate_obj->format('i'));
                }else{
                    $period_end = $startdate_obj->format('H:i');
                }
                $end_period_tmestamp = $startdate_obj->getTimestamp();
                
                $time_periods[$startDate][$i]['timestamp_start'] = $start_period_tmestamp;
                $time_periods[$startDate][$i]['timestamp_end'] = $end_period_tmestamp;
                $time_periods[$startDate][$i]['start_time'] = $period_start;
                $time_periods[$startDate][$i]['end_time'] = $period_end;
                $time_periods[$startDate][$i]['timeslot_id'] = $timeslot_id;
                
           }
        }
        
    }
    else{
        $output = $this->t('No Time slot is added by this Tutor');
    }
    
   $site_url = $Request->getSchemeAndHttpHost().'/node/'.$session_id;
   if(!isset($image_uri)){
       $image_uri = '';
   }
   $element = array(
        '#theme' => 'timeperiod_template',
        '#time_periods' => $time_periods,
        '#tutor_img' => $image_uri,
        '#session_length' => $session_duration,
        '#timeslot_count' => $timeslot_count,
        '#session_url' => $site_url,
        '#recess_time' => $recess_time,
        '#attached' => [
			'library' => [
				'bookingform/bookingform.calculate.js',
			],
		],
    );
    return $element;
  }

  // create method is available to us when we extend from ControllerBase
  public static function create(ContainerInterface $container) {
    // ContainerInterface is step 1 when your controller needs to access services from the container
    
    // Create a $entityQuery variable, set it to $container->get(''); and pass it the name of the service: 
    //entity.query
    //$loggerFactory = $container->get('logger.factory');

    return new static($container->get('entity.query'), $container->get('timezone_service.has_word'), $container->get('user.private_tempstore')); // Create a new instance of TimeslotPeriodController and return it
  }

}