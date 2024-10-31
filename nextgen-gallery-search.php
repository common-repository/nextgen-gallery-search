<?php
/*
Plugin Name: NextGEN Gallery Search
Plugin URI: http://www.xgear.info
Description: A NextGEN Photo gallery Search plugin.
Author: Marco Piccardo
Version: 1.0

Author URI: http://www.xgear.info

*/

	global $wpdb, $ngg;
	add_action('admin_menu', 'add_ngs_menu');

	function add_ngs_menu()  {
	    add_menu_page('Gallery Search', 'Gallery Search', 8, __FILE__, 'ngs_search_page');
//	    add_submenu_page(__FILE__, 'Options', 'Options', 8, 'abstracts_options', 'abstracts_options_page');
//	    add_submenu_page( NGGFOLDER , 'Search', 'Search', 8, __FILE__, 'ngs_search_page');
	}
	
	function ngs_search_galleries($filter = '', $order_by = 'gid', $order_dir = 'DESC', $counter = TRUE) {
		global $wpdb, $ngg;
		
		$order_dir = ( $order_dir == 'DESC') ? 'DESC' : 'ASC';
		if($filter!='') {
			$galleries = $wpdb->get_results( "SELECT * FROM $wpdb->nggallery WHERE title LIKE '%".$filter."%' ORDER BY {$order_by} {$order_dir}", OBJECT_K );
		} else {
			$galleries = $wpdb->get_results( "SELECT * FROM $wpdb->nggallery ORDER BY {$order_by} {$order_dir} LIMIT 20", OBJECT_K );
		}
		if ( !$galleries )
			return array();
		
		if ( !$counter )
			return $galleries;
		
		// get the galleries information
 		foreach ($galleries as $key => $value) {
   			$galleriesID[] = $key;
   			// init the counter values
   			$galleries[$key]->counter = 0;	
		}
		
		// get the counter values
		$picturesCounter = $wpdb->get_results('SELECT galleryid, COUNT(*) as counter FROM '.$wpdb->nggpictures.' WHERE galleryid IN (\''.implode('\',\'', $galleriesID).'\') AND exclude != 1 GROUP BY galleryid', OBJECT_K);			

		if ( !$picturesCounter )
			return $galleries;
		
		// add the counter to the gallery objekt
 		foreach ($picturesCounter as $key => $value)
			$galleries[$value->galleryid]->counter = $value->counter;
		
		return $galleries;
	}

	function ngs_search_page() {
		global $wpdb, $ngg;
		
		if(file_exists(WP_PLUGIN_DIR.'/'.NGGFOLDER.'/admin/functions.php')) {
			require(WP_PLUGIN_DIR.'/'.NGGFOLDER.'/admin/functions.php');
		
		?>
		<div class="wrap">
			<h2><?php _e('NextGEN Gallery Search', 'nggallery') ?></h2>
			<br style="clear: both;"/>

			<form method="post" action="<?=$_SERVER['REQUEST_URI']?>"> 
			
			<table class="form-table" style="width:570px;"> 
				<tr valign="top"> 
					<th scope="row"><label for="ngs_filter">Search Keyword</label></th> 
					<td><input name="ngs_filter" type="text" id="ngs_filter" value="<?=$_POST['ngs_filter'];?>" class="regular-text" /></td>
					<td><input type="submit" name="Submit" class="button-primary" value="Search" /></td>
				</tr> 
			</table>
			
			</form>

			<br style="clear: both;"/>
		
			<table class="widefat">
				<thead>
				<tr>
					<th scope="col" ><?php _e('ID') ?></th>
					<th scope="col" ><?php _e('Title', 'nggallery') ?></th>
					<th scope="col" ><?php _e('Description', 'nggallery') ?></th>
					<th scope="col" ><?php _e('Author', 'nggallery') ?></th>
					<th scope="col" ><?php _e('Page ID', 'nggallery') ?></th>
					<th scope="col" ><?php _e('Quantity', 'nggallery') ?></th>
					<th scope="col" ><?php _e('Action'); ?></th>
				</tr>
				</thead>
				<tbody>
	<?php
				
	$gallerylist = ngs_search_galleries($_POST['ngs_filter']);

	if($gallerylist) {
		foreach($gallerylist as $gallery) {
			$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
			$gid = $gallery->gid;
			$name = (empty($gallery->title) ) ? $gallery->name : $gallery->title;
			$author_user = get_userdata( (int) $gallery->author );
			?>
			<tr id="gallery-<?php echo $gid ?>" <?php echo $class; ?> >
				<th scope="row"><?php echo $gid; ?></th>
				<td>
					<?php if(nggAdmin::can_manage_this_gallery($gallery->author)) { ?>
						<a href="<?php echo wp_nonce_url( "admin.php?page=nggallery-manage-gallery&amp;mode=edit&amp;gid=" . $gid, 'ngg_editgallery')?>" class='edit' title="<?php _e('Edit') ?>" >
							<?php echo $name; ?>
						</a>
					<?php } else { ?>
						<?php echo $gallery->title; ?>
					<?php } ?>
				</td>
				<td><?php echo $gallery->galdesc; ?>&nbsp;</td>
				<td><?php echo $author_user->display_name; ?></td>
				<td><?php echo $gallery->pageid; ?></td>
				<td><?php echo $gallery->counter; ?></td>
				<td>
					<?php if(nggAdmin::can_manage_this_gallery($gallery->author)) : ?>
						<a href="<?php echo wp_nonce_url( "admin.php?page=nggallery-manage-gallery&amp;mode=delete&amp;gid=" . $gid, 'ngg_editgallery')?>" class="delete" onclick="javascript:check=confirm( '<?php _e("Delete this gallery ?",'nggallery')?>');if(check==false) return false;"><?php _e('Delete') ?></a>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}
	} else {
		echo '<tr><td colspan="7" align="center"><strong>'.__('No entries found','nggallery').'</strong></td></tr>';
	}
	?>			
				</tbody>
			</table>
		</div>
	<?php
		} else { ?>
		<div class="wrap">
			<h2><?php _e('NextGEN Gallery not found!', 'nggallery') ?></h2>			
		</div>
<?php		}
	}

?>