import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('custom/termin-block', {
  edit: () => {
    const blockProps = useBlockProps();
    return (
      <div {...blockProps}>
        <p>Vorschau des Termin-Blocks im Editor</p>
      </div>
    );
  },
  save: () => {
    return null; // Frontend wird per PHP gerendert.
  }
});
