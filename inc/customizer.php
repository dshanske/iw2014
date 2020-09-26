<?php
/**
 * Twenty Fourteen Customizer support
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

/**
 * Implement Customizer additions and adjustments.
 *
 * @since Twenty Fourteen 1.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function iw2014_customize_register( $wp_customize ) {
	// Add postMessage support for site title and description.
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'            => '.site-title a',
				'container_inclusive' => false,
				'render_callback'     => 'iw2014_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'            => '.site-description',
				'container_inclusive' => false,
				'render_callback'     => 'iw2014_customize_partial_blogdescription',
			)
		);
	}

	// Rename the label to "Site Title Color" because this only affects the site title in this theme.
	$wp_customize->get_control( 'header_textcolor' )->label = __( 'Site Title Color', 'iw2014' );

	// Rename the label to "Display Site Title & Tagline" in order to make this option extra clear.
	$wp_customize->get_control( 'display_header_text' )->label = __( 'Display Site Title &amp; Tagline', 'iw2014' );

	// Add custom description to Colors and Background controls or sections.
	if ( property_exists( $wp_customize->get_control( 'background_color' ), 'description' ) ) {
		$wp_customize->get_control( 'background_color' )->description = __( 'May only be visible on wide screens.', 'iw2014' );
		$wp_customize->get_control( 'background_image' )->description = __( 'May only be visible on wide screens.', 'iw2014' );
	} else {
		$wp_customize->get_section( 'colors' )->description           = __( 'Background may only be visible on wide screens.', 'iw2014' );
		$wp_customize->get_section( 'background_image' )->description = __( 'Background may only be visible on wide screens.', 'iw2014' );
	}

	// Add the featured content section in case it's not already there.
	$wp_customize->add_section(
		'featured_content',
		array(
			'title'           => __( 'Featured Content', 'iw2014' ),
			'description'     => sprintf(
				/* translators: 1: Featured tag editor URL, 2: Post editor URL. */
				__( 'Use a <a href="%1$s">tag</a> to feature your posts. If no posts match the tag, <a href="%2$s">sticky posts</a> will be displayed instead.', 'iw2014' ),
				esc_url( add_query_arg( 'tag', _x( 'featured', 'featured content default tag slug', 'iw2014' ), admin_url( 'edit.php' ) ) ),
				admin_url( 'edit.php?show_sticky=1' )
			),
			'priority'        => 130,
			'active_callback' => 'is_front_page',
		)
	);

	// Add the featured content layout setting and control.
	$wp_customize->add_setting(
		'featured_content_layout',
		array(
			'default'           => 'grid',
			'sanitize_callback' => 'iw2014_sanitize_layout',
		)
	);

	$wp_customize->add_control(
		'featured_content_layout',
		array(
			'label'   => __( 'Layout', 'iw2014' ),
			'section' => 'featured_content',
			'type'    => 'select',
			'choices' => array(
				'grid'   => __( 'Grid', 'iw2014' ),
				'slider' => __( 'Slider', 'iw2014' ),
			),
		)
	);
}
add_action( 'customize_register', 'iw2014_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @since Twenty Fourteen 1.7
 *
 * @see iw2014_customize_register()
 *
 * @return void
 */
function iw2014_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @since Twenty Fourteen 1.7
 *
 * @see iw2014_customize_register()
 *
 * @return void
 */
function iw2014_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Sanitize the Featured Content layout value.
 *
 * @since Twenty Fourteen 1.0
 *
 * @param string $layout Layout type.
 * @return string Filtered layout type (grid|slider).
 */
function iw2014_sanitize_layout( $layout ) {
	if ( ! in_array( $layout, array( 'grid', 'slider' ), true ) ) {
		$layout = 'grid';
	}

	return $layout;
}

/**
 * Bind JS handlers to make Customizer preview reload changes asynchronously.
 *
 * @since Twenty Fourteen 1.0
 */
function iw2014_customize_preview_js() {
	wp_enqueue_script( 'iw2014_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20141015', true );
}
add_action( 'customize_preview_init', 'iw2014_customize_preview_js' );

/**
 * Add contextual help to the Themes and Post edit screens.
 *
 * @since Twenty Fourteen 1.0
 */
function iw2014_contextual_help() {
	if ( 'admin_head-edit.php' === current_filter() && 'post' !== $GLOBALS['typenow'] ) {
		return;
	}

	get_current_screen()->add_help_tab(
		array(
			'id'      => 'iw2014',
			'title'   => __( 'Twenty Fourteen', 'iw2014' ),
			'content' =>
				'<ul>' .
					/* translators: 1: Featured tag editor URL, 2: Post editor URL, 3: Customizer URL, 4: Post editor URL. */
					'<li>' . sprintf( __( 'The home page features your choice of up to 6 posts prominently displayed in a grid or slider, controlled by a <a href="%1$s">tag</a>; you can change the tag and layout in <a href="%2$s">Appearance &rarr; Customize</a>. If no posts match the tag, <a href="%3$s">sticky posts</a> will be displayed instead.', 'iw2014' ), esc_url( add_query_arg( 'tag', _x( 'featured', 'featured content default tag slug', 'iw2014' ), admin_url( 'edit.php' ) ) ), admin_url( 'customize.php' ), admin_url( 'edit.php?show_sticky=1' ) ) . '</li>' .
					/* translators: %s: Featured images documentation URL. */
					'<li>' . sprintf( __( 'Enhance your site design by using <a href="%s">Featured Images</a> for posts you&rsquo;d like to stand out (also known as post thumbnails). This allows you to associate an image with your post without inserting it. Twenty Fourteen uses featured images for posts and pages&mdash;above the title&mdash;and in the Featured Content area on the home page.', 'iw2014' ), 'https://codex.wordpress.org/Post_Thumbnails#Setting_a_Post_Thumbnail' ) . '</li>' .
					/* translators: %s: Twenty Fourteen documentation URL. */
					'<li>' . sprintf( __( 'For an in-depth tutorial, and more tips and tricks, visit the <a href="%s">Twenty Fourteen documentation</a>.', 'iw2014' ), 'https://codex.wordpress.org/Twenty_Fourteen' ) . '</li>' .
				'</ul>',
		)
	);
}
add_action( 'admin_head-themes.php', 'iw2014_contextual_help' );
add_action( 'admin_head-edit.php', 'iw2014_contextual_help' );
