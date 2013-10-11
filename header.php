<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<title><?php
	global $page, $paged;

	wp_title( '|', true, 'right' );

	bloginfo( 'name' );

	$site_description = get_bloginfo('description', 'display');
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	if ( $paged >= 2 || $page >= 2 )
		echo ' | Página ' . max($paged, $page);

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/img/favicon.ico" type="image/x-icon" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(get_bloginfo('language')); ?>>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1&appId=450923021667564";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<header id="masthead">
		<div class="container">
			<div class="logo">
				<div class="site-meta">
					<h1>
						<a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('name'); ?>">
							<?php bloginfo('name'); ?>
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/logo-white.png" alt="<?php bloginfo('name'); ?>" class="scale-with-grid" />
						</a>
					</h1>
				</div>
			</div>
			<!-- <div class="two columns">
				<?php afdm_city_selector(); ?>
			&nbsp;</div> -->
			<div class="main-menu">
				<?php if(function_exists('qtrans_getLanguage')) : ?>
					<nav id="langnav">
						<ul>
							<?php
							global $q_config;
							if(is_404()) $url = get_option('home'); else $url = '';
							$current = qtrans_getLanguage();
							foreach($q_config['enabled_languages'] as $language) {
								$attrs = '';
								if($language == $current)
									$attrs = 'class="active"';
								echo '<li><a href="' . qtrans_convertURL($url, $language) . '" ' . $attrs . '>' . $language . '</a></li>';
							}
							?>
						</ul>
					</nav>
				<?php endif; ?>
				<div id="masthead-nav">
					<div class="clearfix">
						<nav id="main-nav">
							<?php wp_nav_menu(array('theme_location' => 'header_menu')); ?>
							<ul>
								<li><a href="<?php echo afdm_artists_get_archive_link(); ?>"><?php _e('Artists', 'arteforadomuseu'); ?></a></li>
								<!--<li><a href="<?php echo afdm_artguides_get_archive_link(); ?>"><?php _e('Art guides', 'arteforadomuseu'); ?></a></li>
								<?php
								$categories = get_categories();
								if($categories) :
									?>
									<li class="categories">
										<?php
										$current_category = array_shift(get_the_category());
										if(is_category() && $current_category) :
											$category_id = $current_category->term_id;
											?>
											<a href="<?php echo get_category_link($category_id); ?>" class="current-menu-item <?php echo $current_category->slug; ?>"><?php echo $current_category->name; ?> <span class="lsf">&#xE03e;</span></a>
										<?php else : ?>
											<a href="#"><?php _e('Categories', 'arteforadomuseu'); ?> <span class="lsf">&#xE03e;</span></a>
										<?php endif; ?>
										<ul class="category-list">
											<?php if($current_category) : ?>
												<li class="tip"><?php _e('Choose another category:', 'arteforadomuseu'); ?></li>
											<?php endif; ?>
											<?php foreach($categories as $category) : ?>
												<?php if($current_category && $category->term_id == $category_id) continue; ?>
												<li>
													<a href="<?php echo get_category_link($category->term_id); ?>" class="<?php echo $category->slug; ?>"><?php echo $category->name; ?></a>
												</li>
											<?php endforeach; ?>
										</ul>
									</li>
								<?php endif; ?>-->
							</ul>
							<?php wp_nav_menu(array('theme_location' => 'footer_menu')); ?>
						</nav>
						<?php get_search_form(); ?>
					</div>
				</div>
			</div>
		</div>
	</header>

	<footer id="colophon">
	<div class="container">
		
		<div class="logo-ruler">
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/logos-footer-04.png" alt="<?php bloginfo('name'); ?>" class="scale-with-grid" />
		</div>
		<div class="stamps">
			<div class="cc">
				<p xmlns:dct="http://purl.org/dc/terms/" xmlns:vcard="http://www.w3.org/2001/vcard-rdf/3.0#">
					<a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">
						<img src="http://i.creativecommons.org/p/zero/1.0/88x31.png" style="border-style: none;" alt="CC0" class="scale-with-grid" />
					</a>
					<span class="text">
						To the extent possible under law,
						<a rel="dct:publisher"
						href="http://bhasia.art.br/">
						<span property="dct:title">BHÁsia</span></a>
						has waived all copyright and related or neighboring rights to
						<span property="dct:title">BHÁsia</span>.
						This work is published from:
						<span property="vcard:Country" datatype="dct:ISO3166"
						content="BR" about="http://bhasia.art.br/">
						Brasil</span>.
					</span>
				</p>
			</div>
			<div class="arteforadomuseu">
					<p><a href="http://www.arteforadomuseu.com.br/">
						<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/arteforadomuseu-logo.png" alt="<?php bloginfo('name'); ?>" alt="Arte Fora do Museu" class="scale-with-grid">
					</a>
				</p>
			</div>
		</div>
		<div>
			<div class="social">
				<div class="social-interaction">
					<a href="https://twitter.com/BHAsia" class="twitter-follow-button" data-show-count="false" data-lang="pt" data-show-screen-name="false">Seguir @arteforadomuseu</a>
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
					<div class="fb-like" data-href="https://www.facebook.com/bhasia2013" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true" data-font="verdana" data-colorscheme="dark"></div>
				</div>
				<div class="social-links">
					<a href="https://www.facebook.com/bhasia2013" class="facebook lsf" rel="external" target="_blank">&#xE047;</a>
					<a href="http://instagram.com/bhasia#" class="instagram lsf" rel="external" target="_blank">&#xE155;</a>
					<a href="https://twitter.com/BHAsia" class="twitter lsf" rel="external" target="_blank">&#xE12f;</a>
					<!-- <a href="http://www.youtube.com/arteforadomuseu" class="youtube lsf" rel="external" target="_blank">&#xE141;</a> -->
				</div>
			</div>
		</div>
	</div>
</footer>