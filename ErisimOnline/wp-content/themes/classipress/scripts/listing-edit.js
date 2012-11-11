function vantage_map_edit() {
	var geocoder = new google.maps.Geocoder();

	var map = new google.maps.Map(document.getElementById('listing-map'), {
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});

	var marker = new google.maps.Marker({
		map: map
	});

	function update_position(found_location) {
		map.setCenter(found_location);
		marker.setPosition(found_location);
	}

	var address = jQuery('#listing-address').val();
	
	if ( address != '' )
		 update_map(jQuery.noop);

	var lat = jQuery('input[name="lat"]').val();
	var lng = jQuery('input[name="lng"]').val();

	if ( lat != 0 && lng != 0 )
		update_position(new google.maps.LatLng(lat, lng));

	var map_initialized = false;

	function update_map(callback) {
		map_initialized = true;

		var data = {
			address: jQuery('#listing-address').val()
		}

		geocoder.geocode( data, function(results, status) {
			if ( status == google.maps.GeocoderStatus.OK ) {
				var found_location = results[0].geometry.location;

				jQuery('#listing-address').val(results[0].formatted_address);

				jQuery('input[name="lat"]').val(found_location.lat());
				jQuery('input[name="lng"]').val(found_location.lng());

				update_position(found_location);
			} else {
				alert("Google Maps error: " + status );
			}

			callback(status);
		});
	}

	jQuery('#listing-find-on-map').click(function(ev) {
		update_map(jQuery.noop);
	});

	function ensureMapInit(form) {
		if ( map_initialized ) {
			form.submit();
			return;
		}

		update_map(function(status) {
			form.submit();
		});
	}

	function loadFormFields() {
		var data = {
			action: 'app-render-form',
			listing_category: jQuery(this).val()
		};

		jQuery.post(VA_i18n.ajaxurl, data, function(response) {
			jQuery('#custom-fields').html(response);
		});
	}

	jQuery('#listing_category')
		.change(loadFormFields)
		.find('option').eq(0).val(''); // needed for jQuery.validate()

	jQuery('.uploaded').sortable();

	jQuery('#create-listing').validate({
		submitHandler: ensureMapInit
	});
}

	jQuery('#create-listing input[type="file"]').after('<input type="button" class="clear-file" value="' + VA_i18n.clear + '">');

	jQuery('#create-listing .clear-file').live('click', function() {
		jQuery(this).parent().html( jQuery(this).parent().html() );
		return false;
	});
