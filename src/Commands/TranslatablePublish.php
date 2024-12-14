<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterTranslatable\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;
use Throwable;

class TranslatablePublish extends BaseCommand
{
    protected $group       = 'Translatable';
    protected $name        = 'translatable:publish';
    protected $description = 'Publish translatable config file into the current application.';

    /**
     * @return void
     */
    public function run(array $params)
    {
        $source = service('autoloader')->getNamespace('Michalsn\\CodeIgniterTranslatable')[0];

        $publisher = new Publisher($source, APPPATH);

        try {
            $publisher->addPaths([
                'Config/Translatable.php',
            ])->merge(false);
        } catch (Throwable $e) {
            $this->showError($e);

            return;
        }

        foreach ($publisher->getPublished() as $file) {
            $publisher->replace(
                $file,
                [
                    'namespace Michalsn\\CodeIgniterTranslatable\\Config' => 'namespace Config',
                    'use CodeIgniter\\Config\\BaseConfig'                 => 'use Michalsn\\CodeIgniterTranslatable\Config\\Translatable as BaseTranslatable',
                    'class Translatable extends BaseConfig'               => 'class Translatable extends BaseTranslatable',
                ]
            );
        }

        CLI::write(CLI::color('  Published! ', 'green') . 'You can customize the configuration by editing the "app/Config/Translatable.php" file.');
    }
}
