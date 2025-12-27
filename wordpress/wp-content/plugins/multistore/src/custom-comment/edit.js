import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { authorName, rating, productName, timeAgo, content } = attributes;

	const blockProps = useBlockProps({
		className: 'multistore-block-custom-comment',
	});

	// Generate stars based on rating
	const renderStars = () => {
		const stars = [];
		for (let i = 1; i <= 5; i++) {
			stars.push(
				<span
					key={i}
					className={`multistore-block-custom-comment__star ${
						i <= rating ? 'multistore-block-custom-comment__star--filled' : ''
					}`}
				>
					★
				</span>
			);
		}
		return stars;
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Ustawienia komentarza', 'multistore')} initialOpen={true}>
					<TextControl
						label={__('Imię autora', 'multistore')}
						value={authorName}
						onChange={(value) => setAttributes({ authorName: value })}
						help={__('Wpisz imię autora komentarza', 'multistore')}
					/>
					<RangeControl
						label={__('Ocena (gwiazdki)', 'multistore')}
						value={rating}
						onChange={(value) => setAttributes({ rating: value })}
						min={1}
						max={5}
						help={__('Wybierz liczbę gwiazdek (1-5)', 'multistore')}
					/>
					<TextControl
						label={__('Nazwa produktu', 'multistore')}
						value={productName}
						onChange={(value) => setAttributes({ productName: value })}
						help={__('Wpisz nazwę produktu', 'multistore')}
					/>
					<TextControl
						label={__('Czas dodania', 'multistore')}
						value={timeAgo}
						onChange={(value) => setAttributes({ timeAgo: value })}
						help={__('Np. "2 miesiące temu"', 'multistore')}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="multistore-block-custom-comment__header">
					<h3 className="multistore-block-custom-comment__author">{authorName}</h3>
					<div className="multistore-block-custom-comment__rating">{renderStars()}</div>
				</div>
				<div className="multistore-block-custom-comment__meta">
					<span className="multistore-block-custom-comment__product">{productName}</span>
					<span className="multistore-block-custom-comment__time">{timeAgo}</span>
				</div>
				<RichText
					tagName="p"
					className="multistore-block-custom-comment__content"
					value={content}
					onChange={(value) => setAttributes({ content: value })}
					placeholder={__('Treść komentarza...', 'multistore')}
				/>
			</div>
		</>
	);
}
