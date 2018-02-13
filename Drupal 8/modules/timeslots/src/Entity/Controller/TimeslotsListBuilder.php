<?php

/**
 * @file
 * Contains \Drupal\timeslots\Entity\Controller\TermListBuilder.
 */

namespace Drupal\timeslots\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for timeslots entity.
 *
 * @ingroup timeslots
 */
class TimeslotsListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;


  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new TimeslotsListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type term.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }


  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the dictionary_term list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('Id');
    $header['starttime'] = $this->t('Start Time');
    $header['endtime'] = $this->t('End Time');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\dictionary\Entity\Timeslots */
    $userSubmittedTimezoneString = drupal_get_user_timezone();
    $row['Id'] = $entity->id();

    $starting_time = date('Y-m-d H:i:s', $entity->start_time->value);
    $ending_time = date('Y-m-d H:i:s', $entity->end_time->value);

    $row['starttime'] = date('Y-m-d H:i:s a', $this->converToTz($starting_time, $userSubmittedTimezoneString, "UTC"));
    $row['endtime'] = date('Y-m-d H:i:s a', $this->converToTz($ending_time, $userSubmittedTimezoneString, "UTC"));
    
    return $row + parent::buildRow($entity);
  }

  function converToTz($time="",$toTz='',$fromTz='')
  {   
      $date = new \DateTime($time, new \DateTimeZone($fromTz));
      $date->setTimezone(new \DateTimeZone($toTz));
      $time = $date->format('Y-m-d H:i:s');
      return strtotime($time);
  }

}
