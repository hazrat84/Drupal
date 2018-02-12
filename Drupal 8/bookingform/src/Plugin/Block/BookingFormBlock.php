<?php
/**
 * @file
 * Contains \Drupal\bookingform\Plugin\Block\BookingFormBlock.
 */

namespace Drupal\bookingform\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;

/**
 * Provides a 'booking form' block.
 *
 * @Block(
 *   id = "booking_form_block",
 *   admin_label = @Translation("Booking Overview"),
 *   category = @Translation("Custom Booking Form")
 * )
 */
class BookingFormBlock extends BlockBase {

    /**
    * {@inheritdoc}
    */
    public function build() {
        
  	$bookinginfo = \Drupal::entityTypeManager()
  			->getStorage('bookinginfo')
  			->create(array());
    
    	$form = \Drupal::service('entity.form_builder')->getForm($bookinginfo, 'booking_display');
        return $form;
    	
   }
}