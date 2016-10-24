<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


	

	//FOR QUICK DEBUGGING

	



	if(!function_exists('pre')){
		function pre($data){
			if(isset($_GET['debug'])){
				pree($data);
			}
		}	 
	} 
		
	if(!function_exists('pree')){
	function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		
		}	 
	} 




	function wphi_menu()
	{



		 add_options_page('WP Header Images', 'WP Header Images', 'update_core', 'wp_hi', 'wp_hi');



	}

	function wp_hi(){ 



		if ( !current_user_can( 'update_core' ) )  {



			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );



		}



		global $wpdb, $wphi_dir, $hi_pro, $hi_data; 

		
		include($wphi_dir.'inc/wphi_settings.php');
		

	}	



	
	

	function wphi_plugin_links($links) { 
		global $hi_premium_link, $hi_pro;
		
		$settings_link = '<a href="options-general.php?page=wp_hi">Settings</a>';
		
		if($hi_pro){
			array_unshift($links, $settings_link); 
		}else{
			 
			$hi_premium_link = '<a href="'.$hi_premium_link.'" title="Go Premium" target=_blank>Go Premium</a>'; 
			array_unshift($links, $settings_link, $hi_premium_link); 
		
		}
		
		
		return $links; 
	}
	
	function register_hi_scripts() {
		
			
		if (is_admin ()){
		
			wp_enqueue_media ();
		
			
			 
			wp_enqueue_script(
				'wphi-scripts',
				plugins_url('js/scripts.js', dirname(__FILE__)),
				array('jquery')
			);	
			
			
		
			wp_register_style('wphi-style', plugins_url('css/admin-styles.css', dirname(__FILE__)));	
			
			wp_enqueue_style( 'wphi-style' );
		
		}else{
					
			wp_register_style('wphi-style', plugins_url('css/front-styles.css', dirname(__FILE__)));	
			
			wp_enqueue_style( 'wphi-style' );
		}
		
	
	} 
		
	if(!function_exists('wp_header_images')){
	function wp_header_images(){

		
		}
	}
	
	
		
		
	function get_parent_hmenu_id($id, $arr){
		if($arr[$id]==0)
		return $id;
		else
		return get_parent_hmenu_id($arr[$id], $arr);
	}
	
	function get_header_images(){
		
		global $wphi_dir;
		$args = array( 'taxonomy'=>'nav_menu', 'hide_empty' => true );
		$menus = get_terms($args);
		$wp_header_images = get_option( 'wp_header_images');
		

		
		
		$arr = array();
		$arr_obj = array();
		
		if(is_front_page() || is_home())
		$page_id = 0;
		elseif(is_archive())
		$page_id = get_cat_id( single_cat_title("",false) ); 
		else
		$page_id = get_the_ID();
		
		
		
		
		
		foreach ( $menus as $menu ):
		$menu_items = wp_get_nav_menu_items($menu->name);
		if(!empty($menu_items)){
			foreach($menu_items as $items){
				$parent = $items->menu_item_parent;
				$arr[$items->ID] = $parent;
				$arr_obj[$items->object_id] = $items->ID;
			}
		}
		endforeach;
		
		//pre($page_id);
		//pre($cur_cat_id);
		//pre(is_single());
		//pre(is_page());
		//pre(is_archive());
		
		
		
		if($page_id==0)
		$page_id = current($arr_obj);
		
		$parent_id = $arr_obj[$page_id];	

		//pre($arr_obj);
		
		$img_id = $wp_header_images[$parent_id];
		
		//pre($wp_header_images);
		
		if($img_id>0){
			$img_url = wp_get_attachment_url( $img_id );			
			if($img_url!=''){			
				include($wphi_dir.'templates/header_images.php');
			}
		}
	}
		
		
		

?>