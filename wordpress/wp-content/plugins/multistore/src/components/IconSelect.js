import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { InlineSvg } from './InlineSvg';
import { useBlockProps } from '@wordpress/block-editor';
import { SelectControl } from '@wordpress/components';
import { RangeControl } from '@wordpress/components';

import './IconSelect.scss';

export const IconSizeMap = {
	'x-small'   : 16,
	'small'     : 24,
	'medium'    : 32,
	'default'   : 48,
	'large'     : 56,
	'x-large'   : 64,
	'xx-large'  : 72,
	'xxx-large' : 92,
	'xxxx-large': 128,
}

const getIconsList = async () => {
	try {
		const formData = new FormData();
		formData.append('action', 'get_icon_list');
		formData.append('nonce', window.MultiStoreData?.nonce || '');

		const response = await fetch(window.MultiStoreData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: formData
		});

		const data = await response.json();
		
		if (data.success) {
			window.iconList = data.data;
			return data.data;
		} else {
			console.error('Błąd pobierania ikon:', data);
			return [];
		}
	} catch (error) {
		console.error('Błąd pobierania ikon:', error);
		// Fallback - przykładowe ikony
		return [];
	}
};

export const IconSelectGallery = ({ selectedPack, selectedIcon, onIconSelect }) => {
	const [icons, setIcons] = useState([]);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		const loadIcons = async () => {
			const iconsList = await getIconsList();
			setIcons(iconsList);
			setLoading(false);
		};

		if (!window.iconList) {
			loadIcons();
		} else {
			setIcons(window.iconList);
			setLoading(false);
		}
	}, []);

	if (loading) {
		return (
			<div className="icon-gallery">
				<h4>{ __( 'Wybierz ikonę:', 'multistore' ) } </h4>
				<p>{ __( 'Ładowanie ikon...', 'multistore' ) }</p>
			</div>
		);
	}

	if (!icons[selectedPack] || !Array.isArray(icons[selectedPack])) {
		return (
			<div className="icon-gallery">
				<h4>{ __( 'Wybierz ikonę:', 'multistore' ) }</h4>
				<p>{ __( 'Brak dostępnych ikon dla wybranego pakietu.', 'multistore' ) }</p>
			</div>
		);
	}

	return (
		<div className="icon-gallery">
			<h4>{ __( 'Wybierz ikonę:', 'multistore' ) }</h4>
			<div className="icon-grid">
				{icons[selectedPack].map((iconName) => (
					<Button
						key={iconName}
						className={`icon-item ${selectedIcon === iconName ? 'selected' : ''}`}
						onClick={() => onIconSelect(iconName)}
						variant={selectedIcon === iconName ? 'primary' : 'secondary'}
					>
						<div className="icon-preview">
							<InlineSvg
								src={`${window.MultiStoreData?.iconsUrl}${selectedPack}/${iconName}`}
								alt={iconName}
								className="icon-svg"
							/>
						</div>
						<span className="icon-name">{iconName.replace('.svg', '')}</span>
					</Button>
				))}
			</div>
		</div>
	);
}

export const IconSelectSettings = ({attributes, setAttributes}) => {

	const { pack, file, size, customSize, position } = attributes;

	const onPackSelect = (selectedPack) => {
		setAttributes({ pack: selectedPack });
	};
	
	const onIconSelect = (selectedIcon) => {
		setAttributes({ file: selectedIcon });
	};

	const onSizeSelect = (selectedSize) => {
		setAttributes({ size: selectedSize });
	};

	const onCustomSizeSelect = (selectedSize) => {
		setAttributes({ customSize: selectedSize });
	};
	
	return (
		<>
			<SelectControl
				label={ __( 'Pakiet ikon', 'multistore' ) }
				value={ pack }
				options={[
					{ value: 'default', label: 'Domyślne' },
				]}
				onChange={ onPackSelect }
			/>
			<IconSelectGallery 
				selectedPack={ pack }
				selectedIcon={ file }
				onIconSelect={ onIconSelect }
			/>
			{file && (
				<div className="selected-icon-info">
					<p><strong>{ __( 'Wybrana ikona:', 'multistore' ) }</strong> { file }</p>
					<Button 
						variant="secondary" 
						onClick={ () => onIconSelect('') }
					>
						{ __( 'Usuń wybór', 'multistore' ) }
					</Button>
				</div>
			) }
			<SelectControl
				label={ __( 'Rozmiar ikony', 'multistore' ) }
				value={ size }
				options={[
					// generate from sizeMap
					...Object.keys(IconSizeMap).map((key) => {
						return { value: key, label: IconSizeMap[key] + 'px' };
					}),
					{ value: 'custom', label: __( 'Niestandardowy', 'multistore' ) },
				]}
				onChange={ onSizeSelect }
			/>

			{size === 'custom' && (
				<RangeControl
					label={ __( 'Wysokosc ikony', 'multistore' ) }
					value={ customSize }
					onChange={ onCustomSizeSelect }
					min={8}
					max={256}
				/>
			) }

			{/* <SelectControl
				label={ __( 'Pozycja ikony', 'multistore' ) }
				value={ position }
				options={[
					{ value: 'left', label: __( 'Lewa', 'multistore' ) },
					{ value: 'center', label: __( 'Środek', 'multistore' ) },
					{ value: 'right', label: __( 'Prawa', 'multistore' ) },
				]}
				onChange={(value) => setAttributes({ position: value }) }
			/> */}
		</>
	)
}

export const IconSelectView = ({ pack, file, size, customSize }) => {
	const blockProps = useBlockProps(
		{
			// className: position ? 'has-icon-' + position : '',
		}
	);

	return (
		<>
		{file ? (
			<figure>
				<InlineSvg
					src={`${window.MultiStoreData?.iconsUrl}${pack}/${file}`}
					alt={file.replace('.svg', '') }
					className="selected-icon"
					style={{
						width: size === 'custom' ? customSize + 'px' : IconSizeMap[ size ] + 'px',
						height: size === 'custom' ? customSize + 'px' : IconSizeMap[ size ] + 'px',
					}}
				/>
			</figure>
		) : (
			<div className="icon-placeholder">
				<p>{ __( 'Wybierz ikonę z panelu ustawień', 'multistore' ) }</p>
			</div>
		) }
		</>
	);
}