<?php namespace SuperV\ComposerPlugin\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Class DropletInstaller
 *
 * @package Anomaly\StreamsComposerPlugin\Installer
 */
class DropletInstaller extends LibraryInstaller
{
    /**
     * Droplet types
     *
     * @var array
     */
    protected $types = [
        'port',
        'agent',
        'droplet',
        'plugin',
        'theme',
    ];
    
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return file_exists(str_replace('droplets', 'workbench',
                $this->getInstallPath($package))) || parent::isInstalled($repo, $package);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $name = $package->getPrettyName();
        
        list($vendor, $identity) = explode('/', $name);
        
        if (!$vendor || !$identity) {
            throw new \InvalidArgumentException(
                "Invalid package name [{$name}]. Should be in the form of vendor/package"
            );
        }
        
        preg_match($this->getRegex(), $identity, $match);
        
        if (count($match) != 3) {
            throw new \InvalidArgumentException(
                "Invalid droplet package name [{$name}]. Should be in the form of name-type [{$identity}]."
            );
        }
        
        list($name, $type) = explode('-', $identity);
        
        return "droplets/{$vendor}/{$type}s/{$name}";
    }
    
    /**
     * Get regex
     *
     * @return string
     */
    public function getRegex()
    {
        $types = $this->getTypes();
        
        return "/^([a-zA-Z1-9-_]+)-({$types})$/";
    }
    
    /**
     * Get types
     *
     * @return string
     */
    public function getTypes()
    {
        return implode('|', $this->types);
    }
    
    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'superv-droplet' === $packageType;
    }
    
    /**
     * Update is enabled
     *
     * @return mixed|null
     */
    public function updateIsEnabled()
    {
        return $this->composer->getConfig()->get('superv-composer-plugin-update');
    }
    
    /**
     * Do NOT update droplets
     *
     * @param PackageInterface $initial
     * @param PackageInterface $target
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        if (true) {
            parent::update($repo, $initial, $target);
        }
    }
}
