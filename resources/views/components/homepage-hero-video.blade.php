<?php
use Livewire\Component;

new class extends Component {
    public string $logoPath = './assets/img/ivao-branding-transparent.svg';
    public string $videoPath = './assets/video/hero-background.mp4';
    public string $fallbackImagePath = './assets/img/fallback-video-bg.png';
    public string $posterPath = './assets/img/fallback-video-bg.png';
    public string $title = 'United States Division';
    public string $joinUrl = 'https://ivao.aero/members/person/ADJregister3.asp';
}; ?>

<div>
    <section class="hero-section relative w-full min-h-[calc(100vh-144px)] flex items-center justify-center overflow-hidden">
        {{-- Video Background --}}
        <div    class="video-wrap absolute inset-0 w-full h-full z-0 bg-cover bg-center bg-no-repeat" 
                style="background-image: url('{{ $fallbackImagePath }}');">
            <video 
                id="hero-video"
                autoplay 
                loop 
                muted 
                playsinline 
                webkit-playsinline
                disablepictureinpicture
                preload="metadata"
                poster="{{ $posterPath }}"
                class="custom-video w-full h-full object-cover object-center"
                x-data="heroVideo()"
                x-init="initVideo()">
                    <source src="{{ $videoPath }}#t=0.001" type="video/mp4">
                    <source src="{{ str_replace('.mp4', '.webm', $videoPath) }}#t=0.001" type="video/webm">
                    Your browser does not support the video tag.
            </video>
        </div>
        
        {{-- Dark Overlay --}}
        <div class="section-overlay absolute inset-0 bg-black/40 z-10"></div>

        {{-- Content Container --}}
        <div class="relative z-20 h-full w-full max-w-4xl mx-auto px-6 flex flex-col items-center justify-center md:justify-between text-center">
            
            <div class="flex flex-col items-center space-y-6">
                {{-- Logo --}}
                <div class="logo-container">
                    <img src="{{ $logoPath }}" 
                         alt="IVAO Logo"
                         class="w-80 md:w-100 lg:w-120 xl:w-160 h-auto mx-auto block">
                </div>

                {{-- Title --}}
                <p class="text-white text-center mb-16 h3 md:text-[2rem] lg:text-[4.00rem] xl:text-[5rem] font-bold leading-tight">
                    {{ $title }}
                </p>
            </div>

            <div class="flex flex-col items-center space-y-6 mb-16">
                {{-- Call to Action Button --}}
                <div class="cta-container">
                    <x-button 
                        link="{{ $joinUrl }}"
                        external
                        class="btn btn-accent text-white px-4 py-6 md:py-8 mb-4 md:mb-10 font-bold"
                    >
                        <span class="text-[1.00rem] md:text-[1.25rem] lg:text-[1.50rem]">Join Today!</span>
                    </x-button>
                </div>

                {{-- Social Links --}}
                <livewire:social-links />
            </div>
        </div>
    </section>

    {{-- JavaScript for iOS Safari video handling --}}
    @script
    <script>
        // Make function available globally for Alpine.js
        window.heroVideo = function() {
            return {
                video: null,
                playPromise: null,

                initVideo() {
                    this.video = document.getElementById('hero-video');

                    if (!this.video) {
                        console.error('❌ Video element not found');
                        return;
                    }

                    //console.log('🚀 Initializing video for iOS Safari compatibility');

                    // iOS Safari compatibility setup
                    this.video.muted = true;
                    this.video.playsInline = true;

                    // Event listeners for video states
                    this.video.addEventListener('loadedmetadata', () => {
                        //console.log('✅ Video metadata loaded');
                        this.attemptAutoplay();
                    });

                    this.video.addEventListener('canplay', () => {
                        //console.log('✅ Video can play - showing video');
                        // Show video with fade-in effect
                        this.video.classList.remove('opacity-0');
                        this.video.classList.add('opacity-100');
                    });

                    this.video.addEventListener('play', () => {
                        //console.log('▶️ Video started playing successfully');
                    });

                    this.video.addEventListener('error', (e) => {
                        console.error('❌ Video error:', e);
                        this.fallbackToImage();
                    });

                    // Initial autoplay attempt
                    this.attemptAutoplay();
                },

                attemptAutoplay() {
                    //console.log('🎬 Attempting autoplay...');

                    // iOS Safari autoplay with promise handling
                    this.playPromise = this.video.play();

                    if (this.playPromise !== undefined) {
                        this.playPromise
                            .then(() => {
                                //console.log('✅ Autoplay successful!');
                            })
                            .catch(error => {
                                console.log('⚠️ Autoplay failed:', error.name);
                                this.fallbackToImage();
                            });
                    }
                },

                fallbackToImage() {
                    //console.log('🔄 Falling back to background image');
                    // Hide video and keep the background image
                    this.video.style.display = 'none';
                    // Background image is already set on parent div
                }
            }
        }

        // iOS Safari detection and logging
        (function() {
            const isIOSSafari = /iPad|iPhone|iPod/.test(navigator.userAgent) &&
                               /Safari/.test(navigator.userAgent) &&
                               !/CriOS|FxiOS|OPiOS|mercury/.test(navigator.userAgent);

            if (isIOSSafari) {
                //console.log('📱 iOS Safari detected - applying specific optimizations');
            } else {
                //console.log('🖥️ Desktop/other browser detected');
            }
        })();
    </script>
    @endscript
</div>