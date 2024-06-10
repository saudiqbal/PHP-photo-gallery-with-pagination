<?php
ini_set('max_execution_time', '0');
// Remove trailing slashes (if present), and add one manually.
// Note: This avoids a problem where some servers might add a trailing slash, and others not..
define('BASE_PATH', rtrim(realpath(dirname(__FILE__)), "/") . '/');
//require BASE_PATH . 'includes/global_functions.php';
require BASE_PATH . 'includes/settings.php'; // Note. Include a file in same directory without slash in front of it!
//require BASE_PATH . 'lib/translator_class.php';

$category_json_file = 'category_data.json';

//require BASE_PATH . 'includes/dependency_checker.php';

// <<<<<<<<<<<<<<<<<<<<
// Validate the _GET category input for security and error handling
// >>>>>>>>>>>>>>>>>>>>
$settings = array();
$HTML_navigation = '<a href="index.php">Home</a>';

if (isset($_GET['category'])) {
$HTML_navigation .= ' &#10095; <a href="generate-thumbnails.php">Categories</a>';
if (preg_match("/^[a-zA-Z0-9-]/", $_GET['category'])) {
	$requested_category = $_GET['category'];
	// <<<<<<<<<<<<<<<<<<<<
	// Fetch the files in the category, and include them in an HTML ul list
	// >>>>>>>>>>>>>>>>>>>>
	$files = list_files($settings);
	if (count($files) >= 1) {
	$HTML_cup = '';
	foreach ($files as &$file_name) {
		$thumb_file_location = 'thumbnails/' . $requested_category . '/thumb-' . rawurlencode($file_name);
		$source_file_location = 'gallery/' . $requested_category . '/' . $file_name;
		$HTML_cup .= '<a class="cardthumbnail" href="viewer.php?category=' . $requested_category . '&filename=' . $file_name . '"><div class="cardtext flexible"><img src="' . $thumb_file_location . '" alt="' . $file_name . '"></div></a>';
	}
	$HTML_cup .= '';
	} else {
	$HTML_cup = '<p>There are no files in: <b>' . space_or_dash('-', $requested_category) . '</b></p>';
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
$requested_category = 'Categories';
$categories = list_directories();
if (count($categories) >= 1) {
	$HTML_cup = '';
	foreach ($categories as &$category_name) {
	$category_preview_images = category_previews($category_name, $category_json_file);
	// echo 'cats:'.$category_preview_images; // Testing category views
	$HTML_cup .= '<a class="cardthumbnail" href="generate-thumbnails.php?category=' . $category_name . '"><div class="cardtext flexible">' . $category_preview_images . '</div>' . space_or_dash('-', $category_name) . '</a>';
	}
	$HTML_cup .= '';
} else {
	$HTML_cup = '<p>There are no categories yet...</p>';
}
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
	if (file_exists($path_to_file) !== true) {
		createThumbnail($value, $directory, $thumbs_directory, 200, 200);
	}
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

	$previews_html = '<img src="thumbnails/' . $category . '/' . rawurlencode($category_data['preview_image']) . '">';
	} else {
	// Automatically try to select preview image if none was choosen
	$item_arr = array_diff(scandir($thumbs_directory), array('..', '.'));
	foreach ($item_arr as $key => $value) {
		$previews_html = '<img src="thumbnails/' . $category . '/' . rawurlencode($item_arr["$key"]) . '">'; // add a dot in front of = to return all images
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

function createThumbnail($filename, $source_directory, $thumbs_directory, $max_width, $max_height)
{
$path_to_source_file = $source_directory . '/' . $filename;
$path_to_thumb_file = $thumbs_directory . '/thumb-' . $filename;
$source_filetype = exif_imagetype($path_to_source_file);
if (file_exists($thumbs_directory) !== true) {
	if (!mkdir($thumbs_directory, 0775, true)) {
	echo 'Error: The thumbnails directory could not be created.';
	exit();
	} else {
	// On some hosts, we need to change permissions of the directory using chmod
	// after creating the directory
	chmod($thumbs_directory, 0775);
	}
}
// Create the thumbnail ----->>>>
list($orig_width, $orig_height) = getimagesize($path_to_source_file);
$width = $orig_width;
$height = $orig_height;

if ($height > $max_height) { // taller
	$width = ($max_height / $height) * $width;
	$height = $max_height;
}
if ($width > $max_width) { // wider
	$height = ($max_width / $width) * $height;
	$width = $max_width;
}
$image_p = imagecreatetruecolor($width, $height);

switch ($source_filetype) {
	case IMAGETYPE_JPEG:
	$image = imagecreatefromjpeg($path_to_source_file);
	imagecopyresampled(
		$image_p,
		$image,
		0,
		0,
		0,
		0,
		$width,
		$height,
		$orig_width,
		$orig_height
	);
	imagejpeg($image_p, $path_to_thumb_file);
	break;
	case IMAGETYPE_PNG:
	$image = imagecreatefrompng($path_to_source_file);
	imagecopyresampled(
		$image_p,
		$image,
		0,
		0,
		0,
		0,
		$width,
		$height,
		$orig_width,
		$orig_height
	);
	imagepng($image_p, $path_to_thumb_file);
	break;
	case IMAGETYPE_GIF:
	$image = imagecreatefromgif($path_to_source_file);
	imagecopyresampled(
		$image_p,
		$image,
		0,
		0,
		0,
		0,
		$width,
		$height,
		$orig_width,
		$orig_height
	);
	imagegif($image_p, $path_to_thumb_file);
	break;

	case IMAGETYPE_WEBP:
	$image = imagecreatefromwebp($path_to_source_file);
	imagecopyresampled(
		$image_p,
		$image,
		0,
		0,
		0,
		0,
		$width,
		$height,
		$orig_width,
		$orig_height
	);
	imagewebp($image_p, $path_to_thumb_file);
	break;


	default:
	echo 'Unknown filetype. Supported filetypes are: JPG, PNG og GIF.';
	exit();
}
}
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
<div class="catetory">Thumbnail Generator - <?php echo $requested_category; ?></div>
<?php echo $HTML_navigation; ?>

<div class="row-flex">
<?php echo $HTML_cup; ?>
</div>
</div>
</body>
</html>
