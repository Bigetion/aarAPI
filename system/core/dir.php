<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class Dir
{
    public function delete_file($file_path)
    {
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    public function get_dir($path)
    {
        $results = scandir($path);
        $dir = array();
        foreach ($results as $result) {
            if ($result === '.' or $result === '..') {
                continue;
            }

            if (is_dir($path . '/' . $result)) {
                //code to use if directory
                $dir[] = $result;
            }
        }
        return $dir;
    }

    public function remove_dir($folder)
    {
        $files = glob($folder . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($file)) {
                $this->remove_dir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($folder);
    }

    public function create_dir($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

    }

}
