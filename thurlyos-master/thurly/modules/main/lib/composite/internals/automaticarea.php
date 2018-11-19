<?
namespace Thurly\Main\Composite\Internals;

use Thurly\Main\Composite;
use Thurly\Main\Composite\Helper;
use Thurly\Main\Composite\StaticArea;

class AutomaticArea
{
	/** @var \CThurlyComponent */
	private $component = null;
	private $started = false;

	/** @var  AutomaticArea */
	private static $currentArea = null;

	public function __construct($component)
	{
		$this->component = $component;
	}

	public function start()
	{
		if (!Composite\Engine::getUseHTMLCache() || !$this->isFirstLevelComponent())
		{
			return false;
		}

		if (StaticArea::getCurrentDynamicId() !== false)
		{
			return false;
		}

		if ($this->component->getDefaultFrameMode() === false || $this->getFrameType() === "STATIC")
		{
			return false;
		}

		if (in_array($this->component->getName(), array("thurly:breadcrumb", "thurly:main.include")))
		{
			return false;
		}

		if ($this->component->getName() === "thurly:menu" &&
			isset($this->component->arParams["DELAY"]) &&
			$this->component->arParams["DELAY"] === "Y"
		)
		{
			return false;
		}

		$this->started = true;
		static::$currentArea = $this;

		ob_start();

		return true;
	}

	public function end()
	{
		if (!$this->started)
		{
			return false;
		}

		$isComponentAdapted =
			$this->component->getRealFrameMode() !== null ||
			($this->component->__template !== null && $this->component->__template->getRealFrameMode() !== null);

		if ($isComponentAdapted)
		{
			ob_end_flush();
		}
		else
		{
			$stub = ob_get_contents();
			ob_end_clean();

			$frame = new StaticArea($this->component->randString());

			if ($this->getFrameType() === "DYNAMIC_WITH_STUB")
			{
				$frame->setStub($stub);
			}
			elseif ($this->getFrameType() === "DYNAMIC_WITH_STUB_LOADING")
			{
				$frame->setStub('<div class="bx-composite-loading"></div>');
			}

			$frame->startDynamicArea();
			echo $stub;
			$frame->finishDynamicArea();
		}

		$this->started = false;
		static::$currentArea = null;

		return true;
	}

	public function getFrameType()
	{
		$componentParams = $this->component->arParams;
		if (isset($componentParams["COMPOSITE_FRAME_TYPE"]) && is_string($componentParams["COMPOSITE_FRAME_TYPE"]))
		{
			$type = strtoupper($componentParams["COMPOSITE_FRAME_TYPE"]);
			if (in_array($type, static::getFrameTypes()))
			{
				return $type;
			}
		}

		$compositeOptions = Helper::getOptions();
		if (isset($compositeOptions["FRAME_TYPE"]) && is_string($compositeOptions["FRAME_TYPE"]))
		{
			$type = strtoupper($compositeOptions["FRAME_TYPE"]);
			if (in_array($type, static::getFrameTypes()))
			{
				return $type;
			}
		}

		return "STATIC";
	}

	public static function getFrameTypes()
	{
		return array(
			"STATIC",
			"DYNAMIC_WITH_STUB",
			"DYNAMIC_WITH_STUB_LOADING",
			"DYNAMIC_WITHOUT_STUB",
		);
	}

	/**
	 * @return AutomaticArea
	 */
	public static function getCurrentArea()
	{
		return static::$currentArea;
	}

	private function isFirstLevelComponent()
	{
		return count($GLOBALS["APPLICATION"]->getComponentStack()) <= 1;
	}
}