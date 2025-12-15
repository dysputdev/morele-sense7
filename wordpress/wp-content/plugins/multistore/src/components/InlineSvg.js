import { useState, useEffect } from '@wordpress/element';

/**
 * Komponent do renderowania SVG jako inline HTML
 * Dzięki temu SVG dziedziczy właściwości CSS jak color i fill: currentColor działa poprawnie
 */
export const InlineSvg = ({ src, alt = '', className = '', style = {} }) => {
	const [svgContent, setSvgContent] = useState(null);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(false);

	useEffect(() => {
		if (!src) {
			setLoading(false);
			return;
		}

		setLoading(true);
		setError(false);

		fetch(src)
			.then(response => {
				if (!response.ok) {
					throw new Error('Failed to load SVG');
				}
				return response.text();
			})
			.then(svgText => {
				// Parsuj SVG i dodaj/zaktualizuj atrybuty
				const parser = new DOMParser();
				const svgDoc = parser.parseFromString(svgText, 'image/svg+xml');
				const svgElement = svgDoc.querySelector('svg');

				if (svgElement) {
					// Dodaj className jeśli istnieje
					if (className) {
						const existingClass = svgElement.getAttribute('class') || '';
						svgElement.setAttribute('class', `${existingClass} ${className}`.trim());
					}

					// Dodaj style
					if (style.width) {
						svgElement.setAttribute('width', style.width);
					}
					if (style.height) {
						svgElement.setAttribute('height', style.height);
					}

					// Dodaj aria-label dla dostępności
					if (alt) {
						svgElement.setAttribute('aria-label', alt);
						svgElement.setAttribute('role', 'img');
					}

					setSvgContent(svgElement.outerHTML);
				} else {
					throw new Error('Invalid SVG content');
				}
				setLoading(false);
			})
			.catch(err => {
				console.error('Error loading SVG:', err);
				setError(true);
				setLoading(false);
			});
	}, [src, className, style.width, style.height, alt]);

	if (loading) {
		return <div className={`inline-svg-loading ${className}`} style={style} />;
	}

	if (error || !svgContent) {
		return (
			<div className={`inline-svg-error ${className}`} style={style}>
				<span>⚠️</span>
			</div>
		);
	}

	return (
		<div
			className={`inline-svg-wrapper ${className}`}
			style={style}
			dangerouslySetInnerHTML={{ __html: svgContent }}
		/>
	);
};
