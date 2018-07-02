<?php
/**
 * Template for displaying next/prev item in course.
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 3.0.0
 */

defined( 'ABSPATH' ) or die();

if ( ! isset( $prev_item ) && ! isset( $next_item ) ) {
	return;
}

$course = LP_Global::course();
$user   = LP_Global::user();
$item   = LP_Global::course_item();

if ( $item->is_preview() ) {
	return;
}

$completed = $user->has_completed_item( $item->get_id(), $course->get_id() );
?>

<div class="course-item-nav">
	<?php if ( $prev_item ) { ?>
        <div class="prev">
            
            <a href="<?php echo $prev_item->get_permalink(); ?>">
            <span><?php echo esc_html_x( 'Prev', 'course-item-navigation', 'learnpress' ); ?></span>
				<?php echo $prev_item->get_title(); ?>
            </a>
        </div>
	<?php } ?>

	<?php if ( $next_item && $completed ) { ?>
        <div class="next">
            <a href="<?php echo $next_item->get_permalink(); ?>">
            <span><?php echo esc_html_x( 'Next', 'course-item-navigation', 'learnpress' ); ?></span>
				<?php echo $next_item->get_title(); ?>
            </a>
        </div>
	<?php } ?>
</div>


