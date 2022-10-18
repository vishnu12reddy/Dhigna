<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
    @if(!empty($files))
        <ul>
            @foreach($files as $key => $file)
                <li><a href={{$file}} download>{{basename($file)}}</a></li>
            @endforeach
        </ul>
    @endif    
</body>
</html>




