import { registerBlockType } from '@wordpress/blocks';

registerBlockType('blocks/teammitglieder', {
    edit: () => <p>Teammitglieder werden auf der Webseite angezeigt.</p>,
});
