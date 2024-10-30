<?php
/**
 * View for displayingemail form.
 *
 * @package cnews
 * @subpackage cnews/view
 */

?>
<div class="cnews">
	<?php if ( ! empty( $data['email']['body'] ) ) : ?>
		<div class="cnews-notice cnews-warning">
			<?php _e( 'The mail has not been sent yet.', 'cnews' ) ?>
		</div>
	<?php endif; ?>

	<table class="form-table cnews-table">
		<tr>
			<th><label><?php _e( 'Subject', 'cnews' ) ?>:</label></th>
			<td>
				<input type="text" class="regular-text"
				       name="cnews_email_subject" value="<?php echo $data['email']['subject'] ?>">
				<small
					class="text-muted"><?php _e( 'Must be between 5 and 78 characters long.', 'cnews' ) ?></small>
			</td>
		</tr>
		<tr>
			<th><label><?php _e( 'Message', 'cnews' ) ?>:</label></th>
			<td>
				<?php
				wp_editor( $data['email']['body'], 'cnews_email_body', array(
					'textarea_rows' => '5',
					'media_buttons' => FALSE,
					'wpautop'       => FALSE,
					'tinymce'       => array(
						'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink,undo,redo'
					),
				) );
				?>
			</td>
		</tr>
		<tr>
			<th><label><?php _e( 'Receivers', 'cnews' ) ?>:</label></th>
			<td>

				<?php foreach ( $data['user_roles'] as $key => $value ) : ?>
					<?php $checked = in_array( $key, $data['email']['user_groups'] ) ? 'checked' : '' ?>
					<p>
						<label>
							<input type="checkbox" name="cnews_user_groups[]"
							       value="<?php echo esc_attr( $key ) ?>" <?php echo esc_attr( $checked ) ?>> <?php echo $value ?>
						</label>
					</p>

				<?php endforeach; ?>

				<p><small><?php _e( 'All members in the selected groups that has enabled notifications will receive an email. If there are no members in the selected group(s) the mail will not be sent out.', 'cnews' ) ?></small></p>
			</td>
		</tr>
		<tr>
			<th><label><?php _e( 'Confirm', 'cnews' ) ?>:</label></th>
			<td>
				<label><input type="checkbox" name="cnews_notification_confirm"
				              value="1">
					<small> <?php _e( 'Please confirm in order to send the emails.', 'cnews' ) ?></small>
				</label>
				<?php submit_button( __('Send emails'), 'primary', 'cnews_submit' ) ?>
			</td>
		</tr>
		<?php wp_nonce_field( 'cnews_send_notification', 'cnews_notification_nonce' ); ?>
	</table>

</div>
