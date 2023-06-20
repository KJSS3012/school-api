<?php

class Anao
{
    public string $nome;
    public string $tipo;
    public int $idade;
    public float $preco;
    public bool $isVendido;

    public function __construct(string $nome, string $tipo, int $idade, float $preco = 0, bool $isVendido = false)
    {
        $this->nome = $nome;
        $this->tipo = $tipo;
        $this->idade = $idade;
        $this->preco = $preco;
        $this->isVendido = $isVendido;
    }
}
