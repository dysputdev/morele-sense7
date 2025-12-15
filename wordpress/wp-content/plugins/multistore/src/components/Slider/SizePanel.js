import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl, RangeControl, TextControl } from '@wordpress/components';


export const SizePanel = ({ getValue, setValue, addInheritedIndicator }) => {
	return (
		<PanelBody title={__('Wymiary', 'multistore')} initialOpen={false}>
		<TextControl
			label={addInheritedIndicator(__('Szerokość', 'multistore'), 'width')}
			value={getValue('width')}
			onChange={(value) => setValue('width', value)}
			help={__('Definiuje maksymalną szerokość karuzeli, akceptuje format CSS jak 10em, 80vw.', 'multistore')}
			placeholder="auto"
		/>

		<TextControl
			label={addInheritedIndicator(__('Wysokość', 'multistore'), 'height')}
			value={getValue('height')}
			onChange={(value) => setValue('height', value)}
			help={__('Definiuje wysokość slajdu, akceptuje format CSS z wyjątkiem %.', 'multistore')}
			placeholder="auto"
		/>

		{!getValue('autoWidth') && (
			<TextControl
				label={addInheritedIndicator(__('Stała szerokość slajdu', 'multistore'), 'fixedWidth')}
				value={getValue('fixedWidth')}
				onChange={(value) => setValue('fixedWidth', value)}
				help={__('Ustawia stałą szerokość slajdów w formacie CSS. Karuzela zignoruje opcję perPage.', 'multistore')}
				placeholder="0"
			/>
		)}

		<TextControl
			label={addInheritedIndicator(__('Stała wysokość slajdu', 'multistore'), 'fixedHeight')}
			value={getValue('fixedHeight')}
			onChange={(value) => setValue('fixedHeight', value)}
			help={__('Ustawia stałą wysokość slajdów w formacie CSS (z wyjątkiem %). Ignoruje perPage, height i heightRatio.', 'multistore')}
			placeholder="0"
		/>

		<RangeControl
			label={addInheritedIndicator(__('Proporcje wysokości', 'multistore'), 'heightRatio')}
			value={getValue('heightRatio')}
			onChange={(value) => setValue('heightRatio', value)}
			help={__('Określa wysokość slajdów jako stosunek do szerokości karuzeli. Ignorowane gdy podano height lub fixedHeight.', 'multistore')}
			min={0}
			max={1}
			step={0.01}
		/>

		{getValue('type') !== 'fade' && (
			<ToggleControl
				label={addInheritedIndicator(__('Auto szerokość', 'multistore'), 'autoWidth')}
				help={__('Szerokość slajdów określana jest przez ich własną szerokość. Nie używaj perPage i perMove (lub ustaw na 1).', 'multistore')}
				checked={getValue('autoWidth')}
				onChange={(value) => setValue('autoWidth', value)}
			/>
		)}

		<ToggleControl
			label={addInheritedIndicator(__('Auto wysokość', 'multistore'), 'autoHeight')}
			checked={getValue('autoHeight')}
			onChange={(value) => setValue('autoHeight', value)}
			help={__('Wysokość slajdów określana jest przez ich własną wysokość. Nie używaj perPage i perMove (lub ustaw na 1).', 'multistore')}
		/>
	</PanelBody>
	);
}