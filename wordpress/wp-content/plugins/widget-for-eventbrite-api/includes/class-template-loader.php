<?php
/**
 * helper Class to set up an Gamajo's template loader
 */

namespace WidgetForEventbriteAPI\Includes;

use Gamajo_Template_Loader;


require_once dirname( __FILE__ ) . '/vendor/gamajo/template-loader/class-gamajo-template-loader.php';

/**
 * Template loader
 *
 * Only need to specify class properties here.
 *
 */
class Template_Loader extends Gamajo_Template_Loader {
	/**
	 * Prefix for filter names.
	 *
	 */
	protected $filter_prefix = 'widget-for-eventbrite-api';

	/**
	 * Directory name where custom templates for this plugin should be found in the theme.
	 *
	 */
	protected $theme_template_directory = 'widget-for-eventbrite-api';

	/**
	 * Reference to the root directory path of this plugin.
	 *
	 * Can either be a defined constant, or a relative reference from where the subclass lives.
	 *
	 *
	 */
	protected $plugin_directory = WIDGET_FOR_EVENTBRITE_API_PLUGIN_DIR;

	/**
	 * Directory name where templates are found in this plugin.
	 *
	 * Can either be a defined constant, or a relative reference from where the subclass lives.
	 *
	 * e.g. 'templates' or 'includes/templates', etc.
	 *
	 */
	protected $plugin_template_directory = 'templates';
}