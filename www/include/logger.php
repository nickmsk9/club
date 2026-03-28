<?php

/* Made to minimalize count of queries */
class Logger
{
	var $logs = Array();
	var $cwd = '';

	function Logger()
	{
		$this->logs = Array();
		$this->cwd = getcwd();
		set_error_handler(Array($this, 'error_handler'));
	}

	function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		if (error_reporting() == 0)
		{
			/*
			 * dont log this error, the one who wrote that code knew what he wanted
			 */
			return true;
		}
		$log = Array('type' => 'php');
		$log['errno'] = $errno;
		$log['errstr'] = $errstr;
		if (function_exists('debug_print_backtrace'))
		{
			$log['backtrace'] = debug_backtrace();
			unset($log['backtrace'][0]);
			$d = '';
			foreach ($log['backtrace'] AS $k=>$p)
			{
			  $d .= '#' . $k . '  ' . (isset($p['class'])?$p['class'] . $p['type']:'') . $p['function'] . '(' . (is_array($p['args'])?implode(', ', $p['args']):$p['args']) . ') called at [' . (isset($p['file'])?$p['file'] . ':' . $p['line']:'PHP') . ']' . "\n";
			}
			$log['call_stack'] = $d;
		}
		$log['FILE'] = $errfile;
		$log['LINE'] = $errline;
		$log['GET'] = $_GET;
		$log['POST'] = $_POST;
		$log['COOKIE'] = $_COOKIE;
		$log['url'] = $_SERVER['REQUEST_URI'];
		$log['REMOTE']['HOST'] = $_SERVER['REMOTE_ADDR'];
		$log['REMOTE']['PORT'] = $_SERVER['REMOTE_PORT'];

		$this->write($log);
		return true;
	}

	function write($text)
	{
		$this->logs[] = Array('added' => time(), 'txt' => $text);
	}

	function Save()
	{
		if (!$this->logs)
		{
			return;
		}
		$f = $this->cwd . '/logs/' . date('Y-m-d') . '.log';
		$h = fopen($f, 'a+');
		if (!$h)
		{
			return;
		}
		flock($h, LOCK_EX);
		for ($i = 0; $i < count($this->logs); $i++)
		{
			fputs($h, serialize($this->logs[$i]) . "\n");
		}

		flock($h, LOCK_UN);
		fclose($h);
	}
}
?>