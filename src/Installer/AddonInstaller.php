<?php namespace SuperV\ComposerPlugin\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class AddonInstaller extends LibraryInstaller
{
    /**
     * Droplet types
     *
     * @var array
     */
    protected $types = [
        'addon',
        'agent',
        'module',
        'plugin',
        'resource',
        'theme',
    ];

    protected function isUnderDevelopment(PackageInterface $package)
    {
        $path = str_replace('addons', 'workbench', $this->getInstallPath($package));

        return file_exists($path);
    }

    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return $this->isUnderDevelopment($package) || parent::isInstalled($repo, $package);
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if ($this->isUnderDevelopment($package)) {
            return;
        }

        parent::install($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $name = $package->getPrettyName();

        list($vendor, $identity) = explode('/', $name);

        if (! $vendor || ! $identity) {
            throw new \InvalidArgumentException(
                "Invalid package name [{$name}]. Should be in the form of vendor/package"
            );
        }

        preg_match($this->getRegex(), $identity, $match);

        if (count($match) != 3) {
            throw new \InvalidArgumentException(
                "Invalid addon package name [{$name}]. Should be in the form of name-type [{$identity}]."
            );
        }

        list($name, $type) = explode('-', $identity);

        $vendorPath = "{$vendor}/{$type}s/{$name}";

        /**
         * if package already exists in workbench folder,
         * that means it is under development, so we
         * should return this path
         */
//        if (file_exists("workbench/{$vendorPath}")) {
//            return "workbench/{$vendorPath}";
//        }

        return "addons/{$vendorPath}";
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
        return 'superv-addon' === $packageType;
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
     * Do NOT update addons
     *
     * @param PackageInterface $initial
     * @param PackageInterface $target
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        if ($this->isUnderDevelopment($initial)) {
            return;
        }
        parent::update($repo, $initial, $target);
    }
}
