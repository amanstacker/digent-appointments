<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Form_Ajax {

	public function register() {

		add_action( 'wp_ajax_dgap_render_preview', [ $this, 'render_preview' ] );
		add_action( 'wp_ajax_dgap_save_form', [ $this, 'save_form' ] );
		add_action( 'wp_ajax_dgap_delete_form', [ $this, 'delete' ] );

	}

	public function render_preview() {

		// First, parse the serialized form data
	    if ( empty( $_POST['form_data'] ) ) {
	        wp_send_json_error( ['message' => esc_html__( 'No data received', 'digent-appointments' ) ] );
	    }
	    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash --Reason Sanitization is handled below
	    parse_str( $_POST['form_data'], $form_data );

	    // Verify nonce with sanitization
	    if ( ! isset( $form_data['_dgap_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $form_data['_dgap_nonce'] ) ), 'dgap_render_preview' ) ) {
	        wp_send_json_error( ['message' => esc_html__( 'Security check failed', 'digent-appointments' ) ] );
	    }

	    // Capability check
	    if ( ! current_user_can( 'manage_options' ) ) {
	        wp_send_json_error( ['message' => esc_html__( 'Unauthorized', 'digent-appointments' ) ] );
	    }

	    require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/class-dgap-form-renderer.php';
        //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason Sanitization is handled in the renderer
	    $html = DGAP_Form_Renderer::render( $form_data );

		wp_send_json_success([
			'html' => $html
		]);

	}

	public static function save_form() {

        // Verify nonce
        if ( ! check_ajax_referer( 'dgap_render_preview', '_dgap_nonce', false ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid nonce', 'digent-appointments' ) ], 403 );
        }

        // Permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized', 'digent-appointments' ) ], 403 );
        }

        $id            = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
        $name          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '(no-title)';
        $layout        = isset( $_POST['layout'] ) ? sanitize_text_field( wp_unslash( $_POST['layout'] ) ) : 'layout-1';
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: Sanitization and escaping is done below.
        $settings      = isset( $_POST['settings'] ) ? $_POST['settings'] : [];
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: Sanitization and escaping is done below.
        $custom_fields = isset( $_POST['custom_fields'] ) ? $_POST['custom_fields'] : [];

        // Sanitize settings recursively
        $settings      = self::sanitize_array( $settings );
        $custom_fields = self::sanitize_array( $custom_fields );

        foreach ( $custom_fields as $key => $field ) {
        	if ( strpos( $field['name'] , 'custom_' ) !== 0 ) {
        		continue;
        	}

        	$custom_fields[ sanitize_key( wp_unslash( $key ) )]['name'] 	=	'_dgap_form_' . str_replace( '-', '_', sanitize_title( $field['label'] ) );
        }	        

        $data = [
            'name'          => $name,
            'layout'        => $layout,
            'settings'      => serialize( $settings ), // It has been sanitized above, so we can safely serialize it here
            'custom_fields' => serialize( $custom_fields ), // It has been sanitized above, so we can safely serialize it here
        ];

        if ( $id ) {
            // Update
            $result = DGAP_Form_Repo::update( $id, $data );
            wp_send_json_success( [ 'id' => $id, 'message' => esc_html__( 'Form updated successfully', 'digent-appointments' ) ] );
        } else {
            // Insert
            $new_id = DGAP_Form_Repo::insert( $data );
            if ( $new_id ) {
                wp_send_json_success( [ 'id' => absint( $new_id ), 'message' => esc_html__( 'Form saved successfully', 'digent-appointments' ) ] );
            } else {
                wp_send_json_error( [ 'message' => esc_html__( 'Failed to save form', 'digent-appointments' ) ] );
            }
        }
    }

    private static function sanitize_array( $array ) {
        if ( ! is_array( $array ) ) {
            return sanitize_text_field( wp_unslash( $array ) );
        }
        return array_map( [ __CLASS__, 'sanitize_array' ], $array );
    }

    /**
	 * Delete Form
	 */
	public function delete() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized', 'digent-appointments' ) ], 403 );
        }
		
		if ( ! empty( $_POST['id'] ) ) {
			DGAP_Form_Repo::delete( absint( $_POST['id'] ) );

			wp_send_json_success();
		}
	}

}