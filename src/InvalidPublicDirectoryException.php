<?php
/**
 * This file is part of Cachebust
 *
 * @copyright Copyright (c) Ian Caunce
 * @author    Ian Caunce <iancauncedevelopment@gmail.com>
 * @license   MIT <http://opensource.org/licenses/MIT>
 */

namespace IanCaunce\Cachebust;

class InvalidPublicDirectoryException extends CachebustRuntimeException
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
    protected $messageTemplate = 'The Directory "%s" is Invalid.';

    /**
     * Constructor Overloader
     *
     * @param string $publicDir The public dir which caused the exception.
     */
    public function __construct($publicDir)
    {
        $this->message = sprintf($this->messageTemplate, $publicDir);
    }
}
