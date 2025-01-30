import { registerBlockType } from '@wordpress/blocks';

registerBlockType('blocks/gewerbe', {
    edit: () => <p>Gewerbe werden auf der Webseite angezeigt.</p>,
});
