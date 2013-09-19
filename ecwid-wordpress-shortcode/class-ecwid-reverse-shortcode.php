<?php

class Ecwid_Reverse_Shortcode {

	public function process( $input ) {
		while ( $result = ecwid_shortcode_replace_product_browser( $input ) ) {
			$input = $result;
		}

		while ( $result = ecwid_shortcode_replace_minicart( $input ) ) {
			$input = $result;
		}

		$generic_widgets = array(
			'categories'  => 'xCategories',
			'vcategories' => 'xVCategories',
			'search'      => 'xSearchPanel'
		);
		foreach ( $generic_widgets as $widget => $function ) {
			while ( $result = ecwid_shortcode_replace_generic( $input, $function, $widget ) ) {
				$input = $result;
			}
		}

		return $input;
	}

	protected function build_short_code( $type, $id, $other_args ) {

		$args = '';
		foreach ( $other_args as $name => $value ) {
			if ( $name == 'id' ) continue;
			$args .= "$name=\"$value\" ";
		}

		$result = sprintf( '[ecwid id="%s" %swidgets="%s"]', $id, $args, $type );

		return $result;
	}

	protected function replace_product_browser( $input ) {
		$matches           = array();
		$match_expressions = array(
			// The one in ecwid control panel dashboard
			array(
				'expression' => '#<div id="my-store-.*?</div>\s*<div>\s*<script type="text/javascript" src="http://app.ecwid.com/script.js\?([0-9]*)"[^>]*>\s*</script>\s*<script type="text/javascript"> xProductBrowser\("categoriesPerRow=([^"]*)","views=([^"]*)","categoryView=([^"]*)","searchView=([^"]*)"[^)]*\);\s*</script>\s*</div>#s',
				'args'       => array( 'id', 'categories_per_row', 'views', 'category_view', 'search_view' )
			),
			array(
				'expression' => '#<div>\s*<script type="text/javascript" src="//app.ecwid.com/script.js\?([^"]*)">\s*</script>\s*<script type="text/javascript">\s*xProductBrowser\("categoriesPerRow=([^"]*)"\s*,\s*"views=([^"]*)"\s*,\s*"categoryView=([^"]*)"\s*,\s*"searchView=([^"]*)"\s*,\s*"style="\s*,\s*"defaultCategoryId=([^"]*)"\s*,\s*"responsive=([^"]*)"\s*\);\s*</script>\s*</div>#',
				'args'       => array( 'id', 'categories_per_row', 'views', 'category_view', 'search_view', 'default_category_id', 'responsive' )
			)
		);


		$found = false;
		foreach ( $match_expressions as $ind => $item ) {
			if ( preg_match( $item['expression'], $input, $matches ) ) {
				$found = $item;
				break;
			}
		}
		if ( ! $found ) {
			return false;
		}

		$args = array();

		foreach ( $found['args'] as $ind => $arg ) {
			$args[$arg] = $matches[$ind + 1]; // one for match[0] that is full string
		}

		if ( $args['views'] ) {
			$views = explode( ' ', $args['views'] );
			foreach ( $views as $view ) {
				$matches = array();
				if ( preg_match( '!grid\(([0-9]*),([0-9]*)\)!', $view, $matches ) ) {
					$args['grid'] = "$matches[1],$matches[2]";
				}
				elseif ( preg_match( '!list\(([0-9]*)\)!', $view, $matches ) ) {
					$args['list'] = $matches[1];
				}
				elseif ( preg_match( '!table\(([0-9]*)\)!', $view, $matches ) ) {
					$args['table'] = $matches[1];
				}
			}

			unset( $args['views'] );
		}

		$short_code = build_short_code( "productbrowser", $args['id'], $args );

		return preg_replace( $found['expression'], $short_code, $input, 1 );
	}

	protected function replace_minicart( $input ) {
		$matches           = array();
		$match_expressions = array(
			'#<div>\s*<script type="text/javascript" src="http://app.ecwid.com/script.js\?([^"]*)" charset="utf-8"></script>\s*<!-- remove layout parameter if you want to position minicart yourself -->\s*<script type="text/javascript"> xMinicart\("style=","layout=([^"]*)"\);\s*</script>\s*</div>#s',
			'#<div>\s*<script type="text/javascript" src="//app.ecwid.com/script.js\?([0-9]*)"[^>]*?\s*>\s*</script>\s*<script type="text/javascript">\s*xMinicart\("style=","layout=([^"]*)"\);\s*</script>\s*</div>#s'
		);

		$found = false;
		foreach ( $match_expressions as $expression ) {
			if ( preg_match( $expression, $input, $matches ) ) {
				$found = $expression;
				break;
			}
		}

		if ( ! $found ) {
			return false;
		}

		$short_code = ecwid_shortcode_build_short_code( "minicart", $matches[1], array( 'layout' => $matches[2] ) );

		return preg_replace( $found, $short_code, $input, 1 );
	}

	protected function replace_generic( $input, $function, $widget ) {
		$matches = array();

		$match_expressions = array(
			'#<div>\s*<script type="text/javascript" src="http://app.ecwid.com/script.js\?([0-9]*)"[^>]*>\s*</script>\s*<script type="text/javascript"> ' . $function . '[^<]*</script>\s*</div>#s',
			'#<div>\s*<script type="text/javascript" src="//app.ecwid.com/script.js\?([0-9]*)"></script>\s*<script type="text/javascript">\s*' . $function . '\("style="\);\s*</script>\s*</div>#',
		);

		$found = false;
		foreach ( $match_expressions as $ind => $expression ) {
			if ( preg_match( $expression, $input, $matches ) ) {
				$found = $expression;
				break;
			}
		}

		if ( ! $found ) return false;

		$short_code = ecwid_shortcode_build_short_code( $widget, $matches[1], array() );

		return preg_replace( $found, $short_code, $input, 1 );
	}

}