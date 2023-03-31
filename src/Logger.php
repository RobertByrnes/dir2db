<?php

namespace dir2db;

/**
 * Simple logger class.
 *
 * Simple class with methods & properties to:
 *   - Keep track of log entries.
 *   - Keep track of timings with time() and timeEnd() methods, Javascript style.
 *   - Optionally write log entries in real-time to file or to screen.
 *   - Optionally dump the log to file in one go at any time.
 * 
 * Log entries can be added with any of the following methods:
 *  - info( $message, $title = '' )      // an informational message intended for the user
 *  - debug( $message, $title = '' )     // a diagnostic message intended for the developer
 *  - warning( $message, $title = '' )   // a warning that something might go wrong
 *  - error( $message, $title = '' )     // explain why the program is going to crash
 * The $title argument is optional; if present, it will be
 * prepended to the message: "$title => $message'.
 * 
 * For example, the following code
 *  > Logger::info( "program started" );
 *  > Logger::debug( "variable x is false" );
 *  > Logger::warning( "variable not set, something bad might happen" );
 *  > Logger::error( "file not found, exiting" );
 * will print to STDOUT the following lines:
 *  $ 2021-07-21T11:11:03+02:00 [INFO] : program started
 *  $ 2021-07-21T11:11:03+02:00 [DEBUG] : variable x is false
 *  $ 2021-07-21T11:11:03+02:00 [WARNING] : variable not set, something bad might happen
 *  $ 2021-07-21T11:11:03+02:00 [ERROR] : file not found, exiting
 *
 * To write to file, prepend the following line:
 *  > Logger::$write_log = true;
 *
 * To customize the log file path:
 *  > Logger::$log_dir = 'mylogdir';
 *  > Logger::$log_file_name = 'logname';
 *  > Logger::$log_file_extension = 'txt';
 *
 * To overwrite the log file at every run of the script:
 *  > Logger::$log_file_append = false;
 *
 * To prevent printing to STDOUT:
 * > Logger::$print_log = false;
 *
 * Note: the function uses the $logger_ready property to understand whether to load
 * the init() function. We do this to make the class work straight away, without the
 * need to instantiate it. This however can create race conditions if you are executing
 * parallel code. 
 *
 * TODO: Remove the methods and make this an actual class that can be initialized
 * multiple times.
 */
class Logger
{
    /**
     * Incremental log, where each entry is an array with the following elements:
     *
     *  - timestamp => timestamp in seconds as returned by time()
     *  - level => severity of the bug; one between debug, warning, error, critical
     *  - name => name of the log entry, optional
     *  - message => actual log message
     *
     * @var array
     */
    protected array $log = [];

    /**
     * Whether to print log entries to screen as they are added.
     *
     * @var bool
     */
    public bool $print_log = true;

    /**
     * Whether to write log entries to file as they are added.
     *
     * @var bool
     */
    public bool $write_log = false;

    /**
     * Directory where the log will be dumped, without final slash; default
     * is this file's directory
     *
     * @var string
     */
    public string $log_dir = __DIR__."/../logs";
    
    /**
     * File name for the log saved in the log dir
     *
     * @var string
     */
    public string $log_file_name = '';

    /**
     * File extension for the logs saved in the log dir
     *
     * @var string
     */
    public string $log_file_extension = "log";
    
    /**
     * Whether to append to the log file (true) or to overwrite it (false)
     *
     * @var bool
     */
    public bool $log_file_append = true;

    /**
     * Absolute path of the log file, built at run time
     *
     * @var string
     */
    private string $log_file_path = '';

    /**
     * Where should we write/print the output to? Built at run time
     *
     * @var array
     */
    private array $output_streams = [];

    /**
     * Whether the init() function has already been called
     *
     * @var bool
     */
    private bool $logger_ready = false;
    
    /**
     * Associative array used as a buffer to keep track of timed logs
     *
     * @var array
     */
    private array $time_tracking = [];

    /**
     * @param string|null $log_file_name
     * @param bool $log_file_append
     * @param bool $print_log
     */
    public function __construct(
        string $log_file_name = null,
        bool $log_file_append = true,
        bool $print_log = false
    ) {
        ($log_file_name !== null) ? $this->log_file_name = $log_file_name : $this->log_file_name = 'app';
        ($log_file_append !== true) ?: $this->log_file_append = false;
        ($print_log === true) ?: $this->print_log = true;
    }

    /**
     * Add a log entry with an informational message for the user
     *
     * @param string $message
     * @param string $name
     * @return array
     */
    public function info(string $message, string $name = ''): array
    {
        return $this->add($message, $name, 'info');
    }

    /**
     * Add a log entry with a diagnostic message for the developer
     *
     * @param string $message
     * @param string $name
     * @return array
     */
    public function debug(string $message, string $name = ''): array
    {
        return $this->add($message, $name, 'debug');
    }

    /**
     * Add a log entry with a warning message
     *
     * @param string $message
     * @param string $name
     * @return array
     */
    public function warning(string $message, string $name = ''): array
    {
        return $this->add($message, $name, 'warning');
    }

    /**
     * Add a log entry with an error - usually followed by
     * script termination
     *
     * @param string $message
     * @param string $name
     * @return array
     */
    public function error(string $message, string $name = ''): array
    {
        return $this->add($message, $name, 'error');
    }

    /**
     * Start counting time, using $name as identifier. Returns the
     * start time or false if a time tracker with the same name
     * exists
     *
     * @param string $name
     * @return float
     */
    public function time(string $name): float
    {
        if (!isset($this->time_tracking[$name])) {
            $this->time_tracking[$name] = microtime(true);
            return $this->time_tracking[$name];
        }
        else {
            return 0;
        }
    }

    /**
     * Stop counting time, and create a log entry reporting the elapsed amount of
     * time. Returns the total time elapsed for the given time-tracker, or
     * false if the time tracker is not found
     *
     * @param string $name
     * @return string
     */
    public function timeEnd(string $name): string
    {
        if (isset($this->time_tracking[$name])) {
            $start = $this->time_tracking[$name];
            $end = microtime(true);
            $elapsed_time = number_format(($end-$start),2);
            unset($this->time_tracking[$name]);
            $this->add("$elapsed_time seconds", "'$name' took", "timing");
            return $elapsed_time;
        }
        else {
            return '';
        }
    }

    /**
     * Add an entry to the log
     * This function does not update the pretty log
     *
     * @param string $message
     * @param string $name
     * @param string $level
     * @return array
     */
    private function add(
        string $message,
        string $name = '',
        string $level = 'debug'
    ): array {
        /* Create the log entry */
        $log_entry = [
            'timestamp' => time(),
            'name' => $name,
            'message' => $message,
            'level' => $level,
        ];

        /* Add the log entry to the incremental log */
        $this->log[] = $log_entry;

        /* Initialize the logger if it hasn't been done already */
        if (!$this->logger_ready) {
            $this->init();
        }

        /* Write the log to output, if requested */
        if ($this->logger_ready && count($this->output_streams) > 0) {
            $output_line = $this->format_log_entry($log_entry) . PHP_EOL;
            foreach ($this->output_streams as $key => $stream) {
                fputs($stream, $output_line);
            }
        }
        return $log_entry;
    }

    /**
     * Take one log entry and return a one-line human readable string
     *
     * @param array $log_entry
     * @return string
     */
    public function format_log_entry(array $log_entry): string
    {
        $log_line = "";

        if (!empty($log_entry)) {
            /* Make sure the log entry stringifies */
            $log_entry = array_map(function($v) {
                return print_r($v,true);
                },
                $log_entry
            );
        
            /* Build a line of the pretty log */
            $log_line .= date('c', $log_entry['timestamp']) . " ";
            $log_line .= "[" . strtoupper($log_entry['level']) . "] : ";
            if (!empty($log_entry['name'])) {
                $log_line .= $log_entry['name'] . " => ";
            }
            $log_line .= $log_entry['message'];
        }
        return $log_line;
    }

    /**
     * Determine whether and where the log needs to be written; executed only
     * once
     *
     * @return void {array} - An associative array with the output streams. The
     * keys are 'output' for STDOUT and the filename for file streams
     */
    public function init(): void
    {
        if ( ! $this->logger_ready ) {

            /* Build log file path */
            if ( file_exists( $this->log_dir ) ) {
                $this->log_file_path = implode(
                    DIRECTORY_SEPARATOR,
                    [ $this->log_dir, $this->log_file_name ]
                );
                
                if ( ! empty( $this->log_file_extension ) ) {
                    $this->log_file_path .= "." . $this->log_file_extension;
                }
            }

            /* Print to screen */
            if ( true === $this->print_log ) {
                $this->output_streams[ 'stdout' ] = fopen($this->log_file_path, 'a');
            }

            /* Print to log file */
            if ( true === $this->write_log ) {
                if ( file_exists( $this->log_dir ) ) {
                    $mode = $this->log_file_append ? "a" : "w";
                    $this->output_streams[ $this->log_file_path ] = fopen ( $this->log_file_path, $mode );
                }
            }
        }

        /* Now that we have assigned the output stream, this function does not need
        to be called anymore */
        $this->logger_ready = true;
    }

    /**
     * Dump the whole log to the given file
     *
     * Useful if you don't know beforehand the name of the log file. Otherwise,
     * you should use the real-time logging option, that is, the $write_log or
     * $print_log options
     *
     * @param string $file_path
     * @return bool
     */
    public function dump_to_file(string $file_path=''): bool
    {
        if (!$file_path) {
            $file_path = $this->log_file_path;
        }

        if (file_exists(dirname($file_path))) {
            $mode = $this->log_file_append ? "a" : "w";
            $output_file = fopen($file_path, $mode);

            foreach ($this->log as $log_entry) {
                $log_line = $this->format_log_entry($log_entry);
                fwrite($output_file, $log_line . PHP_EOL);
            }
            fclose($output_file);
            return true;
        }
        return false;
    }
    
    /**
     * Dump the whole log to string, and return it
     *
     * @return string
     */
    public function dump_to_string(): string
    {
        $output = '';
        
        foreach ($this->log as $log_entry) {
            $log_line = $this->format_log_entry($log_entry);
            $output .= $log_line . PHP_EOL;
        }
        
        return $output;
    }
}