import { registerBlockType } from '@wordpress/blocks';

registerBlockType('blocks/termine', {
    edit: () => <p>Terminliste wird auf der Webseite angezeigt.</p>,
});
