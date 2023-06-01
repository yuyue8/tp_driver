# TpDriver

## 安装
~~~
composer require yuyue8/tp_driver
~~~

## 使用流程

创建 `driver` 类
```
只创建基类
php think make:driver /app/driver/pay

创建基类和策略类
php think make:driver /app/driver/pay ali
```

创建时会默认创建一个跟基础类同名的config文件，可以在文件中设置默认的策略类和其他配置

目录格式为：
```
pay
    -storage
        -Ali.php
    -BasePay.php
    -Pay.php
```

可以在`BasePay`中定义策略类必须实现的方法：
```
abstract public function send(string $phone);
```

使用如下：
使用默认策略类：
```php
$pay = new Pay();
$pay->send('123456');
```

使用其他策略类：
```php
$pay = new Pay('ali');
$pay->send('123456');
```
