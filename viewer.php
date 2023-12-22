<?php
define('BASE_PATH', rtrim(realpath(dirname(__FILE__)), "/") . '/');
require BASE_PATH . 'includes/settings.php';
require BASE_PATH . 'lib/translator_class.php';
$translator = new translator($settings['lang']);

$requested_category = '';
$requested_file = '';
$html_title = 'Viewer';
$html_content = '';
$nav_content = '';
$html_backlink = '';
$next_file = false;
$previous_file = false;

$HTML_navigation = '<a href="/">' . $translator->string('Home') . '</a>';

if (
	(isset($_GET['category'])) &&
	(preg_match("/^[a-zA-Z0-9-]/", $_GET['category']))
) {

	if ((isset($_GET['filename'])) && (preg_match("/^[^\/\"'<>*]+$/", $_GET['filename']))) {
		$HTML_navigation .= ' &#10095; <a href="index.php">' . $translator->string('Categories') . '</a>';

		$requested_category = $_GET['category'];
		// Uncomment the following if block to enable directory checking
		//if(!is_dir(__DIR__ . "/gallery/" . $requested_category)) {
		//	exit("Category not found");
		//}
		$requested_file = $_GET['filename'];
		$html_title = $requested_file . ' - ' . $requested_category . ' | ' . $html_title;
		$HTML_navigation .= ' &#10095; <a href="categories.php?category='.$requested_category.'">' . $requested_category . '</a>';
		$HTML_navigation .= ' &#10095; <a>' . $requested_file . '</a>';

		$files = array_values(list_files($settings));
		if (!in_array($requested_file, $files)) {
			exit("File not found");
		}
		$files_count = count($files);

		if ($files_count >= 1) {
			$i = 0;
			while ($i < $files_count) {
				if ($files["$i"] == $requested_file) {
					$next_i = $i + 1;
					$previous_i = $i - 1;
					if (isset($files["$previous_i"])) {
						$previous_file = $files["$previous_i"];
					}
					if (isset($files["$next_i"])) {
						$next_file = $files["$next_i"];
					}
				} else {
					$file_name = $files["$i"];
					$thumb_file_location = 'thumbnails/' . $requested_category . '/thumb-' . $file_name;
					$source_file_location = 'gallery/' . $requested_category . '/' . $file_name;
				}
				++$i;
			}
		}

		$path_to_file = 'gallery/' . $requested_category . '/' . $requested_file;

		$nav_content .= '<div class="pagination_style">';
		if ($previous_file !== false) {
			$nav_content .= '<span class="pagination-prev"><a href="viewer.php?category=' . $requested_category . '&filename=' . $previous_file . '" class="pagination-button left">&#10094; Previous </a></span>';
		}
		else {
			$nav_content .= '<span class="buttonDisabled leftDisabled">&#10094; Previous </span>';
		}
		if ($next_file !== false) {
			$nav_content .= '<span class="pagination-next"><a href="viewer.php?category=' . $requested_category . '&filename=' . $next_file . '" class="pagination-button right"> Next &#10095;</a></span>';
		}
		else {
			$nav_content .= '<span class="buttonDisabled rightDisabled pagination-next"> Next &#10095;</span>';
		}
		$nav_content .= '</div>';

		$html_content .= '<a class="card"><div class="cardtext flexible"><img src="' . $path_to_file . '" alt="' . $requested_file . '" class="fluidimg"></div></a>';
		//$html_action_controls = '<div id="action_controls"><ul><li><a href="categories.php?category=' . $requested_category . '">Back</a></li></ul></div>';
	} else {
		$html_content = '<p>Invalid filename...</p>';
	}
} else {
	$html_content = '<p>Invalid category...</p>';
}

// ====================
// Functions
// Note. Besides CreateThumbnail() these functions are unique to this file
// DO NOT assume they are the same as in index.php
// If you combine and move functions to a functions.php, you will need fix code differences!
// ====================

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

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
//require BASE_PATH . 'templates/' . $template . '/viewer_template.php';
?>
<!doctype html>
<html lang="<?php echo $settings['lang']; ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php echo $html_title; ?></title>
<link rel="stylesheet" href="templates/default/gallery.css">
</head>
<body>
<header class="header">
<span class="logo"><?php echo $settings['title']; ?></span>
</header>
<div class="container">
<div class="catetory"><?php echo $requested_category; ?></div>
<div class="breadcrumbs">
<?php echo $HTML_navigation; ?>
</div>

<?php echo $nav_content; ?>

<div class="row-flex">
<?php echo $html_content; ?>
</div>
</div>
</body>
</html>
