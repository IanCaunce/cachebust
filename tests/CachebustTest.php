<?php

namespace IanCaunce\Cachebust\CachebustTest;

use PHPUnit_Framework_TestCase;
use IanCaunce\Cachebust\Cachebust;
use IanCaunce\Cachebust\MissingAssetException;

require_once(__DIR__ . '/../src/CachebustRuntimeException.php');
require_once(__DIR__ . '/../src/MissingAssetException.php');
require_once(__DIR__ . '/../src/InvalidPublicDirectoryException.php');
require_once(__DIR__ . '/../src/InvalidAlgorithmException.php');
require_once(__DIR__ . '/../src/Cachebust.php');

class CachebustTest extends PHPUnit_Framework_TestCase
{
    public function testBust()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = '/files/c43e1ed8-styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testPrefix()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'prefix' => 'cache',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = '/files/'.$options['prefix'].'-c43e1ed8-styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testQueryParam()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'queryBust' => true,
            'queryParam' => 'cache',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path . '?'.$options['queryParam'].'=c43e1ed8';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testQueryBust()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'queryBust' => true,
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path . '?c=c43e1ed8';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testBustDisabled()
    {
        $options = array(
            'enabled' => false,
            'seed' => 'a4bb8768',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path;
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testQueryBustDisabled()
    {
        $options = array(
            'enabled' => false,
            'seed' => 'a4bb8768',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path;
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->queryBust($path));
    }

    /**
     * @expectedException IanCaunce\Cachebust\MissingAssetException
     */
    public function testMissingAsset()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/missing.css';
        $cachebuster = new Cachebust($options);
        $cachebuster->queryBust($path);
    }

    /**
     * @expectedException IanCaunce\Cachebust\InvalidPublicDirectoryException
     */
    public function testInvalidPublicDirectory()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'publicDir' => dirname(__FILE__) . 'Some/Invalid/Directory'
        );
        $path = '/files/styles.css';
        $cachebuster = new Cachebust($options);
        $cachebuster->queryBust($path);
    }

    /**
     * @expectedException IanCaunce\Cachebust\InvalidAlgorithmException
     */
    public function testInvalidAlgorithm()
    {
        $options = array(
            'enabled' => true,
            'algorithm' => 'invalidAlgorithm',
            'seed' => 'a4bb8768',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $cachebuster = new Cachebust($options);
        $cachebuster->queryBust($path);
    }

    public function testOverriddenPublicDir()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768'
        );
        $path = '/files/styles.css';
        $expected = '/files/c43e1ed8-styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path, dirname(__FILE__)));
    }

    /**
     * @expectedException IanCaunce\Cachebust\InvalidPublicDirectoryException
     */
    public function testInvalidOverriddenPublicDir()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768'
        );
        $path = '/files/styles.css';
        $expected = '/files/c43e1ed8-styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path, dirname(__FILE__) . 'Some/Invalid/Directory'));
    }
}
