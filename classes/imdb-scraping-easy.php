<?php

//main class 
 if(!class_exists('imdb_movie_information_collection')):
	class imdb_movie_information_collection{
		
		//construct
		function __construct(){
			add_action('add_meta_boxes',array($this,'advanced_box'));
			add_action('init',array($this,'custom_post_creation'));
			add_action('init',array($this,'custom_texanomy_creation'));
			//add_filter('wp_get_attachment_url',array($this,'test'));
			add_action('admin_menu',array($this,'optionsPage'));	
			add_action('admin_init',array($this,'registerOption'));			
			add_action('init',array($this,'imdb_data_save'),10);
			
			register_activation_hook(IMDBFILE,array($this,'table_creation'));
		}
		
		//table creation
		function table_creation(){
			
			global $wpdb;
			$table = $wpdb->prefix . 'imdb';
			$sql = "CREATE TABLE IF NOT EXISTS $table(
				`id` bigint unsigned NOT NULL AUTO_INCREMENT,
				`p_id` bigint unsigned NOT NULL,
				`link` varchar(150) NOT NULL,				
				PRIMARY KEY(id)				
			)";
			
			if(!function_exists('dbDelta')) :
				include ABSPATH . 'wp-admin/includes/upgrade.php';
			endif;
			
			dbDelta($sql);
		}
		
		//manipulating the data from imdb
		function imdb_data_save(){		
			
			if(isset($_REQUEST['submit_imdb_link'])) :
				$taxonomy = $_REQUEST['imdb-data-type'];				
				include IMDB_INCLUDES . '/data.php';
			endif;
		}
		
		function advanced_box($post){
			//make the post as golbal using it in callback function
			global $post;
			add_meta_box('imdb-movie-metabox',__('Imdb Movie Link'),array($this,'movies_data'),'imdb_movies','normal','high');
			add_meta_box('imdb-tvshow-metabox',__('Imdb TV-Show Link'),array($this,'tvshows_data'),'tv_shows','normal','high');
		}
		
		//calback function 
		function movies_data($post){			
			//var_dump($post);
			//retreiving meta data
			$movie_link = get_post_meta($post->ID,'imdb_movie_link',true);
			
			?>
			<div class="wrap">
				
					<p>Insert the Imdb link for fetching any movie data</p>
					<input id="imdb-movie-link" type="text"  value="<?php echo esc_url($movie_link); ?>" name="imdb_movie" />
					<input type='hidden' name='imdb-data-type' value='imdb_movies' />
					<input type="submit" name="submit_imdb_link" class="button-primary" value="Get Data"/>						
				
			</div>			

<?php
		}
		
	function tvshows_data($post){			
			//var_dump($post);
			//retreiving meta data
			$movie_link = get_post_meta($post->ID,'imdb_movie_link',true);
			
			?>
			<div class="wrap">
				
					<p>Insert the Imdb link for fetching any Tv shows data</p>
					<input id="imdb-movie-link" type="text"  value="<?php echo esc_url($movie_link); ?>" name="imdb_movie" />
					<input type='hidden' name='imdb-data-type' value='tv_shows' />
					<input type="submit" name="submit_imdb_link" class="button-primary" value="Get Data"/>						
				
			</div>			

<?php
		}
		
		
		//saving meta data
		function save_meta($post_id){
			if(isset($_REQUEST['imdb_movie_link'])){
				update_post_meta($post_id,'imdb_movie_link',esc_url_raw($_REQUEST['imdb_movie_link']));
			}
		}		
		
		// custom post type
		function custom_post_creation(){

				$tv_args = array(
				'public' => true,
				'query_var' => 'tv_show',
				'rewrite' => true,
				'supports' => array('title','editor','author','thumbnail','custom-fields','comments','excerpt','page-attributes'),
				'has_archive' => true,
				'hierarchical' => true,
				'labels' => array(
					'name' => 'TV Shows',
					'singular_name' => 'Movies',
					'add_new' => 'Add New',
					'add_new_item' => 'Add New TV-Show',
					'edit_item' => 'Edit TV-Show',
					'new_item' => 'New TV-Show',
					'view_item' => 'View TV-Show',
					'search_items' => 'Search TV-Show',
					'not_found' => 'No TV-Show Found',
					'not_found_in_trash' => 'No TV-Show Found In Trash'
				),	
				

					
				'taxonomies' => array( 'post_tag','category','genre','actor')

			);

			

			$movie_args = array(
				'public' => true,
				'query_var' => 'moive_name',
				'rewrite' => true,
				'supports' => array('title','editor','author','thumbnail','custom-fields','comments','excerpt'),
				'has_archive' => true,
				'hierarchical' => true,
				'labels' => array(
					'name' => 'Movies',
					'singular_name' => 'Movies',
					'add_new' => 'Add New',
					'add_new_item' => 'Add New Movie',
					'edit_item' => 'Edit Movie',
					'new_item' => 'New Movie',
					'view_item' => 'View Movie',
					'search_items' => 'Search Movies',
					'not_found' => 'No Movies Found',
					'not_found_in_trash' => 'No Movies Found In Trash',
					'rewrite' => array( 'slug' => 'movies', 'with_front' => false ),
				),				
				
				'taxonomies' => array( 'post_tag','category','genre','actor')

			);
		
			register_post_type('imdb_movies', $movie_args );
			register_post_type('tv_shows', $tv_args);
		}

		//custom post type
		
		//custom taxonomy
		function custom_texanomy_creation(){
			$labels = array(
				'name' => 'genre', 
				'search_items' =>  __( 'Search Genres' ),
				'all_items' => __( 'All Genres' ),
				'parent_item_colon' => __( 'Parent Genre:' ),
				'edit_item' => __( 'Edit Genre' ), 
				'update_item' => __( 'Update Genre' ),
				'add_new_item' => __( 'Add New Genre' ),
				'new_item_name' => __( 'New Genre Name' ),
				'menu_name' => __( 'Genre' ),				
			  );
			 register_taxonomy('genre',array('imdb_movies','tv_shows'), array(
				'hierarchical' => true,
				'labels' => $labels,
				'public' => true,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'genre','with_front' => false ),
				
				'_builtin' => true,
			  ));
			  $label = array(
				'name' => 'actor', 
				'singular_name' => 'actor',
				'search_items' =>  __( 'Search actor' ),
				'all_items' => __( 'All actor' ),
				'parent_item' => __( 'Parent actor' ),
				'parent_item_colon' => __( 'Parent actor:' ),
				'edit_item' => __( 'Edit actor' ), 
				'update_item' => __( 'Update actor' ),
				'add_new_item' => __( 'Add New actor' ),
				'new_item_name' => __( 'New actor Name' ),
				'menu_name' => __( 'Actor' ),
			  );
			  register_taxonomy('actor',array('imdb_movies','tv_shows'), array(
				'hierarchical' => false,
				'labels' => $label,
				'public' => true,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'actor','with_font' => false ),
			  ));
			 			  
		}

		function test($attachment,$post_id){
			
						
			return $attachment;			
		}
		function errormessage(){
			echo '<div id="message" class="error"><p>Sorry! Information not found</p></div>';
		}

		//admin menu
		function optionsPage(){
			add_options_page('user fttp information','IMDB-MOVIE','activate_plugins','settings-ftp',array($this,'optionsPageDetails'));
		}
		
		
		//creating options page in admin panel
		function optionsPageDetails(){
			$request = $_REQUEST['error'];			
			if($request == 1){
				echo '<div id="message" class="error"><p>Please Check your FTP information</p></div>';
			}			
			$data = get_option('ftp_information');
			$server = $data['server'];
			$name = $data['name'];
			$password = $data['password'];
			$image = $data['image'];
			//starin html form
		?>
			<div class="wrap">
				<?php screen_icon('options-general'); ?>
				<h2>IMdb Plugin's settings</h2>
				<form action="options.php" method="post">
					<?php 
						settings_fields('ftp_information');							
						$data = get_option('ftp_information');
						$server = $data['server'];
						$user = $data['user'];
						$password = $data['password'];
						$image = $data['image'];
						 							
					?>					
					<table class="form-table">
							<tr valign="top"><th scope="row">FTP SERVER </th>
							
								<td><input name="ftp_information[server]" type="text" value= "<?php echo $server; ?>" /></td>												
							</tr>
							
							<tr valign="top"><th scope="row">FTP USER </th>
							
								<td><input name="ftp_information[user]" type="text" value= "<?php echo $user; ?>" /></td>												
							</tr>
							<tr valign="top"><th scope="row">FTP password </th>
							
								<td><input name="ftp_information[password]" type="text" value= "<?php echo $password; ?>" /></td>												
							</tr>
							<tr>
								<td colspan="3"> Please Insert wp-content direcotory relative to ftp root directory (".../wp-content") pls see the screenshots attached &nbsp <a href= "<?php echo plugins_url('/imdb-scraping-easy/screenshots/screenshots.png'); ?>" target="_blank">screenshots</a> </td>
								
							</tr>
							<tr valign="top"><th scope="row">FTP Path </th>
							
								<td colspan="3"> <input name="ftp_information[image]" type="text" value= "<?php echo $image; ?>" /></td>												
							</tr>
							<tr>
								<td>
								<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
								</td>
							<tr>
						</table>
				</form>
			</div>
			
		<?php
			
		}
		function registerOption(){
			register_setting('ftp_information','ftp_information',array($this,'data_validation'));			
		}

		function data_validation($data){
			return $data;
		}

		//ftp message
		
		function ftpmessage(){			
			echo '<div id="message" class="error"><p>Please Check your FTP information</p></div>';			
		}
			
	}
		
	$imbd_movie_data = new imdb_movie_information_collection();
	
 endif;


?>