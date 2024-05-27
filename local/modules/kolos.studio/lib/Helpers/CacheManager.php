<?php

namespace Kolos\Studio\Helpers;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Bitrix\Main\Data\TaggedCache;

class CacheManager
{
    const BASE_CACHE_KEY = 'kolos.studio';
    const BASE_CACHE_PATH = '/kolos.studio/CacheManager/';
    const BASE_CACHE_TTL = 86400;

    private Cache $cacheInstance;
    private int $cacheTtl;
    private string $cacheKey;
    private string $cachePath;

    private TaggedCache $taggedCacheInstance;
    private string $taggedCacheKey;

    public function __construct($cacheKey, $taggedCacheKey = false, $additionalFolder = false)
    {
        $this->cacheInstance = Cache::createInstance();

        $this->cacheTtl = self::BASE_CACHE_TTL;
        $this->cacheKey = self::BASE_CACHE_KEY . '_' . $cacheKey;
        $this->cachePath = self::BASE_CACHE_PATH . ($additionalFolder ?: $cacheKey) . '/';

        $this->taggedCacheInstance = Application::getInstance()->getTaggedCache();
        $this->taggedCacheKey = $taggedCacheKey;
    }

    public function getCache()
    {
        if ($this->cacheInstance->initCache($this->cacheTtl, $this->cacheKey, $this->cachePath)) {
            return $this->cacheInstance->getVars();
        }

        return false;
    }

    public function getCacheWithSaveCallback($callBack)
    {
        $data = $this->getCache();

        if ($data) {
            return $data;
        }

        $this->cacheInstance->startDataCache();

        if ($this->taggedCacheKey) {
            $this->taggedCacheInstance->startTagCache($this->cachePath);
        }

        $data = call_user_func($callBack);

        if ($this->taggedCacheKey) {
            $this->taggedCacheInstance->registerTag($this->taggedCacheKey);
            $this->taggedCacheInstance->endTagCache();
        }

        $this->cacheInstance->endDataCache($data);

        return $data;
    }

    public function getCacheInstance()
    {
        return $this->cacheInstance;
    }

    public static function clearCacheByTag($tag)
    {
        $taggedCache = Application::getInstance()->getTaggedCache();
        $taggedCache->clearByTag($tag);
    }
}
