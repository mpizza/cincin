<?php
/**
 * Template Name: simple page
 * Description: no sidebar, comment
 * @package arcade-basic
 * @since 1.0.0
 */
get_header();
?>

	<div class="container">
		<div class="row">
			<div cliass="col-md-12">
				<?php
				while ( have_posts() ) : the_post();
					?>
					<article class="simplePage" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1><?php the_title(); ?></h1>

					    <div class="home-jumbotron jumbotron">
						    <?php the_content( __( 'Read more', 'arcade') ); ?>
					    </div><!-- .entry-content -->

					    <?php get_template_part( 'content', 'footer' ); ?>
					</article><!-- #post-<?php the_ID(); ?> -->

					<?php endwhile; ?>
			</div>
		</div>
	</div>

<?php get_footer(); ?>