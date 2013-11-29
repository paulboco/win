<?php namespace Paulboco\Ain;

class Debug {

	public static $bypass = false;

	private static $colors = [
		'#F88',
		'#9F9',
		'#6AF',
		'#FF8',
		'#0FA',
		'#0FF',
		'#FC4',
		'#CF5',
		'#FAF',
		'#DDD',
		'#F00',
	];

	private static $color = -1;


	/**
	 * Output a variable with var_export wrapped in pre tags.
	 *
	 * @param $var    mixed  the variable to export
	 * @param $color  int    the border and text color (0-9)
	 */
	public static function v($var, $color = null, $die = false)
	{
		self::render(var_export($var, 1), $color, $die);
	}

	/**
	 * Output the methods for a class.
	 *
	 * @param $class  string
	 * @param $color  int
	 */
	public static function m($class, $color = null, $die = false)
	{
		$methods = print_r(get_class_methods($class), 1);

		$class_name = get_class($class);

		$methods = str_replace("Array\n", "{$class_name} Methods\n", $methods);

		self::render($methods, $color, $die);
	}

	/**
	 * Output the attributes for a class.
	 *
	 * @param $class  string
	 * @param $color  int
	 */
	public static function a($class, $color = null, $die = false)
	{
		$methods = print_r(get_object_vars($class), 1);

		$class_name = get_class($class);

		$methods = str_replace("Array\n", "{$class_name} Properties\n", $methods);

		self::render($methods, $color, $die);
	}

	/**
	 * Output a variable with var_dump wrapped in pre tags.
	 *
	 * @param $var    mixed  the variable to export
	 * @param $color  int    the border and text color (0-9)
	 */
	public static function d($var, $color = null, $die = false)
	{
		ob_start();
		var_dump($var);
		$dump = ob_get_contents();
		ob_end_clean();

		self::render($dump, $color, $die);
	}

	/**
	 * Output a variable with print_r wrapped in pre tags.
	 *
	 * @param $var    mixed  the variable to export
	 * @param $color  int    the border and text color (0-9)
	 */
	public static function p($var, $color = null, $die = false)
	{
		self::render(print_r($var, 1), $color, $die);
	}

	/**
	 * Render the value wrapped in HTML.
	 *
	 * @param $var    mixed  the variable to export
	 * @param $color  int
	 */
	private static function render($value, $color, $die)
	{
		if (self::$bypass == true) return;

		$color = self::_set_color($color);

		list($caller, $callee) = debug_backtrace();

		echo PHP_EOL . "<pre style='width:auto;background-color:" . self::$colors[$color] . ";border:solid 1px #333;color:#333;font-size:normal;font-family:monospace;line-height:15px;padding:10px;text-align:left;'>";

		echo '<fieldset><legend style="border-radius:7px;font-size:normal;font-weight:bold;padding:5px;background:rgba(0,0,0,0.1);color:rgba(0,0,0,0.6);">'.$callee['file'].' @ line: '.$callee['line'].'</legend><br>';
		echo '<code style="background-color:transparent;">';

		echo $value;

		echo '</code>';
		echo '</fieldset></pre>' . PHP_EOL;

		if ($die) die;
	}

	/**
	 * Set the static color property.
	 *
	 * @param  int  $color
	 * @return int
	 */
	private static function _set_color($color)
	{
		$color = self::validateColor($color);

		if ($color === null)
		{
			if (self::$color == count(self::$colors) - 2)
			{
				self::$color = -1;
			}

			self::$color++;

			$color = self::$color;
		}

		return $color;
	}

	/**
	 * Validate the color.
	 *
	 * @param  int  $color
	 * @return int
	 */
	private static function validateColor($color)
	{
		if ($color === null)
		{
			return $color;
		}

		if (is_numeric($color) and $color >= 0 and $color < count(self::$colors))
		{
			return $color;
		}

		return count(self::$colors) - 1;
	}


}
