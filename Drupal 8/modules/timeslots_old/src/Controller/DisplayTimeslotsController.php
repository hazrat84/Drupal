<?php
namespace Drupal\timeslots\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;
/**
* Class DisplayTimeslotsController
* 
* @package Drupal\timeslots\Controller
*/

class DisplayTimeslotsController extends ControllerBase {
    // https://drupal.stackexchange.com/questions/214797/display-data-from-controller-in-a-block-or-a-view
    public function list_timeslots(){
        
        $user_id = \Drupal::currentUser()->id(); // get current user id

        $userSubmittedTimezoneString = drupal_get_user_timezone();
        $header_table = array(
            'id' => t('Id'),
            'Start Date' => t('Start Date'),
            'End Date' => t('End Date'),
            'opt' => t('Operations'),
        );

        // select records from table
        $query = \Drupal::database()->select('timeslots_schedule', 'ts');
        $query->join('timeslots', 't', 't.id = ts.timeslot_id');
        $query->addExpression('GROUP_CONCAT(DATE_FORMAT(FROM_UNIXTIME(starttime), \'%H:%i:%s\'))', 'starttime');
        $query->addExpression('GROUP_CONCAT(DATE_FORMAT(FROM_UNIXTIME(endtime), \'%H:%i:%s\'))', 'endtime');
        $query->addExpression('GROUP_CONCAT(isbooked)', 'isbooked');
        $query->addExpression('GROUP_CONCAT(ts.id)', 'ts.id');
        $query->addExpression('DATE_FORMAT(FROM_UNIXTIME(`starttime`), \'%Y-%m-%d\')', 'date_timeslot');
        $query->groupBy('date_timeslot');
        $query->orderBy('date_timeslot');
        $query->condition('t.tutor_id', $user_id, '=');
        
        // Limit the rows to 10 for each page.
        $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
                        ->limit(10);
                        //print_r($query->__toString());exit;
        // Get all the results
        $results = $pager->execute()->fetchAll(\PDO::FETCH_OBJ);

        $rows = array();
        //print_r($results);exit;
        foreach($results as $data){
            $delete = Url::fromUserInput('/timeslots/form/delete/'.$data->tsid);
            $edit = Url::fromUserInput('/timeslots/form/?ts_id='.$data->tsid);
            $options = array(
              'attributes' => ['class' => ['use-ajax'], 'data-dialog-type' => 'modal', 'data-dialog-options' => json_encode(['height' => 400, 'width' => 700]),],
              'absolute'   => TRUE,
            );

            // Convert UTC DB Time to User Time
            
            $timeslot_id_arr = explode(",",$data->tsid);
            $start_time_arr = explode(",",$data->starttime);
            $end_time_arr   = explode(",",$data->endtime);
            $isbooked_arr   = explode(",",$data->isbooked);

            //$userSubmittedTimezoneString = 'America/New_York';

            for($i = 0; $i < count($start_time_arr); $i++){
                
                $start_dateTime = $data->date_timeslot.' '.$start_time_arr[$i];
                $start_time = date('Y-m-d H:i:s a', $this->converToTz($start_dateTime, $userSubmittedTimezoneString, "UTC"));

                $end_dateTime = $data->date_timeslot.' '.$end_time_arr[$i];
                $end_time = date('Y-m-d H:i:s', $this->converToTz($end_dateTime, $userSubmittedTimezoneString, "UTC"));

                $isbooked_status = $isbooked_arr[$i];
            
                if($isbooked_status == '1'){
                    $isbooked_class = 'bg-danger';
                }

                $edit_link   = Link::fromTextAndUrl(t('Edit'), Url::fromRoute('timeslots.timeslots_form', ['ts_id' => $timeslot_id_arr[$i], 'action' => 'edit']))->toString();
                $delete_link = Link::fromTextAndUrl(t('Delete'), Url::fromRoute('timeslots.delete_form', ['tid' => $timeslot_id_arr[$i], 'action' => 'delete']))->toString();
                $mainLink    = t('@linkEdit | @linkRDelete', array('@linkEdit' => $edit_link, '@linkRDelete' => $delete_link));
                
                // print the data from table
                $rows[] = array('data' => array(
                    'id' => $timeslot_id_arr[$i],
                    'Start Date' => $start_time,
                    'End Date' => $end_time,
                    
                    //($isbooked == '') ? \Drupal::l('Edit', $edit, $options) : 'Slot is booked',
                    //($isbooked == '') ? \Drupal::l('Delete', $delete) : 'Slot is booked',
                    $mainLink,
                    
                ), 'class' => array($isbooked_class));

                $isbooked_class = '';
          } // inner loop
        }

        // display table in site
        $form['table'] = [
            '#type' => 'table',
            '#header' => $header_table,
            '#rows' => $rows,
            '#empty' => t('No Time Slots found'),
        ];

        // Finally add the pager.
        $form['pager'] = array(
            '#type' => 'pager'
        );
        return $form;
    }

    function converToTz($time="",$toTz='',$fromTz='')
    {   
        $date = new \DateTime($time, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $time = $date->format('Y-m-d H:i:s');
        return strtotime($time);
    }
}