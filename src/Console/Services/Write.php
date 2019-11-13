<?php

namespace Shibuyakosuke\LaravelCrudGenerator\Console\Services;

trait Write
{
    private function write($file, $buffer)
    {
        if ($this->mkdir($file)) {

            if (file_exists($file) && !$this->command->option('force')) {
                $this->output->writeln(sprintf('SKIP: %s', str_replace(base_path(), '', $file)));
                return;
            }

            file_put_contents($file, $buffer);
            $this->output->writeln(str_replace(base_path(), '', $file));
        }
    }

    private function mkdir($file)
    {
        $pathinfo = pathinfo($file);
        $dirname = $pathinfo['dirname'];
        if (!file_exists($dirname)) {
            return mkdir($dirname, 0777, true);
        }
        return true;
    }
}
