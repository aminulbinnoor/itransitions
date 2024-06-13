<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportProductsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_imports_products_from_default_file_correctly()
    {
        $csvContent = <<<CSV
        name,price,stock,discontinued
        Product 1,10,20,false
        Product 2,4,5,false
        Product 3,1001,10,false
        Product 4,50,15,true
        CSV;

        Storage::fake('local');
        Storage::put('stock.csv', $csvContent);

        Artisan::call('import:products', ['--test' => true]);

        $this->assertEquals(2, DB::table('products')->count());
    }

    /** @test */
    public function it_imports_products_correctly()
    {
        $csvContent = <<<CSV
        name,price,stock,discontinued
        Product 1,10,20,false
        Product 2,4,5,false
        Product 3,1001,10,false
        Product 4,50,15,true
        CSV;

        Storage::fake('local');
        Storage::put('stock.csv', $csvContent);

        Artisan::call('import:products', ['file' => storage_path('app/stock.csv'), '--test' => true]);

        $this->assertEquals(2, DB::table('products')->count());
    }

    /** @test */
    public function it_skips_invalid_records()
    {
        $csvContent = <<<CSV
        name,price,stock,discontinued
        ,10,20,false
        Product 2,invalid_price,5,false
        Product 3,1001,10,false
        Product 4,50,15,true
        CSV;

        Storage::fake('local');
        Storage::put('stock.csv', $csvContent);

        Artisan::call('import:products', ['file' => storage_path('app/stock.csv'), '--test' => true]);

        $this->assertEquals(1, DB::table('products')->count());
        // unit test for csv import
    }
}