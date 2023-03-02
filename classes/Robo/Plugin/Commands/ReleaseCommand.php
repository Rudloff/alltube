<?php

namespace Alltube\Robo\Plugin\Commands;

use Robo\Task\Archive\Pack;
use Robo\Task\Base\Exec;
use Robo\Task\Composer\Install;
use Robo\Task\Filesystem\FilesystemStack;
use Robo\Task\Vcs\GitStack;
use Robo\Tasks;

/**
 * Manage robo tasks.
 */
class ReleaseCommand extends Tasks
{
    /**
     * Create release archive
     * @return void
     */
    public function release()
    {
        $this->stopOnFail();

        /** @var Exec $gitTask */
        $gitTask = $this->taskExec('git');
        $result = $gitTask
            ->arg('describe')
            ->run();

        $tmpDir = $this->_tmpDir();

        $filename = 'alltube-' . trim($result->getMessage()) . '.zip';

        /** @var FilesystemStack $rmTask */
        $rmTask = $this->taskFilesystemStack();
        $rmTask->remove($filename)
            ->run();

        /** @var GitStack $gitTask */
        $gitTask = $this->taskGitStack();
        $gitTask->cloneRepo(__DIR__ . '/../../../../', $tmpDir)
            ->run();

        /** @var Install $composerTask */
        $composerTask = $this->taskComposerInstall();
        $composerTask->dir($tmpDir)
            ->optimizeAutoloader()
            ->noDev()
            ->run();

        /** @var Pack $packTask */
        $packTask = $this->taskPack($filename);
        $packTask->addDir('alltube', $tmpDir)
            ->run();
    }
}
