<?php
    namespace App\Models;

    use MF\Model\Model;

    class Tweet extends Model {
        private $id;
        private $id_usuario;
        private $tweet;
        private $data;

        public function __get($attr) {
            return $this->$attr;
        }

        public function __set($attr, $val) {
            $this->$attr = $val;
        }

        public function salvar() {
            $query = "insert into tweet(id_usuario, tweet) values(:id_usuario, :tweet)";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":tweet", $this->__get("tweet"));
            $stmt->bindValue(":id_usuario", $this->__get("id_usuario"));
            
            $stmt->execute();

            return $this;
        }

        public function getAll() {
            $query = "SELECT
                t.id, t.id_usuario, u.nome, t.tweet, DATE_FORMAT(t.data, '%d/%m/%y %H:%i') as data
            FROM
                tweet as t
            LEFT JOIN
                usuario as u
            ON
                (t.id_usuario = u.id)
            WHERE
                id_usuario = :id_usuario
                OR t.id_usuario in (SELECT seguido FROM seguidor WHERE seguidor = :id_usuario)
            ORDER BY
                t.data DESC";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":id_usuario", $this->__get("id_usuario"));
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        public function getTweetById() {
            $query = "SELECT * FROM tweet WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":id", $this->__get("id"));
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        public function remover() {
            $query = "DELETE FROM tweet WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":id", $this->__get("id"));
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }

?>