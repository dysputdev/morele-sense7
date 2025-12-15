import { __ } from '@wordpress/i18n';
import { BlockControls, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button, ToolbarButton, ToolbarGroup } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { useEffect, useRef } from '@wordpress/element';
import { Icon } from '@wordpress/components';

/**
 * Dodaje atrybut megamenu_id do bloku core/navigation-submenu
 */
export const addMegamenuAttribute = (settings, name) => {
	if (name !== 'core/navigation-submenu') {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			megamenuId: {
				type: 'string',
				default: '',
			},
		},
	};
}

/**
 * Blokuje możliwość dodawania bloków w submenu gdy megamenu jest połączone
 */
export const blockInnerBlocksWhenMegamenuConnected = (BlockListBlock) => {
	return (props) => {
		const { name, attributes } = props;

		if (name !== 'core/navigation-submenu') {
			return <BlockListBlock {...props} />;
		}

		const megamenuExists = useSelect((select) => {
			if (!attributes.megamenuId) {
				return false;
			}

			const megamenuBlocks = select('core/block-editor').getBlocksByName('multistore/megamenu');

			for (const blockId of megamenuBlocks) {
				const block = select('core/block-editor').getBlock(blockId);
				if (block && block.attributes.anchor === attributes.megamenuId) {
					return true;
				}
			}
			return false;
		}, [attributes.megamenuId]);

		// Jeśli megamenu jest połączone, zablokuj możliwość dodawania bloków
		const modifiedProps = {
			...props,
			...(megamenuExists && {
				__unstableInnerBlocks: {
					...props.__unstableInnerBlocks,
					allowedBlocks: [], // Pusta tablica = brak dozwolonych bloków
				},
			}),
		};

		return <BlockListBlock {...modifiedProps} />;
	};
};

/**
 * Rozszerza kontrolki bloku core/navigation
 */
export const withMegamenuControls = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		const { name, attributes, setAttributes, clientId } = props;

		if (name !== 'core/navigation-submenu') {
			return <BlockEdit {...props} />;
		}

		const { insertBlocks, updateBlockAttributes, removeBlocks } = useDispatch('core/block-editor');
		const prevMegamenuId = useRef(attributes.megamenuId);

		// Sprawdź czy istnieje blok megamenu z anchor pasującym do megamenuId
		const megamenuExists = useSelect((select) => {
			if (!attributes.megamenuId) {
				return false;
			}

			const findMegamenu = ( blocks ) => {
				for (const blockId of blocks ) {
					let block = select('core/block-editor').getBlock(blockId);
					if ( block.attributes.anchor === attributes.megamenuId ) {
						return true;
					}
				}
				return false;
			};

			const megamenuBlocks = select('core/block-editor').getBlocksByName('multistore/megamenu');

			return findMegamenu( megamenuBlocks );
		}, [attributes.megamenuId]);

		// Znajdź najbliższy parent core/group
		const closestGroupId = useSelect((select) => {
			const parents = select('core/block-editor').getBlockParents(clientId);
			const getBlock = select('core/block-editor').getBlock;

			// Szukaj od najbliższego parenta w górę
			for (let i = parents.length - 1; i >= 0; i--) {
				const parentBlock = getBlock(parents[i]);
				if (parentBlock && parentBlock.name === 'core/group') {
					return parents[i];
				}
			}

			return null;
		}, [clientId]);

		const createMegamenu = () => {
			if (!closestGroupId) {
				console.error('Nie znaleziono bloku core/group');
				return;
			}

			// Generuj unikalne ID
			const uniqueId = `megamenu-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

			// Utwórz nowy blok megamenu z anchor
			const megamenuBlock = createBlock('multistore/megamenu', {
				anchor: uniqueId,
			});

			// Wstaw blok na końcu core/group (undefined = na końcu)
			insertBlocks(megamenuBlock, undefined, closestGroupId);

			// Ustaw megamenuId w submenu
			setAttributes({ megamenuId: uniqueId });
		};

		// Synchronizacja: zmiana megamenuId w submenu -> zmiana anchor w megamenu
		useEffect(() => {
			const newMegamenuId = attributes.megamenuId;
			const oldMegamenuId = prevMegamenuId.current;

			// Jeśli megamenuId się zmienił i nie jest to pierwsza inicjalizacja
			if (newMegamenuId !== oldMegamenuId && oldMegamenuId !== undefined) {
				const megamenuBlocks = wp.data.select('core/block-editor').getBlocksByName('multistore/megamenu');

				// Znajdź blok megamenu ze starym anchor
				for (const blockId of megamenuBlocks) {
					const block = wp.data.select('core/block-editor').getBlock(blockId);
					if (block && block.attributes.anchor === oldMegamenuId) {
						// Zaktualizuj anchor w megamenu
						updateBlockAttributes(blockId, { anchor: newMegamenuId });
						break;
					}
				}
			}

			prevMegamenuId.current = newMegamenuId;
		}, [attributes.megamenuId, updateBlockAttributes]);

		// Usuń innerBlocks z submenu gdy megamenu jest połączone
		useEffect(() => {
			if (megamenuExists) {
				const submenuBlock = wp.data.select('core/block-editor').getBlock(clientId);
				if (submenuBlock && submenuBlock.innerBlocks && submenuBlock.innerBlocks.length > 0) {
					const innerBlockIds = submenuBlock.innerBlocks.map(block => block.clientId);
					removeBlocks(innerBlockIds, false);
				}
			}
		}, [megamenuExists, clientId, removeBlocks]);

		return (
			<>
				<BlockEdit { ...props } />
				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton
							onClick={ createMegamenu }
							disabled={ megamenuExists }
						>
							<Icon icon={ megamenuExists ? 'published' : 'plusCircle' }
								/>
							{__('Megamenu', 'multistore')}
						</ToolbarButton>
					</ToolbarGroup>
				</BlockControls>
				<InspectorControls>
					<PanelBody
						title={ __('Megamenu Settings', 'multistore') }
						initialOpen={ true }
					>
						{/* <TextControl
							label={ __('Megamenu Block ID', 'multistore') }
							value={ attributes.megamenuId }
							onChange={ (value) => setAttributes({ megamenuId: value }) }
							help={ __('Wprowadź ID bloku megamenu', 'multistore') }
						/> */}

						<Button
							variant="primary"
							onClick={ createMegamenu }
							disabled={ megamenuExists }
						>
							{ __('Utwórz Megamenu', 'multistore') }
						</Button>

						{megamenuExists && (
							<p style={{ color: '#00a32a', marginTop: '8px' }}>
								✓ { __('Megamenu jest podłączone', 'multistore') }
							</p>
						)}
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'withMegamenuControls');

/**
 * Synchronizuje zmiany anchor w bloku megamenu z megamenuId w submenu
 */
export const withMegamenuSync = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		const { name, attributes, clientId } = props;

		if (name !== 'multistore/megamenu') {
			return <BlockEdit {...props} />;
		}

		const { updateBlockAttributes } = useDispatch('core/block-editor');
		const prevAnchor = useRef(attributes.anchor);

		// Synchronizacja: zmiana anchor w megamenu -> zmiana megamenuId w submenu
		useEffect(() => {
			const newAnchor = attributes.anchor;
			const oldAnchor = prevAnchor.current;

			// Jeśli anchor się zmienił i nie jest to pierwsza inicjalizacja
			if (newAnchor !== oldAnchor && oldAnchor !== undefined && oldAnchor !== '') {
				const submenuBlocks = wp.data.select('core/block-editor').getBlocksByName('core/navigation-submenu');

				// Znajdź wszystkie bloki submenu ze starym megamenuId
				for (const blockId of submenuBlocks) {
					const block = wp.data.select('core/block-editor').getBlock(blockId);
					if (block && block.attributes.megamenuId === oldAnchor) {
						// Zaktualizuj megamenuId w submenu
						updateBlockAttributes(blockId, { megamenuId: newAnchor });
					}
				}
			}

			prevAnchor.current = newAnchor;
		}, [attributes.anchor, updateBlockAttributes]);

		return <BlockEdit {...props} />;
	};
}, 'withMegamenuSync');


addFilter(
	'blocks.registerBlockType',
	'create-block/megamenu/add-attribute',
	addMegamenuAttribute
);

addFilter(
	'editor.BlockEdit',
	'create-block/megamenu/with-megamenu-controls',
	withMegamenuControls
);

addFilter(
	'editor.BlockEdit',
	'create-block/megamenu/with-megamenu-sync',
	withMegamenuSync
);

addFilter(
	'editor.BlockListBlock',
	'create-block/megamenu/block-inner-blocks-when-connected',
	blockInnerBlocksWhenMegamenuConnected
);
