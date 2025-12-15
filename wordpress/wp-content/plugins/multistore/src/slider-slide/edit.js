import { __ } from "@wordpress/i18n";
import { InnerBlocks, InspectorControls, useBlockProps } from "@wordpress/block-editor";

export default function Edit({ attributes, setAttributes }) {

    const blockProps = useBlockProps({
        className: 'splide__slide multistore-block-slider-slide',
    });

    return (
        <>
            <InspectorControls>
            </InspectorControls>
            <li {...blockProps} aria-label="slide-item">
                <InnerBlocks />
            </li>
        </>
    )
}
