<div class="greeting-box">
	<div class="image">
		<img src="<?php echo ECWID_PLUGIN_URL; ?>/images/store_inprogress.png" width="140" />
	</div>
	<div class="text">
	<h3><?php $this->_e( 'Thank you for choosing Ecwid to build your online store' ); ?></h3>
	<p><?php $this->_e( 'Follow these simple steps to publish your Ecwid store on your site' ); ?></p>
	</div>
</div>

<hr />


<h3><?php $this->_e( 'Register at Ecwid' ); ?></h3>
<p>
	<?php $this->_e( 'Create a new Ecwid account which you will use to manage your store and inventory. The registration is free.' ); ?>
</p>

<div class="buttons">
	<a class="button first" target="_blank" href="http://www.ecwid.com/wordpress-com-shopping-cart?source=wpcom">
		<?php $this->_e( 'Create new Ecwid account' ); ?>
	</a>
	<a class="button last" target="_blank" href="https://my.ecwid.com/cp/?source=wpcom#t1=&t2=Dashboard">
		<?php $this->_e( 'I already have Ecwid account, sign in' ); ?>
	</a>
</div>

<p class="note">
	<?php $this->_e( 'You will be able to sign up through your existing Google, Facebook or PayPal profiles as well.' ); ?>
</p>

<h3><?php $this->_e( 'Enter your Store ID' ); ?></h3>
<p>
	<?php $this->_es(
		'Store ID is a unique identifier of any Ecwid store, it consists of several digits. You can find it on the "Dashboard" page of <a %s>Ecwid control panel</a>. Also the Store ID will be sent in the Welcome email after the registration at Ecwid.',
		'href="https://my.ecwid.com/cp/?source=wpcom#t1=&t2=Dashboard" target="_blank"'
	);
	?>
</p>
<div>
	<label for="ecwid_store_id">
		<?php $this->_e('Enter your store id here:'); ?>
	</label>
	<input
		type="number"
		name="ecwid_store_id"
		value="<?php echo esc_attr( get_option( 'ecwid_store_id' ) ); ?>"
		class="store-id"
		placeholder="<?php $this->_e( 'Store ID' ); ?>"
		/>
	<input type="submit" class="button button-primary" value="<?php $this->_e( 'Save and get a shortcode' ); ?>" />
</div>
