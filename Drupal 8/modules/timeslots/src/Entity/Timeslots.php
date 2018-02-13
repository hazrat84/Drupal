<?php
/**
 * @file
 * Contains \Drupal\content_entity_example\Entity\ContentEntityExample.
 */

namespace Drupal\timeslots\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the ContentEntityExample entity.
 *
 * @ingroup timeslots
 *
 *
 * @ContentEntityType(
 *   id = "timeslots",
 *   label = @Translation("timeslots entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "list_builder" = "Drupal\timeslots\Entity\Controller\TimeslotsListBuilder",
 *     "form" = {
 *       "add" = "Drupal\timeslots\Form\TimeslotsForm",
 *       "edit" = "Drupal\timeslots\Form\TimeslotsForm",
 *       "delete" = "Drupal\timeslots\Form\TimeslotDeleteForm",
 *     },
 *     "access" = "Drupal\timeslots\TimeslotsAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "timeslots",
 *   admin_permission = "administer timeslots entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "startdate" = "startdate",
 *     "starttime" = "starttime",
 *     "endtime" = "endtime",
 *     "enddate" = "enddate",
 *     "tutor_id" = "tutor_id",
 *     "recurrent" = "recurrent",
 *     "isbooked" = "isbooked",
 *     "created" = "created",
 *     "changed" = "changed",
 *   },
 *   links = {
 *     "canonical" = "/timeslots/{timeslots}",
 *     "edit-form" = "/timeslots/{timeslots}/edit",
 *     "delete-form" = "/timeslots/{timeslots}/delete",
 *     "collection" = "/timeslots/list"
 *   },
 * )
 */
class Timeslots extends ContentEntityBase {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the tutor_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    // Default author to current user.
    $values += array(
      'tutor_id' => \Drupal::currentUser()->id(),
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

    $fields = parent::baseFieldDefinitions($entity_type);

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Timeslot entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Timeslot entity.'))
      ->setReadOnly(TRUE);

    
    // We set display options for the view as well as the form.
    // Users with correct privileges can change the view and edit.

      $fields['start_date'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Start Date'))
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        // Default comment body field has weight 20.
        'settings' => array(
            'placeholder' => t('2017-05-25')
          ),
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE);

      $fields['start_time'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Start Time'))
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        // Default comment body field has weight 20.
        'settings' => array(
            'placeholder' => t('15:00')
          ),
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['end_time'] = BaseFieldDefinition::create('string')
      ->setLabel(t('End Time'))
      ->setDescription(t('End Time of a day.'))
      ->setDisplayOptions('view', array(
            'label' => 'above',
            'type' => 'integer',
            'weight' => 0,
        ))
       ->setRequired(TRUE)
        ->setDisplayOptions('form', array(
            'type' => 'string_textfield',
            'settings' => array(
              'placeholder' => t('17:00')
            ),
            'weight' => 0,
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

    $fields['recurrent'] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('Select Repeat'))
        ->setDescription(t('Select Repeat type'))
        ->setSettings(array(
          'allowed_values' => array(
            '0' => 'No Repeat',
            '1' => 'Everyday',
            '2' => 'Business Day',
            '3' => 'Weekends',
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

    $fields['end_date'] = BaseFieldDefinition::create('string')
      ->setLabel(t('End Date'))
      ->setDescription(t('Ending Date.'))
      ->setDisplayOptions('view', array(
            'label' => 'above',
            'type' => 'string',
            'weight' => 0,
        ))
        ->setRequired(TRUE)
        ->setDisplayOptions('form', array(
            'type' => 'text_textfield',
            'settings' => array(
              'placeholder' => t('2017-05-30')
            ),
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

     $fields['isbooked'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('isbooked'))
      ->setDescription(t('Whether or not the node is highlighted.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
