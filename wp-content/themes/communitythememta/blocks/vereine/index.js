import { registerBlockType } from '@wordpress/blocks';

registerBlockType('blocks/vereine', {
    edit: () => <p>Vereine werden auf der Webseite angezeigt.</p>,
});
