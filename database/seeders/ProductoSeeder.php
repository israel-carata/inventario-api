<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Datos de ejemplo. Usa updateOrCreate por SKU, así se puede ejecutar
 * en cada despliegue sin duplicar productos.
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            ['sku' => 'TEC-001', 'nombre' => 'Teclado mecánico RGB', 'categoria' => 'Periféricos', 'precio' => 189.90, 'stock' => 25, 'descripcion' => 'Switches rojos, retroiluminación RGB.'],
            ['sku' => 'MOU-002', 'nombre' => 'Mouse inalámbrico', 'categoria' => 'Periféricos', 'precio' => 79.50, 'stock' => 40, 'descripcion' => 'Sensor óptico 16000 DPI.'],
            ['sku' => 'MON-003', 'nombre' => 'Monitor 27" 144Hz', 'categoria' => 'Monitores', 'precio' => 1450.00, 'stock' => 8, 'descripcion' => 'Panel IPS, resolución QHD.'],
            ['sku' => 'AUD-004', 'nombre' => 'Audífonos con micrófono', 'categoria' => 'Audio', 'precio' => 220.00, 'stock' => 3, 'descripcion' => 'Sonido envolvente 7.1.'],
            ['sku' => 'SSD-005', 'nombre' => 'SSD NVMe 1TB', 'categoria' => 'Almacenamiento', 'precio' => 390.00, 'stock' => 15, 'descripcion' => 'Lectura hasta 3500 MB/s.'],
            ['sku' => 'RAM-006', 'nombre' => 'Memoria RAM 16GB DDR4', 'categoria' => 'Componentes', 'precio' => 260.00, 'stock' => 2, 'descripcion' => '3200 MHz, kit 2x8GB.'],
        ];

        foreach ($productos as $p) {
            Producto::updateOrCreate(['sku' => $p['sku']], $p);
        }
    }
}
