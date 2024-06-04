<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>

    <link
            href="https://db.onlinewebfonts.com/c/040a78b437acd0433612f92e61d04a1b?family=Gilroy-Medium"
            rel="stylesheet"
            type="text/css"
    />

    <style>
        @font-face {
            font-family: "Gilroy-Medium";
            src: url("https://db.onlinewebfonts.com/t/040a78b437acd0433612f92e61d04a1b.eot");
            src: url("https://db.onlinewebfonts.com/t/040a78b437acd0433612f92e61d04a1b.eot?#iefix")
            format("embedded-opentype"),
            url("https://db.onlinewebfonts.com/t/040a78b437acd0433612f92e61d04a1b.woff2")
            format("woff2"),
            url("https://db.onlinewebfonts.com/t/040a78b437acd0433612f92e61d04a1b.woff")
            format("woff"),
            url("https://db.onlinewebfonts.com/t/040a78b437acd0433612f92e61d04a1b.ttf")
            format("truetype"),
            url("https://db.onlinewebfonts.com/t/040a78b437acd0433612f92e61d04a1b.svg#Gilroy-Medium")
            format("svg");
        }
    </style>
</head>

<body style="
			font-family: 'Gilroy-Medium', sans-serif;
			color: #686670;
			font-weight: 500;
		"
>

<table style="
            max-width: 600px;
            min-width: 600px;
            margin: auto;
        ">
    <tbody>

        <tr>
            <td>
                <div class="header__title">
                    <h1 style="font-size: 32px; color: black">{{$greeting}}</h1>
                </div>
            </td>
        </tr>


        <tr>
            <td style="padding: 30px; text-align: center">
                @yield('content')
            </td>
        </tr>


        <tr>

        </tr>

    </tbody>
</table>



</body>
</html>
