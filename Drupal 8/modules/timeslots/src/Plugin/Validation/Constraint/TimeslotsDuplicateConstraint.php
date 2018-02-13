<?php

namespace Drupal\timeslots\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;
use Symfony\Component\Validator\Constraint;

/**
 * Supports validating timeslots duplication.
 *
 * @Constraint(
 *   id = "Timeslots_Duplication",
 * )
 */
class TimeslotsDuplicateConstraint extends CompositeConstraintBase {

  /**
   * Message shown when a duplcate Timeslot is entered.
   *
   * @var string
   */
  public $messageDuplicateTaken = 'The timeslot you try to add is already added.';

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return ['name', 'uid'];
  }

}
