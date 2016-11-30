<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hiject\Console;

use Hiject\Core\Plugin\Base as BasePlugin;
use Hiject\Core\Plugin\Directory;
use Hiject\Core\Plugin\Installer;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Plugin Upgrade command class.
 */
class PluginUpgradeCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('plugin:upgrade')
            ->setDescription('Update all installed plugins');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!Installer::isConfigured()) {
            throw new LogicException('Hiject is not configured to install plugins itself');
        }

        $installer = new Installer($this->container);
        $availablePlugins = Directory::getInstance($this->container)->getAvailablePlugins();

        foreach ($this->pluginLoader->getPlugins() as $installedPlugin) {
            $pluginDetails = $this->getPluginDetails($availablePlugins, $installedPlugin);

            if ($pluginDetails === null) {
                $output->writeln('<error>* Plugin not available in the directory: '.$installedPlugin->getPluginName().'</error>');
            } elseif ($pluginDetails['version'] > $installedPlugin->getPluginVersion()) {
                $output->writeln('<comment>* Updating plugin: '.$installedPlugin->getPluginName().'</comment>');
                $installer->update($pluginDetails['download']);
            } else {
                $output->writeln('<info>* Plugin up to date: '.$installedPlugin->getPluginName().'</info>');
            }
        }
    }

    protected function getPluginDetails(array $availablePlugins, BasePlugin $installedPlugin)
    {
        foreach ($availablePlugins as $availablePlugin) {
            if ($availablePlugin['title'] === $installedPlugin->getPluginName()) {
                return $availablePlugin;
            }
        }
    }
}
