<?php
/**
 * @file
 * Contains \Drupal\content_entity_example\Entity\ContentEntityExample.
 */

namespace Drupal\stripe_payment\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the ContentEntityExample entity.
 *
 * @ingroup stripe_payment
 *
 *
 * @ContentEntityType(
 *   id = "bookorder",
 *   label = @Translation("order entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data"   =   "Drupal\views\EntityViewsData",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "bookorder",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "charge_id" = "charge_id",
 *     "booking_id" = "booking_id",
 *     "student_id" = "student_id",
 *     "created" = "created",
 *   },
 *
 * )
 */

class StripeOrder extends ContentEntityBase {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the student_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    // Default author to current user.
    $values += array(
      'student_id' => \Drupal::currentUser()->id(),
    );
  }
  
  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behavior of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
      
      // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Order entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Order entity.'))
      ->setReadOnly(TRUE);
    
    $fields['charge_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Charge ID'))
      ->setDescription(t('Charge ID '))
      ->setSettings(array(
        'max_length' => 10,
        'text_processing' => 0,
      ))
      ->setDefaultValue(0);
    
    $fields['booking_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Booking ID'))
      ->setDescription(t('Booking ID '))
      ->setSettings(array(
        'max_length' => 10,
        'text_processing' => 0,
      ))
      ->setDefaultValue(0);
    
    $fields['student_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Student ID'))
      ->setDescription(t('The ID of the student.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'author',
        'weight' => -3,
      ))
      ->setDisplayConfigurable('view', TRUE);
    
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));
    
    return $fields;
    
  }

  
}
