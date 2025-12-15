import { registerBlockType } from '@wordpress/blocks';

import './style.scss';

import Edit from './edit';
import Save from './save';
import metadata from './block.json';

import { addMegamenuAttribute, withMegamenuControls } from './filters.js';

registerBlockType( metadata.name, {
	edit: Edit,
	save: Save,
} );
