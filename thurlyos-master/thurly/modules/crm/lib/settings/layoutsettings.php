<?php
namespace Thurly\Crm\Settings;
use Thurly\Main;
class LayoutSettings
{
	/** @var LayoutSettings */
	private static $current = null;

	/** @var BooleanSetting */
	private $enableSlider = null;
	/** @var BooleanSetting */
	private $enableSimpleTimeFormat = null;

	function __construct()
	{
		$this->enableSlider = new BooleanSetting('enable_slider', false);
		$this->enableSimpleTimeFormat = new BooleanSetting('enable_simple_time_format', true);
	}
	/**
	 * Get current instance
	 * @return LayoutSettings
	 */
	public static function getCurrent()
	{
		if(self::$current === null)
		{
			self::$current = new LayoutSettings();
		}
		return self::$current;
	}
	/**
	 * Check if slider enabled for edit and view actions
	 * @return bool
	 */
	public function isSliderEnabled()
	{
		return $this->enableSlider->get();
	}
	/**
	 * Enabled slider for edit and view actions
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableSlider($enabled)
	{
		$this->enableSlider->set($enabled);
	}
	/**
	 * Check if simple time format enabled for display system fields (CREATED, LAST_MODIFIED and etc)
	 * @return bool
	 */
	public function isSimpleTimeFormatEnabled()
	{
		return $this->enableSimpleTimeFormat->get();
	}
	/**
	 * Enabled slider for edit and view actions
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableSimpleTimeFormat($enabled)
	{
		$this->enableSimpleTimeFormat->set($enabled);
	}
}