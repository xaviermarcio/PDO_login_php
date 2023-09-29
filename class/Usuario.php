<?php
require_once('Crud.php');

class Usuario extends Crud{
    protected string $tabela = 'usuarios';

    function __construct(
        public string $nome,
        private string $email,
        private string $senha,
        private string $repete_senha="",
        private string $recupera_senha="",
        private string $token="",
        private string $codigo_confirmacao="",
        private string $status="",
        public array $erro=[]
    ){}

    public function set_repeticao($repete_senha){
        $this->repete_senha = $repete_senha;
    }

    public function validar_cadastro(){

        //VALIDAÇÃO DO NOME
        if (!preg_match("/^[A-Za-záàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ'\s]+$/",$this->nome)) {
           $this->erro["erro_nome"] = "Por favor informe um nome válido!";
        }

        //VERIFICAR SE EMAIL É VÁLIDO
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            $this->erro["erro_email"] = "Formato de e-mail inválido!";
        }

        //VERIFICAR SE SENHA TEM MAIS DE 6 DÍGITOS
        if(strlen($this->senha) < 6){
            $this->erro["erro_senha"] = "Senha deve ter 6 caracteres ou mais!";
        }

        if($this->senha !== $this->repete_senha){
            $this->erro["erro_repete"] = "Senha e repetição de senha diferentes!";
        }

    }

    public function insert(){
        //VERIFICAR SE ESTE EMAIL JÁ ESTÁ CADASTRADO NO BANCO
        $sql = "SELECT * FROM usuarios WHERE email=? LIMIT 1";
        $sql = DB::prepare($sql);
        $sql->execute(array($this->email));
        $usuario = $sql->fetch();
        //SE NÃO EXISTIR O USUARIO - ADICIONAR NO BANCO
        if (!$usuario){
            $data_cadastro = date('d/m/Y');
            $senha_cripto = sha1($this->senha);
            $sql = "INSERT INTO $this->tabela VALUES (null,?,?,?,?,?,?,?,?)";
            $sql = DB::prepare($sql);
            return $sql->execute(array($this->nome,$this->email,$senha_cripto,$this->recupera_senha,$this->token,$this->codigo_confirmacao,$this->status,$data_cadastro));
        }else{
            $this->erro["erro_geral"] = "Usuário já cadastrado!";
        }
    }

    public function update($id){
        $sql = "UPDATE $this->tabela SET token=? WHERE id=?";
        $sql = DB::prepare($sql);
        return $sql->execute(array($token,$id));
    }

}