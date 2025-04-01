import { __ } from '@wordpress/i18n';
import { Panel, PanelBody, PanelRow, CheckboxControl, MediaUpload, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

export function UISettings({ settings, onChange }) {
    const [loginLogo, setLoginLogo] = useState(settings?.login_logo || '');

    const handleMenuItemsChange = (value) => {
        onChange({
            ...settings,
            disable_menu_items: value,
        });
    };

    const handleToolbarItemsChange = (value) => {
        onChange({
            ...settings,
            disable_toolbar_items: value,
        });
    };

    const handleLoginLogoChange = (media) => {
        setLoginLogo(media.url);
        onChange({
            ...settings,
            login_logo: media.url,
        });
    };

    return (
        <Panel>
            <PanelBody
                title={__('UI Settings', 'orbit')}
                initialOpen={true}
            >
                <p className="description">
                    {__('Orbit automatically removes a lot of UI elements that are rarely used and can confuse some CMS users. The items below are a few that can be toggled on/off as needed.', 'orbit')}
                </p>

                <PanelRow>
                    <CheckboxControl
                        label={__('Disable menu items', 'orbit')}
                        checked={settings?.disable_menu_items || []}
                        options={[
                            { label: 'Dashboard', value: 'dashboard' },
                            { label: 'Posts', value: 'posts' },
                            { label: 'Comments', value: 'comments' },
                        ]}
                        onChange={handleMenuItemsChange}
                    />
                </PanelRow>

                <PanelRow>
                    <CheckboxControl
                        label={__('Disable toolbar items', 'orbit')}
                        checked={settings?.disable_toolbar_items || []}
                        options={[
                            { label: 'New content', value: 'new_content' },
                            { label: 'WordPress updates', value: 'wordpress_updates' },
                            { label: 'Comments', value: 'comments' },
                        ]}
                        onChange={handleToolbarItemsChange}
                    />
                </PanelRow>

                <PanelRow>
                    <div className="orbit-login-logo">
                        <p>{__('Login logo', 'orbit')}</p>
                        {/* <MediaUpload
                            // onSelect={handleLoginLogoChange}
                            allowedTypes={['image']}
                            value={loginLogo}
                            render={({ open }) => (
                                <Button
                                    onClick={open}
                                    variant="secondary"
                                >
                                    {loginLogo
                                        ? __('Change Image', 'orbit')
                                        : __('Choose Image', 'orbit')}
                                </Button>
                            )}
                        /> */}
                        {loginLogo && (
                            <div className="orbit-login-logo-preview">
                                <img src={loginLogo} alt={__('Login logo preview', 'orbit')} />
                            </div>
                        )}
                    </div>
                </PanelRow>
            </PanelBody>
        </Panel>
    );
}
