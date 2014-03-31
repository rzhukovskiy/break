<?php
    /*
     * Пишет логи в файл
     */
    class Logger {
        //запись
        public function toLog($message) {
            if(!is_dir('log')) {
                mkdir('log');
            }
            $fp = fopen('log/methods.log','a');
            fwrite($fp, "----------------------------\n\n");
            fwrite($fp, $message);
            fwrite($fp, "----------------------------\n\n");
            fclose($fp);
        }
    }

