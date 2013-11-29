<?php

use Paulboco\Win\Debug;

if ( ! function_exists('dv'))
{
	/**
	 * Dump a variable using var_export.
	 *
	 * @param  mixed   $var
	 * @param  int     $color
	 * @return void
	 */
	function dv($var, $color = null)
	{
		Debug::v($var, $color);
	}
}

if ( ! function_exists('dm'))
{
	/**
	 * Dump an object's methods.
	 *
	 * @param  mixed   $var
	 * @param  int     $color
	 * @return void
	 */
	function dm($var, $color = null)
	{
		Debug::m($var, $color);
	}
}

if ( ! function_exists('dp'))
{
	/**
	 * Dump a variable using print_r.
	 *
	 * @param  mixed   $var
	 * @param  int     $color
	 * @return void
	 */
	function dp($var, $color = null)
	{
		Debug::p($var, $color);
	}
}

if ( ! function_exists('da'))
{
	/**
	 * Dump an object's attributes.
	 *
	 * @param  mixed   $var
	 * @param  int     $color
	 * @return void
	 */
	function da($var, $color = null)
	{
		Debug::a($var, $color);
	}
}

if ( ! function_exists('db'))
{
	/**
	 * Dump a boolean variable using var_export.
	 *
	 * @param  mixed   $var
	 * @return void
	 */
	function db($var)
	{
		Debug::v($var, (int) !! $var);
	}
}
