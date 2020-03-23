<?php

use Robo\Tasks;
use Symfony\Component\Finder\Finder;

/**
 * Manage robo tasks.
 */
class RoboFile extends Tasks
{

    /**
     * Create release archive
     * @return void
     */
    public function release()
    {
        $result = $this->taskExec('git')
            ->args('describe')
            ->printOutput(false)
            ->run();
        $result->provideOutputdata();
        $tag = $result->getOutputData();

        // We don't want the whole vendor directory.
        $finder = new Finder();
        $finder->files()
            ->in(__DIR__ . '/vendor/')
            ->exclude(
                [
                    'ffmpeg/',
                    'bin/',
                    'anam/phantomjs-linux-x86-binary/',
                    'phpunit/',
                    'squizlabs/',
                    'rinvex/countries/resources/geodata/',
                    'rinvex/countries/resources/flags/'
                ]
            );

        $zipTask = $this->taskPack('alltube-' . $tag . '.zip')
            ->add('index.php')
            ->add('config/config.example.yml')
            ->add('.htaccess')
            ->add('img')
            ->add('LICENSE')
            ->add('README.md')
            ->add('robots.txt')
            ->add('resources')
            ->add('templates')
            ->add('templates_c/')
            ->add('classes')
            ->add('controllers')
            ->add('css')
            ->add('i18n');

        foreach ($finder as $file) {
            if ($path = $file->getRelativePathname()) {
                $zipTask->add('vendor/' . $path);
            }
        }

        $zipTask->run();
    }
}
