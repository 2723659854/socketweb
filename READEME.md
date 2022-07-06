##框架简介
<p>&nbsp;&nbsp;&nbsp;&nbsp;socketweb是一款常驻内存的轻量级的php框架，遵循常用的mvc架构。</p>

## 目录结构
~~~
|-- app
    |-- admin               <控制层>
        |-- controller
        |-- error
    |-- model               <模型层>
|-- config                  <配置项>
    ...
|-- mysql                 <mysql文件>
    ...
|-- public                  <公共文件>
|-- root                <系统目录，建议不要轻易改动>
    ...                     
|-- vendor                  <外部包>
|-- view                     <视图层>
        ...             
|-- composer.json
|-- README.md
|-- start.php                  <服务启动器>
~~~

## 快速开始

1,导入mysql文件到你的数据库 <br>
2,进入项目根目录:cd /your_project_root_path<br>
3，调试模式:  php start.php start<br>
4,守护进程模式: php start.php start -d<br>
5,重启项目:  php start.php restart<br>
6,停止项目:  php start.php stop<br>
7,项目默认端口为：8082, 你可以自行修改<br>
8,项目访问地址：localhost://127.0.0.1:8082<br>

## 注意
1,原则上本项目只依赖socket，mysqli，redis扩展，如需要第三方扩展，请自行安装。<br>
2,因为是常驻内存，所以每一次修改了php代码后需要重启项目。
## 联系开发者
2723659854@qq.com<br>
171892716@qq.com

