import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import metadata from './block.json';
import { image as icon } from '@wordpress/icons';

registerBlockType(metadata.name, {
    icon,
    edit: Edit,
});