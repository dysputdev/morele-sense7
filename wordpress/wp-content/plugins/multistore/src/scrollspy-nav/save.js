import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {

  return null;

  const { isSticky, stickyTop } = attributes;
  
  const blockProps = useBlockProps.save({
    className: `scrollspy-nav ${isSticky ? 'is-sticky' : ''}`,
    style: isSticky ? { top: `${stickyTop}px` } : {}
  });

  return (
    <nav {...blockProps}>
      <ul className="scrollspy-nav__list">
        {/* Sections will be rendered by PHP */}
      </ul>
    </nav>
  );
}
