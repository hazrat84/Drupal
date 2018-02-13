<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\timeslots\Controller;
use Drupal\Core\Controller\ControllerBase;
/**
 * Description of DeleteController
 *
 * @author hakh
 */
class DeleteController extends ControllerBase {
    //put your code here
    public function DeleteMultiple() {
        $output = array(
          '#markup' => 'This is a multiple delete page',
        );
        return $output;
    }
    
}
