<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @livewireStyles
</head>
<body>

    livewireテスト
    <div>
        @if (session()->has('message'))
            <div>
                {{session('message')}}
            </div>
        @endif

    </div>
    {{-- <livewire:counter/> --}}
    @livewire('counter')

    @livewireScripts
</body>
</html>
