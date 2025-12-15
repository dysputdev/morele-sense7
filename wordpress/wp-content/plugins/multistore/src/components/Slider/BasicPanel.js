import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, ToggleControl, RangeControl, TextControl } from '@wordpress/components';

export const BasicPanel = ({ getValue, setValue, addInheritedIndicator }) => {
	return (
		<PanelBody title={__('Typ i podstawowe opcje', 'multistore')} initialOpen={true}>
			<SelectControl
				label={addInheritedIndicator(__('Typ slidera', 'multistore'), 'type')}
				value={getValue('type')}
				onChange={(value) => setValue('type', value)}
				help={__('Typ karuzeli. Aby zapętlić fade carousel, włącz opcję rewind.', 'multistore')}
				options={[
					{ label: __('Slide', 'multistore'), value: 'slide' },
					{ label: __('Loop', 'multistore'), value: 'loop' },
					{ label: __('Fade', 'multistore'), value: 'fade' }
				]}
			/>

			{getValue('type') !== 'loop' && (
				<>
					<ToggleControl
						label={addInheritedIndicator(__('Rewind', 'multistore'), 'rewind')}
						help={__('Określa, czy karuzela ma się przewijać do początku. Nie działa w trybie loop.', 'multistore')}
						checked={getValue('rewind')}
						onChange={(value) => setValue('rewind', value)}
					/>

					{getValue('rewind') && (
						<>
							<RangeControl
								label={addInheritedIndicator(__('Prędkość rewind (ms)', 'multistore'), 'rewindSpeed')}
								value={getValue('rewindSpeed')}
								onChange={(value) => setValue('rewindSpeed', value)}
								help={__('Prędkość przejścia przy rewind w milisekundach. Domyślnie używa wartości speed.', 'multistore')}
								min={0}
								max={3000}
								step={100}
							/>

							<ToggleControl
								label={addInheritedIndicator(__('Rewind przez przeciąganie', 'multistore'), 'rewindByDrag')}
								checked={getValue('rewindByDrag')}
								onChange={(value) => setValue('rewindByDrag', value)}
								help={__('Umożliwia użytkownikom przewijanie karuzeli przez przeciąganie. Opcja rewind musi być true.', 'multistore')}
							/>
						</>
					)}
				</>
			)}

			<RangeControl
				label={addInheritedIndicator(__('Prędkość przejścia (ms)', 'multistore'), 'speed')}
				value={getValue('speed')}
				onChange={(value) => setValue('speed', value)}
				help={__('Prędkość przejścia w milisekundach. Jeśli 0, karuzela natychmiast przeskakuje do docelowego slajdu.', 'multistore')}
				min={0}
				max={3000}
				step={100}
			/>

			<TextControl
				label={addInheritedIndicator(__('Easing', 'multistore'), 'easing')}
				value={getValue('easing')}
				onChange={(value) => setValue('easing', value)}
				help={__('Funkcja czasowa dla przejścia CSS, np. linear, ease lub cubic-bezier().', 'multistore')}
			/>
		</PanelBody>
	)
}