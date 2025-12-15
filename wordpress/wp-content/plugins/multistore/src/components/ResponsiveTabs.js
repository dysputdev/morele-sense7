import { __ } from '@wordpress/i18n';
import { TabPanel } from '@wordpress/components';
import { desktop, tablet, mobile } from '@wordpress/icons';

export const tabs = [
	{
		name: 'desktop',
		title: __('Desktop', 'multistore'),
		icon: desktop,
		className: 'responsive-tab-desktop',
	},
	{
		name: 'tablet',
		title: __('Tablet', 'multistore'),
		icon: tablet,
		className: 'responsive-tab-tablet',
	},
	{
		name: 'mobile',
		title: __('Mobile', 'multistore'),
		icon: mobile,
		className: 'responsive-tab-mobile',
	},
];

export const ResponsiveTabs = ({ renderResponsiveControls }) => {
	return (
		<TabPanel
			className="responsive-tab-panel"
			activeClass="is-active"
			tabs={ tabs }
		>
			{(tab) => renderResponsiveControls(tab.name)}
		</TabPanel>
	)
}

export default ResponsiveTabs
