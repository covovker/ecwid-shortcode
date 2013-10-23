<?php

// vim: set foldmethod=marker :

// {{{ dummies to make plugin file inclusion work
function is_admin() {
	return false;
}

function add_shortcode() {
	return false;
}

function plugin_dir_path($arg) {
	return __DIR__ . '/ecwid-wordpress-shortcode';
}

function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
        $atts = (array)$atts;
        $out = array();
        foreach($pairs as $name => $default) {
                if ( array_key_exists($name, $atts) )
                        $out[$name] = $atts[$name];
                else
                        $out[$name] = $default;
        }
	
        return $out;
}

function esc_js($value) {
	return $value;
}
// }}}

// {{{ test framework %)
function assert_array_matches($input, $valid, $message) {
	if ($input != $valid) {
		echo ("Failed on: $message\n");
		echo "Got:";
		print_r($input);
		echo "\n";
		echo "Expected:";
		print_r($valid);
		echo "\n";
		if (@$GLOBALS['argv'][1] != 'all') {
			die();
		}
	} elseif (@$GLOBALS['argv'][1] == 'verbose') {
		echo "Passed: $message\n";
	}
}

function starts_with($needle, $haystack) {
	return strpos($haystack, $needle) === 0;
}

function ends_with($needle, $haystack) {
	return strpos($haystack, $needle) === strlen($haystack) - strlen($needle);
}

// }}}

include "./ecwid-wordpress-shortcode/ecwid-wordpress-shortcode.php";

$ecwid = new Ecwid_Shopping_Cart();


// {{{ test div...script...js?shopid.. div wrapping
if (!preg_match('!\s*<div>\s*<script type="text/javascript" src="//app.ecwid.com/script.js\?12345"></script>.*\s*</div>\s*!', $code = $ecwid->shortcode(array('id' => 12345)))) {
	die("failed wrapping: \n" . $code . "\n");
}

if (preg_match('!\s*<div>\s*<script type="text/javascript" src="//app.ecwid.com/script.js\?1003"></script>.*\s*</div>\s*!', $code = $ecwid->shortcode(array('id' => 12345)))) {
    die("failed remembering script was included before");
}
// }}}

// {{{ product browser specific functions
function get_productbrowser_args($args = array()) {
	global $ecwid;

	$code = trim($ecwid->shortcode($args));

	$regexp = '!<script type="text/javascript">\s*xProductBrowser\((.*)\); </script>!';

	$match = array();
	if(!preg_match($regexp, $code, $match)) {
		die(var_dump('product browser code is wrong:' . $code));
	}

	$args = explode("'", $match[1]);

	$result = array();
	foreach ($args as $key => $arg) {
		
		$edges_not_empty = ($key == 0 || $key == count($args) - 1) && $arg != '';
		$is_even_and_not_comma = ($key % 2 == 0 && $key != 0 && $key != count($args) - 1) && $arg != ',';
		if ($edges_not_empty || $is_even_and_not_comma) {
			die("error parsing product browser code: $edges_not_empty,  $is_even_and_not_comma. key: $key arg: $arg args:$match[1]");
		}
		if ($key %2 == 0) continue;

		$result[] = $arg;
	}

	return $result;
}

function check_productbrowser($args = array(), $valid, $message) {
	$result = get_productbrowser_args($args);

	assert_array_matches($result, $valid, $message);
}
// }}}

// {{{ no params
check_productbrowser(
	array(), 
	array(
		"categoriesPerRow=3", 
		"views=", 
		"categoryView=grid", 
		"searchView=grid",
		"style=",
		"responsive=yes"
	), 
	"check no params"
);
// }}}

// {{{ cats per row
check_productbrowser(
    array('categories_per_row' => 12), 
    array(
        "categoriesPerRow=12", 
        "views=", 
        "categoryView=grid", 
        "searchView=grid", 
        "style=",
        "responsive=yes"
    ),  
    "check valid cats per wow"
);  

check_productbrowser(
    array('categories_per_row' => -1),
    array(
        "categoriesPerRow=3",
        "views=", 
        "categoryView=grid", 
        "searchView=grid", 
        "style=",
        "responsive=yes"
    ),  
    "check negative cats per tow"
);

check_productbrowser(
    array('categories_per_row' => "abc yahoo!"),
    array(
        "categoriesPerRow=3",
        "views=", 
        "categoryView=grid", 
        "searchView=grid", 
        "style=",
        "responsive=yes"
    ),  
    "check invalid cats per tow"
);
// }}}

// {{{ views

// {{{ grid
check_productbrowser(
    array('grid' => '5,5'),
    array(
        "categoriesPerRow=3",
        "views=grid(5,5)", 
        "categoryView=grid", 
        "searchView=grid", 
        "style=",
        "responsive=yes"
    ),  
    "check valid grid"
);

check_productbrowser(
    array('grid' => '25,25'),
    array(
        "categoriesPerRow=3",
        "views=grid(3,3)",
        "categoryView=grid", 
        "searchView=grid", 
        "style=",
        "responsive=yes"
    ),  
    "check too big grid"
);

check_productbrowser(
    array('grid' => '4'),
    array(
        "categoriesPerRow=3",
        "views=grid(3,3)",
        "categoryView=grid", 
        "searchView=grid", 
        "style=",
        "responsive=yes"
    ),  
    "check one param grid"
);

check_productbrowser(
    array('grid' => ''),
    array(
        "categoriesPerRow=3",
        "views=grid(3,3)",
        "categoryView=grid", 
        "searchView=grid", 
        "style=",
        "responsive=yes"
    ),  
    "check empty grid"
);

check_productbrowser(
    array('grid' => 'asd,12'),
    array(
        "categoriesPerRow=3",
        "views=grid(3,3)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check invalid first arg grid"
);

check_productbrowser(
    array('grid' => '12,zzz'),
    array(
        "categoriesPerRow=3",
        "views=grid(3,3)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check invalid second arg grid"
);

check_productbrowser(
    array('grid' => '0,0'),
    array(
        "categoriesPerRow=3",
        "views=grid(3,3)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check zero grid"
);

check_productbrowser(
    array('grid' => '0,1'),
    array(
        "categoriesPerRow=3",
        "views=grid(3,3)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check 0x1 grid"
);

check_productbrowser(
    array('grid' => '1,0'),
    array(
        "categoriesPerRow=3",
        "views=grid(3,3)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check 1x0 grid"
);
// }}}

// {{{ list
check_productbrowser(
    array('list' => '25'),
    array(
        "categoriesPerRow=3",
        "views=list(25)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check good list"
);

check_productbrowser(
    array('list' => '125'),
    array(
        "categoriesPerRow=3",
        "views=list(10)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check too big list"
);

check_productbrowser(
    array('list' => '0'),
    array(
        "categoriesPerRow=3",
        "views=list(10)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check empty list"
);

check_productbrowser(
    array('list' => '-1'),
    array(
        "categoriesPerRow=3",
        "views=list(10)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),  
    "check bad list"
);

check_productbrowser(
    array('list' => 'asdasd"'),
    array(
        "categoriesPerRow=3",
        "views=list(10)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),  
    "check bad list #2"
);
// }}}

// {{{ table
check_productbrowser(
    array('table' => '25'),
    array(
        "categoriesPerRow=3",
        "views=table(25)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),  
    "check good table"
);  

check_productbrowser(
    array('table' => '125'),
    array(
        "categoriesPerRow=3",
        "views=table(20)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check too big table"
);

check_productbrowser(
    array('table' => '0'),
    array(
        "categoriesPerRow=3",
        "views=table(20)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check empty table"
);

check_productbrowser(
    array('table' => '-1'),
    array(
        "categoriesPerRow=3",
        "views=table(20)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check bad table"
);

check_productbrowser(
    array('table' => 'asdasd"'),
    array(
        "categoriesPerRow=3",
        "views=table(20)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check bad table #2"
);
// }}}


check_productbrowser(
    array('grid' => '6,8', 'list' => '5', 'table' => '15'),
    array(
        "categoriesPerRow=3",
        "views=grid(6,8) list(5) table(15)",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check all three valid views"
);
// }}}

// {{{ category view
check_productbrowser(
    array('category_view' => 'grid'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check default category view"
);

check_productbrowser(
    array('category_view' => 'list'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=list",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check list category view"
);

check_productbrowser(
    array('category_view' => 'table'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=table",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check table category view"
);

check_productbrowser(
    array('category_view' => 'incorrect'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check incorrect category view"
);

check_productbrowser(
    array('category_view' => 'asdasd"'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check incorrect category view #2"
);
// }}}

// {{{ search view
check_productbrowser(
    array('search_view' => 'grid'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check default search view"
);

check_productbrowser(
    array('search_view' => 'list'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=list",
        "style=",
        "responsive=yes"
    ),
    "check list search view"
);

check_productbrowser(
    array('search_view' => 'table'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=table",
        "style=",
        "responsive=yes"
    ),
    "check table search view"
);

check_productbrowser(
    array('search_view' => 'incorrect'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check incorrect search view"
);

check_productbrowser(
    array('search_view' => 'asdasd"'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check incorrect search view #2"
);
// }}}

// {{{ responsive

check_productbrowser(
    array('responsive' => 'yes'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
	"responsive=yes"
    ),
    "check yes responsive"
);

check_productbrowser(
    array('responsive' => 'no'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
    ),
    "check no responsive"
);

check_productbrowser(
    array('responsive' => ''),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
	"responsive=yes"
    ),
    "check empty responsive"
);

check_productbrowser(
    array('responsive' => 'asdasd"'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
	"responsive=yes"
    ),
    "check bad responsive"
);
// }}}

// {{{ default category id
check_productbrowser(
    array('default_category_id' => '123456'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes",
        "defaultCategoryId=123456"
    ),
    "check good default category id"
);

check_productbrowser(
    array('default_category_id' => 'abcdef'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "responsive=yes"
    ),
    "check bad default category id"
);
// }}}

echo "all tests ok\n";

?>
