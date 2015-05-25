<?php
/**
 * This file is part of Cachebust
 *
 * @copyright Copyright (c) Ian Caunce
 * @author    Ian Caunce <iancauncedevelopment@gmail.com>
 * @license   MIT <http://opensource.org/licenses/MIT>
 */

namespace IanCaunce\Cachebust;

class InvalidAlgorithmException extends CachebustRuntimeException
{
    /**
     * Exception Message
     * @var string
     */
    protected $message;

    /**
     * Exception Message Template
     * @var string
     */
    protected $messageTemplate = 'The Algorithm "%s" is Invalid.';

    /**
     * Constructor Overloader
     *
     * @param string $algorithm The name of the algorithm which caused the exception.
     */
    public function __construct($algorithm){

        $this->message = sprintf($this->messageTemplate, $algorithm);

    }
}
