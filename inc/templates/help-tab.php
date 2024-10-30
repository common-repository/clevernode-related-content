<?php
/**
 * Display content for Support tab in plugin settings page.
 * 
 * @package clevernode-related-content
 * @since   1.0.0
 */
?>
<h2><?php echo esc_html( $attr["title"] ); ?></h2>
<div class="tab-content">
	<div class="support-content">
		<h4>Ask for support</h4>
		<p>Please report us error logs showed in admin notice with error name, status and message.<br>Also send us your account data to be verified, thank you.</p>
		<p>Support email: <a href="mailto:clevernode@metup.it" title="CleverNode Support">clevernode@metup.it</a></p>

		<?php if( isset( $attr ) && ! empty( $attr ) ) { ?>
			<div class="support-user-data">
				<h3>Your Account Data</h3>
				<table class="user-data">
					<th>Account</th>
					<th>IP</th>
					<th>Site URL</th>

					<tr>
						<td>
							<?php echo esc_html( $attr['account_name'] ); ?>
						</td>
						<td>
							<?php echo esc_html( $attr['account_ip'] ); ?>
						</td>
						<td>
							<?php echo esc_url( get_site_url() ); ?>
						</td>
					</tr>
				</table>
			</div>
		<?php } ?>

		<?php
			/*printf(
				'<div class="help-video">%s</div>',
				apply_filters('the_content', 'https://youtu.be/ScMzIvxBSi4')
			);*/
		?>

		<hr style="margin-top: 20px;">

		<h3>F.A.Q.</h3>
		
		<h4>1. How to activate the  plugin?</h4>
		<p>In order to activate the plugin go under <code>Plugin > Installed plugin</code>, search for the <strong>CleverNode Related Content</strong> plugin and click on the "Activate" link.</p>

		<h4>2. How to connect to CleverNode?</h4>

		<p>In oder to connect to the semantic based content correlation service <strong>CleverNode</strong> it is only necessary to click on <em>"Connect to CleverNode"</em> button in the plugin’s settings first page.</p>

		<h4>3. I activated and connected the plugin but I still don't see the widget?</h4>

		<p>If the related articles widget is not immediately visible try to navigate among the articles of your site, in order to give <strong>CleverNode</strong> enough time to elaborate the contents.<br>
		After one or two minutes of navigation you’ll see the widget getting more crowded, aforementioned widget can be found at the bottom of every article or next to the shortcode’s position.</p>

		<hr>

		<p>Check out the updated F.A.Q. on the CleverNode website: <a href="https://clevernode.it/support" target="_blank">https://clevernode.it/support/</a></p>
	</div>
</div>