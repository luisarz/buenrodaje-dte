<?php

namespace App\Filament\Resources\RetaceoModels\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Notifications\Notification;

class RetaceoModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->compact()
                    ->heading('Datos del Retaceo')
                    ->schema([
                        DatePicker::make('operation_date')
                            ->default(now())
                            ->inlineLabel(true)
                            ->required(),
                        Select::make('poliza_number')
                            ->options(
                                \App\Models\Purchase::query()
                                    ->where('retaced_status', '!=', 'Retaceado')
                                    ->pluck('poliza_number', 'poliza_number')
                            )

                            ->preload()
                            ->searchable()
                            ->required(),
                        TextInput::make('retaceo_number')
                            ->required(),
                    ]),
                Section::make()
                    ->columns(8)
                    ->compact()
                    ->heading('Costos del Retaceo')
                    ->schema([
                        TextInput::make('fob')
                            ->required()


                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))
                            ->default(0.0),
                        TextInput::make('flete')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))
                            ->default(0.0),
                        TextInput::make('seguro')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))
                            ->default(0.0),
                        TextInput::make('otros')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))
                            ->default(0.0),
                        TextInput::make('cif')
                            ->required()
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->readOnly(true)
                            ->default(0.0),
                        TextInput::make('dai')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))
                            ->default(0.0),
                        TextInput::make('suma')
                            ->required()
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->default(0.0),
                        TextInput::make('iva')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                        TextInput::make('almacenaje')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))

                            ->default(0.0),
                        TextInput::make('custodia')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))

                            ->default(0.0),
                        TextInput::make('viaticos')
                            ->required()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))

                            ->numeric()
                            ->default(0.0),
                        TextInput::make('transporte')
                            ->required()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))

                            ->numeric()
                            ->default(0.0),
                        TextInput::make('descarga')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))

                            ->default(0.0),
                        TextInput::make('recarga')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))

                            ->default(0.0),
                        TextInput::make('otros_gastos')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->afterStateUpdated(fn($state, callable $set, $get) => self::calcularCif($get, $set))

                            ->default(0.0),
                        TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->readOnly(true)
                            ->default(0.0),
//                        TextInput::make('observaciones')
//                            ->default(null),
                    ]),


            ]);
    }

    protected static function calcularCif(callable $get, callable $set): void
    {
        try {
            $fob = $get('fob') ?? 0;
            $flete = $get('flete') ?? 0;
            $seguro = $get('seguro') ?? 0;
            $otros = $get('otros') ?? 0;
            $dai = $get('dai') ?? 0;



//        $set('cif', $fob + $flete + $seguro + $otros + $dai);
            $cif = $fob + $flete + $seguro + $otros;
            $set('cif', $cif);
            $suma = $cif + $dai;
            $set('suma', $suma);
            $iva = $suma * 0.13;
            $set('iva', $iva);

            //crear las variables desde almacenamiento hasta otros gastos
            $almacenaje = $get('almacenaje') ?? 0;
            $custodia = $get('custodia') ?? 0;
            $viaticos = $get('viaticos') ?? 0;
            $transporte = $get('transporte') ?? 0;
            $descarga = $get('descarga') ?? 0;
            $recarga = $get('recarga') ?? 0;
            $otros_gastos = $get('otros_gastos') ?? 0;
            //calcular el total
            $total = $almacenaje + $custodia + $viaticos + $transporte + $descarga + $recarga + $otros_gastos;
            $set('total', $total);


        }catch (\Exception $exception){
            Notification::make()
                ->title('Error al calcular CIF, DAI, SUMA e IVA')
                ->body($exception->getMessage())
                ->danger()
                ->send();
            $set('cif', 0);
            $set('suma', 0);
            $set('iva', 0);
        }

    }

}
