<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new 
#[Layout('components.layouts.app')]
#[Title('Become ATC')]
class extends Component {
    public int $currentStep = 1;
    public int $maxUnlockedStep = 1;
    public string $selectedTab = "1-tab";
    
    public function mount(): void
    {
        $this->currentStep = 1;
        $this->maxUnlockedStep = 1;
        $this->selectedTab = "1-tab";
    }
    
    public function nextStep(): void
    {
        if ($this->currentStep < 4) {
            $this->currentStep++;
            $this->maxUnlockedStep = max($this->maxUnlockedStep, $this->currentStep);
            $this->selectedTab = $this->currentStep . "-tab";
        }
    }
    
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->selectedTab = $this->currentStep . "-tab";
        }
    }
    
    public function goToStep(int $step): void
    {
        if ($step <= $this->maxUnlockedStep && $step >= 1 && $step <= 4) {
            $this->currentStep = $step;
            $this->selectedTab = $this->currentStep . "-tab";
        }
    }
    
    public function isStepUnlocked(int $step): bool
    {
        return $step <= $this->maxUnlockedStep;
    }
}; ?>

<div>
    <x-header title="Become ATC" size="h2" subtitle="How to become a great Air Traffic Controller?" class="!mb-5" />

    <x-tabs wire:model="selectedTab"
            class="w-full"
            label-div-class="bg-base-100 !p-3 !mb-4 rounded-lg font-semibold whitespace-nowrap overflow-x-auto" 
            active-class="bg-primary p-3 rounded-lg !text-white font-semibold" 
            label-class="p-3 font-semibold">
        
        {{-- Step 1: Welcome --}}
        <x-tab 
            name="1-tab" 
            label="Welcome" 
            icon="phosphor.house"
            :disabled="false"
            wire:click="goToStep(1)"
        >
            <x-card title="Welcome to IVAO" subtitle="Start your journey as a virtual Air Traffic Controller" shadow separator>
                <div class="space-y-6">
                    <p>
                        <strong>🥳 Nice that you have decided to participate as a virtual air traffic controller.</strong>
                    </p>

                    <p>
                        IVAO – as a technical network, but also as a community for flight enthusiasts – tries to replicate 
                        the real processes and procedures as well as possible under the motto "as real as it gets".<br> 
                        It's lots of fun yet still also requires you to continue your education independently in order 
                        to prepare for the controller exams.
                    </p>
                    
                    <p>
                        But you don't need to be afraid, nobody is perfect and nobody was born or joined IVAO as a 
                        professional – apart from the real pilots or controllers who also exist on our network.<br> 
                        Nevertheless, there are some theoretical basics that you should have internalized as a controller 
                        on the network before logging in for the first time.
                    </p>
                    
                    <p>
                        Of course, you also need an IVAO account.<br>
                        If you have not created one yet, this is the first step: 
                        <x-button 
                            label="Join us!"
                            link="https://ivao.aero/members/person/ADJregister3.asp"
                            external
                            class="btn btn-accent btn-sm" />
                    </p>
                    
                    <p class="font-semibold">
                        So, if you have an IVAO account and you are ready for the next steps, keep on reading!<br>
                        Let's take a look at which theoretical basics you should know before logging in for the first time!
                    </p>
                </div>
            </x-card>
        </x-tab>

        {{-- Step 2: Basics --}}
        <x-tab 
            name="2-tab" 
            label="Basics" 
            icon="o-book-open"
            :disabled="!$this->isStepUnlocked(2)"
            wire:click="goToStep(2)"
        >
            <x-card title="Learning the Basics" subtitle="Get a fundamental understanding of ATC procedures" shadow separator>
                <div class="space-y-6">
                    <p>
                        In order to get started you will need to get a good fundamental understanding on how things work.<br>
                        For this reason you will need to educate yourself and start reading through our basics which can 
                        be found on our wiki.<br>
                        Topics such as Orientation Guides, Standard Procedures, Local Procedures, and How-To's are located there.
                    </p>
                    
                    <p>
                        Our wiki offers content for all types of users, those who have just registered with IVAO and want to take their first steps, or the experienced users who want to prepare for a practical exam.
                    </p>

                    <x-alert icon="phosphor.lightbulb-light" class="w-full alert-info border-info bg-info mb-6 ">
                        <h5 class="text-info-content mb-2">Start reading our Wiki</h5>
                        Learn the fundamentals before you start: 
                        <a target="_blank" href="https://wiki.us.ivao.aero/" class="link">visit our Wiki</a>
                    </x-alert>

                    <div>
                        <p class="mb-4">
                            Since all this content can be overwhelming we recommend focusing on the articles linked below.<br> 
                            Remember that we do not require that you know everything, we simply expect you to know where 
                            to find the information once needed.<br><br>
                            
                            We suggest you bookmark these pages for easier access in the future:
                        </p>
                        
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2">
                                <x-icon name="phosphor.check-circle" class="w-5 h-5 text-info" />
                                <a target="_blank" href="https://wiki.us.ivao.aero/en/atc/sop/general" class="font-semibold underline">Basic Control Procedures</a>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="phosphor.check-circle" class="w-5 h-5 text-info" />
                                <a target="_blank" href="https://wiki.us.ivao.aero/en/atc/guides" class="font-semibold underline">ATC Guides</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </x-card>
        </x-tab>

        {{-- Step 3: Observe --}}
        <x-tab 
            name="3-tab" 
            label="Observe" 
            icon="o-eye"
            :disabled="!$this->isStepUnlocked(3)"
            wire:click="goToStep(3)"
        >
            <x-card title="Observe the Network" subtitle="Learn by watching pilots and controllers in action" shadow separator>
                <div class="space-y-6">
                    <p>
                        Watching or listening to pilots and controllers on the network is what we at IVAO call <strong>observing</strong>.
                    </p>
                    
                    <p>
                        In order to observe on the network you will need to install 
                        <a target="_blank" href="https://ivao.aero/softdev/software/aurora.asp" class="underline font-semibold">Aurora</a> (ATC client).
                    </p>
                    
                    <p>
                        We offer newbie evenings at regular intervals where we show you live what you have to do to connect as an Observer.<br>
                        If you don't want to wait that long, here are the steps to get started:
                    </p>

                    <x-alert icon="phosphor.lightbulb-light" class="w-full alert-info border-info bg-info mb-6 ">
                        <h5 class="text-info-content mb-2">Quick Start Guide</h5>
                        How to observe on the network with Aurora: 
                        <a target="_blank" href="https://wiki.us.ivao.aero/en/atc/guides/aurora#h-7-connect-as-observer" class="link">read the IVAO Wiki article</a>
                    </x-alert>

                    <x-alert icon="phosphor.shooting-star" class="w-full alert-success border-success bg-success mb-6 ">
                        <h5 class="text-success-content mb-2">Pro Tip</h5>
                        Now you can listen to other users on the network while you are working on the content in the wiki basics.<br>
                        We also recommend checking who is online with our 
                        <a target="_blank" href="https://webeye.ivao.aero/" class="underline font-semibold">interactive map at IVAO WebEye</a>. <br><br>
                        <strong>If you feel safe in theory, then it is now a matter of finding an airfield to start as a ATC.</strong>
                    </x-alert>

                </div>
            </x-card>
        </x-tab>

        {{-- Step 4: Training --}}
        <x-tab 
            name="4-tab" 
            label="Training" 
            icon="o-academic-cap"
            :disabled="!$this->isStepUnlocked(4)"
            wire:click="goToStep(4)"
        >
            <x-card title="Request Training" subtitle="Complete your supervised training to become certified" shadow separator>
                <div class="space-y-6">
                    <x-alert icon="phosphor.shield-check" class="w-full alert-warning border-warning bg-warning mb-6 ">
                        <h5 class="text-warning-content mb-2">Quality Control</h5>
                        In order to keep the quality of our network high, it is not possible to control any position as new controller unsupervised.
                    </x-alert>

                    <p>
                        FRAs (facility rating assignments) were created for this purpose, which only allow logging in to 
                        such positions based on certain ATC ratings.<br>
                        Generally speaking our division requires at least <strong>AS2 rating (ATC Student 2)</strong> to connect anywhere.<br>
                        Your initial rating as new controller will be AS1 and thus, in order to control, you will need to request ATC training and successfully 
                        complete 10 supervised hours of active controlling as Clearance Delivery/Ground Controller.
                    </p>
                    
                    <p>
                        You can read more on this in our 
                        <a target="_blank" href="https://wiki.us.ivao.aero/en/atc/training" class="font-semibold underline">Training Procedures</a> page.
                    </p>
                    
                    <p>
                        Once you have decided on an airport, then (unfortunately) you have to start learning again, 
                        because in addition to the uniform basics throughout the US and Canada, every airport is 
                        different and has its own procedures.<br>
                        It is therefore important that you now familiarize yourself with these <a target="_blank" href="https://wiki.us.ivao.aero/en/atc/sop" class="font-semibold underline">local procedures</a> 
                        at your airport (if available).
                    </p>
                    
                    <p class="font-bold">
                        Once done, then nothing stands in the way of your first ATC lessons:
                    </p>

                    {{-- ATC Positions --}}
                    <x-card title="ATC Positions Overview" subtitle="Your duties as a rookie controller" class="bg-neutral/50" separator>
                        <div class="space-y-4">
                            <div class="border-l-4 border-info pl-4">
                                <h6 class="text-secondary font-semibold">Clearance Delivery (DEL)</h6>
                                <p class="text-sm">
                                    At the <strong>clearance delivery (DEL)</strong> position you are responsible to issue IFR flightplan clearances 
                                    in accordance with local SOPs and what the pilot requested.
                                </p>
                            </div>
                            
                            <div class="border-l-4 border-success pl-4">
                                <h6 class="text-secondary font-semibold">Ground Controller (GND)</h6>
                                <p class="text-sm">
                                    The <strong>ground controller (GND)</strong> is responsible for taxiing the aircraft from their parking position 
                                    to the runway and from the runway to the parking position. At airports without delivery or if delivery 
                                    is not online, the ground controller also handles Clearance Delivery.
                                </p>
                            </div>
                            
                            <div class="border-l-4 border-error pl-4">
                                <h6 class="text-secondary font-semibold">Tower Controller (TWR)</h6>
                                <p class="text-sm">
                                    The tasks of the <strong>tower controller (TWR)</strong> becomes more demanding: This position gives all clearances 
                                    for the runway and is responsible for the planes in his control zone which can be quite demanding. 
                                    If the ground controller is absent, the tower controller also takes over his duties.
                                </p>
                            </div>
                        </div>
                    </x-card>

                    <x-alert icon="phosphor.airplane-takeoff" class="w-full alert-success border-success bg-success mb-6 ">
                        <h5 class="text-success-content mb-2">Ready to Start?</h5>
                        Once you feel ready go ahead and submit a 
                        <a target="_blank" href="https://www.ivao.aero/training/training/requestrainingpatc.asp?Rating=0" class="underline font-semibold">training request</a> 
                        via the IVAO training system.<br>
                        You will then be contacted by one of our division training staff.
                        <br><br>
                        <x-button 
                            label="Submit Training Request" 
                            icon="phosphor.paper-plane-tilt" 
                            class="btn-accent"
                            link="https://www.ivao.aero/training/training/requestrainingpatc.asp?Rating=0"
                            external
                        />
                    </x-alert>
                </div>
            </x-card>
        </x-tab>
    </x-tabs>

    {{-- Navigation Buttons Container - Outside the tabs --}}
    <x-card class="mt-4">
        @if($currentStep === 1)
            <div class="flex justify-end">
                <x-button 
                    label="Next Step" 
                    icon="phosphor.arrow-right" 
                    class="btn-primary"
                    wire:click="nextStep"
                />
            </div>
        @elseif($currentStep === 2 || $currentStep === 3)
            <div class="flex justify-between">
                <x-button 
                    label="Previous Step" 
                    icon="phosphor.arrow-left" 
                    class="btn-neutral"
                    wire:click="previousStep"
                />
                <x-button 
                    label="Next Step" 
                    icon="phosphor.arrow-right" 
                    class="btn-primary"
                    wire:click="nextStep"
                />
            </div>
        @elseif($currentStep === 4)
            <div class="flex justify-between">
                <x-button 
                    label="Previous Step" 
                    icon="phosphor.arrow-left" 
                    class="btn-neutral"
                    wire:click="previousStep"
                />
                <x-button 
                    label="Guide Completed" 
                    icon="phosphor.check-circle" 
                    class="btn-success"
                />
            </div>
        @endif
    </x-card>
</div>