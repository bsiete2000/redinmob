/**
 * Here, a functionality is added to the field field_tamanio_construccion, for it
 * to support the hiding/disabling pattern, so any input text item inside it when hidden,
 * will be disabled too
 */
Drupal.behaviors.FieldTamanioConstruccionConditionalFixes = function(context){  
  Drupal.FieldTamanioConstruccionConditionalFixes = {
    "fixFunction" : function() {
                      $('#conditional-field-tamanio-construccion.conditional-field.controlled-field :text').each(function(){
                        if($(this).is(':hidden')){
                          $(this).attr('disabled', 'disabled');
                        } else {
                          $(this).removeAttr('disabled');
                        }
                      });
                    }
  }
  
  setInterval("new " + Drupal.FieldTamanioConstruccionConditionalFixes.fixFunction.toString(), 200);
}