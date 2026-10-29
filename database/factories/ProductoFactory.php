<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'nombre' => ucfirst(fake()->words(3, true)),
            'descripcion' => fake()->sentence(),
            'categoria' => fake()->randomElement(['Periféricos', 'Monitores', 'Audio', 'Componentes']),
            'precio' => fake()->randomFloat(2, 10, 2000),
            'stock' => fake()->numberBetween(0, 100),
            'activo' => true,
        ];
    }
}
