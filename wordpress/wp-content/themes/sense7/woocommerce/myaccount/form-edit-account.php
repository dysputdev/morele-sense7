<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

$hide_name = true;

/**
 * Hook - woocommerce_before_edit_account_form.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_before_edit_account_form' );
?>

<div class="myaccount-edit-account">
	<h2 class="myaccount-edit-account__title"><?php esc_html_e( 'Dane konta', 'sense7' ); ?></h2>

	<div class="myaccount-edit-account__actions">
		<!-- zmień hasło -->
		<a href="#password-modal" data-action="open-modal"><?php esc_html_e( 'Zmień hasło', 'sense7' ); ?></button>
		<!-- wyloguj -->
		<a href="<?php echo esc_url( wc_logout_url() ); ?>" class="myaccount-edit-account__logout"><?php esc_html_e( 'Logout', 'woocommerce' ); ?></a>
	</div>

	<?php // other fields. ?>
	<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >
		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

		<?php if ( false === $hide_name ) : ?>
		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
			<label for="account_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" aria-required="true" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
			<label for="account_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" aria-required="true" />
		</p>
		<div class="clear"></div>
		<?php endif; ?>

		<?php
			/**
			 * Hook where additional fields should be rendered.
			 *
			 * @since 8.7.0
			 */
			do_action( 'woocommerce_edit_account_form_fields' );
		?>

		<?php
			/**
			 * My Account edit account form.
			 *
			 * @since 2.6.0
			 */
			do_action( 'woocommerce_edit_account_form' );
		?>

		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	</form>

	<form class="edit-account edit-account--display-name" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >
		<div class="account-fields">
			<label for="account_display_name"><?php esc_html_e( 'Display name', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<div class="account-field">
				<input
					type="text"
					class="woocommerce-Input woocommerce-Input--text input-text"
					name="account_display_name"
					id="account_display_name"
					aria-describedby="account_display_name_description"
					value="<?php echo esc_attr( $user->display_name ); ?>"
					aria-required="true" />
				<button
					type="submit"
					class="account-field__button"
					name="save_account_details"
					value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"
					>
					<?php esc_html_e( 'Save', 'woocommerce' ); ?>
				</button>
			</div>
			<span id="account_display_name_description">
				<em><?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'woocommerce' ); ?></em>
			</span>
		</div>
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<input type="hidden" name="action" value="save_account_details" />
	</form>
		
	<form class="edit-account edit-account--email" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >
		<div class="account-fields">
			<label for="account_email">
				<?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
			</label>
			<div class="account-field">
				<input
					type="email"
					class="woocommerce-Input woocommerce-Input--email input-text"
					name="account_email"
					id="account_email"
					autocomplete="email"
					value="<?php echo esc_attr( $user->user_email ); ?>"
					aria-required="true" />
				<button
					type="submit"
					class="account-field__button"
					name="save_account_details"
					value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"
				>
					<?php esc_html_e( 'Save', 'woocommerce' ); ?>
				</button>
			</div>
		</div>
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<input type="hidden" name="action" value="save_account_details" />
	</form>
</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>

<?php
wc_get_template( 'myaccount/_password.php' );
wc_get_template( 'myaccount/_address-book.php' ); ?>
