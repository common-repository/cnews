<?php
/**
 * View for displaying emails that has been sent out.
 *
 * @package cnews
 * @subpackage cnews/view
 */

?>
<div class="cnews">
	<?php
	if ( ! empty( $data['emails'] ) ) :

		foreach ( $data['emails'] as $email ) : ?>
			<button
				class="cnews-accordion"><?php _e( 'Subject', 'cnews' ) ?>
				: <?php echo $email->subject ?>
			</button>
			<div class="cnews-panel">


				<table class="form-table cnews-table">
					<tr>
						<th><?php _e( 'Email sent' ) ?>:</th>
						<td>
							<?php echo date( 'm-d-Y H:i', $email->sent ); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Receivers' ) ?>:</th>
						<td>
							<?php

							$i = count( $email->user_groups );

							foreach ( $email->user_groups as $group ) :
								echo $group;
								echo $i > 1 ? ', ' : '';
								$i--;
							endforeach;

							?>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Email' ) ?>:</th>
						<td>
							<?php echo $email->body ?>
						</td>
					</tr>
				</table>

			</div>
		<?php endforeach;

	else : ?>
		<p><?php _e( 'No emails has been sent out.', 'cnews' ) ?></p>
	<?php endif; ?>
</div>
