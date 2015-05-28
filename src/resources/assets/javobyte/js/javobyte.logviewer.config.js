
var logsPath = '/logs/'; // Path where JavoByte\Logviewer\LogsController is.
var refreshInterval = 30000; // Interval in miliseconds when the log will be refresh. Only works when method is tail. Set 0 to disable.
var columnsOrder = ['channel', 'date', 'time', 'level', 'content']; // The order of the columns that will be shown
var disableDateWhenIncluded = true; // When true, date will not be shown in logs which include date such as laravel-2015-01-01.log
var maxSizePerRequest = 5*(1024); // Max size of the response. This is not estrict. This means, the response size can be greater if when the limit was reached, a stacktrace was being loaded
var levelIcons = { // Icons for each level. Change this if you want to use another. Icons can only be those provided by FontAwesome 4.3.0
  'debug' : 'bug',
  'info' : 'info',
  'notice' : 'newspaper-o',
  'warning' : 'exclamation',
  'error' : 'exclamation-triangle',
  'critical' : 'exclamation-triangle',
  'alert' : 'bomb',
  'emergency' : 'fire',
};

