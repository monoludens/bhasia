<?php

/*
 * Arte Fora do Museu
 * Artworks
 */

class ArteForaDoMuseu_Artworks {

	var $post_type = 'post';

	var $taxonomy_slugs = array(
		'style' => 'estilos'
	);

	var $directory_uri = '';

	var $directory = '';

	function __construct() {
		$this->set_directories();
		$this->setup_views();
		$this->setup_scripts();
		$this->register_taxonomies();
		$this->setup_meta_boxes();
		$this->hook_ui_elements();
		$this->setup_ajax();
	}

	function set_directories() {
		$this->directory_uri = apply_filters('artguides_directory_uri', get_stylesheet_directory_uri() . '/inc/artworks');
		$this->directory = apply_filters('artguides_directory', get_stylesheet_directory() . '/inc/artworks');
	}

	/*
	 * Add to view system
	 */
	function setup_views() {
		add_action('afdm_views_post_types', array($this, 'register_views'));
	}

	function register_views($post_types) {
		if(!in_array($this->post_type, $post_types))
			$post_types[] = $this->post_type;

		return $post_types;
	}

	/*
	 * Scripts
	 */

	function setup_scripts() {
		add_action('wp_enqueue_scripts', array($this, 'scripts'));
		add_action('mappress_geocode_scripts', array($this, 'geocode_scripts'));
	}

	function scripts() {
		wp_enqueue_script('afdm-artworks', $this->directory_uri . '/js/artworks.js', array('jquery', 'afdm-lightbox', 'jquery-autosize'), '0.0.5');
		wp_localize_script('afdm-artworks', 'artworks', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'sending_msg' => __('Sending data...', 'arteforadomuseu')
		));
	}

	function geocode_scripts() {
		$geocode_service = mappress_get_geocode_service();
		$gmaps_key = mappress_get_gmaps_api_key();
		if($geocode_service == 'gmaps' && $gmaps_key)
			wp_enqueue_script('google-maps-api');
		wp_enqueue_script('mappress.geocode.box');
	}

	/*
	 * Taxonomies
	 */

	function register_taxonomies() {
		add_action('init', array($this, 'taxonomy_style'));
	}

	function taxonomy_style() {

		$labels = array( 
			'name' => __('Styles', 'arteforadomuseu'),
			'singular_name' => __('Style', 'arteforadomuseu'),
			'search_items' => __('Search styles', 'arteforadomuseu'),
			'popular_items' => __('Popular styles', 'arteforadomuseu'),
			'all_items' => __('All styles', 'arteforadomuseu'),
			'parent_item' => __('Parent style', 'arteforadomuseu'),
			'parent_item_colon' => __('Parent style:', 'arteforadomuseu'),
			'edit_item' => __('Edit style', 'arteforadomuseu'),
			'update_item' => __('Update style', 'arteforadomuseu'),
			'add_new_item' => __('Add new style', 'arteforadomuseu'),
			'new_item_name' => __('New style name', 'arteforadomuseu'),
			'separate_items_with_commas' => __('Separate styles with commas', 'arteforadomuseu'),
			'add_or_remove_items' => __('Add or remove styles', 'arteforadomuseu'),
			'choose_from_most_used' => __('Choose from most used styles', 'arteforadomuseu'),
			'menu_name' => __('Styles', 'arteforadomuseu')
		);

		$args = array( 
			'labels' => $labels,
			'public' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'show_tagcloud' => true,
			'hierarchical' => false,
			'rewrite' => array('slug' => $this->taxonomy_slugs['style'], 'with_front' => false),
			'query_var' => true,
			'show_admin_column' => true
		);

		register_taxonomy('style', array($this->post_type), $args);
	}

	/*
	 * Meta boxes
	 */

	function setup_meta_boxes() {
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_artwork'));
		add_action('admin_footer', array($this, 'admin_css'));
	}

	function admin_css() {
		wp_enqueue_style('artwork-admin', $this->directory_uri . '/css/admin.css');
	}

	// Add meta boxes
	function add_meta_boxes() {
		// Dimensions
		add_meta_box(
			'artwork_dimensions',
			__('Artwork dimensions', 'arteforadomuseu'),
			array($this, 'box_artwork_dimensions'),
			$this->post_type,
			'advanced',
			'high'
		);

		// Dates
		add_meta_box(
			'artwork_dates',
			__('Artwork dates', 'arteforadomuseu'),
			array($this, 'box_artwork_dates'),
			$this->post_type,
			'advanced',
			'high'
		);

		// Videos
		add_meta_box(
			'artwork_videos',
			__('Videos', 'arteforadomuseu'),
			array($this, 'box_artwork_videos'),
			$this->post_type,
			'advanced',
			'high'
		);

		// Links
		add_meta_box(
			'artwork_links',
			__('Links', 'arteforadomuseu'),
			array($this, 'box_artwork_links'),
			$this->post_type,
			'advanced',
			'high'
		);
	}

	function save_artwork($post_id) {

		if(get_post_type($post_id) != $this->post_type)
			return;

		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		if (defined('DOING_AJAX') && DOING_AJAX && !(defined('ALLOWED_AJAX') && ALLOWED_AJAX))
			return;

		if (false !== wp_is_post_revision($post_id))
			return;

		$this->save_artwork_dimensions($post_id);
		$this->save_artwork_dates($post_id);
		$this->save_artwork_videos($post_id);
		$this->save_artwork_links($post_id);
	}

	function box_artwork_dimensions($post = false) {
		if($post) {
			$width = $this->get_artwork_width();
			$height = $this->get_artwork_height();
		}
		?>
		<div id="artwork_dimensions_box">
			<h4><?php _e('Artwork dimensions', 'arteforadomuseu'); ?></h4>
			<div class="box-inputs">
				<p class="input-container dimensions-width">
					<input placeholder="<?php _e('Width', 'arteforadomuseu'); ?>" type="text" name="artwork_dimensions_width" id="artwork_dimensions_width" value="<?php echo $width; ?>" />
					<label for="artwork_dimensions_width"><?php _e('cm', 'arteforadomuseu'); ?></label>
				</p>
				<p class="input-container dimensions-height">
					<input placeholder="<?php _e('Height', 'arteforadomuseu'); ?>" type="text" name="artwork_dimensions_height" id="artwork_dimensions_height" value="<?php echo $height; ?>" />
					<label for="artwork_dimensions_height"><?php _e('cm', 'arteforadomuseu'); ?></label>
				</p>
			</div>
		</div>
		<?php
	}

	function save_artwork_dimensions($post_id) {

		if(isset($_POST['artwork_dimensions_width'])) {
			update_post_meta($post_id, 'artwork_width', $_POST['artwork_dimensions_width']);
		}
		if(isset($_POST['artwork_dimensions_height'])) {
			update_post_meta($post_id, 'artwork_height', $_POST['artwork_dimensions_height']);
		}
	}

	function box_artwork_dates($post = false) {

		wp_enqueue_style('jquery-ui-smoothness', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
		wp_enqueue_script('artworks-box-dates', $this->directory_uri . '/js/artworks.box.dates.js', array('jquery', 'jquery-ui-datepicker', 'jquery-ui-datepicker-pt-BR'), '0.0.5');
		wp_localize_script('artworks-box-dates', 'box_dates_settings', array(
			'dateFormat' => 'dd/mm/yy',
			'language' => get_bloginfo('language')
		));

		if($post) {
			$creation_date = $this->get_artwork_creation_date();
			$termination_date = $this->get_artwork_termination_date();
			$currently_active = $this->is_artwork_currently_active();
		}
		?>
		<div id="artwork_dates_box">
			<h4><?php _e('Creation and termination dates', 'arteforadomuseu'); ?></h4>
			<div class="box-inputs">
				<p class="input-container creation-date">
					<input placeholder="<?php _e('Creation date', 'arteforadomuseu'); ?>" class="datepicker" type="text" name="artwork_date_creation" id="artwork_date_creation" value="<?php echo $creation_date; ?>" />
				</p>
				<p class="input-container termination-date">
					<input placeholder="<?php _e('Termination date', 'arteforadomuseu'); ?>" class="datepicker" type="text" name="artwork_date_termination" id="artwork_date_termination" value="<?php echo $termination_date; ?>" />
				</p>
				<p class="input-container currently-active">
					<input type="checkbox" name="artwork_currently_active" id="artwork_currently_active" <?php if($currently_active) echo 'checked'; ?> /> <label for="artwork_currently_active"><?php _e('Currently active', 'arteforadomuseu'); ?></label>
				</p>
			</div>
		</div>
		<?php
	}

	function save_artwork_dates($post_id) {

		if(isset($_POST['artwork_date_creation'])) {
			update_post_meta($post_id, 'artwork_date_creation', $_POST['artwork_date_creation']);
		}
		if(isset($_POST['artwork_date_termination'])) {
			update_post_meta($post_id, 'artwork_date_termination', $_POST['artwork_date_termination']);
		}
		if(isset($_POST['artwork_currently_active'])) {
			update_post_meta($post_id, 'artwork_currently_active', 1);
		} else {
			delete_post_meta($post_id, 'artwork_currently_active');
		}
	}

	function box_artwork_videos($post = false) {

		wp_enqueue_script('artworks-box-videos', $this->directory_uri . '/js/artworks.box.videos.js', array('jquery'), '0.0.2');

		if($post) {
			$videos = $this->get_artwork_videos();
			$featured_video = $this->get_artwork_featured_video();
		}

		?>
		<div id="artwork_videos_box" class="loop-box">
			<h4><?php _e('Videos', 'arteforadomuseu'); ?></h4>
			<p class="tip"><?php _e('Video URLs from YouTube, Vimeo, Blip.tv, Dailymotion, Qik or Flickr', 'arteforadomuseu'); ?></p>
			<a class="new-video button new-button secondary" href="#"><?php _e('Add video', 'arteforadomuseu'); ?></a>
			<div class="box-inputs">
				<ul class="video-template" style="display:none;">
					<li class="template">
						<?php $this->video_input_template(); ?>
					</li>
				</ul>
				<ul class="video-list">
					<?php if($videos) : foreach($videos as $video) : ?>
						<li>
							<?php
							$featured = ($featured_video == $video['id']);
							$this->video_input_template($video['id'], $video['url'], $featured);
							?>
						</li>
					<?php endforeach; endif; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	function video_input_template($id = false, $url = false, $featured = false) {
		?>
			<p class="input-container video-url main-input">
				<input type="text" class="video-input" size="60" <?php if($id) echo 'name="videos[' . $id . '][url]"'; ?> <?php if($url) echo 'value="' . $url . '"'; ?> placeholder="<?php _e('Video url', 'arteforadomuseu'); ?>" />
			</p>
			<p class="input-container featured">
				<input type="radio" <?php if($id) echo 'value="' . $id . '" id="featured_video_' . $id . '"'; ?> name="featured_video" class="featured-input" <?php if($featured) echo 'checked'; ?> /> <label <?php if($id) echo 'for="featured_video_' . $id . '"'; ?> class="featured-label"><?php _e('Featured', 'arteforadomuseu'); ?></label>
			</p>
			<input type="hidden" class="video-id" <?php if($id) echo 'name="videos[' . $id . '][id]" value="' . $id . '"'; ?> />
			<a class="remove-video button remove" href="#"><?php _e('Remove', 'arteforadomuseu'); ?></a>
		<?php
	}

	function save_artwork_videos($post_id) {

		if(isset($_POST['videos'])) {
			update_post_meta($post_id, 'artwork_videos', $_POST['videos']);
		}

		if(isset($_POST['featured_video'])) {
			update_post_meta($post_id, 'artwork_featured_video', $_POST['featured_video']);
		}

	}

	function box_artwork_links($post = false) {

		wp_enqueue_script('artworks-box-links', $this->directory_uri . '/js/artworks.box.links.js', array('jquery'), '0.0.2');

		if($post) {
			$links = $this->get_artwork_links();
			$featured_link = $this->get_artwork_featured_link();
		}

		?>
		<div id="artwork_links_box" class="loop-box">
			<h4><?php _e('Links', 'arteforadomuseu'); ?></h4>
			<p class="tip"><?php _e('Links related to this artwork', 'arteforadomuseu'); ?></p>
			<a class="new-link new-button button secondary" href="#"><?php _e('Add link', 'arteforadomuseu'); ?></a>
			<div class="box-inputs">
				<ul class="link-template" style="display:none;">
					<li class="template">
						<?php $this->link_input_template(); ?>
					</li>
				</ul>
				<ul class="link-list">
					<?php if($links) : foreach($links as $link) : ?>
						<li>
							<?php
							$featured = ($featured_link == $link['id']);
							$this->link_input_template($link['id'], $link['title'], $link['url'], $featured);
							?>
						</li>
					<?php endforeach; endif; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	function link_input_template($id = false, $title = false, $url = false, $featured = false) {
		?>
			<p class="input-container link main-input">
				<input type="text" class="link-title" size="30" <?php if($id) echo 'name="artwork_links[' . $id . '][title]"'; ?> <?php if($title) echo 'value="' . $title . '"'; ?> placeholder="<?php _e('Link title', 'arteforadomuseu'); ?>" />
				<input type="text" class="link-url" size="40" <?php if($id) echo 'name="artwork_links[' . $id . '][url]"'; ?> <?php if($url) echo 'value="' . $url . '"'; ?> placeholder="<?php _e('Link url', 'arteforadomuseu'); ?>" />
			</p>
			<p class="input-container featured">
				<input type="radio" <?php if($id) echo 'value="' . $id . '" id="featured_link_' . $id . '"'; ?> name="featured_link" class="featured-input" <?php if($featured) echo 'checked'; ?> /> <label <?php if($id) echo 'for="featured_link_' . $id . '"'; ?> class="featured-label"><?php _e('Featured', 'arteforadomuseu'); ?></label>
			</p>
			<input type="hidden" class="link-id" <?php if($id) echo 'name="artwork_links[' . $id . '][id]" value="' . $id . '"'; ?> />
			<a class="remove-link button remove" href="#"><?php _e('Remove', 'arteforadomuseu'); ?></a>
		<?php
	}

	function save_artwork_links($post_id) {

		if(isset($_POST['artwork_links'])) {
			update_post_meta($post_id, 'artwork_links', $_POST['artwork_links']);
		} else {
			delete_post_meta($post_id, 'artwork_links');
		}

		if(isset($_POST['featured_link'])) {
			update_post_meta($post_id, 'artwork_featured_link', $_POST['featured_link']);
		} else {
			delete_post_meta($post_id, 'artwork_featured_link');
		}

	}

	/*
	 * Taxonomy boxes, for non-admin dashboard usage only
	 */

	function box_artwork_styles($post = false) {

		wp_enqueue_script('jquery-tag-it');
		wp_enqueue_style('jquery-tag-it');

		if($post) {
			$post_style_names = $this->get_artwork_style_names();
		}

		$styles = get_terms('style', array('hide_empty' => 0));
		$style_names = array();
		if($styles) {
			foreach($styles as $style) {
				$style_names[] = $style->name;
			}
		}
		?>
		<div id="artwork_styles_box">
			<h4><?php _e('Tag styles for this artwork', 'arteforadomuseu'); ?></h4>
			<div class="box-inputs">
				<ul id="style-tags">
					<?php
					if($post_style_names) {
						foreach($post_style_names as $style_name) {
							echo '<li>' . $style_name . '</li>';
						}
					}
					?>
				</ul>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('#style-tags').tagit({
							fieldName: 'styles',
							tagLimit: 5,
							availableTags: <?php echo json_encode($style_names); ?>,
							autocomplete: { delay: 0, minLength: 2 },
							allowSpaces: true,
							caseSensitive: false
						});
					});
				</script>
			</div>
		</div>
		<?php
	}

	function save_artwork_styles($post_id) {

		/*
		 * TO DO
		 */

	}

	function box_artwork_categories($post = false) {

		if($post) {
			$category = array_shift(get_the_category($post->ID));
			$category_id = $category->term_id;
		}

		$categories = get_categories(array('hide_empty' => 0));

		if(!$categories)
			return false;

		?>
		<div id="artwork_categories_box">
			<h4><?php _e('Select a category', 'arteforadomuseu'); ?></h4>
			<div class="box-inputs">
				<select id="artwork_categories_select" name="categories">
					<option></option>
					<?php foreach($categories as $category) : ?>
						<option value="<?php echo $category->term_id; ?>" <?php if($category->term_id == $category_id) echo 'selected'; ?>><?php echo $category->name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
	}

	function save_artwork_categories($post_id) {

		/*
		 * TO DO
		 */

	}

	function box_artwork_images($post = false) {

		wp_enqueue_script('artworks-box-images', $this->directory_uri . '/js/artworks.box.images.js', array('jquery'), '0.0.2');

		if($post) {
			$images = $this->get_artwork_images();
			$featured_link = $this->get_artwork_featured_image();
		}

		?>
		<div id="artwork_images_box" class="loop-box">
			<h4><?php _e('Images', 'arteforadomuseu'); ?></h4>
			<p class="tip"><?php _e('Pictures for this artwork', 'arteforadomuseu'); ?></p>
			<a class="new-image new-button button secondary" href="#"><?php _e('Add image', 'arteforadomuseu'); ?></a>
			<div class="box-inputs">
				<ul class="image-template" style="display:none;">
					<li class="template">
						<?php $this->image_input_template(); ?>
					</li>
				</ul>
				<ul class="image-list">
					<?php if($images) : foreach($images as $image) : ?>
						<li>
							<?php
							$featured = ($featured_link == $image['id']);
							$this->image_input_template($image['id'], $image['title'], $image['thumb_url'], $featured);
							?>
						</li>
					<?php endforeach; endif; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	function image_input_template($id = false, $title = false, $thumb_url = false, $featured = false) {
		?>
			<p class="input-container image main-input">
				<input type="text" class="image-title" size="30" <?php if($id) echo 'name="artwork_images[' . $id . '][title]"'; ?> <?php if($title) echo 'value="' . $title . '"'; ?> placeholder="<?php _e('Image title', 'arteforadomuseu'); ?>" />
				<input type="file" class="image-file" size="40" <?php if($id) echo 'name="artwork_images[' . $id . '][url]"'; ?> <?php if($thumb_url) echo 'value="' . $thumb_url . '"'; ?> placeholder="<?php _e('Image file', 'arteforadomuseu'); ?>" />
			</p>
			<p class="input-container featured">
				<input type="radio" <?php if($id) echo 'value="' . $id . '" id="featured_image_' . $id . '"'; ?> name="featured_image" class="featured-input" <?php if($featured) echo 'checked'; ?> /> <label <?php if($id) echo 'for="featured_image_' . $id . '"'; ?> class="featured-label"><?php _e('Featured', 'arteforadomuseu'); ?></label>
			</p>
			<input type="hidden" class="image-id" <?php if($id) echo 'name="artwork_images[' . $id . '][id]" value="' . $id . '"'; ?> />
			<a class="remove-image button remove" href="#"><?php _e('Remove', 'arteforadomuseu'); ?></a>
		<?php
	}

	function save_artwork_images($post_id) {

		/*
		 * TO DO
		 */

	}

	/*
	 * UI
	 */

	function hook_ui_elements() {
		if(current_user_can('edit_posts')) { 
			add_action('afdm_logged_in_user_menu_items', array($this, 'user_menu_items'));
			add_action('wp_footer', array($this, 'add_artwork_box'));
		}
	}

	function user_menu_items() {
		?>
		<li><a href="#" class="add_artwork"><?php _e('Submit an artwork', 'arteforadomuseu'); ?></a></li>
		<?php
	}

	function add_artwork_box() {
		?>
		<div id="add_artwork">
			<h2 class="lightbox_title"><span class="lsf">addnew</span> <?php _e('Submit new artwork', 'arteforadomuseu'); ?></h2>
			<div class="lightbox_content">
				<form id="new_artwork">
					<div class="form-inputs">
						<?php $this->artwork_form_inputs(); ?>
					</div>
					<div class="form-actions">
						<input type="submit" value="<?php _e('Submit', 'arteforadomuseu'); ?>" />
						<a class="close button secondary" href="#"><?php _e('Cancel', 'arteforadomuseu'); ?></a>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	function artwork_form_inputs($post = false) {
		?>
		<input type="text" name="title" class="title" placeholder="<?php _e('Title', 'arteforadomuseu'); ?>" />
		<textarea name="content" placeholder="<?php _e('Description', 'arteforadomuseu'); ?>"></textarea>
		<div class="clearfix">
			<div class="two-thirds-1">
				<div class="categories">
					<?php $this->box_artwork_styles($post); ?>
					<?php $this->box_artwork_categories($post); ?>
				</div>
			</div>
			<div class="one-third-2">
				<?php $this->box_artwork_dimensions($post); ?>
			</div>
		</div>
		<h3><?php _e('Multimedia', 'arteforadomuseu'); ?></h3>
		<div class="multimedia form-section">
			<div class="clearfix">
				<?php $this->box_artwork_videos($post); ?>
			</div>
			<div class="clearfix">
				<?php $this->box_artwork_links($post); ?>
			</div>
			<div class="clearfix">
				<?php $this->box_artwork_images($post); ?>
			</div>
		</div>
		<div class="clearfix">
			<?php $this->box_artwork_dates($post); ?>
		</div>
		<div class="clearfix">
			<?php mappress_geocode_box($post); ?>
		</div>
		<?php
	}

	/*
	 * Ajax stuff
	 */
	function setup_ajax() {
		add_action('wp_ajax_nopriv_submit_artwork', array($this, 'ajax_add_artwork'));
		add_action('wp_ajax_submit_artwork', array($this, 'ajax_add_artwork'));
	}

	function ajax_response($data) {
		header('Content Type: application/json');
		echo json_encode($data);
		exit;
	}

	function ajax_add_artwork() {
		$this->ajax_response(array('error_msg' => 'Em desenvolvimento'));
	}

	/*
	 * Functions
	 */

	function get_popular($amount = 5) {
		$query = array(
			'post_type' => $this->post_type,
			'posts_per_page' => $amount,
		);
		return get_posts(afdm_get_popular_query($query));
	}

	function get_artwork_width($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_width', true);
	}

	function get_artwork_height($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_height', true);
	}

	function get_artwork_creation_date($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_date_creation', true);
	}

	function get_artwork_termination_date($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_date_termination', true);
	}

	function is_artwork_currently_active($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_currently_active', true);
	}

	function get_artwork_videos($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_videos', true);
	}

	function get_artwork_featured_video($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_featured_video', true);
	}

	function get_artwork_links($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_links', true);
	}

	function get_artwork_featured_link($post_id = false) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		return get_post_meta($post_id, 'artwork_featured_link', true);
	}

	function get_artwork_styles() {
		return false;
	}

	function get_artwork_style_names() {
		return false;
	}

}

$artworks = new ArteForaDoMuseu_Artworks();