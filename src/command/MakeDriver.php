<?php

namespace Yuyue8\TpDriver\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\helper\Str;

class MakeDriver extends Command
{
    public function configure()
    {
        $this->setName('make:driver')
        ->addArgument('driver_class', Argument::REQUIRED, "The name of the class")
        ->addArgument('storage_class', Argument::OPTIONAL, "The name of the class")
        ->setDescription('Create a new driver class');
    }

    protected function execute(Input $input, Output $output)
    {
        /**
         * /app/Ceshi
         */
        $classname = trim($input->getArgument('driver_class'));

        /**
         * app/Ceshi
         */
        $classname = ltrim(str_replace('\\', '/', $classname), '/');

        $pathname = $this->getPathName($classname);

        if (is_file($pathname)) {
            $output->writeln('<error>' . 'driver:' . $classname . ' already exists!</error>');
            return false;
        }

        [$namespace, $class] = $this->getNamespaceName($classname);

        $this->createConfig($output, $class, 'config');
        $this->createBaseClass($output, $namespace, $class, 'driver');
        $this->createBaseClass($output, $namespace, $class , 'base_driver');

        $storage_path = $this->app->getRootPath() . $namespace . DIRECTORY_SEPARATOR . Str::snake($class) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR;
        if (!is_dir($storage_path)) {
            mkdir($storage_path, 0755, true);
        }

        $storage_class = trim($input->getArgument('storage_class'));
        if(!empty($storage_class)){
            $this->createClass($output, $namespace, $class, $storage_class , 'class');
        }
    }

    /**
     * 获取文件名和域名空间
     *
     * @param string $classname
     * @return array [namespace, class]
     */
    public function getNamespaceName(string $classname)
    {
        $namespace = trim(implode('/', array_slice(explode('/', $classname), 0, -1)), '/');

        return [
            $namespace,
            str_replace($namespace . '/', '', $classname)
        ];
    }

    public function createBaseClass(Output $output, string $namespace, string $name, string $stub_name)
    {
        $_name = Str::snake($name);

        $name = Str::studly($stub_name == 'base_driver' ? 'base_' . $name : $name);

        $pathname = $this->getPathName($namespace . DIRECTORY_SEPARATOR . $_name . DIRECTORY_SEPARATOR . $name);

        if (is_file($pathname)) {
            return true;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        $stub = file_get_contents($this->getStub($stub_name));

        $namespace = ltrim(str_replace('/', '\\', $namespace), '\\') . '\\' . $_name;
        $namespace2 = str_replace('\\', '\\\\', $namespace);

        file_put_contents($pathname, str_replace(['{%className%}', '{%namespace%}', '{%name%}', '{%namespace2%}'], [
            $name,
            $namespace,
            $_name,
            $namespace2
        ], $stub));

        $output->writeln('<info>' . $name . ' BaseClass created successfully.</info>');
    }

    public function createClass(Output $output, string $namespace, string $name, string $class, string $stub_name)
    {
        $_name = Str::snake($name);

        $base_name = Str::studly('base_' . $name);

        $class = Str::studly($class);

        $namespace = str_replace('/', '\\', $namespace) . DIRECTORY_SEPARATOR . $_name;

        $pathname = $this->getPathName($namespace . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $class);

        if (is_file($pathname)) {
            return true;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        $stub = file_get_contents($this->getStub($stub_name));

        file_put_contents($pathname, str_replace(['{%className%}', '{%namespace%}', '{%base_name%}'], [
            $class,
            $namespace,
            $base_name
        ], $stub));

        $output->writeln('<info>' . $class . ' Class created successfully.</info>');
    }

    public function createConfig(Output $output, string $base_name, string $stub_name)
    {
        $pathname = $this->app->getRootPath() . 'config' . DIRECTORY_SEPARATOR . Str::lower($base_name) . '.php';

        if (is_file($pathname)) {
            return true;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        $stub = file_get_contents($this->getStub($stub_name));

        file_put_contents($pathname, $stub);

        $output->writeln('<info>' . $base_name . ' Config created successfully.</info>');
    }

    /**
     * 获取文件完整路径
     *
     * @param string $classname
     * @return string
     */
    protected function getPathName(string $classname): string
    {
        return $this->app->getRootPath() . $classname . '.php';
    }

    protected function getStub(string $stub_name): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $stub_name . '.stub';
    }
}
