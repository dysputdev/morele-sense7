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
<form class="multistore-modal__form" id="password-change-form" method="post">
	<div class="multistore-modal__body">
		<div class="form-field">
			<label for="password_current_modal"><?php esc_html_e( 'Stare hasło', 'sense7' ); ?></label>
			<div class="password-input-wrapper">
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current_modal" autocomplete="off" required />
			</div>
		</div>

		<div class="form-field">
			<label for="password_1_modal"><?php esc_html_e( 'Nowe hasło', 'sense7' ); ?></label>
			<div class="password-input-wrapper">
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1_modal" autocomplete="off" required />
				<button type="button" class="password-toggle" data-target="password_1_modal" aria-label="<?php esc_attr_e( 'Pokaż/ukryj hasło', 'sense7' ); ?>">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-show">
						<path d="M1.66666 10C1.66666 10 4.16666 5 10 5C15.8333 5 18.3333 10 18.3333 10C18.3333 10 15.8333 15 10 15C4.16666 15 1.66666 10 1.66666 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-hide" style="display: none;">
						<path d="M8.81668 8.81667C8.53581 9.09375 8.31169 9.42371 8.15714 9.78643C8.00259 10.1491 7.92073 10.5392 7.91668 10.9333C7.91668 11.7283 8.23298 12.4908 8.79559 13.0534C9.3582 13.616 10.1207 13.9323 10.9158 13.9323C11.3115 13.9323 11.7032 13.8513 12.0667 13.6942M3.25001 3.25L16.75 16.75M7.48334 7.48333C6.67179 8.1 6.04167 8.9 5.66667 9.83333C5.66667 9.83333 6.83334 13.5 10 13.5C11.025 13.5 11.9583 13.2333 12.75 12.7833M10.9167 6.50833C11.1944 6.53086 11.4687 6.58485 11.7333 6.66917C12.3417 6.86667 12.8917 7.23083 13.3167 7.71917C13.7417 8.2075 14.0275 8.80083 14.1417 9.44167C14.1667 9.585 14.1833 9.725 14.1917 9.86667M10 5C13.1667 5 14.3333 8.66667 14.3333 8.66667C14.0833 9.3 13.7417 9.88333 13.3167 10.3833" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
			<div class="password-requirements">
				<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.5"/>
					<path d="M6 3V6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
					<circle cx="6" cy="8.5" r="0.5" fill="currentColor"/>
				</svg>
				<span><?php esc_html_e( 'Hasło musi mieć jedną wielką literę, jedną małą literę, jedną cyfrę, znak specjalny, minimum 8 znaków', 'sense7' ); ?></span>
			</div>
		</div>

		<div class="form-field">
			<label for="password_2_modal"><?php esc_html_e( 'Potwierdź nowe hasło', 'sense7' ); ?></label>
			<div class="password-input-wrapper">
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2_modal" autocomplete="off" required />
			</div>
		</div>

		<div class="multistore-modal__error" id="password-error" style="display: none;"></div>
	</div>

	<div class="multistore-modal__footer">
		<button type="button" class="button button--secondary" data-action="close-modal" data-modal-id="password-modal">
			<?php esc_html_e( 'Anuluj', 'sense7' ); ?>
		</button>
		<button type="submit" class="button button--primary">
			<?php esc_html_e( 'Zmień hasło', 'sense7' ); ?>
		</button>
	</div>

	<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce-modal' ); ?>
	<input type="hidden" name="action" value="save_account_details" />
	<input type="hidden" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>" />
</form>
<?php
$content = ob_get_clean();

// Render modal.
\Sense7\Theme\WooCommerce\Account::render_modal(
	array(
		'id'      => 'password-modal',
		'title'   => __( 'Ustaw nowe hasło', 'sense7' ),
		'content' => $content,
	)
);