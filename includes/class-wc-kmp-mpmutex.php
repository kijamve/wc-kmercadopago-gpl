<?php
/**
 * MercadoPago Tools GPL - Lock File Class
 *
 * @category   Components
 * @package    MercadoPago Tools GPL
 * @author     Kijam.com <info@kijam.com>
 * @license    GNU/GPLv2
 * @link       https://kijam.com
 * @since      1.0.0
 */

if ( ! class_exists( 'WC_KMP_MpMutex' ) ) :
	/**
	 * Class to lock files
	 */
	class WC_KMP_MpMutex {
		/**
		 * Resource of File to lock
		 *
		 * @var Resource
		 */
		private $file = null;

		/**
		 * Bool check is current instance is own
		 *
		 * @var bool
		 */
		private $own = false;

		/**
		 * Constructor
		 *
		 * @param string $key Filename to lock.
		 *
		 * @return void
		 */
		public function __construct( $key ) {
			$this->file = fopen( "$key.lockfile", 'w+' );
		}

		/**
		 * Destructor
		 *
		 * @return void
		 */
		public function __destruct() {
			if ( $this->own ) {
				$this->unlock();
			}
		}

		/**
		 * Lock file
		 *
		 * @return bool
		 */
		public function lock() {
			if ( ! flock( $this->file, LOCK_EX ) ) {
				return false;
			}
			ftruncate( $this->file, 0 );
			fwrite( $this->file, "Locked\n" );
			fflush( $this->file );
			$this->own = true;
			return $this->own;
		}

		/**
		 * Unlock file
		 *
		 * @return bool
		 */
		public function unlock() {
			if ( $this->own ) {
				if ( ! flock( $this->file, LOCK_UN ) ) {
					return false;
				}
				ftruncate( $this->file, 0 );
				fwrite( $this->file, "Unlocked\n" );
				fflush( $this->file );
			}
			$this->own = false;
			return $this->own;
		}
	}
endif;
