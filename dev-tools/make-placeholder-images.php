<?php
/**
 * Generates simple flat product illustrations (transparent PNG) for the
 * seed catalog's products that have no real photo in the design kit.
 * These are dev placeholders only — the client supplies real photos.
 *
 *   php dev-tools/make-placeholder-images.php
 */

$out = __DIR__ . '/placeholder-images/';
if ( ! is_dir( $out ) ) {
	mkdir( $out, 0755, true );
}

const SS = 3;   // supersample factor for smooth edges
const W  = 600; // final size
const H  = 450;

function ss_canvas() {
	$im = imagecreatetruecolor( W * SS, H * SS );
	imagealphablending( $im, false );
	imagesavealpha( $im, true );
	imagefill( $im, 0, 0, imagecolorallocatealpha( $im, 0, 0, 0, 127 ) );
	imagealphablending( $im, true );
	return $im;
}

function ss_col( $im, $hex, $alpha = 0 ) {
	$hex = ltrim( $hex, '#' );
	return imagecolorallocatealpha( $im, hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ), $alpha );
}

function ss_rr( $im, $x1, $y1, $x2, $y2, $r, $color ) {
	$x1 *= SS; $y1 *= SS; $x2 *= SS; $y2 *= SS; $r *= SS;
	imagefilledrectangle( $im, $x1 + $r, $y1, $x2 - $r, $y2, $color );
	imagefilledrectangle( $im, $x1, $y1 + $r, $x2, $y2 - $r, $color );
	imagefilledellipse( $im, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color );
	imagefilledellipse( $im, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color );
	imagefilledellipse( $im, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color );
	imagefilledellipse( $im, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color );
}

function ss_poly( $im, array $pts, $color ) {
	$flat = array();
	foreach ( $pts as $p ) {
		$flat[] = $p * SS;
	}
	imagefilledpolygon( $im, $flat, $color );
}

function ss_ellipse( $im, $cx, $cy, $w, $h, $color ) {
	imagefilledellipse( $im, $cx * SS, $cy * SS, $w * SS, $h * SS, $color );
}

function ss_save( $im, $name ) {
	global $out;
	$final = imagecreatetruecolor( W, H );
	imagealphablending( $final, false );
	imagesavealpha( $final, true );
	imagefill( $final, 0, 0, imagecolorallocatealpha( $final, 0, 0, 0, 127 ) );
	imagecopyresampled( $final, $im, 0, 0, 0, 0, W, H, W * SS, H * SS );
	imagepng( $final, $out . $name, 9 );
	echo "wrote $name\n";
}

$ink    = '#22302F';
$ink2   = '#2E403F';
$screen = '#0B3A38';
$teal   = '#0EBAAF';
$metal  = '#B9CBC9';
$shadow = '#000000';

// ---- Monitor ----
$im = ss_canvas();
ss_ellipse( $im, 300, 410, 260, 22, ss_col( $im, $shadow, 108 ) );
ss_rr( $im, 250, 380, 350, 400, 8, ss_col( $im, $metal ) );
ss_poly( $im, array( 280, 320, 320, 320, 330, 384, 270, 384 ), ss_col( $im, '#9FB4B2' ) );
ss_rr( $im, 70, 50, 530, 330, 18, ss_col( $im, $ink ) );
ss_rr( $im, 84, 64, 516, 316, 8, ss_col( $im, $screen ) );
ss_poly( $im, array( 84, 316, 84, 250, 330, 64, 430, 64 ), ss_col( $im, $teal, 108 ) );
ss_poly( $im, array( 200, 316, 470, 64, 516, 64, 516, 120, 330, 316 ), ss_col( $im, '#FFFFFF', 118 ) );
ss_ellipse( $im, 300, 323, 6, 6, ss_col( $im, $teal ) );
ss_save( $im, 'monitor.png' );

// ---- Mini PC ----
$im = ss_canvas();
ss_ellipse( $im, 300, 352, 380, 26, ss_col( $im, $shadow, 108 ) );
ss_rr( $im, 110, 170, 490, 340, 20, ss_col( $im, $ink ) );
ss_rr( $im, 110, 170, 490, 200, 16, ss_col( $im, $ink2 ) );
ss_ellipse( $im, 150, 262, 16, 16, ss_col( $im, $teal ) );
ss_rr( $im, 190, 240, 300, 250, 4, ss_col( $im, '#0E1B1A' ) );
ss_rr( $im, 190, 262, 300, 272, 4, ss_col( $im, '#0E1B1A' ) );
ss_rr( $im, 190, 284, 300, 294, 4, ss_col( $im, '#0E1B1A' ) );
ss_rr( $im, 380, 236, 450, 262, 5, ss_col( $im, '#0E1B1A' ) );
ss_rr( $im, 380, 276, 450, 302, 5, ss_col( $im, '#0E1B1A' ) );
ss_save( $im, 'mini-pc.png' );

// ---- RAM module ----
$im = ss_canvas();
ss_ellipse( $im, 300, 330, 440, 20, ss_col( $im, $shadow, 108 ) );
ss_rr( $im, 60, 150, 540, 300, 8, ss_col( $im, '#0A6F69' ) );
ss_rr( $im, 60, 282, 540, 304, 4, ss_col( $im, '#E0A302' ) );
for ( $i = 0; $i < 8; $i++ ) {
	ss_rr( $im, 88 + $i * 56, 292, 88 + $i * 56 + 32, 306, 2, ss_col( $im, '#FFFFFF', 60 ) );
}
foreach ( array( 100, 210, 320, 430 ) as $x ) {
	ss_rr( $im, $x, 178, $x + 78, 246, 6, ss_col( $im, '#141F1E' ) );
	ss_ellipse( $im, $x + 14, 192, 6, 6, ss_col( $im, $metal ) );
}
ss_rr( $im, 250, 116, 350, 152, 6, ss_col( $im, '#0A6F69' ) );
ss_ellipse( $im, 300, 300, 22, 22, ss_col( $im, '#FFFFFF', 100 ) );
ss_save( $im, 'ram-module.png' );

// ---- Mouse ----
$im = ss_canvas();
ss_ellipse( $im, 300, 388, 200, 20, ss_col( $im, $shadow, 108 ) );
ss_rr( $im, 210, 70, 390, 380, 88, ss_col( $im, $ink ) );
imagefilledrectangle( $im, 298 * SS, 70 * SS, 302 * SS, 210 * SS, ss_col( $im, '#0E1B1A' ) );
imageline( $im, 210 * SS, 214 * SS, 390 * SS, 214 * SS, ss_col( $im, '#0E1B1A' ) );
ss_rr( $im, 288, 120, 312, 176, 12, ss_col( $im, $teal ) );
ss_save( $im, 'mouse.png' );

// ---- Laptop (generic) ----
$im = ss_canvas();
ss_ellipse( $im, 300, 372, 460, 22, ss_col( $im, $shadow, 108 ) );
ss_rr( $im, 130, 60, 470, 290, 14, ss_col( $im, $ink ) );
ss_rr( $im, 142, 72, 458, 278, 6, ss_col( $im, $screen ) );
ss_poly( $im, array( 142, 278, 142, 210, 340, 72, 420, 72 ), ss_col( $im, $teal, 108 ) );
ss_poly( $im, array( 160, 300, 440, 300, 520, 356, 80, 356 ), ss_col( $im, '#33403F' ) );
ss_rr( $im, 80, 352, 520, 366, 6, ss_col( $im, $metal ) );
ss_rr( $im, 254, 312, 346, 338, 5, ss_col( $im, '#22302F' ) );
foreach ( array( 300, 314, 328 ) as $y ) {
	imageline( $im, ( 190 + ( $y - 300 ) ) * SS, $y * SS, ( 410 - ( $y - 300 ) ) * SS, $y * SS, ss_col( $im, '#1B2726' ) );
}
ss_save( $im, 'laptop.png' );

// ---- Blog covers (16:9) ----
function ss_cover( $name, $accent, $glyph ) {
	global $out;
	$w = 1200;
	$h = 675;
	$im = imagecreatetruecolor( $w, $h );
	imagealphablending( $im, true );
	// vertical gradient ink-900 -> ink-600
	for ( $y = 0; $y < $h; $y++ ) {
		$t = $y / $h;
		$c = imagecolorallocate( $im, (int) ( 4 + 7 * $t ), (int) ( 33 + 25 * $t ), (int) ( 31 + 25 * $t ) );
		imageline( $im, 0, $y, $w, $y, $c );
	}
	$hex = ltrim( $accent, '#' );
	$rgb = array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
	$band1 = imagecolorallocatealpha( $im, $rgb[0], $rgb[1], $rgb[2], 108 );
	$band2 = imagecolorallocatealpha( $im, $rgb[0], $rgb[1], $rgb[2], 118 );
	imagefilledpolygon( $im, array( 640, 0, 860, 0, 520, $h, 300, $h ), $band1 );
	imagefilledpolygon( $im, array( 900, 0, 1010, 0, 670, $h, 560, $h ), $band2 );
	$line = imagecolorallocatealpha( $im, $rgb[0], $rgb[1], $rgb[2], 70 );
	imageellipse( $im, 1080, 60, 420, 420, $line );
	imageellipse( $im, 1080, 60, 380, 380, $line );

	$solid = imagecolorallocate( $im, $rgb[0], $rgb[1], $rgb[2] );
	$soft  = imagecolorallocatealpha( $im, 235, 237, 236, 100 );
	$white = imagecolorallocatealpha( $im, 235, 237, 236, 30 );
	imagesetthickness( $im, 6 );
	$cx = 600;
	$cy = 340;
	if ( 'laptop' === $glyph ) {
		imagerectangle( $im, $cx - 190, $cy - 150, $cx + 190, $cy + 60, $solid );
		imagefilledrectangle( $im, $cx - 170, $cy - 130, $cx + 170, $cy + 40, $soft );
		imageline( $im, $cx - 250, $cy + 100, $cx + 250, $cy + 100, $solid );
		imageline( $im, $cx - 250, $cy + 100, $cx - 190, $cy + 60, $solid );
		imageline( $im, $cx + 250, $cy + 100, $cx + 190, $cy + 60, $solid );
	} elseif ( 'ssd' === $glyph ) {
		imagerectangle( $im, $cx - 230, $cy - 120, $cx + 230, $cy + 120, $solid );
		imagefilledrectangle( $im, $cx - 210, $cy - 100, $cx + 210, $cy + 100, $soft );
		foreach ( array( -130, -20, 90 ) as $dx ) {
			imagefilledrectangle( $im, $cx + $dx, $cy - 60, $cx + $dx + 90, $cy + 20, $white );
		}
		imagefilledrectangle( $im, $cx - 230, $cy + 60, $cx - 200, $cy + 100, $solid );
	} else { // compare / scales
		imageline( $im, $cx, $cy - 150, $cx, $cy + 130, $solid );
		imageline( $im, $cx - 210, $cy - 90, $cx + 210, $cy - 90, $solid );
		imageline( $im, $cx - 110, $cy + 130, $cx + 110, $cy + 130, $solid );
		imagerectangle( $im, $cx - 260, $cy - 60, $cx - 160, $cy + 20, $solid );
		imagerectangle( $im, $cx + 160, $cy - 60, $cx + 260, $cy + 20, $solid );
		imagefilledrectangle( $im, $cx - 250, $cy - 50, $cx - 170, $cy + 10, $soft );
		imagefilledrectangle( $im, $cx + 170, $cy - 50, $cx + 250, $cy + 10, $soft );
	}
	imagepng( $im, $out . $name, 8 );
	echo "wrote $name\n";
}

ss_cover( 'cover-buying-guide.png', '#0EBAAF', 'laptop' );
ss_cover( 'cover-troubleshooting.png', '#F58220', 'ssd' );
ss_cover( 'cover-comparison.png', '#5FD9D0', 'compare' );
ss_cover( 'cover-battery.png', '#13A05C', 'laptop' );
