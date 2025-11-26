<?php

/**
 * Dynamic Select Component with On-the-fly Option Creation
 * Requires Livewire 3+ due to Modelable
 * 
 * This component extends the standard MaryUI2 <x-select> and <x-select-group> components 
 * with the ability to add new options dynamically without leaving the form context.
 * 
 * Features:
 * - Support for both standard select and grouped select (optgroup)
 * - Ability to add new options on-the-fly with validation
 * - Auto-sorting of newly added options alphabetically
 * - Event dispatching when new options are added
 * - Full support for all standard MaryUI2 select attributes (label, hint, icons, etc.)
 * - Proper handling of the 'required' attribute
 * 
 * Usage:
 * 
 * <livewire:inputable-select
 *     :initial-options="$options"      // Array of options or grouped options array
 *     :selected-option="$value"        // Currently selected value 
 *     :use-grouped="true|false"        // Whether to use optgroup structure (default: false)
 *     label="Select Label"             // Label for the select
 *     placeholder="Select..."          // Placeholder text
 *     icon="phosphor.tag"                // Icon to display (uses MaryUI icon naming)
 *     hint="Optional help text"        // Help text displayed below the select
 *     :option-value="'id'"             // Key to use for option values (default: 'id')
 *     :option-label="'name'"           // Key to use for option labels (default: 'name')
 *     required                         // Add the required attribute
 *     maxlength                        // Add the maxlength attribute
 *     :forceUppercase="true|false"     // Force the input to be sent to the select in uppercase
 *     wire:model="fieldName"           // Wire model to bind the selected value
 *     wire:key="unique_identifier"     // It needs to be uniquely named
 * />
 * 
 * Events:
 * - 'option-added': Dispatched when a new option is added, with the new option as payload
 * - 'focus-new-option': Dispatched when the new option input is shown
 * 
 * Format for simple options array:
 * [
 *    ['id' => 'value1', 'name' => 'Label 1'],
 *    ['id' => 'value2', 'name' => 'Label 2']
 * ]
 *
 * Format for grouped options array:
 * [
 *    'Group 1' => [
 *       ['id' => 'value1', 'name' => 'Label 1'],
 *       ['id' => 'value2', 'name' => 'Label 2']
 *    ],
 *    'Group 2' => [
 *       ['id' => 'value3', 'name' => 'Label 3']
 *    ]
 * ]
 * 
 * Note: When adding new options to a grouped select, options are added to a 'Custom' group.
 * 
 * @author Joey Salzmann
 * @version 1.0
 */


use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Modelable;

new class extends Component {
    #[Modelable]
    public $selectedOption = null;

    /* Component properties */
    public $options = [];
    public $newOptionName = '';
    public $showNewOptionInput = false;
    public $useGrouped = false;
    public $forceUppercase = false;
    
    /* Component attributes */
    public $label = null;
    public $placeholder = null;
    public $icon = null;
    public $hint = null;
    public $optionValue = 'id';
    public $optionLabel = 'name';
    public $required = false;
    public $maxlength = 747;
    
    public function mount(
        $initialOptions = [], 
        $selectedOption = null, 
        $useGrouped = false,
        $forceUppercase = false,
        $label = null, 
        $placeholder = 'Sélectionnez une option', 
        $icon = null,
        $hint = null,
        $optionValue = 'id',
        $optionLabel = 'name',
        $required = false,
        $maxlength = 747
    ) {
        $this->useGrouped = $useGrouped;
        $this->forceUppercase = $forceUppercase;
        $this->options = $initialOptions;
        $this->selectedOption = $selectedOption;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->icon = $icon;
        $this->hint = $hint;
        $this->optionValue = $optionValue;
        $this->optionLabel = $optionLabel;
        $this->required = $required;
        $this->maxlength = $maxlength;
    }
    
    protected function rules(): array
    {
        return [
            'newOptionName'  => 'required|min:1|max:255',
        ];
    }
    
    public function createNewOption()
    {
        $this->validate();

        /* Do we want to force uppercase ? */
        $optionName = $this->forceUppercase ? strtoupper($this->newOptionName) : $this->newOptionName;

        /* New unique ID */
        $newId = $optionName;

        /* Add the new option */
        $newOption = [
            $this->optionValue => $newId,
            $this->optionLabel => $optionName
        ];

        if ($this->useGrouped) {
            /* Custom group opt name */
            $group = 'Custom';

            /* Initialize group */
            if (!isset($this->options[$group])) {
                $this->options[$group] = [];
            }

            /* Check if option already exists */
            if (!collect($this->options[$group])->contains($this->optionLabel, $this->newOptionName)) {
                // Ajouter et trier les options dans le groupe
                $this->options[$group][] = $newOption;

                usort($this->options[$group], function ($a, $b) {
                    return strcasecmp($a[$this->optionLabel], $b[$this->optionLabel]);
                });

                $this->dispatch('option-added', $newOption);
            }

            $this->selectedOption = $newId;

        } else {
            /* Check if option already exists */
            if (!in_array($this->newOptionName, array_column($this->options, $this->optionLabel))) {
                $this->options[] = $newOption;

                usort($this->options, function ($a, $b) {
                    return strcasecmp($a[$this->optionLabel], $b[$this->optionLabel]);
                });

                $this->dispatch('option-added', $newOption);
            }

            $this->selectedOption = $newId;
        }

        /* Reset the form */
        $this->newOptionName = '';
        $this->showNewOptionInput = false;
    }
        
    public function toggleNewOptionInput()
    {
        $this->showNewOptionInput = !$this->showNewOptionInput;
        if ($this->showNewOptionInput) {
            $this->dispatch('focus-new-option');
        }
    }
    
    public function cancel()
    {
        $this->newOptionName = '';
        $this->showNewOptionInput = false;
    }
}; ?>

<div>
    {{-- Select component --}}
    <div class="relative">
        @if ($useGrouped)
            @if ($required)
                <x-select-group
                    wire:model="selectedOption"
                    :options="$options"
                    :label="$label"
                    :placeholder="$placeholder"
                    :hint="$hint"
                    :option-value="$optionValue"
                    :option-label="$optionLabel"
                    class="w-full pr-10"
                    required
                >
                    <x-slot:prepend>
                        <x-button icon="{{ $icon }}" class="join-item" />
                    </x-slot:prepend>
                    <x-slot:append>
                        <x-button 
                            label=""
                            icon="phosphor.magnifying-glass-light"
                            wire:click="toggleNewOptionInput"
                            class="join-item btn-primary"
                            tooltip-bottom="Search/Add"
                        />
                    </x-slot:append>
                </x-select-group>
            @else
                <x-select-group
                    wire:model="selectedOption"
                    :options="$options"
                    :label="$label"
                    :placeholder="$placeholder"
                    :hint="$hint"
                    :option-value="$optionValue"
                    :option-label="$optionLabel"
                    class="w-full pr-10"
                >
                    <x-slot:prepend>
                        <x-button icon="{{ $icon }}" class="join-item" />
                    </x-slot:prepend>
                    <x-slot:append>
                        <x-button 
                            label=""
                            icon="phosphor.magnifying-glass-light"
                            wire:click="toggleNewOptionInput"
                            class="join-item btn-primary"
                            tooltip-bottom="Search/Add"
                        />
                    </x-slot:append>
                </x-select-group>
            @endif
        @endif

        @if (!$useGrouped)
            @if ($required)
                <x-select
                    wire:model="selectedOption"
                    :options="$options"
                    :label="$label"
                    :placeholder="$placeholder"
                    :hint="$hint"
                    :option-value="$optionValue"
                    :option-label="$optionLabel"
                    class="w-full pr-10"
                    required
                >
                    <x-slot:prepend>
                        <x-button icon="{{ $icon }}" class="join-item" />
                    </x-slot:prepend>
                    <x-slot:append>
                        <x-button 
                            label=""
                            icon="phosphor.magnifying-glass-light"
                            wire:click="toggleNewOptionInput"
                            class="join-item btn-primary"
                            tooltip-bottom="Search/Add"
                        />
                    </x-slot:append>
                </x-select>
            @else
                <x-select
                    wire:model="selectedOption"
                    :options="$options"
                    :label="$label"
                    :placeholder="$placeholder"
                    :hint="$hint"
                    :option-value="$optionValue"
                    :option-label="$optionLabel"
                    class="w-full pr-10"
                >
                    <x-slot:prepend>
                        <x-button icon="{{ $icon }}" class="join-item" />
                    </x-slot:prepend>
                    <x-slot:append>
                        <x-button 
                            label=""
                            icon="phosphor.magnifying-glass-light"
                            wire:click="toggleNewOptionInput"
                            class="join-item btn-primary"
                            tooltip-bottom="Search/Add"
                        />
                    </x-slot:append>
                </x-select>
            @endif
        @endif
    </div>
    
    {{-- Form to add a new option --}}
    @if($showNewOptionInput)
    <div class="mt-2 p-3 border rounded-md bg-base-200">
        <div class="flex items-center gap-2">
            @if ($forceUppercase)
            <x-input 
                wire:model="newOptionName"
                placeholder="..."
                class="flex-1 input-sm uppercase"
                wire:keydown.enter="createNewOption"
                x-init="$nextTick(() => $el.focus())"
                :maxlength="$maxlength"
            />
            @endif
            @if (!$forceUppercase)
            <x-input 
                wire:model="newOptionName"
                placeholder="..."
                class="flex-1 input-sm"
                wire:keydown.enter="createNewOption"
                x-init="$nextTick(() => $el.focus())"
                :maxlength="$maxlength"
            />
            @endif
            <x-button wire:click="createNewOption" icon="phosphor.plus" class="btn-primary btn-sm" />
            <x-button wire:click="cancel" icon="phosphor.x-circle" class="btn-ghost btn-sm" />
        </div>
        @error('newOptionName') 
            <div class="text-error text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>
    @endif
</div>