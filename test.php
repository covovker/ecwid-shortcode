<?php

// vim: set foldmethod=marker :

// {{{ dummies to make plugin file inclusion work
function is_admin() {
	return false;
}

function add_shortcode() {
	return false;
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

// {{{ test div...script...js?shopid.. div wrapping
if (!preg_match('!\s*<div>\s*<script type="text/javascript" src="//app.ecwid.com/script.js\?12345"></script>.*\s*</div>\s*!', $code = ecwid_shortcode(array('id' => 12345)))) {
	die('failed wrapping: ' . $code);
}

if (preg_match('!\s*<div>\s*<script type="text/javascript" src="//app.ecwid.com/script.js\?1003"></script>.*\s*</div>\s*!', $code = ecwid_shortcode(array('id' => 12345)))) {
    die("failed remembering script was included before");
}
// }}}

// {{{ product browser specific functions
function get_productbrowser_args($args = array()) {
	$code = trim(ecwid_get_widget_productbrowser($args));

	$regexp = '!<script type="text/javascript">\s*xProductBrowser\((.*)\); </script>!';

	$match = array();
	if(!preg_match($regexp, $code, $match)) {
		die(var_dump('product browser code is wrong:' . $code));
	}

	$args = explode('"', $match[1]);

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
	), 
	"check no params"
);
// }}}

// {{{ cats per row
check_productbrowser(
    array('categoriesperrow' => 12), 
    array(
        "categoriesPerRow=12", 
        "views=", 
        "categoryView=grid", 
        "searchView=grid", 
        "style="
    ),  
    "check valid cats per wow"
);  

check_productbrowser(
    array('categoriesperrow' => -1),
    array(
        "categoriesPerRow=3",
        "views=", 
        "categoryView=grid", 
        "searchView=grid", 
        "style="
    ),  
    "check negative cats per tow"
);

check_productbrowser(
    array('categoriesperrow' => "abc yahoo!"),
    array(
        "categoriesPerRow=3",
        "views=", 
        "categoryView=grid", 
        "searchView=grid", 
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
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
        "style="
    ),
    "check all three valid views"
);
// }}}

// {{{ category view
check_productbrowser(
    array('categoryview' => 'grid'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style="
    ),
    "check default category view"
);

check_productbrowser(
    array('categoryview' => 'list'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=list",
        "searchView=grid",
        "style="
    ),
    "check list category view"
);

check_productbrowser(
    array('categoryview' => 'table'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=table",
        "searchView=grid",
        "style="
    ),
    "check table category view"
);

check_productbrowser(
    array('categoryview' => 'incorrect'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style="
    ),
    "check incorrect category view"
);

check_productbrowser(
    array('categoryview' => 'asdasd"'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style="
    ),
    "check incorrect category view #2"
);
// }}}

// {{{ search view
check_productbrowser(
    array('searchview' => 'grid'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style="
    ),
    "check default search view"
);

check_productbrowser(
    array('searchview' => 'list'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=list",
        "style="
    ),
    "check list search view"
);

check_productbrowser(
    array('searchview' => 'table'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=table",
        "style="
    ),
    "check table search view"
);

check_productbrowser(
    array('searchview' => 'incorrect'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style="
    ),
    "check incorrect search view"
);

check_productbrowser(
    array('searchview' => 'asdasd"'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style="
    ),
    "check incorrect search view #2"
);
// }}}

// {{{ responsive
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
    "check responsive"
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
    array('defaultcategoryid' => '123456'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
        "defaultCategoryId=123456"
    ),
    "check good default category id"
);

check_productbrowser(
    array('defaultcategoryid' => 'abcdef'),
    array(
        "categoriesPerRow=3",
        "views=",
        "categoryView=grid",
        "searchView=grid",
        "style=",
    ),
    "check bad default category id"
);
// }}}

echo "all tests ok\n";

?>
