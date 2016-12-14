<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Cookie</title>
        <!--
        Laravel to make test pass
        tests/ExampleTest.php:17
         -->
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    </head>
    <body>
        <div>
            No valid API code found, please provide API code
            <form action="." method="post" style="margin: 0; padding: 0;">
                Toggle API: <input type="text" name="toggl_api" />
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit"/>
            </form>
        </div>
    </body>
</html>
