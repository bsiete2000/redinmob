// $Id: $

// Let the system work with another version on jQuery
var $jq = jQuery.noConflict();

// Uniforms all form items
$jq(function(){
  $jq("select, input:checkbox, input:radio, input:file").uniform();
});

// To prevent the change of version of the jQuery object, that variable
// is assigned again to the value of the old jQuery version
jQuery = $;

Drupal.behaviors.redinmobiliaria = function (context) {
  $('fieldset.collapsible:not(.redinmobiliaria-processed) > legend > .fieldset-title').each(function() {
    var fieldset = $(this).parents('fieldset').eq(0);
    fieldset.addClass('redinmobiliaria-processed');

    // Expand any fieldsets containing errors.
    if ($('input.error, textarea.error, select.error', fieldset).size() > 0) {
      $(fieldset).removeClass('collapsed');
    }

    // Add a click handler for toggling fieldset state.
    $(this).click(function() {
      if (fieldset.is('.collapsed')) {
        $(fieldset).removeClass('collapsed').children('.fieldset-content').show();
      }
      else {
        $(fieldset).addClass('collapsed').children('.fieldset-content').hide();
      }
      return false;
    });
  });
  
  // Update all file uniformed items, just in case the context passed is a jQuery object
  if(typeof context.jquery == 'string'){
    if($("input:file", context).parent('.uploader').size() == 0){
      context.each(function(){
        // Use $jq to do the uniform, not $
        $jq("input:file", this).uniform();
      });
    }
  }  
  
  // Update all file uniformed items, just in case the context passed is a function
  if(typeof context == 'function'){
    context().each(function(){
      // Use $jq to do the uniform, not $
      $jq("select", this).uniform();
      
      // Set a function that reacts to an event, to remove all selectors who 
      // don't have a select
      var $parent = this;
      
      $("select", this).click(function(){
        func = function(){
          $('.selector:not(:has(select))', $parent).remove();
        }
        
        // For the time delay, get the value from the one set in the Hierarchical module
        var id_item;
        
        for(item in Drupal.settings.HierarchicalSelect.settings){
          id_item = item;
        }

        setTimeout('new func()', parseInt(Drupal.settings.HierarchicalSelect.settings[id_item].animationDelay) + 20);
      });
    });
  }  
};
