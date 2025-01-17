<?php

/**
 * Lead scoring report
 */
?>
<div class="groundhogg-chart">
	<h2 class="title"><?php _e( 'Lead Score', 'groundhogg' ); ?></h2>
	<?php if ( has_action( 'groundhogg/admin/report/lead_score' ) ) : ?>
		<?php do_action( 'groundhogg/admin/report/lead_score' ); ?>
	<?php else : ?>
		<img id="leadscore-ad" src="<?php echo GROUNDHOGG_ASSETS_URL . 'images/leadscoring-ad.png'; ?>">
		<div class="notice-no-data">
			<p><?php _e( 'Please install the <b>Lead Scoring</b> extension to view this report.', 'groundhogg' ); ?></p>
			<p><a href="https://www.groundhogg.io/downloads/lead-scoring/" target="_blank"
			      class="button"><?php _e( 'Get it now!', 'groundhogg' ); ?></a></p>
		</div>
	<?php endif; ?>
</div>
