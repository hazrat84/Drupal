<?php

/**
 * @file
 * Default simple view template to all the fields as a row.
 *
 * - $view: The view in use.
 * - $fields: an array of $field objects. Each one contains:
 *   - $field->content: The output of the field.
 *   - $field->raw: The raw data for the field, if it exists. This is NOT output safe.
 *   - $field->class: The safe class id to use.
 *   - $field->handler: The Views field handler object controlling this field. Do not use
 *     var_export to dump this object, as it can't handle the recursion.
 *   - $field->inline: Whether or not the field should be inline.
 *   - $field->inline_html: either div or span based on the above flag.
 *   - $field->wrapper_prefix: A complete wrapper containing the inline_html to use.
 *   - $field->wrapper_suffix: The closing tag for the wrapper.
 *   - $field->separator: an optional separator that may appear before a field.
 *   - $field->label: The wrap label text to use.
 *   - $field->label_html: The full HTML of the label to use including
 *     configured element type.
 * - $row: The raw result object from the query, with all data it fetched.
 *
 * @ingroup views_templates
 */
?>
<?php 
	$counter = 0;
	foreach ($fields as $id => $field): ?>
  <?php if (!empty($field->separator)): ?>
    <?php print $field->separator; ?>
  <?php endif; ?>
  <?php if($counter < 5){?>
  <?php if($counter == 1){?>
  	<div class="profile_left"> 
  <?php } ?>

  <?php if($counter == 2){?>
  	<div class="about_txt"> 
  		<?php echo t("About");?>
  	</div>
  <?php } ?>

  <?php print $field->wrapper_prefix; ?>
    <?php   print $field->label_html; 
            print $field->content; 
    	//var_dump($field);
    ?>
  <?php print $field->wrapper_suffix; 
  		if($counter == 4){?>
  			</div> 
  <?php } ?>
  <?php } // for wrapping the first three field in a div
        else{ 
        	if( $counter == 5){ echo "<div class='profile_right'>";}
        	print "<div class='content-right'>";
        	print $field->label_html; 
            print $field->content; 
            print "</div>";
   } // for wrapping the remaining fields in a div?>
<?php $counter += 1; endforeach; ?>
</div> 