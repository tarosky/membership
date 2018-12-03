<?php

namespace Tarosky\Membership\Controller;


use Tarosky\Membership\Pattern\AbstractController;

/**
 * Taxonomy Handler
 *
 * @package membership
 */
class Taxonomy extends AbstractController {
	
	/**
	 * Add action
	 */
	protected function init_handler() {
		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_action( 'admin_init', [ $this, 'register_default_term' ] );
		add_action( 'save_post', [ $this, 'save_member_level' ], 10, 2 );
		add_action( 'manage_product_posts_custom_column', [ $this, 'render_columns' ], 9, 2 );
	}
	
	/**
	 * Register taxonomy for membership.
	 */
	public function register_taxonomy() {
		register_taxonomy( 'member-type', [ 'product' ], [
			'label'             => __( 'Member Type', 'membership' ),
			'hierarchical'      => true,
			'public'            => true,
			'rewrite'           => [
				'with_front' => false,
			],
			'meta_box_cb'       => [ self::class, 'taxonomy_callback' ],
		] );
	}
	
	/**
	 * Get all terms of member type.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_all_terms() {
		return get_terms( [
			'taxonomy'   => 'member-type',
			'hide_empty' => false,
			'meta_query' => [
				[
					'key'   => 'is_default',
					'value' => 1,
				],
			],
		] );
	}
	
	/**
	 * Auto register default taxonomy.
	 */
	public function register_default_term() {
		$terms = self::get_all_terms();
		if ( ! is_wp_error( $terms ) && ! $terms ) {
			// Term doesn't exist.
			$term = wp_insert_term( __( 'Membership', 'membership' ), 'member-type', [
				'slug' => 'membership',
			] );
			if ( $term && ! is_wp_error( $term ) ) {
				// Save term meta.
				update_term_meta( $term['term_id'], 'is_default', 1 );
			}
		}
	}
	
	/**
	 * Save post meta.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public function save_member_level( $post_id, $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		}
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_membership_type_nonce' ), 'update_membership_type' ) ) {
			return;
		}
		update_post_meta( $post_id, '_membership_level', (int) filter_input( INPUT_POST, 'membership-level' ) );
	}
	
	/**
	 * Render meta box.
	 *
	 * @param \WP_Post $post
	 */
	public static function taxonomy_callback( $post ) {
		$terms = self::get_all_terms();
		if ( is_wp_error( $terms ) ) {
			?>
			<p class="description"><?php echo wp_kses_post( sprintf( __( 'No membership type is registered. Please <a href="%s">register one</a> at admin screen.', 'membership' ), esc_url( admin_url( 'edit-tags.php?taxonomy=member-type&post_type=product' ) ) ) ) ?></p>
			<?php
			return;
		}
		wp_nonce_field( 'update_membership_type', '_membership_type_nonce', false );
		?>
		<div class="membership-type">
			<?php foreach ( $terms as $term ) : ?>
				<ul class="categorychecklist">
					<li>
						<label>
							<input type="checkbox" name="tax_input[member-type][]" value="<?php echo esc_attr( $term->term_id ) ?>" <?php checked( has_term( $term->name, 'member-type', $post ) ) ?> />
							<?php echo esc_html( $term->name ) ?>
						</label>
					</li>
				</ul>
			<?php endforeach; ?>
		</div>
		<p>
			<label for="membership-level"><?php esc_html_e( 'Membership Level', 'membership' ) ?></label>
			<input type="number" id="membership-level" name="membership-level" value="<?php echo esc_attr( get_post_meta( $post->ID, '_membership_level', true ) ) ?>" />
		</p>
		<p class="description">
			<?php esc_html_e( 'You can set member level as number.', 'membership' ) ?>
		</p>
		<?php
	}
	
	/**
	 * Render post column.
	 *
	 * @param string $column
	 * @param int    $post_id
	 */
	public function render_columns( $column, $post_id ) {
		if ( 'name' !== $column ) {
			return;
		}
		$terms = get_the_terms( $post_id, 'member-type' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}
		foreach ( $terms as $term ) {
			?>
			<span style="display:inline-block; padding: 2px 5px; font-size: 0.85em; background: #0073aa; color: #fff; margin-right: 5px; border-radius: 3px;"><?php echo esc_html( $term->name ) ?></span>
			<?php
		}
	}
}
