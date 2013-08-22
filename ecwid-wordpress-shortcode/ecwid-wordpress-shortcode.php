<?php
/*
Plugin Name: Ecwid Shopping Cart Shortcode
Plugin URI: http://www.ecwid.com/ 
Description: Ecwid is a free full-featured shopping cart. It can be easily integreted with any Wordpress blog and takes less than 5 minutes to set up.
Author: Ecwid Team
Version: 0.1 
Author URI: http://www.ecwid.com/
*/

define('ECWID_DEMO_STORE_ID', '1003');
define('ECWID_URL', 'app.ecwid.com');

define('ECWID_MAX_VIEW_ITEMS', '100');
define('ECWID_MAX_CATEGORIES_PER_ROW', '25');

define('ECWID_DEFAULT_CATEGORIES_PER_ROW', '3');
define('ECWID_DEFAULT_GRID_SIZE', '3,3');
define('ECWID_DEFAULT_LIST_SIZE', '10');
define('ECWID_DEFAULT_TABLE_SIZE', '20');
define('ECWID_DEFAULT_SEARCH_VIEW', 'grid');
define('ECWID_DEFAULT_CATEGORY_VIEW', 'grid');

if (!is_admin()) {
	add_shortcode('ecwid', 'ecwid_shortcode');
}

function ecwid_shortcode($args) {

	$result = '<div>';

	if (!defined('ECWID_SCRIPTJS')) {
		$store_id = intval(ecwid_get_arg($args, 'id'));
		if (!$store_id) {
			$store_id = ECWID_DEMO_STORE_ID;
		}
		$result .= "<script type=\"text/javascript\" src=\"//" . ECWID_URL . "/script.js?$store_id\"></script>";
		define('ECWID_SCRIPTJS','Yep');
	}

	$widgets = explode(' ', ecwid_get_arg($args, 'widgets', 'productbrowser'));
	foreach ($widgets as $widget) {
		$widget = trim($widget);
		if (in_array($widget, array('productbrowser', 'categories', 'vcategories', 'search', 'minicart'))) {
			$getter = "ecwid_get_widget_$widget";
			$result .= $getter($args);
		}
	}

	$result .= '</div>';

	return $result;
}


function ecwid_get_arg($args, $name, $default = null) {
	$value = $default;
	if (is_array($args) && array_key_exists($name, $args)) {
		$value = $args[$name];
	}

	return $value;
}


function ecwid_get_widget_productbrowser($args = array()) {

	$cats_per_row = intval(ecwid_get_arg($args,'categoriesperrow'));
	
	if ($cats_per_row > ECWID_MAX_CATEGORIES_PER_ROW || $cats_per_row < 1) {
		$cats_per_row = ECWID_DEFAULT_CATEGORIES_PER_ROW;
	}

	$view = array();

	$grid = ecwid_get_arg($args, 'grid');
	if (!is_null($grid)) {
		
		$value = ECWID_DEFAULT_GRID_SIZE;
		if (count($sizes = explode(",", $grid)) == 2) {
	        $rows = intval($sizes[0]);
	        $cols = intval($sizes[1]);

			if (
				$rows <= ECWID_MAX_VIEW_ITEMS 
				&& $rows >= 1 
				&& $cols <= ECWID_MAX_VIEW_ITEMS 
				&& $cols >= 1 
				&& $rows * $cols <= ECWID_MAX_VIEW_ITEMS
			) {
				$value = "$rows,$cols";
			}
		}

		$views[]= "grid($value)";
	}

	$list = ecwid_get_arg($args, 'list');
	if (!is_null($list)) { 
		$list = intval($list);
		if ($list < 1 || $list > ECWID_MAX_VIEW_ITEMS) {
			$list = ECWID_DEFAULT_LIST_SIZE;
		}

		$views[] = "list($list)";
	}

	$table = ecwid_get_arg($args, 'table');
	if (!is_null($table)){
		$table = intval($table);
		if ($table < 1 || $table > ECWID_MAX_VIEW_ITEMS) {
			$table = ECWID_DEFAULT_TABLE_SIZE;
		}

		$views[] = "table($table)";
	}

	if (!empty($views)) {
		$views = implode(" ", $views);
	} else {
		$views = '';
	}


	$search_view = ecwid_get_arg($args, 'searchview');
	if (!in_array($search_view, array('list', 'grid', 'table'))) {
		$search_view = ECWID_DEFAULT_SEARCH_VIEW;
	}

	$cat_view = ecwid_get_arg($args, 'categoryview');
	if (!in_array($cat_view, array('list', 'grid', 'table'))) {
		$cat_view = ECWID_DEFAULT_CATEGORY_VIEW;
	}

	$responsive = ecwid_get_arg($args, 'responsive');
	if (!is_null($responsive)) {
		$responsive = ',"responsive=yes"';
	}

	$default_cat = intval(ecwid_get_arg($args, 'defaultcategoryid'));
	if ($default_cat) {
		$default_cat = ',"defaultCategoryId=' . $default_cat . '"';
	} else {
		$default_cat = '';
	}

	$result = <<<HTML
<script type="text/javascript"> xProductBrowser("categoriesPerRow=$cats_per_row","views=$views","categoryView=$cat_view","searchView=$search_view","style="$responsive$default_cat); </script> 
HTML;

	return $result;
}

function ecwid_get_widget_minicart($args) {

	$layout_code = '';
	
	$layout = ecwid_get_arg($args, 'layout');
	if (in_array($layout, array('attachToCategories', 'floating', 'Mini', 'MiniAttachToProductBrowser'))) {
		$layout_code = ',"layout=' . $layout . '"';
	}

    $result = <<<HTML
    <script type="text/javascript"> xMinicart("style="$layout_code);</script>
HTML;

    return $result;
}


function ecwid_get_widget_categories($args) {

    $result = <<<HTML
	<script type="text/javascript"> xCategories("style=");</script>
HTML;

	return $result;
}

function ecwid_get_widget_vcategories($args) {

    $result = <<<HTML
    <script type="text/javascript"> xVCategories("style=");</script>
HTML;

    return $result;
}


function ecwid_get_widget_search($args) {

    $result = <<<HTML
    <script type="text/javascript"> xSearchPanel("style=");</script>
HTML;

    return $result;
}

function ecwid_shortcode_replace_all($input)
{
    while ($result = ecwid_shortcode_replace_product_browser($input)) {
        $input = $result;
    }

    while ($result = ecwid_shortcode_replace_minicart($input)) {
        $input = $result;
    }

    $generic_widgets = array(
        'categories' => 'xCategories',
        'vcategories' => 'xVCategories',
        'search' => 'xSearchPanel'
    );
    foreach ($generic_widgets as $widget => $function) {
        while ($result = ecwid_shortcode_replace_generic($input, $function, $widget)) {
            $input = $result;
        }
    }

    return $input;
}

function ecwid_shortcode_build_short_code($type, $id, $other_args) {

    $args = '';
    foreach ($other_args as $name => $value) {
        if ($name == 'id') continue;
        $args .= "$name=\"$value\" ";
    }

    $result = sprintf('[ecwid id="%s" %swidgets="%s"]', $id, $args, $type);

    return $result;
}

function ecwid_shortcode_replace_product_browser($input) {
    $matches = array();
    $match_expression = '#<div id="my-store-.*?</div>\s*<div>\s*<script type="text/javascript" src="http://app.ecwid.com/script.js\?([0-9]*)".*?<script type="text/javascript"> xProductBrowser\("categoriesPerRow=([^"]*)","views=([^"]*)","categoryView=([^"]*)","searchView=([^"]*)"[^)]*\);\s*</script>\s*</div>#s';

    if (!preg_match($match_expression, $input, $matches)) {
        return false;
    }

    $args = array();

    list (, $args['id'], $args['categoriesperrow'], $views, $args['categoryview'], $args['searchview']) = $matches;

    $views = explode(' ', $views);
    foreach ($views as $view) {
        $matches = array();
        if (preg_match('!grid\(([0-9]*),([0-9]*)\)!', $view, $matches)) {
            $args['grid'] = "$matches[1],$matches[2]";
        } elseif (preg_match('!list\(([0-9]*)\)!', $view, $matches)) {
            $args['list'] = $matches[1];
        } elseif (preg_match('!table\(([0-9]*)\)!', $view, $matches)) {
            $args['table'] = $matches[1];
        }
    }

    $short_code = ecwid_shortcode_build_short_code("productbrowser", $args['id'], $args);

    return preg_replace($match_expression, $short_code, $input, 1);
}

function ecwid_shortcode_replace_minicart($input) {
    $matches = array();
    $match_expression = '#<div>\s*<script type="text/javascript" src="http://app.ecwid.com/script.js\?([0-9]*)"[^>]*?</script>\s*<!-- remove layout parameter if you want to position minicart yourself -->\s*<script type="text/javascript">\s*xMinicart\("style=","layout=([^"]*)"\);\s*</script>\s*?</div>#s';

    if (!preg_match($match_expression, $input, $matches)) {
        return false;
    }

    $short_code = ecwid_shortcode_build_short_code("minicart", $matches[1], array('layout' => $matches[2]));

    return preg_replace($match_expression, $short_code, $input, 1);
}

function ecwid_shortcode_replace_generic($input, $function, $widget) {
    $matches = array();

    $match_expression = '#<div>\s*<script type="text/javascript" src="http://app.ecwid.com/script.js\?([0-9]*)" charset="utf-8"></script>\s*<script type="text/javascript"> ' . $function . '[^<]*</script>\s*</div>#s';

    if (!preg_match($match_expression, $input, $matches)) {
        return false;
    }

    $short_code = ecwid_shortcode_build_short_code($widget, $matches[1], array());

    return preg_replace($match_expression, $short_code, $input, 1);
}



?>
