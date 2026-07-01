<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductCodeFavourite;
use Livewire\Component;

class ProductCodeRegistry extends Component
{
    public string $search = '';
    public string $filterStatus = '';   // '', 'used', 'missing' — numeric section only
    public string $activeTab = 'all';   // 'all', 'favourites'

    public function toggleFavourite(string $code): void
    {
        $code = strtoupper(trim($code));

        $existing = ProductCodeFavourite::where('code', $code)->first();

        if ($existing) {
            $existing->delete();
        } else {
            ProductCodeFavourite::create([
                'code'       => $code,
                'created_by' => auth()->id(),
            ]);
        }
    }

    public function render()
    {
        $allProducts = Product::whereNotNull('code')
            ->get(['id', 'code', 'name', 'type', 'color', 'is_active', 'is_abandoned']);

        // All favourite codes as a flat set for O(1) lookup
        $favouriteCodes = ProductCodeFavourite::pluck('code')->map(fn($c) => strtoupper($c))->flip()->toArray();

        $numericRows  = [];
        $prefixedRows = [];
        $maxNumeric   = 0;

        foreach ($allProducts as $product) {
            $code = trim($product->code);

            if (preg_match('/^([A-Z]+)-(\d+)$/i', $code, $m)) {
                $prefix = strtoupper($m[1]);
                $prefixedRows[$prefix][] = $product;
            } elseif (is_numeric($code) && (int) $code > 0) {
                $n = (int) $code;
                $numericRows[$n] = $product;
                if ($n > $maxNumeric) {
                    $maxNumeric = $n;
                }
            }
        }

        // Build full numeric range 1 → max
        $numericRange = [];
        if ($maxNumeric > 0) {
            for ($i = 1; $i <= $maxNumeric; $i++) {
                $numericRange[$i] = $numericRows[$i] ?? null;
            }
        }

        // ── Favourites tab ──
        if ($this->activeTab === 'favourites') {
            // Numeric favourites (only those within range)
            $numericRange = array_filter($numericRange, function ($product, $number) use ($favouriteCodes) {
                $code = str_pad($number, 4, '0', STR_PAD_LEFT);
                // Also check plain integer string
                return isset($favouriteCodes[(string) $number])
                    || isset($favouriteCodes[$code]);
            }, ARRAY_FILTER_USE_BOTH);

            // Prefixed favourites
            foreach ($prefixedRows as $prefix => &$products) {
                $products = array_values(array_filter($products, fn($p) => isset($favouriteCodes[strtoupper($p->code)])));
            }
            unset($products);
            $prefixedRows = array_filter($prefixedRows, fn($p) => count($p) > 0);

            // Reset status filter — not relevant in favourites tab
            $this->filterStatus = '';
        }

        // ── Search ──
        if ($this->search) {
            $s = strtolower($this->search);

            $numericRange = array_filter($numericRange, function ($product, $number) use ($s) {
                if (str_contains((string) $number, $s)) return true;
                if ($product && str_contains(strtolower($product->name), $s)) return true;
                if ($product && str_contains(strtolower($product->color ?? ''), $s)) return true;
                return false;
            }, ARRAY_FILTER_USE_BOTH);

            foreach ($prefixedRows as $prefix => &$products) {
                $products = array_values(array_filter($products, function ($product) use ($s) {
                    return str_contains(strtolower($product->code), $s)
                        || str_contains(strtolower($product->name), $s)
                        || str_contains(strtolower($product->color ?? ''), $s);
                }));
            }
            unset($products);
            $prefixedRows = array_filter($prefixedRows, fn($p) => count($p) > 0);
        }

        // ── Status filter (numeric only, all tab only) ──
        if ($this->activeTab === 'all' && $this->filterStatus === 'used') {
            $numericRange = array_filter($numericRange, fn($p) => $p !== null);
        } elseif ($this->activeTab === 'all' && $this->filterStatus === 'missing') {
            $numericRange = array_filter($numericRange, fn($p) => $p === null);
        }

        $numericUsed    = count(array_filter($numericRange, fn($p) => $p !== null));
        $numericMissing = count(array_filter($numericRange, fn($p) => $p === null));

        ksort($prefixedRows);

        $totalFavourites = count($favouriteCodes);

        return view('livewire.products.product-code-registry', compact(
            'numericRange', 'numericUsed', 'numericMissing', 'maxNumeric',
            'prefixedRows', 'favouriteCodes', 'totalFavourites'
        ));
    }
}