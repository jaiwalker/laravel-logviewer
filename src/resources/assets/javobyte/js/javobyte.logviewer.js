var read, logname, date, known_size, full_size;
var interval;
var levels, min_level;



function formatBytes(bytes,decimals) {
   // as answered in
   // http://stackoverflow.com/questions/15900485/correct-way-to-convert-size-in-bytes-to-kb-mb-gb-in-javascript
  if(bytes == 0) return '0 Bytes';
  var k = 1024;
  var sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
  var i = Math.floor(Math.log(bytes) / Math.log(k));
  //return (bytes / Math.pow(k, i)).toPrecision(dm) + ' ' + sizes[i];
  var dm = i == 0 ? 0 : decimals;
  return (bytes / Math.pow(k, i)).toFixed(dm) + ' ' + sizes[i];
}

function resetLog()
{
  if(interval) clearInterval(interval);

  read = 0;

  $('#read-more-btn').hide();
  $('#log-container tbody').empty();
  if(logname){
    var method = $('#select-method').val();
    getLog(method);
    if(method == 'tail' && refreshInterval > 0){
      interval = setInterval(refreshLog, refreshInterval);
    }
  }
}

function updateDownloadButton()
{
  $('#download-btn').prop('disabled', false);
  $('#size-span').text(formatBytes(full_size, 2));
}

function renderLogEntry(entry)
{
  var tr = $('<tr>').attr('class', 'log-entry log-' + entry.level.toLowerCase());
  var content_td = $('<td>');
  var date_td = $('<td>');
  var time_td = $('<td>');
  var channel_td = $('<td>');
  var level_td = $('<td>');

  
  var tds = {
    'channel' : channel_td,
    'date' : date_td, 
    'time' : time_td,
    'level' : level_td,
    'content' : content_td,
  };

  for(i in columnsOrder){
    var column = columnsOrder[i];
    if(disableDateWhenIncluded && date && column == 'date') continue;
    tr.append(tds[column]);
  }

  if(entry.stack !== undefined && entry.stack.length > 0){
    var btn = $('<button>').attr('class', 'btn btn-xs btn-danger');
    btn.append($('<span>').attr('class', 'fa fa-fw fa-wrench'));
    btn.tooltip({
      'title' : 'View stack trace',
      'container' : 'body',
    });

    var stack = $('<div>').attr('class', 'list-group');
    stack.css('font-size', '80%');

    for(i in entry.stack){
      stack.append($('<li>').attr('class', 'list-group-item').text(entry.stack[i]));
    }
    btn.click(function(){
      $('#stacktrace-modal .modal-body').empty().append(stack);
      $('#stacktrace-modal').modal('show');
    });

    var div_right = $('<div>').attr('class', 'pull-right');
    content_td.append(div_right);
    div_right.append(btn);
  }

  channel_td.text(entry.channel).addClass('channel');
  content_td.append(entry.content).addClass('content');
  date_td.text(entry.date).addClass('date');
  time_td.text(entry.time).addClass('time');

  level_td.append($('<span>').attr('class', 'fa fa-fw fa-'+levelIcons[entry.level.toLowerCase()]));
  level_td.append(' ' + entry.level).addClass('level');
  

  var thisLevel = entry.level.toLowerCase();
  var thisIdx = levels.indexOf(thisLevel);
  var idx = levels.indexOf(level);

  if(thisIdx < idx){
    tr.hide();
  }

  return tr;

}

function getLog(method){
  method = method ? method : 'tail';

  $('#read-more-btn').prop('disabled', true);
  
  return $.get(logsPath + logname, {
    date : date,
    offset : read,
    limit : maxSizePerRequest,
    known_size : known_size,
    method : method,
  }, function(data){
    if(data.status == 200){
      read += data.read;
      full_size = data.full_size;
      updateDownloadButton();
      if(read >= known_size && method == 'tail'){
        $('#read-more-btn').hide();
      }else{
        $('#read-more-btn').show();
        $('#read-more-btn').prop('disabled', false);
      }

      for(var i=0; i<data.log_entries.length; i++){
        var tr = renderLogEntry(data.log_entries[i]);
        $('#log-container tbody').append(tr);
      }

    }
  });
}

function refreshLog(){
  return $.get(logsPath + logname, {
    date : date,
    offset : read,
    limit : maxSizePerRequest, //5kB
    known_size : known_size,
    refresh : true
  }, function(data){
    if(data.status == 200){
      read += data.read;
      known_size = data.size;
      full_size = data.full_size;
      updateDownloadButton();
      for(var i=0; i<data.log_entries.length; i++){
        var tr = renderLogEntry(data.log_entries[i]);
        $('#log-container tbody').prepend(tr);
      }
    }
  });
}

$(function(){

  
  $('a[data-log-name]').click(function(){
    logname = $(this).attr('data-log-name');
    known_size = $(this).attr('data-log-size');
    date = undefined;
    if($(this).attr('data-log-date')){
      date = $(this).attr('data-log-date');
    }
    known_size = parseInt(known_size);
    
    console.log('fetching log', logname, date);
    
    $(this).parents('.list-group').find('.active').removeClass('active');
      
    if(date){
      $(this).parents('li').addClass('active');
      $(this).parents('.dropdown-menu').siblings('.list-group-item').addClass('active');
    }else{
      $(this).addClass('active');
    }
    
    resetLog();
  
  });

  levels = $('#select-min-level option').map(function(idx, elem){
    return $(elem).text();
  }).get();

  level = levels[0];

  $('#select-min-level').change(function(){
    level = $(this).val();

    var idx = levels.indexOf(level);

    for(var i=0; i<idx; i++){
      $('.log-' + levels[i]).hide(200);
    }
    for(var i=idx; i<levels.length; i++){
      $('.log-' + levels[i]).show(200);
    }
  });

  $('#select-method').change(function(){
    resetLog();
  })
  
  $('#read-more-btn').hide();
  $('#read-more-btn').click(function(){
    getLog($('#select-method').val());
  });

  $('#download-btn').click(function(event) {
    var path = logsPath + logname;
    if(date){
      path += '?date=' + date;
    }
    window.open(path, '_blank');
  });
});