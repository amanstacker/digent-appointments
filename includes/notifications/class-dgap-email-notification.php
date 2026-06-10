<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Email_Notification {

    private static $email_templates = [];

    public function __construct() {

    }

    /**
     * Send email notifications
     * @param   $status     string
     * @param   $data       array
     * @since   1.0
     * */
    public static function send_notification_email( $status, $data, $type ) {

        if ( empty( self::$email_templates ) ) {
            self::$email_templates  =   apply_filters( 'dgap_email_notification_templates', [] );
        }

        $options = get_option( 'dgap_notifications_settings', [] );

        $option_key     =   "{$type}_email_{$status}_enabled";
        $template_key   =   "{$type}_email_template_{$status}";

        // Check if email enabled
        if ( isset( $options[ $option_key ] ) && $options[ $option_key ] === 0 ) {
            return;
        }

        $template = isset( $options[ $template_key ] ) ? $options[ $template_key ] : '';

        if ( empty( $template ) ) {

            // If user has not saved the notification settings then fetch the defailt template
            $template           =   isset( self::$email_templates["{$type}_email_templates"][$status] ) ? self::$email_templates["{$type}_email_templates"][$status] : '';
            if ( empty( $template ) ) {
                return;
            }
        }

        $data       =   self::format_email_data( $data, $status );

        $email_body =   self::parse_tags( $template, $data );

        $to         =   '';
        if ( 'admin' === $type ) {
            $to         =   get_option( 'admin_email');
        }else if( 'customer' === $type ) {
            $to         =   $data['email'];
        } else if ( 'employee' === $type ) {
            if ( ! empty( $data['staff_id'] ) ) {
                $staff_data             =   DGAP_Staff_Repo::get( absint( $data['staff_id'] ) );
                if ( ! empty( $staff_data ) && is_array( $staff_data ) && ! empty( $staff_data['email'] ) ) {
                    $to              =   $staff_data['email'];
                }
            }
        }
        
        if ( empty( $to ) ) {
            return;
        }

        $subject    =   self::get_email_subject( $type, $status );

        $mail_status = wp_mail(
            $to,
            $subject,
            $email_body,
            ['Content-Type: text/html; charset=UTF-8']
        );

    }


    /**
     * Format data to replace the email tags
     * @param   $data   array
     * @return  $data   array
     * @since   1.0
     * */
    public static function format_email_data( $data, $status = '' ) {

        $service                    =   'NA';
        $service_price              =   '';
        $staff_name                 =   'NA';
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

        if ( ! empty( $data['staff_id'] ) ) { 
            $staff_data             =   DGAP_Staff_Repo::get( absint( $data['staff_id'] ) );
            if ( ! empty( $staff_data ) && is_array( $staff_data ) ) {
                $staff_name         =   '';
                if ( isset( $staff_data['first_name'] ) ) {
                    $staff_name     .=   $staff_data['first_name'] . ' ';
                }
                if ( isset( $staff_data['last_name'] ) ) {
                    $staff_name     .=   $staff_data['last_name'];
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

        $data['booking_id']         =   isset( $data['booking_id'] ) ? $data['booking_id'] : '';
        $data['customer_name']      =   $first_name . ' ' . $last_name;
        $data['customer_email']      =   isset( $data['email'] ) ? $data['email'] : '';
        $data['service_name']       =   $service;
        $data['service_price']      =   isset( $data['price'] ) ? $data['price'] : '';
        $data['staff_name']         =   $staff_name;
        $data['booking_date']       =   isset( $data['booking_date'] ) ? $data['booking_date'] : '';
        $data['booking_time']       =   gmdate( 'Y-m-d' );
        $data['booking_status']     =   ucfirst( $status );
        $data['payment_status']     =   '';
        $data['location']           =   $location_name;
        $data['meeting_link']       =   '';
        $data['reschedule_link']    =   '';
        $data['confirm_link']       =   '';
        $data['cancel_link']        =   '';

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
            '{service_name}'    => esc_html( $data['service_name'] ?? '' ),
            '{service_price}'   => esc_html( $data['service_price'] ?? '' ),
            '{staff_name}'      => esc_html( $data['staff_name'] ?? '' ),
            '{booking_date}'    => esc_html( $data['booking_date'] ?? '' ),
            '{booking_time}'    => esc_html( $data['booking_time'] ?? '' ),
            '{booking_status}'  => esc_html( $data['booking_status'] ?? '' ),
            '{payment_status}'  => esc_html( $data['payment_status'] ?? '' ),
            '{location}'        => esc_html( $data['location'] ?? '' ),

            // URL fields.
            '{meeting_link}'    => esc_url( $data['meeting_link'] ?? '' ),
            '{reschedule_link}' => esc_url( $data['reschedule_link'] ?? '' ),
            '{confirm_link}'    => esc_url( $data['confirm_link'] ?? '' ),
            '{cancel_link}'     => esc_url( $data['cancel_link'] ?? '' ),

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
    private static function get_email_subject( $type, $status ) {

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