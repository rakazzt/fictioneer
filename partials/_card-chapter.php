<?php
/**
 * Partial: Chapter Card
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 4.0
 *
 * @internal $args['show_type']    Whether to show the post type label. Unsafe.
 * @internal $args['cache']        Whether to account for active caching. Unsafe.
 * @internal $args['hide_author']  Whether to hide the author. Unsafe.
 * @internal $args['order']        Current order. Default 'desc'. Unsafe.
 * @internal $args['orderby']      Current orderby. Default 'modified'. Unsafe.
 */
?>

<?php

// Setup
$title = fictioneer_get_safe_title( get_the_ID() );
$story_id = fictioneer_get_field( 'fictioneer_chapter_story' );
$story_unpublished = get_post_status( $story_id ) !== 'publish';
$story_data = $story_id ? fictioneer_get_story_data( $story_id, false ) : null; // Does not refresh comment count!
$chapter_rating = fictioneer_get_field( 'fictioneer_chapter_rating' );
$story_thumbnail_url_full = $story_id ? get_the_post_thumbnail_url( $story_id, 'full' ) : null;
$text_icon = fictioneer_get_field( 'fictioneer_chapter_text_icon' );
$excerpt = fictioneer_get_forced_excerpt( $post, 512, true );

// Taxonomies
$tags = false;
$fandoms = false;
$characters = false;
$genres = false;

if (
  get_option( 'fictioneer_show_tags_on_chapter_cards' ) &&
  ! get_option( 'fictioneer_hide_taxonomies_on_chapter_cards' )
) {
  $tags = get_the_tags();
}

if ( ! get_option( 'fictioneer_hide_taxonomies_on_chapter_cards' ) ) {
  $fandoms = get_the_terms( $post->ID, 'fcn_fandom' );
  $characters = get_the_terms( $post->ID, 'fcn_character' );
  $genres = get_the_terms( $post->ID, 'fcn_genre' );
}

// Flags
$hide_author = $args['hide_author'] ?? false && ! get_option( 'fictioneer_show_authors' );
$show_taxonomies = ! get_option( 'fictioneer_hide_taxonomies_on_chapter_cards' ) && ( $tags || $fandoms || $characters || $genres );
$show_type = $args['show_type'] ?? false;

?>

<li
  id="chapter-card-<?php the_ID(); ?>"
  class="card <?php echo $story_unpublished ? '_story-unpublished' : ''; ?>"
  data-story-id="<?php echo $story_id; ?>"
  data-check-id="<?php the_ID(); ?>"
>
  <div class="card__body polygon">

    <div class="card__header _large">

      <?php if ( $show_type ) : ?>
        <div class="card__label"><?php _ex( 'Chapter', 'Chapter card label.', 'fictioneer' ); ?></div>
      <?php endif; ?>

      <h3 class="card__title">
        <a href="<?php the_permalink(); ?>" class="truncate _1-1"><?php
          // Make sure there are no whitespaces in-between!
          if ( fictioneer_get_field( 'fictioneer_chapter_list_title' ) ) {
            echo '<span class="show-below-480">' . wp_strip_all_tags( fictioneer_get_field( 'fictioneer_chapter_list_title' ) ) . '</span>';
            echo '<span class="hide-below-480">' . $title . '</span>';
          } else {
            echo $title;
          }
        ?></a>
      </h3>

      <?php
        if ( ! empty( $story_id ) && ! empty( $story_data ) ) {
          echo fictioneer_get_card_controls( $story_id, get_the_ID() );
        }
      ?>

    </div>

    <div class="card__main _grid _large">

      <?php
        // Thumbnail
        if ( has_post_thumbnail() ) {

          printf(
            '<a href="%1$s" title="%2$s" class="card__image _chapter-image cell-img" %3$s>%4$s</a>',
            get_the_post_thumbnail_url( null, 'full' ),
            sprintf( __( '%s Thumbnail', 'fictioneer' ), $title ),
            fictioneer_get_lightbox_attribute(),
            get_the_post_thumbnail( null, 'cover' )
          );

        } elseif ( ! empty( $story_thumbnail_url_full ) ) {

          printf(
            '<a href="%1$s" title="%2$s" class="card__image _story-image cell-img" %3$s>%4$s</a>',
            $story_thumbnail_url_full,
            sprintf( __( '%s Thumbnail', 'fictioneer' ), $title ),
            fictioneer_get_lightbox_attribute(),
            get_the_post_thumbnail( $story_id, 'cover' )
          );

        } elseif ( ! empty( $text_icon ) ) {

          printf(
            '<a href="%1$s" title="%2$s" class="card__text-icon cell-img"><span class="text-icon">%3$s</span></a>',
            get_permalink(),
            esc_attr( $title ),
            fictioneer_get_field( 'fictioneer_chapter_text_icon' )
          );

        }

        // Content
        printf(
          '<div class="card__content cell-desc truncate _4-4">%1$s<span>%2$s</span>%3$s</div>',
          $hide_author ? '' : sprintf(
            '<span class="card__by-author show-below-desktop">%s</span> ',
            sprintf(
              _x( 'by %s —', 'Large card: by {Author} —.', 'fictioneer' ),
              fictioneer_get_author_node()
            )
          ),
          empty( $excerpt ) ? __( 'No description provided yet.', 'fictioneer' ) : $excerpt,
          $story_unpublished ? '<div class="card__unavailable">' . __( 'Unavailable', 'fictioneer' ) . '</div>' : ''
        );
      ?>

      <?php if ( ! empty( $story_id ) && ! empty( $story_data ) ) : ?>
        <ul class="card__link-list cell-list">
          <li>
            <div class="card__left text-overflow-ellipsis">
              <i class="fa-solid fa-caret-right"></i>
              <a href="<?php the_permalink( $story_id ); ?>"><?php echo $story_data['title']; ?></a>
            </div>
            <div class="card__right">
              <?php
                printf(
                  '%1$s<span class="hide-below-480"> %2$s</span><span class="separator-dot">&#8196;&bull;&#8196;</span>%3$s',
                  $story_data['word_count_short'],
                  __( 'Words', 'fictioneer' ),
                  $story_data['status']
                );
              ?>
            </div>
          </li>
        </ul>
      <?php endif; ?>

      <?php if ( $show_taxonomies ) : ?>
        <div class="card__tag-list cell-tax">
          <?php
            $taxonomies = array_merge(
              $fandoms ? fictioneer_generate_card_tags( $fandoms, '_fandom' ) : [],
              $genres ? fictioneer_generate_card_tags( $genres, '_genre' ) : [],
              $tags ? fictioneer_generate_card_tags( $tags ) : [],
              $characters ? fictioneer_generate_card_tags( $characters, '_character' ) : []
            );

            // Implode with three-per-em spaces around a bullet
            echo implode( '&#8196;&bull;&#8196;', $taxonomies );
          ?>
        </div>
      <?php endif; ?>

    </div>

    <div class="card__footer">

      <div class="card__left text-overflow-ellipsis">

        <i class="fa-solid fa-font" title="<?php esc_attr_e( 'Words', 'fictioneer' ) ?>"></i>
        <?php echo fictioneer_shorten_number( get_post_meta( get_the_ID(), '_word_count', true ) ); ?>

        <?php if ( ( $args['orderby'] ?? 0 ) === 'date' ) : ?>
          <i class="fa-solid fa-clock" title="<?php esc_attr_e( 'Published', 'fictioneer' ) ?>"></i>
          <?php echo get_the_date( FICTIONEER_CARD_CHAPTER_FOOTER_DATE ); ?>
        <?php else : ?>
          <i class="fa-regular fa-clock" title="<?php esc_attr_e( 'Last Updated', 'fictioneer' ) ?>"></i>
          <?php the_modified_date( FICTIONEER_CARD_CHAPTER_FOOTER_DATE ); ?>
        <?php endif; ?>

        <?php if ( get_option( 'fictioneer_show_authors' ) && ! $hide_author ) : ?>
          <i class="fa-solid fa-circle-user hide-below-desktop"></i>
          <?php fictioneer_the_author_node( get_the_author_meta( 'ID' ), 'hide-below-desktop' ); ?>
        <?php endif; ?>

        <i class="fa-solid fa-message" title="<?php esc_attr_e( 'Comments', 'fictioneer' ) ?>"></i>
        <?php echo get_comments_number( $post ); ?>

      </div>

      <?php if ( ! empty( $chapter_rating ) ) : ?>
        <div class="card__right rating-letter-label _large tooltipped" data-tooltip="<?php echo fcntr( $chapter_rating, true ); ?>">
          <span class="hide-below-480"><?php echo fcntr( $chapter_rating ); ?></span>
          <span class="show-below-480"><?php echo fcntr( $chapter_rating[0] ); ?></span>
        </div>
      <?php endif; ?>

    </div>

  </div>
</li>
