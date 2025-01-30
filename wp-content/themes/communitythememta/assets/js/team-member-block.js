import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';

registerBlockType('custom/team-member', {
    title: 'Team Member',
    icon: 'admin-users',
    category: 'widgets',
    attributes: {
        team_image: { type: 'string' },
        team_name: { type: 'string' },
        team_position: { type: 'string' },
        team_phone: { type: 'string' },
        team_mail: { type: 'string' },
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title="Team Member Details">
                        <TextControl
                            label="Image URL"
                            value={attributes.team_image}
                            onChange={(value) => setAttributes({ team_image: value })}
                        />
                        <TextControl
                            label="Name"
                            value={attributes.team_name}
                            onChange={(value) => setAttributes({ team_name: value })}
                        />
                        <TextControl
                            label="Position"
                            value={attributes.team_position}
                            onChange={(value) => setAttributes({ team_position: value })}
                        />
                        <TextControl
                            label="Phone"
                            value={attributes.team_phone}
                            onChange={(value) => setAttributes({ team_phone: value })}
                        />
                        <TextControl
                            label="Email"
                            value={attributes.team_mail}
                            onChange={(value) => setAttributes({ team_mail: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="team-member">
                    {attributes.team_image && (
                        <div className="team-member-image">
                            <img src={attributes.team_image} alt={attributes.team_name} />
                        </div>
                    )}
                    <div className="team-member-content">
                        <h3>{attributes.team_name || 'Name'}</h3>
                        <p>{attributes.team_position || 'Position'}</p>
                        <p>{attributes.team_phone || 'Phone'}</p>
                        <p>{attributes.team_mail || 'Email'}</p>
                    </div>
                </div>
            </div>
        );
    },
    save: () => {
        // Save is handled by PHP render callback
        return null;
    },
});
