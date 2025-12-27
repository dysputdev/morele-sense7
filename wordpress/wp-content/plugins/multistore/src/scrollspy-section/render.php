<?php

namespace Multistore\Plugin\Block\ScrollSpySection;

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'id'    => ! empty( $attributes['sectionId'] ) ? esc_attr( $attributes['sectionId'] ) : '',
		'class' => 'multistore-block-scrollspy-section',
	)
);

?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<div class="scrollspy-section__header">
		<h2 class="scrollspy-section__title">
			<?php echo esc_html( $attributes['label'] ?? 'Section' ); ?>
		</h2>
	</div>
	<div class="scrollspy-section__content">
		<?php echo $content; ?>
	</div>
</div>
