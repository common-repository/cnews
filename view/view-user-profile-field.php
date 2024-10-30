<?php
/**
 * View for displaying user field.
 *
 * @package cnews
 * @subpackage cnews/view
 */

?>

<h3><?php _e( 'Receive notifications', 'cnews' ) ?></h3>
<table class="form-table">
	<tr>
		<th><label for="cnews_receive_notifications"><?php _e( 'Receive notifications', 'cnews' ) ?></label></th>
		<td><label><input type="checkbox" id="cnews_receive_notifications" name="cnews_receive_notifications" value="1" <?php echo 1 == $data['status'] ? 'checked' : '' ?>> Yes</label></td>
	</tr>
</table>
