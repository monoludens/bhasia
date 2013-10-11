<?php get_header(); ?>



	<?php if(have_posts()) : the_post(); ?>

		<?php
		$links = afdm_get_links();
		$videos = afdm_get_videos();
		$images = afdm_get_artwork_images();
		$featured_video_id = afdm_get_featured_video_id();
		$dimensions = afdm_get_artwork_dimensions();
		$creation_date = afdm_get_creation_date();
		$termination_date = afdm_get_termination_date();
		?>

		<?php jeo_map(); ?>

		<div class="single-post-container">
			<section id="content" class="single-post">
				<header class="single-post-header clearfix">
					<h1><?php the_title(); ?></h1>
					<?php endif; ?>
				</header>
				<p><?php the_content(); ?></p>
			</section>
		</div>
	<?php endif; ?>

<?php get_footer(); ?>
