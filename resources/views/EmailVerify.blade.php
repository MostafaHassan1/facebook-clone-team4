<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <div>
            Hi {{ $name }};
            <br>
            Thank you for creating an account with us. Don't forget to complete your Registrstion!!
            <br>
            Please click on the link below or copy it into tha address bar of your browser to confirm your email address:
            <br>
            <a href="{{route('verify', ["code"=>$code])}}">Confirm my email address</a>
            
            <br/>
        </div>
    </body>
</html>