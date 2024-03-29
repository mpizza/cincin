<?php
/**
 * Defining constants
 *
 * @since 1.0.0
 */
$bavotasan_theme_data = wp_get_theme();
define( 'BAVOTASAN_THEME_URL', get_template_directory_uri() );
define( 'BAVOTASAN_THEME_TEMPLATE', get_template_directory() );
define( 'BAVOTASAN_THEME_VERSION', trim( $bavotasan_theme_data->Version ) );
define( 'BAVOTASAN_THEME_NAME', $bavotasan_theme_data->Name );
define( 'BAVOTASAN_THEME_FILE', get_option( 'template' ) );
define( 'FONT_AWESOME_VERSION', '4.0.3' );

/**
 * Includes
 *
 * @since 1.0.0
 */
require( BAVOTASAN_THEME_TEMPLATE . '/library/customizer.php' ); // Functions for theme options page
require( BAVOTASAN_THEME_TEMPLATE . '/library/preview-pro.php' ); // Functions for preview pro page
require( BAVOTASAN_THEME_TEMPLATE . '/library/custom-metaboxes.php' ); // Functions for home page alignment

/**
 * Prepare the content width
 *
 * @since 1.0.0
 */
$bavotasan_theme_options = bavotasan_theme_options();
$array_content = array( 'col-md-2' => .1666, 'col-md-3' => .25, 'col-md-4' => .3333, 'col-md-5' => .4166, 'col-md-6' => .5, 'col-md-7' => .5833, 'col-md-8' => .6666, 'col-md-9' => .75, 'col-md-10' => .8333, 'col-md-12' => 1 );
$bavotasan_main_content =  $array_content[$bavotasan_theme_options['primary']] * $bavotasan_theme_options['width'] - 30;

if ( ! isset( $content_width ) )
	$content_width = round( $bavotasan_main_content );

add_action( 'after_setup_theme', 'bavotasan_setup' );
if ( ! function_exists( 'bavotasan_setup' ) ) :
/**
 * Initial setup
 *
 * This function is attached to the 'after_setup_theme' action hook.
 *
 * @uses	load_theme_textdomain()
 * @uses	get_locale()
 * @uses	BAVOTASAN_THEME_TEMPLATE
 * @uses	add_theme_support()
 * @uses	add_editor_style()
 * @uses	add_custom_background()
 * @uses	add_custom_image_header()
 * @uses	register_default_headers()
 *
 * @since 1.0.0
 */
function bavotasan_setup() {
	load_theme_textdomain( 'arcade', BAVOTASAN_THEME_TEMPLATE . '/library/languages' );

	// Add default posts and comments RSS feed links to <head>.
	add_theme_support( 'automatic-feed-links' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// This theme uses wp_nav_menu() in two location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'arcade' ) );

	// Add support for a variety of post formats
	add_theme_support( 'post-formats', array( 'gallery', 'image', 'video', 'audio', 'quote', 'link', 'status', 'aside' ) );

	// This theme uses Featured Images (also known as post thumbnails) for archive pages
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'half', 570, 220, true );
	add_image_size( 'square100', 100, 100, true );

	// Add a filter to bavotasan_header_image_width and bavotasan_header_image_height to change the width and height of your custom header.
	add_theme_support( 'custom-header', array(
		'header-text' => false,
		'flex-height' => true,
		'flex-width' => true,
		'random-default' => true,
		'width' => apply_filters( 'bavotasan_header_image_width', 1500 ),
		'height' => apply_filters( 'bavotasan_header_image_height', 600 ),
	) );

	// Default custom headers packaged with the theme. %s is a placeholder for the theme template directory URI.
	register_default_headers( array(
		'header01' => array(
			'url' => '%s/library/images/header01.jpg',
			'thumbnail_url' => '%s/library/images/header01-thumbnail.jpg',
			'description' => __( 'Default Header 1', 'arcade' )
		),
	) );

	// Add support for custom backgrounds
	add_theme_support( 'custom-background' );

	// Add HTML5 elements
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', ) );

	// Infinite scroll
	add_theme_support( 'infinite-scroll', array(
	    'type' => 'scroll',
	    'container' => 'primary',
		'wrapper' => false,
		'footer' => false,
	) );
}
endif; // bavotasan_setup

add_action( 'pre_get_posts', 'bavotasan_home_page_query' );
/**
 * Set up home page query
 *
 * This function is attached to the 'pre_get_posts' action hook.
 *
 * @since 1.0.0
 */
function bavotasan_home_page_query( $query ) {
	if ( $query->is_home() && $query->is_main_query() ) {
		if ( get_queried_object_id() == get_option('page_for_posts') )
			return;

		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$per_page = get_option( 'posts_per_page' );
		$offset = ( 1 < $paged ) ? ( 10 * ( $paged - 1 ) + 4 ) - $per_page : 0;
		$show = ( 0 == $offset ) ? 4 : $per_page;

		$query->set( 'ignore_sticky_posts', true );
		$query->set( 'posts_per_page', $show );
		$query->set( 'offset', $offset );
	}
}

add_action( 'wp_head', 'bavotasan_styles' );
/**
 * Add a style block to the theme for the current link color.
 *
 * This function is attached to the 'wp_head' action hook.
 *
 * @since 1.0.0
 */
function bavotasan_styles() {
	$bavotasan_theme_options = bavotasan_theme_options();
	?>
<style>
.container { max-width: <?php echo $bavotasan_theme_options['width']; ?>px; }
</style>
	<?php
}

add_action( 'wp_enqueue_scripts', 'bavotasan_add_js' );
if ( ! function_exists( 'bavotasan_add_js' ) ) :
/**
 * Load all JavaScript to header
 *
 * This function is attached to the 'wp_enqueue_scripts' action hook.
 *
 * @uses	is_admin()
 * @uses	is_singular()
 * @uses	get_option()
 * @uses	wp_enqueue_script()
 * @uses	BAVOTASAN_THEME_URL
 *
 * @since 1.0.0
 */
function bavotasan_add_js() {
	$bavotasan_theme_options = bavotasan_theme_options();

	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	wp_enqueue_script( 'bootstrap', BAVOTASAN_THEME_URL .'/library/js/bootstrap.min.js', array( 'jquery' ), '3.0.3', true );
	wp_enqueue_script( 'fillsize', BAVOTASAN_THEME_URL .'/library/js/fillsize.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'arctext', BAVOTASAN_THEME_URL .'/library/js/jquery.arctext.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'theme_js', BAVOTASAN_THEME_URL .'/library/js/theme.js', array( 'bootstrap' ), '', true );

	wp_localize_script( 'theme_js', 'theme_js_vars', array(
		'arc' => $bavotasan_theme_options['arc'],
	) );

	wp_enqueue_style( 'theme_stylesheet', get_stylesheet_uri() );
	wp_enqueue_style( 'google_fonts', '//fonts.googleapis.com/css?family=Megrim|Raleway|Open+Sans:400,400italic,700,700italic', false, null, 'all' );
	wp_enqueue_style( 'font_awesome', esc_url( '//netdna.bootstrapcdn.com/font-awesome/' . FONT_AWESOME_VERSION . '/css/font-awesome.css' ), false, null, 'all' );
}
endif; // bavotasan_add_js

add_action( 'widgets_init', 'bavotasan_widgets_init' );
if ( ! function_exists( 'bavotasan_widgets_init' ) ) :
/**
 * Creating the two sidebars
 *
 * This function is attached to the 'widgets_init' action hook.
 *
 * @uses	register_sidebar()
 *
 * @since 1.0.0
 */
function bavotasan_widgets_init() {
	$bavotasan_theme_options = bavotasan_theme_options();
	require( BAVOTASAN_THEME_TEMPLATE . '/library/widgets/widget-image-icon.php' ); // Custom Image/Icon Text widget

	register_sidebar( array(
		'name' => __( 'First Sidebar', 'arcade' ),
		'id' => 'sidebar',
		'description' => __( 'This is the first sidebar. It won\'t appear on the home page unless you set a static front page.', 'arcade' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Home Page Top Area', 'arcade' ),
		'id' => 'home-page-top-area',
		'description' => __( 'Area on the home page above the main content. Designed specifically for four Icon & Text widgets. Add at least one widget to make it appear.', 'arcade' ),
		'before_widget' => '<aside id="%1$s" class="home-widget col-md-3 %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="home-widget-title">',
		'after_title' => '</h3>',
	) );
}
endif; // bavotasan_widgets_init

/**
 * Add pagination
 *
 * @uses	paginate_links()
 * @uses	add_query_arg()
 *
 * @since 1.0.0
 */
function bavotasan_pagination() {
	global $wp_query, $paged, $bavotasan_grid_query;

	$wp_query = ( $bavotasan_grid_query ) ? $bavotasan_grid_query : $wp_query;

	// Don't print empty markup if there's only one page.
	if ( $wp_query->max_num_pages < 2 && 0 == $paged )
		return;
	?>
	<nav class="navigation clearfix" role="navigation">
		<h1 class="sr-only"><?php _e( 'Posts navigation', 'arcade' ); ?></h1>
		<?php if ( get_next_posts_link() ) :
			$text = ( 2 > $paged && is_front_page() ) ? __( 'View more &rarr;', 'arcade' ) : __( '&larr; Older posts', 'arcade' );
		?>
		<div class="nav-previous"><?php next_posts_link( $text ); ?></div>
		<?php endif; ?>

		<?php if ( get_previous_posts_link() ) :
			$text = ( 2 == $paged && is_front_page() ) ? __( 'Back to home &rarr;', 'arcade' ) : __( 'Newer posts &rarr;', 'arcade' );
		?>
		<div class="nav-next"><?php previous_posts_link( $text ); ?></div>
		<?php endif; ?>
	</nav><!-- .navigation -->
	<?php
	wp_reset_query();
}

add_filter( 'wp_title', 'bavotasan_filter_wp_title', 10, 2 );
if ( ! function_exists( 'bavotasan_filter_wp_title' ) ) :
/**
 * Filters the page title appropriately depending on the current page
 *
 * @uses	get_bloginfo()
 * @uses	is_home()
 * @uses	is_front_page()
 *
 * @since 1.0.0
 */
function bavotasan_filter_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'arcade' ), max( $paged, $page ) );

	return $title;
}
endif; // bavotasan_filter_wp_title

if ( ! function_exists( 'bavotasan_comment' ) ) :
/**
 * Callback function for comments
 *
 * Referenced via wp_list_comments() in comments.php.
 *
 * @uses	get_avatar()
 * @uses	get_comment_author_link()
 * @uses	get_comment_date()
 * @uses	get_comment_time()
 * @uses	edit_comment_link()
 * @uses	comment_text()
 * @uses	comments_open()
 * @uses	comment_reply_link()
 *
 * @since 1.0.0
 */
function bavotasan_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	switch ( $comment->comment_type ) :
		case '' :
		?>
		<li <?php comment_class(); ?>>
			<div id="comment-<?php comment_ID(); ?>" class="comment-body">
				<div class="comment-avatar">
					<?php echo get_avatar( $comment, 60 ); ?>
				</div>
				<div class="comment-content">
					<div class="comment-author">
						<?php echo get_comment_author_link() . ' '; ?>
					</div>
					<div class="comment-meta">
						<?php
						printf( __( '%1$s at %2$s', 'arcade' ), get_comment_date(), get_comment_time() );
						edit_comment_link( __( '(edit)', 'arcade' ), '  ', '' );
						?>
					</div>
					<div class="comment-text">
						<?php if ( '0' == $comment->comment_approved ) { echo '<em>' . __( 'Your comment is awaiting moderation.', 'arcade' ) . '</em>'; } ?>
						<?php comment_text() ?>
					</div>
					<?php if ( $args['max_depth'] != $depth && comments_open() && 'pingback' != $comment->comment_type ) { ?>
					<div class="reply">
						<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					</div>
					<?php } ?>
				</div>
			</div>
			<?php
			break;

		case 'pingback'  :
		case 'trackback' :
		?>
		<li id="comment-<?php comment_ID(); ?>" class="pingback">
			<div class="comment-body">
				<i class="fa fa-paperclip"></i>
				<?php _e( 'Pingback:', 'arcade' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(edit)', 'arcade' ), ' ' ); ?>
			</div>
			<?php
			break;
	endswitch;
}
endif; // bavotasan_comment

add_filter( 'excerpt_more', 'bavotasan_excerpt' );
if ( ! function_exists( 'bavotasan_excerpt' ) ) :
/**
 * Adds a read more link to all excerpts
 *
 * This function is attached to the 'excerpt_more' filter hook.
 *
 * @param	int $more
 *
 * @return	Custom excerpt ending
 *
	 * @since 1.0.0
 */
function bavotasan_excerpt( $more ) {
	return '&hellip;';
}
endif; // bavotasan_excerpt

add_filter( 'wp_trim_excerpt', 'bavotasan_excerpt_more' );
if ( ! function_exists( 'bavotasan_excerpt_more' ) ) :
/**
 * Adds a read more link to all excerpts
 *
 * This function is attached to the 'wp_trim_excerpt' filter hook.
 *
 * @param	string $text
 *
 * @return	Custom read more link
 *
 * @since 1.0.0
 */
function bavotasan_excerpt_more( $text ) {
	if ( is_singular() )
		return $text;

	return '<p class="excerpt">' . $text . ' <a href="' . get_permalink( get_the_ID() ) . '">' . __( 'Read more &rarr;', 'arcade' ) . '</a></p>';
}
endif; // bavotasan_excerpt_more

add_filter( 'the_content_more_link', 'bavotasan_content_more_link', 10, 2 );
if ( ! function_exists( 'bavotasan_content_more_link' ) ) :
/**
 * Customize read more link for content
 *
 * This function is attached to the 'the_content_more_link' filter hook.
 *
 * @param	string $link
 * @param	string $text
 *
 * @return	Custom read more link
 *
 * @since 1.0.0
 */
function bavotasan_content_more_link( $link, $text ) {
	return '<a href="' . get_permalink( get_the_ID() ) . '">' . $text . '</a>';
}
endif; // bavotasan_content_more_link

add_filter( 'excerpt_length', 'bavotasan_excerpt_length', 999 );
if ( ! function_exists( 'bavotasan_excerpt_length' ) ) :
/**
 * Custom excerpt length
 *
 * This function is attached to the 'excerpt_length' filter hook.
 *
 * @param	int $length
 *
 * @return	Custom excerpt length
 *
 * @since 1.0.0
 */
function bavotasan_excerpt_length( $length ) {
	global $bavotasan_custom_excerpt_length;

	if ( $bavotasan_custom_excerpt_length )
		return $bavotasan_custom_excerpt_length;

	return 60;
}
endif; // bavotasan_excerpt_length

/*
 * Remove default gallery styles
 */
add_filter( 'use_default_gallery_style', '__return_false' );

/**
 * Print the attached image with a link to the next attached image.
 *
 * @since 1.0.9
 */
function bavotasan_the_attached_image() {
	$post = get_post();

	$attachment_size = apply_filters( 'bavotasan_attachment_size', array( 810, 810 ) );
	$next_attachment_url = wp_get_attachment_url();

	$attachment_ids = get_posts( array(
		'post_parent' => $post->post_parent,
		'fields' => 'ids',
		'numberposts' => -1,
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'order' => 'ASC',
		'orderby' => 'menu_order ID',
	) );

	if ( count( $attachment_ids ) > 1 ) {
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id == $post->ID ) {
				$next_id = current( $attachment_ids );
				break;
			}
		}

		if ( $next_id )
			$next_attachment_url = get_attachment_link( $next_id );
		else
			$next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
	}

	printf( '<a href="%1$s" rel="attachment">%2$s</a>',
		esc_url( $next_attachment_url ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}

/**
 * Create the required attributes for the #primary container
 *
 * @since 1.0.0
 */
function bavotasan_primary_attr() {
	$bavotasan_theme_options = bavotasan_theme_options();
	$primary = str_replace( 'col-md-', '', $bavotasan_theme_options['primary'] );
	$secondary = ( is_active_sidebar( 'second-sidebar' ) ) ? str_replace( 'col-md-', '', $bavotasan_theme_options['secondary'] ) : 12 - $primary;
	$tertiary = 12 - $primary - $secondary;
	$class = $bavotasan_theme_options['primary'];
	$class = ( 'left' == $bavotasan_theme_options['layout'] ) ? $class . ' pull-right' : $class;

	echo 'class="' . $class . ' hfeed"';
}

/**
 * Create the required classes for the #secondary sidebar container
 *
 * @since 1.0.0
 */
function bavotasan_sidebar_class() {
	$bavotasan_theme_options = bavotasan_theme_options();
	$primary = str_replace( 'col-md-', '', $bavotasan_theme_options['primary'] );
	$end = ( 'right' == $bavotasan_theme_options['layout'] ) ? ' end' : '';
	$class = 'col-md-' . ( 12 - $primary ) . $end;

	echo 'class="' . $class . '"';
}

/**
 * Default menu
 *
 * Referenced via wp_nav_menu() in header.php.
 *
 * @since 1.0.0
 */
function bavotasan_default_menu( $args ) {
	extract( $args );

	$output = wp_list_categories( array(
		'title_li' => '',
		'echo' => 0,
		'number' => 5,
		'depth' => 2,
	) );
	$output = $output. "<li><a href='/contact/'>聯絡我們</a></li>";
	echo "<ul class='$menu_class'>$output</ul>";
}

/**
 * Add bootstrap classes to menu items
 *
 * @since 1.0.0
 */
class Bavotasan_Page_Navigation_Walker extends Walker_Nav_Menu {
	function check_current( $classes ) {
		return preg_match( '/(current[-_])|active|dropdown/', $classes );
	}

	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "\n<ul class=\"dropdown-menu\">\n";
	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$item_html = '';
		parent::start_el( $item_html, $item, $depth, $args );

		if ( $item->is_dropdown && ( $depth === 0 ) ) {
			$item_html = str_replace( '<a', '<a class="dropdown-toggle" data-toggle="dropdown" data-target="#"', $item_html );
			$item_html = str_replace( '</a>', ' <b class="caret"></b></a>', $item_html );
		} elseif ( stristr( $item_html, 'li class="divider' ) ) {
			$item_html = preg_replace( '/<a[^>]*>.*?<\/a>/iU', '', $item_html );
		} elseif ( stristr( $item_html, 'li class="nav-header' ) ) {
			$item_html = preg_replace( '/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html );
		}

		$output .= $item_html;
	}

	function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
		$element->is_dropdown = !empty( $children_elements[$element->ID] );

		if ( $element->is_dropdown ) {
			if ( $depth === 0 ) {
				$element->classes[] = 'dropdown';
			} elseif ( $depth > 0 ) {
				$element->classes[] = 'dropdown-submenu';
			}
		}
		$element->classes[] = ( $element->current || in_array( 'current-menu-parent', $element->classes ) ) ? 'active' : '';

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
}

add_filter( 'wp_nav_menu_args', 'bavotasan_nav_menu_args' );
/**
 * Set our new walker only if a menu is assigned and a child theme hasn't modified it to one level deep
 *
 * This function is attached to the 'wp_nav_menu_args' filter hook.
 *
 * @author Kirk Wight <http://kwight.ca/adding-a-sub-menu-indicator-to-parent-menu-items/>
 * @since 1.0.0
 */
function bavotasan_nav_menu_args( $args ) {
    if ( 1 !== $args[ 'depth' ] && has_nav_menu( 'primary' ) )
        $args[ 'walker' ] = new Bavotasan_Page_Navigation_Walker;

    return $args;
}

/**
 * Display either the home page slider, custom image or default header image
 *
 * @since 1.0.0
 */
function bavotasan_header_images() {
	$custom_image = ( is_singular() ) ? get_post_meta( get_the_ID(), 'arcade_basic_custom_image', true ) : '';

	if ( $custom_image ) {
		echo '<img src="' . esc_url( $custom_image ) . '" alt="" class="header-img" />';
	} else {
		if ( $header_image = get_header_image() ) :
			?>
			<img class="header-img" src="<?php header_image(); ?>" alt="" />
			<?php
		endif;
	}
}

/**
 * Gathers icons from Font Awesome stylesheet
 *
 * @since 1.0.0
 */
function bavotasan_font_awesome_icons( $display_name = true ) {
	$icons = get_transient( 'bavotasan_font_awesome_icons' );
	if ( empty( $icons ) ){
		$pattern = '/\.(fa-(?:\w+(?:-)?)+):before\s+{\s*content:\s*"(.+)";\s+}/';
		$subject = wp_remote_fopen( 'http://netdna.bootstrapcdn.com/font-awesome/' . FONT_AWESOME_VERSION . '/css/font-awesome.css' );

		preg_match_all( $pattern, $subject, $matches, PREG_SET_ORDER );

		$icons = array();
		foreach ( $matches as $match ){
		    $icons[$match[1]] = $match[2];
		}

		set_transient( 'bavotasan_font_awesome_icons', $icons, 12 * HOUR_IN_SECONDS );
	}
	?>
	<div class="font-awesome-picker">
		<?php
		foreach ( $icons as $icon => $code ) {
			?>
			<div class="c4" data-value="<?php echo $icon; ?>"><div><i class="fa <?php echo $icon; ?>"></i><?php if ( $display_name ) echo $icon; ?></div></div>
			<?php
		}
		?>
	</div>
	<?php
}

add_filter( 'body_class', 'bavotasan_body_class' );
/**
 * Add body class
 *
 * @since 1.0.0
 */
function bavotasan_body_class( $classes ) {
    global $paged;
    if ( is_front_page() && 2 > $paged )
    	$classes[] = 'only-on-home';

   	$classes[] = 'basic';

	return $classes;
}

add_filter( 'post_class', 'bavotasan_post_class' );
/**
 * Add post class
 *
 * @since 1.0.0
 */
function bavotasan_post_class( $classes ) {
   	$classes[] = 'xfolkentry';

	return $classes;
}