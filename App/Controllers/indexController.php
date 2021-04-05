<?php

	namespace App\Controllers;

	use MF\Controller\Action;
	use MF\Model\Container;

	class IndexController extends Action {

		public function index() {
			$this->view->login = isset($_GET["login"]) ? $_GET["login"] : "";

			session_start();
			if(!empty($_SESSION["id"]) && !empty($_SESSION["nome"])) {
				header("Location: /timeline");
			}

			$this->render("index", "layout");
		}

		public function inscreverse() {
			$this->view->usuario = array(
				"nome" => "",
				"email" => "",
				"senha" => ""
			);
			$this->render("inscreverse", "layout");
		}

		public function registrar() {

			$usuario = Container::getModel("usuario");
			$usuario->__set("nome", $_POST["nome"]);
			$usuario->__set("email", $_POST["email"]);
			$usuario->__set("senha", md5($_POST["senha"]));

			$errors = $usuario->getErrors();
			$usuariosPorEmail = count($usuario->getUsuarioPorEmail());

			if(count($errors) == 0 && $usuariosPorEmail == 0){
				$usuario->salvar();
				$this->render("cadastro", "layout");
			} else {
				$this->view->usuario = array(
					"nome" => $_POST["nome"],
					"email" => $_POST["email"],
					"senha" => $_POST["senha"]
				);
				$this->view->errors = $errors;
				$this->render("inscreverse", "layout");
			}
		}

	}

?>