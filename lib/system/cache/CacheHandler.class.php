<?php
namespace dns\system\cache;
use dns\system\cache\builder\ICacheBuilder;
use dns\system\cache\source\DiskCacheSource;
use dns\system\SingletonFactory;

if (!defined('CACHE_SOURCE_TYPE')) define('CACHE_SOURCE_TYPE', 'disk');

/**
 * Manages transparent cache access.
 * 
 * @author	Alexander Ebert, Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category	Community Framework
 */
class CacheHandler extends SingletonFactory {
	/**
	 * cache source object
	 * @var	\dns\system\cache\source\ICacheSource
	 */
	protected $cacheSource = null;
	
	/**
	 * Creates a new CacheHandler object.
	 */
	protected function init() {
		// init cache source object
		try {
			$className = 'dns\system\cache\source\\'.ucfirst(CACHE_SOURCE_TYPE).'CacheSource';
			if (class_exists($className)) {
				$this->cacheSource = new $className();
			}
			else {
				// fallback to disk cache
				$this->cacheSource = new DiskCacheSource();
			}
		}
		catch (\Exception $e) {
			if (CACHE_SOURCE_TYPE != 'disk') {
				// fallback to disk cache
				$this->cacheSource = new DiskCacheSource();
			}
			else {
				throw $e;
			}
		}
	}
	
	/**
	 * Flush cache for given resource.
	 * 
	 * @param	\dns\system\cache\builder\ICacheBuilder		$cacheBuilder
	 * @param	array						$parameters
	 */
	public function flush(ICacheBuilder $cacheBuilder, array $parameters) {
		$this->getCacheSource()->flush($this->getCacheName($cacheBuilder, $parameters), empty($parameters));
	}
	
	/**
	 * Flushes the entire cache.
	 */
	public function flushAll() {
		$this->getCacheSource()->flushAll();
	}
	
	/**
	 * Returns cached value for given resource, false if no cache exists.
	 * 
	 * @param	\dns\system\cache\builder\ICacheBuilder		$cacheBuilder
	 * @param	array						$parameters
	 * @return	mixed
	 */
	public function get(ICacheBuilder $cacheBuilder, array $parameters) {
		return $this->getCacheSource()->get($this->getCacheName($cacheBuilder, $parameters), $cacheBuilder->getMaxLifetime());
	}
	
	/**
	 * Caches a value for given resource,
	 * 
	 * @param	\dns\system\cache\builder\ICacheBuilder		$cacheBuilder
	 * @param	array						$parameters
	 * @param	array						$data
	 */
	public function set(ICacheBuilder $cacheBuilder, array $parameters, array $data) {
		$this->getCacheSource()->set($this->getCacheName($cacheBuilder, $parameters), $data, $cacheBuilder->getMaxLifetime());
	}
	
	/**
	 * Returns cache index hash.
	 * 
	 * @param	array		$parameters
	 * @return	string
	 */
	public function getCacheIndex(array $parameters) {
		return sha1(serialize($this->orderParameters($parameters)));
	}
	
	/**
	 * Builds cache name.
	 * 
	 * @param	\dns\system\cache\builder\ICacheBuilder		$cacheBuilder
	 * @param	array						$parameters
	 * @return	string
	 */
	protected function getCacheName(ICacheBuilder $cacheBuilder, array $parameters = array()) {
		$className = explode('\\', get_class($cacheBuilder));
		$cacheName = str_replace('CacheBuilder', '', array_pop($className));
		if (!empty($parameters)) {
			$cacheName .= '-' . $this->getCacheIndex($parameters);
		}
		
		return ucfirst($cacheName);
	}
	
	/**
	 * Returns the cache source object.
	 * 
	 * @return	\dns\system\cache\source\ICacheSource
	 */
	public function getCacheSource() {
		return $this->cacheSource;
	}
	
	/**
	 * Unifys parameter order, numeric indizes will be discarded.
	 * 
	 * @param	array		$parameters
	 * @return	array
	 */
	protected function orderParameters($parameters) {
		if (!empty($parameters)) {
			array_multisort($parameters);
		}
		
		return $parameters;
	}
}
