<?php
/**
 * Template part for tooltip.
 *
 * @package multistore
 */

?>

<div class="multistore-tooltip">
	<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="multistore-tooltip__icon">
		<rect x="0.5" y="0.5" width="13" height="13" rx="1.5" stroke="currentColor"/>
		<path d="M6.22195 3.95404V2.42004H7.63895V3.95404H6.22195ZM8.54895 11H5.42895V9.93404H6.29995V6.24204H5.37695V5.16304H7.67795V9.93404H8.54895V11Z" fill="currentColor"/>
	</svg>
	<div class="multistore-tooltip__content">
		<?php echo wp_kses( $args['tooltip'], array( 'b', 'i', 'em', 'strong', 'a' ) ); ?>
	</div>
</div>
