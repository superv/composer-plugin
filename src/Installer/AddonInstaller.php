<?php namespace SuperV\ComposerPlugin\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class AddonInstaller extends LibraryInstaller
{
    /**
     * Addon types
     *
     * @var array
     */
    protected $types = [
        'addon',
        'drop',
        'agent',
        'module',
        'plugin',
        'resource',
        'theme',
    ];

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

        $type = $package->getType();
        if (! preg_match('/^superv-([\w\-]+)$/', $type, $match)) {
            throw new \InvalidArgumentException(
                "Invalid superV package type [{$type}]. Type should be in the form of superv-{type}."
            );
        }

        $type = $match[1];

        if ($type === 'tool') {
            return sprintf("tools/%s/%s", $vendor, $identity);
        }

        $pluralType = $type.'s';

        return sprintf("addons/%s/%s/%s", $vendor, $pluralType, $identity);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return preg_match('/^superv-([\w\-]+)$/', $packageType);
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
     * Update is enabled
     *
     * @return mixed|null
     */
    public function updateIsEnabled()
    {
        return $this->composer->getConfig()->get('superv-composer-plugin-update');
    }

    protected function isUnderDevelopment(PackageInterface $package)
    {
        $path = str_replace('addons', 'workbench', $this->getInstallPath($package));

        return file_exists($path);
    }
}
