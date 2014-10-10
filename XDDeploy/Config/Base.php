<?php
	namespace XDDeploy\Config;

	/**
	 * 	Base config object
	 *
	 * 	@author XIDA
	 */
	class Base {

		/**
		 *	Stores config values from file
		 *
		 *	@var array
		 */
		protected $data;

		/**
		 *	stores if this is a preset config object
		 *
		 *	@var boolean
		 */
		private $isPreset;

		/**
		 *	Setup config object from an input array
		 *
		 *	@param	array		$data		Configuration array from file
		 *	@param	boolean		$preset		Is this a preset configuration object
		 *
		 *	@return \XDDeploy\Config\Base
		 */
		protected function __construct($data, $preset = false) {
			if(!is_array($data)) {
				die('Config data invalid!');
			}
			$this->data		= $data;
			$this->isPreset = $preset;

			// try to merge the current config/preset with a preset
			$this->mergeWithPreset();

			// only validate a normal config after the merge
			if(!$this->isPreset() && $this->validateConfig() === false) {
				die();
			}

			return $this;
		}

		/**
		 *	Merge the current config object with a new config preset object
		 */
		private function mergeWithPreset() {
			if($this->getPresetName()) {
				// get config object for the preset based on current config object
				$preset			= Manager::getPresetByName($this->getPresetName(), get_class($this));
				// merge preset object with current config object
				$this->data		= array_replace_recursive($preset->getData(), $this->data);
			}
		}

		/**
		 *	Validate all required paramaters
		 */
		protected function validateConfig() {
			return true;
		}

		/**
		 *	Get a config value.
		 *	Added this function to prevent undefined index errors
		 *
		 *	@param	string		$name		Name of the config value
		 *
		 *	@return value of an item or null
		 */
		protected function getValue($name) {
			if(isset($this->data[$name])) {
				return $this->data[$name];
			}
			return null;
		}

		/**
		 *
		 *	@return boolean
		 */
		public function isPreset() {
			return (boolean) $this->isPreset;
		}

		/**
		 *	Get preset name
		 *
		 *	@return string
		 */
		public function getPresetName() {
			return $this->getValue(Manager::PRESET_NAME);
		}

		/**
		 *	Get the array from file
		 *
		 *	@return array
		 */
		public function getData() {
			return $this->data;
		}
	}
?>