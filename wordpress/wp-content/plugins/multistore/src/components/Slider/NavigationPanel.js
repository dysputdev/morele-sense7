import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components'

export const NavigationPanel = ({ getValue, setValue, addInheritedIndicator }) => {
	
	return (
		<PanelBody title={__('Paginacja', 'multistore')} initialOpen={false}>
			<ToggleControl
				label={addInheritedIndicator(__('Pokaż paginację', 'multistore'), 'pagination')}
				checked={getValue('pagination')}
				onChange={(value) => setValue('pagination', value)}
				help={__('Określa, czy tworzyć paginację (kropki wskaźnika). Musi być false jeśli używasz isNavigation.', 'multistore')}
			/>

			{getValue('pagination') && (
				<>
					<SelectControl
						label={addInheritedIndicator(__('Typ paginacji', 'multistore'), 'paginationType')}
						value={getValue('paginationType')}
						onChange={(value) => setValue('paginationType', value)}
						help={__('Określa typ wskaźników paginacji.', 'multistore')}
						options={[
							{ label: __('Punktory/Kropki', 'multistore'), value: 'bullets' },
							{ label: __('Numeracja', 'multistore'), value: 'numbers' },
							{ label: __('Kreski', 'multistore'), value: 'lines' },
							{ label: __('Miniatury', 'multistore'), value: 'thumbnails' }
						]}
					/>

					<SelectControl
						label={addInheritedIndicator(__('Pozycja paginacji', 'multistore'), 'paginationPosition')}
						value={getValue('paginationPosition')}
						onChange={(value) => setValue('paginationPosition', value)}
						help={__('Określa pozycję paginacji.', 'multistore')}
						options={[
							{ label: __('Na dole - pod sliderem', 'multistore'), value: 'bottom-outside' },
							{ label: __('Na dole - wewnątrz slidera', 'multistore'), value: 'bottom-inside' },
							{ label: __('Na górze - nad sliderem', 'multistore'), value: 'top-outside' },
							{ label: __('Na górze - wewnątrz slidera', 'multistore'), value: 'top-inside' }
						]}
					/>

					<TextControl
						label={addInheritedIndicator(__('Dodatkowa klasa CSS', 'multistore'), 'paginationClass')}
						value={getValue('paginationClass')}
						onChange={(value) => setValue('paginationClass', value)}
						help={__('Niestandardowe klasy CSS do dodania do kontenera paginacji.', 'multistore')}
						placeholder="my-custom-pagination"
					/>
				</>
			)}
		</PanelBody>
	)
}
