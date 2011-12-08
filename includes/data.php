<?php
	//import the object into this file
	
		
		$link = esc_url_raw($_REQUEST['imdb_movie']);		
		$m = new imdbInfo();
		
		// get data
		$result = $m->getDataFromIMDB($link);
		var_dump($result);
		exit;
		
		// error control
		 
		if ($result['error'] == null) {
/*
			
			$uploads_url = wp_upload_dir();	
			$image_src = $uploads_url['basedir'].'/imdb/'.$result['id'].'.jpg';
			$guide = $uploads_url['baseurl'] . '/imdb/'.$result['id'].'.jpg';
			
			var_dump($image_src);
			var_dump($guide);
			
			//attchment data
			$attachment = array(
				 'post_mime_type' => 'image/jpeg',
				 'post_title' => $result['id'],
				 'guid' => $guide,
				 'post_content' => '',
				 'post_status' => 'inherit'				 
			  );
			$attach_id = wp_insert_attachment( $attachment,$image_src);
			var_dump($attach_id);
			
			if(!function_exists('wp_generate_attachment_metadata')) : 
				require_once(ABSPATH . 'wp-admin/includes/image.php');
			endif;
			
			$attach_data = wp_generate_attachment_metadata($attach_id,$image_src);			
			wp_update_attachment_metadata($attach_id,$attach_data);
						
			
			$post_image = get_option('home').'/assets/imdb/'.$result['id'].'.jpg';
			$post_content = "<img title='$result[id]' class='movieposter' src='$guide' alt='$result[id]' />";
			*/
				
			$data = array(
				'post_type' =>$taxonomy,
				'post_title' => $result['title'],
				'post_content' => $post_content.strip_tags($result['plot']),
				'post_status' => 'draft',			
				'post_date' => date("Y-m-d H:i:s",time()),
				'post_date_gmt' =>date("Y-m-d H:i:s",time()),		
				'ping_status' =>'open',				
				
			);
			//inserting data with some defined data
			$p_id = wp_insert_post( $data, $wp_error );
							
			
			wp_set_post_terms($p_id,$result['genres'],'genre',false);
			wp_set_post_terms($p_id,$result['cast'],'actor',false);	
			
	
			
			$redirect_url = get_option('home').'/wp-admin/post.php?post='.$p_id.'&action=edit';
			
			if(!function_exists('wp_redirect')){
				include ABSPATH . '/wp-includes/pluggable.php';
			} 
			wp_redirect($redirect_url);
			exit;
		}
		
		else {
			add_action('admin_notices',array($imbd_movie_data,'errormessage'));
			return;			
		}	
?>