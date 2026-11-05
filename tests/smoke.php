<?php
/** Tiny dependency-free shortcode tests using stand-ins for WordPress functions. */
error_reporting( E_ALL );
define( 'ABSPATH', __DIR__ );

function add_action() {}
function add_shortcode() {}
function wp_timezone_string() { return 'UTC'; }
function shortcode_atts( $defaults, $provided ) { return array_merge( $defaults, $provided ); }
function __( $s ) { return $s; }
function esc_html__( $s ) { return $s; }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function sanitize_hex_color( $s ) { return preg_match( '/^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i', $s ) ? $s : null; }

require dirname( __DIR__ ) . '/glitchepoch.php';

function check( $result, $message ) {
    if ( ! $result ) {
        fwrite( STDERR, "FAIL: {$message}\n" );
        exit( 1 );
    }
    echo "PASS: {$message}\n";
}

check( glitchepoch_parse_start( '2026-11-05 02:30:00', 'Europe/Rome' )->getTimestamp() === 1793842200, 'Europe/Rome timezone converts to UTC' );
check( glitchepoch_parse_start( '2026-03-29 02:30:00', 'Europe/Rome' ) === null, 'nonexistent spring-forward local time rejected' );
check( glitchepoch_parse_start( '2026-02-29 12:00:00', 'UTC' ) === null, 'invalid calendar date rejected' );
check( glitchepoch_parse_start( '2024-02-29 12:00', 'UTC' ) instanceof DateTimeImmutable, 'leap day accepted' );
check( glitchepoch_parse_start( 'tomorrow', 'UTC' ) === null, 'free-form dates rejected' );

$html = glitchepoch_shortcode( array( 'start' => '2020-01-01 00:00:00', 'timezone' => 'UTC', 'accent' => '#2b5ee5' ) );
check( strpos( $html, 'data-start="2020-01-01T00:00:00Z"' ) !== false, 'UTC origin emitted' );
check( strpos( $html, 'Days' ) !== false && strpos( $html, 'Seconds' ) !== false, 'English labels emitted' );
check( strpos( $html, '--ge-accent:#2b5ee5' ) !== false, 'custom accent emitted' );

$unsafe = glitchepoch_shortcode( array( 'start' => '2020-01-01 00:00:00', 'timezone' => 'UTC', 'accent' => 'red; background:url(https://bad.example)' ) );
check( strpos( $unsafe, 'bad.example' ) === false, 'malicious accent rejected' );
$invalid = glitchepoch_shortcode( array( 'start' => 'not-a-date' ) );
check( strpos( $invalid, 'valid start date' ) !== false, 'invalid shortcode emits helpful error' );
echo "All tests passed.\n";
