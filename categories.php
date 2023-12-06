<?php
// Remove trailing slashes (if present), and add one manually.
// Note: This avoids a problem where some servers might add a trailing slash, and others not..
define('BASE_PATH', rtrim(realpath(dirname(__FILE__)), "/") . '/');
//require BASE_PATH . 'includes/global_functions.php';
require BASE_PATH . 'includes/settings.php'; // Note. Include a file in same directory without slash in front of it!
require BASE_PATH . 'lib/translator_class.php';

$translator = new translator($settings['lang']);

//require BASE_PATH . 'includes/dependency_checker.php';

// <<<<<<<<<<<<<<<<<<<<
// Validate the _GET category input for security and error handling
// >>>>>>>>>>>>>>>>>>>>
$HTML_navigation = '<a href="/">' . $translator->string('Home') . '</a>';

if (isset($_GET['category'])) {
  $HTML_navigation .= ' &#10095; <a href="index.php">' . $translator->string('Categories') . '</a>';
  if (preg_match("/^[a-zA-Z0-9-]/", $_GET['category'])) {
    $requested_category = $_GET['category'];
    // <<<<<<<<<<<<<<<<<<<<
    // Fetch the files in the category, and include them in an HTML ul list
    // >>>>>>>>>>>>>>>>>>>>
    $HTML_navigation .= ' &#10095; <a href="categories.php?category='.$requested_category.'">' . $requested_category . '</a>';
    $files = list_files($settings);
    $totalfiles = count($files);
    if (count($files) >= 1) {
      $HTML_cup = '';
// Number of images per page set below
$nb_elem_per_page = 9;
$adjacents = 3;
$page = isset($_GET['page'])?intval($_GET['page']-1):0;
if(preg_match('/[^0-9]/', $page))
{
exit("Invalid pagination");
}
$pgpage = isset($_GET['page'])?$_GET['page']:1;
if(preg_match('/[^0-9]/', $pgpage))
{
exit("Invalid pagination");
}
$number_of_pages = intval($totalfiles/$nb_elem_per_page)+2;
      foreach ((array_slice($files, $page*$nb_elem_per_page, $nb_elem_per_page)) as &$file_name) {
      //foreach ($files as &$file_name) {
        $thumb_file_location = 'thumbnails/' . $requested_category . '/thumb-' . rawurlencode($file_name);
        $source_file_location = 'gallery/' . $requested_category . '/' . $file_name;
        $HTML_cup .= '<a class="cardthumbnail" href="viewer.php?category=' . $requested_category . '&filename=' . $file_name . '"><div class="cardtext flexible"><img src="' . $thumb_file_location . '" alt="' . $file_name . '"></div></a>' . "\n";
      }
      $HTML_cup .= '';

    } else {
      $HTML_cup = '<p>' . $translator->string('There are no files in:') . ' <b>' . space_or_dash('-', $requested_category) . '</b></p>';
    }
  } else {
    header("HTTP/1.0 500 Internal Server Error");
    echo '<!doctype html><html><head></head><body><h1>Error</h1><p>Invalid category</p></body></html>';
    exit();
  }
} else { // If no category was requested
  // <<<<<<<<<<<<<<<<<<<<
  // Fetch categories, and include them in a HTML ul list
  // >>>>>>>>>>>>>>>>>>>>
    echo "Category not defined...";
    exit;
}
$HTML_navigation = '<div class="breadcrumbs">' . $HTML_navigation . '</div>';

// ====================
// Functions
// ====================
function space_or_dash($replace_this = '-', $in_this)
{
  if ($replace_this == '-') {
    return preg_replace('/([-]+)/', ' ', $in_this);
  } elseif ($replace_this == ' ') {
    return preg_replace('/([ ]+)/', '-', $in_this);
  }
}
function list_files($settings)
{
  $directory = BASE_PATH . 'gallery/' . $_GET['category'];
  $thumbs_directory = BASE_PATH . 'thumbnails/' . $_GET['category'];
  $item_arr = array_diff(scandir($directory), array('..', '.'));
  foreach ($item_arr as $key => $value) {
    if (is_dir($directory . '/' . $value)) {
      unset($item_arr["$key"]);
    } else {
      $path_to_file = $thumbs_directory . '/thumb-' . $value;
    }
  }
  return $item_arr;
}
function category_previews($category, $category_json_file)
{
  $thumbs_directory = BASE_PATH . 'thumbnails/' . $category;
  $previews_html = '';

  if (file_exists($thumbs_directory)) {

    if (file_exists($thumbs_directory . '/' . $category_json_file)) {
      $category_data = json_decode(file_get_contents($thumbs_directory . '/' . $category_json_file), true);

      $previews_html = '<div style="background:url(thumbnails/' . $category . '/' . rawurlencode($category_data['preview_image']) . ');" class="category_preview_img"></div>';
    } else {
      // Automatically try to select preview image if none was choosen
      $item_arr = array_diff(scandir($thumbs_directory), array('..', '.'));
      foreach ($item_arr as $key => $value) {
        $previews_html = '<div style="background:url(thumbnails/' . $category . '/' . rawurlencode($item_arr["$key"]) . ');" class="category_preview_img"></div>'; // add a dot in front of = to return all images
      }
      $category_data = json_encode(array('preview_image' => $item_arr["$key"]));
      file_put_contents($thumbs_directory . '/' . $category_json_file, $category_data);
    }
  }
  return $previews_html;
}
function list_directories()
{
  $item_arr = array_diff(scandir(BASE_PATH . 'gallery/'), array('..', '.'));
  foreach ($item_arr as $key => $value) {
    if (is_dir(BASE_PATH . 'gallery/' . $value) === false) {
      unset($item_arr["$key"]);
    }
  }
  return $item_arr;
}

/*
	Plugin Name: *Digg Style Paginator
	Plugin URI: http://www.mis-algoritmos.com/2006/11/23/paginacion-al-estilo-digg-y-sabrosus/
	Description: Adds a <strong>digg style pagination</strong>.
	Version: 0.1 Beta
*/
function pagination($total_pages,$limit,$page,$file,$adjacents){
		if($page)
				$start = ($page - 1) * $limit; 			//first item to display on this page
			else
				$start = 0;								//if no page var is given, set start to 0

		/* Setup page vars for display. */
		if ($page == 0) $page = 1;					//if no page var is given, default to 1.
		$prev = $page - 1;							//anterior page is page - 1
		$siguiente = $page + 1;							//siguiente page is page + 1
		$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1;					//last page minus 1
		if($page > $lastpage)
		{
			echo "SQL Injection detected!";
			exit();
		}
		$link_previous = "&#x276E; Previous";
		$link_next = "Next &#x276F;";

		$p = false;
		if(strpos($file,"?")>0)
			$p = true;

		//ob_start();
		if($lastpage > 1){
				//anterior button
				if($page > 1)
								if($p)
									echo "<span class=\"pagination-prev\"><a href=\"$file$prev\" class=\"pagination-button left\">$link_previous</a></span>";
									else
									echo "<span class=\"pagination-prev\"><a href=\"$file$prev\" class=\"pagination-button left\">$link_previous</a></span>";
					else
						echo "<span class=\"buttonDisabled leftDisabled\">$link_previous</span>";
				//pages
				if ($lastpage < 7 + ($adjacents * 2)){//not enough pages to bother breaking it up
						for ($counter = 1; $counter <= $lastpage; $counter++){
								if ($counter == $page)
										echo "<span class=\"pagination-button middleCurrent\">$counter</span>";
									else
												if($p)
												echo "<a href=\"$file$counter\" class=\"pagination-button middle\">$counter</a>";
												else
												echo "<a href=\"$file?page=$counter\" class=\"pagination-button middle\">$counter</a>";
							}
					}
				elseif($lastpage > 5 + ($adjacents * 2)){//enough pages to hide some
						//close to beginning; only hide later pages
						if($page < 1 + ($adjacents * 2)){
								for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){
										if ($counter == $page)
												echo "<span class=\"pagination-button middleCurrent\">$counter</span>";
											else
														if($p)
														echo "<a href=\"$file$counter\" class=\"pagination-button middle\">$counter</a>";
														else
														echo "<a href=\"$file?page=$counter\" class=\"pagination-button middle\">$counter</a>";
									}
								echo "";
										if($p){
										echo "<a href=\"$file$lpm1\" class=\"pagination-button middle\">$lpm1</a>";
										echo "<a href=\"$file$lastpage\" class=\"pagination-button middle\">$lastpage</a>";
										}else{
										echo "<a href=\"$file?page=$lpm1\" class=\"pagination-button middle\">$lpm1</a>";
										echo "<a href=\"$file?page=$lastpage\" class=\"pagination-button middle\">$lastpage</a>";
										}

							}
						//in middle; hide some front and some back
						elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)){
										if($p){
										echo "<a href=\"{$file}1\" class=\"pagination-button middle\">1</a>";
										echo "<a href=\"{$file}2\" class=\"pagination-button middle\">2</a>";
										}else{
										echo "<a href=\"$file?page=1\" class=\"pagination-button middle\">1</a>";
										echo "<a href=\"$file?page=2\" class=\"pagination-button middle\">2</a>";
										}
								echo "";
								for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
									if ($counter == $page)
											echo "<span class=\"pagination-button middleCurrent\">$counter</span>";
										else
													if($p)
													echo "<a href=\"$file$counter\" class=\"pagination-button middle\">$counter</a>";
													else
													echo "<a href=\"$file?page=$counter\" class=\"pagination-button middle\">$counter</a>";
								echo "";
										if($p){
										echo "<a href=\"$file$lpm1\" class=\"pagination-button middle\">$lpm1</a>";
										echo "<a href=\"$file$lastpage\" class=\"pagination-button middle\">$lastpage</a>";
										}else{
										echo "<a href=\"$file?page=$lpm1\" class=\"pagination-button middle\">$lpm1</a>";
										echo "<a href=\"$file?page=$lastpage\" class=\"pagination-button middle\">$lastpage</a>";
										}
							}
						//close to end; only hide early pages
						else{
										if($p){
										echo "<a href=\"{$file}1\" class=\"pagination-button middle\">1</a>";
										echo "<a href=\"{$file}2\" class=\"pagination-button middle\">2</a>";
										}else{
										echo "<a href=\"$file?page=1\" class=\"pagination-button middle\">1</a>";
										echo "<a href=\"$file?page=2\" class=\"pagination-button middle\">2</a>";
										}
								echo "";
								for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
									if ($counter == $page)
											echo "<span class=\"pagination-button middleCurrent\">$counter</span>";
										else
													if($p)
													echo "<a href=\"$file$counter\" class=\"pagination-button middle\">$counter</a>";
													else
													echo "<a href=\"$file?page=$counter\" class=\"pagination-button middle\">$counter</a>";
							}
					}
				if ($page < $counter - 1)
								if($p)
								echo "<span class=\"pagination-next\"><a href=\"$file$siguiente\" class=\"pagination-button right\">$link_next</a></span>";
								else
								echo "<span class=\"pagination-next\"><a href=\"$file?page=$siguiente\" class=\"pagination-button rightDisabled\">$link_next</a></span>";
					else
						echo "<span class=\"buttonDisabled rightDisabled pagination-next\">$link_next</span>";
			}
	}
// Pagination Ends


header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!doctype html>
<html lang="<?php echo $settings['lang']; ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php echo $settings['title']; ?></title>
<link rel="stylesheet" href="templates/default/gallery.css">
</head>
<body>
<header class="header">
<span class="logo"><?php echo $settings['title']; ?></span>
</header>
<div class="container">

<?php echo $HTML_navigation; ?>

<div class="catetory"><?php echo $requested_category; ?></div>
<div class="row-flex">
<?php echo $HTML_cup; ?>
</div>
<div class="pagination_style">
<?php $pagination = pagination($totalfiles,$nb_elem_per_page,$pgpage,"categories.php?category=".$requested_category ."&page=",$adjacents); ?>
</div>
</div>
</body>
</html>
