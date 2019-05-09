<?php
namespace Groundhogg;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Submission
 *
 * Process a from submission if a form submission is in progress.
 *
 * @package     Includes
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.9
 */
class Submission_Handler extends Supports_Errors
{

    /**
     * Acts as an alias for the $_POST variable
     *
     * @var array
     */
    protected $data;

    /**
     * @var array Acts as an alias for $_FILES
     */
    protected $files;

    /**
     * These are the EXPECTED Fields given by the form shortcode present
     *
     * @var array
     */
    protected $fields;

    /**
     * The form config array object
     *
     * @var array
     */
    protected $config;

    /**
     * @var string this is set to the referer which is also the source page
     */
    protected $source;

    /**
     * @var int this ends up being the ID of the form
     */
    protected $form_id;

	/**
	 * @var Step the funnel's step, mostly here to use the is_active()
	 *
	 */
    protected $step;

    /**
     * @var Contact
     */
    protected $contact;

    /**
     * WPGH_Submission constructor.
     *
     * If the GH_SUBMIT nonce is active than a from submission is
     * underway and must be completed.
     */
    public function __construct()
    {
        if ( $this->is_submitting() ) {
            add_action( 'init', [ $this, 'process' ] );
        }
    }

    /**
     * Magic method GET to access $_POST
     *
     * @param $key
     * @return bool
     */
    public function __get( $key )
    {
        if ( property_exists( $this, $key ) ) {

            return $this->$key;

        } else if ( isset( $this->data[ $key ] ) ) {

            return $this->data[ $key ];

        }

        return false;
    }

    /**
     * Set the data to the given value
     *
     * @param $key
     * @param $value
     */
    public function __set( $key, $value )
    {
        $this->data[ $key ] = $value;
    }

    /**
     * IS this data set
     *
     * @param $name
     * @return bool;
     */
    public function __isset( $name )
    {
        return isset( $this->data[ $name ] );
    }

    /**
     * Unset the data
     *
     * @param $name
     */
    public function __unset( $name )
    {
        unset( $this->data[ $name ] );
    }

    /**
     * @return bool
     */
    public function is_admin_submission()
    {
        return is_admin() && current_user_can( 'edit_contacts' );
    }

    /**
     * @return bool
     */
    public function is_submitting()
    {
        return get_request_var( 'action' ) === 'gh_submit_form';
    }

    /**
     * Process the submission.
     *
     * Verify the submission should be processed, if not exit out and die.
     * Set the basic META fields
     *  leadsource
     *  source page
     *  GDPR
     *  Terms
     *  Ip address
     *
     * Add the rest of the meta from the DATA
     */
    public function process()
    {
        if ( ! $this->setup() ){
            return;
        }

        if ( ! $this->verify() ) {
            return;
        }

        if ( ! ( $c = $this->create_contact() ) ){
            return;
        }

        /* Exclude these if submitting from the ADMIN Screen */
        if ( ! $this->is_admin_submission ){

            if ( isset( $this->agree_terms ) ){
                $c->update_meta( 'terms_agreement', 'yes' );
                $c->update_meta( 'terms_agreement_date', date_i18n( wpgh_get_option( 'date_format' ) ) );
                do_action( 'wpgh_agreed_to_terms', $c, $this );
                do_action( 'groundhogg/submission/agreed_to_terms', $c, $this );

                if( $config = $this->get_field_config( 'agree_terms' ) ){
                    $tag_key = base64_encode( $this->agree_terms );
                    if ( key_exists( 'tag_map', $config ) && key_exists( $tag_key, $config[ 'tag_map' ] ) ){
                        $c->apply_tag( [ $config[ 'tag_map' ][ $tag_key ] ] );
                    }
                }

                unset( $this->agree_terms );
            }

            if ( isset( $this->gdpr_consent ) ){
                $c->update_meta( 'gdpr_consent', 'yes' );
                $c->update_meta( 'gdpr_consent_date', date_i18n( wpgh_get_option( 'date_format' ) ) );
                do_action( 'wpgh_gdpr_consented', $c, $this );
                do_action( 'groundhogg/submission/gdpr_gave_consent', $c, $this );

                if( $config = $this->get_field_config( 'gdpr_consent' ) ){
                    $tag_key = base64_encode( $this->gdpr_consent );
                    if ( key_exists( 'tag_map', $config ) && key_exists( $tag_key, $config[ 'tag_map' ] ) ){
                        $c->apply_tag( [ $config[ 'tag_map' ][ $tag_key ] ] );
                    }
                }

                unset( $this->gdpr_consent );
            }

            wpgh_after_form_submit_handler( $c );

        }

        foreach ( $this->data as $key => $value ) {

            $key = sanitize_key( $key );

            if ( is_array( $value ) ){
                $value = implode( ', ', $value );
            }

            if ( strpos( $value, PHP_EOL  ) !== false ){
                $value = sanitize_textarea_field( stripslashes( $value ) );
            } else {
                $value = sanitize_text_field( stripslashes( $value ) );
            }

            if ( $this->has_field( $key ) ) {

                /* NEW: Pass the field's config object to a filter to sanitize it */
                if( $config = $this->get_field_config( $key ) ){
                    $value = apply_filters( 'wpgh_sanitize_submit_value', $value, $config );
                    $value = apply_filters( 'groundhogg/submission/meta/sanitize', $value, $config );
                    $value = apply_filters( "groundhogg/submission/meta/sanitize/{$key}", $value, $config );
                    $c->update_meta( $key, $value );
                    $tag_key = base64_encode( $value );
                    if ( key_exists( 'tag_map', $config ) && key_exists( $tag_key, $config[ 'tag_map' ] ) ){
                        $c->apply_tag( [ $config[ 'tag_map' ][ $tag_key ] ] );
                    }
                }

            }

        }

        if ( ! empty( $_FILES ) ){
            if ( ! $this->upload_files() ){
                return;
            }
        }

        if ( isset( $_POST[ 'email_preferences_nonce' ] ) ){
            $this->process_email_preference_changes();
        }

        $feed_response = apply_filters( 'wpgh_form_submit_feed', true, $this->id, $c, $this );
        $feed_response = apply_filters( 'groundhogg/submission/feed', $feed_response, $this->id, $c, $this );

        if ( ! $this->has_errors() ){

            if ( $this->id ){

                /* Remove the Tracking hook */
                if ( $this->is_admin_submission ){
                    remove_action( 'wpgh_form_submit', array( WPGH()->tracking, 'form_filled' ) );
                }

                do_action( 'wpgh_form_submit', $this->id, $c, $this );
                do_action( 'groundhogg/submission/after', $this->id, $c, $this );
            }

            if ( ! $this->is_admin_submission ){
                /* redirect to ensure cookie is set and can be used on the following page */
                $success_page = $this->step->get_meta('success_page' );
                wp_redirect( $success_page );
                die();
            } else {
                /* Go to contact edit page and add notice of success */
                WPGH()->notices->add( 'form_filled', _x( 'Form submitted', 'notice', 'groundhogg' ) );
                $admin_url = admin_url( sprintf( 'admin.php?page=gh_contacts&action=edit&contact=%d', $this->contact->ID ) );
                wp_redirect( $admin_url );
                die();
            }

        } else if ( is_wp_error( $feed_response ) ){
            $this->add_error( $feed_response );
            return;
        } else {
            /* Default failure handling. */
            $this->add_error( 'UNKNOWN_ERROR', _x( 'Something went wrong.', 'submission_error', 'groundhogg' ) );
            return;
        }


    }

    /**
     * Setup the vars.
     *
     * @return bool whether the setup was successful.
     */
    public function setup(){

        $this->data = wp_unslash( $_POST );
        $this->files = $_FILES;

        $this->source = wpgh_get_referer();

        /* set the form ID as the submission ID */

        if ( isset( $this->step_id ) ) {

            $this->form_id = absint( $this->step_id );
            $this->step = Plugin::$instance->utils->get_step( $this->form_id  );

            unset( $this->step_id );

            if ( ! $this->step->is_active() ){
                $this->add_error( 'inactive_form', _x( 'This form is not accepting submissions.', 'submission_error', 'groundhogg' ) );
                return false;
            }

            $this->fields = $this->step->get_meta( 'expected_fields' );
            $this->config = $this->step->get_meta( 'config' );

        } else {
            $this->form_id = 0;
        }

        if ( empty( $this->fields ) ){
            $this->add_error( 'invalid_form', _x( 'This form is setup incorrectly.', 'submission_error', 'groundhogg' ) );
            return false;
        }

        return true;
    }

    /**
     * Verify the visitor with the nonce check
     * if it fails, return to the previous page.
     *
     * Also performs other various checks,
     * GDPR, reCaptcha & Terms are both checked here as well.
     *
     * @return true|false true on success, false otherwise
     */
    public function verify()
    {
//        if( ! wp_verify_nonce( $_POST[ 'gh_submit_nonce' ], 'gh_submit' ) ) {
//            $this->add_error( 'SECURITY_CHECK_FAILED', _x( 'Failed security check.', 'submission_error', 'groundhogg' ) );
//            return false;
//        }

//        unset( $_POST[ 'gh_submit_nonce' ] );

        // Ensure valid form.
        if ( empty( $this->fields ) ) {
            $this->add_error( 'invalid_form', _x( 'This form is setup incorrectly.', 'submission_error', 'groundhogg' ) );
            return false;
        }

        // GDPR Check
        if ( Plugin::$instance->preferences->is_gdpr_enabled() && $this->has_field( 'gdpr_consent' ) && ! isset_not_empty( $this, 'gdpr_consent' ) ) {
            $this->add_error( 'gdpr_consent_missing', _x( 'You must verify that you consent to receive marketing before you can sign up.', 'submission_error', 'groundhogg' ) );
            return false;
        }

        // Terms is required
        if ( $this->has_field( 'agree_terms' ) && ! isset_not_empty( $this, 'agree_terms' ) ){
            $this->add_error( 'TERMS_AGREEMENT_REQUIRED', _x( 'You must agree to the terms to sign up.', 'submission_error', 'groundhogg' ) );
            return false;
        }

        // Recaptcha check
        if ( $this->has_field( 'g-recaptcha' ) ) {

            if ( ! isset( $this->data[ 'g-recaptcha-response' ] ) ) {
                $this->add_error( 'captcha_verification_failed', _x( 'Failed reCaptcha verification. You are probably a robot.', 'submission_error', 'groundhogg' ) );
                return false;
            }

            $file_name = sprintf(
                "https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s",
                Plugin::$instance->settings->get_option( 'gh_recaptcha_secret_key' ),
                $this->data[ 'g-recaptcha-response' ]
            );

            $verifyResponse = file_get_contents( $file_name );
            $responseData = json_decode( $verifyResponse );

            if( $responseData->success == false ){
                $this->add_error( 'captcha_verification_failed', _x( 'Failed reCaptcha verification. You are probably a robot.', 'submission_error', 'groundhogg' ) );
                return false;
            }
        }

        if( ! class_exists( '\Browser' ) ){
            require_once GROUNDHOGG_PATH . 'includes/lib/browser.php';
        }

        $browser = new \Browser();

        if ( $browser->isRobot() || $browser->isAol() ){
            $this->add_error( 'spam_check_failed', _x( 'Failed spam check.',  'submission_error','groundhogg' ) );
            return false;
        }

        // Check the IP against the spam list
        if ( $this->is_spam( Plugin::$instance->utils->location->get_real_ip() ) ) {
            $this->add_error( 'spam_check_failed', _x( 'Failed spam check.',  'submission_error', 'groundhogg' ) );
            return false;
        }

        // Check all the POST data against the blacklist
        if ( $this->is_spam( $this->data ) ) {
            $this->add_error('spam_check_failed', _x( 'Failed spam check.', 'submission_error', 'groundhogg') );
            return false;
        }

        //check for missing required fields
        foreach ( $this->fields as $field_name ){
            $config = $this->get_field_config( $field_name );

            $missing_required = false;

            if ( isset( $config[ 'required' ] ) && $config[ 'required' ] && $config[ 'required' ] !== "false" ) {
                switch ( $config[ 'type' ] ){
                    case 'file':
                        $missing_required = ! key_exists( $field_name, $this->files ) || $this->files[ $field_name ] === '' || $this->files[ $field_name ] === null;
                        break;
                    default:
                        $missing_required = ! key_exists( $field_name, $this->data ) || $this->data[ $field_name ] === '' || $this->data[ $field_name ] === null;
                        break;
                }
            }

            if ( $missing_required ){
                $this->add_error( new \WP_Error( 'required_field_missing', sprintf( _x( 'Missing a required field: %s', 'submission_error', 'groundhogg' ), esc_html( $config[ 'label' ] ) ), $config ) );
                return false;
            }

        }

        return apply_filters( 'groundhogg/submission_handler/verify', true, $this );
    }

    /**
     * Return whether the form has a given field
     *
     * @param $field string The field in quetion
     *
     * @return bool true if field exists, false otherwise
     */
    public function has_field( $field )
    {
        return in_array( trim( $field ), $this->fields );
    }

    /**
     * Return the config object or false if it doesn't exist
     *
     * @param $field
     * @return bool|mixed
     */
    public function get_field_config( $field )
    {
        return isset( $this->config[$field] )? $this->config[$field] : false;
    }

    /**
     * Check a given value for spam.
     * If it's in the blacklist, mark the contact as spam and die
     *
     * @param $args mixed
     * @return bool true if spam | false if pass
     */
    public function is_spam( $args )
    {
        /* Turn into array */
        if ( ! is_array( $args ) ){ $args = [ $args ]; }

        $blacklist = get_option( 'blacklist_keys', false );

        if ( ! empty( $blacklist ) ) {

            $words = explode(PHP_EOL, $blacklist );

            foreach ($words as $word) {

                foreach ( $args as $key => $value ){

                    /* if found */
                    if ( strpos( $value, $word ) !== false ){
                        return true;
                    /* Further checking */
                    } else if ( apply_filters( 'groundhogg/submission_handler/spam', false, $value, $word, $this ) ){
                        return true;
                    }
                }
            }
        }

        return false;

    }

    /**
     * Create the contact record and return back the contact ID
     *
     * @return Contact|false the $contact or false if failure.
     *
     */
    public function create_contact()
    {
        if ( isset( $this->email ) ){

            $email = sanitize_email( $this->email );

            if ( empty( $email ) || ! is_email( $email ) ){
                $this->add_error( 'INVALID_EMAIL', _x( 'Please provide a valid email address.', 'submission_error', 'groundhogg' ) );
                return false;
            }

            $args = array(
                'email' => $email,
            );

            if ( $this->first_name ){
                $this->first_name =  sanitize_text_field( stripslashes( $this->first_name ) );
                $args[ 'first_name' ] = $this->first_name;
                if ( preg_match( '/[0-9]/', $this->first_name ) ){
                    $this->add_error( 'INVALID_NAME', _x( 'Name should not contain numbers.', 'submission_error', 'groundhogg' ) );
                    return false;
                }
            }

            if ( $this->last_name ){
                $this->last_name =  sanitize_text_field( stripslashes( $this->last_name ) );
                $args[ 'last_name' ] = $this->last_name;
                if ( preg_match( '/[0-9]/', $this->last_name ) ){
                    $this->add_error( 'INVALID_NAME', _x( 'Name should not contain numbers.', 'submission_error', 'groundhogg' ) );
                    return false;
                }
            }

            if ( $this->first_name && $this->last_name && $this->first_name === $this->last_name ){
                $this->add_error( 'INVALID_NAME', _x( 'First and last name cannot be the same.', 'submission_error', 'groundhogg' ) );
                return false;
            }

            /**
             * Do not update if is admin submission
             */
            if ( ! $this->is_admin_submission ){
                if ( is_user_logged_in() ){
                    $args[ 'user_id' ] = get_current_user_id();
                } else {
                    $user = get_user_by( 'email', $email );
                    if ( $user ){
                        $args[ 'user_id' ] = $user->ID;
                    }
                }
            }

            if ( $this->is_spam( $args ) ){
                $this->add_error( 'FOUND_SPAM', _x( 'Your submission looks like spam, please change your information.', 'submission_error', 'groundhogg' ) );
                return false;
            }


            if ( WPGH()->contacts->exists( $email ) ){
                $this->contact = wpgh_get_contact( $email );
                $this->contact->update( $args );
            } else{
                $cid = WPGH()->contacts->add( $args );
                if ( ! $cid ){
                    $this->add_error( 'UNKNOWN_ERROR', _x( 'Something went wrong.', 'submission_error', 'groundhogg' ) );
                    return false;
                }
                $this->contact = wpgh_get_contact( $cid );
            }

            //unset used DATA from the data prop
            unset( $this->first_name );
            unset( $this->last_name );
            unset( $this->email );
            return $this->contact;

        } else if ( WPGH()->tracking->get_contact() instanceof WPGH_Contact) {
            $this->contact = WPGH()->tracking->get_contact();
            return $this->contact;
        } else {
            $this->add_error( 'UNKNOWN_ERROR', _x( 'Something went wrong.', 'submission_error', 'groundhogg' ) );
            return false;
        }

        return false;

    }

    /**
     * Process any file uploads tht may be present.
     *
     * @return bool true if no files or files uploaded, false otherwise.
     */
    public function upload_files()
    {

        if ( empty( $_FILES ) ){
            /* No files present, don't worry about it */
            return true;
        }

        foreach ( $_FILES as $key => $file ) {

            $key = sanitize_key( $key );

            if ($this->has_field( $key ) ) {

                if ($config = $this->get_field_config($key)) {

                    if ($config['type'] === 'file') {

                        if ( $file = $this->handle_file_upload( $key, $config ) ) {

                            if ( is_wp_error($file) || ! is_array( $file ) ) {
                                $this->add_error( $file->get_error_code(), $file->get_error_message() );
                                return false;
                            }

                            $files = $this->contact->get_meta('files');

                            if (!$files) {
                                $files = array();
                            }

                            $file[ 'key' ] = $key;
                            /* Compat for local host WP filesystems */
                            $file = array_map( 'wp_normalize_path', $file );

                            $files[ $key ] = $file;
                            $this->contact->update_meta('files', $files);
                            $this->contact->update_meta($key, $file['url']);

                        } else {

                            $this->add_error( 'FILE_UPLOAD_ERROR',  _x( 'Could not upload file.',  'submission_error', 'groundhogg' ) );
                            return false;

                        }

                    }

                }

            }

        }

        return true;

    }

    /**
     * Upload a file to the Groundhogg file directory
     *
     * @param $key
     * @param $config
     * @return array|bool|WP_Error
     */
    private function handle_file_upload( $key, $config )
    {
        $file = $_FILES[ $key ];
        $size = $file[ 'size' ];

        if ( isset_not_emtpy( $config, 'max_file_size' ) && intval( $size ) > intval( $config[ 'max_file_size' ] ) ){
            return new WP_Error( 'FILE_TOO_BIG', _x( 'The file you have uploaded is too big.',  'submission_error', 'greoundhogg' ) );
        }

        $extension = wp_check_filetype( $file[ 'name' ] );

        /* Check if mime is specified */
        if ( ! empty( $config[ 'file_types' ] ) ){
            $mimes = explode( ',', $config[ 'file_types' ] );
            if ( ! in_array( '.' . $extension[ 'ext' ], $mimes ) ){
                return new WP_Error( 'INCORRECT_MIME', _x( 'You are not permitted to upload this type of file.', 'submission_error', 'groundhogg' ) );
            }
        }

        $upload_overrides = array( 'test_form' => false );

        if ( !function_exists('wp_handle_upload') ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
        }

        $this->set_upload_dirs();

        add_filter( 'upload_dir', array( $this, 'files_upload_dir' ) );
        $mfile = wp_handle_upload( $file, $upload_overrides );
        remove_filter( 'upload_dir', array( $this, 'files_upload_dir' ) );

        if( isset( $mfile['error'] ) ) {

            if ( empty( $mfile[ 'error' ] ) ){
                $mfile[ 'error' ] = _x( 'Could not upload file.',  'submission_error', 'groundhogg' );
            }

            return new WP_Error( 'BAD_UPLOAD', $mfile['error'] );
        }

        return $mfile;
    }
}