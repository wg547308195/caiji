<?php
namespace app\common\behavior;

class Common
{

    public function run($params)
    {
        $dirs = scandir(APP_PATH);
        foreach ($dirs as $name) {
            $path = APP_PATH.$name;
            if ($name === '.' or $name === '..' or $name === 'common' or !is_dir($path)) continue;
            $file = $path. DIRECTORY_SEPARATOR .'hook.php';
            if(is_file($file) and file_exists($file)) \Hook::import(include $file);
        }
    }

}
