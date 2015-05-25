<?php
/**
 * This file is part of Cachebust
 *
 * @copyright Copyright (c) Ian Caunce
 * @author    Ian Caunce <iancauncedevelopment@gmail.com>
 * @license   MIT <http://opensource.org/licenses/MIT>
 */

namespace IanCaunce\Cachebust;

use IanCaunce\Cachebust\MissingAssetException;
use IanCaunce\Cachebust\InvalidBustMethodException;
use IanCaunce\Cachebust\InvalidAlgorithmException;
use IanCaunce\Cachebust\InvalidPublicDirectoryException;

class Cachebust
{

    /**
     * Path bust method constant.
     */
    const BUST_METHOD_PATH = 'path';

    /**
     * File bust method constant.
     */
    const BUST_METHOD_FILE = 'file';

    /**
     * Query bust method constant.
     */
    const BUST_METHOD_QUERY = 'query';

    /**
     * Dictates if the package is enabled.
     * @var boolean
     */
    protected $enabled = true;

    /**
     * Dictates the default busting method
     * @var string
     */
    protected $bustMethod;

    /**
     * Dictates if the file contents should
     * be used when generating the hash.
     * @var string
     */
    protected $useFileContents = false;

    /**
     * The hashing algorithm used.
     * @var string
     */
    protected $algorithm = 'crc32';

    /**
     * A string seed which alters the file's hash.
     * @var string
     */
    protected $seed = 'a4bb8768';

    /**
     * Path to the public directory.
     * @var string
     */
    protected $publicDir = '';

    /**
     * A prefix which will be appended before the hash
     * for standard busting (not query busting)
     * @var string
     */
    protected $prefix = '';

    /**
     * Dictates if the package uses the
     * query busting technique.
     * @var boolean
     */
    protected $queryParam = 'c';

    /**
     * Class Constructor
     * @param array[mixed] $options An array of config options.
     */
    public function __construct($options)
    {

        if (isset($options['enabled']) === true) {
            $this->setEnabled($options['enabled']);
        }

        if (isset($options['useFileContents']) === true) {
            $this->setUseFileContents($options['useFileContents']);
        }

        if (isset($options['algorithm']) === true) {
            $this->setAlgorithm($options['algorithm']);
        }

        if (isset($options['seed']) === true) {
            $this->setSeed($options['seed']);
        }

        if (isset($options['prefix']) === true) {
            $this->setPrefix($options['prefix']);
        }

        if (isset($options['publicDir']) === true) {
            $this->setPublicDir($options['publicDir']);
        }

        $bustMethod = isset($options['bustMethod']) === true? $options['bustMethod']: self::BUST_METHOD_FILE;

        $this->setBustMethod($bustMethod);

        if (isset($options['queryParam']) === true) {
            $this->setQueryParam($options['queryParam']);
        }

    }

    /**
     * Sets the enabled flag.
     * @param boolean $enabled True enables the package, false disables it.
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean)$enabled;
    }

    /**
     * Sets the useFileContents flag.
     * @param boolean $useFileContents True enables hashing using the file's contents.
     */
    public function setUseFileContents($useFileContents)
    {
        $this->useFileContents = (boolean)$useFileContents;
    }

    /**
     * Sets the algorithm to be used when hashing.
     * @param boolean $algorithm The name of the algorithm.
     * @throws IanCaunce\Cachebust\InvalidAlgorithmException
     */
    public function setAlgorithm($algorithm)
    {

        if (in_array($algorithm, hash_algos()) === false) {
            throw new InvalidAlgorithmException($algorithm);
        }

        $this->algorithm = $algorithm;
    }

    /**
     * Sets the string seed.
     * @param string $seed Changing this seed will cause all resulting
     *                     hash's to be different.
     */
    public function setSeed($seed)
    {
        $this->seed = (string)$seed;
    }

    /**
     * Sets the public directory path
     * @param string $publicDir Setting this will change the public
     *                          directory path.
     * @throws IanCaunce\Cachebust\InvalidPublicDirectoryException
     */
    public function setPublicDir($publicDir)
    {

        if (file_exists($publicDir) === false) {
            throw new InvalidPublicDirectoryException($publicDir);
        }

        $this->publicDir = $publicDir;
    }

    /**
     * Sets the prefix
     * @param string $prefix Changing this will add a prefix to the returned
     *                       file path.
     */
    public function setPrefix($prefix)
    {
        $this->prefix = (string)$prefix;
    }

    /**
     * Sets the query parameter
     * @param string $queryParam Changing this will change the name of the query
     *                       parameter used.
     */
    public function setQueryParam($queryParam)
    {
        $this->queryParam = (string)$queryParam;
    }

    /**
     * Sets the query bust flag
     * @param boolean $queryBust True enables query bust, false disables it.
     */
    public function setBustMethod($bustMethod)
    {
        if ($bustMethod !== self::BUST_METHOD_FILE && $bustMethod !== self::BUST_METHOD_QUERY && $bustMethod !== self::BUST_METHOD_PATH) {
            throw new InvalidBustMethodException($bustMethod);
        }

        $this->bustMethod = $bustMethod . 'Bust';
    }

    /**
     * Returns the disk path to the file.
     * @param  string $assetWebPath The web accessible path to the file.
     * @param  string $publicDir The path to the public directory. If this is not set,
     *                           the default one will be used.
     * @throws IanCaunce\Cachebust\MissingAssetException
     * @return string The path to the file on disk
     */
    public function getDiskPath($assetWebPath, $publicDir = null)
    {

        if ($publicDir === null) {
            $publicDir = $this->publicDir;
        } elseif (file_exists($publicDir) === false) {
            throw new InvalidPublicDirectoryException($publicDir);
        }

        $publicDir = rtrim($publicDir, '/') . '/';

        $assetDiskPath = $publicDir . ltrim($assetWebPath, '/');

        if (file_exists($assetDiskPath) === false) {
            throw new MissingAssetException($assetWebPath);
        }

        return $assetDiskPath;
    }

    /**
     * Returns the segements of the path.
     * @param  string $assetWebPath The web path of the asset
     * @return array[string]
     */
    public function getPathSegments($assetWebPath)
    {
        $assetWebPath = trim($assetWebPath, '/');

        return explode('/', $assetWebPath);
    }

    /**
     * Generates the prefixed hash of the asset
     * @param  string $assetWebPath Path to the asset
     * @param  string $publicDir The path to the public directory. If this is not set,
     *                           the default one will be used.
     * @return string The prefixed hash of the asset.
     */
    public function getPrefixedHash($assetWebPath, $publicDir = null)
    {

        $hash = $this->getHash($assetWebPath, $publicDir);

        if (strlen($this->prefix) > 0) {
            $hash = $this->prefix . '-' . $hash;
        }

        return $hash;

    }

    /**
     * Generates the hash of the asset
     * @param  string $assetWebPath Path to the asset
     * @param  string $publicDir The path to the public directory. If this is not set,
     *                           the default one will be used.
     * @return string The hash of the asset.
     */
    public function getHash($assetWebPath, $publicDir = null)
    {

        $assetDiskPath = $this->getDiskPath($assetWebPath, $publicDir);

        if ($this->useFileContents === true) {
            $fileStr = file_get_contents($assetDiskPath);
        } else {
            $fileStr = filemtime($assetDiskPath);
        }

        $fileStr .= $this->seed;

        return hash($this->algorithm, $fileStr);

    }

    /**
     * Generates the path of the asset.
     * @param  string $assetWebPath Path of the asset
     * @param  string $publicDir The path to the public directory. If this is not set,
     *                           the default one will be used.
     * @return string The new path of the asset with the hash.
     */
    public function asset($assetWebPath, $publicDir = null)
    {
        return $this->{$this->bustMethod}($assetWebPath, $publicDir);
    }

    /**
     * Generates the path of the asset using the
     * file bust pattern prefix-hash.asset
     * @param  string $assetWebPath Path of the asset
     * @param  string $publicDir The path to the public directory. If this is not set,
     *                           the default one will be used.
     * @return string The new path of the asset with the hash.
     */
    public function fileBust($assetWebPath, $publicDir = null)
    {
        if ($this->enabled === false) {
            return $assetWebPath;
        }

        $hash = $this->getPrefixedHash($assetWebPath, $publicDir);

        $segments = $this->getPathSegments($assetWebPath);

        $asset = $hash . '.' . array_pop($segments);

        array_push($segments, $asset);

        return '/' . implode('/', $segments);

    }

    /**
     * Generates the path of the asset using the
     * path bust pattern /prefix-hash/asset
     * @param  string $assetWebPath Path of the asset
     * @param  string $publicDir The path to the public directory. If this is not set,
     *                           the default one will be used.
     * @return string The new path of the asset with the hash.
     */
    public function pathBust($assetWebPath, $publicDir = null)
    {
        if ($this->enabled === false) {
            return $assetWebPath;
        }

        $hash = $this->getPrefixedHash($assetWebPath, $publicDir);

        $segments = $this->getPathSegments($assetWebPath);

        array_splice($segments, count($segments) - 1, 0, $hash);

        return '/' . implode('/', $segments);
    }

    /**
     * Generates the path of the asset using the
     * query bust pattern asset?v=hash
     * @param  string $assetWebPath Path of the asset
     * @param  string $publicDir The path to the public directory. If this is not set,
     *                           the default one will be used.
     * @return string The new path of the asset with the hash.
     */
    public function queryBust($assetWebPath, $publicDir = null)
    {

        if ($this->enabled === true) {
            $assetWebPath .= '?'.$this->queryParam.'=' . $this->getHash($assetWebPath, $publicDir);
        }

        return $assetWebPath;

    }
}
