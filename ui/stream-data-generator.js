/* globals wp_stream_data_generator */
jQuery(function($){

	var from = $('#wp_stream_data_generator_date_from'),
	to = $('#wp_stream_data_generator_date_to'),
	to_remove = to.prev('.date-remove'),
	from_remove = from.prev('.date-remove'),
	datepickers = $('').add(to).add(from);

	if ( jQuery.datepicker ) {

		// Apply a GMT offset due to Date() using the visitor's local time
		var siteGMTOffsetHours  = parseFloat( wp_stream_data_generator.gmt_offset );
		var localGMTOffsetHours = new Date().getTimezoneOffset() / 60 * -1;
		var totalGMTOffsetHours = siteGMTOffsetHours - localGMTOffsetHours;

		var localTime = new Date();
		var siteTime = new Date( localTime.getTime() + ( totalGMTOffsetHours * 60 * 60 * 1000 ) );
		var dayOffset = '0';
		var twoYearsAgo = new Date( siteTime.getTime() - ( 2 * 365 * 24 * 60 * 60 * 1000 ) );

		// check if the site date is different from the local date, and set a day offset
		if ( localTime.getDate() !== siteTime.getDate() || localTime.getMonth() !== siteTime.getMonth() ) {
			if ( localTime.getTime() < siteTime.getTime() ) {
				dayOffset = '+1d';
			} else {
				dayOffset = '-1d';
			}
		}

		datepickers.datepicker({
			dateFormat: 'yy/mm/dd',
			maxDate: dayOffset,
			changeMonth: true,
			changeYear: true,
			beforeShow: function() {
				$(this).prop( 'disabled', true );
			},
			onClose: function() {
				$(this).prop( 'disabled', false );
			}
		});

		datepickers.datepicker('widget').addClass('stream-datepicker');

		from.datepicker('option','defaultDate',twoYearsAgo).datepicker('setDate',twoYearsAgo);
		to.datepicker('option','defaultDate',siteTime).val(siteTime).datepicker('setDate',siteTime);

		from.on({
			'change': function () {
				if ('' !== from.val()) {
					from_remove.show();
					to.datepicker('option', 'minDate', from.val());
				} else {
					from_remove.hide();
				}
			}
		});

		to.on({
			'change': function () {
				if ('' !== to.val()) {
					to_remove.show();
					from.datepicker('option', 'maxDate', to.val());
				} else {
					to_remove.hide();
				}
			}
		});

		$('').add(from_remove).add(to_remove).on({
			'click': function () {
				$(this).next('input').val('').trigger('change');
			}
		});

	}

	// Confirmation on some important actions
	$('#wp_stream_data_generator_submit').click(function(e){
		if ( ! confirm( wp_stream_data_generator.i18n.confirm_generate ) ) {
			e.preventDefault();
		}
	});
});