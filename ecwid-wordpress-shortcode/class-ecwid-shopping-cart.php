<?php

class Ecwid_Shopping_Cart {

	const demo_store_id = '1003';
	const ecwid_url     = 'app.ecwid.com';

	const max_view_items = 100;

	const max_categories_per_row     = '25';
	const default_categories_per_row = '3';

	const default_grid_size  = '3,3';
	const default_list_size  = '10';
	const default_table_size = '20';

	const default_search_view   = 'grid';
	const default_category_view = 'grid';

	public function add_hooks() {
		if ( ! is_admin() ) {
			add_shortcode( 'ecwid', array( $this, 'shortcode' ) );
		}
	}

	/**
	 * The ecwid shopping cart shortcode.
	 *
	 * Produces ecwid widgets for listed in "widgets" attributes.
	 *
	 * The supported attributes are:
	 * - 'widgets', 'id' are common attributes
	 * - 'categories_per_row', 'search_view', 'category_view', 'responsive', 'default_category_id', 'grid', 'list', 'table' are for product browser widget.
	 * - 'layout' is for minicart widget
	 *
	 * More information about widgets attributes of certain widgets can be found here: http://kb.ecwid.com/w/page/15853259/Ecwid%20widgets
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function shortcode( $attr ) {
		$args = shortcode_atts(
			array(
				'id'                  => self::demo_store_id,
				'widgets'             => 'productbrowser',
				'categories_per_row'  => self::default_categories_per_row,
				'search_view'         => self::default_search_view,
				'category_view'       => self::default_category_view,
				'responsive'          => 'yes',
				'default_category_id' => 0,
				// grid, list and table are not reset to defaults because if one does not specify them, then the products view does not include that type of display
				'grid'                => null,
				'table'               => null,
				'list'                => null,
			),
			$attr,
			'ecwid'
		);

		$result = '<div>';

		if ( ! defined( 'ECWID_SCRIPTJS' ) ) {
			$store_id = intval( $args['id'] );
			if ( ! $store_id ) {
				$args['id'] = $store_id = self::demo_store_id;
			}
			$result .= '<script type="text/javascript" src="//' . self::ecwid_url . '/script.js?' . $store_id . '"></script>';
			define( 'ECWID_SCRIPTJS', 'Yep' );
		}

		$widgets = explode( ' ', $args['widgets'] );
		foreach ( $widgets as $widget ) {
			$widget = trim( $widget );
			if ( in_array( $widget, array( 'productbrowser', 'categories', 'vcategories', 'search', 'minicart' ) ) ) {
				$getter = "get_widget_$widget";
				$result .= $this->$getter( $args );
			}
		}

		$result .= '</div>';

		return $result;
	}

	protected function get_widget_productbrowser( $args ) {

		// Categories per row
		$cats_per_row = $this->sanitize_int(
			$args['categories_per_row'],
			self::default_categories_per_row,
			self::max_categories_per_row
		);

		// Views
		$views = array();

		$grid = $args['grid'];
		if ( ! is_null( $grid ) ) {
			$value = $this->sanitize_grid(
				$grid,
				self::default_grid_size,
				self::max_view_items
			);

			$views[] = "grid($value)";
		}

		$list = $args['list'];
		if ( ! is_null( $list ) ) {
			$list = $this->sanitize_int(
				$list,
				self::default_list_size,
				self::max_view_items
			);

			$views[] = "list($list)";
		}

		$table = $args['table'];
		if ( ! is_null( $table ) ) {
			$table = $this->sanitize_int(
				$table,
				self::default_table_size,
				self::max_view_items
			);

			$views[] = "table($table)";
		}

		if ( ! empty( $views ) ) {
			$views = implode( " ", $views );
		}
		else {
			$views = '';
		}


		// Search view
		$search_view = $this->sanitize_enum(
			$args['search_view'],
			self::default_search_view,
			array( 'list', 'grid', 'table' )
		);


		// Category view
		$cat_view = $this->sanitize_enum(
			$args['category_view'],
			self::default_category_view,
			array( 'list', 'grid', 'table' )
		);

		// Responsive
		$responsive     = $args['responsive'];
		$responsive     = in_array( $responsive, array( 'yes', 'no' ) ) ? $responsive : 'yes';
		$responsive_str = $responsive == 'yes' ? ',"responsive=yes"' : '';

		// Default category id
		$default_cat = intval( $args['default_category_id'] );
		$def_cat_str = $default_cat ? ',"defaultCategoryId=' . $default_cat . '"' : '';

		$result = sprintf(
			'<script type="text/javascript"> xProductBrowser('
			. '"categoriesPerRow=%s",'
			. '"views=%s",'
			. '"categoryView=%s",'
			. '"searchView=%s",'
			. '"style="'
			. $responsive_str
			. $def_cat_str
			. '); </script>',
			$cats_per_row, $views, $cat_view, $search_view
		);

		return $result;
	}

	protected function get_widget_minicart( $args ) {

		$layout      = $args['layout'];
		$layout_code = '';

		if ( in_array( $layout, array( 'attachToCategories', 'floating', 'Mini', 'MiniAttachToProductBrowser' ) ) ) {
			$layout_code = ',"layout=' . $layout . '"';
		}

		$result = '<script type="text/javascript"> xMinicart("style="' . $layout_code . ');</script>';

		return $result;
	}

	protected function get_widget_categories( $args ) {
		return '<script type="text/javascript"> xCategories("style=");</script>';
	}

	protected function get_widget_vcategories( $args ) {
		return '<script type="text/javascript"> xVCategories("style=");</script>';
	}


	protected function get_widget_search( $args ) {
		return '<script type="text/javascript"> xSearchPanel("style=");</script>';
	}

	/**
	 * Returns $value if $it is a positive int less than $max; $default otherwise.
	 *
	 * @param $value
	 * @param $default
	 * @param $max
	 *
	 * @return int
	 */
	protected function sanitize_int( $value, $default, $max ) {

		$result = $default;

		$value = intval( $value );
		if ( 0 < $value && $max >= $value ) {
			$result = $value;
		}

		return $result;
	}

	/**
	 * Returns $value if it represents one of the $values array items; $default otherwise.
	 *
	 * @param $value
	 * @param $default
	 * @param $values
	 *
	 * @return mixed
	 */
	protected function sanitize_enum( $value, $default, $values ) {

		$result = $default;

		if ( in_array( $value, $values ) ) {
			$result = $value;
		}

		return $result;
	}

	/**
	 * Returns a $default value if $value is not in form "int,int" or its elements count is zero or exceeds $max_total
	 *
	 * @param $value
	 * @param $default
	 * @param $max_total
	 *
	 * @return string
	 */
	protected function sanitize_grid( $value, $default, $max_total ) {
		$result = $default;

		$sizes = explode( ",", $value );
		if ( count( $sizes ) == 2 ) {
			$rows = intval( $sizes[0] );
			$cols = intval( $sizes[1] );

			if (
				$max_total >= $rows
				&& 1 <= $rows
				&& $max_total >= $cols
				&& 1 <= $cols
				&& $max_total >= $rows * $cols
			) {
				$result = "$rows,$cols";
			}
		}

		return $result;
	}
}
