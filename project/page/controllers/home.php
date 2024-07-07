<?php if (!defined('INDEX')) {
    exit('No direct script access allowed');
}

class home extends Controller
{

    public function index()
    {
        $this->render->json(array(
            "status" => "success",
            "timestamp" => date('Y-m-d\TH:i:s\Z'),
        ));
    }
}
