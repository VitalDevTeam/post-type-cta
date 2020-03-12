<?php
if (!class_exists('VTL_Taxonomy_Radio')) {

	/**
	 * Removes and replaces the built-in taxonomy metabox with a radio-select metabox.
	 *
	 * USAGE:
	 * $custom_tax_mb = new VTL_Taxonomy_Radio('category', ['community']);
	 *
	 * OPTIONAL PROPERTIES:
	 * $custom_tax_mb->priority = 'low';
	 * $custom_tax_mb->context = 'normal';
	 * $custom_tax_mb->metabox_title = __('Custom Metabox Title', 'textdomain');
	 * $custom_tax_mb->force_selection = true;
	 *
	 * @link https://webdevstudios.com/2013/07/08/replace-wordpress-default-taxonomy-metabox-with-a-radio-select-metabox/
	 * @since 3.0.0
	 */
	class VTL_Taxonomy_Radio {

		/**
		 * The post types to replace the metabox. Defaults to all post types associated with taxonomy.
		 *
		 * @var    array|string
		 * @access public
		 * @since 3.0.0
		 */
		public $post_types = [];

		/**
		 * The taxonomy slug.
		 *
		 * @var    string
		 * @access public
		 * @since 3.0.0
		 */
		public $slug = '';

		/**
		 * The taxonomy object.
		 *
		 * @var    object
		 * @access public
		 * @since 3.0.0
		 */
		public $taxonomy = false;

		/**
		 * The metabox title. Defaults to taxonomy name.
		 *
		 * @var    string
		 * @access public
		 * @since 3.0.0
		 */
		public $metabox_title = '';

		/**
		 * The metabox priority.
		 *
		 * @var    string
		 * @access public
		 * @since 3.0.0
		 */
		public $priority = 'default';

		/**
		 * The metabox position.
		 *
		 * @var    string
		 * @access public
		 * @since 3.0.0
		 */
		public $context = 'side';

		/**
		 * Removes "None" option.
		 *
		 * @var    boolean
		 * @access public
		 * @since 3.0.0
		 */
		public $force_selection = false;

		/**
		 * Initializes metabox.
		 *
		 * @since 3.0.0
		 * @param string $tax_slug The taxonomy slug.
		 * @param array $post_types The post types to replace the metabox.
		 */
		public function __construct($tax_slug, $post_types = []) {
			$this->slug = $tax_slug;
			$this->post_types = is_array($post_types) ? $post_types : [$post_types];
			add_action('add_meta_boxes', [$this, 'add_radio_box']);
		}

		/**
		 * Removes and replaces the built-in taxonomy metabox.
		 *
		 * @since 3.0.0
		 * @return void
		 */
		public function add_radio_box() {
			foreach ($this->post_types() as $key => $cpt) {
				// remove default category type metabox
				remove_meta_box($this->slug . 'div', $cpt, 'side');
				// remove default tag type metabox
				remove_meta_box('tagsdiv-' . $this->slug, $cpt, 'side' );
				// add the custom radio box
				add_meta_box($this->slug . '_radio', $this->metabox_title(), [$this, 'radio_box'], $cpt, $this->context, $this->priority);
			}
		}

		/**
		 * Displays the taxonomy radio box metabox.
		 *
		 * @since 3.0.0
		 * @return void
		 */
		public function radio_box() {

			// uses same noncename as default box so no save_post hook needed
			wp_nonce_field('taxonomy_' . $this->slug, 'taxonomy_noncename');

			// get terms associated with this post
			$names = wp_get_object_terms(get_the_ID(), $this->slug);
			// get all terms in this taxonomy
			$terms = (array) get_terms($this->slug, 'hide_empty=0');
			// filter the ids out of the terms
			$existing = (!is_wp_error($names) && !empty($names)) ? (array) wp_list_pluck($names, 'term_id') : [];
			// Check if taxonomy is hierarchical
			// Terms are saved differently between types
			$h = $this->taxonomy()->hierarchical;

			// default value
			$default_val = $h ? 0 : '';
			// input name
			$name = $h ? 'tax_input[' . $this->slug . '][]' : 'tax_input[' . $this->slug . ']';

			echo '<div style="margin-bottom: 5px;">
			<ul id="' . $this->slug . '_taxradiolist" data-wp-lists="list:' . $this->slug . '_tax" class="categorychecklist form-no-clear">';

			// If 'category,' force a selection, or force_selection is true
			if ($this->slug != 'category' && !$this->force_selection) {
				// our radio for selecting none
				echo '<li id="' . $this->slug . '_tax-0"><label><input value="' . $default_val . '" type="radio" name="' . $name . '" id="in-' . $this->slug . '_tax-0" ';
				checked(empty($existing));
				// translators: %s: taxonomy singlular name
				echo '> ' . sprintf(__('No %s', 'wds'), $this->taxonomy()->labels->singular_name) . '</label></li>';
			}

			// loop our terms and check if they're associated with this post
			foreach ($terms as $term) {
				$val = $h ? $term->term_id : $term->slug;

				echo '<li id="' . $this->slug . '_tax-' . $term->term_id . '"><label><input value="' . $val . '" type="radio" name="' . $name . '" id="in-' . $this->slug . '_tax-' . $term->term_id . '" ';
				// if so, they get "checked"
				checked(!empty($existing) && in_array($term->term_id, $existing));
				echo '> ' . $term->name . '</label></li>';
			}

			echo '</ul></div>';
		}

		/**
		 * Gets the taxonomy object from the slug.
		 *
		 * @since 3.0.0
		 * @return object Taxonomy object.
		 */
		public function taxonomy() {
			$this->taxonomy = $this->taxonomy ? $this->taxonomy : get_taxonomy($this->slug);
			return $this->taxonomy;
		}

		/**
		 * Gets the taxonomy's associated post types.
		 *
		 * @since 3.0.0
		 * @return array Taxonomy's associated post types.
		 */
		public function post_types() {
			$this->post_types = !empty($this->post_types) ? $this->post_types : $this->taxonomy()->object_type;
			return $this->post_types;
		}

		/**
		 * Gets the metabox title from the taxonomy object's labels (or uses the passed in title).
		 *
		 * @since 3.0.0
		 * @return string Metabox title.
		 */
		public function metabox_title() {
			$this->metabox_title = !empty($this->metabox_title) ? $this->metabox_title : $this->taxonomy()->labels->name;
			return $this->metabox_title;
		}
	}
}
