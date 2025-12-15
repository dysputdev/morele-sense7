import { BasicPanel } from './Slider/BasicPanel';
import { SizePanel } from './Slider/SizePanel';
import { SlidePanel } from './Slider/SlidePanel';
import { ArrowPanel } from './Slider/ArrowPanel';
import { NavigationPanel } from './Slider/NavigationPanel';
import { LazyloadingPanel } from './Slider/Lazyloading';
import { AutoplayPanel } from './Slider/AutoplayPanel';
import { OtherPanel } from './Slider/OtherPanel';
import { DragAndDropPanel } from './Slider/DragAndDropPanel';
import { buildConfig } from './Slider/utils/splideConfig';

export {
    BasicPanel,
    SizePanel,
    SlidePanel,
    ArrowPanel,
    NavigationPanel,
    LazyloadingPanel,
    AutoplayPanel,
    OtherPanel,
    DragAndDropPanel,
    buildConfig
}

export function SliderControllPanel( { device, attributes, setAttributes } ) {

    const isDesktop = device === 'desktop';
    const isTablet = device === 'tablet';
    const isMobile = device === 'mobile';

    // Helper function to get value with inheritance
    const getValue = (key) => {
        if (isMobile) {
            // Mobile inherits from tablet, which inherits from desktop
            return attributes.mobile[key] !== undefined && attributes.mobile[key] !== ''
                ? attributes.mobile[key]
                : (attributes.tablet[key] !== undefined && attributes.tablet[key] !== ''
                    ? attributes.tablet[key]
                    : attributes.desktop[key]);
        } else if (isTablet) {
            // Tablet inherits from desktop
            return attributes.tablet[key] !== undefined && attributes.tablet[key] !== ''
                ? attributes.tablet[key]
                : attributes.desktop[key];
        }
        // Desktop uses its own values
        return attributes.desktop[key];
    };

    // Helper function to set value for current device
    const setValue = (key, value) => {
        setAttributes({
            [device]: {
                ...attributes[device],
                [key]: value
            }
        });
    };

    // Helper function to check if value is inherited
    const isInherited = (key) => {
        if (isDesktop) return false;
        return attributes[device][key] === undefined || attributes[device][key] === '';
    };

    // Helper function to add blue dot for inherited values
    const addInheritedIndicator = (label, key) => {
        if (isInherited(key)) {
            return (
                <>
                    {label} <span style={{color: '#2271b1', fontSize: '1.2em'}}>•</span>
                </>
            );
        }
        return label;
    };

	return (
		<>
			<BasicPanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
			<SizePanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
			<SlidePanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
			<ArrowPanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
			<NavigationPanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
			<DragAndDropPanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
			<AutoplayPanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
			<LazyloadingPanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
			<OtherPanel
				getValue={ getValue }
				setValue={ setValue }
				addInheritedIndicator={ addInheritedIndicator }
				/>
		</>
	)
}

export const SliderTrack = ({ attributes, innerBlocksProps }) => {
    return (
        <div className="splide__track">
            <ul {...innerBlocksProps} />
        </div>
    )
}

export const SliderPagination = ({ attributes }) => {
    const { desktop, tablet, mobile } = attributes;

    // Get effective values with inheritance
    const getPaginationValue = (key) => {
        if (mobile?.[key]) return mobile[key];
        if (tablet?.[key]) return tablet[key];
        return desktop?.[key];
    };

    const showPagination     = getPaginationValue('pagination');
    const paginationType     = getPaginationValue('paginationType') || 'bullets';
    const paginationPosition = getPaginationValue('paginationPosition') || 'bottom-outside';
    const paginationClass    = getPaginationValue('paginationClass') || '';

    if (!showPagination) return null;

    const paginationClasses = `splide__pagination splide__pagination--${paginationType} splide__pagination--${paginationPosition}${paginationClass ? ' ' + paginationClass : ''}`;

    return (
        <ul className={paginationClasses}></ul>
    )
}

export const SliderArrows = ({ attributes }) => {
    const { desktop, tablet, mobile } = attributes;

    // Get effective values with inheritance
    const getArrowsValue = (key) => {
        if (mobile?.[key]) return mobile[key];
        if (tablet?.[key]) return tablet[key];
        return desktop?.[key];
    };

    const showArrows     = getArrowsValue('arrows');
    const arrowsPosition = getArrowsValue('arrowsPosition') || 'center-inside';
    const arrowsClass    = getArrowsValue('arrowsClass') || '';
    const arrowPath      = getArrowsValue('arrowPath') || 'm15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z';

    if (!showArrows) return null;

    const arrowsClasses = `splide__arrows splide__arrows--${arrowsPosition}${arrowsClass ? ' ' + arrowsClass : ''}`;

    return (
        <div className={arrowsClasses}>
            <button className="splide__arrow splide__arrow--prev">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40">
                    <path d={arrowPath}></path>
                </svg>
            </button>
            <button className="splide__arrow splide__arrow--next">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40">
                    <path d={arrowPath}></path>
                </svg>
            </button>
        </div>
    )
}

export const SliderProgressBar = ({ attributes }) => {
    return (
        <div class="splide__progress">
            <div class="splide__progress__bar"></div>
        </div>
    )
}
