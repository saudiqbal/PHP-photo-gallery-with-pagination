<?php
$startTime = array_sum(explode(' ', microtime()));
header('Cache-Control: max-age=604800, public');
// include '/var/www/www.saudiqbal.com/includes/tracking-code.php';
// Remove trailing slashes (if present), and add one manually.
// Note: This avoids a problem where some servers might add a trailing slash, and others not..
define('BASE_PATH', rtrim(realpath(dirname(__FILE__)), "/gallery/") . '/gallery/');
//require BASE_PATH . 'includes/global_functions.php';
require BASE_PATH . 'includes/settings.php'; // Note. Include a file in same directory without slash in front of it!
require BASE_PATH . 'lib/translator_class.php';

$translator = new translator($settings['lang']);

//require BASE_PATH . 'includes/dependency_checker.php';

// <<<<<<<<<<<<<<<<<<<<
// Validate the _GET category input for security and error handling
// >>>>>>>>>>>>>>>>>>>>
$HTML_navigation = '<a href="/gallery/">' . $translator->string('Home') . '</a>';

// <<<<<<<<<<<<<<<<<<<<
// Fetch categories, and include them in a HTML ul list
// >>>>>>>>>>>>>>>>>>>>
$requested_category = $translator->string('Categories');
$categories = list_directories();
if (count($categories) >= 1) {
$HTML_cup = '';
foreach ($categories as &$category_name) {
$category_preview_images = category_previews($category_name, $category_json_file);
// echo 'cats:'.$category_preview_images; // Testing category views
$HTML_cup .= '<a class="cardthumbnail" href="categories.php?category=' . $category_name . '"><div class="cardtext flexible">' . $category_preview_images . '</div>' . space_or_dash('-', $category_name) . '</a>' . "\n";
}
$HTML_cup .= '';
} else {
$HTML_cup = '<p>' . $translator->string('There are no categories yet...') . '</p>';
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Saud Iqbal &#9655; Photo Gallery</title>
<meta name="keywords" content="saud, iqbal, saud iqbal, photo, gallery">
<meta name="description" content="Saud Iqbal Photo Gallery">
<meta name="viewport" content="user-scalable=yes, initial-scale=1, width=device-width">
<meta name="email" content="saudiqbal@yopmail.com">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="referrer" content="always">
<link rel="icon" href="data:;base64,iVBORw0KGgo=">
<link rel="stylesheet" type="text/css" href="/css/stylesheet.css">
<script src="/static-content/js/keyboard-navigation.js"></script>
<?php
if(strpos(basename($_SERVER['REQUEST_URI']), '?') !== false)
{
echo '<meta name="robots" content="noindex, nofollow">';
}
?>

<!-- <?php echo date('c'); ?> -->
</head>
<body>
<div class="sidebar">
<h1>Saud Iqbal</h1>
<nav id="mainnav" itemscope itemtype="http://schema.org/SiteNavigationElement">
<a itemprop="url" href="/"><span itemprop="name">Home</span></a>
<a itemprop="url" href="/contact-me.php"><span itemprop="name">Contact Me</span></a>
<a itemprop="url" href="/blog/"><span itemprop="name">Blog</span></a>
<a itemprop="url" href="/gallery/" class="active"><span itemprop="name">Gallery</span></a>
<a itemprop="url" href="/videos.php"><span itemprop="name">Videos</span></a>
</nav>
</div>

<div class="content">
<div class="gallery">
<div class="catetory"><h4><?php echo $requested_category; ?></h4></div>
<div class="breadcrumbs">
<?php echo $HTML_navigation; ?>

</div>
<div class="row-flex">
<?php echo $HTML_cup; ?>
</div>
</div>
</div>
<footer>
<hr class="hr-style">
<p><?php $totalTime = array_sum(explode(' ', microtime())) - $startTime;
$totalTime = round($totalTime, 4);
echo "Page generated in " . $totalTime . " seconds";
ob_end_flush();
?></p>
</footer>
<script>
keyboardPagination('.pagination_keyboard',
{
    prev: '.pagination-prev',
    next: '.pagination-next'
});
</script>
</body>
</html>
