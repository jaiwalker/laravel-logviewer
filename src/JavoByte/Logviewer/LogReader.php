<?
namespace JavoByte\Logviewer;

use SplFileInfo;
use SplFileObject;
use ReflectionClass;
use \Psr\Log\LogLevel;
use File;

class LogReader
{

	private $path;

	private static $date_regex = '/\d{4}-\d{2}-\d{2}$/';
	private static $date_separator = '-';

	private static $log_date_regex = '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/';
	
	private static function getLevelPattern()
	{
		$levels = (new ReflectionClass(new LogLevel))->getConstants();
		$levels = join("|", $levels);
		return "/^(.+?)\.($levels):/i";
	}

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function getFiles()
	{
		$filesArray = [];
		$files = File::glob($this->path . '/*.log');
		foreach($files as $file_path){
			$name = File::name($file_path);
			
			$file = [
				'path' => $file_path,
				'name' => $name,
				'size' => File::size($file_path)
			];
			$match = [];
			if(preg_match(self::$date_regex, $name, $match)){
				
				$date = $match[0];
				$canonical_name = preg_replace(self::$date_regex, '', $name);
				
				$canonical_name = substr(
															$canonical_name,
															0,
															strlen($canonical_name)-strlen(self::$date_separator)
														); //remove date separator

				$file['name'] = $canonical_name;
				$file['date'] = $date;

				$filesArray[$canonical_name][$date] = $file;
			}else{
				$filesArray[$name] = $file;
			}
		}

		return $filesArray;
	}

	public function readLog($name, $known_size, $date = NULL, $offset = 0, $limit = NULL, $method = 'tail', $refresh = false){
		$logs = $this->getFiles();
		$logGroup = $logs[$name];
		
		if($date){
			$log = $logGroup[$date];
		}else{
			$log = $logGroup;
		}
		$path = $log['path'];
		if($path){
		
			if($refresh){
				return $this->refreshLog($path, $known_size, $limit);
			}

			if($method == 'cat'){
				$file = new SplFileObject($path);
				if($offset > 0){
					$file->fseek($offset);
				}
			}else{
				$file = new FileReverseReader($path);
				$file->fseek($known_size-2);
				if($offset > 0){
					$file->fseek(-$offset, SEEK_CUR);
				}

			}
			$read = 0;

			$level_pattern = self::getLevelPattern();

			$log_entries = [];
			$stack = [];


			while($entry = $this->readLogEntry($file, $method)){
				
				$read += $entry['read'];
				unset($entry['read']);
				$log_entries[] = $entry;
				if($limit and $limit <= $read){
					break;
				}
			}
			return [
				'log_entries' => $log_entries,
				'read' => $read,
				'full_size' => $file->getSize()
			];
		}else{
			return NULL;
		}
	}

	private function refreshLog($path, $known_size, $limit = NULL)
	{
		$file = new SplFileObject($path);
		if($file->getSize() <= $known_size){
			return [
				'log_entries' => [],
				'read' => 0,
				'size' => $file->getSize(),
				'full_size' => $file->getSize()
			];
		}

		$file->fseek($known_size);

		$log_entries = [];
		$read = 0;
		while($entry = $this->readLogEntry($file, 'cat')){
			$read += $entry['read'];
			unset($entry['read']);
			$log_entries[] = $entry;

			if($read >= $limit){
				break;
			}
		}

		return [
			'log_entries' => $log_entries,
			'read' => $read,
			'size' => $known_size + $read,
			'full_size' => $file->getSize()
		];
	}


	private function readLogEntry($file, $method = 'cat')
	{
		$read = 0;
		$stack = [];
		$entry = NULL;

		$level_pattern = self::getLevelPattern();

		while(!$file->eof() and ($line = $file->fgets()))
		{
			$read += strlen($line);
			$match = [];
			$is_log_entry = preg_match(self::$log_date_regex, $line, $match);
			
			if($is_log_entry){

				$date = \Carbon\Carbon::createFromFormat('[Y-m-d H:i:s]', $match[0]);
				$content = substr($line, strlen($match[0]) + 1);

				$channel_level = [];
				preg_match($level_pattern, $content, $channel_level);

				$channel = @$channel_level[1];
				$level = @$channel_level[2];

				$content = substr($content, strlen(@$channel_level[0])+1);

				$entry = [
					'date' => $date->format('Y-m-d'),
					'time' => $date->format('H:i:s'),
					'content' => $content,
					'channel' => $channel,
					'level' => $level,
					'stack' => $stack
				];

				if($method == 'tail' and count($stack) > 0){
					$stack = array_reverse($stack);
					$entry['stack'] = $stack;
				}

				break;
				
			}else{

				$stack[] = $line;
			}
		}

		if($method == 'cat' and is_array($entry)) {
			$stack = [];

			$pos = $file->ftell();
			
			while(!$file->eof() and (($line = $file->fgets()))){
				if(preg_match(self::$log_date_regex, $line)){
					$file->fseek($pos);
					break;
				}else{
					$read += strlen($line);
					$pos = $file->ftell();
					$stack[] = $line; 
				}
			
			}

			$entry['stack'] = $stack;
		}

		if($read > 0 and is_array($entry)){
			$entry['read'] = $read;
		}

		return $entry;
	}

}