<?php

namespace Yuyue8\TpDriver;

class Service extends \think\Service
{

    /**
     * 服务启动
     *
     * @return void
     */
    public function boot()
    {
        $this->commands(
            \Yuyue8\TpDriver\command\MakeDriver::class
        );
    }

}
