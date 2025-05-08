<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Cache;

use RecursiveIteratorIterator,
    RecursiveDirectoryIterator;

/**
 * 文件缓存
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class FileCache extends Cache
{
    /**
     * key前缀
     *
     * @var string
     */
    public $prefix = '';

    /**
     * 缓存存放路径
     *
     * @var string
     */
    protected $cachePath;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config, CacheManager $cacheManager)
    {
        parent::__construct($config, $cacheManager);

        $cachePath = $config['path'] ?? './';
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $this->cachePath = $cachePath;
        $this->prefix    = $config['prefix'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0)
    {
        $cacheFile = $this->filepath($key);
        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0777, true);
        }

        $cache = [];
        if ($expire === 0) {
            $cache['timeout'] = $expire;
        } else {
            $cache['timeout'] = time() + $expire;
        }
        if (is_object($value) || is_resource($value)) {
            $cache['serialize'] = serialize($value);
        } else {
            $cache['value'] = $value;
        }

        $result = file_put_contents($cacheFile,
            "<?php return \n" . var_export($cache, true) . ";\n", LOCK_EX);

        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $cacheFile = $this->filepath($key);
        if (!is_file($cacheFile)) {
            return false;
        }

        $cache = include $cacheFile;
        if ($cache['timeout'] === 0 || $cache['timeout'] > time()) {
            if (isset($cache['value'])) {
                return $cache['value'];
            } else {
                return unserialize($cache['value']);
            }
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key) : bool
    {
        $cacheFile = $this->filepath($key);
        if (!is_file($cacheFile)) {
            return false;
        }
        $cache = include $cacheFile;

        return $cache['timeout'] === 0 || $cache['timeout'] > time();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key) : bool
    {
        $cacheFile = $this->filepath($key);
        if (is_file($cacheFile)) {
            return unlink($cacheFile);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : bool
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cachePath),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            /** @var \SplFileInfo $path */
            if ($path->isFile()) {
                unlink($path->__toString());
            }
        }

        return true;
    }

    /**
     * 获取缓存文件路径
     *
     * @param  string $key
     * @return string
     */
    protected function filepath($key)
    {
        $cacheFile = $this->cachePath . $this->prefix . $key . ".php";

        return $cacheFile;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $offset = 1)
    {
        $value = $this->get($key);
        $value += (int)$offset;
        $result = $this->set($key, $value);

        if (!$result) {
            return false;
        } else {
            return $value;
        }
    }
}
