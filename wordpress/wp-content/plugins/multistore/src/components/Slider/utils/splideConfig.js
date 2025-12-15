/**
 * Build Splide configuration from block attributes with responsive breakpoints
 * @param {Object} attributes - Block attributes containing desktop/tablet/mobile settings
 * @returns {Object} Splide configuration object
 */
export const buildConfig = (attributes) => {
    const { desktop = {}, tablet = {}, mobile = {} } = attributes;

    // Helper to filter out custom attributes that aren't part of Splide options
    const filterSplideOptions = (options) => {
        const {
            arrowsPosition,
            arrowsStyle,
            arrowsClass,
            arrowPath,
            paginationType,
            paginationPosition,
            paginationClass,
            ...splideOptions
        } = options;
        return splideOptions;
    };

    // Start with desktop configuration
    const config = filterSplideOptions(desktop);

    // Build breakpoints object for responsive settings
    const breakpoints = {};

    // Tablet breakpoint (768px)
    if (tablet && Object.keys(tablet).length > 0) {
        breakpoints[768] = filterSplideOptions(tablet);
    }

    // Mobile breakpoint (567px)
    if (mobile && Object.keys(mobile).length > 0) {
        breakpoints[567] = filterSplideOptions(mobile);
    }

    // Add breakpoints to config if they exist
    if (Object.keys(breakpoints).length > 0) {
        config.breakpoints = breakpoints;
    }

    // Clean up config - remove empty strings and convert string numbers
    Object.keys(config).forEach(key => {
        if (config[key] === '' || config[key] === null || config[key] === undefined) {
            delete config[key];
        }

        // Convert numeric strings to numbers for certain properties
        const numericProps = ['width', 'height', 'fixedWidth', 'fixedHeight', 'gap', 'padding'];
        if (numericProps.includes(key) && typeof config[key] === 'string' && config[key] !== '') {
            const num = parseFloat(config[key]);
            if (!isNaN(num)) {
                config[key] = num;
            }
        }
    });

    // Handle conditional options based on type
    if (config.type === 'fade') {
        // Fade type restrictions
        delete config.perPage;
        delete config.perMove;
        delete config.gap;
        delete config.padding;
        delete config.focus;
        delete config.autoWidth;
        delete config.dragFree;
    }

    if (config.type === 'loop') {
        // Loop type restrictions
        delete config.rewind;
        delete config.rewindSpeed;
        delete config.rewindByDrag;
    } else {
        // Only loop type can use clones
        delete config.clones;
    }

    // Handle autoWidth/autoHeight conflicts
    if (config.autoWidth || config.autoHeight) {
        delete config.perPage;
        delete config.perMove;
    }

    // Handle dependent options
    if (!config.rewind) {
        delete config.rewindSpeed;
        delete config.rewindByDrag;
    }

    delete config.dragFree;
    delete config.dragMinThreshold;
    delete config.flickPower;
    delete config.flickMaxPages;
    

    config.drag = false;
    config.autoplay = false;

    // Remove arrows and pagination as they're handled separately in HTML
    delete config.arrows;
    delete config.pagination;

    return config;
};

/**
 * Parse Splide configuration from JSON string (for frontend)
 * @param {string} jsonString - JSON string from data attribute
 * @returns {Object} Parsed Splide configuration
 */
export const parseSplideConfig = (jsonString) => {
    try {
        return JSON.parse(jsonString);
    } catch (e) {
        console.error('Failed to parse Splide config:', e);
        return {};
    }
};
