<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Email_Notification {

    private static $email_templates = [];

    public function __construct() {
        add_action( 'admin_post_dgap_email_action', [ $this, 'handle_email_action' ] );
        add_action( 'admin_post_nopriv_dgap_email_action', [ $this, 'handle_email_action' ] );
    }

    /**
     * Generate an HMAC-signed one-click email action URL.
     *
     * @param int    $booking_id Booking ID.
     * @param string $action     Action slug ('confirm' or 'cancel').
     * @param string $actor      Recipient role ('admin', 'employee', or 'customer').
     * @param int    $ttl        Lifetime in seconds (default 7 days).
     * @return string
     */
    public static function generate_action_url( $booking_id, $action, $actor = 'admin', $ttl = 604800 ) {
        $booking_id = absint( $booking_id );
        $actor      = in_array( $actor, [ 'admin', 'employee', 'customer' ], true ) ? $actor : 'admin';
        if ( $booking_id <= 0 || ! in_array( $action, [ 'confirm', 'cancel' ], true ) ) {
            return '';
        }

        $expires = time() + absint( $ttl );
        $payload = $booking_id . '|' . $action . '|' . $actor . '|' . $expires;
        $sig     = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );

        return add_query_arg(
            [
                'action'     => 'dgap_email_action',
                'do'         => $action,
                'actor'      => $actor,
                'booking_id' => $booking_id,
                'expires'    => $expires,
                'sig'        => $sig,
            ],
            admin_url( 'admin-post.php' )
        );
    }

    /**
     * Handle signed one-click email action links (confirm / cancel).
     */
    public function handle_email_action() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $do         = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $actor      = isset( $_GET['actor'] ) ? sanitize_key( wp_unslash( $_GET['actor'] ) ) : 'admin';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $booking_id = isset( $_GET['booking_id'] ) ? absint( $_GET['booking_id'] ) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $expires    = isset( $_GET['expires'] ) ? absint( $_GET['expires'] ) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $sig        = isset( $_GET['sig'] ) ? sanitize_text_field( wp_unslash( $_GET['sig'] ) ) : '';

        if ( ! in_array( $do, [ 'confirm', 'cancel' ], true ) || ! in_array( $actor, [ 'admin', 'employee', 'customer' ], true ) || $booking_id <= 0 || $expires < time() || '' === $sig ) {
            wp_die(
                esc_html__( 'This email action link is invalid or has expired.', 'digent-appointments' ),
                esc_html__( 'Invalid Link', 'digent-appointments' ),
                [ 'response' => 403 ]
            );
        }

        $expected_sig = hash_hmac( 'sha256', $booking_id . '|' . $do . '|' . $actor . '|' . $expires, wp_salt( 'auth' ) );
        if ( ! hash_equals( $expected_sig, $sig ) ) {
            wp_die(
                esc_html__( 'Security verification failed for this action link.', 'digent-appointments' ),
                esc_html__( 'Verification Failed', 'digent-appointments' ),
                [ 'response' => 403 ]
            );
        }

        $booking = DGAP_Booking_Repo::get( $booking_id );
        if ( ! $booking || ! is_array( $booking ) ) {
            wp_die(
                esc_html__( 'Booking not found.', 'digent-appointments' ),
                esc_html__( 'Not Found', 'digent-appointments' ),
                [ 'response' => 404 ]
            );
        }

        $new_status  = ( 'confirm' === $do ) ? 'confirmed' : 'cancelled';
        $is_customer = ( 'customer' === $actor );
        $return_url  = $is_customer ? home_url( '/' ) : admin_url( 'admin.php?page=digent-appointments-bookings' );
        $return_lbl  = $is_customer ? __( 'Back to Website', 'digent-appointments' ) : __( 'View Bookings in Admin', 'digent-appointments' );

        if ( isset( $booking['status'] ) && $booking['status'] === $new_status ) {
            wp_die(
                sprintf(
                    '<div style="font-family:sans-serif;max-width:480px;margin:20px auto;text-align:center;"><h2>%s</h2><p>%s</p><p><a href="%s" style="display:inline-block;padding:10px 20px;background:#10b981;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;">%s</a></p></div>',
                    esc_html__( 'Already Updated', 'digent-appointments' ),
                    /* translators: 1: booking ID, 2: status */
                    sprintf( esc_html__( 'Booking #%1$d is already %2$s.', 'digent-appointments' ), $booking_id, esc_html( ucfirst( $new_status ) ) ),
                    esc_url( $return_url ),
                    esc_html( $return_lbl )
                ),
                esc_html__( 'Booking Status', 'digent-appointments' ),
                [ 'response' => 200 ]
            );
        }

        $updated = DGAP_Booking_Repo::update( $booking_id, [ 'status' => $new_status ] );
        if ( is_wp_error( $updated ) || false === $updated ) {
            wp_die(
                esc_html__( 'Could not update booking status. Please try again later.', 'digent-appointments' ),
                esc_html__( 'Update Failed', 'digent-appointments' ),
                [ 'response' => 500 ]
            );
        }

        do_action( 'dgap_booking_status_updated', $booking_id, $new_status );

        $booking['booking_id'] = $booking_id;
        $booking['status']     = $new_status;
        if ( $is_customer ) {
            self::send_notification_email( $new_status, $booking, 'admin' );
            self::send_notification_email( $new_status, $booking, 'employee' );
        } else {
            self::send_notification_email( $new_status, $booking, 'customer' );
        }

        wp_die(
            sprintf(
                '<div style="font-family:sans-serif;max-width:480px;margin:20px auto;text-align:center;"><h2>%s</h2><p>%s</p><p><a href="%s" style="display:inline-block;padding:10px 20px;background:#10b981;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;">%s</a></p></div>',
                'confirmed' === $new_status ? esc_html__( 'Booking Confirmed', 'digent-appointments' ) : esc_html__( 'Booking Cancelled', 'digent-appointments' ),
                /* translators: 1: booking ID, 2: status */
                sprintf( esc_html__( 'Booking #%1$d has been marked as %2$s.', 'digent-appointments' ), $booking_id, esc_html( ucfirst( $new_status ) ) ),
                esc_url( $return_url ),
                esc_html( $return_lbl )
            ),
            esc_html__( 'Booking Updated', 'digent-appointments' ),
            [ 'response' => 200 ]
        );
    }

    /**
     * Send email notifications
     * @param   $status     string
     * @param   $data       array
     * @since   1.0
     * */
    public static function send_notification_email( $status, $data, $type ) {

        if ( empty( self::$email_templates ) ) {
            if ( ! function_exists( 'dgap_email_notification_templates_clbk' ) ) {
                $tpl_file = DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/email-templates/notification-emails.php';
                if ( file_exists( $tpl_file ) ) {
                    require_once $tpl_file;
                }
            }
            self::$email_templates = apply_filters( 'dgap_email_notification_templates', [] );
        }

        $options = get_option( 'dgap_email_notifications_settings', [] );
        if ( empty( $options ) ) {
            $options = get_option( 'dgap_notifications_settings', [] );
        }

        // Master notification switch.
        if ( isset( $options['enable_notifications'] ) && 0 === (int) $options['enable_notifications'] ) {
            return;
        }

        $option_key   = "{$type}_email_{$status}_enabled";
        $template_key = "{$type}_email_template_{$status}";

        $is_enabled = ! isset( $options[ $option_key ] ) || 1 === (int) $options[ $option_key ];
        $is_enabled = (bool) apply_filters( 'dgap_email_enabled_override', $is_enabled, $type, $status, $data, $options );
        if ( ! $is_enabled ) {
            return;
        }

        $template = isset( $options[ $template_key ] ) ? $options[ $template_key ] : '';

        if ( empty( $template ) ) {
            // If user has not saved the notification settings then fetch the default template.
            $template = isset( self::$email_templates[ "{$type}_email_templates" ][ $status ] ) ? self::$email_templates[ "{$type}_email_templates" ][ $status ] : '';
        }

        $template = apply_filters( 'dgap_email_template_override', $template, $type, $status, $data, $options );
        if ( empty( $template ) ) {
            return;
        }

        $data       = self::format_email_data( $data, $status, $type );
        $email_body = self::parse_tags( $template, $data );

        $to = '';
        if ( 'admin' === $type ) {
            $raw_admin = ! empty( $options['admin_notification_email'] ) ? (string) $options['admin_notification_email'] : get_option( 'admin_email' );
            $emails    = array_filter(
                array_map( 'sanitize_email', array_map( 'trim', explode( ',', $raw_admin ) ) ),
                'is_email'
            );
            $to = ! empty( $emails ) ? array_values( $emails ) : get_option( 'admin_email' );
        } elseif ( 'customer' === $type ) {
            $to = $data['email'] ?? '';
        } elseif ( 'employee' === $type ) {
            if ( ! empty( $data['worker_id'] ) ) {
                $worker_data = DGAP_Worker_Repo::get( absint( $data['worker_id'] ) );
                if ( ! empty( $worker_data ) && is_array( $worker_data ) && ! empty( $worker_data['email'] ) ) {
                    $to = $worker_data['email'];
                }
            }
        }

        if ( empty( $to ) || ( is_string( $to ) && ! is_email( $to ) ) ) {
            return;
        }

        $subject_key = "{$type}_email_subject_{$status}";
        $subject     = ! empty( $options[ $subject_key ] ) ? $options[ $subject_key ] : self::get_email_subject( $type, $status );
        $subject     = apply_filters( 'dgap_email_subject_override', $subject, $type, $status, $data, $options );
        $subject     = wp_strip_all_tags( self::parse_tags( $subject, $data ) );

        $from_name  = ! empty( $options['from_name'] ) ? sanitize_text_field( $options['from_name'] ) : get_bloginfo( 'name' );
        $from_email = ( ! empty( $options['from_email'] ) && is_email( $options['from_email'] ) ) ? $options['from_email'] : get_option( 'admin_email' );
        $raw_reply  = ! empty( $options['reply_to_email'] ) ? (string) $options['reply_to_email'] : '';
        $reply_list = array_filter(
            array_map( 'sanitize_email', array_map( 'trim', explode( ',', $raw_reply ) ) ),
            'is_email'
        );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            sprintf( 'From: %s <%s>', $from_name, $from_email ),
        ];
        if ( ! empty( $reply_list ) ) {
            $headers[] = sprintf( 'Reply-To: %s', implode( ', ', $reply_list ) );
        }

        $attachments = [];
        $ics_file    = '';
        if ( 'customer' === $type && 'confirmed' === $status && ! empty( $options['attach_ics_file'] ) ) {
            $ics_file = self::create_ics_attachment( $data );
            if ( $ics_file ) {
                $attachments[] = $ics_file;
            }
        }

        $max_retries = isset( $options['notification_retry'] ) ? min( 5, absint( $options['notification_retry'] ) ) : 0;
        $mail_status = false;

        for ( $attempt = 0; $attempt <= $max_retries; $attempt++ ) {
            $mail_status = wp_mail( $to, $subject, $email_body, $headers, $attachments );
            if ( $mail_status ) {
                break;
            }
        }

        if ( $ics_file && file_exists( $ics_file ) ) {
            @unlink( $ics_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
        }

        $log_to = is_array( $to ) ? implode( ', ', $to ) : $to;
        do_action( 'dgap_notification_logged', 'email', $type, $status, $log_to, $subject, $email_body, (bool) $mail_status, $data );

    }

    /**
     * Generate a temporary .ics calendar invite file for confirmed appointments.
     *
     * @param array $data Formatted booking data.
     * @return string File path or empty string.
     */
    private static function create_ics_attachment( $data ) {
        $date       = $data['booking_date'] ?? '';
        $start_time = $data['start_time'] ?? '';
        $end_time   = $data['end_time'] ?? '';
        if ( empty( $date ) || empty( $start_time ) ) {
            return '';
        }

        $tz     = wp_timezone();
        $start  = date_create_immutable( "{$date} {$start_time}", $tz );
        $end    = ! empty( $end_time ) ? date_create_immutable( "{$date} {$end_time}", $tz ) : ( $start ? $start->modify( '+30 minutes' ) : false );
        if ( ! $start || ! $end ) {
            return '';
        }

        $uid     = 'dgap-' . ( $data['booking_id'] ?? uniqid() ) . '@' . wp_parse_url( home_url(), PHP_URL_HOST );
        $summary = str_replace( [ "\r", "\n" ], ' ', (string) ( $data['service_name'] ?? 'Appointment' ) );
        $loc     = str_replace( [ "\r", "\n" ], ' ', (string) ( $data['location'] ?? '' ) );

        $ics = implode(
            "\r\n",
            [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//Digent Appointments//EN',
                'BEGIN:VEVENT',
                'UID:' . $uid,
                'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
                'DTSTART:' . $start->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' ),
                'DTEND:' . $end->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' ),
                'SUMMARY:' . $summary,
                'LOCATION:' . $loc,
                'END:VEVENT',
                'END:VCALENDAR',
            ]
        );

        $tmp_file = wp_tempnam( 'appointment-' . absint( $data['booking_id'] ?? 0 ) . '.ics' );
        if ( ! $tmp_file ) {
            return '';
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        file_put_contents( $tmp_file, $ics );
        return $tmp_file;
    }


    /**
     * Format data to replace the email tags
     * @param   $data   array
     * @return  $data   array
     * @since   1.0
     * */
    public static function format_email_data( $data, $status = '', $type = 'admin' ) {

        $service                    =   'NA';
        $service_price              =   '';
        $worker_name                 =   'NA';
        $location_name              =   'NA';

        if ( ! empty( $data['service_id'] ) ) {
            $service_data           =   DGAP_Service_Repo::get( absint( $data['service_id'] ) );
            if ( ! empty( $service_data ) && is_array( $service_data ) ) {
                if ( isset( $service_data['name'] ) ) {
                    $service        =   $service_data['name'];
                }
                if ( isset( $service_data['price'] ) ) {
                    $service_price  =   $service_data['price'];
                }
            }
        }

        $worker_email = '';
        if ( ! empty( $data['worker_id'] ) ) { 
            $worker_data             =   DGAP_Worker_Repo::get( absint( $data['worker_id'] ) );
            if ( ! empty( $worker_data ) && is_array( $worker_data ) ) {
                $worker_name         =   '';
                if ( isset( $worker_data['first_name'] ) ) {
                    $worker_name     .=   $worker_data['first_name'] . ' ';
                }
                if ( isset( $worker_data['last_name'] ) ) {
                    $worker_name     .=   $worker_data['last_name'];
                }
                $worker_name         =   trim( $worker_name );
                if ( ! empty( $worker_data['email'] ) ) {
                    $worker_email    =   $worker_data['email'];
                }
            }
        }

        if ( ! empty( $data['location_id'] ) ) { 
            $location_data             =   DGAP_Location_Repo::get( absint( $data['location_id'] ) );
            if ( ! empty( $location_data ) && is_array( $location_data ) ) {
                $location_name         =   '';
                if ( isset( $location_data['name'] ) ) {
                    $location_name     .=   $location_data['name'] . ' ';
                }
                if ( isset( $location_data['address'] ) ) {
                    $location_name     .=   $location_data['address'];
                }
            }
        }
        
        $first_name                 =   isset( $data['first_name'] ) ? $data['first_name'] : '';
        $last_name                  =   isset( $data['last_name'] ) ? $data['last_name'] : '';

        $start_time                 =   isset( $data['start_time'] ) ? trim( (string) $data['start_time'] ) : '';
        $end_time                   =   isset( $data['end_time'] ) ? trim( (string) $data['end_time'] ) : '';
        $booking_time               =   $start_time;
        if ( '' !== $start_time && '' !== $end_time ) {
            $booking_time           =   $start_time . ' - ' . $end_time;
        }

        $booking_id                 =   isset( $data['booking_id'] ) ? absint( $data['booking_id'] ) : 0;
        $raw_price = isset( $data['price'] ) ? (float) $data['price'] : (float) $service_price;
        $gen_opts  = get_option( 'dgap_general_settings', [] );
        $sym       = dgap_get_currency_symbol( $gen_opts['currency_code'] ?? 'USD' );
        $pos       = $gen_opts['currency_position'] ?? 'before';
        $show_dec  = isset( $gen_opts['show_decimals'] )
            ? ! empty( $gen_opts['show_decimals'] )
            : empty( $gen_opts['hide_decimals'] );
        $decimals  = ( ! $show_dec && floor( $raw_price ) === $raw_price ) ? 0 : 2;
        $num_str   = number_format( $raw_price, $decimals );
        switch ( $pos ) {
            case 'after':
                $formatted_price = $num_str . $sym;
                break;
            case 'before_space':
                $formatted_price = $sym . ' ' . $num_str;
                break;
            case 'after_space':
                $formatted_price = $num_str . ' ' . $sym;
                break;
            default:
                $formatted_price = $sym . $num_str;
                break;
        }

        $data['booking_id']         =   $booking_id ?: '';
        $data['customer_name']      =   trim( $first_name . ' ' . $last_name );
        $data['customer_email']     =   isset( $data['email'] ) ? $data['email'] : '';
        $data['customer_phone']     =   isset( $data['phone'] ) ? $data['phone'] : '';
        $data['service_name']       =   $service;
        $data['service_price']      =   $formatted_price;
        $data['worker_name']         =   $worker_name;
        $data['worker_email']        =   $worker_email;
        $data['booking_date']       =   isset( $data['booking_date'] ) ? $data['booking_date'] : '';
        $data['booking_time']       =   $booking_time;
        $data['booking_status']     =   ucfirst( $status );
        $data['payment_status']     =   '';
        $data['location']           =   $location_name;
        $data['meeting_link']       =   '';
        $data['reschedule_link']    =   apply_filters( 'dgap_reschedule_link', home_url( '/' ), $data );
        $data['confirm_link']       =   $booking_id > 0 ? self::generate_action_url( $booking_id, 'confirm', $type ) : '';
        $data['cancel_link']        =   $booking_id > 0 ? self::generate_action_url( $booking_id, 'cancel', $type ) : '';
        $data['view_booking_link']  =   admin_url( 'admin.php?page=digent-appointments-bookings' );

        return $data;

    }

    /**
     * Parse email dynamic tags with data
     * @param   $template   string
     * @param   $data       array
     * @return  $template   array
     * @since   1.1
     * */
    private static function parse_tags( $template, $data ) {

        $tags = [
            '{booking_id}'      => esc_html( $data['booking_id'] ?? '' ),
            '{customer_name}'   => esc_html( $data['customer_name'] ?? '' ),
            '{customer_email}'  => esc_html( $data['customer_email'] ?? '' ),
            '{customer_phone}'  => esc_html( $data['customer_phone'] ?? '' ),
            '{service_name}'    => esc_html( $data['service_name'] ?? '' ),
            '{service_price}'   => esc_html( $data['service_price'] ?? '' ),
            '{worker_name}'      => esc_html( $data['worker_name'] ?? '' ),
            '{worker_email}'     => esc_html( $data['worker_email'] ?? '' ),
            '{staff_name}'       => esc_html( $data['worker_name'] ?? '' ),
            '{staff_email}'      => esc_html( $data['worker_email'] ?? '' ),
            '{booking_date}'    => esc_html( $data['booking_date'] ?? '' ),
            '{booking_time}'    => esc_html( $data['booking_time'] ?? '' ),
            '{booking_status}'  => esc_html( $data['booking_status'] ?? '' ),
            '{payment_status}'  => esc_html( $data['payment_status'] ?? '' ),
            '{location}'        => esc_html( $data['location'] ?? '' ),

            // URL fields.
            '{meeting_link}'      => esc_url( $data['meeting_link'] ?? '' ),
            '{reschedule_link}'   => esc_url( $data['reschedule_link'] ?? '' ),
            '{confirm_link}'      => esc_url( $data['confirm_link'] ?? '' ),
            '{cancel_link}'       => esc_url( $data['cancel_link'] ?? '' ),
            '{view_booking_link}' => esc_url( $data['view_booking_link'] ?? '' ),

            '{site_name}'       => esc_html( get_bloginfo( 'name' ) ),
        ];

        $template = str_replace(
            array_keys( $tags ),
            array_values( $tags ),
            $template
        );

        // Filter hook to modify the email template.
        $template = apply_filters( 'dgap_modify_email_template', $template );

        return  $template;
    }

    /**
     * Function to get email subject
     * @param   $type       string
     * @return  $subject    string
     * @since   1.0
     * */
    public static function get_email_subject( $type, $status ) {

        $subjects = [
            'admin' => [
                'confirmed' => esc_html__( 'Appointment Confirmed', 'digent-appointments' ),
                'pending'   => esc_html__( 'Appointment Pending', 'digent-appointments' ),
                'reserved'  => esc_html__( 'Appointment Reserved', 'digent-appointments' ),
                'cancelled' => esc_html__( 'Appointment Cancelled', 'digent-appointments' ),
                'reminder'  => esc_html__( 'Appointment Reminder', 'digent-appointments' ),
            ],
            'customer' => [
                'confirmed' => esc_html__( 'Your Appointment Confirmed', 'digent-appointments' ),
                'pending'   => esc_html__( 'Your Appointment Pending', 'digent-appointments' ),
                'reserved'  => esc_html__( 'Your Appointment Reserved', 'digent-appointments' ),
                'cancelled' => esc_html__( 'Your Appointment Cancelled', 'digent-appointments' ),
                'reminder'  => esc_html__( 'Your Appointment Reminder', 'digent-appointments' ),
            ],
            'employee' => [
                'confirmed' => esc_html__( 'New Appointment Confirmed', 'digent-appointments' ),
                'pending'   => esc_html__( 'Appointment Pending', 'digent-appointments' ),
                'reserved'  => esc_html__( 'Appointment Reserved', 'digent-appointments' ),
                'cancelled' => esc_html__( 'Appointment Cancelled', 'digent-appointments' ),
                'reminder'  => esc_html__( 'Appointment Reminder', 'digent-appointments' ),
            ]
        ];

        return isset( $subjects[$type][$status] ) ? $subjects[$type][$status] : esc_html__( 'Appointment Update', 'digent-appointments');
    }

}