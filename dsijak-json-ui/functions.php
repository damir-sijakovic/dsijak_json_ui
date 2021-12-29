<?php

require_once __DIR__ . '/config.php';


function dsijak_getPosts()
{
	
	$args = array(
	  'post_type' => 'post' ,
	  'orderby' => 'date' ,
	  'order' => 'DESC' ,
	  'posts_per_page' => 6,
	  'category_name' => 'blog', //'editor-picks'
	  'paged' => get_query_var('paged'),
	); 
	$q = new WP_Query($args);


	$editorPicksPostIds = [];
	$counter = 0;
	if ($q->have_posts()) 
	{ 
	  while ($q->have_posts()) 
	  {
			if ($counter === 12)
			{
				break;
			}
		  
			$q->the_post();

			array_push($editorPicksPostIds, get_the_id());

			$counter++;
	  }
	  
	  wp_reset_postdata();
	}

	$editorPicksPosts = [];
	for ($i=0; $i<count($editorPicksPostIds); $i++)
	{	
		$post = get_post($editorPicksPostIds[$i]);	

		$t = new StdClass;
		$t->guid = $post->guid;
		$t->title = $post->post_title;
		$t->titleSlug = $post->post_name;	
		$t->modified = $post->post_modified;
		$t->id = url_to_postid( $t->guid );
		$t->thumbnail = parse_url( wp_get_attachment_image_src( get_post_thumbnail_id( $t->id ))[0] )['path'];
		$t->shortDescription = get_the_excerpt( $t->id );

		array_push($editorPicksPosts, $t);
	}
	
	return $editorPicksPosts;          
}

function dsijak_getMenuItems(){
    $menus = wp_get_nav_menus();
    $menu_locations = get_nav_menu_locations();

	if (!count($menus))
	{
		return null;
	}

	$returnData = [];
	
	for ($i=0; $i<count($menus); $i++)
	{

		$tmpPostObjArr = wp_get_nav_menu_items($menus[$i]);
		$returnData[$menus[$i]->slug] = [];
		
		for ($j=0; $j<count($tmpPostObjArr); $j++)		
		{
			$tmpPostObj = ["id" => $tmpPostObjArr[$j]->ID,"title" => $tmpPostObjArr[$j]->title, "url" => $tmpPostObjArr[$j]->url, "parentId" => $tmpPostObjArr[$j]->menu_item_parent];
			array_push($returnData[$menus[$i]->slug], $tmpPostObj);
		}
	

	}
	

	return $returnData;

}



function dsijak_getCategories()
{
	$taxonomy     = 'category';
	$orderby      = 'name';  
	$show_count   = 1;     
	$pad_counts   = 0;     
	$hierarchical = 1;     
	$title        = '';  
	$empty        = 0;

	$args = array(
		 'taxonomy'     => $taxonomy,
		 'orderby'      => $orderby,
		 'show_count'   => $show_count,
		 'pad_counts'   => $pad_counts,
		 'hierarchical' => $hierarchical,
		 'title_li'     => $title,
		 'hide_empty'   => $empty
	);

	$all_categories = get_categories( $args );
   
	$outputArray = [];

	foreach ($all_categories as $cat) 
	{		
		$t = new stdClass;
		$t->id = $cat->term_id;	
		$t->parent = $cat->parent;	
		$t->name = $cat->name;	
		$t->count = $cat->count; //category_count, count
		$t->slug = $cat->slug;	
		$t->description = $cat->description;
							
		array_push($outputArray, $t);

	}    
    
    return $outputArray;
}

	

function dsijak_getNumberOfPosts()
{
	return wp_count_posts()->publish; 
}


function dsijak_getInfoText()
{	
	$str = "<h2>Dsijak Json UI</h2>";	
	$str .= "<p><b>Creates Wordpress categories/posts/menu items list in JSON format.</b><br>";
	$str .= "You can async fetch it via route: '/dsijak-json-ui.json'. </p>";

	if (dsijak_jsonFileExists())	
	{
		$str .= "<hr>";
		$str .= "<p>JSON file url: <a target='_blank' href='/dsijak-json-ui.json'>route</a></p>";
	}
	
	$str .= "<hr>";
	
	return $str;
}

function dsijak_jsonFileExists()
{		
	if (file_exists(DSIJAK_JSON_UI_FILE_NAME))
	{
		return true;
	}
	
	return false;
}

function dsijak_createJsonFile()
{		
	$outputJsonFile = [];

	$outputJsonFile['numberOfPosts'] = dsijak_getNumberOfPosts();
	$outputJsonFile['categories'] = dsijak_getCategories();
	$outputJsonFile['blogPosts'] = dsijak_getPosts();
	$outputJsonFile['menuItems'] = dsijak_getMenuItems();	
	$outputJsonFile['blogName'] = get_bloginfo( 'name' ); 
    $outputJsonFile['blogDescription'] = get_bloginfo( 'description', 'display' );
	
	return file_put_contents(DSIJAK_JSON_UI_FILE_NAME, json_encode($outputJsonFile));
	
	
}

function dsijak_deleteJsonFile()
{		
	if (file_exists(DSIJAK_JSON_UI_FILE_NAME))
	{
		unlink(DSIJAK_JSON_UI_FILE_NAME);
		return true;
	}
	return false;
}
