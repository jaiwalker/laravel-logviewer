<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Logviewer</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body style="padding-top: 70px">
    
  	<div class="container-fluid">
  		<div class="row">
  			<div class="col-xs-2">

          <h4>Select a log</h4>

          <div class="list-group">
            @foreach($logs as $log_name => $logFiles)
              @if(!isset($logFiles['name']))
              <div class="dropdown">
                <a class="dropdown-toggle list-group-item" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
                  {{$log_name}}
                  <span class="badge">{{count($logFiles)}}</span>
                </a>
                <ul class="dropdown-menu" role="menu">
                  @foreach($logFiles as $date => $logFile)
                  <li role="presentation">
                    <a role="menuitem" tabindex="-1" href="#"
                      data-log-name="{{$log_name}}" data-log-date="{{$date}}" data-log-size="{{$logFile['size']}}">
                      {{$date}}
                    </a>
                  </li>
                  @endforeach
                </ul>
              </div>
              @else
                <a href="#" class="list-group-item" data-log-name="{{$log_name}}" data-log-size="{{$logFiles['size']}}">
                  {{$log_name}}
                </a>
              @endif
            @endforeach
          </div>
        </div>

  			<div class="col-xs-10">
  				
          <div class="form-horizontal">
            <div class="form-group">
              <label for="select-method" class="control-label col-xs-2">Method</label>
              <div class="col-xs-2">
                <select class="form-control" id="select-method" name="method">
                  <option>tail</option>
                  <option>cat</option>
                </select>
              </div>
            
              <label for="select-min-level" class="control-label col-xs-2">Min level</label>
              <div class="col-xs-2">
                <select class="form-control" id="select-min-level" name="level">
                  @foreach(array_reverse((new ReflectionClass(new \Psr\Log\LogLevel))->getConstants()) as $level)
                  <option>{{$level}}</option>
                  @endforeach
                </select>  
              </div>

              <div class="col-xs-2">
                <button id="download-btn" class="btn btn-default btn-block" disabled>
                  <span class="glyphicon glyphicon-download-alt"></span> <span id="size-span">Download</span></button>
              </div>
            </div>
          </div>
          <table id="log-container" class="table table-striped">

            <thead>
              
            </thead>
            <tbody>
              
            </tbody>
          </table>
          <div class="text-right">
            <button id="read-more-btn" class="btn btn-default">Read more</button>
          </div>
  			</div>
  		</div>
  	</div>


    <div class="modal" id="stacktrace-modal" role="dialog" aria-labelledby='stacktrace-title' aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 id="stacktrace-title" class="modal-title">Stack trace</h4>
          </div>

          <div class="modal-body">
            
          </div>
        </div>
      </div>  
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
	  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    
    <link rel="stylesheet" href="{{asset('javobyte/css/javobyte.logviewer.css')}}">

    <script type="text/javascript" src="{{asset('javobyte/js/javobyte.logviewer.config.js')}}"></script>
    <script type="text/javascript" src="{{asset('javobyte/js/javobyte.logviewer.js')}}"></script>

  </body>
</html>