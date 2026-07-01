<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class ProductCodeRegistry extends Component
{
    public string $search = '';
    public string $filterStatus = ''; // '', 'used', 'missing' — only applies to numeric section

    public function render()
    {
        $allProducts = Product::whereNotNull('code')
            ->get(['id', 'code', 'name', 'type', 'color', 'is_active', 'is_abandoned']);

        $numericRows  = [];   // int → Product|null
        $prefixedRows = [];   // 'BL' => [Product, ...]

        $maxNumeric = 0;

        foreach ($allProducts as $product) {
            $code = trim($product->code);

            if (preg_match('/^([A-Z]+)-(\d+)$/i', $code, $m)) {
                // Prefixed: BL-001
                $prefix = strtoupper($m[1]);
                $prefixedRows[$prefix][] = $product;
            } elseif (is_numeric($code) && (int)$code > 0) {
                // Plain integer code
                $n = (int)$code;
                $numericRows[$n] = $product;
                if ($n > $maxNumeric) $maxNumeric = $n;
            }
            // anything else (e.g. SVC-X, free text) — ignored
        }

        // Build full numeric range 1 → max
        $numericRange = [];
        if ($maxNumeric > 0) {
            for ($i = 1; $i <= $maxNumeric; $i++) {
                $numericRange[$i] = $numericRows[$i] ?? null;
            }
        }

        // Apply search to numeric range
        if ($this->search) {
            $s = strtolower($this->search);
            $numericRange = array_filter($numericRange, function ($product, $number) use ($s) {
                if (str_contains((string)$number, $s)) return true;
                if ($product && str_contains(strtolower($product->name), $s)) return true;
                if ($product && str_contains(strtolower($product->color ?? ''), $s)) return true;
                return false;
            }, ARRAY_FILTER_USE_BOTH);
        }

        // Apply status filter to numeric range
        if ($this->filterStatus === 'used') {
            $numericRange = array_filter($numericRange, fn($p) => $p !== null);
        } elseif ($this->filterStatus === 'missing') {
            $numericRange = array_filter($numericRange, fn($p) => $p === null);
        }

        // Stats
        $numericUsed    = count(array_filter($numericRange, fn($p) => $p !== null));
        $numericMissing = count(array_filter($numericRange, fn($p) => $p === null));

        // Sort prefixed alphabetically
        ksort($prefixedRows);

        // Apply search to prefixed section
        if ($this->search) {
            $s = strtolower($this->search);
            foreach ($prefixedRows as $prefix => &$products) {
                $products = array_values(array_filter($products, function ($product) use ($s, $prefix) {
                    return str_contains(strtolower($product->code), $s)
                        || str_contains(strtolower($product->name), $s)
                        || str_contains(strtolower($product->color ?? ''), $s);
                }));
            }
            unset($products);
            $prefixedRows = array_filter($prefixedRows, fn($p) => count($p) > 0);
        }

        return view('livewire.products.product-code-registry', compact(
            'numericRange', 'numericUsed', 'numericMissing', 'maxNumeric',
            'prefixedRows'
        ));
    }
}