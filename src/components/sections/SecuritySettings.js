import { __ } from '@wordpress/i18n';
import { Panel, PanelBody, PanelRow, CheckboxControl } from '@wordpress/components';

export function SecuritySettings({ settings, onChange }) {
    const handleGeneralChange = (value) => {
        onChange({
            ...settings,
            general: value,
        });
    };

    const handleRestApiChange = (value) => {
        onChange({
            ...settings,
            rest_api: value,
        });
    };

    return (
        <Panel>
            <PanelBody
                title={__('Security Settings', 'orbit')}
                initialOpen={true}
            >
                <p className="description">
                    {__('We highly encourage all of these options to be left at the default value (unchecked) unless this website has very specific reason to re-enable a feature.', 'orbit')}
                </p>

                <PanelRow>
                    <div className="orbit-security-general">
                        <p className="components-base-control__label">
                            {__('General', 'orbit')}
                        </p>
                        <CheckboxControl
                            label={__('Display the WordPress version', 'orbit')}
                            help={__('This could act as an hint for hackers to target a website with known vulnerabilities.', 'orbit')}
                            checked={settings?.general?.includes('expose_wordpress_version')}
                            onChange={(checked) => {
                                const current = settings?.general || [];
                                const newValue = checked
                                    ? [...current, 'expose_wordpress_version']
                                    : current.filter(item => item !== 'expose_wordpress_version');
                                handleGeneralChange(newValue);
                            }}
                        />
                        <CheckboxControl
                            label={__('Enable XML-RPC', 'orbit')}
                            help={__('This outdated way of communicating with WordPress leaves websites open to brute force and DDoS attacks. If you must enable this, please try to limit it to necessary functionality and put request rate limiting in place elsewhere.', 'orbit')}
                            checked={settings?.general?.includes('enable_xmlrpc')}
                            onChange={(checked) => {
                                const current = settings?.general || [];
                                const newValue = checked
                                    ? [...current, 'enable_xmlrpc']
                                    : current.filter(item => item !== 'enable_xmlrpc');
                                handleGeneralChange(newValue);
                            }}
                        />
                    </div>
                </PanelRow>

                <PanelRow>
                    <div className="orbit-security-rest-api">
                        <p className="components-base-control__label">
                            {__('REST API', 'orbit')}
                        </p>
                        <CheckboxControl
                            label={__('Enable user endpoints in the REST API', 'orbit')}
                            help={__('You should allow Orbit to disable the user endpoints if not needed. This helps user privacy, hides usernames from hackers, and adds a layer of protection in case some other code opens up a vulnerability in user management.', 'orbit')}
                            checked={settings?.rest_api?.includes('enable_user_endpoints')}
                            onChange={(checked) => {
                                const current = settings?.rest_api || [];
                                const newValue = checked
                                    ? [...current, 'enable_user_endpoints']
                                    : current.filter(item => item !== 'enable_user_endpoints');
                                handleRestApiChange(newValue);
                            }}
                        />
                    </div>
                </PanelRow>
            </PanelBody>
        </Panel>
    );
}
