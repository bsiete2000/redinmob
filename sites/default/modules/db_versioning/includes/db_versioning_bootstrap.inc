<?php

// Definition of the hook name
define('HOOK_DB_QUERY_ALTER', 'db_query_alter');

// TODO: include the parser, some general way

/**
 * Function that enable the use of extra hooks to be run just before a db_query
 * call is executed
 * 
 * @staticvar array $files Array of files to be stored, after the process of discovering
 * @param string $query SQL string to be processed
 * @return void
 */
// TODO: handle db store of file data retrieved, and hooks, to prevent overload
// of the system with call after call, and to implement load ordering
function db_versioning_invoke_all(&$query) {
  // Recover all the implementor files, related to db_versioning
  static $files;
  
  if(is_null($files)){
    // Includes of necessary files to let the module do a search in the site files
    require_once 'includes/common.inc';
    require_once 'includes/file.inc';
  
    // Get the files
    $files = drupal_system_listing('\.db_versioning$', 'modules', 'name', 0);
  }
  
  // Go through the files, applying the necessary operations
  if(!is_null($files)){
    foreach ($files as $file) {
      $filename = './' . $file->filename;

      // Do the file loading
      if(is_file($filename)) {
        require_once $filename;

        // Test if the desired hook is implemented, if so, call it
        $function_name = $file->name . '_' . HOOK_DB_QUERY_ALTER;
        if(function_exists($function_name)){
          $function_name($query);
        }
      }
    }    
  }  
}
