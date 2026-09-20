<?php
/**
 * Template Name: Repair
 * Source: 06 Repair.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$services      = stocksystem_repair_services();
$steps         = stocksystem_repair_steps();
$submit_status = isset( $_GET['repair_request'] ) ? sanitize_key( wp_unslash( $_GET['repair_request'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="container page-crumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'stocksystem' ); ?></a>
	<span>/</span>
	<span><?php esc_html_e( 'تعمیرات تخصصی', 'stocksystem' ); ?></span>
</div>

<section class="repair-hero">
	<div class="container repair-hero__grid">
		<div class="repair-hero__copy">
			<h1><?php esc_html_e( 'تعمیر در کارگاه، با تشخیص پیش از هزینه', 'stocksystem' ); ?></h1>
			<p><?php esc_html_e( 'عیب‌یابی اولیه رایگان است. پس از تشخیص، هزینه و زمان دقیق اعلام می‌شود و کار فقط با تأیید شما آغاز می‌شود.', 'stocksystem' ); ?></p>
			<span class="repair-hero__badges">
				<span class="repair-badge"><?php esc_html_e( 'عیب‌یابی رایگان', 'stocksystem' ); ?></span>
				<span class="repair-badge"><?php esc_html_e( 'گارانتی کتبی تعمیر', 'stocksystem' ); ?></span>
				<span class="repair-badge"><?php esc_html_e( 'قطعات اصلی', 'stocksystem' ); ?></span>
			</span>
		</div>

		<div class="repair-steps">
			<span class="repair-steps__title"><?php esc_html_e( 'مراحل کار', 'stocksystem' ); ?></span>
			<?php foreach ( $steps as $index => $step ) : ?>
				<span class="repair-steps__item">
					<span class="repair-steps__marker">
						<span class="repair-steps__dot"><?php echo esc_html( stocksystem_to_persian_digits( $index + 1 ) ); ?></span>
						<?php if ( $index < count( $steps ) - 1 ) : ?><span class="repair-steps__line"></span><?php endif; ?>
					</span>
					<span class="repair-steps__label"><?php echo esc_html( $step ); ?></span>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="repair-services">
	<div class="container">
		<div class="repair-table">
			<div class="repair-table__head">
				<span><?php esc_html_e( 'خدمت', 'stocksystem' ); ?></span>
				<span><?php esc_html_e( 'زمان انجام', 'stocksystem' ); ?></span>
				<span><?php esc_html_e( 'گارانتی', 'stocksystem' ); ?></span>
				<span><?php esc_html_e( 'هزینه از', 'stocksystem' ); ?></span>
			</div>
			<?php foreach ( $services as $service ) : ?>
				<div class="repair-table__row">
					<span class="repair-table__service">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo wp_kses( $service['icon'], array( 'rect' => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ), 'path' => array( 'd' => true ), 'circle' => array( 'cx' => true, 'cy' => true, 'r' => true ), 'ellipse' => array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true ) ) ); ?></svg>
						<?php echo esc_html( $service['name'] ); ?>
					</span>
					<span><?php echo esc_html( $service['turnaround'] ); ?></span>
					<span><?php echo $service['warranty'] ? esc_html( $service['warranty'] ) : '—'; ?></span>
					<span class="repair-table__price<?php echo $service['price_from'] ? '' : ' is-quote'; ?>"><?php echo $service['price_from'] ? esc_html( stocksystem_format_number( $service['price_from'] ) ) : esc_html__( 'پس از بررسی', 'stocksystem' ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="repair-form-section" id="repair-request">
	<div class="container">
	<div class="repair-form-section__grid">
		<div class="repair-form-section__copy">
			<h2><?php esc_html_e( 'درخواست تعمیر ثبت کنید', 'stocksystem' ); ?></h2>
			<p><?php esc_html_e( 'ظرف چند ساعت کاری تماس می‌گیریم.', 'stocksystem' ); ?></p>
			<span class="repair-form-section__values">
				<span class="repair-form-section__values-bar" aria-hidden="true"></span>
				<span class="repair-form-section__values-list">
					<span><?php esc_html_e( 'عیب‌یابی رایگان', 'stocksystem' ); ?></span>
					<span><?php esc_html_e( 'هزینه پیش از کار', 'stocksystem' ); ?></span>
					<span><?php esc_html_e( 'گارانتی کتبی', 'stocksystem' ); ?></span>
				</span>
			</span>
		</div>

		<div class="repair-form-wrap">
			<?php if ( 'success' === $submit_status ) : ?>
				<p class="repair-form__notice repair-form__notice--success"><?php esc_html_e( 'درخواست شما ثبت شد. ظرف چند ساعت کاری تماس می‌گیریم.', 'stocksystem' ); ?></p>
			<?php elseif ( 'invalid' === $submit_status ) : ?>
				<p class="repair-form__notice repair-form__notice--error"><?php esc_html_e( 'نام و شمارهٔ موبایل معتبر (۰۹xxxxxxxxx) الزامی است.', 'stocksystem' ); ?></p>
			<?php endif; ?>

			<form class="repair-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="stocksystem_repair_request">
				<?php wp_nonce_field( 'stocksystem_repair_request', 'stocksystem_repair_nonce' ); ?>

				<label>
					<span><?php esc_html_e( 'نام و نام خانوادگی', 'stocksystem' ); ?></span>
					<input type="text" name="name" required>
				</label>
				<label>
					<span><?php esc_html_e( 'شمارهٔ موبایل', 'stocksystem' ); ?></span>
					<input type="tel" name="phone" class="ltr" pattern="09[0-9]{9}" placeholder="09xxxxxxxxx" required>
				</label>
				<label>
					<span><?php esc_html_e( 'مدل دستگاه', 'stocksystem' ); ?></span>
					<input type="text" name="device" placeholder="<?php esc_attr_e( 'مثلاً HP 840 G6', 'stocksystem' ); ?>">
				</label>
				<label>
					<span><?php esc_html_e( 'نوع خدمت', 'stocksystem' ); ?></span>
					<select name="service">
						<?php foreach ( $services as $service ) : ?>
							<option value="<?php echo esc_attr( $service['name'] ); ?>"><?php echo esc_html( $service['name'] ); ?></option>
						<?php endforeach; ?>
						<option value="<?php esc_attr_e( 'نمی‌دانم', 'stocksystem' ); ?>"><?php esc_html_e( 'نمی‌دانم', 'stocksystem' ); ?></option>
					</select>
				</label>
				<label class="repair-form__full">
					<span><?php esc_html_e( 'شرح مشکل', 'stocksystem' ); ?></span>
					<textarea name="description" rows="3" placeholder="<?php esc_attr_e( 'چه اتفاقی افتاد و از چه زمانی؟', 'stocksystem' ); ?>"></textarea>
				</label>
				<span class="repair-form__actions repair-form__full">
					<button type="submit" class="btn btn--primary"><?php esc_html_e( 'ثبت درخواست', 'stocksystem' ); ?></button>
					<span class="repair-form__phone-hint">
						<?php esc_html_e( 'یا تماس مستقیم:', 'stocksystem' ); ?>
						<a class="ltr" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>"><?php echo esc_html( stocksystem_business( 'phone' ) ); ?></a>
					</span>
				</span>
			</form>
		</div>
	</div>
	</div>
</section>

<?php get_footer(); ?>
