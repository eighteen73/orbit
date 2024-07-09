<?php
/**
 * If using Wordfence you will find that their version of this file is deleted from ABSPATH
 * during WordPress updates which causes the website to crash.
 *
 * This version of the file should be loaded via `auto_prepend_file` (instead of Wordfence's
 * version) by putting the following snippet into "/path/to/repo/web/.user.ini":
 *
 *   ; Wordfence WAF
 *   auto_prepend_file = '/path/to/repo/web/app/mu-plugins/orbit/includes/wordfence-waf.php'
 *   ; END Wordfence WAF
 *
 * This file has no effect if Wordfence is not installed.
 *
 * @package Nebula
 */

if ( file_exists( dirname( __DIR__, 3 ) . '/plugins/wordfence/waf/bootstrap.php' ) ) {
	define( 'WFWAF_LOG_PATH', dirname( __DIR__, 3 ) . '/wflogs/' );
	include_once dirname( __DIR__, 3 ) . '/plugins/wordfence/waf/bootstrap.php';
}
