<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


    <!-- Styles -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/sass/main.scss', 'resources/sass/user-area.scss'])
</head>

<body class="font-sans antialiased">
    @include('layouts.top-bar')
    @include('layouts.navigation')

    <!-- Page Content -->
    <main class="tw-p-4">
        <div class="container-fluied">
            <div class="row">
                <div class="left-nav col-12 col-md-2">
                    <div class="thumbnail user-dp-container tw-flex tw-flex-col tw-justify-center tw-items-center">
                        <img src="{{ asset($user->avatar) }}" alt="" class="">
                        <span class="tw-mt-1 tw-font-medium">{{ $user->first_name . ' ' . $user->last_name }}</span>
                    </div>
                    <div x-data="{ showAdminLinks: true }" class="left-links tw-mt-7">
                        <a href="/user-area"><i class="fa fa-home"></i> Home</a>
                        <a href=""><i class="fa fa-cog"></i> Profile Edit</a>
                        <a href=""><i class="fa fa-comments-o"></i> Comments</a>
                        <span @click="showAdminLinks = !showAdminLinks" class="tw-cursor-pointer tw-ml-2">Admin
                            Area</span>
                        <div x-show="showAdminLinks" x-transition.duration.500ms class="">
                            <a href="/admin/users"><i class="fa fa-users"></i> Users</a>
                            <a href="/courses"><i class="fa fa-users"></i> Courses</a>
                            <a href="/posts/create"><i class="fa fa-file-text"></i> New Post</a>
                            <a href="/posts"><i class="fa fa-eye"></i> View Posts</a>
                            <a href="/comments"><i class="fa fa-unlock-alt"></i> Comments</a>
                            <a href="/roles"><i class="fa fa-compass"></i> Roles</a>
                        </div>
                        <a href=""><i class="fa fa-sign-out"></i> Logout</a>
                    </div>
                </div>
                <div class="col-12 col-md-10">
                    <!-- <h1>Welcome to DTech where we do tech with conscience!</h1> -->
                    {{ $slot }}

                </div>
                {{-- <div class="col-12 col-md-3">
                    <p class="tw-text-green-300 tw-font-bold">Let see how we can make this work</p>
                </div> --}}
            </div>
        </div>


    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://kit.fontawesome.com/b379d389cf.js" crossorigin="anonymous"></script>
</body>

</html>
