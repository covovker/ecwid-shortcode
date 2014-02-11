<div class="wrap ecwid-settings <?php if ( get_option( 'ecwid_store_id' ) ) echo 'complete'; ?>">
	<h2><?php echo $this->get_page_title(); ?></h2>
	<form method="POST" action="options.php">
	<?php settings_fields( 'ecwid_options_page' ); ?>

	<div class="settings-block initial">
		<?php include ECWID_PLUGIN_DIR . 'templates/settings-initial.php'; ?>
	</div>

	<div class="settings-block complete">
		<?php include ECWID_PLUGIN_DIR . 'templates/settings-complete.php'; ?>
	</div>

	<hr />

	<p><?php $this->_es( 'Questions? Visit <a %s>Ecwid support center</a>.', 'target="_blank" href="http://en.support.wordpress.com/ecwid/"' ); ?></p>
	</form>
</div>