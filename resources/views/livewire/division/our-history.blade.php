<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule; 

use Livewire\Volt\Component;
use App\Traits\HasSEO;

new 
#[Layout('components.layouts.app')]
class extends Component {
    use HasSEO;
    public array $slides = [];

    public function mount(): void
    {
		$this->setSEOWithBreadcrumbs(
			title: 'Our History',
			description: config('seotools.meta.defaults.description'),
			image: asset('assets/seo/snapshot.jpg'),
			keywords: config('seotools.meta.defaults.keywords')
		);

        $this->slides = [
            [
                'image' => '../assets/img/our-history/2006-2008.png',
                'title' => '2006-2008',
                'description' => 'www.ivaousa.org',
                'url' => 'https://web.archive.org/web/20070106170738/http://www.ivaousa.org/web/',
                'urlText' => 'Web Archive',
            ],
            [
                'image' => '../assets/img/our-history/2008-2010.png',
                'title' => '2008-2010',
                'description' => 'www.ivaousa.org',
                'url' => 'http://web.archive.org/web/20081006211511/http://ivaousa.org/',
                'urlText' => 'Web Archive',
            ],
            [
                'image' => '../assets/img/our-history/2010-2013.png',
                'title' => '2010-2013',
                'description' => 'www.ivaousa.org/v2',
                'url' => 'http://web.archive.org/web/20100625235447/http://www.ivaousa.org/v2/',
                'urlText' => 'Web Archive',
            ],
            [
                'image' => '../assets/img/our-history/2013-2015.png',
                'title' => '2013-2015',
                'description' => 'www.ivaous.org',
                'url' => 'http://web.archive.org/web/20131005213413/http://www.ivaous.org/main/',
                'urlText' => 'Web Archive',
            ],
            [
                'image' => '../assets/img/our-history/2015-2019.png',
                'title' => '2015-2019',
                'description' => 'www.ivaoxa.org/web',
                'url' => 'http://web.archive.org/web/20180412095254/http://www.ivaoxa.org/web/',
                'urlText' => 'Web Archive',
            ],
            [
                'image' => '../assets/img/our-history/2019-2022.png',
                'title' => '2019-2022',
                'description' => 'xa.ivao.aero/web',
                'url' => 'http://web.archive.org/web/20201126074133/https://xa.ivao.aero/web/',
                'urlText' => 'Web Archive',
            ],
            [
                'image' => '../assets/img/our-history/2022-2023.png',
                'title' => '2022-2023',
                'description' => 'xa.ivao.aero/web',
                'url' => 'https://web.archive.org/web/20220621221659/https://xa.ivao.aero/web/',
                'urlText' => 'Web Archive',
            ],
            [
                'image' => '../assets/img/our-history/2023-2025.png',
                'title' => '2023-2025',
                'description' => 'xa.ivao.aero | us.ivao.aero',
                'url' => 'http://web.archive.org/web/20230610193906/https://xa.ivao.aero/',
                'urlText' => 'Web Archive',
            ],
        ];
    }
}; ?>

<div>
    <x-header title="Our History" size="h2" subtitle="A look into the past of IVAO USA and the North America Division." class="!mb-5" />

    <x-card title="Blast from the past" subtitle="Visit the web archive for more details." shadow separator>
        <x-carousel :slides="$slides" class="!h-[50vh] lg:!h-[65vh] lg:!h-[80vh]" />
    </x-card>
</div>