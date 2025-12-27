import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';
import { SelectControl } from '@wordpress/components';


export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps({
		className: 'multistore-block-product-reviews',
	});

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Ustawienia', 'multistore' ) }>
				<ToggleControl
					label={ __( 'Wyswietlaj oceny', 'multistore' ) }
					checked={ attributes.displaySummary }
					onChange={ ( value ) => setAttributes( { displaySummary: value } ) }
				/>

				<ToggleControl
					label={ __( 'Wyświetlaj oceny ze wszystkich sklepów', 'multistore' ) }
					checked={ attributes.displayAllStores }
					onChange={ ( value ) => setAttributes( { displayAllStores: value } ) }
				/>

				<RangeControl
					label={ __( 'Ilość ocen na stronie', 'multistore' ) }
					value={ attributes.perPage }
					onChange={ ( value ) => setAttributes( { perPage: value } ) }
					min={ 1 }
					max={ 10 }
				/>

				<RangeControl
					label={ __( 'Wczytanych ocen', 'multistore' ) }
					value={ attributes.loadedReviews }
					onChange={ ( value ) => setAttributes( { loadedReviews: value } ) }
					min={ 1 }
					max={ 10 }
				/>

				<SelectControl
					label={ __( 'Sortowanie', 'multistore' ) }
					value={ attributes.sort }
					options={[
						{ value: 'newest', label: __( 'Najnowsze', 'multistore' ) },
						{ value: 'oldest', label: __( 'Najstarsze', 'multistore' ) },
						{ value: 'best', label: __( 'Najlepsze', 'multistore' ) },
						{ value: 'worst', label: __( 'Najgorsze', 'multistore' ) },
						{ value: 'random', label: __( 'Losowe', 'multistore' ) },
					]}
					onChange={ ( value ) => setAttributes( { sort: value } ) }
				/>
			</PanelBody>
		</InspectorControls>
		
		<div { ...blockProps }>
			<p>Product Reviews Block - Edit View: TODO</p>
		</div>
	</>
}