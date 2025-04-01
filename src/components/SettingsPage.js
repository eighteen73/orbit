import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';
import { UISettings } from './sections/UISettings';
import { SecuritySettings } from './sections/SecuritySettings';
import { useSettings } from '../hooks/useSettings';

export function SettingsPage() {
    const { settings, isLoading, error, saveSettings } = useSettings();
    const [saveStatus, setSaveStatus] = useState(null);

    if (isLoading) {
        return <div>{__('Loading...', 'orbit')}</div>;
    }

    if (error) {
        return (
            <Notice status="error">
                {__('Error loading settings:', 'orbit')} {error}
            </Notice>
        );
    }

    const handleSave = async () => {
        try {
            await saveSettings(settings);
            setSaveStatus({ type: 'success', message: __('Settings saved successfully!', 'orbit') });
        } catch (err) {
            setSaveStatus({ type: 'error', message: __('Error saving settings:', 'orbit') + ' ' + err.message });
        }
    };

    return (
        <div className="wrap">
            <h1>{__('Orbit Settings', 'orbit')}</h1>

            {saveStatus && (
                <Notice status={saveStatus.type}>
                    {saveStatus.message}
                </Notice>
            )}

            <UISettings
                settings={settings}
                onChange={(newSettings) => saveSettings(newSettings)}
            />

            <SecuritySettings
                settings={settings}
                onChange={(newSettings) => saveSettings(newSettings)}
            />

            <div className="submit-container">
                <button
                    className="button button-primary"
                    onClick={handleSave}
                >
                    {__('Save Settings', 'orbit')}
                </button>
            </div>
        </div>
    );
}
