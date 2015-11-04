<?php

use Brain\Monkey;
use Brain\Monkey\Functions;

class DynamicContentTagTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var MC4WP_Dynamic_Content_Tags
	 */
	protected $instance;

	/**
	 * Runs before all tests
	 */
	public function setUp() {
		parent::setUp();
		Monkey::setUp();
		$this->instance = new MC4WP_Dynamic_Content_Tags( 'context' );
	}

	/**
	 * Runs after all tests
	 */
	protected function tearDown() {
		Monkey::tearDown();
		parent::tearDown();
	}

	/**
	 * @covers MC4WP_Dynamic_Content_Tags::__construct
	 */
	public function test_constructor() {
		$context = 'something';
		$this->assertAttributeEquals( $context, 'context', new MC4WP_Dynamic_Content_Tags( $context ) );
	}

	/**
	 * @covers MC4WP_Dynamic_Content_Tags::escape_value_url
	 */
	public function test_escape_value_url() {

		$reflectionOfUser = new ReflectionClass('MC4WP_Dynamic_Content_Tags');
		$method = $reflectionOfUser->getMethod('escape_value_url');
		$method->setAccessible(true);

		$value = 'john@email.com';
		$this->assertEquals( $method->invoke( $this->instance, $value ), urlencode( $value ) );
	}

	/**
	 * @covers MC4WP_Dynamic_Content_Tags::escape_value_html
	 */
	public function test_escape_value_html() {

		$reflection = new ReflectionClass('MC4WP_Dynamic_Content_Tags');
		$method = $reflection->getMethod('escape_value_html');
		$method->setAccessible(true);

		$value = '<script>alert("hi");</script>';

		// just test if "esc_html" is called
		Functions::when('esc_html')->justReturn('called');
		$this->assertEquals( $method->invoke( $this->instance, $value ), 'called' );
	}

	/**
	 * @covers MC4WP_Dynamic_Content_Tags::escape_value_url
	 */
	public function test_escape_attributes() {
		$reflection = new ReflectionClass('MC4WP_Dynamic_Content_Tags');
		$method = $reflection->getMethod('escape_value_attributes');
		$method->setAccessible(true);

		$value = 'an-invalid="attribute string"';

		// just test if "esc_html" is called
		Functions::when('esc_attr')->justReturn('called');
		$this->assertEquals( $method->invoke( $this->instance, $value ), 'called' );
	}

	/**
	 * @covers MC4WP_Dynamic_Content_Tags::replace
	 */
	public function test_replace() {

		$tags = array(
			'sample_tag' => array (
				'replacement' => 'sample replacement'
			)
		);
		$instance = new MC4WP_Dynamic_Content_Tags( 'context', $tags );

		// default
		$string = "String with {sample_tag} in it.";
		$this->assertEquals( "String with sample replacement in it.", $instance->replace( $string ) );

		// with double-quoted attribute
		$string = "String with {sample_tag attribute=\"value with space\"} in it.";
		$this->assertEquals( "String with sample replacement in it.", $instance->replace( $string ) );

		// with unquoted attribute
		$string = "String with {sample_tag attribute=value with spaces} in it.";
		$this->assertEquals( "String with sample replacement in it.", $instance->replace( $string ) );

		// with single-quoted attribute
		$string = "String with {sample_tag attribute='value with spaces'} in it.";
		$this->assertEquals( "String with sample replacement in it.", $instance->replace( $string ) );

		// space after opening tag, do notihing
		$string = "String with { sample_tag attribute=\"value\"} in it.";
		$this->assertEquals( $string, $instance->replace( $string ) );
	}

	/**
	 * @covers MC4WP_Dynamic_Content_Tags::replace
	 */
	public function test_replace_with_callback() {
		$tags = array(
			'sample_tag' => array (
				'callback' => function( $attributes ) {

					if( ! empty( $attributes['return'] ) ) {
						return $attributes['return'];
					}

					return 'sample replacement';
				}
			)
		);
		$instance = new MC4WP_Dynamic_Content_Tags( 'context', $tags );

		// normal
		$string = "String with {sample_tag} in it.";
		$this->assertEquals( "String with sample replacement in it.", $instance->replace( $string ) );

		// attribute
		$string = "String with {sample_tag attribute=value} in it.";
		$this->assertEquals( "String with sample replacement in it.", $instance->replace( $string ) );

		// "default" attribute
		$string = "String with {sample_tag return=value} in it.";
		$this->assertEquals( "String with value in it.", $instance->replace( $string ) );

	}


}