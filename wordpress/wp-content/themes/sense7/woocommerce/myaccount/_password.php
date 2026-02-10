<?php
/**
 * Password Change Modal
 *
 * @package Sense7
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Prepare modal content.
ob_start();
?>
<div class="multistore-modal__body">
	<form class="multistore-modal__form" id="password-change-form" method="post">

		<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_current"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
		</div>

		<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="off" />

			<div class="description password-requirements">
				<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.5"/>
					<path d="M6 3V6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
					<circle cx="6" cy="8.5" r="0.5" fill="currentColor"/>
				</svg>
				<span><?php esc_html_e( 'Hasło musi mieć jedną wielką literę, jedną małą literę, jedną cyfrę, znak specjalny, minimum 8 znaków', 'sense7' ); ?></span>
			</div>
		</div>

		<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_2"><?php esc_html_e( 'Confirm new password', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="off" />
		</div>

		<div class="multistore-modal__error" id="password-error" style="display: none;"></div>

		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce-modal' ); ?>
		<input type="hidden" name="action" value="save_account_details" />
		<input type="hidden" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>" />
	</form>
</div>

<div class="multistore-modal__footer">
	<button type="button" class="button button--secondary" data-action="close-modal" data-modal-id="password-modal">
		<?php esc_html_e( 'Anuluj', 'sense7' ); ?>
	</button>
	<button type="submit" class="button button--primary" form="password-change-form">
		<?php esc_html_e( 'Zmień hasło', 'sense7' ); ?>
	</button>
</div>

<?php
$content = ob_get_clean();

$template_args = array(
	'id'      => 'password-modal',
	'title'   => esc_html__( 'Ustaw nowe hasło', 'sense7' ),
	'content' => $content,
);
get_template_part( 'template-parts/modal', null, $template_args );
// echo $content;
