<?php

namespace Sense7\Theme\WooCommerce;

class MiniCart {

	public function __construct() {
		add_action( 'render_block', array( $this, 'change_minicart_icon' ), 10, 2 );
	}

	public function change_minicart_icon( $block_content, $block ) {
		if ( 'woocommerce/mini-cart' !== $block['blockName'] ) {
			return $block_content;
		}

		// replace svg icon.
		$icon = '<svg width="31" height="24" viewBox="0 0 33 26" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M24.738 21.05H11.92C11.2973 21.0488 10.693 20.839 10.2036 20.4539C9.71412 20.0689 9.36786 19.531 9.22006 18.926L5.29004 2.993" stroke="white" stroke-width="2" stroke-miterlimit="10"/>
			<path d="M5.28998 2.98901L5.078 2.12903C4.99951 1.80748 4.81546 1.52149 4.55533 1.31683C4.2952 1.11217 3.974 1.00062 3.64301 1H0" stroke="white" stroke-width="2" stroke-miterlimit="10"/>
			<path d="M5.29004 2.98904H31.0001L28.247 14.15C28.124 14.6537 27.8357 15.1015 27.4282 15.422C27.0207 15.7425 26.5175 15.9172 25.9991 15.9181H8.49905" stroke="white" stroke-width="2" stroke-miterlimit="10"/>
			<path d="M12.668 25C13.7538 25 14.634 24.1149 14.634 23.023C14.634 21.9312 13.7538 21.046 12.668 21.046C11.5822 21.046 10.702 21.9312 10.702 23.023C10.702 24.1149 11.5822 25 12.668 25Z" stroke="white" stroke-width="2" stroke-miterlimit="10"/>
			<path d="M24.738 25C25.8238 25 26.704 24.1149 26.704 23.023C26.704 21.9312 25.8238 21.046 24.738 21.046C23.6522 21.046 22.772 21.9312 22.772 23.023C22.772 24.1149 23.6522 25 24.738 25Z" stroke="white" stroke-width="2" stroke-miterlimit="10"/>
			</svg>';

		return preg_replace( '/<svg.*?>.*?<\/svg>/s', $icon, $block_content );
	}
}
