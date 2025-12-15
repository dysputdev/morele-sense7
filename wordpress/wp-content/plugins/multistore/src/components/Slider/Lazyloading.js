import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, RangeControl } from '@wordpress/components';
export const LazyloadingPanel = ({ getValue, setValue, addInheritedIndicator }) => {
	return (
		<PanelBody title={__('Lazy loading', 'multistore')} initialOpen={false}>
			<SelectControl
				label={addInheritedIndicator(__('Lazy load', 'multistore'), 'lazyLoad')}
				value={getValue('lazyLoad')}
				onChange={(value) => setValue('lazyLoad', value)}
				help={__('Włącza lazy loading obrazów.', 'multistore')}
				options={[
					{ label: __('Wyłączone', 'multistore'), value: 'off' },
					{ label: __('Sequential', 'multistore'), value: 'sequential' },
					{ label: __('Nearby', 'multistore'), value: 'nearby' }
				]}
			/>

			{getValue('lazyLoad') !== 'off' && (
				<RangeControl
					label={addInheritedIndicator(__('Preload stron', 'multistore'), 'preloadPages')}
					value={getValue('preloadPages')}
					onChange={(value) => setValue('preloadPages', value)}
					help={__('Określa ile stron (nie slajdów) wokół aktywnego slajdu załadować wcześniej. Działa tylko z lazyLoad: nearby.', 'multistore')}
					min={0}
					max={5}
				/>
			)}
		</PanelBody>
	)
}