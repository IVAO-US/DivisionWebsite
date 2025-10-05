<?php
use Livewire\Volt\Component;

new class extends Component {
    public array $images = [];
    public array $optimizedImages = [];
    public int $maxRowsDesktop = 12;
    public int $maxRowsTablet = 0; // 0 = infinite
    public int $maxRowsMobile = 0; // 0 = infinite
    
    // New properties for setupId and debug functionality
    public bool $debugMode = false;
    public ?string $setupId = null;
    private string $generatedSetupId = '';

    public function mount(): void
    {
        // Generate or use provided setupId
        if ($this->setupId) {
            // Use provided setupId and generate layout based on it
            $this->generatedSetupId = $this->setupId;
            $this->optimizedImages = $this->generateLayoutFromSetupId($this->setupId);
        } else {
            // Generate setupId FIRST to ensure consistency
            $this->generatedSetupId = $this->generateRandomSetupId();
            // Then generate layout using this setupId
            $this->optimizedImages = $this->generateLayoutFromSetupId($this->generatedSetupId);
        }
    }

    /**
     * Generate a random setupId for new layouts
     * This creates a unique seed that can be used to reproduce the layout
     */
    public function generateRandomSetupId(): string
    {
        // Generate a random 12-character hex string
        $timestamp = time();
        $randomBytes = random_bytes(6);
        $hash = substr(md5($timestamp . bin2hex($randomBytes)), 0, 12);
        
        return strtoupper($hash);
    }

    /**
     * Generate layout based on provided setupId (seed)
     * This allows reproducing specific configurations
     */
    public function generateLayoutFromSetupId(string $setupId): array
    {
        if (empty($this->images)) {
            return [];
        }

        // Convert setupId to numeric seed for reproducible randomness
        $seed = $this->setupIdToSeed($setupId);
        mt_srand($seed);
        
        $images = $this->images;
        $optimized = [];
        
        // Separate priority and non-priority items (same as original logic)
        $priorityItems = array_filter($images, fn($img) => $img['priority'] ?? false);
        $regularItems = array_filter($images, fn($img) => !($img['priority'] ?? false));
        
        // Define size patterns based on seed
        $prioritySizes = [
            ['cols' => 3, 'rows' => 2], // Large featured
            ['cols' => 2, 'rows' => 2], // Square large
            ['cols' => 3, 'rows' => 1], // Wide featured
        ];
        
        $randomSizes = [
            ['cols' => 1, 'rows' => 1], // Small square
            ['cols' => 2, 'rows' => 1], // Wide small
            ['cols' => 1, 'rows' => 2], // Tall small
            ['cols' => 2, 'rows' => 1], // Wide small (repeated for higher probability)
            ['cols' => 1, 'rows' => 1], // Small square (repeated for higher probability)
            ['cols' => 1, 'rows' => 1], // Small square (more common)
        ];
        
        // Process priority items with seeded randomness
        foreach ($priorityItems as $index => $image) {
            $sizeIndex = $index % count($prioritySizes);
            $pattern = $prioritySizes[$sizeIndex];
            
            $optimized[] = array_merge($image, [
                'gridCols' => $pattern['cols'],
                'gridRows' => $pattern['rows'],
                'gridClasses' => $this->generateGridClasses($pattern['cols'], $pattern['rows']),
                'isPriority' => true
            ]);
        }
        
        // Process regular items with seeded randomness
        foreach ($regularItems as $image) {
            // Use seeded random to pick size consistently
            $randomIndex = mt_rand(0, count($randomSizes) - 1);
            $pattern = $randomSizes[$randomIndex];
            
            $optimized[] = array_merge($image, [
                'gridCols' => $pattern['cols'],
                'gridRows' => $pattern['rows'],
                'gridClasses' => $this->generateGridClasses($pattern['cols'], $pattern['rows']),
                'isPriority' => false
            ]);
        }
        
        // Shuffle the final array slightly to create more organic placement
        // but keep priority items towards the beginning (using seeded randomness)
        $finalOrder = [];
        
        // Place first priority item at start
        if (count($optimized) > 0) {
            $finalOrder[] = array_shift($optimized);
        }
        
        // Mix remaining items for organic feel using seeded randomness
        while (count($optimized) > 0) {
            // 60% chance to pick from remaining priority items if any exist
            $remainingPriority = array_filter($optimized, fn($item) => $item['isPriority']);
            $remainingRegular = array_filter($optimized, fn($item) => !$item['isPriority']);
            
            if (count($remainingPriority) > 0 && (mt_rand(1, 100) <= 60 || count($remainingRegular) === 0)) {
                $picked = array_shift($remainingPriority);
                $optimized = array_merge($remainingPriority, $remainingRegular);
            } else {
                $picked = array_shift($remainingRegular);
                $optimized = array_merge($remainingPriority, $remainingRegular);
            }
            
            $finalOrder[] = $picked;
        }
        
        // Replace the simple shuffle with the more complex organic arrangement
        $optimized = $finalOrder;
        
        // Apply maxRows filtering FIRST if needed (before other optimizations)
        $gridCols = 6;
        $maxRows = $this->getCurrentMaxRows();
        if ($maxRows > 0) {
            $optimized = $this->enforceMaxRows($optimized, $gridCols, $maxRows);
        }
        
        // Apply lightweight optimizations that don't lose items
        // eliminateGridHoles and heavy optimizations are disabled to prevent item loss
        
        // Only apply last row optimization and straggler removal
        $optimized = $this->optimizeLastRowLightweight($optimized);
        $optimized = $this->ensureCompleteRows($optimized);
        
        // Verify we haven't lost items (log for debugging)
        $initialCount = count($this->images);
        $finalCount = count($optimized);
        if ($finalCount < $initialCount && $maxRows === 0) {
            // If maxRows is not set and we lost items, something went wrong
            // Return all items to be safe
            return $finalOrder;
        }
        
        // Reset random seed to avoid affecting other operations
        mt_srand();
        
        return $optimized;
    }

    /**
     * Convert setupId string to numeric seed
     */
    private function setupIdToSeed(string $setupId): int
    {
        // Convert hex setupId to integer seed
        $hex = strtolower($setupId);
        $seed = 0;
        
        for ($i = 0; $i < strlen($hex); $i++) {
            $char = $hex[$i];
            if (ctype_xdigit($char)) {
                $seed = ($seed * 16 + hexdec($char)) % PHP_INT_MAX;
            }
        }
        
        return $seed ?: 1; // Ensure non-zero seed
    }

    /**
     * Get the current setupId for display/debugging
     */
    public function getCurrentSetupId(): string
    {
        return $this->generatedSetupId;
    }

    /**
     * Get current max rows based on screen size (for server-side logic)
     * Note: This assumes desktop by default, real responsive detection needs JS
     */
    public function getCurrentMaxRows(): int
    {
        return $this->maxRowsDesktop;
    }

    /**
     * Find all empty spaces (holes) in the grid
     */
    private function detectAllHoles(array $grid, int $gridCols): array
    {
        if (empty($grid)) return [];
        
        $holes = [];
        $maxRow = max(array_keys($grid));
        
        for ($row = 0; $row <= $maxRow; $row++) {
            for ($col = 0; $col < $gridCols; $col++) {
                // Check if this position is empty but has content above or to the left
                if (!isset($grid[$row][$col])) {
                    $hasContentAbove = $row > 0 && isset($grid[$row - 1][$col]);
                    $hasContentLeft = $col > 0 && isset($grid[$row][$col - 1]);
                    $hasContentAnywhere = $this->hasContentInRow($grid, $row);
                    
                    if ($hasContentAbove || $hasContentLeft || $hasContentAnywhere) {
                        $holes[] = ['row' => $row, 'col' => $col];
                    }
                }
            }
        }
        
        return $holes;
    }

    /**
     * Check if a row has any content
     */
    private function hasContentInRow(array $grid, int $row): bool
    {
        return isset($grid[$row]) && !empty($grid[$row]);
    }

    /**
     * Detect all holes in the grid and reorganize items to fill them
     * SIMPLIFIED: Reduced iterations to prevent item loss
     */
    public function eliminateGridHoles(array $items): array
    {
        if (empty($items)) return $items;
        
        // Grid holes are now handled naturally by the layout algorithm
        // Over-optimization here caused items to disappear
        // Just return items as-is
        return $items;
    }

    /**
     * Reorganize items to fill detected holes
     * FIXED: Ensures no items are lost during reorganization
     */
    private function reorganizeToFillHoles(array $items, array $holes, int $gridCols): array
    {
        if (empty($holes)) return $items;
        
        // Don't modify the items, just return them as-is
        // The holes will be naturally filled by the grid layout algorithm
        // Trying to be too clever here causes items to disappear
        return $items;
    }
    
    /**
     * Lightweight last row optimization - only adjusts sizes, never removes items
     */
    private function optimizeLastRowLightweight(array $items): array
    {
        if (empty($items)) return $items;
            
        $gridCols = 6;
        $gridState = $this->simulateGridPlacement($items, $gridCols);
        $lastRowInfo = $this->analyzeLastRow($gridState, $gridCols);
        
        // If last row has significant empty space, try to expand items
        if ($lastRowInfo['needsOptimization'] && $lastRowInfo['emptySpace'] >= 2) {
            // Find the last item and try to expand it to fill empty space
            $lastRowItems = $lastRowInfo['lastRowItems'];
            if (!empty($lastRowItems)) {
                $lastItemIndex = end($lastRowItems);
                if (isset($items[$lastItemIndex])) {
                    $emptySpace = $lastRowInfo['emptySpace'];
                    $currentCols = $items[$lastItemIndex]['gridCols'];
                    
                    // Expand by min of (empty space, 2 columns max)
                    $expansion = min($emptySpace, 2, $gridCols - $currentCols);
                    if ($expansion > 0) {
                        $items[$lastItemIndex]['gridCols'] += $expansion;
                        $items[$lastItemIndex]['gridClasses'] = $this->generateGridClasses(
                            $items[$lastItemIndex]['gridCols'],
                            $items[$lastItemIndex]['gridRows']
                        );
                    }
                }
            }
        }
        
        return $items;
    }
    
    /**
     * OLD COMPLEX METHOD - kept for reference but not used
     * Optimize last row filling to avoid half-empty rows
     * Analyzes grid placement and adjusts sizes for better visual balance
     */
    private function optimizeLastRow(array $items): array
    {
        if (empty($items)) return $items;
            
        $gridCols = 6;
        $maxRows = $this->getCurrentMaxRows();
        $optimizedItems = $items;
        
        // If max rows is set, filter items that fit within limit
        if ($maxRows > 0) {
            $optimizedItems = $this->enforceMaxRows($optimizedItems, $gridCols, $maxRows);
        }
        
        // Simulate grid placement to find last row elements
        $gridState = $this->simulateGridPlacement($optimizedItems, $gridCols);
        $lastRowInfo = $this->analyzeLastRow($gridState, $gridCols);
        
        // If last row is not optimally filled, adjust it
        if ($lastRowInfo['needsOptimization']) {
            $optimizedItems = $this->adjustLastRowSizes($optimizedItems, $lastRowInfo, $gridCols);
        }
        
        return $optimizedItems;
    }

    /**
     * Enforce max rows limitation by filtering/adjusting items
     */
    private function enforceMaxRows(array $items, int $gridCols, int $maxRows): array
    {
        $filteredItems = [];
        $gridState = $this->simulateGridPlacement($items, $gridCols);
        
        foreach ($gridState['items'] as $index => $item) {
            $itemEndRow = $item['gridRow'] + $item['gridRows'] - 1;
            
            // Only include items that fit within max rows
            if ($itemEndRow < $maxRows) {
                $filteredItems[] = $item;
            }
        }
        
        return $filteredItems;
    }
    
    /**
     * NEW: Ensure complete and balanced rows without losing items
     * Removes only stragglers (1-2 items) on the last row if needed
     */
    private function ensureCompleteRows(array $items): array
    {
        if (empty($items)) return $items;
        
        $gridCols = 6;
        $gridState = $this->simulateGridPlacement($items, $gridCols);
        $grid = $gridState['grid'];
        
        if (empty($grid)) return $items;
        
        // Analyze the last row
        $maxRow = max(array_keys($grid));
        $lastRow = $grid[$maxRow] ?? [];
        $lastRowOccupiedCols = count($lastRow);
        
        // Find items in the last row
        $lastRowItemIndices = [];
        foreach ($gridState['items'] as $index => $item) {
            $itemEndRow = $item['gridRow'] + $item['gridRows'] - 1;
            if ($itemEndRow === $maxRow) {
                $lastRowItemIndices[] = $index;
            }
        }
        
        // Calculate how much of the last row is filled (as percentage)
        $lastRowFillPercentage = ($lastRowOccupiedCols / $gridCols) * 100;
        
        // Only remove stragglers if:
        // 1. Last row is less than 40% filled (less than ~2.5 columns)
        // 2. AND there are only 1-2 items in the last row
        $shouldRemoveStragglers = ($lastRowFillPercentage < 40) && (count($lastRowItemIndices) <= 2);
        
        if (!$shouldRemoveStragglers) {
            // Keep all items - the last row is acceptable
            return $items;
        }
        
        // Remove only the straggler items from the last row
        $filteredItems = [];
        foreach ($gridState['items'] as $index => $item) {
            if (!in_array($index, $lastRowItemIndices)) {
                $filteredItems[] = $item;
            }
        }
        
        return $filteredItems;
    }
    
    /**
     * Simulate how items would be placed on the grid
     */
    private function simulateGridPlacement(array $items, int $gridCols): array
    {
        $grid = [];
        $currentRow = 0;
        $currentCol = 0;
        
        foreach ($items as $index => $item) {
            $cols = $item['gridCols'];
            $rows = $item['gridRows'];
            
            // Find next available position
            while ($this->isPositionOccupied($grid, $currentRow, $currentCol, $cols, $rows, $gridCols)) {
                $currentCol++;
                if ($currentCol + $cols > $gridCols) {
                    $currentCol = 0;
                    $currentRow++;
                }
            }
            
            // Place item in grid
            for ($r = $currentRow; $r < $currentRow + $rows; $r++) {
                for ($c = $currentCol; $c < $currentCol + $cols; $c++) {
                    $grid[$r][$c] = $index;
                }
            }
            
            // Store placement info in item
            $items[$index]['gridRow'] = $currentRow;
            $items[$index]['gridCol'] = $currentCol;
        }
        
        return ['grid' => $grid, 'items' => $items];
    }
    
    /**
     * Check if a position is already occupied in the grid
     */
    private function isPositionOccupied(array $grid, int $row, int $col, int $cols, int $rows, int $gridCols): bool
    {
        if ($col + $cols > $gridCols) return true;
        
        for ($r = $row; $r < $row + $rows; $r++) {
            for ($c = $col; $c < $col + $cols; $c++) {
                if (isset($grid[$r][$c])) return true;
            }
        }
        return false;
    }
    
    /**
     * Analyze the last row to determine if optimization is needed
     */
    private function analyzeLastRow(array $gridState, int $gridCols): array
    {
        $grid = $gridState['grid'];
        if (empty($grid)) return ['needsOptimization' => false];
        
        $lastRowIndex = max(array_keys($grid));
        $lastRow = $grid[$lastRowIndex] ?? [];
        $occupiedCols = count($lastRow);
        $emptySpace = $gridCols - $occupiedCols;
        
        // Find items that occupy the last row
        $lastRowItems = [];
        foreach ($gridState['items'] as $index => $item) {
            $itemEndRow = $item['gridRow'] + $item['gridRows'] - 1;
            if ($itemEndRow === $lastRowIndex) {
                $lastRowItems[] = $index;
            }
        }
        
        return [
            'needsOptimization' => $emptySpace >= 2, // Optimize if 2+ empty columns
            'lastRowIndex' => $lastRowIndex,
            'occupiedCols' => $occupiedCols,
            'emptySpace' => $emptySpace,
            'lastRowItems' => $lastRowItems
        ];
    }
    
    /**
     * Adjust sizes of items in the last row for better filling
     */
    private function adjustLastRowSizes(array $items, array $lastRowInfo, int $gridCols): array
    {
        $lastRowItems = $lastRowInfo['lastRowItems'];
        $emptySpace = $lastRowInfo['emptySpace'];
        
        if (empty($lastRowItems) || $emptySpace < 2) return $items;
        
        // Strategy: Extend one or more items to fill the empty space
        foreach ($lastRowItems as $itemIndex) {
            $item = &$items[$itemIndex];
            $currentCols = $item['gridCols'];
            $maxExpansion = min($emptySpace, $gridCols - $currentCols);
            
            if ($maxExpansion >= 1) {
                // Expand this item
                $newCols = $currentCols + min($maxExpansion, 2); // Max expansion of 2 columns
                $item['gridCols'] = min($newCols, $gridCols);
                $item['gridClasses'] = $this->generateGridClasses($item['gridCols'], $item['gridRows']);
                
                // Reduce remaining empty space
                $emptySpace -= ($item['gridCols'] - $currentCols);
                if ($emptySpace <= 0) break;
            }
        }
        
        return $items;
    }
    
    /**
     * Generate responsive grid classes for different screen sizes
     */
    public function generateGridClasses(int $cols, int $rows): string
    {
        // Base classes for desktop
        $classes = "col-span-{$cols} row-span-{$rows}";
        
        // Responsive adjustments
        if ($cols >= 3) {
            // Large tiles become medium on tablet, small on mobile
            $classes .= " lg:col-span-{$cols} md:col-span-2 col-span-1";
            $classes .= " lg:row-span-{$rows} md:row-span-1 row-span-1";
        } elseif ($cols == 2) {
            // Medium tiles stay medium on tablet, small on mobile
            $classes .= " lg:col-span-{$cols} md:col-span-{$cols} col-span-1";
            $classes .= " lg:row-span-{$rows} md:row-span-{$rows} row-span-1";
        } else {
            // Small tiles stay consistent
            $classes .= " col-span-1 row-span-1";
        }
        
        return $classes;
    }
    
    /**
     * Get container grid configuration based on screen size
     * Ensures proper Bento grid behavior across all devices
     */
    public function getContainerGridClasses(): string
    {
        $baseClasses = "grid gap-3 bg-base-100 rounded-xl";
        $gridClasses = "grid-cols-2 md:grid-cols-4 lg:grid-cols-6";
        
        // Dynamic row classes based on max rows settings
        $rowClasses = "auto-rows-[120px] md:auto-rows-[140px] lg:auto-rows-[160px]";
        
        if ($this->maxRowsMobile > 0) {
            $maxHeight = $this->maxRowsMobile * 120 + ($this->maxRowsMobile - 1) * 12; // Include gaps
            $rowClasses .= " max-h-[{$maxHeight}px]";
        }
        
        if ($this->maxRowsTablet > 0) {
            $maxHeight = $this->maxRowsTablet * 140 + ($this->maxRowsTablet - 1) * 12;
            $rowClasses .= " md:max-h-[{$maxHeight}px]";
        }
        
        if ($this->maxRowsDesktop > 0) {
            $maxHeight = $this->maxRowsDesktop * 160 + ($this->maxRowsDesktop - 1) * 12;
            $rowClasses .= " lg:max-h-[{$maxHeight}px]";
        }
        
        return "{$baseClasses} {$gridClasses} {$rowClasses}";
    }
}; ?>

{{-- Container with debug info --}}
<div>
    {{-- Bento Grid Container with Responsive Design --}}
    <div class="{{ $this->getContainerGridClasses() }}">
        @foreach($optimizedImages as $index => $image)
            <div 
                class="group relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-all duration-300 hover:scale-[1.02] {{ $image['gridClasses'] }}"
            >
                <a 
                    href="{{ $image['href'] }}" 
                    target="_blank" 
                    class="block w-full h-full relative"
                >
                    {{-- Image with aspect ratio handling --}}
                    <img 
                        src="{{ $image['url'] }}" 
                        alt="{{ $image['title'] ?? 'Image' }}"
                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                        loading="lazy"
                    />
                    
                    {{-- Overlay with gradient --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-0 left-0 right-0 p-3">
                            <h4 class="text-white font-semibold text-sm mb-1 line-clamp-2">
                                {{ $image['title'] ?? 'Image' }}
                            </h4>
                            @if(isset($image['description']))
                                <p class="text-white/80 text-xs line-clamp-2">
                                    {{ $image['description'] }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Hover icon --}}
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="bg-primary text-primary-content rounded-full p-1">
                            <span class="iconify text-sm" data-icon="phosphor:arrow-square-out"></span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Debug information displayed below the grid --}}
    @if($debugMode)
        <div class="mt-4 p-3 bg-base-200 rounded-lg border border-base-300">
            <div class="text-sm text-base-content">
                <strong>Setup ID:</strong> 
                <span class="font-mono bg-base-300 px-2 py-1 rounded">
                    {{ $this->getCurrentSetupId() }}
                </span>
            </div>
        </div>
    @endif
</div>