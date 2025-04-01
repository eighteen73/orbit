import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { SettingsPage } from './components/SettingsPage';

domReady(() => {
    const container = document.getElementById('orbit-settings-root');
    if (container) {
        const root = createRoot(container);
        root.render(<SettingsPage />);
    }
});
