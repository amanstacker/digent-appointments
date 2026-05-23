<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Frontend_Booking_Ajax {

	/**
	 * Register all AJAX actions
	 */
	public function register() {

		add_action( 'wp_ajax_dgap_front_get_locations', [ $this, 'get_locations' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_locations', [ $this, 'get_locations' ] );
		
		add_action( 'wp_ajax_dgap_front_get_services', [ $this, 'get_services' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_services', [ $this, 'get_services' ] );
		
		add_action( 'wp_ajax_dgap_front_get_staffs', [ $this, 'get_staffs' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_staffs', [ $this, 'get_staffs' ] );
		
		add_action( 'wp_ajax_dgap_front_get_slots', [ $this, 'get_slots' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_slots', [ $this, 'get_slots' ] );
		
		add_action( 'wp_ajax_dgap_front_create_booking', [ $this, 'create_booking' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_create_booking', [ $this, 'create_booking' ] );

	}

	/* ================= Locations ================= */

	public function get_locations() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$results = DGAP_Booking_Repo::get_locations();

		wp_send_json_success( $results );
	}

	/* ================= Services ================= */

	public function get_services() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$location_id = absint( $_POST['location_id'] ?? 0 );

		if ( ! $location_id ) {
			wp_send_json_error( esc_html__( 'Invalid location', 'digent-appointments' ) );
		}

		$results = DGAP_Booking_Repo::get_services_by_location( $location_id );

		wp_send_json_success( $results );
	}

	/* ================= Staff ================= */

	public function get_staffs() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$location_id = absint( $_POST['location_id'] ?? 0 );
		$service_id  = absint( $_POST['service_id'] ?? 0 );

		if ( ! $location_id || ! $service_id ) {
			wp_send_json_error( esc_html__( 'Invalid data', 'digent-appointments' ) );
		}

		$results = DGAP_Booking_Repo::get_staff_by_location_service(
			$location_id,
			$service_id
		);

		wp_send_json_success( $results );
	}

	/* ================= Slots ================= */

	public function get_slots() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$staff_id    = absint( $_POST['staff_id'] ?? 0 );
		$service_id  = absint( $_POST['service_id'] ?? 0 );
		$location_id = absint( $_POST['location_id'] ?? 0 );

		$date        = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );

		if ( ! $staff_id || ! $service_id || ! $location_id || ! $date ) {
			wp_send_json_error( esc_html__( 'Invalid data', 'digent-appointments' ) );
		}

		$schedule = DGAP_Booking_Repo::get_schedule(
			$location_id,
			$service_id,
			$staff_id,
			$date
		);

		if ( ! $schedule ) {
			wp_send_json_error( esc_html__( 'No schedule found', 'digent-appointments' ) );
		}

		$service = DGAP_Booking_Repo::get_service_meta( $service_id );

		if ( ! $service ) {
			wp_send_json_error( esc_html__( 'Service not found', 'digent-appointments' ) );
		}

		$slots = DGAP_Slot_Generator::generate_slot_from_schedule(
			$schedule,
			$service,
			$date
		);

		wp_send_json_success( $slots );
	}

	/* ================= Create Booking ================= */

	public function create_booking() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		// ------------------------------
		// Collect & sanitize data
		// ------------------------------

		$data = [
			'first_name'      => sanitize_text_field( wp_unslash( $_POST['_dgap_form_name'] ?? '' ) ),
			'last_name'       => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
			'email'           => sanitize_email( wp_unslash( $_POST['_dgap_form_email'] ?? '' ) ),
			'phone'           => sanitize_text_field( wp_unslash( $_POST['_dgap_form_phone'] ?? '' ) ),
			'phone_dial_code' => sanitize_text_field( wp_unslash( $_POST['phone_dial_code'] ?? '' ) ),
			'notes'           => sanitize_textarea_field( wp_unslash( $_POST['_dgap_form_description'] ?? '' ) ),

			'location_id'     => absint( $_POST['location_id'] ?? 0 ),
			'service_id'      => absint( $_POST['service_id'] ?? 0 ),
			'staff_id'        => absint( $_POST['staff_id'] ?? 0 ),

			'booking_date'    => sanitize_text_field( wp_unslash( $_POST['booking_date'] ?? '' ) ),
			'start_time'      => sanitize_text_field( wp_unslash( $_POST['start_time'] ?? '' ) ),
			'end_time'        => sanitize_text_field( wp_unslash( $_POST['end_time'] ?? '' ) ),

			'price'           => floatval( $_POST['price'] ?? 0 ),
			'user_id'         => get_current_user_id() ?: null,
			'ip'              => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
		];

		// Get Service Price
		if ( ! empty( $data['service_id'] ) ) {
			$service_data 	=	DGAP_Service_Repo::get( $data['service_id'] );
			if ( is_array( $service_data ) && isset( $service_data['price'] ) ) {
				$data['price'] 	=	$service_data['price'];
			}
		}
		
		// ------------------------------
		// REQUIRED FIELD VALIDATION (UNCHANGED)
		// ------------------------------
		$missing = [];

		if ( ! $data['first_name'] )   $missing[] = 'First Name';
		if ( ! $data['email'] )        $missing[] = 'Email';
		if ( ! $data['location_id'] )  $missing[] = 'Location';
		if ( ! $data['service_id'] )   $missing[] = 'Service';
		if ( ! $data['staff_id'] )     $missing[] = 'Staff';
		if ( ! $data['booking_date'] ) $missing[] = 'Booking Date';
		if ( ! $data['start_time'] )   $missing[] = 'Start Time';
		if ( ! $data['end_time'] )     $missing[] = 'End Time';

		if ( ! empty( $missing ) ) {
			wp_send_json_error(
				esc_html__( 'Missing required fields: ', 'digent-appointments' ) . implode( ', ', $missing )
			);
		}

		if ( ! is_email( $data['email'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid email address', 'digent-appointments' ) );
		}

		$form_id 	=	isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( $form_id <= 0 ) {
			wp_send_json_error( esc_html__( 'Form ID is missing', 'digent-appointments' ) );	
		}

		// Get dynamic custom fields
		$custom_fields = [];

		foreach ( $_POST as $key => $value ) {

			// only custom digent fields
			if ( strpos( $key, '_dgap_form_' ) !== 0 ) {
				continue;
			}

			$custom_fields[ $key ] = sanitize_text_field(
				wp_unslash( $value )
			);
		}

		if ( ! empty( $custom_fields ) ) {
			$data['custom_fields'] 	=	maybe_serialize( $custom_fields );
		}else{
			$data['custom_fields'] 	=	NULL;
		}
		
		// ------------------------------
		// Create booking via repo
		// ------------------------------
		$result = DGAP_Booking_Repo::create_booking( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( esc_html( $result->get_error_message() ) );
		}

		// Assign booking id
		$data['booking_id'] 	=	$result;


		// Send confirmation email to admin
		// Digent_Email_Notification::send_admin_email( 'confirmed', $data );
		DGAP_Email_Notification::send_notification_email( 'confirmed', $data, 'admin' );

		// Send confirmation email to customer
		// Digent_Email_Notification::send_customer_email( 'confirmed', $data );
		DGAP_Email_Notification::send_notification_email( 'confirmed', $data, 'customer' );

		// Send confirmation email to staff
		// Digent_Email_Notification::send_staff_email( 'confirmed', $data );
		DGAP_Email_Notification::send_notification_email( 'confirmed', $data, 'employee' );

		wp_send_json_success(
			[
				'message'    => esc_html__( 'Booking confirmed', 'digent-appointments' ),
				'booking_id' => (int) $result,
			]
		);
	}
}
