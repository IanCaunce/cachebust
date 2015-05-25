<?php
/**
 * This file is part of Cachebust
 *
 * @copyright Copyright (c) Ian Caunce
 * @author    Ian Caunce <iancauncedevelopment@gmail.com>
 * @license   MIT <http://opensource.org/licenses/MIT>
 */

namespace IanCaunce\Cachebust;

class InvalidBustMethodException extends CachebustRuntimeException
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
    protected $messageTemplate = 'The Bust Method "%s" is Invalid.';

    /**
     * Constructor Overloader
     *
     * @param string $bustMethod The bust method trying to be set.
     */
    public function __construct($bustMethod)
    {
        $this->message = sprintf($this->messageTemplate, $bustMethod);
    }
}
