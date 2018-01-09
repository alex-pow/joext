<?php

//no direct accees
defined('_JEXEC') or die('resticted aceess');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class cache_ap_core {

    protected static $cacheDir = null;
    protected static $cacheIndex = null;
    protected static $cacheIndexFile = null;
    protected static $cacheIndexLoaded = 0;

    //initialize
    public function __construct() {
        //load index file
        self::$cacheDir = JPATH_CACHE . DS . 'cache_ap' . DS;
        self::$cacheIndexFile = self::$cacheDir . 'cacheIndex';
    }

    private static function loadCacheIndex() {
        if (is_file(self::$cacheIndexFile)) {
            self::$cacheIndex = unserialize(JFile::read(self::$cacheIndexFile));
        } else {
            self::$cacheIndex = new stdClass();
        }
    }

    public static function setCache($key, $data, $durationMin = 60) {
        if (!self::$cacheIndexLoaded) {
            self::loadCacheIndex();
        }
        self::writeCache($key, $data, $durationMin);
    }

    public static function getCache($key, $data = false) {
        if (!self::$cacheIndexLoaded) {
            self::loadCacheIndex();
        }

        if (isset(self::$cacheIndex->$key)) {
            $readResulat = self::readCache(self::$cacheIndex->$key);
            if (!empty($readResulat)) {
                $data = $readResulat;
            } else {
                //delete cache file
                JFile::delete(self::$cacheIndex->$key->cacheFile);
                //remove entry from index
                unset(self::$cacheIndex->$key);
                self::updateCacheIndex();
            }
        }

        return $data;
    }

    private static function readCache($infoObj) {
        if (!is_file($infoObj->cacheFile)) {
            return false;
        }

        if (time() >= $infoObj->decayTime) {
            return false;
        }

        return unserialize(JFile::read($infoObj->cacheFile));
    }

    private static function writeCache($key, $data, $durationMin) {
        $filekey = md5($key);
        $cacheFile = self::$cacheDir . $filekey . '.cache';
        $decayTime = ($durationMin * 60) + time();

        //append infos to index
        $objInfo = (object) array(
                    'cacheFile' => $cacheFile,
                    'fileKey' => $filekey,
                    'decayTime' => $decayTime
        );
        self::$cacheIndex->$key = $objInfo;
        //write index file
        self::updateCacheIndex();
        //write cache file
        JFile::write($cacheFile, serialize($data));
    }

    private static function updateCacheIndex() {
        JFile::write(self::$cacheIndexFile, serialize(self::$cacheIndex));
    }

}
