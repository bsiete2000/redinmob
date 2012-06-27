/**
 * Added a behavior used to do the community message loading
 */
Drupal.behaviors.community_messages_ahah_process_load = function(context){
  // This behavior is supposed to run on ahah sucessful responses only
  if(typeof context == 'object' && context.jquery){
    if(context.children().size()){
      // Launch the process in the correct element, to do the message loading process
      context.closest('.ahah-wrapper-container').loadCommunityMessage();      
    }
  }
}
