import { registerBlockType } from '@wordpress/blocks';

// Splide CSS
import '@splidejs/splide/css';

import './style.scss';

import Edit from './edit';
import Save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	save: Save,
} );
