<?php
/**
 * Adds ACF field groups.
 *
 * @since 3.0.0
 */
class ACF_Local_Field_Groups {

	public function __construct() {
		add_action('acf/init', [$this, 'add_local_field_groups']);
	}

	public function add_local_field_groups() {

		// acf_add_local_field_group(
		// 	[
		// 		'key'      => 'group_cta_example',
		// 		'title'    => 'Example',
		// 		'fields'   => [
		// 			// ...
		// 		],
		// 		'location' => [
		// 			[
		// 				[
		// 					'param'    => 'post_taxonomy',
		// 					'operator' => '==',
		// 					'value'    => 'cta_style:example',
		// 				],
		// 			],
		// 		],
		// 		'style'    => 'seamless',
		// 	]
		// );
	}
}

new ACF_Local_Field_Groups();
