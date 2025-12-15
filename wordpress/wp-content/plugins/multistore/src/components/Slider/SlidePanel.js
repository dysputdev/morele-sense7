import { __ } from '@wordpress/i18n';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components'

export const SlidePanel = ({ getValue, setValue, addInheritedIndicator }) => {
	return (
		<PanelBody title={__('Slajdy i nawigacja', 'multistore')} initialOpen={false}>
			<RangeControl
				label={addInheritedIndicator(__('Początkowy slajd', 'multistore'), 'start')}
				value={getValue('start')}
				onChange={(value) => setValue('start', value)}
				help={__('Definiuje indeks początkowy.', 'multistore')}
				min={0}
				max={20}
			/>

			{getValue('type') !== 'fade' && !getValue('autoWidth') && !getValue('autoHeight') && (
				<>
					<RangeControl
						label={addInheritedIndicator(__('Slajdów na stronę', 'multistore'), 'perPage')}
						value={getValue('perPage')}
						onChange={(value) => setValue('perPage', value)}
						help={__('Określa liczbę slajdów wyświetlanych na stronie. Wartość musi być liczbą całkowitą.', 'multistore')}
						min={1}
						max={10}
					/>

					<RangeControl
						label={addInheritedIndicator(__('Przesunięcie na raz', 'multistore'), 'perMove')}
						value={getValue('perMove')}
						onChange={(value) => setValue('perMove', value)}
						help={__('Określa liczbę slajdów do przesunięcia na raz. Wartość musi być liczbą całkowitą.', 'multistore')}
						min={0}
						max={10}
					/>
				</>
			)}

			{getValue('type') === 'loop' && (
				<RangeControl
					label={addInheritedIndicator(__('Liczba klonów', 'multistore'), 'clones')}
					value={getValue('clones')}
					onChange={(value) => setValue('clones', value)}
					help={__('Określa liczbę klonów po każdej stronie karuzeli loop.', 'multistore')}
					min={0}
					max={20}
				/>
			)}

			{getValue('type') !== 'fade' && (
				<>
					<TextControl
						label={addInheritedIndicator(__('Focus', 'multistore'), 'focus')}
						value={getValue('focus')}
						onChange={(value) => setValue('focus', value)}
						help={__('Określa, który slajd powinien być aktywny gdy karuzela ma wiele slajdów na stronie.', 'multistore')}
						placeholder="0"
					/>

					<TextControl
						label={addInheritedIndicator(__('Odstęp (gap)', 'multistore'), 'gap')}
						value={getValue('gap')}
						onChange={(value) => setValue('gap', value)}
						help={__('Odstęp między slajdami. Akceptowany jest format CSS, np. 1em.', 'multistore')}
						placeholder="0"
					/>

					<TextControl
						label={addInheritedIndicator(__('Padding', 'multistore'), 'padding')}
						value={getValue('padding')}
						onChange={(value) => setValue('padding', value)}
						help={__('Ustawia padding lewo/prawo dla poziomej lub góra/dół dla pionowej karuzeli. Format CSS akceptowany.', 'multistore')}
						placeholder="0"
					/>
				</>
			)}
		</PanelBody>
	)
}
