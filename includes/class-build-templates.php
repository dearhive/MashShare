<?php

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Description of class-templates
 *
 * @author Rene Hermenau
 */
class mashsbBuildTemplates {

        /**
         * Return template content
	 * @return string name of view
         * @param string name of template
         * @param array $args optional
	 */
	public function get_template($template, $args = array() ) {
		ob_start();
		include MASHSB_PLUGIN_DIR . '/templates/' . $template . '.php';
		$html = ob_get_clean();
		return $html;
	}
        
}