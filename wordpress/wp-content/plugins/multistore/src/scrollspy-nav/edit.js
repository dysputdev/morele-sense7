import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, store as blockEditorStore } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import './editor.scss';

export default function Edit({ attributes, setAttributes, context, clientId }) {
	const { isSticky, stickyTop } = attributes;
	const wrapperId = context['multistore/scrollspy-wrapper-id'];

	// Get parent block ID
	const parentClientId = useSelect(
		( select ) => {
			const { getBlockParents } = select( blockEditorStore );
			const parents = getBlockParents( clientId );
			return parents[ parents.length - 1 ];
		},
		[ clientId ]
	);

	// Get all scrollspy-section blocks from parent
	const sections = useSelect(
		( select ) => {
			if ( ! parentClientId ) {
				return [];
			}

			const { getBlocks } = select( blockEditorStore );
			const parentBlocks = getBlocks( parentClientId );

			return parentBlocks
				.filter( ( block ) => block.name === 'multistore/scrollspy-section' )
				.map( ( block ) => ({
					id: block.attributes.sectionId || `section-${block.clientId}`,
					label: block.attributes.label || __( 'Unnamed Section', 'multistore' ),
				}));
		},
		[ parentClientId ]
	);

	const blockProps = useBlockProps({
		className: `multistore-block-scrollspy-nav ${isSticky ? 'is-sticky' : ''}`,
		style: isSticky ? { top: `${stickyTop}px` } : {}
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Ustawienia nawigacji', 'multistore' ) }>
				<ToggleControl
					label={ __( 'Sticky', 'multistore' ) }
					checked={ isSticky }
					onChange={ ( value ) => setAttributes( { isSticky: value } ) }
				/>
				{isSticky && (
					<RangeControl
						label={ __( 'Pozycja Sticky top (px)', 'multistore' ) }
						value={ stickyTop }
						onChange={ ( value ) => setAttributes( { stickyTop: value } ) }
						min={ 0 }
						max={ 200 }
					/>
				)}
				</PanelBody>
			</InspectorControls>

			<nav { ...blockProps }>
				<ul className="multistore-block-scrollspy-nav__list">
				{sections.length === 0 ? (
					<li className="multistore-block-scrollspy-nav__item multistore-block-scrollspy-nav__placeholder">
						{ __( 'Dodaj sekcje, aby zobaczyć nawigację', 'multistore' ) }
					</li>
				) : (
					sections.map( ( section ) => (
						<li key={section.id} className="multistore-block-scrollspy-nav__item">
							<a 
								href={`#${section.id}`}
								className="multistore-block-scrollspy-nav__link"
							>
								{section.label}
							</a>
						</li>
					))
				)}
				</ul>
			</nav>
		</>
	);
}