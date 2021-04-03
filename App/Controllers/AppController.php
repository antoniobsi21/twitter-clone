<?php

	namespace App\Controllers;

use App\Models\Usuario;
use MF\Controller\Action;
	use MF\Model\Container;

	class AppController extends Action {

		public function valida_autenticacao() {
			session_start();

			if(!empty($_SESSION["id"]) && !empty($_SESSION["nome"])) {
				return true;
			}
            header("Location: /?login=necessario");
		}

		public function timeline() {
            $this->valida_autenticacao();

			$tweet = Container::getModel("Tweet");

			$tweet->__set("id_usuario", $_SESSION["id"]);
			$tweets = $tweet->getAll();

			$usuario = Container::getModel("usuario");
			$usuario->__set("id", $_SESSION["id"]);
			$perfil = $usuario->getUserProfile();

			$this->view->tweets = $tweets;
			$this->view->perfil = $perfil;

			$this->render("timeline", "layout");
            
		}

		public function tweet() {
			$this->valida_autenticacao();

			$tweet = Container::getModel("Tweet");

			$tweet->__set("tweet", $_POST["tweet"]);
			$tweet->__set("id_usuario", $_SESSION["id"]);

			$tweet->salvar();

			header("Location: /timeline");
		}

		public function quemSeguir() {
			$this->valida_autenticacao();

			$usuarioPesquisado = isset($_GET["usuario"]) ? $_GET["usuario"] : "";			

			if(!empty($usuarioPesquisado)) {
				$usuario = Container::getModel("Usuario");
				$usuario->__set("nome", $usuarioPesquisado);
				$usuario->__set("id", $_SESSION["id"]);
				$usuariosPesquisados = $usuario->getAllLike();
				$this->view->usuariosPesquisados = $usuariosPesquisados;
			}

			$usuario = Container::getModel("usuario");
			$usuario->__set("id", $_SESSION["id"]);
			$perfil = $usuario->getUserProfile();
			
			$this->view->perfil = $perfil;

			$this->render("quemSeguir", "layout");
		}

		public function acao() {
			$this->valida_autenticacao();

			$acao = isset($_GET["acao"]) ? $_GET["acao"] : "";
			$id = isset($_GET["id"]) ? $_GET["id"] : "";

			$usuario = Container::getModel("Usuario");
			$usuario->__set("id", $_SESSION["id"]);

			if($acao == "seguir"){
				$usuario->seguirUsuario($id);

				header("Location: /quem_seguir");
			} else if($acao == "desseguir") {
				$usuario->desseguirUsuario($id);

				header("Location: /quem_seguir");
			} else if($acao == "remover") {
				$tweet = Container::getModel("Tweet");
				$tweet->__set("id", $id);
				$tweet_obj = $tweet->getTweetById();

				if(!empty($tweet_obj) && $tweet_obj["id_usuario"] == $_SESSION["id"]) {
					$tweet->remover();
				}
				header("Location: /timeline");
			}

			
		}
	}

?>