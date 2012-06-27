/**
 * Generated a function to check if the form is in the last step
 */
(function ($) {
  $(document).ready(function(){
    Drupal.publishButtonAnonTimer = setTimeout(function(){
      if($('ul.tabs li:last-child').hasClass('active')){
        $('#node-form :submit').show('slow');      
      } else {
        $('#node-form :submit').hide('slow');
      }
      Drupal.publishButtonAnonTimer = setTimeout(arguments.callee, 200);
    }, 200);    
  });
}(jQuery));
