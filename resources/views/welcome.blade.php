<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel Event Broadcasting Example</title>
    <style>
        body{
            padding: 20px;
        }
    </style>
</head>
<body>
    <h1>Queue List</h1>
    <pre id="queue"></pre>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdn.socket.io/socket.io-1.3.5.js"></script>
    <script>
        var socket = io('http://localhost:3000');

        socket.on('test-channel:App\\Events\\TestEvent', function(message){        
            $('#queue').text(JSON.stringify(message.data, undefined, 2));
        });

        socket.on('test-channel:App\\Events\\ReleaseEvent', function(message){        
            $('#queue').text(JSON.stringify(message.data, undefined, 2));
        });

    </script>
</body>
</html>