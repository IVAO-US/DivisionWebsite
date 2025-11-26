<!DOCTYPE html>
<html>
<head>
    
    <!-- 
        Thank you to Saleh Riaz for publishing his codepen: https://codepen.io/salehriaz/pen/erJrZM
        All ErrorDocuments use his assets.
        More information can be found here: https://dribbble.com/shots/4330167-404-Page-Lost-In-Space
    -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield("code") Server Error Page</title>

    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,900" rel="stylesheet">

    <!-- Theme Styles -->
	<link rel="stylesheet" href="{{ asset('errorDocuments/styles.css') }}">
</head>
<body class="bg-purple">
    <div class="stars">
        <div class="central-body">
            <h1>@yield('code', '404')</h1>
            <h2>@yield('message', 'Oops! Page Not Found')</h2>
            <a href="{{ route('home') }}" class="btn-go-home">GO BACK HOME</a>
        </div>
        <div class="objects">
            <img class="object_rocket" src="{{ asset('errorDocuments/rocket.svg') }}" width="40px">
            <div class="earth-moon">
                <img class="object_earth" src="{{ asset('errorDocuments/earth.svg') }}" width="100px">
                <img class="object_moon" src="{{ asset('errorDocuments/moon.svg') }}" width="80px">
            </div>
            <div class="box_astronaut">
                <img class="object_astronaut" src="{{ asset('errorDocuments/astronaut.svg') }}" width="140px">
            </div>
        </div>
        <div class="glowing_stars">
            <div class="star"></div>
            <div class="star"></div>
            <div class="star"></div>
            <div class="star"></div>
            <div class="star"></div>

        </div>
    </div>
</body>
</html>