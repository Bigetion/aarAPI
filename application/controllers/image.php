<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class image extends Main
{
    public function getAll()
    {
        $post_data = $this->render->json_post();
        $image_path = '';
        $path = 'application/images/featured';
        if (isset($post_data['path'])) {
            $image_path = $post_data['path'] . '/';
            $path = 'application/images/' . $post_data['path'];
            $this->dir->create_dir($path);
        }
        $images = load_recursive($path, 0, array('jpg', 'jpeg', 'png'));

        $data['images'] = array();
        foreach ($images as $image) {
            $image_info = pathinfo($image);
            $data['images'][] = array(
                "name" => $image_info['basename'],
                "url" => base_url . 'image/get/' . $image_path . $image_info['basename'],
                "createdDate" => date("d/m/Y H:i:s", filectime($image)),
            );
        }
        usort($data['images'], function ($a1, $a2) {
            $v1 = $a1['createdDate'];
            $v2 = $a2['createdDate'];
            if ($v1 === $v2) {
                return 0;
            }
            return $v1 < $v2 ? 1 : -1;
        });
        $this->render->json($data);
    }

    public function uploadImage()
    {
        $this->auth->permission();
        $allowedExts = array("jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["image"]["name"]);

        $extension = strtolower(end($temp));
        $path = 'application/images/featured';

        if (isset($_POST['path'])) {
            $path = 'application/images/' . $_POST['path'];
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
        $this->dir->create_dir($path);

        if ((($mime == "image/gif")
            || ($mime == "image/jpeg")
            || ($mime == "image/pjpeg")
            || ($mime == "image/x-png")
            || ($mime == "image/png"))
            && in_array($extension, $allowedExts)) {
            $name = sha1(microtime());

            if (isset($_POST['filename'])) {
                $name = $_POST['filename'];
            }

            $filepath = $path . '/' . $name;
            $file_pattern = "$filepath.*";
            array_map("unlink", glob($file_pattern));
            $filepath = $filepath . "." . $extension;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $filepath)) {
                $this->imageresize->fromFile($filepath)->toFile($filepath, null, 80);
                $this->set->success_message(true);
            }
        }
        $this->set->error_message(true);
    }

    public function deleteImage()
    {
        $this->auth->permission();
        $post_data = $this->render->json_post();
        $path = 'application/images/featured';

        if (isset($post_data['path'])) {
            $path = $post_data['path'];
        }

        if (isset($post_data['path']) && isset($post_data['img'])) {
            $path = 'application/images/' . $post_data['path'];
            $img = $post_data['img'];
            $filename = $path . '/' . $img;
            $directory = dirname($filename);
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $files = scandir($directory);
            $matchingFiles = preg_grep("/^" . preg_quote($basename, '/') . "\.[a-zA-Z0-9]+$/", $files);

            $result = array_values($matchingFiles);
            if (count($result) > 0) {
                $success = array();
                foreach ($result as $file) {
                    if (unlink("$directory/$file")) {
                        $success[] = 1;
                    } else {
                        $success[] = 0;
                    }
                }
                if (!array_search(0, $success)) {
                    $this->set->success_message(true);
                }
            }
        }
        $this->set->error_message(true);
    }

    public function deleteAll()
    {
        $this->auth->permission();
        $post_data = $this->render->json_post();
        $path = '';

        if (isset($post_data['path'])) {
            $path = $post_data['path'];
        }

        if (isset($post_data['path'])) {
            $path = 'application/images/' . $post_data['path'];
            $this->dir->remove_dir($path);
        }
        $this->set->error_message(true);
    }

    public function get()
    {
        $id_image = subsegment(-1);
        $base_path = explode('/', str_replace('://', '', base_url));
        $path = subsegment(count($base_path) + 1, -1);
        if (!empty($path)) {
            $path = 'application/images/' . $path;
        } else {
            $path = 'application/images';
        }

        $fileOut = "application/images/default.png";
        if (file_exists($path . "/default.png")) {
            $fileOut = $path . "/default.png";
        }

        if (is_dir($path)) {
            $images = load_recursive($path, 0, array('jpg', 'jpeg', 'gif', 'png'));
            foreach ($images as $image) {
                $path_info = pathinfo($image);
                $basename = $path_info['basename'];
                $filename = $path_info['filename'];
                if ($filename == $id_image || $basename == $id_image) {
                    $fileOut = $image;
                    break;
                }
            }
        }
        if (isset($_GET['w']) && isset($_GET['h'])) {
            $this->imageresize->fromFile($fileOut)->resize($_GET['w'], $_GET['h'])->toScreen();
        } else if (isset($_GET['w'])) {
            $this->imageresize->fromFile($fileOut)->resize($_GET['w'])->toScreen();
        } else if (isset($_GET['h'])) {
            $this->imageresize->fromFile($fileOut)->resize(false, $_GET['h'])->toScreen();
        }
        $this->render->image($fileOut);
    }

    public function generateBase64($path_string)
    {
        $parse_data = parse_url($path_string);

        $paths = explode("/", $parse_data['path']);
        $path_count = count($paths);

        $new_paths = explode("/", $parse_data['path']);
        array_pop($new_paths);

        $params = array();
        if (isset($parse_data['query'])) {
            parse_str($parse_data['query'], $params);
        }

        $path = implode("/", $new_paths);
        $id_image = "default.png";
        if ($path_count > 0) {
            $id_image = $paths[$path_count - 1];
        }
        if (!empty($path)) {
            $path = 'application/images/' . $path;
        } else {
            $path = 'application/images';
        }

        $fileOut = "application/images/default.png";
        if (file_exists($path . "/default.png")) {
            $fileOut = $path . "/default.png";
        }

        if (is_dir($path)) {
            $images = load_recursive($path, 0, array('jpg', 'jpeg', 'gif', 'png'));
            foreach ($images as $image) {
                $path_info = pathinfo($image);
                $basename = $path_info['basename'];
                $filename = $path_info['filename'];
                if ($filename == $id_image || $basename == $id_image) {
                    $fileOut = $image;
                    break;
                }
            }
        }
        $type = pathinfo($fileOut, PATHINFO_EXTENSION);
        $img = file_get_contents($fileOut);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($img);
        if (isset($_GET['w']) && isset($_GET['h'])) {
            $base64 = $this->imageresize->fromFile($fileOut)->resize($_GET['w'], $_GET['h'])->toDataUri();
        } else if (isset($_GET['w'])) {
            $base64 = $this->imageresize->fromFile($fileOut)->resize($_GET['w'])->toDataUri();
        } else if (isset($_GET['h'])) {
            $base64 = $this->imageresize->fromFile($fileOut)->resize(false, $_GET['h'])->toDataUri();
        }
        return $base64;
    }

    public function getBase64()
    {
        $id_image = subsegment(-1);
        $base_path = explode('/', str_replace('://', '', base_url));
        $path = subsegment(count($base_path) + 1, -1);
        $data = array(
            "base64" => $this->generateBase64("$path/$id_image"),
        );
        $this->render->json($data);
    }

    public function getBase64Array()
    {
        $post_data = $this->render->json_post();
        $base64 = array();

        if (isset($post_data['images'])) {
            if (is_array($post_data['images'])) {
                foreach ($post_data['images'] as $image) {
                    $base64[] = $this->generateBase64($image);
                }
            }
        }
        $data = array("base64" => $base64);

        $this->render->json($data);
    }

}
