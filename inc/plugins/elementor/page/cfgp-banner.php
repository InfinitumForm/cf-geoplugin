<?php if (!defined('WPINC') || !defined('ABSPATH')) {
    die("Don't try to trick us. We know who you are!");
}

/**
 * CF Geo Banner page
 *
 * @package Geo Controller
 *
 * @since 7.12.14
 *
 * @version 0.0.1
 *
 * @author Ivijan-Stefan Stipic
 *
 * @url https://infinitumform.com
 */
global $post, $wp_query, $current_user;
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class('geo-banner-body'); ?>>
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="geo-banner geo-banner-<?php the_ID(); ?>" id="geo-banner-page">
		<?php the_content(); ?>
	</div>
	<?php endwhile; endif;
wp_footer(); ?>
</body>
</html>
