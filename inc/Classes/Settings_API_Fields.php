<?php
/**
 * WP Plugin Settings API wrapper class.
 * Fields Output and Utility.
 * 
 * Based on Tareq1988 Wordpress Settings API Class.
 * @see https://github.com/tareq1988/wordpress-settings-api-class
 *
 * @package clevernode-related-content
 * @since   1.0.0
 */
namespace CleverNodeRCFree\Classes;

/**
 * Don't load directly.
 */
if( ! defined('ABSPATH') ) {
	exit;
}

/**
 * Use required classes.
 */
use CleverNodeRCFree\Utils\Utility;

/**
 * Plugin Settings API.
 * 
 * Handles settings fields display output.
 *
 * @since 1.0.0
 */
if ( !class_exists( 'Settings_API_Fields' ) ):

	class Settings_API_Fields {

		private $utility, $active, $verified;

		public function __construct(){
			$this->utility = new Utility;

			$this->active = $this->utility->get_account_activated();
			$this->verified = $this->utility->get_account_verified();
		}

		/**
		 * Get the value of a settings field
		 *
		 * @param string  $option  settings field name
		 * @param string  $section the section name this field belongs to
		 * @param string  $default default text if it's not found
		 * @return string
		 */
		public function get_option( $option, $section, $default = '' ) {
			$options = get_option( $section );

			if ( isset( $options[$option] ) ) {
				return $options[$option];
			}

			return $default;
		}

		/**
		 * Get field description for display
		 *
		 * @param array   $args settings field args
		 */
		public function get_field_description( $args ) {
			if ( ! empty( $args['desc'] ) ) {
				$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
			} else {
				$desc = '';
			}

			return $desc;
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_text( $args ) {
			$value       = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'text';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$required = $args["required"] ? 'required' : '';

			$html        = sprintf(
				'<input type="%1$s" class="%2$s-text" id="%4$s" name="%3$s" value="%5$s"%6$s %7$s/>',
				esc_attr( $type ),
				esc_attr( $size ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				esc_attr( $value ),
				$placeholder,
				esc_attr( $required )
			);
			$html       .= $this->get_field_description( $args );

			print( $html );
		}

		/**
		 * Displays a email field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_email( $args ) {
			$value       = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'email';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$required = $args["required"] ? 'required' : '';

			$html        = sprintf(
				'<input type="%1$s" class="%2$s-text" id="%4$s" name="%3$s" value="%5$s"%6$s %7$s/>',
				esc_attr( $type ),
				esc_attr( $size ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				esc_attr( $value ),
				$placeholder,
				esc_attr( $required )
			);
			$html       .= $this->get_field_description( $args );

			print( $html );
		}

		/**
		 * Displays an image field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_image( $args, $html = '' ) {
			$value       = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'text';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$image = wp_get_attachment_image_src( $value, 'medium' );

			if ( $image ) {
				$html .= sprintf( '<a href="#" class="wp-media-upl"><img src="%s" alt="Default image" /></a>', $image[0] );
				$html .= '<br><a href="#" class="wp-media-rmv button button-small">Remove image</a>';
				$html .= sprintf( '<input type="hidden" class="%1$s-image image-data" id="%3$s" name="%2$s" value="%4$s"%5$s/>', $size, $args['name'], $args['id'], $value, $placeholder );
			} else {
				$html .= '<a href="#" class="wp-media-upl button button-small">Upload image</a>';
				$html .= '<br><a href="#" class="wp-media-rmv button button-small" style="display:none">Remove image</a>';
				$html .= sprintf( 
					'<input type="hidden" class="%1$s-image image-data" id="%3$s" name="%2$s" value="%4$s"%5$s/>',
					esc_attr( $size ),
					esc_attr( $args['name'] ),
					esc_attr( $args['id'] ),
					esc_attr( $value ),
					$placeholder
				);
			}
			$html     .= $this->get_field_description( $args );

			print( $html );
		}

		/**
		 * Displays a url field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_url( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Displays a number field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_number( $args ) {
			$value       = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'number';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$min         = ( $args['min'] == '' ) ? '' : ' min="' . $args['min'] . '"';
			$max         = ( $args['max'] == '' ) ? '' : ' max="' . $args['max'] . '"';
			$step        = ( $args['step'] == '' ) ? '' : ' step="' . $args['step'] . '"';

			$html        = sprintf(
				'<input type="%1$s" class="%2$s-number" id="%4$s" name="%3$s" value="%5$s"%6$s%7$s%8$s%9$s/>',
				esc_attr( $type ),
				esc_attr( $size ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				esc_attr( $value ),
				$placeholder,
				esc_attr( $min ),
				esc_attr( $max ),
				esc_attr( $step )
			);
			$html       .= $this->get_field_description( $args );

			print( $html );
		}

		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_checkbox( $args ) {
			$value = $this->get_option( $args['id'], $args['option'], $args['std'] );

			$html  = '<fieldset>';

			$html .= sprintf(
				'<input type="checkbox" id="%2$s" name="%1$s" value="1" %3$s />',
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				checked( 1, esc_attr( $value ), false )
			);
			$html  .= sprintf(
				'<label for="%1$s">%2$s</label>',
				esc_attr( $args['id'] ),
				$args['desc']
			);
			if( isset($args['info']) ) {
				$html  .= sprintf(
					'<p class="description">%s</p>',
					$args['info']
				);
			}
			$html  .= '</fieldset>';

			print( $html );
		}

		/**
		 * Displays a multicheckbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_multicheck( $args ) {
			//$value = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$value = get_option( $args['option'] );
			
			$html  = '<fieldset>';
			$html .= sprintf(
				'<input type="hidden" name="%1$s" value="" />',
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] )
			);
			foreach ( $args['options'] as $key => $label ) {
				//$checked = isset( $value[$key] ) ? $value[$key] : '0';
				$checked = in_array($key, $value) ? $key : '0';
				$html    .= sprintf(
					'<label for="%1$s[%2$s]">',
					esc_attr( $args['id'] ),
					esc_attr( $key )
				);
				$html    .= sprintf(
					'<input type="checkbox" class="checkbox" id="%2$s[%3$s]" name="%1$s[]" value="%3$s" %4$s />',
					esc_attr( $args['name'] ),
					esc_attr( $args['id'] ),
					esc_attr( $key ),
					checked( esc_attr( $checked ), $key, false )
				);
				$html    .= sprintf( '%1$s</label><br>',  $label );
			}

			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';

			print( $html );
		}

		/**
		 * Displays a radio button for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_radio( $args ) {
			$value = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$html  = '<fieldset>';

			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf(
					'<label for="%3$s">',
					esc_attr( $args['name'] ),
					esc_attr( $args['id'] ),
					esc_attr( $key )
				);
				$html .= sprintf(
					'<input type="radio" class="radio" id="%3$s" name="%1$s" value="%3$s" %4$s />',
					esc_attr( $args['name'] ),
					esc_attr( $args['id'] ),
					esc_attr( $key ),
					checked( esc_attr( $value ), $key, false )
				);
				$html .= sprintf( '%1$s</label><br>', $label );
			}

			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';

			print( $html );
		}

		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_select( $args ) {
			$value = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf(
				'<select class="%1$s" name="%2$s" id="%3$s">',
				esc_attr( $size ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] )
			);

			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $key ),
					selected( esc_attr( $value ), $key, false ),
					esc_attr( $label )
				);
			}

			$html .= sprintf( '</select>' );
			$html .= $this->get_field_description( $args );

			print( $html );
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_textarea( $args ) {
			$value       = esc_textarea( $this->get_option( $args['id'], $args['option'], $args['std'] ) );
			$size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="'.$args['placeholder'].'"';

			$html        = sprintf(
				'<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s"%4$s>%5$s</textarea>',
				esc_attr( $size ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				$placeholder,
				esc_textarea( $value )
			);
			$html        .= $this->get_field_description( $args );

			print( $html );
		}

		/**
		 * Displays the html for a settings field
		 *
		 * @param array   $args settings field args
		 * @return string
		 */
		public function callback_html( $args ) {
			$html = $this->get_field_description( $args );
			print( $html );
		}

		/**
		 * Displays a rich text textarea for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_wysiwyg( $args ) {
			$value = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : '500px';
			$desc  = $this->get_field_description( $args );

			printf(
				'<div style="max-width: %s;">',
				esc_attr( $size )
			);

			$editor_settings = array(
				'teeny'         => true,
				'textarea_name' => $args['name'],
				'textarea_rows' => 10
			);

			if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
				$editor_settings = array_merge( $editor_settings, $args['options'] );
			}

			wp_editor( esc_attr( $value ), esc_attr( $args['id'] ), $editor_settings );

			print( '</div>' );

			print( $desc );
		}

		/**
		 * Displays a file upload field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_file( $args ) {
			$value = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
			$label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File' );

			$html  = sprintf(
				'<input type="text" class="%1$s-text wpsa-url" id="%2$s" name="%2$s" value="%4$s"/>',
				esc_attr( $size ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				esc_attr( $value )
			);
			$html  .= sprintf(
				'<input type="button" class="button wpsa-browse" value="%s" />',
				$label
			);
			$html  .= $this->get_field_description( $args );

			print( $html );
		}

		/**
		 * Displays a password field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_password( $args ) {
			$value = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html  = sprintf(
				'<input type="password" class="%1$s-text" id="%3$s" name="%2$s" value="%4$s"/>',
				esc_attr( $size ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				esc_attr( $value )
			);
			$html  .= $this->get_field_description( $args );

			print( $html );
		}

		/**
		 * Displays a color picker field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function callback_color( $args ) {
			$value = $this->get_option( $args['id'], $args['option'], $args['std'] );
			$size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html  = sprintf(
				'<input type="text" class="%1$s-text wp-color-picker-field" id="%3$s" name="%2$s" value="%4$s" data-default-color="%5$s" />',
				esc_attr( $size ),
				esc_attr( $args['name'] ),
				esc_attr( $args['id'] ),
				esc_attr( $value ),
				esc_attr( $args['std'] )
			);
			$html  .= $this->get_field_description( $args );

			print( $html );
		}


		/**
		 * Displays a select box for creating the pages select box
		 *
		 * @param array   $args settings field args
		 */
		public function callback_pages( $args ) {
			$dropdown_args = array(
				'selected' => esc_attr( $this->get_option( $args['id'], $args['option'], $args['std'] ) ),
				'name'     => esc_attr( $args['name'] ),
				'id'       => esc_attr( $args['id'] ),
				'echo'     => 0
			);
			$html = wp_dropdown_pages( $dropdown_args );
			print( $html );
		}

		/**
		 * Subscription Form
		 */
		public function callback_subscription_form( $args ){
			$user_data = get_option( 'clevernode_account_data' );
		?>
			<form method="POST" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" id="<?php echo esc_attr( $args["id"] ); ?>" class="<?php echo esc_attr( $args["class"] ); ?>">
				<h4><?php _e( 'Not a subscriber yet?', 'clevernode-related-content' ); ?></h4>
				<p><?php _e( 'Fill the form to request a free account to our service.', 'clevernode-related-content' ); ?></p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="sub_name"><?php _e( 'Contact Name', 'clevernode-related-content' ) ?></label></th>
							<td><input type="text" placeholder="<?php _e( 'Contact Name', 'clevernode-related-content' ) ?>" name="sub_name" id="sub_name" value="" required></td>
						</tr>
						<tr>
							<th scope="row"><label for="sub_company"><?php _e( 'Company', 'clevernode-related-content' ) ?></label></th>
							<td><input type="text" placeholder="<?php _e( 'Company', 'clevernode-related-content' ) ?>" name="sub_company" id="sub_company" value="" required></td>
						</tr>
						<tr>
							<th scope="row"><label for="sub_email"><?php _e('Contact Email', 'clevernode-related-content') ?></label></th>
							<td><input type="email" placeholder="<?php _e( 'Contact Email', 'clevernode-related-content' ) ?>" name="sub_email" id="sub_email" value="" required></td>
						</tr>
						<tr>
							<th scope="row"></th>
							<td>
								<input type="checkbox" class="checkbox" id="subscription_privacy" name="subscription_privacy" value="1" required>
								<label for="subscription_privacy"><?php _e( '<small>I accept the terms and conditions of the <a href="https://clevernode.it/privacy-policy/" target="_blank">Privacy Policy</a></small>.', 'clevernode-related-content' ); ?></label>
							</td>
						</tr>
						<tr scope="row">
							<th>
								<input type="hidden" name="action" value="clevernode_subscription" />
								<?php echo wp_nonce_field( 'clevernode_subscription_action', 'clevernode_subscription_nonce' ); ?>
							</th>  
							<td><input type="submit" class="button button-primary" value="<?php _e( 'Send request', 'clevernode-related-content' ); ?>" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		<?php    
		}

		/**
		 * Connection Form
		 */
		public function callback_connection_form( $args ){
			$user_data = function_exists( 'get_option' ) ? get_option( 'clevernode_account_data' ) : null;
			$admin_email = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'admin_email' ) : null;
			$token = md5('clev' . $_SERVER["REMOTE_ADDR"]);
		?>
			<form method="POST" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" id="<?php echo esc_attr( $args["id"] ); ?>" class="<?php echo esc_attr( $args["class"] ); ?>">
				<h3><?php _e( 'Connect your site to CleverNode', 'clevernode-related-content' ); ?></h3>
				<p><?php _e( 'Simply click on the button below and get related content results.', 'clevernode-related-content' ); ?></p>
				<table class="form-table connection-form">
					<tbody>
						<tr scope="row">
							<th>
								<p><?php _e( 'Setup connection', 'clevernode-related-content' ); ?></p>
								<input type="hidden" name="action" value="clevernode_connection" />
								<?php wp_nonce_field( 'clevernode_connection_action', 'clevernode_connection_nonce' ); ?>
							</th>
							<td>
								<fieldset class="checkbox-switch">
									<input type="checkbox" id="allow_support" name="allow_support" value="1" <?php isset( $user_data["allow_support"] ) ? checked( $user_data["allow_support"], "1", true ) : ''; ?>>
									<label for="allow_support"><?php /* translators: CleverNode Support Team */ printf( esc_html__( 'Allow %s to receive administration email address', 'clevernode-related-content' ), '<strong>' . __( 'CleverNode Support Team', 'clevernode-related-content' ) . '</strong>' ); ?></label>
								</fieldset>
								<div class="switch-yes<?php echo isset( $user_data["allow_support"] ) && $user_data["allow_support"] === "1" ? " switch-default" : ""; ?>" style="display: inline-block;">
									<input type="email" id="admin_email" name="admin_email" value="<?php echo isset( $user_data["admin_email"] ) ? esc_attr( $user_data["admin_email"] ) : ( isset( $admin_email ) ? esc_html( $admin_email ) : '' ); ?>" placeholder="<?php echo esc_html( $admin_email ); ?>" required readonly disabled>
								</div>
								<input type="hidden" name="clevernode-token" value="<?php echo $token; ?>">
								<input type="submit" class="button<?php echo $args['connect'] === true ? ' button-success' : ' button-primary'; ?>" value="<?php $args['connect'] === true ? _e( '✓ Connected to CleverNode', 'clevernode-related-content' ) : _e( 'Connect to CleverNode', 'clevernode-related-content' ); ?>" />
							</td>
						</tr>
						<tr>
							<th><p><?php _e( 'Connection status', 'clevernode-related-content' ); ?></p></th>
							<td class="connection-status">
								<?php if( $args['connect'] === true ) { ?>
									<p class="status-success"><?php _e( 'OK', 'clevernode-related-content' ); ?></p>
								<?php } else {?>
									<p class="status-warning"><?php _e( 'Your site is not connected...', 'clevernode-related-content' ); ?></p>
								<?php } ?>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		<?php    
		}

		/**
		 * Callback activation form
		 */
		public function callback_activation_form( $args ){
			$user_data = get_option( 'clevernode_account_data' );
		?>
			<form method="POST" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" id="<?php echo esc_attr( $args["id"] ); ?>" class="<?php echo esc_attr( $args["class"] ); ?>">
				<h2><?php _e( 'Setup Connection', 'clevernode-related-content' ); ?></h2>
				<p><?php _e( 'Apply for free membership in our service or enter your Account.', 'clevernode-related-content' ); ?></p>
				<table class="form-table">
					<tbody>
						<tr class="checkbox-switch">
							<th scope="row">
								<label for="subscription_active"><?php _e( 'Already a subscriber?', 'clevernode-related-content' ); ?></label>
							</th>
							<td>
								<fieldset>
									<input type="checkbox" id="subscription_active" name="subscription_active" value="<?php echo isset($user_data["subscription_active"]) ? esc_attr( $user_data["subscription_active"] ) : "0"; ?>"<?php echo isset( $user_data["subscription_active"] ) && $user_data["subscription_active"] === "1" ? " checked" : ""; ?>>
									<label for="subscription_active"></label>
								</fieldset>
							</td>
						</tr>
						<tr class="<?php echo ( $this->active && $this->verified ) ? 'switch-yes switch-default' : 'switch-yes'; ?>">
							<th scope="row">
								<label for="validate_account"><?php _e( 'Account', 'clevernode-related-content' ); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text" id="validate_account" name="validate_account" value="<?php echo isset( $user_data["account"] ) ? esc_attr( $user_data["account"] ) : ""; ?>" placeholder="Account name es. cnaxxxxxxx" required>
							</td>
						</tr>
						<tr class="<?php echo ( $this->active && $this->verified ) ? 'switch-yes switch-default' : 'switch-yes'; ?>">
							<th scope="row">
								<label for="validate_email"><?php _e( 'Account Email', 'clevernode-related-content' ); ?></label>
							</th>
							<td>
								<input type="email" class="regular-text" id="validate_email" name="validate_email" value="<?php echo isset( $user_data["email"] ) ? esc_attr( $user_data["email"] ) : ""; ?>" placeholder="name@email.com" required>
							</td>
						</tr>
						<tr class="<?php echo ( $this->active && $this->verified ) ? 'switch-yes switch-default' : 'switch-yes'; ?>">
							<th>
								<input type="hidden" name="action" value="clevernode_account" />
								<?php echo wp_nonce_field( 'clevernode_activation_action', 'clevernode_activation_nonce' ); ?>
							</th>
							<td><input type="submit" class="button button-primary" value="<?php _e( 'Activate plugin', 'clevernode-related-content' ); ?>" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		<?php
		}

		/**
		 * Custom Content
		 */
		public function callback_custom_content($args){
		?>
			<div class="section-help-content <?php echo $args["class"] ? esc_attr( $args["class"] ) : ''; ?>">
				<?php echo $this->utility->get_template_html( $args['tpl'], $args['tpl_args'] ); ?>
			</div>
		<?php
		}

	}

endif;