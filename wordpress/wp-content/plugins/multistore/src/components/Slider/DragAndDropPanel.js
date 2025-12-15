import { __ } from '@wordpress/i18n';
import { PanelBody, RangeControl, ToggleControl } from "@wordpress/components"

export const DragAndDropPanel = ({ getValue, setValue, addInheritedIndicator }) => {	
	return (
		<PanelBody title={__('Przeciąganie i interakcja', 'multistore')} initialOpen={false}>
			<ToggleControl
				label={addInheritedIndicator(__('Włącz przeciąganie', 'multistore'), 'drag')}
				checked={getValue('drag')}
				onChange={(value) => setValue('drag', value)}
				help={__('Określa, czy pozwolić użytkownikowi przeciągać karuzelę.', 'multistore')}
			/>

			{getValue('drag') && (
				<>
					{getValue('type') !== 'fade' && (
						<ToggleControl
							label={addInheritedIndicator(__('Swobodne przeciąganie', 'multistore'), 'dragFree')}
							help={__('Slider porusza się swobodnie bez przyciągania. Nie działa z fade.', 'multistore')}
							checked={getValue('dragFree')}
							onChange={(value) => setValue('dragFree', value)}
						/>
					)}

					<RangeControl
						label={addInheritedIndicator(__('Min. threshold przeciągania', 'multistore'), 'dragMinThreshold')}
						value={getValue('dragMinThreshold')}
						onChange={(value) => setValue('dragMinThreshold', value)}
						help={__('Wymagana odległość do rozpoczęcia przesuwania karuzeli przez dotyk.', 'multistore')}
						min={0}
						max={100}
					/>

					<RangeControl
						label={addInheritedIndicator(__('Siła flick', 'multistore'), 'flickPower')}
						value={getValue('flickPower')}
						onChange={(value) => setValue('flickPower', value)}
						help={__('Określa siłę "flick". Im większa liczba, tym dalej przesuwa się karuzela. Zalecane ~500.', 'multistore')}
						min={0}
						max={2000}
						step={50}
					/>

					<RangeControl
						label={addInheritedIndicator(__('Max stron flick', 'multistore'), 'flickMaxPages')}
						value={getValue('flickMaxPages')}
						onChange={(value) => setValue('flickMaxPages', value)}
						help={__('Ogranicza liczbę stron do przesunięcia akcją flick.', 'multistore')}
						min={1}
						max={10}
					/>
				</>
			)}

			<ToggleControl
				label={addInheritedIndicator(__('Czekaj na zakończenie', 'multistore'), 'waitForTransition')}
				checked={getValue('waitForTransition')}
				onChange={(value) => setValue('waitForTransition', value)}
				help={__('Czy wyłączyć akcje podczas przejścia karuzeli. Nawet jeśli false, karuzela czeka na punktach pętli.', 'multistore')}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Klawiatura', 'multistore'), 'keyboard')}
				checked={getValue('keyboard')}
				onChange={(value) => setValue('keyboard', value)}
				help={__('Włącza niestandardowe skróty klawiaturowe.', 'multistore')}
			/>

			<ToggleControl
				label={addInheritedIndicator(__('Kółko myszy', 'multistore'), 'wheel')}
				checked={getValue('wheel')}
				onChange={(value) => setValue('wheel', value)}
				help={__('Włącza nawigację kółkiem myszy. Ustaw waitForTransition na true i/lub podaj wheelSleep.', 'multistore')}
			/>

			{getValue('wheel') && (
				<>
					<RangeControl
						label={addInheritedIndicator(__('Wheel sleep (ms)', 'multistore'), 'wheelSleep')}
						value={getValue('wheelSleep')}
						onChange={(value) => setValue('wheelSleep', value)}
						help={__('Czas uśpienia w ms do zaakceptowania następnego ruchu kółka. Timer startuje gdy rozpoczyna się przejście.', 'multistore')}
						min={0}
						max={5000}
						step={100}
					/>

					<ToggleControl
						label={addInheritedIndicator(__('Release wheel', 'multistore'), 'releaseWheel')}
						checked={getValue('releaseWheel')}
						onChange={(value) => setValue('releaseWheel', value)}
						help={__('Czy zwolnić zdarzenie kółka gdy karuzela osiągnie pierwszy lub ostatni slajd, aby kontynuować scroll strony.', 'multistore')}
					/>
				</>
			)}
		</PanelBody>
	)
}