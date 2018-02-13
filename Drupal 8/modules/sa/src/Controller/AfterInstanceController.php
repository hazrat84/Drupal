<?php

namespace Drupal\siteattention\Controller;

use Drupal\Core\Config\Config;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for saving instance name after when instance name is updated on the server.
 */
class AfterInstanceController extends ControllerBase {


  /**
   * Update Instance Name in Local DB after being updated on the server
   *
   * @param string $iname
   *   The new instance name to be updated in local DB.
   *
   */
  public function saveInstanceName(){
    $instance_name = (isset($_GET['instance_name']) ? $_GET['instance_name'] : '');
    $config = \Drupal::service('config.factory')->getEditable('siteattention.settings');
    $config->set('siteattention.SA_INAME', $instance_name)->save();
    $response_array = array(
      'success'=> 1,
      'data' => 'Instance Name Updated'
    );
    return new JsonResponse($response_array);
  }

}
