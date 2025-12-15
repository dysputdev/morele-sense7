import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl, SelectControl, TextControl, RangeControl } from '@wordpress/components';

export const OtherPanel = ({ getValue, setValue, addInheritedIndicator }) => {
	return (
		<PanelBody title={__('Inne', 'multistore')} initialOpen={false}>
			<SelectControl
				label={addInheritedIndicator(__('Kierunek', 'multistore'), 'direction')}
				value={getValue('direction')}
				onChange={(value) => setValue('direction', value)}
				help={__('Kierunek karuzeli.', 'multistore')}
				options={[
					{ label: __('Lewo do prawa', 'multistore'), value: 'ltr' },
					{ label: __('Prawo do lewa', 'multistore'), value: 'rtl' },
					{ label: __('Góra-dół', 'multistore'), value: 'ttb' }
				]}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Cover mode', 'multistore'), 'cover')}
				checked={getValue('cover')}
				onChange={(value) => setValue('cover', value)}
				help={__('Konwertuje src obrazu na CSS background-image URL elementu rodzica. Wymaga height, fixedHeight lub heightRatio.', 'multistore')}
			/>

			<TextControl
				label={addInheritedIndicator(__('Klasa slajdu', 'multistore'), 'slideClass')}
				value={getValue('slideClass')}
				onChange={(value) => setValue('slideClass', value)}
				help={__('Niestandardowa klasa CSS dla elementów slajdów.', 'multistore')}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Slider jako nawigacja', 'multistore'), 'isNavigation')}
				checked={getValue('isNavigation')}
				onChange={(value) => setValue('isNavigation', value)}
				help={__('Jeśli true, karuzela czyni slajdy klikalnymi do nawigacji innej karuzeli. Użyj Splide#sync(). Wyłącz pagination.', 'multistore')}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Trim space', 'multistore'), 'trimSpace')}
				checked={getValue('trimSpace')}
				onChange={(value) => setValue('trimSpace', value)}
				help={__('Określa, czy przycinać przestrzenie przed/po karuzeli jeśli dostępna jest opcja focus.', 'multistore')}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Update on move', 'multistore'), 'updateOnMove')}
				checked={getValue('updateOnMove')}
				onChange={(value) => setValue('updateOnMove', value)}
				help={__('Aktualizuje klasę statusu is-active tuż przed przesunięciem karuzeli.', 'multistore')}
			/>

			<RangeControl
				label={addInheritedIndicator(__('Throttle (ms)', 'multistore'), 'throttle')}
				value={getValue('throttle')}
				onChange={(value) => setValue('throttle', value)}
				help={__('Limit częstotliwości wywoływania zdarzeń resize/scroll.', 'multistore')}
				min={0}
				max={500}
				step={10}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Destroy', 'multistore'), 'destroy')}
				checked={getValue('destroy')}
				onChange={(value) => setValue('destroy', value)}
				help={__('Czy zniszczyć slider (używane z media queries).', 'multistore')}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Używaj breakpoints', 'multistore'), 'breakpoints')}
				checked={getValue('breakpoints')}
				onChange={(value) => setValue('breakpoints', value)}
				help={__('Czy wykorzystywać responsywne breakpoints.', 'multistore')}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Reduced motion', 'multistore'), 'reducedMotion')}
				checked={getValue('reducedMotion')}
				onChange={(value) => setValue('reducedMotion', value)}
				help={__('Opcje używane gdy wykryto (prefers-reduced-motion: reduce).', 'multistore')}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Live region', 'multistore'), 'live')}
				checked={getValue('live')}
				onChange={(value) => setValue('live', value)}
				help={__('Włącza live region. Jeśli isNavigation jest włączone, Splide tego nie aktywuje.', 'multistore')}
			/>
		</PanelBody>
	)
}