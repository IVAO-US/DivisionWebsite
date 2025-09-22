<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule; 

use Livewire\Volt\Component;

new 
#[Layout('components.layouts.app')]
#[Title('Our History')]
class extends Component {

}; ?>

<div>
    <x-header title="Our History" size="h2" subtitle="A look into the past of IVAO USA, Canada and North America Division" class="!mb-5" />

    <x-card title="Blast from the past" subtitle="Click on the image to open the Webarchive website snapshot!" shadow separator>
        Hello
    </x-card>
</div>