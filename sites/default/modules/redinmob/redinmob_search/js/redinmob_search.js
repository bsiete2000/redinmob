// $Id:  $

Drupal.behaviors.redinmob_search = function (context) {
  // Get all the items that are used to deactivate a facet
  $unclick = $('a.apachesolr-unclick', context);

  // Add a checkbox to each unclick link
  $unclick.each(function(){
    // Get the url
    var href = $(this).attr('href');

    $(this).text('');

    // Create a container label
    $label = $('<label>' + $(this).parent().text() + '</label>');

    // Create a checkbox
    $input = $('<input type="checkbox">').attr({
      'checked': 'check'
    }).click(function(){
      document.location = href;
    });

    var $parent = $(this).parent();

    $(this).parent().text('');
    $parent.append($label.prepend($input));
  });

  // Hide all the links
  $unclick.hide();

  // Get all the items that are used to activate a facet
  $click = $('a.apachesolr-facet', context);

  // Replace the original link by a checkbox version
  $click.each(function(){
    // Get the url
    var href = $(this).attr('href');

    // Create a container label
    $label = $('<label>' + $(this).text() + '</label>');

    // Create a checkbox
    $input = $('<input type="checkbox">').attr({
      'checked': ''
    }).click(function(){
      document.location = href;
    });

    $(this).parent().prepend($label.prepend($input));
  });

  // Hide all the links
  $click.hide();
};
