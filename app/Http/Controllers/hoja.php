<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\EconomicActivity;
use App\Models\Municipality;
use Exception;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\hoja1;
use App\Models\Inventory;
use App\Models\Kardex;
use App\Models\Marca;
use App\Models\Price;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class hoja extends Controller
{
    //
    public function ejecutar()
    {
        set_time_limit(0);
        $products = Product::all();
        foreach ($products as $producto) {
            $inventario = new Inventory();
            $inventario->product_id = $producto->id;
            $inventario->branch_id = 12;
            $cost = $producto->costo ?? 0; // Si $producto->cost es null, asigna 0
            $inventario->cost_without_taxes = $cost;
            $inventario->cost_with_taxes = $cost > 0 ? $cost * 1.13 : 0; // Evita multiplicar si es 0

//                    $stock = ($producto->unidades_presentacion * $oldInventory->saldo_caja) + $oldInventory->saldo_fraccion + $oldInventory->bonificables;
            $stock = $producto->stcok ?? 0;

            $inventario->stock = $stock;
            $inventario->stock_min = $producto->E_minimo ?? 0;
            $inventario->stock_max = $producto->E_maximo ?? 0;
            $inventario->is_stock_alert = true;
            $inventario->is_expiration_date = false;
            $inventario->is_active = true;
            if ($inventario->save()) {
                $precioDetalle = new Price();
                $precioDetalle->inventory_id = $inventario->id;
                $precioDetalle->name = 'Público';
                $ivaDetalle = 0;
                $precioDetalle->price = 0;
                $precioDetalle->utilidad = 0;
                $precioDetalle->is_default = true;
                $precioDetalle->is_active = true;
                $precioDetalle->save();
            } else {
                dd('no guardo');
            }
        }
        dd('listo');
//            // Buscar en storage/app/public/uploads/{id}.*
//            $archivos = Storage::disk('public')->files("uploads");
//
//            // Filtrar el archivo que coincida con el ID
//            $archivoOrigen = collect($archivos)->first(function ($file) use ($product) {
//                return pathinfo($file, PATHINFO_FILENAME) == $product->id;
//            });
//
//            if ($archivoOrigen) {
//                $ext = pathinfo($archivoOrigen, PATHINFO_EXTENSION);
//                $archivoDestino = "products/{$product->id}.{$ext}";
//
//                // Copiar archivo dentro de storage/app/public/products
//                Storage::disk('public')->copy($archivoOrigen, $archivoDestino);
//
//                // Actualizar en DB
//                $product->images = $archivoDestino;
//                $product->save();
//            }
//        }
//
//
//
//        dd("Imagenes migradas");
        //limpiar las tablas
//        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//        Customer::truncate();
//        Municipality::truncate();
//
//        //customer
//        $clientes = DB::connection('mariadb')->table('customer_migrate')->get();
//        foreach ($clientes as $oldCliente) {
//            $cliente = new Customer();
//            $cliente->id = $oldCliente->ID_CLIENTE;
//            $cliente->name = $oldCliente->NOMBRE;
//            $cliente->last_name = $oldCliente->APELLIDO;
//            $cliente->person_type_id = (empty($oldCliente->TIPO_CLIENTE) || is_null($oldCliente->TIPO_CLIENTE)
//                ? 1
//                : (strtolower($oldCliente->TIPO_CLIENTE) === 'natural' ? 1 : 2)
//            );
//            $cliente->document_type_id = (empty($oldCliente->TIPO_DOCUMENTO) || is_null($oldCliente->TIPO_DOCUMENTO)
//                ? 1
//                : (strtolower($oldCliente->TIPO_CLIENTE) === '36' ? 1 : 2)//sI ES DUI
//            );
//
//            $cliente->email = $oldCliente->CORREO;
//            $cliente->phone = $oldCliente->TELEFONO!='NULL' ?'(503)' . $oldCliente->TELEFONO:null;
//            $country = Country::where('name', 'like', '%' . trim($oldCliente->PAIS) . '%')->first();
//            $cliente->country_id = $country->id ?? 1;
//
//            $dte_departamento = str_pad($oldCliente->dte_departamento, 2, '0', STR_PAD_LEFT);
//            $departamento = Departamento::where('code', '=', trim($dte_departamento))->first();
//
//            if(!$departamento){
//                Log::error('Departamento no encontrado', ['code' => $dte_departamento, 'cliente_id' => $cliente->id]);
//            }
//
//            $cliente->departamento_id = $departamento->id ?? 1;
//
//            $codfilter_municipio = str_pad($oldCliente->dte_municipio, 2, '0', STR_PAD_LEFT);
//            $municipio = Distrito::where('code', '=', trim($codfilter_municipio))
//                ->where('departamento_id',$departamento->id)->first();
//
//            if(!$municipio){
//                Log::error('Municipio no encontrado', ['code' => $codfilter_municipio, 'cliente_id' => $cliente->id]);
//
//                //si no existe el municipio lo creamos
//                if (!empty($departamento) && ($departamento->id ?? 0) > 0) {
//
//                    $nuevoMunicipio=new Distrito();
//                    $nuevoMunicipio->code=$codfilter_municipio;
//                    $nuevoMunicipio->name=$oldCliente->DTE_MUNICIPIO1??'SIN NOMBRE';
//                    $nuevoMunicipio->departamento_id=$departamento->id;
//                    $nuevoMunicipio->save();
//                    $municipio=$nuevoMunicipio;
//                }
//            }
//
//            $cliente->distrito_id = $municipio->id?? 1;
////            dd($municipio,$codfilter_municipio,$departamento->id);
//            $municipio_filter=$oldCliente->DTE_MUNICIPIO2;
//            $distrito_id_registro=1;
//            if($municipio_filter){
//                $distrito = Municipality::where('name', '=', trim($municipio_filter))
//                    ->where('distrito_id',$municipio->id)->first();
//                if($distrito){
//                    $distrito_id_registro=$distrito->id;
//                }else{
//                    Log::error('Distrito no encontrado', ['code' => $codfilter_municipio, 'cliente_id' => $cliente->id]);
//
//                    if (!empty($municipio) && ($municipio->id ?? 0) > 0) {
//
//                        $nuevoDistrito=new Municipality();
//                        $nuevoDistrito->code='00';
//                        $nuevoDistrito->name=$municipio_filter;
//                        $nuevoDistrito->distrito_id=$municipio->id;
//                        $nuevoDistrito->is_active=true;
//                        $nuevoDistrito->save();
//                        $distrito_id_registro=$nuevoDistrito->id;
//                    }
//
//                }
//            }
//            $cliente->municipio_id = $distrito_id_registro;
//
//            $cliente->nrc = $oldCliente->nrc;
//            $cliente->nit = $oldCliente->nit;
//            $cliente->dui = $oldCliente->dui;
//            $cliente->is_taxed = true;
//            $cliente->is_active = true;
//            $cliente->wherehouse_id = 12;
//            $cliente->is_taxed = $oldCliente->exento_iva ? false : true;
//
//            $filter_economic_activity = trim($oldCliente->Cod_MH);
//            $actividad_economica=EconomicActivity::where('code', '=', trim($filter_economic_activity))->first();
//            $id_actividad_economica= $actividad_economica ? $actividad_economica->id : 773;
//            if(!$actividad_economica){
//                Log::error('Actividad económica no encontrada', ['code' => $filter_economic_activity, 'cliente_id' => $cliente->id]);
//            }
//
//
//            $cliente->economicactivity_id =$id_actividad_economica;
//
//            $cliente->is_credit_client = $oldCliente->plazo > 0 ? true : false;
//            $cliente->credit_limit = $oldCliente->limitecredito;
//            $cliente->credit_days = $oldCliente->plazo;
//            $cliente->wholesale_price = $oldCliente->cliente_mayoreo == 2;
//            $cliente->is_isr_applicable = $oldCliente->sin_retencion==1;
//            $cliente->address = $oldCliente->COMP_DIRECCION ;
//            $cliente->save();
//
//        }
//        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
//
//        dd('Clientes');
//        Provider::truncate();
//
//        $providers=DB::connection('sqlsrv')->table('Proveedores')->get();
//        foreach ($providers as $provider){
//            $providerNew = new Provider();
//
////            $providerNew->id = $provider->id_proveedor; // Asignando el ID manualmente si es necesario
//            $providerNew->legal_name = strtoupper($provider->Proveedor);
//            $providerNew->comercial_name =strtoupper($provider->Proveedor);
//            $providerNew->country_id = 1;
//            $providerNew->department_id =1;
//            $providerNew->municipility_id = 1;
//            $providerNew->distrito_id = 1;
//            $providerNew->direction = strtoupper($provider->Direccion);
//            $providerNew->phone_one = $provider->Telefono1;
//            $providerNew->phone_two = $provider->Telefono2;
//            $providerNew->email = null;
//            $providerNew->nrc = $provider->Nrc;
//            $providerNew->nit = $provider->Nit;
//            $providerNew->economic_activity_id = 1;
//            $providerNew->condition_payment =1;
//            $providerNew->credit_days =$provider->Plazo_credito;
//            $providerNew->credit_limit =$provider->Limite_credito;
//            $providerNew->balance =$provider->Balance;
//            $providerNew->provider_type = 1;
//            $providerNew->is_active = true;
//            $providerNew->contact_seller = strtoupper($provider->Contacto??'');
//            $providerNew->phone_seller = null;
//            $providerNew->email_seller = null;
//            $providerNew->last_purchase = null;
//            $providerNew->purchase_decimals = 2;
//            $providerNew->save();
//
//
//        }
//
//
//dd('Clinte y proveedores');
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');


        Product::truncate();
        Marca::truncate();
        Category::truncate();
        Price::truncate();
        Inventory::truncate();
        Kardex::truncate();

//        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
//        $categorias = DB::connection('sqlsrv')->table('Lineas')->get();
//        foreach ($categorias as $category) {
//            $newCategory = new Category();
//            $newCategory->id = $category->Id;
//            $newCategory->name = trim($category->Nombre);
//            $newCategory->is_active = true;
//            $newCategory->save();
//        }
//
//        dd('Cater');
//        $brands = DB::connection('sqlsrv')->table('Marcas')->get();
//        foreach ($brands as $brand) {
//            $newBrand = new Marca();
//            $newBrand->id = $brand->Id;
//            $newBrand->nombre = trim($brand->Marca);
//            $newBrand->descripcion = trim($brand->Marca);
//            $newBrand->estado = true;
//            $newBrand->save();
//        }
//        dd('Marcas');
        $products = DB::connection('mariadb')->table('migrar_inv')->get();
//        return response()->json($products);
//        dd($products);
        $lineasNuevas = 0;
        $marcasNuevas = 0;
        $productosNuevos = 0;
        $inventarioNuevo = 0;
        $productosNoCrados = [];
        $inventariosNoCreados = [];
        foreach ($products as $producto) {

            $existProducto = Product::where('id', trim($producto->id))->first();
            if ($existProducto) {
                //si el producto ya existe no lo creamos buscamos si existe en el inventario
                $existeInventario = Inventory::where('product_id', $producto->id)
                    ->where('branch_id', $producto->sucursal_id)
                    ->first();
                if (!$existeInventario) {
                    //llenar el inventario
                    $inventario = new Inventory();
                    $inventario->product_id = $producto->id;
                    $inventario->branch_id = $producto->sucursal_id;
                    $cost = $producto->costo ?? 0; // Si $producto->cost es null, asigna 0
                    $inventario->cost_without_taxes = $cost;
                    $inventario->cost_with_taxes = $cost > 0 ? $cost * 1.13 : 0; // Evita multiplicar si es 0

//                    $stock = ($producto->unidades_presentacion * $oldInventory->saldo_caja) + $oldInventory->saldo_fraccion + $oldInventory->bonificables;
                    $stock = $producto->stcok ?? 0;

                    $inventario->stock = $stock;
                    $inventario->stock_min = $producto->E_minimo ?? 0;
                    $inventario->stock_max = $producto->E_maximo ?? 0;
                    $inventario->is_stock_alert = true;
                    $inventario->is_expiration_date = false;
                    $inventario->is_active = true;
//                    if ($inventario->save()) {
//                        $inventarioNuevo++;
//                    } else {
//                        $inventariosNoCreados[] = $producto->Id;
//                    }
                    //llenar los precios


                    $precioDetalle = new Price();
                    $precioDetalle->inventory_id = $inventario->id;
                    $precioDetalle->name = 'Público';
                    $ivaDetalle = $producto->detalle * 0.13;
                    $precioDetalle->price = $producto->detalle + $ivaDetalle;
                    $precioDetalle->utilidad = 0;
                    $precioDetalle->is_default = true;
                    $precioDetalle->is_active = true;
//                    $precioDetalle->save();

                    $precioMayorista = new Price();
                    $precioMayorista->inventory_id = $inventario->id;
                    $precioMayorista->name = 'Mayorista';
                    $ivaMayorista = $producto->detalle * 0.13;
                    $precioMayorista->price = $producto->mallorista + $ivaMayorista;
                    $precioMayorista->utilidad = 0;
                    $precioMayorista->is_default = false;
                    $precioMayorista->is_active = true;
//                    $precioMayorista->save();
                }
            } else {
                try {
                    $nuevo = new Product();
                    $nuevo->id = $producto->id;
                    $nuevo->name = trim($producto->producto);
                    $nuevo->aplications = "";//str_replace(',', ';', $producto['Linea']);
                    $nuevo->codigo = trim($producto->codigo);
                    $nuevo->bar_code = str_pad($producto->cod_barra, 7, '0', STR_PAD_LEFT);
                    $nuevo->sku = trim($producto->codigo_original);
                    $nuevo->presentacion = trim($producto->presentacion);
                    $nuevo->is_service = false;
                    $idLinea = 0;
                    $categoria = Category::where('name', trim($producto->categoria))->first();
                    if (!$categoria) {
                        $categoria = new Category();
                        $categoria->name = $producto->categoria;
                        $categoria->is_active = true;
//                        $categoria->save();
                        $idLinea = $categoria->id;
                        $lineasNuevas++;
                    } else {
                        $idLinea = $categoria->id;
                    }
                    $nuevo->category_id = $idLinea;
                    $idMarca = 0;
                    $marca = Marca::where('nombre', trim($producto->marca))->first();
                    if (!$marca) {
                        $marca = new Marca();
                        $marca->nombre = $producto->marca;
                        $marca->descripcion = $producto->marca;
                        $marca->estado = true;
//                        $marca->save();
                        $idMarca = $marca->id;
                        $marcasNuevas++;
                    } else {
                        $idMarca = $marca->id;
                    }

                    $nuevo->marca_id = $idMarca;
                    $nuevo->unit_measurement_id = 1;
                    $nuevo->is_taxed = true;
                    $nuevo->images = null;
                    $nuevo->is_active = true;
//                    if ($nuevo->save()) {
//                        $productosNuevos++;
//                    } else {
//                        $productosNoCrados[] = $producto->Id;
//                    }


                    //llenar el inventario
                    $inventario = new Inventory();
//                    $inventario->id = $oldInventory->id_inventario;
                    $inventario->product_id = $producto->id;
                    $inventario->branch_id = $producto->sucursal_id;
                    $cost = $producto->costo ?? 0; // Si $producto->cost es null, asigna 0
                    $inventario->cost_without_taxes = $cost;
                    $inventario->cost_with_taxes = $cost > 0 ? $cost * 1.13 : 0; // Evita multiplicar si es 0

//                    $stock = ($producto->unidades_presentacion * $oldInventory->saldo_caja) + $oldInventory->saldo_fraccion + $oldInventory->bonificables;
                    $stock = $producto->stcok ?? 0;

                    $inventario->stock = $stock;
                    $inventario->stock_min = $producto->E_minimo ?? 0;
                    $inventario->stock_max = $producto->E_maximo ?? 0;
                    $inventario->is_stock_alert = true;
                    $inventario->is_expiration_date = false;
                    $inventario->is_active = true;
//                    if ($inventario->save()) {
//                        $inventarioNuevo++;
//                    } else {
//                        $inventariosNoCreados[] = $producto->Id;
//                    }
                    //llenar los precios


                    $precioDetalle = new Price();
                    $precioDetalle->inventory_id = $inventario->id;
                    $precioDetalle->name = 'Público';
                    $ivaDetalle = $producto->detalle * 0.13;
                    $precioDetalle->price = $producto->detalle + $ivaDetalle;
                    $precioDetalle->utilidad = 0;
                    $precioDetalle->is_default = true;
                    $precioDetalle->is_active = true;
//                    $precioDetalle->save();

                    $precioMayorista = new Price();
                    $precioMayorista->inventory_id = $inventario->id;
                    $precioMayorista->name = 'Mayorista';
                    $ivaMayorista = $producto->detalle * 0.13;
                    $precioMayorista->price = $producto->mallorista + $ivaMayorista;
                    $precioMayorista->utilidad = 0;
                    $precioMayorista->is_default = false;
                    $precioMayorista->is_active = true;
//                    $precioMayorista->save();


                } catch (Exception $e) {
//                dd($e);
//                Log::error("Failed to save product ID {$producto['id']}: " . $e->getMessage());
                    dd($e->getMessage());
//                $items[] = $producto['id']; // Use the actual product ID for tracking failures
                }
            }


        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        dd('productos' . $lineasNuevas . ' lineas nuevas y ' . $marcasNuevas . ' marcas nuevas' .
            ' y ' . $productosNuevos . ' productos nuevos y ' . $inventarioNuevo . ' inventarios nuevos' .
            ' productos no creados: ' . implode(',', $productosNoCrados) .
            ' inventarios no creados: ' . implode(',', $inventariosNoCreados));


    }

    public function replicarInventarioAndBranch()
    {
        set_time_limit(0);
        $inventories = Inventory::all();
        $branches = Branch::whereNot('id', 12)->get();
//        dd($branches);
        foreach ($branches as $branch) {
            foreach ($inventories as $inventory) {
                try {
                    $newInventory = new Inventory();
                    $newInventory->product_id = $inventory->product_id;
                    $newInventory->branch_id = $branch->id;
                    $newInventory->cost_without_taxes = $inventory->cost_without_taxes;
                    $newInventory->cost_with_taxes = $inventory->cost_with_taxes;
                    $newInventory->stock = 0; // Inicializa el stock en 0
                    $newInventory->stock_min = 0; // Inicializa el stock mínimo en 0
                    $newInventory->stock_max = 0; // Inicializa el stock máximo en 0
                    $newInventory->is_stock_alert = false; // Desactiva la alerta de stock
                    $newInventory->is_expiration_date = false; // Desactiva la fecha de caducidad
                    $newInventory->is_active = true; // Activa el inventario
                    $newInventory->save();

                    //buscamos los prcios y los replicamos
                    $prices = Price::where('inventory_id', $inventory->id)->get();
                    foreach ($prices as $price) {
                        $newPrice = new Price();
                        $newPrice->inventory_id = $newInventory->id;
                        $newPrice->name = $price->name;
                        $newPrice->price = $price->price;
                        $newPrice->utilidad = $price->utilidad;
                        $newPrice->is_default = $price->is_default;
                        $newPrice->is_active = true; // Activa el precio
                        $newPrice->save();
                    }
                } catch (Exception $e) {
                    Log::error("Error replicating inventory for branch ID {$branch->id} and product ID {$inventory->product_id}: " . $e->getMessage());
                    die(); // Continúa con el siguiente inventario en caso de error
                }

            }
        }
        dd('Inventarios replicados');

    }
}
