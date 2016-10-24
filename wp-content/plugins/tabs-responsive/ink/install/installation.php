<?php 
add_action('plugins_loaded', 'wpsm_tabs_r_tr');
function wpsm_tabs_r_tr() {
	load_plugin_textdomain( wpshopmart_tabs_r_text_domain, FALSE, dirname( plugin_basename(__FILE__)).'/languages/' );
}

function wpsm_tabs_r_front_script() {
    
		wp_enqueue_script('jquery');
		
		//font awesome css
		wp_enqueue_style('wpsm_tabs_r-font-awesome-front', wpshopmart_tabs_r_directory_url.'assets/css/font-awesome/css/font-awesome.min.css');
		wp_enqueue_style('wpsm_tabs_r_bootstrap-front', wpshopmart_tabs_r_directory_url.'assets/css/bootstrap-front.css');
		wp_enqueue_style('wpsm_tabs_r_animate', wpshopmart_tabs_r_directory_url.'assets/css/animate.css');
		
		wp_enqueue_script( 'wpsm_tabs_r_bootstrap-js-front', wpshopmart_tabs_r_directory_url.'assets/js/bootstrap.js', array(), '', true );
		
}

add_action( 'wp_enqueue_scripts', 'wpsm_tabs_r_front_script' );
add_filter( 'widget_text', 'do_shortcode');

add_action('media_buttons_context', 'wpsm_tabs_r_editor_popup_content_button');
add_action('admin_footer', 'wpsm_tabs_r_editor_popup_content');

function wpsm_tabs_r_editor_popup_content_button($context) {
 $img = wpshopmart_tabs_r_directory_url.'assets/images/tabs_48.png';
  $container_id = 'TABS_R';
  $title = 'Select Tabs to insert into post';
  $context .= '<style>.wp_tabs_r_shortcode_button {
				background: #11CAA5 !important;
				border-color: #11CAA5 #11CAA5 #11CAA5 !important;
				-webkit-box-shadow: 0 1px 0 #11CAA5 !important;
				box-shadow: 0 1px 0 #11CAA5 !important;
				color: #fff;
				text-decoration: none;
				text-shadow: 0 -1px 1px #11CAA5 ,1px 0 1px #11CAA5,0 1px 1px #11CAA5,-1px 0 1px #11CAA5 !important;
			    }</style>
			    <a class="button button-primary wp_tabs_r_shortcode_button thickbox" title="Select Tabs to insert into post"    href="#TB_inline?width=400&inlineId='.$container_id.'">
					<span class="wp-media-buttons-icon" style="background: url('.$img.'); background-repeat: no-repeat; background-position: left bottom;"></span>
				Tabs Responsive Shortcode
				</a>';
  return $context;
}

function wpsm_tabs_r_editor_popup_content() {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#wpsm_tabs_r_insert').on('click', function() {
			var id = jQuery('#wpsm_tabs_r_insertselect option:selected').val();
			window.send_to_editor('<p>[TABS_R id=' + id + ']</p>');
			tb_remove();
		})
	});
	</script>
<style>
.wp_tabs_r_shortcode_button {
    background: #11CAA5; !important;
    border-color: #11CAA5; #11CAA5 #11CAA5 !important;
    -webkit-box-shadow: 0 1px 0 #11CAA5 !important;
    box-shadow: 0 1px 0 #11CAA5 !important;
    color: #fff !important;
    text-decoration: none;
    text-shadow: 0 -1px 1px #11CAA5 ,1px 0 1px #11CAA5,0 1px 1px #11CAA5,-1px 0 1px #11CAA5 !important;
}
</style>
	<div id="TABS_R" style="display:none;">
	  <h3>Select Tabs To Insert Into Post</h3>
	  <?php 
		
		$all_posts = wp_count_posts( 'tabs_responsive')->publish;
		$args = array('post_type' => 'tabs_responsive', 'posts_per_page' =>$all_posts);
		global $All_rac;
		$All_rac = new WP_Query( $args );			
		if( $All_rac->have_posts() ) { ?>	
			<select id="wpsm_tabs_r_insertselect" style="width: 100%;margin-bottom: 20px;">
				<?php
				while ( $All_rac->have_posts() ) : $All_rac->the_post(); ?>
				<?php $title = get_the_title(); ?>
				<option value="<?php echo get_the_ID(); ?>"><?php if (strlen($title) == 0) echo 'No Title Found'; else echo $title;   ?></option>
				<?php
				endwhile; 
				?>
			</select>
			<button class='button primary wp_tabs_r_shortcode_button' id='wpsm_tabs_r_insert'><?php _e('Insert Tabs Shortcode', wpshopmart_tabs_r_text_domain); ?></button>
			<?php
		} else {
			_e('No Tabs Found', wpshopmart_tabs_r_text_domain);
		}
		?>
	</div>
	<?php
}
?>