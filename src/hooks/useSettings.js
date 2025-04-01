import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export function useSettings() {
    const [settings, setSettings] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await apiFetch({
                path: '/orbit/v1/settings',
                method: 'GET',
            });
            setSettings(response);
        } catch (err) {
            setError(err.message);
        } finally {
            setIsLoading(false);
        }
    };

    const saveSettings = async (newSettings) => {
        try {
            await apiFetch({
                path: '/orbit/v1/settings',
                method: 'POST',
                data: newSettings,
            });
            setSettings(newSettings);
        } catch (err) {
            throw new Error(err.message);
        }
    };

    return {
        settings,
        isLoading,
        error,
        saveSettings,
        setSettings,
    };
}
