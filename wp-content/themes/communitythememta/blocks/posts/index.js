import { registerBlockType } from '@wordpress/blocks';

registerBlockType('blocks/posts', {
    edit: () => <p>Beitragsliste wird auf der Webseite angezeigt.</p>,
});
