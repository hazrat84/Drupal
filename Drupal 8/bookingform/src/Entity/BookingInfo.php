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
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

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
 *     "views_data"   =   "Drupal\views\EntityViewsData",
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
   * in the GUI. The behavior of the widgets used can be determined here.
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

    $fields['number_of_sessions'] = BaseFieldDefinition::create('list_string') // number of sessions
        ->setLabel(t('Set amount of sessions'))
        
        ->setSettings(array(
          'allowed_values' => array(
            '1' => '1',
            '2' => '2',
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
    
    
    $userLanguages = array();
    
    $fields['available_language'] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('Available Language'))
        ->setDescription(t(''))
        ->setSetting('allowed_values', $userLanguages)
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


      $fields['session_amount'] = BaseFieldDefinition::create('string') // Session total Amount = quantity X price
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
    
    $fields['session_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Session ID'))
      ->setDescription(t('Session ID '))
      ->setSettings(array(
        'max_length' => 10,
        'text_processing' => 0,
      ))
      ->setDefaultValue(0);
    
    $fields['timeslot_id'] = BaseFieldDefinition::create('integer') // Timeslot id
      ->setLabel(t('Timeslot ID'))
      ->setDescription(t('Timeslot ID '))
      ->setSettings(array(
        'max_length' => 10,
        'text_processing' => 0,
      ))
      ->setDefaultValue(0);
    
    $fields['is_accepted'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('isaccepted'))
      ->setDescription(t('will show that this booking is accepted or not'));
    
    $fields['is_canceled'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('iscanceled'))
      ->setDescription(t('will show that this booking is canceled or not'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    return $fields;
  }
  
  /**
   * Default value callback for 'nid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  
  public static function getCurrentNodeId(){
      $node = \Drupal::routeMatch()->getParameter('node');
      return $node->id();
  }

}
