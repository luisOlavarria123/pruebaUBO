<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        Categoria::updateOrCreate(['nombre' => 'Infraestructura'], ['activa' => true]);
        Categoria::updateOrCreate(['nombre' => 'Software'], ['activa' => true]);
        Categoria::updateOrCreate(['nombre' => 'Accesos'], ['activa' => true]);
        Categoria::updateOrCreate(['nombre' => 'Legado (descontinuada)'], ['activa' => false]);
    }
}
