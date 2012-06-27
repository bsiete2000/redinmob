Drupal.behaviors.views_exposed_form_propiedades = function(context){
  // On change of the selects, launches the form submit
  $('#views-exposed-form-Propiedades-panel-pane-1 select').change(function(){
    $(this).parents('form').submit();
  }); 
}