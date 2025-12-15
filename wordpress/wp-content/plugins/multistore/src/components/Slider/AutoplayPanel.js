import { __ } from "@wordpress/i18n"
import { PanelBody, ToggleControl, RangeControl } from "@wordpress/components"

export const AutoplayPanel = ({ getValue, setValue, addInheritedIndicator }) => {
	return (
		<PanelBody title={__('Autoplay', 'multistore')} initialOpen={false}>
			<ToggleControl
				label={addInheritedIndicator(__('Włącz autoplay', 'multistore'), 'autoplay')}
				checked={getValue('autoplay')}
				onChange={(value) => setValue('autoplay', value)}
				help={__('Określa, czy włączyć autoplay.', 'multistore')}
			/>

			{getValue('autoplay') && (
				<>
					<RangeControl
						label={addInheritedIndicator(__('Interwał (ms)', 'multistore'), 'interval')}
						value={getValue('interval')}
						onChange={(value) => setValue('interval', value)}
						help={__('Czas trwania interwału autoplay w milisekundach.', 'multistore')}
						min={1000}
						max={10000}
						step={500}
					/>

					<ToggleControl
						label={addInheritedIndicator(__('Pauza po najechaniu', 'multistore'), 'pauseOnHover')}
						checked={getValue('pauseOnHover')}
						onChange={(value) => setValue('pauseOnHover', value)}
						help={__('Określa, czy wstrzymać autoplay po najechaniu myszą. To powinno być true dla dostępności.', 'multistore')}
					/>

					<ToggleControl
						label={addInheritedIndicator(__('Pauza przy focus', 'multistore'), 'pauseOnFocus')}
						checked={getValue('pauseOnFocus')}
						onChange={(value) => setValue('pauseOnFocus', value)}
						help={__('Czy wstrzymać autoplay gdy karuzela zawiera aktywny element. To powinno być true dla dostępności.', 'multistore')}
					/>

					<ToggleControl
						label={addInheritedIndicator(__('Reset postępu', 'multistore'), 'resetProgress')}
						checked={getValue('resetProgress')}
						onChange={(value) => setValue('resetProgress', value)}
						help={__('Określa, czy resetować postęp autoplay gdy zostaje ponownie uruchomione.', 'multistore')}
					/>
				</>
			)}
		</PanelBody>
	)
}