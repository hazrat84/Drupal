<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\timeslots\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Description of DeleteController
 *
 * @author hakh
 */
class MultipleDeleteController extends ControllerBase {
        
    public function multipleDeleteAction()
    {
        $response_array = array(
            'success'=> 0,
            'data' => 'Timeslots Deleted'
        );
        $timeslots_ids_arr = \Drupal::request()->request->get('id');
        
        $timeslots_count = count($timeslots_ids_arr);
        if(count($timeslots_ids_arr) > 0){
            entity_delete_multiple('timeslots', $timeslots_ids_arr);
            $response_array = array(
                'success'=> 1,
                'data' => 'Timeslots Deleted'
            );
        }
        
        return new JsonResponse($response_array);
    }
}
