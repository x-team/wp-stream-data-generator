<div class="wrap">

	<form action="" method="post" id="generator-form">

		<h2><?php esc_html_e( 'Stream Data Generator', 'stream-data-exporter' ) ?></h2>
		<p><?php esc_html_e( 'Generate dummy records to populate your Stream for testing.', 'stream-data-generator' ) ?></p>

		<?php do_action( 'wp_stream_before_generator_form' ) ?>

		<input type="hidden" name="page" value="<?php echo WP_Stream_Data_Generator::GENERATOR_PAGE_SLUG; ?>"/>

		<?php wp_nonce_field( 'stream-data-generator-page', 'stream_data_generator_nonce', false ) ?>

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="wp_stream_data_generator_date_from"><?php esc_html_e( 'Start Date', 'stream-data-generator' ); ?></label>
					</th>
					<td>
						<div class="date-inputs">
							<i class="date-remove dashicons" style="display: none;"></i>
							<input type="text" name="wp_stream_data_generator[date_from]" id="wp_stream_data_generator_date_from" value="">
						</div>
						<p class="description"><?php esc_html_e( 'Dummy records will be generated beginning on this date.', 'stream' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wp_stream_data_generator_date_to"><?php esc_html_e( 'End Date', 'stream-data-generator' ); ?></label>
					</th>
					<td>
						<div class="date-inputs">
							<i class="date-remove dashicons" style="display: none;"></i>
							<input type="text" name="wp_stream_data_generator[date_to]" id="wp_stream_data_generator_date_to" value="">
						</div>
						<p class="description"><?php esc_html_e( 'Dummy records will stop on this date.', 'stream' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wp_stream_data_generator_entries_per_day"><?php esc_html_e( 'Entries Per Day', 'stream-data-generator' ); ?></label>
					</th>
					<td>
						<input type="text" name="wp_stream_data_generator[entries_per_day]" id="wp_stream_data_generator_entries_per_day" value="10">
						<p class="description"><?php esc_html_e( 'Number of dummy entries generated for each day.', 'stream-data-generator' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php do_action( 'wp_stream_after_generator_form' ) ?>

		<p class="submit"><input type="submit" name="submit" id="wp_stream_data_generator_submit" class="button button-primary" value="Generate!"></p>

	</form>
</div>