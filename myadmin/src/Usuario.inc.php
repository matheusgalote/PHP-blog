<?php


class Usuario {
    public $cd_usuario;
    public $ds_usuario_nome;
    public $ds_usuario_email;

    public function setCd_usuario($param) {
        $this->cd_usuario = $param;
    } 

    public function getCd_usuario() {
        return $this->cd_usuario;
    } 

    public function setDs_usuario_nome($param) {
        $this->ds_usuario_nome = $param;
    } 

    public function getDs_usuario_nome() {
        return $this->ds_usuario_nome;
    } 

    public function setDs_usuario_email($param) {
        $this->ds_usuario_email = $param;
    } 

    public function getDs_usuario_email() {
        return $this->ds_usuario_email;
    } 
}

class UsuarioDAO extends Aedra {
}