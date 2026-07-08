jQuery(function($) {
	console.log(_gsteca_sort_data);
	var isValid = window._gsteca_sort_data.is_pro_active;

	if ( isValid ) {
		var nonce = window._gsteca_sort_data.nonce;
	}

	function debounce(fn, delay) {
		let timer;
		return function(...args) {
			clearTimeout(timer);
			timer = setTimeout(() => fn.apply(this, args), delay);
		};
	}

	var resourceSort = $('#sortable-list');

	resourceSort.sortable({

		update: function() {

			// if ( ! isValid ) {
			// 	return;
			// }

			$('#loading-animation').show();


			$.ajax({
				url: _gsteca_sort_data.ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
				type: 'POST',
				async: true,
				cache: false,
				dataType: 'json',
				data: {
					_nonce: _gsteca_sort_data.nonce,
					action: _gsteca_sort_data.action, // Tell WordPress how to handle this ajax request
					order: resourceSort.sortable('toArray').toString() // Passes ID's of list items in	1,3,2 format
				},
				success: function() {
					setTimeout(function() {
						$('#loading-animation').hide();
					}, 200);
				},
				error: function() {
					alert('There was an error saving the updates');
					setTimeout(function() {
						$('#loading-animation').hide();
					}, 200);
				}
			});

		}
		
	});

	var $visibilityFieldsSort = $('.table-visibility .ui-sortable--table');

	$visibilityFieldsSort.sortable({

		update: function(event, ui) {
			

			let data = [];

			$visibilityFieldsSort.sortable('toArray').forEach( function( field_id ) {

				let $field = $('#' + field_id);

				let field = { field: field_id };

				$field.find('input[type="checkbox"]').each( function() {
					field[ $(this).attr('id').replace(field_id + '_', '') ] = $(this).is(':checked');
				});

				data.push( field );

			});

			$('#loading-animation').show();

			$.ajax({
				url: _gsteca_sort_data.ajaxurl,
				type: 'POST',
				async: true,
				cache: false,
				dataType: 'json',
				data:{
					action: _gsteca_sort_data.action,
					_wpnonce: _gsteca_sort_data.nonce,
					data: data
				},
				success: function(response) {
					$('#loading-animation').hide();
					return; 
				},
				error: function(xhr,textStatus,e) { 
					alert('There was an error saving the updates');
					$('#loading-animation').hide();
					return;
				}
			});

		}

	});

	var resourceSort = $('#sortable-list');

	resourceSort.sortable({

		update: function() {

			$('#loading-animation').show();

			var taxonomy = resourceSort.data('taxonomy'); // 🔥 ADD THIS

			$.ajax({
				url: _gsteca_sort_data.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					_nonce: _gsteca_sort_data.nonce,
					action: _gsteca_sort_data.action,
					order: resourceSort.sortable('toArray').toString(),
					taxonomy: taxonomy // 🔥 SEND TAXONOMY
				},
				success: function() {
					setTimeout(function() {
						$('#loading-animation').hide();
					}, 200);
				},
				error: function() {
					alert('There was an error saving the updates');
					setTimeout(function() {
						$('#loading-animation').hide();
					}, 200);
				}
			});
		}
	});

	var triggerUpdate = debounce(function() {
		var sortable = $visibilityFieldsSort.data("ui-sortable");
		if (sortable) {
			sortable._trigger("update", null, { item: null });
		}
	}, 100);

	$visibilityFieldsSort.on('change', 'input[type="checkbox"]', function() {
		triggerUpdate();
	});

	$visibilityFieldsSort.find('.table-row > div:first-child').on('click', function() {
		var $checkbox = $(this).siblings('div').find('input[type="checkbox"]');
		let checked = $checkbox.first().is(':checked');
		$checkbox.prop('checked', !checked);
		triggerUpdate();
	});

});

