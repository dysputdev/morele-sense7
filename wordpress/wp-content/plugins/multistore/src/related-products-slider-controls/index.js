/**
 * Inspector Controls for Related Products Slider
 *
 * @package MultiStore\Plugin
 */

import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	ToggleControl,
	TabPanel,
} from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';

import { tabs } from '../components/ResponsiveTabs';

import './style.scss';

/**
 * Add slider controls to Query block when it's our variation
 */
const withRelatedProductsSliderControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, setAttributes, name } = props;

		// Only apply to core/query with our namespace.
		if ( name !== 'core/query' || attributes.namespace !== 'multistore/related-products-slider' ) {
			return <BlockEdit { ...props } />;
		}

		/**
		 * Update slider attribute for specific device
		 *
		 * @param {string} device  Device name (desktop, tablet, mobile).
		 * @param {string} key     Attribute key.
		 * @param {*}      value   New value.
		 */
		const updateSliderAttribute = ( device, key, value ) => {
			const attributeName = `slider${device.charAt( 0 ).toUpperCase() + device.slice( 1 )}`;
			const currentSettings = attributes[ attributeName ] || {};

			setAttributes( {
				[ attributeName ]: {
					...currentSettings,
					[ key ]: value,
				},
			} );
		};

		/**
		 * Get slider attribute value for specific device
		 *
		 * @param {string} device  Device name (desktop, tablet, mobile).
		 * @param {string} key     Attribute key.
		 * @param {*}      defaultValue Default value.
		 * @return {*} Attribute value.
		 */
		const getSliderAttribute = ( device, key, defaultValue = null ) => {
			const attributeName = `slider${device.charAt( 0 ).toUpperCase() + device.slice( 1 )}`;
			const settings = attributes[ attributeName ] || {};
			return settings[ key ] !== undefined ? settings[ key ] : defaultValue;
		};

		/**
		 * Render controls for specific device
		 *
		 * @param {Object} tab Tab data with device name.
		 * @return {JSX.Element} Controls panel.
		 */
		const renderDeviceControls = ( tab ) => {
			const device = tab.name;
			const isDesktop = device === 'desktop';

			return (
				<>
					<PanelBody title={ __( 'Slider Settings', 'multistore' ) } initialOpen={ true }>
						<RangeControl
							label={ __( 'Products per Page', 'multistore' ) }
							value={ getSliderAttribute( device, 'perPage', isDesktop ? 4 : null ) }
							onChange={ ( value ) => updateSliderAttribute( device, 'perPage', value ) }
							min={ 1 }
							max={ 8 }
							help={ __( 'Number of products visible per slide', 'multistore' ) }
						/>

						<RangeControl
							label={ __( 'Gap', 'multistore' ) }
							value={ getSliderAttribute( device, 'gap', isDesktop ? 20 : null ) }
							onChange={ ( value ) => updateSliderAttribute( device, 'gap', value ) }
							min={ 0 }
							max={ 100 }
							help={ __( 'Space between slides in pixels', 'multistore' ) }
						/>

						{ isDesktop && (
							<>
								<RangeControl
									label={ __( 'Transition Speed', 'multistore' ) }
									value={ getSliderAttribute( device, 'speed', 400 ) }
									onChange={ ( value ) => updateSliderAttribute( device, 'speed', value ) }
									min={ 100 }
									max={ 2000 }
									step={ 100 }
									help={ __( 'Animation speed in milliseconds', 'multistore' ) }
								/>

								<ToggleControl
									label={ __( 'Rewind', 'multistore' ) }
									checked={ getSliderAttribute( device, 'rewind', true ) }
									onChange={ ( value ) => updateSliderAttribute( device, 'rewind', value ) }
									help={ __( 'Go back to first slide after last one', 'multistore' ) }
								/>
							</>
						) }
					</PanelBody>

					<PanelBody title={ __( 'Navigation', 'multistore' ) } initialOpen={ false }>
						<ToggleControl
							label={ __( 'Show Arrows', 'multistore' ) }
							checked={ getSliderAttribute( device, 'arrows', isDesktop ? true : null ) }
							onChange={ ( value ) => updateSliderAttribute( device, 'arrows', value ) }
						/>

						<ToggleControl
							label={ __( 'Show Pagination', 'multistore' ) }
							checked={ getSliderAttribute( device, 'pagination', isDesktop ? true : null ) }
							onChange={ ( value ) => updateSliderAttribute( device, 'pagination', value ) }
						/>
					</PanelBody>

					{ isDesktop && (
						<PanelBody title={ __( 'Autoplay', 'multistore' ) } initialOpen={ false }>
							<ToggleControl
								label={ __( 'Enable Autoplay', 'multistore' ) }
								checked={ getSliderAttribute( device, 'autoplay', false ) }
								onChange={ ( value ) => updateSliderAttribute( device, 'autoplay', value ) }
							/>

							{ getSliderAttribute( device, 'autoplay', false ) && (
								<RangeControl
									label={ __( 'Autoplay Interval', 'multistore' ) }
									value={ getSliderAttribute( device, 'interval', 5000 ) }
									onChange={ ( value ) => updateSliderAttribute( device, 'interval', value ) }
									min={ 1000 }
									max={ 10000 }
									step={ 500 }
									help={ __( 'Time between slides in milliseconds', 'multistore' ) }
								/>
							) }
						</PanelBody>
					) }
				</>
			);
		};

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<TabPanel
						className="multistore-responsive-tab-panel"
						activeClass="is-active"
						tabs={ tabs }
					>
						{ renderDeviceControls }
					</TabPanel>
				</InspectorControls>
			</>
		);
	};
}, 'withRelatedProductsSliderControls' );

addFilter(
	'editor.BlockEdit',
	'multistore/related-products-slider-controls',
	withRelatedProductsSliderControls
);
