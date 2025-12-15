import { registerBlockType } from '@wordpress/blocks';

import './filters';

// Default theme
import './style.scss';

import Edit from './edit';
import Save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	save: Save,
} );
