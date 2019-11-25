<?php
namespace core;

class app
{
    protected $db;
    protected $cfg;
    protected $request;
    protected $start_time;
    protected $class_load_count = 0;
    protected $router;

    static private $instance;

    protected function __construct()
    {
        $this->start_time = explode(' ', microtime());

        spl_autoload_register([$this, 'autoload']);

        require BASE_PATH . DS . 'cfg.php';

        $this->db = $db = new db;
        $this->cfg = $db->cfg = $cfg;
        $db->connect();

        mb_internal_encoding( $cfg['db_encoding'] );
    }

    public function getSecretKey()
    {
        return $this->cfg['secret_key'];
    }

    public function getDB()
    {
        return $this->db;
    }

    public function __destruct()
    {
        spl_autoload_unregister([$this, 'autoload']);
    }

    public function autoload($class)
    {
        $path = BASE_PATH . DS . strtr($class, '\\', DS) . '.php';

        if(file_exists($path))
        {
            require_once $path;

            $this->class_load_count++;
        }
        else {
            throw new \Exception($path.' not found');
        }
    }

    static public function instance()
    {
        if(!self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function run()
    {
        $this->router = new \routes();
        $this->router->setRoutes();

        $request = $this->router->find();

        if (!$request) {
              $this->sendNotFound();
        }
        else {
            $this->dispatch($request);
        }
    }

    private function dispatch($request)
    {
        if (is_callable($request->getCallback())) {
            call_user_func($request->getCallback(), $this->router->getParams());
        }
        else {
            $this->dispatchController($request);
        }
    }

    private function dispatchController($request)
    {
        $segments = explode('/', $request->getCallback());
        $class = 'controllers\\' . $segments[0];

        if (class_exists($class)) {
            $controller = new $class(
                $this->router->getParams(),
                $request->getAccess()
            );
        }
        else {
			throw new \Exception($class.' not found');
        }

        $action = isset($segments[1]) ? $segments[1] : false;
        $controller->dispatch($action);
    }

    private function sendNotFound()
    {
        $controller = new controller();
        $controller->dispatch('NotFound');
	}

    public function uptime()
    {
        $end = explode(' ', microtime());
        return sprintf('%0.06f', ($end[0] - $this->start_time[0]) + ($end[1] - $this->start_time[1]));
    }
}