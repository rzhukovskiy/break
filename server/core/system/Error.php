<?php
    /**
     * Обработка ошибок
     */
    class ErrorOrWarningException extends Exception {
        protected $context = null;
        public function getContext() {
            return $this->context;
        }

        public function setContext($value) {
            $this->context = $value;
        }

        public function __construct($code, $message, $file, $line, $context) {
            parent::__construct($message, $code);

            $this->file = $file;
            $this->line = $line;
            $this->setContext($context);
        }
    }

    class Error {
        private $http_code = 500;
        private $http_codes;
        public function __construct() {
            $this->http_codes = array(
                                200  => 'OK',
                                201  => 'Created',
                                202  => 'Accepted',
                                203  => 'Non-Authoritative Information',
                                204  => 'No Content',
                                205  => 'Reset Content',
                                206  => 'Partial Content',

                                300  => 'Multiple Choices',
                                301  => 'Moved Permanently',
                                302  => 'Found',
                                304  => 'Not Modified',
                                305  => 'Use Proxy',
                                307  => 'Temporary Redirect',

                                400  => 'Bad Request',
                                401  => 'Unauthorized',
                                403  => 'Forbidden',
                                404  => 'Not Found',
                                405  => 'Method Not Allowed',
                                406  => 'Not Acceptable',
                                407  => 'Proxy Authentication Required',
                                408  => 'Request Timeout',
                                409  => 'Conflict',
                                410  => 'Gone',
                                411  => 'Length Required',
                                412  => 'Precondition Failed',
                                413  => 'Request Entity Too Large',
                                414  => 'Request-URI Too Long',
                                415  => 'Unsupported Media Type',
                                416  => 'Requested Range Not Satisfiable',
                                417  => 'Expectation Failed',

                                500  => 'Internal Server Error',
                                501  => 'Not Implemented',
                                502  => 'Bad Gateway',
                                503  => 'Service Unavailable',
                                504  => 'Gateway Timeout',
                                505  => 'HTTP Version Not Supported'
                            );
        }

        public function htmlError($error) {
            $style = "
            body { margin: 0px; padding: 0px; font-family: 'Helvetica Neue', Arial, 'Liberation Sans', FreeSans, sans-serif;
            font-size: 14px; color:#333333; background: #F7F4E9;}
            #header { background:#313230; color:#ffffff; }
            #header div {margin-left:20px; padding-top:10px; }
            #message { font-size:40px; }
            #version { font-size:14px; padding-bottom:10px; }
            h2, h3, #trace { margin-left:20px; margin-right:20px; padding:5px; }
            h2 { background:#434A48; color:#ffffff; }
            h3 { border-bottom:1px solid #dddddd; }
            .trace { margin-left:20px; margin-right:20px; }
            .trace div { padding-top: 5px; } ";

            ob_get_clean();
            $html_trace = '';
            $trace_string = explode("\n", $error->getTraceAsString());
            foreach($trace_string as $trace_line) {
                $html_trace .= "<div>".htmlspecialchars($trace_line)."</div>";
            }
            $server_trace = '';
            foreach($_SERVER as $key => $val) {
                $server_trace .= "<div>$key: $val</div>";
            }
            echo "<html>
                        <head>
                            <title>".$this->http_code." - ".$this->http_codes[$this->http_code]."</title>
                            <style>$style</style>
                        </head>
                        <body>
                            <div id='header'>
                                <div id='message'>Error traceback: ".$this->http_code." - ".$this->http_codes[$this->http_code]."</div>
                                <div id='version'>Version: ".phpversion()."</div>
                            </div>
                            <h2>Http error code: {$this->http_code}</h2>
                            <h2>Error code: {$error->getCode()}</h2>
                            <h3>Message: {$error->getMessage()}</h3>
                            <h3>File: {$error->getFile()}, line: {$error->getLine()}</h3>
                            <h2>Traceback: </h2>
                            <div class='trace'>{$html_trace}</div>
                            <h2>Environment vars: </h2>
                            <div class='trace'>{$server_trace}</div>
                        </body>
                    </html>";
            exit(1);
        }

        public function setError($message, $http_code = 500, $code = E_USER_ERROR) {
            $this->http_code = $http_code;
            trigger_error($message, $code);
        }

        public static function exceptionHandler($code, $message, $file, $line, $context) {
            //for not handling notices
            if ($code == E_STRICT)
                return true;

            throw new ErrorOrWarningException($code, $message, $file, $line, $context);
        }
    }





