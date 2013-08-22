<?php

/*
 * USAGE:
 * $result = ecwid_shortcode_replace_all($input);
 * $input string HTML code
 *
 * returns HTML code with ecwid dashboard widget snippets replaced with the corresponding shortcodes
 * 
 */

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
    $match_expression = '#<div>\s*<script type="text/javascript" src="http://app.ecwid.com/script.js\?([0-9]*)".*?</script>\s*<!-- remove layout parameter if you want to position minicart yourself -->\s*<script type="text/javascript">\s*xMinicart\("style=","layout=([^"]*)"\);\s*</script>.*?</div>#s';

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
