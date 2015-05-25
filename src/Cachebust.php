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
use IanCaunce\Cachebust\InvalidAlgorithmException;
use IanCaunce\Cachebust\InvalidPublicDirectoryException;

class Cachebust
{
    /**
     * Dictates if the package is enabled.
     * @var boolean
     */
    protected $enabled = true;

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
    protected $queryBust = false;

    /**
     * Dictates if the package uses the
     * query busting technique.
     * @var boolean
     */
    protected $queryParam = 'c';

    /**
     * Class Constructor
     * @param array $options An array of config options.
     */
    public function __construct($options)
    {

        if (isset($options['enabled']) === true) {
            $this->setEnabled($options['enabled']);
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

        if (isset($options['queryBust']) === true) {
            $this->setQueryBust($options['queryBust']);
        }

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
    public function setQueryBust($queryBust)
    {
        $this->queryBust = (boolean)$queryBust;
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
     * Generates the hash of the asset
     * @param  string $assetWebPath Path to the asset
     * @param  string $publicDir The path to the public directory. If this is not set,
     *                           the default one will be used.
     * @return string The hash of the asset.
     */
    public function getHash($assetWebPath, $publicDir = null)
    {

        $assetDiskPath = $this->getDiskPath($assetWebPath, $publicDir);

        $fileHash = hash_file($this->algorithm, $assetDiskPath);

        return hash($this->algorithm, $fileHash . $this->seed);

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

        if ($this->enabled === true) {

            if ($this->queryBust === true) {

                return $this->queryBust($assetWebPath, $publicDir);

            } else {

                $hash = $this->getHash($assetWebPath, $publicDir);

                $assetWebPath = trim($assetWebPath, '/');

                $segments = explode('/', $assetWebPath);

                $asset = $hash . '-' . array_pop($segments);

                if (strlen($this->prefix) > 0) {
                    $asset = $this->prefix . '-' . $asset;
                }

                array_push($segments, $asset);

                $assetWebPath = '/' . implode('/', $segments);

            }

        }

        return $assetWebPath;

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
