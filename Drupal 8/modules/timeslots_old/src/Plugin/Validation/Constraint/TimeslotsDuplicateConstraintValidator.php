<?php

namespace Drupal\timeslots\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


/**
 * Validates the Timeslots duplication.
 */
class TimeslotsDuplicateConstraintValidator extends ConstraintValidator {

  /**
   * Validator 2.5 and upwards compatible execution context.
   *
   * @var \Symfony\Component\Validator\Context\ExecutionContextInterface
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    
    $user_id = \Drupal::currentUser()->id();
    $userSubmittedTimezoneString = drupal_get_user_timezone(); // get current user timezone

    $entity    = $items->getEntity();
    $start_date = $entity->startdate->value;
    $start_time = $entity->starttime->value;
    $end_date   = $entity->enddate->value;
    $end_time   = $entity->endtime->value;

    $start_dateTime_string = $this->converToTz($start_date.' '.$start_time, "UTC", $userSubmittedTimezoneString);
    $end_dateTime_string   = $this->converToTz($end_date.' '.$end_time, "UTC", $userSubmittedTimezoneString);

    $entity_type_id = $entity->getEntityTypeId();
    $starttime_bool = (bool) \Drupal::entityQuery($entity_type_id)
    ->condition('starttime', $start_dateTime_string, '<=')
    ->condition('endtime', $start_dateTime_string, '>=')
    ->condition('tutor_id', $user_id, '=')
    ->count()
    ->execute();

    if ($starttime_bool)
    {
      $this->context->addViolation($constraint->messageDuplicateTaken);
    }
  }

  private function isDuplicate($columName, $value) {
    // Here is where the check for a unique value would happen.
  }

  function converToTz($time="",$toTz='',$fromTz='')
  {   
      $date = new \DateTime($time, new \DateTimeZone($fromTz));
      $date->setTimezone(new \DateTimeZone($toTz));
      $time = $date->format('Y-m-d H:i:s');
      return strtotime($time);
  }

}
