import { __ } from "@wordpress/i18n";
import { ToggleControl, SelectControl, PanelBody, TextControl } from "@wordpress/components";

export const ArrowPanel = ({ getValue, setValue, addInheritedIndicator }) => {
	return (
		<PanelBody title={__('Strzałki', 'multistore')} initialOpen={false}>
			<ToggleControl
				label={addInheritedIndicator(__('Pokaż strzałki', 'multistore'), 'arrows')}
				checked={getValue('arrows')}
				onChange={(value) => setValue('arrows', value)}
				help={__('Określa, czy tworzyć strzałki. Ta opcja musi być true jeśli dostarczasz strzałki przez HTML.', 'multistore')}
			/>

			{getValue('arrows') && (
				<>
					<SelectControl
						label={addInheritedIndicator(__('Styl strzałek', 'multistore'), 'arrowsStyle')}
						value={getValue('arrowsStyle')}
						onChange={(value) => {
							setValue('arrowsStyle', value);
							// Update arrowPath based on style
							const arrowPaths = {
								'default': 'm15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z',
								'chevron': 'M7.61 0.807L2.12 6.298l14.614 14.615L2.12 35.528l5.49 5.49 20.104-20.105z',
								'arrow': 'M0 20l20-20v12h20v16H20v12z',
								'thin': 'M25.37,0l14.06,16.99c.77.93.75,2.39-.05,3.29l-14.75,16.72-2.91-3.35,11.16-12.65L.03,21.24l-.03-4.72,33.18-.23L22.38,3.24l3-3.24Z',
								'circle': 'M20 0C8.954 0 0 8.954 0 20s8.954 20 20 20 20-8.954 20-20S31.046 0 20 0zm5 21h-4v4h-2v-4h-4v-2h4v-4h2v4h4v2z',
								'custom': getValue('arrowPath') || ''
							};
							if (value !== 'custom') {
								setValue('arrowPath', arrowPaths[value]);
							}
						}}
						help={__('Wybierz wbudowany styl strzałki lub użyj niestandardowego SVG path.', 'multistore')}
						options={[
							{ label: __('Domyślny', 'multistore'), value: 'default' },
							{ label: __('Chevron', 'multistore'), value: 'chevron' },
							{ label: __('Strzałka', 'multistore'), value: 'arrow' },
							{ label: __('Cienka', 'multistore'), value: 'thin' },
							{ label: __('Okrągła', 'multistore'), value: 'circle' },
							{ label: __('Niestandardowa', 'multistore'), value: 'custom' }
						]}
					/>

					{getValue('arrowsStyle') === 'custom' && (
						<TextControl
							label={addInheritedIndicator(__('SVG Path strzałki', 'multistore'), 'arrowPath')}
							value={getValue('arrowPath')}
							onChange={(value) => setValue('arrowPath', value)}
							help={__('Niestandardowa ścieżka SVG dla strzałki (SVG musi mieć rozmiar 40×40).', 'multistore')}
							placeholder="m7.61 0.807-2.12..."
						/>
					)}

					<SelectControl
						label={addInheritedIndicator(__('Pozycja strzałek', 'multistore'), 'arrowsPosition')}
						value={getValue('arrowsPosition')}
						onChange={(value) => setValue('arrowsPosition', value)}
						help={__('Określa pozycję strzałek nawigacji.', 'multistore')}
						options={[
							{ label: __('Na środku - wewnątrz', 'multistore'), value: 'center-inside' },
							{ label: __('Na środku - na zewnątrz', 'multistore'), value: 'center-outside' },
							{ label: __('Nad - wycentrowane', 'multistore'), value: 'top-center' },
							{ label: __('Nad - po prawej', 'multistore'), value: 'top-right' },
							{ label: __('Nad - po lewej', 'multistore'), value: 'top-left' },
							{ label: __('Pod - wycentrowane', 'multistore'), value: 'bottom-center' },
							{ label: __('Pod - po prawej', 'multistore'), value: 'bottom-right' },
							{ label: __('Pod - po lewej', 'multistore'), value: 'bottom-left' }
						]}
					/>

					<TextControl
						label={addInheritedIndicator(__('Dodatkowa klasa CSS', 'multistore'), 'arrowsClass')}
						value={getValue('arrowsClass')}
						onChange={(value) => setValue('arrowsClass', value)}
						help={__('Niestandardowe klasy CSS do dodania do kontenera strzałek.', 'multistore')}
						placeholder="my-custom-arrows"
					/>
				</>
			)}
		</PanelBody>
	)
}
