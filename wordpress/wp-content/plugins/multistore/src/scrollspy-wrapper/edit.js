import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

const ALLOWED_BLOCKS = [
    'multistore/scrollspy-nav',
    'multistore/scrollspy-section'
];

const TEMPLATE = [
    [ 'multistore/scrollspy-nav' ],
    [ 'multistore/scrollspy-section', { label: __( 'Sekcja 1', 'multistore' ) } ],
    [ 'multistore/scrollspy-section', { label: __( 'Sekcja 2', 'multistore' ) } ],
];


export default function Edit({ attributes, setAttributes, clientId }) {

    const { wrapperId, scrollOffset, activeClass } = attributes;
    const blockProps = useBlockProps({
        className: 'multisotere-block-scrollspy-wrapper'
    });

    // Generate unique ID on mount
    useEffect(() => {
        if ( ! wrapperId ) {
            setAttributes({ wrapperId: `scrollspy-${clientId}` });
        }
    }, []);

    return (
        <>
            <InspectorControls>
                <PanelBody title={ __( 'Ustawienia ScrollSpy', 'multistore' ) } initialOpen={ true }>
                    <TextControl
                        label={__( 'ID kontenera', 'multistore')}
                        value={ wrapperId }
                        onChange={ ( value ) => setAttributes( { wrapperId: value } ) }
                        help={ __( 'Indywidualny identyfikator kontenera śledzenia', 'multistore' ) }
                    />
                    <RangeControl
                        label={ __( 'Scroll Offset (px)', 'multistore' ) }
                        value={ scrollOffset }
                        onChange={ ( value ) => setAttributes( { scrollOffset: value } ) }
                        min={0}
                        max={300}
                    />
                    <TextControl
                        label={ __( 'Klasa CSS dla aktywnego elementu', 'multistore' ) }
                        value={ activeClass }
                        onChange={ ( value ) => setAttributes( { activeClass: value } ) }
                    />
                </PanelBody>
            </InspectorControls>

            <div { ...blockProps }>
                <InnerBlocks
                    allowedBlocks={ ALLOWED_BLOCKS }
                    template={ TEMPLATE}
                    templateLock={ false }
                />
            </div>
        </>
    );
}