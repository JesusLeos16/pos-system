<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <div class="flex items-center justify-center h-screen">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">Login</h2>
            <form action="">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username</label>
                    <input type="text" id="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" id="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg"><a href="Pages/tienda.php">Iniciar Sesión</a></button>
            </form>
        </div>
    </div>

</body>

</html>