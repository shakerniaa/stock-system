<?php
/**
 * Footer trust badges — اینماد، ساماندهی، ترب، ایمالز and whatever else
 * gets added later, as uploaded images rather than pasted HTML.
 *
 * The existing «نمادها و مجوزها (کد HTML)» box in «منو و فوتر» stays,
 * because some authorities (اینماد in particular) hand out a <script>
 * widget that has to be pasted verbatim. This page is the easier path
 * for the common case: upload a logo, give it a link, done.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Badge rows that have an image, in their configured order. */
function stocksystem_footer_badges() {
	$cfg   = stocksystem_opt( 'footerx' );
	$items = isset( $cfg['badges'] ) && is_array( $cfg['badges'] ) ? $cfg['badges'] : array();

	$badges = array();

	foreach ( $items as $item ) {
		$id = (int) $item['image'];
		if ( ! $id || ! wp_attachment_is_image( $id ) ) {
			continue;
		}
		$badges[] = array(
			'image' => $id,
			'title' => (string) $item['title'],
			'url'   => (string) $item['url'],
		);
	}

	return $badges;
}

function stocksystem_footer_admin_page( $pages ) {
	$pages['footerx'] = array(
		'group'    => 'محتوای صفحه‌ها',
		'title'    => __( 'نمادها و لوگوهای فوتر', 'stocksystem' ),
		'menu'     => __( 'نمادهای فوتر', 'stocksystem' ),
		'intro'    => __( 'لوگوی اینماد، ساماندهی، ترب، ایمالز یا هر نماد دیگری را اینجا آپلود کن. هرکدام می‌تواند لینک داشته باشد. اگر نمادی به‌جای تصویر یک کد اسکریپت می‌دهد (مثل کد رسمی اینماد)، آن را در «منو و فوتر ← نمادها و مجوزها (کد HTML)» بگذار.', 'stocksystem' ),
		'view'     => '/',
		'sections' => array(
			array(
				'title'  => __( 'نمادها', 'stocksystem' ),
				'fields' => array(
					'badges' => array(
						'type'      => 'repeater',
						'label'     => __( 'نمادها و لوگوها', 'stocksystem' ),
						'max'       => 12,
						'add_label' => __( '+ نماد تازه', 'stocksystem' ),
						'fields'    => array(
							'image' => array( 'type' => 'image', 'label' => __( 'تصویر نماد', 'stocksystem' ) ),
							'title' => array( 'type' => 'text', 'label' => __( 'نام (برای توضیح تصویر)', 'stocksystem' ), 'allow_empty' => true ),
							'url'   => array( 'type' => 'url', 'label' => __( 'لینک (اختیاری)', 'stocksystem' ), 'allow_empty' => true ),
						),
						'default'   => array(),
					),
				),
			),
		),
	);

	return $pages;
}
add_filter( 'stocksystem_admin_pages', 'stocksystem_footer_admin_page' );
