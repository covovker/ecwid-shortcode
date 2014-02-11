<div class="greeting-box complete">
	<div class="image">
		<img src="<?php echo ECWID_PLUGIN_URL; ?>/images/store_ready.png" width="140" />
	</div>
	<div>
		<h3><?php $this->_e( 'Store ID:' ); ?> <b><?php echo get_option( 'ecwid_store_id' ); ?></b></h3>
		<a href="https://my.ecwid.com/cp/?source=wpcom#t1=&t2=Dashboard" target="_blank">
			<?php $this->_e( 'Open Ecwid control panel' ); ?>
		</a>
		<a class="secondary" href="javascript: void(0);" onClick="javascript: jQuery('.ecwid-settings').removeClass('complete');">
			<?php $this->_e( 'Change Store ID' ); ?>
		</a>
	</div>
</div>

<hr />


<div class="complete">
	<h3><?php $this->_e( 'Your Ecwid shortcode' ); ?></h3>

	<textarea readonly="readonly" class="shortcode">[ecwid id="<?php echo esc_html( get_option( 'ecwid_store_id' ) ); ?>" categories_per_row="3" category_view="grid" search_view="list" grid="3,3" list="10" table="20" widgets="productbrowser"]</textarea>

	<h4><?php $this->_e( 'How to use it:' ); ?></h4>
	<ol>
		<li><?php $this->_e( 'Copy the code' ); ?></li>
		<li><?php $this->_e( 'Paste the code to the page that you want your store to reside on' ); ?></li>
		<li><?php $this->_e( 'Save the changes and preview the page on your site - your Ecwid store should be displayed there' ); ?></li>
	</ol>
</div>
