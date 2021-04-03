<?php
    namespace App\Models;

    use MF\Model\Model;

    class Usuario extends Model {
        private $id;
        private $nome;
        private $email;
        private $senha;

        public function __get($attr) {
            return $this->$attr;
        }

        public function __set($attr, $val) {
            $this->$attr = $val;
        }

        public function salvar() {
            $query = "insert into usuario(nome, email, senha) values(:nome, :email, :senha)";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(":nome", $this->__get("nome"));
            $stmt->bindValue(":email", $this->__get("email"));
            $stmt->bindValue(":senha", $this->__get("senha")); //md5();

            $stmt->execute();

            return $this;
        }

        public function getErrors() {
            $errors = array();

            if(strlen($this->__get("nome")) < 3) {
                $errors["nome"][] = "Nome muito curto.";
            }

            if(strlen($this->__get("email")) < 10) {
                $errors["email"][] = "Email muito curto.";
            }
            if(!str_contains($this->__get("email"), "@"))
                $errors["email"][] = "Email inválido.";

            if(strlen($this->__get("senha")) < 5) {
                $errors["senha"][] = "Senha muito curta.";
            }
            
            return $errors;
        }

        public function getUsuarioPorEmail() {
            $query = "select nome, email from usuario where email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":email", $this->__get("email"));
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        public function autenticar() {
            $query = "select id, nome, email from usuario where email=:email and senha=:senha";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":email", $this->__get("email"));
            $stmt->bindValue(":senha", $this->__get("senha"));

            $stmt->execute();

            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if(!empty($usuario["id"]) && !empty($usuario["nome"])){
                $this->__set("nome", $usuario["nome"]);
                $this->__set("id", $usuario["id"]);
            }
            return $this;
        }

        public function getAllLike() {
            
            $query = "SELECT
                        u.id, u.nome, (
                            SELECT
                                count(*)
                            from
                                seguidor as s
                            WHERE
                                s.seguidor = :id_usuario AND
                                s.seguido = u.id
                            LIMIT 1
                            ) as seguido
                            
                    FROM
                        usuario as u
                    WHERE
                        u.nome like :nome AND u.id != :id_usuario";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":nome", "%" . $this->__get("nome") . "%");
            $stmt->bindValue(":id_usuario", $this->__get("id"));
            $stmt->execute();

            $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $usuarios;
        }

        public function desseguirUsuario($id) {
            $query = "DELETE FROM seguidor WHERE seguidor = :seguidor AND seguido = :seguido";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":seguidor", $this->__get("id"));
            $stmt->bindValue(":seguido", $id);

            $stmt->execute();                
            
            return $this;
        }

        public function seguirUsuario($id) {
            $query = "select seguidor, seguido from seguidor where seguidor = :seguidor and seguido = :seguido";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":seguidor", $this->__get("id"));
            $stmt->bindValue(":seguido", $id);
            $stmt->execute();

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if(count($result) == 0){
                $query = "INSERT INTO seguidor(seguidor, seguido) VALUES (:seguidor, :seguido)";
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(":seguidor", $this->__get("id"));
                $stmt->bindValue(":seguido", $id);

                $stmt->execute();                
            } 
            return $this;
        }

        public function getUserProfile() {
            $query = "SELECT
                        u.nome,
                        (SELECT count(s.seguidor) from SEGUIDOR as s WHERE s.seguido = :id_usuario) as seguidores,
                        (SELECT count(s.seguidor) from SEGUIDOR as s WHERE s.seguidor = :id_usuario) as seguindo,
                        (SELECT count(t.id) from tweet as t WHERE t.id_usuario = :id_usuario) as tweets
                    FROM
                        usuario as u
                    WHERE
                        u.id = :id_usuario;";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":id_usuario", $this->__get("id"));
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

    }

?>