<?php

function responsive_bartik_preprocess_html(&$variables)
{
  // Add variables for path to theme.
  $variables['base_path'] = base_path();
  $variables['path_to_resbartik'] = drupal_get_path('theme', 'responsive_bartik');

  // Add local.css stylesheet
  if (file_exists(drupal_get_path('theme', 'responsive_bartik') . '/css/local.css')) {
    drupal_add_css(drupal_get_path('theme', 'responsive_bartik') . '/css/local.css',
      array('group' => CSS_THEME, 'every_page' => TRUE));
  }

  // Add body classes if certain regions have content.
  if (!empty($variables['page']['featured'])) {
    $variables['classes_array'][] = 'featured';
  }

  if (!empty($variables['page']['triptych_first'])
    || !empty($variables['page']['triptych_middle'])
    || !empty($variables['page']['triptych_last'])
  ) {
    $variables['classes_array'][] = 'triptych';
  }

  if (!empty($variables['page']['footer_firstcolumn'])
    || !empty($variables['page']['footer_secondcolumn'])
    || !empty($variables['page']['footer_thirdcolumn'])
    || !empty($variables['page']['footer_fourthcolumn'])
  ) {
    $variables['classes_array'][] = 'footer-columns';
  }
}

/**
 * Override or insert variables into the page template for HTML output.
 */
function responsive_bartik_process_html(&$variables)
{
  // Hook into color.module.
  if (module_exists('color')) {
    _color_html_alter($variables);
  }
}

/**
 * Override or insert variables into the page template.
 */
function responsive_bartik_process_page(&$variables)
{
  // Hook into color.module.
  if (module_exists('color')) {
    _color_page_alter($variables);
  }
  // Always print the site name and slogan, but if they are toggled off, we'll
  // just hide them visually.
  $variables['hide_site_name'] = theme_get_setting('toggle_name') ? FALSE : TRUE;
  $variables['hide_site_slogan'] = theme_get_setting('toggle_slogan') ? FALSE : TRUE;
  if ($variables['hide_site_name']) {
    // If toggle_name is FALSE, the site_name will be empty, so we rebuild it.
    $variables['site_name'] = filter_xss_admin(variable_get('site_name', 'Drupal'));
  }
  if ($variables['hide_site_slogan']) {
    // If toggle_site_slogan is FALSE, the site_slogan will be empty, so we rebuild it.
    $variables['site_slogan'] = filter_xss_admin(variable_get('site_slogan', ''));
  }
  // Since the title and the shortcut link are both block level elements,
  // positioning them next to each other is much simpler with a wrapper div.
  if (!empty($variables['title_suffix']['add_or_remove_shortcut']) && $variables['title']) {
    // Add a wrapper div using the title_prefix and title_suffix render elements.
    $variables['title_prefix']['shortcut_wrapper'] = array(
      '#markup' => '<div class="shortcut-wrapper clearfix">',
      '#weight' => 100,
    );
    $variables['title_suffix']['shortcut_wrapper'] = array(
      '#markup' => '</div>',
      '#weight' => -99,
    );
    // Make sure the shortcut link is the first item in title_suffix.
    $variables['title_suffix']['add_or_remove_shortcut']['#weight'] = -100;
  }
  if(arg(0) == 'user' && (arg(1) == 'register' || arg(1) == 'login' || arg(1) == 'password')){
    unset($variables['tabs']['#primary'][0]['#theme']);
    unset($variables['tabs']['#primary'][1]['#theme']);
    unset($variables['tabs']['#primary'][2]['#theme']);
    
    if(arg(1) == 'register'){
        unset($variables['tabs']['#primary'][3]['#theme']);
    }
  }
  
}

/**
 * Implements hook_preprocess_maintenance_page().
 */
function responsive_bartik_preprocess_maintenance_page(&$variables)
{
  // By default, site_name is set to Drupal if no db connection is available
  // or during site installation. Setting site_name to an empty string makes
  // the site and update pages look cleaner.
  // @see template_preprocess_maintenance_page
  if (!$variables['db_is_active']) {
    $variables['site_name'] = '';
  }
  drupal_add_css(drupal_get_path('theme', 'responsive_bartik') . '/css/maintenance-page.css');
}

/**
 * Override or insert variables into the maintenance page template.
 */
function responsive_bartik_process_maintenance_page(&$variables)
{
  // Always print the site name and slogan, but if they are toggled off, we'll
  // just hide them visually.
  $variables['hide_site_name'] = theme_get_setting('toggle_name') ? FALSE : TRUE;
  $variables['hide_site_slogan'] = theme_get_setting('toggle_slogan') ? FALSE : TRUE;
  if ($variables['hide_site_name']) {
    // If toggle_name is FALSE, the site_name will be empty, so we rebuild it.
    $variables['site_name'] = filter_xss_admin(variable_get('site_name', 'Drupal'));
  }
  if ($variables['hide_site_slogan']) {
    // If toggle_site_slogan is FALSE, the site_slogan will be empty, so we rebuild it.
    $variables['site_slogan'] = filter_xss_admin(variable_get('site_slogan', ''));
  }
}

/**
 * Override or insert variables into the node template.
 */
function responsive_bartik_preprocess_node(&$variables)
{
  if ($variables['view_mode'] == 'full' && node_is_page($variables['node'])) {
    $variables['classes_array'][] = 'node-full';
  }
}

/**
 * Override or insert variables into the block template.
 */
function responsive_bartik_preprocess_block(&$variables)
{
  // In the header region visually hide block titles.
  if ($variables['block']->region == 'header') {
    $variables['title_attributes_array']['class'][] = 'element-invisible';
  }
}

/**
 * Implements theme_menu_tree().
 */
function responsive_bartik_menu_tree($variables)
{
  return '<ul class="menu clearfix">' . $variables['tree'] . '</ul>';
}

/**
 * Implements theme_field__field_type().
 */
function responsive_bartik_field__taxonomy_term_reference($variables)
{
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<h3 class="field-label">' . $variables['label'] . ': </h3>';
  }

  // Render the items.
  $output .= ($variables['element']['#label_display'] == 'inline') ? '<ul class="links inline">' : '<ul class="links">';
  foreach ($variables['items'] as $delta => $item) {
    $output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</li>';
  }
  $output .= '</ul>';

  // Render the top-level DIV.
  $output = '<div class="' . $variables['classes'] . (!in_array('clearfix', $variables['classes_array']) ? ' clearfix' : '') . '"' . $variables['attributes'] . '>' . $output . '</div>';

  return $output;
}

function responsive_bartik_preprocess_page(&$vars) {
  global $user;
  drupal_add_js(path_to_theme().'/js/red_menu.js', array('scope'=>'footer'));
  
  if(user_is_logged_in()){
    
    if(in_array("administrator", $user->roles)){
        global $language;
        $find_submission = '';
        if($language->language == 'ar'){
            
        }
        
       /* $result = db_select('node', 'n')
        ->fields('n')
        ->condition('type', 'company','=')
        ->condition('uid', $user->uid,'=')
        ->execute()
        ->fetchAssoc();

        $nid = $result['nid'];*/
        //$vars['secondary_menu']['menu-479']['href'] = 'node/'.$nid.'/edit';
        
        /*$vars['secondary_menu']['menu-414'] = array(
          'href' => 'find-your-submission',
          'title' => 'Find Your Submission',
        );
        
        $vars['secondary_menu']['menu-418'] = array(
          'href' => 'node/add/exhibition',
          'weight' => -50,
          'title' => 'Add Exhibition',
        );*/
        
        $vars['secondary_menu']['menu-15'] = array(
          'href' => 'user/logout',
          'title' => t('Log out'),
        );
      }
  }
  
  if(arg(0) == 'taxonomy') {
    array_push($vars['theme_hook_suggestions'], 'page__taxonomy');
  }
  
  // Do we have a node?
  if (isset($vars['node'])) {

    // Ref suggestions cuz it's stupid long.
    $suggests = &$vars['theme_hook_suggestions'];

    // Get path arguments.
    $args = arg();
    // Remove first argument of "node".
    unset($args[0]);

    // Set type.
    $type = "page__type_{$vars['node']->type}";

    // Bring it all together.
    $suggests = array_merge(
      $suggests,
      array($type),
      theme_get_suggestions($args, $type)
    );

    // if the url is: 'http://domain.com/node/123/edit'
    // and node type is 'blog'..
    // 
    // This will be the suggestions:
    //
    // - page__node
    // - page__node__%
    // - page__node__123
    // - page__node__edit
    // - page__type_blog
    // - page__type_blog__%
    // - page__type_blog__123
    // - page__type_blog__edit
    // 
    // Which connects to these templates:
    //
    // - page--node.tpl.php
    // - page--node--%.tpl.php
    // - page--node--123.tpl.php
    // - page--node--edit.tpl.php
    // - page--type-blog.tpl.php          << this is what you want.
    // - page--type-blog--%.tpl.php
    // - page--type-blog--123.tpl.php
    // - page--type-blog--edit.tpl.php
    // 
    // Latter items take precedence.
  }
  
}


function responsive_bartik_theme(&$existing, $type, $theme, $path){
  $hooks = array();
   // Make user-register.tpl.php available
    $hooks['user_register_form'] = array (
        'render element' => 'form',
        'path' => drupal_get_path('theme','responsive_bartik'),
        'template' => 'templates/user-register',
        'preprocess functions' => array('responsive_bartik_preprocess_user_register_form'),
    );
  
    $hooks['find_your_partner_entityform_edit_form'] = array(
      //'variables' => array('elements' => null),
        'render element' => 'form',
        'path' => drupal_get_path('theme','responsive_bartik'),
        'template' => 'templates/entity-form-wrapper',
        'preprocess functions' => array('responsive_bartik_preprocess_find_your_partner_entityform_edit_form'),
    );
  
  return $hooks;
}

function responsive_bartik_preprocess_user_register_form(&$variables) {
  $args = func_get_args();
  array_shift($args);
  $form_state['build_info']['args'] = $args; 
  
    //$variables['form'] = drupal_build_form('user_register', user_register_form(array()));
  $variables['form'] = drupal_build_form('user_register_form', $form_state['build_info']['args']);
}

function responsive_bartik_preprocess_find_your_partner_entityform_edit_form(&$variables) {
  $variables['field_please'] = drupal_render($variables['form']['field_please_tell_us_about_your_']);
}

/*function responsive_bartik_preprocess_user_register_form(&$vars) {
  $args = func_get_args();
  array_shift($args);
  $form_state['build_info']['args'] = $args; 
  $vars['form'] = drupal_build_form('user_register_form', $form_state['build_info']['args']);
}*/
function responsive_bartik_form_alter(&$form, &$form_state, $form_id) {
    if($form['#id'] == 'user-register-form'){
        $params = drupal_get_query_parameters();
        $options_arr = array();
        $sub_arr = array();
        $options_arr = $form['ms_membership']['sku']['#options'];
        foreach($options_arr as $key => $value) {
            $sub_arr = explode('-', $value);
            $options_arr[$key] = $sub_arr[0];
        }
        
        $form['ms_membership']['sku']['#options'] = $options_arr;
        if(count($params) > 0){
            //$form['ms_membership']['sku']['#default_value'] = $params['membership']; 
        }
    }
}
