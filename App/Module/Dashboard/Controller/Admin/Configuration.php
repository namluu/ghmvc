<?php
namespace App\Module\Dashboard\Controller\Admin;
use App\Config;
use Core\Controller;
use Core\View;
use App\Module\Dashboard\Model\Configuration as ConfigurationModel;

class Configuration extends Controller
{
    protected $configurationModel;
    protected $session;
    protected $config;

    public function __construct(
        array $routeParams,
        ConfigurationModel $configurationModel,
        Config $config
    ) {
        $this->config = $config;
        $this->configurationModel = $configurationModel;
        $this->session = $this->getSession();
        parent::__construct($routeParams);
    }

    public function indexAction()
    {
        if (Config::$config) {
            $data = Config::$config;
        } else {
            $data = $this->configurationModel->getAll();
        }

        View::renderTemplate('Dashboard::backend/configuration/index.html', [
            'data' => $data
        ]);
    }

    public function saveAction()
    {
        if ($_POST) {
            $errorMsg = $this->validateData($_POST);
            if ($errorMsg) {
                $this->session->setMessage('error', implode(', ', $errorMsg));
            } else {
                $this->saveDataChanged($_POST);
                $this->config->loadConfig();
                $this->session->setMessage('success', 'Save successfully');
            }
        }
        $this->redirect(\App\Helper::getAdminUrl('dashboard/configuration'));
    }

    protected function validateData($data)
    {
        $msg = array();
        foreach ($data as $key => $value) {
            if (!$value) {
                $msg[] = 'Missing ' . $key;
            }
        }
        return $msg;
    }

    protected function saveDataChanged($data)
    {
        $originData = \App\Config::$config;
        foreach ($originData as $origin) {
            $id = $origin->id;
            if ($origin->value != $data[$id]) {
                $this->configurationModel->save(['value' => $data[$id]], $id);
            }
        }
    }
}