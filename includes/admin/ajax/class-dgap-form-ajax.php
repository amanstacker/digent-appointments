<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Form_Ajax {

	public function register() {

		add_action( 'wp_ajax_dgap_render_preview', [ $this, 'render_preview' ] );
		add_action( 'wp_ajax_dgap_save_form', [ $this, 'save_form' ] );
		add_action( 'wp_ajax_dgap_delete_form', [ $this, 'delete' ] );
		add_action( 'wp_ajax_dgap_bulk_action_forms', [ $this, 'bulk_action' ] );

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
        $name          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $layout        = isset( $_POST['layout'] ) ? sanitize_text_field( wp_unslash( $_POST['layout'] ) ) : 'layout-1';
        $status        = isset( $_POST['status'] ) ? absint( $_POST['status'] ) : 0;

        if ( '' === $name ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Form title is required.', 'digent-appointments' ) ] );
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: Sanitization and escaping is done below.
        $settings      = isset( $_POST['settings'] ) ? $_POST['settings'] : [];
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: Sanitization and escaping is done below.
        $custom_fields = isset( $_POST['custom_fields'] ) ? $_POST['custom_fields'] : [];

        // Sanitize settings recursively
        $settings      = self::sanitize_array( $settings );
        $custom_fields = self::sanitize_array( $custom_fields );

        $allowed_modes = [ 'location_service_worker', 'location_service', 'service_only' ];
        $selection_mode = $settings['selection_mode'] ?? 'location_service_worker';
        if ( ! in_array( $selection_mode, $allowed_modes, true ) ) {
            $selection_mode = 'location_service_worker';
        }
        $settings['selection_mode'] = $selection_mode;

        if ( 'service_only' === $selection_mode ) {
            $conflicts = DGAP_Booking_Repo::get_multi_location_services();
            if ( ! empty( $conflicts ) ) {
                $names = wp_list_pluck( $conflicts, 'name' );
                wp_send_json_error(
                    [
                        'message' => sprintf(
                            /* translators: %s: comma-separated service names */
                            esc_html__( 'Cannot use "Service Only" mode because the following service(s) are assigned to multiple locations in Connections: %s', 'digent-appointments' ),
                            esc_html( implode( ', ', $names ) )
                        ),
                    ]
                );
            }
        }

        foreach ( $custom_fields as $key => $field ) {
        	if ( strpos( $field['name'] , 'custom_' ) !== 0 ) {
        		continue;
        	}

        	$custom_fields[ sanitize_key( wp_unslash( $key ) )]['name'] 	=	'_dgap_form_' . str_replace( '-', '_', sanitize_title( $field['label'] ) );
        }	        

        $data = [
            'name'          => $name,
            'layout'        => $layout,
            'status'        => $status ? 1 : 0,
            'settings'      => serialize( $settings ), // It has been sanitized above, so we can safely serialize it here
            'custom_fields' => serialize( $custom_fields ), // It has been sanitized above, so we can safely serialize it here
        ];

        if ( $id ) {
            // Update
            $result = DGAP_Form_Repo::update( $id, $data );
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( [ 'message' => esc_html( $result->get_error_message() ) ] );
            }
            wp_send_json_success( [ 'id' => $id, 'message' => esc_html__( 'Form updated successfully', 'digent-appointments' ) ] );
        } else {
            // Insert
            $new_id = DGAP_Form_Repo::insert( $data );
            if ( is_wp_error( $new_id ) ) {
                wp_send_json_error( [ 'message' => esc_html( $new_id->get_error_message() ) ] );
            }
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

	/**
	 * Bulk Actions (Activate, Deactivate, Delete)
	 */
	public function bulk_action() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized', 'digent-appointments' ) ], 403 );
		}

		$action = sanitize_text_field( wp_unslash( $_POST['bulk_action'] ?? '' ) );
		$ids    = isset( $_POST['ids'] ) && is_array( $_POST['ids'] )
			? array_values( array_filter( array_map( 'absint', $_POST['ids'] ) ) )
			: [];

		if ( empty( $ids ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No items selected.', 'digent-appointments' ) ] );
		}

		if ( 'status_active' === $action ) {
			DGAP_Form_Repo::bulk_update_status( $ids, 1 );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected forms activated.', 'digent-appointments' ) ] );
		}

		if ( 'status_inactive' === $action ) {
			DGAP_Form_Repo::bulk_update_status( $ids, 0 );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected forms deactivated.', 'digent-appointments' ) ] );
		}

		if ( 'delete' === $action ) {
			DGAP_Form_Repo::bulk_delete( $ids );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected forms deleted.', 'digent-appointments' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Invalid bulk action.', 'digent-appointments' ) ] );
	}

}