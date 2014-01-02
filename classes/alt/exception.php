<?php defined('SYSPATH') or die('No direct script access.');

class Alt_Exception extends Kohana_Kohana_Exception {
	public static function handler(Exception $e)
	{
            if (Kohana::DEVELOPMENT === Kohana::$environment) {
                parent::handler($e);
            } else {
                try {

                    Kohana::$log->add(Log::ERROR, parent::text($e));

                    /// Sending email notification
                    self::sendmail($e);
                    if($e->getCode() == 404){
                        echo View::factory("alt-error/404");
                    }else{
                        echo View::factory("alt-error/404");
                    }
                    
                } catch (Exception $e) {
                    // Display the exception text
                    echo parent::text($e);

                    // Exit with an error status
                    exit(1);
                }
            }
            
        }
        private static function sendmail(Exception $e)
        {
                     // Get the exception information
                    $type    = get_class($e);
                    $code    = $e->getCode();
                    $message = $e->getMessage();
                    $file    = $e->getFile();
                    $line    = $e->getLine();

                    // Get the exception backtrace
                    $trace = $e->getTrace();


                    // We may don' want see error messages from silly bots
                    $is_bot = preg_match("/(heritrix|bingbot|YandexBot|Googlebot|crawler|AhrefsBot)/", $_SERVER['HTTP_USER_AGENT']);
                    $allow_bots =kohana::$config->load('alt-error')->get('allow_bots') ;

                    if ( $allow_bots OR !$is_bot){

                            $headers = 'MIME-Version: 1.0' . "\r\n" ."Content-type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\nFrom:" . kohana::$config->load('alt-error')->get('site_title') . " <" . kohana::$config->load('alt-error')->get('From') . ">";

                            $subject = 'Error notification from ' . kohana::$config->load('alt-error')->get('site_title');
                            $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';

                            $mail_message = "URL: http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] . "<br>";
                            $mail_message .= "Referer: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']:"") . "<br>";
                            $mail_message .= "User-agent: " . $_SERVER['HTTP_USER_AGENT'] . "<br>";
                            $mail_message .= "Users IP: " . $_SERVER['REMOTE_ADDR'] . "<br><br><br>";

                            $mail_message .= "$type [ $code ]: ". html::chars($message) . "<br>" .
                                        Debug::path($file) ." [ $line ]";

                            $mail_message .= "<ol>";
                            foreach (Debug::trace($trace) as $i => $step){
                                $mail_message .= "<li><p>";
                                if ($step['file']){
                                    $mail_message .= Debug::path($step['file']) . " [ " . $step['line'] ." ]";
                                }else{
                                    $mail_message .= __('PHP internal call');
                                }

                                $mail_message .=  " &raquo; {$step['function']} () ";
                                $mail_message .= "</p></li>";

                            };
                            $mail_message .= "</ol>";


                            $mailto = kohana::$config->load('alt-error')->get('To');

                            mail($mailto, $subject,$mail_message, $headers);
                    }
        }
}