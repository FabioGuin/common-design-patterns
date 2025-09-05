<?php

namespace App\Services;

class DocumentContext
{
    private DocumentTemplate $template;
    private array $extrinsicData;
    private string $id;
    private string $title;
    private string $author;
    private string $createdAt;

    public function __construct(
        DocumentTemplate $template,
        array $extrinsicData,
        string $id,
        string $title,
        string $author
    ) {
        $this->template = $template;
        $this->extrinsicData = $extrinsicData;
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->createdAt = now()->toISOString();
    }

    /**
     * Renderizza il documento
     */
    public function render(): string
    {
        \Log::info('Rendering document context', [
            'document_id' => $this->id,
            'template' => $this->template->getName(),
            'data_keys' => array_keys($this->extrinsicData)
        ]);

        // Aggiunge dati comuni a tutti i documenti
        $renderData = array_merge($this->extrinsicData, [
            'title' => $this->title,
            'author' => $this->author,
            'date' => $this->createdAt,
            'document_id' => $this->id,
        ]);

        return $this->template->render($renderData);
    }

    /**
     * Ottiene l'ID del documento
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Ottiene il titolo del documento
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Ottiene l'autore del documento
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Ottiene la data di creazione
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * Ottiene il template utilizzato
     */
    public function getTemplate(): DocumentTemplate
    {
        return $this->template;
    }

    /**
     * Ottiene i dati estrinseci
     */
    public function getExtrinsicData(): array
    {
        return $this->extrinsicData;
    }

    /**
     * Aggiorna i dati estrinseci
     */
    public function updateExtrinsicData(array $data): void
    {
        $this->extrinsicData = array_merge($this->extrinsicData, $data);
    }

    /**
     * Ottiene le informazioni del documento
     */
    public function getInfo(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'created_at' => $this->createdAt,
            'template' => $this->template->getInfo(),
            'extrinsic_data_keys' => array_keys($this->extrinsicData),
        ];
    }

    /**
     * Ottiene il contenuto del documento come array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'created_at' => $this->createdAt,
            'template_name' => $this->template->getName(),
            'template_layout' => $this->template->getLayout(),
            'template_style' => $this->template->getStyle(),
            'extrinsic_data' => $this->extrinsicData,
            'rendered_content' => $this->render(),
        ];
    }

    /**
     * Verifica se il documento contiene una chiave specifica
     */
    public function hasData(string $key): bool
    {
        return array_key_exists($key, $this->extrinsicData);
    }

    /**
     * Ottiene un valore specifico dai dati estrinseci
     */
    public function getData(string $key, $default = null)
    {
        return $this->extrinsicData[$key] ?? $default;
    }

    /**
     * Imposta un valore specifico nei dati estrinseci
     */
    public function setData(string $key, $value): void
    {
        $this->extrinsicData[$key] = $value;
    }

    /**
     * Rimuove una chiave dai dati estrinseci
     */
    public function removeData(string $key): void
    {
        unset($this->extrinsicData[$key]);
    }

    /**
     * Ottiene il numero di chiavi nei dati estrinseci
     */
    public function getDataCount(): int
    {
        return count($this->extrinsicData);
    }

    /**
     * Verifica se il documento è vuoto
     */
    public function isEmpty(): bool
    {
        return empty($this->extrinsicData);
    }
}
