<?php
/* 
* Plugin Name: Riverbend Glossary 1006
* Version: 1.0
* Description: Glossary interface which alphabetizes the published post types and adds the letter in front of the grouped posts + the live, reactive search
* Author: Jonathan Skinner
* Author URI: https://skinnerconsulting.tech
* License:  GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

defined('WPINC') || exit;

define('RIVERBEND_GLOSSARY_1006_VERSION', '1.0.0');

class Riverbend_Glossary_1006
{
	/**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	private function __construct()
	{
	}

	private function setup_actions()
	{
		add_action('add_meta_boxes', array($this, 'add_link_override_field'));
		add_action('init', array($this, 'register_glossary_items_type'));
		add_action('save_post', array($this, 'riverbend_save_postdata'), 1);
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'my_enqueue_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'my_enqueue_styles'));
		add_shortcode('rbg_glossary_1006', array($this, 'rb_glossary_func'));
	}
	public function rb_glossary_func()
	{
		$output = '<div id="rbg-container"><input type="text" id="glossary-search" placeholder="Search..."><div class="rbg-list">';

		$args = array(
			'post_type' => 'glossary_item',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC'
		);
		$query = new WP_Query($args);

		if ($query->have_posts()) {
			$current_letter = '';
			while ($query->have_posts()) {
				$query->the_post();
				$title = get_the_title();
				$first_letter = strtoupper($title[0]);
				if ($first_letter !== $current_letter) {
					if ($current_letter !== '') {
						$output .= '</div></div>';
					}
					$output .= '<div class="glossary-group"><div class="group-header">' . $first_letter . '</div><div class="items-list">';
					$current_letter = $first_letter;
				}

				$link_override = get_post_meta(get_the_ID(), '_riverbend_link_override', true);
				$link = $link_override ? esc_url($link_override) : get_permalink();

				$output .= '<div class="glossary-item"><a href="' . $link . '">' . $title . '</a></div>';
			}
			$output .= '</div></div>';
		}
		wp_reset_postdata();
		$output .= '</div></div>';

		return $output;
	}


	public function enqueue_admin_scripts()
	{
		wp_enqueue_script('required_fields', plugin_dir_url(__FILE__) . 'admin/js/required_fields.js', array('jquery'), '1.0.0', true);
	}

	public function my_enqueue_scripts()
	{
		wp_enqueue_script('live_search', plugin_dir_url(__FILE__) . 'public/js/live_search.js', array('jquery'), '1.0.0', true);
	}

	public function my_enqueue_styles()
	{
		wp_enqueue_style('live_search', plugin_dir_url(__FILE__) . 'public/css/live_search.css');
	}

	public function register_glossary_items_type()
	{
		register_post_type('glossary_item', array(
			'labels' => array(
				'name'			=> 'Glossary Items',
				'singular_name'	=> 'Glossary Item'
			),
			'public'			=> true,
			'has_archive'		=> true,
			'rewrite'			=> array('slug' => 'glossary-items'),
		));
	}

	public function add_link_override_field()
	{
		add_meta_box(
			'riverbend_link_override_id',
			'Link Override',
			array($this, 'riverbend_custom_box_html'),
			'glossary_item'
		);
	}

	public function riverbend_custom_box_html($post)
	{
		$value = get_post_meta($post->ID, '_riverbend_link_override', true);
		wp_nonce_field('riverbend_save_link_override_data', 'riverbend_link_override_nonce');
		echo '<input type="text" name="riverbend_link_override" value="' . esc_attr($value) . '" />';
	}

	public function riverbend_save_postdata($post_id)
	{
		if (!isset($_POST['riverbend_link_override_nonce']) || !wp_verify_nonce($_POST['riverbend_link_override_nonce'], 'riverbend_save_link_override_data'))
			return;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		if (isset($_POST['riverbend_link_override'])) {
			update_post_meta(
				$post_id,
				'_riverbend_link_override',
				esc_url_raw($_POST['riverbend_link_override'])
			);
		}
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return Riverbend_Glossary_1006
	 */

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self;
			self::$instance->setup_actions();
		}
		return self::$instance;
	}
}

// Instantiate our class
$Riverbend_Glossary_1006 = Riverbend_Glossary_1006::getInstance();
