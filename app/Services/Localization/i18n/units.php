<?php
/**
 * Units
 *
 * Returns a multidimensional array of measurement units and their labels.
 * Unit labels should be defined in English and translated native through localization files.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

return array(
	'weight'     => array(
		'kg'  => __( 'kg', 'webmakerr-cart' ),
		'g'   => __( 'g', 'webmakerr-cart' ),
		'lbs' => __( 'lbs', 'webmakerr-cart' ),
		'oz'  => __( 'oz', 'webmakerr-cart' ),
	),
	'dimensions' => array(
		'm'  => __( 'm', 'webmakerr-cart' ),
		'cm' => __( 'cm', 'webmakerr-cart' ),
		'mm' => __( 'mm', 'webmakerr-cart' ),
		'in' => __( 'in', 'webmakerr-cart' ),
		'yd' => __( 'yd', 'webmakerr-cart' ),
	),
);