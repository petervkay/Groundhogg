<?php

namespace Groundhogg\Admin\Contacts;

use Groundhogg\Saved_Searches;
use function Groundhogg\action_input;
use function Groundhogg\get_db;
use function Groundhogg\get_url_var;
use function Groundhogg\html;

?>
<div id="search-filters" class="postbox <?php echo ( get_url_var( 'is_searching' ) ) ? '' : 'hidden'; ?>">
	<form method="get">
		<?php echo html()->input( [
			'type'  => 'hidden',
			'name'  => 'is_searching',
			'value' => 'on',
		] ); ?>
		<?php html()->hidden_GET_inputs(); ?>

		<div class="first-name-search inline-block search-param">

			<?php

			echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Filter By First Name', 'groundhogg' ) );
			?>
			<p><?php

				echo html()->dropdown( [
					'name'        => 'first_name_compare',
					'class'       => 'first-name-compare',
					'options'     => [
						'equals'      => __( 'Equals', 'groundhogg' ),
						'contains'    => __( 'Contains', 'groundhogg' ),
						'starts_with' => __( 'Starts with', 'groundhogg' ),
						'ends_with'   => __( 'Ends with', 'groundhogg' ),
					],
					'selected'    => sanitize_text_field( get_url_var( 'first_name_compare' ) ),
					'option_none' => false,
					'id'          => '',
				] );
				?></p>
			<p><?php

				echo html()->input( [
					'name'        => 'first_name',
					'value'       => sanitize_text_field( get_url_var( 'first_name' ) ),
					'class'       => 'input first-name',
					'placeholder' => __( 'John' )
				] );
				?></p>
		</div>
		<div class="last-name-search inline-block search-param">

			<?php

			echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Filter By Last Name', 'groundhogg' ) );
			?>
			<p><?php

				echo html()->dropdown( [
					'name'        => 'last_name_compare',
					'class'       => 'last-name-compare',
					'options'     => [
						'equals'      => __( 'Equals', 'groundhogg' ),
						'contains'    => __( 'Contains', 'groundhogg' ),
						'starts_with' => __( 'Starts with', 'groundhogg' ),
						'ends_with'   => __( 'Ends with', 'groundhogg' ),
					],
					'selected'    => sanitize_text_field( get_url_var( 'last_name_compare' ) ),
					'option_none' => false,
					'id'          => '',
				] );
				?></p>
			<p><?php


				echo html()->input( [
					'name'        => 'last_name',
					'value'       => sanitize_text_field( get_url_var( 'last_name' ) ),
					'class'       => 'input last-name',
					'placeholder' => __( 'Doe' )
				] );
				?></p>
		</div>
		<div class="email-search inline-block search-param">
			<?php
			echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Filter By Email', 'groundhogg' ) );
			?>
			<p><?php
				echo html()->dropdown( [
					'name'        => 'email_compare',
					'class'       => 'email-compare',
					'options'     => [
						'equals'      => __( 'Equals', 'groundhogg' ),
						'contains'    => __( 'Contains', 'groundhogg' ),
						'starts_with' => __( 'Starts with', 'groundhogg' ),
						'ends_with'   => __( 'Ends with', 'groundhogg' ),
					],
					'selected'    => sanitize_text_field( get_url_var( 'email_compare' ) ),
					'option_none' => false,
					'id'          => '',
				] );
				?></p>
			<p><?php

				echo html()->input( [
					'name'        => 'email',
					'value'       => sanitize_text_field( get_url_var( 'email' ) ),
					'class'       => 'input email',
					'placeholder' => __( 'example@mydomain.com' )
				] );
				?></p>
			<p>
		</div>
		<div class="optin-status inline-block search-param">

			<?php

			echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Filter By Optin Status', 'groundhogg' ) );
			echo "&nbsp;";

			echo html()->wrap( html()->select2( [
				'name'     => 'optin_status[]',
				'id'       => 'optin_status',
				'class'    => 'gh-select2',
				'options'  => [
					1 => __( 'Unconfirmed', 'groundhogg' ),
					2 => __( 'Confirmed', 'groundhogg' ),
					3 => __( 'Unsubscribed', 'groundhogg' ),
					4 => __( 'Weekly', 'groundhogg' ),
					5 => __( 'Monthly', 'groundhogg' ),
					6 => __( 'Bounced', 'groundhogg' ),
					7 => __( 'Spam', 'groundhogg' ),
					8 => __( 'Complained', 'groundhogg' ),
				],
				'multiple' => true,
				'selected' => wp_parse_id_list( get_url_var( 'optin_status' ) ),
			] ), 'p' );

			?>
		</div>
		<div class="meta-search inline-block search-param">

			<?php

			echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Filter By Meta', 'groundhogg' ) );

			$keys = get_db( 'contactmeta' )->get_keys();

			?><p><?php

				echo html()->dropdown( [
					'name'        => 'meta_key',
					'class'       => 'meta-key',
					'options'     => $keys,
					'selected'    => sanitize_key( get_url_var( 'meta_key' ) ),
					'option_none' => __( 'Select a meta key', 'groundhogg' ),
					'id'          => '',
				] );

				?></p>
			<p><?php


				echo html()->dropdown( [
					'name'        => 'meta_compare',
					'class'       => 'meta-compare',
					'options'     => [
						'='          => __( 'Equals', 'groundhogg' ),
						'!='         => __( 'Not Equals', 'groundhogg' ),
						'>'          => __( 'Greater than', 'groundhogg' ),
						'<'          => __( 'Less than', 'groundhogg' ),
						'REGEXP'     => __( 'Contains', 'groundhogg' ),
						'NOT REGEXP' => __( 'Does not contain', 'groundhogg' ),
					],
					'selected'    => sanitize_text_field( get_url_var( 'meta_compare' ) ),
					'option_none' => false,
					'id'          => '',
				] );
				?></p>
			<p><?php


				echo html()->input( [
					'name'        => 'meta_value',
					'value'       => sanitize_text_field( get_url_var( 'meta_value' ) ),
					'class'       => 'input meta-value',
					'placeholder' => __( 'Value' )
				] );

				?>
			</p>
		</div>
		<div class="date-search inline-block search-param">

			<?php

			echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Filter By Date', 'groundhogg' ) );

			?><p><?php
				_e( 'From: ' );

				echo '<br/>';

				echo html()->date_picker( [
					'min-date' => date( 'Y-m-d', strtotime( '-100 years' ) ),
					'name'     => 'date_after',
					'class'    => 'date-after',
					'value'    => sanitize_text_field( get_url_var( 'date_after' ) ),
				] );

				?></p>
			<p><?php

				_e( 'To: ' );
				echo '<br/>';

				echo html()->date_picker( [
					'min-date' => date( 'Y-m-d', strtotime( '-100 years' ) ),
					'name'     => 'date_before',
					'class'    => 'date-before',
					'value'    => sanitize_text_field( get_url_var( 'date_before' ) ),
				] );

				?></p>
		</div>
		<div class="owner-search inline-block search-param">
			<?php

			echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Filter By Owner', 'groundhogg' ) );

			?><p><?php

				echo html()->dropdown_owners( [
					'name'     => 'owner',
					'class'    => 'owner',
					'selected' => absint( get_url_var( 'owner' ) ),
				] );

				?></p>
		</div>
		<div class="tags-filter search-param">
			<?php echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Filter By Tags', 'groundhogg' ) ); ?>
			<div class="filters">
				<div class="tags-include inline-block search-param">
					<?php

					echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Includes contacts with', 'groundhogg' ) );
					echo "&nbsp;";
					echo html()->dropdown( [
						'name'        => 'tags_include_needs_all',
						'id'          => 'tags_include_needs_all',
						'class'       => '',
						'options'     => array(
							0 => __( 'Any', 'groundhogg' ),
							1 => __( 'All', 'groundhogg' )
						),
						'selected'    => absint( get_url_var( 'tags_include_needs_all' ) ),
						'option_none' => false
					] );

					echo html()->e( 'p', [], [
						html()->tag_picker( [
							'name'     => 'tags_include[]',
							'id'       => 'tags_include',
							'selected' => wp_parse_id_list( get_url_var( 'tags_include' ) )
						] )

					] );

					?>
				</div>
				<div class="tags-exclude inline-block search-param">
					<?php

					echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Excludes contacts with', 'groundhogg' ) );
					echo "&nbsp;";

					echo html()->dropdown( [
						'name'        => 'tags_exclude_needs_all',
						'id'          => 'tags_exclude_needs_all',
						'class'       => '',
						'options'     => array(
							0 => __( 'Any', 'groundhogg' ),
							1 => __( 'All', 'groundhogg' )
						),
						'selected'    => absint( get_url_var( 'tags_exclude_needs_all' ) ),
						'option_none' => false
					] );

					echo html()->e( 'p', [], [
						html()->tag_picker( [
							'name'     => 'tags_exclude[]',
							'id'       => 'tags_exclude',
							'selected' => wp_parse_id_list( get_url_var( 'tags_exclude' ) )
						] )
					] );

					?>
				</div>
			</div>
		</div>
		<?php do_action( 'groundhogg/admin/contacts/search' ); ?>

		<div class="start-search">
			<?php submit_button( __( 'Search' ), 'primary', 'submit', false ); ?>
		</div>
	</form>
	<div class="saved-search-form">
		<div class="inline-block search-param">
			<form method="post">
				<?php

				wp_nonce_field( 'load_search' );
				action_input( 'load_search' );

				echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Saved Searches', 'groundhogg' ) );

				?><p><?php

					echo html()->dropdown( [
						'name'     => 'saved_search',
						'class'    => 'saved-search',
						'options'  => Saved_Searches::instance()->get_for_select(),
						'selected' => get_url_var( 'saved_search_id' ),
					] );

					?></p>
				<?php submit_button( __( 'Load Search', 'groundhogg' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>

		<?php if ( get_url_var( 'is_searching' ) === 'on' ): ?>
			<div class="inline-block search-param">
				<form method="post" class="save-this-search">
					<?php

					wp_nonce_field( 'save_this_search' );
					action_input( 'save_this_search' );

					echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Save This Search', 'groundhogg' ) );

					?><p><?php

						echo html()->input( [
							'name'        => 'saved_search_name',
							'placeholder' => __( 'My search name...', 'groundhogg' ),
						] );

						?></p>
					<?php submit_button( __( 'Save Search', 'groundhogg' ), 'secondary', 'submit', false ); ?>
				</form>
			</div>
		<?php endif; ?>
		<?php
		$search_id = get_url_var( 'saved_search_id' );
		if ( $search_id && Saved_Searches::instance()->get( $search_id ) ): ?>
			<div class="inline-block search-param">
				<form method="post" class="delete-search">
					<?php

					wp_nonce_field( 'delete_search' );
					action_input( 'delete_search' );

					echo html()->input( [
						'type'  => 'hidden',
						'name'  => 'saved_search',
						'value' => $search_id
					] );

					echo html()->e( 'label', [ 'class' => 'search-label' ], __( 'Delete Current Search', 'groundhogg' ) ); ?>
					<p>
						<?php submit_button( __( 'Delete Search', 'groundhogg' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>
