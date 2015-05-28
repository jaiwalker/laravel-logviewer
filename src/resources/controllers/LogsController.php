<?php

namespace JavoByte\Logviewer;

use \Illuminate\Routing\Controller;
use Input;
use Request;
use View;

class LogsController extends Controller
{
  
  public function index(LogReader $reader){
    $logs = $reader->getFiles();

    return view('logviewer::logviewer')->withLogs($logs);
  }

  public function show(LogReader $reader, $logName)
  {
    if(Request::ajax()){
      
      $content = $reader->readLog($logName, Input::get('known_size'),
                    Input::get('date'),
                    Input::get('offset'), Input::get('limit'),
                    Input::get('method'), Input::get('refresh', false));
    
      return response()->json([
        'status' => 200,
      ] + $content);
    }else{
      $logs = $reader->getFiles();
      $log = ($date = Input::get('date')) ? @$logs[$logName][$date] : @$logs[$logName]; 


      if(!$log){
        abort(404);
      }
      return response()->download($log['path']);
    }
  }

}