<?php

namespace IanCaunce\Cachebust\CachebustTest;

use PHPUnit_Framework_TestCase;
use IanCaunce\Cachebust\Cachebust;
use IanCaunce\Cachebust\MissingAssetException;

require_once(__DIR__ . '/../src/CachebustRuntimeException.php');
require_once(__DIR__ . '/../src/MissingAssetException.php');
require_once(__DIR__ . '/../src/InvalidPublicDirectoryException.php');
require_once(__DIR__ . '/../src/InvalidBustMethodException.php');
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
        $expected = '/files/3de1e771.styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testUseFileContents()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'useFileContents' => true,
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = '/files/5b252146.styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testFilePrefix()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'prefix' => 'cache',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = '/files/'.$options['prefix'].'-3de1e771.styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testPathPrefix()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'prefix' => 'cache',
            'bustMethod' => Cachebust::BUST_METHOD_PATH,
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = '/files/'.$options['prefix'].'-3de1e771/styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testQueryPrefix()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'prefix' => 'cache',
            'bustMethod' => Cachebust::BUST_METHOD_QUERY,
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path . '?c=3de1e771';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testQueryBust()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'bustMethod' => Cachebust::BUST_METHOD_QUERY,
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path . '?c=3de1e771';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testPathBust()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'bustMethod' => Cachebust::BUST_METHOD_PATH,
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = '/files/3de1e771/styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testQueryParam()
    {
        $options = array(
            'enabled' => true,
            'seed' => 'a4bb8768',
            'bustMethod' => Cachebust::BUST_METHOD_QUERY,
            'queryParam' => 'cache',
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path . '?'.$options['queryParam'].'=3de1e771';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testPathBustDisabled()
    {
        $options = array(
            'enabled' => false,
            'seed' => 'a4bb8768',
            'bustMethod' => Cachebust::BUST_METHOD_PATH,
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path;
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
    }

    public function testFileBustDisabled()
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
            'bustMethod' => Cachebust::BUST_METHOD_QUERY,
            'publicDir' => dirname(__FILE__)
        );
        $path = '/files/styles.css';
        $expected = $path;
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path));
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
        $expected = '/files/3de1e771.styles.css';
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
        $expected = '/files/3de1e771.styles.css';
        $cachebuster = new Cachebust($options);
        $this->assertEquals($expected, $cachebuster->asset($path, dirname(__FILE__) . 'Some/Invalid/Directory'));
    }
}
