<?php

namespace Geekbrains\Application1\Domain\Controllers;

use Geekbrains\Application1\Application\Application;
use Geekbrains\Application1\Application\Render;
use Geekbrains\Application1\Application\Auth;
use Geekbrains\Application1\Domain\Models\User;

class UserController extends AbstractController {

    protected array $actionsPermissions = [
        'actionHash' => ['admin'],
        'actionSave' => ['admin'],
        'actionEdit' => ['admin'],
        'actionIndex' => ['admin'],
        'actionLogout' => ['admin'],
    ];

    public function actionIndex(): string {
        $users = User::getAllUsersFromStorage();

        $render = new Render();

        if(!$users){
            return $render->renderPage(
                'user-empty.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'message' => "Список пуст или не найден"
                ]);
        }
        else{
            return $render->renderPage(
                'user-index.tpl', 
                [
                    'title' => 'Список пользователей в хранилище',
                    'users' => $users
                ]);
        }
    }

    public function actionSave(): string {
        if (empty($_POST['name']) || empty($_POST['lastname'])) {
            throw new \Exception(
                empty($_POST['name']) 
                    ? "Не введено имя пользователя" 
                    : "Не введена фамилия пользователя"
            );
        }
    
        if (User::validateRequestData()) {
            $user = new User();
            $user->setParamsFromRequestData();
            $user->saveToStorage();
    
            $render = new Render();
    
            return $render->renderPage(
                'user-created.tpl',
                [
                    'title' => 'Пользователь создан',
                    'message' => "Создан пользователь " . $user->getUserName() . " " . $user->getUserLastName()
                ]
            );
        } else {
            throw new \Exception("Переданные данные некорректны");
        }
    }

    public function actionDelete(): string {
        if (empty($_GET['user_id'])) {
            throw new \Exception("Не указан ID пользователя для удаления");
        }
    
        if (User::exists($_GET['user_id'])) {
            User::deleteFromStorage($_GET['user_id']);
            header('Location: /user');
            die();
        } else {
            throw new \Exception("Пользователь с ID {$_GET['user_id']} не существует");
        }
    }
    

    public function actionEdit(): string {
        $render = new Render();


        $action = '/user/save';
        if(isset($_GET['user_id'])){
            $userId = $_GET['user_id'];
            $action = '/user/update';
            $userData = User::getUserDataByID($userId);

        }
        
        return $render->renderPageWithForm(
                'user-form.tpl', 
                [
                    'title' => 'Форма создания пользователя',
                    'user_data'=> $userData ?? [],
                    'action' => $action
                ]);
    }

    public function actionUpdate(): string {
        if(User::exists($_POST['user_id'])) {
            $user = new User();
            $user->setUserId($_POST['user_id']);

            $arrayData = [];

            if(isset($_POST['name']))
                $arrayData['user_name'] = $_POST['name'];

            if(isset($_POST['lastname'])) {
                $arrayData['user_lastname'] = $_POST['lastname'];
            }

            $user->updateUser($arrayData);
        }
        else {
            throw new \Exception("Пользователь не существует");
        }

        $render = new Render();
        return $render->renderPage(
            'user-created.tpl',
            [
                'title' => 'Пользователь обновлен',
                'message' => "Обновлен пользователь " . $user->getUserId()
            ]);
    }

    public function actionAuth(): string {
        $render = new Render();
        
        return $render->renderPageWithForm(
                'user-auth.tpl', 
                [
                    'title' => 'Форма логина'
                ]);
    }

    public function actionHash(): string {
        return Auth::getPasswordHash($_GET['pass_string']);
    }

    public function actionLogin(): string {
        $render = new Render();
    
        if (empty($_POST['login']) || empty($_POST['password'])) {
            return $render->renderPageWithForm(
                'user-auth.tpl',
                [
                    'title' => 'Форма логина',
                    'auth-success' => false,
                    'auth-error' => empty($_POST['login']) 
                        ? "Не введен логин" 
                        : "Не введен пароль",
                ]
            );
        }
    
        $result = Application::$auth->proceedAuth($_POST['login'], $_POST['password']);
        if ($result && isset($_POST['user-remember']) && $_POST['user-remember'] == 'remember') {
            $token = Application::$auth->generateToken($_SESSION['auth']['id_user']);
            User::setToken($_SESSION['auth']['id_user'], $token);
        }
    
        if (!$result) {
            return $render->renderPageWithForm(
                'user-auth.tpl',
                [
                    'title' => 'Форма логина',
                    'auth-success' => false,
                    'auth-error' => 'Неверные логин или пароль',
                ]
            );
        } else {
            header('Location: /');
            return "";
        }
    }

    public function actionDeleteAjax(): string {
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (empty($input['user_id'])) {
            return json_encode(['success' => false, 'error' => 'Не указан ID пользователя']);
        }
    
        if (User::exists($input['user_id'])) {
            User::deleteFromStorage($input['user_id']);
            return json_encode(['success' => true]);
        } else {
            return json_encode(['success' => false, 'error' => 'Пользователь не существует']);
        }
    }
    
}