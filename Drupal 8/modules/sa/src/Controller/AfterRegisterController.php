<?php

namespace Drupal\siteattention\Controller;

use Drupal\Core\Config\Config;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for saving instance name after Registration.
 */
class AfterRegisterController extends ControllerBase {


  /**
   * Update status, License Key, Instance Id, Instance name in Local DB after Registration
   *
   * @param string $iname
   *   The new four values returned after registration will be saved in local DB.
   *
   */
  public function saveAfterRegister(){
    $status = (isset($_GET['status']) ? $_GET['status'] : '');
    $license_key = (isset($_GET['key']) ? $_GET['key'] : '');
    $instance_arr = (isset($_GET['iid']) ? $_GET['iid'] : '');
  
    $iid     = $instance_arr['iid'];
    $iname   = $instance_arr['name'];
    $ilocked = $instance_arr['locked'];
    
    $config = \Drupal::service('config.factory')->getEditable('siteattention.settings');
    $config->set('siteattention.SA_KEY', $license_key);
    $config->set('siteattention.SA_INAME', $iname);
    $config->set('siteattention.SA_IID', $iid);
    $config->set('siteattention.SA_ILOCKED', $ilocked);
    $config->save();
    $response_array = array(
      'success'=> 1,
      'data' => 'Settings value saved'
    );
    return new JsonResponse($response_array);
  }

}
