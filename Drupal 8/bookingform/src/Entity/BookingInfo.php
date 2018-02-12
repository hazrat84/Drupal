<?php
/**
 * @file
 * Contains \Drupal\content_entity_example\Entity\ContentEntityExample.
 */

namespace Drupal\bookingform\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;


/**
 * Defines the ContentEntityExample entity.
 *
 * @ingroup bookingform
 *
 *
 * @ContentEntityType(
 *   id = "bookinginfo",
 *   label = @Translation("booking entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "bookinginfo",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "amount_session" = "amount_session",
 *     "available_language" = "available_language",
 *     "session_amount" = "session_amount",
 *     "commission_fee" = "commission_fee",
 *     "tutor_id" = "tutor_id",
 *     "student_id" = "student_id",
 *     "created" = "created",
 *   },
 *
 * )
 */
class BookingInfo extends ContentEntityBase {

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
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    //$fields = parent::baseFieldDefinitions($entity_type);

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Booking entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Booking entity.'))
      ->setReadOnly(TRUE);

    $fields['amount_session'] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('Set amount of sessions'))
        ->setDescription(t('Number of Sessions'))
        ->setSettings(array(
          'allowed_values' => array(
            '1' => '1',
            '2' => '2',
            '3' => '3',
          ),
        ))
        ->setRequired(TRUE)
        ->setDisplayOptions('view', array(
            'label' => 'above',
            'type' => 'string',
            'weight' => 0,
        ))
        ->setDisplayOptions('form', array(
            'type' => 'options_select',
            'weight' => 0,
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

    $age = array("da"=>"Danish", "en"=>"English");

    $fields['available_language'] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('Available Language'))
        ->setDescription(t('Number of Sessions'))
        ->setSetting('allowed_values', $age)
        ->setRequired(TRUE)
        ->setDisplayOptions('view', array(
            'label' => 'above',
            'type' => 'string',
            'weight' => 0,
        ))
        ->setDisplayOptions('form', array(
            'type' => 'options_select',
            'weight' => 0,
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);


      $fields['session_amount'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Session'))
        ->setDisplayOptions('view', array(
            'label' => 'above',
            'type' => 'integer',
            'weight' => 0,
        ))
        ->setDisplayOptions('form', array(
            'type' => 'text_textfield',
            'weight' => 0,
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

    $fields['commission_fee'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Commission Fee'))
        ->setDisplayOptions('view', array(
            'label' => 'above',
            'type' => 'integer',
            'weight' => 0,
        ))
        ->setDisplayOptions('form', array(
            'type' => 'text_textfield',
            'weight' => 0,
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

    // Owner field of the contact.
    // Entity reference field, holds the reference to the user object.
    // The view shows the user name field of the user.
    // The form presents a auto complete field for the user name.
    $fields['tutor_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tutor ID'))
      ->setDescription(t('The ID of the tutor.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'author',
        'weight' => -3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

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
