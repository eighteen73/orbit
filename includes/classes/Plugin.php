<?php
/**
 * Main plugin class.
 *
 * @package Eighteen73\Orbit
 */

namespace Eighteen73\Orbit;

defined( 'ABSPATH' ) || exit;

/**
 * Main Plugin class.
 */
class Plugin {

	use Singleton;

	/**
	 * Setup the plugin.
	 *
	 * @return void
	 */
	public function setup(): void {
		Admin\CleanUI::instance()->setup();
		Admin\HideUpdates::instance()->setup();
		Admin\EnvironmentIcon::instance()->setup();
		BlockEditor\Patterns::instance()->setup();
		Branding\BrandedEmails::instance()->setup();
		Capabilities\GravityForms::instance()->setup();
		Capabilities\Users::instance()->setup();
		Capabilities\Editor::instance()->setup();
		Capabilities\Privacy::instance()->setup();
		DisallowIndexing\DisallowIndexing::instance()->setup();
		Media\RemoteFiles::instance()->setup();
		Monitoring\HealthCheck::instance()->setup();
		Performance\Fast404::instance()->setup();
		Security\DisableAPI::instance()->setup();
		Security\DisableXMLRPC::instance()->setup();
		Security\Headers::instance()->setup();
		Security\HideAuthor::instance()->setup();
		Security\HideVersion::instance()->setup();
		Security\RateLimits::instance()->setup();
		Security\RemoveHeadLinks::instance()->setup();
		ThirdParty\WooCommerce::instance()->setup();
		ThirdParty\Altcha::instance()->setup();
		ThirdParty\ActionScheduler::instance()->setup();
		Utilities\ErrorReporting::instance()->setup();
	}
}
