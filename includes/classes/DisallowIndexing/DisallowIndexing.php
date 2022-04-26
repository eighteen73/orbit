<?php

namespace Orbit\DisallowIndexing;

use Orbit\Singleton;

class DisallowIndexing extends Singleton {

	private bool $enabled = false;

	public function __construct() {
		$this->enabled = $this->is_enabled();
	}

	public function setup() {
		add_action('pre_option_blog_public', [ $this, 'disallow' ]);
		add_action('admin_notices', [ $this, 'showNotice' ]);
	}

	public function is_enabled(): bool {
		return WP_ENV !== 'production';
	}

	public function disallow(): int {
		if (!$this->enabled) {
			return 1;
		}
		return 0;
	}

	public function showNotice() {
		if (!$this->enabled) {
			return null;
		}

		$message = sprintf(
			__('%1$s Search engine indexing has been discouraged because the current environment is %2$s.', 'orbit'),
			'<strong>Orbit:</strong>',
			'<code>'.WP_ENV.'</code>'
		);
		echo "<div class='notice notice-warning'><p>{$message}</p></div>";
	}

}
