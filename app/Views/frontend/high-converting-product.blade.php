<?php
use FluentCart\App\Vite;
?>
<div class="w-full bg-white">
    <div class="max-w-6xl mx-auto px-4 py-10 md:py-14 flex flex-col gap-12">
        <section class="grid gap-8 md:grid-cols-2 items-start">
            <div class="flex flex-col gap-4">
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">
                    {{ $productTagline ?? __('Limited Offer', 'fluent-cart') }}
                </p>
                <h1 class="text-3xl md:text-4xl font-semibold text-slate-900 leading-tight">
                    {{ $productName ?? '' }}
                </h1>
                <p class="text-base text-slate-600 leading-relaxed">
                    {{ $productDescription ?? '' }}
                </p>
                <div class="flex flex-wrap items-center gap-4">
                    <div class="text-2xl font-semibold text-slate-900">
                        {{ $productPrice ?? '' }}
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="inline-flex items-center justify-center h-9 px-3 border border-slate-200 bg-slate-50 text-slate-700 font-medium">{{ $guaranteeLabel ?? __('30-Day Money-Back Guarantee', 'fluent-cart') }}</span>
                        <span class="inline-flex items-center justify-center h-9 px-3 border border-slate-200 bg-slate-50 text-slate-700 font-medium">{{ $securityLabel ?? __('Secure Checkout', 'fluent-cart') }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <a href="{{ $ctaUrl ?? '#' }}" class="inline-flex items-center justify-center px-6 py-3 bg-slate-900 text-white font-semibold text-base tracking-tight hover:bg-slate-800 transition-colors duration-150">
                        {{ $ctaText ?? __('Add to Cart', 'fluent-cart') }}
                    </a>
                    <p class="text-sm text-slate-500">{{ $subText ?? __('Instant digital delivery. Cancel anytime.', 'fluent-cart') }}</p>
                </div>
            </div>
            <div class="w-full">
                <div class="overflow-hidden border border-slate-200 bg-slate-50">
                    <img src="{{ $productImage ?? Vite::getAssetUrl('images/placeholder.svg') }}" alt="{{ $productName ?? __('Product image', 'fluent-cart') }}" class="w-full h-full object-cover">
                </div>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-3">
            @foreach(($benefits ?? []) as $benefit)
                <div class="flex gap-3 p-4 border border-slate-200 bg-white">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 font-semibold">
                        {{ $benefit['icon'] ?? '✓' }}
                    </div>
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-slate-900">{{ $benefit['title'] ?? '' }}</p>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $benefit['description'] ?? '' }}</p>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-8 md:grid-cols-2 items-center">
            <div class="aspect-video w-full bg-slate-100 border border-slate-200 flex items-center justify-center">
                <iframe src="{{ $videoUrl ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ' }}" title="{{ $productName ?? __('Product demo', 'fluent-cart') }}" class="w-full h-full" allowfullscreen></iframe>
            </div>
            <div class="flex flex-col gap-3">
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">{{ $videoLabel ?? __('See it in action', 'fluent-cart') }}</p>
                <h2 class="text-2xl font-semibold text-slate-900">{{ $videoHeading ?? __('Watch the demo', 'fluent-cart') }}</h2>
                <p class="text-base text-slate-600 leading-relaxed">{{ $videoDescription ?? __('Discover how this product works in real scenarios and why customers love it.', 'fluent-cart') }}</p>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-slate-900">{{ $descriptionHeading ?? __('Product details', 'fluent-cart') }}</h2>
            <div class="prose max-w-none text-slate-700 leading-relaxed">
                {!! $fullDescription ?? '' !!}
            </div>
        </section>

        <section class="flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">{{ $testimonialLabel ?? __('Trusted by customers', 'fluent-cart') }}</p>
                <h2 class="text-2xl font-semibold text-slate-900">{{ $testimonialHeading ?? __('What people are saying', 'fluent-cart') }}</h2>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach(($testimonials ?? [[],[],[]]) as $testimonial)
                    <div class="p-5 border border-slate-200 bg-white flex flex-col gap-3">
                        <p class="text-base text-slate-700 leading-relaxed">“{{ $testimonial['quote'] ?? __('This product made a big difference. Highly recommend!', 'fluent-cart') }}”</p>
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-slate-900">{{ $testimonial['name'] ?? __('Happy Customer', 'fluent-cart') }}</span>
                            <span class="text-xs text-slate-500">{{ $testimonial['role'] ?? __('Verified Buyer', 'fluent-cart') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-slate-900">{{ $featuresHeading ?? __('Features checklist', 'fluent-cart') }}</h2>
            <div class="overflow-hidden border border-slate-200">
                <table class="w-full text-left">
                    <tbody class="divide-y divide-slate-200">
                    @foreach(($features ?? []) as $feature)
                        <tr>
                            <td class="px-4 py-3 text-base text-slate-900 font-medium">{{ $feature['title'] ?? '' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $feature['description'] ?? '' }}</td>
                            <td class="px-4 py-3 text-center text-green-600 font-semibold">{{ $feature['included'] ?? __('Included', 'fluent-cart') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="p-6 border border-slate-200 bg-slate-50 flex flex-col gap-3">
            <h2 class="text-2xl font-semibold text-slate-900">{{ $guaranteeHeading ?? __('Our guarantee', 'fluent-cart') }}</h2>
            <p class="text-base text-slate-600 leading-relaxed">{{ $guaranteeCopy ?? __('We stand behind every purchase. If you are not satisfied, contact us within 30 days for a full refund.', 'fluent-cart') }}</p>
            <div class="flex flex-wrap gap-3 text-sm text-slate-700">
                <span class="inline-flex items-center px-3 py-2 bg-white border border-slate-200 font-medium">{{ $guaranteeBadge ?? __('Fast support response', 'fluent-cart') }}</span>
                <span class="inline-flex items-center px-3 py-2 bg-white border border-slate-200 font-medium">{{ $guaranteeBadgeSecondary ?? __('Hassle-free returns', 'fluent-cart') }}</span>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">{{ $faqLabel ?? __('Questions', 'fluent-cart') }}</p>
                <h2 class="text-2xl font-semibold text-slate-900">{{ $faqHeading ?? __('Frequently asked questions', 'fluent-cart') }}</h2>
            </div>
            <div class="flex flex-col divide-y divide-slate-200">
                @foreach(($faqs ?? []) as $faq)
                    <details class="py-3">
                        <summary class="flex items-center justify-between cursor-pointer text-base font-semibold text-slate-900">
                            <span>{{ $faq['question'] ?? '' }}</span>
                            <span class="text-slate-500">+</span>
                        </summary>
                        <div class="mt-2 text-sm text-slate-600 leading-relaxed">
                            {{ $faq['answer'] ?? '' }}
                        </div>
                    </details>
                @endforeach
            </div>
        </section>

        <section class="flex flex-col gap-4 items-center text-center p-6 border border-slate-200 bg-white">
            <h2 class="text-2xl md:text-3xl font-semibold text-slate-900">{{ $bottomHeading ?? __('Ready to get started?', 'fluent-cart') }}</h2>
            <p class="text-base text-slate-600 leading-relaxed max-w-2xl">{{ $bottomCopy ?? __('Join thousands of customers who transformed their results with this product.', 'fluent-cart') }}</p>
            <a href="{{ $ctaUrl ?? '#' }}" class="inline-flex items-center justify-center px-6 py-3 bg-slate-900 text-white font-semibold text-base tracking-tight hover:bg-slate-800 transition-colors duration-150">
                {{ $ctaText ?? __('Buy Now', 'fluent-cart') }}
            </a>
        </section>
    </div>
</div>
