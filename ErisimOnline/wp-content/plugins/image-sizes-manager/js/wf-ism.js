/*
  Image Sizes Manager
  (c) 2011. Web factory Ltd
  www.webfactoryltd.com
*/

var close_dialog;
var images = '';
var current_item = 0;
var ok_images = new Array();
var err_images = new Array();

function overlay_click_close() {
  if (close_dialog) {
    jQuery('.wf_dialog').dialog('close');
  }
  close_dialog = 0;
}

jQuery(document).ready(function($){
  // jQuery UI Dialog for adding or editing image sizes
  if (jQuery('.wf_dialog').length)
  jQuery('.wf_dialog').dialog({
    width: 420,
    height: 480,
    modal: true,
    autoOpen: false,
    open: function(){
      close_dialog = 1;
      $('.ui-widget-overlay').bind('click', overlay_click_close);
      if (jQuery('input#form_action').val() == 'add') {
        $('input:text', this).val('');
      }
      $('#ism-err').hide();
    },
    dialogClass: 'wp-dialog',
    buttons: [{
        text: 'Save',
        className: 'button-primary',
        'click': function() {
          var action = jQuery('input#form_action').val();
          var size_label = jQuery('input#size_label').val();
          var width = jQuery('input#width').val();
          var height = jQuery('input#height').val();
          var crop = jQuery('select#crop').val();
          var dialog = jQuery(this);
          jQuery.post(ajaxurl,
                     {'action':     'wf_save_image_size',
                      'size_label': size_label,
                      'width':      width,
                      'height':     height,
                      'crop':       crop,
                      'do':         action},
                     function(response) {
                       $('#ism-err').hide();
                       if (response != '1') {
                         $('#ism-err').html(response).show();
                       } else {
                         if (action == 'edit') {
                           alert('Changes have been saved.');
                         } else {
                           alert('New image size has been created.');
                         }
                         $('input', dialog).val('');
                         window.location.reload();
                       }
                     });
        }},
      // close button
      { text: 'Cancel',
        className: 'button-secondary',
        "click": function() {
          $('.ui-widget-overlay').unbind('click');
          jQuery(this).dialog("close");
        }
      }
    ]
  });

  // Progress bar function
  function progressBar(current, total) {
    current = current + 1;
    var percent = (current / total) * 100;
    jQuery("#wf_progress").progressBar(Math.round(percent), { max: 100, increment: 1, speed: 12, width: 300, height: 20, barImage: wf_ism_url + 'images/progressbg_red.gif', boxImage  : wf_ism_url + 'images/progressbar.gif'});
  } // progressBar

  // Regenerate images in the list (JSON)
  function regenerateImages(sizes, image_list) {
    var images = jQuery.parseJSON(image_list);
    var total_images = images.length;

    if (current_item >= total_images) {
      current_item = 0;
      $("#wf_progress").hide();
      msg = 'Done. Number of successfully generated images: ' + ok_images.length + ' in ' + jQuery('input.thumbnails:checked', '.wp-list-table').length;
      msg += jQuery('input.thumbnails:checked', '.wp-list-table').length == 1? ' size.': ' sizes.';
      if (err_images.length > 0) {
        msg += '\nImages with following IDs could not be regenerated: ' + err_images.join(', ') + '.';
      }
      alert(msg);
      jQuery("#wf_regenerate").removeAttr('disabled');
      return;
    }

    // Start the progressbar
    if (current_item == 0) {
      $("#wf_progress").show().progressBar(0, { max: 100, increment: 100, speed: 1, width: 300, height: 20, barImage: wf_ism_url + 'images/progressbg_red.gif', boxImage  : wf_ism_url + 'images/progressbar.gif'});
      ok_images = new Array;
      err_images = new Array;
    }

    // Bulk regenerate images
    jQuery.ajax({url: ajaxurl,
                 type: "POST",
                 data: "action=wf_thumbnail_regenerate&id=" + images[current_item].ID + sizes,
                 beforeSend: function() {
                 },
                 complete: function(data){
                   progressBar(current_item, total_images);
                   if (data.responseText != '0') {
                     ok_images.push(images[current_item].ID);

                   } else {
                     err_images.push(images[current_item].ID);
                   }
                   current_item++;
                   regenerateImages(sizes, image_list);
                 }});
  } // regenerateImages

  function regenerateImage(image_id) {
    // Start ajax
    jQuery.ajax({url: ajaxurl,
                 type: "POST",
                 data: "action=wf_thumbnail_regenerate&id=" + image_id,
                 complete: function(data){
                   if (data.responseText != '0') {
                     alert('All image sizes were successfully regenerated.');
                   } else {
                     alert('An error occurred while regenerating at least one image size.');
                   }
                 }});
  } // regenerateImage

  // Regenerate all images
  jQuery("#wf_regenerate").click(function(button){
    jQuery('input.thumbnails:checked', '.wp-list-table').each(function(){
      selected_sizes += '&thumbnails[]=' + jQuery(this).val();
    });

    if (!selected_sizes) {
      alert('Please select at least one image size.');
      return;
    }

    var question = confirm('Are you sure you want to regenerate all images for selected image sizes?');
    var selected_sizes = '';

    if (question) {
      jQuery("#wf_regenerate").attr('disabled', 'disabled');
      jQuery.ajax({url: ajaxurl,
                   type: "POST",
                   data: "action=wf_get_list",
                   success: function(data){
                     regenerateImages(selected_sizes, data);
                   }});

    } // if question
    return false;
  });


  // regenerate single image
  jQuery("a.regenerate-single").click(function(){
    var question = confirm('Are you sure you want to regenerate all custom image sizes for this image?');
    var image_id = jQuery(this).attr('id');
    if (question) {
      // Regenerate this image in< all it's sizes
      regenerateImage(image_id);
    }
    return false;
  });

  // add new image size
  jQuery("#wf_add_new_size").click(function(){
    jQuery("input#form_action", "div.wf_dialog").val('add');
    jQuery("input#size_label", "div.wf_dialog").removeAttr('disabled');
    jQuery("div.wf_dialog").dialog({ title: 'Add new image size' })
                           .dialog("open");
    return false;
  });

  // delete image size
  jQuery("a.delete").click(function(){
    var label = jQuery(this).attr('label');
    var tmp_bg = jQuery('tr#size-' + label).css('background-color');
    jQuery('tr#size-' + label).css('background-color', 'red');
    var question = confirm('Are you sure you want to delete the selected image size?');

    var row = jQuery(this);
    if (question) {
      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: "action=wf_delete&label=" + label,
        success: function(){
          jQuery('tr#size-' + label).remove();
        }
      });
    } else {
      jQuery('tr#size-' + label).css('background-color', '');
    }
    return false;
  });

  // edit image size
  jQuery("a.edit").click(function(){
    var label = jQuery(this).attr('id');
      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: "action=wf_edit&label=" + label,
        success: function(data){
          var obj = jQuery.parseJSON(data);
          jQuery("input#form_action", "div.wf_dialog").val('edit');
          jQuery("input#size_label","div.wf_dialog").val(obj.size_label).attr('disabled','disabled');
          jQuery("input#width","div.wf_dialog").val(obj.width);
          jQuery("input#height","div.wf_dialog").val(obj.height);
          // Crop
          if (obj.crop == '1') {
            jQuery("select#crop","div.wf_dialog").val('1');
          } else {
            jQuery("select#crop","div.wf_dialog").val('0');
          }
          jQuery("div.wf_dialog").dialog({ title: 'Edit image size' })
                                 .dialog("open");
        }
      });
    return false;
  });
});