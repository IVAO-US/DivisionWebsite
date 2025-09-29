<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new 
#[Layout('components.layouts.app')]
#[Title('Training Request')]
class extends Component {

}; ?>

<div>
    <x-header title="Training" size="h2" subtitle="Ready to start your training journey?" class="!mb-5" />

    <x-card title="Training Request" subtitle="How to request your training" shadow separator>
        
        <div class="mb-8">
            <p class="mb-4">
                Our Division has an excellent team of Trainers, most of them with real experience, all which try their best to help you to learn how to sit behind the flight controls and how to guide planes safely.
            </p>
            
            <p class="mb-4">
                For initial <strong>Pilot</strong> training, you have the <a target="_blank" href="https://wiki.ivao.aero/en/home/training/documentation">IVAO Wiki</a> which will help you understand how the network works and explain topics such as flight rules, airspaces, phraseology, etc.
            </p>
            
            <p class="mb-4">
                For advanced Pilot ratings, the same link above will help you to tune your knowledge to perfection for the theoretical exam. Our Trainer team will then gladly help you to put your knowledge to practice and give you some tips before you request your exam.
            </p>
            
            <p>
                For new <strong>Air Traffic Controllers</strong> the training can be much more complex and it is expected that atc students do some initial reading about our atc training procedures on their own time. After that, we are always more than ready to assist you in any doubt you might have.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mt-8">
            <a href="https://www.ivao.aero/training/training/requestrainingpatc.asp?Rating=0" class="btn btn-accent lg:btn-lg lg:min-w-[250px]">
                <x-icon name="phosphor.radio" class="w-5 h-5" />
                Request ATC Training
            </a>
            
            <a href="https://www.ivao.aero/training/training/requestrainingpilot.asp?Rating=0" class="btn btn-primary lg:btn-lg lg:min-w-[250px]">
                <x-icon name="phosphor.airplane-takeoff" class="w-5 h-5" />
                Request Pilot Training
            </a>
        </div>

    </x-card>
</div>