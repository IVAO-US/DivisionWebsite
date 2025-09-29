<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new 
#[Layout('components.layouts.app')]
#[Title('Exams')]
class extends Component {
}; ?>

<div>
    <x-header title="Exams" size="h2" subtitle="Ready to advance your rating?" class="!mb-5" />

    <x-card title="Exam Request" subtitle="How to request your practical exam" shadow separator>
        
        <x-alert icon="phosphor.info" class="w-full alert-info border-info bg-info mb-6">
            <h5 class="text-info-content mb-4">Official Exam Briefings</h5>
            <span class="text-info-content">
                Please visit the official <a target="_blank" href="https://wiki.ivao.aero/en/home/training/main/training_procedures/Examination_procedure" class="font-semibold underline">IVAO Exam Briefings</a> page and read carefully through your corresponding exam briefing document.
            </span>
        </x-alert>

        <x-alert icon="phosphor.warning" class="w-full alert-error border-error bg-error mb-6">
            <h5 class="text-error-content mb-4">Theoretical Knowledge</h5>
            <span class="text-error-content">
                The theoretical part is always performed first, and if your knowledge is too low, you will have to repeat it before you can advance to the practical part.<br>
                This goes inline with the HQ Training Department, to ensure that you possess the required skills to pass the practical part of the Exam.            
            </span>
        </x-alert>

        <h5 class="mb-4">For Pilots</h5>
        
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.airplane-taxiing" class="w-6 h-6 text-secondary mt-1" />
                    <span>You as an examinee need to inform the used aircraft to perform the exam when the Examiner contacts you to schedule the exam.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.map-pin" class="w-6 h-6 text-secondary mt-1" />
                    <span>
                        In order for you to prepare your flight, do all the required calculations and routing<br>
                        Your departure and destination airports will be given between <b>48h to 24h hours</b> before the planned date.
                    </span>
                </div>
            </div>
        </div>

        <h5 class="mb-4">As ATC</h5>
        
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.check-circle" class="w-6 h-6 text-secondary mt-1" />
                    <span>A practical exam is designed to evaluate the student's ability to work a position without any assistance.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.hand-waving" class="w-6 h-6 text-secondary mt-1" />
                    <span>To request a practical exam the student must obtain at least one "recommendation" by his/her assigned instructor.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.identification-card" class="w-6 h-6 text-secondary mt-1" />
                    <span>A practical exam will be proctored by a division training advisor who will then be the designated examiner.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.notepad" class="w-6 h-6 text-secondary mt-1" />
                    <span>A score and summary of the exam will then be submitted by the examiner to a Senior Training Advisor for final evaluation.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.medal" class="w-6 h-6 text-secondary mt-1" />
                    <span>If passed, the new rating will be assigned.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.arrow-clockwise" class="w-6 h-6 text-secondary mt-1" />
                    <span>If failed, the student may request a new practical exam.</span>
                </div>
            </div>
        </div>

        <div class="w-full flex justify-center">
            <x-button 
                label="Submit Exam Request" 
                icon="phosphor.paper-plane-tilt" 
                class="btn-accent"
                link="https://www.ivao.aero/training/exam/status.asp"
                external
            />
        </div>
    </x-card>
</div>