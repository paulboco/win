<?php

use Paulboco\Ain\Debug;

if ( ! function_exists('dv'))
{
	function dv($var, $color = null)
	{
		Debug::v($var, $color);
	}
}

if ( ! function_exists('dm'))
{
	function dm($var, $color = null)
	{
		Debug::m($var, $color);
	}
}

if ( ! function_exists('dp'))
{
	function dp($var, $color = null)
	{
		Debug::p($var, $color);
	}
}

if ( ! function_exists('da'))
{
	function da($var, $color = null)
	{
		Debug::a($var, $color);
	}
}

if ( ! function_exists('db'))
{
	function db($var)
	{
		Debug::v($var, (int) !! $var);
	}
}
