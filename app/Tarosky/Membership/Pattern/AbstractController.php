<?php

namespace Tarosky\Membership\Pattern;


use Hametuha\Pattern\Singleton;

/**
 * Abstract Controller
 *
 * @package membership
 */
abstract class AbstractController extends Singleton {
	
	public $feature_name = '';
	
	/**
	 * Detect if this controller should be enabled.
	 *
	 * @return bool
	 */
	protected function is_enabled() {
		return true;
	}
	
	/**
	 * Done in constructor.
	 */
	protected function init() {
		$enabled = apply_filters( 'membership_feature_enabled', $this->is_enabled(), $this->feature_name );
		if ( ! $this->is_enabled() ) {
			return;
		}
		$this->init_handler();
	}
	
	/**
	 * Do something if enabled.
	 *
	 * @return void
	 */
	abstract protected function init_handler();
}
