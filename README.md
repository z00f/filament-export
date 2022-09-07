# Filament Export
Customizable export and print functionality for Filament Admin Panel.

This package provides a bulk action and header action to export your filament tables easily.

![filament-export-3](https://user-images.githubusercontent.com/83382417/179013026-14ddd872-fedc-45d2-954a-1447005777bb.png)

## Requirements
- PHP 8
- [Filament 2.0](https://github.com/laravel-filament/filament)

### Dependencies
- [maatwebsite/excel](https://github.com/SpartnerNL/Laravel-Excel)
- [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)

## Installation

```bash
composer require alperenersoy/filament-export
```

## Using

### Simple Usage

#### Bulk Action

You can export selected rows with the bulk action.

```php
$table->bulkActions([
    ...
    FilamentReportBulkAction::make('export')
    ...
]);
```

#### Header Action (Experimental)

You can filter, search, sort and export your table with the header action. Header action is experimental for now, bulk action is safer to use.

```php
$table->headerActions([
    ...
    FilamentReportHeaderAction::make('export')
    ...
]);
```

Since ButtonAction is deprecated you may use this action with ->button() instead.

### Full Usage

Both actions provide functions for configuration.

```php
FilamentReportBulkAction::make('export')
    ->fileName('My File') // Default file name
    ->timeFormat('m y d') // Default time format for naming exports
    ->defaultFormat('pdf') // xlsx, csv or pdf
    ->defaultPageOrientation('landscape') // Page orientation for pdf files. portrait or landscape
    ->disableAdditionalColumns() // Disable additional columns input
    ->disableFilterColumns() // Disable filter columns input
    ->disableFileName() // Disable file name input
    ->disableFileNamePrefix() // Disable file name prefix
    ->disablePreview() // Disable export preview
    ->fileNameFieldLabel('File Name') // Label for file name input
    ->formatFieldLabel('Format') // Label for format input
    ->pageOrientationFieldLabel('Page Orientation') // Label for page orientation input
    ->filterColumnsFieldLabel('filter columns') // Label for filter columns input
    ->additionalColumnsFieldLabel('Additional Columns') // Label for additional columns input
    ->additionalColumnsTitleFieldLabel('Title') // Label for additional columns' title input 
    ->additionalColumnsDefaultValueFieldLabel('Default Value') // Label for additional columns' default value input 
    ->additionalColumnsAddButtonLabel('Add Column') // Label for additional columns' add button 
```
You can also use default bulk action and header action functions to customize actions.

## Configuration

Publish configuration

```bash
php artisan vendor:publish --provider="z00f\FilamentReport\FilamentReportServiceProvider" --tag="config"
```

You can configure these settings:

```php
return [
    'default_format' => 'xlsx',
    'time_format' => 'M_d_Y-H_i',
    'default_page_orientation' => 'portrait',
    'disable_additional_columns' => false,
    'disable_filter_columns' => false,
    'disable_file_name' => false,
    'disable_preview' => false,
    'use_snappy' => false,
    'action_icon' => 'heroicon-o-document-download',
    'preview_icon' => 'heroicon-o-eye',
    'export_icon' => 'heroicon-o-download',
    'print_icon' => 'heroicon-o-printer',
    'cancel_icon' => 'heroicon-o-x-circle'
];
```

## Overriding Views

Publish views

```bash
php artisan vendor:publish --provider="z00f\FilamentReport\FilamentReportServiceProvider" --tag="views"
```

This package has two views:

1. "components\table_view.blade.php" view is used for preview and as print template.

2. "pdf.blade.php" view is used as pdf export template.
   
### Using Custom Variables In Templates

```php
FilamentReportBulkAction::make('export')
    ->extraViewData([
        'myVariable' => 'My Variable'
    ])
```

or use closures

```php
FilamentReportHeaderAction::make('export')
    ->extraViewData(fn ($action) => [
        'recordCount' => $action->getRecords()->count()
    ])
```

Then use them in the templates as regular blade variables:

```blade
{{ $myVariable }}
```

## Using Snappy

By default, this package uses [dompdf](https://github.com/barryvdh/laravel-dompdf) as pdf generator.

If you want to use Snappy instead you need to install **barryvdh/laravel-snappy** to your project and configure it yourself. (See [barryvdh/laravel-snappy](https://github.com/barryvdh/laravel-snappy) for more information.)

To use snappy for PDF exports:

1. You can simply add ->snappy() to your actions.
   
```php
FilamentReportBulkAction::make('export')
    ->snappy()
```
or
```php
FilamentReportHeaderAction::make('export')
    ->snappy()
```
2. You can update the config file to use it as default.
```php
[
    ...
    'use_snappy' => true,
    ...
]
```
# filament-export
