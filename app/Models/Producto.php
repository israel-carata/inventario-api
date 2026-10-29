<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /** @use HasFactory<\Database\Factories\ProductoFactory> */
    use HasFactory;

    /** Campos que se pueden asignar en masa (create / update). */
    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'categoria',
        'precio',
        'stock',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'stock' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /** Filtro de búsqueda por nombre, SKU o descripción: ?buscar=texto */
    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (! $texto) {
            return $query;
        }

        $like = '%'.mb_strtolower($texto).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->whereRaw('LOWER(nombre) LIKE ?', [$like])
                ->orWhereRaw('LOWER(sku) LIKE ?', [$like])
                ->orWhereRaw('LOWER(descripcion) LIKE ?', [$like]);
        });
    }
}
