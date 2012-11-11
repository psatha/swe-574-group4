<?php
/*
Plugin Name: Simple Wordpress Gallery
Plugin URI: http://shaneandpeter.com
Description: Adds a gallery shortcode with way more options than the standard WordPress gallery shortcode.
Author: Shane and Peter
Version: 1.1 Beta
Author URI: http://shaneandpeter.com

Content copyrighted and commisioned by: Shawn and Peter
Contributors: Aaron Harun, http://aahacreative.com

*/ 

if( !class_exists( 'SP_Gallery' ) ) {
	class SP_Gallery {

		private $url;

		private $large_width = 589; //Default value
		private $large_height = 430; //Default value
		private $small_width = 48; //Default value
		private $small_height = 48; //Default value
		private $folder; //Default value
		private $options = array(); //
		private $pluginDomain = 'simple_gallery'; //
		private $supportUrl = 'http://support.makedesignnotwar.com/categories/simple-wordpress-gallery';

		private static $_meta_inGallery = '_sp_gallery_image';

		public function __construct() {
			$this->addActions();
			$this->addFilters();
			$this->addImageSize();
			// customize this if not in the template's plugins/gallery dir
			$this->url = WP_PLUGIN_URL . '/'.end(explode('/',str_replace('\\','/',dirname(__file__))));

			$this->options = get_option('sp_gallery');

			if($this->options['large_height']) //Custom values have been set. Overide.
				$this->large_height = $this->options['large_height'];

			if($this->options['large_width']) //Custom values have been set. Overide.
				$this->large_width = $this->options['large_width'];

			if($this->options['small_height']) //Custom values have been set. Overide.
				$this->small_height = $this->options['small_height'];

			if($this->options['small_width']) //Custom values have been set. Overide.
				$this->small_width = $this->options['small_width'];
		}

		private function addImageSize() {
			add_image_size('sp-gallery-thumb', $this->small_width, $this->small_height, true);
			add_image_size('sp-gallery-large', $this->large_width, $this->large_height );
		}

		private function addActions() {
			add_action('admin_init', array($this,'enqueueAdminCss'));
			add_action('wp_head', array($this, 'enqueueIECss'));
			add_action('template_redirect', array($this, 'enqueueFrontEnd'));
			add_action('admin_menu', array($this,'admin_setup'));
		}

		private function addFilters() {
			add_filter('attachment_fields_to_edit', array($this,'galleryImageFormFields'), 100, 2);
			add_filter('attachment_fields_to_save', array($this,'galleryImageFormSave'), 10, 2 );
			add_filter('post_gallery', array($this,'gallery'),100,2);
		}

		public function enqueueFrontEnd() {
			if (is_singular() || $this->archive_has_gallery()){
				wp_register_script('jquery-cycle', $this->url.'/resources/jquery.cycle.min.js', array('jquery'), '2.86', true );
				wp_enqueue_script('sp-gallery', $this->url.'/resources/sp-gallery.js', array('jquery-cycle'), '', true );
				wp_enqueue_style('sp-gallery', $this->url.'/resources/sp-gallery.css');
			}
		}

		public function enqueueAdminCss() {
			wp_enqueue_style('sp-gallery-admin', $this->url.'/resources/sp-gallery-admin.css');
		}

		public function enqueueIECss() {
			if (is_singular() || $this->archive_has_gallery()) {
				echo '<!--[if lt IE 8]><link rel="stylesheet" href="'.$this->url.'/resources/ie7.css" type="text/css" media="screen"><![endif]-->'."\n";
				echo '<!--[if IE 8]><link rel="stylesheet" href="'.$this->url.'/resources/ie8.css" type="text/css" media="screen"><![endif]-->'."\n";
			}
		}

		private function archive_has_gallery(){
				global $wp_query;

				if($wp_query->posts)
					foreach($wp_query->posts as $post)
						if(strpos($post->post_content,'[gallery') !== false)
							return true;

		}
		public function galleryImageFormFields($form_fields, $post) {
			if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
				$inGallery = get_post_meta($post->ID, self::$_meta_inGallery, true);
				if( '' === $inGallery ) {
					$inGallery = 1;
				} else {
					$inGallery = ('yes' == $inGallery) ? 1 : 0;
				}

				ob_start();
				include('views/image-options.php');
				$html = ob_get_clean();
				$form_fields['include-in-gallery'] = array('label'=>__('Include in Gallery'),'input'=>'html','html'=>$html);
			}
			return $form_fields;
		}

		public function galleryImageFormSave($post, $attachment) {
			if( isset( $attachment['in-gallery'] ) ) {
				$value = intval( $attachment['in-gallery']);
				$value = (1 === $value) ? 'yes' : 'no';
				update_post_meta($post['ID'],self::$_meta_inGallery,$value);
			}
			return $post;
		}

		public function gallery($content = '', $attr) {
			global $post;

			$options = get_option("sp_gallery");
			$options['timeout'] = ($options['timeout'] > 0) ? $options['timeout'] : 0; //Anything non-numeric needs to be set to 0

			// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
			if ( isset( $attr['orderby'] ) ) {
				$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
				if ( !$attr['orderby'] )
					unset( $attr['orderby'] );
			}

			extract(shortcode_atts(array(
				'order'      => 'DESC', //Mysql statement
				'orderby'    => 'menu_order ID', //duh
				'id'         => $post->ID, // The id of the current post
				'include'    => '', //comma separated list of specific images to include
				'exclude'    => '', //same but exclude
				'width'    => $this->large_width, //A custom width
				'height'    => $this->large_height, //a custom height
				'thumb_width'    => $this->small_width, //thumb width
				'thumb_height'    => $this->small_height, //thumb height
				'mode'    => 'normal', // 'slider' hides all controls
				'timeout'    => $options['timeout'] , //length of time between slides
				'speed'    => 1500, //length of transition
				'default' => $options['default'], //Show default WP Gallery
				'lightbox' => $options['lightbox']
			), $attr));

			if($default == 'yes')
				return;

			if ( 'RAND' == $order )
				$orderby = 'none';

			if($order != 'ASC' && $order != 'DESC')
				$order = 'ASC';

			$images = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

			$excludedAttachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby, 'meta_key' => self::$_meta_inGallery, 'meta_value' => 'no') );

			$excludedIds = array();

			if($exclude != '')
				$excludedIds = explode(',', $exclude);

			foreach($excludedAttachments as $excludedGalleryItem) {
				$excludedIds[] = $excludedGalleryItem->ID;
			}

			foreach($images as $key => $galleryItem) {
				if( in_array($galleryItem->ID,$excludedIds) ) {
					unset($images[$key]);
				}
			}


			if ( empty($images) ) {
				return '';
			}

			// grab info from first photo for initial state below
			$first_gal = current($images);
			$gal_title = $first_gal->post_title;
			$gal_caption = $first_gal->post_excerpt;
			$gal_description = $first_gal->post_content;

			if($gal_caption)
				$hide_title = 'style="display:none;"';

			


			// loading animation.
			$loading = '';

			$id = $post->ID;
			// set up gallery
			ob_start();
				include('views/gallery-template.php');
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

	function admin_setup() {
		add_submenu_page('options-general.php', 'Simple Wordpress Gallery', 'Simple Wordpress Gallery', 8, basename(__FILE__), array($this,'admin'));
	}

	function admin() {
		$options = get_option("sp_gallery");

		if ( isset($_POST['sp_gallerysubmit']) ) {
			$options = $_POST['sp_gallery'];
			$this->large_width = $options['large_width'];
			$this->large_height = $options['large_height'];
			$this->small_width = $options['small_width'];
			$this->small_height = $options['small_height'];
			update_option('sp_gallery', $options);

		}
	?>
	<div class="wrap">
			<h2><?=__('Simple Wordpress Gallery',$this->pluginDomain);?></h2>
		<div class="form">
			<h3><?php _e('Need a hand?',$this->pluginDomain); ?></h3>
			<p><?php printf( __( 'If you’re stuck on these options, please <a href="%s">check out the documentation</a>. Or, go to the <a href="%s">support forum</a>.', $this->pluginDomain ), trailingslashit($this->url) . 'docs/Main_Documentation.html', $this->supportUrl ); ?></p>

			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

			<h3><?php _e('Basic Settings', $this->pluginDomain); ?></h3>
			<table class="form-table">

				<tr>
					<th scope="row"><?php _e('Use the default WordPress gallery by default?',$this->pluginDomain); ?></th>
				<td>
					<fieldset>
					<span style="margin-left:20px;" >
						<label><input type="radio" name="sp_gallery[default]" value="yes" <?php if($options['default'] == "yes"){ ?> checked="checked" <?php } ?>> <?php _e('Yes',$this->pluginDomain); ?></label>
						<label><input type="radio" name="sp_gallery[default]" value="no" <?php if($options['default'] != "yes"){ ?> checked="checked" <?php } ?>> <?php _e('No',$this->pluginDomain); ?></label>
					</span>
			<br />
					</fieldset>
				</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Gallery Image Size',$this->pluginDomain); ?></th>
				<td>
					<fieldset>
					<span style="margin-left:20px;" >
						<input type="text" name="sp_gallery[large_width]" value="<?php echo $this->large_width ?>" size=4> <?php _e('wide',$this->pluginDomain); ?><?php _e(' by ', $this->pluginDomain); ?> 
						<input type="text" name="sp_gallery[large_height]" value="<?php echo $this->large_height ?>" size=4> <?php _e('tall',$this->pluginDomain); ?> <?php _e('(number)', $this->pluginDomain); ?> 
					</span>
			<br />
					</fieldset>
				</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Gallery Thumb Size',$this->pluginDomain); ?></th>
				<td>
					<fieldset>
					<span style="margin-left:20px;" >
						<input type="text" name="sp_gallery[small_width]" value="<?php echo $this->small_width ?>" size=4> <?php _e('wide',$this->pluginDomain); ?><?php _e(' by ', $this->pluginDomain); ?> 
						<input type="text" name="sp_gallery[small_height]" value="<?php echo $this->small_height ?>" size=4> <?php _e('tall',$this->pluginDomain); ?> <?php _e('(number)', $this->pluginDomain); ?> 
					</span>
			<br />
					</fieldset>
				</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Timeout between slide changes.',$this->pluginDomain); ?></th>
				<td>
					<fieldset>
					<span style="margin-left:20px;" >
						<input type="text" name="sp_gallery[timeout]" value="<?php echo $options['timeout'] ?>" size=5><?php _e('milliseconds. (1000 milliseconds = 1 second) set this to 0 for no scrolling.', $this->pluginDomain); ?> 
					</span>
			<br />
					</fieldset>
				</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Enable Lightbox on all galleries.',$this->pluginDomain); ?></th>
				<td>
					<fieldset>
					<span style="margin-left:20px;" >
						<label><input type="radio" name="sp_gallery[lightbox]" value="yes" <?php if($options['lightbox'] == "yes"){ ?> checked="checked" <?php } ?>> <?php _e('Yes',$this->pluginDomain); ?></label>
						<label><input type="radio" name="sp_gallery[lightbox]" value="no" <?php if($options['lightbox'] != "yes"){ ?> checked="checked" <?php } ?>> <?php _e('No',$this->pluginDomain); ?></label>
					</span>
			<br />
					</fieldset>
				</td>
				</tr><tr>
			</table>
		<h3><?php _e('Display Settings', $this->pluginDomain); ?></h3>
			<table class="form-table">

					<th scope="row"><?php _e('Start with thumbs collapsed?',$this->pluginDomain); ?></th>
				<td>
					<fieldset>
					<span style="margin-left:20px;" >
						<label><input type="radio" name="sp_gallery[collapse_thumbs]" value="yes" <?php if($options['collapse_thumbs'] == "yes"){ ?> checked="checked" <?php } ?>> <?php _e('Yes',$this->pluginDomain); ?></label>
						<label><input type="radio" name="sp_gallery[collapse_thumbs]" value="no" <?php if($options['collapse_thumbs'] != "yes"){ ?> checked="checked" <?php } ?>> <?php _e('No',$this->pluginDomain); ?></label>
					</span>
			<br />
					</fieldset>
				</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Hide all Image Meta (title and description)?',$this->pluginDomain); ?></th>
				<td>
					<fieldset>
					<span style="margin-left:20px;" >
						<label><input type="radio" name="sp_gallery[hide_meta]" value="yes" <?php if($options['hide_meta'] == "yes"){ ?> checked="checked" <?php } ?>> <?php _e('Yes',$this->pluginDomain); ?></label>
						<label><input type="radio" name="sp_gallery[hide_meta]" value="no" <?php if($options['hide_meta'] != "yes"){ ?> checked="checked" <?php } ?>> <?php _e('No',$this->pluginDomain); ?></label>
					</span>
			<br />
					</fieldset>
				</td>
				</tr><tr>
					<th scope="row"><?php _e('Hide image descriptions?',$this->pluginDomain); ?></th>
				<td>
					<fieldset>
					<span style="margin-left:20px;" >
						<label><input type="radio" name="sp_gallery[hide_descriptions]" value="yes" <?php if($options['hide_descriptions'] == "yes"){ ?> checked="checked" <?php } ?>> <?php _e('Yes',$this->pluginDomain); ?></label>
						<label><input type="radio" name="sp_gallery[hide_descriptions]" value="no" <?php if($options['hide_descriptions'] != "yes"){ ?> checked="checked" <?php } ?>> <?php _e('No',$this->pluginDomain); ?></label>
					</span>
			<br />
					</fieldset>
				</td>
				</tr>
				<tr>
				<td>
					<input id="sp_gallerysubmit" class="button-primary" type="submit" name="sp_gallerysubmit" value="<?php _e('Save Changes', $this->pluginDomain); ?>" />
				</td>
			</tr>
		</table>

		</form>

	</div>
	<?php
	}

	}

	global $sp_gallery;
	$sp_gallery = new SP_Gallery;

	include('lib/template-tags.php');
}
