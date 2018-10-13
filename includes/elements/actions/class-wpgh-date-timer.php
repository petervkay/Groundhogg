<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2018-10-04
 * Time: 5:42 PM
 */

class WPGH_Date_Timer extends WPGH_Funnel_Step
{

    /**
     * @var string
     */
    public $type    = 'date_timer';

    /**
     * @var string
     */
    public $group   = 'action';

    /**
     * @var string
     */
    public $icon    = 'date-timer.png';

    /**
     * @var string
     */
    public $name    = 'Date Timer';

    /**
     * @param $step WPGH_Step
     */
    public function settings( $step )
    {

        $run_date = $step->get_meta( 'run_date' );
        if ( ! $run_date )
            $run_date = date( 'Y-m-d', strtotime( '+1 day' ) );

        $run_time = $step->get_meta( 'run_time' );
        if ( ! $run_time )
            $run_time = '09:30';

        ?>

        <table class="form-table">
            <tbody>
            <tr>
                <th><?php echo esc_html__( 'Wait till:', 'groundhogg' ); ?></th>
                <td>
                    <?php $args = array(
                        'name'          => $step->prefix( 'run_date' ),
                        'id'            => $step->prefix( 'run_date' ),
                        'value'         => $run_date,
                        'placeholder'   => 'yyy-mm-dd',
                    );

                    echo WPGH()->html->input( $args ); ?>

                    <script>jQuery(function($){$('#<?php echo $step->prefix( 'run_date' ); ?>').datepicker({
                        changeMonth: true,
                        changeYear: true,
                        minDate:0,
                        dateFormat:'yy-m-d'
                    })});</script>
            </tr>
            <tr>
                <th><?php echo esc_html__( 'And run at:', 'groundhogg' ); ?></th>
                <td>
                    <?php

                    echo WPGH()->html->dropdown( $args );

                    $args = array(
                        'type'  => 'time',
                        'name'  => $step->prefix( 'run_time' ),
                        'id'    => $step->prefix( 'run_time' ),
                        'value' => $run_time,
                    );

                    echo WPGH()->html->input( $args ); ?>
                </td>
            </tr>
            </tbody>
        </table>

        <?php
    }

    /**
     * Save the step settings
     *
     * @param $step WPGH_Step
     */
    public function save( $step )
    {

        $amount = sanitize_text_field(  $_POST[ $step->prefix( 'run_date' )] );
        $date = date( 'Y-m-d', strtotime( $amount ) );
        $step->update_meta( 'run_date', $date );

        $type = sanitize_text_field( $_POST[ $step->prefix( 'run_time' ) ] );
        $step->update_meta( 'run_time', $type );

    }

    /**
     * Override the parent and set the run time of this function to the settings
     *
     * @param WPGH_Step $step
     * @return int
     */
    public function enqueue( $step )
    {
        $run_date = $step->get_meta( 'run_date' );
        if ( ! $run_date )
            $run_date = date( 'Y-m-d', strtotime( '+1 day' ) );

        $run_time = $step->get_meta( 'run_time' );
        if ( ! $run_time )
            $run_time = '09:30';

        $time_string = $run_date . ' ' . $run_time;

        /* convert to UTC */
        $final_time = strtotime( $time_string ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

        return $final_time;
    }

    /**
     * Process the apply tag step...
     *
     * @param $contact WPGH_Contact
     * @param $event WPGH_Event
     *
     * @return true
     */
    public function run( $contact, $event )
    {
        //do nothing
        return true;
    }

}