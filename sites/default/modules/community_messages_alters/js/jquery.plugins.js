/*
 * jQuery plugin used do the message loading, into the main view
 */
(function($) {
  $.fn.loadCommunityMessage = function() {
    // Locate the content to be added
    $content = $(this)
      .find('.node-type-community_message')
      .parent();
      
    // Locate the view content area
    $wrapper = $(this)
      .closest('#content')
      .find('.view-community-messages > .view-content');
      
    // Create a view content area, if no one exists
    if(!$wrapper.size()){
      $wrapper = $(this)
        .closest('#content')
        .find('.view-community-messages')
        .append('<div class="view-content"></div>')
        .find('.view-content');
    }
      
    // Create the wrapper to put the content into, with class names and other stuff
    $item = $('<div></div>')
      .addClass('views-row')
      .addClass('views-row-first')
      .append($content)
      .hide();
      
    var odd = true;
    
    if(!$wrapper.children().size()){
      $item.addClass('views-row-last');
    } else {
      odd = $wrapper.children(':first-child')
        .removeClass('views-row-first')
        .hasClass('views-row-even');
    }
      
    $item.addClass(odd ? 'views-row-odd' : 'views-row-even');
      
    // Put the element into the view
    $wrapper.prepend($item);
    $item.show('slow');
    
    // Remove the content of the ahah response container
    $(this).children().remove();
    
    // Remove the form data 
    $('#node-form textarea').val('');
  };
}(jQuery));
