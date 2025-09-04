<?php

namespace App\Traits;

trait Cloneable
{
    public function clone(string $newTitle = null): self
    {
        $clone = clone $this;
        
        // Reset ID e timestamp per nuovo record
        $clone->id = null;
        $clone->created_at = null;
        $clone->updated_at = null;
        
        // Aggiorna il titolo se fornito
        if ($newTitle) {
            $clone->title = $newTitle;
        } else {
            $clone->title = 'Copia di ' . $this->title;
        }
        
        // Reset dello status
        $clone->status = 'draft';
        
        return $clone;
    }

    public function __clone()
    {
        // Clonazione profonda degli array
        if (isset($this->metadata) && is_array($this->metadata)) {
            $this->metadata = array_map(
                fn($item) => is_array($item) ? $item : $item,
                $this->metadata
            );
        }
        
        if (isset($this->settings) && is_array($this->settings)) {
            $this->settings = array_map(
                fn($item) => is_array($item) ? $item : $item,
                $this->settings
            );
        }
        
        if (isset($this->tags) && is_array($this->tags)) {
            $this->tags = array_map(
                fn($item) => is_array($item) ? $item : $item,
                $this->tags
            );
        }
    }
}
