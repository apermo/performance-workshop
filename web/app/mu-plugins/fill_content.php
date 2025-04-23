<?php
/**
 * Plugin Name: Content Filler CLI Commands
 * Description: Custom WP-CLI commands for bulk content updates
 * Version: 1.0
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class Fill_Content_Command {

		public function __invoke( $args, $assoc_args ) {
			global $wpdb;

			$content = <<<EOD
<!-- wp:image {"id":2484315,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://performance.ddev.site/wp-content/uploads/2025/03/Mooer_GTRS_MSC20_Pro_Guitar_020_FIN-1024x614.jpg" alt="" class="wp-image-2484315"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
<!-- /wp:paragraph -->

<!-- wp:gallery {"linkTo":"none"} -->
<figure class="wp-block-gallery has-nested-images columns-default is-cropped"><!-- wp:image {"id":2484864,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://performance.ddev.site/wp-content/uploads/2025/03/Harley_Benton_JAMster_Bass_test_review_totale4-1024x614.jpg" alt="Harley Benton JAMster Bass" class="wp-image-2484864"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2484843,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://performance.ddev.site/wp-content/uploads/2025/03/Harley_Benton_JAMster_Bass_test_review_front2-1024x614.jpg" alt="Harley Benton JAMster Bass" class="wp-image-2484843"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2484855,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://performance.ddev.site/wp-content/uploads/2025/03/Harley_Benton_JAMster_Bass_test_review_totale-1024x614.jpg" alt="Harley Benton JAMster Bass" class="wp-image-2484855"/><figcaption class="wp-element-caption">... Desktop-Amp zum Üben prädestiniert.</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2484834,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://performance.ddev.site/wp-content/uploads/2025/03/Harley_Benton_JAMster_Bass_test_review_cockpit_closeup-1024x614.jpg" alt="Harley Benton JAMster Bass" class="wp-image-2484834"/><figcaption class="wp-element-caption">Hier erkennt man eine ganze Reihe ...</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2484813,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://performance.ddev.site/wp-content/uploads/2025/03/zynaptiq-balance-ft-1024x565.jpg" alt="" class="wp-image-2484813"/><figcaption class="wp-element-caption">Zynaptiq Balance</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2484822,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://performance.ddev.site/wp-content/uploads/2025/03/Harley_Benton_JAMster_Bass_compartment-1024x614.jpg" alt="Harley Benton JAMster Bass" class="wp-image-2484822"/><figcaption class="wp-element-caption">... von Batterien zu betreiben. </figcaption></figure>
<!-- /wp:image --></figure>
<!-- /wp:gallery -->
EOD;

			$queries = [
				"UPDATE wp_posts SET comment_status = 'closed', ping_status = 'closed'",
				"UPDATE wp_posts SET post_content = '$content' WHERE post_type = 'post'"
			];

			foreach ( $queries as $query ) {
				$prepared_query = $wpdb->prepare(
					str_replace( '{posts}', $wpdb->posts, $query ),
					$content
				);

				WP_CLI::log( "Executing: " . substr( $prepared_query, 0, 100 ) . "..." );

				$result = $wpdb->query( $prepared_query );

				if ( false === $result ) {
					WP_CLI::warning( "Query failed: " . $wpdb->last_error );
				} else {
					WP_CLI::success( sprintf(
						"Affected %d rows",
						$wpdb->rows_affected
					) );
				}
			}
		}
	}

	WP_CLI::add_command( 'fill-content', new Fill_Content_Command() );
}
