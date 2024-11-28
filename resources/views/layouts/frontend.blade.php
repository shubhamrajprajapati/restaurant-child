@props(['metadata', 'socialMedia', 'colorTheme', 'homePageData'])

<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.frontend.partials.page-edit-data', ['pageEdit' => $homePageData])
    {{-- @include('layouts.partials.frontend.partials.opening-hour-data') --}}
    @include('layouts.partials.frontend.partials.color-theme-data', compact('colorTheme'))
    @include('layouts.partials.frontend.head')
    @stack('styles')
</head>

<body>
    <!--HEADER START-->

    @if(!empty($rollingMessage) && !empty($rollingMessage->contents))
        <marquee class="res_marquees_css">
            {{ $rollingMessage }}
        </marquee>
    @endif

    @include('layouts.partials.frontend.header', compact('metadata'))

    <!--HEADER END-->

    <main>
        <!--MAIN CONTENT START-->
        {{ $slot }}
        <!--MAIN CONTENT END-->
    </main>

    <!--FOOTER START-->
    @include('layouts.partials.frontend.footer')
    <!--FOOTER END-->

    <!--FOOTER SCRIPTS START -->
    @include('layouts.partials.frontend.script')
    <!--FOOTER SCRIPTS START -->

    <!-- This is where scripts will be stacked -->
    @stack('scripts')
</body>

</html>
