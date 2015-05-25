<?php
/**
 * This file is part of Cachebust
 *
 * @copyright Copyright (c) Ian Caunce
 * @author    Ian Caunce <iancauncedevelopment@gmail.com>
 * @license   MIT <http://opensource.org/licenses/MIT>
 */

namespace IanCaunce\Cachebust;

class MissingAssetException extends CachebustRuntimeException
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
    protected $messageTemplate = 'The Asset "%s" Does Not Exist.';

    /**
     * Constructor Overloader
     *
     * @param string $assetPath The path of the asset which caused the exception.
     */
    public function __construct($assetPath){

        $this->message = sprintf($this->messageTemplate, $assetPath);

    }
}
