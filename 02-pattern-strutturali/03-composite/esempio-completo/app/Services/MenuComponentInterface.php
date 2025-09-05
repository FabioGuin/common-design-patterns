<?php

namespace App\Services;

interface MenuComponentInterface
{
    /**
     * Ottiene il nome dell'elemento
     *
     * @return string Nome dell'elemento
     */
    public function getName(): string;

    /**
     * Ottiene il prezzo dell'elemento
     *
     * @return float Prezzo dell'elemento
     */
    public function getPrice(): float;

    /**
     * Ottiene la descrizione dell'elemento
     *
     * @return string Descrizione dell'elemento
     */
    public function getDescription(): string;

    /**
     * Verifica se l'elemento è una categoria
     *
     * @return bool True se è una categoria
     */
    public function isCategory(): bool;

    /**
     * Aggiunge un elemento figlio (solo per categorie)
     *
     * @param MenuComponentInterface $component Elemento da aggiungere
     * @return void
     * @throws \Exception Se l'elemento non supporta figli
     */
    public function add(MenuComponentInterface $component): void;

    /**
     * Rimuove un elemento figlio (solo per categorie)
     *
     * @param MenuComponentInterface $component Elemento da rimuovere
     * @return void
     * @throws \Exception Se l'elemento non supporta figli
     */
    public function remove(MenuComponentInterface $component): void;

    /**
     * Ottiene tutti gli elementi figli (solo per categorie)
     *
     * @return array Array di elementi figli
     */
    public function getChildren(): array;

    /**
     * Ottiene il numero totale di elementi (inclusi i figli)
     *
     * @return int Numero totale di elementi
     */
    public function getTotalCount(): int;

    /**
     * Ottiene il prezzo totale (inclusi i figli)
     *
     * @return float Prezzo totale
     */
    public function getTotalPrice(): float;

    /**
     * Cerca un elemento per nome
     *
     * @param string $name Nome da cercare
     * @return MenuComponentInterface|null Elemento trovato o null
     */
    public function findByName(string $name): ?MenuComponentInterface;

    /**
     * Ottiene la rappresentazione in array dell'elemento
     *
     * @return array Array rappresentativo dell'elemento
     */
    public function toArray(): array;
}
