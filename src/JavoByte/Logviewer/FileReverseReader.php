<?php

namespace JavoByte\Logviewer;

use SplFileObject;
use Log;

class FileReverseReader
extends SplFileObject{

	private $eof;

	public function __construct($path)
	{

		parent::__construct($path);
		$this->fseek(-2, SEEK_END);
		$this->eof = false;
		
	}


	public function fgets()
	{
		$line = '';
		$position = $this->ftell();
		while(($this->fseek($position) !== -1)){
			
			$char = $this->fgetc($position);
			if($char == PHP_EOL){
				break;
			}else{
				$line = $char . $line;
			}
			$position--;
			
		}
		if(strlen($line) > 0){
			if($position < 0){
				$this->eof = true;
			}else{
				$this->fseek($position-1, SEEK_SET); // Skip next EOL
			}
			return $line.PHP_EOL;
		}else{
			return null;
		}
		
	}

	public function eof()
	{
		return $this->eof or parent::eof();
	}

	public function fseek($offset, $whence = SEEK_SET)
	{
		if($this->eof and $position < 0){
			return -1;
		}
		return parent::fseek($offset, $whence);
	}

}