<?php

/**
 * Implementation of hook_theme().
 */
function redinmobiliaria_theme() {
  $items = array();

  // Consolidate a variety of theme functions under a single template type.
  $items['block'] = array(
    'arguments' => array('block' => NULL),
    'template' => 'object',
    'path' => drupal_get_path('theme', 'redinmobiliaria') .'/templates',
  );
  $items['box'] = array(
    'arguments' => array('title' => NULL, 'content' => NULL, 'region' => 'main'),
    'template' => 'object',
    'path' => drupal_get_path('theme', 'redinmobiliaria') .'/templates',
  );
  $items['comment'] = array(
    'arguments' => array('comment' => NULL, 'node' => NULL, 'links' => array()),
    'template' => 'object',
    'path' => drupal_get_path('theme', 'redinmobiliaria') .'/templates',
  );
  $items['node'] = array(
    'arguments' => array('node' => NULL, 'teaser' => FALSE, 'page' => FALSE),
    'template' => 'node',
    'path' => drupal_get_path('theme', 'redinmobiliaria') .'/templates',
  );
  $items['fieldset'] = array(
    'arguments' => array('element' => array()),
    'template' => 'fieldset',
    'path' => drupal_get_path('theme', 'redinmobiliaria') .'/templates',
  );

  // Use a template for form elements
  $items['form_element'] = array(
    'arguments' => array('element' => array(), 'value' => NULL),
    'template' => 'form-item',
    'path' => drupal_get_path('theme', 'redinmobiliaria') .'/templates',
  );

  // Print friendly page headers.
  $items['print_header'] = array(
    'arguments' => array(),
    'template' => 'print-header',
    'path' => drupal_get_path('theme', 'redinmobiliaria') .'/templates',
  );

  // Split out pager list into separate theme function.
  $items['pager_list'] = array('arguments' => array(
    'tags' => array(),
    'limit' => 10,
    'element' => 0,
    'parameters' => array(),
    'quantity' => 9,
  ));
  
  /////////////////
  // CUSTOM CODE //
  /////////////////  
  
  // Split out pager list into separate theme function.
  $items['simplenews_block_form_39'] = array('arguments' => array(
    'form' => null,
  ));
  
  // To customize the search form
  $items['search_form'] = array('arguments' => array(
    'form' => null,
  ));

  // Used to theme the user picture display of a user
  $items['user_picture'] = array('arguments' => array(
    'account' => NULL,
    'imagecache_preset_name' => '',
    'attributes' => NULL,
  ));

  // Used to theme the user picture display of a user
  $items['menu_item_link'] = array('arguments' => array(
    'link' => NULL,
  ));

  // Used to theme the list of participants displayed in the messaged mailbox
  $items['privatemsg_views_participants'] = array('arguments' => array(
    'message' => NULL,
  ));
  
  // Used to theme the pager displayed in the list of properties per project
  $items['views_slideshow_singleframe_pager'] = array('arguments' => array(
    'vss_id' => '', 
    'view' => NULL, 
    'options' => array(),
  ));
  

  return $items;
}

/**
 * DEPRECATED. CSS exclusion is better handled with positive (yet omitted)
 * entries in your .info file.
 *
 * Strips CSS files from a Drupal CSS array whose filenames start with
 * prefixes provided in the $match argument.
 */
function redinmobiliaria_css_stripped($match = array('modules/*'), $exceptions = NULL) {
  // Set default exceptions
  if (!is_array($exceptions)) {
    $exceptions = array(
      'modules/color/color.css',
      'modules/locale/locale.css',
      'modules/system/system.css',
      'modules/update/update.css',
      'modules/openid/openid.css',
      'modules/acquia/*',
    );
  }
  $css = drupal_add_css();
  $match = implode("\n", $match);
  $exceptions = implode("\n", $exceptions);
  foreach (array_keys($css['all']['module']) as $filename) {
    if (drupal_match_path($filename, $match) && !drupal_match_path($filename, $exceptions)) {
      unset($css['all']['module'][$filename]);
    }
  }

  // This servers to move the "all" CSS key to the front of the stack.
  // Mainly useful because modules register their CSS as 'all', while
  // RedInmobiliaria has a more media handling.
  ksort($css);
  return $css;
}

/**
 * Print all child pages of a book.
 */
function redinmobiliaria_print_book_children($node) {
  // We use a semaphore here since this function calls and is called by the
  // node_view() stack so that it may be called multiple times for a single book tree.
  static $semaphore;

  if (module_exists('book') && book_type_is_allowed($node->type)) {
    if (isset($_GET['print']) && isset($_GET['book_recurse']) && !isset($semaphore)) {
      $semaphore = TRUE;

      $child_pages = '';
      $zomglimit = 0;
      $tree = array_shift(book_menu_subtree_data($node->book));
      if (!empty($tree['below'])) {
        foreach ($tree['below'] as $link) {
          _redinmobiliaria_print_book_children($link, $child_pages, $zomglimit);
        }
      }

      unset($semaphore);

      return $child_pages;
    }
  }

  return '';
}

/**
 * Book printing recursion.
 */
function _redinmobiliaria_print_book_children($link, &$content, &$zomglimit, $limit = 500) {
  if ($zomglimit < $limit) {
    $zomglimit++;
    if (!empty($link['link']['nid'])) {
      $node = node_load($link['link']['nid']);
      if ($node) {
        $content .= node_view($node);
      }
      if (!empty($link['below'])) {
        foreach ($link['below'] as $child) {
          _redinmobiliaria_print_book_children($child, $content);
        }
      }
    }
  }
}

/**
 * CUSTOM CODE. Function to get the user picture as a link, correctly modified using 
 * imagecache presets, and with fallbacks in case the user has not image
 *
 * @param object $account User object of the user to extract picture info
 * @param string $imagecache_preset_name Name of the imagecache preset to used to
 * format the user picture
 * @param array $attributes Associative array of attributes to be placed in the 
 * generated a tag
 * @return string The html generated for the user picture
 */
function redinmobiliaria_user_picture($account = NULL, $imagecache_preset_name = 'user_picture', $attributes = NULL){
  $output = '';
  
  if($account && !is_object($account)){
    return $output;
  }
  
  // Load the account of the user to be treated if not received anything at all
  if(!$account){
    global $user;
    
    $account = $user;
  }
  
  // Use the imagecache preset only if the module is installed and enabled, and the
  // imagecache preset name exists
  if(module_exists('imagecache')) {
    $preset = imagecache_preset_by_name($imagecache_preset_name);
    
    if(empty($preset)){
      $imagecache_preset_name = '';
    }
  }  
  
  // Load the user picture's path, or a default image if one could not be set
  $pic = $account->picture;
  
  if(!$pic){
    $pic = drupal_get_path('theme', 'redinmobiliaria') . '/images/anonymous-user.jpg';
    
    // TODO: add code to load the user picture male or female if the user's gender
    // could be determined
  }

  // Use a correct theme funcion for the user pic
  if($imagecache_preset_name){
    $pic = theme('imagecache', $imagecache_preset_name, $pic, $account->name, $account->name);
  } else {
    $pic = theme('image', $pic, $account->name, $account->name);
  }
    
  // Validate if the user can access user profiles. Idea taken from theme_username
  if (user_access('access user profiles')) {    
    // Create the link and stuff for the output
    $options = array(
      'html' => true,
      'attributes' => $attributes,
    );
    $options['attributes']['class'] = trim('user-image ' . $options['attributes']['class']);
    
    $output = l($pic, 'user/' . $account->uid, $options);
  } else {
    $output = $pic;
  }
  
  return '<div class="user-picture">' . $output . '</div>';
}

/**
 * Preprocess functions ===============================================
 */

/**
 * Implementation of preprocess_page().
 */
function redinmobiliaria_preprocess_page(&$vars) {
  $attr = array();
  $attr['class'] = $vars['body_classes'];
  $attr['class'] .= ' redinmobiliaria'; // Add the redinmobiliaria class so that we can avoid using the 'body' selector

  // Replace screen/all stylesheets with print
  // We want a minimal print representation here for full control.
  if (isset($_GET['print'])) {
    $css = drupal_add_css();
    unset($css['all']);
    unset($css['screen']);
    $css['all'] = $css['print'];
    $vars['styles'] = drupal_get_css($css);

    // Add print header
    $vars['print_header'] = theme('print_header');

    // Replace all body classes
    $attr['class'] = 'print';

    // Use print template
    $vars['template_file'] = 'print-page';

    // Suppress devel output
    $GLOBALS['devel_shutdown'] = FALSE;
  }

  // Split primary and secondary local tasks
  $vars['tabs'] = theme('menu_local_tasks', 'primary');
  $vars['tabs2'] = theme('menu_local_tasks', 'secondary');

  // Link site name to frontpage
  $vars['site_name'] = l($vars['site_name'], '<front>');

  // Don't render the attributes yet so subthemes can alter them
  $vars['attr'] = $attr;

  // Skip navigation links (508).
  $vars['skipnav'] = "<a id='skipnav' href='#content'>" . t('Skip navigation') ."</a>";
  
  /////////////////
  // CUSTOM CODE //
  /////////////////
  
  // Remove the breadcrumb
  unset($vars['breadcrumb']);
  
  // Change the page title, in case a query value has been detected
  if($title = $_GET['page-title']){
    $vars['title'] = t($title);
  }
  
  // Call additional preprocess functions using template suggestions as base
  foreach ($vars['template_files'] as $suggestion) {
    $function = 'redinmobiliaria_preprocess_' . strtr($suggestion, '-', '_');
    if (function_exists($function)) {
      $function(&$vars);
    }
  }
}

/**
 * Implementation of preprocess_block().
 */
function redinmobiliaria_preprocess_block(&$vars) {
  // Hide blocks with no content.
  $vars['hide'] = empty($vars['block']->content);

  $attr = array();
  $attr['id'] = "block-{$vars['block']->module}-{$vars['block']->delta}";
  $attr['class'] = "block block-{$vars['block']->module}";
  $vars['attr'] = $attr;

  $vars['hook'] = 'block';
  $vars['title'] = !empty($vars['block']->subject) ? $vars['block']->subject : '';
  $vars['content'] = $vars['block']->content;
  $vars['is_prose'] = ($vars['block']->module == 'block') ? TRUE : FALSE;
  
  /////////////////
  // CUSTOM CODE //
  /////////////////
    
  // Call to specific preproccess function, by creator module (if exists)
  $function = __FUNCTION__ . '_' . strtr($vars['block']->module, '-', '_');
  if (function_exists($function)) {
    $function(&$vars);
  }  
}

/**
 * CUSTOM CODE. Specific function to do the preprocessing of blocks from redinmob_search module
 */
function redinmobiliaria_preprocess_block_redinmob_search(&$vars) {
  // The special process is not done with some blocks of the module in mention
  switch ($vars['block']->delta) {
    case 'search_selective':
    case 'search_by_code':
      return;
  }
  
  static $elements;
  
  // Save the content of te block to be displayed
  $content_aux = $vars['content'];
  $vars['content'] = '';
  
//  // Find if there is an 'apachesolr-unclick' class item. Needed to set the
//  // collapsible status of fieldset
//  $collapse = (strpos($content_aux, 'apachesolr-unclick') === FALSE);
  
  // Flag that indicates if an adittional class needs to be added
  $add_first = false;
  
  if(!isset($elements['#title_processed'])){
    $elements = array(
      '#value' => '<div class="redinmob-search-filters-title">' . t('Filter Results') . '</div>',
    );
    
    $vars['content'] .= drupal_render($elements);
    $elements['#title_processed'] = true;
    $add_first = true;
  }
  
  // For each faceted search block, a collpasible fieldset is added
  $elements[] = array(
    '#type' => 'fieldset',
//    '#collapsible' => true,
//    '#collapsed' => $collapse,
    '#value' => $content_aux,
    '#title' => $vars['title'],
  );
  
  $vars['content'] .= drupal_render($elements);
  
  // Set another HTML code to help with the theming
  $vars['pre_object'] .= '<div class="redinmob-search-facet-block' . ($add_first ? ' first' : '') . '">';
  $vars['post_object'] = $vars['post_object'] . '</div>';
  
  // Unset other unnecessary elements
  unset($vars['title']);
  
  // Remove rendered elements in the $elements array, to prevent their re-renderization
  array_pop($elements);
  unset($elements['#value']);
}

/**
 * Implementation of preprocess_box().
 */
function redinmobiliaria_preprocess_box(&$vars) {
  $attr = array();
  $attr['class'] = "box";
  $vars['attr'] = $attr;
  $vars['hook'] = 'box';
}

/**
 * Implementation of preprocess_node().
 */
function redinmobiliaria_preprocess_node(&$vars) {
  $attr = array();
  $attr['id'] = "node-{$vars['node']->nid}";
  $attr['class'] = "node node-{$vars['node']->type}";
  $attr['class'] .= $vars['node']->sticky ? ' sticky' : '';
  $vars['attr'] = $attr;

  $vars['hook'] = 'node';
  $vars['is_prose'] = TRUE;

  // Add print customizations
  if (isset($_GET['print'])) {
    $vars['post_object'] = redinmobiliaria_print_book_children($vars['node']);
  }
}

/**
 * Implementation of preprocess_comment().
 */
function redinmobiliaria_preprocess_comment(&$vars) {
  $attr = array();
  $attr['id'] = "comment-{$vars['comment']->cid}";
  $attr['class'] = "comment {$vars['status']}";
  $vars['attr'] = $attr;

  $vars['hook'] = 'comment';
  $vars['is_prose'] = TRUE;
}

/**
 * Implementation of preprocess_fieldset().
 */
function redinmobiliaria_preprocess_fieldset(&$vars) {
  $element = $vars['element'];

  $attr = isset($element['#attributes']) ? $element['#attributes'] : array();
  $attr['class'] = !empty($attr['class']) ? $attr['class'] : '';
  $attr['class'] .= ' fieldset';
  $attr['class'] .= !empty($element['#title']) ? ' titled' : '';
  $attr['class'] .= !empty($element['#collapsible']) ? ' collapsible' : '';
  $attr['class'] .= !empty($element['#collapsible']) && !empty($element['#collapsed']) ? ' collapsed' : '';
  $vars['attr'] = $attr;

  $description = !empty($element['#description']) ? "<div class='description'>{$element['#description']}</div>" : '';
  $children = !empty($element['#children']) ? $element['#children'] : '';
  $value = !empty($element['#value']) ? $element['#value'] : '';
  $vars['content'] = $description . $children . $value;
  $vars['title'] = !empty($element['#title']) ? $element['#title'] : '';
  if (!empty($element['#collapsible'])) {
    $vars['title'] = l(filter_xss_admin($vars['title']), $_GET['q'], array('fragment' => 'fieldset', 'html' => TRUE));
  }
  $vars['hook'] = 'fieldset';
}

/**
 * Implementation of preprocess_form_element().
 * Take a more sensitive/delineative approach toward theming form elements.
 */
function redinmobiliaria_preprocess_form_element(&$vars) {
  $element = $vars['element'];

  // Main item attributes.
  $vars['attr'] = array();
  $vars['attr']['class'] = 'form-item';
  $vars['attr']['id'] = !empty($element['#id']) ? "{$element['#id']}-wrapper" : NULL;
  if (!empty($element['#type']) && in_array($element['#type'], array('checkbox', 'radio'))) {
    $vars['attr']['class'] .= ' form-option';
  }
  $vars['description'] = isset($element['#description']) ? $element['#description'] : '';

  // Generate label markup
  if (!empty($element['#title'])) {
    $t = get_t();
    $required_title = $t('This field is required.');
    $required = !empty($element['#required']) ? "<span class='form-required' title='{$required_title}'>*</span>" : '';
    $vars['label_title'] = $t('!title: !required', array('!title' => filter_xss_admin($element['#title']), '!required' => $required));
    $vars['label_attr'] = array();
    if (!empty($element['#id'])) {
      $vars['label_attr']['for'] = $element['#id'];
    }

    // Indicate that this form item is labeled
    $vars['attr']['class'] .= ' form-item-labeled';
  }
}

/**
 * Preprocessor for theme_print_header().
 */
function redinmobiliaria_preprocess_print_header(&$vars) {
  $vars = array(
    'base_path' => base_path(),
    'theme_path' => base_path() .'/'. path_to_theme(),
    'site_name' => variable_get('site_name', 'Drupal'),
  );
  $count ++;
}

/**
 * CUSTOM CODE. Implementation of preprocess_search_result().
 */
function redinmobiliaria_preprocess_search_result(&$vars) {
  // Get the node that comes with the result data
  $node = $vars['result']['node']->node;
          
  if(!$node){
    return;
  }

  // Load the type of the node to be handled
  $type = $node->type;
  
  // Array of field to extract info from
  $field_names = array(
    'field_precio',
    'field_dormitorios',
    'field_banios',
    'field_lineas_telefonicas',
    'field_tamanio_propiedad',
    'field_garajes',
  );
  
  // Loop through the fields to extract info from
  foreach ($field_names as $field_name) {
    // Work if the field ha data
    $value = $node->{$field_name}[0]['value'];

    if(!$value){
      continue;
    }
    
    // Get info about the field
    $field = content_fields($field_name, $type);
    
    // Customized data extraction per field
    switch ($field_name) {
      case 'field_precio':
        $vars['data'] = '<div class="precio">' . theme('box', $field['widget']['label'], $value) . '</div>';

        break;
      
      case 'field_tamanio_propiedad':
        $items[] = theme('box', $field['widget']['label'], $value . ' ' . units_get_symbol($node->{$field_name}[0]['unit']));

        break;

      default:
        $items[] = theme('box', $field['widget']['label'], $value);
        
        break;
    }
  }
  
  // Load the items extracted
  if($items){
    $vars['data'] .= theme('item_list', $items);
  }
  
  // Load the photo
  $fotos = $node->field_foto;

  if($fotos){
    $pic = theme('imagecache', 'foto_in_search', $fotos[0]['filepath']);
    
    $vars['picture'] .= l($pic, 'node/' . $node->nid, array('html' => true));;
    $vars['picture'] .= '<div class="photo-text">' . format_plural(count($fotos), '1 photo', '@count photos') . '</div>';
  }
  
  // Load the user data
  $vars['user_data'] = redinmobiliaria_user_picture(user_load($node->uid), 'user_foto_in_search');
}

/**
 * CUSTOM CODE. Implementation of preprocess_user_profile().
 */
function redinmobiliaria_preprocess_user_profile(&$vars) {
  // Load the user picture
  $vars['user_profile'] = theme('user_picture', $vars['account']);

  // Load the user name
  $user_name = theme('username', $vars['account']);
  
  // Load the user data
  global $user;
  
  $params = array(
    'type' => 'contacto_publicacion',
    'uid' => $vars['account']->uid,
  );
  
  if($profile = node_load($params)){
    $fields = array(
      'field_telefono',
      'field_celular',
      'field_pagina_web',
    );
    
    foreach ($fields as $field_name) {
      switch ($field_name) {
        case 'field_pagina_web':
          $value = $profile->{$field_name}[0]['url'];
          $value = l($value, $value);

          break;

        default:
          $value = $profile->{$field_name}[0]['value'];
          
          break;
      }
      
      if(!$value){
        continue;
      }
      
      $field = content_fields($field_name, $params['type']);
      
      $element = array(
        '#title' => $field['widget']['label'],
        '#value' => $value,
      );

      $user_data .= theme('item', $element);
    }
  } elseif($user->uid == $vars['account']->uid) {
    // Present a create your profile data link, in case the user viewing the profile
    // is the same as the logged in user
    $element = array(
      '#content_type' =>  $params['type'],
      '#uid' =>  $user->uid,
    );
    
    $user_data = theme('content_profile_display_add_link', $element);
  }
    
  $vars['user_profile'] .= '<div class="user-data">' . $user_name . $user_data . '</div>';
  $vars['user_profile'] .= $vars['profile']['Extra Info'];
}

/**
 * CUSTOM CODE. Implementation of preprocess_user_profile_item().
 */
function redinmobiliaria_preprocess_user_profile_item(&$vars) {
  // Remove the title when no required, in the description field
  if($vars['element']['#type'] == 'user_profile_item' && stristr($vars['element']['#attributes']['class'], 'profile_description') !== FALSE){
    unset($vars['title']);
  }
}

/**
 * CUSTOM CODE. Implementation of preprocess_user_profile_category().
 */
function redinmobiliaria_preprocess_user_profile_category(&$vars) {
  // Remove the title when no required, in the description field
  if(isset($vars['element']['profile_description'])){
    unset($vars['title']);
  }
}

/**
 * Function overrides =================================================
 */

/**
 * Override of theme_menu_local_tasks().
 * Add argument to allow primary/secondary local tasks to be printed
 * separately. Use theme_links() markup to consolidate.
 */
function redinmobiliaria_menu_local_tasks($type = '') {
  if ($primary = menu_primary_local_tasks()) {
    $primary = "<ul class='links primary-tabs'>{$primary}</ul>";
  }
  if ($secondary = menu_secondary_local_tasks()) {
    $secondary = "<ul class='links secondary-tabs'>$secondary</ul>";
  }
  switch ($type) {
    case 'primary':
      return $primary;
    case 'secondary':
      return $secondary;
    default:
      return $primary . $secondary;
  }
}

/**
 * Override of theme_file().
 * Reduces the size of upload fields which are by default far too long.
 */
function redinmobiliaria_file($element) {
  _form_set_class($element, array('form-file'));
  $attr = $element['#attributes'] ? ' '. drupal_attributes($element['#attributes']) : '';
  return theme('form_element', $element, "<input type='file' name='{$element['#name']}' id='{$element['#id']}' size='15' {$attr} />");
}

/**
 * Override of theme_blocks().
 * Allows additional theme functions to be defined per region to
 * control block display on a per-region basis. Falls back to default
 * block region handling if no region-specific overrides are found.
 */
function redinmobiliaria_blocks($region) {
  // Allow theme functions some additional control over regions.
  $registry = theme_get_registry();
  if (isset($registry['blocks_'. $region])) {
    return theme('blocks_'. $region);
  }
  return module_exists('context') && function_exists('context_blocks') ? context_blocks($region) : theme_blocks($region);
}

/**
 * Override of theme_username().
 */
function redinmobiliaria_username($object) {
  if (!empty($object->name)) {
    // Shorten the name when it is too long or it will break many tables.
    $name = drupal_strlen($object->name) > 20 ? drupal_substr($object->name, 0, 15) .'...' : $object->name;
    $name = check_plain($name);

    // Default case -- we have a real Drupal user here.
    if ($object->uid && user_access('access user profiles')) {
      return l($name, 'user/'. $object->uid, array('attributes' => array('class' => 'username', 'title' => t('View user profile.'))));
    }
    // Handle cases where user is not registered but has a link or name available.
    else if (!empty($object->homepage)) {
      return l($name, $object->homepage, array('attributes' => array('class' => 'username', 'rel' => 'nofollow')));
    }
    // Produce an unlinked username.
    else {
      return "<span class='username'>{$name}</span>";
    }
  }
  return "<span class='username'>". variable_get('anonymous', t('Anonymous')) ."</span>";
}

/**
 * Override of theme_pager().
 * Easily one of the most obnoxious theming jobs in Drupal core.
 * Goals: consolidate functionality into less than 5 functions and
 * ensure the markup will not conflict with major other styles
 * (theme_item_list() in particular).
 */
function redinmobiliaria_pager($tags = array(), $limit = 10, $element = 0, $parameters = array(), $quantity = 9) {
  $pager_list = theme('pager_list', $tags, $limit, $element, $parameters, $quantity);

  $links = array();
  $links['pager-first'] = theme('pager_first', ($tags[0] ? $tags[0] : t('First')), $limit, $element, $parameters);
  $links['pager-previous'] = theme('pager_previous', ($tags[1] ? $tags[1] : t('Prev')), $limit, $element, 1, $parameters);
  $links['pager-next'] = theme('pager_next', ($tags[3] ? $tags[3] : t('Next')), $limit, $element, 1, $parameters);
  $links['pager-last'] = theme('pager_last', ($tags[4] ? $tags[4] : t('Last')), $limit, $element, $parameters);
  $links = array_filter($links);
  $pager_links = theme('links', $links, array('class' => 'links pager pager-links'));

  if ($pager_list) {
    return "<div class='pager clear-block'>$pager_list $pager_links</div>";
  }
}

/**
 * Split out page list generation into its own function.
 */
function redinmobiliaria_pager_list($tags = array(), $limit = 10, $element = 0, $parameters = array(), $quantity = 9) {
  global $pager_page_array, $pager_total, $theme_key;
  if ($pager_total[$element] > 1) {
    // Calculate various markers within this pager piece:
    // Middle is used to "center" pages around the current page.
    $pager_middle = ceil($quantity / 2);
    // current is the page we are currently paged to
    $pager_current = $pager_page_array[$element] + 1;
    // first is the first page listed by this pager piece (re quantity)
    $pager_first = $pager_current - $pager_middle + 1;
    // last is the last page listed by this pager piece (re quantity)
    $pager_last = $pager_current + $quantity - $pager_middle;
    // max is the maximum page number
    $pager_max = $pager_total[$element];
    // End of marker calculations.

    // Prepare for generation loop.
    $i = $pager_first;
    if ($pager_last > $pager_max) {
      // Adjust "center" if at end of query.
      $i = $i + ($pager_max - $pager_last);
      $pager_last = $pager_max;
    }
    if ($i <= 0) {
      // Adjust "center" if at start of query.
      $pager_last = $pager_last + (1 - $i);
      $i = 1;
    }
    // End of generation loop preparation.

    $links = array();

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      // Now generate the actual pager piece.
      for ($i; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $links["$i pager-item"] = theme('pager_previous', $i, $limit, $element, ($pager_current - $i), $parameters);
        }
        if ($i == $pager_current) {
          $links["$i pager-current"] = array('title' => $i);
        }
        if ($i > $pager_current) {
          $links["$i pager-item"] = theme('pager_next', $i, $limit, $element, ($i - $pager_current), $parameters);
        }
      }
      return theme('links', $links, array('class' => 'links pager pager-list'));
    }
  }
  return '';
}

/**
 * Return an array suitable for theme_links() rather than marked up HTML link.
 */
function redinmobiliaria_pager_link($text, $page_new, $element, $parameters = array(), $attributes = array()) {
  $page = isset($_GET['page']) ? $_GET['page'] : '';
  if ($new_page = implode(',', pager_load_array($page_new[$element], $element, explode(',', $page)))) {
    $parameters['page'] = $new_page;
  }

  $query = array();
  if (count($parameters)) {
    $query[] = drupal_query_string_encode($parameters, array());
  }
  $querystring = pager_get_querystring();
  if ($querystring != '') {
    $query[] = $querystring;
  }

  // Set each pager link title
  if (!isset($attributes['title'])) {
    static $titles = NULL;
    if (!isset($titles)) {
      $titles = array(
        t('« first') => t('Go to first page'),
        t('‹ previous') => t('Go to previous page'),
        t('next ›') => t('Go to next page'),
        t('last »') => t('Go to last page'),
      );
    }
    if (isset($titles[$text])) {
      $attributes['title'] = $titles[$text];
    }
    else if (is_numeric($text)) {
      $attributes['title'] = t('Go to page @number', array('@number' => $text));
    }
  }

  return array(
    'title' => $text,
    'href' => $_GET['q'],
    'attributes' => $attributes,
    'query' => count($query) ? implode('&', $query) : NULL,
  );
}

/**
 * Override of theme_views_mini_pager().
 */
function redinmobiliaria_views_mini_pager($tags = array(), $limit = 10, $element = 0, $parameters = array(), $quantity = 9) {
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.


  $links = array();
  if ($pager_total[$element] > 1) {
    $links['pager-previous'] = theme('pager_previous', (isset($tags[1]) ? $tags[1] : t('‹‹')), $limit, $element, 1, $parameters);
    $links['pager-current'] = array('title' => t('@current of @max', array('@current' => $pager_current, '@max' => $pager_max)));
    $links['pager-next'] = theme('pager_next', (isset($tags[3]) ? $tags[3] : t('››')), $limit, $element, 1, $parameters);
    return theme('links', $links, array('class' => 'links pager views-mini-pager'));
  }
}

/**
 * CUSTOM CODE. Theme function for the form simplenews_block_form_39
 */
function redinmobiliaria_simplenews_block_form_39($form) {
  global $user;
  
  if(!$user->uid){
    $form['submit']['#value'] = t('Subscribe');
  }
  
  return drupal_render($form);
}

/**
 * CUSTOM CODE. Theme function for the form search_form
 */
function redinmobiliaria_search_form($form) {
  // If the search form has not been deplyed by the module 'redinmob_search',
  // simple render it
  if($form['module']['#value'] != 'redinmob_search'){
    return drupal_render($form);
  }
  
  // Change some stuff in the form
  $form['basic']['main_group']['sm_cck_field_ciudad_sector']['hierarchical_select']['selects'][0]['#options']['none'] = t('- City -');
  $form['basic']['inline']['keys']['#title'] = t('Keywords');
  
  $form['basic']['main_group']['#prefix'] = '<div class="selective-search">';
  $form['basic']['main_group']['#suffix'] = '</div>';
  
  return drupal_render($form);
}

/**
 * CUSTOM CODE. Override of theme_views_mini_pager()
 */
function redinmobiliaria_menu_item_link($link) {
  // Modify the href of the item link, if it comes from primary-links and has
  // the adecuate rel attribute set
  if($link['menu_name'] == 'primary-links' && ($rel = $link['options']['attributes']['rel'])){
    $link['href'] = theme('redinmob_search_get_search_path', $rel, $link['href']);
  }

  if (empty($link['localized_options'])) {
    $link['localized_options'] = array();
  }
  
  return l($link['title'], $link['href'], $link['localized_options']);  
}

/**
 * CUSTOM CODE. Override of theme_privatemsg_views_participants()
 */
function redinmobiliaria_privatemsg_views_participants($participants, $link = TRUE, $replace = TRUE) {
  global $user;
  $items = array();
  $count = 0;
  
  foreach ($participants as $participant) {
    $uid = $participant->uid;   
    
    if ($user->uid == $uid && $replace) {
      $item = theme('user_picture', $user, 'user_photo_in_message_mailbox', array('class' => 'me'));
    }
    elseif ($link && user_access('access user profiles')) {
      $account = user_load(array('uid' => $uid));
      $item = theme('user_picture', $account, 'user_photo_in_message_mailbox');
    }
    else {
      $item = check_plain($participant->name);
    }
    
    // Add a wrapper around each user data item, so each third item will have a 
    // custom class
    $count++;
    $custom_class = '';
    
    if($count % 3 == 0){
      $custom_class = 'third-item';
    }
    
    $items[] = '<div' . ($custom_class ? " class='$custom_class'" : '') . '>' . $item . '</div>';
  }
  
  return implode('', $items);
}

/**
 * CUSTOM CODE. Override of theme_views_slideshow_singleframe_pager()
 * 
 * Used to implement the list of properties in a select, just for the properties
 * displayed in a project
 */
function redinmobiliaria_views_slideshow_singleframe_pager($vss_id, $view, $options) {
  // If the view is not the required, return
  if($view->name != 'propiedades_proyecto'){
    return;
  }
  
  // Go through the items of the view, to generate the items for the select
  foreach ($view->result as $item) {
    $node = node_load($item->nid);
    
    $select_options[] = $node->title_old;
  }
  
  // Create the elements responsible of the change of item
  $element['properties'] = array(
    '#type' => 'fieldset',
    '#title' => t('Unidades del Proyecto'),
  );
  
  $element['properties']['selector'] = array(
    '#type' => 'select',
    '#options' => $select_options,
  );

  drupal_add_js('$(document).ready(function(){
                   $(".view-propiedades-proyecto select").change(function(){
                     var $links = $(this).parents("fieldset").next();
                     var o = $links.children().get($(this).val());
                     
                     $(o).click();
                   });
                 });', 'inline');

  // Load the normal pager items  
  $pager_type = $options['views_slideshow_singleframe']['pager_type'];

  $attributes['class'] = "views_slideshow_singleframe_pager views_slideshow_pager$pager_type";
  $attributes['id'] = "views_slideshow_singleframe_pager_" . $vss_id;
  $attributes = drupal_attributes($attributes);

  return drupal_render($element) . "<div$attributes></div>";
}
