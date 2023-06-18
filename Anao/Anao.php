<?php

class Anao
{
    private string $nome;
    private string $tipo;
    private int $idade;
    private float $preco;
    private bool $isVendido;

    public function __construct(string $nome, string $tipo, int $idade, float $preco = 0, bool $isVendido = false)
    {
        $this->nome = $nome;
        $this->tipo = $tipo;
        $this->idade = $idade;
        $this->preco = $preco;
        $this->isVendido = $isVendido;
    }


    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function getIdade(): int
    {
        return $this->idade;
    }

    public function setIdade(int $idade): void
    {
        $this->idade = $idade;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function setPreco(float $preco): void
    {
        $this->preco = $preco;
    }

    public function isVendido(): bool
    {
        return $this->isVendido;
    }

    public function setVendido(bool $isVendido): void
    {
        $this->isVendido = $isVendido;
    }
}
