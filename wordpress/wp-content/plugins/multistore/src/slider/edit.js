import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks, useInnerBlocksProps, InspectorControls } from '@wordpress/block-editor';
import { TabPanel } from '@wordpress/components';
import { useEffect, useRef } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

import Splide from '@splidejs/splide';
import './editor.scss';

import { tabs } from '../../src/components/ResponsiveTabs';
import {
    buildConfig,
    SliderArrows,
    SliderPagination,
    SliderProgressBar,
    SliderTrack,
    SliderControllPanel
} from '../components/Slider';

export default function Edit({ attributes, setAttributes, clientId }) {

    // Get dispatch function to select blocks
    const sliderRef = useRef(null);
    const splideInstance = useRef(null);
    const navItemsRef = useRef([]);

    const blockProps = useBlockProps({
        className: 'multistore-block-slider',
        'aria-label': 'slider',
    });

    const innerBlocks = useSelect(
        (select) => select('core/block-editor').getBlock(clientId)?.innerBlocks || [],
        [clientId]
    );

    const innerBlocksProps = useInnerBlocksProps({
        className: 'splide__list',
    }, {
        allowedBlocks: ['multistore/slider-slide'],
        template: [
            ['multistore/slider-slide'],
        ],
        renderAppender: InnerBlocks.ButtonBlockAppender,
    });
    
    const { selectBlock } = useDispatch('core/block-editor');

    useEffect(() => {
        // Destroy existing instance
        if (splideInstance.current) {
            splideInstance.current.destroy();
            splideInstance.current = null;
        }

        // Don't initialize if no slides
        if (!sliderRef.current || innerBlocks.length === 0) {
            return;
        }

        // Small delay to ensure DOM is ready
        const timer = setTimeout(() => {
            const sliderElement = sliderRef.current.querySelector('.splide');
            if (!sliderElement) {
                return;
            }

            // Build splide config from block attributes with responsive breakpoints
            const splideConfig = buildConfig(attributes);

            splideInstance.current = new Splide(sliderElement, splideConfig).mount();

        }, 100)

        // Select the block in the editor
        return () => {
            clearTimeout(timer);
            if (splideInstance.current) {
                splideInstance.current.destroy();
                splideInstance.current = null;
            }
            // Clear nav items
            navItemsRef.current = [];
        };

    }, [innerBlocks.length, innerBlocks, clientId, attributes]);

    return (
        <div {...blockProps} ref={ sliderRef }>
            <InspectorControls>
                <TabPanel
                    className="responsive-tab-panel"
                    activeClass="is-active"
                    tabs={ tabs }
                >
                    {(tab) => <SliderControllPanel
                        device={ tab.name }
                        attributes={ attributes }
                        setAttributes={ setAttributes }
                    />}
                </TabPanel>
            </InspectorControls>
            
            <div className="splide">
                <SliderArrows attributes={ attributes } />
                <SliderPagination attributes={ attributes } />
                <SliderProgressBar attributes={ attributes } />
                <SliderTrack
                    attributes={ attributes }
                    innerBlocksProps={ innerBlocksProps }
                    />
            </div>
        </div>
    )
}
